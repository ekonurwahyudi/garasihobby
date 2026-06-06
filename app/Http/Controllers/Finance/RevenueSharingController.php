<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use App\Models\RevenueCutoff;
use App\Models\RevenueSharing;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RevenueSharingController extends Controller
{
    public function index(): View
    {
        $defaultCutoff = $this->periodFromRequest(request());
        $data = RevenueSharing::with(['bankAccount', 'submitter', 'approver', 'rejecter'])
            ->latest('period_end')
            ->latest('id')
            ->get();
        $cutoffs = RevenueCutoff::with(['activeSharings', 'sharings'])
            ->latest('period_start')
            ->latest('id')
            ->get();

        return view('finance.revenue-sharings.index', [
            'data' => $data,
            'cutoffs' => $cutoffs,
            'defaultCutoff' => $defaultCutoff,
        ]);
    }

    public function create(Request $request): View
    {
        $cutoff = $this->periodFromRequest($request);

        return view('finance.revenue-sharings.create', [
            'revenueSharing' => null,
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
            'cutoff' => $cutoff,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $cutoff = $this->periodFromData($data);

        $data = array_merge($data, [
            'revenue_cutoff_id' => $cutoff['id'] ?? null,
            'sharing_number' => $this->sharingNumber(),
            'cutoff_month' => $cutoff['month'],
            'cutoff_quarter' => $cutoff['quarter'],
            'period_start' => $cutoff['start'],
            'period_end' => $cutoff['end'],
            'gross_revenue' => $cutoff['grossRevenue'],
            'total_expense' => $cutoff['totalExpense'],
            'net_revenue' => $cutoff['netRevenue'],
            'sharing_amount' => $this->sharingAmount($cutoff['netRevenue'], $data['sharing_percentage']),
            'evidence_paths' => $this->storeFiles($request, 'evidence', 'revenue-sharings/evidence'),
            'status' => 'menunggu_approval',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        RevenueSharing::create($data);

        return redirect()->route('revenue-sharings.index')->with('success', 'Revenue sharing berhasil diajukan dan menunggu approval.');
    }

    public function storeCutoff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cutoff_type' => 'required|in:monthly,quarterly,yearly',
            'cutoff_year' => 'required|integer|min:2020|max:2100',
            'cutoff_month' => 'nullable|required_if:cutoff_type,monthly|integer|min:1|max:12',
            'cutoff_quarter' => 'nullable|required_if:cutoff_type,quarterly|integer|min:1|max:4',
        ]);

        $period = $this->periodFromData($data);
        $cutoff = $this->firstOrCreateCutoff($period);

        return redirect()
            ->route('revenue-sharings.index')
            ->with('success', 'Cut off revenue ' . $cutoff->period_label . ' berhasil dicatat.');
    }

    public function destroyCutoff(RevenueCutoff $revenue_cutoff): RedirectResponse
    {
        if ($revenue_cutoff->sharings()->exists()) {
            return back()->with('error', 'Cut off tidak bisa dihapus karena sudah memiliki data sharing revenue.');
        }

        $revenue_cutoff->delete();

        return redirect()->route('revenue-sharings.index')->with('success', 'Cut off revenue berhasil dihapus.');
    }

    public function show(RevenueSharing $revenue_sharing): View
    {
        $revenue_sharing->load(['bankAccount', 'submitter', 'approver', 'rejecter', 'financeTransaction']);

        return view('finance.revenue-sharings.show', [
            'revenueSharing' => $revenue_sharing,
            'bank' => $revenue_sharing->bankAccount,
            'evidenceFiles' => $this->fileObjects($revenue_sharing->evidence_paths ?: []),
            'statusConfig' => $this->statusConfig($revenue_sharing->status),
        ]);
    }

    public function edit(RevenueSharing $revenue_sharing): View|RedirectResponse
    {
        if ($revenue_sharing->status === 'disetujui' && ! $this->canManageApproved()) {
            return redirect()->route('revenue-sharings.show', $revenue_sharing)->with('error', 'Revenue sharing yang sudah disetujui tidak dapat diedit.');
        }

        return view('finance.revenue-sharings.edit', [
            'revenueSharing' => $revenue_sharing,
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
            'cutoff' => $this->periodFromSharing($revenue_sharing),
        ]);
    }

    public function update(Request $request, RevenueSharing $revenue_sharing): RedirectResponse
    {
        if ($revenue_sharing->status === 'disetujui' && ! $this->canManageApproved()) {
            return redirect()->route('revenue-sharings.show', $revenue_sharing)->with('error', 'Revenue sharing yang sudah disetujui tidak dapat diedit.');
        }

        $data = $this->validatedData($request);
        $cutoff = $this->periodFromData($data);

        $data = array_merge($data, [
            'revenue_cutoff_id' => $cutoff['id'] ?? null,
            'period_start' => $cutoff['start'],
            'period_end' => $cutoff['end'],
            'cutoff_month' => $cutoff['month'],
            'cutoff_quarter' => $cutoff['quarter'],
            'gross_revenue' => $cutoff['grossRevenue'],
            'total_expense' => $cutoff['totalExpense'],
            'net_revenue' => $cutoff['netRevenue'],
            'sharing_amount' => $this->sharingAmount($cutoff['netRevenue'], $data['sharing_percentage']),
            'status' => 'menunggu_approval',
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_paths'] = $this->storeFiles($request, 'evidence', 'revenue-sharings/evidence');
        }

        DB::transaction(function () use ($revenue_sharing, $data) {
            if ($revenue_sharing->status === 'disetujui') {
                $this->reverseFinanceTransaction($revenue_sharing->finance_transaction_id);
                $data['finance_transaction_id'] = null;
            }

            $revenue_sharing->update($data);
        });

        return redirect()->route('revenue-sharings.show', $revenue_sharing)->with('success', 'Revenue sharing berhasil diperbarui.');
    }

    public function approve(RevenueSharing $revenue_sharing): RedirectResponse
    {
        if ($revenue_sharing->status !== 'menunggu_approval') {
            return back()->with('error', 'Revenue sharing ini sudah diproses.');
        }

        DB::transaction(function () use ($revenue_sharing) {
            $bank = BankAccount::lockForUpdate()->findOrFail($revenue_sharing->bank_account_id);
            $bank->decrement('balance', $revenue_sharing->sharing_amount);

            $financeTransaction = FinanceTransaction::create([
                'transaction_number' => $this->financeTransactionNumber(),
                'transaction_type' => 'expense',
                'transaction_date' => $revenue_sharing->period_end,
                'finance_item_id' => $this->financeItem()->id,
                'bank_account_id' => $revenue_sharing->bank_account_id,
                'activity' => 'Revenue Sharing ' . $revenue_sharing->sharing_number,
                'description' => 'Revenue Sharing ' . $revenue_sharing->recipient_name,
                'amount' => $revenue_sharing->sharing_amount,
                'notes' => $revenue_sharing->notes,
                'evidence_paths' => $revenue_sharing->evidence_paths ?: [],
                'status' => 'disetujui',
                'created_by' => auth()->id(),
                'submitted_by' => $revenue_sharing->submitted_by,
                'submitted_at' => $revenue_sharing->submitted_at,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $revenue_sharing->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
                'finance_transaction_id' => $financeTransaction->id,
            ]);
        });

        return redirect()->route('revenue-sharings.show', $revenue_sharing)->with('success', 'Revenue sharing disetujui dan tercatat sebagai uang keluar.');
    }

    public function reject(Request $request, RevenueSharing $revenue_sharing): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        if ($revenue_sharing->status !== 'menunggu_approval') {
            return back()->with('error', 'Revenue sharing ini sudah diproses.');
        }

        $revenue_sharing->update([
            'status' => 'ditolak',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('revenue-sharings.show', $revenue_sharing)->with('success', 'Revenue sharing ditolak.');
    }

    public function destroy(RevenueSharing $revenue_sharing): RedirectResponse
    {
        if ($revenue_sharing->status === 'disetujui' && ! $this->canManageApproved()) {
            return back()->with('error', 'Revenue sharing yang sudah disetujui tidak dapat dihapus.');
        }

        DB::transaction(function () use ($revenue_sharing) {
            if ($revenue_sharing->status === 'disetujui') {
                $this->reverseFinanceTransaction($revenue_sharing->finance_transaction_id);
            }

            $revenue_sharing->delete();
        });

        return redirect()->route('revenue-sharings.index')->with('success', 'Revenue sharing berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'recipient_name' => 'required|string|max:150',
            'revenue_cutoff_id' => 'nullable|exists:revenue_cutoffs,id',
            'cutoff_type' => 'required|in:monthly,quarterly,yearly',
            'cutoff_year' => 'required|integer|min:2020|max:2100',
            'cutoff_month' => 'nullable|required_if:cutoff_type,monthly|integer|min:1|max:12',
            'cutoff_quarter' => 'nullable|required_if:cutoff_type,quarterly|integer|min:1|max:4',
            'sharing_percentage' => 'required|numeric|min:0|max:100',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'evidence' => 'nullable|array',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'notes' => 'nullable|string',
        ]);
    }

    private function periodFromRequest(Request $request): array
    {
        if ($request->filled('revenue_cutoff_id')) {
            $cutoff = RevenueCutoff::find($request->integer('revenue_cutoff_id'));
            if ($cutoff) {
                return $this->periodFromCutoff($cutoff);
            }
        }

        return $this->periodFromData([
            'cutoff_type' => $request->get('cutoff_type', 'monthly'),
            'cutoff_year' => (int) $request->get('cutoff_year', now()->year),
            'cutoff_month' => (int) $request->get('cutoff_month', now()->month),
            'cutoff_quarter' => (int) $request->get('cutoff_quarter', now()->quarter),
        ]);
    }

    private function periodFromSharing(RevenueSharing $sharing): array
    {
        return [
            'type' => $sharing->cutoff_type,
            'year' => $sharing->cutoff_year,
            'month' => $sharing->cutoff_month,
            'quarter' => $sharing->cutoff_quarter,
            'start' => $sharing->period_start,
            'end' => $sharing->period_end,
            'grossRevenue' => (float) $sharing->gross_revenue,
            'totalExpense' => (float) $sharing->total_expense,
            'netRevenue' => (float) $sharing->net_revenue,
            'label' => $sharing->period_label,
        ];
    }

    private function periodFromData(array $data): array
    {
        if (! empty($data['revenue_cutoff_id'])) {
            $cutoff = RevenueCutoff::find($data['revenue_cutoff_id']);
            if ($cutoff) {
                return $this->periodFromCutoff($cutoff);
            }
        }

        $type = $data['cutoff_type'] ?? 'monthly';
        $year = (int) ($data['cutoff_year'] ?? now()->year);

        if ($type === 'quarterly') {
            $quarter = max(1, min(4, (int) ($data['cutoff_quarter'] ?? now()->quarter)));
            $start = Carbon::create($year, (($quarter - 1) * 3) + 1, 1)->startOfDay();
            $end = $start->copy()->addMonths(2)->endOfMonth();
            $month = null;
            $label = 'Triwulan ' . $quarter . ' ' . $year;
        } elseif ($type === 'yearly') {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
            $month = null;
            $quarter = null;
            $label = 'Tahun ' . $year;
        } else {
            $month = max(1, min(12, (int) ($data['cutoff_month'] ?? now()->month)));
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
            $quarter = null;
            $label = $start->translatedFormat('F Y');
        }

        $grossRevenue = (float) FinanceTransaction::where('status', 'disetujui')
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
        $totalExpense = (float) FinanceTransaction::where('status', 'disetujui')
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        return [
            'type' => $type,
            'id' => null,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'grossRevenue' => $grossRevenue,
            'totalExpense' => $totalExpense,
            'netRevenue' => $grossRevenue - $totalExpense,
            'label' => $label,
        ];
    }

    private function periodFromCutoff(RevenueCutoff $cutoff): array
    {
        return [
            'type' => $cutoff->cutoff_type,
            'id' => $cutoff->id,
            'year' => $cutoff->cutoff_year,
            'month' => $cutoff->cutoff_month,
            'quarter' => $cutoff->cutoff_quarter,
            'start' => $cutoff->period_start?->toDateString(),
            'end' => $cutoff->period_end?->toDateString(),
            'grossRevenue' => (float) $cutoff->gross_revenue,
            'totalExpense' => (float) $cutoff->total_expense,
            'netRevenue' => (float) $cutoff->net_revenue,
            'label' => $cutoff->period_label,
        ];
    }

    private function firstOrCreateCutoff(array $period): RevenueCutoff
    {
        return RevenueCutoff::firstOrCreate(
            [
                'cutoff_type' => $period['type'],
                'cutoff_year' => $period['year'],
                'cutoff_month' => $period['month'],
                'cutoff_quarter' => $period['quarter'],
            ],
            [
                'cutoff_number' => $this->cutoffNumber(),
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'gross_revenue' => $period['grossRevenue'],
                'total_expense' => $period['totalExpense'],
                'net_revenue' => $period['netRevenue'],
                'created_by' => auth()->id(),
            ]
        );
    }

    private function sharingAmount(float $netRevenue, float|string $percentage): float
    {
        return round(max(0, $netRevenue) * ((float) $percentage / 100), 2);
    }

    private function canManageApproved(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    private function statusConfig(?string $status): array
    {
        return match ($status) {
            'disetujui' => ['label' => 'Disetujui', 'badge' => 'badge-light-success', 'bg' => 'bg-light-success', 'text' => 'text-success', 'icon' => 'ki-check-circle'],
            'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-light-danger', 'bg' => 'bg-light-danger', 'text' => 'text-danger', 'icon' => 'ki-cross-circle'],
            default => ['label' => 'Menunggu Approval', 'badge' => 'badge-light-warning', 'bg' => 'bg-light-warning', 'text' => 'text-warning', 'icon' => 'ki-time'],
        };
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
        if (! Str::startsWith((string) $file->getMimeType(), 'image/')) {
            return $file->store($dir, 'r2');
        }

        $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $image) {
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
            ['code' => 'AUTO-RS'],
            ['name' => 'Revenue Sharing', 'type' => 'expense', 'description' => 'Transaksi otomatis revenue sharing']
        );

        return FinanceItem::firstOrCreate(
            ['code' => 'AUTO-RS'],
            ['finance_category_id' => $category->id, 'name' => 'Revenue Sharing', 'description' => 'Transaksi otomatis revenue sharing', 'is_active' => true]
        );
    }

    private function sharingNumber(): string
    {
        $prefix = 'RS-' . now()->format('y') . '-';
        $last = RevenueSharing::where('sharing_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('sharing_number')->value('sharing_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }

    private function cutoffNumber(): string
    {
        $prefix = 'CO-' . now()->format('y') . '-';
        $last = RevenueCutoff::where('cutoff_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('cutoff_number')->value('cutoff_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }

    private function financeTransactionNumber(): string
    {
        $prefix = 'KEU-' . now()->format('y') . '-';
        $last = FinanceTransaction::where('transaction_number', 'like', $prefix . '%')->lockForUpdate()->orderByDesc('transaction_number')->value('transaction_number');
        return $prefix . str_pad((string) ($last ? ((int) Str::afterLast($last, '-') + 1) : 1), 5, '0', STR_PAD_LEFT);
    }
}
