@extends('layouts.app')

@section('title', 'Detail Persediaan Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('material-inventory.index') }}" class="text-muted text-hover-primary">Persediaan Material</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('material-inventory.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
    @can('materials.edit')
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
        <i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Penyesuaian Stok
    </button>
    @endcan
@endsection

@push('styles')
<style>
.inventory-detail-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}
.inventory-detail-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}
.inventory-photo{width:76px;height:76px;border-radius:18px;border:1px solid #e4e8f0;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.inventory-photo img{width:100%;height:100%;object-fit:cover}.inventory-photo i{color:#94a3b8}
.inventory-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}
.inventory-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.inventory-meta-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}
.inventory-stock-panel{height:100%;padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);color:#fff;position:relative;overflow:hidden}.inventory-stock-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}
.inventory-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}.inventory-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}.inventory-person-card .value{color:#fff;font-weight:700}
.inventory-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}
.inventory-info-card{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:16px;height:100%;box-shadow:0 8px 22px rgba(15,23,42,.035)}
.inventory-info-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.inventory-modal-content{border:0;border-radius:18px;box-shadow:0 24px 70px rgba(15,23,42,.18);overflow:hidden}
.inventory-modal-header{padding:22px 28px;border-bottom:1px solid #edf1f7;background:linear-gradient(135deg,#f8fbff 0%,#fff 72%)}
.inventory-modal-icon{width:46px;height:46px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.inventory-modal-summary{display:flex;align-items:center;gap:14px;border:1px solid #e4e8f0;border-radius:14px;background:#fff;padding:14px 16px;box-shadow:0 8px 20px rgba(15,23,42,.035)}
.inventory-modal-summary-icon{width:46px;height:46px;border-radius:13px;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;background:#f1f5f9}.inventory-modal-summary-icon img{width:100%;height:100%;object-fit:cover}
.inventory-adjust-preview{border:1px solid #e4e8f0;border-radius:14px;background:#f8fafc;padding:14px 16px}.inventory-adjust-preview-value{font-size:22px;font-weight:800;line-height:1}.inventory-adjust-preview-value.is-up{color:#16a34a}.inventory-adjust-preview-value.is-down{color:#dc2626}.inventory-adjust-preview-value.is-flat{color:#475569}
</style>
@endpush

@section('content')
@php
    $statusConfig = match($material->stock_status) {
        'Aman' => ['label' => 'Aman', 'badge' => 'badge-light-success', 'text' => 'text-success', 'bg' => 'bg-light-success', 'icon' => 'ki-check-circle'],
        'Hampir Habis' => ['label' => 'Hampir Habis', 'badge' => 'badge-light-warning', 'text' => 'text-warning', 'bg' => 'bg-light-warning', 'icon' => 'ki-warning-2'],
        default => ['label' => 'Habis', 'badge' => 'badge-light-danger', 'text' => 'text-danger', 'bg' => 'bg-light-danger', 'icon' => 'ki-cross-circle'],
    };
@endphp

@if($errors->any())
<div class="alert alert-danger mb-5"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="inventory-detail-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 inventory-detail-main">
            <div class="d-flex align-items-start gap-4 mb-7">
                <div class="inventory-photo">
                    @if($material->photo_url)
                        <img src="{{ $material->photo_url }}" alt="{{ $material->name }}">
                    @else
                        <i class="ki-outline ki-parcel fs-1"></i>
                    @endif
                </div>
                <div>
                    <div class="inventory-number-pill mb-3"><i class="ki-duotone ki-parcel fs-5"><span class="path1"></span><span class="path2"></span></i>{{ $material->sku ?? 'Tanpa SKU' }}</div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <h1 class="fw-bolder text-gray-900 mb-0 fs-2">{{ $material->name }}</h1>
                        <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                    </div>
                    <div class="text-gray-600">{{ $material->category?->name ?? 'Tanpa kategori' }}</div>
                    <div class="inventory-meta-line">
                        <span class="inventory-meta-chip">Binrow: {{ $material->binrow ?? '-' }}</span>
                        <span class="inventory-meta-chip">Min. Stok: {{ number_format($material->min_stock, 0, ',', '.') }}</span>
                        <span class="inventory-meta-chip">Harga/Unit: Rp {{ number_format($material->cost_price ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4"><div class="inventory-info-card"><div class="d-flex align-items-center gap-3"><div class="inventory-info-icon bg-light-primary"><i class="ki-duotone ki-abstract-26 fs-2 text-primary"></i></div><div><div class="text-muted fs-8 text-uppercase fw-semibold">Stok Saat Ini</div><div class="fw-bold text-gray-900">{{ number_format($material->stock_qty, 0, ',', '.') }}</div></div></div></div></div>
                <div class="col-md-4"><div class="inventory-info-card"><div class="d-flex align-items-center gap-3"><div class="inventory-info-icon bg-light-warning"><i class="ki-duotone ki-time fs-2 text-warning"></i></div><div><div class="text-muted fs-8 text-uppercase fw-semibold">Minimum</div><div class="fw-bold text-gray-900">{{ number_format($material->min_stock, 0, ',', '.') }}</div></div></div></div></div>
                <div class="col-md-4"><div class="inventory-info-card"><div class="d-flex align-items-center gap-3"><div class="inventory-info-icon {{ $statusConfig['bg'] }}"><i class="ki-duotone {{ $statusConfig['icon'] }} fs-2 {{ $statusConfig['text'] }}"></i></div><div><div class="text-muted fs-8 text-uppercase fw-semibold">Status</div><div class="fw-bold text-gray-900">{{ $material->stock_status }}</div></div></div></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="inventory-stock-panel d-flex flex-column justify-content-between">
                <div class="position-relative">
                    <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Estimasi Nilai Stok</div>
                    <div class="fw-bolder fs-1 text-white mb-2">Rp {{ number_format($material->stock_qty * (float) ($material->cost_price ?? 0), 0, ',', '.') }}</div>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="position-relative mt-8">
                    <div class="inventory-person-card mb-3"><div class="label">Update Stok Terakhir</div><div class="value">{{ $material->stock?->updated_at?->format('d/m/Y H:i') ?? '-' }}</div></div>
                    <div class="inventory-person-card"><div class="label">Total History</div><div class="value">{{ $history->count() }} transaksi</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush inventory-section-card mb-7">
    <div class="card-header border-0 pt-6"><div class="card-title"><h3 class="fw-bold mb-0">History Persediaan</h3></div></div>
    <div class="card-body pt-0">
        <table class="table align-middle border rounded">
            <thead><tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Qty Masuk</th><th>Qty Keluar</th><th>Harga/Unit</th><th>Harga Total</th><th>Catatan</th></tr></thead>
            <tbody>
                @forelse($history as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->date?->format('d/m/Y') ?? '-' }}</td>
                    <td><span class="badge {{ $row->qty_in ? 'badge-light-success' : ($row->qty_out ? 'badge-light-danger' : 'badge-light-primary') }}">{{ $row->type }}</span></td>
                    <td class="text-success fw-bold">{{ $row->qty_in ?: '-' }}</td>
                    <td class="text-danger fw-bold">{{ $row->qty_out ?: '-' }}</td>
                    <td>Rp {{ number_format($row->unit_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($row->total_price, 0, ',', '.') }}</td>
                    <td>{{ $row->notes ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-8">Belum ada history persediaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-flush inventory-section-card">
    <div class="card-header border-0 pt-6"><div class="card-title"><h3 class="fw-bold mb-0">Penyesuaian Stok</h3></div></div>
    <div class="card-body pt-0">
        <table class="table align-middle border rounded">
            <thead><tr><th>No</th><th>Tanggal</th><th>Qty Sebelumnya</th><th>Qty Sebenarnya</th><th>Selisih</th><th>Alasan</th><th>Oleh</th></tr></thead>
            <tbody>
                @forelse($adjustments as $i => $adjustment)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $adjustment->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $adjustment->previous_qty }}</td>
                    <td>{{ $adjustment->actual_qty }}</td>
                    <td class="{{ $adjustment->difference_qty >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ $adjustment->difference_qty >= 0 ? '+' : '' }}{{ $adjustment->difference_qty }}</td>
                    <td>{{ $adjustment->reason }}</td>
                    <td>{{ $adjustment->creator?->name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-8">Belum ada penyesuaian stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('materials.edit')
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content inventory-modal-content">
        <div class="modal-header inventory-modal-header">
            <div class="d-flex align-items-center gap-3">
                <span class="inventory-modal-icon bg-light-primary text-primary">
                    <i class="ki-duotone ki-arrows-circle fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                </span>
                <div>
                    <h2 class="fw-bold mb-1">Penyesuaian Stok</h2>
                    <div class="text-muted fs-7">Sesuaikan stok sistem dengan hasil stok aktual.</div>
                </div>
            </div>
            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div>
        </div>
        <form method="POST" action="{{ route('material-inventory.adjust', $material) }}">
            @csrf
            <div class="modal-body px-8 py-7">
                <div class="inventory-modal-summary mb-6">
                    <div class="inventory-modal-summary-icon">
                        @if($material->photo_url)
                            <img src="{{ $material->photo_url }}" alt="{{ $material->name }}">
                        @else
                            <i class="ki-duotone ki-parcel fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted fs-8 text-uppercase fw-semibold">Material</div>
                        <div class="fw-bold text-gray-900 fs-5">{{ $material->name }}</div>
                        <div class="text-muted fs-7">{{ $material->sku ?? 'Tanpa SKU' }} - Binrow: {{ $material->binrow ?? '-' }}</div>
                    </div>
                    <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                </div>
                <div class="row g-5 mb-6">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Qty Sebelumnya</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ki-duotone ki-abstract-26 fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="text" class="form-control" id="previousQty" value="{{ $material->stock_qty }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="required form-label fw-semibold">Qty Sebenarnya</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ki-duotone ki-check-square fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="text" name="actual_qty" id="actualQty" class="form-control" inputmode="numeric" required placeholder="Masukkan qty aktual">
                        </div>
                    </div>
                </div>
                <div class="inventory-adjust-preview d-flex align-items-center justify-content-between gap-4 mb-6">
                    <div>
                        <div class="text-muted fs-8 text-uppercase fw-semibold">Selisih Stok</div>
                        <div class="text-gray-600 fs-7">Dihitung dari qty sebenarnya dikurangi qty sebelumnya.</div>
                    </div>
                    <div class="inventory-adjust-preview-value is-flat" id="adjustDiffPreview">0</div>
                </div>
                <label class="required form-label fw-semibold">Alasan Perubahan</label>
                <textarea name="reason" class="form-control" rows="4" required placeholder="Contoh: hasil stock opname, barang rusak, salah input, dll."></textarea>
            </div>
            <div class="modal-footer flex-center px-8 pb-8 pt-0 border-0"><button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="ki-duotone ki-check fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan Penyesuaian</button></div>
        </form>
    </div></div>
</div>
@endcan
@endsection

@push('scripts')
<script>
var actualQtyInput = document.getElementById('actualQty');
var previousQtyInput = document.getElementById('previousQty');
var adjustDiffPreview = document.getElementById('adjustDiffPreview');

function updateAdjustDiffPreview() {
    if (!actualQtyInput || !previousQtyInput || !adjustDiffPreview) return;

    var previousQty = parseInt(previousQtyInput.value || '0', 10) || 0;
    var actualQty = parseInt((actualQtyInput.value || '').replace(/\D/g, ''), 10);
    var diff = Number.isNaN(actualQty) ? 0 : actualQty - previousQty;

    adjustDiffPreview.textContent = diff > 0 ? '+' + diff : diff.toString();
    adjustDiffPreview.classList.remove('is-up', 'is-down', 'is-flat');
    adjustDiffPreview.classList.add(diff > 0 ? 'is-up' : (diff < 0 ? 'is-down' : 'is-flat'));
}

if (actualQtyInput) {
    actualQtyInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        updateAdjustDiffPreview();
    });
}
</script>
@endpush
