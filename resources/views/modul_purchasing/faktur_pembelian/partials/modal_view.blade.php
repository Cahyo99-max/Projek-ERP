<!-- ===== MODAL DETAIL / VIEW FAKTUR PEMBELIAN ===== -->
<div class="modal fade" id="modalDetailInvoice" tabindex="-1" aria-labelledby="modalDetailInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xl-down modal-dialog-invoice-wide modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg">

            <!-- ── GRADIENT MODAL HEADER ── -->
            <div class="modal-header inv-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 shadow-sm" style="background:rgba(255,255,255,0.15); backdrop-filter:blur(6px);">
                        <i class="bi bi-receipt fs-4 text-white"></i>
                    </div>
                    <div>
                        <div class="modal-title fs-5 fw-bold" id="modalDetailInvoiceLabel">Detail Faktur Pembelian</div>
                        <div class="text-subtitle" id="view_nomor_faktur_sub" style="font-size:0.75rem; opacity:0.85;">Purchase Invoice (AP) — Rincian Tagihan & Pelunasan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div id="view_status_badge"></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- ── MODAL BODY ── -->
            <div class="modal-body p-0 bg-body-tertiary">

                <!-- ── STICKY ACTION BAR ── -->
                <div class="inv-action-bar shadow-sm">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left me-1"></i> Tutup
                        </button>
                        <div class="vr mx-1 d-none d-sm-block"></div>
                        <button type="button" class="btn btn-warning rounded-pill px-3 text-dark fw-semibold shadow-sm" id="view_btn_edit_trigger" style="display:none;">
                            <i class="bi bi-pencil-square me-1"></i> Edit Faktur
                        </button>
                        <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold shadow-sm" id="view_btn_post_trigger" style="display:none;">
                            <i class="bi bi-send-check me-1"></i> Posting Faktur
                        </button>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="#" target="_blank" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" id="view_btn_print_pdf">
                            <i class="bi bi-printer me-1"></i> Cetak Bukti Faktur (PDF)
                        </a>
                    </div>
                </div>

                <!-- ── MAIN CONTENT ── -->
                <div class="p-3 p-md-4">
                    <div class="row g-3 g-md-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-12 col-xl-8">

                            <!-- CARD 1: INFORMASI DOKUMEN -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-info-circle-fill text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body">Informasi Dokumen Tagihan</h6>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace" style="font-size:0.75rem;">
                                        Dokumen Sah
                                    </span>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">No. Faktur Internal</span>
                                            <span class="fw-bold text-primary font-monospace fs-6" id="view_disp_nomor_faktur">-</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">No. Faktur Supplier</span>
                                            <span class="fw-bold text-body fs-6" id="view_disp_faktur_supplier">-</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Tanggal Faktur Tagihan</span>
                                            <span class="fw-semibold text-body" id="view_disp_tanggal_faktur">-</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Tanggal Jatuh Tempo</span>
                                            <span class="fw-semibold text-warning-emphasis font-monospace" id="view_disp_tanggal_jatuh_tempo">-</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Nama Supplier</span>
                                            <strong class="text-body d-block fs-6" id="view_disp_supplier_name">-</strong>
                                            <span class="extra-small text-muted font-monospace" id="view_disp_supplier_npwp">NPWP: -</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Ref. Dokumen Penerimaan (GRN) & PO</span>
                                            <strong class="text-info font-monospace d-block" id="view_disp_ref_gr">-</strong>
                                            <span class="extra-small text-muted font-monospace" id="view_disp_ref_po">PO: -</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Mata Uang & Kurs</span>
                                            <span class="fw-semibold text-body font-monospace" id="view_disp_currency_kurs">IDR (Kurs: 1.0000)</span>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <span class="text-muted extra-small d-block">Syarat Pembayaran / Pajak</span>
                                            <span class="fw-semibold text-body" id="view_disp_term_pajak">-</span>
                                        </div>
                                        <div class="col-12" id="view_container_keterangan">
                                            <span class="text-muted extra-small d-block mb-1">Catatan / Keterangan Faktur</span>
                                            <div class="small text-body bg-body-tertiary p-2 rounded-2 border" id="view_disp_keterangan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 2: TABEL RINCIAN ITEM BARANG -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-box-seam-fill text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body">Rincian Item Barang Diterima</h6>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover mb-0" id="tableViewItems">
                                            <thead class="bg-body-tertiary">
                                                <tr>
                                                    <th width="4%" class="text-center">NO</th>
                                                    <th width="15%">KODE</th>
                                                    <th width="28%">NAMA BARANG</th>
                                                    <th width="8%" class="text-center">QTY</th>
                                                    <th width="8%">SATUAN</th>
                                                    <th width="17%" class="text-end">HARGA SATUAN</th>
                                                    <th width="20%" class="text-end">SUBTOTAL NET</th>
                                                </tr>
                                            </thead>
                                            <tbody id="view_items_tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 3: BIAYA TAMBAHAN & PPN -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" id="view_card_extra_costs">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-truck text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body">Biaya Tambahan & PPN</h6>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover mb-0" id="tableViewExtraCosts">
                                            <thead class="bg-body-tertiary">
                                                <tr>
                                                    <th width="35%">JENIS BIAYA / PAJAK</th>
                                                    <th width="20%" class="text-center">ALOKASI HPP</th>
                                                    <th width="25%">AKUN (COA)</th>
                                                    <th width="20%" class="text-end">NOMINAL</th>
                                                </tr>
                                            </thead>
                                            <tbody id="view_extra_costs_tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 4: RIWAYAT PEMBAYARAN UTANG -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" id="view_card_payments">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-cash-stack text-success"></i>
                                        <h6 class="fw-bold mb-0 text-body">Riwayat Pembayaran Utang</h6>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace" id="view_pay_count_badge">
                                        0 Pembayaran
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover mb-0" id="tableViewPayments">
                                            <thead class="bg-body-tertiary">
                                                <tr>
                                                    <th width="24%">NO. BUKTI BAYAR</th>
                                                    <th width="16%">TANGGAL</th>
                                                    <th width="22%">KAS / BANK</th>
                                                    <th width="22%" class="text-end">JUMLAH BAYAR</th>
                                                    <th width="16%" class="text-center">STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody id="view_payments_tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT COLUMN: FINANCIAL & JURNAL -->
                        <div class="col-12 col-xl-4">

                            <!-- RINGKASAN KALKULASI TAGIHAN -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                <div class="erp-card-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-receipt text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body">RINGKASAN NILAI FAKTUR</h6>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="calc-row">
                                        <span class="calc-label">Subtotal Barang (Gross)</span>
                                        <span class="calc-value text-body" id="view_sum_subtotal">0,00</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">Total Diskon Item</span>
                                        <span class="calc-value text-danger" id="view_sum_diskon">0,00</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label fw-bold">DPP (Net Barang)</span>
                                        <span class="calc-value text-primary" id="view_sum_dpp">0,00</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label" id="view_label_ppn">PPN Masukan</span>
                                        <span class="calc-value text-body" id="view_sum_ppn">0,00</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">Total Biaya Tambahan</span>
                                        <span class="calc-value text-body" id="view_sum_biaya_lain">0,00</span>
                                    </div>
                                    <div class="calc-grand-total shadow-sm">
                                        <div class="label">Total Tagihan (Grand Total)</div>
                                        <div class="value" id="view_sum_total_invoice">Rp 0,00</div>
                                        <div class="small mt-1 text-white-50 font-monospace" id="view_sum_total_invoice_idr" style="display:none; font-size:0.75rem;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- STATUS PEMBAYARAN & SISA HUTANG -->
                            <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                <div class="erp-card-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-wallet2 text-success"></i>
                                        <h6 class="fw-bold mb-0 text-body">STATUS PEMBAYARAN UTANG</h6>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small">Total Tagihan</span>
                                        <span class="fw-bold text-body font-monospace" id="view_pay_tagihan">Rp 0,00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small">Sudah Dibayar</span>
                                        <span class="fw-bold text-success font-monospace" id="view_pay_terbayar">Rp 0,00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span class="text-muted small">Potongan Pembayaran</span>
                                        <span class="fw-bold text-info font-monospace" id="view_pay_potongan">Rp 0,00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2">
                                        <span class="fw-bold text-body">Sisa Hutang Tagihan</span>
                                        <span class="fw-bold fs-6 text-warning-emphasis font-monospace" id="view_pay_sisa">Rp 0,00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- INFORMASI JURNAL AKUNTANSI OTOMATIS -->
                            <div class="erp-card bg-body shadow-sm" id="view_card_jurnal">
                                <div class="erp-card-header">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-journal-bookmark-fill text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body">JURNAL AKUNTANSI OTOMATIS</h6>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div id="view_jurnal_content">
                                        <div class="small text-muted text-center py-3">Belum ada jurnal diterbitkan (Status Draft).</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
