@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Pelanggan</li>
@endsection

@section('toolbar_actions')
    @can('customers.create')
    <a href="#" class="btn btn-sm btn-success" id="exportExcel">
        <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i> Export Excel
    </a>
    <button type="button" class="btn btn-sm btn-primary" onclick="openCreateModal()">
        <i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Pelanggan
    </button>
    @endcan
@endsection

@section('content')
@php
    $vehicleSizeLabels = [
        'small' => 'S',
        'medium' => 'M',
        'large' => 'L',
    ];
    $vehicleSizeDescriptions = [
        'small' => 'City Car / Hatchback / Sedan Kecil',
        'medium' => 'MPV / SUV Medium / Pickup Ringan',
        'large' => 'SUV Besar / Double Cabin / Ladder Frame',
    ];
@endphp
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
                <input type="text" id="searchInput" class="form-control form-control w-250px ps-12" placeholder="Cari pelanggan..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded">
            <thead>
                <tr class="fw-semibold fs-6 text-gray-800">
                    <th class="w-50px">No</th>
                    <th>Plat Mobil</th>
                    <th>Nama Pemilik</th>
                    <th>Jenis Mobil</th>
                    <th>Ukuran</th>
                    <th>Tahun</th>
                    <th>No HP</th>
                    <th>Email</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $customer)
                @foreach($customer->vehicles as $vehicle)
                <tr>
                    <td>{{ $loop->parent->iteration }}</td>
                    <td><span class="fw-bold">{{ $vehicle->plate_number }}</span></td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                    <td>
                        @if($vehicle->vehicle_size)
                            <span class="badge badge-light-primary">
                                {{ $vehicleSizeLabels[$vehicle->vehicle_size] ?? strtoupper($vehicle->vehicle_size) }}
                            </span>
                            <span class="text-muted fs-8 d-block">{{ $vehicleSizeDescriptions[$vehicle->vehicle_size] ?? '' }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $vehicle->year ?? '-' }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->email ?? '-' }}</td>
                    <td class="text-end">
                        @can('customers.view')
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-icon btn-sm btn-info" title="Detail">
                            <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </a>
                        @endcan
                        @can('customers.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $customer->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('customers.delete')
                        <button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $customer->id }}', '{{ $customer->name }}')" title="Hapus">
                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                        @endcan
                    </td>
                </tr>
                @endforeach
                @if($customer->vehicles->isEmpty())
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>-</td>
                    <td>{{ $customer->name }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->email ?? '-' }}</td>
                    <td class="text-end">
                        @can('customers.view')
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-icon btn-sm btn-info" title="Detail">
                            <i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </a>
                        @endcan
                        @can('customers.edit')
                        <button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $customer->id }}')" title="Edit">
                            <i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                        @endcan
                        @can('customers.delete')
                        <button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $customer->id }}', '{{ $customer->name }}')" title="Hapus">
                            <i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        </button>
                        @endcan
                    </td>
                </tr>
                @endif
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
                <h2 class="fw-bold">Detail Pelanggan</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body mx-5 my-7">
                <table class="table table-row-bordered gy-4 gs-5">
                    <tbody>
                        <tr><td class="fw-semibold text-gray-600 w-150px">Plat Mobil</td><td class="fw-bold text-gray-800" id="v_plate"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Nama Pemilik</td><td class="fw-bold text-gray-800" id="v_name"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Jenis Mobil</td><td class="fw-bold text-gray-800" id="v_vehicle"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Ukuran Mobil</td><td class="fw-bold text-gray-800" id="v_vehicle_size"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Tahun</td><td class="fw-bold text-gray-800" id="v_year"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">No HP</td><td class="fw-bold text-gray-800" id="v_phone"></td></tr>
                        <tr><td class="fw-semibold text-gray-600">Email</td><td class="fw-bold text-gray-800" id="v_email"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Form --}}
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modalTitle">Tambah Pelanggan</h2>
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
                    <div class="fw-bold fs-5 mb-4 text-gray-800">Data Kendaraan</div>
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Plat Mobil</label>
                            <input type="text" name="plate_number" id="f_plate" class="form-control form-control" placeholder="B 1234 XYZ" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold">Merk</label>
                            <select name="brand" id="f_brand" class="form-select" data-control="select2" data-placeholder="Pilih Merk" data-dropdown-parent="#formModal">
                                <option></option>
                                <option value="Toyota">Toyota</option>
                                <option value="Honda">Honda</option>
                                <option value="Daihatsu">Daihatsu</option>
                                <option value="Suzuki">Suzuki</option>
                                <option value="Mitsubishi">Mitsubishi</option>
                                <option value="Nissan">Nissan</option>
                                <option value="Hyundai">Hyundai</option>
                                <option value="KIA">KIA</option>
                                <option value="Mazda">Mazda</option>
                                <option value="Wuling">Wuling</option>
                                <option value="Isuzu">Isuzu</option>
                                <option value="BMW">BMW</option>
                                <option value="Mercedes-Benz">Mercedes-Benz</option>
                                <option value="Chevrolet">Chevrolet</option>
                                <option value="Ford">Ford</option>
                                <option value="Volkswagen">Volkswagen</option>
                                <option value="Subaru">Subaru</option>
                                <option value="Lexus">Lexus</option>
                                <option value="MG">MG</option>
                                <option value="Chery">Chery</option>
                                <option value="DFSK">DFSK</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold">Jenis Mobil</label>
                            <input type="text" name="vehicle_model" id="f_model" class="form-control form-control" placeholder="Avanza, Jazz, dll" />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" name="year" id="f_year" class="form-control form-control" min="1900" max="{{ date('Y') + 1 }}" />
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="required form-label fw-semibold">Ukuran Mobil</label>
                        <input type="hidden" name="vehicle_size" id="f_vehicle_size" required />
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="vehicle-size-option border rounded p-3 d-flex align-items-start gap-3 h-100 cursor-pointer" for="vehicle_size_small">
                                    <input type="checkbox" class="form-check-input vehicle-size-check mt-1" id="vehicle_size_small" value="small" />
                                    <span class="vehicle-size-icon">
                                        <i class="ki-duotone ki-car fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span>
                                        <span class="fw-bold text-gray-900 d-block">S</span>
                                        <span class="text-muted fs-8">Small</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="vehicle-size-option border rounded p-3 d-flex align-items-start gap-3 h-100 cursor-pointer" for="vehicle_size_medium">
                                    <input type="checkbox" class="form-check-input vehicle-size-check mt-1" id="vehicle_size_medium" value="medium" />
                                    <span class="vehicle-size-icon">
                                        <i class="ki-duotone ki-car fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span>
                                        <span class="fw-bold text-gray-900 d-block">M</span>
                                        <span class="text-muted fs-8">Medium</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="vehicle-size-option border rounded p-3 d-flex align-items-start gap-3 h-100 cursor-pointer" for="vehicle_size_large">
                                    <input type="checkbox" class="form-check-input vehicle-size-check mt-1" id="vehicle_size_large" value="large" />
                                    <span class="vehicle-size-icon">
                                        <i class="ki-duotone ki-car fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span>
                                        <span class="fw-bold text-gray-900 d-block">L</span>
                                        <span class="text-muted fs-8">Large</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="vehicle-size-guide border rounded mt-3 overflow-hidden">
                            <div class="vehicle-size-guide-row">
                                <span class="vehicle-size-guide-badge">S</span>
                                <i class="ki-duotone ki-car fs-3 text-gray-600"><span class="path1"></span><span class="path2"></span></i>
                                <span>City Car / Hatchback / Sedan Kecil</span>
                            </div>
                            <div class="vehicle-size-guide-row">
                                <span class="vehicle-size-guide-badge">M</span>
                                <i class="ki-duotone ki-car fs-3 text-gray-600"><span class="path1"></span><span class="path2"></span></i>
                                <span>MPV / SUV Medium / Pickup Ringan</span>
                            </div>
                            <div class="vehicle-size-guide-row">
                                <span class="vehicle-size-guide-badge">L</span>
                                <i class="ki-duotone ki-car fs-3 text-gray-600"><span class="path1"></span><span class="path2"></span></i>
                                <span>SUV Besar / Double Cabin / Ladder Frame</span>
                            </div>
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Foto Mobil</label>
                        <input type="file" name="vehicle_photo" id="vehiclePhotoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp" />
                        <div class="form-text">Opsional. Format JPG, PNG, atau WebP, maksimal 4 MB.</div>
                        <div id="vehiclePhotoPreview" class="d-none border rounded p-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold" id="vehiclePhotoPreviewTitle">Preview Foto Mobil</div>
                                <button type="button" class="btn btn-sm btn-light-danger" id="removeVehiclePhotoBtn">Hapus Preview</button>
                            </div>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="height:180px;">
                                <img src="" alt="Preview foto mobil" id="vehiclePhotoPreviewImage" class="w-100 h-100" style="object-fit:cover;" />
                            </div>
                        </div>
                    </div>
                    <div class="separator separator-dashed my-5"></div>
                    <div class="fw-bold fs-5 mb-4 text-gray-800">Data Pemilik</div>
                    <div class="row mb-5">
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">Nama Pemilik</label>
                            <input type="text" name="name" id="f_name" class="form-control form-control" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required form-label fw-semibold">No HP</label>
                            <input type="text" name="phone" id="f_phone" class="form-control form-control" required />
                        </div>
                    </div>
                    <div class="fv-row mb-5">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="f_email" class="form-control form-control" />
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
        buttons: [{ extend: 'excelHtml5', title: 'Data Pelanggan - Garasi Hobby', exportOptions: { columns: [0,1,2,3,4,5,6,7] } }],
        order: [],
        pageLength: 10,
        columnDefs: [{ orderable: false, targets: [0, 8] }],
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

