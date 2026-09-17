<div class="modal fade" id="modalTolakTrainerBooking" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-x-circle me-1 text-danger"></i> Tolak Permohonan Sesi & Beritahu via WA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formTolakTrainerBooking">
                @csrf
                @method('PATCH')
                <input type="hidden" name="open_wa" id="tolakBookingOpenWa" value="0">

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Kolom Kiri: Rincian & Input Alasan -->
                        <div class="col-md-5 border-end">
                            <h6 class="fw-semibold text-muted text-uppercase small mb-2">Rincian Permohonan</h6>
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-2 px-3 small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Pelanggan</span>
                                        <strong class="text-dark" id="tolakBookingMemberName">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Kode Booking</span>
                                        <span class="fw-semibold text-dark" id="tolakBookingCode">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Hari & Tanggal</span>
                                        <span class="fw-semibold text-dark" id="tolakBookingSessionDate">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Trainer</span>
                                        <span class="fw-semibold text-dark" id="tolakBookingTrainerName">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="tolakBookingMemberPhone" class="form-label fw-semibold small mb-1">
                                    <i class="bx bxl-whatsapp text-success me-1"></i> No. WhatsApp Pelanggan
                                </label>
                                <input type="text" name="member_phone" id="tolakBookingMemberPhone"
                                    class="form-control form-control-sm"
                                    placeholder="Contoh: 081234567890">
                            </div>

                            <div class="mb-0">
                                <label for="tolakBookingReason" class="form-label required fw-semibold small mb-1">
                                    Alasan Penolakan / Berhalangan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control form-control-sm" id="tolakBookingReason" name="reason" rows="3" required
                                    placeholder="Contoh: Ada jadwal khusus di luar kota, atau kuota pendampingan hari tersebut penuh."></textarea>
                                <div class="form-text small" style="font-size: 0.75rem;">
                                    Alasan ini akan otomatis dimasukkan ke dalam template pesan WhatsApp.
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Preview Pesan WA Penolakan -->
                        <div class="col-md-7 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold text-muted text-uppercase small mb-0">
                                    <i class="bx bxl-whatsapp text-danger me-1"></i> Pesan Pemberitahuan WhatsApp
                                </h6>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnCopyTolakWaMessage">
                                    <i class="bx bx-copy me-1"></i> Salin Pesan
                                </button>
                            </div>

                            <div class="flex-grow-1 mb-2">
                                <textarea id="tolakBookingWaMessageText" name="wa_message" rows="11"
                                    class="form-control small bg-light text-dark font-monospace"
                                    style="font-size: 0.82rem; line-height: 1.45; resize: vertical;"
                                    placeholder="Memuat pesan penolakan WhatsApp..."></textarea>
                            </div>

                            <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-0" role="alert">
                                <i class="bx bx-info-circle me-2 fs-5"></i>
                                <div class="small" style="font-size: 0.78rem;">
                                    Klik <strong>Tolak & Kirim WhatsApp</strong> untuk menolak permohonan dan langsung membuka chat WhatsApp dengan pesan di atas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-danger" id="btnSubmitTolakOnly">
                            <i class="bx bx-x me-1"></i> Tolak Saja
                        </button>
                        <button type="button" class="btn btn-danger" id="btnSubmitTolakAndWa">
                            <i class="bx bxl-whatsapp me-1"></i> Tolak & Kirim WhatsApp
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
