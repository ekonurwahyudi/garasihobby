@extends('layouts.app')

@section('title', 'Revenue Sharing')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Keuangan</li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">Revenue Sharing</li>
@endsection

@section('toolbar_actions')
@can('revenue-sharings.create')
<button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#cutoffModal"><i class="ki-duotone ki-calendar-tick fs-3"><span class="path1"></span><span class="path2"></span></i> Cut Off Revenue</button>
@endcan
@endsection

@section('content')
@php
    $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F')]);
@endphp
<div class="d-flex align-items-center justify-content-between mb-5">
    <div>
        <h2 class="fw-bold text-gray-900 mb-1">Data Cut Off Revenue</h2>
        <div class="text-muted">Setiap cut off tersimpan di database dan bisa dipakai untuk sharing revenue.</div>
    </div>
</div>

@if($cutoffs->isNotEmpty())
<div class="row g-6 mb-7">
    @foreach($cutoffs as $cutoff)
        @php
            $usedPercentage = (float) $cutoff->activeSharings->sum('sharing_percentage');
            $remainingPercentage = max(0, 100 - $usedPercentage);
            $sharingUrl = route('revenue-sharings.create', [
                'revenue_cutoff_id' => $cutoff->id,
                'cutoff_type' => $cutoff->cutoff_type,
                'cutoff_year' => $cutoff->cutoff_year,
                'cutoff_month' => $cutoff->cutoff_month,
                'cutoff_quarter' => $cutoff->cutoff_quarter,
            ]);
            $typeLabel = ['monthly' => 'Bulanan', 'quarterly' => 'Triwulan', 'yearly' => 'Tahunan'][$cutoff->cutoff_type] ?? 'Cut Off';
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="rs-cycle-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-5">
                    <div class="d-flex align-items-center gap-4">
                        <span class="rs-cycle-icon"><i class="ki-duotone ki-arrows-circle fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i></span>
                        <div>
                            <div class="fw-bolder fs-4 text-gray-900">{{ $cutoff->period_label }}</div>
                            <div class="text-muted">{{ $typeLabel }} · {{ $cutoff->cutoff_number }}</div>
                        </div>
                    </div>
                    <span class="badge badge-light-success">Selesai</span>
                </div>
                <div class="rs-cycle-row"><span>Tanggal Cut Off</span><strong>{{ $cutoff->period_start?->format('d/m/Y') }}</strong></div>
                <div class="rs-cycle-row"><span><i class="rs-dot bg-success"></i>Uang Masuk</span><strong>Rp {{ number_format($cutoff->gross_revenue, 0, ',', '.') }}</strong></div>
                <div class="rs-cycle-row"><span><i class="rs-dot bg-danger"></i>Uang Keluar</span><strong>Rp {{ number_format($cutoff->total_expense, 0, ',', '.') }}</strong></div>
                <div class="separator my-4"></div>
                <div class="rs-cycle-row mb-4"><span class="fw-semibold text-gray-900">Estimasi Keuntungan</span><strong class="{{ $cutoff->net_revenue >= 0 ? 'text-success' : 'text-danger' }}">{{ $cutoff->net_revenue >= 0 ? '+Rp ' : '-Rp ' }}{{ number_format(abs((float) $cutoff->net_revenue), 0, ',', '.') }}</strong></div>
                <div class="rs-sharing-box">
                    <div class="d-flex justify-content-between mb-3"><span class="fw-bold text-gray-900">Sisa Sharing</span><span class="text-primary fw-bold">{{ number_format($remainingPercentage, 2, ',', '.') }}%</span></div>
                    <div class="rs-cycle-progress"><div style="width: {{ min(100, $usedPercentage) }}%"></div></div>
                    <div class="d-flex justify-content-between text-muted fs-8 mt-2"><span>Terpakai {{ number_format($usedPercentage, 2, ',', '.') }}%</span><span>Total 100%</span></div>
                </div>
                <div class="d-flex gap-3 mt-5">
                    <a href="#kt_table" class="btn btn-sm btn-light flex-fill rs-cycle-action"><i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail</a>
                    @can('revenue-sharings.create')
                        <a href="{{ $sharingUrl }}" class="btn btn-sm btn-primary flex-fill rs-cycle-action {{ $remainingPercentage <= 0 ? 'disabled' : '' }}" @if($remainingPercentage <= 0) onclick="return false;" aria-disabled="true" @endif>
                            <i class="ki-duotone ki-percentage fs-4"><span class="path1"></span><span class="path2"></span></i> Sharing Revenue
                        </a>
                    @endcan
                    @can('revenue-sharings.delete')
                        <form method="POST" action="{{ route('revenue-sharings.cutoffs.destroy', $cutoff) }}" onsubmit="return confirm('Hapus data cut off ini?')" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-light-danger btn-icon rs-cycle-delete" title="Hapus Cut Off"><i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    @endforeach
