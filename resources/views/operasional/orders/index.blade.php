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
    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
        <i class="ki-duotone ki-plus-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Input Order
    </a>
    @endcan
@endsection

@section('content')
@php
    $orderTabs = [
        'semua' => ['label' => 'Semua', 'pane' => 'tab_all', 'icon' => 'ki-abstract-26', 'icon_color' => 'text-info', 'badge' => 'badge-light-info'],
        'open' => ['label' => 'Open', 'pane' => 'tab_open', 'icon' => 'ki-notepad', 'icon_color' => 'text-primary', 'badge' => 'badge-light-primary'],
        'draft' => ['label' => 'Draft', 'pane' => 'tab_draft', 'icon' => 'ki-pencil', 'icon_color' => 'text-gray-600', 'badge' => 'badge-light'],
        'belum_bayar' => ['label' => 'Belum Bayar', 'pane' => 'tab_unpaid', 'icon' => 'ki-time', 'icon_color' => 'text-warning', 'badge' => 'badge-light-warning'],
        'selesai' => ['label' => 'Selesai', 'pane' => 'tab_completed', 'icon' => 'ki-check-circle', 'icon_color' => 'text-success', 'badge' => 'badge-light-success'],
    ];
    $orderStats = [
        ['label' => 'Total Order', 'value' => $data->count(), 'hint' => 'Semua status', 'icon' => 'ki-abstract-26', 'tone' => 'info'],
        ['label' => 'Open', 'value' => $data->where('status', 'open')->count(), 'hint' => 'Sedang berjalan', 'icon' => 'ki-notepad', 'tone' => 'primary'],
        ['label' => 'Menunggu Bayar', 'value' => $data->where('status', 'belum_bayar')->count(), 'hint' => 'Perlu pembayaran', 'icon' => 'ki-time', 'tone' => 'warning'],
    ];
    $revenue = $data->where('status', 'selesai')->sum('total');
@endphp

<div class="order-stat-grid mb-7">
    @foreach($orderStats as $stat)
    <div>
        <div class="order-stat-card order-stat-{{ $stat['tone'] }}">
            <span class="order-stat-icon"><i class="ki-duotone {{ $stat['icon'] }} fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">{{ $stat['label'] }}</div>
                <div class="order-stat-value">{{ number_format($stat['value'], 0, ',', '.') }}</div>
                <div class="order-stat-hint">{{ $stat['hint'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="order-stat-revenue-col">
        <div class="order-stat-card order-stat-revenue">
            <span class="order-stat-icon"><i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Revenue</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($revenue, 0, ',', '.') }}</div>
                <div class="order-stat-hint">Order selesai</div>
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
                        <span class="badge {{ $tab['badge'] }} ms-2">{{ $status === 'semua' ? $data->count() : $data->where('status', $status)->count() }}</span>
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
                @php($tabOrders = $status === 'semua' ? $data->values() : $data->where('status', $status)->values())
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab['pane'] }}" role="tabpanel">
                    <table id="table_{{ $status }}" class="table align-middle border rounded order-table" data-status="{{ $status }}">
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
    border: 1px solid #e4e8f0;
    border-radius: 12px;
    background: #fff;
    padding: 18px;
    min-height: 118px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    height: 100%;
    transition: transform .15s ease, box-shadow .15s ease;
}
.order-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
}
.order-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
}
.order-stat-hint {
    color: #8a96a8;
    font-size: 12px;
    font-weight: 500;
    margin-top: 8px;
}
.order-stat-value {
    color: #061535;
    font-size: 22px;
    font-weight: 750;
    line-height: 1.2;
    margin-top: 8px;
    overflow-wrap: anywhere;
}
.order-stat-currency {
    font-size: clamp(18px, 1.35vw, 22px);
    line-height: 1.2;
    white-space: nowrap;
    overflow-wrap: normal;
}
.order-stat-label {
    color: #64748b;
    font-size: 12px;
    font-weight: 650;
    text-transform: uppercase;
}
.order-stat-info .order-stat-icon { background: #e7f9ff; color: #00a3c7; }
.order-stat-primary .order-stat-icon { background: #e8f3ff; color: #1682ff; }
.order-stat-warning .order-stat-icon { background: #fff3d8; color: #ff9f0a; }
.order-stat-success .order-stat-icon,
.order-stat-revenue .order-stat-icon { background: #e7f8ef; color: #12a150; }
.order-stat-danger .order-stat-icon { background: #ffecef; color: #f1416c; }
.order-stat-icon i { color: currentColor !important; }
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
.order-table.dataTable {
    border: 1px solid #dfe5ef;
    border-radius: 12px;
    background: #fff;
}
.order-table.dataTable thead th:first-child {
    border-top-left-radius: 12px;
}
.order-table.dataTable thead th:last-child {
    border-top-right-radius: 12px;
}
.order-table.dataTable tbody tr:last-child td:first-child {
    border-bottom-left-radius: 12px;
}
.order-table.dataTable tbody tr:last-child td:last-child {
    border-bottom-right-radius: 12px;
}
.order-table {
    margin-bottom: 0 !important;
}
.order-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}
@media (max-width: 1199.98px) {
    .order-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .order-stat-revenue-col {
        grid-column: span 2;
    }
}
@media (max-width: 575.98px) {
    .order-stat-grid {
        grid-template-columns: 1fr;
    }
    .order-stat-revenue-col {
        grid-column: auto;
    }
    .order-stat-card {
        min-height: 108px;
    }
}
</style>
@endpush

@push('scripts')
<script>
var tables = {};
var activeStatus = 'semua';

$(document).ready(function() {
    $('.order-table').each(function() {
        var status = this.dataset.status;
        tables[status] = $(this).DataTable({
            fixedHeader: { header: true },
            dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
            buttons: [{ extend: 'excelHtml5', title: 'Order Management - ' + status + ' - Garasi Hobby', exportOptions: { columns: [0,1,2,3,4,5,6] } }],
            ordering: false,
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
