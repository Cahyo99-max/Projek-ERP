<!-- MODAL POPUP BROWSE PURCHASE ORDER (PO) -->
<div class="modal fade" id="modalBrowsePO" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" style="z-index: 1065 !important; background: rgba(15, 23, 42, 0.72) !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1280px; width: 96%; min-width: 0 !important;">
        <div class="modal-content border-0 shadow-lg rounded-4" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
            <!-- Modal Header with Modern Emerald Gradient -->
            <div class="modal-header py-3 px-4 border-0 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #065f46 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(4px);">
                        <i class="bi bi-file-earmark-text fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="letter-spacing: -0.01em;">Pilih Dokumen Purchase Order (PO)</h5>
                        <div class="text-white-50 small mt-0.5" style="font-size: 0.76rem;">Pilih dokumen purchase order yang akan dibuatkan tagihan faktur langsung</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1 shadow-sm" id="btn_refresh_browse_po" style="background: rgba(255,255,255,0.18);" title="Muat ulang dokumen PO">
                        <i class="bi bi-arrow-clockwise fs-6"></i>
                        <span>Segarkan</span>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4" style="overflow-x: hidden !important; max-width: 100%; min-width: 0; touch-action: pan-y;">
                <!-- Search Box -->
                <div class="mb-3">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-pill ps-3 pe-2 text-success">
                            <i class="bi bi-search fs-6"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill py-2" id="search_po_lookup" placeholder="Ketik No. PO, Supplier, atau Tender untuk mencari dokumen..." autocomplete="off">
                    </div>
                </div>

                <!-- Mobile Swipe Hint -->
                <div class="d-flex align-items-center justify-content-between px-2 py-1.5 mb-2 rounded bg-body-tertiary border d-md-none text-muted" style="font-size: 0.72rem;">
                    <span><i class="bi bi-arrows-expand me-1 text-success"></i>Geser tabel ke samping untuk melihat detail dokumen</span>
                </div>

                <!-- Table Wrapper -->
                <div class="table-responsive rounded-3 border shadow-sm" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                    <table class="table align-middle table-hover mb-0" id="tableBrowsePO" style="min-width: 780px;">
                        <thead class="bg-body-secondary text-body" style="font-size: 0.73rem; letter-spacing: 0.05em; text-transform: uppercase;">
                            <tr>
                                <th width="20%" class="py-2.5 px-3">NO. PURCHASE ORDER</th>
                                <th width="12%" class="py-2.5 px-3">TANGGAL</th>
                                <th width="32%" class="py-2.5 px-3">SUPPLIER & PROYEK TENDER</th>
                                <th width="22%" class="py-2.5 px-3 text-end">TOTAL ORDER</th>
                                <th width="14%" class="py-2.5 px-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tableBrowsePO_tbody">
                            <tr id="browse_po_loading_row" style="display:none;">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <div class="spinner-border spinner-border-sm text-success me-2"></div>
                                    Memuat daftar dokumen Purchase Order...
                                </td>
                            </tr>
                            <tr class="no-po-row" id="empty_browse_po_row" style="display:none;">
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-semibold">Tidak ada dokumen Purchase Order (PO) yang siap ditagihkan</div>
                                    <small class="text-muted">Semua Purchase Order telah dibuatkan faktur atau belum ada PO yang berstatus disetujui.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2.5 px-4 bg-body-tertiary d-flex justify-content-between align-items-center">
                <div class="small text-muted d-none d-md-block">
                    <i class="bi bi-info-circle me-1 text-success"></i>Klik pada salah satu baris atau tekan tombol <strong>Pilih</strong> untuk membuat tagihan langsung dari PO.
                </div>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold ms-auto" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
