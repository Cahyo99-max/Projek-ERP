<x-layouts title="Manajemen Proyek Tender & Kontrak">

    @push('styles')
    <link rel="stylesheet" href="{{ asset('modul_smd/tender.css') }}?v={{ time() }}">
    <style>
        .customer-lookup-card.is-hidden {
            display: none !important;
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
                        Manajemen Proyek Tender
                    </h4>
                </div>
                <div class="text-muted small">Manajemen data kontrak, spesifikasi barang, penawaran harga, negosiasi, hingga status lelang aktif.</div>
            </div>

            <!-- Action Buttons & View Switcher (Fully Responsive on Mobile & Desktop) -->
            <div class="tender-header-actions d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2.5 w-100 w-md-auto">
                <div class="tender-view-pills d-flex w-100 w-sm-auto">
                    <button type="button" class="tender-view-btn flex-fill justify-content-center active" id="btn_view_kanban">
                        <i class="bi bi-kanban-fill"></i> <span>Papan Pipeline</span>
                    </button>
                    <button type="button" class="tender-view-btn flex-fill justify-content-center" id="btn_view_table">
                        <i class="bi bi-table"></i> <span>Tabel Data</span>
                    </button>
                </div>

                @can('tambah-tender')
                <button type="button" class="btn btn-success btn-tambah-tender-main rounded-pill px-3.5 py-2 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalTambahTender">
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
                            <div class="fs-2 fw-bold mt-1 text-body font-monospace" id="kpi_count_total">{{ number_format($countTotal) }}</div>
                            <div class="text-muted small mt-1" id="kpi_count_active"><i class="bi bi-clock-history me-1"></i>{{ $countActive }} Proyek Aktif Berjalan</div>
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
                            <div class="fs-2 fw-bold mt-1 text-success font-monospace" id="kpi_count_won">{{ number_format($countWon) }} <span class="fs-6 text-muted fw-normal">Proyek</span></div>
                            <div class="text-success small fw-bold mt-1" id="kpi_win_rate"><i class="bi bi-trophy-fill me-1"></i>Win Rate: {{ $winRate }}%</div>
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
                            <div class="fs-4 fw-bold mt-1 text-success font-monospace" id="kpi_value_won">Rp {{ number_format($valueWon, 0, ',', '.') }}</div>
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
                            <div class="fs-4 fw-bold mt-1 text-info font-monospace" id="kpi_value_pipeline">Rp {{ number_format($valuePipeline, 0, ',', '.') }}</div>
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
                    <p class="text-muted mt-2 small">Memuat Tahapan Pipeline Tender...</p>
                </div>
            </div>
        </div>

        <!-- ===== VIEW 2: DATATABLES TABLE VIEW ===== -->
        <div id="section_table_view" class="d-none">
            <div class="tender-table-card overflow-hidden mb-4">
                <div class="tender-table-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                            <i class="bi bi-table fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-body fs-6">Daftar Rekapitulasi Proyek Tender</h6>
                            <div class="text-muted small" style="font-size:0.78rem;">Manajemen data kontrak, penawaran harga & status lelang aktif</div>
                        </div>
                    </div>
                    
                    <div class="tender-filter-container d-flex flex-wrap align-items-center gap-2">
                        <!-- Quick Status Filter Pills for Fast Switching -->
                        <div class="tender-filter-pills-group d-none d-xl-inline-flex align-items-center me-1">
                            <button type="button" class="tender-quick-filter-btn active" data-status="all">
                                <span>Semua</span>
                            </button>
                            <button type="button" class="tender-quick-filter-btn" data-status="Draft">
                                <span class="dot-indicator dot-draft"></span>
                                <span>Draft</span>
                            </button>
                            <button type="button" class="tender-quick-filter-btn" data-status="Pending Approval">
                                <span class="dot-indicator dot-review"></span>
                                <span>Review</span>
                            </button>
                            <button type="button" class="tender-quick-filter-btn" data-status="Negotiation">
                                <span class="dot-indicator dot-negotiation"></span>
                                <span>Negosiasi</span>
                            </button>
                            <button type="button" class="tender-quick-filter-btn" data-status="Won">
                                <span class="dot-indicator dot-won"></span>
                                <span>Won</span>
                            </button>
                            <button type="button" class="tender-quick-filter-btn" data-status="Lost">
                                <span class="dot-indicator dot-lost"></span>
                                <span>Lost</span>
                            </button>
                        </div>

                        <!-- Modern Custom Dropdown: Status Filter -->
                        <div class="dropdown">
                            <button type="button" class="tender-modern-filter-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="btn_filter_status">
                                <i class="bi bi-funnel-fill text-success"></i>
                                <span id="label_filter_status">Semua Status</span>
                                <i class="bi bi-chevron-down chevron-icon"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end tender-custom-dropdown-menu shadow-lg">
                                <li class="dropdown-header text-uppercase">Status Proyek Tender</li>
                                <li>
                                    <a class="dropdown-item tender-filter-item active" href="javascript:void(0)" data-filter-type="status" data-value="all" data-label="Semua Status">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-grid-fill text-muted"></i>
                                            <span>Semua Status</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Draft" data-label="Draft">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-draft"></span>
                                            <span>Draft (Penyusunan)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Pending Approval" data-label="Review">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-review"></span>
                                            <span>Menunggu Approval</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Approved" data-label="Disetujui">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-approved"></span>
                                            <span>Disetujui (Approved)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Rejected" data-label="Ditolak">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-rejected"></span>
                                            <span>Ditolak (Rejected)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Sent" data-label="Sent">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-sent"></span>
                                            <span>Sent (Penawaran Dikirim)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Negotiation" data-label="Negosiasi">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-negotiation"></span>
                                            <span>Negosiasi / Aanwijzing</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Won" data-label="Won">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-won"></span>
                                            <span>Won (Menang / SPK)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="status" data-value="Lost" data-label="Lost">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="status-dot dot-lost"></span>
                                            <span>Lost (Kalah Lelang)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Modern Custom Dropdown: Priority Filter -->
                        <div class="dropdown">
                            <button type="button" class="tender-modern-filter-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="btn_filter_priority">
                                <i class="bi bi-fire text-warning"></i>
                                <span id="label_filter_priority">Semua Prioritas</span>
                                <i class="bi bi-chevron-down chevron-icon"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end tender-custom-dropdown-menu shadow-lg">
                                <li class="dropdown-header text-uppercase">Tingkat Prioritas</li>
                                <li>
                                    <a class="dropdown-item tender-filter-item active" href="javascript:void(0)" data-filter-type="priority" data-value="all" data-label="Semua Prioritas">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-lightning-charge text-muted"></i>
                                            <span>Semua Prioritas</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="priority" data-value="High" data-label="Tinggi (High)">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge badge-priority-high px-2 py-0.5 rounded-pill"><i class="bi bi-fire me-1"></i>High</span>
                                            <span>Tinggi (Prioritas Utama)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="priority" data-value="Medium" data-label="Sedang (Medium)">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge badge-priority-medium px-2 py-0.5 rounded-pill"><i class="bi bi-dash-circle me-1"></i>Medium</span>
                                            <span>Sedang (Standar)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item tender-filter-item" href="javascript:void(0)" data-filter-type="priority" data-value="Low" data-label="Rendah (Low)">
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="badge badge-priority-low px-2 py-0.5 rounded-pill"><i class="bi bi-arrow-down-circle me-1"></i>Low</span>
                                            <span>Rendah (Opsional)</span>
                                        </span>
                                        <i class="bi bi-check2 check-icon"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Reset Filter Button -->
                        <button type="button" class="btn-tender-filter-reset d-none" id="btn_reset_tender_filters" title="Reset Semua Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset</span>
                        </button>

                        <!-- Hidden Inputs to preserve state & AJAX parameters -->
                        <input type="hidden" id="table_filter_status" value="all">
                        <input type="hidden" id="table_filter_priority" value="all">
                    </div>
                </div>

                <div class="tender-table-wrapper">
                    <div class="tender-table-inner">
                        <div class="table-responsive mb-0">
                            <table class="table align-middle mb-0 w-100 tender-modern-table" id="tableTender">
                                <thead>
                                    <tr>
                                        <th class="text-center ps-3" width="4%">NO</th>
                                        <th width="14%">NO. KONTRAK / RKS</th>
                                        <th width="21%">NAMA PROYEK & KLIEN</th>
                                        <th width="11%">ENTITAS</th>
                                        <th width="11%">KATEGORI INSTANSI</th>
                                        <th class="text-end" width="13%">NILAI ESTIMASI (IDR)</th>
                                        <th class="text-center" width="10%">DEADLINE</th>
                                        <th class="text-center" width="8%">PRIORITAS</th>
                                        <th class="text-center" width="9%">STATUS</th>
                                        <th class="text-center pe-3" width="7%">AKSI</th>
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
                            $searchPayload = mb_strtolower(implode(' ', array_filter([
                                $corpName,
                                $c->nama_perusahaan,
                                $c->nama_customer,
                                $c->kode_customer,
                                $c->telepon,
                                $c->hp,
                                $c->email,
                                $c->jenis_customer,
                                $c->alamat,
                            ])));
                        @endphp
                        <div class="customer-lookup-card customer-lookup-row d-flex align-items-center justify-content-between gap-3" 
                             data-id="{{ $c->id }}" 
                             data-name="{{ $corpName }}"
                             data-pic="{{ $c->nama_customer }}"
                             data-search="{{ $searchPayload }}">
                            
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
                        <input type="text" id="search_barang_tender_input" class="form-control tender-input rounded-pill ps-5 py-2.5 shadow-xs" placeholder="Ketik nama barang, kode SKU, atau kategori..." autocomplete="off">
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

                    <div class="table-responsive border rounded-4 tender-table-responsive bg-body" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="table_lookup_barang_tender" style="cursor: pointer; min-width: 620px;">
                            <thead class="bg-body-tertiary sticky-top border-bottom">
                                <tr>
                                    <th class="ps-4 py-3 fw-bold text-muted small" style="min-width: 250px;">PRODUK & SPESIFIKASI</th>
                                    <th class="py-3 fw-bold text-muted small" style="min-width: 140px;">KATEGORI</th>
                                    <th class="text-center py-3 fw-bold text-muted small" style="min-width: 90px;">SATUAN</th>
                                    <th class="text-center py-3 pe-4 fw-bold text-muted small" style="min-width: 90px;">AKSI</th>
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

        window.userPermissions = {
            canEdit: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('edit-tender')) : false),
            canDelete: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('hapus-tender')) : false),
            canApprove: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('approve-tender')) : false),
            canKirim: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('kirim-lelang-tender')) : false),
            canNego: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('negosiasi-tender')) : false),
            canFinal: @json(auth()->user() ? (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('keputusan-final-tender')) : false),
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

            // ===== GLOBAL REALTIME PIPELINE KANBAN & KPI LOADER =====
            window.loadKanbanBoard = function (showSpinner = false) {
                if (showSpinner) {
                    $('#kanban_board_container').html(`
                        <div class="w-100 text-center py-5">
                            <div class="spinner-border text-success" role="status"></div>
                            <p class="text-muted mt-2 small">Memuat Tahapan Pipeline Tender...</p>
                        </div>
                    `);
                }

                let filterEntitas = $('#filter_entitas').length ? $('#filter_entitas').val() : 'all';
                let filterStatus = $('#table_filter_status').length ? $('#table_filter_status').val() : 'all';
                let filterPriority = $('#table_filter_priority').length ? $('#table_filter_priority').val() : 'all';

                $.get('{{ route("tender.kanban-data") }}', {
                    filter_entitas: filterEntitas,
                    filter_status: filterStatus,
                    filter_priority: filterPriority
                }, function (res) {
                    // Update KPI Metrics Realtime
                    if (res.metrics) {
                        $('#kpi_count_total').text(Number(res.metrics.count_total).toLocaleString('id-ID'));
                        $('#kpi_count_active').html(`<i class="bi bi-clock-history me-1"></i>${res.metrics.count_active} Proyek Aktif Berjalan`);
                        $('#kpi_count_won').html(`${Number(res.metrics.count_won).toLocaleString('id-ID')} <span class="fs-6 text-muted fw-normal">Proyek</span>`);
                        $('#kpi_win_rate').html(`<i class="bi bi-trophy-fill me-1"></i>Win Rate: ${res.metrics.win_rate}%`);
                        $('#kpi_value_won').text(res.metrics.value_won_formatted);
                        $('#kpi_value_pipeline').text(res.metrics.value_pipeline_formatted);
                    }

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

                                let entitasBadge = t.branch_id
                                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.65rem;"><i class="bi bi-shop me-0.5"></i>${escapeHtml(t.entitas_label)}</span>`
                                    : `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.65rem;"><i class="bi bi-building me-0.5"></i>PT (Pusat)</span>`;

                                html += `
                                    <div class="kanban-card btn-view-detail" data-id="${t.id}">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.7rem;">${escapeHtml(t.nomor_kontrak)}</span>
                                            <div class="d-flex align-items-center gap-1">
                                                ${entitasBadge}
                                                ${prioBadge}
                                            </div>
                                        </div>
                                        <div class="kanban-card-title">${escapeHtml(t.nama_tender)}</div>
                                        <div class="text-muted small mb-2 text-truncate"><i class="bi bi-building me-1"></i>${escapeHtml(t.customer_name)}</div>
                                        
                                        <div class="kanban-card-value text-success mb-2">
                                            ${formatRp(t.nilai_estimasi)}
                                            ${(t.status === 'Won' && t.nilai_deal > 0 && t.pagu_awal > 0 && t.nilai_deal !== t.pagu_awal) ? `<div class="text-muted font-monospace" style="font-size:0.68rem;">Pagu: ${formatRp(t.pagu_awal)}</div>` : ''}
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
            };

            // Global helper to refresh both Kanban & DataTables simultaneously
            window.refreshTenderViews = function () {
                window.loadKanbanBoard(false);
                if ($.fn.DataTable.isDataTable('#tableTender')) {
                    $('#tableTender').DataTable().ajax.reload(null, false);
                }
            };

            // Initial load Kanban
            window.loadKanbanBoard(true);

            // Auto-Refresh Realtime Multi-User (Setiap 15 Detik di latar belakang secara hening / silent)
            setInterval(function () {
                if (!$('.modal.show').length) {
                    if (!$('#section_kanban_view').hasClass('d-none')) {
                        window.loadKanbanBoard(false);
                    } else if (!$('#section_table_view').hasClass('d-none') && $.fn.DataTable.isDataTable('#tableTender')) {
                        $('#tableTender').DataTable().ajax.reload(null, false);
                    }
                }
            }, 15000);

            // ===== DATATABLES INITIALIZATION =====
            let tableTender = $('#tableTender').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: '{{ route("tender.data") }}',
                    data: function (d) {
                        d.filter_entitas = $('#filter_entitas').length ? $('#filter_entitas').val() : 'all';
                        d.filter_status = $('#table_filter_status').val() || 'all';
                        d.filter_priority = $('#table_filter_priority').val() || 'all';
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
                            return `<a href="javascript:void(0)" class="btn-view-detail badge-kontrak-tender text-decoration-none" data-id="${row.id}" title="Klik untuk lihat detail"><i class="bi bi-file-earmark-text me-1"></i>${escapeHtml(data)}</a>`;
                        }
                    },
                    { 
                        data: 'nama_tender', 
                        name: 'nama_tender',
                        render: function (data, type, row) {
                            let custName = (row.customer_name || '').trim();
                            custName = custName.replace(/^(\s*PT[\.\s]+)+/i, 'PT ');

                            return `
                                <div>
                                    <div class="fw-bold text-body btn-view-detail cursor-pointer d-flex align-items-center gap-1.5 flex-wrap" data-id="${row.id}" title="Klik untuk lihat detail">
                                        <span class="hover-primary transition" style="font-size:0.88rem;">${escapeHtml(data)}</span>
                                    </div>
                                    <div class="text-muted small mt-1 d-flex align-items-center gap-2 flex-wrap" style="font-size:0.75rem;">
                                        <span class="d-inline-flex align-items-center text-truncate" style="max-width:240px;"><i class="bi bi-building me-1 text-primary"></i> ${escapeHtml(custName)}</span>
                                        <span class="text-muted opacity-50">•</span>
                                        <span class="d-inline-flex align-items-center"><i class="bi bi-person text-secondary me-1"></i> ${escapeHtml(row.sales_name)}</span>
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
                            return row.entitas_badge || '<span class="badge badge-entitas-pt px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;"><i class="bi bi-building me-1"></i>PT (Pusat)</span>';
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
                        render: function (data, type, row) {
                            let dealVal = Number(row.nilai_deal) || 0;
                            let estVal = Number(data) || 0;
                            if (row.status === 'Won' && dealVal > 0) {
                                return `
                                    <div class="font-monospace fw-bold text-success" style="font-size:0.92rem;"><span style="font-size:0.74rem;" class="text-success-emphasis me-1">Rp</span>${dealVal.toLocaleString('id-ID')}</div>
                                    ${dealVal !== estVal && estVal > 0 ? `<div class="text-muted font-monospace" style="font-size:0.68rem;" title="Pagu Awal">Pagu: Rp ${estVal.toLocaleString('id-ID')}</div>` : ''}
                                `;
                            }
                            if (estVal > 0) {
                                return `<div class="font-monospace fw-bold text-success" style="font-size:0.92rem;"><span style="font-size:0.74rem;" class="text-success-emphasis me-1">Rp</span>${estVal.toLocaleString('id-ID')}</div>`;
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
                            let editBtn = (window.userPermissions && window.userPermissions.canEdit) ? `
                                <button type="button" class="btn-table-action btn-action-edit btn-edit-tender" data-id="${data}" title="Ubah Proposal Tender">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            ` : '';

                            return `
                                <div class="d-flex justify-content-center align-items-center gap-1.5">
                                    <button type="button" class="btn-table-action btn-action-view btn-view-detail" data-id="${data}" title="Lihat Detail Tender">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    ${editBtn}
                                </div>
                            `;
                        }
                    }
                ]
            });

            // ===== MODERN DUAL-THEME FILTER CONTROLLER =====
            function updateTenderFilter(type, val, label) {
                if (type === 'status') {
                    $('#table_filter_status').val(val);
                    $('#label_filter_status').text(label || 'Semua Status');
                    
                    // Update dropdown checkmark
                    $('[data-filter-type="status"]').removeClass('active');
                    $(`[data-filter-type="status"][data-value="${val}"]`).addClass('active');

                    // Highlight trigger button if filtered
                    if (val !== 'all') {
                        $('#btn_filter_status').addClass('active-filtered');
                    } else {
                        $('#btn_filter_status').removeClass('active-filtered');
                    }

                    // Sync quick filter pills
                    $('.tender-quick-filter-btn').removeClass('active');
                    $(`.tender-quick-filter-btn[data-status="${val}"]`).addClass('active');
                } else if (type === 'priority') {
                    $('#table_filter_priority').val(val);
                    $('#label_filter_priority').text(label || 'Semua Prioritas');

                    // Update dropdown checkmark
                    $('[data-filter-type="priority"]').removeClass('active');
                    $(`[data-filter-type="priority"][data-value="${val}"]`).addClass('active');

                    // Highlight trigger button if filtered
                    if (val !== 'all') {
                        $('#btn_filter_priority').addClass('active-filtered');
                    } else {
                        $('#btn_filter_priority').removeClass('active-filtered');
                    }
                }

                // Check if any filter is active to show/hide Reset button
                let st = $('#table_filter_status').val();
                let pr = $('#table_filter_priority').val();
                let ent = $('#filter_entitas').length ? $('#filter_entitas').val() : 'all';

                if (st !== 'all' || pr !== 'all' || (ent && ent !== 'all')) {
                    $('#btn_reset_tender_filters').removeClass('d-none');
                } else {
                    $('#btn_reset_tender_filters').addClass('d-none');
                }

                // Refresh both Kanban & DataTable
                window.refreshTenderViews();
            }

            // Click dropdown item
            $(document).on('click', '.tender-filter-item', function (e) {
                e.preventDefault();
                let type = $(this).data('filter-type');
                let val = $(this).data('value');
                let label = $(this).data('label');
                updateTenderFilter(type, val, label);
            });

            // Click quick filter pill
            $(document).on('click', '.tender-quick-filter-btn', function () {
                let val = $(this).data('status');
                let label = $(`[data-filter-type="status"][data-value="${val}"]`).data('label') || $(this).find('span:last').text().trim();
                updateTenderFilter('status', val, label);
            });

            // Reset all filters
            $('#btn_reset_tender_filters').on('click', function () {
                updateTenderFilter('status', 'all', 'Semua Status');
                updateTenderFilter('priority', 'all', 'Semua Prioritas');
                if ($('#filter_entitas').length) {
                    $('#filter_entitas').val('all');
                }
            });

            $('#filter_entitas').on('change', function () {
                window.refreshTenderViews();
            });

            // ===== CUSTOMER LOOKUP MODAL (CINEMATIC CARD-BASED) =====
            $(document).on('click', '.btn-lookup-customer', function () {
                let prefix = $(this).data('prefix') || 'tambah';
                $('#modalCariCustomer').data('target-prefix', prefix);
                $('#search_customer_input').val('');
                $('#btn_clear_search_customer').addClass('d-none');
                $('#customer_lookup_container .customer-lookup-card').removeClass('is-hidden d-none');
                $('#customer_lookup_empty').addClass('d-none');
                $('#badge_total_customer').text('{{ $customers->count() }} Terdaftar');
                $('#modalCariCustomer').modal('show');
                setTimeout(() => $('#search_customer_input').focus(), 300);
            });

            $('#search_customer_input').on('input keyup', function () {
                let rawVal = $(this).val() || '';
                let val = rawVal.toLowerCase().trim();
                let terms = val.split(/\s+/).filter(Boolean);
                let visibleCount = 0;

                if (val.length > 0) {
                    $('#btn_clear_search_customer').removeClass('d-none');
                } else {
                    $('#btn_clear_search_customer').addClass('d-none');
                }

                $('#customer_lookup_container .customer-lookup-card').each(function () {
                    let $card = $(this);
                    let searchStr = (($card.attr('data-search') || '') + ' ' + $card.text()).toLowerCase();
                    
                    let matches = true;
                    if (terms.length > 0) {
                        matches = terms.every(term => searchStr.indexOf(term) > -1);
                    }

                    if (matches) {
                        $card.removeClass('is-hidden d-none');
                        visibleCount++;
                    } else {
                        $card.addClass('is-hidden d-none');
                    }
                });

                if (visibleCount === 0) {
                    $('#customer_lookup_empty').removeClass('d-none');
                    $('#badge_total_customer').text('0 Ditemukan');
                } else {
                    $('#customer_lookup_empty').addClass('d-none');
                    if (terms.length > 0) {
                        $('#badge_total_customer').text(visibleCount + ' Ditemukan');
                    } else {
                        $('#badge_total_customer').text('{{ $customers->count() }} Terdaftar');
                    }
                }
            });

            $('#search_customer_input').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    let firstVisible = $('#customer_lookup_container .customer-lookup-card:not(.is-hidden):not(.d-none):first');
                    if (firstVisible.length) {
                        firstVisible.find('.btn-select-customer').trigger('click');
                    }
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
