<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- Brand Logo / Name -->
    <div class="app-brand demo d-flex flex-column align-items-center justify-content-center text-center position-relative py-4 px-3"
        style="height: auto; min-height: 135px; margin-top: 8px;">
        <a href="{{ route('dashboard') }}"
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
        <!-- 1. DASHBOARD -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate">Dashboard</div>
            </a>
        </li>

        @hasanyrole('Admin/Manager|Kasir')
        <!-- 2. MANAJEMEN -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">MANAJEMEN</span>
        </li>
        <li class="menu-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
            <a href="{{ route('pengguna.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div class="text-truncate">Pengguna</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('memberships.*') ? 'active' : '' }}">
            <a href="{{ route('memberships.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-id-card"></i>
                <div class="text-truncate">Membership</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div class="text-truncate">Paket Layanan</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
            <a href="{{ route('payment-methods.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card-front"></i>
                <div class="text-truncate">Metode Pembayaran</div>
            </a>
        </li>
        @hasrole('Admin/Manager')
        <li class="menu-item {{ request()->routeIs('time-slots.*') ? 'active' : '' }}">
            <a href="{{ route('time-slots.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-time-five"></i>
                <div class="text-truncate">Jadwal Operasional</div>
            </a>
        </li>
        @endhasrole
        @endhasanyrole

        <!-- 3. KUNJUNGAN (Core Flow Algoritma Greedy) -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">KUNJUNGAN</span>
        </li>
        <li class="menu-item {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
            <a href="{{ route('reservations.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                <div class="text-truncate">Reservasi</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
            <a href="{{ route('schedules.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div class="text-truncate">Jadwal Kunjungan</div>
            </a>
        </li>

        <li class="menu-divider my-2"></li>

        <!-- Logout -->
        <li class="menu-item">
            <a href="javascript:void(0);"
                onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
                class="menu-link text-danger">
                <i class="menu-icon tf-icons bx bx-log-out text-danger"></i>
                <div class="text-truncate">Keluar (Logout)</div>
            </a>
            <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</aside>
