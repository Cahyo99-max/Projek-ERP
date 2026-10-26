<x-layouts title="Data Customer / Pelanggan">

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* ─── Cinema Modern Variable Design Tokens ─────────────────── */
        :root {
            --cine-card-bg: #ffffff;
            --cine-card-sub: #f8fafc;
            --cine-border: #e2e8f0;
            --cine-border-glow: rgba(59, 130, 246, 0.15);
            --cine-text-main: #0f172a;
            --cine-text-sub: #475569;
            --cine-text-muted: #64748b;
            --cine-primary: #2563eb;
            --cine-primary-glow: rgba(37, 99, 235, 0.12);
            --cine-success: #059669;
            --cine-success-glow: rgba(16, 185, 129, 0.12);
            --cine-info: #0284c7;
            --cine-info-glow: rgba(14, 165, 233, 0.12);
            --cine-warning: #d97706;
            --cine-warning-glow: rgba(245, 158, 11, 0.12);
            --cine-purple: #7c3aed;
            --cine-purple-glow: rgba(139, 92, 246, 0.12);
            
            --table-header-bg: #f8fafc;
            --table-hover-bg: #f1f5f9;
        }

        [data-bs-theme="dark"],
        html[data-theme="dark"],
        .dark-mode {
            --cine-card-bg: #111827;
            --cine-card-sub: #1f293d;
            --cine-border: rgba(255, 255, 255, 0.08);
            --cine-border-glow: rgba(56, 189, 248, 0.25);
            --cine-text-main: #f9fafb;
            --cine-text-sub: #cbd5e1;
            --cine-text-muted: #94a3b8;
            --cine-primary: #38bdf8;
            --cine-primary-glow: rgba(56, 189, 248, 0.2);
            --cine-success: #34d399;
            --cine-success-glow: rgba(52, 211, 153, 0.2);
            --cine-info: #22d3ee;
            --cine-info-glow: rgba(34, 211, 238, 0.2);
            --cine-warning: #fbbf24;
            --cine-warning-glow: rgba(251, 191, 36, 0.2);
            --cine-purple: #c084fc;
            --cine-purple-glow: rgba(192, 132, 252, 0.2);

            --table-header-bg: #0f172a;
            --table-hover-bg: rgba(255, 255, 255, 0.035);
        }

        /* ─── Cinematic Summary Cards ─────────────────────────────── */
        .summary-card {
            background-color: var(--cine-card-bg);
            border: 1px solid var(--cine-border);
            border-radius: 16px;
            padding: 1.35rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0.85;
        }
        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12);
            border-color: var(--cine-border-glow);
        }
        .summary-card-total::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .summary-card-active::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .summary-card-distributor::before { background: linear-gradient(90deg, #06b6d4, #38bdf8); }
        .summary-card-retail::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

        .summary-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--cine-text-muted);
            margin-bottom: 0.35rem;
        }
        .summary-value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }
        .summary-value-total { color: var(--cine-primary); text-shadow: 0 0 20px var(--cine-primary-glow); }
        .summary-value-active { color: var(--cine-success); text-shadow: 0 0 20px var(--cine-success-glow); }
        .summary-value-distributor { color: var(--cine-info); text-shadow: 0 0 20px var(--cine-info-glow); }
        .summary-value-retail { color: var(--cine-warning); text-shadow: 0 0 20px var(--cine-warning-glow); }

        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }
        .summary-card:hover .summary-icon {
            transform: scale(1.08);
        }
        .summary-icon-total { background: var(--cine-primary-glow); color: var(--cine-primary); }
        .summary-icon-active { background: var(--cine-success-glow); color: var(--cine-success); }
        .summary-icon-distributor { background: var(--cine-info-glow); color: var(--cine-info); }
        .summary-icon-retail { background: var(--cine-warning-glow); color: var(--cine-warning); }

        /* ─── Table Modern Styling ───────────────────────────────── */
        #tableCustomer {
            color: var(--cine-text-main) !important;
        }
        #tableCustomer thead th {
            background-color: var(--table-header-bg) !important;
            color: var(--cine-text-muted) !important;
            border-bottom: 2px solid var(--cine-border) !important;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 16px;
            white-space: nowrap;
        }
        #tableCustomer tbody td {
            background-color: transparent !important;
            color: var(--cine-text-main) !important;
            border-bottom: 1px solid var(--cine-border) !important;
            padding: 13px 16px;
            vertical-align: middle;
        }
        #tableCustomer tbody tr {
            transition: background-color 0.15s ease;
        }
        #tableCustomer tbody tr:hover td {
            background-color: var(--table-hover-bg) !important;
        }

        /* ─── Cell Component Design ──────────────────────────────── */
        .customer-code-badge {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 50px;
            background: var(--cine-primary-glow);
            color: var(--cine-primary);
            border: 1px solid rgba(56, 189, 248, 0.3);
            letter-spacing: 0.5px;
        }

        .customer-name-title {
            font-weight: 700;
            font-size: 0.94rem;
            color: var(--cine-text-main) !important;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .customer-company-sub {
            font-size: 0.78rem;
            color: var(--cine-text-muted) !important;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Type Badges */
        .badge-type-pill {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 3px 9px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 3px;
        }
        .badge-type-distributor {
            background: var(--cine-primary-glow);
            color: var(--cine-primary);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .badge-type-agen {
            background: var(--cine-info-glow);
            color: var(--cine-info);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .badge-type-retail {
            background: var(--cine-warning-glow);
            color: var(--cine-warning);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .badge-type-enduser {
            background: var(--cine-purple-glow);
            color: var(--cine-purple);
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .customer-cp-sub {
            font-size: 0.78rem;
            color: var(--cine-text-sub) !important;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .customer-phone {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--cine-text-main) !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }
        .customer-email-sub {
            font-size: 0.76rem;
            color: var(--cine-text-muted) !important;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .customer-location {
            font-size: 0.84rem;
            color: var(--cine-text-sub) !important;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Status Pills */
        .badge-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .badge-status-active {
            background: var(--cine-success-glow);
            color: var(--cine-success);
            border: 1px solid rgba(16, 185, 129, 0.35);
        }
        .badge-status-inactive {
            background: rgba(148, 163, 184, 0.12);
            color: var(--cine-text-muted);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        /* Action Buttons */
        .btn-action-circle {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            border: 1px solid var(--cine-border);
            background: var(--cine-card-bg);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-action-view {
            color: var(--cine-primary);
        }
        .btn-action-view:hover {
            background: var(--cine-primary);
            color: #ffffff;
            border-color: var(--cine-primary);
            transform: scale(1.08);
            box-shadow: 0 4px 12px var(--cine-primary-glow);
        }
        .btn-action-edit {
            color: var(--cine-info);
        }
        .btn-action-edit:hover {
            background: var(--cine-info);
            color: #ffffff;
            border-color: var(--cine-info);
            transform: scale(1.08);
            box-shadow: 0 4px 12px var(--cine-info-glow);
        }
        .btn-action-delete {
            color: #ef4444;
        }
        .btn-action-delete:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* ─── Modal & Detail Tabs ────────────────────────────────── */
        .nav-tabs-custom {
            border-bottom: 2px solid var(--cine-border);
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--cine-text-muted);
            font-weight: 600;
            padding: 0.85rem 1.3rem;
            position: relative;
            background: transparent;
            transition: all 0.2s ease;
        }
        .nav-tabs-custom .nav-link:hover {
            color: var(--cine-primary);
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--cine-primary);
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--cine-primary);
            border-radius: 2px;
        }

        .customer-detail-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--cine-text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        .customer-detail-val {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--cine-text-main);
        }
        .card-detail-section {
            background-color: var(--cine-card-sub);
            border: 1px solid var(--cine-border);
            border-radius: 14px;
            padding: 1.25rem;
        }

        .card-advanced-options {
            border: 1.5px dashed var(--cine-border) !important;
            border-radius: 12px;
            overflow: visible !important;
            background: transparent !important;
        }
        .card-advanced-options .collapse.show,
        .card-advanced-options .collapsing {
            overflow: visible !important;
        }
        .header-advanced-toggle {
            cursor: pointer;
            user-select: none;
            background-color: var(--cine-card-sub) !important;
            transition: background-color 0.2s;
        }
        .header-advanced-toggle:hover {
            opacity: 0.9;
        }
        .header-advanced-toggle[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }
        .header-advanced-toggle .bi-chevron-down {
            transition: transform 0.2s ease;
        }

        .address-card, .contact-card {
            background-color: var(--cine-card-bg) !important;
            border: 1px solid var(--cine-border) !important;
            border-radius: 12px !important;
            transition: all 0.25s ease;
        }
        .address-card:hover, .contact-card:hover {
            border-color: var(--cine-primary) !important;
            box-shadow: 0 4px 14px var(--cine-primary-glow) !important;
        }
        .address-card .card-header, .contact-card .card-header {
            background-color: var(--cine-card-sub) !important;
            border-bottom: 1px solid var(--cine-border) !important;
        }

        .form-control-custom {
            background-color: var(--cine-card-bg) !important;
            color: var(--cine-text-main) !important;
            border: 1px solid var(--cine-border) !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: var(--cine-primary) !important;
            box-shadow: 0 0 0 3px var(--cine-primary-glow) !important;
        }

        /* Modal Dialog Styling */
        .modal-content {
            background-color: var(--cine-card-bg) !important;
            border: 1px solid var(--cine-border) !important;
            color: var(--cine-text-main) !important;
        }
        #modalTambahCustomer .modal-content-custom,
        #modalEditCustomer .modal-content-custom {
            overflow: visible !important;
        }
        .modal-header, .modal-footer {
            border-color: var(--cine-border) !important;
        }

        /* ─── Select2 Modern Theme Integration ───────────────────── */
        .select2-container--bootstrap-5 {
            display: block;
            width: 100% !important;
        }
        .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--cine-card-bg) !important;
            border: 1px solid var(--cine-border) !important;
            border-radius: 8px !important;
            min-height: 38px !important;
            padding: 5px 12px !important;
            display: flex !important;
            align-items: center !important;
            color: var(--cine-text-main) !important;
            transition: all 0.2s ease;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--cine-text-main) !important;
            font-size: 0.88rem !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
            line-height: 1.5 !important;
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--cine-primary) !important;
            box-shadow: 0 0 0 3px var(--cine-primary-glow) !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--cine-card-bg) !important;
            border: 1px solid var(--cine-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 4px 12px rgba(0, 0, 0, 0.2) !important;
            z-index: 99999 !important;
            overflow: hidden !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown--above {
            border-bottom: 1px solid var(--cine-border) !important;
            margin-top: -3px !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown--below {
            border-top: 1px solid var(--cine-border) !important;
            margin-top: 3px !important;
        }
        .select2-container--bootstrap-5 .select2-search {
            padding: 8px 8px 4px 8px !important;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--cine-card-sub) !important;
            border: 1.5px solid var(--cine-primary) !important;
            border-radius: 8px !important;
            color: var(--cine-text-main) !important;
            font-size: 0.84rem !important;
            padding: 7px 12px !important;
            width: 100% !important;
            outline: none !important;
            box-shadow: 0 0 0 2px var(--cine-primary-glow) !important;
        }
        .select2-container--bootstrap-5 .select2-search__field:focus {
            border-color: var(--cine-primary) !important;
            box-shadow: 0 0 0 3px var(--cine-primary-glow) !important;
        }
        .select2-container--bootstrap-5 .select2-results__options {
            max-height: 180px !important;
            overflow-y: auto !important;
            padding: 6px !important;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        .select2-container--bootstrap-5 .select2-results__options::-webkit-scrollbar {
            width: 5px;
        }
        .select2-container--bootstrap-5 .select2-results__options::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 8px 12px !important;
            font-size: 0.84rem !important;
            border-radius: 6px !important;
            margin-bottom: 2px !important;
            color: var(--cine-text-main) !important;
            transition: all 0.15s ease;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--cine-primary) !important;
            color: #ffffff !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: var(--cine-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        /* Dark Mode Select2 */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        .dark-mode .select2-container--bootstrap-5 .select2-selection {
            background-color: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f9fafb !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        .dark-mode .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f9fafb !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown,
        .dark-mode .select2-container--bootstrap-5 .select2-dropdown {
            background-color: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f9fafb !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        .dark-mode .select2-container--bootstrap-5 .select2-search__field {
            background-color: #1f293d !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f9fafb !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option {
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .form-select-sm + .select2-container--bootstrap-5 .select2-selection {
            min-height: 31px !important;
            padding: 2px 8px !important;
            font-size: 0.82rem !important;
        }
    </style>
    @endpush

    {{-- ===== CINEMATIC SUMMARY CARDS ===== --}}
    @php
        $countTotal       = $countTotal ?? 0;
        $countActive      = $countActive ?? 0;
        $countDistributor = $countDistributor ?? 0;
        $countRetail      = $countRetail ?? 0;
    @endphp
    <div class="container-fluid pt-3 px-2 px-md-4">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card summary-card-total">
                    <div>
                        <div class="summary-title">Total Customer</div>
                        <div class="summary-value summary-value-total">{{ number_format($countTotal) }}</div>
                    </div>
                    <div class="summary-icon summary-icon-total">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card summary-card-active">
                    <div>
                        <div class="summary-title">Customer Aktif</div>
                        <div class="summary-value summary-value-active">{{ number_format($countActive) }}</div>
                    </div>
                    <div class="summary-icon summary-icon-active">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card summary-card-distributor">
                    <div>
                        <div class="summary-title">Distributor & Agen</div>
                        <div class="summary-value summary-value-distributor">{{ number_format($countDistributor) }}</div>
                    </div>
                    <div class="summary-icon summary-icon-distributor">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="summary-card summary-card-retail">
                    <div>
                        <div class="summary-title">Retail & End User</div>
                        <div class="summary-value summary-value-retail">{{ number_format($countRetail) }}</div>
                    </div>
                    <div class="summary-icon summary-icon-retail">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Card -->
    <x-table_card tableId="tableCustomer" title="Data Customer / Pelanggan" icon="bi-people-fill"
        btnTarget="{{ auth()->user()?->can('tambah-customer') ? '#modalTambahCustomer' : '' }}"
        btnText="Tambah Customer">

        <x-slot name="extraActions">
            {{-- Tombol Export --}}
            @can('lihat-customer')
            <button type="button" id="btnExportCustomer"
                class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-1 px-3"
                title="Export data customer ke Excel"
                data-url="{{ route('customer.export') }}"
                data-filename="data_customer_{{ date('Ymd') }}.xlsx">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span class="d-none d-sm-inline">Export</span>
            </button>
            {{-- Tombol Import --}}
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-1 px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Import dari Excel">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span class="d-none d-sm-inline">Import</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 200px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" id="btnDownloadTemplateCustomer"
                            data-url="{{ route('customer.import.template') }}"
                            data-filename="template_import_customer.xlsx">
                            <i class="bi bi-download text-success"></i> Download Template
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalImportCustomer">
                            <i class="bi bi-upload text-primary"></i> Upload File Excel
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
        </x-slot>

        <x-slot name="thead">
            <th width="4%" class="text-center py-3">No</th>
            <th class="py-3" width="12%">Kode</th>
            <th class="py-3" width="24%">Nama / Perusahaan</th>
            <th class="py-3" width="18%">Jenis</th>
            <th class="py-3" width="18%">Hubungi</th>
            <th class="py-3" width="14%">Lokasi Utama</th>
            <th class="py-3 text-center" width="8%">Status</th>
            <th width="10%" class="text-center py-3">Aksi</th>
        </x-slot>
    </x-table_card>

    {{-- ===== MODAL IMPORT CUSTOMER ===== --}}
    @can('tambah-customer')
    <div class="modal fade" id="modalImportCustomer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content modal-content-custom bg-body">
                <div class="modal-header header-cinematic">
                    <h5 class="modal-title fw-semibold text-white">
                        <i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Import Customer dari Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formImportCustomer" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        {{-- Info --}}
                        <div class="alert alert-info rounded-3 small d-flex gap-2 align-items-start py-2 px-3 mb-3">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div>
                                Format file: <b>.xlsx</b> atau <b>.csv</b>.<br>
                                Download <a href="{{ route('customer.import.template') }}" class="fw-bold">template import</a> terlebih dahulu untuk memastikan format kolom benar.
                            </div>
                        </div>

                        {{-- File Upload --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="excel_file" id="import_customer_file"
                                class="form-control form-control-custom"
                                accept=".xlsx,.csv,.txt" required>
                            <div class="form-text text-muted">Maks. ukuran file: 5 MB</div>
                        </div>

                        {{-- Progress --}}
                        <div id="import_customer_progress" class="d-none">
                            <div class="progress rounded-pill mb-2" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
                            </div>
                            <p class="text-center text-muted small mb-0">Sedang memproses data, harap tunggu...</p>
                        </div>

                        {{-- Error List --}}
                        <div id="import_customer_errors" class="d-none">
                            <div class="alert alert-warning rounded-3 py-2 px-3 small mb-0">
                                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Beberapa baris dilewati:</div>
                                <ul class="mb-0 ps-3" id="import_customer_error_list" style="max-height:150px; overflow-y:auto;"></ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 py-3 pe-4 shadow-sm"
                        style="background-color: var(--cine-input-bg); border-top: 1px solid var(--cine-border-line) !important;">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-cinematic" id="btnImportCustomer">
                            <i class="bi bi-upload me-1"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- ===== MODAL TAMBAH ===== --}}
    <x-modal_form id="modalTambahCustomer" formId="formTambahCustomer" title="<i class='bi bi-people-fill me-2'></i> Tambah Customer Baru" autocomplete="off" novalidate>
        <x-slot name="slot">
            <!-- Chrome & Edge Autofill Trap -->
            <input type="text" name="_fake_autofill_user" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
            <input type="password" name="_fake_autofill_pwd" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="new-password">

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="tambahTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tambah-umum-tab" data-bs-toggle="tab" data-bs-target="#tambah-umum" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle me-1.5"></i> Info Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tambah-alamat-tab" data-bs-toggle="tab" data-bs-target="#tambah-alamat" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Pengiriman
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tambah-kontak-tab" data-bs-toggle="tab" data-bs-target="#tambah-kontak" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-person-lines-fill me-1.5"></i> Kontak Personil
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="tambahTabsContent">
                <!-- Tab 1: Info Umum -->
                <div class="tab-pane fade show active" id="tambah-umum" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Customer <span class="text-danger">*</span></label>
                                <span id="counter_tambah_nama_customer" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/60</span>
                            </div>
                            <input type="text" name="nama_customer" id="tambah_nama_customer"
                                class="form-control form-control-custom char-limited-input"
                                maxlength="60" data-max="60" data-name="Nama Customer"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                placeholder="Nama lengkap customer" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Customer <span class="text-danger">*</span></label>
                            <select name="jenis_customer" id="tambah_jenis_customer" class="form-select form-control-custom select2-searchable" data-placeholder="Pilih Jenis Customer" autocomplete="off" required>
                                <option value="Retail">Retail</option>
                                <option value="End User">End User</option>
                                <option value="Distributor">Distributor</option>
                                <option value="Agen">Agen</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Perusahaan / Toko</label>
                                <span id="counter_tambah_nama_perusahaan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/60</span>
                            </div>
                            <input type="text" name="nama_perusahaan" id="tambah_nama_perusahaan"
                                class="form-control form-control-custom char-limited-input"
                                maxlength="60" data-max="60" data-name="Nama Perusahaan"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                placeholder="Contoh: PT. Maju Bersama">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Handphone / WA</label>
                                <span id="counter_tambah_hp" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/13</span>
                            </div>
                            <input type="text" name="hp" id="tambah_hp"
                                class="form-control form-control-custom phone-number-input char-limited-input font-monospace"
                                maxlength="13" data-max="13" data-name="Handphone / WA"
                                inputmode="numeric" pattern="[0-9]*"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                placeholder="08XXXXXXXXXX">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="text" inputmode="email" name="email" id="tambah_email"
                                class="form-control form-control-custom"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                placeholder="email@domain.com">
                        </div>
                        
                        <!-- Collapsible Advanced Options -->
                        <div class="col-12">
                            <div class="card card-advanced-options shadow-none mt-2">
                                <div class="card-header py-2.5 px-3 border-0 d-flex justify-content-between align-items-center header-advanced-toggle" 
                                     data-bs-toggle="collapse" data-bs-target="#tambah_opsi_lanjutan" aria-expanded="false">
                                    <span class="fw-semibold text-secondary small"><i class="bi bi-sliders me-1.5 text-primary"></i> Opsi Lanjutan</span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                                <div id="tambah_opsi_lanjutan" class="collapse">
                                    <div class="card-body p-3 pb-4 border-top" style="background-color: var(--cine-card-bg);">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Kode Customer</label>
                                                    <span id="counter_tambah_kode_customer" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/15</span>
                                                </div>
                                                <input type="text" name="kode_customer" id="tambah_kode_customer"
                                                    class="form-control form-control-custom char-limited-input font-monospace"
                                                    maxlength="15" data-max="15" data-name="Kode Customer"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="Otomatis jika kosong">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Contact Person Utama</label>
                                                    <span id="counter_tambah_contact_person" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/30</span>
                                                </div>
                                                <input type="text" name="contact_person" id="tambah_contact_person"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="30" data-max="30" data-name="Contact Person Utama"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="Nama Utama">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Jabatan </label>
                                                    <span id="counter_tambah_jabatan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/30</span>
                                                </div>
                                                <input type="text" name="jabatan" id="tambah_jabatan"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="30" data-max="30" data-name="Jabatan "
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="Contoh: Manager Purchasing">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">No. Telepon Kantor</label>
                                                    <span id="counter_tambah_telepon" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/13</span>
                                                </div>
                                                <input type="text" name="telepon" id="tambah_telepon"
                                                    class="form-control form-control-custom phone-number-input char-limited-input font-monospace"
                                                    maxlength="13" data-max="13" data-name="No. Telepon Kantor"
                                                    inputmode="numeric" pattern="[0-9]*"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="021XXXXXXX">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Website</label>
                                                    <span id="counter_tambah_website" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <input type="text" name="website" id="tambah_website"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="40" data-max="40" data-name="Website"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="www.domain.com">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Mata Uang (Currency)</label>
                                                <select name="currency" id="tambah_currency" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Currency --" autocomplete="off">
                                                    <option value="">-- Pilih Currency --</option>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }} ({{ $curr->simbol }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Nomor NPWP</label>
                                                    <span id="counter_tambah_npwp" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <input type="text" name="npwp" id="tambah_npwp"
                                                    class="form-control form-control-custom char-limited-input font-monospace"
                                                    maxlength="40" data-max="40" data-name="Nomor NPWP"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                                    placeholder="00.000.000.0-000.000">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status PKP</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input class="form-check-input" type="checkbox" name="pkp" id="tambah_pkp" value="1">
                                                    <label class="form-check-label" for="tambah_pkp">Wajib Pajak</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status Keaktifan</label>
                                                <select name="status" id="tambah_status" class="form-select form-control-custom select2-searchable" data-placeholder="Pilih Status" autocomplete="off" required>
                                                    <option value="Active">Aktif</option>
                                                    <option value="Inactive">Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Term Pembayaran (Days)</label>
                                                <select name="payment_term_id" id="tambah_payment_term_id" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Term --" autocomplete="off">
                                                    <option value="">-- Pilih Term --</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->nama_term }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Batas Kredit (Credit Limit)</label>
                                                <input type="number" name="credit_limit" id="tambah_credit_limit"
                                                    class="form-control form-control-custom"
                                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    placeholder="Batas limit utang piutang" min="0">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Sales Representative</label>
                                                <select name="sales_id" id="tambah_sales_id" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Sales --" autocomplete="off">
                                                    <option value="">-- Pilih Sales --</option>
                                                    @foreach($salesList as $sales)
                                                        <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Keterangan Tambahan</label>
                                                    <span id="counter_tambah_keterangan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <textarea name="keterangan" id="tambah_keterangan" rows="2"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="40" data-max="40" data-name="Keterangan Tambahan"
                                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    placeholder="Catatan internal..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Alamat Pengiriman -->
                <div class="tab-pane fade" id="tambah-alamat" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Input minimal satu alamat pengiriman. Centang "Alamat Utama" untuk menetapkan alamat default.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_tambah_alamat_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_tambah_alamat">
                        <!-- Dinamis via JS (Address Cards) -->
                    </div>
                </div>

                <!-- Tab 3: Kontak Personil -->
                <div class="tab-pane fade" id="tambah-kontak" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar kontak penanggung jawab sekunder dari customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_tambah_kontak_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Personil
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_tambah_kontak">
                        <!-- Dinamis via JS (Contact Cards) -->
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    {{-- ===== MODAL EDIT ===== --}}
    <x-modal_form id="modalEditCustomer" formId="formEditCustomer" title="<i class='bi bi-pencil-square me-2'></i> Edit Data Customer" autocomplete="off" novalidate>
        <x-slot name="slot">
            <!-- Chrome & Edge Autofill Trap -->
            <input type="text" name="_fake_autofill_user_edit" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
            <input type="password" name="_fake_autofill_pwd_edit" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="new-password">

            <input type="hidden" name="id" id="edit_id">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="editTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="edit-umum-tab" data-bs-toggle="tab" data-bs-target="#edit-umum" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-info-circle me-1.5"></i> Info Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="edit-alamat-tab" data-bs-toggle="tab" data-bs-target="#edit-alamat" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Pengiriman
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="edit-kontak-tab" data-bs-toggle="tab" data-bs-target="#edit-kontak" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-person-lines-fill me-1.5"></i> Kontak Personil
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="editTabsContent">
                <!-- Tab 1: Info Umum -->
                <div class="tab-pane fade show active" id="edit-umum" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Customer <span class="text-danger">*</span></label>
                                <span id="counter_edit_nama_customer" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/60</span>
                            </div>
                            <input type="text" name="nama_customer" id="edit_nama_customer"
                                class="form-control form-control-custom char-limited-input"
                                maxlength="60" data-max="60" data-name="Nama Customer"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Customer <span class="text-danger">*</span></label>
                            <select name="jenis_customer" id="edit_jenis_customer" class="form-select form-control-custom select2-searchable" data-placeholder="Pilih Jenis Customer" autocomplete="off" required>
                                <option value="Retail">Retail</option>
                                <option value="End User">End User</option>
                                <option value="Distributor">Distributor</option>
                                <option value="Agen">Agen</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Nama Perusahaan / Toko</label>
                                <span id="counter_edit_nama_perusahaan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/60</span>
                            </div>
                            <input type="text" name="nama_perusahaan" id="edit_nama_perusahaan"
                                class="form-control form-control-custom char-limited-input"
                                maxlength="60" data-max="60" data-name="Nama Perusahaan"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Handphone / WA</label>
                                <span id="counter_edit_hp" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/13</span>
                            </div>
                            <input type="text" name="hp" id="edit_hp"
                                class="form-control form-control-custom phone-number-input char-limited-input font-monospace"
                                maxlength="13" data-max="13" data-name="Handphone / WA"
                                inputmode="numeric" pattern="[0-9]*"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="text" inputmode="email" name="email" id="edit_email"
                                class="form-control form-control-custom"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                        </div>
                        
                        <!-- Collapsible Advanced Options -->
                        <div class="col-12">
                            <div class="card card-advanced-options shadow-none mt-2">
                                <div class="card-header py-2.5 px-3 border-0 d-flex justify-content-between align-items-center header-advanced-toggle" 
                                     data-bs-toggle="collapse" data-bs-target="#edit_opsi_lanjutan" aria-expanded="false">
                                    <span class="fw-semibold text-secondary small"><i class="bi bi-sliders me-1.5 text-primary"></i> Opsi Lanjutan</span>
                                    <i class="bi bi-chevron-down text-muted small"></i>
                                </div>
                                <div id="edit_opsi_lanjutan" class="collapse">
                                    <div class="card-body p-3 pb-4 border-top" style="background-color: var(--cine-card-bg);">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Kode Customer</label>
                                                    <span id="counter_edit_kode_customer" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/15</span>
                                                </div>
                                                <input type="text" name="kode_customer" id="edit_kode_customer"
                                                    class="form-control form-control-custom char-limited-input font-monospace"
                                                    maxlength="15" data-max="15" data-name="Kode Customer"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" required>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Contact Person Utama</label>
                                                    <span id="counter_edit_contact_person" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/30</span>
                                                </div>
                                                <input type="text" name="contact_person" id="edit_contact_person"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="30" data-max="30" data-name="Contact Person Utama"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Jabatan </label>
                                                    <span id="counter_edit_jabatan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/30</span>
                                                </div>
                                                <input type="text" name="jabatan" id="edit_jabatan"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="30" data-max="30" data-name="Jabatan"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">No. Telepon Kantor</label>
                                                    <span id="counter_edit_telepon" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/13</span>
                                                </div>
                                                <input type="text" name="telepon" id="edit_telepon"
                                                    class="form-control form-control-custom phone-number-input char-limited-input font-monospace"
                                                    maxlength="13" data-max="13" data-name="No. Telepon Kantor"
                                                    inputmode="numeric" pattern="[0-9]*"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Website</label>
                                                    <span id="counter_edit_website" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <input type="text" name="website" id="edit_website"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="40" data-max="40" data-name="Website"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Mata Uang (Currency)</label>
                                                <select name="currency" id="edit_currency" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Currency --" autocomplete="off">
                                                    <option value="">-- Pilih Currency --</option>
                                                    @foreach($currencies as $curr)
                                                        <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }} ({{ $curr->simbol }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Nomor NPWP</label>
                                                    <span id="counter_edit_npwp" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <input type="text" name="npwp" id="edit_npwp"
                                                    class="form-control form-control-custom char-limited-input font-monospace"
                                                    maxlength="40" data-max="40" data-name="Nomor NPWP"
                                                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                    readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status PKP</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input class="form-check-input" type="checkbox" name="pkp" id="edit_pkp" value="1">
                                                    <label class="form-check-label" for="edit_pkp">Wajib Pajak</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label fw-semibold">Status Keaktifan</label>
                                                <select name="status" id="edit_status" class="form-select form-control-custom select2-searchable" data-placeholder="Pilih Status" autocomplete="off" required>
                                                    <option value="Active">Aktif</option>
                                                    <option value="Inactive">Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Term Pembayaran (Days)</label>
                                                <select name="payment_term_id" id="edit_payment_term_id" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Term --" autocomplete="off">
                                                    <option value="">-- Pilih Term --</option>
                                                    @foreach($paymentTerms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->nama_term }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Batas Kredit (Credit Limit)</label>
                                                <input type="number" name="credit_limit" id="edit_credit_limit"
                                                    class="form-control form-control-custom"
                                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other" min="0">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label fw-semibold">Sales Representative</label>
                                                <select name="sales_id" id="edit_sales_id" class="form-select form-control-custom select2-searchable" data-placeholder="-- Pilih Sales --" autocomplete="off">
                                                    <option value="">-- Pilih Sales --</option>
                                                    @foreach($salesList as $sales)
                                                        <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="form-label fw-semibold mb-0">Keterangan Tambahan</label>
                                                    <span id="counter_edit_keterangan" class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">0/40</span>
                                                </div>
                                                <textarea name="keterangan" id="edit_keterangan" rows="2"
                                                    class="form-control form-control-custom char-limited-input"
                                                    maxlength="40" data-max="40" data-name="Keterangan Tambahan"
                                                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Alamat Pengiriman -->
                <div class="tab-pane fade" id="edit-alamat" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar alamat pengiriman yang terdaftar untuk customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_edit_alamat_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alamat
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_edit_alamat">
                        <!-- Dinamis via JS (Address Cards) -->
                    </div>
                </div>

                <!-- Tab 3: Kontak Personil -->
                <div class="tab-pane fade" id="edit-kontak" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="text-muted small"><i class="bi bi-info-circle"></i> Daftar kontak penanggung jawab sekunder dari customer ini.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn_edit_kontak_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Personil
                        </button>
                    </div>
                    <div class="pe-1" style="max-height: 400px; overflow-y: auto; overflow-x: hidden;" id="container_edit_kontak">
                        <!-- Dinamis via JS (Contact Cards) -->
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal_form>

    {{-- ===== MODAL DETAIL SHOW ===== --}}
    <div class="modal fade" id="modalDetailCustomer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--cine-primary);"><i class="bi bi-info-circle-fill me-2"></i> Profil & Spesifikasi Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div class="row g-3">
                        <!-- Summary Section -->
                        <div class="col-12">
                            <div class="card-detail-section d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fs-4 fw-bold" id="detail_nama_customer">-</div>
                                    <div class="text-muted small" id="detail_nama_perusahaan">-</div>
                                </div>
                                <div class="text-end">
                                    <span class="customer-code-badge" id="detail_kode_customer">-</span>
                                    <div class="mt-1" id="detail_status_badge"></div>
                                </div>
                            </div>
                        </div>

                        <!-- General Info Column 1 -->
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Jenis Customer</div>
                            <div class="customer-detail-val" id="detail_jenis_customer">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Mata Uang</div>
                            <div class="customer-detail-val" id="detail_currency">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Batas Kredit</div>
                            <div class="customer-detail-val" style="color: var(--cine-primary);" id="detail_credit_limit">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Term Pembayaran</div>
                            <div class="customer-detail-val" id="detail_payment_term">-</div>
                        </div>

                        <!-- General Info Column 2 -->
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label"> Utama</div>
                            <div class="customer-detail-val" id="detail_contact_person">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Jabatan </div>
                            <div class="customer-detail-val" id="detail_jabatan">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Sales Rep</div>
                            <div class="customer-detail-val" id="detail_sales">-</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="customer-detail-label">Status PKP</div>
                            <div class="customer-detail-val" id="detail_pkp">-</div>
                        </div>

                        <div class="col-12"><hr class="my-1" style="border-color: var(--cine-border);"></div>

                        <!-- Contacts Column -->
                        <div class="col-6 col-md-4">
                            <div class="customer-detail-label">Telepon Kantor</div>
                            <div class="customer-detail-val font-monospace" id="detail_telepon">-</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="customer-detail-label">Handphone/WA</div>
                            <div class="customer-detail-val font-monospace" id="detail_hp">-</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="customer-detail-label">Email</div>
                            <div class="customer-detail-val" id="detail_email">-</div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="customer-detail-label">NPWP</div>
                            <div class="customer-detail-val font-monospace" id="detail_npwp">-</div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="customer-detail-label">Website</div>
                            <div class="customer-detail-val" id="detail_website">-</div>
                        </div>

                        <!-- Tabs Detail sub relations -->
                        <div class="col-12">
                            <ul class="nav nav-tabs nav-tabs-custom mb-3" id="detailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="detail-alamat-tab" data-bs-toggle="tab" data-bs-target="#detail-alamat" type="button" role="tab">
                                        <i class="bi bi-geo-alt me-1.5"></i> Alamat Terdaftar
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="detail-kontak-tab" data-bs-toggle="tab" data-bs-target="#detail-kontak" type="button" role="tab">
                                        <i class="bi bi-person-lines-fill me-1.5"></i> Personil Kontak
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="detailTabsContent">
                                <!-- Tab 1: Alamat -->
                                <div class="tab-pane fade show active" id="detail-alamat" role="tabpanel">
                                    <div id="detail_alamat_list" class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                                        <!-- render via JS -->
                                    </div>
                                </div>
                                <!-- Tab 2: Kontak -->
                                <div class="tab-pane fade" id="detail-kontak" role="tabpanel">
                                    <div class="table-responsive border rounded" style="border-color: var(--cine-border) !important;">
                                        <table class="table table-sm table-striped mb-0 align-middle small" style="color: var(--cine-text-main);">
                                            <thead>
                                                <tr style="background-color: var(--cine-card-sub); border-bottom: 1px solid var(--cine-border);">
                                                    <th class="py-2 px-3">Nama</th>
                                                    <th class="py-2">Jabatan</th>
                                                    <th class="py-2">No. HP</th>
                                                    <th class="py-2">Email</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detail_kontak_table_body">
                                                <!-- render via JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Internal Note -->
                        <div class="col-12">
                            <div class="customer-detail-label">Catatan Keterangan</div>
                            <div class="p-3 rounded-3 mt-1 small" id="detail_keterangan" style="background-color: var(--cine-card-sub); border: 1px solid var(--cine-border); white-space: pre-wrap; color: var(--cine-text-main);">-</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Helper to format currency
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        // Security helper to escape HTML entities (XSS protection)
        function escapeHtml(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Enforce disable autocomplete across all inputs and forms (Chrome address autofill blocker)
        function enforceDisableAutocomplete() {
            $('form#formTambahCustomer, form#formEditCustomer').attr('autocomplete', 'off').attr('novalidate', 'novalidate');
            $('form#formTambahCustomer input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(.select2-search__field), form#formTambahCustomer textarea, form#formEditCustomer input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not(.select2-search__field), form#formEditCustomer textarea').each(function() {
                let $el = $(this);
                $el.attr('autocomplete', 'new-password')
                   .attr('autocorrect', 'off')
                   .attr('autocapitalize', 'off')
                   .attr('spellcheck', 'false')
                   .attr('data-lpignore', 'true')
                   .attr('data-1p-ignore', 'true')
                   .attr('data-form-type', 'other');

                if (!$el.is(':focus')) {
                    $el.attr('readonly', 'readonly');
                }
            });
            $('#tableCustomer_filter input').attr('autocomplete', 'off').attr('spellcheck', 'false');
        }

        // ===== INITIALIZE SEARCHABLE SELECT2 =====
        function initCustomerSelect2($scope) {
            if (!$.fn.select2) return;

            let targetScope = $scope || $(document);
            targetScope.find('select.select2-searchable').each(function () {
                let $this = $(this);
                let placeholder = $this.data('placeholder') || $this.find('option:first').text() || 'Pilih...';

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $(document.body),
                    placeholder: placeholder,
                    width: '100%',
                    allowClear: false,
                    minimumResultsForSearch: 0
                });
            });
        }

        $(document).ready(function () {
            // Tutup dropdown jika modal di-scroll agar posisi selalu sinkron dan rapi
            $('.modal').on('scroll', function () {
                if ($('.select2-container--open').length) {
                    $('select.select2-searchable').select2('close');
                }
            });

            // Matikan enkapsulasi fokus Bootstrap modal agar search field Select2 tidak langsung menutup dropdown
            $('#modalTambahCustomer, #modalEditCustomer, #modalDetailCustomer, #modalImportCustomer').attr('data-bs-focus', 'false');
            if ($.fn.modal && $.fn.modal.Constructor && $.fn.modal.Constructor.prototype) {
                $.fn.modal.Constructor.prototype._enforceFocus = function () {};
            }
            $(document).off('focusin.modal');

            enforceDisableAutocomplete();

            // Auto focus search field when Select2 opens and ensure not readonly
            $(document).on('select2:open', function () {
                let container = document.querySelector('.select2-container--open');
                if (container) {
                    let searchField = container.querySelector('.select2-search__field');
                    if (searchField) {
                        searchField.removeAttribute('readonly');
                        searchField.setAttribute('autocomplete', 'off');
                        searchField.setAttribute('autocorrect', 'off');
                        searchField.setAttribute('autocapitalize', 'off');
                        searchField.setAttribute('spellcheck', 'false');
                        setTimeout(function () {
                            searchField.focus();
                        }, 40);
                    }
                }
            });

            // When user interacts with input, unlock readonly synchronously so typing works smoothly
            $(document).on('pointerdown touchstart focus mousedown', '#modalTambahCustomer input:not(.select2-search__field), #modalEditCustomer input:not(.select2-search__field), #modalTambahCustomer textarea, #modalEditCustomer textarea', function() {
                $(this).removeAttr('readonly').prop('readonly', false);
            });

            // When user leaves the input, lock with readonly again if not focused to prevent browser autofill on next focus
            $(document).on('blur', '#modalTambahCustomer input:not(.select2-search__field), #modalEditCustomer input:not(.select2-search__field), #modalTambahCustomer textarea, #modalEditCustomer textarea', function() {
                let $el = $(this);
                setTimeout(function() {
                    if (!$el.is(':focus')) {
                        $el.attr('readonly', 'readonly');
                    }
                }, 50);
            });

            // Ensure readonly is removed right before submission so serialization never misses anything
            $(document).on('submit', '#formTambahCustomer, #formEditCustomer', function() {
                $(this).find('input, textarea').removeAttr('readonly').prop('readonly', false);
            });

            // ===== INITIALIZE DATATABLE IN SERVER-SIDE MODE =====
            const table = $('#tableCustomer').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("customer.data") }}',
                    type: 'GET'
                },
                dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    search: 'Search:',
                    paginate: { previous: '<', next: '>' },
                    emptyTable: '<div class="text-center text-muted py-4"><i class="bi bi-people fs-2 d-block mb-2 opacity-25"></i>Belum ada data customer.</div>',
                    zeroRecords: 'Tidak ditemukan data yang cocok'
                },
                initComplete: function() {
                    $('#tableCustomer_filter input')
                        .attr('autocomplete', 'off')
                        .attr('autocorrect', 'off')
                        .attr('autocapitalize', 'off')
                        .attr('spellcheck', 'false')
                        .attr('placeholder', 'Cari kode, nama, kontak...');
                },
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50, 100],
                columns: [
                    { data: null, orderable: false, searchable: false, className: 'text-center' },
                    { data: 'kode_customer' },
                    { data: 'nama_customer' },
                    { data: 'jenis_customer' },
                    { data: 'telepon' },
                    { data: 'location' },
                    { data: 'status', className: 'text-center' },
                    { data: 'id', orderable: false, searchable: false, className: 'text-center' }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data, type, row, meta) {
                            return `<span class="text-muted fw-semibold font-monospace">${meta.row + meta.settings._iDisplayStart + 1}</span>`;
                        }
                    },
                    {
                        targets: 1,
                        render: function (data, type, row) {
                            return `<span class="customer-code-badge">${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        targets: 2,
                        render: function (data, type, row) {
                            let comp = row.nama_perusahaan !== '-' ? `<div class="customer-company-sub"><i class="bi bi-building"></i>${escapeHtml(row.nama_perusahaan)}</div>` : '';
                            return `<div class="customer-name-title">${escapeHtml(data)}</div>${comp}`;
                        }
                    },
                    {
                        targets: 3,
                        render: function (data, type, row) {
                            let typeClass = 'badge-type-pill badge-type-retail';
                            let icon = 'bi-cart';
                            if (data === 'Distributor') {
                                typeClass = 'badge-type-pill badge-type-distributor';
                                icon = 'bi-diagram-3';
                            } else if (data === 'Agen') {
                                typeClass = 'badge-type-pill badge-type-agen';
                                icon = 'bi-shop';
                            } else if (data === 'End User') {
                                typeClass = 'badge-type-pill badge-type-enduser';
                                icon = 'bi-person';
                            }
                            
                            let cp = row.contact_person !== '-' ? `<div class="customer-cp-sub"><i class="bi bi-person-fill"></i>${escapeHtml(row.contact_person)}</div>` : '';
                            return `
                                <span class="${typeClass}"><i class="bi ${icon}"></i> ${escapeHtml(data)}</span>
                                ${cp}
                            `;
                        }
                    },
                    {
                        targets: 4,
                        render: function (data, type, row) {
                            let emailInfo = row.email !== '-' ? `<div class="customer-email-sub"><i class="bi bi-envelope-at"></i>${escapeHtml(row.email)}</div>` : '';
                            let phoneIcon = data !== '-' ? '<i class="bi bi-telephone-fill me-1 opacity-75"></i>' : '';
                            return `<div class="customer-phone">${phoneIcon}${escapeHtml(data)}</div>${emailInfo}`;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            let pinIcon = data !== '-' ? '<i class="bi bi-geo-alt-fill text-danger me-1"></i>' : '';
                            return `<span class="customer-location">${pinIcon}${escapeHtml(data)}</span>`;
                        }
                    },
                    {
                        targets: 6,
                        render: function (data, type, row) {
                            if (data === 'Active') {
                                return `<span class="badge-status-pill badge-status-active"><i class="bi bi-check-circle-fill"></i> Aktif</span>`;
                            }
                            return `<span class="badge-status-pill badge-status-inactive"><i class="bi bi-x-circle-fill"></i> Nonaktif</span>`;
                        }
                    },
                    {
                        targets: 7,
                        render: function (data, type, row) {
                            let detailBtn = `<button class="btn-action-circle btn-action-view btn-detail" data-id="${data}" title="Lihat Detail Profil"><i class="bi bi-eye"></i></button>`;
                            let editBtn = '';
                            let deleteBtn = '';
                            
                            @can('edit-customer')
                            editBtn = `<button class="btn-action-circle btn-action-edit btn-edit" data-id="${data}" title="Edit Data"><i class="bi bi-pencil"></i></button>`;
                            @endcan
                            
                            @can('hapus-customer')
                            deleteBtn = `<button class="btn-action-circle btn-action-delete btn-delete" data-id="${data}" data-name="${escapeHtml(row.nama_customer)}" title="Hapus"><i class="bi bi-trash"></i></button>`;
                            @endcan
                            
                            return `<div class="d-flex justify-content-center gap-1.5">${detailBtn} ${editBtn} ${deleteBtn}</div>`;
                        }
                    }
                ]
            });

            // ===== DYNAMIC SUB CARDS HELPERS =====
            let addrIndex = 0;
            let contactIndex = 0;

            function addAddressCard(prefix, addressData = null) {
                let uniqueId = addrIndex++;
                let container = $(`#container_${prefix}_alamat`);

                let jenis = addressData ? addressData.jenis_alamat : 'Kantor';
                let penerima = addressData ? addressData.nama_penerima : '';
                let telp = addressData ? addressData.telepon : '';
                let alamat = addressData ? addressData.alamat : '';
                let kota = addressData ? addressData.kota : '';
                let prov = addressData ? addressData.provinsi : '';
                let pos = addressData ? addressData.kode_pos : '';
                let defaultChecked = (addressData && (addressData.is_default == 1 || addressData.is_default == true)) ? 'checked' : '';
                if (!addressData && container.children().length === 0) {
                    defaultChecked = 'checked'; // Default checked for first row
                }

                // Determine badge class
                let badgeClass = 'badge-type-distributor';
                if (jenis === 'Gudang') badgeClass = 'badge-type-agen';
                else if (jenis === 'Toko') badgeClass = 'badge-type-retail';
                else if (jenis === 'Rumah') badgeClass = 'badge-type-enduser';
                else if (jenis === 'Lainnya') badgeClass = 'badge-type-distributor';

                let html = `
                    <div class="card address-card mb-3 position-relative">
                        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge-type-pill ${badgeClass} text-uppercase" style="font-size: 0.68rem;" id="badge_jenis_${prefix}_${uniqueId}">${jenis}</span>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input address-default-radio" type="radio" 
                                        name="${prefix}_address_default" value="${uniqueId}" ${defaultChecked}>
                                    <input type="hidden" name="addresses[${uniqueId}][is_default]" class="address-default-val" value="${defaultChecked ? '1' : '0'}">
                                    <label class="form-check-label small fw-bold" style="font-size: 0.76rem; color: var(--cine-primary);">Alamat Utama</label>
                                </div>
                            </div>
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove-address-card" title="Hapus Alamat" style="text-decoration: none;">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Jenis Alamat</label>
                                    <select name="addresses[${uniqueId}][jenis_alamat]" class="form-select form-select-sm form-control-custom select-jenis-alamat select2-searchable" data-placeholder="Pilih Jenis Alamat" data-target="badge_jenis_${prefix}_${uniqueId}" autocomplete="off" required>
                                        <option value="Kantor" ${jenis === 'Kantor' ? 'selected' : ''}>Kantor</option>
                                        <option value="Gudang" ${jenis === 'Gudang' ? 'selected' : ''}>Gudang</option>
                                        <option value="Toko" ${jenis === 'Toko' ? 'selected' : ''}>Toko</option>
                                        <option value="Rumah" ${jenis === 'Rumah' ? 'selected' : ''}>Rumah</option>
                                        <option value="Lainnya" ${jenis === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Nama Penerima <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][nama_penerima]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input addr-penerima" 
                                        maxlength="40" data-max="40" data-name="Nama Penerima Alamat"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Nama Penerima" value="${escapeHtml(penerima)}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Telepon Penerima</label>
                                    <input type="text" name="addresses[${uniqueId}][telepon]" 
                                        class="form-control form-control-sm form-control-custom phone-number-input char-limited-input font-monospace addr-telepon" 
                                        maxlength="13" data-max="13" data-name="Telepon Penerima" inputmode="numeric" pattern="[0-9]*"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="08XXXXXXXXXX" value="${escapeHtml(telp)}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea name="addresses[${uniqueId}][alamat]" class="form-control form-control-sm form-control-custom char-limited-input addr-alamat" 
                                        maxlength="70" data-max="70" data-name="Alamat Lengkap"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        rows="2" placeholder="Nama Jalan, RT/RW, Blok, No. Rumah, Kecamatan...">${escapeHtml(alamat)}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Kota / Kabupaten <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][kota]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input addr-kota" 
                                        maxlength="30" data-max="30" data-name="Kota / Kabupaten"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Kota" value="${escapeHtml(kota)}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Provinsi <span class="text-danger">*</span></label>
                                    <input type="text" name="addresses[${uniqueId}][provinsi]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input addr-provinsi" 
                                        maxlength="30" data-max="30" data-name="Provinsi"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Provinsi" value="${escapeHtml(prov)}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Kode Pos</label>
                                    <input type="text" name="addresses[${uniqueId}][kode_pos]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input font-monospace addr-kodepos" 
                                        maxlength="15" data-max="15" data-name="Kode Pos"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Kode Pos" value="${escapeHtml(pos)}">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(html);
                let modalParent = prefix === 'tambah' ? $('#modalTambahCustomer') : $('#modalEditCustomer');
                let newCard = container.children('.address-card').last();
                initCustomerSelect2(newCard, modalParent);
                enforceDisableAutocomplete();
            }

            function addContactCard(prefix, contactData = null) {
                let uniqueId = contactIndex++;
                let container = $(`#container_${prefix}_kontak`);

                let nama = contactData ? contactData.nama : '';
                let jabatan = contactData ? contactData.jabatan : '';
                let hp = contactData ? contactData.hp : '';
                let email = contactData ? contactData.email : '';

                let html = `
                    <div class="card contact-card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="fw-bold small" style="color: var(--cine-text-main);"><i class="bi bi-person-fill me-1" style="color: var(--cine-primary);"></i> Personil Kontak</span>
                            <button type="button" class="btn btn-link text-danger p-0 btn-remove-contact-card" title="Hapus Kontak" style="text-decoration: none;">
                                <i class="bi bi-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Nama Lengkap</label>
                                    <input type="text" name="contacts[${uniqueId}][nama]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input contact-nama" 
                                        maxlength="60" data-max="60" data-name="Nama Lengkap Kontak"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Nama Lengkap" value="${escapeHtml(nama)}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Jabatan</label>
                                    <input type="text" name="contacts[${uniqueId}][jabatan]" 
                                        class="form-control form-control-sm form-control-custom char-limited-input contact-jabatan" 
                                        maxlength="30" data-max="30" data-name="Jabatan Kontak"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="Contoh: Manager Finance" value="${escapeHtml(jabatan)}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">No. HP / WhatsApp</label>
                                    <input type="text" name="contacts[${uniqueId}][hp]" 
                                        class="form-control form-control-sm form-control-custom phone-number-input char-limited-input font-monospace contact-hp" 
                                        maxlength="13" data-max="13" data-name="No. HP Kontak" inputmode="numeric" pattern="[0-9]*"
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="08XXXXXXXXXX" value="${escapeHtml(hp)}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold mb-1">Email</label>
                                    <input type="text" inputmode="email" name="contacts[${uniqueId}][email]" 
                                        class="form-control form-control-sm form-control-custom contact-email" 
                                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');"
                                        placeholder="email@domain.com" value="${escapeHtml(email)}">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.append(html);
                enforceDisableAutocomplete();
            }

            // Sync dynamic default radios to hidden values
            $(document).on('change', '.address-default-radio', function() {
                let container = $(this).closest('.pe-1');
                container.find('.address-default-val').val('0');
                $(this).closest('.address-card').find('.address-default-val').val('1');
            });

            // Handle live badge text/color updates when Jenis Alamat select changes
            $(document).on('change', '.select-jenis-alamat', function() {
                let targetId = $(this).data('target');
                let val = $(this).val();
                let badge = $(`#${targetId}`);
                badge.text(val);
                
                badge.removeClass('badge-type-distributor badge-type-agen badge-type-retail badge-type-enduser');
                if (val === 'Kantor') badge.addClass('badge-type-distributor');
                else if (val === 'Gudang') badge.addClass('badge-type-agen');
                else if (val === 'Toko') badge.addClass('badge-type-retail');
                else if (val === 'Rumah') badge.addClass('badge-type-enduser');
                else badge.addClass('badge-type-distributor');
            });

            // Remove card handlers
            $(document).on('click', '.btn-remove-address-card', function() {
                let card = $(this).closest('.address-card');
                let wasDefault = card.find('.address-default-radio').is(':checked');
                let container = card.closest('.pe-1');
                
                card.remove();
                
                // Reset default option to the first remaining card if default was deleted
                if (wasDefault && container.children().length > 0) {
                    container.find('.address-default-radio').first().prop('checked', true).trigger('change');
                }
            });

            $(document).on('click', '.btn-remove-contact-card', function() {
                $(this).closest('.contact-card').remove();
            });

            // Bind click buttons
            $('#btn_tambah_alamat_row').on('click', () => addAddressCard('tambah'));
            $('#btn_edit_alamat_row').on('click', () => addAddressCard('edit'));
            $('#btn_tambah_kontak_row').on('click', () => addContactCard('tambah'));
            $('#btn_edit_kontak_row').on('click', () => addContactCard('edit'));

            // ===== HELPER: UPDATE CHARACTER COUNTERS =====
            function updateCharCounter($input) {
                let max = parseInt($input.data('max') || $input.attr('maxlength') || 0);
                let val = $input.val() || '';
                let id = $input.attr('id');
                let counterId = '#counter_' + id;

                if (max > 0 && $(counterId).length) {
                    let len = val.length;
                    $(counterId).text(`${len}/${max}`);
                    if (len >= max) {
                        $(counterId).removeClass('bg-secondary-subtle text-secondary bg-warning-subtle text-dark')
                            .addClass('bg-danger text-white');
                    } else if (len >= max * 0.85) {
                        $(counterId).removeClass('bg-secondary-subtle text-secondary bg-danger text-white')
                            .addClass('bg-warning-subtle text-dark');
                    } else {
                        $(counterId).removeClass('bg-warning-subtle text-dark bg-danger text-white')
                            .addClass('bg-secondary-subtle text-secondary');
                    }
                }
            }

            function updateAllCharCounters(formSelector) {
                $(formSelector).find('.char-limited-input').each(function () {
                    updateCharCounter($(this));
                });
            }

            // ===== REAL-TIME INPUT CHARACTER LIMITER & SECURITY ALERT =====
            $(document).on('input keyup paste change', '.char-limited-input', function (e) {
                let $input = $(this);
                let max = parseInt($input.data('max') || $input.attr('maxlength') || 0);
                let name = $input.data('name') || 'Bidang isian';
                let val = $input.val() || '';

                if (max > 0) {
                    let len = val.length;

                    // If exceeds limit, truncate
                    if (len > max) {
                        $input.val(val.substring(0, max));
                    }

                    updateCharCounter($input);

                    // Warning alert if limit reached on user typing/pasting
                    if (len >= max && (e.type === 'paste' || e.type === 'keyup' || e.type === 'input')) {
                        if (!$input.data('toast-active')) {
                            $input.data('toast-active', true);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Batas Maksimal Tercapai!',
                                html: `
                                    <div class="text-center p-1">
                                        <p class="mb-2">Bidang <strong>${escapeHtml(name)}</strong> telah mencapai batas maksimal <strong>${max} karakter</strong>.</p>
                                        <div class="alert alert-warning py-2 px-3 rounded-3 small mb-0 d-inline-block">
                                            <i class="bi bi-shield-exclamation me-1"></i> Karakter melebihi batas tidak akan disimpan.
                                        </div>
                                    </div>
                                `,
                                confirmButtonColor: '#0d6efd',
                                confirmButtonText: 'Saya Mengerti'
                            });
                            setTimeout(() => { $input.data('toast-active', false); }, 2000);
                        }
                    }
                }
            });

            // ===== NUMERIC ENFORCEMENT FOR PHONE NUMBERS (MAX 13 DIGITS, NUMBERS ONLY) =====
            $(document).on('keypress', '.phone-number-input', function (e) {
                // Allow control keys (backspace, delete, tab, arrows)
                if (e.which === 0 || e.which === 8) return true;
                // Only allow digits 0-9 (charCode 48 to 57)
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                    return false;
                }
            });

            $(document).on('input paste', '.phone-number-input', function () {
                let $input = $(this);
                setTimeout(function () {
                    let raw = $input.val() || '';
                    let cleaned = raw.replace(/[^0-9]/g, '').slice(0, 13);
                    if (raw !== cleaned) {
                        $input.val(cleaned);
                    }
                    updateCharCounter($input);
                }, 0);
            });

            // ===== RESET FORMS ON OPEN =====
            $('#modalTambahCustomer').on('show.bs.modal', function () {
                $('#formTambahCustomer')[0].reset();
                $('#container_tambah_alamat').empty();
                $('#container_tambah_kontak').empty();
                $('#tambahTabs button:first').tab('show');
                
                // Collapse advanced options by default
                let collapseEl = $('#tambah_opsi_lanjutan');
                if (collapseEl.hasClass('show')) {
                    collapseEl.collapse('hide');
                }
                
                // Seed 1 blank address and contact automatically as placeholders
                addAddressCard('tambah');
                addContactCard('tambah');
                updateAllCharCounters('#formTambahCustomer');

                // Reset Select2 values to defaults
                $('#tambah_jenis_customer').val('Retail').trigger('change');
                $('#tambah_currency').val('').trigger('change');
                $('#tambah_status').val('Active').trigger('change');
                $('#tambah_payment_term_id').val('').trigger('change');
                $('#tambah_sales_id').val('').trigger('change');

                enforceDisableAutocomplete();
            });

            $('#modalTambahCustomer').on('shown.bs.modal', function () {
                initCustomerSelect2($('#modalTambahCustomer'), $('#modalTambahCustomer'));
                enforceDisableAutocomplete();
            });

            // Kosongkan form Tambah saat modal ditutup (Batal/X/ESC)
            // → cegah Chrome/Edge mengambil data alamat dari DOM yang masih terbuka
            $('#modalTambahCustomer').on('hidden.bs.modal', function () {
                clearFormForAutofill('#formTambahCustomer');
            });

            $('#modalEditCustomer').on('shown.bs.modal', function () {
                initCustomerSelect2($('#modalEditCustomer'), $('#modalEditCustomer'));
                enforceDisableAutocomplete();
            });

            // Kosongkan form Edit saat modal ditutup (Batal/X/ESC)
            $('#modalEditCustomer').on('hidden.bs.modal', function () {
                clearFormForAutofill('#formEditCustomer');
            });

            // Re-initialize Select2 when collapsible advanced options are expanded (fixes width calculation)
            $('#tambah_opsi_lanjutan, #edit_opsi_lanjutan').on('shown.bs.collapse', function () {
                let modalParent = $(this).closest('.modal');
                initCustomerSelect2($(this), modalParent);
            });

            // Re-initialize Select2 when tabs switch (e.g. Alamat Pengiriman tab)
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                let modalParent = $(this).closest('.modal');
                if (modalParent.length) {
                    initCustomerSelect2(modalParent, modalParent);
                }
            });

            // ===== CLIENT-SIDE MULTI-TAB VALIDATOR =====
            function validateCustomerForm(prefix) {
                // 1. Validasi Tab Info Umum
                let namaCust = $(`#${prefix}_nama_customer`).val() || '';
                let namaPerusahaan = $(`#${prefix}_nama_perusahaan`).val() || '';
                let hp = $(`#${prefix}_hp`).val() || '';
                let telp = $(`#${prefix}_telepon`).val() || '';
                let kodeCust = $(`#${prefix}_kode_customer`).val() || '';
                let cp = $(`#${prefix}_contact_person`).val() || '';
                let jabatan = $(`#${prefix}_jabatan`).val() || '';
                let website = $(`#${prefix}_website`).val() || '';
                let npwp = $(`#${prefix}_npwp`).val() || '';
                let ket = $(`#${prefix}_keterangan`).val() || '';

                if (!namaCust.trim()) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_nama_customer`).focus();
                    Swal.fire('Perhatian!', 'Nama Customer wajib diisi!', 'warning');
                    return false;
                }
                if (namaCust.trim().length > 60) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_nama_customer`).focus();
                    Swal.fire('Perhatian!', 'Nama Customer tidak boleh melebihi 60 karakter!', 'warning');
                    return false;
                }
                if (namaPerusahaan.trim().length > 60) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_nama_perusahaan`).focus();
                    Swal.fire('Perhatian!', 'Nama Perusahaan tidak boleh melebihi 60 karakter!', 'warning');
                    return false;
                }
                if (hp && (!/^[0-9]+$/.test(hp) || hp.length > 13)) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_hp`).focus();
                    Swal.fire('Perhatian!', 'Nomor Handphone / WA harus berupa angka dan maksimal 13 karakter!', 'warning');
                    return false;
                }
                if (telp && (!/^[0-9]+$/.test(telp) || telp.length > 13)) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_telepon`).focus();
                    Swal.fire('Perhatian!', 'Nomor Telepon Kantor harus berupa angka dan maksimal 13 karakter!', 'warning');
                    return false;
                }

                // Validasi Opsi Lanjutan Info Umum
                if (kodeCust && kodeCust.trim().length > 15) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_kode_customer`).focus();
                    Swal.fire('Perhatian!', 'Kode Customer tidak boleh melebihi 15 karakter!', 'warning');
                    return false;
                }
                if (cp && cp.trim().length > 30) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_contact_person`).focus();
                    Swal.fire('Perhatian!', 'Contact Person Utama tidak boleh melebihi 30 karakter!', 'warning');
                    return false;
                }
                if (jabatan && jabatan.trim().length > 30) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_jabatan`).focus();
                    Swal.fire('Perhatian!', 'Jabatan  tidak boleh melebihi 30 karakter!', 'warning');
                    return false;
                }
                if (website && website.trim().length > 40) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_website`).focus();
                    Swal.fire('Perhatian!', 'Website tidak boleh melebihi 40 karakter!', 'warning');
                    return false;
                }
                if (npwp && npwp.trim().length > 40) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_npwp`).focus();
                    Swal.fire('Perhatian!', 'Nomor NPWP tidak boleh melebihi 40 karakter!', 'warning');
                    return false;
                }
                if (ket && ket.trim().length > 40) {
                    $(`#${prefix}Tabs #${prefix}-umum-tab`).tab('show');
                    $(`#${prefix}_opsi_lanjutan`).collapse('show');
                    $(`#${prefix}_keterangan`).focus();
                    Swal.fire('Perhatian!', 'Keterangan Tambahan tidak boleh melebihi 40 karakter!', 'warning');
                    return false;
                }

                // 2. Validasi Tab Alamat Pengiriman
                let addressCards = $(`#container_${prefix}_alamat .address-card`);
                let addressError = null;

                if (addressCards.length === 0) {
                    $(`#${prefix}Tabs #${prefix}-alamat-tab`).tab('show');
                    Swal.fire('Perhatian!', 'Alamat pengiriman wajib diisi! Silakan tambah minimal 1 alamat.', 'warning');
                    return false;
                }

                addressCards.each(function(i) {
                    let penerima = $(this).find('.addr-penerima').val() || '';
                    let telpAddr = $(this).find('.addr-telepon').val() || '';
                    let alamat = $(this).find('.addr-alamat').val() || '';
                    let kota = $(this).find('.addr-kota').val() || '';
                    let prov = $(this).find('.addr-provinsi').val() || '';
                    let pos = $(this).find('.addr-kodepos').val() || '';

                    if (!penerima.trim()) {
                        addressError = { el: $(this).find('.addr-penerima'), msg: `Nama penerima pada Alamat #${i+1} wajib diisi!` };
                        return false;
                    }
                    if (penerima.trim().length > 40) {
                        addressError = { el: $(this).find('.addr-penerima'), msg: `Nama penerima pada Alamat #${i+1} tidak boleh melebihi 40 karakter!` };
                        return false;
                    }
                    if (telpAddr && (!/^[0-9]+$/.test(telpAddr) || telpAddr.length > 13)) {
                        addressError = { el: $(this).find('.addr-telepon'), msg: `Nomor telepon penerima pada Alamat #${i+1} harus berupa angka dan maksimal 13 karakter!` };
                        return false;
                    }
                    if (!alamat.trim()) {
                        addressError = { el: $(this).find('.addr-alamat'), msg: `Alamat lengkap pada Alamat #${i+1} wajib diisi!` };
                        return false;
                    }
                    if (alamat.trim().length > 70) {
                        addressError = { el: $(this).find('.addr-alamat'), msg: `Alamat lengkap pada Alamat #${i+1} tidak boleh melebihi 70 karakter!` };
                        return false;
                    }
                    if (!kota.trim()) {
                        addressError = { el: $(this).find('.addr-kota'), msg: `Kota / Kabupaten pada Alamat #${i+1} wajib diisi!` };
                        return false;
                    }
                    if (kota.trim().length > 30) {
                        addressError = { el: $(this).find('.addr-kota'), msg: `Kota / Kabupaten pada Alamat #${i+1} tidak boleh melebihi 30 karakter!` };
                        return false;
                    }
                    if (!prov.trim()) {
                        addressError = { el: $(this).find('.addr-provinsi'), msg: `Provinsi pada Alamat #${i+1} wajib diisi!` };
                        return false;
                    }
                    if (prov.trim().length > 30) {
                        addressError = { el: $(this).find('.addr-provinsi'), msg: `Provinsi pada Alamat #${i+1} tidak boleh melebihi 30 karakter!` };
                        return false;
                    }
                    if (pos && pos.trim().length > 15) {
                        addressError = { el: $(this).find('.addr-kodepos'), msg: `Kode pos pada Alamat #${i+1} tidak boleh melebihi 15 karakter!` };
                        return false;
                    }
                });

                if (addressError) {
                    $(`#${prefix}Tabs #${prefix}-alamat-tab`).tab('show');
                    if (addressError.el && addressError.el.length) {
                        addressError.el.focus();
                    }
                    Swal.fire('Perhatian!', addressError.msg, 'warning');
                    return false;
                }

                // 3. Validasi Tab Kontak Personil
                let contactCards = $(`#container_${prefix}_kontak .contact-card`);
                let contactError = null;

                contactCards.each(function(i) {
                    let nama = $(this).find('.contact-nama').val() || '';
                    let jabatanCon = $(this).find('.contact-jabatan').val() || '';
                    let hp = $(this).find('.contact-hp').val() || '';
                    let isFilled = nama.trim() || jabatanCon.trim() || hp.trim();

                    if (isFilled) {
                        if (!nama.trim()) {
                            contactError = { el: $(this).find('.contact-nama'), msg: `Nama personil kontak #${i+1} wajib diisi!` };
                            return false;
                        }
                        if (nama.trim().length > 60) {
                            contactError = { el: $(this).find('.contact-nama'), msg: `Nama personil kontak #${i+1} tidak boleh melebihi 60 karakter!` };
                            return false;
                        }
                        if (jabatanCon && jabatanCon.trim().length > 30) {
                            contactError = { el: $(this).find('.contact-jabatan'), msg: `Jabatan kontak #${i+1} tidak boleh melebihi 30 karakter!` };
                            return false;
                        }
                        if (!hp.trim()) {
                            contactError = { el: $(this).find('.contact-hp'), msg: `Nomor HP personil kontak #${i+1} wajib diisi!` };
                            return false;
                        }
                        if (!/^[0-9]+$/.test(hp) || hp.length > 13) {
                            contactError = { el: $(this).find('.contact-hp'), msg: `Nomor HP kontak #${i+1} harus berupa angka dan maksimal 13 karakter!` };
                            return false;
                        }
                    }
                });

                if (contactError) {
                    $(`#${prefix}Tabs #${prefix}-kontak-tab`).tab('show');
                    contactError.el.focus();
                    Swal.fire('Perhatian!', contactError.msg, 'warning');
                    return false;
                }

                return true;
            }

            /**
             * Mengosongkan semua field dalam form sebelum modal di-hide.
             * Tujuan: mencegah browser (Chrome/Edge) mendeteksi data alamat
             * dan memunculkan prompt "Save address?" atau "Save info?".
             */
            function clearFormForAutofill(formSelector) {
                let $form = $(formSelector);
                // Reset semua input teks, number, textarea
                $form.find('input[type="text"], input[type="number"], input[type="email"], textarea').val('');
                // Reset checkbox
                $form.find('input[type="checkbox"]').prop('checked', false);
                // Reset hidden inputs kecuali CSRF & method
                $form.find('input[type="hidden"]').not('[name="_token"], [name="_method"]').val('');
                // Hapus dynamic rows (alamat & kontak) supaya tidak ada nilai tersisa
                $form.find('#container_tambah_alamat, #container_edit_alamat').empty();
                $form.find('#container_tambah_kontak, #container_edit_kontak').empty();
                // Reset select2 (destroy tidak perlu, cukup val kosong)
                $form.find('select').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).val(null).trigger('change');
                    } else {
                        $(this).val('');
                    }
                });
            }

            // ===== SUBMIT CREATION =====
            $('#formTambahCustomer').on('submit', function (e) {
                e.preventDefault();

                if (!validateCustomerForm('tambah')) {
                    return false;
                }

                let btnSubmit = $(this).find('button[type="submit"]');
                let originalBtnHtml = btnSubmit.html();
                btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                let formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('customer.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        // Kosongkan form sebelum hide → cegah Chrome "Save address?" prompt
                        clearFormForAutofill('#formTambahCustomer');
                        $('#modalTambahCustomer').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            table.ajax.reload(null, false);
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        } else if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                    },
                    complete: function() {
                        btnSubmit.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            });

            // ===== SHOW DETAILS MODAL =====
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                $.get('/customer/' + id + '/edit', function (res) {
                    let c = res.customer;
                    
                    $('#detail_nama_customer').text(c.nama_customer);
                    $('#detail_nama_perusahaan').text(c.nama_perusahaan ? c.nama_perusahaan : '— Tanpa Nama Perusahaan');
                    $('#detail_kode_customer').text(c.kode_customer);
                    
                    let activeHtml = c.status === 'Active' 
                        ? '<span class="badge-status-pill badge-status-active"><i class="bi bi-check-circle-fill"></i> Aktif</span>'
                        : '<span class="badge-status-pill badge-status-inactive"><i class="bi bi-x-circle-fill"></i> Nonaktif</span>';
                    $('#detail_status_badge').html(activeHtml);
                    
                    $('#detail_jenis_customer').text(c.jenis_customer);
                    $('#detail_currency').text(c.currency ? c.currency : '-');
                    $('#detail_credit_limit').text(c.credit_limit ? formatRupiah(c.credit_limit) : 'Rp 0');
                    $('#detail_payment_term').text(c.payment_term ? c.payment_term.nama_term : 'COD (0 Hari)');
                    
                    $('#detail_contact_person').text(c.contact_person ? c.contact_person : '-');
                    $('#detail_jabatan').text(c.jabatan ? c.jabatan : '-');
                    $('#detail_sales').text(c.sales ? c.sales.nama_lengkap : '-');
                    $('#detail_pkp').text(c.pkp ? 'Wajib Pajak (PKP)' : 'Bukan PKP');
                    
                    $('#detail_telepon').text(c.telepon ? c.telepon : '-');
                    $('#detail_hp').text(c.hp ? c.hp : '-');
                    $('#detail_email').text(c.email ? c.email : '-');
                    $('#detail_npwp').text(c.npwp ? c.npwp : '-');
                    $('#detail_website').text(c.website ? c.website : '-');
                    $('#detail_keterangan').text(c.keterangan ? c.keterangan : '-');

                    // Addresses rendering
                    let addrHtml = '';
                    if (c.addresses && c.addresses.length > 0) {
                        c.addresses.forEach(a => {
                            let badgeDefault = a.is_default ? `<span class="badge-type-pill badge-type-distributor" style="font-size:0.62rem;">UTAMA</span>` : '';
                            addrHtml += `
                                <div class="rounded-3 p-3 mb-2" style="background-color: var(--cine-card-bg); border: 1px solid var(--cine-border); position:relative;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge-type-pill badge-type-agen text-uppercase" style="font-size:0.65rem;">${a.jenis_alamat}</span>
                                        ${badgeDefault}
                                    </div>
                                    <div class="fw-bold mb-1" style="font-size:0.88rem; color: var(--cine-text-main);">Penerima: ${escapeHtml(a.nama_penerima)} ${a.telepon ? '('+escapeHtml(a.telepon)+')' : ''}</div>
                                    <div class="small mb-1" style="color: var(--cine-text-muted);">${escapeHtml(a.alamat)}</div>
                                    <div class="small font-monospace fw-semibold" style="font-size:0.78rem; color: var(--cine-text-sub);">${escapeHtml(a.kota)}, ${escapeHtml(a.provinsi)} ${a.kode_pos ? '- '+escapeHtml(a.kode_pos) : ''} (${escapeHtml(a.negara)})</div>
                                </div>
                            `;
                        });
                    } else {
                        addrHtml = '<div class="text-center text-muted p-4 small">Belum ada alamat pengiriman terdaftar.</div>';
                    }
                    $('#detail_alamat_list').html(addrHtml);

                    // Contacts rendering
                    let contactsHtml = '';
                    if (c.contacts && c.contacts.length > 0) {
                        c.contacts.forEach(con => {
                            contactsHtml += `
                                <tr style="border-bottom: 1px solid var(--cine-border);">
                                    <td class="fw-bold py-2 px-3" style="color: var(--cine-text-main);">${escapeHtml(con.nama)}</td>
                                    <td class="py-2" style="color: var(--cine-text-sub);">${escapeHtml(con.jabatan ?? '-')}</td>
                                    <td class="font-monospace py-2" style="color: var(--cine-text-main);">${escapeHtml(con.hp)}</td>
                                    <td class="py-2" style="color: var(--cine-text-muted);">${escapeHtml(con.email ?? '-')}</td>
                                </tr>
                            `;
                        });
                    } else {
                        contactsHtml = '<tr><td colspan="4" class="text-center text-muted py-3 small">Tidak ada personil kontak terdaftar.</td></tr>';
                    }
                    $('#detail_kontak_table_body').html(contactsHtml);

                    $('#detailTabs button:first').tab('show');
                    $('#modalDetailCustomer').modal('show');
                });
            });

            // ===== TRIGGER EDIT MODAL =====
            $(document).on('click', '.btn-edit', function () {
                let id = $(this).data('id');
                $.get('/customer/' + id + '/edit', function (res) {
                    let c = res.customer;
                    
                    $('#edit_id').val(c.id);
                    $('#edit_kode_customer').val(c.kode_customer);
                    $('#edit_nama_customer').val(c.nama_customer);
                    $('#edit_nama_perusahaan').val(c.nama_perusahaan);
                    $('#edit_jenis_customer').val(c.jenis_customer).trigger('change');
                    $('#edit_contact_person').val(c.contact_person);
                    $('#edit_jabatan').val(c.jabatan);
                    $('#edit_telepon').val(c.telepon);
                    $('#edit_hp').val(c.hp);
                    $('#edit_email').val(c.email);
                    $('#edit_website').val(c.website);
                    $('#edit_currency').val(c.currency ? c.currency : '').trigger('change');
                    $('#edit_npwp').val(c.npwp);
                    $('#edit_pkp').prop('checked', c.pkp == 1);
                    $('#edit_status').val(c.status).trigger('change');
                    $('#edit_payment_term_id').val(c.payment_term_id ? c.payment_term_id : '').trigger('change');
                    $('#edit_credit_limit').val(c.credit_limit ? parseFloat(c.credit_limit) : '');
                    $('#edit_sales_id').val(c.sales_id ? c.sales_id : '').trigger('change');
                    $('#edit_keterangan').val(c.keterangan);

                    // Collapse advanced options by default in Edit
                    let collapseEl = $('#edit_opsi_lanjutan');
                    if (collapseEl.hasClass('show')) {
                        collapseEl.collapse('hide');
                    }

                    // Populate Addresses
                    $('#container_edit_alamat').empty();
                    if (c.addresses && c.addresses.length > 0) {
                        c.addresses.forEach(addr => addAddressCard('edit', addr));
                    } else {
                        addAddressCard('edit');
                    }

                    // Populate Contacts
                    $('#container_edit_kontak').empty();
                    if (c.contacts && c.contacts.length > 0) {
                        c.contacts.forEach(contact => addContactCard('edit', contact));
                    } else {
                        addContactCard('edit');
                    }

                    updateAllCharCounters('#formEditCustomer');
                    $('#editTabs button:first').tab('show');
                    $('#modalEditCustomer').modal('show');
                    enforceDisableAutocomplete();
                });
            });

            // ===== SUBMIT UPDATE =====
            $('#formEditCustomer').on('submit', function (e) {
                e.preventDefault();

                if (!validateCustomerForm('edit')) {
                    return false;
                }

                let id = $('#edit_id').val();
                let btnSubmit = $(this).find('button[type="submit"]');
                let originalBtnHtml = btnSubmit.html();
                btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                let formData = $(this).serialize();

                $.ajax({
                    url: "/customer/" + id + "/update",
                    type: "POST",
                    data: formData,
                    success: function (res) {
                        // Kosongkan form sebelum hide → cegah Chrome "Save address?" prompt
                        clearFormForAutofill('#formEditCustomer');
                        $('#modalEditCustomer').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            table.ajax.reload(null, false);
                        });
                    },
                    error: function (err) {
                        let errorMsg = 'Terjadi kesalahan saat memperbarui data.';
                        if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                            errorMsg = '<b>Data tidak valid:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        } else if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                    },
                    complete: function() {
                        btnSubmit.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            });

            // ===== TRIGGER DELETION =====
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Menghapus customer " + name + " juga akan menonaktifkan alamat dan kontak terkait!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/customer/" + id + "/delete",
                            type: "DELETE",
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data customer.';
                                if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg = err.responseJSON.message;
                                }
                                Swal.fire({ title: 'Gagal!', text: errorMsg, icon: 'error' });
                            }
                        });
                    }
                });
            });

            // ===== IMPORT EXCEL HANDLER =====
            $('#formImportCustomer').on('submit', function (e) {
                e.preventDefault();

                let fileInput = $('#import_customer_file')[0];
                if (!fileInput.files.length) {
                    Swal.fire('Perhatian!', 'Pilih file Excel terlebih dahulu!', 'warning');
                    return;
                }

                let formData = new FormData(this);
                let btnImport = $('#btnImportCustomer');
                let originalHtml = btnImport.html();

                btnImport.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor...');
                $('#import_customer_progress').removeClass('d-none');
                $('#import_customer_errors').addClass('d-none');
                $('#import_customer_error_list').empty();

                $.ajax({
                    url: "{{ route('customer.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('#modalImportCustomer').modal('hide');

                        let isPartial = res.inserted > 0 && res.skipped > 0;
                        let isFullSuccess = res.inserted > 0 && res.skipped === 0;
                        let icon = isFullSuccess ? 'success' : (res.inserted > 0 ? 'warning' : 'error');
                        let title = res.inserted > 0 ? 'Import Selesai!' : 'Import Gagal!';

                        let html = `<div class="mb-2"><b>${res.inserted}</b> customer berhasil diimport.</div>`;
                        if (res.skipped > 0) {
                            html += `<div class="text-warning mb-2"><b>${res.skipped}</b> baris dilewati (duplikat atau data tidak valid).</div>`;
                        }

                        // Tampilkan rincian baris error langsung di modal SweetAlert
                        if (res.errors && res.errors.length > 0) {
                            html += `
                                <div class="text-start mt-3 p-3 rounded" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); max-height: 220px; overflow-y: auto;">
                                    <div class="fw-bold text-danger mb-2 small d-flex align-items-center gap-1">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Rincian Kesalahan (${res.errors.length} baris):
                                    </div>
                                    <ul class="mb-0 ps-3 small" style="line-height: 1.6; color: inherit;">`;
                            res.errors.forEach(msg => {
                                html += `<li class="mb-1">${escapeHtml(msg)}</li>`;
                            });
                            html += `</ul></div>`;
                        }

                        Swal.fire({
                            title: title,
                            html: html,
                            icon: icon,
                            width: (res.errors && res.errors.length > 0) ? '580px' : '420px',
                            confirmButtonText: 'Tutup'
                        }).then(() => {
                            if (res.inserted > 0) {
                                table.ajax.reload(null, false);
                            }
                        });
                    },
                    error: function (err) {
                        let msg = 'Terjadi kesalahan saat import.';
                        if (err.responseJSON && err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        Swal.fire({ title: 'Gagal!', text: msg, icon: 'error' });
                    },
                    complete: function () {
                        btnImport.prop('disabled', false).html(originalHtml);
                        $('#import_customer_progress').addClass('d-none');
                    }
                });
            });

            // Reset form import saat modal ditutup
            $('#modalImportCustomer').on('hidden.bs.modal', function () {
                $('#formImportCustomer')[0].reset();
                $('#import_customer_errors').addClass('d-none');
                $('#import_customer_error_list').empty();
                $('#import_customer_progress').addClass('d-none');
            });

            // ===== HELPER: DOWNLOAD FILE WITH SWAL ALERT =====
            /**
             * Fetch file dari URL, trigger browser download, lalu tampilkan SweetAlert.
             * @param {string} url        - URL endpoint download
             * @param {string} filename   - Nama file yang akan disimpan
             * @param {jQuery} $btn       - jQuery element tombol (untuk loading state)
             * @param {string} label      - Label untuk pesan sukses (misal: "Export" / "Template")
             */
            function downloadFileWithAlert(url, filename, $btn, label) {
                let originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> ' + label + '...'
                );

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                    }
                })
                .then(function (response) {
                    let contentType = response.headers.get('Content-Type') || '';
                    // Cek apakah respons adalah file (bukan JSON error)
                    if (!response.ok) {
                        // Baca sebagai text/json untuk ambil pesan error
                        return response.text().then(function (text) {
                            let errMsg = 'Server mengembalikan error ' + response.status + '.';
                            try {
                                let json = JSON.parse(text);
                                if (json.message) errMsg = json.message;
                            } catch (e) { /* bukan JSON */ }
                            throw new Error(errMsg);
                        });
                    }
                    if (contentType.includes('application/json')) {
                        // Server kembalikan JSON padahal harusnya file → error
                        return response.json().then(function (json) {
                            throw new Error(json.message || 'Gagal mengunduh file.');
                        });
                    }
                    // Sukses → kembalikan sebagai blob
                    return response.blob();
                })
                .then(function (blob) {
                    // Trigger download via temporary anchor
                    let blobUrl = URL.createObjectURL(blob);
                    let a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    setTimeout(function () { URL.revokeObjectURL(blobUrl); }, 1000);

                    // Alert sukses (toast style)
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diunduh!',
                        html: 'File <b>' + escapeHtml(filename) + '</b> berhasil diunduh.',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });
                })
                .catch(function (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengunduh!',
                        text: err.message || 'Terjadi kesalahan saat mengunduh file.',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(function () {
                    $btn.prop('disabled', false).html(originalHtml);
                });
            }

            // Bind Export Button
            $('#btnExportCustomer').on('click', function () {
                let $btn     = $(this);
                let url      = $btn.data('url');
                let filename = $btn.data('filename');
                downloadFileWithAlert(url, filename, $btn, 'Mengekspor');
            });

            // Bind Download Template Button
            $('#btnDownloadTemplateCustomer').on('click', function (e) {
                e.preventDefault();
                let $btn     = $(this);
                let url      = $btn.data('url');
                let filename = $btn.data('filename');
                downloadFileWithAlert(url, filename, $btn, 'Mengunduh');
            });
        });
    </script>
    @endpush

</x-layouts>
