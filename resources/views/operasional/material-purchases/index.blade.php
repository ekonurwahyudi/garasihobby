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
    <a href="{{ route('material-purchases.create') }}" class="btn btn-sm btn-primary">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Pembelian
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
            'label' => 'Menunggu Approval',
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
            <div class="d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" id="searchInput" class="form-control w-250px ps-12" placeholder="Cari pembelian..." />
            </div>
        </div>

        <div class="tab-content" id="purchaseTabContent">
            @foreach($tabs as $status => $tab)
                @php($tabTransactions = $status === 'semua' ? $transactions : $transactions->where('status', $status)->values())
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab['pane'] }}" role="tabpanel">
                    <table id="table_{{ $status }}" class="table table-striped table-row-bordered gy-5 gs-7 border rounded purchase-table" data-status="{{ $status }}">
                        <thead>
                            <tr class="fw-semibold fs-6 text-gray-800">
                                <th class="w-50px">No</th>
                                <th>Tanggal Kwitansi</th>
                                <th>No. Transaksi</th>
                                <th>Material</th>
                                <th>Supplier</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabTransactions as $i => $transaction)
                            <tr>
                                <td>{{ $i + 1 }}</td>
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
                                            <span class="badge badge-light-warning">Menunggu Approval</span>
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
                                    @can('purchases.approve')
                                        @if($transaction->status === 'menunggu_approval')
                                            <form action="{{ route('material-purchases.accept', $transaction->invoice_number) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-icon btn-sm btn-success" title="Accept">
                                                    <i class="ki-duotone ki-check fs-3 text-white"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-icon btn-sm btn-danger" onclick="openRejectModal('{{ $transaction->invoice_number }}')" title="Reject">
                                                <i class="ki-duotone ki-cross fs-3 text-white"></i>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('purchases.edit')
                                        <a class="btn btn-icon btn-sm btn-warning" href="{{ route('material-purchases.edit', $transaction->invoice_number) }}" title="Edit">
                                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                                        </a>
                                    @endcan
                                    <a class="btn btn-icon btn-sm btn-info" href="{{ route('material-purchases.show', $transaction->invoice_number) }}" title="Detail">
                                        <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </a>
                                    @can('purchases.delete')
                                        <button type="button" class="btn btn-icon btn-sm btn-danger" onclick="deleteTransaction('{{ $transaction->invoice_number }}')" title="Hapus">
                                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    @endcan
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
</style>
@endpush

@push('scripts')
<script>
var tables = {};
var activeStatus = 'semua';

$(document).ready(function() {
    $('.purchase-table').each(function() {
        var status = this.dataset.status;
        tables[status] = $(this).DataTable({
            fixedHeader: { header: true },
            dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
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
