<!-- Modal Voucher Jurnal Akuntansi Pembayaran Piutang -->
<div class="modal fade" id="modalJurnalPayment" tabindex="-1" aria-labelledby="modalJurnalPaymentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2 rounded-3 bg-info text-white d-inline-flex shadow-sm">
                        <i class="bi bi-journal-check fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalJurnalPaymentLabel">Voucher Jurnal Akuntansi</h5>
                        <small class="text-body-secondary" id="jurnal_payment_subtitle">Penerimaan Kas & Pelunasan Piutang</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btn_print_jurnal_payment" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-none">
                        <i class="bi bi-printer me-1"></i> Cetak Voucher
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-body" id="modalJurnalPaymentBody">
                <div class="text-center py-5" id="jurnal_payment_loading">
                    <div class="spinner-border text-info" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat jurnal...</span>
                    </div>
                    <p class="text-body-secondary small mt-2 mb-0">Memuat rincian voucher jurnal akuntansi...</p>
                </div>

                <div id="jurnal_payment_content" class="d-none">
                    <!-- Info Header Voucher -->
                    <div class="card border border-body-tertiary shadow-sm rounded-3 mb-4 bg-body-tertiary">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Nomor Jurnal</small>
                                    <span class="fw-bold font-monospace text-info fs-6" id="jp_nomor_jurnal">-</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Tanggal Jurnal</small>
                                    <span class="fw-semibold text-body-emphasis" id="jp_tanggal_jurnal">-</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Referensi / Tipe</small>
                                    <span class="badge bg-info-subtle text-info fw-semibold" id="jp_referensi">-</span>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Dibuat Oleh</small>
                                    <span class="fw-semibold text-body-emphasis" id="jp_creator">-</span>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="flex-grow-1">
                                        <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Keterangan / Memo</small>
                                        <span class="text-body-emphasis small" id="jp_keterangan">-</span>
                                    </div>
                                    <div id="jp_tender_wrapper" class="d-none text-end">
                                        <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.725rem;">Tender / Proyek Terkait</small>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace px-2.5 py-1" id="jp_tender" style="font-size: 0.78rem;">
                                            <i class="bi bi-briefcase me-1"></i> -
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Journal Details Table -->
                    <div class="table-responsive rounded-3 border border-body-tertiary shadow-sm mb-3">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                            <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th style="width: 15%;" class="ps-3 py-2.5">Kode Akun</th>
                                    <th style="width: 35%;" class="py-2.5">Nama Akun (COA)</th>
                                    <th style="width: 25%;" class="text-end py-2.5">Debit (IDR)</th>
                                    <th style="width: 25%;" class="text-end pe-3 py-2.5">Kredit (IDR)</th>
                                </tr>
                            </thead>
                            <tbody id="jp_details_table_body">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                            <tfoot class="bg-body-secondary font-monospace fw-bold border-top border-2">
                                <tr>
                                    <td colspan="2" class="ps-3 py-2 text-uppercase text-body-emphasis">Total Saldo</td>
                                    <td class="text-end py-2 text-success" id="jp_total_debit">Rp 0,00</td>
                                    <td class="text-end pe-3 py-2 text-primary" id="jp_total_kredit">Rp 0,00</td>
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
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-4 d-flex justify-content-between">
                <span class="text-body-secondary small"><i class="bi bi-shield-check text-success me-1"></i> Jurnal Akuntansi Terintegrasi Otomatis</span>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
