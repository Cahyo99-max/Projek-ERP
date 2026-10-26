<!-- Modal Detail Invoice Penjualan -->
<div class="modal fade" id="modalDetailInvoice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable faktur-modal-dialog" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 bg-body" id="modalDetailInvoiceContent">
            <div class="p-5 text-center text-body-secondary">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0 font-monospace">Memuat detail faktur penjualan...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Dokumen Pendukung -->
<div class="modal fade" id="modalPreviewDokumen" tabindex="-1" aria-labelledby="modalPreviewDokumenLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <div class="modal-header border-bottom py-3 px-4 bg-body-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm bg-primary">
                        <i class="bi bi-file-earmark-text fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="preview_doc_title">Preview Dokumen</h6>
                        <small class="text-body-secondary font-monospace" id="preview_doc_subtitle">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btn_download_from_preview" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm btn-download-document no-loader">
                        <i class="bi bi-download me-1"></i> Download File
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 text-center bg-dark-subtle d-flex align-items-center justify-content-center" id="preview_doc_body" style="min-height: 480px; max-height: 80vh; overflow: auto;">
                <!-- Injected preview content -->
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-body-secondary d-flex justify-content-between align-items-center">
                <span class="text-body-secondary small font-monospace" id="preview_doc_info"></span>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup Preview</button>
            </div>
        </div>
    </div>
</div>

