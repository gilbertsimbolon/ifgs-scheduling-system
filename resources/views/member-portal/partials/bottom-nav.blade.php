<div class="member-bottom-nav fixed-bottom bg-white border-top">
    <div class="container d-flex justify-content-around align-items-center position-relative px-1" style="max-width: 600px; height: 64px;">
        <!-- 1. Reservasi -->
        <a href="{{ route('member.reservasi') }}" class="nav-item-btn text-decoration-none {{ request()->routeIs('member.reservasi*') ? 'active' : '' }}">
            <i class="bx bx-calendar-event nav-icon"></i>
            <span class="nav-label">Reservasi</span>
        </a>

        <!-- 2. Riwayat -->
        <a href="{{ route('member.riwayat') }}" class="nav-item-btn text-decoration-none {{ request()->routeIs('member.riwayat*') ? 'active' : '' }}">
            <i class="bx bx-history nav-icon"></i>
            <span class="nav-label">Riwayat</span>
        </a>

        <!-- 3. Floating Central Main Button: Beranda / Home -->
        <div class="nav-item-center-wrapper">
            <a href="{{ route('member.index') }}" class="btn-floating-center {{ request()->routeIs('member.index') ? 'active' : '' }}" title="Beranda Member">
                <i class="bx bx-home-alt"></i>
            </a>
            <span class="nav-label-center {{ request()->routeIs('member.index') ? 'text-primary fw-bold' : '' }}">Beranda</span>
        </div>

        <!-- 4. Paket Layanan -->
        <a href="{{ route('member.paket-layanan') }}" class="nav-item-btn text-decoration-none {{ request()->routeIs('member.paket-layanan*') ? 'active' : '' }}">
            <i class="bx bx-package nav-icon"></i>
            <span class="nav-label">Paket</span>
        </a>

        <!-- 5. Profil -->
        <a href="{{ route('member.profil') }}" class="nav-item-btn text-decoration-none {{ request()->routeIs('member.profil*') ? 'active' : '' }}">
            <i class="bx bx-user nav-icon"></i>
            <span class="nav-label">Profil</span>
        </a>
    </div>
</div>

<style>
    .member-bottom-nav {
        z-index: 1045;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: 0 -3px 16px rgba(15, 23, 42, 0.06);
    }

    .nav-item-btn {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 6px 2px;
        color: #64748b;
        transition: color 0.15s ease, transform 0.15s ease;
        text-align: center;
        min-width: 0;
    }

    .nav-item-btn:hover {
        color: #696cff;
    }

    .nav-item-btn.active {
        color: #696cff;
        font-weight: 700;
    }

    .nav-item-btn .nav-icon {
        font-size: 1.35rem;
        line-height: 1.2;
        margin-bottom: 2px;
        display: block;
    }

    .nav-item-btn.active .nav-icon {
        transform: translateY(-1px);
    }

    .nav-label {
        font-size: 0.68rem;
        letter-spacing: -0.2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        line-height: 1.1;
    }

    .nav-item-center-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        position: relative;
        text-align: center;
        padding-bottom: 4px;
    }

    .btn-floating-center {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #696cff 0%, #5356e3 100%);
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        box-shadow: 0 6px 18px rgba(105, 108, 255, 0.42);
        margin-top: -24px;
        margin-bottom: 2px;
        text-decoration: none;
        border: 3px solid #ffffff;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
    }

    .btn-floating-center:hover {
        transform: scale(1.08) translateY(-2px);
        box-shadow: 0 8px 22px rgba(105, 108, 255, 0.55);
        color: #ffffff;
    }

    .btn-floating-center:active {
        transform: scale(0.96);
    }

    .btn-floating-center.active {
        background: linear-gradient(135deg, #5356e3 0%, #3e41c4 100%);
        box-shadow: 0 6px 20px rgba(83, 86, 227, 0.5);
    }

    .nav-label-center {
        font-size: 0.68rem;
        color: #64748b;
        letter-spacing: -0.2px;
        line-height: 1.1;
    }
</style>

