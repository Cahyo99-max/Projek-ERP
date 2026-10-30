<x-layouts>

<x-slot:title>Alokasi Biaya Pembelian (Landed Cost)</x-slot:title>

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    :root {
        --erp-bg: #f8fafc;
        --erp-card-bg: #ffffff;
        --erp-border: #e2e8f0;
        --erp-table-header: #f8fafc;
        --erp-table-hover: #f1f5f9;
        --erp-text-main: #0f172a;
        --erp-text-muted: #64748b;
    }

    [data-bs-theme="dark"] {
        --erp-bg: #0b1120;
        --erp-card-bg: #111827;
        --erp-border: rgba(255, 255, 255, 0.08);
        --erp-table-header: rgba(30, 41, 59, 0.7);
        --erp-table-hover: rgba(30, 41, 59, 0.5);
        --erp-text-main: #f8fafc;
        --erp-text-muted: #94a3b8;
    }

    .erp-card {
        background-color: var(--erp-card-bg) !important;
        border: 1px solid var(--erp-border) !important;
        border-radius: 0.85rem;
    }

    /* ===== CINEMATIC BENTO GLOW CARDS ===== */
    .inv-cinematic-card {
        border-radius: 1.15rem;
        padding: 1.35rem 1.45rem;
        position: relative;
        overflow: hidden;
        border: none !important;
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1;
    }
    .inv-cinematic-card:hover {
        transform: translateY(-3px);
    }
    .inv-cine-blue {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4), 0 8px 10px -6px rgba(37, 99, 235, 0.2);
    }
    .inv-cine-amber {
        background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);
        box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.4), 0 8px 10px -6px rgba(217, 119, 6, 0.2);
    }
    .inv-cine-emerald {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);
        box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.4), 0 8px 10px -6px rgba(5, 150, 105, 0.2);
    }
    .inv-cine-purple {
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 50%, #8b5cf6 100%);
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.4), 0 8px 10px -6px rgba(124, 58, 237, 0.2);
    }
    .inv-cine-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }
    .inv-cine-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: #ffffff;
        margin-bottom: 0.35rem;
        font-family: system-ui, -apple-system, sans-serif;
    }
    .inv-cine-sub {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.85);
    }
    .inv-cine-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .btn-check:checked + .filter-status-pill {
        border-width: 2px !important;
        font-weight: 700 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* ===== MODERN EXECUTIVE ACTION BUTTONS ===== */
    .action-btn-group {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        background: rgba(148, 163, 184, 0.08);
        padding: 4px 6px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.16);
    }

    [data-bs-theme="dark"] .action-btn-group {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        position: relative;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .action-btn:active {
        transform: translateY(0);
    }

    /* Action Button Variants */
    /* 1. View Detail - Sleek Indigo / Blue */
    .action-btn-view {
        background: rgba(59, 130, 246, 0.12);
        color: #3b82f6;
        border-color: rgba(59, 130, 246, 0.25);
    }
    .action-btn-view:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    }

    /* 2. Edit - Rich Amber / Gold */
    .action-btn-edit {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
        border-color: rgba(245, 158, 11, 0.25);
    }
    .action-btn-edit:hover {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);
    }

    /* 3. Post - Emerald Green */
    .action-btn-post {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.25);
    }
    .action-btn-post:hover {
        background: #059669;
        color: #ffffff;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
    }

    /* 4. Jurnal - Cyan / Purple */
    .action-btn-jurnal {
        background: rgba(6, 182, 212, 0.12);
        color: #06b6d4;
        border-color: rgba(6, 182, 212, 0.25);
    }
    .action-btn-jurnal:hover {
        background: #0891b2;
        color: #ffffff;
        border-color: #0891b2;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.4);
    }

    /* 5. Cancel / Void - Rose / Coral Red */
    .action-btn-cancel {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.25);
    }
    .action-btn-cancel:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }

    /* 6. Delete - Crimson / Ruby Red */
    .action-btn-delete {
        background: rgba(225, 29, 72, 0.12);
        color: #e11d48;
        border-color: rgba(225, 29, 72, 0.25);
    }
    .action-btn-delete:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.4);
    }

    /* ===== MODERN TABLE DESIGN ===== */
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-modern thead th {
        background-color: var(--erp-table-header) !important;
        color: var(--erp-text-muted);
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid var(--erp-border);
        padding: 0.9rem 1rem;
    }
    .table-modern tbody tr {
        transition: background-color 0.18s ease;
    }
    .table-modern tbody tr:hover {
        background-color: var(--erp-table-hover) !important;
    }
    .table-modern tbody td {
        padding: 0.95rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--erp-border);
    }

    /* Document Number Link */
    .doc-number-link {
        color: #3b82f6;
        font-weight: 700;
        text-decoration: none;
        font-family: monospace, SFMono-Regular, Consolas;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.18s ease;
    }
    .doc-number-link:hover {
        color: #60a5fa;
        text-decoration: underline;
        transform: translateX(2px);
    }

    /* Live Pulsing Status Dot */
    .status-pulse-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        animation: status-pulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes status-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.35; transform: scale(0.85); }
    }

    /* Quick Search Input Styling */
    .search-input-group {
        border-radius: 0.75rem;
        border: 1px solid var(--erp-border);
        background-color: var(--erp-card-bg);
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .search-input-group:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
    }
    .search-input-group .form-control:focus {
        box-shadow: none;
    }

    /* Responsive Breakpoints */
    @media (max-width: 767.98px) {
        .inv-cinematic-card {
            padding: 1.1rem 1.15rem;
        }
        .inv-cine-value {
            font-size: 1.25rem;
        }
        .inv-cine-icon {
            width: 2.35rem;
            height: 2.35rem;
            font-size: 1.1rem;
        }
    }

    /* Modal Dialog Lockdown - Anti Geser Horizontal pada Modal */
    .modal-dialog-alokasi {
        max-width: 95vw;
        width: 95vw;
        margin: 1.25rem auto;
        min-width: 0 !important;
    }

    #modalCreateAlokasi,
    #modalViewAlokasi {
        overflow-x: hidden !important;
    }

    #modalCreateAlokasi .modal-dialog,
    #modalViewAlokasi .modal-dialog {
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    #modalCreateAlokasi .modal-content,
    #modalViewAlokasi .modal-content {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    #modalCreateAlokasi .modal-body,
    #modalViewAlokasi .modal-body {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
        touch-action: pan-y;
        overscroll-behavior-x: none;
    }

    #modalCreateAlokasi form,
    #modalViewAlokasi form {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
        overflow-x: hidden !important;
    }

    #modalCreateAlokasi .card,
    #modalViewAlokasi .card {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    #modalCreateAlokasi .card-body,
    #modalViewAlokasi .card-body {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    /* Khusus kontainer table-responsive: Izinkan swipe/geser horizontal */
    #modalCreateAlokasi .table-responsive,
    #modalViewAlokasi .table-responsive,
    #modalCreateAlokasi .card .table-responsive,
    #modalViewAlokasi .card .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        overflow-x: auto !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-x pan-y !important;
        overscroll-behavior-x: contain !important;
        display: block !important;
    }

    @media (max-width: 991.98px) {
        .modal-dialog-alokasi {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            min-height: 100vh;
            min-width: 0 !important;
        }
        .modal-dialog-alokasi .modal-content {
            border-radius: 0 !important;
            min-height: 100vh;
            max-width: 100% !important;
            width: 100% !important;
        }
    }

    /* Modal Step Number Badge */
    .step-num-badge {
        width: 28px;
        height: 28px;
        min-width: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        flex-shrink: 0 !important;
        margin-top: 1px;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
    }
