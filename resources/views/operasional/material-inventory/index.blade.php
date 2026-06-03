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
        <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Material</th>
                    <th>Kategori</th>
                    <th>Binrow</th>
                    <th>Stok</th>
                    <th>Min. Stok</th>
                    <th>Status</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <span class="fw-bold">{{ $item->name }}</span>
                        <div class="text-muted fs-7">{{ $item->sku ?? '-' }}</div>
                    </td>
                    <td>{{ $item->category->name ?? '-' }}</td>
                    <td>{{ $item->binrow ?? '-' }}</td>
                    <td>{{ $item->stock_qty }}</td>
                    <td>{{ $item->min_stock }}</td>
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
                        <button class="btn btn-icon btn-sm btn-info" onclick="viewMaterial('{{ $item->id }}')" title="Detail">
                            <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Detail Persediaan Material</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body mx-5 my-7">
                <table class="table table-row-bordered gy-4 gs-5">
                    <tbody>
                        <tr><td class="fw-semibold text-gray-600 w-150px">Material</td><td class="fw-bold text-gray-800" id="v_name"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">SKU</td><td id="v_sku"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Kategori</td><td id="v_category"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Binrow</td><td id="v_binrow"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Stok</td><td id="v_stock"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Stok Minimum</td><td id="v_min_stock"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Harga Jual</td><td id="v_price"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Update Stok</td><td id="v_stock_updated_at"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Status</td><td id="v_status"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table;

$(document).ready(function() {
    table = $('#kt_table').DataTable({
        fixedHeader: { header: true },
        dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        buttons: [{ extend: 'excelHtml5', title: 'Persediaan Material - Garasi Hobby', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } }],
        order: [],
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: [0, 7] }],
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

function formatRupiah(value) {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
}

function viewMaterial(id) {
    fetch('/operasional/persediaan-material/' + id, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('v_name').textContent = data.name || '-';
        document.getElementById('v_sku').textContent = data.sku || '-';
        document.getElementById('v_category').textContent = data.category || '-';
        document.getElementById('v_binrow').textContent = data.binrow || '-';
        document.getElementById('v_stock').textContent = data.stock_qty || 0;
        document.getElementById('v_min_stock').textContent = data.min_stock || 0;
        document.getElementById('v_price').textContent = formatRupiah(data.price);
        document.getElementById('v_stock_updated_at').textContent = data.stock_updated_at || '-';
        document.getElementById('v_status').textContent = data.stock_status || '-';
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    });
}
</script>
@endpush
