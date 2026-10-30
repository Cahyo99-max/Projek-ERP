<!-- Modal Jurnal Akuntansi Faktur Penjualan -->
<div class="modal fade" id="modalJurnalSalesInvoice" tabindex="-1" aria-labelledby="modalJurnalSalesInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable faktur-modal-dialog" style="max-width: 950px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center border-bottom bg-body-secondary">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                    <div class="si-jurnal-icon-badge flex-shrink-0">
                        <i class="bi bi-journal-check fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-0.5 flex-wrap">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-sm-5 text-truncate" id="modalJurnalSalesInvoiceLabel">
                                <span class="d-none d-sm-inline">Jurnal Akuntansi Faktur Penjualan</span>
                                <span class="d-inline d-sm-none">Jurnal Faktur Penjualan</span>
                            </h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fw-bold font-monospace px-2 py-0.5 d-none d-md-inline" style="font-size: 0.7rem;">Auto-Posted</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-body-secondary small" style="font-size: 0.76rem;">No. Voucher:</span>
                            <span class="si-jurnal-badge-code fw-bold font-monospace px-2 py-0.5 rounded-2" id="inv_jurnal_nomor" style="font-size: 0.78rem;">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <a href="#" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs fw-bold no-loader" id="btn_print_inv_jurnal" title="Cetak Voucher Jurnal">
                        <i class="bi bi-printer"></i>
                        <span class="d-none d-sm-inline">Cetak Voucher</span>
                        <span class="d-inline d-sm-none">Cetak</span>
                    </a>
                    <button type="button" class="si-btn-close" data-bs-dismiss="modal" aria-label="Close" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-sm-4">
                <!-- Info Header Jurnal Grid -->
                <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 mb-3.5 shadow-2xs">
                    <div class="row g-2.5 g-sm-3">
                        <div class="col-6 col-md-3">
                            <div class="h-100">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-calendar3 text-success me-1"></i> Tanggal Jurnal
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="inv_jurnal_tanggal">-</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="h-100">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-receipt text-info me-1"></i> Ref. Faktur
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="inv_jurnal_ref_faktur">-</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="h-100 overflow-hidden">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-building text-primary me-1"></i> Entitas Pembukuan
                                </span>
                                <div id="inv_jurnal_entitas_badge" class="overflow-hidden mw-100">-</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="h-100 overflow-hidden">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-person text-warning me-1"></i> Customer
                                </span>
                                <div class="fw-semibold text-body-emphasis text-truncate" id="inv_jurnal_customer">-</div>
                            </div>
                        </div>
                        <div class="col-12 border-top pt-2">
                            <span class="text-body-secondary small fw-semibold d-block mb-1">
                                <i class="bi bi-card-text text-secondary me-1"></i> Keterangan Transaksi
                            </span>
                            <div class="text-body-emphasis text-break small" id="inv_jurnal_keterangan">-</div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Swipe Hint Banner -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2.5 rounded-3 si-mobile-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-success"></i>
                        <span class="fw-semibold small">Geser tabel jurnal ke samping</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold si-swipe-pill">Swipe &rarr;</span>
                </div>

                <!-- Tabel Baris Jurnal -->
                <div class="table-responsive rounded-3 border border-body-tertiary mb-3" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                    <table class="table table-hover align-middle mb-0 w-100" style="min-width: 760px;">
                        <thead class="bg-body-secondary text-body-secondary small text-uppercase fw-bold">
                            <tr>
                                <th class="text-center" style="width: 44px;">No</th>
                                <th style="min-width: 250px;">Kode &amp; Nama Akun COA</th>
                                <th class="text-end text-nowrap" style="width: 175px;">Debit (Rp)</th>
                                <th class="text-end text-nowrap" style="width: 175px;">Kredit (Rp)</th>
                                <th style="min-width: 200px;">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="inv_jurnal_rows_container" class="font-monospace small">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot class="bg-body-secondary border-top">
                            <tr>
                                <td colspan="2" class="text-end fw-bold text-uppercase small text-body-secondary">TOTAL:</td>
                                <td class="text-end font-monospace fw-bold text-success fs-6 text-nowrap" id="inv_jurnal_total_debit">Rp 0</td>
                                <td class="text-end font-monospace fw-bold text-primary fs-6 text-nowrap" id="inv_jurnal_total_kredit">Rp 0</td>
                                <td class="text-center">
                                    <div id="inv_jurnal_balance_container">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1">
                                            <i class="bi bi-check2-circle me-1"></i>Balance
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-secondary py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
                <div class="text-body-secondary small font-monospace" style="font-size: 0.74rem;">
                    <i class="bi bi-shield-check text-success me-1"></i>Diposting otomatis oleh sistem Akuntansi ERP
                </div>
            </div>
        </div>
    </div>
</div>
