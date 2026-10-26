<x-layouts>
    <x-slot:title>Rekap Sisa Tagihan & Utang Supplier</x-slot:title>

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Modul Hutang Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/hutang.css') }}?v={{ file_exists(public_path('css/hutang.css')) ? filemtime(public_path('css/hutang.css')) : time() }}" />
    @endpush

    <div class="container-fluid px-1 px-sm-2 px-md-4 py-2 py-md-3">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="fw-bold mb-0 text-body-emphasis" style="letter-spacing: -0.02em;"> Rekap Utang Supplier</h4>
                        @if(session('active_branch_id'))
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-geo-alt-fill me-1"></i>Cabang: {{ session('active_branch_name') }}
                            </span>
                        @else
                            <span class="badge bg-indigo-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1" style="background: rgba(30, 64, 175, 0.1); color: #1e40af; border-color: rgba(30, 64, 175, 0.25); font-size: 0.72rem;">
                                <i class="bi bi-building me-1"></i>Pusat: {{ session('active_company_name') ?? 'PT Pusat' }}
                            </span>
                        @endif
                    </div>
                    <!-- <p class="text-body-secondary mb-0 d-none d-sm-block" style="font-size:0.79rem;">Monitoring histori faktur pembelian, DP, pelunasan utang, dan analisis umur utang (AP aging).</p> -->
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold shadow-sm d-flex align-items-center gap-1 flex-shrink-0" id="btn_refresh_table">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="d-none d-sm-inline">Refresh</span>
            </button>
        </div>

        <!-- Navigation Tabs: Switcher antara Pembayaran dan Rekap Utang -->
        <div class="mb-4">
            <div class="nav-cinematic">
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-pembayaran-utang'))
                <a href="{{ route('purchase-payments.index') }}" class="nav-link">
                    <i class="bi bi-credit-card-2-front-fill flex-shrink-0"></i>
                    <span class="nav-label-full">Riwayat Pembayaran Utang</span>
                    <span class="nav-label-short">Pembayaran</span>
                </a>
                @endif
                <a href="{{ route('modul_purchasing.hutang.index') }}" class="nav-link active">
                    <i class="bi bi-wallet2 flex-shrink-0"></i>
                    <span class="nav-label-full">Utang Supplier</span>
                    <span class="nav-label-short">Rekap Utang</span>
                </a>
            </div>
        </div>

        @php
            $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
        @endphp

        <!-- KPI Cards Summary Row -->
        <div class="row g-2 g-sm-3 mb-4">
            <!-- Total Utang Berjalan -->
            <div class="col-6 col-lg-3">
                <div class="card kpi-stat-card shadow-sm h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="overflow-hidden pe-1">
                            <div class="kpi-label text-body-secondary fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Total Utang Berjalan</div>
                            <div class="kpi-value fw-bold text-danger font-monospace mt-1" style="font-size:clamp(0.88rem,2.5vw,1.2rem);">Rp {{ $fmtRupiah($kpiStats->total_utang_berjalan ?? 0) }}</div>
                            <div class="kpi-sub text-muted mt-1" style="font-size:0.7rem;">
                                <span class="fw-semibold text-danger">{{ number_format($kpiStats->supplier_unpaid_count ?? 0) }}</span> Supplier Belum Lunas
                            </div>
                        </div>
                        <div class="kpi-icon p-2 rounded-circle bg-danger-subtle text-danger shadow-sm flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Utang Jatuh Tempo -->
            <div class="col-6 col-lg-3">
                <div class="card kpi-stat-card shadow-sm h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="overflow-hidden pe-1">
                            <div class="kpi-label text-body-secondary fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Jatuh Tempo</div>
                            <div class="kpi-value fw-bold text-warning font-monospace mt-1" style="font-size:clamp(0.88rem,2.5vw,1.2rem);">Rp {{ $fmtRupiah($kpiStats->total_utang_overdue ?? 0) }}</div>
                            <div class="kpi-sub text-muted mt-1" style="font-size:0.7rem;">
                                <span class="fw-semibold text-warning">{{ number_format($kpiStats->supplier_overdue_count ?? 0) }}</span> Supplier Menunggak
                            </div>
                        </div>
                        <div class="kpi-icon p-2 rounded-circle bg-warning-subtle text-warning shadow-sm flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Menunggak -->
            <div class="col-6 col-lg-3">
                <div class="card kpi-stat-card shadow-sm h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="overflow-hidden pe-1">
                            <div class="kpi-label text-body-secondary fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Supplier Menunggak</div>
                            <div class="kpi-value fw-bold text-body font-monospace mt-1" style="font-size:clamp(0.88rem,2.5vw,1.2rem);">
                                {{ number_format($kpiStats->supplier_overdue_count ?? 0) }} <span class="text-muted fw-normal" style="font-size:0.78rem;">Vendor</span>
                            </div>
                            <div class="kpi-sub text-muted mt-1" style="font-size:0.7rem;">
                                Dari total <span class="fw-semibold">{{ number_format($kpiStats->supplier_unpaid_count ?? 0) }}</span> berutang
                            </div>
                        </div>
                        <div class="kpi-icon p-2 rounded-circle bg-warning-subtle text-warning-emphasis shadow-sm flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-person-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Pembayaran Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="card kpi-stat-card shadow-sm h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="overflow-hidden pe-1">
                            <div class="kpi-label text-body-secondary fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Pembayaran Bulan Ini</div>
                            <div class="kpi-value fw-bold text-success font-monospace mt-1" style="font-size:clamp(0.88rem,2.5vw,1.2rem);">Rp {{ $fmtRupiah($totalBayarBulanIni) }}</div>
                            <div class="kpi-sub text-muted mt-1" style="font-size:0.7rem;">
                                Periode: <span class="fw-semibold">{{ now()->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                        <div class="kpi-icon p-2 rounded-circle bg-success-subtle text-success shadow-sm flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-check2-all"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Trigger Button Bar & Active Chips -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-filter-trigger d-flex align-items-center gap-2 px-4 py-2 rounded-pill fw-semibold shadow-sm" id="btn_open_filter_modal" data-bs-toggle="modal" data-bs-target="#modalFilterHutang">
                    <i class="bi bi-sliders2 fs-6"></i>
                    <span>Filter Data</span>
                    <span class="badge rounded-pill ms-1 d-none" id="filter_active_badge">0</span>
                </button>
                <div id="filter_active_chips" class="d-flex align-items-center gap-2 flex-wrap"></div>
            </div>

            </div>
        </div>

        <!-- Main Datatable Card -->
        <div class="card payable-table-card border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 d-inline-flex shadow-sm" style="background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.25); color: #6366f1;">
                        <i class="bi bi-wallet2 fs-6"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold text-body-emphasis mb-0" style="letter-spacing: -0.01em;">Daftar Rekap Utang per Supplier</h6>
                        <small class="text-muted d-none d-sm-inline" style="font-size: 0.77rem;">Pantau total faktur, uang muka, riwayat pembayaran &amp; sisa utang berjalan</small>
                    </div>
                </div>
                <div class="text-muted small d-none d-md-flex align-items-center gap-1.5">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;">
                        <i class="bi bi-info-circle me-1 text-primary"></i>Klik baris untuk rincian lengkap
                    </span>
                </div>
            </div>

            <div class="card-body p-3 p-md-4">
                <!-- Top Dual-Scroll Bar -->
                <div class="payable-top-scroll-container mb-2" id="payableTopScroll">
                    <div class="payable-top-scroll-inner" id="payableTopScrollInner"></div>
                </div>

                <!-- Table Wrapper -->
                <div class="payable-table-scroll-wrapper" id="payableTableScrollWrapper">
                    <table class="table table-hover align-middle w-100 table-payable mb-0" id="tablePayable">
                        <thead>
                            <tr>
                                <th width="4%" class="text-center">NO</th>
                                <th width="24%">SUPPLIER</th>
                                <th width="14%" class="text-end">TOTAL FAKTUR</th>
                                <th width="11%" class="text-end">UANG MUKA</th>
                                <th width="12%" class="text-end">TERBAYAR</th>
                                <th width="15%" class="text-end">SISA UTANG</th>
                                <th width="12%" class="text-center">STATUS UMUR</th>
                                <th width="8%" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL FILTER HUTANG — MODERN SANGAR GLASSMORPHIC STYLE (LEBAR & LEGA)
         ══════════════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalFilterHutang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-filter-wide">
            <div class="modal-content rounded-4 border-0 overflow-hidden" style="backdrop-filter: blur(20px);">

                <!-- Modal Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="filter-modal-icon">
                            <i class="bi bi-sliders2 fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Filter Data Hutang</h5>
                            <p class="text-muted small mb-0">Pilih kriteria untuk menyaring data</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 py-3">

                    <!-- Filter Supplier -->
                    <div class="filter-group mb-4">
                        <label class="filter-group-label">
                            <i class="bi bi-building"></i>
                            SUPPLIER / PERUSAHAAN (PT)
                        </label>
                        <select class="form-select" id="filter_supplier_id" style="width: 100%;">
                            <option value="">— Cari atau pilih nama supplier / PT... —</option>
                            @foreach($supplierList as $sup)
                            <option value="{{ $sup->id }}">
                                {{ $sup->kode_supplier ? '[' . $sup->kode_supplier . '] ' : '' }}{{ $sup->nama_supplier }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Aging -->
                    <div class="filter-group mb-2">
                        <label class="filter-group-label">
                            <i class="bi bi-bar-chart-steps"></i>
                            STATUS UMUR UTANG 
                        </label>
                        <div class="aging-option-grid">
                            <label class="aging-option-card" data-value="">
                                <input type="radio" name="filter_aging_radio" value="" class="d-none">
                                <div class="aging-option-icon bg-secondary-subtle text-secondary"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                                <div class="aging-option-text">Semua (Incl. Lunas)</div>
                            </label>
                            <label class="aging-option-card selected" data-value="has_utang">
                                <input type="radio" name="filter_aging_radio" value="has_utang" class="d-none" checked>
                                <div class="aging-option-icon bg-danger-subtle text-danger"><i class="bi bi-hourglass-split"></i></div>
                                <div class="aging-option-text">Belum Lunas</div>
                            </label>
                            <label class="aging-option-card" data-value="current">
                                <input type="radio" name="filter_aging_radio" value="current" class="d-none">
                                <div class="aging-option-icon bg-primary-subtle text-primary"><i class="bi bi-shield-check"></i></div>
                                <div class="aging-option-text">Lancar</div>
                            </label>
                            <label class="aging-option-card" data-value="overdue_1_30">
                                <input type="radio" name="filter_aging_radio" value="overdue_1_30" class="d-none">
                                <div class="aging-option-icon bg-warning-subtle text-warning"><i class="bi bi-clock-history"></i></div>
                                <div class="aging-option-text">1–30 Hari</div>
                            </label>
                            <label class="aging-option-card" data-value="overdue_31_60">
                                <input type="radio" name="filter_aging_radio" value="overdue_31_60" class="d-none">
                                <div class="aging-option-icon bg-orange-subtle text-orange"><i class="bi bi-exclamation-circle"></i></div>
                                <div class="aging-option-text">31–60 Hari</div>
                            </label>
                            <label class="aging-option-card" data-value="overdue_60_plus">
                                <input type="radio" name="filter_aging_radio" value="overdue_60_plus" class="d-none">
                                <div class="aging-option-icon bg-danger-subtle text-danger"><i class="bi bi-fire"></i></div>
                                <div class="aging-option-text">Kritis >60 Hari</div>
                            </label>
                            <label class="aging-option-card" data-value="lunas">
                                <input type="radio" name="filter_aging_radio" value="lunas" class="d-none">
                                <div class="aging-option-icon bg-success-subtle text-success"><i class="bi bi-patch-check-fill"></i></div>
                                <div class="aging-option-text">Lunas</div>
                            </label>
                            <label class="aging-option-card" data-value="all_master">
                                <input type="radio" name="filter_aging_radio" value="all_master" class="d-none">
                                <div class="aging-option-icon bg-info-subtle text-info"><i class="bi bi-people-fill"></i></div>
                                <div class="aging-option-text">Semua Master</div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer with Action Buttons -->
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <div class="d-flex align-items-center justify-content-end w-100 gap-2 pt-3 border-top border-secondary-subtle">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold shadow-sm flex-fill flex-sm-grow-0" id="btn_reset_filter_modal">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-filter-apply rounded-pill px-4 py-2 fw-bold shadow flex-fill flex-sm-grow-0" id="btn_apply_filter" data-bs-dismiss="modal">
                            <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════
         MODAL DETAIL HISTORI FAKTUR & PEMBAYARAN SUPPLIER
         ══════════════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="modalDetailSupplier" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-payable-wide modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content payable-modal-content">
                <!-- Modal Header -->
                <div class="modal-header payable-modal-header text-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="payable-header-icon shadow-sm">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="modal-title fw-bold text-white mb-0" id="modalSupplierName">-</h4>
                                <span class="payable-supplier-badge" id="modalSupplierCode">-</span>
                                <span id="modalSupplierValasBadge" style="display: none;"></span>
                            </div>
                            <div class="text-white-50 small mt-1 d-flex align-items-center gap-2" id="modalSupplierContact">-</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2.5 ms-auto">
                        <a href="javascript:void(0)" class="btn btn-payable-print d-flex align-items-center gap-2" id="btnModalPrintStatement" target="_blank">
                            <i class="bi bi-printer"></i>
                            <span class="d-none d-sm-inline">Cetak Kartu Utang</span>
                        </a>
                        <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body payable-modal-body p-3 p-md-4">
                    
                    <!-- Loading Spinner Indicator -->
                    <div id="modalLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 2.75rem; height: 2.75rem;">
                            <span class="visually-hidden">Memuat data...</span>
                        </div>
                        <div class="text-muted mt-3 small fw-semibold">Memuat rincian faktur &amp; pembayaran...</div>
                    </div>

                    <!-- Modal Content Container -->
                    <div id="modalContentWrapper" style="display: none;">
                        
                        <!-- 4 Summary Highlight Cards (Bento Style) -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-lg-3">
                                <div class="payable-bento-card payable-bento-primary">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-uppercase fw-bold" style="color: #94a3b8; font-size:0.75rem; letter-spacing: 0.06em;">Total Ditagihkan</span>
                                        <div class="bento-icon-pill bento-icon-blue">
                                            <i class="bi bi-receipt-cutoff"></i>
                                        </div>
                                    </div>
                                    <div class="fw-bold font-monospace fs-4" style="color: #f8fafc;" id="summaryTotalInvoiced">Rp 0</div>
                                    <div class="small mt-1 font-monospace" style="color: #94a3b8; font-size: 0.75rem;" id="summaryTotalInvoicedSub">Akumulasi seluruh tagihan faktur</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="payable-bento-card payable-bento-info">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-uppercase fw-bold" style="color: #94a3b8; font-size:0.75rem; letter-spacing: 0.06em;">Uang Muka / DP</span>
                                        <div class="bento-icon-pill bento-icon-cyan">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                    </div>
                                    <div class="fw-bold font-monospace fs-4" style="color: #38bdf8;" id="summaryTotalDp">Rp 0</div>
                                    <div class="small mt-1 font-monospace" style="color: #94a3b8; font-size: 0.75rem;" id="summaryTotalDpSub">Deposit / uang muka dibayarkan</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="payable-bento-card payable-bento-success">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-uppercase fw-bold" style="color: #94a3b8; font-size:0.75rem; letter-spacing: 0.06em;">Total Terbayar</span>
                                        <div class="bento-icon-pill bento-icon-emerald">
                                            <i class="bi bi-check2-circle"></i>
                                        </div>
                                    </div>
                                    <div class="fw-bold font-monospace fs-4" style="color: #34d399;" id="summaryTotalPaid">Rp 0</div>
                                    <div class="small mt-1 font-monospace" style="color: #94a3b8; font-size: 0.75rem;" id="summaryTotalPaidSub">Pelunasan faktur yang tercatat</div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="payable-bento-card payable-bento-danger">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-uppercase fw-bold" style="color: #f87171; font-size:0.75rem; letter-spacing: 0.06em;">Sisa Utang Usaha</span>
                                        <div class="bento-icon-pill bento-icon-rose">
                                            <i class="bi bi-exclamation-octagon"></i>
                                        </div>
                                    </div>
                                    <div class="fw-bold font-monospace fs-4" style="color: #f87171;" id="summarySisaUtang">Rp 0</div>
                                    <div class="small mt-1 font-monospace" style="color: #f87171; font-size: 0.75rem;" id="summarySisaUtangSub">Kewajiban berjalan yang harus dibayar</div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer & PIC Info Bar (Sleek Pod) -->
                        <div class="payable-meta-pod mb-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="payable-meta-item">
                                        <div class="payable-meta-icon text-primary">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </div>
                                        <div>
                                            <div class="text-uppercase fw-semibold" style="color: #94a3b8; font-size: 0.72rem; letter-spacing: 0.05em;">Kontak PIC &amp; Telepon</div>
                                            <div class="fw-bold fs-6 mt-0.5" style="color: #f8fafc;" id="infoSupplierPic">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5">
                                    <div class="payable-meta-item">
                                        <div class="payable-meta-icon text-info">
                                            <i class="bi bi-bank2"></i>
                                        </div>
                                        <div>
                                            <div class="text-uppercase fw-semibold" style="color: #94a3b8; font-size: 0.72rem; letter-spacing: 0.05em;">Rekening Pembayaran Bank</div>
                                            <div class="fw-bold font-monospace fs-6 mt-0.5" style="color: #38bdf8;" id="infoSupplierBank">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="payable-meta-item">
                                        <div class="payable-meta-icon text-secondary">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-uppercase fw-semibold" style="color: #94a3b8; font-size: 0.72rem; letter-spacing: 0.05em;">Termin Pembayaran</div>
                                            <div class="fw-bold fs-6 mt-0.5" style="color: #f8fafc;" id="infoSupplierTerm">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nav Tabs for Unpaid, Paid, and Payment History -->
                        <ul class="nav nav-pills payable-modal-tabs mb-3" role="tablist">
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100 active d-flex align-items-center justify-content-center gap-2" id="tab-unpaid-btn" data-bs-toggle="pill" data-bs-target="#tab-unpaid" type="button" role="tab">
                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    <span>Faktur Belum Lunas</span>
                                    <span class="badge rounded-pill px-2.5 py-0.5" style="background: #ef4444; color: #fff; font-weight: 700; font-size: 0.74rem;" id="badgeCountUnpaid">0</span>
                                </button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2" id="tab-paid-btn" data-bs-toggle="pill" data-bs-target="#tab-paid" type="button" role="tab">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span>Faktur Lunas (Selesai)</span>
                                    <span class="badge rounded-pill px-2.5 py-0.5" style="background: #10b981; color: #fff; font-weight: 700; font-size: 0.74rem;" id="badgeCountPaid">0</span>
                                </button>
                            </li>
                            <li class="nav-item flex-fill" role="presentation">
                                <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2" id="tab-history-btn" data-bs-toggle="pill" data-bs-target="#tab-history" type="button" role="tab">
                                    <i class="bi bi-clock-history text-info"></i>
                                    <span>Riwayat Pembayaran</span>
                                    <span class="badge rounded-pill px-2.5 py-0.5" style="background: #3b82f6; color: #fff; font-weight: 700; font-size: 0.74rem;" id="badgeCountPayments">0</span>
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Panes -->
                        <div class="tab-content">
                            <!-- Tab 1: Faktur Belum Lunas -->
                            <div class="tab-pane fade show active" id="tab-unpaid" role="tabpanel">
                                <div class="payable-modal-table-card">
                                    <div class="table-responsive">
                                        <table class="table payable-modal-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>No. Faktur</th>
                                                    <th>No. Faktur Supplier</th>
                                                    <th>Tanggal</th>
                                                    <th>Jatuh Tempo</th>
                                                    <th class="text-end">Total Tagihan</th>
                                                    <th class="text-end">Uang Muka (DP)</th>
                                                    <th class="text-end">Pelunasan Bayar</th>
                                                    <th class="text-end">Sisa Utang</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyUnpaidInvoices"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Faktur Lunas -->
                            <div class="tab-pane fade" id="tab-paid" role="tabpanel">
                                <div class="payable-modal-table-card">
                                    <div class="table-responsive">
                                        <table class="table payable-modal-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>No. Faktur</th>
                                                    <th>No. Faktur Supplier</th>
                                                    <th>Tanggal</th>
                                                    <th>Jatuh Tempo</th>
                                                    <th class="text-end">Total Tagihan</th>
                                                    <th class="text-end">Uang Muka (DP)</th>
                                                    <th class="text-end">Pelunasan Kas/Bank</th>
                                                    <th class="text-end">Total Pelunasan</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyPaidInvoices"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 3: Riwayat Pembayaran -->
                            <div class="tab-pane fade" id="tab-history" role="tabpanel">
                                <div class="payable-modal-table-card">
                                    <div class="table-responsive">
                                        <table class="table payable-modal-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>No. Pembayaran</th>
                                                    <th>Tanggal</th>
                                                    <th>Kas / Bank</th>
                                                    <th>Metode</th>
                                                    <th class="text-end">Jumlah Dibayar</th>
                                                    <th class="text-end">Potongan</th>
                                                    <th>Faktur Terkait</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyPaymentHistory"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer payable-modal-footer py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="small d-none d-sm-block" style="color: #94a3b8;">
                        <i class="bi bi-shield-check me-1.5 text-primary"></i> Data dihitung secara real-time dan terintegrasi otomatis dengan modul Faktur Pembelian dan Pelunasan Utang.
                    </div>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" style="border-color: rgba(255,255,255,0.2); color: #cbd5e1;" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1.5"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function () {
        // ── State Filter ──────────────────────────────────────────────
        let activeSupplierId   = '';
        let activeSupplierText = '';
        let activeAgingValue   = 'has_utang';
        let activeAgingText    = 'Belum Lunas';

        // ── Select2 Filter Modal ──────────────────────────────────────
        $('#filter_supplier_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari atau pilih nama supplier / PT...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalFilterHutang')
        });

        // ── Aging Option Card: Visual Toggle ─────────────────────────
        $(document).on('click', '.aging-option-card', function() {
            $('.aging-option-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type=radio]').prop('checked', true);
        });

        // ── Update Filter Badge & Chips ───────────────────────────────
        function updateFilterUI() {
            let count = 0;
            const chips = [];

            if (activeSupplierId) {
                count++;
                chips.push(`<span class="filter-chip"><i class="bi bi-building"></i> ${activeSupplierText} <button class="chip-remove" data-clear="supplier">&times;</button></span>`);
            }
            if (activeAgingValue) {
                count++;
                chips.push(`<span class="filter-chip"><i class="bi bi-bar-chart-steps"></i> ${activeAgingText} <button class="chip-remove" data-clear="aging">&times;</button></span>`);
            }

            const $badge = $('#filter_active_badge');
            if (count > 0) {
                $badge.text(count).removeClass('d-none');
                $('#btn_open_filter_modal').addClass('btn-filter-active');
            } else {
                $badge.text(0).addClass('d-none');
                $('#btn_open_filter_modal').removeClass('btn-filter-active');
            }
            $('#filter_active_chips').html(chips.join(''));
        }

        // ── Chip Remove Button ────────────────────────────────────────
        $(document).on('click', '.chip-remove', function() {
            const target = $(this).data('clear');
            if (target === 'supplier') {
                activeSupplierId   = '';
                activeSupplierText = '';
                $('#filter_supplier_id').val('').trigger('change');
            }
            if (target === 'aging') {
                activeAgingValue   = '';
                activeAgingText    = '';
                $('[name=filter_aging_radio][value=""]').prop('checked', true);
                $('.aging-option-card').removeClass('selected');
                $('.aging-option-card[data-value=""]').addClass('selected');
            }
            updateFilterUI();
            table.ajax.reload();
        });

        // ── Apply Filter dari Modal ───────────────────────────────────
        $('#btn_apply_filter').on('click', function() {
            const $selOpt = $('#filter_supplier_id option:selected');
            activeSupplierId   = $('#filter_supplier_id').val() || '';
            activeSupplierText = activeSupplierId ? $selOpt.text().trim() : '';

            const $agingChecked = $('[name=filter_aging_radio]:checked');
            activeAgingValue   = $agingChecked.val() || '';
            activeAgingText    = activeAgingValue
                ? $agingChecked.closest('.aging-option-card').find('.aging-option-text').text().trim()
                : '';

            updateFilterUI();
            table.ajax.reload();
        });

        // ── Reset Filter ──────────────────────────────────────────────
        $('#btn_reset_filter_modal').on('click', function() {
            activeSupplierId   = '';
            activeSupplierText = '';
            activeAgingValue   = 'has_utang';
            activeAgingText    = 'Belum Lunas';
            $('#filter_supplier_id').val('').trigger('change');
            $('[name=filter_aging_radio][value="has_utang"]').prop('checked', true);
            $('.aging-option-card').removeClass('selected');
            $('.aging-option-card[data-value="has_utang"]').addClass('selected');
            updateFilterUI();
            table.ajax.reload();
            $('#modalFilterHutang').modal('hide');
        });

        // Inisialisasi awal UI filter
        updateFilterUI();


        // ══════════════════════════════════════════════════════════════════════
        // DataTables Server-Side Rekap Utang
        // ══════════════════════════════════════════════════════════════════════
        var table = $('#tablePayable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('modul_purchasing.hutang.index') }}",
                data: function (d) {
                    d.supplier_id  = activeSupplierId;
                    d.aging_filter = activeAgingValue;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center text-muted small' },
                { data: 'supplier_info', name: 'supplier_info' },
                { data: 'total_invoiced', name: 'inv.total_invoiced', className: 'text-end' },
                { data: 'total_dp', name: 'inv.total_dp', className: 'text-end' },
                { data: 'total_paid', name: 'inv.total_paid', className: 'text-end' },
                { data: 'sisa_utang', name: 'inv.sisa_utang', className: 'text-end' },
                { data: 'aging_status', name: 'aging_status', className: 'text-center', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[5, 'desc']], // Default urutkan dari sisa utang terbesar
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, 250, -1],
                [10, 25, 50, 100, 250, 'Semua']
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nama / kode supplier...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                zeroRecords: "Tidak ditemukan data utang supplier yang cocok",
                emptyTable: "Belum ada transaksi faktur pembelian",
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data utang...',
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>'
                }
            }
        });

        // Dual Scroll Sync
        const topScroll = document.getElementById('payableTopScroll');
        const topInner = document.getElementById('payableTopScrollInner');
        const wrapper = document.getElementById('payableTableScrollWrapper');

        function syncScrollWidth() {
            if (topInner && wrapper) {
                topInner.style.width = wrapper.scrollWidth + 'px';
            }
        }

        if (topScroll && wrapper) {
            topScroll.addEventListener('scroll', () => { wrapper.scrollLeft = topScroll.scrollLeft; });
            wrapper.addEventListener('scroll', () => { topScroll.scrollLeft = wrapper.scrollLeft; });
            table.on('draw', syncScrollWidth);
            window.addEventListener('resize', syncScrollWidth);
            setTimeout(syncScrollWidth, 300);
        }

        $('#btn_refresh_table').on('click', function () {
            table.ajax.reload(null, false);
        });

        // Klik baris tabel untuk langsung membuka modal detail
        $('#tablePayable tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('.action-cinematic-group, a, button, input, select, .dropdown').length) return;
            const btn = $(this).find('.btn-detail-supplier');
            if (btn.length) {
                btn.first().trigger('click');
            }
        });

        // ══════════════════════════════════════════════════════════════════════
        // MODAL DETAIL SUPPLIER (AJAX)
        // ══════════════════════════════════════════════════════════════════════
        const fmtNumber = (num) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

        // Sanitasi DOM XSS Protection
        const escapeHtml = (str) => {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        $(document).on('click', '.btn-detail-supplier', function () {
            let supplierId = $(this).data('id');
            $('#modalLoadingSpinner').show();
            $('#modalContentWrapper').hide();
            $('#modalDetailSupplier').modal('show');

            // Reset Tabs
            $('#tab-unpaid-btn').tab('show');

            $.ajax({
                url: "{{ url('modul-purchasing/hutang/detail') }}/" + supplierId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        let sup = res.supplier;
                        let sum = res.summary;

                        // Set Header Info (Aman XSS)
                        $('#modalSupplierName').text(sup.nama_supplier);
                        $('#modalSupplierCode').text(sup.kode_supplier || 'SUP-' + sup.id);
                        $('#modalSupplierContact').html(
                            (sup.nama_kontak ? '<i class="bi bi-person me-1"></i>' + escapeHtml(sup.nama_kontak) + ' &bull; ' : '') +
                            (sup.telepon ? '<i class="bi bi-telephone me-1"></i>' + escapeHtml(sup.telepon) : '')
                        );

                        // Set Print URL
                        $('#btnModalPrintStatement').attr('href', "{{ url('modul-purchasing/hutang/print-statement') }}/" + sup.id);

                        // Set Highlight Summaries (Tampilan Valas / Dollar Utama, Rupiah di Bawahnya)
                        if (sum.has_foreign && sum.foreign_sisa_total > 0.01) {
                            $('#summarySisaUtang').text(sum.foreign_symbol + ' ' + fmtNumber(sum.foreign_sisa_total));
                            $('#summarySisaUtangSub').html('≈ Rp ' + fmtNumber(sum.sisa_utang) + ' <span class="opacity-75">(Kewajiban IDR)</span>');
                        } else {
                            $('#summarySisaUtang').text('Rp ' + fmtNumber(sum.sisa_utang));
                            $('#summarySisaUtangSub').text(sum.sisa_utang > 0.01 ? 'Kewajiban berjalan yang harus dibayar' : 'Semua tagihan telah lunas');
                        }

                        if (sum.has_foreign && sum.foreign_invoiced > 0.01) {
                            $('#summaryTotalInvoiced').text(sum.foreign_symbol + ' ' + fmtNumber(sum.foreign_invoiced));
                            $('#summaryTotalInvoicedSub').html('≈ Rp ' + fmtNumber(sum.total_invoiced) + ' <span class="opacity-75">(Total Tagihan IDR)</span>');
                        } else {
                            $('#summaryTotalInvoiced').text('Rp ' + fmtNumber(sum.total_invoiced));
                            $('#summaryTotalInvoicedSub').text('Akumulasi seluruh tagihan faktur');
                        }

                        if (sum.has_foreign && sum.foreign_dp > 0.01) {
                            $('#summaryTotalDp').text(sum.foreign_symbol + ' ' + fmtNumber(sum.foreign_dp));
                            $('#summaryTotalDpSub').html('≈ Rp ' + fmtNumber(sum.total_dp) + ' <span class="opacity-75">(Deposit IDR)</span>');
                        } else {
                            $('#summaryTotalDp').text(sum.has_foreign ? sum.foreign_symbol + ' 0,00' : 'Rp ' + fmtNumber(sum.total_dp));
                            $('#summaryTotalDpSub').text('Deposit / uang muka dibayarkan');
                        }

                        if (sum.has_foreign && sum.foreign_paid > 0.01) {
                            $('#summaryTotalPaid').text(sum.foreign_symbol + ' ' + fmtNumber(sum.foreign_paid));
                            $('#summaryTotalPaidSub').html('≈ Rp ' + fmtNumber(sum.total_paid) + ' <span class="opacity-75">(Pelunasan IDR)</span>');
                        } else {
                            $('#summaryTotalPaid').text(sum.has_foreign ? sum.foreign_symbol + ' 0,00' : 'Rp ' + fmtNumber(sum.total_paid));
                            $('#summaryTotalPaidSub').text('Pelunasan faktur yang tercatat');
                        }

                        // Cek apakah supplier ini bertransaksi dalam Valas (Mata Uang Asing)
                        if (sum.has_foreign) {
                            let currNames = (sum.foreign_currencies || []).join(', ');
                            $('#modalSupplierValasBadge').html('<span class="badge bg-info bg-opacity-25 text-info border border-info rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;"><i class="bi bi-globe me-1"></i>Transaksi Valas (' + currNames + ')</span>').show();
                        } else {
                            $('#modalSupplierValasBadge').hide();
                        }

                        // Set Info Bar
                        let picText = (sup.nama_kontak || '-') + (sup.telepon ? ' (' + sup.telepon + ')' : '');
                        $('#infoSupplierPic').text(picText);

                        let bankText = sup.bank_name !== '-' 
                            ? sup.bank_name + ' - No. ' + sup.bank_rekening + ' (a.n ' + sup.bank_atas_nama + ')'
                            : 'Belum diatur pada Master Supplier';
                        $('#infoSupplierBank').text(bankText);

                        $('#infoSupplierTerm').text(sup.term_pembayaran || 'Net 30');

                        // Set Badges Count (Hanya yang Posted/Sah)
                        let validPayments = (res.payment_history || []).filter(p => p.status === 'Posted');
                        $('#badgeCountUnpaid').text(res.unpaid_invoices.length);
                        $('#badgeCountPaid').text(res.paid_invoices.length);
                        $('#badgeCountPayments').text(validPayments.length);

                        // ── Tab 1: Render Unpaid Invoices ──
                        let htmlUnpaid = '';
                        if (res.unpaid_invoices.length === 0) {
                            htmlUnpaid = `
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-patch-check-fill text-success fs-1 d-block mb-2"></i>
                                            <div class="fw-bold fs-6" style="color: #f8fafc;">Tidak Ada Faktur Belum Lunas</div>
                                            <div class="small" style="color: #94a3b8;">Semua tagihan pembelian ke supplier ini telah selesai dilunasi!</div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } else {
                            res.unpaid_invoices.forEach(inv => {
                                let statusBadge = inv.is_overdue 
                                    ? `<span class="badge-pay-overdue"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat ${inv.days_overdue} Hari</span>`
                                    : `<span class="badge-pay-unpaid"><i class="bi bi-hourglass-split me-1"></i>Belum Lunas</span>`;

                                let curSym = escapeHtml(inv.currency_symbol || '$');
                                let curCod = escapeHtml(inv.currency_code || 'USD');
                                let noFak  = escapeHtml(inv.nomor_faktur);
                                let noSup  = escapeHtml(inv.nomor_faktur_supplier || '-');

                                let noFakturCell = inv.is_foreign
                                    ? `<div><span class="fw-bold font-monospace" style="color: #60a5fa;">${noFak}</span> <span class="badge rounded-pill px-2 py-0.5 ms-1" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); font-size:0.68rem; font-family: var(--bs-font-monospace);"><i class="bi bi-globe me-1"></i>${curCod}</span></div>`
                                    : `<span class="fw-bold font-monospace" style="color: #60a5fa;">${noFak}</span>`;

                                let tagihanCell = inv.is_foreign
                                    ? `<div class="fw-bold font-monospace" style="color: #f8fafc;">${curSym} ${fmtNumber(inv.grand_total)}</div>
                                       <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.grand_total_idr)} <span class="badge" style="background: rgba(255,255,255,0.06); color: #94a3b8; font-size:0.68rem;">@${fmtNumber(inv.kurs)}</span></div>`
                                    : `<div class="fw-bold font-monospace" style="color: #f8fafc;">Rp ${fmtNumber(inv.grand_total)}</div>`;

                                let dpCell = inv.is_foreign
                                    ? (inv.uang_muka > 0
                                        ? `<div class="fw-semibold font-monospace" style="color: #38bdf8;">${curSym} ${fmtNumber(inv.uang_muka)}</div>
                                           <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.uang_muka_idr)}</div>`
                                        : '<span class="font-monospace" style="color: #64748b;">-</span>')
                                    : (inv.uang_muka > 0 ? `<span class="fw-semibold font-monospace" style="color: #38bdf8;">Rp ${fmtNumber(inv.uang_muka)}</span>` : '<span class="font-monospace" style="color: #64748b;">-</span>');

                                let paidCell = inv.is_foreign
                                    ? (inv.paid_amount > 0
                                        ? `<div class="fw-semibold font-monospace" style="color: #34d399;">${curSym} ${fmtNumber(inv.paid_amount)}</div>
                                           <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.paid_amount_idr)}</div>`
                                        : '<span class="font-monospace" style="color: #64748b;">-</span>')
                                    : (inv.paid_amount > 0 ? `<span class="fw-semibold font-monospace" style="color: #34d399;">Rp ${fmtNumber(inv.paid_amount)}</span>` : '<span class="font-monospace" style="color: #64748b;">-</span>');

                                let sisaCell = inv.is_foreign
                                    ? `<div class="fw-bold font-monospace fs-6" style="color: #f87171;">${curSym} ${fmtNumber(inv.sisa_utang)}</div>
                                       <div class="small font-monospace" style="color: #f87171; opacity: 0.85; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.sisa_utang_idr)}</div>`
                                    : `<div class="fw-bold font-monospace fs-6" style="color: #f87171;">Rp ${fmtNumber(inv.sisa_utang)}</div>`;

                                htmlUnpaid += `
                                    <tr>
                                        <td>${noFakturCell}</td>
                                        <td><span class="badge-pay-supplier-inv">${noSup}</span></td>
                                        <td><span style="color: #cbd5e1;"><i class="bi bi-calendar3 me-1.5 opacity-50"></i>${escapeHtml(inv.tanggal_faktur)}</span></td>
                                        <td><span class="${inv.is_overdue ? 'fw-bold' : ''}" style="color: ${inv.is_overdue ? '#f87171' : '#cbd5e1'};"><i class="bi bi-clock me-1.5 opacity-50"></i>${escapeHtml(inv.tanggal_jatuh_tempo)}</span></td>
                                        <td class="text-end">${tagihanCell}</td>
                                        <td class="text-end">${dpCell}</td>
                                        <td class="text-end">${paidCell}</td>
                                        <td class="text-end">${sisaCell}</td>
                                        <td class="text-center">${statusBadge}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#tbodyUnpaidInvoices').html(htmlUnpaid);

                        // ── Tab 2: Render Paid Invoices ──
                        let htmlPaid = '';
                        if (res.paid_invoices.length === 0) {
                            htmlPaid = `
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox text-secondary fs-1 d-block mb-2"></i>
                                            <div class="fw-bold fs-6" style="color: #f8fafc;">Belum Ada Riwayat Faktur Lunas</div>
                                            <div class="small" style="color: #94a3b8;">Faktur pembelian yang telah selesai dilunasi akan tersimpan di sini.</div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } else {
                            res.paid_invoices.forEach(inv => {
                                let curSym = escapeHtml(inv.currency_symbol || '$');
                                let curCod = escapeHtml(inv.currency_code || 'USD');
                                let noFak  = escapeHtml(inv.nomor_faktur);
                                let noSup  = escapeHtml(inv.nomor_faktur_supplier || '-');

                                let noFakturCell = inv.is_foreign
                                    ? `<div><span class="fw-bold font-monospace" style="color: #f8fafc;">${noFak}</span> <span class="badge rounded-pill px-2 py-0.5 ms-1" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); font-size:0.68rem; font-family: var(--bs-font-monospace);"><i class="bi bi-globe me-1"></i>${curCod}</span></div>`
                                    : `<span class="fw-bold font-monospace" style="color: #f8fafc;">${noFak}</span>`;

                                let tagihanCell = inv.is_foreign
                                    ? `<div class="fw-bold font-monospace" style="color: #f8fafc;">${curSym} ${fmtNumber(inv.grand_total)}</div>
                                       <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.grand_total_idr)} <span class="badge" style="background: rgba(255,255,255,0.06); color: #94a3b8; font-size:0.68rem;">@${fmtNumber(inv.kurs)}</span></div>`
                                    : `<div class="fw-bold font-monospace" style="color: #f8fafc;">Rp ${fmtNumber(inv.grand_total)}</div>`;

                                let dpCell = inv.is_foreign && inv.uang_muka > 0
                                    ? `<div class="font-monospace fw-semibold" style="color: #38bdf8;">${curSym} ${fmtNumber(inv.uang_muka)}</div>
                                       <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.uang_muka_idr)}</div>`
                                    : (inv.uang_muka > 0 ? `<span class="font-monospace fw-semibold" style="color: #38bdf8;">Rp ${fmtNumber(inv.uang_muka)}</span>` : '<span class="font-monospace" style="color: #64748b;">-</span>');

                                let bayarCell = inv.is_foreign && inv.paid_amount > 0
                                    ? `<div class="font-monospace fw-semibold" style="color: #34d399;">${curSym} ${fmtNumber(inv.paid_amount)}</div>
                                       <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(inv.paid_amount_idr)}</div>`
                                    : (inv.paid_amount > 0 ? `<span class="font-monospace fw-semibold" style="color: #34d399;">Rp ${fmtNumber(inv.paid_amount)}</span>` : '<span class="font-monospace" style="color: #64748b;">-</span>');

                                let pelunasanTotal = inv.paid_amount + inv.uang_muka;
                                let pelunasanTotalIdr = inv.paid_amount_idr + inv.uang_muka_idr;
                                let totalLunasCell = inv.is_foreign
                                    ? `<div class="fw-bold font-monospace" style="color: #34d399;">${curSym} ${fmtNumber(pelunasanTotal)}</div>
                                       <div class="small font-monospace" style="color: #94a3b8; font-size:0.72rem;">≈ Rp ${fmtNumber(pelunasanTotalIdr)}</div>`
                                    : `<div class="fw-bold font-monospace" style="color: #34d399;">Rp ${fmtNumber(pelunasanTotal)}</div>`;

                                htmlPaid += `
                                    <tr>
                                        <td>${noFakturCell}</td>
                                        <td><span class="badge-pay-supplier-inv">${noSup}</span></td>
                                        <td><span style="color: #cbd5e1;"><i class="bi bi-calendar3 me-1.5 opacity-50"></i>${escapeHtml(inv.tanggal_faktur)}</span></td>
                                        <td><span style="color: #cbd5e1;"><i class="bi bi-clock me-1.5 opacity-50"></i>${escapeHtml(inv.tanggal_jatuh_tempo)}</span></td>
                                        <td class="text-end">${tagihanCell}</td>
                                        <td class="text-end">${dpCell}</td>
                                        <td class="text-end">${bayarCell}</td>
                                        <td class="text-end">${totalLunasCell}</td>
                                        <td class="text-center"><span class="badge-pay-lunas"><i class="bi bi-check-all me-1"></i>Lunas</span></td>
                                    </tr>
                                `;
                            });
                        }
                        $('#tbodyPaidInvoices').html(htmlPaid);

                        // ── Tab 3: Render Payment History (Hanya Posted) ──
                        let htmlPay = '';
                        if (validPayments.length === 0) {
                            htmlPay = `
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-credit-card-2-front text-secondary fs-1 d-block mb-2"></i>
                                            <div class="fw-bold fs-6" style="color: #f8fafc;">Belum Ada Riwayat Pembayaran</div>
                                            <div class="small" style="color: #94a3b8;">Belum ada transaksi pembayaran utang yang diposting ke supplier ini.</div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } else {
                            validPayments.forEach(pay => {
                                let badgePay = '<span class="badge-pay-status-posted"><i class="bi bi-check-circle-fill me-1"></i>Posted</span>';

                                let isForeign = !!pay.is_foreign;
                                let curSym    = escapeHtml(pay.currency_symbol || '$');
                                let curCode   = escapeHtml(pay.currency_code || 'USD');
                                let kursVal   = parseFloat(pay.kurs || 1);
                                let noPay     = escapeHtml(pay.nomor_pembayaran);

                                // No. Pembayaran cell (tampilkan pill valas jika bukan IDR)
                                let noPayCell = isForeign
                                    ? `<div><span class="fw-bold font-monospace" style="color: #60a5fa;">${noPay}</span> <span class="badge rounded-pill px-2 py-0.5 ms-1" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); font-size:0.68rem; font-family: var(--bs-font-monospace);"><i class="bi bi-globe me-1"></i>${curCode}</span></div>`
                                    : `<span class="fw-bold font-monospace" style="color: #60a5fa;">${noPay}</span>`;

                                // Jumlah Dibayar: Jika valas / dolar, tampilkan nominal valas, konversi IDR, dan KURS YANG DIBAYARKAN
                                let bayarCell = '';
                                if (isForeign) {
                                    bayarCell = `
                                        <div class="text-end">
                                            <div class="fw-bold font-monospace" style="color: #34d399; font-size: 0.90rem;">
                                                ${curSym} ${fmtNumber(pay.total_bayar)}
                                            </div>
                                            <div class="small font-monospace" style="color: #94a3b8; font-size: 0.73rem;">
                                                ≈ Rp ${fmtNumber(pay.total_bayar_idr || (pay.total_bayar * kursVal))}
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge-pay-kurs" title="Kurs saat pembayaran dibayarkan">
                                                    <i class="bi bi-currency-exchange"></i>Kurs: Rp ${fmtNumber(kursVal)}
                                                </span>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    bayarCell = `
                                        <div class="text-end font-monospace fw-bold" style="color: #34d399; font-size: 0.88rem;">
                                            Rp ${fmtNumber(pay.total_bayar)}
                                        </div>
                                    `;
                                }

                                // Potongan Cell
                                let potonganCell = '';
                                if (pay.total_potongan && parseFloat(pay.total_potongan) > 0.01) {
                                    if (isForeign) {
                                        potonganCell = `
                                            <div class="text-end">
                                                <div class="font-monospace fw-semibold" style="color: #fbbf24;">
                                                    ${curSym} ${fmtNumber(pay.total_potongan)}
                                                </div>
                                                <div class="small font-monospace" style="color: #94a3b8; font-size: 0.72rem;">
                                                    ≈ Rp ${fmtNumber(pay.total_potongan_idr || (pay.total_potongan * kursVal))}
                                                </div>
                                            </div>
                                        `;
                                    } else {
                                        potonganCell = `<div class="text-end font-monospace fw-semibold" style="color: #fbbf24;">Rp ${fmtNumber(pay.total_potongan)}</div>`;
                                    }
                                } else {
                                    potonganCell = `<div class="text-end font-monospace" style="color: #64748b;">-</div>`;
                                }

                                let fakturCell = pay.faktur_list
                                    ? `<span class="font-monospace small" style="color: #cbd5e1;"><i class="bi bi-file-earmark-text me-1 text-primary opacity-75"></i>${escapeHtml(pay.faktur_list)}</span>`
                                    : `<span class="font-monospace" style="color: #64748b;">-</span>`;

                                htmlPay += `
                                    <tr>
                                        <td>${noPayCell}</td>
                                        <td><span style="color: #cbd5e1;"><i class="bi bi-calendar3 me-1.5 opacity-50"></i>${escapeHtml(pay.tanggal_bayar)}</span></td>
                                        <td><span class="badge-pay-bank"><i class="bi bi-wallet2 me-1"></i>${escapeHtml(pay.kas_bank)}</span></td>
                                        <td><span class="badge-pay-method"><i class="bi bi-credit-card me-1 opacity-75"></i>${escapeHtml(pay.metode_bayar)}</span></td>
                                        <td>${bayarCell}</td>
                                        <td>${potonganCell}</td>
                                        <td>${fakturCell}</td>
                                        <td class="text-center">${badgePay}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#tbodyPaymentHistory').html(htmlPay);

                        $('#modalLoadingSpinner').hide();
                        $('#modalContentWrapper').fadeIn(200);
                    }
                },
                error: function (xhr) {
                    $('#modalLoadingSpinner').hide();
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal memuat rincian data supplier.', 'error');
                }
            });
        });
    });
    </script>
    @endpush
</x-layouts>
