<div class="modal fade" id="modalDetailTrainerBooking" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-show me-1 text-primary"></i> Detail Janji Sesi Personal Trainer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Kolom Kiri: Profil Member & Kontak -->
                    <div class="col-md-5 border-end d-flex flex-column align-items-center">
                        <div class="text-center mb-3 w-100">
                            <div class="avatar avatar-xl mx-auto mb-2">
                                <span id="detailBookingAvatar"
                                    class="avatar-initial rounded-circle bg-label-primary fs-3 fw-bold">
                                    --
                                </span>
                            </div>
                            <h5 id="detailBookingMemberName" class="mb-1 fw-bold text-dark"></h5>
                            <p class="text-muted small mb-2"><span id="detailBookingMemberCode"
                                    class="fw-semibold text-secondary"></span></p>
                            <span id="detailBookingStatusBadge" class="badge"></span>
                        </div>

                        <!-- Profil Kontak Member -->
                        <div class="mb-3 text-center w-100">
                            <div class="d-flex align-items-center justify-content-center mb-1 text-muted small">
                                <i class="bx bx-envelope text-primary me-2"></i>
                                <span id="detailBookingEmail">-</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-phone text-success me-1"></i>
                                    <span id="detailBookingPhone">-</span>
                                </div>
                                <a id="detailBookingWaLink" href="#" target="_blank"
                                    class="btn btn-xs btn-outline-success">
                                    <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Info Trainer -->
                        <div class="card bg-light border-0 w-100 mt-2">
                            <div class="card-body p-3 text-center">
                                <div class="text-muted small mb-1">Personal Trainer Bertugas</div>
                                <div class="fw-bold text-dark fs-6" id="detailBookingTrainerName">-</div>
                                <div class="badge bg-label-info mt-1" id="detailBookingTrainerSpec">-</div>
                                <div class="small text-muted mt-1" id="detailBookingTrainerPhone">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian Janji & Status -->
                    <div class="col-md-7">
                        <h6 class="fw-semibold text-muted text-uppercase small mb-3">Rincian Janji Latihan</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-0 text-muted" style="width: 42%;">
                                        <i class="bx bx-hash me-1 text-primary"></i> Kode Booking
                                    </th>
                                    <td>: <span id="detailBookingCode" class="fw-semibold text-dark"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-calendar me-1 text-primary"></i> Hari & Tanggal
                                    </th>
                                    <td>: <strong id="detailBookingSessionDate" class="text-primary"></strong></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-time-five me-1 text-primary"></i> Jam Operasional
                                    </th>
                                    <td>: <span class="text-dark">08:00 - 20:00 WITA</span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted align-top">
                                        <i class="bx bx-note me-1 text-primary"></i> Catatan Pelanggan
                                    </th>
                                    <td>: <span id="detailBookingNotes" class="text-muted small"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-time me-1 text-primary"></i> Diajukan Pada
                                    </th>
                                    <td>: <span id="detailBookingCreatedAt" class="text-muted"></span></td>
                                </tr>
                                <tr id="detailBookingApprovedRow" class="d-none">
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-check-circle me-1 text-success"></i> Disetujui Pada
                                    </th>
                                    <td>: <span id="detailBookingApprovedAt" class="text-success small"></span></td>
                                </tr>
                                <tr id="detailBookingCompletedRow" class="d-none">
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-badge-check me-1 text-info"></i> Selesai Pada
                                    </th>
                                    <td>: <span id="detailBookingCompletedAt" class="text-info small"></span></td>
                                </tr>
                                <tr id="detailBookingRejectRow" class="d-none">
                                    <th class="ps-0 text-danger align-top">
                                        <i class="bx bx-x-circle me-1"></i> Alasan Ditolak
                                    </th>
                                    <td>: <span id="detailBookingRejectReason" class="text-danger small"></span></td>
                                </tr>
                            </tbody>
                        </table>

                        <div id="detailBookingPendingAlert" class="alert alert-warning mt-3 mb-0 d-none" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-time-five fs-4 me-2"></i>
                                <div class="small">
                                    Permohonan ini berstatus <strong>Belum Disetujui</strong>. Trainer dapat menyetujui (ACC) atau menolak janji latihan ini.
                                </div>
                            </div>
                        </div>

                        <div id="detailBookingScanNotice" class="alert alert-info mt-3 mb-0 d-none" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-scan fs-4 me-2"></i>
                                <div class="small">
                                    Permohonan telah disetujui. Status akan otomatis berubah menjadi <strong>Sedang Berjalan</strong> saat member melakukan scan kehadiran di gym.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <a id="detailBookingBtnWa" href="#" target="_blank" class="btn btn-success d-none">
                        <i class="bx bxl-whatsapp me-1"></i> Chat WhatsApp
                    </a>
                    <div id="detailBookingActionButtons" class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger" id="detailBookingBtnTolak">
                            <i class="bx bx-x me-1"></i> Menolak
                        </button>
                        <button type="button" class="btn btn-success" id="detailBookingBtnAcc">
                            <i class="bx bx-check me-1"></i> Menyetujui
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