</div>
@else
<div class="card card-flush mb-7"><div class="card-body text-center py-10"><div class="fw-bold fs-4 text-gray-900 mb-2">Belum ada data cut off.</div><div class="text-muted mb-5">Klik Cut Off Revenue untuk mencatat periode revenue pertama.</div>@can('revenue-sharings.create')<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cutoffModal">Buat Cut Off</button>@endcan</div></div>
@endif

<div class="card card-flush finance-table-card">
    <div class="card-header border-0 pt-6">
        <div class="card-title"><div class="d-flex align-items-center"><span class="text-gray-700 fs-7 me-2">Tampilkan</span><select id="lengthSelect" class="form-select form-select-sm w-75px"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></div></div>
        <div class="card-toolbar"><div class="d-flex align-items-center position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control finance-search w-250px ps-12" placeholder="Cari revenue sharing..."></div></div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="kt_table" class="table align-middle">
                <thead><tr><th>#</th><th>No</th><th>Penerima</th><th>Periode</th><th>Revenue Bersih</th><th>%</th><th>Nominal Sharing</th><th>Bank</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @foreach($data as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><div class="fw-bold">{{ $item->sharing_number }}</div><div class="text-muted fs-8">{{ $item->created_at?->format('d/m/Y') }}</div></td>
                        <td><div class="fw-bold">{{ $item->recipient_name }}</div><div class="text-muted fs-8">{{ $item->submitter?->name ?? '-' }}</div></td>
                        <td><span class="badge badge-light-info">{{ $item->period_label }}</span><div class="text-muted fs-8">{{ $item->period_start?->format('d/m/Y') }} - {{ $item->period_end?->format('d/m/Y') }}</div></td>
                        <td class="fw-bold text-success">Rp {{ number_format($item->net_revenue,0,',','.') }}</td>
                        <td>{{ number_format((float)$item->sharing_percentage,2,',','.') }}%</td>
                        <td class="fw-bold text-danger">- Rp {{ number_format($item->sharing_amount,0,',','.') }}</td>
                        <td>{{ $item->bankAccount?->code ?? '-' }}</td>
                        <td>
                            @if($item->status === 'disetujui')
                                <span class="badge badge-light-success">Disetujui</span>
                            @elseif($item->status === 'ditolak')
                                <span class="badge badge-light-danger">Ditolak</span>
                            @else
                                <span class="badge badge-light-warning">Awaiting</span>
                            @endif
                        </td>
                        <td class="text-end"><div class="gh-action-group">
                            @can('revenue-sharings.approve')
                                @if($item->status === 'menunggu_approval')
                                    <form method="POST" action="{{ route('revenue-sharings.approve', $item) }}" class="d-inline rs-process-form" onsubmit="return confirm('Approve revenue sharing ini? Saldo bank akan berkurang.');">
                                        @csrf
                                        <button class="gh-action-btn gh-action-approve" title="Approve"><i class="ki-duotone ki-check fs-2"></i></button>
                                    </form>
                                    <button type="button" class="gh-action-btn gh-action-reject" onclick="openRejectModal(@js(route('revenue-sharings.reject', $item)), @js($item->sharing_number))" title="Reject">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                @endif
                            @endcan
                            <a class="gh-action-btn gh-action-view" href="{{ route('revenue-sharings.show', $item) }}" title="Detail"><i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></a>
                            @can('revenue-sharings.edit')
                                @if($item->status !== 'disetujui' || auth()->user()?->hasRole('Superadmin'))
                                    <a class="gh-action-btn gh-action-edit" href="{{ route('revenue-sharings.edit', $item) }}" title="Edit"><i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                                @endif
                            @endcan
                            @can('revenue-sharings.delete')
                                @if($item->status !== 'disetujui' || auth()->user()?->hasRole('Superadmin'))
                                    <form method="POST" action="{{ route('revenue-sharings.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus revenue sharing ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="gh-action-btn gh-action-delete" title="Hapus"><i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                                    </form>
                                @endif
                            @endcan
                        </div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cutoffModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3 class="fw-bold">Cut Off Revenue</h3><button class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button></div><form method="POST" action="{{ route('revenue-sharings.cutoffs.store') }}">@csrf<div class="modal-body"><div class="row g-4"><div class="col-12"><label class="required form-label">Tipe Cut Off</label><select id="modalCutoffType" name="cutoff_type" class="form-select"><option value="monthly" @selected($defaultCutoff['type']==='monthly')>Bulanan</option><option value="quarterly" @selected($defaultCutoff['type']==='quarterly')>Triwulan</option><option value="yearly" @selected($defaultCutoff['type']==='yearly')>Tahunan</option></select></div><div class="col-12"><label class="required form-label">Tahun</label><input type="number" id="modalCutoffYear" name="cutoff_year" class="form-control" value="{{ $defaultCutoff['year'] }}" min="2020" max="2100"></div><div class="col-12 cutoff-modal-month"><label class="required form-label">Bulan</label><select id="modalCutoffMonth" name="cutoff_month" class="form-select">@foreach($months as $value => $label)<option value="{{ $value }}" @selected((int) $defaultCutoff['month']===$value)>{{ $label }}</option>@endforeach</select></div><div class="col-12 cutoff-modal-quarter d-none"><label class="required form-label">Triwulan</label><select id="modalCutoffQuarter" name="cutoff_quarter" class="form-select">@foreach(range(1,4) as $quarter)<option value="{{ $quarter }}" @selected((int) $defaultCutoff['quarter']===$quarter)>Triwulan {{ $quarter }}</option>@endforeach</select></div></div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Cut Off</button></div></form></div></div></div>
