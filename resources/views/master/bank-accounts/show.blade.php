@extends('layouts.app')

@section('title', 'Mutasi ' . $bank_account->bank_name)

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('bank-accounts.index') }}" class="text-muted text-hover-primary">Account Bank</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Detail Mutasi</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('bank-accounts.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@push('styles')
<style>
.bank-hero{border:1px solid #e4e8f0;border-radius:16px;overflow:hidden;box-shadow:0 12px 32px rgba(15,23,42,.05)}
.bank-hero-top{background:linear-gradient(135deg,#eff6ff 0%,#fff 58%);padding:24px}
.bank-logo-box{width:58px;height:58px;border:1px solid #dfe6f2;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center}
.bank-logo-box img{max-width:42px;max-height:28px;object-fit:contain}
.bank-logo-box span{font-size:14px;font-weight:800;color:#1d4ed8}
.bank-stat-card{border:1px solid #e4e8f0;border-radius:14px;background:#fff;padding:18px;height:100%}
.bank-stat-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center}
.bank-table-card{border:1px solid #e4e8f0;border-radius:14px;box-shadow:0 10px 28px rgba(15,23,42,.04)}
#kt_table thead th{font-size:13px;font-weight:700;background:#f5f7fb;color:#061535}
#kt_table tbody td{font-size:13px;vertical-align:middle}
.straighten-card{border:1px solid #dbe7ff;border-radius:14px;background:#f8fbff}
.straighten-form-wrap{border-top:1px dashed #cbdaf5;margin-top:18px;padding-top:18px}
.adjustment-card{border:1px solid #e4e8f0;border-radius:14px;box-shadow:0 10px 28px rgba(15,23,42,.04)}
.adjustment-row{border:1px solid #e9edf5;border-radius:14px;padding:16px;background:#fff}
.adjustment-amount{border-radius:12px;background:#f8fafc;padding:10px 12px}
.adjustment-label{white-space:nowrap}
</style>
@endpush

@section('content')
@php
    $totalIncome = $movements->where('type', 'income')->sum('amount');
    $totalExpense = $movements->where('type', 'expense')->sum('amount');
    $netMutation = $totalIncome - $totalExpense;
    $maskedAccount = $bank_account->account_number ? '**** ' . substr(preg_replace('/\s+/', '', $bank_account->account_number), -4) : 'Nomor belum diisi';
    $initials = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $bank_account->bank_name ?: 'Bank')))
        ->filter()
        ->map(fn ($word) => substr($word, 0, 1))
        ->take(3)
        ->implode('');
    $bankName = strtoupper($bank_account->bank_name ?? 'BANK');
    $bankLogoFile = str_contains($bankName, 'BCA DIGITAL') ? 'BCA Digital logo.svg' :
        (str_contains($bankName, 'BCA SYARIAH') ? 'BCA Syariah.svg' :
        (str_contains($bankName, 'BCA') ? 'Bank Central Asia.svg' :
        (str_contains($bankName, 'BRI') ? 'BRI 2020.svg' :
        (str_contains($bankName, 'BNI') ? 'Bank Negara Indonesia logo (2004).svg' :
        (str_contains($bankName, 'MANDIRI') ? 'Bank Mandiri logo 2016.svg' :
        (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA') ? 'Bank Syariah Indonesia.svg' :
        (str_contains($bankName, 'BTN') ? 'Bank BTN logo.svg' :
        (str_contains($bankName, 'CIMB') ? 'CIMB Niaga logo.svg' :
        (str_contains($bankName, 'DANAMON') ? 'Danamon.svg' :
        (str_contains($bankName, 'MEGA') ? 'Bank Mega 2013.svg' :
        (str_contains($bankName, 'PERMATA') ? 'Permata Bank (2024).svg' :
        (str_contains($bankName, 'PANIN') ? 'Logo Panin Bank.svg' :
        (str_contains($bankName, 'JAGO') ? 'Logo-jago.svg' :
        (str_contains($bankName, 'SEABANK') || str_contains($bankName, 'SEA BANK') ? 'SeaBank.svg' :
        (str_contains($bankName, 'UOB') ? 'UOB Logo (2022).svg' :
        (str_contains($bankName, 'DKI') ? 'Bank DKI.svg' : null))))))))))))))));
    $bankLogoUrl = $bankLogoFile
        ? 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($bankLogoFile) . '?width=160'
        : (str_contains($bankName, 'CASH') ? asset('assets/media/favicon.png') : null);
@endphp

@if($errors->any())
<div class="alert alert-danger mb-5">
    <div class="fw-bold mb-1">Terjadi kesalahan:</div>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="bank-hero mb-7">
    <div class="bank-hero-top">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-6">
            <div class="d-flex align-items-center">
                <div class="bank-logo-box me-4">
                    @if($bankLogoUrl)
                        <img src="{{ $bankLogoUrl }}" alt="{{ $bank_account->bank_name }}">
                    @else
                        <span>{{ $initials ?: 'BNK' }}</span>
                    @endif
                </div>
                <div>
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">{{ $bank_account->code }}</div>
                    <h2 class="fw-bold mb-1">{{ $bank_account->bank_name }}</h2>
                    <div class="text-gray-600">{{ $bank_account->account_name ?? '-' }} · {{ $maskedAccount }}</div>
                </div>
            </div>
            <div class="text-lg-end">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Saldo Saat Ini</div>
                <div class="fw-bolder fs-1 text-primary">Rp {{ number_format($bank_account->balance, 0, ',', '.') }}</div>
                <div class="text-muted fs-7">Saldo awal Rp {{ number_format($bank_account->opening_balance, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="p-5 bg-white">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="bank-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bank-stat-icon bg-light-success me-3"><i class="ki-duotone ki-arrow-up fs-2 text-success"></i></div>
                        <div>
                            <div class="text-muted fs-8">Total Masuk</div>
                            <div class="fw-bold text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bank-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bank-stat-icon bg-light-danger me-3"><i class="ki-duotone ki-arrow-down fs-2 text-danger"></i></div>
                        <div>
                            <div class="text-muted fs-8">Total Keluar</div>
                            <div class="fw-bold text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bank-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bank-stat-icon bg-light-primary me-3"><i class="ki-duotone ki-chart-line-up fs-2 text-primary"></i></div>
                        <div>
                            <div class="text-muted fs-8">Mutasi Bersih</div>
                            <div class="fw-bold {{ $netMutation >= 0 ? 'text-success' : 'text-danger' }}">{{ $netMutation >= 0 ? '+' : '-' }} Rp {{ number_format(abs($netMutation), 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="bank-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="bank-stat-icon bg-light-info me-3"><i class="ki-duotone ki-document fs-2 text-info"></i></div>
                        <div>
                            <div class="text-muted fs-8">Riwayat Mutasi</div>
                            <div class="fw-bold text-gray-900">{{ $movements->count() }} transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@can('bank-accounts.edit')
<div class="straighten-card p-5 mb-7">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-lg-center">
        <div>
            <div class="fw-bold fs-4 mb-1">Meluruskan Saldo</div>
            <div class="text-muted fs-7">Gunakan ketika saldo aktual bank berbeda. Perubahan akan tercatat di riwayat penyesuaian beserta alasannya.</div>
        </div>
        <button type="button" class="btn btn-light-primary" id="showStraightenFormBtn">
            <i class="ki-duotone ki-setting-4 fs-3"><span class="path1"></span><span class="path2"></span></i>
            Meluruskan Saldo
        </button>
    </div>
    <div class="straighten-form-wrap d-none" id="straightenFormWrap">
        <form method="POST" action="{{ route('bank-accounts.straighten-balance', $bank_account) }}" id="straightenBalanceForm" onsubmit="return confirm('Yakin meluruskan saldo? Riwayat dan alasan akan disimpan.')" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="required form-label fw-semibold">Saldo Benar</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="newBalanceDisplay" class="form-control" value="{{ number_format((int) $bank_account->balance, 0, ',', '.') }}" inputmode="numeric" autocomplete="off" required>
                </div>
                <input type="hidden" name="new_balance" id="newBalanceValue" value="{{ (int) $bank_account->balance }}">
            </div>
            <div class="col-md-5">
                <label class="required form-label fw-semibold">Alasan</label>
                <input type="text" name="reason" class="form-control" placeholder="Contoh: koreksi saldo aktual bank" required>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-light flex-fill" id="hideStraightenFormBtn">Batal</button>
                <button type="submit" class="btn btn-primary flex-fill"><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endcan

<div class="card card-flush adjustment-card mb-7">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div>
                <h3 class="fw-bold mb-1">Riwayat Meluruskan Saldo</h3>
                <div class="text-muted fs-7">Catatan perubahan saldo manual, berisi saldo sebelum, saldo baru, selisih, dan alasan koreksi.</div>
            </div>
        </div>
        <div class="card-toolbar">
            <span class="badge badge-light-primary">{{ $adjustments->count() }} adjustment</span>
        </div>
    </div>
    <div class="card-body pt-0">
        @forelse($adjustments as $adjustment)
            <div class="adjustment-row mb-3">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div class="min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge {{ $adjustment->type === 'increase' ? 'badge-light-success' : 'badge-light-danger' }}">
                                {{ $adjustment->type === 'increase' ? 'Saldo Naik' : 'Saldo Turun' }}
                            </span>
                            <span class="fw-semibold text-gray-700">ADJ-{{ str_pad($adjustment->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-muted fs-8">{{ $adjustment->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="fw-semibold text-gray-900 mb-1">{{ $adjustment->description ?: 'Penyesuaian saldo manual' }}</div>
                        <div class="text-muted fs-8">Dicatat oleh {{ $adjustment->creator?->name ?? '-' }}</div>
                    </div>
                    <div class="row g-3 flex-lg-nowrap">
                        <div class="col-12 col-md-4">
                            <div class="adjustment-amount">
                                <div class="text-muted fs-8 text-uppercase adjustment-label">Saldo Sebelum</div>
                                <div class="fw-bold text-gray-900">Rp {{ number_format($adjustment->previous_balance, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="adjustment-amount">
                                <div class="text-muted fs-8 text-uppercase adjustment-label">Saldo Baru</div>
                                <div class="fw-bold text-primary">Rp {{ number_format($adjustment->new_balance, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="adjustment-amount">
                                <div class="text-muted fs-8 text-uppercase adjustment-label">Selisih</div>
                                <div class="fw-bold {{ $adjustment->type === 'increase' ? 'text-success' : 'text-danger' }}">
                                    {{ $adjustment->type === 'increase' ? '+' : '-' }} Rp {{ number_format($adjustment->difference, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-8">
                <i class="ki-outline ki-notepad fs-2x mb-3"></i>
                <div>Belum ada riwayat meluruskan saldo.</div>
            </div>
        @endforelse
    </div>
</div>

<div class="card card-flush bank-table-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h3 class="fw-bold">Riwayat Mutasi</h3></div>
        <div class="card-toolbar position-relative">
            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
            <input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari mutasi...">
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_table" class="table table-row-bordered table-striped gy-4 gs-5 border rounded">
                <thead>
                    <tr class="fw-semibold text-gray-800">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Referensi</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Saldo</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $i => $m)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($m['date'])->format('d/m/Y') }}</td>
                        <td class="fw-semibold">{{ $m['reference'] }}</td>
                        <td>{{ $m['source'] }}</td>
                        <td>{{ $m['description'] }}</td>
                        <td><span class="badge {{ $m['type'] === 'income' ? 'badge-light-success' : 'badge-light-danger' }}">{{ $m['type'] === 'income' ? 'Masuk' : 'Keluar' }}</span></td>
                        <td class="{{ $m['type'] === 'income' ? 'text-success' : 'text-danger' }} fw-bold">{{ $m['type'] === 'income' ? '+' : '-' }} Rp {{ number_format($m['amount'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($m['balance'], 0, ',', '.') }}</td>
                        <td class="text-end">
                            @if($m['action_url'])
                            <a href="{{ $m['action_url'] }}" class="btn btn-icon btn-sm btn-light-primary" title="Detail Transaksi">
                                <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-gray-500 py-10">Belum ada mutasi rekening.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var table = $('#kt_table').DataTable({
    order: [],
    columnDefs: [{ orderable: false, targets: [0, 8] }],
    language: {
        zeroRecords: 'Data tidak ditemukan',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        paginate: {
            next: '<i class="ki-duotone ki-right fs-4"></i>',
            previous: '<i class="ki-duotone ki-left fs-4"></i>'
        }
    }
});
$('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });

function normalizeAmount(value) {
    return (value || '').toString().replace(/\D/g, '').replace(/^0+(?=\d)/, '');
}

function formatAmount(value) {
    var digits = normalizeAmount(value);
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

var showStraightenFormBtn = document.getElementById('showStraightenFormBtn');
var hideStraightenFormBtn = document.getElementById('hideStraightenFormBtn');
var straightenFormWrap = document.getElementById('straightenFormWrap');
var newBalanceDisplay = document.getElementById('newBalanceDisplay');
var newBalanceValue = document.getElementById('newBalanceValue');

if (showStraightenFormBtn && straightenFormWrap) {
    showStraightenFormBtn.addEventListener('click', function() {
        straightenFormWrap.classList.remove('d-none');
        showStraightenFormBtn.classList.add('d-none');
        if (newBalanceDisplay) newBalanceDisplay.focus();
    });
}

if (hideStraightenFormBtn && straightenFormWrap) {
    hideStraightenFormBtn.addEventListener('click', function() {
        straightenFormWrap.classList.add('d-none');
        if (showStraightenFormBtn) showStraightenFormBtn.classList.remove('d-none');
    });
}

if (newBalanceDisplay && newBalanceValue) {
    newBalanceDisplay.addEventListener('input', function() {
        var digits = normalizeAmount(this.value);
        this.value = formatAmount(digits);
        newBalanceValue.value = digits || '0';
    });
}
</script>
@endpush
