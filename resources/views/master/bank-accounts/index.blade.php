@extends('layouts.app')

@section('title', 'Account Bank')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Master Data</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Account Bank</li>
@endsection

@section('toolbar_actions')
@can('bank-accounts.edit')<button class="btn btn-sm btn-light-primary" onclick="openTransferModal()"><i class="ki-duotone ki-arrow-right-left fs-3"><span class="path1"></span><span class="path2"></span></i> Transfer Saldo</button>@endcan
@can('bank-accounts.create')<button class="btn btn-sm btn-primary" onclick="openCreateModal()"><i class="ki-duotone ki-plus-square fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tambah Account</button>@endcan
@endsection

@push('styles')
<style>
.bank-summary-card{border:1px solid #e4e8f0;border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.05)}
.bank-grid{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:18px}
@media (min-width:768px){.bank-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (min-width:1200px){.bank-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.bank-wallet-card{position:relative;min-height:218px;border:1px solid #e4e8f0;border-radius:14px;background:#fff;padding:18px;height:100%;overflow:hidden;transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease}
.bank-wallet-card::after{content:"";position:absolute;width:118px;height:118px;right:-56px;top:-58px;border-radius:999px;background:#f5f9ff}
.bank-wallet-card:hover{transform:translateY(-2px);box-shadow:0 16px 34px rgba(15,23,42,.08);border-color:#d8e1ef}
.bank-card-head{position:relative;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}
.bank-card-identity{display:flex;align-items:center;gap:12px;min-width:0;flex:1 1 auto}
.bank-logo-box{width:54px;height:54px;border:1px solid #e4e8f0;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 54px}
.bank-logo-box img{max-width:42px;max-height:30px;object-fit:contain}
.bank-logo-fallback{font-size:12px;font-weight:750;letter-spacing:.03em}
.bank-action-soft{position:relative;z-index:1;width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;border:0}
.bank-balance-panel{position:relative;z-index:1;border:1px solid #edf1f7;border-radius:12px;background:#fbfdff;padding:13px;margin-top:8px}
.bank-balance-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}
.bank-balance{font-size:22px;font-weight:650;line-height:1.2;overflow-wrap:anywhere}
.bank-balance.is-plus{color:#00a884}
.bank-balance.is-minus{color:#1d4ed8}
.bank-balance-badge{display:inline-flex;align-items:center;min-height:24px;border-radius:8px;padding:4px 8px;font-size:11px;font-weight:650}
.bank-balance-badge.is-plus{background:#dcfce7;color:#00a884}
.bank-balance-badge.is-minus{background:#e8f3ff;color:#1d4ed8}
.bank-card-name{font-size:15px;font-weight:700;color:#061535;line-height:1.25;max-width:100%;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;word-break:break-word}
.bank-card-owner{font-size:12px;font-weight:500;color:#667085;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;text-transform:uppercase;margin-top:3px}
.bank-small-label{font-size:11px;font-weight:650;letter-spacing:.03em}
.bank-card-footer{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px}
.bank-mutasi-link{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:650}
.bank-mutasi-link i{color:currentColor!important}
.bank-total-pill{border:1px solid #e4e8f0;border-radius:12px;padding:10px 14px;background:#f8fbff;text-align:right}
.bank-total-pill .label{font-size:11px;color:#667085;text-transform:uppercase;letter-spacing:.03em}
.bank-total-pill .value{font-size:18px;font-weight:650;color:#1d4ed8;line-height:1.2}
.bank-table-bank{display:flex;align-items:center;gap:10px;min-width:190px}
.bank-table-logo{width:38px;height:38px;border:1px solid #e4e8f0;border-radius:9px;background:#fff;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;flex:0 0 38px}
.bank-table-logo img{max-width:30px;max-height:22px;object-fit:contain}
.bank-table-logo span{font-size:10px;font-weight:750;color:#1682ff;letter-spacing:.03em}
.bank-table-name{color:#061535;font-weight:650;line-height:1.25;max-width:220px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;word-break:break-word}
.select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered{line-height:1.5}
</style>
@endpush

@section('content')
@if($errors->any())
<div class="alert alert-danger d-flex align-items-center mb-5">
    <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div>{{ $errors->first() }}</div>
</div>
@endif

<div class="card card-flush bank-summary-card mb-6">
    <div class="card-header border-0 pt-6">
        @php
            $totalActiveBalance = $data->where('is_active', true)->sum('balance');
            $totalAllBalance = $data->sum('balance');
        @endphp
        <div class="card-title d-block">
            <h3 class="fw-bold mb-1">Saldo Rekening</h3>
            <div class="text-muted fs-8">Saldo aktif Rp {{ number_format($totalActiveBalance, 0, ',', '.') }}</div>
        </div>
        <div class="card-toolbar gap-3">
            <div class="bank-total-pill">
                <div class="label">Total Saldo Gabungan</div>
                <div class="value">Rp {{ number_format($totalAllBalance, 0, ',', '.') }}</div>
            </div>
            @can('bank-accounts.create')
            <button class="btn btn-sm btn-light-primary" onclick="openCreateModal()">Kelola Rekening <i class="ki-duotone ki-arrow-right fs-4"></i></button>
            @endcan
        </div>
    </div>
    <div class="card-body pt-0">
        @if($data->count())
        <div class="bank-grid">
            @foreach($data as $account)
            @php
                $bankName = strtoupper($account->bank_name ?? 'BANK');
                $palette = str_contains($bankName, 'BCA') ? ['#1d4ed8', '#dbeafe', '#ffffff'] :
                    (str_contains($bankName, 'BRI') ? ['#00529c', '#dbeafe', '#ffffff'] :
                    (str_contains($bankName, 'MANDIRI') ? ['#f6b100', '#fff7d6', '#111827'] :
                    (str_contains($bankName, 'BNI') ? ['#f97316', '#ffedd5', '#ffffff'] :
                    (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA') ? ['#00a884', '#dcfce7', '#ffffff'] :
                    (str_contains($bankName, 'CASH') ? ['#6b7280', '#f3f4f6', '#ffffff'] : ['#2563eb', '#eff6ff', '#ffffff'])))));
                $bankWords = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $account->bank_name ?? 'Bank')))->filter();
                $bankLogo = $bankWords->count() > 1
                    ? $bankWords->map(fn($word) => substr($word, 0, 1))->take(3)->implode('')
                    : strtoupper(substr($account->bank_name ?? 'B', 0, 3));
                $bankLogoFile = str_contains($bankName, 'BCA DIGITAL') ? 'BCA Digital logo.svg' :
                    (str_contains($bankName, 'BCA SYARIAH') ? 'BCA Syariah.svg' :
                    (str_contains($bankName, 'BCA') ? 'Bank Central Asia.svg' :
                    (str_contains($bankName, 'BRI') ? 'BRI 2020.svg' :
                    (str_contains($bankName, 'BNI') ? 'Bank Negara Indonesia logo (2004).svg' :
                    (str_contains($bankName, 'MANDIRI') ? 'Bank Mandiri logo 2016.svg' :
                    (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA') ? 'Bank Syariah Indonesia.svg' :
                    (str_contains($bankName, 'BTN') ? 'Bank BTN logo.svg' :
                    (str_contains($bankName, 'CIMB') ? 'CIMB Niaga logo.svg' :
                    (str_contains($bankName, 'DANAMON') ? 'Danamon.svg' :
                    (str_contains($bankName, 'MEGA') ? 'Bank Mega 2013.svg' :
                    (str_contains($bankName, 'PERMATA') ? 'Permata Bank (2024).svg' :
                    (str_contains($bankName, 'PANIN') ? 'Logo Panin Bank.svg' :
                    (str_contains($bankName, 'JAGO') ? 'Logo-jago.svg' :
                    (str_contains($bankName, 'SEABANK') || str_contains($bankName, 'SEA BANK') ? 'SeaBank.svg' :
                    (str_contains($bankName, 'UOB') ? 'UOB Logo (2022).svg' :
                    (str_contains($bankName, 'DKI') ? 'Bank DKI.svg' : null))))))))))))))));
                $bankLogoUrl = $bankLogoFile
                    ? 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($bankLogoFile) . '?width=160'
                    : (str_contains($bankName, 'CASH') ? asset('assets/media/favicon.png') : null);
                $nomorRekening = preg_replace('/\s+/', '', (string) ($account->account_number ?? ''));
                $maskedRekening = $nomorRekening ? '**** ' . substr($nomorRekening, -4) : 'Nomor belum diisi';
                $isMinus = (float) $account->balance < 0;
            @endphp
            <div class="bank-wallet-card">
                <div class="bank-card-head">
                    <div class="bank-card-identity">
                        <div class="bank-logo-box">
                            @if($bankLogoUrl)
                                <img src="{{ $bankLogoUrl }}" alt="{{ $account->bank_name }}">
                            @else
                                <span class="bank-logo-fallback" style="color:{{ $palette[0] }}">{{ $bankLogo }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="bank-card-name" title="{{ $account->bank_name }}">{{ $account->bank_name }}</div>
                            <div class="bank-card-owner" title="{{ $account->account_name ?? '-' }}">{{ $account->account_name ?? '-' }}</div>
                        </div>
                    </div>
                    @can('bank-accounts.edit')
                    <button class="bank-action-soft" style="background:{{ $palette[1] }};color:{{ $palette[0] }}" onclick="openEditModal('{{ $account->id }}')" title="Edit rekening">
                        <i class="ki-duotone ki-bank fs-3"><span class="path1"></span><span class="path2"></span></i>
                    </button>
                    @endcan
                </div>
                <div class="bank-balance-panel">
                    <div class="bank-balance-row">
                        <div class="text-muted text-uppercase bank-small-label">Saldo Tersedia</div>
                        <span class="bank-balance-badge {{ $isMinus ? 'is-minus' : 'is-plus' }}">{{ $isMinus ? 'Minus' : ($account->is_active ? 'Aktif' : 'Nonaktif') }}</span>
                    </div>
                    <div class="bank-balance {{ $isMinus ? 'is-minus' : 'is-plus' }}">Rp {{ number_format($account->balance, 0, ',', '.') }}</div>
                </div>
                <div class="bank-card-footer">
                    <span class="text-muted fs-8">{{ $maskedRekening }}</span>
                    <a href="{{ route('bank-accounts.show', $account) }}" class="fw-semibold bank-mutasi-link" style="color:{{ $palette[0] }}">Mutasi <i class="ki-duotone ki-arrow-right fs-5"></i></a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-muted py-10">Belum ada account bank.</div>
        @endif
    </div>
</div>

<div class="card card-flush"><div class="card-header border-0 pt-6"><div class="card-title"><span class="text-gray-600">Daftar rekening dan saldo saat ini.</span></div><div class="card-toolbar position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari account..."></div></div>
<div class="card-body pt-0"><table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded"><thead><tr class="fw-semibold fs-6 text-gray-800"><th>No</th><th>Kode</th><th>Bank / Cash</th><th>Pemilik</th><th>No. Rekening</th><th>Saldo</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
@foreach($data as $i => $item)
@php
    $tableBankName = strtoupper($item->bank_name ?? 'BANK');
    $tableBankWords = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $item->bank_name ?? 'Bank')))->filter();
    $tableBankLogo = $tableBankWords->count() > 1
        ? $tableBankWords->map(fn($word) => substr($word, 0, 1))->take(3)->implode('')
        : strtoupper(substr($item->bank_name ?? 'B', 0, 3));
    $tableBankLogoFile = str_contains($tableBankName, 'BCA DIGITAL') ? 'BCA Digital logo.svg' :
        (str_contains($tableBankName, 'BCA SYARIAH') ? 'BCA Syariah.svg' :
        (str_contains($tableBankName, 'BCA') ? 'Bank Central Asia.svg' :
        (str_contains($tableBankName, 'BRI') ? 'BRI 2020.svg' :
        (str_contains($tableBankName, 'BNI') ? 'Bank Negara Indonesia logo (2004).svg' :
        (str_contains($tableBankName, 'MANDIRI') ? 'Bank Mandiri logo 2016.svg' :
        (str_contains($tableBankName, 'BSI') || str_contains($tableBankName, 'SYARIAH INDONESIA') ? 'Bank Syariah Indonesia.svg' :
        (str_contains($tableBankName, 'BTN') ? 'Bank BTN logo.svg' :
        (str_contains($tableBankName, 'CIMB') ? 'CIMB Niaga logo.svg' :
        (str_contains($tableBankName, 'DANAMON') ? 'Danamon.svg' :
        (str_contains($tableBankName, 'MEGA') ? 'Bank Mega 2013.svg' :
        (str_contains($tableBankName, 'PERMATA') ? 'Permata Bank (2024).svg' :
        (str_contains($tableBankName, 'PANIN') ? 'Logo Panin Bank.svg' :
        (str_contains($tableBankName, 'JAGO') ? 'Logo-jago.svg' :
        (str_contains($tableBankName, 'SEABANK') || str_contains($tableBankName, 'SEA BANK') ? 'SeaBank.svg' :
        (str_contains($tableBankName, 'UOB') ? 'UOB Logo (2022).svg' :
        (str_contains($tableBankName, 'DKI') ? 'Bank DKI.svg' : null))))))))))))))));
    $tableBankLogoUrl = $tableBankLogoFile
        ? 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($tableBankLogoFile) . '?width=120'
        : (str_contains($tableBankName, 'CASH') ? asset('assets/media/favicon.png') : null);
    $tableBalance = (float) $item->balance;
@endphp
<tr><td>{{ $i+1 }}</td><td>{{ $item->code }}</td><td><div class="bank-table-bank"><div class="bank-table-logo">@if($tableBankLogoUrl)<img src="{{ $tableBankLogoUrl }}" alt="{{ $item->bank_name }}">@else<span>{{ $tableBankLogo }}</span>@endif</div><div class="bank-table-name" title="{{ $item->bank_name }}">{{ $item->bank_name }}</div></div></td><td>{{ $item->account_name ?? '-' }}</td><td>{{ $item->account_number ?? '-' }}</td><td class="{{ $tableBalance < 0 ? 'text-danger' : 'text-gray-900' }} fw-semibold">Rp {{ number_format($item->balance,0,',','.') }}</td><td><span class="badge {{ $item->is_active?'badge-light-success':'badge-light' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td class="text-end">
<a class="btn btn-icon btn-sm btn-info" href="{{ route('bank-accounts.show',$item) }}"><i class="ki-duotone ki-eye fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>
@can('bank-accounts.edit')<button class="btn btn-icon btn-sm btn-warning" onclick="openEditModal('{{ $item->id }}')"><i class="ki-duotone ki-pencil fs-3 text-white"><span class="path1"></span><span class="path2"></span></i></button>@endcan
@can('bank-accounts.delete')<button class="btn btn-icon btn-sm btn-danger" onclick="deleteItem('{{ $item->id }}',@js($item->bank_name))"><i class="ki-duotone ki-trash fs-3 text-white"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>@endcan
</td></tr>@endforeach
</tbody></table></div></div>

<div class="modal fade" id="formModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content"><div class="modal-header"><h2 id="modalTitle">Tambah Account</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
<form id="dataForm"><div class="modal-body mx-5 my-7"><div id="formErrors" class="alert alert-danger d-none"><ul class="mb-0"></ul></div>
<div class="row g-5"><div class="col-md-6"><label class="required form-label">Kode Account</label><input name="code" id="f_code" class="form-control" required></div><div class="col-md-6"><label class="required form-label">Nama Bank / Cash</label><select name="bank_name" id="f_bank_name" class="form-select" required><option value="">-- Pilih / Ketik Bank --</option>@foreach($banks as $bank)<option value="{{ $bank }}">{{ $bank }}</option>@endforeach</select><div class="form-text">Ketik nama bank baru jika tidak ada di daftar.</div></div>
<div class="col-md-6"><label class="form-label">Nama Pemilik</label><input name="account_name" id="f_account_name" class="form-control"></div><div class="col-md-6"><label class="form-label">Nomor Rekening</label><input name="account_number" id="f_account_number" class="form-control"></div>
<div class="col-md-6"><label class="required form-label">Saldo</label><input type="number" min="0" name="balance" id="f_balance" class="form-control" required></div><div class="col-md-6"><label class="required form-label">Status</label><select name="is_active" id="f_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
<div class="col-12 d-none" id="balanceDescriptionWrap"><label class="form-label">Keterangan Perubahan Saldo</label><textarea name="balance_description" id="f_balance_description" class="form-control" rows="2"></textarea></div></div>
</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div></div>

<div class="modal fade" id="transferModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered mw-650px"><div class="modal-content"><div class="modal-header"><h2>Transfer Saldo</h2><div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div></div>
<form method="POST" action="{{ route('bank-accounts.transfer') }}">@csrf<div class="modal-body mx-5 my-7"><div class="row g-5"><div class="col-md-6"><label class="required form-label">Dari Account</label><select name="from_bank_account_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($data->where('is_active',true) as $a)<option value="{{ $a->id }}">{{ $a->bank_name }} - Rp {{ number_format($a->balance,0,',','.') }}</option>@endforeach</select></div><div class="col-md-6"><label class="required form-label">Ke Account</label><select name="to_bank_account_id" class="form-select" required><option value="">-- Pilih --</option>@foreach($data->where('is_active',true) as $a)<option value="{{ $a->id }}">{{ $a->bank_name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="required form-label">Tanggal</label><input type="date" name="transfer_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div><div class="col-md-6"><label class="required form-label">Nominal</label><input type="number" min="1" name="amount" class="form-control" required></div><div class="col-12"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Transfer</button></div></form></div></div></div>
@endsection

@push('scripts')
<script>
var editId=null;var table=$('#kt_table').DataTable({order:[],columnDefs:[{orderable:false,targets:[0,7]}]});$('#searchInput').on('keyup',function(){table.search(this.value).draw();});
$('#f_bank_name').select2({tags:true,width:'100%',dropdownParent:$('#formModal'),placeholder:'-- Pilih / Ketik Bank --'});
function modal(id){return new bootstrap.Modal(document.getElementById(id));} function errors(d){var b=$('#formErrors');b.addClass('d-none').find('ul').empty();if(d.errors){Object.values(d.errors).flat().forEach(x=>b.find('ul').append($('<li>').text(x)));b.removeClass('d-none');}}
function openCreateModal(){editId=null;$('#modalTitle').text('Tambah Account');$('#dataForm')[0].reset();$('#f_bank_name').val(null).trigger('change');$('#balanceDescriptionWrap').addClass('d-none');errors({});modal('formModal').show();}
function openEditModal(id){fetch('/master/bank-accounts/'+id+'/edit').then(r=>r.json()).then(d=>{editId=id;$('#modalTitle').text('Edit Account');$('#f_code').val(d.code);if($('#f_bank_name option[value="'+d.bank_name+'"]').length===0){$('#f_bank_name').append(new Option(d.bank_name,d.bank_name,true,true));}$('#f_bank_name').val(d.bank_name).trigger('change');$('#f_account_name').val(d.account_name);$('#f_account_number').val(d.account_number);$('#f_balance').val(d.balance);$('#f_active').val(d.is_active?1:0);$('#f_balance_description').val('');$('#balanceDescriptionWrap').removeClass('d-none');errors({});modal('formModal').show();});}
function openTransferModal(){modal('transferModal').show();}
$('#dataForm').on('submit',function(e){e.preventDefault();var fd=new FormData(this);if(editId)fd.append('_method','PUT');fetch(editId?'/master/bank-accounts/'+editId:'{{ route('bank-accounts.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'},body:fd}).then(r=>r.json()).then(d=>d.success?location.reload():errors(d));});
function deleteItem(id,name){Swal.fire({title:'Hapus account?',text:name,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'}).then(x=>{if(x.isConfirmed)fetch('/master/bank-accounts/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':$('meta[name=csrf-token]').attr('content'),'Accept':'application/json'}}).then(r=>r.json()).then(d=>d.success?location.reload():Swal.fire('Gagal',d.message,'error'));});}
</script>
@endpush
