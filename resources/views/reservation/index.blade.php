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
                                    <span class="fw-bold text-primary font-monospace">{{ $res->code }}</span>
                                    <small class="d-block text-muted">{{ $res->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                @if (!$isMember)
                                    <td>
                                        <div class="fw-semibold text-heading">{{ $res->member->user->name ?? '-' }}</div>
                                        <small
                                            class="text-muted font-monospace">{{ $res->member->member_code ?? '-' }}</small>
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
                                    <span class="text-body font-monospace">{{ $res->timeSlot->time_range ?? '-' }}</span>
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
                                            title="Detail Reservasi & Greedy Assignment">
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
                                            <h5 class="modal-title fw-bold">
                                                <i class="bx bx-calendar-detail text-primary me-1"></i> Detail Reservasi
                                                Kunjungan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center pb-3 border-bottom mb-3">
                                                <span
                                                    class="badge bg-label-primary font-monospace fs-6 px-3 py-2">{{ $res->code }}</span>
                                                <h6 class="mt-2 mb-0">{{ $res->member->user->name ?? '-' }}</h6>
                                                <small class="text-muted">{{ $res->member->member_code ?? '-' }} &bull;
                                                    {{ $res->member->user->email ?? '-' }}</small>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Waktu</small>
                                                    <span
                                                        class="fw-semibold">{{ $res->visit_date->locale('id')->translatedFormat('l, d F Y') }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Paket</small>
                                                    <span
                                                        class="fw-semibold">{{ $res->membership->product->name ?? '-' }}</span>
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <small class="text-muted d-block">Jenis Kunjungan</small>
                                                    <span class="fw-semibold text-primary">
                                                        {{ $res->timeSlot->name ?? ($res->membership->product->name ?? 'Fitness') }}
                                                    </span>
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <small class="text-muted d-block">Waktu Sesi</small>
                                                    <span class="fw-semibold font-monospace">
                                                        {{ $res->timeSlot ? $res->timeSlot->time_range : 'Belum Ditentukan' }}
                                                    </span>
                                                </div>
                                                <div class="col-6 mt-2">
                                                    <small class="text-muted d-block">Status Kunjungan</small>
                                                    <span
                                                        class="badge bg-label-{{ $res->status === 'scheduled' ? 'success' : ($res->status === 'pending' ? 'warning' : ($res->status === 'completed' ? 'info' : 'danger')) }}">
                                                        {{ strtoupper($res->status) }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($res->schedule)
                                                <div class="card bg-lighter border mb-3">
                                                    <div class="card-body p-3">
                                                        <h6 class="mb-1 text-primary"><i class="bx bx-cog me-1"></i> Info
                                                            Optimasi Algoritma Greedy</h6>
                                                        <small class="d-block text-muted mb-1">Kode Jadwal: <strong
                                                                class="text-dark">{{ $res->schedule->schedule_code }}</strong></small>
                                                        <small
                                                            class="text-dark">{{ $res->schedule->notes ?? 'Dialokasikan oleh Algoritma Greedy untuk menjaga keseimbangan beban gym.' }}</small>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($res->notes)
                                                <div class="mb-2">
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
                        <h5 class="modal-title fw-bold">
                            <i class="bx bx-calendar-plus text-primary me-1"></i> Buat Reservasi Kunjungan Gym
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Alert Penjelasan Algoritma Greedy -->
                        <div class="alert alert-primary d-flex align-items-center mb-3">
                            <i class="bx bx-brain fs-3 me-2"></i>
                            <div class="small">
                                <strong>Sistem Penjadwalan Cerdas Algoritma Greedy:</strong>
                                Sistem akan secara otomatis menganalisis dan merekomendasikan Time Slot yang paling seimbang
                                beban kunjungannya untuk mencegah penumpukan massa di gym Indo Fitness Tondano.
                            </div>
                        </div>

                        <div class="row g-3">
                            @if (!$isMember)
                                <div class="col-12">
                                    <label class="form-label" for="select_member_id">Pilih Member (Membership Aktif) <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="select_member_id" name="member_id" required>
                                        <option value="">-- Pilih Member --</option>
                                        @foreach ($activeMembers as $m)
                                            <option value="{{ $m->id }}"
                                                {{ old('member_id') == $m->id ? 'selected' : '' }}>
                                                {{ $m->user->name }} ({{ $m->member_code }}) - Paket:
                                                {{ $m->memberships->first()->product->name ?? 'Aktif' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hanya member yang memiliki paket membership aktif yang dapat
                                        melakukan reservasi.</small>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="p-3 bg-lighter rounded border">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small d-block">Paket Membership Anda:</span>
                                                <strong
                                                    class="text-primary fs-6">{{ $myActiveMembership->product->name ?? 'Membership Aktif' }}</strong>
                                            </div>
                                            <span class="badge bg-label-success">
                                                Berlaku s/d
                                                {{ $myActiveMembership ? $myActiveMembership->end_date->format('d M Y') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label" for="res_visit_date">Tanggal Kunjungan <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="res_visit_date" name="visit_date"
                                    min="{{ date('Y-m-d') }}" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                                <small class="text-muted">Pilih tanggal rencana Anda berolahraga.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="res_time_slot_id">Preferensi Time Slot (Opsional)</label>
                                <select class="form-select" id="res_time_slot_id" name="time_slot_id">
                                    <option value="">-- Otomatis Pilih Terbaik (Algoritma Greedy) --</option>
                                    @foreach ($timeSlots as $slot)
                                        <option value="{{ $slot->id }}"
                                            {{ old('time_slot_id') == $slot->id ? 'selected' : '' }}>
                                            {{ $slot->name }} ({{ $slot->time_range }}) - Kuota: {{ $slot->capacity }}
                                            org
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika dibiarkan kosong, Algoritma Greedy akan memilihkan sesi
                                    paling renggang.</small>
                            </div>

                            <!-- Live Slot Capacity Monitor -->
                            <div class="col-12 mt-3">
                                <label class="form-label small fw-bold text-muted mb-1">Ketersediaan Kuota Slot Waktu pada
                                    Tanggal Terpilih:</label>
                                <div id="slotCapacityContainer" class="p-2 border rounded bg-lighter">
                                    <div class="text-center py-2 text-muted small" id="slotLoadingNotice">
                                        <i class="bx bx-loader-alt bx-spin me-1"></i> Memuat status slot waktu...
                                    </div>
                                    <div class="row g-2" id="slotCardsRow"></div>
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
            var dateInput = document.getElementById('res_visit_date');
            var container = document.getElementById('slotCardsRow');
            var loading = document.getElementById('slotLoadingNotice');

            function checkSlotAvailability(selectedDate) {
                if (!selectedDate) return;
                loading.style.display = 'block';
                container.innerHTML = '';

                fetch("{{ route('reservations.available-slots') }}?date=" + encodeURIComponent(selectedDate))
                    .then(response => response.json())
                    .then(res => {
                        loading.style.display = 'none';
                        if (!res.slots || res.slots.length === 0) {
                            container.innerHTML =
                                '<div class="col-12 text-center text-muted small py-2">Tidak ada time slot aktif.</div>';
                            return;
                        }

                        var html = '';
                        res.slots.forEach(slot => {
                            var badgeColor = slot.remaining === 0 ? 'bg-danger' : (slot.remaining <= 3 ?
                                'bg-warning' : 'bg-success');
                            var cardBg = slot.remaining === 0 ? 'border-danger' : 'border-success';

                            html += `
                                <div class="col-sm-6 col-md-4">
                                    <div class="card p-2 border ${cardBg} h-100 shadow-none">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold small text-truncate">${slot.name}</span>
                                            <span class="badge ${badgeColor}">${slot.remaining === 0 ? 'Penuh' : 'Sisa ' + slot.remaining}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                                            <span><i class="bx bx-time me-1"></i>${slot.time_range}</span>
                                            <span>${slot.occupied}/${slot.capacity} org</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        loading.style.display = 'none';
                        container.innerHTML =
                            '<div class="col-12 text-center text-danger small py-2">Gagal memuat kuota slot.</div>';
                    });
            }

            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    checkSlotAvailability(this.value);
                });
                checkSlotAvailability(dateInput.value);
            }
        });
    </script>
@endpush
