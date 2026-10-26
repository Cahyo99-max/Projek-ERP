
<!-- MODAL CREATE RECEIPT -->
<div class="modal fade" id="modalTambahGR" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTambahGRLabel">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl gr-modal-dialog">
        <div class="modal-content border-0 shadow-lg gr-modal-content">
            <div class="modal-header border-0 py-3 px-3 px-md-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(59, 130, 246, 0.18); border: 1px solid rgba(59, 130, 246, 0.35); color: #38bdf8;">
                        <i class="bi bi-box-seam-fill fs-4 text-primary"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalTambahGRLabel" style="font-size: 1.15rem; letter-spacing: -0.2px;">Penerimaan Barang</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); font-size: 0.70rem; font-weight: 600; letter-spacing: 0.3px;">Goods Receipt</span>
                        </div>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 0.78rem;">Pencatatan fisik barang masuk gudang berdasarkan Purchase Order (PO)</small>
                    </div>
                </div>
                <button type="button" class="gr-modal-header-close flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Tutup" title="Tutup Modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="formTambahGR" method="POST">
                <input type="hidden" name="gr_id" id="gr_id" value="">
                <input type="hidden" name="is_draft" id="is_draft" value="0">
                <div class="modal-body p-3 p-md-4">
                    <!-- HEADER TRANSACTION INFO -->
                    <div class="card border-0 bg-light bg-opacity-50 rounded-3 p-3 mb-4 shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold">Nomor Transaksi</label>
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" id="auto_number_toggle" checked>
                                    <label class="form-check-label small text-muted" for="auto_number_toggle">Auto Number</label>
                                </div>
                                <input type="text" name="nomor_gr" id="nomor_gr_input" class="form-control form-control-sm rounded-pill d-none" placeholder="Masukkan manual...">
                                <span class="badge bg-secondary rounded-pill px-3 py-1.5 fs-7 d-block text-start w-100" id="auto_number_label">
                                    <i class="bi bi-magic me-1"></i> Generated Otomatis
                                </span>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="tanggal_gr">Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_gr" id="tanggal_gr" class="form-control form-control-sm rounded-pill" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="po_display">Pilih Purchase Order (PO) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="po_id" id="po_id" required>
                                    <input type="text" id="po_display" class="form-control form-control-sm form-control-custom" placeholder="Cari & pilih PO asal..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;" onclick="window.openPOPickerModal()" required>
                                    <button type="button" class="btn btn-primary px-3" id="btnOpenPOPicker" title="Cari PO" onclick="window.openPOPickerModal()" style="border-radius: 0 50px 50px 0; height: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; margin: 0 !important;">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
@php
    $activeBranchIdInput = session('active_branch_id');
    $isBranchActiveInput = !empty($activeBranchIdInput);
    $activeScopeNameInput = $isBranchActiveInput 
        ? (session('active_branch_name') ?: (\App\Models\Branch::find($activeBranchIdInput)?->nama_cabang ?? 'Cabang Aktif'))
        : (session('active_company_name') ?: (auth()->user()?->company?->nama_perusahaan ?? 'PT Aktif'));
