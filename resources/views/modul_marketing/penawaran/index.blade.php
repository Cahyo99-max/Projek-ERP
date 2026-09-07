<x-layouts title="Penawaran Harga (Sales Quotation)">

    @push('styles')
    <style>
        /* KPI Cards */
        .sq-kpi-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 18px;
            padding: 1.25rem 1.35rem;
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 16px rgba(0,0,0,0.025);
        }
        [data-bs-theme="dark"] .sq-kpi-card {
            background: #182234 !important;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        .sq-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
        }
        .sq-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        /* Soft Badges */
        .badge-soft-emerald { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-soft-amber   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-soft-rose    { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
        .badge-soft-sky     { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-soft-indigo  { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-soft-neutral { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }

        [data-bs-theme="dark"] .badge-soft-emerald { background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        [data-bs-theme="dark"] .badge-soft-amber   { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        [data-bs-theme="dark"] .badge-soft-rose    { background: rgba(244, 63, 94, 0.2); color: #fda4af; border-color: rgba(244, 63, 94, 0.3); }
        [data-bs-theme="dark"] .badge-soft-sky     { background: rgba(14, 165, 233, 0.2); color: #7dd3fc; border-color: rgba(14, 165, 233, 0.3); }
        [data-bs-theme="dark"] .badge-soft-indigo  { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border-color: rgba(99, 102, 241, 0.3); }
        [data-bs-theme="dark"] .badge-soft-neutral { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }

        @media (min-width: 1200px) {
            .col-xl-2-4 {
                flex: 0 0 auto;
                width: 20%;
            }
        }

        /* Main DataTables Card */
        .sq-main-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.07);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 18px rgba(0,0,0,0.025);
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .sq-main-card {
            background: #182234 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }

        /* Table Styling */
        #quotationTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }
        #quotationTable thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0 !important;
            border-top: none;
            padding: 0.85rem 0.75rem;
        }
        [data-bs-theme="dark"] #quotationTable thead th {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-bottom-color: rgba(255, 255, 255, 0.12) !important;
        }
        #quotationTable tbody td {
            background-color: #ffffff;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            transition: background-color 0.15s ease;
        }
        [data-bs-theme="dark"] #quotationTable tbody td {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        }
        #quotationTable tbody tr:hover td {
            background-color: #f8fafc !important;
        }
        [data-bs-theme="dark"] #quotationTable tbody tr:hover td {
            background-color: #1e293b !important;
        }

        /* DataTables Controls (Search, Length, Pagination) */
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_info {
            font-size: 0.82rem;
            color: #64748b;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length label,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter label,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info {
            color: #94a3b8 !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.82rem;
            outline: none;
            background-color: #ffffff;
            color: #0f172a;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter input {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: #f8fafc !important;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.35rem 0.6rem;
            font-size: 0.82rem;
            background-color: #ffffff;
            color: #0f172a;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length select {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: #f8fafc !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.82rem !important;
            border: 1px solid transparent !important;
            color: #475569 !important;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #cbd5e1 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4f46e5 !important;
            color: #ffffff !important;
            border-color: #4f46e5 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(99, 102, 241, 0.15) !important;
            color: #4f46e5 !important;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(99, 102, 241, 0.25) !important;
            color: #a5b4fc !important;
        }

        /* Timeline in Detail Modal */
        .revision-timeline-item {
            position: relative;
            padding-left: 24px;
        }
        .revision-timeline-item::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 24px;
            bottom: -8px;
            width: 2px;
            background: #e2e8f0;
        }
        [data-bs-theme="dark"] .revision-timeline-item::before {
            background: rgba(255, 255, 255, 0.1);
        }
        .revision-timeline-item:last-child::before {
            display: none;
        }
        .revision-timeline-dot {
            position: absolute;
            left: 0;
            top: 6px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6366f1;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }
        [data-bs-theme="dark"] .revision-timeline-dot {
            border-color: #1e293b;
        }
        .revision-timeline-dot.is-old {
            background: #94a3b8;
            box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.2);
        }

        /* Modal Dialog Styling */
        [data-bs-theme="dark"] .modal-content {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5) !important;
        }
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .sq-detail-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.15rem;
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .sq-detail-box {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.09) !important;
            color: #f8fafc !important;
        }

        /* Detail Table in Modal */
        .sq-detail-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-color: #e2e8f0;
            padding: 0.7rem 0.6rem;
        }
        [data-bs-theme="dark"] .sq-detail-table th {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        .sq-detail-table td {
            background-color: #ffffff;
            color: #1e293b;
            border-color: #e2e8f0;
            padding: 0.65rem 0.6rem;
        }
        [data-bs-theme="dark"] .sq-detail-table td {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .sq-detail-table tfoot td {
            background-color: #f8fafc;
            color: #1e293b;
            border-color: #e2e8f0;
        }
        [data-bs-theme="dark"] .sq-detail-table tfoot td {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        .sq-detail-table tfoot tr.table-active td {
            background-color: #eef2ff !important;
            color: #3730a3 !important;
        }
        [data-bs-theme="dark"] .sq-detail-table tfoot tr.table-active td {
            background-color: rgba(99, 102, 241, 0.18) !important;
            color: #c7d2fe !important;
        }
    </style>
    @endpush

    <div class="container-fluid px-3 px-md-4 py-4">

        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 fs-8">
                        <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Penjualan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Penawaran Harga</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0 text-body">Penawaran Harga (Sales Quotation)</h3>
                    <span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill fw-semibold">SALES & CRM</span>
                </div>
                <p class="text-muted fs-8 mb-0 mt-0.5">Kelola dokumen penawaran harga resmi, multi-revisi negosiasi, persetujuan, dan konversi ke Sales Order (SO).</p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Filter Trigger Button (Opens Modal) -->
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 shadow-xs d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalFilterQuotation" id="btnFilterModalTrigger">
                    <i class="bi bi-funnel text-primary"></i>
                    <span class="fw-semibold">Filter</span>
                    <span id="filterCountBadge" class="badge bg-primary rounded-pill fs-9 px-1.5 py-0.5" style="display:none;">0</span>
                </button>

                @can('tambah-penawaran')
                <a href="{{ route('penawaran.create') }}" class="btn btn-primary rounded-pill px-3.5 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span class="fw-semibold">Buat Penawaran Baru</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Active Filter Indicator Pill (Shown when filter is applied) -->
        <div id="activeFilterContainer" class="d-flex flex-wrap align-items-center gap-2 mb-3" style="display:none !important;">
            <span class="fs-9 text-muted fw-semibold"><i class="bi bi-funnel-fill text-primary me-1"></i> Filter Aktif:</span>
            <div id="activeFilterTags" class="d-flex flex-wrap gap-1.5"></div>
            <button type="button" class="btn btn-link text-danger p-0 fs-9 text-decoration-none fw-semibold ms-1" id="btnClearAllFilters">
                <i class="bi bi-x-circle me-0.5"></i> Reset Semua
            </button>
        </div>

        <!-- KPI Metric Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Penawaran -->
            <div class="col-12 col-sm-6 col-xl-2-4">
                <div class="sq-kpi-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-8 text-muted fw-semibold">Total Penawaran</div>
                            <h3 class="fw-bold mb-0 text-body mt-1">{{ number_format($kpis['total_penawaran'] ?? 0) }}</h3>
                        </div>
                        <div class="sq-kpi-icon badge-soft-indigo">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                    </div>
                    <div class="fs-9 text-muted mt-2">Dokumen penawaran aktif</div>
                </div>
            </div>

            <!-- Draft & Terkirim -->
            <div class="col-12 col-sm-6 col-xl-2-4">
                <div class="sq-kpi-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-8 text-muted fw-semibold">Draft & Terkirim</div>
                            <h3 class="fw-bold mb-0 text-info mt-1">{{ number_format($kpis['menunggu'] ?? 0) }}</h3>
                        </div>
                        <div class="sq-kpi-icon badge-soft-sky">
                            <i class="bi bi-send-fill"></i>
                        </div>
                    </div>
                    <div class="fs-9 text-muted mt-2">Menunggu respon / negosiasi</div>
                </div>
            </div>

            <!-- Disetujui -->
            <div class="col-12 col-sm-6 col-xl-2-4">
                <div class="sq-kpi-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-8 text-muted fw-semibold">Disetujui (Approved)</div>
                            <h3 class="fw-bold mb-0 text-success mt-1">{{ number_format($kpis['approved'] ?? 0) }}</h3>
                        </div>
                        <div class="sq-kpi-icon badge-soft-emerald">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                    <div class="fs-9 text-muted mt-2">Siap terbit Sales Order</div>
                </div>
            </div>

            <!-- Terkonversi SO -->
            <div class="col-12 col-sm-6 col-xl-2-4">
                <div class="sq-kpi-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-8 text-muted fw-semibold">Jadi Sales Order</div>
                            <h3 class="fw-bold mb-0 text-primary mt-1">{{ number_format($kpis['so'] ?? 0) }}</h3>
                        </div>
                        <div class="sq-kpi-icon badge-soft-indigo">
                            <i class="bi bi-cart-check-fill"></i>
                        </div>
                    </div>
                    <div class="fs-9 text-muted mt-2">Sudah masuk tahap SO</div>
                </div>
            </div>

            <!-- Nilai Pipeline -->
            <div class="col-12 col-sm-6 col-xl-2-4">
                <div class="sq-kpi-card h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-8 text-muted fw-semibold">Nilai Pipeline</div>
                            <h4 class="fw-bold mb-0 text-body mt-1">Rp {{ number_format(($kpis['total_nilai'] ?? 0) / 1000000, 1, ',', '.') }}<span class="fs-8 text-muted fw-normal ms-1">Jt</span></h4>
                        </div>
                        <div class="sq-kpi-icon badge-soft-amber">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="fs-9 text-muted mt-2">Total estimasi potensi nilai</div>
                </div>
            </div>
        </div>

        <!-- DataTables Main Card -->
        <div class="sq-main-card">
            <div class="table-responsive">
                <table id="quotationTable" class="table align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>No. Penawaran</th>
                            <th>Customer & Proyek</th>
                            <th>Tgl Penawaran</th>
                            <th class="text-end">Total Nilai</th>
                            <th>HPP & Margin</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Filter Penawaran -->
    <div class="modal fade" id="modalFilterQuotation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="badge-soft-indigo p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-funnel-fill text-primary"></i>
                        </div>
                        <h5 class="modal-title fw-bold mb-0 text-body">Filter Dokumen Penawaran</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1">Filter Customer / Klien</label>
                            <select id="filterCustomer" class="form-select form-select-sm select2-modal">
                                <option value="">-- Semua Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_perusahaan ?: $c->nama_customer }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1">Filter Status Penawaran</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">-- Semua Status Aktif --</option>
                                <option value="Draft">Draft</option>
                                <option value="Sent">Terkirim (Sent)</option>
                                <option value="Approved">Disetujui (Approved)</option>
                                <option value="Converted_to_SO">Terkonversi ke SO</option>
                                <option value="Rejected">Ditolak (Rejected)</option>
                                <option value="Revised">Direvisi (Arsip Revisi Lama)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1">Filter Proyek / Tender Terkait</label>
                            <select id="filterTender" class="form-select form-select-sm select2-modal">
                                <option value="">-- Semua Proyek / Non-Tender --</option>
                                @foreach($tenders as $t)
                                    <option value="{{ $t->id }}">{{ $t->nomor_kontrak }} - {{ $t->nama_tender }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 pt-2 border-top">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" role="switch" id="checkIncludeRevisions">
                                <label class="form-check-label text-muted" for="checkIncludeRevisions">
                                    Tampilkan riwayat revisi lama yang sudah digantikan (Status Direvisi)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3.5 rounded-pill" id="btnResetFilterModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-4 rounded-pill fw-semibold shadow-xs" id="btnApplyFilterModal" data-bs-dismiss="modal">
                        <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penawaran -->
    <div class="modal fade" id="modalDetailQuotation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="badge-soft-indigo p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-bold mb-0 text-body" id="detailNomor">SQ/2026/...</h5>
                                <div id="detailStatusBadge"></div>
                            </div>
                            <span class="fs-8 text-muted" id="detailPerihal">Perihal Penawaran</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="detailBodyContent">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data detail penawaran...
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div id="detailActionLeft" class="d-flex flex-wrap gap-2"></div>
                    <div class="d-flex gap-2" id="detailActionRight">
                        <button type="button" class="btn btn-sm btn-secondary px-3.5 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Helper to format date cleanly
        function formatIndoDate(dateStr) {
            if (!dateStr) return '-';
            let cleanStr = dateStr.replace('T', ' ').split('.')[0];
            let parts = cleanStr.split(' ')[0].split('-');
            if (parts.length === 3) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                let year = parts[0];
                let month = parseInt(parts[1], 10) - 1;
                let day = parseInt(parts[2], 10);
                if (month >= 0 && month < 12) {
                    return `${day} ${months[month]} ${year}`;
                }
            }
            return dateStr;
        }

        $(document).ready(function() {
            // Select2 inside filter modal
            if ($.fn.select2) {
                $('.select2-modal').select2({
                    dropdownParent: $('#modalFilterQuotation'),
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            // Initialize DataTables
            const table = $('#quotationTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: "<'row mb-3 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row mt-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                ajax: {
                    url: "{{ route('penawaran.data') }}",
                    data: function(d) {
                        d.customer_id = $('#filterCustomer').val();
                        d.status = $('#filterStatus').val();
                        d.tender_id = $('#filterTender').val();
                        d.include_revisions = $('#checkIncludeRevisions').is(':checked') ? 1 : 0;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables AJAX error:', xhr);
                    }
                },
                columns: [
                    { data: 'nomor_formatted', name: 'nomor_formatted' },
                    { data: 'customer_info', name: 'customer_info' },
                    { data: 'tanggal_formatted', name: 'tanggal_formatted' },
                    { data: 'grand_total_formatted', name: 'grand_total_formatted', className: 'text-end fw-bold text-body font-monospace' },
                    { data: 'hpp_margin', name: 'hpp_margin' },
                    { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[2, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Cari nomor SQ, customer...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ penawaran",
                    infoEmpty: "Tidak ada data penawaran",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data penawaran yang sesuai",
                    emptyTable: "Belum ada dokumen penawaran harga",
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat data...',
                    paginate: {
                        first: '<i class="bi bi-chevron-double-left"></i>',
                        last: '<i class="bi bi-chevron-double-right"></i>',
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // Update Active Filter Pills & Badges
            function updateFilterBadges() {
                let activeCount = 0;
                let tagsHtml = '';

                let custVal = $('#filterCustomer').val();
                let custText = $('#filterCustomer option:selected').text();
                if (custVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-indigo fs-9 px-2 py-1 rounded-pill">Customer: ${custText}</span>`;
                }

                let statVal = $('#filterStatus').val();
                if (statVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-sky fs-9 px-2 py-1 rounded-pill">Status: ${statVal}</span>`;
                }

                let tenderVal = $('#filterTender').val();
                let tenderText = $('#filterTender option:selected').text();
                if (tenderVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-amber fs-9 px-2 py-1 rounded-pill">Proyek: ${tenderText}</span>`;
                }

                let incRev = $('#checkIncludeRevisions').is(':checked');
                if (incRev) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-neutral fs-9 px-2 py-1 rounded-pill">Termasuk Revisi Lama</span>`;
                }

                if (activeCount > 0) {
                    $('#filterCountBadge').text(activeCount).show();
                    $('#activeFilterTags').html(tagsHtml);
                    $('#activeFilterContainer').attr('style', 'display: flex !important;');
                } else {
                    $('#filterCountBadge').hide();
                    $('#activeFilterTags').empty();
                    $('#activeFilterContainer').attr('style', 'display: none !important;');
                }
            }

            // Apply Filter from Modal
            $('#btnApplyFilterModal').on('click', function() {
                updateFilterBadges();
                table.ajax.reload();
            });

            // Reset Filter Function
            function resetAllFilters() {
                $('#filterCustomer').val('').trigger('change');
                $('#filterStatus').val('');
                $('#filterTender').val('').trigger('change');
                $('#checkIncludeRevisions').prop('checked', false);
                updateFilterBadges();
                table.ajax.reload();
            }

            $('#btnResetFilterModal').on('click', function() {
                resetAllFilters();
                $('#modalFilterQuotation').modal('hide');
            });

            $('#btnClearAllFilters').on('click', function() {
                resetAllFilters();
            });

            // View Detail Function
            window.loadQuotationDetail = function(id) {
                $('#modalDetailQuotation').modal('show');
                $('#detailBodyContent').html(`
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data detail penawaran...
                    </div>
                `);

                $.ajax({
                    url: `/penawaran/${id}`,
                    method: 'GET',
                    success: function(resp) {
                        const res = resp;
                        const data = resp.data || resp;
                        const allRevisions = resp.all_revisions || [];
                        const canSeeHpp = resp.can_see_hpp || false;
                        const canApprove = (resp.can_approve || resp.can_approve == 1 || resp.can_approve === true);
                        const canConvertSo = (resp.can_convert_so || resp.can_convert_so == 1 || resp.can_convert_so === true);
                        const canPrint = (resp.can_print || resp.can_print == 1 || resp.can_print === true);

                        // Header info
                        let revBadge = data.revisi > 0 
                            ? `<span class="badge badge-soft-indigo fs-9 ms-1 rounded-pill"><i class="bi bi-clock-history me-0.5"></i>Rev.${data.revisi}</span>`
                            : `<span class="badge badge-soft-neutral fs-9 ms-1 rounded-pill text-muted">Rev.0</span>`;

                        $('#detailNomor').html(`<span class="font-monospace">${data.nomor_quotation}</span> ${revBadge}`);
                        $('#detailPerihal').text(data.perihal || 'Penawaran Harga');

                        // Status Badge in Header
                        let statusBadges = {
                            'Draft': '<span class="badge badge-soft-neutral rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-pencil-square me-1"></i>Draft</span>',
                            'Sent': '<span class="badge badge-soft-sky rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-send me-1"></i>Terkirim</span>',
                            'Revised': '<span class="badge badge-soft-amber rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-arrow-repeat me-1"></i>Direvisi</span>',
                            'Approved': '<span class="badge badge-soft-emerald rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>',
                            'Rejected': '<span class="badge badge-soft-rose rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>',
                            'Converted_to_SO': '<span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-cart-check-fill me-1"></i>Jadi SO</span>'
                        };
                        $('#detailStatusBadge').html(statusBadges[data.status] || `<span class="badge bg-secondary">${data.status}</span>`);

                        // Revision Timeline Card (if multiple revisions exist)
                        let revisionTimelineHtml = '';
                        if (allRevisions.length > 1) {
                            let revListHtml = '';
                            allRevisions.forEach((rev) => {
                                let isCurrent = (rev.id === data.id);
                                let dotClass = isCurrent ? '' : 'is-old';
                                let btnAction = isCurrent 
                                    ? `<span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill fw-semibold">Versi Aktif Saat Ini</span>`
                                    : `<button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2 fs-9" onclick="loadQuotationDetail(${rev.id})">Buka Versi Ini <i class="bi bi-arrow-right ms-0.5"></i></button>`;

                                revListHtml += `
                                    <div class="revision-timeline-item pb-3">
                                        <div class="revision-timeline-dot ${dotClass}"></div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div>
                                                <span class="fw-bold fs-8 ${isCurrent ? 'text-primary' : 'text-body'}">Revisi ${rev.revisi}</span>
                                                <span class="text-muted fs-9 ms-1">(${formatIndoDate(rev.tanggal_quotation)})</span>
                                                <span class="ms-1">${statusBadges[rev.status] || rev.status}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold font-monospace fs-8">Rp ${parseFloat(rev.grand_total).toLocaleString('id-ID')}</span>
                                                ${btnAction}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            revisionTimelineHtml = `
                                <div class="sq-detail-box mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                        <span class="fs-8 fw-bold text-uppercase text-secondary"><i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Rantai Revisi (${allRevisions.length} Versi)</span>
                                    </div>
                                    <div class="pt-1">
                                        ${revListHtml}
                                    </div>
                                </div>
                            `;
                        }

                        // Items breakdown
                        let itemsHtml = '';
                        if (data.items && data.items.length > 0) {
                            data.items.forEach((item, idx) => {
                                let namaItem = item.nama_barang_custom || (item.barang ? item.barang.nama_barang : '-');
                                let kodeItem = item.barang ? item.barang.kode_barang : 'CUSTOM';
                                let satuan = item.nama_satuan || (item.barang && item.barang.satuan ? item.barang.satuan.nama_satuan : 'Pcs');

                                itemsHtml += `
                                    <tr>
                                        <td class="text-center text-muted fs-8">${idx + 1}</td>
                                        <td>
                                            <div class="fw-bold text-body fs-8">${namaItem}</div>
                                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                                <code class="badge badge-soft-neutral fs-9 px-1.5 py-0.5">${kodeItem}</code>
                                                ${item.kategori ? `<span class="badge badge-soft-sky fs-9 px-1.5 py-0.5">${item.kategori.nama_kategori}</span>` : ''}
                                            </div>
                                            ${item.deskripsi_item ? `<div class="fs-9 text-muted mt-1">${item.deskripsi_item}</div>` : ''}
                                        </td>
                                        <td class="text-center fs-8 fw-semibold">${parseFloat(item.qty).toLocaleString('id-ID')} <span class="text-muted fw-normal fs-9">${satuan}</span></td>
                                        <td class="text-end fs-8 font-monospace">Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}</td>
                                        <td class="text-end fs-8">${parseFloat(item.diskon_persen) > 0 ? `<span class="text-danger fw-semibold">${parseFloat(item.diskon_persen)}%</span>` : (parseFloat(item.diskon_nominal) > 0 ? `<span class="text-danger">Rp ${parseFloat(item.diskon_nominal).toLocaleString('id-ID')}</span>` : '<span class="text-muted">-</span>')}</td>
                                        <td class="text-end fw-bold text-body fs-8 font-monospace">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</td>
                                    </tr>
                                `;
                            });
                        }

                        // Customer info
                        let custAddress = '';
                        if (data.customer) {
                            if (data.customer.default_address) {
                                custAddress = data.customer.default_address.alamat || '';
                                if (data.customer.default_address.kota) {
                                    custAddress += (custAddress ? ', ' : '') + data.customer.default_address.kota;
                                }
                            }
                            if (!custAddress) {
                                custAddress = data.customer.telepon || data.customer.email || '-';
                            }
                        }

                        // Payment Term display
                        let termText = '-';
                        if (data.payment_term) {
                            termText = `${data.payment_term.nama_term || data.payment_term.nama_termin} (${data.payment_term.jumlah_hari || 0} Hari)`;
                        } else if (data.catatan_pembayaran) {
                            termText = data.catatan_pembayaran;
                        }

                        // Status & Approval Workflow Banner
                        let approvalBannerHtml = '';

                        if (data.status === 'Draft') {
                            approvalBannerHtml = `
                                <div class="alert alert-secondary d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 mb-3 rounded-3 border">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="badge-soft-neutral p-2 rounded-circle">
                                            <i class="bi bi-pencil fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-body">Status: Draft Internal</div>
                                            <div class="fs-8 text-muted">Dokumen baru tersimpan sebagai draf dan belum diajukan untuk persetujuan (approval).</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        ${canApprove ? `
                                            <button type="button" class="btn btn-sm btn-success px-3 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Approved">
                                                <i class="bi bi-check-circle me-1"></i> Setujui Langsung
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Sent') {
                            approvalBannerHtml = `
                                <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-3 rounded-3 border border-primary border-opacity-25" style="background: rgba(14, 165, 233, 0.08);">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="badge-soft-sky p-2 rounded-circle">
                                            <i class="bi bi-clock-history fs-5 text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-primary d-flex align-items-center gap-2">
                                                Menunggu Persetujuan (Pending Approval)
                                                <span class="badge bg-primary text-white fs-9 rounded-pill">Need Action</span>
                                            </div>
                                            <div class="fs-8 text-muted">Penawaran telah diajukan dan sedang menunggu persetujuan dari Manajer / Direktur.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${canApprove ? `
                                            <button type="button" class="btn btn-sm btn-success px-3.5 py-1.5 rounded-pill btn-approve-sq shadow-sm" data-id="${data.id}" data-status="Approved">
                                                <i class="bi bi-check-circle-fill me-1"></i> Setujui (Approve)
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Rejected">
                                                <i class="bi bi-x-circle me-1"></i> Tolak
                                            </button>
                                        ` : `
                                            <span class="badge badge-soft-neutral fs-8 py-2 px-3 rounded-pill text-muted">
                                                <i class="bi bi-lock me-1"></i> Hanya Approver yang dapat menyetujui
                                            </span>
                                        `}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Approved') {
                            let approverName = data.approver ? (data.approver.nama_lengkap || data.approver.name) : 'Manajer/Direktur';
                            let approvedTime = data.approved_at ? formatIndoDate(data.approved_at) : '';
                            approvalBannerHtml = `
                                <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-3 rounded-3 border border-success border-opacity-25" style="background: rgba(16, 185, 129, 0.08);">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="badge-soft-emerald p-2 rounded-circle">
                                            <i class="bi bi-check2-circle fs-4 text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-success">Penawaran Telah Disetujui (Approved)</div>
                                            <div class="fs-8 text-muted">Disetujui oleh <strong class="text-body">${approverName}</strong> ${approvedTime ? `pada ${approvedTime}` : ''}. Dokumen resmi siap dicetak atau dilanjutkan ke Sales Order.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${!data.sales_order_id && canConvertSo ? `
                                            <button type="button" class="btn btn-sm btn-success px-3.5 py-1.5 rounded-pill btn-convert-so shadow-sm" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                                <i class="bi bi-cart-check-fill me-1"></i> Konversi ke Sales Order (SO)
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Rejected') {
                            let rejectorName = data.approver ? (data.approver.nama_lengkap || data.approver.name) : 'Manajer';
                            approvalBannerHtml = `
                                <div class="alert alert-danger d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-3 rounded-3 border border-danger border-opacity-25" style="background: rgba(239, 68, 68, 0.08);">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="badge-soft-rose p-2 rounded-circle">
                                            <i class="bi bi-x-circle-fill fs-4 text-danger"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-danger">Penawaran Ditolak (Rejected)</div>
                                            <div class="fs-8 text-muted">Ditolak oleh <strong class="text-body">${rejectorName}</strong>. Silakan klik tombol <strong>Buat Revisi</strong> untuk memperbaiki rincian harga.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-warning px-3.5 py-1.5 rounded-pill btn-revisi" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                            <i class="bi bi-arrow-repeat me-1"></i> Buat Revisi Baru
                                        </button>
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Converted_to_SO') {
                            let soNumber = data.sales_order ? data.sales_order.nomor_so : 'SO Terbit';
                            approvalBannerHtml = `
                                <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-3 rounded-3 border border-info border-opacity-25" style="background: rgba(99, 102, 241, 0.08);">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="badge-soft-indigo p-2 rounded-circle">
                                            <i class="bi bi-bag-check-fill fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-primary">Sudah Dikonversi ke Sales Order (SO)</div>
                                            <div class="fs-8 text-muted">Transaksi resmi terbit dengan nomor <strong class="text-body font-monospace">${soNumber}</strong>.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('marketing.sales-order.index') }}" class="btn btn-sm btn-outline-primary px-3 py-1.5 rounded-pill">
                                            <i class="bi bi-arrow-right-circle me-1"></i> Buka Menu Sales Order
                                        </a>
                                    </div>
                                </div>
                            `;
                        }

                        let html = `
                            ${approvalBannerHtml}
                            ${revisionTimelineHtml}

                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <div class="sq-detail-box h-100">
                                        <h6 class="fs-8 fw-bold text-uppercase text-secondary mb-2"><i class="bi bi-building me-1"></i> Informasi Klien & Proyek</h6>
                                        <div class="fw-bold text-body fs-7">${data.customer ? (data.customer.nama_perusahaan || data.customer.nama_customer) : '-'}</div>
                                        <div class="fs-8 text-muted mt-0.5">${custAddress}</div>
                                        ${data.tender ? `
                                            <div class="mt-2.5 pt-2.5 border-top fs-8">
                                                <span class="text-muted">Tender / Proyek:</span>
                                                <div class="fw-bold text-primary"><i class="bi bi-briefcase me-1"></i>${data.tender.nama_tender}</div>
                                                <div class="fs-9 text-muted font-monospace">No. Kontrak: ${data.tender.nomor_kontrak || '-'}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="sq-detail-box h-100">
                                        <h6 class="fs-8 fw-bold text-uppercase text-secondary mb-2"><i class="bi bi-info-circle me-1"></i> Parameter Penawaran</h6>
                                        <div class="d-flex justify-content-between fs-8 mb-1.5">
                                            <span class="text-muted">Tgl Penawaran:</span>
                                            <span class="fw-semibold text-body">${formatIndoDate(data.tanggal_quotation)}</span>
                                        </div>
                                        <div class="d-flex justify-content-between fs-8 mb-1.5">
                                            <span class="text-muted">Masa Berlaku:</span>
                                            <span class="fw-semibold text-body">${formatIndoDate(data.berlaku_hingga) || '30 Hari'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between fs-8 mb-1.5">
                                            <span class="text-muted">Sales / PIC:</span>
                                            <span class="fw-semibold text-body">${data.sales ? data.sales.nama_lengkap : (data.creator ? data.creator.name : '-')}</span>
                                        </div>
                                        <div class="d-flex justify-content-between fs-8">
                                            <span class="text-muted">Term Pembayaran:</span>
                                            <span class="fw-semibold text-body">${termText}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold fs-7 mb-2"><i class="bi bi-box-seam me-1 text-primary"></i> Rincian Item Penawaran</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered align-middle sq-detail-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 40px;">#</th>
                                            <th>Nama Item & Spesifikasi</th>
                                            <th class="text-center" style="width: 120px;">Qty</th>
                                            <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                            <th class="text-end" style="width: 110px;">Diskon</th>
                                            <th class="text-end" style="width: 160px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold fs-8">Subtotal Item:</td>
                                            <td class="text-end fw-bold fs-8 font-monospace">Rp ${parseFloat(data.subtotal).toLocaleString('id-ID')}</td>
                                        </tr>
                                        ${data.diskon_nominal > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8">Diskon Tambahan (${parseFloat(data.diskon_persen) > 0 ? parseFloat(data.diskon_persen) + '%' : 'Nominal'}):</td>
                                                <td class="text-end text-danger fs-8 font-monospace">- Rp ${parseFloat(data.diskon_nominal).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        ${data.nilai_ppn > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8">PPN (${parseFloat(data.tarif_ppn)}%):</td>
                                                <td class="text-end fs-8 font-monospace">+ Rp ${parseFloat(data.nilai_ppn).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        ${data.nilai_pph > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8">PPh (${parseFloat(data.tarif_pph)}%):</td>
                                                <td class="text-end fs-8 font-monospace">+ Rp ${parseFloat(data.nilai_pph).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        ${data.biaya_pengiriman > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8">Biaya Pengiriman:</td>
                                                <td class="text-end fs-8 font-monospace">+ Rp ${parseFloat(data.biaya_pengiriman).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        <tr class="table-active">
                                            <td colspan="5" class="text-end fw-bold fs-7">Grand Total:</td>
                                            <td class="text-end fw-bold fs-7 text-primary font-monospace">Rp ${parseFloat(data.grand_total).toLocaleString('id-ID')}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            ${data.catatan || data.syarat_ketentuan ? `
                                <div class="row g-3 mb-2">
                                    ${data.catatan ? `
                                        <div class="col-12 col-md-6">
                                            <div class="sq-detail-box h-100">
                                                <div class="fs-8 fw-bold text-secondary text-uppercase mb-1">Catatan Internal / Klien</div>
                                                <div class="fs-8 text-body">${data.catatan}</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                    ${data.syarat_ketentuan ? `
                                        <div class="col-12 col-md-6">
                                            <div class="sq-detail-box h-100">
                                                <div class="fs-8 fw-bold text-secondary text-uppercase mb-1">Syarat & Ketentuan</div>
                                                <div class="fs-8 text-body">${data.syarat_ketentuan}</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        `;

                        $('#detailBodyContent').html(html);

                        // Actions in modal footer
                        let leftBtns = '';
                        if (resp.can_print) {
                            leftBtns += `<a href="/penawaran/${data.id}/print" target="_blank" class="btn btn-sm btn-outline-secondary px-3 rounded-pill"><i class="bi bi-printer me-1"></i> Cetak PDF</a>`;
                        }

                        // Approval buttons (Only visible for users with approve-penawaran permission / Super Admin / Owner)
                        const isPending = (data.status === 'Draft' || data.status === 'Sent' || data.status === 'Pending Approval');
                        if (isPending && (resp.can_approve || resp.can_approve == 1 || resp.can_approve === true)) {
                            leftBtns += `
                                <button type="button" class="btn btn-sm btn-success px-3 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Approved">
                                    <i class="bi bi-check-circle me-1"></i> Setujui (Approve)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Rejected">
                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                </button>
                            `;
                        } else if (data.status === 'Approved' && !data.sales_order_id && (resp.can_convert_so || resp.can_convert_so == 1 || resp.can_convert_so === true)) {
                            leftBtns += `
                                <button type="button" class="btn btn-sm btn-success px-3 rounded-pill btn-convert-so" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                    <i class="bi bi-cart-plus me-1"></i> Konversi ke Sales Order (SO)
                                </button>
                            `;
                        }
                        $('#detailActionLeft').html(leftBtns);
                    },
                    error: function(xhr) {
                        $('#detailBodyContent').html(`
                            <div class="alert alert-danger m-4 text-center rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal memuat data detail: ${xhr.responseJSON?.message || xhr.statusText || 'Terjadi kesalahan sistem.'}
                            </div>
                        `);
                    }
                });
            };

            // View Detail Click
            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                loadQuotationDetail(id);
            });

            // Create Revision Handler
            $(document).on('click', '.btn-revisi', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Buat Revisi Penawaran?',
                    text: `Sistem akan membuat salinan penawaran ${nomor} dengan nomor revisi baru dan menandai penawaran lama sebagai 'Revised'.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Buat Revisi',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/revisi`,
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Revisi Berhasil Dibuat!',
                                    text: resp.message || 'Penawaran revisi baru siap disunting.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                                if (resp.redirect) {
                                    window.location.href = resp.redirect;
                                }
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Membuat Revisi',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Approve / Reject Quotation Handler
            $(document).on('click', '.btn-approve-sq', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');
                const isApproved = (status === 'Approved');

                Swal.fire({
                    title: isApproved ? 'Setujui Penawaran Ini?' : 'Tolak Penawaran Ini?',
                    text: isApproved ? 'Status penawaran akan diubah menjadi Approved dan siap diterbitkan Sales Order (SO).' : 'Status penawaran akan ditandai sebagai Rejected.',
                    icon: isApproved ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isApproved ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: isApproved ? 'Ya, Setujui' : 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/approve`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            success: function(resp) {
                                $('#modalDetailQuotation').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Status Berhasil Diperbarui',
                                    text: resp.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Memproses Status',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Convert to SO Handler
            $(document).on('click', '.btn-convert-so', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Konversi ke Sales Order (SO)?',
                    text: `Dokumen penawaran ${nomor} akan diterbitkan menjadi Sales Order resmi.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Terbitkan SO',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/convert-so`,
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                $('#modalDetailQuotation').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sales Order Berhasil Diterbitkan!',
                                    text: resp.message,
                                    confirmButtonText: 'Buka Sales Order',
                                    showCancelButton: true,
                                    cancelButtonText: 'Tetap di Sini'
                                }).then((navResult) => {
                                    table.ajax.reload();
                                    if (navResult.isConfirmed && resp.redirect) {
                                        window.location.href = resp.redirect;
                                    }
                                });
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Konversi SO',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Delete Quotation
            $(document).on('click', '.btn-delete-sq', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Hapus Penawaran?',
                    text: `Apakah Anda yakin ingin menghapus draft penawaran ${nomor}? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/delete`,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: resp.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menghapus',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts>
