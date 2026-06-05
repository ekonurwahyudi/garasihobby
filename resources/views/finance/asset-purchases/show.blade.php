@extends('layouts.app')

@section('title', 'Detail Pembelian Aset')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('asset-purchases.index') }}" class="text-muted text-hover-primary">Pembelian Aset</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('asset-purchases.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@can('asset-purchases.edit')
    @if($assetPurchase->status !== 'disetujui')
        <a href="{{ route('asset-purchases.edit', $assetPurchase) }}" class="btn btn-sm btn-warning"><i class="ki-duotone ki-pencil fs-3"></i> Edit</a>
    @endif
@endcan
@endsection

@section('content')
@php
    $statusConfig = match($assetPurchase->status) {
        'disetujui' => ['label'=>'Disetujui','badge'=>'badge-light-success','bg'=>'bg-light-success','text'=>'text-success','icon'=>'ki-check-circle'],
        'ditolak' => ['label'=>'Ditolak','badge'=>'badge-light-danger','bg'=>'bg-light-danger','text'=>'text-danger','icon'=>'ki-cross-circle'],
        default => ['label'=>'Menunggu Approval','badge'=>'badge-light-warning','bg'=>'bg-light-warning','text'=>'text-warning','icon'=>'ki-time'],
    };
@endphp
<div class="purchase-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 purchase-hero-main">
            <div class="d-flex align-items-start gap-4 mb-7">
                <div class="purchase-status-icon {{ $statusConfig['bg'] }}"><i class="ki-outline {{ $statusConfig['icon'] }} fs-1 {{ $statusConfig['text'] }}"></i></div>
                <div>
                    <div class="purchase-number-pill mb-3">{{ $assetPurchase->asset_number }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2"><h1 class="fw-bolder fs-2 text-gray-900 mb-0">{{ $assetPurchase->asset_name }}</h1><span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span></div>
                    <div class="text-gray-600">Dibeli dari {{ $assetPurchase->supplier ?? '-' }} pada {{ $assetPurchase->purchase_date?->format('d/m/Y') ?? '-' }}</div>
                    <div class="purchase-meta-line"><span class="purchase-meta-chip">{{ $assetPurchase->asset_category ?? 'Tanpa kategori' }}</span><span class="purchase-meta-chip">{{ $assetPurchase->bankAccount?->code ?? '-' }}</span><span class="purchase-meta-chip">{{ $photoFiles->count() }} foto</span></div>
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
<div class="card card-flush purchase-section-card mb-7"><div class="card-body"><div class="row g-5"><div class="col-lg-5"><form method="POST" action="{{ route('asset-purchases.approve', $assetPurchase) }}">@csrf<button class="btn btn-success w-100"><i class="ki-duotone ki-check fs-2 text-white"></i> Approve Pembelian Aset</button></form></div><div class="col-lg-7"><form method="POST" action="{{ route('asset-purchases.reject', $assetPurchase) }}">@csrf<label class="required form-label">Alasan Reject</label><textarea name="rejection_reason" class="form-control mb-3" rows="3" required></textarea><button class="btn btn-danger"><i class="ki-duotone ki-cross fs-2 text-white"></i> Reject</button></form></div></div></div></div>
@endif
@endcan

<div class="row g-7">
    <div class="col-lg-5"><div class="card card-flush purchase-section-card h-100"><div class="card-header pt-6"><h3 class="fw-bold">Detail Depresiasi</h3></div><div class="card-body pt-0"><div class="row g-4"><div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Umur Manfaat</div><div class="fw-bold">{{ $assetPurchase->useful_life_years }} tahun</div></div></div><div class="col-6"><div class="purchase-note-box"><div class="text-muted fs-8">Nilai Residu</div><div class="fw-bold">Rp {{ number_format($assetPurchase->residual_value,0,',','.') }}</div></div></div><div class="col-12"><div class="purchase-note-box"><div class="text-muted fs-8">Metode</div><div class="fw-bold">{{ str($assetPurchase->depreciation_method)->replace('_',' ')->title() }} {{ $assetPurchase->depreciation_percentage ? '(' . $assetPurchase->depreciation_percentage . '%)' : '' }}</div></div></div></div></div></div></div>
    <div class="col-lg-7"><div class="card card-flush purchase-section-card h-100"><div class="card-header pt-6"><h3 class="fw-bold">Foto & Eviden</h3></div><div class="card-body pt-0"><div class="row g-4">@foreach($photoFiles->merge($evidenceFiles) as $file)<div class="col-md-6">@if($file->is_image)<a href="{{ $file->url }}" target="_blank" class="purchase-evidence-tile d-block"><div class="purchase-evidence-frame mb-3"><img src="{{ $file->url }}" class="w-100 h-100" style="object-fit:contain" alt="{{ $file->name }}"></div><div class="fw-semibold text-truncate">{{ $file->name }}</div></a>@else<a href="{{ $file->url }}" target="_blank" class="btn btn-light-primary">{{ $file->name }}</a>@endif</div>@endforeach</div></div></div></div>
</div>
@endsection

@push('styles')
<style>
.purchase-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}.purchase-hero-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}.purchase-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}.purchase-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}.purchase-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.purchase-meta-chip{display:inline-flex;align-items:center;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}.purchase-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#fff;padding:18px}.purchase-total-panel{padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);position:relative;overflow:hidden}.purchase-total-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}.purchase-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}.purchase-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}.purchase-person-card .value{color:#fff;font-weight:700}.purchase-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}.purchase-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%;transition:transform .15s ease,box-shadow .15s ease}.purchase-evidence-frame{height:220px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}
</style>
@endpush
