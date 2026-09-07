<x-layouts title="Stok Saat Ini (Stock Overview)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/stok_saat_ini.css') }}">
    <style>
        .btn-tab-entitas {
            color: #64748b;
            background: rgba(148, 163, 184, 0.1);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.2s ease;
        }
        .btn-tab-entitas:hover {
            color: #1e293b;
            background: rgba(148, 163, 184, 0.2);
        }
        .btn-tab-entitas.active {
            color: #ffffff !important;
            background: #2563eb !important;
            border-color: #2563eb !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .btn-tab-entitas.active i {
            color: #ffffff !important;
        }
        .pulse-dot-primary {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #2563eb;
            animation: pulsePrimary 1.5s infinite;
        }
        @keyframes pulsePrimary {
            0% { transform: scale(0.9); opacity: 0.8; }
            50% { transform: scale(1.3); opacity: 0.4; }
            100% { transform: scale(0.9); opacity: 0.8; }
        }
    </style>

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- PAGE HEADER -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam-fill text-primary me-1"></i>Stok Saat Ini (Stock Overview)
                    <span id="liveIndicator" class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill fw-semibold"
                          style="font-size:0.65rem; background:rgba(34,197,94,0.12); color:#16a34a; border:1px solid rgba(34,197,94,0.25); letter-spacing:0.5px;">
                        <span class="live-pulse-dot"></span>LIVE
                    </span>
                </h4>
                <small class="text-muted">Status stok barang ter-update per masing-masing lokasi gudang &mdash; Diperbarui: <span id="lastUpdateText" class="fw-semibold text-primary">-</span></small>
            </div>
            <div class="d-flex gap-2 btn-group-print flex-wrap">
                <button type="button" class="btn btn-outline-info rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRumusPerhitungan">
                    <i class="bi bi-calculator me-1"></i> Rumus Perhitungan
                </button>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalCetakStok">
                    <i class="bi bi-printer me-1"></i> Cetak PDF
                </button>
                <button onclick="exportToExcel()" class="btn btn-outline-success rounded-pill px-3">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
            </div>
        </div>

        <!-- ENTITAS SELECTOR / SCOPE BANNER -->
        @if($isBranchSession)
            <div class="card border-0 shadow-sm rounded-3 mb-3 p-3" style="background-color: var(--card-bg); border-left: 4px solid #0284c7 !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #0284c7, #0ea5e9);">
                            <i class="bi bi-shop fs-6"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-dark">Stok Khusus Cabang: {{ session('active_branch_name') }}</h6>
                                <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-0.5 small fw-semibold">Mode Cabang</span>
                            </div>
                            <small class="text-muted">Menampilkan khusus persediaan gudang milik <strong>{{ session('active_branch_name') }}</strong> &bull; Terpisah mutlak dari stok gudang PT Pusat.</small>
                        </div>
                    </div>
                    <div class="text-secondary small">
                        <i class="bi bi-info-circle me-1 text-info"></i>Untuk melihat stok PT Pusat, silakan ganti ke <strong>Pusat</strong> di menu navigasi atas.
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-3 mb-3 p-2.5" style="background-color: var(--card-bg); border-top: 3px solid #2563eb !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-secondary small fw-bold text-uppercase me-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-building me-1 text-primary"></i>Pilih Kepemilikan Stok:
                        </span>
                        <ul class="nav nav-pills gap-1.5" id="tabEntitasStok" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-tab-entitas rounded-pill px-3.5 py-1.5 fw-semibold small {{ $entitas === 'pt' ? 'active' : '' }}"
                                        data-entitas="pt" data-cabang-id="" type="button" id="tabStokPT">
                                    <i class="bi bi-building-fill-check me-1.5 text-primary"></i>Stok PT Pusat (Head Quarter)
                                </button>
                            </li>
                            @foreach($branches as $branch)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link btn-tab-entitas rounded-pill px-3.5 py-1.5 fw-semibold small {{ ($entitas === 'cabang' && $cabangId == $branch->id) ? 'active' : '' }}"
                                        data-entitas="cabang" data-cabang-id="{{ $branch->id }}" type="button">
                                    <i class="bi bi-shop me-1.5 text-info"></i>Stok {{ $branch->nama_cabang }}
                                </button>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div id="entitasBadgeIndicator">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold small d-inline-flex align-items-center gap-1.5">
                            <span class="pulse-dot-primary"></span>
                            <span id="textActiveEntity">Menampilkan: {{ $activeEntityLabel }}</span>
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <!-- FILTER SEARCH BAR -->
        <div class="row g-2 mb-4 align-items-center filter-section">
            <div class="col">
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-secondary">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" id="inputSearch" class="form-control form-control-lg rounded-pill ps-5 border-0 shadow-sm"
                           placeholder="Cari kode atau nama barang..." value="{{ $search }}" style="height: 48px; background-color: var(--card-bg); color: var(--text-main); font-size: 0.95rem;">
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-lg rounded-pill px-4 shadow-sm position-relative d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#modalFilterStok"
                        style="height: 48px; font-size: 0.95rem; font-weight: 600; background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color);">
                    <i class="bi bi-funnel me-2"></i>Filter
                    <span id="filterBadge" class="position-absolute top-0 start-100 translate-middle p-1.5 bg-primary border border-light rounded-circle {{ ($gudangId || $kategoriId || $status) ? '' : 'd-none' }}">
                        <span class="visually-hidden">Filter Aktif</span>
                    </span>
                </button>
            </div>
            <div class="col-auto">
                <button type="button" id="btnRefreshStok" class="btn btn-lg rounded-pill shadow-sm d-flex align-items-center justify-content-center"
                        title="Segarkan Data"
                        style="width: 48px; height: 48px; padding: 0; background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color); transition: all 0.2s ease;">
                    <i class="bi bi-arrow-clockwise" style="font-size: 1.15rem;"></i>
                </button>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5" id="kpiContainer">
            <!-- Total Qty -->
            <div class="col">
                <div class="kpi-card h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary" style="width:36px;height:36px;font-size:1rem;">
                            <i class="bi bi-archive-fill"></i>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small" style="font-size:0.68rem;">Total Qty</span>
                    </div>
                    <div id="kpiTotalQty" class="kpi-value fs-4 text-body fw-bold">{{ number_format($totalQty, 0, ',', '.') }}</div>
                    <div class="kpi-label text-muted small mt-1">Stok Fisik</div>
                </div>
            </div>
            <!-- Total Nilai Persediaan -->
            <div class="col">
                <div class="kpi-card h-100 p-3 border-primary border-opacity-25" style="border: 1.5px solid var(--border-color);">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon bg-primary text-white" style="width:36px;height:36px;font-size:1rem;">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <span class="badge bg-primary text-white rounded-pill small" style="font-size:0.68rem;">Total Nilai</span>
                    </div>
                    <div id="kpiTotalNilaiPersediaan" class="kpi-value fs-5 text-primary fw-bold" style="font-size: 1.15rem !important; line-height: 1.4; padding-top: 2px;">{{ !empty($canSeeHpp) ? 'Rp ' . number_format($totalNilaiPersediaan, 0, ',', '.') : '***' }}</div>
                    <div class="kpi-label text-primary-emphasis small mt-1">Aset Inventaris</div>
                </div>
            </div>
            <!-- Total Aman -->
            <div class="col">
                <div class="kpi-card h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon bg-success bg-opacity-10 text-success" style="width:36px;height:36px;font-size:1rem;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill small" style="font-size:0.68rem;">Aman</span>
                    </div>
                    <div id="kpiTotalAman" class="kpi-value fs-4 text-success fw-bold">{{ number_format($totalAman, 0, ',', '.') }}</div>
                    <div class="kpi-label text-muted small mt-1">Barang Aman</div>
                </div>
            </div>
            <!-- Total Kritis -->
            <div class="col">
                <div class="kpi-card h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon bg-warning bg-opacity-10 text-warning" style="width:36px;height:36px;font-size:1rem;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill small" style="font-size:0.68rem;">Kritis</span>
                    </div>
                    <div id="kpiTotalKritis" class="kpi-value fs-4 text-warning fw-bold">{{ number_format($totalKritis, 0, ',', '.') }}</div>
                    <div class="kpi-label text-muted small mt-1">Stok Menipis</div>
                </div>
            </div>
            <!-- Total Kosong -->
            <div class="col">
                <div class="kpi-card h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon bg-danger bg-opacity-10 text-danger" style="width:36px;height:36px;font-size:1rem;">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill small" style="font-size:0.68rem;">Kosong</span>
                    </div>
                    <div id="kpiTotalKosong" class="kpi-value fs-4 text-danger fw-bold">{{ number_format($totalKosong, 0, ',', '.') }}</div>
                    <div class="kpi-label text-muted small mt-1">Stok Habis</div>
                </div>
            </div>
        </div>

        <!-- STOK TABLE -->
        <div id="printArea" class="card border-0 shadow-sm rounded-3" style="background-color: var(--card-bg);">
            <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-body mb-0">
                    <i class="bi bi-layers-half me-2 text-primary"></i>Rincian Posisi Inventaris
                </h6>
                <span id="rowCountBadge" class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 fw-semibold">
                    0 Baris Data
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-modern mb-0 w-100" id="tableOverview" style="width:100% !important;">
                        <thead>
                            <tr>
                                <th width="3%" class="text-center text-nowrap">No</th>
                                <th width="10%" class="text-start text-nowrap">Kode Barang</th>
                                <th width="18%" class="text-start text-nowrap">Nama Barang</th>
                                <th width="12%" class="text-start text-nowrap">Lokasi Gudang</th>
                                <th width="6%" class="text-center text-nowrap">Min. Stok</th>
                                <th width="9%" class="text-center text-nowrap">Stok Aktual</th>
                                <th width="11%" class="text-end text-nowrap">Harga Saat Ini</th>
                                <th width="12%" class="text-end text-nowrap">HPP (Avg Cost)</th>
                                <th width="14%" class="text-end text-nowrap">Nilai Persediaan</th>
                                <th width="5%" class="text-center text-nowrap">Status</th>
                                <th width="3%" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FILTER STOK -->
    <div class="modal fade" id="modalFilterStok" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color) !important;">
                <div class="modal-header border-0 py-3" style="background-color: var(--bg-color); border-bottom: 1px solid var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-body-emphasis d-flex align-items-center" style="color: var(--text-main) !important;">
                        <i class="bi bi-funnel text-primary me-2"></i>Filter Stok Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--bs-theme-dark-close-button-filter);"></button>
                </div>
                <form method="GET" action="{{ route('stock-overview.index') }}" id="filterForm">
                    <!-- Maintain search & entity values -->
                    <input type="hidden" name="search" id="hiddenSearch" value="{{ $search }}">
                    <input type="hidden" name="entitas" id="filterEntitas" value="{{ $entitas }}">
                    <input type="hidden" name="cabang_id" id="filterCabangId" value="{{ $cabangId }}">
                    
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Gudang</label>
                                <select name="gudang_id" id="filterGudang" class="form-select select2-filter">
                                    <option value="">-- Semua Gudang --</option>
                                    @foreach($gudangs as $g)
                                        <option value="{{ $g->id }}" {{ $gudangId == $g->id ? 'selected' : '' }}>{{ $g->nama_gudang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Kategori</label>
                                <select name="kategori_id" id="filterKategori" class="form-select select2-filter">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Status Keamanan Stok</label>
                                <select name="status" id="filterStatus" class="form-select select2-filter">
                                    <option value="">-- Semua Status --</option>
                                    <option value="Aman" {{ $status == 'Aman' ? 'selected' : '' }}>Aman</option>
                                    <option value="Kritis" {{ $status == 'Kritis' ? 'selected' : '' }}>Kritis / Menipis</option>
                                    <option value="Kosong" {{ $status == 'Kosong' ? 'selected' : '' }}>Kosong</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 py-2" style="background-color: var(--bg-color); border-top: 1px solid var(--border-color) !important;">
                        <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">Reset</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">Terapkan Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CETAK STOK -->
    <div class="modal fade" id="modalCetakStok" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color) !important;">
                <div class="modal-header border-0 py-3" style="background-color: var(--bg-color); border-bottom: 1px solid var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-body-emphasis d-flex align-items-center" style="color: var(--text-main) !important;">
                        <i class="bi bi-printer text-primary me-2"></i>Cetak Laporan Stok (PDF)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--bs-theme-dark-close-button-filter);"></button>
                </div>
                <form action="{{ route('stock-overview.print') }}" method="GET" target="_blank" id="cetakForm">
                    <input type="hidden" name="entitas" id="printEntitas" value="{{ $entitas }}">
                    <input type="hidden" name="cabang_id" id="printCabangId" value="{{ $cabangId }}">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Gudang</label>
                                <select name="gudang_id" id="printGudang" class="form-select select2-print">
                                    <option value="">-- Semua Gudang --</option>
                                    @foreach($gudangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Kategori</label>
                                <select name="kategori_id" id="printKategori" class="form-select select2-print">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Barang</label>
                                <select name="barang_id" id="printBarang" class="form-select select2-print">
                                    <option value="">-- Semua Barang --</option>
                                    @foreach($barangs as $b)
                                        <option value="{{ $b->id }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-3 pt-2 border-top">
                                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="tampilkan_foto" value="1" id="printTampilkanFoto" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                    <label class="form-check-label small fw-bold text-body mb-0" for="printTampilkanFoto" style="cursor: pointer;">
                                        <i class="bi bi-image text-primary me-1"></i> Sertakan Foto Produk pada Cetakan PDF
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 py-2" style="background-color: var(--bg-color); border-top: 1px solid var(--border-color) !important;">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill">Cetak Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL STOK -->
    <div class="modal fade" id="modalDetailStok" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 95vw; width: 95vw; margin: 15px auto;">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color) !important;">
                <div class="modal-header border-0 py-3 d-flex align-items-center justify-content-between" style="background-color: var(--bg-color); border-bottom: 1px solid var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-body-emphasis d-flex align-items-center mb-0" style="color: var(--text-main) !important;">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>Detail Posisi Stok
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRumusPerhitungan">
                            <i class="bi bi-calculator me-1"></i>Rumus HPP & Stok
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--bs-theme-dark-close-button-filter);"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Column 1: Foto Barang (Carousel) -->
                        <div class="col-md-3 text-center">
                            <div class="rounded-3 p-2 d-flex flex-column align-items-center justify-content-center position-relative" style="height: 240px; overflow: hidden; background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <div id="carouselFotosDetail" class="carousel slide w-100 h-100" data-bs-ride="carousel" style="display: none;">
                                    <div class="carousel-inner h-100 d-flex align-items-center">
                                        <!-- Carousel items will be injected here dynamically by JavaScript -->
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotosDetail" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselFotosDetail" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                                <div id="detailFotoPlaceholder" class="d-flex flex-column align-items-center" style="color: var(--text-muted);">
                                    <i class="bi bi-image fs-1 mb-2" style="color: var(--text-muted);"></i>
                                    <span class="small">Tidak ada foto</span>
                                </div>
                            </div>
                        </div>
                        <!-- Column 2: Rincian -->
                        <div class="col-md-9">
                            <h4 id="detailNamaBarang" class="fw-bold text-body-emphasis mb-1" style="color: var(--text-main) !important;">-</h4>
                            <div class="mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="detailKodeBarang">-</span>
                                <span id="detailSafetyStatus"></span>
                            </div>
                            
                            <hr class="my-3" style="border-top: 1px solid var(--border-color); opacity: 0.8;">
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Gudang Penyimpanan</label>
                                    <span id="detailGudang" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Kategori Barang</label>
                                    <span id="detailKategori" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Warna Barang</label>
                                    <span id="detailWarna" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Minimum Stok</label>
                                    <span id="detailMinStok" class="fw-semibold" style="color: var(--text-muted);">-</span>
                                </div>
                        
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Stok Saatini</label>
                                    <span id="detailStokAktual" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>

                                
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Stok sebelumnya</label>
                                    <span id="detailStokSebelumnya" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);"><i class="bi bi-clock-history me-1 text-info"></i>Sumber Transaksi Terakhir</label>
                                    <span id="detailSumberTransaksiTerakhir" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                
                                @if(!empty($canSeeHpp))
                                <div class="col-12 mt-1">
                                    <div class="p-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background-color: var(--bg-color); border: 1.5px solid var(--border-color);">
                                        <div>
                                            <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Harga Pokok Rata-Rata (HPP Saat Ini)</label>
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <span id="detailHPP" class="fs-5 fw-bold text-success">-</span>
                                                <span id="detailHPPStatusBadge"></span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">HPP Sebelumnya</label>
                                            <span id="detailHPPSebelumnya" class="fw-bold text-body-emphasis" style="font-size: 0.95rem; color: var(--text-main) !important;">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Harga Beli Terakhir</label>
                                    <span id="detailBeliTerakhir" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Harga Beli Sebelumnya</label>
                                    <span id="detailBeliSebelumnya" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Tanggal Beli Terakhir</label>
                                    <span id="detailTanggalBeliTerakhir" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold d-block mb-1" style="color: var(--text-muted);">Tanggal Beli Sebelumnya</label>
                                    <span id="detailTanggalBeliSebelumnya" class="fw-bold text-body-emphasis" style="color: var(--text-main) !important;">-</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="p-3 rounded-3 d-flex align-items-center justify-content-between" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                        <div>
                                            <label class="small fw-semibold d-block mb-0" style="color: var(--text-muted);">Total Nilai Persediaan (Source of Truth)</label>
                                        </div>
                                        <span id="detailNilaiPersediaan" class="fs-4 fw-bold text-primary" style="color: var(--primary) !important;">-</span>
                                    </div>
                                </div>
                                @else
                                <div class="col-12 mt-2">
                                    <div class="p-3 rounded-3 d-flex align-items-center justify-content-between" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-shield-lock-fill text-secondary fs-5"></i>
                                            <span class="small text-muted fw-semibold">Informasi HPP & Nilai Persediaan Dibatasi oleh Hak Akses Role Anda</span>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5">Akses Dibatasi</span>
                                    </div>
                                </div>
                                @endif

                                <!-- TABEL MUTASI HISTORY & KATEGORI MUTASI -->
                                <div class="col-12 mt-4 pt-3 border-top">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2" style="color: var(--text-main) !important;">
                                                <i class="bi bi-clock-history text-primary"></i>Riwayat Mutasi & Transaksi Stok
                                            </h6>
                                            <small class="text-muted" style="font-size:0.75rem;">Pilih kategori transaksi di sebelah kanan untuk menyaring riwayat mutasi.</small>
                                        </div>
                                        
                                        <ul class="nav nav-pills flex-nowrap overflow-x-auto gap-1 p-1 rounded-pill border" id="pillsTabMutasi" style="background-color: var(--bg-color); font-size: 0.78rem; scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                                            <li class="nav-item flex-shrink-0">
                                                <button class="nav-link active rounded-pill px-3 py-1 fw-bold text-nowrap btn-tab-mutasi" data-filter="semua">
                                                    <i class="bi bi-layers me-1"></i>Semua Mutasi
                                                </button>
                                            </li>
                                            <li class="nav-item flex-shrink-0">
                                                <button class="nav-link rounded-pill px-3 py-1 fw-bold text-success text-nowrap btn-tab-mutasi" data-filter="masuk">
                                                    <i class="bi bi-box-arrow-in-down me-1"></i>Barang Masuk
                                                </button>
                                            </li>
                                            <li class="nav-item flex-shrink-0">
                                                <button class="nav-link rounded-pill px-3 py-1 fw-bold text-danger text-nowrap btn-tab-mutasi" data-filter="keluar">
                                                    <i class="bi bi-box-arrow-up me-1"></i>Barang Keluar
                                                </button>
                                            </li>
                                            <li class="nav-item flex-shrink-0">
                                                <button class="nav-link rounded-pill px-3 py-1 fw-bold text-warning text-nowrap btn-tab-mutasi" data-filter="transfer">
                                                    <i class="bi bi-arrow-left-right me-1"></i>Transfer Gudang
                                                </button>
                                            </li>
                                            <li class="nav-item flex-shrink-0">
                                                <button class="nav-link rounded-pill px-3 py-1 fw-bold text-info text-nowrap btn-tab-mutasi" data-filter="opname">
                                                    <i class="bi bi-clipboard-check me-1"></i>Stok Opname
                                                </button>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="table-responsive rounded-3 border w-100 mb-2" style="max-height: 380px; overflow-y: auto; background-color: var(--bg-color);">
                                        <table class="table table-hover align-middle mb-0 w-100" id="tableMutasiDetail" style="font-size: 0.82rem; table-layout: auto;">
                                            <thead class="bg-body-tertiary sticky-top" style="z-index: 10; border-bottom: 1.5px solid var(--border-color);">
                                                <tr>
                                                    <th class="text-nowrap ps-3" style="width: 14%;">Tanggal & Waktu</th>
                                                    <th class="text-center text-nowrap" style="width: 10%;">Tipe Mutasi</th>
                                                    <th class="text-nowrap" style="width: 20%;">Referensi Dokumen</th>
                                                    <th class="text-end text-nowrap" style="width: 10%;">Jumlah Qty</th>
                                                    <th class="text-end text-nowrap" style="width: 13%;">Harga / HPP Satuan</th>
                                                    <th class="text-end text-nowrap" style="width: 13%;">Total Nilai Mutasi</th>
                                                    <th class="text-center text-nowrap" style="width: 10%;">Posisi Stok</th>
                                                    <th class="pe-3" style="width: 10%;">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyMutasiDetail">
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data mutasi stok...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Table Footer Info & Load More -->
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-1 pt-1" id="mutasiFooterInfo">
                                        <small class="text-muted" style="font-size: 0.76rem;" id="textMutasiCountInfo">
                                            Menampilkan 15 mutasi terbaru
                                        </small>
                                        <div id="containerLoadMoreMutasi">
                                            <!-- Dynamically injected load more button -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-2" style="background-color: var(--bg-color); border-top: 1px solid var(--border-color) !important;">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL RUMUS PERHITUNGAN STOK & HPP -->
    <div class="modal fade" id="modalRumusPerhitungan" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3" style="background-color: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color) !important;">
                <div class="modal-header border-0 py-3 d-flex align-items-center justify-content-between" style="background-color: var(--bg-color); border-bottom: 1px solid var(--border-color) !important;">
                    <h5 class="modal-title fw-bold text-body-emphasis d-flex align-items-center mb-0" style="color: var(--text-main) !important;">
                        <i class="bi bi-calculator-fill text-info me-2"></i>Rumus & Formula Perhitungan Barang Masuk & Transfer Gudang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--bs-theme-dark-close-button-filter);"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                    <div class="alert bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 p-3 mb-4 text-info">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <strong class="text-body-emphasis">Metode Akuntansi: Moving Average Cost (HPP Rata-Rata Bergerak)</strong>
                        </div>
                        <small class="d-block text-secondary" style="font-size:0.82rem;">
                            Sistem secara otomatis mengkalkulasi ulang HPP dan nilai persediaan setiap kali terjadi penerimaan barang masuk atau konfirmasi transfer antar gudang.
                        </small>
                    </div>

                    <!-- CARD 1: BARANG MASUK -->
                    <div class="card border rounded-3 shadow-sm mb-4 overflow-hidden" style="background-color: var(--bg-color); border-color: var(--border-color) !important;">
                        <div class="card-header bg-success bg-opacity-10 py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-success d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                                <i class="bi bi-box-arrow-in-down fs-5"></i>1. Perhitungan Barang Masuk (Goods Receipt / Pembelian)
                            </span>
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1">Inbound Stock</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="small fw-bold text-muted d-block mb-1">A. Formula Rekalkulasi HPP Rata-Rata Bergerak Baru:</label>
                                    <div class="p-3 rounded-3 bg-body-tertiary border font-monospace text-dark text-center fw-bold" style="font-size: 0.88rem;">
                                        HPP Baru = [ (Stok Lama × HPP Lama) + (Qty Masuk × Harga Beli) ] ÷ (Stok Lama + Qty Masuk)
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted d-block mb-1">B. Pembaruan Qty Stok:</label>
                                    <div class="p-2.5 rounded-3 bg-body-tertiary border text-body small">
                                        <code>Stok Akhir = Stok Lama + Qty Masuk</code>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold text-muted d-block mb-1">C. Nilai Persediaan Akhir:</label>
                                    <div class="p-2.5 rounded-3 bg-body-tertiary border text-body small">
                                        <code>Nilai Persediaan = Stok Akhir × HPP Baru</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: TRANSFER GUDANG -->
                    <div class="card border rounded-3 shadow-sm mb-4 overflow-hidden" style="background-color: var(--bg-color); border-color: var(--border-color) !important;">
                        <div class="card-header bg-warning bg-opacity-10 py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-warning d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                                <i class="bi bi-arrow-left-right fs-5"></i>2. Perhitungan Transfer Gudang (Intra & Inter-PT)
                            </span>
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">Stock Transfer</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                                        <h6 class="fw-bold text-danger mb-2 d-flex align-items-center gap-1.5" style="font-size:0.85rem;">
                                            <i class="bi bi-box-arrow-up"></i>Gudang Asal (Pengirim):
                                        </h6>
                                        <ul class="mb-0 ps-3 small text-secondary" style="font-size:0.8rem;">
                                            <li class="mb-1">Potong stok sebesar: <code>Qty Transfer</code></li>
                                            <li class="mb-1">Nilai keluar: <code>Qty Transfer × HPP Rata-Rata Gudang Asal</code></li>
                                            <li>HPP Gudang Asal: <strong>Tetap / Tidak Berubah</strong>.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                                        <h6 class="fw-bold text-success mb-2 d-flex align-items-center gap-1.5" style="font-size:0.85rem;">
                                            <i class="bi bi-box-arrow-in-down"></i>Gudang Tujuan (Penerima):
                                        </h6>
                                        <ul class="mb-0 ps-3 small text-secondary" style="font-size:0.8rem;">
                                            <li class="mb-1">Tambah stok sebesar: <code>Qty Transfer</code></li>
                                            <li class="mb-1">Nilai persediaan bertambah: <code>Nilai Transfer Asal</code></li>
                                            <li>HPP Gudang Tujuan dihitung ulang secara <em>Moving Average</em>.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold text-muted d-block mb-1">Formula HPP Baru Gudang Tujuan:</label>
                                    <div class="p-2.5 rounded-3 bg-body-tertiary border font-monospace text-dark text-center fw-bold" style="font-size: 0.84rem;">
                                        HPP Baru Tujuan = [ Nilai Persediaan Lama + (Qty Transfer × HPP Asal) ] ÷ (Stok Lama + Qty Transfer)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 3: BARANG KELUAR & STOK OPNAME -->
                    <div class="card border rounded-3 shadow-sm mb-0 overflow-hidden" style="background-color: var(--bg-color); border-color: var(--border-color) !important;">
                        <div class="card-header bg-primary bg-opacity-10 py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-primary d-flex align-items-center gap-2" style="font-size: 0.92rem;">
                                <i class="bi bi-clipboard-check fs-5"></i>3. Barang Keluar & Stok Opname
                            </span>
                            <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">Adjustment & Outbound</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-2.5 rounded-3 bg-body-tertiary border h-100">
                                        <strong class="d-block text-danger mb-1 small"><i class="bi bi-box-arrow-up me-1"></i>Barang Keluar (Sales/Issue)</strong>
                                        <p class="mb-0 text-secondary" style="font-size:0.78rem;">
                                            Memotong stok secara langsung sebesar Qty transaksi. HPP transaksi dinilai sebesar HPP Rata-Rata berjalan di gudang tersebut.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2.5 rounded-3 bg-body-tertiary border h-100">
                                        <strong class="d-block text-info mb-1 small"><i class="bi bi-sliders me-1"></i>Stok Opname Adjustment</strong>
                                        <p class="mb-0 text-secondary" style="font-size:0.78rem;">
                                            <code>Selisih = Stok Fisik - Stok Sistem</code>. Jika selisih (+), stok bertambah. Jika selisih (-), stok dipotong sebesar nilai HPP berjalan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 py-2" style="background-color: var(--bg-color); border-top: 1px solid var(--border-color) !important;">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── REALTIME UPDATE TOAST ──────────────────────────── --}}
    <div id="realtimeToast" class="p-3" style="background-color: var(--card-bg); border: 1px solid var(--border-color);">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="live-pulse-dot" style="flex-shrink:0;"></span>
            <span class="fw-bold small" style="color: var(--text-main);">Real-time Update</span>
        </div>
        <div class="toast-body small" style="color: var(--text-muted); padding: 0;"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.branchName   = {!! json_encode(session('active_branch_name', '-')) !!};
        let currentEntitas  = @json($entitas);
        let currentCabangId = @json($cabangId);
        let isBranchSession = @json($isBranchSession);

        $(document).ready(function () {
            // Suppress the default DataTables browser warning pop-up
            $.fn.dataTable.ext.errMode = 'none';
            $('#tableOverview').on('error.dt', function(e, settings, techNote, message) {
                console.warn('DataTables error: ', message);
            });

            // Tab Entitas Switcher Click Handler
            $(document).on('click', '.btn-tab-entitas', function (e) {
                e.preventDefault();
                $('.btn-tab-entitas').removeClass('active');
                $(this).addClass('active');

                currentEntitas  = $(this).data('entitas') || 'pt';
                currentCabangId = $(this).data('cabang-id') || '';

                // Sync hidden inputs for modals
                $('#filterEntitas, #printEntitas').val(currentEntitas);
                $('#filterCabangId, #printCabangId').val(currentCabangId);

                let tabLabel = $(this).text().trim();
                $('#textActiveEntity').text('Menampilkan: ' + tabLabel);

                // Reset filter gudang when switching entity
                $('#filterGudang').val(null).trigger('change');

                // Reload data table with new scope
                reloadTableStok(true);
            });

            // Redirect to login page if AJAX returns 401 or 419 (session expired)
            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                if (jqxhr.status === 419 || jqxhr.status === 401 || jqxhr.status === 302) {
                    window.location.reload();
                }
            });

            // Initialize Select2 filter elements with modal parent to ensure search box functions
            $('.select2-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: '-- Pilih --',
                dropdownParent: $('#modalFilterStok')
            });

            // Initialize Select2 print elements with modal parent to ensure search box functions
            $('#printGudang, #printKategori').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: '-- Pilih --',
                dropdownParent: $('#modalCetakStok')
            });

            $('#printBarang').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: '-- Ketik nama atau kode barang --',
                dropdownParent: $('#modalCetakStok'),
                ajax: {
                    url: "{{ route('stock-overview.search-barang') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Close print modal after submit
            $('#cetakForm').on('submit', function () {
                $('#modalCetakStok').modal('hide');
            });

            // Global flag for silent background reloads
            let isSilentReload = false;

            // AJAX Table Reloading function
            function reloadTableStok(resetPaging = false, silent = false) {
                if ($.fn.DataTable.isDataTable('#tableOverview')) {
                    isSilentReload = silent;
                    $('#tableOverview').DataTable().ajax.reload(null, resetPaging);
                }
            }

            // Bind search input typing with a short delay (debounce)
            let searchTimeout = null;
            $('#inputSearch').on('keyup input', function () {
                let val = $(this).val();
                $('#hiddenSearch').val(val);
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    reloadTableStok(true);
                }, 300);
            });

            // Bind filter form submit and reset
            $('#filterForm').on('submit', function (e) {
                e.preventDefault();
                reloadTableStok(true);
                $('#modalFilterStok').modal('hide');
                updateFilterBadge();
            });

            $('#btnResetFilter').on('click', function () {
                $('#filterGudang').val(null).trigger('change');
                $('#filterKategori').val(null).trigger('change');
                $('#filterStatus').val(null).trigger('change');
                $('#inputSearch').val('');
                $('#hiddenSearch').val('');
                reloadTableStok(true);
                $('#modalFilterStok').modal('hide');
                updateFilterBadge();
            });

            function updateFilterBadge() {
                let gudang = $('#filterGudang').val();
                let kategori = $('#filterKategori').val();
                let status = $('#filterStatus').val();
                if (gudang || kategori || status) {
                    $('#filterBadge').removeClass('d-none');
                } else {
                    $('#filterBadge').addClass('d-none');
                }
            }

            // Handle Detail Button Click
            $(document).on('click', '.btn-detail-stok', function () {
                let btn = $(this);
                let barangId = btn.data('barang-id');
                let gudangId = btn.data('gudang-id');

                $('#detailNamaBarang').text(btn.data('barang-nama'));
                $('#detailKodeBarang').text(btn.data('barang-kode'));
                $('#detailKategori').text(btn.data('kategori'));
                $('#detailGudang').text(btn.data('gudang'));
                $('#detailWarna').text(btn.data('warna'));
                $('#detailMinStok').text(btn.data('min-stok'));
                $('#detailStokSebelumnya').text('...');
                $('#detailSumberTransaksiTerakhir').text('...');
                $('#detailStokAktual').text(btn.data('stok-aktual'));
                $('#detailHPP').text(btn.data('hpp'));
                $('#detailHPPSebelumnya').text(btn.data('hpp-sebelumnya'));
                
                let initialBeliTerakhir = btn.data('harga-beli-terakhir') || btn.data('beli-terakhir') || '-';
                let initialBeliSebelumnya = btn.data('harga-beli-sebelumnya') || btn.data('beli-sebelumnya') || '-';
                $('#detailBeliTerakhir').text(initialBeliTerakhir);
                $('#detailBeliSebelumnya').text(initialBeliSebelumnya);

                $('#detailTanggalBeliTerakhir').text('...');
                $('#detailTanggalBeliSebelumnya').text('...');
                let totalNilaiStr = btn.data('total-nilai') || btn.data('nilai-persediaan');
                if (!totalNilaiStr || totalNilaiStr === 'undefined') {
                    totalNilaiStr = 'Rp 0,00';
                }
                $('#detailNilaiPersediaan').text(totalNilaiStr);

                // Calculate HPP change indicator (Handling Indonesian number formatting cleanly)
                function parseIndoCurrency(val) {
                    if (!val) return 0;
                    let clean = String(val).replace(/[^\d,-]/g, '');
                    clean = clean.replace(/\./g, '').replace(',', '.');
                    return parseFloat(clean) || 0;
                }

                let hppVal = parseIndoCurrency(btn.data('hpp'));
                let hppPrevVal = parseIndoCurrency(btn.data('hpp-sebelumnya'));
                let diff = hppVal - hppPrevVal;
                let pct = hppPrevVal > 0 ? ((diff / hppPrevVal) * 100) : 0;

                let badgeHtml = '';
                if (Math.abs(diff) < 0.01) {
                    badgeHtml = '<span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-bold small"><i class="bi bi-dash-lg me-1"></i>Tetap (0%)</span>';
                } else if (diff > 0) {
                    badgeHtml = '<span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-arrow-up-right me-1"></i>Naik +Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(diff) + ' (+' + pct.toFixed(2) + '%)</span>';
                } else {
                    badgeHtml = '<span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold shadow-sm" style="font-size: 0.78rem;"><i class="bi bi-arrow-down-right me-1"></i>Turun -Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.abs(diff)) + ' (' + pct.toFixed(2) + '%)</span>';
                }
                $('#detailHPPStatusBadge').html(badgeHtml);

                // Lazy-load KartuStok info via AJAX
                if (barangId && gudangId) {
                    $.ajax({
                        url: "{{ route('stock-overview.kartu-info') }}",
                        method: 'GET',
                        data: {
                            barang_id: barangId,
                            gudang_id: gudangId,
                            entitas: currentEntitas,
                            cabang_id: currentCabangId
                        },
                        success: function (res) {
                            $('#detailStokSebelumnya').text(res.stok_sebelumnya ?? '-');
                            $('#detailSumberTransaksiTerakhir').text(res.sumber_transaksi_terakhir ?? '-');
                            $('#detailTanggalBeliTerakhir').text(res.tanggal_beli_terakhir ?? '-');
                            $('#detailTanggalBeliSebelumnya').text(res.tanggal_beli_sebelumnya ?? '-');
                            if (res.harga_beli_terakhir) {
                                $('#detailBeliTerakhir').text(res.harga_beli_terakhir);
                            }
                            if (res.harga_beli_sebelumnya) {
                                $('#detailBeliSebelumnya').text(res.harga_beli_sebelumnya);
                            }
                        },
                        error: function () {
                            $('#detailStokSebelumnya').text('-');
                            $('#detailSumberTransaksiTerakhir').text('-');
                            $('#detailTanggalBeliTerakhir').text('-');
                            $('#detailTanggalBeliSebelumnya').text('-');
                        }
                    });
                }

                // Handle slideable photos list in detail modal
                let fotos = btn.data('fotos') || [];
                let carousel = $('#carouselFotosDetail');
                let inner = carousel.find('.carousel-inner');
                let placeholder = $('#detailFotoPlaceholder');

                inner.empty();

                if (fotos && fotos.length > 0) {
                    fotos.forEach(function (url, index) {
                        let activeClass = index === 0 ? 'active' : '';
                        inner.append(`
                            <div class="carousel-item ${activeClass} h-100 text-center">
                                <img src="${url}" alt="Foto Barang #${index + 1}" class="img-fluid rounded-3" style="max-height: 200px; object-fit: contain; width: auto; margin: 0 auto;" />
                            </div>
                        `);
                    });

                    carousel.show();
                    placeholder.addClass('d-none').removeClass('d-flex');

                    let carouselInst = bootstrap.Carousel.getOrCreateInstance(document.getElementById('carouselFotosDetail'));
                    carouselInst.to(0);

                    if (fotos.length <= 1) {
                        carousel.find('.carousel-control-prev, .carousel-control-next').hide();
                    } else {
                        carousel.find('.carousel-control-prev, .carousel-control-next').show();
                    }
                } else {
                    carousel.hide();
                    placeholder.removeClass('d-none').addClass('d-flex');
                }

                // Safety status badge
                let status = btn.data('safety-status');
                let statusHtml = '';
                if (status === 'Aman') {
                    statusHtml = '<span class="badge-safe"><i class="bi bi-shield-check me-1"></i>Aman</span>';
                } else if (status === 'Kritis') {
                    statusHtml = '<span class="badge-warning-stock"><span class="pulse-dot-warning"></span><i class="bi bi-exclamation-triangle me-1"></i>Min</span>';
                } else {
                    statusHtml = '<span class="badge-danger-stock"><i class="bi bi-x-circle me-1"></i>Kosong</span>';
                }
                $('#detailSafetyStatus').html(statusHtml);

                // Photo preview
                let fotoUrl = btn.data('foto-url');
                if (fotoUrl) {
                    $('#detailFotoBarang').attr('src', fotoUrl).show();
                    $('#detailFotoPlaceholder').hide();
                } else {
                    $('#detailFotoBarang').hide();
                    $('#detailFotoPlaceholder').show();
                }

                // Store active barangId & gudangId for mutasi history filters
                currentDetailBarangId = barangId;
                currentDetailGudangId = gudangId;

                // Reset tab filter active state to 'semua'
                $('.btn-tab-mutasi').removeClass('active');
                $('.btn-tab-mutasi[data-filter="semua"]').addClass('active');

                // Load initial Mutasi History (Semua)
                loadMutasiHistory(barangId, gudangId, 'semua');

                $('#modalDetailStok').modal('show');
            });

            let currentDetailBarangId = null;
            let currentDetailGudangId = null;
            let currentDetailLimit = 15;

            function escapeHtml(text) {
                if (!text) return '-';
                return String(text)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function loadMutasiHistory(barangId, gudangId, tipeFilter, limit) {
                tipeFilter = tipeFilter || 'semua';
                limit = limit || 15;
                currentDetailLimit = limit;

                const tbody = $('#tbodyMutasiDetail');
                tbody.html(`
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data mutasi stok...
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: "{{ route('stock-overview.mutasi-history') }}",
                    method: 'GET',
                    data: {
                        barang_id: barangId,
                        gudang_id: gudangId,
                        tipe_filter: tipeFilter,
                        limit: limit,
                        entitas: currentEntitas,
                        cabang_id: currentCabangId
                    },
                    success: function (res) {
                        const totalCount = res.total_count || 0;
                        const loadedRows = res.total_rows || 0;

                        if (!res.mutasi || res.mutasi.length === 0) {
                            tbody.html(`
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-4 d-block mb-1 text-secondary"></i>
                                        Tidak ada riwayat mutasi untuk kategori ini.
                                    </td>
                                </tr>
                            `);
                            $('#textMutasiCountInfo').html('Menampilkan 0 mutasi');
                            $('#containerLoadMoreMutasi').empty();
                            return;
                        }

                        let rows = '';
                        res.mutasi.forEach(function (m) {
                            let badgeTipe = '';
                            if (m.tipe === 'Masuk') {
                                badgeTipe = '<span class="badge badge-mutasi-masuk rounded-pill px-2.5 py-1 text-nowrap"><i class="bi bi-arrow-down-left me-1"></i>Masuk</span>';
                            } else {
                                badgeTipe = '<span class="badge badge-mutasi-keluar rounded-pill px-2.5 py-1 text-nowrap"><i class="bi bi-arrow-up-right me-1"></i>Keluar</span>';
                            }

                            let refJenisStr = m.referensi_jenis || m.tipe_ket || '';
                            let refBadgeClass = 'badge-ref-default';
                            let refIcon = 'bi-file-text';

                            if (refJenisStr.toLowerCase().includes('inter-pt')) {
                                refBadgeClass = 'badge-ref-inter-pt';
                                refIcon = 'bi-buildings';
                            } else if (refJenisStr.toLowerCase().includes('inter-cabang')) {
                                refBadgeClass = 'badge-ref-inter-cabang';
                                refIcon = 'bi-diagram-3';
                            } else if (refJenisStr.toLowerCase().includes('internal') || refJenisStr.toLowerCase().includes('transfer')) {
                                refBadgeClass = 'badge-ref-internal';
                                refIcon = 'bi-arrow-left-right';
                            } else if (refJenisStr.toLowerCase().includes('issue')) {
                                refBadgeClass = 'badge-ref-gi';
                                refIcon = 'bi-box-arrow-up';
                            } else if (refJenisStr.toLowerCase().includes('receipt')) {
                                refBadgeClass = 'badge-ref-gr';
                                refIcon = 'bi-box-arrow-in-down';
                            } else if (refJenisStr.toLowerCase().includes('opname')) {
                                refBadgeClass = 'badge-ref-opname';
                                refIcon = 'bi-clipboard-check';
                            }

                            let safeKet = escapeHtml(m.keterangan);
                            let safeRefNomor = escapeHtml(m.referensi_nomor || m.referensi);

                            rows += `
                                <tr>
                                    <td class="text-nowrap ps-3">
                                        <small class="fw-semibold d-block" style="font-size:0.82rem; color: var(--text-main, #f8fafc);">${m.tanggal}</small>
                                    </td>
                                    <td class="text-center text-nowrap">${badgeTipe}</td>
                                    <td class="text-nowrap">
                                        <span class="badge ${refBadgeClass} rounded-pill px-2 py-0.5 text-nowrap" style="font-size:0.71rem">
                                            <i class="bi ${refIcon} me-1"></i>${escapeHtml(m.tipe_ket || refJenisStr)}
                                        </span>
                                        <small class="fw-bold font-monospace d-block mt-0.5" style="font-size:0.80rem; color: #38bdf8 !important;">${safeRefNomor}</small>
                                    </td>
                                    <td class="text-end text-nowrap fw-bold ${m.tipe === 'Masuk' ? 'text-success' : 'text-danger'}" style="font-size:0.86rem;">
                                        ${m.tipe === 'Masuk' ? '+' : '-'}${m.jumlah_fmt}
                                    </td>
                                    <td class="text-end text-nowrap font-monospace text-secondary" style="font-size:0.82rem;">${m.harga_satuan_fmt}</td>
                                    <td class="text-end text-nowrap font-monospace fw-bold ${m.tipe === 'Masuk' ? 'text-success' : 'text-primary'}" style="font-size:0.84rem;">${m.nilai_total_fmt}</td>
                                    <td class="text-center text-nowrap small" style="color: var(--text-muted, #94a3b8); font-size:0.80rem;">
                                        ${m.stok_awal_fmt} &rarr; <span class="fw-bold" style="color: var(--text-main, #f8fafc);">${m.stok_akhir_fmt}</span>
                                    </td>
                                    <td class="pe-3">
                                        <span class="small d-block text-truncate" style="max-width: 250px; color: var(--text-muted, #94a3b8);" title="${safeKet}">${safeKet}</span>
                                    </td>
                                </tr>
                            `;
                        });

                        tbody.html(rows);

                        // Update Footer Count & Load More Button
                        $('#textMutasiCountInfo').html(`Menampilkan <strong>${loadedRows}</strong> dari <strong>${totalCount}</strong> riwayat mutasi (Default 15 transaksi)`);

                        if (loadedRows < totalCount) {
                            $('#containerLoadMoreMutasi').html(`
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold" id="btnLoadMoreMutasi" style="font-size:0.75rem;">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Tampilkan Lebih Banyak (Top 50)
                                </button>
                            `);
                        } else {
                            $('#containerLoadMoreMutasi').empty();
                        }
                    },
                    error: function () {
                        tbody.html(`
                            <tr>
                                <td colspan="7" class="text-center py-4 text-danger">
                                    <i class="bi bi-exclamation-octagon fs-4 d-block mb-1"></i>
                                    Gagal memuat data mutasi stok.
                                </td>
                            </tr>
                        `);
                        $('#textMutasiCountInfo').text('-');
                        $('#containerLoadMoreMutasi').empty();
                    }
                });
            }

            // Tab Mutasi Filter Click Handler
            $(document).on('click', '.btn-tab-mutasi', function (e) {
                e.preventDefault();
                $('.btn-tab-mutasi').removeClass('active');
                $(this).addClass('active');

                let filter = $(this).data('filter');
                if (currentDetailBarangId && currentDetailGudangId) {
                    loadMutasiHistory(currentDetailBarangId, currentDetailGudangId, filter, 15);
                }
            });

            // Load More Click Handler
            $(document).on('click', '#btnLoadMoreMutasi', function (e) {
                e.preventDefault();
                let nextLimit = currentDetailLimit === 15 ? 50 : 100;
                let activeFilter = $('.btn-tab-mutasi.active').data('filter') || 'semua';
                if (currentDetailBarangId && currentDetailGudangId) {
                    loadMutasiHistory(currentDetailBarangId, currentDetailGudangId, activeFilter, nextLimit);
                }
            });

            // Handle Manual Refresh Click
            $('#btnRefreshStok').on('click', function () {
                let btn = $(this);
                let icon = btn.find('i');
                
                // Add spin animation class
                icon.addClass('spin-animation');
                
                // Reset all filters and search inputs
                $('#filterGudang').val(null).trigger('change');
                $('#filterKategori').val(null).trigger('change');
                $('#filterStatus').val(null).trigger('change');
                $('#inputSearch').val('');
                $('#hiddenSearch').val('');
                updateFilterBadge();

                // Reload data
                reloadTableStok(true);
                
                // Remove animation after complete
                setTimeout(function () {
                    icon.removeClass('spin-animation');
                }, 800);
            });

            // ══════════════════════════════════════════════════════════
            // SMART REAL-TIME ENGINE
            // Ping lightweight endpoint every 5s.
            // Full DataTable reload ONLY when server timestamp changes.
            // This means: near-zero server load when data is not changing.
            // ══════════════════════════════════════════════════════════
            let lastKnownTimestamp = null;
            let consecutiveErrors   = 0;
            let realtimeToastTimer  = null;

            function showUpdateToast(msg) {
                let toast = $('#realtimeToast');
                toast.find('.toast-body').html(msg);
                toast.addClass('show');
                clearTimeout(realtimeToastTimer);
                realtimeToastTimer = setTimeout(() => toast.removeClass('show'), 3500);
            }

            function setLiveStatus(isOnline) {
                let el = $('#liveIndicator');
                if (isOnline) {
                    el.removeClass('is-offline').find('.live-pulse-dot').css('background','');
                    el.html('<span class="live-pulse-dot"></span>LIVE');
                } else {
                    el.addClass('is-offline');
                    el.html('<span class="live-pulse-dot"></span>OFFLINE');
                }
            }

            function pingLastUpdated() {
                let isModalOpen = $('#modalDetailStok').hasClass('show') || $('#modalFilterStok').hasClass('show');
                if (isModalOpen) return; // Don't reload while user is reading detail

                $.ajax({
                    url: "{{ route('stock-overview.last-updated') }}",
                    method: 'GET',
                    data: { entitas: currentEntitas, cabang_id: currentCabangId },
                    timeout: 4000,
                    success: function (res) {
                        consecutiveErrors = 0;
                        setLiveStatus(true);

                        // Update the timestamp on every successful ping to show last sync time
                        let now = new Date();
                        let hours = String(now.getHours()).padStart(2, '0');
                        let minutes = String(now.getMinutes()).padStart(2, '0');
                        let seconds = String(now.getSeconds()).padStart(2, '0');
                        $('#lastUpdateText').text(hours + ':' + minutes + ':' + seconds);

                        if (lastKnownTimestamp === null) {
                            // First ping — just store timestamp, no reload needed
                            lastKnownTimestamp = res.last_updated;
                            return;
                        }

                        if (res.last_updated !== lastKnownTimestamp) {
                            // Data changed — trigger silent reload
                            lastKnownTimestamp = res.last_updated;
                            reloadTableStok(false, true);
                            showUpdateToast(
                                '<i class="bi bi-arrow-repeat me-1 text-success"></i>' +
                                '<strong>Data stok diperbarui</strong><br>' +
                                '<small class="text-muted">Perubahan stok baru terdeteksi.</small>'
                            );
                        }
                    },
                    error: function () {
                        consecutiveErrors++;
                        if (consecutiveErrors >= 3) {
                            setLiveStatus(false);
                        }
                    }
                });
            }

            // Start pinging every 5 seconds
            setInterval(pingLastUpdated, 5000);
            // Initial ping on page load
            setTimeout(pingLastUpdated, 1000);

            // Initial DataTable initialization (Server-Side Yajra DataTables)
            var dt = $('#tableOverview').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('stock-overview.index') }}",
                    data: function (d) {
                        d.search = $('#inputSearch').val();
                        d.gudang_id = $('#filterGudang').val();
                        d.kategori_id = $('#filterKategori').val();
                        d.status = $('#filterStatus').val();
                        d.entitas = currentEntitas;
                        d.cabang_id = currentCabangId;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, class: 'text-muted small text-center text-nowrap align-middle' },
                    { data: 'kode_barang', name: 'master_barang.kode_barang', class: 'fw-semibold text-nowrap align-middle' },
                    { data: 'nama_barang', name: 'master_barang.nama_barang', class: 'fw-medium align-middle' },
                    { data: 'nama_gudang', name: 'master_gudang.nama_gudang', class: 'text-start text-nowrap align-middle' },
                    { data: 'min_stok', name: 'master_barang.minimum_stok', class: 'text-center text-muted text-nowrap align-middle' },
                    { data: 'stok_aktual_fmt', name: 'stok_gudangs.stok_aktual', class: 'text-center fw-bold text-body text-nowrap align-middle' },
                    { data: 'harga_beli_terakhir_fmt', name: 'stok_gudangs.harga_beli_terakhir', class: 'text-end fw-semibold text-body text-nowrap align-middle font-monospace' },
                    { data: 'harga_pokok_rata_rata_fmt', name: 'stok_gudangs.harga_pokok_rata_rata', class: 'text-end fw-semibold text-success text-nowrap align-middle font-monospace' },
                    { data: 'nilai_persediaan_fmt', name: 'nilai_persediaan', orderable: false, searchable: false, class: 'text-end fw-bold text-body text-nowrap align-middle font-monospace' },
                    { data: 'safety_status_badge', name: 'safety_status', orderable: false, searchable: false, class: 'text-center text-nowrap align-middle' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center text-nowrap align-middle' }
                ],
                dom: '<"row mb-2"<"col-sm-6"l><"col-sm-6">>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
                columnDefs: [
                    { targets: [0, 9, 10], searchable: false, orderable: false }
                ],
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ item',
                    search: 'Cari:',
                    paginate: { previous: '←', next: '→' },
                    emptyTable: 'Tidak ada data stok untuk entitas ini.'
                }
            });

            dt.on('processing.dt', function (e, settings, processing) {
                if (processing && isSilentReload) {
                    $('#tableOverview_processing, .dataTables_processing').css('display', 'none');
                }
            });

            dt.on('xhr', function (e, settings, json) {
                if (json) {
                    $('#kpiTotalQty').text(json.totalQty);
                    $('#kpiTotalNilaiPersediaan').text(json.totalNilaiPersediaan);
                    $('#kpiTotalAman').text(json.totalAman);
                    $('#kpiTotalKritis').text(json.totalKritis);
                    $('#kpiTotalKosong').text(json.totalKosong);
                    $('#rowCountBadge').text(json.recordsFiltered + ' Baris Data');

                    // Update Gudang dropdown options dynamically based on active entity
                    if (json.gudangs) {
                        let currentVal = $('#filterGudang').val();
                        let optionsHtml = '<option value="">-- Semua Gudang --</option>';
                        json.gudangs.forEach(function (g) {
                            let isSel = (currentVal == g.id) ? 'selected' : '';
                            optionsHtml += `<option value="${g.id}" ${isSel}>${g.nama_gudang}</option>`;
                        });
                        $('#filterGudang').html(optionsHtml);
                        $('#printGudang').html(optionsHtml);
                    }

                    if (json.activeEntityLabel) {
                        $('#textActiveEntity').text('Menampilkan: ' + json.activeEntityLabel);
                    }

                    // Update Last Update page-wide timestamp
                    let now = new Date();
                    let hours = String(now.getHours()).padStart(2, '0');
                    let minutes = String(now.getMinutes()).padStart(2, '0');
                    let seconds = String(now.getSeconds()).padStart(2, '0');
                    $('#lastUpdateText').text(hours + ':' + minutes + ':' + seconds);
                }
            });
        });

        // Client-side Excel export function (exports current page only for stability on large datasets)
        function exportToExcel() {
            let table = document.getElementById("tableOverview");
            if (!table) return;

            let userCanSeeHpp = @json(!empty($canSeeHpp));

            // Clone table to manipulate columns
            let clone = table.cloneNode(true);
            
            // Remove column indexes: Aksi (10), Status (9), Min. Stok (4)
            // Working backwards to keep original index references intact
            $(clone).find('tr').each(function() {
                $(this).find('th:eq(10), td:eq(10)').remove();
                $(this).find('th:eq(9), td:eq(9)').remove();
                $(this).find('th:eq(4), td:eq(4)').remove();
            });

            // Styling for professional excel view
            $(clone).find('th').css({
                'background-color': '#f1f5f9',
                'color': '#0f172a',
                'font-weight': 'bold',
                'border': '1px solid #cbd5e1',
                'padding': '10px 8px',
                'text-align': 'center',
                'font-size': '11px',
                'text-transform': 'uppercase',
                'white-space': 'nowrap'
            });
            $(clone).find('td').css({
                'border': '1px solid #e2e8f0',
                'padding': '8px 6px',
                'font-size': '11px',
                'vertical-align': 'middle',
                'white-space': 'nowrap'
            });

            // Calculate totals for Excel footer
            let api = $('#tableOverview').DataTable();
            let data = api.rows({ search: 'applied' }).data();
            let totalQty = 0;
            let totalNilai = 0;

            data.each(function (row) {
                let qty = parseFloat(row.stok_aktual) || 0;
                let hpp = parseFloat(row.harga_pokok_rata_rata) || 0;
                totalQty += qty;
                totalNilai += (qty * hpp);
            });

            let formattedTotalQty = (Math.floor(totalQty) === totalQty) ? totalQty.toLocaleString('id-ID') : totalQty.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });

            let nilaiAssetCell = userCanSeeHpp 
                ? `Rp ${totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` 
                : '*** Dibatasi ***';

            // Append footer to clone (8 columns: No, Kode, Nama, Gudang, Stok Aktual, Harga Beli, HPP, Nilai)
            let footerHtml = `
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #64748b;">
                    <td colspan="4" style="text-align: right; font-weight: bold; padding: 10px 8px;">TOTAL RINGKASAN:</td>
                    <td style="text-align: center; font-weight: bold; padding: 10px 8px; color: #0284c7;">
                        ${formattedTotalQty}
                    </td>
                    <td colspan="2" style="text-align: right; font-weight: bold; padding: 10px 8px;">Total Nilai Asset:</td>
                    <td style="text-align: right; font-weight: bold; padding: 10px 8px; color: #0284c7;">
                        ${nilaiAssetCell}
                    </td>
                </tr>
            `;
            $(clone).find('tbody').append(footerHtml);

            let companyName = @json(session('active_company_name') ?: 'SATRIA ERP');
            let isBranchSession = @json(session('is_branch_session', false));
            let activeLabel = $('#textActiveEntity').text().replace('Menampilkan: ', '').trim() || (isBranchSession ? @json(session('active_branch_name')) : 'PT Pusat');
            let gudangName = $('#filterGudang option:selected').text() ? $('#filterGudang option:selected').text().trim() : 'Semua Gudang';
            let kategoriName = $('#filterKategori option:selected').text() ? $('#filterKategori option:selected').text().trim() : 'Semua Kategori';
            let today = new Date().toLocaleDateString('id-ID');

            let headerHtml = `
                <table style="margin-bottom: 20px;">
                    <tr>
                        <td colspan="8" style="font-size: 16px; font-weight: bold; text-align: center; color: #0f172a;">${companyName} - LAPORAN POSISI INVENTARIS STOK (${activeLabel.toUpperCase()})</td>
                    </tr>
                    <tr>
                        <td colspan="8" style="font-size: 10.5px; text-align: center; color: #64748b; font-weight: 500;">
                            Entitas: <strong>${activeLabel}</strong> | Gudang: <strong>${gudangName}</strong> | Kategori: <strong>${kategoriName}</strong> | Tanggal Ekspor: <strong>${today}</strong> ${userCanSeeHpp ? '' : '| (Akses HPP Dibatasi)'}
                        </td>
                    </tr>
                    <tr><td colspan="8"></td></tr>
                </table>
            `;

            let finalHtml = headerHtml + clone.outerHTML;

            // Convert raw HTML table to downloadable Excel file format
            let blob = new Blob(['\ufeff' + finalHtml], {
                type: 'application/vnd.ms-excel;charset=utf-8'
            });

            let safeLabel = activeLabel.replace(/[^a-zA-Z0-9_-]/g, '_');
            let filename = 'Stok_Saat_Ini_' + safeLabel + '_' + new Date().toISOString().slice(0, 10) + '.xlsx';

            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Laporan Stok Saat Ini berhasil diexport ke Excel.',
                timer: 2000,
                showConfirmButton: false,
                position: 'center'
            });
        }
    </script>
    @endpush
</x-layouts>
