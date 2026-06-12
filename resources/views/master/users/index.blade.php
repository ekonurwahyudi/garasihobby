@extends('layouts.app')

@section('title', 'Data User')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Master Data</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">User</li>
@endsection

@section('toolbar_actions')
    @can('users.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah User
    </button>
    @endcan
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            {{-- Length menu di kiri atas --}}
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
            {{-- Search di kanan atas --}}
            <div class="d-flex align-items-center position-relative">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari user..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_users_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Role</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->jabatan ?? '-' }}</td>
                    <td>
                        @foreach($item->roles as $role)
                            <span class="badge badge-light-primary fs-7">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $item->phone }}</td>
                    <td>{{ $item->email }}</td>
                    <td>
                        @if($item->status === 'aktif')
                            <span class="badge badge-light-success">Aktif</span>
                        @else
                            <span class="badge badge-light-danger">Block</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @canany(['users.view', 'users.edit', 'users.delete'])
                        <button class="btn btn-icon btn-sm btn-info" onclick="viewUser('{{ $item->id }}')" title="Detail">
                            <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </button>
                        @can('users.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('users.delete')
                        <button class="btn btn-icon btn-sm btn-danger" onclick="deleteUser('{{ $item->id }}', '{{ $item->name }}')" title="Hapus">
                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                        @endcan
                        @endcanany
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal View Detail --}}
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Detail User</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body mx-5 my-7">
                <table class="table table-row-bordered gy-4 gs-5">
                    <tbody>
                        <tr><td class="fw-semibold text-gray-600 w-150px">Nama</td><td class="fw-bold text-gray-800" id="v_name"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Jabatan</td><td class="fw-bold text-gray-800" id="v_jabatan"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Role</td><td id="v_role"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">No HP</td><td class="fw-bold text-gray-800" id="v_phone"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Email</td><td class="fw-bold text-gray-800" id="v_email"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Status</td><td id="v_status"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal User --}}
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modalTitle">Tambah User</h2>
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
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Nama</label>
                            <input type="text" name="name" id="f_name" class="form-control form-control" />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Jabatan</label>
                            <input type="text" name="jabatan" id="f_jabatan" class="form-control form-control" />
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">No HP</label>
                            <input type="text" name="phone" id="f_phone" class="form-control form-control" />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="f_email" class="form-control form-control" />
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Role</label>
                            <select name="role" id="f_role" class="form-select form-select">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Status</label>
                            <select name="status" id="f_status" class="form-select form-select">
                                <option value="aktif">Aktif</option>
                                <option value="block">Block</option>
                            </select>
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Password <span class="text-danger" id="pwdRequired">*</span></label>
                        <input type="password" name="password" id="f_password" class="form-control form-control" />
                        <div class="form-text d-none" id="pwdHint">Kosongkan jika tidak ingin mengubah password</div>
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
var usersTable;

// Init DataTable
$(document).ready(function() {
    usersTable = $('#kt_users_table').DataTable({
        fixedHeader: {
            header: true
        },
        dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Data User - Garasi Hobby',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
            }
        ],
        order: [],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 7] }
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

    // Connect external search input
    $('#searchInput').on('keyup', function() {
        usersTable.search(this.value).draw();
    });

    // Connect external length select
    $('#lengthSelect').on('change', function() {
        usersTable.page.len($(this).val()).draw();
    });

    // Export Excel via custom button
    $('#exportExcel').on('click', function(e) {
        e.preventDefault();
        usersTable.button(0).trigger();
    });
});

function viewUser(id) {
    fetch('/master/users/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('v_name').textContent = data.name || '-';
        document.getElementById('v_jabatan').textContent = data.jabatan || '-';
        document.getElementById('v_role').innerHTML = data.role ? '<span class="badge badge-light-primary">' + data.role + '</span>' : '-';
        document.getElementById('v_phone').textContent = data.phone || '-';
        document.getElementById('v_email').textContent = data.email || '-';
        document.getElementById('v_status').innerHTML = data.status === 'aktif'
            ? '<span class="badge badge-light-success">Aktif</span>'
            : '<span class="badge badge-light-danger">Block</span>';
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    });
}

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
    document.getElementById('modalTitle').textContent = 'Tambah User';
    document.getElementById('dataForm').reset();
    document.getElementById('f_status').value = 'aktif';
    document.getElementById('pwdRequired').style.display = '';
    document.getElementById('pwdHint').classList.add('d-none');
    document.getElementById('f_password').required = true;
    hideErrors();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;
    hideErrors();

    fetch('/master/users/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('f_name').value = data.name || '';
        document.getElementById('f_jabatan').value = data.jabatan || '';
        document.getElementById('f_phone').value = data.phone || '';
        document.getElementById('f_email').value = data.email || '';
        document.getElementById('f_role').value = data.role || '';
        document.getElementById('f_status').value = data.status || 'aktif';
        document.getElementById('f_password').value = '';
        document.getElementById('f_password').required = false;
        document.getElementById('pwdRequired').style.display = 'none';
        document.getElementById('pwdHint').classList.remove('d-none');
        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteUser(id, name) {
    Swal.fire({
        title: 'Hapus User?',
        text: 'Yakin ingin menghapus "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/master/users/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(() => window.location.reload());
        }
    });
}

document.getElementById('dataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    hideErrors();

    var url = formMode === 'create' ? '{{ route("users.store") }}' : '/master/users/' + editId;
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
