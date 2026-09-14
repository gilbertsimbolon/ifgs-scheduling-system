@extends('layouts.app')

@section('title', 'Master Data Time Slot')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-2 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Master Data /</span> Time Slot
                </h5>
                <p class="text-muted mb-0">Kelola master slot waktu operasional dan batasan kapasitas gym untuk Algoritma Greedy.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTimeSlot">
                    <i class="bx bx-plus me-1"></i> Tambah Time Slot
                </button>
            </div>
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

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card card-border-shadow-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-time-five fs-4"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $metrics['total'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted small">Total Sesi Terdaftar</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-border-shadow-success h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-check-circle fs-4"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $metrics['active'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted small">Sesi Aktif (Jadwal Buka)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-border-shadow-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="bx bx-power-off fs-4"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $metrics['inactive'] }}</h4>
                        </div>
                        <p class="mb-0 text-muted small">Sesi Non-Aktif (Libur/Tutup)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-user-check fs-4"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $metrics['total_capacity'] }} <span class="fs-6 fw-normal text-muted">Orang</span></h4>
                        </div>
                        <p class="mb-0 text-muted small">Total Kapasitas Kuota Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card Table -->
        <div class="card">
            <!-- Filter Bar -->
            <div class="card-body border-bottom">
                <form action="{{ route('time-slots.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="search">Cari Nama Sesi / Jam</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Contoh: Pagi, 08:00, Siang..." value="{{ request('search') }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="filter_status">Status</label>
                        <select name="status" id="filter_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('time-slots.index') }}" class="btn btn-outline-secondary">
                                Reset
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
                            <th style="width: 60px;">#</th>
                            <th>Nama Sesi / Slot</th>
                            <th>Rentang Waktu</th>
                            <th>Kapasitas Maksimal</th>
                            <th>Status Operasional</th>
                            <th class="text-center" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($timeSlots as $index => $slot)
                            <tr>
                                <td>{{ $timeSlots->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-time"></i>
                                            </span>
                                        </div>
                                        <span class="fw-semibold text-heading">{{ $slot->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-dark font-monospace fs-7">
                                        <i class="bx bx-calendar-event me-1"></i> {{ $slot->time_range }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $slot->capacity }}</span>
                                    <span class="text-muted small">Orang / Sesi</span>
                                </td>
                                <td>
                                    <form action="{{ route('time-slots.toggle-status', $slot) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Klik untuk ubah status">
                                            @if ($slot->status === 'active')
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
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-icon btn-sm btn-label-warning"
                                            data-bs-toggle="modal" data-bs-target="#modalEditTimeSlot{{ $slot->id }}"
                                            title="Edit Slot">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-label-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusTimeSlot{{ $slot->id }}"
                                            title="Hapus Slot">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit Time Slot -->
                            <div class="modal fade" id="modalEditTimeSlot{{ $slot->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('time-slots.update', $slot) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="_modal" value="edit_{{ $slot->id }}">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bx bx-edit text-warning me-1"></i> Edit Time Slot
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label" for="name_{{ $slot->id }}">Nama Sesi / Slot <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="name_{{ $slot->id }}" name="name"
                                                        value="{{ old('_modal') === 'edit_'.$slot->id ? old('name') : $slot->name }}" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label" for="start_time_{{ $slot->id }}">Jam Mulai <span class="text-danger">*</span></label>
                                                        <input type="time" class="form-control" id="start_time_{{ $slot->id }}" name="start_time"
                                                            value="{{ old('_modal') === 'edit_'.$slot->id ? old('start_time') : \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label" for="end_time_{{ $slot->id }}">Jam Selesai <span class="text-danger">*</span></label>
                                                        <input type="time" class="form-control" id="end_time_{{ $slot->id }}" name="end_time"
                                                            value="{{ old('_modal') === 'edit_'.$slot->id ? old('end_time') : \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="capacity_{{ $slot->id }}">Kapasitas Maksimal (Kuota) <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="capacity_{{ $slot->id }}" name="capacity" min="1" max="200"
                                                            value="{{ old('_modal') === 'edit_'.$slot->id ? old('capacity') : $slot->capacity }}" required>
                                                        <span class="input-group-text">Orang</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="status_{{ $slot->id }}">Status Operasional <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="status_{{ $slot->id }}" name="status" required>
                                                        <option value="active" {{ (old('_modal') === 'edit_'.$slot->id ? old('status') : $slot->status) === 'active' ? 'selected' : '' }}>Aktif (Tersedia untuk reservasi)</option>
                                                        <option value="inactive" {{ (old('_modal') === 'edit_'.$slot->id ? old('status') : $slot->status) === 'inactive' ? 'selected' : '' }}>Non-Aktif (Libur / Perawatan)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Time Slot -->
                            <div class="modal fade" id="modalHapusTimeSlot{{ $slot->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('time-slots.destroy', $slot) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-danger">
                                                    <i class="bx bx-trash me-1"></i> Konfirmasi Hapus Time Slot
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menghapus Time Slot <strong>"{{ $slot->name }}"</strong> ({{ $slot->time_range }})?</p>
                                                <div class="alert alert-warning mb-0">
                                                    <small><i class="bx bx-info-circle me-1"></i> Slot yang sudah memiliki riwayat reservasi atau jadwal tidak dapat dihapus, melainkan disarankan untuk dinonaktifkan.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bx bx-time-five fs-2 mb-2 d-block"></i>
                                    Belum ada data Time Slot yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($timeSlots->hasPages())
                <div class="card-footer d-flex justify-content-end pb-2 pt-3">
                    {{ $timeSlots->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Tambah Time Slot -->
    <div class="modal fade" id="modalTambahTimeSlot" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('time-slots.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_modal" value="tambah">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bx bx-plus-circle text-primary me-1"></i> Tambah Time Slot Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                            <label class="form-label" for="tambah_name">Nama Sesi / Slot <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tambah_name" name="name"
                                placeholder="Contoh: Sesi Pagi 1, Sesi Sore 2" value="{{ old('_modal') === 'tambah' ? old('name') : '' }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" for="tambah_start_time">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="tambah_start_time" name="start_time"
                                    value="{{ old('_modal') === 'tambah' ? old('start_time') : '08:00' }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="tambah_end_time">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="tambah_end_time" name="end_time"
                                    value="{{ old('_modal') === 'tambah' ? old('end_time') : '09:30' }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="tambah_capacity">Kapasitas Maksimal (Kuota) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tambah_capacity" name="capacity" min="1" max="200"
                                    placeholder="Contoh: 15" value="{{ old('_modal') === 'tambah' ? old('capacity') : '15' }}" required>
                                <span class="input-group-text">Orang</span>
                            </div>
                            <small class="text-muted">Batas maksimum jumlah member yang dapat ditampung dalam satu sesi gym.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="tambah_status">Status Operasional <span class="text-danger">*</span></label>
                            <select class="form-select" id="tambah_status" name="status" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif (Tersedia untuk reservasi)</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif (Libur / Perawatan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Time Slot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any() && old('_modal'))
                var modalId = "{{ old('_modal') }}";
                if (modalId === 'tambah') {
                    var myModal = new bootstrap.Modal(document.getElementById('modalTambahTimeSlot'));
                    myModal.show();
                } else if (modalId.startsWith('edit_')) {
                    var slotId = modalId.replace('edit_', '');
                    var editModal = document.getElementById('modalEditTimeSlot' + slotId);
                    if (editModal) {
                        var bsModal = new bootstrap.Modal(editModal);
                        bsModal.show();
                    }
                }
            @endif
        });
    </script>
@endpush

