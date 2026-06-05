@extends('layouts.app')

@section('title', 'Tambah Hutang/Piutang')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted"><a href="{{ route('debt-receivables.index') }}" class="text-muted text-hover-primary">Hutang Piutang</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Tambah</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('debt-receivables.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><h2 class="fw-bold">Tambah Hutang/Piutang</h2></div>
    </div>
    <form id="dataForm" method="POST" action="{{ route('debt-receivables.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body pt-0">
            @if($errors->any())
            <div class="alert alert-danger mb-5">
                <div class="fw-bold mb-1">Terjadi kesalahan:</div>
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ki-duotone ki-calendar fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                        <input name="transaction_date" id="transactionDate" class="form-control" placeholder="Pilih tanggal" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Jatuh Tempo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ki-duotone ki-calendar fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                        <input name="due_date" id="dueDate" class="form-control" placeholder="Pilih tanggal" value="{{ old('due_date') }}" required>
                    </div>
                </div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Jenis</label>
                    <select name="type" id="typeSelect" class="form-select" required>
                        <option value="debt" @selected(old('type', 'debt') === 'debt')>Hutang</option>
                        <option value="receivable" @selected(old('type') === 'receivable')>Piutang</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="required form-label fw-semibold">Kategori</label>
                    <select name="debt_receivable_category_id" id="debtCategorySelect" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($debtCategories as $category)
                        <option value="{{ $category->id }}" data-type="{{ $category->type }}" @selected(old('debt_receivable_category_id') == $category->id)>{{ $category->description ?: $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-5">
                <label class="required form-label fw-semibold" id="partyLabel">Nama Pemberi Hutang</label>
                <input name="party_name" id="partyName" class="form-control" value="{{ old('party_name') }}" placeholder="Nama pihak pemberi hutang" required>
            </div>

            <div class="mb-5">
                <label class="required form-label fw-semibold">Aktivitas/Kegiatan</label>
                <textarea name="activity" class="form-control" rows="4" required>{{ old('activity') }}</textarea>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-md-6">
                    <label class="required form-label fw-semibold" id="amountLabel">Nominal (Pinjaman)</label>
                    <div class="input-group"><span class="input-group-text">Rp.</span><input id="amountDisplay" class="form-control money" inputmode="numeric" value="{{ old('amount') ? number_format((float) old('amount'),0,',','.') : '0' }}" required></div>
                    <input type="hidden" name="amount" id="amountValue" value="{{ old('amount', 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Total Bayar <span class="text-muted fs-8">(termasuk bunga/biaya lain)</span></label>
                    <div class="input-group"><span class="input-group-text">Rp.</span><input id="totalDisplay" class="form-control money" inputmode="numeric" placeholder="Kosongkan jika sama dengan nominal" value="{{ old('total_amount') ? number_format((float) old('total_amount'),0,',','.') : '' }}"></div>
                    <input type="hidden" name="total_amount" id="totalValue" value="{{ old('total_amount') }}">
                    <div class="form-text">Contoh: pinjam Rp 2.000.000 tapi total harus bayar Rp 2.500.000 karena bunga</div>
                </div>
            </div>

            <div class="row g-5 mb-5" id="paymentRow">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sudah Dibayar</label>
                    <div class="input-group"><span class="input-group-text">Rp.</span><input id="paidDisplay" class="form-control money" inputmode="numeric" value="{{ old('paid_amount') ? number_format((float) old('paid_amount'),0,',','.') : '0' }}"></div>
                    <input type="hidden" name="paid_amount" id="paidValue" value="{{ old('paid_amount', 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sisa Pembayaran</label>
                    <div class="input-group"><span class="input-group-text">Rp.</span><input id="remainingDisplay" class="form-control bg-light" value="0" readonly></div>
                </div>
            </div>

            <div class="mb-5">
                <label class="required form-label fw-semibold">Account Bank</label>
                <select name="bank_account_id" class="form-select bank-account-select" required>
                    <option value="">-- Pilih Bank --</option>
                    @foreach($bankAccounts as $account)
                    <option value="{{ $account->id }}"
                        data-logo-url="{{ $account->logo_url }}"
                        data-logo-text="{{ $account->logo_text }}"
                        data-bank-name="{{ $account->bank_name }}"
                        data-bank-code="{{ $account->code }}"
                        data-bank-balance="Rp {{ number_format($account->balance, 0, ',', '.') }}"
                        @selected(old('bank_account_id') == $account->id)>
                        {{ $account->bank_name }}
                    </option>
                    @endforeach
                </select>
                <div class="form-text" id="bankHint">Saat disetujui, hutang dicatat sebagai uang masuk ke bank ini. Saat bayar hutang nanti menjadi uang keluar.</div>
            </div>

            <div class="mb-5">
                <label class="form-label fw-semibold">Catatan</label>
                <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="form-label fw-semibold">Eviden</label>
                <input type="file" name="evidence[]" id="evidenceInput" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.xls,.xlsx">
                <div class="form-text">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</div>
            </div>

            <div id="evidencePreview" class="d-none border rounded p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold">Preview Eviden Baru</div>
                    <button type="button" class="btn btn-sm btn-light-danger" id="removeAllEvidenceBtn">Hapus Semua</button>
                </div>
                <div class="row g-3" id="evidencePreviewList"></div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('debt-receivables.index') }}" class="btn btn-light me-3"><i class="ki-duotone ki-cross fs-3"><span class="path1"></span><span class="path2"></span></i> Batal</a>
            <button class="btn btn-primary"><i class="ki-duotone ki-send fs-3"><span class="path1"></span><span class="path2"></span></i> Simpan & Ajukan</button>
        </div>
    </form>
