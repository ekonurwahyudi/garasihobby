@extends('layouts.app')

@section('title', 'Katalog Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Master Data</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Katalog Material</li>
@endsection

@section('toolbar_actions')
    @can('materials.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Material
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
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari material..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th class="w-80px">Foto</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga Satuan</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="symbol symbol-45px overflow-hidden bg-light">
                            @if($item->photo_url)
                                <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="object-fit-cover w-100 h-100">
                            @else
                                <div class="symbol-label bg-light-primary text-primary">
                                    <i class="ki-duotone ki-picture fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category->name ?? '-' }}</td>
                    <td>Rp {{ number_format($item->cost_price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-end">
                        @can('materials.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('materials.delete')
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
                <h2 class="fw-bold" id="modalTitle">Tambah Material</h2>
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
                        <label class="required form-label fw-semibold">Kategori</label>
                        <select name="material_category_id" id="f_category" class="form-select form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="required form-label fw-semibold">Nama</label>
                        <input type="text" name="name" id="f_name" class="form-control form-control" required />
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Foto Material</label>
                        <input type="file" name="photo" id="f_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text mb-3">Format JPG, PNG, atau WEBP. Maksimal 4MB.</div>
                        <div class="material-photo-preview" id="photoPreviewWrapper">
                            <div class="material-photo-placeholder" id="photoPlaceholder">
                                <i class="ki-duotone ki-picture fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                <div>
                                    <div class="fw-bold text-gray-800">Preview foto material</div>
                                    <div class="text-muted fs-8">Gambar akan tampil di katalog dan detail persediaan.</div>
                                </div>
                            </div>
                            <img src="" alt="Preview foto material" id="photoPreview" class="d-none">
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Harga Satuan</label>
                        <div class="input-group mb-5">
                            <span class="input-group-text" id="cost-price-addon">Rp</span>
                            <input type="text" id="f_cost_price_display" class="form-control" placeholder="0" aria-label="Harga Satuan" aria-describedby="cost-price-addon" inputmode="numeric" autocomplete="off" />
                        </div>
                        <input type="hidden" name="cost_price" id="f_cost_price" />
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

@push('styles')
<style>
.material-photo-preview{width:100%;min-height:150px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden}
.material-photo-preview img{width:100%;height:180px;object-fit:cover;display:block}
.material-photo-placeholder{display:flex;align-items:center;gap:14px;padding:20px;color:#475569;text-align:left}
</style>
@endpush

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
                title: 'Katalog Material - Garasi Hobby',
                exportOptions: { columns: [0, 2, 3, 4] }
            }
        ],
        order: [],
        pageLength: 10,
        columnDefs: [
            { orderable: false, targets: [0, 1, 5] }
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
        msgs.forEach(function(msg) { list.innerHTML += '<li>' + msg + '</li>'; });
    });
    document.getElementById('formErrors').classList.remove('d-none');
}

function normalizePriceInput(value) {
    var raw = (value || '').toString().trim();

    if (/^\d+\.\d{1,2}$/.test(raw)) {
        raw = raw.split('.')[0];
    }

    return raw.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
}

function formatRupiah(value) {
    var digits = normalizePriceInput(value);
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

function setCurrencyValue(displayId, hiddenId, value) {
    var digits = normalizePriceInput(value);
    document.getElementById(hiddenId).value = digits;
    document.getElementById(displayId).value = formatRupiah(digits);
}

function setPhotoPreview(url) {
    var preview = document.getElementById('photoPreview');
    var placeholder = document.getElementById('photoPlaceholder');

    if (url) {
        preview.src = url;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    } else {
        preview.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }
}

function openCreateModal() {
    formMode = 'create';
    editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Material';
    document.getElementById('dataForm').reset();
    setCurrencyValue('f_cost_price_display', 'f_cost_price', '');
    setPhotoPreview('');
    hideErrors();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;
    hideErrors();

    fetch('/master/materials/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Material';
        document.getElementById('f_category').value = data.material_category_id || '';
        document.getElementById('f_name').value = data.name || '';
        setCurrencyValue('f_cost_price_display', 'f_cost_price', data.cost_price || '');
        document.getElementById('f_photo').value = '';
        setPhotoPreview(data.photo_url || '');
        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Material?',
        text: 'Yakin ingin menghapus material "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/master/materials/' + id, {
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
    setCurrencyValue('f_cost_price_display', 'f_cost_price', document.getElementById('f_cost_price_display').value);

    var url = formMode === 'create' ? '{{ route("materials.store") }}' : '/master/materials/' + editId;
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

document.getElementById('f_cost_price_display').addEventListener('input', function() {
    setCurrencyValue('f_cost_price_display', 'f_cost_price', this.value);
});

document.getElementById('f_photo').addEventListener('change', function() {
    var file = this.files && this.files[0] ? this.files[0] : null;
    setPhotoPreview(file ? URL.createObjectURL(file) : '');
});
</script>
@endpush
