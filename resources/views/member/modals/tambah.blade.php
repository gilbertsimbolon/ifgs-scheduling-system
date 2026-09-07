<div class="modal fade" id="modalTambahMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('member.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="create">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberName">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create') @error('name') is-invalid @enderror @endif"
                            id="tambahMemberName" name="name" placeholder="Masukkan nama lengkap member"
                            value="{{ old('_modal') === 'create' ? old('name') : '' }}" required />
                        @if (old('_modal') === 'create')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberEmail">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email"
                            class="form-control @if (old('_modal') === 'create') @error('email') is-invalid @enderror @endif"
                            id="tambahMemberEmail" name="email" placeholder="member@email.com"
                            value="{{ old('_modal') === 'create' ? old('email') : '' }}" required />
                        @if (old('_modal') === 'create')
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberPhone">Nomor HP</label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create') @error('phone') is-invalid @enderror @endif"
                            id="tambahMemberPhone" name="phone" placeholder="08xxxxxxxxxx"
                            value="{{ old('_modal') === 'create' ? old('phone') : '' }}" />
                        @if (old('_modal') === 'create')
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="tambahMemberPassword">Kata Sandi <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="tambahMemberPassword" name="password"
                                class="form-control @if (old('_modal') === 'create') @error('password') is-invalid @enderror @endif"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                required />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @if (old('_modal') === 'create')
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimal 8 karakter.</div>
                            @enderror
                        @else
                            <div class="form-text">Minimal 8 karakter.</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberStatus">Status Akun <span class="text-danger">*</span></label>
                        <select
                            class="form-select @if (old('_modal') === 'create') @error('status') is-invalid @enderror @endif"
                            id="tambahMemberStatus" name="status" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}"
                                    {{ (old('_modal') === 'create' ? old('status') === $status : $status === \App\Models\User::STATUS_ACTIVE) ? 'selected' : '' }}>
                                    {{ $status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Tidak Aktif' }}
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'create')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
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
