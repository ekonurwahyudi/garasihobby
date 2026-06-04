@extends('layouts.app')

@section('title', 'Tambah Input Keuangan')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Keuangan</li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted"><a href="{{ route('finance-transactions.index') }}" class="text-muted text-hover-primary">Input Keuangan</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Tambah</li>
@endsection

@section('toolbar_actions')
    <a href="{{ route('finance-transactions.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-left fs-3"></i> Kembali
    </a>
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2 class="fw-bold">Tambah Input Keuangan</h2>
        </div>
    </div>
    <form id="dataForm" method="POST" action="{{ route('finance-transactions.store') }}" enctype="multipart/form-data">
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

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Jenis Transaksi</label>
                    <select name="transaction_type" id="transactionType" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="income" @selected(old('transaction_type') === 'income')>Uang Masuk</option>
                        <option value="expense" @selected(old('transaction_type', 'expense') === 'expense')>Uang Keluar</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Tanggal Kwitansi</label>
                    <input type="text" name="transaction_date" id="transactionDate" class="form-control" placeholder="Pilih tanggal" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required />
                </div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-md-8">
                    <label class="required form-label fw-semibold">Aktivitas/Kegiatan</label>
                    <input type="text" name="activity" class="form-control" value="{{ old('activity') }}" required />
                </div>
                <div class="col-md-4">
                    <label class="required form-label fw-semibold">Nominal</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="amountDisplay" class="form-control" inputmode="numeric" autocomplete="off" value="{{ old('amount') ? number_format((float) old('amount'), 0, ',', '.') : '' }}" required />
                    </div>
                    <input type="hidden" name="amount" id="amount" value="{{ old('amount') }}" />
                </div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Kategori Transaksi</label>
                    <select name="finance_category_id" id="categorySelect" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('finance_category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Item Transaksi</label>
                    <select name="finance_item_id" id="itemSelect" class="form-select" required>
                        <option value="">-- Pilih Item --</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" data-category="{{ $item->finance_category_id }}" @selected((string) old('finance_item_id') === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Pembayaran Dari Bank/Cash</label>
                    <select name="bank_account_id" class="form-select bank-account-select" data-placeholder="Pilih Bank/Cash" required>
                        <option value="">-- Pilih Account --</option>
                        @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}"
                            data-logo-url="{{ $account->logo_url }}"
                            data-logo-text="{{ $account->logo_text }}"
                            data-bank-name="{{ $account->bank_name }}"
                            data-bank-code="{{ $account->code }}"
                            data-bank-balance="Rp {{ number_format($account->balance, 0, ',', '.') }}"
                            @selected((string) old('bank_account_id') === (string) $account->id)>
                            {{ $account->code }} - {{ $account->bank_name }} - Rp {{ number_format($account->balance, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Eviden</label>
                    <input type="file" name="evidence[]" id="evidenceInput" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple />
                    <div class="form-text">Format JPG, PNG, WebP, atau PDF. Maksimal 4 MB per file.</div>
                </div>
            </div>

            <div id="evidencePreview" class="d-none border rounded p-3 mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold">Preview Eviden Baru</div>
                    <button type="button" class="btn btn-sm btn-light-danger" id="removeAllEvidenceBtn">Hapus Semua</button>
                </div>
                <div class="row g-3" id="evidencePreviewList"></div>
            </div>

            <div class="fv-row mb-5">
                <label class="form-label fw-semibold">Catatan</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('finance-transactions.index') }}" class="btn btn-light me-3">
                <i class="ki-duotone ki-cross fs-3"><span class="path1"></span><span class="path2"></span></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ki-duotone ki-send fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan & Ajukan
            </button>
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

@push('scripts')
<script>
var selectedEvidenceFiles = [];
var evidenceObjectUrls = [];

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

function normalizeAmount(value) {
    return (value || '').toString().replace(/\D/g, '').replace(/^0+(?=\d)/, '');
}

function formatRupiah(value) {
    var digits = normalizeAmount(value);
    return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
}

function syncAmount() {
    var digits = normalizeAmount(document.getElementById('amountDisplay').value);
    document.getElementById('amount').value = digits;
    document.getElementById('amountDisplay').value = formatRupiah(digits);
}

document.getElementById('amountDisplay').addEventListener('input', syncAmount);
document.getElementById('dataForm').addEventListener('submit', syncAmount);

$('#transactionDate').flatpickr({
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    allowInput: true
});

$('.bank-account-select').select2({
    width: '100%',
    templateResult: bankOptionTemplate,
    templateSelection: bankOptionTemplate,
    escapeMarkup: function(markup) { return markup; }
});

function filterItemByCategory() {
    var category = document.getElementById('categorySelect').value;
    document.querySelectorAll('#itemSelect option[data-category]').forEach(function(option) {
        option.hidden = category && option.dataset.category !== category;
    });
    var selectedItem = document.getElementById('itemSelect').value;
    if (selectedItem && document.querySelector('#itemSelect option[value="' + selectedItem + '"]').hidden) {
        document.getElementById('itemSelect').value = '';
    }
}

document.getElementById('categorySelect').addEventListener('change', filterItemByCategory);
filterItemByCategory();
syncAmount();

function syncEvidenceInput() {
    var dataTransfer = new DataTransfer();
    selectedEvidenceFiles.forEach(function(file) { dataTransfer.items.add(file); });
    document.getElementById('evidenceInput').files = dataTransfer.files;
}

function clearEvidencePreview() {
    evidenceObjectUrls.forEach(function(url) { URL.revokeObjectURL(url); });
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
    evidenceObjectUrls.forEach(function(url) { URL.revokeObjectURL(url); });
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
        removeEvidenceAt(parseInt(removeButton.dataset.index, 10));
        return;
    }
    if (!viewButton) return;
    var index = parseInt(viewButton.dataset.index, 10);
    var file = selectedEvidenceFiles[index];
    var objectUrl = evidenceObjectUrls[index];
    if (!file || !objectUrl) return;
    document.getElementById('evidenceModalBody').innerHTML = file.type.indexOf('image/') === 0
        ? '<img src="' + objectUrl + '" class="w-100 rounded" alt="Preview eviden">'
        : '<iframe src="' + objectUrl + '" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';
    new bootstrap.Modal(document.getElementById('evidenceModal')).show();
});

document.getElementById('removeAllEvidenceBtn').addEventListener('click', clearEvidencePreview);
</script>
@endpush
