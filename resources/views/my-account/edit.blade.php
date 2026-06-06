@extends('layouts.app')

@section('title', 'My Account')

@section('breadcrumb')
<li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
<li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
<li class="breadcrumb-item text-muted">My Account</li>
@endsection

@section('toolbar_actions')
<a href="{{ route('dashboard') }}" class="btn btn-sm btn-light"><i class="ki-duotone ki-left fs-3"></i> Kembali</a>
@endsection

@section('content')
@php
    $roles = $user->getRoleNames();
    $initials = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $user->name ?: 'User')))
        ->filter()
        ->map(fn ($word) => substr($word, 0, 1))
        ->take(2)
        ->implode('') ?: 'U';
@endphp

<div class="account-hero mb-7">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-5">
        <div class="d-flex align-items-center gap-4 min-w-0">
            <div class="account-avatar">{{ $initials }}</div>
            <div class="min-w-0">
                <div class="text-muted fs-8 text-uppercase fw-bold mb-2">Profil Pengguna</div>
                <h1 class="fw-bolder text-gray-900 mb-1 text-truncate">{{ $user->name }}</h1>
                <div class="text-gray-600 text-truncate">{{ $user->email }}</div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @forelse($roles as $role)
                        <span class="badge badge-light-primary">{{ $role }}</span>
                    @empty
                        <span class="badge badge-light">Tidak ada role</span>
                    @endforelse
                    <span class="badge {{ $user->status === 'aktif' ? 'badge-light-success' : 'badge-light-danger' }}">{{ str($user->status ?? '-')->title() }}</span>
                </div>
            </div>
        </div>
        <div class="account-hero-meta">
            <div class="text-muted fs-8 text-uppercase fw-bold">Terakhir diperbarui</div>
            <div class="fw-bold text-gray-900">{{ $user->updated_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="row g-7">
    <div class="col-xl-7">
        <div class="card account-card h-100">
            <div class="card-header border-0 pt-6">
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Informasi Profil</h3>
                    <div class="text-muted fs-7">Perbarui nama, jabatan, nomor telepon, dan email login.</div>
                </div>
            </div>
            <form method="POST" action="{{ route('my-account.profile') }}">
                @csrf
                @method('PUT')
                <div class="card-body pt-0">
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="required form-label fw-semibold">Nama Lengkap</label>
                            <div class="input-group account-input">
                                <span class="input-group-text"><i class="ki-duotone ki-user fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            </div>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jabatan</label>
                            <div class="input-group account-input">
                                <span class="input-group-text"><i class="ki-duotone ki-badge fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Supervisor">
                            </div>
                            @error('jabatan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <div class="input-group account-input">
                                <span class="input-group-text"><i class="ki-duotone ki-phone fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                            </div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label fw-semibold">Email</label>
                            <div class="input-group account-input">
                                <span class="input-group-text"><i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            </div>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer border-0 pt-0 d-flex justify-content-end">
                    <button class="btn btn-primary"><i class="ki-duotone ki-check fs-3"></i> Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card account-card h-100">
            <div class="card-header border-0 pt-6">
                <div>
                    <h3 class="fw-bold text-gray-900 mb-1">Ubah Password</h3>
                    <div class="text-muted fs-7">Gunakan password kuat minimal 8 karakter.</div>
                </div>
            </div>
            <form method="POST" action="{{ route('my-account.password') }}">
                @csrf
                @method('PUT')
                <div class="card-body pt-0">
                    <div class="mb-5">
                        <label class="required form-label fw-semibold">Password Lama</label>
                        <div class="input-group account-input">
                            <span class="input-group-text"><i class="ki-duotone ki-lock fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                        </div>
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-5">
                        <label class="required form-label fw-semibold">Password Baru</label>
                        <div class="input-group account-input">
                            <span class="input-group-text"><i class="ki-duotone ki-shield-tick fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="required form-label fw-semibold">Konfirmasi Password Baru</label>
                        <div class="input-group account-input">
                            <span class="input-group-text"><i class="ki-duotone ki-check-circle fs-3"><span class="path1"></span><span class="path2"></span></i></span>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="card-footer border-0 pt-0 d-flex justify-content-end">
                    <button class="btn btn-dark"><i class="ki-duotone ki-key fs-3"><span class="path1"></span><span class="path2"></span></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.account-hero{border:1px solid #e4e8f0;border-radius:20px;background:linear-gradient(135deg,#f8fbff,#fff 68%);padding:26px;box-shadow:0 14px 34px rgba(15,23,42,.05)}
.account-avatar{width:76px;height:76px;border-radius:22px;background:linear-gradient(135deg,#1b84ff,#60a5fa);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:850;box-shadow:0 14px 30px rgba(27,132,255,.24);flex:0 0 76px}
.account-hero-meta{border:1px solid #e4e8f0;border-radius:14px;background:#fff;padding:14px 16px;min-width:210px}
.account-card{border:1px solid #e4e8f0;border-radius:18px;box-shadow:0 14px 34px rgba(15,23,42,.05);overflow:hidden}
.account-input .input-group-text{background:#f8fafc;border-color:#dfe6f2;color:#1b84ff}
.account-input .form-control{border-color:#dfe6f2}
.account-input .form-control:focus{border-color:#93c5fd;box-shadow:0 0 0 .2rem rgba(27,132,255,.08)}
.account-card .btn{border-radius:10px;display:inline-flex;align-items:center;gap:7px}
</style>
@endpush
