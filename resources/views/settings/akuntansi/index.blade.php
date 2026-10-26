<x-layouts title="Pengaturan Akuntansi Terpadu - ">

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/barang_dashboard.css?v=' . time()) }}">
    <style>
        .card-pr-container {
            background-color: #f0f6ff;
            border: 1px solid #dbeafe;
            border-radius: 18px;
        }

        /* ─── Select2 Custom & Scrollable (Display ~5 items) ─── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 50px !important;
            padding: 3px 8px !important;
            min-height: 32px !important;
            font-size: 0.76rem !important;
            font-weight: 500;
            display: flex !important;
            align-items: center !important;
            border-color: #cbd5e1;
            transition: all 0.2s ease;
        }
        .mapping-box .select2-container--bootstrap-5 .select2-selection {
            border-radius: 10px !important;
            min-height: 38px !important;
            font-size: 0.82rem !important;
        }
        .table-akuntansi .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important;
            min-height: 34px !important;
            font-size: 0.8rem !important;
        }
        .modal .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px !important;
            min-height: 38px !important;
            font-size: 0.82rem !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            font-size: 1.15rem !important;
            line-height: 1 !important;
            margin-right: 22px !important;
            color: #94a3b8 !important;
            position: absolute !important;
            right: 0 !important;
            cursor: pointer !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
            color: #ef4444 !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 4px !important;
            padding-right: 24px !important;
            color: inherit;
            font-family: var(--bs-font-monospace);
        }
        .select2-dropdown {
            border-radius: 12px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #cbd5e1 !important;
            overflow: hidden !important;
            z-index: 99999 !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options,
        .select2-results__options {
            max-height: 195px !important; /* ~5 visible items scrollable */
            overflow-y: auto !important;
            padding: 4px !important;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 6px 10px !important;
            font-size: 0.76rem !important;
            border-radius: 6px !important;
            margin-bottom: 2px !important;
            font-family: var(--bs-font-monospace);
            transition: all 0.15s ease;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 8px !important;
            padding: 5px 10px !important;
            font-size: 0.78rem !important;
            margin: 4px !important;
            width: calc(100% - 8px) !important;
        }
        /* Slim Scrollbar */
        .select2-results__options::-webkit-scrollbar {
            width: 5px;
        }
        .select2-results__options::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 10px;
        }
        .select2-results__options::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }
        
        /* Dark Mode Select2 */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        .dark-mode .select2-container--bootstrap-5 .select2-selection {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        .dark-mode .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-dropdown,
        .dark-mode .select2-dropdown {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        .dark-mode .select2-container--bootstrap-5 .select2-search__field {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-results__options::-webkit-scrollbar-thumb,
        .dark-mode .select2-results__options::-webkit-scrollbar-thumb {
            background: #475569;
        }
        [data-bs-theme="dark"] .select2-results__option,
        .dark-mode .select2-results__option {
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        /* ─── Horizontal Scrollable Table with Sticky Columns ─── */
        .table-category-wrapper {
            overflow-x: auto;
            cursor: grab;
            user-select: none;
            -webkit-overflow-scrolling: touch;
        }
        .table-category-wrapper.cursor-grabbing {
            cursor: grabbing;
        }
        .table-category-wrapper::-webkit-scrollbar {
            height: 7px;
        }
        .table-category-wrapper::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 10px;
        }
        .table-category-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
            border-radius: 10px;
        }
        [data-bs-theme="dark"] .table-category-wrapper::-webkit-scrollbar-track,
        .dark-mode .table-category-wrapper::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .sticky-col-no {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: var(--bs-body-bg) !important;
        }
        .sticky-col-cat {
            position: sticky;
            left: 55px;
            z-index: 5;
            background-color: var(--bs-body-bg) !important;
            box-shadow: 4px 0 10px -2px rgba(0,0,0,0.06);
        }
        .table-akuntansi thead th.sticky-col-no {
            z-index: 7;
            background-color: #dbeafe !important;
        }
        .table-akuntansi thead th.sticky-col-cat {
            z-index: 7;
            background-color: #dbeafe !important;
            box-shadow: 4px 0 10px -2px rgba(0,0,0,0.06);
        }
        [data-bs-theme="dark"] .table-akuntansi thead th.sticky-col-no,
        [data-bs-theme="dark"] .table-akuntansi thead th.sticky-col-cat,
        .dark-mode .table-akuntansi thead th.sticky-col-no,
        .dark-mode .table-akuntansi thead th.sticky-col-cat {
            background-color: #1e293b !important;
        }

        .nav-tabs-akuntansi {
            border-bottom: 1px solid #dbeafe;
        }
        .nav-tabs-akuntansi .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 18px;
            background: transparent;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .nav-tabs-akuntansi .nav-link:hover {
            color: #2563eb;
        }
        .nav-tabs-akuntansi .nav-link.active {
            color: #2563eb;
            font-weight: 700;
            border-bottom: 3px solid #2563eb;
            background: transparent;
        }

        /* Table Header PR Style (Light Mode: Blue Header) */
        .table-akuntansi {
            --bs-table-bg: transparent;
            --bs-table-accent-bg: transparent;
            --bs-table-striped-bg: transparent;
            --bs-table-hover-bg: transparent;
            color: #1e293b;
        }

        .table-akuntansi thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 800;
            color: #1e40af !important;
            background-color: #dbeafe !important;
            padding: 14px 16px;
            border-bottom: 1px solid #bfdbfe !important;
        }

        /* Zebra Striping PR Style */
        .table-akuntansi tbody tr:nth-of-type(odd) td {
            background-color: #eff6ff !important;
            color: #1e293b !important;
        }
        .table-akuntansi tbody tr:nth-of-type(even) td {
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
        .table-akuntansi tbody tr:hover td {
            background-color: #dbeafe !important;
        }

        .table-akuntansi td {
            padding: 12px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-card, .mapping-box {
            background-color: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 16px;
        }

        .coa-header-row {
            font-weight: 700;
        }

        .badge-debit {
            background-color: rgba(3, 105, 161, 0.1);
            color: #0284c7;
            border: 1px solid rgba(3, 105, 161, 0.2);
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .badge-kredit {
            background-color: rgba(180, 83, 9, 0.1);
            color: #d97706;
            border: 1px solid rgba(180, 83, 9, 0.2);
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* Action Buttons PR Style (White Circle Buttons) */
        .btn-action-circle {
            width: 34px;
            height: 34px;
            padding: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: 1px solid #dbeafe;
            background-color: #ffffff;
            color: #2563eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-action-circle:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            background-color: #f8fafc;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 10px;
            padding: 6px 14px;
            font-size: 0.875rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
        }

        /* ===== DARK MODE OVERRIDES ===== */
        [data-bs-theme="dark"] .card-pr-container,
        html[data-theme="dark"] .card-pr-container,
        .dark-mode .card-pr-container {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
        }

        [data-bs-theme="dark"] .text-dark,
        html[data-theme="dark"] .text-dark,
        .dark-mode .text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi,
        html[data-theme="dark"] .nav-tabs-akuntansi,
        .dark-mode .nav-tabs-akuntansi {
            border-bottom-color: #334155 !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi .nav-link,
        html[data-theme="dark"] .nav-tabs-akuntansi .nav-link,
        .dark-mode .nav-tabs-akuntansi .nav-link {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .nav-tabs-akuntansi .nav-link.active,
        html[data-theme="dark"] .nav-tabs-akuntansi .nav-link.active,
        .dark-mode .nav-tabs-akuntansi .nav-link.active {
            color: #3b82f6 !important;
            border-bottom-color: #3b82f6 !important;
            background: transparent !important;
        }

        [data-bs-theme="dark"] .info-card,
        [data-bs-theme="dark"] .mapping-box,
        html[data-theme="dark"] .info-card,
        html[data-theme="dark"] .mapping-box,
        .dark-mode .info-card,
        .dark-mode .mapping-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi,
        html[data-theme="dark"] .table-akuntansi,
        .dark-mode .table-akuntansi {
            --bs-table-bg: transparent !important;
            --bs-table-accent-bg: transparent !important;
            --bs-table-striped-bg: transparent !important;
            --bs-table-hover-bg: transparent !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi thead th,
        html[data-theme="dark"] .table-akuntansi thead th,
        .dark-mode .table-akuntansi thead th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:nth-of-type(odd) td,
        html[data-theme="dark"] .table-akuntansi tbody tr:nth-of-type(odd) td,
        .dark-mode .table-akuntansi tbody tr:nth-of-type(odd) td {
            background-color: #151e2e !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:nth-of-type(even) td,
        html[data-theme="dark"] .table-akuntansi tbody tr:nth-of-type(even) td,
        .dark-mode .table-akuntansi tbody tr:nth-of-type(even) td {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi tbody tr:hover td,
        html[data-theme="dark"] .table-akuntansi tbody tr:hover td,
        .dark-mode .table-akuntansi tbody tr:hover td {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .table-akuntansi td,
        html[data-theme="dark"] .table-akuntansi td,
        .dark-mode .table-akuntansi td {
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .table-akuntansi .text-dark,
        html[data-theme="dark"] .table-akuntansi .text-dark,
        .dark-mode .table-akuntansi .text-dark {
            color: #f8fafc !important;
        }

        /* ===== DARK MODE BADGES & BUTTONS ===== */
        [data-bs-theme="dark"] .bg-primary-subtle,
        html[data-theme="dark"] .bg-primary-subtle,
        .dark-mode .bg-primary-subtle {
            background-color: rgba(37, 99, 235, 0.2) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }

        [data-bs-theme="dark"] .bg-info-subtle,
        html[data-theme="dark"] .bg-info-subtle,
        .dark-mode .bg-info-subtle {
            background-color: rgba(14, 165, 233, 0.2) !important;
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.3) !important;
        }

        [data-bs-theme="dark"] .bg-success-subtle,
        html[data-theme="dark"] .bg-success-subtle,
        .dark-mode .bg-success-subtle {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border-color: rgba(52, 211, 153, 0.3) !important;
        }

        [data-bs-theme="dark"] .bg-danger-subtle,
        html[data-theme="dark"] .bg-danger-subtle,
        .dark-mode .bg-danger-subtle {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #f87171 !important;
            border-color: rgba(248, 113, 113, 0.3) !important;
        }

        [data-bs-theme="dark"] .bg-secondary-subtle,
        html[data-theme="dark"] .bg-secondary-subtle,
        .dark-mode .bg-secondary-subtle {
            background-color: rgba(148, 163, 184, 0.2) !important;
            color: #cbd5e1 !important;
            border-color: rgba(203, 213, 225, 0.3) !important;
        }

        /* ===== BADGE TIPE AKUN & FALLBACK BG-LIGHT ===== */
        .badge-account-type {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 500;
            padding: 4px 12px;
        }

        [data-bs-theme="dark"] .badge-account-type,
        html[data-theme="dark"] .badge-account-type,
        .dark-mode .badge-account-type,
        [data-bs-theme="dark"] .table-akuntansi .badge.bg-light,
        html[data-theme="dark"] .table-akuntansi .badge.bg-light,
        .dark-mode .table-akuntansi .badge.bg-light,
        [data-bs-theme="dark"] .badge.bg-light,
        html[data-theme="dark"] .badge.bg-light,
        .dark-mode .badge.bg-light {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #f1f5f9 !important;
            border-color: rgba(148, 163, 184, 0.35) !important;
        }

        /* Warna Teks Kode Akun Header agar kontras terang di Dark Mode */
        [data-bs-theme="dark"] .table-akuntansi .text-primary,
        html[data-theme="dark"] .table-akuntansi .text-primary,
        .dark-mode .table-akuntansi .text-primary {
            color: #60a5fa !important;
        }

        /* Baris Akun Header di COA */
        .coa-header-row {
            background-color: rgba(37, 99, 235, 0.04);
            font-weight: 600;
        }

        [data-bs-theme="dark"] .coa-header-row,
        html[data-theme="dark"] .coa-header-row,
        .dark-mode .coa-header-row {
            background-color: rgba(30, 58, 138, 0.22) !important;
        }

        [data-bs-theme="dark"] .coa-header-row td,
        html[data-theme="dark"] .coa-header-row td,
        .dark-mode .coa-header-row td {
            color: #f8fafc !important;
        }

        /* Judul Section Header */
        [data-bs-theme="dark"] h5.text-dark,
        html[data-theme="dark"] h5.text-dark,
        .dark-mode h5.text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .badge-debit,
        html[data-theme="dark"] .badge-debit,
        .dark-mode .badge-debit {
            background-color: rgba(56, 189, 248, 0.2) !important;
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.4) !important;
        }

        [data-bs-theme="dark"] .badge-kredit,
        html[data-theme="dark"] .badge-kredit,
        .dark-mode .badge-kredit {
            background-color: rgba(251, 146, 60, 0.2) !important;
            color: #fb923c !important;
            border-color: rgba(251, 146, 60, 0.4) !important;
        }

        [data-bs-theme="dark"] .btn-action-circle,
        html[data-theme="dark"] .btn-action-circle,
        .dark-mode .btn-action-circle {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_info,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_length,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_filter {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length select,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_length select,
        .dark-mode .dataTables_wrapper .dataTables_length select,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter input,
        html[data-theme="dark"] .dataTables_wrapper .dataTables_filter input,
        .dark-mode .dataTables_wrapper .dataTables_filter input {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
            border-radius: 6px !important;
            padding: 3px 8px !important;
        }

        [data-bs-theme="dark"] .page-link,
        html[data-theme="dark"] .page-link,
        .dark-mode .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .page-item.active .page-link,
        html[data-theme="dark"] .page-item.active .page-link,
        .dark-mode .page-item.active .page-link {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        html[data-theme="dark"] .form-control,
        .dark-mode .form-select,
        .dark-mode .form-control {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-select:focus,
        [data-bs-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus,
        html[data-theme="dark"] .form-control:focus,
        .dark-mode .form-select:focus,
        .dark-mode .form-control:focus {
            background-color: #0f172a !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-control::placeholder,
        html[data-theme="dark"] .form-control::placeholder,
        .dark-mode .form-control::placeholder {
            color: #64748b !important;
            opacity: 1 !important;
        }

        [data-bs-theme="dark"] .form-select option,
        html[data-theme="dark"] .form-select option,
        .dark-mode .form-select option {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        /* ===== DARK MODE MODAL & INPUT STYLING ===== */
        [data-bs-theme="dark"] .modal-content,
        html[data-theme="dark"] .modal-content,
        .dark-mode .modal-content {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
        }

        [data-bs-theme="dark"] .modal-header,
        html[data-theme="dark"] .modal-header,
        .dark-mode .modal-header {
            border-bottom: 1px solid #1e293b !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .modal-header .btn-close,
        html[data-theme="dark"] .modal-header .btn-close,
        .dark-mode .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%) !important;
        }

        [data-bs-theme="dark"] .modal-title,
        html[data-theme="dark"] .modal-title,
        .dark-mode .modal-title {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .modal-body,
        html[data-theme="dark"] .modal-body,
        .dark-mode .modal-body {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .modal-footer,
        html[data-theme="dark"] .modal-footer,
        .dark-mode .modal-footer {
            border-top: 1px solid #1e293b !important;
        }

        [data-bs-theme="dark"] .form-label,
        html[data-theme="dark"] .form-label,
        .dark-mode .form-label {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .btn-light,
        html[data-theme="dark"] .btn-light,
        .dark-mode .btn-light {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .btn-light:hover,
        html[data-theme="dark"] .btn-light:hover,
        .dark-mode .btn-light:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .input-group-text,
        html[data-theme="dark"] .input-group-text,
        .dark-mode .input-group-text {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
    </style>
    @endpush

    <div class="container-fluid py-3">
        <!-- Breadcrumb & Top Bar Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 p-2.5 rounded-3 text-primary d-flex align-items-center justify-content-center">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold m-0 text-dark">
                        @if($activeTab == 'coa') Chart of Account
                        @elseif($activeTab == 'account_type') Master Tipe Akun
                        @elseif($activeTab == 'system_mapping') Account Mapping System
                        @elseif($activeTab == 'category_mapping') Mapping Kategori Barang
                        @elseif($activeTab == 'kas_bank_mapping') Mapping Kategori Kas & Bank
                        @elseif($activeTab == 'tipe_pengeluaran') Master & Mapping Tipe Pengeluaran
                        @else Master & Mapping Jenis Biaya @endif
                    </h4>
                    <span class="text-muted small">Pengaturan &gt; Akuntansi Terpadu</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <div class="border px-3 py-1.5 rounded-3 shadow-sm d-flex align-items-center gap-2 bg-body">
                    <i class="bi bi-building text-primary"></i>
                    <span class="small fw-semibold text-dark">{{ session('active_company_name') ?: (auth()->user()->company->nama_perusahaan ?? 'PT Akuntansi ERP') }}</span>
                </div>
                <div class="border px-3 py-1.5 rounded-3 shadow-sm d-flex align-items-center gap-2 bg-body">
                    <i class="bi bi-calendar3 text-muted"></i>
                    <span class="small text-muted">Periode: <strong>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Sub-Tabs Header Navigation Bar -->
        <div class="card card-pr-container shadow-sm mb-4">
            <div class="card-header border-bottom px-4 pt-2 pb-0 bg-transparent">
                <ul class="nav nav-tabs-akuntansi gap-2" id="akuntansiTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'coa' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'coa']) }}">
                            <i class="bi bi-journal-text me-1"></i> Chart of Account
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'account_type' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'account_type']) }}">
                            <i class="bi bi-layers me-1"></i> Master Tipe Akun
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'system_mapping' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'system_mapping']) }}">
                            <i class="bi bi-sliders me-1"></i> Account Mapping System
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'category_mapping' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'category_mapping']) }}">
                            <i class="bi bi-diagram-3 me-1"></i> Mapping Kategori Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'kas_bank_mapping' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'kas_bank_mapping']) }}">
                            <i class="bi bi-wallet2 me-1"></i> Mapping Kategori Kas & Bank
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'petty_cash_mapping' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'petty_cash_mapping']) }}">
                            <i class="bi bi-cash-coin me-1"></i> Mapping Kategori Kas Kecil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ in_array($activeTab, ['expense_type', 'expense_mapping']) ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'expense_type']) }}">
                            <i class="bi bi-tags me-1"></i> Master & Mapping Jenis Biaya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'tipe_pengeluaran' ? 'active' : '' }}" href="{{ route('akuntansi.index', ['tab' => 'tipe_pengeluaran']) }}">
                            <i class="bi bi-box-arrow-up me-1"></i> Master & Mapping Tipe Pengeluaran
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                @if($activeTab == 'coa')
                    @include('settings.akuntansi.tab_coa')
                @elseif($activeTab == 'account_type')
                    @include('settings.akuntansi.tab_tipe_akun')
                @elseif($activeTab == 'system_mapping')
                    @include('settings.akuntansi.tab_system_mapping')
                @elseif($activeTab == 'category_mapping')
                    @include('settings.akuntansi.tab_category_mapping')
                @elseif($activeTab == 'kas_bank_mapping')
                    @include('settings.akuntansi.tab_kas_bank_mapping')
                @elseif($activeTab == 'petty_cash_mapping')
                    @include('settings.akuntansi.tab_petty_cash_mapping')
                @elseif($activeTab == 'tipe_pengeluaran')
                    @include('settings.akuntansi.tab_tipe_pengeluaran')
                @else
                    @include('settings.akuntansi.tab_jenis_biaya')
                @endif
            </div>
        </div>
    </div>

    <!-- Modular Modals -->
    @include('settings.akuntansi.modal_coa')
    @include('settings.akuntansi.modal_tipe_akun')
    @include('settings.akuntansi.modal_import_tipe_akun')
    @include('settings.akuntansi.modal_jenis_biaya')
    @include('settings.akuntansi.modal_tipe_pengeluaran')
    @include('settings.akuntansi.modal_kas_bank_mapping')
    @include('settings.akuntansi.modal_petty_cash_mapping')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Matikan Autocomplete Browser Secara Total
            $(document).on('focus', 'input:not([type="hidden"]), textarea', function() {
                $(this).attr('autocomplete', 'off');
                $(this).attr('autocorrect', 'off');
                $(this).attr('autocapitalize', 'off');
                $(this).attr('spellcheck', 'false');
            });

            // Initialize Select2 for Category Mapping
            function initCategorySelect2() {
                if ($.fn.select2) {
                    $('.cat-select').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: '-- Gunakan Default --',
                                allowClear: true,
                                dropdownParent: $(document.body)
                            });
                        }
                    });
                }
            }
            initCategorySelect2();

            // Initialize Select2 for System Mapping
            function initSystemMappingSelect2() {
                if ($.fn.select2) {
                    $('.select-system-coa').each(function() {
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            const placeholder = $(this).find('option[value=""]').first().text().trim() || '-- Pilih Akun COA --';
                            $(this).select2({
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: placeholder,
                                allowClear: true,
                                dropdownParent: $(document.body)
                            });
                        }
                    });
                }
            }
            initSystemMappingSelect2();

            // Initialize Select2 for Modal Tambah/Edit Jenis Biaya
            if ($.fn.select2 && $('#exp_coa_id').length) {
                $('#exp_coa_id').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: '-- Pilih Akun COA (Wajib) --',
                    allowClear: true,
                    dropdownParent: $('#modalTambahJenisBiaya')
                });
            }

            // Initialize Select2 for Modal Tambah/Edit Tipe Pengeluaran
            if ($.fn.select2) {
                if ($('#tambah_tipe_coa_id').length) {
                    $('#tambah_tipe_coa_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: '-- Pilih Akun COA Beban (Wajib) --',
                        allowClear: true,
                        dropdownParent: $('#modalTambahTipePengeluaran')
                    });
                }
                if ($('#edit_tipe_coa_id').length) {
                    $('#edit_tipe_coa_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: '-- Pilih Akun COA Beban (Wajib) --',
                        allowClear: true,
                        dropdownParent: $('#modalEditTipePengeluaran')
                    });
                }
            }

            const dtLang = {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                emptyTable: "Belum ada data.",
                zeroRecords: "Data tidak ditemukan.",
                paginate: { previous: "<", next: ">" }
            };
            const dtDom = '<"d-flex flex-wrap justify-content-between align-items-center mb-3"l f>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"i p>';

            if ($('#tableCoa').length) $('#tableCoa').DataTable({ pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]], language: dtLang, ordering: false, dom: dtDom });
            if ($('#tableMasterType').length) $('#tableMasterType').DataTable({ pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]], language: dtLang, ordering: false, dom: dtDom });
            if ($('#tableMasterExpense').length) $('#tableMasterExpense').DataTable({ pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]], language: dtLang, ordering: false, dom: dtDom });

            // Realtime Character Limit Check & Pop-up Alert
            $('#code').on('input keyup paste', function() {
                let val = $(this).val();
                $('#code_count').text(val.length);
                if (val.length > 10) {
                    $(this).val(val.substring(0, 10));
                    $('#code_count').text(10);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Kode Akun tidak boleh lebih dari 10 karakter!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            });

            $('#name').on('input keyup paste', function() {
                let val = $(this).val();
                $('#name_count').text(val.length);
                if (val.length > 40) {
                    $(this).val(val.substring(0, 40));
                    $('#name_count').text(40);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Nama Akun tidak boleh lebih dari 40 karakter!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            });

            // Save System Account Mapping
            $('#btnSaveSystemMapping').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: '{{ route("akuntansi.system-mappings.update") }}',
                    type: 'POST',
                    data: $('#formSystemMapping').serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Semua Pemetaan');
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan.', 'error');
                    }
                });
            });

            // Live Search Filter for Category Mapping
            $(document).on('input keyup', '#searchCategoryMapping', function() {
                let term = $(this).val().toLowerCase().trim();
                $('.category-map-row').each(function() {
                    let catName = $(this).data('category-name') || '';
                    if (term === '' || catName.includes(term)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Mark Changes on Category Select
            $(document).on('change', '.cat-select', function() {
                let val = $(this).val();
                let name = $(this).attr('name') || '';
                
                if (name.includes('[inventory]')) {
                    $(this).toggleClass('border-primary bg-primary bg-opacity-10', !!val);
                } else if (name.includes('[return]')) {
                    $(this).toggleClass('border-warning bg-warning bg-opacity-10', !!val);
                } else if (name.includes('[adjustment]')) {
                    $(this).toggleClass('border-info bg-info bg-opacity-10', !!val);
                } else if (name.includes('[cogs]')) {
                    $(this).toggleClass('border-danger bg-danger bg-opacity-10', !!val);
                }

                $('#catMappingStatus')
                    .removeClass('bg-success-subtle text-success border-success-subtle')
                    .addClass('bg-warning-subtle text-warning border-warning-subtle')
                    .html('<i class="bi bi-exclamation-circle-fill me-1"></i> Perubahan Belum Disimpan');
            });

            // Save Category Mapping
            $('#btnSaveCategoryMapping').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5"></span> Menyimpan...');
                $.ajax({
                    url: '{{ route("akuntansi.category-mappings.update") }}',
                    type: 'POST',
                    data: $('#formCategoryMapping').serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Simpan Mapping');
                        $('#catMappingStatus')
                            .removeClass('bg-warning-subtle text-warning border-warning-subtle')
                            .addClass('bg-success-subtle text-success border-success-subtle')
                            .html('<i class="bi bi-check-circle-fill me-1"></i> Semua Tersimpan');
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Berhasil Disimpan!', 
                            text: res.message || 'Mapping kategori barang berhasil diperbarui.', 
                            timer: 2000, 
                            showConfirmButton: false 
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Simpan Mapping');
                        Swal.fire('Gagal Menyimpan', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                    }
                });
            });

            // Horizontal Drag to Scroll for Category Mapping Table
            const catWrapper = document.querySelector('.table-category-wrapper');
            if (catWrapper) {
                let isDown = false;
                let startX;
                let scrollLeft;

                catWrapper.addEventListener('mousedown', (e) => {
                    if (e.target.closest('.select2-container') || e.target.closest('select') || e.target.closest('button') || e.target.closest('input')) return;
                    isDown = true;
                    catWrapper.classList.add('cursor-grabbing');
                    startX = e.pageX - catWrapper.offsetLeft;
                    scrollLeft = catWrapper.scrollLeft;
                });
                catWrapper.addEventListener('mouseleave', () => {
                    isDown = false;
                    catWrapper.classList.remove('cursor-grabbing');
                });
                catWrapper.addEventListener('mouseup', () => {
                    isDown = false;
                    catWrapper.classList.remove('cursor-grabbing');
                });
                catWrapper.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - catWrapper.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    catWrapper.scrollLeft = scrollLeft - walk;
                });
            }

            // Reset Modal Master Tipe Akun on Hide
            let codeManuallyEdited = false;
            $('#modalTambahMasterTipeAkun').on('hidden.bs.modal', function() {
                $('#formMasterTipeAkun')[0].reset();
                $('#type_id').val('');
                $('#modalTypeTitle').text('Tambah Master Tipe Akun');
                $('#btnSubmitType').prop('disabled', false).html('Simpan');
                codeManuallyEdited = false;
            });

            // Auto-Generate Kode Tipe Akun
            function fetchAutoTypeCode() {
                const nama = ($('#type_nama').val() || '').trim();
                const icon = $('#btnRefreshCodeType i');
                icon.addClass('spin-animation');
                $.get('/settings/master-tipe-akuns/generate-code', { nama: nama }, function(res) {
                    if (res && res.code) {
                        $('#type_kode').val(res.code);
                    }
                }).always(function() {
                    icon.removeClass('spin-animation');
                });
            }

            $(document).on('click', '#btnAutoCodeType, #btnRefreshCodeType', function(e) {
                e.preventDefault();
                fetchAutoTypeCode();
            });

            $('#type_kode').on('input', function() {
                codeManuallyEdited = true;
            });

            $('#modalTambahMasterTipeAkun').on('shown.bs.modal', function() {
                if (!$('#type_id').val() && !$('#type_kode').val()) {
                    fetchAutoTypeCode();
                }
            });

            $('#type_nama').on('blur', function() {
                if (!$('#type_id').val() && (!codeManuallyEdited || !$('#type_kode').val())) {
                    fetchAutoTypeCode();
                }
            });

            // Master Tipe Akun Submit
            $('#formMasterTipeAkun').on('submit', function(e) {
                e.preventDefault();
                const id = $('#type_id').val();
                const url = id ? `/settings/master-tipe-akuns/${id}` : '/settings/master-tipe-akuns';
                let formData = $(this).serializeArray();
                if (id) formData.push({ name: '_method', value: 'PUT' });
                $('#btnSubmitType').prop('disabled', true).html('Menyimpan...');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $.param(formData),
                    success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                    error: function(xhr) {
                        $('#btnSubmitType').prop('disabled', false).html('Simpan');
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan Tipe Akun.', 'error');
                    }
                });
            });

            // Edit Master Tipe Akun
            $(document).on('click', '.btn-edit-type', function() {
                const id = $(this).data('id');
                $.get(`/settings/master-tipe-akuns/${id}/edit`, function(res) {
                    const t = res.tipe_akun;
                    $('#type_id').val(t.id);
                    $('#type_kode').val(t.kode_tipe);
                    $('#type_nama').val(t.nama_tipe);
                    $('#type_kategori').val(t.kategori_laporan);
                    $('#type_saldo').val(t.saldo_normal_default);
                    $('#type_status').val(t.status);
                    $('#type_ket').val(t.keterangan);
                    $('#modalTypeTitle').text('Edit Master Tipe Akun');
                    $('#modalTambahMasterTipeAkun').modal('show');
                });
            });

            // Delete Master Tipe Akun
            $(document).on('click', '.btn-delete-type', function() {
                const id = $(this).data('id');
                Swal.fire({ title: 'Hapus Master Tipe Akun?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus' }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/settings/master-tipe-akuns/${id}`,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                            success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error'); }
                        });
                    }
                });
            });

            // Dropzone & File Input Handler untuk Import Tipe Akun
            function resetImportDropzone() {
                $('#import_type_excel_file').val('');
                $('#dropzoneEmptyState').removeClass('d-none');
                $('#dropzoneSelectedState').addClass('d-none');
                $('#selectedFileName').text('');
                $('#selectedFileSize').text('');
            }

            $('#import_type_excel_file').on('change', function() {
                const file = this.files && this.files[0];
                if (file) {
                    $('#selectedFileName').text(file.name);
                    const sizeFormatted = file.size > 1048576 
                        ? (file.size / 1048576).toFixed(2) + ' MB' 
                        : (file.size / 1024).toFixed(1) + ' KB';
                    $('#selectedFileSize').text(sizeFormatted);
                    $('#dropzoneEmptyState').addClass('d-none');
                    $('#dropzoneSelectedState').removeClass('d-none');
                } else {
                    resetImportDropzone();
                }
            });

            $('#btnResetSelectedFile').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                resetImportDropzone();
            });

            $('#importTypeDropzone').on('dragover dragenter', function() {
                $(this).addClass('border-primary shadow-sm').css('background', 'rgba(var(--bs-primary-rgb, 37, 99, 235), 0.08)');
            }).on('dragleave dragend drop', function() {
                $(this).removeClass('border-primary shadow-sm').css('background', 'rgba(var(--bs-primary-rgb, 37, 99, 235), 0.02)');
            });

            $('#modalImportMasterTipeAkun').on('hidden.bs.modal', function() {
                $('#formImportMasterTipeAkun')[0].reset();
                resetImportDropzone();
                $('#btnSubmitImportType').prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Mulai Import');
                $('#importTypeProgress').addClass('d-none');
            });

            // Import Master Tipe Akun Submit
            $('#formImportMasterTipeAkun').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const fileInput = $('#import_type_excel_file')[0];
                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.fire('Peringatan', 'Harap pilih file Excel (.xlsx / .csv) terlebih dahulu.', 'warning');
                    return false;
                }

                const formData = new FormData(form);
                const btn = $('#btnSubmitImportType');
                const progress = $('#importTypeProgress');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Mengimpor...');
                progress.removeClass('d-none');

                $.ajax({
                    url: '/settings/master-tipe-akuns/import',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#modalImportMasterTipeAkun').modal('hide');
                        form.reset();
                        resetImportDropzone();

                        if (res.status === 'warning') {
                            let warningListHtml = '';
                            if (res.errors && Array.isArray(res.errors) && res.errors.length > 0) {
                                warningListHtml = `
                                    <div class="text-start mt-3">
                                        <div class="small fw-semibold text-warning-emphasis mb-1.5">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Rincian Baris yang Dilewati (${res.errors.length}):
                                        </div>
                                        <div class="p-2.5 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25" style="max-height: 200px; overflow-y: auto;">
                                            <ul class="small text-warning-emphasis mb-0 ps-3 text-start" style="font-size: 12px; line-height: 1.6;">
                                                ${res.errors.map(err => `<li>${$('<div>').text(err).html()}</li>`).join('')}
                                            </ul>
                                        </div>
                                    </div>
                                `;
                            }
                            Swal.fire({
                                icon: 'warning',
                                title: 'Import Selesai dengan Catatan',
                                html: `<div class="text-start small text-muted mb-2">${$('<div>').text(res.message).html()}</div>${warningListHtml}`,
                                confirmButtonText: '<i class="bi bi-check-lg me-1"></i> OK, Muat Ulang Data'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Berhasil!',
                                html: `<div class="text-start text-body mb-2">${$('<div>').text(res.message || 'Semua data Master Tipe Akun berhasil diimpor ke sistem.').html()}</div>
                                       <div class="small text-muted text-start"><i class="bi bi-check-circle-fill text-success me-1"></i> Halaman akan dimuat ulang untuk menampilkan data terbaru.</div>`,
                                confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Selesai',
                                customClass: {
                                    confirmButton: 'btn btn-primary px-4'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Mulai Import');
                        progress.addClass('d-none');

                        const res = xhr.responseJSON;
                        const errorMsg = res?.message || 'Terjadi kesalahan saat memproses file Excel.';
                        
                        let rawErrors = [];
                        if (Array.isArray(res?.errors)) {
                            rawErrors = res.errors;
                        } else if (res?.errors && typeof res.errors === 'object') {
                            Object.values(res.errors).forEach(errArr => {
                                if (Array.isArray(errArr)) rawErrors.push(...errArr);
                                else rawErrors.push(errArr);
                            });
                        }

                        let errorHtml = `<div class="text-start text-muted small mb-2">${$('<div>').text(errorMsg).html()}</div>`;
                        if (rawErrors.length > 0) {
                            errorHtml += `
                                <div class="text-start mt-3">
                                    <div class="small fw-semibold text-danger mb-1.5">
                                        <i class="bi bi-x-octagon-fill me-1"></i> Rincian Kesalahan Ditemukan (${rawErrors.length}):
                                    </div>
                                    <div class="p-2.5 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25" style="max-height: 220px; overflow-y: auto;">
                                        <ul class="small text-danger-emphasis mb-0 ps-3 text-start" style="font-size: 12px; line-height: 1.6;">
                                            ${rawErrors.map(err => `<li>${$('<div>').text(err).html()}</li>`).join('')}
                                        </ul>
                                    </div>
                                    <div class="small text-muted mt-2 fst-italic" style="font-size: 11px;">
                                        * Silakan periksa kolom pada nomor baris di atas, perbaiki file Excel, lalu coba import kembali.
                                    </div>
                                </div>
                            `;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Import Gagal!',
                            html: errorHtml,
                            confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Tutup & Perbaiki',
                            customClass: {
                                confirmButton: 'btn btn-primary px-4'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            });

            // Export Excel Master Tipe Akun Handler
            $(document).on('click', '#btnExportMasterTipeAkun', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof window.hideLoader === 'function') window.hideLoader();

                const btn = $(this);
                const originalHtml = btn.html();
                const exportUrl = btn.attr('href');

                btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Menyiapkan...');

                fetch(exportUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal mengekspor data Excel');
                        
                        const disposition = response.headers.get('Content-Disposition');
                        let filename = 'master_tipe_akun.xlsx';
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) { 
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        return response.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        btn.removeClass('disabled').html(originalHtml);
                        if (typeof window.hideLoader === 'function') window.hideLoader();

                        // Trigger download file
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        // Alert Sukses Export
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Berhasil!',
                            html: `<div class="text-body small text-start mb-1">Data Master Tipe Akun berhasil diekspor.</div>
                                   <div class="small text-muted text-start">File <strong>${$('<div>').text(filename).html()}</strong> telah berhasil diunduh ke perangkat Anda.</div>`,
                            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Selesai',
                            customClass: {
                                confirmButton: 'btn btn-success px-4'
                            },
                            buttonsStyling: false
                        });
                    })
                    .catch(err => {
                        btn.removeClass('disabled').html(originalHtml);
                        if (typeof window.hideLoader === 'function') window.hideLoader();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Export',
                            text: 'Terjadi kesalahan sistem saat mengekspor file Excel. Silakan coba beberapa saat lagi.'
                        });
                    });
            });

            // Download Template Excel Master Tipe Akun Handler
            $(document).on('click', '#btnDownloadTemplateTipeAkun', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof window.hideLoader === 'function') window.hideLoader();

                const btn = $(this);
                const originalHtml = btn.html();
                const templateUrl = btn.attr('href');

                btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Mengunduh...');

                fetch(templateUrl)
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal mengunduh template Excel');
                        return response.blob().then(blob => ({ blob, filename: 'template_import_master_tipe_akun.xlsx' }));
                    })
                    .then(({ blob, filename }) => {
                        btn.removeClass('disabled').html(originalHtml);
                        if (typeof window.hideLoader === 'function') window.hideLoader();

                        // Trigger download template
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        // Toast Notifikasi Sukses Download Template
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Template Excel berhasil diunduh!'
                        });
                    })
                    .catch(err => {
                        btn.removeClass('disabled').html(originalHtml);
                        if (typeof window.hideLoader === 'function') window.hideLoader();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Download Template',
                            text: 'Terjadi kendala saat mengunduh format template Excel.'
                        });
                    });
            });

            // COA Submit Form
            $('#formCoa').on('submit', function(e) {
                e.preventDefault();

                const codeVal = ($('#code').val() || '').trim();
                const nameVal = ($('#name').val() || '').trim();

                if (codeVal.length > 10) {
                    Swal.fire('Peringatan', 'Kode Akun tidak boleh lebih dari 10 karakter!', 'warning');
                    return false;
                }
                if (nameVal.length > 40) {
                    Swal.fire('Peringatan', 'Nama Akun tidak boleh lebih dari 40 karakter!', 'warning');
                    return false;
                }

                const id = $('#coa_id').val();
                const url = id ? `/finance/chart-of-accounts/${id}` : '/finance/chart-of-accounts';
                let formData = $(this).serializeArray();
                if (id) formData.push({ name: '_method', value: 'PUT' });
                $('#btnSubmitCoa').prop('disabled', true).html('Menyimpan...');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $.param(formData),
                    success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                    error: function(xhr) {
                        $('#btnSubmitCoa').prop('disabled', false).html('Simpan');
                        const msg = xhr.responseJSON?.message || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Gagal menyimpan COA.');
                        Swal.fire('Peringatan', msg, 'warning');
                    }
                });
            });

            // Edit COA
            $(document).on('click', '.btn-edit-coa', function() {
                const id = $(this).data('id');
                $.get(`/finance/chart-of-accounts/${id}/edit`, function(res) {
                    const acc = res.account;
                    $('#coa_id').val(acc.id);
                    $('#code').val(acc.code);
                    $('#name').val(acc.name);
                    $('#master_tipe_akun_id').val(acc.master_tipe_akun_id || '');
                    $('#normal_balance').val(acc.normal_balance || 'debit');
                    $('#parent_id').val(acc.parent_id || '');
                    $('#level').val(acc.level || 1);
                    $('#is_header').prop('checked', acc.is_header);
                    $('#is_active').prop('checked', acc.is_active);

                    $('#code_count').text((acc.code || '').length);
                    $('#name_count').text((acc.name || '').length);

                    $('#modalCoaTitle').text('Edit Akun COA');
                    $('#modalTambahCoa').modal('show');
                });
            });

            // Delete COA
            $(document).on('click', '.btn-delete-coa', function() {
                const id = $(this).data('id');
                Swal.fire({ title: 'Hapus Akun COA?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus' }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/finance/chart-of-accounts/${id}`,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                            success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error'); }
                        });
                    }
                });
            });

            // Reset Modal Jenis Biaya saat ditutup
            $('#modalTambahJenisBiaya').on('hidden.bs.modal', function() {
                $('#formJenisBiaya')[0].reset();
                $('#exp_id').val('');
                $('#exp_coa_id').val('').trigger('change');
                $('#modalExpTitle').text('Tambah Jenis Biaya');
            });

            // Master Jenis Biaya Submit
            $('#formJenisBiaya').on('submit', function(e) {
                e.preventDefault();

                // Validasi Client-side Akun COA wajib dipilih
                const coaId = $('#exp_coa_id').val();
                if (!coaId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Akun COA (Mapping) wajib dipilih!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                    if ($.fn.select2) {
                        $('#exp_coa_id').select2('open');
                    }
                    return false;
                }

                const id = $('#exp_id').val();
                const url = id ? `/settings/master-jenis-biayas/${id}` : '/settings/master-jenis-biayas';
                let formData = $(this).serializeArray();
                if (id) formData.push({ name: '_method', value: 'PUT' });
                $('#btnSubmitExp').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $.param(formData),
                    success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                    error: function(xhr) {
                        $('#btnSubmitExp').prop('disabled', false).html('Simpan');
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan Jenis Biaya.', 'error');
                    }
                });
            });

            // Edit Jenis Biaya
            $(document).on('click', '.btn-edit-exp', function() {
                const id = $(this).data('id');
                $.get(`/settings/master-jenis-biayas/${id}/edit`, function(res) {
                    const exp = res.jenis_biaya;
                    $('#exp_id').val(exp.id);
                    $('#nama_jenis_biaya').val(exp.nama_jenis_biaya);
                    $('#kode_biaya').val(exp.kode_biaya);
                    $('#alokasi_biaya').val(exp.alokasi_biaya);
                    $('#exp_coa_id').val(exp.coa_id || '').trigger('change');
                    $('#masuk_hpp').prop('checked', exp.masuk_hpp);
                    $('#exp_status').val(exp.status);
                    $('#modalExpTitle').text('Edit Jenis Biaya');
                    $('#modalTambahJenisBiaya').modal('show');
                });
            });

            // Master Tipe Pengeluaran DataTables
            if ($('#tableMasterTipePengeluaran').length) {
                $('#tableMasterTipePengeluaran').DataTable({
                    language: { search: "Cari:", lengthMenu: "Tampilkan _MENU_ data" }
                });
            }

            // Reset Modal Tambah Tipe Pengeluaran saat ditutup
            $('#modalTambahTipePengeluaran').on('hidden.bs.modal', function() {
                $('#formTambahTipePengeluaran')[0].reset();
                $('#tambah_tipe_coa_id').val('').trigger('change');
            });

            // Submit Tambah Tipe Pengeluaran
            $('#formTambahTipePengeluaran').on('submit', function(e) {
                e.preventDefault();

                // Validasi Client-side Akun COA wajib dipilih
                const coaId = $('#tambah_tipe_coa_id').val();
                if (!coaId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Alokasi Akun COA Pengeluaran / Beban wajib dipilih!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                    if ($.fn.select2) {
                        $('#tambah_tipe_coa_id').select2('open');
                    }
                    return false;
                }

                $.ajax({
                    url: '{{ route("master-tipe-pengeluarans.store") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalTambahTipePengeluaran').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menyimpan Tipe Pengeluaran.', 'error');
                    }
                });
            });

            // Edit Tipe Pengeluaran
            $(document).on('click', '.btn-edit-tipe-pengeluaran', function() {
                const id = $(this).data('id');
                $.get(`/settings/master-tipe-pengeluarans/${id}/edit`, function(res) {
                    const item = res.tipe_pengeluaran;
                    $('#edit_tipe_pengeluaran_id').val(item.id);
                    $('#edit_tipe_nama_tipe').val(item.nama_tipe);
                    $('#edit_tipe_kode_tipe').val(item.kode_tipe);
                    $('#edit_tipe_coa_id').val(item.coa_id || '').trigger('change');
                    $('#edit_tipe_coa_kredit_id').val(item.coa_kredit_id || '');
                    $('#edit_tipe_status').val(item.status);
                    $('#edit_tipe_deskripsi').val(item.deskripsi);
                    $('#modalEditTipePengeluaran').modal('show');
                });
            });

            // Submit Edit Tipe Pengeluaran
            $('#formEditTipePengeluaran').on('submit', function(e) {
                e.preventDefault();

                // Validasi Client-side Akun COA wajib dipilih
                const coaId = $('#edit_tipe_coa_id').val();
                if (!coaId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Alokasi Akun COA Pengeluaran / Beban wajib dipilih!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                    if ($.fn.select2) {
                        $('#edit_tipe_coa_id').select2('open');
                    }
                    return false;
                }

                const id = $('#edit_tipe_pengeluaran_id').val();
                $.ajax({
                    url: `/settings/master-tipe-pengeluarans/${id}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalEditTipePengeluaran').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memperbarui Tipe Pengeluaran.', 'error');
                    }
                });
            });

            // Delete Tipe Pengeluaran
            $(document).on('click', '.btn-delete-tipe-pengeluaran', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: `Hapus Tipe Pengeluaran '${nama}'?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Hapus'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/settings/master-tipe-pengeluarans/${id}`,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                            success: function(res) { Swal.fire('Berhasil', res.message, 'success').then(() => location.reload()); },
                            error: function(xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error'); }
                        });
                    }
                });
            });

            // ===== KAS & BANK KATEGORI MAPPINGS =====
            if ($.fn.select2) {
                $('#modalTambahKasKategori').on('shown.bs.modal', function () {
                    if (!$('#tambah_kas_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#tambah_kas_coa_id').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: '-- Pilih Akun COA Lawan (Wajib) --',
                            dropdownParent: $('#modalTambahKasKategori')
                        });
                    }
                });
                $('#modalEditKasKategori').on('shown.bs.modal', function () {
                    if (!$('#edit_kas_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_kas_coa_id').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: '-- Pilih Akun COA Lawan (Wajib) --',
                            dropdownParent: $('#modalEditKasKategori')
                        });
                    }
                });
            }

            if ($('#tableKasKategoriMapping').length) {
                const tableKasKategori = $('#tableKasKategoriMapping').DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    language: dtLang,
                    ordering: false,
                    dom: dtDom
                });

                // Filter pills event
                $('#kasKategoriFilterPills .kas-pill').on('click', function() {
                    $('#kasKategoriFilterPills .kas-pill').removeClass('active');
                    $(this).addClass('active');

                    const filter = $(this).data('filter');
                    if (filter === 'all') {
                        tableKasKategori.column(1).search('').draw();
                    } else {
                        tableKasKategori.column(1).search(filter).draw();
                    }
                });
            }

            // Realtime Character Limit & Counter - Kategori Kas & Bank
            function setupKasKategoriLimit(inputSelector, counterSelector, maxLimit, fieldLabel) {
                $(document).on('input keyup paste', inputSelector, function() {
                    let val = $(this).val() || '';
                    if (val.length > maxLimit) {
                        $(this).val(val.substring(0, maxLimit));
                        $(counterSelector).text(`${maxLimit}/${maxLimit}`);
                        if (!Swal.isVisible()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Batas Karakter Tercapai',
                                text: `${fieldLabel} tidak boleh lebih dari ${maxLimit} karakter!`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    } else {
                        $(counterSelector).text(`${val.length}/${maxLimit}`);
                    }
                });
            }

            setupKasKategoriLimit('#tambah_kas_nama_kategori', '#tambah_kas_nama_count', 40, 'Nama Kategori');
            setupKasKategoriLimit('#tambah_kas_deskripsi', '#tambah_kas_deskripsi_count', 60, 'Deskripsi');
            setupKasKategoriLimit('#edit_kas_nama_kategori', '#edit_kas_nama_count', 40, 'Nama Kategori');
            setupKasKategoriLimit('#edit_kas_deskripsi', '#edit_kas_deskripsi_count', 60, 'Deskripsi');

            $('#modalTambahKasKategori').on('show.bs.modal', function() {
                $('#tambah_kas_nama_count').text('0/40');
                $('#tambah_kas_deskripsi_count').text('0/60');
            });

            // Submit Tambah Kas Kategori
            $('#formTambahKasKategori').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnSubmitTambahKasKategori');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: '{{ route("akuntansi.kas-kategori-mappings.store") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalTambahKasKategori').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                    }
                });
            });

            // Edit Kas Kategori
            $(document).on('click', '.btn-edit-kas-kategori', function() {
                const id = $(this).data('id');
                $.get(`/settings/akuntansi/kas-kategori-mappings/${id}/edit`, function(res) {
                    const item = res.mapping;
                    $('#edit_kas_kategori_id').val(item.id);
                    $('#edit_kas_nama_kategori').val(item.nama_kategori);
                    $('#edit_kas_nama_count').text(`${(item.nama_kategori || '').length}/40`);
                    if (item.tipe === 'Masuk') {
                        $('#edit_tipe_masuk').prop('checked', true);
                    } else {
                        $('#edit_tipe_keluar').prop('checked', true);
                    }
                    $('#edit_kas_coa_id').val(item.coa_id || '').trigger('change');
                    $('#edit_kas_deskripsi').val(item.deskripsi || '');
                    $('#edit_kas_deskripsi_count').text(`${(item.deskripsi || '').length}/60`);
                    $('#modalEditKasKategori').modal('show');
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data kategori.', 'error');
                });
            });

            // Submit Edit Kas Kategori
            $('#formEditKasKategori').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit_kas_kategori_id').val();
                const btn = $('#btnSubmitEditKasKategori');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: `/settings/akuntansi/kas-kategori-mappings/${id}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalEditKasKategori').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Perubahan');
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui.', 'error');
                    }
                });
            });

            // Delete Kas Kategori
            $(document).on('click', '.btn-delete-kas-kategori', function() {
                const id = $(this).data('id');
                const nama = $(this).attr('data-nama') || 'Kategori ini';
                Swal.fire({
                    title: 'Konfirmasi Hapus Kategori',
                    text: `Apakah Anda yakin ingin menghapus kategori "${nama}"? Kategori ini tidak akan muncul lagi di formulir kas masuk & kas keluar.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/settings/akuntansi/kas-kategori-mappings/${id}`,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error');
                            }
                        });
                    }
                });
            });

            // Toggle Status Switch Kas Kategori
            $(document).on('change', '.btn-toggle-kas-kategori', function() {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const self = $(this);
                $.ajax({
                    url: `/settings/akuntansi/kas-kategori-mappings/${id}/toggle`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PATCH' },
                    success: function(res) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'success', title: res.message });
                        self.closest('td').find('.text-xxs').text(res.is_active ? 'Aktif' : 'Non-Aktif')
                            .toggleClass('text-success fw-semibold', res.is_active)
                            .toggleClass('text-danger', !res.is_active);
                    },
                    error: function(xhr) {
                        self.prop('checked', !isChecked);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal mengubah status.', 'error');
                    }
                });
            });

            // ===== PETTY CASH (KAS KECIL) KATEGORI MAPPINGS =====
            if ($.fn.select2) {
                $('#modalTambahPettyCashMapping').on('shown.bs.modal', function () {
                    if (!$('#tambah_petty_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#tambah_petty_coa_id').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: '-- Pilih Akun COA Beban (Wajib) --',
                            dropdownParent: $('#modalTambahPettyCashMapping')
                        });
                    }
                });
                $('#modalEditPettyCashMapping').on('shown.bs.modal', function () {
                    if (!$('#edit_petty_coa_id').hasClass('select2-hidden-accessible')) {
                        $('#edit_petty_coa_id').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: '-- Pilih Akun COA Beban (Wajib) --',
                            dropdownParent: $('#modalEditPettyCashMapping')
                        });
                    }
                });
            }

            if ($('#tablePettyCashMapping').length) {
                $('#tablePettyCashMapping').DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    language: dtLang,
                    ordering: false,
                    dom: dtDom
                });
            }

            // Realtime Character Limit & Counter - Kategori Kas Kecil
            setupKasKategoriLimit('#tambah_petty_nama_kategori', '#tambah_petty_nama_count', 40, 'Nama Kategori Kas Kecil');
            setupKasKategoriLimit('#tambah_petty_deskripsi', '#tambah_petty_deskripsi_count', 60, 'Deskripsi');
            setupKasKategoriLimit('#edit_petty_nama_kategori', '#edit_petty_nama_count', 40, 'Nama Kategori Kas Kecil');
            setupKasKategoriLimit('#edit_petty_deskripsi', '#edit_petty_deskripsi_count', 60, 'Deskripsi');

            $('#modalTambahPettyCashMapping').on('show.bs.modal', function() {
                $('#tambah_petty_nama_count').text('0/40');
                $('#tambah_petty_deskripsi_count').text('0/60');
            });

            // Submit Tambah Petty Cash Mapping
            $('#formTambahPettyCashMapping').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnSubmitTambahPettyCashMapping');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: '{{ route("akuntansi.petty-cash-mappings.store") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalTambahPettyCashMapping').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            window.location.href = '{{ route("akuntansi.index", ["tab" => "petty_cash_mapping"]) }}';
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                    }
                });
            });

            // Edit Petty Cash Mapping
            $(document).on('click', '.btn-edit-petty-cash-mapping', function() {
                const id = $(this).data('id');
                $.get(`/settings/akuntansi/petty-cash-mappings/${id}/edit`, function(res) {
                    const item = res.mapping;
                    $('#edit_petty_mapping_id').val(item.id);
                    $('#edit_petty_nama_kategori').val(item.nama_kategori);
                    $('#edit_petty_nama_count').text(`${(item.nama_kategori || '').length}/40`);
                    $('#edit_petty_coa_id').val(item.coa_id || '').trigger('change');
                    $('#edit_petty_warna_badge').val(item.warna_badge || 'primary');
                    $('#edit_petty_icon').val(item.icon || 'bi-tag');
                    $('#edit_petty_deskripsi').val(item.deskripsi || '');
                    $('#edit_petty_deskripsi_count').text(`${(item.deskripsi || '').length}/60`);
                    $('#modalEditPettyCashMapping').modal('show');
                }).fail(function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memuat data kategori kas kecil.', 'error');
                });
            });

            // Submit Edit Petty Cash Mapping
            $('#formEditPettyCashMapping').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit_petty_mapping_id').val();
                const btn = $('#btnSubmitEditPettyCashMapping');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                $.ajax({
                    url: `/settings/akuntansi/petty-cash-mappings/${id}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalEditPettyCashMapping').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            window.location.href = '{{ route("akuntansi.index", ["tab" => "petty_cash_mapping"]) }}';
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan Perubahan');
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui.', 'error');
                    }
                });
            });

            // Delete Petty Cash Mapping
            $(document).on('click', '.btn-delete-petty-cash-mapping', function() {
                const id = $(this).data('id');
                const nama = $(this).attr('data-nama') || 'Kategori ini';
                Swal.fire({
                    title: 'Konfirmasi Hapus Kategori Kas Kecil',
                    text: `Apakah Anda yakin ingin menghapus kategori "${nama}"? Kategori ini tidak akan muncul lagi sebagai shortcut di formulir kas kecil.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: `/settings/akuntansi/petty-cash-mappings/${id}`,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    window.location.href = '{{ route("akuntansi.index", ["tab" => "petty_cash_mapping"]) }}';
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus.', 'error');
                            }
                        });
                    }
                });
            });

            // Toggle Status Switch Petty Cash Mapping
            $(document).on('change', '.btn-toggle-petty-cash-mapping', function() {
                const id = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const self = $(this);
                $.ajax({
                    url: `/settings/akuntansi/petty-cash-mappings/${id}/toggle`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'PATCH' },
                    success: function(res) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'success', title: res.message });
                        self.closest('td').find('.text-xxs').text(res.is_active ? 'Aktif' : 'Non-Aktif')
                            .toggleClass('text-success fw-semibold', res.is_active)
                            .toggleClass('text-danger', !res.is_active);
                    },
                    error: function(xhr) {
                        self.prop('checked', !isChecked);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal mengubah status.', 'error');
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
