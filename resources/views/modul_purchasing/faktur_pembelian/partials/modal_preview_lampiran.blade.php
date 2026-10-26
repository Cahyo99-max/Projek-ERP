<!-- ===== MODAL PREVIEW LAMPIRAN / FOTO BUKTI FAKTUR ===== -->
<div class="modal fade" id="modalPreviewLampiranInvoice" tabindex="-1" aria-labelledby="modalPreviewLampiranInvoiceLabel" aria-hidden="true" style="z-index: 1085 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header inv-modal-header py-3 px-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(6px);">
                        <i class="bi bi-file-earmark-image fs-4 text-white"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="preview_lampiran_title">Bukti Dokumen / Foto Faktur</h6>
                        <small class="text-white-50" style="font-size: 0.75rem;">Pratinjau Berkas Lampiran Tanpa Buka Halaman Baru</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4 text-center bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="min-height: 380px;">
                <!-- Image Viewer -->
                <div id="preview_lampiran_img_wrapper" class="w-100 d-none text-center">
                    <img id="preview_lampiran_img" src="" alt="Bukti Faktur" class="img-fluid rounded-3 shadow border" style="max-height: 72vh; width: auto; max-width: 100%; object-fit: contain;">
                </div>

                <!-- PDF Viewer (iframe) -->
                <div id="preview_lampiran_pdf_wrapper" class="w-100 d-none">
                    <iframe id="preview_lampiran_iframe" src="" style="width: 100%; height: 72vh; border: none;" class="rounded-3 shadow border"></iframe>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top p-3 d-flex justify-content-between align-items-center">
                <a href="#" id="preview_lampiran_download_btn" target="_blank" download class="btn btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-download me-1"></i> Unduh Berkas
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
