<!-- Modal Pemilihan & Pengalokasian Serial Number (Enterprise & Responsive Edition) -->
<div class="modal modal-blur fade" id="modalSelectSerials" tabindex="-1" aria-labelledby="modalSelectSerialsTitle" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg do-modal-sn-dialog">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden do-modal-container">
            
            <!-- Modal Header -->
            <div class="modal-header px-3 px-sm-4 py-3 do-modal-header border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="do-mini-badge fs-5 flex-shrink-0" style="width: 36px; height: 36px; border-radius: 9px;">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalSelectSerialsTitle">Alokasi Serial Number</h6>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">UNIT TRACKING</span>
                        </div>
                        <div class="text-muted small mt-0.5 d-none d-sm-block" id="modal_sn_subtitle" style="font-size: 0.76rem;">Pilih nomor seri unit yang akan dikeluarkan secara fisik dari gudang.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-sm-4 do-modal-body" style="overflow-y: auto; max-height: calc(85vh - 120px);">
                
                <!-- Target & Status Alokasi Bento Card (Responsif Mobile & PC) -->
                <div class="card border-0 rounded-4 shadow-sm mb-3 do-card-section">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-sm-7">
                                <div class="small text-muted fw-semibold mb-0.5" style="font-size: 0.74rem;">Produk Terpilih:</div>
                                <div class="fw-bold text-body-emphasis fs-6 text-truncate" id="modal_sn_barang_nama">-</div>
                                <div class="small font-monospace text-primary" id="modal_sn_barang_sku">-</div>
                            </div>
                            <div class="col-12 col-sm-5 text-sm-end">
                                <div class="small text-muted fw-semibold mb-1" style="font-size: 0.74rem;">Status Alokasi Unit:</div>
                                <div class="d-flex align-items-center justify-content-start justify-content-sm-end gap-2 flex-wrap">
                                    <span class="badge bg-body-secondary text-body border px-2.5 py-1.5 font-monospace fw-bold" id="modal_sn_counter_badge">
                                        <span id="modal_sn_allocated_num" class="text-primary fs-6">0</span> / <span id="modal_sn_target_num">0</span> Unit
                                    </span>
                                    <div id="modal_sn_status_indicator"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nav Tabs: Dari Gudang vs Input Manual / Scan (Fleksibel) -->
                <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-3 gap-1.5 p-1 rounded-3 border bg-body-tertiary" id="snTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-2 fw-semibold small py-2 px-2.5 text-center d-flex align-items-center justify-content-center gap-1.5 text-nowrap" id="tab-stok-sn" data-bs-toggle="pill" data-bs-target="#panel-stok-sn" type="button" role="tab">
                            <i class="bi bi-box-seam"></i>
                            <span>Pilih Stok (<span id="modal_sn_stock_available_count">0</span> Tersedia)</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-2 fw-semibold small py-2 px-2.5 text-center d-flex align-items-center justify-content-center gap-1.5 text-nowrap" id="tab-manual-sn" data-bs-toggle="pill" data-bs-target="#panel-manual-sn" type="button" role="tab">
                            <i class="bi bi-keyboard"></i>
                            <span>Input Manual / Scanner</span>
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="snTabContent">
                    
                    <!-- Panel 1: Dari Stok Gudang -->
                    <div class="tab-pane fade show active" id="panel-stok-sn" role="tabpanel">
                        <!-- Toolbar Pencarian & Aksi Responsif -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-2.5">
                            <div class="input-group input-group-sm flex-grow-1">
                                <span class="input-group-text do-input-icon"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control do-form-control" id="modal_sn_search_stock" placeholder="Cari serial / batch...">
                            </div>
                            <div class="d-flex gap-1.5 justify-content-end flex-shrink-0">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 small fw-semibold text-nowrap flex-grow-1 flex-sm-grow-0" id="btn_modal_sn_auto_select">
                                    <i class="bi bi-check2-all me-1"></i>Pilih Otomatis
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 small text-nowrap flex-grow-1 flex-sm-grow-0" id="btn_modal_sn_clear_selection">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- List Serial Numbers dari DB -->
                        <div class="border rounded-3 p-3 bg-body overflow-auto" style="max-height: 290px; min-height: 140px;" id="modal_sn_stock_list_container">
                            <div class="text-center py-4 text-muted small fst-italic" id="modal_sn_stock_loading">
                                <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div> Memuat serial number dari gudang...
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Input Manual / Barcode Scan -->
                    <div class="tab-pane fade" id="panel-manual-sn" role="tabpanel">
                        <div id="modal_sn_manual_alert_container">
                            <div class="alert alert-primary-subtle border-primary-subtle d-flex align-items-center gap-2.5 py-2 px-3 rounded-3 mb-2.5" id="modal_sn_manual_alert" style="font-size: 0.78rem;">
                                <i class="bi bi-shield-check fs-6 text-primary flex-shrink-0"></i>
                                <div>
                                    <span class="fw-bold text-primary-emphasis">Pengecekan Kode Barang &amp; Serial Aktif:</span> Seluruh nomor seri yang di-scan / diketik <u>wajib telah terdaftar</u> di gudang untuk kode barang terpilih dan berstatus <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">Tersedia</span>.
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 rounded-3 p-3 bg-body-tertiary border mb-3">
                            <label class="form-label small fw-bold text-body-emphasis mb-1 d-flex align-items-center justify-content-between flex-wrap gap-1">
                                <span><i class="bi bi-upc-scan text-primary me-1"></i> Scan Barcode / Ketik Nomor Seri:</span>
                                <span class="badge bg-secondary-subtle text-muted font-monospace" style="font-size: 0.7rem;">TEKAN ENTER / SCAN</span>
                            </label>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text do-input-icon"><i class="bi bi-barcode text-muted"></i></span>
                                <input type="text" class="form-control do-form-control font-monospace" id="modal_sn_quick_scan_input" placeholder="Scan barcode fisik unit atau ketik serial lalu tekan Enter...">
                                <button type="button" class="btn btn-outline-primary px-3 fw-semibold d-inline-flex align-items-center gap-1.5" id="btn_modal_sn_toggle_camera" title="Buka Kamera Barcode / QR Scanner">
                                    <i class="bi bi-camera-video-fill text-primary"></i> <span class="d-none d-sm-inline">Scan Kamera</span>
                                </button>
                                <button type="button" class="btn btn-primary px-3 fw-bold" id="btn_modal_sn_add_single">Tambah</button>
                            </div>

                            <!-- Live Camera Viewport Container (Hidden by default) -->
                            <div id="modal_sn_camera_scanner_container" class="mb-3 d-none">
                                <div class="card border border-primary-subtle shadow-sm overflow-hidden" style="background: #090e1a; border-radius: 12px;">
                                    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center bg-body-tertiary border-bottom flex-wrap gap-2" style="font-size: 0.8rem;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="spinner-grow spinner-grow-sm text-danger" role="status" style="width: 8px; height: 8px;"></span>
                                            <span class="fw-bold text-body-emphasis"><i class="bi bi-camera me-1 text-primary"></i> Live Barcode / QR Scanner</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <select class="form-select form-select-sm py-0.5 px-2 font-monospace" id="modal_sn_camera_select" style="font-size: 0.72rem; width: auto; max-width: 170px; height: 28px;">
                                                <option value="">Deteksi Kamera...</option>
                                            </select>
                                            <button type="button" class="btn btn-danger btn-sm py-0.5 px-2.5 d-inline-flex align-items-center gap-1" id="btn_modal_sn_stop_camera" style="font-size: 0.72rem; height: 28px;">
                                                <i class="bi bi-x-circle"></i> Tutup Kamera
                                            </button>
                                        </div>
                                    </div>
                                    <div class="position-relative d-flex justify-content-center align-items-center" style="min-height: 220px; max-height: 300px; background: #000; overflow: hidden;">
                                        <div id="modal_sn_camera_reader" style="width: 100%;"></div>
                                        <div class="modal-sn-scanner-overlay" id="modal_sn_scanner_overlay" style="display: none;">
                                            <div class="modal-sn-target-box">
                                                <div class="modal-sn-laser-line"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1.5 px-3 bg-body-tertiary d-flex justify-content-between align-items-center text-muted flex-wrap gap-1" style="font-size: 0.72rem;">
                                        <span><i class="bi bi-info-circle me-1 text-info"></i> Arahkan kamera ke barcode / QR serial unit barang.</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="modal_sn_scan_status_badge">Kamera Siap</span>
                                    </div>
                                </div>
                            </div>

                            <label class="form-label small fw-semibold text-muted mb-1 mt-2">Atau Paste Banyak Nomor Seri Sekaligus (Pisahkan dengan baris baru / koma):</label>
                            <textarea class="form-control do-form-control font-monospace small" id="modal_sn_bulk_paste_input" rows="3" placeholder="SN-001&#10;SN-002&#10;SN-003"></textarea>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold w-100 w-sm-auto" id="btn_modal_sn_apply_bulk">
                                    <i class="bi bi-plus-circle me-1"></i> Tambahkan Semua ke Pilihan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bagian Bawah: Daftar Serial Number yang Sedang Terpilih -->
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                        <span class="small fw-bold text-body-emphasis d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                            <i class="bi bi-tags-fill text-primary"></i> Serial Terpilih:
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-0.5" id="modal_sn_active_tags_count">0 SN</span>
                    </div>
                    <div class="p-2.5 border rounded-3 bg-body-tertiary d-flex flex-wrap gap-1.5 align-items-center" style="min-height: 48px; max-height: 120px; overflow-y: auto;" id="modal_sn_selected_chips_box">
                        <span class="text-muted small fst-italic" id="modal_sn_empty_selected_hint">Belum ada serial number yang dipilih.</span>
                    </div>
                </div>

            </div>

            <!-- Modal Footer (Responsif Mobile & PC) -->
            <div class="modal-footer px-3 px-sm-4 py-2.5 do-modal-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 flex-grow-1 flex-sm-grow-0" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 do-btn-primary flex-grow-1 flex-sm-grow-0" id="btn_modal_sn_save">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Terapkan Serial Number</span>
                </button>
            </div>

        </div>
    </div>
</div>
