@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a>
    </li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Dashboard</li>
@endsection

@section('toolbar_actions')
    <a href="#" class="btn btn-sm btn-flex btn-light-primary fw-semibold">
        <i class="ki-outline ki-calendar fs-3"></i>
        {{ now()->translatedFormat('d F Y') }}
    </a>
@endsection

@section('content')
    {{-- Welcome banner --}}
    @include('dashboard.widgets.welcome-banner')

    {{-- Row 1: Highlights, Projects, Sales (Metronic widgets) --}}
    @include('dashboard.widgets.row-1')

    {{-- Row 2: Tables, Card, List widget (Metronic widgets) --}}
    @include('dashboard.widgets.row-2')

    {{-- Row 3: Engage widget --}}
    @include('dashboard.widgets.row-3')
@endsection

@section('modals')
    @include('layouts.partials.modals')
@endsection

@push('scripts')
    @include('dashboard.widgets.scripts')
@endpush
