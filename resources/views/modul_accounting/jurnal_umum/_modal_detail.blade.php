<!-- Modal Detail Voucher Jurnal -->
<div class="modal fade" id="modalDetailJurnal" tabindex="-1" aria-labelledby="modalDetailJurnalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <div class="modal-header py-3 px-4 kb-modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge bg-primary-subtle text-primary rounded-3">
                        <i class="bi bi-file-earmark-spreadsheet fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalDetailJurnalLabel">Bukti Voucher Jurnal</h5>
                        <p class="text-muted small mb-0 font-monospace" id="detail_nomor_jurnal">-</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1.5" id="btn_print_detail_jurnal">
                        <i class="bi bi-printer"></i> Cetak
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4">
                
                <!-- Info Jurnal -->
                <div class="row g-2 p-3 rounded-3 bg-body-tertiary border mb-3 small">
                    <div class="col-sm-4">
                        <span class="text-muted">Tanggal Jurnal:</span>
                        <strong class="d-block font-monospace text-body-emphasis" id="detail_tanggal">-</strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted">Sumber Transaksi:</span>
                        <strong class="d-block font-monospace text-body-emphasis" id="detail_sumber">-</strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted">Dibuat Oleh:</span>
                        <strong class="d-block text-body-emphasis" id="detail_creator">-</strong>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <span class="text-muted">Keterangan:</span>
                        <div class="fw-semibold text-body-emphasis" id="detail_keterangan">-</div>
                    </div>
                </div>

                <!-- Table Rows -->
                <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                    <table class="table table-hover align-middle mb-0 w-100">
                        <thead>
                            <tr class="text-uppercase small fw-bold text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 55%, #1d4ed8 100%) !important;">
                                <th class="py-2.5 px-3 text-center" style="width: 40px; border-right: 1px solid rgba(255,255,255,0.2);">No</th>
                                <th class="py-2.5 px-3" style="border-right: 1px solid rgba(255,255,255,0.2);">Kode &amp; Nama Akun COA</th>
                                <th class="py-2.5 px-3 text-end" style="width: 170px; border-right: 1px solid rgba(255,255,255,0.2);">Debit (Rp)</th>
                                <th class="py-2.5 px-3 text-end" style="width: 170px; border-right: 1px solid rgba(255,255,255,0.2);">Kredit (Rp)</th>
                                <th class="py-2.5 px-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="detail_rows_container">
                            <!-- Populated via AJAX -->
                        </tbody>
                        <tfoot class="bg-body-tertiary fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">TOTAL DEBIT &amp; KREDIT:</td>
                                <td class="text-end font-monospace text-success" id="detail_total_debit">Rp 0</td>
                                <td class="text-end font-monospace text-primary" id="detail_total_kredit">Rp 0</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-0.5">
                                        <i class="bi bi-check-circle me-1"></i>Balance
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

            <div class="modal-footer py-2.5 px-4 kb-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
