@extends('layouts.app')

@section('title', 'Role & Permission')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Pengaturan</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Role & Permission</li>
@endsection

@section('toolbar_actions')
    @can('roles.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Role
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
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari role..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_roles_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Nama Role</th>
                    <th>Jumlah Permission</th>
                    <th>Users</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $i => $role)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><span class="fw-bold">{{ $role->name }}</span></td>
                    <td><span class="badge badge-light-info">{{ $role->permissions->count() }} permission</span></td>
                    <td><span class="badge badge-light-primary">{{ $role->users_count }} user</span></td>
                    <td class="text-end">
                        @can('roles.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal({{ $role->id }})" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @if($role->name !== 'Superadmin')
                        @can('roles.delete')
                        <button class="btn btn-icon btn-sm btn-danger" onclick="deleteRole({{ $role->id }}, '{{ $role->name }}')" title="Hapus">
                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                        @endcan
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Role --}}
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modalTitle">Tambah Role</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="dataForm">
                <div class="modal-body scroll-y mx-5 my-7" style="max-height:60vh">
                    <div class="fv-row mb-7">
                        <label class="required form-label fw-semibold">Nama Role</label>
                        <input type="text" name="name" id="f_roleName" class="form-control form-control" required />
                    </div>

                    <div class="fw-bold fs-5 mb-4">Permissions</div>

                    @foreach($structured as $groupName => $pages)
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-3">
                            <span class="fw-bold fs-6 text-gray-800">{{ $groupName }}</span>
                            <div class="separator separator-dashed flex-grow-1 ms-3"></div>
                        </div>
                        @foreach($pages as $pg)
                        <div class="border border-dashed border-gray-300 rounded-3 p-4 mb-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <span class="fw-semibold text-gray-700 fs-6">{{ $pg['label'] }}</span>
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    @foreach($pg['actions'] as $act)
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input perm-check perm-{{ $pg['page'] }}" type="checkbox" name="permissions[]" value="{{ $act['name'] }}" />
                                        <span class="form-check-label text-gray-600 text-capitalize">{{ $act['action'] }}</span>
                                    </label>
                                    @endforeach
                                    <span class="border-start border-gray-300 h-20px"></span>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" onchange="togglePage(this, '{{ $pg['page'] }}')" />
                                        <span class="form-check-label text-gray-500 fst-italic">Semua</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
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
var rolesTable;

// Init DataTable
$(document).ready(function() {
    rolesTable = $('#kt_roles_table').DataTable({
        fixedHeader: {
            header: true
        },
        dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Data Role - Garasi Hobby',
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

    // Connect external search input
    $('#searchInput').on('keyup', function() {
        rolesTable.search(this.value).draw();
    });

    // Connect external length select
    $('#lengthSelect').on('change', function() {
        rolesTable.page.len($(this).val()).draw();
    });

    // Export Excel via custom button
    $('#exportExcel').on('click', function(e) {
        e.preventDefault();
        rolesTable.button(0).trigger();
    });
});

function togglePage(el, page) {
    document.querySelectorAll('.perm-' + page).forEach(cb => cb.checked = el.checked);
}

function uncheckAll() {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
}

function checkPermissions(perms) {
    uncheckAll();
    perms.forEach(p => {
        var cb = document.querySelector('input[value="' + p + '"]');
        if (cb) cb.checked = true;
    });
}

function openCreateModal() {
    formMode = 'create';
    editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Role';
    document.getElementById('f_roleName').value = '';
    uncheckAll();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;

    fetch('/pengaturan/roles/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Role';
        document.getElementById('f_roleName').value = data.name;
        checkPermissions(data.permissions);
        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteRole(id, name) {
    Swal.fire({
        title: 'Hapus Role?',
        text: 'Yakin ingin menghapus role "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/pengaturan/roles/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success === false) {
                    Swal.fire('Gagal', data.message, 'error');
                } else {
                    window.location.reload();
                }
            });
        }
    });
}

document.getElementById('dataForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var url = formMode === 'create' ? '{{ route("roles.store") }}' : '/pengaturan/roles/' + editId;
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
            Swal.fire('Gagal', Object.values(data.errors).flat().join('<br>'), 'error');
        }
    })
    .catch(() => window.location.reload());
});
</script>
@endpush