var vehicleSizeLabels = {
    small: 'S - City Car / Hatchback / Sedan Kecil',
    medium: 'M - MPV / SUV Medium / Pickup Ringan',
    large: 'L - SUV Besar / Double Cabin / Ladder Frame'
};
var vehiclePhotoObjectUrl = null;

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

function setVehicleSize(value) {
    document.getElementById('f_vehicle_size').value = value || '';
    document.querySelectorAll('.vehicle-size-check').forEach(function(input) {
        var checked = input.value === value;
        input.checked = checked;
        input.closest('.vehicle-size-option').classList.toggle('border-primary', checked);
        input.closest('.vehicle-size-option').classList.toggle('bg-light-primary', checked);
    });
}

function setBrandValue(value) {
    var exists = Array.from(document.getElementById('f_brand').options).some(function(option) {
        return option.value === value;
    });

    if (value && !exists) {
        $('#f_brand').append(new Option(value, value, true, true));
    }

    $('#f_brand').val(value || null).trigger('change');
}

function clearVehiclePhotoPreview() {
    if (vehiclePhotoObjectUrl) {
        URL.revokeObjectURL(vehiclePhotoObjectUrl);
        vehiclePhotoObjectUrl = null;
    }

    document.getElementById('vehiclePhotoInput').value = '';
    document.getElementById('vehiclePhotoPreviewImage').src = '';
    document.getElementById('vehiclePhotoPreview').classList.add('d-none');
}

