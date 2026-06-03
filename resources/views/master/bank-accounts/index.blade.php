@extends('layouts.app')

@section('title', 'Account Bank')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Master Data</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Account Bank</li>
@endsection

@section('toolbar_actions')
@can('bank-accounts.edit')<button class="btn btn-sm btn-light-primary" onclick="openTransferModal()"><i class="ki-duotone ki-arrow-right-left fs-3"><span class="path1"></span><span class="path2"></span></i> Transfer Saldo</button>@endcan
@can('bank-accounts.create')<button class="btn btn-sm btn-primary" onclick="openCreateModal()"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Account</button>@endcan
@endsection

@section('content')
@if($errors->any())
<div class="alert alert-danger d-flex align-items-center mb-5">
    <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div>{{ $errors->first() }}</div>
</div>
@endif
<div class="row g-5 mb-6">
@foreach($data as $account)
<div class="col-md-6 col-xl-4"><a href="{{ route('bank-accounts.show', $account) }}" class="card card-flush h-100 hover-elevate-up">
<div class="card-body"><div class="d-flex justify-content-between mb-5"><div><div class="text-gray-500 fs-7">{{ $account->code }}</div><div class="text-gray-900 fw-bold fs-4">{{ $account->bank_name }}</div></div><span class="badge {{ $account->is_active ? 'badge-light-success' : 'badge-light' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
<div class="text-gray-500 fs-7">{{ $account->account_name ?? '-' }} @if($account->account_number) - {{ $account->account_number }} @endif</div>
<div class="text-primary fw-bold fs-2 mt-2">Rp {{ number_format($account->balance, 0, ',', '.') }}</div></div></a></div>
@endforeach
</div>

<div class="card card-flush"><div class="card-header border-0 pt-6"><div class="card-title"><span class="text-gray-600">Daftar rekening dan saldo saat ini.</span></div><div class="card-toolbar position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari account..."></div></div>
<div class="card-body pt-0"><table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded"><thead><tr class="fw-semibold fs-6 text-gray-800"><th>No</th><th>Kode</th><th>Bank / Cash</th><th>Pemilik</th><th>No. Rekening</th><th>Saldo</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
@foreach($data as $i => $item)<tr><td>{{ $i+1 }}</td><td>{{ $item->code }}</td><td>{{ $item->bank_name }}</td><td>{{ $item->account_name ?? '-' }}</td><td>{{ $item->account_number ?? '-' }}</td><td>Rp {{ number_format($item->balance,0,',','.') }}</td><td><span class="badge {{ $item->is_active?'badge-light-success':'badge-light' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td class="text-end">
<a class="btn btn-icon btn-sm btn-info" href="{{ route('bank-accounts.show',$item) }}"><i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>
@can('bank-accounts.edit')<button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')"><i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i></button>@endcan
@can('bank-accounts.delete')<button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $item->id }}',@js($item->bank_name))"><i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>@endcan
</td></tr>@endforeach
</tbody></table></div></div>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content"><div class="modal-header"><h2 id="modalTitle">Tambah Account</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
<form id="dataForm"><div class="modal-body mx-5 my-7"><div id="formErrors" class="alert alert-danger d-none"><ul class="mb-0"></ul></div>
<div class="row g-5"><div class="col-md-6"><label class="required form-label">Kode Account</label><input name="code" id="f_code" class="form-control" required></div><div class="col-md-6"><label class="required form-label">Nama Bank / Cash</label><select name="bank_name" id="f_bank_name" class="form-select" required><option value="">-- Pilih Bank --</option>@foreach($banks as $bank)<option value="{{ $bank }}">{{ $bank }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Nama Pemilik</label><input name="account_name" id="f_account_name" class="form-control"></div><div class="col-md-6"><label class="form-label">Nomor Rekening</label><input name="account_number" id="f_account_number" class="form-control"></div>
<div class="col-md-6"><label class="required form-label">Saldo</label><input type="number" min="0" name="balance" id="f_balance" class="form-control" required></div><div class="col-md-6"><label class="required form-label">Status</label><select name="is_active" id="f_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
<div class="col-12 d-none" id="balanceDescriptionWrap"><label class="form-label">Keterangan Perubahan Saldo</label><textarea name="balance_description" id="f_balance_description" class="form-control" rows="2"></textarea></div></div>
</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="transferModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content"><div class="modal-header"><h2>Transfer Saldo</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
<form method="POST" action="{{ route('bank-accounts.transfer') }}">@csrf<div class="modal-body mx-5 my-7"><div class="row g-5"><div class="col-md-6"><label class="required form-label">Dari Account</label><select name="from_bank_account_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($data->where('is_active',true) as $a)<option value="{{ $a->id }}">{{ $a->bank_name }} - Rp {{ number_format($a->balance,0,',','.') }}</option>@endforeach</select></div><div class="col-md-6"><label class="required form-label">Ke Account</label><select name="to_bank_account_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($data->where('is_active',true) as $a)<option value="{{ $a->id }}">{{ $a->bank_name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="required form-label">Tanggal</label><input type="date" name="transfer_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div><div class="col-md-6"><label class="required form-label">Nominal</label><input type="number" min="1" name="amount" class="form-control" required></div><div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Transfer</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
var editId=null;var table=$('#kt_table').DataTable({order:[],columnDefs:[{orderable:false,targets:[0,7]}]});$('#searchInput').on('keyup',function(){table.search(this.value).draw();});
function modal(id){return new bootstrap.Modal(document.getElementById(id));} function errors(d){var b=$('#formErrors');b.addClass('d-none').find('ul').empty();if(d.errors){Object.values(d.errors).flat().forEach(x=>b.find('ul').append($('<li>').text(x)));b.removeClass('d-none');}}
function openCreateModal(){editId=null;$('#modalTitle').text('Tambah Account');$('#dataForm')[0].reset();$('#balanceDescriptionWrap').addClass('d-none');errors({});modal('formModal').show();}
function openEditModal(id){fetch('/master/bank-accounts/'+id+'/edit').then(r=>r.json()).then(d=>{editId=id;$('#modalTitle').text('Edit Account');$('#f_code').val(d.code);if($('#f_bank_name option[value="'+d.bank_name+'"]').length===0){$('#f_bank_name').append($('<option>',{value:d.bank_name,text:d.bank_name}));}$('#f_bank_name').val(d.bank_name);$('#f_account_name').val(d.account_name);$('#f_account_number').val(d.account_number);$('#f_balance').val(d.balance);$('#f_active').val(d.is_active?1:0);$('#f_balance_description').val('');$('#balanceDescriptionWrap').removeClass('d-none');errors({});modal('formModal').show();});}
function openTransferModal(){modal('transferModal').show();}
$('#dataForm').on('submit',function(e){e.preventDefault();var fd=new FormData(this);if(editId)fd.append('_method','PUT');fetch(editId?'/master/bank-accounts/'+editId:'{{ route('bank-accounts.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'},body:fd}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d));});
function deleteItem(id,name){Swal.fire({title:'Hapus account?',text:name,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'}).then(x=>{if(x.isConfirmed)fetch('/master/bank-accounts/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(d=>d.success?location.reload():Swal.fire('Gagal',d.message,'error'));});}
</script>
@endpush
