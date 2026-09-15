@php
    $authUser = auth()->user();
    $userName = $authUser ? $authUser->name : 'Admin';
    $userInitials = strtoupper(substr($userName, 0, 2));
    $userRole = $authUser ? $authUser->roles->first()?->name ?? 'Administrator' : 'Administrator';
    $userAvatarUrl = $authUser ? $authUser->avatar_url : null;
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <!-- Sidebar Toggle (Mobile / Tablet) -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end w-100" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- User Profile Dropdown -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        @if ($userAvatarUrl)
                            <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}"
                                class="w-px-40 h-px-40 rounded-circle object-fit-cover" />
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                {{ $userInitials }}
                            </span>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('profile.show') }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        @if ($userAvatarUrl)
                                            <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}"
                                                class="w-px-40 h-px-40 rounded-circle object-fit-cover" />
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                {{ $userInitials }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-semibold text-heading">{{ $userName }}</h6>
                                    <small class="text-muted">{{ $userRole }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="icon-base bx bx-user icon-md me-3 text-primary"></i><span>Profil Saya</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="navbar-logout-form">
                            @csrf
                            <button type="submit"
                                class="dropdown-item cursor-pointer border-0 bg-transparent w-100 text-start text-danger">
                                <i class="icon-base bx bx-power-off icon-md me-3 text-danger"></i><span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            <!-- /User Profile Dropdown -->
        </ul>
    </div>
</nav>
