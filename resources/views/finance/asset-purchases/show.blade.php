@extends('layouts.app')

@php
    $statusConfig = [
        'disetujui' => ['label' => 'Disetujui', 'badge' => 'badge-light-success', 'bg' => 'bg-light-success', 'text' => 'text-success', 'icon' => 'ki-check-circle'],
        'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-light-danger', 'bg' => 'bg-light-danger', 'text' => 'text-danger', 'icon' => 'ki-cross-circle'],
        'menunggu_approval' => ['label' => 'Menunggu Approval', 'badge' => 'badge-light-warning', 'bg' => 'bg-light-warning', 'text' => 'text-warning', 'icon' => 'ki-time'],
    ][$assetPurchase->status] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $assetPurchase->status)), 'badge' => 'badge-light', 'bg' => 'bg-light', 'text' => 'text-muted', 'icon' => 'ki-information'];
    $bank = $assetPurchase->bankAccount;
    $bankName = strtoupper($bank?->bank_name ?? 'BANK');
    $bankLogoFile = str_contains($bankName, 'BCA DIGITAL') ? 'BCA Digital logo.svg' :
        (str_contains($bankName, 'BCA SYARIAH') ? 'BCA Syariah.svg' :
        (str_contains($bankName, 'BCA') ? 'Bank Central Asia.svg' :
        (str_contains($bankName, 'BRI') ? 'BRI 2020.svg' :
        (str_contains($bankName, 'BNI') ? 'Bank Negara Indonesia logo (2004).svg' :
        (str_contains($bankName, 'MANDIRI') ? 'Bank Mandiri logo 2016.svg' :
        (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA') ? 'Bank Syariah Indonesia.svg' :
        (str_contains($bankName, 'BTN') ? 'Bank BTN logo.svg' :
        (str_contains($bankName, 'CIMB') ? 'CIMB Niaga logo.svg' :
        (str_contains($bankName, 'DANAMON') ? 'Danamon.svg' :
        (str_contains($bankName, 'MEGA') ? 'Bank Mega 2013.svg' :
        (str_contains($bankName, 'PERMATA') ? 'Permata Bank (2024).svg' :
        (str_contains($bankName, 'PANIN') ? 'Logo Panin Bank.svg' :
        (str_contains($bankName, 'JAGO') ? 'Logo-jago.svg' :
        (str_contains($bankName, 'SEABANK') || str_contains($bankName, 'SEA BANK') ? 'SeaBank.svg' :
        (str_contains($bankName, 'UOB') ? 'UOB Logo (2022).svg' :
        (str_contains($bankName, 'DKI') ? 'Bank DKI.svg' : null))))))))))))))));
    $bankLogoUrl = $bankLogoFile
        ? 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($bankLogoFile) . '?width=160'
        : (str_contains($bankName, 'CASH') ? asset('assets/media/favicon.png') : null);
    $bankInitials = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $bank?->bank_name ?? 'Bank')))->filter()->map(fn($word) => substr($word, 0, 1))->take(3)->implode('');
    $methodLabel = ['straight_line' => 'Garis Lurus', 'percentage' => 'Persen', 'none' => 'Tanpa Depresiasi'][$assetPurchase->depreciation_method] ?? '-';
    $accumulatedDepreciation = max(0, (float) $assetPurchase->purchase_amount - (float) $assetPurchase->book_value);
@endphp

@section('title', 'Detail Pembelian Aset')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('asset-purchases.index') }}" class="text-muted text-hover-primary">Pembelian Aset</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
@php($canForceManage = auth()->user()?->hasRole('Superadmin'))
<a href="{{ route('asset-purchases.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@can('asset-purchases.edit')
    @if($assetPurchase->status !== 'disetujui' || $canForceManage)
        <a href="{{ route('asset-purchases.edit', $assetPurchase) }}" class="btn btn-sm btn-warning"><i class="ki-duotone ki-pencil fs-3"></i> Edit</a>
    @endif
@endcan
@endsection

