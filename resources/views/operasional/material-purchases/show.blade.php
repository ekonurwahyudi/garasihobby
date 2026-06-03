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
            <form action="{{ route('material-purchases.accept', $summary->invoice_number) }}" method="POST" class="d-inline">
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

<div class="card card-flush mb-7">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2 class="fw-bold">Detail Pembelian Material</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row gy-5">
            <div class="col-md-3">
                <div class="text-muted fs-7">No. Transaksi</div>
                <div class="fw-bold fs-5">{{ $summary->invoice_number }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted fs-7">Tanggal Kwitansi</div>
                <div class="fw-bold fs-5">{{ $summary->purchase_date?->format('d/m/Y') ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted fs-7">Supplier</div>
                <div class="fw-bold fs-5">{{ $summary->supplier ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted fs-7">Total</div>
                <div class="fw-bold fs-5">Rp {{ number_format($summary->total_price, 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted fs-7">Status</div>
                <div class="fw-bold fs-5">
                    @switch($summary->status)
                        @case('menunggu_approval')
                            <span class="badge badge-light-warning">Menunggu Approval</span>
                            @break
                        @case('ditolak')
                            <span class="badge badge-light-danger">Ditolak</span>
                            @break
                        @case('disetujui')
                            <span class="badge badge-light-success">Disetujui</span>
                            @break
                        @default
                            <span class="badge badge-light">{{ $summary->status }}</span>
                    @endswitch
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-muted fs-7">Jumlah Item</div>
                <div class="fw-bold fs-5">{{ $summary->item_count }} item</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted fs-7">Catatan</div>
                <div class="fw-bold fs-5">{{ $summary->notes ?? '-' }}</div>
            </div>
            @if($summary->status === 'ditolak')
            <div class="col-md-12">
                <div class="text-muted fs-7">Alasan Penolakan</div>
                <div class="fw-bold fs-5 text-danger">{{ $summary->rejection_reason ?? '-' }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card card-flush mb-5">
    <div class="card-header border-0 pt-4">
        <div class="card-title">
            <h4 class="fw-bold mb-0">Item Pembelian</h4>
        </div>
    </div>
    <div class="card-body pt-0 pb-4">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-row-bordered gy-3 gs-4 border rounded fs-7 mb-0">
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

<div class="card card-flush">
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
                            <a href="{{ $evidence->url }}" target="_blank" class="d-block border rounded bg-light p-3 h-100">
                                <img src="{{ $evidence->url }}" alt="Eviden pembelian" class="rounded d-block mx-auto" style="width:100%; height:260px; object-fit:contain;">
                            </a>
                        @else
                            <div class="border rounded overflow-hidden">
                                <iframe src="{{ $evidence->url }}" class="w-100 border-0" style="height:260px;"></iframe>
                            </div>
                            <a href="{{ $evidence->url }}" target="_blank" class="btn btn-sm btn-light-primary mt-2">Buka PDF</a>
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

@push('scripts')
<script>
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
