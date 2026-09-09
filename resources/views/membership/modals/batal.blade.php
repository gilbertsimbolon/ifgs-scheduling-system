<div class="modal fade" id="modalBatalMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-x-circle me-1 text-warning"></i> Batalkan Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formBatalMembership">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="mb-3">
                            <i class="bx bx-error-circle text-warning display-4"></i>
                        </div>
                        <h6 class="fw-semibold">Apakah Anda yakin ingin membatalkan membership ini?</h6>
                        <p class="text-muted mb-0">
                            Status membership untuk member <strong id="batalMembershipMemberName"></strong>
                            (<span id="batalMembershipProduct"></span>) akan diubah menjadi <strong>Dibatalkan</strong>.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-x me-1"></i> Batalkan Membership
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>