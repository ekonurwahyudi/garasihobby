@extends('layouts.app')

@section('title', 'Detail Order')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('orders.index') }}" class="text-muted text-hover-primary">Order Management</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">{{ $order->order_number }}</li>
@endsection

@section('toolbar_actions')
    @if($order->status === 'selesai')
    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-success me-2">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Download Invoice
    </a>
    @endif
    @can('orders.edit')
    <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-warning me-2">
        <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i> Edit
    </a>
    @endcan
    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-arrow-left fs-3"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>
@endsection

@section('content')
@php
    $statusMap = [
        'draft' => ['label' => 'Draft', 'class' => 'badge-light'],
        'open' => ['label' => 'Open', 'class' => 'badge-light-primary'],
        'belum_bayar' => ['label' => 'Belum Bayar', 'class' => 'badge-light-warning'],
        'selesai' => ['label' => 'Selesai', 'class' => 'badge-light-success'],
    ];
    $vehicleSizeMap = [
        'small' => 'S - City Car / Hatchback / Sedan Kecil',
        'medium' => 'M - MPV / SUV Medium / Pickup Ringan',
        'large' => 'L - SUV Besar / Double Cabin / Ladder Frame',
    ];
    $status = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'badge-light'];
    $checklistTotal = $order->items->sum('price');
    $materialTotal = $order->materials->sum('subtotal');
    $evidenceGroups = [
        'Eviden Checklist / Pekerjaan' => collect($order->evidence_work_paths ?? []),
        'Eviden Pembayaran' => collect($order->evidence_payment_paths ?? []),
    ];
    $evidenceFiles = collect($evidenceGroups)->map(fn ($paths) => $paths->filter()->map(fn ($path) => [
        'path' => $path,
        'url' => \Illuminate\Support\Facades\Storage::disk('r2')->url($path),
        'is_image' => in_array(\Illuminate\Support\Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true),
        'name' => basename($path),
    ])->values());
@endphp

<div class="order-hero mb-7">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-6">
        <div class="d-flex align-items-start gap-4">
            <div class="order-status-icon {{ $status['class'] }}">
                <i class="ki-duotone ki-notepad fs-1"><span class="path1"></span><span class="path2"></span></i>
            </div>
            <div>
                <div class="order-number-pill mb-3">
                    <i class="ki-duotone ki-wrench fs-5"><span class="path1"></span><span class="path2"></span></i>
                    {{ $order->order_number }}
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                    <h1 class="fw-bolder fs-2 text-gray-900 mb-0">Detail Order Bengkel</h1>
                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                </div>
                <div class="text-gray-600">{{ $order->order_date->format('d/m/Y') }} oleh {{ $order->creator->name ?? '-' }}</div>
                <div class="order-meta-line">
                    <span class="order-meta-chip"><i class="ki-duotone ki-user fs-5"><span class="path1"></span><span class="path2"></span></i>{{ $order->customer->name ?? '-' }}</span>
                    <span class="order-meta-chip">{{ $order->vehicle->plate_number ?? '-' }}</span>
                    <span class="order-meta-chip">{{ trim(($order->vehicle->brand ?? '') . ' ' . ($order->vehicle->model ?? '') . ' ' . ($order->vehicle->year ?? '')) ?: '-' }}</span>
                </div>
            </div>
        </div>
        <div class="order-total-panel">
            <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Total Order</div>
            <div class="fw-bolder fs-1 text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            <div class="text-white-50 fs-8 mt-2">{{ $order->paid_at ? 'Dibayar ' . $order->paid_at->format('d/m/Y H:i') : 'Status pembayaran mengikuti order' }}</div>
        </div>
    </div>
</div>

