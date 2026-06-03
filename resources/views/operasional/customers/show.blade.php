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
<div class="row g-7">
    <div class="col-xl-4">
        <div class="card card-flush h-100">
            <div class="card-header pt-6">
                <div class="card-title">
                    <h2 class="fw-bold mb-0">Data Pemilik</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center mb-7">
                    <div class="symbol symbol-60px symbol-circle bg-light-primary me-4">
                        <span class="symbol-label fw-bold fs-2 text-primary">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
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
        <div class="card card-flush">
            <div class="card-header pt-6">
                <div class="card-title">
                    <h2 class="fw-bold mb-0">Data Kendaraan</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                @forelse($customer->vehicles as $vehicle)
                    @php($photoUrl = $vehicle->photo_path ? Storage::disk('r2')->url($vehicle->photo_path) : null)
                    <div class="customer-vehicle-panel border rounded p-5 {{ !$loop->last ? 'mb-5' : '' }}">
                        <div class="row g-5 align-items-stretch">
                            <div class="col-md-5">
                                @if($photoUrl)
                                    <a href="{{ $photoUrl }}" target="_blank" class="d-block border rounded overflow-hidden bg-light h-100">
                                        <img src="{{ $photoUrl }}" alt="Foto {{ $vehicle->plate_number }}" class="w-100 h-100 customer-vehicle-photo">
                                    </a>
                                @else
                                    <div class="border rounded bg-light d-flex flex-column align-items-center justify-content-center h-100 customer-vehicle-empty">
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
.customer-vehicle-photo,
.customer-vehicle-empty {
    min-height: 260px;
    object-fit: cover;
}

.customer-vehicle-panel {
    background: #fff;
}
</style>
@endpush
