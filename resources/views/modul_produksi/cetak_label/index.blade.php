<x-layouts title="Cetak Label QR & Barcode - Modul Produksi">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spk_produksi.css') }}?v={{ time() }}">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* ================================================================
       PRO-GRADE THEME & STUDIO STYLING
       ================================================================ */
    :root {
        --studio-card-bg: #ffffff;
        --studio-card-border: #e2e8f0;
        --studio-text-main: #0f172a;
        --studio-text-sub: #64748b;
        --studio-accent: #2563eb;
        --studio-tab-bg: #f1f5f9;
    }

    [data-bs-theme="dark"],
    html[data-bs-theme="dark"],
    .dark-mode {
        --studio-card-bg: #1e293b;
        --studio-card-border: #334155;
        --studio-text-main: #f8fafc;
        --studio-text-sub: #94a3b8;
        --studio-accent: #38bdf8;
        --studio-tab-bg: #0f172a;
    }

    .studio-glass-card {
        background-color: var(--studio-card-bg) !important;
        border: 1px solid var(--studio-card-border) !important;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.12);
        border-radius: 20px;
        overflow: hidden;
    }

    .studio-header-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);
        color: #ffffff;
        border-radius: 20px;
        padding: 1.6rem 2rem;
        box-shadow: 0 16px 36px -10px rgba(3, 105, 161, 0.35);
        border: 1px solid rgba(56, 189, 248, 0.3);
        position: relative;
        overflow: hidden;
    }

    /* NAV TABS PILLS */
    .studio-nav-tabs {
        background: var(--studio-tab-bg);
        border-radius: 14px;
        padding: 5px;
        border: 1px solid var(--studio-card-border);
        display: flex;
        gap: 6px;
    }
    .studio-nav-tabs .nav-link {
        border-radius: 10px;
        font-weight: 700;
        font-size: 12.5px;
        color: var(--studio-text-sub);
        padding: 8px 16px;
        border: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .studio-nav-tabs .nav-link.active {
        background: #2563eb;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }
    [data-bs-theme="dark"] .studio-nav-tabs .nav-link.active,
    .dark-mode .studio-nav-tabs .nav-link.active {
        background: #0284c7;
        color: #ffffff !important;
    }

    /* PRESET RADIO CARDS */
    .preset-studio-card {
        border: 1.5px solid var(--studio-card-border);
        background: var(--studio-card-bg);
        border-radius: 12px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .preset-studio-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.15);
    }
    .preset-studio-card.active {
        border-color: #2563eb !important;
        background: rgba(37, 99, 235, 0.08) !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.3) !important;
    }
    [data-bs-theme="dark"] .preset-studio-card.active,
    .dark-mode .preset-studio-card.active {
        background: rgba(59, 130, 246, 0.18) !important;
    }
    .preset-studio-card input[type="radio"] {
        position: absolute;
        top: 12px;
        right: 12px;
    }

    /* BARCODE TYPE SWITCH */
    .type-switch-btn {
        border: 1.5px solid var(--studio-card-border);
        background: var(--studio-card-bg);
        color: var(--studio-text-main);
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 700;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex: 1;
    }
    .type-switch-btn.active {
        border-color: #10b981 !important;
        background: rgba(16, 185, 129, 0.1) !important;
        color: #059669 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    }
    [data-bs-theme="dark"] .type-switch-btn.active,
    .dark-mode .type-switch-btn.active {
        color: #34d399 !important;
    }

    /* LAYOUT SWITCH BUTTONS */
    .layout-switch-btn {
        border: 1.5px solid var(--studio-card-border);
        background: var(--studio-card-bg);
        color: var(--studio-text-main);
        border-radius: 10px;
        padding: 6px 4px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .layout-switch-btn:hover {
        border-color: #3b82f6;
        transform: translateY(-1px);
    }
    .layout-switch-btn.active {
        border-color: #2563eb !important;
        background: rgba(37, 99, 235, 0.1) !important;
        color: #1d4ed8 !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
    }
    [data-bs-theme="dark"] .layout-switch-btn.active,
    .dark-mode .layout-switch-btn.active {
        color: #38bdf8 !important;
    }

    /* LIVE PREVIEW CONTAINER */
    .live-preview-viewport {
        background: #0f172a;
        background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
        background-size: 12px 12px;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 240px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    /* LIVE STICKER RENDERING BOX */
    .live-sticker-card {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 6px;
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.35);
        box-sizing: border-box;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
        position: relative;
        overflow: hidden;
    }

    /* THEMED TABLES */
    .studio-table {
        color: var(--studio-text-main) !important;
        background-color: var(--studio-card-bg) !important;
    }
    .studio-table thead th {
        background-color: var(--studio-tab-bg) !important;
        color: var(--studio-text-main) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 10px 12px !important;
        border-bottom: 2px solid var(--studio-card-border) !important;
    }
    .studio-table tbody td {
        background-color: var(--studio-card-bg) !important;
        color: var(--studio-text-main) !important;
        padding: 10px 12px !important;
        border-color: var(--studio-card-border) !important;
        vertical-align: middle !important;
    }

    /* STICKY SIDEBAR STUDIO */
    @media (min-width: 992px) {
        .sticky-studio-panel {
            position: sticky;
            top: 20px;
            z-index: 10;
        }
    }
</style>
@endpush

<div class="content-wrapper py-3 px-2 px-md-4">
    
    {{-- HERO BANNER --}}
    <div class="studio-header-card mb-3 mb-md-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-1.5">
                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-2.5 py-1 rounded-pill fs-9 fw-bold">
                        <i class="bi bi-qr-code-scan text-warning me-1"></i> Label Print Station
                    </span>
                    <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-2.5 py-1 rounded-pill fs-9 fw-bold">
                        <i class="bi bi-printer-fill me-1"></i> Multi-Printer Ready
                    </span>
                </div>
                <h4 class="fw-extrabold mb-1 text-white">Pusat Cetak Label QR &amp; Barcode</h4>
                <p class="text-white text-opacity-80 small mb-0">
                    Studio cetak label pintar untuk <strong>Serial Number (SN)</strong>, <strong>Katalog Produk SKU</strong>, dan <strong>Label Kustom</strong> dengan preset printer thermal rol maupun kertas stiker A4.
                </p>
            </div>
            <div class="col-lg-5 text-lg-end">
                @if($isBranchSession)
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 text-white">
                        <i class="bi bi-shop text-warning"></i>
                        <span class="fs-8 fw-semibold">Sesi Cabang: <strong>{{ $entityName }}</strong></span>
                    </div>
                @else
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 text-white">
                        <i class="bi bi-building text-info"></i>
                        <span class="fs-8 fw-semibold">Sesi Pusat: <strong>{{ $entityName }}</strong></span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MAIN STUDIO GRID --}}
    <div class="row g-3 g-md-4">
        
        {{-- KOLOM KIRI: SUMBER DATA & TABEL SELEKSI --}}
        <div class="col-lg-7 col-xl-8">
            <div class="studio-glass-card p-3 p-md-4">
                
                {{-- TAB NAVIGATION --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <ul class="nav studio-nav-tabs" id="labelSourceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-sn-btn" data-bs-toggle="tab" data-bs-target="#tab-sn-pane" type="button" role="tab" onclick="switchSourceTab('sn')">
                                <i class="bi bi-upc-scan"></i>
                                <span>Unit Serial Number</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-product-btn" data-bs-toggle="tab" data-bs-target="#tab-product-pane" type="button" role="tab" onclick="switchSourceTab('product')">
                                <i class="bi bi-box-seam"></i>
                                <span>Katalog Produk Jadi (SKU)</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-custom-btn" data-bs-toggle="tab" data-bs-target="#tab-custom-pane" type="button" role="tab" onclick="switchSourceTab('custom')">
                                <i class="bi bi-pencil-square"></i>
                                <span>Label Manual / Kustom</span>
                            </button>
                        </li>
                    </ul>

                    {{-- TOMBOL CEPAT SELEKSI --}}
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 fs-9" onclick="clearSelectedLabels()">
                            <i class="bi bi-x-circle me-1"></i>Reset Seleksi
                        </button>
                    </div>
                </div>

                {{-- TAB CONTENT --}}
                <div class="tab-content" id="labelSourceTabsContent">
                    
                    {{-- TAB 1: SERIAL NUMBER UNIT --}}
                    <div class="tab-pane fade show active" id="tab-sn-pane" role="tabpanel">
                        
                        {{-- FILTER BAR UNTUK SN --}}
                        <div class="p-3 rounded-3 mb-3 border" style="background: var(--studio-tab-bg); border-color: var(--studio-card-border) !important;">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <select id="filter_sn_barang" class="form-select form-select-sm select2-filter" onchange="reloadSnTable()">
                                        <option value="">-- Semua Produk Jadi --</option>
                                        @foreach($barangJadis as $b)
                                            <option value="{{ $b->id }}">[{{ $b->kode_barang }}] {{ $b->nama_barang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filter_sn_status" class="form-select form-select-sm" onchange="reloadSnTable()">
                                        <option value="">-- Semua Status --</option>
                                        <option value="Tersedia">🟢 Tersedia</option>
                                        <option value="Terjual">🔵 Terjual</option>
                                        <option value="Rusak">🔴 Rusak</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" id="filter_sn_batch" class="form-control form-control-sm font-monospace" placeholder="Cari No. Batch..." onkeyup="debounceSnBatch(this.value)">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold" onclick="selectBulkFilterSerials()" title="Pilih semua unit hasil filter aktif">
                                        <i class="bi bi-check2-all me-1"></i>Pilih Filter
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL SERIAL NUMBER --}}
                        <div class="table-responsive">
                            <table class="table studio-table align-middle w-100" id="table_print_serials">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;" class="text-center">
                                            <input type="checkbox" id="check_all_sn_tab" class="form-check-input cursor-pointer" onclick="toggleCheckAllSnTab(this)">
                                        </th>
                                        <th style="width: 40px;" class="text-center">No</th>
                                        <th style="width: 150px;">Serial Number</th>
                                        <th>Barang Jadi</th>
                                        <th style="width: 120px;">Batch &amp; SPK</th>
                                        <th style="width: 130px;">Gudang</th>
                                        <th style="width: 90px;" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>

                    {{-- TAB 2: KATALOG PRODUK (SKU) --}}
                    <div class="tab-pane fade" id="tab-product-pane" role="tabpanel">
                        
                        {{-- FILTER BAR UNTUK PRODUK --}}
                        <div class="p-3 rounded-3 mb-3 border" style="background: var(--studio-tab-bg); border-color: var(--studio-card-border) !important;">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <select id="filter_prod_kategori" class="form-select form-select-sm" onchange="reloadProductTable()">
                                        <option value="">-- Semua Kategori --</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select id="filter_prod_warna" class="form-select form-select-sm" onchange="reloadProductTable()">
                                        <option value="">-- Semua Warna --</option>
                                        @foreach($warnas as $w)
                                            <option value="{{ $w->id }}">{{ $w->nama_warna }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- TABEL PRODUK JADI --}}
                        <div class="table-responsive">
                            <table class="table studio-table align-middle w-100" id="table_print_products">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;" class="text-center">
                                            <input type="checkbox" id="check_all_prod_tab" class="form-check-input cursor-pointer" onclick="toggleCheckAllProdTab(this)">
                                        </th>
                                        <th style="width: 40px;" class="text-center">No</th>
                                        <th style="width: 140px;">Kode / Barcode</th>
                                        <th>Nama Produk</th>
                                        <th style="width: 120px;">Kategori</th>
                                        <th style="width: 120px;">Satuan / Brand</th>
                                        <th style="width: 90px;" class="text-center">Qty Cetak</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>

                    {{-- TAB 3: GENERATOR MANUAL / CUSTOM --}}
                    <div class="tab-pane fade" id="tab-custom-pane" role="tabpanel">
                        <div class="p-3 p-md-4 rounded-3 border" style="background: var(--studio-tab-bg); border-color: var(--studio-card-border) !important;">
                            <h6 class="fw-bold mb-3"><i class="bi bi-sliders text-primary me-2"></i>Input Parameter Label Kustom</h6>
                            <form id="form_custom_label" onsubmit="addCustomLabelToQueue(event)">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fs-8 fw-bold">Nomor Kode / Barcode / SN <span class="text-danger">*</span></label>
                                        <input type="text" id="cust_code" class="form-control font-monospace" placeholder="Contoh: SN-CUSTOM-2026-001" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fs-8 fw-bold">Nama Produk / Label <span class="text-danger">*</span></label>
                                        <input type="text" id="cust_name" class="form-control" placeholder="Contoh: Kemeja Flanel Slimfit" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold">Kode SKU Barang</label>
                                        <input type="text" id="cust_sku" class="form-control font-monospace" placeholder="Contoh: KFL-001">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold">Nomor Batch / Lot</label>
                                        <input type="text" id="cust_batch" class="form-control font-monospace" placeholder="Contoh: B2026-09">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fs-8 fw-bold">Varian Warna / Ukuran</label>
                                        <input type="text" id="cust_color" class="form-control" placeholder="Contoh: Navy - Size L">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fs-8 fw-bold">Jumlah Lembar Stiker</label>
                                        <input type="number" id="cust_qty" class="form-control font-monospace" value="1" min="1" max="500">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            <i class="bi bi-plus-circle me-1"></i> Tambahkan ke Antrian Cetak
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- KOLOM KANAN: STUDIO CONTROLS, PREVIEW & CETAK --}}
        <div class="col-lg-5 col-xl-4">
            <div class="studio-glass-card p-3 p-md-4 sticky-studio-panel">
                
                {{-- HEADER STUDIO --}}
                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom" style="border-color: var(--studio-card-border) !important;">
                    <h6 class="fw-extrabold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-sliders2 text-primary fs-5"></i>
                        <span>Studio Pengaturan</span>
                    </h6>
                    <span class="badge bg-primary rounded-pill px-2.5 py-1 fs-8 fw-black font-monospace" id="label_queue_badge">
                        0 Label
                    </span>
                </div>

                {{-- PILIHAN TIPE KODE (QR CODE vs BARCODE 1D) --}}
                <label class="form-label fs-8 fw-bold text-muted text-uppercase mb-1.5">
                    Format Simbologi Barcode:
                </label>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="type-switch-btn active" id="btn_type_qr" onclick="switchBarcodeType('qr')">
                        <i class="bi bi-qr-code fs-5"></i>
                        <span>QR Code 2D</span>
                    </button>
                    <button type="button" class="type-switch-btn" id="btn_type_barcode" onclick="switchBarcodeType('barcode1d')">
                        <i class="bi bi-upc fs-5"></i>
                        <span>Barcode 1D (Code-128)</span>
                    </button>
                </div>

                {{-- PILIHAN PRESET UKURAN PRINTER --}}
                <label class="form-label fs-8 fw-bold text-muted text-uppercase mb-1.5">
                    Preset Ukuran Label / Kertas:
                </label>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="preset-studio-card d-block" onclick="selectStudioPreset('thermal_50_30')">
                            <input type="radio" name="studio_preset" value="thermal_50_30" class="form-check-input">
                            <div class="fw-bold fs-9">Thermal 50×30 mm</div>
                            <small class="text-muted fs-10">Mini Rol</small>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="preset-studio-card active d-block" onclick="selectStudioPreset('thermal_70_50')">
                            <input type="radio" name="studio_preset" value="thermal_70_50" class="form-check-input" checked>
                            <div class="fw-bold fs-9">Thermal 70×50 mm</div>
                            <small class="text-muted fs-10">Standar Rol</small>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="preset-studio-card d-block" onclick="selectStudioPreset('thermal_100_75')">
                            <input type="radio" name="studio_preset" value="thermal_100_75" class="form-check-input">
                            <div class="fw-bold fs-9">Thermal 100×75 mm</div>
                            <small class="text-muted fs-10">Karton / Dus Master</small>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="preset-studio-card d-block" onclick="selectStudioPreset('a4_grid_10')">
                            <input type="radio" name="studio_preset" value="a4_grid_10" class="form-check-input">
                            <div class="fw-bold fs-9">Kertas A4 (10 Stiker)</div>
                            <small class="text-muted fs-10">Grid 2 × 5</small>
                        </label>
                    </div>
                </div>

                {{-- PENGATURAN TATA LETAK & PENEMPATAN --}}
                <div class="p-2.5 rounded-3 mb-3 border" style="background: var(--studio-tab-bg); border-color: var(--studio-card-border) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <small class="fs-9 fw-bold text-muted text-uppercase">
                            <i class="bi bi-layout-split text-primary me-1"></i> Tata Letak &amp; Penempatan:
                        </small>
                    </div>
                    
                    {{-- 3 Mode Susunan --}}
                    <div class="row g-1.5 mb-2">
                        <div class="col-4">
                            <button type="button" class="layout-switch-btn active w-100" id="btn_layout_stacked" onclick="switchLayoutMode('stacked')">
                                <i class="bi bi-distribute-vertical fs-5 mb-0.5"></i>
                                <span class="d-block fs-10 fw-bold">Vertikal</span>
                                <small class="text-muted" style="font-size: 8px;">Atas-Bawah</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="layout-switch-btn w-100" id="btn_layout_side_by_side" onclick="switchLayoutMode('side_by_side')">
                                <i class="bi bi-distribute-horizontal fs-5 mb-0.5"></i>
                                <span class="d-block fs-10 fw-bold">Horizontal</span>
                                <small class="text-muted" style="font-size: 8px;">Kiri-Kanan</small>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="layout-switch-btn w-100" id="btn_layout_compact_code" onclick="switchLayoutMode('compact_code')">
                                <i class="bi bi-qr-code-scan fs-5 mb-0.5"></i>
                                <span class="d-block fs-10 fw-bold">Ringkas</span>
                                <small class="text-muted" style="font-size: 8px;">Hanya Kode</small>
                            </button>
                        </div>
                    </div>

                    {{-- Perataan & Skala --}}
                    <div class="row g-1.5">
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-between px-2 py-1 rounded bg-white bg-opacity-50 border">
                                <small class="fs-10 fw-bold text-muted">Perataan:</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 active" id="btn_align_center" onclick="switchAlignment('center')" title="Rata Tengah">
                                        <i class="bi bi-text-center"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btn_align_left" onclick="switchAlignment('left')" title="Rata Kiri">
                                        <i class="bi bi-text-left"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-between px-2 py-1 rounded bg-white bg-opacity-50 border">
                                <small class="fs-10 fw-bold text-muted">Ukuran:</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" id="btn_scale_s" onclick="switchBarcodeScale('s')">S</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5 active" id="btn_scale_m" onclick="switchBarcodeScale('m')">M</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" id="btn_scale_l" onclick="switchBarcodeScale('l')">L</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOGGLE FIELD VISIBILITY --}}
                <div class="p-2.5 rounded-3 mb-3 border" style="background: var(--studio-tab-bg); border-color: var(--studio-card-border) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <small class="fs-9 fw-bold text-muted text-uppercase">Opsi Elemen Stiker:</small>
                    </div>
                    <div class="row g-1">
                        <div class="col-6">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" id="opt_show_header" checked onchange="updateLivePreview()">
                                <label class="form-check-label" for="opt_show_header">Header Entitas</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" id="opt_show_sku" checked onchange="updateLivePreview()">
                                <label class="form-check-label" for="opt_show_sku">Kode SKU</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" id="opt_show_batch" checked onchange="updateLivePreview()">
                                <label class="form-check-label" for="opt_show_batch">No. Batch</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" id="opt_show_color" checked onchange="updateLivePreview()">
                                <label class="form-check-label" for="opt_show_color">Varian Warna</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LIVE WYSIWYG PREVIEW BOX --}}
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <label class="form-label fs-8 fw-bold text-muted text-uppercase mb-0">
                        <i class="bi bi-eye text-primary me-1"></i> Live Label Preview:
                    </label>
                    <small class="font-monospace fs-10 text-primary fw-bold" id="preview_preset_label">70 × 50 mm</small>
                </div>

                <div class="live-preview-viewport mb-3">
                    <div id="live_sticker_card" class="live-sticker-card position-relative shadow-lg" style="background: #ffffff !important; color: #0f172a !important; border: 1.5px solid #cbd5e1; border-radius: 12px; font-family: 'Plus Jakarta Sans', sans-serif;">
                        
                        {{-- Header Perusahaan / Cabang Modern Capsule --}}
                        <div id="prev_header_area" class="d-flex align-items-center justify-content-between px-3 pt-2 pb-1.5 border-bottom" style="border-color: #e2e8f0 !important;">
                            <span class="badge rounded-pill font-monospace fw-extrabold text-uppercase px-2 py-0.5" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 8px; letter-spacing: 0.5px;">
                                <i class="bi bi-building me-1"></i>{{ $entityName }}
                            </span>
                            <span class="font-monospace fw-bold text-muted" style="font-size: 7.5px;">ORIGINAL UNIT</span>
                        </div>

                        {{-- Body Container (Dinamis: Stacked / Side-by-Side) --}}
                        <div id="prev_body_container" class="p-2.5">
                            
                            {{-- Section Barcode / QR --}}
                            <div id="prev_barcode_section" class="text-center">
                                <div id="prev_qr_wrapper" class="d-inline-block p-1 bg-white border rounded-3 shadow-xs" style="border-color: #3b82f6 !important;">
                                    <img id="prev_qr_img" src="" alt="QR" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
                                </div>
                                <div id="prev_barcode_wrapper" style="display: none; width: 100%; max-width: 180px; margin: 0 auto; background: #ffffff; padding: 2px 4px; border-radius: 6px;">
                                    <svg id="prev_barcode_svg" style="width: 100%; max-height: 42px; display: block; margin: 0 auto;"></svg>
                                </div>
                                <div class="mt-1" id="prev_code_pill_wrapper">
                                    <span class="d-inline-block px-2 py-0.5 rounded-pill font-monospace fw-black fs-8 shadow-xs" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; letter-spacing: 0.5px;" id="prev_code_text">
                                        PAS-TAS-2026-09-002
                                    </span>
                                </div>
                            </div>

                            {{-- Section Spesifikasi Barang --}}
                            <div id="prev_specs_section" class="p-2 rounded-3 border mt-1.5" style="background: #f8fafc !important; border-color: #e2e8f0 !important;">
                                <div class="fw-extrabold text-truncate mb-1" style="font-size: 10.5px; color: #0f172a;" id="prev_name_text">Tas Kuliah Laptop</div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-1" style="font-size: 8px;" id="prev_badges_row">
                                    <span id="prev_sku_badge" class="badge bg-white text-dark border font-monospace px-1.5 py-0.5 rounded shadow-2xs">
                                        SKU: <strong id="prev_sku_val">AK-TK-HJ</strong>
                                    </span>
                                    <span id="prev_batch_badge" class="badge font-monospace px-1.5 py-0.5 rounded shadow-2xs" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                        LOT: <strong id="prev_batch_val">PAS-TAS-2026-09</strong>
                                    </span>
                                </div>
                                
                                <div id="prev_color_row" class="d-flex align-items-center gap-1.5 mt-1 pt-1 border-top" style="border-top-color: #e2e8f0 !important; font-size: 8px;">
                                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#3b82f6;border:1px solid #93c5fd;" id="prev_color_dot"></span>
                                    <span class="fw-bold" style="color: #334155;" id="prev_color_text">RG (Red Gold)</span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- CTA ACTION PRINT --}}
                <div class="d-grid gap-2">
                    <button type="button" id="btn_execute_studio_print" class="btn btn-success rounded-pill py-2.5 fw-black shadow-lg d-flex align-items-center justify-content-center gap-2" onclick="executeStudioPrint()" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none;">
                        <i class="bi bi-printer-fill fs-5"></i>
                        <span class="fs-7">Cetak Stiker (<span id="btn_print_count">0</span> Label)</span>
                    </button>
                </div>

            </div>
        </div>

    </div>

