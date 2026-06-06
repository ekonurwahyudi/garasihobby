<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\DebtReceivable;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FinanceTransactionController extends Controller
{
    public function index(): View
    {
        $data = FinanceTransaction::with(['item.category', 'bankAccount'])
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        return view('finance.transactions.index', compact('data'));
    }

    public function create(): View
    {
        return view('finance.transactions.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $evidencePaths = $this->storeEvidences($request);

        $transaction = DB::transaction(function () use ($data, $evidencePaths) {
            $item = FinanceItem::findOrFail($data['finance_item_id']);
            $this->assertItemMatches($item, $data['finance_category_id']);

            return FinanceTransaction::create([
                'transaction_number' => $this->transactionNumber(),
                'transaction_type' => $data['transaction_type'],
                'transaction_date' => $data['transaction_date'],
                'finance_item_id' => $data['finance_item_id'],
                'bank_account_id' => $data['bank_account_id'],
                'activity' => $data['activity'],
                'description' => $data['activity'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidencePaths,
                'status' => 'menunggu_approval',
                'created_by' => auth()->id(),
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
            ]);
        });

        $this->notifyFinanceApprovers($transaction);

        return redirect()->route('finance-transactions.index')->with('success', 'Transaksi keuangan berhasil diajukan dan menunggu approval.');
    }

    public function show(FinanceTransaction $finance_transaction): View
    {
        $finance_transaction->load(['item.category', 'bankAccount', 'submitter', 'approver', 'rejecter']);
        $evidenceFiles = $this->evidenceFiles($finance_transaction);
        $relatedOrder = Order::where('finance_transaction_id', $finance_transaction->id)->first();
        $relatedDebtReceivable = $this->relatedDebtReceivable($finance_transaction);

        return view('finance.transactions.show', compact('finance_transaction', 'evidenceFiles', 'relatedOrder', 'relatedDebtReceivable'));
    }

    public function edit(FinanceTransaction $finance_transaction): View
    {
        $finance_transaction->load(['item.category', 'bankAccount']);
        $evidenceFiles = $this->evidenceFiles($finance_transaction);

        return view('finance.transactions.edit', $this->formData() + compact('finance_transaction', 'evidenceFiles'));
    }

    public function update(Request $request, FinanceTransaction $finance_transaction): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data, $finance_transaction) {
            if ($finance_transaction->status === 'disetujui') {
                $oldBank = BankAccount::lockForUpdate()->findOrFail($finance_transaction->bank_account_id);
                $this->applyDelta($oldBank, -$this->delta($finance_transaction->transaction_type, (float) $finance_transaction->amount));
            }

            $item = FinanceItem::findOrFail($data['finance_item_id']);
            $this->assertItemMatches($item, $data['finance_category_id']);

            $evidencePaths = $finance_transaction->evidence_paths ?: [];
            if ($request->hasFile('evidence')) {
                $evidencePaths = $this->storeEvidences($request);
            }

            $finance_transaction->update([
                'transaction_type' => $data['transaction_type'],
                'transaction_date' => $data['transaction_date'],
                'finance_item_id' => $data['finance_item_id'],
                'bank_account_id' => $data['bank_account_id'],
                'activity' => $data['activity'],
                'description' => $data['activity'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'evidence_paths' => $evidencePaths,
                'status' => 'menunggu_approval',
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);
        });

        $this->notifyFinanceApprovers($finance_transaction->refresh());

        return redirect()->route('finance-transactions.index')->with('success', 'Transaksi keuangan berhasil diperbarui dan diajukan ulang untuk approval.');
    }

    public function approve(FinanceTransaction $finance_transaction): RedirectResponse
    {
        if ($finance_transaction->status !== 'menunggu_approval') {
            return back()->with('error', 'Transaksi ini sudah diproses.');
        }

        DB::transaction(function () use ($finance_transaction) {
            $bank = BankAccount::lockForUpdate()->findOrFail($finance_transaction->bank_account_id);
            $this->applyDelta($bank, $this->delta($finance_transaction->transaction_type, (float) $finance_transaction->amount));

            $finance_transaction->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);
        });

        $this->notifySubmitter($finance_transaction->refresh(), 'Input Keuangan Disetujui', 'Transaksi ' . $finance_transaction->transaction_number . ' sudah disetujui.', 'check-circle');

        return redirect()->route('finance-transactions.show', $finance_transaction)->with('success', 'Transaksi keuangan disetujui dan saldo bank sudah diperbarui.');
    }

    public function reject(Request $request, FinanceTransaction $finance_transaction): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($finance_transaction->status !== 'menunggu_approval') {
            return back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $finance_transaction->update([
            'status' => 'ditolak',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->notifySubmitter($finance_transaction, 'Input Keuangan Ditolak', 'Transaksi ' . $finance_transaction->transaction_number . ' ditolak: ' . $request->rejection_reason, 'cross-circle');

        return redirect()->route('finance-transactions.show', $finance_transaction)->with('success', 'Transaksi keuangan ditolak.');
    }

    public function destroy(FinanceTransaction $finance_transaction): JsonResponse
    {
        DB::transaction(function () use ($finance_transaction) {
            if ($finance_transaction->status === 'disetujui') {
                $bank = BankAccount::lockForUpdate()->findOrFail($finance_transaction->bank_account_id);
                $this->applyDelta($bank, -$this->delta($finance_transaction->transaction_type, (float) $finance_transaction->amount));
            }

            $finance_transaction->delete();
        });

        return response()->json(['success' => true]);
    }

    private function formData(): array
    {
        return [
            'categories' => FinanceCategory::orderBy('name')->get(),
            'items' => FinanceItem::with('category')->where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'transaction_type' => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'activity' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'finance_item_id' => 'required|exists:finance_items,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'notes' => 'nullable|string',
        ]);
    }

    private function assertItemMatches(FinanceItem $item, int|string $categoryId): void
    {
        if ((int) $item->finance_category_id !== (int) $categoryId) {
            throw ValidationException::withMessages([
                'finance_item_id' => 'Item transaksi tidak sesuai dengan kategori transaksi yang dipilih.',
            ]);
        }
    }

    private function delta(string $transactionType, float $amount): float
    {
        return $transactionType === 'income' ? $amount : -$amount;
    }

    private function applyDelta(BankAccount $bank, float $delta): void
    {
        $bank->update(['balance' => (float) $bank->balance + $delta]);
    }

    private function transactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $lastNumber = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $nextNumber = $lastNumber ? ((int) Str::afterLast($lastNumber, '-') + 1) : 1;

        return $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function storeEvidences(Request $request): array
    {
        $files = $request->file('evidence', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        return collect($files)
            ->filter()
            ->map(fn (UploadedFile $file) => $this->storeEvidence($file))
            ->values()
            ->all();
    }

    private function storeEvidence(UploadedFile $file): string
    {
        if (!Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store('finance-transactions', 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store('finance-transactions', 'r2');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagewebp($canvas, null, 82);
        $contents = ob_get_clean();

        imagedestroy($image);
        imagedestroy($canvas);

        $path = 'finance-transactions/' . Str::uuid() . '.webp';
        Storage::disk('r2')->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);

        return $path;
    }

    private function isImageEvidence(?string $path): bool
    {
        return $path && in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    private function evidenceFiles(FinanceTransaction $transaction)
    {
        return collect($transaction->evidence_paths ?: [])
            ->filter()
            ->map(fn ($path) => (object) [
                'path' => $path,
                'url' => Storage::disk('r2')->url($path),
                'is_image' => $this->isImageEvidence($path),
                'name' => basename($path),
            ])
            ->values();
    }

    private function relatedDebtReceivable(FinanceTransaction $transaction): ?DebtReceivable
    {
        $fromPayment = DebtReceivable::whereHas('payments', fn ($query) => $query->where('finance_transaction_id', $transaction->id))->first();
        if ($fromPayment) {
            return $fromPayment;
        }

        $text = trim(($transaction->activity ?? '') . ' ' . ($transaction->description ?? ''));
        if (preg_match('/HP-\d{2}-\d{5}/', $text, $match)) {
            return DebtReceivable::where('transaction_number', $match[0])->first();
        }

        return null;
    }

    private function notifyFinanceApprovers(FinanceTransaction $transaction): void
    {
        $users = User::permission('finance-transactions.approve')->where('status', 'aktif')->get();
        $message = 'Transaksi ' . $transaction->transaction_number . ' menunggu approval.';

        $this->insertNotifications($users, [
            'title' => 'Approval Input Keuangan',
            'message' => $message,
            'url' => route('finance-transactions.show', $transaction),
            'icon' => 'wallet',
        ]);
    }

    private function notifySubmitter(FinanceTransaction $transaction, string $title, string $message, string $icon): void
    {
        if (!$transaction->submitted_by) {
            return;
        }

        $user = User::find($transaction->submitted_by);
        if (!$user) {
            return;
        }

        $this->insertNotifications(collect([$user]), [
            'title' => $title,
            'message' => $message,
            'url' => route('finance-transactions.show', $transaction),
            'icon' => $icon,
        ]);
    }

    private function insertNotifications($users, array $data): void
    {
        $now = now();
        $rows = $users->map(fn ($user) => [
            'id' => (string) Str::uuid(),
            'type' => 'finance_transaction',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode($data),
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows) {
            DB::table('notifications')->insert($rows);
        }
    }
}
