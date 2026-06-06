@extends('layouts.app')

@php
    $status = $statusConfig ?? match($revenueSharing->status) {
        'disetujui' => ['label' => 'Disetujui', 'badge' => 'badge-light-success', 'bg' => 'bg-light-success', 'text' => 'text-success', 'icon' => 'ki-check-circle'],
        'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-light-danger', 'bg' => 'bg-light-danger', 'text' => 'text-danger', 'icon' => 'ki-cross-circle'],
        default => ['label' => 'Menunggu Approval', 'badge' => 'badge-light-warning', 'bg' => 'bg-light-warning', 'text' => 'text-warning', 'icon' => 'ki-time'],
    };
    $account = $bank ?? $revenueSharing->bankAccount;
    $files = $evidenceFiles ?? collect();
    $canForceManage = auth()->user()?->hasRole('Superadmin');
@endphp

@section('title', 'Detail Revenue Sharing')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('revenue-sharings.index') }}" class="text-muted text-hover-primary">Revenue Sharing</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('revenue-sharings.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@can('revenue-sharings.edit')
    @if($revenueSharing->status !== 'disetujui' || $canForceManage)
        <a href="{{ route('revenue-sharings.edit', $revenueSharing) }}" class="btn btn-sm btn-warning"><i class="ki-duotone ki-pencil fs-3"></i> Edit</a>
    @endif
@endcan
@endsection

