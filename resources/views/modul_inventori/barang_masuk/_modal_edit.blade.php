<!-- MODAL EDIT RECEIPT (DRAFT) -->
<div class="modal fade" id="modalEditGR" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalEditGRLabel">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl gr-modal-dialog">
        <div class="modal-content border-0 shadow-lg gr-modal-content">
            <div class="modal-header border-0 py-3 px-3 px-md-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24;">
                        <i class="bi bi-pencil-square fs-4 text-warning"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalEditGRLabel" style="font-size: 1.15rem; letter-spacing: -0.2px;">Perbarui Penerimaan Barang</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 0.70rem; font-weight: 600;">Draft Mode</span>
                        </div>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 0.78rem;">Penyesuaian kuantitas fisik barang dan penempatan lokasi rak</small>
                    </div>
                </div>
                <button type="button" class="gr-modal-header-close flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Tutup" title="Tutup Modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="formEditGR" method="POST">
                <input type="hidden" name="edit_gr_id" id="edit_gr_id" value="">
                <input type="hidden" name="edit_is_draft" id="edit_is_draft" value="1">
                <div class="modal-body p-3 p-md-4">
                    
                    <!-- HEADER TRANSACTION INFO -->
                    <div class="card border-0 bg-light bg-opacity-50 rounded-3 p-3 mb-4 shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold">Nomor Transaksi</label>
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" id="edit_auto_number_toggle" checked disabled>
                                    <label class="form-check-label small text-muted" for="edit_auto_number_toggle" id="edit_auto_number_label">Auto Number</label>
                                </div>
                                <input type="text" name="edit_nomor_gr" id="edit_nomor_gr_input" class="form-control form-control-sm rounded-pill d-none" placeholder="Nomor GR">
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fs-7 d-block text-start w-100" id="edit_auto_number_badge">
                                    <i class="bi bi-magic me-1"></i> Generated Otomatis
                                </span>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="edit_tanggal_gr">Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm rounded-pill" id="edit_tanggal_gr" name="edit_tanggal_gr" required>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="edit_po_display">Pilih Purchase Order (PO) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="edit_po_id" id="edit_po_id" required>
                                    <input type="text" id="edit_po_display" class="form-control form-control-sm form-control-custom" placeholder="Cari & pilih PO asal..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;" onclick="window.openPOPickerModal()" required>
                                    <button type="button" class="btn btn-primary px-3" id="btnOpenEditPOPicker" title="Cari PO" onclick="window.openPOPickerModal()" style="border-radius: 0 50px 50px 0; height: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; margin: 0 !important;">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

@php
    $activeBranchIdEdit = session('active_branch_id');
    $isBranchActiveEdit = !empty($activeBranchIdEdit);
    $activeScopeNameEdit = $isBranchActiveEdit 
        ? (session('active_branch_name') ?: (\App\Models\Branch::find($activeBranchIdEdit)?->nama_cabang ?? 'Cabang Aktif'))
        : (session('active_company_name') ?: (auth()->user()?->company?->nama_perusahaan ?? 'PT Aktif'));
