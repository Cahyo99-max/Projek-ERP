<x-layouts title="Pelacakan Serial Number (SN Tracker) - Modul Produksi">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<!-- Select2 CSS with Bootstrap 5 Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* ================================================================
       THEME ADAPTIVE DESIGN: PRO-GRADE LIGHT & DARK MODE SUPPORT
       ================================================================ */
    :root {
        --sn-card-bg: #ffffff;
        --sn-card-border: #e2e8f0;
        --sn-text-main: #0f172a;
        --sn-text-sub: #64748b;
        --sn-table-th-bg: #f1f5f9;
        --sn-table-th-color: #1e293b;
        --sn-table-td-bg: #ffffff;
        --sn-table-hover: rgba(37, 99, 235, 0.06);
        --sn-input-bg: #ffffff;
        --sn-input-border: #cbd5e1;
        --sn-input-color: #0f172a;
    }

    [data-bs-theme="dark"],
    html[data-bs-theme="dark"],
    .dark-mode {
        --sn-card-bg: #1e293b;
        --sn-card-border: #334155;
        --sn-text-main: #f8fafc;
        --sn-text-sub: #94a3b8;
        --sn-table-th-bg: #0f172a;
        --sn-table-th-color: #60a5fa;
        --sn-table-td-bg: #1e293b;
        --sn-table-hover: rgba(59, 130, 246, 0.15);
        --sn-input-bg: #0f172a;
        --sn-input-border: #334155;
        --sn-input-color: #f8fafc;
    }

    /* KPI STAT CARDS */
    .kpi-stat-card {
        background-color: var(--sn-card-bg) !important;
        border: 1px solid var(--sn-card-border) !important;
        box-shadow: 0 10px 25px -8px rgba(0, 0, 0, 0.12);
        border-radius: 18px;
        padding: 1.2rem 1.35rem;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .kpi-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.25);
        border-color: var(--kpi-glow, #3b82f6) !important;
    }
    .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--kpi-glow, #3b82f6), transparent);
    }
    .kpi-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        border: 1px solid var(--sn-card-border);
    }

    /* HERO SCANNER CARD */
    .hero-scanner-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);
        color: #ffffff;
        border-radius: 20px;
        padding: 1.6rem 2rem;
        box-shadow: 0 16px 36px -10px rgba(30, 58, 138, 0.45);
        border: 1px solid rgba(59, 130, 246, 0.35);
        position: relative;
        overflow: hidden;
    }
    .hero-scanner-card::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .scanner-input-group {
        background: var(--sn-input-bg);
        border: 1.5px solid rgba(59, 130, 246, 0.4);
        border-radius: 50px;
        padding: 5px 6px 5px 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        transition: all 0.2s ease;
    }
    .scanner-input-group:focus-within {
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.25), 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    .scanner-input-group input {
        border: none;
        background: transparent !important;
        outline: none;
        box-shadow: none;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--sn-input-color) !important;
    }
    .scanner-input-group input::placeholder {
        color: var(--sn-text-sub);
        font-weight: 500;
    }
    .btn-clear-hero {
        background: transparent;
        border: none;
        color: var(--sn-text-sub);
        cursor: pointer;
        padding: 0 6px;
        font-size: 1.15rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .btn-clear-hero:hover {
        color: #ef4444 !important;
        transform: scale(1.1);
    }

    /* SCANNER MODAL & OVERLAY STYLING */
    .scanner-viewport-wrapper {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        background: #000000;
        min-height: 260px;
    }
    #tracking_scanner_reader {
        width: 100% !important;
        border: none !important;
    }
    #tracking_scanner_reader video {
        width: 100% !important;
        height: auto !important;
        border-radius: 16px;
        object-fit: cover;
    }
    .scanner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .scanner-target-box {
        position: relative;
        width: 80%;
        height: 70%;
        max-width: 300px;
        max-height: 200px;
        border-radius: 12px;
        box-shadow: 0 0 0 9999px rgba(9, 13, 22, 0.55);
        transition: box-shadow 0.2s ease;
    }
    .scanner-target-box::before,
    .scanner-target-box::after {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        border-color: #22c55e;
        border-style: solid;
        pointer-events: none;
    }
    .scanner-target-box::before {
        top: -2px;
        left: -2px;
        border-width: 4px 0 0 4px;
        border-top-left-radius: 8px;
        filter: drop-shadow(0 0 6px #22c55e);
    }
    .scanner-target-box::after {
        bottom: -2px;
        right: -2px;
        border-width: 0 4px 4px 0;
        border-bottom-right-radius: 8px;
        filter: drop-shadow(0 0 6px #22c55e);
    }
    .scanner-corner-tr {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 24px;
        height: 24px;
        border-color: #22c55e;
        border-style: solid;
        border-width: 4px 4px 0 0;
        border-top-right-radius: 8px;
        filter: drop-shadow(0 0 6px #22c55e);
    }
    .scanner-corner-bl {
        position: absolute;
        bottom: -2px;
        left: -2px;
        width: 24px;
        height: 24px;
        border-color: #22c55e;
        border-style: solid;
        border-width: 0 0 4px 4px;
        border-bottom-left-radius: 8px;
        filter: drop-shadow(0 0 6px #22c55e);
    }
    .scanner-laser-line {
        position: absolute;
        top: 0;
        left: 5%;
        right: 5%;
        height: 2.5px;
        background: linear-gradient(90deg, rgba(34, 197, 94, 0) 0%, #22c55e 50%, rgba(34, 197, 94, 0) 100%);
        box-shadow: 0 0 12px 3px rgba(34, 197, 94, 0.85);
        border-radius: 50%;
        animation: trackingScannerLaser 2s infinite ease-in-out alternate;
    }
    @keyframes trackingScannerLaser {
        0% { top: 6%; opacity: 0.3; }
        50% { opacity: 1; }
        100% { top: 92%; opacity: 0.3; }
    }
    .scanner-center-dot {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 8px 3px rgba(34, 197, 94, 0.85);
    }
    .scanner-guide-dots {
        position: absolute;
        width: 5px;
        height: 5px;
        background: #4ade80;
        border-radius: 50%;
        box-shadow: 0 0 5px #22c55e;
    }
    .scanner-dot-left { top: 50%; left: 12px; transform: translateY(-50%); }
    .scanner-dot-right { top: 50%; right: 12px; transform: translateY(-50%); }
    .scanner-dot-top { top: 12px; left: 50%; transform: translateX(-50%); }
    .scanner-dot-bottom { bottom: 12px; left: 50%; transform: translateX(-50%); }

    /* ACTION BAR CARD */
    .action-glass-bar {
        background-color: var(--sn-card-bg) !important;
        border: 1px solid var(--sn-card-border) !important;
        border-radius: 18px;
        box-shadow: 0 6px 20px -6px rgba(0, 0, 0, 0.08);
        padding: 0.9rem 1.25rem;
    }

    /* ACTIVE FILTER CHIPS */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 50px;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.3);
        transition: all 0.2s ease;
    }
    [data-bs-theme="dark"] .filter-chip,
    .dark-mode .filter-chip {
        background: rgba(59, 130, 246, 0.18);
        color: #93c5fd;
        border-color: rgba(59, 130, 246, 0.4);
    }
    .filter-chip .btn-remove-chip {
        cursor: pointer;
        font-size: 13px;
        opacity: 0.75;
        transition: opacity 0.15s;
    }
    .filter-chip .btn-remove-chip:hover {
        opacity: 1;
        color: #ef4444;
    }

    /* TABLE GLASS CARD */
    .table-glass-card {
        background-color: var(--sn-card-bg) !important;
        border: 1px solid var(--sn-card-border) !important;
        box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.25);
        border-radius: 20px;
        overflow: hidden;
    }

    /* THEMED TABLE STYLING */
    #table_tracking_sn {
        color: var(--sn-text-main) !important;
        background-color: var(--sn-table-td-bg) !important;
    }
    #table_tracking_sn thead th {
        background-color: var(--sn-table-th-bg) !important;
        color: var(--sn-table-th-color) !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        border-bottom: 2px solid var(--sn-card-border) !important;
        padding: 13px 14px !important;
    }
    #table_tracking_sn tbody td {
        background-color: var(--sn-table-td-bg) !important;
        color: var(--sn-text-main) !important;
        padding: 11px 14px !important;
        border-color: var(--sn-card-border) !important;
        vertical-align: middle !important;
    }
    #table_tracking_sn tbody tr:hover td {
        background-color: var(--sn-table-hover) !important;
    }

    /* VIBRANT HIGH CONTRAST BADGES */
    .sn-code-badge {
        font-size: 11.5px;
        letter-spacing: 0.5px;
        padding: 4px 9px;
        border-radius: 6px;
        font-weight: 700;
    }
    [data-bs-theme="dark"] .sn-code-badge,
    .dark-mode .sn-code-badge {
        background: rgba(59, 130, 246, 0.18) !important;
        color: #93c5fd !important;
        border: 1px solid rgba(59, 130, 246, 0.45) !important;
    }
    [data-bs-theme="light"] .sn-code-badge {
        background: #eff6ff !important;
        color: #1e40af !important;
        border: 1px solid #bfdbfe !important;
    }

    .batch-code-badge {
        font-size: 11px;
        padding: 3.5px 9px;
        border-radius: 6px;
        font-weight: 700;
    }
    [data-bs-theme="dark"] .batch-code-badge,
    .dark-mode .batch-code-badge {
        background: rgba(245, 158, 11, 0.16) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.35) !important;
    }
    [data-bs-theme="light"] .batch-code-badge {
        background: #fffbeb !important;
        color: #b45309 !important;
        border: 1px solid #fde68a !important;
    }

    /* STATUS PILLS */
    .status-pill-tersedia {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
    }
    [data-bs-theme="dark"] .status-pill-tersedia,
    .dark-mode .status-pill-tersedia {
        background: rgba(16, 185, 129, 0.2) !important;
        color: #34d399 !important;
        border: 1px solid rgba(52, 211, 153, 0.45) !important;
    }
    [data-bs-theme="light"] .status-pill-tersedia {
        background: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #86efac !important;
    }

    .status-pill-terjual {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
    }
    [data-bs-theme="dark"] .status-pill-terjual,
    .dark-mode .status-pill-terjual {
        background: rgba(99, 102, 241, 0.2) !important;
        color: #a5b4fc !important;
        border: 1px solid rgba(165, 180, 252, 0.45) !important;
    }
    [data-bs-theme="light"] .status-pill-terjual {
        background: #e0e7ff !important;
        color: #4338ca !important;
        border: 1px solid #c7d2fe !important;
    }

    .status-pill-rusak {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
    }
    [data-bs-theme="dark"] .status-pill-rusak,
    .dark-mode .status-pill-rusak {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #f87171 !important;
        border: 1px solid rgba(248, 113, 113, 0.45) !important;
    }
    [data-bs-theme="light"] .status-pill-rusak {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fca5a5 !important;
    }

    .status-pill-other {
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: 700;
        font-size: 11px;
        background: rgba(148, 163, 184, 0.15) !important;
        color: var(--sn-text-sub) !important;
    }

    /* ACTION BUTTON */
    .btn-track-action {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        border-radius: 50px !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        font-size: 11px !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }
    .btn-track-action:hover {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4) !important;
    }

    /* TEXT HELPERS */
    .item-title-text { color: var(--sn-text-main) !important; }
    .item-sub-text   { color: var(--sn-text-sub) !important; }
    .spk-num-text    { color: #2563eb !important; }
    [data-bs-theme="dark"] .spk-num-text,
    .dark-mode .spk-num-text { color: #38bdf8 !important; }
    .rak-sub-text    { color: #0284c7 !important; }
    [data-bs-theme="dark"] .rak-sub-text,
    .dark-mode .rak-sub-text { color: #38bdf8 !important; }

    /* MODAL FILTER MODERN STYLING */
    .filter-input-icon-group {
        position: relative;
    }
    .filter-input-icon-group .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #3b82f6;
        font-size: 1.05rem;
        z-index: 4;
        pointer-events: none;
    }
    .filter-input-icon-group .form-control {
        padding-left: 42px !important;
        background-color: var(--sn-input-bg) !important;
        border-color: var(--sn-input-border) !important;
        color: var(--sn-input-color) !important;
        border-radius: 12px;
        font-size: 13px;
        height: 42px;
    }
    .filter-input-icon-group .form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
    }

    /* SELECT2 CUSTOM & SEARCHABLE STYLING (DARK & LIGHT THEME) */
    .filter-input-icon-group .select2-container--bootstrap-5 {
        width: 100% !important;
    }
    .filter-input-icon-group .select2-container--bootstrap-5 .select2-selection {
        background-color: var(--sn-input-bg) !important;
        border: 1px solid var(--sn-input-border) !important;
        color: var(--sn-input-color) !important;
        border-radius: 12px !important;
        min-height: 42px !important;
        padding: 6px 12px 6px 42px !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease;
    }
    .filter-input-icon-group .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .filter-input-icon-group .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
    }
    .filter-input-icon-group .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--sn-input-color) !important;
        padding-left: 0 !important;
        font-size: 13px !important;
        font-weight: 500;
    }
    .select2-dropdown {
        background-color: var(--sn-card-bg) !important;
        border: 1px solid var(--sn-card-border) !important;
        border-radius: 14px !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35) !important;
        z-index: 1060 !important;
        overflow: hidden !important;
    }
    .select2-container--bootstrap-5 .select2-search {
        padding: 8px 10px !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        background-color: var(--sn-input-bg) !important;
        border: 1px solid var(--sn-input-border) !important;
        color: var(--sn-input-color) !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        font-size: 12.5px !important;
    }
    .select2-container--bootstrap-5 .select2-search__field:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        outline: none !important;
    }
    .select2-container--bootstrap-5 .select2-results__options {
        max-height: 220px !important;
        overflow-y: auto !important;
        padding: 4px !important;
    }
    .select2-container--bootstrap-5 .select2-results__option {
        color: var(--sn-text-main) !important;
        font-size: 12.5px !important;
        padding: 8px 12px !important;
        border-radius: 8px !important;
        margin-bottom: 2px !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--selected {
        background-color: rgba(37, 99, 235, 0.2) !important;
        color: #38bdf8 !important;
        font-weight: 700 !important;
    }

    /* ================================================================
       MOBILE RESPONSIVENESS OPTIMIZATION (PRO GRADE)
       ================================================================ */
    @media (max-width: 767.98px) {
        .tracking-header-title {
            font-size: 1.15rem !important;
        }
        .hero-scanner-card {
            padding: 1.15rem 1rem !important;
            border-radius: 16px !important;
        }
        .hero-scanner-card h5 {
            font-size: 1.05rem !important;
        }
        .scanner-input-group {
            padding: 4px 6px 4px 10px !important;
            border-radius: 30px !important;
        }
        .scanner-input-group input {
            font-size: 12.5px !important;
            padding: 4px 6px !important;
        }
        .scanner-input-group .btn {
            font-size: 11.5px !important;
            padding: 5px 10px !important;
        }
        .kpi-stat-card {
            padding: 0.75rem 0.85rem !important;
            border-radius: 14px !important;
        }
        .kpi-stat-card h4 {
            font-size: 1.25rem !important;
        }
        .kpi-stat-card .text-uppercase {
            font-size: 9.5px !important;
            letter-spacing: 0.2px !important;
        }
        .kpi-icon-wrapper {
            width: 36px !important;
            height: 36px !important;
            border-radius: 10px !important;
            font-size: 1rem !important;
        }
        .action-glass-bar {
            padding: 10px 12px !important;
            border-radius: 14px !important;
        }
        .action-bar-mobile-stack {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }
        .action-bar-mobile-btn-group {
            width: 100% !important;
            display: flex !important;
            gap: 8px !important;
            margin-left: 0 !important;
        }
        .action-bar-mobile-btn-group .btn {
            flex: 1 !important;
            justify-content: center !important;
            padding: 6px 10px !important;
            font-size: 11.5px !important;
        }
        .table-glass-card {
            border-radius: 14px !important;
        }
        .table-glass-card .table-responsive {
            padding: 10px !important;
            -webkit-overflow-scrolling: touch;
        }
        #table_tracking_sn thead th {
            padding: 9px 10px !important;
            font-size: 10.5px !important;
            white-space: nowrap;
        }
        #table_tracking_sn tbody td {
            padding: 8px 10px !important;
            font-size: 11.5px !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
            text-align: left !important;
            margin-bottom: 8px !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            margin-top: 4px !important;
        }
    }

