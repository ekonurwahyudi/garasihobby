@extends('layouts.app')

@section('title', 'Input Keuangan')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Keuangan</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Input Keuangan</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('finance-transactions.balance-sheet') }}" class="btn btn-sm btn-info d-inline-flex align-items-center gap-2">
        <i class="ki-duotone ki-chart-pie-simple fs-3"><span class="path1"></span><span class="path2"></span></i> Neraca Keuangan
    </a>
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    @can('finance-transactions.create')
    <button type="button" class="btn btn-sm btn-light-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#importFinanceModal">
        <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i> Import Excel
    </button>
    <a href="{{ route('finance-transactions.create') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2">
        <i class="ki-duotone ki-plus-circle fs-2"><span class="path1"></span><span class="path2"></span></i> Tambah Transaksi
    </a>
    @endcan
@endsection

@push('styles')
<style>
    .finance-table-card {
        border: 1px solid #e4e8f0;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }
    .finance-table-card .card-header {
        border-bottom: 0;
    }
    #kt_table.dataTable {
        border: 1px solid #dfe5ef;
        border-radius: 12px;
        background: #fff;
    }
    #kt_table.dataTable thead th:first-child {
        border-top-left-radius: 12px;
    }
    #kt_table.dataTable thead th:last-child {
        border-top-right-radius: 12px;
    }
    #kt_table.dataTable tbody tr:last-child td:first-child {
        border-bottom-left-radius: 12px;
    }
    #kt_table.dataTable tbody tr:last-child td:last-child {
        border-bottom-right-radius: 12px;
    }
    #kt_table {
        margin-bottom: 0 !important;
        border: 0 !important;
        color: #17213b;
    }
    #kt_table thead th {
        background: #f3f6fa;
        color: #061535;
        font-size: 13px;
        font-weight: 700;
        padding: 14px 12px;
        border-bottom: 1px solid #dfe5ef !important;
        vertical-align: middle;
        white-space: nowrap;
    }
    #kt_table thead th:first-child,
    #kt_table tbody td:first-child {
        text-align: center;
    }
    #kt_table tbody td {
        padding: 13px 10px;
        border-bottom: 1px solid #edf1f6;
        vertical-align: middle;
        font-size: 12.5px;
    }
    #kt_table tbody tr:last-child td {
        border-bottom: 0;
    }
    #kt_table tbody tr:hover {
        background: #f9fbff;
    }
    .finance-table-number {
        color: #25314f;
        font-weight: 500;
        width: 54px;
    }
    .finance-amount-cell {
        white-space: nowrap;
        min-width: 124px;
        font-size: 12.5px;
    }
    .finance-action-group {
        display: inline-flex;
        gap: 6px;
        justify-content: flex-end;
        align-items: center;
    }
    .finance-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .finance-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }
    .finance-action-btn i {
        font-size: 18px !important;
    }
    .finance-action-view { background: #e8f3ff; color: #1682ff; }
    .finance-action-edit { background: #fff3d8; color: #ff9f0a; }
    .finance-action-approve { background: #e7f8ef; color: #12a150; }
    .finance-action-reject,
    .finance-action-delete { background: #ffecef; color: #f1416c; }
    .finance-action-btn i {
        color: currentColor !important;
    }
    .finance-search {
        border-color: #dfe5ef;
        border-radius: 10px;
        font-size: 13px;
    }
    #kt_table.dataTable > thead > tr > th,
    #kt_table.dataTable > thead > tr > th.sorting_asc,
    #kt_table.dataTable > thead > tr > th.sorting_desc {
        position: relative;
        padding-right: 28px;
    }
    #kt_table.dataTable > thead > tr > th::before,
    #kt_table.dataTable > thead > tr > th::after,
    #kt_table.dataTable > thead > tr > th.sorting_asc::before,
    #kt_table.dataTable > thead > tr > th.sorting_asc::after,
    #kt_table.dataTable > thead > tr > th.sorting_desc::before,
    #kt_table.dataTable > thead > tr > th.sorting_desc::after {
        content: "";
        position: absolute;
        right: 10px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        opacity: 1;
    }
    #kt_table.dataTable > thead > tr > th::before,
    #kt_table.dataTable > thead > tr > th.sorting_asc::before,
    #kt_table.dataTable > thead > tr > th.sorting_desc::before {
        top: calc(50% - 8px);
        border-bottom: 6px solid #aab2bd;
    }
    #kt_table.dataTable > thead > tr > th::after,
    #kt_table.dataTable > thead > tr > th.sorting_asc::after,
    #kt_table.dataTable > thead > tr > th.sorting_desc::after {
        top: calc(50% + 2px);
        border-top: 6px solid #aab2bd;
    }
    #kt_table.dataTable > thead > tr > th.sorting_asc::before {
        border-bottom-color: #1f2937;
    }
    #kt_table.dataTable > thead > tr > th.sorting_desc::after {
        border-top-color: #1f2937;
    }
    #kt_table.dataTable > thead > tr > th.text-end::before,
    #kt_table.dataTable > thead > tr > th.text-end::after {
        right: 12px;
    }
    .finance-table-footer {
        margin-top: 16px !important;
        padding: 0 4px !important;
    }
    .finance-table-footer .dataTables_info {
        padding-top: 0 !important;
        color: #061535;
        font-size: 13px;
    }
    .finance-table-footer .pagination {
        margin-bottom: 0;
    }
    .finance-summary-card {
        border: 1px solid #e4e8f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        position: relative;
    }
    .finance-summary-card::after {
        content: "";
        position: absolute;
        width: 112px;
        height: 112px;
        right: -42px;
        top: -42px;
        border-radius: 50%;
        background: rgba(255,255,255,.55);
    }
    .finance-summary-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.82);
    }
    .finance-summary-income { background: linear-gradient(135deg,#ecfdf3,#ffffff); }
    .finance-summary-expense { background: linear-gradient(135deg,#fff1f2,#ffffff); }
    .finance-summary-net { background: linear-gradient(135deg,#eff6ff,#ffffff); }
    .finance-filter-dropdown {
        width: min(720px, calc(100vw - 32px));
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.12);
        padding: 18px;
    }
    .finance-filter-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid #e4e8f0;
        background: #fff;
        color: #1f2937;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .finance-filter-btn:hover,
    .finance-filter-btn.show {
        background: #eff6ff;
        color: #1b84ff;
        border-color: #bfdbfe;
    }
    .finance-import-note {
        border: 1px dashed #cbdaf5;
        border-radius: 12px;
        background: #f8fbff;
        padding: 14px;
    }
    .finance-import-sample {
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .finance-import-sample table {
        margin-bottom: 0;
        font-size: 12px;
    }
    .finance-import-reference {
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        height: 100%;
    }
    .finance-import-reference-head {
        padding: 12px 14px;
        border-bottom: 1px solid #edf1f7;
        background: #f8fbff;
    }
    .finance-import-reference-body {
        max-height: 260px;
        overflow: auto;
    }
    .finance-import-reference table {
        margin-bottom: 0;
        font-size: 12px;
    }
    .finance-import-reference th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f3f6fa;
        color: #5e6278;
        font-weight: 600;
        white-space: nowrap;
    }
    .finance-import-reference td {
        vertical-align: middle;
    }
    .finance-import-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        border-radius: 8px;
        padding: 4px 8px;
        background: #eef6ff;
        color: #1b84ff;
        font-weight: 600;
        letter-spacing: .02em;
    }
    .finance-stat-grid .order-stat-card {
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        padding: 18px;
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        align-items: center;
        column-gap: 14px;
        min-height: 112px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .045);
        position: relative;
        overflow: hidden;
    }
    .finance-stat-grid .order-stat-card::after {
        content: "";
        position: absolute;
        width: 96px;
        height: 96px;
        right: -36px;
        top: -36px;
        border-radius: 50%;
        background: rgba(255,255,255,.65);
    }
    .finance-stat-grid .order-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }
    .finance-stat-grid .order-stat-card > .min-w-0 {
        position: relative;
        z-index: 1;
    }
    .finance-stat-grid .order-stat-label {
        font-size: 11px;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: .03em;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 5px;
    }
    .finance-stat-grid .order-stat-value {
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
        color: #111827;
        overflow-wrap: anywhere;
    }
    .finance-stat-grid .order-stat-currency {
        font-size: 17px;
    }
    .finance-stat-grid .order-stat-hint {
        font-size: 12px;
        color: #7e8299;
        margin-top: 4px;
    }
    .finance-stat-grid .order-stat-info {
        background: linear-gradient(135deg, #f0f9ff, #fff);
    }
    .finance-stat-grid .order-stat-info .order-stat-icon {
        color: #0284c7;
    }
    @media (max-width: 575.98px) {
        .finance-stat-grid .order-stat-card {
            grid-template-columns: 42px minmax(0, 1fr);
            column-gap: 12px;
            min-height: 100px;
            padding: 16px;
        }
        .finance-stat-grid .order-stat-icon {
            width: 42px;
            height: 42px;
        }
        .finance-stat-grid .order-stat-value,
        .finance-stat-grid .order-stat-currency {
            font-size: 15px;
        }
    }
</style>
@endpush

@section('content')
@php
    $canForceEdit = auth()->user()?->hasRole('Superadmin');
    $approvedData = $data->where('status', 'disetujui');
    $totalIncome = $approvedData->where('transaction_type', 'income')->sum('amount');
    $totalExpense = $approvedData->where('transaction_type', 'expense')->sum('amount');
    $pendingCount = $data->where('status', 'menunggu_approval')->count();
    $totalTransactions = $data->count();
    $categoryOptions = $data->map(function ($transaction) {
        $activity = $transaction->activity ?: $transaction->description;
        $isOrderPayment = ($transaction->item?->code === 'AUTO-ORDER') || \Illuminate\Support\Str::startsWith($activity, 'Pembayaran Order');
        return $isOrderPayment ? 'Order Bengkel' : ($transaction->item?->category?->name ?? '-');
    })->unique()->sort()->values();
    $sampleIncomeItem = $importItems->first(fn ($item) => $item->category?->type === 'income') ?: $importItems->first();
    $sampleExpenseItem = $importItems->first(fn ($item) => $item->category?->type === 'expense') ?: $importItems->first();
    $sampleBankAccount = $importBankAccounts->first();
@endphp
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-5 mb-7 finance-stat-grid">
    <div class="col">
        <div class="order-stat-card order-stat-info h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Total Transaksi</div>
                <div class="order-stat-value">{{ number_format($totalTransactions,0,',','.') }}</div>
                <div class="order-stat-hint">{{ $pendingCount }} awaiting</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card order-stat-success h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-arrow-up fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Total Uang Masuk</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($totalIncome,0,',','.') }}</div>
                <div class="order-stat-hint">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card order-stat-danger h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-arrow-down fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Total Uang Keluar</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($totalExpense,0,',','.') }}</div>
                <div class="order-stat-hint">Disetujui</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card {{ $totalIncome - $totalExpense >= 0 ? 'order-stat-primary' : 'order-stat-danger' }} h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-chart-line-up fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Selisih Disetujui</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($totalIncome - $totalExpense,0,',','.') }}</div>
                <div class="order-stat-hint">{{ $pendingCount }} awaiting</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush finance-table-card">
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
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                    <input id="searchInput" class="form-control finance-search w-250px ps-12" placeholder="Cari transaksi..." />
                </div>
                <div class="dropdown" data-bs-auto-close="outside">
                    <button type="button" class="finance-filter-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Filter transaksi">
                        <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end finance-filter-dropdown">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <div class="fw-bold text-gray-900">Filter Transaksi</div>
                                <div class="text-muted fs-8">Atur tanggal, status, jenis, dan kategori.</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light" id="resetFilterBtn">
                                <i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
                                Reset
                            </button>
                        </div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7">Range Tanggal</label>
                                <div class="position-relative">
                                    <i class="ki-duotone ki-calendar fs-3 position-absolute top-50 translate-middle-y ms-4"><span class="path1"></span><span class="path2"></span></i>
                                    <input id="dateRangeFilter" class="form-control ps-11" placeholder="Pilih range tanggal">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7">Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="disetujui">Disetujui</option>
                                    <option value="ditolak">Ditolak</option>
                                    <option value="menunggu_approval">Awaiting</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7">Jenis Transaksi</label>
                                <select id="typeFilter" class="form-select">
                                    <option value="">Semua Jenis</option>
                                    <option value="income">Uang Masuk</option>
                                    <option value="expense">Uang Keluar</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7">Kategori</label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categoryOptions as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table align-middle border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th>#</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal Kwitansi</th>
                    <th>Type transaksi</th>
                    <th>Deksripsi</th>
                    <th>Kategori</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $transaction)
                @php
                    $isOrderPayment = ($transaction->item?->code === 'AUTO-ORDER') || \Illuminate\Support\Str::startsWith($transaction->activity ?: $transaction->description, 'Pembayaran Order');
                    $categoryLabel = $isOrderPayment ? 'Order Bengkel' : ($transaction->item?->category?->name ?? '-');
                @endphp
                <tr data-date="{{ $transaction->transaction_date?->format('Y-m-d') }}"
                    data-status="{{ $transaction->status }}"
                    data-type="{{ $transaction->transaction_type }}"
                    data-category="{{ $categoryLabel }}">
                    <td class="finance-table-number">{{ $i + 1 }}</td>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>{{ $transaction->transaction_date?->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $transaction->transaction_type === 'income' ? 'badge-light-success' : 'badge-light-danger' }}">
                            {{ $transaction->transaction_type === 'income' ? 'Uang Masuk' : 'Uang Keluar' }}
                        </span>
                    </td>
                    <td>{{ $transaction->activity ?: $transaction->description }}</td>
                    <td>{{ $categoryLabel }}</td>
                    <td class="finance-amount-cell {{ $transaction->transaction_type === 'income' ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ $transaction->transaction_type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount,0,',','.') }}
                    </td>
                    <td>
                        @if($transaction->status === 'disetujui')
                            <span class="badge badge-light-success">Disetujui</span>
                        @elseif($transaction->status === 'ditolak')
                            <span class="badge badge-light-danger">Ditolak</span>
                        @else
                            <span class="badge badge-light-warning">Awaiting</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="finance-action-group">
                        @can('finance-transactions.approve')
                        @if($transaction->status === 'menunggu_approval')
                        <button class="finance-action-btn finance-action-approve" onclick="approveItem('{{ $transaction->id }}', @js($transaction->transaction_number))" title="Approve">
                            <i class="ki-duotone ki-check fs-2"></i>
                        </button>
                        <button class="finance-action-btn finance-action-reject" onclick="rejectItem('{{ $transaction->id }}', @js($transaction->transaction_number))" title="Reject">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endif
                        @endcan
                        <a class="finance-action-btn finance-action-view" href="{{ route('finance-transactions.show', $transaction) }}" title="Detail">
                            <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </a>
                        @can('finance-transactions.edit')
                        @if($transaction->status !== 'disetujui' || $canForceEdit)
                        <a class="finance-action-btn finance-action-edit" href="{{ route('finance-transactions.edit', $transaction) }}" title="Edit">
                            <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </a>
                        @endif
                        @endcan
                        @can('finance-transactions.delete')
                        <button class="finance-action-btn finance-action-delete" onclick="deleteItem('{{ $transaction->id }}', @js($transaction->transaction_number))" title="Hapus">
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
</div>

