@extends('layouts.app')

@section('title', 'Member')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Member
                </h5>
                <p class="text-muted mb-0">Kelola data member yang terdaftar pada sistem.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMember">
                    <i class="bx bx-plus me-1"></i> Tambah Member
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
                <form action="{{ route('member.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label" for="search">Cari Member</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Cari nama, email, kode member, atau no. HP..."
                                value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ $status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Tidak Aktif' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('member.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Member -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>No. HP</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $members->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge bg-label-secondary font-monospace">{{ $member->member_code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($member->user?->name ?? 'M', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-heading text-truncate" style="max-width: 220px;">
                                                {{ $member->user?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="{{ $member->phone ? '' : 'text-muted' }}">
                                        {{ $member->phone ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $member->user?->email ?? '-' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer toggle-status-switch"
                                                type="checkbox" role="switch" id="switchStatus{{ $member->id }}"
                                                data-action="{{ route('member.toggle-status', $member) }}"
                                                title="Klik untuk on/off status akun"
                                                {{ ($member->user?->status ?? '') === \App\Models\User::STATUS_ACTIVE ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label cursor-pointer mb-0"
                                            for="switchStatus{{ $member->id }}">
                                            @if (($member->user?->status ?? '') === \App\Models\User::STATUS_ACTIVE)
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
                                        @php
                                            $activeMem = $member->activeMembership();
                                            $memText = $activeMem
                                                ? $activeMem->product?->name .
                                                    ' (s.d. ' .
                                                    $activeMem->end_date->format('d M Y') .
                                                    ')'
                                                : 'Belum Ada / Tidak Aktif';
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info"
                                            title="Detail Member" data-bs-toggle="modal" data-bs-target="#modalDetailMember"
                                            data-name="{{ $member->user?->name ?? '-' }}"
                                            data-code="{{ $member->member_code }}"
                                            data-email="{{ $member->user?->email ?? '-' }}"
                                            data-phone="{{ $member->phone ?? '-' }}"
                                            data-status="{{ $member->user?->status ?? '-' }}"
                                            data-membership="{{ $memText }}"
                                            data-created="{{ $member->created_at ? $member->created_at->format('d M Y, H:i') : '-' }}"
                                            data-updated="{{ $member->updated_at ? $member->updated_at->format('d M Y, H:i') : '-' }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                                            title="Edit Member" data-bs-toggle="modal" data-bs-target="#modalEditMember"
                                            data-action="{{ route('member.update', $member) }}"
                                            data-code="{{ $member->member_code }}"
                                            data-name="{{ $member->user?->name ?? '' }}"
                                            data-email="{{ $member->user?->email ?? '' }}"
                                            data-phone="{{ $member->phone ?? '' }}"
                                            data-status="{{ $member->user?->status ?? 'Active' }}">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        <!-- Delete Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus Member" data-bs-toggle="modal" data-bs-target="#modalHapusMember"
                                            data-action="{{ route('member.destroy', $member) }}"
                                            data-name="{{ $member->user?->name ?? '-' }}"
                                            data-code="{{ $member->member_code }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-group fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Belum ada member</h6>
                                        <span class="text-muted">Belum ada member yang terdaftar pada sistem.</span>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'status']))
                                                Tidak ada data member yang ditemukan
                                            @else
                                                Belum ada member
                                            @endif
                                        </h6>
                                        <span class="text-muted">
                                            @if (request()->hasAny(['search', 'status']))
                                                Coba ubah kata kunci pencarian atau bersihkan filter.
                                            @else
                                                Belum ada member yang terdaftar pada sistem.
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">
                    @if ($members->total() > 0)
                        Menampilkan {{ $members->firstItem() }}–{{ $members->lastItem() }} dari {{ $members->total() }} member
                    @else
                        Tidak ada data member
                    @endif
                </small>
                @if ($members->hasPages())
                    {{ $members->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Tambah Member (Pilih Pengguna) -->
    @include('member.modals.tambah')

    <!-- Modal Tambah Pengguna Baru (Jika belum ada di User) -->
    @include('member.modals.tambah-user')

    <!-- Modal Detail Member -->
    @include('member.modals.detail')

    <!-- Modal Edit Member -->
    @include('member.modals.edit')

    <!-- Modal Hapus Member -->
    @include('member.modals.hapus')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Detail: populate on show
            const modalDetail = document.getElementById('modalDetailMember');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const name = button.getAttribute('data-name') || '-';
                    const code = button.getAttribute('data-code') || '-';
                    const email = button.getAttribute('data-email') || '-';
                    const phone = button.getAttribute('data-phone') || '-';
                    const status = button.getAttribute('data-status') || '-';
                    const membership = button.getAttribute('data-membership') || '-';
                    const created = button.getAttribute('data-created') || '-';
                    const updated = button.getAttribute('data-updated') || '-';

                    document.getElementById('detailNama').textContent = name;
                    document.getElementById('detailKodeMember').textContent = code;
                    document.getElementById('detailEmail').textContent = email;
                    document.getElementById('detailPhone').textContent = phone;
                    const memEl = document.getElementById('detailMembership');
                    if (memEl) {
                        memEl.textContent = membership;
                    }

                    const avatarInitial = document.getElementById('detailAvatarInitial');
                    if (avatarInitial) {
                        avatarInitial.textContent = name.substring(0, 2).toUpperCase();
                    }

                    const statusEl = document.getElementById('detailStatus');
                    if (statusEl) {
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
            const modalEdit = document.getElementById('modalEditMember');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const action = button.getAttribute('data-action') || '';
                    const code = button.getAttribute('data-code') || '';
                    const name = button.getAttribute('data-name') || '';
                    const email = button.getAttribute('data-email') || '';
                    const phone = button.getAttribute('data-phone') || '';
                    const status = button.getAttribute('data-status') || 'Active';

                    const form = document.getElementById('formEditMember');
                    if (form) {
                        form.action = action;
                    }
                    const actionInput = document.getElementById('editMemberActionInput');
                    if (actionInput) {
                        actionInput.value = action;
                    }

                    document.getElementById('editMemberCode').value = code;
                    document.getElementById('editMemberName').value = name;
                    document.getElementById('editMemberEmail').value = email;
                    document.getElementById('editMemberPhone').value = phone;
                    document.getElementById('editMemberPassword').value = '';
                    document.getElementById('editMemberStatus').value = status;
                });
            }

            // Modal Hapus: populate on show
            const modalHapus = document.getElementById('modalHapusMember');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const action = button.getAttribute('data-action') || '';
                    const name = button.getAttribute('data-name') || '';
                    const code = button.getAttribute('data-code') || '';

                    const form = document.getElementById('formHapusMember');
                    if (form) {
                        form.action = action;
                    }

                    const nameEl = document.getElementById('hapusMemberNama');
                    if (nameEl) {
                        nameEl.textContent = name;
                    }

                    const codeEl = document.getElementById('hapusMemberCode');
                    if (codeEl) {
                        codeEl.textContent = code;
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
                                    badgeEl.className = 'badge status-badge ' + (data.status ===
                                        'Active' ? 'bg-label-success' : 'bg-label-danger');
                                }

                                const tr = currentSwitch.closest('tr');
                                if (tr) {
                                    const detailBtn = tr.querySelector(
                                        '[data-bs-target="#modalDetailMember"]');
                                    if (detailBtn) detailBtn.setAttribute('data-status', data
                                        .status);

                                    const editBtn = tr.querySelector(
                                        '[data-bs-target="#modalEditMember"]');
                                    if (editBtn) editBtn.setAttribute('data-status', data
                                        .status);
                                }
                            } else {
                                currentSwitch.checked = !isChecked;
                            }
                        })
                        .catch(error => {
                            currentSwitch.disabled = false;
                            currentSwitch.checked = !isChecked;
                            alert('Terjadi kesalahan saat mengubah status member.');
                        });
                });
            });

            // Auto reopen modal if validation fails
            @if ($errors->any())
                @if (old('_modal') === 'create_member')
                    const modalTambahMemberEl = document.getElementById('modalTambahMember');
                    if (modalTambahMemberEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambahMemberEl).show();
                    }
                @elseif (old('_modal') === 'create_user')
                    const modalTambahUserEl = document.getElementById('modalTambahUser');
                    if (modalTambahUserEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambahUserEl).show();
                    }
                @elseif (old('_modal') === 'edit')
                    const formEdit = document.getElementById('formEditMember');
                    const prevAction = '{{ old('_action') }}';
                    if (formEdit && prevAction) {
                        formEdit.action = prevAction;
                    }
                    const modalEditEl = document.getElementById('modalEditMember');
                    if (modalEditEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalEditEl).show();
                    }
                @endif
            @endif
        });
    </script>
@endpush
