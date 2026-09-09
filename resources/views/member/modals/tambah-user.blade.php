<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Pengguna & Member Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('member.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="create_user">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create_user')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info d-flex align-items-center mb-3 py-2 px-3" role="alert">
                        <i class="bx bx-user-check me-2 fs-5"></i>
                        <div class="small">
                            Sistem akan membuat akun Pengguna (User) terlebih dahulu dengan peran <strong>Member</strong>, kemudian secara otomatis mendaftarkannya ke dalam data Member.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahUserName">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_user') @error('name') is-invalid @enderror @endif"
                            id="tambahUserName" name="name" placeholder="Masukkan nama lengkap pengguna"
                            value="{{ old('_modal') === 'create_user' ? old('name') : '' }}" required />
                        @if (old('_modal') === 'create_user')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahUserEmail">Alamat Email Pengguna <span class="text-danger">*</span></label>
                        <input type="email"
                            class="form-control @if (old('_modal') === 'create_user') @error('email') is-invalid @enderror @endif"
                            id="tambahUserEmail" name="email" placeholder="nama@email.com"
                            value="{{ old('_modal') === 'create_user' ? old('email') : '' }}" required />
                        @if (old('_modal') === 'create_user')
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahUserPhone">Nomor HP Member</label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_user') @error('phone') is-invalid @enderror @endif"
                            id="tambahUserPhone" name="phone" placeholder="Contoh: 08123456789"
                            value="{{ old('_modal') === 'create_user' ? old('phone') : '' }}" />
                        @if (old('_modal') === 'create_user')
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="tambahUserPassword">Kata Sandi Akun <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="tambahUserPassword" name="password"
                                class="form-control @if (old('_modal') === 'create_user') @error('password') is-invalid @enderror @endif"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                required />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @if (old('_modal') === 'create_user')
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimal 8 karakter untuk login pengguna.</div>
                            @enderror
                        @else
                            <div class="form-text">Minimal 8 karakter untuk login pengguna.</div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tambahUserStatus">Status Akun <span class="text-danger">*</span></label>
                            <select
                                class="form-select @if (old('_modal') === 'create_user') @error('status') is-invalid @enderror @endif"
                                id="tambahUserStatus" name="status" required>
                                <option value="{{ \App\Models\User::STATUS_ACTIVE }}"
                                    {{ old('_modal') === 'create_user' && old('status') === \App\Models\User::STATUS_ACTIVE ? 'selected' : (!old('_modal') ? 'selected' : '') }}>
                                    Aktif
                                </option>
                                <option value="{{ \App\Models\User::STATUS_INACTIVE }}"
                                    {{ old('_modal') === 'create_user' && old('status') === \App\Models\User::STATUS_INACTIVE ? 'selected' : '' }}>
                                    Tidak Aktif
                                </option>
                            </select>
                            @if (old('_modal') === 'create_user')
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Pengguna & Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
