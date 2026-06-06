<?php

namespace App\Http\Controllers\Operasional;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use App\Models\Material;
use App\Models\MaterialPurchase;
use App\Models\MaterialStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MaterialPurchaseController extends Controller
{
    public function index(): View
    {
        $transactions = MaterialPurchase::with(['material.category', 'material.stock'])
            ->latest('purchase_date')
            ->latest()
            ->get()
            ->groupBy('invoice_number')
            ->map(function ($items, $number) {
                $first = $items->first();
                $units = $items->pluck('unit')->filter()->unique();
                $firstMaterial = $first->material?->name ?? '-';

                return (object) [
                    'invoice_number' => $number,
                    'purchase_date' => $first->purchase_date,
                    'supplier' => $first->supplier,
                    'material_summary' => $items->count() > 1
                        ? $firstMaterial . ' +' . ($items->count() - 1) . ' item'
                        : $firstMaterial,
                    'qty_summary' => $units->count() === 1
                        ? $items->sum('qty') . ' ' . $units->first()
                        : $items->count() . ' item',
                    'total_price' => $items->sum('total_price'),
                    'item_count' => $items->count(),
                    'status' => $first->status,
                    'last_id' => $items->max('id'),
                ];
            })
            ->sortByDesc('last_id')
            ->values();

        return view('operasional.material-purchases.index', compact('transactions'));
    }

    public function create(): View
    {
        $materialOptions = $this->materialOptions();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('operasional.material-purchases.create', compact('materialOptions', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier' => 'nullable|string|max:150',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'material_id' => 'required|array|min:1',
            'material_id.*' => 'required|exists:materials,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
            'unit' => 'required|array|min:1',
            'unit.*' => 'required|string|max:30',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ], [
            'material_id.required' => 'Material wajib dipilih.',
            'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
            'qty.required' => 'Jumlah pembelian wajib diisi.',
            'unit_price.required' => 'Harga satuan wajib diisi.',
        ]);

        DB::transaction(function () use ($request) {
            $transactionNumber = $this->generateTransactionNumber($request->purchase_date);
            $evidencePaths = $this->storeEvidences($request);
            $evidencePath = $evidencePaths[0] ?? null;

            foreach ($request->material_id as $index => $materialId) {
                $qty = (int) $request->qty[$index];
                $unitPrice = (float) $request->unit_price[$index];
                $totalPrice = $qty * $unitPrice;

                MaterialPurchase::create([
                    'material_id' => $materialId,
                    'invoice_number' => $transactionNumber,
                    'supplier' => $request->supplier,
                    'purchase_date' => $request->purchase_date,
                    'qty' => $qty,
                    'unit' => $request->unit[$index],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'notes' => $request->notes,
                    'evidence_path' => $evidencePath,
                    'evidence_paths' => $evidencePaths,
                    'status' => 'menunggu_approval',
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                    'bank_account_id' => $request->bank_account_id,
                ]);
            }

            $this->notifyPurchaseApprovers($transactionNumber, $request->supplier);
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('material-purchases.index')
            ->with('success', 'Pembelian material berhasil disimpan.');
    }

    public function edit(string $transaction): View
    {
        $items = MaterialPurchase::with(['material.category', 'material.stock', 'submitter', 'approver', 'rejecter'])
            ->where('invoice_number', $transaction)
            ->orderBy('id')
            ->get();

        abort_if($items->isEmpty(), 404);

        $first = $items->first();
        $evidenceFiles = $this->evidenceFiles($first);
        $summary = (object) [
            'invoice_number' => $transaction,
            'purchase_date' => $first->purchase_date,
            'supplier' => $first->supplier,
            'notes' => $first->notes,
            'bank_account_id' => $first->bank_account_id,
            'evidence_url' => $first->evidence_path ? Storage::disk('r2')->url($first->evidence_path) : null,
            'evidence_files' => $evidenceFiles,
        ];
        $initialItems = $items->map(fn ($item) => [
            'material_id' => $item->material_id,
            'qty' => $item->qty,
            'unit' => $item->unit,
            'unit_price' => (int) $item->unit_price,
        ])->values();
        $materialOptions = $this->materialOptions();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('operasional.material-purchases.edit', compact('materialOptions', 'summary', 'initialItems', 'bankAccounts'));
    }

    public function update(Request $request, string $transaction)
    {
        $request->validate([
            'supplier' => 'nullable|string|max:150',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'material_id' => 'required|array|min:1',
            'material_id.*' => 'required|exists:materials,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
            'unit' => 'required|array|min:1',
            'unit.*' => 'required|string|max:30',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
        ]);

        $items = MaterialPurchase::where('invoice_number', $transaction)->get();
        abort_if($items->isEmpty(), 404);

        DB::transaction(function () use ($request, $transaction, $items) {
            $first = $items->first();
            $wasApproved = $first->status === 'disetujui';
            $status = $wasApproved ? 'disetujui' : 'menunggu_approval';
            $evidencePaths = $this->evidencePathsFromPurchase($first);

            if ($request->hasFile('evidence')) {
                $evidencePaths = $this->storeEvidences($request);
            }
            $evidencePath = $evidencePaths[0] ?? null;

            if ($wasApproved) {
                $this->applyStockMutation($items, -1);
                $this->reverseFinanceTransaction($first->finance_transaction_id);
            }

            MaterialPurchase::where('invoice_number', $transaction)->delete();

            $newItems = collect();
            foreach ($request->material_id as $index => $materialId) {
                $qty = (int) $request->qty[$index];
                $unitPrice = (float) $request->unit_price[$index];

                $newItems->push(MaterialPurchase::create([
                    'material_id' => $materialId,
                    'invoice_number' => $transaction,
                    'supplier' => $request->supplier,
                    'purchase_date' => $request->purchase_date,
                    'qty' => $qty,
                    'unit' => $request->unit[$index],
                    'unit_price' => $unitPrice,
                    'total_price' => $qty * $unitPrice,
                    'notes' => $request->notes,
                    'evidence_path' => $evidencePath,
                    'evidence_paths' => $evidencePaths,
                    'status' => $status,
                    'submitted_by' => $first->submitted_by,
                    'submitted_at' => $first->submitted_at,
                    'approved_by' => $wasApproved ? $first->approved_by : null,
                    'approved_at' => $wasApproved ? $first->approved_at : null,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                    'bank_account_id' => $request->bank_account_id,
                ]));
            }

            if ($wasApproved) {
                $this->applyStockMutation($newItems, 1);
                $this->createApprovedFinanceTransactionForPurchase($transaction, $newItems, $request->bank_account_id);
            }
        });

        return redirect()->route('material-purchases.show', $transaction)->with('success', 'Pembelian material berhasil diperbarui.');
    }

    public function show(string $transaction): View
    {
        $items = MaterialPurchase::with(['material.category', 'material.stock', 'bankAccount'])
            ->where('invoice_number', $transaction)
            ->orderBy('id')
            ->get();

        abort_if($items->isEmpty(), 404);

        $first = $items->first();
        $evidenceFiles = $this->evidenceFiles($first);
        $summary = (object) [
            'invoice_number' => $transaction,
            'purchase_date' => $first->purchase_date,
            'supplier' => $first->supplier,
            'notes' => $first->notes,
            'evidence_path' => $first->evidence_path,
            'evidence_url' => $first->evidence_path ? Storage::disk('r2')->url($first->evidence_path) : null,
            'evidence_is_image' => $this->isImageEvidence($first->evidence_path),
            'evidence_files' => $evidenceFiles,
            'bank_account_id' => $first->bank_account_id,
            'bank_code' => $first->bankAccount?->code,
            'bank_name' => $first->bankAccount?->bank_name,
            'bank_account_name' => $first->bankAccount?->account_name,
            'bank_account_number' => $first->bankAccount?->account_number,
            'bank_logo_url' => $first->bankAccount?->logo_url,
            'bank_logo_text' => $first->bankAccount?->logo_text,
            'total_price' => $items->sum('total_price'),
            'item_count' => $items->count(),
            'status' => $first->status,
            'rejection_reason' => $first->rejection_reason,
            'submitter_name' => $first->submitter?->name,
            'submitted_at' => $first->submitted_at,
            'processor_name' => $first->approver?->name ?? $first->rejecter?->name,
            'processed_at' => $first->approved_at ?: $first->rejected_at,
        ];

        return view('operasional.material-purchases.show', compact('summary', 'items'));
    }

    public function accept(string $transaction)
    {
        $items = MaterialPurchase::where('invoice_number', $transaction)->get();
        abort_if($items->isEmpty(), 404);

        if ($items->first()->status !== 'menunggu_approval') {
            return back()->with('error', 'Pembelian ini sudah diproses.');
        }

        DB::transaction(function () use ($items, $transaction) {
            foreach ($items as $item) {
                $stock = MaterialStock::firstOrCreate(
                    ['material_id' => $item->material_id],
                    ['qty' => 0, 'updated_at' => now()]
                );
                $stock->update([
                    'qty' => $stock->qty + $item->qty,
                    'updated_at' => now(),
                ]);

                Material::whereKey($item->material_id)->update([
                    'cost_price' => $item->unit_price,
                ]);
            }

            MaterialPurchase::where('invoice_number', $transaction)->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            $this->createApprovedFinanceTransactionForPurchase($transaction, $items, $items->first()->bank_account_id);
            $this->notifySubmitter($items->first(), 'Pembelian Material Disetujui', 'Pembelian ' . $transaction . ' sudah disetujui.', 'check-circle');
        });

        return redirect()->route('material-purchases.show', $transaction)->with('success', 'Pembelian material disetujui.');
    }

    public function reject(Request $request, string $transaction)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $items = MaterialPurchase::where('invoice_number', $transaction)->get();
        abort_if($items->isEmpty(), 404);

        if ($items->first()->status !== 'menunggu_approval') {
            return back()->with('error', 'Pembelian ini sudah diproses.');
        }

        MaterialPurchase::where('invoice_number', $transaction)->update([
            'status' => 'ditolak',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->notifySubmitter($items->first(), 'Pembelian Material Ditolak', 'Pembelian ' . $transaction . ' ditolak: ' . $request->rejection_reason, 'cross-circle');

        return redirect()->route('material-purchases.show', $transaction)->with('success', 'Pembelian material ditolak.');
    }

    public function destroy(string $transaction): JsonResponse
    {
        $items = MaterialPurchase::where('invoice_number', $transaction)->get();
        abort_if($items->isEmpty(), 404);

        DB::transaction(function () use ($items, $transaction) {
            if ($items->first()->status === 'disetujui') {
                foreach ($items as $item) {
                    $stock = MaterialStock::firstOrCreate(
                        ['material_id' => $item->material_id],
                        ['qty' => 0, 'updated_at' => now()]
                    );
                    $stock->update([
                        'qty' => max(0, $stock->qty - $item->qty),
                        'updated_at' => now(),
                    ]);
                }
            }

            MaterialPurchase::where('invoice_number', $transaction)->delete();
        });

        return response()->json(['success' => true]);
    }

    private function materialOptions()
    {
        return Material::with(['category', 'stock'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($material) => [
                'id' => $material->id,
                'category_id' => $material->material_category_id,
                'name' => $material->name,
                'stock_qty' => $material->stock_qty,
                'cost_price' => (int) ($material->cost_price ?? 0),
            ])
            ->values();
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
            return $file->store('material-purchases', 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store('material-purchases', 'r2');
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

        $path = 'material-purchases/' . Str::uuid() . '.webp';
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

    private function evidencePathsFromPurchase(MaterialPurchase $purchase): array
    {
        $paths = $purchase->evidence_paths ?: [];
        if (!$paths && $purchase->evidence_path) {
            $paths = [$purchase->evidence_path];
        }

        return collect($paths)->filter()->values()->all();
    }

    private function evidenceFiles(MaterialPurchase $purchase)
    {
        return collect($this->evidencePathsFromPurchase($purchase))
            ->map(fn ($path) => (object) [
                'path' => $path,
                'url' => Storage::disk('r2')->url($path),
                'is_image' => $this->isImageEvidence($path),
                'name' => basename($path),
            ])
            ->values();
    }

    private function applyStockMutation($items, int $direction): void
    {
        foreach ($items as $item) {
            $stock = MaterialStock::firstOrCreate(
                ['material_id' => $item->material_id],
                ['qty' => 0, 'updated_at' => now()]
            );
            $stock->update([
                'qty' => max(0, $stock->qty + ($direction * $item->qty)),
                'updated_at' => now(),
            ]);

            if ($direction > 0) {
                Material::whereKey($item->material_id)->update([
                    'cost_price' => $item->unit_price,
                ]);
            }
        }
    }

    private function generateTransactionNumber(string $purchaseDate): string
    {
        $year = date('y', strtotime($purchaseDate));
        $prefix = 'PSC-' . $year . '-';
        $lastNumber = MaterialPurchase::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');
        $nextSequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    private function createApprovedFinanceTransactionForPurchase(string $transaction, $items, int|string|null $bankAccountId): void
    {
        if (!$bankAccountId) {
            throw ValidationException::withMessages(['bank_account_id' => 'Bank pembayaran wajib dipilih.']);
        }

        $amount = (float) $items->sum('total_price');
        $bank = BankAccount::lockForUpdate()->findOrFail($bankAccountId);

        $first = $items->first();
        $item = $this->financeItem('AUTO-MATERIAL', 'Pembelian Material', 'Pembelian material operasional');
        $financeTransaction = FinanceTransaction::create([
            'transaction_number' => $this->financeTransactionNumber(),
            'transaction_type' => 'expense',
            'transaction_date' => $first->purchase_date,
            'finance_item_id' => $item->id,
            'bank_account_id' => $bankAccountId,
            'activity' => 'Pembelian Material ' . $transaction,
            'description' => 'Pembelian Material ' . $transaction,
            'amount' => $amount,
            'notes' => $first->supplier ? 'Supplier: ' . $first->supplier : null,
            'evidence_paths' => $first->evidence_paths,
            'status' => 'disetujui',
            'created_by' => auth()->id(),
            'submitted_by' => $first->submitted_by,
            'submitted_at' => $first->submitted_at,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $bank->decrement('balance', $amount);
        MaterialPurchase::where('invoice_number', $transaction)->update(['finance_transaction_id' => $financeTransaction->id]);
    }

    private function reverseFinanceTransaction(?int $financeTransactionId): void
    {
        if (!$financeTransactionId) {
            return;
        }

        $transaction = FinanceTransaction::find($financeTransactionId);
        if (!$transaction || $transaction->status !== 'disetujui') {
            return;
        }

        $bank = BankAccount::lockForUpdate()->findOrFail($transaction->bank_account_id);
        $transaction->transaction_type === 'income'
            ? $bank->decrement('balance', $transaction->amount)
            : $bank->increment('balance', $transaction->amount);
        $transaction->delete();
    }

    private function financeItem(string $code, string $name, string $description): FinanceItem
    {
        $category = FinanceCategory::firstOrCreate(
            ['code' => 'AUTO-OPS'],
            ['name' => 'Operasional Otomatis', 'type' => 'expense', 'description' => 'Kategori otomatis dari modul operasional.']
        );

        return FinanceItem::firstOrCreate(
            ['code' => $code],
            ['finance_category_id' => $category->id, 'name' => $name, 'description' => $description, 'is_active' => true]
        );
    }

    private function financeTransactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $lastNumber = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $nextNumber = $lastNumber ? ((int) Str::afterLast($lastNumber, '-') + 1) : 1;

        return $prefix . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function notifyPurchaseApprovers(string $transactionNumber, ?string $supplier): void
    {
        $users = User::permission('purchases.approve')->where('status', 'aktif')->get();
        $message = 'Pembelian ' . $transactionNumber . ' menunggu approval' . ($supplier ? ' dari ' . $supplier : '') . '.';

        $this->insertNotifications($users, [
            'title' => 'Approval Pembelian Material',
            'message' => $message,
            'url' => route('material-purchases.show', $transactionNumber),
            'icon' => 'purchase',
        ]);
    }

    private function notifySubmitter(MaterialPurchase $purchase, string $title, string $message, string $icon): void
    {
        if (!$purchase->submitted_by) {
            return;
        }

        $user = User::find($purchase->submitted_by);
        if (!$user) {
            return;
        }

        $this->insertNotifications(collect([$user]), [
            'title' => $title,
            'message' => $message,
            'url' => route('material-purchases.show', $purchase->invoice_number),
            'icon' => $icon,
        ]);
    }

    private function insertNotifications($users, array $data): void
    {
        $now = now();
        $rows = $users->map(fn ($user) => [
            'id' => (string) Str::uuid(),
            'type' => 'material_purchase',
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
