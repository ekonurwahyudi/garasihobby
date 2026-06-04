@extends('layouts.app')

@section('title', 'Notifikasi')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted"><a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Beranda</a></li>
    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
    <li class="breadcrumb-item text-muted">Notifikasi</li>
@endsection

@section('toolbar_actions')
    @if(auth()->user()->unreadNotifications()->exists())
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-light-primary">Tandai Semua Dibaca</button>
    </form>
    @endif
@endsection

@section('content')
<div class="card card-flush">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h2 class="fw-bold">Semua Notifikasi</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        @forelse($notifications as $notification)
            <div class="d-flex align-items-start justify-content-between border rounded p-5 mb-4 {{ $notification->read_at ? 'bg-white' : 'bg-light-primary' }}">
                <div class="d-flex align-items-start">
                    <div class="symbol symbol-45px me-4">
                        <span class="symbol-label bg-light">
                            <i class="ki-outline ki-{{ $notification->data['icon'] ?? 'notification-bing' }} fs-2 text-primary"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-gray-900">{{ $notification->data['title'] ?? 'Notifikasi' }}</div>
                        <div class="text-gray-700 mt-1">{{ $notification->data['message'] ?? '' }}</div>
                        <div class="text-muted fs-8 mt-2">{{ $notification->created_at->format('d/m/Y H:i') }} - {{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $notification->read_at ? 'btn-light' : 'btn-primary' }}">Buka</button>
                </form>
            </div>
        @empty
            <div class="text-center text-muted py-10">Belum ada notifikasi.</div>
        @endforelse

        <div class="mt-5">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
