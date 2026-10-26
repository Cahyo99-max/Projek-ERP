<x-layouts title="Master Tahapan Produksi - Konfigurasi Alur Kerja Garmen">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<style>
    .img-thumbnail-tahapan {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .img-thumbnail-tahapan:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35) !important;
    }
    .tahapan-upload-box {
        border: 2px dashed rgba(59, 130, 246, 0.4);
        background: rgba(59, 130, 246, 0.04);
        border-radius: 14px;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .tahapan-upload-box:hover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.08);
    }

    /* ── SMOOTH HORIZONTAL SCROLL FOR TABLE ── */
    .spk-table-scroll-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(37, 99, 235, 0.4) transparent;
        border: 1px solid var(--spk-border-color, #e2e8f0);
        border-radius: 0.75rem;
    }
    .spk-table thead th {
        background-color: var(--spk-table-head-bg, #f1f5f9) !important;
        color: var(--spk-table-head-text, #1e293b) !important;
        border-bottom: 2px solid var(--spk-border-color, #e2e8f0) !important;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: 0.5px;
    }
    .spk-table tbody td {
        background-color: var(--spk-table-row-bg, #ffffff) !important;
        color: var(--spk-text-main, #0f172a) !important;
        border-bottom: 1px solid var(--spk-border-color, #e2e8f0) !important;
    }
    .spk-table tbody tr:hover td {
        background-color: var(--spk-table-hover-bg, #f0f9ff) !important;
    }
    .spk-table-scroll-container::-webkit-scrollbar {
        height: 6px;
    }
    .spk-table-scroll-container::-webkit-scrollbar-thumb {
        background: rgba(37, 99, 235, 0.4);
        border-radius: 4px;
    }
    .spk-table-scroll-container::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Controls styling */
    @media (max-width: 767.98px) {
        .spk-hero-actions {
            width: 100%;
            display: flex;
            gap: 0.5rem;
        }
        .spk-hero-actions > * {
            flex: 1;
            text-align: center;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 0.5rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            display: block;
            margin-top: 0.25rem;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            float: none !important;
            text-align: center !important;
            margin-top: 0.75rem;
        }
        .dataTables_wrapper .dataTables_paginate ul.pagination {
            justify-content: center !important;
        }
    }

    /* ── TAHAPAN INFO NOTE (RESPONSIVE CHIP / BANNER) ── */
    .tahapan-info-note {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        background: rgba(37, 99, 235, 0.08);
        border: 1px solid rgba(37, 99, 235, 0.22);
        color: var(--spk-accent, #2563eb);
        border-radius: 0.75rem;
        padding: 0.55rem 0.85rem;
        font-size: 11.5px;
        line-height: 1.45;
        font-weight: 500;
        width: 100%;
        box-sizing: border-box;
    }
    .tahapan-info-note i {
        font-size: 14px;
        margin-top: 1px;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .tahapan-info-note {
            display: inline-flex;
            align-items: center;
            width: auto;
            border-radius: 50rem;
            padding: 0.4rem 1rem;
            font-size: 12px;
        }
        .tahapan-info-note i {
            margin-top: 0;
        }
    }

    /* ── MODERN FILTER PILL BAR & DROPDOWN ── */
    .filter-toolbar-card {
        padding: 1.15rem 1.35rem !important;
        margin-bottom: 1.75rem !important;
        border: 1px solid var(--spk-border-color, #cbd5e1) !important;
        background: var(--spk-card-bg, #ffffff);
        border-radius: 1.15rem !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03) !important;
    }
    @media (max-width: 767.98px) {
        .filter-toolbar-card {
            padding: 1rem 1.15rem !important;
            margin-bottom: 1.35rem !important;
        }
    }
    .filter-chips-container {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.65rem 0.65rem;
    }
    .filter-pill {
        background: var(--spk-card-bg, #ffffff);
        color: var(--spk-text-main, #334155);
        border: 1px solid var(--spk-border-color, #cbd5e1);
        font-size: 12.5px;
        font-weight: 600;
        padding: 0.45rem 1.05rem !important;
        line-height: 1.45;
        border-radius: 50rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .filter-pill:hover {
        background: rgba(37, 99, 235, 0.07);
        border-color: rgba(37, 99, 235, 0.35);
        color: var(--spk-accent, #2563eb);
        transform: translateY(-1px);
    }
    .filter-pill.active {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28) !important;
    }
    .filter-pill.active i,
    .filter-pill.active span {
        color: #ffffff !important;
    }
    .filter-dropdown-menu {
        min-width: 320px;
        max-width: 400px;
        background: var(--spk-card-bg, #ffffff);
        border: 1px solid var(--spk-border-color, #cbd5e1) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), 0 4px 12px rgba(0, 0, 0, 0.08);
        border-radius: 1rem;
        padding: 0.5rem;
        z-index: 1050;
    }
    .filter-dropdown-item {
        transition: all 0.15s ease;
        border-radius: 0.6rem;
        cursor: pointer;
        border: 1px solid transparent;
    }
    .filter-dropdown-item:hover {
        background: rgba(37, 99, 235, 0.08);
        border-color: rgba(37, 99, 235, 0.2);
    }
    .filter-dropdown-item.active {
        background: rgba(37, 99, 235, 0.14);
        border-color: rgba(37, 99, 235, 0.3);
    }

    /* ── PREVENT ZOOM IN / ZOOM OUT & STABILIZE SCROLL ── */
    html, body, .modal, .modal-dialog, .modal-content, .spk-card {
        touch-action: manipulation !important;
        -webkit-text-size-adjust: 100% !important;
        -moz-text-size-adjust: 100% !important;
        text-size-adjust: 100% !important;
    }

    /* Stabilkan scrolling pada modal-body & cegah card melar */
    .modal-dialog-scrollable .modal-body {
        -webkit-overflow-scrolling: touch !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        overscroll-behavior-y: contain !important;
        touch-action: pan-y !important;
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .modal .spk-card {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        overflow: hidden !important; /* Mencegah card melebar atau ikut tergeser ke samping */
    }

    /* ── STABILISASI SCROLL TABEL BATCH INPUT & EDIT (PC & HP OPTIMIZED) ── */
    .batch-table-scroll-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        border: 1px solid var(--spk-border-color, #e2e8f0);
        border-radius: 0.75rem;
        background: var(--spk-card-bg, #ffffff);
    }

    /* Di Layar HP (<768px): Tabel dikunci 680px fixed & scroll horizontal mulus */
    @media (max-width: 767.98px) {
        .batch-table-scroll-container {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            overscroll-behavior-x: contain !important;
            touch-action: pan-x pan-y;
        }
        .batch-table-scroll-container::-webkit-scrollbar {
            height: 6px;
        }
        .batch-table-scroll-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
            border-radius: 4px;
        }
        .batch-table-scroll-container::-webkit-scrollbar-thumb {
            background: rgba(37, 99, 235, 0.35);
            border-radius: 4px;
        }
        .batch-table-scroll-container table {
            width: 680px !important;
            min-width: 680px !important;
            table-layout: fixed !important;
        }
        .batch-col-no { width: 45px !important; }
        .batch-col-foto { width: 75px !important; }
        .batch-col-nama { width: 250px !important; }
        .batch-col-desk { width: 260px !important; }
        .batch-col-aksi { width: 50px !important; }
    }

    /* Di Layar PC & Tablet (>=768px): Tabel tampil PENUH 100%, proporsional, luas & rapi tanpa ruang kosong */
    @media (min-width: 768px) {
        .batch-table-scroll-container {
            overflow-x: visible !important;
            width: 100% !important;
        }
        .batch-table-scroll-container table {
            width: 100% !important;
            min-width: 100% !important;
            table-layout: auto !important;
        }
        .batch-col-no { width: 55px !important; }
        .batch-col-foto { width: 85px !important; }
        .batch-col-nama { width: 35% !important; }
        .batch-col-desk { width: 45% !important; }
        .batch-col-aksi { width: 65px !important; }
    }

    /* Pastikan input dalam tabel tidak menyebabkan auto-overflow aneh */
    .batch-table-scroll-container input.form-control {
        min-width: 0 !important;
    }

    /* Responsive Footer Buttons */
    @media (max-width: 575.98px) {
        .spk-modal-footer-btns .btn {
            width: 100% !important;
        }
    }
    @media (min-width: 576px) {
        .spk-modal-footer-btns .btn {
            width: auto !important;
        }
    }

    /* Prevent auto-zoom on mobile when tapping inputs/selects inside modals */
    @media (max-width: 767.98px) {
        .modal {
            overflow-x: hidden !important;
        }
        .modal-dialog {
            width: calc(100% - 1rem) !important;
            max-width: calc(100% - 1rem) !important;
            margin: 0.5rem auto !important;
        }
        .modal-content {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .modal-body {
            max-width: 100% !important;
            width: 100% !important;
            overflow-x: hidden !important;
            padding-left: 0.6rem !important;
            padding-right: 0.6rem !important;
        }
        .modal .spk-card {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            overflow: hidden !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            box-sizing: border-box !important;
        }
        .modal input,
        .modal select,
        .modal textarea,
        .modal .form-control,
        .modal .form-select,
        .modal .spk-input,
        #filterBomTahapan {
            font-size: 16px !important;
        }
    }
</style>
@endpush
<div class="container-fluid px-2 px-sm-4 py-2 py-sm-3">

    {{-- ── HEADER ──────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 mb-sm-4 px-1 px-sm-0">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold spk-text-main mb-0">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i>Master Tahapan & Alur Kerja Produksi
                </h5>
                @if(!empty($isCabang))
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" style="background: linear-gradient(135deg, #059669, #10b981); font-size: 11px; border: 1px solid rgba(255,255,255,0.25);">
                        <i class="bi bi-geo-alt-fill me-1"></i>{{ \Illuminate\Support\Str::startsWith($activeBranchName, 'Cabang') ? $activeBranchName : 'Cabang ' . $activeBranchName }}
                    </span>
                @else
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-white shadow-xs" style="background: linear-gradient(135deg, #1e40af, #3b82f6); font-size: 11px; border: 1px solid rgba(255,255,255,0.25);">
                        <i class="bi bi-building me-1"></i>PT Pusat (Head Office)
                    </span>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 spk-hero-actions">
            <a href="{{ route('produksi.spk.index') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold" style="font-size:13px;">
                <i class="bi bi-arrow-left me-1"></i><span class="d-none d-sm-inline">Kembali ke SPK</span><span class="d-inline d-sm-none">SPK</span>
            </a>
            @if(auth()->user()->can('tambah-master-tahapan') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
                <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="bukaTambahTahapan()" style="font-size:13px;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tahapan
                </button>
            @endif
        </div>
    </div>

    {{-- ── MODERN TOOLBAR FILTER BAR (SPACIOUS & BALANCED) ──────────────── --}}
    <div class="spk-card border-0 rounded-4 shadow-sm filter-toolbar-card position-relative overflow-visible">
        <input type="hidden" id="filterBomTahapan" value="" data-nama="" data-kode="" data-barang="">

        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            
            {{-- Left: Modern Segmented Filter Chips --}}
            <div class="filter-chips-container w-100 w-md-auto">
                <div class="d-flex align-items-center gap-1.5 me-1 text-uppercase fw-bold spk-text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                    <span class="badge rounded-circle p-1 d-flex align-items-center justify-content-center text-primary" style="width: 24px; height: 24px; background: rgba(37,99,235,0.1);">
                        <i class="bi bi-funnel-fill" style="font-size: 11px;"></i>
                    </span>
                    <span>Filter:</span>
                </div>

                {{-- Pill 1: Semua Alur (Default Active) --}}
                <button type="button" class="btn filter-pill active shadow-xs"
                        id="btnFilterSemua" onclick="setTahapanFilter('', this, 'Semua Alur Proses')">
                    <i class="bi bi-grid-fill me-1.5"></i>
                    <span>Semua Alur</span>
                </button>

                {{-- Pill 2: Template Standar SPK --}}
                <button type="button" class="btn filter-pill shadow-xs"
                        id="btnFilterGeneral" onclick="setTahapanFilter('general', this, 'Template Umum Standar')">
                    <i class="bi bi-layers-fill text-info me-1.5"></i>
                    <span>Template Umum</span>
                </button>

                {{-- Pill 3: Dropdown Resep BOM Khusus --}}
                <div class="dropdown">
                    <button type="button" class="btn filter-pill shadow-xs dropdown-toggle"
                            id="btnDropdownBomFilter" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="bi bi-diagram-2-fill text-primary me-1.5" id="iconDropdownBom"></i>
                        <span id="lblDropdownBom">Resep BOM Khusus</span>
                        <span class="badge rounded-pill bg-white text-primary fw-bold shadow-xs px-2 py-0.5 ms-1 d-none" id="badgeActiveBomCount" style="font-size: 10px;">Aktif</span>
                    </button>
                    
                    <div class="dropdown-menu filter-dropdown-menu mt-1 animate slideIn" aria-labelledby="btnDropdownBomFilter">
                        <div class="p-2 border-bottom mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold spk-text-main text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="bi bi-diagram-3 me-1 text-primary"></i>Pilih Resep BOM
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                    {{ isset($boms) ? $boms->count() : 0 }} BOM
                                </span>
                            </div>
                            <div class="input-group input-group-sm shadow-xs">
                                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control form-control-sm border-start-0 ps-0" 
                                       id="searchDropdownBomList" placeholder="Cari nama / kode BOM..." 
                                       onclick="event.stopPropagation();"
                                       oninput="filterBomListInDropdown(this.value)">
                            </div>
                        </div>

                        {{-- BOM Item List --}}
                        <div class="px-1" id="containerDropdownBomItems" style="max-height: 240px; overflow-y: auto;">
                            @if(isset($boms) && $boms->count() > 0)
                                @foreach($boms as $b)
                                    @php
                                        $bjNama = $b->barangJadi ? $b->barangJadi->nama_barang : '';
                                        $bjKode = $b->barangJadi ? $b->barangJadi->kode_barang : '';
                                    @endphp
                                    <div class="dropdown-item filter-dropdown-item p-2 mb-1 cursor-pointer" 
                                         data-id="{{ $b->id }}"
                                         data-nama="{{ e($b->nama_bom) }}"
                                         data-kode="{{ e($b->kode_bom) }}"
                                         data-barang="{{ e($bjNama) }}"
                                         onclick="pilihSpecificBomFilter('{{ $b->id }}', '{{ e(addslashes($b->nama_bom)) }}', '{{ e(addslashes($b->kode_bom)) }}', '{{ e(addslashes($bjNama)) }}')">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="text-truncate me-2">
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold text-white shadow-xs" style="background: linear-gradient(135deg, #1d4ed8, #2563eb); font-size: 10px;">
                                                        {{ $b->kode_bom }}
                                                    </span>
                                                    <strong class="spk-text-main small text-truncate">{{ $b->nama_bom }}</strong>
                                                </div>
                                                @if($bjNama)
                                                    <div class="spk-text-muted mt-0.5 text-truncate" style="font-size: 11px;">
                                                        <i class="bi bi-box-seam text-primary me-1"></i>{{ $bjNama }}
                                                    </div>
                                                @endif
                                            </div>
                                            <i class="bi bi-chevron-right text-muted" style="font-size: 11px;"></i>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="emptySearchDropdownBom" class="text-center py-3 spk-text-muted d-none">
                                    <i class="bi bi-search text-secondary d-block mb-1 fs-5"></i>
                                    <small>Tidak ada BOM yang sesuai pencarian.</small>
                                </div>
                            @else
                                <div class="text-center py-4 spk-text-muted">
                                    <i class="bi bi-inbox fs-3 text-secondary d-block mb-1"></i>
                                    <small>Belum ada alur resep BOM.</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Reset Filter Button --}}
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold d-none align-items-center gap-1 shadow-xs" 
                        id="btnResetFilter" onclick="resetFilterTahapan()" style="font-size: 11.5px;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset</span>
                </button>
            </div>

            {{-- Right: Sleek Mode / Status Indicator Pill --}}
            <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1.5 rounded-pill flex-shrink-0"
                 style="background: rgba(37,99,235,0.06); border: 1px solid rgba(37,99,235,0.18); font-size: 11.5px;">
                <span class="badge rounded-circle p-1 d-flex align-items-center justify-content-center text-white bg-primary" style="width: 18px; height: 18px;">
                    <i class="bi bi-check" style="font-size: 11px;"></i>
                </span>
                <span class="spk-text-muted">Menampilkan: <strong class="spk-text-main fw-bold" id="lblActiveFilterName">Semua Alur Proses</strong></span>
            </div>

        </div>
    </div>

    {{-- ── TABLE CARD ──────────────────────────────────────── --}}
    <div class="spk-card border-0 rounded-4 shadow-sm">
        <div class="card-body p-3 p-sm-4">
            
            {{-- Petunjuk Geser di Layar HP --}}
            <div class="d-flex d-md-none align-items-center justify-content-between mb-2 px-1 spk-text-muted small" style="font-size: 11px;">
                <span><i class="bi bi-arrow-left-right text-primary me-1"></i>Geser ke samping untuk melihat seluruh kolom</span>
            </div>

            {{-- TABLE WITH HORIZONTAL SCROLL --}}
            <div class="table-responsive spk-table-scroll-container">
                <table class="table table-hover align-middle mb-0 w-100 spk-table text-nowrap" id="tabelMasterTahapan" style="min-width: 820px;">
                    <thead>
                        <tr class="small text-uppercase fw-bold">
                            <th style="width: 45px;" class="text-center">No</th>
                            <th style="min-width: 250px;">Resep BOM / Target Alur</th>
                            <th style="min-width: 340px;">Alur & Cuplikan Tahapan Proses</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th style="width: 150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO LIGHTBOX --}}
<div class="modal fade" id="modalPreviewFotoTahapan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-0 p-3 p-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-image text-primary fs-4"></i>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="previewFotoTahapanTitle">Foto SOP / Contoh Hasil</h6>
                        <small class="text-white-50">Pratinjau visual instruksi pengerjaan tahapan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 p-md-4 text-center">
                <img id="previewFotoTahapanImg" src="" alt="Foto Tahapan" class="img-fluid rounded-3 shadow-lg" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
@include('modul_produksi.master_tahapan._modal_input')
@include('modul_produksi.master_tahapan._modal_edit')
@include('modul_produksi.master_tahapan._modal_detail')
@include('modul_produksi.master_tahapan._modal_lookup_bom')

@push('scripts')
<script>
    let dtTahapan = null;

    $(document).ready(function() {
        dtTahapan = $('#tabelMasterTahapan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("produksi.master-tahapan.index") }}',
                data: function(d) {
                    d.filter_bom_id = $('#filterBomTahapan').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center spk-text-muted' },
                { data: 'bom_info' },
                { data: 'preview_alur' },
                { data: 'status', className: 'text-center', orderable: false },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                search: 'Cari BOM / Alur:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Alur tahapan BOM tidak ditemukan',
                emptyTable: '<div class="text-center py-4 spk-text-muted"><i class="bi bi-diagram-3 fs-2 d-block mb-2 text-secondary"></i>Belum ada alur tahapan untuk {{ !empty($isCabang) ? "Cabang " . addslashes($activeBranchName) : "PT Pusat" }}. Klik "+ Tambah Tahapan" untuk mulai.</div>',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                processing: '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span>Memuat...</span></div>'
            }
        });

        $('#filterBomTahapan').on('change', function() {
            reloadTabelTahapan();
        });
    });

    function reloadTabelTahapan() {
        if (dtTahapan) dtTahapan.ajax.reload();
    }

    function setTahapanFilter(val, btnEl, modeName) {
        $('.filter-pill').removeClass('active');
        $(btnEl).addClass('active');

        // Reset BOM dropdown label if not active
        $('#lblDropdownBom').text('Resep BOM Khusus');
        $('#btnDropdownBomFilter').removeClass('active');
        $('#badgeActiveBomCount').addClass('d-none');
        $('.filter-dropdown-item').removeClass('active');
        $('#searchDropdownBomList').val('');
        filterBomListInDropdown('');

        $('#filterBomTahapan').val(val).attr('data-nama', '').attr('data-kode', '').attr('data-barang', '');
        $('#lblActiveFilterName').text(modeName || 'Semua Alur Proses');

        if (val) {
            $('#btnResetFilter').removeClass('d-none').addClass('d-flex');
        } else {
            $('#btnResetFilter').addClass('d-none').removeClass('d-flex');
        }

        reloadTabelTahapan();
    }

    function pilihSpecificBomFilter(bomId, bomNama, bomKode, barangNama) {
        $('.filter-pill').removeClass('active');
        $('#btnDropdownBomFilter').addClass('active');
        $('#lblDropdownBom').text(`${bomNama}`);
        $('#badgeActiveBomCount').removeClass('d-none');

        // Mark active item in dropdown list
        $('.filter-dropdown-item').removeClass('active');
        $(`.filter-dropdown-item[data-id="${bomId}"]`).addClass('active');

        // Close dropdown
        const dropdownEl = document.getElementById('btnDropdownBomFilter');
        const bsDropdown = bootstrap.Dropdown.getInstance(dropdownEl);
        if (bsDropdown) bsDropdown.hide();

        $('#filterBomTahapan').val(bomId)
            .attr('data-nama', bomNama)
            .attr('data-kode', bomKode)
            .attr('data-barang', barangNama);

        $('#lblActiveFilterName').text(`${bomNama} (${bomKode})`);
        $('#btnResetFilter').removeClass('d-none').addClass('d-flex');

        reloadTabelTahapan();
    }

    function resetFilterTahapan() {
        setTahapanFilter('', $('#btnFilterSemua'), 'Semua Alur Proses');
    }

    function filterBomListInDropdown(keyword) {
        const q = (keyword || '').toLowerCase().trim();
        let matched = 0;
        const $items = $('#containerDropdownBomItems .filter-dropdown-item');
        $items.each(function() {
            const text = $(this).text().toLowerCase();
            if (!q || text.indexOf(q) > -1) {
                $(this).removeClass('d-none');
                matched++;
            } else {
                $(this).addClass('d-none');
            }
        });

        if ($items.length > 0) {
            if (matched === 0 && q) {
                $('#emptySearchDropdownBom').removeClass('d-none');
            } else {
                $('#emptySearchDropdownBom').addClass('d-none');
            }
        }
    }

    function previewFotoTahapan(url, title) {
        $('#previewFotoTahapanImg').attr('src', url);
        $('#previewFotoTahapanTitle').text(title || 'Foto SOP Tahapan Produksi');
        $('#modalPreviewFotoTahapan').modal('show');
    }

    function bukaTambahTahapan() {
        const activeFilter = $('#filterBomTahapan').val();
        $('#modalTambahTahapan').modal('show');
        if (activeFilter && activeFilter !== 'general') {
            const nama = $('#filterBomTahapan').attr('data-nama') || '';
            const kode = $('#filterBomTahapan').attr('data-kode') || '';
            const barang = $('#filterBomTahapan').attr('data-barang') || '';
            if (nama && kode) {
                pilihBomTahapan(activeFilter, nama, kode, barang);
            } else {
                $('#inp_tah_bom_id').val(activeFilter);
            }
        } else {
            resetBomTahapanToUmum();
        }
    }

    function hapusAlurBom(bomKey, bomNama) {
        Swal.fire({
            title: 'Hapus Alur Tahapan?',
            html: `Yakin ingin menghapus seluruh tahapan kerja untuk <strong>"${bomNama}"</strong>?<br><small class='text-danger'>Seluruh langkah alur kerja dan foto SOP terkait akan langsung dihapus permanen dari sistem.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Batal',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Semua Alur'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/master-tahapan/bom/${bomKey}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        reloadTabelTahapan();
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1600,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Alur tahapan berhasil dihapus!'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                });
            }
        });
    }

    // ── KUNCI ZOOM IN / ZOOM OUT (MOBILE & DESKTOP) ──
    (function() {
        // 1. Pastikan meta viewport melarang scaling / pinch zoom
        let metaViewport = document.querySelector('meta[name="viewport"]');
        if (!metaViewport) {
            metaViewport = document.createElement('meta');
            metaViewport.name = 'viewport';
            document.head.appendChild(metaViewport);
        }
        metaViewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no');

        // 2. Cegah gesture zoom di Safari iOS
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        }, { passive: false });
        document.addEventListener('gesturechange', function(e) {
            e.preventDefault();
        }, { passive: false });
        document.addEventListener('gestureend', function(e) {
            e.preventDefault();
        }, { passive: false });

        // 3. Cegah pinch to zoom (cubit 2 jari) di HP layar sentuh
        document.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches.length > 1) {
                e.preventDefault();
            }
        }, { passive: false });

        // 4. Cegah zoom keyboard & mouse di Laptop / Desktop (Ctrl + Scroll & Ctrl + / - / 0)
        window.addEventListener('wheel', function(e) {
            if (e.ctrlKey) {
                e.preventDefault();
            }
        }, { passive: false });

        window.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '=' || e.key === '_' || e.key === '0')) {
                e.preventDefault();
            }
        });
    })();
</script>
@endpush
</x-layouts>
