@extends('layouts.app')

@section('title', 'Detail Hutang/Piutang')
@section('toolbar_actions')
<a href="{{ route('debt-receivables.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@can('debt-receivables.edit')
    @if($debtReceivable->status !== 'disetujui')
        <a href="{{ route('debt-receivables.edit',$debtReceivable) }}" class="btn btn-sm btn-warning"><i class="ki-duotone ki-pencil fs-3"></i> Edit</a>
    @endif
@endcan
@endsection

@section('content')
@php
    $isDebt = $debtReceivable->type === 'debt';
    $dueDate = $debtReceivable->due_date;
    $remainingAmount = (float) $debtReceivable->remaining_amount;
    $dueDiff = $dueDate ? now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false) : null;
    $dueConfig = $remainingAmount <= 0
        ? ['label' => 'Lunas', 'badge' => 'badge-light-success', 'text' => 'text-success', 'icon' => 'ki-check-circle', 'description' => 'Tidak ada sisa pembayaran.']
        : ($dueDate
            ? ($dueDiff < 0
                ? ['label' => 'Lewat Jatuh Tempo', 'badge' => 'badge-light-danger', 'text' => 'text-danger', 'icon' => 'ki-warning-2', 'description' => 'Terlambat ' . abs($dueDiff) . ' hari dari jatuh tempo.']
                : ($dueDiff === 0
                    ? ['label' => 'Jatuh Tempo Hari Ini', 'badge' => 'badge-light-warning', 'text' => 'text-warning', 'icon' => 'ki-time', 'description' => 'Pembayaran jatuh tempo hari ini.']
                    : ['label' => 'Belum Jatuh Tempo', 'badge' => 'badge-light-primary', 'text' => 'text-primary', 'icon' => 'ki-calendar', 'description' => $dueDiff . ' hari menuju jatuh tempo.']))
            : ['label' => 'Tanpa Jatuh Tempo', 'badge' => 'badge-light-secondary', 'text' => 'text-gray-600', 'icon' => 'ki-calendar', 'description' => 'Tanggal jatuh tempo belum diisi.']);
    $statusConfig = match($debtReceivable->status) {
        'disetujui' => ['label'=>'Disetujui','badge'=>'badge-light-success','bg'=>'bg-light-success','text'=>'text-success','icon'=>'ki-check-circle'],
        'ditolak' => ['label'=>'Ditolak','badge'=>'badge-light-danger','bg'=>'bg-light-danger','text'=>'text-danger','icon'=>'ki-cross-circle'],
        default => ['label'=>'Menunggu Approval','badge'=>'badge-light-warning','bg'=>'bg-light-warning','text'=>'text-warning','icon'=>'ki-time'],
    };
@endphp
<div class="purchase-hero mb-7"><div class="row g-0"><div class="col-xl-8 purchase-hero-main"><div class="d-flex align-items-start gap-4 mb-7"><div class="purchase-status-icon {{ $statusConfig['bg'] }}"><i class="ki-outline {{ $statusConfig['icon'] }} fs-1 {{ $statusConfig['text'] }}"></i></div><div><div class="purchase-number-pill mb-3">{{ $debtReceivable->transaction_number }}</div><div class="d-flex flex-wrap align-items-center gap-3 mb-2"><h1 class="fw-bolder fs-2 text-gray-900 mb-0">{{ $debtReceivable->activity }}</h1><span class="badge {{ $isDebt ? 'badge-light-danger' : 'badge-light-success' }}">{{ $isDebt ? 'Hutang' : 'Piutang' }}</span><span class="badge {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span><span class="badge {{ $dueConfig['badge'] }}"><i class="ki-outline {{ $dueConfig['icon'] }} me-1"></i>{{ $dueConfig['label'] }}</span></div><div class="text-gray-600">{{ $debtReceivable->party_name }} {{ $dueDate ? 'jatuh tempo ' . $dueDate->format('d/m/Y') : '' }}</div><div class="purchase-meta-line"><span class="purchase-meta-chip">{{ $debtReceivable->category ?? 'Tanpa kategori' }}</span><span class="purchase-meta-chip">{{ str($debtReceivable->payment_status)->replace('_',' ')->title() }}</span><span class="purchase-meta-chip {{ $dueConfig['text'] }}">{{ $dueConfig['description'] }}</span></div></div></div><div class="purchase-note-box"><div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Catatan</div><div class="fw-semibold">{{ $debtReceivable->notes ?: 'Tidak ada catatan tambahan.' }}</div></div>@if($debtReceivable->status==='ditolak')<div class="alert alert-danger mt-5">{{ $debtReceivable->rejection_reason }}</div>@endif</div><div class="col-xl-4"><div class="purchase-total-panel h-100 d-flex flex-column justify-content-between"><div class="position-relative"><div class="text-white-50 fs-8 text-uppercase fw-semibold mb-2">Sisa {{ $isDebt ? 'Hutang' : 'Piutang' }}</div><div class="fw-bolder fs-1 text-white">Rp {{ number_format($debtReceivable->remaining_amount,0,',','.') }}</div><div class="text-white-50 fs-8 mt-2">Total Rp {{ number_format($debtReceivable->total_amount,0,',','.') }} | Dibayar Rp {{ number_format($debtReceivable->paid_amount,0,',','.') }}</div></div><div class="position-relative mt-8"><div class="purchase-person-card mb-3"><div class="label">Diajukan Oleh</div><div class="value">{{ $debtReceivable->submitter?->name ?? '-' }}</div><div class="text-white-50 fs-8">{{ $debtReceivable->submitted_at?->format('d/m/Y H:i') ?? '-' }}</div></div><div class="purchase-person-card"><div class="label">Diproses Oleh</div><div class="value">{{ $debtReceivable->approver?->name ?? $debtReceivable->rejecter?->name ?? '-' }}</div><div class="text-white-50 fs-8">{{ ($debtReceivable->approved_at ?: $debtReceivable->rejected_at)?->format('d/m/Y H:i') ?? '-' }}</div></div></div></div></div></div></div>

