<x-layouts title="Pembayaran Utang Supplier (Purchase Payment)">

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* ===== DESIGN SYSTEM TOKENS ===== */
    :root {
        --erp-card-bg: #ffffff;
        --erp-card-border: #e2e8f0;
        --erp-section-bg: #f8fafc;
        --erp-text-main: #0f172a;
        --erp-text-muted: #64748b;
        --erp-primary: #2563eb;
        --erp-primary-gradient: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        --erp-success-gradient: linear-gradient(135deg, #065f46 0%, #059669 100%);
        --erp-warning-gradient: linear-gradient(135deg, #92400e 0%, #d97706 100%);
        --erp-danger-gradient: linear-gradient(135deg, #991b1b 0%, #dc2626 100%);
        --erp-radius: 0.75rem;
    }

    /* ===== CINEMATIC BENTO GLOW STAT CARDS ===== */
    .pay-cinematic-card {
        border-radius: 1.25rem !important;
        min-height: 125px;
        padding: 1.15rem 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        border: none !important;
        width: 100%;
    }
    .pay-cinematic-card:hover {
        transform: translateY(-4px);
    }
    .pay-cine-blue {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%) !important;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45), 0 8px 10px -6px rgba(37, 99, 235, 0.25) !important;
    }
    .pay-cine-blue:hover {
        box-shadow: 0 16px 32px -4px rgba(37, 99, 235, 0.6), 0 10px 14px -4px rgba(37, 99, 235, 0.35) !important;
    }
    .pay-cine-green {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%) !important;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.45), 0 8px 10px -6px rgba(16, 185, 129, 0.25) !important;
    }
    .pay-cine-green:hover {
        box-shadow: 0 16px 32px -4px rgba(16, 185, 129, 0.6), 0 10px 14px -4px rgba(16, 185, 129, 0.35) !important;
    }
    .pay-cine-purple {
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 50%, #a855f7 100%) !important;
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.45), 0 8px 10px -6px rgba(124, 58, 237, 0.25) !important;
    }
    .pay-cine-purple:hover {
        box-shadow: 0 16px 32px -4px rgba(124, 58, 237, 0.6), 0 10px 14px -4px rgba(124, 58, 237, 0.35) !important;
    }
    .pay-cine-amber {
        background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%) !important;
        box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.45), 0 8px 10px -6px rgba(245, 158, 11, 0.25) !important;
    }
    .pay-cine-amber:hover {
        box-shadow: 0 16px 32px -4px rgba(245, 158, 11, 0.6), 0 10px 14px -4px rgba(245, 158, 11, 0.35) !important;
    }

    .pay-cine-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .pay-cine-title {
        font-size: 0.73rem !important;
        letter-spacing: 0.05em;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }
    .pay-cine-value {
        font-size: 1.35rem !important;
        font-weight: 800;
        letter-spacing: -0.02em;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }
    .pay-cine-sub {
        font-size: 0.72rem !important;
        opacity: 0.85;
    }
    .pay-cine-circle {
        position: absolute;
        right: -20px;
        bottom: -25px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    /* ===== WIDE MODAL ===== */
    .modal-dialog-payment-wide {
        max-width: 92vw !important;
        width: 92vw !important;
        margin: 1rem auto;
    }
    @media (max-width: 1199.98px) {
        .modal-dialog-payment-wide { max-width: 98vw !important; width: 98vw !important; margin: 0.5rem auto; }
    }

    /* ===== MODAL HEADER ===== */
    .pay-modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 1rem 1.5rem;
        border-bottom: none;
    }
    .pay-modal-header .modal-title { color: #fff; font-weight: 700; font-size: 1.05rem; }
    .pay-modal-header .text-subtitle { color: rgba(255,255,255,0.7); font-size: 0.8rem; }
    .pay-modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); opacity: 0.8; }

    /* ===== FORM CONTROLS IN MODALS ===== */
    .pay-modal-body .form-control,
    .pay-modal-body .form-select {
        font-size: 0.875rem;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        border-color: #cbd5e1;
        background-color: #f8fafc;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .pay-modal-body .form-control:focus,
    .pay-modal-body .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        background-color: #fff;
    }
    .pay-modal-body label.form-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }

    /* ===== SECTION TITLES ===== */
    .pay-section-title {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 1rem;
    }
    .pay-section-title .pay-section-num {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background: #2563eb;
        flex-shrink: 0;
    }
    .pay-section-title h6 {
        font-weight: 700;
        font-size: 0.875rem;
        margin: 0;
        letter-spacing: 0.02em;
        color: #1e293b;
    }
    .pay-section-title .pay-section-line {
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    /* ===== INVOICE TABLE STYLING ===== */
    .table-invoices th {
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-weight: 700;
        padding: 0.65rem 0.75rem;
        background-color: #f1f5f9;
        color: #475569;
    }
    .table-invoices td {
        font-size: 0.85rem;
        padding: 0.55rem 0.75rem;
        vertical-align: middle;
    }

    /* ===== SUMMARY SIDEBAR ===== */
    .calc-grand-total {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
        border-radius: 0.625rem;
        padding: 1.125rem 1.25rem;
        color: #fff;
    }
    .calc-grand-total .label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255,255,255,0.7);
        font-weight: 600;
    }
    .calc-grand-total .value {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    /* ===== BADGES & CHIPS ===== */
    .overdue-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 9999px;
        padding: 0.15rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* ===== AUDIT TRAIL TIMELINE ===== */
    .pay-audit-timeline {
        position: relative;
        padding-left: 28px !important;
    }
    .pay-audit-line {
        position: absolute;
        top: 14px;
        bottom: 14px;
        left: 11px;
        width: 2px;
        background: linear-gradient(180deg, #3b82f6 0%, #10b981 50%, #64748b 100%);
        border-radius: 999px;
        opacity: 0.35;
        z-index: 1;
    }
    .pay-audit-item {
        position: relative;
        z-index: 2;
    }
    .pay-audit-dot {
        position: absolute;
        left: -28px;
        top: 6px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        color: #fff;
        z-index: 3;
        border: 2px solid #0f172a;
    }
    .pay-dot-primary {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }
    .pay-dot-success {
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    }
    .pay-dot-warning {
        background: linear-gradient(135deg, #d97706, #f59e0b);
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
    }
    .pay-dot-danger {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
    }
    .pay-audit-card {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.08) !important;
        transition: all 0.2s ease;
    }
    .pay-audit-card:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
    .pay-audit-card-danger {
        background: rgba(220, 38, 38, 0.08) !important;
        border-color: rgba(220, 38, 38, 0.3) !important;
    }
    .pay-audit-role {
        font-size: 0.68rem;
        letter-spacing: 0.05em;
    }
    .pay-audit-time {
        font-size: 0.72rem;
    }
    .pay-audit-avatar {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        letter-spacing: -0.02em;
        flex-shrink: 0;
    }
    .pay-audit-name {
        font-size: 0.82rem;
        line-height: 1.2;
    }
    .pay-audit-sub {
        font-size: 0.7rem;
    }

    /* Light mode audit trail overrides */
    [data-bs-theme="light"] .pay-audit-dot {
        border-color: #ffffff;
    }
    [data-bs-theme="light"] .pay-audit-card {
        background: #f8fafc;
        border-color: #e2e8f0 !important;
    }
    [data-bs-theme="light"] .pay-audit-card:hover {
        background: #f1f5f9;
        border-color: #cbd5e1 !important;
    }
    [data-bs-theme="light"] .pay-audit-card-danger {
        background: #fef2f2 !important;
        border-color: #fecaca !important;
    }

    /* ===== DARK MODE OVERRIDES ===== */
    [data-bs-theme="dark"] {
        --erp-card-bg: #1e293b;
        --erp-card-border: #334155;
        --erp-section-bg: #0f172a;
        --erp-text-main: #f8fafc;
        --erp-text-muted: #94a3b8;
    }

    [data-bs-theme="dark"] .kpi-card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;
    }
    [data-bs-theme="dark"] .kpi-card .kpi-title {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .kpi-card .kpi-value {
        color: #f8fafc !important;
    }

    [data-bs-theme="dark"] .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .card-header {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    [data-bs-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .modal-body {
        background-color: #0f172a !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .modal-footer {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .modal-body .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }

    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.2) !important;
    }
    [data-bs-theme="dark"] .input-group-text {
        background-color: #334155 !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }

    [data-bs-theme="dark"] .pay-section-title h6 {
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .pay-section-title .pay-section-line {
        background: #334155 !important;
    }
    [data-bs-theme="dark"] label.form-label {
        color: #94a3b8 !important;
    }

    [data-bs-theme="dark"] .table {
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .table th {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .table td {
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .table-invoices th {
        background-color: #162032 !important;
        color: #94a3b8 !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .table-hover tbody tr:hover {
        background-color: rgba(255,255,255,0.04) !important;
    }
    [data-bs-theme="dark"] .table-primary,
    [data-bs-theme="dark"] tr.table-primary,
    [data-bs-theme="dark"] tr.table-primary td {
        background-color: rgba(37,99,235,0.18) !important;
        color: #f8fafc !important;
    }

    [data-bs-theme="dark"] .text-dark {
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .text-muted {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .bg-light,
    [data-bs-theme="dark"] .bg-white {
        background-color: #1e293b !important;
    }
    [data-bs-theme="dark"] .border {
        border-color: #334155 !important;
    }

    [data-bs-theme="dark"] .calc-grand-total {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
    }

    [data-bs-theme="dark"] .dataTables_info,
    [data-bs-theme="dark"] .dataTables_length label,
    [data-bs-theme="dark"] .dataTables_paginate {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .page-link {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    /* ===== SELECT2 CUSTOMIZATION (~10 VISIBLE ITEMS) ===== */
    .select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
        max-height: 320px !important;
        overflow-y: auto !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.5rem !important;
        min-height: 38px !important;
        padding: 0.35rem 0.65rem !important;
        font-size: 0.875rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding-left: 0 !important;
        font-size: 0.875rem !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-radius: 0.5rem !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 1060 !important;
    }
    .select2-container--bootstrap-5 .select2-results__option {
        padding: 0.55rem 0.85rem !important;
        font-size: 0.85rem !important;
        border-bottom: 1px dashed rgba(226, 232, 240, 0.5);
    }
    .select2-container--bootstrap-5 .select2-results__option:last-child {
        border-bottom: none;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        border-radius: 0.375rem !important;
        padding: 0.4rem 0.6rem !important;
        font-size: 0.83rem !important;
    }
    .select2-results__options::-webkit-scrollbar {
        width: 6px;
    }
    .select2-results__options::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 4px;
    }

    /* Dark Mode Select2 */
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #f1f5f9 !important;
    }
    [data-bs-theme="dark"] .select2-dropdown {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
    }
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
        color: #cbd5e1 !important;
        border-bottom-color: rgba(51, 65, 85, 0.5) !important;
    }
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--selected {
        background-color: rgba(37, 99, 235, 0.25) !important;
        color: #93c5fd !important;
    }

    /* Nested Modals Stack Z-Index */
    #modalLookupInvoice {
        z-index: 1070 !important;
    }
    #modalLiveCameraCapture {
        z-index: 1080 !important;
    }

    /* ===== MOBILE / RESPONSIVE OPTIMIZATIONS ===== */
    @media (max-width: 767.98px) {
        .container-fluid {
            padding-left: 0.35rem !important;
            padding-right: 0.35rem !important;
        }
        .pay-cinematic-card {
            border-radius: 1rem !important;
            min-height: 105px !important;
            padding: 0.95rem 1.15rem !important;
        }
        .pay-cine-icon {
            width: 36px !important;
            height: 36px !important;
            font-size: 1.15rem !important;
            border-radius: 10px !important;
        }
        .pay-cine-title {
            font-size: 0.72rem !important;
            letter-spacing: 0.04em !important;
            margin-bottom: 0.2rem !important;
        }
        .pay-cine-value {
            font-size: 1.25rem !important;
        }
        .pay-cine-sub {
            font-size: 0.72rem !important;
        }
        .pay-cine-circle {
            width: 105px !important;
            height: 105px !important;
            right: -15px !important;
            bottom: -20px !important;
        }
        .card {
            border-radius: 0.65rem !important;
            width: 100% !important;
        }
        .card-header {
            padding: 0.85rem 0.95rem !important;
        }
        .modal-dialog-payment-wide {
            max-width: 96% !important;
            width: 96% !important;
            margin: 0.5rem auto !important;
            height: calc(100% - 1rem) !important;
        }
        .modal-dialog-payment-wide .modal-content {
            border-radius: 0.75rem !important;
            max-height: 100% !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        .pay-modal-header {
            padding: 0.75rem 1rem !important;
            flex-shrink: 0 !important;
        }
        .pay-modal-body,
        .modal-dialog-payment-wide .modal-body {
            padding: 0.85rem !important;
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }
        .pay-section-title {
            margin-bottom: 0.75rem !important;
        }
        .calc-grand-total {
            padding: 0.75rem 0.85rem !important;
        }
        .calc-grand-total .value {
            font-size: 1.2rem !important;
        }
        .table-invoices th, .table-invoices td {
            font-size: 0.75rem !important;
            padding: 0.4rem 0.5rem !important;
        }
        .input-bayar, .input-potongan, .edit-input-bayar, .edit-input-potongan {
            font-size: 0.85rem !important;
            padding: 0.35rem 0.5rem !important;
            min-width: 110px !important;
        }
        .modal-footer {
            padding: 0.65rem 0.85rem !important;
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.5rem !important;
            flex-shrink: 0 !important;
            border-top: 1px solid var(--border-color, #e2e8f0) !important;
            background: var(--bg-card, #ffffff) !important;
        }
        .modal-footer > * {
            width: auto !important;
        }
        .modal-footer .d-flex {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 0.35rem !important;
            align-items: center !important;
            justify-content: flex-end !important;
        }
        .modal-footer button, .modal-footer a {
            min-height: 38px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.82rem !important;
            padding: 0.4rem 0.75rem !important;
            white-space: nowrap !important;
        }
    }

    /* ===== THEMED TABLE STYLES (LIGHT & DARK MODE) ===== */
    .table {
        --bs-table-bg: transparent;
        --bs-table-color: #1e293b;
        border-color: #e2e8f0;
    }
    .table thead th {
        background: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.76rem !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
        font-weight: 700 !important;
        padding: 0.75rem 0.85rem !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        vertical-align: middle !important;
    }
    .table tbody td {
        padding: 0.75rem 0.85rem !important;
        font-size: 0.85rem !important;
        color: #1e293b !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
    }
    .table tbody tr:hover td {
        background-color: #f8fafc !important;
    }
    .dataTables_empty {
        padding: 2.5rem 1rem !important;
        color: #64748b !important;
        font-size: 0.875rem !important;
        background-color: transparent !important;
    }

    /* Dark Mode Theme Overrides */
    [data-bs-theme="dark"] .table {
        --bs-table-bg: transparent;
        --bs-table-color: #f8fafc;
        border-color: #334155;
    }
    [data-bs-theme="dark"] .table thead th {
        background: #0f172a !important;
        color: #94a3b8 !important;
        border-bottom: 2px solid #334155 !important;
        border-top: none !important;
    }
    [data-bs-theme="dark"] .table tbody td {
        background-color: transparent !important;
        color: #f8fafc !important;
        border-bottom: 1px solid #334155 !important;
    }
    [data-bs-theme="dark"] .table tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }
    [data-bs-theme="dark"] .dataTables_empty {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .card-header {
        background-color: #1e293b !important;
        border-bottom-color: #334155 !important;
    }
    [data-bs-theme="dark"] .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }

    /* ===== HORIZONTAL TABLE SCROLLBAR & WIDE STYLING (TOUCH & DRAG FRIENDLY) ===== */
    .pay-top-scroll-container,
    .pay-table-scroll-wrapper,
    .table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-x pan-y !important;
        display: block !important;
        position: relative !important;
        scrollbar-width: thin;
        scrollbar-color: #64748b #0f172a;
    }
    .pay-top-scroll-container {
        height: 11px;
        background: #0b1120;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        cursor: grab;
        z-index: 5;
    }
    .pay-top-scroll-container:active {
        cursor: grabbing;
    }
    #tablePayments,
    .pay-wide-table {
        min-width: 1200px !important;
        width: 100% !important;
        table-layout: auto !important;
    }
    #tablePayments th, 
    #tablePayments td {
        white-space: nowrap !important;
    }

    /* WebKit Scrollbar Styling (Chrome, Safari, Edge, Mobile WebViews) */
    .pay-top-scroll-container::-webkit-scrollbar,
    .pay-table-scroll-wrapper::-webkit-scrollbar,
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 10px !important;
        width: 10px !important;
        background: #0f172a !important;
    }
    .pay-top-scroll-container::-webkit-scrollbar-track,
    .pay-table-scroll-wrapper::-webkit-scrollbar-track,
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #0f172a !important;
        border-radius: 9999px;
    }
    .pay-top-scroll-container::-webkit-scrollbar-thumb,
    .pay-table-scroll-wrapper::-webkit-scrollbar-thumb,
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #475569 !important;
        border-radius: 9999px;
        border: 2px solid #0f172a;
        min-width: 65px;
        transition: background-color 0.2s ease;
    }
    .pay-top-scroll-container::-webkit-scrollbar-thumb:hover,
    .pay-top-scroll-container::-webkit-scrollbar-thumb:active,
    .pay-table-scroll-wrapper::-webkit-scrollbar-thumb:hover,
    .pay-table-scroll-wrapper::-webkit-scrollbar-thumb:active {
        background: #3b82f6 !important;
    }

    /* Light Mode Scrollbar Overrides */
    [data-bs-theme="light"] .pay-top-scroll-container {
        background: #f1f5f9 !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    [data-bs-theme="light"] .pay-top-scroll-container::-webkit-scrollbar,
    [data-bs-theme="light"] .pay-table-scroll-wrapper::-webkit-scrollbar {
        background: #f1f5f9 !important;
    }
    [data-bs-theme="light"] .pay-top-scroll-container::-webkit-scrollbar-track,
    [data-bs-theme="light"] .pay-table-scroll-wrapper::-webkit-scrollbar-track {
        background: #e2e8f0 !important;
    }
    [data-bs-theme="light"] .pay-top-scroll-container::-webkit-scrollbar-thumb,
    [data-bs-theme="light"] .pay-table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #94a3b8 !important;
        border: 2px solid #e2e8f0 !important;
    }
    [data-bs-theme="light"] .pay-top-scroll-container::-webkit-scrollbar-thumb:hover,
    [data-bs-theme="light"] .pay-table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: #2563eb !important;
    }

    /* ===== PAGINATION & FOOTER STYLING ===== */
    .pay-dt-footer {
        background-color: transparent;
    }
    .dataTables_paginate {
        display: flex;
        align-items: center;
        margin: 0 !important;
    }
    .dataTables_paginate .pagination {
        margin: 0 !important;
        gap: 4px;
    }
    .dataTables_paginate .page-item .page-link {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.35rem 0.65rem;
        min-width: 32px;
        text-align: center;
        color: #475569;
        transition: all 0.15s ease-in-out;
    }
    .dataTables_paginate .page-item.active .page-link {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
    }
    .dataTables_paginate .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    .dataTables_paginate .page-item.disabled .page-link {
        opacity: 0.45;
        background-color: transparent;
    }

    /* Dark Mode Pagination */
    [data-bs-theme="dark"] .dataTables_paginate .page-item .page-link {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .dataTables_paginate .page-item.active .page-link {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.45);
    }
    [data-bs-theme="dark"] .dataTables_paginate .page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }

    /* DataTables Length Select */
    .dataTables_length select {
        padding: 0.3rem 1.8rem 0.3rem 0.6rem !important;
        font-size: 0.8rem !important;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
    }
    [data-bs-theme="dark"] .dataTables_length select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    /* ===== HEADER ACTION WRAPPER ===== */
    .header-action-btn-wrapper {
        margin-left: auto;
    }
    @media (max-width: 767.98px) {
        .header-action-btn-wrapper {
            width: 100%;
            margin-left: 0;
        }
        .header-action-btn-wrapper .btn {
            width: 100%;
        }
    }
</style>
@endpush

<div class="container-fluid px-1 px-sm-2 px-md-4 py-2 py-md-3">

    <!-- Page Header & Action Button -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                <span class="p-1.5 p-md-2 rounded-3 text-white d-inline-flex shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                    <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                </span>
                <span>Pembayaran Utang Supplier</span>
            </h4>
        </div>
        
        <!-- Tombol Tambah Data Compact Mepet ke Kanan -->
        @can('tambah-pembayaran-utang')
        <div class="header-action-btn-wrapper ms-md-auto">
            <button type="button" class="btn btn-primary text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" id="btnOpenModalCreate" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                <i class="bi bi-plus-circle-fill fs-6"></i>
                <span>Buat Pembayaran Utang</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- Cinematic Bento Glow Stat Cards (Full Width Stack on Mobile, 4 Cols on Desktop) -->
    <div class="row g-3 gy-3 gy-md-4 mb-4">
        <!-- Card 1: Pembayaran Bulan Ini (Blue) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-blue position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        PEMBAYARAN BULAN INI
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold font-monospace text-white fs-6 text-nowrap">Rp {{ number_format($totalPaymentIdrThisMonth, 2, ',', '.') }}</span>
                            <span class="badge bg-white bg-opacity-25 text-white font-monospace" style="font-size: 0.68rem;">IDR</span>
                        </div>
                        @if($totalPaymentUsdThisMonth > 0)
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold font-monospace text-warning fs-6 text-nowrap">$ {{ number_format($totalPaymentUsdThisMonth, 2, ',', '.') }}</span>
                            <span class="badge bg-warning text-dark fw-bold font-monospace" style="font-size: 0.68rem;">USD</span>
                        </div>
                        @endif
                    </div>
                    <div class="text-white text-opacity-80 font-monospace pay-cine-sub text-truncate mt-1.5 pt-1 border-top border-white border-opacity-20" style="font-size: 0.72rem;">
                        @if($totalPaymentUsdThisMonth > 0)
                            Est. Realisasi: <strong>≈ Rp {{ number_format($totalPaymentEquivalentIdrThisMonth, 2, ',', '.') }}</strong>
                        @else
                            Total realisasi pelunasan kas
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Utang Beredar (Green) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-green position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        TOTAL UTANG BEREDAR
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold font-monospace text-white fs-6 text-nowrap">Rp {{ number_format($totalOutstandingApIdr, 2, ',', '.') }}</span>
                            <span class="badge bg-white bg-opacity-25 text-white font-monospace" style="font-size: 0.68rem;">IDR</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold font-monospace text-warning fs-6 text-nowrap">$ {{ number_format($totalOutstandingApUsd, 2, ',', '.') }}</span>
                            <span class="badge bg-warning text-dark fw-bold font-monospace" style="font-size: 0.68rem;">USD</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1.5 pt-1 border-top border-white border-opacity-20" style="font-size: 0.72rem;">
                        Est. Total: <strong>≈ Rp {{ number_format($totalOutstandingApEquivalentIdr, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Faktur Lewat Tempo (Purple) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-purple position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        LEWAT TEMPO
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="fw-bold font-monospace text-white pay-cine-value text-nowrap">
                        {{ $totalOverdueCount }} <span class="fs-6 fw-normal text-white text-opacity-75">Faktur</span>
                    </div>
                    <div class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1">
                        @if($totalOverdueCount > 0)
                            @if($overdueIdrAmount > 0 && $overdueUsdAmount > 0)
                                Rp {{ number_format($overdueIdrAmount, 2, ',', '.') }} + ${{ number_format($overdueUsdAmount, 2, ',', '.') }}
                            @elseif($overdueIdrAmount > 0)
                                Rp {{ number_format($overdueIdrAmount, 2, ',', '.') }} (IDR)
                            @elseif($overdueUsdAmount > 0)
                                ${{ number_format($overdueUsdAmount, 2, ',', '.') }} (USD)
                            @endif
                        @else
                            Semua jatuh tempo aman
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Transaksi Bayar (Amber) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-amber position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        TOTAL TRANSAKSI
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="fw-bold font-monospace text-white pay-cine-value text-nowrap">
                        {{ $totalPaymentCount }} <span class="fs-6 fw-normal text-white text-opacity-75">Voucher</span>
                    </div>
                    <div class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1">
                        {{ $totalPostedCount }} Diposting • {{ $totalPendingCount }} Diajukan • {{ $totalDraftCount }} Draft
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card with Search & Filter Modal Trigger -->
    <div class="card border-0 shadow-sm rounded-3 rounded-md-4 overflow-hidden mb-4 w-100">
        <div class="card-header border-bottom p-3 p-md-3.5">
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2.5 gap-md-3">
                <!-- Left: Search Box -->
                <div class="input-group input-group-sm flex-grow-1" style="max-width: 420px;">
                    <span class="input-group-text border-end-0 py-2 px-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="filterSearch" class="form-control border-start-0 py-2" placeholder="Cari No. Bayar / Supplier / Ref...">
                </div>

                <!-- Right: Filter Modal Button & Reset Button -->
                <div class="d-flex align-items-center gap-2 pt-1 pt-sm-0 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold d-none" id="btnResetFilters" title="Reset Semua Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterPayments">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Filter Data</span>
                        <span class="badge bg-primary text-white rounded-pill ms-1 d-none" id="activeFilterBadge">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Filter Indicator Banner (appears only when filters are active) -->
        <div id="activeFilterInfoBanner" class="px-3 py-1.5 bg-body-tertiary border-bottom small d-none d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-1.5 flex-wrap" id="activeFilterBadgesContainer">
                <!-- Populated dynamically via JS -->
            </div>
            <a href="javascript:void(0)" class="text-danger small fw-semibold text-decoration-none" id="btnClearAllFilterChips">
                <i class="bi bi-trash me-1"></i>Hapus Filter
            </a>
        </div>

        <!-- DataTable Container with Wide Table and Smooth Horizontal Scroll -->
        <div class="card-body p-0">
            <!-- Top Synchronized Scrollbar Track (Visible for Direct Mobile Touch & Drag) -->
            <div class="pay-top-scroll-container w-100" id="payTopScrollContainer" title="Geser untuk melihat kolom tabel lainnya">
                <div id="payTopScrollDummy" style="height: 11px; min-width: 1200px; width: 1200px;"></div>
            </div>

            <div class="table-responsive pay-table-scroll-wrapper">
                <table class="table table-hover align-middle mb-0 text-nowrap pay-wide-table" id="tablePayments" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th class="text-center text-nowrap" style="width: 45px;">#</th>
                            <th class="text-nowrap" style="min-width: 155px;">No. Pembayaran</th>
                            <th class="text-nowrap" style="min-width: 110px;">Tanggal</th>
                            <th class="text-nowrap" style="min-width: 180px;">Supplier</th>
                            <th class="text-nowrap" style="min-width: 160px;">Akun Kas / Bank</th>
                            <th class="text-nowrap" style="min-width: 140px;">Metode Bayar</th>
                            <th class="text-end text-nowrap" style="min-width: 140px;">Total Bayar</th>
                            <th class="text-end text-nowrap" style="min-width: 120px;">Potongan</th>
                            <th class="text-center text-nowrap" style="min-width: 110px;">Status</th>
                            <th class="text-center text-nowrap" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modular Modal Partials -->
@include('modul_purchasing.pembayaran_utang.partials.modal_filter')
@can('tambah-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_create')
@include('modul_purchasing.pembayaran_utang.partials.modal_lookup_invoice')
@include('modul_purchasing.pembayaran_utang.partials.modal_camera')
@endcan
@can('edit-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_edit')
@endcan
@can('detail-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_view')
@endcan
@include('modul_purchasing.pembayaran_utang.partials.modal_preview_image')

@push('scripts')
    <!-- Select2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Pass permission flags ke JavaScript --}}
    <script>
        window.payPermissions = @json($permissions);
    </script>

    <!-- Modular JavaScript Files -->
    @include('modul_purchasing.pembayaran_utang.scripts.index_js')
    @can('tambah-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.create_js')
    @include('modul_purchasing.pembayaran_utang.scripts.camera_js')
    @endcan
    @can('edit-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.edit_js')
    @endcan
    @can('detail-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.view_js')
    @endcan
@endpush

</x-layouts>
