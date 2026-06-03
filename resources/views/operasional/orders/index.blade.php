@extends('layouts.app')

@section('title', 'Order Management')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Order Management</li>
@endsection

@section('toolbar_actions')
    @can('orders.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Input Order
    </a>
    @endcan
@endsection

@section('content')
@php
    $orderTabs = [
        'open' => ['label' => 'Open', 'pane' => 'tab_open', 'icon' => 'ki-notepad', 'icon_color' => 'text-primary', 'badge' => 'badge-light-primary'],
        'draft' => ['label' => 'Draft', 'pane' => 'tab_draft', 'icon' => 'ki-pencil', 'icon_color' => 'text-gray-600', 'badge' => 'badge-light'],
        'belum_bayar' => ['label' => 'Belum Bayar', 'pane' => 'tab_unpaid', 'icon' => 'ki-time', 'icon_color' => 'text-warning', 'badge' => 'badge-light-warning'],
        'selesai' => ['label' => 'Selesai', 'pane' => 'tab_completed', 'icon' => 'ki-check-circle', 'icon_color' => 'text-success', 'badge' => 'badge-light-success'],
    ];
    $orderStats = [
        ['label' => 'Open', 'value' => $data->where('status', 'open')->count(), 'icon' => 'ki-notepad', 'color' => 'primary'],
        ['label' => 'Menunggu Bayar', 'value' => $data->where('status', 'belum_bayar')->count(), 'icon' => 'ki-time', 'color' => 'warning'],
        ['label' => 'Selesai', 'value' => $data->where('status', 'selesai')->count(), 'icon' => 'ki-check-circle', 'color' => 'success'],
        ['label' => 'Total Order', 'value' => $data->count(), 'icon' => 'ki-abstract-26', 'color' => 'info'],
    ];
    $revenue = $data->where('status', 'selesai')->sum('total');
@endphp

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-5 mb-7">
    @foreach($orderStats as $stat)
    <div class="col">
        <div class="card card-flush h-100 order-stat-card">
            <div class="card-body d-flex align-items-center gap-4">
                <div class="symbol symbol-50px">
                    <div class="symbol-label bg-light-{{ $stat['color'] }}">
                        <i class="ki-duotone {{ $stat['icon'] }} fs-2x text-{{ $stat['color'] }}"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 fw-semibold fs-7 mb-1">{{ $stat['label'] }}</div>
                    <div class="text-gray-900 fw-bold fs-2">{{ number_format($stat['value'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col">
        <div class="card card-flush h-100 order-stat-card">
            <div class="card-body d-flex align-items-center gap-4">
                <div class="symbol symbol-50px">
                    <div class="symbol-label bg-light-success">
                        <i class="ki-duotone ki-dollar fs-2x text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </div>
                </div>
                <div>
                    <div class="text-gray-500 fw-semibold fs-7 mb-1">Revenue</div>
                    <div class="text-gray-900 fw-bold fs-4">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8">Order selesai</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-body pt-6">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x order-status-tabs mb-5 fs-6">
            @foreach($orderTabs as $status => $tab)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }} fw-semibold" data-bs-toggle="tab" data-status="{{ $status }}" href="#{{ $tab['pane'] }}">
                        <i class="ki-duotone {{ $tab['icon'] }} {{ $tab['icon_color'] }} fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        {{ $tab['label'] }}
                        <span class="badge {{ $tab['badge'] }} ms-2">{{ $data->where('status', $status)->count() }}</span>
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
                <input type="text" id="searchInput" class="form-control w-250px ps-12" placeholder="Cari order..." />
            </div>
        </div>

        <div class="tab-content" id="orderTabContent">
            @foreach($orderTabs as $status => $tab)
                @php($tabOrders = $data->where('status', $status)->values())
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab['pane'] }}" role="tabpanel">
                    <table id="table_{{ $status }}" class="table table-striped table-row-bordered gy-5 gs-7 border rounded order-table" data-status="{{ $status }}">
                        <thead>
                            <tr class="fw-semibold fs-6 text-gray-800">
                                <th class="w-50px">No</th>
                                <th>No Order</th>
                                <th>Tanggal</th>
                                <th>Plat Mobil</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabOrders as $i => $order)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><span class="fw-bold">{{ $order->order_number }}</span></td>
                                <td>{{ $order->order_date->format('d/m/Y') }}</td>
                                <td>{{ $order->vehicle->plate_number ?? '-' }}</td>
                                <td>{{ $order->customer->name ?? '-' }}</td>
                                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td>
                                    @switch($order->status)
                                        @case('draft') <span class="badge badge-light">Draft</span> @break
                                        @case('open') <span class="badge badge-light-primary">Open</span> @break
                                        @case('belum_bayar') <span class="badge badge-light-warning">Belum Bayar</span> @break
                                        @case('selesai') <span class="badge badge-light-success">Selesai</span> @break
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    @can('orders.view')
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-icon btn-sm btn-info" title="Detail">
                                        <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </a>
                                    @if($order->status === 'selesai')
                                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-icon btn-sm btn-success" title="Download Invoice">
                                        <i class="ki-duotone ki-file-down fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                    @endif
                                    @endcan
                                    @can('orders.edit')
                                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-icon btn-sm btn-warning" title="Edit">
                                        <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                                    </a>
                                    @endcan
                                    @can('orders.delete')
                                    @if($order->status === 'draft' || auth()->user()?->hasRole('Superadmin'))
                                    <button class="btn btn-icon btn-sm btn-danger" onclick="deleteOrder('{{ $order->id }}', '{{ $order->order_number }}')" title="Hapus">
                                        <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                    @endif
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
@endsection

