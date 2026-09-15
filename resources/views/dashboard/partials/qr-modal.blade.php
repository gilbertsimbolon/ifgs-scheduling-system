<!-- Modal Perbesar QR Code Absensi (High-Contrast untuk Scanner) -->
<div class="modal fade" id="modalQrCodeMember" tabindex="-1" aria-labelledby="modalQrCodeMemberLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center shadow-lg border-0">
            <div class="modal-header bg-primary text-white border-0 pb-3">
                <div class="w-100 text-center">
                    <h5 class="modal-title text-white fw-bold d-inline-flex align-items-center justify-content-center" id="modalQrCodeMemberLabel">
                        <i class="bx bx-qr-scan me-2 fs-4"></i> QR Code Absensi Kunjungan
                    </h5>
                    <small class="text-white-50">Indo Fitness Gym Sport Tondano</small>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</h5>
                    <p class="text-muted small mb-0">ID Member: <code class="text-primary fw-bold fs-6">{{ $member->member_code ?? '-' }}</code></p>
                </div>

                <!-- Kontainer QR Code dengan Background Putih Bersih & Kontras Tinggi -->
                <div class="d-inline-block p-3 bg-white rounded-3 border border-2 border-dark shadow-sm my-2" style="max-width: 260px;">
                    {!! auth()->user()->getQrCodeSvg(230) !!}
                </div>

                <div class="mt-2">
                    <span class="badge bg-label-secondary font-monospace">{{ auth()->user()->qr_code }}</span>
                </div>

                <div class="alert alert-info py-2 px-3 mt-3 mb-0 text-start small">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-info-circle fs-5 me-2 flex-shrink-0"></i>
                        <div>
                            Arahkan layar ponsel ini ke barcode scanner absensi di meja resepsionis saat memasuki area gym.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4 bg-white">
                <a href="{{ route('user.qr-code.download') }}" class="btn btn-primary">
                    <i class="bx bx-download me-1"></i> Unduh File QR (SVG)
                </a>
                <a href="{{ route('user.card', auth()->user()) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bx bx-printer me-1"></i> Cetak Kartu Member
                </a>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

