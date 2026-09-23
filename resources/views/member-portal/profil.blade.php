@extends('layouts.member')

@section('title', 'Profil Member')

@section('content')
    <!-- Header Halaman -->
    <div class="mb-3">
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
            Profil Saya
        </h5>
        <small class="text-muted" style="font-size: 0.78rem;">
            Informasi akun dan identitas keanggotaan gym Anda
        </small>
    </div>

    <!-- 1. Card Identitas Utama Member -->
    <div class="card p-3 mb-3 bg-white border text-center position-relative">
        <div class="mb-2">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                    class="rounded-circle shadow-sm object-fit-cover border border-2 border-primary" width="76" height="76">
            @else
                <div class="mx-auto rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border border-2 border-primary"
                    style="width: 76px; height: 76px; font-size: 1.6rem;">
                    {{ $user->initials }}
                </div>
            @endif
        </div>

        <h6 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">{{ $user->name }}</h6>
        <span class="text-muted small d-block mb-2" style="font-size: 0.78rem;">{{ $user->email }}</span>

        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <span class="badge bg-light text-primary border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bx bx-id-card me-1"></i> {{ $member?->member_code ?? $user->user_code ?? '-' }}
            </span>
            @if ($activeMembership)
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1" style="font-size: 0.72rem;">
                    <i class="bx bx-check-circle me-1"></i> Membership Aktif
                </span>
            @else
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.72rem;">
                    Membership Tidak Aktif
                </span>
            @endif
        </div>
    </div>

    <!-- 2. QR Code Absensi Gym -->
    <div class="card p-3 mb-3 bg-white border text-center">
        <h6 class="fw-bold text-dark mb-1 fs-6 d-flex align-items-center justify-content-center gap-2">
            <i class="bx bx-qr-scan text-primary"></i> QR Code Presensi Gym
        </h6>
        <p class="text-muted small mb-3" style="font-size: 0.72rem;">
            Tunjukkan kode QR ini ke scanner atau petugas kasir untuk absensi masuk gym
        </p>

        <div class="d-inline-block p-3 bg-white rounded-3 border shadow-sm mx-auto mb-2" style="max-width: 200px;">
            {!! $user->getQrCodeSvg(160) !!}
        </div>

        <div class="mb-3">
            <span class="badge bg-light text-secondary font-monospace border px-3 py-1" style="font-size: 0.78rem;">
                {{ $user->qr_code }}
            </span>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-2">
            @if (Route::has('user.qr-code.download'))
                <a href="{{ route('user.qr-code.download') }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bx bx-download me-1"></i> Simpan QR
                </a>
            @endif
            @if (Route::has('user.card'))
                <a href="{{ route('user.card', $user->slug) }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" style="font-size: 0.75rem;">
                    <i class="bx bx-printer me-1"></i> Cetak Kartu
                </a>
            @endif
        </div>
    </div>

    <!-- 3. Rincian Informasi Akun -->
    <div class="card mb-3">
        <div class="card-header py-2 px-3 bg-white border-bottom">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-info-circle text-primary"></i> Rincian Informasi
            </h6>
        </div>
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <span class="text-muted small" style="font-size: 0.78rem;">Nama Lengkap</span>
                <span class="fw-semibold text-dark small">{{ $user->name }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <span class="text-muted small" style="font-size: 0.78rem;">Nomor WhatsApp / HP</span>
                <span class="fw-semibold text-dark small">{{ $member?->phone ?? $user->phone ?? '-' }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <span class="text-muted small" style="font-size: 0.78rem;">Alamat Email</span>
                <span class="fw-semibold text-dark small">{{ $user->email }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <span class="text-muted small" style="font-size: 0.78rem;">Paket Aktif</span>
                <span class="fw-semibold text-primary small">{{ $activeMembership->product?->name ?? 'Belum Berlangganan' }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-2">
                <span class="text-muted small" style="font-size: 0.78rem;">Terdaftar Sejak</span>
                <span class="fw-semibold text-dark small">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- 4. Tombol Aksi Pengaturan Akun -->
    <div class="d-flex flex-column gap-2 mb-4">
        <button type="button" class="btn btn-primary btn-sm py-2 rounded-3 w-100" data-bs-toggle="modal" data-bs-target="#modalEditProfil">
            <i class="bx bx-edit me-1"></i> Edit Data Profil
        </button>

        <button type="button" class="btn btn-outline-secondary btn-sm py-2 rounded-3 w-100" data-bs-toggle="modal" data-bs-target="#modalGantiPassword">
            <i class="bx bx-lock-alt me-1"></i> Ganti Kata Sandi
        </button>

        <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun?');" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm py-2 rounded-3 w-100">
                <i class="bx bx-log-out me-1"></i> Keluar (Logout)
            </button>
        </form>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="modalEditProfil" tabindex="-1" aria-labelledby="modalEditProfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalEditProfilLabel">
                        <i class="bx bx-edit text-primary me-1"></i> Edit Data Profil
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('member.profil.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label for="prof_name" class="form-label small fw-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="prof_name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="prof_email" class="form-label small fw-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-sm" id="prof_email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="prof_phone" class="form-label small fw-bold text-dark">Nomor Handphone / WhatsApp</label>
                            <input type="tel" class="form-control form-control-sm" id="prof_phone" name="phone" value="{{ old('phone', $member?->phone ?? $user->phone) }}" placeholder="Contoh: 08123456789">
                        </div>

                        <div class="mb-2">
                            <label for="prof_avatar" class="form-label small fw-bold text-dark">Foto Profil (Opsional)</label>
                            <input type="file" class="form-control form-control-sm" id="prof_avatar" name="avatar" accept="image/*">
                            <small class="text-muted" style="font-size: 0.7rem;">Maksimal 2MB (JPG, PNG, WEBP).</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="bx bx-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ganti Kata Sandi -->
    <div class="modal fade" id="modalGantiPassword" tabindex="-1" aria-labelledby="modalGantiPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalGantiPasswordLabel">
                        <i class="bx bx-lock-alt text-primary me-1"></i> Ubah Kata Sandi
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('member.profil.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-bold text-dark">Password Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-sm" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label small fw-bold text-dark">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-sm" id="new_password" name="password" minlength="8" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Minimal 8 karakter.</small>
                        </div>

                        <div class="mb-2">
                            <label for="new_password_confirmation" class="form-label small fw-bold text-dark">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-sm" id="new_password_confirmation" name="password_confirmation" minlength="8" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="bx bx-check me-1"></i> Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
