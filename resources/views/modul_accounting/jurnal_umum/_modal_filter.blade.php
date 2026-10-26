<!-- Modal Filter Buku Jurnal Umum (Modern Pro FinTech Edition) -->
<div class="modal fade" id="modalFilterJurnal" tabindex="-1" aria-labelledby="modalFilterJurnalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 960px; width: 96%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden ju-modal-filter-content">
            
            <!-- Modal Header Modern Gradient (Sesuai Tema Cyber Royal Blue) -->
            <div class="modal-header border-0 py-3.5 px-4 position-relative overflow-hidden ju-modal-filter-header text-white">
                <div class="ju-card-shine"></div>
                <div class="d-flex align-items-center gap-3 position-relative z-1">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm ju-filter-header-icon" style="width: 44px; height: 44px;">
                        <i class="bi bi-funnel-fill fs-5 text-warning"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalFilterJurnalLabel" style="letter-spacing: -0.2px;">
                                Filter Riwayat Jurnal Umum
                            </h5>
                            <span class="badge rounded-pill fw-normal text-white d-none d-sm-inline-block" style="background: rgba(255,255,255,0.18); font-size: 0.70rem; border: 1px solid rgba(255,255,255,0.25);">
                                General Journal Filter
                            </span>
                        </div>
                        <div class="text-white text-opacity-80 small mt-0.5" style="font-size: 0.78rem;">
                            Saring pencatatan pembukuan akuntansi berdasarkan rentang tanggal &amp; sumber modul transaksi
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none position-relative z-1" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body dengan Card Containers Terpisah & Berjarak -->
            <div class="modal-body p-4 bg-body-tertiary d-flex flex-column gap-3.5">
                
                <!-- CARD 1: Periode & Rentang Tanggal Jurnal -->
                <div class="card border rounded-4 shadow-sm bg-body ju-filter-section-card">
                    <div class="card-body p-3.5">
                        
                        <!-- Header Card 1 -->
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-1.5 rounded-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-calendar-check fs-6"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-body-emphasis small text-uppercase" style="letter-spacing: 0.5px;">1. Periode &amp; Rentang Tanggal</span>
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                                Wajib Terisi
                            </span>
                        </div>

                        <!-- 1A: Presets Rentang Cepat -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-secondary small fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pilihan Rentang Cepat (Preset):
                                </span>
                                <span class="text-muted small font-monospace" style="font-size: 0.70rem;">Klik untuk rentang instan</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="wrap_modal_date_presets">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="today">Hari Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="last7">7 Hari</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset active" data-preset="this_month">Bulan Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="last_month">Bulan Lalu</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="30_days">30 Hari</button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="this_year">Tahun Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold btn-modal-preset" data-preset="all">Semua Waktu</button>
                            </div>
                        </div>

                        <!-- 1B: Grid 2 Kolom (Bulan Stepper & Custom Date Input) -->
                        <div class="row g-3">
                            <!-- Kolom Kiri: Navigator Bulan -->
                            <div class="col-md-6">
                                <div class="p-2.5 rounded-3 border bg-body-tertiary h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <span class="text-secondary small fw-semibold" style="font-size: 0.74rem;">
                                            <i class="bi bi-calendar-month text-primary me-1"></i>Pilihan Bulan Pembukuan
                                        </span>
                                        <span class="badge bg-body border text-muted font-monospace" style="font-size: 0.65rem;">Kalender</span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between gap-1 p-1 rounded-3 bg-body border">
                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-circle d-flex align-items-center justify-content-center ju-stepper-btn" id="btn_modal_month_prev" title="Bulan Sebelumnya" style="width: 32px; height: 32px;">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        
                                        <div class="text-center flex-grow-1 user-select-none py-0.5" style="cursor: pointer;" id="trigger_modal_month_picker" title="Klik untuk pilih bulan">
                                            <div class="fw-bold text-body-emphasis small" id="display_modal_stepper_month">Oktober 2026</div>
                                            <div class="text-muted font-monospace" style="font-size: 0.70rem;" id="display_modal_stepper_range">01/10/2026 s/d 31/10/2026</div>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-light border-0 rounded-circle d-flex align-items-center justify-content-center ju-stepper-btn" id="btn_modal_month_next" title="Bulan Berikutnya" style="width: 32px; height: 32px;">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>

                                        <label for="modal_month_input" class="btn btn-sm btn-outline-secondary border-0 rounded-circle d-flex align-items-center justify-content-center mb-0 ju-stepper-btn" title="Buka Kalender Bulan" style="width: 32px; height: 32px; cursor: pointer;">
                                            <i class="bi bi-calendar3"></i>
                                            <input type="month" id="modal_month_input" class="visually-hidden" value="{{ date('Y-m', strtotime($startDate)) }}">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Rentang Tanggal Spesifik -->
                            <div class="col-md-6">
                                <div class="p-2.5 rounded-3 border bg-body-tertiary h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <span class="text-secondary small fw-semibold" style="font-size: 0.74rem;">
                                            <i class="bi bi-calendar-range text-info me-1"></i>Rentang Tanggal Spesifik
                                        </span>
                                        <span class="badge bg-body border text-muted font-monospace" style="font-size: 0.65rem;">Custom</span>
                                    </div>
                                    
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-body text-muted border-end-0" style="font-size: 0.72rem;">Dari</span>
                                                <input type="date" class="form-control font-monospace border-start-0 ps-1" id="modal_filter_start_date" value="{{ $startDate }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-body text-muted border-end-0" style="font-size: 0.72rem;">S/d</span>
                                                <input type="date" class="form-control font-monospace border-start-0 ps-1" id="modal_filter_end_date" value="{{ $endDate }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- CARD 2: Sumber & Modul Transaksi Asal (Searchable) -->
                <div class="card border rounded-4 shadow-sm bg-body ju-filter-section-card">
                    <div class="card-body p-3.5">
                        
                        <!-- Header Card 2 -->
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-1.5 rounded-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-tags fs-6"></i>
                                </div>
                                <div>
                                    <span class="fw-bold text-body-emphasis small text-uppercase" style="letter-spacing: 0.5px;">2. Sumber / Modul Transaksi</span>
                                </div>
                            </div>
                            <span class="badge bg-body-tertiary border text-secondary rounded-pill font-monospace" style="font-size: 0.68rem;">
                                Searchable
                            </span>
                        </div>

                        <!-- Dropdown Select2 Searchable -->
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <label for="modal_filter_referensi_tipe" class="text-secondary small fw-semibold mb-0" style="font-size: 0.74rem;">
                                    Pilih Modul Transaksi Akuntansi:
                                </label>
                                <span class="text-muted small" style="font-size: 0.70rem;">Bisa diketik untuk mencari</span>
                            </div>
                            <select class="form-select select2-filter-tipe" id="modal_filter_referensi_tipe" style="width: 100%;">
                                <option value="">-- Semua Sumber Transaksi --</option>
                                <option value="Kas Mutasi">💰 Kas &amp; Rekening Bank (Treasury)</option>
                                <option value="PettyCash">👛 Kas Kecil (Petty Cash)</option>
                                <option value="Sales">🛒 Penjualan &amp; Pembayaran Piutang (Sales)</option>
                                <option value="Purchase">📦 Pembelian &amp; Pembayaran Utang (Purchase)</option>
                                <option value="Alokasi Biaya">🚢 Alokasi Biaya Ekspedisi (Landed Cost)</option>
                                <option value="Delivery Order">🚚 Logistik &amp; Surat Jalan (Delivery Order)</option>
                                <option value="Goods Receipt">📥 Penerimaan Barang Gudang (Goods Receipt)</option>
                                <option value="Manufaktur">⚙️ Produksi &amp; Bahan Baku SPK</option>
                                <option value="Stok">📊 Persediaan &amp; Stok Opname</option>
                                <option value="Reversal">🔄 Jurnal Pembalik (Reversal)</option>
                                <option value="Saldo Awal">🏁 Saldo Awal Akun / Rekening</option>
                                <option value="Manual">✍️ Jurnal Manual / Penyesuaian</option>
                            </select>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Modal Footer Modern -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5" id="btn_modal_reset_filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Filter</span>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    @can('cetak-jurnal-umum')
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5" id="btn_modal_print_period" title="Cetak Laporan Jurnal Umum sesuai filter ini">
                        <i class="bi bi-printer-fill"></i>
                        <span>Cetak Laporan (PDF)</span>
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5" id="btn_modal_export_excel" title="Export Laporan Jurnal Umum ke Excel sesuai filter ini">
                        <i class="bi bi-file-earmark-excel-fill text-success"></i>
                        <span>Export Excel</span>
                    </button>
                    @endcan
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 ju-btn-apply" id="btn_modal_apply_filter">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
