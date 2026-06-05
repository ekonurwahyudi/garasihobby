@extends('layouts.app')

@section('title', 'Persediaan Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Persediaan Material</li>
@endsection

@section('toolbar_actions')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
@endsection

@section('content')
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-5 mb-7">
    <div class="col">
        <div class="inventory-stat-card inventory-stat-primary h-100">
            <div class="inventory-stat-top"><span class="inventory-stat-icon"><i class="ki-duotone ki-parcel fs-2"><span class="path1"></span><span class="path2"></span></i></span><span class="inventory-stat-hint">Item aktif</span></div>
            <div class="inventory-stat-value">{{ number_format($stats['total_material'], 0, ',', '.') }}</div>
            <div class="inventory-stat-label">Total Material</div>
        </div>
    </div>
    <div class="col">
        <div class="inventory-stat-card inventory-stat-info h-100">
            <div class="inventory-stat-top"><span class="inventory-stat-icon"><i class="ki-duotone ki-abstract-26 fs-2"><span class="path1"></span><span class="path2"></span></i></span><span class="inventory-stat-hint">Qty tersedia</span></div>
            <div class="inventory-stat-value">{{ number_format($stats['total_stock'], 0, ',', '.') }}</div>
            <div class="inventory-stat-label">Total Stok</div>
        </div>
    </div>
    <div class="col">
        <div class="inventory-stat-card inventory-stat-warning h-100" role="button" data-bs-toggle="modal" data-bs-target="#lowStockModal">
            <div class="inventory-stat-top"><span class="inventory-stat-icon"><i class="ki-duotone ki-warning-2 fs-2"><span class="path1"></span><span class="path2"></span></i></span><span class="inventory-stat-hint">Klik lihat data</span></div>
            <div class="inventory-stat-value">{{ number_format($stats['low_stock'], 0, ',', '.') }}</div>
            <div class="inventory-stat-label">Stok Kurang</div>
        </div>
    </div>
    <div class="col">
        <div class="inventory-stat-card inventory-stat-success h-100">
            <div class="inventory-stat-top"><span class="inventory-stat-icon"><i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span><span class="inventory-stat-hint">Harga modal</span></div>
            <div class="inventory-stat-value inventory-stat-currency">Rp {{ number_format($stats['stock_value'], 0, ',', '.') }}</div>
            <div class="inventory-stat-label">Estimasi Nilai Stok</div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center">
                <span class="text-gray-700 fs-7 me-2">Tampilkan</span>
                <select id="lengthSelect" class="form-select form-select-sm w-75px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" id="searchInput" class="form-control w-250px ps-12" placeholder="Cari persediaan..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th class="w-80px">Foto</th>
                    <th>Material</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Min. Stok</th>
                    <th>Binrow</th>
                    <th>Status</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="symbol symbol-45px overflow-hidden bg-light">
                            @if($item->photo_url)
                                <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="object-fit-cover w-100 h-100">
                            @else
                                <div class="symbol-label bg-light-primary text-primary">
                                    <i class="ki-duotone ki-picture fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold">{{ $item->name }}</span>
                        <div class="text-muted fs-7">{{ $item->sku ?? '-' }}</div>
                    </td>
                    <td>{{ $item->category->name ?? '-' }}</td>
                    <td>{{ $item->stock_qty }}</td>
                    <td>{{ $item->min_stock }}</td>
                    <td>{{ $item->binrow ?? '-' }}</td>
                    <td>
                        @if($item->stock_status === 'Aman')
                            <span class="badge badge-light-success">Aman</span>
                        @elseif($item->stock_status === 'Hampir Habis')
                            <span class="badge badge-light-warning">Hampir Habis</span>
                        @else
                            <span class="badge badge-light-danger">Habis</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a class="btn btn-icon btn-sm btn-info" href="{{ route('material-inventory.show', $item) }}" title="Detail">
                            <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </a>
                        @can('materials.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}', @js($item->name), @js($item->binrow), '{{ $item->min_stock }}')" title="Edit Binrow & Stok Minimum">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editStockMetaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content inventory-modal-content">
            <div class="modal-header inventory-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="inventory-modal-icon bg-light-warning text-warning">
                        <i class="ki-duotone ki-pencil fs-2 text-warning"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                    <div>
                        <h2 class="fw-bold mb-1">Edit Lokasi & Minimum Stok</h2>
                        <div class="text-muted fs-7">Atur lokasi binrow dan batas minimum material.</div>
                    </div>
                </div>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="editStockMetaForm">
                <div class="modal-body px-8 py-7">
                    <div class="inventory-modal-summary mb-6">
                        <div class="inventory-modal-summary-icon bg-light-primary text-primary">
                            <i class="ki-duotone ki-parcel fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <div>
                            <div class="text-muted fs-8 text-uppercase fw-semibold">Material</div>
                            <div class="fw-bold text-gray-900 fs-5" id="editMaterialName"></div>
                        </div>
                    </div>
                    <div id="formErrors" class="alert alert-danger d-none mb-6"><ul class="mb-0" id="formErrorList"></ul></div>
                    <input type="hidden" id="editMaterialId" />
                    <div class="row g-5">
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold">Binrow</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ki-duotone ki-geolocation fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="text" id="editBinrow" name="binrow" class="form-control" maxlength="50" placeholder="Contoh: A-01" />
                            </div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Stok Minimum</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ki-duotone ki-chart-line-down fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="text" id="editMinStock" name="min_stock" class="form-control" inputmode="numeric" required placeholder="0" />
                            </div>
                        </div>
                    </div>
                    <div class="notice bg-light-warning rounded border-warning border border-dashed p-4 mt-6">
                        <div class="d-flex align-items-start gap-3">
                            <i class="ki-duotone ki-information-5 fs-2 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="text-gray-700 fs-7">Status stok akan berubah otomatis mengikuti stok aktual dan nilai minimum ini.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center px-8 pb-8 pt-0 border-0">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-check fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Material Stok Kurang</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle border rounded">
                        <thead><tr><th>Material</th><th>Stok</th><th>Minimum</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($data->filter(fn ($material) => $material->stock_status !== 'Aman') as $material)
                            <tr>
                                <td class="fw-bold">{{ $material->name }}<div class="text-muted fs-8">{{ $material->sku ?? '-' }}</div></td>
                                <td>{{ $material->stock_qty }}</td>
                                <td>{{ $material->min_stock }}</td>
                                <td><span class="badge {{ $material->stock_status === 'Habis' ? 'badge-light-danger' : 'badge-light-warning' }}">{{ $material->stock_status }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-7">Semua stok material aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.inventory-stat-card{position:relative;overflow:hidden;border:1px solid #e4e8f0;border-radius:18px;background:#fff;padding:20px;min-height:140px;box-shadow:0 12px 28px rgba(15,23,42,.05);transition:transform .15s ease,box-shadow .15s ease}
.inventory-stat-card::after{content:"";position:absolute;width:118px;height:118px;right:-48px;top:-48px;border-radius:50%;background:rgba(255,255,255,.72)}
.inventory-stat-card:hover{transform:translateY(-2px);box-shadow:0 16px 34px rgba(15,23,42,.08)}
.inventory-stat-top{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
.inventory-stat-icon{width:44px;height:44px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.86)}
.inventory-stat-hint{color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase}
.inventory-stat-value{position:relative;z-index:1;color:#0f172a;font-size:30px;font-weight:800;line-height:1}
.inventory-stat-currency{font-size:20px;line-height:1.2}
.inventory-stat-label{position:relative;z-index:1;color:#64748b;font-size:13px;font-weight:700;margin-top:8px}
.inventory-stat-primary{background:linear-gradient(135deg,#eef4ff 0%,#fff 70%)}
.inventory-stat-info{background:linear-gradient(135deg,#eff6ff 0%,#fff 70%)}
.inventory-stat-warning{background:linear-gradient(135deg,#fff8e6 0%,#fff 70%)}
.inventory-stat-success{background:linear-gradient(135deg,#ecfdf3 0%,#fff 70%)}
.inventory-stat-primary .inventory-stat-icon{color:#1b84ff}.inventory-stat-info .inventory-stat-icon{color:#0ea5e9}.inventory-stat-warning .inventory-stat-icon{color:#f59e0b}.inventory-stat-success .inventory-stat-icon{color:#12a150}
.inventory-modal-content{border:0;border-radius:18px;box-shadow:0 24px 70px rgba(15,23,42,.18);overflow:hidden}
.inventory-modal-header{padding:22px 28px;border-bottom:1px solid #edf1f7;background:linear-gradient(135deg,#f8fbff 0%,#fff 72%)}
.inventory-modal-icon{width:46px;height:46px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
.inventory-modal-summary{display:flex;align-items:center;gap:14px;border:1px solid #e4e8f0;border-radius:14px;background:#fff;padding:14px 16px;box-shadow:0 8px 20px rgba(15,23,42,.035)}
.inventory-modal-summary-icon{width:42px;height:42px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0}
</style>
@endpush

@push('scripts')
<script>
var table;

$(document).ready(function() {
    table = $('#kt_table').DataTable({
        fixedHeader: { header: true },
        dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        buttons: [{ extend: 'excelHtml5', title: 'Persediaan Material - Garasi Hobby', exportOptions: { columns: [0, 2, 3, 4, 5, 6, 7] } }],
        order: [],
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: [0, 1, 8] }],
        language: {
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(filter dari _MAX_ total data)",
            paginate: {
                first: '<i class="ki-duotone ki-double-left fs-4"></i>',
                last: '<i class="ki-duotone ki-double-right fs-4"></i>',
                next: '<i class="ki-duotone ki-right fs-4"></i>',
                previous: '<i class="ki-duotone ki-left fs-4"></i>',
            }
        }
    });

    $('#searchInput').on('keyup', function() { table.search(this.value).draw(); });
    $('#lengthSelect').on('change', function() { table.page.len($(this).val()).draw(); });
    $('#exportExcel').on('click', function(e) { e.preventDefault(); table.button(0).trigger(); });
});

function openEditModal(id, name, binrow, minStock) {
    document.getElementById('editMaterialId').value = id;
    document.getElementById('editMaterialName').textContent = name;
    document.getElementById('editBinrow').value = binrow || '';
    document.getElementById('editMinStock').value = minStock || 0;
    document.getElementById('formErrors').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('editStockMetaModal')).show();
}

document.getElementById('editStockMetaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('editMaterialId').value;
    var formData = new FormData(this);
    fetch('/operasional/persediaan-material/' + id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: formData
    }).then(function(response) {
        if (response.ok) {
            window.location.reload();
            return;
        }
        return response.json().then(function(data) {
            var list = document.getElementById('formErrorList');
            list.innerHTML = '';
            Object.values(data.errors || {}).forEach(function(messages) {
                messages.forEach(function(message) { list.innerHTML += '<li>' + message + '</li>'; });
            });
            document.getElementById('formErrors').classList.remove('d-none');
        });
    });
});
</script>
@endpush
