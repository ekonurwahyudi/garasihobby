{{-- Notification Bell --}}
@can('notifications.view')
@php
    \App\Support\NotificationCleanup::markResolvedApprovalNotificationsRead(auth()->user());
    $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(10)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp
<div class="app-navbar-item ms-1 ms-md-2">
    <div class="gh-header-icon-btn position-relative cursor-pointer"
         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
         data-kt-menu-attach="parent"
         data-kt-menu-placement="bottom-end">
        <i class="ki-duotone ki-notification-bing fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-9 fw-bold">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>

    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-400px gh-notification-menu" data-kt-menu="true">
        <div class="gh-notification-head px-6 py-5">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <h3 class="text-gray-900 fw-bolder fs-4 mt-0 mb-1">Notifikasi</h3>
                    <div class="text-muted fs-7">{{ $unreadCount }} belum dibaca</div>
                </div>
                <span class="gh-header-icon-btn">
                    <i class="ki-duotone ki-notification-status fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
            </div>
        </div>

        <div class="scroll-y mh-350px px-5 py-5">
            @forelse($unreadNotifications as $notif)
                <div class="gh-notification-item mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="symbol symbol-38px flex-shrink-0">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-{{ $notif->data['icon'] ?? 'notification-bing' }} fs-2 text-primary"></i>
                            </span>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <a href="{{ route('notifications.read', $notif) }}" onclick="event.preventDefault(); document.getElementById('notif-read-{{ $notif->id }}').submit();" class="fs-6 text-gray-800 text-hover-primary fw-bold">
                                {{ $notif->data['title'] ?? 'Notifikasi' }}
                            </a>
                            <form id="notif-read-{{ $notif->id }}" method="POST" action="{{ route('notifications.read', $notif) }}" class="d-none">@csrf</form>
                            <div class="text-gray-500 fs-7 mt-1">{{ $notif->data['message'] ?? '' }}</div>
                            <span class="badge badge-light fs-8 mt-2">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 px-5">
                    <div class="symbol symbol-56px mx-auto mb-4">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-notification-bing fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </span>
                    </div>
                    <div class="fw-bold text-gray-800 mb-1">Tidak ada notifikasi baru</div>
                    <div class="text-muted fs-7">Semua informasi terbaru sudah dibaca.</div>
                </div>
            @endforelse
        </div>

        <div class="py-4 text-center border-top bg-light">
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-light-primary">
                Lihat Semua
                <i class="ki-outline ki-arrow-right fs-5"></i>
            </a>
        </div>
    </div>
</div>
@endcan