function showVehiclePhotoPreview(src, title) {
    document.getElementById('vehiclePhotoPreviewTitle').textContent = title || 'Preview Foto Mobil';
    document.getElementById('vehiclePhotoPreviewImage').src = src;
    document.getElementById('vehiclePhotoPreview').classList.remove('d-none');
}

function viewCustomer(id) {
    fetch('/operasional/customers/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('v_plate').textContent = data.plate_number || '-';
        document.getElementById('v_name').textContent = data.name || '-';
        document.getElementById('v_vehicle').textContent = (data.brand || '') + ' ' + (data.vehicle_model || '') || '-';
        document.getElementById('v_vehicle_size').textContent = vehicleSizeLabels[data.vehicle_size] || '-';
        document.getElementById('v_year').textContent = data.year || '-';
        document.getElementById('v_phone').textContent = data.phone || '-';
        document.getElementById('v_email').textContent = data.email || '-';
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    });
}

function openCreateModal() {
    formMode = 'create';
    editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Pelanggan';
    document.getElementById('dataForm').reset();
    setBrandValue('');
    setVehicleSize('');
    clearVehiclePhotoPreview();
    hideErrors();
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function openEditModal(id) {
    formMode = 'edit';
    editId = id;
    hideErrors();

    fetch('/operasional/customers/' + id + '/edit', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('modalTitle').textContent = 'Edit Pelanggan';
        document.getElementById('f_plate').value = data.plate_number || '';
        setBrandValue(data.brand || '');
        document.getElementById('f_model').value = data.vehicle_model || '';
        setVehicleSize(data.vehicle_size || '');
        document.getElementById('f_year').value = data.year || '';
        document.getElementById('f_name').value = data.name || '';
        document.getElementById('f_phone').value = data.phone || '';
        document.getElementById('f_email').value = data.email || '';
        clearVehiclePhotoPreview();
        if (data.vehicle_photo_url) {
            showVehiclePhotoPreview(data.vehicle_photo_url, 'Foto Mobil Saat Ini');
        }
        new bootstrap.Modal(document.getElementById('formModal')).show();
    });
}

