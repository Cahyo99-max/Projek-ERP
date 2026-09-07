<div class="modal modal-blur fade" id="modalScanner" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: #0f172a; color: #f8fafc;">
            <div class="modal-header border-0 pb-1 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-camera-fill me-1"></i> Mode Kamera Belakang
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="switchCamera()" title="Ganti Kamera">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="scanner-viewport-wrapper my-2">
                    <div id="scanner_reader"></div>
                    <div class="scanner-overlay" style="display: none;">
                        <div class="scanner-target-box">
                            <div class="scanner-corner-tr"></div>
                            <div class="scanner-corner-bl"></div>
                            <div class="scanner-laser-line"></div>
                            <div class="scanner-center-dot"></div>
                            <div class="scanner-guide-dots scanner-dot-left"></div>
                            <div class="scanner-guide-dots scanner-dot-right"></div>
                            <div class="scanner-guide-dots scanner-dot-top"></div>
                            <div class="scanner-guide-dots scanner-dot-bottom"></div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="text-white-50 small">Arahkan barcode / QR Code ke dalam area bidik hijau</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
