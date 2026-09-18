@extends('layouts.app')

@section('title', 'Reservasi Kunjungan Gym')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-2 mt-2 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Kunjungan /</span> Reservasi
                </h5>
            </div>
            <div>
                @if ($isMember)
                    @if ($myActiveMembership)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambahReservasi">
                            <i class="bx bx-calendar-plus me-1"></i> Buat Reservasi Kunjungan
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary" disabled
                            title="Anda belum memiliki paket membership aktif">
                            <i class="bx bx-calendar-plus me-1"></i> Membership Tidak Aktif
                        </button>
                    @endif
                @else
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalTambahReservasi">
                        <i class="bx bx-calendar-plus me-1"></i> Tambah Reservasi Baru
                    </button>
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <i class="bx bx-info-circle me-1"></i> {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Member Membership Warning Banner -->
        @if ($isMember && !auth()->user()->member)
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bx bx-info-circle fs-3 me-2"></i>
                <div>
                    <strong>Akun Anda Belum Terdaftar Sebagai Member Gym!</strong>
                    <div class="small">Anda telah berhasil membuat akun pengguna di IFGS. Untuk mulai melakukan reservasi
                        jadwal latihan gym, silakan kunjungi kasir/staf gym untuk mengaktifkan paket membership Anda.</div>
                </div>
            </div>
        @elseif ($isMember && !$myActiveMembership)
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bx bx-alert-triangle fs-3 me-2"></i>
                <div>
                    <strong>Perhatian!</strong> Akun Anda saat ini belum memiliki paket membership yang aktif. Reservasi
                    kunjungan hanya dapat dibuat oleh member dengan status membership aktif.
                </div>
            </div>
        @endif

        <!-- Table Card -->
        <div class="card">
            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('reservations.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Pencarian</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="{{ $isMember ? 'Kode reservasi...' : 'Kode, nama member...' }}"
                                value="{{ request('search') }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="date">Tanggal Kunjungan</label>
                        <input type="date" id="date" name="date" class="form-control"
                            value="{{ request('date') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal
                            </option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Batal
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'date', 'status']))
                            <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Kode Reservasi</th>
                            @if (!$isMember)
                                <th>Member</th>
                            @endif
                            <th>Paket</th>
                            <th>Waktu</th>
                            <th>Jenis Kunjungan</th>
                            <th>Waktu Sesi</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($reservations as $index => $res)
                            <tr>
                                <td>{{ $reservations->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $res->code }}</span>
                                    <small class="d-block text-muted">{{ $res->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                @if (!$isMember)
                                    <td>
                                        <div class="fw-semibold text-heading">{{ $res->member->user->name ?? '-' }}</div>
                                        <small class="text-muted">{{ $res->member->member_code ?? '-' }}</small>
                                    </td>
                                @endif
                                <td>
                                    <span class="text-body">{{ $res->membership->product->name ?? 'Membership' }}</span>
                                </td>
                                <td>
                                    <span
                                        class="text-heading fw-medium">{{ $res->visit_date->locale('id')->translatedFormat('l, d F Y') }}</span>
                                </td>
                                <td>
                                    <span
                                        class="text-body">{{ $res->timeSlot->name ?? ($res->membership->product->name ?? 'Fitness') }}</span>
                                </td>
                                <td>
                                    <span class="text-body">{{ $res->timeSlot->time_range ?? '-' }}</span>
                                </td>
                                <td>
                                    @if ($res->status === 'scheduled')
                                        <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>
                                            Terjadwal</span>
                                    @elseif ($res->status === 'pending')
                                        <span class="badge bg-label-warning"><i class="bx bx-time-five me-1"></i>
                                            Pending</span>
                                    @elseif ($res->status === 'completed')
                                        <span class="badge bg-label-info"><i class="bx bx-check-double me-1"></i>
                                            Selesai</span>
                                    @else
                                        <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>
                                            Dibatalkan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-icon btn-sm btn-label-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetailReservation{{ $res->id }}"
                                            title="Detail Reservasi">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Cancel Button -->
                                        @if ($res->canBeCancelled())
                                            <button type="button" class="btn btn-icon btn-sm btn-label-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalCancelReservation{{ $res->id }}"
                                                title="Batalkan Reservasi">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Detail Reservation -->
                            <div class="modal fade" id="modalDetailReservation{{ $res->id }}" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold text-dark">
                                                <i class="bx bx-calendar-detail me-1"></i> Detail Reservasi Kunjungan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Foto Profil & Identitas Member -->
                                            <div class="text-center pb-3 border-bottom mb-3">
                                                <div class="avatar avatar-xl mx-auto mb-2">
                                                    @if ($res->member?->user?->avatar_url)
                                                        <img src="{{ $res->member->user->avatar_url }}"
                                                            alt="{{ $res->member->user->name ?? '-' }}"
                                                            class="rounded-circle object-fit-cover border"
                                                            style="width: 75px; height: 75px;" />
                                                    @else
                                                        <span
                                                            class="avatar-initial rounded-circle bg-secondary text-white fw-bold fs-3"
                                                            style="width: 75px; height: 75px; display: inline-flex; align-items: center; justify-content: center;">
                                                            {{ strtoupper(substr($res->member->user->name ?? 'M', 0, 2)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h5 class="mb-1 fw-bold text-dark">{{ $res->member->user->name ?? '-' }}
                                                </h5>
                                                <small class="text-muted d-block">{{ $res->member->member_code ?? '-' }}
                                                    &bull; {{ $res->member->user->email ?? '-' }}</small>
                                                <div class="mt-2 text-muted small">
                                                    Kode Reservasi: <span
                                                        class="fw-semibold text-dark">{{ $res->code }}</span>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-2">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Waktu</small>
                                                    <span
                                                        class="fw-semibold text-dark">{{ $res->visit_date->locale('id')->translatedFormat('l, d F Y') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Paket</small>
                                                    <span
                                                        class="fw-semibold text-dark">{{ $res->membership->product->name ?? '-' }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Jenis Kunjungan</small>
                                                    <span class="fw-semibold text-dark">
                                                        {{ $res->timeSlot->name ?? ($res->membership->product->name ?? 'Fitness') }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Waktu Sesi</small>
                                                    <span class="fw-semibold text-dark">
                                                        {{ $res->timeSlot ? $res->timeSlot->time_range : '-' }}
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Status Kunjungan</small>
                                                    <span class="fw-semibold text-dark">
                                                        @if ($res->status === 'scheduled')
                                                            Terjadwal
                                                        @elseif ($res->status === 'pending')
                                                            Pending
                                                        @elseif ($res->status === 'completed')
                                                            Selesai
                                                        @else
                                                            Dibatalkan
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($res->notes)
                                                <div class="pt-3 border-top mt-2">
                                                    <small class="text-muted d-block">Catatan Reservasi:</small>
                                                    <p class="text-dark mb-0 small">{{ $res->notes }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Cancel Reservation -->
                            @if ($res->canBeCancelled())
                                <div class="modal fade" id="modalCancelReservation{{ $res->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('reservations.cancel', $res) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger">
                                                        <i class="bx bx-x-circle me-1"></i> Batalkan Reservasi
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin membatalkan reservasi
                                                        <strong>{{ $res->code }}</strong> pada waktu
                                                        <strong>{{ $res->visit_date->locale('id')->translatedFormat('l, d F Y') }}</strong>?
                                                    </p>
                                                    <p class="text-muted small mb-0">Kuota slot waktu akan dilepaskan
                                                        kembali dan dapat digunakan oleh member lain.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Kembali</button>
                                                    <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ $isMember ? 8 : 9 }}" class="text-center py-4 text-muted">
                                    <i class="bx bx-calendar-x fs-2 mb-2 d-block"></i>
                                    Belum ada data reservasi kunjungan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($reservations->hasPages())
                <div class="card-footer d-flex justify-content-end pb-2 pt-3">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Tambah Reservasi Baru -->
    <div class="modal fade" id="modalTambahReservasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('reservations.store') }}" method="POST" id="formTambahReservasi">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-calendar-plus me-1"></i> Buat Reservasi Kunjungan Gym
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            @if (!$isMember)
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="member_search_input">
                                        Pilih Member (Membership Aktif) <span class="text-danger">*</span>
                                    </label>

                                    <!-- Input tersembunyi member_id untuk form submission -->
                                    <input type="hidden" name="member_id" id="selected_member_id"
                                        value="{{ old('member_id') }}" required>

                                    <!-- Search bar input -->
                                    <div class="position-relative" id="member_search_wrapper">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                                            <input type="text" id="member_search_input" class="form-control"
                                                placeholder="Ketik nama member, kode member (IFGS-...), atau nomor HP..."
                                                autocomplete="off">
                                        </div>
                                        <small class="text-muted d-block mt-1" id="member_search_help">
                                            <i class="bx bx-info-circle me-1"></i> Ketik minimal 3 huruf untuk mencari
                                            member secara instan.
                                        </small>

                                        <!-- Floating live search results -->
                                        <div id="member_search_results"
                                            class="dropdown-menu w-100 shadow-lg p-0 overflow-auto border mt-1"
                                            style="max-height: 250px; display: none; position: absolute; z-index: 1060;">
                                        </div>
                                    </div>

                                    <!-- Selected Member Preview Card -->
                                    <div id="selected_member_box"
                                        class="p-3 border rounded bg-lighter d-none align-items-center justify-content-between mt-1">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-md flex-shrink-0" id="selected_member_avatar"></div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-dark" id="selected_member_name">-</h6>
                                                <div class="text-muted small">
                                                    <span id="selected_member_code">-</span> &bull; Paket: <span
                                                        id="selected_member_package" class="fw-medium text-dark">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="btn_change_selected_member">
                                            <i class="bx bx-refresh me-1"></i> Ganti
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="p-3 bg-lighter rounded border">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small d-block">Paket Membership Anda:</span>
                                                <strong
                                                    class="text-dark fs-6">{{ $myActiveMembership->product->name ?? 'Membership Aktif' }}</strong>
                                            </div>
                                            <span class="badge bg-secondary">
                                                Berlaku s/d
                                                {{ $myActiveMembership ? $myActiveMembership->end_date->format('d M Y') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 2 Cards Checklist Jenis Kunjungan (Fitness vs Zumba) -->
                            <div class="col-12">
                                <label class="form-label fw-semibold d-block mb-2">
                                    Pilih Jenis Kunjungan (Sesi Layanan) <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="time_slot_id" id="res_time_slot_id"
                                    value="{{ old('time_slot_id') }}" required>

                                <div class="row g-2" id="serviceSlotCardsContainer">
                                    @foreach ($timeSlots as $slot)
                                        <div class="col-12 col-md-6">
                                            <div class="service-slot-card border rounded p-3 h-100 position-relative bg-white"
                                                id="service_card_{{ $slot->id }}" data-slot-id="{{ $slot->id }}"
                                                data-category="{{ $slot->category }}" data-name="{{ $slot->name }}"
                                                style="cursor: pointer; transition: all 0.2s ease-in-out;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <input class="form-check-input service-card-radio m-0"
                                                            type="radio" name="service_card_radio"
                                                            id="radio_slot_{{ $slot->id }}"
                                                            value="{{ $slot->id }}"
                                                            style="pointer-events: none; width: 1.2em; height: 1.2em;">
                                                        <div>
                                                            <label
                                                                class="form-check-label fw-semibold text-dark mb-0 d-block cursor-pointer"
                                                                for="radio_slot_{{ $slot->id }}">
                                                                {{ $slot->name }}
                                                            </label>
                                                            <small class="text-muted">
                                                                <i
                                                                    class="bx bx-time-five me-1"></i>{{ $slot->time_range }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-label-secondary service-card-badge"
                                                        id="service_badge_{{ $slot->id }}">
                                                        {{ $isMember ? 'Memuat...' : 'Pilih Member' }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="pt-2 border-top mt-2 d-flex justify-content-between align-items-center text-muted small">
                                                    <span>Kapasitas: <strong class="text-dark">{{ $slot->capacity }}
                                                            org</strong></span>
                                                    <span>{{ $slot->category === 'fitness' ? 'Sesi Umum / Bebas' : 'Sesi Khusus Terjadwal' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-1" id="service_card_help">
                                    @if ($isMember)
                                        Pilih jenis kunjungan yang aktif pada paket membership Anda.
                                    @else
                                        Pilih member terlebih dahulu untuk melihat jenis kunjungan yang aktif pada paketnya.
                                    @endif
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" for="res_visit_date">
                                    Tanggal Kunjungan <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="res_visit_date" name="visit_date"
                                    min="{{ date('Y-m-d') }}" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                                <small class="text-muted">Pilih tanggal rencana Anda berolahraga.</small>
                            </div>

                            <!-- Live Schedule & Slot Realtime Area -->
                            <div class="col-12 mt-2">
                                <label class="form-label small fw-semibold text-muted mb-1">
                                    Jadwal Operasional & Ketersediaan Slot Real-Time:
                                </label>
                                <div id="realtimeScheduleContainer">
                                    <!-- Prompt before choosing service type -->
                                    <div id="schedulePlaceholder"
                                        class="p-3 border rounded bg-lighter text-center text-muted small">
                                        <i class="bx bx-calendar-event fs-3 d-block mb-1 text-muted"></i>
                                        Silakan pilih <strong>Jenis Kunjungan</strong> terlebih dahulu untuk melihat jadwal
                                        dan ketersediaan slot.
                                    </div>

                                    <!-- Schedule & Slot Card when service type & date are selected -->
                                    <div id="scheduleCardBox" class="d-none"></div>

                                    <!-- Loading notice -->
                                    <div id="scheduleLoadingBox"
                                        class="p-3 border rounded bg-lighter text-center text-muted small d-none">
                                        <i class="bx bx-loader-alt bx-spin me-1"></i> Memeriksa ketersediaan kuota slot...
                                    </div>

                                    <!-- Sunday closed notice -->
                                    <div id="scheduleClosedBox" class="alert alert-danger mb-0 d-none">
                                        <i class="bx bx-calendar-x me-1"></i> <strong>Gym Tutup:</strong> Indo Fitness Gym
                                        Sport tutup pada hari Minggu sesuai ketentuan jadwal operasional resmi gym. Silakan
                                        pilih hari operasional (Senin - Sabtu).
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="res_notes">Catatan Tambahan (Opsional)</label>
                                <textarea class="form-control" id="res_notes" name="notes" rows="2"
                                    placeholder="Contoh: Fokus latihan kaki / butuh pendampingan instruktur...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitReservasi">
                            <i class="bx bx-check me-1"></i> Jadwalkan Kunjungan Sekarang
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
            // === 1. FITUR PENCARIAN INSTAN MEMBER (SEARCH BAR MIN 3 HURUF, ZERO LOADING) ===
            const activeMembers = @json($activeMembersList ?? []);
            const searchInput = document.getElementById('member_search_input');
            const searchResults = document.getElementById('member_search_results');
            const searchWrapper = document.getElementById('member_search_wrapper');
            const selectedBox = document.getElementById('selected_member_box');
            const selectedIdInput = document.getElementById('selected_member_id');
            const btnChange = document.getElementById('btn_change_selected_member');

            function selectMember(member) {
                if (!member) return;
                selectedIdInput.value = member.id;
                searchWrapper.classList.add('d-none');
                searchResults.style.display = 'none';

                document.getElementById('selected_member_name').textContent = member.name;
                document.getElementById('selected_member_code').textContent = member.member_code;
                document.getElementById('selected_member_package').textContent = member.package_name;

                const avatarContainer = document.getElementById('selected_member_avatar');
                if (member.avatar_url) {
                    avatarContainer.innerHTML =
                        `<img src="${member.avatar_url}" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">`;
                } else {
                    avatarContainer.innerHTML =
                        `<span class="avatar-initial rounded-circle bg-secondary text-white fw-bold">${member.initials}</span>`;
                }

                selectedBox.classList.remove('d-none');
                selectedBox.classList.add('d-flex');

                // Update 2 card jenis kunjungan sesuai paket member yang dipilih
                if (typeof updateServiceCards === 'function') {
                    updateServiceCards(member.allowed_categories || []);
                }
            }

            if (btnChange) {
                btnChange.addEventListener('click', function() {
                    selectedIdInput.value = '';
                    selectedBox.classList.add('d-none');
                    selectedBox.classList.remove('d-flex');
                    searchWrapper.classList.remove('d-none');
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                    }
                    if (typeof updateServiceCards === 'function') {
                        updateServiceCards([]);
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const q = this.value.trim().toLowerCase();
                    if (q.length < 3) {
                        searchResults.style.display = 'none';
                        searchResults.innerHTML = '';
                        return;
                    }

                    const matches = activeMembers.filter(m => {
                        const name = (m.name || '').toLowerCase();
                        const code = (m.member_code || '').toLowerCase();
                        const phone = (m.phone || '').toLowerCase();
                        const email = (m.email || '').toLowerCase();
                        return name.includes(q) || code.includes(q) || phone.includes(q) || email
                            .includes(q);
                    });

                    if (matches.length === 0) {
                        searchResults.innerHTML =
                            '<div class="p-3 text-center text-muted small"><i class="bx bx-user-x me-1"></i> Tidak ditemukan member aktif dengan kata kunci tersebut.</div>';
                        searchResults.style.display = 'block';
                        return;
                    }

                    let html = '';
                    matches.forEach(m => {
                        const avatarHtml = m.avatar_url ?
                            `<img src="${m.avatar_url}" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;">` :
                            `<span class="avatar-initial rounded-circle bg-secondary text-white fw-semibold" style="font-size: 0.75rem;">${m.initials}</span>`;

                        html += `
                            <a href="javascript:void(0);" class="dropdown-item p-2 border-bottom member-result-item d-flex align-items-center justify-content-between" data-id="${m.id}">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        ${avatarHtml}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark mb-0">${m.name}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">${m.member_code} &bull; ${m.phone !== '-' ? m.phone : m.email}</small>
                                    </div>
                                </div>
                                <span class="badge bg-label-primary ms-2">${m.package_name}</span>
                            </a>
                        `;
                    });

                    searchResults.innerHTML = html;
                    searchResults.style.display = 'block';

                    searchResults.querySelectorAll('.member-result-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const memId = this.getAttribute('data-id');
                            const mem = activeMembers.find(m => m.id == memId);
                            if (mem) {
                                selectMember(mem);
                            }
                        });
                    });
                });

                document.addEventListener('click', function(e) {
                    if (searchWrapper && !searchWrapper.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });

                const oldMemberId = "{{ old('member_id') }}";
                if (oldMemberId) {
                    const oldMem = activeMembers.find(m => m.id == oldMemberId);
                    if (oldMem) {
                        selectMember(oldMem);
                    }
                }
            }

            // === 2. FITUR 2 CARD CHECKLIST JENIS KUNJUNGAN & REALTIME JADWAL + KUOTA SLOT ===
            const slotSelect = document.getElementById('res_time_slot_id');
            const dateInput = document.getElementById('res_visit_date');
            const placeholderBox = document.getElementById('schedulePlaceholder');
            const cardBox = document.getElementById('scheduleCardBox');
            const loadingBox = document.getElementById('scheduleLoadingBox');
            const closedBox = document.getElementById('scheduleClosedBox');
            const submitBtn = document.getElementById('btnSubmitReservasi');
            const serviceCards = document.querySelectorAll('.service-slot-card');

            window.selectServiceCard = function(slotId) {
                const targetCard = document.getElementById('service_card_' + slotId);
                if (!targetCard) return;

                const radio = targetCard.querySelector('.service-card-radio');
                if (radio && radio.disabled) return;

                serviceCards.forEach(c => {
                    c.classList.remove('border-primary');
                    c.style.borderColor = '#d9dee3';
                    c.style.backgroundColor = '#ffffff';
                    const r = c.querySelector('.service-card-radio');
                    if (r && !r.disabled) r.checked = false;
                });

                targetCard.classList.add('border-primary');
                targetCard.style.borderColor = '#696cff';
                targetCard.style.backgroundColor = 'rgba(105, 108, 255, 0.04)';
                if (radio) radio.checked = true;

                slotSelect.value = slotId;
                updateRealtimeSchedule();
            };

            window.updateServiceCards = function(allowedCategories = []) {
                let autoSelectId = null;
                let enabledCount = 0;

                serviceCards.forEach(card => {
                    const slotId = card.getAttribute('data-slot-id');
                    const category = card.getAttribute('data-category');
                    const radio = card.querySelector('.service-card-radio');
                    const badge = document.getElementById('service_badge_' + slotId);

                    if (!allowedCategories || allowedCategories.length === 0) {
                        // Belum ada member yang dipilih
                        card.classList.remove('border-primary');
                        card.style.opacity = '0.65';
                        card.style.cursor = 'not-allowed';
                        card.style.borderColor = '#d9dee3';
                        card.style.backgroundColor = '#fbfbfb';
                        if (radio) {
                            radio.disabled = true;
                            radio.checked = false;
                        }
                        if (badge) {
                            badge.className = 'badge bg-label-secondary service-card-badge';
                            badge.textContent = 'Pilih Member';
                        }
                        return;
                    }

                    const isAllowed = allowedCategories.includes(category);
                    if (isAllowed) {
                        enabledCount++;
                        card.style.opacity = '1';
                        card.style.cursor = 'pointer';
                        card.style.backgroundColor = '#ffffff';
                        if (radio) radio.disabled = false;
                        if (badge) {
                            badge.className = 'badge bg-label-success service-card-badge';
                            badge.textContent = 'Termasuk Paket';
                        }

                        if (slotSelect.value == slotId || enabledCount === 1) {
                            autoSelectId = slotId;
                        }
                    } else {
                        // Nonaktif / Tidak Berlangganan
                        card.classList.remove('border-primary');
                        card.style.opacity = '0.5';
                        card.style.cursor = 'not-allowed';
                        card.style.borderColor = '#e7eaf0';
                        card.style.backgroundColor = '#f8f9fa';
                        if (radio) {
                            radio.disabled = true;
                            radio.checked = false;
                        }
                        if (badge) {
                            badge.className = 'badge bg-label-secondary service-card-badge';
                            badge.textContent = 'Tidak Berlangganan';
                        }

                        if (slotSelect.value == slotId) {
                            slotSelect.value = '';
                        }
                    }
                });

                if (autoSelectId) {
                    selectServiceCard(autoSelectId);
                } else {
                    updateRealtimeSchedule();
                }
            };

            serviceCards.forEach(card => {
                card.addEventListener('click', function() {
                    const slotId = this.getAttribute('data-slot-id');
                    const radio = this.querySelector('.service-card-radio');

                    if (radio && radio.disabled) {
                        const memberSelected = selectedIdInput ? selectedIdInput.value : '';
                        if (!memberSelected && @json(!$isMember)) {
                            alert(
                                'Silakan cari dan pilih member terlebih dahulu pada kolom pencarian di atas.'
                                );
                            if (searchInput) searchInput.focus();
                        } else {
                            alert(
                                'Member tidak berlangganan sesi kunjungan ini pada paket membership aktifnya.'
                                );
                        }
                        return;
                    }

                    selectServiceCard(slotId);
                });
            });

            function updateRealtimeSchedule() {
                const slotId = slotSelect.value;
                const selectedDate = dateInput ? dateInput.value : '';

                if (!selectedDate) {
                    return;
                }

                // Cek apakah hari Minggu
                const d = new Date(selectedDate + 'T00:00:00');
                if (d.getDay() === 0) {
                    placeholderBox.classList.add('d-none');
                    cardBox.classList.add('d-none');
                    loadingBox.classList.add('d-none');
                    closedBox.classList.remove('d-none');
                    if (submitBtn) submitBtn.disabled = true;
                    return;
                } else {
                    closedBox.classList.add('d-none');
                    if (submitBtn) submitBtn.disabled = false;
                }

                // Jika jenis kunjungan belum dipilih, tampilkan placeholder instruksi
                if (!slotId) {
                    placeholderBox.classList.remove('d-none');
                    cardBox.classList.add('d-none');
                    loadingBox.classList.add('d-none');
                    return;
                }

                // Jika jenis kunjungan sudah dipilih, muat jadwal & kuota slot realtime
                placeholderBox.classList.add('d-none');
                cardBox.classList.add('d-none');
                loadingBox.classList.remove('d-none');

                fetch("{{ route('reservations.available-slots') }}?date=" + encodeURIComponent(selectedDate))
                    .then(response => response.json())
                    .then(res => {
                        loadingBox.classList.add('d-none');
                        if (!res.slots || res.slots.length === 0) {
                            cardBox.innerHTML =
                                '<div class="p-3 border rounded text-center text-muted small">Tidak ada time slot aktif.</div>';
                            cardBox.classList.remove('d-none');
                            return;
                        }

                        const slot = res.slots.find(s => s.id == slotId);
                        if (!slot) {
                            cardBox.innerHTML =
                                '<div class="p-3 border rounded text-center text-muted small">Informasi slot tidak ditemukan.</div>';
                            cardBox.classList.remove('d-none');
                            return;
                        }

                        const badgeClass = slot.is_full ? 'bg-label-danger' : (slot.remaining <= 10 ?
                            'bg-label-warning' : 'bg-label-success');
                        const statusText = slot.is_full ? 'Kuota Penuh' : 'Tersedia (' + slot.remaining +
                            ' Slot)';

                        if (slot.is_full) {
                            if (submitBtn) submitBtn.disabled = true;
                        } else {
                            if (submitBtn) submitBtn.disabled = false;
                        }

                        cardBox.innerHTML = `
                            <div class="card p-3 border rounded bg-lighter shadow-none">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="text-muted small d-block">Jadwal Sesi</span>
                                        <span class="fw-semibold text-dark fs-6">
                                            <i class="bx bx-time-five me-1 text-primary"></i> ${slot.time_range}
                                        </span>
                                        <span class="text-muted ms-1">(${slot.name})</span>
                                    </div>
                                    <div>
                                        <span class="badge ${badgeClass} fs-6 py-2 px-3">${statusText}</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted pt-2 mt-2 border-top">
                                    <span>Kapasitas: <strong class="text-dark">${slot.capacity} org</strong></span>
                                    <span>Terjadwal: <strong class="text-dark">${slot.occupied} org</strong></span>
                                    <span>Sisa Kuota: <strong class="${slot.is_full ? 'text-danger' : 'text-success'}">${slot.remaining} org</strong></span>
                                </div>
                                ${slot.is_full ? '<div class="alert alert-danger p-2 small mt-2 mb-0"><i class="bx bx-x-circle me-1"></i> Kuota untuk sesi ini telah penuh pada tanggal yang dipilih. Silakan pilih tanggal lain.</div>' : ''}
                            </div>
                        `;
                        cardBox.classList.remove('d-none');
                    })
                    .catch(err => {
                        loadingBox.classList.add('d-none');
                        cardBox.innerHTML =
                            '<div class="p-3 border rounded text-center text-danger small">Gagal memuat status kuota slot secara realtime.</div>';
                        cardBox.classList.remove('d-none');
                    });
            }

            if (dateInput) {
                dateInput.addEventListener('change', updateRealtimeSchedule);
            }

            // Inisialisasi awal saat modal / form dimuat
            @if ($isMember)
                const myAllowedCategories = @json($myAllowedCategories ?? []);
                updateServiceCards(myAllowedCategories);
            @else
                updateServiceCards([]);
            @endif

            const oldSlotId = "{{ old('time_slot_id') }}";
            if (oldSlotId) {
                selectServiceCard(oldSlotId);
            }

            // === 3. VALIDASI SUBMIT FORM ===
            const form = document.getElementById('formTambahReservasi');
            if (form) {
                form.addEventListener('submit', function(e) {
                    @if (!$isMember)
                        if (!selectedIdInput.value) {
                            e.preventDefault();
                            alert('Silakan pilih member terlebih dahulu melalui kolom pencarian.');
                            if (searchInput) searchInput.focus();
                            return false;
                        }
                    @endif

                    if (!slotSelect.value) {
                        e.preventDefault();
                        alert('Silakan pilih Jenis Kunjungan (klik salah satu kartu sesi yang aktif).');
                        return false;
                    }
                });
            }
        });
    </script>
@endpush
