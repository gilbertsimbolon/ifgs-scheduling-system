@extends('layouts.app')

@section('title', 'Profil Saya - Indo Fitness Gym Sport')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Alert Notifikasi -->
        @if (session('profile_success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-circle fs-4 me-2"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('profile_success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('password_success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-shield fs-4 me-2"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('password_success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bx bx-error-circle fs-4 me-2 mt-1"></i>
                    <div>
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Card Header Ringkasan Profil Pengguna -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body py-3 px-4 text-center d-flex flex-column align-items-center justify-content-center">
                <!-- Foto Profil -->
                <div class="position-relative mb-1">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                            class="rounded-circle shadow-sm object-fit-cover border border-3 border-light"
                            style="width: 110px; height: 110px;" id="profileAvatarPreview" />
                    @else
                        <div class="avatar avatar-xl rounded-circle bg-label-primary d-flex align-items-center justify-content-center fw-bold fs-2 text-primary shadow-sm border border-3 border-light"
                            style="width: 110px; height: 110px;" id="profileAvatarPreview">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif

                    <label for="avatarInput"
                        class="btn btn-icon btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 shadow cursor-pointer"
                        title="Klik untuk memilih foto baru" style="cursor: pointer;">
                        <i class="bx bx-camera"></i>
                    </label>
                </div>

                <small class="text-secondary small mb-0">{{ $roleName }}</small>

                <!-- Nama -->
                <h4 class="fw-bold text-heading mt-1 mb-0">{{ $user->name }}</h4>

                <!-- Email -->
                <p class="text-muted small mb-0">{{ $user->email }}</p>

                @if ($isMember && $activeMembership)
                    <div class="mt-2 bg-lighter px-3 py-1.5 rounded-3 border small text-muted">
                        Paket Aktif: <strong
                            class="text-primary">{{ $activeMembership->product->name ?? 'Reguler' }}</strong>
                        &bull; Berlaku s/d <strong
                            class="text-dark">{{ $activeMembership->end_date->format('d M Y') }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Kolom Kiri: QR Code & Identitas Absensi -->
            <div class="col-12 col-lg-5 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title fw-bold mb-0 text-heading">
                            <i class="bx bx-qr-scan text-primary me-1"></i> QR Code Absensi
                        </h5>
                        <span class="badge bg-label-primary">ID Resmi</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <p class="text-muted small mb-3">
                            QR Code ini terhubung dengan akun Anda untuk absensi dan verifikasi kunjungan gym.
                        </p>

                        <!-- Box Tampilan SVG QR Code -->
                        <div class="d-inline-block p-3 bg-white rounded-3 border border-2 border-primary shadow-sm mb-3"
                            style="max-width: 240px;">
                            {!! $user->getQrCodeSvg(180) !!}
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-label-secondary font-monospace fs-6 px-3 py-2" title="Kode QR Unik">
                                {{ $user->qr_code }}
                            </span>
                        </div>

                        <!-- Tombol Aksi QR Code -->
                        <div class="d-flex flex-column gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalQrCodeProfile">
                                <i class="bx bx-fullscreen me-1"></i> Perbesar QR Code
                            </button>

                            <a href="{{ route('user.qr-code.download') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-download me-1"></i> Unduh File QR (SVG)
                            </a>

                            @if ($isMember)
                                <a href="{{ route('user.card', $user) }}" target="_blank"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-printer me-1"></i> Cetak Kartu Member Digital
                                </a>
                            @endif
                        </div>

                        <div class="alert alert-info py-2 px-3 mt-4 mb-0 text-start small">
                            <div class="d-flex align-items-start">
                                <i class="bx bx-info-circle fs-5 me-2 flex-shrink-0 mt-1"></i>
                                <div>
                                    Arahkan layar ponsel Anda ke mesin barcode scanner di meja resepsionis saat check-in
                                    kedatangan ke gym.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Data Diri, Aktivitas Kunjungan, & Ganti Password -->
            <div class="col-12 col-lg-7 col-xl-8">
                <!-- Form Data Diri & Upload Foto -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-heading">
                            <i class="bx bx-user me-1 text-primary"></i> Data Diri & Foto Profil
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <!-- Upload Foto Profil -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="avatarInput">
                                        Foto Profil (Avatar)
                                    </label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="file" id="avatarInput" name="avatar"
                                            class="form-control @error('avatar') is-invalid @enderror"
                                            accept="image/png, image/jpeg, image/jpg, image/webp"
                                            onchange="previewAvatar(this)" />
                                        @if ($user->avatar)
                                            <div class="form-check text-nowrap">
                                                <input class="form-check-input" type="checkbox" name="remove_avatar"
                                                    value="1" id="removeAvatarCheck">
                                                <label class="form-check-label text-danger small" for="removeAvatarCheck">
                                                    Hapus Foto
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">Format: JPG, JPEG, PNG, atau WEBP. Maksimal
                                        2MB.</small>
                                    @error('avatar')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nama Lengkap -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" for="name">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        <input type="text" id="name" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}" required
                                            placeholder="Nama lengkap Anda" />
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Alamat Email -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" for="email">
                                        Alamat Email <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="email" id="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $user->email) }}" required
                                            placeholder="email@contoh.com" />
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nomor Telepon / HP -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" for="phone">
                                        Nomor Handphone / WhatsApp
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="text" id="phone" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone', $user->phone) }}"
                                            placeholder="Contoh: 081234567890" />
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Peran Akun (Read-only) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Peran / Hak Akses</label>
                                    <input type="text" class="form-control bg-light" value="{{ $roleName }}"
                                        readonly disabled />
                                </div>

                                <!-- Terdaftar Sejak -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Terdaftar Sejak</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $user->created_at ? $user->created_at->translatedFormat('d F Y, H:i') : '-' }}"
                                        readonly disabled />
                                </div>

                                <!-- Terakhir Diperbarui -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Terakhir Diperbarui</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : '-' }}"
                                        readonly disabled />
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Simpan Data Diri
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Aktivitas Check-In & Check-Out Kunjungan -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title fw-bold mb-0 text-heading">
                            <i class="bx bx-history me-1 text-primary"></i> Aktivitas Kunjungan (Check-In dan Check-Out)
                        </h5>
                        <span class="badge bg-label-info">Log Kehadiran</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="timeline-activity">
                            @forelse ($visitActivities as $act)
                                <div class="d-flex align-items-start pb-3 mb-3 border-bottom">
                                    <div class="avatar avatar-sm me-3 flex-shrink-0">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $act['color'] }}">
                                            <i class="{{ $act['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                            <span class="badge {{ $act['badge_class'] }} fw-semibold">
                                                {{ $act['title'] }}
                                            </span>
                                            <small class="text-muted font-monospace">{{ $act['time'] }}</small>
                                        </div>
                                        <p class="mb-0 text-body small">
                                            {{ $act['description'] }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted small">
                                    <i class="bx bx-calendar-x fs-2 d-block mb-1"></i>
                                    Belum ada catatan aktivitas kunjungan.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Form Ganti Password -->
                <div class="card shadow-sm border-0">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-heading">
                            <i class="bx bx-lock-alt me-1 text-warning"></i> Ganti Password Akun
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <!-- Password Saat Ini -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="current_password">
                                        Password Saat Ini <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                        <input type="password" id="current_password" name="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            placeholder="Masukkan kata sandi saat ini" required
                                            autocomplete="current-password" />
                                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password Baru -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" for="password">
                                        Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <span class="input-group-text"><i class="bx bx-key"></i></span>
                                        <input type="password" id="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Minimal 8 karakter" required autocomplete="new-password" />
                                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Konfirmasi Password Baru -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold" for="password_confirmation">
                                        Konfirmasi Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <span class="input-group-text"><i class="bx bx-check-double"></i></span>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control" placeholder="Ulangi kata sandi baru" required
                                            autocomplete="new-password" />
                                        <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border py-2 px-3 mt-3 mb-0 small text-muted">
                                <i class="bx bx-shield me-1 text-warning"></i>
                                Pastikan password baru minimal 8 karakter dan merupakan kombinasi huruf serta angka yang
                                kuat.
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bx bx-key me-1"></i> Perbarui Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Perbesar QR Code Profil -->
    <div class="modal fade" id="modalQrCodeProfile" tabindex="-1" aria-labelledby="modalQrCodeProfileLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-center shadow-lg border-0">
                <div class="modal-header bg-primary text-white border-0 pb-3">
                    <div class="w-100 text-center">
                        <h5 class="modal-title text-white fw-bold" id="modalQrCodeProfileLabel">
                            <i class="bx bx-qr-scan me-1"></i> QR Code Identitas & Absensi
                        </h5>
                        <small class="text-white-50">Indo Fitness Gym Sport Tondano</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <h5 class="fw-bold text-dark mb-0">{{ $user->name }}</h5>
                        <p class="text-muted small mb-0">
                            @if ($user->member)
                                Kode Member: <code
                                    class="text-primary fw-bold fs-6">{{ $user->member->member_code }}</code>
                            @elseif ($user->trainer)
                                Kode Trainer: <code
                                    class="text-warning fw-bold fs-6">{{ $user->trainer->trainer_code }}</code>
                            @else
                                Peran: <span class="badge bg-label-primary">{{ $roleName }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Kontainer QR Code Kontras Tinggi -->
                    <div class="d-inline-block p-3 bg-white rounded-3 border border-2 border-dark shadow-sm my-2"
                        style="max-width: 260px;">
                        {!! $user->getQrCodeSvg(230) !!}
                    </div>

                    <div class="mt-2">
                        <span class="badge bg-label-secondary font-monospace">{{ $user->qr_code }}</span>
                    </div>

                    <div class="alert alert-info py-2 px-3 mt-3 mb-0 text-start small">
                        <i class="bx bx-info-circle me-1"></i>
                        Posisikan layar ke arah sensor barcode scanner di meja resepsionis gym saat check-in absensi
                        kunjungan.
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4 bg-white">
                    <a href="{{ route('user.qr-code.download') }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Unduh File QR (SVG)
                    </a>
                    @if ($isMember)
                        <a href="{{ route('user.card', $user) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="bx bx-printer me-1"></i> Cetak Kartu Member
                        </a>
                    @endif
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewEl = document.getElementById('profileAvatarPreview');
                    if (previewEl) {
                        if (previewEl.tagName === 'IMG') {
                            previewEl.src = e.target.result;
                        } else {
                            const img = document.createElement('img');
                            img.id = 'profileAvatarPreview';
                            img.src = e.target.result;
                            img.alt = 'Preview Foto Profil';
                            img.className = 'rounded-circle shadow-sm object-fit-cover border border-3 border-light';
                            img.style.width = '110px';
                            img.style.height = '110px';
                            previewEl.replaceWith(img);
                        }
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
