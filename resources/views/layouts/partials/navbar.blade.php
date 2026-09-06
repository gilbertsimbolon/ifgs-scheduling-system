<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <!-- Sidebar Toggle (Mobile / Tablet) -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
        <!-- Search Area -->
        <div class="navbar-nav align-items-center me-auto">
            <div class="nav-item d-flex align-items-center">
                <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none"
                    placeholder="Search..." aria-label="Search..." />
            </div>
        </div>
        <!-- /Search Area -->

        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <!-- Notification Dropdown -->
            <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-expanded="false">
                    <span class="position-relative">
                        <i class="icon-base bx bx-bell icon-md"></i>
                        <span class="badge rounded-pill bg-danger badge-notifications">3</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 22rem;">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h6 class="mb-0 me-auto">Notifikasi</h6>
                            <span class="badge rounded-pill bg-label-primary">3 Baru</span>
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-success">
                                                <i class="bx bx-calendar-event"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="small mb-1 fw-semibold">Reservasi Baru</h6>
                                        <small class="text-body-secondary d-block">Member melakukan reservasi
                                            kunjungan</small>
                                        <small class="text-body-secondary">5 menit yang lalu</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-info">
                                                <i class="bx bx-calendar-check"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="small mb-1 fw-semibold">Jadwal Kunjungan Diperbarui</h6>
                                        <small class="text-body-secondary d-block">Slot waktu sesi sore telah
                                            diperbarui</small>
                                        <small class="text-body-secondary">1 jam yang lalu</small>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-warning">
                                                <i class="bx bx-credit-card"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="small mb-1 fw-semibold">Membership Akan Berakhir</h6>
                                        <small class="text-body-secondary d-block">3 member masa aktif tersisa 3
                                            hari</small>
                                        <small class="text-body-secondary">1 hari yang lalu</small>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li class="border-top">
                        <div class="d-grid p-2">
                            <a class="btn btn-primary btn-sm d-flex justify-content-center" href="javascript:void(0);">
                                <small>Lihat Semua Notifikasi</small>
                            </a>
                        </div>
                    </li>
                </ul>
            </li>
            <!-- /Notification Dropdown -->

            <!-- User Profile Dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('sneat/assets/img/avatars/1.png') }}" alt="User Avatar"
                            class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('profile.show') ? route('profile.show') : (Route::has('profile.edit') ? route('profile.edit') : '#') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('sneat/assets/img/avatars/1.png') }}" alt="User Avatar"
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ auth()->check() ? auth()->user()->name : 'Admin' }}</h6>
                                    <small
                                        class="text-body-secondary">{{ auth()->check() ? auth()->user()->roles->first()?->name ?? 'Administrator' : 'Administrator' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('profile.show') ? route('profile.show') : (Route::has('profile.edit') ? route('profile.edit') : '#') }}">
                            <i class="icon-base bx bx-user icon-md me-3"></i><span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}">
                            <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="navbar-logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item cursor-pointer border-0 bg-transparent w-100 text-start">
                                <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            <!-- /User Profile Dropdown -->
        </ul>
    </div>
</nav>
