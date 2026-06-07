@extends('layouts.app')

@section('title', 'Paket Promo')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Master Data</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Paket Promo</li>
@endsection

@section('toolbar_actions')
    @can('promo.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Paket
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
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari paket..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Nama Paket</th>
                    <th>Small (S)</th>
                    <th>Medium (M)</th>
                    <th>Large (L)</th>
                    <th>Berlaku</th>
                    <th>Status</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>Rp {{ number_format($item->price_small ?: $item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->price_medium ?: $item->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->price_large ?: $item->price, 0, ',', '.') }}</td>
                    <td>
                        @if($item->valid_from && $item->valid_until)
                            {{ \Carbon\Carbon::parse($item->valid_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->valid_until)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($item->is_active)
                            <span class="badge badge-light-success">Aktif</span>
                        @else
                            <span class="badge badge-light-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @can('promo.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('promo.delete')
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
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modalTitle">Tambah Paket</h2>
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
                            <label class="required form-label fw-semibold">Nama Paket</label>
                            <input type="text" name="name" id="f_name" class="form-control form-control" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold">Berlaku Range Tanggal</label>
                            <div class="row g-3">
                                <div class="col-6">
                                    <input type="date" name="valid_from" id="f_valid_from" class="form-control form-control" />
                                </div>
                                <div class="col-6">
                                    <input type="date" name="valid_until" id="f_valid_until" class="form-control form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-4 fv-row">
                            <label class="required form-label fw-semibold">Harga Small (S)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="f_price_small_display" class="form-control price-display" data-target="f_price_small" placeholder="0" inputmode="numeric" autocomplete="off" required />
                            </div>
                            <input type="hidden" name="price_small" id="f_price_small" />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required form-label fw-semibold">Harga Medium (M)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="f_price_medium_display" class="form-control price-display" data-target="f_price_medium" placeholder="0" inputmode="numeric" autocomplete="off" required />
                            </div>
                            <input type="hidden" name="price_medium" id="f_price_medium" />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required form-label fw-semibold">Harga Large (L)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="f_price_large_display" class="form-control price-display" data-target="f_price_large" placeholder="0" inputmode="numeric" autocomplete="off" required />
                            </div>
                            <input type="hidden" name="price_large" id="f_price_large" />
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" id="f_description" class="form-control form-control" rows="3"></textarea>
                    </div>
                    <div class="fv-row mb-5 d-none" id="statusWrapper">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" id="f_status" class="form-select form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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
                title: 'Paket Promo - Garasi Hobby',
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

    return raw.replace(/\D/g, '');
}

function formatRupiah(value) {
    var digits = normalizePriceInput(value);
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

function setPriceValue(targetId, value) {
    var digits = normalizePriceInput(value);
    var target = document.getElementById(targetId);
    var display = document.querySelector('[data-target="' + targetId + '"]');

    if (target) target.value = digits;
    if (display) display.value = formatRupiah(digits);
}

function formatDateInput(date) {
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');

    return date.getFullYear() + '-' + month + '-' + day;
}

function setDefaultDateRange() {
    var from = new Date();
    var until = new Date(from);
    until.setMonth(until.getMonth() + 2);

    document.getElementById('f_valid_from').value = formatDateInput(from);
    document.getElementById('f_valid_until').value = formatDateInput(until);
}

function openCreateModal() {
    formMode = 'create';
    editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Paket';
    document.getElementById('dataForm').reset();
    setPriceValue('f_price_small', '');
    setPriceValue('f_price_medium', '');
    setPriceValue('f_price_large', '');
    setDefaultDateRange();
    document.getElementById('statusWrapper').classList.add('d-none');
    hideErrors();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;
    hideErrors();

    fetch('/master/promo-packages/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Paket';
        document.getElementById('f_name').value = data.name || '';
        setPriceValue('f_price_small', data.price_small || data.price || 0);
        setPriceValue('f_price_medium', data.price_medium || data.price || 0);
        setPriceValue('f_price_large', data.price_large || data.price || 0);
        document.getElementById('f_description').value = data.description || '';
        document.getElementById('f_valid_from').value = data.valid_from || '';
        document.getElementById('f_valid_until').value = data.valid_until || '';
        document.getElementById('f_status').value = data.is_active ? '1' : '0';
        document.getElementById('statusWrapper').classList.remove('d-none');

        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Paket?',
        text: 'Yakin ingin menghapus paket "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/master/promo-packages/' + id, {
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
    document.querySelectorAll('.price-display').forEach(function(input) {
        setPriceValue(input.dataset.target, input.value);
    });

    var url = formMode === 'create' ? '{{ route("promo-packages.store") }}' : '/master/promo-packages/' + editId;
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

document.querySelectorAll('.price-display').forEach(function(input) {
    input.addEventListener('input', function() {
        setPriceValue(this.dataset.target, this.value);
    });
});
</script>
@endpush
