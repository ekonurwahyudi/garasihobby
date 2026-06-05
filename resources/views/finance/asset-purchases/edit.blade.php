@extends('layouts.app')

@section('title', $assetPurchase ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('asset-purchases.index') }}" class="text-muted text-hover-primary">Pembelian Aset</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">{{ $assetPurchase ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('asset-purchases.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header pt-6"><h2 class="fw-bold">{{ $assetPurchase ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset' }}</h2></div>
    <form method="POST" action="{{ $assetPurchase ? route('asset-purchases.update', $assetPurchase) : route('asset-purchases.store') }}" enctype="multipart/form-data">
        @csrf @if($assetPurchase) @method('PUT') @endif
        <div class="card-body pt-0">
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="row g-5">
                <div class="col-md-6"><label class="required form-label fw-semibold">Nama Aset</label><input name="asset_name" class="form-control" value="{{ old('asset_name', $assetPurchase?->asset_name) }}" required></div>
                <div class="col-md-6"><label class="required form-label fw-semibold">Kategori Aset</label><select name="asset_category_id" class="form-select" required><option value="">-- Pilih Kategori Aset --</option>@foreach($assetCategories as $category)<option value="{{ $category->id }}" @selected(old('asset_category_id', $assetPurchase?->asset_category_id)==$category->id)>{{ $category->code }} - {{ $category->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="required form-label fw-semibold">Tanggal Pembelian</label><input type="text" name="purchase_date" id="purchaseDate" class="form-control" value="{{ old('purchase_date', $assetPurchase?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Supplier</label><input name="supplier" class="form-control" value="{{ old('supplier', $assetPurchase?->supplier) }}"></div>
                <div class="col-md-4"><label class="required form-label fw-semibold">Bank/Cash</label><select name="bank_account_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($bankAccounts as $bank)<option value="{{ $bank->id }}" @selected(old('bank_account_id', $assetPurchase?->bank_account_id)==$bank->id)>{{ $bank->code }} - {{ $bank->bank_name }} (Rp {{ number_format($bank->balance,0,',','.') }})</option>@endforeach</select></div>
                <div class="col-md-4"><label class="required form-label fw-semibold">Nominal Pembelian</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="amountDisplay" class="form-control money" value="{{ number_format((int) old('purchase_amount', $assetPurchase?->purchase_amount ?? 0),0,',','.') }}" required></div><input type="hidden" name="purchase_amount" id="amountValue" value="{{ old('purchase_amount', (int) ($assetPurchase?->purchase_amount ?? 0)) }}"></div>
                <div class="col-md-4"><label class="required form-label fw-semibold">Umur Manfaat</label><div class="input-group"><input type="number" min="0" name="useful_life_years" class="form-control" value="{{ old('useful_life_years', $assetPurchase?->useful_life_years ?? 0) }}" required><span class="input-group-text">tahun</span></div></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Nilai Residu</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="residualDisplay" class="form-control money" value="{{ number_format((int) old('residual_value', $assetPurchase?->residual_value ?? 0),0,',','.') }}"></div><input type="hidden" name="residual_value" id="residualValue" value="{{ old('residual_value', (int) ($assetPurchase?->residual_value ?? 0)) }}"></div>
                <div class="col-md-6"><label class="required form-label fw-semibold">Metode Depresiasi</label><select name="depreciation_method" class="form-select" required><option value="straight_line" @selected(old('depreciation_method', $assetPurchase?->depreciation_method ?? 'straight_line')==='straight_line')>Garis Lurus</option><option value="percentage" @selected(old('depreciation_method', $assetPurchase?->depreciation_method)==='percentage')>Persen</option><option value="none" @selected(old('depreciation_method', $assetPurchase?->depreciation_method)==='none')>Tanpa Depresiasi</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Persen Depresiasi</label><div class="input-group"><input type="number" step="0.01" min="0" max="100" name="depreciation_percentage" class="form-control" value="{{ old('depreciation_percentage', $assetPurchase?->depreciation_percentage) }}"><span class="input-group-text">%</span></div></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Foto Aset</label><input type="file" name="asset_photos[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Eviden</label><input type="file" name="evidence[]" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple></div>
                <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea name="notes" class="form-control" rows="3">{{ old('notes', $assetPurchase?->notes) }}</textarea></div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end"><a href="{{ route('asset-purchases.index') }}" class="btn btn-light me-3">Batal</a><button class="btn btn-primary">{{ $assetPurchase ? 'Update' : 'Simpan' }}</button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function normalize(v){return (v||'').toString().replace(/\D/g,'').replace(/^0+(?=\d)/,'');}
function format(v){var d=normalize(v);return d?d.replace(/\B(?=(\d{3})+(?!\d))/g,'.'):'';}
function bindMoney(display,value){var d=document.getElementById(display),v=document.getElementById(value);if(!d||!v)return;d.addEventListener('input',function(){var x=normalize(this.value);this.value=format(x);v.value=x||0;});}
bindMoney('amountDisplay','amountValue');bindMoney('residualDisplay','residualValue');
$('#purchaseDate').flatpickr({dateFormat:'Y-m-d',altInput:true,altFormat:'d/m/Y'});
</script>
@endpush
