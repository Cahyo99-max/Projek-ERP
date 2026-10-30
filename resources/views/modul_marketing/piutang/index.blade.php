<x-layouts>
    <x-slot:title>Rekap Sisa Tagihan & Piutang Pelanggan</x-slot:title>

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_smd/piutang.css') }}?v={{ time() }}" />
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
        .select2-dropdown {
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--bs-border-color, #dee2e6);
            background-color: var(--bs-body-bg, #ffffff);
            font-size: 0.84rem;
            z-index: 1070 !important;
            overflow: hidden;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 0.5rem;
            font-size: 0.82rem;
            padding: 6px 12px;
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            border-color: var(--bs-border-color, #dee2e6);
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
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .action-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 0.88rem;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: transparent;
        }

        .action-btn:hover {
            transform: translateY(-2px) scale(1.08);
        }

        .action-btn:active {
            transform: translateY(0) scale(0.95);
        }

        /* View / Detail (Cyan Neon) */
        .action-btn-view {
            background: rgba(6, 182, 212, 0.12);
            border-color: rgba(6, 182, 212, 0.35);
            color: #22d3ee !important;
        }
        .action-btn-view:hover {
            background: rgba(6, 182, 212, 0.28);
            border-color: #06b6d4;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(6, 182, 212, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }

        /* Print (Purple / Indigo Neon) */
        .action-btn-print {
            background: rgba(168, 85, 247, 0.12);
            border-color: rgba(168, 85, 247, 0.35);
            color: #c084fc !important;
        }
        .action-btn-print:hover {
            background: rgba(168, 85, 247, 0.28);
            border-color: #a855f7;
            color: #ffffff !important;
            box-shadow: 0 0 14px rgba(168, 85, 247, 0.6), 0 2px 6px rgba(0,0,0,0.4);
        }





        /* Cinematic Nav Tabs */
        .nav-cinematic {
            background: rgba(15, 23, 42, 0.05);
            border-radius: 12px;
            padding: 4px;
            display: flex;
            gap: 4px;
            border: 1px solid var(--bs-border-color);
            width: 100%;
        }
        .nav-cinematic .nav-link {
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 0.83rem;
            color: var(--bs-body-color);
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            flex: 1;
            text-align: center;
            line-height: 1.3;
        }
        .nav-cinematic .nav-link:hover {
            background: rgba(0, 0, 0, 0.04);
            color: #059669;
        }
        .nav-cinematic .nav-link.active {
            background: #059669;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        /* Hide long nav text on small screens, show short version */
        .nav-cinematic .nav-link .nav-label-full { display: inline; }
        .nav-cinematic .nav-link .nav-label-short { display: none; }
        @media (max-width: 575.98px) {
            .nav-cinematic .nav-link .nav-label-full { display: none; }
            .nav-cinematic .nav-link .nav-label-short { display: inline; }
            .nav-cinematic .nav-link { padding: 8px 8px; font-size: 0.78rem; }
        }

        /* Custom KPI Card Styling */
        .kpi-stat-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        /* ════════════════════════════════════════════════════════════════
           FILTER TRIGGER BUTTON — adaptive dark/light
           ════════════════════════════════════════════════════════════════ */
        .btn-filter-trigger {
            background: var(--bs-tertiary-bg);
            border: 1.5px solid var(--bs-border-color);
            color: var(--bs-body-color);
            font-size: 0.86rem;
            transition: all 0.22s ease;
        }
        .btn-filter-trigger:hover {
            background: var(--bs-secondary-bg);
            border-color: #818cf8;
            color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
        }
        .btn-filter-trigger .badge {
            background: #6366f1;
            color: #fff;
            font-size: 0.72rem;
            padding: 0.25em 0.6em;
        }
        .btn-filter-trigger.btn-filter-active {
            background: rgba(99, 102, 241, 0.12);
            border-color: #818cf8;
            color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
        }
        .btn-filter-apply {
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
            border: none;
            color: #fff;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }
        .btn-filter-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        /* ════════════════════════════════════════════════════════════════
           FILTER CHIPS — adaptive dark/light
           ════════════════════════════════════════════════════════════════ */
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px 4px 12px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(129, 140, 248, 0.35);
            color: #818cf8;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
            animation: chipIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes chipIn {
            from { opacity: 0; transform: scale(0.8) translateY(4px); }
            to   { opacity: 1; transform: scale(1)   translateY(0); }
        }
        .chip-remove {
            all: unset;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            opacity: 0.6;
            transition: opacity 0.15s;
        }
        .chip-remove:hover { opacity: 1; }

        /* ════════════════════════════════════════════════════════════════
           MODAL FILTER — fully adaptive dark/light
           ════════════════════════════════════════════════════════════════ */
        #modalFilterPiutang .modal-content {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
        }
        #modalFilterPiutang .modal-header {
            background: var(--bs-body-bg);
        }
        #modalFilterPiutang .modal-body {
            background: var(--bs-body-bg);
        }
        #modalFilterPiutang .modal-footer {
            background: var(--bs-tertiary-bg);
            border-top: 1px solid var(--bs-border-color);
        }
        .filter-modal-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            flex-shrink: 0;
        }
        .filter-group-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--bs-secondary-color);
            margin-bottom: 10px;
        }
        .filter-group-label i {
            font-size: 0.9rem;
            color: #818cf8;
        }

        /* ════════════════════════════════════════════════════════════════
           AGING OPTION CARDS — adaptive dark/light
           ════════════════════════════════════════════════════════════════ */
        .aging-option-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .aging-option-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 6px;
            border-radius: 12px;
            border: 1.5px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-align: center;
            user-select: none;
        }
        .aging-option-card:hover {
            border-color: #818cf8;
            background: rgba(99, 102, 241, 0.08);
            transform: translateY(-2px);
        }
        .aging-option-card.selected {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.14);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
            transform: translateY(-2px);
        }
        .aging-option-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: transform 0.2s ease;
        }
        .aging-option-card.selected .aging-option-icon {
            transform: scale(1.1);
        }
        .aging-option-text {
            font-size: 0.71rem;
            font-weight: 600;
            color: var(--bs-body-color);
            line-height: 1.2;
        }

        /* Orange subtle helper */
        .bg-orange-subtle { background-color: rgba(249, 115, 22, 0.12) !important; }
        .text-orange { color: #f97316 !important; }

        /* ════════════════════════════════════════════════════════════════
           MOBILE RESPONSIVE OVERRIDES
           ════════════════════════════════════════════════════════════════ */
        @media (max-width: 575.98px) {
            /* Header */
            .page-title-text { font-size: 1rem !important; }
            .page-subtitle-text { font-size: 0.78rem !important; }

            /* KPI Cards — compact on mobile */
            .kpi-stat-card .card-body { padding: 12px 12px !important; }
            .kpi-stat-card .kpi-label { font-size: 0.68rem !important; }
            .kpi-stat-card .kpi-value { font-size: 1rem !important; }
            .kpi-stat-card .kpi-sub { font-size: 0.7rem !important; }
            .kpi-stat-card .kpi-icon { width: 36px !important; height: 36px !important; padding: 8px !important; font-size: 1rem !important; }

            /* Aging option grid: 2 columns on mobile */
            .aging-option-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
            .aging-option-card { padding: 10px 4px; }
            .aging-option-icon { width: 32px; height: 32px; font-size: 0.85rem; }

            /* Filter modal: full width comfortable */
            #modalFilterPiutang .modal-dialog { margin: 8px; }
            #modalFilterPiutang .modal-body { padding: 12px 14px !important; }
            #modalFilterPiutang .modal-header { padding: 14px 14px 8px !important; }
            #modalFilterPiutang .modal-footer { padding: 8px 14px 16px !important; }

            /* Table: smaller font on mobile */
            #tableCustomerReceivables { font-size: 0.78rem !important; }
            .table-scroll-hint { display: flex !important; }
        }

        /* ════════════════════════════════════════════════════════════════
           MODERN TABLE STYLES
           ════════════════════════════════════════════════════════════════ */
        .table-card-modern {
            border: 1px solid var(--bs-border-color);
            border-radius: 16px;
            overflow: hidden;
        }
        .table-card-modern .card-header {
            background: var(--bs-tertiary-bg);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 14px 20px;
        }
        .table-scroll-wrapper {
            position: relative;
            overflow: hidden;
        }
        .table-scroll-wrapper .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--bs-border-color) transparent;
        }
        .table-scroll-wrapper .table-responsive::-webkit-scrollbar {
            height: 5px;
        }
        .table-scroll-wrapper .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-scroll-wrapper .table-responsive::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
            border-radius: 99px;
        }
        /* Scroll shadow overlay — right edge fade */
        .table-scroll-wrapper::after {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 5px;
            width: 32px;
            background: linear-gradient(to left, var(--bs-body-bg, #fff), transparent);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 0 0 16px 0;
        }
        .table-scroll-wrapper.can-scroll-right::after { opacity: 1; }
        /* Table itself */
        #tableCustomerReceivables {
            min-width: 760px;
            font-size: 0.84rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        #tableCustomerReceivables thead th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--bs-secondary-color);
            background: var(--bs-tertiary-bg);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 12px 14px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        #tableCustomerReceivables tbody tr {
            border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,0.06));
            transition: background 0.15s ease;
        }
        #tableCustomerReceivables tbody tr:last-child { border-bottom: none; }
        #tableCustomerReceivables tbody tr:hover {
            background: var(--bs-tertiary-bg) !important;
        }
        #tableCustomerReceivables td {
            padding: 13px 14px;
            vertical-align: middle;
        }
        /* Scroll hint badge — visible only on mobile */
        .table-scroll-hint {
            display: none;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            color: var(--bs-secondary-color);
            animation: hintPulse 2s ease-in-out infinite;
        }
        @keyframes hintPulse {
            0%, 100% { opacity: 0.6; transform: translateX(0); }
            50% { opacity: 1; transform: translateX(3px); }
        }

        /* Mobile Scrollable Tabs & Detail Modal in HP */
        .modal-detail-tabs {
            border-bottom: 2px solid var(--bs-border-color);
            gap: 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .modal-detail-tabs::-webkit-scrollbar {
            display: none;
        }
        .modal-detail-tabs .nav-link {
            white-space: nowrap;
            border-radius: 8px 8px 0 0;
            border: 1px solid transparent;
            color: var(--bs-secondary-color);
            transition: all 0.2s ease;
        }
        .modal-detail-tabs .nav-link.active {
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color) var(--bs-border-color) transparent;
            color: var(--bs-primary);
            box-shadow: 0 -2px 6px rgba(0,0,0,0.04);
        }
        @media (max-width: 767.98px) {
            #modalDetailCustomerReceivable .modal-body {
                padding: 1rem 0.75rem !important;
            }
        }
        @media (max-width: 575.98px) {
            #modalDetailCustomerReceivable .modal-footer .btn {
                width: 100% !important;
            }
        }
    </style>
    @endpush

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title (Simplified & Modern) -->
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3.5">
            <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                <div class="ar-page-icon shadow-sm flex-shrink-0">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
                <div class="overflow-hidden">
                    <h4 class="fw-bold mb-0 text-body-emphasis text-truncate" style="font-size: clamp(1.05rem, 3.5vw, 1.25rem);">
                        Rekap Piutang &amp; Sisa Tagihan
                    </h4>
                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                        @if(session('active_branch_name'))
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 text-truncate" style="font-size: 0.7rem; max-width: 220px;">
                                <i class="bi bi-geo-alt-fill me-1"></i>Cabang: {{ session('active_branch_name') }}
                            </span>
                        @else
                            <span class="badge bg-indigo-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 text-truncate" style="background: rgba(30, 64, 175, 0.1); color: #1e40af; border-color: rgba(30, 64, 175, 0.25); font-size: 0.7rem; max-width: 220px;">
                                <i class="bi bi-building me-1"></i>Pusat: {{ session('active_company_name') ?? 'PT Pusat' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tombol Refresh Bulat Minimalis -->
            <button type="button" class="btn btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" id="btn_refresh_table" style="width: 38px; height: 38px;" title="Segarkan Data">
                <i class="bi bi-arrow-clockwise fs-6"></i>
            </button>
        </div>

        <!-- Navigation Tabs: Switcher antara Pembayaran dan Rekap Piutang -->
        <div class="mb-4">
            <div class="nav-cinematic">
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-sales-payment'))
                <a href="{{ route('modul_marketing.pembayaran.index') }}" class="nav-link">
                    <i class="bi bi-cash-stack flex-shrink-0"></i>
                    <span>Pembayaran</span>
                </a>
                @endif
                <a href="{{ route('modul_marketing.piutang.index') }}" class="nav-link active">
                    <i class="bi bi-card-checklist flex-shrink-0"></i>
                    <span>Rekap Piutang</span>
                </a>
            </div>
        </div>

        <!-- KPI Cards Summary Row (Modern Glassmorphic) -->
        <div class="row g-3 g-sm-3.5 g-md-4 mb-4 mb-md-4.5">
            <!-- Total Piutang Berjalan -->
            <div class="col-6 col-lg-3">
                <div class="ar-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="ar-kpi-label">Total Piutang</span>
                            <div class="ar-kpi-icon ar-kpi-icon-rose">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-danger font-monospace fs-4 fs-md-3 text-truncate" title="Rp {{ number_format((float)($kpi->total_piutang_berjalan ?? 0), 0, ',', '.') }}">
                            Rp {{ number_format((float)($kpi->total_piutang_berjalan ?? 0), 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-clock-history me-1"></i>{{ number_format($kpi->customer_unpaid_count ?? 0) }} Belum Lunas
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Piutang Jatuh Tempo -->
            <div class="col-6 col-lg-3">
                <div class="ar-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="ar-kpi-label">Jatuh Tempo</span>
                            <div class="ar-kpi-icon ar-kpi-icon-amber">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-warning font-monospace fs-4 fs-md-3 text-truncate" title="Rp {{ number_format((float)($kpi->total_piutang_overdue ?? 0), 0, ',', '.') }}">
                            Rp {{ number_format((float)($kpi->total_piutang_overdue ?? 0), 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-shield-exclamation me-1"></i>{{ number_format($kpi->customer_overdue_count ?? 0) }} Menunggak
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pelanggan Menunggak -->
            <div class="col-6 col-lg-3">
                <div class="ar-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="ar-kpi-label">Menunggak</span>
                            <div class="ar-kpi-icon ar-kpi-icon-indigo">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-body font-monospace fs-4 fs-md-3">
                            {{ number_format($kpi->customer_overdue_count ?? 0) }}
                            <small class="fs-7 text-body-secondary font-sans">PT</small>
                        </h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-exclamation-circle me-1"></i>Perlu Ditindaklanjuti
                        </span>
                    </div>
                </div>
            </div>

            <!-- Terbayar Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="ar-kpi-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="ar-kpi-label">Terbayar</span>
                            <div class="ar-kpi-icon ar-kpi-icon-emerald">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 text-success font-monospace fs-4 fs-md-3 text-truncate" title="Rp {{ number_format((float)($totalBayarBulanIni ?? 0), 0, ',', '.') }}">
                            Rp {{ number_format((float)($totalBayarBulanIni ?? 0), 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-calendar2-month me-1"></i>{{ now()->translatedFormat('F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Trigger Button Bar -->
        <div class="d-flex align-items-center gap-2 mb-4">
            <button type="button" class="btn btn-filter-trigger d-flex align-items-center gap-2 px-4 py-2 rounded-pill fw-semibold shadow-sm" id="btn_open_filter_modal" data-bs-toggle="modal" data-bs-target="#modalFilterPiutang">
                <i class="bi bi-sliders2 fs-6"></i>
                <span>Filter Data</span>
                <span class="badge rounded-pill ms-1 d-none" id="filter_active_badge">0</span>
            </button>
            <div id="filter_active_chips" class="d-flex align-items-center gap-2 flex-wrap"></div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             MODAL FILTER PIUTANG — MODERN GLASSMORPHISM STYLE
             ════════════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalFilterPiutang" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
                <div class="modal-content rounded-4 border-0 overflow-hidden" style="backdrop-filter: blur(20px);">

                    <!-- Modal Header -->
                    <div class="modal-header border-0 px-4 pt-4 pb-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="filter-modal-icon">
                                <i class="bi bi-sliders2 fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Filter Data Piutang</h5>
                                <p class="text-muted small mb-0">Pilih kriteria untuk menyaring data</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body px-4 py-3">

                        <!-- Filter Pelanggan -->
                        <div class="filter-group mb-4">
                            <label class="filter-group-label">
                                <i class="bi bi-building"></i>
                                Pelanggan / Perusahaan (PT)
                            </label>
                            <select class="form-select select2-filter-modal" id="filter_customer_id" style="width: 100%;">
                                <option value="">— Semua Pelanggan —</option>
                                @foreach($customerList as $cust)
                                <option value="{{ $cust->id }}">
                                    [{{ $cust->kode_customer }}] {{ $cust->nama_customer }} {{ $cust->nama_perusahaan ? '(' . $cust->nama_perusahaan . ')' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Status Aging -->
                        <div class="filter-group mb-2">
                            <label class="filter-group-label">
                                <i class="bi bi-bar-chart-steps"></i>
                                Status Umur Piutang (Aging)
                            </label>
                            <div class="aging-option-grid">
                                <label class="aging-option-card" data-value="">
                                    <input type="radio" name="filter_aging_radio" value="" class="d-none" checked>
                                    <div class="aging-option-icon bg-secondary-subtle text-secondary"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                                    <div class="aging-option-text">Semua (Incl. Lunas)</div>
                                </label>
                                <label class="aging-option-card" data-value="has_piutang">
                                    <input type="radio" name="filter_aging_radio" value="has_piutang" class="d-none">
                                    <div class="aging-option-icon bg-danger-subtle text-danger"><i class="bi bi-hourglass-split"></i></div>
                                    <div class="aging-option-text">Belum Lunas</div>
                                </label>
                                <label class="aging-option-card" data-value="current">
                                    <input type="radio" name="filter_aging_radio" value="current" class="d-none">
                                    <div class="aging-option-icon bg-primary-subtle text-primary"><i class="bi bi-shield-check"></i></div>
                                    <div class="aging-option-text">Lancar</div>
                                </label>
                                <label class="aging-option-card" data-value="overdue_1_30">
                                    <input type="radio" name="filter_aging_radio" value="overdue_1_30" class="d-none">
                                    <div class="aging-option-icon bg-warning-subtle text-warning"><i class="bi bi-clock-history"></i></div>
                                    <div class="aging-option-text">1–30 Hari</div>
                                </label>
                                <label class="aging-option-card" data-value="overdue_31_60">
                                    <input type="radio" name="filter_aging_radio" value="overdue_31_60" class="d-none">
                                    <div class="aging-option-icon bg-orange-subtle text-orange"><i class="bi bi-exclamation-circle"></i></div>
                                    <div class="aging-option-text">31–60 Hari</div>
                                </label>
                                <label class="aging-option-card" data-value="overdue_60_plus">
                                    <input type="radio" name="filter_aging_radio" value="overdue_60_plus" class="d-none">
                                    <div class="aging-option-icon bg-danger-subtle text-danger"><i class="bi bi-fire"></i></div>
                                    <div class="aging-option-text">Kritis >60 Hari</div>
                                </label>
                                <label class="aging-option-card" data-value="lunas">
                                    <input type="radio" name="filter_aging_radio" value="lunas" class="d-none">
                                    <div class="aging-option-icon bg-success-subtle text-success"><i class="bi bi-patch-check-fill"></i></div>
                                    <div class="aging-option-text">Lunas</div>
                                </label>
                                <label class="aging-option-card" data-value="all_master">
                                    <input type="radio" name="filter_aging_radio" value="all_master" class="d-none">
                                    <div class="aging-option-icon bg-info-subtle text-info"><i class="bi bi-people-fill"></i></div>
                                    <div class="aging-option-text">Semua Master</div>
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold flex-fill" id="btn_reset_filter">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-filter-apply rounded-pill px-4 fw-bold flex-fill" id="btn_apply_filter" data-bs-dismiss="modal">
                            <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Main Card Data Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden ar-table-card">
            <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex">
                        <i class="bi bi-table fs-6"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-body-emphasis">Daftar Rekap Piutang Pelanggan</h6>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1 fw-bold d-none d-sm-inline-block">Live Data</span>
                </div>
            </div>

            <div class="card-body p-3 p-md-3.5">
                <!-- Mobile Swipe Hint Banner -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2.5 mb-3 rounded-3 ar-mobile-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-success"></i>
                        <span class="fw-semibold small">Geser tabel piutang ke samping</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold ar-swipe-pill">Swipe &rarr;</span>
                </div>

                <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                    <table class="table table-hover align-middle mb-0 w-100" id="tableCustomerReceivables" style="min-width: 960px;">
                        <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem;">
                            <tr>
                                <th class="text-center ps-3" style="width: 42px;">No</th>
                                <th style="min-width: 220px;">Pelanggan / Nama PT</th>
                                <th class="text-end" style="min-width: 130px;">Total Faktur</th>
                                <th class="text-end" style="min-width: 110px;">Total DP</th>
                                <th class="text-end" style="min-width: 130px;">Pembayaran Masuk</th>
                                <th class="text-end" style="min-width: 130px;">Sisa Piutang</th>
                                <th class="text-center" style="min-width: 140px;">Status / Aging</th>
                                <th class="text-center pe-3" style="min-width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-body-emphasis">
                            <!-- Data loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════════
         MODAL DETAIL DRILLDOWN PIUTANG PELANGGAN
         ════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalDetailCustomerReceivable" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1300px; width: 95%;">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden bg-body">
                <div class="modal-header bg-body-tertiary border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                        <div class="ar-detail-icon flex-shrink-0">
                            <i class="bi bi-file-earmark-person-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis text-truncate fs-6 fs-sm-5" id="detail_customer_title">Kartu Piutang Pelanggan</h5>
                            <small class="text-body-secondary d-block font-monospace text-truncate" id="detail_customer_subtitle" style="font-size: 0.76rem;">-</small>
                        </div>
                    </div>
                    <button type="button" class="ar-btn-close flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close" title="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body p-3.5 p-md-4">
                    <!-- Customer Card Header -->
                    <div class="ar-customer-card mb-4">
                        <div class="row g-3.5 g-md-4 align-items-center">
                            <div class="col-12 col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1.5">
                                    <h5 class="fw-bold text-body-emphasis mb-0 fs-5" id="detail_nama_customer">-</h5>
                                </div>
                                <div class="text-primary fw-semibold small mb-2.5" id="detail_nama_perusahaan">-</div>
                                <div class="small text-body-secondary mb-1.5 text-break d-flex align-items-start gap-2">
                                    <i class="bi bi-geo-alt-fill text-danger mt-0.5 flex-shrink-0"></i>
                                    <span id="detail_alamat">-</span>
                                </div>
                                <div class="small text-body-secondary font-monospace d-flex align-items-center gap-2">
                                    <i class="bi bi-telephone-fill text-success flex-shrink-0"></i>
                                    <span id="detail_kontak">-</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="p-3.5 rounded-3 bg-body-tertiary border border-body-tertiary d-flex flex-column gap-2.5">
                                    <div class="d-flex justify-content-between align-items-center small text-body-secondary">
                                        <span>Syarat Pembayaran:</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold rounded-pill px-2.5 py-1 font-monospace" id="detail_term">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small text-body-secondary">
                                        <span>Batas Kredit (Limit):</span>
                                        <span class="fw-bold text-body-emphasis font-monospace" id="detail_credit_limit">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small pt-2.5 border-top">
                                        <span class="fw-semibold text-danger">Total Sisa Tagihan:</span>
                                        <span class="fw-bold text-danger font-monospace fs-5" id="detail_summary_sisa">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Badges Row (2x2 Grid di HP, 4 di Desktop) -->
                    <div class="row g-3 g-md-3.5 mb-4 text-center">
                        <div class="col-6 col-lg-3">
                            <div class="ar-mini-stat-card h-100 d-flex flex-column justify-content-center p-3">
                                <div class="text-body-secondary small text-truncate mb-1.5" style="font-size: 0.76rem;">Total Nilai Faktur</div>
                                <div class="fw-bold font-monospace text-body-emphasis text-truncate" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);" id="detail_kpi_invoiced">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="ar-mini-stat-card h-100 d-flex flex-column justify-content-center p-3">
                                <div class="text-body-secondary small text-truncate mb-1.5" style="font-size: 0.76rem;">Total Uang Muka (DP)</div>
                                <div class="fw-bold font-monospace text-info text-truncate" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);" id="detail_kpi_dp">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="ar-mini-stat-card h-100 d-flex flex-column justify-content-center p-3">
                                <div class="text-body-secondary small text-truncate mb-1.5" style="font-size: 0.76rem;">Pembayaran Masuk</div>
                                <div class="fw-bold font-monospace text-success text-truncate" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);" id="detail_kpi_paid">Rp 0</div>
                                <div class="font-monospace small text-body-secondary mt-1 d-none" id="detail_kpi_potongan_wrapper" style="font-size: 0.72rem;">
                                    + Diskon/Potongan: <span class="fw-bold text-danger" id="detail_kpi_potongan">Rp 0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="ar-mini-stat-card h-100 d-flex flex-column justify-content-center p-3">
                                <div class="text-body-secondary small text-truncate mb-1.5" style="font-size: 0.76rem;">Sisa Piutang</div>
                                <div class="fw-bold font-monospace text-danger text-truncate" style="font-size: clamp(0.95rem, 2.2vw, 1.2rem);" id="detail_kpi_sisa">Rp 0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs inside Modal (Scrollable di HP) -->
                    <ul class="nav nav-tabs ar-modal-tabs mb-3.5" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabUnpaidInvoices" type="button" role="tab">
                                <i class="bi bi-clock-history me-1 text-warning"></i> Belum Lunas (<span id="count_unpaid">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPaidInvoices" type="button" role="tab">
                                <i class="bi bi-check-circle me-1 text-success"></i> Riwayat Lunas (<span id="count_paid">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPaymentHistory" type="button" role="tab">
                                <i class="bi bi-receipt me-1 text-primary"></i> Pembayaran Masuk (<span id="count_history">0</span>)
                            </button>
                        </li>
                    </ul>

                    <!-- Mobile Swipe Hint Banner inside Detail Modal -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2.5 rounded-3 ar-mobile-swipe-banner shadow-2xs">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left-right text-success"></i>
                            <span class="fw-semibold small">Geser tabel rincian piutang ke samping</span>
                        </div>
                        <span class="badge rounded-pill px-2.5 py-1 fw-bold ar-swipe-pill">Swipe &rarr;</span>
                    </div>

                    <div class="tab-content">
                        <!-- Tab 1: Faktur Belum Lunas -->
                        <div class="tab-pane fade show active" id="tabUnpaidInvoices" role="tabpanel">
                            <div class="table-responsive rounded-3 border border-body-tertiary" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                                <table class="table table-hover align-middle mb-0 text-nowrap w-100" style="font-size: 0.82rem; min-width: 820px;">
                                    <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.74rem;">
                                        <tr>
                                            <th class="ps-3 py-2.5">No. Faktur</th>
                                            <th>Tgl Faktur</th>
                                            <th>Jatuh Tempo</th>
                                            <th class="text-end">Grand Total</th>
                                            <th class="text-end">Uang Muka (DP)</th>
                                            <th class="text-end">Pelunasan Masuk</th>
                                            <th class="text-end">Sisa Tagihan</th>
                                            <th class="text-center pe-3">Status / Aging</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_unpaid_invoices">
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Faktur Lunas -->
                        <div class="tab-pane fade" id="tabPaidInvoices" role="tabpanel">
                            <div class="table-responsive rounded-3 border border-body-tertiary" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                                <table class="table table-hover align-middle mb-0 text-nowrap w-100" style="font-size: 0.82rem; min-width: 750px;">
                                    <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.74rem;">
                                        <tr>
                                            <th class="ps-3 py-2.5">No. Faktur</th>
                                            <th>Tgl Faktur</th>
                                            <th>Jatuh Tempo</th>
                                            <th class="text-end">Grand Total</th>
                                            <th class="text-end">Uang Muka (DP)</th>
                                            <th class="text-end">Total Pelunasan</th>
                                            <th class="text-center pe-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_paid_invoices">
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 3: Riwayat Pembayaran Masuk -->
                        <div class="tab-pane fade" id="tabPaymentHistory" role="tabpanel">
                            <div class="table-responsive rounded-3 border border-body-tertiary" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                                <table class="table table-hover align-middle mb-0 text-nowrap w-100" style="font-size: 0.82rem; min-width: 880px;">
                                    <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.74rem;">
                                        <tr>
                                            <th class="ps-3 py-2.5">No. Pembayaran</th>
                                            <th>Tgl Bayar</th>
                                            <th>Akun Kas / Bank</th>
                                            <th>Metode</th>
                                            <th>Faktur Terkait</th>
                                            <th class="text-end">Kas Masuk</th>
                                            <th class="text-end">Potongan</th>
                                            <th class="text-end">Total Pelunasan</th>
                                            <th class="text-center pe-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_payment_history">
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-body-tertiary border-top py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">Tutup</button>
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('cetak-piutang'))
                    <a href="#" target="_blank" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1.5 shadow-2xs" id="btn_modal_print">
                        <i class="bi bi-printer-fill"></i> <span>Cetak Kartu Piutang</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // ── Select2 Filter Modal ──────────────────────────────────────
            $('#filter_customer_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari atau pilih nama pelanggan / PT...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalFilterPiutang')
            });

            // ── Sanitasi String untuk Mencegah DOM XSS ───────────────────
            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Format Rupiah Helper
            function formatRupiah(num) {
                let n = Number(num || 0);
                return 'Rp ' + n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // ── State Filter ──────────────────────────────────────────────
            let activeCustomerId   = '';
            let activeCustomerText = '';
            let activeAgingValue   = '';
            let activeAgingText    = '';

            // ── Aging Option Card: Visual Toggle ─────────────────────────
            $(document).on('click', '.aging-option-card', function() {
                $('.aging-option-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type=radio]').prop('checked', true);
            });

            // ── Update Filter Badge & Chips ───────────────────────────────
            function updateFilterUI() {
                let count = 0;
                const chips = [];

                if (activeCustomerId) {
                    count++;
                    chips.push(`<span class="filter-chip"><i class="bi bi-building"></i> ${activeCustomerText} <button class="chip-remove" data-clear="customer">&times;</button></span>`);
                }
                if (activeAgingValue) {
                    count++;
                    chips.push(`<span class="filter-chip"><i class="bi bi-bar-chart-steps"></i> ${activeAgingText} <button class="chip-remove" data-clear="aging">&times;</button></span>`);
                }

                const $badge = $('#filter_active_badge');
                if (count > 0) {
                    $badge.text(count).removeClass('d-none');
                    $('#btn_open_filter_modal').addClass('btn-filter-active');
                } else {
                    $badge.text(0).addClass('d-none');
                    $('#btn_open_filter_modal').removeClass('btn-filter-active');
                }
                $('#filter_active_chips').html(chips.join(''));
            }

            // ── Chip Remove Button ────────────────────────────────────────
            $(document).on('click', '.chip-remove', function() {
                const target = $(this).data('clear');
                if (target === 'customer') {
                    activeCustomerId   = '';
                    activeCustomerText = '';
                    $('#filter_customer_id').val('').trigger('change');
                }
                if (target === 'aging') {
                    activeAgingValue   = '';
                    activeAgingText    = '';
                    $('[name=filter_aging_radio][value=""]').prop('checked', true);
                    $('.aging-option-card').removeClass('selected');
                    $('.aging-option-card[data-value=""]').addClass('selected');
                }
                updateFilterUI();
                table.draw();
            });

            // ── Inisialisasi DataTables Server-Side ───────────────────────
            const table = $('#tableCustomerReceivables').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                autoWidth: false,
                scrollX: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: {
                    url: "{{ route('modul_marketing.piutang.index') }}",
                    data: function(d) {
                        d.customer_id  = activeCustomerId;
                        d.aging_filter = activeAgingValue;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'customer_info', name: 'customer_info' },
                    { data: 'total_invoiced', name: 'inv.total_invoiced', className: 'text-end' },
                    { data: 'total_dp', name: 'inv.total_dp', className: 'text-end' },
                    { data: 'total_paid', name: 'pay.total_payment_received', className: 'text-end' },
                    { data: 'sisa_piutang', name: 'inv.sisa_piutang', className: 'text-end' },
                    { data: 'aging_status', name: 'inv.max_overdue_days', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[5, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Cari pelanggan, PT, atau nomor telepon...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    emptyTable: "Belum ada riwayat faktur atau piutang",
                    processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data...'
                }
            });

            // Debounce input pencarian
            $('#tableCustomerReceivables_filter input').unbind().bind('input', function(e) {
                const val = $(this).val();
                clearTimeout(window.piutangSearchDebounceTimer);
                window.piutangSearchDebounceTimer = setTimeout(function() {
                    table.search(val).draw();
                }, 400);
            });

            // ── Apply Filter dari Modal ───────────────────────────────────
            $('#btn_apply_filter').on('click', function() {
                const $selOpt = $('#filter_customer_id option:selected');
                activeCustomerId   = $('#filter_customer_id').val() || '';
                activeCustomerText = activeCustomerId ? $selOpt.text().trim() : '';

                const $agingChecked = $('[name=filter_aging_radio]:checked');
                activeAgingValue   = $agingChecked.val() || '';
                activeAgingText    = activeAgingValue
                    ? $agingChecked.closest('.aging-option-card').find('.aging-option-text').text().trim()
                    : '';

                updateFilterUI();
                table.draw();
            });

            // ── Reset Filter ──────────────────────────────────────────────
            $('#btn_reset_filter').on('click', function() {
                activeCustomerId   = '';
                activeCustomerText = '';
                activeAgingValue   = '';
                activeAgingText    = '';
                $('#filter_customer_id').val('').trigger('change');
                $('[name=filter_aging_radio][value=""]').prop('checked', true);
                $('.aging-option-card').removeClass('selected');
                $('.aging-option-card[data-value=""]').addClass('selected');
                updateFilterUI();
                table.draw();
            });

            // Pre-select default aging card
            $('.aging-option-card[data-value=""]').addClass('selected');

            $('#btn_refresh_table').on('click', function() {
                table.draw();
            });

            // ── Table Scroll Shadow & Hint Logic ─────────────────────────
            const $wrapper = $('#table_scroll_wrapper');
            const $scrollEl = $wrapper.find('.table-responsive');
            const $hint = $('#table_scroll_hint');
            let hintDismissed = false;

            function checkTableScroll() {
                const el = $scrollEl[0];
                if (!el) return;
                const canRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 2;
                $wrapper.toggleClass('can-scroll-right', canRight);
                // Show hint only if can scroll and not dismissed
                if (canRight && !hintDismissed) {
                    $hint.css('display', 'flex');
                } else {
                    $hint.hide();
                }
            }

            $scrollEl.on('scroll', function() {
                if (!hintDismissed && this.scrollLeft > 10) {
                    hintDismissed = true;
                    $hint.fadeOut(300);
                }
                checkTableScroll();
            });

            // Check on draw complete
            table.on('draw', function() {
                setTimeout(checkTableScroll, 100);
            });

            $(window).on('resize', checkTableScroll);
            setTimeout(checkTableScroll, 500);

            // ════════════════════════════════════════════════════════════════
            // ACTION: DETAIL DRILLDOWN CUSTOMER MODAL
            // ════════════════════════════════════════════════════════════════
            $(document).on('click', '.btn-detail-customer', function() {
                const customerId = $(this).data('id');
                const detailUrl = "{{ url('modul-marketing/piutang/detail') }}/" + customerId;

                $('#tbody_unpaid_invoices').html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data faktur...</td></tr>');
                $('#tbody_paid_invoices').html('');
                $('#tbody_payment_history').html('');

                $.ajax({
                    url: detailUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (!res.success) return;

                        const c = res.customer;
                        const s = res.summary;

                        // Header Info
                        $('#detail_customer_title').text('Kartu Piutang: ' + (c.nama_perusahaan || c.nama_customer));
                        $('#detail_customer_subtitle').text('[' + c.kode_customer + '] ' + c.nama_customer);
                        $('#detail_nama_customer').text(c.nama_customer);
                        $('#detail_nama_perusahaan').text(c.nama_perusahaan || '-');
                        $('#detail_alamat').text(c.alamat || '-');
                        $('#detail_kontak').text((c.hp || c.telepon || '-') + (c.email ? ' | ' + c.email : ''));
                        $('#detail_term').text(c.payment_term);
                        $('#detail_credit_limit').text(formatRupiah(c.credit_limit));
                        $('#detail_summary_sisa').text(formatRupiah(s.sisa_piutang));

                        // Summary Badges
                        $('#detail_kpi_invoiced').text(formatRupiah(s.total_invoiced));
                        $('#detail_kpi_dp').text(formatRupiah(s.total_dp));
                        $('#detail_kpi_paid').text(formatRupiah(s.total_paid));
                        $('#detail_kpi_sisa').text(formatRupiah(s.sisa_piutang));

                        if (s.total_potongan > 0) {
                            $('#detail_kpi_potongan').text(formatRupiah(s.total_potongan));
                            $('#detail_kpi_potongan_wrapper').removeClass('d-none');
                        } else {
                            $('#detail_kpi_potongan_wrapper').addClass('d-none');
                        }

                        // Counts
                        $('#count_unpaid').text(res.unpaid_invoices.length);
                        $('#count_paid').text(res.paid_invoices.length);
                        $('#count_history').text(res.payment_history.length);

                        // Actions URLs
                        $('#btn_modal_print').attr('href', "{{ url('modul-marketing/piutang/print-statement') }}/" + c.id);

                        // Render Unpaid Invoices (Secured with escapeHtml)
                        let htmlUnpaid = '';
                        if (res.unpaid_invoices.length === 0) {
                            htmlUnpaid = '<tr><td colspan="8" class="text-center py-4 text-success"><i class="bi bi-check-circle-fill me-1"></i> Semua faktur telah lunas diselesaikan!</td></tr>';
                        } else {
                            res.unpaid_invoices.forEach(function(inv) {
                                let badgeAging = '';
                                if (inv.is_overdue) {
                                    badgeAging = '<span class="badge bg-danger-subtle text-danger font-monospace border border-danger-subtle"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat ' + parseInt(inv.days_overdue || 0) + ' Hari</span>';
                                } else {
                                    badgeAging = '<span class="badge bg-primary-subtle text-primary font-monospace border border-primary-subtle"><i class="bi bi-clock me-1"></i>Belum Tempo</span>';
                                }

                                htmlUnpaid += '<tr>'
                                    + '<td class="fw-bold font-monospace text-primary">' + escapeHtml(inv.nomor_faktur) + '</td>'
                                    + '<td>' + escapeHtml(inv.tanggal_faktur) + '</td>'
                                    + '<td class="font-monospace">' + escapeHtml(inv.tanggal_jatuh_tempo) + '</td>'
                                    + '<td class="text-end font-monospace">' + formatRupiah(inv.grand_total) + '</td>'
                                    + '<td class="text-end font-monospace text-info">' + formatRupiah(inv.uang_muka) + '</td>'
                                    + '<td class="text-end font-monospace text-success">' + formatRupiah(inv.paid_amount) + '</td>'
                                    + '<td class="text-end font-monospace fw-bold text-danger">' + formatRupiah(inv.sisa_tagihan) + '</td>'
                                    + '<td class="text-center">' + badgeAging + '</td>'
                                    + '</tr>';
                            });
                        }
                        $('#tbody_unpaid_invoices').html(htmlUnpaid);

                        // Render Paid Invoices (Secured with escapeHtml)
                        let htmlPaid = '';
                        if (res.paid_invoices.length === 0) {
                            htmlPaid = '<tr><td colspan="7" class="text-center py-3 text-muted">Belum ada riwayat faktur yang lunas sebelumnya.</td></tr>';
                        } else {
                            res.paid_invoices.forEach(function(inv) {
                                htmlPaid += '<tr>'
                                    + '<td class="fw-semibold font-monospace">' + escapeHtml(inv.nomor_faktur) + '</td>'
                                    + '<td>' + escapeHtml(inv.tanggal_faktur) + '</td>'
                                    + '<td class="font-monospace">' + escapeHtml(inv.tanggal_jatuh_tempo) + '</td>'
                                    + '<td class="text-end font-monospace">' + formatRupiah(inv.grand_total) + '</td>'
                                    + '<td class="text-end font-monospace">' + formatRupiah(inv.uang_muka) + '</td>'
                                    + '<td class="text-end font-monospace text-success fw-bold">' + formatRupiah(inv.paid_amount) + '</td>'
                                    + '<td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2-all me-1"></i>Lunas</span></td>'
                                    + '</tr>';
                            });
                        }
                        $('#tbody_paid_invoices').html(htmlPaid);

                        // Render Payment History (Secured with escapeHtml)
                        let htmlHistory = '';
                        if (res.payment_history.length === 0) {
                            htmlHistory = '<tr><td colspan="9" class="text-center py-3 text-muted">Belum ada riwayat pembayaran masuk dari pelanggan ini.</td></tr>';
                        } else {
                            res.payment_history.forEach(function(p) {
                                let badgeStatus = '';
                                if (p.status === 'Posted') {
                                    badgeStatus = '<span class="badge bg-success-subtle text-success border border-success-subtle">Posted</span>';
                                } else if (p.status === 'Diajukan') {
                                    badgeStatus = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Diajukan</span>';
                                } else {
                                    badgeStatus = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Draft</span>';
                                }

                                let potonganHtml = p.total_potongan > 0 
                                    ? '<span class="text-danger fw-semibold">' + formatRupiah(p.total_potongan) + '</span>'
                                    : '<span class="text-muted">-</span>';

                                htmlHistory += '<tr>'
                                    + '<td class="fw-bold font-monospace text-primary">' + escapeHtml(p.nomor_pembayaran) + '</td>'
                                    + '<td>' + escapeHtml(p.tanggal_bayar) + '</td>'
                                    + '<td>' + escapeHtml(p.kas_bank) + '</td>'
                                    + '<td>' + escapeHtml(p.metode_bayar) + '</td>'
                                    + '<td><small class="text-muted">' + (p.faktur_list ? escapeHtml(p.faktur_list) : '-') + '</small></td>'
                                    + '<td class="text-end font-monospace fw-bold text-success">' + formatRupiah(p.total_bayar) + '</td>'
                                    + '<td class="text-end font-monospace">' + potonganHtml + '</td>'
                                    + '<td class="text-end font-monospace fw-bold text-body-emphasis">' + formatRupiah(p.total_alokasi) + '</td>'
                                    + '<td class="text-center">' + badgeStatus + '</td>'
                                    + '</tr>';
                            });
                        }
                        $('#tbody_payment_history').html(htmlHistory);

                        // Buka Modal
                        $('#modalDetailCustomerReceivable').modal('show');
                    },
                    error: function() {
                        alert('Gagal mengambil data rincian piutang pelanggan!');
                    }
                });
            });

            // Selesai Inisialisasi
        });
    </script>
    @endpush
</x-layouts>
