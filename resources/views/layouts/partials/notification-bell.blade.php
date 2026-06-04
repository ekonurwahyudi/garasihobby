{{-- Notification Bell (skeleton — Step 4 isi data real dari Notification) --}}
@can('notifications.view')
<div class="app-navbar-item ms-1 ms-md-3">
    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px position-relative"
         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
         data-kt-menu-attach="parent"
         data-kt-menu-placement="bottom-end">
        <i class="ki-outline ki-notification-bing fs-1"></i>
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-8 fw-bold">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>

    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">
        <div class="d-flex flex-column bgi-no-repeat rounded-top px-9 pt-10 pb-6 bg-light-primary">
            <h3 class="text-gray-900 fw-bold px-9 mt-0 mb-2">
                Notifikasi
                <span class="fs-8 fw-semibold text-muted ms-2">{{ $unreadCount }} belum dibaca</span>
            </h3>
        </div>

        <div class="tab-content">
            <div class="scroll-y mh-325px my-5 px-8">
                @forelse(auth()->user()->unreadNotifications()->latest()->take(10)->get() as $notif)
                    <div class="d-flex flex-stack py-4">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-4">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-{{ $notif->data['icon'] ?? 'notification-bing' }} fs-2 text-primary"></i>
                                </span>
                            </div>
                            <div class="mb-0 me-2">
                                <a href="{{ route('notifications.read', $notif) }}" onclick="event.preventDefault(); document.getElementById('notif-read-{{ $notif->id }}').submit();" class="fs-6 text-gray-800 text-hover-primary fw-bold">
                                    {{ $notif->data['title'] ?? 'Notifikasi' }}
                                </a>
                                <form id="notif-read-{{ $notif->id }}" method="POST" action="{{ route('notifications.read', $notif) }}" class="d-none">@csrf</form>
                                <div class="text-gray-500 fs-7">{{ $notif->data['message'] ?? '' }}</div>
                            </div>
                        </div>
                        <span class="badge badge-light fs-8">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="text-center text-muted py-10 fs-7">
                        Belum ada notifikasi baru.
                    </div>
                @endforelse
            </div>

            <div class="py-3 text-center border-top">
                <a href="{{ route('notifications.index') }}" class="btn btn-color-gray-600 btn-active-color-primary">
                    Lihat Semua
                    <i class="ki-outline ki-arrow-right fs-5"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endcan