@push('styles')
<style>
.order-stat-card {
    border: 1px solid #eff2f5;
    transition: transform .15s ease, box-shadow .15s ease;
}
.order-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(24, 28, 50, .08);
}
.order-status-tabs {
    border-bottom-width: 3px;
}
.order-status-tabs .nav-link {
    border-bottom-width: 4px;
    padding-bottom: 1rem;
}
.order-status-tabs .nav-link.active {
    border-bottom-width: 4px;
}
</style>
@endpush

@push('scripts')
<script>
var tables = {};
var activeStatus = 'open';

$(document).ready(function() {
    $('.order-table').each(function() {
        var status = this.dataset.status;
        tables[status] = $(this).DataTable({
            fixedHeader: { header: true },
            dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
            buttons: [{ extend: 'excelHtml5', title: 'Order Management - ' + status + ' - Garasi Hobby', exportOptions: { columns: [0,1,2,3,4,5,6] } }],
            order: [[2, 'desc']],
            pageLength: 10,
            columnDefs: [{ orderable: false, targets: [0, 7] }],
            language: {
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(filter dari _MAX_ total data)",
                paginate: { first: '<i class="ki-duotone ki-double-left fs-4"></i>', last: '<i class="ki-duotone ki-double-right fs-4"></i>', next: '<i class="ki-duotone ki-right fs-4"></i>', previous: '<i class="ki-duotone ki-left fs-4"></i>' }
            }
        });
    });

    $('#searchInput').on('keyup', function() { tables[activeStatus].search(this.value).draw(); });
    $('#lengthSelect').on('change', function() { tables[activeStatus].page.len($(this).val()).draw(); });
    $('#exportExcel').on('click', function(e) { e.preventDefault(); tables[activeStatus].button(0).trigger(); });
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        activeStatus = e.target.dataset.status;
        $('#searchInput').val('');
        tables[activeStatus].search('').page.len($('#lengthSelect').val()).draw();
        tables[activeStatus].columns.adjust();
    });
});

function deleteOrder(id, number) {
    Swal.fire({ title: 'Hapus Order?', text: 'Yakin ingin menghapus order "' + number + '"?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' }).then(result => {
        if (result.isConfirmed) {
            fetch('/operasional/orders/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } }).then(() => window.location.reload());
        }
    });
}
</script>
@endpush
