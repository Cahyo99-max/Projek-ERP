<!-- Modal Create Alokasi Biaya (Landed Cost) -->
<div class="modal fade" id="modalCreateAlokasi" tabindex="-1" aria-labelledby="modalCreateAlokasiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-alokasi modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            
            <!-- GRADIENT MODAL HEADER -->
            <div class="modal-header border-0 px-3 px-md-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                    <div class="p-2 p-md-2.5 rounded-3 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); width: 40px; height: 40px;">
                        <i class="bi bi-calculator-fill fs-5 text-warning"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-1.5 gap-md-2">
                            <h5 class="modal-title fw-bold text-white mb-0 fs-6 fs-md-5 text-truncate" id="modalCreateAlokasiLabel">Buat Alokasi Biaya Pengiriman</h5>
                            <span class="badge rounded-pill px-2 py-0.5 text-white fw-semibold font-monospace d-none d-sm-inline-flex" style="background: rgba(245, 158, 11, 0.25); border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.7rem;">
                                <i class="bi bi-shield-check me-1"></i>HPP Engine
                            </span>
                        </div>
                        <small class="d-block text-truncate" style="color: rgba(255, 255, 255, 0.75); font-size: 0.78rem;">Kapitalisasi ongkir langsung ke HPP persediaan stok barang</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL BODY -->
            <div class="modal-body p-2.5 p-md-4" style="background-color: var(--erp-bg);">
                <form id="formCreateAlokasi">
                    @csrf
                    <input type="hidden" name="alokasi_id" id="create_alokasi_id" value="">

                    <!-- SECTION 1: PO EKSPEDISI & METODE -->
                    <div class="card border mb-3 shadow-sm" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; border-radius: 0.95rem;">
                        <div class="card-header border-0 bg-transparent py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="d-flex align-items-start gap-2.5 min-w-0">
                                <span class="step-num-badge">1</span>
                                <div class="min-w-0">
                                    <h6 class="fw-bold mb-0" style="color: var(--erp-text-main);">Informasi PO Jasa Ekspedisi &amp; Metode Alokasi</h6>
                                    <small class="d-block mt-0.5" style="color: var(--erp-text-muted);">Pilih PO jasa pengiriman dan metode proporsional alokasi biaya</small>
                                </div>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small flex-shrink-0">
                                <i class="bi bi-truck me-1"></i>Data Ekspedisi
                            </span>
                        </div>

                        <div class="card-body px-3 px-md-4 pt-1 pb-4">
                            <div class="row g-3">
                                <!-- Tanggal -->
                                <div class="col-12 col-md-2">
                                    <label class="form-label fw-semibold small" style="color: var(--erp-text-main);">Tanggal Alokasi <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_alokasi" id="create_tanggal_alokasi" class="form-control fw-semibold" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <!-- PO Jasa Ekspedisi -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold small" style="color: var(--erp-text-main);">Pilih PO Jasa Ekspedisi / Pengiriman <span class="text-danger">*</span></label>
                                    <select name="po_biaya_id" id="create_po_biaya_id" class="form-select select2-po" required style="width: 100%;">
                                        <option value="">-- Memuat daftar PO... --</option>
                                    </select>
                                    <div class="form-text small mt-1" id="po_selected_info" style="color: var(--erp-text-muted);">
                                        <i class="bi bi-funnel text-primary me-1"></i>Hanya menampilkan PO Kategori Jasa Ekspedisi / Pengiriman
                                    </div>
                                </div>

                                <!-- Proyek / Tender Terkait -->
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold small" style="color: var(--erp-text-main);">Proyek / Tender Terkait</label>
                                    <select name="tender_id" id="create_tender_id" class="form-select select2-tender" style="width: 100%;">
                                        <option value="">-- Tanpa Proyek / Tender --</option>
                                        @if(isset($tenders) && $tenders->count() > 0)
                                            @foreach($tenders as $tender)
                                                @php
                                                    $tDisplay = !empty($tender->nomor_kontrak) && $tender->nomor_kontrak !== '-'
                                                        ? ($tender->nomor_kontrak . (!empty($tender->nama_tender) ? ' - ' . $tender->nama_tender : ''))
                                                        : ($tender->nama_tender ?: ('Tender #' . $tender->id));
                                                @endphp
                                                <option value="{{ $tender->id }}">{{ $tDisplay }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="form-text small" style="color: var(--erp-text-muted);">
                                        <i class="bi bi-briefcase me-1"></i>Tercatat di Jurnal Akuntansi
                                    </div>
                                </div>

                                <!-- Total Biaya Ekspedisi (Readonly terkunci dari PO) -->
                                <div class="col-12 col-md-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold small mb-0" style="color: var(--erp-text-main);">Total Biaya Ekspedisi (Rp) <span class="text-danger">*</span></label>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small font-monospace" style="font-size: 0.65rem;">
                                            <i class="bi bi-lock-fill me-0.5"></i>Terkunci
                                        </span>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold text-success" style="background-color: var(--erp-table-header); border-color: var(--erp-border);">Rp</span>
                                        <input type="text" id="create_total_biaya_display" class="form-control form-control-lg fw-bold text-end font-monospace text-success" 
                                               placeholder="0,00" autocomplete="off" readonly required 
                                               style="font-size: 1.15rem; letter-spacing: 0.5px; background-color: var(--erp-table-header) !important; cursor: not-allowed;"
                                               title="Nilai total biaya terkunci otomatis dari PO Jasa yang dipilih">
                                        <input type="hidden" name="total_biaya" id="create_total_biaya" value="0">
                                    </div>
                                    <div class="form-text small" style="color: var(--erp-text-muted); font-size: 0.72rem;">
                                        <i class="bi bi-shield-check text-success me-1"></i>Otomatis diambil dari <b>DPP PO Jasa</b> (murni biaya jasa, PPN &amp; PPh <u>tidak dihitung</u> ke HPP).
                                    </div>
                                </div>

                                <!-- Metode Alokasi -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold small" style="color: var(--erp-text-main);">Metode Alokasi Biaya <span class="text-danger">*</span></label>
                                    <select name="metode_alokasi" id="create_metode_alokasi" class="form-select fw-semibold" required>
                                        <option value="Berdasarkan Nilai Barang" selected>Berdasarkan Nilai Barang (Proporsional Harga - Standar)</option>
                                        <option value="Berdasarkan Qty">Berdasarkan Kuantitas (Dibagi Rata per Qty)</option>
                                    </select>
                                </div>

                                <!-- Keterangan (Maks 40 karakter) -->
                                <div class="col-12 col-md-8">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold small mb-0" style="color: var(--erp-text-main);">Keterangan / Catatan Ekspedisi</label>
                                        <span class="small text-muted font-monospace" id="keterangan_char_count" style="font-size: 0.72rem;">0/40 karakter</span>
                                    </div>
                                    <input type="text" name="keterangan" id="create_keterangan" class="form-control" maxlength="40" placeholder="Contoh: Ongkir truk kargo genset (Maks. 40 karakter)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: PILIH PENERIMAAN BARANG (GR) -->
                    <div class="card border mb-3 shadow-sm" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; border-radius: 0.95rem;">
                        <div class="card-header border-0 bg-transparent py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="d-flex align-items-start gap-2.5 min-w-0">
                                <span class="step-num-badge">2</span>
                                <div class="min-w-0">
                                    <h6 class="fw-bold mb-0" style="color: var(--erp-text-main);">Pilih Penerimaan Barang (Goods Receipt) Terkait</h6>
                                    <small class="d-block mt-0.5" style="color: var(--erp-text-muted);">Centang satu atau beberapa GR yang barang fisiknya diantar oleh ekspedisi ini</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold flex-shrink-0" id="btnRefreshGr">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Daftar GR
                            </button>
                        </div>

                        <div class="card-body px-2.5 px-md-4 pt-1 pb-3">
                            <!-- Toolbar Filter & Pencarian GR -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 mb-2.5 p-2 rounded-3" 
                                 style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid var(--erp-border);">
                                <!-- Selector Jumlah Tampil (Default 10) -->
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small fw-semibold text-nowrap" style="color: var(--erp-text-muted); font-size: 0.78rem;">
                                        <i class="bi bi-list-ol me-1 text-primary"></i>Tampilkan:
                                    </span>
                                    <select id="grPageSizeSelect" class="form-select form-select-sm py-1 font-monospace fw-semibold" style="width: 85px; border-radius: 8px;">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="all">Semua</option>
                                    </select>
                                    <span class="small text-muted d-none d-sm-inline" style="font-size: 0.76rem;">data per halaman</span>
                                </div>

                                <!-- Kolom Pencarian Cepat GR -->
                                <div class="input-group input-group-sm" style="max-width: 340px;">
                                    <span class="input-group-text bg-transparent border-end-0" style="color: var(--erp-text-muted);">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="searchGrInput" class="form-control border-start-0 ps-1" placeholder="Cari No. GR, PO, Supplier, Gudang..." autocomplete="off">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" id="btnClearSearchGr" style="display: none;" title="Hapus pencarian">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Mobile swipe hint -->
                            <div class="d-flex d-md-none align-items-center gap-1.5 text-muted small px-1 pb-2">
                                <i class="bi bi-arrows-expand-vertical text-primary"></i>
                                <span>Geser tabel ke samping untuk melihat data GR lengkap</span>
                            </div>

                            <div class="table-responsive border rounded-3" style="max-height: 480px; overflow-y: auto; overflow-x: auto; border-color: var(--erp-border) !important; -webkit-overflow-scrolling: touch;">
                                <table class="table table-sm table-hover align-middle mb-0" id="tableEligibleGr">
                                    <thead class="sticky-top" style="background-color: var(--erp-table-header);">
                                        <tr class="small fw-bold text-uppercase" style="color: var(--erp-text-muted); font-size: 0.73rem; letter-spacing: 0.5px;">
                                            <th style="width: 44px; min-width: 44px;" class="text-center py-2.5">
                                                <input type="checkbox" class="form-check-input" id="checkAllGr" style="cursor: pointer;">
                                            </th>
                                            <th class="text-nowrap" style="min-width: 130px;">No. GR</th>
                                            <th class="text-nowrap" style="min-width: 100px;">Tanggal</th>
                                            <th class="text-nowrap" style="min-width: 130px;">PO Barang</th>
                                            <th class="text-nowrap" style="min-width: 180px;">Supplier Barang</th>
                                            <th class="text-nowrap" style="min-width: 150px;">Tender / Proyek</th>
                                            <th class="text-nowrap" style="min-width: 130px;">Gudang Masuk</th>
                                            <th class="text-end text-nowrap" style="min-width: 100px;">Total Qty</th>
                                            <th class="text-end pe-3 text-nowrap" style="min-width: 140px;">Total Nilai Beli</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyEligibleGr">
                                        <tr>
                                            <td colspan="9" class="text-center py-4" style="color: var(--erp-text-muted);">
                                                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                                Memuat data penerimaan barang...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Navigasi Halaman & Info Jumlah GR -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-2.5 pt-2 border-top" id="grPaginationContainer" style="border-color: var(--erp-border) !important;">
                                <div class="small" id="grPaginationInfo" style="color: var(--erp-text-muted); font-size: 0.78rem;">
                                    Memuat data GR...
                                </div>
                                <nav aria-label="Navigasi Halaman GR">
                                    <ul class="pagination pagination-sm mb-0 gap-1" id="grPaginationList">
                                        <!-- Rendered by JS -->
                                    </ul>
                                </nav>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2.5 mt-3 pt-2">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold" id="selectedGrCountInfo">
                                        0 GR terpilih
                                    </span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small" id="realtimeCalcIndicator" style="display: none;">
                                        <i class="bi bi-lightning-charge-fill me-1 text-warning"></i>Kalkulasi Real-time Aktif
                                    </span>
                                </div>
                                <button type="button" class="btn fw-bold px-4 py-2 text-white shadow-sm rounded-pill w-100 w-sm-auto" id="btnHitungPreview"
                                        style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);"
                                        title="Kalkulasi berjalan otomatis secara real-time saat Anda memilih GR atau mengganti metode. Klik untuk hitung ulang manual.">
                                    <i class="bi bi-cpu-fill me-1.5"></i> Hitung Ulang Alokasi HPP
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: HASIL SIMULASI TABEL ALOKASI HPP -->
                    <div class="card border shadow-sm" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; border-radius: 0.95rem;">
                        <div class="card-header border-0 bg-transparent py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="d-flex align-items-start gap-2.5 min-w-0">
                                <span class="step-num-badge">3</span>
                                <div class="min-w-0">
                                    <h6 class="fw-bold mb-0" style="color: var(--erp-text-main);">Rincian &amp; Pratinjau Alokasi HPP Baru</h6>
                                    <small class="d-block mt-0.5" style="color: var(--erp-text-muted);">Simulasi nilai kapitalisasi ongkir ke masing-masing item barang dan HPP akhir</small>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-3 py-1.5 fw-semibold flex-shrink-0" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                                <i class="bi bi-graph-up-arrow me-1"></i>Kalkulasi Presisi
                            </span>
                        </div>

                        <div class="card-body px-2.5 px-md-4 pt-1 pb-3">
                            <!-- Mobile swipe hint -->
                            <div class="d-flex d-md-none align-items-center gap-1.5 text-muted small px-1 pb-2">
                                <i class="bi bi-arrows-expand-vertical text-primary"></i>
                                <span>Geser tabel ke samping untuk melihat rincian kalkulasi</span>
                            </div>

                            <div class="table-responsive border rounded-3" style="border-color: var(--erp-border) !important; max-height: 380px; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0" id="tablePreviewAlokasi" style="border-color: var(--erp-border);">
                                    <thead class="sticky-top" style="background-color: var(--erp-table-header);">
                                        <tr class="small text-center align-middle" style="color: var(--erp-text-muted); font-size: 0.74rem;">
                                            <th rowspan="2" class="text-nowrap" style="width: 38px; min-width: 38px; background-color: var(--erp-table-header);">#</th>
                                            <th rowspan="2" class="text-nowrap" style="min-width: 125px; background-color: var(--erp-table-header);">No. GR</th>
                                            <th rowspan="2" class="text-nowrap" style="background-color: var(--erp-table-header); min-width: 220px;">Barang</th>
                                            <th rowspan="2" class="text-end text-nowrap" style="min-width: 105px; background-color: var(--erp-table-header);">Qty Diterima</th>
                                            <th rowspan="2" class="text-end text-nowrap" style="min-width: 130px; background-color: var(--erp-table-header);">Nilai Beli (Rp)</th>
                                            <th rowspan="2" class="text-end text-nowrap" style="min-width: 85px; background-color: var(--erp-table-header);">Porsi (%)</th>
                                            <th colspan="2" class="text-center fw-bold text-nowrap" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border-bottom: 2px solid #f59e0b;">
                                                <i class="bi bi-box-arrow-in-down-right me-1"></i> Alokasi Biaya Ekspedisi
                                            </th>
                                            <th colspan="2" class="text-center fw-bold text-nowrap" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border-bottom: 2px solid #10b981;">
                                                <i class="bi bi-calculator me-1"></i> Hasil HPP Persediaan
                                            </th>
                                        </tr>
                                        <tr class="small align-middle" style="color: var(--erp-text-muted); font-size: 0.73rem;">
                                            <th class="text-end text-nowrap" style="min-width: 125px; background: rgba(245, 158, 11, 0.08); color: #f59e0b;">Total (Rp)</th>
                                            <th class="text-end text-nowrap" style="min-width: 110px; background: rgba(245, 158, 11, 0.08); color: #f59e0b;">Per Unit (Rp)</th>
                                            <th class="text-end text-nowrap" style="min-width: 120px; background: rgba(16, 185, 129, 0.08); color: #10b981;">HPP Awal (Rp)</th>
                                            <th class="text-end fw-bold text-nowrap" style="min-width: 130px; background: rgba(16, 185, 129, 0.18); color: #10b981;">HPP Baru (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPreviewAlokasi">
                                        <tr>
                                            <td colspan="10" class="text-center py-5 px-3" style="color: var(--erp-text-muted);">
                                                <div class="d-inline-flex p-3 rounded-circle bg-warning bg-opacity-10 text-warning mb-2">
                                                    <i class="bi bi-info-circle fs-4"></i>
                                                </div>
                                                <div class="fw-semibold text-secondary">Belum Ada Simulasi Kalkulasi</div>
                                                <div class="small text-muted mt-1" style="max-width: 480px; margin: 0 auto;">
                                                    Centang GR di atas lalu klik tombol <b>"Hitung &amp; Simulasikan Alokasi HPP"</b> untuk melihat hasil kalkulasi per unit barang.
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="fw-bold" id="tfootPreviewAlokasi" style="display: none; background-color: var(--erp-table-header);">
                                        <tr style="border-top: 2px solid var(--erp-border);">
                                            <td colspan="3" class="text-end text-uppercase text-nowrap" style="color: var(--erp-text-main);">TOTAL REKAP:</td>
                                            <td class="text-end font-monospace text-nowrap" id="footTotalQty" style="color: var(--erp-text-main);">0</td>
                                            <td class="text-end font-monospace text-success text-nowrap" id="footTotalNilai">Rp 0</td>
                                            <td class="text-end font-monospace text-info text-nowrap" id="footTotalPorsi">100%</td>
                                            <td class="text-end font-monospace fw-bold text-nowrap" id="footTotalBiaya" style="color: #f59e0b;">Rp 0</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- MODAL FOOTER -->
            <div class="modal-footer border-0 px-3 px-md-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2" style="background-color: var(--erp-card-bg); border-top: 1px solid var(--erp-border) !important;">
                <button type="button" class="btn btn-outline-secondary px-3.5 py-2 rounded-pill w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Batal
                </button>
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto order-1 order-sm-2">
                    @can('tambah-alokasi-biaya')
                    <button type="button" class="btn btn-outline-primary px-3.5 py-2 rounded-pill fw-semibold w-100 w-sm-auto" id="btnSimpanDraft">
                        <i class="bi bi-pencil-square me-1"></i> Simpan Draft
                    </button>
                    @endcan
                    @can('posting-alokasi-biaya')
                    <button type="button" class="btn text-white px-4 py-2 rounded-pill fw-bold shadow w-100 w-sm-auto" id="btnSimpanPost"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Simpan &amp; Posting ke HPP
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