</div>

@push('scripts')
<!-- Select2 Library -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- QRious Library (QR Code) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<!-- JsBarcode Library (1D Barcode Code-128) -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
    let activeSourceTab = 'sn';
    let currentBarcodeType = 'qr';
    let currentPreset = 'thermal_70_50';
    let currentLayoutMode = 'stacked'; // 'stacked', 'side_by_side', 'compact_code'
    let currentAlignment = 'center'; // 'center', 'left'
    let currentBarcodeScale = 'm'; // 's', 'm', 'l'
    let selectedLabelsMap = new Map(); // Key: ID/Code, Value: Object
    let snTable, productTable;
    let snBatchTimer = null;

    $(document).ready(function() {
        initSnDataTable();
        initProductDataTable();
        updateLivePreview();
        updateQueueDisplay();
    });

    function switchSourceTab(tab) {
        activeSourceTab = tab;
        if (tab === 'sn' && snTable) {
            snTable.columns.adjust().draw();
        } else if (tab === 'product' && productTable) {
            productTable.columns.adjust().draw();
        }
    }

    function switchBarcodeType(type) {
        currentBarcodeType = type;
        if (type === 'qr') {
            $('#btn_type_qr').addClass('active');
            $('#btn_type_barcode').removeClass('active');
        } else {
            $('#btn_type_barcode').addClass('active');
            $('#btn_type_qr').removeClass('active');
        }
        updateLivePreview();
    }

    function switchLayoutMode(mode) {
        currentLayoutMode = mode;
        $('.layout-switch-btn').removeClass('active');
        $(`#btn_layout_${mode}`).addClass('active');
        updateLivePreview();
    }

    function switchAlignment(align) {
        currentAlignment = align;
        if (align === 'center') {
            $('#btn_align_center').addClass('active');
            $('#btn_align_left').removeClass('active');
        } else {
            $('#btn_align_left').addClass('active');
            $('#btn_align_center').removeClass('active');
        }
        updateLivePreview();
    }

    function switchBarcodeScale(scale) {
        currentBarcodeScale = scale;
        $('#btn_scale_s, #btn_scale_m, #btn_scale_l').removeClass('active');
        $(`#btn_scale_${scale}`).addClass('active');
        updateLivePreview();
    }

    function selectStudioPreset(preset) {
        currentPreset = preset;
        $('.preset-studio-card').removeClass('active');
        $(`.preset-studio-card input[value="${preset}"]`).prop('checked', true).closest('.preset-studio-card').addClass('active');
        
        const names = {
            'thermal_50_30': '50 × 30 mm',
            'thermal_70_50': '70 × 50 mm',
            'thermal_100_75': '100 × 75 mm',
            'a4_grid_10': 'A4 Grid (10)'
        };
        $('#preview_preset_label').text(names[preset] || '70 × 50 mm');
        updateLivePreview();
    }

    // ===== DATATABLES INIT =====
    function initSnDataTable() {
        snTable = $('#table_print_serials').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('produksi.cetak-label.data-serials') }}",
                data: function(d) {
                    d.barang_jadi_id = $('#filter_sn_barang').val();
                    d.status         = $('#filter_sn_status').val();
                    d.nomor_batch    = $('#filter_sn_batch').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center', width: '32px' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted fs-8', width: '40px' },
                { data: 'sn_badge', name: 'serial_number' },
                { data: 'barang_info', name: 'barangJadi.nama_barang' },
                { data: 'batch_spk', name: 'nomor_batch' },
                { data: 'gudang_info', name: 'penerimaan.gudang.nama_gudang' },
                { data: 'status_badge', name: 'status', className: 'text-center' }
            ],
            order: [[2, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function() {
                // Re-check items in map
                $('#table_print_serials .check-label-item').each(function() {
                    const code = $(this).data('code');
                    if (selectedLabelsMap.has(code)) {
                        this.checked = true;
                    }
                });
            }
        });
    }

    function initProductDataTable() {
        productTable = $('#table_print_products').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('produksi.cetak-label.data-products') }}",
                data: function(d) {
                    d.kategori_id = $('#filter_prod_kategori').val();
                    d.warna_id    = $('#filter_prod_warna').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center', width: '32px' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted fs-8', width: '40px' },
                { data: 'kode_barcode', name: 'kode_barang' },
                { data: 'nama_produk', name: 'nama_barang' },
                { data: 'kategori', name: 'kategori.nama_kategori' },
                { data: 'satuan_brand', name: 'satuan.nama_satuan' },
                { data: 'qty_input', name: 'qty_input', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[2, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            drawCallback: function() {
                $('#table_print_products .check-label-item').each(function() {
                    const code = $(this).data('code');
                    if (selectedLabelsMap.has(code)) {
                        this.checked = true;
                    }
                });
            }
        });
    }

    function reloadSnTable() {
        if (snTable) snTable.ajax.reload();
    }

    function reloadProductTable() {
        if (productTable) productTable.ajax.reload();
    }

    function debounceSnBatch(val) {
        clearTimeout(snBatchTimer);
        snBatchTimer = setTimeout(() => {
            reloadSnTable();
        }, 300);
    }

    // ===== CHECKBOX & QUEUE MANAGEMENT =====
    function handleLabelItemCheck(el) {
        const code = String($(el).data('code'));
        if (el.checked) {
            selectedLabelsMap.set(code, {
                type: $(el).data('type') || 'sn',
                code: code,
                sn: $(el).data('sn') || code,
                sku: $(el).data('sku') || '',
                name: $(el).data('name') || '',
                batch: $(el).data('batch') || '',
                color: $(el).data('color') || '',
                qty: parseInt($(el).data('qty')) || 1
            });
        } else {
            selectedLabelsMap.delete(code);
        }
        updateQueueDisplay();
        updateLivePreview();
    }

    function handleQtyChange(id, val) {
        const checkbox = $(`#check_prod_${id}`);
        const qty = parseInt(val) || 1;
        checkbox.data('qty', qty);
        const code = checkbox.data('code');
        if (selectedLabelsMap.has(code)) {
            let item = selectedLabelsMap.get(code);
            item.qty = qty;
            selectedLabelsMap.set(code, item);
        }
        updateQueueDisplay();
    }

    function toggleCheckAllSnTab(master) {
        const isChecked = master.checked;
        $('#table_print_serials .check-label-item').each(function() {
            this.checked = isChecked;
            handleLabelItemCheck(this);
        });
    }

    function toggleCheckAllProdTab(master) {
        const isChecked = master.checked;
        $('#table_print_products .check-label-item').each(function() {
            this.checked = isChecked;
            handleLabelItemCheck(this);
        });
    }

    function clearSelectedLabels() {
        selectedLabelsMap.clear();
        $('.check-label-item').prop('checked', false);
        $('#check_all_sn_tab').prop('checked', false);
        $('#check_all_prod_tab').prop('checked', false);
        updateQueueDisplay();
        updateLivePreview();
    }

    function updateQueueDisplay() {
        let totalStickers = 0;
        selectedLabelsMap.forEach(item => {
            totalStickers += (parseInt(item.qty) || 1);
        });
        $('#label_queue_badge').text(`${totalStickers} Label`);
        $('#btn_print_count').text(totalStickers);
    }

    function selectBulkFilterSerials() {
        Swal.fire({
            title: 'Memuat Data Filter...',
            text: 'Mengambil semua data Serial Number sesuai filter...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('produksi.cetak-label.bulk-serials') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barang_jadi_id: $('#filter_sn_barang').val(),
                status: $('#filter_sn_status').val(),
                nomor_batch: $('#filter_sn_batch').val()
            },
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.success && res.data && res.data.length > 0) {
                    res.data.forEach(it => {
                        selectedLabelsMap.set(it.code, it);
                    });
                    updateQueueDisplay();
                    updateLivePreview();
                    if (snTable) snTable.draw(false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Terpilih',
                        text: `${res.count} unit SN otomatis ditambahkan ke antrian cetak.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Data',
                        text: 'Tidak ada serial number yang cocok dengan filter saat ini.'
                    });
                }
            }
        });
    }

    function addCustomLabelToQueue(e) {
        e.preventDefault();
        const code = $('#cust_code').val().trim();
        const name = $('#cust_name').val().trim();
        const sku = $('#cust_sku').val().trim();
        const batch = $('#cust_batch').val().trim();
        const color = $('#cust_color').val().trim();
        const qty = parseInt($('#cust_qty').val()) || 1;

        if (!code || !name) return;

        selectedLabelsMap.set(code, {
            type: 'custom',
            code: code,
            sn: code,
            sku: sku || code,
            name: name,
            batch: batch,
            color: color,
            qty: qty
        });

        $('#form_custom_label')[0].reset();
        $('#cust_qty').val(1);
        updateQueueDisplay();
        updateLivePreview();

        Swal.fire({
            icon: 'success',
            title: 'Label Ditambahkan',
            text: `Label "${name}" (${qty} lembar) berhasil masuk ke antrian cetak.`,
            timer: 1500,
            showConfirmButton: false
        });
    }

    // ===== LIVE PREVIEW RENDERING =====
    function updateLivePreview() {
        const sample = selectedLabelsMap.size > 0 
            ? Array.from(selectedLabelsMap.values())[0]
            : {
                code: 'PAS-TAS-2026-09-002',
                sn: 'PAS-TAS-2026-09-002',
                sku: 'AK-TK-HJ',
                name: 'Tas Kuliah Laptop',
                batch: 'PAS-TAS-2026-09',
                color: 'RG (Red Gold)'
            };

        const codeVal = sample.code || sample.sn || 'PAS-TAS-2026-09-002';

        // Text bindings
        $('#prev_code_text').text(codeVal);
        $('#prev_name_text').text(sample.name || '-');
        $('#prev_sku_val').text(sample.sku || '-');
        $('#prev_batch_val').text(sample.batch || '-');
        $('#prev_color_text').text(sample.color || '-');

        // Toggles
        if ($('#opt_show_header').is(':checked')) {
            $('#prev_header_area').show();
        } else {
            $('#prev_header_area').hide();
        }

        if ($('#opt_show_sku').is(':checked') && sample.sku) {
            $('#prev_sku_badge').show();
        } else {
            $('#prev_sku_badge').hide();
        }

        if ($('#opt_show_batch').is(':checked') && sample.batch) {
            $('#prev_batch_badge').show();
        } else {
            $('#prev_batch_badge').hide();
        }

        if ($('#opt_show_color').is(':checked') && sample.color) {
            $('#prev_color_row').show();
        } else {
            $('#prev_color_row').hide();
        }

        // Adjust live sticker card dimensions per preset
        const card = $('#live_sticker_card');
        if (currentPreset === 'thermal_50_30') {
            card.css({ 'width': '220px', 'min-height': '130px' });
        } else if (currentPreset === 'thermal_70_50') {
            card.css({ 'width': '250px', 'min-height': '165px' });
        } else if (currentPreset === 'thermal_100_75') {
            card.css({ 'width': '280px', 'min-height': '195px' });
        } else {
            card.css({ 'width': '245px', 'min-height': '150px' });
        }

        // Layout Mode (Stacked / Side-by-Side / Compact)
        const bodyContainer = $('#prev_body_container');
        const barcodeSec = $('#prev_barcode_section');
        const specsSec = $('#prev_specs_section');

        if (currentLayoutMode === 'side_by_side') {
            bodyContainer.attr('style', 'display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; gap: 8px !important; padding: 8px !important;');
            barcodeSec.attr('style', 'width: 44% !important; flex-shrink: 0 !important; text-align: ' + currentAlignment + ' !important;');
            specsSec.attr('style', 'width: 54% !important; margin-top: 0 !important; flex-grow: 1 !important; display: block !important; background: #f8fafc !important; border-color: #e2e8f0 !important;');
        } else if (currentLayoutMode === 'compact_code') {
            bodyContainer.attr('style', 'display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; padding: 14px 8px !important;');
            barcodeSec.attr('style', 'width: 100% !important; text-align: ' + currentAlignment + ' !important;');
            specsSec.attr('style', 'display: none !important;');
        } else {
            // Stacked (Default)
            bodyContainer.attr('style', 'display: block !important; padding: 8px 10px !important;');
            barcodeSec.attr('style', 'width: 100% !important; text-align: ' + currentAlignment + ' !important;');
            specsSec.attr('style', 'width: 100% !important; margin-top: 6px !important; display: block !important; background: #f8fafc !important; border-color: #e2e8f0 !important;');
        }

        // Alignment adjustments
        if (currentAlignment === 'left') {
            specsSec.css('text-align', 'left');
            $('#prev_badges_row').css('justify-content', 'flex-start');
            $('#prev_color_row').css('justify-content', 'flex-start');
            $('#prev_barcode_wrapper').css('margin', '0');
        } else {
            specsSec.css('text-align', 'center');
            $('#prev_badges_row').css('justify-content', 'center');
            $('#prev_color_row').css('justify-content', 'center');
            $('#prev_barcode_wrapper').css('margin', '0 auto');
        }

        // Sizing per Scale
        let qrPx = 75;
        let barH = 38;
        if (currentBarcodeScale === 's') {
            qrPx = 60;
            barH = 28;
        } else if (currentBarcodeScale === 'l') {
            qrPx = 90;
            barH = 46;
        }

        // Barcode / QR Symbology rendering (STRICT MUTUALLY EXCLUSIVE)
        if (currentBarcodeType === 'qr') {
            $('#prev_qr_wrapper').attr('style', 'display: inline-block !important;');
            $('#prev_barcode_wrapper').attr('style', 'display: none !important;');
            try {
                let qr = new QRious({
                    value: codeVal,
                    size: qrPx * 2,
                    level: 'H'
                });
                $('#prev_qr_img').attr('src', qr.toDataURL('image/png')).css({ 'width': qrPx + 'px', 'height': qrPx + 'px' });
            } catch(e) {
                console.warn("QRious error:", e);
            }
        } else {
            $('#prev_qr_wrapper').attr('style', 'display: none !important;');
            $('#prev_barcode_wrapper').attr('style', 'display: block !important;');
            try {
                JsBarcode("#prev_barcode_svg", codeVal, {
                    format: "CODE128",
                    displayValue: false,
                    height: barH,
                    margin: 0,
                    width: currentBarcodeScale === 's' ? 1.2 : (currentBarcodeScale === 'l' ? 1.6 : 1.4)
                });
            } catch(e) {
                console.warn("JsBarcode error:", e);
            }
        }
    }

    // ===== PRINT EXECUTION ENGINE =====
    function executeStudioPrint() {
        if (selectedLabelsMap.size === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Antrian Cetak Kosong',
                text: 'Silakan pilih atau tambahkan minimal 1 label yang ingin dicetak.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        const items = Array.from(selectedLabelsMap.values());
        const showHeader = $('#opt_show_header').is(':checked');
        const showSku = $('#opt_show_sku').is(':checked');
        const showBatch = $('#opt_show_batch').is(':checked');
        const showColor = $('#opt_show_color').is(':checked');

        const btn = $('#btn_execute_studio_print');
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyiapkan Template...');

        setTimeout(() => {
            try {
                // Flatten items by their qty
                let expandedList = [];
                items.forEach(it => {
                    const qty = parseInt(it.qty) || 1;
                    for (let q = 0; q < qty; q++) {
                        expandedList.push(it);
                    }
                });

                // Compute dimensions based on Scale
                let qrRenderSize = currentBarcodeScale === 's' ? 200 : (currentBarcodeScale === 'l' ? 320 : 260);
                let barHeight = currentBarcodeScale === 's' ? 32 : (currentBarcodeScale === 'l' ? 52 : 42);

                // Generate code image for each item
                let processedItems = expandedList.map(it => {
                    const codeVal = it.code || it.sn || '';
                    let codeImgHtml = '';
                    if (currentBarcodeType === 'qr') {
                        let qr = new QRious({
                            value: codeVal,
                            size: qrRenderSize,
                            level: 'H'
                        });
                        codeImgHtml = `<img src="${qr.toDataURL('image/png')}" style="width: 100%; height: 100%; object-fit: contain; display: block; margin: 0 auto;">`;
                    } else {
                        // 1D Barcode
                        let canvas = document.createElement("canvas");
                        try {
                            JsBarcode(canvas, codeVal, {
                                format: "CODE128",
                                displayValue: false,
                                height: barHeight,
                                margin: 0,
                                width: currentBarcodeScale === 's' ? 1.3 : (currentBarcodeScale === 'l' ? 1.8 : 1.5)
                            });
                            codeImgHtml = `<img src="${canvas.toDataURL('image/png')}" style="width: 100%; max-height: 100%; object-fit: contain; display: block; margin: 0 auto;">`;
                        } catch(e) {
                            codeImgHtml = `<div style="font-family: monospace; font-size: 8pt; text-align: center;">${codeVal}</div>`;
                        }
                    }
                    return { ...it, codeVal: codeVal, codeImgHtml: codeImgHtml };
                });

                // Build Modern HTML Layout per Preset & Placement Mode
                let printBody = '';
                let pageCss = '';

                // Helper to render Specs Box
                function renderSpecsHtml(it, compact = false) {
                    if (currentLayoutMode === 'compact_code') return '';
                    return `
                        <div class="specs-card">
                            <div class="name-text">${it.name}</div>
                            <div class="badges-row">
                                ${showSku && it.sku ? `<span class="spec-badge">SKU: <strong>${it.sku}</strong></span>` : ''}
                                ${showBatch && it.batch ? `<span class="spec-lot">LOT: ${it.batch}</span>` : ''}
                                ${showColor && it.color ? `<span class="spec-color">• ${it.color}</span>` : ''}
                            </div>
                        </div>
                    `;
                }

                if (currentPreset === 'thermal_50_30') {
                    pageCss = `
                        @page { size: 50mm 30mm; margin: 0; }
                        body { margin: 0; padding: 1mm; font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .print-sticker {
                            width: 48mm; height: 28mm; box-sizing: border-box;
                            page-break-after: always; break-after: page;
                            display: flex; flex-direction: column; justify-content: space-between;
                            padding: 1.2mm 1.5mm; border: 1px dashed #94a3b8; border-radius: 4px;
                        }
                        .top-bar { display: flex; align-items: center; justify-content: space-between; border-bottom: 0.5px solid #e2e8f0; padding-bottom: 0.5mm; margin-bottom: 0.8mm; }
                        .top-badge { font-size: 5pt; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.3px; }
                        .top-sub { font-size: 4.5pt; font-weight: 700; color: #64748b; }
                        
                        /* Body Layouts */
                        .body-side { display: flex; align-items: center; justify-content: space-between; gap: 1.5mm; flex-grow: 1; }
                        .body-stacked { text-align: ${currentAlignment}; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
                        
                        .code-box-side { width: ${currentBarcodeType === 'qr' ? '18mm' : '22mm'}; height: ${currentBarcodeType === 'qr' ? '18mm' : '11mm'}; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
                        .code-box-stack { width: 100%; height: ${currentBarcodeType === 'qr' ? '14mm' : '9mm'}; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
                        
                        .code-pill { font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 6pt; color: #1d4ed8; background: #eff6ff; border: 0.5px solid #bfdbfe; border-radius: 999px; padding: 0.2mm 1mm; display: inline-block; margin-top: 0.5mm; letter-spacing: 0.2px; }
                        .specs-card { background: #f8fafc; border: 0.5px solid #e2e8f0; border-radius: 3px; padding: 0.8mm 1.2mm; text-align: ${currentAlignment}; }
                        .name-text { font-weight: 800; font-size: 5.8pt; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.3mm; }
                        .badges-row { display: flex; align-items: center; gap: 1mm; font-size: 4.8pt; justify-content: ${currentAlignment === 'left' ? 'flex-start' : 'center'}; }
                        .spec-badge { background: #fff; border: 0.5px solid #cbd5e1; border-radius: 2px; padding: 0.1mm 0.8mm; font-family: 'JetBrains Mono', monospace; color: #334155; }
                        .spec-lot { background: #f0fdf4; border: 0.5px solid #bbf7d0; border-radius: 2px; padding: 0.1mm 0.8mm; font-family: 'JetBrains Mono', monospace; color: #15803d; font-weight: 700; }
                        .spec-color { color: #475569; font-weight: 600; }
                    `;

                    processedItems.forEach(it => {
                        let bodyContent = '';
                        if (currentLayoutMode === 'side_by_side') {
                            bodyContent = `
                                <div class="body-side">
                                    <div style="text-align: ${currentAlignment};">
                                        <div class="code-box-side">${it.codeImgHtml}</div>
                                        <div><span class="code-pill">${it.codeVal}</span></div>
                                    </div>
                                    <div style="flex-grow: 1; overflow: hidden;">
                                        ${renderSpecsHtml(it)}
                                    </div>
                                </div>
                            `;
                        } else if (currentLayoutMode === 'compact_code') {
                            bodyContent = `
                                <div style="text-align: center; padding: 1mm 0;">
                                    <div style="width: 100%; height: ${currentBarcodeType === 'qr' ? '18mm' : '13mm'}; display: flex; align-items: center; justify-content: center;">${it.codeImgHtml}</div>
                                    <div><span class="code-pill" style="font-size: 7pt;">${it.codeVal}</span></div>
                                </div>
                            `;
                        } else {
                            // Stacked
                            bodyContent = `
                                <div class="body-stacked">
                                    <div>
                                        <div class="code-box-stack">${it.codeImgHtml}</div>
                                        <div><span class="code-pill">${it.codeVal}</span></div>
                                    </div>
                                    ${renderSpecsHtml(it)}
                                </div>
                            `;
                        }

                        printBody += `
                            <div class="print-sticker">
                                ${showHeader ? `
                                <div class="top-bar">
                                    <span class="top-badge">{{ $entityName }}</span>
                                    <span class="top-sub">ORIGINAL</span>
                                </div>
                                ` : ''}
                                ${bodyContent}
                            </div>
                        `;
                    });

                } else if (currentPreset === 'thermal_70_50') {
                    pageCss = `
                        @page { size: 70mm 50mm; margin: 0; }
                        body { margin: 0; padding: 1.5mm; font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .print-sticker {
                            width: 67mm; height: 47mm; box-sizing: border-box;
                            page-break-after: always; break-after: page;
                            display: flex; flex-direction: column; justify-content: space-between;
                            padding: 2mm 2.5mm; border: 1.5px solid #0f172a; border-radius: 6px;
                        }
                        .header-row { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 1mm; }
                        .header-pill { font-size: 6pt; font-weight: 800; color: #1e40af; background: #eff6ff; border: 0.5px solid #bfdbfe; border-radius: 999px; padding: 0.3mm 1.5mm; text-transform: uppercase; letter-spacing: 0.4px; }
                        .header-sub { font-size: 5.5pt; font-weight: 700; color: #64748b; font-family: 'JetBrains Mono', monospace; }
                        
                        .body-side { display: flex; align-items: center; justify-content: space-between; gap: 2.5mm; flex-grow: 1; padding: 1.5mm 0; }
                        .body-stacked { text-align: ${currentAlignment}; padding: 1.5mm 0; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
                        
                        .code-box-side { width: ${currentBarcodeType === 'qr' ? '26mm' : '30mm'}; height: ${currentBarcodeType === 'qr' ? '26mm' : '18mm'}; display: flex; align-items: center; justify-content: center; }
                        .code-box-stack { width: 100%; height: ${currentBarcodeType === 'qr' ? '22mm' : '15mm'}; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
                        
                        .sn-pill { display: inline-block; font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 7.5pt; color: #1d4ed8; background: #eff6ff; border: 0.5px solid #93c5fd; border-radius: 999px; padding: 0.4mm 2.5mm; margin-top: 1mm; letter-spacing: 0.4px; }
                        .specs-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1.2mm 2mm; text-align: ${currentAlignment}; }
                        .name-text { font-weight: 800; font-size: 7.5pt; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.8mm; }
                        .badges-row { display: flex; align-items: center; gap: 1.5mm; font-size: 6pt; justify-content: ${currentAlignment === 'left' ? 'flex-start' : 'center'}; }
                        .spec-badge { background: #ffffff; border: 0.5px solid #cbd5e1; border-radius: 3px; padding: 0.3mm 1mm; font-family: 'JetBrains Mono', monospace; color: #334155; }
                        .spec-lot { background: #f0fdf4; border: 0.5px solid #bbf7d0; border-radius: 3px; padding: 0.3mm 1mm; font-family: 'JetBrains Mono', monospace; color: #15803d; font-weight: 700; }
                        .spec-color { color: #475569; font-weight: 600; }
                    `;

                    processedItems.forEach(it => {
                        let bodyContent = '';
                        if (currentLayoutMode === 'side_by_side') {
                            bodyContent = `
                                <div class="body-side">
                                    <div style="text-align: ${currentAlignment};">
                                        <div class="code-box-side">${it.codeImgHtml}</div>
                                        <div><span class="sn-pill">${it.codeVal}</span></div>
                                    </div>
                                    <div style="flex-grow: 1; overflow: hidden;">
                                        ${renderSpecsHtml(it)}
                                    </div>
                                </div>
                            `;
                        } else if (currentLayoutMode === 'compact_code') {
                            bodyContent = `
                                <div style="text-align: center; padding: 3mm 0;">
                                    <div style="width: 100%; height: ${currentBarcodeType === 'qr' ? '28mm' : '20mm'}; display: flex; align-items: center; justify-content: center;">${it.codeImgHtml}</div>
                                    <div><span class="sn-pill" style="font-size: 9pt; padding: 0.8mm 3.5mm;">${it.codeVal}</span></div>
                                </div>
                            `;
                        } else {
                            // Stacked
                            bodyContent = `
                                <div class="body-stacked">
                                    <div>
                                        <div class="code-box-stack">${it.codeImgHtml}</div>
                                        <div><span class="sn-pill">${it.codeVal}</span></div>
                                    </div>
                                    ${renderSpecsHtml(it)}
                                </div>
                            `;
                        }

                        printBody += `
                            <div class="print-sticker">
                                ${showHeader ? `
                                <div class="header-row">
                                    <span class="header-pill">{{ $entityName }}</span>
                                    <span class="header-sub">ORIGINAL UNIT</span>
                                </div>
                                ` : ''}
                                ${bodyContent}
                            </div>
                        `;
                    });

                } else if (currentPreset === 'thermal_100_75') {
                    pageCss = `
                        @page { size: 100mm 75mm; margin: 0; }
                        body { margin: 0; padding: 2mm; font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .print-sticker {
                            width: 96mm; height: 71mm; box-sizing: border-box;
                            page-break-after: always; break-after: page;
                            display: flex; flex-direction: column; justify-content: space-between;
                            padding: 3mm 3.5mm; border: 2px solid #0f172a; border-radius: 8px;
                        }
                        .header-box { border-bottom: 2px solid #0f172a; padding-bottom: 1.5mm; display: flex; justify-content: space-between; align-items: center; }
                        .middle-box { display: flex; align-items: center; gap: 4mm; flex-grow: 1; padding: 2.5mm 0; }
                        .code-box { width: 38mm; height: 38mm; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
                        .info-box { flex-grow: 1; font-size: 8.5pt; line-height: 1.4; color: #0f172a; text-align: ${currentAlignment}; }
                        .code-title { font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 11pt; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 1mm 2.5mm; display: inline-block; margin-bottom: 2mm; }
                        .footer-box { border-top: 1px solid #cbd5e1; padding-top: 1.5mm; font-size: 7pt; color: #64748b; display: flex; justify-content: space-between; font-weight: 600; }
                    `;
                    processedItems.forEach(it => {
                        printBody += `
                            <div class="print-sticker">
                                <div class="header-box">
                                    <strong style="font-size: 10pt; color: #0f172a; text-transform: uppercase;">{{ $entityName }}</strong>
                                    <span style="font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 8pt; color: #64748b;">AUTHENTIC SPEC</span>
                                </div>
                                <div class="middle-box">
                                    <div class="code-box">${it.codeImgHtml}</div>
                                    <div class="info-box">
                                        <div class="code-title">${it.codeVal}</div>
                                        <div style="font-weight: 800; font-size: 10pt; margin-bottom: 1.5mm; color: #0f172a;">${it.name}</div>
                                        ${showSku && it.sku ? `<div><strong>Kode SKU:</strong> <span style="font-family: 'JetBrains Mono', monospace;">${it.sku}</span></div>` : ''}
                                        ${showBatch && it.batch ? `<div><strong>No. Batch / Lot:</strong> <span style="font-family: 'JetBrains Mono', monospace;">${it.batch}</span></div>` : ''}
                                        ${showColor && it.color ? `<div><strong>Varian Warna:</strong> ${it.color}</div>` : ''}
                                    </div>
                                </div>
                                <div class="footer-box">
                                    <span>Production &amp; Inventory Management ERP</span>
                                    <span>VERIFIED UNIT</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    // A4 Grid 10 Labels (2x5)
                    pageCss = `
                        @page { size: A4 portrait; margin: 8mm 6mm; }
                        body { margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .a4-grid {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
                            grid-auto-rows: 52mm;
                            gap: 4mm 6mm;
                            page-break-after: always;
                            break-after: page;
                            box-sizing: border-box;
                        }
                        .a4-cell {
                            border: 1px dashed #94a3b8;
                            border-radius: 6px;
                            padding: 2.5mm 3.5mm;
                            display: flex;
                            align-items: center;
                            gap: 3mm;
                            box-sizing: border-box;
                            background: #ffffff;
                        }
                        .code-box { width: 30mm; height: 30mm; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
                        .info-box { flex-grow: 1; overflow: hidden; line-height: 1.25; font-size: 8pt; text-align: ${currentAlignment}; }
                        .sn-badge { font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 8.5pt; color: #1d4ed8; background: #eff6ff; border: 0.5px solid #bfdbfe; border-radius: 999px; padding: 0.4mm 2mm; display: inline-block; margin-bottom: 1mm; }
                    `;
                    const chunkSize = 10;
                    for (let i = 0; i < processedItems.length; i += chunkSize) {
                        const chunk = processedItems.slice(i, i + chunkSize);
                        printBody += '<div class="a4-grid">';
                        chunk.forEach(it => {
                            printBody += `
                                <div class="a4-cell">
                                    <div class="code-box">${it.codeImgHtml}</div>
                                    <div class="info-box">
                                        <div class="sn-badge">${it.codeVal}</div>
                                        <div style="font-weight: 800; font-size: 8.5pt; color: #0f172a; margin-bottom: 1mm;">${it.name}</div>
                                        ${showSku && it.sku ? `<div><small style="color: #64748b;">SKU:</small> <strong style="font-family: monospace;">${it.sku}</strong></div>` : ''}
                                        ${showBatch && it.batch ? `<div><small style="color: #64748b;">Lot:</small> <strong style="font-family: monospace;">${it.batch}</strong></div>` : ''}
                                        ${showColor && it.color ? `<div><small style="color: #64748b;">Varian:</small> <strong>${it.color}</strong></div>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        printBody += '</div>';
                    }
                }

                const printWin = window.open('', '', 'width=850,height=950');
                printWin.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Cetak Label Barcode &amp; QR (${processedItems.length} Stiker)</title>
                        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
                        <style>
                            * { box-sizing: border-box; }
                            ${pageCss}
                        </style>
                    </head>
                    <body>
                        ${printBody}
                        <script>
                            window.onload = function() {
                                window.print();
                                setTimeout(() => window.close(), 1200);
                            };
                        <\/script>
                    </body>
                    </html>
                `);
                printWin.document.close();

            } catch(e) {
                console.error("Print Error:", e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memproses Cetak',
                    text: 'Terjadi kendala teknis saat memproses layout pencetakan.'
                });
            } finally {
                btn.prop('disabled', false).html(originalHtml);
            }
        }, 100);
    }
</script>
@endpush

</x-layouts>