</div>

<div class="modal fade" id="evidenceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h2 class="fw-bold">Preview Eviden</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
            <div class="modal-body" id="evidenceModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bank-select-option{display:flex;align-items:center;gap:10px;min-width:0}.bank-select-logo{width:32px;height:32px;border:1px solid #dfe6f2;border-radius:9px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}.bank-select-logo img{max-width:26px;max-height:18px;object-fit:contain}.bank-select-logo span{font-size:10px;font-weight:800;color:#1d4ed8}.bank-select-text{min-width:0;line-height:1.25}.bank-select-text .name{font-weight:700;color:#1f2937;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bank-select-text .meta{font-size:11px;color:#7e8299;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>
@endpush

@push('scripts')
<script>
var selectedEvidenceFiles = [];
var evidenceObjectUrls = [];

function bankOptionTemplate(option){if(!option.id)return option.text;var el=option.element;var logoUrl=el.getAttribute('data-logo-url');var logoText=el.getAttribute('data-logo-text')||'BNK';var bankName=el.getAttribute('data-bank-name')||option.text;var bankCode=el.getAttribute('data-bank-code')||'';var balance=el.getAttribute('data-bank-balance')||'';var logo=logoUrl?'<img src="'+logoUrl+'" alt="'+bankName.replace(/"/g,'&quot;')+'">':'<span>'+logoText+'</span>';return $('<div class="bank-select-option"><div class="bank-select-logo">'+logo+'</div><div class="bank-select-text"><div class="name">'+bankCode+' - '+bankName+'</div><div class="meta">Saldo '+balance+'</div></div></div>');}
function normalize(v){return(v||'').toString().replace(/\D/g,'').replace(/^0+(?=\d)/,'');}
function format(v){var d=normalize(v);return d?d.replace(/\B(?=(\d{3})+(?!\d))/g,'.'):'';}
function numberValue(id){return parseInt(document.getElementById(id).value || '0', 10) || 0;}
function bindMoney(display,value){var d=document.getElementById(display),v=document.getElementById(value);d.addEventListener('input',function(){var x=normalize(this.value);this.value=format(x);v.value=x||0;syncRemaining();});}
function syncRemaining(){var amount=numberValue('amountValue');var total=numberValue('totalValue')||amount;var paid=numberValue('paidValue');document.getElementById('remainingDisplay').value=format(Math.max(0,total-paid));}
function filterCategories(){var type=$('#typeSelect').val(),selected=$('#debtCategorySelect').val();$('#debtCategorySelect option[data-type]').each(function(){var itemType=$(this).data('type');var show=itemType==='both'||itemType===type;$(this).prop('hidden',!show);if(!show&&this.value===selected){$('#debtCategorySelect').val('');}});}
function syncTypeUi(){var isReceivable=$('#typeSelect').val()==='receivable';$('#partyLabel').text(isReceivable?'Nama Penerima Piutang':'Nama Pemberi Hutang');$('#partyName').attr('placeholder',isReceivable?'Nama pihak penerima piutang':'Nama pihak pemberi hutang');$('#amountLabel').text(isReceivable?'Nominal (Pinjam)':'Nominal (Pinjaman)');$('#paymentRow').toggleClass('d-none',isReceivable);if(isReceivable){$('#paidDisplay').val('0');$('#paidValue').val('0');}$('#bankHint').text(isReceivable?'Saat disetujui, piutang dicatat sebagai uang keluar dari bank ini. Saat bayar piutang nanti menjadi uang masuk.':'Saat disetujui, hutang dicatat sebagai uang masuk ke bank ini. Saat bayar hutang nanti menjadi uang keluar.');filterCategories();syncRemaining();}
bindMoney('amountDisplay','amountValue');bindMoney('totalDisplay','totalValue');bindMoney('paidDisplay','paidValue');
$('#transactionDate,#dueDate').flatpickr({dateFormat:'Y-m-d',allowInput:true});
$('.bank-account-select').select2({width:'100%',templateResult:bankOptionTemplate,templateSelection:bankOptionTemplate,escapeMarkup:function(markup){return markup;}});
$('#typeSelect').on('change',syncTypeUi);syncTypeUi();syncRemaining();

function syncEvidenceInput(){var dt=new DataTransfer();selectedEvidenceFiles.forEach(function(file){dt.items.add(file);});document.getElementById('evidenceInput').files=dt.files;}
function clearEvidencePreview(){evidenceObjectUrls.forEach(function(url){URL.revokeObjectURL(url);});evidenceObjectUrls=[];selectedEvidenceFiles=[];document.getElementById('evidenceInput').value='';document.getElementById('evidencePreviewList').innerHTML='';document.getElementById('evidenceModalBody').innerHTML='';document.getElementById('evidencePreview').classList.add('d-none');}
function removeEvidenceAt(index){selectedEvidenceFiles.splice(index,1);syncEvidenceInput();renderEvidencePreview();}
function formatFileSize(bytes){if(bytes<1024)return bytes+' B';if(bytes<1024*1024)return(bytes/1024).toFixed(1)+' KB';return(bytes/1024/1024).toFixed(1)+' MB';}
document.getElementById('evidenceInput').addEventListener('change',function(){selectedEvidenceFiles=Array.from(this.files||[]);renderEvidencePreview();});
function renderEvidencePreview(){evidenceObjectUrls.forEach(function(url){URL.revokeObjectURL(url);});evidenceObjectUrls=[];if(!selectedEvidenceFiles.length){clearEvidencePreview();return;}var list=document.getElementById('evidencePreviewList');list.innerHTML='';selectedEvidenceFiles.forEach(function(file,index){var objectUrl=URL.createObjectURL(file);evidenceObjectUrls.push(objectUrl);var ext=(file.name.split('.').pop()||'FILE').toUpperCase();var thumb=file.type.indexOf('image/')===0?'<img src="'+objectUrl+'" class="w-100 h-100" style="object-fit:cover;" alt="Preview eviden">':'<span class="badge badge-light-primary">'+ext+'</span>';var col=document.createElement('div');col.className='col-md-6 col-xl-4';col.innerHTML='<div class="border rounded p-3 h-100"><div class="border rounded bg-light d-flex align-items-center justify-content-center mb-3" style="height:120px;overflow:hidden;">'+thumb+'</div><div class="fw-semibold text-truncate" title="'+file.name+'">'+file.name+'</div><div class="text-muted fs-7 mb-3">'+formatFileSize(file.size)+'</div><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-light-primary evidence-view" data-index="'+index+'">Lihat</button><button type="button" class="btn btn-sm btn-light-danger evidence-remove" data-index="'+index+'">Hapus</button></div></div>';list.appendChild(col);});document.getElementById('evidencePreview').classList.remove('d-none');}
document.getElementById('evidencePreviewList').addEventListener('click',function(event){var viewButton=event.target.closest('.evidence-view');var removeButton=event.target.closest('.evidence-remove');if(removeButton){removeEvidenceAt(parseInt(removeButton.dataset.index,10));return;}if(!viewButton)return;var index=parseInt(viewButton.dataset.index,10);var file=selectedEvidenceFiles[index];var objectUrl=evidenceObjectUrls[index];if(!file||!objectUrl)return;document.getElementById('evidenceModalBody').innerHTML=file.type.indexOf('image/')===0?'<img src="'+objectUrl+'" class="w-100 rounded" alt="Preview eviden">':'<iframe src="'+objectUrl+'" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';new bootstrap.Modal(document.getElementById('evidenceModal')).show();});
document.getElementById('removeAllEvidenceBtn').addEventListener('click',clearEvidencePreview);
</script>
@endpush
