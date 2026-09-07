<!-- ===== MODAL EDIT FAKTUR PEMBELIAN ===== -->
<div class="modal fade" id="modalEditInvoice" tabindex="-1" aria-labelledby="modalEditInvoiceLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-xl-down modal-dialog-invoice-wide modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg">

            <!-- ── GRADIENT MODAL HEADER ── -->
            <div class="modal-header inv-modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 shadow-sm" style="background:rgba(255,255,255,0.15); backdrop-filter:blur(6px);">
                        <i class="bi bi-pencil-square fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="modal-title fs-5 fw-bold" id="modalEditInvoiceLabel">Edit Faktur Pembelian</div>
                        <div class="text-subtitle" id="modalEditInvoiceSubtitle" style="font-size:0.75rem; opacity:0.85;">Purchase Invoice (AP) — Penyesuaian Data Tagihan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="inv-status-pill draft shadow-sm" id="edit_badge_status_form">
                        <i class="bi bi-pencil-fill text-warning" style="font-size:0.5rem;"></i> DRAFT
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- ── MODAL BODY ── -->
            <div class="modal-body p-0 bg-body-tertiary">
                <form id="formEditInvoice" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="invoice_id" id="edit_invoice_id" value="">
                    <input type="hidden" name="action_type" id="edit_action_type" value="draft">

                    <!-- ── STICKY ACTION BAR ── -->
                    <div class="inv-action-bar shadow-sm">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </button>
                            <div class="vr mx-1 d-none d-sm-block"></div>
                            <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                            <button type="button" class="btn fw-semibold rounded-pill px-4 text-white shadow-sm" style="background:#0ea5e9;" id="edit_btn_submit_post_invoice">
                                <i class="bi bi-send-check me-1"></i> Simpan & Posting Jurnal
                            </button>
                        </div>
                    </div>

                    <!-- ── MAIN FORM BODY ── -->
                    <div class="p-3 p-md-4">
                        <div class="row g-3 g-md-4">

                            <!-- LEFT COLUMN: DOKUMEN & ITEM -->
                            <div class="col-12 col-xl-8">

                                <!-- CARD 1: INFORMASI DOKUMEN FAKTUR -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text-fill text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">1. Informasi Dokumen Faktur</h6>
                                        </div>
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 font-monospace" style="font-size:0.75rem;">
                                            <i class="bi bi-pencil me-1"></i>Mode Edit
                                        </span>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <div class="row g-3">

                                            <!-- DOKUMEN PENERIMAAN (GRN) TERKUNCI -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted small">
                                                    <i class="bi bi-box-seam me-1"></i>Dokumen Penerimaan Barang (GRN / BPB)
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                                                    <input type="text" class="form-control border-start-0 fw-semibold bg-body-tertiary font-monospace" id="edit_disp_gr_ref" readonly>
                                                    <input type="hidden" name="gr_id" id="edit_gr_id">
                                                </div>
                                                <div class="form-text text-muted extra-small">GRN terkunci pada mode edit faktur.</div>
                                            </div>

                                            <div class="col-12"><hr class="my-1 border-secondary-subtle"></div>

                                            <!-- NOMOR FAKTUR INTERNAL & SUPPLIER -->
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-muted small">No. Faktur Internal</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-hash text-muted"></i></span>
                                                    <input type="text" class="form-control bg-body-tertiary font-monospace fw-bold text-primary" id="edit_disp_nomor_faktur_internal" readonly>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small required">No. Faktur dari Supplier</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-receipt text-muted"></i></span>
                                                    <input type="text" class="form-control fw-bold" name="nomor_faktur_supplier" id="edit_nomor_faktur_supplier" required placeholder="Contoh: INV-SUP-2026-0089">
                                                </div>
                                            </div>

                                            <!-- TANGGAL FAKTUR & JATUH TEMPO -->
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small required">Tanggal Faktur Tagihan</label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_faktur" id="edit_tanggal_faktur" required>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small">Tanggal Jatuh Tempo</label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_jatuh_tempo" id="edit_tanggal_jatuh_tempo">
                                            </div>

                                            <!-- PAYMENT TERM & PAJAK -->
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small">Syarat Pembayaran (Term)</label>
                                                <select class="form-select form-select-sm" name="payment_term_id" id="edit_payment_term_id">
                                                    <option value="">Cash / Tanpa Term (0 Hari)</option>
                                                    @foreach($paymentTerms as $pt)
                                                    <option value="{{ $pt->id }}" data-hari="{{ $pt->jumlah_hari }}">{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} Hari)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small">Pajak Pertambahan Nilai (PPN)</label>
                                                <select class="form-select form-select-sm" name="pajak_id" id="edit_pajak_id">
                                                    <option value="" data-tarif="0">Non-PPN (0%)</option>
                                                    @foreach($pajaks as $pjk)
                                                    <option value="{{ $pjk->id }}" data-tarif="{{ $pjk->tarif }}">{{ $pjk->nama_pajak }} ({{ $pjk->tarif }}%)</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- METADATA CARD READONLY -->
                                            <div class="col-12">
                                                <div class="p-3 rounded-3 bg-body-tertiary border">
                                                    <div class="row g-2 extra-small">
                                                        <div class="col-12 col-md-4">
                                                            <span class="text-muted d-block">Supplier:</span>
                                                            <strong class="text-body" id="edit_disp_supplier_txt">—</strong>
                                                            <input type="hidden" id="edit_disp_supplier">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <span class="text-muted d-block">Gudang Penerima:</span>
                                                            <strong class="text-body" id="edit_disp_gudang_txt">—</strong>
                                                            <input type="hidden" id="edit_disp_gudang">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <span class="text-muted d-block">Ref. Purchase Order (PO):</span>
                                                            <strong class="text-primary font-monospace" id="edit_disp_po_nomor_txt">—</strong>
                                                            <input type="hidden" id="edit_disp_po_nomor">
                                                        </div>
                                                        <div class="col-12 col-md-4 mt-2">
                                                            <span class="text-muted d-block">Mata Uang & Kurs:</span>
                                                            <strong class="text-body font-monospace" id="edit_disp_currency_txt">IDR (Kurs: 1.0000)</strong>
                                                            <input type="hidden" id="edit_disp_currency">
                                                            <input type="hidden" id="edit_disp_kurs">
                                                        </div>
                                                        <div class="col-12 col-md-8 mt-2">
                                                            <span class="text-muted d-block">NPWP Supplier:</span>
                                                            <span class="text-body font-monospace" id="edit_disp_npwp_txt">—</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-body small">Catatan / Keterangan Faktur</label>
                                                <textarea class="form-control form-control-sm" name="keterangan" id="edit_keterangan" rows="2" placeholder="Catatan opsional..."></textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- CARD 2: RINCIAN ITEM BARANG DARI GRN -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-box-seam-fill text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">2. Rincian Item Barang (Sesuai GRN)</h6>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-hover mb-0" id="tableEditItems">
                                                <thead class="bg-body-tertiary">
                                                    <tr>
                                                        <th width="4%" class="text-center">NO</th>
                                                        <th width="14%">KODE</th>
                                                        <th width="24%">NAMA BARANG</th>
                                                        <th width="18%">AKUN ALOKASI (COA)</th>
                                                        <th width="8%" class="text-center">QTY</th>
                                                        <th width="8%">SATUAN</th>
                                                        <th width="14%" class="text-end">HARGA SATUAN</th>
                                                        <th width="10%" class="text-end">DISKON</th>
                                                        <th width="14%" class="text-end">SUBTOTAL NET</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_items_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- CARD 3: BIAYA TAMBAHAN & ALOKASI COA -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-truck text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">3. Biaya Tambahan & Alokasi Akun (COA)</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3 shadow-sm" id="edit_btn_add_extra_cost">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Biaya
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-hover mb-0" id="tableEditExtraCosts">
                                                <thead class="bg-body-tertiary">
                                                    <tr>
                                                        <th width="24%">JENIS BIAYA</th>
                                                        <th width="18%" class="text-end">NOMINAL</th>
                                                        <th width="14%" class="text-center">MASUK HPP?</th>
                                                        <th width="26%">AKUN ALOKASI (COA)</th>
                                                        <th width="14%">KETERANGAN</th>
                                                        <th width="4%" class="text-center">AKSI</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_extra_costs_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT COLUMN: SUMMARY & FINANCIAL CALCULATION -->
                            <div class="col-12 col-xl-4">

                                <!-- RINGKASAN KALKULASI TAGIHAN -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm">
                                    <div class="erp-card-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calculator-fill text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">KALKULASI TAGIHAN FAKTUR</h6>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="calc-row">
                                            <span class="calc-label">Subtotal Barang (Gross)</span>
                                            <span class="calc-value text-body" id="edit_sum_subtotal">0,00</span>
                                        </div>
                                        <div class="calc-row">
                                            <span class="calc-label">Total Diskon Item/PO</span>
                                            <span class="calc-value text-danger" id="edit_sum_diskon">0,00</span>
                                        </div>
                                        <div class="calc-row">
                                            <span class="calc-label fw-bold">DPP (Net Barang)</span>
                                            <span class="calc-value text-primary" id="edit_sum_dpp">0,00</span>
                                        </div>
                                        <div class="calc-row">
                                            <span class="calc-label" id="edit_label_ppn">PPN Masukan (0%)</span>
                                            <span class="calc-value text-body" id="edit_sum_ppn">0,00</span>
                                        </div>
                                        <div class="calc-row">
                                            <span class="calc-label">Total Biaya Tambahan</span>
                                            <span class="calc-value text-body" id="edit_sum_biaya_lain">0,00</span>
                                        </div>
                                        <div class="calc-grand-total shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #d97706 100%);">
                                            <div class="label">Grand Total Tagihan</div>
                                            <div class="value" id="edit_sum_total_invoice">Rp 0,00</div>
                                            <div class="small mt-1 text-white-50 font-monospace" id="edit_sum_total_invoice_idr" style="display:none; font-size:0.75rem;"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RINGKASAN DOKUMEN -->
                                <div class="erp-card bg-body shadow-sm">
                                    <div class="erp-card-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-card-list text-secondary"></i>
                                            <h6 class="fw-bold mb-0 text-body" style="font-size:0.8rem; letter-spacing:0.04em;">RINGKASAN DOKUMEN</h6>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div style="font-size:0.8rem;">
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                <span class="text-muted">Jenis Barang</span>
                                                <span class="fw-bold text-body" id="edit_sb_total_barang">0</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                <span class="text-muted">Total Qty</span>
                                                <span class="fw-bold text-body font-monospace" id="edit_sb_total_qty">0</span>
                                            </div>
                                            <div class="py-2 border-bottom">
                                                <div class="text-muted mb-1">Supplier</div>
                                                <div class="fw-bold text-body text-truncate" id="edit_sb_supplier">—</div>
                                            </div>
                                            <div class="py-2 border-bottom">
                                                <div class="text-muted mb-1">Ref. PO</div>
                                                <div class="fw-semibold text-primary font-monospace" id="edit_sb_po">—</div>
                                            </div>
                                            <div class="py-2 border-bottom">
                                                <div class="text-muted mb-1">Ref. GRN</div>
                                                <div class="fw-semibold text-info font-monospace" id="edit_sb_grn">—</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="text-muted">Status Dokumen</span>
                                                <div id="edit_sb_status_badge"><span class="badge bg-warning text-dark">Draft</span></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-2">
                                                <span class="text-muted">Status Pembayaran</span>
                                                <div><span class="badge bg-secondary">Belum Dibayar</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div><!-- end p-4 -->

                </form>
            </div>
        </div>
    </div>
</div>
