@extends('layouts.app')

@section('title', 'Detail Input Keuangan')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Keuangan</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('finance-transactions.index') }}" class="text-muted text-hover-primary">Input Keuangan</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('finance-transactions.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
    @can('finance-transactions.edit')
    @if($finance_transaction->status !== 'disetujui')
    <a href="{{ route('finance-transactions.edit', $finance_transaction) }}" class="btn btn-sm btn-warning">
        <i class="ki-duotone ki-pencil fs-3"></i> Edit
    </a>
    @endif
    @endcan
@endsection

@push('styles')
<style>
.finance-detail-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}
.finance-detail-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}
.finance-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center}
.finance-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}
.finance-amount-panel{height:100%;padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);color:#fff;position:relative;overflow:hidden}
.finance-amount-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}
.finance-info-card{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:16px;height:100%;box-shadow:0 8px 22px rgba(15,23,42,.035)}
.finance-info-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.finance-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.finance-meta-chip{display:inline-flex;align-items:center;gap:7px;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}
.finance-order-link{border-radius:12px}
.finance-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}
.finance-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}
.finance-person-card .value{color:#fff;font-weight:700}
.finance-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}
.finance-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#f8fbff;padding:18px;min-height:130px}
.finance-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%;transition:transform .15s ease,box-shadow .15s ease}
.finance-evidence-tile:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(15,23,42,.08)}
.finance-evidence-frame{height:220px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}
.bank-mini-logo{width:42px;height:42px;border:1px solid #e4e8f0;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bank-mini-logo img{max-width:30px;max-height:22px;object-fit:contain}
.bank-mini-logo span{font-size:11px;font-weight:800;color:#1d4ed8}
</style>
@endpush

@section('content')
@php
    $statusConfig = match($finance_transaction->status) {
        'disetujui' => [
            'label' => 'Disetujui',
            'badge' => 'badge-light-success',
            'text' => 'text-success',
            'bg' => 'bg-light-success',
            'icon' => 'ki-check-circle',
            'description' => 'Transaksi sudah disetujui dan saldo bank telah diperbarui.',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'badge' => 'badge-light-danger',
            'text' => 'text-danger',
            'bg' => 'bg-light-danger',
            'icon' => 'ki-cross-circle',
            'description' => 'Transaksi ditolak. Lihat alasan reject untuk perbaikan.',
        ],
        default => [
            'label' => 'Menunggu Approval',
            'badge' => 'badge-light-warning',
            'text' => 'text-warning',
            'bg' => 'bg-light-warning',
            'icon' => 'ki-time',
            'description' => 'Transaksi sudah diajukan dan menunggu persetujuan.',
        ],
    };
    $typeConfig = $finance_transaction->transaction_type === 'income'
        ? ['label' => 'Uang Masuk', 'text' => 'text-success', 'badge' => 'badge-light-success', 'sign' => '+']
        : ['label' => 'Uang Keluar', 'text' => 'text-danger', 'badge' => 'badge-light-danger', 'sign' => '-'];
    $bankAccount = $finance_transaction->bankAccount;
    $bankName = strtoupper($bankAccount?->bank_name ?? 'BANK');
    $bankInitials = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $bankAccount?->bank_name ?? 'Bank')))
        ->filter()
        ->map(fn ($word) => substr($word, 0, 1))
        ->take(3)
        ->implode('');
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
    $activityLabel = $finance_transaction->activity ?: $finance_transaction->description;
    $isOrderPayment = ($finance_transaction->item?->code === 'AUTO-ORDER') || \Illuminate\Support\Str::startsWith($activityLabel, 'Pembayaran Order');
    $categoryLabel = $isOrderPayment ? 'Order Bengkel' : ($finance_transaction->item?->category?->name ?? '-');
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

