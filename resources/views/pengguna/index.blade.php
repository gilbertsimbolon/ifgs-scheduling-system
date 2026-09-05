@extends('layouts.app')

@section('title', 'Pengelolaan Pengguna')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Pengguna
                </h5>
                <p class="text-muted mb-0">Kelola data pengguna sistem, hak akses peran, dan status akun.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                    <i class="bx bx-plus me-1"></i> Tambah Pengguna
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Global Validation Alert -->
        @if ($errors->any() && !old('_modal'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <h6 class="alert-heading fw-bold mb-1">Terjadi kesalahan input:</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Card -->
        <div class="card">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('pengguna.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Cari Nama atau Email</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Ketik nama atau email..." value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="role">Filter Peran</label>
                        <select name="role" id="role" class="form-select">
                            <option value="">Semua Peran</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'role', 'status']))
                            <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-heading text-truncate" style="max-width: 220px;">
                                                {{ $user->name }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge bg-label-primary">{{ $role->name }}</span>
                                    @empty
                                        <span class="badge bg-label-secondary">Tanpa Peran</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($user->status === \App\Models\User::STATUS_ACTIVE)
                                        <span class="badge bg-label-success">{{ $user->status }}</span>
                                    @else
                                        <span class="badge bg-label-danger">{{ $user->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Detail Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info"
                                            title="Detail Pengguna" data-bs-toggle="modal"
                                            data-bs-target="#modalDetailPengguna" data-name="{{ $user->name }}"
                                            data-slug="{{ $user->slug }}" data-email="{{ $user->email }}"
                                            data-role="{{ $user->roles->pluck('name')->implode(', ') ?: 'Tanpa Peran' }}"
                                            data-status="{{ $user->status }}"
                                            data-created="{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}"
                                            data-updated="{{ $user->updated_at ? $user->updated_at->format('d M Y, H:i') : '-' }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                                            title="Edit Pengguna" data-bs-toggle="modal"
                                            data-bs-target="#modalEditPengguna"
                                            data-action="{{ route('pengguna.update', $user) }}"
                                            data-name="{{ $user->name }}" data-slug="{{ $user->slug }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->roles->first()?->name ?? '' }}"
                                            data-status="{{ $user->status }}">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        <!-- Delete Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus Pengguna" data-bs-toggle="modal"
                                            data-bs-target="#modalHapusPengguna"
                                            data-action="{{ route('pengguna.destroy', $user) }}"
                                            data-name="{{ $user->name }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <i class="bx bx-user-x fs-1 mb-2"></i>
                                        <span>Tidak ada data pengguna yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                <div>
                    @if ($users->total() > 0)
                        <small class="text-muted">
                            Menampilkan <strong>{{ $users->firstItem() }}</strong> sampai
                            <strong>{{ $users->lastItem() }}</strong> dari total <strong>{{ $users->total() }}</strong>
                            pengguna
                        </small>
                    @else
                        <small class="text-muted">Total 0 pengguna</small>
                    @endif
                </div>
                @if ($users->hasPages())
                    <div>
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pengguna.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_modal" value="create">

                    <div class="modal-body">
                        @if ($errors->any() && old('_modal') === 'create')
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="tambahName">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'create') @error('name') is-invalid @enderror @endif"
                                id="tambahName" name="name" placeholder="Masukkan nama lengkap"
                                value="{{ old('_modal') === 'create' ? old('name') : '' }}" required />
                            @if (old('_modal') === 'create')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tambahEmail">Alamat Email <span
                                    class="text-danger">*</span></label>
                            <input type="email"
                                class="form-control @if (old('_modal') === 'create') @error('email') is-invalid @enderror @endif"
                                id="tambahEmail" name="email" placeholder="nama@email.com"
                                value="{{ old('_modal') === 'create' ? old('email') : '' }}" required />
                            @if (old('_modal') === 'create')
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="tambahPassword">Kata Sandi <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="tambahPassword" name="password"
                                    class="form-control @if (old('_modal') === 'create') @error('password') is-invalid @enderror @endif"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    required />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @if (old('_modal') === 'create')
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Minimal 8 karakter.</div>
                                @enderror
                            @else
                                <div class="form-text">Minimal 8 karakter.</div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="tambahRole">Peran <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-select @if (old('_modal') === 'create') @error('role') is-invalid @enderror @endif"
                                    id="tambahRole" name="role" required>
                                    <option value="">-- Pilih Peran --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}"
                                            {{ old('_modal') === 'create' && old('role') === $role ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (old('_modal') === 'create')
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="tambahStatus">Status Akun <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-select @if (old('_modal') === 'create') @error('status') is-invalid @enderror @endif"
                                    id="tambahStatus" name="status" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ (old('_modal') === 'create' ? old('status') === $status : $status === \App\Models\User::STATUS_ACTIVE) ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (old('_modal') === 'create')
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. MODAL DETAIL PENGGUNA -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalDetailPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-md mx-auto mb-2">
                            <span id="detailAvatarInitial" class="avatar-initial rounded-circle bg-label-primary fs-4">
                                --
                            </span>
                        </div>
                        <h5 id="detailNama" class="mb-0"></h5>
                        <p class="text-muted mb-0"><code id="detailSlug"></code></p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-0" style="width: 35%;">
                                        <i class="bx bx-envelope me-1 text-primary"></i> Email
                                    </th>
                                    <td>: <span id="detailEmail"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0">
                                        <i class="bx bx-shield-quarter me-1 text-primary"></i> Peran
                                    </th>
                                    <td>: <span id="detailRole" class="badge bg-label-primary"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0">
                                        <i class="bx bx-check-circle me-1 text-primary"></i> Status
                                    </th>
                                    <td>: <span id="detailStatus" class="badge bg-label-success"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0">
                                        <i class="bx bx-calendar me-1 text-primary"></i> Dibuat pada
                                    </th>
                                    <td>: <span id="detailCreatedAt" class="text-muted"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0">
                                        <i class="bx bx-time me-1 text-primary"></i> Diperbarui pada
                                    </th>
                                    <td>: <span id="detailUpdatedAt" class="text-muted"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 3. MODAL EDIT PENGGUNA -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditPengguna" action="{{ old('_action', '') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="edit">
                    <input type="hidden" name="_action" id="editActionInput" value="{{ old('_action', '') }}">

                    <div class="modal-body">
                        @if ($errors->any() && old('_modal') === 'edit')
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="editName">Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'edit') @error('name') is-invalid @enderror @endif"
                                id="editName" name="name" value="{{ old('_modal') === 'edit' ? old('name') : '' }}"
                                required />
                            @if (old('_modal') === 'edit')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="editEmail">Alamat Email <span
                                    class="text-danger">*</span></label>
                            <input type="email"
                                class="form-control @if (old('_modal') === 'edit') @error('email') is-invalid @enderror @endif"
                                id="editEmail" name="email" value="{{ old('_modal') === 'edit' ? old('email') : '' }}"
                                required />
                            @if (old('_modal') === 'edit')
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="editPassword">Kata Sandi</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="editPassword" name="password"
                                    class="form-control @if (old('_modal') === 'edit') @error('password') is-invalid @enderror @endif"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            @if (old('_modal') === 'edit')
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter
                                        jika diisi.</div>
                                @enderror
                            @else
                                <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter
                                    jika diisi.</div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="editRole">Peran <span class="text-danger">*</span></label>
                                <select
                                    class="form-select @if (old('_modal') === 'edit') @error('role') is-invalid @enderror @endif"
                                    id="editRole" name="role" required>
                                    <option value="">-- Pilih Peran --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}"
                                            {{ old('_modal') === 'edit' && old('role') === $role ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (old('_modal') === 'edit')
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="editStatus">Status Akun <span
                                        class="text-danger">*</span></label>
                                <select
                                    class="form-select @if (old('_modal') === 'edit') @error('status') is-invalid @enderror @endif"
                                    id="editStatus" name="status" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ old('_modal') === 'edit' && old('status') === $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (old('_modal') === 'edit')
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 4. MODAL KONFIRMASI HAPUS PENGGUNA -->
    <!-- ======================================================= -->
    <div class="modal fade" id="modalHapusPengguna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formHapusPengguna" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-1">
                            Apakah Anda yakin ingin menghapus pengguna <strong id="hapusPenggunaNama"></strong>?
                        </p>
                        <small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Detail: populate on show
            const modalDetail = document.getElementById('modalDetailPengguna');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const name = button.getAttribute('data-name') || '-';
                    const slug = button.getAttribute('data-slug') || '-';
                    const email = button.getAttribute('data-email') || '-';
                    const role = button.getAttribute('data-role') || '-';
                    const status = button.getAttribute('data-status') || '-';
                    const created = button.getAttribute('data-created') || '-';
                    const updated = button.getAttribute('data-updated') || '-';

                    document.getElementById('detailNama').textContent = name;
                    document.getElementById('detailSlug').textContent = slug;
                    document.getElementById('detailEmail').textContent = email;
                    document.getElementById('detailRole').textContent = role;

                    const avatarInitial = document.getElementById('detailAvatarInitial');
                    if (avatarInitial) {
                        avatarInitial.textContent = name.substring(0, 2).toUpperCase();
                    }

                    const statusEl = document.getElementById('detailStatus');
                    if (statusEl) {
                        statusEl.textContent = status;
                        statusEl.className = 'badge ' + (status === 'Active' ? 'bg-label-success' :
                            'bg-label-danger');
                    }

                    document.getElementById('detailCreatedAt').textContent = created;
                    document.getElementById('detailUpdatedAt').textContent = updated;
                });
            }

            // Modal Edit: populate on show
            const modalEdit = document.getElementById('modalEditPengguna');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return; // Reopened via validation check

                    const action = button.getAttribute('data-action') || '';
                    const name = button.getAttribute('data-name') || '';
                    const email = button.getAttribute('data-email') || '';
                    const role = button.getAttribute('data-role') || '';
                    const status = button.getAttribute('data-status') || '';

                    const form = document.getElementById('formEditPengguna');
                    if (form) {
                        form.action = action;
                    }
                    const actionInput = document.getElementById('editActionInput');
                    if (actionInput) {
                        actionInput.value = action;
                    }

                    document.getElementById('editName').value = name;
                    document.getElementById('editEmail').value = email;
                    document.getElementById('editPassword').value = '';
                    document.getElementById('editRole').value = role;
                    document.getElementById('editStatus').value = status;
                });
            }

            // Modal Hapus: populate on show
            const modalHapus = document.getElementById('modalHapusPengguna');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const action = button.getAttribute('data-action') || '';
                    const name = button.getAttribute('data-name') || '';

                    const form = document.getElementById('formHapusPengguna');
                    if (form) {
                        form.action = action;
                    }

                    const nameEl = document.getElementById('hapusPenggunaNama');
                    if (nameEl) {
                        nameEl.textContent = name;
                    }
                });
            }

            // Auto reopen modal if validation fails
            @if ($errors->any())
                @if (old('_modal') === 'create')
                    const modalTambahEl = document.getElementById('modalTambahPengguna');
                    if (modalTambahEl && typeof bootstrap !== 'undefined') {
                        const modalTambah = new bootstrap.Modal(modalTambahEl);
                        modalTambah.show();
                    }
                @elseif (old('_modal') === 'edit')
                    const formEdit = document.getElementById('formEditPengguna');
                    const prevAction = '{{ old('_action') }}';
                    if (formEdit && prevAction) {
                        formEdit.action = prevAction;
                    }
                    const modalEditEl = document.getElementById('modalEditPengguna');
                    if (modalEditEl && typeof bootstrap !== 'undefined') {
                        const modalEdit = new bootstrap.Modal(modalEditEl);
                        modalEdit.show();
                    }
                @endif
            @endif
        });
    </script>
@endpush
