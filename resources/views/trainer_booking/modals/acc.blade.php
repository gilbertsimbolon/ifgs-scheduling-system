<div class="modal fade" id="modalAccTrainerBooking" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-check-circle me-1 text-success"></i> Setujui Sesi Latihan & Kirim Konfirmasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formAccTrainerBooking">
                @csrf
                @method('PATCH')
                <input type="hidden" name="open_wa" id="accBookingOpenWa" value="0">

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Kolom Kiri: Rincian Jadwal & Data Diri Trainer -->
                        <div class="col-md-5 border-end">
                            <h6 class="fw-semibold text-muted text-uppercase small mb-2">Rincian Janji Latihan</h6>
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-2 px-3 small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Pelanggan</span>
                                        <strong class="text-dark" id="accBookingMemberName">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Kode Booking</span>
                                        <span class="fw-semibold text-dark" id="accBookingCode">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Hari & Tanggal</span>
                                        <span class="fw-semibold text-primary" id="accBookingSessionDate">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Jam Operasional</span>
                                        <span class="fw-semibold text-dark">08:00 - 20:00 WITA</span>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-semibold text-muted text-uppercase small mb-2">Data Diri Personal Trainer</h6>
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-2 px-3 small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Nama Trainer</span>
                                        <strong class="text-dark" id="accBookingTrainerName">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Spesialisasi</span>
                                        <span class="badge bg-label-info" id="accBookingTrainerSpec">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">No. HP/WA</span>
                                        <span class="fw-semibold text-dark" id="accBookingTrainerPhone">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Nomor WhatsApp Tujuan Pelanggan -->
                            <div>
                                <label for="accBookingMemberPhone" class="form-label fw-semibold small mb-1">
                                    <i class="bx bxl-whatsapp text-success me-1"></i> No. WhatsApp Pelanggan
                                </label>
                                <input type="text" name="member_phone" id="accBookingMemberPhone"
                                    class="form-control form-control-sm"
                                    placeholder="Contoh: 081234567890">
                                <div class="form-text small" style="font-size: 0.75rem;">
                                    Pesan konfirmasi kesediaan akan dikirimkan ke nomor ini.
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Template Pesan WhatsApp Kesediaan Trainer -->
                        <div class="col-md-7 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold text-muted text-uppercase small mb-0">
                                    <i class="bx bxl-whatsapp text-success me-1"></i> Pesan Pernyataan Bersedia
                                </h6>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btnCopyWaMessage">
                                    <i class="bx bx-copy me-1"></i> Salin Pesan
                                </button>
                            </div>

                            <div class="flex-grow-1 mb-2">
                                <textarea id="accBookingWaMessageText" name="wa_message" rows="11"
                                    class="form-control small bg-light text-dark font-monospace"
                                    style="font-size: 0.82rem; line-height: 1.45; resize: vertical;"
                                    placeholder="Memuat pesan konfirmasi kesediaan..."></textarea>
                            </div>

                            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-0" role="alert">
                                <i class="bx bx-check-shield me-2 fs-5"></i>
                                <div class="small" style="font-size: 0.78rem;">
                                    Klik <strong>Setujui & Kirim WhatsApp</strong> untuk menyetujui sekaligus membuka chat WhatsApp dengan pesan di atas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-success" id="btnSubmitAccOnly">
                            <i class="bx bx-check me-1"></i> Setujui Saja
                        </button>
                        <button type="button" class="btn btn-success" id="btnSubmitAccAndWa">
                            <i class="bx bxl-whatsapp me-1"></i> Setujui & Kirim WhatsApp
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