<div class="finance-detail-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 finance-detail-main">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-4 mb-7">
                <div class="d-flex align-items-start gap-4">
                    <div class="finance-status-icon {{ $statusConfig['bg'] }}">
                        <i class="ki-outline {{ $statusConfig['icon'] }} fs-1 {{ $statusConfig['text'] }}"></i>
                    </div>
                    <div>
                        <div class="finance-number-pill mb-3">
                            <i class="ki-duotone ki-wallet fs-5"><span class="path1"></span><span class="path2"></span></i>
                            {{ $finance_transaction->transaction_number }}
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                            <h1 class="fw-bolder text-gray-900 mb-0 fs-2">{{ $activityLabel }}</h1>
                            <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                        </div>
                        <div class="text-gray-600">{{ $statusConfig['description'] }}</div>
                        <div class="finance-meta-line">
                            <span class="finance-meta-chip">
                                <i class="ki-duotone ki-calendar fs-5"><span class="path1"></span><span class="path2"></span></i>
                                {{ $finance_transaction->transaction_date?->format('d/m/Y') ?? '-' }}
                            </span>
                            <span class="finance-meta-chip">{{ $categoryLabel }}</span>
                            <span class="finance-meta-chip">{{ $finance_transaction->item?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                @if($relatedOrder)
                    <div class="align-self-md-start">
                        <a href="{{ route('orders.show', $relatedOrder) }}" class="btn btn-light-primary finance-order-link">
                            <i class="ki-duotone ki-document fs-3"><span class="path1"></span><span class="path2"></span></i>
                            Lihat Detail Order
                        </a>
                    </div>
                @endif
                @if($relatedDebtReceivable)
                    <div class="align-self-md-start">
                        <a href="{{ route('debt-receivables.show', $relatedDebtReceivable) }}" class="btn btn-light-info finance-order-link">
                            <i class="ki-duotone ki-arrows-circle fs-3"><span class="path1"></span><span class="path2"></span></i>
                            Lihat Detail Hutang/Piutang
                        </a>
                    </div>
                @endif
                @if($relatedRevenueSharing)
                    <div class="align-self-md-start">
                        <a href="{{ route('revenue-sharings.show', $relatedRevenueSharing) }}" class="btn btn-light-success finance-order-link">
                            <i class="ki-duotone ki-chart-line-up fs-3"><span class="path1"></span><span class="path2"></span></i>
                            Lihat Detail Revenue Sharing
                        </a>
                    </div>
                @endif
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="finance-info-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="finance-info-icon {{ $typeConfig['badge'] }}"><i class="ki-duotone ki-arrow-up-down fs-2 {{ $typeConfig['text'] }}"></i></div>
                            <div>
                                <div class="text-muted fs-8 text-uppercase fw-semibold">Jenis Transaksi</div>
                                <div class="fw-bold text-gray-900">{{ $typeConfig['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="finance-info-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bank-mini-logo">
                                @if($bankLogoUrl)
                                    <img src="{{ $bankLogoUrl }}" alt="{{ $bankAccount?->bank_name ?? 'Bank/Cash' }}">
                                @else
                                    <span>{{ $bankInitials ?: 'BNK' }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted fs-8 text-uppercase fw-semibold">Bank/Cash</div>
                                <div class="fw-bold text-gray-900">{{ $bankAccount?->code ?? '-' }}</div>
                                <div class="text-muted fs-8 text-truncate">{{ $bankAccount?->bank_name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="finance-info-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="finance-info-icon bg-light-info"><i class="ki-duotone ki-file fs-2 text-info"><span class="path1"></span><span class="path2"></span></i></div>
                            <div>
                                <div class="text-muted fs-8 text-uppercase fw-semibold">Eviden</div>
                                <div class="fw-bold text-gray-900">{{ $evidenceFiles->count() }} File</div>
                                <div class="text-muted fs-8">Lampiran transaksi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="finance-amount-panel d-flex flex-column justify-content-between">
                <div class="position-relative">
                    <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Nominal Transaksi</div>
                    <div class="fw-bolder fs-1 mb-2 {{ $finance_transaction->transaction_type === 'income' ? 'text-success' : 'text-danger' }}">
                        {{ $typeConfig['sign'] }} Rp {{ number_format($finance_transaction->amount, 0, ',', '.') }}
                    </div>
                    <span class="badge {{ $typeConfig['badge'] }}">{{ $typeConfig['label'] }}</span>
                </div>
                <div class="position-relative mt-8">
                    <div class="finance-person-card mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-user fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diajukan Oleh</div>
                                <div class="value">{{ $finance_transaction->submitter?->name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ $finance_transaction->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="finance-person-card">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-shield-tick fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diproses Oleh</div>
                                <div class="value">{{ $finance_transaction->approver?->name ?? $finance_transaction->rejecter?->name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ ($finance_transaction->approved_at ?: $finance_transaction->rejected_at)?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($finance_transaction->status === 'ditolak')
<div class="alert alert-danger d-flex align-items-start mb-7">
    <i class="ki-outline ki-information-5 fs-2 text-danger me-3"></i>
    <div>
        <div class="fw-bold mb-1">Alasan Reject</div>
        <div>{{ $finance_transaction->rejection_reason }}</div>
    </div>
</div>
@endif

@can('finance-transactions.approve')
@if($finance_transaction->status === 'menunggu_approval')
<div class="card card-flush mb-7">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="fw-bold">Approval Input Keuangan</h3>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="finance-info-card p-5">
                    <div class="fw-bold fs-5 mb-2">Setujui transaksi</div>
                    <div class="text-muted mb-5">Saldo bank akan diperbarui setelah approval berhasil.</div>
                    <form method="POST" action="{{ route('finance-transactions.approve', $finance_transaction) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ki-duotone ki-check fs-2 text-white"></i> Approve Transaksi
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="finance-info-card p-5">
                    <div class="fw-bold fs-5 mb-2">Reject transaksi</div>
                    <form method="POST" action="{{ route('finance-transactions.reject', $finance_transaction) }}">
                        @csrf
                        <label class="required form-label fw-semibold">Alasan Reject</label>
                        <textarea name="rejection_reason" class="form-control mb-3" rows="4" required placeholder="Tuliskan alasan agar pemohon tahu apa yang perlu diperbaiki.">{{ old('rejection_reason') }}</textarea>
                        <button type="submit" class="btn btn-danger">
                            <i class="ki-duotone ki-cross fs-2 text-white"><span class="path1"></span><span class="path2"></span></i> Reject Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endcan

<div class="row g-7">
    <div class="col-lg-5">
        <div class="card card-flush h-100 finance-section-card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="finance-info-icon bg-light-primary"><i class="ki-duotone ki-note-2 fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i></span>
                        <h3 class="fw-bold mb-0">Catatan</h3>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="finance-note-box text-gray-700 fs-6">{{ $finance_transaction->notes ?: 'Tidak ada catatan tambahan.' }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-flush h-100 finance-section-card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center gap-3">
                        <span class="finance-info-icon bg-light-info"><i class="ki-duotone ki-file fs-2 text-info"><span class="path1"></span><span class="path2"></span></i></span>
                        <div>
                            <h3 class="fw-bold mb-0">Eviden</h3>
                            <div class="text-muted fs-8">{{ $evidenceFiles->count() }} lampiran transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                @if($evidenceFiles->isNotEmpty())
                    <div class="row g-4">
                        @foreach($evidenceFiles as $evidence)
                            <div class="col-md-6">
                                @if($evidence->is_image)
                                    <a href="{{ $evidence->url }}" target="_blank" class="finance-evidence-tile d-block">
                                        <div class="finance-evidence-frame mb-3">
                                            <img src="{{ $evidence->url }}" alt="Eviden transaksi" class="w-100 h-100" style="object-fit:contain;">
                                        </div>
                                        <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                                    </a>
                                @else
                                    <div class="finance-evidence-tile">
                                        <div class="finance-evidence-frame mb-3">
                                            <iframe src="{{ $evidence->url }}" class="w-100 h-100 border-0"></iframe>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                                            <a href="{{ $evidence->url }}" target="_blank" class="btn btn-sm btn-light-primary">
                                                <i class="ki-duotone ki-document fs-3"></i> Buka
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-10">
                        <i class="ki-outline ki-file-deleted fs-2x mb-3"></i>
                        <div>Belum ada eviden.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
