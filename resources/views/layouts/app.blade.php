<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed layout-compact"
    data-assets-path="{{ asset('sneat/assets') }}/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') |
        @endif{{ 'Indo Fitness Gym Sport®' }}
    </title>

    <meta name="description"
        content="Sistem Informasi Penjadwalan Kunjungan Member pada Indo Fitness Gym Sport Tondano Berbasis Web" />

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

    <!-- Page CSS -->
    @stack('page-css')
    <!-- Penyelarasan Global Ikon & Teks -->
    <style>
        /* Standar Alignment Ikon Vektor Boxicons */
        i.bx,
        i.icon-base {
            vertical-align: -0.125em;
            line-height: 1;
            display: inline-block;
        }

        /* Tombol: Penyelarasan Ikon & Teks Rata Tengah Sempurna */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
        }

        .btn i.bx,
        .btn i.icon-base {
            vertical-align: middle;
            line-height: 1;
            margin-top: -1px;
        }

        /* Tombol Khusus Icon-Only */
        .btn-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
        }

        .btn-icon i.bx,
        .btn-icon i.icon-base {
            margin: 0 !important;
            line-height: 1 !important;
        }

        /* Badge: Teks & Ikon Sejajar Rata Tengah */
        .badge {
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
        }

        .badge i.bx,
        .badge i.icon-base {
            vertical-align: middle;
            line-height: 1;
        }

        /* Header Kartu & Judul Modal */
        .card-title,
        .modal-title {
            display: inline-flex;
            align-items: center;
            line-height: 1.3;
        }

        .card-title i.bx,
        .modal-title i.bx,
        .card-title i.icon-base,
        .modal-title i.icon-base {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        /* Menu Dropdown */
        .dropdown-item {
            display: flex;
            align-items: center;
        }

        .dropdown-item i.bx,
        .dropdown-item i.icon-base {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        /* Menu Sidebar */
        .menu-link {
            display: flex;
            align-items: center;
        }

        .menu-link i.menu-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Input Group Addon Icon */
        .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group-text i.bx,
        .input-group-text i.icon-base {
            line-height: 1;
        }

        /* Alert Icon */
        .alert i.bx,
        .alert i.icon-base {
            line-height: 1;
        }

        /* ==========================================================================
           Pengecilan Tipografi & Elemen UI pada Layar Ponsel (Mobile Responsive)
           Dikecilkan secara proporsional agar pas, rapi, dan tidak memakan layar HP.
           Penyelarasan Tipografi & Elemen UI pada Layar Ponsel (Mobile Responsive)
           Skala ideal (14px root): ringkas, proporsional, dan nyaman dibaca di HP.
           ========================================================================== */
        @media (max-width: 767.98px) {
            html {
                font-size: 11.5px !important;
                font-size: 14px !important;
            }

            body {
                font-size: 0.82rem !important;
                line-height: 1.35 !important;
                font-size: 0.9375rem !important; /* ~13.1px */
                line-height: 1.4 !important;
            }

            /* Judul & Headings */
            h1,
            .h1 {
                font-size: 1.35rem !important;
            }
            h1, .h1 { font-size: 1.75rem !important; }
            h2, .h2 { font-size: 1.5rem !important; }
            h3, .h3 { font-size: 1.35rem !important; }
            h4, .h4 { font-size: 1.2rem !important; }
            h5, .h5 { font-size: 1.05rem !important; }
            h6, .h6 { font-size: 0.95rem !important; }

            h2,
            .h2 {
                font-size: 1.2rem !important;
            }

            h3,
            .h3 {
                font-size: 1.1rem !important;
            }

            h4,
            .h4 {
                font-size: 1rem !important;
            }

            h5,
            .h5 {
                font-size: 0.9rem !important;
            }

            h6,
            .h6 {
                font-size: 0.8rem !important;
            }

            /* Utility Font Classes */
            .fs-1 {
                font-size: 1.35rem !important;
            }
            .fs-1 { font-size: 1.75rem !important; }
            .fs-2 { font-size: 1.5rem !important; }
            .fs-3 { font-size: 1.35rem !important; }
            .fs-4 { font-size: 1.2rem !important; }
            .fs-5 { font-size: 1.05rem !important; }
            .fs-6 { font-size: 0.95rem !important; }

            .fs-2 {
                font-size: 1.2rem !important;
            }

            .fs-3 {
                font-size: 1.1rem !important;
            }

            .fs-4 {
                font-size: 1rem !important;
            }

            .fs-5 {
                font-size: 0.9rem !important;
            }

            .fs-6 {
                font-size: 0.8rem !important;
            }

            /* Form Elements */
            .form-control,
            .form-select {
                font-size: 0.82rem !important;
                padding: 0.35rem 0.65rem !important;
            .form-control, .form-select {
                font-size: 0.875rem !important;
                padding: 0.4375rem 0.75rem !important;
            }

            .form-label {
                font-size: 0.76rem !important;
                margin-bottom: 0.2rem !important;
                font-size: 0.82rem !important;
                margin-bottom: 0.25rem !important;
            }

            /* Tombol */
            .btn {
                font-size: 0.78rem !important;
                padding: 0.35rem 0.65rem !important;
                font-size: 0.875rem !important;
                padding: 0.4375rem 0.875rem !important;
            }

            .btn-sm {
                font-size: 0.72rem !important;
                padding: 0.22rem 0.45rem !important;
                font-size: 0.78rem !important;
                padding: 0.28rem 0.55rem !important;
            }

            /* Badges */
            .badge {
                font-size: 0.7rem !important;
                padding: 0.25em 0.5em !important;
                font-size: 0.78rem !important;
                padding: 0.35em 0.6em !important;
            }

            /* Tabel */
            .table th,
            .table td {
                font-size: 0.76rem !important;
                padding: 0.4rem 0.5rem !important;
            .table th, .table td {
                font-size: 0.82rem !important;
                padding: 0.5rem 0.65rem !important;
            }

            /* Kartu & Container */
            .card-header {
                padding: 0.75rem 1rem !important;
                padding: 0.85rem 1.15rem !important;
            }

            .card-body {
                padding: 0.85rem !important;
                padding: 1rem !important;
            }

            /* Dropdown & Alert */
            .dropdown-item {
                font-size: 0.8rem !important;
                padding: 0.35rem 0.75rem !important;
                font-size: 0.875rem !important;
                padding: 0.45rem 0.85rem !important;
            }

            .alert {
                font-size: 0.8rem !important;
                padding: 0.5rem 0.75rem !important;
                font-size: 0.875rem !important;
                padding: 0.65rem 0.85rem !important;
            }

            /* Drawer Menu Sidebar */
            .layout-menu {
                width: 260px !important;
            }

            .layout-menu .menu-link {
                font-size: 0.85rem !important;
                font-size: 0.9rem !important;
            }
        }
    </style>
    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('sneat/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('sneat/assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu / Sidebar -->
            @include('layouts.partials.sidebar')
            <!-- / Menu / Sidebar -->

            <!-- Layout page -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.partials.navbar')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    @yield('content')
                    <!-- / Content -->

                    <!-- Footer (Akan dikerjakan pada tahap berikutnya) -->
                    <!-- Footer -->
                    @include('layouts.partials.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('sneat/assets/js/main.js') }}"></script>

    <!-- Page JS -->
    @stack('page-js')
    @stack('scripts')
</body>

</html>
