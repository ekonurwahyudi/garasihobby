<!DOCTYPE html>
<html lang="id">
<head>
    <base href="{{ url('/') }}/" />
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Manajemen Bengkel Garasi Hobby" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos.png') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/media/logos.png') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .auth-brand-logo {
            display: block;
            width: 210px;
            max-width: 70vw;
            height: auto;
            object-fit: contain;
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

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">

            {{-- Form area --}}
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="w-lg-500px p-10">
                        @yield('content')
                    </div>
                </div>

                <div class="w-lg-500px d-flex flex-stack px-10 mx-auto">
                    <div class="text-muted fs-7">
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </div>
                </div>
            </div>

            {{-- Aside / Brand area --}}
            <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center order-1 order-lg-2"
                 style="background-image: url('{{ asset('assets/media/background.png') }}');">
                <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                    <a href="{{ route('login') }}" class="mb-0 mb-lg-12">
                        <img src="{{ asset('assets/media/logos.png') }}" alt="Garasi Hobby" class="auth-brand-logo" />
                    </a>
                    <h1 class="d-none d-lg-block text-white fs-2qx fw-bolder text-center mb-7">
                        Sistem Manajemen Bengkel
                    </h1>
                    <div class="d-none d-lg-block text-white fs-base text-center px-10">
                        Kelola order, mekanik, QC, pembayaran, dan invoice dalam satu platform terpadu.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>var hostUrl = "{{ asset('assets/') }}/";</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    @stack('scripts')
</body>
</html>
