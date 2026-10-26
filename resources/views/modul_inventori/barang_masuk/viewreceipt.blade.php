<x-layouts title="Barang Masuk (Goods Receipt)">
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        :root,
        [data-bs-theme="light"] {
            --gr-bg-body: #f8fafc;
            --gr-bg-card: #ffffff;
            --gr-bg-card-header: #ffffff;
            --gr-bg-card-footer: #f8fafc;
            --gr-bg-table-th: #f1f5f9;
            --gr-bg-table-row: #ffffff;
            --gr-bg-table-row-alt: #f8fafc;
            --gr-bg-input: #ffffff;
            --gr-bg-img-wrapper: #f1f5f9;
            --gr-bg-progress: #e2e8f0;
            --gr-bg-summary: #f8fafc;
            --gr-bg-totals: #f1f5f9;
            --gr-color-text: #0f172a;
            --gr-color-muted: #64748b;
            --gr-border: #e2e8f0;
            --gr-border-card: #cbd5e1;
            --gr-border-table-th: #cbd5e1;
            --gr-border-table-td: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --gr-bg-body: #0f172a;
            --gr-bg-card: #1e293b;
            --gr-bg-card-header: #1e293b;
            --gr-bg-card-footer: #0f172a;
            --gr-bg-table-th: #0f172a;
            --gr-bg-table-row: #1e293b;
            --gr-bg-table-row-alt: #162032;
            --gr-bg-input: #0f172a;
            --gr-bg-img-wrapper: #0f172a;
            --gr-bg-progress: #1e293b;
            --gr-bg-summary: #0f172a;
            --gr-bg-totals: #0f172a;
            --gr-color-text: #f1f5f9;
            --gr-color-muted: #94a3b8;
            --gr-border: #334155;
            --gr-border-card: #334155;
            --gr-border-table-th: #334155;
            --gr-border-table-td: #334155;
        }

        /* =============================================
           SELECT2
           ============================================= */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 50px;
            padding: 0.375rem 0.75rem;
            font-size: 0.9rem;
            border: 1px solid var(--gr-border-card);
            height: calc(2.25rem + 2px);
            background-color: var(--gr-bg-input) !important;
            color: var(--gr-color-text) !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            color: var(--gr-color-text) !important;
            font-weight: 600;
            padding-left: 0;
        }
        .select2-container--bootstrap-5 .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
        }

        /* =============================================
           MODAL
           ============================================= */
        #modalTambahGR .modal-content,
        #modalEditGR .modal-content,
        #modalDetailGR .modal-content,
        #modalSerialAsset .modal-content {
            background-color: var(--gr-bg-card) !important;
            color: var(--gr-color-text) !important;
        }
        #modalTambahGR .modal-body,
        #modalEditGR .modal-body,
        #modalDetailGR .modal-body,
        #modalSerialAsset .modal-body {
            background-color: var(--gr-bg-body) !important;
            max-height: 80vh;
            overflow-y: auto;
        }
        /* Modal Header stays dark always for GR modals */
        #modalTambahGR .modal-header,
        #modalEditGR .modal-header,
        #modalDetailGR .modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
        }
        .modal-header .modal-title { color: #ffffff !important; }
        .modal-header small { color: rgba(255,255,255,0.85) !important; font-weight: 500; }
        #modalSerialAsset .modal-header .modal-title { color: var(--gr-color-text) !important; }

        /* Modal Footers */
        #modalTambahGR .modal-footer,
        #modalEditGR .modal-footer,
        #modalDetailGR .modal-footer {
            background-color: var(--gr-bg-card-header) !important;
            border-top: 1px solid var(--gr-border-card) !important;
        }

        /* =============================================
           FORM FIELDS
           ============================================= */
        .form-label { font-weight: 700 !important; }
        .form-control, .form-select, .form-control-custom {
            font-weight: 600 !important;
            background-color: var(--gr-bg-input) !important;
            color: var(--gr-color-text) !important;
            border-color: var(--gr-border-card) !important;
        }
        .form-control::placeholder { color: var(--gr-color-muted) !important; }

        /* =============================================
           CARDS INSIDE MODAL
           ============================================= */
        #modalTambahGR .card,
        #modalEditGR .card,
        #modalDetailGR .card,
        #modalSerialAsset .card {
            background-color: var(--gr-bg-card) !important;
            border-color: var(--gr-border-card) !important;
            color: var(--gr-color-text) !important;
        }
        #modalTambahGR .card-header,
        #modalEditGR .card-header,
        #modalDetailGR .card-header {
            background-color: var(--gr-bg-card-header) !important;
            border-color: var(--gr-border) !important;
            color: var(--gr-color-text) !important;
        }
        #modalTambahGR .card-footer,
        #modalEditGR .card-footer,
        #modalDetailGR .card-footer,
        #modalSerialAsset .modal-footer,
        #gr_footer_summary,
        #edit_gr_footer_summary {
            background-color: var(--gr-bg-card-footer) !important;
            border-color: var(--gr-border) !important;
            color: var(--gr-color-text) !important;
        }
        #gr_footer_summary .text-dark,
        #edit_gr_footer_summary .text-dark { color: var(--gr-color-text) !important; }

        /* PO Info Card specifics */
        #po_info_card_wrapper .card,
        #edit_po_info_card_wrapper .card {
            background-color: var(--gr-bg-card) !important;
            border-color: var(--gr-border-card) !important;
        }
        #po_info_card_wrapper .border-end,
        #edit_po_info_card_wrapper .border-end {
            border-color: var(--gr-border-card) !important;
        }
        #po_info_card_wrapper .row.bg-light,
        #po_info_card_wrapper .bg-light,
        #edit_po_info_card_wrapper .row.bg-light,
        #edit_po_info_card_wrapper .bg-light {
            background-color: var(--gr-bg-totals) !important;
        }

        /* Top Transaction Header Info Card */
        #modalTambahGR .card.bg-light,
        #modalEditGR .card.bg-light,
        #modalTambahGR .bg-light.bg-opacity-50,
        #modalEditGR .bg-light.bg-opacity-50 {
            background-color: var(--gr-bg-card-header) !important;
            border: 1px solid var(--gr-border-card) !important;
        }

        /* Progress bar track */
        .progress { background-color: var(--gr-bg-progress) !important; }

        /* =============================================
           DETAIL MODAL - Colored Headers & Field Boxes
           ============================================= */
        .detail-card-blue-header {
            background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        }
        .detail-card-green-header {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
        }
        .detail-card-yellow-header {
            background: linear-gradient(135deg, #fffbeb, #fef3c7) !important;
        }

        [data-bs-theme="dark"] .detail-card-blue-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        }
        [data-bs-theme="dark"] .detail-card-green-header {
            background: linear-gradient(135deg, #14532d 0%, #166534 100%) !important;
        }
        [data-bs-theme="dark"] .detail-card-yellow-header {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%) !important;
        }
        [data-bs-theme="dark"] .detail-card-blue-header h6,
        [data-bs-theme="dark"] .detail-card-green-header h6,
        [data-bs-theme="dark"] .detail-card-yellow-header h6 {
            color: #ffffff !important;
        }

        .detail-field-box {
            background-color: var(--gr-bg-body) !important;
            border: 1px solid var(--gr-border) !important;
            border-radius: 10px;
            padding: 8px 12px;
            transition: background-color 0.2s ease;
        }
        .detail-field-box .text-dark {
            color: var(--gr-color-text) !important;
        }
        .detail-field-box .text-muted {
            color: var(--gr-color-muted) !important;
        }

        /* =============================================
           TABLE
           ============================================= */
        .table-modern th {
            background-color: var(--gr-bg-table-th) !important;
            color: var(--gr-color-text) !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            padding: 12px 16px !important;
            border-bottom: 2px solid var(--gr-border-table-th) !important;
        }
        .table-modern td {
            padding: 12px 16px !important;
            border-bottom: 1px solid var(--gr-border-table-td) !important;
            color: var(--gr-color-text) !important;
            font-weight: 500;
            background-color: var(--gr-bg-table-row) !important;
        }
        .table-modern tbody tr:nth-child(even) td {
            background-color: var(--gr-bg-table-row-alt) !important;
        }
        .table-modern tfoot td {
            background-color: var(--gr-bg-card-footer) !important;
            color: var(--gr-color-text) !important;
            border-color: var(--gr-border) !important;
        }

        /* =============================================
           PRODUCT IMAGE WRAPPER
           ============================================= */
        .product-img-wrapper {
            width: 48px; height: 48px; border-radius: 8px; overflow: hidden;
            background-color: var(--gr-bg-img-wrapper);
            border: 1px solid var(--gr-border-card);
            display: flex; align-items: center; justify-content: center;
        }
        .product-img { width: 100%; height: 100%; object-fit: cover; }

        /* =============================================
           SUMMARY / PLACEHOLDER
           ============================================= */
        .summary-card {
            background: var(--gr-bg-summary);
            border-radius: 12px; padding: 1.25rem;
            border: 1px dashed var(--gr-border-card);
            color: var(--gr-color-text);
        }
        #items_placeholder {
            background-color: var(--gr-bg-card) !important;
            color: var(--gr-color-muted) !important;
            border-color: var(--gr-border) !important;
        }

        /* =============================================
           STATUS BADGES
           ============================================= */
        .badge-status { font-size: 0.8rem; padding: 0.35em 0.8em; border-radius: 50px; font-weight: 700; }
        .bg-warning-soft { background-color: rgba(245,158,11,0.15); color: #d97706; font-weight: 700; }
        .bg-success-soft { background-color: rgba(16,185,129,0.15); color: #059669; font-weight: 700; }
        .bg-danger-soft  { background-color: rgba(239,68,68,0.15);  color: #dc2626; font-weight: 700; }
        .bg-primary-soft { background-color: rgba(14,165,233,0.15); color: #0284c7; font-weight: 700; }

        /* text-dark responsive */
        .text-dark { color: var(--gr-color-text) !important; }
        .text-muted, .text-secondary { color: var(--gr-color-muted) !important; }

        /* =============================================
           PICKER MODALS (LOOKUP PO & GUDANG)
           ============================================= */
        .picker-modal {
            z-index: 999999 !important;
            background: rgba(15,23,42,0.75) !important;
        }
        .picker-modal.show {
            opacity: 1 !important;
            display: block !important;
        }
        .picker-modal .modal-dialog { z-index: 1000000 !important; }
        .picker-modal .modal-content {
            background-color: var(--gr-bg-card) !important;
            color: var(--gr-color-text) !important;
            border-color: var(--gr-border-card) !important;
        }
        .picker-modal .modal-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important; }
        .picker-search-wrap { position: relative; }
        .picker-search-wrap i {
            position: absolute; top: 50%; left: 14px;
            transform: translateY(-50%); color: var(--gr-color-muted);
        }
        .picker-search-input {
            padding-left: 40px !important; border-radius: 50px !important;
            border: 1px solid var(--gr-border-card) !important;
            background-color: var(--gr-bg-input) !important;
            color: var(--gr-color-text) !important;
            font-size: 0.9rem !important; height: 42px !important;
        }
        .picker-results-list {
            max-height: 350px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 8px;
        }
        .picker-item {
            padding: 12px 16px; border-radius: 12px;
            border: 1px solid var(--gr-border);
            background: var(--gr-bg-card);
            color: var(--gr-color-text);
            cursor: pointer; transition: all 0.2s ease;
        }
        .picker-item:hover {
            border-color: #0284c7;
            background: var(--gr-bg-body);
        }
        .picker-item.active { border-color: #0284c7; }
        .picker-loading, .picker-empty { text-align: center; padding: 30px; color: var(--gr-color-muted); }
        .picker-clear-btn { background: none; border: none; color: #ef4444; font-size: 0.85rem; font-weight: 600; cursor: pointer; }
        .picker-name, .picker-meta { color: var(--gr-color-text) !important; }
        .picker-sub { color: var(--gr-color-muted) !important; }

        /* =============================================
           CINEMATIC MOBILE STYLES FOR GR INPUT TABLE
           ============================================= */
        .gr-table-scroll-wrapper {
            position: relative;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            cursor: grab;
        }
        .gr-table-scroll-wrapper.grabbing {
            cursor: grabbing !important;
            user-select: none !important;
        }
        .gr-table-scroll-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .gr-table-scroll-wrapper::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        .gr-table-scroll-wrapper::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.35);
            border-radius: 10px;
        }
        .gr-table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.65);
        }

        .gr-scroll-table {
            min-width: 1120px !important;
            border-collapse: separate !important;
            border-spacing: 0;
        }

        /* Sticky first column (Nama Barang) on horizontal scroll */
        .gr-scroll-table th.sticky-col-header,
        .gr-scroll-table td.sticky-col-body {
            position: sticky !important;
            left: 0 !important;
            z-index: 2 !important;
            background-color: var(--gr-bg-card, #ffffff) !important;
            box-shadow: 3px 0 8px rgba(0, 0, 0, 0.06);
        }
        .gr-scroll-table th.sticky-col-header {
            z-index: 3 !important;
            background-color: var(--gr-bg-header, #f8fafc) !important;
        }

        /* Scroll hint banner */
        .gr-scroll-hint-bar {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.12) 0%, rgba(59, 130, 246, 0.08) 100%);
            border-bottom: 1px solid rgba(14, 165, 233, 0.2);
            color: #0284c7;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Toolbar scrollable chips on mobile */
        .gr-toolbar-scroll {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .gr-toolbar-scroll::-webkit-scrollbar {
            display: none;
        }

        /* Cinematic Mobile Summary Cards */
        .gr-summary-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3), 0 8px 10px -6px rgba(15, 23, 42, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        .gr-summary-hero::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .gr-metric-tile {
            background: var(--gr-bg-card, #ffffff);
            border: 1px solid var(--gr-border, #e2e8f0);
            border-radius: 10px;
            padding: 8px 12px;
            transition: all 0.2s ease;
        }
        .gr-metric-tile:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        /* =============================================
           STABLE NATIVE MODAL SCROLLING (MENTOK ATAS & BAWAH)
           ============================================= */
        #modalTambahGR,
        #modalEditGR {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-y: contain !important;
            scroll-behavior: smooth !important;
        }

        @media (min-width: 1200px) {
            .gr-modal-dialog {
                max-width: 96vw !important;
                width: 96vw !important;
                margin: 1.5rem auto 5rem auto !important; /* Ruang lega 5rem di bawah agar bisa scroll mentok */
            }
            .gr-modal-dialog .gr-modal-content {
                border-radius: 20px !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
            }
        }

        @media (max-width: 1199.98px) {
            .gr-modal-dialog {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 0 5rem 0 !important; /* Ruang lega 5rem di bawah di laptop & mobile */
            }
            .gr-modal-dialog .gr-modal-content {
                border-radius: 0 !important;
                border: none !important;
                min-height: auto !important;
            }
        }

        /* Biarkan body modal mengalir bebas tanpa nesting scrollbar */
        #modalTambahGR .modal-body,
        #modalEditGR .modal-body {
            overflow-x: hidden !important;
            overflow-y: visible !important;
            height: auto !important;
            min-height: auto !important;
            flex: none !important;
            box-sizing: border-box !important;
            padding-bottom: 24px !important;
        }

        /* =============================================
           MODAL ITEMS CARD — STRICTLY INSIDE MODAL
           ============================================= */
        #items_card,
        #edit_items_card {
            max-width: 100% !important;
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-bottom: 1.5rem !important;
            box-sizing: border-box !important;
            border-radius: 12px !important;
            overflow: hidden !important;
        }

        .gr-table-scroll-wrapper {
            position: relative;
            max-width: 100% !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            box-sizing: border-box !important;
        }

        /* =============================================
           MODAL ACTION FOOTER (INSIDE MODAL CONTENT)
           ============================================= */
        #modalTambahGR .modal-footer,
        #modalEditGR .modal-footer {
            background: var(--gr-bg-card, #ffffff) !important;
            border-top: 1px solid var(--gr-border-card, rgba(0, 0, 0, 0.08)) !important;
            padding: 18px 24px 28px 24px !important;
            border-bottom-left-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
        }

        [data-bs-theme="dark"] #modalTambahGR .modal-footer,
        [data-bs-theme="dark"] #modalEditGR .modal-footer {
            background: #1e293b !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        @media (max-width: 1199.98px) {
            #modalTambahGR .modal-footer,
            #modalEditGR .modal-footer {
                border-radius: 0 !important;
                padding: 14px 16px 24px 16px !important;
            }
        }

        /* Modern Bento tiles with dark glass aesthetic */
        [data-bs-theme="dark"] .gr-metric-tile {
            background: rgba(30, 41, 59, 0.65) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 12px !important;
        }

        /* Modern Finger-friendly Header Close Button */
        .gr-modal-header-close {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .gr-modal-header-close:hover,
        .gr-modal-header-close:active {
            background: rgba(239, 68, 68, 0.25);
            border-color: rgba(239, 68, 68, 0.5);
            color: #ef4444;
            transform: scale(1.06);
        }

        /* =============================================
           AESTHETIC SATUAN BADGE & SELECT
           ============================================= */
        .gr-satuan-badge {
            background: rgba(14, 165, 233, 0.12) !important;
            border: 1px solid rgba(14, 165, 233, 0.3) !important;
            color: #38bdf8 !important;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.15) !important;
            font-family: 'JetBrains Mono', 'Fira Code', monospace, sans-serif !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
            cursor: default !important;
        }
        .gr-satuan-badge:hover {
            background: rgba(14, 165, 233, 0.22) !important;
            border-color: rgba(14, 165, 233, 0.55) !important;
            transform: scale(1.04);
        }
        [data-bs-theme="light"] .gr-satuan-badge {
            background: #e0f2fe !important;
            border: 1px solid #bae6fd !important;
            color: #0284c7 !important;
        }

        .gr-satuan-select {
            min-width: 105px !important;
            border-radius: 50px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            padding: 5px 24px 5px 12px !important;
            background-color: rgba(30, 41, 59, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            color: #f8fafc !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
        }
        .gr-satuan-select:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
            background-color: rgba(30, 41, 59, 1) !important;
            color: #ffffff !important;
        }
        [data-bs-theme="light"] .gr-satuan-select {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
        }

        /* Seamless Dark Mode Table Headers */
        [data-bs-theme="dark"] #modalTambahGR .table-light,
        [data-bs-theme="dark"] #modalTambahGR .table-light th,
        [data-bs-theme="dark"] #modalEditGR .table-light,
        [data-bs-theme="dark"] #modalEditGR .table-light th {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Mobile specific adjustments */
        @media (max-width: 768px) {
            #modalTambahGR .modal-body,
            #modalEditGR .modal-body {
                padding: 12px 10px 24px 10px !important;
            }

            /* Di layar HP, lepas sticky nama barang agar tabel leluasa bisa digeser ke samping */
            .gr-scroll-table th.sticky-col-header,
            .gr-scroll-table td.sticky-col-body {
                position: static !important;
                box-shadow: none !important;
            }

            /* Tabel sentuh horizontal lancar tanpa geser modal */
            .gr-table-scroll-wrapper {
                touch-action: pan-x !important;
            }
        }

        /* =============================================
           DATATABLE CONTROLS — Search, Length, Pagination
           ============================================= */

        /* Search input */
        div.dataTables_filter input {
            border: 1.5px solid var(--gr-border-card) !important;
            border-radius: 50px !important;
            padding: 6px 16px 6px 38px !important;
            font-size: 0.875rem !important;
            background-color: var(--gr-bg-input) !important;
            color: var(--gr-color-text) !important;
            min-width: 220px;
            width: 100% !important;
            max-width: 320px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: 12px center !important;
        }
        div.dataTables_filter input:focus {
            outline: none !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12) !important;
        }
        div.dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 0 !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            color: var(--gr-color-muted) !important;
            width: 100% !important;
            justify-content: flex-end !important;
        }
        div.dataTables_filter { text-align: right !important; }

        /* Length select */
        div.dataTables_length select {
            border: 1.5px solid var(--gr-border-card) !important;
            border-radius: 50px !important;
            padding: 5px 28px 5px 12px !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            background-color: var(--gr-bg-input) !important;
            color: var(--gr-color-text) !important;
            min-width: 70px;
        }
        div.dataTables_length label {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin: 0 !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            color: var(--gr-color-muted) !important;
            white-space: nowrap !important;
        }

        /* Pagination */
        div.dataTables_paginate {
            text-align: right !important;
        }
        div.dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid var(--gr-border) !important;
            padding: 4px 12px !important;
            margin: 0 2px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: var(--gr-color-muted) !important;
            background: var(--gr-bg-card) !important;
            transition: all 0.18s ease !important;
        }
        div.dataTables_paginate .paginate_button:hover {
            border-color: #0284c7 !important;
            color: #0284c7 !important;
            background: rgba(2,132,199,0.06) !important;
        }
        div.dataTables_paginate .paginate_button.current,
        div.dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #0284c7, #0ea5e9) !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(2,132,199,0.25) !important;
        }
        div.dataTables_paginate .paginate_button.disabled,
        div.dataTables_paginate .paginate_button.disabled:hover {
            opacity: 0.38 !important;
            cursor: not-allowed !important;
        }

        /* Info text */
        div.dataTables_info {
            font-size: 0.82rem !important;
            color: var(--gr-color-muted) !important;
            font-weight: 600 !important;
            padding-top: 6px !important;
        }

        /* Mobile DataTable controls */
        @media (max-width: 575.98px) {
            div.dataTables_filter label,
            div.dataTables_filter { justify-content: flex-start !important; text-align: left !important; }
            div.dataTables_filter input { max-width: 100% !important; }
            div.dataTables_paginate { text-align: left !important; }
            div.dataTables_info { padding-bottom: 4px !important; }
        }
    </style>

    {{-- ============================================================
         PARTIAL: Tabel list penerimaan barang + JS tabel
         ============================================================ --}}
    @include('modul_inventori.barang_masuk._tabel_penerimaan')

    {{-- ============================================================
         PARTIAL: Modal input tambah GR + JS input
         ============================================================ --}}
    @include('modul_inventori.barang_masuk._modal_input')

    {{-- ============================================================
         PARTIAL: Modal edit GR draft + JS edit
         ============================================================ --}}
    @include('modul_inventori.barang_masuk._modal_edit')

    {{-- ============================================================
         PARTIAL: Modal detail GR (read-only) + JS detail
         ============================================================ --}}
    @include('modul_inventori.barang_masuk._modal_detail')

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // =============================================
        // SHARED HELPERS (dipakai oleh semua partial)
        // =============================================

        window.escapeHtml = window.escapeHtml || function (text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Custom picker modal helpers (untuk lookup PO & Gudang nested di atas modal tambah)
        window.openCustomPickerModal = function (modalId) {
            let modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            modalEl.style.zIndex = '999999';
            modalEl.style.backgroundColor = 'rgba(15, 23, 42, 0.6)';
            const dialog = modalEl.querySelector('.modal-dialog');
            if (dialog) dialog.style.zIndex = '1000000';
            $(modalEl).find('[data-bs-dismiss="modal"], .btn-close').off('click.closePicker').on('click.closePicker', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.closeCustomPickerModal(modalId);
            });
        };

        window.closeCustomPickerModal = function (modalId) {
            let modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            if ($('#modalTambahGR').hasClass('show') || $('#modalEditGR').hasClass('show') || $('#modalDetailGR').hasClass('show')) {
                document.body.classList.add('modal-open');
            }
        };

        // Picker button click handlers
        $(document).on('click', '#btnOpenPOPicker, #po_display', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.openPOPickerModal === 'function') {
                window.openPOPickerModal();
            }
        });

        $(document).on('click', '#btnOpenGudangPicker, #gudang_display', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof window.openGudangPickerModal === 'function') {
                window.openGudangPickerModal();
            }
        });

        // Auto trigger Edit Modal if ?edit_gr=ID parameter exists in URL
        const urlParams = new URLSearchParams(window.location.search);
        const editGrId = urlParams.get('edit_gr');
        if (editGrId) {
            setTimeout(function () {
                let btnEdit = $('.btn-edit-gr[data-id="' + editGrId + '"]');
                if (btnEdit.length) {
                    btnEdit.trigger('click');
                }
            }, 400);
        }
        // ===== KURS FORMATTING UTILITY =====
        /**
         * Format angka ke format Indonesia: 17.828,10
         * Dipakai untuk field kurs GR yang bisa diedit
         */
        window.formatKursID = function(value) {
            if (value === '' || value === null || value === undefined) return '';
            let num = typeof value === 'number' ? value : window.parseKursID(value);
            if (isNaN(num) || num <= 0) return '';
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 4
            }).format(num);
        };

        window.parseKursID = function(str) {
            if (typeof str === 'number') return isNaN(str) ? 0 : str;
            if (!str) return 0;
            let s = String(str).trim();
            if (!s) return 0;

            // Kasus 1: Mengandung titik dan koma (misal: "17.828,10" atau "17,828.10")
            if (s.indexOf('.') !== -1 && s.indexOf(',') !== -1) {
                if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
                    // Format Indonesia: titik = ribuan, koma = desimal -> "17828.10"
                    s = s.replace(/\./g, '').replace(',', '.');
                } else {
                    // Format US: koma = ribuan, titik = desimal -> "17828.10"
                    s = s.replace(/,/g, '');
                }
            }
            // Kasus 2: Hanya mengandung koma (misal: "17828,10" atau "18,5")
            else if (s.indexOf(',') !== -1) {
                s = s.replace(',', '.');
            }
            // Kasus 3: Hanya mengandung titik (misal: "18000" atau "17828.10" atau "18.000")
            else if (s.indexOf('.') !== -1) {
                // Jika titik dipakai sebagai pemisah ribuan (misal: "18.000" tanpa desimal)
                let parts = s.split('.');
                if (parts.length === 2 && parts[1].length === 3 && parseFloat(parts[0]) > 0 && parseFloat(parts[0]) < 1000) {
                    s = parts[0] + parts[1]; // "18000"
                } else if (parts.length > 2) {
                    s = s.replace(/\./g, ''); // "1.000.000" -> "1000000"
                }
                // Jika "17828.10" -> tetap sebagai desimal standard
            }

            let val = parseFloat(s);
            return isNaN(val) ? 0 : val;
        };

        window.setKursField = function(displayId, rawId, value) {
            let raw = typeof value === 'number' ? value : parseFloat(value) || 0;
            $('#' + displayId).val(raw > 0 ? window.formatKursID(raw) : '');
            $('#' + rawId).val(raw > 0 ? raw : '');
        };

        // Event: format saat mengetik di field kurs (tambah GR) & langsung hitung ulang
        $(document).on('input', '#kurs_gr', function () {
            let raw = window.parseKursID($(this).val());
            $('#kurs_gr_raw').val(raw > 0 ? raw : '');
            if (typeof window.recalculateAllPrices === 'function') {
                window.recalculateAllPrices();
            }
        });
        $(document).on('blur', '#kurs_gr', function () {
            let raw = window.parseKursID($(this).val());
            if (raw > 0) {
                $(this).val(window.formatKursID(raw));
                $('#kurs_gr_raw').val(raw);
            }
            if (typeof window.recalculateAllPrices === 'function') {
                window.recalculateAllPrices();
            }
        });
        $(document).on('focus', '#kurs_gr', function () {
            // Pilih seluruh teks saat fokus agar langsung bisa diketik ulang tanpa merusak format
            $(this).select();
        });

        // Event: format saat mengetik di field kurs (edit GR) & langsung hitung ulang
        $(document).on('input', '#edit_kurs_gr', function () {
            let raw = window.parseKursID($(this).val());
            $('#edit_kurs_gr_raw').val(raw > 0 ? raw : '');
            if (typeof window.recalculateEditAllPrices === 'function') {
                window.recalculateEditAllPrices();
            }
        });
        $(document).on('blur', '#edit_kurs_gr', function () {
            let raw = window.parseKursID($(this).val());
            if (raw > 0) {
                $(this).val(window.formatKursID(raw));
                $('#edit_kurs_gr_raw').val(raw);
            }
            if (typeof window.recalculateEditAllPrices === 'function') {
                window.recalculateEditAllPrices();
            }
        });
        $(document).on('focus', '#edit_kurs_gr', function () {
            // Pilih seluruh teks saat fokus agar langsung bisa diketik ulang tanpa merusak format
            $(this).select();
        });

        // ===== DRAG TO SCROLL FOR GR DETAILS TABLE =====
        window.initGrTableDragScroll = function () {
            document.querySelectorAll('.gr-table-scroll-wrapper').forEach(function (tableWrapper) {
                if (tableWrapper.dataset.dragScrollInit) return;
                tableWrapper.dataset.dragScrollInit = "true";

                let isTableDown = false;
                let tableStartX;
                let tableScrollLeft;

                tableWrapper.addEventListener('mousedown', (e) => {
                    if (e.target.closest('button, a, input, select, textarea, .dropdown-menu, label')) return;
                    isTableDown = true;
                    tableWrapper.classList.add('grabbing');
                    tableStartX = e.pageX - tableWrapper.offsetLeft;
                    tableScrollLeft = tableWrapper.scrollLeft;
                });

                tableWrapper.addEventListener('mouseleave', () => {
                    isTableDown = false;
                    tableWrapper.classList.remove('grabbing');
                });

                tableWrapper.addEventListener('mouseup', () => {
                    isTableDown = false;
                    tableWrapper.classList.remove('grabbing');
                });

                tableWrapper.addEventListener('mousemove', (e) => {
                    if (!isTableDown) return;
                    e.preventDefault();
                    const x = e.pageX - tableWrapper.offsetLeft;
                    const walk = (x - tableStartX) * 1.5;
                    tableWrapper.scrollLeft = tableScrollLeft - walk;
                });
            });
        };

        $(document).ready(function () {
            window.initGrTableDragScroll();
            $('#modalTambahGR, #modalEditGR').on('shown.bs.modal', function () {
                window.initGrTableDragScroll();
            });
        });

    </script>
    @endpush
</x-layouts>
