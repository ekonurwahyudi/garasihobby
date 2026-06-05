<!DOCTYPE html>
<html lang="id">
<head>
    <base href="{{ url('/') }}/" />
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Manajemen Bengkel Garasi Hobby" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" type="image/png" href="{{ asset('assets/media/favicon.png') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/media/favicon.png') }}" />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">

    {{-- Vendor Stylesheets --}}
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --bs-font-sans-serif: "Onest", sans-serif;
        }
        body,
        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: "Onest", sans-serif;
        }
        .brand-logo-img {
            display: block;
            width: auto;
            height: 44px;
            object-fit: contain;
        }
        .brand-logo-img-mobile {
            height: 36px;
        }
        .brand-logo-img-sidebar {
            max-width: 150px;
        }
        .brand-logo-img-sidebar-minimize {
            width: 32px;
            height: 32px;
            object-fit: cover;
            object-position: left center;
        }
        .app-content .table-responsive {
            border-radius: 12px;
        }
        .app-content .table:not(.table-borderless) {
            margin-bottom: 0;
            color: #17213b;
        }
        .app-content .table:not(.table-borderless) > thead > tr > th {
            background: #f3f6fa;
            color: #061535;
            font-size: 13px;
            font-weight: 700;
            padding: 14px 12px;
            border-bottom: 1px solid #dfe5ef;
            vertical-align: middle;
            white-space: nowrap;
        }
        .app-content .table:not(.table-borderless) > tbody > tr > td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf1f6;
            vertical-align: middle;
            font-size: 13px;
        }
        .app-content .table:not(.table-borderless) > tbody > tr:hover {
            background: #f9fbff;
        }
        .app-content .table.table-striped > tbody > tr:nth-of-type(odd) > *,
        .app-content .table.table-striped > tbody > tr:nth-of-type(even) > * {
            --bs-table-bg-type: transparent;
            background-color: transparent !important;
            box-shadow: none !important;
        }
        .app-content .table.table-striped > tbody > tr:hover > * {
            background-color: #f9fbff !important;
        }
        .app-content .table:not(.table-borderless) > tbody > tr:last-child > td {
            border-bottom: 0;
        }
        .app-content .table.border,
        .app-content .table.rounded {
            border-color: #dfe5ef !important;
            border-radius: 12px !important;
            overflow: hidden;
        }
        .app-content table.dataTable > thead > tr > th,
        .app-content table.dataTable > thead > tr > th.sorting_asc,
        .app-content table.dataTable > thead > tr > th.sorting_desc {
            position: relative;
            padding-right: 28px;
        }
        .app-content table.dataTable > thead > tr > th::before,
        .app-content table.dataTable > thead > tr > th::after,
        .app-content table.dataTable > thead > tr > th.sorting_asc::before,
        .app-content table.dataTable > thead > tr > th.sorting_asc::after,
        .app-content table.dataTable > thead > tr > th.sorting_desc::before,
        .app-content table.dataTable > thead > tr > th.sorting_desc::after {
            content: "";
            position: absolute;
            right: 10px;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            opacity: 1;
        }
        .app-content table.dataTable > thead > tr > th::before,
        .app-content table.dataTable > thead > tr > th.sorting_asc::before,
        .app-content table.dataTable > thead > tr > th.sorting_desc::before {
            top: calc(50% - 8px);
            border-bottom: 6px solid #aab2bd;
        }
        .app-content table.dataTable > thead > tr > th::after,
        .app-content table.dataTable > thead > tr > th.sorting_asc::after,
        .app-content table.dataTable > thead > tr > th.sorting_desc::after {
            top: calc(50% + 2px);
            border-top: 6px solid #aab2bd;
        }
        .app-content table.dataTable > thead > tr > th.sorting_asc::before {
            border-bottom-color: #1f2937;
        }
        .app-content table.dataTable > thead > tr > th.sorting_desc::after {
            border-top-color: #1f2937;
        }
        .app-content table.dataTable > thead > tr > th.text-end::before,
        .app-content table.dataTable > thead > tr > th.text-end::after {
            right: 12px;
        }
        .app-content table.dataTable > thead > tr > th:first-child::before,
        .app-content table.dataTable > thead > tr > th:first-child::after,
        .app-content table.dataTable > thead > tr > th.no-sort::before,
        .app-content table.dataTable > thead > tr > th.no-sort::after,
        .app-content table.dataTable > thead > tr > th.sorting_disabled::before,
        .app-content table.dataTable > thead > tr > th.sorting_disabled::after,
        .app-content table.dataTable > thead > tr > th:last-child::before,
        .app-content table.dataTable > thead > tr > th:last-child::after {
            display: none !important;
        }
        .app-content table.dataTable > thead > tr > th:first-child,
        .app-content table.dataTable > thead > tr > th.no-sort,
        .app-content table.dataTable > thead > tr > th.sorting_disabled,
        .app-content table.dataTable > thead > tr > th:last-child {
            padding-right: 12px;
        }
        .app-content .dataTables_wrapper .dataTables_info {
            color: #061535;
            font-size: 13px;
            padding-top: 0 !important;
        }
        .app-content .dataTables_wrapper > .row:last-child,
        .app-content .dataTables_wrapper .finance-table-footer,
        .app-content .dataTables_wrapper .purchase-table-footer {
            margin-top: 16px !important;
            padding: 0 4px !important;
        }
        .app-content .dataTables_wrapper .pagination {
            margin-bottom: 0;
        }
        .app-content .dataTables_wrapper .page-link {
            border-radius: 10px;
        }
        .app-content .dataTables_wrapper .page-item.active .page-link {
            background: #1b84ff;
            border-color: #1b84ff;
        }
        .app-content .gh-action-group,
        .app-content .finance-action-group {
            display: inline-flex;
            gap: 6px;
            justify-content: flex-end;
            align-items: center;
        }
        .app-content .gh-action-btn,
        .app-content .finance-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .app-content .gh-action-btn:hover,
        .app-content .finance-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }
        .app-content .gh-action-btn i,
        .app-content .finance-action-btn i {
            color: currentColor !important;
            font-size: 18px !important;
        }
        .app-content .gh-action-view,
        .app-content .finance-action-view { background: #e8f3ff; color: #1682ff; }
        .app-content .gh-action-edit,
        .app-content .finance-action-edit { background: #fff3d8; color: #ff9f0a; }
        .app-content .gh-action-approve,
        .app-content .finance-action-approve { background: #e7f8ef; color: #12a150; }
        .app-content .gh-action-reject,
        .app-content .gh-action-delete,
        .app-content .finance-action-reject,
        .app-content .finance-action-delete { background: #ffecef; color: #f1416c; }
        .app-content table .btn.btn-icon.btn-sm {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .app-content table .btn.btn-icon.btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }
        .app-content table .btn.btn-icon.btn-sm i {
            color: currentColor !important;
            font-size: 18px !important;
        }
        .app-content table .btn.btn-icon.btn-sm.btn-info,
        .app-content table .btn.btn-icon.btn-sm.btn-primary {
            background: #e8f3ff !important;
            color: #1682ff !important;
        }
        .app-content table .btn.btn-icon.btn-sm.btn-warning {
            background: #fff3d8 !important;
            color: #ff9f0a !important;
        }
        .app-content table .btn.btn-icon.btn-sm.btn-success {
            background: #e7f8ef !important;
            color: #12a150 !important;
        }
        .app-content table .btn.btn-icon.btn-sm.btn-danger {
            background: #ffecef !important;
            color: #f1416c !important;
        }
        #kt_app_toolbar .btn.btn-sm.btn-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }
        .modal .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
        }
        .modal .modal-header {
            min-height: 78px;
            padding: 22px 28px;
            border-bottom: 1px solid #edf1f7;
            background: linear-gradient(135deg, #f8fbff 0%, #fff 72%);
            align-items: center;
        }
        .modal .modal-header h2,
        .modal .modal-title {
            margin: 0;
            color: #0f172a;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.25;
        }
        .modal .modal-header .btn.btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #64748b;
        }
        .modal .modal-header .btn.btn-icon:hover {
            background: #e8f3ff;
            color: #1682ff;
        }
        .modal .gh-modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .modal .gh-modal-title-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #e8f3ff;
            color: #1682ff;
        }
        .modal .gh-modal-title-icon i {
            color: currentColor !important;
        }
        .modal .modal-body {
            padding: 28px;
        }
        .modal .modal-body.mx-5,
        .modal .modal-body.my-7 {
            margin: 0 !important;
        }
        .modal .modal-body.scroll-y {
            padding-right: 28px !important;
        }
        .modal .modal-footer {
            padding: 0 28px 28px;
            border-top: 0;
            gap: 10px;
        }
        .modal .modal-footer.flex-center {
            justify-content: center;
        }
        .modal .form-label {
            color: #334155;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .modal .form-control,
        .modal .form-select,
        .modal .input-group-text {
            border-color: #dfe6f2;
            border-radius: 10px;
        }
        .modal .input-group > .form-control,
        .modal .input-group > .form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .modal .input-group > .input-group-text {
            background: #f8fafc;
            color: #64748b;
        }
        .modal .alert.alert-danger {
            border: 1px solid #ffd5de;
            border-radius: 12px;
            background: #fff4f6;
            color: #b42342;
        }
        .modal .table {
            border-radius: 12px !important;
            overflow: hidden;
        }
        .gh-header-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f3f7fd;
            color: #516075;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
        }
        .gh-header-icon-btn:hover {
            transform: translateY(-1px);
            background: #e8f3ff;
            color: #1682ff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }
        .gh-notification-menu,
        .gh-user-menu {
            border: 1px solid #e4e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .15);
        }
        .gh-notification-head,
        .gh-user-head {
            background: linear-gradient(135deg, #f8fbff 0%, #fff 72%);
            border-bottom: 1px solid #edf1f7;
        }
        .gh-notification-item {
            border: 1px solid #edf1f7;
            border-radius: 14px;
            padding: 12px;
            transition: background .15s ease, border-color .15s ease;
        }
        .gh-notification-item:hover {
            background: #f8fbff;
            border-color: #d8e9ff;
        }
        .gh-user-trigger {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 14px;
            padding: 6px 8px 6px 6px;
            background: #f8fafc;
            border: 1px solid #edf1f7;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .gh-user-trigger:hover {
            transform: translateY(-1px);
            border-color: #d8e9ff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
        }
        .gh-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
            display: block;
            background: #eef4ff;
        }
        .gh-user-avatar-lg {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            object-fit: cover;
            display: block;
            background: #eef4ff;
        }
    </style>

    @stack('styles')
</head>
<body id="kt_app_body"
      data-kt-app-layout="dark-sidebar"
      data-kt-app-header-fixed="true"
      data-kt-app-sidebar-enabled="true"
      data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-hoverable="true"
      data-kt-app-sidebar-push-header="true"
      data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

    {{-- Theme mode setup --}}
    <script>
        var defaultThemeMode = "light"; var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                themeMode = localStorage.getItem("data-bs-theme") !== null
                    ? localStorage.getItem("data-bs-theme")
                    : defaultThemeMode;
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

            @include('layouts.partials.header')

            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

                @include('layouts.partials.sidebar')

                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">

                        {{-- Toolbar / Page Heading --}}
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                                        @yield('page_title', View::yieldContent('title'))
                                    </h1>
                                    @hasSection('breadcrumb')
                                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                                            @yield('breadcrumb')
                                        </ul>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 gap-lg-3">
                                    @yield('toolbar_actions')
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-xxl">
                                @if (session('success'))
                                    <div class="alert alert-success d-flex align-items-center mb-5">
                                        <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
                                        <div>{{ session('success') }}</div>
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger d-flex align-items-center mb-5">
                                        <i class="ki-outline ki-cross-circle fs-2 text-danger me-3"></i>
                                        <div>{{ session('error') }}</div>
                                    </div>
                                @endif

                                @yield('content')
                            </div>
                        </div>

                    </div>

                    @include('layouts.partials.footer')
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll to top --}}
    @include('layouts.partials.scrolltop')

    {{-- Page-specific modals (optional) --}}
    @hasSection('modals')
        @yield('modals')
    @endif

    {{-- Global JS --}}
    <script>var hostUrl = "{{ asset('assets/') }}/";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        if (window.jQuery && $.fn.dataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                dom: "<'d-none'B><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5 d-flex align-items-center'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
                language: {
                    zeroRecords: 'Data tidak ditemukan',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(filter dari _MAX_ total data)',
                    paginate: {
                        first: '<i class="ki-duotone ki-double-left fs-4"></i>',
                        last: '<i class="ki-duotone ki-double-right fs-4"></i>',
                        next: '<i class="ki-duotone ki-right fs-4"></i>',
                        previous: '<i class="ki-duotone ki-left fs-4"></i>'
                    }
                }
            });
        }

        document.querySelectorAll('#kt_app_toolbar .ki-plus-square').forEach(function(icon) {
            icon.classList.remove('ki-plus-square');
            icon.classList.add('ki-plus-circle');
        });

        document.querySelectorAll('.modal .modal-header > h2').forEach(function(title) {
            if (title.closest('.gh-modal-title-wrap')) return;

            var wrapper = document.createElement('div');
            wrapper.className = 'gh-modal-title-wrap';

            var icon = document.createElement('span');
            icon.className = 'gh-modal-title-icon';
            icon.innerHTML = '<i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i>';

            var textWrap = document.createElement('div');
            textWrap.className = 'min-w-0';

            title.parentNode.insertBefore(wrapper, title);
            wrapper.appendChild(icon);
            wrapper.appendChild(textWrap);
            textWrap.appendChild(title);
        });
    </script>

    @stack('scripts')
</body>
</html>
