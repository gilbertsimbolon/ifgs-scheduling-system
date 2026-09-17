@extends('layouts.app')

@section('title', 'Sesi Trainer')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Sesi Trainer
                </h5>
                <div class="text-muted small">Kelola permohonan dan persetujuan sesi latihan personal trainer (Jam
                    Operasional Gym: 08:00 - 20:00 WITA).</div>
            </div>
            <div class="d-flex gap-2">
                @if (Route::has('trainers.index'))
                    <a href="{{ route('trainers.index') }}" class="btn btn-outline-primary">
                        <i class="bx bx-run me-1"></i> Data Trainer
                    </a>
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

        @if (session('whatsapp_open_url'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center mb-3" role="alert">
                <i class="bx bxl-whatsapp fs-3 text-success me-2"></i>
                <div class="flex-grow-1">
                    <strong>Pemberitahuan Berhasil Diproses!</strong> Pesan konfirmasi siap dikirimkan ke WhatsApp
                    pelanggan.
                    <a href="{{ session('whatsapp_open_url') }}" target="_blank" class="btn btn-xs btn-success ms-2">
                        <i class="bx bxl-whatsapp me-1"></i> Buka WhatsApp Sekarang
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
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

        <!-- 4 Quick Summary Cards (3 Alur Utama + Disetujui) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time-five fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Belum Disetujui</span>
                            <h4 class="card-title mb-0 fw-bold">{{ $metrics['pending'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-check-circle fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Disetujui</span>
                            <h4 class="card-title mb-0 fw-bold">{{ $metrics['approved'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-run fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Sedang Berjalan</span>
                            <h4 class="card-title mb-0 fw-bold">{{ $metrics['in_progress'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-badge-check fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Selesai</span>
                            <h4 class="card-title mb-0 fw-bold">{{ $metrics['completed'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card shadow-sm">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('trainer-bookings.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="search">Cari Permohonan</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Kode, member, atau trainer..." value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum
                                Disetujui</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang
                                Berjalan</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>

                    @if (!auth()->user()->hasRole('Trainer'))
                        <div class="col-md-3">
                            <label class="form-label" for="trainer_id">Filter Trainer</label>
                            <select name="trainer_id" id="trainer_id" class="form-select">
                                <option value="">Semua Trainer</option>
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer->id }}"
                                        {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                        {{ $trainer->user?->name ?? 'Trainer #' . $trainer->id }}
                                        ({{ $trainer->specialization ?? 'Fitness' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2">
                        <label class="form-label" for="session_date">Tanggal</label>
                        <input type="date" id="session_date" name="session_date" class="form-control"
                            value="{{ request('session_date') }}" />
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('trainer-bookings.index') }}" class="btn btn-outline-secondary"
                            title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Kode Booking</th>
                            <th>Hari & Tanggal</th>
                            <th>Pelanggan / Member</th>
                            <th>Trainer Bertugas</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($bookings as $index => $item)
                            @php
                                $memberName = $item->member?->user?->name ?? 'Member Dihapus';
                                $memberCode = $item->member?->member_code ?? '-';
                                $memberEmail = $item->member?->user?->email ?? '-';
                                $memberPhone = $item->member?->phone ?? ($item->member?->phone_number ?? '-');
                                $trainerName = $item->trainer?->user?->name ?? 'Trainer Dihapus';
                                $trainerSpec = $item->trainer?->specialization ?? 'General Fitness';
                                $trainerPhone = $item->trainer?->phone ?? '-';
                                $trainerBio = $item->trainer?->bio ?? '';
                                $sessionDateFormatted = $item->session_date
                                    ? $item->session_date->translatedFormat('l, d F Y')
                                    : '-';
                                $createdAtFormatted = $item->created_at
                                    ? $item->created_at->translatedFormat('d M Y, H:i')
                                    : '-';
                                $approvedAtFormatted = $item->approved_at
                                    ? $item->approved_at->translatedFormat('d M Y, H:i')
                                    : '-';
                                $completedAtFormatted = $item->completed_at
                                    ? $item->completed_at->translatedFormat('d M Y, H:i')
                                    : '-';
                                $rejectionReason = $item->rejection_reason ?? '';
                                $waUrl = $item->whatsapp_url;
                            @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted">
                                    {{ $bookings->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $item->booking_code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $sessionDateFormatted }}</div>
                                    <div class="small text-muted"><i class="bx bx-time-five me-1"></i>08:00 - 20:00 WITA
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                {{ strtoupper(substr($memberName, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $memberName }}</div>
                                            <div class="small text-muted">{{ $memberCode }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $trainerName }}</div>
                                    <div class="small text-muted"><i
                                            class="bx bx-badge-check me-1 text-primary"></i>{{ $trainerSpec }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $item->status_badge_class }}">
                                        {{ $item->status_label }}
                                    </span>
                                    @if ($item->status === \App\Models\TrainerBooking::STATUS_REJECTED && $rejectionReason)
                                        <div class="small text-danger mt-1" title="{{ $rejectionReason }}">
                                            <i class="bx bx-info-circle"></i>
                                            {{ \Illuminate\Support\Str::limit($rejectionReason, 20) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- 1. Tombol Detail -->
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-outline-info btn-detail-booking"
                                            data-bs-toggle="modal" data-bs-target="#modalDetailTrainerBooking"
                                            data-id="{{ $item->id }}" data-code="{{ $item->booking_code }}"
                                            data-member-name="{{ $memberName }}"
                                            data-member-code="{{ $memberCode }}" data-email="{{ $memberEmail }}"
                                            data-phone="{{ $memberPhone }}" data-trainer-name="{{ $trainerName }}"
                                            data-trainer-spec="{{ $trainerSpec }}"
                                            data-trainer-phone="{{ $trainerPhone }}"
                                            data-trainer-bio="{{ $trainerBio }}"
                                            data-session-date="{{ $sessionDateFormatted }}"
                                            data-notes="{{ $item->notes ?? '-' }}" data-status="{{ $item->status }}"
                                            data-status-label="{{ $item->status_label }}"
                                            data-status-class="{{ $item->status_badge_class }}"
                                            data-created-at="{{ $createdAtFormatted }}"
                                            data-approved-at="{{ $approvedAtFormatted }}"
                                            data-completed-at="{{ $completedAtFormatted }}"
                                            data-rejection-reason="{{ $rejectionReason }}"
                                            data-wa-url="{{ $waUrl ?? '' }}"
                                            data-acc-action="{{ route('trainer-bookings.approve', $item) }}"
                                            data-tolak-action="{{ route('trainer-bookings.reject', $item) }}"
                                            title="Detail Janji Sesi">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        @if ($item->status === \App\Models\TrainerBooking::STATUS_PENDING)
                                            <!-- 2. Tombol Menyetujui (ACC) -->
                                            <button type="button" class="btn btn-sm btn-icon btn-success btn-acc-booking"
                                                data-bs-toggle="modal" data-bs-target="#modalAccTrainerBooking"
                                                data-action="{{ route('trainer-bookings.approve', $item) }}"
                                                data-code="{{ $item->booking_code }}"
                                                data-member-name="{{ $memberName }}"
                                                data-member-phone="{{ $memberPhone !== '-' ? $memberPhone : '' }}"
                                                data-trainer-name="{{ $trainerName }}"
                                                data-trainer-spec="{{ $trainerSpec }}"
                                                data-trainer-phone="{{ $trainerPhone }}"
                                                data-trainer-bio="{{ $trainerBio }}"
                                                data-session-date="{{ $sessionDateFormatted }}"
                                                title="Menyetujui & Kirim WA">
                                                <i class="bx bx-check"></i>
                                            </button>

                                            <!-- 3. Tombol Menolak -->
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-danger btn-tolak-booking"
                                                data-bs-toggle="modal" data-bs-target="#modalTolakTrainerBooking"
                                                data-action="{{ route('trainer-bookings.reject', $item) }}"
                                                data-code="{{ $item->booking_code }}"
                                                data-member-name="{{ $memberName }}"
                                                data-member-phone="{{ $memberPhone !== '-' ? $memberPhone : '' }}"
                                                data-trainer-name="{{ $trainerName }}"
                                                data-session-date="{{ $sessionDateFormatted }}"
                                                title="Menolak & Kirim WA">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        @elseif (
                                            $item->status === \App\Models\TrainerBooking::STATUS_APPROVED ||
                                                $item->status === \App\Models\TrainerBooking::STATUS_IN_PROGRESS)
                                            <!-- Tombol Chat WhatsApp -->
                                            <a href="{{ $waUrl ?? 'javascript:void(0);' }}"
                                                @if ($waUrl) target="_blank" @endif
                                                class="btn btn-sm btn-icon btn-outline-success {{ !$waUrl ? 'btn-wa-prompt' : '' }}"
                                                @if (!$waUrl) data-id="{{ $item->id }}"
                                                    data-code="{{ $item->booking_code }}"
                                                    data-member-name="{{ $memberName }}"
                                                    data-trainer-name="{{ $trainerName }}"
                                                    data-trainer-spec="{{ $trainerSpec }}"
                                                    data-trainer-phone="{{ $trainerPhone }}"
                                                    data-trainer-bio="{{ $trainerBio }}"
                                                    data-session-date="{{ $sessionDateFormatted }}" @endif
                                                title="{{ $waUrl ? 'Chat WhatsApp Pelanggan' : 'Nomor WA belum ada - klik untuk masukkan' }}">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center align-middle py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center mx-auto py-3 text-center"
                                        style="white-space: normal;">
                                        <div
                                            class="avatar avatar-md rounded-circle bg-label-secondary mb-3 d-flex align-items-center justify-content-center">
                                            <i class="bx bx-calendar-x fs-2 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'status', 'trainer_id', 'session_date']))
                                                Tidak Ada Permohonan Sesi yang Sesuai
                                            @else
                                                Belum Ada Permohonan Sesi Latihan
                                            @endif
                                        </h6>
                                        <p class="text-muted small mb-0 text-center" style="max-width: 420px;">
                                            @if (request()->hasAny(['search', 'status', 'trainer_id', 'session_date']))
                                                Tidak ditemukan data sesi latihan dengan filter saat ini. Coba ubah kata
                                                kunci atau reset filter.
                                            @else
                                                Belum ada data permohonan sesi latihan personal trainer yang tercatat di
                                                sistem.
                                            @endif
                                        </p>
                                        @if (request()->hasAny(['search', 'status', 'trainer_id', 'session_date']))
                                            <a href="{{ route('trainer-bookings.index') }}"
                                                class="btn btn-sm btn-outline-primary mt-3">
                                                <i class="bx bx-refresh me-1"></i> Reset Filter
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($bookings->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        Menampilkan {{ $bookings->firstItem() }} sampai {{ $bookings->lastItem() }} dari
                        {{ $bookings->total() }} permohonan
                    </span>
                    <div>
                        {{ $bookings->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    @include('trainer_booking.modals.detail')
    @include('trainer_booking.modals.acc')
    @include('trainer_booking.modals.tolak')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper WhatsApp URL
            function formatWaUrl(phone, memberName) {
                if (!phone || phone === '-') return '#';
                let cleaned = phone.replace(/[^0-9]/g, '');
                if (cleaned.startsWith('0')) {
                    cleaned = '62' + cleaned.substring(1);
                } else if (!cleaned.startsWith('62')) {
                    cleaned = '62' + cleaned;
                }
                const msg = encodeURIComponent(
                    `Halo ${memberName}, ini dari Indo Fitness Gym Sport mengenai sesi latihan Personal Trainer Anda.`
                    );
                return `https://wa.me/${cleaned}?text=${msg}`;
            }

            // Generator Pesan WhatsApp Konfirmasi Persetujuan (Bersedia)
            function generateTrainerWaMessage(data) {
                const memberName = data.memberName || 'Member';
                const trainerName = data.trainerName || 'Trainer';
                const trainerSpec = data.trainerSpec || 'General Fitness';
                const trainerPhone = data.trainerPhone || '-';
                const trainerBio = data.trainerBio ? `\n• Profil: ${data.trainerBio}` : '';
                const bookingCode = data.code || '-';
                const sessionDate = data.sessionDate || '-';

                return `Halo ${memberName}! 👋\n\n` +
                    `Saya Coach *${trainerName}* dari *Indo Fitness Gym Sport*.\n\n` +
                    `Menindaklanjuti permohonan pendampingan latihan Anda (*${bookingCode}*), dengan ini saya menyatakan *BERSEDIA* mendampingi latihan Anda pada:\n` +
                    `📅 *Hari & Tanggal:* ${sessionDate}\n` +
                    `⏰ *Jam Operasional Gym:* 08:00 - 20:00 WITA\n\n` +
                    `👤 *Data Diri Personal Trainer:*\n` +
                    `• Nama: Coach ${trainerName}\n` +
                    `• Spesialisasi: ${trainerSpec}\n` +
                    `• No. HP/WA: ${trainerPhone}` +
                    `${trainerBio}\n\n` +
                    `Sampai jumpa di gym! Silakan lakukan scan kehadiran saat tiba di gym. Tetap semangat! 💪🔥\n` +
                    `*Indo Fitness Gym Sport Tondano*`;
            }

            // Generator Pesan WhatsApp Penolakan (Berhalangan)
            function generateTrainerWaRejectMessage(data, reason) {
                const memberName = data.memberName || 'Member';
                const trainerName = data.trainerName || 'Trainer';
                const sessionDate = data.sessionDate || '-';
                const bookingCode = data.code || '-';
                const reasonText = reason || 'Ada jadwal khusus yang tidak dapat ditinggalkan.';

                return `Halo ${memberName}! 🙏\n\n` +
                    `Saya Coach *${trainerName}* dari *Indo Fitness Gym Sport*.\n\n` +
                    `Terkait permohonan pendampingan latihan Anda (*${bookingCode}*) untuk hari *${sessionDate}*, saya memohon maaf sebesar-besarnya karena *BERHALANGAN / TIDAK DAPAT MENDAMPINGI* pada tanggal tersebut.\n\n` +
                    `📋 *Alasan:* ${reasonText}\n\n` +
                    `Anda dapat memilih jadwal hari lain atau memilih personal trainer lainnya yang tersedia di sistem IFGS. Terima kasih atas pengertiannya! 🙏\n` +
                    `*Indo Fitness Gym Sport Tondano*`;
            }

            // ACC Modal
            let currentAccData = {};
            const modalAcc = document.getElementById('modalAccTrainerBooking');
            if (modalAcc) {
                modalAcc.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const data = {
                        action: btn.getAttribute('data-action') || '',
                        code: btn.getAttribute('data-code') || '-',
                        memberName: btn.getAttribute('data-member-name') || '-',
                        memberPhone: btn.getAttribute('data-member-phone') || '',
                        trainerName: btn.getAttribute('data-trainer-name') || '-',
                        trainerSpec: btn.getAttribute('data-trainer-spec') || '-',
                        trainerPhone: btn.getAttribute('data-trainer-phone') || '-',
                        trainerBio: btn.getAttribute('data-trainer-bio') || '',
                        sessionDate: btn.getAttribute('data-session-date') || '-',
                        waUrl: btn.getAttribute('data-wa-url') || '',
                    };
                    currentAccData = data;

                    const form = document.getElementById('formAccTrainerBooking');
                    if (form) form.action = data.action;

                    document.getElementById('accBookingOpenWa').value = '0';
                    document.getElementById('accBookingMemberName').textContent = data.memberName;
                    document.getElementById('accBookingCode').textContent = data.code;
                    document.getElementById('accBookingSessionDate').textContent = data.sessionDate;

                    document.getElementById('accBookingTrainerName').textContent = data.trainerName;
                    document.getElementById('accBookingTrainerSpec').textContent = data.trainerSpec;
                    document.getElementById('accBookingTrainerPhone').textContent = data.trainerPhone;

                    const phoneInput = document.getElementById('accBookingMemberPhone');
                    if (phoneInput) phoneInput.value = data.memberPhone;

                    const messageTextarea = document.getElementById('accBookingWaMessageText');
                    if (messageTextarea) {
                        messageTextarea.value = generateTrainerWaMessage(data);
                    }
                });

                // Tombol Salin Pesan WA ACC
                const btnCopy = document.getElementById('btnCopyWaMessage');
                if (btnCopy) {
                    btnCopy.addEventListener('click', function() {
                        const messageTextarea = document.getElementById('accBookingWaMessageText');
                        if (messageTextarea) {
                            navigator.clipboard.writeText(messageTextarea.value).then(() => {
                                const originalText = btnCopy.innerHTML;
                                btnCopy.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                                btnCopy.classList.replace('btn-outline-secondary', 'btn-success');
                                setTimeout(() => {
                                    btnCopy.innerHTML = originalText;
                                    btnCopy.classList.replace('btn-success',
                                        'btn-outline-secondary');
                                }, 2000);
                            });
                        }
                    });
                }

                // Tombol Setujui & Kirim WhatsApp
                const btnSubmitAndWa = document.getElementById('btnSubmitAccAndWa');
                if (btnSubmitAndWa) {
                    btnSubmitAndWa.addEventListener('click', function() {
                        const phoneInput = document.getElementById('accBookingMemberPhone');
                        let phoneVal = phoneInput ? phoneInput.value.trim() : '';

                        if (!phoneVal) {
                            alert('Silakan masukkan nomor WhatsApp pelanggan terlebih dahulu.');
                            if (phoneInput) phoneInput.focus();
                            return;
                        }

                        let cleanPhone = phoneVal.replace(/[^0-9]/g, '');
                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = '62' + cleanPhone.substring(1);
                        } else if (!cleanPhone.startsWith('62')) {
                            cleanPhone = '62' + cleanPhone;
                        }

                        const msgText = document.getElementById('accBookingWaMessageText').value;
                        const waUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(
                        msgText);

                        // Buka WhatsApp di tab baru
                        window.open(waUrl, '_blank');

                        // Submit form ACC
                        document.getElementById('accBookingOpenWa').value = '1';
                        document.getElementById('formAccTrainerBooking').submit();
                    });
                }
            }

            // Tolak Modal
            let currentTolakData = {};
            const modalTolak = document.getElementById('modalTolakTrainerBooking');
            if (modalTolak) {
                modalTolak.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const data = {
                        action: btn.getAttribute('data-action') || '',
                        code: btn.getAttribute('data-code') || '-',
                        memberName: btn.getAttribute('data-member-name') || '-',
                        memberPhone: btn.getAttribute('data-member-phone') || '',
                        trainerName: btn.getAttribute('data-trainer-name') || '-',
                        sessionDate: btn.getAttribute('data-session-date') || '-',
                    };
                    currentTolakData = data;

                    const form = document.getElementById('formTolakTrainerBooking');
                    if (form) form.action = data.action;

                    document.getElementById('tolakBookingOpenWa').value = '0';
                    document.getElementById('tolakBookingMemberName').textContent = data.memberName;
                    document.getElementById('tolakBookingCode').textContent = data.code;
                    document.getElementById('tolakBookingSessionDate').textContent = data.sessionDate;
                    document.getElementById('tolakBookingTrainerName').textContent = data.trainerName;

                    const phoneInput = document.getElementById('tolakBookingMemberPhone');
                    if (phoneInput) phoneInput.value = data.memberPhone;

                    const reasonTextarea = document.getElementById('tolakBookingReason');
                    const messageTextarea = document.getElementById('tolakBookingWaMessageText');

                    if (reasonTextarea && messageTextarea) {
                        reasonTextarea.value = '';
                        messageTextarea.value = generateTrainerWaRejectMessage(data, '');

                        reasonTextarea.oninput = function() {
                            messageTextarea.value = generateTrainerWaRejectMessage(data, this.value
                                .trim());
                        };
                    }
                });

                // Tombol Salin Pesan WA Tolak
                const btnCopyTolak = document.getElementById('btnCopyTolakWaMessage');
                if (btnCopyTolak) {
                    btnCopyTolak.addEventListener('click', function() {
                        const messageTextarea = document.getElementById('tolakBookingWaMessageText');
                        if (messageTextarea) {
                            navigator.clipboard.writeText(messageTextarea.value).then(() => {
                                const originalText = btnCopyTolak.innerHTML;
                                btnCopyTolak.innerHTML =
                                    '<i class="bx bx-check me-1"></i> Tersalin!';
                                btnCopyTolak.classList.replace('btn-outline-secondary',
                                    'btn-success');
                                setTimeout(() => {
                                    btnCopyTolak.innerHTML = originalText;
                                    btnCopyTolak.classList.replace('btn-success',
                                        'btn-outline-secondary');
                                }, 2000);
                            });
                        }
                    });
                }

                // Tombol Tolak & Kirim WhatsApp
                const btnSubmitTolakAndWa = document.getElementById('btnSubmitTolakAndWa');
                if (btnSubmitTolakAndWa) {
                    btnSubmitTolakAndWa.addEventListener('click', function() {
                        const reasonTextarea = document.getElementById('tolakBookingReason');
                        if (!reasonTextarea || !reasonTextarea.value.trim()) {
                            alert('Silakan isi alasan penolakan terlebih dahulu.');
                            if (reasonTextarea) reasonTextarea.focus();
                            return;
                        }

                        const phoneInput = document.getElementById('tolakBookingMemberPhone');
                        let phoneVal = phoneInput ? phoneInput.value.trim() : '';

                        if (phoneVal) {
                            let cleanPhone = phoneVal.replace(/[^0-9]/g, '');
                            if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
                            else if (!cleanPhone.startsWith('62')) cleanPhone = '62' + cleanPhone;

                            const msgText = document.getElementById('tolakBookingWaMessageText').value;
                            const waUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(
                                msgText);
                            window.open(waUrl, '_blank');
                            document.getElementById('tolakBookingOpenWa').value = '1';
                        }

                        document.getElementById('formTolakTrainerBooking').submit();
                    });
                }
            }

            // Handler tombol prompt WA jika nomor belum ada
            document.querySelectorAll('.btn-wa-prompt').forEach(btn => {
                btn.addEventListener('click', function() {
                    const memberName = this.getAttribute('data-member-name') || 'Member';
                    const promptPhone = prompt(
                        `Nomor WhatsApp pelanggan "${memberName}" belum tercatat.\nMasukkan nomor WhatsApp:`,
                        '08');
                    if (promptPhone && promptPhone.trim()) {
                        let cleanPhone = promptPhone.replace(/[^0-9]/g, '');
                        if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.substring(1);
                        else if (!cleanPhone.startsWith('62')) cleanPhone = '62' + cleanPhone;

                        const data = {
                            code: this.getAttribute('data-code'),
                            memberName: memberName,
                            trainerName: this.getAttribute('data-trainer-name'),
                            trainerSpec: this.getAttribute('data-trainer-spec'),
                            trainerPhone: this.getAttribute('data-trainer-phone'),
                            trainerBio: this.getAttribute('data-trainer-bio'),
                            sessionDate: this.getAttribute('data-session-date'),
                        };
                        const msg = generateTrainerWaMessage(data);
                        window.open('https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(
                            msg), '_blank');
                    }
                });
            });

            // Detail Modal
            let currentDetailData = {};
            const modalDetail = document.getElementById('modalDetailTrainerBooking');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const data = {
                        id: btn.getAttribute('data-id'),
                        code: btn.getAttribute('data-code'),
                        memberName: btn.getAttribute('data-member-name'),
                        memberCode: btn.getAttribute('data-member-code'),
                        email: btn.getAttribute('data-email'),
                        phone: btn.getAttribute('data-phone'),
                        trainerName: btn.getAttribute('data-trainer-name'),
                        trainerSpec: btn.getAttribute('data-trainer-spec'),
                        trainerPhone: btn.getAttribute('data-trainer-phone'),
                        trainerBio: btn.getAttribute('data-trainer-bio'),
                        sessionDate: btn.getAttribute('data-session-date'),
                        notes: btn.getAttribute('data-notes'),
                        status: btn.getAttribute('data-status'),
                        statusLabel: btn.getAttribute('data-status-label'),
                        statusClass: btn.getAttribute('data-status-class'),
                        createdAt: btn.getAttribute('data-created-at'),
                        approvedAt: btn.getAttribute('data-approved-at'),
                        completedAt: btn.getAttribute('data-completed-at'),
                        rejectionReason: btn.getAttribute('data-rejection-reason'),
                        waUrl: btn.getAttribute('data-wa-url'),
                        accAction: btn.getAttribute('data-acc-action'),
                        tolakAction: btn.getAttribute('data-tolak-action'),
                    };
                    currentDetailData = data;

                    // Populate left column
                    const initials = (data.memberName || '--').substring(0, 2).toUpperCase();
                    document.getElementById('detailBookingAvatar').textContent = initials;
                    document.getElementById('detailBookingMemberName').textContent = data.memberName || '-';
                    document.getElementById('detailBookingMemberCode').textContent = data.memberCode || '-';

                    const statusBadge = document.getElementById('detailBookingStatusBadge');
                    statusBadge.className = 'badge ' + (data.statusClass || 'bg-secondary');
                    statusBadge.textContent = data.statusLabel || '-';

                    document.getElementById('detailBookingEmail').textContent = data.email || '-';
                    document.getElementById('detailBookingPhone').textContent = data.phone || '-';

                    const waLink = document.getElementById('detailBookingWaLink');
                    if (data.phone && data.phone !== '-') {
                        waLink.href = formatWaUrl(data.phone, data.memberName);
                        waLink.classList.remove('d-none');
                    } else {
                        waLink.classList.add('d-none');
                    }

                    document.getElementById('detailBookingTrainerName').textContent = data.trainerName ||
                        '-';
                    document.getElementById('detailBookingTrainerSpec').textContent = data.trainerSpec ||
                        '-';
                    document.getElementById('detailBookingTrainerPhone').textContent = data.trainerPhone ||
                        '-';

                    // Populate right column
                    document.getElementById('detailBookingCode').textContent = data.code || '-';
                    document.getElementById('detailBookingSessionDate').textContent = data.sessionDate ||
                        '-';
                    document.getElementById('detailBookingNotes').textContent = data.notes || '-';
                    document.getElementById('detailBookingCreatedAt').textContent = data.createdAt || '-';

                    // Approved row
                    const approvedRow = document.getElementById('detailBookingApprovedRow');
                    if (data.approvedAt && data.approvedAt !== '-') {
                        approvedRow.classList.remove('d-none');
                        document.getElementById('detailBookingApprovedAt').textContent = data.approvedAt;
                    } else {
                        approvedRow.classList.add('d-none');
                    }

                    // Completed row
                    const completedRow = document.getElementById('detailBookingCompletedRow');
                    if (data.completedAt && data.completedAt !== '-') {
                        completedRow.classList.remove('d-none');
                        document.getElementById('detailBookingCompletedAt').textContent = data.completedAt;
                    } else {
                        completedRow.classList.add('d-none');
                    }

                    // Reject row
                    const rejectRow = document.getElementById('detailBookingRejectRow');
                    if (data.status === 'rejected' && data.rejectionReason) {
                        rejectRow.classList.remove('d-none');
                        document.getElementById('detailBookingRejectReason').textContent = data
                            .rejectionReason;
                    } else {
                        rejectRow.classList.add('d-none');
                    }

                    // Alerts and Action Buttons
                    const pendingAlert = document.getElementById('detailBookingPendingAlert');
                    const scanNotice = document.getElementById('detailBookingScanNotice');
                    const actionButtons = document.getElementById('detailBookingActionButtons');
                    const btnWa = document.getElementById('detailBookingBtnWa');

                    if (data.status === 'pending') {
                        pendingAlert.classList.remove('d-none');
                        scanNotice.classList.add('d-none');
                        actionButtons.classList.remove('d-none');
                        if (btnWa) btnWa.classList.add('d-none');
                    } else if (data.status === 'approved' || data.status === 'in_progress') {
                        pendingAlert.classList.add('d-none');
                        scanNotice.classList.remove('d-none');
                        actionButtons.classList.add('d-none');
                        if (btnWa) {
                            btnWa.classList.remove('d-none');
                            if (data.waUrl) {
                                btnWa.href = data.waUrl;
                                btnWa.onclick = null;
                            } else {
                                btnWa.href = 'javascript:void(0);';
                                btnWa.onclick = function() {
                                    const promptPhone = prompt(
                                        `Nomor WhatsApp pelanggan "${data.memberName}" belum tercatat.\nMasukkan nomor WhatsApp:`,
                                        '08');
                                    if (promptPhone && promptPhone.trim()) {
                                        let clean = promptPhone.replace(/[^0-9]/g, '');
                                        if (clean.startsWith('0')) clean = '62' + clean.substring(1);
                                        else if (!clean.startsWith('62')) clean = '62' + clean;
                                        const msg = generateTrainerWaMessage(data);
                                        window.open('https://wa.me/' + clean + '?text=' +
                                            encodeURIComponent(msg), '_blank');
                                    }
                                };
                            }
                        }
                    } else {
                        pendingAlert.classList.add('d-none');
                        scanNotice.classList.add('d-none');
                        actionButtons.classList.add('d-none');
                        if (btnWa) btnWa.classList.add('d-none');
                    }
                });
            }

            // Quick Menyetujui button in Detail modal
            const detailBtnAcc = document.getElementById('detailBookingBtnAcc');
            if (detailBtnAcc) {
                detailBtnAcc.addEventListener('click', function() {
                    const bsDetail = bootstrap.Modal.getInstance(modalDetail);
                    if (bsDetail) bsDetail.hide();

                    setTimeout(() => {
                        const modalAccEl = document.getElementById('modalAccTrainerBooking');
                        const fakeBtn = document.createElement('button');
                        fakeBtn.setAttribute('data-action', currentDetailData.accAction);
                        fakeBtn.setAttribute('data-code', currentDetailData.code);
                        fakeBtn.setAttribute('data-member-name', currentDetailData.memberName);
                        fakeBtn.setAttribute('data-member-phone', currentDetailData.phone !== '-' ?
                            currentDetailData.phone : '');
                        fakeBtn.setAttribute('data-trainer-name', currentDetailData.trainerName);
                        fakeBtn.setAttribute('data-trainer-spec', currentDetailData.trainerSpec);
                        fakeBtn.setAttribute('data-trainer-phone', currentDetailData.trainerPhone);
                        fakeBtn.setAttribute('data-trainer-bio', currentDetailData.trainerBio);
                        fakeBtn.setAttribute('data-session-date', currentDetailData.sessionDate);
                        fakeBtn.setAttribute('data-wa-url', currentDetailData.waUrl || '');

                        const bsAcc = new bootstrap.Modal(modalAccEl);
                        modalAccEl.dispatchEvent(new CustomEvent('show.bs.modal', {
                            relatedTarget: fakeBtn
                        }));
                        bsAcc.show();
                    }, 350);
                });
            }

            // Quick Menolak button in Detail modal
            const detailBtnTolak = document.getElementById('detailBookingBtnTolak');
            if (detailBtnTolak) {
                detailBtnTolak.addEventListener('click', function() {
                    const bsDetail = bootstrap.Modal.getInstance(modalDetail);
                    if (bsDetail) bsDetail.hide();

                    setTimeout(() => {
                        const modalTolakEl = document.getElementById('modalTolakTrainerBooking');
                        const fakeBtn = document.createElement('button');
                        fakeBtn.setAttribute('data-action', currentDetailData.tolakAction);
                        fakeBtn.setAttribute('data-code', currentDetailData.code);
                        fakeBtn.setAttribute('data-member-name', currentDetailData.memberName);
                        fakeBtn.setAttribute('data-member-phone', currentDetailData.phone !== '-' ?
                            currentDetailData.phone : '');
                        fakeBtn.setAttribute('data-trainer-name', currentDetailData.trainerName);
                        fakeBtn.setAttribute('data-session-date', currentDetailData.sessionDate);

                        const bsTolak = new bootstrap.Modal(modalTolakEl);
                        modalTolakEl.dispatchEvent(new CustomEvent('show.bs.modal', {
                            relatedTarget: fakeBtn
                        }));
                        bsTolak.show();
                    }, 350);
                });
            }
        });
    </script>
@endpush
