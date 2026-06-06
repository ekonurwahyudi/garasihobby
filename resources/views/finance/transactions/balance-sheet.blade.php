@extends('layouts.app')

@section('title', 'Neraca Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted"><a href="{{ route('finance-transactions.index') }}" class="text-muted text-hover-primary">Input Keuangan</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Neraca</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('finance-transactions.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@section('content')
@php
    $fmtRp = fn ($value) => ((float) $value < 0 ? '-' : '') . 'Rp ' . number_format(abs((float) $value), 0, ',', '.');
    $cutoffDateText = \Carbon\Carbon::parse($snapshot->cutoff_date)->translatedFormat('d F Y');
    $difference = (float) $snapshot->total_assets - ((float) $snapshot->total_liabilities + (float) $snapshot->total_equity);
    $balanced = abs($difference) < 1;
@endphp

@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-3">
    <i class="ki-duotone ki-check-circle fs-2 text-success"><span class="path1"></span><span class="path2"></span></i>
    <div>{{ session('success') }}</div>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-3">
    <i class="ki-duotone ki-information fs-2 text-danger"><span class="path1"></span><span class="path2"></span></i>
    <div>{{ session('error') }}</div>
</div>
@endif

<div class="balance-hero mb-7">
    <div class="balance-hero-main">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-5">
            <div class="min-w-0">
                <div class="balance-eyebrow mb-3">Neraca Keuangan</div>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <h1 class="fw-bolder text-gray-900 mb-0">Posisi Keuangan {{ $year }}</h1>
                    <span class="balance-status-pill {{ $balanced ? 'is-balanced' : 'is-warning' }}">
                        <i class="ki-duotone {{ $balanced ? 'ki-shield-tick' : 'ki-information' }} fs-4"><span class="path1"></span><span class="path2"></span></i>
                        {{ $balanced ? 'Seimbang' : 'Perlu Dicek' }}
                    </span>
                    @if(!$selectedCutoff)
                        <span class="badge badge-light-warning">Preview belum disimpan</span>
                    @endif
                </div>
                <div class="balance-period-line">
                    <span><i class="ki-duotone ki-calendar fs-4"><span class="path1"></span><span class="path2"></span></i> 1 Januari {{ $year }} sampai {{ $cutoffDateText }}</span>
                    @if($selectedCutoff)
                        <span>{{ $selectedCutoff->label ?: $selectedCutoff->cutoff_number }}</span>
                    @endif
                </div>
            </div>
            <div class="balance-hero-total">
                <div class="text-muted fs-8 text-uppercase fw-bold mb-1">Total Aset</div>
                <div class="fw-bolder fs-1 text-gray-900">{{ $fmtRp($snapshot->total_assets) }}</div>
                <div class="text-muted fs-8 mt-1">Kewajiban + Ekuitas {{ $fmtRp((float) $snapshot->total_liabilities + (float) $snapshot->total_equity) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush balance-control-card mb-7">
    <div class="card-body d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-5">
        <form method="GET" action="{{ route('finance-transactions.balance-sheet') }}" class="d-flex flex-column flex-md-row align-items-md-end gap-3">
            <div>
                <label class="form-label fw-semibold">Cut Off Tersimpan</label>
                <select name="cutoff_id" class="form-select min-w-250px" onchange="this.form.submit()">
                    @forelse($cutoffs as $cutoff)
                        <option value="{{ $cutoff->id }}" @selected($selectedCutoff?->id === $cutoff->id)>
                            {{ $cutoff->label ? $cutoff->label . ' - ' : '' }}{{ $cutoff->year }} ({{ $cutoff->cutoff_date?->format('d/m/Y') }})
                        </option>
                    @empty
                        <option value="">Belum ada cut off tersimpan</option>
                    @endforelse
                </select>
            </div>
            <button class="btn btn-light-primary"><i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tampilkan</button>
        </form>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-icon btn-light balance-icon-btn" id="printBalanceSheet" title="Print">
                <i class="ki-duotone ki-printer fs-3"><span class="path1"></span><span class="path2"></span></i>
            </button>
            <button type="button" class="btn btn-sm btn-icon btn-success balance-icon-btn" id="downloadBalanceExcel" title="Download Excel">
                <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
            </button>
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#balanceCutoffModal">
                <i class="ki-duotone ki-calculator fs-3"><span class="path1"></span><span class="path2"></span></i> Cut Off Data Tahunan
            </button>
            @if($selectedCutoff)
                @can('finance-transactions.delete')
                    <form method="POST" action="{{ route('finance-transactions.balance-sheet.cutoff.destroy', $selectedCutoff) }}" onsubmit="return confirm('Hapus cut off neraca ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-icon btn-light-danger balance-icon-btn" title="Hapus Cut Off"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                    </form>
                @endcan
            @endif
        </div>
    </div>
</div>

<div id="balancePrintArea">
<div class="balance-print-header">
    <div>
        <div class="balance-print-company">Garasi Hobby</div>
        <h1>Laporan Neraca Keuangan</h1>
        <p>Periode 1 Januari {{ $year }} sampai {{ $cutoffDateText }}</p>
    </div>
    <div class="balance-print-meta">
        <div>Tahun</div>
        <strong>{{ $year }}</strong>
        <span>{{ $selectedCutoff?->label ?: ($selectedCutoff?->cutoff_number ?: 'Preview') }}</span>
    </div>
</div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-5 mb-7">
    <div class="col"><div class="balance-summary-card balance-summary-blue h-100"><span class="summary-icon"><i class="ki-duotone ki-wallet fs-2"><span class="path1"></span><span class="path2"></span></i></span><div class="label">Total Aset</div><div class="value">{{ $fmtRp($snapshot->total_assets) }}</div></div></div>
    <div class="col"><div class="balance-summary-card balance-summary-red h-100"><span class="summary-icon"><i class="ki-duotone ki-arrow-down fs-2"><span class="path1"></span><span class="path2"></span></i></span><div class="label">Total Hutang</div><div class="value">{{ $fmtRp($snapshot->total_liabilities) }}</div></div></div>
    <div class="col"><div class="balance-summary-card balance-summary-green h-100"><span class="summary-icon"><i class="ki-duotone ki-shield-tick fs-2"><span class="path1"></span><span class="path2"></span></i></span><div class="label">Total Ekuitas</div><div class="value">{{ $fmtRp($snapshot->total_equity) }}</div></div></div>
    <div class="col"><div class="balance-summary-card {{ (float) $snapshot->current_year_profit >= 0 ? 'balance-summary-emerald' : 'balance-summary-rose' }} h-100"><span class="summary-icon"><i class="ki-duotone ki-chart-line-up fs-2"><span class="path1"></span><span class="path2"></span></i></span><div class="label">Laba Bersih</div><div class="value">{{ $fmtRp($snapshot->current_year_profit) }}</div></div></div>
</div>

@if($balanced)
<div class="balance-alert is-balanced mb-7">
    <i class="ki-duotone ki-shield-tick fs-2 text-success"><span class="path1"></span><span class="path2"></span></i>
    <div class="fw-semibold">Neraca seimbang: Aset = Kewajiban + Ekuitas.</div>
</div>
@else
<div class="balance-alert is-warning mb-7">
    <i class="ki-duotone ki-information fs-2 text-warning"><span class="path1"></span><span class="path2"></span></i>
    <div><div class="fw-semibold">Neraca belum seimbang.</div><div class="fs-8">Selisih {{ $fmtRp(abs($difference)) }}.</div></div>
</div>
@endif

<div class="row g-7">
    <div class="col-lg-6">
        <div class="card card-flush balance-sheet-card h-100">
            <div class="card-header border-0 balance-table-head balance-table-head-primary"><h3 class="fw-bold mb-0">Aset</h3><span>{{ $fmtRp($snapshot->total_assets) }}</span></div>
            <div class="card-body p-4">
                <table class="table balance-table mb-0" id="assetBalanceTable">
                    <colgroup><col class="balance-name-col"><col class="balance-value-col"></colgroup>
                    <tbody>
                        <tr class="section-row"><td colspan="2">Aset Lancar</td></tr>
                        <tr><td>Kas & Bank</td><td class="text-end">{{ $fmtRp($snapshot->cash_bank) }}</td></tr>
                        <tr><td>Piutang</td><td class="text-end">{{ $fmtRp($snapshot->receivables) }}</td></tr>
                        <tr><td>Persediaan</td><td class="text-end">{{ $fmtRp($snapshot->inventory) }}</td></tr>
                        <tr class="subtotal-row"><td>Subtotal Aset Lancar</td><td class="text-end">{{ $fmtRp((float) $snapshot->cash_bank + (float) $snapshot->receivables + (float) $snapshot->inventory) }}</td></tr>
                        <tr class="section-row"><td colspan="2">Aset Tetap</td></tr>
                        <tr><td>Aset Tetap Bruto</td><td class="text-end">{{ $fmtRp($snapshot->fixed_assets_gross) }}</td></tr>
                        <tr><td class="text-muted">Akumulasi Penyusutan</td><td class="text-end text-danger">({{ $fmtRp($snapshot->accumulated_depreciation) }})</td></tr>
                        <tr class="subtotal-row"><td>Subtotal Aset Tetap Netto</td><td class="text-end">{{ $fmtRp($snapshot->fixed_assets_net) }}</td></tr>
                    </tbody>
                    <tfoot><tr><td>Total Aset</td><td class="text-end">{{ $fmtRp($snapshot->total_assets) }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-flush balance-sheet-card h-100">
            <div class="card-header border-0 balance-table-head"><h3 class="fw-bold mb-0">Kewajiban & Ekuitas</h3><span>{{ $fmtRp((float) $snapshot->total_liabilities + (float) $snapshot->total_equity) }}</span></div>
            <div class="card-body p-4">
                <table class="table balance-table mb-0" id="liabilityBalanceTable">
                    <colgroup><col class="balance-name-col"><col class="balance-value-col"></colgroup>
                    <tbody>
                        <tr class="section-row danger"><td colspan="2">Kewajiban</td></tr>
                        <tr><td>Hutang Usaha</td><td class="text-end">{{ $fmtRp($snapshot->payables) }}</td></tr>
                        <tr class="subtotal-row danger"><td>Total Kewajiban</td><td class="text-end">{{ $fmtRp($snapshot->total_liabilities) }}</td></tr>
                        <tr class="section-row success"><td colspan="2">Ekuitas</td></tr>
                        <tr><td>Modal Pemilik / Saldo Awal</td><td class="text-end">{{ $fmtRp($snapshot->owner_equity) }}</td></tr>
                        <tr><td>Laba Tahun Berjalan</td><td class="text-end {{ (float) $snapshot->current_year_profit >= 0 ? 'text-success' : 'text-danger' }}">{{ $fmtRp($snapshot->current_year_profit) }}</td></tr>
                        <tr class="subtotal-row success"><td>Total Ekuitas</td><td class="text-end">{{ $fmtRp($snapshot->total_equity) }}</td></tr>
                    </tbody>
                    <tfoot><tr><td>Total Kewajiban + Ekuitas</td><td class="text-end">{{ $fmtRp((float) $snapshot->total_liabilities + (float) $snapshot->total_equity) }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="balanceCutoffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="fw-bold">Simpan Cut Off Neraca Tahunan</h3>
                <button type="button" class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button>
            </div>
            <form method="POST" action="{{ route('finance-transactions.balance-sheet.cutoff') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="required form-label">Tahun</label>
                        <select name="year" class="form-select" required>
                            @for($optionYear = now()->year; $optionYear >= now()->year - 10; $optionYear--)
                                <option value="{{ $optionYear }}" @selected($optionYear === $year)>{{ $optionYear }}</option>
                            @endfor
                        </select>
                        <div class="form-text">Tanggal cut off otomatis pada 31 Desember tahun terpilih.</div>
                    </div>
                    <div>
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control" placeholder="Contoh: Neraca akhir tahun {{ now()->year }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan Cut Off</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('printBalanceSheet')?.addEventListener('click', function(){
    window.print();
});

document.getElementById('downloadBalanceExcel')?.addEventListener('click', function(){
    var html = '<html><head><meta charset="UTF-8"></head><body>';
    html += '<h2>Neraca Keuangan {{ $year }}</h2>';
    html += '<p>Periode: 1 Januari {{ $year }} sampai {{ $cutoffDateText }}</p>';
    html += document.getElementById('assetBalanceTable').outerHTML;
    html += '<br>';
    html += document.getElementById('liabilityBalanceTable').outerHTML;
    html += '</body></html>';
    var blob = new Blob([html], {type: 'application/vnd.ms-excel;charset=utf-8;'});
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'neraca-keuangan-{{ $year }}.xls';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
});
</script>
@endpush

@push('styles')
<style>
.balance-hero{border:1px solid #e4e8f0;border-radius:20px;background:#fff;box-shadow:0 18px 44px rgba(15,23,42,.06);overflow:hidden}
.balance-print-header{display:none}
.balance-hero-main{padding:30px;background:linear-gradient(135deg,#f8fbff 0%,#fff 62%)}
.balance-eyebrow{display:inline-flex;align-items:center;border:1px solid #dbeafe;background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.03em}
.balance-period-line{display:flex;flex-wrap:wrap;gap:10px;color:#64748b;font-size:13px;font-weight:600}
.balance-period-line span{display:inline-flex;align-items:center;gap:7px;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px}
.balance-status-pill{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:8px 12px;font-size:12px;font-weight:800}
.balance-status-pill.is-balanced{background:#ecfdf3;color:#15803d}.balance-status-pill.is-warning{background:#fffbeb;color:#b45309}
.balance-hero-total{min-width:280px;border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:18px;box-shadow:0 10px 24px rgba(15,23,42,.04)}
.balance-control-card{border:1px solid #e4e8f0;border-radius:16px;box-shadow:0 12px 30px rgba(15,23,42,.04)}
.balance-icon-btn{width:36px;height:36px;border-radius:10px}
.balance-summary-card{border:1px solid #e4e8f0;border-radius:16px;padding:18px;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.04);position:relative;overflow:hidden;display:grid;grid-template-columns:44px minmax(0,1fr);column-gap:14px;align-items:center}
.balance-summary-card::after{content:"";position:absolute;width:96px;height:96px;right:-36px;top:-36px;border-radius:50%;background:rgba(255,255,255,.65)}
.balance-summary-card .summary-icon{width:44px;height:44px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;grid-row:1 / span 2}
.balance-summary-card .label{font-size:11px;text-transform:uppercase;letter-spacing:.02em;font-weight:800;color:#64748b;margin-bottom:4px;position:relative;z-index:1;line-height:1.2}
.balance-summary-card .value{font-size:19px;font-weight:800;color:#111827;position:relative;z-index:1;line-height:1.25;min-width:0;overflow-wrap:anywhere}
.balance-summary-blue{background:linear-gradient(135deg,#eff6ff,#fff);border-color:#bfdbfe}.balance-summary-blue .summary-icon{color:#1d4ed8}
.balance-summary-red{background:linear-gradient(135deg,#fff1f2,#fff);border-color:#fecdd3}.balance-summary-red .summary-icon{color:#be123c}
.balance-summary-green,.balance-summary-emerald{background:linear-gradient(135deg,#ecfdf3,#fff);border-color:#bbf7d0}.balance-summary-green .summary-icon,.balance-summary-emerald .summary-icon{color:#15803d}
.balance-summary-rose{background:linear-gradient(135deg,#fff1f2,#fff);border-color:#fecdd3}.balance-summary-rose .summary-icon{color:#be123c}
.balance-alert{border:1px solid #e4e8f0;border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:12px;background:#fff}
.balance-alert.is-balanced{border-color:#bbf7d0;background:#f0fdf4;color:#166534}.balance-alert.is-warning{border-color:#fde68a;background:#fffbeb;color:#92400e}
.balance-sheet-card{border:1px solid #dfe6f2;border-radius:16px;overflow:hidden;box-shadow:0 12px 30px rgba(15,23,42,.045);background:#fff}
.balance-table-head{min-height:82px!important;background:#f8fafc;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:18px;color:#111827;padding:0 36px!important;border-bottom:1px solid #e4e8f0}
.balance-table-head h3{font-size:18px;margin:0;line-height:1.2}.balance-table-head span{font-weight:800;color:#475569;font-size:15px;line-height:1.2;white-space:nowrap;padding-left:16px}.balance-table-head-primary{background:#eff6ff;color:#1d4ed8}.balance-table-head-primary span{color:#1d4ed8}
.balance-table{color:#17213b;table-layout:fixed;width:100%;border-collapse:separate;border-spacing:0;border:1px solid #edf1f6;border-radius:12px;overflow:hidden;background:#fff}
.balance-name-col{width:60%}.balance-value-col{width:40%}
.balance-table td{padding:11px 14px;border-bottom:1px solid #edf1f6;vertical-align:middle;line-height:1.35;height:46px}
.balance-table tbody tr:last-child td{border-bottom:1px solid #edf1f6}
.balance-table td:first-child{font-weight:600;color:#334155;white-space:normal;word-break:break-word}.balance-table td.text-end{font-weight:800;color:#0f172a;font-variant-numeric:tabular-nums;white-space:nowrap}
.balance-table .section-row td{height:42px;background:#eef6ff;color:#1d4ed8;font-size:11px;text-transform:uppercase;font-weight:900;letter-spacing:.04em;border-bottom:1px solid #dbeafe}
.balance-table .section-row.danger td{background:#fff1f2;color:#be123c;border-bottom-color:#ffe4e6}
.balance-table .section-row.success td{background:#ecfdf3;color:#15803d;border-bottom-color:#dcfce7}
.balance-table .subtotal-row td{background:#f8fbff;color:#1d4ed8;font-weight:900}
.balance-table .subtotal-row.danger td{background:#fff7f8;color:#be123c}
.balance-table .subtotal-row.success td{background:#f0fdf4;color:#15803d}
.balance-table tfoot td{height:52px;background:#eef6ff;color:#1d4ed8;font-size:14px;font-weight:900;border-bottom:0;border-top:1px solid #dbeafe}
.balance-table tfoot td:first-child{padding-left:18px}.balance-table tfoot td.text-end{padding-right:18px}
@media (max-width:575.98px){.balance-hero-main{padding:22px}.balance-hero-total{min-width:0}.balance-summary-card{grid-template-columns:38px minmax(0,1fr);column-gap:10px}.balance-summary-card .summary-icon{width:38px;height:38px}.balance-summary-card .value{font-size:15px}.balance-table td{padding:10px 11px;height:42px}.balance-table td.text-end{font-size:12px}.balance-table-head{min-height:64px!important;padding:0 18px!important}.balance-table-head h3{font-size:15px}.balance-table-head span{font-size:12px;padding-left:8px}.balance-name-col{width:56%}.balance-value-col{width:44%}}
@media print{
    @page{size:A4;margin:14mm}
    body{background:#fff!important;color:#111827!important}
    body *{visibility:hidden!important}
    #balancePrintArea,#balancePrintArea *{visibility:visible!important}
    #balancePrintArea{position:absolute;left:0;top:0;width:100%;padding:0;background:#fff;font-family:Arial,Helvetica,sans-serif}
    .app-header,.app-sidebar,.app-toolbar,.balance-control-card,.balance-hero,.modal,.btn{display:none!important}
    .balance-print-header{display:flex!important;visibility:visible!important;align-items:flex-start;justify-content:space-between;border-bottom:2px solid #111827;padding-bottom:14px;margin-bottom:16px}
    .balance-print-company{font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;color:#475569;margin-bottom:4px}
    .balance-print-header h1{font-size:22px;line-height:1.2;margin:0 0 5px;color:#111827}
    .balance-print-header p{margin:0;color:#475569;font-size:12px}
    .balance-print-meta{text-align:right;border:1px solid #cbd5e1;border-radius:8px;padding:9px 12px;min-width:150px}
    .balance-print-meta div{font-size:10px;text-transform:uppercase;color:#64748b;font-weight:700}
    .balance-print-meta strong{display:block;font-size:18px;color:#111827;line-height:1.2}
    .balance-print-meta span{display:block;font-size:11px;color:#475569;margin-top:3px}
    .row{display:flex!important;flex-wrap:wrap!important;margin-left:-6px!important;margin-right:-6px!important}
    .col,.col-xl-4{width:25%!important;max-width:25%!important;padding-left:6px!important;padding-right:6px!important;margin-bottom:12px}
    .col-lg-6{width:50%!important;max-width:50%!important;padding-left:6px!important;padding-right:6px!important;margin-bottom:0}
    .balance-summary-card{box-shadow:none!important;border:1px solid #cbd5e1!important;border-radius:8px!important;padding:10px!important;display:block!important;min-height:72px}
    .balance-summary-card::after,.balance-summary-card .summary-icon{display:none!important}
    .balance-summary-card .label{font-size:9px!important;color:#475569!important;margin-bottom:4px!important}
    .balance-summary-card .value{font-size:13px!important;color:#111827!important;line-height:1.2!important;overflow-wrap:normal!important}
    .balance-alert{box-shadow:none!important;border-radius:8px!important;padding:9px 11px!important;margin-bottom:14px!important;font-size:12px!important}
    .balance-sheet-card{box-shadow:none!important;border:1px solid #cbd5e1!important;border-radius:8px!important;break-inside:avoid}
    .balance-sheet-card .card-body{padding:8px!important}
    .balance-table-head{min-height:44px!important;padding:0 12px!important;border-bottom:1px solid #cbd5e1!important;background:#f8fafc!important}
    .balance-table-head h3{font-size:14px!important;color:#111827!important}
    .balance-table-head span{font-size:12px!important;color:#111827!important}
    .balance-table{border:1px solid #cbd5e1!important;border-radius:6px!important;font-size:11px!important}
    .balance-table td{padding:7px 8px!important;height:auto!important;border-bottom:1px solid #e2e8f0!important;line-height:1.25!important}
    .balance-table td:first-child{color:#1f2937!important}
    .balance-table td.text-end{font-size:11px!important;color:#111827!important}
    .balance-table .section-row td{background:#eef2f7!important;color:#111827!important;font-size:9px!important}
    .balance-table .subtotal-row td{background:#f8fafc!important;color:#111827!important}
    .balance-table tfoot td{background:#e2e8f0!important;color:#111827!important;font-size:12px!important}
    #balancePrintArea::after{content:"Dicetak pada {{ now()->format('d/m/Y H:i') }}";display:block;visibility:visible!important;margin-top:16px;padding-top:8px;border-top:1px solid #cbd5e1;color:#64748b;font-size:10px;text-align:right}
}
</style>
@endpush
