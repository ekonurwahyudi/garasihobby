@extends('layouts.app')

@section('title', 'Pembelian Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Pembelian Material</li>
@endsection

@section('toolbar_actions')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    @can('purchases.create')
    <a href="{{ route('material-purchases.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
        <i class="ki-duotone ki-plus-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Tambah Pembelian
    </a>
    @endcan
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success d-flex align-items-center p-5 mb-5">
    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
    <div class="fw-semibold">{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger d-flex align-items-center p-5 mb-5">
    <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="fw-semibold">{{ session('error') }}</div>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger mb-5">
    <div class="d-flex flex-column">
        <span class="fw-bold mb-1">Terjadi kesalahan:</span>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

@php
    $tabs = [
        'semua' => [
            'label' => 'Semua',
            'pane' => 'tab_all',
            'icon' => 'ki-abstract-26',
            'icon_color' => 'text-primary',
            'badge' => 'badge-light-primary',
        ],
        'menunggu_approval' => [
            'label' => 'Awaiting',
            'pane' => 'tab_waiting',
            'icon' => 'ki-time',
            'icon_color' => 'text-warning',
            'badge' => 'badge-light-warning',
        ],
        'disetujui' => [
            'label' => 'Disetujui',
            'pane' => 'tab_approved',
            'icon' => 'ki-check-circle',
            'icon_color' => 'text-success',
            'badge' => 'badge-light-success',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'pane' => 'tab_rejected',
            'icon' => 'ki-cross-circle',
            'icon_color' => 'text-danger',
            'badge' => 'badge-light-danger',
        ],
    ];
@endphp

<div class="card card-flush">
    <div class="card-body pt-6">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x purchase-status-tabs mb-5 fs-6">
            @foreach($tabs as $status => $tab)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} fw-semibold" data-bs-toggle="tab" data-status="{{ $status }}" href="#{{ $tab['pane'] }}">
                        <i class="ki-duotone {{ $tab['icon'] }} {{ $tab['icon_color'] }} fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        {{ $tab['label'] }}
                        <span class="badge {{ $tab['badge'] }} ms-2">{{ $status === 'semua' ? $transactions->count() : $transactions->where('status', $status)->count() }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
            <div class="d-flex align-items-center">
                <span class="text-gray-700 fs-7 me-2">Tampilkan</span>
                <select id="lengthSelect" class="form-select form-select-sm w-75px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="dateFrom" class="form-control form-control-sm purchase-date-filter" placeholder="Dari tanggal" />
                    <input type="text" id="dateTo" class="form-control form-control-sm purchase-date-filter" placeholder="Sampai tanggal" />
                </div>
                <div class="d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" id="searchInput" class="form-control w-250px ps-12" placeholder="Cari pembelian..." />
                </div>
            </div>
        </div>

        <div class="tab-content" id="purchaseTabContent">
            @foreach($tabs as $status => $tab)
                @php($tabTransactions = $status === 'semua' ? $transactions : $transactions->where('status', $status)->values())
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab['pane'] }}" role="tabpanel">
                    <table id="table_{{ $status }}" class="table align-middle border rounded purchase-table" data-status="{{ $status }}">
                        <thead>
                            <tr class="fw-semibold fs-6 text-gray-800">
                                <th class="w-50px text-center no-sort" data-orderable="false" data-dt-order="disable">No</th>
                                <th>Tanggal Kwitansi</th>
                                <th>No. Transaksi</th>
                                <th>Material</th>
                                <th>Supplier</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end min-w-100px no-sort" data-orderable="false" data-dt-order="disable">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabTransactions as $i => $transaction)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $transaction->purchase_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $transaction->invoice_number ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold">{{ $transaction->material_summary }}</span>
                                    <div class="text-muted fs-7">{{ $transaction->item_count }} item pembelian</div>
                                </td>
                                <td>{{ $transaction->supplier ?? '-' }}</td>
                                <td>{{ $transaction->qty_summary }}</td>
                                <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                <td>
                                    @switch($transaction->status)
                                        @case('menunggu_approval')
                                            <span class="badge badge-light-warning">Awaiting</span>
                                            @break
                                        @case('disetujui')
                                            <span class="badge badge-light-success">Disetujui</span>
                                            @break
                                        @case('ditolak')
                                            <span class="badge badge-light-danger">Ditolak</span>
                                            @break
                                        @default
                                            <span class="badge badge-light">{{ $transaction->status }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <div class="gh-action-group">
                                    @can('purchases.approve')
                                        @if($transaction->status === 'menunggu_approval')
                                            <form action="{{ route('material-purchases.accept', $transaction->invoice_number) }}" method="POST" class="d-inline accept-purchase-form">
                                                @csrf
                                                <button type="submit" class="gh-action-btn gh-action-approve" title="Accept">
                                                    <i class="ki-duotone ki-check fs-2"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="gh-action-btn gh-action-reject" onclick="openRejectModal('{{ $transaction->invoice_number }}')" title="Reject">
                                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('purchases.edit')
                                        <a class="gh-action-btn gh-action-edit" href="{{ route('material-purchases.edit', $transaction->invoice_number) }}" title="Edit">
                                            <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                        </a>
                                    @endcan
                                    <a class="gh-action-btn gh-action-view" href="{{ route('material-purchases.show', $transaction->invoice_number) }}" title="Detail">
                                        <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </a>
                                    @can('purchases.delete')
                                        <button type="button" class="gh-action-btn gh-action-delete" onclick="deleteTransaction('{{ $transaction->invoice_number }}')" title="Hapus">
                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Reject Pembelian</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body mx-5 my-7">
                    <label class="required form-label fw-semibold">Alasan Penolakan</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.purchase-status-tabs {
    border-bottom-width: 3px;
}
.purchase-status-tabs .nav-link {
    border-bottom-width: 4px;
    padding-bottom: 1rem;
}
.purchase-status-tabs .nav-link.active {
    border-bottom-width: 4px;
}
.purchase-table.dataTable {
    border: 1px solid #dfe5ef;
    border-radius: 12px;
    background: #fff;
}
.purchase-table.dataTable thead th:first-child {
    border-top-left-radius: 12px;
}
.purchase-table.dataTable thead th:last-child {
    border-top-right-radius: 12px;
}
.purchase-table.dataTable tbody tr:last-child td:first-child {
    border-bottom-left-radius: 12px;
}
.purchase-table.dataTable tbody tr:last-child td:last-child {
    border-bottom-right-radius: 12px;
}
.purchase-table {
    margin-bottom: 0 !important;
}
.purchase-table-footer {
    margin-top: 16px !important;
    padding: 0 4px !important;
}
.purchase-table-footer .dataTables_info {
    padding-top: 0 !important;
}
.purchase-table th.no-sort::before,
.purchase-table th.no-sort::after {
    display: none !important;
}
.purchase-date-filter {
    width: 145px;
}
</style>
@endpush

@push('scripts')
<script>
var tables = {};
var activeStatus = 'semua';

function parseTableDate(value) {
    var parts = (value || '').split('/');
    if (parts.length !== 3) return null;
    return new Date(parseInt(parts[2], 10), parseInt(parts[1], 10) - 1, parseInt(parts[0], 10));
}

function parseFilterDate(value) {
    if (!value) return null;
    var parts = value.split('-');
    if (parts.length !== 3) return null;
    return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
}

$.fn.dataTable.ext.search.push(function(settings, data) {
    if (!settings.nTable || !settings.nTable.classList.contains('purchase-table')) return true;

    var rowDate = parseTableDate(data[1]);
    var dateFrom = parseFilterDate($('#dateFrom').val());
    var dateTo = parseFilterDate($('#dateTo').val());

    if (!rowDate) return true;
    if (dateFrom && rowDate < dateFrom) return false;
    if (dateTo && rowDate > dateTo) return false;
    return true;
});

$(document).ready(function() {
    $('.accept-purchase-form').on('submit', function(e) {
        var form = this;
        var button = form.querySelector('button[type="submit"]');

        if (!window.Swal) {
            if (button) button.disabled = true;
            return;
        }

        e.preventDefault();
        Swal.fire({
            title: 'Accept Pembelian?',
            text: 'Stok material dan mutasi uang keluar akan otomatis diproses.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, accept',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            if (button) button.disabled = true;
            form.submit();
        });
    });

    $('.purchase-table').each(function() {
        var status = this.dataset.status;
        tables[status] = $(this).DataTable({
            fixedHeader: { header: true },
            dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row purchase-table-footer'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
            buttons: [{ extend: 'excelHtml5', title: 'Pembelian Material - Garasi Hobby', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] } }],
            order: [],
            pageLength: 10,
            columnDefs: [{ orderable: false, targets: [0, 8] }],
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
    });

    $('#searchInput').on('keyup', function() {
        tables[activeStatus].search(this.value).draw();
    });
    $('#dateFrom, #dateTo').on('change', function() {
        tables[activeStatus].draw();
    });
    $('#dateFrom, #dateTo').flatpickr({
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y'
    });
    $('#lengthSelect').on('change', function() {
        tables[activeStatus].page.len($(this).val()).draw();
    });
    $('#exportExcel').on('click', function(e) {
        e.preventDefault();
        tables[activeStatus].button(0).trigger();
    });
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        activeStatus = e.target.dataset.status;
        $('#searchInput').val('');
        tables[activeStatus].search('').page.len($('#lengthSelect').val()).draw();
        tables[activeStatus].columns.adjust();
    });
});

function deleteTransaction(transaction) {
    Swal.fire({
        title: 'Hapus Pembelian?',
        text: 'Yakin ingin menghapus transaksi "' + transaction + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch('/operasional/pembelian-material/' + encodeURIComponent(transaction), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(function(response) {
            if (response.ok) {
                window.location.reload();
                return;
            }
            Swal.fire('Gagal', 'Transaksi tidak bisa dihapus.', 'error');
        });
    });
}

function openRejectModal(transaction) {
    var form = document.getElementById('rejectForm');
    form.action = '/operasional/pembelian-material/' + encodeURIComponent(transaction) + '/reject';
    form.querySelector('textarea[name="rejection_reason"]').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush
