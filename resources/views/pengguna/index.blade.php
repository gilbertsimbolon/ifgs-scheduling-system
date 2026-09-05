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
                                    {{ $status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Tidak Aktif' }}
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
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer toggle-status-switch"
                                                type="checkbox" role="switch"
                                                id="switchStatus{{ $user->id }}"
                                                data-action="{{ route('pengguna.toggle-status', $user) }}"
                                                title="Klik untuk on/off status akun"
                                                {{ $user->status === \App\Models\User::STATUS_ACTIVE ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label cursor-pointer mb-0" for="switchStatus{{ $user->id }}">
                                            @if ($user->status === \App\Models\User::STATUS_ACTIVE)
                                                <span class="badge bg-label-success status-badge">Aktif</span>
                                            @else
                                                <span class="badge bg-label-danger status-badge">Tidak Aktif</span>
                                            @endif
                                        </label>
                                    </div>
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
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-user-x fs-1 text-secondary"></i>
                                        </div>
                                        <span class="text-muted">Tidak ada data pengguna yang ditemukan.</span>
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

    <!-- Modal Tambah Pengguna -->
    @include('pengguna.modals.tambah')

    <!-- Modal Detail Pengguna -->
    @include('pengguna.modals.detail')

    <!-- Modal Edit Pengguna -->
    @include('pengguna.modals.edit')

    <!-- Modal Hapus Pengguna -->
    @include('pengguna.modals.hapus')
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
                        statusEl.textContent = status === 'Active' ? 'Aktif' : (status === 'Inactive' ?
                            'Tidak Aktif' : status);
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

            // Toggle Status On/Off Switch
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            document.querySelectorAll('.toggle-status-switch').forEach(function(switchEl) {
                switchEl.addEventListener('change', function() {
                    const currentSwitch = this;
                    const action = currentSwitch.getAttribute('data-action');
                    const badgeEl = currentSwitch.closest('td')?.querySelector('.status-badge');
                    const isChecked = currentSwitch.checked;

                    currentSwitch.disabled = true;

                    fetch(action, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal memperbarui status');
                        }
                        return response.json();
                    })
                    .then(data => {
                        currentSwitch.disabled = false;
                        if (data.success) {
                            if (badgeEl) {
                                badgeEl.textContent = data.label;
                                badgeEl.className = 'badge status-badge ' + (data.status === 'Active' ? 'bg-label-success' : 'bg-label-danger');
                            }

                            const tr = currentSwitch.closest('tr');
                            if (tr) {
                                const detailBtn = tr.querySelector('[data-bs-target="#modalDetailPengguna"]');
                                if (detailBtn) detailBtn.setAttribute('data-status', data.status);

                                const editBtn = tr.querySelector('[data-bs-target="#modalEditPengguna"]');
                                if (editBtn) editBtn.setAttribute('data-status', data.status);
                            }
                        } else {
                            currentSwitch.checked = !isChecked;
                        }
                    })
                    .catch(error => {
                        currentSwitch.disabled = false;
                        currentSwitch.checked = !isChecked;
                        alert('Terjadi kesalahan saat mengubah status pengguna.');
                    });
                });
            });

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
