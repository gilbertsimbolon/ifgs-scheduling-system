<div class="modal fade" id="modalDetailTransaction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-receipt me-1 text-primary"></i> Detail Transaksi & Validasi Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Kolom Kiri: Profil Member & Bukti Transfer -->
                    <div class="col-md-5 border-end d-flex flex-column align-items-center">
                        <div class="text-center mb-3 w-100">
                            <div class="avatar avatar-xl mx-auto mb-2">
                                <span id="detailTxAvatar"
                                    class="avatar-initial rounded-circle bg-label-primary fs-3 fw-bold">
                                    --
                                </span>
                            </div>
                            <h5 id="detailTxMemberName" class="mb-1 fw-bold text-dark"></h5>
                            <p class="text-muted small mb-2"><span id="detailTxMemberCode"
                                    class="fw-semibold text-secondary"></span></p>
                            <span id="detailTxStatusBadge" class="badge"></span>
                        </div>

                        <!-- Profil Kontak (Tanpa latar belakang abu-abu) -->
                        <div class="mb-3 text-center w-100">
                            <div class="d-flex align-items-center justify-content-center mb-1 text-muted small">
                                <i class="bx bx-envelope text-primary me-2"></i>
                                <span id="detailTxEmail"></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center gap-2 text-muted small">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-phone text-success me-1"></i>
                                    <span id="detailTxPhone"></span>
                                </div>
                                <a id="detailTxWaLink" href="#" target="_blank"
                                    class="btn btn-xs btn-outline-success">
                                    <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Bukti Transfer (Hanya ditampilkan untuk pembayaran non-tunai/transfer) -->
                        <div id="detailTxProofSection" class="w-100 text-center">
                            <label class="form-label fw-semibold mb-2 text-center d-block">
                                <i class="bx bx-image text-primary me-1"></i> Bukti Transfer (SS)
                            </label>
                            <div id="detailTxProofContainer"
                                class="p-3 border rounded bg-white position-relative d-flex flex-column align-items-center justify-content-center text-center mx-auto"
                                style="min-height: 170px;">
                                <img id="detailTxProofImg" src="" alt="Bukti Transfer"
                                    class="img-fluid rounded mx-auto d-block"
                                    style="max-height: 200px; object-fit: contain; cursor: pointer;">
                                <div id="detailTxNoProof"
                                    class="text-muted small py-3 d-flex flex-column align-items-center justify-content-center text-center w-100 d-none">
                                    <i class="bx bx-image-alt text-secondary mb-2"
                                        style="font-size: 2.8rem; line-height: 1;"></i>
                                    <span class="d-block text-center">Tidak ada bukti transfer diunggah</span>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <a id="detailTxProofZoomBtn" href="javascript:void(0);"
                                    class="btn btn-xs btn-outline-primary d-inline-flex align-items-center">
                                    <i class="bx bx-zoom-in me-1"></i> Lihat Gambar Penuh
                                </a>
                            </div>
                        </div>

                        <!-- Note Pembayaran Tunai / Cash (Tidak butuh bukti transfer) -->
                        <div id="detailTxCashNote"
                            class="p-4 border rounded text-center mb-0 d-flex flex-column align-items-center justify-content-center d-none mx-auto w-100"
                            style="min-height: 150px;">
                            <i class="bx bx-money text-success mb-2" style="font-size: 2.8rem; line-height: 1;"></i>
                            <span class="fw-semibold text-dark d-block mb-1">Pembayaran Tunai</span>
                            <small class="text-muted text-center d-block">Transaksi ini dibayar langsung secara tunai di
                                kasir dan tidak memerlukan bukti transfer.</small>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian Transaksi & Membership -->
                    <div class="col-md-7">
                        <h6 class="fw-semibold text-muted text-uppercase small mb-3">Rincian Pembayaran & Paket</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-0 text-muted" style="width: 42%;">
                                        <i class="bx bx-hash me-1 text-primary"></i> No. Invoice
                                    </th>
                                    <td>: <span id="detailTxInvoice" class="fw-semibold text-dark"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-package me-1 text-primary"></i> Paket Layanan
                                    </th>
                                    <td>: <strong id="detailTxProductName" class="text-dark"></strong></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-time me-1 text-primary"></i> Durasi Paket
                                    </th>
                                    <td>: <span id="detailTxDuration" class="text-dark"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-wallet me-1 text-primary"></i> Total Biaya
                                    </th>
                                    <td>: <strong class="text-success fs-6" id="detailTxPrice"></strong></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-credit-card me-1 text-primary"></i> Metode Bayar
                                    </th>
                                    <td>: <span id="detailTxPaymentMethod" class="fw-semibold text-dark"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-calendar me-1 text-primary"></i> Tgl Pesanan
                                    </th>
                                    <td>: <span id="detailTxCreatedAt" class="text-muted"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-calendar-event me-1 text-primary"></i> Periode Aktif
                                    </th>
                                    <td>: <span id="detailTxPeriod" class="small text-dark"></span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted">
                                        <i class="bx bx-user-check me-1 text-primary"></i> Petugas Kasir
                                    </th>
                                    <td>: <span id="detailTxCashier">-</span></td>
                                </tr>
                                <tr id="detailTxRejectRow" class="d-none">
                                    <th class="ps-0 text-danger align-top">
                                        <i class="bx bx-x-circle me-1"></i> Alasan Ditolak
                                    </th>
                                    <td class="text-danger">: <span id="detailTxRejectReason"></span></td>
                                </tr>
                            </tbody>
                        </table>

                        <div id="detailTxPendingAlert" class="alert alert-warning mt-3 mb-0 d-none" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-time-five fs-4 me-2"></i>
                                <div class="small">
                                    Pesanan ini berstatus <strong>Menunggu Validasi</strong>. Mohon periksa pembayaran
                                    sebelum melakukan persetujuan (ACC) atau penolakan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
                <div id="detailTxActionButtons" class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger" id="detailTxBtnTolak">
                        <i class="bx bx-x me-1"></i> Tolak Transaksi
                    </button>
                    <button type="button" class="btn btn-success" id="detailTxBtnAcc">
                        <i class="bx bx-check me-1"></i> Setujui (ACC)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
