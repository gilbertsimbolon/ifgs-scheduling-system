<div class="modal fade" id="modalDetailProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="bx bx-show text-primary me-1"></i> Detail Paket Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Nama Paket</label>
                        <h4 class="fw-bold text-dark mb-0" id="detailProductName">-</h4>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Status</label>
                        <div id="detailProductStatus">
                            <span class="badge bg-label-success">Aktif</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Total Pilihan Durasi</label>
                        <div>
                            <span class="badge bg-label-primary" id="detailProductDurationCount">0 Pilihan Durasi</span>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Deskripsi Fasilitas</label>
                        <p class="text-secondary mb-0" id="detailProductDesc" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">-</p>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <div>
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bx bx-time-five text-primary me-1"></i> Pilihan Durasi &amp; Harga
                    </h6>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="text-muted small fw-semibold text-uppercase" style="border-bottom: 2px solid var(--bs-border-color);">
                                    <th class="ps-1 py-2" style="width: 60%;">DURASI</th>
                                    <th class="pe-1 py-2 text-end" style="width: 40%;">HARGA</th>
                                </tr>
                            </thead>
                            <tbody id="detailDurationsTableBody">
                                <!-- Populated dynamically via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>