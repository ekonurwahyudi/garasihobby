{{--
    Sidebar Garasi Hobby
    Struktur mengikuti persis metronic/index.html (light-sidebar layout):
    - Logo block + toggle minimize
    - Menu column dengan menu-accordion
    - Section heading via .menu-heading
    - Footer sidebar
    Item menu di-gate by permission (Spatie).
--}}
<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column"
     data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="225px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby" class="app-sidebar-logo-default brand-logo-img brand-logo-img-sidebar" />
            <img src="{{ asset('assets/media/logos.png') }}" alt="GH" class="app-sidebar-logo-minimize brand-logo-img-sidebar-minimize" />
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true"
             data-kt-toggle-state="active"
             data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll"
                 class="scroll-y my-5 mx-3"
                 data-kt-scroll="true"
                 data-kt-scroll-activate="true"
                 data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu"
                 data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                     id="#kt_app_sidebar_menu"
                     data-kt-menu="true"
                     data-kt-menu-expand="false">

                    {{-- ====== DASHBOARD ====== --}}
                    @can('dashboard.view')
                    <!--begin:Menu item-->
                    <div class="menu-item {{ request()->routeIs('dashboard') ? 'here show' : '' }}">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-element-11 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    {{-- ====== SECTION: OPERASIONAL ====== --}}
                    @canany(['customers.view', 'orders.create', 'orders.view', 'purchases.view', 'purchases.create', 'purchases.approve', 'materials.view'])
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Operasional</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    @endcanany

                    @can('customers.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-user-square fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pelanggan</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @canany(['orders.create', 'orders.view'])
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-briefcase fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Order Management</span>
                        </a>
                    </div>
                    <!--end:Menu item-->
                    @endcanany

                    @can('purchases.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('material-purchases.*') ? 'active' : '' }}" href="{{ route('material-purchases.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-purchase fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Pembelian Material</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('materials.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('material-inventory.*') ? 'active' : '' }}" href="{{ route('material-inventory.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-courier-express fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Persediaan Material</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @canany(['finance-transactions.view', 'bank-accounts.view', 'asset-purchases.view', 'debt-receivables.view', 'revenue-sharings.view'])
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Keuangan</span>
                        </div>
                    </div>
                    @endcanany

                    @can('bank-accounts.view')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}" href="{{ route('bank-accounts.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-bank fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                            <span class="menu-title">Account Bank</span>
                        </a>
                    </div>
                    @endcan

                    @can('finance-transactions.view')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('finance-transactions.*') ? 'active' : '' }}" href="{{ route('finance-transactions.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-wallet fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                            <span class="menu-title">Input Keuangan</span>
                        </a>
                    </div>
                    @endcan

                    @can('asset-purchases.view')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('asset-purchases.*') ? 'active' : '' }}" href="{{ route('asset-purchases.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-briefcase fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                            <span class="menu-title">Pembelian Aset</span>
                        </a>
                    </div>
                    @endcan

                    @can('debt-receivables.view')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('debt-receivables.*') ? 'active' : '' }}" href="{{ route('debt-receivables.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-arrows-circle fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                            <span class="menu-title">Hutang Piutang</span>
                        </a>
                    </div>
                    @endcan

                    @can('revenue-sharings.view')
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('revenue-sharings.*') ? 'active' : '' }}" href="{{ route('revenue-sharings.index') }}">
                            <span class="menu-icon"><i class="ki-duotone ki-percentage fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                            <span class="menu-title">Revenue Sharing</span>
                        </a>
                    </div>
                    @endcan

                     {{-- ====== SECTION: MASTER DATA ====== --}}
                    @canany(['users.view', 'checklist.view', 'materials.view', 'promo.view', 'finance-master.view'])
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Master Data</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    @endcanany

                    @can('users.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-people fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </span>
                            <span class="menu-title">Karyawan</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('checklist.view')
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-check-circle fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Item Checklist</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('checklist-items.*') ? 'active' : '' }}" href="{{ route('checklist-items.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Daftar Item</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('checklist-categories.*') ? 'active' : '' }}" href="{{ route('checklist-categories.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Checklist</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('materials.view')
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-package fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Material</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('materials.*') ? 'active' : '' }}" href="{{ route('materials.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Katalog Material</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('material-categories.*') ? 'active' : '' }}" href="{{ route('material-categories.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Material</span>
                                </a>
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('promo.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('promo-packages.*') ? 'active' : '' }}" href="{{ route('promo-packages.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-discount fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Paket Promo</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('finance-master.view')
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('finance-categories.*', 'finance-items.*', 'asset-categories.*', 'debt-receivable-categories.*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="ki-duotone ki-dollar fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                            <span class="menu-title">Item Keuangan</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('finance-items.*') ? 'active' : '' }}" href="{{ route('finance-items.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Daftar Item</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('finance-categories.*') ? 'active' : '' }}" href="{{ route('finance-categories.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Item</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('asset-categories.*') ? 'active' : '' }}" href="{{ route('asset-categories.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Aset</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('debt-receivable-categories.*') ? 'active' : '' }}" href="{{ route('debt-receivable-categories.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Kategori Hutang Piutang</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    {{-- ====== SECTION: PENGATURAN ====== --}}
                    @canany(['roles.manage', 'notifications.view'])
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Pengaturan</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    @endcanany

                    @can('roles.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-key fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Role &amp; Permission</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                    @can('notifications.view')
                    <!--begin:Menu item-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-notification-bing fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Notifikasi</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->
                    @endcan

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->

    <!--begin::Footer-->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit"
                    class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
                    data-bs-toggle="tooltip"
                    data-bs-trigger="hover"
                    title="Keluar dari sistem">
                <i class="ki-duotone ki-exit-right fs-2 me-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <span class="btn-label">Keluar</span>
            </button>
        </form>
    </div>
    <!--end::Footer-->
</div>
<!--end::Sidebar-->
