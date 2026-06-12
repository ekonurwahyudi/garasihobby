<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\AssetPurchase;
use App\Models\BankAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetPurchaseController extends Controller
{
    public function index(): View
    {
        $data = AssetPurchase::with(['bankAccount', 'submitter', 'approver', 'rejecter'])
            ->orderByDesc('asset_number')
            ->latest('id')
            ->get();

        return view('finance.asset-purchases.index', compact('data'));
    }

    public function create(): View
    {
        return view('finance.asset-purchases.create', [
            'assetPurchase' => null,
            'assetCategories' => AssetCategory::orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['asset_category'] = AssetCategory::find($data['asset_category_id'])?->name;
        $data = $this->normalizeDepreciationData($data);
        $data['asset_number'] = $this->assetNumber();
        $data['book_value'] = $this->bookValue($data);
        $data['asset_photo_paths'] = $this->storeFiles($request, 'asset_photos', 'asset-purchases/photos');
        $data['evidence_paths'] = $this->storeFiles($request, 'evidence', 'asset-purchases/evidence');
        $data['status'] = 'menunggu_approval';
        $data['submitted_by'] = auth()->id();
        $data['submitted_at'] = now();

        AssetPurchase::create($data);

        return redirect()->route('asset-purchases.index')->with('success', 'Pembelian aset berhasil diajukan dan menunggu approval.');
    }

    public function show(AssetPurchase $asset_purchase): View
    {
        $asset_purchase->load(['bankAccount', 'submitter', 'approver', 'rejecter', 'financeTransaction']);

        return view('finance.asset-purchases.show', [
            'assetPurchase' => $asset_purchase,
            'photoFiles' => $this->fileObjects($asset_purchase->asset_photo_paths ?: []),
            'evidenceFiles' => $this->fileObjects($asset_purchase->evidence_paths ?: []),
        ]);
    }

    public function edit(AssetPurchase $asset_purchase): View
    {
        if ($asset_purchase->status === 'disetujui' && ! $this->canManageApproved()) {
            return redirect()->route('asset-purchases.show', $asset_purchase)->with('error', 'Pembelian aset yang sudah disetujui tidak dapat diedit.');
        }

        return view('finance.asset-purchases.edit', [
            'assetPurchase' => $asset_purchase,
            'assetCategories' => AssetCategory::orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ]);
    }

    public function update(Request $request, AssetPurchase $asset_purchase): RedirectResponse
    {
        if ($asset_purchase->status === 'disetujui' && ! $this->canManageApproved()) {
            return redirect()->route('asset-purchases.show', $asset_purchase)->with('error', 'Pembelian aset yang sudah disetujui tidak dapat diedit.');
        }

        $data = $this->validatedData($request);
        $data['asset_category'] = AssetCategory::find($data['asset_category_id'])?->name;
        $data = $this->normalizeDepreciationData($data);
        $data['book_value'] = $this->bookValue($data);
        $data['status'] = 'menunggu_approval';
        $data['approved_by'] = null;
        $data['approved_at'] = null;
        $data['rejected_by'] = null;
        $data['rejected_at'] = null;
        $data['rejection_reason'] = null;

        if ($request->hasFile('asset_photos')) {
            $data['asset_photo_paths'] = $this->storeFiles($request, 'asset_photos', 'asset-purchases/photos');
        }
        if ($request->hasFile('evidence')) {
            $data['evidence_paths'] = $this->storeFiles($request, 'evidence', 'asset-purchases/evidence');
        }

        DB::transaction(function () use ($asset_purchase, $data) {
            if ($asset_purchase->status === 'disetujui') {
                $this->reverseFinanceTransaction($asset_purchase->finance_transaction_id);
                $data['finance_transaction_id'] = null;
            }

            $asset_purchase->update($data);
        });

        return redirect()->route('asset-purchases.show', $asset_purchase)->with('success', 'Pembelian aset berhasil diperbarui.');
    }

    public function approve(AssetPurchase $asset_purchase): RedirectResponse
    {
        if ($asset_purchase->status !== 'menunggu_approval') {
            return back()->with('error', 'Pembelian aset ini sudah diproses.');
        }

        DB::transaction(function () use ($asset_purchase) {
            $bank = BankAccount::lockForUpdate()->findOrFail($asset_purchase->bank_account_id);

            $bank->decrement('balance', $asset_purchase->purchase_amount);
            $financeTransaction = FinanceTransaction::create([
                'transaction_number' => $this->financeTransactionNumber(),
                'transaction_type' => 'expense',
                'transaction_date' => $asset_purchase->purchase_date,
                'finance_item_id' => $this->financeItem()->id,
                'bank_account_id' => $asset_purchase->bank_account_id,
                'activity' => 'Pembelian Aset ' . $asset_purchase->asset_number,
                'description' => 'Pembelian Aset ' . $asset_purchase->asset_number,
                'amount' => $asset_purchase->purchase_amount,
                'notes' => $asset_purchase->notes,
                'evidence_paths' => $asset_purchase->evidence_paths ?: [],
                'status' => 'disetujui',
                'created_by' => auth()->id(),
                'submitted_by' => $asset_purchase->submitted_by,
                'submitted_at' => $asset_purchase->submitted_at,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $asset_purchase->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'finance_transaction_id' => $financeTransaction->id,
            ]);
        });

        $asset_purchase->refresh();

        return redirect()->route('asset-purchases.show', $asset_purchase)->with('success', 'Pembelian aset disetujui dan tercatat di mutasi bank.');
    }

    public function reject(Request $request, AssetPurchase $asset_purchase): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        if ($asset_purchase->status !== 'menunggu_approval') {
            return back()->with('error', 'Pembelian aset ini sudah diproses.');
        }

        $asset_purchase->update([
            'status' => 'ditolak',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('asset-purchases.show', $asset_purchase)->with('success', 'Pembelian aset ditolak.');
    }

    public function updateCondition(Request $request, AssetPurchase $asset_purchase): RedirectResponse
    {
        $data = $request->validate([
            'condition_status' => 'required|in:bagus,rusak',
        ]);

        $asset_purchase->update($data);

        return redirect()->route('asset-purchases.show', $asset_purchase)->with('success', 'Status aset berhasil diperbarui.');
    }

    public function destroy(AssetPurchase $asset_purchase): RedirectResponse
    {
        if ($asset_purchase->status === 'disetujui' && ! $this->canManageApproved()) {
            return back()->with('error', 'Pembelian aset yang sudah disetujui tidak dapat dihapus.');
        }

        DB::transaction(function () use ($asset_purchase) {
            if ($asset_purchase->status === 'disetujui') {
                $this->reverseFinanceTransaction($asset_purchase->finance_transaction_id);
            }

            $asset_purchase->delete();
        });

        return redirect()->route('asset-purchases.index')->with('success', 'Pembelian aset berhasil dihapus.');
    }

    private function canManageApproved(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'asset_name' => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'purchase_date' => 'required|date',
            'supplier' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:120',
            'condition_status' => 'nullable|in:bagus,rusak',
            'purchase_amount' => 'required|numeric|min:1',
            'useful_life_years' => 'required|integer|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,percentage,none',
            'depreciation_percentage' => 'nullable|numeric|min:0|max:100',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'asset_photos' => 'nullable|array',
            'asset_photos.*' => 'file|mimes:jpg,jpeg,png,webp|max:4096',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'notes' => 'nullable|string',
        ]);
    }

    private function bookValue(array $data): float
    {
        return max(0, (float) $data['purchase_amount'] - (float) ($data['residual_value'] ?? 0));
    }

    private function normalizeDepreciationData(array $data): array
    {
        if (($data['depreciation_method'] ?? null) === 'none') {
            $data['residual_value'] = 0;
            $data['depreciation_percentage'] = null;
        }

        if (($data['depreciation_method'] ?? null) === 'straight_line') {
            $data['depreciation_percentage'] = null;
        }

        return $data;
    }

    private function storeFiles(Request $request, string $input, string $dir): array
    {
        return collect($request->file($input, []))
            ->filter()
            ->map(fn (UploadedFile $file) => $this->storeFile($file, $dir))
            ->values()
            ->all();
    }

    private function storeFile(UploadedFile $file, string $dir): string
    {
        if (!Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store($dir, 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return $file->store($dir, 'r2');
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

        $path = trim($dir, '/') . '/' . Str::uuid() . '.webp';
        Storage::disk('r2')->put($path, $contents, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);

        return $path;
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

    private function reverseFinanceTransaction(?int $financeTransactionId): void
    {
        if (! $financeTransactionId) {
            return;
        }

        $transaction = FinanceTransaction::find($financeTransactionId);
        if (! $transaction || $transaction->status !== 'disetujui') {
            return;
        }

        $bank = BankAccount::lockForUpdate()->findOrFail($transaction->bank_account_id);
        $transaction->transaction_type === 'income'
            ? $bank->decrement('balance', $transaction->amount)
            : $bank->increment('balance', $transaction->amount);
        $transaction->delete();
    }

    private function financeItem(): FinanceItem
    {
        $category = FinanceCategory::firstOrCreate(
            ['code' => 'AUTO-ASSET'],
            ['name' => 'Pembelian Aset', 'type' => 'expense', 'description' => 'Transaksi otomatis pembelian aset']
        );

        return FinanceItem::firstOrCreate(
            ['code' => 'AUTO-ASSET'],
            ['finance_category_id' => $category->id, 'name' => 'Pembelian Aset', 'description' => 'Transaksi otomatis pembelian aset', 'is_active' => true]
        );
    }

    private function assetNumber(): string
    {
        $prefix = 'AST-' . now()->format('y') . '-';
        $last = AssetPurchase::where('asset_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('asset_number')->value('asset_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }

    private function financeTransactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $last = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('transaction_number')->value('transaction_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }
}
