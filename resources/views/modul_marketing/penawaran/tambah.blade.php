<x-layouts title="Buat Penawaran Harga Baru">

    @push('styles')
    <style>
        /* Base Card Styling */
        .sq-form-section {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.025);
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        [data-bs-theme="dark"] .sq-form-section {
            background: #182234 !important;
            border-color: rgba(255, 255, 255, 0.09) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
            color: #f8fafc;
        }

        /* Section Titles */
        .sq-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4f46e5;
            margin-bottom: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        [data-bs-theme="dark"] .sq-section-title {
            color: #818cf8 !important;
        }

        /* Labels */
        .form-label, label {
            color: #334155;
            font-weight: 600;
        }
        [data-bs-theme="dark"] .form-label, 
        [data-bs-theme="dark"] label {
            color: #e2e8f0 !important;
        }

        /* Inputs & Form Controls */
        .form-control, .form-select {
            background-color: #ffffff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        [data-bs-theme="dark"] .form-control, 
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
        }

        /* Readonly & Input Groups */
        .input-group-text {
            background-color: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }
        [data-bs-theme="dark"] .input-group-text {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }
        .form-control[readonly], .form-control:disabled {
            background-color: #f8fafc !important;
            color: #64748b !important;
        }
        [data-bs-theme="dark"] .form-control[readonly], 
        [data-bs-theme="dark"] .form-control:disabled {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        /* Table Items */
        .table-items th {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8fafc;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 0.6rem;
        }
        [data-bs-theme="dark"] .table-items th {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-bottom-color: rgba(255, 255, 255, 0.12) !important;
        }
        .table-items td {
            background-color: #ffffff;
            color: #1e293b;
            border-color: #e2e8f0;
            padding: 0.65rem 0.6rem;
        }
        [data-bs-theme="dark"] .table-items td {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .select-price-tier {
            font-size: 0.73rem !important;
            border-radius: 6px;
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        [data-bs-theme="dark"] .select-price-tier {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        .item-subtotal-display {
            color: #0f172a;
            font-family: var(--bs-font-monospace);
            font-weight: 700;
        }
        [data-bs-theme="dark"] .item-subtotal-display {
            color: #f8fafc !important;
        }

        /* Grand Total Box */
        .sq-grand-total-box {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        [data-bs-theme="dark"] .sq-grand-total-box {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.28) 0%, rgba(99, 102, 241, 0.12) 100%) !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
        }
        .sq-grand-total-title {
            color: #4f46e5;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        [data-bs-theme="dark"] .sq-grand-total-title {
            color: #a5b4fc !important;
        }
        .sq-grand-total-amount {
            color: #3730a3;
            font-weight: 800;
        }
        [data-bs-theme="dark"] .sq-grand-total-amount {
            color: #ffffff !important;
        }

        /* HPP & Margin Box */
        .sq-hpp-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        [data-bs-theme="dark"] .sq-hpp-box {
            background: rgba(255, 255, 255, 0.04) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Sticky Sidebar */
        .sticky-summary {
            position: sticky;
            top: 80px;
        }

        /* Badges */
        .badge-soft-emerald { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-soft-amber   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-soft-sky     { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-soft-indigo  { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-soft-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-soft-neutral { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }

        [data-bs-theme="dark"] .badge-soft-emerald { background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(16, 185, 129, 0.3); }
        [data-bs-theme="dark"] .badge-soft-amber   { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3); }
        [data-bs-theme="dark"] .badge-soft-sky     { background: rgba(14, 165, 233, 0.2); color: #7dd3fc; border-color: rgba(14, 165, 233, 0.3); }
        [data-bs-theme="dark"] .badge-soft-indigo  { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border-color: rgba(99, 102, 241, 0.3); }
        [data-bs-theme="dark"] .badge-soft-danger  { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
        [data-bs-theme="dark"] .badge-soft-neutral { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3); }

        /* Select2 Dark Mode Override */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #64748b !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
            background-color: #182234 !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
            color: #e2e8f0 !important;
            background-color: transparent;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option[aria-selected="true"] {
            background-color: rgba(99, 102, 241, 0.25) !important;
            color: #a5b4fc !important;
        }

        /* Modal Picker Dark Mode */
        [data-bs-theme="dark"] #modalPickerBarang .modal-content {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
        }
        [data-bs-theme="dark"] #modalPickerBarang .modal-header,
        [data-bs-theme="dark"] #modalPickerBarang .modal-footer {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        [data-bs-theme="dark"] #modalPickerBarang .table thead th {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        [data-bs-theme="dark"] #modalPickerBarang .table tbody td {
            background-color: transparent !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        /* Modal Lookup Table & Filter Chips (Dual Light & Dark Theme) */
        .modal-lookup-table th {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            background-color: #f8fafc !important;
            color: #64748b !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 0.65rem 0.75rem !important;
            white-space: nowrap !important;
        }
        [data-bs-theme="dark"] .modal-lookup-table th {
            background-color: #182234 !important;
            color: #94a3b8 !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }
        .modal-lookup-table td {
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 0.75rem 0.75rem !important;
            transition: background-color 0.15s ease !important;
        }
        [data-bs-theme="dark"] .modal-lookup-table td {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border-bottom-color: rgba(255, 255, 255, 0.06) !important;
        }
        .modal-lookup-table tr:hover td {
            background-color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .modal-lookup-table tr:hover td {
            background-color: #1e293b !important;
        }

        /* Filter Chips Styling */
        .filter-chip-cust, .filter-chip-tender {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 9999px !important;
            padding: 0.3rem 0.75rem !important;
            transition: all 0.15s ease !important;
            white-space: nowrap !important;
        }
        .filter-chip-cust.btn-outline-secondary, .filter-chip-tender.btn-outline-secondary {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }
        .filter-chip-cust.btn-outline-secondary:hover, .filter-chip-tender.btn-outline-secondary:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }
        [data-bs-theme="dark"] .filter-chip-cust.btn-outline-secondary, 
        [data-bs-theme="dark"] .filter-chip-tender.btn-outline-secondary {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .filter-chip-cust.btn-outline-secondary:hover, 
        [data-bs-theme="dark"] .filter-chip-tender.btn-outline-secondary:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
        }

        /* Modal Dialog Theme */
        [data-bs-theme="dark"] .modal-content {
            background-color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        [data-bs-theme="dark"] .modal-header {
            background-color: #182234 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Mobile Responsive Optimizations */
        @media (max-width: 767.98px) {
            .sq-form-section {
                padding: 1rem 1.1rem !important;
                border-radius: 14px !important;
                margin-bottom: 1rem !important;
            }
            .sq-section-title {
                font-size: 0.8rem !important;
                margin-bottom: 0.85rem !important;
            }
            .page-header-title {
                font-size: 1.15rem !important;
                line-height: 1.35 !important;
            }
            /* Table Horizontal Scrolling Protection */
            .table-items-wrapper {
                position: relative;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
                border: 1px solid rgba(0,0,0,0.08);
            }
            [data-bs-theme="dark"] .table-items-wrapper {
                border-color: rgba(255,255,255,0.1);
            }
            #itemsTable {
                min-width: 680px !important;
            }
            /* Add item buttons grid */
            .btn-add-item-group {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                width: 100%;
            }
            .btn-add-item-group .btn {
                padding: 0.45rem 0.5rem !important;
                font-size: 0.75rem !important;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
                white-space: nowrap;
            }
            /* Tender Info Box Responsive */
            #tenderPaguInfoBox {
                padding: 0.65rem 0.8rem !important;
            }
            #tenderPaguInfoBox .d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.4rem !important;
            }
            /* Floating Scroll Button Safety */
            .sticky-summary {
                margin-bottom: 4.5rem !important;
            }
        }
    </style>
    @endpush

    <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4 gap-2.5">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 fs-9 fs-md-8">
                        <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('penawaran.index') }}" class="text-decoration-none">Penawaran Harga</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Buat Penawaran Baru</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-bold mb-0 text-body page-header-title">Buat Penawaran Harga</h3>
                    <span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill fw-semibold">REV.0</span>
                </div>
                <!-- <p class="text-muted fs-8 mb-0 mt-0.5">Lengkapi data klien, rincian barang penawaran harga, pajak, dan ketentuan pembayaran.</p> -->
            </div>
            
            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end">
                <a href="{{ route('penawaran.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 shadow-xs">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <form id="formQuotation" action="{{ route('penawaran.store') }}" method="POST">
            @csrf
            <input type="hidden" name="status" id="inputQuotationStatus" value="Sent">

            <div class="row g-3 g-md-4">
                
                <!-- Main Form Column (Left: col-12 col-xl-8) -->
                <div class="col-12 col-xl-8">
                    
                    <!-- 1. Identitas Dokumen & Klien -->
                    <div class="sq-form-section">
                        <div class="sq-section-title">
                            <i class="bi bi-building me-1"></i> 1. Identitas Klien & Dokumen Penawaran
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Nomor Penawaran <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_quotation" class="form-control form-control-sm font-monospace fw-bold" value="{{ $nomorQuotation }}" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fs-8">Tanggal Penawaran <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_quotation" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fs-8">Masa Berlaku Hingga</label>
                                <input type="date" name="berlaku_hingga" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>

                            @php
                                $curCust = $customers->firstWhere('id', $selectedCustomerId);
                                $curCustText = $curCust ? ($curCust->nama_perusahaan ?: $curCust->nama_customer) : '';
                                $curTenderText = $selectedTender ? ($selectedTender->nomor_kontrak . ' - ' . $selectedTender->nama_tender) : '';
                            @endphp
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Pilih Customer <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="customer_id" id="selectCustomer" value="{{ $selectedCustomerId }}" required>
                                    <input type="text" id="displayCustomer" class="form-control form-control-sm rounded-start-3 bg-body" placeholder="Pilih Customer / Instansi..." readonly required value="{{ $curCustText }}">
                                    <button type="button" class="btn btn-primary px-3 rounded-end-3" id="btnOpenLookupCustomer" title="Cari Customer">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Terkait Tender / Proyek (Opsional)</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="tender_id" id="selectTender" value="{{ $selectedTender?->id }}" data-pagu="{{ $selectedTender ? (float)$selectedTender->nilai_estimasi : 0 }}">
                                    <input type="text" id="displayTender" class="form-control form-control-sm rounded-start-3 bg-body" placeholder="-- Penawaran Langsung (Non-Tender) --" readonly value="{{ $curTenderText }}">
                                    <button type="button" class="btn btn-outline-danger px-2.5 {{ $selectedTender ? '' : 'd-none' }}" id="btnClearTender" title="Hapus Kaitan Tender">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary px-3 rounded-end-3" id="btnOpenLookupTender" title="Cari Tender">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="tenderPaguInfoBox" class="mt-1.5 p-2 rounded-3 bg-body-tertiary border {{ $selectedTender ? '' : 'd-none' }}">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 fs-8">
                                        <div>
                                            <span class="text-muted"><i class="bi bi-wallet2 text-success me-1"></i>Nilai Proyek:</span>
                                            <strong class="font-monospace text-body ms-1" id="displayTenderPagu">Rp {{ $selectedTender ? number_format($selectedTender->nilai_estimasi, 0, ',', '.') : 0 }}</strong>
                                        </div>
                                        <div id="badgeBudgetStatus">
                                            <!-- Dynamically populated via JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fs-8">Perihal / Judul Penawaran <span class="text-danger">*</span></label>
                                <input type="text" name="perihal" class="form-control form-control-sm" placeholder="Contoh: Penawaran Pengadaan Seragam Kerja & APD Proyek" value="{{ $selectedTender ? 'Penawaran ' . $selectedTender->nama_tender : '' }}" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fs-8">Sales / PIC</label>
                                <select name="sales_id" class="form-select form-select-sm select2">
                                    <option value="">-- Pilih Sales PIC --</option>
                                    @foreach($salesList as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Rincian Item Penawaran -->
                    <div class="sq-form-section">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                            <div class="sq-section-title mb-0">
                                <i class="bi bi-cart-check me-1"></i> 2. Rincian Item Barang & Harga
                            </div>
                            <div class="btn-add-item-group d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddMasterItem">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Master
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnAddCustomItem">
                                    <i class="bi bi-pencil-square me-1"></i> Item Bebas / Custom
                                </button>
                            </div>
                        </div>

                        <div class="d-md-none text-muted fs-9 mb-2 d-flex align-items-center gap-1.5 px-1">
                            <i class="bi bi-arrows-expand text-primary"></i>
                            <span>Geser tabel ke samping untuk melihat seluruh kolom</span>
                        </div>

                        <div class="table-responsive table-items-wrapper mb-3">
                            <table class="table table-bordered align-middle table-items w-100 mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 32%;">Item Barang & Spesifikasi</th>
                                        <th style="width: 14%;">Qty & Satuan</th>
                                        <th style="width: 20%;">Harga Satuan (Rp)</th>
                                        <th style="width: 12%;">Diskon (%)</th>
                                        <th style="width: 16%;">Subtotal (Rp)</th>
                                        <th style="width: 6%;" class="text-center"><i class="bi bi-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    @if($selectedTender && $selectedTender->items->count() > 0)
                                        @foreach($selectedTender->items as $idx => $tItem)
                                        @php
                                            $pPath = $tItem->barang?->primaryFoto ? $tItem->barang->primaryFoto->foto_path : null;
                                            $pUrl = $pPath ? (str_starts_with($pPath, 'http') ? $pPath : (str_starts_with($pPath, 'storage/') ? asset($pPath) : asset('storage/' . ltrim($pPath, '/')))) : asset('uploads/barang/placeholder_garment.png');
                                            $hRetail = (float)($tItem->barang?->harga_jual_retail ?: 0);
                                            $hGrosir = (float)($tItem->barang?->harga_jual_grosir ?: 0);
                                            $hTender = (float)($tItem->barang?->harga_jual_tender ?: 0);
                                            $hDefault = (float)($tItem->harga_estimasi ?: ($hTender ?: ($hGrosir ?: ($hRetail ?: 0))));
                                        @endphp
                                        <tr id="itemRow_{{ $idx + 1 }}" class="item-calc-row">
                                            <td>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    @if($tItem->barang_id)
                                                        <img src="{{ $pUrl }}" alt="Foto" style="width: 36px; height: 36px; object-fit: cover; border-radius: 8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                                        <div>
                                                            <div class="fw-bold text-body fs-8">{{ $tItem->barang?->nama_barang }}</div>
                                                            <code class="badge badge-soft-neutral fs-9 px-1.5 py-0.5">{{ $tItem->barang?->kode_barang }}</code>
                                                        </div>
                                                        <input type="hidden" name="items[{{ $idx + 1 }}][barang_id]" value="{{ $tItem->barang_id }}">
                                                    @else
                                                        <input type="text" name="items[{{ $idx + 1 }}][nama_barang_custom]" class="form-control form-control-sm fw-bold" value="{{ $tItem->nama_barang_custom }}" placeholder="Nama item..." required>
                                                    @endif
                                                </div>
                                                <input type="text" name="items[{{ $idx + 1 }}][deskripsi_item]" class="form-control form-control-sm fs-9" value="{{ $tItem->catatan_item }}" placeholder="Catatan spesifikasi...">
                                                <input type="hidden" name="items[{{ $idx + 1 }}][kategori_id]" value="{{ $tItem->barang?->kategori_barang_id }}">
                                                <input type="hidden" name="items[{{ $idx + 1 }}][satuan_id]" value="{{ $tItem->barang?->satuan_id }}">
                                                <input type="hidden" name="items[{{ $idx + 1 }}][hpp_satuan]" class="item-hpp-satuan" value="{{ $tItem->barang?->hpp_referensi ?: 0 }}">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="0.01" name="items[{{ $idx + 1 }}][qty]" class="form-control form-control-sm text-end item-qty" value="{{ (float)$tItem->qty }}" required>
                                                    <input type="text" name="items[{{ $idx + 1 }}][nama_satuan]" class="form-control form-control-sm" value="{{ $tItem->satuan ?: ($tItem->barang?->satuan?->nama_satuan ?? 'Pcs') }}" style="max-width: 60px;" readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm mb-1">
                                                    <span class="input-group-text fs-9 px-1.5">Rp</span>
                                                    <input type="text" inputmode="numeric" name="items[{{ $idx + 1 }}][harga]" class="form-control form-control-sm text-end item-price fw-bold font-monospace" value="{{ number_format($hDefault, 0, ',', '.') }}" required>
                                                </div>
                                                @if($tItem->barang_id)
                                                <select class="form-select form-select-xs py-0.5 px-1 fs-9 select-price-tier"
                                                    data-harga-retail="{{ $hRetail }}"
                                                    data-harga-grosir="{{ $hGrosir }}"
                                                    data-harga-tender="{{ $hTender }}">
                                                    <option value="tender" selected>🏷️ Tender: Rp {{ number_format($hTender, 0, ',', '.') }}</option>
                                                    <option value="grosir">📦 Grosir: Rp {{ number_format($hGrosir, 0, ',', '.') }}</option>
                                                    <option value="satuan">🛍️ Satuan: Rp {{ number_format($hRetail, 0, ',', '.') }}</option>
                                                    <option value="custom">✏️ Custom / Manual</option>
                                                </select>
                                                @else
                                                <span class="badge badge-soft-neutral border fs-9 w-100 py-1">Item Bebas / Custom</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="100" name="items[{{ $idx + 1 }}][diskon_persen]" class="form-control form-control-sm text-end item-disc-persen" value="0">
                                            </td>
                                            <td class="text-end">
                                                <span class="item-subtotal-display fs-8">Rp 0</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-remove-item" data-id="{{ $idx + 1 }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div id="emptyItemNotice" class="text-center py-4 text-muted border border-dashed rounded-3" style="{{ ($selectedTender && $selectedTender->items->count() > 0) ? 'display:none;' : '' }}">
                            <i class="bi bi-box-seam fs-3 d-block mb-1 text-secondary opacity-50"></i>
                            <span class="fs-8">Belum ada item ditambahkan. Klik tombol <strong>Tambah dari Master Barang</strong> di atas.</span>
                        </div>
                    </div>

                    <!-- 3. Syarat & Ketentuan -->
                    <div class="sq-form-section">
                        <div class="sq-section-title">
                            <i class="bi bi-card-text me-1"></i> 3. Syarat, Ketentuan, & Catatan
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Syarat & Ketentuan (Terms & Conditions)</label>
                                <textarea name="syarat_ketentuan" class="form-control form-control-sm" rows="3" placeholder="Contoh: 1. Harga belum termasuk biaya bongkar muat. 2. Waktu produksi 14 hari kerja.">1. Harga sudah termasuk PPh/PPN sesuai yang tertera.
2. Waktu pengiriman maksimal 14 hari kerja setelah PO diterima.
3. Garansi produk cacat produksi 30 hari kalender.</textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-8">Catatan Internal / Eksternal</label>
                                <textarea name="catatan" class="form-control form-control-sm" rows="3" placeholder="Catatan tambahan untuk penawaran ini..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Summary Sidebar (Right: col-12 col-xl-4) -->
                <div class="col-12 col-xl-4">
                    <div class="sticky-summary">
                        
                        <!-- Calculation Breakdown Card -->
                        <div class="sq-form-section mb-4">
                            <div class="sq-section-title text-success">
                                <i class="bi bi-calculator me-1"></i> Ringkasan Nilai Penawaran
                            </div>

                            <div class="d-flex justify-content-between fs-8 mb-2">
                                <span class="text-muted">Subtotal Item:</span>
                                <span class="fw-bold font-monospace" id="displaySubtotal">Rp 0</span>
                                <input type="hidden" name="subtotal" id="inputSubtotal" value="0">
                            </div>

                            <!-- Diskon Global -->
                            <div class="mb-3 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="fs-8 mb-0">Diskon Global:</label>
                                    <div class="input-group input-group-sm" style="width: 130px;">
                                        <input type="number" step="0.01" min="0" name="diskon_persen" id="inputDiskonPersen" class="form-control form-control-sm text-end" value="0" placeholder="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between fs-9 text-danger">
                                    <span>Nominal Diskon:</span>
                                    <span id="displayDiskonNominal" class="font-monospace">- Rp 0</span>
                                    <input type="hidden" name="diskon_nominal" id="inputDiskonNominal" value="0">
                                </div>
                            </div>

                            <!-- Pajak PPN -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="fs-8 mb-1">Pajak Pertambahan Nilai (PPN):</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <select name="tipe_ppn" id="selectTipePpn" class="form-select form-select-sm">
                                            <option value="None" selected>Tanpa PPN (0%)</option>
                                            <option value="Exclude">Exclude PPN</option>
                                            <option value="Include">Include PPN</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        @php
                                            $defaultPpn = $pajakPpnList->firstWhere('tarif', '11.00') ?: ($pajakPpnList->firstWhere('tarif', '12.00') ?: $pajakPpnList->first());
                                            $defaultTarifPpn = $defaultPpn ? (float)$defaultPpn->tarif : 11;
                                            $defaultPpnId = $defaultPpn ? $defaultPpn->id : null;
                                        @endphp
                                        <select name="ppn_pajak_id" id="selectPpnPajak" class="form-select form-select-sm" disabled>
                                            <option value="" data-tarif="0" selected>Non PPN (0%)</option>
                                            @foreach($pajakPpnList as $p)
                                                <option value="{{ $p->id }}" data-tarif="{{ (float)$p->tarif }}" data-default="{{ ($p->id == $defaultPpnId) ? '1' : '0' }}">
                                                    {{ $p->nama_pajak }} ({{ (float)$p->tarif }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="tarif_ppn" id="inputTarifPpn" value="0">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between fs-9 text-info mt-1">
                                    <span>Nilai PPN:</span>
                                    <span id="displayNilaiPpn" class="font-monospace">+ Rp 0</span>
                                    <input type="hidden" name="nilai_ppn" id="inputNilaiPpn" value="0">
                                </div>
                            </div>

                            <!-- Biaya Pengiriman / Ekstra -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="fs-8 mb-1">Biaya Pengiriman / Ekstra (Rp):</label>
                                <input type="text" inputmode="numeric" name="biaya_pengiriman" id="inputBiayaPengiriman" class="form-control form-control-sm text-end fw-semibold font-monospace" value="0">
                            </div>

                            <!-- Grand Total Box -->
                            <div class="sq-grand-total-box mb-3">
                                <div class="sq-grand-total-title mb-1">Grand Total Penawaran</div>
                                <div class="fs-4 sq-grand-total-amount font-monospace" id="displayGrandTotal">Rp 0</div>
                                <input type="hidden" name="grand_total" id="inputGrandTotal" value="0">
                            </div>

                            <!-- HPP & Profit Analysis (Authorized Only) -->
                            @if($canSeeHpp)
                            <div class="sq-hpp-box mb-3">
                                <div class="fs-8 fw-bold text-secondary text-uppercase mb-1.5"><i class="bi bi-graph-up-arrow me-1"></i> Analisis HPP & Margin</div>
                                <div class="d-flex justify-content-between fs-8 mb-1">
                                    <span class="text-muted">Total HPP:</span>
                                    <span class="fw-semibold font-monospace" id="displayTotalHpp">Rp 0</span>
                                    <input type="hidden" name="total_hpp" id="inputTotalHpp" value="0">
                                </div>
                                <div class="d-flex justify-content-between fs-8 mb-1">
                                    <span class="text-muted">Estimasi Laba Kotor:</span>
                                    <span class="fw-bold text-success font-monospace" id="displayLabaKotor">Rp 0</span>
                                    <input type="hidden" name="estimasi_laba_kotor" id="inputLabaKotor" value="0">
                                </div>
                                <div class="d-flex justify-content-between align-items-center fs-8">
                                    <span class="text-muted">Margin Keuntungan:</span>
                                    <span class="badge badge-soft-emerald fw-bold fs-8" id="displayMarginPersen">0.0%</span>
                                    <input type="hidden" name="margin_persen" id="inputMarginPersen" value="0">
                                </div>
                            </div>
                            @endif

                            <!-- Ketentuan Pembayaran -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="fs-8 mb-1">Tenor / Termin Pembayaran:</label>
                                <select name="payment_term_id" class="form-select form-select-sm">
                                    <option value="">-- Pilih Termin --</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}">{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} Hari)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="fs-8 mb-1">Uang Muka (DP):</label>
                                    <select name="tipe_dp" id="selectTipeDp" class="form-select form-select-sm">
                                        <option value="None">Tanpa DP</option>
                                        <option value="Persen">Persen (%)</option>
                                        <option value="Nominal">Nominal (Rp)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="fs-8 mb-1">Nilai DP:</label>
                                    <input type="number" step="0.01" min="0" name="dp_persen" id="inputDpPersen" class="form-control form-control-sm text-end" placeholder="Persen %" style="display:none;">
                                    <input type="text" inputmode="numeric" name="dp_nominal" id="inputDpNominal" class="form-control form-control-sm text-end font-monospace" placeholder="Nominal Rp" style="display:none;">
                                </div>
                            </div>

                            <!-- Action Submit Buttons -->
                            <div class="d-grid gap-2 pt-3 border-top">
                                <button type="submit" name="status_btn" value="Sent" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm btn-submit-quotation">
                                    <i class="bi bi-send-fill me-1"></i> Simpan & Kirim Penawaran
                                </button>
                                <button type="submit" name="status_btn" value="Draft" class="btn btn-outline-secondary rounded-pill py-2 fs-8 fw-semibold btn-submit-quotation">
                                    <i class="bi bi-save me-1"></i> Simpan Sebagai Draft
                                </button>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </form>

    </div>

    <!-- Modal Picker Barang -->
    <div class="modal fade" id="modalPickerBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold mb-0">Pilih Barang dari Master Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <input type="text" id="searchBarangInput" class="form-control rounded-pill px-3.5" placeholder="Ketik nama atau kode barang untuk mencari...">
                    </div>
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="pickerBarangTable">
                            <thead class="sticky-top">
                                <tr>
                                    <th style="width: 40%;">Kode & Nama Produk</th>
                                    <th style="width: 18%;">Kategori</th>
                                    <th style="width: 10%;">Satuan</th>
                                    <th style="width: 22%;" class="text-end">Pilihan Harga Jual</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangList as $b)
                                @php
                                    $pUrl = $b->resolved_photo_url ?: ($b->primaryFoto ? (str_starts_with($b->primaryFoto->foto_path, 'uploads/') ? asset($b->primaryFoto->foto_path) : asset('storage/' . ltrim($b->primaryFoto->foto_path, '/'))) : asset('uploads/barang/placeholder_garment.png'));
                                    $hRetail = (float)($b->harga_retail ?? $b->harga_jual_retail ?? 0);
                                    $hGrosir = (float)($b->harga_grosir ?? $b->harga_jual_grosir ?? 0);
                                    $hTender = (float)($b->harga_tender ?? $b->harga_jual_tender ?? 0);
                                    $hDefault = $hTender ?: ($hGrosir ?: ($hRetail ?: 0));
                                @endphp
                                <tr class="barang-item-row" data-search="{{ strtolower($b->kode_barang . ' ' . $b->nama_barang . ' ' . ($b->kategori?->nama_kategori ?? '')) }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <img src="{{ $pUrl }}" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                            <div>
                                                <div class="fw-bold text-body fs-8 mb-0.5">{{ $b->nama_barang }}</div>
                                                <span class="badge badge-soft-neutral font-monospace" style="font-size:0.7rem;">{{ $b->kode_barang }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-soft-sky fs-9">{{ $b->kategori?->nama_kategori ?? '-' }}</span></td>
                                    <td><span class="badge badge-soft-neutral font-monospace fs-9">{{ $b->satuan?->nama_satuan ?? 'Pcs' }}</span></td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end gap-0.5 font-monospace">
                                            <span class="fs-9 text-muted"><span class="badge badge-soft-neutral me-1">Satuan</span>Rp {{ number_format($hRetail, 0, ',', '.') }}</span>
                                            <span class="fs-9 text-muted"><span class="badge badge-soft-sky me-1">Grosir</span>Rp {{ number_format($hGrosir, 0, ',', '.') }}</span>
                                            <span class="fs-8 fw-bold text-success"><span class="badge badge-soft-emerald me-1">Tender</span>Rp {{ number_format($hTender, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-select-item shadow-xs"
                                            data-id="{{ $b->id }}"
                                            data-kode="{{ $b->kode_barang }}"
                                            data-nama="{{ $b->nama_barang }}"
                                            data-satuan-id="{{ $b->satuan_id }}"
                                            data-satuan-nama="{{ $b->satuan?->nama_satuan ?? 'Pcs' }}"
                                            data-kategori-id="{{ $b->kategori_barang_id }}"
                                            data-harga="{{ $hDefault }}"
                                            data-harga-retail="{{ $hRetail }}"
                                            data-harga-grosir="{{ $hGrosir }}"
                                            data-harga-tender="{{ $hTender }}"
                                            data-hpp="{{ $b->hpp_referensi ?: 0 }}"
                                            data-photo-url="{{ $pUrl }}">
                                            Pilih
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

    <!-- Modal Cari Customer Penawaran (Dual Theme & Horizontal Swipeable Table) -->
    <div class="modal fade" id="modalCariCustomerPenawaran" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-2 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0">
                            <i class="bi bi-building-check fs-5"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="modal-title fw-bold text-body mb-0 fs-6">Pilih Customer / Instansi</h6>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size:0.68rem;">{{ $customers->count() }} Klien</span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size:0.75rem;">Pilih customer yang dituju untuk surat penawaran harga.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 p-md-4 pt-2">
                    <!-- Search Input -->
                    <div class="mb-2.5 position-relative">
                        <input type="text" id="search_customer_penawaran_input" class="form-control rounded-pill ps-5 pe-5 py-2 shadow-xs" placeholder="Ketik nama instansi, PIC, telepon, atau kode..." autocomplete="off">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-primary fs-6"></i>
                        <button type="button" id="btn_clear_search_customer_penawaran" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted border-0 d-none" style="background: transparent;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Swipeable Category Chips (Geser ke Samping) -->
                    <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-2 text-nowrap align-items-center" id="filter_chips_customer_penawaran" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-semibold filter-chip-cust active" data-filter="all">Semua ({{ $customers->count() }})</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="pt">🏢 PT</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="cv">🏭 CV</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="distributor">📦 Distributor</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="end user">🎯 End User</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="retail">🏪 Retail</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-cust" data-filter="agen">🏷️ Agen</button>
                    </div>

                    <!-- Swipe Hint Mobile -->
                    <div class="d-md-none text-muted fs-9 mb-2 d-flex align-items-center gap-1.5 px-1">
                        <i class="bi bi-arrows-expand text-primary"></i>
                        <span>Geser tabel ke samping untuk melihat detail lengkap</span>
                    </div>

                    <!-- Horizontal Scrollable Table -->
                    <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table modal-lookup-table table-hover align-middle mb-0" id="table_customer_penawaran" style="min-width: 560px;">
                            <thead class="bg-body-tertiary sticky-top" style="z-index: 2;">
                                <tr class="fs-9 text-uppercase text-muted">
                                    <th style="width: 42%;" class="ps-3 py-2.5 text-nowrap">Nama Instansi / Klien</th>
                                    <th style="width: 28%;" class="py-2.5 text-nowrap">Kontak / PIC</th>
                                    <th style="width: 16%;" class="py-2.5 text-nowrap">Jenis</th>
                                    <th style="width: 14%;" class="text-center pe-3 py-2.5 text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="customer_penawaran_lookup_container">
                                @foreach($customers as $c)
                                @php
                                    $corpName = $c->nama_perusahaan ?: $c->nama_customer;
                                    $displayCorpName = preg_replace('/^(PT|CV|UD|PD|KOP)\s+\1\s+/i', '$1 ', $corpName);
                                    $initials = mb_strtoupper(mb_substr($displayCorpName, 0, 2));
                                    if (str_starts_with(mb_strtoupper($displayCorpName), 'PT ')) {
                                        $initials = 'PT';
                                    } elseif (str_starts_with(mb_strtoupper($displayCorpName), 'CV ')) {
                                        $initials = 'CV';
                                    } elseif (str_starts_with(mb_strtoupper($displayCorpName), 'UD ')) {
                                        $initials = 'UD';
                                    }
                                @endphp
                                <tr class="customer-penawaran-card cursor-pointer"
                                    data-id="{{ $c->id }}" 
                                    data-name="{{ $displayCorpName }}"
                                    data-pic="{{ $c->nama_customer }}"
                                    data-jenis="{{ strtolower($c->jenis_customer ?? '') }}"
                                    data-search="{{ strtolower($displayCorpName . ' ' . $c->nama_customer . ' ' . $c->kode_customer . ' ' . $c->telepon . ' ' . ($c->jenis_customer ?? '')) }}">
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary font-monospace rounded-3 px-2 py-1 flex-shrink-0" style="font-size: 0.72rem;">{{ $initials }}</span>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-body fs-7 text-truncate" style="max-width: 240px;">{{ $displayCorpName }}</div>
                                                <code class="text-muted fs-9 font-monospace">{{ $c->kode_customer ?: '-' }}</code>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5">
                                        <div class="fs-8 fw-semibold text-body text-truncate" style="max-width: 160px;">{{ $c->nama_customer ?: '-' }}</div>
                                        <div class="text-muted fs-9"><i class="bi bi-telephone text-success me-1"></i>{{ $c->telepon ?: ($c->hp ?: '-') }}</div>
                                    </td>
                                    <td class="py-2.5 text-nowrap">
                                        @if($c->jenis_customer)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">{{ $c->jenis_customer }}</span>
                                        @else
                                            <span class="text-muted fs-9">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3 py-2.5 text-nowrap">
                                        <button type="button" class="btn btn-sm btn-primary btn-select-customer-penawaran rounded-pill px-3.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                                                data-id="{{ $c->id }}"
                                                data-name="{{ $displayCorpName }}">
                                            <i class="bi bi-check2"></i>
                                            <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div id="customer_penawaran_lookup_empty" class="text-center py-5 d-none">
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

    <!-- Modal Cari Tender Penawaran (Dual Theme & Horizontal Swipeable Table) -->
    <div class="modal fade" id="modalCariTenderPenawaran" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-2 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <span class="p-2 rounded-3 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0">
                            <i class="bi bi-briefcase-fill fs-5"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="modal-title fw-bold text-body mb-0 fs-6">Pilih Proyek / Tender</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size:0.68rem;">{{ $tenders->count() }} Proyek</span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size:0.75rem;">Pilih tender untuk memuat otomatis instansi, PIC, dan rincian item.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 p-md-4 pt-2">
                    <!-- Search Input -->
                    <div class="mb-2.5 position-relative">
                        <input type="text" id="search_tender_penawaran_input" class="form-control rounded-pill ps-5 pe-5 py-2 shadow-xs" placeholder="Ketik nama proyek, no. tender, atau nama instansi..." autocomplete="off">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-success fs-6"></i>
                        <button type="button" id="btn_clear_search_tender_penawaran" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted border-0 d-none" style="background: transparent;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Swipeable Category Chips (Geser ke Samping) -->
                    <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-2 text-nowrap align-items-center" id="filter_chips_tender_penawaran" style="-webkit-overflow-scrolling: touch;">
                        <button type="button" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-semibold filter-chip-tender active" data-filter="all">Semua ({{ $tenders->count() }})</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="won">🏆 Won</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="negotiation">🤝 Negotiation</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="sent">📨 Sent</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="approved">✅ Approved</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold filter-chip-tender" data-filter="draft">📝 Draft</button>
                    </div>

                    <!-- Swipe Hint Mobile -->
                    <div class="d-md-none text-muted fs-9 mb-2 d-flex align-items-center gap-1.5 px-1">
                        <i class="bi bi-arrows-expand text-success"></i>
                        <span>Geser tabel ke samping untuk melihat detail lengkap</span>
                    </div>

                    <!-- Horizontal Scrollable Table -->
                    <div class="table-responsive rounded-3 border" style="max-height: 420px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <table class="table modal-lookup-table table-hover align-middle mb-0" id="table_tender_penawaran" style="min-width: 600px;">
                            <thead class="bg-body-tertiary sticky-top" style="z-index: 2;">
                                <tr class="fs-9 text-uppercase text-muted">
                                    <th style="width: 38%;" class="ps-3 py-2.5 text-nowrap">Nama Proyek / Tender</th>
                                    <th style="width: 27%;" class="py-2.5 text-nowrap">Instansi Klien</th>
                                    <th style="width: 21%;" class="py-2.5 text-nowrap">Nilai Proyek</th>
                                    <th style="width: 14%;" class="text-center pe-3 py-2.5 text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tender_penawaran_lookup_container">
                                @foreach($tenders as $t)
                                @php
                                    $tCustCorp = $t->customer ? ($t->customer->nama_perusahaan ?: $t->customer->nama_customer) : '-';
                                    $displayTCustCorp = preg_replace('/^(PT|CV|UD|PD|KOP)\s+\1\s+/i', '$1 ', $tCustCorp);
                                    $statusBadgeClass = [
                                        'Draft' => 'bg-secondary-subtle text-secondary',
                                        'Pending Approval' => 'bg-warning-subtle text-dark',
                                        'Approved' => 'bg-success-subtle text-success',
                                        'Sent' => 'bg-info-subtle text-info',
                                        'Negotiation' => 'bg-primary-subtle text-primary',
                                        'Won' => 'bg-success text-white',
                                    ][$t->status] ?? 'bg-secondary-subtle text-secondary';
                                @endphp
                                <tr class="tender-penawaran-card cursor-pointer"
                                    data-id="{{ $t->id }}" 
                                    data-nama="{{ $t->nama_tender }}"
                                    data-nomor="{{ $t->nomor_kontrak }}"
                                    data-pagu="{{ (float)$t->nilai_estimasi }}"
                                    data-customer-id="{{ $t->customer_id }}"
                                    data-customer-name="{{ $displayTCustCorp }}"
                                    data-status="{{ strtolower($t->status) }}"
                                    data-search="{{ strtolower($t->nama_tender . ' ' . $t->nomor_kontrak . ' ' . $displayTCustCorp . ' ' . $t->status) }}">
                                    <td class="ps-3 py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success-subtle text-success font-monospace rounded-3 px-2 py-1 flex-shrink-0"><i class="bi bi-briefcase"></i></span>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-body fs-7 text-truncate" style="max-width: 240px;">{{ $t->nama_tender }}</div>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <code class="text-muted fs-9 font-monospace">{{ $t->nomor_kontrak }}</code>
                                                    <span class="badge {{ $statusBadgeClass }} font-monospace" style="font-size: 0.62rem;">{{ $t->status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5">
                                        <div class="fs-8 fw-semibold text-body text-truncate" style="max-width: 180px;">{{ $displayTCustCorp }}</div>
                                        <div class="text-muted fs-9"><i class="bi bi-box-seam me-1"></i>{{ $t->items->count() }} Item</div>
                                    </td>
                                    <td class="py-2.5 text-nowrap">
                                        <div class="fw-bold text-success font-monospace fs-8 text-nowrap">Rp {{ number_format($t->nilai_estimasi, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="text-center pe-3 py-2.5 text-nowrap">
                                        <button type="button" class="btn btn-sm btn-success btn-select-tender-penawaran rounded-pill px-3.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;"
                                                data-id="{{ $t->id }}"
                                                data-nama="{{ $t->nama_tender }}"
                                                data-nomor="{{ $t->nomor_kontrak }}"
                                                data-pagu="{{ (float)$t->nilai_estimasi }}"
                                                data-customer-id="{{ $t->customer_id }}"
                                                data-customer-name="{{ $displayTCustCorp }}">
                                            <i class="bi bi-check2"></i>
                                            <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div id="tender_penawaran_lookup_empty" class="text-center py-5 d-none">
                            <div class="p-3 rounded-circle bg-body-tertiary text-muted d-inline-flex mb-2">
                                <i class="bi bi-search fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Tender Tidak Ditemukan</h6>
                            <p class="text-muted small mb-0">Tidak ada proyek lelang atau tender yang cocok dengan kata kunci pencarian Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window._tendersMap = @json($tendersMap ?? []);

        // Helper functions for Rupiah formatting with dots
        function parseRupiah(val) {
            if (!val) return 0;
            if (typeof val === 'number') return val;
            let clean = val.toString().replace(/[^0-9]/g, '');
            return parseFloat(clean) || 0;
        }

        function formatRupiah(num) {
            return (parseFloat(num) || 0).toLocaleString('id-ID');
        }

        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            let itemIndex = {{ count($initialItems ?? []) + 10 }};

            // Trigger Modal Picker
            $('#btnAddMasterItem').on('click', function() {
                $('#modalPickerBarang').modal('show');
                $('#searchBarangInput').val('').trigger('keyup').focus();
            });

            // Filter in modal picker
            $('#searchBarangInput').on('keyup', function() {
                let term = $(this).val().toLowerCase();
                $('.barang-item-row').each(function() {
                    let searchData = $(this).data('search');
                    $(this).toggle(searchData.indexOf(term) > -1);
                });
            });

            // Select item from picker
            $(document).on('click', '.btn-select-item', function() {
                const b = $(this).data();
                addItemRow({
                    barang_id: b.id,
                    kode: b.kode,
                    nama: b.nama,
                    satuan_id: b.satuanId,
                    satuan_nama: b.satuanNama,
                    kategori_id: b.kategoriId,
                    harga: b.harga,
                    harga_retail: b.hargaRetail,
                    harga_grosir: b.hargaGrosir,
                    harga_tender: b.hargaTender,
                    hpp: b.hpp,
                    photo_url: b.photoUrl,
                    qty: 1,
                    diskon_persen: 0,
                    is_custom: false
                });
                $('#modalPickerBarang').modal('hide');
            });

            // Add Custom Item
            $('#btnAddCustomItem').on('click', function() {
                addItemRow({
                    barang_id: null,
                    kode: 'CUSTOM',
                    nama: '',
                    nama_barang_custom: '',
                    deskripsi_item: '',
                    satuan_id: null,
                    satuan_nama: 'Pcs',
                    kategori_id: null,
                    qty: 1,
                    harga: 0,
                    diskon_persen: 0,
                    harga_retail: 0,
                    harga_grosir: 0,
                    harga_tender: 0,
                    hpp: 0,
                    is_custom: true
                });
            });

            // Function to append Row
            function addItemRow(data) {
                itemIndex++;
                $('#emptyItemNotice').hide();

                let safeCustomName = data.nama_barang_custom || data.nama || '';
                let nameInput = data.is_custom 
                    ? `<input type="text" name="items[${itemIndex}][nama_barang_custom]" class="form-control form-control-sm fw-bold" value="${$('<div>').text(safeCustomName).html()}" placeholder="Nama item / jasa / pekerjaan..." required>`
                    : `<div class="d-flex align-items-center gap-2">
                            ${data.photo_url ? `<img src="${data.photo_url}" style="width:36px;height:36px;object-fit:cover;border-radius:8px;" class="border shadow-xs flex-shrink-0" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">` : ''}
                            <div>
                                <div class="fw-bold text-body fs-8">${$('<div>').text(data.nama).html()}</div>
                                <code class="badge badge-soft-neutral fs-9 px-1.5 py-0.5">${$('<div>').text(data.kode).html()}</code>
                            </div>
                       </div>
                       <input type="hidden" name="items[${itemIndex}][barang_id]" value="${data.barang_id}">`;

                let hRetail = parseFloat(data.harga_retail) || 0;
                let hGrosir = parseFloat(data.harga_grosir) || 0;
                let hTender = parseFloat(data.harga_tender) || 0;
                let currentPrice = parseFloat(data.harga) || 0;
                let tierDef = data.tier_default || 'tender';

                let priceSelectorHtml = !data.is_custom ? `
                    <select class="form-select form-select-xs py-0.5 px-1 fs-9 select-price-tier"
                        data-harga-retail="${hRetail}"
                        data-harga-grosir="${hGrosir}"
                        data-harga-tender="${hTender}">
                        <option value="tender" ${tierDef === 'tender' ? 'selected' : ''}>🏷️ Tender: Rp ${hTender.toLocaleString('id-ID')}</option>
                        <option value="grosir" ${tierDef === 'grosir' ? 'selected' : ''}>📦 Grosir: Rp ${hGrosir.toLocaleString('id-ID')}</option>
                        <option value="satuan" ${tierDef === 'satuan' ? 'selected' : ''}>🛍️ Satuan: Rp ${hRetail.toLocaleString('id-ID')}</option>
                        <option value="custom" ${tierDef === 'custom' ? 'selected' : ''}>✏️ Custom / Manual</option>
                    </select>
                ` : `<span class="badge badge-soft-neutral border fs-9 w-100 py-1">Item Bebas / Custom</span>`;

                let rowHtml = `
                    <tr id="itemRow_${itemIndex}" class="item-calc-row">
                        <td>
                            ${nameInput}
                            <input type="text" name="items[${itemIndex}][deskripsi_item]" class="form-control form-control-sm mt-1 fs-9 text-muted" value="${$('<div>').text(data.deskripsi_item || '').html()}" placeholder="Catatan spesifikasi / detail item...">
                            <input type="hidden" name="items[${itemIndex}][kategori_id]" value="${data.kategori_id || ''}">
                            <input type="hidden" name="items[${itemIndex}][satuan_id]" value="${data.satuan_id || ''}">
                            <input type="hidden" name="items[${itemIndex}][hpp_satuan]" class="item-hpp-satuan" value="${data.hpp || 0}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][qty]" class="form-control form-control-sm text-end item-qty" value="${parseFloat(data.qty) || 1}" required>
                                <input type="text" name="items[${itemIndex}][nama_satuan]" class="form-control form-control-sm" value="${$('<div>').text(data.satuan_nama || 'Pcs').html()}" style="max-width: 60px;" readonly>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text fs-9 px-1.5">Rp</span>
                                <input type="text" inputmode="numeric" name="items[${itemIndex}][harga]" class="form-control form-control-sm text-end item-price fw-bold font-monospace" value="${formatRupiah(currentPrice)}" required>
                            </div>
                            ${priceSelectorHtml}
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" name="items[${itemIndex}][diskon_persen]" class="form-control form-control-sm text-end item-disc-persen" value="${parseFloat(data.diskon_persen) || 0}">
                        </td>
                        <td class="text-end">
                            <span class="item-subtotal-display fs-8">Rp 0</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-remove-item" data-id="${itemIndex}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#itemsTableBody').append(rowHtml);
                calculateTotal();
            }

            // Remove Row
            $(document).on('click', '.btn-remove-item', function() {
                const id = $(this).data('id');
                $(`#itemRow_${id}`).remove();
                if ($('#itemsTableBody tr').length === 0) {
                    $('#emptyItemNotice').show();
                }
                calculateTotal();
            });

            // Real-time Indonesian dot formatting on input for price and currency inputs
            $(document).on('input', '.item-price, #inputBiayaPengiriman, #inputDpNominal', function() {
                let raw = $(this).val().replace(/[^0-9]/g, '');
                if (raw === '') {
                    $(this).val('0');
                } else {
                    $(this).val(parseInt(raw, 10).toLocaleString('id-ID'));
                }
            });

            // Price Tier Change
            $(document).on('change', '.select-price-tier', function() {
                let row = $(this).closest('tr');
                let tier = $(this).val();
                let priceInput = row.find('.item-price');
                let hRetail = parseFloat($(this).data('harga-retail')) || 0;
                let hGrosir = parseFloat($(this).data('harga-grosir')) || 0;
                let hTender = parseFloat($(this).data('harga-tender')) || 0;

                if (tier === 'tender') {
                    priceInput.val(formatRupiah(hTender));
                } else if (tier === 'grosir') {
                    priceInput.val(formatRupiah(hGrosir));
                } else if (tier === 'satuan') {
                    priceInput.val(formatRupiah(hRetail));
                }
                calculateTotal();
            });

            // When user types manually in price input, mark tier as custom if different
            $(document).on('input', '.item-price', function() {
                let row = $(this).closest('tr');
                let tierSelect = row.find('.select-price-tier');
                if (tierSelect.length) {
                    let currentVal = parseRupiah($(this).val());
                    let hTender = parseFloat(tierSelect.data('harga-tender')) || 0;
                    let hGrosir = parseFloat(tierSelect.data('harga-grosir')) || 0;
                    let hRetail = parseFloat(tierSelect.data('harga-retail')) || 0;

                    if (currentVal === hTender && hTender > 0) {
                        tierSelect.val('tender');
                    } else if (currentVal === hGrosir && hGrosir > 0) {
                        tierSelect.val('grosir');
                    } else if (currentVal === hRetail && hRetail > 0) {
                        tierSelect.val('satuan');
                    } else {
                        tierSelect.val('custom');
                    }
                }
            });

            // Live Calculation Trigger
            $(document).on('input change', '.item-qty, .item-price, .item-disc-persen, #inputDiskonPersen, #selectTipePpn, #selectPpnPajak, #inputBiayaPengiriman, #selectTipeDp, #inputDpPersen, #inputDpNominal', function() {
                calculateTotal();
            });

            // Update PPN percentage when tax select changes
            $('#selectPpnPajak').on('change', function() {
                if ($('#selectTipePpn').val() !== 'None') {
                    let tarif = $(this).find(':selected').data('tarif');
                    if (typeof tarif === 'undefined' || isNaN(tarif) || tarif === 0) {
                        tarif = 11;
                    }
                    $('#inputTarifPpn').val(tarif);
                } else {
                    $('#inputTarifPpn').val(0);
                }
                calculateTotal();
            });

            // Toggle PPN Active / Inactive
            $('#selectTipePpn').on('change', function() {
                let val = $(this).val();
                if (val === 'None') {
                    $('#selectPpnPajak').val('').prop('disabled', true);
                    $('#inputTarifPpn').val(0);
                } else {
                    $('#selectPpnPajak').prop('disabled', false);
                    if (!$('#selectPpnPajak').val()) {
                        let defaultOption = $('#selectPpnPajak option[data-tarif!="0"]').first();
                        if (defaultOption.length) {
                            $('#selectPpnPajak').val(defaultOption.val());
                        }
                    }
                    let tarif = $('#selectPpnPajak').find(':selected').data('tarif');
                    if (typeof tarif === 'undefined' || isNaN(tarif) || tarif === 0) {
                        tarif = 11;
                    }
                    $('#inputTarifPpn').val(tarif);
                }
                calculateTotal();
            });

            // ===== MODAL LOOKUP CUSTOMER PENAWARAN =====
            $('#btnOpenLookupCustomer').on('click', function() {
                $('#modalCariCustomerPenawaran').modal('show');
                $('#search_customer_penawaran_input').val('');
                $('#filter_chips_customer_penawaran .filter-chip-cust').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $('#filter_chips_customer_penawaran .filter-chip-cust[data-filter="all"]').removeClass('btn-outline-secondary').addClass('btn-primary active');
                filterCustomerTable();
                setTimeout(() => $('#search_customer_penawaran_input').focus(), 300);
            });

            function filterCustomerTable() {
                let term = $('#search_customer_penawaran_input').val().toLowerCase().trim();
                let activeFilter = $('#filter_chips_customer_penawaran .filter-chip-cust.active').data('filter') || 'all';
                $('#btn_clear_search_customer_penawaran').toggleClass('d-none', !term);
                
                let visibleCount = 0;
                $('#customer_penawaran_lookup_container tr.customer-penawaran-card').each(function() {
                    let searchStr = (String($(this).attr('data-search') || '')).toLowerCase();
                    let jenis = (String($(this).attr('data-jenis') || '')).toLowerCase();
                    let name = (String($(this).attr('data-name') || '')).toLowerCase();

                    let matchSearch = !term || searchStr.indexOf(term) > -1;
                    let matchCategory = true;
                    if (activeFilter === 'pt') {
                        matchCategory = name.startsWith('pt ') || searchStr.indexOf('pt ') > -1;
                    } else if (activeFilter === 'cv') {
                        matchCategory = name.startsWith('cv ') || searchStr.indexOf('cv ') > -1;
                    } else if (activeFilter !== 'all') {
                        matchCategory = jenis.indexOf(activeFilter) > -1;
                    }

                    if (matchSearch && matchCategory) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });
                $('#customer_penawaran_lookup_empty').toggleClass('d-none', visibleCount > 0);
            }

            $(document).on('input keyup search paste', '#search_customer_penawaran_input', function() {
                filterCustomerTable();
            });

            $(document).on('click', '.filter-chip-cust', function() {
                $('#filter_chips_customer_penawaran .filter-chip-cust').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
                filterCustomerTable();
            });

            $('#btn_clear_search_customer_penawaran').on('click', function() {
                $('#search_customer_penawaran_input').val('');
                filterCustomerTable();
                $('#search_customer_penawaran_input').focus();
            });

            $(document).on('click', '.btn-select-customer-penawaran, .customer-penawaran-card', function(e) {
                if ($(e.target).closest('button').length && !$(e.target).closest('.btn-select-customer-penawaran').length) return;
                let card = $(this).closest('.customer-penawaran-card');
                let id = card.data('id');
                let name = card.data('name');

                $('#selectCustomer').val(id).trigger('change');
                $('#displayCustomer').val(name);
                $('#modalCariCustomerPenawaran').modal('hide');
            });

            // ===== MODAL LOOKUP TENDER PENAWARAN =====
            $('#btnOpenLookupTender').on('click', function() {
                $('#modalCariTenderPenawaran').modal('show');
                $('#search_tender_penawaran_input').val('');
                $('#filter_chips_tender_penawaran .filter-chip-tender').removeClass('btn-success active').addClass('btn-outline-secondary');
                $('#filter_chips_tender_penawaran .filter-chip-tender[data-filter="all"]').removeClass('btn-outline-secondary').addClass('btn-success active');
                filterTenderTable();
                setTimeout(() => $('#search_tender_penawaran_input').focus(), 300);
            });

            function filterTenderTable() {
                let term = $('#search_tender_penawaran_input').val().toLowerCase().trim();
                let activeFilter = $('#filter_chips_tender_penawaran .filter-chip-tender.active').data('filter') || 'all';
                $('#btn_clear_search_tender_penawaran').toggleClass('d-none', !term);
                
                let visibleCount = 0;
                $('#tender_penawaran_lookup_container tr.tender-penawaran-card').each(function() {
                    let searchStr = (String($(this).attr('data-search') || '')).toLowerCase();
                    let status = (String($(this).attr('data-status') || '')).toLowerCase();

                    let matchSearch = !term || searchStr.indexOf(term) > -1;
                    let matchCategory = (activeFilter === 'all') || (status.indexOf(activeFilter) > -1);

                    if (matchSearch && matchCategory) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });
                $('#tender_penawaran_lookup_empty').toggleClass('d-none', visibleCount > 0);
            }

            $(document).on('input keyup search paste', '#search_tender_penawaran_input', function() {
                filterTenderTable();
            });

            $(document).on('click', '.filter-chip-tender', function() {
                $('#filter_chips_tender_penawaran .filter-chip-tender').removeClass('btn-success active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-success active');
                filterTenderTable();
            });

            $('#btn_clear_search_tender_penawaran').on('click', function() {
                $('#search_tender_penawaran_input').val('');
                filterTenderTable();
                $('#search_tender_penawaran_input').focus();
            });

            $(document).on('click', '.btn-select-tender-penawaran, .tender-penawaran-card', function(e) {
                if ($(e.target).closest('button').length && !$(e.target).closest('.btn-select-tender-penawaran').length) return;
                let card = $(this).closest('.tender-penawaran-card');
                let id = card.data('id');
                let nama = card.data('nama');
                let nomor = card.data('nomor');
                let pagu = parseFloat(card.data('pagu')) || 0;
                let customerId = card.data('customer-id');
                let customerName = card.data('customer-name');

                $('#selectTender').val(id).data('pagu', pagu).attr('data-pagu', pagu);
                $('#displayTender').val(nomor + ' - ' + nama);
                $('#displayTenderPagu').text('Rp ' + formatRupiah(pagu));
                $('#tenderPaguInfoBox').removeClass('d-none');
                $('#btnClearTender').removeClass('d-none');

                if (customerId) {
                    $('#selectCustomer').val(customerId).trigger('change');
                    $('#displayCustomer').val(customerName);
                }

                // Process Tender Map Data
                let tData = window._tendersMap ? window._tendersMap[id] : null;
                if (tData) {
                    if (tData.sales_id && $('select[name="sales_id"]').length) {
                        $('select[name="sales_id"]').val(tData.sales_id).trigger('change');
                    }
                    let perihalInput = $('input[name="perihal"]');
                    if (perihalInput.length && (!perihalInput.val() || perihalInput.val().startsWith('Penawaran '))) {
                        perihalInput.val('Penawaran ' + tData.nama_tender);
                    }
                    if (Array.isArray(tData.items) && tData.items.length > 0) {
                        $('#itemsTableBody').empty();
                        $('#emptyItemNotice').hide();
                        tData.items.forEach(function(item) {
                            addItemRow(item);
                        });
                        calculateTotal();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: `${tData.items.length} item dari tender berhasil dimuat!`,
                                showConfirmButton: false,
                                timer: 2500
                            });
                        }
                    }
                } else {
                    calculateTotal();
                }

                $('#modalCariTenderPenawaran').modal('hide');
            });

            $('#btnClearTender').on('click', function() {
                $('#selectTender').val('').data('pagu', 0).attr('data-pagu', 0);
                $('#displayTender').val('');
                $('#tenderPaguInfoBox').addClass('d-none');
                $(this).addClass('d-none');
                calculateTotal();
            });

            // DP Type Toggle
            $('#selectTipeDp').on('change', function() {
                let val = $(this).val();
                if (val === 'Persen') {
                    $('#inputDpPersen').show();
                    $('#inputDpNominal').hide();
                } else if (val === 'Nominal') {
                    $('#inputDpPersen').hide();
                    $('#inputDpNominal').show();
                } else {
                    $('#inputDpPersen').hide();
                    $('#inputDpNominal').hide();
                }
            });

            function calculateTotal() {
                let subtotal = 0;
                let totalHpp = 0;

                $('.item-calc-row').each(function() {
                    let qty = parseFloat($(this).find('.item-qty').val()) || 0;
                    let price = parseRupiah($(this).find('.item-price').val());
                    let discPersen = parseFloat($(this).find('.item-disc-persen').val()) || 0;
                    let hppSatuan = parseFloat($(this).find('.item-hpp-satuan').val()) || 0;

                    let gross = qty * price;
                    let discNominal = (gross * (discPersen / 100));
                    let itemSubtotal = Math.max(0, gross - discNominal);

                    $(this).find('.item-subtotal-display').text('Rp ' + itemSubtotal.toLocaleString('id-ID'));

                    subtotal += itemSubtotal;
                    totalHpp += (qty * hppSatuan);
                });

                $('#displaySubtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
                $('#inputSubtotal').val(subtotal);

                // Global Discount
                let discGlobalPersen = parseFloat($('#inputDiskonPersen').val()) || 0;
                let discGlobalNominal = subtotal * (discGlobalPersen / 100);
                $('#displayDiskonNominal').text('- Rp ' + Math.round(discGlobalNominal).toLocaleString('id-ID'));
                $('#inputDiskonNominal').val(discGlobalNominal);

                let dpp = Math.max(0, subtotal - discGlobalNominal);

                // PPN
                let tipePpn = $('#selectTipePpn').val();
                let tarifPpn = parseFloat($('#inputTarifPpn').val()) || 0;
                let nilaiPpn = 0;
                let grandTotal = 0;

                let shipping = parseRupiah($('#inputBiayaPengiriman').val());

                if (tipePpn === 'Exclude') {
                    nilaiPpn = dpp * (tarifPpn / 100);
                    grandTotal = dpp + nilaiPpn + shipping;
                } else if (tipePpn === 'Include') {
                    nilaiPpn = (tarifPpn > 0) ? (dpp - (dpp / (1 + (tarifPpn / 100)))) : 0;
                    grandTotal = dpp + shipping;
                } else {
                    nilaiPpn = 0;
                    grandTotal = dpp + shipping;
                }

                $('#displayNilaiPpn').text('+ Rp ' + Math.round(nilaiPpn).toLocaleString('id-ID'));
                $('#inputNilaiPpn').val(nilaiPpn);

                // Grand Total
                $('#displayGrandTotal').text('Rp ' + Math.round(grandTotal).toLocaleString('id-ID'));
                $('#inputGrandTotal').val(grandTotal);

                // Tender Pagu Comparison Indicator
                let tenderPagu = parseFloat($('#selectTender').data('pagu')) || parseFloat($('#selectTender').attr('data-pagu')) || 0;
                if ($('#selectTender').val() && tenderPagu > 0) {
                    $('#tenderPaguInfoBox').removeClass('d-none');
                    $('#displayTenderPagu').text('Rp ' + formatRupiah(tenderPagu));
                    if (grandTotal <= tenderPagu) {
                        let sisa = tenderPagu - grandTotal;
                        $('#badgeBudgetStatus').html(`
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.72rem;" title="Harga penawaran masih dalam batas nilai proyek">
                                <i class="bi bi-shield-check me-1"></i>Sesuai Nilai Proyek (Sisa Rp ${formatRupiah(sisa)})
                            </span>
                        `);
                    } else {
                        let over = grandTotal - tenderPagu;
                        $('#badgeBudgetStatus').html(`
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace" style="font-size: 0.72rem;" title="Harga penawaran melebihi nilai proyek!">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Melebihi Nilai Proyek (+Rp ${formatRupiah(over)})
                            </span>
                        `);
                    }
                } else {
                    $('#tenderPaguInfoBox').addClass('d-none');
                }

                // HPP & Margin
                $('#displayTotalHpp').text('Rp ' + Math.round(totalHpp).toLocaleString('id-ID'));
                $('#inputTotalHpp').val(totalHpp);

                let labaKotor = grandTotal - totalHpp;
                let labaDisplayEl = $('#displayLabaKotor');
                let marginBadgeEl = $('#displayMarginPersen');

                if (labaKotor < 0) {
                    labaDisplayEl.removeClass('text-success').addClass('text-danger fw-bold').text('- Rp ' + Math.abs(Math.round(labaKotor)).toLocaleString('id-ID') + ' (Rugi)');
                } else {
                    labaDisplayEl.removeClass('text-danger').addClass('text-success fw-bold').text('Rp ' + Math.round(labaKotor).toLocaleString('id-ID'));
                }
                $('#inputLabaKotor').val(labaKotor);

                let margin = grandTotal > 0 ? (labaKotor / grandTotal) * 100 : 0;
                if (margin < 0) {
                    marginBadgeEl.removeClass('badge-soft-emerald').addClass('badge-soft-danger').html('<i class="bi bi-exclamation-triangle me-1"></i>' + margin.toFixed(1) + '% (Minus)');
                } else {
                    marginBadgeEl.removeClass('badge-soft-danger').addClass('badge-soft-emerald').text(margin.toFixed(1) + '%');
                }
                $('#inputMarginPersen').val(margin);
            }

            // Form Submission Validation Guard & Currency Unmasking
            $('#formQuotation').on('submit', function(e) {
                if ($('#itemsTableBody tr.item-calc-row').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Item Masih Kosong',
                        text: 'Silakan tambahkan minimal 1 item barang atau jasa sebelum menyimpan penawaran!',
                        confirmButtonColor: '#4f46e5'
                    });
                    return false;
                }

                // Calculate Total HPP & Grand Total
                let totalHpp = parseFloat($('#inputTotalHpp').val()) || 0;
                let grandTotal = parseFloat($('#inputGrandTotal').val()) || 0;
                let labaKotor = parseFloat($('#inputLabaKotor').val()) || 0;
                let margin = parseFloat($('#inputMarginPersen').val()) || 0;

                // Fallback check directly from table rows
                if (totalHpp === 0) {
                    $('.item-calc-row').each(function() {
                        let qty = parseFloat($(this).find('.item-qty').val()) || 0;
                        let hpp = parseFloat($(this).find('.item-hpp-satuan').val()) || 0;
                        totalHpp += (qty * hpp);
                    });
                }

                // Guard: Minus Margin Validation (Block Save & Draft)
                if (totalHpp > 0 && (grandTotal < totalHpp || labaKotor < 0 || margin < 0)) {
                    e.preventDefault();
                    let defisit = Math.abs(labaKotor || (grandTotal - totalHpp));
                    Swal.fire({
                        icon: 'error',
                        title: '<span class="text-danger fw-bold">Peringatan: Margin Negatif!</span>',
                        html: `
                            <div class="text-start fs-8">
                                <div class="alert alert-danger border-0 p-2.5 rounded-3 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0"></i>
                                        <div class="fw-semibold">Penawaran harga ini menghasilkan potensi kerugian operasional dan tidak dapat disimpan (termasuk sebagai Draft).</div>
                                    </div>
                                </div>
                                <div class="bg-body-tertiary p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between mb-1.5">
                                        <span class="text-muted">Total HPP Modal:</span>
                                        <strong class="font-monospace text-body">Rp ${formatRupiah(totalHpp)}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1.5">
                                        <span class="text-muted">Total Penawaran Jual:</span>
                                        <strong class="font-monospace text-body">Rp ${formatRupiah(grandTotal)}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between pt-2 border-top border-secondary-subtle">
                                        <span class="text-danger fw-bold">Estimasi Defisit / Rugi:</span>
                                        <strong class="text-danger font-monospace fw-bold">- Rp ${formatRupiah(defisit)} (${margin.toFixed(1)}%)</strong>
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small"><i class="bi bi-info-circle text-primary me-1"></i>Sesuai kebijakan keuangan perusahaan, harga penawaran tidak boleh berada di bawah total modal pokok (HPP). Silakan sesuaikan kembali <strong>harga jual</strong> atau <strong>potongan diskon</strong>.</p>
                            </div>
                        `,
                        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Tinjau Kembali Harga',
                        confirmButtonColor: '#e11d48',
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                    return false;
                }

                // Strip thousand dots from prices and extra cost before sending
                $('.item-price').each(function() {
                    let rawPrice = parseRupiah($(this).val());
                    $(this).val(rawPrice);
                });

                let rawShipping = parseRupiah($('#inputBiayaPengiriman').val());
                $('#inputBiayaPengiriman').val(rawShipping);

                let rawDp = parseRupiah($('#inputDpNominal').val());
                $('#inputDpNominal').val(rawDp);
            });

            // Handle submit button click for status assignment (Draft vs Sent)
            $(document).on('click', '.btn-submit-quotation', function() {
                const val = $(this).val();
                if (val) {
                    $('#inputQuotationStatus').val(val);
                }
            });

            // Run initial calculation if items prefilled
            calculateTotal();
        });
    </script>
    @endpush

</x-layouts>
