{{-- Header bar --}}
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">

        {{-- Sidebar mobile toggle --}}
        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Buka menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-outline ki-abstract-14 fs-2 fs-md-1"></i>
            </div>
        </div>

        {{-- Mobile logo --}}
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ route('dashboard') }}" class="d-lg-none">
                <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby" class="brand-logo-img brand-logo-img-mobile" />
            </a>
        </div>

        {{-- Header wrapper --}}
        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">

            {{-- Left side: brand text desktop --}}
            <div class="app-navbar align-items-stretch">
                <div class="app-navbar-item d-none d-lg-flex align-items-center">
                    <span class="fs-5 fw-semibold text-gray-700">Sistem Manajemen Bengkel</span>
                </div>
            </div>

            {{-- Right side: notifications + user --}}
            <div class="app-navbar flex-shrink-0">

                {{-- Notification bell --}}
                @include('layouts.partials.notification-bell')

                {{-- User menu --}}
                <div class="app-navbar-item ms-2 ms-md-3" id="kt_header_user_menu_toggle">
                    <div class="cursor-pointer gh-user-trigger"
                         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                         data-kt-menu-attach="parent"
                         data-kt-menu-placement="bottom-end">
                        <img src="{{ asset('assets/media/profil-user.png') }}" alt="{{ auth()->user()->name ?? 'User' }}" class="gh-user-avatar">
                        <div class="d-none d-md-flex flex-column lh-sm">
                            <span class="fw-bold text-gray-900 fs-7">{{ auth()->user()->name ?? 'User' }}</span>
                            <span class="text-muted fs-8">{{ auth()->user()->jabatan ?? 'Pengguna' }}</span>
                        </div>
                        <i class="ki-duotone ki-down fs-5 text-muted d-none d-md-inline-flex"></i>
                    </div>

                    {{-- User dropdown --}}
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-325px gh-user-menu"
                         data-kt-menu="true">
                        <div class="gh-user-head px-6 py-5">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/media/profil-user.png') }}" alt="{{ auth()->user()->name ?? 'User' }}" class="gh-user-avatar-lg me-4">
                                <div class="min-w-0">
                                    <div class="fw-bolder text-gray-900 fs-5 text-truncate">{{ auth()->user()->name ?? 'User' }}</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                        <span class="text-muted fs-7 text-truncate">{{ auth()->user()->jabatan ?? '-' }}</span>
                                        @foreach(auth()->user()->getRoleNames()->take(2) as $role)
                                            <span class="badge badge-light-primary">{{ $role }}</span>
                                        @endforeach
                                    </div>
                                    <div class="text-gray-500 fs-8 text-truncate">{{ auth()->user()->email ?? '' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-0"></div>

                        <div class="px-4 py-3">
                            <a href="{{ route('my-account.edit') }}" class="menu-link px-4 py-3 rounded {{ request()->routeIs('my-account.*') ? 'active' : '' }}">
                                <i class="ki-outline ki-user fs-2 me-3 text-primary"></i>
                                My Account
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="menu-link px-4 py-3 w-100 text-start bg-transparent border-0 rounded text-danger">
                                    <i class="ki-outline ki-exit-right fs-2 me-3 text-danger"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