@endphp
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="edit_gudang_display">Gudang Penerima <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="edit_gudang_id" id="edit_gudang_id" required>
                                    <input type="text" id="edit_gudang_display" class="form-control form-control-sm form-control-custom" placeholder="Cari & pilih gudang ({{ $activeScopeNameEdit }})..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;" onclick="window.openGudangPickerModal()" required>
                                    <button type="button" class="btn btn-warning px-3 text-white" id="btnOpenEditGudangPicker" title="Cari Gudang" onclick="window.openGudangPickerModal()" style="border-radius: 0 50px 50px 0; height: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; margin: 0 !important;">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="edit_nomor_surat_jalan">Nomor Surat Jalan Supplier</label>
                                <input type="text" class="form-control form-control-sm rounded-pill" id="edit_nomor_surat_jalan" name="edit_nomor_surat_jalan" placeholder="Contoh: SJ-9921...">
                            </div>

                            <div class="col-md-6 col-lg-3" id="edit_kurs_gr_wrapper" style="display: none;">
                                <label class="form-label text-secondary small fw-bold" for="edit_kurs_gr">
                                    Kurs Saat Penerimaan <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal" style="font-size:.75rem;" id="edit_kurs_gr_currency_hint"></span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" style="border-radius: 50px 0 0 50px; font-size: .8rem;" id="edit_kurs_gr_symbol">IDR</span>
                                    <input type="text" id="edit_kurs_gr" class="form-control form-control-sm"
                                        placeholder="Contoh: 15.750,00"
                                        style="border-radius: 0 50px 50px 0;"
                                        inputmode="decimal" autocomplete="off">
                                    <input type="hidden" name="edit_kurs" id="edit_kurs_gr_raw">
                                </div>
                                <small class="text-muted" style="font-size:.72rem;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Kurs PO: <span id="edit_kurs_po_reference" class="fw-semibold text-primary">-</span>
                                </small>
                            </div>

                            <div class="col-md-6 col-lg-9">
                                <label class="form-label text-secondary small fw-bold" for="edit_keterangan">Catatan / Keterangan</label>
                                <input type="text" class="form-control form-control-sm rounded-pill" id="edit_keterangan" name="edit_keterangan" placeholder="Catatan penerimaan barang...">
                            </div>
                        </div>
                    </div>

                    <!-- PO INFO CARD -->
                    <div id="edit_po_info_card_wrapper" class="d-none mb-4">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="bi bi-info-circle text-primary me-2"></i> Rincian Purchase Order (PO) Asal (Edit)
                                </h6>
                                <div class="row g-3">
                                    <!-- Column 1: PO & Supplier info -->
                                    <div class="col-md-4 border-end">
                                        <div class="d-flex flex-column gap-2 small">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nomor PO</span>
                                                <strong id="edit_card_nomor_po" class="text-primary" style="font-size: 0.85rem;">-</strong>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Tanggal PO</span>
                                                <span id="edit_card_tanggal_po" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nama Tender / Proyek</span>
                                                <span id="edit_card_tender_name" class="fw-bold text-info" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Supplier</span>
                                                <span id="edit_card_supplier" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PIC Supplier</span>
                                                <span id="edit_card_pic_supplier" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">No. Telp Supplier</span>
                                                <span id="edit_card_telepon_supplier" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Column 2: Logistics & Status -->
                                    <div class="col-md-4 border-end">
                                        <div class="d-flex flex-column gap-2 small">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Gudang Tujuan</span>
                                                <span id="edit_card_gudang_tujuan" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Estimasi Kedatangan</span>
                                                <span id="edit_card_eta" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Keterlambatan</span>
                                                <span id="edit_card_status_keterlambatan" class="fw-bold" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Status PO</span>
                                                <span id="edit_card_status_po" class="fw-bold" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PPN / Pajak PO</span>
                                                <span id="edit_card_pajak_info" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center d-none" id="edit_card_pph_row">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PPh PO</span>
                                                <span id="edit_card_pph_info" class="fw-semibold text-warning" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Uang Muka (DP)</span>
                                                <span id="edit_card_dp_info" class="fw-bold text-warning" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Biaya Lain PO</span>
                                                <span id="edit_card_biaya_lain_info" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center" id="edit_card_kurs_row">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nilai Kurs</span>
                                                <strong id="edit_card_kurs" class="text-dark" style="font-size: 0.8rem;">-</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Column 3: Totals & Progress -->
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="row g-2 text-center bg-light rounded p-2 mb-1">
                                                <div class="col-4 border-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Total Item</small>
                                                    <span id="edit_card_total_item" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                                </div>
                                                <div class="col-4 border-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Total Qty</small>
                                                    <span id="edit_card_total_qty" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Grand Total</small>
                                                    <span id="edit_card_grand_total" class="fw-bold text-success" style="font-size: 0.8rem;">-</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-muted small fw-bold" style="font-size: 0.72rem; text-transform: uppercase;">Progres Penerimaan</span>
                                                    <span class="fw-extrabold text-primary small" id="edit_card_progress_text">0%</span>
                                                </div>
                                                <div class="progress rounded-pill mb-1" style="height: 8px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary rounded-pill" id="edit_card_progress_bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted d-block text-center" style="font-size: 0.72rem;" id="edit_card_progress_ratio">Sudah diterima: 0 / 0</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITEMS SECTION -->
                    <div id="edit_items_placeholder" class="text-center py-5 text-muted border border-dashed rounded-3">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Silakan pilih Purchase Order (PO) di atas untuk memuat daftar barang.
                    </div>

                    <div id="edit_items_card" class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 d-none">
                        <div class="card-header bg-light py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0;">
                                    <i class="bi bi-list-check fs-5"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">Rincian Barang Diterima (Edit)</h6>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1.5 fw-normal small">
                                <i class="bi bi-pencil-square me-1"></i>Isi Qty Diterima &amp; Lokasi Rak
                            </span>
                        </div>

                        <!-- Swipe Hint Banner for Mobile -->
                        <div class="d-md-none gr-scroll-hint-bar">
                            <span><i class="bi bi-arrow-left-right me-1.5"></i>Geser tabel ke kanan untuk edit Qty &amp; Rak</span>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.65rem;">Bisa Digeser</span>
                        </div>

                        <div class="table-responsive gr-table-scroll-wrapper">
                            <table class="table table-hover align-middle mb-0 gr-scroll-table" id="edit_gr_details_table" style="min-width: 1120px;">
                                <thead class="table-light">
                                    <tr class="text-secondary small fw-bold">
                                        <th class="sticky-col-header" style="min-width: 240px; width: 240px;">Nama Barang</th>
                                        <th style="min-width: 130px;" class="text-center">Lokasi Rak</th>
                                        <th style="min-width: 80px; white-space: nowrap;" class="text-center">Qty PO</th>
                                        <th style="min-width: 90px; white-space: nowrap;" class="text-center" title="Qty yang sudah pernah diterima dari PO ini sebelumnya">Qty Sdh Diterima</th>
                                        <th style="min-width: 80px; white-space: nowrap;" class="text-center">Qty Sisa</th>
                                        <th style="min-width: 110px; white-space: nowrap;" class="text-end">Harga Satuan</th>
                                        <th style="min-width: 95px; white-space: nowrap;" class="text-end">Diskon Item</th>
                                        <th style="min-width: 90px; white-space: nowrap;" class="text-center">PPN</th>
                                        <th style="min-width: 105px;" class="text-center">Qty Diterima</th>
                                        <th style="min-width: 95px;" class="text-center">Qty Reject</th>
                                        <th style="min-width: 105px;" class="text-center">Satuan</th>
                                        <th style="min-width: 120px; white-space: nowrap;" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="edit_gr_details_tbody">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>

                        <!-- FOOTER SUMMARY & CALCULATIONS -->
                        <div class="card-footer border-top p-3 p-md-4" id="edit_gr_footer_summary">
                            <!-- MOBILE CINEMATIC GRAND TOTAL HERO (Visible on mobile < 992px) -->
                            <div class="d-lg-none mb-3">
                                <div class="gr-summary-hero">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-uppercase text-white-50 fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.8px;">Grand Total Penerimaan</span>
                                            <div class="small text-info mt-0.5">
                                                <i class="bi bi-box-seam me-1"></i><span id="edit_summary_total_jenis_mobile">0</span> jenis barang
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span id="edit_summary_grand_total" class="fs-4 fw-extrabold" style="color: #34d399 !important;">Rp 0,00</span>
                                            <span id="edit_summary_grand_total_idr" class="small text-white-50 d-block" style="font-size: 0.72rem;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- METRICS GRID -->
                            <div class="row g-3 align-items-center">
                                <!-- LEFT BENTO METRICS -->
                                <div class="col-xl-9 col-lg-8 col-12">
                                    <div class="row g-2 text-center text-md-start">
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Diterima / Reject</small>
                                                <div>
                                                    <span id="edit_summary_qty_diterima" class="fs-6 fw-bold text-primary">0</span>
                                                    <span class="text-muted small">/</span>
                                                    <span id="edit_summary_qty_reject" class="fs-6 fw-bold text-danger">0</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Subtotal Barang</small>
                                                <span id="edit_summary_nilai_barang" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                                <span id="edit_summary_nilai_barang_idr" class="small text-muted d-block" style="font-size: 0.68rem;"></span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Total Diskon</small>
                                                <span id="edit_summary_diskon_val" class="fw-bold text-danger" style="font-size: 0.85rem;">- Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">PPN / Pajak</small>
                                                <span id="edit_summary_ppn_val" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 d-none" id="edit_summary_pph_tile">
                                            <div class="gr-metric-tile h-100" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.04);">
                                                <small class="text-warning d-block mb-1 fw-semibold" style="font-size: 0.68rem;" id="edit_summary_pph_title">Potongan PPh</small>
                                                <span id="edit_summary_pph_val" class="fw-bold text-warning" style="font-size: 0.85rem;">- Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Biaya Lain-lain</small>
                                                <span id="edit_summary_biaya_lain_val" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Sebelum DP</small>
                                                <span id="edit_summary_total_sebelum_dp_val" class="fw-bold text-info" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Uang Muka (DP)</small>
                                                <span id="edit_summary_dp_val" class="fw-bold text-warning" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT GRAND TOTAL (Visible on desktop >= 992px) -->
                                <div class="col-xl-3 col-lg-4 d-none d-lg-block text-end ms-auto">
                                    <div class="gr-summary-hero text-end w-100">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                            <small class="text-white-50 fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Grand Total</small>
                                            <span id="edit_summary_grand_total_desktop" class="fs-5 fw-extrabold" style="color: #34d399;">Rp 0,00</span>
                                        </div>
                                        <span id="edit_summary_grand_total_idr_desktop" class="small text-white-50 d-block" style="font-size: 0.72rem;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 d-flex align-items-center gap-1.5" data-bs-dismiss="modal" style="font-weight: 600; font-size: 0.86rem;">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-edit-action-draft rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" id="btn_update_draft" style="font-size: 0.86rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #000; border: none;">
                            <i class="bi bi-file-earmark-code"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn btn-edit-action-posting rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-1.5 shadow-sm text-white" id="btn_update_gr" style="font-size: 0.88rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;">
                            <i class="bi bi-check-circle-fill"></i> Simpan &amp; Posting
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {

        if (typeof window.escapeHtml !== 'function') {
            window.escapeHtml = function (str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };
        }
        let escapeHtml = window.escapeHtml;

        // Helper format quantity integer
        let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : n.toString(); };
        let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

        // ===== EDIT PO DETAILS LOADER =====
        window.loadEditPODetails = function (poId, grDetailsMap, callback, preloadedPoDetails, preloadedRaks) {
            if (!poId) {
                $('#edit_items_placeholder').removeClass('d-none').show();
                $('#edit_items_card').addClass('d-none').hide();
                $('#edit_po_info_card_wrapper').addClass('d-none').hide();
                $('#edit_gr_details_tbody').empty();
                if (typeof callback === 'function') callback();
                return;
            }

            $('#edit_items_placeholder').addClass('d-none').hide();
            $('#edit_items_card').removeClass('d-none').show();
            $('#edit_po_info_card_wrapper').removeClass('d-none').show();

            let renderPo = function (res) {
                if (!res || !res.po || !res.items) {
                    $('#edit_gr_details_tbody').html('<tr><td colspan="10" class="text-center text-danger py-3">Gagal memuat rincian PO.</td></tr>');
                    return;
                }

                $('#edit_card_nomor_po').text(res.po.nomor_po);
                window.currentPoKurs = parseFloat(res.po.kurs) || 1;
                window.currentCurrencySymbol = res.po.currency_symbol || 'Rp';
                window.currentPoPajakTarif = parseFloat(res.po.pajak_tarif) || 0;
                window.currentPoPajakNama = res.po.pajak_nama || 'PPN';
                window.currentPoBiayaLain = parseFloat(res.po.biaya_lain) || 0;
                window.currentPoDeskripsiBiayaLain = res.po.deskripsi_biaya_lain || 'Biaya Lain';
                window.currentPoDiskonPersen = parseFloat(res.po.diskon_persen) || 0;
                window.currentPoTotalDiskon = parseFloat(res.po.total_diskon) || 0;
                window.currentPoSubtotal = parseFloat(res.po.subtotal) || 0;
                window.currentPoDp = parseFloat(res.po.down_payment) || 0;
                window.currentPoTotalPajak = parseFloat(res.po.total_pajak) || 0;
                window.currentPoPphTarif = parseFloat(res.po.pph_tarif) || 0;
                window.currentPoPphNominal = parseFloat(res.po.pph_nominal) || parseFloat(res.po.total_pph) || 0;
                window.currentPoPphNama = res.po.pph_nama || 'PPh';
                window.currentPoGrandTotal = parseFloat(res.po.grand_total) || 0;

                $('#edit_card_supplier').text(res.po.supplier_name);
                $('#edit_card_pic_supplier').text(res.po.pic_supplier || '-');
                $('#edit_card_telepon_supplier').text(res.po.telepon_supplier || '-');
                $('#edit_card_tanggal_po').text(res.po.tanggal_po);
                $('#edit_card_tender_name').text(res.po.tender_name || '-');
                $('#edit_card_eta').text(res.po.eta || '-');

                let lateHtml = res.po.status_keterlambatan === 'Terlambat'
                    ? '<span class="badge bg-danger">Terlambat</span>'
                    : '<span class="badge bg-success">Ontime</span>';
                $('#edit_card_status_keterlambatan').html(lateHtml);

                let statusPOHtml = res.po.status_po === 'Selesai'
                    ? '<span class="badge bg-success">Selesai</span>'
                    : (res.po.status_po === 'Sebagian (Partial)'
                        ? '<span class="badge bg-warning text-white">Sebagian (Partial)</span>'
                        : '<span class="badge bg-secondary">Belum Diterima</span>');
                $('#edit_card_status_po').html(statusPOHtml);

                let dpVal = parseFloat(res.po.down_payment || 0);
                $('#edit_card_dp_info').text(dpVal > 0 ? `${res.po.currency_symbol} ${fmt(dpVal)}` : 'Rp 0,00');

                let taxRateVal = parseFloat(res.po.pajak_tarif || 0);
                let taxNameVal = res.po.pajak_nama || 'PPN';
                $('#edit_card_pajak_info').text(taxRateVal > 0 ? `${taxNameVal} (${taxRateVal}%)` : 'Tanpa PPN');

                if (window.currentPoPphTarif > 0 || window.currentPoPphNominal > 0) {
                    let pphText = `${window.currentPoPphNama}`;
                    if (window.currentPoPphTarif > 0) {
                        pphText += ` (${window.currentPoPphTarif}%)`;
                    }
                    if (window.currentPoPphNominal > 0) {
                        pphText += ` - ${window.currentCurrencySymbol} ${fmt(window.currentPoPphNominal)}`;
                    }
                    $('#edit_card_pph_info').text(pphText);
                    $('#edit_card_pph_row').removeClass('d-none');
                } else {
                    $('#edit_card_pph_row').addClass('d-none');
                    $('#edit_card_pph_info').text('-');
                }

                let biayaLainVal = parseFloat(res.po.biaya_lain || 0);
                let deskBiayaLainVal = res.po.deskripsi_biaya_lain || 'Biaya Lain';
                $('#edit_card_biaya_lain_info').text(biayaLainVal > 0 ? `${res.po.currency_symbol} ${fmt(biayaLainVal)} (${deskBiayaLainVal})` : '-');

                let effectiveKursInit = parseFloat($('#edit_kurs_gr_raw').val()) || (typeof window.parseKursID === 'function' ? window.parseKursID($('#edit_kurs_gr').val()) : 0) || parseFloat(res.po.kurs) || 1;
                if (res.po.currency_symbol === 'Rp') {
                    $('#edit_card_kurs_row').addClass('d-none');
                } else {
                    $('#edit_card_kurs_row').removeClass('d-none');
                    if (Math.abs(effectiveKursInit - parseFloat(res.po.kurs)) > 0.0001) {
                        let kursPoFmt = fmt(res.po.kurs);
                        let kursGrFmt = fmt(effectiveKursInit);
                        $('#edit_card_kurs').html(`<span class="text-primary fw-bold">${kursGrFmt}</span> <small class="text-muted" style="font-size:0.75rem;">(PO: ${kursPoFmt})</small>`);
                    } else {
                        $('#edit_card_kurs').text(fmt(res.po.kurs));
                    }
                }

                $('#edit_card_total_item').text(res.po.total_item);
                $('#edit_card_total_qty').text(fmtQ(res.po.total_qty));
                $('#edit_card_grand_total').text(res.po.currency_symbol + ' ' + fmt(res.po.grand_total));

                let executeRender = function () {
                    let html = '';
                    res.items.forEach(function (item) {
                        let fotoUrl = item.foto_path ? '/storage/' + item.foto_path : '';
                        let isEditingExistingGr = (grDetailsMap !== null && grDetailsMap !== undefined);
                        let existingDetail = grDetailsMap ? grDetailsMap[item.po_detail_id] : null;

                        let existingQtyInThisGr = existingDetail ? parseFloat(existingDetail.jumlah || 0) : 0;
                        let existingRejectInThisGr = existingDetail ? parseFloat(existingDetail.jumlah_reject || 0) : 0;
                        let existingSatuanId = existingDetail && existingDetail.satuan_id ? existingDetail.satuan_id : item.default_satuan_id;
                        let existingFaktor = existingDetail && parseFloat(existingDetail.faktor_pengali) > 0 ? parseFloat(existingDetail.faktor_pengali) : (item.po_faktor_pengali || 1);

                        let poFaktor = parseFloat(item.po_faktor_pengali || 1);
                        let poHargaSatuan = parseFloat(item.harga_satuan || 0);
                        let currentUnitPrice = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * existingFaktor) : poHargaSatuan;

                        let qtySisaDasar = parseFloat(item.qty_sisa_dasar || 0);
                        let existingQtyDasar = (existingQtyInThisGr + existingRejectInThisGr) * existingFaktor;
                        let maxAllowedDasar = qtySisaDasar + existingQtyDasar;
                        let maxAllowedInUnit = (existingFaktor > 0) ? (maxAllowedDasar / existingFaktor) : maxAllowedDasar;
                        let isFull = maxAllowedDasar <= 0 && !existingDetail;

                        let initialQty = existingDetail ? existingQtyInThisGr : (isEditingExistingGr ? 0 : (isFull ? 0 : parseFloat(item.qty_sisa || 0)));
                        let initialReject = existingDetail ? existingRejectInThisGr : 0;

                        let diskonItem = parseFloat(item.diskon_item || 0);
                        let diskonPersen = parseFloat(item.diskon_persen || 0);
                        let tarifPpn = parseFloat(item.tarif_ppn || 0);
                        let namaPpn = item.nama_ppn || (tarifPpn > 0 ? 'PPN ' + tarifPpn + '%' : 'Non PPN');

                        let initialTotal = initialQty * Math.max(0, currentUnitPrice - diskonItem);
                        let hargaIdr = currentUnitPrice * effectiveKursInit;
                        let diskonIdr = diskonItem * effectiveKursInit;
                        let totalIdr = initialTotal * effectiveKursInit;
                        let fmtH = fmt(currentUnitPrice);
                        let fmtD = fmt(diskonItem);
                        let fmtT = fmt(initialTotal);
                        let fmtHIdr = fmt(hargaIdr);
                        let fmtDIdr = fmt(diskonIdr);
                        let fmtTIdr = fmt(totalIdr);

                        let discText = '-';
                        if (diskonItem > 0) {
                            let pctLabel = diskonPersen > 0 ? `-${diskonPersen % 1 === 0 ? diskonPersen.toFixed(0) : diskonPersen.toFixed(2)}% ` : '';
                            discText = `<span class="text-danger fw-bold">${pctLabel}</span><br><small class="text-muted" style="font-size:0.72rem;">(${window.currentCurrencySymbol} ${fmtD})</small>`;
                        }

                        let ppnBadge = tarifPpn > 0 
                            ? `<span class="badge bg-warning bg-opacity-10 text-warning px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.72rem;">${escapeHtml(namaPpn)}</span>` 
                            : `<span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">0%</span>`;

                        let serialBtnHtml = '';
                        if (item.tipe_item === 'Aset') {
                            serialBtnHtml = `<div class="mt-1"><button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-0.5 btn-trigger-serial" style="font-size: 0.7rem; font-weight: 600;"><i class="bi bi-upc-scan me-1"></i> Serial & Tag Aset</button></div>`;
                        }

                        let selectedRak = existingDetail ? existingDetail.lokasi_rak_id : null;
                        let rakSelectHtml = window.buildRakSelectOptions(selectedRak);

                        let editUnits = item.available_units || [];
                        let satuanCellHtml = '';

                        if (editUnits.length <= 1) {
                            let displaySatuan = (editUnits.length === 1 && editUnits[0].nama_satuan) ? editUnits[0].nama_satuan : (item.po_satuan_nama || 'UNT');
                            satuanCellHtml = `
                                <div class="d-inline-flex align-items-center justify-content-center px-3 py-1.5 rounded-pill gr-satuan-badge" title="Satuan: ${escapeHtml(displaySatuan)}">
                                    <i class="bi bi-tag-fill me-1.5" style="font-size: 0.72rem;"></i>
                                    <span class="fw-bold font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">${escapeHtml(displaySatuan)}</span>
                                </div>
                                <input type="hidden" class="satuan-id-val" value="${existingSatuanId}">
                                <input type="hidden" class="faktor-pengali-val" value="${existingFaktor}">
                            `;
                        } else {
                            let satuanOptionsHtml = '';
                            editUnits.forEach(function (u) {
                                let selectedAttr = (u.satuan_id == existingSatuanId) ? 'selected' : '';
                                let optLabel = u.label || u.nama_satuan;
                                satuanOptionsHtml += `<option value="${u.satuan_id}" data-faktor="${u.faktor_pengali}" data-nama="${u.nama_satuan}" ${selectedAttr}>${escapeHtml(optLabel)}</option>`;
                            });
                            satuanCellHtml = `
                                <select class="form-select form-select-sm satuan-select gr-satuan-select rounded-pill px-2.5 py-1 text-center">
                                    ${satuanOptionsHtml}
                                </select>
                                <input type="hidden" class="satuan-id-val" value="${existingSatuanId}">
                                <input type="hidden" class="faktor-pengali-val" value="${existingFaktor}">
                            `;
                        }

                        html += `
                            <tr data-po-detail-id="${item.po_detail_id}" 
                                data-barang-id="${item.barang_id}" 
                                data-po-harga-satuan="${item.harga_satuan}"
                                data-po-faktor="${poFaktor}"
                                data-satuan-faktor="${existingFaktor}"
                                data-harga-satuan="${currentUnitPrice}" 
                                data-qty-sisa-dasar="${qtySisaDasar}"
                                data-qty-max-dasar="${maxAllowedDasar}"
                                data-dasar-satuan-nama="${item.dasar_satuan_nama}"
                                data-po-satuan-nama="${item.po_satuan_nama}"
                                data-diskon-item="${diskonItem}"
                                data-tarif-ppn="${tarifPpn}">
                                <td data-label="Nama Barang" class="sticky-col-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-img-wrapper" style="flex-shrink:0;">
                                            ${fotoUrl ? `<img src="${fotoUrl}" alt="${item.nama_barang}" class="product-img zoomable-img" style="cursor: zoom-in;">` : `<i class="bi bi-image text-muted"></i>`}
                                        </div>
                                        <div style="min-width:0;">
                                            <span class="fw-semibold text-dark text-truncate d-block" style="max-width: 170px;">${item.nama_barang}</span>
                                            <small class="text-muted font-monospace">${item.kode_barang}</small>
                                            ${serialBtnHtml}
                                        </div>
                                    </div>
                                    <input type="hidden" class="po-detail-id-val" value="${item.po_detail_id}">
                                    <input type="hidden" class="barang-id-val" value="${item.barang_id}">
                                </td>
                                <td data-label="Lokasi Rak" class="text-center lokasi-rak-cell">${rakSelectHtml}</td>
                                <td data-label="Qty Dipesan" class="text-center fw-medium text-dark text-nowrap">
                                    ${fmtQ(item.qty_dipesan)} <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace" style="font-size:0.75rem;">${item.po_satuan_nama}</span>
                                    ${poFaktor > 1 ? `<br><small class="text-muted" style="font-size:0.68rem;">(${fmtQ(item.qty_dipesan_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
                                </td>
                                <td data-label="Qty Sudah Diterima" class="text-center fw-medium text-secondary text-nowrap">
                                    ${fmtQ(item.qty_diterima)} <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace" style="font-size:0.75rem;">${item.po_satuan_nama}</span>
                                    ${poFaktor > 1 ? `<br><small class="text-muted" style="font-size:0.68rem;">(${fmtQ(item.qty_diterima_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
                                </td>
                                <td data-label="Qty Sisa" class="text-center fw-bold text-info text-nowrap">
                                    <span class="qty-sisa-val">${fmtQ(item.qty_sisa)}</span> <span class="badge bg-info bg-opacity-10 text-info font-monospace sisa-satuan-label" style="font-size:0.75rem;">${item.po_satuan_nama}</span>
                                    ${poFaktor > 1 ? `<br><small class="text-muted sisa-dasar-label" style="font-size:0.68rem;">(${fmtQ(item.qty_sisa_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
                                </td>
                                <td data-label="Harga Satuan" class="text-end fw-medium text-nowrap">
                                    ${window.currentCurrencySymbol} ${fmtH}
                                    ${window.currentCurrencySymbol !== 'Rp' ? `<br><small class="text-muted harga-satuan-idr-val" style="font-size: 0.72rem;">Rp ${fmtHIdr}</small>` : ''}
                                </td>
                                <td data-label="Diskon Item" class="text-end fw-medium text-nowrap">
                                    ${discText}
                                </td>
                                <td data-label="PPN" class="text-center text-nowrap">
                                    ${ppnBadge}
                                    <div class="row-ppn-preview small text-muted" style="font-size: 0.68rem;"></div>
                                </td>
                                <td data-label="Qty Diterima" class="text-center">
                                    <input type="number" step="any" class="form-control form-control-sm text-end qty-input rounded-pill"
                                        value="${fmtQ(initialQty)}" min="0" max="${maxAllowedInUnit}" data-max-sisa="${maxAllowedInUnit}" style="width: 90px; display: inline-block;">
                                    <small class="text-muted d-block mt-0.5 konversi-live-hint" style="font-size: 0.68rem;"></small>
                                </td>
                                <td data-label="Qty Reject" class="text-center">
                                    <input type="number" step="any" class="form-control form-control-sm text-end qty-reject-input rounded-pill"
                                        value="${fmtQ(initialReject)}" min="0" max="${maxAllowedInUnit}" data-max-sisa="${maxAllowedInUnit}" style="width: 90px; display: inline-block;">
                                </td>
                                <td data-label="Satuan" class="text-center">
                                    ${satuanCellHtml}
                                </td>
                                <td data-label="Total" class="text-end fw-bold text-success total-price-val">
                                    ${window.currentCurrencySymbol} ${fmtT}
                                    ${window.currentCurrencySymbol !== 'Rp' ? `<br><small class="text-muted total-price-idr-val" style="font-size: 0.72rem;">Rp ${fmtTIdr}</small>` : ''}
                                </td>
                            </tr>
                        `;
                    });

                    $('#edit_gr_details_tbody').html(html);
                    if (typeof window.initGrTableDragScroll === 'function') {
                        window.initGrTableDragScroll();
                    }
                    if (typeof window.recalculateEditAllPrices === 'function') {
                        window.recalculateEditAllPrices();
                    }
                    window.calculateEditFooterSummary();
                    if (typeof callback === 'function') callback();
                };

                if (preloadedRaks && preloadedRaks.length > 0) {
                    window.activeGudangRaks = preloadedRaks;
                    window.gudangRaksCache = preloadedRaks;
                    window.gudangGunakanRakCache = true;
                    executeRender();
                } else {
                    let editGudangId = $('#edit_gudang_id').val();
                    if (editGudangId && typeof window.fetchGudangRaks === 'function') {
                        window.fetchGudangRaks(editGudangId, function () {
                            executeRender();
                        });
                    } else {
                        executeRender();
                    }
                }
            };

            if (preloadedPoDetails) {
                renderPo(preloadedPoDetails);
            } else {
                $('#edit_gr_details_tbody').html('<tr><td colspan="12" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Memuat detail PO...</td></tr>');
                $.get('/goods-receipt/po-details/' + poId, renderPo).fail(function () {
                    $('#edit_gr_details_tbody').html('<tr><td colspan="12" class="text-center text-danger py-3">Gagal memuat rincian PO.</td></tr>');
                });
            }
        };

        // ===== EDIT FOOTER SUMMARY CALCULATION =====
        window.calculateEditFooterSummary = function () {
            let totalQtyReceived = 0;
            let totalQtyReject = 0;
            let totalNilaiBarang = 0;
            let totalDiskonBarang = 0;
            let totalNetItemSubtotal = 0;
            let totalPajakBarang = 0;
            let itemIds = new Set();
            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
            let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : parseFloat(n.toFixed(4)).toString(); };

            $('#edit_gr_details_tbody tr').each(function () {
                let bId = $(this).find('.barang-id-val').val();
                let qtyReceived = parseFloat($(this).find('.qty-input').val()) || 0;
                let qtyReject = parseFloat($(this).find('.qty-reject-input').val()) || 0;
                let hargaSatuan = parseFloat($(this).data('harga-satuan')) || 0;
                let diskonItem = parseFloat($(this).data('diskon-item')) || 0;
                let tarifPpn = parseFloat($(this).data('tarif-ppn')) || 0;

                if (qtyReceived > 0 || qtyReject > 0) {
                    if (bId) itemIds.add(bId);
                }
                let netPrice = Math.max(0, hargaSatuan - diskonItem);
                let lineGross = qtyReceived * hargaSatuan;
                let lineDiskon = qtyReceived * diskonItem;
                let lineNet = qtyReceived * netPrice;
                let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;

                totalQtyReceived += qtyReceived;
                totalQtyReject += qtyReject;
                totalNilaiBarang += lineGross;
                totalDiskonBarang += lineDiskon;
                totalNetItemSubtotal += lineNet;
                totalPajakBarang += linePpn;

                // Live updates for row
                let effectiveKurs = parseFloat($('#edit_kurs_gr_raw').val()) || window.currentPoKurs || 1;
                let fmtT = fmt(lineNet);
                let fmtTIdr = fmt(lineNet * effectiveKurs);
                $(this).find('.total-price-val').html(
                    `${window.currentCurrencySymbol} ${fmtT}` +
                    (window.currentCurrencySymbol !== 'Rp' ? `<br><small class="text-muted total-price-idr-val" style="font-size: 0.72rem;">Rp ${fmtTIdr}</small>` : '')
                );
                $(this).find('.row-ppn-preview').text(linePpn > 0 ? ('+ ' + window.currentCurrencySymbol + ' ' + fmt(linePpn)) : '');
            });

            totalNilaiBarang = Math.round(totalNilaiBarang * 100) / 100;
            totalDiskonBarang = Math.round(totalDiskonBarang * 100) / 100;
            totalNetItemSubtotal = Math.round(totalNetItemSubtotal * 100) / 100;
            totalPajakBarang = Math.round(totalPajakBarang * 100) / 100;

            let totalDiscountVal = totalDiskonBarang;
            let dpp = totalNetItemSubtotal;
            let taxVal = totalPajakBarang;

            let ratio = (window.currentPoSubtotal > 0) ? (totalNetItemSubtotal / window.currentPoSubtotal) : 0;
            let isFullReceipt = Math.abs(ratio - 1) < 0.0001;

            let pphVal = 0;
            if (window.currentPoPphTarif > 0) {
                pphVal = Math.round(dpp * (window.currentPoPphTarif / 100) * 100) / 100;
            } else if (window.currentPoPphNominal > 0) {
                pphVal = (isFullReceipt && window.currentPoPphNominal > 0) 
                    ? window.currentPoPphNominal 
                    : Math.round(((window.currentPoPphNominal || 0) * ratio) * 100) / 100;
            }

            let biayaLain = (isFullReceipt && window.currentPoBiayaLain > 0) 
                ? window.currentPoBiayaLain 
                : Math.round(((window.currentPoBiayaLain || 0) * ratio) * 100) / 100;
            let totalSebelumDp = Math.max(0, dpp + taxVal + biayaLain - pphVal);
            let dpVal = (isFullReceipt && window.currentPoDp > 0) 
                ? window.currentPoDp 
                : Math.round(((window.currentPoDp || 0) * ratio) * 100) / 100;
            let grandTotal = Math.max(0, totalSebelumDp - dpVal);
            grandTotal = Math.round(grandTotal * 100) / 100;

            let effectiveKurs = parseFloat($('#edit_kurs_gr_raw').val()) 
                || (typeof window.parseKursID === 'function' ? window.parseKursID($('#edit_kurs_gr').val()) : 0) 
                || window.currentPoKurs 
                || 1;

            let totalNilaiBarangIdr = Math.round(totalNilaiBarang * effectiveKurs * 100) / 100;
            let grandTotalIdr = Math.round(grandTotal * effectiveKurs * 100) / 100;

            $('#edit_summary_total_jenis, #edit_summary_total_jenis_mobile').text(itemIds.size);
            $('#edit_summary_qty_diterima').text(new Intl.NumberFormat('id-ID').format(totalQtyReceived));
            $('#edit_summary_qty_reject').text(new Intl.NumberFormat('id-ID').format(totalQtyReject));

            $('#edit_summary_nilai_barang').text(window.currentCurrencySymbol + ' ' + fmt(totalNilaiBarang));
            $('#edit_summary_diskon_val').text(totalDiscountVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(totalDiscountVal)) : (window.currentCurrencySymbol + ' 0,00'));
            $('#edit_summary_ppn_val').text(window.currentCurrencySymbol + ' ' + fmt(taxVal));

            if (pphVal > 0 || window.currentPoPphTarif > 0 || window.currentPoPphNominal > 0) {
                $('#edit_summary_pph_tile').removeClass('d-none');
                let pphTitle = window.currentPoPphTarif > 0 ? ('PPh (' + window.currentPoPphTarif + '%)') : (window.currentPoPphNama || 'Potongan PPh');
                $('#edit_summary_pph_title').text(pphTitle);
                $('#edit_summary_pph_val').text(pphVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(pphVal)) : (window.currentCurrencySymbol + ' 0,00'));
            } else {
                $('#edit_summary_pph_tile').addClass('d-none');
            }

            $('#edit_summary_biaya_lain_val').text(window.currentCurrencySymbol + ' ' + fmt(biayaLain));
            $('#edit_summary_total_sebelum_dp_val').text(window.currentCurrencySymbol + ' ' + fmt(totalSebelumDp));
            $('#edit_summary_dp_val').text(dpVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(dpVal)) : (window.currentCurrencySymbol + ' 0,00'));
            $('#edit_summary_grand_total, #edit_summary_grand_total_desktop').text(window.currentCurrencySymbol + ' ' + fmt(grandTotal));

            if (window.currentCurrencySymbol !== 'Rp') {
                $('#edit_summary_nilai_barang_idr').text('(Rp ' + fmt(totalNilaiBarangIdr) + ')').removeClass('d-none');
                $('#edit_summary_grand_total_idr, #edit_summary_grand_total_idr_desktop').text('(Rp ' + fmt(grandTotalIdr) + ')').removeClass('d-none');
            } else {
                $('#edit_summary_nilai_barang_idr').addClass('d-none').text('');
                $('#edit_summary_grand_total_idr, #edit_summary_grand_total_idr_desktop').addClass('d-none').text('');
            }
        };

        // ===== RECALCULATE ALL ROW PRICES AND SUMMARY FOR EDIT MODAL =====
        window.recalculateEditAllPrices = function () {
            let effectiveKurs = parseFloat($('#edit_kurs_gr_raw').val()) || (typeof window.parseKursID === 'function' ? window.parseKursID($('#edit_kurs_gr').val()) : 0) || window.currentPoKurs || 1;
            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

            // Update Nilai Kurs & Grand Total di Card PO Asal secara real-time
            if (window.currentCurrencySymbol !== 'Rp') {
                let kursPoFmt = fmt(window.currentPoKurs);
                let kursGrFmt = fmt(effectiveKurs);
                if (Math.abs(effectiveKurs - window.currentPoKurs) > 0.0001) {
                    $('#edit_card_kurs').html(`<span class="text-primary fw-bold">${kursGrFmt}</span> <small class="text-muted" style="font-size:0.75rem;">(PO: ${kursPoFmt})</small>`);
                } else {
                    $('#edit_card_kurs').text(kursPoFmt);
                }

                if (window.currentPoGrandTotal) {
                    let poGrandTotalIdr = window.currentPoGrandTotal * effectiveKurs;
                    $('#edit_card_grand_total').html(`${window.currentCurrencySymbol} ${fmt(window.currentPoGrandTotal)}<br><small class="text-muted fw-normal" style="font-size: 0.68rem;">(Rp ${fmt(poGrandTotalIdr)})</small>`);
                }
            }

            $('#edit_gr_details_tbody tr').each(function () {
                let row = $(this);
                let hargaSatuan = parseFloat(row.data('harga-satuan')) || 0;
                let diskonItem = parseFloat(row.data('diskon-item')) || 0;
                let tarifPpn = parseFloat(row.data('tarif-ppn')) || 0;
                let qtyVal = parseFloat(row.find('.qty-input').val()) || 0;
                let netPrice = Math.max(0, hargaSatuan - diskonItem);
                let total = qtyVal * netPrice;
                let linePpn = Math.round(total * (tarifPpn / 100) * 100) / 100;

                let hargaIdr = hargaSatuan * effectiveKurs;
                let totalIdr = total * effectiveKurs;

                row.find('.harga-satuan-idr-val').text('Rp ' + fmt(hargaIdr));
                row.find('.total-price-idr-val').text('Rp ' + fmt(totalIdr));
                row.find('.row-ppn-preview').text(linePpn > 0 ? ('+ ' + window.currentCurrencySymbol + ' ' + fmt(linePpn)) : '');
            });

            window.calculateEditFooterSummary();
        };

        // Unit change handler (Konversi Satuan) in Edit Modal
        $(document).on('change', '#edit_gr_details_tbody .satuan-select', function () {
            let row = $(this).closest('tr');
            let opt = $(this).find('option:selected');
            let satuanId = opt.val();
            let currentFaktor = parseFloat(opt.data('faktor')) || 1.0;
            let satuanNama = opt.data('nama') || '';

            row.find('.satuan-id-val').val(satuanId);
            row.find('.faktor-pengali-val').val(currentFaktor);
            row.data('satuan-faktor', currentFaktor);

            let poHargaSatuan = parseFloat(row.data('po-harga-satuan')) || 0;
            let poFaktor = parseFloat(row.data('po-faktor')) || 1.0;
            let maxAllowedDasar = parseFloat(row.data('qty-max-dasar')) || 0;
            let qtySisaDasar = parseFloat(row.data('qty-sisa-dasar')) || 0;

            // Recalculate price in currently selected unit
            let newUnitPrice = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * currentFaktor) : poHargaSatuan;
            row.data('harga-satuan', newUnitPrice);

            // Recalculate max in currently selected unit
            let newMax = (currentFaktor > 0) ? (maxAllowedDasar / currentFaktor) : maxAllowedDasar;
            let newRemaining = (currentFaktor > 0) ? (qtySisaDasar / currentFaktor) : qtySisaDasar;
            let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : parseFloat(n.toFixed(4)).toString(); };
            row.find('.qty-sisa-val').text(fmtQ(newRemaining));
            row.find('.sisa-satuan-label').text(satuanNama);

            let qtyInput = row.find('.qty-input');
            let rejectInput = row.find('.qty-reject-input');
            qtyInput.attr('max', newMax).data('max-sisa', newMax).attr('data-max-sisa', newMax);
            rejectInput.attr('max', newMax).data('max-sisa', newMax).attr('data-max-sisa', newMax);

            // Update Harga Satuan column text
            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
            let effectiveKurs = parseFloat($('#edit_kurs_gr_raw').val()) || window.currentPoKurs || 1;
            let newPriceIdr = newUnitPrice * effectiveKurs;
            let hargaHtml = `${window.currentCurrencySymbol} ${fmt(newUnitPrice)}`;
            if (window.currentCurrencySymbol !== 'Rp') {
                hargaHtml += `<br><small class="text-muted harga-satuan-idr-val" style="font-size: 0.72rem;">Rp ${fmt(newPriceIdr)}</small>`;
            }
            row.find('td[data-label="Harga Satuan"]').html(hargaHtml);

            // Trigger Qty input change to re-evaluate conversion hint & row total
            qtyInput.trigger('input');
        });

        // Recalculate row total & validate Qty limit on change in Edit Modal
        $(document).on('input change', '#edit_gr_details_tbody .qty-input, #edit_gr_details_tbody .qty-reject-input', function () {
            let row = $(this).closest('tr');
            let qtyInput = row.find('.qty-input');
            let rejectInput = row.find('.qty-reject-input');
            let qtyVal = parseFloat(qtyInput.val()) || 0;
            let rejectVal = parseFloat(rejectInput.val()) || 0;

            let currentFaktor = parseFloat(row.find('.faktor-pengali-val').val()) || parseFloat(row.data('satuan-faktor')) || 1.0;
            let dasarSatuanNama = row.data('dasar-satuan-nama') || '';
            let poFaktor = parseFloat(row.data('po-faktor')) || 1.0;
            let poSatuanNama = row.data('po-satuan-nama') || '';
            let maxAllowedDasar = parseFloat(row.data('qty-max-dasar')) || 0;

            if (qtyVal < 0) { qtyVal = 0; qtyInput.val(0); }
            if (rejectVal < 0) { rejectVal = 0; rejectInput.val(0); }

            let totalQtyDasarInRow = (qtyVal + rejectVal) * currentFaktor;
            let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : parseFloat(n.toFixed(4)).toString(); };

            if (totalQtyDasarInRow > (maxAllowedDasar + 0.0001)) {
                let barangNama = row.find('span.fw-semibold').first().text().trim() || 'Barang';
                let maxInCurrentUnit = (currentFaktor > 0) ? (maxAllowedDasar / currentFaktor) : maxAllowedDasar;

                Swal.fire({
                    title: 'Peringatan Kelebihan Qty!',
                    html: `Total kuantitas penerimaan baru & reject untuk <b>${escapeHtml(barangNama)}</b> melebihi batas maksimal sisa PO!<br><small class="text-muted">Batas maksimal: <b>${fmtQ(maxInCurrentUnit)}</b> (setara <b>${fmtQ(maxAllowedDasar)} ${escapeHtml(dasarSatuanNama)}</b>).</small>`,
                    icon: 'warning',
                    confirmButtonText: 'Saya Mengerti',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill px-4' }
                });

                let overflowDasar = totalQtyDasarInRow - maxAllowedDasar;
                let overflowInCurrentUnit = overflowDasar / currentFaktor;
                if ($(this).hasClass('qty-input')) {
                    qtyVal = Math.max(0, qtyVal - overflowInCurrentUnit);
                    qtyInput.val(fmtQ(qtyVal));
                } else {
                    rejectVal = Math.max(0, rejectVal - overflowInCurrentUnit);
                    rejectInput.val(fmtQ(rejectVal));
                }
            }

            // Update live conversion helper text
            let hintText = '';
            if (qtyVal > 0) {
                if (currentFaktor > 1) {
                    let baseEq = qtyVal * currentFaktor;
                    hintText = `= ${fmtQ(baseEq)} ${dasarSatuanNama}`;
                } else if (currentFaktor === 1 && poFaktor > 1) {
                    let poEq = qtyVal / poFaktor;
                    hintText = `≈ ${fmtQ(poEq)} ${poSatuanNama}`;
                }
            }
            row.find('.konversi-live-hint').text(hintText);

            window.calculateEditFooterSummary();
        });

        // Change handler for #edit_gudang_id in Edit Modal
        $(document).on('change', '#edit_gudang_id', function () {
            let editGudangId = $(this).val();
            if (editGudangId && typeof window.fetchGudangRaks === 'function') {
                window.fetchGudangRaks(editGudangId, function () {
                    $('#edit_gr_details_tbody tr').each(function () {
                        let cell = $(this).find('.lokasi-rak-cell');
                        let currentVal = cell.find('.lokasi-rak-select').val();
                        cell.html(window.buildRakSelectOptions(currentVal));
                    });
                });
            }
        });

        // Open Pickers for Edit Modal
        $(document).on('click', '#btnOpenEditPOPicker, #edit_po_display', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.openPOPickerModal === 'function') {
                window.openPOPickerModal();
            }
        });

        $(document).on('click', '#btnOpenEditGudangPicker, #edit_gudang_display', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.openGudangPickerModal === 'function') {
                window.openGudangPickerModal();
            }
        });

        // ===== CLICK .btn-edit-gr (OPEN EDIT DRAFT MODAL INSTANTLY) =====
        $(document).on('click', '.btn-edit-gr', function () {
            let grId = $(this).data('id');

            // Open modal immediately (0ms popup delay) & show table spinner
            $('#modalEditGRLabel').text('Edit Draft Penerimaan Barang');
            $('#edit_gr_id').val(grId);
            $('#edit_gr_details_tbody').html('<tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Memuat data draft...</td></tr>');
            $('#modalEditGR').modal('show');

            $.get('/goods-receipt/' + grId, function (res) {
                let gr = res.gr;
                if (!gr || (gr.status !== 'Draft' && !res.is_super_admin)) {
                    $('#modalEditGR').modal('hide');
                    Swal.fire('Perhatian!', 'Hanya dokumen Penerimaan Barang berstatus Draft yang dapat di-edit.', 'warning');
                    return;
                }

                let titleText = gr.status === 'Posted' 
                    ? 'Edit Penerimaan Barang [' + escapeHtml(gr.nomor_gr) + '] <span class="badge bg-danger ms-1">Super Admin</span>' 
                    : 'Edit Draft Penerimaan Barang (' + escapeHtml(gr.nomor_gr) + ')';
                $('#modalEditGRLabel').html(titleText);
                let dateVal = gr.tanggal_gr ? gr.tanggal_gr.substring(0, 10) : '';
                $('#edit_tanggal_gr').val(dateVal);
                $('#edit_po_id').val(gr.po_id);
                $('#edit_po_display').val(gr.po ? gr.po.nomor_po : '');
                $('#edit_gudang_id').val(gr.gudang_id);
                $('#edit_gudang_display').val(gr.gudang ? gr.gudang.nama_gudang : '');
                $('#edit_nomor_surat_jalan').val(gr.nomor_surat_jalan || '');
                $('#edit_keterangan').val(gr.keterangan || '');
                $('#edit_nomor_gr_input').val(gr.nomor_gr);

                let detailsMap = {};
                if (gr.details && gr.details.length > 0) {
                    gr.details.forEach(function (d) {
                        detailsMap[d.po_detail_id] = d;
                    });
                }

                // Fill kurs dari data GR yang sudah tersimpan
                let grKurs = parseFloat(gr.kurs) || 0;
                let poCurrency = gr.po ? (gr.po.currency ? gr.po.currency.simbol : 'Rp') : 'Rp';
                let poCurrCode = gr.po ? (gr.po.currency ? gr.po.currency.kode_currency : 'IDR') : 'IDR';
                let poKurs = gr.po ? parseFloat(gr.po.kurs) : 1;

                if (poCurrency !== 'Rp') {
                    $('#edit_kurs_gr_wrapper').show();
                    $('#edit_kurs_gr_symbol').text(poCurrCode);
                    $('#edit_kurs_gr_currency_hint').text('(' + poCurrCode + ' → IDR)');
                    $('#edit_kurs_po_reference').text(
                        new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 4}).format(poKurs)
                    );
                    if (typeof window.setKursField === 'function') {
                        window.setKursField('edit_kurs_gr', 'edit_kurs_gr_raw', grKurs > 0 ? grKurs : poKurs);
                    } else {
                        $('#edit_kurs_gr').val(grKurs > 0 ? grKurs : poKurs);
                        $('#edit_kurs_gr_raw').val(grKurs > 0 ? grKurs : poKurs);
                    }
                } else {
                    $('#edit_kurs_gr_wrapper').hide();
                    $('#edit_kurs_gr').val('');
                    $('#edit_kurs_gr_raw').val('');
                }

                window.loadEditPODetails(gr.po_id, detailsMap, null, res.po_details, res.raks);
            }).fail(function (xhr) {
                $('#modalEditGR').modal('hide');
                Swal.fire('Gagal!', 'Gagal memuat data penerimaan barang.', 'error');
            });
        });

        // Single submission guard for Edit Modal
        let isEditSubmitting = false;

        $(document).on('click', '#btn_update_draft, .btn-edit-action-draft', function (e) {
            e.preventDefault();
            if (isEditSubmitting) return;
            $('#edit_is_draft').val('1');
            $('#formEditGR').trigger('submit');
        });

        $(document).on('click', '#btn_update_gr, .btn-edit-action-posting', function (e) {
            e.preventDefault();
            if (isEditSubmitting) return;
            $('#edit_is_draft').val('0');
            $('#formEditGR').trigger('submit');
        });

        // Submit #formEditGR (PUT /goods-receipt/{id}/update)
        $('#formEditGR').on('submit', function (e) {
            e.preventDefault();
            if (isEditSubmitting) return;

            let grId = $('#edit_gr_id').val();
            if (!grId) return;

            let items = [];
            let totalReceived = 0;
            let totalReject = 0;

            $('#edit_gr_details_tbody tr').each(function () {
                let poDetailId = $(this).find('.po-detail-id-val').val();
                let barangId = $(this).find('.barang-id-val').val();
                let rakId = $(this).find('.lokasi-rak-select').val() || null;
                let qtyReceived = parseFloat($(this).find('.qty-input').val()) || 0;
                let qtyReject = parseFloat($(this).find('.qty-reject-input').val()) || 0;

                if (poDetailId && barangId && (qtyReceived > 0 || qtyReject > 0)) {
                    items.push({
                        po_detail_id: poDetailId,
                        barang_id: barangId,
                        lokasi_rak_id: rakId,
                        satuan_id: $(this).find('.satuan-id-val').val() || $(this).find('.satuan-select').val() || null,
                        faktor_pengali: parseFloat($(this).find('.faktor-pengali-val').val()) || parseFloat($(this).data('satuan-faktor')) || 1.0,
                        jumlah: qtyReceived,
                        jumlah_reject: qtyReject
                    });
                    totalReceived += qtyReceived;
                    totalReject += qtyReject;
                }
            });

            if (items.length === 0 || (totalReceived === 0 && totalReject === 0)) {
                Swal.fire('Gagal!', 'Kuantitas penerimaan barang atau reject tidak boleh kosong!', 'warning');
                return;
            }

            isEditSubmitting = true;
            let submitBtn = $('#btn_update_gr');
            let draftBtn = $('#btn_update_draft');
            submitBtn.prop('disabled', true);
            draftBtn.prop('disabled', true);

            let isDraftVal = $('#edit_is_draft').val() === '1' ? 1 : 0;
            if (isDraftVal) {
                draftBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Draft...');
            } else {
                submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan & Posting...');
            }

            let editKursRaw = parseFloat($('#edit_kurs_gr_raw').val()) || 0;
            let payload = {
                tanggal_gr: $('#edit_tanggal_gr').val(),
                po_id: $('#edit_po_id').val(),
                gudang_id: $('#edit_gudang_id').val(),
                nomor_surat_jalan: $('#edit_nomor_surat_jalan').val(),
                keterangan: $('#edit_keterangan').val(),
                kurs: editKursRaw > 0 ? editKursRaw : 1,
                auto_number: 1,
                nomor_gr: $('#edit_nomor_gr_input').val(),
                is_draft: isDraftVal,
                items: items
            };

            $.ajax({
                url: '/goods-receipt/' + grId + '/update',
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    isEditSubmitting = false;
                    $('#modalEditGR').modal('hide');
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i>Simpan & Posting');
                    draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-code me-1"></i>Simpan Draft');
                    Swal.fire({
                        title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false
                    }).then(() => { reloadTableGR(); });
                },
                error: function (err) {
                    isEditSubmitting = false;
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i>Simpan & Posting');
                    draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-code me-1"></i>Simpan Draft');
                    let errorMsg = 'Gagal mengedit penerimaan barang.';
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Validasi Gagal:</b><br>';
                        $.each(err.responseJSON.errors, function (key, val) { errorMsg += '• ' + val[0] + '<br>'; });
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                }
            });
        });

    }); // end document.ready [modal_edit]
</script>
@endpush
