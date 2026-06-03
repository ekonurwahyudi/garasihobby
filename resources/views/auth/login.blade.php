@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<form class="form w-100" method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    <div class="text-center mb-11">
        <h1 class="text-gray-900 fw-bolder mb-3">Selamat Datang</h1>
        <div class="text-gray-500 fw-semibold fs-6">Masuk ke {{ config('app.name') }}</div>
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

    <div class="fv-row mb-8">
        <input type="email"
               name="email"
               value="{{ old('email') }}"
               placeholder="Email"
               autocomplete="email"
               autofocus
               class="form-control bg-transparent @error('email') is-invalid @enderror" />
    </div>

    <div class="fv-row mb-3">
        <input type="password"
               name="password"
               placeholder="Password"
               autocomplete="current-password"
               class="form-control bg-transparent @error('password') is-invalid @enderror" />
    </div>

    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
        <label class="form-check form-check-inline form-check-solid">
            <input class="form-check-input" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span class="form-check-label text-gray-700">Ingat saya</span>
        </label>
    </div>

    <div class="d-grid mb-10">
        <button type="submit" class="btn btn-primary">
            <span class="indicator-label">Masuk</span>
        </button>
    </div>

    <div class="text-gray-500 text-center fw-semibold fs-7">
        Hubungi admin jika belum punya akun.
    </div>
</form>
@endsection
