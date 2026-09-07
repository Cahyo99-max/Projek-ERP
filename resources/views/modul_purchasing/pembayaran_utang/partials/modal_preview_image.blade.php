<!-- Modal Preview / Lightbox Foto Bukti Pembayaran -->
<div class="modal fade" id="modalPreviewImage" tabindex="-1" aria-labelledby="modalPreviewImageLabel" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: #0b1120; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1) !important;">
            
            <!-- Modal Header with Glassmorphism -->
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-3.5 py-3 d-flex align-items-center justify-content-between" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden me-2">
                    <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-image-fill fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h6 class="modal-title mb-0 fw-bold text-white text-truncate" id="previewImageTitle">Bukti Pembayaran</h6>
                        <div class="text-white-50 small font-monospace text-truncate" id="previewImageSubtitle" style="font-size: 0.75rem;">Lampiran Transaksi</div>
                    </div>
                </div>

                <!-- Action Controls in Header -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a id="btnDownloadPreviewImage" href="#" download class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 fw-semibold d-none d-sm-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                        <i class="bi bi-download"></i>
                        <span>Unduh</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle text-white border-0" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1);">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body: High Resolution Viewer -->
            <div class="modal-body p-2 p-md-3 text-center position-relative d-flex align-items-center justify-content-center" style="background: radial-gradient(circle at center, #1e293b 0%, #090d16 100%); min-height: 350px; max-height: 78vh; overflow: auto;">
                <div id="previewImageLoadingSpinner" class="position-absolute top-50 start-50 translate-middle text-primary d-none">
                    <div class="spinner-border" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat gambar...</span>
                    </div>
                </div>
                <img id="previewImageSrc" src="" alt="Bukti Pembayaran" class="img-fluid rounded-3 shadow-lg" style="max-height: 72vh; max-width: 100%; object-fit: contain; transition: transform 0.25s ease;">
            </div>

            <!-- Modal Footer (Mobile Actions & Zoom Info) -->
            <div class="modal-footer border-top border-secondary border-opacity-25 px-3 py-2.5 d-flex align-items-center justify-content-between" style="background: rgba(15, 23, 42, 0.95);">
                <div class="small text-white-50 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span style="font-size: 0.75rem;">Format WebP/JPEG Optimal</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="btnDownloadPreviewImageMobile" href="#" download class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-semibold d-inline-flex d-sm-none align-items-center gap-1" style="font-size: 0.78rem;">
                        <i class="bi bi-download"></i> Unduh
                    </a>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-1.5 fw-semibold shadow-sm" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