@section('content')
<div class="purchase-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 purchase-hero-main">
            <div class="d-flex align-items-start gap-4 mb-7">
                <div class="purchase-status-icon {{ $statusConfig['bg'] }}"><i class="ki-outline {{ $statusConfig['icon'] }} fs-1 {{ $statusConfig['text'] }}"></i></div>
                <div>
                    <div class="purchase-number-pill mb-3">{{ $assetPurchase->asset_number }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2"><h1 class="fw-bolder fs-2 text-gray-900 mb-0">{{ $assetPurchase->asset_name }}</h1><span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span></div>
                    <div class="text-gray-600">Merk/Brand {{ $assetPurchase->supplier ?? '-' }} pada {{ $assetPurchase->purchase_date?->format('d/m/Y') ?? '-' }}</div>
                    <div class="purchase-meta-line"><span class="purchase-meta-chip">{{ $assetPurchase->asset_category ?? 'Tanpa kategori' }}</span><span class="purchase-meta-chip">{{ $assetPurchase->bankAccount?->code ?? '-' }}</span><span class="purchase-meta-chip">SN: {{ $assetPurchase->serial_number ?: '-' }}</span><span class="purchase-meta-chip {{ ($assetPurchase->condition_status ?? 'bagus') === 'rusak' ? 'text-danger' : 'text-success' }}">Kondisi: {{ str($assetPurchase->condition_status ?? 'bagus')->title() }}</span><span class="purchase-meta-chip">{{ $photoFiles->count() }} foto</span></div>
                </div>
            </div>
            <div class="purchase-note-box"><div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Catatan</div><div class="fw-semibold text-gray-800">{{ $assetPurchase->notes ?: 'Tidak ada catatan tambahan.' }}</div></div>
            @if($assetPurchase->status === 'ditolak')<div class="alert alert-danger mt-5 mb-0"><div class="fw-bold mb-1">Alasan Reject</div>{{ $assetPurchase->rejection_reason }}</div>@endif
        </div>
        <div class="col-xl-4"><div class="purchase-total-panel h-100 d-flex flex-column justify-content-between">
            <div class="position-relative"><div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Nominal Pembelian</div><div class="fw-bolder fs-1 text-white">Rp {{ number_format($assetPurchase->purchase_amount,0,',','.') }}</div><div class="text-white-50 fs-8 mt-2">Nilai buku Rp {{ number_format($assetPurchase->book_value,0,',','.') }}</div></div>
            <div class="position-relative mt-8">
                <div class="purchase-person-card mb-3"><div class="d-flex align-items-center gap-3"><span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-user fs-3 text-primary"></i></span></span><div><div class="label">Diajukan Oleh</div><div class="value">{{ $assetPurchase->submitter?->name ?? '-' }}</div><div class="text-white-50 fs-8">{{ $assetPurchase->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div></div></div></div>
                <div class="purchase-person-card"><div class="d-flex align-items-center gap-3"><span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-shield-tick fs-3 text-primary"></i></span></span><div><div class="label">Diproses Oleh</div><div class="value">{{ $assetPurchase->approver?->name ?? $assetPurchase->rejecter?->name ?? '-' }}</div><div class="text-white-50 fs-8">{{ ($assetPurchase->approved_at ?: $assetPurchase->rejected_at)?->format('d/m/Y H:i') ?? '-' }}</div></div></div></div>
            </div>
        </div></div>
    </div>
</div>

@can('asset-purchases.approve')
@if($assetPurchase->status === 'menunggu_approval')
<div class="card card-flush purchase-section-card mb-7"><div class="card-body"><div class="row g-5"><div class="col-lg-5"><form method="POST" action="{{ route('asset-purchases.approve', $assetPurchase) }}" class="asset-process-form" onsubmit="return confirm('Approve pembelian aset ini? Saldo bank akan berkurang sebagai uang keluar.');">@csrf<button type="submit" class="btn btn-success w-100"><i class="ki-duotone ki-check fs-2 text-white"></i> Approve Pembelian Aset</button></form></div><div class="col-lg-7"><form method="POST" action="{{ route('asset-purchases.reject', $assetPurchase) }}" class="asset-process-form">@csrf<label class="required form-label">Alasan Reject</label><textarea name="rejection_reason" class="form-control mb-3" rows="3" required></textarea><button type="submit" class="btn btn-danger"><i class="ki-duotone ki-cross fs-2 text-white"></i> Reject</button></form></div></div></div></div>
@endif
@endcan

<div class="row g-7">
    <div class="col-lg-5"><div class="card card-flush purchase-section-card h-100"><div class="card-header pt-6"><h3 class="fw-bold">Detail Depresiasi</h3></div><div class="card-body pt-0"><div class="row g-4">
        <div class="col-12"><div class="purchase-note-box d-flex align-items-center gap-3"><div class="bank-mini-logo">@if($bankLogoUrl)<img src="{{ $bankLogoUrl }}" alt="{{ $bank?->bank_name ?? 'Bank' }}">@else<span>{{ $bankInitials ?: 'BNK' }}</span>@endif</div><div><div class="text-muted fs-8">Account Bank</div><div class="fw-bold">{{ $bank?->code ?? '-' }} - {{ $bank?->bank_name ?? '-' }}</div></div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Metode Depresiasi</div><div class="fw-bold">{{ $methodLabel }} {{ $assetPurchase->depreciation_percentage ? '(' . $assetPurchase->depreciation_percentage . '%)' : '' }}</div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Umur Manfaat</div><div class="fw-bold">{{ $assetPurchase->useful_life_years }} tahun</div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Nilai Residu</div><div class="fw-bold">Rp {{ number_format($assetPurchase->residual_value,0,',','.') }}</div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Akumulasi Depresiasi</div><div class="fw-bold text-warning">Rp {{ number_format($accumulatedDepreciation,0,',','.') }}</div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Nilai Buku</div><div class="fw-bold text-success">Rp {{ number_format($assetPurchase->book_value,0,',','.') }}</div></div></div>
        <div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Kondisi Aset</div><div class="fw-bold {{ ($assetPurchase->condition_status ?? 'bagus') === 'rusak' ? 'text-danger' : 'text-success' }}">{{ str($assetPurchase->condition_status ?? 'bagus')->title() }}</div></div></div>
        @can('asset-purchases.edit')
        <div class="col-12">
            <form method="POST" action="{{ route('asset-purchases.condition', $assetPurchase) }}" class="purchase-condition-form">
                @csrf
                <div class="purchase-note-box">
                    <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
                        <div class="flex-grow-1">
                            <label class="form-label fw-semibold mb-2">Ubah Status Aset</label>
                            <select name="condition_status" class="form-select">
                                <option value="bagus" @selected(($assetPurchase->condition_status ?? 'bagus') === 'bagus')>Bagus</option>
                                <option value="rusak" @selected(($assetPurchase->condition_status ?? 'bagus') === 'rusak')>Rusak</option>
                            </select>
                            <div class="text-muted fs-8 mt-2">Gunakan ini untuk memperbarui kondisi fisik aset tanpa mengubah data pembelian.</div>
                        </div>
                        <button type="submit" class="btn btn-primary text-nowrap"><i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan Status</button>
                    </div>
                </div>
            </form>
        </div>
        @endcan
    </div></div></div></div>
    <div class="col-lg-7"><div class="card card-flush purchase-section-card h-100"><div class="card-header pt-6"><h3 class="fw-bold">Foto & Eviden</h3></div><div class="card-body pt-0"><div class="row g-4">@foreach($photoFiles->merge($evidenceFiles) as $file)<div class="col-md-6">@if($file->is_image)<a href="{{ $file->url }}" target="_blank" class="purchase-evidence-tile d-block"><div class="purchase-evidence-frame mb-3"><img src="{{ $file->url }}" class="w-100 h-100" style="object-fit:contain" alt="{{ $file->name }}"></div><div class="fw-semibold text-truncate">{{ $file->name }}</div></a>@else<a href="{{ $file->url }}" target="_blank" class="btn btn-light-primary">{{ $file->name }}</a>@endif</div>@endforeach</div></div></div></div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.asset-process-form, .purchase-condition-form').forEach(function(form){
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
.purchase-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}.purchase-hero-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}.purchase-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}.purchase-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}.purchase-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.purchase-meta-chip{display:inline-flex;align-items:center;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}.purchase-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#fff;padding:18px}.purchase-total-panel{padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);position:relative;overflow:hidden}.purchase-total-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}.purchase-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}.purchase-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}.purchase-person-card .value{color:#fff;font-weight:700}.purchase-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}.purchase-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%;transition:transform .15s ease,box-shadow .15s ease}.purchase-evidence-frame{height:220px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}.bank-mini-logo{width:44px;height:44px;border:1px solid #e4e8f0;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}.bank-mini-logo img{max-width:32px;max-height:22px;object-fit:contain}.bank-mini-logo span{font-size:11px;font-weight:800;color:#1d4ed8}
</style>
@endpush
