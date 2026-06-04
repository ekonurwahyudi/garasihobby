@extends('layouts.app')

@section('title', 'Detail Pembelian Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('material-purchases.index') }}" class="text-muted text-hover-primary">Pembelian Material</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Detail</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('material-purchases.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
    @can('purchases.approve')
        @if($summary->status === 'menunggu_approval')
            <form action="{{ route('material-purchases.accept', $summary->invoice_number) }}" method="POST" class="d-inline accept-purchase-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="ki-duotone ki-check fs-3"></i> Accept
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="ki-duotone ki-cross fs-3"></i> Reject
            </button>
        @endif
    @endcan
    @can('purchases.edit')
        <a href="{{ route('material-purchases.edit', $summary->invoice_number) }}" class="btn btn-sm btn-warning">
            <i class="ki-duotone ki-pencil fs-3"></i> Edit
        </a>
    @endcan
    @can('purchases.delete')
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteTransaction('{{ $summary->invoice_number }}')">
            <i class="ki-duotone ki-trash fs-3"></i> Delete
        </button>
    @endcan
@endsection

@section('content')
@php
    $statusConfig = match($summary->status) {
        'disetujui' => ['label' => 'Disetujui', 'badge' => 'badge-light-success', 'bg' => 'bg-light-success', 'text' => 'text-success', 'icon' => 'ki-check-circle'],
        'ditolak' => ['label' => 'Ditolak', 'badge' => 'badge-light-danger', 'bg' => 'bg-light-danger', 'text' => 'text-danger', 'icon' => 'ki-cross-circle'],
        default => ['label' => 'Awaiting', 'badge' => 'badge-light-warning', 'bg' => 'bg-light-warning', 'text' => 'text-warning', 'icon' => 'ki-time'],
    };
@endphp
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
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="purchase-hero mb-7">
    <div class="row g-0">
        <div class="col-xl-8 purchase-hero-main">
            <div class="d-flex align-items-start gap-4 mb-7">
                <div class="purchase-status-icon {{ $statusConfig['bg'] }}">
                    <i class="ki-outline {{ $statusConfig['icon'] }} fs-1 {{ $statusConfig['text'] }}"></i>
                </div>
                <div>
                    <div class="purchase-number-pill mb-3">
                        <i class="ki-duotone ki-parcel fs-5"><span class="path1"></span><span class="path2"></span></i>
                        {{ $summary->invoice_number }}
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        <h1 class="fw-bolder fs-2 text-gray-900 mb-0">Detail Pembelian Material</h1>
                        <span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                    </div>
                    <div class="text-gray-600">Pembelian dari {{ $summary->supplier ?? 'supplier belum diisi' }} pada {{ $summary->purchase_date?->format('d/m/Y') ?? '-' }}</div>
                    <div class="purchase-meta-line">
                        <span class="purchase-meta-chip">{{ $summary->item_count }} item</span>
                        <span class="purchase-meta-chip">{{ $summary->evidence_files->count() }} eviden</span>
                        <span class="purchase-meta-chip">{{ $summary->bank_name ? ($summary->bank_code . ' - ' . $summary->bank_name) : 'Bank belum dipilih' }}</span>
                        <span class="purchase-meta-chip">{{ $summary->notes ? 'Ada catatan' : 'Tanpa catatan' }}</span>
                    </div>
                </div>
            </div>
            <div class="purchase-note-box">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Catatan</div>
                <div class="fw-semibold text-gray-800">{{ $summary->notes ?? 'Tidak ada catatan tambahan.' }}</div>
            </div>
            @if($summary->status === 'ditolak')
            <div class="alert alert-danger mt-5 mb-0">
                <div class="fw-bold mb-1">Alasan Penolakan</div>
                <div>{{ $summary->rejection_reason ?? '-' }}</div>
            </div>
            @endif
        </div>
        <div class="col-xl-4">
            <div class="purchase-total-panel h-100 d-flex flex-column justify-content-between">
                <div class="position-relative">
                    <div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Total Pembelian</div>
                    <div class="fw-bolder fs-1 text-white">Rp {{ number_format($summary->total_price, 0, ',', '.') }}</div>
                    <div class="text-white-50 fs-8 mt-2">Mutasi uang keluar saat pembelian disetujui.</div>
                </div>
                <div class="position-relative mt-8">
                    <div class="purchase-person-card mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="bank-logo-circle bg-white">
                                @if($summary->bank_logo_url)
                                    <img src="{{ $summary->bank_logo_url }}" alt="{{ $summary->bank_name ?? 'Bank' }}">
                                @else
                                    <span>{{ $summary->bank_logo_text ?? 'BNK' }}</span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <div class="label">Bank Pembayaran</div>
                                <div class="value text-truncate">{{ $summary->bank_name ? ($summary->bank_code . ' - ' . $summary->bank_name) : '-' }}</div>
                                <div class="text-white-50 fs-8 text-truncate">
                                    {{ $summary->bank_account_name ?? '-' }}{{ $summary->bank_account_number ? ' / ' . $summary->bank_account_number : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="purchase-person-card mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-user fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diajukan Oleh</div>
                                <div class="value">{{ $summary->submitter_name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ $summary->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="purchase-person-card">
                        <div class="d-flex align-items-center gap-3">
                            <span class="symbol symbol-38px"><span class="symbol-label bg-white"><i class="ki-outline ki-shield-tick fs-3 text-primary"></i></span></span>
                            <div>
                                <div class="label">Diproses Oleh</div>
                                <div class="value">{{ $summary->processor_name ?? '-' }}</div>
                                <div class="text-white-50 fs-8">{{ $summary->processed_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush purchase-section-card mb-5">
    <div class="card-header border-0 pt-4">
        <div class="card-title">
            <h4 class="fw-bold mb-0">Item Pembelian</h4>
        </div>
    </div>
    <div class="card-body pt-0 pb-4">
        <div class="table-responsive">
            <table class="table table-row-bordered gy-4 gs-5 border rounded fs-7 mb-0 purchase-table">
                <thead>
                    <tr class="fw-semibold text-gray-800">
                        <th class="w-50px">No</th>
                        <th>Nama Material</th>
                        <th>Kategori</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-bold">{{ $item->material->name ?? '-' }}</td>
                        <td>{{ $item->material->category->name ?? '-' }}</td>
                        <td>{{ $item->qty }} {{ $item->unit ?? '' }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Grand Total</td>
                        <td class="fw-bold">Rp {{ number_format($summary->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card card-flush purchase-section-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3 class="fw-bold">Eviden Pembelian</h3>
        </div>
    </div>
    <div class="card-body pt-0">
        @if($summary->evidence_files->isNotEmpty())
            <div class="row g-4">
                @foreach($summary->evidence_files as $evidence)
                    <div class="col-md-6 col-xl-4">
                        @if($evidence->is_image)
                            <a href="{{ $evidence->url }}" target="_blank" class="purchase-evidence-tile d-block">
                                <div class="purchase-evidence-frame mb-3">
                                    <img src="{{ $evidence->url }}" alt="Eviden pembelian" class="w-100 h-100" style="object-fit:contain;">
                                </div>
                                <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                            </a>
                        @else
                            <div class="purchase-evidence-tile">
                                <div class="purchase-evidence-frame mb-3">
                                    <iframe src="{{ $evidence->url }}" class="w-100 h-100 border-0"></iframe>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                                    <a href="{{ $evidence->url }}" target="_blank" class="btn btn-sm btn-light-primary">Buka</a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted">Belum ada eviden.</div>
        @endif
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
            <form action="{{ route('material-purchases.reject', $summary->invoice_number) }}" method="POST">
                @csrf
                <div class="modal-body mx-5 my-7">
                    <label class="required form-label fw-semibold">Alasan Penolakan</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" required>{{ old('rejection_reason') }}</textarea>
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
.purchase-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}
.purchase-hero-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}
.purchase-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.purchase-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}
.purchase-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.purchase-meta-chip{display:inline-flex;align-items:center;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}
.purchase-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#fff;padding:18px}
.purchase-total-panel{padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);position:relative;overflow:hidden}
.purchase-total-panel::after{content:"";position:absolute;width:150px;height:150px;right:-54px;top:-54px;border-radius:50%;background:rgba(255,255,255,.08)}
.purchase-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}
.purchase-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}
.purchase-person-card .value{color:#fff;font-weight:700}
.bank-logo-circle{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
.bank-logo-circle img{max-width:30px;max-height:22px;object-fit:contain}
.bank-logo-circle span{font-size:11px;font-weight:800;color:#1d4ed8}
.purchase-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}
.purchase-table thead th{background:#f3f6fa;color:#061535;font-weight:700;white-space:nowrap}
.purchase-table tbody td{vertical-align:middle}
.purchase-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%;transition:transform .15s ease,box-shadow .15s ease}
.purchase-evidence-tile:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(15,23,42,.08)}
.purchase-evidence-frame{height:240px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.accept-purchase-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
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
                window.location.href = '{{ route('material-purchases.index') }}';
                return;
            }
            Swal.fire('Gagal', 'Transaksi tidak bisa dihapus.', 'error');
        });
    });
}
</script>
@endpush
