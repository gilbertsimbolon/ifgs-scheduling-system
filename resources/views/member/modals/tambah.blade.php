<div class="modal fade" id="modalTambahMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('member.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="create_member">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create_member')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-primary d-flex align-items-center mb-3 py-2 px-3" role="alert">
                        <i class="bx bx-info-circle me-2 fs-5"></i>
                        <div class="small">
                            Nama member berasal dari akun Pengguna (User). Pilih pengguna terdaftar di bawah, atau buat akun pengguna baru terlebih dahulu jika belum terdaftar.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahUserId">Pilih Pengguna <span class="text-danger">*</span></label>
                        <select
                            class="form-select @if (old('_modal') === 'create_member') @error('user_id') is-invalid @enderror @endif"
                            id="tambahUserId" name="user_id" required>
                            <option value="">-- Pilih Pengguna Terdaftar --</option>
                            @foreach ($availableUsers as $availableUser)
                                <option value="{{ $availableUser->id }}"
                                    {{ old('_modal') === 'create_member' && old('user_id') == $availableUser->id ? 'selected' : '' }}>
                                    {{ $availableUser->name }} ({{ $availableUser->email }})
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'create_member')
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif

                        @if ($availableUsers->isEmpty())
                            <div class="form-text text-warning mt-1">
                                <i class="bx bx-info-circle me-1"></i> Tidak ada pengguna yang tersedia. Silakan buat akun pengguna baru.
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberPhone">Nomor HP</label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_member') @error('phone') is-invalid @enderror @endif"
                            id="tambahMemberPhone" name="phone" placeholder="Contoh: 08123456789"
                            value="{{ old('_modal') === 'create_member' ? old('phone') : '' }}" />
                        @if (old('_modal') === 'create_member')
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="border-top pt-3 mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Pengguna belum memiliki akun?</span>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal"
                            data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                            <i class="bx bx-user-plus me-1"></i> Buat Pengguna Baru
                        </button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
