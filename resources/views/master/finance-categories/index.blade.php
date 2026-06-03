@extends('layouts.app')

@section('title', 'Kategori Item Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Master Data</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Kategori Item Keuangan</li>
@endsection

@section('toolbar_actions')
@can('finance-master.create')
<button class="btn btn-sm btn-primary" onclick="openCreateModal()"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Kategori</button>
@endcan
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><span class="text-gray-600">Kelola kelompok pemasukan dan pengeluaran.</span></div>
        <div class="card-toolbar position-relative">
            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
            <input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari kategori..." />
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead><tr class="fw-semibold fs-6 text-gray-800"><th>No</th><th>Kode</th><th>Nama</th><th>Jenis</th><th>Deskripsi</th><th>Jumlah Item</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach($data as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td><td>{{ $item->code }}</td><td>{{ $item->name }}</td>
                <td><span class="badge {{ $item->type === 'income' ? 'badge-light-success' : 'badge-light-danger' }}">{{ $item->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span></td>
                <td>{{ $item->description ?? '-' }}</td><td>{{ $item->items_count }}</td>
                <td class="text-end">
                    @can('finance-master.edit')<button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')"><i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i></button>@endcan
                    @can('finance-master.delete')<button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $item->id }}', @js($item->name))"><i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>@endcan
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content">
    <div class="modal-header"><h2 id="modalTitle">Tambah Kategori</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
    <form id="dataForm"><div class="modal-body mx-5 my-7">
        <div id="formErrors" class="alert alert-danger d-none"><ul class="mb-0"></ul></div>
        <div class="mb-5"><label class="required form-label">Kode</label><input name="code" id="f_code" class="form-control" required></div>
        <div class="mb-5"><label class="required form-label">Nama Kategori</label><input name="name" id="f_name" class="form-control" required></div>
        <div class="mb-5"><label class="required form-label">Jenis</label><select name="type" id="f_type" class="form-select" required><option value="income">Pemasukan</option><option value="expense">Pengeluaran</option></select></div>
        <div><label class="form-label">Deskripsi</label><textarea name="description" id="f_description" class="form-control" rows="3"></textarea></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form>
</div></div></div>
@endsection

@push('scripts')
<script>
var editId = null;
var table = $('#kt_table').DataTable({order: [], columnDefs: [{orderable:false, targets:[0,6]}]});
$('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
function errors(data){ var box=$('#formErrors'); box.addClass('d-none').find('ul').empty(); if(data.errors){ Object.values(data.errors).flat().forEach(x=>box.find('ul').append($('<li>').text(x))); box.removeClass('d-none'); } }
function openCreateModal(){ editId=null; $('#modalTitle').text('Tambah Kategori'); $('#dataForm')[0].reset(); errors({}); new bootstrap.Modal(document.getElementById('formModal')).show(); }
function openEditModal(id){ fetch('/master/finance-categories/'+id+'/edit').then(r=>r.json()).then(d=>{ editId=id; $('#modalTitle').text('Edit Kategori'); $('#f_code').val(d.code); $('#f_name').val(d.name); $('#f_type').val(d.type); $('#f_description').val(d.description); errors({}); new bootstrap.Modal(document.getElementById('formModal')).show(); }); }
$('#dataForm').on('submit', function(e){ e.preventDefault(); var fd=new FormData(this); if(editId) fd.append('_method','PUT'); fetch(editId?'/master/finance-categories/'+editId:'{{ route('finance-categories.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'},body:fd}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d)); });
function deleteItem(id,name){ Swal.fire({title:'Hapus kategori?',text:name,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'}).then(x=>{if(x.isConfirmed) fetch('/master/finance-categories/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(d=>d.success?location.reload():Swal.fire('Gagal',d.message,'error'));});}
</script>
@endpush
