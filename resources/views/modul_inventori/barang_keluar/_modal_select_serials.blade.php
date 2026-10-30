<!-- Modal Pemilihan & Pengalokasian Serial Number - Goods Issue (Barang Keluar) -->
<div class="modal modal-blur fade" id="modalSelectSerialsGI" tabindex="-1" aria-labelledby="modalSelectSerialsGITitle" aria-hidden="true" style="z-index: 1070 !important;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl gi-modal-sn-dialog" style="z-index: 1071 !important;">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden gi-modal-container">
            
            <!-- Modal Header -->
            <div class="modal-header px-3 px-sm-4 py-2.5 py-sm-3.5 bg-danger bg-gradient text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="d-flex align-items-center justify-content-center bg-white text-danger fs-5 flex-shrink-0 shadow-sm rounded-3" style="width: 36px; height: 36px;">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 flex-wrap">
                            <h6 class="modal-title fw-bold mb-0 text-white fs-6" id="modalSelectSerialsGITitle">Alokasi Serial Number</h6>
                            <span class="badge bg-white text-danger border border-white rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.65rem; font-weight: 700;">GOODS ISSUE</span>
                        </div>
                        <div class="text-white-50 small mt-0.5 d-none d-sm-block" id="modal_gi_sn_subtitle" style="font-size: 0.78rem;">Pilih nomor seri unit yang akan dikeluarkan secara fisik dari gudang.</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body with Responsive Spacing -->
            <div class="modal-body p-2.5 p-sm-4 d-flex flex-column gap-3 gap-sm-4" style="overflow-y: auto; max-height: calc(85vh - 120px);">
                
                <!-- Target & Status Alokasi Bento Card -->
                <div class="card border rounded-4 gi-sn-bento-card">
                    <div class="card-body p-2.5 p-sm-3.5 p-md-4">
                        <div class="row g-2 g-sm-3 align-items-center">
                            <div class="col-12 col-md-7">
                                <div class="d-flex align-items-center gap-2.5 gap-sm-3">
                                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.4px;">Barang yang Dikeluarkan:</div>
                                        <h6 class="fw-bold text-dark mb-0.5 text-truncate fs-6" id="modal_gi_sn_barang_nama">-</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-0.5" id="modal_gi_sn_barang_sku" style="font-size: 0.72rem;">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5 text-md-end pt-1 pt-md-0 border-top border-top-md-0">
                                <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2 flex-wrap">
                                    <div class="text-muted fw-semibold text-uppercase d-md-none" style="font-size: 0.68rem;">Kebutuhan:</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="bg-white border rounded-3 px-2.5 py-1 font-monospace fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="modal_gi_sn_counter_badge" style="font-size: 0.8rem;">
                                            <span class="small text-muted d-none d-sm-inline">Terpilih:</span>
                                            <span id="modal_gi_sn_allocated_num" class="text-danger fw-bold">0</span>
                                            <span class="text-muted">/</span>
                                            <span id="modal_gi_sn_target_num" class="text-dark fw-bold">0</span>
                                            <span class="small text-muted">Unit</span>
                                        </div>
                                        <div id="modal_gi_sn_status_indicator"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nav Tabs: Horizontal on all viewports, side-by-side -->
                <div>
                    <ul class="nav nav-pills nav-fill flex-row gap-1.5 gap-sm-2" id="giSnTab" role="tablist">
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link active text-center d-flex align-items-center justify-content-center gap-1.5 text-truncate w-100" id="tab-gi-stok-sn" data-bs-toggle="pill" data-bs-target="#panel-gi-stok-sn" type="button" role="tab">
                                <i class="bi bi-box-seam flex-shrink-0"></i>
                                <span class="text-truncate"><span class="d-none d-sm-inline">Pilih dari </span>Stok (<span id="modal_gi_sn_stock_available_count">0</span><span class="d-none d-sm-inline"> Tersedia</span>)</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill" role="presentation">
                            <button class="nav-link text-center d-flex align-items-center justify-content-center gap-1.5 text-truncate w-100" id="tab-gi-manual-sn" data-bs-toggle="pill" data-bs-target="#panel-gi-manual-sn" type="button" role="tab">
                                <i class="bi bi-upc-scan flex-shrink-0"></i>
                                <span class="text-truncate"><span class="d-none d-sm-inline">Input </span>Manual / Scan</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content Area -->
                <div class="tab-content" id="giSnTabContent">
                    
                    <!-- Panel 1: Dari Stok Gudang -->
                    <div class="tab-pane fade show active" id="panel-gi-stok-sn" role="tabpanel">
                        <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                            <!-- Toolbar Pencarian & Aksi Responsif -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-2.5">
                                <div class="input-group flex-grow-1 shadow-xs">
                                    <span class="input-group-text bg-light border-end-0 text-muted py-1.5 px-2.5"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="modal_gi_sn_search_stock" placeholder="Cari serial / batch..." style="height: 38px; font-size: 0.85rem;">
                                </div>
                                <div class="d-flex gap-1.5 flex-shrink-0">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 fw-semibold text-nowrap d-inline-flex align-items-center justify-content-center gap-1 shadow-xs flex-fill flex-sm-grow-0" id="btn_modal_gi_sn_auto_select" style="font-size: 0.78rem;">
                                        <i class="bi bi-magic"></i>
                                        <span>Pilih Otomatis</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1.5 small text-nowrap d-inline-flex align-items-center justify-content-center gap-1 shadow-xs flex-fill flex-sm-grow-0" id="btn_modal_gi_sn_clear_selection" style="font-size: 0.78rem;">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        <span>Reset</span>
                                    </button>
                                </div>
                            </div>

                            <!-- List Serial Numbers dari DB -->
                            <div class="gi-stock-scroll-box" id="modal_gi_sn_stock_list_container">
                                <div class="text-center py-4 text-muted small fst-italic" id="modal_gi_sn_stock_loading">
                                    <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div> Memuat serial number dari gudang...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Input Manual / Barcode Scan -->
                    <div class="tab-pane fade" id="panel-gi-manual-sn" role="tabpanel">
                        <div class="d-flex flex-column gap-2.5 gap-sm-3.5">
                            <div id="modal_gi_sn_manual_alert_container">
                                <div class="alert alert-danger-subtle border-danger-subtle d-flex align-items-center gap-2.5 py-2 px-3 rounded-3 mb-0" id="modal_gi_sn_manual_alert" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-check fs-5 text-danger flex-shrink-0"></i>
                                    <div>
                                        <span class="fw-bold text-danger-emphasis">Validasi Aktif:</span> Nomor seri wajib terdaftar di gudang untuk produk terpilih &amp; berstatus <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">Tersedia</span>.
                                    </div>
                                </div>
                            </div>

                            <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center justify-content-between flex-wrap gap-1">
                                        <span style="font-size: 0.82rem;"><i class="bi bi-upc-scan text-danger me-1"></i> Scan Barcode Fisik / Input Cepat:</span>
                                        <span class="badge bg-secondary-subtle text-muted font-monospace px-1.5 py-0.5" style="font-size: 0.68rem;">ENTER / SCAN</span>
                                    </label>
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <div class="input-group shadow-xs flex-grow-1">
                                            <span class="input-group-text bg-light text-muted px-2.5 py-1"><i class="bi bi-barcode"></i></span>
                                            <input type="text" class="form-control font-monospace ps-2" id="modal_gi_sn_quick_scan_input" placeholder="Scan barcode / ketik serial..." style="height: 38px; font-size: 0.84rem;">
                                            <button type="button" class="btn btn-danger px-3 fw-bold d-inline-flex align-items-center gap-1 flex-shrink-0" id="btn_modal_gi_sn_add_single">
                                                <i class="bi bi-plus-lg"></i>
                                                <span>Tambah</span>
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger px-3 py-1.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 rounded-3 flex-shrink-0 shadow-xs" id="btn_modal_gi_sn_toggle_camera" title="Buka Kamera Barcode / QR Scanner" style="height: 38px; font-size: 0.82rem;">
                                            <i class="bi bi-camera-video-fill text-danger"></i>
                                            <span>Scan Kamera</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Camera Viewport Container (Hidden by default) -->
                                <div id="modal_gi_sn_camera_scanner_container" class="mb-3 d-none">
                                    <div class="card border border-danger-subtle shadow-sm overflow-hidden" style="background: #090e1a; border-radius: 14px;">
                                        <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-body-tertiary border-bottom flex-wrap gap-2" style="font-size: 0.78rem;">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="spinner-grow spinner-grow-sm text-danger" role="status" style="width: 8px; height: 8px;"></span>
                                                <span class="fw-bold text-body-emphasis"><i class="bi bi-camera me-1 text-danger"></i> Live Scanner</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <select class="form-select form-select-sm py-0.5 px-2 font-monospace" id="modal_gi_sn_camera_select" style="font-size: 0.72rem; width: auto; max-width: 170px;">
                                                    <option value="">Deteksi Kamera...</option>
                                                </select>
                                                <button type="button" class="btn btn-danger btn-sm py-0.5 px-2.5 d-inline-flex align-items-center gap-1" id="btn_modal_gi_sn_stop_camera" style="font-size: 0.72rem;">
                                                    <i class="bi bi-x-circle"></i> Tutup
                                                </button>
                                            </div>
                                        </div>
                                        <div class="position-relative d-flex justify-content-center align-items-center" style="min-height: 200px; max-height: 280px; background: #000; overflow: hidden;">
                                            <div id="modal_gi_sn_camera_reader" style="width: 100%;"></div>
                                        </div>
                                        <div class="card-footer py-1.5 px-3 bg-body-tertiary d-flex justify-content-between align-items-center text-muted flex-wrap gap-1" style="font-size: 0.72rem;">
                                            <span><i class="bi bi-info-circle me-1 text-danger"></i> Arahkan kamera ke barcode / QR serial unit.</span>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5" id="modal_gi_sn_scan_status_badge">Kamera Siap</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-secondary mb-1 d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-clipboard-data text-danger"></i>
                                        <span>Paste Banyak Serial (Pisahkan baris baru / koma):</span>
                                    </label>
                                    <textarea class="form-control font-monospace small p-2.5" id="modal_gi_sn_bulk_paste_input" rows="5" placeholder="Contoh:&#10;PAS-2026-288-001&#10;PAS-2026-288-002&#10;PAS-2026-288-003" style="font-size: 0.8rem; min-height: 125px; resize: vertical;"></textarea>
                                    <div class="text-end mt-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold w-100 w-sm-auto shadow-xs" id="btn_modal_gi_sn_apply_bulk" style="font-size: 0.78rem;">
                                            <i class="bi bi-plus-circle me-1"></i> Tambahkan Semua ke Pilihan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bagian Bawah: Daftar Serial Number yang Sedang Terpilih Bento Card -->
                <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1.5">
                        <span class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
                            <i class="bi bi-tags-fill text-danger"></i>
                            <span>Serial Terpilih:</span>
                        </span>
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5 font-monospace shadow-xs" id="modal_gi_sn_active_tags_count" style="font-size: 0.72rem;">0 SN</span>
                    </div>
                    <div class="gi-chips-box-container d-flex flex-wrap gap-1.5 align-items-center" id="modal_gi_sn_selected_chips_box">
                        <div class="text-muted small fst-italic d-flex align-items-center gap-1.5 py-1" id="modal_gi_sn_empty_selected_hint" style="font-size: 0.76rem;">
                            <i class="bi bi-info-circle text-muted"></i>
                            <span>Belum ada serial number yang dipilih. Centang dari stok atau scan barcode.</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer (Side-by-side on mobile, never truncated) -->
            <div class="modal-footer px-3 px-sm-4 py-2 py-sm-3 border-top d-flex flex-row justify-content-between align-items-center gap-2 bg-light-subtle">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 flex-grow-1 flex-sm-grow-0" data-bs-dismiss="modal" style="font-size: 0.82rem; min-width: 90px;">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0" id="btn_modal_gi_sn_save" style="font-size: 0.82rem;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Terapkan Serial Number</span>
                </button>
            </div>

        </div>
    </div>
</div>
