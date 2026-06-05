@extends('layouts.app')

@section('title', 'Kategori Hutang Piutang')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Master Data</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Kategori Hutang Piutang</li>
@endsection

@section('toolbar_actions')
@can('finance-master.create')
<button class="btn btn-sm btn-primary" onclick="openCreateModal()">
    <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    Tambah Kategori
</button>
@endcan
@endsection

@section('content')
<div class="card card-flush gh-table-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <span class="text-gray-600">Kelola kategori untuk hutang dan piutang.</span>
        </div>
        <div class="card-toolbar position-relative">
            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
            <input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari kategori..." />
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_table" class="table gh-table align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width:64px">#</th>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Jenis</th>
                        <th>Deskripsi</th>
                        <th>Dipakai</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $i => $item)
                    @php
                        $typeLabel = ['debt' => 'Hutang', 'receivable' => 'Piutang', 'both' => 'Hutang & Piutang'][$item->type] ?? 'Semua';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><span class="badge badge-light-primary">{{ $item->code }}</span></td>
                        <td class="fw-semibold">{{ $item->name }}</td>
                        <td><span class="badge badge-light-info">{{ $typeLabel }}</span></td>
                        <td class="text-gray-600">{{ $item->description ?? '-' }}</td>
                        <td>{{ $item->debt_receivables_count }} transaksi</td>
                        <td class="text-end">
                            <div class="gh-action-group justify-content-end">
                                @can('finance-master.edit')
                                <button class="gh-action-btn gh-action-edit" onclick="openEditModal('{{ $item->id }}')" title="Edit">
                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                @endcan
                                @can('finance-master.delete')
                                <button class="gh-action-btn gh-action-delete" onclick="deleteItem('{{ $item->id }}', @js($item->name))" title="Hapus">
                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
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

<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Kategori Hutang Piutang</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="dataForm">
                <div class="modal-body mx-5 my-7">
                    <div id="formErrors" class="alert alert-danger d-none"><ul class="mb-0"></ul></div>
                    <div class="mb-5"><label class="required form-label">Kode</label><input name="code" id="f_code" class="form-control" required></div>
                    <div class="mb-5"><label class="required form-label">Nama Kategori</label><input name="name" id="f_name" class="form-control" required></div>
                    <div class="mb-5">
                        <label class="required form-label">Jenis</label>
                        <select name="type" id="f_type" class="form-select" required>
                            <option value="both">Hutang & Piutang</option>
                            <option value="debt">Hutang</option>
                            <option value="receivable">Piutang</option>
                        </select>
                    </div>
                    <div><label class="form-label">Deskripsi</label><textarea name="description" id="f_description" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary"><i class="ki-duotone ki-check fs-3"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var editId = null;
var table = $('#kt_table').DataTable({order: [], columnDefs: [{orderable:false, targets:[0,6]}]});
$('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
function errors(data){ var box=$('#formErrors'); box.addClass('d-none').find('ul').empty(); if(data.errors){ Object.values(data.errors).flat().forEach(x=>box.find('ul').append($('<li>').text(x))); box.removeClass('d-none'); } }
function openCreateModal(){ editId=null; $('#modalTitle').text('Tambah Kategori Hutang Piutang'); $('#dataForm')[0].reset(); $('#f_type').val('both'); errors({}); new bootstrap.Modal(document.getElementById('formModal')).show(); }
function openEditModal(id){ fetch('/master/debt-receivable-categories/'+id+'/edit').then(r=>r.json()).then(d=>{ editId=id; $('#modalTitle').text('Edit Kategori Hutang Piutang'); $('#f_code').val(d.code); $('#f_name').val(d.name); $('#f_type').val(d.type); $('#f_description').val(d.description); errors({}); new bootstrap.Modal(document.getElementById('formModal')).show(); }); }
$('#dataForm').on('submit', function(e){ e.preventDefault(); var fd=new FormData(this); if(editId) fd.append('_method','PUT'); fetch(editId?'/master/debt-receivable-categories/'+editId:'{{ route('debt-receivable-categories.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'},body:fd}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d)); });
function deleteItem(id,name){ Swal.fire({title:'Hapus kategori?',text:name,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'}).then(x=>{if(x.isConfirmed) fetch('/master/debt-receivable-categories/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(d=>d.success?location.reload():Swal.fire('Gagal',d.message,'error'));});}
</script>
@endpush