@endphp
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="gudang_display">Gudang Penerima <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="gudang_id" id="gudang_id" required>
                                    <input type="text" id="gudang_display" class="form-control form-control-sm form-control-custom" placeholder="Cari & pilih gudang ({{ $activeScopeNameInput }})..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;" onclick="window.openGudangPickerModal()" required>
                                    <button type="button" class="btn btn-warning px-3 text-white" id="btnOpenGudangPicker" title="Cari Gudang" onclick="window.openGudangPickerModal()" style="border-radius: 0 50px 50px 0; height: 38px; min-height: 38px; display: flex; align-items: center; justify-content: center; margin: 0 !important;">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label text-secondary small fw-bold" for="nomor_surat_jalan">Nomor Surat Jalan Supplier</label>
                                <input type="text" name="nomor_surat_jalan" id="nomor_surat_jalan" class="form-control form-control-sm rounded-pill" placeholder="Contoh: SJ-9921...">
                            </div>
                            <div class="col-md-6 col-lg-3" id="kurs_gr_wrapper" style="display: none;">
                                <label class="form-label text-secondary small fw-bold" for="kurs_gr">
                                    Kurs Saat Penerimaan <span class="text-danger">*</span>
                                    <span class="text-muted fw-normal" style="font-size:.75rem;" id="kurs_gr_currency_hint"></span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" style="border-radius: 50px 0 0 50px; font-size: .8rem;" id="kurs_gr_symbol">IDR</span>
                                    <input type="text" id="kurs_gr" class="form-control form-control-sm"
                                        placeholder="Contoh: 15.750,00"
                                        style="border-radius: 0 50px 50px 0;"
                                        inputmode="decimal" autocomplete="off">
                                    <input type="hidden" name="kurs" id="kurs_gr_raw">
                                </div>
                                <small class="text-muted" style="font-size:.72rem;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Kurs PO: <span id="kurs_po_reference" class="fw-semibold text-primary">-</span>
                                </small>
                            </div>
                            <div class="col-md-6 col-lg-9" id="keterangan_gr_col">
                                <label class="form-label text-secondary small fw-bold" for="keterangan">Keterangan / Catatan</label>
                                <input type="text" name="keterangan" id="keterangan" class="form-control form-control-sm rounded-pill" placeholder="Catatan penerimaan barang...">
                            </div>
                        </div>
                    </div>


                    <!-- ITEMS SECTION -->
                    <div id="items_placeholder" class="text-center py-5 text-muted border border-dashed rounded-3">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Silakan pilih Purchase Order (PO) di atas untuk memuat daftar barang.
                    </div>

                    <!-- PO Info Card Wrapper -->
                    <div id="po_info_card_wrapper" class="d-none mb-4">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="bi bi-info-circle text-primary me-2"></i> Rincian Purchase Order (PO) Asal
                                </h6>
                                <div class="row g-3">
                                    <!-- Column 1: PO & Supplier info -->
                                    <div class="col-md-4 border-end">
                                        <div class="d-flex flex-column gap-2 small">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nomor PO</span>
                                                <strong id="card_nomor_po" class="text-primary" style="font-size: 0.85rem;">-</strong>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Tanggal PO</span>
                                                <span id="card_tanggal_po" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nama Tender / Proyek</span>
                                                <span id="card_tender_name" class="fw-bold text-info" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Supplier</span>
                                                <span id="card_supplier" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PIC Supplier</span>
                                                <span id="card_pic_supplier" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">No. Telp Supplier</span>
                                                <span id="card_telepon_supplier" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Column 2: Logistics & Status -->
                                    <div class="col-md-4 border-end">
                                        <div class="d-flex flex-column gap-2 small">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Gudang Tujuan</span>
                                                <span id="card_gudang_tujuan" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Estimasi Kedatangan</span>
                                                <span id="card_eta" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Keterlambatan</span>
                                                <span id="card_status_keterlambatan" class="fw-bold" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Status PO</span>
                                                <span id="card_status_po" class="fw-bold" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PPN / Pajak PO</span>
                                                <span id="card_pajak_info" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center d-none" id="card_pph_row">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">PPh PO</span>
                                                <span id="card_pph_info" class="fw-semibold text-warning" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Uang Muka (DP)</span>
                                                <span id="card_dp_info" class="fw-bold text-warning" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Biaya Lain PO</span>
                                                <span id="card_biaya_lain_info" class="fw-semibold text-dark" style="font-size: 0.8rem;">-</span>
                                            </div>
                                            <div class="d-flex align-items-center" id="card_kurs_row">
                                                <span class="text-muted" style="width: 140px; font-size: 0.8rem;">Nilai Kurs</span>
                                                <strong id="card_kurs" class="text-dark" style="font-size: 0.8rem;">-</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Column 3: Totals & Progress -->
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="row g-2 text-center bg-light rounded p-2 mb-1">
                                                <div class="col-4 border-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Total Item</small>
                                                    <span id="card_total_item" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                                </div>
                                                <div class="col-4 border-end">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Total Qty</small>
                                                    <span id="card_total_qty" class="fw-bold text-dark" style="font-size: 0.8rem;">-</span>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block" style="font-size: 0.65rem; font-weight: 600;">Grand Total</small>
                                                    <span id="card_grand_total" class="fw-bold text-success" style="font-size: 0.8rem;">-</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted fw-bold" style="font-size: 0.68rem;">PROGRESS PENERIMAAN</small>
                                                    <span id="card_progress_text" class="fw-bold text-primary" style="font-size: 0.75rem;">0%</span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div id="card_progress_bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE GR DETAILS CARD -->
                    <div id="items_card" class="card border-0 shadow-sm rounded-3 overflow-hidden d-none">
                        <div class="card-header bg-light py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; flex-shrink: 0;">
                                    <i class="bi bi-list-check fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0">Daftar Barang Diterima</h6>
                            </div>
                            <div class="gr-toolbar-scroll w-100 w-md-auto mt-2 mt-md-0">
                                <span id="po_badge_number" class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold text-nowrap flex-shrink-0" style="font-size: 0.78rem;">
                                    Nomor PO: -
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap flex-shrink-0 shadow-2xs" id="btn_set_mass_rak" title="Atur No. Rak sekaligus ke semua barang">
                                    <i class="bi bi-geo-alt me-1"></i>Set Rak Massal
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-nowrap flex-shrink-0 shadow-2xs" id="btn_fill_all_remaining" title="Isi Qty Diterima dengan sisa Qty PO">
                                    <i class="bi bi-check-all me-1"></i>Terima Semua Sisa
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 text-nowrap flex-shrink-0 shadow-2xs" id="btn_clear_all_items">
                                    <i class="bi bi-eraser me-1"></i>Kosongkan Qty
                                </button>
                            </div>
                        </div>

                        <!-- Swipe Hint Banner for Mobile -->
                        <div class="d-md-none gr-scroll-hint-bar">
                            <span><i class="bi bi-arrow-left-right me-1.5"></i>Geser tabel ke kanan untuk input Qty &amp; Rak</span>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size:0.65rem;">Bisa Digeser</span>
                        </div>

                        <div class="table-responsive gr-table-scroll-wrapper">
                            <table class="table table-hover align-middle mb-0 gr-scroll-table" id="gr_details_table" style="min-width: 1120px;">
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
                                <tbody id="gr_details_tbody">
                                    <!-- Loaded dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Summary Card (Cinematic Bento-style) -->
                        <div class="card-footer border-top p-3 p-md-4" id="gr_footer_summary">
                            <!-- MOBILE CINEMATIC GRAND TOTAL HERO (Visible on mobile < 992px) -->
                            <div class="d-lg-none mb-3">
                                <div class="gr-summary-hero">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-uppercase text-white-50 fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.8px;">Grand Total Penerimaan</span>
                                            <div class="small text-info mt-0.5" id="mobile_summary_quick_info">
                                                <i class="bi bi-box-seam me-1"></i><span id="summary_total_jenis_mobile">0</span> jenis barang
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span id="summary_grand_total" class="fs-4 fw-extrabold" style="color: #34d399 !important;">Rp 0,00</span>
                                            <span id="summary_grand_total_idr" class="small text-white-50 d-block" style="font-size: 0.72rem;"></span>
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
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Total Jenis</small>
                                                <span id="summary_total_jenis" class="fs-6 fw-bold text-dark">0</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Qty Diterima / Reject</small>
                                                <div>
                                                    <span id="summary_qty_diterima" class="fs-6 fw-bold text-primary">0</span>
                                                    <span class="text-muted small">/</span>
                                                    <span id="summary_qty_reject" class="fs-6 fw-bold text-danger">0</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Subtotal Barang</small>
                                                <span id="summary_nilai_barang" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                                <span id="summary_nilai_barang_idr" class="small text-muted d-block" style="font-size: 0.68rem;"></span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Total Diskon</small>
                                                <span id="summary_diskon_val" class="fw-bold text-danger" style="font-size: 0.85rem;">- Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">PPN / Pajak</small>
                                                <span id="summary_ppn_val" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 d-none" id="summary_pph_tile">
                                            <div class="gr-metric-tile h-100" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.04);">
                                                <small class="text-warning d-block mb-1 fw-semibold" style="font-size: 0.68rem;" id="summary_pph_title">Potongan PPh</small>
                                                <span id="summary_pph_val" class="fw-bold text-warning" style="font-size: 0.85rem;">- Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Biaya Lain-lain</small>
                                                <span id="summary_biaya_lain_val" class="fw-bold text-dark" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Total Sebelum DP</small>
                                                <span id="summary_total_sebelum_dp_val" class="fw-bold text-info" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="gr-metric-tile h-100">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.68rem; font-weight: 600;">Uang Muka (DP)</small>
                                                <span id="summary_dp_val" class="fw-bold text-warning" style="font-size: 0.85rem;">Rp 0,00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT GRAND TOTAL (Visible on desktop >= 992px) -->
                                <div class="col-xl-3 col-lg-4 d-none d-lg-block text-end ms-auto">
                                    <div class="gr-summary-hero text-end w-100">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                            <small class="text-white-50 fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Grand Total</small>
                                            <span id="summary_grand_total_desktop" class="fs-5 fw-extrabold" style="color: #34d399;">Rp 0,00</span>
                                        </div>
                                        <span id="summary_grand_total_idr_desktop" class="small text-white-50 d-block" style="font-size: 0.72rem;"></span>
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
                        <button type="button" class="btn btn-action-draft rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" id="btn_save_draft" style="font-size: 0.86rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #000; border: none;">
                            <i class="bi bi-file-earmark-code"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn btn-action-posting rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-1.5 shadow-sm text-white" id="btn_save_gr" style="font-size: 0.88rem; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4) !important;">
                            <i class="bi bi-check-circle-fill"></i> Simpan &amp; Posting
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL SERIAL & TAG ASSET -->
<div class="modal fade" id="modalSerialAsset" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-md modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning bg-opacity-10 text-warning p-2.5 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-upc-scan fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Input Serial & Tag Aset</h5>
                        <small class="text-muted" id="modal_asset_name_label">Aset Tagging</small>
                    </div>
                </div>
                <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-info-circle-fill me-1"></i> Masukkan nomor seri fisik dan kode tag aset (opsional). Kosongkan kode aset jika ingin digenerate otomatis.
                </div>
                <div id="serial_inputs_container" class="row g-3" style="max-height: 400px; overflow-y: auto;">
                    <!-- Dynamic serial input fields go here -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 text-white" id="btn_save_serials">Simpan Detail</button>
            </div>
        </div>
    </div>
</div>

@include('modul_inventori.barang_masuk.lookup_po')
@include('modul_inventori.barang_masuk.lookup_gudang')

<!-- LIGHTBOX PREVIEW -->
<div id="simpleLightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center; flex-direction:column;">
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4 p-2 bg-dark rounded-circle" id="closeLightbox" style="opacity:1;"></button>
    <img id="lightboxImg" src="" class="img-fluid rounded shadow" style="max-width:90%; max-height:90%; object-fit:contain; background: repeating-conic-gradient(#80808033 0% 25%, transparent 0% 50%) 50% / 20px 20px;">
</div>

