<!-- ===== MODAL EDIT FAKTUR PEMBELIAN ===== -->
<div class="modal fade" id="modalEditInvoice" aria-labelledby="modalEditInvoiceLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">

            <!-- ── GRADIENT MODAL HEADER ── -->
            <div class="modal-header inv-modal-header border-0 px-3 px-md-4 py-2.5 py-md-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important; color: #fff;">
                <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0" style="min-width: 0; flex: 1 1 auto; overflow: hidden;">
                    <div class="p-2 rounded-3 shadow-sm flex-shrink-0" style="background:rgba(255,255,255,0.15); backdrop-filter:blur(6px); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-pencil-square fs-5 text-warning"></i>
                    </div>
                    <div style="min-width: 0; overflow: hidden;">
                        <div class="modal-title fs-6 fs-md-5 fw-bold text-truncate text-white" id="modalEditInvoiceLabel">Edit Faktur Pembelian</div>
                        <div class="text-subtitle text-truncate text-white-50" id="modalEditInvoiceSubtitle" style="font-size:0.72rem;">Purchase Invoice (AP) — Penyesuaian Data Tagihan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <span class="inv-status-pill draft shadow-sm" id="edit_badge_status_form" style="font-size:0.7rem; padding: 0.2rem 0.55rem;">
                        <i class="bi bi-pencil-fill text-warning" style="font-size:0.45rem;"></i> DRAFT
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- ── MODAL BODY ── -->
            <div class="modal-body p-0 bg-body-tertiary" style="overflow-x: hidden !important; max-width: 100%; min-width: 0; touch-action: pan-y;">
                <form id="formEditInvoice" action="" method="POST" enctype="multipart/form-data" autocomplete="off" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                    @csrf
                    <input type="hidden" name="invoice_id" id="edit_invoice_id" value="">
                    <input type="hidden" name="action_type" id="edit_action_type" value="draft">

                    <!-- ── MAIN FORM BODY ── -->
                    <div class="p-2.5 p-md-4" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">

                        <!-- CARD 1: INFORMASI DOKUMEN FAKTUR -->
                        <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
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
                                                <label class="form-label fw-semibold text-body small d-flex justify-content-between align-items-center">
                                                    <span>No. Faktur Internal</span>
                                                    <span class="text-muted extra-small fw-normal"><i class="bi bi-pencil-square me-1"></i>Bisa diedit</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-hash text-muted"></i></span>
                                                    <input type="text" class="form-control font-monospace fw-bold text-primary" id="edit_disp_nomor_faktur_internal" name="nomor_faktur" autocomplete="off">
                                                </div>
                                                <div class="form-text text-muted extra-small">Nomor identitas faktur internal pada sistem.</div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small required d-flex justify-content-between align-items-center">
                                                    <span>No. Faktur dari Supplier <span class="text-danger">*</span></span>
                                                    <span class="text-muted extra-small" id="edit_no_faktur_sup_char_count">0/100</span>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-receipt text-muted"></i></span>
                                                    <input type="text" class="form-control fw-bold" name="nomor_faktur_supplier" id="edit_nomor_faktur_supplier" required maxlength="100" placeholder="Contoh: INV-SUP-2026-0089" autocomplete="off">
                                                </div>
                                                <div class="form-text text-muted extra-small">Wajib diisi, maksimal 100 karakter.</div>
                                            </div>

                                            <!-- TANGGAL FAKTUR & JATUH TEMPO -->
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small required">Tanggal Faktur Tagihan</label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_faktur" id="edit_tanggal_faktur" required autocomplete="off">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-semibold text-body small required">Tanggal Jatuh Tempo</label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_jatuh_tempo" id="edit_tanggal_jatuh_tempo" required autocomplete="off">
                                            </div>

                                            <!-- SYARAT PEMBAYARAN, MATA UANG & KURS -->
                                            <div class="col-12 col-md-5">
                                                <label class="form-label fw-semibold text-body small">Syarat Pembayaran (Term)</label>
                                                <select class="form-select form-select-sm select2-termin" name="payment_term_id" id="edit_payment_term_id">
                                                    <option value="">Cash / Tanpa Term (0 Hari)</option>
                                                    @foreach($paymentTerms as $pt)
                                                    <option value="{{ $pt->id }}" data-hari="{{ $pt->jumlah_hari }}">{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} Hari)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label fw-semibold text-body small">Mata Uang</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-coin text-warning"></i></span>
                                                    <input type="text" class="form-control form-control-sm bg-body-tertiary font-monospace fw-bold text-body" id="edit_disp_currency" readonly value="IDR (Rupiah)">
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold text-body small d-flex justify-content-between align-items-center">
                                                    <span>Nilai Kurs (Rate)</span>
                                                    <small class="text-muted fw-normal" style="font-size:0.68rem;"><i class="bi bi-info-circle me-1"></i>Def. Barang Masuk</small>
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary font-monospace text-warning fw-bold" id="edit_kurs_symbol_addon">IDR</span>
                                                    <input type="text" class="form-control form-control-sm text-end fw-bold font-monospace bg-body-tertiary" id="edit_input_kurs" name="kurs" value="1" placeholder="1" readonly title="Kurs untuk mata uang Rupiah adalah 1 (hanya dapat diubah jika valas)" autocomplete="off">
                                                </div>
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
                                                            <input type="hidden" id="edit_ppn_coa_id" name="ppn_coa_id" value="{{ $defaultPpnCoaId ?? '' }}">
                                                        </div>
                                                        <div class="col-12 col-md-4 mt-2">
                                                            <span class="text-muted d-block">NPWP Supplier:</span>
                                                            <span class="text-body font-monospace" id="edit_disp_npwp_txt">—</span>
                                                        </div>
                                                        <div class="col-12 col-md-4 mt-2" id="edit_box_tender" style="display:none;">
                                                            <span class="text-muted d-block"><i class="bi bi-briefcase me-1 text-warning"></i>Proyek / Tender:</span>
                                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1" id="edit_disp_tender_txt">—</span>
                                                            <input type="hidden" name="tender_id" id="edit_tender_id">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-7">
                                                <label class="form-label fw-semibold text-body small d-flex justify-content-between align-items-center">
                                                    <span>Catatan / Keterangan Faktur</span>
                                                    <span class="text-muted extra-small" id="edit_keterangan_char_count">0/1000</span>
                                                </label>
                                                <textarea class="form-control form-control-sm" name="keterangan" id="edit_keterangan" rows="3" maxlength="1000" placeholder="Catatan opsional (maks. 1000 karakter)..." autocomplete="off"></textarea>
                                                <div class="form-text text-muted extra-small">Maksimal 1000 karakter.</div>
                                            </div>
                                            <div class="col-12 col-lg-5">
                                                <label class="form-label fw-semibold text-body small d-flex justify-content-between align-items-center">
                                                    <span><i class="bi bi-paperclip text-warning me-1"></i>Foto / Berkas Bukti Faktur</span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill" style="font-size:0.65rem;" title="Foto otomatis dikompres ke ukuran KB saat disimpan"><i class="bi bi-file-earmark-zip me-1"></i>Auto-kompres KB</span>
                                                </label>
                                                <input type="file" class="form-control form-control-sm" name="lampiran_faktur" id="edit_lampiran_faktur" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                                <div class="d-flex align-items-center justify-content-between mt-1 text-muted extra-small" style="font-size:0.68rem;">
                                                    <span>Format: JPG, PNG, WEBP, PDF (Maks 5 MB)</span>
                                                    <div id="edit_lampiran_existing_container" style="display:none;">
                                                        <a href="javascript:void(0)" id="edit_lampiran_existing_link" class="text-primary text-decoration-underline fw-medium btn-preview-lampiran" data-url="" data-title="Berkas Lama Faktur">
                                                            <i class="bi bi-eye me-1"></i>Lihat Berkas Lama
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- CARD 2: RINCIAN ITEM BARANG DARI GRN -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-box-seam-fill text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">2. Rincian Item Barang (Sesuai GRN)</h6>
                                        </div>
                                    </div>
                                    <!-- Mobile Table Swipe Hint -->
                                    <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-tertiary border-bottom d-md-none text-muted" style="font-size: 0.72rem;">
                                        <span><i class="bi bi-arrows-expand me-1 text-primary"></i>Geser tabel ke samping untuk melihat & edit kolom</span>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">12 Kolom</span>
                                    </div>
                                    <div class="card-body p-0" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                                        <div class="table-responsive inv-table-responsive" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                                            <table class="table align-middle table-hover mb-0 inv-modern-table" id="tableEditItems" style="min-width: 1550px;">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 45px;" class="text-center">#</th>
                                                        <th style="width: 110px;"><i class="bi bi-upc-scan me-1 opacity-75"></i> Kode</th>
                                                        <th style="min-width: 170px;"><i class="bi bi-box-seam me-1 opacity-75"></i> Nama Barang</th>
                                                        <th style="min-width: 220px;"><i class="bi bi-journal-bookmark me-1 opacity-75"></i> Akun Alokasi (COA)</th>
                                                        <th style="min-width: 110px;" class="text-center"><i class="bi bi-hash me-1 opacity-75"></i> Qty</th>
                                                        <th style="width: 85px;" class="text-center">Satuan</th>
                                                        <th style="min-width: 135px;" class="text-end"><i class="bi bi-tag me-1 opacity-75"></i> Harga Satuan</th>
                                                        <th style="min-width: 105px;" class="text-end">Diskon</th>
                                                        <th style="min-width: 145px;" class="text-center">PPN</th>
                                                        <th style="min-width: 135px;" class="text-end"><i class="bi bi-wallet2 me-1 opacity-75"></i> Subtotal Net</th>
                                                        <th style="min-width: 140px;" class="text-end text-primary"><i class="bi bi-truck me-1 opacity-75"></i> Alokasi Biaya</th>
                                                        <th style="min-width: 150px;" class="text-end text-success"><i class="bi bi-graph-up-arrow me-1 opacity-75"></i> HPP Akhir / Unit</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_items_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- CARD 3: BIAYA TAMBAHAN & ALOKASI COA -->
                                <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-truck text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">3. Biaya Tambahan & Alokasi Akun (COA)</h6>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3 shadow-sm" id="edit_btn_add_extra_cost">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Biaya
                                        </button>
                                    </div>
                                    <!-- Mobile Table Swipe Hint -->
                                    <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-tertiary border-bottom d-md-none text-muted" style="font-size: 0.72rem;">
                                        <span><i class="bi bi-arrows-expand me-1 text-primary"></i>Geser tabel ke samping untuk melihat data biaya</span>
                                    </div>
                                    <div class="card-body p-0" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                                        <div class="table-responsive inv-table-responsive" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                                            <table class="table align-middle table-hover mb-0 inv-modern-table" id="tableEditExtraCosts" style="min-width: 950px;">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width: 180px;">Jenis Biaya</th>
                                                        <th style="min-width: 140px;" class="text-end">Nominal</th>
                                                        <th style="width: 120px;" class="text-center">Masuk HPP?</th>
                                                        <th style="min-width: 220px;">Akun Alokasi (COA)</th>
                                                        <th style="min-width: 150px;">Keterangan</th>
                                                        <th style="width: 50px;" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="edit_extra_costs_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                        <!-- ── 4. BOTTOM SECTION: RINGKASAN DOKUMEN & KALKULASI TAGIHAN ── -->
                        <div class="row g-3 g-md-4 mt-1">

                            <!-- RINGKASAN DOKUMEN (KIRI) -->
                            <div class="col-12 col-xl-5">
                                <div class="erp-card bg-body shadow-sm h-100">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-card-checklist text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body" style="font-size:0.85rem; letter-spacing:0.03em;">RINGKASAN DOKUMEN</h6>
                                        </div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace" style="font-size:0.7rem;">
                                            Audit Info
                                        </span>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <div class="row g-2" style="font-size:0.82rem;">
                                            <div class="col-6">
                                                <div class="p-2 rounded-3 border bg-body-tertiary">
                                                    <div class="text-muted extra-small">Jenis Barang</div>
                                                    <div class="fs-5 fw-bold text-body" id="edit_sb_total_barang">0</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 rounded-3 border bg-body-tertiary">
                                                    <div class="text-muted extra-small">Total Qty Diterima</div>
                                                    <div class="fs-5 fw-bold text-body font-monospace" id="edit_sb_total_qty">0</div>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">Supplier:</span>
                                                    <span class="fw-bold text-body text-truncate text-end" style="max-width:250px;" id="edit_sb_supplier">—</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">Ref. PO:</span>
                                                    <span class="fw-semibold text-primary font-monospace" id="edit_sb_po">—</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">Ref. GRN:</span>
                                                    <span class="fw-semibold text-info font-monospace" id="edit_sb_grn">—</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                    <span class="text-muted">Status Dokumen:</span>
                                                    <div id="edit_sb_status_badge"><span class="badge bg-warning text-dark">Draft</span></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-2">
                                                    <span class="text-muted">Status Pembayaran:</span>
                                                    <div><span class="badge bg-secondary">Belum Dibayar</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KALKULASI TAGIHAN FAKTUR (KANAN) -->
                            <div class="col-12 col-xl-7">
                                <div class="erp-card bg-body shadow-sm h-100">
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calculator-fill text-warning"></i>
                                            <h6 class="fw-bold mb-0 text-body">KALKULASI TAGIHAN FAKTUR</h6>
                                        </div>
                                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 font-monospace" style="font-size:0.72rem;">
                                            <i class="bi bi-cash-stack me-1"></i>Rekap Finansial
                                        </span>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <div class="row g-2 mb-3">
                                            <div class="col-12 col-sm-6">
                                                <div class="calc-row">
                                                    <span class="calc-label">Subtotal Barang (Gross)</span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-body" id="edit_sum_subtotal">0,00</span>
                                                        <small class="text-muted font-monospace d-block extra-small" id="edit_sum_subtotal_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                                <div class="calc-row">
                                                    <span class="calc-label">Total Diskon Item/PO</span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-danger" id="edit_sum_diskon">0,00</span>
                                                        <small class="text-danger font-monospace d-block extra-small" id="edit_sum_diskon_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                                <div class="calc-row">
                                                    <span class="calc-label fw-bold">DPP (Net Barang)</span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-primary" id="edit_sum_dpp">0,00</span>
                                                        <small class="text-primary font-monospace d-block extra-small" id="edit_sum_dpp_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <div class="calc-row">
                                                    <span class="calc-label" id="edit_label_ppn">Total PPN Masukan (Item)</span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-body" id="edit_sum_ppn">0,00</span>
                                                        <small class="text-muted font-monospace d-block extra-small" id="edit_sum_ppn_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                                <div class="calc-row">
                                                    <span class="calc-label">Total Biaya Tambahan</span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-body" id="edit_sum_biaya_lain">0,00</span>
                                                        <small class="text-muted font-monospace d-block extra-small" id="edit_sum_biaya_lain_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                                <div class="calc-row">
                                                    <span class="calc-label">Potongan PPh <small class="text-muted" id="edit_label_pph_tarif"></small></span>
                                                    <div class="text-end">
                                                        <span class="calc-value text-warning" id="edit_sum_pph">0,00</span>
                                                        <small class="text-warning font-monospace d-block extra-small" id="edit_sum_pph_idr" style="display:none; font-size:0.7rem;"></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PANEL POTONGAN PPh -->
                                        <div class="inv-pph-panel mb-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-percent text-warning"></i>
                                                <span class="fw-bold small text-body">Potongan PPh (Withholding Tax)</span>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill ms-auto" style="font-size:0.65rem;">Mengurangi Utang Supplier</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small text-muted mb-1">Jenis PPh</label>
                                                    <select class="form-select form-select-sm select-pph-pajak" id="edit_pph_pajak_id" name="pph_pajak_id">
                                                        <option value="" data-tarif="0">Tanpa PPh</option>
                                                        @foreach($pajaks as $pj)
                                                            <option value="{{ $pj->id }}" data-tarif="{{ (float) $pj->tarif }}">{{ $pj->nama_pajak }} ({{ rtrim(rtrim(number_format($pj->tarif, 2, ',', '.'), '0'), ',') }}%)</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="tarif_pph" id="edit_tarif_pph" value="0">
                                                </div>
                                                <div class="col-12 col-md-3">
                                                    <label class="form-label small text-muted mb-1">Nominal PPh</label>
                                                    <div class="input-group input-group-sm inv-price-group">
                                                        <span class="input-group-text currency-symbol-addon font-monospace">Rp</span>
                                                        <input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-pph-nominal bg-body-tertiary" id="edit_pph_nominal" name="pph_nominal" value="0,00" disabled readonly autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-5">
                                                    <label class="form-label small text-muted mb-1">Akun Utang PPh (COA)</label>
                                                    <select class="form-select form-select-sm select-pph-coa" id="edit_pph_coa_id" name="pph_coa_id" disabled>
                                                        <option value="">-- Pilih Akun PPh --</option>
                                                        @foreach($coas as $c)
                                                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PANEL UANG MUKA / DOWN PAYMENT (DP) -->
                                        <div class="inv-pph-panel mb-3 border-success-subtle bg-success bg-opacity-10">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-wallet2 text-success"></i>
                                                <span class="fw-bold small text-body">Uang Muka Pembelian (Down Payment / DP)</span>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill ms-auto" style="font-size:0.65rem;">
                                                    <i class="bi bi-dash-circle me-1"></i>Mengurangi Tagihan Hutang
                                                </span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small text-muted mb-1">Nominal Uang Muka (DP)</label>
                                                    <div class="input-group input-group-sm inv-price-group">
                                                        <span class="input-group-text currency-symbol-addon font-monospace text-success fw-bold">Rp</span>
                                                        <input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format text-success" id="edit_uang_muka" name="uang_muka" value="0,00" placeholder="0,00" autocomplete="off">
                                                    </div>
                                                    <div id="edit_uang_muka_error_msg" class="text-danger extra-small mt-1 fw-bold" style="display:none; font-size:0.7rem;">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><span>Uang Muka tidak boleh melebihi Total Tagihan!</span>
                                                    </div>
                                                    <div id="edit_uang_muka_partial_hint" class="small text-primary font-monospace mt-1" style="display:none; font-size:0.7rem;"></div>
                                                    <div class="extra-small text-muted mt-1" style="font-size:0.66rem;">Otomatis dari DP PO (jika ada) atau input manual</div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small text-muted mb-1">
                                                        <i class="bi bi-bank text-primary me-1"></i>Kas / Bank Pengeluaran DP <span class="text-danger" id="edit_kas_bank_required_star" style="display:none;">*</span>
                                                    </label>
                                                    <select class="form-select form-select-sm select2-kas-bank" id="edit_kas_bank_id" name="kas_bank_id">
                                                        <option value="">-- Pilih Kas / Bank --</option>
                                                        @if(isset($kasBanks) && count($kasBanks) > 0)
                                                            @foreach($kasBanks as $kb)
                                                                <option value="{{ $kb->id }}" data-saldo="{{ (float)$kb->saldo_berjalan }}">
                                                                    {{ $kb->nama_akun }} ({{ $kb->nama_bank ?: 'Kas' }} - Rp {{ number_format($kb->saldo_berjalan, 2, ',', '.') }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <div id="edit_kas_bank_error_msg" class="text-danger extra-small mt-1 fw-bold" style="display:none; font-size:0.7rem;">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Kas / Bank wajib dipilih jika ada Uang Muka (DP)!
                                                    </div>
                                                    <div id="edit_kas_bank_saldo_error_msg" class="text-danger extra-small mt-1 fw-bold" style="display:none; font-size:0.7rem;">
                                                        <i class="bi bi-slash-circle-fill me-1"></i><span id="edit_kas_bank_saldo_error_text">Saldo Kas/Bank tidak mencukupi!</span>
                                                    </div>
                                                    <div class="extra-small text-muted mt-1" style="font-size:0.66rem;">Saldo kas/bank akan langsung terpotong</div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small text-muted mb-1">Akun Uang Muka (COA)</label>
                                                    <select class="form-select form-select-sm select2-uang-muka-coa" id="edit_uang_muka_coa_id" name="uang_muka_coa_id">
                                                        <option value="">-- Pilih Akun Uang Muka --</option>
                                                        @foreach($coas as $c)
                                                            <option value="{{ $c->id }}">
                                                                {{ $c->code }} - {{ $c->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="extra-small text-muted mt-1" style="font-size:0.66rem;">Akun perantara DP Supplier</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="calc-grand-total shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #d97706 100%);">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                                <div>
                                                    <div class="label" id="edit_label_total_tagihan">Grand Total Tagihan</div>
                                                    <div class="value fs-4 fw-bold" id="edit_sum_total_invoice">Rp 0,00</div>
                                                    <div id="edit_box_sisa_hutang" class="mt-2 pt-2 border-top border-white border-opacity-25" style="display:none;">
                                                        <div class="label text-warning-emphasis fw-bold" style="font-size:0.75rem;"><i class="bi bi-exclamation-circle me-1"></i>Sisa Tagihan Hutang (Net AP)</div>
                                                        <div class="fs-3 fw-bolder text-warning" id="edit_sum_sisa_hutang">Rp 0,00</div>
                                                    </div>
                                                </div>
                                                <div class="text-end p-2 px-3 rounded-3" id="edit_box_total_idr" style="display:none; background: rgba(0, 0, 0, 0.22); border: 1px solid rgba(255, 255, 255, 0.15);">
                                                    <div class="label text-warning-emphasis fw-bold" style="font-size:0.7rem; letter-spacing:0.5px;"><i class="bi bi-cash-stack me-1"></i>TOTAL EKUIVALEN (IDR)</div>
                                                    <div class="fs-4 fw-bold text-warning font-monospace" id="edit_sum_total_invoice_idr">Rp 0,00</div>
                                                    <div class="extra-small text-white-50 font-monospace" id="edit_sum_kurs_info" style="font-size:0.68rem;">Kurs: 1,00</div>
                                                    <div id="edit_box_sisa_hutang_idr" class="mt-1 pt-1 border-top border-white border-opacity-10" style="display:none;">
                                                        <span class="extra-small text-white-50">Sisa Hutang: </span>
                                                        <strong class="text-warning font-monospace" id="edit_sum_sisa_hutang_idr">Rp 0,00</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div><!-- end p-4 -->
                </form>
            </div>

            <!-- ── MODAL FOOTER ── -->
            <div class="modal-footer border-top py-2.5 py-md-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; flex-shrink: 0;">
                <div class="text-muted small d-none d-lg-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-warning"></i>
                    <span>Perubahan dapat disimpan sebagai Draft atau langsung diajukan ke Atasan via WhatsApp untuk persetujuan (ACC).</span>
                </div>
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 ms-sm-auto w-100 w-sm-auto">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 order-2 order-sm-1" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold order-1 order-sm-2" id="edit_btn_save_draft_invoice">
                        <i class="bi bi-file-earmark-code me-1"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm order-0 order-sm-3" id="edit_btn_submit_post_invoice">
                        <i class="bi bi-send-check me-1"></i> Simpan &amp; Ajukan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