@can('finance-transactions.create')
<div class="modal fade" id="importFinanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Import Transaksi Keuangan</h2>
                <button type="button" class="btn btn-icon btn-sm" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <form method="POST" action="{{ route('finance-transactions.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="finance-import-note mb-5">
                        <div class="fw-semibold text-gray-900 mb-1">Nomor transaksi dan status tidak perlu diisi.</div>
                        <div class="text-muted fs-7">Sistem akan membuat nomor transaksi otomatis, dan semua data import masuk dengan status Awaiting.</div>
                    </div>

                    <div class="row g-5">
                        <div class="col-lg-5">
                            <label class="required form-label fw-semibold">Upload File</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv,.txt" required>
                            <div class="form-text">Gunakan file CSV. Jika dari Excel, pilih Save As lalu format CSV.</div>

                            <button type="button" class="btn btn-sm btn-light-primary mt-4" id="downloadImportSample">
                                <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
                                Download Contoh CSV
                            </button>
                        </div>
                        <div class="col-lg-7">
                            <div class="fw-semibold text-gray-900 mb-2">Format Kolom</div>
                            <div class="finance-import-sample table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>transaction_type</th>
                                            <th>transaction_date</th>
                                            <th>activity</th>
                                            <th>amount</th>
                                            <th>finance_item_code</th>
                                            <th>bank_account_code</th>
                                            <th>notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>income</td>
                                            <td>{{ now()->format('Y-m-d') }}</td>
                                            <td>Pembayaran jasa</td>
                                            <td>250000</td>
                                            <td>{{ $sampleIncomeItem?->code ?? 'KODE_ITEM' }}</td>
                                            <td>{{ $sampleBankAccount?->code ?? 'KODE_BANK' }}</td>
                                            <td>Contoh pemasukan</td>
                                        </tr>
                                        <tr>
                                            <td>expense</td>
                                            <td>{{ now()->format('Y-m-d') }}</td>
                                            <td>Biaya operasional</td>
                                            <td>100000</td>
                                            <td>{{ $sampleExpenseItem?->code ?? 'KODE_ITEM' }}</td>
                                            <td>{{ $sampleBankAccount?->code ?? 'KODE_BANK' }}</td>
                                            <td>Contoh pengeluaran</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-muted fs-8 mt-3">
                                `finance_item_code` memakai kode item keuangan aktif. `bank_account_code` memakai kode account bank/cash aktif.
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="finance-import-reference">
                                <div class="finance-import-reference-head">
                                    <div class="fw-semibold text-gray-900">Kode Item Aktif</div>
                                    <div class="text-muted fs-8">Gunakan kode berikut pada kolom finance_item_code.</div>
                                </div>
                                <div class="finance-import-reference-body table-responsive">
                                    <table class="table table-row-dashed align-middle">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Item</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($importItems as $item)
                                                <tr>
                                                    <td><span class="finance-import-code">{{ $item->code }}</span></td>
                                                    <td class="text-gray-800">{{ $item->name }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-5">Belum ada item aktif.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="finance-import-reference">
                                <div class="finance-import-reference-head">
                                    <div class="fw-semibold text-gray-900">Kode Rekening Aktif</div>
                                    <div class="text-muted fs-8">Gunakan kode berikut pada kolom bank_account_code.</div>
                                </div>
                                <div class="finance-import-reference-body table-responsive">
                                    <table class="table table-row-dashed align-middle">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Bank / Cash</th>
                                                <th>Pemilik</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($importBankAccounts as $account)
                                                <tr>
                                                    <td><span class="finance-import-code">{{ $account->code }}</span></td>
                                                    <td class="text-gray-800">{{ $account->bank_name }}</td>
                                                    <td class="text-muted">{{ $account->account_name ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-5">Belum ada rekening aktif.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-file-up fs-3"><span class="path1"></span><span class="path2"></span></i>
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
var table = $('#kt_table').DataTable({
    fixedHeader: { header: true },
    dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row finance-table-footer'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
    buttons: [{
        extend: 'excelHtml5',
        title: 'Input Keuangan - Garasi Hobby',
        exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7],
            modifier: { search: 'applied', order: 'applied', page: 'all' }
        }
    }],
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
$('#searchInput').on('keyup', function() { table.search(this.value).draw(); });
$('#lengthSelect').on('change', function() { table.page.len($(this).val()).draw(); });
$('#exportExcel').on('click', function(e) {
    e.preventDefault();
    table.button(0).trigger();
});

$('#downloadImportSample').on('click', function() {
    var csv = [
        ['transaction_type','transaction_date','activity','amount','finance_item_code','bank_account_code','notes'],
        ['income','{{ now()->format('Y-m-d') }}','Pembayaran jasa','250000','{{ $sampleIncomeItem?->code ?? 'KODE_ITEM' }}','{{ $sampleBankAccount?->code ?? 'KODE_BANK' }}','Contoh pemasukan'],
        ['expense','{{ now()->format('Y-m-d') }}','Biaya operasional','100000','{{ $sampleExpenseItem?->code ?? 'KODE_ITEM' }}','{{ $sampleBankAccount?->code ?? 'KODE_BANK' }}','Contoh pengeluaran']
    ].map(function(row) {
        return row.map(function(value) {
            value = String(value || '');
            return '"' + value.replace(/"/g, '""') + '"';
        }).join(',');
    }).join('\r\n');

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'contoh-import-transaksi-keuangan.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
});

var dateRangeStart = null;
var dateRangeEnd = null;
var dateRangePicker = flatpickr('#dateRangeFilter', {
    mode: 'range',
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    locale: {
        rangeSeparator: ' sampai '
    },
    onChange: function(selectedDates) {
        dateRangeStart = selectedDates[0] || null;
        dateRangeEnd = selectedDates[1] || null;
        table.draw();
    }
});

$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'kt_table') return true;

    var row = table.row(dataIndex).node();
    if (!row) return true;

    var rowDateValue = row.getAttribute('data-date');
    var rowStatus = row.getAttribute('data-status') || '';
    var rowType = row.getAttribute('data-type') || '';
    var rowCategory = row.getAttribute('data-category') || '';
    var statusFilter = document.getElementById('statusFilter').value;
    var typeFilter = document.getElementById('typeFilter').value;
    var categoryFilter = document.getElementById('categoryFilter').value;

    if (statusFilter && rowStatus !== statusFilter) return false;
    if (typeFilter && rowType !== typeFilter) return false;
    if (categoryFilter && rowCategory !== categoryFilter) return false;

    if (dateRangeStart || dateRangeEnd) {
        if (!rowDateValue) return false;
        var rowDate = new Date(rowDateValue + 'T00:00:00');
        if (dateRangeStart && rowDate < new Date(dateRangeStart.getFullYear(), dateRangeStart.getMonth(), dateRangeStart.getDate())) return false;
        if (dateRangeEnd && rowDate > new Date(dateRangeEnd.getFullYear(), dateRangeEnd.getMonth(), dateRangeEnd.getDate())) return false;
    }

    return true;
});

$('#statusFilter, #typeFilter, #categoryFilter').on('change', function() {
    table.draw();
});

$('#resetFilterBtn').on('click', function() {
    $('#statusFilter, #typeFilter, #categoryFilter').val('');
    if (dateRangePicker) dateRangePicker.clear();
    dateRangeStart = null;
    dateRangeEnd = null;
    table.search('').draw();
    $('#searchInput').val('');
});

function deleteItem(id, number) {
    Swal.fire({
        title: 'Hapus transaksi?',
        text: 'Yakin ingin menghapus "' + number + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;

        fetch('/keuangan/transaksi/' + id, {
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

function approveItem(id, number) {
    Swal.fire({
        title: 'Approve transaksi?',
        text: 'Transaksi ' + number + ' akan disetujui dan saldo bank diperbarui.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, approve',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (!result.isConfirmed) return;
        postAction('/keuangan/transaksi/' + id + '/approve');
    });
}

function rejectItem(id, number) {
    Swal.fire({
        title: 'Reject transaksi ' + number + '?',
        input: 'textarea',
        inputLabel: 'Alasan Reject',
        inputPlaceholder: 'Tuliskan alasan reject...',
        inputAttributes: { 'aria-label': 'Alasan reject' },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Batal',
        inputValidator: function(value) {
            if (!value) return 'Alasan reject wajib diisi.';
        }
    }).then(function(result) {
        if (!result.isConfirmed) return;
        postAction('/keuangan/transaksi/' + id + '/reject', { rejection_reason: result.value });
    });
}

function postAction(url, payload) {
    var body = new FormData();
    Object.keys(payload || {}).forEach(function(key) {
        body.append(key, payload[key]);
    });

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: body
    }).then(function(response) {
        if (response.redirected || response.ok) {
            window.location.reload();
            return;
        }
        return response.text().then(function() {
            Swal.fire('Gagal', 'Aksi approval tidak bisa diproses.', 'error');
        });
    });
}
</script>
@endpush