@section('content')
<div class="rs-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 rs-hero-main">
            <div class="d-flex align-items-start gap-4 mb-7">
                <div class="rs-status-icon {{ $status['bg'] }}">
                    <i class="ki-outline {{ $status['icon'] }} fs-1 {{ $status['text'] }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="rs-number-pill mb-3">{{ $revenueSharing->sharing_number }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <h1 class="fw-bolder fs-2 text-gray-900 mb-0">{{ $revenueSharing->recipient_name }}</h1>
                        <span class="badge {{ $status['badge'] }}">{{ $status['label'] }}</span>
                    </div>
                    <div class="text-gray-600">Revenue sharing {{ $revenueSharing->period_label }}</div>
                    <div class="rs-meta-line">
                        <span class="rs-meta-chip">{{ $revenueSharing->period_start?->format('d/m/Y') }} - {{ $revenueSharing->period_end?->format('d/m/Y') }}</span>
                        <span class="rs-meta-chip">{{ number_format((float) $revenueSharing->sharing_percentage, 2, ',', '.') }}%</span>
                        <span class="rs-meta-chip">{{ $account?->code ?? '-' }} - {{ $account?->bank_name ?? '-' }}</span>
                        <span class="rs-meta-chip">{{ $files->count() }} eviden</span>
                    </div>
                </div>
            </div>

            <div class="rs-note-box">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Catatan</div>
                <div class="fw-semibold text-gray-800">{{ $revenueSharing->notes ?: 'Tidak ada catatan tambahan.' }}</div>
            </div>

            @if($revenueSharing->status === 'ditolak')
                <div class="alert alert-danger mt-5 mb-0">
                    <div class="fw-bold mb-1">Alasan Reject</div>
                    {{ $revenueSharing->rejection_reason }}
                </div>
            @endif
        </div>
        <div class="col-xl-4">
            <div class="rs-total-panel h-100 d-flex flex-column justify-content-between">
                <div class="position-relative">
                    <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Nominal Sharing</div>
                    <div class="fw-bolder fs-1 text-white">Rp {{ number_format($revenueSharing->sharing_amount, 0, ',', '.') }}</div>
                    <div class="text-white-50 fs-8 mt-2">Dari revenue bersih Rp {{ number_format($revenueSharing->net_revenue, 0, ',', '.') }}</div>
                </div>
                <div class="position-relative mt-8">
                    <div class="rs-person-card mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-user fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diajukan Oleh</div>
                                <div class="value">{{ $revenueSharing->submitter?->name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ $revenueSharing->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="rs-person-card">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-shield-tick fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diproses Oleh</div>
                                <div class="value">{{ $revenueSharing->approver?->name ?? $revenueSharing->rejecter?->name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ ($revenueSharing->approved_at ?: $revenueSharing->rejected_at)?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@can('revenue-sharings.approve')
    @if($revenueSharing->status === 'menunggu_approval')
        <div class="card card-flush rs-section-card mb-7">
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-lg-5">
                        <form method="POST" action="{{ route('revenue-sharings.approve', $revenueSharing) }}" class="rs-process-form" onsubmit="return confirm('Approve revenue sharing ini? Saldo bank akan berkurang.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"><i class="ki-duotone ki-check fs-2 text-white"></i> Approve Revenue Sharing</button>
                        </form>
                    </div>
                    <div class="col-lg-7">
                        <form method="POST" action="{{ route('revenue-sharings.reject', $revenueSharing) }}" class="rs-process-form">
                            @csrf
                            <label class="required form-label">Alasan Reject</label>
                            <textarea name="rejection_reason" class="form-control mb-3" rows="3" required></textarea>
                            <button type="submit" class="btn btn-danger"><i class="ki-duotone ki-cross fs-2 text-white"></i> Reject</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endcan

<div class="row g-7">
    <div class="col-lg-5">
        <div class="card card-flush rs-section-card h-100">
            <div class="card-header pt-6"><h3 class="fw-bold">Detail Revenue</h3></div>
            <div class="card-body pt-0">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="rs-note-box d-flex align-items-center gap-3">
                            <div class="bank-mini-logo">
                                @if($account?->logo_url)
                                    <img src="{{ $account->logo_url }}" alt="{{ $account->bank_name }}">
                                @else
                                    <span>{{ $account?->logo_text ?? 'BNK' }}</span>
                                @endif
                            </div>
                            <div>
                                <div class="text-muted fs-8">Akun Bank</div>
                                <div class="fw-bold">{{ $account?->code ?? '-' }} - {{ $account?->bank_name ?? '-' }}</div>
                                <div class="text-muted fs-8">{{ $account?->account_number ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6"><div class="rs-note-box"><div class="text-muted fs-8">Revenue Kotor</div><div class="fw-bold text-primary">Rp {{ number_format($revenueSharing->gross_revenue, 0, ',', '.') }}</div></div></div>
                    <div class="col-6"><div class="rs-note-box"><div class="text-muted fs-8">Total Expense</div><div class="fw-bold text-danger">Rp {{ number_format($revenueSharing->total_expense, 0, ',', '.') }}</div></div></div>
                    <div class="col-6"><div class="rs-note-box"><div class="text-muted fs-8">Revenue Bersih</div><div class="fw-bold text-success">Rp {{ number_format($revenueSharing->net_revenue, 0, ',', '.') }}</div></div></div>
                    <div class="col-6"><div class="rs-note-box"><div class="text-muted fs-8">Persentase</div><div class="fw-bold">{{ number_format((float) $revenueSharing->sharing_percentage, 2, ',', '.') }}%</div></div></div>
                    <div class="col-12">
                        <div class="rs-note-box">
                            <div class="text-muted fs-8">Transaksi Keuangan</div>
                            <div class="fw-bold">
                                @if($revenueSharing->financeTransaction)
                                    <a href="{{ route('finance-transactions.show', $revenueSharing->financeTransaction) }}" class="text-hover-primary">{{ $revenueSharing->financeTransaction->transaction_number }}</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-flush rs-section-card h-100">
            <div class="card-header pt-6"><h3 class="fw-bold">Eviden</h3></div>
            <div class="card-body pt-0">
                @if($files->isNotEmpty())
                    <div class="row g-4">
                        @foreach($files as $file)
                            <div class="col-md-6">
                                @if($file->is_image)
                                    <a href="{{ $file->url }}" target="_blank" class="rs-evidence-tile d-block">
                                        <div class="rs-evidence-frame mb-3"><img src="{{ $file->url }}" class="w-100 h-100" style="object-fit:contain" alt="{{ $file->name }}"></div>
                                        <div class="fw-semibold text-truncate">{{ $file->name }}</div>
                                    </a>
                                @else
                                    <a href="{{ $file->url }}" target="_blank" class="btn btn-light-primary">{{ $file->name }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rs-note-box text-muted">Belum ada eviden.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.rs-process-form').forEach(function(form){
    form.addEventListener('submit', function(){
        form.querySelectorAll('button[type="submit"]').forEach(function(button){
            button.disabled = true;
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.rs-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}
.rs-hero-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}
.rs-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rs-number-pill{display:inline-flex;align-items:center;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}
.rs-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.rs-meta-chip{display:inline-flex;align-items:center;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}
.rs-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#fff;padding:18px}
.rs-total-panel{padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);position:relative;overflow:hidden}
.rs-total-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}
.rs-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}
.rs-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}
.rs-person-card .value{color:#fff;font-weight:700}
.rs-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}
.rs-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%}
.rs-evidence-frame{height:220px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}
.bank-mini-logo{width:44px;height:44px;border:1px solid #e4e8f0;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bank-mini-logo img{max-width:32px;max-height:22px;object-fit:contain}
.bank-mini-logo span{font-size:11px;font-weight:800;color:#1d4ed8}
</style>
@endpush
