<div class="modal fade" id="modalEditMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit-alt me-1 text-warning"></i> Edit Data Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditMembership">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit_membership">
                <input type="hidden" name="_action" id="editMembershipActionInput" value="">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'edit_membership')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Member info (Readonly) -->
                    <div class="mb-3">
                        <label class="form-label">Member</label>
                        <input type="text" class="form-control bg-light" id="editMemberDisplayName" readonly />
                    </div>

                    <!-- Paket Layanan -->
                    <div class="mb-3">
                        <label class="form-label" for="editProductId">
                            Paket Layanan <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_membership') @error('product_id') is-invalid @enderror @endif"
                            id="editProductId" name="product_id" required>
                            @foreach ($allProducts as $productItem)
                                <option value="{{ $productItem->id }}"
                                    data-price="{{ $productItem->price }}"
                                    data-duration-value="{{ $productItem->duration_value }}"
                                    data-duration-unit="{{ $productItem->duration_unit }}">
                                    {{ $productItem->name }} — {{ $productItem->formatted_price }} ({{ $productItem->duration_formatted }})
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'edit_membership')
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Tanggal Mulai & Berakhir -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editStartDate">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                class="form-control @if (old('_modal') === 'edit_membership') @error('start_date') is-invalid @enderror @endif"
                                id="editStartDate" name="start_date" required />
                            @if (old('_modal') === 'edit_membership')
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editEndDate">
                                Tanggal Berakhir <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                class="form-control @if (old('_modal') === 'edit_membership') @error('end_date') is-invalid @enderror @endif"
                                id="editEndDate" name="end_date" required />
                            @if (old('_modal') === 'edit_membership')
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Biaya -->
                    <div class="mb-3">
                        <label class="form-label" for="editPrice">
                            Biaya (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1000" min="0"
                                class="form-control @if (old('_modal') === 'edit_membership') @error('price') is-invalid @enderror @endif"
                                id="editPrice" name="price" required />
                        </div>
                        @if (old('_modal') === 'edit_membership')
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodId">
                            Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_membership') @error('payment_method_id') is-invalid @enderror @endif"
                            id="editPaymentMethodId" name="payment_method_id" required>
                            @foreach ($allPaymentMethods as $pm)
                                <option value="{{ $pm->id }}"
                                    {{ old('_modal') === 'edit_membership' && old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                    {{ $pm->name }} {{ $pm->status !== \App\Models\PaymentMethod::STATUS_ACTIVE ? '(Non-Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'edit_membership')
                            @error('payment_method_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label" for="editStatus">
                            Status Membership <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_membership') @error('status') is-invalid @enderror @endif"
                            id="editStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="expired">Kadaluarsa</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        @if (old('_modal') === 'edit_membership')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
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