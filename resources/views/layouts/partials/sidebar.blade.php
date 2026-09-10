<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- Brand Logo / Name -->
    <div class="app-brand demo d-flex flex-column align-items-center justify-content-center text-center position-relative py-4 px-3"
        style="height: auto; min-height: 135px; margin-top: 8px;">
        <a href="{{ url('/') }}"
            class="app-brand-link d-flex flex-column align-items-center text-center text-decoration-none w-100">
            <span class="app-brand-logo mb-2 d-flex justify-content-center">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="Indo Fitness Gym Sport®" class="rounded-circle shadow-sm"
                    style="width: 58px; height: 58px; object-fit: cover;" />
            </span>
            <span class="app-brand-text menu-text fw-bold text-center text-wrap"
                style="font-size: 0.92rem; line-height: 1.35; color: var(--bs-heading-color, #566a7f); white-space: normal;">
                Indo Fitness Gym Sport®
            </span>
        </a>

        <a href="javascript:void(0);"
            class="layout-menu-toggle menu-link text-large position-absolute top-0 end-0 mt-3 me-2">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
        </a>
    </div>

    <div class="menu-divider mt-0"></div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Dashboard</div>
            </a>
        </li>

        <!-- MANAJEMEN -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">MANAJEMEN</span>
        </li>
        <li class="menu-item {{ request()->is('pengguna*') ? 'active' : '' }}">
            <a href="{{ route('pengguna.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div class="text-truncate">Pengguna</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('member*') && !request()->is('memberships*') ? 'active' : '' }}">
            <a href="{{ route('member.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div class="text-truncate">Member</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('memberships*') ? 'active' : '' }}">
            <a href="{{ Route::has('memberships.index') ? route('memberships.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div class="text-truncate">Membership</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('products*') ? 'active' : '' }}">
            <a href="{{ Route::has('products.index') ? route('products.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div class="text-truncate">Paket Layanan</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('payment-methods*') ? 'active' : '' }}">
            <a href="{{ Route::has('payment-methods.index') ? route('payment-methods.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card-front"></i>
                <div class="text-truncate">Metode Pembayaran</div>
            </a>
        </li>

        <!-- KUNJUNGAN -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">KUNJUNGAN</span>
        </li>
        <li class="menu-item {{ request()->is('reservations*') ? 'active' : '' }}">
            <a href="{{ Route::has('reservations.index') ? route('reservations.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                <div class="text-truncate">Reservasi</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('schedules*') ? 'active' : '' }}">
            <a href="{{ Route::has('schedules.index') ? route('schedules.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div class="text-truncate">Jadwal Kunjungan</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('time-slots*') ? 'active' : '' }}">
            <a href="{{ Route::has('time-slots.index') ? route('time-slots.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-time-five"></i>
                <div class="text-truncate">Time Slot</div>
            </a>
        </li>

        <!-- AKTIVITAS -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">AKTIVITAS</span>
        </li>
        <li class="menu-item {{ request()->is('check-in*') ? 'active' : '' }}">
            <a href="{{ Route::has('check-in.index') ? route('check-in.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-check-circle"></i>
                <div class="text-truncate">Check-in</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('zumba*') ? 'active' : '' }}">
            <a href="{{ Route::has('zumba.index') ? route('zumba.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-run"></i>
                <div class="text-truncate">Jadwal Zumba</div>
            </a>
        </li>

        <!-- LAPORAN -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">LAPORAN</span>
        </li>
        <li class="menu-item {{ request()->is('reports*') ? 'active' : '' }}">
            <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div class="text-truncate">Laporan</div>
            </a>
        </li>

        <!-- SISTEM -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">SISTEM</span>
        </li>
        <li class="menu-item {{ request()->is('notifications*') ? 'active' : '' }}">
            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div class="text-truncate">Notifikasi</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('settings*') ? 'active' : '' }}">
            <a href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div class="text-truncate">Settings</div>
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ Route::has('logout') ? route('logout') : '#' }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div class="text-truncate">Logout</div>
            </a>
        </li>
    </ul>
</aside>