</style>
@endpush

<div class="container-fluid px-3 px-md-4 py-4">

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill fw-semibold">
                    <i class="bi bi-calculator-fill me-1"></i> Landed Cost Engine
                </span>
                <span class="text-secondary small">&bull; Modul Pembelian &amp; Persediaan</span>
            </div>
            <h3 class="fw-bold mt-1 mb-0" style="color: var(--erp-text-main);">Alokasi Biaya Pembelian</h3>
            <p class="text-muted small mb-0">Kapitalisasi biaya jasa pengiriman / ekspedisi ke dalam nilai perolehan HPP stok barang</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @can('tambah-alokasi-biaya')
            <button type="button" class="btn btn-primary px-3.5 py-2 shadow-sm rounded-3 fw-semibold" id="btnTambahAlokasi">
                <i class="bi bi-plus-circle-fill me-1.5"></i> Buat Alokasi Biaya
            </button>
            @endcan
        </div>
    </div>

    <!-- Periode Indicator & Bento KPI Cards (Bulan Berjalan) -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                  style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25); font-size: 0.76rem;">
                <i class="bi bi-calendar3"></i> Ringkasan Bulan Berjalan: <span class="fw-bold">{{ $namaBulanBerjalan ?? now()->translatedFormat('F Y') }}</span>
            </span>
        </div>
    </div>

    <!-- Bento KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="inv-cinematic-card inv-cine-blue d-flex align-items-center justify-content-between">
                <div>
                    <div class="inv-cine-title mb-1">Total Dokumen</div>
                    <div class="inv-cine-value">{{ number_format($totalAlokasi, 0, ',', '.') }}</div>
                    <div class="inv-cine-sub"><i class="bi bi-calendar-check me-1"></i> Bulan Ini ({{ $namaBulanBerjalan ?? now()->translatedFormat('M Y') }})</div>
                </div>
                <div class="inv-cine-icon">
                    <i class="bi bi-calculator"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="inv-cinematic-card inv-cine-amber d-flex align-items-center justify-content-between">
                <div>
                    <div class="inv-cine-title mb-1">Status Draft</div>
                    <div class="inv-cine-value">{{ number_format($totalDraft, 0, ',', '.') }}</div>
                    <div class="inv-cine-sub"><i class="bi bi-pencil-square me-1"></i> {{ !empty($isManagerial) ? 'Draft Pending' : 'Draft Anda' }} &bull; Batal ({{ number_format($totalBatal ?? 0, 0, ',', '.') }})</div>
                </div>
                <div class="inv-cine-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="inv-cinematic-card inv-cine-emerald d-flex align-items-center justify-content-between">
                <div>
                    <div class="inv-cine-title mb-1">Status Posted</div>
                    <div class="inv-cine-value">{{ number_format($totalPosted, 0, ',', '.') }}</div>
                    <div class="inv-cine-sub"><i class="bi bi-check-circle-fill me-1"></i> Masuk HPP Bulan Ini</div>
                </div>
                <div class="inv-cine-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="inv-cinematic-card inv-cine-purple d-flex align-items-center justify-content-between">
                <div>
                    <div class="inv-cine-title mb-1">Total Biaya Masuk HPP</div>
                    <div class="inv-cine-value" style="font-size: 1.25rem;">Rp {{ number_format($totalNominal, 2, ',', '.') }}</div>
                    <div class="inv-cine-sub"><i class="bi bi-truck me-1"></i> Kapitalisasi Bulan Ini</div>
                </div>
                <div class="inv-cine-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Compact Filter Toolbar -->
    @php
        $activeFiltersCount = 0;
        if (request()->filled('status')) $activeFiltersCount++;
        if (request()->filled('metode')) $activeFiltersCount++;
        if (request()->filled('start_date') || request()->filled('end_date')) $activeFiltersCount++;
    @endphp

    <div class="card erp-card mb-4 shadow-sm border-0">
        <div class="card-body p-2.5 p-md-3">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2.5">
                
                <!-- Quick Search Input Form -->
                <form action="{{ route('alokasi-biaya.index') }}" method="GET" class="flex-grow-1" id="formQuickSearch">
                    <!-- Carry forward filter params if present -->
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    @if(request('metode')) <input type="hidden" name="metode" value="{{ request('metode') }}"> @endif
                    @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                    @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                    @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif

                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="quickSearchInput" class="form-control border-0 bg-transparent py-2" 
                               placeholder="Cari No Alokasi, PO Ekspedisi, Vendor, Proyek Tender..." 
                               value="{{ request('search') }}" autocomplete="off">
                        @if(request()->filled('search'))
                            <a href="{{ route('alokasi-biaya.index', request()->except('search')) }}" class="input-group-text bg-transparent border-0 text-muted" title="Hapus Pencarian">
                                <i class="bi bi-x-circle-fill text-secondary"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3.5 fw-semibold my-1 me-1 rounded-2 shadow-sm">
                            Cari
                        </button>
                    </div>
                </form>

                <!-- Filter Modal Trigger, Per-Page Selector & Reset Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Dropdown Tampilan Baris (10, 25, 50, 100) -->
                    <div class="d-flex align-items-center bg-dark bg-opacity-25 px-2.5 py-1.5 rounded-3 border" style="border-color: var(--erp-border) !important;">
                        <i class="bi bi-list-ol text-muted me-1.5 small"></i>
                        <span class="small text-muted me-1.5 d-none d-sm-inline" style="font-size: 0.8rem;">Baris:</span>
                        <select class="form-select form-select-sm border-0 bg-transparent fw-semibold select-per-page p-0 pe-4" style="width: auto; font-size: 0.82rem; color: var(--erp-text); cursor: pointer;" title="Tampilkan Jumlah Baris Per Halaman">
                            <option value="10" class="bg-dark text-white" {{ (int)request('per_page', 10) === 10 ? 'selected' : '' }}>10</option>
                            <option value="25" class="bg-dark text-white" {{ (int)request('per_page', 10) === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" class="bg-dark text-white" {{ (int)request('per_page', 10) === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" class="bg-dark text-white" {{ (int)request('per_page', 10) === 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <button type="button" class="btn {{ $activeFiltersCount > 0 ? 'btn-primary' : 'btn-outline-primary' }} px-3.5 py-2 rounded-3 fw-semibold d-flex align-items-center gap-2 shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#modalFilterAlokasi" id="btnOpenFilterModal">
                        <i class="bi bi-sliders fs-6"></i>
                        <span>Filter</span>
                        @if($activeFiltersCount > 0)
                            <span class="badge bg-white text-primary rounded-pill px-2 fw-bold" style="font-size: 0.72rem;">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>

                    @if(request()->hasAny(['search', 'status', 'metode', 'start_date', 'end_date']) || (request()->has('per_page') && request('per_page') != 10))
                        <a href="{{ route('alokasi-biaya.index') }}" class="btn btn-outline-danger px-2.5 py-2 rounded-3 fw-semibold" title="Reset Semua Filter, Pencarian &amp; Baris">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>

            </div>

            <!-- Active Filters Chip Pills -->
            @if(request()->hasAny(['status', 'metode', 'start_date', 'end_date']))
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2.5 pt-2 border-top" style="border-color: var(--erp-border) !important;">
                    <span class="small text-muted me-1"><i class="bi bi-funnel me-0.5"></i> Filter aktif:</span>
                    
                    @if(request('status'))
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle py-1 px-2.5 rounded-pill d-inline-flex align-items-center gap-1.5 font-monospace">
                            Status: <b>{{ request('status') }}</b>
                            <a href="{{ route('alokasi-biaya.index', request()->except('status')) }}" class="text-primary text-decoration-none fw-bold ms-1">&times;</a>
                        </span>
                    @endif

                    @if(request('metode'))
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle py-1 px-2.5 rounded-pill d-inline-flex align-items-center gap-1.5">
                            Metode: <b>{{ request('metode') }}</b>
                            <a href="{{ route('alokasi-biaya.index', request()->except('metode')) }}" class="text-info text-decoration-none fw-bold ms-1">&times;</a>
                        </span>
                    @endif

                    @if(request('start_date') || request('end_date'))
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle py-1 px-2.5 rounded-pill d-inline-flex align-items-center gap-1.5 font-monospace">
                            <i class="bi bi-calendar3 me-0.5"></i>
                            {{ request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : 'Awal' }} &ndash; {{ request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : 'Sekarang' }}
                            <a href="{{ route('alokasi-biaya.index', request()->except(['start_date', 'end_date'])) }}" class="text-secondary text-decoration-none fw-bold ms-1">&times;</a>
                        </span>
                    @endif

                    <a href="{{ route('alokasi-biaya.index', request()->only('search')) }}" class="small text-danger text-decoration-none ms-auto fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Hapus Semua Filter
                    </a>
                </div>
            @endif

        </div>
    </div>

    <!-- Data Table -->
    <div class="card erp-card shadow-sm border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-modern table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 py-3 text-nowrap">No. Alokasi</th>
                        <th class="text-nowrap">Tanggal</th>
                        <th class="text-nowrap">PO Jasa Ekspedisi</th>
                        <th class="text-nowrap">Vendor Ekspedisi</th>
                        <th class="text-end text-nowrap">Total Biaya Kirim</th>
                        <th class="text-center text-nowrap">Status</th>
                        <th class="text-end pe-3 text-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alokasiList as $item)
                        <tr>
                            <td class="ps-3 py-3">
                                <div>
                                    <a href="javascript:void(0)" class="doc-number-link btn-view" data-id="{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Klik untuk lihat rincian alokasi">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <span>{{ $item->nomor_alokasi }}</span>
                                    </a>
                                </div>
                                <div class="text-muted small mt-0.5">
                                    <i class="bi bi-box-seam me-1"></i>{{ $item->details->count() }} item barang dialokasikan
                                </div>
                                @php
                                    $t = $item->tender ?? $item->poBiaya?->tender;
                                @endphp
                                @if($t)
                                    <div class="mt-1">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 rounded-pill small" title="{{ $t->nama_tender }}">
                                            <i class="bi bi-briefcase me-1"></i>{{ !empty($t->nomor_kontrak) && $t->nomor_kontrak !== '-' ? $t->nomor_kontrak : ($t->nama_tender ?: ('Tender #' . $t->id)) }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="fw-medium" style="color: var(--erp-text-main);">
                                    <i class="bi bi-calendar-event me-1 text-muted"></i>{{ $item->tanggal_alokasi ? $item->tanggal_alokasi->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                @if($item->poBiaya)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle font-monospace px-2.5 py-1">
                                        <i class="bi bi-receipt me-1"></i>{{ $item->poBiaya->nomor_po }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="color: var(--erp-text-main); font-size: 0.9rem; max-width: 210px;" title="{{ $item->poBiaya?->supplier?->nama_supplier ?? 'Vendor Ekspedisi' }}">
                                    <i class="bi bi-building me-1 text-secondary opacity-75"></i>{{ $item->poBiaya?->supplier?->nama_supplier ?? 'Vendor Ekspedisi' }}
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="fw-bold text-success font-monospace" style="font-size: 0.95rem;">
                                    Rp {{ number_format($item->total_biaya, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="text-center text-nowrap">
                                @if($item->status === 'Posted')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5">
                                        <i class="bi bi-check-circle-fill"></i> Posted
                                    </span>
                                @elseif($item->status === 'Batal')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5" @if($item->alasan_batal) data-bs-toggle="tooltip" data-bs-placement="top" title="Alasan: {{ $item->alasan_batal }}" @endif>
                                        <i class="bi bi-x-circle-fill"></i> Batal
                                    </span>
                                    @if($item->alasan_batal)
                                        <div class="text-danger small fst-italic mt-0.5 text-truncate" style="font-size: 0.72rem; max-width: 140px; margin: 0 auto;" title="{{ $item->alasan_batal }}">
                                            {{ $item->alasan_batal }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill fw-semibold d-inline-flex align-items-center gap-1.5">
                                        <span class="status-pulse-dot bg-warning"></span> Draft
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <div class="action-btn-group">
                                    @can('detail-alokasi-biaya')
                                    <button type="button" class="action-btn action-btn-view btn-view" data-id="{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Rincian Alokasi">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @endcan
                                    @if($item->status === 'Draft' && ($item->created_by == auth()->id() || auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance'])))
                                        @if(auth()->user()->can('edit-alokasi-biaya') || auth()->user()->can('tambah-alokasi-biaya'))
                                        <button type="button" class="action-btn action-btn-edit btn-edit" data-id="{{ $item->id }}" data-nomor="{{ $item->nomor_alokasi }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Alokasi Biaya (Draft)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @can('posting-alokasi-biaya')
                                        <button type="button" class="action-btn action-btn-post btn-post" data-id="{{ $item->id }}" data-nomor="{{ $item->nomor_alokasi }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Posting ke Stok &amp; Jurnal">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        @endcan
                                        @if(auth()->user()->can('hapus-alokasi-biaya') || auth()->user()->can('batalkan-alokasi-biaya'))
                                        <button type="button" class="action-btn action-btn-delete btn-delete" data-id="{{ $item->id }}" data-nomor="{{ $item->nomor_alokasi }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Dokumen Draft">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                        @endif
                                    @elseif($item->status === 'Posted')
                                        @can('lihat-jurnal-alokasi-biaya')
                                        <button type="button" class="action-btn action-btn-jurnal btn-open-jurnal" data-id="{{ $item->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Jurnal Akuntansi">
                                            <i class="bi bi-journal-text"></i>
                                        </button>
                                        @endcan
                                        @can('batalkan-alokasi-biaya')
                                        <button type="button" class="action-btn action-btn-cancel btn-cancel" data-id="{{ $item->id }}" data-nomor="{{ $item->nomor_alokasi }}" data-status="Posted" data-bs-toggle="tooltip" data-bs-placement="top" title="Batalkan / Void Alokasi">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <div class="mb-3">
                                        <div class="d-inline-flex p-3 rounded-circle bg-secondary bg-opacity-10 text-secondary">
                                            <i class="bi bi-inbox fs-1"></i>
                                        </div>
                                    </div>
                                    <h6 class="fw-semibold text-secondary mb-1">Belum Ada Data Alokasi Biaya</h6>
                                    <p class="small text-muted mb-3">Kapitalisasi biaya jasa ekspedisi ke dalam persediaan HPP barang sekarang.</p>
                                    @can('tambah-alokasi-biaya')
                                    <button type="button" class="btn btn-sm btn-primary px-3 py-1.5 rounded-pill shadow-sm fw-semibold" onclick="$('#btnTambahAlokasi').click();">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Alokasi Pertama
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Table Footer: Data Count & Pagination -->
        <div class="p-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" style="border-color: var(--erp-border) !important;">
            <div class="text-muted small">
                Menampilkan 
                <span class="fw-semibold text-white">{{ $alokasiList->total() > 0 ? $alokasiList->firstItem() : 0 }}</span> 
                sampai 
                <span class="fw-semibold text-white">{{ $alokasiList->total() > 0 ? $alokasiList->lastItem() : 0 }}</span> 
                dari 
                <span class="fw-semibold text-white">{{ $alokasiList->total() }}</span> dokumen
            </div>
            @if($alokasiList->hasPages())
                <div>
                    {{ $alokasiList->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modals Container Outside Main Stacking Context -->
@push('modals')
@include('modul_purchasing.alokasi_biaya.partials.modal_create')
@include('modul_purchasing.alokasi_biaya.partials.modal_view')
@include('modul_purchasing.alokasi_biaya.partials.modal_jurnal')
@include('modul_purchasing.alokasi_biaya.partials.modal_filter')
@endpush

@push('scripts')
<!-- Select2 & SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('modul_purchasing.alokasi_biaya.scripts.alokasi_js')
@endpush

</x-layouts>