<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3 class="fw-bold">Reject Revenue Sharing</h3><button class="btn btn-icon btn-sm" data-bs-dismiss="modal"><i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i></button></div><form method="POST" id="rejectForm" class="rs-process-form">@csrf<div class="modal-body"><div class="text-muted mb-3" id="rejectNumber">-</div><label class="required form-label">Alasan Reject</label><textarea name="rejection_reason" class="form-control" rows="4" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Reject</button></div></form></div></div></div>
@endsection

@push('styles')
<style>.rs-cycle-card{border:1px solid #e4e8f0;border-radius:12px;background:#fff;padding:20px;box-shadow:0 10px 28px rgba(15,23,42,.045)}.rs-cycle-icon{width:50px;height:50px;border-radius:10px;background:#edf4ff;display:flex;align-items:center;justify-content:center;flex-shrink:0}.rs-cycle-row{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:10px;color:#111827}.rs-cycle-row span{display:flex;align-items:center;gap:8px;min-width:0}.rs-cycle-row strong{text-align:right;font-weight:700;white-space:nowrap}.rs-dot{width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0}.rs-sharing-box{border:1px solid #e4e8f0;border-radius:10px;padding:14px;background:#fff}.rs-cycle-progress{height:7px;border-radius:999px;background:#f0f2f5;overflow:hidden}.rs-cycle-progress div{height:100%;border-radius:999px;background:#3b82f6}.rs-cycle-action{font-size:12px;font-weight:700;padding:8px 10px;gap:5px}.rs-cycle-delete{width:34px;height:34px}.finance-table-card{border:1px solid #e4e8f0;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.04)}#kt_table thead th{background:#f3f6fa;color:#061535;font-size:13px;font-weight:700;padding:14px 12px;border-bottom:1px solid #dfe5ef!important;white-space:nowrap}#kt_table tbody td{padding:13px 10px;border-bottom:1px solid #edf1f6;vertical-align:middle;font-size:12.5px}.finance-search{border-color:#dfe5ef;border-radius:10px;font-size:13px}.gh-action-group{display:inline-flex;gap:6px;align-items:center}.gh-action-btn{width:36px;height:36px;border:0;border-radius:10px;display:inline-flex;align-items:center;justify-content:center}.gh-action-view{background:#e8f3ff;color:#1682ff}.gh-action-edit{background:#fff3d8;color:#ff9f0a}.gh-action-approve{background:#e7f8ef;color:#12a150}.gh-action-reject,.gh-action-delete{background:#ffecef;color:#f1416c}.gh-action-btn i{color:currentColor!important}</style>
@endpush

@push('scripts')
<script>
var table=$('#kt_table').DataTable({dom:"<'d-none'B><'row'<'col-sm-12'tr>><'row mt-4'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",order:[],pageLength:10,columnDefs:[{orderable:false,targets:[0,9]}]});$('#searchInput').on('keyup',function(){table.search(this.value).draw();});$('#lengthSelect').on('change',function(){table.page.len($(this).val()).draw();});
function syncCutoffModal(){var type=document.getElementById('modalCutoffType').value;document.querySelector('.cutoff-modal-month').classList.toggle('d-none',type!=='monthly');document.querySelector('.cutoff-modal-quarter').classList.toggle('d-none',type!=='quarterly');}
document.getElementById('modalCutoffType')?.addEventListener('change',syncCutoffModal);syncCutoffModal();
function openRejectModal(action, number){document.getElementById('rejectForm').action=action;document.getElementById('rejectNumber').textContent=number;new bootstrap.Modal(document.getElementById('rejectModal')).show();}
document.querySelectorAll('.rs-process-form').forEach(function(form){form.addEventListener('submit',function(){form.querySelectorAll('button[type=submit],button:not([type])').forEach(function(button){button.disabled=true;});});});
</script>
@endpush
