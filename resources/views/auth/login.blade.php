@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<form class="form w-100 auth-login-form" method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    <div class="auth-login-head text-center mb-10">
        <img src="{{ asset('assets/media/favicon.png') }}" alt="Garasi Hobby" class="auth-login-logo mb-5">
        <!-- <div class="auth-login-badge mb-4">Garasi Hobby</div> -->
        <h1 class="text-gray-900 fw-bolder mb-3">Selamat Datang Kembali</h1>
        <div class="text-gray-500 fw-semibold fs-6">Sistem mengelola operasional dan keuangan bengkel.</div>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-7">
            <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center mb-7">
            <i class="ki-outline ki-cross-circle fs-2 text-danger me-3"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="fv-row mb-6">
        <label class="form-label fw-semibold">Email</label>
        <div class="input-group auth-input">
            <span class="input-group-text"><i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i></span>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="nama@email.com"
                   autocomplete="email"
                   autofocus
                   class="form-control @error('email') is-invalid @enderror" />
        </div>
    </div>

    <div class="fv-row mb-5">
        <label class="form-label fw-semibold">Password</label>
        <div class="input-group auth-input">
            <span class="input-group-text"><i class="ki-duotone ki-lock fs-3"><span class="path1"></span><span class="path2"></span></i></span>
            <input type="password"
                   name="password"
                   placeholder="Masukkan password"
                   autocomplete="current-password"
                   class="form-control @error('password') is-invalid @enderror" />
        </div>
    </div>

    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
        <label class="form-check form-check-inline form-check-solid">
            <input class="form-check-input" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span class="form-check-label text-gray-700">Ingat saya</span>
        </label>
    </div>

    <div class="d-grid mb-10">
        <button type="submit" class="btn btn-primary auth-submit-btn">
            <span class="indicator-label d-inline-flex align-items-center gap-2">Masuk <i class="ki-duotone ki-arrow-right fs-3"><span class="path1"></span><span class="path2"></span></i></span>
        </button>
    </div>

    <div class="text-gray-500 text-center fw-semibold fs-7">
        Hubungi admin jika belum punya akun.
    </div>
</form>
@endsection

@push('styles')
<style>
.auth-login-badge{display:inline-flex;align-items:center;border:1px solid #dbeafe;background:#eff6ff;color:#1b84ff;border-radius:999px;padding:7px 12px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.03em}
.auth-login-logo{display:block;width:176px;max-width:72%;height:auto;object-fit:contain;margin-left:auto;margin-right:auto}
.auth-input .input-group-text{height:48px;border-color:#dfe6f2;background:#f8fafc;color:#1b84ff;border-radius:12px 0 0 12px}
.auth-input .form-control{height:48px;border-color:#dfe6f2;border-radius:0 12px 12px 0;font-weight:600}
.auth-input .form-control:focus{border-color:#93c5fd;box-shadow:0 0 0 .2rem rgba(27,132,255,.08)}
.auth-submit-btn{height:48px;border-radius:12px;font-weight:800;box-shadow:0 12px 24px rgba(27,132,255,.20)}
</style>
@endpush