</style>
@endpush

<div class="content-wrapper py-3 px-2 px-md-4">
    
    {{-- HEADER TITLE --}}
    <div class="d-flex flex-row justify-content-between align-items-center gap-2 mb-3 mb-md-4">
        <div class="min-w-0">
            <h4 class="fw-extrabold mb-0.5 d-flex align-items-center gap-2 tracking-header-title" style="color: var(--sn-text-main);">
                <i class="bi bi-upc-scan text-primary"></i>
                <span class="text-truncate">Pelacakan Serial Number</span>
            </h4>
            <p class="text-muted small mb-0 d-none d-sm-block">Lacak riwayat perjalanan lengkap unit barang jadi dari SPK, Penerimaan Gudang, hingga Distribusi.</p>
            <p class="text-muted fs-9 mb-0 d-block d-sm-none">Lacak riwayat lengkap unit dari SPK hingga Distribusi.</p>
        </div>
        <div class="flex-shrink-0">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2.5 px-md-3 py-1.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="reloadTable()" title="Segarkan Data Tabel">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="d-none d-sm-inline">Segarkan Data</span>
            </button>
        </div>
    </div>

    {{-- HERO SCANNER BARCODE & SEARCH BOX (SN, LOT/LOD, KODE BARANG) --}}
    <div class="hero-scanner-card mb-3 mb-md-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-2.5 py-1 rounded-pill fs-9 fw-bold">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Instant Search &amp; Scan
                    </span>
                </div>
                <h5 class="fw-extrabold mb-1 text-white">Lacak Rekam Jejak Unit</h5>
                <p class="text-white text-opacity-75 small mb-0 d-none d-sm-block">Mendukung pencarian instan via <strong>Nomor SN</strong>, <strong>No. Lot / Batch (LOD)</strong>, atau <strong>Kode Barang</strong>:</p>
                <p class="text-white text-opacity-75 fs-9 mb-0 d-block d-sm-none">Cari via <strong>SN</strong>, <strong>Lot/Batch</strong>, atau <strong>Kode Barang</strong>:</p>
            </div>
            <div class="col-lg-6">
                <form id="form_hero_search" onsubmit="handleHeroSearch(event)">
                    <div class="scanner-input-group d-flex align-items-center gap-1.5">
                        <i class="bi bi-upc text-primary fs-5 ms-1 d-none d-sm-inline"></i>
                        <input type="text" id="input_hero_sn" class="form-control font-monospace flex-grow-1" placeholder="Ketik No. SN / Lot / Kode..." autofocus autocomplete="off" style="min-width: 0;">
                        <button type="button" id="btn_clear_hero" class="btn-clear-hero" style="display: none;" onclick="clearHeroSearch()" title="Hapus Pencarian">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1.5 fw-bold text-nowrap d-flex align-items-center gap-1 shadow-xs flex-shrink-0" onclick="openQrScannerModal()" title="Pindai Barcode / QR Code">
                            <i class="bi bi-qr-code-scan"></i>
                            <span class="d-none d-sm-inline fs-8">Scan QR</span>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold text-nowrap d-flex align-items-center gap-1 shadow-sm flex-shrink-0">
                            <i class="bi bi-search"></i>
                            <span>Lacak</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- KPI METRICS CARDS --}}
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card" style="--kpi-glow: #3b82f6;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <span class="text-muted text-uppercase d-block mb-1 text-truncate" style="font-size: 10px; font-weight: 700;">Total SN Terdaftar</span>
                        <h4 class="fw-extrabold mb-0 font-monospace text-primary">{{ number_format($totalSn, 0, ',', '.') }}</h4>
                        <small class="text-muted fs-9 d-block text-truncate">Unit di ERP</small>
                    </div>
                    <div class="kpi-icon-wrapper bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card" style="--kpi-glow: #10b981;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <span class="text-muted text-uppercase d-block mb-1 text-truncate" style="font-size: 10px; font-weight: 700;">Tersedia di Gudang</span>
                        <h4 class="fw-extrabold mb-0 font-monospace text-success">{{ number_format($totalTersedia, 0, ',', '.') }}</h4>
                        <small class="text-success fs-9 d-block text-truncate">Siap Distribusi</small>
                    </div>
                    <div class="kpi-icon-wrapper bg-success bg-opacity-10 text-success flex-shrink-0">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card" style="--kpi-glow: #6366f1;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <span class="text-muted text-uppercase d-block mb-1 text-truncate" style="font-size: 10px; font-weight: 700;">Terjual / Keluar</span>
                        <h4 class="fw-extrabold mb-0 font-monospace" style="color: #818cf8;">{{ number_format($totalTerjual, 0, ',', '.') }}</h4>
                        <small class="text-muted fs-9 d-block text-truncate">Keluar Gudang</small>
                    </div>
                    <div class="kpi-icon-wrapper bg-info bg-opacity-10 text-info flex-shrink-0">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card" style="--kpi-glow: #ef4444;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <span class="text-muted text-uppercase d-block mb-1 text-truncate" style="font-size: 10px; font-weight: 700;">Rusak / Reject</span>
                        <h4 class="fw-extrabold mb-0 font-monospace text-danger">{{ number_format($totalRusak, 0, ',', '.') }}</h4>
                        <small class="text-danger fs-9 d-block text-truncate">Kondisi Rusak</small>
                    </div>
                    <div class="kpi-icon-wrapper bg-danger bg-opacity-10 text-danger flex-shrink-0">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTION BAR: INDIKATOR ENTITAS OTOMATIS & TOMBOL FILTER MODAL --}}
    <div class="action-glass-bar mb-3 mb-md-4">
        <div class="d-flex justify-content-between align-items-center gap-2 action-bar-mobile-stack">
            
            {{-- INDIKATOR ENTITAS AKTIF OTOMATIS SESUAI SESI USER --}}
            <div class="d-flex align-items-center gap-2">
                @if($isBranchSession)
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill border" style="background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.3) !important;">
                        <span class="spinner-grow spinner-grow-sm text-primary" style="width: 8px; height: 8px;" role="status"></span>
                        <i class="bi bi-shop text-purple" style="color: #a855f7;"></i>
                        <span class="fs-8 fw-bold item-title-text">Sesi Cabang: <span class="text-primary">{{ $entityName }}</span></span>
                    </div>
                @else
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill border" style="background: rgba(14, 165, 233, 0.12); border-color: rgba(14, 165, 233, 0.3) !important;">
                        <span class="spinner-grow spinner-grow-sm text-info" style="width: 8px; height: 8px;" role="status"></span>
                        <i class="bi bi-building text-info"></i>
                        <span class="fs-8 fw-bold item-title-text">Sesi PT Pusat: <span class="text-info">{{ $entityName }}</span></span>
                    </div>
                @endif
            </div>

            {{-- TOMBOL FILTER MODAL, RESET & EXPORT EXCEL --}}
            <div class="d-flex align-items-center gap-2 ms-auto action-bar-mobile-btn-group">
                <button type="button" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="exportFilteredExcel()" title="Unduh Data Serial Number ke Format Excel (Sesuai Filter)">
                    <i class="bi bi-file-earmark-excel-fill text-success"></i>
                    <span>Export Excel</span>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" data-bs-toggle="modal" data-bs-target="#modalFilterSN">
                    <i class="bi bi-sliders2-vertical"></i>
                    <span>Filter Data</span>
                    <span class="badge bg-primary text-white rounded-pill ms-0.5 fs-9" id="filter_active_count" style="display: none;">0</span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5" id="btn_reset_filter" onclick="resetFilter()" style="display: none;">
                    <i class="bi bi-x-circle"></i>
                    <span>Reset</span>
                </button>
            </div>

        </div>

        {{-- ACTIVE FILTER CHIPS / PILLS CONTAINER --}}
        <div id="active_chips_container" class="mt-2.5 pt-2 border-top d-flex flex-wrap align-items-center gap-2" style="display: none !important; border-color: var(--sn-card-border) !important;">
            <small class="item-sub-text fs-9 fw-bold me-1">Filter Aktif:</small>
            <div id="active_chips_list" class="d-flex flex-wrap align-items-center gap-2"></div>
        </div>
    </div>

    {{-- DATA TABLE CONTAINER --}}
    <div class="table-glass-card position-relative">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle w-100 mb-0" id="table_tracking_sn" style="font-size: 12.5px;">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th style="width: 170px;">Serial Number</th>
                        <th>Barang Jadi</th>
                        <th style="width: 140px;">No. Batch</th>
                        <th style="width: 150px;">SPK Produksi</th>
                        <th style="width: 180px;">Gudang &amp; Rak</th>
                        <th style="width: 110px;">Tgl Terima</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                        <th style="width: 110px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Diisi oleh DataTables Server-Side AJAX --}}
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL FILTER MODERN DENGAN SEARCHABLE SELECT2 (OTOMATIS SESUAI SESI) --}}
<div class="modal fade" id="modalFilterSN" tabindex="-1" aria-labelledby="modalFilterSNLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden" style="background: var(--sn-card-bg, #ffffff); color: var(--sn-text-main, #0f172a);">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.12) 0%, rgba(59, 130, 246, 0.05) 100%); border-color: var(--sn-card-border, #e2e8f0) !important;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-sliders2-vertical fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-extrabold mb-0" id="modalFilterSNLabel" style="color: var(--sn-text-main, #0f172a);">
                            Filter Lanjutan Data Serial Number
                        </h6>
                        <small class="item-sub-text fs-8">Parameter pencarian otomatis untuk <strong>{{ $entityName }}</strong></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-4">
                <form id="form_filter_sn" onsubmit="applyModalFilter(event)">
                    <div class="row g-3">
                        
                        {{-- 1. PRODUK BARANG JADI (SEARCHABLE SELECT2) --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-box-seam text-primary me-1"></i> Produk Barang Jadi
                            </label>
                            <div class="filter-input-icon-group">
                                <i class="bi bi-box-seam input-icon"></i>
                                <select id="filter_barang_jadi" class="form-select select2-modal-filter" data-placeholder="-- Cari Produk Jadi --">
                                    <option value="">-- Semua Produk Jadi --</option>
                                    @foreach($barangJadis as $bj)
                                        <option value="{{ $bj->id }}">[{{ $bj->kode_barang }}] {{ $bj->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 2. STATUS UNIT (SEARCHABLE SELECT2) --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-shield-check text-primary me-1"></i> Status Unit SN
                            </label>
                            <div class="filter-input-icon-group">
                                <i class="bi bi-shield-check input-icon"></i>
                                <select id="filter_status" class="form-select select2-modal-filter" data-placeholder="-- Cari Status Unit --">
                                    <option value="">-- Semua Status Unit --</option>
                                    <option value="Tersedia">🟢 Tersedia (In Stock)</option>
                                    <option value="Terjual">🔵 Terjual / Terdistribusi</option>
                                    <option value="Rusak">🔴 Rusak / Reject / RMA</option>
                                </select>
                            </div>
                        </div>

                        {{-- 3. GUDANG PENYIMPANAN (SEARCHABLE SELECT2) --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-building text-primary me-1"></i> Gudang Penyimpanan
                            </label>
                            <div class="filter-input-icon-group">
                                <i class="bi bi-building input-icon"></i>
                                <select id="filter_gudang" class="form-select select2-modal-filter" data-placeholder="-- Cari Gudang --">
                                    <option value="">-- Semua Gudang --</option>
                                    @foreach($gudangs as $gd)
                                        <option value="{{ $gd->id }}">[{{ $gd->kode_gudang }}] {{ $gd->nama_gudang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 4. NOMOR BATCH / LOT --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-tag text-primary me-1"></i> Nomor Batch / Lot
                            </label>
                            <div class="filter-input-icon-group">
                                <i class="bi bi-tag input-icon"></i>
                                <input type="text" id="filter_batch" class="form-control font-monospace" placeholder="Contoh: PAS-TAS-2026-09">
                            </div>
                        </div>

                        {{-- 5. SPK PRODUKSI (SEARCHABLE SELECT2) --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-file-earmark-text text-primary me-1"></i> SPK Produksi
                            </label>
                            <div class="filter-input-icon-group">
                                <i class="bi bi-file-earmark-text input-icon"></i>
                                <select id="filter_spk" class="form-select select2-modal-filter" data-placeholder="-- Cari SPK --">
                                    <option value="">-- Semua SPK Produksi --</option>
                                    @foreach($spks as $spk)
                                        <option value="{{ $spk->id }}">{{ $spk->nomor_spk }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 6. RENTANG TANGGAL PENERIMAAN --}}
                        <div class="col-md-6">
                            <label class="form-label fs-8 fw-bold item-title-text mb-1">
                                <i class="bi bi-calendar-range text-primary me-1"></i> Periode Penerimaan
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="filter-input-icon-group">
                                        <i class="bi bi-calendar3 input-icon"></i>
                                        <input type="date" id="filter_tgl_awal" class="form-control" title="Tanggal Mulai">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="filter-input-icon-group">
                                        <i class="bi bi-calendar3 input-icon"></i>
                                        <input type="date" id="filter_tgl_akhir" class="form-control" title="Tanggal Akhir">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between" style="border-color: var(--sn-card-border, #e2e8f0) !important;">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold" onclick="resetFilterModal()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm px-3 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="exportFilteredExcel()" title="Download Excel Berdasarkan Pilihan Form Ini">
                        <i class="bi bi-file-earmark-excel-fill text-success"></i>
                        <span>Download Excel</span>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm" onclick="$('#form_filter_sn').submit()">
                        <i class="bi bi-check-circle me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL DETAIL TIMELINE --}}
@include('modul_produksi.tracking_sn._modal_tracking_detail')

{{-- MODAL SCANNER QR / BARCODE CAMERA --}}
<div class="modal modal-blur fade" id="modalTrackingQrScanner" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: #0b132b; color: #f8fafc;">
            
            {{-- HEADER DENGAN PILIHAN KAMERA --}}
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-3 py-2.5 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.04);">
                <div class="d-flex align-items-center gap-2 flex-grow-1 me-2">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 rounded-pill px-2.5 py-1 fs-8 fw-bold text-nowrap">
                        <i class="bi bi-camera-fill me-1"></i> Scanner
                    </span>
                    <select id="select_tracking_camera" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-50 rounded-pill py-1 fs-8 text-truncate" style="max-width: 190px;" onchange="handleTrackingCameraChange(this.value)">
                        <option value="">Deteksi Kamera...</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" onclick="switchTrackingCamera()" title="Ganti Kamera">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- BODY VIEWPORT SCANNER --}}
            <div class="modal-body p-3 text-center position-relative">
                <div class="scanner-viewport-wrapper my-1">
                    <div id="tracking_scanner_reader"></div>
                    <div class="scanner-overlay" style="display: none;">
                        <div class="scanner-target-box">
                            <div class="scanner-corner-tr"></div>
                            <div class="scanner-corner-bl"></div>
                            <div class="scanner-laser-line"></div>
                            <div class="scanner-center-dot"></div>
                            <div class="scanner-guide-dots scanner-dot-left"></div>
                            <div class="scanner-guide-dots scanner-dot-right"></div>
                            <div class="scanner-guide-dots scanner-dot-top"></div>
                            <div class="scanner-guide-dots scanner-dot-bottom"></div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="text-white-50 fs-8">Arahkan QR Code / Barcode unit ke dalam area bidik hijau</span>
                </div>
            </div>

            {{-- FOOTER DENGAN OPSI UPLOAD GAMBAR QR --}}
            <div class="modal-footer border-top border-secondary border-opacity-25 px-3 py-2.5 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.04);">
                <div>
                    <input type="file" id="input_qr_file" accept="image/*" style="display: none;" onchange="handleQrFileScan(this)">
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fs-8 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="$('#input_qr_file').click()" title="Pindai Gambar / Foto QR dari Galeri">
                        <i class="bi bi-image"></i>
                        <span>Scan dari Foto / Galeri</span>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fs-8 text-white" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

{{-- MODAL PREVIEW & CETAK LABEL STIKER QR CODE UNIT --}}
<div class="modal fade" id="modalQrLabelUnit" tabindex="-1" aria-labelledby="modalQrLabelUnitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 430px;">
        <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden" style="background: var(--sn-card-bg, #ffffff); color: var(--sn-text-main, #0f172a);">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(6, 182, 212, 0.05) 100%); border-color: var(--sn-card-border, #e2e8f0) !important;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 text-white shadow-xs" style="width: 36px; height: 36px; background: linear-gradient(135deg, #2563eb, #3b82f6);">
                        <i class="bi bi-qr-code fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-extrabold mb-0 fs-6" id="modalQrLabelUnitLabel" style="letter-spacing: -0.2px;">
                            Label QR Code Unit
                        </h6>
                        <small class="item-sub-text fs-8">Stiker Identitas &amp; Serial Number Unit</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY: STICKER LABEL CARD --}}
            <div class="modal-body p-4 text-center" style="background: var(--sn-page-bg, #f1f5f9);">
                        {{-- PRINTABLE LABEL STIKER AREA (Super Clean, Minimalist, Spacious) --}}
                <div id="printable_qr_label_card" class="p-4 rounded-4 text-start shadow-sm position-relative overflow-hidden" style="background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #e2e8f0 !important; font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;">
                    
                    {{-- Top Accent Gradient Bar --}}
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #2563eb, #06b6d4, #10b981);"></div>

                    {{-- QR Code Container with Generous Padding & Soft Border --}}
                    <div class="text-center pt-2 pb-1 mb-3">
                        <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm position-relative" style="border: 2px solid #3b82f6; background: #ffffff !important;">
                            <img id="qr_label_img" src="" alt="QR Code Unit" style="width: 175px; height: 175px; object-fit: contain; display: block;">
                        </div>
                        
                        {{-- Prominent Serial Number Tag --}}
                        <div class="mt-2.5 text-center">
                            <span class="d-inline-block px-4 py-1.5 rounded-pill font-monospace fw-black fs-7 shadow-xs" style="background: #eff6ff; color: #1d4ed8 !important; border: 1.5px solid #93c5fd; letter-spacing: 0.8px; font-weight: 800;" id="qr_label_sn">-</span>
                        </div>
                    </div>

                    {{-- Specifications Detail Card (Spacious, Clean Spacing, High Contrast) --}}
                    <div class="p-3 rounded-3 border" style="background: #f8fafc !important; border-color: #e2e8f0 !important; font-size: 12px; line-height: 1.6;">
                        
                        {{-- Row: No. SN --}}
                        <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-bottom-color: #e2e8f0 !important;">
                            <span class="d-flex align-items-center gap-1.5" style="color: #64748b !important; font-weight: 600; font-size: 11.5px;">
                                <i class="bi bi-upc-scan text-primary" style="font-size: 11px;"></i> No. SN
                            </span>
                            <span class="font-monospace fw-bold px-2 py-0.5 rounded text-truncate max-w-60" style="background: #eff6ff; color: #1d4ed8 !important; border: 1px solid #bfdbfe; font-size: 11.5px;" id="qr_label_box_sn">-</span>
                        </div>

                        {{-- Row: Kode Barang --}}
                        <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-bottom-color: #e2e8f0 !important;">
                            <span class="d-flex align-items-center gap-1.5" style="color: #64748b !important; font-weight: 600; font-size: 11.5px;">
                                <i class="bi bi-tag text-muted" style="font-size: 11px;"></i> Kode Barang
                            </span>
                            <span class="font-monospace fw-bold px-2 py-0.5 rounded text-truncate max-w-60" style="background: #e2e8f0; color: #0f172a !important; font-size: 11.5px;" id="qr_label_kode">-</span>
                        </div>

                        {{-- Row: Nama Barang --}}
                        <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-bottom-color: #e2e8f0 !important;">
                            <span class="d-flex align-items-center gap-1.5" style="color: #64748b !important; font-weight: 600; font-size: 11.5px;">
                                <i class="bi bi-box-seam text-muted" style="font-size: 11px;"></i> Nama Barang
                            </span>
                            <span class="fw-extrabold text-truncate text-end max-w-60" style="color: #0f172a !important; font-size: 12px;" id="qr_label_nama">-</span>
                        </div>

                        {{-- Row: No. Batch --}}
                        <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-bottom-color: #e2e8f0 !important;">
                            <span class="d-flex align-items-center gap-1.5" style="color: #64748b !important; font-weight: 600; font-size: 11.5px;">
                                <i class="bi bi-layers text-muted" style="font-size: 11px;"></i> No. Batch / Lot
                            </span>
                            <span class="font-monospace fw-bold px-2 py-0.5 rounded text-truncate max-w-60" style="background: #e0f2fe; color: #0369a1 !important; font-size: 11.5px;" id="qr_label_batch">-</span>
                        </div>

                        {{-- Row: Warna --}}
                        <div class="d-flex justify-content-between align-items-center pt-1.5">
                            <span class="d-flex align-items-center gap-1.5" style="color: #64748b !important; font-weight: 600; font-size: 11.5px;">
                                <i class="bi bi-palette text-muted" style="font-size: 11px;"></i> Varian Warna
                            </span>
                            <span class="fw-bold d-inline-flex align-items-center gap-1.5 px-2.5 py-0.5 rounded-pill" style="background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; font-size: 11.5px;" id="qr_label_warna_wrapper">
                                <span id="qr_label_warna_dot" style="display:inline-block;width:9px;height:9px;border-radius:50%;box-shadow: 0 0 2px rgba(0,0,0,0.25);"></span>
                                <span id="qr_label_warna">-</span>
                            </span>
                        </div>

                    </div>

                </div>

            </div>

            {{-- MODAL FOOTER DENGAN TOMBOL PRINT & DOWNLOAD --}}
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between" style="border-color: var(--sn-card-border, #e2e8f0) !important;">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-pill fs-8 fw-semibold" onclick="downloadUnitQrImage()" title="Unduh File Gambar QR Code">
                    <i class="bi bi-download me-1"></i> Unduh PNG
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm px-3.5 py-1.5 rounded-pill fs-8 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" onclick="printUnitQrLabel()" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                        <i class="bi bi-printer-fill"></i>
                        <span>Cetak Stiker</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<!-- Select2 Library -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Html5Qrcode Library (Stable Version 2.3.8) -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<!-- jsQR Library for High-Precision QR Code Image Decoding -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<!-- QRious Library for Crisp QR Code Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<script>
    let snTable;
    let heroSearchDebounceTimer = null;

    $(document).ready(function() {
        initDataTable();
        initSelect2InModal();
        initRealtimeHeroSearch();
    });

    function initRealtimeHeroSearch() {
        $('#input_hero_sn').on('input', function() {
            const query = $(this).val();
            
            // Toggle tombol hapus (X)
            if (query.trim().length > 0) {
                $('#btn_clear_hero').show();
            } else {
                $('#btn_clear_hero').hide();
            }

            // Realtime debounced search pada DataTables
            clearTimeout(heroSearchDebounceTimer);
            heroSearchDebounceTimer = setTimeout(function() {
                if (snTable) {
                    snTable.search(query.trim()).draw();
                }
            }, 300);
        });
    }

    function clearHeroSearch() {
        $('#input_hero_sn').val('');
        $('#btn_clear_hero').hide();
        if (snTable) {
            snTable.search('').draw();
        }
        $('#input_hero_sn').focus();
    }

    function initSelect2InModal() {
        $('#modalFilterSN').on('shown.bs.modal', function () {
            $('.select2-modal-filter').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterSN'),
                        width: '100%',
                        placeholder: $(this).data('placeholder') || '-- Pilih --',
                        allowClear: true
                    });
                }
            });
        });
    }

    function initDataTable() {
        snTable = $('#table_tracking_sn').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('produksi.tracking-sn.data') }}",
                data: function(d) {
                    d.barang_jadi_id = $('#filter_barang_jadi').val();
                    d.status         = $('#filter_status').val();
                    d.gudang_id      = $('#filter_gudang').val();
                    d.nomor_batch    = $('#filter_batch').val();
                    d.spk_id         = $('#filter_spk').val();
                    d.tanggal_awal   = $('#filter_tgl_awal').val();
                    d.tanggal_akhir  = $('#filter_tgl_akhir').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center fw-semibold text-muted', width: '40px' },
                { data: 'serial_number_badge', name: 'spk_penerimaan_barang_jadi_serials.serial_number' },
                { data: 'barang_info', name: 'barangJadi.nama_barang' },
                { data: 'batch_info', name: 'spk_penerimaan_barang_jadi_serials.nomor_batch' },
                { data: 'spk_info', name: 'spk.nomor_spk' },
                { data: 'gudang_lokasi', name: 'penerimaan.gudang.nama_gudang' },
                { data: 'tanggal_terima', name: 'penerimaan.tanggal_penerimaan' },
                { data: 'status_badge', name: 'spk_penerimaan_barang_jadi_serials.status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[6, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari Serial / Batch / SPK...",
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data serial number...',
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Tidak ada data Serial Number yang cocok.",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ unit",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(disaring dari _MAX_ total unit)",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
    }

    function applyModalFilter(e) {
        if (e) e.preventDefault();
        
        const modalEl = document.getElementById('modalFilterSN');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }

        updateFilterChips();

        if (snTable) {
            snTable.ajax.reload();
        }
    }

    function updateFilterChips() {
        const chips = [];

        const bj = $('#filter_barang_jadi option:selected');
        if (bj.val()) chips.push({ label: 'Produk', val: bj.text(), key: 'barang_jadi' });

        const st = $('#filter_status option:selected');
        if (st.val()) chips.push({ label: 'Status', val: st.text(), key: 'status' });

        const gd = $('#filter_gudang option:selected');
        if (gd.val()) chips.push({ label: 'Gudang', val: gd.text(), key: 'gudang' });

        const batch = $('#filter_batch').val().trim();
        if (batch) chips.push({ label: 'Batch', val: batch, key: 'batch' });

        const spk = $('#filter_spk option:selected');
        if (spk.val()) chips.push({ label: 'SPK', val: spk.text(), key: 'spk' });

        const tglAwal = $('#filter_tgl_awal').val();
        const tglAkhir = $('#filter_tgl_akhir').val();
        if (tglAwal || tglAkhir) {
            chips.push({ label: 'Periode', val: (tglAwal || '...') + ' s/d ' + (tglAkhir || '...'), key: 'tanggal' });
        }

        const list = $('#active_chips_list');
        list.empty();

        if (chips.length > 0) {
            $('#filter_active_count').text(chips.length).show();
            $('#btn_reset_filter').show();
            $('#active_chips_container').attr('style', 'display: flex !important;');

            chips.forEach(c => {
                list.append(`
                    <span class="filter-chip">
                        <span><strong>${c.label}:</strong> ${c.val}</span>
                        <i class="bi bi-x-circle-fill btn-remove-chip" onclick="removeSingleFilter('${c.key}')"></i>
                    </span>
                `);
            });
        } else {
            $('#filter_active_count').hide();
            $('#btn_reset_filter').hide();
            $('#active_chips_container').attr('style', 'display: none !important;');
        }
    }

    function removeSingleFilter(key) {
        if (key === 'barang_jadi') {
            $('#filter_barang_jadi').val('').trigger('change');
        } else if (key === 'status') {
            $('#filter_status').val('').trigger('change');
        } else if (key === 'gudang') {
            $('#filter_gudang').val('').trigger('change');
        } else if (key === 'batch') {
            $('#filter_batch').val('');
        } else if (key === 'spk') {
            $('#filter_spk').val('').trigger('change');
        } else if (key === 'tanggal') {
            $('#filter_tgl_awal').val('');
            $('#filter_tgl_akhir').val('');
        }

        updateFilterChips();
        if (snTable) {
            snTable.ajax.reload();
        }
    }

    function resetFilter() {
        $('#filter_barang_jadi').val('').trigger('change');
        $('#filter_status').val('').trigger('change');
        $('#filter_gudang').val('').trigger('change');
        $('#filter_spk').val('').trigger('change');
        $('#filter_batch').val('');
        $('#filter_tgl_awal').val('');
        $('#filter_tgl_akhir').val('');
        $('#input_hero_sn').val('');
        $('#btn_clear_hero').hide();

        updateFilterChips();
        if (snTable) {
            snTable.search('').ajax.reload();
        }
    }

    function resetFilterModal() {
        $('#form_filter_sn')[0].reset();
        $('.select2-modal-filter').val('').trigger('change');
    }

    function reloadTable() {
        if (snTable) {
            snTable.ajax.reload(null, false);
        }
    }

    function exportFilteredExcel() {
        const params = new URLSearchParams();
        
        const bj = $('#filter_barang_jadi').val();
        if (bj) params.append('barang_jadi_id', bj);

        const status = $('#filter_status').val();
        if (status) params.append('status', status);

        const gd = $('#filter_gudang').val();
        if (gd) params.append('gudang_id', gd);

        const batch = $('#filter_batch').val().trim();
        if (batch) params.append('nomor_batch', batch);

        const spk = $('#filter_spk').val();
        if (spk) params.append('spk_id', spk);

        const tglAwal = $('#filter_tgl_awal').val();
        if (tglAwal) params.append('tanggal_awal', tglAwal);

        const tglAkhir = $('#filter_tgl_akhir').val();
        if (tglAkhir) params.append('tanggal_akhir', tglAkhir);

        const heroSearch = $('#input_hero_sn').val().trim();
        if (heroSearch) params.append('search', heroSearch);

        window.location.href = "{{ route('produksi.tracking-sn.export-excel') }}?" + params.toString();
    }

    function handleHeroSearch(e) {
        e.preventDefault();
        const query = $('#input_hero_sn').val().trim();
        if (!query) {
            Swal.fire({
                icon: 'warning',
                title: 'Parameter Pencarian Kosong',
                text: 'Silakan ketik nomor Serial Number, No. Lot/Batch (LOD), atau Kode Barang terlebih dahulu.',
                confirmButtonColor: '#1e40af'
            });
            return;
        }

        // Sinkronkan filter tabel DataTables juga
        if (snTable) {
            snTable.search(query).draw();
        }

        // Buka modal timeline langsung
        openTimelineModal(query);
    }

    function openTimelineModal(queryParam) {
        const modal = new bootstrap.Modal(document.getElementById('modalTrackingDetail'));
        modal.show();

        $('#modal_tracking_loading').show();
        $('#modal_tracking_content').hide();

        $.ajax({
            url: "{{ url('produksi/tracking-sn/track') }}/" + encodeURIComponent(queryParam),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    renderTimelineModal(res.data);
                } else {
                    $('#modal_tracking_loading').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Ditemukan',
                        text: res.message || 'Data unit tidak ditemukan.',
                        confirmButtonColor: '#1e40af'
                    });
                }
            },
            error: function(xhr) {
                $('#modal_tracking_loading').hide();
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal melacak rekam jejak unit.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat',
                    text: msg,
                    confirmButtonColor: '#1e40af'
                });
            }
        });
    }

    let lastTrackingDetailData = null;
    let currentQrData = {};

    function extractSnFromText(text) {
        if (!text) return '';
        let str = text.trim();
        // If JSON payload
        if (str.startsWith('{') && str.endsWith('}')) {
            try {
                let parsed = JSON.parse(str);
                if (parsed.sn) return parsed.sn.toString().trim();
                if (parsed.serial_number) return parsed.serial_number.toString().trim();
                if (parsed.kode) return parsed.kode.toString().trim();
            } catch(e) {}
        }
        // If "SN: XXX" or "S/N: XXX" format anywhere in text
        let snMatch = str.match(/S\/?N\s*:\s*([^\r\n]+)/i) || str.match(/Serial\s*(?:Number)?\s*:\s*([^\r\n]+)/i);
        if (snMatch && snMatch[1]) {
            return snMatch[1].trim();
        }
        // If multiline text, check line by line
        let lines = str.split(/[\r\n]+/);
        for (let line of lines) {
            let m = line.match(/S\/?N\s*:\s*([^\r\n]+)/i);
            if (m && m[1]) return m[1].trim();
        }
        // Fallback: Return first non-empty line
        for (let line of lines) {
            let clean = line.trim();
            if (clean) return clean;
        }
        return str;
    }

    function generateUnitQrPayload(sn, kode, nama, batch, warna) {
        return `SN: ${sn || '-'}\nKODE: ${kode || '-'}\nBARANG: ${nama || '-'}\nBATCH: ${batch || '-'}\nWARNA: ${warna || '-'}`;
    }

    function renderTimelineModal(data) {
        lastTrackingDetailData = data;

        $('#modal_sn_title').text(data.serial_number);
        $('#modal_sn_box').text(data.serial_number);
        
        // Status Badge
        let statusBadgeClass = 'status-pill-tersedia';
        if (data.status === 'Terjual') statusBadgeClass = 'status-pill-terjual';
        else if (data.status === 'Rusak' || data.status === 'Reject') statusBadgeClass = 'status-pill-rusak';
        
        $('#modal_sn_status_badge').attr('class', 'badge ' + statusBadgeClass).text(data.status);
        $('#modal_sn_status_pill').attr('class', 'badge ' + statusBadgeClass).text(data.status);

        // Product Summary (Nama, Kode, Lot/LOD, Kategori, Warna)
        $('#modal_product_name').text(data.barang && data.barang.nama ? data.barang.nama : '-');
        $('#modal_product_code').html('<i class="bi bi-tag-fill me-1"></i>' + (data.barang && data.barang.kode ? data.barang.kode : '-'));
        $('#modal_product_kategori').text('• ' + (data.barang && data.barang.kategori ? data.barang.kategori : 'Barang Jadi'));
        $('#modal_batch_no').text(data.nomor_batch || '-');

        // Render Warna Badge
        if (data.barang && data.barang.warna && data.barang.warna !== '-') {
            $('#modal_product_warna_text').text(data.barang.warna);
            if (data.barang.warna_hex) {
                $('#modal_product_warna_dot').css({'background': data.barang.warna_hex, 'border': '1px solid #cbd5e1'}).show();
            } else {
                $('#modal_product_warna_dot').hide();
            }
            $('#modal_product_warna_badge').show();
        } else {
            $('#modal_product_warna_badge').hide();
        }

        // Generate QR Code Thumbnail in Summary Card
        try {
            const qrPayload = generateUnitQrPayload(data.serial_number, data.barang.kode, data.barang.nama, data.nomor_batch, data.barang.warna);
            let qr = new QRious({
                value: qrPayload,
                size: 180,
                level: 'H'
            });
            $('#modal_qr_thumb_img').attr('src', qr.toDataURL('image/png'));
        } catch(e) {}

        // Render Timeline Stepper
        const wrapper = $('#modal_timeline_wrapper');
        wrapper.empty();

        if (data.timeline && data.timeline.length > 0) {
            let lastLocation = '-';

            data.timeline.forEach((item, idx) => {
                const nodeClass = item.step_number === 1 ? 'node-primary' : (item.badge_color === 'success' ? 'node-success' : 'node-primary');
                
                let detailsHtml = '<div class="row g-2 small mt-2">';
                for (const [k, v] of Object.entries(item.details)) {
                    detailsHtml += `
                        <div class="col-6">
                            <span class="text-muted fs-8">${k}:</span>
                            <div class="fw-bold item-title-text fs-8">${v || '-'}</div>
                        </div>`;
                    if (k === 'Lokasi Fisik' || k === 'Gudang Tujuan') {
                        lastLocation = v;
                    }
                }
                detailsHtml += '</div>';

                let docLinkHtml = '';
                if (item.doc_link && item.doc_no) {
                    docLinkHtml = `
                        <a href="${item.doc_link}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 fw-bold fs-8 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-printer"></i>
                            <span>Cetak Dokumen</span>
                        </a>`;
                }

                const itemHtml = `
                    <div class="timeline-item">
                        <div class="timeline-node ${nodeClass}">
                            ${item.step_number}
                        </div>
                        <div class="timeline-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <div>
                                    <span class="badge bg-${item.badge_color}-subtle text-${item.badge_color} border border-${item.badge_color}-subtle px-2 py-0.5 rounded-pill fs-8 fw-bold mb-1">
                                        ${item.badge}
                                    </span>
                                    <h6 class="fw-extrabold item-title-text mb-0">${item.title}</h6>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold item-title-text font-monospace fs-8">${item.date}</div>
                                    <small class="text-muted fs-8">${item.time}</small>
                                </div>
                            </div>
                            
                            ${item.doc_no ? `
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                    <div class="font-monospace fw-bold text-primary fs-8">
                                        <i class="bi bi-file-earmark-text me-1"></i>${item.doc_no}
                                    </div>
                                    ${docLinkHtml}
                                </div>` : ''}

                            ${detailsHtml}
                        </div>
                    </div>`;

                wrapper.append(itemHtml);
            });

            $('#modal_current_location').text(lastLocation);
        }

        $('#modal_tracking_loading').hide();
        $('#modal_tracking_content').fadeIn(200);
    }

    // ===== QR CODE & BARCODE SCANNER LOGIC (HIGH PERFORMANCE & STABLE) =====
    let trackingQrScanner = null;
    let trackingCameraDevices = [];
    let currentTrackingCamIndex = 0;
    let isTrackingScanning = false;

    function openQrScannerModal() {
        $('#modalTrackingQrScanner').modal('show');
    }

    // Mulai kamera setelah modal benar-benar selesai terbuka (Bootstrap shown.bs.modal)
    $('#modalTrackingQrScanner').on('shown.bs.modal', function () {
        initTrackingCameraListAndStart();
    });

    // Hentikan kamera dan bersihkan resource saat modal ditutup
    $('#modalTrackingQrScanner').on('hide.bs.modal hidden.bs.modal', function () {
        stopTrackingScanner();
    });

    function initTrackingCameraListAndStart() {
        $('#select_tracking_camera').html('<option value="">Mendeteksi kamera...</option>');
        $('#tracking_scanner_reader').html('<div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;"><div class="spinner-border text-success mb-2" role="status"></div><span class="small text-success">Menginisialisasi Kamera...</span></div>');
        $('.scanner-overlay').hide();

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length > 0) {
                trackingCameraDevices = devices;
                let selectHtml = '';
                let defaultIndex = 0;

                devices.forEach((d, idx) => {
                    let label = d.label || `Kamera ${idx + 1}`;
                    let isBack = /back|rear|belakang|environment/i.test(label);
                    if (isBack) defaultIndex = idx;
                    selectHtml += `<option value="${d.id}">${label}</option>`;
                });

                $('#select_tracking_camera').html(selectHtml);
                currentTrackingCamIndex = defaultIndex;
                $('#select_tracking_camera').val(devices[defaultIndex].id);

                startTrackingScanner(devices[defaultIndex].id);
            } else {
                // Fallback jika enumerasi devices tidak tersedia
                startTrackingScanner({ facingMode: "environment" });
            }
        }).catch(err => {
            console.warn("Enumerasi kamera gagal, fallback ke facingMode:", err);
            startTrackingScanner({ facingMode: "environment" });
        });
    }

    function handleTrackingCameraChange(deviceId) {
        if (!deviceId) return;
        let foundIdx = trackingCameraDevices.findIndex(d => d.id === deviceId);
        if (foundIdx !== -1) currentTrackingCamIndex = foundIdx;
        startTrackingScanner(deviceId);
    }

    function switchTrackingCamera() {
        if (!trackingCameraDevices || trackingCameraDevices.length <= 1) {
            Swal.fire({
                icon: 'info',
                title: 'Info Kamera',
                text: 'Hanya 1 perangkat kamera yang terdeteksi.',
                timer: 1500,
                showConfirmButton: false
            });
            return;
        }
        currentTrackingCamIndex = (currentTrackingCamIndex + 1) % trackingCameraDevices.length;
        let nextCamId = trackingCameraDevices[currentTrackingCamIndex].id;
        $('#select_tracking_camera').val(nextCamId);
        startTrackingScanner(nextCamId);
    }

    function startTrackingScanner(cameraParam) {
        stopTrackingScanner(false); // Stop session sebelumnya tanpa mereset UI

        $('#tracking_scanner_reader').html('<div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;"><div class="spinner-border text-success mb-2" role="status"></div><span class="small text-success">Membuka Lensa Kamera...</span></div>');
        $('.scanner-overlay').hide();

        let formats = undefined;
        if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
            formats = [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.DATA_MATRIX
            ];
        }

        try {
            trackingQrScanner = new Html5Qrcode("tracking_scanner_reader", {
                formatsToSupport: formats,
                verbose: false,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            });
        } catch (e) {
            console.error("Gagal instansiasi Html5Qrcode:", e);
            return;
        }

        isTrackingScanning = false;

        const qrSuccessCallback = (decodedText, decodedResult) => {
            if (isTrackingScanning) return;
            isTrackingScanning = true;

            // Audio Beep
            try {
                let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = audioCtx.createOscillator();
                let gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = "sine";
                osc.frequency.value = 850;
                gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
                setTimeout(() => { try { audioCtx.close(); } catch(e) {} }, 250);
            } catch(e) {}

            // Visual Effect
            $('.scanner-target-box').css('box-shadow', '0 0 35px 12px #22c55e');

            const cleanScannedText = extractSnFromText(decodedText);
            $('#input_hero_sn').val(cleanScannedText);
            $('#btn_clear_hero').show();

            setTimeout(() => {
                $('#modalTrackingQrScanner').modal('hide');

                // Filter DataTables realtime
                if (snTable) {
                    snTable.search(cleanScannedText).draw();
                }

                // Langsung buka modal timeline pelacakan detail
                openTimelineModal(cleanScannedText);
            }, 150);
        };

        const scanConfig = {
            fps: 20,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                let size = Math.floor(minEdge * 0.82);
                return {
                    width: Math.max(size, 200),
                    height: Math.max(size, 200)
                };
            },
            aspectRatio: 1.333333,
            disableFlip: false
        };

        const cameraConfig = typeof cameraParam === 'string' ? { deviceId: { exact: cameraParam } } : cameraParam;

        trackingQrScanner.start(
            cameraConfig,
            scanConfig,
            qrSuccessCallback,
            (errorMessage) => {}
        ).then(() => {
            $('.scanner-overlay').fadeIn(150);
        }).catch(err => {
            console.error("Akses kamera gagal:", err);
            let cleanError = err ? err.toString() : 'Unknown Error';
            $('#tracking_scanner_reader').html(`
                <div class="d-flex align-items-center justify-content-center text-white flex-column p-4 text-center" style="min-height: 250px; background: #0f172a;">
                    <i class="bi bi-exclamation-triangle-fill text-warning mb-2" style="font-size: 2.2rem;"></i>
                    <span class="fw-bold">Gagal Mengakses Kamera</span>
                    <small class="text-white-50 mt-1">${cleanError}</small>
                    <small class="text-warning mt-2" style="font-size: 0.75rem;">Pastikan izin kamera diizinkan atau gunakan tombol "Scan dari Foto" di bawah.</small>
                </div>
            `);
        });
    }

    // Helper Memuat Gambar dari File Input
    function loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => reject(new Error("Gagal memuat gambar"));
                img.src = e.target.result;
            };
            reader.onerror = () => reject(new Error("Gagal membaca file"));
            reader.readAsDataURL(file);
        });
    }

    function canvasToBlob(canvas, type = 'image/jpeg', quality = 0.92) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob), type, quality);
        });
    }

    // Generator Canvas dengan Margin Putih (Quiet Zone) Wajib ZXing untuk QR Code terpotong / rapat ke tepi
    function renderPaddedCanvas(img, maxDim = null, applyFilter = null, padRatio = 0.15) {
        let w = img.width;
        let h = img.height;
        if (maxDim && (w > maxDim || h > maxDim)) {
            if (w > h) {
                h = Math.round((h * maxDim) / w);
                w = maxDim;
            } else {
                w = Math.round((w * maxDim) / h);
                h = maxDim;
            }
        }
        const pad = Math.max(30, Math.round(Math.max(w, h) * padRatio));
        const canvas = document.createElement('canvas');
        canvas.width = w + (pad * 2);
        canvas.height = h + (pad * 2);
        const ctx = canvas.getContext('2d');

        // Margin Putih Solid (Mandatory Quiet Zone)
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (applyFilter) {
            ctx.filter = applyFilter;
        }

        ctx.drawImage(img, pad, pad, w, h);
        return canvas;
    }

    async function tryScanWithJsQR(img, maxDim = null, applyFilter = null, padRatio = 0.15) {
        if (typeof jsQR === 'undefined') return null;
        try {
            const canvas = renderPaddedCanvas(img, maxDim, applyFilter, padRatio);
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'attemptBoth'
            });
            if (code && code.data) {
                return code.data;
            }
        } catch (e) {
            console.warn("jsQR scan pass error:", e);
        }
        return null;
    }

    async function tryScanCanvas(canvas) {
        // 1. Native BarcodeDetector pada Canvas Element
        if ('BarcodeDetector' in window) {
            try {
                const formats = await BarcodeDetector.getSupportedFormats().catch(() => []);
                const detector = new BarcodeDetector({
                    formats: formats.length > 0 ? formats : ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'data_matrix']
                });
                const barcodes = await detector.detect(canvas);
                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                    return barcodes[0].rawValue;
                }
            } catch (e) {}
        }

        // 2. Html5Qrcode.scanFile pada Canvas Blob
        try {
            const blob = await canvasToBlob(canvas);
            if (blob) {
                const file = new File([blob], "temp_scan.jpg", { type: "image/jpeg" });
                const fileScanner = new Html5Qrcode("tracking_scanner_reader", { verbose: false });
                const text = await fileScanner.scanFile(file, false);
                try { await fileScanner.clear(); } catch(e) {}
                if (text) return text;
            }
        } catch (e) {}

        return null;
    }

    // Multi-stage Robust Barcode & QR Code Decoder from Image File (jsQR + BarcodeDetector + ZXing)
    async function decodeBarcodeFromImageFile(imageFile) {
        let img = null;
        try {
            img = await loadImageFromFile(imageFile);
        } catch(e) {}

        if (img) {
            // Stage A: High-Precision jsQR Engine (Super Fast & Accurate for Gallery QR Codes)
            let qrRes = await tryScanWithJsQR(img, null, null); // Original Size + White Quiet Zone
            if (qrRes) return qrRes;

            qrRes = await tryScanWithJsQR(img, 1000, null); // 1000px Max Dimension
            if (qrRes) return qrRes;

            qrRes = await tryScanWithJsQR(img, 600, null); // 600px Max Dimension
            if (qrRes) return qrRes;

            qrRes = await tryScanWithJsQR(img, 800, 'grayscale(100%) contrast(160%)'); // Contrast Binarized
            if (qrRes) return qrRes;

            qrRes = await tryScanWithJsQR(img, 800, 'invert(100%)'); // Inverted Colors
            if (qrRes) return qrRes;
        }

        // Stage B: Native BarcodeDetector pada ImageBitmap asli
        if ('BarcodeDetector' in window) {
            try {
                const formats = await BarcodeDetector.getSupportedFormats().catch(() => []);
                const detector = new BarcodeDetector({
                    formats: formats.length > 0 ? formats : ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'data_matrix']
                });
                const imageBitmap = await createImageBitmap(imageFile);
                const barcodes = await detector.detect(imageBitmap);
                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                    return barcodes[0].rawValue;
                }
            } catch (e) {
                console.warn("Native BarcodeDetector stage B failed:", e);
            }
        }

        if (img) {
            // Stage C: Canvas Padded Margin Putih + ZXing / BarcodeDetector
            let c1 = renderPaddedCanvas(img, null, null);
            let res = await tryScanCanvas(c1);
            if (res) return res;

            let c2 = renderPaddedCanvas(img, 1000, null);
            res = await tryScanCanvas(c2);
            if (res) return res;

            let c3 = renderPaddedCanvas(img, 600, null);
            res = await tryScanCanvas(c3);
            if (res) return res;

            let c4 = renderPaddedCanvas(img, 800, 'grayscale(100%) contrast(160%)');
            res = await tryScanCanvas(c4);
            if (res) return res;

            let c5 = renderPaddedCanvas(img, 800, 'invert(100%)');
            res = await tryScanCanvas(c5);
            if (res) return res;
        }

        // Stage D: Fallback Html5Qrcode.scanFile pada raw file
        try {
            const fileScanner = new Html5Qrcode("tracking_scanner_reader", { verbose: false });
            const decodedText = await fileScanner.scanFile(imageFile, false);
            try { await fileScanner.clear(); } catch(e) {}
            if (decodedText) return decodedText;
        } catch (e) {}

        throw new Error("No readable barcode or QR code found in image.");
    }

    // Fungsi scan dari file / gambar galeri (Alternative scan 100% stabil)
    async function handleQrFileScan(inputEl) {
        if (!inputEl.files || inputEl.files.length === 0) return;
        const imageFile = inputEl.files[0];

        // 1. Tampilkan loading modal
        Swal.fire({
            title: 'Memproses Gambar...',
            text: 'Membaca QR Code / Barcode dari foto...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // 2. Hentikan kamera live agar tidak ada lock canvas/stream
        stopTrackingScanner(false);

        try {
            const decodedText = await decodeBarcodeFromImageFile(imageFile);

            Swal.close();
            const cleanScannedText = extractSnFromText(decodedText);
            if (cleanScannedText) {
                $('#input_hero_sn').val(cleanScannedText);
                $('#btn_clear_hero').show();
                $('#modalTrackingQrScanner').modal('hide');

                if (snTable) {
                    snTable.search(cleanScannedText).draw();
                }
                openTimelineModal(cleanScannedText);
            } else {
                throw new Error("Teks tidak dapat diekstrak.");
            }
        } catch (err) {
            console.warn("Scan file gagal:", err);
            Swal.fire({
                icon: 'error',
                title: 'Tidak Terbaca',
                text: 'Tidak ditemukan QR Code atau Barcode yang jelas pada foto tersebut. Pastikan gambar memiliki fokus tajam dan pencahayaan cukup.',
                confirmButtonColor: '#1e40af'
            });

            // Restart kamera kembali jika modal scanner masih aktif
            if ($('#modalTrackingQrScanner').hasClass('show')) {
                let activeId = trackingCameraDevices[currentTrackingCamIndex]?.id || { facingMode: "environment" };
                startTrackingScanner(activeId);
            }
        } finally {
            inputEl.value = '';
        }
    }

    function stopTrackingScanner(resetDom = true) {
        try {
            $('#tracking_scanner_reader video').each(function() {
                if (this.srcObject) {
                    let stream = this.srcObject;
                    if (stream && stream.getTracks) {
                        stream.getTracks().forEach(track => {
                            try { track.stop(); } catch(e) {}
                        });
                    }
                    this.srcObject = null;
                }
            });
        } catch(e) {}

        if (trackingQrScanner) {
            try {
                if (trackingQrScanner.isScanning) {
                    trackingQrScanner.stop().then(() => {
                        try { trackingQrScanner.clear(); } catch(e) {}
                        trackingQrScanner = null;
                    }).catch(() => {
                        try { trackingQrScanner.clear(); } catch(e) {}
                        trackingQrScanner = null;
                    });
                } else {
                    try { trackingQrScanner.clear(); } catch(e) {}
                    trackingQrScanner = null;
                }
            } catch (e) {
                trackingQrScanner = null;
            }
        }

        if (resetDom) {
            $('#tracking_scanner_reader').empty();
            $('.scanner-overlay').hide();
        }
    }

    // ===== QR CODE LABEL STIKER GENERATOR & PRINT FUNCTIONS =====
    function openQrLabelModal(sn, kode, nama, batch, warna, warnaHex) {
        currentQrData = {
            sn: sn || '-',
            kode: kode || '-',
            nama: nama || '-',
            batch: batch || '-',
            warna: (warna && warna !== '-') ? warna : 'Tanpa Warna',
            warnaHex: warnaHex || ''
        };

        $('#qr_label_sn').text(currentQrData.sn);
        $('#qr_label_box_sn').text(currentQrData.sn);
        $('#qr_label_kode').text(currentQrData.kode);
        $('#qr_label_nama').text(currentQrData.nama);
        $('#qr_label_batch').text(currentQrData.batch);
        $('#qr_label_warna').text(currentQrData.warna);

        if (currentQrData.warnaHex) {
            $('#qr_label_warna_dot').css({
                'background': currentQrData.warnaHex,
                'border': '1px solid #cbd5e1'
            }).show();
        } else {
            $('#qr_label_warna_dot').hide();
        }

        const qrPayload = generateUnitQrPayload(currentQrData.sn, currentQrData.kode, currentQrData.nama, currentQrData.batch, currentQrData.warna);

        try {
            let qr = new QRious({
                value: qrPayload,
                size: 320,
                level: 'H'
            });
            $('#qr_label_img').attr('src', qr.toDataURL('image/png'));
        } catch(e) {
            console.error("Gagal generate QRious:", e);
        }

        $('#modalQrLabelUnit').modal('show');
    }

    function openQrLabelModalFromCurrentDetail() {
        if (lastTrackingDetailData) {
            let b = lastTrackingDetailData.barang || {};
            openQrLabelModal(
                lastTrackingDetailData.serial_number,
                b.kode,
                b.nama,
                lastTrackingDetailData.nomor_batch,
                b.warna,
                b.warna_hex
            );
        }
    }

    function downloadUnitQrImage() {
        if (!currentQrData || !currentQrData.sn) return;
        const qrPayload = generateUnitQrPayload(currentQrData.sn, currentQrData.kode, currentQrData.nama, currentQrData.batch, currentQrData.warna);
        try {
            let qr = new QRious({
                value: qrPayload,
                size: 500,
                level: 'H'
            });
            const link = document.createElement('a');
            link.download = `QR_${currentQrData.sn}.png`;
            link.href = qr.toDataURL('image/png');
            link.click();
        } catch(e) {}
    }

    function printUnitQrLabel() {
        const printContent = document.getElementById('printable_qr_label_card').innerHTML;
        const printWindow = window.open('', '', 'width=450,height=580');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Cetak Label QR - ${currentQrData.sn || 'Unit'}</title>
                <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
                <style>
                    @page {
                        size: auto;
                        margin: 4mm;
                    }
                    * {
                        box-sizing: border-box;
                    }
                    body {
                        font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
                        margin: 0;
                        padding: 8px;
                        color: #0f172a;
                        background: #ffffff;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .font-monospace { font-family: 'JetBrains Mono', monospace; }
                    .text-center { text-align: center; }
                    .text-start { text-align: left; }
                    .fw-bold { font-weight: 700; }
                    .fw-extrabold { font-weight: 800; }
                    .fw-black { font-weight: 900; }
                    .d-flex { display: flex; }
                    .d-inline-flex { display: inline-flex; }
                    .d-inline-block { display: inline-block; }
                    .justify-content-between { justify-content: space-between; }
                    .align-items-center { align-items: center; }
                    .gap-1 { gap: 4px; }
                    .gap-1\\.5 { gap: 6px; }
                    .gap-2 { gap: 8px; }
                    .border { border: 1px solid #cbd5e1; }
                    .border-bottom { border-bottom: 1px solid #cbd5e1; }
                    .rounded-circle { border-radius: 50%; }
                    .rounded-2 { border-radius: 4px; }
                    .rounded-3 { border-radius: 8px; }
                    .rounded-4 { border-radius: 12px; }
                    .rounded-pill { border-radius: 9999px; }
                    .p-2 { padding: 6px; }
                    .p-2\\.5 { padding: 8px; }
                    .p-3 { padding: 10px; }
                    .p-3\\.5 { padding: 12px; }
                    .py-0\\.5 { padding-top: 2px; padding-bottom: 2px; }
                    .py-1 { padding-top: 4px; padding-bottom: 4px; }
                    .px-1\\.5 { padding-left: 6px; padding-right: 6px; }
                    .px-2 { padding-left: 8px; padding-right: 8px; }
                    .px-2\\.5 { padding-left: 10px; padding-right: 10px; }
                    .px-3 { padding-left: 12px; padding-right: 12px; }
                    .mb-2 { margin-bottom: 8px; }
                    .mb-2\\.5 { margin-bottom: 10px; }
                    .mt-1 { margin-top: 4px; }
                    .mt-2 { margin-top: 8px; }
                    .pt-1\\.5 { padding-top: 6px; }
                    .pb-2 { padding-bottom: 8px; }
                    .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                    .row { display: flex; flex-wrap: wrap; margin: 0 -4px; }
                    .col-4 { width: 33.33%; padding: 0 4px; }
                    .col-8 { width: 66.67%; padding: 0 4px; }
                    .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 8pt; }
                    img { max-width: 100%; height: auto; }
                </style>
            </head>
            <body>
                <div style="max-width: 310px; margin: 0 auto; border: 1.5px solid #cbd5e1; padding: 12px; border-radius: 12px; background: #ffffff; position: relative; overflow: hidden;">
                    ${printContent}
                </div>
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(() => window.close(), 600);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endpush

</x-layouts>
