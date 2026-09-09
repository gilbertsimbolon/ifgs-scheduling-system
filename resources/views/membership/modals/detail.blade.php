<div class="modal fade" id="modalDetailMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-show me-1 text-info"></i> Detail Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span id="detailMembershipAvatar" class="avatar-initial rounded-circle bg-label-primary fs-4">
                            --
                        </span>
                    </div>
                    <h5 id="detailMembershipMemberName" class="mb-0"></h5>
                    <p class="text-muted mb-0"><code id="detailMembershipMemberCode"></code></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 38%;">
                                    <i class="bx bx-package me-1 text-primary"></i> Paket Layanan
                                </th>
                                <td>: <strong id="detailMembershipProductName"></strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-time me-1 text-primary"></i> Durasi Paket
                                </th>
                                <td>: <span id="detailMembershipDuration"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-calendar me-1 text-primary"></i> Tanggal Mulai
                                </th>
                                <td>: <span id="detailMembershipStartDate"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-calendar-check me-1 text-primary"></i> Tanggal Berakhir
                                </th>
                                <td>: <span id="detailMembershipEndDate"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-wallet me-1 text-primary"></i> Biaya Transaksi
                                </th>
                                <td>: <strong class="text-success" id="detailMembershipPrice"></strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-badge-check me-1 text-primary"></i> Status
                                </th>
                                <td>: <span id="detailMembershipStatusBadge" class="badge"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-history me-1 text-primary"></i> Dibuat Pada
                                </th>
                                <td>: <span id="detailMembershipCreatedAt" class="text-muted"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>