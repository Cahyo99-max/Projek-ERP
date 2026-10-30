<!-- Modal Voucher Jurnal Akuntansi Pembayaran Piutang -->
<div class="modal fade" id="modalJurnalPayment" tabindex="-1" aria-labelledby="modalJurnalPaymentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 950px; width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                    <div class="pay-jurnal-icon-badge flex-shrink-0">
                        <i class="bi bi-journal-check fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-0.5 flex-wrap">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-sm-5 text-truncate" id="modalJurnalPaymentLabel">Voucher Jurnal Akuntansi</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fw-bold font-monospace px-2 py-0.5 d-none d-md-inline" style="font-size: 0.7rem;">Auto-Posted</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-body-secondary small" style="font-size: 0.76rem;">No. Voucher:</span>
                            <span class="pay-jurnal-badge-code fw-bold font-monospace px-2 py-0.5 rounded-2" id="jp_nomor_jurnal" style="font-size: 0.78rem;">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <a href="#" id="btn_print_jurnal_payment" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs fw-bold no-loader d-none" title="Cetak Voucher Jurnal">
                        <i class="bi bi-printer"></i>
                        <span class="d-none d-sm-inline">Cetak Voucher</span>
                        <span class="d-inline d-sm-none">Cetak</span>
                    </a>
                    <button type="button" class="pay-btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-sm-4 bg-body" id="modalJurnalPaymentBody">
                <div class="text-center py-5" id="jurnal_payment_loading">
                    <div class="spinner-border text-info" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat jurnal...</span>
                    </div>
                    <p class="text-body-secondary small mt-2 mb-0">Memuat rincian voucher jurnal akuntansi...</p>
                </div>

                <div id="jurnal_payment_content" class="d-none">
                    <!-- Info Header Voucher Grid -->
                    <div class="card border border-body-tertiary shadow-sm rounded-3 mb-3.5 bg-body-tertiary">
                        <div class="card-body p-3">
                            <div class="row g-2.5 g-sm-3">
                                <div class="col-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.72rem;">
                                        <i class="bi bi-calendar3 text-success me-1"></i> Tanggal Jurnal
                                    </small>
                                    <span class="fw-semibold text-body-emphasis font-monospace" id="jp_tanggal_jurnal">-</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.72rem;">
                                        <i class="bi bi-tag text-info me-1"></i> Referensi
                                    </small>
                                    <span class="badge bg-info-subtle text-info fw-semibold" id="jp_referensi">-</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.72rem;">
                                        <i class="bi bi-person text-primary me-1"></i> Dibuat Oleh
                                    </small>
                                    <span class="fw-semibold text-body-emphasis text-truncate d-block" id="jp_creator">-</span>
                                </div>
                                <div class="col-6 col-md-3" id="jp_tender_wrapper">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.72rem;">
                                        <i class="bi bi-briefcase text-warning me-1"></i> Tender / Proyek
                                    </small>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace px-2 py-0.5 text-truncate mw-100" id="jp_tender" style="font-size: 0.75rem;">
                                        -
                                    </span>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.72rem;">
                                        <i class="bi bi-card-text text-secondary me-1"></i> Keterangan / Memo Transaksi
                                    </small>
                                    <span class="text-body-emphasis small text-break" id="jp_keterangan">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Swipe Hint Banner -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2.5 rounded-3 pay-mobile-swipe-banner shadow-2xs">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left-right text-success"></i>
                            <span class="fw-semibold small">Geser tabel jurnal ke samping</span>
                        </div>
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold pay-swipe-pill">Swipe &rarr;</span>
                    </div>

                    <!-- Journal Details Table -->
                    <div class="table-responsive rounded-3 border border-body-tertiary shadow-sm mb-3" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                        <table class="table table-hover align-middle mb-0 w-100" style="min-width: 680px; font-size: 0.86rem;">
                            <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th style="width: 14%;" class="ps-3 py-2.5 text-nowrap">Kode Akun</th>
                                    <th style="min-width: 250px;" class="py-2.5">Nama Akun (COA) &amp; Keterangan</th>
                                    <th style="width: 22%; min-width: 155px;" class="text-end py-2.5 text-nowrap">Debit (IDR)</th>
                                    <th style="width: 22%; min-width: 155px;" class="text-end pe-3 py-2.5 text-nowrap">Kredit (IDR)</th>
                                </tr>
                            </thead>
                            <tbody id="jp_details_table_body">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                            <tfoot class="bg-body-secondary font-monospace fw-bold border-top border-2">
                                <tr>
                                    <td colspan="2" class="ps-3 py-2 text-uppercase text-body-emphasis">Total Saldo</td>
                                    <td class="text-end py-2 text-success text-nowrap" id="jp_total_debit">Rp 0,00</td>
                                    <td class="text-end pe-3 py-2 text-primary text-nowrap" id="jp_total_kredit">Rp 0,00</td>
                                </tr>
                                <tr id="jp_balance_row">
                                    <td colspan="4" class="ps-3 pe-3 py-1.5 text-center small" id="jp_balance_status">
                                        <!-- Balance alert badge -->
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-body-secondary small"><i class="bi bi-shield-check text-success me-1"></i> Jurnal Akuntansi Terintegrasi Otomatis</span>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
