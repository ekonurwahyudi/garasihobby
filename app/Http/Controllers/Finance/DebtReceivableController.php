<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\DebtReceivable;
use App\Models\DebtReceivableCategory;
use App\Models\DebtReceivablePayment;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DebtReceivableController extends Controller
{
    public function index(): View
    {
        $data = DebtReceivable::with(['bankAccount', 'payments.bankAccount', 'submitter', 'approver'])
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        return view('finance.debt-receivables.index', compact('data'));
    }

    public function create(): View
    {
        return view('finance.debt-receivables.create', [
            'debtReceivable' => null,
            'debtCategories' => DebtReceivableCategory::orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['category'] = DebtReceivableCategory::find($data['debt_receivable_category_id'])?->name;
        $base = (float) ($data['total_amount'] ?: $data['amount']);
        $paid = (float) ($data['paid_amount'] ?? 0);
        $data['transaction_number'] = $this->transactionNumber();
        $data['total_amount'] = $base;
        $data['remaining_amount'] = max(0, $base - $paid);
        $data['payment_status'] = $this->paymentStatus($base, $paid);
        $data['status'] = 'menunggu_approval';
        $data['submitted_by'] = auth()->id();
        $data['submitted_at'] = now();
        $data['evidence_paths'] = $this->storeFiles($request, 'evidence', 'debt-receivables');

        DebtReceivable::create($data);

        return redirect()->route('debt-receivables.index')->with('success', 'Hutang/piutang berhasil diajukan dan menunggu approval.');
    }

    public function show(DebtReceivable $debt_receivable): View
    {
        $debt_receivable->load(['bankAccount', 'payments.bankAccount', 'payments.creator', 'submitter', 'approver', 'rejecter']);

        return view('finance.debt-receivables.show', [
            'debtReceivable' => $debt_receivable,
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
            'evidenceFiles' => $this->fileObjects($debt_receivable->evidence_paths ?: []),
        ]);
    }

    public function edit(DebtReceivable $debt_receivable): View
    {
        if ($debt_receivable->status === 'disetujui') {
            return redirect()->route('debt-receivables.show', $debt_receivable)->with('error', 'Data yang sudah disetujui tidak dapat diedit.');
        }

        return view('finance.debt-receivables.edit', [
            'debtReceivable' => $debt_receivable,
            'debtCategories' => DebtReceivableCategory::orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ]);
    }

    public function update(Request $request, DebtReceivable $debt_receivable): RedirectResponse
    {
        if ($debt_receivable->status === 'disetujui') {
            return redirect()->route('debt-receivables.show', $debt_receivable)->with('error', 'Data yang sudah disetujui tidak dapat diedit.');
        }

        $data = $this->validatedData($request);
        $data['category'] = DebtReceivableCategory::find($data['debt_receivable_category_id'])?->name;
        $base = (float) ($data['total_amount'] ?: $data['amount']);
        $paid = (float) ($data['paid_amount'] ?? 0);
        $data['total_amount'] = $base;
        $data['remaining_amount'] = max(0, $base - $paid);
        $data['payment_status'] = $this->paymentStatus($base, $paid);
        $data['status'] = 'menunggu_approval';
        $data['rejected_by'] = null;
        $data['rejected_at'] = null;
        $data['rejection_reason'] = null;
        if ($request->hasFile('evidence')) {
            $data['evidence_paths'] = $this->storeFiles($request, 'evidence', 'debt-receivables');
        }

        $debt_receivable->update($data);

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Hutang/piutang berhasil diperbarui.');
    }

    public function approve(DebtReceivable $debt_receivable): RedirectResponse
    {
        if ($debt_receivable->status !== 'menunggu_approval') {
            return back()->with('error', 'Data ini sudah diproses.');
        }

        DB::transaction(function () use ($debt_receivable) {
            $bank = BankAccount::lockForUpdate()->findOrFail($debt_receivable->bank_account_id);
            $isDebt = $debt_receivable->type === 'debt';
            $principal = (float) $debt_receivable->amount;

            if (! $isDebt && (float) $bank->balance < $principal) {
                throw ValidationException::withMessages(['bank_account_id' => 'Saldo bank tidak mencukupi untuk pencairan piutang.']);
            }

            $isDebt ? $bank->increment('balance', $principal) : $bank->decrement('balance', $principal);
            FinanceTransaction::create([
                'transaction_number' => $this->financeTransactionNumber(),
                'transaction_type' => $isDebt ? 'income' : 'expense',
                'transaction_date' => $debt_receivable->transaction_date,
                'finance_item_id' => $this->initialFinanceItem($debt_receivable->type)->id,
                'bank_account_id' => $debt_receivable->bank_account_id,
                'activity' => ($isDebt ? 'Penerimaan Hutang ' : 'Pencairan Piutang ') . $debt_receivable->transaction_number,
                'description' => ($isDebt ? 'Penerimaan Hutang ' : 'Pencairan Piutang ') . $debt_receivable->transaction_number,
                'amount' => $principal,
                'notes' => $debt_receivable->notes,
                'evidence_paths' => $debt_receivable->evidence_paths ?: [],
                'status' => 'disetujui',
                'created_by' => auth()->id(),
                'submitted_by' => $debt_receivable->submitted_by,
                'submitted_at' => $debt_receivable->submitted_at,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $paid = (float) $debt_receivable->paid_amount;
            if ($paid > 0 && ! $debt_receivable->payments()->exists()) {
                if ($isDebt && (float) $bank->balance < $paid) {
                    throw ValidationException::withMessages(['bank_account_id' => 'Saldo bank tidak mencukupi untuk pembayaran hutang.']);
                }

                $isDebt ? $bank->decrement('balance', $paid) : $bank->increment('balance', $paid);
                $finance = FinanceTransaction::create([
                    'transaction_number' => $this->financeTransactionNumber(),
                    'transaction_type' => $isDebt ? 'expense' : 'income',
                    'transaction_date' => $debt_receivable->transaction_date,
                    'finance_item_id' => $this->financeItem($debt_receivable->type)->id,
                    'bank_account_id' => $debt_receivable->bank_account_id,
                    'activity' => ($isDebt ? 'Pembayaran Awal Hutang ' : 'Penerimaan Awal Piutang ') . $debt_receivable->transaction_number,
                    'description' => ($isDebt ? 'Pembayaran Awal Hutang ' : 'Penerimaan Awal Piutang ') . $debt_receivable->transaction_number,
                    'amount' => $paid,
                    'notes' => $debt_receivable->notes,
                    'evidence_paths' => $debt_receivable->evidence_paths ?: [],
                    'status' => 'disetujui',
                    'created_by' => auth()->id(),
                    'submitted_by' => $debt_receivable->submitted_by,
                    'submitted_at' => $debt_receivable->submitted_at,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                DebtReceivablePayment::create([
                    'debt_receivable_id' => $debt_receivable->id,
                    'payment_date' => $debt_receivable->transaction_date,
                    'amount' => $paid,
                    'bank_account_id' => $debt_receivable->bank_account_id,
                    'notes' => $debt_receivable->notes,
                    'evidence_paths' => $debt_receivable->evidence_paths ?: [],
                    'finance_transaction_id' => $finance->id,
                    'created_by' => auth()->id(),
                ]);
            }

            $debt_receivable->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);
        });

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Hutang/piutang disetujui.');
    }

    public function reject(Request $request, DebtReceivable $debt_receivable): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $debt_receivable->update([
            'status' => 'ditolak',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Hutang/piutang ditolak.');
    }

    public function pay(Request $request, DebtReceivable $debt_receivable): RedirectResponse
    {
        if ($debt_receivable->status !== 'disetujui') {
            return back()->with('error', 'Pembayaran hanya bisa dicatat setelah data disetujui.');
        }

        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:' . (float) $debt_receivable->remaining_amount,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ]);

        DB::transaction(function () use ($request, $debt_receivable, $data) {
            $bank = BankAccount::lockForUpdate()->findOrFail($data['bank_account_id']);
            $isDebt = $debt_receivable->type === 'debt';
            if ($isDebt && (float) $bank->balance < (float) $data['amount']) {
                throw ValidationException::withMessages(['amount' => 'Saldo bank tidak mencukupi untuk pembayaran hutang.']);
            }

            $isDebt ? $bank->decrement('balance', $data['amount']) : $bank->increment('balance', $data['amount']);
            $evidence = $this->storeFiles($request, 'evidence', 'debt-receivables/payments');
            $finance = FinanceTransaction::create([
                'transaction_number' => $this->financeTransactionNumber(),
                'transaction_type' => $isDebt ? 'expense' : 'income',
                'transaction_date' => $data['payment_date'],
                'finance_item_id' => $this->financeItem($debt_receivable->type)->id,
                'bank_account_id' => $data['bank_account_id'],
                'activity' => ($isDebt ? 'Pembayaran Hutang ' : 'Penerimaan Piutang ') . $debt_receivable->transaction_number,
                'description' => ($isDebt ? 'Pembayaran Hutang ' : 'Penerimaan Piutang ') . $debt_receivable->transaction_number,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidence,
                'status' => 'disetujui',
                'created_by' => auth()->id(),
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DebtReceivablePayment::create([
                'debt_receivable_id' => $debt_receivable->id,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'bank_account_id' => $data['bank_account_id'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidence,
                'finance_transaction_id' => $finance->id,
                'created_by' => auth()->id(),
            ]);

            $paid = (float) $debt_receivable->paid_amount + (float) $data['amount'];
            $base = (float) $debt_receivable->total_amount;
            $debt_receivable->update([
                'paid_amount' => $paid,
                'remaining_amount' => max(0, $base - $paid),
                'payment_status' => $this->paymentStatus($base, $paid),
            ]);
        });

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Pembayaran berhasil dicatat di mutasi bank.');
    }

    public function updatePayment(Request $request, DebtReceivable $debt_receivable, DebtReceivablePayment $payment): RedirectResponse
    {
        $this->ensurePaymentBelongsToDebt($debt_receivable, $payment);

        if ($debt_receivable->status !== 'disetujui') {
            return back()->with('error', 'Pembayaran hanya bisa diedit setelah data disetujui.');
        }

        $maxAmount = (float) $debt_receivable->remaining_amount + (float) $payment->amount;
        $data = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:' . $maxAmount,
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ]);

        DB::transaction(function () use ($request, $debt_receivable, $payment, $data) {
            $isDebt = $debt_receivable->type === 'debt';
            $this->reversePaymentEffect($payment, $isDebt);
            $bank = BankAccount::lockForUpdate()->findOrFail($data['bank_account_id']);
            $this->applyPaymentEffect($bank, $isDebt, (float) $data['amount']);

            $evidence = $payment->evidence_paths ?: [];
            if ($request->hasFile('evidence')) {
                $evidence = $this->storeFiles($request, 'evidence', 'debt-receivables/payments');
            }

            $finance = $payment->financeTransaction;
            if (! $finance) {
                $finance = new FinanceTransaction(['transaction_number' => $this->financeTransactionNumber()]);
            }

            $finance->fill([
                'transaction_type' => $isDebt ? 'expense' : 'income',
                'transaction_date' => $data['payment_date'],
                'finance_item_id' => $this->financeItem($debt_receivable->type)->id,
                'bank_account_id' => $data['bank_account_id'],
                'activity' => ($isDebt ? 'Pembayaran Hutang ' : 'Penerimaan Piutang ') . $debt_receivable->transaction_number,
                'description' => ($isDebt ? 'Pembayaran Hutang ' : 'Penerimaan Piutang ') . $debt_receivable->transaction_number,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidence,
                'status' => 'disetujui',
                'created_by' => $finance->created_by ?: auth()->id(),
                'submitted_by' => $finance->submitted_by ?: auth()->id(),
                'submitted_at' => $finance->submitted_at ?: now(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ])->save();

            $payment->update([
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'bank_account_id' => $data['bank_account_id'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidence,
                'finance_transaction_id' => $finance->id,
            ]);

            $this->recalculatePaymentStatus($debt_receivable);
        });

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroyPayment(DebtReceivable $debt_receivable, DebtReceivablePayment $payment): RedirectResponse
    {
        $this->ensurePaymentBelongsToDebt($debt_receivable, $payment);

        DB::transaction(function () use ($debt_receivable, $payment) {
            $this->reversePaymentEffect($payment, $debt_receivable->type === 'debt');

            if ($payment->financeTransaction) {
                $payment->financeTransaction->delete();
            }

            $payment->delete();
            $this->recalculatePaymentStatus($debt_receivable);
        });

        return redirect()->route('debt-receivables.show', $debt_receivable)->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function destroy(DebtReceivable $debt_receivable): RedirectResponse
    {
        if ($debt_receivable->payments()->exists()) {
            return back()->with('error', 'Data yang sudah memiliki pembayaran tidak dapat dihapus.');
        }
        $debt_receivable->delete();

        return redirect()->route('debt-receivables.index')->with('success', 'Hutang/piutang berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:debt,receivable',
            'transaction_date' => 'required|date',
            'due_date' => 'required|date',
            'party_name' => 'required|string|max:150',
            'debt_receivable_category_id' => 'required|exists:debt_receivable_categories,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'total_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf,xls,xlsx|max:5120',
        ]);
    }

    private function paymentStatus(float $base, float $paid): string
    {
        if ($paid <= 0) return 'belum_lunas';
        return $paid >= $base ? 'lunas' : 'sebagian';
    }

    private function ensurePaymentBelongsToDebt(DebtReceivable $debtReceivable, DebtReceivablePayment $payment): void
    {
        if ((int) $payment->debt_receivable_id !== (int) $debtReceivable->id) {
            abort(404);
        }
    }

    private function applyPaymentEffect(BankAccount $bank, bool $isDebt, float $amount): void
    {
        if ($isDebt && (float) $bank->balance < $amount) {
            throw ValidationException::withMessages(['amount' => 'Saldo bank tidak mencukupi untuk pembayaran hutang.']);
        }

        $isDebt ? $bank->decrement('balance', $amount) : $bank->increment('balance', $amount);
    }

    private function reversePaymentEffect(DebtReceivablePayment $payment, bool $isDebt): void
    {
        $bank = BankAccount::lockForUpdate()->findOrFail($payment->bank_account_id);
        $amount = (float) $payment->amount;

        if (! $isDebt && (float) $bank->balance < $amount) {
            throw ValidationException::withMessages(['amount' => 'Saldo bank tidak mencukupi untuk membatalkan penerimaan piutang.']);
        }

        $isDebt ? $bank->increment('balance', $amount) : $bank->decrement('balance', $amount);
    }

    private function recalculatePaymentStatus(DebtReceivable $debtReceivable): void
    {
        $paid = (float) $debtReceivable->payments()->sum('amount');
        $base = (float) $debtReceivable->total_amount;

        $debtReceivable->update([
            'paid_amount' => $paid,
            'remaining_amount' => max(0, $base - $paid),
            'payment_status' => $this->paymentStatus($base, $paid),
        ]);
    }

    private function storeFiles(Request $request, string $input, string $dir): array
    {
        return collect($request->file($input, []))->filter()->map(fn ($file) => $file->store($dir, 'r2'))->values()->all();
    }

    private function fileObjects(array $paths)
    {
        return collect($paths)->filter()->map(fn ($path) => (object) [
            'path' => $path,
            'url' => Storage::disk('r2')->url($path),
            'name' => basename($path),
            'is_image' => in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true),
        ]);
    }

    private function financeItem(string $type): FinanceItem
    {
        $isDebt = $type === 'debt';
        $code = $isDebt ? 'AUTO-DEBT' : 'AUTO-RECEIVABLE';
        $name = $isDebt ? 'Pembayaran Hutang' : 'Penerimaan Piutang';
        $category = FinanceCategory::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => $isDebt ? 'expense' : 'income', 'description' => 'Transaksi otomatis ' . strtolower($name)]
        );

        return FinanceItem::firstOrCreate(
            ['code' => $code],
            ['finance_category_id' => $category->id, 'name' => $name, 'description' => 'Transaksi otomatis ' . strtolower($name), 'is_active' => true]
        );
    }

    private function initialFinanceItem(string $type): FinanceItem
    {
        $isDebt = $type === 'debt';
        $code = $isDebt ? 'AUTO-DEBT-IN' : 'AUTO-RECEIVABLE-OUT';
        $name = $isDebt ? 'Penerimaan Hutang' : 'Pencairan Piutang';
        $category = FinanceCategory::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => $isDebt ? 'income' : 'expense', 'description' => 'Transaksi otomatis ' . strtolower($name)]
        );

        return FinanceItem::firstOrCreate(
            ['code' => $code],
            ['finance_category_id' => $category->id, 'name' => $name, 'description' => 'Transaksi otomatis ' . strtolower($name), 'is_active' => true]
        );
    }

    private function transactionNumber(): string
    {
        $prefix = 'HP-' . now()->format('y') . '-';
        $last = DebtReceivable::where('transaction_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('transaction_number')->value('transaction_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }

    private function financeTransactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $last = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('transaction_number')->value('transaction_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }
}
