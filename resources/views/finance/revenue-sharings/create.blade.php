@extends('layouts.app')

@section('title', 'Tambah Revenue Sharing')

@section('breadcrumb')
<li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted"><a href="{{ route('revenue-sharings.index') }}" class="text-muted text-hover-primary">Revenue Sharing</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Tambah</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('revenue-sharings.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@section('content')
@php
    $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F')]);
    $selectedType = old('cutoff_type', $cutoff['type']);
    $selectedYear = old('cutoff_year', $cutoff['year']);
    $selectedMonth = old('cutoff_month', $cutoff['month']);
    $selectedQuarter = old('cutoff_quarter', $cutoff['quarter']);
@endphp
<div class="card card-flush revenue-form-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h2 class="fw-bold">Sharing Revenue</h2></div>
    </div>
    <form method="POST" action="{{ route('revenue-sharings.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="revenue_cutoff_id" value="{{ old('revenue_cutoff_id', $cutoff['id'] ?? '') }}">
        <div class="card-body pt-0">
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="revenue-cutoff-box mb-7">
                <div>
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Cut Off Revenue</div>
                    <div class="fw-bolder fs-3 text-gray-900">{{ $cutoff['label'] }}</div>
                    <div class="text-muted">Revenue bersih periode ini: <span class="fw-bold text-success">Rp {{ number_format($cutoff['netRevenue'], 0, ',', '.') }}</span></div>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-4 text-primary">Rp {{ number_format($cutoff['grossRevenue'], 0, ',', '.') }}</div>
                    <div class="text-muted fs-8">Revenue kotor</div>
                </div>
            </div>
            <div class="row g-5">
                <div class="col-md-4"><label class="required form-label fw-semibold">Periode Cut Off</label><select name="cutoff_type" id="cutoffType" class="form-select"><option value="monthly" @selected($selectedType==='monthly')>Bulanan</option><option value="quarterly" @selected($selectedType==='quarterly')>Triwulan</option><option value="yearly" @selected($selectedType==='yearly')>Tahunan</option></select></div>
                <div class="col-md-4"><label class="required form-label fw-semibold">Tahun</label><input type="number" name="cutoff_year" class="form-control" value="{{ $selectedYear }}" min="2020" max="2100" required></div>
                <div class="col-md-4 cutoff-month"><label class="required form-label fw-semibold">Bulan</label><select name="cutoff_month" class="form-select">@foreach($months as $value => $label)<option value="{{ $value }}" @selected((int)$selectedMonth===$value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-4 cutoff-quarter"><label class="required form-label fw-semibold">Triwulan</label><select name="cutoff_quarter" class="form-select">@foreach(range(1, 4) as $quarter)<option value="{{ $quarter }}" @selected((int)$selectedQuarter===$quarter)>Triwulan {{ $quarter }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="required form-label fw-semibold">Nama Penerima</label><input name="recipient_name" class="form-control" value="{{ old('recipient_name') }}" placeholder="Nama partner/penerima sharing" required></div>
                <div class="col-md-3"><label class="required form-label fw-semibold">Persentase Pembagian</label><div class="input-group"><input type="number" step="0.01" min="0" max="100" name="sharing_percentage" id="sharingPercentage" class="form-control" value="{{ old('sharing_percentage', 0) }}" required><span class="input-group-text">%</span></div></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Nominal Sharing</label><div class="input-group"><span class="input-group-text">Rp</span><input id="sharingAmountDisplay" class="form-control" value="0" readonly></div><div class="form-text">Otomatis dari % x revenue bersih.</div></div>
                <div class="col-12"><label class="required form-label fw-semibold">Akun Bank</label><select name="bank_account_id" class="form-select bank-account-select" required><option value="">-- Pilih Bank --</option>@foreach($bankAccounts as $bank)<option value="{{ $bank->id }}" data-logo-url="{{ $bank->logo_url }}" data-logo-text="{{ $bank->logo_text }}" data-bank-name="{{ $bank->bank_name }}" data-bank-code="{{ $bank->code }}" data-bank-balance="Rp {{ number_format($bank->balance,0,',','.') }}" @selected(old('bank_account_id')==$bank->id)>{{ $bank->bank_name }}</option>@endforeach</select><div class="form-text">Saat disetujui, nominal sharing tercatat sebagai uang keluar dari bank ini.</div></div>
                <div class="col-12"><label class="form-label fw-semibold">Eviden</label><input type="file" name="evidence[]" id="evidenceInput" class="form-control preview-input" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple><div class="form-text">Format JPG, PNG, WebP, atau PDF.</div></div>
                <div class="col-12"><div id="evidencePreview" class="d-none border rounded p-3"><div class="fw-semibold mb-3">Preview Eviden</div><div class="row g-3 preview-list"></div></div></div>
                <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk revenue sharing">{{ old('notes') }}</textarea></div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end"><a href="{{ route('revenue-sharings.index') }}" class="btn btn-light me-3">Batal</a><button class="btn btn-primary"><i class="ki-duotone ki-send fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan & Ajukan</button></div>
    </form>
</div>
<div class="modal fade" id="previewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content"><div class="modal-header"><h2 class="fw-bold">Preview Eviden</h2><button class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button></div><div class="modal-body" id="previewModalBody"></div></div></div></div>
@endsection

@push('styles')
<style>.revenue-form-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}.revenue-cutoff-box{border:1px dashed #cfe0f5;border-radius:16px;background:#f8fbff;padding:18px;display:flex;justify-content:space-between;gap:16px;align-items:center}.bank-select-option{display:flex;align-items:center;gap:10px;min-width:0}.bank-select-logo{width:32px;height:32px;border:1px solid #dfe6f2;border-radius:9px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}.bank-select-logo img{max-width:26px;max-height:18px;object-fit:contain}.bank-select-logo span{font-size:10px;font-weight:800;color:#1d4ed8}.bank-select-text{min-width:0;line-height:1.25}.bank-select-text .name{font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bank-select-text .meta{font-size:11px;color:#7e8299;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}</style>
@endpush

@push('scripts')
<script>
var netRevenue = {{ (float) $cutoff['netRevenue'] }};
function formatNumber(v){return Math.round(v||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function syncSharing(){var pct=parseFloat(document.getElementById('sharingPercentage').value||0);document.getElementById('sharingAmountDisplay').value=formatNumber(Math.max(0,netRevenue)*(pct/100));}
function syncCutoffFields(){var type=document.getElementById('cutoffType').value;document.querySelector('.cutoff-month').classList.toggle('d-none',type!=='monthly');document.querySelector('.cutoff-quarter').classList.toggle('d-none',type!=='quarterly');}
document.getElementById('sharingPercentage').addEventListener('input',syncSharing);document.getElementById('cutoffType').addEventListener('change',syncCutoffFields);syncSharing();syncCutoffFields();
function bankOptionTemplate(option){if(!option.id)return option.text;var el=option.element;var logoUrl=el.getAttribute('data-logo-url');var logoText=el.getAttribute('data-logo-text')||'BNK';var bankName=el.getAttribute('data-bank-name')||option.text;var bankCode=el.getAttribute('data-bank-code')||'';var balance=el.getAttribute('data-bank-balance')||'';var logo=logoUrl?'<img src="'+logoUrl+'" alt="'+bankName.replace(/"/g,'&quot;')+'">':'<span>'+logoText+'</span>';return $('<div class="bank-select-option"><div class="bank-select-logo">'+logo+'</div><div class="bank-select-text"><div class="name">'+bankCode+' - '+bankName+'</div><div class="meta">Saldo '+balance+'</div></div></div>');}
$('.bank-account-select').select2({width:'100%',templateResult:bankOptionTemplate,templateSelection:bankOptionTemplate,escapeMarkup:function(markup){return markup;}});
var previewUrls=[];document.getElementById('evidenceInput').addEventListener('change',function(){var box=document.getElementById('evidencePreview'),list=box.querySelector('.preview-list');previewUrls.forEach(function(url){URL.revokeObjectURL(url);});previewUrls=[];list.innerHTML='';Array.from(this.files||[]).forEach(function(file){var url=URL.createObjectURL(file);previewUrls.push(url);var isImage=file.type.indexOf('image/')===0;var col=document.createElement('div');col.className='col-md-3';col.innerHTML='<div class="border rounded p-3 h-100"><button type="button" class="border rounded bg-light d-flex align-items-center justify-content-center w-100 mb-3 preview-open" data-url="'+url+'" data-type="'+(isImage?'image':'file')+'" style="height:120px;overflow:hidden;">'+(isImage?'<img src="'+url+'" class="w-100 h-100" style="object-fit:cover" alt="Preview">':'<span class="badge badge-light-primary">PDF</span>')+'</button><div class="fw-semibold text-truncate">'+file.name+'</div></div>';list.appendChild(col);});box.classList.toggle('d-none',!this.files.length);});document.addEventListener('click',function(e){var btn=e.target.closest('.preview-open');if(!btn)return;document.getElementById('previewModalBody').innerHTML=btn.dataset.type==='image'?'<img src="'+btn.dataset.url+'" class="w-100 rounded" alt="Preview">':'<iframe src="'+btn.dataset.url+'" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';new bootstrap.Modal(document.getElementById('previewModal')).show();});
</script>
@endpush