@can('debt-receivables.approve')
@if($debtReceivable->status === 'menunggu_approval')
<div class="card card-flush purchase-section-card mb-7"><div class="card-body"><div class="row g-5"><div class="col-lg-5"><form method="POST" action="{{ route('debt-receivables.approve',$debtReceivable) }}">@csrf<button class="btn btn-success w-100">Approve</button></form></div><div class="col-lg-7"><form method="POST" action="{{ route('debt-receivables.reject',$debtReceivable) }}">@csrf<textarea name="rejection_reason" class="form-control mb-3" rows="3" required placeholder="Alasan reject"></textarea><button class="btn btn-danger">Reject</button></form></div></div></div></div>
@endif
@endcan

<div class="card card-flush purchase-section-card mb-7">
    <div class="card-header pt-6">
        <div class="card-title">
            <div>
                <h3 class="fw-bold mb-1">Eviden Awal</h3>
                <div class="text-muted fs-8">{{ $evidenceFiles->count() }} lampiran saat pembuatan hutang/piutang</div>
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        @if($evidenceFiles->isNotEmpty())
            <div class="row g-4">
                @foreach($evidenceFiles as $evidence)
                <div class="col-md-6 col-xl-4">
                    @if($evidence->is_image)
                        <a href="{{ $evidence->url }}" target="_blank" class="debt-evidence-tile d-block">
                            <div class="debt-evidence-frame mb-3"><img src="{{ $evidence->url }}" alt="{{ $evidence->name }}"></div>
                            <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                        </a>
                    @else
                        <div class="debt-evidence-tile">
                            <div class="debt-evidence-frame mb-3">
                                @if(str($evidence->name)->lower()->endsWith('.pdf'))
                                    <iframe src="{{ $evidence->url }}" class="w-100 h-100 border-0"></iframe>
                                @else
                                    <span class="badge badge-light-primary">{{ strtoupper(pathinfo($evidence->name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="fw-semibold text-gray-800 text-truncate">{{ $evidence->name }}</div>
                                <a href="{{ $evidence->url }}" target="_blank" class="btn btn-sm btn-light-primary">Buka</a>
                            </div>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-8">
                <i class="ki-outline ki-file-deleted fs-2x mb-3"></i>
                <div>Belum ada eviden awal.</div>
            </div>
        @endif
    </div>
</div>

@if($debtReceivable->status === 'disetujui' && $debtReceivable->remaining_amount > 0)
<div class="card card-flush purchase-section-card debt-payment-card mb-7">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-xl-4 debt-payment-aside">
                <div class="debt-payment-icon {{ $isDebt ? 'bg-light-danger' : 'bg-light-success' }}">
                    <i class="ki-outline {{ $isDebt ? 'ki-arrow-up' : 'ki-arrow-down' }} fs-1 {{ $isDebt ? 'text-danger' : 'text-success' }}"></i>
                </div>
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-2">{{ $isDebt ? 'Pembayaran Hutang' : 'Penerimaan Piutang' }}</div>
                <h3 class="fw-bolder text-gray-900 mb-3">{{ $isDebt ? 'Bayar Hutang' : 'Terima Piutang' }}</h3>
                <div class="text-gray-600 mb-6">{{ $isDebt ? 'Transaksi ini akan mengurangi saldo bank.' : 'Transaksi ini akan menambah saldo bank.' }}</div>
                <div class="debt-payment-balance">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Sisa {{ $isDebt ? 'Hutang' : 'Piutang' }}</div>
                    <div class="fw-bolder fs-2 {{ $isDebt ? 'text-danger' : 'text-success' }}">Rp {{ number_format($debtReceivable->remaining_amount,0,',','.') }}</div>
                    <div class="text-muted fs-8 mt-1">{{ $dueConfig['label'] }} - {{ $dueDate?->format('d/m/Y') ?? '-' }}</div>
                </div>
            </div>
            <div class="col-xl-8">
                <form method="POST" action="{{ route('debt-receivables.pay',$debtReceivable) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="p-7">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="required form-label fw-semibold">Tanggal Pembayaran</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ki-duotone ki-calendar fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                    <input name="payment_date" id="paymentDate" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="required form-label fw-semibold">Nominal</label>
                                <div class="input-group"><span class="input-group-text">Rp</span><input id="payDisplay" class="form-control" inputmode="numeric" value="{{ number_format($debtReceivable->remaining_amount,0,',','.') }}" required></div>
                                <input type="hidden" name="amount" id="payValue" value="{{ (int) $debtReceivable->remaining_amount }}">
                            </div>
                            <div class="col-md-6">
                                <label class="required form-label fw-semibold">Account Bank</label>
                                <select name="bank_account_id" class="form-select" required>
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}" @selected($debtReceivable->bank_account_id === $bank->id)>{{ $bank->code }} - {{ $bank->bank_name }} (Rp {{ number_format($bank->balance,0,',','.') }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ $isDebt ? 'Bayar hutang = uang keluar.' : 'Bayar piutang = uang masuk.' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Eviden</label>
                                <input type="file" name="evidence[]" id="paymentEvidenceInput" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
                                <div class="form-text">Format JPG, PNG, WebP, atau PDF. Maksimal 4 MB per file.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan Pembayaran</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Catatan pembayaran">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div id="paymentEvidencePreview" class="d-none border rounded p-3 mt-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">Preview Eviden Pembayaran</div>
                                <button type="button" class="btn btn-sm btn-light-danger" id="removeAllPaymentEvidenceBtn">Hapus Semua</button>
                            </div>
                            <div class="row g-3" id="paymentEvidencePreviewList"></div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end">
                        <button class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-3"><span class="path1"></span><span class="path2"></span></i>
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card card-flush purchase-section-card">
    <div class="card-header pt-6"><h3 class="fw-bold">Riwayat Pembayaran</h3></div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Bank</th>
                        <th>Nominal</th>
                        <th>Eviden</th>
                        <th>Catatan</th>
                        <th>Input Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debtReceivable->payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $payment->payment_date?->format('d/m/Y') }}</td>
                        <td>{{ $payment->bankAccount?->code ?? '-' }}</td>
                        <td class="fw-bold {{ $isDebt ? 'text-danger' : 'text-success' }}">{{ $isDebt ? '-' : '+' }} Rp {{ number_format($payment->amount,0,',','.') }}</td>
                        <td>
                            @php
                                $paymentEvidencePaths = collect($payment->evidence_paths ?: []);
                            @endphp
                            @if($paymentEvidencePaths->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($paymentEvidencePaths as $path)
                                        @php
                                            $url = Storage::disk('r2')->url($path);
                                            $isImage = in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
                                        @endphp
                                        <button type="button" class="stored-evidence-thumb" data-url="{{ $url }}" data-type="{{ $isImage ? 'image' : 'file' }}" title="{{ basename($path) }}">
                                            @if($isImage)
                                                <img src="{{ $url }}" alt="{{ basename($path) }}">
                                            @else
                                                <span>{{ strtoupper(pathinfo($path, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                        <td>{{ $payment->creator?->name ?? '-' }}</td>
                        <td class="text-end">
                            <div class="gh-action-group justify-content-end">
                                @can('debt-receivables.edit')
                                <button type="button" class="gh-action-btn gh-action-edit" data-bs-toggle="modal" data-bs-target="#editPaymentModal{{ $payment->id }}" title="Edit">
                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                @endcan
                                @can('debt-receivables.delete')
                                <form method="POST" action="{{ route('debt-receivables.payments.destroy', [$debtReceivable, $payment]) }}" class="d-inline" onsubmit="return confirm('Hapus pembayaran ini? Saldo bank dan mutasi terkait akan dibalik.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="gh-action-btn gh-action-delete" title="Hapus">
                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-8">Belum ada pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($debtReceivable->payments as $payment)
@can('debt-receivables.edit')
<div class="modal fade" id="editPaymentModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Edit Pembayaran</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div>
            </div>
            <form method="POST" action="{{ route('debt-receivables.payments.update', [$debtReceivable, $payment]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="required form-label fw-semibold">Tanggal</label>
                            <input name="payment_date" class="form-control edit-payment-date" value="{{ $payment->payment_date?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label fw-semibold">Nominal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input class="form-control edit-payment-display" inputmode="numeric" value="{{ number_format($payment->amount, 0, ',', '.') }}" required>
                            </div>
                            <input type="hidden" name="amount" class="edit-payment-value" value="{{ (int) $payment->amount }}">
                        </div>
                        <div class="col-md-12">
                            <label class="required form-label fw-semibold">Account Bank</label>
                            <select name="bank_account_id" class="form-select" required>
                                @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}" @selected($payment->bank_account_id === $bank->id)>{{ $bank->code }} - {{ $bank->bank_name }} (Rp {{ number_format($bank->balance,0,',','.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ganti Eviden</label>
                            <input type="file" name="evidence[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf">
                            <div class="form-text">Kosongkan jika eviden lama tetap dipakai.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $payment->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endforeach

<div class="modal fade" id="paymentEvidenceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Preview Eviden Pembayaran</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></div>
            </div>
            <div class="modal-body" id="paymentEvidenceModalBody"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.purchase-hero{border:1px solid #e4e8f0;border-radius:20px;overflow:hidden;box-shadow:0 16px 42px rgba(15,23,42,.06);background:#fff}.purchase-hero-main{background:linear-gradient(135deg,#f8fbff 0%,#fff 62%);padding:28px}.purchase-status-icon{width:62px;height:62px;border-radius:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}.purchase-number-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #dfe6f2;background:#fff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:700;color:#334155}.purchase-meta-line{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.purchase-meta-chip{display:inline-flex;align-items:center;border:1px solid #e4e8f0;background:#fff;border-radius:999px;padding:8px 12px;color:#475569;font-size:12px;font-weight:600}.purchase-note-box{border:1px dashed #d8e1ef;border-radius:14px;background:#fff;padding:18px}.purchase-total-panel{padding:28px;background:linear-gradient(155deg,#0f172a,#1e293b);position:relative;overflow:hidden}.purchase-person-card{border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px;background:rgba(255,255,255,.06);position:relative}.purchase-person-card .label{color:#cbd5e1;font-size:11px;text-transform:uppercase;letter-spacing:.02em}.purchase-person-card .value{color:#fff;font-weight:700}.purchase-section-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045)}.debt-payment-card{overflow:hidden}.debt-payment-aside{padding:28px;background:linear-gradient(135deg,#f8fbff 0%,#fff 72%);border-right:1px solid #e4e8f0}.debt-payment-icon{width:58px;height:58px;border-radius:18px;display:flex;align-items:center;justify-content:center;margin-bottom:18px}.debt-payment-balance{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:18px}.debt-evidence-tile{border:1px solid #e4e8f0;border-radius:16px;background:#fff;padding:12px;height:100%;transition:transform .15s ease,box-shadow .15s ease}.debt-evidence-tile:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(15,23,42,.08)}.debt-evidence-frame{height:190px;border-radius:12px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center}.debt-evidence-frame img{width:100%;height:100%;object-fit:contain}.stored-evidence-thumb{width:46px;height:46px;border:1px solid #dfe6f2;border-radius:12px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden;padding:0}.stored-evidence-thumb img{width:100%;height:100%;object-fit:cover}.stored-evidence-thumb span{font-size:10px;font-weight:800;color:#1d4ed8}
</style>
@endpush

@push('scripts')
<script>
function normalize(v){return(v||'').toString().replace(/\D/g,'').replace(/^0+(?=\d)/,'');}function format(v){var d=normalize(v);return d?d.replace(/\B(?=(\d{3})+(?!\d))/g,'.'):'';}var pd=document.getElementById('payDisplay'),pv=document.getElementById('payValue');if(pd&&pv){pd.addEventListener('input',function(){var x=normalize(this.value);this.value=format(x);pv.value=x||0;});}$('#paymentDate').flatpickr({dateFormat:'Y-m-d',altInput:true,altFormat:'d/m/Y'});
var paymentEvidenceFiles=[];var paymentEvidenceObjectUrls=[];var paymentInput=document.getElementById('paymentEvidenceInput');
function syncPaymentEvidenceInput(){var dt=new DataTransfer();paymentEvidenceFiles.forEach(function(file){dt.items.add(file);});paymentInput.files=dt.files;}
function clearPaymentEvidencePreview(){paymentEvidenceObjectUrls.forEach(function(url){URL.revokeObjectURL(url);});paymentEvidenceObjectUrls=[];paymentEvidenceFiles=[];if(paymentInput)paymentInput.value='';var list=document.getElementById('paymentEvidencePreviewList');var body=document.getElementById('paymentEvidenceModalBody');if(list)list.innerHTML='';if(body)body.innerHTML='';var box=document.getElementById('paymentEvidencePreview');if(box)box.classList.add('d-none');}
function removePaymentEvidenceAt(index){paymentEvidenceFiles.splice(index,1);syncPaymentEvidenceInput();renderPaymentEvidencePreview();}
function formatFileSize(bytes){if(bytes<1024)return bytes+' B';if(bytes<1024*1024)return(bytes/1024).toFixed(1)+' KB';return(bytes/1024/1024).toFixed(1)+' MB';}
function renderPaymentEvidencePreview(){paymentEvidenceObjectUrls.forEach(function(url){URL.revokeObjectURL(url);});paymentEvidenceObjectUrls=[];if(!paymentEvidenceFiles.length){clearPaymentEvidencePreview();return;}var list=document.getElementById('paymentEvidencePreviewList');list.innerHTML='';paymentEvidenceFiles.forEach(function(file,index){var objectUrl=URL.createObjectURL(file);paymentEvidenceObjectUrls.push(objectUrl);var thumb=file.type.indexOf('image/')===0?'<img src="'+objectUrl+'" class="w-100 h-100" style="object-fit:cover;" alt="Preview eviden">':'<span class="badge badge-light-primary">'+((file.name.split('.').pop()||'FILE').toUpperCase())+'</span>';var col=document.createElement('div');col.className='col-md-6 col-xl-4';col.innerHTML='<div class="border rounded p-3 h-100"><div class="border rounded bg-light d-flex align-items-center justify-content-center mb-3" style="height:120px;overflow:hidden;">'+thumb+'</div><div class="fw-semibold text-truncate" title="'+file.name+'">'+file.name+'</div><div class="text-muted fs-7 mb-3">'+formatFileSize(file.size)+'</div><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-light-primary payment-evidence-view" data-index="'+index+'">Lihat</button><button type="button" class="btn btn-sm btn-light-danger payment-evidence-remove" data-index="'+index+'">Hapus</button></div></div>';list.appendChild(col);});document.getElementById('paymentEvidencePreview').classList.remove('d-none');}
if(paymentInput){paymentInput.addEventListener('change',function(){paymentEvidenceFiles=Array.from(this.files||[]);renderPaymentEvidencePreview();});document.getElementById('paymentEvidencePreviewList').addEventListener('click',function(event){var viewButton=event.target.closest('.payment-evidence-view');var removeButton=event.target.closest('.payment-evidence-remove');if(removeButton){removePaymentEvidenceAt(parseInt(removeButton.dataset.index,10));return;}if(!viewButton)return;var index=parseInt(viewButton.dataset.index,10);var file=paymentEvidenceFiles[index];var objectUrl=paymentEvidenceObjectUrls[index];if(!file||!objectUrl)return;document.getElementById('paymentEvidenceModalBody').innerHTML=file.type.indexOf('image/')===0?'<img src="'+objectUrl+'" class="w-100 rounded" alt="Preview eviden">':'<iframe src="'+objectUrl+'" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';new bootstrap.Modal(document.getElementById('paymentEvidenceModal')).show();});document.getElementById('removeAllPaymentEvidenceBtn').addEventListener('click',clearPaymentEvidencePreview);}
document.querySelectorAll('.stored-evidence-thumb').forEach(function(button){button.addEventListener('click',function(){var url=this.dataset.url;var type=this.dataset.type;document.getElementById('paymentEvidenceModalBody').innerHTML=type==='image'?'<img src="'+url+'" class="w-100 rounded" alt="Eviden pembayaran">':'<iframe src="'+url+'" class="w-100 border-0 rounded" style="height:75vh;"></iframe>';new bootstrap.Modal(document.getElementById('paymentEvidenceModal')).show();});});
$('.edit-payment-date').flatpickr({dateFormat:'Y-m-d',altInput:true,altFormat:'d/m/Y'});
document.querySelectorAll('.edit-payment-display').forEach(function(display){display.addEventListener('input',function(){var x=normalize(this.value);this.value=format(x);this.closest('.input-group').parentElement.querySelector('.edit-payment-value').value=x||0;});});
</script>
@endpush
