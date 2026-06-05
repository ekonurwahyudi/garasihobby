@extends('layouts.app')

@section('title', 'Pembelian Aset')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Pembelian Aset</li>
@endsection

@section('toolbar_actions')
@can('asset-purchases.create')
<a href="{{ route('asset-purchases.create') }}" class="btn btn-sm btn-primary"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span></i> Tambah Aset</a>
@endcan
@endsection

@section('content')
@php
    $approved = $data->where('status', 'disetujui');
    $pending = $data->where('status', 'menunggu_approval')->count();
@endphp
<div class="row g-5 mb-6">
    <div class="col-md-4"><div class="card finance-summary-card finance-summary-expense h-100"><div class="card-body position-relative"><div class="text-gray-600 fs-7 fw-semibold mb-4">Total Aset Disetujui</div><div class="text-primary fw-bolder fs-2">Rp {{ number_format($approved->sum('purchase_amount'), 0, ',', '.') }}</div><div class="text-muted fs-8 mt-1">{{ $approved->count() }} pembelian aset</div></div></div></div>
    <div class="col-md-4"><div class="card finance-summary-card finance-summary-net h-100"><div class="card-body position-relative"><div class="text-gray-600 fs-7 fw-semibold mb-4">Nilai Buku</div><div class="text-success fw-bolder fs-2">Rp {{ number_format($approved->sum('book_value'), 0, ',', '.') }}</div><div class="text-muted fs-8 mt-1">Akumulasi nilai buku aset</div></div></div></div>
    <div class="col-md-4"><div class="card finance-summary-card finance-summary-income h-100"><div class="card-body position-relative"><div class="text-gray-600 fs-7 fw-semibold mb-4">Menunggu Approval</div><div class="text-warning fw-bolder fs-2">{{ $pending }}</div><div class="text-muted fs-8 mt-1">Pengajuan aset belum diproses</div></div></div></div>
</div>

<div class="card card-flush finance-table-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><div class="d-flex align-items-center"><span class="text-gray-700 fs-7 me-2">Tampilkan</span><select id="lengthSelect" class="form-select form-select-sm w-75px"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></div></div>
        <div class="card-toolbar"><div class="d-flex align-items-center position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control finance-search w-250px ps-12" placeholder="Cari aset..."></div></div>
    </div>
    <div class="card-body pt-0">
        <div class="finance-table-wrap">
            <table id="kt_table" class="table align-middle">
                <thead><tr><th>#</th><th>No. Transaksi</th><th>Nama Aset</th><th>Tanggal</th><th>Bank/Cash</th><th>Nominal</th><th>Nilai Buku</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @foreach($data as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $item->asset_number }}</td>
                        <td><div class="fw-bold">{{ $item->asset_name }}</div><div class="text-muted fs-8">{{ $item->asset_category ?? 'Tanpa kategori' }}</div></td>
                        <td>{{ $item->purchase_date?->format('d/m/Y') ?? '-' }}</td>
                        <td><span class="badge badge-light-primary">{{ $item->bankAccount?->code ?? '-' }}</span></td>
                        <td class="fw-bold text-danger">- Rp {{ number_format($item->purchase_amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->book_value, 0, ',', '.') }}</td>
                        <td>
                            @if($item->status === 'disetujui')<span class="badge badge-light-success">Disetujui</span>
                            @elseif($item->status === 'ditolak')<span class="badge badge-light-danger">Ditolak</span>
                            @else<span class="badge badge-light-warning">Awaiting</span>@endif
                        </td>
                        <td class="text-end">
                            <div class="gh-action-group">
                                @can('asset-purchases.approve')
                                    @if($item->status === 'menunggu_approval')
                                        <form method="POST" action="{{ route('asset-purchases.approve', $item) }}" class="d-inline">
                                            @csrf
                                            <button class="gh-action-btn gh-action-approve" title="Approve"><i class="ki-duotone ki-check fs-2"></i></button>
                                        </form>
                                        <button class="gh-action-btn gh-action-reject" onclick="openRejectModal('{{ $item->id }}', @js($item->asset_number))" title="Reject">
                                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                    @endif
                                @endcan
                                <a class="gh-action-btn gh-action-view" href="{{ route('asset-purchases.show', $item) }}" title="Detail"><i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>
                                @can('asset-purchases.edit')
                                    @if($item->status !== 'disetujui')
                                        <a class="gh-action-btn gh-action-edit" href="{{ route('asset-purchases.edit', $item) }}" title="Edit"><i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                                    @endif
                                @endcan
                                @can('asset-purchases.delete')
                                    @if($item->status !== 'disetujui')
                                        <form method="POST" action="{{ route('asset-purchases.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus pembelian aset ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="gh-action-btn gh-action-delete" title="Hapus"><i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3 class="fw-bold">Reject Pembelian Aset</h3><button class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button></div><form method="POST" id="rejectForm">@csrf<div class="modal-body"><div class="text-muted mb-3" id="rejectNumber">-</div><label class="required form-label">Alasan Reject</label><textarea name="rejection_reason" class="form-control" rows="4" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Reject</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
var table=$('#kt_table').DataTable({dom:"<'d-none'B><'row'<'col-sm-12'tr>><'row finance-table-footer'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",order:[],pageLength:10,columnDefs:[{orderable:false,targets:[0,8]}]});
$('#searchInput').on('keyup',function(){table.search(this.value).draw();});
$('#lengthSelect').on('change',function(){table.page.len($(this).val()).draw();});
function openRejectModal(id, number){document.getElementById('rejectForm').action='/keuangan/pembelian-aset/'+id+'/reject';document.getElementById('rejectNumber').textContent=number;new bootstrap.Modal(document.getElementById('rejectModal')).show();}
</script>
@endpush
