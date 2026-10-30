<!-- Modal Pemilihan & Pengalokasian Serial Number - Penyesuaian Stok (Stock Adjustment) -->
<div class="modal modal-blur fade" id="modalManageSerialsAdj" tabindex="-1" aria-labelledby="modalManageSerialsAdjTitle" aria-hidden="true" style="z-index: 1070 !important;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl gi-modal-sn-dialog" style="max-width: 1040px; width: 95vw; z-index: 1071 !important;">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden gi-modal-container">
            
            <!-- Modal Header -->
            <div class="modal-header px-3 px-sm-4 py-2.5 py-sm-3.5 bg-danger bg-gradient text-white d-flex justify-content-between align-items-center" id="modalAdjSnHeader">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="d-flex align-items-center justify-content-center bg-white text-danger fs-5 flex-shrink-0 shadow-sm rounded-3" style="width: 36px; height: 36px;" id="modalAdjSnIconBox">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 flex-wrap">
                            <h6 class="modal-title fw-bold mb-0 text-white fs-6" id="modalManageSerialsAdjTitle">Alokasi Serial Number</h6>
                            <span class="badge bg-white text-danger border border-white rounded-pill font-monospace px-2 py-0.5" id="modalAdjSnTypeBadge" style="font-size: 0.65rem; font-weight: 700;">PENGURANGAN</span>
                        </div>
                        <div class="text-white-50 small mt-0.5 d-none d-sm-block" id="modalAdjSnSubtitle" style="font-size: 0.78rem;">Pilih nomor seri unit yang akan dikeluarkan/dikurangkan dari gudang.</div>
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
                                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" id="bento_icon_container" style="width: 40px; height: 40px;">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.4px;">Barang yang Disesuaikan:</div>
                                        <h6 class="fw-bold text-dark mb-0.5 text-truncate fs-6" id="modal_adj_sn_barang_nama">-</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-0.5" id="modal_adj_sn_barang_sku" style="font-size: 0.72rem;">-</span>
                                            <span class="text-muted small"><i class="bi bi-house-door me-1"></i><span id="modal_adj_sn_gudang_name">-</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5 text-md-end pt-1 pt-md-0 border-top border-top-md-0">
                                <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2 flex-wrap">
                                    <div class="text-muted fw-semibold text-uppercase d-md-none" style="font-size: 0.68rem;">Kebutuhan:</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="bg-white border rounded-3 px-2.5 py-1 font-monospace fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="modal_adj_sn_counter_badge" style="font-size: 0.8rem;">
                                            <span class="small text-muted d-none d-sm-inline">Terpilih:</span>
                                            <span id="modal_adj_sn_allocated_num" class="text-danger fw-bold">0</span>
                                            <span class="text-muted">/</span>
                                            <span id="modal_adj_sn_target_num" class="text-dark fw-bold">0</span>
                                            <span class="small text-muted">Unit</span>
                                        </div>
                                        <div id="modal_adj_sn_status_indicator"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAMPILAN PENGURANGAN (MIRIP PERSIS BARANG KELUAR) -->
                <div id="sectionAdjSnPengurangan" class="d-flex flex-column gap-3">
                    <!-- Nav Tabs: Horizontal on all viewports, side-by-side -->
                    <div>
                        <ul class="nav nav-pills nav-fill flex-row gap-1.5 gap-sm-2" id="giSnTab" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link active text-center d-flex align-items-center justify-content-center gap-1.5 text-truncate w-100" id="tab-adj-stok-sn" data-bs-toggle="pill" data-bs-target="#panel-adj-stok-sn" type="button" role="tab">
                                    <i class="bi bi-box-seam flex-shrink-0"></i>
                                    <span class="text-truncate"><span class="d-none d-sm-inline">Pilih dari </span>Stok (<span id="modal_adj_sn_stock_available_count">0</span><span class="d-none d-sm-inline"> Tersedia</span>)</span>
                                </button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link text-center d-flex align-items-center justify-content-center gap-1.5 text-truncate w-100" id="tab-adj-manual-sn" data-bs-toggle="pill" data-bs-target="#panel-adj-manual-sn" type="button" role="tab">
                                    <i class="bi bi-upc-scan flex-shrink-0"></i>
                                    <span class="text-truncate"><span class="d-none d-sm-inline">Input </span>Manual / Scan</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content Area -->
                    <div class="tab-content" id="adjSnTabContent">
                        <!-- Panel 1: Dari Stok Gudang -->
                        <div class="tab-pane fade show active" id="panel-adj-stok-sn" role="tabpanel">
                            <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                                <!-- Toolbar Pencarian & Aksi Responsif -->
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-2.5">
                                    <div class="input-group flex-grow-1 shadow-xs">
                                        <span class="input-group-text bg-light border-end-0 text-muted py-1.5 px-2.5"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="modal_adj_sn_search_stock" placeholder="Cari serial / batch..." style="height: 38px; font-size: 0.85rem;">
                                    </div>
                                    <div class="d-flex gap-1.5 flex-shrink-0">
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 fw-semibold text-nowrap d-inline-flex align-items-center justify-content-center gap-1 shadow-xs flex-fill flex-sm-grow-0" id="btn_modal_adj_sn_auto_select" onclick="autoPilihSnPengurangan()" style="font-size: 0.78rem;">
                                            <i class="bi bi-magic"></i>
                                            <span>Pilih Otomatis</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1.5 small text-nowrap d-inline-flex align-items-center justify-content-center gap-1 shadow-xs flex-fill flex-sm-grow-0" id="btn_modal_adj_sn_clear_selection" onclick="clearAllSelectedSnAdj()" style="font-size: 0.78rem;">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                            <span>Reset</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- List Serial Numbers dari DB (Grid Cards) -->
                                <div class="gi-stock-scroll-box" id="modal_adj_sn_stock_list_container">
                                    <div class="text-center py-4 text-muted small fst-italic" id="modal_adj_sn_stock_loading">
                                        <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div> Memuat serial number dari gudang...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel 2: Input Manual / Barcode Scan -->
                        <div class="tab-pane fade" id="panel-adj-manual-sn" role="tabpanel">
                            <div class="d-flex flex-column gap-2.5 gap-sm-3.5">
                                <div class="alert alert-danger-subtle border-danger-subtle d-flex align-items-center gap-2.5 py-2 px-3 rounded-3 mb-0" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-check fs-5 text-danger flex-shrink-0"></i>
                                    <div>
                                        <span class="fw-bold text-danger-emphasis">Validasi Aktif:</span> Nomor seri wajib terdaftar di gudang untuk produk terpilih &amp; berstatus <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5">Tersedia</span>.
                                    </div>
                                </div>

                                <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center justify-content-between flex-wrap gap-1">
                                            <span style="font-size: 0.82rem;"><i class="bi bi-upc-scan text-danger me-1"></i> Scan Barcode Fisik / Input Cepat:</span>
                                            <span class="badge bg-secondary-subtle text-muted font-monospace px-1.5 py-0.5" style="font-size: 0.68rem;">ENTER / SCAN</span>
                                        </label>
                                        <div class="input-group shadow-xs">
                                            <span class="input-group-text bg-light text-muted px-2.5 py-1"><i class="bi bi-barcode"></i></span>
                                            <input type="text" class="form-control font-monospace ps-2" id="modal_adj_sn_quick_scan_input" placeholder="Scan barcode / ketik serial lalu Enter..." style="height: 38px; font-size: 0.84rem;">
                                            <button type="button" class="btn btn-danger px-3 fw-bold d-inline-flex align-items-center gap-1 flex-shrink-0" id="btn_adj_sn_add_single_pengurangan" onclick="processSingleAdjSerialPengurangan()">
                                                <i class="bi bi-plus-lg"></i>
                                                <span>Tambah</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.82rem;">
                                            <i class="bi bi-clipboard-plus text-danger me-1"></i> Paste Banyak Serial (Pisahkan baris baru / koma):
                                        </label>
                                        <textarea class="form-control font-monospace small" id="modal_adj_sn_bulk_paste_input" rows="5" placeholder="Contoh:&#10;SN-2026-001&#10;SN-2026-002&#10;SN-2026-003" style="font-size: 0.82rem; min-height: 125px; resize: vertical;"></textarea>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" onclick="processBulkAdjSerialPengurangan()">
                                                <i class="bi bi-plus-circle"></i>
                                                <span>Tambahkan Semua ke Pilihan</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAMPILAN PENAMBAHAN (REGISTRASI SERIAL BARU) -->
                <div id="sectionAdjSnPenambahan" class="d-none flex-column gap-3">
                    <ul class="nav nav-pills nav-fill p-1.5 bg-light rounded-3 border" id="adjSnAddTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold py-2 px-3 rounded-2" id="tab-adj-sn-scan" data-bs-toggle="pill" data-bs-target="#content-adj-sn-scan" type="button" role="tab">
                                <i class="bi bi-upc-scan me-1.5"></i> Scan Barcode / Input Cepat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-2 px-3 rounded-2" id="tab-adj-sn-paste" data-bs-toggle="pill" data-bs-target="#content-adj-sn-paste" type="button" role="tab">
                                <i class="bi bi-clipboard-plus me-1.5"></i> Paste Banyak Serial
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="adjSnAddTabContent">
                        <div class="tab-pane fade show active" id="content-adj-sn-scan" role="tabpanel">
                            <div class="card gi-sn-section-card p-3 p-md-4">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-upc-scan text-primary me-1"></i> Scan Barcode / Ketik Serial Baru:
                                </label>
                                <div class="input-group shadow-xs">
                                    <span class="input-group-text bg-light text-primary"><i class="bi bi-barcode fs-5"></i></span>
                                    <input type="text" class="form-control font-monospace ps-2" id="inp_adj_sn_single" placeholder="Ketik nomor seri atau scan barcode fisik lalu Enter...">
                                    <button type="button" class="btn btn-primary fw-semibold px-4" id="btn_adj_sn_add_single" onclick="tambahSingleSnAdj()">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle me-1"></i>Nomor seri baru akan didaftarkan ke gudang dengan status <b>Tersedia</b> setelah dokumen disetujui.</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="content-adj-sn-paste" role="tabpanel">
                            <div class="card gi-sn-section-card p-3 p-md-4">
                                <label class="form-label small fw-bold text-dark mb-1.5">
                                    <i class="bi bi-clipboard-plus text-primary me-1"></i> Paste Banyak Serial Sekaligus:
                                </label>
                                <textarea class="form-control font-monospace" id="inp_adj_sn_bulk" rows="5" placeholder="Tempel nomor seri di sini (pisahkan baris baru / Enter atau tanda koma)...&#10;Contoh:&#10;PAS-2026-288-001&#10;PAS-2026-288-002&#10;PAS-2026-288-003" style="font-size: 0.82rem; min-height: 125px; resize: vertical;"></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2.5">
                                    <small class="text-muted"><i class="bi bi-lightbulb me-1"></i>Spasi dan baris kosong otomatis dibersihkan.</small>
                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-xs" onclick="prosesBulkSnAdj()">
                                        <i class="bi bi-plus-circle me-1"></i> Tambahkan Semua ke Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHIPS DAFTAR SERIAL NUMBER TERPILIH / TERDAFTAR (SAMA PERSIS DENGAN GOODS ISSUE) -->
                <div class="card gi-sn-section-card p-2.5 p-sm-3.5 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-dark" id="lbl_adj_sn_selected_title">Daftar Serial Number Terpilih:</span>
                            <span class="badge bg-danger rounded-pill font-monospace" id="lbl_adj_sn_selected_count" style="font-size: 0.75rem;">0</span>
                        </div>
                        <button type="button" class="btn btn-link text-danger text-decoration-none btn-sm p-0 fw-semibold" onclick="clearAllSelectedSnAdj()" style="font-size: 0.78rem;">
                            <i class="bi bi-trash3 me-1"></i>Hapus Semua
                        </button>
                    </div>
                    <div class="gi-chips-box-container" id="modal_adj_sn_selected_chips_box">
                        <div class="text-center py-2 text-muted small fst-italic" id="lbl_empty_selected_sn">
                            Belum ada nomor seri yang dipilih.
                        </div>
                        <div class="d-flex flex-wrap gap-1.5" id="container_adj_selected_chips"></div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer px-3 px-sm-4 py-2.5 py-sm-3 bg-light border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted d-none d-sm-block" id="lbl_adj_sn_footer_info">
                    <i class="bi bi-info-circle me-1"></i>Pastikan alokasi nomor seri sesuai target sebelum menyimpan.
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-secondary rounded-pill px-3 py-1.5 small" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 py-1.5 fw-bold shadow-xs small" id="btn_save_adj_sn" onclick="simpanAlokasiSnAdj()">
                        <i class="bi bi-check2-circle me-1"></i> Simpan &amp; Terapkan Serial
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT LOGIKA SERIAL NUMBER ADJUSTMENT --}}
@push('scripts')
<script>
    window.adjSnActiveRow = null;
    window.adjSnActiveContext = 'input'; // 'input' or 'edit'
    window.adjSnTipe = 'Pengurangan';   // 'Penambahan' or 'Pengurangan'
    window.adjSnBarangId = null;
    window.adjSnGudangId = null;
    window.adjSnTargetQty = 0;
    window.adjSnSelectedList = [];
    window.adjSnAvailableList = [];

    // Buka Modal Kelola Serial Number
    window.openModalManageSerialsAdj = function(rowId, context = 'input') {
        window.adjSnActiveRow = rowId;
        window.adjSnActiveContext = context;

        const isInput = (context === 'input');
        const tipe = isInput ? $('#inp_tipe').val() : $('#edit_tipe').val();
        window.adjSnTipe = tipe;

        const gudangId = isInput ? $('#inp_gudang_id').val() : $('#edit_gudang_id').val();
        const gudangName = isInput ? ($('#inp_gudang_id option:selected').text() || '-') : ($('#edit_gudang_name').val() || $('#edit_gudang_id option:selected').text() || '-');
        window.adjSnGudangId = gudangId;

        const $row = $(`#${rowId}`);
        const barangId = $row.find('.inp-barang-id').val();
        const barangName = $row.find('.inp-barang-name').val() || 'Barang Terpilih';
        const qtyVal = parseFloat($row.find('.inp-qty-input, .inp-qty-edit').val() || 0);

        if (!barangId) {
            Swal.fire('Peringatan', 'Harap pilih barang terlebih dahulu sebelum mengelola serial number!', 'warning');
            return;
        }

        if (qtyVal <= 0) {
            Swal.fire('Peringatan', 'Harap isi Qty Adjustment terlebih dahulu sebelum mengelola serial number!', 'warning');
            return;
        }

        window.adjSnBarangId = barangId;
        window.adjSnTargetQty = parseInt(qtyVal, 10);

        // Ambil existing serials yang sudah tersimpan di baris tersebut
        let currentSns = [];
        const rawJson = $row.find('.inp-serial-numbers').val();
        if (rawJson) {
            try {
                currentSns = Array.isArray(rawJson) ? rawJson : JSON.parse(rawJson);
            } catch (e) {
                currentSns = [];
            }
        }
        window.adjSnSelectedList = Array.isArray(currentSns) ? [...currentSns] : [];

        // Set Info Header & Card
        $('#modal_adj_sn_barang_nama').text(barangName);
        $('#modal_adj_sn_barang_sku').text(`BARANG ID #${barangId}`);
        $('#modal_adj_sn_gudang_name').text(gudangName);
        $('#modal_adj_sn_target_num').text(window.adjSnTargetQty);

        // Styling Modal berdasarkan Tipe: Biru untuk Penambahan, Merah untuk Pengurangan
        if (tipe === 'Penambahan') {
            $('#modalAdjSnHeader').removeClass('bg-danger').addClass('bg-primary');
            $('#modalAdjSnIconBox').removeClass('text-danger').addClass('text-primary').html('<i class="bi bi-plus-circle"></i>');
            $('#modalAdjSnTypeBadge').removeClass('text-danger').addClass('text-primary').text('PENAMBAHAN');
            $('#modalManageSerialsAdjTitle').text('Registrasi Serial Number Baru');
            $('#modalAdjSnSubtitle').text('Daftarkan nomor seri unik untuk unit baru hasil penyesuaian stok opname.');
            $('#lbl_adj_sn_selected_title').text('Daftar Serial Number yang Akan Ditambahkan:');
            $('#lbl_adj_sn_selected_count').removeClass('bg-danger').addClass('bg-primary');
            $('#btn_save_adj_sn').removeClass('btn-danger').addClass('btn-primary');
            $('#bento_icon_container').removeClass('bg-danger text-danger').addClass('bg-primary text-primary');

            $('#sectionAdjSnPenambahan').removeClass('d-none').addClass('d-flex');
            $('#sectionAdjSnPengurangan').removeClass('d-flex').addClass('d-none');
            $('#inp_adj_sn_single').val('');
            $('#inp_adj_sn_bulk').val('');
            $('#tab-adj-sn-scan').tab('show');
        } else {
            $('#modalAdjSnHeader').removeClass('bg-primary').addClass('bg-danger');
            $('#modalAdjSnIconBox').removeClass('text-primary').addClass('text-danger').html('<i class="bi bi-upc-scan"></i>');
            $('#modalAdjSnTypeBadge').removeClass('text-primary').addClass('text-danger').text('PENGURANGAN');
            $('#modalManageSerialsAdjTitle').text('Alokasi Serial Number');
            $('#modalAdjSnSubtitle').text('Pilih nomor seri unit yang akan dikeluarkan secara fisik dari gudang.');
            $('#lbl_adj_sn_selected_title').text('Daftar Serial Number yang Dipilih:');
            $('#lbl_adj_sn_selected_count').removeClass('bg-primary').addClass('bg-danger');
            $('#btn_save_adj_sn').removeClass('btn-primary').addClass('btn-danger');
            $('#bento_icon_container').removeClass('bg-primary text-primary').addClass('bg-danger text-danger');

            $('#sectionAdjSnPenambahan').removeClass('d-flex').addClass('d-none');
            $('#sectionAdjSnPengurangan').removeClass('d-none').addClass('d-flex');
            $('#modal_adj_sn_search_stock').val('');
            $('#modal_adj_sn_quick_scan_input').val('');
            $('#modal_adj_sn_bulk_paste_input').val('');
            $('#tab-adj-stok-sn').tab('show');

            loadAvailableSerialsAdj(barangId, gudangId);
        }

        renderSelectedChipsAdj();
        $('#modalManageSerialsAdj').modal('show');

        setTimeout(() => {
            if (tipe === 'Penambahan') {
                $('#inp_adj_sn_single').focus();
            } else {
                $('#modal_adj_sn_search_stock').focus();
            }
        }, 400);
    };

    // Render Chips Serial yang telah terpilih / terdaftar
    function renderSelectedChipsAdj() {
        const list = window.adjSnSelectedList;
        const count = list.length;
        const target = window.adjSnTargetQty;

        $('#modal_adj_sn_allocated_num').text(count);
        $('#lbl_adj_sn_selected_count').text(count);

        if (count === target && target > 0) {
            $('#modal_adj_sn_status_indicator').html('<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill me-1"></i>Lengkap</span>');
        } else if (count > target) {
            $('#modal_adj_sn_status_indicator').html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2.5 py-1" style="font-size:0.75rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Melebihi (' + count + '/' + target + ')</span>');
        } else {
            const sisa = target - count;
            $('#modal_adj_sn_status_indicator').html('<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace px-2.5 py-1" style="font-size:0.75rem;"><i class="bi bi-exclamation-circle me-1"></i>Kurang ' + sisa + ' unit</span>');
        }

        if (count === 0) {
            $('#lbl_empty_selected_sn').removeClass('d-none');
            $('#container_adj_selected_chips').empty();
            return;
        }

        $('#lbl_empty_selected_sn').addClass('d-none');
        let chipsHtml = '';
        list.forEach((sn, idx) => {
            const badgeTheme = (window.adjSnTipe === 'Penambahan') 
                ? 'bg-primary-subtle text-primary border-primary-subtle' 
                : 'bg-danger-subtle text-danger border-danger-subtle';
            chipsHtml += `
                <div class="badge ${badgeTheme} border rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 font-monospace shadow-xs" style="font-size: 0.76rem;">
                    <i class="bi bi-upc-scan"></i>
                    <span>${escapeHtmlAdj(sn)}</span>
                    <button type="button" class="btn-close p-0 ms-1" style="font-size: 0.65rem;" onclick="removeSelectedSnAdj(${idx})" title="Hapus Serial"></button>
                </div>
            `;
        });
        $('#container_adj_selected_chips').html(chipsHtml);

        // Jika mode Pengurangan, sync checkbox visual pada list stock
        if (window.adjSnTipe === 'Pengurangan') {
            syncStockGridCheckboxes();
        }
    }

    // Sync visual checkbox pada grid stok
    function syncStockGridCheckboxes() {
        $('#adj_stock_serials_grid .gi-stock-sn-checkbox').each(function() {
            const sn = $(this).val();
            const isChecked = window.adjSnSelectedList.includes(sn);
            $(this).prop('checked', isChecked);
            const $label = $(this).closest('.gi-sn-card-label');
            if (isChecked) {
                $label.addClass('border-danger bg-danger-subtle bg-opacity-25');
            } else {
                $label.removeClass('border-danger bg-danger-subtle bg-opacity-25');
            }
        });
    }

    // Hapus satu serial dari daftar
    window.removeSelectedSnAdj = function(idx) {
        window.adjSnSelectedList.splice(idx, 1);
        renderSelectedChipsAdj();
    };

    // Bersihkan semua serial
    window.clearAllSelectedSnAdj = function() {
        if (window.adjSnSelectedList.length === 0) return;
        window.adjSnSelectedList = [];
        renderSelectedChipsAdj();
    };

    // Load available serials dari database (mode Pengurangan)
    function loadAvailableSerialsAdj(barangId, gudangId) {
        $('#modal_adj_sn_stock_list_container').html(`
            <div class="text-center py-4 text-muted small fst-italic">
                <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div> Memuat serial number dari gudang...
            </div>
        `);

        const currentAdjId = (window.adjSnActiveContext === 'edit') ? $('#edit_id').val() : null;

        $.ajax({
            url: '/stock-adjustment/available-serials',
            method: 'GET',
            data: {
                barang_id: barangId,
                gudang_id: gudangId,
                stock_adjustment_id: currentAdjId
            },
            success: function(res) {
                window.adjSnAvailableList = res.serials || [];
                $('#modal_adj_sn_stock_available_count').text(window.adjSnAvailableList.length);

                if (window.adjSnAvailableList.length === 0) {
                    $('#modal_adj_sn_stock_list_container').html(`
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-box-seam fs-3 d-block text-secondary mb-2"></i>
                            Tidak ada serial number berstatus Tersedia di gudang ini untuk produk ini.
                        </div>
                    `);
                    return;
                }

                renderStockGridAdj(window.adjSnAvailableList);
            },
            error: function() {
                $('#modal_adj_sn_stock_available_count').text(0);
                $('#modal_adj_sn_stock_list_container').html('<div class="text-center py-5 text-danger small">Gagal memuat serial number dari server.</div>');
            }
        });
    }

    // Render Stock List checkboxes in Tab 1 (Persis Goods Issue)
    function renderStockGridAdj(serials) {
        let html = '<div class="row g-2.5" id="adj_stock_serials_grid">';
        serials.forEach(s => {
            const isChecked = window.adjSnSelectedList.includes(s.serial_number);
            const borderClass = isChecked ? 'border-danger bg-danger-subtle bg-opacity-25' : '';
            html += `
                <div class="col-12 col-md-6 gi-stock-sn-item" data-sn="${escapeHtmlAdj((s.serial_number || '').toLowerCase())}" data-batch="${escapeHtmlAdj((s.nomor_batch || '').toLowerCase())}">
                    <label class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white h-100 ${borderClass} gi-sn-card-label shadow-xs" style="cursor: pointer; transition: all 0.2s ease;">
                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                            <input type="checkbox" class="form-check-input gi-stock-sn-checkbox mt-0 flex-shrink-0" value="${escapeHtmlAdj(s.serial_number)}" ${isChecked ? 'checked' : ''} style="width: 1.15rem; height: 1.15rem;">
                            <div class="overflow-hidden">
                                <div class="font-monospace fw-bold text-dark text-truncate d-flex align-items-center gap-1.5" style="font-size:0.88rem;">
                                    <i class="bi bi-upc-scan text-danger"></i> <span>${escapeHtmlAdj(s.serial_number)}</span>
                                </div>
                                <div class="text-muted small font-monospace mt-1 d-flex align-items-center gap-2" style="font-size:0.75rem;">
                                    <span>Batch: <span class="badge bg-light text-dark border px-1.5 py-0.5">${escapeHtmlAdj(s.nomor_batch || '-')}</span></span>
                                    <span class="text-truncate text-muted"><i class="bi bi-geo-alt me-0.5"></i>${escapeHtmlAdj(s.gudang || '-')}</span>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1 small flex-shrink-0" style="font-size:0.72rem;">
                            <i class="bi bi-check-circle me-1"></i>Tersedia
                        </span>
                    </label>
                </div>
            `;
        });
        html += '</div>';
        $('#modal_adj_sn_stock_list_container').html(html);
    }

    // Event Handler Click Checkbox Grid Card
    $(document).on('change', '.gi-stock-sn-checkbox', function() {
        const sn = $(this).val();
        const isChecked = $(this).is(':checked');

        if (isChecked) {
            if (window.adjSnSelectedList.length >= window.adjSnTargetQty) {
                $(this).prop('checked', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'Target Terpenuhi',
                    text: `Alokasi sudah mencapai target kuantitas (${window.adjSnTargetQty} unit)!`,
                    confirmButtonColor: '#ef4444'
                });
                return;
            }
            if (!window.adjSnSelectedList.includes(sn)) {
                window.adjSnSelectedList.push(sn);
            }
        } else {
            const idx = window.adjSnSelectedList.indexOf(sn);
            if (idx !== -1) {
                window.adjSnSelectedList.splice(idx, 1);
            }
        }

        renderSelectedChipsAdj();
    });

    if (typeof window.parseQrOrBarcodeString !== 'function') {
        window.parseQrOrBarcodeString = function(str) {
            str = (str || '').toString().trim();
            let sn = null;
            let kode = null;

            if (str.includes('sn=')) {
                let m = str.match(/[?&]sn=([^&]+)/i);
                if (m) sn = decodeURIComponent(m[1].trim());
            }

            if (!sn) {
                let m = str.match(/SN\s*:\s*(.+?)(?=(?:(?:KODE|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
                if (m) sn = m[1].trim();
            }

            let mKode = str.match(/KODE\s*:\s*(.+?)(?=(?:(?:SN|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
            if (mKode) kode = mKode[1].trim();

            if (!sn) {
                let m = str.match(/^SN\s*[:\-\s]\s*(.+)$/i);
                if (m) sn = m[1].trim();
            }

            return { sn: sn, kode: kode, raw: str };
        };
    }

    // Live search filter persis Goods Issue
    $('#modal_adj_sn_search_stock').on('input', function() {
        let raw = $(this).val();
        let parsed = window.parseQrOrBarcodeString(raw);
        const q = $.trim(parsed.sn || parsed.kode || raw).toLowerCase();
        $('#adj_stock_serials_grid .gi-stock-sn-item').each(function() {
            const sn = $(this).data('sn') || '';
            const batch = $(this).data('batch') || '';
            if (!q || sn.includes(q) || batch.includes(q)) {
                $(this).removeClass('d-none');
            } else {
                $(this).addClass('d-none');
            }
        });
    });

    // Auto-pick N serials teratas sesuai target
    window.autoPilihSnPengurangan = function() {
        const target = window.adjSnTargetQty;
        const avail = window.adjSnAvailableList;

        if (avail.length === 0) {
            Swal.fire('Kosong', 'Tidak ada nomor seri tersedia di gudang ini!', 'warning');
            return;
        }

        const toPick = [];
        for (let i = 0; i < avail.length && toPick.length < target; i++) {
            toPick.push(avail[i].serial_number);
        }

        window.adjSnSelectedList = toPick;
        renderSelectedChipsAdj();
    };

    // Single Manual Scan Pengurangan
    window.processSingleAdjSerialPengurangan = function() {
        const inputVal = $('#modal_adj_sn_quick_scan_input').val();
        const cleanSn = $.trim(inputVal);
        if (!cleanSn) return;

        const existsInStock = window.adjSnAvailableList.some(s => s.serial_number.toLowerCase() === cleanSn.toLowerCase());
        if (!existsInStock) {
            Swal.fire({
                icon: 'error',
                title: 'Serial Number Tidak Ditemukan',
                html: `Nomor seri <b>${escapeHtmlAdj(cleanSn)}</b> tidak terdaftar berstatus <i>Tersedia</i> di gudang ini untuk produk ini!`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        const stockRecord = window.adjSnAvailableList.find(s => s.serial_number.toLowerCase() === cleanSn.toLowerCase());
        const officialSn = stockRecord ? stockRecord.serial_number : cleanSn;

        if (window.adjSnSelectedList.includes(officialSn)) {
            Swal.fire({
                icon: 'warning',
                title: 'Sudah Terpilih',
                html: `Nomor seri <b>${escapeHtmlAdj(officialSn)}</b> sudah ada dalam daftar pilihan!`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        if (window.adjSnSelectedList.length >= window.adjSnTargetQty) {
            Swal.fire({
                icon: 'warning',
                title: 'Target Terpenuhi',
                text: `Kapasitas target kuantitas (${window.adjSnTargetQty} unit) sudah terpenuhi!`,
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        window.adjSnSelectedList.push(officialSn);
        $('#modal_adj_sn_quick_scan_input').val('').focus();
        renderSelectedChipsAdj();
    };

    $('#modal_adj_sn_quick_scan_input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            processSingleAdjSerialPengurangan();
        }
    });

    // Bulk Paste Pengurangan
    window.processBulkAdjSerialPengurangan = function() {
        const rawText = $('#modal_adj_sn_bulk_paste_input').val();
        if (!$.trim(rawText)) {
            Swal.fire('Info', 'Silakan tempel atau ketik nomor seri pada textarea.', 'info');
            return;
        }

        const lines = rawText.split(/[\r\n,]+/).map(s => $.trim(s)).filter(s => s.length > 0);
        if (lines.length === 0) return;

        let addedCount = 0;
        let notFoundList = [];
        let alreadySelectedList = [];

        lines.forEach(lineSn => {
            if (window.adjSnSelectedList.length >= window.adjSnTargetQty) return;

            const stockRecord = window.adjSnAvailableList.find(s => s.serial_number.toLowerCase() === lineSn.toLowerCase());
            if (!stockRecord) {
                if (!notFoundList.includes(lineSn)) notFoundList.push(lineSn);
                return;
            }

            const officialSn = stockRecord.serial_number;
            if (window.adjSnSelectedList.includes(officialSn)) {
                if (!alreadySelectedList.includes(officialSn)) alreadySelectedList.push(officialSn);
                return;
            }

            window.adjSnSelectedList.push(officialSn);
            addedCount++;
        });

        $('#modal_adj_sn_bulk_paste_input').val('');
        renderSelectedChipsAdj();

        let infoHtml = `Berhasil menambahkan <b>${addedCount}</b> unit serial number.`;
        if (notFoundList.length > 0) {
            infoHtml += `<br><br><span class="text-danger small">Tidak ditemukan di stok (${notFoundList.length}): ${notFoundList.slice(0, 5).join(', ')}${notFoundList.length > 5 ? '...' : ''}</span>`;
        }

        Swal.fire({
            icon: addedCount > 0 ? 'success' : 'warning',
            title: 'Hasil Paste Serial',
            html: infoHtml,
            confirmButtonColor: '#ef4444'
        });
    };

    // Single Input Penambahan (Registrasi Baru)
    window.tambahSingleSnAdj = function() {
        let raw = $('#inp_adj_sn_single').val().trim();
        if (!raw) return;

        let parsed = window.parseQrOrBarcodeString(raw);
        const val = parsed.sn || raw;

        if (window.adjSnSelectedList.includes(val)) {
            Swal.fire('Duplikat', `Nomor seri "${val}" sudah ada di dalam daftar!`, 'warning');
            $('#inp_adj_sn_single').select().focus();
            return;
        }

        if (window.adjSnSelectedList.length >= window.adjSnTargetQty) {
            Swal.fire('Target Terpenuhi', `Target kuantitas (${window.adjSnTargetQty} unit) sudah terpenuhi!`, 'info');
            return;
        }

        $.ajax({
            url: '/stock-adjustment/validate-new-serials',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barang_id: window.adjSnBarangId,
                serials: [val]
            },
            success: function(res) {
                if (res.duplicates && res.duplicates.length > 0) {
                    Swal.fire('Serial Sudah Ada', `Nomor seri "${val}" sudah terdaftar dan aktif di sistem. Harap gunakan nomor seri unik!`, 'error');
                } else {
                    window.adjSnSelectedList.push(val);
                    renderSelectedChipsAdj();
                    $('#inp_adj_sn_single').val('').focus();
                }
            },
            error: function() {
                window.adjSnSelectedList.push(val);
                renderSelectedChipsAdj();
                $('#inp_adj_sn_single').val('').focus();
            }
        });
    };

    $('#inp_adj_sn_single').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            tambahSingleSnAdj();
        }
    });

    // Bulk Paste Penambahan (Registrasi Baru)
    window.prosesBulkSnAdj = function() {
        const text = $('#inp_adj_sn_bulk').val();
        if (!text.trim()) {
            Swal.fire('Peringatan', 'Harap tempel nomor seri terlebih dahulu!', 'warning');
            return;
        }

        const items = text.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
        if (items.length === 0) return;

        const uniqueItems = [];
        items.forEach(sn => {
            if (!window.adjSnSelectedList.includes(sn) && !uniqueItems.includes(sn)) {
                uniqueItems.push(sn);
            }
        });

        if (uniqueItems.length === 0) {
            Swal.fire('Info', 'Semua nomor seri yang ditempel sudah ada di dalam daftar.', 'info');
            return;
        }

        const remainingQuota = window.adjSnTargetQty - window.adjSnSelectedList.length;
        if (uniqueItems.length > remainingQuota && remainingQuota > 0) {
            Swal.fire({
                title: 'Kelebihan Nomor Seri',
                text: `Anda menempel ${uniqueItems.length} nomor seri, tetapi kuota tersisa hanya ${remainingQuota} unit. Apakah ingin memasukkan hanya ${remainingQuota} serial pertama?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Ya, masukkan ${remainingQuota} serial`,
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    validateAndAddBulkAdj(uniqueItems.slice(0, remainingQuota));
                }
            });
            return;
        }

        validateAndAddBulkAdj(uniqueItems);
    };

    function validateAndAddBulkAdj(sns) {
        $.ajax({
            url: '/stock-adjustment/validate-new-serials',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barang_id: window.adjSnBarangId,
                serials: sns
            },
            success: function(res) {
                const dups = res.duplicates || [];
                const validToAdd = sns.filter(s => !dups.includes(s));

                if (dups.length > 0) {
                    Swal.fire({
                        title: `${dups.length} Serial Duplikat Ditolak!`,
                        html: `Serial berikut sudah terdaftar di sistem:<br><b class="text-danger font-monospace">${dups.join(', ')}</b><br><br>${validToAdd.length} serial unik lainnya berhasil ditambahkan.`,
                        icon: 'warning'
                    });
                }

                validToAdd.forEach(s => window.adjSnSelectedList.push(s));
                renderSelectedChipsAdj();
                $('#inp_adj_sn_bulk').val('');
                $('#tab-adj-sn-scan').tab('show');
            },
            error: function() {
                sns.forEach(s => window.adjSnSelectedList.push(s));
                renderSelectedChipsAdj();
                $('#inp_adj_sn_bulk').val('');
            }
        });
    }

    // Simpan & Terapkan Serial ke baris tabel
    window.simpanAlokasiSnAdj = function() {
        const count = window.adjSnSelectedList.length;
        const target = window.adjSnTargetQty;

        if (count !== target) {
            Swal.fire({
                title: 'Jumlah Serial Belum Pas',
                text: `Kuantitas adjustment adalah ${target} unit, tetapi serial yang dialokasikan baru ${count} unit. Tetap simpan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Tetap Simpan',
                cancelButtonText: 'Lengkapi Dulu'
            }).then((res) => {
                if (res.isConfirmed) {
                    applySerialsToRowAdj();
                }
            });
            return;
        }

        applySerialsToRowAdj();
    };

    function applySerialsToRowAdj() {
        const rowId = window.adjSnActiveRow;
        if (!rowId) return;

        const $row = $(`#${rowId}`);
        const count = window.adjSnSelectedList.length;
        const target = window.adjSnTargetQty;

        $row.find('.inp-serial-numbers').val(JSON.stringify(window.adjSnSelectedList));

        const $btnSn = $row.find('.btn-manage-sn');
        $btnSn.removeClass('d-none');

        const btnClass = (count === target && target > 0) ? 'btn-outline-success text-success' : 'btn-outline-danger text-danger';
        $btnSn.removeClass('btn-outline-success text-success btn-outline-danger text-danger')
              .addClass(btnClass);
        $btnSn.find('.sn-btn-text').text(`Serial (${count}/${target})`);

        $('#modalManageSerialsAdj').modal('hide');
    }
</script>
@endpush
