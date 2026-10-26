<!-- MODAL PREVIEW BUKTI / STRUK TRANSAKSI -->
<div class="modal fade" id="modalPreviewBukti" tabindex="-1" aria-labelledby="modalPreviewBuktiLabel" aria-hidden="true" style="z-index: 1075;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content pc-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header pc-modal-header px-4 py-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold pc-text-title-adaptive mb-0" id="modalPreviewBuktiLabel">Lampiran Bukti Transaksi</h6>
                        <div class="d-flex align-items-center gap-2 small mt-0.5">
                            <span class="font-monospace text-info fw-semibold" id="previewBuktiNomor">PC-xxxx</span>
                            <span class="pc-text-muted-adaptive">&bull;</span>
                            <span class="text-danger font-monospace fw-bold" id="previewBuktiNominal">Rp 0,00</span>
                            <span class="pc-text-muted-adaptive">&bull;</span>
                            <span class="pc-text-muted-adaptive small" id="previewBuktiKategori">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewBuktiDownloadBtn" download class="btn btn-sm btn-outline-info rounded-pill px-3 d-flex align-items-center gap-1.5" title="Unduh File">
                        <i class="bi bi-download"></i>
                        <span class="d-none d-sm-inline">Unduh</span>
                    </a>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body Image / PDF View -->
            <div class="modal-body pc-modal-body-canvas p-3 p-md-4 text-center position-relative" style="min-height: 320px; max-height: 75vh; overflow-y: auto;">
                <div class="d-flex justify-content-center align-items-center h-100 w-100" id="previewBuktiContainer">
                    <img src="" id="previewBuktiImg" class="img-fluid rounded-3 shadow d-none" style="max-height: 65vh; object-fit: contain; border: 1px solid rgba(148, 163, 184, 0.25);" alt="Bukti Transaksi">
                    <iframe src="" id="previewBuktiPdfIframe" class="w-100 rounded-3 d-none shadow" style="height: 65vh; border: 1px solid rgba(148, 163, 184, 0.25);" title="Preview PDF"></iframe>
                    <div id="previewBuktiPdfPlaceholder" class="d-none py-5">
                        <i class="bi bi-file-earmark-pdf text-danger fs-1 d-block mb-3"></i>
                        <h6 class="pc-text-title-adaptive fw-bold mb-2">Dokumen Bukti Berformat PDF</h6>
                        <p class="pc-text-muted-adaptive small mb-3">File ini adalah dokumen PDF dan dapat dibuka atau diunduh langsung.</p>
                        <a href="#" id="previewBuktiPdfBtn" target="_blank" class="btn btn-danger btn-sm rounded-pill px-4 fw-semibold">
                            <i class="bi bi-eye me-1"></i> Buka Dokumen PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer pc-modal-footer px-4 py-2.5 justify-content-between">
                <small class="pc-text-muted-adaptive" style="font-size: 11px;">
                    <i class="bi bi-shield-check me-1 text-success"></i>Bukti pembayaran sah tersimpan di server
                </small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
