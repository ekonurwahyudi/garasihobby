<!DOCTYPE html>
<html lang="id">
<head>
    <base href="{{ url('/') }}/" />
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Manajemen Bengkel Garasi Hobby" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" type="image/png" href="{{ asset('assets/media/favicon.png') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/media/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Onest', sans-serif;
            background: #f5f8fc;
        }
        .auth-brand-logo {
            display: block;
            width: 190px;
            max-width: 70vw;
            height: auto;
            object-fit: contain;
        }
        .auth-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at 18% 12%, rgba(27,132,255,.12), transparent 28%),
                radial-gradient(circle at 86% 84%, rgba(18,161,80,.12), transparent 30%),
                #f7faff;
        }
        .auth-form-pane {
            position: relative;
        }
        .auth-form-card {
            width: min(100%, 460px);
            border: 1px solid #e4e8f0;
            border-radius: 24px;
            background: rgba(255,255,255,.92);
            box-shadow: 0 24px 70px rgba(15,23,42,.10);
            padding: 34px;
        }
        .auth-aside {
            position: relative;
            overflow: hidden;
            background-size: cover;
            background-position: center;
        }
        .auth-aside::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15,23,42,.84), rgba(27,132,255,.56));
        }
        .auth-aside::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            right: -120px;
            bottom: -120px;
            border-radius: 50%;
            background: rgba(255,255,255,.12);
        }
        .auth-aside-content {
            position: relative;
            z-index: 1;
        }
        .auth-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255,255,255,.20);
            border-radius: 16px;
            background: rgba(255,255,255,.10);
            color: #fff;
            padding: 14px 16px;
            backdrop-filter: blur(10px);
        }
        .auth-feature-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(255,255,255,.16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px;
        }
        .auth-footer {
            width: min(100%, 460px);
        }
        @media (max-width: 991.98px) {
            .auth-aside {
                min-height: 320px;
            }
            .auth-form-card {
                padding: 26px;
            }
        }
    </style>

    @stack('styles')
</head>
<body id="kt_body" class="app-blank">
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

    <div class="d-flex flex-column flex-root auth-shell" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">

            {{-- Form area --}}
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-6 p-lg-10 order-2 order-lg-1 auth-form-pane">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="auth-form-card">
                        @yield('content')
                    </div>
                </div>

                <div class="auth-footer d-flex flex-stack px-2 px-lg-0 mx-auto mt-6">
                    <div class="text-muted fs-7">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </div>
                </div>
            </div>

            {{-- Aside / Brand area --}}
            <div class="d-flex flex-lg-row-fluid w-lg-50 order-1 order-lg-2 auth-aside"
                 style="background-image: url('{{ asset('assets/media/background.png') }}');">
                <div class="auth-aside-content d-flex flex-column justify-content-center py-10 py-lg-15 px-6 px-md-15 w-100">
                    <a href="{{ route('login') }}" class="mb-0 mb-lg-12">
                        <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby" class="auth-brand-logo" />
                    </a>
                    <h1 class="text-white fs-2qx fw-bolder mb-5">
                        Sistem Manajemen Bengkel
                    </h1>
                    <div class="text-white fs-base opacity-75 mb-8 mw-500px">
                        Kelola order, mekanik, QC, pembayaran, dan invoice dalam satu platform terpadu.
                    </div>
                    <div class="d-grid gap-4 mw-500px">
                        <div class="auth-feature">
                            <span class="auth-feature-icon"><i class="ki-duotone ki-chart-line-up fs-2 text-white"><span class="path1"></span><span class="path2"></span></i></span>
                            <div><div class="fw-bold">Pantau performa harian</div><div class="fs-8 opacity-75">Order, revenue, dan approval dalam satu dashboard.</div></div>
                        </div>
                        <div class="auth-feature">
                            <span class="auth-feature-icon"><i class="ki-duotone ki-wallet fs-2 text-white"><span class="path1"></span><span class="path2"></span></i></span>
                            <div><div class="fw-bold">Keuangan lebih terkendali</div><div class="fs-8 opacity-75">Mutasi bank, hutang piutang, dan aset tercatat rapi.</div></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('layouts.partials.global-loading')

    <script>var hostUrl = "{{ asset('assets/') }}/";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    @stack('scripts')
</body>
</html>
