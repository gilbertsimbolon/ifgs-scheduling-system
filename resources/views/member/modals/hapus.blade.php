<div class="modal fade" id="modalHapusMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formHapusMember" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="mb-1">
                        Apakah Anda yakin ingin menghapus member <strong id="hapusMemberNama"></strong> (<span id="hapusMemberCode" class="font-monospace"></span>)?
                    </p>
                    <small class="text-danger">Tindakan ini akan menghapus akun login dan profil member terkait. Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Hapus Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
