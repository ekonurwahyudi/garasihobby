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
use Carbon\Carbon;
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
            ->latest('created_at')
            ->latest('id')
            ->get();
        $importItems = FinanceItem::with('category')->where('is_active', true)->orderBy('code')->get();
        $importBankAccounts = BankAccount::where('is_active', true)->orderBy('code')->get();

        return view('finance.transactions.index', compact('data', 'importItems', 'importBankAccounts'));
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

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:4096',
        ], [
            'import_file.required' => 'File import wajib dipilih.',
            'import_file.mimes' => 'Gunakan file CSV. Jika memakai Excel, simpan file sebagai CSV terlebih dahulu.',
        ]);

        [$rows, $errors] = $this->readImportRows($request->file('import_file'));
        if ($errors) {
            return back()->with('error', implode(' ', array_slice($errors, 0, 5)));
        }

        if (empty($rows)) {
            return back()->with('error', 'File import tidak memiliki data transaksi.');
        }

        $createdTransactions = collect();
        $errors = [];

        try {
            DB::transaction(function () use ($rows, &$errors, $createdTransactions) {
                foreach ($rows as $rowNumber => $row) {
                    try {
                        $transactionType = $this->normalizeImportType($row['transaction_type'] ?? '');
                        $date = $this->parseImportDate($row['transaction_date'] ?? '');
                        $amount = $this->parseImportAmount($row['amount'] ?? '');
                        $activity = trim((string) ($row['activity'] ?? ''));
                        $itemCode = trim((string) ($row['finance_item_code'] ?? ''));
                        $bankCode = trim((string) ($row['bank_account_code'] ?? ''));

                        if (!$transactionType) {
                            throw ValidationException::withMessages(['transaction_type' => 'Jenis transaksi harus income/expense.']);
                        }
                        if (!$date) {
                            throw ValidationException::withMessages(['transaction_date' => 'Tanggal transaksi tidak valid.']);
                        }
                        if ($activity === '') {
                            throw ValidationException::withMessages(['activity' => 'Aktivitas wajib diisi.']);
                        }
                        if ($amount <= 0) {
                            throw ValidationException::withMessages(['amount' => 'Nominal wajib lebih dari 0.']);
                        }
                        if ($itemCode === '') {
                            throw ValidationException::withMessages(['finance_item_code' => 'Kode item wajib diisi.']);
                        }
                        if ($bankCode === '') {
                            throw ValidationException::withMessages(['bank_account_code' => 'Kode rekening wajib diisi.']);
                        }

                        $item = FinanceItem::with('category')
                            ->whereRaw('LOWER(code) = ?', [Str::lower($itemCode)])
                            ->where('is_active', true)
                            ->first();
                        if (!$item) {
                            throw ValidationException::withMessages(['finance_item_code' => 'Kode item "' . $itemCode . '" tidak ditemukan atau tidak aktif.']);
                        }
                        if ($item->category?->type !== $transactionType) {
                            throw ValidationException::withMessages(['transaction_type' => 'Jenis transaksi tidak sesuai kategori item "' . $item->name . '".']);
                        }

                        $bank = BankAccount::whereRaw('LOWER(code) = ?', [Str::lower($bankCode)])
                            ->where('is_active', true)
                            ->first();
                        if (!$bank) {
                            throw ValidationException::withMessages(['bank_account_code' => 'Kode rekening "' . $bankCode . '" tidak ditemukan atau tidak aktif.']);
                        }

                        $createdTransactions->push(FinanceTransaction::create([
                            'transaction_number' => $this->transactionNumber(),
                            'transaction_type' => $transactionType,
                            'transaction_date' => $date->format('Y-m-d'),
                            'finance_item_id' => $item->id,
                            'bank_account_id' => $bank->id,
                            'activity' => $activity,
                            'description' => $activity,
                            'amount' => $amount,
                            'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
                            'evidence_paths' => [],
                            'status' => 'menunggu_approval',
                            'created_by' => auth()->id(),
                            'submitted_by' => auth()->id(),
                            'submitted_at' => now(),
                        ]));
                    } catch (ValidationException $exception) {
                        $message = collect($exception->errors())->flatten()->first();
                        $errors[] = 'Baris ' . $rowNumber . ': ' . $message;
                    }
                }

                if ($errors) {
                    throw ValidationException::withMessages(['import_file' => implode(' ', array_slice($errors, 0, 5))]);
                }
            });
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->implode(' '));
        }

        $createdTransactions->each(fn (FinanceTransaction $transaction) => $this->notifyFinanceApprovers($transaction));

        return redirect()
            ->route('finance-transactions.index')
            ->with('success', $createdTransactions->count() . ' transaksi berhasil diimport. Nomor transaksi dibuat otomatis dan status diset Awaiting.');
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

    private function readImportRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return [[], ['File import tidak bisa dibaca.']];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [[], []];
        }

        $delimiter = $this->detectCsvDelimiter($firstLine);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            return [[], []];
        }

        $headers = array_map(fn ($header) => Str::of(trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"))->lower()->replace(' ', '_')->toString(), $headers);
        $requiredHeaders = ['transaction_type', 'transaction_date', 'activity', 'amount', 'finance_item_code', 'bank_account_code'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if ($missingHeaders) {
            fclose($handle);
            return [[], ['Header wajib belum ada: ' . implode(', ', $missingHeaders) . '.']];
        }

        $rows = [];
        $rowNumber = 1;
        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $values[$index] ?? null;
            }
            $rows[$rowNumber] = $row;
        }
        fclose($handle);

        return [$rows, []];
    }

    private function detectCsvDelimiter(string $line): string
    {
        return collect([',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")])
            ->sortDesc()
            ->keys()
            ->first() ?: ',';
    }

    private function normalizeImportType(string $value): ?string
    {
        $value = Str::of($value)->lower()->trim()->replace([' ', '-'], '_')->toString();

        return match ($value) {
            'income', 'uang_masuk', 'masuk', 'pemasukan' => 'income',
            'expense', 'uang_keluar', 'keluar', 'pengeluaran' => 'expense',
            default => null,
        };
    }

    private function parseImportDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->startOfDay();
            } catch (\Throwable) {
                //
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseImportAmount(?string $value): float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        $value = str_replace(['Rp', 'rp', ' ', ',00'], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
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
