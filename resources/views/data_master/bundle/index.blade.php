<x-layouts title="Master Bundle Produk (Paket Set)">
    <div class="container-fluid px-4 py-3">
        
        {{-- HEADER TITLE & ACTION BUTTON --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold spk-text-contrast mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-boxes text-info fs-3"></i>
                    <span>Master Bundle Produk (Paket Set)</span>
                </h4>
                <p class="spk-text-muted mb-0 small">
                    Kelola paket bundling produk komersial (contoh: <i>Paket Seragam TNI Lengkap</i> yang terdiri dari Baju, Celana, Tas, Rompi, dan Helm).
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-bundle'))
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahBundle">
                    <i class="bi bi-plus-lg fs-6"></i>
                    <span>Tambah Paket Bundle Baru</span>
                </button>
                @endif
            </div>
        </div>

        {{-- SUMMARY KPI GRADIENT CARDS --}}
        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-blue p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">TOTAL MASTER BUNDLE</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardTotalBundle">{{ number_format($totalBundleCount, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-layers me-1 opacity-75"></i> Paket Set Terdaftar
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-green p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">PAKET BUNDLE AKTIF</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value" id="cardActiveBundle">{{ number_format($totalActiveBundle, 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-shield-check me-1 opacity-75"></i> Status Siap Dijual
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-amber p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">KOMPONEN TERSEDIA</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value">{{ number_format($availableComponents->count(), 0, ',', '.') }}</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-collection me-1 opacity-75"></i> Pilihan Barang Jadi / Stok
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="spk-kpi-card spk-kpi-orange p-4 rounded-4 h-100 position-relative overflow-hidden shadow-lg">
                    <div class="d-flex align-items-start justify-content-between mb-3 position-relative" style="z-index: 2;">
                        <div>
                            <span class="spk-kpi-label d-block text-uppercase fw-bold mb-1">SKEMA BUNDLING</span>
                            <div class="fw-bold font-monospace text-white spk-kpi-value-text">Virtual Kit</div>
                        </div>
                        <div class="spk-kpi-icon-box flex-shrink-0">
                            <i class="bi bi-lightning-charge-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="spk-kpi-subtext position-relative" style="z-index: 2;">
                        <i class="bi bi-cpu me-1 opacity-75"></i> Potong Otomatis Saat Order
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="spk-card p-3 p-md-4 rounded-4 shadow-sm border border-secondary border-opacity-10 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-list-stars text-info fs-5"></i>
                    <h5 class="fw-bold spk-text-contrast mb-0">Daftar Paket Bundle & Komponen Penyusun</h5>
                </div>

                {{-- FILTER CONTROL (MODERN MODAL TRIGGER) --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3.5 py-1.5 font-monospace fw-bold shadow-xs d-inline-flex align-items-center gap-2 spk-filter-btn" data-bs-toggle="modal" data-bs-target="#modalFilterBundle">
                        <i class="bi bi-funnel-fill"></i>
                        <span>Filter Data</span>
                        <span class="badge bg-info text-dark rounded-pill px-2 py-0.5" id="badgeActiveFilterCount" style="display:none; font-size: 10px;">0</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 font-monospace shadow-xs d-none" id="btnQuickResetFilter" title="Reset Semua Filter">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tableBundle">
                    <thead class="spk-table-header">
                        <tr>
                            <th class="text-center" style="width: 45px;">NO</th>
                            <th style="min-width: 260px;">INFORMASI PAKET BUNDLE</th>
                            <th style="min-width: 170px;">HARGA UTAMA (RETAIL)</th>
                            <th class="text-center" style="min-width: 130px;">KOMPOSISI</th>
                            <th class="text-center" style="min-width: 170px;">KETERSEDIAAN DIRAKIT</th>
                            <th class="text-center" style="width: 90px;">STATUS</th>
                            <th class="text-center" style="width: 120px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Server side datatables --}}
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- MODAL FILES INCLUSION --}}
    @include('data_master.bundle._modal_tambah')
    @include('data_master.bundle._modal_edit')
    @include('data_master.bundle._modal_detail')
    @include('data_master.bundle._modal_filter')
    @include('data_master.bundle._modal_lookup_barang')
    @include('data_master.bundle._modal_lookup_master_options')

    @push('styles')
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <style>
        /* VIBRANT LUXURY RESPONSIVE KPI GRADIENT CARDS */
        .spk-kpi-card {
            border: none;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            color: #ffffff;
            cursor: default;
            min-height: 130px;
            border-top: 1px solid rgba(255, 255, 255, 0.4) !important;
            padding: 22px 24px !important;
        }
        .spk-kpi-card:hover {
            transform: translateY(-5px) scale(1.01);
        }

        .spk-kpi-card::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -20px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
            z-index: 1;
        }

        .spk-kpi-card::after {
            content: '';
            position: absolute;
            bottom: -40px;
            right: 40px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
            z-index: 1;
        }
        
        .spk-kpi-blue {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%);
            box-shadow: 0 12px 28px -5px rgba(37, 99, 235, 0.55), 0 8px 10px -6px rgba(37, 99, 235, 0.3);
        }
        .spk-kpi-green {
            background: linear-gradient(135deg, #047857 0%, #059669 55%, #10b981 100%);
            box-shadow: 0 12px 28px -5px rgba(5, 150, 105, 0.55), 0 8px 10px -6px rgba(5, 150, 105, 0.3);
        }
        .spk-kpi-amber {
            background: linear-gradient(135deg, #b45309 0%, #d97706 55%, #f59e0b 100%);
            box-shadow: 0 12px 28px -5px rgba(217, 119, 6, 0.55), 0 8px 10px -6px rgba(217, 119, 6, 0.3);
        }
        .spk-kpi-orange {
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 55%, #f97316 100%);
            box-shadow: 0 12px 28px -5px rgba(234, 88, 12, 0.55), 0 8px 10px -6px rgba(234, 88, 12, 0.3);
        }

        .spk-kpi-label {
            font-size: 11px;
            letter-spacing: 0.8px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 700;
        }
        .spk-kpi-value {
            font-size: 2.3rem;
            line-height: 1.1;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .spk-kpi-value-text {
            font-size: 1.65rem;
            line-height: 1.2;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .spk-kpi-subtext {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }
        .spk-kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        @media (max-width: 768px) {
            .spk-kpi-card {
                padding: 18px 20px !important;
                min-height: 110px;
            }
            .spk-kpi-value {
                font-size: 1.9rem;
            }
            .spk-kpi-value-text {
                font-size: 1.45rem;
            }
        }

        /* HIGH CONTRAST & CINEMATIC STYLING FOR DARK & LIGHT MODES */
        .spk-badge-bundle-code {
            background: rgba(14, 165, 233, 0.15) !important;
            color: #0369a1 !important;
            border: 1px solid rgba(14, 165, 233, 0.45) !important;
            font-size: 11.5px !important;
            font-weight: 700 !important;
            letter-spacing: 0.4px;
        }
        html[data-bs-theme="dark"] .spk-badge-bundle-code {
            background: rgba(14, 165, 233, 0.25) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.6) !important;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.6) !important;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.2) !important;
        }

        .spk-badge-bundle-unit {
            background: rgba(100, 116, 139, 0.15) !important;
            color: #334155 !important;
            border: 1px solid rgba(100, 116, 139, 0.35) !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
        }
        html[data-bs-theme="dark"] .spk-badge-bundle-unit {
            background: rgba(148, 163, 184, 0.2) !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(148, 163, 184, 0.45) !important;
        }

        .spk-bundle-title {
            color: #0f172a;
            letter-spacing: -0.2px;
        }
        html[data-bs-theme="dark"] .spk-bundle-title {
            color: #f8fafc !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .spk-bundle-meta {
            color: #475569;
        }
        html[data-bs-theme="dark"] .spk-bundle-meta {
            color: #94a3b8 !important;
        }

        .spk-table-header th {
            font-family: var(--bs-font-monospace, monospace);
            font-size: 11.5px;
            letter-spacing: 0.5px;
            padding: 12px 14px !important;
        }
        html:not([data-bs-theme="dark"]) .spk-table-header th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-table-header th {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #f1f5f9 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
        }

        .spk-action-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .spk-action-btn:hover {
            transform: scale(1.12);
        }

        .spk-btn-glow {
            transition: all 0.2s ease-in-out;
        }
        .spk-btn-glow:hover {
            box-shadow: 0 0 12px rgba(14, 165, 233, 0.45);
            background-color: #0ea5e9 !important;
            color: #ffffff !important;
            border-color: #0ea5e9 !important;
        }

        /* FILTER MODAL SPECIFIC DARK & LIGHT THEMING */
        html:not([data-bs-theme="dark"]) .spk-filter-modal-content {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        html[data-bs-theme="dark"] .spk-filter-modal-content {
            background-color: #0b132b !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(56, 189, 248, 0.15) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-filter-modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            border-color: #334155 !important;
        }
        html[data-bs-theme="dark"] .spk-filter-modal-header {
            background: linear-gradient(135deg, #070d19 0%, #0f172a 100%) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .spk-filter-title {
            color: #ffffff !important;
        }
        .spk-filter-subtitle {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .spk-filter-badge {
            background: rgba(14, 165, 233, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
            font-size: 0.65rem;
            letter-spacing: 0.5px;
        }
        .spk-filter-icon-box {
            width: 40px;
            height: 40px;
            background: rgba(14, 165, 233, 0.18);
            border: 1px solid rgba(56, 189, 248, 0.4);
            color: #38bdf8;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.3);
        }
        .spk-filter-btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Filter Body & Sections */
        html:not([data-bs-theme="dark"]) .spk-filter-modal-body {
            background-color: #f8fafc !important;
        }
        html[data-bs-theme="dark"] .spk-filter-modal-body {
            background-color: #0b132b !important;
        }

        html:not([data-bs-theme="dark"]) .spk-filter-section {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        }
        html[data-bs-theme="dark"] .spk-filter-section {
            background-color: rgba(30, 41, 59, 0.45) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-filter-section-label {
            color: #1e293b !important;
        }
        html[data-bs-theme="dark"] .spk-filter-section-label {
            color: #f1f5f9 !important;
        }

        /* Interactive Chip Buttons */
        .spk-chip-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-family: var(--bs-font-monospace, monospace);
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 20px;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }
        .spk-chip-btn:hover {
            border-color: #0ea5e9;
            color: #0ea5e9;
        }
        .spk-chip-btn.active {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
        }

        html[data-bs-theme="dark"] .spk-chip-btn {
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(15, 23, 42, 0.6);
            color: #cbd5e1;
        }
        html[data-bs-theme="dark"] .spk-chip-btn:hover {
            border-color: #38bdf8;
            color: #38bdf8;
        }
        html[data-bs-theme="dark"] .spk-chip-btn.active {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
        }

        /* Filter Selects */
        html:not([data-bs-theme="dark"]) .spk-filter-select,
        html:not([data-bs-theme="dark"]) .spk-filter-input-addon {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        html[data-bs-theme="dark"] .spk-filter-select,
        html[data-bs-theme="dark"] .spk-filter-input-addon {
            background-color: #020617 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }

        /* Filter Footer */
        html:not([data-bs-theme="dark"]) .spk-filter-modal-footer {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        html[data-bs-theme="dark"] .spk-filter-modal-footer {
            background-color: rgba(15, 23, 42, 0.6) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        .spk-filter-btn-reset {
            border: 1px solid #cbd5e1;
            color: #64748b;
        }
        html[data-bs-theme="dark"] .spk-filter-btn-reset {
            border-color: rgba(255, 255, 255, 0.2);
            color: #94a3b8;
        }
        .spk-filter-btn-reset:hover {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #ef4444;
        }

        .spk-filter-btn-cancel {
            background-color: #64748b;
            color: #ffffff;
        }
        html[data-bs-theme="dark"] .spk-filter-btn-cancel {
            background-color: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .spk-filter-btn-apply {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
            border: none;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }
        .spk-filter-btn-apply:hover {
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.6);
            transform: translateY(-1px);
        }

        /* DETAIL MODAL SPECIFIC THEME STYLING */
        html:not([data-bs-theme="dark"]) .spk-detail-modal-content {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-detail-modal-content {
            background-color: #0b132b !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(56, 189, 248, 0.15) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-detail-modal-body {
            background-color: #f8fafc !important;
        }
        html[data-bs-theme="dark"] .spk-detail-modal-body {
            background-color: #070d19 !important;
        }

        html:not([data-bs-theme="dark"]) .spk-detail-card-section {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        }
        html[data-bs-theme="dark"] .spk-detail-card-section {
            background-color: #0b132b !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
        }

        /* Tier Pricing Cards */
        html:not([data-bs-theme="dark"]) .spk-detail-tier-card {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
        html[data-bs-theme="dark"] .spk-detail-tier-card {
            background-color: rgba(15, 23, 42, 0.75) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-detail-price-text {
            color: #0f172a !important;
        }
        html[data-bs-theme="dark"] .spk-detail-price-text {
            color: #f8fafc !important;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
        }

        /* Table header & rows */
        html:not([data-bs-theme="dark"]) .spk-detail-table-header th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-detail-table-header th {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #f1f5f9 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
        }

        /* Footer */
        html:not([data-bs-theme="dark"]) .spk-detail-modal-footer {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        html[data-bs-theme="dark"] .spk-detail-modal-footer {
            background-color: #070d19 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* LOOKUP MODAL THEME STYLING */
        html:not([data-bs-theme="dark"]) .spk-lookup-modal-content {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-lookup-modal-content {
            background-color: #0b132b !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(56, 189, 248, 0.15) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-lookup-modal-body {
            background-color: #f8fafc !important;
        }
        html[data-bs-theme="dark"] .spk-lookup-modal-body {
            background-color: #070d19 !important;
        }

        html:not([data-bs-theme="dark"]) .spk-lookup-card-section {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
        }
        html[data-bs-theme="dark"] .spk-lookup-card-section {
            background-color: #0b132b !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        html:not([data-bs-theme="dark"]) .spk-lookup-table-header th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        html[data-bs-theme="dark"] .spk-lookup-table-header th {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #f1f5f9 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
        }

        /* BUNDLE MODALS DARK & LIGHT MODE STYLES */
        .spk-modal-content {
            background-color: var(--bs-modal-bg, #ffffff);
            color: var(--bs-body-color, #0f172a);
        }
        
        html:not([data-bs-theme="dark"]) .spk-card-section {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            color: #0f172a !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }
        
        html[data-bs-theme="dark"] .spk-card-section {
            background-color: rgba(30, 41, 59, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }

        html:not([data-bs-theme="dark"]) .spk-input-label {
            color: #334155 !important;
        }
        
        html[data-bs-theme="dark"] .spk-input-label {
            color: #cbd5e1 !important;
        }

        html:not([data-bs-theme="dark"]) .spk-form-input {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        
        html[data-bs-theme="dark"] .spk-form-input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html:not([data-bs-theme="dark"]) .spk-table-header-custom {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        
        html[data-bs-theme="dark"] .spk-table-header-custom {
            background-color: rgba(15, 23, 42, 0.7) !important;
            color: #94a3b8 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1) !important;
        }

        .btn-lookup-komponen:hover {
            background-color: #0ea5e9 !important;
            color: #ffffff !important;
        }

        /* FilePond & Photo Gallery Styles */
        .filepond--root {
            font-family: inherit;
            margin-bottom: 0;
        }
        .filepond--panel-root {
            border: 2px dashed #cbd5e1;
            background-color: rgba(248, 250, 252, 0.8);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        html[data-bs-theme="dark"] .filepond--panel-root {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            background-color: rgba(15, 23, 42, 0.6);
        }
        .filepond--drop-label {
            color: #64748b;
            cursor: pointer;
        }
        .filepond--label-action {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
        }
        .filepond--label-action:hover {
            text-decoration: underline;
        }
        .bundle-photo-gallery-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            user-select: none;
        }
        html[data-bs-theme="dark"] .bundle-photo-gallery-item {
            border-color: rgba(255,255,255,0.15);
        }
        .bundle-photo-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .bundle-photo-gallery-item .btn-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.65);
            display: flex;
            justify-content: space-around;
            padding: 3px 0;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .bundle-photo-gallery-item:hover .btn-actions {
            opacity: 1;
        }
        .bundle-photo-gallery-item .btn-action-icon {
            color: #fff;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }
        .bundle-photo-gallery-item .primary-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #e11d48;
            color: white;
            font-size: 0.55rem;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: bold;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
    </style>
    @endpush

    @push('scripts')
    <!-- FilePond JS & Plugins -->
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <!-- Background Removal Library (AI, runs entirely in browser) -->
    <script type="module">
        try {
            const module = await import('https://esm.sh/@imgly/background-removal@1.4.5?bundle');
            window._imglyRemoveBg = module.removeBackground || module.default;
            window._imglyReady = true;
            document.dispatchEvent(new CustomEvent('imglyReady'));
            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => {
                    fetch('https://staticimgly.com/@imgly/background-removal-data/1.4.5/dist/medium/model.onnx', { mode: 'cors', cache: 'force-cache' }).catch(() => {});
                });
            }
        } catch (e) {
            console.error("Gagal memuat library AI Hapus Background:", e);
            window._imglyError = e;
            window._imglyErrorMessage = e.message || String(e);
        }
    </script>
    <script>
        // Register FilePond plugins
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginImagePreview,
            FilePondPluginFileValidateSize
        );

        // Helper: Resize Gambar via Canvas sebelum diproses AI
        function resizeImage(blob, maxWidthHeight = 1000) {
            return new Promise((resolve) => {
                const url = URL.createObjectURL(blob instanceof Blob ? blob : new Blob([blob]));
                const img = new Image();
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    let w = img.width, h = img.height;
                    if (w > maxWidthHeight || h > maxWidthHeight) {
                        if (w > h) { h = Math.round(h * maxWidthHeight / w); w = maxWidthHeight; }
                        else       { w = Math.round(w * maxWidthHeight / h); h = maxWidthHeight; }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    canvas.toBlob(resolve, 'image/png');
                };
                img.src = url;
            });
        }

        let tableBundle;

        $(document).ready(function() {
            tableBundle = $('#tableBundle').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: {
                    url: "{{ route('master-bundle.data') }}",
                    data: function(d) {
                        d.status = $('#modal_filter_status').val();
                        d.kategori_id = $('#modal_filter_kategori').val();
                        d.satuan_id = $('#modal_filter_satuan').val();
                        d.brand_id = $('#modal_filter_brand').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, class: 'text-center fw-semibold text-muted' },
                    { data: 'bundle_info', name: 'nama_barang' },
                    { data: 'harga_jual', name: 'harga_jual_retail' },
                    { data: 'komposisi', name: 'id', orderable: false, searchable: false, class: 'text-center' },
                    { data: 'estimasi_stok_bundle', name: 'id', orderable: false, searchable: false, class: 'text-center' },
                    { data: 'status', name: 'status', class: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center' }
                ],
                language: {
                    search: "Cari Paket Bundle...",
                    lengthMenu: "Tampil _MENU_ baris",
                    zeroRecords: "Belum ada data Paket Bundle terdaftar",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ Paket Bundle",
                    infoEmpty: "Menampilkan 0 Paket Bundle",
                    processing: '<div class="spinner-border spinner-border-sm text-info" role="status"></div> Memuat data Master Bundle...'
                }
            });

            function updateActiveFilterBadge() {
                let count = 0;
                if ($('#modal_filter_status').val() !== 'all') count++;
                if ($('#modal_filter_kategori').val() !== 'all') count++;
                if ($('#modal_filter_satuan').val() !== 'all') count++;
                if ($('#modal_filter_brand').val() !== 'all') count++;

                if (count > 0) {
                    $('#badgeActiveFilterCount').text(count).show();
                    $('#btnQuickResetFilter').removeClass('d-none');
                } else {
                    $('#badgeActiveFilterCount').hide();
                    $('#btnQuickResetFilter').addClass('d-none');
                }
            }

            $('#btnApplyFilterModal').on('click', function() {
                updateActiveFilterBadge();
                $('#modalFilterBundle').modal('hide');
                tableBundle.ajax.reload();
            });

            $('#btnResetFilterModal, #btnQuickResetFilter').on('click', function() {
                $('#modal_filter_status').val('all');
                $('#modal_filter_kategori').val('all');
                $('#modal_filter_satuan').val('all');
                $('#modal_filter_brand').val('all');
                updateActiveFilterBadge();
                $('#modalFilterBundle').modal('hide');
                tableBundle.ajax.reload();
            });
        });

        function deleteBundle(id, name) {
            Swal.fire({
                title: 'Hapus Master Bundle?',
                html: `Apakah Anda yakin ingin menghapus Paket Bundle <b>${name}</b>?<br><small class="text-danger">Rincian pemetaan komponen bundle ini juga akan dihapus.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Ya, Hapus Paket',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/master-bundle/${id}/delete`,
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Terhapus!', res.message, 'success');
                                tableBundle.ajax.reload();
                            }
                        },
                        error: function(err) {
                            Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal menghapus bundle.', 'error');
                        }
                    });
                }
            });
        }
    </script>
    @endpush
</x-layouts>
