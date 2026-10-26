<!-- Modal Modern Preview Bukti Transfer / Lampiran -->
<div class="modal fade" id="modalPreviewBuktiTransfer" tabindex="-1" aria-labelledby="modalPreviewBuktiTransferLabel" aria-hidden="true" style="z-index: 1075;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="bi bi-file-earmark-image fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalPreviewBuktiTransferLabel">Bukti Transfer / Lampiran</h6>
                        <small class="text-body-secondary font-monospace" style="font-size: 0.76rem;">
                            No. Pembayaran: <span id="modal_preview_nomor" class="fw-semibold text-primary font-monospace">-</span>
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btn_modal_preview_external" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold shadow-xs" title="Buka di Tab Baru">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Tab Baru
                    </a>
                    <a href="#" id="btn_modal_preview_download" download class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs" title="Unduh File">
                        <i class="bi bi-download me-1"></i> Unduh
                    </a>
                    <button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-circle" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4 text-center bg-body-secondary bg-opacity-50 position-relative" style="min-height: 380px;">
                <!-- Loading Spinner -->
                <div id="preview_lampiran_spinner" class="d-none position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat berkas...</span>
                    </div>
                    <div class="small text-body-secondary mt-2 fw-semibold">Memuat berkas bukti transfer...</div>
                </div>

                <!-- Container Image Preview -->
                <div id="preview_lampiran_img_container" class="d-flex align-items-center justify-content-center h-100">
                    <img id="preview_lampiran_img" src="" alt="Bukti Transfer" class="img-fluid rounded-3 shadow border border-body-tertiary transition-transform" style="max-height: 72vh; max-width: 100%; object-fit: contain;">
                </div>

                <!-- Container PDF Preview (iframe) -->
                <div id="preview_lampiran_pdf_container" class="d-none w-100 h-100">
                    <iframe id="preview_lampiran_pdf" src="" class="w-100 rounded-3 border border-body-tertiary shadow-sm" style="height: 72vh; border: none;"></iframe>
                </div>

                <!-- Fallback / Empty -->
                <div id="preview_lampiran_empty" class="d-none text-center py-5">
                    <i class="bi bi-file-earmark-x fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                    <h6 class="fw-bold text-body-emphasis mb-1">Berkas Tidak Ditemukan</h6>
                    <p class="small text-body-secondary mb-0">Lampiran bukti transfer belum diunggah atau tidak tersedia di server.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top px-4 py-2.5 d-flex justify-content-between align-items-center">
                <span class="small text-body-secondary font-monospace" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-check text-success me-1"></i>Berkas terenkripsi & tersimpan aman
                </span>
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
