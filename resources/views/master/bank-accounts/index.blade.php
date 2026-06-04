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
.bank-summary-card{border:1px solid #e4e8f0;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.04)}
.bank-grid{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:14px}
@media (min-width:768px){.bank-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (min-width:1200px){.bank-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.bank-wallet-card{border:1px solid #e4e8f0;border-radius:12px;background:#fff;padding:16px;height:100%;transition:transform .15s ease,box-shadow .15s ease,border-color .15s ease}
.bank-wallet-card:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(15,23,42,.08);border-color:#cfd8e7}
.bank-logo-box{width:48px;height:48px;border:1px solid #e4e8f0;border-radius:10px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.bank-logo-box img{max-width:38px;max-height:26px;object-fit:contain}
.bank-logo-fallback{font-size:11px;font-weight:800;letter-spacing:.04em}
.bank-action-soft{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;border:0}
.bank-balance{font-size:20px;font-weight:700;line-height:1.2}
.bank-card-name{font-size:14px;font-weight:700;color:#061535;line-height:1.25;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bank-card-owner{font-size:12px;color:#667085;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px}
.bank-small-label{font-size:11px;letter-spacing:.03em}
.bank-mutasi-link{font-size:12px}
.bank-total-pill{border:1px solid #e4e8f0;border-radius:12px;padding:10px 14px;background:#f8fbff;text-align:right}
.bank-total-pill .label{font-size:11px;color:#667085;text-transform:uppercase;letter-spacing:.03em}
.bank-total-pill .value{font-size:18px;font-weight:800;color:#1d4ed8;line-height:1.2}
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
                    : (str_contains($bankName, 'CASH') ? asset('assets/media/logos.png') : null);
                $nomorRekening = preg_replace('/\s+/', '', (string) ($account->account_number ?? ''));
                $maskedRekening = $nomorRekening ? '**** ' . substr($nomorRekening, -4) : 'Nomor belum diisi';
                $balanceClass = (float) $account->balance < 0 ? 'text-danger' : ($palette[0] === '#00a884' ? 'text-success' : 'text-primary');
            @endphp
            <div class="bank-wallet-card">
                <div class="d-flex justify-content-between align-items-start mb-5">
                    <div class="d-flex align-items-center min-w-0">
                        <div class="bank-logo-box me-3">
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
                <div class="text-muted text-uppercase mb-2 bank-small-label">Saldo Tersedia</div>
                <div class="bank-balance {{ $balanceClass }}">Rp {{ number_format($account->balance, 0, ',', '.') }}</div>
                <div class="d-flex justify-content-between align-items-center mt-4">
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
@foreach($data as $i => $item)<tr><td>{{ $i+1 }}</td><td>{{ $item->code }}</td><td>{{ $item->bank_name }}</td><td>{{ $item->account_name ?? '-' }}</td><td>{{ $item->account_number ?? '-' }}</td><td>Rp {{ number_format($item->balance,0,',','.') }}</td><td><span class="badge {{ $item->is_active?'badge-light-success':'badge-light' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td><td class="text-end">
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
