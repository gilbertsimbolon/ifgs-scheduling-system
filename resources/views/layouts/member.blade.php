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
        content="Member Portal Sistem Informasi Penjadwalan & Layanan Gym Indo Fitness Gym Sport Tondano" />

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
            background-color: #f8fafc;
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
            padding-bottom: 95px; /* Memberikan ruang aman agar tidak tertutup bottom nav */
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
            font-family: Arial, Helvetica, sans-serif;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn i.bx {
            margin-top: -1px;
        }

        .card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
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
    <!-- Top Navbar Khusus Member (Bersih, Tanpa Sidebar Backoffice Admin) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top py-2">
        <div class="container-xl d-flex align-items-center justify-content-between">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" width="40" height="40"
    <div class="member-viewport-wrapper">
        <!-- Top App Bar Khusus Member -->
        <header class="member-top-bar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" width="34" height="34"
                    class="rounded-circle shadow-sm object-fit-cover" />
                <div class="d-flex flex-column">
                    <span class="fw-bold text-dark fs-6 mb-0"
                        style="font-family: Arial, Helvetica, sans-serif; letter-spacing: -0.2px;">
                        Indo Fitness Gym Sport®
                    </span>
                    <small class="text-muted" style="font-size: 0.72rem; font-family: Arial, Helvetica, sans-serif;">
                        Portal Profil Member
                    </small>
                <div>
                    <h6 class="mb-0 fw-bold text-dark lh-1" style="font-size: 0.95rem; letter-spacing: -0.2px;">
                        IFGS Sport
                    </h6>
                    <small class="text-muted" style="font-size: 0.7rem;">Member Portal</small>
                </div>
            </a>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <a href="{{ route('home') }}"
                    class="btn btn-outline-primary btn-sm d-inline-flex align-items-center shadow-sm"
                    style="font-family: Arial, Helvetica, sans-serif;">
                    <i class="bx bx-home-alt me-1"></i> Kembali ke Beranda
                </a>

            <div class="d-flex align-items-center gap-2">
                @if (auth()->check())
                    <div class="dropdown">
                        <button
                            class="btn btn-light border btn-sm d-flex align-items-center gap-2 shadow-sm rounded-pill px-3 py-1"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="font-family: Arial, Helvetica, sans-serif;">
                        <button class="btn btn-light btn-sm border-0 p-1 rounded-circle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Menu Pengguna">
                            @if (auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                    class="rounded-circle object-fit-cover" width="28" height="28">
                                    class="rounded-circle object-fit-cover" width="30" height="30">
                            @else
                                <span
                                    class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px; font-size: 0.75rem;">
                                <span class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center"
                                    style="width: 30px; height: 30px; font-size: 0.75rem;">
                                    {{ auth()->user()->initials }}
                                </span>
                            @endif
                            <span
                                class="fw-semibold text-dark small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                            <i class="bx bx-chevron-down text-muted small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                            style="font-family: Arial, Helvetica, sans-serif;">
                            style="font-family: Arial, Helvetica, sans-serif; font-size: 0.85rem;">
                            <li>
                                <h6 class="dropdown-header">Akun Saya</h6>
                                <div class="px-3 py-2 border-bottom">
                                    <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">{{ auth()->user()->name }}</div>
                                    <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">{{ auth()->user()->email }}</small>
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('home') }}">
                                    <i class="bx bx-home-alt me-2 text-primary"></i> Beranda Utama
                                <a class="dropdown-item py-2" href="{{ route('member.profil') }}">
                                    <i class="bx bx-user me-2 text-primary"></i> Pengaturan Profil
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item py-2" href="{{ route('home') }}">
                                    <i class="bx bx-globe me-2 text-info"></i> Landing Page Publik
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                        <i class="bx bx-log-out me-2"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </nav>
        </header>

    <!-- Main Content Area -->
    <main class="py-4" style="min-height: calc(100vh - 130px);">
        @yield('content')
    </main>
        <!-- Main Content Area -->
        <main class="member-content">
            <!-- Alert Session Notifications -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="font-size: 0.85rem;">
                    <i class="bx bx-check-circle fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted small"
        style="font-family: Arial, Helvetica, sans-serif;">
        <div class="container-xl">
            &copy; {{ date('Y') }} Indo Fitness Gym Sport®. Sistem Informasi Manajemen & Penjadwalan Gym.
        </div>
    </footer>
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="font-size: 0.85rem;">
                    <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="font-size: 0.85rem;">
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

