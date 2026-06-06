@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Dashboard</li>
@endsection

@section('toolbar_actions')
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
        <select name="year" class="form-select form-select-sm fw-semibold gh-year-filter" onchange="this.form.submit()">
            @foreach($availableYears as $year)
                <option value="{{ $year }}" @selected((int) $year === (int) $selectedYear)>Tahun {{ $year }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary fw-semibold">
        <i class="ki-duotone ki-plus-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
        Input Order
    </a>
@endsection

@section('content')
    @php
        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn ($value) => number_format((float) $value, 0, ',', '.');

        $summaryCards = [
            ['label' => 'Revenue Hari Ini', 'value' => $money($stats['revenue_daily']), 'hint' => 'Total order hari ini: ' . $number($stats['orders_today']), 'icon' => 'ki-dollar', 'tone' => 'success'],
            ['label' => 'Revenue Bulan Ini', 'value' => $money($stats['revenue_monthly']), 'hint' => 'Total order bulan ini: ' . $number($stats['orders_month']), 'icon' => 'ki-calendar-tick', 'tone' => 'primary'],
            ['label' => 'Revenue Total', 'value' => $money($stats['revenue_total']), 'hint' => 'Total order keseluruhan: ' . $number($stats['orders_total']), 'icon' => 'ki-chart-line-up', 'tone' => 'info'],
            ['label' => 'Total Nilai Investasi', 'value' => $money($stats['investment_total']), 'hint' => 'Pemasukan kategori investasi', 'icon' => 'ki-bank', 'tone' => 'violet'],
            ['label' => 'Total Pengeluaran', 'value' => $money($stats['money_out']), 'hint' => 'Expense yang sudah disetujui', 'icon' => 'ki-arrow-up-right', 'tone' => 'danger'],
            ['label' => 'Total Revenue Bersih', 'value' => $money($stats['net_revenue']), 'hint' => 'Pemasukan dikurangi pengeluaran', 'icon' => 'ki-chart-simple-2', 'tone' => 'dark'],
        ];

        $debtCards = [
            ['label' => 'Total Hutang', 'value' => $money($debtReceivableStats['total_debt']), 'icon' => 'ki-arrow-down', 'tone' => 'danger'],
            ['label' => 'Sisa Hutang', 'value' => $money($debtReceivableStats['remaining_debt']), 'icon' => 'ki-time', 'tone' => 'warning'],
            ['label' => 'Total Piutang', 'value' => $money($debtReceivableStats['total_receivable']), 'icon' => 'ki-arrow-up', 'tone' => 'success'],
            ['label' => 'Sisa Piutang', 'value' => $money($debtReceivableStats['remaining_receivable']), 'icon' => 'ki-calendar-tick', 'tone' => 'primary'],
            ['label' => 'Telat Bayar', 'value' => $money($debtReceivableStats['overdue']), 'icon' => 'ki-timer', 'tone' => 'danger'],
            ['label' => 'Deadline', 'value' => $money($debtReceivableStats['deadline']), 'icon' => 'ki-calendar-8', 'tone' => 'warning'],
        ];
        $categoryColors = ['#2563eb', '#3b82f6', '#4f67c8', '#7db7e8', '#12a150', '#ff9f0a'];
    @endphp

    <div class="gh-dashboard">
        <div class="gh-page-head mb-6">
            <div>
                <div class="text-muted fw-semibold fs-8 text-uppercase mb-2">Garasi Hobby</div>
                <h2 class="fw-bolder text-gray-900 mb-1">Dashboard Keuangan & Operasional</h2>
                <div class="text-muted fs-7">Ringkasan tahun {{ $selectedYear }} dan posisi keuangan terbaru.</div>
            </div>
            <div class="gh-head-date">
                <i class="ki-outline ki-calendar fs-3"></i>
                {{ now()->translatedFormat('d F Y') }}
            </div>
        </div>

        <div class="row g-5 g-xl-6 mb-6">
            @foreach($summaryCards as $item)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="gh-stat gh-tone-{{ $item['tone'] }}">
                        <div class="gh-stat-icon">
                            <i class="ki-duotone {{ $item['icon'] }} fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                        <div class="min-w-0">
                            <div class="gh-stat-label">{{ $item['label'] }}</div>
                            <div class="gh-stat-value">{{ $item['value'] }}</div>
                            <div class="gh-stat-hint">{{ $item['hint'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-5 g-xl-6 mb-6">
            <div class="col-xl-7">
                <div class="card gh-card h-100">
                    <div class="card-header border-0 pt-6 gh-card-header-compact">
                        <div>
                            <h3 class="card-title fw-bold text-gray-900 mb-1">Pemasukan vs Pengeluaran</h3>
                            <div class="text-muted fs-7">Periode Januari sampai Desember {{ $selectedYear }}</div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div id="gh_revenue_chart" class="gh-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card gh-card h-100">
                    <div class="card-header border-0 pt-6 gh-card-header-compact">
                        <div>
                            <h3 class="card-title fw-bold text-gray-900 mb-1">Grafik Order Bulanan</h3>
                            <div class="text-muted fs-7">Open vs selesai</div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div id="gh_order_chart" class="gh-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-6 mb-6">
            <div class="col-xl-6">
                <div class="card gh-card h-100">
                    <div class="card-header border-0 pt-6">
                        <div>
                            <h3 class="card-title fw-bold text-gray-900 mb-1">Hutang & Piutang</h3>
                            <div class="text-muted fs-7">Posisi kewajiban dan tagihan aktif</div>
                        </div>
                        <a href="{{ route('debt-receivables.index') }}" class="btn btn-sm btn-primary fw-semibold gh-card-action">
                            <i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-4">
                            @foreach($debtCards as $item)
                                <div class="col-12 col-sm-6">
                                    <div class="gh-mini gh-tone-{{ $item['tone'] }}">
                                        <div>
                                            <div class="gh-mini-label">{{ $item['label'] }}</div>
                                            <div class="gh-mini-value">{{ $item['value'] }}</div>
                                        </div>
                                        <div class="gh-mini-icon">
                                            <i class="ki-duotone {{ $item['icon'] }} fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card gh-card h-100">
                    <div class="card-header border-0 pt-6">
                        <div>
                            <h3 class="card-title fw-bold text-gray-900 mb-1">Pengeluaran per Kategori</h3>
                            <div class="text-muted fs-7">Pengeluaran berdasarkan kategori</div>
                        </div>
                        <a href="{{ route('finance-transactions.index') }}" class="btn btn-sm btn-primary fw-semibold gh-card-action">
                            <i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body pt-2">
                        @forelse($expenseCategories as $index => $category)
                            @php
                                $categoryTotal = (float) $category->total_amount;
                                $percentage = $expenseCategoryTotal > 0 ? max(4, ($categoryTotal / $expenseCategoryTotal) * 100) : 0;
                                $color = $categoryColors[$index % count($categoryColors)];
                            @endphp
                            <div class="gh-category-row">
                                <div class="gh-category-name">
                                    <span class="gh-dot" style="background: {{ $color }}"></span>
                                    <span>{{ $category->category_name }}</span>
                                </div>
                                <div class="gh-category-bar">
                                    <span style="width: {{ $percentage }}%; background: {{ $color }}"></span>
                                </div>
                                <div class="gh-category-value">{{ $money($categoryTotal) }}</div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-8">Belum ada pengeluaran approved.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card gh-card mb-6">
            <div class="card-header border-0 pt-6 gh-card-header-compact">
                <div>
                    <h3 class="card-title fw-bold text-gray-900 mb-1">Saldo Rekening</h3>
                    <div class="gh-balance-total-badge mt-2">Total saldo aktif {{ $money($stats['bank_balance_total']) }}</div>
                </div>
                <a href="{{ route('bank-accounts.index') }}" class="btn btn-sm btn-primary fw-semibold gh-card-action">
                    <i class="ki-duotone ki-setting-2 fs-4"><span class="path1"></span><span class="path2"></span></i>
                    Kelola Rekening
                </a>
            </div>
            <div class="card-body pt-2">
                <div class="row g-5">
                    @forelse($bankAccounts as $account)
                        @php
                            $accountNumber = $account->account_number ?: '';
                            $maskedNumber = $accountNumber ? '**** ' . substr($accountNumber, -4) : 'Nomor belum diisi';
                            $balance = (float) $account->balance;
                        @endphp
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="gh-bank">
                                <div class="gh-bank-head">
                                    <div class="gh-bank-identity">
                                        <div class="gh-bank-logo">
                                            @if($account->logo_url)
                                                <img src="{{ $account->logo_url }}" alt="{{ $account->bank_name }}">
                                            @else
                                                <span>{{ $account->logo_text }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="gh-bank-name" title="{{ $account->bank_name }}">{{ $account->bank_name }}</div>
                                            <div class="gh-bank-owner" title="{{ $account->account_name ?: '-' }}">{{ $account->account_name ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="gh-bank-balance-panel">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="gh-bank-label">Saldo Tersedia</div>
                                        <span class="gh-bank-balance-badge {{ $balance < 0 ? 'is-minus' : 'is-plus' }}">{{ $balance < 0 ? 'Minus' : 'Aktif' }}</span>
                                    </div>
                                    <div class="gh-bank-balance {{ $balance < 0 ? 'is-minus' : 'is-plus' }}">{{ $money($balance) }}</div>
                                </div>
                                <div class="gh-bank-footer">
                                    <div class="gh-bank-number">{{ $maskedNumber }}</div>
                                    <a href="{{ route('bank-accounts.show', $account) }}" class="gh-bank-link">Mutasi <i class="ki-duotone ki-arrow-right fs-5"><span class="path1"></span><span class="path2"></span></i></a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center text-muted py-8">Belum ada rekening aktif.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card gh-card">
            <div class="card-header border-0 pt-6 gh-card-header-compact">
                <div>
                    <h3 class="card-title fw-bold text-gray-900 mb-1">Stok Persediaan</h3>
                    <div class="text-muted fs-7">Material habis, hampir habis, atau minimum stok belum diset</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('material-purchases.create') }}" class="btn btn-sm btn-primary fw-semibold gh-card-action">
                        <i class="ki-duotone ki-plus-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                        Beli Material
                    </a>
                    <a href="{{ route('material-inventory.index') }}" class="btn btn-sm btn-light-primary fw-semibold gh-card-action">
                        <i class="ki-duotone ki-parcel fs-4"><span class="path1"></span><span class="path2"></span></i>
                        Cek Stok
                    </a>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive gh-stock-table-wrap">
                    <table class="table align-middle fs-7 gy-3 gh-stock-table">
                        <thead>
                            <tr>
                                <th class="w-60px">No.</th>
                                <th class="w-70px">Foto</th>
                                <th>Nama Material</th>
                                <th>Kategori</th>
                                <th class="text-end">Qty On Hand</th>
                                <th class="text-end">Min Stok</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockMaterials as $material)
                                @php
                                    $qty = (int) ($material->stock?->qty ?? 0);
                                    $minStock = (int) $material->min_stock;
                                    if ($qty <= 0) {
                                        $stockStatus = ['label' => 'Habis', 'class' => 'danger'];
                                    } elseif ($minStock <= 0) {
                                        $stockStatus = ['label' => 'Min stok belum diset', 'class' => 'warning'];
                                    } else {
                                        $stockStatus = ['label' => 'Hampir habis', 'class' => 'danger'];
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="gh-material-thumb">
                                            @if($material->photo_url)
                                                <img src="{{ $material->photo_url }}" alt="{{ $material->name }}">
                                            @else
                                                <i class="ki-duotone ki-picture fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-gray-900">{{ $material->name }}</td>
                                    <td class="text-muted">{{ $material->category?->name ?? 'Tanpa kategori' }}</td>
                                    <td class="text-end fw-semibold">{{ $number($qty) }}</td>
                                    <td class="text-end">{{ $minStock > 0 ? $number($minStock) : '-' }}</td>
                                    <td><span class="badge badge-light-{{ $stockStatus['class'] }}">{{ $stockStatus['label'] }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('material-purchases.create') }}" class="btn btn-sm btn-light-primary fw-semibold gh-card-action">
                                            <i class="ki-duotone ki-plus-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                                            Beli
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-8">Stok persediaan aman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    @include('layouts.partials.modals')
@endsection

@push('styles')
<style>
    .gh-dashboard {
        color: #17213b;
    }
    .gh-year-filter {
        min-width: 132px;
        border-color: #dfe6f2;
        border-radius: 10px;
    }
    .gh-page-head {
        min-height: 108px;
        border: 1px solid #e4e8f0;
        border-radius: 14px;
        background: linear-gradient(135deg, #fff 0%, #f7fbff 100%);
        box-shadow: 0 12px 30px rgba(15, 23, 42, .04);
        padding: 22px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    .gh-head-date {
        min-height: 38px;
        padding: 8px 12px;
        border-radius: 10px;
        background: #eef6ff;
        color: #1682ff;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 650;
        white-space: nowrap;
    }
    .gh-card-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: auto;
        min-width: max-content;
        height: 36px;
        min-height: 36px;
        padding: 0 14px;
        border-radius: 9px;
        line-height: 1;
        font-size: 13px;
        white-space: nowrap;
    }
    .gh-card-action i {
        color: currentColor !important;
    }
    .gh-card-header-compact {
        min-height: auto !important;
        padding-bottom: 12px !important;
        display: flex;
        align-items: flex-start !important;
        justify-content: space-between;
        gap: 14px;
    }
    .gh-card-header-compact .gh-card-action {
        margin-top: 0;
        flex: 0 0 auto;
    }
    .gh-balance-total-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef6ff;
        color: #1682ff;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .gh-card,
    .gh-stat {
        border: 1px solid #e4e8f0;
        border-radius: 14px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .05);
    }
    .gh-stat {
        position: relative;
        min-height: 132px;
        padding: 20px;
        background: #fff;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .gh-stat:hover,
    .gh-bank:hover,
    .gh-mini:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
    }
    .gh-stat-icon,
    .gh-mini-icon,
    .gh-bank-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
    }
    .gh-stat-icon i,
    .gh-mini-icon i,
    .gh-bank-icon i {
        color: currentColor !important;
    }
    .gh-stat-label,
    .gh-bank-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        text-transform: uppercase;
    }
    .gh-stat-value {
        color: #061535;
        font-size: 23px;
        font-weight: 750;
        line-height: 1.2;
        margin-top: 8px;
        overflow-wrap: anywhere;
    }
    .gh-stat-hint {
        color: #8a96a8;
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }
    .gh-tone-primary .gh-stat-icon,
    .gh-tone-primary .gh-mini-icon,
    .gh-bank-icon { background: #e8f3ff; color: #1682ff; }
    .gh-tone-success .gh-stat-icon,
    .gh-tone-success .gh-mini-icon { background: #e7f8ef; color: #12a150; }
    .gh-tone-danger .gh-stat-icon,
    .gh-tone-danger .gh-mini-icon { background: #ffecef; color: #f1416c; }
    .gh-tone-warning .gh-stat-icon,
    .gh-tone-warning .gh-mini-icon { background: #fff3d8; color: #ff9f0a; }
    .gh-tone-info .gh-stat-icon,
    .gh-tone-info .gh-mini-icon { background: #e7f9ff; color: #00a3c7; }
    .gh-tone-violet .gh-stat-icon,
    .gh-tone-violet .gh-mini-icon { background: #f1edff; color: #7239ea; }
    .gh-tone-dark .gh-stat-icon,
    .gh-tone-dark .gh-mini-icon { background: #eef2f7; color: #334155; }
    .gh-chart {
        min-height: 340px;
    }
    .gh-mini {
        min-height: 120px;
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        padding: 18px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .gh-mini-label {
        color: #061535;
        font-size: 14px;
        font-weight: 600;
    }
    .gh-mini-value {
        color: #061535;
        font-size: 19px;
        font-weight: 650;
        margin-top: 24px;
        overflow-wrap: anywhere;
    }
    .gh-category-row {
        display: grid;
        grid-template-columns: minmax(120px, 1fr) minmax(120px, 190px) minmax(120px, auto);
        align-items: center;
        gap: 16px;
        min-height: 42px;
        margin-bottom: 10px;
    }
    .gh-category-name {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        color: #061535;
        font-weight: 600;
    }
    .gh-dot {
        width: 10px;
        height: 10px;
        border-radius: 99px;
        flex: 0 0 10px;
    }
    .gh-category-bar {
        height: 7px;
        border-radius: 99px;
        background: #f1f3f6;
        overflow: hidden;
    }
    .gh-category-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }
    .gh-category-value {
        color: #061535;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
    }
    .gh-bank {
        min-height: 214px;
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        padding: 16px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
        transition: transform .16s ease, box-shadow .16s ease;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .gh-bank::after {
        content: "";
        position: absolute;
        width: 96px;
        height: 96px;
        right: -42px;
        top: -42px;
        border-radius: 50%;
        background: rgba(22, 130, 255, .08);
        pointer-events: none;
    }
    .gh-bank-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
        position: relative;
        z-index: 1;
    }
    .gh-bank-identity {
        min-width: 0;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .gh-bank-logo {
        width: 54px;
        height: 54px;
        border: 1px solid #e4e8f0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        flex: 0 0 54px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
    }
    .gh-bank-logo img {
        display: block;
        max-width: 44px;
        max-height: 34px;
        object-fit: contain;
    }
    .gh-bank-logo span {
        color: #1682ff;
        font-size: 12px;
        font-weight: 750;
    }
    .gh-bank-name {
        max-width: 100%;
        color: #061535;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
    }
    .gh-bank-owner {
        max-width: 100%;
        color: #64748b;
        font-size: 12px;
        font-weight: 500;
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
    }
    .gh-bank-balance-panel {
        position: relative;
        z-index: 1;
        border: 1px solid #e8edf5;
        border-radius: 14px;
        background: rgba(255,255,255,.78);
        padding: 13px 14px;
        min-width: 0;
    }
    .gh-bank-balance {
        font-size: clamp(17px, 1.55vw, 22px);
        font-weight: 750;
        line-height: 1.24;
        margin-top: 10px;
        max-width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .gh-bank-balance.is-plus {
        color: #12a150;
    }
    .gh-bank-balance.is-minus {
        color: #f1416c;
    }
    .gh-bank-balance-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .gh-bank-balance-badge.is-plus {
        background: #e7f8ef;
        color: #12a150;
    }
    .gh-bank-balance-badge.is-minus {
        background: #ffecef;
        color: #f1416c;
    }
    .gh-bank-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #1682ff;
        font-size: 12px;
        font-weight: 650;
        white-space: nowrap;
    }
    .gh-bank-footer {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
        margin-top: auto;
    }
    .gh-bank-number {
        min-width: 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .gh-stock-table-wrap {
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .gh-stock-table > :not(caption) > * > * {
        border-bottom-color: #edf1f7;
    }
    .gh-stock-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .gh-material-thumb {
        width: 46px;
        height: 46px;
        border: 1px solid #e4e8f0;
        border-radius: 10px;
        background: #f8fafc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: #94a3b8;
    }
    .gh-material-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .gh-material-thumb i {
        color: currentColor !important;
    }
    .gh-list-row {
        min-height: 66px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #edf1f7;
    }
    .gh-list-row:last-child {
        border-bottom: 0;
    }
    @media (max-width: 991.98px) {
        .gh-category-row {
            grid-template-columns: 1fr;
            align-items: stretch;
            gap: 8px;
            margin-bottom: 20px;
        }
        .gh-category-value {
            text-align: left;
        }
    }
    @media (max-width: 767.98px) {
        .gh-page-head {
            flex-direction: column;
            align-items: flex-start;
        }
        .gh-stat-value {
            font-size: 21px;
        }
        .gh-list-row {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moneyFormatter = function(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        };

        const revenueData = @json($revenueChart);
        const orderData = @json($orderChart);

        if (window.ApexCharts) {
            new ApexCharts(document.querySelector('#gh_revenue_chart'), {
                chart: { type: 'area', height: 340, toolbar: { show: false }, fontFamily: 'Onest, sans-serif' },
                series: [
                    { name: 'Pemasukan', data: revenueData.income },
                    { name: 'Pengeluaran', data: revenueData.expense }
                ],
                xaxis: { categories: revenueData.labels, labels: { style: { colors: '#7e8aa0' } } },
                yaxis: { labels: { formatter: moneyFormatter, style: { colors: '#7e8aa0' } } },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#12a150', '#f1416c'],
                fill: { type: 'gradient', gradient: { opacityFrom: .24, opacityTo: .04 } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#edf1f7', strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { y: { formatter: moneyFormatter } }
            }).render();

            new ApexCharts(document.querySelector('#gh_order_chart'), {
                chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'Onest, sans-serif' },
                series: [
                    { name: 'Open', data: orderData.open },
                    { name: 'Selesai', data: orderData.done }
                ],
                xaxis: { categories: orderData.labels, labels: { rotate: 0, style: { colors: '#7e8aa0' } } },
                yaxis: { labels: { style: { colors: '#7e8aa0' } } },
                plotOptions: { bar: { borderRadius: 5, columnWidth: '46%' } },
                colors: ['#ff9f0a', '#12a150'],
                dataLabels: { enabled: false },
                grid: { borderColor: '#edf1f7', strokeDashArray: 4 },
                legend: { position: 'top' }
            }).render();
        }
    });
</script>
@endpush
