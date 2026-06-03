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
                <div class="app-navbar-item ms-1 ms-md-3" id="kt_header_user_menu_toggle">
                    <div class="cursor-pointer symbol symbol-35px symbol-md-40px"
                         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                         data-kt-menu-attach="parent"
                         data-kt-menu-placement="bottom-end">
                        <div class="symbol-label fs-3 bg-light-primary text-primary fw-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    </div>

                    {{-- User dropdown --}}
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                         data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label fs-3 bg-light-primary text-primary fw-bold">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <span class="fw-semibold text-muted text-hover-primary fs-7">
                                        {{ auth()->user()->jabatan ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <div class="menu-content text-muted pb-2 px-5 fs-7 text-uppercase">
                                Role
                            </div>
                            @foreach(auth()->user()->getRoleNames() as $role)
                                <div class="menu-content px-5 py-1">
                                    <span class="badge badge-light-primary">{{ $role }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="menu-link px-5 w-100 text-start bg-transparent border-0">
                                    <i class="ki-outline ki-exit-right fs-2 me-2"></i>
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
