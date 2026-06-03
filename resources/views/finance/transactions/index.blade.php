@extends('layouts.app')

@section('title', 'Input Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Input Keuangan</li>
@endsection

@section('toolbar_actions')
@can('finance-transactions.create')<button class="btn btn-sm btn-primary" onclick="openCreateModal()"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Transaksi</button>@endcan
@endsection

@section('content')
@php($totalIncome=$data->filter(fn($x)=>$x->item?->category?->type==='income')->sum('amount'))
@php($totalExpense=$data->filter(fn($x)=>$x->item?->category?->type==='expense')->sum('amount'))
<div class="row g-5 mb-6"><div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Total Pemasukan</div><div class="text-success fw-bold fs-2">Rp {{ number_format($totalIncome,0,',','.') }}</div></div></div></div><div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Total Pengeluaran</div><div class="text-danger fw-bold fs-2">Rp {{ number_format($totalExpense,0,',','.') }}</div></div></div></div><div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Selisih</div><div class="{{ $totalIncome-$totalExpense>=0?'text-primary':'text-danger' }} fw-bold fs-2">Rp {{ number_format($totalIncome-$totalExpense,0,',','.') }}</div></div></div></div></div>
<div class="card card-flush"><div class="card-header border-0 pt-6"><div class="card-title"><span class="text-gray-600">Catat pemasukan dan pengeluaran Garasi Hobby.</span></div><div class="card-toolbar position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari transaksi..."></div></div>
<div class="card-body pt-0"><table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded"><thead><tr class="fw-semibold fs-6 text-gray-800"><th>No</th><th>Tanggal</th><th>No. Transaksi</th><th>Item</th><th>Keterangan</th><th>Account</th><th>Jenis</th><th>Nominal</th><th class="text-end">Aksi</th></tr></thead><tbody>
@foreach($data as $i=>$item)<tr><td>{{ $i+1 }}</td><td>{{ $item->transaction_date?->format('d/m/Y') }}</td><td>{{ $item->transaction_number }}</td><td>{{ $item->item?->name }}</td><td>{{ $item->description }}</td><td>{{ $item->bankAccount?->bank_name }}</td>
<td><span class="badge {{ $item->item?->category?->type==='income'?'badge-light-success':'badge-light-danger' }}">{{ $item->item?->category?->type==='income'?'Pemasukan':'Pengeluaran' }}</span></td><td class="{{ $item->item?->category?->type==='income'?'text-success':'text-danger' }} fw-bold">Rp {{ number_format($item->amount,0,',','.') }}</td><td class="text-end">
@can('finance-transactions.edit')<button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')"><i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i></button>@endcan
@can('finance-transactions.delete')<button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $item->id }}',@js($item->transaction_number))"><i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>@endcan
</td></tr>@endforeach
</tbody></table></div></div>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-750px"><div class="modal-content"><div class="modal-header"><h2 id="modalTitle">Tambah Transaksi</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
<form id="dataForm"><div class="modal-body mx-5 my-7"><div id="formErrors" class="alert alert-danger d-none"><ul class="mb-0"></ul></div><div class="row g-5">
<div class="col-md-6"><label class="required form-label">Tanggal</label><input type="date" name="transaction_date" id="f_date" class="form-control" required></div>
<div class="col-md-6"><label class="required form-label">Account Bank / Cash</label><select name="bank_account_id" id="f_bank" class="form-select" required><option value="">-- Pilih Account --</option>@foreach($bankAccounts as $a)<option value="{{ $a->id }}">{{ $a->bank_name }} - Rp {{ number_format($a->balance,0,',','.') }}</option>@endforeach</select></div>
<div class="col-12"><label class="required form-label">Item Keuangan</label><select name="finance_item_id" id="f_item" class="form-select" required><option value="">-- Pilih Item --</option>@foreach($items as $x)<option value="{{ $x->id }}">{{ $x->category?->type==='income'?'[Pemasukan]':'[Pengeluaran]' }} {{ $x->name }} - {{ $x->category?->name }}</option>@endforeach</select></div>
<div class="col-md-8"><label class="required form-label">Keterangan</label><input name="description" id="f_description" class="form-control" required></div><div class="col-md-4"><label class="required form-label">Nominal</label><input type="number" min="1" name="amount" id="f_amount" class="form-control" required></div>
<div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" id="f_notes" class="form-control" rows="3"></textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
var editId=null;var table=$('#kt_table').DataTable({order:[],columnDefs:[{orderable:false,targets:[0,8]}]});$('#searchInput').on('keyup',function(){table.search(this.value).draw();});
function modal(){return new bootstrap.Modal(document.getElementById('formModal'));}function errors(d){var b=$('#formErrors');b.addClass('d-none').find('ul').empty();if(d.errors){Object.values(d.errors).flat().forEach(x=>b.find('ul').append($('<li>').text(x)));b.removeClass('d-none');}}
function openCreateModal(){editId=null;$('#modalTitle').text('Tambah Transaksi');$('#dataForm')[0].reset();$('#f_date').val('{{ now()->format('Y-m-d') }}');errors({});modal().show();}
function openEditModal(id){fetch('/keuangan/transaksi/'+id+'/edit').then(r=>r.json()).then(d=>{editId=id;$('#modalTitle').text('Edit Transaksi');$('#f_date').val(d.transaction_date.substring(0,10));$('#f_bank').val(d.bank_account_id);$('#f_item').val(d.finance_item_id);$('#f_description').val(d.description);$('#f_amount').val(d.amount);$('#f_notes').val(d.notes);errors({});modal().show();});}
$('#dataForm').on('submit',function(e){e.preventDefault();var fd=new FormData(this);if(editId)fd.append('_method','PUT');fetch(editId?'/keuangan/transaksi/'+editId:'{{ route('finance-transactions.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'},body:fd}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d));});
function deleteItem(id,number){Swal.fire({title:'Hapus transaksi?',text:number,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'}).then(x=>{if(x.isConfirmed)fetch('/keuangan/transaksi/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d));});}
</script>
@endpush
