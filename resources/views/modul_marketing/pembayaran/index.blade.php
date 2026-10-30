<x-layouts>
    <x-slot:title>Penerimaan Pembayaran (Customer Payment)</x-slot:title>

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_smd/pembayaran.css') }}?v={{ time() }}" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.625rem;
            min-height: 38px;
            font-size: 0.85rem;
            padding: 4px 10px;
            border-color: var(--bs-border-color, #dee2e6);
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--bs-body-color, #212529);
            line-height: 28px;
            padding-left: 2px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--bs-secondary-color, #6c757d);
        }
        .select2-dropdown {
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--bs-border-color, #dee2e6);
            background-color: var(--bs-body-bg, #ffffff);
            font-size: 0.84rem;
            z-index: 1070 !important;
            overflow: hidden;
        }
        .select2-container--bootstrap-5 .select2-search {
            padding: 8px;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 0.5rem;
            font-size: 0.82rem;
            padding: 6px 12px;
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            border-color: var(--bs-border-color, #dee2e6);
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #059669 !important;
            color: #ffffff !important;
        }

        /* ════════════════════════════════════════════════════════════════
           MODERN DARK SELECT2 FOR COA POTONGAN IN SUMMARY CARD
           ════════════════════════════════════════════════════════════════ */
        .dark-select2-container .select2-container {
            width: 100% !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection {
            background-color: #0b1120 !important;
            border: 1px solid rgba(245, 158, 11, 0.45) !important;
            border-radius: 0.625rem !important;
            min-height: 42px !important;
            display: flex !important;
            align-items: center !important;
            padding: 4px 12px !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4), 0 1px 2px rgba(245, 158, 11, 0.1) !important;
            transition: all 0.2s ease-in-out !important;
        }
        .dark-select2-container .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .dark-select2-container .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 0.25rem rgba(245, 158, 11, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.4) !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #fef08a !important;
            font-family: var(--bs-font-monospace, monospace) !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            line-height: 32px !important;
            padding-left: 0 !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-family: var(--bs-font-sans-serif, sans-serif) !important;
            font-weight: normal !important;
            font-size: 0.82rem !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            color: #f87171 !important;
            font-size: 1.1rem !important;
            margin-right: 8px !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
            color: #ef4444 !important;
        }
        .dark-select2-container .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow b {
            border-color: #f59e0b transparent transparent transparent !important;
        }
        .dark-select2-container .select2-container--bootstrap-5.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #f59e0b transparent !important;
        }

        /* Dropdown Popup Styling for Dark Amber Theme */
        .select2-dropdown-dark-amber {
            background-color: #0f172a !important;
            border: 1px solid rgba(245, 158, 11, 0.35) !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.65), 0 0 15px rgba(245, 158, 11, 0.15) !important;
            overflow: hidden !important;
            z-index: 1075 !important;
        }
        .select2-dropdown-dark-amber .select2-search {
            padding: 10px !important;
            background-color: #1e293b !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .select2-dropdown-dark-amber .select2-search__field {
            background-color: #0f172a !important;
            border: 1px solid #475569 !important;
            color: #f8fafc !important;
            border-radius: 0.5rem !important;
            padding: 8px 12px !important;
            font-size: 0.825rem !important;
        }
        .select2-dropdown-dark-amber .select2-search__field:focus {
            border-color: #f59e0b !important;
            outline: none !important;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.2) !important;
        }
        .select2-dropdown-dark-amber .select2-results__options {
            max-height: 250px !important;
            padding: 4px !important;
        }
        .select2-dropdown-dark-amber .select2-results__option {
            color: #cbd5e1 !important;
            font-family: var(--bs-font-monospace, monospace) !important;
            font-size: 0.825rem !important;
            padding: 8px 12px !important;
            border-radius: 0.375rem !important;
            margin-bottom: 2px !important;
            transition: all 0.15s ease !important;
        }
        .select2-dropdown-dark-amber .select2-results__option--highlighted {
            background-color: #f59e0b !important;
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        .select2-dropdown-dark-amber .select2-results__option--selected {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fbbf24 !important;
            font-weight: 600 !important;
        }

        /* Input Group Integration for Select2 */
        .input-group > .select2-container--bootstrap-5 {
            flex: 1 1 auto;
            width: 1% !important;
        }
        .input-group > .select2-container--bootstrap-5 .select2-selection {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* Preview Modal Stacking */
        #modalPreviewBuktiTransfer {
            z-index: 1080 !important;
        }
        .modal-backdrop.preview-backdrop {
            z-index: 1075 !important;
        }

        /* ════════════════════════════════════════════════════════════════
           CINEMATIC ACTION BUTTONS & POD CONTAINER
           ════════════════════════════════════════════════════════════════ */
        .action-cinematic-group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 3px 6px;
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .btn-action-cinematic {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            font-size: 0.9rem;
            transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            cursor: pointer;
            text-decoration: none;
            outline: none !important;
            border: 1px solid transparent;
        }

        .btn-action-cinematic:hover {
            transform: translateY(-2px) scale(1.1);
            z-index: 2;
        }

        .btn-action-cinematic:active {
            transform: translateY(0) scale(0.94);
        }

        /* 1. Detail / View (Cyan Neon) */
        .action-btn-view {
            background: rgba(14, 165, 233, 0.1);
            border-color: rgba(14, 165, 233, 0.3);
            color: #38bdf8 !important;
        }
        .action-btn-view:hover {
            background: rgba(14, 165, 233, 0.25);
            border-color: #38bdf8;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(56, 189, 248, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 2. Print Kwitansi (Platinum Slate) */
        .action-btn-print {
            background: rgba(148, 163, 184, 0.1);
            border-color: rgba(148, 163, 184, 0.3);
            color: #cbd5e1 !important;
        }
        .action-btn-print:hover {
            background: rgba(148, 163, 184, 0.25);
            border-color: #e2e8f0;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(226, 232, 240, 0.45), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 3. Edit Draft / Diajukan (Amber Sun) */
        .action-btn-edit {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fbbf24 !important;
        }
        .action-btn-edit:hover {
            background: rgba(245, 158, 11, 0.25);
            border-color: #fbbf24;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(251, 191, 36, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 4. Submit / Ajukan ke Atasan (Cyan Sky Rocket) */
        .action-btn-submit {
            background: rgba(6, 182, 212, 0.1);
            border-color: rgba(6, 182, 212, 0.35);
            color: #22d3ee !important;
        }
        .action-btn-submit:hover {
            background: rgba(6, 182, 212, 0.25);
            border-color: #22d3ee;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(34, 211, 238, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 5. Post / ACC Konfirmasi (Emerald Green) */
        .action-btn-post {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(16, 185, 129, 0.4);
            color: #34d399 !important;
        }
        .action-btn-post:hover {
            background: rgba(16, 185, 129, 0.28);
            border-color: #10b981;
            color: #ffffff !important;
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.65), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 6. Delete / Batal Void (Ruby Crimson) */
        .action-btn-delete {
            background: rgba(244, 63, 94, 0.1);
            border-color: rgba(244, 63, 94, 0.35);
            color: #fb7185 !important;
        }
        .action-btn-delete:hover {
            background: rgba(244, 63, 94, 0.25);
            border-color: #f43f5e;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(244, 63, 94, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* 7. Jurnal Button (Violet / Indigo Neon) */
        .jurnal-cinematic-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 9px;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.35);
            color: #a78bfa !important;
            transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.15);
        }
        .jurnal-cinematic-badge:hover {
            background: rgba(139, 92, 246, 0.26);
            border-color: #c084fc;
            color: #ffffff !important;
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 0 15px rgba(192, 132, 252, 0.65), 0 2px 6px rgba(0,0,0,0.4);
        }
        .jurnal-cinematic-badge:active {
            transform: translateY(0) scale(0.94);
        }

        /* Cinematic Nav Tabs */
        .nav-cinematic {
            background: rgba(15, 23, 42, 0.05);
            border-radius: 12px;
            padding: 4px;
            display: inline-flex;
            gap: 4px;
            border: 1px solid var(--bs-border-color, #e2e8f0);
        }
        .nav-cinematic .nav-link {
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.86rem;
            color: var(--bs-body-color, #475569);
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-cinematic .nav-link:hover {
            background: rgba(0, 0, 0, 0.04);
            color: var(--bs-primary, #059669);
        }
        .nav-cinematic .nav-link.active {
            background: #059669;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
    </style>
    @endpush

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </span>
                    Penerimaan Pembayaran
                </h4>
                <p class="text-body-secondary small mb-0 mt-1">Catat penerimaan kas/bank dari pelanggan, pelunasan piutang multi-faktur, dan cetak kwitansi resmi.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap pay-header-actions">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" id="btn_open_filter" data-bs-toggle="modal" data-bs-target="#modalFilterPayment">
                    <i class="bi bi-funnel-fill text-success fs-6"></i>
                    <span>Filter Data</span>
                </button>

                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-sales-payment'))
                <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btn_open_modal_create" data-bs-toggle="modal" data-bs-target="#modalCreatePayment">
                    <i class="bi bi-plus-circle-fill fs-6"></i> <span>Catat Pembayaran</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs: Switcher antara Pembayaran dan Rekap Piutang -->
        <div class="mb-4">
            <div class="nav-cinematic">
                <a href="{{ route('modul_marketing.pembayaran.index') }}" class="nav-link active">
                    <i class="bi bi-cash-stack"></i>
                    <span>Riwayat Penerimaan Pembayaran</span>
                </a>
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-piutang'))
                <a href="{{ route('modul_marketing.piutang.index') }}" class="nav-link">
                    <i class="bi bi-card-checklist"></i>
                    <span>Rekap Sisa Tagihan & Piutang Pelanggan</span>
                </a>
                @endif
            </div>
        </div>

        <!-- KPI Metric Cards Summary Row (Modern Glassmorphic) -->
        <div class="row g-3 g-sm-3.5 g-md-4 mb-4 mb-md-4.5">
            <!-- Total Penerimaan Bulan Ini -->
            <div class="col-6 col-md-3">
                <div class="pay-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pay-kpi-label">Penerimaan Bulan Ini</span>
                            <div class="pay-kpi-icon pay-kpi-icon-emerald">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-success font-monospace fs-4 fs-md-3 text-truncate" id="kpi_total_bulan_ini" title="Rp {{ number_format($totalBulanIni, 0, ',', '.') }}">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-calendar2-month me-1"></i>Bulan Berjalan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Penerimaan Hari Ini -->
            <div class="col-6 col-md-3">
                <div class="pay-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pay-kpi-label">Penerimaan Hari Ini</span>
                            <div class="pay-kpi-icon pay-kpi-icon-sky">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-info font-monospace fs-4 fs-md-3 text-truncate" id="kpi_total_hari_ini" title="Rp {{ number_format($totalHariIni, 0, ',', '.') }}">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-clock-history me-1"></i>Kas Masuk Hari Ini
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Transaksi Selesai -->
            <div class="col-6 col-md-3">
                <div class="pay-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pay-kpi-label">Transaksi Selesai</span>
                            <div class="pay-kpi-icon pay-kpi-icon-indigo">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-body font-monospace fs-4 fs-md-3">
                            <span id="kpi_count_trx">{{ number_format($countTrx, 0, ',', '.') }}</span>
                            <small class="fs-7 text-body-secondary font-sans">Kwitansi</small>
                        </h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-file-earmark-check me-1"></i>Kwitansi Terbit
                        </span>
                    </div>
                </div>
            </div>

            <!-- Potongan Diberikan -->
            <div class="col-6 col-md-3">
                <div class="pay-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="pay-kpi-label">Potongan Bulan Ini</span>
                            <div class="pay-kpi-icon pay-kpi-icon-amber">
                                <i class="bi bi-percent"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-warning font-monospace fs-4 fs-md-3 text-truncate" id="kpi_potongan" title="Rp {{ number_format($totalPotongan, 0, ',', '.') }}">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-tag me-1"></i>Diskon / Potongan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden pay-table-card">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-success"></i> Riwayat Penerimaan Pembayaran
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1 fw-bold d-none d-sm-inline-block">Live Data</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btn_refresh_table">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="card-body p-3 p-md-3.5">
                <!-- Mobile Swipe Hint Banner -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2.5 mb-3 rounded-3 pay-mobile-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-success"></i>
                        <span class="fw-semibold small">Geser tabel pembayaran ke samping</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold pay-swipe-pill">Swipe &rarr;</span>
                </div>

                <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                    <table class="table table-hover align-middle mb-0" id="table_sales_payments" style="min-width: 1080px; width: 100%;">
                        <thead class="bg-body-tertiary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="ps-3 py-3" style="width: 4%;">No</th>
                                <th style="width: 16%;">No. Pembayaran</th>
                                <th style="width: 10%;">Tanggal</th>
                                <th style="width: 18%;">Customer</th>
                                <th style="width: 13%;">Kas / Bank</th>
                                <th style="width: 13%;">Faktur Terkait</th>
                                <th style="width: 12%;" class="text-end">Total Bayar</th>
                                <th style="width: 7%;" class="text-center">Status</th>
                                <th style="width: 5%;" class="text-center">Jurnal</th>
                                <th style="width: 14%; min-width: 165px;" class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-body-emphasis">
                            <!-- Populated by DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="modalFilterPayment" tabindex="-1" aria-labelledby="modalFilterPaymentLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-body-tertiary border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-body-emphasis" id="modalFilterPaymentLabel">
                        <i class="bi bi-funnel text-success me-1"></i> Filter Data Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body-secondary">Dari Tanggal</label>
                            <input type="date" class="form-control" id="filter_start_date">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-body-secondary">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="filter_end_date">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body-secondary">Customer</label>
                            <select class="form-select" id="filter_customer_id">
                                <option value="">-- Semua Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body-secondary">Akun Kas / Bank</label>
                            <select class="form-select" id="filter_kas_bank_id">
                                <option value="">-- Semua Kas & Bank --</option>
                                @foreach($kasBanks as $kb)
                                    <option value="{{ $kb->id }}">{{ $kb->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body-secondary">Status</label>
                            <select class="form-select" id="filter_status">
                                <option value="">-- Semua Status --</option>
                                <option value="Posted">Posted (Lunas)</option>
                                <option value="Diajukan">Menunggu Persetujuan</option>
                                <option value="Draft">Draft</option>
                                <option value="Cancelled">Cancelled (Dibatalkan)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5" id="btn_reset_filter">Reset</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" id="btn_apply_filter" data-bs-dismiss="modal">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Sub-Modals -->
    @include('modul_marketing.pembayaran._modal_create')
    @include('modul_marketing.pembayaran._modal_edit')
    @include('modul_marketing.pembayaran._modal_lookup_invoice')
    @include('modul_marketing.pembayaran._modal_detail')
    @include('modul_marketing.pembayaran._modal_jurnal')
    @include('modul_marketing.pembayaran._modal_preview_lampiran')

    <!-- Scripts -->
    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            const table = $('#table_sales_payments').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[1, 'desc']],
                ajax: {
                    url: "{{ route('modul_marketing.pembayaran.index') }}",
                    data: function(d) {
                        d.customer_id = $('#filter_customer_id').val();
                        d.kas_bank_id = $('#filter_kas_bank_id').val();
                        d.status      = $('#filter_status').val();
                        d.start_date  = $('#filter_start_date').val();
                        d.end_date    = $('#filter_end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-3 text-body-secondary' },
                    { data: 'nomor_pembayaran', name: 'nomor_pembayaran' },
                    { data: 'tanggal_bayar', name: 'tanggal_bayar' },
                    { data: 'customer', name: 'customer' },
                    { data: 'kas_bank', name: 'kas_bank' },
                    { data: 'faktur_terkait', name: 'faktur_terkait', orderable: false, searchable: false },
                    { data: 'total_bayar', name: 'total_bayar', className: 'text-end' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'jurnal', name: 'jurnal', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center pe-3' }
                ],
                drawCallback: function() {
                    if (window.bootstrap && bootstrap.Tooltip) {
                        document.querySelectorAll('#table_sales_payments [data-bs-toggle="tooltip"]').forEach(function(el) {
                            new bootstrap.Tooltip(el);
                        });
                    }
                },
                language: {
                    search: "",
                    searchPlaceholder: "Cari nomor kwitansi, customer...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada data pembayaran yang ditemukan",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    processing: '<div class="spinner-border spinner-border-sm text-success" role="status"></div> Memuat...'
                }
            });

            // Debounce DataTables Search Input
            $('#table_sales_payments_filter input').unbind().bind('input', function(e) {
                var item = $(this);
                clearTimeout(window.searchDebounceTimer);
                window.searchDebounceTimer = setTimeout(function() {
                    table.search(item.val()).draw();
                }, 400);
            });

            // Apply & Reset Filter
            $('#btn_apply_filter').on('click', function() {
                table.draw();
            });

            $('#btn_reset_filter').on('click', function() {
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                if ($.fn.select2) {
                    $('#filter_customer_id').val('').trigger('change.select2');
                    $('#filter_kas_bank_id').val('').trigger('change.select2');
                } else {
                    $('#filter_customer_id').val('');
                    $('#filter_kas_bank_id').val('');
                }
                $('#filter_status').val('');
                table.draw();
                $('#modalFilterPayment').modal('hide');
            });

            $('#btn_refresh_table').on('click', function() {
                table.ajax.reload(null, false);
            });

            // ── Background Polling Real-time Live Sync ────────────────────────
            let lastFingerprint = null;
            let isPollingActive = true;
            let activeDetailPaymentId = null;

            function checkLiveSync() {
                if (!isPollingActive) return;

                // Tunda refresh jika pengguna sedang menginput data di form create atau edit
                if ($('#modalCreatePayment').hasClass('show') || $('#modalEditPayment').hasClass('show')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('modul_marketing.pembayaran.check_sync') }}",
                    method: 'GET',
                    cache: false,
                    success: function(res) {
                        if (res && res.fingerprint) {
                            if (lastFingerprint === null) {
                                lastFingerprint = res.fingerprint;
                            } else if (lastFingerprint !== res.fingerprint) {
                                lastFingerprint = res.fingerprint;
                                table.ajax.reload(null, false);

                                // Jika modal detail pembayaran sedang dilihat, perbarui status dan datanya secara real-time
                                if ($('#modalDetailPayment').hasClass('show') && activeDetailPaymentId) {
                                    openDetailModal(activeDetailPaymentId);
                                }
                            }
                        }
                    }
                });
            }

            const pollInterval = setInterval(checkLiveSync, 2500);

            // ── Select2 Searchable Dropdown Initialization ─────────────────────
            function initPaymentSelect2() {
                if ($.fn.select2) {
                    if (!$('#create_customer_id').hasClass('select2-hidden-accessible')) {
                        $('#create_customer_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalCreatePayment'),
                            placeholder: '-- Pilih Customer / Nama PT --',
                            allowClear: true,
                            width: '100%'
                        });
                    }

                    if (!$('#create_invoice_select').hasClass('select2-hidden-accessible')) {
                        $('#create_invoice_select').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalCreatePayment'),
                            placeholder: '-- Pilih / Cari No. Invoice --',
                            allowClear: true,
                            width: '100%'
                        });
                    }

                    if (!$('#create_kas_bank_id').hasClass('select2-hidden-accessible')) {
                        $('#create_kas_bank_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalCreatePayment'),
                            placeholder: '-- Pilih Rekening Kas/Bank --',
                            allowClear: false,
                            width: '100%'
                        });
                    }

                    if (!$('#create_payment_method_id').hasClass('select2-hidden-accessible')) {
                        $('#create_payment_method_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalCreatePayment'),
                            placeholder: '-- Pilih Metode Pembayaran --',
                            allowClear: true,
                            width: '100%'
                        });
                    }

                    if (!$('#create_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#create_potongan_coa_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalCreatePayment'),
                            placeholder: '-- Cari Kode atau Nama Akun COA --',
                            allowClear: true,
                            width: '100%',
                            dropdownCssClass: 'select2-dropdown-dark-amber'
                        });
                    }
                }
            }

            // Inisialisasi Select2 saat modal tambah pembayaran dibuka
            $('#modalCreatePayment').on('shown.bs.modal', function () {
                initPaymentSelect2();
            });

            // Inisialisasi Select2 saat modal edit pembayaran dibuka
            $('#modalEditPayment').on('shown.bs.modal', function () {
                if ($.fn.select2) {
                    if (!$('#edit_customer_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_customer_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalEditPayment'),
                            placeholder: '-- Pilih Customer / Nama PT --',
                            allowClear: false,
                            width: '100%'
                        });
                    }
                    if (!$('#edit_kas_bank_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_kas_bank_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalEditPayment'),
                            placeholder: '-- Pilih Rekening Kas/Bank --',
                            allowClear: false,
                            width: '100%'
                        });
                    }
                    if (!$('#edit_payment_method_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_payment_method_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalEditPayment'),
                            placeholder: '-- Pilih Metode Pembayaran --',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                    if (!$('#edit_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_potongan_coa_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalEditPayment'),
                            placeholder: '-- Cari Kode atau Nama Akun COA --',
                            allowClear: true,
                            width: '100%',
                            dropdownCssClass: 'select2-dropdown-dark-amber'
                        });
                    }
                }
            });

            // Inisialisasi Select2 saat modal filter dibuka
            $('#modalFilterPayment').on('shown.bs.modal', function () {
                if ($.fn.select2) {
                    if (!$('#filter_customer_id').hasClass('select2-hidden-accessible')) {
                        $('#filter_customer_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalFilterPayment'),
                            placeholder: '-- Semua Customer --',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                    if (!$('#filter_kas_bank_id').hasClass('select2-hidden-accessible')) {
                        $('#filter_kas_bank_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalFilterPayment'),
                            placeholder: '-- Semua Kas & Bank --',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                }
            });

            // Cache data invoice awal dari dropdown HTML
            let allInvoicesCache = [];
            function populateInvoicesCache() {
                allInvoicesCache = [];
                $('#create_invoice_select option').each(function() {
                    const val = $(this).val();
                    if (val) {
                        allInvoicesCache.push({
                            id: val,
                            customer_id: $(this).data('customer-id'),
                            customer_name: $(this).data('customer-name'),
                            nomor: $(this).data('nomor'),
                            sisa: $(this).data('sisa'),
                            text: $(this).text()
                        });
                    }
                });
            }
            populateInvoicesCache();

            // Refresh list invoice di dropdown create_invoice_select
            function refreshInvoiceDropdown(filteredList, selectedVal = '') {
                const select = $('#create_invoice_select');
                select.empty().append('<option value="">-- Pilih / Cari No. Invoice --</option>');
                filteredList.forEach(item => {
                    const isSelected = String(item.id) === String(selectedVal);
                    const opt = new Option(item.text, item.id, false, isSelected);
                    $(opt).attr('data-customer-id', item.customer_id);
                    $(opt).attr('data-customer-name', item.customer_name);
                    $(opt).attr('data-nomor', item.nomor);
                    $(opt).attr('data-sisa', item.sisa);
                    select.append(opt);
                });
                if ($.fn.select2 && select.hasClass('select2-hidden-accessible')) {
                    select.val(selectedVal || '').trigger('change.select2');
                } else {
                    select.val(selectedVal || '');
                }
            }

            // ── Event: Tarik Berdasarkan No. Invoice Dipilih ────────────────────
            $('#create_invoice_select').on('change', function() {
                const invoiceId = $(this).val();
                if (!invoiceId) {
                    $('#btn_clear_invoice_select').addClass('d-none');
                    return;
                }

                $('#btn_clear_invoice_select').removeClass('d-none');
                const selectedOpt = $(this).find('option:selected');
                const customerId = selectedOpt.data('customer-id');
                const customerName = selectedOpt.data('customer-name');

                if (customerId) {
                    // Tampilkan badge indikator otomatis terisi
                    $('#badge_customer_auto').removeClass('d-none').hide().fadeIn(200);

                    // Pilih customer dan load invoice dengan menandai invoice yang ditarik
                    if ($.fn.select2 && $('#create_customer_id').hasClass('select2-hidden-accessible')) {
                        $('#create_customer_id').val(customerId).trigger('change.select2');
                    } else {
                        $('#create_customer_id').val(customerId);
                    }
                    $('#create_customer_id').trigger('change.byInvoice', [invoiceId]);
                }
            });

            // Reset Pilihan Invoice Button
            $('#btn_clear_invoice_select').on('click', function() {
                if ($.fn.select2 && $('#create_invoice_select').hasClass('select2-hidden-accessible')) {
                    $('#create_invoice_select').val('').trigger('change.select2');
                } else {
                    $('#create_invoice_select').val('');
                }
                $(this).addClass('d-none');
                $('#badge_customer_auto').addClass('d-none');
            });

            // ── Dynamic Customer Selection -> Load Invoices ────────────────────
            $('#create_customer_id').on('change change.byInvoice', function(e, autoSelectInvoiceId) {
                const customerId = $(this).val();

                // Jika diubah manual oleh user (bukan dari tarik invoice)
                if (!autoSelectInvoiceId) {
                    $('#badge_customer_auto').addClass('d-none');
                    // Filter dropdown invoice agar hanya faktur customer ini
                    if (customerId) {
                        const filtered = allInvoicesCache.filter(inv => String(inv.customer_id) === String(customerId));
                        refreshInvoiceDropdown(filtered);
                    } else {
                        refreshInvoiceDropdown(allInvoicesCache);
                        $('#btn_clear_invoice_select').addClass('d-none');
                    }
                }

                if (!customerId) {
                    $('#invoices_empty_state').removeClass('d-none');
                    $('#invoices_loading_state').addClass('d-none');
                    $('#invoices_none_state').addClass('d-none');
                    $('#invoices_table_container').addClass('d-none');
                    $('#badge_count_invoices').text('0 Faktur');
                    calculateSummary();
                    return;
                }

                $('#invoices_empty_state').addClass('d-none');
                $('#invoices_none_state').addClass('d-none');
                $('#invoices_table_container').addClass('d-none');
                $('#invoices_loading_state').removeClass('d-none');

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran/unpaid-invoices') }}/" + customerId,
                    method: 'GET',
                    success: function(res) {
                        $('#invoices_loading_state').addClass('d-none');
                        if (res.success && res.data && res.data.length > 0) {
                            $('#badge_count_invoices').text(res.data.length + ' Faktur');
                            renderInvoicesTable(res.data);
                            $('#invoices_table_container').removeClass('d-none');

                            // Jika ditarik dari No. Invoice tertentu, checklist otomatis faktur tsb
                            if (autoSelectInvoiceId) {
                                const targetRow = $(`#unpaid_invoices_tbody tr[data-id="${autoSelectInvoiceId}"]`);
                                if (targetRow.length > 0) {
                                    const chk = targetRow.find('.invoice-checkbox');
                                    chk.prop('checked', true);
                                    targetRow.addClass('table-active bg-primary-subtle bg-opacity-10');

                                    const rem = parseFloat(targetRow.find('.inv-remaining').data('val')) || 0;
                                    const bayarInput = targetRow.find('.inv-bayar-input');
                                    const potonganInput = targetRow.find('.inv-potongan-input');

                                    bayarInput.prop('disabled', false).val(formatRupiahDisplay(rem));
                                    potonganInput.prop('disabled', false).val('0');
                                    targetRow.find('.inv-sisa-akhir').html('<span class="badge bg-success text-white border border-success px-2.5 py-1 fw-bold font-monospace shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>');

                                    targetRow.addClass('table-success');
                                    setTimeout(() => targetRow.removeClass('table-success'), 1500);

                                    calculateSummary();
                                }
                            }
                        } else {
                            $('#badge_count_invoices').text('0 Faktur');
                            $('#invoices_none_state').removeClass('d-none');
                            calculateSummary();
                        }
                    },
                    error: function(xhr) {
                        $('#invoices_loading_state').addClass('d-none');
                        $('#invoices_none_state').removeClass('d-none');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Faktur',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.'
                        });
                    }
                });
            });

            // ── Helper Formatter Rupiah Indonesia (Selalu 2 Desimal) ─────────
            function formatRupiahDisplay(val) {
                if (val === null || val === undefined || val === '') return '0,00';
                let num = typeof val === 'number' ? val : parseFloat(val);
                if (isNaN(num)) return '0,00';
                return num.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function parseRupiahInput(val) {
                if (val === null || val === undefined || val === '') return 0;
                if (typeof val === 'number') return Math.round(val * 100) / 100;
                let s = val.toString().trim();
                // Hapus titik pemisah ribuan, ganti koma menjadi titik desimal
                if (s.indexOf(',') !== -1) {
                    s = s.replace(/\./g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
                } else {
                    const dotCount = (s.match(/\./g) || []).length;
                    if (dotCount > 1) {
                        s = s.replace(/\./g, '');
                    } else if (dotCount === 1) {
                        let parts = s.split('.');
                        if (parts[1].length === 3 && parts[0].length >= 1) {
                            s = parts[0] + parts[1];
                        }
                    }
                    s = s.replace(/[^0-9.-]/g, '');
                }
                let num = parseFloat(s);
                return isNaN(num) ? 0 : (Math.round(num * 100) / 100);
            }

            // Izinkan pengetikan titik pada numpad keyboard agar otomatis menjadi koma desimal
            $(document).on('keydown', '.inv-bayar-input, .inv-potongan-input, .edit-inv-bayar-input, .edit-inv-potongan-input', function(e) {
                if (e.key === '.' || e.key === 'Decimal') {
                    e.preventDefault();
                    let curVal = $(this).val();
                    if (curVal.indexOf(',') === -1) {
                        $(this).val(curVal + ',').trigger('input');
                    }
                }
            });

            function renderInvoicesTable(invoices) {
                let html = '';
                invoices.forEach(function(inv, idx) {
                    const grandTotalFormatted = formatRupiahDisplay(inv.grand_total);
                    const remainingFormatted = formatRupiahDisplay(inv.remaining_balance);

                    html += `
                        <tr class="invoice-row" data-id="${inv.id}" style="transition: all 0.2s ease;">
                            <td class="ps-3 py-3 text-center align-middle">
                                <input type="checkbox" class="form-check-input invoice-checkbox" data-index="${idx}" value="${inv.id}" style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                            </td>
                            <td class="py-3 align-middle">
                                <div class="fw-bold font-monospace text-primary d-flex align-items-center gap-1.5" style="font-size: 0.9rem;">
                                    <i class="bi bi-file-earmark-check text-primary"></i> ${inv.nomor_faktur}
                                </div>
                                <div class="text-body-secondary small mt-0.5 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                    <span><i class="bi bi-calendar3 me-1 text-secondary"></i>${inv.tanggal_faktur}</span>
                                    <span class="text-body-tertiary">|</span>
                                    <span class="text-warning-emphasis fw-medium"><i class="bi bi-clock-history me-1"></i>JT: ${inv.tanggal_jatuh_tempo}</span>
                                </div>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <span class="font-monospace text-body-emphasis fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp ${grandTotalFormatted}</span>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <span class="font-monospace fw-bold text-danger inv-remaining" data-val="${inv.remaining_balance}" style="font-variant-numeric: tabular-nums; font-size: 0.92rem;">Rp ${remainingFormatted}</span>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <div class="input-group input-group-sm ms-auto shadow-sm" style="max-width: 175px;">
                                    <span class="input-group-text bg-body-secondary text-success fw-bold font-monospace px-2" style="font-size: 0.76rem;">Rp</span>
                                    <input type="text" inputmode="numeric" class="form-control form-control-sm text-end font-monospace fw-bold text-success inv-bayar-input" value="0" placeholder="0" style="font-variant-numeric: tabular-nums; font-size: 0.9rem; letter-spacing: 0.3px;" disabled>
                                </div>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <div class="input-group input-group-sm ms-auto shadow-sm" style="max-width: 155px;">
                                    <span class="input-group-text bg-body-secondary text-warning fw-bold font-monospace px-2" style="font-size: 0.76rem;">Rp</span>
                                    <input type="text" inputmode="numeric" class="form-control form-control-sm text-end font-monospace fw-semibold text-warning inv-potongan-input" value="0" placeholder="0" style="font-variant-numeric: tabular-nums; font-size: 0.9rem; letter-spacing: 0.3px;" disabled>
                                </div>
                            </td>
                            <td class="text-end pe-3 py-3 align-middle">
                                <span class="font-monospace fw-bold text-body-emphasis inv-sisa-akhir" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp ${remainingFormatted}</span>
                            </td>
                        </tr>
                    `;
                });
                $('#unpaid_invoices_tbody').html(html);
                $('#check_all_invoices').prop('checked', false);
                calculateSummary();
            }

            // Check All Invoices
            $('#check_all_invoices').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.invoice-checkbox').each(function() {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            // Single Checkbox Change
            $(document).on('change', '.invoice-checkbox', function() {
                const row = $(this).closest('tr');
                const isChecked = $(this).is(':checked');
                const remaining = parseFloat(row.find('.inv-remaining').data('val')) || 0;
                const bayarInput = row.find('.inv-bayar-input');
                const potonganInput = row.find('.inv-potongan-input');

                row.toggleClass('table-active bg-primary-subtle bg-opacity-10', isChecked);

                if (isChecked) {
                    bayarInput.prop('disabled', false).val(formatRupiahDisplay(remaining));
                    potonganInput.prop('disabled', false).val('0');
                } else {
                    bayarInput.prop('disabled', true).val('0');
                    potonganInput.prop('disabled', true).val('0');
                }

                updateRowSisaAkhir(row);
                calculateSummary();
            });

            // Format input bayar & potongan secara real-time saat mengetik
            $(document).on('input', '.inv-bayar-input, .inv-potongan-input', function() {
                const row = $(this).closest('tr');
                const isPotongan = $(this).hasClass('inv-potongan-input');
                let rawVal = $(this).val();

                // Format angka real-time dengan titik pemisah ribuan
                let parts = rawVal.split(',');
                let intDigits = parts[0].replace(/\D/g, '');
                let decDigits = parts.length > 1 ? parts[1].replace(/\D/g, '').slice(0, 2) : null;

                let formattedInt = intDigits ? parseInt(intDigits, 10).toLocaleString('id-ID') : '';
                let formattedVal = decDigits !== null ? (formattedInt + ',' + decDigits) : formattedInt;

                $(this).val(formattedVal || '0');

                // Auto-koreksi nilai agar tidak melebihi sisa piutang dan selaras antara kas vs potongan
                const remaining = parseFloat(row.find('.inv-remaining').data('val')) || 0;
                if (isPotongan) {
                    let potongan = parseRupiahInput(formattedVal);
                    if (potongan > remaining) {
                        potongan = remaining;
                        $(this).val(formatRupiahDisplay(potongan));
                    }
                    const newBayar = Math.max(0, Math.round((remaining - potongan) * 100) / 100);
                    row.find('.inv-bayar-input').val(formatRupiahDisplay(newBayar));
                } else {
                    let bayar = parseRupiahInput(formattedVal);
                    if (bayar > remaining) {
                        bayar = remaining;
                        $(this).val(formatRupiahDisplay(bayar));
                    }
                    const currentPotongan = parseRupiahInput(row.find('.inv-potongan-input').val());
                    if (bayar + currentPotongan > remaining) {
                        const newPotongan = Math.max(0, Math.round((remaining - bayar) * 100) / 100);
                        row.find('.inv-potongan-input').val(formatRupiahDisplay(newPotongan));
                    }
                }

                updateRowSisaAkhir(row);
                calculateSummary();
            });

            $(document).on('focus', '.inv-bayar-input, .inv-potongan-input', function() {
                if ($(this).val() === '0') {
                    $(this).select();
                }
            });

            $(document).on('blur', '.inv-bayar-input, .inv-potongan-input', function() {
                let parsed = parseRupiahInput($(this).val());
                $(this).val(formatRupiahDisplay(parsed));
                const row = $(this).closest('tr');
                const isPotongan = $(this).hasClass('inv-potongan-input');
                const remaining = parseFloat(row.find('.inv-remaining').data('val')) || 0;

                if (isPotongan) {
                    if (parsed > remaining) {
                        parsed = remaining;
                        $(this).val(formatRupiahDisplay(parsed));
                    }
                    const newBayar = Math.max(0, Math.round((remaining - parsed) * 100) / 100);
                    row.find('.inv-bayar-input').val(formatRupiahDisplay(newBayar));
                } else {
                    if (parsed > remaining) {
                        parsed = remaining;
                        $(this).val(formatRupiahDisplay(parsed));
                    }
                    const currentPotongan = parseRupiahInput(row.find('.inv-potongan-input').val());
                    if (parsed + currentPotongan > remaining) {
                        const newPotongan = Math.max(0, Math.round((remaining - parsed) * 100) / 100);
                        row.find('.inv-potongan-input').val(formatRupiahDisplay(newPotongan));
                    }
                }

                updateRowSisaAkhir(row);
                calculateSummary();
            });

            function updateRowSisaAkhir(row) {
                const remaining = parseFloat(row.find('.inv-remaining').data('val')) || 0;
                const bayar = parseRupiahInput(row.find('.inv-bayar-input').val());
                const potongan = parseRupiahInput(row.find('.inv-potongan-input').val());
                const totalPaid = Math.round((bayar + potongan) * 100) / 100;
                const sisaAkhir = Math.max(0, Math.round((remaining - totalPaid) * 100) / 100);
                const lebih = Math.max(0, Math.round((totalPaid - remaining) * 100) / 100);

                if (sisaAkhir <= 0.009 && totalPaid > 0) {
                    let html = '<span class="badge bg-success text-white border border-success px-2.5 py-1 fw-bold font-monospace shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>';
                    if (lebih > 0.009) {
                        html += `<div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5" style="font-size: 0.72rem;"><i class="bi bi-arrow-up-right-circle me-1"></i>Lebih: Rp ${formatRupiahDisplay(lebih)}</span></div>`;
                    }
                    row.find('.inv-sisa-akhir').html(html);
                } else {
                    row.find('.inv-sisa-akhir').html(`<span class="font-monospace fw-bold ${sisaAkhir > 0 ? 'text-danger' : 'text-body-emphasis'}" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp ${formatRupiahDisplay(sisaAkhir)}</span>`);
                }
            }

            function calculateSummary() {
                let selectedCount = 0;
                let totalBayar = 0;
                let totalPotongan = 0;
                let totalLebih = 0;

                $('.invoice-checkbox:checked').each(function() {
                    selectedCount++;
                    const row = $(this).closest('tr');
                    const remaining = parseFloat(row.find('.inv-remaining').data('val')) || 0;
                    const bayar = parseRupiahInput(row.find('.inv-bayar-input').val());
                    const potongan = parseRupiahInput(row.find('.inv-potongan-input').val());
                    totalBayar += bayar;
                    totalPotongan += potongan;
                    const diff = (bayar + potongan) - remaining;
                    if (diff > 0.009) {
                        totalLebih += diff;
                    }
                });

                totalBayar = Math.round(totalBayar * 100) / 100;
                totalPotongan = Math.round(totalPotongan * 100) / 100;
                totalLebih = Math.round(totalLebih * 100) / 100;

                $('#sum_selected_count').text(selectedCount);
                $('#sum_total_potongan').text('Rp ' + formatRupiahDisplay(totalPotongan));
                $('#sum_total_bayar').text('Rp ' + formatRupiahDisplay(totalBayar));

                if (totalLebih > 0.009) {
                    $('#sum_lebih_bayar_container').removeClass('d-none');
                    $('#sum_total_lebih_bayar').text('Rp ' + formatRupiahDisplay(totalLebih));
                } else {
                    $('#sum_lebih_bayar_container').addClass('d-none');
                }

                // Tampilkan atau sembunyikan dropdown akun COA potongan secara dinamis
                if (totalPotongan > 0.009) {
                    $('#container_potongan_coa').removeClass('d-none');
                    if ($.fn.select2) {
                        if (!$('#create_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                            $('#create_potongan_coa_id').select2({
                                theme: 'bootstrap-5',
                                dropdownParent: $('#modalCreatePayment'),
                                placeholder: '-- Cari Kode atau Nama Akun COA --',
                                allowClear: true,
                                width: '100%',
                                dropdownCssClass: 'select2-dropdown-dark-amber'
                            });
                        }
                    }
                } else {
                    $('#container_potongan_coa').addClass('d-none');
                }
            }

            // ── Save Payment (Draft / Diajukan) ─────────────────────────────
            $('#btn_save_draft').on('click', function() {
                submitPaymentForm('Draft');
            });

            $('#btn_save_submit').on('click', function() {
                submitPaymentForm('Diajukan');
            });

            function submitPaymentForm(targetStatus) {
                const customerId = $('#create_customer_id').val();
                const tanggalBayar = $('#create_tanggal_bayar').val();
                const kasBankId = $('#create_kas_bank_id').val();

                if (!customerId) {
                    Swal.fire('Validasi', 'Silakan pilih customer terlebih dahulu.', 'warning');
                    return;
                }
                if (!tanggalBayar) {
                    Swal.fire('Validasi', 'Silakan isi tanggal pembayaran.', 'warning');
                    return;
                }
                if (!kasBankId) {
                    Swal.fire('Validasi', 'Silakan pilih Kas / Bank tujuan.', 'warning');
                    return;
                }

                const lampiranFile = $('#create_lampiran')[0]?.files[0];
                if (!lampiranFile) {
                    Swal.fire('Validasi', 'Dokumen atau foto bukti transfer pembayaran wajib disertakan.', 'warning');
                    $('#create_lampiran').focus();
                    return;
                }

                const selectedInvoices = [];
                let totalBayar = 0;

                $('.invoice-checkbox:checked').each(function() {
                    const row = $(this).closest('tr');
                    const invId = $(this).val();
                    const bayar = parseRupiahInput(row.find('.inv-bayar-input').val());
                    const potongan = parseRupiahInput(row.find('.inv-potongan-input').val());

                    if ((bayar + potongan) > 0) {
                        selectedInvoices.push({
                            id: invId,
                            bayar: bayar,
                            potongan: potongan,
                            catatan: ''
                        });
                        totalBayar += bayar;
                    }
                });

                if (selectedInvoices.length === 0) {
                    Swal.fire('Validasi', 'Pilih minimal satu faktur dengan nominal alokasi bayar atau potongan lebih besar dari 0.', 'warning');
                    return;
                }

                let confirmTitle = 'Simpan Draft Pembayaran?';
                let confirmText = 'Data akan disimpan sebagai Draft dan dapat diperiksa kembali.';
                let confirmBtnText = '<i class="bi bi-save"></i> Simpan Draft';
                let confirmBtnColor = '#2563eb';

                if (targetStatus === 'Posted') {
                    confirmTitle = 'Posting Penerimaan Pembayaran?';
                    confirmText = `Penerimaan sebesar Rp ${formatRupiahDisplay(totalBayar)} akan dicatat, memotong piutang, dan otomatis membuat jurnal akuntansi.`;
                    confirmBtnText = '<i class="bi bi-check2-circle"></i> Ya, Posting Sekarang';
                    confirmBtnColor = '#059669';
                } else if (targetStatus === 'Diajukan') {
                    confirmTitle = 'Ajukan Pembayaran ke Atasan?';
                    confirmText = `Data akan disimpan dan diajukan ke Atasan untuk persetujuan (ACC). Notifikasi WhatsApp akan otomatis dikirimkan ke Atasan.`;
                    confirmBtnText = '<i class="bi bi-send"></i> Ya, Simpan & Ajukan';
                    confirmBtnColor = '#0284c7';
                }

                Swal.fire({
                    title: confirmTitle,
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'Batal',
                    confirmButtonColor: confirmBtnColor
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData($('#formCreatePayment')[0]);
                        formData.append('status', targetStatus);
                        formData.append('total_bayar', totalBayar);

                        selectedInvoices.forEach(function(item, index) {
                            formData.append(`invoices[${index}][id]`, item.id);
                            formData.append(`invoices[${index}][bayar]`, item.bayar);
                            formData.append(`invoices[${index}][potongan]`, item.potongan);
                        });

                        let submitBtn = targetStatus === 'Diajukan' ? $('#btn_save_submit') : $('#btn_save_draft');
                        const originalBtnHtml = submitBtn.html();
                        $('#btn_save_draft, #btn_save_submit').prop('disabled', true);
                        submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                        $.ajax({
                            url: "{{ route('modul_marketing.pembayaran.store') }}",
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                $('#btn_save_draft, #btn_save_submit').prop('disabled', false);
                                submitBtn.html(originalBtnHtml);
                                if (res.success) {
                                    $('#modalCreatePayment').modal('hide');
                                    $('#formCreatePayment')[0].reset();
                                    if ($.fn.select2) {
                                        $('#create_invoice_select').val('').trigger('change.select2');
                                        $('#create_customer_id').val('').trigger('change.select2');
                                        $('#create_kas_bank_id').val('').trigger('change.select2');
                                        $('#create_payment_method_id').val('').trigger('change.select2');
                                    } else {
                                        $('#create_invoice_select').val('');
                                        $('#create_customer_id').val('');
                                        $('#create_kas_bank_id').val('');
                                        $('#create_payment_method_id').val('');
                                    }
                                    $('#badge_customer_auto').addClass('d-none');
                                    $('#btn_clear_invoice_select').addClass('d-none');
                                    table.ajax.reload(null, false);

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: res.message,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Lihat Detail / Cetak Kwitansi',
                                        showCancelButton: true,
                                        cancelButtonText: 'Selesai'
                                    }).then((subRes) => {
                                        if (subRes.isConfirmed && res.data) {
                                            openDetailModal(res.data.id);
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                $('#btn_save_draft, #btn_save_submit').prop('disabled', false);
                                submitBtn.html(originalBtnHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menyimpan',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            }

            // ── Detail Modal ──────────────────────────────────────────────────
            $(document).on('click', '.btn-detail-payment', function() {
                const id = $(this).data('id');
                openDetailModal(id);
            });

            function openDetailModal(id) {
                activeDetailPaymentId = id;
                $('#modalDetailPayment').modal('show');
                $('#detail_payment_loading').removeClass('d-none');
                $('#detail_payment_content').addClass('d-none');

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran') }}/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#detail_payment_loading').addClass('d-none');
                        if (res.success && res.data) {
                            const p = res.data;
                            $('#detail_nomor_pembayaran').text(p.nomor_pembayaran);
                            
                            // Format Tanggal Bayar (Rapi tanpa format ISO mentah)
                            let tglBayarText = p.tanggal_bayar_formatted;
                            if (!tglBayarText && p.tanggal_bayar) {
                                const rawDate = p.tanggal_bayar.includes('T') ? p.tanggal_bayar.split('T')[0] : p.tanggal_bayar;
                                const parts = rawDate.split('-');
                                if (parts.length === 3) {
                                    tglBayarText = parts[2] + '/' + parts[1] + '/' + parts[0];
                                } else {
                                    tglBayarText = rawDate;
                                }
                            }
                            $('#detail_tanggal_bayar').text('Tanggal Bayar: ' + (tglBayarText || '-'));
                            const creatorName = p.creator?.nama_lengkap || p.creator?.username || p.creator?.name || '-';
                            $('#detail_creator').text(creatorName);

                            // ── Audit Trail (Pembuat, Pengedit, Approver) ────────────────
                            // 1. Dibuat Oleh
                            $('#detail_audit_creator').text(creatorName);
                            $('#detail_audit_created_at').text(p.created_at_formatted || '-');

                            // 2. Terakhir Diedit Oleh (Hanya tampil jika ada riwayat edit aktual)
                            const updaterName = p.updater?.nama_lengkap || p.updater?.username || p.updater?.name;
                            const hasActualEdit = Boolean(p.is_edited && updaterName);

                            if (hasActualEdit) {
                                $('#col_audit_updater').removeClass('d-none');
                                $('#col_audit_creator').attr('class', 'col-md-4 border-end');
                                $('#col_audit_updater').attr('class', 'col-md-4 border-end');
                                $('#col_audit_approver').attr('class', 'col-md-4');

                                $('#detail_audit_updater').text(updaterName);
                                $('#detail_audit_updated_at').text(p.updated_at_formatted || '-');
                            } else {
                                // Sembunyikan kolom edit jika memang tidak pernah ada yang mengedit
                                $('#col_audit_updater').addClass('d-none');
                                $('#col_audit_creator').attr('class', 'col-md-6 border-end');
                                $('#col_audit_approver').attr('class', 'col-md-6');
                            }

                            // 3. Disetujui / Di-ACC Oleh
                            if (p.status === 'Posted') {
                                const posterName = p.poster?.nama_lengkap || p.poster?.username || p.poster?.name || 'Authorized User';
                                $('#detail_audit_approver_title').text('Disetujui / Diposting (ACC)');
                                $('#detail_audit_approver').text(posterName).removeClass('text-danger text-warning text-secondary').addClass('text-body-emphasis');
                                $('#detail_audit_approved_at').text(p.posted_at_formatted || '-');
                                $('#detail_audit_approver_icon_wrapper').attr('class', 'p-2 rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0');
                                $('#detail_audit_approver_icon').attr('class', 'bi bi-check-circle-fill fs-6');
                            } else if (p.status === 'Cancelled') {
                                const cancellerName = p.canceller?.nama_lengkap || p.canceller?.username || p.canceller?.name || 'Authorized User';
                                $('#detail_audit_approver_title').text('Dibatalkan Oleh');
                                $('#detail_audit_approver').text(cancellerName).removeClass('text-success text-warning text-secondary').addClass('text-danger');
                                $('#detail_audit_approved_at').text(p.cancelled_at_formatted || '-');
                                $('#detail_audit_approver_icon_wrapper').attr('class', 'p-2 rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0');
                                $('#detail_audit_approver_icon').attr('class', 'bi bi-x-circle-fill fs-6');
                            } else if (p.status === 'Diajukan') {
                                $('#detail_audit_approver_title').text('Status Persetujuan');
                                $('#detail_audit_approver').text('Menunggu Persetujuan Atasan').removeClass('text-danger text-body-emphasis text-secondary').addClass('text-warning');
                                $('#detail_audit_approved_at').text('Diajukan ke Atasan');
                                $('#detail_audit_approver_icon_wrapper').attr('class', 'p-2 rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0');
                                $('#detail_audit_approver_icon').attr('class', 'bi bi-hourglass-split fs-6');
                            } else {
                                $('#detail_audit_approver_title').text('Status Persetujuan');
                                $('#detail_audit_approver').text('Draft (Belum Diajukan)').removeClass('text-danger text-body-emphasis text-warning').addClass('text-secondary');
                                $('#detail_audit_approved_at').text('-');
                                $('#detail_audit_approver_icon_wrapper').attr('class', 'p-2 rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0');
                                $('#detail_audit_approver_icon').attr('class', 'bi bi-pencil-square fs-6');
                            }
                            
                            // Status badge
                            let badge = '';
                            if (p.status === 'Posted') {
                                badge = '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-check2-circle me-1"></i> Posted</span>';
                            } else if (p.status === 'Diajukan') {
                                badge = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan</span>';
                            } else if (p.status === 'Draft') {
                                badge = '<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-pencil-square me-1"></i> Draft</span>';
                            } else {
                                badge = '<span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-x-circle me-1"></i> Batal</span>';
                            }
                            $('#detail_status_badge').html(badge);

                            // Customer
                            let instansi = (p.customer?.nama_perusahaan || '').trim();
                            let person = (p.customer?.nama_customer || '').trim();

                            if (instansi && person && instansi.toLowerCase() !== person.toLowerCase()) {
                                $('#detail_customer_nama').text(instansi);
                                $('#detail_customer_kode').html('<i class="bi bi-person me-1"></i>' + person);
                            } else {
                                $('#detail_customer_nama').text(instansi || person || '-');
                                $('#detail_customer_kode').text('');
                            }

                            // Kas/Bank
                            $('#detail_kas_bank').text(p.kas_bank?.nama_akun || '-');
                            $('#detail_rekening').text(p.kas_bank?.nomor_rekening ? 'No. Rek: ' + p.kas_bank.nomor_rekening : 'Kas Tunai');
                            $('#detail_metode_bayar').text(p.payment_method?.nama_metode || 'Transfer Bank');

                            // Referensi & Lampiran
                            $('#detail_nomor_referensi').text(p.nomor_referensi || '-');
                            if (p.lampiran_url) {
                                $('#detail_lampiran_container').html(`
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-xs btn-open-preview-bukti" data-url="${p.lampiran_url}" data-nomor="${p.nomor_pembayaran}">
                                            <i class="bi bi-eye-fill me-1"></i> Lihat Bukti Transfer
                                        </button>
                                        <a href="${p.lampiran_url}" target="_blank" download class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs">
                                            <i class="bi bi-download me-1"></i> Unduh
                                        </a>
                                    </div>
                                `);
                            } else {
                                $('#detail_lampiran_container').html('<span class="text-body-secondary small fst-italic">Tidak ada lampiran</span>');
                            }

                            // Keterangan
                            $('#detail_keterangan').text(p.keterangan || '-');

                            // Action buttons in detail with permission checks
                            const userCanEdit = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-sales-payment')) ? 'true' : 'false' }};
                            const userCanSubmit = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-sales-payment') || auth()->user()->can('edit-sales-payment')) ? 'true' : 'false' }};
                            const userCanApprove = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner', 'Manager Finance']) || auth()->user()->can('approve-sales-payment')) ? 'true' : 'false' }};
                            const userCanViewJurnal = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-jurnal-sales-payment')) ? 'true' : 'false' }};
                            const userCanPrint = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('cetak-sales-payment')) ? 'true' : 'false' }};
                            const userCanCancel = {{ (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('batalkan-sales-payment')) ? 'true' : 'false' }};

                            if (userCanPrint) {
                                $('#btn_detail_print_kwitansi').removeClass('d-none').attr('href', "{{ url('modul-marketing/pembayaran') }}/" + p.id + "/print-kwitansi");
                            } else {
                                $('#btn_detail_print_kwitansi').addClass('d-none');
                            }
                            
                            // Reset visibility
                            $('#btn_detail_submit_payment, #btn_footer_submit_payment').addClass('d-none');
                            $('#btn_detail_edit_payment').addClass('d-none');
                            $('#btn_detail_post_payment, #btn_footer_post_payment').addClass('d-none');
                            $('#btn_detail_view_jurnal').addClass('d-none');
                            $('#btn_detail_cancel_payment, #btn_footer_cancel_payment').addClass('d-none');

                            if (p.status === 'Draft') {
                                // Pada Draft: bisa Edit jika memiliki izin edit
                                if (userCanEdit) {
                                    $('#btn_detail_edit_payment')
                                        .removeClass('d-none')
                                        .data('id', p.id);
                                }
                                if (userCanSubmit) {
                                    $('#btn_detail_submit_payment, #btn_footer_submit_payment')
                                        .removeClass('d-none')
                                        .data('id', p.id)
                                        .data('nomor', p.nomor_pembayaran);
                                }
                            } else if (p.status === 'Diajukan') {
                                // Pada Diajukan (Menunggu): Edit jika berhak, Posting jika approver
                                if (userCanEdit) {
                                    $('#btn_detail_edit_payment')
                                        .removeClass('d-none')
                                        .data('id', p.id);
                                }
                                if (userCanApprove) {
                                    $('#btn_detail_post_payment, #btn_footer_post_payment')
                                        .removeClass('d-none')
                                        .data('id', p.id)
                                        .data('nomor', p.nomor_pembayaran);
                                }
                            } else if (p.status === 'Posted') {
                                // Pada Posted: jurnal aktif jika berhak
                                if (userCanViewJurnal) {
                                    $('#btn_detail_view_jurnal')
                                        .removeClass('d-none')
                                        .data('id', p.id);
                                }
                                // Batalkan Pembayaran (Void) - tersembunyi di dalam modal detail
                                if (userCanCancel) {
                                    $('#btn_detail_cancel_payment, #btn_footer_cancel_payment')
                                        .removeClass('d-none')
                                        .data('id', p.id)
                                        .data('nomor', p.nomor_pembayaran);
                                }
                            }

                            // Details Table
                            let rowsHtml = '';
                            if (p.details && p.details.length > 0) {
                                p.details.forEach(function(item, idx) {
                                    let lebihRowHtml = '';
                                    let itemLebih = parseFloat(item.lebih_bayar) || 0;
                                    if (itemLebih > 0) {
                                        lebihRowHtml = `<div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-1.5 py-0.5" style="font-size: 0.7rem;"><i class="bi bi-arrow-up-right-circle me-1"></i>Lebih: Rp ${formatRupiahDisplay(itemLebih)}</span></div>`;
                                    }

                                    let tenderHtml = '';
                                    let t = item.invoice?.tender || item.invoice?.sales_order?.tender || item.invoice?.delivery_order?.sales_order?.tender;
                                    if (t) {
                                        let tLabel = (t.nomor_kontrak && t.nomor_kontrak !== '-')
                                            ? (t.nomor_kontrak + (t.nama_tender ? ' - ' + t.nama_tender : ''))
                                            : (t.nama_tender || ('Tender #' + t.id));
                                        tenderHtml = `<div class="mt-1"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-briefcase me-1"></i>${$('<div>').text(tLabel).html()}</span></div>`;
                                    }

                                    rowsHtml += `
                                        <tr>
                                            <td class="ps-3 py-2 text-body-secondary">${idx + 1}</td>
                                            <td>
                                                <span class="fw-bold font-monospace text-primary">${item.invoice?.nomor_faktur || '-'}</span>
                                                ${tenderHtml}
                                            </td>
                                            <td class="text-end font-monospace">Rp ${formatRupiahDisplay(item.total_faktur)}</td>
                                            <td class="text-end font-monospace text-danger">Rp ${formatRupiahDisplay(item.sisa_piutang)}</td>
                                            <td class="text-end font-monospace">
                                                <div class="fw-bold text-success">Rp ${formatRupiahDisplay(item.jumlah_bayar)}</div>
                                                ${lebihRowHtml}
                                            </td>
                                            <td class="text-end pe-3 font-monospace">Rp ${formatRupiahDisplay(item.sisa_akhir)}</td>
                                        </tr>
                                    `;
                                });
                            }
                            $('#detail_invoices_tbody').html(rowsHtml);
                            
                            let totalLebihPayment = parseFloat(p.lebih_bayar) || 0;
                            if (totalLebihPayment > 0) {
                                $('#detail_total_bayar').html(`
                                    <div class="fw-bold text-success fs-6">Rp ${formatRupiahDisplay(p.total_bayar)}</div>
                                    <div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5" style="font-size: 0.72rem; font-weight: normal;"><i class="bi bi-arrow-up-right-circle me-1"></i>Lebih: Rp ${formatRupiahDisplay(totalLebihPayment)}</span></div>
                                `);
                            } else {
                                $('#detail_total_bayar').text('Rp ' + formatRupiahDisplay(p.total_bayar));
                            }
                            let coaPotonganText = p.potongan_coa ? ` [${p.potongan_coa.code} &bull; ${p.potongan_coa.name}]` : '';
                            $('#detail_total_potongan_text').html(p.total_potongan > 0 ? `<div class="text-danger font-monospace"><small>Potongan: Rp ${formatRupiahDisplay(p.total_potongan)}</small><small class="d-block text-body-secondary" style="font-size: 0.72rem;">${coaPotonganText}</small></div>` : '-');

                            $('#detail_payment_content').removeClass('d-none');
                        }
                    },
                    error: function() {
                        $('#detail_payment_loading').addClass('d-none');
                        Swal.fire('Error', 'Gagal memuat rincian pembayaran.', 'error');
                    }
                });
            }

            // ── View Jurnal Modal ──────────────────────────────────────────────
            $(document).on('click', '.btn-view-jurnal, #btn_detail_view_jurnal', function() {
                const id = $(this).data('id');
                $('#modalJurnalPayment').modal('show');
                $('#jurnal_payment_loading').removeClass('d-none');
                $('#jurnal_payment_content').addClass('d-none');
                $('#btn_print_jurnal_payment').addClass('d-none');

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran') }}/" + id + "/jurnal",
                    method: 'GET',
                    success: function(res) {
                        $('#jurnal_payment_loading').addClass('d-none');
                        if (res.success && res.data) {
                            const j = res.data;
                            $('#jp_nomor_jurnal').text(j.nomor_jurnal);
                            $('#jp_tanggal_jurnal').text(j.tanggal_jurnal);
                            $('#jp_referensi').text(j.referensi_tipe);
                            $('#jp_creator').text(j.creator?.nama_lengkap || j.creator?.username || '-');
                            $('#jp_keterangan').text(j.keterangan || '-');

                            if (j.no_tender) {
                                $('#jp_tender').html('<i class="bi bi-briefcase me-1"></i> ' + $('<div>').text(j.no_tender).html());
                                $('#jp_tender_wrapper').removeClass('d-none');
                            } else {
                                $('#jp_tender').text('-');
                                $('#jp_tender_wrapper').addClass('d-none');
                            }

                            let rows = '';
                            let totalDebit = 0;
                            let totalKredit = 0;

                            if (j.details && j.details.length > 0) {
                                j.details.forEach(function(d) {
                                    const debit = parseFloat(d.debit) || 0;
                                    const kredit = parseFloat(d.kredit) || 0;
                                    totalDebit += debit;
                                    totalKredit += kredit;

                                    rows += `
                                        <tr>
                                            <td class="ps-3 py-2.5 text-nowrap">
                                                <span class="pay-coa-badge font-monospace px-2 py-1">${d.account?.code || '-'}</span>
                                            </td>
                                            <td class="py-2.5">
                                                <div class="fw-semibold text-body-emphasis">${d.account?.name || '-'}</div>
                                                ${d.keterangan ? `<small class="text-body-secondary d-block mt-0.5" style="font-size: 0.75rem;">${d.keterangan}</small>` : ''}
                                            </td>
                                            <td class="text-end font-monospace text-success text-nowrap py-2.5">${debit > 0 ? 'Rp ' + formatRupiahDisplay(debit) : '-'}</td>
                                            <td class="text-end pe-3 font-monospace text-primary text-nowrap py-2.5">${kredit > 0 ? 'Rp ' + formatRupiahDisplay(kredit) : '-'}</td>
                                        </tr>
                                    `;
                                });
                            }

                            $('#jp_details_table_body').html(rows);
                            $('#jp_total_debit').text('Rp ' + formatRupiahDisplay(totalDebit));
                            $('#jp_total_kredit').text('Rp ' + formatRupiahDisplay(totalKredit));

                            if (Math.abs(totalDebit - totalKredit) < 0.01) {
                                $('#jp_balance_row').addClass('bg-success-subtle text-success');
                                $('#jp_balance_status').html('<i class="bi bi-check-circle-fill me-1"></i> <strong>BALANCED / SEIMBANG</strong> (Debit = Kredit)');
                            } else {
                                $('#jp_balance_row').addClass('bg-danger-subtle text-danger');
                                $('#jp_balance_status').html('<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>TIDAK SEIMBANG</strong>');
                            }

                            $('#btn_print_jurnal_payment').attr('href', "{{ url('accounting/jurnal-umum') }}/" + j.id + "/print").removeClass('d-none');
                            $('#jurnal_payment_content').removeClass('d-none');
                        }
                    },
                    error: function(xhr) {
                        $('#jurnal_payment_loading').addClass('d-none');
                        Swal.fire({
                            icon: 'info',
                            title: 'Jurnal Akuntansi',
                            text: xhr.responseJSON?.message || 'Jurnal akuntansi belum dibuat atau transaksi belum diposting.'
                        });
                        $('#modalJurnalPayment').modal('hide');
                    }
                });
            });

            // ── Ajukan Pembayaran (Draft -> Diajukan / Menunggu Persetujuan) ───────
            $(document).on('click', '.btn-submit-payment, #btn_detail_submit_payment, #btn_footer_submit_payment', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor') || 'pembayaran ini';

                Swal.fire({
                    title: 'Ajukan Pembayaran?',
                    html: `Apakah Anda yakin ingin mengajukan <strong>${nomor}</strong> ke atasan?<br><small class="text-body-secondary">Status akan berubah menjadi <strong>Menunggu Persetujuan</strong> dan <strong>Notifikasi WhatsApp</strong> akan dikirimkan ke atasan untuk verifikasi.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan Sekarang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mengajukan Pembayaran...',
                            html: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Mengirim notifikasi WhatsApp ke atasan...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/pembayaran') }}/" + id + "/submit",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Diajukan!',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    $('#modalDetailPayment').modal('hide');
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal Mengajukan', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // ── Konfirmasi & Posting Pembayaran (Draft / Diajukan -> Posted) ───────
            $(document).on('click', '.btn-post-payment, #btn_detail_post_payment, #btn_footer_post_payment', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor') || 'pembayaran ini';

                Swal.fire({
                    title: 'Konfirmasi Posting Pembayaran?',
                    html: `Apakah Anda yakin ingin memposting <strong>${nomor}</strong>?<br><small class="text-body-secondary">Status pembayaran akan menjadi <strong>Posted</strong>, Jurnal Otomatis Akuntansi akan diterbitkan, dan <strong>Notifikasi WhatsApp</strong> akan dikirimkan.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Posting Sekarang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#16a34a'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Posting...',
                            html: '<div class="spinner-border spinner-border-sm text-success me-2"></div> Menerbitkan jurnal & mengirim WhatsApp...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/pembayaran') }}/" + id + "/post",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Diposting!',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    $('#modalDetailPayment').modal('hide');
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal Posting', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // ── Cancel / Delete Payment ────────────────────────────────────────
            $(document).on('click', '.btn-cancel-payment, .btn-delete-payment, #btn_detail_cancel_payment, #btn_footer_cancel_payment', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor') || 'pembayaran ini';

                Swal.fire({
                    title: 'Batalkan Pembayaran?',
                    html: `Apakah Anda yakin ingin membatalkan pembayaran <strong>${nomor}</strong>?<br><small class="text-danger">Sisa piutang faktur terkait akan dikembalikan dan jurnal akuntansi akan disesuaikan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('modul-marketing/pembayaran') }}/" + id,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Dibatalkan!', res.message, 'success');
                                    $('#modalDetailPayment').modal('hide');
                                    table.ajax.reload(null, false);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // ── Edit Payment Modal (Khusus Draft) ──────────────────────────────
            $(document).on('click', '.btn-edit-payment, #btn_detail_edit_payment', function() {
                const id = $(this).data('id');
                $('#modalDetailPayment').modal('hide');
                $('#modalEditPayment').modal('show');

                // Reset state
                $('#formEditPayment')[0].reset();
                $('#edit_payment_id').val(id);
                $('#edit_lampiran_preview').addClass('d-none');
                $('#edit_invoices_loading_state').removeClass('d-none');
                $('#edit_invoices_table_container').addClass('d-none');
                $('#edit_unpaid_invoices_tbody').empty();
                $('#btn_update_draft, #btn_update_submit').prop('disabled', true);

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran') }}/" + id + "/edit",
                    method: 'GET',
                    success: function(res) {
                        $('#edit_invoices_loading_state').addClass('d-none');
                        $('#btn_update_draft, #btn_update_submit').prop('disabled', false);

                        if (res.success && res.payment) {
                            const p = res.payment;
                            $('#edit_nomor_pembayaran').val(p.nomor_pembayaran);
                            $('#edit_nomor_badge').text(p.nomor_pembayaran);
                            $('#edit_tanggal_bayar').val(p.tanggal_bayar ? p.tanggal_bayar.substring(0, 10) : '');
                            $('#edit_customer_id').val(p.customer_id).trigger('change.select2');
                            $('#edit_kas_bank_id').val(p.kas_bank_id).trigger('change.select2');
                            $('#edit_payment_method_id').val(p.payment_method_id || '').trigger('change.select2');
                            $('#edit_nomor_referensi').val(p.nomor_referensi || '');
                            $('#edit_keterangan').val(p.keterangan || '');
                            if ($.fn.select2 && $('#edit_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                                $('#edit_potongan_coa_id').val(p.potongan_coa_id || '').trigger('change.select2');
                            } else {
                                $('#edit_potongan_coa_id').val(p.potongan_coa_id || '');
                            }

                            if (p.lampiran_url) {
                                $('#btn_preview_edit_lampiran')
                                    .removeClass('d-none')
                                    .attr('data-url', p.lampiran_url)
                                    .removeAttr('data-filename')
                                    .html('<i class="bi bi-eye-fill me-1"></i> Lihat Bukti');
                            } else {
                                $('#btn_preview_edit_lampiran').addClass('d-none').attr('data-url', '');
                            }

                            if (res.invoices && res.invoices.length > 0) {
                                $('#edit_badge_count_invoices').text(res.invoices.length + ' Faktur');
                                renderEditInvoicesTable(res.invoices);
                                $('#edit_invoices_table_container').removeClass('d-none');
                            } else {
                                $('#edit_badge_count_invoices').text('0 Faktur');
                                $('#edit_unpaid_invoices_tbody').html('<tr><td colspan="7" class="text-center py-4 text-body-secondary">Tidak ada faktur piutang untuk customer ini.</td></tr>');
                                $('#edit_invoices_table_container').removeClass('d-none');
                                editCalculateSummary();
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#edit_invoices_loading_state').addClass('d-none');
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat data pembayaran.', 'error');
                        $('#modalEditPayment').modal('hide');
                    }
                });
            });

            // Dynamic customer change on edit modal
            $('#edit_customer_id').on('change', function() {
                const customerId = $(this).val();
                if (!customerId) {
                    $('#edit_badge_count_invoices').text('0 Faktur');
                    $('#edit_unpaid_invoices_tbody').empty();
                    editCalculateSummary();
                    return;
                }
                $('#edit_invoices_loading_state').removeClass('d-none');
                $('#edit_invoices_table_container').addClass('d-none');

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran/unpaid-invoices') }}/" + customerId,
                    method: 'GET',
                    success: function(res) {
                        $('#edit_invoices_loading_state').addClass('d-none');
                        if (res.success && res.data && res.data.length > 0) {
                            $('#edit_badge_count_invoices').text(res.data.length + ' Faktur');
                            const mapped = res.data.map(function(item) {
                                return {
                                    id: item.id,
                                    nomor_faktur: item.nomor_faktur,
                                    tanggal_faktur: item.tanggal_faktur,
                                    tanggal_jatuh_tempo: item.tanggal_jatuh_tempo,
                                    grand_total: item.grand_total,
                                    available_balance: item.remaining_balance,
                                    is_selected: false,
                                    current_bayar: 0,
                                    current_potongan: 0,
                                    current_catatan: '',
                                    current_sisa_akhir: item.remaining_balance
                                };
                            });
                            renderEditInvoicesTable(mapped);
                            $('#edit_invoices_table_container').removeClass('d-none');
                        } else {
                            $('#edit_badge_count_invoices').text('0 Faktur');
                            $('#edit_unpaid_invoices_tbody').html('<tr><td colspan="7" class="text-center py-4 text-body-secondary">Tidak ada faktur piutang untuk customer ini.</td></tr>');
                            $('#edit_invoices_table_container').removeClass('d-none');
                            editCalculateSummary();
                        }
                    },
                    error: function() {
                        $('#edit_invoices_loading_state').addClass('d-none');
                        $('#edit_invoices_table_container').removeClass('d-none');
                    }
                });
            });

            function renderEditInvoicesTable(invoices) {
                let html = '';
                invoices.forEach(function(inv, idx) {
                    const grandTotalFormatted = formatRupiahDisplay(inv.grand_total);
                    const availableFormatted = formatRupiahDisplay(inv.available_balance);
                    const bayarFormatted = formatRupiahDisplay(inv.current_bayar);
                    const potonganFormatted = formatRupiahDisplay(inv.current_potongan);
                    const isChecked = inv.is_selected;

                    html += `
                        <tr class="edit-invoice-row ${isChecked ? 'table-active bg-primary-subtle bg-opacity-10' : ''}" data-id="${inv.id}" style="transition: all 0.2s ease;">
                            <td class="ps-3 py-3 text-center align-middle">
                                <input type="checkbox" class="form-check-input edit-invoice-checkbox" data-index="${idx}" value="${inv.id}" ${isChecked ? 'checked' : ''} style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                            </td>
                            <td class="py-3 align-middle">
                                <div class="fw-bold font-monospace text-primary d-flex align-items-center gap-1.5" style="font-size: 0.9rem;">
                                    <i class="bi bi-file-earmark-check text-primary"></i> ${inv.nomor_faktur}
                                </div>
                                <div class="text-body-secondary small mt-0.5 d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                    <span><i class="bi bi-calendar3 me-1 text-secondary"></i>${inv.tanggal_faktur}</span>
                                    <span class="text-body-tertiary">|</span>
                                    <span class="text-warning-emphasis fw-medium"><i class="bi bi-clock-history me-1"></i>JT: ${inv.tanggal_jatuh_tempo}</span>
                                </div>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <span class="font-monospace text-body-emphasis fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp ${grandTotalFormatted}</span>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <span class="font-monospace fw-bold text-danger edit-inv-remaining" data-val="${inv.available_balance}" style="font-variant-numeric: tabular-nums; font-size: 0.92rem;">Rp ${availableFormatted}</span>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <div class="input-group input-group-sm ms-auto shadow-sm" style="max-width: 175px;">
                                    <span class="input-group-text bg-body-secondary text-success fw-bold font-monospace px-2" style="font-size: 0.76rem;">Rp</span>
                                    <input type="text" inputmode="numeric" class="form-control form-control-sm text-end font-monospace fw-bold text-success edit-inv-bayar-input" value="${bayarFormatted}" placeholder="0" style="font-variant-numeric: tabular-nums; font-size: 0.9rem; letter-spacing: 0.3px;" ${isChecked ? '' : 'disabled'}>
                                </div>
                            </td>
                            <td class="text-end py-3 align-middle">
                                <div class="input-group input-group-sm ms-auto shadow-sm" style="max-width: 155px;">
                                    <span class="input-group-text bg-body-secondary text-warning fw-bold font-monospace px-2" style="font-size: 0.76rem;">Rp</span>
                                    <input type="text" inputmode="numeric" class="form-control form-control-sm text-end font-monospace fw-semibold text-warning edit-inv-potongan-input" value="${potonganFormatted}" placeholder="0" style="font-variant-numeric: tabular-nums; font-size: 0.9rem; letter-spacing: 0.3px;" ${isChecked ? '' : 'disabled'}>
                                </div>
                            </td>
                            <td class="text-end pe-3 py-3 align-middle">
                                <span class="font-monospace fw-bold text-body-emphasis edit-inv-sisa-akhir" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp 0</span>
                            </td>
                        </tr>
                    `;
                });
                $('#edit_unpaid_invoices_tbody').html(html);

                // Update sisa akhir for each rendered row
                $('#edit_unpaid_invoices_tbody tr').each(function() {
                    editUpdateRowSisaAkhir($(this));
                });
                editCalculateSummary();
            }

            // Edit Check All
            $('#edit_check_all_invoices').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.edit-invoice-checkbox').each(function() {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            // Edit Single Checkbox Change
            $(document).on('change', '.edit-invoice-checkbox', function() {
                const row = $(this).closest('tr');
                const isChecked = $(this).is(':checked');
                const remaining = parseFloat(row.find('.edit-inv-remaining').data('val')) || 0;
                const bayarInput = row.find('.edit-inv-bayar-input');
                const potonganInput = row.find('.edit-inv-potongan-input');

                row.toggleClass('table-active bg-primary-subtle bg-opacity-10', isChecked);

                if (isChecked) {
                    bayarInput.prop('disabled', false);
                    potonganInput.prop('disabled', false);
                    if (parseRupiahInput(bayarInput.val()) === 0) {
                        bayarInput.val(formatRupiahDisplay(remaining));
                    }
                } else {
                    bayarInput.prop('disabled', true).val('0');
                    potonganInput.prop('disabled', true).val('0');
                }

                editUpdateRowSisaAkhir(row);
                editCalculateSummary();
            });

            // Realtime formatting input di edit modal
            $(document).on('input', '.edit-inv-bayar-input, .edit-inv-potongan-input', function() {
                const row = $(this).closest('tr');
                const isPotongan = $(this).hasClass('edit-inv-potongan-input');
                let rawVal = $(this).val();
                let parts = rawVal.split(',');
                let intDigits = parts[0].replace(/\D/g, '');
                let decDigits = parts.length > 1 ? parts[1].replace(/\D/g, '').slice(0, 2) : null;
                let formattedInt = intDigits ? parseInt(intDigits, 10).toLocaleString('id-ID') : '';
                let formattedVal = decDigits !== null ? (formattedInt + ',' + decDigits) : formattedInt;
                $(this).val(formattedVal || '0');

                // Auto-koreksi nilai agar tidak melebihi sisa piutang dan selaras antara kas vs potongan di modal edit
                const remaining = parseFloat(row.find('.edit-inv-remaining').data('val')) || 0;
                if (isPotongan) {
                    let potongan = parseRupiahInput(formattedVal);
                    if (potongan > remaining) {
                        potongan = remaining;
                        $(this).val(formatRupiahDisplay(potongan));
                    }
                    const newBayar = Math.max(0, Math.round((remaining - potongan) * 100) / 100);
                    row.find('.edit-inv-bayar-input').val(formatRupiahDisplay(newBayar));
                } else {
                    let bayar = parseRupiahInput(formattedVal);
                    if (bayar > remaining) {
                        bayar = remaining;
                        $(this).val(formatRupiahDisplay(bayar));
                    }
                    const currentPotongan = parseRupiahInput(row.find('.edit-inv-potongan-input').val());
                    if (bayar + currentPotongan > remaining) {
                        const newPotongan = Math.max(0, Math.round((remaining - bayar) * 100) / 100);
                        row.find('.edit-inv-potongan-input').val(formatRupiahDisplay(newPotongan));
                    }
                }

                editUpdateRowSisaAkhir(row);
                editCalculateSummary();
            });

            $(document).on('focus', '.edit-inv-bayar-input, .edit-inv-potongan-input', function() {
                if ($(this).val() === '0') {
                    $(this).select();
                }
            });

            $(document).on('blur', '.edit-inv-bayar-input, .edit-inv-potongan-input', function() {
                let parsed = parseRupiahInput($(this).val());
                $(this).val(formatRupiahDisplay(parsed));
                const row = $(this).closest('tr');
                const isPotongan = $(this).hasClass('edit-inv-potongan-input');
                const remaining = parseFloat(row.find('.edit-inv-remaining').data('val')) || 0;

                if (isPotongan) {
                    if (parsed > remaining) {
                        parsed = remaining;
                        $(this).val(formatRupiahDisplay(parsed));
                    }
                    const newBayar = Math.max(0, Math.round((remaining - parsed) * 100) / 100);
                    row.find('.edit-inv-bayar-input').val(formatRupiahDisplay(newBayar));
                } else {
                    if (parsed > remaining) {
                        parsed = remaining;
                        $(this).val(formatRupiahDisplay(parsed));
                    }
                    const currentPotongan = parseRupiahInput(row.find('.edit-inv-potongan-input').val());
                    if (parsed + currentPotongan > remaining) {
                        const newPotongan = Math.max(0, Math.round((remaining - parsed) * 100) / 100);
                        row.find('.edit-inv-potongan-input').val(formatRupiahDisplay(newPotongan));
                    }
                }

                editUpdateRowSisaAkhir(row);
                editCalculateSummary();
            });

            function editUpdateRowSisaAkhir(row) {
                const remaining = parseFloat(row.find('.edit-inv-remaining').data('val')) || 0;
                const bayar = parseRupiahInput(row.find('.edit-inv-bayar-input').val());
                const potongan = parseRupiahInput(row.find('.edit-inv-potongan-input').val());
                const totalPaid = Math.round((bayar + potongan) * 100) / 100;
                const sisaAkhir = Math.max(0, Math.round((remaining - totalPaid) * 100) / 100);
                const lebih = Math.max(0, Math.round((totalPaid - remaining) * 100) / 100);

                if (sisaAkhir <= 0.009 && totalPaid > 0) {
                    let html = '<span class="badge bg-success text-white border border-success px-2.5 py-1 fw-bold font-monospace shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>';
                    if (lebih > 0.009) {
                        html += `<div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5" style="font-size: 0.72rem;"><i class="bi bi-arrow-up-right-circle me-1"></i>Lebih: Rp ${formatRupiahDisplay(lebih)}</span></div>`;
                    }
                    row.find('.edit-inv-sisa-akhir').html(html);
                } else {
                    row.find('.edit-inv-sisa-akhir').html(`<span class="font-monospace fw-bold ${sisaAkhir > 0 ? 'text-danger' : 'text-body-emphasis'}" style="font-variant-numeric: tabular-nums; font-size: 0.9rem;">Rp ${formatRupiahDisplay(sisaAkhir)}</span>`);
                }
            }

            function editCalculateSummary() {
                let selectedCount = 0;
                let totalBayar = 0;
                let totalPotongan = 0;
                let totalLebih = 0;

                $('.edit-invoice-checkbox:checked').each(function() {
                    selectedCount++;
                    const row = $(this).closest('tr');
                    const remaining = parseFloat(row.find('.edit-inv-remaining').data('val')) || 0;
                    const bayar = parseRupiahInput(row.find('.edit-inv-bayar-input').val());
                    const potongan = parseRupiahInput(row.find('.edit-inv-potongan-input').val());
                    totalBayar += bayar;
                    totalPotongan += potongan;
                    const diff = (bayar + potongan) - remaining;
                    if (diff > 0.009) {
                        totalLebih += diff;
                    }
                });

                totalBayar = Math.round(totalBayar * 100) / 100;
                totalPotongan = Math.round(totalPotongan * 100) / 100;
                totalLebih = Math.round(totalLebih * 100) / 100;

                $('#edit_sum_selected_count').text(selectedCount);
                $('#edit_sum_total_potongan').text('Rp ' + formatRupiahDisplay(totalPotongan));
                $('#edit_sum_total_bayar').text('Rp ' + formatRupiahDisplay(totalBayar));

                if (totalLebih > 0.009) {
                    $('#edit_sum_lebih_bayar_container').removeClass('d-none');
                    $('#edit_sum_total_lebih').text('Rp ' + formatRupiahDisplay(totalLebih));
                } else {
                    $('#edit_sum_lebih_bayar_container').addClass('d-none');
                }

                // Tampilkan atau sembunyikan dropdown akun COA potongan pada form edit
                if (totalPotongan > 0.009) {
                    $('#edit_container_potongan_coa').removeClass('d-none');
                    if ($.fn.select2) {
                        if (!$('#edit_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                            $('#edit_potongan_coa_id').select2({
                                theme: 'bootstrap-5',
                                dropdownParent: $('#modalEditPayment'),
                                placeholder: '-- Cari Kode atau Nama Akun COA --',
                                allowClear: true,
                                width: '100%',
                                dropdownCssClass: 'select2-dropdown-dark-amber'
                            });
                        }
                    }
                } else {
                    $('#edit_container_potongan_coa').addClass('d-none');
                }
            }

            // Update Draft Button
            $('#btn_update_draft').on('click', function() {
                submitEditPaymentForm('Draft');
            });

            // Update & Submit (Ajukan ke Atasan) Button
            $('#btn_update_submit').on('click', function() {
                Swal.fire({
                    title: 'Ajukan Pembayaran ke Atasan?',
                    html: 'Apakah Anda yakin ingin menyimpan dan <strong>langsung mengajukan</strong> pembayaran ini ke atasan?<br><small class="text-body-secondary">Status akan berubah menjadi <strong>Menunggu Persetujuan</strong> dan notifikasi WhatsApp akan dikirimkan ke atasan untuk verifikasi.</small>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Simpan & Ajukan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#0d6efd'
                }).then((res) => {
                    if (res.isConfirmed) {
                        submitEditPaymentForm('Diajukan');
                    }
                });
            });

            function submitEditPaymentForm(targetStatus) {
                const paymentId = $('#edit_payment_id').val();
                const customerId = $('#edit_customer_id').val();
                const tanggalBayar = $('#edit_tanggal_bayar').val();
                const kasBankId = $('#edit_kas_bank_id').val();

                if (!customerId) {
                    Swal.fire('Validasi', 'Silakan pilih customer terlebih dahulu.', 'warning');
                    return;
                }
                if (!tanggalBayar) {
                    Swal.fire('Validasi', 'Silakan isi tanggal pembayaran.', 'warning');
                    return;
                }
                if (!kasBankId) {
                    Swal.fire('Validasi', 'Silakan pilih Kas / Bank tujuan.', 'warning');
                    return;
                }

                const editLampiranFile = $('#edit_lampiran')[0]?.files[0];
                const existingLampiran = $('#btn_preview_edit_lampiran').attr('data-url');
                if (!editLampiranFile && !existingLampiran) {
                    Swal.fire('Validasi', 'Dokumen atau foto bukti transfer pembayaran wajib disertakan.', 'warning');
                    $('#edit_lampiran').focus();
                    return;
                }

                const checkedBoxes = $('.edit-invoice-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    Swal.fire('Validasi', 'Silakan pilih minimal satu faktur yang akan dibayar.', 'warning');
                    return;
                }

                let hasZeroAllocation = false;
                const invoicesData = [];

                checkedBoxes.each(function() {
                    const row = $(this).closest('tr');
                    const invId = $(this).val();
                    const bayar = parseRupiahInput(row.find('.edit-inv-bayar-input').val());
                    const potongan = parseRupiahInput(row.find('.edit-inv-potongan-input').val());

                    if ((bayar + potongan) <= 0) {
                        hasZeroAllocation = true;
                    }

                    totalBayar += bayar;
                    invoicesData.push({
                        id: invId,
                        bayar: bayar,
                        potongan: potongan
                    });
                });

                if (hasZeroAllocation) {
                    Swal.fire('Validasi', 'Setiap faktur yang dicentang harus memiliki nominal bayar atau potongan lebih besar dari Rp 0.', 'warning');
                    return;
                }

                const formData = new FormData($('#formEditPayment')[0]);
                formData.set('status', targetStatus);
                formData.set('tanggal_bayar', tanggalBayar);
                formData.set('customer_id', customerId);
                formData.set('kas_bank_id', kasBankId);
                formData.set('payment_method_id', $('#edit_payment_method_id').val() || '');
                formData.set('nomor_referensi', $('#edit_nomor_referensi').val() || '');
                formData.set('keterangan', $('#edit_keterangan').val() || '');
                formData.set('potongan_coa_id', $('#edit_potongan_coa_id').val() || '');

                // Append array of invoices
                invoicesData.forEach(function(inv, idx) {
                    formData.append(`invoices[${idx}][id]`, inv.id);
                    formData.append(`invoices[${idx}][bayar]`, inv.bayar);
                    formData.append(`invoices[${idx}][potongan]`, inv.potongan);
                });

                const saveBtn = targetStatus === 'Diajukan' ? $('#btn_update_submit') : $('#btn_update_draft');
                const origText = saveBtn.html();
                $('#btn_update_draft, #btn_update_submit').prop('disabled', true);
                saveBtn.html('<div class="spinner-border spinner-border-sm me-1" role="status"></div> Menyimpan...');

                $.ajax({
                    url: "{{ url('modul-marketing/pembayaran') }}/" + paymentId + "/update",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#btn_update_draft, #btn_update_submit').prop('disabled', false);
                        saveBtn.html(origText);
                        if (res.success) {
                            $('#modalEditPayment').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        $('#btn_update_draft, #btn_update_submit, #btn_update_post').prop('disabled', false);
                        saveBtn.html(origText);
                        let msg = xhr.responseJSON?.message || 'Gagal menyimpan perubahan pembayaran.';
                        if (xhr.responseJSON?.errors) {
                            const errs = Object.values(xhr.responseJSON.errors).flat();
                            msg = errs.join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: msg
                        });
                    }
                });
            }

            // ── Reset Form saat Modal Create Ditutup ───────────────────────────
            $('#modalCreatePayment').on('hidden.bs.modal', function () {
                $('#formCreatePayment')[0].reset();
                if ($.fn.select2) {
                    $('#create_invoice_select').val('').trigger('change.select2');
                    $('#create_customer_id').val('').trigger('change.select2');
                    $('#create_kas_bank_id').val('').trigger('change.select2');
                    $('#create_payment_method_id').val('').trigger('change.select2');
                } else {
                    $('#create_invoice_select').val('');
                    $('#create_customer_id').val('');
                    $('#create_kas_bank_id').val('');
                    $('#create_payment_method_id').val('');
                }
                $('#badge_customer_auto').addClass('d-none');
                $('#btn_clear_invoice_select').addClass('d-none');
                $('#invoices_empty_state').removeClass('d-none');
                $('#invoices_loading_state').addClass('d-none');
                $('#invoices_none_state').addClass('d-none');
                $('#invoices_table_container').addClass('d-none');
                $('#unpaid_invoices_tbody').empty();
                if ($.fn.select2 && $('#create_potongan_coa_id').hasClass('select2-hidden-accessible')) {
                    $('#create_potongan_coa_id').val('{{ $defaultPotonganCoaId ?? '' }}').trigger('change.select2');
                } else {
                    $('#create_potongan_coa_id').val('{{ $defaultPotonganCoaId ?? '' }}');
                }
                $('#container_potongan_coa').addClass('d-none');
                calculateSummary();
            });

            $('#modalDetailPayment').on('hidden.bs.modal', function () {
                activeDetailPaymentId = null;
            });

            // ── Modern Modal Preview Bukti Transfer Logic ─────────────────────
            function openPreviewLampiranModal(url, nomor, filename) {
                if (!url) return;
                $('#modal_preview_nomor').text(nomor || '-');
                $('#btn_modal_preview_external').attr('href', url);
                $('#btn_modal_preview_download').attr('href', url);

                const isPdf = url.toLowerCase().includes('.pdf') || (filename && filename.toLowerCase().endsWith('.pdf'));

                if (isPdf) {
                    $('#preview_lampiran_img_container').addClass('d-none');
                    $('#preview_lampiran_pdf_container').removeClass('d-none');
                    $('#preview_lampiran_pdf').attr('src', url);
                    $('#preview_lampiran_img').attr('src', '');
                } else {
                    $('#preview_lampiran_pdf_container').addClass('d-none');
                    $('#preview_lampiran_img_container').removeClass('d-none');
                    $('#preview_lampiran_img').attr('src', url);
                    $('#preview_lampiran_pdf').attr('src', '');
                }

                $('#preview_lampiran_empty').addClass('d-none');
                $('#modalPreviewBuktiTransfer').modal('show');
            }

            // Atur backdrop khusus untuk modal preview agar bertumpuk rapi di atas modal edit
            $('#modalPreviewBuktiTransfer').on('show.bs.modal', function () {
                setTimeout(function () {
                    $('.modal-backdrop:last').addClass('preview-backdrop');
                }, 10);
            });

            // Kembalikan class modal-open pada body jika modal edit masih terbuka saat preview ditutup
            $('#modalPreviewBuktiTransfer').on('hidden.bs.modal', function () {
                if ($('#modalEditPayment').hasClass('show') || $('#modalDetailPayment').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // Klik tombol preview lampiran pada form Edit
            $(document).on('click', '#btn_preview_edit_lampiran', function(e) {
                e.preventDefault();
                const url = $(this).attr('data-url');
                const nomor = $('#edit_nomor_pembayaran').val() || $('#edit_nomor_badge').text() || 'Pembayaran';
                const filename = $(this).attr('data-filename') || '';
                if (url) {
                    openPreviewLampiranModal(url, nomor, filename);
                }
            });

            // Klik tombol preview lampiran pada form Detail
            $(document).on('click', '.btn-open-preview-bukti', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const nomor = $(this).data('nomor') || 'Pembayaran';
                if (url) {
                    openPreviewLampiranModal(url, nomor);
                }
            });

            function validateSelectedAttachment(inputEl) {
                const file = inputEl.files[0];
                if (!file) return true;

                const maxBytes = 10 * 1024 * 1024; // 10MB
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
                const fileExt = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExt)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Format Berkas Tidak Didukung',
                        text: 'Format berkas "' + file.name + '" tidak diperbolehkan. Harap unggah berkas dengan format JPG, PNG, WEBP, atau PDF.',
                        confirmButtonColor: '#059669'
                    });
                    inputEl.value = '';
                    return false;
                }

                if (file.size > maxBytes) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Ukuran Berkas Terlalu Besar',
                        html: `Ukuran berkas <strong>${fileSizeMB} MB</strong> melebihi batas maksimal <strong>10 MB</strong>.<br>Silakan pilih berkas yang lebih kecil.`,
                        confirmButtonColor: '#059669'
                    });
                    inputEl.value = '';
                    return false;
                }

                return true;
            }

            // Deteksi pemilihan file baru pada form Edit
            $(document).on('change', '#edit_lampiran', function() {
                if (!validateSelectedAttachment(this)) {
                    $('#btn_preview_edit_lampiran').addClass('d-none').attr('data-url', '');
                    return;
                }
                const file = this.files[0];
                if (file) {
                    const fileUrl = URL.createObjectURL(file);
                    $('#btn_preview_edit_lampiran')
                        .removeClass('d-none')
                        .attr('data-url', fileUrl)
                        .attr('data-filename', file.name)
                        .html('<i class="bi bi-eye-fill me-1"></i> Lihat Berkas Baru');
                }
            });

            // Deteksi pemilihan file baru pada form Tambah (Create)
            $(document).on('change', '#create_lampiran', function() {
                if (!validateSelectedAttachment(this)) {
                    $('#btn_preview_create_lampiran').addClass('d-none').attr('data-url', '');
                    return;
                }
                const file = this.files[0];
                if (file) {
                    const fileUrl = URL.createObjectURL(file);
                    $('#btn_preview_create_lampiran')
                        .removeClass('d-none')
                        .attr('data-url', fileUrl)
                        .attr('data-filename', file.name);
                } else {
                    $('#btn_preview_create_lampiran').addClass('d-none').attr('data-url', '');
                }
            });

            $(document).on('click', '#btn_preview_create_lampiran', function(e) {
                e.preventDefault();
                const url = $(this).attr('data-url');
                const filename = $(this).attr('data-filename') || '';
                if (url) {
                    openPreviewLampiranModal(url, 'Pembayaran Baru', filename);
                }
            });

            // ── Modal Lookup Faktur Penjualan ─────────────────────────────────
            let lookupInvoicesData = [];

            $('#btn_open_lookup_invoice').on('click', function() {
                $('#modalLookupSalesInvoice').modal('show');
                loadLookupInvoices();
            });

            $('#btn_refresh_lookup').on('click', function() {
                loadLookupInvoices();
            });

            $('#lookup_customer_filter').on('change', function() {
                filterAndRenderLookupTable();
            });

            $('#lookup_search_query').on('input', function() {
                filterAndRenderLookupTable();
            });

            function loadLookupInvoices() {
                $('#lookup_table_wrapper').addClass('d-none');
                $('#lookup_empty_state').addClass('d-none');
                $('#lookup_loading_state').removeClass('d-none');

                $.ajax({
                    url: "{{ route('modul_marketing.pembayaran.all_unpaid_invoices') }}",
                    method: 'GET',
                    success: function(res) {
                        $('#lookup_loading_state').addClass('d-none');
                        if (res.success && res.data) {
                            lookupInvoicesData = res.data;
                            filterAndRenderLookupTable();
                        } else {
                            $('#lookup_empty_state').removeClass('d-none');
                            $('#lookup_count_info').text('Menampilkan 0 faktur');
                        }
                    },
                    error: function() {
                        $('#lookup_loading_state').addClass('d-none');
                        $('#lookup_empty_state').removeClass('d-none');
                    }
                });
            }

            function filterAndRenderLookupTable() {
                const query = ($('#lookup_search_query').val() || '').toLowerCase().trim();
                const custFilter = $('#lookup_customer_filter').val();

                const filtered = lookupInvoicesData.filter(function(item) {
                    const matchQuery = !query || 
                        item.nomor_faktur.toLowerCase().includes(query) || 
                        item.customer_name.toLowerCase().includes(query) ||
                        (item.customer_code && item.customer_code.toLowerCase().includes(query));

                    const matchCust = !custFilter || String(item.customer_id) === String(custFilter);
                    return matchQuery && matchCust;
                });

                if (filtered.length === 0) {
                    $('#lookup_table_wrapper').addClass('d-none');
                    $('#lookup_empty_state').removeClass('d-none');
                    $('#lookup_count_info').text('Menampilkan 0 faktur');
                    return;
                }

                $('#lookup_empty_state').addClass('d-none');
                let html = '';
                filtered.forEach(function(item) {
                    html += `
                        <tr>
                            <td class="ps-3 py-2.5">
                                <span class="fw-bold font-monospace text-primary">${item.nomor_faktur}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-body-emphasis">${item.customer_name}</div>
                                ${item.customer_code ? `<span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">${item.customer_code}</span>` : ''}
                            </td>
                            <td class="text-body-secondary small">${item.tanggal_faktur}</td>
                            <td class="text-body-secondary small">${item.tanggal_jatuh_tempo}</td>
                            <td class="text-end font-monospace text-body-secondary">${item.grand_total_formated}</td>
                            <td class="text-end font-monospace fw-bold text-danger">${item.remaining_formated}</td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning">${item.status}</span>
                            </td>
                            <td class="text-center pe-3">
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 btn-select-lookup"
                                    data-id="${item.id}"
                                    data-customer-id="${item.customer_id}"
                                    data-customer-name="${item.customer_name}"
                                    data-nomor="${item.nomor_faktur}"
                                    data-sisa="${item.remaining_balance}">
                                    <i class="bi bi-check2 me-1"></i>Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#lookup_invoices_tbody').html(html);
                $('#lookup_table_wrapper').removeClass('d-none');
                $('#lookup_count_info').text(`Menampilkan ${filtered.length} faktur`);
            }

            // Click button Pilih inside Lookup
            $(document).on('click', '.btn-select-lookup', function() {
                const invId = $(this).data('id');
                const custId = $(this).data('customer-id');
                const custName = $(this).data('customer-name');
                const nomor = $(this).data('nomor');
                const sisa = $(this).data('sisa');

                // Tutup modal lookup
                $('#modalLookupSalesInvoice').modal('hide');

                // Pastikan option ada di create_invoice_select
                if ($(`#create_invoice_select option[value="${invId}"]`).length === 0) {
                    const formattedSisa = formatRupiahDisplay(sisa);
                    const optText = `${nomor} — ${custName} (Sisa: Rp ${formattedSisa})`;
                    const newOpt = new Option(optText, invId, true, true);
                    $(newOpt).attr('data-customer-id', custId);
                    $(newOpt).attr('data-customer-name', custName);
                    $(newOpt).attr('data-nomor', nomor);
                    $(newOpt).attr('data-sisa', sisa);
                    $('#create_invoice_select').append(newOpt);
                    populateInvoicesCache();
                }

                // Set value & trigger change
                if ($.fn.select2 && $('#create_invoice_select').hasClass('select2-hidden-accessible')) {
                    $('#create_invoice_select').val(invId).trigger('change.select2');
                } else {
                    $('#create_invoice_select').val(invId);
                }
                $('#create_invoice_select').trigger('change');

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: `Faktur ${nomor} untuk ${custName} berhasil ditarik!`
                });
            });

            // ── Auto-Open Create Modal from URL params (?customer_id=...&invoice_id=...) ──
            const urlParams = new URLSearchParams(window.location.search);
            const targetCustomerId = urlParams.get('customer_id');
            const targetInvoiceId  = urlParams.get('invoice_id');

            if (targetInvoiceId) {
                $('#modalCreatePayment').modal('show');
                setTimeout(function() {
                    initPaymentSelect2();
                    if ($(`#create_invoice_select option[value="${targetInvoiceId}"]`).length > 0) {
                        $('#create_invoice_select').val(targetInvoiceId).trigger('change');
                    } else if (targetCustomerId) {
                        $('#create_customer_id').val(targetCustomerId).trigger('change.byInvoice', [targetInvoiceId]);
                    }
                }, 400);
            } else if (targetCustomerId) {
                $('#modalCreatePayment').modal('show');
                setTimeout(function() {
                    initPaymentSelect2();
                    $('#create_customer_id').val(targetCustomerId).trigger('change');
                }, 400);
            }
        });
    </script>
    @endpush
</x-layouts>
