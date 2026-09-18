@extends('layouts.app')

@section('title', 'Presensi Barcode - Indo Fitness Gym Sport')

@push('styles')
    <style>
        /* Hilangkan SEMUA MENU, Sidebar, Navbar, dan Footer agar Halaman Murni Full Screen */
        .layout-menu,
        #layout-menu,
        .layout-navbar,
        #layout-navbar,
        .content-footer,
        footer,
        .layout-menu-toggle,
        .layout-overlay {
            display: none !important;
        }

        .layout-page {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-top: 0 !important;
        }

        .content-wrapper {
            padding: 0 !important;
        }

        .layout-wrapper,
        .layout-container {
            min-height: 100vh !important;
            background: #ffffff !important;
        }

        body,
        html {
            background: #ffffff !important;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .fullscreen-kiosk {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 1rem;
            background: #ffffff;
        }

        .scanner-container {
            position: relative;
            background-color: #000000;
            border-radius: 1rem;
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        #reader {
            width: 100% !important;
            border: none !important;
        }

        #reader video {
            border-radius: 1rem;
            object-fit: cover;
            width: 100% !important;
            max-height: 340px;
        }

        .scan-laser {
            position: absolute;
            top: 20%;
            left: 8%;
            right: 8%;
            height: 2px;
            background: #00ff88;
            box-shadow: 0 0 14px 4px rgba(0, 255, 136, 0.8);
            animation: laserMove 2.2s ease-in-out infinite alternate;
            z-index: 10;
            pointer-events: none;
        }

        @keyframes laserMove {
            0% {
                top: 15%;
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }

            100% {
                top: 85%;
                opacity: 0.3;
            }
        }

        .scan-feedback-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(8px);
            z-index: 25;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            animation: fadeInOverlay 0.25s ease-out;
        }

        @keyframes fadeInOverlay {
            from {
                opacity: 0;
                transform: scale(0.96);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .feedback-icon {
            font-size: 3.8rem;
            line-height: 1;
        }

        .cooldown-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 50rem;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            color: #ffffff;
        }

        .user-friendly-note {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2b343b;
            letter-spacing: -0.2px;
            line-height: 1.5;
        }

        .slogan-badge {
            display: inline-block;
            background: #fff8e1;
            color: #b78103;
            border: 1.5px solid #ffe082;
            border-radius: 50rem;
            padding: 0.4rem 1.3rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            font-size: 1rem;
        }

        .hover-opacity-100 {
            opacity: 0.45;
            transition: opacity 0.2s ease;
        }

        .hover-opacity-100:hover {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    <!-- Toast Notifikasi Hasil Scan Mengambang di Layar -->
    <div id="scanToastNotification" class="position-fixed top-0 start-50 translate-middle-x mt-4 p-3 d-none"
        style="z-index: 9999; max-width: 90%; min-width: 320px;">
        <div id="toastAlertBox" class="alert alert-success shadow-lg border-0 d-flex align-items-center mb-0" role="alert"
            style="border-radius: 0.75rem;">
            <i id="toastIcon" class="bx bx-check-circle fs-1 me-3"></i>
            <div class="flex-grow-1">
                <h5 id="toastTitle" class="fw-bold mb-0">Check-in Berhasil!</h5>
                <div id="toastMessage" class="small mt-1">Selamat datang di Indo Fitness Gym Sport.</div>
            </div>
        </div>
    </div>

    <div class="fullscreen-kiosk">
        <!-- Floating Utility Bar Pojok Atas: Dashboard & Fullscreen -->
        <div class="position-absolute top-0 start-0 end-0 d-flex justify-content-between align-items-center p-3"
            style="z-index: 100;">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm hover-opacity-100"
                title="Kembali ke Dashboard">
                <i class="bx bx-arrow-back me-1"></i> Dashboard
            </a>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm hover-opacity-100" id="btnToggleFullscreen"
                    title="Layar Penuh">
                    <i class="bx bx-fullscreen me-1"></i> Full Screen
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm hover-opacity-100" data-bs-toggle="modal"
                    data-bs-target="#modalAttendanceData" title="Data Presensi">
                    <i class="bx bx-list-ul me-1"></i> Data Presensi ({{ $currentlyInGym->count() }})
                </button>
            </div>
        </div>

        <!-- Area Pusat: Logo, Tanggal, Jam, Nama Gym, Note, Slogan, dan Area Scan Barcode TOK -->
        <div class="text-center w-100 my-auto" style="max-width: 600px;">
            <!-- 1. Logo Resmi IFGS (Teks di atas logo telah dihapus sesuai permintaan) -->
            <div class="d-flex justify-content-center mb-2">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="Indo Fitness Gym Sport®" class="rounded-circle"
                    style="width: 96px; height: 96px; object-fit: cover; border: 3px solid #696cff;" />
            </div>

            <!-- 2. Tanggal di bawah logo -->
            <div class="fw-semibold text-secondary mb-1" id="liveDateDisplay" style="font-size: 1.15rem;">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>

            <!-- 3. Jam di bawah tanggal -->
            <div class="fw-bolder text-dark mb-2" id="liveTimeDisplay"
                style="font-size: 1.85rem; letter-spacing: 1px; font-variant-numeric: tabular-nums;">
                {{ now()->translatedFormat('H:i:s') }} WITA
            </div>

            <!-- 4. Indo Fitness Gym Sport -->
            <h1 class="fw-bolder text-heading mb-2" style="font-size: 2rem; letter-spacing: -0.5px;">
                Indo Fitness Gym Sport
            </h1>

            <!-- 5. Note yang user friendly dengan font jelas -->
            <div class="user-friendly-note mb-2 px-2">
                Silahkan scan barcode Anda terlebih dahulu untuk check in/checkout.
            </div>

            <!-- 6. Slogan KOMUNITAS PALING SPAN!!! -->
            <div class="mb-4">
                <div class="slogan-badge">
                    🔥 KOMUNITAS PALING SPAN!!! 🔥
                </div>
            </div>

            <!-- 7. Langsung Area Scan Barcode TOK (Kamera Otomatis Aktif Langsung, Tanpa Embel-embel Lain) -->
            <div class="scanner-container mx-auto mb-2 position-relative" id="cameraWrapper">
                <div id="reader"></div>
                <div id="laserLine" class="scan-laser"></div>

                <!-- Overlay Konfirmasi Hasil Scan (Jeda Kamera & Countdown) -->
                <div id="scanFeedbackOverlay" class="scan-feedback-overlay d-none">
                    <div class="feedback-content text-center p-3 w-100">
                        <div id="feedbackIconWrapper" class="feedback-icon mb-2">
                            <i id="feedbackIcon" class="bx bx-check-circle text-success"></i>
                        </div>
                        <h4 id="feedbackTitle" class="fw-bold mb-1 text-white">Check-in Berhasil!</h4>
                        <h5 id="feedbackMemberName" class="fw-bold text-warning mb-1 fs-5">Member</h5>
                        <div class="mb-2">
                            <span id="feedbackCode" class="badge bg-dark bg-opacity-75 text-light font-monospace border border-secondary">ID: -</span>
                        </div>
                        <div id="feedbackMessage" class="small text-white-50 mb-3 px-2">Selamat datang di Indo Fitness Gym Sport.</div>
                        <div>
                            <div class="cooldown-badge">
                                <i class="bx bx-time-five me-1 text-warning"></i>
                                <span>Siap scan berikutnya dalam <strong id="cooldownSeconds" class="text-warning">4</strong> detik</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Ganti Kamera jika di HP memiliki kamera depan & belakang -->
                <button type="button" id="btnSwitchCamera"
                    class="btn btn-sm btn-dark position-absolute bottom-0 end-0 m-2 opacity-75 hover-opacity-100 d-none"
                    style="z-index: 30; border-radius: 50rem;" title="Ganti Kamera Depan / Belakang">
                    <i class="bx bx-sync me-1"></i> Ganti Kamera
                </button>
            </div>

            <!-- Bagian di bawah area scan dihilangkan sesuai permintaan user -->
        </div>
    </div>

    <!-- Modal Data Riwayat Presensi & Member di Gym (Tersedia bagi Admin/Staff via Tombol Pojok Atas) -->
    <div class="modal fade" id="modalAttendanceData" tabindex="-1" aria-labelledby="modalAttendanceDataLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="modalAttendanceDataLabel">
                        <i class="bx bx-clipboard me-2 text-primary"></i> Data Presensi Hari Ini
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <ul class="nav nav-tabs nav-fill border-bottom" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active fw-semibold" data-bs-toggle="tab"
                                data-bs-target="#tabModalInGym" role="tab">
                                <i class="bx bx-user-check me-1 text-primary"></i> Sedang di Gym
                                <span class="badge bg-primary ms-1">{{ $currentlyInGym->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link fw-semibold" data-bs-toggle="tab"
                                data-bs-target="#tabModalHistory" role="tab">
                                <i class="bx bx-history me-1 text-secondary"></i> Riwayat Hari Ini
                                <span class="badge bg-secondary ms-1">{{ $todayAttendances->total() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- Tab Member Sedang di Gym -->
                        <div class="tab-pane fade show active" id="tabModalInGym" role="tabpanel">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover align-middle mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Member</th>
                                            <th>Check-in</th>
                                            <th>Durasi Saat Ini</th>
                                            <th>Paket Membership</th>
                                            <th>Sesi Personal Trainer</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($currentlyInGym as $active)
                                            @php
                                                $activeUser = $active->member?->user;
                                                $activeMembership = $active->member?->activeMembership();
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="avatar avatar-sm rounded-circle bg-label-primary me-2 d-flex align-items-center justify-content-center fw-bold">
                                                            {{ strtoupper(substr($activeUser?->name ?? 'MB', 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">
                                                                {{ $activeUser?->name ?? 'Member' }}</div>
                                                            <small
                                                                class="text-muted">{{ $active->member?->member_code ?? '-' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-success">
                                                        <i class="bx bx-time-five me-1"></i>
                                                        {{ $active->check_in_at?->format('H:i') }} WITA
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-primary">
                                                        {{ $active->check_in_at ? $active->check_in_at->diffForHumans(null, true) : '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($activeMembership)
                                                        <span
                                                            class="badge bg-label-primary">{{ $activeMembership->product?->name ?? 'Paket' }}</span>
                                                    @else
                                                        <span class="badge bg-label-danger">Tidak Aktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($active->trainerBooking)
                                                        <span class="badge bg-label-warning">
                                                            <i class="bx bx-run me-1"></i> Coach
                                                            {{ $active->trainerBooking->trainer?->user?->name ?? 'Trainer' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('attendances.checkout', $active) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                            onclick="return confirm('Catat Check-out untuk {{ $activeUser?->name }}?')">
                                                            <i class="bx bx-log-out me-1"></i> Check-out
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted small">Saat ini tidak ada member yang sedang
                                                        berada di gym.</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Riwayat Presensi Hari Ini -->
                        <div class="tab-pane fade" id="tabModalHistory" role="tabpanel">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover align-middle mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Kode</th>
                                            <th>Member</th>
                                            <th>Masuk</th>
                                            <th>Keluar</th>
                                            <th>Total Durasi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($todayAttendances as $log)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><span
                                                        class="badge bg-label-secondary">{{ $log->attendance_code }}</span>
                                                </td>
                                                <td>{{ $log->member?->user?->name ?? 'Member' }}</td>
                                                <td>{{ $log->check_in_at?->format('H:i') }} WITA</td>
                                                <td>{{ $log->check_out_at?->format('H:i') ? $log->check_out_at->format('H:i') . ' WITA' : '-' }}
                                                </td>
                                                <td>{{ $log->duration_formatted }}</td>
                                                <td><span
                                                        class="badge {{ $log->status_badge_class }}">{{ $log->status_label }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <div class="text-muted small">Belum ada riwayat kehadiran hari ini.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jam & Tanggal digital realtime
            function updateClockAndDate() {
                const now = new Date();
                const dateEl = document.getElementById('liveDateDisplay');
                const timeEl = document.getElementById('liveTimeDisplay');
                if (dateEl) {
                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
                    dateEl.textContent = now.toLocaleDateString('id-ID', options);
                }
                if (timeEl) {
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    timeEl.textContent = `${hours}:${minutes}:${seconds} WITA`;
                }
            }
            setInterval(updateClockAndDate, 1000);
            updateClockAndDate();

            // Toggle Browser Full Screen
            const btnFullscreen = document.getElementById('btnToggleFullscreen');
            if (btnFullscreen) {
                btnFullscreen.addEventListener('click', function() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.warn('Fullscreen request failed:', err);
                        });
                        btnFullscreen.innerHTML = '<i class="bx bx-exit-fullscreen me-1"></i> Layar Normal';
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                        btnFullscreen.innerHTML = '<i class="bx bx-fullscreen me-1"></i> Full Screen';
                    }
                });

                document.addEventListener('fullscreenchange', function() {
                    if (!document.fullscreenElement) {
                        btnFullscreen.innerHTML = '<i class="bx bx-fullscreen me-1"></i> Full Screen';
                    }
                });
            }

            // Web Audio API: Synthesizer Beep (Zero external asset dependencies)
            const audioCtx = new(window.AudioContext || window.webkitAudioContext)();

            function playBeep(type = 'success') {
                if (!audioCtx) return;
                try {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    if (type === 'check_in') {
                        // Nada ceria naik untuk check-in
                        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                        osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
                        gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.28);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.3);
                    } else if (type === 'check_out') {
                        // Nada santai ganda untuk check-out
                        osc.frequency.setValueAtTime(659.25, audioCtx.currentTime); // E5
                        osc.frequency.setValueAtTime(440, audioCtx.currentTime + 0.12); // A4
                        gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.37);
                    } else {
                        // Nada buzz rendah untuk peringatan/error
                        osc.type = 'sawtooth';
                        osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.38);
                    }
                } catch (e) {
                    // AudioContext autoplay protection safeguard
                }
            }

            // Scanner State Management
            let html5QrCode = null;
            let isScanning = false;
            let isProcessing = false;
            let isCooldown = false;
            let cooldownTimer = null;
            let cooldownInterval = null;
            let lastScannedCode = '';
            let lastScanTime = 0;
            let toastTimer = null;

            // Callback when QR detected by camera
            function onScanSuccess(decodedText) {
                if (isCooldown || isProcessing) {
                    return;
                }

                const now = Date.now();
                // Prevent duplicate rapid-fire triggers within 4 seconds for same code
                if (decodedText === lastScannedCode && (now - lastScanTime < 4000)) {
                    return;
                }
                lastScannedCode = decodedText;
                lastScanTime = now;

                processScanCode(decodedText, 'camera');
            }

            function onScanFailure(error) {
                // Background scan frame errors are ignored
            }

            // OTOMATIS AKTIFKAN KAMERA PERANGKAT LANGSUNG (CEPAT, HARDWARE-ACCELERATED & BEBAS PROMPT PONSEL)
            let availableCameras = [];
            let currentCameraIndex = 0;

            const scannerConfig = {
                fps: 25, // 25 frame per detik untuk pemindaian responsif instan
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    // Tangkap barcode di seluruh area kamera (85% layar) tanpa terbatasi kotak kecil
                    const edge = Math.min(viewfinderWidth, viewfinderHeight);
                    return {
                        width: Math.max(Math.floor(edge * 0.88), 240),
                        height: Math.max(Math.floor(edge * 0.88), 240)
                    };
                },
                aspectRatio: 1.0,
                videoConstraints: {
                    width: {
                        ideal: 1280
                    },
                    height: {
                        ideal: 720
                    }
                }
            };

            function autoStartCamera() {
                try {
                    // Gunakan hardware-accelerated BarcodeDetector bawaan browser (misal Chrome di Android/PC)
                    html5QrCode = new Html5Qrcode("reader", {
                        experimentalFeatures: {
                            useBarCodeDetectorIfSupported: true
                        },
                        verbose: false
                    });

                    Html5Qrcode.getCameras().then(cameras => {
                        if (cameras && cameras.length > 0) {
                            availableCameras = cameras;

                            // Jika di HP / tablet (memiliki lebih dari 1 kamera), prioritaskan kamera belakang utama
                            let selectedIndex = 0;
                            if (cameras.length > 1) {
                                const btnSwitch = document.getElementById('btnSwitchCamera');
                                if (btnSwitch) btnSwitch.classList.remove('d-none');

                                for (let i = 0; i < cameras.length; i++) {
                                    const label = cameras[i].label.toLowerCase();
                                    if (label.includes('back') || label.includes('rear') || label.includes(
                                            'belakang') || label.includes('environment')) {
                                        selectedIndex = i;
                                        break;
                                    }
                                }
                            }
                            currentCameraIndex = selectedIndex;

                            startCameraWithDevice(cameras[selectedIndex].id);
                        } else {
                            fallbackStartCamera();
                        }
                    }).catch(err => {
                        console.warn("Tidak dapat membaca daftar kamera:", err);
                        fallbackStartCamera();
                    });
                } catch (e) {
                    console.error("Inisialisasi kamera gagal:", e);
                }
            }

            function startCameraWithDevice(deviceId) {
                html5QrCode.start(
                    deviceId,
                    scannerConfig,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                }).catch(err => {
                    console.warn("Gagal membuka kamera dengan deviceId, mencoba fallback:", err);
                    fallbackStartCamera();
                });
            }

            function fallbackStartCamera() {
                // Fallback menggunakan webcam bawaan (facingMode: "user")
                html5QrCode.start({
                        facingMode: "user"
                    },
                    scannerConfig,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                }).catch(err => {
                    console.warn("Fallback kamera gagal:", err);
                });
            }

            // Tombol Switch Kamera (Depan / Belakang di HP)
            const btnSwitch = document.getElementById('btnSwitchCamera');
            if (btnSwitch) {
                btnSwitch.addEventListener('click', function() {
                    if (availableCameras.length > 1 && html5QrCode && isScanning) {
                        currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                        html5QrCode.stop().then(() => {
                            isScanning = false;
                            startCameraWithDevice(availableCameras[currentCameraIndex].id);
                        }).catch(e => console.warn(e));
                    }
                });
            }

            autoStartCamera();

            // Hardware USB Barcode Scanner Gun Global Keyboard Listener
            // Menangkap input tembak barcode fisik secara instan tanpa perlu kolom input manual di layar
            let barcodeBuffer = '';
            let barcodeLastKeyTime = 0;
            window.addEventListener('keydown', function(e) {
                // Jangan tangkap jika sedang mengetik di input form atau modal
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    return;
                }

                const currentTime = Date.now();
                if (currentTime - barcodeLastKeyTime > 150) {
                    barcodeBuffer = '';
                }
                barcodeLastKeyTime = currentTime;

                if (e.key === 'Enter') {
                    if (barcodeBuffer.length >= 3) {
                        processScanCode(barcodeBuffer.trim(), 'barcode_scanner');
                        barcodeBuffer = '';
                    }
                } else if (e.key.length === 1) {
                    barcodeBuffer += e.key;
                }
            });

            // Core Scan AJAX Dispatcher
            function processScanCode(code, method = 'camera') {
                if (isProcessing || isCooldown) {
                    return;
                }

                isProcessing = true;

                fetch('{{ route('attendances.scan') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            code: code,
                            mode: 'auto',
                            method: method
                        })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw data;
                        }
                        return data;
                    })
                    .then(data => {
                        handleScanSuccess(data);
                    })
                    .catch(error => {
                        handleScanError(error);
                    })
                    .finally(() => {
                        isProcessing = false;
                    });
            }

            // Tampilkan Notifikasi Toast & Feedback Overlay Saat Scan Berhasil
            function handleScanSuccess(data) {
                const action = data.action; // 'check_in', 'check_out', or 'already_checked_in'
                playBeep(action === 'already_checked_in' ? 'check_in' : action);

                const member = data.member || {};
                const attendance = data.attendance || {};
                const isCheckIn = action === 'check_in';
                const isCheckOut = action === 'check_out';
                const isAlready = action === 'already_checked_in';

                // Tampilkan Overlay Visual Langsung di Atas Scanner Viewport
                showFeedbackOverlay(action, member, attendance, data.trainer_notice || data.message);

                // Notifikasi Toast Mengambang di Atas
                let toastType = 'success';
                let toastIcon = 'bx-check-circle';
                let toastTitle = `Check-in Berhasil! (${attendance.check_in_at || ''} WITA)`;

                if (isCheckOut) {
                    toastType = 'info';
                    toastIcon = 'bx-log-out-circle';
                    toastTitle = `Check-out Berhasil! (${attendance.check_out_at || ''} WITA)`;
                } else if (isAlready) {
                    toastType = 'warning';
                    toastIcon = 'bx-time-five';
                    toastTitle = `Sudah Check-in (${attendance.check_in_at || ''} WITA)`;
                }

                showToast(
                    toastType,
                    toastIcon,
                    toastTitle,
                    `${member.name || 'Member'} &bull; ${data.trainer_notice || data.message || 'Presensi berhasil dicatat.'}`
                );

                // Berikan jeda kamera (Pause/Cooldown 4 detik) agar tidak ter-scan ganda
                startScannerCooldown(4, true);
            }

            // Tampilkan Notifikasi Toast Saat Scan Gagal
            function handleScanError(err) {
                playBeep('error');
                showToast(
                    'danger',
                    'bx-error-circle',
                    'Pemindaian Gagal!',
                    err.message || 'Kode barcode tidak dikenali atau terjadi kesalahan.'
                );

                // Jeda singkat 2 detik saat error agar tidak membunyikan beep bertubi-tubi
                startScannerCooldown(2, false);
            }

            function showFeedbackOverlay(action, member, attendance, message) {
                const overlay = document.getElementById('scanFeedbackOverlay');
                const icon = document.getElementById('feedbackIcon');
                const title = document.getElementById('feedbackTitle');
                const memberName = document.getElementById('feedbackMemberName');
                const codeBadge = document.getElementById('feedbackCode');
                const msgEl = document.getElementById('feedbackMessage');
                const laser = document.getElementById('laserLine');

                if (!overlay) return;

                if (laser) laser.style.display = 'none';

                if (action === 'check_in') {
                    icon.className = 'bx bx-check-circle text-success';
                    title.textContent = 'Check-in Berhasil!';
                    title.className = 'fw-bold mb-1 text-success';
                } else if (action === 'check_out') {
                    icon.className = 'bx bx-log-out-circle text-info';
                    title.textContent = 'Check-out Berhasil!';
                    title.className = 'fw-bold mb-1 text-info';
                } else { // already_checked_in
                    icon.className = 'bx bx-time-five text-warning';
                    title.textContent = 'Sudah Check-in!';
                    title.className = 'fw-bold mb-1 text-warning';
                }

                memberName.textContent = member.name || 'Member IFGS';
                codeBadge.textContent = member.member_code || ('ID: ' + (attendance.code || '-'));
                msgEl.textContent = message || '';

                overlay.classList.remove('d-none');
            }

            function startScannerCooldown(seconds = 4, showVisualCountdown = true) {
                isCooldown = true;
                if (cooldownTimer) clearTimeout(cooldownTimer);
                if (cooldownInterval) clearInterval(cooldownInterval);

                let remaining = Math.ceil(seconds);
                const secondsEl = document.getElementById('cooldownSeconds');
                if (secondsEl) secondsEl.textContent = remaining;

                if (showVisualCountdown) {
                    cooldownInterval = setInterval(() => {
                        remaining--;
                        if (secondsEl) secondsEl.textContent = Math.max(0, remaining);
                        if (remaining <= 0) {
                            clearInterval(cooldownInterval);
                        }
                    }, 1000);
                }

                cooldownTimer = setTimeout(() => {
                    const overlay = document.getElementById('scanFeedbackOverlay');
                    const laser = document.getElementById('laserLine');
                    if (overlay) overlay.classList.add('d-none');
                    if (laser) laser.style.display = '';

                    isCooldown = false;
                    lastScannedCode = ''; // Reset kode terakhir agar kamera siap scan berikutnya
                }, seconds * 1000);
            }

            function showToast(type, icon, title, message) {
                const toastContainer = document.getElementById('scanToastNotification');
                const toastBox = document.getElementById('toastAlertBox');
                const toastIcon = document.getElementById('toastIcon');
                const toastTitle = document.getElementById('toastTitle');
                const toastMessage = document.getElementById('toastMessage');

                if (toastTimer) clearTimeout(toastTimer);

                toastBox.className = `alert alert-${type} shadow-lg border-0 d-flex align-items-center mb-0`;
                toastIcon.className = `bx ${icon} fs-1 me-3 text-${type}`;
                toastTitle.textContent = title;
                toastMessage.innerHTML = message;

                toastContainer.classList.remove('d-none');

                toastTimer = setTimeout(() => {
                    toastContainer.classList.add('d-none');
                }, 3500);
            }
        });
    </script>
@endpush
