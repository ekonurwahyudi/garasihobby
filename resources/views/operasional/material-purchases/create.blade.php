@extends('layouts.app')

@section('title', 'Tambah Pembelian Material')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Operasional</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('material-purchases.index') }}" class="text-muted text-hover-primary">Pembelian Material</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Tambah Pembelian</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('material-purchases.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
@endsection

@push('styles')
<style>
.bank-select-option{display:flex;align-items:center;gap:10px;min-width:0}
.bank-select-logo{width:32px;height:32px;border:1px solid #dfe6f2;border-radius:9px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
.bank-select-logo img{max-width:26px;max-height:18px;object-fit:contain}
.bank-select-logo span{font-size:10px;font-weight:800;color:#1d4ed8}
.bank-select-text{min-width:0;line-height:1.25}
.bank-select-text .name{font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bank-select-text .meta{font-size:11px;color:#7e8299;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>
@endpush

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2 class="fw-bold">Tambah Pembelian Material</h2>
        </div>
    </div>
    <form id="dataForm" method="POST" action="{{ route('material-purchases.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body pt-0">
            @if($errors->any())
            <div class="alert alert-danger mb-5">
                <div class="d-flex flex-column">
                    <span class="fw-bold mb-1">Terjadi kesalahan:</span>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <div class="row mb-5">
                <div class="col-md-6 fv-row">
                    <label class="required form-label fw-semibold">Tanggal Kwitansi</label>
                    <div class="input-group mb-5">
                        <span class="input-group-text">
                            <i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <input type="text" name="purchase_date" id="kt_datepicker_1" class="form-control" placeholder="Pick a date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" required />
                    </div>
                </div>
                <div class="col-md-6 fv-row">
                    <label class="form-label fw-semibold">Supplier</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}" />
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0">Item Pembelian</h3>
                <button type="button" class="btn btn-sm btn-light-primary" id="addItemBtn">
                    <i class="ki-duotone ki-plus fs-3"></i> Tambah Item
                </button>
            </div>

            <div class="table-responsive mb-5 border rounded">
                <table class="table table-row-bordered gy-4 gs-5 align-middle mb-0" id="itemsTable">
                    <thead>
                        <tr class="fw-semibold fs-6 text-gray-800">
                            <th class="min-w-300px">Nama Material</th>
                            <th class="min-w-100px">Qty</th>
                            <th class="min-w-120px">Satuan</th>
                            <th class="min-w-180px">Harga Satuan</th>
                            <th class="min-w-180px">Harga Total</th>
                            <th class="w-60px"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                </table>
            </div>

            <div class="border rounded p-5 mb-5">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-gray-700">Grand Total</span>
                    <span class="fw-bold fs-2 text-primary" id="grandTotal">Rp 0</span>
                </div>
                <div class="text-muted fs-7 text-end mt-2">Total dihitung otomatis dari item pembelian.</div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-lg-6 fv-row">
                    <label class="required form-label fw-semibold">Pembayaran Dari Bank/Cash</label>
                    <select name="bank_account_id" class="form-select bank-account-select" data-placeholder="Pilih Bank/Cash" required>
                        <option value="">-- Pilih Bank/Cash --</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}"
                                data-logo-url="{{ $account->logo_url }}"
                                data-logo-text="{{ $account->logo_text }}"
                                data-bank-name="{{ $account->bank_name }}"
                                data-bank-code="{{ $account->code }}"
                                data-bank-balance="Rp {{ number_format($account->balance, 0, ',', '.') }}"
                                @selected((string) old('bank_account_id') === (string) $account->id)>
                                {{ $account->code }} - {{ $account->bank_name }} (Rp {{ number_format($account->balance, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Saat pembelian di-approve, mutasi otomatis tercatat sebagai uang keluar.</div>
                </div>
                <div class="col-lg-6 fv-row">
                    <label class="form-label fw-semibold">Eviden</label>
                    <input type="file" name="evidence[]" id="evidenceInput" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple />
                    <div id="evidencePreview" class="d-none border rounded p-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold">Preview Eviden Baru</div>
                            <button type="button" class="btn btn-sm btn-light-danger" id="removeAllEvidenceBtn">Hapus Semua</button>
                        </div>
                        <div class="row g-3" id="evidencePreviewList"></div>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="form-label fw-semibold">Catatan</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('material-purchases.index') }}" class="btn btn-light me-3">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<div class="modal fade" id="evidenceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Preview Eviden</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body" id="evidenceModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var materials = @json($materialOptions);
var rowIndex = 0;
var selectedEvidenceFiles = [];
var evidenceObjectUrls = [];

function normalizePriceInput(value) {
    var raw = (value || '').toString().trim();

    if (/^\d+\.\d{1,2}$/.test(raw)) {
        raw = raw.split('.')[0];
    }

    var digits = raw.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
    return digits;
}

function formatRupiahInput(value) {
    var digits = normalizePriceInput(value);
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

function formatRupiahLabel(value) {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
}

function materialOptions() {
    return '<option></option>' + materials.map(function(material) {
        return '<option value="' + material.id + '" data-price="' + material.cost_price + '">' + material.name + ' - Stok: ' + material.stock_qty + '</option>';
    }).join('');
}

function addItemRow(data) {
    data = data || {};
    rowIndex++;
    var row = document.createElement('tr');
    row.innerHTML =
        '<td><select name="material_id[]" class="form-select row-material" data-control="select2" data-placeholder="Pilih Nama Material" required>' + materialOptions() + '</select></td>' +
        '<td><input type="text" name="qty[]" class="form-control row-qty" inputmode="numeric" autocomplete="off" required /></td>' +
        '<td><select name="unit[]" class="form-select" required><option value="pcs">pcs</option><option value="kg">kg</option><option value="liter">liter</option><option value="meter">meter</option><option value="set">set</option></select></td>' +
        '<td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control row-unit-price-display" placeholder="0" inputmode="numeric" autocomplete="off" required /></div><input type="hidden" name="unit_price[]" class="row-unit-price" /></td>' +
        '<td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control row-total-display bg-light" value="0" readonly disabled /></div></td>' +
        '<td class="text-end"><button type="button" class="btn btn-icon btn-sm btn-light-danger row-delete" title="Hapus"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button></td>';

    document.getElementById('itemsBody').appendChild(row);
    bindRowEvents(row);
    initSelect2(row);
    if (data.material_id) {
        $(row).find('.row-material').val(data.material_id).trigger('change');
    }
    if (data.qty) {
        row.querySelector('.row-qty').value = data.qty;
    }
    if (data.unit) {
        row.querySelector('select[name="unit[]"]').value = data.unit;
    }
    if (data.unit_price !== undefined) {
        setRowUnitPrice(row, data.unit_price);
    } else {
        updateRowTotal(row);
    }
    updateDeleteButtons();
}

function bindRowEvents(row) {
    row.querySelector('.row-qty').addEventListener('input', function() {
        updateRowTotal(row);
    });

    row.querySelector('.row-unit-price-display').addEventListener('input', function() {
        setRowUnitPrice(row, this.value);
    });

    row.querySelector('.row-delete').addEventListener('click', function() {
        row.remove();
        updateGrandTotal();
        updateDeleteButtons();
    });
}

function setRowUnitPrice(row, value) {
    var digits = normalizePriceInput(value);
    row.querySelector('.row-unit-price').value = digits;
    row.querySelector('.row-unit-price-display').value = formatRupiahInput(digits);
    updateRowTotal(row);
}

function updateRowTotal(row) {
    var qty = parseInt(normalizePriceInput(row.querySelector('.row-qty').value) || '0', 10);
    var unitPrice = parseInt(row.querySelector('.row-unit-price').value || '0', 10);
    row.querySelector('.row-total-display').value = formatRupiahInput(qty * unitPrice);
    updateGrandTotal();
}

function updateGrandTotal() {
    var total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(function(row) {
        var qty = parseInt(normalizePriceInput(row.querySelector('.row-qty').value) || '0', 10);
        var unitPrice = parseInt(row.querySelector('.row-unit-price').value || '0', 10);
        total += qty * unitPrice;
    });
    document.getElementById('grandTotal').textContent = formatRupiahLabel(total);
}

function updateDeleteButtons() {
    var rows = document.querySelectorAll('#itemsBody tr');
    rows.forEach(function(row) {
        row.querySelector('.row-delete').disabled = rows.length <= 1;
    });
}

function initSelect2(row) {
    $(row).find('.row-material').select2({
        placeholder: 'Pilih Nama Material',
        width: '100%'
    });
}

function bankOptionTemplate(option) {
    if (!option.id) return option.text;

    var el = option.element;
    var logoUrl = el.getAttribute('data-logo-url');
    var logoText = el.getAttribute('data-logo-text') || 'BNK';
    var bankName = el.getAttribute('data-bank-name') || option.text;
    var bankCode = el.getAttribute('data-bank-code') || '';
    var balance = el.getAttribute('data-bank-balance') || '';
    var logo = logoUrl
        ? '<img src="' + logoUrl + '" alt="' + bankName.replace(/"/g, '&quot;') + '">'
        : '<span>' + logoText + '</span>';

    return $(
        '<div class="bank-select-option">' +
            '<div class="bank-select-logo">' + logo + '</div>' +
            '<div class="bank-select-text">' +
                '<div class="name">' + bankCode + ' - ' + bankName + '</div>' +
                '<div class="meta">Saldo ' + balance + '</div>' +
            '</div>' +
        '</div>'
    );
}

document.getElementById('dataForm').addEventListener('submit', function() {
    document.querySelectorAll('#itemsBody tr').forEach(function(row) {
        row.querySelector('.row-qty').value = normalizePriceInput(row.querySelector('.row-qty').value);
        setRowUnitPrice(row, row.querySelector('.row-unit-price-display').value);
    });
});

document.getElementById('addItemBtn').addEventListener('click', addItemRow);

$('.bank-account-select').select2({
    width: '100%',
    templateResult: bankOptionTemplate,
    templateSelection: bankOptionTemplate,
    escapeMarkup: function(markup) { return markup; }
});

function syncEvidenceInput() {
    var dataTransfer = new DataTransfer();
    selectedEvidenceFiles.forEach(function(file) {
        dataTransfer.items.add(file);
    });
    document.getElementById('evidenceInput').files = dataTransfer.files;
}

function clearEvidencePreview() {
    evidenceObjectUrls.forEach(function(url) {
        URL.revokeObjectURL(url);
    });
    evidenceObjectUrls = [];
    selectedEvidenceFiles = [];
    document.getElementById('evidenceInput').value = '';
    document.getElementById('evidencePreviewList').innerHTML = '';
    document.getElementById('evidenceModalBody').innerHTML = '';
    document.getElementById('evidencePreview').classList.add('d-none');
}

function removeEvidenceAt(index) {
    selectedEvidenceFiles.splice(index, 1);
    syncEvidenceInput();
    renderEvidencePreview();
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1024 / 1024).toFixed(1) + ' MB';
}

document.getElementById('evidenceInput').addEventListener('change', function() {
    selectedEvidenceFiles = Array.from(this.files || []);
    renderEvidencePreview();
});

function renderEvidencePreview() {
    evidenceObjectUrls.forEach(function(url) {
        URL.revokeObjectURL(url);
    });
    evidenceObjectUrls = [];

    if (!selectedEvidenceFiles.length) {
        clearEvidencePreview();
        return;
    }

    var list = document.getElementById('evidencePreviewList');
    list.innerHTML = '';

    selectedEvidenceFiles.forEach(function(file, index) {
        var objectUrl = URL.createObjectURL(file);
        evidenceObjectUrls.push(objectUrl);
        var thumb = file.type.indexOf('image/') === 0
            ? '<img src="' + objectUrl + '" class="w-100 h-100" style="object-fit:cover;" alt="Preview eviden">'
            : '<span class="badge badge-light-danger">PDF</span>';

        var col = document.createElement('div');
        col.className = 'col-md-6 col-xl-4';
        col.innerHTML =
            '<div class="border rounded p-3 h-100">' +
                '<div class="border rounded bg-light d-flex align-items-center justify-content-center mb-3" style="height:120px;overflow:hidden;">' + thumb + '</div>' +
                '<div class="fw-semibold text-truncate" title="' + file.name + '">' + file.name + '</div>' +
                '<div class="text-muted fs-7 mb-3">' + formatFileSize(file.size) + '</div>' +
                '<div class="d-flex gap-2">' +
                    '<button type="button" class="btn btn-sm btn-light-primary evidence-view" data-index="' + index + '">Lihat</button>' +
                    '<button type="button" class="btn btn-sm btn-light-danger evidence-remove" data-index="' + index + '">Hapus</button>' +
                '</div>' +
            '</div>';
        list.appendChild(col);
    });

    document.getElementById('evidencePreview').classList.remove('d-none');
}

document.getElementById('evidencePreviewList').addEventListener('click', function(event) {
    var viewButton = event.target.closest('.evidence-view');
    var removeButton = event.target.closest('.evidence-remove');

    if (removeButton) {
        removeEvidenceAt(parseInt(removeButton.getAttribute('data-index'), 10));
        return;
    }

    if (!viewButton) return;

    var index = parseInt(viewButton.getAttribute('data-index'), 10);
    var file = selectedEvidenceFiles[index];
    var objectUrl = evidenceObjectUrls[index];
    if (!file || !objectUrl) return;

    var body = document.getElementById('evidenceModalBody');
    body.innerHTML = file.type.indexOf('image/') === 0
        ? '<img src="' + objectUrl + '" class="w-100 rounded" alt="Preview eviden">'
        : '<iframe src="' + objectUrl + '" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';
    new bootstrap.Modal(document.getElementById('evidenceModal')).show();
});

document.getElementById('removeAllEvidenceBtn').addEventListener('click', clearEvidencePreview);

$('#kt_datepicker_1').flatpickr({
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    defaultDate: '{{ old('purchase_date', now()->format('Y-m-d')) }}'
});

addItemRow();
</script>
@endpush
