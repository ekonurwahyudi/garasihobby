@extends('layouts.app')

@section('title', 'Mutasi ' . $bank_account->bank_name)

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('bank-accounts.index') }}" class="text-muted text-hover-primary">Account Bank</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">Detail Mutasi</li>
@endsection

@section('toolbar_actions')<a href="{{ route('bank-accounts.index') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>@endsection

@section('content')
@php($totalIncome=$movements->where('type','income')->sum('amount'))
@php($totalExpense=$movements->where('type','expense')->sum('amount'))
<div class="row g-5 mb-6"><div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Saldo Saat Ini</div><div class="text-primary fw-bold fs-2">Rp {{ number_format($bank_account->balance,0,',','.') }}</div><div class="text-gray-600 mt-3">{{ $bank_account->code }} - {{ $bank_account->account_name ?? '-' }}<br>{{ $bank_account->account_number ?? '-' }}</div></div></div></div>
<div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Total Masuk</div><div class="text-success fw-bold fs-2">Rp {{ number_format($totalIncome,0,',','.') }}</div><div class="text-gray-500 mt-3">Saldo awal: Rp {{ number_format($bank_account->opening_balance,0,',','.') }}</div></div></div></div>
<div class="col-md-4"><div class="card card-flush h-100"><div class="card-body"><div class="text-gray-500 fs-7">Total Keluar</div><div class="text-danger fw-bold fs-2">Rp {{ number_format($totalExpense,0,',','.') }}</div><div class="text-gray-500 mt-3">{{ $movements->count() }} riwayat mutasi</div></div></div></div></div>
<div class="card card-flush"><div class="card-header border-0 pt-6"><div class="card-title"><h3>Riwayat Mutasi</h3></div><div class="card-toolbar position-relative"><i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i><input id="searchInput" class="form-control w-250px ps-12" placeholder="Cari mutasi..."></div></div>
<div class="card-body pt-0"><table id="kt_table" class="table table-striped table-row-bordered gy-5 gs-7 border rounded"><thead><tr class="fw-semibold fs-6 text-gray-800"><th>No</th><th>Tanggal</th><th>Referensi</th><th>Sumber</th><th>Keterangan</th><th>Jenis</th><th>Nominal</th><th>Saldo</th></tr></thead><tbody>
@forelse($movements as $i=>$m)<tr><td>{{ $i+1 }}</td><td>{{ \Carbon\Carbon::parse($m['date'])->format('d/m/Y') }}</td><td>{{ $m['reference'] }}</td><td>{{ $m['source'] }}</td><td>{{ $m['description'] }}</td><td><span class="badge {{ $m['type']==='income'?'badge-light-success':'badge-light-danger' }}">{{ $m['type']==='income'?'Masuk':'Keluar' }}</span></td><td class="{{ $m['type']==='income'?'text-success':'text-danger' }} fw-bold">{{ $m['type']==='income'?'+':'-' }} Rp {{ number_format($m['amount'],0,',','.') }}</td><td>Rp {{ number_format($m['balance'],0,',','.') }}</td></tr>
@empty<tr><td colspan="8" class="text-center text-gray-500 py-10">Belum ada mutasi rekening.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
@push('scripts')<script>var table=$('#kt_table').DataTable({order:[],columnDefs:[{orderable:false,targets:[0]}]});$('#searchInput').on('keyup',function(){table.search(this.value).draw();});</script>@endpush
