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
            <div class="scanner-container mx-auto mb-2" id="cameraWrapper">
                <div id="reader"></div>
                <div id="laserLine" class="scan-laser"></div>
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
            let lastScannedCode = '';
            let lastScanTime = 0;
            let toastTimer = null;

            // Callback when QR detected by camera
            function onScanSuccess(decodedText) {
                const now = Date.now();
                // Prevent duplicate rapid-fire triggers within 3 seconds for same code
                if (decodedText === lastScannedCode && (now - lastScanTime < 3000)) {
                    return;
                }
                lastScannedCode = decodedText;
                lastScanTime = now;

                processScanCode(decodedText, 'camera');
            }

            function onScanFailure(error) {
                // Background scan frame errors are ignored
            }

            // OTOMATIS AKTIFKAN KAMERA PERANGKAT LANGSUNG (BEBAS PROMPT PONSEL)
            function autoStartCamera() {
                try {
                    html5QrCode = new Html5Qrcode("reader");
                    const config = {
                        fps: 15,
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                        aspectRatio: 1.0
                    };

                    // Dapatkan kamera fisik lokal yang terpasang di perangkat (laptop webcam / kamera bawaan)
                    // Menggunakan device ID spesifik atau facingMode "user" agar Windows/Chrome tidak memicu prompt 'Connect to Phone'
                    Html5Qrcode.getCameras().then(cameras => {
                        if (cameras && cameras.length > 0) {
                            const selectedCameraId = cameras[0].id;
                            html5QrCode.start(
                                selectedCameraId,
                                config,
                                onScanSuccess,
                                onScanFailure
                            ).then(() => {
                                isScanning = true;
                            }).catch(err => {
                                console.warn(
                                    "Gagal membuka kamera dengan deviceId, mencoba fallback:",
                                    err);
                                fallbackStartCamera(config);
                            });
                        } else {
                            fallbackStartCamera(config);
                        }
                    }).catch(err => {
                        console.warn("Tidak dapat membaca daftar kamera:", err);
                        fallbackStartCamera(config);
                    });
                } catch (e) {
                    console.error("Inisialisasi kamera gagal:", e);
                }
            }

            function fallbackStartCamera(config) {
                // Fallback menggunakan webcam bawaan (facingMode: "user")
                html5QrCode.start({
                        facingMode: "user"
                    },
                    config,
                    onScanSuccess,
                    onScanFailure
                ).then(() => {
                    isScanning = true;
                }).catch(err => {
                    console.warn("Fallback kamera gagal:", err);
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
                    });
            }

            // Tampilkan Notifikasi Toast Mengambang Saat Scan Berhasil
            function handleScanSuccess(data) {
                const action = data.action; // 'check_in' or 'check_out'
                playBeep(action);

                const member = data.member || {};
                const attendance = data.attendance || {};
                const isCheckIn = action === 'check_in';

                showToast(
                    isCheckIn ? 'success' : 'info',
                    isCheckIn ? 'bx-check-circle' : 'bx-log-out-circle',
                    isCheckIn ? `Check-in Berhasil! (${attendance.check_in_at || ''} WITA)` :
                    `Check-out Berhasil! (${attendance.check_out_at || ''} WITA)`,
                    `${member.name || 'Member'} &bull; ${data.trainer_notice || data.message || 'Presensi berhasil dicatat.'}`
                );
            }

            // Tampilkan Notifikasi Toast Mengambang Saat Scan Gagal
            function handleScanError(err) {
                playBeep('error');
                showToast(
                    'danger',
                    'bx-error-circle',
                    'Pemindaian Gagal!',
                    err.message || 'Kode barcode tidak dikenali atau terjadi kesalahan.'
                );
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