function deleteItem(id, name) {
    Swal.fire({
        title: 'Hapus Pelanggan?',
        text: 'Yakin ingin menghapus "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/operasional/customers/' + id, {
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

    var url = formMode === 'create' ? '{{ route("customers.store") }}' : '/operasional/customers/' + editId;
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

document.querySelectorAll('.vehicle-size-check').forEach(function(input) {
    input.addEventListener('change', function() {
        setVehicleSize(this.checked ? this.value : '');
    });
});

document.getElementById('vehiclePhotoInput').addEventListener('change', function() {
    if (vehiclePhotoObjectUrl) {
        URL.revokeObjectURL(vehiclePhotoObjectUrl);
        vehiclePhotoObjectUrl = null;
    }

    var file = this.files && this.files[0];
    if (!file) {
        clearVehiclePhotoPreview();
        return;
    }

    vehiclePhotoObjectUrl = URL.createObjectURL(file);
    showVehiclePhotoPreview(vehiclePhotoObjectUrl, 'Preview Foto Mobil Baru');
});

document.getElementById('removeVehiclePhotoBtn').addEventListener('click', clearVehiclePhotoPreview);
</script>
@endpush

@push('styles')
<style>
.vehicle-size-option {
    transition: border-color .15s ease, background-color .15s ease;
}

.vehicle-size-icon {
    display: inline-flex;
    width: 30px;
    min-width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #f5f8fa;
}

.vehicle-size-guide-row {
    display: grid;
    grid-template-columns: 34px 34px 1fr;
    align-items: center;
    column-gap: 12px;
    padding: 10px 16px;
    font-size: .925rem;
}

.vehicle-size-guide-row + .vehicle-size-guide-row {
    border-top: 1px solid #eff2f5;
}

.vehicle-size-guide-badge {
    font-weight: 700;
    color: #1f2a44;
}

@media (max-width: 575.98px) {
    .vehicle-size-guide-row {
        grid-template-columns: 28px 28px 1fr;
        column-gap: 10px;
        padding: 10px 12px;
    }
}
</style>
@endpush
