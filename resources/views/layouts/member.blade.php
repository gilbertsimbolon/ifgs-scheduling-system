<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-compact"
    data-assets-path="{{ asset('sneat/assets') }}/">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') |
        @endif{{ 'Member IFGS - Indo Fitness Gym Sport®' }}
    </title>

    <meta name="description"
        content="Sistem Informasi Penjadwalan & Layanan Gym Indo Fitness Gym Sport Tondano" />

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo-ifgs.jpg') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif !important;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        .member-viewport-wrapper {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
        }

        /* Top Header */
        .member-top-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1030;
            padding: 10px 16px;
        }

        /* Content Area */
        .member-content {
            flex: 1;
            padding: 16px;
            padding-bottom: 95px;
            /* Memberikan ruang aman agar tidak tertutup bottom nav */
        }

        i.bx,
        i.icon-base {
            vertical-align: -0.125em;
            line-height: 1;
            display: inline-block;
        }

        .btn {
            font-family: Arial, Helvetica, sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn i.bx {
            margin-top: -1px;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
            border-radius: 12px;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 16px;
        }

        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 16px;
        }

        .badge {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 600;
        }

        /* Mobile Viewport Optimizations */
        @media (max-width: 575.98px) {
            .member-viewport-wrapper {
                box-shadow: none;
            }

            .member-content {
                padding: 14px 12px 95px 12px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="member-viewport-wrapper">
        <!-- Top App Bar Khusus Member -->
        <header class="member-top-bar d-flex align-items-center justify-content-between">
            <a href="{{ route('member.index') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="Indo Fitness Gym Sport®" width="34" height="34"
                    class="rounded-circle shadow-sm object-fit-cover" />
                <h6 class="mb-0 fw-bold text-dark lh-1" style="font-size: 0.95rem; letter-spacing: -0.2px;">
                    Indo Fitness Gym Sport®
                </h6>
            </a>

            <div class="d-flex align-items-center gap-2">
                @if (auth()->check())
                    <!-- Avatar Profil Member (Inisial seperti di panel admin) -->
                    <a href="{{ route('member.profil') }}" class="d-inline-flex text-decoration-none" title="Profil Saya">
                        <div class="avatar avatar-sm" style="width: 36px; height: 36px;">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                    class="rounded-circle object-fit-cover border" width="36" height="36">
                            @else
                                <span class="avatar-initial rounded-circle bg-label-primary fw-bold"
                                    style="width: 36px; height: 36px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">
                                    {{ auth()->user()->initials }}
                                </span>
                            @endif
                        </div>
                    </a>

                    <!-- Icon Logout Langsung di Samping Avatar (Tanpa Dropdown) -->
                    <form action="{{ route('logout') }}" method="POST" class="m-0 d-inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun?');">
                        @csrf
                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger rounded-circle border-0 shadow-none d-flex align-items-center justify-content-center"
                            style="width: 36px; height: 36px; background-color: rgba(255, 62, 29, 0.08);" title="Keluar (Logout)">
                            <i class="bx bx-power-off fs-5 text-danger"></i>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="member-content">
            <!-- Alert Session Notifications -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3"
                    role="alert" style="font-size: 0.85rem;">
                    <i class="bx bx-check-circle fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3"
                    role="alert" style="font-size: 0.85rem;">
                    <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert"
                    style="font-size: 0.85rem;">
                    <div class="d-flex align-items-center mb-1">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <strong>Perhatian:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Bottom Navigation Bar Mobile App -->
        @include('member-portal.partials.bottom-nav')
    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    @stack('scripts')
</body>

</html>
