<!-- MODAL PREVIEW BUKTI / FOTO TRANSAKSI KAS & BANK -->
<div class="modal fade" id="modalPreviewBuktiKasBank" tabindex="-1" aria-labelledby="modalPreviewBuktiKasBankLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <!-- Modal Header -->
            <div class="modal-header px-4 py-3 border-bottom kb-modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" id="previewKbHeaderIconBox" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; width: 40px; height: 40px;">
                        <i class="bi bi-receipt-cutoff fs-5" id="previewKbHeaderIcon"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalPreviewBuktiKasBankLabel">Lampiran Bukti Transaksi</h6>
                            <span class="badge rounded-pill font-monospace" id="previewKbTipeBadge" style="font-size: 0.68rem;">KAS</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 small text-muted mt-0.5 font-monospace flex-wrap" style="font-size: 0.76rem;">
                            <span class="fw-bold text-body-emphasis" id="previewKbNomor">KM-xxxx</span>
                            <span>&bull;</span>
                            <span class="fw-bold" id="previewKbNominal">Rp 0</span>
                            <span>&bull;</span>
                            <span id="previewKbKategori">-</span>
                            <span id="previewKbAkunSep">&bull;</span>
                            <span id="previewKbAkun" class="text-truncate" style="max-width: 180px;">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewKbDownloadBtn" download class="btn btn-sm btn-outline-success rounded-pill px-3 d-flex align-items-center gap-1.5" title="Unduh Berkas Asli">
                        <i class="bi bi-download"></i>
                        <span class="d-none d-sm-inline">Unduh</span>
                    </a>
                    <button type="button" class="btn-close ms-1 shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body Image / PDF View -->
            <div class="modal-body p-3 p-md-4 text-center position-relative bg-body-tertiary" style="min-height: 340px; max-height: 75vh; overflow-y: auto;">
                <div class="d-flex justify-content-center align-items-center h-100 w-100" id="previewKbContentArea">
                    <!-- Preview Image -->
                    <img src="" id="previewKbImg" class="img-fluid rounded-3 shadow d-none" style="max-height: 65vh; object-fit: contain; border: 1px solid rgba(148, 163, 184, 0.25); background: #ffffff;" alt="Bukti Foto Kas Bank">
                    
                    <!-- Preview PDF Iframe -->
                    <iframe src="" id="previewKbPdfIframe" class="w-100 rounded-3 d-none shadow" style="height: 65vh; border: 1px solid rgba(148, 163, 184, 0.25);" title="Preview PDF"></iframe>
                    
                    <!-- PDF Fallback Placeholder -->
                    <div id="previewKbPdfPlaceholder" class="d-none py-5">
                        <i class="bi bi-file-earmark-pdf text-danger fs-1 d-block mb-3"></i>
                        <h6 class="text-body-emphasis fw-bold mb-2">Dokumen Bukti Berformat PDF</h6>
                        <p class="text-muted small mb-3">Dokumen PDF resmi transaksi tersimpan di server.</p>
                        <a href="#" id="previewKbPdfBtn" target="_blank" class="btn btn-danger btn-sm rounded-pill px-4 fw-semibold">
                            <i class="bi bi-eye me-1"></i> Buka Dokumen PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-2.5 justify-content-between kb-modal-footer border-top">
                <small class="text-muted font-monospace" style="font-size: 11px;">
                    <i class="bi bi-shield-check me-1 text-success"></i>Bukti pembayaran sah &amp; terverifikasi modul kas/bank
                </small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
