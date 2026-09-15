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
                        <label class="form-label" for="search">Cari Nama / Email / Kode / Spesialisasi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Contoh: Mario, Zumba, TRN-001..." value="{{ request('search') }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter_specialization">Spesialisasi</label>
                        <select name="specialization" id="filter_specialization" class="form-select">
                            <option value="">Semua Spesialisasi</option>
                            @foreach ($specializations as $spec)
                                <option value="{{ $spec }}"
                                    {{ request('specialization') === $spec ? 'selected' : '' }}>
                                    {{ $spec }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter_status">Status</label>
                        <select name="status" id="filter_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
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
                <table class="table table-hover">
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
                                $color =
                                    str_contains(strtolower($trainer->specialization), 'zumba') ||
                                    str_contains(strtolower($trainer->specialization), 'aerobic')
                                        ? 'danger'
                                        : 'primary';
                                $icon = $color === 'danger' ? 'bx-body' : 'bx-dumbbell';
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
                                                class="fw-semibold text-heading d-block">{{ $trainer->user->name }}</span>
                                            <small class="text-muted">{{ $trainer->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-dark font-monospace">{{ $trainer->trainer_code }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $color }}">
                                        <i class="bx {{ $icon }} me-1"></i> {{ $trainer->specialization }}
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
                                        <form action="{{ route('trainers.toggle-status', $trainer) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent"
                                                title="Klik untuk ubah status">
                                                @if ($trainer->status === 'active')
                                                    <span class="badge bg-label-success cursor-pointer">
                                                        <i class="bx bx-check-circle me-1"></i> Aktif
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-secondary cursor-pointer">
                                                        <i class="bx bx-x-circle me-1"></i> Non-Aktif
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        @if ($trainer->status === 'active')
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
                                                title="Edit Trainer">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalHapusTrainer{{ $trainer->id }}"
                                                title="Hapus Trainer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endhasrole
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasRole('Admin/Manager') ? '7' : '6' }}"
                                    class="text-center py-4 text-muted">
                                    <i class="bx bx-run fs-2 mb-2 d-block"></i>
                                    Belum ada data Trainer yang tersedia.
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

    <!-- Modals Edit & Hapus (Di luar table-responsive agar tidak terkena text-nowrap) -->
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
                                <div class="mb-3">
                                    <label class="form-label" for="name_{{ $trainer->id }}">Nama Lengkap <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name_{{ $trainer->id }}" name="name"
                                        value="{{ old('_modal') === 'edit_' . $trainer->id ? old('name') : $trainer->user->name }}"
                                        required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="email_{{ $trainer->id }}">Email Login <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email_{{ $trainer->id }}"
                                            name="email"
                                            value="{{ old('_modal') === 'edit_' . $trainer->id ? old('email') : $trainer->user->email }}"
                                            required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="phone_{{ $trainer->id }}">No. HP / WhatsApp</label>
                                        <input type="text" class="form-control" id="phone_{{ $trainer->id }}"
                                            name="phone"
                                            value="{{ old('_modal') === 'edit_' . $trainer->id ? old('phone') : $trainer->phone }}"
                                            placeholder="Contoh: 08123456789">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="specialization_{{ $trainer->id }}">Spesialisasi Keahlian
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="specialization_{{ $trainer->id }}"
                                        name="specialization"
                                        value="{{ old('_modal') === 'edit_' . $trainer->id ? old('specialization') : $trainer->specialization }}"
                                        placeholder="Contoh: Fitness & Bodybuilding, Aerobic & Zumba" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password_{{ $trainer->id }}">Kata Sandi Baru <small
                                            class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                    <input type="password" class="form-control" id="password_{{ $trainer->id }}"
                                        name="password" placeholder="Minimal 8 karakter">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="bio_{{ $trainer->id }}">Profil / Sertifikasi /
                                        Catatan</label>
                                    <textarea class="form-control" id="bio_{{ $trainer->id }}" name="bio" rows="2"
                                        placeholder="Keterangan singkat pengalaman atau sertifikasi...">{{ old('_modal') === 'edit_' . $trainer->id ? old('bio') : $trainer->bio }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="status_{{ $trainer->id }}">Status Operasional <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="status_{{ $trainer->id }}" name="status" required>
                                        <option value="active"
                                            {{ (old('_modal') === 'edit_' . $trainer->id ? old('status') : $trainer->status) === 'active' ? 'selected' : '' }}>
                                            Aktif (Tersedia melatih)
                                        </option>
                                        <option value="inactive"
                                            {{ (old('_modal') === 'edit_' . $trainer->id ? old('status') : $trainer->status) === 'inactive' ? 'selected' : '' }}>
                                            Non-Aktif (Cuti / Berhenti)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Hapus Trainer -->
            <div class="modal fade" id="modalHapusTrainer{{ $trainer->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('trainers.destroy', $trainer) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-danger">
                                    <i class="bx bx-trash me-1"></i> Konfirmasi Hapus Trainer
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-wrap" style="white-space: normal;">
                                <p class="mb-3">Apakah Anda yakin ingin menghapus data Trainer
                                    <strong>"{{ $trainer->user->name }}"</strong> ({{ $trainer->trainer_code }})?
                                </p>
                                <div class="alert alert-warning mb-0 text-wrap"
                                    style="white-space: normal; word-break: break-word;">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bx bx-info-circle fs-5 mt-1 text-warning flex-shrink-0"></i>
                                        <div>
                                            Menghapus trainer juga akan menghapus akun login terkait. Jika hanya ingin
                                            menonaktifkan, silakan gunakan tombol status.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Modal Tambah Trainer -->
        <div class="modal fade" id="modalTambahTrainer" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('trainers.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="bx bx-plus-circle text-primary me-1"></i> Tambah Trainer Baru
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

                            <div class="mb-3">
                                <label class="form-label" for="tambah_name">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tambah_name" name="name"
                                    placeholder="Contoh: Coach Vicky, Zin Sarah..."
                                    value="{{ old('_modal') === 'tambah' ? old('name') : '' }}" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label" for="tambah_email">Email Login <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="tambah_email" name="email" placeholder="nama@ifgs.test"
                                        value="{{ old('_modal') === 'tambah' ? old('email') : '' }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="tambah_phone">No. HP / WhatsApp</label>
                                    <input type="text" class="form-control" id="tambah_phone" name="phone"
                                        placeholder="08123456789"
                                        value="{{ old('_modal') === 'tambah' ? old('phone') : '' }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tambah_specialization">Spesialisasi Keahlian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tambah_specialization" name="specialization"
                                    placeholder="Contoh: Fitness & Bodybuilding, Aerobic & Zumba..."
                                    value="{{ old('_modal') === 'tambah' ? old('specialization') : '' }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tambah_password">Kata Sandi Akun <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="tambah_password" name="password"
                                    placeholder="Minimal 8 karakter" required>
                                <small class="text-muted">Digunakan oleh trainer untuk masuk ke sistem.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tambah_bio">Profil / Sertifikasi / Catatan</label>
                                <textarea class="form-control" id="tambah_bio" name="bio" rows="2"
                                    placeholder="Keterangan singkat pengalaman atau sertifikasi...">{{ old('_modal') === 'tambah' ? old('bio') : '' }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tambah_status">Status Operasional <span class="text-danger">*</span></label>
                                <select class="form-select" id="tambah_status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                        Aktif (Tersedia melatih)
                                    </option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                        Non-Aktif (Cuti / Berhenti)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Trainer</button>
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
            @if ($errors->any() && old('_modal'))
                var modalId = "{{ old('_modal') }}";
                if (modalId === 'tambah') {
                    var myModal = new bootstrap.Modal(document.getElementById('modalTambahTrainer'));
                    myModal.show();
                } else if (modalId.startsWith('edit_')) {
                    var trainerId = modalId.replace('edit_', '');
                    var editModal = document.getElementById('modalEditTrainer' + trainerId);
                    if (editModal) {
                        var bsModal = new bootstrap.Modal(editModal);
                        bsModal.show();
                    }
                } @endif
                });
            </script>
    @endpush
