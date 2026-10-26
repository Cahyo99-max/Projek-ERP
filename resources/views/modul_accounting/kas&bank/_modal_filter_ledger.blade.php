<!-- Modal Filter Riwayat Mutasi & Rekening Koran (Modern FinTech Edition) -->
<div class="modal fade" id="modalFilterLedger" tabindex="-1" aria-labelledby="modalFilterLedgerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content modal-filter-premium border-0 shadow-lg overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="filter-header-badge">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalFilterLedgerLabel" style="letter-spacing: -0.2px; font-size: 1.05rem;">
                            Filter Buku Kas &amp; Rekening Koran
                        </h5>
                        <div class="text-muted small mt-0.5" style="font-size: 0.77rem;">
                            Saring mutasi kas berdasarkan periode bulan atau tanggal
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close-custom shadow-none" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-3.5 d-flex flex-column gap-3.5">
                
                <!-- 1. Quick Presets Bar (Pill Bar) -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="filter-section-title">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>PRESET CEPAT
                        </span>
                        <span class="text-muted small" style="font-size: 0.72rem;">Klik untuk rentang instan</span>
                    </div>
                    <div class="filter-presets-wrap" id="wrap_date_presets">
                        <button type="button" class="btn-filter-pill btn-date-preset" data-preset="today">Hari Ini</button>
                        <button type="button" class="btn-filter-pill btn-date-preset" data-preset="last7">7 Hari</button>
                        <button type="button" class="btn-filter-pill btn-date-preset active" data-preset="thisMonth">Bulan Ini</button>
                        <button type="button" class="btn-filter-pill btn-date-preset" data-preset="lastMonth">Bulan Lalu</button>
                        <button type="button" class="btn-filter-pill btn-date-preset" data-preset="thisYear">Tahun Ini</button>
                        <button type="button" class="btn-filter-pill btn-date-preset" data-preset="all">Semua Waktu</button>
                    </div>
                </div>

                <!-- 2. Interactive Month Navigator (Highlight Feature) -->
                <div class="month-navigator-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="filter-section-title">
                            <i class="bi bi-calendar-month text-success me-1"></i>PILIHAN BULAN PEMBUKUAN
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                            Saldo Awal s/d Saldo Akhir
                        </span>
                    </div>
                    
                    <div class="month-stepper-control">
                        <button type="button" class="btn-stepper-nav" id="btn_month_prev" title="Bulan Sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        
                        <div class="month-stepper-info" id="btn_stepper_pick_trigger" role="button" title="Pilih Bulan dari Kalender">
                            <div class="month-stepper-name" id="display_stepper_month">Oktober 2026</div>
                            <div class="month-stepper-meta" id="display_stepper_range">01/10/2026 s/d 31/10/2026</div>
                        </div>

                        <button type="button" class="btn-stepper-nav" id="btn_month_next" title="Bulan Berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>

                        <label for="modal_filter_month_input" class="btn-stepper-calendar" title="Pilih Kalender Bulan">
                            <i class="bi bi-calendar3"></i>
                            <input type="month" id="modal_filter_month_input" class="visually-hidden" value="{{ date('Y-m', strtotime($startDate)) }}">
                        </label>
                    </div>
                </div>

                <!-- 3. Rentang Tanggal Spesifik (Dual Inputs) -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="filter-section-title">
                            <i class="bi bi-calendar2-range text-primary me-1"></i>RENTANG TANGGAL BEBAS
                        </span>
                        <span class="text-muted small" style="font-size: 0.72rem;">Kustomisasi tanggal fleksibel</span>
                    </div>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="input-date-wrap">
                                <span class="input-date-icon"><i class="bi bi-calendar-event"></i></span>
                                <div class="w-100">
                                    <label class="input-date-label">DARI TANGGAL</label>
                                    <input type="date" class="input-date-field font-monospace" id="modal_filter_start_date" value="{{ $startDate }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-muted px-0 text-center" style="width: 20px;">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                        <div class="col">
                            <div class="input-date-wrap">
                                <span class="input-date-icon"><i class="bi bi-calendar-check"></i></span>
                                <div class="w-100">
                                    <label class="input-date-label">SAMPAI TANGGAL</label>
                                    <input type="date" class="input-date-field font-monospace" id="modal_filter_end_date" value="{{ $endDate }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Jenis Transaksi (Modern Glassmorphic Radio Tiles) -->
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="filter-section-title">
                            <i class="bi bi-tag-fill text-info me-1"></i>JENIS TRANSAKSI
                        </span>
                        <span class="text-muted small" style="font-size: 0.72rem;">Saring arus kas</span>
                    </div>

                    <div class="row g-2">
                        <!-- Semua -->
                        <div class="col-6 col-sm-3">
                            <input type="radio" class="btn-check" name="modal_filter_tipe_radio" id="modal_tipe_all" value="" checked>
                            <label class="filter-tile-radio tile-all" for="modal_tipe_all">
                                <div class="tile-icon-box"><i class="bi bi-collection"></i></div>
                                <div class="tile-title">Semua</div>
                                <div class="tile-sub">Semua mutasi</div>
                            </label>
                        </div>
                        <!-- Kas Masuk -->
                        <div class="col-6 col-sm-3">
                            <input type="radio" class="btn-check" name="modal_filter_tipe_radio" id="modal_tipe_in" value="Masuk">
                            <label class="filter-tile-radio tile-in" for="modal_tipe_in">
                                <div class="tile-icon-box"><i class="bi bi-arrow-down-left"></i></div>
                                <div class="tile-title">Kas Masuk</div>
                                <div class="tile-sub">Penerimaan</div>
                            </label>
                        </div>
                        <!-- Kas Keluar -->
                        <div class="col-6 col-sm-3">
                            <input type="radio" class="btn-check" name="modal_filter_tipe_radio" id="modal_tipe_out" value="Keluar">
                            <label class="filter-tile-radio tile-out" for="modal_tipe_out">
                                <div class="tile-icon-box"><i class="bi bi-arrow-up-right"></i></div>
                                <div class="tile-title">Kas Keluar</div>
                                <div class="tile-sub">Pengeluaran</div>
                            </label>
                        </div>
                        <!-- Transfer -->
                        <div class="col-6 col-sm-3">
                            <input type="radio" class="btn-check" name="modal_filter_tipe_radio" id="modal_tipe_transfer" value="Transfer">
                            <label class="filter-tile-radio tile-transfer" for="modal_tipe_transfer">
                                <div class="tile-icon-box"><i class="bi bi-arrow-left-right"></i></div>
                                <div class="tile-title">Transfer</div>
                                <div class="tile-sub">Antar rekening</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 5. Active Filter Live Preview Bar -->
                <div class="filter-live-preview">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <i class="bi bi-info-circle-fill text-success fs-6 flex-shrink-0"></i>
                        <div class="text-truncate">
                            <span class="text-muted small">Periode yang difilter:</span>
                            <span class="fw-bold text-body ms-1 font-monospace small" id="modal_live_preview_period">Oktober 2026</span>
                        </div>
                    </div>
                    <span class="badge bg-body border rounded-pill text-body-secondary font-monospace flex-shrink-0" id="modal_live_preview_tipe" style="font-size: 0.72rem;">Semua Transaksi</span>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-2 pb-4 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-1.5 filter-reset-link" id="btn_modal_reset_filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset ke Default</span>
                </button>
                
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm filter-btn-apply" id="btn_modal_apply_filter">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* ─── MODAL FILTER MODERN THEME (LIGHT & DARK COMPATIBLE) ─── */
    .modal-filter-premium {
        border-radius: 22px !important;
        background: var(--bs-body-bg, #ffffff);
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.45) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    [data-bs-theme="dark"] .modal-filter-premium {
        background: #0f172a !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .filter-header-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(5, 150, 105, 0.28) 100%);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }

    .btn-close-custom {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--bs-body-color, #64748b);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-close-custom:hover {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.3);
        transform: rotate(90deg);
    }

    .filter-section-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        color: var(--bs-secondary-color, #94a3b8);
        display: inline-flex;
        align-items: center;
    }

    /* Preset Pills */
    .filter-presets-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .btn-filter-pill {
        padding: 5px 13px;
        border-radius: 9999px;
        font-size: 0.76rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
        color: var(--bs-body-color, #475569);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
    }

    [data-bs-theme="dark"] .btn-filter-pill {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.08);
        color: #94a3b8;
    }

    .btn-filter-pill:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--bs-emphasis-color, #0f172a);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    [data-bs-theme="dark"] .btn-filter-pill:hover {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.08);
    }

    .btn-filter-pill.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border-color: #10b981 !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35) !important;
        font-weight: 700;
    }

    /* Month Navigator Card */
    .month-navigator-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 12px 14px;
    }

    [data-bs-theme="light"] .month-navigator-card {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    .month-stepper-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 6px 8px;
    }

    [data-bs-theme="light"] .month-stepper-control {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .btn-stepper-nav {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.05);
        color: var(--bs-body-color, #cbd5e1);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-stepper-nav:hover {
        background: rgba(16, 185, 129, 0.18);
        border-color: #10b981;
        color: #10b981;
        transform: scale(1.05);
    }

    .month-stepper-info {
        flex: 1;
        text-align: center;
        cursor: pointer;
        padding: 2px 8px;
        border-radius: 8px;
        transition: background 0.2s ease;
    }

    .month-stepper-info:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .month-stepper-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--bs-emphasis-color, #f8fafc);
        letter-spacing: -0.2px;
    }

    .month-stepper-meta {
        font-size: 0.71rem;
        color: var(--bs-secondary-color, #94a3b8);
        font-family: var(--bs-font-monospace);
    }

    .btn-stepper-calendar {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(16, 185, 129, 0.3);
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin: 0;
        transition: all 0.2s ease;
    }

    .btn-stepper-calendar:hover {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }

    /* Date Range Inputs */
    .input-date-wrap {
        display: flex;
        align-items: center;
        gap: 9px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 12px;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }

    [data-bs-theme="light"] .input-date-wrap {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .input-date-wrap:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        background: rgba(255, 255, 255, 0.06);
    }

    .input-date-icon {
        color: #10b981;
        font-size: 1.05rem;
    }

    .input-date-label {
        font-size: 0.63rem;
        font-weight: 700;
        color: var(--bs-secondary-color, #94a3b8);
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 1px;
    }

    .input-date-field {
        background: transparent;
        border: none;
        color: var(--bs-body-color, #f8fafc);
        font-size: 0.82rem;
        outline: none;
        width: 100%;
        color-scheme: dark;
    }

    [data-bs-theme="light"] .input-date-field {
        color-scheme: light;
        color: #1e293b;
    }

    /* Transaction Type Tile Cards */
    .filter-tile-radio {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 4px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.03);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: center;
        height: 100%;
        user-select: none;
    }

    [data-bs-theme="light"] .filter-tile-radio {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    .filter-tile-radio:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.18);
    }

    .tile-icon-box {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        margin-bottom: 5px;
        transition: all 0.2s ease;
    }

    .tile-all .tile-icon-box { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
    .tile-in .tile-icon-box { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .tile-out .tile-icon-box { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .tile-transfer .tile-icon-box { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }

    .tile-title {
        font-size: 0.77rem;
        font-weight: 700;
        color: var(--bs-body-color, #cbd5e1);
        margin-bottom: 1px;
    }

    .tile-sub {
        font-size: 0.65rem;
        color: var(--bs-secondary-color, #94a3b8);
    }

    /* Checked States */
    .btn-check:checked + .tile-all {
        border-color: #94a3b8 !important;
        background: rgba(148, 163, 184, 0.12) !important;
        box-shadow: 0 0 14px rgba(148, 163, 184, 0.2);
    }
    .btn-check:checked + .tile-all .tile-icon-box {
        background: #94a3b8;
        color: #ffffff;
    }

    .btn-check:checked + .tile-in {
        border-color: #10b981 !important;
        background: rgba(16, 185, 129, 0.12) !important;
        box-shadow: 0 0 14px rgba(16, 185, 129, 0.25);
    }
    .btn-check:checked + .tile-in .tile-icon-box {
        background: #10b981;
        color: #ffffff;
    }

    .btn-check:checked + .tile-out {
        border-color: #ef4444 !important;
        background: rgba(239, 68, 68, 0.12) !important;
        box-shadow: 0 0 14px rgba(239, 68, 68, 0.25);
    }
    .btn-check:checked + .tile-out .tile-icon-box {
        background: #ef4444;
        color: #ffffff;
    }

    .btn-check:checked + .tile-transfer {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.12) !important;
        box-shadow: 0 0 14px rgba(59, 130, 246, 0.25);
    }
    .btn-check:checked + .tile-transfer .tile-icon-box {
        background: #3b82f6;
        color: #ffffff;
    }

    /* Live Preview Bar */
    .filter-live-preview {
        background: rgba(16, 185, 129, 0.06);
        border: 1px dashed rgba(16, 185, 129, 0.28);
        border-radius: 12px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    /* Reset Link */
    .filter-reset-link {
        font-size: 0.78rem;
        color: var(--bs-secondary-color, #94a3b8) !important;
        transition: all 0.2s ease;
    }
    .filter-reset-link:hover {
        color: #ef4444 !important;
        transform: scale(1.02);
    }

    /* Apply Button */
    .filter-btn-apply {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        border: none !important;
        transition: all 0.2s ease;
    }
    .filter-btn-apply:hover {
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.5);
        transform: translateY(-1px);
    }
</style>
