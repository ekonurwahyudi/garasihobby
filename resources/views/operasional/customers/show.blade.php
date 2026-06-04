@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@php
    use Illuminate\Support\Facades\Storage;

    $vehicleSizeLabels = [
        'small' => 'S',
        'medium' => 'M',
        'large' => 'L',
    ];
    $vehicleSizeDescriptions = [
        'small' => 'City Car / Hatchback / Sedan Kecil',
        'medium' => 'MPV / SUV Medium / Pickup Ringan',
        'large' => 'SUV Besar / Double Cabin / Ladder Frame',
    ];
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('customers.index') }}" class="text-muted text-hover-primary">Pelanggan</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
@endsection

@section('content')
<div class="customer-hero mb-7">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-6">
        <div class="d-flex align-items-start gap-4">
            <div class="customer-avatar">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div>
                <div class="customer-number-pill mb-3">
                    <i class="ki-duotone ki-user fs-5"><span class="path1"></span><span class="path2"></span></i>
                    Pelanggan Garasi Hobby
                </div>
                <h1 class="fw-bolder fs-2 text-gray-900 mb-2">{{ $customer->name }}</h1>
                <div class="text-gray-600">Detail pemilik dan kendaraan yang terdaftar.</div>
                <div class="customer-meta-line">
                    <span class="customer-meta-chip">{{ $customer->phone }}</span>
                    <span class="customer-meta-chip">{{ $customer->email ?? 'Email belum diisi' }}</span>
                    <span class="customer-meta-chip">{{ $customer->vehicles->count() }} kendaraan</span>
                </div>
            </div>
        </div>
        <div class="customer-total-panel">
            <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Total Kendaraan</div>
            <div class="fw-bolder fs-1 text-white">{{ $customer->vehicles->count() }}</div>
            <div class="text-white-50 fs-8 mt-2">Kendaraan aktif di data pelanggan.</div>
        </div>
    </div>
</div>

<div class="row g-7">
    <div class="col-xl-4">
        <div class="card card-flush h-100 customer-section-card">
            <div class="card-header pt-6">
                <div class="card-title">
                    <h2 class="fw-bold mb-0">Data Pemilik</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center mb-7">
                    <div class="customer-avatar customer-avatar-sm me-4">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold fs-4 text-gray-900">{{ $customer->name }}</div>
                        <div class="text-muted fs-7">Pelanggan</div>
                    </div>
                </div>

                <div class="separator separator-dashed mb-6"></div>
                <div class="mb-5">
                    <div class="text-muted fs-7 mb-1">No HP</div>
                    <div class="fw-semibold fs-6">{{ $customer->phone }}</div>
                </div>
                <div class="mb-5">
                    <div class="text-muted fs-7 mb-1">Email</div>
                    <div class="fw-semibold fs-6">{{ $customer->email ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted fs-7 mb-1">Jumlah Kendaraan</div>
                    <div class="fw-semibold fs-6">{{ $customer->vehicles->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card card-flush customer-section-card">
            <div class="card-header pt-6">
                <div class="card-title">
                    <h2 class="fw-bold mb-0">Data Kendaraan</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                @forelse($customer->vehicles as $vehicle)
                    @php($photoUrl = $vehicle->photo_path ? Storage::disk('r2')->url($vehicle->photo_path) : null)
                    <div class="customer-vehicle-panel p-5 {{ !$loop->last ? 'mb-5' : '' }}">
                        <div class="row g-5 align-items-stretch">
                            <div class="col-md-5">
                                @if($photoUrl)
                                    <a href="{{ $photoUrl }}" target="_blank" class="d-block customer-vehicle-photo-wrap h-100">
                                        <img src="{{ $photoUrl }}" alt="Foto {{ $vehicle->plate_number }}" class="w-100 h-100 customer-vehicle-photo">
                                    </a>
                                @else
                                    <div class="customer-vehicle-empty d-flex flex-column align-items-center justify-content-center h-100">
                                        <i class="ki-duotone ki-car fs-3x text-gray-500 mb-3"><span class="path1"></span><span class="path2"></span></i>
                                        <div class="text-muted fw-semibold">Belum ada foto mobil</div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start mb-5">
                                    <div>
                                        <div class="text-muted fs-7 mb-1">Plat Mobil</div>
                                        <div class="fw-bold fs-2 text-gray-900">{{ $vehicle->plate_number }}</div>
                                    </div>
                                    @if($vehicle->vehicle_size)
                                        <span class="badge badge-light-primary fs-7">
                                            {{ $vehicleSizeLabels[$vehicle->vehicle_size] ?? strtoupper($vehicle->vehicle_size) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="row g-4">
                                    <div class="col-sm-6">
                                        <div class="text-muted fs-7 mb-1">Merk</div>
                                        <div class="fw-semibold">{{ $vehicle->brand ?? '-' }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-muted fs-7 mb-1">Jenis Mobil</div>
                                        <div class="fw-semibold">{{ $vehicle->model ?? '-' }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-muted fs-7 mb-1">Tahun</div>
                                        <div class="fw-semibold">{{ $vehicle->year ?? '-' }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-muted fs-7 mb-1">Ukuran Mobil</div>
                                        <div class="fw-semibold">
                                            @if($vehicle->vehicle_size)
                                                {{ $vehicleSizeLabels[$vehicle->vehicle_size] ?? strtoupper($vehicle->vehicle_size) }}
                                                <span class="text-muted d-block fs-8">{{ $vehicleSizeDescriptions[$vehicle->vehicle_size] ?? '' }}</span>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="border rounded p-7 text-center text-muted">
                        <i class="ki-duotone ki-car fs-3x mb-3"><span class="path1"></span><span class="path2"></span></i>
                        <div class="fw-semibold">Belum ada kendaraan terdaftar.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.customer-hero {
    border: 1px solid #e4e8f0;
    border-radius: 20px;
    background: linear-gradient(135deg, #f8fbff 0%, #ffffff 62%);
    padding: 28px;
    box-shadow: 0 16px 42px rgba(15,23,42,.06);
}
.customer-avatar {
    width: 68px;
    height: 68px;
    border-radius: 20px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1b84ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 900;
    flex-shrink: 0;
}
.customer-avatar-sm {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    font-size: 24px;
}
.customer-number-pill {
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
.customer-meta-line {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}
.customer-meta-chip {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e4e8f0;
    background: #fff;
    border-radius: 999px;
    padding: 8px 12px;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}
.customer-total-panel {
    min-width: 240px;
    border-radius: 18px;
    padding: 24px;
    background: linear-gradient(155deg, #0f172a, #1e293b);
    position: relative;
    overflow: hidden;
}
.customer-total-panel::after {
    content: "";
    position: absolute;
    width: 130px;
    height: 130px;
    right: -48px;
    top: -48px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.customer-section-card {
    border: 1px solid #e4e8f0;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(15,23,42,.045);
}
.customer-vehicle-photo,
.customer-vehicle-empty {
    min-height: 260px;
    object-fit: cover;
}

.customer-vehicle-panel {
    background: #fff;
    border: 1px solid #e4e8f0;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15,23,42,.04);
}
.customer-vehicle-photo-wrap,
.customer-vehicle-empty {
    border: 1px solid #e4e8f0;
    border-radius: 16px;
    overflow: hidden;
    background: #f8fafc;
}
</style>
@endpush
