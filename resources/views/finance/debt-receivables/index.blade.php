@extends('layouts.app')

@section('title', 'Hutang Piutang')
@section('toolbar_actions')
@can('debt-receivables.create')<a href="{{ route('debt-receivables.create') }}" class="btn btn-sm btn-primary"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span></i> Tambah</a>@endcan
@endsection

@section('content')
@php
    $active = $data->where('status', '!=', 'ditolak');
    $debt = $active->where('type', 'debt');
    $receivable = $active->where('type', 'receivable');
@endphp
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-5 mb-7">
    <div class="col">
        <div class="order-stat-card order-stat-danger h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-arrow-down fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Total Hutang</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($debt->sum('total_amount'),0,',','.') }}</div>
                <div class="order-stat-hint">{{ $debt->count() }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card order-stat-warning h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-time fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Sisa Hutang</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($debt->sum('remaining_amount'),0,',','.') }}</div>
                <div class="order-stat-hint">Belum dibayar</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card order-stat-success h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-arrow-up fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Total Piutang</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($receivable->sum('total_amount'),0,',','.') }}</div>
                <div class="order-stat-hint">{{ $receivable->count() }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="order-stat-card order-stat-primary h-100">
            <span class="order-stat-icon"><i class="ki-duotone ki-wallet fs-2"><span class="path1"></span><span class="path2"></span></i></span>
            <div class="min-w-0">
                <div class="order-stat-label">Sisa Piutang</div>
                <div class="order-stat-value order-stat-currency">Rp {{ number_format($receivable->sum('remaining_amount'),0,',','.') }}</div>
                <div class="order-stat-hint">Belum diterima</div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush finance-table-card">
    <div class="card-header pt-6 border-0"><div class="card-title"><span class="text-gray-700 fs-7 me-2">Tampilkan</span><select id="lengthSelect" class="form-select form-select-sm w-75px"><option>10</option><option>25</option><option>50</option></select></div><div class="card-toolbar"><div class="position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control finance-search w-250px ps-12" placeholder="Cari hutang/piutang..."></div></div></div>
    <div class="card-body pt-0"><div class="finance-table-wrap"><table id="kt_table" class="table align-middle"><thead><tr><th>#</th><th>No. Transaksi</th><th>Jenis</th><th>Aktivitas</th><th>Pihak</th><th>Nominal</th><th>Sisa</th><th>Jatuh Tempo</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @foreach($data as $i => $item)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td>{{ $item->transaction_number }}</td>
            <td><span class="badge {{ $item->type === 'debt' ? 'badge-light-danger' : 'badge-light-success' }}">{{ $item->type === 'debt' ? 'Hutang' : 'Piutang' }}</span></td>
            <td>{{ $item->activity }}</td>
            <td>{{ $item->party_name }}</td>
            <td>Rp {{ number_format($item->total_amount,0,',','.') }}</td>
            <td class="{{ $item->remaining_amount <= 0 ? 'text-success' : 'text-danger' }} fw-bold">Rp {{ number_format($item->remaining_amount,0,',','.') }}</td>
            <td>{{ $item->due_date?->format('d/m/Y') ?? '-' }}</td>
            <td>
                @if($item->status==='disetujui')
                    <span class="badge badge-light-success">{{ $item->payment_status === 'lunas' ? 'Lunas' : 'Disetujui' }}</span>
                @elseif($item->status==='ditolak')
                    <span class="badge badge-light-danger">Ditolak</span>
                @else
                    <span class="badge badge-light-warning">Awaiting</span>
                @endif
            </td>
            <td class="text-end">
                <div class="gh-action-group">
                    @can('debt-receivables.approve')
                        @if($item->status==='menunggu_approval')
                            <form method="POST" action="{{ route('debt-receivables.approve',$item) }}">
                                @csrf
                                <button class="gh-action-btn gh-action-approve"><i class="ki-duotone ki-check fs-2"></i></button>
                            </form>
                            <button class="gh-action-btn gh-action-reject" onclick="openRejectModal('{{ $item->id }}', @js($item->transaction_number))">
                                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        @endif
                    @endcan
                    <a href="{{ route('debt-receivables.show',$item) }}" class="gh-action-btn gh-action-view"><i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>
                    @can('debt-receivables.edit')
                        @if($item->status!=='disetujui')
                            <a href="{{ route('debt-receivables.edit',$item) }}" class="gh-action-btn gh-action-edit"><i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                        @endif
                    @endcan
                    @can('debt-receivables.delete')
                        @if(!$item->payments()->exists())
                            <form method="POST" action="{{ route('debt-receivables.destroy',$item) }}" onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="gh-action-btn gh-action-delete"><i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                            </form>
                        @endif
                    @endcan
                </div>
            </td>
        </tr>
        @endforeach
    </tbody></table></div></div>
</div>
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3>Reject Hutang/Piutang</h3><button class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button></div><form method="POST" id="rejectForm">@csrf<div class="modal-body"><div class="text-muted mb-3" id="rejectNumber"></div><textarea name="rejection_reason" class="form-control" rows="4" required></textarea></div><div class="modal-footer"><button class="btn btn-danger">Reject</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
var table=$('#kt_table').DataTable({dom:"<'d-none'B><'row'<'col-sm-12'tr>><'row finance-table-footer'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",ordering:false,pageLength:10,columnDefs:[{orderable:false,targets:[0,9]}]});
$('#searchInput').on('keyup',function(){table.search(this.value).draw();});$('#lengthSelect').on('change',function(){table.page.len($(this).val()).draw();});
function openRejectModal(id,number){document.getElementById('rejectForm').action='/keuangan/hutang-piutang/'+id+'/reject';document.getElementById('rejectNumber').textContent=number;new bootstrap.Modal(document.getElementById('rejectModal')).show();}
</script>
@endpush
