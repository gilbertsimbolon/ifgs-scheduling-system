<div class="modal fade" id="modalEditMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditMember" action="{{ old('_action', '') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="_action" id="editMemberActionInput" value="{{ old('_action', '') }}">

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
                        <label class="form-label" for="editMemberCode">Kode Member</label>
                        <input type="text" class="form-control font-monospace bg-light" id="editMemberCode" readonly />
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editMemberName">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit') @error('name') is-invalid @enderror @endif"
                            id="editMemberName" name="name" value="{{ old('_modal') === 'edit' ? old('name') : '' }}"
                            required />
                        @if (old('_modal') === 'edit')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editMemberEmail">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email"
                            class="form-control @if (old('_modal') === 'edit') @error('email') is-invalid @enderror @endif"
                            id="editMemberEmail" name="email" value="{{ old('_modal') === 'edit' ? old('email') : '' }}"
                            required />
                        @if (old('_modal') === 'edit')
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editMemberPhone">Nomor HP</label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit') @error('phone') is-invalid @enderror @endif"
                            id="editMemberPhone" name="phone" value="{{ old('_modal') === 'edit' ? old('phone') : '' }}" />
                        @if (old('_modal') === 'edit')
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="editMemberPassword">Kata Sandi</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="editMemberPassword" name="password"
                                class="form-control @if (old('_modal') === 'edit') @error('password') is-invalid @enderror @endif"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        @if (old('_modal') === 'edit')
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter jika diisi.</div>
                            @enderror
                        @else
                            <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi. Minimal 8 karakter jika diisi.</div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editMemberStatus">Status Akun <span class="text-danger">*</span></label>
                            <select
                                class="form-select @if (old('_modal') === 'edit') @error('status') is-invalid @enderror @endif"
                                id="editMemberStatus" name="status" required>
                                <option value="{{ \App\Models\User::STATUS_ACTIVE }}">Aktif</option>
                                <option value="{{ \App\Models\User::STATUS_INACTIVE }}">Tidak Aktif</option>
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
