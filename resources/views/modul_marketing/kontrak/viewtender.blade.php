<x-layouts title="Manajemen Proyek Tender & Kontrak">

    @push('styles')
    <style>
        /* ==========================================================
           MODERN CINEMATIC TENDER & PIPELINE KANBAN STYLES
           ========================================================== */
        :root {
            --tender-bg-surface: #ffffff;
            --tender-border: #e2e8f0;
            --tender-text-main: #1e293b;
            --tender-text-muted: #64748b;
        }
        [data-bs-theme="dark"] {
            --tender-bg-surface: #1e293b;
            --tender-border: rgba(255, 255, 255, 0.09);
            --tender-text-main: #f8fafc;
            --tender-text-muted: #94a3b8;
        }

        /* Frosted Glass KPI Cards */
        .tender-kpi-card {
            background: var(--tender-bg-surface);
            border: 1px solid var(--tender-border);
            border-radius: 18px;
            padding: 1.25rem 1.4rem;
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        [data-bs-theme="dark"] .tender-kpi-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        .tender-kpi-card:hover {
            transform: translateY(-3px);
            border-color: #10b981;
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.12);
        }
        .tender-kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
        }

        /* View Switcher Tabs */
        .tender-view-pills {
            background: rgba(0, 0, 0, 0.04);
            padding: 4px;
            border-radius: 14px;
            display: inline-flex;
            border: 1px solid var(--tender-border);
        }
        [data-bs-theme="dark"] .tender-view-pills {
            background: rgba(15, 23, 42, 0.6);
        }
        .tender-view-btn {
            border: none;
            background: transparent;
            color: var(--tender-text-muted);
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .tender-view-btn.active {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }

        /* KANBAN PIPELINE BOARD */
        .kanban-board-wrapper {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: thin;
            min-height: 550px;
        }
        .kanban-column {
            flex: 0 0 320px;
            background: rgba(241, 245, 249, 0.6);
            border: 1px solid var(--tender-border);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 260px);
        }
        [data-bs-theme="dark"] .kanban-column {
            background: rgba(15, 23, 42, 0.5);
            border-color: rgba(255, 255, 255, 0.06);
        }
        .kanban-col-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--tender-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kanban-col-body {
            padding: 12px;
            overflow-y: auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            scrollbar-width: thin;
        }

        /* KANBAN CARDS */
        .kanban-card {
            background: var(--tender-bg-surface);
            border: 1px solid var(--tender-border);
            border-radius: 14px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        [data-bs-theme="dark"] .kanban-card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .kanban-card:hover {
            transform: translateY(-3px) scale(1.01);
            border-color: #10b981;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.18);
        }
        .kanban-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--tender-text-main);
            margin-bottom: 6px;
        }
        .kanban-card-value {
            font-size: 1.05rem;
            font-weight: 800;
            font-family: var(--bs-font-monospace);
        }

        /* Priority Dot Badges */
        .badge-priority-high {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge-priority-medium {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .badge-priority-low {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Nav Tabs in Modals */
        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--tender-text-muted);
            font-weight: 700;
            padding: 0.75rem 1.25rem;
            position: relative;
            background: transparent;
            transition: all 0.2s ease;
        }
        .nav-tabs-custom .nav-link.active {
            color: #10b981;
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #10b981;
            border-radius: 3px;
        }

        /* Category Filter Chips in Lookup Modal */
        .btn-kategori-tender-chip {
            border: 1px solid var(--tender-border);
            color: var(--tender-text-muted);
            background: transparent;
            font-size: 0.75rem;
            transition: all 0.2s ease;
        }
        .btn-kategori-tender-chip:hover {
            border-color: #10b981;
            color: #10b981;
        }
        .btn-kategori-tender-chip.active {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.35);
        }

        /* Modal Sizing (Ultra Spacious & Wide for Executive Dashboard) */
        .modal-custom-xl {
            max-width: 1540px !important;
            width: 96vw !important;
        }
        .modal-custom-xxl {
            max-width: 1580px !important;
            width: 96vw !important;
        }
        @media (min-width: 1600px) {
            .modal-custom-xl {
                max-width: 1580px !important;
            }
            .modal-custom-xxl {
                max-width: 1640px !important;
            }
        }

        /* ==========================================================
           CINEMATIC & MODERN TENDER TABLE STYLES (DARK & LIGHT)
           ========================================================== */
        .tender-table-card {
            background: var(--tender-bg-surface);
            border: 1px solid var(--tender-border);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .tender-table-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }

        .tender-table-header {
            background: transparent;
            border-bottom: 1px solid var(--tender-border);
        }
        [data-bs-theme="dark"] .tender-table-header {
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* Modern Table Aesthetics */
        .tender-modern-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        .tender-modern-table thead th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 0.72rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            padding: 1rem 0.85rem !important;
            border-top: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }
        [data-bs-theme="dark"] .tender-modern-table thead th {
            background: #141b2d !important;
            color: #94a3b8 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .tender-modern-table tbody tr {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            background: transparent !important;
        }
        .tender-modern-table tbody tr:hover {
            background: rgba(16, 185, 129, 0.04) !important;
            box-shadow: inset 3px 0 0 #10b981 !important;
        }
        [data-bs-theme="dark"] .tender-modern-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03) !important;
            box-shadow: inset 3px 0 0 #10b981 !important;
        }

        .tender-modern-table tbody td {
            padding: 1.05rem 0.85rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid var(--tender-border) !important;
            border-top: none !important;
        }
        [data-bs-theme="dark"] .tender-modern-table tbody td {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        /* Number Badge */
        .table-no-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.04);
            color: var(--tender-text-muted);
            font-weight: 800;
            font-family: var(--bs-font-monospace);
            font-size: 0.78rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--tender-border);
        }
        [data-bs-theme="dark"] .table-no-badge {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.09);
            color: #94a3b8;
        }

        /* Contract Badge */
        .badge-kontrak-tender {
            background: rgba(59, 130, 246, 0.08);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
            font-family: var(--bs-font-monospace);
            font-size: 0.74rem;
            padding: 4px 9px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 700;
        }
        [data-bs-theme="dark"] .badge-kontrak-tender {
            background: rgba(59, 130, 246, 0.15);
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.25);
        }

        /* Category Badges */
        .badge-kategori-b2g {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        [data-bs-theme="dark"] .badge-kategori-b2g {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.35);
        }

        .badge-kategori-tni {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        [data-bs-theme="dark"] .badge-kategori-tni {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.35);
        }

        .badge-kategori-bumn {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        [data-bs-theme="dark"] .badge-kategori-bumn {
            background: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, 0.35);
        }

        .badge-kategori-b2b {
            background: rgba(168, 85, 247, 0.12);
            color: #9333ea;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        [data-bs-theme="dark"] .badge-kategori-b2b {
            background: rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
            border-color: rgba(168, 85, 247, 0.35);
        }

        /* Action Buttons */
        .btn-table-action {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }
        .btn-table-action:hover {
            transform: translateY(-2px) scale(1.08);
        }

        .btn-action-print {
            background: rgba(6, 182, 212, 0.12);
            color: #0891b2;
            border-color: rgba(6, 182, 212, 0.25);
        }
        .btn-action-print:hover {
            background: #0891b2;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(8, 145, 178, 0.35);
        }
        [data-bs-theme="dark"] .btn-action-print {
            background: rgba(6, 182, 212, 0.18);
            color: #67e8f9;
            border-color: rgba(103, 232, 249, 0.25);
        }
        [data-bs-theme="dark"] .btn-action-print:hover {
            background: #06b6d4;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
        }

        .btn-action-view {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            border-color: rgba(59, 130, 246, 0.25);
        }
        .btn-action-view:hover {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        [data-bs-theme="dark"] .btn-action-view {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.25);
        }
        [data-bs-theme="dark"] .btn-action-view:hover {
            background: #3b82f6;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-action-edit {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border-color: rgba(245, 158, 11, 0.25);
        }
        .btn-action-edit:hover {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
        }
        [data-bs-theme="dark"] .btn-action-edit {
            background: rgba(245, 158, 11, 0.18);
            color: #fcd34d;
            border-color: rgba(252, 211, 77, 0.25);
        }
        [data-bs-theme="dark"] .btn-action-edit:hover {
            background: #f59e0b;
            color: #0f172a;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        /* DataTables Controls Integration */
        .dataTables_wrapper {
            padding: 1rem 1.25rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--tender-text-muted);
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--tender-border) !important;
            border-radius: 10px !important;
            padding: 5px 28px 5px 12px !important;
            font-size: 0.8rem !important;
            background-color: var(--tender-bg-surface) !important;
            color: var(--tender-text-main) !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--tender-border) !important;
            border-radius: 20px !important;
            padding: 6px 16px !important;
            font-size: 0.82rem !important;
            background-color: var(--tender-bg-surface) !important;
            color: var(--tender-text-main) !important;
            outline: none !important;
            transition: all 0.2s ease !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
        }
        .dataTables_wrapper .dataTables_info {
            color: var(--tender-text-muted) !important;
            font-size: 0.8rem !important;
            padding-top: 1rem !important;
            font-weight: 500 !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.85rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid transparent !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            padding: 5px 12px !important;
            color: var(--tender-text-muted) !important;
            transition: all 0.2s ease !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35) !important;
        }
        /* ==========================================================
           CINEMATIC CUSTOMER LOOKUP MODAL (DUAL THEME)
           ========================================================== */
        .modal-customer-cinematic {
            border-radius: 24px !important;
            background: #ffffff !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25) !important;
        }
        [data-bs-theme="dark"] .modal-customer-cinematic {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6) !important;
        }

        .customer-lookup-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 16px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
        }
        .customer-lookup-card:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.03);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
        }
        [data-bs-theme="dark"] .customer-lookup-card {
            background: rgba(15, 23, 42, 0.5);
            border-color: rgba(255, 255, 255, 0.07);
        }
        [data-bs-theme="dark"] .customer-lookup-card:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
        }

        .customer-avatar-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.92rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }
        [data-bs-theme="dark"] .customer-avatar-badge {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(29, 78, 216, 0.3) 100%);
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.25);
        }

        .customer-lookup-scroll {
            max-height: 440px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(150, 150, 150, 0.3) transparent;
        }
        .customer-lookup-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .customer-lookup-scroll::-webkit-scrollbar-thumb {
            background: rgba(150, 150, 150, 0.3);
            border-radius: 10px;
        }
    </style>
    @endpush

    <div class="container-fluid px-3 px-md-4 py-3">
        
        <!-- Header & Multi-Tenant Context -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center">
                            <i class="bi bi-briefcase-fill fs-5"></i>
                        </span>
                        Manajemen Proyek Tender & Kontrak
                    </h4>
                    @if($activeCompany)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fs-7 d-inline-flex align-items-center gap-1.5 shadow-sm">
                        <i class="bi bi-building"></i>
                        <span>PT: <strong>{{ $activeCompany->nama_perusahaan }}</strong></span>
                        @if($activeBranch)
                            <span class="mx-1 text-muted">|</span>
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ $activeBranch->nama_cabang }}</span>
                        @endif
                    </span>
                    @endif
                </div>
                <div class="text-muted small">Pencatatan lelang, spesifikasi kebutuhan barang, berkas RKS, penawaran resmi, hingga konversi otomatis ke SPK Proyek.</div>
            </div>

            <!-- Action Buttons & View Switcher -->
            <div class="d-flex align-items-center gap-2.5">
                <div class="tender-view-pills">
                    <button type="button" class="tender-view-btn active" id="btn_view_kanban">
                        <i class="bi bi-kanban-fill"></i> Pipeline Kanban
                    </button>
                    <button type="button" class="tender-view-btn" id="btn_view_table">
                        <i class="bi bi-table"></i> DataTables
                    </button>
                </div>

                @can('tambah-tender')
                <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold d-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTender">
                    <i class="bi bi-plus-circle-fill fs-6"></i>
                    <span>+ Proposal Tender Baru</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- 4 FROSTED GLASS KPI METRICS -->
        <div class="row g-3 mb-4">
            <!-- Total Tender -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="tender-kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Total Proyek Tender</div>
                            <div class="fs-2 fw-bold mt-1 text-body font-monospace">{{ number_format($countTotal) }}</div>
                            <div class="text-muted small mt-1"><i class="bi bi-clock-history me-1"></i>{{ $countActive }} Proyek Aktif Berjalan</div>
                        </div>
                        <div class="tender-kpi-icon bg-primary-subtle text-primary">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tender Won & Win Rate -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="tender-kpi-card" style="border-color: rgba(16, 185, 129, 0.3);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-success small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Tender Menang (Won)</div>
                            <div class="fs-2 fw-bold mt-1 text-success font-monospace">{{ number_format($countWon) }} <span class="fs-6 text-muted fw-normal">Proyek</span></div>
                            <div class="text-success small fw-bold mt-1"><i class="bi bi-trophy-fill me-1"></i>Win Rate: {{ $winRate }}%</div>
                        </div>
                        <div class="tender-kpi-icon bg-success-subtle text-success">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nilai Kontrak Won (IDR) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="tender-kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-success small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Nilai Kontrak Deal (Won)</div>
                            <div class="fs-4 fw-bold mt-1 text-success font-monospace">Rp {{ number_format($valueWon, 0, ',', '.') }}</div>
                            <div class="text-muted small mt-1"><i class="bi bi-check2-all me-1"></i>Omset SPK Tervalidasi</div>
                        </div>
                        <div class="tender-kpi-icon bg-success-subtle text-success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pipeline Berjalan (Draft, Sent, Negosiasi) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="tender-kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-info small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Pipeline Tender Berjalan</div>
                            <div class="fs-4 fw-bold mt-1 text-info font-monospace">Rp {{ number_format($valuePipeline, 0, ',', '.') }}</div>
                            <div class="text-muted small mt-1"><i class="bi bi-graph-up-arrow me-1"></i>Potensi Pendapatan</div>
                        </div>
                        <div class="tender-kpi-icon bg-info-subtle text-info">
                            <i class="bi bi-funnel-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== VIEW 1: KANBAN PIPELINE BOARD (DEFAULT ACTIVE) ===== -->
        <div id="section_kanban_view">
            <div class="kanban-board-wrapper" id="kanban_board_container">
                <!-- Injected Dynamically via AJAX -->
                <div class="w-100 text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="text-muted mt-2 small">Memuat Pipeline Kanban...</p>
                </div>
            </div>
        </div>

        <!-- ===== VIEW 2: DATATABLES TABLE VIEW ===== -->
        <div id="section_table_view" class="d-none">
            <div class="tender-table-card overflow-hidden mb-4">
                <div class="tender-table-header p-3.5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi bi-table fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-body">Daftar Rekapitulasi Proyek Tender</h6>
                            <div class="text-muted small" style="font-size:0.75rem;">Manajemen data kontrak, penawaran harga & status lelang aktif</div>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1.5 bg-body-tertiary px-2 py-1 rounded-3 border">
                            <i class="bi bi-funnel text-muted small"></i>
                            <select class="form-select form-select-sm border-0 bg-transparent py-1 pe-4 fw-semibold text-body" id="table_filter_status" style="width: 175px; font-size: 0.8rem; box-shadow: none;">
                                <option value="all">Semua Status</option>
                                <option value="Draft">Draft</option>
                                <option value="Pending Approval">Menunggu Approval</option>
                                <option value="Approved">Disetujui (Approved)</option>
                                <option value="Rejected">Ditolak (Rejected)</option>
                                <option value="Sent">Sent (Dikirim)</option>
                                <option value="Negotiation">Negosiasi</option>
                                <option value="Won">Won (Menang)</option>
                                <option value="Lost">Lost (Kalah)</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 bg-body-tertiary px-2 py-1 rounded-3 border">
                            <i class="bi bi-flag text-muted small"></i>
                            <select class="form-select form-select-sm border-0 bg-transparent py-1 pe-4 fw-semibold text-body" id="table_filter_priority" style="width: 145px; font-size: 0.8rem; box-shadow: none;">
                                <option value="all">Semua Prioritas</option>
                                <option value="High">Tinggi (High)</option>
                                <option value="Medium">Sedang (Medium)</option>
                                <option value="Low">Rendah (Low)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 w-100 tender-modern-table" id="tableTender">
                        <thead>
                            <tr>
                                <th class="text-center ps-3" width="4%">NO</th>
                                <th width="13%">NO. KONTRAK / RKS</th>
                                <th width="21%">NAMA PROYEK & KLIEN</th>
                                <th width="12%">ENTITAS</th>
                                <th width="11%">KATEGORI INSTANSI</th>
                                <th class="text-end" width="13%">NILAI ESTIMASI (IDR)</th>
                                <th class="text-center" width="10%">DEADLINE</th>
                                <th class="text-center" width="8%">PRIORITAS</th>
                                <th class="text-center" width="9%">STATUS</th>
                                <th class="text-center pe-3" width="8%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Cari Customer Global (Cinematic Dual Theme) -->
    <div class="modal fade" id="modalCariCustomer" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-customer-cinematic overflow-hidden">
                <!-- Modal Header with Gradient Accent -->
                <div class="modal-header border-0 pb-2 pt-3.5 px-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-3">
                        <span class="p-2.5 rounded-3 d-inline-flex align-items-center justify-content-center shadow-xs" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #ffffff;">
                            <i class="bi bi-building-check fs-5"></i>
                        </span>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-black text-body mb-0">Pilih Customer / Instansi</h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size:0.7rem;" id="badge_total_customer">{{ $customers->count() }} Terdaftar</span>
                            </div>
                            <div class="text-muted small" style="font-size:0.78rem;">Pilih instansi pemerintah, BUMN, TNI/POLRI, atau perusahaan klien untuk tender.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 pt-3">
                    <!-- Search Input with Clear Button -->
                    <div class="mb-3.5 position-relative">
                        <input type="text" id="search_customer_input" class="form-control tender-input rounded-pill ps-5 pe-5 py-2.5 shadow-xs" placeholder="Ketik nama instansi, nama customer, atau kode..." autocomplete="off">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-primary fs-6"></i>
                        <button type="button" id="btn_clear_search_customer" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted border-0 d-none" style="background: transparent;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Cards Container -->
                    <div class="customer-lookup-scroll d-flex flex-column gap-2 px-1" id="customer_lookup_container">
                        @foreach($customers as $c)
                        @php
                            $corpName = $c->nama_perusahaan ?: $c->nama_customer;
                            $initials = mb_strtoupper(mb_substr($corpName, 0, 2));
                            if (str_starts_with(mb_strtoupper($corpName), 'PT ')) {
                                $initials = 'PT';
                            } elseif (str_starts_with(mb_strtoupper($corpName), 'CV ')) {
                                $initials = 'CV';
                            } elseif (str_starts_with(mb_strtoupper($corpName), 'UD ')) {
                                $initials = 'UD';
                            }
                        @endphp
                        <div class="customer-lookup-card customer-lookup-row d-flex align-items-center justify-content-between gap-3" 
                             data-id="{{ $c->id }}" 
                             data-name="{{ $corpName }}"
                             data-pic="{{ $c->nama_customer }}"
                             data-search="{{ strtolower($corpName . ' ' . $c->nama_customer . ' ' . $c->kode_customer . ' ' . $c->telepon) }}">
                            
                            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                <div class="customer-avatar-badge font-monospace shadow-2xs">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                        <h6 class="fw-bold text-body mb-0 text-truncate fs-6">{{ $corpName }}</h6>
                                        @if($c->kode_customer)
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.68rem;">{{ $c->kode_customer }}</span>
                                        @endif
                                        @if($c->jenis_customer)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">{{ $c->jenis_customer }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small d-flex align-items-center gap-3 flex-wrap" style="font-size: 0.76rem;">
                                        @if($c->nama_customer && $c->nama_customer !== $c->nama_perusahaan)
                                            <span><i class="bi bi-person-badge text-primary me-1"></i>PIC: <strong>{{ $c->nama_customer }}</strong></span>
                                        @endif
                                        @if($c->telepon || $c->hp)
                                            <span><i class="bi bi-telephone text-success me-1"></i>{{ $c->telepon ?: $c->hp }}</span>
                                        @endif
                                        @if($c->email)
                                            <span class="d-none d-md-inline text-truncate" style="max-width: 180px;"><i class="bi bi-envelope text-secondary me-1"></i>{{ $c->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-primary btn-select-customer rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs transition hover-scale">
                                    <i class="bi bi-check-lg"></i>
                                    <span>Pilih</span>
                                </button>
                            </div>
                        </div>
                        @endforeach

                        <!-- Empty Search Fallback -->
                        <div id="customer_lookup_empty" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle bg-body-tertiary text-muted d-inline-flex mb-2">
                                <i class="bi bi-search fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Customer Tidak Ditemukan</h6>
                            <p class="text-muted small mb-0">Tidak ada instansi atau customer yang cocok dengan kata kunci pencarian Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cari Barang Tender Global -->
    <div class="modal fade" id="modalCariBarangTender" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content tender-modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
                <div class="modal-header border-0 pb-2 pt-3.5 px-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-3">
                        <span class="p-2.5 rounded-3 bg-primary-subtle text-primary d-inline-flex shadow-xs"><i class="bi bi-box-seam-fill fs-5"></i></span>
                        <div>
                            <h5 class="modal-title fw-black text-body mb-0">Pilih Barang / Jasa Katalog</h5>
                            <div class="text-muted small" style="font-size:0.78rem;">Pilih produk untuk kebutuhan tender berdasarkan katalog master barang & paket bundle.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <div class="mb-3 position-relative">
                        <input type="text" id="search_barang_tender_input" class="form-control tender-input rounded-pill ps-5 py-2.5 shadow-xs" placeholder="Ketik nama barang, kode SKU, atau kategori...">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-muted fs-6"></i>
                    </div>

                    <!-- Filter Kategori Tabs (Clean Horizontal Scroll tanpa Scrollbar Kasar) -->
                    <div class="d-flex align-items-center gap-2 mb-3 overflow-x-auto pb-1" id="kategori_filter_chips_container" style="scrollbar-width: none; -ms-overflow-style: none;">
                        <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold btn-kategori-tender-chip active flex-shrink-0" data-kategori-id="all">
                            🌐 Semua Kategori ({{ $barangList->count() }})
                        </button>
                        @foreach($kategoriList as $kat)
                        @php
                            $countKat = $barangList->where('kategori_barang_id', $kat->id)->count();
                        @endphp
                        <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold btn-kategori-tender-chip flex-shrink-0" data-kategori-id="{{ $kat->id }}">
                            🏷️ {{ $kat->nama_kategori }} ({{ $countKat }})
                        </button>
                        @endforeach
                    </div>

                    <div class="table-responsive border rounded-4 tender-table-wrapper bg-body" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="table_lookup_barang_tender" style="cursor: pointer;">
                            <thead class="bg-body-tertiary sticky-top border-bottom">
                                <tr>
                                    <th class="ps-4 py-3 fw-bold text-muted small" width="52%;">PRODUK & SPESIFIKASI</th>
                                    <th class="py-3 fw-bold text-muted small" width="22%;">KATEGORI</th>
                                    <th class="text-center py-3 fw-bold text-muted small" width="12%;">SATUAN</th>
                                    <th class="text-center py-3 pe-4 fw-bold text-muted small" width="14%;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangList as $b)
                                @php
                                    $isBundle = !empty($b->is_bundle);
                                    $bundleComps = $b->bundle_components ?? [];
                                    $bundleJson = json_encode($bundleComps);
                                    $bFotoPath = ($b->primaryFoto) ? $b->primaryFoto->foto_path : (($b->fotos && $b->fotos->first()) ? $b->fotos->first()->foto_path : null);
                                    $bFotoUrl = $bFotoPath ? (str_starts_with($bFotoPath, 'uploads/') ? asset($bFotoPath) : asset('storage/' . $bFotoPath)) : asset('uploads/barang/placeholder_garment.png');
                                    $bPhotos = [];
                                    if ($b->fotos && $b->fotos->isNotEmpty()) {
                                        foreach ($b->fotos as $f) {
                                            $p = $f->foto_path;
                                            $u = str_starts_with($p, 'uploads/') ? asset($p) : asset('storage/' . $p);
                                            if (!in_array($u, $bPhotos)) $bPhotos[] = $u;
                                        }
                                    }
                                    if (empty($bPhotos)) {
                                        $bPhotos[] = $bFotoUrl;
                                    }
                                    $bPhotosJson = json_encode($bPhotos);
                                @endphp
                                <tr class="barang-lookup-row border-bottom" 
                                    data-id="{{ $b->id }}" 
                                    data-name="{{ $b->nama_barang }}" 
                                    data-code="{{ $b->kode_barang }}" 
                                    data-kategori-id="{{ $b->kategori_barang_id ?: 'none' }}"
                                    data-satuan="{{ $b->satuan ? $b->satuan->nama_satuan : 'Pcs' }}" 
                                    data-is-bundle="{{ $isBundle ? '1' : '0' }}"
                                    data-bundle-components="{{ e($bundleJson) }}"
                                    data-photo-url="{{ $bFotoUrl }}"
                                    data-photos="{{ e($bPhotosJson) }}">
                                    <td class="ps-4 py-3 align-middle">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative flex-shrink-0" style="width:52px;height:52px;border-radius:12px;overflow:hidden;border:1px solid rgba(125,125,125,0.2);background:rgba(125,125,125,0.06);">
                                                <img src="{{ $bFotoUrl }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                                @if(count($bPhotos) > 1)
                                                    <span class="position-absolute bottom-0 end-0 badge bg-dark bg-opacity-85 text-white font-monospace" style="font-size:0.55rem; padding: 1px 3px; border-top-left-radius: 4px;">
                                                        {{ count($bPhotos) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-bold text-body fs-6 mb-1">
                                                    {{ $b->nama_barang }}
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                                                        {{ $b->kode_barang }}
                                                    </span>
                                                    @if($isBundle)
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle py-0.5 px-2 font-monospace" style="font-size:0.68rem;">
                                                            <i class="bi bi-boxes me-1"></i> Paket Bundle
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($isBundle && !empty($bundleComps))
                                                <div class="mt-1.5 d-flex flex-wrap align-items-center gap-1.5">
                                                    <span class="text-primary fw-bold" style="font-size: 0.7rem;">📦 Isi Paket:</span>
                                                    @foreach($bundleComps as $c)
                                                        <span class="badge bg-body-secondary text-body border font-monospace" style="font-size: 0.68rem;">{{ $c['qty'] + 0 }}x {{ $c['nama_barang'] }}</span>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 align-middle">
                                        @if($b->kategori)
                                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill fw-semibold">{{ $b->kategori->nama_kategori }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3 align-middle">
                                        <span class="badge bg-body-secondary text-body font-monospace px-3 py-1.5 rounded-pill fw-bold">{{ $b->satuan ? $b->satuan->nama_satuan : 'Pcs' }}</span>
                                    </td>
                                    <td class="text-center py-3 pe-4 align-middle">
                                        <button type="button" class="btn btn-sm btn-success btn-select-barang-tender rounded-pill px-3.5 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5">
                                            <i class="bi bi-check-lg"></i>
                                            <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Sub-Components -->
    @include('modul_marketing.kontrak.tambah')
    @include('modul_marketing.kontrak.edit')
    @include('modul_marketing.kontrak.detail')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        window.formatRp = function(amount) {
            if (!amount && amount !== 0) return 'Rp 0';
            return 'Rp ' + Number(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        };

        window.escapeHtml = function(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        $(document).ready(function () {

            // ===== REAL-TIME INPUT CHARACTER LIMITER & SECURITY ALERT =====
            $(document).on('input keyup paste change', '.char-limited-input', function (e) {
                let $input = $(this);
                let max = parseInt($input.data('max') || $input.attr('maxlength') || 0);
                let name = $input.data('name') || 'Bidang isian';
                let val = $input.val() || '';
                let id = $input.attr('id');
                let counterId = '#counter_' + id;

                if (max > 0) {
                    let len = val.length;
                    
                    // Update badge counter
                    if ($(counterId).length) {
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

                    // If exceeds / pasted beyond limit
                    if (len > max) {
                        $input.val(val.substring(0, max));
                        if ($(counterId).length) $(counterId).text(`${max}/${max}`);
                    }

                    if (len >= max && (e.type === 'paste' || e.type === 'keyup' || e.type === 'input')) {
                        if (!$input.data('toast-active')) {
                            $input.data('toast-active', true);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Batas Maksimal Karakter!',
                                html: `
                                    <div class="text-center p-1">
                                        <p class="mb-2">Bidang <strong>${escapeHtml(name)}</strong> telah mencapai batas maksimal <strong>${max} karakter</strong>.</p>
                                        <div class="alert alert-warning py-2 px-3 rounded-3 small mb-0 d-inline-block">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i> Karakter berlebih tidak akan tersimpan.
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

            // ===== BERKAS / FILE UPLOAD VALIDATION (MAX 10 MB PER FILE) =====
            $(document).on('change', '.tender-doc-file-input', function () {
                const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB in Bytes
                const MAX_FILE_SIZE_MB = 10;
                
                let file = this.files[0];
                let row = $(this).closest('tr');
                let sizeBadge = row.find('.file-size-badge');
                let nameInput = row.find('input[name="document_names[]"]');

                if (file) {
                    let fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

                    // Check if file exceeds 10 MB
                    if (file.size > MAX_FILE_SIZE_BYTES) {
                        $(this).val(''); // Reset file input
                        if (sizeBadge.length) sizeBadge.addClass('d-none').text('');

                        Swal.fire({
                            icon: 'warning',
                            title: 'Ukuran Berkas Terlalu Besar!',
                            html: `
                                <div class="text-start p-2">
                                    <p class="mb-2">Berkas <strong>${escapeHtml(file.name)}</strong> berukuran <strong><span class="text-danger">${fileSizeMB} MB</span></strong>.</p>
                                    <div class="alert alert-warning p-2.5 rounded-3 mb-0 small">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Batas maksimal yang diperbolehkan adalah <strong>${MAX_FILE_SIZE_MB} MB</strong> per berkas. Silakan gunakan berkas yang lebih kecil atau lakukan kompresi.
                                    </div>
                                </div>
                            `,
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'Saya Mengerti'
                        });
                        return;
                    }

                    // Valid file: Display size badge & auto-fill document name
                    if (sizeBadge.length) {
                        sizeBadge.removeClass('d-none bg-danger-subtle text-danger')
                            .addClass('bg-success-subtle text-success')
                            .html(`<i class="bi bi-file-check me-0.5"></i> ${fileSizeMB} MB`);
                    }

                    if (nameInput.length && !nameInput.val()) {
                        let cleanName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                        nameInput.val(cleanName.substring(0, 40));
                    }
                } else {
                    if (sizeBadge.length) sizeBadge.addClass('d-none').text('');
                }
            });
            
            // ===== VIEW SWITCHER (KANBAN VS TABLE) =====
            $('#btn_view_kanban').on('click', function () {
                $(this).addClass('active');
                $('#btn_view_table').removeClass('active');
                $('#section_kanban_view').removeClass('d-none');
                $('#section_table_view').addClass('d-none');
                loadKanbanBoard();
            });

            $('#btn_view_table').on('click', function () {
                $(this).addClass('active');
                $('#btn_view_kanban').removeClass('active');
                $('#section_table_view').removeClass('d-none');
                $('#section_kanban_view').addClass('d-none');
                tableTender.ajax.reload();
            });

            // ===== LOAD KANBAN BOARD =====
            function loadKanbanBoard() {
                $.get('{{ route("tender.kanban-data") }}', function (res) {
                    let stages = res.stages;
                    let html = '';

                    Object.keys(stages).forEach(stageKey => {
                        let st = stages[stageKey];
                        let totalStageVal = st.items.reduce((sum, item) => sum + Number(item.nilai_estimasi), 0);

                        html += `
                            <div class="kanban-column" data-stage="${stageKey}">
                                <div class="kanban-col-header">
                                    <div>
                                        <span class="badge ${st.badge_class} px-2.5 py-1 rounded-pill fw-bold" style="font-size:0.75rem;">
                                            ${st.label}
                                        </span>
                                        <div class="text-muted small fw-bold mt-1" style="font-size:0.72rem;">
                                            ${st.items.length} Tender • ${formatRp(totalStageVal)}
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-col-body">
                        `;

                        if (st.items.length === 0) {
                            html += `
                                <div class="text-center py-4 text-muted small fst-italic">
                                    Tidak ada tender di tahap ini
                                </div>
                            `;
                        } else {
                            st.items.forEach(t => {
                                let prioBadge = t.priority === 'High' 
                                    ? '<span class="badge badge-priority-high px-2 py-0.5 rounded-pill small">High</span>' 
                                    : (t.priority === 'Medium' ? '<span class="badge badge-priority-medium px-2 py-0.5 rounded-pill small">Medium</span>' : '<span class="badge badge-priority-low px-2 py-0.5 rounded-pill small">Low</span>');

                                let countdownBadge = '';
                                if (t.days_to_deadline < 0) {
                                    countdownBadge = `<span class="badge bg-danger text-white rounded-pill small"><i class="bi bi-exclamation-circle me-1"></i>Lewat ${Math.abs(t.days_to_deadline)} Hari</span>`;
                                } else if (t.days_to_deadline <= 3) {
                                    countdownBadge = `<span class="badge bg-warning text-dark rounded-pill small"><i class="bi bi-clock-history me-1"></i>${t.days_to_deadline} Hari Lagi</span>`;
                                } else {
                                    countdownBadge = `<span class="badge bg-secondary-subtle text-secondary rounded-pill small"><i class="bi bi-calendar3 me-1"></i>${t.deadline}</span>`;
                                }

                                let soBadge = t.sales_order_id 
                                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill small"><i class="bi bi-check-circle-fill me-1"></i>SPK Terbit</span>` 
                                    : '';

                                let entitasBadge = t.branch_id
                                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.65rem;"><i class="bi bi-shop me-0.5"></i>${escapeHtml(t.entitas_label)}</span>`
                                    : `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.65rem;"><i class="bi bi-building me-0.5"></i>PT (Pusat)</span>`;

                                html += `
                                    <div class="kanban-card btn-view-detail" data-id="${t.id}">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.7rem;">${t.nomor_kontrak}</span>
                                            <div class="d-flex align-items-center gap-1">
                                                ${entitasBadge}
                                                ${prioBadge}
                                                ${soBadge}
                                            </div>
                                        </div>
                                        <div class="kanban-card-title">${escapeHtml(t.nama_tender)}</div>
                                        <div class="text-muted small mb-2 text-truncate"><i class="bi bi-building me-1"></i>${escapeHtml(t.customer_name)}</div>
                                        
                                        <div class="kanban-card-value text-success mb-2">
                                            ${formatRp(t.nilai_estimasi)}
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                                            <div>${countdownBadge}</div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold"><i class="bi bi-person me-1"></i>${escapeHtml(t.sales_name)}</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        }

                        html += `
                                </div>
                            </div>
                        `;
                    });

                    $('#kanban_board_container').html(html);
                });
            }

            // Initial load Kanban
            loadKanbanBoard();

            // ===== DATATABLES INITIALIZATION =====
            let tableTender = $('#tableTender').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("tender.data") }}',
                    data: function (d) {
                        d.filter_status = $('#table_filter_status').val();
                        d.filter_priority = $('#table_filter_priority').val();
                    }
                },
                columns: [
                    { 
                        data: 'DT_RowIndex', 
                        name: 'DT_RowIndex', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center ps-3',
                        render: function (data) {
                            return `<span class="table-no-badge">${data}</span>`;
                        }
                    },
                    { 
                        data: 'nomor_kontrak', 
                        name: 'nomor_kontrak',
                        render: function (data, type, row) {
                            let text = (data && data !== '-') ? escapeHtml(data) : `TND-${row.id}`;
                            return `<span class="badge-kontrak-tender"><i class="bi bi-file-earmark-text text-primary"></i> ${text}</span>`;
                        }
                    },
                    { 
                        data: 'nama_tender', 
                        name: 'nama_tender',
                        render: function (data, type, row) {
                            let soBadge = row.has_sales_order ? `<span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-2 rounded-pill font-monospace" style="font-size:0.68rem;"><i class="bi bi-check2-all me-0.5"></i> SO Terbit</span>` : '';
                            return `
                                <div>
                                    <div class="fw-bold text-body btn-view-detail cursor-pointer d-flex align-items-center gap-1.5 flex-wrap" data-id="${row.id}" title="Klik untuk lihat detail">
                                        <span class="hover-primary transition">${escapeHtml(data)}</span>
                                        ${soBadge}
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size:0.75rem;">
                                        <i class="bi bi-building me-1 text-primary"></i> ${escapeHtml(row.customer_name)}
                                        <span class="mx-1.5 text-muted opacity-50">•</span>
                                        <i class="bi bi-person text-secondary me-0.5"></i> ${escapeHtml(row.sales_name)}
                                    </div>
                                </div>
                            `;
                        }
                    },
                    { 
                        data: 'entitas_badge', 
                        name: 'entitas_badge',
                        orderable: false,
                        render: function (data, type, row) {
                            return row.entitas_badge || '<span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill">PT (Pusat)</span>';
                        }
                    },
                    { 
                        data: 'kategori_instansi', 
                        name: 'kategori_instansi',
                        render: function (data) {
                            let kat = data || 'Pemerintah / B2G';
                            let katBadgeCls = 'badge-kategori-b2g';
                            let katIcon = 'bi-building-columns';
                            if (kat.includes('TNI')) {
                                katBadgeCls = 'badge-kategori-tni';
                                katIcon = 'bi-shield-check';
                            } else if (kat.includes('BUMN')) {
                                katBadgeCls = 'badge-kategori-bumn';
                                katIcon = 'bi-buildings';
                            } else if (kat.includes('Swasta')) {
                                katBadgeCls = 'badge-kategori-b2b';
                                katIcon = 'bi-briefcase';
                            }
                            return `<span class="badge ${katBadgeCls} px-2.5 py-1 rounded-pill small fw-bold d-inline-flex align-items-center gap-1"><i class="bi ${katIcon}"></i> ${escapeHtml(kat)}</span>`;
                        }
                    },
                    { 
                        data: 'nilai_estimasi', 
                        name: 'nilai_estimasi',
                        className: 'text-end',
                        render: function (data) {
                            let val = Number(data) || 0;
                            if (val > 0) {
                                return `<div class="font-monospace fw-bold text-success fs-6"><span style="font-size:0.72rem;" class="text-success-emphasis me-0.5">Rp</span>${val.toLocaleString('id-ID')}</div>`;
                            }
                            return `<span class="text-muted font-monospace small">-</span>`;
                        }
                    },
                    { 
                        data: 'deadline', 
                        name: 'deadline',
                        className: 'text-center',
                        render: function (data, type, row) {
                            let days = row.days_to_deadline;
                            if (days < 0) {
                                return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill small font-monospace"><i class="bi bi-exclamation-triangle-fill me-1"></i>${data} (Lewat)</span>`;
                            } else if (days <= 3) {
                                return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill small font-monospace"><i class="bi bi-clock-fill me-1"></i>${data} (${days}h)</span>`;
                            }
                            return `<div class="font-monospace text-body small"><i class="bi bi-calendar3 me-1 text-muted"></i>${data}</div>`;
                        }
                    },
                    { 
                        data: 'priority', 
                        name: 'priority',
                        className: 'text-center',
                        render: function (data) {
                            let prioMap = {
                                'High': '<span class="badge badge-priority-high px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-fire me-1"></i>High</span>',
                                'Medium': '<span class="badge badge-priority-medium px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-dash-circle me-1"></i>Medium</span>',
                                'Low': '<span class="badge badge-priority-low px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-arrow-down-circle me-1"></i>Low</span>'
                            };
                            return prioMap[data] || `<span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill small">${data}</span>`;
                        }
                    },
                    { 
                        data: 'status', 
                        name: 'status',
                        className: 'text-center',
                        render: function (data) {
                            let statusMap = {
                                'Draft': '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-pencil me-1"></i>Draft</span>',
                                'Pending Approval': '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Review</span>',
                                'Approved': '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-patch-check me-1"></i>Approved</span>',
                                'Rejected': '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-x-octagon me-1"></i>Rejected</span>',
                                'Sent': '<span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-send me-1"></i>Sent</span>',
                                'Negotiation': '<span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-chat-dots me-1"></i>Aanwijzing</span>',
                                'Won': '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-trophy-fill me-1"></i>Won (SPK)</span>',
                                'Lost': '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill small fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Lost</span>',
                                'Cancelled': '<span class="badge bg-body-secondary text-body border px-2.5 py-1 rounded-pill small fw-bold">Batal</span>'
                            };
                            return statusMap[data] || `<span class="badge bg-secondary px-2.5 py-1 rounded-pill small">${data}</span>`;
                        }
                    },
                    { 
                        data: 'id', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center pe-3',
                        render: function (data, type, row) {
                            return `
                                <div class="d-flex justify-content-center align-items-center gap-1.5">
                                    <button type="button" class="btn-table-action btn-action-view btn-view-detail" data-id="${data}" title="Lihat Detail Tender">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn-table-action btn-action-edit btn-edit-tender" data-id="${data}" title="Ubah Proposal Tender">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            $('#table_filter_status, #table_filter_priority').on('change', function () {
                tableTender.ajax.reload();
            });

            // ===== CUSTOMER LOOKUP MODAL (CINEMATIC CARD-BASED) =====
            $(document).on('click', '.btn-lookup-customer', function () {
                let prefix = $(this).data('prefix');
                $('#modalCariCustomer').data('target-prefix', prefix);
                $('#search_customer_input').val('');
                $('#btn_clear_search_customer').addClass('d-none');
                $('#customer_lookup_container .customer-lookup-card').show();
                $('#customer_lookup_empty').addClass('d-none');
                $('#modalCariCustomer').modal('show');
                setTimeout(() => $('#search_customer_input').focus(), 300);
            });

            $('#search_customer_input').on('input', function () {
                let val = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                if (val.length > 0) {
                    $('#btn_clear_search_customer').removeClass('d-none');
                } else {
                    $('#btn_clear_search_customer').addClass('d-none');
                }

                $('#customer_lookup_container .customer-lookup-card').each(function () {
                    let searchStr = $(this).data('search') || $(this).text().toLowerCase();
                    let matches = searchStr.indexOf(val) > -1;
                    $(this).toggle(matches);
                    if (matches) visibleCount++;
                });

                if (visibleCount === 0) {
                    $('#customer_lookup_empty').removeClass('d-none');
                } else {
                    $('#customer_lookup_empty').addClass('d-none');
                }
            });

            $('#btn_clear_search_customer').on('click', function () {
                $('#search_customer_input').val('').trigger('input').focus();
            });

            $(document).on('click', '.customer-lookup-row, .btn-select-customer', function (e) {
                e.stopPropagation();
                let card = $(this).closest('.customer-lookup-card');
                let id = card.data('id');
                let name = card.data('name');
                let prefix = $('#modalCariCustomer').data('target-prefix') || 'tambah';

                $(`#${prefix}_customer_id`).val(id);
                $(`#${prefix}_customer_name`).val(name);

                $('#modalCariCustomer').modal('hide');
            });

            // ===== PRODUCT LOOKUP MODAL FOR TENDER ITEMS =====
            $(document).on('click', '#btn_tambah_master_item_row', function () {
                $('#modalCariBarangTender').data('target-action', 'append-tambah');
                $('#search_barang_tender_input').val('');
                $('.btn-kategori-tender-chip').removeClass('active');
                $('.btn-kategori-tender-chip[data-kategori-id="all"]').addClass('active');
                $('#table_lookup_barang_tender tbody tr').show();
                $('#modalCariBarangTender').modal('show');
            });

            $(document).on('click', '#btn_edit_master_item_row', function () {
                $('#modalCariBarangTender').data('target-action', 'append-edit');
                $('#search_barang_tender_input').val('');
                $('.btn-kategori-tender-chip').removeClass('active');
                $('.btn-kategori-tender-chip[data-kategori-id="all"]').addClass('active');
                $('#table_lookup_barang_tender tbody tr').show();
                $('#modalCariBarangTender').modal('show');
            });

            $(document).on('click', '.btn-lookup-barang', function () {
                let prefix = $(this).data('prefix');
                let rowIdx = $(this).data('row');
                $('#modalCariBarangTender').data('target-action', 'replace-row').data('target-prefix', prefix).data('target-row', rowIdx);
                $('#search_barang_tender_input').val('');
                $('.btn-kategori-tender-chip').removeClass('active');
                $('.btn-kategori-tender-chip[data-kategori-id="all"]').addClass('active');
                $('#table_lookup_barang_tender tbody tr').show();
                $('#modalCariBarangTender').modal('show');
            });

            $(document).on('click', '.btn-kategori-tender-chip', function () {
                $('.btn-kategori-tender-chip').removeClass('active');
                $(this).addClass('active');
                filterBarangLookupTable();
            });

            $('#search_barang_tender_input').on('keyup', function () {
                filterBarangLookupTable();
            });

            function filterBarangLookupTable() {
                let text = $('#search_barang_tender_input').val().toLowerCase();
                let activeKatId = $('.btn-kategori-tender-chip.active').data('kategori-id');

                $('#table_lookup_barang_tender tbody tr').each(function () {
                    let row = $(this);
                    let rowKatId = row.data('kategori-id');
                    let rowText = row.text().toLowerCase();

                    let matchText = (rowText.indexOf(text) > -1);
                    let matchKat = (activeKatId === 'all' || activeKatId == rowKatId);

                    row.toggle(matchText && matchKat);
                });
            }

            $(document).on('click', '.barang-lookup-row, .btn-select-barang-tender', function (e) {
                e.stopPropagation();
                let bRow = $(this).closest('tr');
                let id = bRow.data('id');
                let name = bRow.data('name');
                let code = bRow.data('code');
                let satuan = bRow.data('satuan') || 'Pcs';

                let isBundle = (bRow.data('is-bundle') == '1');
                let bundleComps = bRow.data('bundle-components');
                if (typeof bundleComps === 'string') {
                    try { bundleComps = JSON.parse(bundleComps); } catch(err) { bundleComps = []; }
                }

                let prefix = $('#modalCariBarangTender').data('target-prefix') || 'tambah';
                let rowIdx = $('#modalCariBarangTender').data('target-row');

                let photoUrl = bRow.data('photo-url') || bRow.find('img').attr('src') || '/uploads/barang/placeholder_garment.png';
                let photosList = bRow.data('photos');
                if (typeof photosList === 'string') {
                    try { photosList = JSON.parse(photosList); } catch(err) { photosList = [photoUrl]; }
                }
                if (!Array.isArray(photosList) || photosList.length === 0) {
                    photosList = [photoUrl];
                }

                let targetItemRow = $(`#tbody_${prefix}_items tr[data-index="${rowIdx}"]`);
                if (targetItemRow.length === 0) {
                    targetItemRow = $(`#tbody_${prefix}_items tr#tnd_item_row_${prefix}_${rowIdx}`);
                }
                if (targetItemRow.length === 0 && rowIdx !== undefined && rowIdx !== null && rowIdx !== '') {
                    targetItemRow = $(`#tbody_${prefix}_items tr`).filter(function() {
                        return $(this).data('index') == rowIdx;
                    });
                }
                if (targetItemRow.length === 0) {
                    targetItemRow = $(`#tbody_${prefix}_items tr.item-row:last`);
                }

                if (targetItemRow.length > 0) {
                    targetItemRow.find('.item-barang-id, .select-product-input, .item-barang-id-input').val(id);
                    targetItemRow.find('.item-barang-name, .label-product-name, .item-barang-name-input').val(`[${code}] ${name}`);
                    targetItemRow.find('.item-satuan-input').val(satuan);
                    targetItemRow.find('.item-satuan-badge, .label-satuan-name').text(satuan);

                    targetItemRow.data('photos', photosList);
                    targetItemRow.data('photo-url', photoUrl);

                    let imgHtml = `
                        <img src="${photoUrl}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                        ${photosList.length > 1 ? `
                            <span class="position-absolute top-0 end-0 badge bg-dark bg-opacity-85 text-white font-monospace" style="font-size:0.52rem; padding: 1px 3px; transform: translate(15%, -15%);">
                                ${photosList.length}
                            </span>
                        ` : ''}
                    `;
                    targetItemRow.find('.tender-product-img-frame')
                        .removeClass('bg-body-tertiary text-muted d-flex align-items-center justify-content-center')
                        .addClass('position-relative cursor-pointer border rounded-2 overflow-hidden shadow-xs hover-scale')
                        .attr('onclick', 'window.showProductPhotoPreviewFromRow(this, event)')
                        .attr('title', photosList.length > 1 ? `Klik untuk melihat & geser ${photosList.length} foto` : 'Klik untuk memperbesar foto')
                        .html(imgHtml);
                } else {
                    // Fallback to append if no target row found
                    if (prefix === 'tambah' && typeof window.addTenderItemRowTambah === 'function') {
                        window.addTenderItemRowTambah({
                            barang_id: id,
                            kode: code,
                            nama: name,
                            satuan: satuan,
                            photo_url: photoUrl,
                            photos: photosList,
                            is_custom: false
                        });
                    } else if (prefix === 'edit' && typeof window.addTenderItemRowEdit === 'function') {
                        window.addTenderItemRowEdit({
                            barang_id: id,
                            kode: code,
                            nama: name,
                            satuan: satuan,
                            photo_url: photoUrl,
                            photos: photosList,
                            is_custom: false
                        });
                    }
                }

                // Hide modal safely
                $('#modalCariBarangTender').modal('hide');
                let mEl = document.getElementById('modalCariBarangTender');
                if (mEl && window.bootstrap) {
                    let inst = bootstrap.Modal.getInstance(mEl);
                    if (inst) inst.hide();
                }
            });

            // Preserve scrolling when closing nested child modals
            $('#modalCariBarangTender, #modalCariCustomer, #modalDetailKomponenBundle, #modalPreviewFotoBarang').on('hidden.bs.modal', function () {
                if ($('#modalTambahTender').hasClass('show') || $('#modalEditTender').hasClass('show') || $('#modalDetailTender').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // Refresh on modal hidden
            $('#modalTambahTender, #modalEditTender').on('hidden.bs.modal', function () {
                loadKanbanBoard();
                if ($.fn.DataTable.isDataTable('#tableTender')) {
                    tableTender.ajax.reload(null, false);
                }
            });
        });
    </script>
    @endpush

</x-layouts>