@push('scripts')
<script>
    window.escapeHtml = window.escapeHtml || function (str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    // ===== SELECT2 INIT =====
    $(document).ready(function () {
        $('.select2-init').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#modalTambahGR')
        });
    });

    // ===== GLOBAL WINDOW VARS FOR GR CALCULATIONS =====
    window.poTotalQty = 0;
    window.poTotalReceivedSoFar = 0;
    window.currentCurrencySymbol = 'Rp';
    window.currentPoKurs = 1;
    window.currentPoSubtotal = 0;
    window.currentPoDiskonPersen = 0;
    window.currentPoTotalDiskon = 0;
    window.currentPoPajakNama = 'PPN';
    window.currentPoPajakTarif = 0;
    window.currentPoBiayaLain = 0;
    window.currentPoDeskripsiBiayaLain = 'Biaya Lain-lain';
    window.currentPoDp = 0;
    window.currentPoTotalPajak = 0;
    window.currentPoPphTarif = 0;
    window.currentPoPphNominal = 0;
    window.currentPoPphNama = 'PPh';
    window.activeGudangRaks = window.activeGudangRaks || [];
    window.gudangGunakanRak = window.gudangGunakanRak || false;

    // ===== PROGRESS BAR UPDATE =====
    window.updateProgressBar = function (totalNewReceived) {
        let currentTotalReceived = window.poTotalReceivedSoFar + totalNewReceived;
        let percent = window.poTotalQty > 0 ? (currentTotalReceived / window.poTotalQty) * 100 : 0;
        percent = Math.min(100, Math.max(0, percent));

        $('#card_progress_text').text(percent.toFixed(1) + '%');
        $('#card_progress_bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#card_progress_ratio').text('Sudah diterima: ' + currentTotalReceived + ' / ' + window.poTotalQty);

        let statusPOHtml = '';
        if (currentTotalReceived > 0) {
            if (currentTotalReceived >= window.poTotalQty) {
                statusPOHtml = '<span class="badge bg-success">Selesai</span>';
            } else {
                statusPOHtml = '<span class="badge bg-warning text-white">Sebagian (Partial)</span>';
            }
        } else {
            statusPOHtml = '<span class="badge bg-secondary">Belum Diterima</span>';
        }
        $('#card_status_po').html(statusPOHtml);
    };

    // ===== FOOTER SUMMARY CALCULATOR (Per-Item Discount & PPN) =====
    window.calculateFooterSummary = function () {
        let totalJenis = 0;
        let totalQtyReceived = 0;
        let totalNilaiBarang = 0;
        let totalQtyReject = 0;
        let totalDiskonBarang = 0;
        let totalNetItemSubtotal = 0;
        let totalPajakBarang = 0;
        let itemIds = new Set();
        let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

        let effectiveKurs = parseFloat($('#kurs_gr_raw').val()) 
            || (typeof window.parseKursID === 'function' ? window.parseKursID($('#kurs_gr').val()) : 0) 
            || window.currentPoKurs 
            || 1;

        $('#gr_details_tbody tr').each(function () {
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

        effectiveKurs = parseFloat($('#kurs_gr_raw').val()) 
            || (typeof window.parseKursID === 'function' ? window.parseKursID($('#kurs_gr').val()) : 0) 
            || window.currentPoKurs 
            || 1;

        let totalNilaiBarangIdr = Math.round(totalNilaiBarang * effectiveKurs * 100) / 100;
        let grandTotalIdr = Math.round(grandTotal * effectiveKurs * 100) / 100;

        $('#summary_total_jenis, #summary_total_jenis_mobile').text(itemIds.size);
        $('#summary_qty_diterima').text(new Intl.NumberFormat('id-ID').format(totalQtyReceived));
        $('#summary_qty_reject').text(new Intl.NumberFormat('id-ID').format(totalQtyReject));

        $('#summary_nilai_barang').text(window.currentCurrencySymbol + ' ' + fmt(totalNilaiBarang));
        $('#summary_diskon_val').text(totalDiscountVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(totalDiscountVal)) : (window.currentCurrencySymbol + ' 0,00'));
        $('#summary_ppn_val').text(window.currentCurrencySymbol + ' ' + fmt(taxVal));

        if (pphVal > 0 || window.currentPoPphTarif > 0 || window.currentPoPphNominal > 0) {
            $('#summary_pph_tile').removeClass('d-none');
            let pphTitle = window.currentPoPphTarif > 0 ? ('PPh (' + window.currentPoPphTarif + '%)') : (window.currentPoPphNama || 'Potongan PPh');
            $('#summary_pph_title').text(pphTitle);
            $('#summary_pph_val').text(pphVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(pphVal)) : (window.currentCurrencySymbol + ' 0,00'));
        } else {
            $('#summary_pph_tile').addClass('d-none');
        }

        $('#summary_biaya_lain_val').text(window.currentCurrencySymbol + ' ' + fmt(biayaLain));
        $('#summary_total_sebelum_dp_val').text(window.currentCurrencySymbol + ' ' + fmt(totalSebelumDp));
        $('#summary_dp_val').text(dpVal > 0 ? ('-' + window.currentCurrencySymbol + ' ' + fmt(dpVal)) : (window.currentCurrencySymbol + ' 0,00'));
        $('#summary_grand_total, #summary_grand_total_desktop').text(window.currentCurrencySymbol + ' ' + fmt(grandTotal));

        if (window.currentCurrencySymbol !== 'Rp') {
            $('#summary_nilai_barang_idr').text('(Rp ' + fmt(totalNilaiBarangIdr) + ')').removeClass('d-none');
            $('#summary_grand_total_idr, #summary_grand_total_idr_desktop').text('(Rp ' + fmt(grandTotalIdr) + ')').removeClass('d-none');
        } else {
            $('#summary_nilai_barang_idr').addClass('d-none').text('');
            $('#summary_grand_total_idr, #summary_grand_total_idr_desktop').addClass('d-none').text('');
        }

        window.updateProgressBar(totalQtyReceived);
    };

    // ===== RECALCULATE ALL ROW PRICES AND SUMMARY (WHEN KURS OR QTY CHANGES) =====
    window.recalculateAllPrices = function () {
        let effectiveKurs = parseFloat($('#kurs_gr_raw').val()) || (typeof window.parseKursID === 'function' ? window.parseKursID($('#kurs_gr').val()) : 0) || window.currentPoKurs || 1;
        let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

        // Update Nilai Kurs & Grand Total di Card PO Asal secara real-time
        if (window.currentCurrencySymbol !== 'Rp') {
            let kursPoFmt = fmt(window.currentPoKurs);
            let kursGrFmt = fmt(effectiveKurs);
            if (Math.abs(effectiveKurs - window.currentPoKurs) > 0.0001) {
                $('#card_kurs').html(`<span class="text-primary fw-bold">${kursGrFmt}</span> <small class="text-muted" style="font-size:0.75rem;">(PO: ${kursPoFmt})</small>`);
            } else {
                $('#card_kurs').text(kursPoFmt);
            }

            if (window.currentPoGrandTotal) {
                let poGrandTotalIdr = window.currentPoGrandTotal * effectiveKurs;
                $('#card_grand_total').html(`${window.currentCurrencySymbol} ${fmt(window.currentPoGrandTotal)}<br><small class="text-muted fw-normal" style="font-size: 0.68rem;">(Rp ${fmt(poGrandTotalIdr)})</small>`);
            }
        }

        $('#gr_details_tbody tr').each(function () {
            let row = $(this);
            let hargaSatuan = parseFloat(row.data('harga-satuan')) || 0;
            let qtyVal = parseFloat(row.find('.qty-input').val()) || 0;
            let total = qtyVal * hargaSatuan;

            let hargaIdr = hargaSatuan * effectiveKurs;
            let totalIdr = total * effectiveKurs;

            row.find('.harga-satuan-idr-val').text('Rp ' + fmt(hargaIdr));
            row.find('.total-price-idr-val').text('Rp ' + fmt(totalIdr));
        });

        window.calculateFooterSummary();
    };

    // ===== BUILD RAK SELECT OPTIONS =====
    window.buildRakSelectOptions = function (selectedId, isSplitRow = false) {
        let gudangId = $('#edit_gudang_id').val() || $('#gudang_id').val();
        if (!gudangId) {
            return `<select class="form-select form-select-sm lokasi-rak-select rounded-pill" disabled style="font-size:0.78rem;"><option value="">Pilih Gudang Dahulu</option></select>`;
        }
        let raks = (window.activeGudangRaks && Array.isArray(window.activeGudangRaks) && window.activeGudangRaks.length > 0)
            ? window.activeGudangRaks
            : ((window.gudangRaksCache && Array.isArray(window.gudangRaksCache)) ? window.gudangRaksCache : []);

        if (raks.length === 0) {
            return `<span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.75rem;"><i class="bi bi-dash-circle me-1"></i>Tanpa Rak</span>`;
        }
        let html = `<div class="d-flex align-items-center gap-1 justify-content-center">`;
        html += `<select class="form-select form-select-sm lokasi-rak-select rounded-pill" style="font-size:0.78rem;">`;
        html += `<option value="">-- Pilih No. Rak --</option>`;
        raks.forEach(function (rak) {
            let sel = (selectedId && String(selectedId) === String(rak.id)) ? 'selected' : '';
            let label = `[${rak.kode_rak}] ${rak.nama_rak}`;
            if (rak.baris || rak.kolom || rak.level) {
                let sub = [];
                if (rak.baris) sub.push('B:' + rak.baris);
                if (rak.kolom) sub.push('K:' + rak.kolom);
                if (rak.level) sub.push('L:' + rak.level);
                if (sub.length > 0) label += ' (' + sub.join('/') + ')';
            }
            html += `<option value="${rak.id}" ${sel}>${window.escapeHtml(label)}</option>`;
        });
        html += `</select>`;
        if (!isSplitRow) {
            html += `<button type="button" class="btn btn-xs btn-outline-info rounded-circle btn-split-rak flex-shrink-0" style="width:24px;height:24px;padding:0;display:inline-flex;align-items:center;justify-content:center;" title="Split barang ini ke Rak lain"><i class="bi bi-plus-lg"></i></button>`;
        } else {
            html += `<button type="button" class="btn btn-xs btn-outline-danger rounded-circle btn-remove-split-rak flex-shrink-0" style="width:24px;height:24px;padding:0;display:inline-flex;align-items:center;justify-content:center;" title="Hapus split baris ini"><i class="bi bi-dash-lg"></i></button>`;
        }
        html += `</div>`;
        return html;
    };

    // ===== FETCH GUDANG RAKS =====
    window.fetchGudangRaks = function (gudangId, callback) {
        if (!gudangId) {
            window.activeGudangRaks = [];
            window.gudangGunakanRak = false;
            if (typeof callback === 'function') callback();
            return;
        }
        $.get('/goods-receipt/lokasi-raks/' + gudangId, function (res) {
            window.gudangGunakanRak = res.gunakan_lokasi_rak;
            window.activeGudangRaks = res.raks || [];
            if (typeof callback === 'function') callback();
        }).fail(function () {
            window.activeGudangRaks = [];
            window.gudangGunakanRak = false;
            if (typeof callback === 'function') callback();
        });
    };

    // ===== LOAD PO DETAILS =====
    window.loadPODetails = function (poId, callback) {
        if (!poId) {
            $('#items_placeholder').removeClass('d-none').show();
            $('#items_card').addClass('d-none').hide();
            $('#po_info_card_wrapper').addClass('d-none').hide();
            $('#gr_details_tbody').empty();
            if (typeof callback === 'function') callback();
            return;
        }

        $('#items_placeholder').addClass('d-none').hide();
        $('#items_card').removeClass('d-none').show();
        $('#po_info_card_wrapper').removeClass('d-none').show();
        $('#gr_details_tbody').html('<tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Memuat detail PO...</td></tr>');

        $.get('/goods-receipt/po-details/' + poId, function (res) {
            if (!res || !res.po || !res.items) {
                $('#gr_details_tbody').html('<tr><td colspan="10" class="text-center text-danger py-3">Gagal memuat rincian PO.</td></tr>');
                return;
            }

            $('#po_badge_number').text('Nomor PO: ' + res.po.nomor_po);

            window.poTotalQty = parseFloat(res.po.total_qty) || 0;
            window.poTotalReceivedSoFar = parseFloat(res.po.total_received_so_far) || 0;
            window.currentCurrencySymbol = res.po.currency_symbol || 'Rp';
            window.currentPoKurs = parseFloat(res.po.kurs) || 1;
            window.currentPoSubtotal = parseFloat(res.po.subtotal) || 0;
            window.currentPoDiskonPersen = parseFloat(res.po.diskon_persen) || 0;
            window.currentPoTotalDiskon = parseFloat(res.po.total_diskon) || 0;
            window.currentPoPajakNama = res.po.pajak_nama || 'PPN';
            window.currentPoPajakTarif = parseFloat(res.po.pajak_tarif) || 0;
            window.currentPoBiayaLain = parseFloat(res.po.biaya_lain) || 0;
            window.currentPoDeskripsiBiayaLain = res.po.deskripsi_biaya_lain || 'Biaya Lain-lain';
            window.currentPoDp = parseFloat(res.po.down_payment) || 0;
            window.currentPoTotalPajak = parseFloat(res.po.total_pajak) || 0;
            window.currentPoPphTarif = parseFloat(res.po.pph_tarif) || 0;
            window.currentPoPphNominal = parseFloat(res.po.pph_nominal) || parseFloat(res.po.total_pph) || 0;
            window.currentPoPphNama = res.po.pph_nama || 'PPh';
            window.currentPoGrandTotal = parseFloat(res.po.grand_total) || 0;

            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

            $('#card_nomor_po').text(res.po.nomor_po);
            $('#card_tanggal_po').text(res.po.tanggal_po);
            $('#card_tender_name').text(res.po.tender_name || '-');
            $('#card_supplier').text(res.po.supplier_name);
            $('#card_pic_supplier').text(res.po.pic_supplier || '-');
            $('#card_telepon_supplier').text(res.po.telepon_supplier || '-');
            $('#card_kurs').text(fmt(window.currentPoKurs));

            if (window.currentCurrencySymbol === 'Rp') {
                $('#card_kurs_row').addClass('d-none');
                // IDR — kurs = 1, sembunyikan field kurs GR
                $('#kurs_gr_wrapper').hide();
                $('#kurs_gr').val('');
                $('#kurs_gr_raw').val('1');
            } else {
                $('#card_kurs_row').removeClass('d-none');
                // Non-IDR — tampilkan field kurs GR, auto-fill default persis dari kurs PO
                let currCode = res.po.currency_code || res.po.currency_symbol || '';
                $('#kurs_gr_wrapper').show();
                $('#kurs_gr_symbol').text(currCode);
                $('#kurs_gr_currency_hint').text('(' + currCode + ' → IDR)');
                $('#kurs_po_reference').text(new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 4}).format(window.currentPoKurs));

                // Default SELALU samakan dengan kurs PO saat PO dipilih
                if (typeof window.setKursField === 'function') {
                    window.setKursField('kurs_gr', 'kurs_gr_raw', window.currentPoKurs);
                } else {
                    $('#kurs_gr').val(window.currentPoKurs);
                    $('#kurs_gr_raw').val(window.currentPoKurs);
                }
            }

            let currentGudang = $('#gudang_display').val() || '-';
            $('#card_gudang_tujuan').text(currentGudang);

            $('#card_pajak_info').text(window.currentPoPajakTarif > 0 ? `${window.currentPoPajakNama} (${window.currentPoPajakTarif}%)` : 'Tanpa PPN');

            if (window.currentPoPphTarif > 0 || window.currentPoPphNominal > 0) {
                let pphText = `${window.currentPoPphNama}`;
                if (window.currentPoPphTarif > 0) {
                    pphText += ` (${window.currentPoPphTarif}%)`;
                }
                if (window.currentPoPphNominal > 0) {
                    pphText += ` - ${window.currentCurrencySymbol} ${fmt(window.currentPoPphNominal)}`;
                }
                $('#card_pph_info').text(pphText);
                $('#card_pph_row').removeClass('d-none');
            } else {
                $('#card_pph_row').addClass('d-none');
                $('#card_pph_info').text('-');
            }

            $('#card_dp_info').text(window.currentPoDp > 0 ? `${window.currentCurrencySymbol} ${fmt(window.currentPoDp)}` : 'Rp 0,00 (Tanpa DP)');
            $('#card_biaya_lain_info').text(window.currentPoBiayaLain > 0 ? `${window.currentCurrencySymbol} ${fmt(window.currentPoBiayaLain)} (${window.currentPoDeskripsiBiayaLain})` : '-');
            $('#card_eta').text(res.po.eta || '-');

            let lateHtml = res.po.status_keterlambatan === 'Terlambat' ? '<span class="badge bg-danger">Terlambat</span>' : '<span class="badge bg-success">Ontime</span>';
            $('#card_status_keterlambatan').html(lateHtml);

            let statusPOHtml = '';
            if (res.po.status_po === 'Selesai') {
                statusPOHtml = '<span class="badge bg-success">Selesai</span>';
            } else if (res.po.status_po === 'Sebagian (Partial)') {
                statusPOHtml = '<span class="badge bg-warning text-white">Sebagian (Partial)</span>';
            } else {
                statusPOHtml = '<span class="badge bg-secondary">Belum Diterima</span>';
            }
            $('#card_status_po').html(statusPOHtml);
            $('#card_total_item').text(res.po.total_item);
            $('#card_total_qty').text(res.po.total_qty);
            $('#card_grand_total').text(res.po.currency_symbol + ' ' + fmt(res.po.grand_total));

            let html = '';
            res.items.forEach(function (item) {
                let fotoUrl = item.foto_path ? '/storage/' + item.foto_path : '';
                let isFull = item.qty_sisa <= 0;
                let initialQty = '';
                let initialQtyVal = 0;
                let diskonItem = parseFloat(item.diskon_item || 0);
                let diskonPersen = parseFloat(item.diskon_persen || 0);
                let tarifPpn = parseFloat(item.tarif_ppn || 0);
                let namaPpn = item.nama_ppn || (tarifPpn > 0 ? 'PPN ' + tarifPpn + '%' : 'Non PPN');

                let initialTotal = initialQtyVal * Math.max(0, item.harga_satuan - diskonItem);
                let hargaIdr = item.harga_satuan * window.currentPoKurs;
                let diskonIdr = diskonItem * window.currentPoKurs;
                let totalIdr = initialTotal * window.currentPoKurs;
                let fmtH = fmt(item.harga_satuan);
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
                    ? `<span class="badge bg-warning bg-opacity-10 text-warning px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.72rem;">${window.escapeHtml(namaPpn)}</span>` 
                    : `<span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">0%</span>`;

                let serialBtnHtml = '';
                if (item.tipe_item === 'Aset' && !isFull) {
                    serialBtnHtml = `<div class="mt-1"><button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-0.5 btn-trigger-serial" style="font-size: 0.7rem; font-weight: 600;"><i class="bi bi-upc-scan me-1"></i> Serial & Tag Aset</button><span class="badge rounded-pill d-none serial-filled-badge" style="font-size: 0.7rem; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">Sudah Diisi</span></div>`;
                } else if (item.tipe_item === 'Jasa') {
                    serialBtnHtml = `<div class="mt-1"><span class="badge rounded-pill" style="font-size: 0.7rem; background-color: rgba(100, 116, 139, 0.1); color: #64748b;">Jasa / Layanan</span></div>`;
                }

                let rakSelectHtml = window.buildRakSelectOptions();

                let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : n.toString(); };

                let defaultSatuanId = item.default_satuan_id;
                let defaultFaktor = item.po_faktor_pengali || 1;
                let units = item.available_units || [];
                let satuanCellHtml = '';

                if (units.length <= 1) {
                    let displaySatuan = (units.length === 1 && units[0].nama_satuan) ? units[0].nama_satuan : (item.po_satuan_nama || 'UNT');
                    satuanCellHtml = `
                        <div class="d-inline-flex align-items-center justify-content-center px-3 py-1.5 rounded-pill gr-satuan-badge" title="Satuan: ${window.escapeHtml(displaySatuan)}">
                            <i class="bi bi-tag-fill me-1.5" style="font-size: 0.72rem;"></i>
                            <span class="fw-bold font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">${window.escapeHtml(displaySatuan)}</span>
                        </div>
                        <input type="hidden" class="satuan-id-val" value="${defaultSatuanId}">
                        <input type="hidden" class="faktor-pengali-val" value="${defaultFaktor}">
                    `;
                } else {
                    let satuanOptionsHtml = '';
                    units.forEach(function (u) {
                        let selectedAttr = (u.satuan_id == defaultSatuanId) ? 'selected' : '';
                        let optLabel = u.label || u.nama_satuan;
                        satuanOptionsHtml += `<option value="${u.satuan_id}" data-faktor="${u.faktor_pengali}" data-nama="${u.nama_satuan}" ${selectedAttr}>${window.escapeHtml(optLabel)}</option>`;
                    });
                    satuanCellHtml = `
                        <select class="form-select form-select-sm satuan-select gr-satuan-select rounded-pill px-2.5 py-1 text-center" ${isFull ? 'disabled' : ''}>
                            ${satuanOptionsHtml}
                        </select>
                        <input type="hidden" class="satuan-id-val" value="${defaultSatuanId}">
                        <input type="hidden" class="faktor-pengali-val" value="${defaultFaktor}">
                    `;
                }

                html += `
                    <tr data-po-detail-id="${item.po_detail_id}" 
                        data-barang-id="${item.barang_id}" 
                        data-po-harga-satuan="${item.harga_satuan}"
                        data-po-faktor="${item.po_faktor_pengali || 1}"
                        data-satuan-faktor="${defaultFaktor}"
                        data-harga-satuan="${item.harga_satuan}" 
                        data-qty-sisa-dasar="${item.qty_sisa_dasar}"
                        data-qty-dipesan-dasar="${item.qty_dipesan_dasar}"
                        data-dasar-satuan-nama="${item.dasar_satuan_nama}"
                        data-po-satuan-nama="${item.po_satuan_nama}"
                        data-diskon-item="${diskonItem}"
                        data-diskon-persen="${diskonPersen}"
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
                            ${item.po_faktor_pengali > 1 ? `<br><small class="text-muted" style="font-size:0.68rem;">(${fmtQ(item.qty_dipesan_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
                        </td>
                        <td data-label="Qty Sudah Diterima" class="text-center fw-medium text-secondary text-nowrap">
                            ${fmtQ(item.qty_diterima)} <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace" style="font-size:0.75rem;">${item.po_satuan_nama}</span>
                            ${item.po_faktor_pengali > 1 ? `<br><small class="text-muted" style="font-size:0.68rem;">(${fmtQ(item.qty_diterima_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
                        </td>
                        <td data-label="Qty Sisa" class="text-center fw-bold text-info text-nowrap">
                            <span class="qty-sisa-val">${fmtQ(item.qty_sisa)}</span> <span class="badge bg-info bg-opacity-10 text-info font-monospace sisa-satuan-label" style="font-size:0.75rem;">${item.po_satuan_nama}</span>
                            ${item.po_faktor_pengali > 1 ? `<br><small class="text-muted sisa-dasar-label" style="font-size:0.68rem;">(${fmtQ(item.qty_sisa_dasar)} ${item.dasar_satuan_nama})</small>` : ''}
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
                            <small class="text-muted d-block row-ppn-preview mt-0.5" style="font-size: 0.68rem;"></small>
                        </td>
                        <td data-label="Qty Diterima" class="text-center">
                            <input type="number" step="any" class="form-control form-control-sm text-end qty-input rounded-pill"
                                value="" placeholder="0" min="0" max="${item.qty_sisa}" data-max-sisa="${item.qty_sisa}" style="width: 90px; display: inline-block;" ${isFull ? 'disabled' : ''}>
                            <small class="text-muted d-block mt-0.5 konversi-live-hint" style="font-size: 0.68rem;"></small>
                        </td>
                        <td data-label="Qty Reject" class="text-center">
                            <input type="number" step="any" class="form-control form-control-sm text-end qty-reject-input rounded-pill"
                                value="0" min="0" max="${item.qty_sisa}" data-max-sisa="${item.qty_sisa}" style="width: 90px; display: inline-block;" ${isFull ? 'disabled' : ''}>
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

            if (!html || res.items.length === 0) {
                $('#gr_details_tbody').html('<tr><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-info-circle fs-4 d-block mb-1"></i> Tidak ada rincian barang untuk PO ini.</td></tr>');
            } else {
                $('#gr_details_tbody').html(html);
                if (typeof window.initGrTableDragScroll === 'function') {
                    window.initGrTableDragScroll();
                }
            }

            if (typeof window.calculateFooterSummary === 'function') {
                window.calculateFooterSummary();
            }

            // Asynchronously update Raks if Gudang is selected
            let gudangId = $('#gudang_id').val();
            if (gudangId) {
                window.fetchGudangRaks(gudangId, function () {
                    $('#gr_details_tbody tr').each(function () {
                        let cell = $(this).find('.lokasi-rak-cell');
                        let currentVal = cell.find('.lokasi-rak-select').val();
                        cell.html(window.buildRakSelectOptions(currentVal));
                    });
                    if (typeof callback === 'function') callback();
                });
            } else {
                if (typeof callback === 'function') callback();
            }
        }).fail(function (xhr) {
            console.error('AJAX Error PO Details:', xhr);
            $('#gr_details_tbody').html('<tr><td colspan="10" class="text-center text-danger py-3"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat rincian PO. Silakan coba pilih ulang.</td></tr>');
        });
    };

    // ===== ALL INPUT EVENT HANDLERS =====
    $(document).ready(function () {

        // Auto number toggle
        $('#auto_number_toggle').on('change', function () {
            if ($(this).is(':checked')) {
                $('#auto_number_label').removeClass('d-none');
                $('#nomor_gr_input').addClass('d-none').val('').removeAttr('required');
            } else {
                $('#auto_number_label').addClass('d-none');
                $('#nomor_gr_input').removeClass('d-none').attr('required', 'required').focus();
            }
        });

        // PO change → load details
        $('#po_id').on('change', function () {
            window.loadPODetails($(this).val());
        });

        // Terima Semua Sisa
        $(document).on('click', '#btn_fill_all_remaining, #btn_receive_all_items', function () {
            $('#gr_details_tbody tr').each(function () {
                let qtyInput = $(this).find('.qty-input');
                let rejectInput = $(this).find('.qty-reject-input');
                if (!qtyInput.is(':disabled')) {
                    let maxVal = parseFloat(qtyInput.attr('max')) || 0;
                    qtyInput.val(maxVal);
                    rejectInput.val(0);
                    qtyInput.trigger('change');
                }
            });
            calculateFooterSummary();
        });

        // Kosongkan Semua
        $(document).on('click', '#btn_clear_all_items', function () {
            $('#gr_details_tbody tr').each(function () {
                let qtyInput = $(this).find('.qty-input');
                let rejectInput = $(this).find('.qty-reject-input');
                if (!qtyInput.is(':disabled')) {
                    qtyInput.val(0);
                    rejectInput.val(0);
                    qtyInput.trigger('change');
                }
            });
            calculateFooterSummary();
        });

        // Rak click check — gudang belum dipilih
        $(document).on('click mousedown', '.lokasi-rak-cell, .lokasi-rak-select', function (e) {
            let gudangId = $('#edit_gudang_id').val() || $('#gudang_id').val();
            if (!gudangId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: 'Gudang Belum Dipilih!',
                    text: 'Silakan cari & pilih Gudang Penerima terlebih dahulu sebelum menentukan Lokasi Rak.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Gudang Sekarang',
                    confirmButtonColor: '#f59e0b',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup'
                }).then((res) => {
                    if (res.isConfirmed) { window.openGudangPickerModal(); }
                });
                return false;
            }
        });

        // Set Rak Massal
        $('#btn_set_mass_rak').on('click', function () {
            let gudangId = $('#edit_gudang_id').val() || $('#gudang_id').val();
            if (!gudangId) {
                Swal.fire({
                    title: 'Gudang Belum Dipilih!',
                    text: 'Silakan cari & pilih Gudang Penerima terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Gudang Sekarang',
                    confirmButtonColor: '#f59e0b',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup'
                }).then((res) => {
                    if (res.isConfirmed) { window.openGudangPickerModal(); }
                });
                return;
            }
            if (!window.activeGudangRaks || window.activeGudangRaks.length === 0) {
                Swal.fire('Perhatian!', 'Gudang ini tidak memiliki data lokasi rak aktif.', 'warning');
                return;
            }
            let optionsObj = {};
            window.activeGudangRaks.forEach(function (rak) {
                let label = `[${rak.kode_rak}] ${rak.nama_rak}`;
                if (rak.baris || rak.kolom || rak.level) {
                    let sub = [];
                    if (rak.baris) sub.push('B:' + rak.baris);
                    if (rak.kolom) sub.push('K:' + rak.kolom);
                    if (rak.level) sub.push('L:' + rak.level);
                    if (sub.length > 0) label += ' (' + sub.join('/') + ')';
                }
                optionsObj[rak.id] = label;
            });
            Swal.fire({
                title: 'Pilih Rak Massal',
                text: 'Terapkan lokasi rak ini ke semua barang dalam daftar:',
                input: 'select',
                inputOptions: optionsObj,
                inputPlaceholder: '-- Pilih No. Rak --',
                showCancelButton: true,
                confirmButtonText: 'Terapkan ke Semua',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $('.lokasi-rak-select').val(result.value);
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Lokasi rak berhasil diterapkan ke seluruh item!', timer: 1200, showConfirmButton: false });
                }
            });
        });

        // Split Rak handler
        $(document).on('click', '.btn-split-rak', function (e) {
            let gudangId = $('#edit_gudang_id').val() || $('#gudang_id').val();
            if (!gudangId) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: 'Gudang Belum Dipilih!',
                    text: 'Silakan cari & pilih Gudang Penerima terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Gudang Sekarang',
                    confirmButtonColor: '#f59e0b',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup'
                }).then((res) => {
                    if (res.isConfirmed) { window.openGudangPickerModal(); }
                });
                return false;
            }
            let row = $(this).closest('tr');
            let clonedRow = row.clone();
            clonedRow.addClass('is-split-row');
            if (clonedRow.find('.split-badge').length === 0) {
                clonedRow.find('.product-img-wrapper').next('div').append('<br><span class="badge bg-info bg-opacity-10 text-info mt-1 d-inline-block split-badge" style="font-size:0.68rem;"><i class="bi bi-diagram-2 me-1"></i>Split Baris</span>');
            }
            clonedRow.find('.qty-input').val(0);
            clonedRow.find('.qty-reject-input').val(0);
            clonedRow.find('.total-price-val').html(window.currentCurrencySymbol + ' 0,00');
            let rakCell = clonedRow.find('.lokasi-rak-cell');
            let currentRakId = row.find('.lokasi-rak-select').val();
            rakCell.html(window.buildRakSelectOptions(currentRakId, true));
            row.after(clonedRow);
            calculateFooterSummary();
        });

        // Remove Split Rak handler
        $(document).on('click', '.btn-remove-split-rak', function () {
            $(this).closest('tr').remove();
            calculateFooterSummary();
        });

        // Lightbox
        $(document).on('click', '.zoomable-img', function () {
            let src = $(this).attr('src');
            if (src) {
                $('#lightboxImg').attr('src', src);
                $('#simpleLightbox').css('display', 'flex').hide().fadeIn('fast');
            }
        });
        $(document).on('click', '#closeLightbox, #simpleLightbox', function (e) {
            if (e.target === this || e.target.id === 'closeLightbox') {
                $('#simpleLightbox').fadeOut('fast');
            }
        });

        // Prevent focus stealing from select2
        document.addEventListener('focusin', function (e) {
            if (e.target.closest('.select2-container') || e.target.closest('.select2-dropdown') || e.target.closest('.select2-search')) {
                e.stopImmediatePropagation();
            }
        });

        // Unit change handler (Konversi Satuan)
        $(document).on('change', '#gr_details_tbody .satuan-select', function () {
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
            let qtySisaDasar = parseFloat(row.data('qty-sisa-dasar')) || 0;

            // Recalculate price in currently selected unit
            let newUnitPrice = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * currentFaktor) : poHargaSatuan;
            row.data('harga-satuan', newUnitPrice);

            // Recalculate remaining qty in currently selected unit
            let newRemaining = (currentFaktor > 0) ? (qtySisaDasar / currentFaktor) : qtySisaDasar;
            let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : parseFloat(n.toFixed(4)).toString(); };
            row.find('.qty-sisa-val').text(fmtQ(newRemaining));
            row.find('.sisa-satuan-label').text(satuanNama);

            let qtyInput = row.find('.qty-input');
            let rejectInput = row.find('.qty-reject-input');
            qtyInput.attr('max', newRemaining).data('max-sisa', newRemaining);
            rejectInput.attr('max', newRemaining).data('max-sisa', newRemaining);

            // Update Harga Satuan column text
            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
            let effectiveKurs = parseFloat($('#kurs_gr_raw').val()) || window.currentPoKurs || 1;
            let newPriceIdr = newUnitPrice * effectiveKurs;
            let hargaHtml = `${window.currentCurrencySymbol} ${fmt(newUnitPrice)}`;
            if (window.currentCurrencySymbol !== 'Rp') {
                hargaHtml += `<br><small class="text-muted harga-satuan-idr-val" style="font-size: 0.72rem;">Rp ${fmt(newPriceIdr)}</small>`;
            }
            row.find('td[data-label="Harga Satuan"]').html(hargaHtml);

            // Trigger Qty input change to re-evaluate conversion hint & row total
            qtyInput.trigger('input');
        });

        // Qty input validation & dynamic subtotal update
        $(document).on('input change', '#gr_details_tbody .qty-input, #gr_details_tbody .qty-reject-input', function () {
            let row = $(this).closest('tr');
            let qtyInput = row.find('.qty-input');
            let rejectInput = row.find('.qty-reject-input');
            let qtyVal = parseFloat(qtyInput.val()) || 0;
            let rejectVal = parseFloat(rejectInput.val()) || 0;

            let currentFaktor = parseFloat(row.find('.faktor-pengali-val').val()) || parseFloat(row.data('satuan-faktor')) || 1.0;
            let dasarSatuanNama = row.data('dasar-satuan-nama') || '';
            let poFaktor = parseFloat(row.data('po-faktor')) || 1.0;
            let poSatuanNama = row.data('po-satuan-nama') || '';
            let maxSisaDasar = parseFloat(row.data('qty-sisa-dasar')) || 0;

            let poDetailId = row.find('.po-detail-id-val').val();
            let totalQtyDasarForPoDetail = 0;
            $('#gr_details_tbody tr').each(function () {
                if ($(this).find('.po-detail-id-val').val() === poDetailId) {
                    let q = parseFloat($(this).find('.qty-input').val()) || 0;
                    let r = parseFloat($(this).find('.qty-reject-input').val()) || 0;
                    let f = parseFloat($(this).find('.faktor-pengali-val').val()) || parseFloat($(this).data('satuan-faktor')) || 1.0;
                    totalQtyDasarForPoDetail += ((q + r) * f);
                }
            });

            if (qtyVal < 0) { qtyVal = 0; qtyInput.val(0); }
            if (rejectVal < 0) { rejectVal = 0; rejectInput.val(0); }

            let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : parseFloat(n.toFixed(4)).toString(); };

            if (totalQtyDasarForPoDetail > (maxSisaDasar + 0.0001)) {
                let barangNama = row.find('span.fw-semibold').first().text().trim() || 'Barang';
                let maxInCurrentUnit = (currentFaktor > 0) ? (maxSisaDasar / currentFaktor) : maxSisaDasar;

                Swal.fire({
                    title: 'Peringatan Kelebihan Qty!',
                    html: `Total kuantitas diterima & reject untuk <b>${window.escapeHtml(barangNama)}</b> melebihi sisa PO!<br><small class="text-muted">Batas sisa: <b>${fmtQ(maxInCurrentUnit)}</b> (setara <b>${fmtQ(maxSisaDasar)} ${window.escapeHtml(dasarSatuanNama)}</b>).</small>`,
                    icon: 'warning',
                    confirmButtonText: 'Saya Mengerti',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill px-4' }
                });

                let overflowDasar = totalQtyDasarForPoDetail - maxSisaDasar;
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

            let hargaSatuan = parseFloat(row.data('harga-satuan')) || 0;
            let total = qtyVal * hargaSatuan;
            let effectiveKurs = parseFloat($('#kurs_gr_raw').val()) || (typeof window.parseKursID === 'function' ? window.parseKursID($('#kurs_gr').val()) : 0) || window.currentPoKurs || 1;
            let totalIdr = total * effectiveKurs;
            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);

            if (window.currentCurrencySymbol !== 'Rp') {
                row.find('.total-price-val').html(`${window.currentCurrencySymbol} ${fmt(total)}<br><small class="text-muted total-price-idr-val" style="font-size: 0.72rem;">Rp ${fmt(totalIdr)}</small>`);
            } else {
                row.find('.total-price-val').text(window.currentCurrencySymbol + ' ' + fmt(total));
            }

            calculateFooterSummary();
        });

        // Reset modal on close
        $('#modalTambahGR').on('hidden.bs.modal', function () {
            $('#modalTambahGRLabel').html('Penerimaan Barang Baru (Goods Receipt)');
            $('#gr_id').val('');
            $('#formTambahGR')[0].reset();
            $('#po_id').val('');
            $('#po_display').val('');
            $('#gudang_id').val('');
            $('#gudang_display').val('');
            $('#gr_details_tbody').empty();
            $('#items_placeholder').removeClass('d-none');
            $('#items_card').addClass('d-none');
            $('#po_info_card_wrapper').addClass('d-none');
            $('#auto_number_toggle').prop('checked', true).trigger('change');
            $('#card_nomor_po').text('-');
            $('#card_tanggal_po').text('-');
            $('#card_supplier').text('-');
            $('#card_pic_supplier').text('-');
            $('#card_telepon_supplier').text('-');
            $('#card_gudang_tujuan').text('-');
            $('#card_eta').text('-');
            $('#card_status_keterlambatan').html('-');
            $('#card_status_po').html('-');
            $('#card_total_item').text('-');
            $('#card_total_qty').text('-');
            $('#card_grand_total').text('-');
            $('#card_pph_row').addClass('d-none');
            $('#card_pph_info').text('-');
            $('#summary_pph_tile').addClass('d-none');
            $('#summary_pph_val').text('Rp 0,00');
            window.currentPoPphTarif = 0;
            window.currentPoPphNominal = 0;
            window.currentPoPphNama = '';
            $('#card_progress_text').text('0%');
            $('#card_progress_bar').css('width', '0%').attr('aria-valuenow', 0);
            $('#card_progress_ratio').text('Sudah diterima: 0 / 0');
            $('#is_draft').val('0');
            $('#kurs_gr').val('');
            $('#kurs_gr_raw').val('');
            $('#kurs_gr_wrapper').hide();
        });

        // Gudang change → fetch raks
        $('#gudang_id').on('change', function () {
            let gudangId = $(this).val();
            let gudangNama = $('#gudang_display').val() || '-';
            $('#card_gudang_tujuan').text(gudangNama);
            fetchGudangRaks(gudangId, function () {
                $('#gr_details_tbody tr').each(function () {
                    let cell = $(this).find('.lokasi-rak-cell');
                    let currentVal = cell.find('.lokasi-rak-select').val();
                    cell.html(buildRakSelectOptions(currentVal));
                });
            });
        });

        // ===== SERIAL ASSET =====
        let currentActiveRowForSerials = null;

        $(document).on('click', '.btn-trigger-serial', function () {
            let row = $(this).closest('tr');
            let barangName = row.find('.fw-semibold').text();
            let qtyInput = row.find('.qty-input');
            let qty = parseInt(qtyInput.val()) || 0;

            if (qty <= 0) {
                Swal.fire('Perhatian!', 'Silakan isi Qty Penerimaan Baru terlebih dahulu sebelum mengisi serial number!', 'warning');
                return;
            }

            currentActiveRowForSerials = row;
            $('#modal_asset_name_label').text(barangName);

            let existingSerials = row.data('serials') || [];
            let existingCodes = row.data('asset-codes') || [];
            let container = $('#serial_inputs_container');
            container.empty();

            for (let i = 0; i < qty; i++) {
                let sVal = existingSerials[i] || '';
                let cVal = existingCodes[i] || '';
                container.append(`
                    <div class="col-12 py-2 border-bottom border-light">
                        <span class="fw-bold text-secondary small d-block mb-1.5">Unit #${i + 1}</span>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0.5">Serial Number</label>
                                <input type="text" class="form-control form-control-sm rounded-pill serial-input-field" placeholder="Masukkan S/N..." value="${sVal}">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small mb-0.5">Kode Aset Kustom (Opsional)</label>
                                <input type="text" class="form-control form-control-sm rounded-pill asset-code-field" placeholder="Auto-generated..." value="${cVal}">
                            </div>
                        </div>
                    </div>
                `);
            }
            $('#modalSerialAsset').modal('show');
        });

        $('#btn_save_serials').on('click', function () {
            if (!currentActiveRowForSerials) return;
            let serials = [];
            let assetCodes = [];
            $('#serial_inputs_container .col-12').each(function () {
                serials.push($(this).find('.serial-input-field').val().trim());
                assetCodes.push($(this).find('.asset-code-field').val().trim());
            });
            currentActiveRowForSerials.data('serials', serials);
            currentActiveRowForSerials.data('asset-codes', assetCodes);
            let filled = serials.some(s => s !== '') || assetCodes.some(c => c !== '');
            if (filled) {
                currentActiveRowForSerials.find('.serial-filled-badge').removeClass('d-none');
            } else {
                currentActiveRowForSerials.find('.serial-filled-badge').addClass('d-none');
            }
            $('#modalSerialAsset').modal('hide');
        });

        // Adjust serial inputs when qty changes
        $(document).on('change', '#gr_details_tbody .qty-input', function () {
            let row = $(this).closest('tr');
            let qty = parseInt($(this).val()) || 0;
            let serials = row.data('serials') || [];
            let assetCodes = row.data('asset-codes') || [];
            if (serials.length > qty) {
                row.data('serials', serials.slice(0, qty));
                row.data('asset-codes', assetCodes.slice(0, qty));
            }
            if (serials.length === 0 && assetCodes.length === 0) {
                row.find('.serial-filled-badge').addClass('d-none');
            }
        });

        // Single submission guard for Input Modal
        let isInputSubmitting = false;

        $(document).on('click', '#btn_save_draft, .btn-action-draft', function (e) {
            e.preventDefault();
            if (isInputSubmitting) return;
            $('#is_draft').val('1');
            $('#formTambahGR').trigger('submit');
        });

        $(document).on('click', '#btn_save_gr, .btn-action-posting', function (e) {
            e.preventDefault();
            if (isInputSubmitting) return;
            $('#is_draft').val('0');
            $('#formTambahGR').trigger('submit');
        });

        // ===== SUBMIT GR =====
        $('#formTambahGR').on('submit', function (e) {
            e.preventDefault();
            if (isInputSubmitting) return;

            let gudangId = $('#gudang_id').val();
            if (!gudangId) {
                Swal.fire({
                    title: 'Gudang Belum Dipilih!',
                    text: 'Silakan cari & pilih Gudang Penerima terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Gudang Sekarang',
                    confirmButtonColor: '#f59e0b',
                    showCancelButton: true,
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) { window.openGudangPickerModal(); }
                });
                return;
            }

            let rows = $('#gr_details_tbody tr');
            if (rows.length === 0) {
                Swal.fire('Gagal!', 'Daftar barang kosong!', 'warning');
                return;
            }

            let items = [];
            let totalReceived = 0;
            let totalReject = 0;
            rows.each(function () {
                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                let qtyReject = parseFloat($(this).find('.qty-reject-input').val()) || 0;
                if (qty > 0 || qtyReject > 0) {
                    items.push({
                        po_detail_id: $(this).find('.po-detail-id-val').val(),
                        barang_id: $(this).find('.barang-id-val').val(),
                        lokasi_rak_id: $(this).find('.lokasi-rak-select').val() || null,
                        satuan_id: $(this).find('.satuan-id-val').val() || $(this).find('.satuan-select').val() || null,
                        faktor_pengali: parseFloat($(this).find('.faktor-pengali-val').val()) || parseFloat($(this).data('satuan-faktor')) || 1.0,
                        jumlah: qty,
                        jumlah_reject: qtyReject,
                        serials: $(this).data('serials') || [],
                        asset_codes: $(this).data('asset-codes') || []
                    });
                    totalReceived += qty;
                    totalReject += qtyReject;
                }
            });

            if (items.length === 0 || (totalReceived === 0 && totalReject === 0)) {
                Swal.fire('Gagal!', 'Kuantitas penerimaan barang atau reject tidak boleh kosong!', 'warning');
                return;
            }

            isInputSubmitting = true;
            let submitBtn = $('#btn_save_gr');
            let draftBtn = $('#btn_save_draft');
            submitBtn.prop('disabled', true);
            draftBtn.prop('disabled', true);

            let isDraftVal = $('#is_draft').val() === '1' ? 1 : 0;
            if (isDraftVal) {
                draftBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Draft...');
            } else {
                submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan & Posting...');
            }

            let payload = {
                tanggal_gr: $('#tanggal_gr').val(),
                po_id: $('#po_id').val(),
                gudang_id: $('#gudang_id').val(),
                nomor_surat_jalan: $('#nomor_surat_jalan').val(),
                keterangan: $('#keterangan').val(),
                kurs: parseFloat($('#kurs_gr_raw').val()) || window.currentPoKurs || 1,
                auto_number: $('#auto_number_toggle').is(':checked') ? 1 : 0,
                nomor_gr: $('#nomor_gr_input').val(),
                is_draft: isDraftVal,
                items: items
            };

            $.ajax({
                url: "{{ route('gr.store') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    isInputSubmitting = false;
                    $('#modalTambahGR').modal('hide');
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Simpan & Posting');
                    draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-code me-1"></i>Simpan Draft');
                    Swal.fire({
                        title: 'Berhasil!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false
                    }).then(() => { reloadTableGR(); });
                },
                error: function (err) {
                    isInputSubmitting = false;
                    submitBtn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Simpan & Posting');
                    draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-code me-1"></i>Simpan Draft');
                    let errorMsg = 'Gagal menyimpan penerimaan barang.';
                    if (err.status === 403) {
                        errorMsg = '<b>Akses Ditolak:</b> Akun Anda tidak memiliki izin untuk membuat Penerimaan Barang.';
                    } else if (err.status === 422 && err.responseJSON) {
                        if (err.responseJSON.errors) {
                            errorMsg = '<b>Validasi Gagal:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) { errorMsg += '• ' + val[0] + '<br>'; });
                        } else if (err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                }
            });
        });

    }); // end document.ready [modal_input]
</script>
@endpush