<div class="row g-7 mb-7">
    <div class="col-xl-7">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <h3 class="card-title fw-bold">Pelanggan & Kendaraan</h3>
            </div>
            <div class="card-body pt-0">
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="text-muted fs-8">Pelanggan</div>
                            <div class="fw-bold fs-5">{{ $order->customer->name ?? '-' }}</div>
                            <div class="text-muted">{{ $order->customer->phone ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="text-muted fs-8">Kendaraan</div>
                            <div class="fw-bold fs-5">{{ $order->vehicle->plate_number ?? '-' }}</div>
                            <div class="text-muted">{{ $order->vehicle->brand ?? '' }} {{ $order->vehicle->model ?? '' }} {{ $order->vehicle->year ?? '' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="text-muted fs-8">Ukuran Mobil</div>
                            <div class="fw-bold fs-6">{{ $vehicleSizeMap[$order->vehicle->vehicle_size ?? ''] ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-tile">
                            <div class="text-muted fs-8">Keluhan / Catatan</div>
                            <div class="fw-semibold">{{ $order->complaint ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <h3 class="card-title fw-bold">Mekanik & Kilometer</h3>
            </div>
            <div class="card-body pt-0">
                <div class="row g-4">
                    <div class="col-6"><div class="mini-stat"><span>Jarak Tempuh</span><strong>{{ $order->mileage ? number_format((int) $order->mileage, 0, ',', '.') : '-' }}</strong></div></div>
                    <div class="col-6"><div class="mini-stat"><span>KM Service</span><strong>{{ $order->km_service ? number_format((int) $order->km_service, 0, ',', '.') : '-' }}</strong></div></div>
                    <div class="col-6"><div class="mini-stat"><span>KM Kembali</span><strong>{{ $order->km_return ? number_format((int) $order->km_return, 0, ',', '.') : '-' }}</strong></div></div>
                    <div class="col-6"><div class="mini-stat"><span>No Mekanik</span><strong>{{ $order->mechanic_number ?? '-' }}</strong></div></div>
                    <div class="col-6"><div class="mini-stat"><span>Kepala Mekanik</span><strong>{{ $order->head_mechanic ?? '-' }}</strong></div></div>
                    <div class="col-6"><div class="mini-stat"><span>Mekanik</span><strong>{{ $order->mechanic ?? '-' }}</strong></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Item Checklist --}}
@if($order->items->count() > 0)
<div class="card card-flush mb-7">
    <div class="card-header pt-5">
        <h3 class="card-title fw-bold">Item Pengecekan</h3>
    </div>
    <div class="card-body pt-0">
        <table class="table table-bordered gy-4 gs-4">
            <thead>
                <tr class="fw-semibold fs-7 text-gray-800 bg-light">
                    <th class="w-200px">Kategori</th>
                    <th>Nama Item</th>
                    <th class="text-end">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <span class="badge badge-light-primary">{{ $item->checklistItem?->category?->name ?? '-' }}</span>
                    </td>
                    <td class="fw-semibold">{{ $item->name }}</td>
                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Material --}}
@if($order->materials->count() > 0)
<div class="card card-flush mb-7">
    <div class="card-header pt-5">
        <h3 class="card-title fw-bold">Material yang Digunakan</h3>
    </div>
    <div class="card-body pt-0">
        <table class="table table-bordered gy-4 gs-4">
            <thead>
                <tr class="fw-semibold fs-7 text-gray-800 bg-light">
                    <th>Nama Material</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->materials as $mat)
                <tr>
                    <td>{{ $mat->name }}</td>
                    <td>{{ $mat->qty }}</td>
                    <td>Rp {{ number_format($mat->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($mat->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Eviden & Total --}}
<div class="card card-flush mb-7">
    <div class="card-header pt-5">
        <h3 class="card-title fw-bold">Eviden & Ringkasan Harga</h3>
    </div>
    <div class="card-body pt-0">
        <div class="row g-5 align-items-stretch">
            <div class="col-xl-8">
                <div class="row g-5 h-100">
                    @foreach($evidenceFiles as $title => $files)
                        <div class="col-md-6">
                            <div class="evidence-panel">
                                <div class="fw-bold fs-6 mb-3">{{ $title }}</div>
                                <div class="evidence-gallery">
                                    @forelse($files as $file)
                                        @if($file['is_image'])
                                            <a href="{{ $file['url'] }}" target="_blank" class="evidence-link">
                                                <img src="{{ $file['url'] }}" class="rounded border evidence-thumb" alt="{{ $title }}" />
                                            </a>
                                        @else
                                            <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-light-primary">{{ $file['name'] }}</a>
                                        @endif
                                    @empty
                                        <span class="text-muted">Belum ada eviden.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-xl-4">
                <div class="total-box h-100">
                    <div class="total-row">
                        <span>Subtotal Checklist</span>
                        <strong>Rp {{ number_format($checklistTotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Subtotal Material</span>
                        <strong>Rp {{ number_format($materialTotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Jasa Lainnya</span>
                        <strong>Rp {{ number_format($order->other_service_price ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Subtotal Semua</span>
                        <strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row">
                        <span>Diskon</span>
                        <strong>Rp {{ number_format($order->discount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="total-row grand">
                        <span>Total</span>
                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.order-hero {
    border: 1px solid #e4e8f0;
    border-radius: 20px;
    background: linear-gradient(135deg, #f8fbff 0%, #ffffff 62%);
    padding: 28px;
    box-shadow: 0 16px 42px rgba(15, 23, 42, .06);
    overflow: hidden;
}
.order-status-icon {
    width: 62px;
    height: 62px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.order-number-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #dfe6f2;
    background: #fff;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}
.order-meta-line {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}
.order-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: 1px solid #e4e8f0;
    background: #fff;
    border-radius: 999px;
    padding: 8px 12px;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}
.order-total-panel {
    min-width: 280px;
    border-radius: 18px;
    padding: 24px;
    background: linear-gradient(155deg, #0f172a, #1e293b);
    position: relative;
    overflow: hidden;
}
.order-total-panel::after {
    content: "";
    position: absolute;
    width: 130px;
    height: 130px;
    right: -48px;
    top: -48px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.info-tile,
.mini-stat {
    border: 1px solid #e4e8f0;
    border-radius: 16px;
    padding: 16px;
    height: 100%;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
}
.mini-stat span {
    display: block;
    color: #7e8299;
    font-size: .8rem;
    margin-bottom: 4px;
}
.mini-stat strong {
    color: #181c32;
}
.evidence-panel {
    border: 1px solid #e4e8f0;
    border-radius: 16px;
    min-height: 150px;
    padding: 18px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
}
.evidence-link {
    display: block;
    width: 100%;
    transition: transform .15s ease, box-shadow .15s ease;
}
.evidence-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(24, 28, 50, .08);
}
.evidence-thumb {
    display: block;
    width: 100%;
    height: auto;
    max-height: 360px;
    object-fit: contain;
    background: #f8f9fa;
}
.evidence-gallery {
    display: grid;
    gap: 12px;
}
.total-box {
    border: 1px solid #e4e8f0;
    border-radius: 18px;
    background: linear-gradient(180deg, #fff, #f8fbff);
    padding: 18px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
}
.total-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    border-bottom: 1px solid #eff2f5;
}
.total-row span {
    color: #7e8299;
    font-weight: 600;
}
.total-row strong {
    color: #181c32;
    text-align: right;
}
.total-row.grand {
    border-bottom: 0;
    padding-top: 16px;
    font-size: 1.15rem;
}
.total-row.grand span,
.total-row.grand strong {
    color: #0d6efd;
}
</style>
@endpush
