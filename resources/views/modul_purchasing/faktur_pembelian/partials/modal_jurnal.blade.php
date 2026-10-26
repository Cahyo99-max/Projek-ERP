<!-- Modal Jurnal Akuntansi Faktur Pembelian (Purchase Invoice) -->
<div class="modal fade" id="modalJurnalInvoice" tabindex="-1" aria-labelledby="modalJurnalInvoiceLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 960px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" 
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); width: 42px; height: 42px;" id="pi_jurnal_header_icon_box">
                        <i class="bi bi-journal-check fs-4 text-info" id="pi_jurnal_header_icon"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h5 class="modal-title fw-bold text-white mb-0 fs-6" id="modalJurnalInvoiceLabel">Jurnal Akuntansi Faktur Pembelian</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fw-bold font-monospace px-2 py-0.5" style="font-size: 0.68rem;" id="pi_jurnal_status_badge">POSTED</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="extra-small" style="color: rgba(255, 255, 255, 0.7);">No. Voucher:</span>
                            <span class="fw-bold font-monospace px-2 py-0.5 rounded-2 bg-dark text-info border border-info border-opacity-50 small" id="pi_jurnal_nomor">-</span>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-body">

                <!-- Loading State -->
                <div class="text-center py-5" id="pi_jurnal_loading">
                    <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                        <span class="visually-hidden">Memuat jurnal...</span>
                    </div>
                    <p class="text-body-secondary small mt-2 mb-0 fw-semibold">Memuat rincian jurnal akuntansi...</p>
                </div>

                <div id="pi_jurnal_content" class="d-none">

                    <!-- DUAL JURNAL SWITCHER TABS (Tampil Jika Transaksi Dibatalkan & Memiliki Jurnal Pembalik) -->
                    <div id="pi_jurnal_tabs_container" class="mb-3 d-none">
                        <div class="nav nav-pills nav-fill p-1 bg-body-tertiary rounded-3 border gap-1" role="tablist">
                            <button type="button" class="nav-link active py-2 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-2 border-0 shadow-xs pi-tab-jurnal-toggle" id="pi_tab_jurnal_utama" data-target="utama">
                                <i class="bi bi-journal-text text-primary"></i>
                                <span>Jurnal Faktur (Asli)</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary font-monospace ms-1" id="pi_tab_jurnal_utama_no">-</span>
                            </button>
                            <button type="button" class="nav-link py-2 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-2 border-0 pi-tab-jurnal-toggle" id="pi_tab_jurnal_pembalik" data-target="pembalik">
                                <i class="bi bi-arrow-repeat text-danger"></i>
                                <span>Jurnal Pembalik (Reversal)</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger font-monospace ms-1" id="pi_tab_jurnal_pembalik_no">-</span>
                            </button>
                        </div>
                    </div>

                    <!-- REVERSAL ALERT NOTICE BANNER -->
                    <div id="pi_jurnal_reversal_notice" class="alert alert-danger d-none border border-danger-subtle rounded-3 p-3 mb-3 shadow-xs">
                        <div class="d-flex align-items-start gap-2.5">
                            <div class="p-2 rounded-circle bg-danger bg-opacity-10 text-danger flex-shrink-0">
                                <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-danger mb-0.5" style="font-size:0.88rem;">Voucher Jurnal Pembalik (Reversal Entry)</div>
                                <div class="extra-small text-danger-emphasis">
                                    Voucher ini diterbitkan otomatis untuk membalik (offset) mutasi akun debit dan kredit dari Faktur Pembelian yang telah dibatalkan (VOID). Seluruh saldo utang dagang dan akun terkait transaksi ini telah dinetralisir.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TENDER / PROYEK HIGHLIGHT CARD -->
                    <div id="pi_jurnal_tender_card" class="rounded-3 p-3 mb-3 d-none border shadow-xs" 
                         style="background: rgba(245, 158, 11, 0.06); border-color: rgba(245, 158, 11, 0.28) !important;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-warning shadow-xs" 
                                     style="width: 38px; height: 38px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-briefcase-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                        <span class="badge bg-warning bg-opacity-25 text-warning-emphasis font-monospace fw-bold text-uppercase px-2 py-0.5 rounded-pill border border-warning border-opacity-25" style="font-size: 0.68rem;">
                                            <i class="bi bi-link-45deg me-0.5"></i>Terkait Tender / Proyek
                                        </span>
                                    </div>
                                    <div class="fw-bold text-body-emphasis font-monospace" style="font-size: 0.92rem;" id="pi_jurnal_tender_title">-</div>
                                </div>
                            </div>
                            <div class="text-end" id="pi_jurnal_tender_customer_wrapper">
                                <div class="extra-small text-muted fw-semibold mb-0.5">Customer / Instansi</div>
                                <div class="fw-semibold text-body-emphasis font-monospace small" id="pi_jurnal_tender_customer">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Header Jurnal Grid -->
                    <div class="card border rounded-3 p-3 mb-3 bg-body-tertiary shadow-xs">
                        <div class="row g-2.5">
                            <div class="col-6 col-md-3">
                                <div class="text-secondary extra-small fw-semibold mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-calendar3 text-success"></i>
                                    <span>Tanggal Jurnal</span>
                                </div>
                                <div class="font-monospace fw-bold text-body-emphasis small" id="pi_jurnal_tanggal">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-secondary extra-small fw-semibold mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-receipt text-primary"></i>
                                    <span>No. Faktur Internal</span>
                                </div>
                                <div class="font-monospace fw-bold text-primary small" id="pi_jurnal_ref_faktur">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-secondary extra-small fw-semibold mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-tag text-info"></i>
                                    <span>No. Faktur Supplier</span>
                                </div>
                                <div class="font-monospace fw-bold text-body-emphasis small" id="pi_jurnal_ref_supplier_inv">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-secondary extra-small fw-semibold mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-building text-warning"></i>
                                    <span>Supplier / Pemasok</span>
                                </div>
                                <div class="fw-semibold text-body-emphasis small text-truncate" id="pi_jurnal_supplier">-</div>
                            </div>
                            <div class="col-12 pt-2 border-top mt-2">
                                <div class="text-secondary extra-small fw-semibold mb-1 d-flex align-items-center gap-1">
                                    <i class="bi bi-card-text text-secondary"></i>
                                    <span>Keterangan Transaksi</span>
                                </div>
                                <div class="text-body-secondary small text-break" id="pi_jurnal_keterangan">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Entri Akun (Double-Entry Debit & Kredit) -->
                    <div class="table-responsive border rounded-3 mb-3 shadow-xs overflow-hidden">
                        <table class="table table-hover align-middle mb-0" id="pi_jurnal_table" style="font-size: 0.82rem;">
                            <thead>
                                <tr class="text-uppercase fw-bold text-body-secondary border-bottom bg-body-tertiary" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                    <th class="text-center py-2.5 px-3" style="width: 45px;">No</th>
                                    <th class="py-2.5 px-3" style="width: 110px;">Kode Akun</th>
                                    <th class="py-2.5 px-3" style="width: 210px;">Nama Akun COA</th>
                                    <th class="text-end py-2.5 px-3" style="width: 155px;">Debit (Rp)</th>
                                    <th class="text-end py-2.5 px-3" style="width: 155px;">Kredit (Rp)</th>
                                    <th class="py-2.5 px-3" style="min-width: 180px;">Catatan Baris</th>
                                </tr>
                            </thead>
                            <tbody id="pi_jurnal_items_tbody" class="border-top-0">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                            <tfoot class="border-top bg-body-tertiary fw-bold" style="font-size: 0.85rem;">
                                <tr>
                                    <td colspan="3" class="text-end py-2.5 px-3 text-body-secondary text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                        Total Debit &amp; Kredit:
                                    </td>
                                    <td class="text-end font-monospace py-2.5 px-3 text-success text-nowrap" id="pi_jurnal_total_debit">Rp 0,00</td>
                                    <td class="text-end font-monospace py-2.5 px-3 text-primary text-nowrap" id="pi_jurnal_total_kredit">Rp 0,00</td>
                                    <td class="py-2.5 px-3 text-center" id="pi_jurnal_balance_badge">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">
                                            <i class="bi bi-check-circle me-1"></i>100% Balance
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Catatan Tambahan Footer -->
                    <div class="p-2.5 px-3 rounded-3 bg-body-tertiary border d-flex align-items-center justify-content-between text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-5" id="pi_jurnal_footer_note_icon"></i>
                            <span id="pi_jurnal_footer_note_text">Voucher jurnal ini diterbitkan otomatis oleh sistem ERP saat Faktur Pembelian disetujui (ACC).</span>
                        </div>
                        <div class="font-monospace text-body-secondary" style="font-size: 0.72rem;">
                            Double-Entry Ledger Verified
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-2.5 px-4 bg-body d-flex justify-content-between">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Dua desimal dibulatkan presisi sesuai standar akuntansi internasional.
                </span>
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
