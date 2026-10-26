<!-- Modal Jurnal Akuntansi Faktur Penjualan -->
<div class="modal fade" id="modalJurnalSalesInvoice" tabindex="-1" aria-labelledby="modalJurnalSalesInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable faktur-modal-dialog" style="max-width: 950px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header bg-body-secondary py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 bg-success-subtle text-success fs-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalJurnalSalesInvoiceLabel">Jurnal Akuntansi Faktur Penjualan</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fw-bold font-monospace px-2 py-0.5" style="font-size: 0.72rem;">Auto-Posted</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">No. Voucher Jurnal:</span>
                            <span class="fw-bold font-monospace px-2 py-0.5 rounded-2 bg-body-tertiary text-primary border border-primary-subtle" id="inv_jurnal_nomor">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm fw-bold no-loader" id="btn_print_inv_jurnal">
                        <i class="bi bi-printer"></i> Cetak Voucher
                    </a>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Info Header Jurnal Grid -->
                <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="h-100">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-calendar3 text-success me-1"></i> Tanggal Jurnal
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="inv_jurnal_tanggal">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h-100">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-building text-primary me-1"></i> Entitas Pembukuan
                                </span>
                                <div id="inv_jurnal_entitas_badge">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h-100">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-receipt text-info me-1"></i> Ref. Faktur
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="inv_jurnal_ref_faktur">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="h-100">
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

                <!-- Tabel Baris Jurnal -->
                <div class="table-responsive rounded-3 border border-body-tertiary mb-3">
                    <table class="table table-hover align-middle mb-0 w-100">
                        <thead class="bg-body-secondary text-body-secondary small text-uppercase fw-bold">
                            <tr>
                                <th class="text-center" style="width: 44px;">No</th>
                                <th>Kode &amp; Nama Akun COA</th>
                                <th class="text-end" style="width: 175px;">Debit (Rp)</th>
                                <th class="text-end" style="width: 175px;">Kredit (Rp)</th>
                                <th style="width: 250px;">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="inv_jurnal_rows_container" class="font-monospace small">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot class="bg-body-secondary border-top">
                            <tr>
                                <td colspan="2" class="text-end fw-bold text-uppercase small text-body-secondary">TOTAL:</td>
                                <td class="text-end font-monospace fw-bold text-success fs-6" id="inv_jurnal_total_debit">Rp 0</td>
                                <td class="text-end font-monospace fw-bold text-primary fs-6" id="inv_jurnal_total_kredit">Rp 0</td>
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
            <div class="modal-footer bg-body-secondary py-2.5 px-4 d-flex justify-content-between align-items-center border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i> Tutup
                </button>
                <div class="text-body-secondary small font-monospace">
                    Diposting otomatis oleh sistem Akuntansi ERP
                </div>
            </div>
        </div>
    </div>
</div>
