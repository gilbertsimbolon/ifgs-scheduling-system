@extends('layouts.app')

@section('title', 'Manajemen Trainer')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-2 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Trainer
                </h5>
            </div>
            @hasrole('Admin/Manager')
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTrainer">
                        <i class="bx bx-plus me-1"></i> Tambah Trainer
                    </button>
                </div>
            @endhasrole
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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

        <!-- Main Card Table -->
        <div class="card">
            <!-- Filter Bar -->
            <div class="card-body border-bottom">
                <form action="{{ route('trainers.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Cari Nama / Email / Kode / No. HP</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Contoh: Mario, TRN-001..." value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="filter_specialization">Spesialisasi</label>
                        <select id="filter_specialization" name="specialization" class="form-select">
                            <option value="">Semua Spesialisasi</option>
                            @foreach ($specializations as $spec)
                                <option value="{{ $spec }}"
                                    {{ request('specialization') == $spec ? 'selected' : '' }}>
                                    {{ $spec }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="filter_status">Status</label>
                        <select id="filter_status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'specialization', 'status']))
                            <a href="{{ route('trainers.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table Content -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover w-100" style="width: 100% !important; min-width: 100% !important;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Trainer & Kontak</th>
                            <th>Kode</th>
                            <th>Spesialisasi</th>
                            <th>No. WhatsApp / HP</th>
                            <th>Status</th>
                            @hasrole('Admin/Manager')
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            @endhasrole
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($trainers as $index => $trainer)
                            @php
                                $specLower = strtolower($trainer->specialization ?? '');
                                $color =
                                    str_contains($specLower, 'zumba') || str_contains($specLower, 'aerobic')
                                        ? 'danger'
                                        : 'primary';
                                $icon = $color === 'danger' ? 'bx-body' : 'bx-dumbbell';
                                $isActive = ($trainer->trainer_status ?? 'active') === 'active';
                            @endphp
                            <tr>
                                <td>{{ $trainers->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-{{ $color }}">
                                                <i class="bx {{ $icon }}"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <span
                                                class="fw-semibold text-heading d-block">{{ $trainer->user?->name ?? 'Trainer' }}</span>
                                            <small class="text-muted">{{ $trainer->user?->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-dark font-monospace">{{ $trainer->trainer_code }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $color }}">
                                        <i class="bx {{ $icon }} me-1"></i>
                                        {{ $trainer->specialization ?? 'Fitness Trainer' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($trainer->phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $trainer->phone) }}"
                                            target="_blank" class="text-body d-inline-flex align-items-center"
                                            title="Hubungi via WhatsApp">
                                            <i class="bx bxl-whatsapp text-success me-1"></i> {{ $trainer->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @hasrole('Admin/Manager')
                                        <form id="form_toggle_{{ $trainer->id }}"
                                            action="{{ route('trainers.toggle-status', $trainer) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch d-inline-flex align-items-center mb-0">
                                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch"
                                                    id="switchStatus{{ $trainer->id }}" {{ $isActive ? 'checked' : '' }}
                                                    onchange="document.getElementById('form_toggle_{{ $trainer->id }}').submit();"
                                                    title="Klik untuk ubah status trainer">
                                                <label class="form-check-label ms-2 cursor-pointer"
                                                    for="switchStatus{{ $trainer->id }}">
                                                    @if ($isActive)
                                                        <span class="badge bg-label-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">Non-Aktif</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </form>
                                    @else
                                        @if ($isActive)
                                            <span class="badge bg-label-success">Aktif</span>
                                        @else
                                            <span class="badge bg-label-secondary">Non-Aktif</span>
                                        @endif
                                    @endhasrole
                                </td>
                                @hasrole('Admin/Manager')
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-icon btn-sm btn-outline-warning"
                                                data-bs-toggle="modal" data-bs-target="#modalEditTrainer{{ $trainer->id }}"
                                                title="Edit Spesialisasi & Status">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalHapusTrainer{{ $trainer->id }}"
                                                title="Cabut Status Trainer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endhasrole
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4 w-100 mx-auto"
                                        style="min-height: 240px; white-space: normal;">
                                        <div class="avatar avatar-xl mb-3">
                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                <i class="bx bx-run fs-1 text-muted"></i>
                                            </span>
                                        </div>
                                        <h5 class="fw-semibold text-secondary mb-1">
                                            @if (request()->hasAny(['search', 'specialization', 'status']))
                                                Tidak Ada Data Trainer Yang Sesuai
                                            @else
                                                Belum Ada Data Trainer
                                            @endif
                                        </h5>
                                        <p class="text-muted small mb-3" style="max-width: 420px;">
                                            @if (request()->hasAny(['search', 'specialization', 'status']))
                                                Tidak ditemukan data trainer yang sesuai dengan filter pencarian Anda.
                                            @else
                                                Belum ada member yang diaktifkan sebagai trainer. Silakan klik tombol di
                                                bawah untuk memilih akun member yang sudah terdaftar.
                                            @endif
                                        </p>
                                        @if (request()->hasAny(['search', 'specialization', 'status']))
                                            <a href="{{ route('trainers.index') }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-reset me-1"></i> Reset Filter
                                            </a>
                                        @else
                                            @hasrole('Admin/Manager')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modalTambahTrainer">
                                                    <i class="bx bx-plus me-1"></i> Tambah Trainer
                                                </button>
                                            @endhasrole
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($trainers->hasPages())
                <div class="card-footer d-flex justify-content-end pb-2 pt-3">
                    {{ $trainers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modals Edit & Cabut Status Trainer -->
    @hasrole('Admin/Manager')
        @foreach ($trainers as $trainer)
            <!-- Modal Edit Trainer -->
            <div class="modal fade" id="modalEditTrainer{{ $trainer->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('trainers.update', $trainer) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_modal" value="edit_{{ $trainer->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    <i class="bx bx-edit text-warning me-1"></i> Edit Data Trainer
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-wrap" style="white-space: normal;">
                                @if ($errors->any() && old('_modal') === 'edit_' . $trainer->id)
                                    <div class="alert alert-danger mb-3">
                                        <ul class="mb-0 ps-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label text-muted">Akun Trainer</label>
                                    <div class="form-control bg-light text-muted">
                                        <strong>{{ $trainer->user?->name ?? 'Trainer' }}</strong>
                                        ({{ $trainer->user?->email ?? '-' }})
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="specialization_{{ $trainer->id }}">Spesialisasi
                                        Keahlian</label>
                                    <input type="text" class="form-control" id="specialization_{{ $trainer->id }}"
                                        name="specialization"
                                        value="{{ old('_modal') === 'edit_' . $trainer->id ? old('specialization') : $trainer->specialization }}"
                                        placeholder="Contoh: Fitness & Bodybuilding, Aerobic & Zumba">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="status_{{ $trainer->id }}">Status Operasional <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="status_{{ $trainer->id }}" name="status" required>
                                        <option value="active"
                                            {{ (old('_modal') === 'edit_' . $trainer->id ? old('status') : $trainer->trainer_status ?? 'active') === 'active' ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="inactive"
                                            {{ (old('_modal') === 'edit_' . $trainer->id ? old('status') : $trainer->trainer_status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                            Non-Aktif
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Cabut Status Trainer -->
            <div class="modal fade" id="modalHapusTrainer{{ $trainer->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('trainers.destroy', $trainer) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">
                                    <i class="bx bx-trash text-danger me-1"></i> Cabut Status Trainer
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-wrap" style="white-space: normal;">
                                <p class="mb-3">Apakah Anda yakin ingin mencabut status Trainer untuk akun
                                    <strong>"{{ $trainer->user?->name ?? 'Trainer' }}"</strong>
                                    ({{ $trainer->trainer_code }})?
                                </p>
                                <div class="alert alert-warning mb-0 text-wrap"
                                    style="white-space: normal; word-break: break-word;">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bx bx-info-circle fs-5 mt-1 text-warning flex-shrink-0"></i>
                                        <div>
                                            Tindakan ini hanya akan menonaktifkan peran trainer (<code>is_trainer =
                                                false</code>). Akun member dan data pengguna tetap tersimpan di sistem.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Cabut Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Modal Tambah Trainer (Pencarian Akun Member) -->
        <div class="modal fade" id="modalTambahTrainer" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('trainers.store') }}" method="POST" id="formTambahTrainer">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah">
                        <input type="hidden" name="member_id" id="selected_member_id"
                            value="{{ old('_modal') === 'tambah' ? old('member_id') : '' }}" required>

                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="bx bx-plus-circle text-primary me-1"></i> Tambah Trainer
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-wrap" style="white-space: normal;">
                            @if ($errors->any() && old('_modal') === 'tambah')
                                <div class="alert alert-danger mb-3">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="alert alert-primary mb-3">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bx bx-info-circle fs-5 mt-1 flex-shrink-0"></i>
                                    <div>
                                        Pilih akun member yang sudah terdaftar untuk diaktifkan peran trainernya. Trainer baru
                                        secara default akan berstatus <strong>Aktif</strong>.
                                    </div>
                                </div>
                            </div>

                            <!-- Search Bar Input Member -->
                            <div class="mb-3 position-relative" id="member_search_container">
                                <label class="form-label fw-semibold" for="member_search_input">
                                    Cari Akun Member <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="member_search_input"
                                        placeholder="Ketik nama, email, atau kode member..." autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" id="clear_search_btn"
                                        style="display: none;">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Cari member terdaftar yang belum menjadi trainer.</small>

                                <!-- Dropdown List Hasil Pencarian -->
                                <div id="member_search_results" class="list-group shadow border mt-1 position-absolute w-100"
                                    style="max-height: 220px; overflow-y: auto; z-index: 1060; display: none; background: #fff;">
                                </div>
                            </div>

                            <!-- Kartu Preview Member Terpilih -->
                            <div id="selected_member_box" class="card border border-primary bg-lighter mb-3"
                                style="display: none;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-3">
                                                <span class="avatar-initial rounded-circle bg-primary text-white"
                                                    id="selected_avatar_initial">MB</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-primary" id="selected_member_name">-</h6>
                                                <small class="text-muted d-block font-monospace"
                                                    id="selected_member_code">-</small>
                                                <small class="text-muted" id="selected_member_email">-</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="btn_change_member" title="Pilih akun member lain">
                                            <i class="bx bx-refresh me-1"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Spesialisasi Keahlian -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="tambah_specialization">Spesialisasi
                                    Keahlian</label>
                                <input type="text" class="form-control" id="tambah_specialization" name="specialization"
                                    placeholder="Contoh: Fitness & Gym Trainer, Aerobic & Zumba..."
                                    value="{{ old('_modal') === 'tambah' ? old('specialization') : 'Fitness & Gym Trainer' }}">
                                <small class="text-muted">Informasi keahlian trainer untuk ditampilkan ke member.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btn_submit_trainer" disabled>
                                <i class="bx bx-user-check me-1"></i> Aktifkan Sebagai Trainer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endhasrole
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data eligible members dari controller
            var eligibleMembers = {!! json_encode($eligibleMembersJson ?? []) !!};

            var searchInput = document.getElementById('member_search_input');
            var resultsBox = document.getElementById('member_search_results');
            var clearSearchBtn = document.getElementById('clear_search_btn');
            var selectedMemberIdInput = document.getElementById('selected_member_id');
            var selectedMemberBox = document.getElementById('selected_member_box');
            var selectedAvatarInitial = document.getElementById('selected_avatar_initial');
            var selectedMemberName = document.getElementById('selected_member_name');
            var selectedMemberCode = document.getElementById('selected_member_code');
            var selectedMemberEmail = document.getElementById('selected_member_email');
            var btnChangeMember = document.getElementById('btn_change_member');
            var btnSubmitTrainer = document.getElementById('btn_submit_trainer');
            var searchContainer = document.getElementById('member_search_container');

            function renderResults(members) {
                if (!resultsBox) return;
                resultsBox.innerHTML = '';

                if (members.length === 0) {
                    var emptyDiv = document.createElement('div');
                    emptyDiv.className = 'list-group-item text-center text-muted py-3 small';
                    emptyDiv.innerHTML = '<i class="bx bx-search-alt me-1"></i> Tidak ada akun member yang cocok.';
                    resultsBox.appendChild(emptyDiv);
                    resultsBox.style.display = 'block';
                    return;
                }

                members.slice(0, 10).forEach(function(m) {
                    var item = document.createElement('a');
                    item.href = 'javascript:void(0);';
                    item.className =
                        'list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2';
                    item.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary font-monospace">${m.initials}</span>
                            </div>
                            <div>
                                <span class="fw-semibold text-heading d-block">${m.name}</span>
                                <small class="text-muted font-monospace">${m.code}</small> &bull; <small class="text-muted">${m.email}</small>
                            </div>
                        </div>
                        <span class="btn btn-xs btn-outline-primary"><i class="bx bx-check"></i> Pilih</span>
                    `;

                    item.addEventListener('click', function() {
                        selectMember(m);
                    });

                    resultsBox.appendChild(item);
                });

                resultsBox.style.display = 'block';
            }

            function selectMember(member) {
                if (selectedMemberIdInput) selectedMemberIdInput.value = member.id;
                if (selectedMemberName) selectedMemberName.textContent = member.name;
                if (selectedMemberCode) selectedMemberCode.textContent = member.code;
                if (selectedMemberEmail) selectedMemberEmail.textContent = member.email;
                if (selectedAvatarInitial) selectedAvatarInitial.textContent = member.initials;

                if (selectedMemberBox) selectedMemberBox.style.display = 'block';
                if (searchContainer) searchContainer.style.display = 'none';
                if (resultsBox) resultsBox.style.display = 'none';
                if (btnSubmitTrainer) btnSubmitTrainer.disabled = false;
            }

            function resetMemberSelection() {
                if (selectedMemberIdInput) selectedMemberIdInput.value = '';
                if (selectedMemberBox) selectedMemberBox.style.display = 'none';
                if (searchContainer) searchContainer.style.display = 'block';
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                if (clearSearchBtn) clearSearchBtn.style.display = 'none';
                if (resultsBox) resultsBox.style.display = 'none';
                if (btnSubmitTrainer) btnSubmitTrainer.disabled = true;
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var query = this.value.trim().toLowerCase();
                    if (clearSearchBtn) {
                        clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';
                    }

                    if (query.length === 0) {
                        renderResults(eligibleMembers);
                        return;
                    }

                    var filtered = eligibleMembers.filter(function(m) {
                        return m.name.toLowerCase().includes(query) ||
                            m.email.toLowerCase().includes(query) ||
                            m.code.toLowerCase().includes(query) ||
                            m.phone.toLowerCase().includes(query);
                    });

                    renderResults(filtered);
                });

                searchInput.addEventListener('focus', function() {
                    var query = this.value.trim().toLowerCase();
                    if (query.length === 0) {
                        renderResults(eligibleMembers);
                    }
                });
            }

            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                    this.style.display = 'none';
                    renderResults(eligibleMembers);
                });
            }

            if (btnChangeMember) {
                btnChangeMember.addEventListener('click', function() {
                    resetMemberSelection();
                });
            }

            // Klik di luar dropdown untuk menutup dropdown
            document.addEventListener('click', function(e) {
                if (resultsBox && !resultsBox.contains(e.target) && searchInput && !searchInput.contains(e
                        .target)) {
                    resultsBox.style.display = 'none';
                }
            });

            // Handle pre-selected member jika ada error validation pada old modal
            var oldMemberId = '{{ old('_modal') === 'tambah' ? old('member_id') : '' }}';
            if (oldMemberId) {
                var found = eligibleMembers.find(function(m) {
                    return String(m.id) === String(oldMemberId);
                });
                if (found) {
                    selectMember(found);
                }
            }

            // Auto show modal jika validation error
            @if ($errors->any())
                @if (old('_modal') === 'tambah')
                    var modalTambah = document.getElementById('modalTambahTrainer');
                    if (modalTambah && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambah).show();
                    }
                @elseif (str_starts_with(old('_modal') ?? '', 'edit_'))
                    var editModalId = 'modalEditTrainer{{ str_replace('edit_', '', old('_modal')) }}';
                    var modalEdit = document.getElementById(editModalId);
                    if (modalEdit && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalEdit).show();
                    }
                @endif
            @endif
        });
    </script>
@endpush
