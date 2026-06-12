@extends('layouts.app')

@section('title', 'Kategori Checklist')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Master Data</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Kategori Checklist</li>
@endsection

@section('toolbar_actions')
    @can('checklist.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Kategori
    </button>
    @endcan
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center">
                <span class="text-gray-700 fs-7 me-2">Tampilkan</span>
                <select id="lengthSelect" class="form-select form-select form-select-sm w-75px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari kategori..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Kode</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Item</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->items_count ?? 0 }}</td>
                    <td class="text-end">
                        @can('checklist.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('checklist.delete')
                        <button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $item->id }}', '{{ $item->name }}')" title="Hapus">
                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Form --}}
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modalTitle">Tambah Kategori</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="dataForm">
                <div class="modal-body scroll-y mx-5 my-7" style="max-height:60vh">
                    <div id="formErrors" class="alert alert-danger d-none mb-5">
                        <div class="d-flex flex-column">
                            <span class="fw-bold mb-1">Terjadi kesalahan:</span>
                            <ul id="formErrorList" class="mb-0"></ul>
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="required form-label fw-semibold">Kode</label>
                        <input type="text" name="code" id="f_code" class="form-control form-control" required />
                    </div>
                    <div class="fv-row mb-5">
                        <label class="required form-label fw-semibold">Nama</label>
                        <input type="text" name="name" id="f_name" class="form-control form-control" required />
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var formMode = 'create';
var editId = null;
var table;

$(document).ready(function() {
    table = $('#kt_table').DataTable({
        fixedHeader: { header: true },
        dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Kategori Checklist - Garasi Hobby',
                exportOptions: { columns: [0, 1, 2, 3] }
            }
        ],
        order: [],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 4] }
        ],
        language: {
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(filter dari _MAX_ total data)",
            paginate: {
                first: '<i class="ki-duotone ki-double-left fs-4"></i>',
                last: '<i class="ki-duotone ki-double-right fs-4"></i>',
                next: '<i class="ki-duotone ki-right fs-4"></i>',
                previous: '<i class="ki-duotone ki-left fs-4"></i>',
            }
        }
    });

    $('#searchInput').on('keyup', function() { table.search(this.value).draw(); });
    $('#lengthSelect').on('change', function() { table.page.len($(this).val()).draw(); });
    $('#exportExcel').on('click', function(e) { e.preventDefault(); table.button(0).trigger(); });
});

function hideErrors() {
    document.getElementById('formErrors').classList.add('d-none');
    document.getElementById('formErrorList').innerHTML = '';
}

function showErrors(errors) {
    var list = document.getElementById('formErrorList');
    list.innerHTML = '';
    Object.values(errors).forEach(function(msgs) {
        msgs.forEach(function(msg) {
            var item = document.createElement('li');
            item.textContent = msg;
            list.appendChild(item);
        });
    });
    document.getElementById('formErrors').classList.remove('d-none');
}

function openCreateModal() {
    formMode = 'create';
    editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('dataForm').reset();
    hideErrors();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;
    hideErrors();

    fetch('/master/checklist-categories/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Kategori';
        document.getElementById('f_code').value = data.code || '';
        document.getElementById('f_name').value = data.name || '';
        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Kategori?',
        text: 'Yakin ingin menghapus kategori "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/master/checklist-categories/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                    return;
                }

                Swal.fire('Gagal', data.message || 'Kategori tidak bisa dihapus.', 'error');
            })
            .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat menghapus kategori.', 'error'));
        }
    });
}

document.getElementById('dataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors();

    var url = formMode === 'create' ? '{{ route("checklist-categories.store") }}' : '/master/checklist-categories/' + editId;
    var method = formMode === 'create' ? 'POST' : 'PUT';
    var formData = new FormData(this);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': method,
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('formModal')).hide();
            window.location.reload();
        } else if (data.errors) {
            showErrors(data.errors);
        }
    })
    .catch(() => window.location.reload());
});
</script>
@endpush
