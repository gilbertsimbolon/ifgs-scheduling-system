<div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditPengguna" action="{{ old('_action', '') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="_action" id="editActionInput" value="{{ old('_action', '') }}">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'edit')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="editName">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit') @error('name') is-invalid @enderror @endif"
                            id="editName" name="name" value="{{ old('_modal') === 'edit' ? old('name') : '' }}"
                            required />
                        @if (old('_modal') === 'edit')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editEmail">Alamat Email <span
                                class="text-danger">*</span></label>
                        <input type="email"
                            class="form-control @if (old('_modal') === 'edit') @error('email') is-invalid @enderror @endif"
                            id="editEmail" name="email" value="{{ old('_modal') === 'edit' ? old('email') : '' }}"
                            required />
                        @if (old('_modal') === 'edit')
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="editPassword">Kata Sandi</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="editPassword" name="password"
                                class="form-control @if (old('_modal') === 'edit') @error('password') is-invalid @enderror @endif"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @if (old('_modal') === 'edit')
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter
                                    jika diisi.</div>
                            @enderror
                        @else
                            <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter
                                jika diisi.</div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editRole">Peran <span class="text-danger">*</span></label>
                            <select
                                class="form-select @if (old('_modal') === 'edit') @error('role') is-invalid @enderror @endif"
                                id="editRole" name="role" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}"
                                        {{ old('_modal') === 'edit' && old('role') === $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @if (old('_modal') === 'edit')
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editStatus">Status Akun <span
                                    class="text-danger">*</span></label>
                            <select
                                class="form-select @if (old('_modal') === 'edit') @error('status') is-invalid @enderror @endif"
                                id="editStatus" name="status" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}"
                                        {{ old('_modal') === 'edit' && old('status') === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                        {{ $status === \App\Models\User::STATUS_ACTIVE ? 'Aktif' : 'Tidak Aktif' }}
                                    </option>
                                @endforeach
                            </select>
                            @if (old('_modal') === 'edit')
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
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
