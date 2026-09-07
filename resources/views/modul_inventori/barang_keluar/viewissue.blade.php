<x-layouts title="Barang Keluar (Goods Issue)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* ─── Status Badges ─────────────────────────────────────── */
        .badge-draft    { background: rgba(245, 158, 11, 0.15); color: #d97706; padding: .35em .85em; border-radius: 50px; font-size: .78rem; font-weight: 700; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-pending  { background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: .35em .85em; border-radius: 50px; font-size: .78rem; font-weight: 700; border: 1px solid rgba(14, 165, 233, 0.3); }
        .badge-confirmed{ background: rgba(16, 185, 129, 0.15); color: #059669; padding: .35em .85em; border-radius: 50px; font-size: .78rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.3); }

        [data-bs-theme="dark"] .badge-draft, .dark-mode .badge-draft { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border-color: rgba(245, 158, 11, 0.4); }
        [data-bs-theme="dark"] .badge-pending, .dark-mode .badge-pending { background: rgba(14, 165, 233, 0.2); color: #38bdf8; border-color: rgba(14, 165, 233, 0.4); }
        [data-bs-theme="dark"] .badge-confirmed, .dark-mode .badge-confirmed { background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(16, 185, 129, 0.4); }

        /* ─── Tipe Badges ────────────────────────────────────────── */
        .tipe-badge { padding: .28em .75em; border-radius: 50px; font-size: .74rem; font-weight: 700; display: inline-flex; align-items: center; }
        .tipe-internal    { background: rgba(139, 92, 246, 0.12); color: #7c3aed; border: 1px solid rgba(139, 92, 246, 0.25); }
        .tipe-retur       { background: rgba(249, 115, 22, 0.12); color: #ea580c; border: 1px solid rgba(249, 115, 22, 0.25); }
        .tipe-penyesuaian { background: rgba(6, 182, 212, 0.12); color: #0891b2; border: 1px solid rgba(6, 182, 212, 0.25); }
        .tipe-lainnya     { background: rgba(100, 116, 139, 0.12); color: #475569; border: 1px solid rgba(100, 116, 139, 0.25); }

        [data-bs-theme="dark"] .tipe-internal, .dark-mode .tipe-internal { background: rgba(139, 92, 246, 0.2); color: #a78bfa; border-color: rgba(139, 92, 246, 0.4); }
        [data-bs-theme="dark"] .tipe-retur, .dark-mode .tipe-retur { background: rgba(249, 115, 22, 0.2); color: #fb923c; border-color: rgba(249, 115, 22, 0.4); }
        [data-bs-theme="dark"] .tipe-penyesuaian, .dark-mode .tipe-penyesuaian { background: rgba(6, 182, 212, 0.2); color: #22d3ee; border-color: rgba(6, 182, 212, 0.4); }
        [data-bs-theme="dark"] .tipe-lainnya, .dark-mode .tipe-lainnya { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.4); }

        /* ─── Count Badge ────────────────────────────────────────── */
        .gi-count-badge {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 14px;
            letter-spacing: 0.3px;
        }
        [data-bs-theme="dark"] .gi-count-badge,
        .dark-mode .gi-count-badge {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.4);
        }

        /* ─── Table Modern & Cinematic Horizontal Scroll ───────── */
        .table-responsive-gi {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(239, 68, 68, 0.4) transparent;
        }
        .table-responsive-gi::-webkit-scrollbar {
            height: 7px;
        }
        .table-responsive-gi::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
            border-radius: 4px;
        }
        [data-bs-theme="dark"] .table-responsive-gi::-webkit-scrollbar-track,
        .dark-mode .table-responsive-gi::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.03);
        }
        .table-responsive-gi::-webkit-scrollbar-thumb {
            background: rgba(239, 68, 68, 0.4);
            border-radius: 4px;
        }
        .table-responsive-gi::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 68, 68, 0.7);
        }

        .gi-table-scroll-hint {
            background: rgba(239, 68, 68, 0.06);
            border-bottom: 1px solid rgba(239, 68, 68, 0.12);
            color: #ef4444;
            font-size: 0.76rem;
            padding: 7px 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        [data-bs-theme="dark"] .gi-table-scroll-hint,
        .dark-mode .gi-table-scroll-hint {
            background: rgba(239, 68, 68, 0.12);
            border-bottom: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .table-modern {
            min-width: 900px !important;
            width: 100%;
            white-space: nowrap;
            margin-bottom: 0;
            border-collapse: collapse;
        }
        .table-modern th {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .74rem;
            letter-spacing: .6px;
            padding: 13px 16px !important;
            border-bottom: 2px solid #e2e8f0 !important;
            border-top: none !important;
            white-space: nowrap;
            vertical-align: middle;
        }
        [data-bs-theme="dark"] .table-modern th,
        .dark-mode .table-modern th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-bottom: 2px solid #334155 !important;
        }

        .table-modern td {
            padding: 12px 16px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            vertical-align: middle;
            color: var(--text-main, #0f172a) !important;
            white-space: nowrap;
            font-size: .88rem;
        }
        [data-bs-theme="dark"] .table-modern td,
        .dark-mode .table-modern td {
            border-bottom: 1px solid #334155 !important;
            color: #f8fafc !important;
        }

        .table-modern tbody tr {
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }
        .table-modern tbody tr:hover td {
            background-color: rgba(239, 68, 68, 0.04) !important;
        }
        [data-bs-theme="dark"] .table-modern tbody tr:hover td,
        .dark-mode .table-modern tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        /* ─── DataTables Custom Controls & Responsive Styling ───── */
        .dataTables_wrapper {
            padding: 0 !important;
        }
        .dataTables_wrapper .row:first-child {
            padding: 14px 18px;
            margin: 0 !important;
            align-items: center;
            gap: 10px 0;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            background: var(--card-bg, #ffffff);
        }
        [data-bs-theme="dark"] .dataTables_wrapper .row:first-child,
        .dark-mode .dataTables_wrapper .row:first-child {
            background: #1e293b;
            border-bottom-color: #334155;
        }

        .dataTables_wrapper .row:last-child {
            padding: 14px 18px;
            margin: 0 !important;
            align-items: center;
            border-top: 1px solid var(--border-color, #e2e8f0);
            background: var(--card-bg, #ffffff);
        }
        [data-bs-theme="dark"] .dataTables_wrapper .row:last-child,
        .dark-mode .dataTables_wrapper .row:last-child {
            background: #1e293b;
            border-top-color: #334155;
        }

        /* Page Length Select */
        .dataTables_length {
            display: flex;
            align-items: center;
        }
        .dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted, #64748b);
            margin-bottom: 0;
        }
        .dataTables_length select {
            padding: 6px 32px 6px 14px !important;
            border-radius: 50px !important;
            font-size: 0.84rem !important;
            font-weight: 600 !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            background-color: var(--bg-color, #ffffff) !important;
            color: var(--text-main, #0f172a) !important;
            outline: none !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .dataTables_length select,
        .dark-mode .dataTables_length select {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .dataTables_length select:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
        }

        /* Search Filter Input */
        .dataTables_filter {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .dataTables_filter label {
            width: 100%;
            max-width: 320px;
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 0;
        }
        .dataTables_filter input {
            width: 100% !important;
            padding: 8px 16px 8px 38px !important;
            border-radius: 50px !important;
            font-size: 0.85rem !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            background-color: var(--bg-color, #ffffff) !important;
            color: var(--text-main, #0f172a) !important;
            outline: none !important;
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .dataTables_filter input,
        .dark-mode .dataTables_filter input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .dataTables_filter input:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        .dataTables_filter label::before {
            content: "\F52A";
            font-family: "bootstrap-icons";
            position: absolute;
            left: 14px;
            color: var(--text-muted, #94a3b8);
            font-size: 0.9rem;
            pointer-events: none;
        }

        /* Info & Pagination */
        .dataTables_info {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-muted, #64748b) !important;
            padding-top: 0 !important;
        }
        [data-bs-theme="dark"] .dataTables_info,
        .dark-mode .dataTables_info {
            color: #94a3b8 !important;
        }

        .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
        }
        .dataTables_paginate .pagination {
            gap: 4px;
            margin-bottom: 0;
            flex-wrap: wrap;
        }
        .dataTables_paginate .page-item .page-link {
            border-radius: 50px !important;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid var(--border-color, #cbd5e1);
            background-color: var(--bg-color, #ffffff);
            color: var(--text-main, #0f172a);
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .dataTables_paginate .page-item .page-link,
        .dark-mode .dataTables_paginate .page-item .page-link {
            background-color: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }
        .dataTables_paginate .page-item.active .page-link {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        }
        .dataTables_paginate .page-item.disabled .page-link {
            opacity: 0.4;
            background-color: transparent !important;
            border-color: var(--border-color, #e2e8f0) !important;
        }

        @media (max-width: 768px) {
            .dataTables_wrapper .row:first-child {
                flex-direction: column-reverse;
                padding: 12px 14px;
                gap: 12px;
            }
            .dataTables_filter, .dataTables_length {
                width: 100% !important;
                justify-content: stretch !important;
            }
            .dataTables_filter label {
                max-width: 100% !important;
            }
            .dataTables_length {
                justify-content: space-between;
            }
            .dataTables_wrapper .row:last-child {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .dataTables_paginate {
                justify-content: center !important;
                width: 100%;
            }
        }

        /* ─── Item Rows in Modal ─────────────────────────────────── */
        .item-row {
            background-color: var(--bg-color, #f8fafc);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
        .item-row:last-child { margin-bottom: 0; }
        .stok-indicator { font-size: .78rem; margin-top: 4px; }
        .stok-ok    { color: #16a34a; }
        .stok-warn  { color: #d97706; }
        .stok-empty { color: #dc2626; }

        /* ─── Scrollbar & Smooth Touch for Items Container ──────── */
        .gi-items-scrollable,
        #gi_items_container,
        #edit_gi_items_container {
            max-height: 480px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-y: contain;
            scroll-behavior: smooth;
            touch-action: pan-y;
        }

        #gi_items_container::-webkit-scrollbar,
        #edit_gi_items_container::-webkit-scrollbar,
        .gi-items-scrollable::-webkit-scrollbar {
            width: 6px;
        }
        #gi_items_container::-webkit-scrollbar-track,
        #edit_gi_items_container::-webkit-scrollbar-track,
        .gi-items-scrollable::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.05);
            border-radius: 4px;
        }
        #gi_items_container::-webkit-scrollbar-thumb,
        #edit_gi_items_container::-webkit-scrollbar-thumb,
        .gi-items-scrollable::-webkit-scrollbar-thumb {
            background: rgba(239, 68, 68, 0.4);
            border-radius: 4px;
        }
        #gi_items_container::-webkit-scrollbar-thumb:hover,
        #edit_gi_items_container::-webkit-scrollbar-thumb:hover,
        .gi-items-scrollable::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 68, 68, 0.7);
        }

        /* Modal Dialog & Body Mobile Fluid Scrolling */
        .modal-dialog-scrollable .modal-body {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-y: contain;
            scroll-behavior: smooth;
        }

        @media (max-width: 768px) {
            .gi-items-scrollable,
            #gi_items_container,
            #edit_gi_items_container {
                max-height: none !important;
                overflow-y: visible !important;
                overflow-x: visible !important;
                padding-right: 0 !important;
            }
            .modal-dialog-scrollable {
                height: calc(100% - 1rem) !important;
            }
            .modal-dialog-scrollable .modal-content {
                max-height: 94vh !important;
            }
            .modal-body {
                padding: 1rem !important;
            }
        }

        /* ─── Select2 override ───────────────────────────────────── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px; font-size: .88rem; border: 1px solid var(--border-color, #cbd5e1);
            background-color: var(--card-bg, #ffffff) !important;
            color: var(--text-main, #0f172a) !important;
        }

        /* ─── DARK MODE ADAPTABILITY OVERRIDES ───────────────────── */
        [data-bs-theme="dark"], .dark-mode {
            --card-bg: #1e293b;
            --bg-color: #0f172a;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        [data-bs-theme="dark"] .card, .dark-mode .card,
        [data-bs-theme="dark"] .card-header, .dark-mode .card-header,
        [data-bs-theme="dark"] .card-body, .dark-mode .card-body {
            background-color: var(--card-bg, #1e293b) !important;
            border-color: var(--border-color, #334155) !important;
            color: var(--text-main, #f8fafc) !important;
        }

        [data-bs-theme="dark"] .text-dark, .dark-mode .text-dark,
        [data-bs-theme="dark"] .fw-bold.text-dark, .dark-mode .fw-bold.text-dark,
        [data-bs-theme="dark"] h4.text-dark, .dark-mode h4.text-dark,
        [data-bs-theme="dark"] h5.text-dark, .dark-mode h5.text-dark,
        [data-bs-theme="dark"] h6.text-dark, .dark-mode h6.text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted, .dark-mode .text-muted,
        [data-bs-theme="dark"] .text-secondary, .dark-mode .text-secondary {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .table-modern, .dark-mode .table-modern {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .table-modern th, .dark-mode .table-modern th {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
            border-bottom: 2px solid #334155 !important;
        }

        [data-bs-theme="dark"] .table-modern td, .dark-mode .table-modern td {
            background-color: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .table-modern tbody tr:hover td, .dark-mode .table-modern tbody tr:hover td {
            background-color: #334155 !important;
        }

        [data-bs-theme="dark"] .modal-content, .dark-mode .modal-content {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .modal-footer.bg-light, .dark-mode .modal-footer.bg-light,
        [data-bs-theme="dark"] .bg-light, .dark-mode .bg-light {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select,
        .dark-mode .form-control, .dark-mode .form-select {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-control:focus, [data-bs-theme="dark"] .form-select:focus,
        .dark-mode .form-control:focus, .dark-mode .form-select:focus {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #ef4444 !important;
        }

        [data-bs-theme="dark"] .item-row, .dark-mode .item-row {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

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

        [data-bs-theme="dark"] .select2-dropdown, .dark-mode .select2-dropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .select2-results__option, .dark-mode .select2-results__option {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .select2-results__option--highlighted, .dark-mode .select2-results__option--highlighted {
            background-color: #ef4444 !important;
            color: #ffffff !important;
        }
    </style>

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- ─── Page Header ─────────────────────────────────────────── -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right text-danger me-1"></i>Barang Keluar (Goods Issue)
                </h4>
                <small class="text-muted">Pencatatan pengeluaran barang dari gudang</small>
            </div>
            @can('tambah-gi')
            <button class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahGI" id="btnTambahGI">
                <i class="bi bi-plus-lg me-1"></i> Buat Pengeluaran
            </button>
            @endcan
        </div>

        <!-- ─── Table Card ───────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 py-3.5 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-list-ul fs-5"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">
                        Daftar Pengeluaran Barang
                    </h6>
                </div>
                <span class="badge rounded-pill gi-count-badge">
                    <i class="bi bi-file-earmark-text me-1"></i>{{ number_format($issues->total(), 0, ',', '.') }} Dokumen
                </span>
            </div>
            <div class="card-body p-0" id="gi_card_body">
                @if($issues->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                        <p class="text-muted mb-1">Belum ada pengeluaran barang yang dibuat.</p>
                        @can('tambah-gi')
                        <small class="text-muted">Klik tombol <strong>"Buat Pengeluaran"</strong> untuk memulai.</small>
                        @endcan
                    </div>
                @else
                    <div class="gi-table-scroll-hint d-md-none">
                        <i class="bi bi-arrow-left-right"></i> Geser tabel ke samping untuk melihat detail & aksi
                    </div>
                    <div class="table-responsive table-responsive-gi">
                        <table class="table table-hover table-modern mb-0" id="tableGI">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="min-width: 170px;">Nomor GI</th>
                                    <th style="min-width: 120px;">Tanggal</th>
                                    <th style="min-width: 220px;">Tujuan / Penerima</th>
                                    <th style="min-width: 140px;">Tipe</th>
                                    <th style="min-width: 110px;" class="text-center">Status</th>
                                    <th style="min-width: 110px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($issues as $idx => $gi)
                                <tr onclick="showDetailGI({{ $gi->id }})" id="row-gi-{{ $gi->id }}">
                                    <td class="text-muted small text-center">{{ $issues->firstItem() + $idx }}</td>
                                    <td>
                                        <span class="fw-bold text-dark font-monospace" style="font-size:.85rem">{{ $gi->nomor_gi }}</span>
                                    </td>
                                    <td data-order="{{ $gi->tanggal_gi->format('Ymd') }}{{ str_pad($gi->id, 8, '0', STR_PAD_LEFT) }}">
                                        <span style="font-size:.84rem">{{ $gi->tanggal_gi->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark" style="font-size:.85rem">{{ $gi->tujuan }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $tipeClass = match($gi->tipe_pengeluaran) {
                                                'Internal'       => 'tipe-internal',
                                                'Retur Supplier' => 'tipe-retur',
                                                'Penyesuaian'    => 'tipe-penyesuaian',
                                                default          => 'tipe-lainnya',
                                            };
                                        @endphp
                                        <span class="tipe-badge {{ $tipeClass }}">{{ $gi->tipe_pengeluaran }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($gi->status === 'Draft')
                                            <span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>
                                        @elseif($gi->status === 'Pending')
                                            <span class="badge-pending"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>
                                        @else
                                            <span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>
                                        @endif
                                    </td>
                                     <td onclick="event.stopPropagation()" class="text-center">
                                         @php
                                             $isSuperAdmin = auth()->check() && (
                                                 auth()->user()->hasRole('Super Admin') || 
                                                 auth()->user()->hasRole('Superadmin') || 
                                                 auth()->user()->hasRole('superadmin') ||
                                                 auth()->user()->hasRole('Owner') ||
                                                 (property_exists(auth()->user(), 'role') && strtolower(strval(auth()->user()->role)) === 'superadmin')
                                             );
                                             $isCreator = auth()->check() && ((int) $gi->created_by === (int) auth()->id());
                                         @endphp
                                         <div class="d-flex justify-content-center gap-1">
                                             <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5" onclick="showDetailGI({{ $gi->id }})" title="Lihat Detail & Persetujuan">
                                                 <i class="bi bi-eye"></i>
                                             </button>
                                             <a href="/goods-issue/{{ $gi->id }}/print" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Cetak PDF Dokumen" onclick="event.stopPropagation()">
                                                 <i class="bi bi-printer"></i>
                                             </a>
                                             {{-- Edit: Draft hanya pembuatnya, Pending semua yang punya hak edit, Confirmed hanya Super Admin --}}
                                             @if((($gi->status === 'Draft' && ($isCreator || $isSuperAdmin)) || $gi->status === 'Pending' || $isSuperAdmin) && (auth()->user()->can('edit-gi') || auth()->user()->can('tambah-gi')))
                                             <button class="btn btn-sm btn-outline-warning rounded-pill px-2.5 text-dark" onclick="openModalEditGI({{ $gi->id }})" title="{{ $gi->status === 'Confirmed' ? 'Edit Dokumen (Super Admin)' : ($gi->status === 'Pending' ? 'Edit Pengajuan' : 'Edit Draf') }}">
                                                 <i class="bi bi-pencil"></i>
                                             </button>
                                             @endif
                                             {{-- Hapus: Hanya Draft milik pembuatnya yang bisa dihapus (Pending terkunci; Confirmed hanya Super Admin) --}}
                                             @if((($gi->status === 'Draft' && ($isCreator || $isSuperAdmin)) || $isSuperAdmin) && auth()->user()->can('hapus-gi'))
                                             <button class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="hapusGI({{ $gi->id }}, '{{ $gi->nomor_gi }}')" title="Hapus Dokumen">
                                                 <i class="bi bi-trash3"></i>
                                             </button>
                                             @endif
                                         </div>
                                     </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($issues->hasPages())
            <div class="card-footer bg-transparent border-top py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Menampilkan <strong>{{ $issues->firstItem() ?? 0 }}</strong> - <strong>{{ $issues->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($issues->total(), 0, ',', '.') }}</strong> Dokumen
                </div>
                <div>
                    {{ $issues->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════ --}}
    {{-- MODULAR PARTIALS INCLUSION                                 --}}
    {{-- ════════════════════════════════════════════════════════════ --}}
    @include('modul_inventori.barang_keluar._modal_input')
    @include('modul_inventori.barang_keluar._modal_detail')
    @include('modul_inventori.barang_keluar._modal_edit')
    @include('modul_inventori.barang_keluar.lookup_gudang')
    @include('modul_inventori.barang_keluar.lookup_barang')
    @include('modul_inventori.barang_keluar.preview_foto')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    window.barangsData = @json($barangsData);
    let isFetchingGI = false;

    function reloadTableGI(showLoading = true) {
        if (isFetchingGI) return;
        isFetchingGI = true;

        let spinner = null;
        if (showLoading) {
            let tableCard = $('#gi_card_body').closest('.card');
            tableCard.addClass('position-relative');
            spinner = $('<div class="table-loading-overlay d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75" style="z-index: 1000;"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            tableCard.append(spinner);
        }

        // Capture current search input and pagination state if DataTable exists
        let currentSearch = '';
        let currentPage = 0;
        if ($.fn.DataTable.isDataTable('#tableGI')) {
            let dt = $('#tableGI').DataTable();
            currentSearch = dt.search();
            currentPage = dt.page();
        }

        $.get(window.location.href, function (html) {
            let $response = $(html);
            let $newRows = $response.find('#tableGI tbody tr');
            let $currentRows = $('#tableGI tbody tr');

            // If structure or row count changed, update card body smoothly
            if ($newRows.length !== $currentRows.length || $('#tableGI tbody tr td').length <= 1) {
                let newTbodyHtml = $response.find('#tableGI tbody').html();
                let currentTbodyHtml = $('#tableGI tbody').html();
                if (newTbodyHtml !== currentTbodyHtml) {
                    if ($.fn.DataTable.isDataTable('#tableGI')) {
                        $('#tableGI').DataTable().destroy();
                    }
                    $('#gi_card_body').html($response.find('#gi_card_body').html());
                    
                    let newBadgeCount = $response.find('.gi-count-badge').text();
                    if (newBadgeCount) $('.gi-count-badge').text(newBadgeCount);

                    if ($('#tableGI').length && $('#tableGI tbody tr').length > 0 && !$('#tableGI tbody tr td').hasClass('dataTables_empty')) {
                        let newDt = $('#tableGI').DataTable(giDataTableConfig);
                        if (currentSearch) newDt.search(currentSearch).draw(false);
                        if (currentPage) newDt.page(currentPage).draw(false);
                    }
                }
            } else {
                // If row count is identical, update each row's status & actions in place without flickering / re-init
                $newRows.each(function () {
                    let rowId = $(this).attr('id');
                    if (rowId) {
                        let $currentRow = $('#' + rowId);
                        if ($currentRow.length) {
                            let newStatusHtml = $(this).find('td:nth-child(6)').html();
                            let curStatusHtml = $currentRow.find('td:nth-child(6)').html();
                            if (newStatusHtml && newStatusHtml.trim() !== curStatusHtml.trim()) {
                                $currentRow.find('td:nth-child(6)').html(newStatusHtml);
                            }

                            let newActionHtml = $(this).find('td:nth-child(7)').html();
                            let curActionHtml = $currentRow.find('td:nth-child(7)').html();
                            if (newActionHtml && newActionHtml.trim() !== curActionHtml.trim()) {
                                $currentRow.find('td:nth-child(7)').html(newActionHtml);
                            }
                        }
                    }
                });
            }
        }).always(function () {
            if (spinner) spinner.remove();
            isFetchingGI = false;
        });
    }

    const giDataTableConfig = {
        dom: '<"row mb-0"<"col-md-6 col-12"l><"col-md-6 col-12"f>>rt<"row mt-0"<"col-md-5 col-12"i><"col-md-7 col-12"p>>',
        order: [],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 data',
            infoFiltered: '(disaring dari _MAX_ total)',
            zeroRecords: 'Tidak ada data pengeluaran yang cocok',
            search: '',
            searchPlaceholder: 'Cari no. GI, penerima, tipe...',
            paginate: { 
                previous: '<i class="bi bi-chevron-left"></i>', 
                next: '<i class="bi bi-chevron-right"></i>' 
            }
        }
    };

    $(document).ready(function () {
        @if($issues->count())
        $('#tableGI').DataTable(giDataTableConfig);
        @endif

        // ─── REAL-TIME POLLER (1 DETIK) ───────────────────────────────
        // Secara instan memperbarui status (Draft -> Pending -> Confirmed) tanpa mengganggu interaksi modal atau pencarian
        setInterval(function () {
            let isSearching = $('#tableGI_filter input').is(':focus');
            let isModalOpen = $('.modal.show').length > 0;
            if (!isSearching && !isModalOpen) {
                reloadTableGI(false); // Silent background auto-sync
            }
        }, 1000);
    });
    </script>
    @endpush
</x-layouts>
