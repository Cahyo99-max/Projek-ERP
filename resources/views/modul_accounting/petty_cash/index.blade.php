<x-layouts>
    <x-slot:title>Kas Kecil (Petty Cash) - {{ $activeEntityName }}</x-slot:title>

    <div class="container-fluid py-2 px-2 px-md-4 pc-container">
        
        <!-- =========================================================================
             1. HEADER TOOLBAR & ENTITAS ACTIVE PILL
             ========================================================================= -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #007d96 0%, #00e5ff 100%);">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0 text-body-emphasis fs-5 fs-md-4">Kas Kecil</h4>
                       
                    </div>
                </div>
            </div>

            <!-- Entitas Status Badge & Quick Action Header -->
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto justify-content-between justify-content-md-end">
                <div class="badge bg-body border shadow-sm rounded-pill px-3 py-2 text-body d-flex align-items-center gap-2">
                    <span class="p-1 rounded-circle bg-success d-inline-block"></span>
                    <span class="fw-bold small">{{ $activeEntityName }}</span>
                    <span class="badge {{ $isCabang ? 'bg-info-subtle text-info border-info-subtle' : 'bg-primary-subtle text-primary border-primary-subtle' }} border rounded-pill" style="font-size: 10px;">
                        {{ $isCabang ? 'Cabang' : 'Pusat' }}
                    </span>
                </div>

                @can('catat-petty-cash')
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-none d-md-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none;">
                    <i class="bi bi-dash-circle fs-6"></i>
                    <span>Catat Pengeluaran</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- =========================================================================
             2. DIGITAL WALLET CARD (HERO BENTO CARD SALDO KAS KECIL)
             ========================================================================= -->
        <div class="row g-3 mb-3">
            <!-- Main Hero Wallet Card (Vibrant Aqua Fintech Look) -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 text-white rounded-4 shadow-sm position-relative overflow-hidden pc-wallet-hero h-100" style="background: radial-gradient(circle at 90% 15%, rgba(0, 242, 254, 0.4) 0%, rgba(0, 242, 254, 0) 55%), linear-gradient(135deg, #042432 0%, #00566e 30%, #0098b7 65%, #00f2fe 100%); min-height: 185px;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between position-relative z-1">
                        <!-- Top Row: Card Title & Eye Toggle -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-3 py-1.5 small font-monospace fw-semibold" style="background: rgba(0, 242, 254, 0.15); color: #ffffff !important; border: 1px solid rgba(0, 242, 254, 0.35); backdrop-filter: blur(8px);">
                                    <i class="bi bi-wallet2 text-warning me-1.5"></i> {{ !empty($kasKecilAccount?->nama_akun) ? $kasKecilAccount->nama_akun : 'Kas Kecil' }}
                                </span>
                                <span class="small font-monospace d-none d-sm-inline" style="color: rgba(255, 255, 255, 0.85);">
                                    ({{ $activeEntityName }})
                                </span>
                            </div>
                            <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center p-1.5" id="btnToggleBalance" title="Sembunyikan/Tampilkan Saldo" style="background: rgba(0, 242, 254, 0.2); color: #ffffff; width: 32px; height: 32px; backdrop-filter: blur(6px); border: 1px solid rgba(0, 242, 254, 0.4);">
                                <i class="bi bi-eye fs-6" id="iconEyeBalance"></i>
                            </button>
                        </div>

                        <!-- Middle Row: Single Focused Balance Display -->
                        <div class="my-2">
                            <div class="text-white-50 small mb-0.5 font-monospace">Sisa Saldo Kas Kecil</div>
                            <h2 class="fw-bold font-monospace mb-1 text-white pc-balance-val fs-2 fs-md-1" data-nominal="{{ $saldoKasKecil }}">
                                Rp {{ number_format($saldoKasKecil, 2, ',', '.') }}
                            </h2>
                            <div class="small font-monospace" style="font-size: 11px;">
                                @if($saldoKasKecil <= 0)
                                    <span class="text-warning"><i class="bi bi-exclamation-circle-fill me-1"></i> Saldo belum diisi &bull; Top Up via Transfer Kas &amp; Bank</span>
                                @else
                                    <span class="text-white-50"><i class="bi bi-arrow-down-right text-success me-1"></i> Siap digunakan untuk pengeluaran operasional</span>
                                @endif
                            </div>
                        </div>

                        <!-- Bottom Row: Quick Action Pill Buttons -->
                        <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top border-white border-opacity-10">
                            @can('catat-petty-cash')
                            <button type="button" class="btn btn-sm btn-danger text-white rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-sm-grow-0 justify-content-center btn-quick-action" data-type="Pengeluaran" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none;">
                                <i class="bi bi-dash-circle"></i>
                                <span>Catat Pengeluaran</span>
                            </button>
                            @endcan
                            <a href="{{ route('accounting.kas-bank.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 flex-sm-grow-0 justify-content-center" title="Top Up Saldo melalui menu Transfer Kas & Bank">
                                <i class="bi bi-arrow-left-right text-success"></i>
                                <span>Transfer Kas &amp; Bank</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat Metric Cards (Bulan Ini) -->
            <div class="col-12 col-xl-5">
                <div class="row g-2 g-md-3 h-100">
                    <!-- Pengeluaran Hari Ini / Tanggal Berjalan -->
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small fw-semibold mb-0.5">Pengeluaran Hari Ini ({{ \Carbon\Carbon::parse($todayDate ?? now())->translatedFormat('d M Y') }})</div>
                                    <h5 class="fw-bold font-monospace mb-0 text-danger fs-6 fs-md-5">- Rp {{ number_format($todayOut ?? $thisMonthOut, 2, ',', '.') }}</h5>
                                    <div class="small text-muted mt-0.5 font-monospace" style="font-size: 11px;">
                                        <i class="bi bi-cash-stack text-danger"></i> {{ $todayCount ?? $thisMonthCount }} Transaksi Beban Operasional
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-3 bg-danger-subtle text-danger d-inline-flex">
                                    <i class="bi bi-cash-stack fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Up Kas Kecil dari Kas & Bank (Hari Ini) -->
                    <div class="col-12 col-sm-6 col-xl-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-body">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-muted small fw-semibold mb-0.5">Top Up Hari Ini (Kas &amp; Bank)</div>
                                    <h5 class="fw-bold font-monospace mb-0 text-success fs-6 fs-md-5">+ Rp {{ number_format($todayTopUp ?? $thisMonthTopUp, 2, ',', '.') }}</h5>
                                    <div class="small text-muted mt-0.5 font-monospace text-truncate" style="font-size: 11px; max-width: 220px;">
                                        @if($lastTopUp)
                                            <i class="bi bi-check-circle text-success"></i> Terakhir: {{ date('d/m/Y', strtotime($lastTopUp->tanggal)) }} (Rp {{ number_format($lastTopUp->jumlah, 2, ',', '.') }})
                                        @else
                                            <i class="bi bi-arrow-left-right text-success"></i> Via Transfer Kas &amp; Bank
                                        @endif
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-3 bg-success-subtle text-success d-inline-flex">
                                    <i class="bi bi-bank fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             3. SEARCH & MODAL FILTER BAR
             ========================================================================= -->
        @php
            $activeFilterCount = 0;
            if (!empty($kategoriFilter) && $kategoriFilter !== 'all') $activeFilterCount++;
            if (!empty($coaKasFilter) && $coaKasFilter !== 'all') $activeFilterCount++;
            if (!empty($pemohonFilter) && $pemohonFilter !== 'all') $activeFilterCount++;
            if (!empty($tenderFilter)) $activeFilterCount++;
            if (!empty($tipeFilter) && $tipeFilter !== 'all') $activeFilterCount++;
            if ($startDate != now()->startOfMonth()->toDateString() || $endDate != now()->endOfMonth()->toDateString()) $activeFilterCount++;
            if (!empty($sortBy) && $sortBy !== 'tanggal') $activeFilterCount++;
            if (!empty($sortOrder) && $sortOrder !== 'desc') $activeFilterCount++;
        @endphp

        <div class="card border-0 shadow-sm rounded-4 mb-3 bg-body">
            <div class="card-body p-2.5 p-md-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    
                    <!-- Search Input Form -->
                    @can('filter-petty-cash')
                    <form action="{{ route('accounting.petty-cash.index') }}" method="GET" class="flex-grow-1 m-0" id="formSearchPettyCash">
                        <!-- Pertahankan Parameter Filter jika sedang aktif -->
                        @if(!empty($perPage) && $perPage != 10) <input type="hidden" name="per_page" value="{{ $perPage }}"> @endif
                        @if(!empty($kategoriFilter) && $kategoriFilter !== 'all') <input type="hidden" name="kategori" value="{{ $kategoriFilter }}"> @endif
                        @if(!empty($coaKasFilter) && $coaKasFilter !== 'all') <input type="hidden" name="coa_kas_id" value="{{ $coaKasFilter }}"> @endif
                        @if(!empty($pemohonFilter) && $pemohonFilter !== 'all') <input type="hidden" name="nama_pengaju" value="{{ $pemohonFilter }}"> @endif
                        @if(!empty($tenderFilter)) <input type="hidden" name="tender_id" value="{{ $tenderFilter }}"> @endif
                        @if(!empty($tipeFilter) && $tipeFilter !== 'all') <input type="hidden" name="tipe" value="{{ $tipeFilter }}"> @endif
                        @if($startDate != now()->startOfMonth()->toDateString()) <input type="hidden" name="start_date" value="{{ $startDate }}"> @endif
                        @if($endDate != now()->endOfMonth()->toDateString()) <input type="hidden" name="end_date" value="{{ $endDate }}"> @endif
                        @if(!empty($sortBy)) <input type="hidden" name="sort_by" value="{{ $sortBy }}"> @endif
                        @if(!empty($sortOrder)) <input type="hidden" name="sort_order" value="{{ $sortOrder }}"> @endif

                        <div class="input-group input-group-sm pc-search-pill-group">
                            <span class="input-group-text bg-transparent border-0 text-muted ps-2.5">
                                <i class="bi bi-search" id="iconSearchPettyCash"></i>
                            </span>
                            <input type="text" name="search" id="inputSearchPettyCash" class="form-control form-control-sm border-0 ps-1 bg-transparent" placeholder="Cari transaksi..." value="{{ $search }}" autocomplete="off">
                            <button type="button" class="btn btn-link text-muted btn-sm border-0 text-decoration-none px-2 {{ empty($search) ? 'd-none' : '' }}" id="btnClearSearchPettyCash" title="Hapus Kata Kunci">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 fw-semibold d-none d-md-inline-block ms-1" id="btnSubmitSearchPettyCash">Cari</button>
                        </div>
                    </form>
                    @else
                    <div class="flex-grow-1"></div>
                    @endcan

                    <!-- Filter Trigger Button (Munculkan Modal Filter) & Reset -->
                    <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                        @canany(['filter-petty-cash', 'cetak-pdf-petty-cash', 'export-excel-petty-cash'])
                        <button type="button" 
                                class="btn btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold pc-btn-filter-trigger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalFilterPettyCash" 
                                title="Buka Filter Transaksi">
                            <i class="bi bi-funnel-fill text-info"></i>
                            <span>Filter</span>
                            @if($activeFilterCount > 0)
                                <span class="badge rounded-pill bg-danger text-white px-1.5 py-0.5 font-monospace" style="font-size: 10px;">
                                    {{ $activeFilterCount }}
                                </span>
                            @endif
                        </button>
                        @endcanany

                        @if($activeFilterCount > 0 || !empty($search))
                            <a href="{{ route('accounting.petty-cash.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-danger small d-inline-flex align-items-center gap-1" title="Reset Semua Filter & Pencarian">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span class="d-none d-sm-inline small">Reset</span>
                            </a>
                        @endif
                    </div>

                </div>

                <!-- Active Filter Tags / Chips -->
                @if($activeFilterCount > 0 || !empty($search))
                    <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2.5 pt-2 border-top pc-border-adaptive">
                        <span class="small pc-text-muted-adaptive me-1" style="font-size: 11px;">Filter Aktif:</span>

                        @if(!empty($search))
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-search me-1 text-info"></i> "{{ $search }}"
                                <a href="{{ route('accounting.petty-cash.index', request()->except('search')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($kategoriFilter) && $kategoriFilter !== 'all')
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-tag me-1 text-warning"></i> {{ $categoryMapping[$kategoriFilter]['label'] ?? $kategoriFilter }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('kategori')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($coaKasFilter) && $coaKasFilter !== 'all')
                            @php
                                $kasObj = isset($coaKasAccounts) ? $coaKasAccounts->first(function($item) use ($coaKasFilter) {
                                    return ($item->coa_id ?? $item->coa?->id) == $coaKasFilter;
                                }) : null;
                                $kasName = $kasObj ? ($kasObj->nama_akun ?: ($kasObj->coa?->name ?? 'Kas #'.$coaKasFilter)) : 'Kas #'.$coaKasFilter;
                            @endphp
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-wallet2 me-1 text-success"></i> Akun Kas: {{ $kasName }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('coa_kas_id')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($pemohonFilter) && $pemohonFilter !== 'all')
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-person-badge me-1 text-info"></i> Pemohon: {{ $pemohonFilter }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('nama_pengaju')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($tenderFilter))
                            @php $tObj = $tenders->firstWhere('id', $tenderFilter); @endphp
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-bullseye me-1 text-primary"></i> {{ $tObj ? $tObj->nomor_kontrak : 'Tender #'.$tenderFilter }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('tender_id')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($tipeFilter) && $tipeFilter !== 'all')
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-arrow-left-right me-1 text-success"></i> {{ $tipeFilter }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('tipe')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if($startDate != now()->startOfMonth()->toDateString() || $endDate != now()->endOfMonth()->toDateString())
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-calendar3 me-1 text-info"></i> {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
                                <a href="{{ route('accounting.petty-cash.index', request()->except('start_date', 'end_date')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif

                        @if(!empty($sortBy) && $sortBy !== 'tanggal')
                            @php
                                $sortLabel = ucfirst(str_replace('_', ' ', $sortBy));
                                if ($sortBy === 'kategori') {
                                    $orderText = ($sortOrder === 'asc') ? 'A-Z' : 'Z-A';
                                } elseif ($sortBy === 'nominal') {
                                    $orderText = ($sortOrder === 'asc') ? 'Terkecil' : 'Terbesar';
                                } elseif ($sortBy === 'nomor_transaksi') {
                                    $orderText = ($sortOrder === 'asc') ? 'A-Z / Terlama' : 'Z-A / Terbaru';
                                } else {
                                    $orderText = ($sortOrder === 'asc') ? 'Terlama' : 'Terbaru';
                                }
                            @endphp
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-sort-down me-1 text-info"></i> Urut: {{ $sortLabel }} ({{ $orderText }})
                                <a href="{{ route('accounting.petty-cash.index', request()->except('sort_by', 'sort_order')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @elseif(!empty($sortOrder) && $sortOrder === 'asc')
                            <span class="badge pc-filter-chip">
                                <i class="bi bi-sort-up me-1 text-info"></i> Urut: Tanggal (Terlama)
                                <a href="{{ route('accounting.petty-cash.index', request()->except('sort_by', 'sort_order')) }}" class="pc-text-muted-adaptive ms-1 text-decoration-none hover-danger">&times;</a>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- =========================================================================
             4. MOBILE TRANSACTION CARDS FEED (OPTIMIZED FOR HP)
             ========================================================================= -->
        <div class="d-block d-lg-none mb-4" id="mobileListPettyCash">
            <div class="d-flex justify-content-between align-items-center mb-2.5 px-1">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Daftar Transaksi ({{ $transactions->total() }})</span>
                <span class="small text-muted font-monospace" style="font-size: 11px;">Hal {{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</span>
            </div>

            @forelse($transactions as $trx)
                @php
                    $badgeTipeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                    
                    // Metadata Kategori dari Mapping
                    $catMeta = $categoryMapping[$trx->kategori] ?? null;
                    $iconClass = $catMeta['icon'] ?? 'bi-wallet2';
                    $badgeColor = $catMeta['badge'] ?? 'primary';
                    $iconBg = "bg-{$badgeColor}-subtle text-{$badgeColor}";
                    $nominalFormatted = fmod((float)$trx->nominal, 1) == 0 
                        ? number_format($trx->nominal, 0, ',', '.') 
                        : number_format($trx->nominal, 2, ',', '.');
                @endphp
                <div class="card border-0 shadow-sm rounded-4 mb-3 bg-body pc-trx-mobile-card" data-id="{{ $trx->id }}">
                    <div class="card-body p-3">
                        <!-- 1. Header Baris Atas: Icon Kategori + Nama Kategori + Badge Tipe + Nominal -->
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <!-- Left: Icon & Kategori -->
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <div class="p-2 rounded-3 {{ $iconBg }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="bi {{ $iconClass }} fs-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-bold text-body-emphasis small text-truncate" title="{{ $trx->kategori }}">
                                        {{ $trx->kategori }}
                                    </div>
                                    <span class="badge {{ $badgeTipeClass }} border rounded-pill px-2 py-0.5 font-monospace" style="font-size: 9.5px;">
                                        {{ $trx->tipe }}
                                    </span>
                                </div>
                            </div>

                            <!-- Right: Nominal (Rapi, Bold, Tidak Terpotong) -->
                            <div class="text-end flex-shrink-0">
                                <span class="fw-bold font-monospace fs-6 d-block {{ $trx->status === 'Dibatalkan' ? 'text-decoration-line-through text-muted' : 'text-danger' }}">
                                    - Rp {{ $nominalFormatted }}
                                </span>
                                <span class="text-muted font-monospace" style="font-size: 10px;">
                                    {{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- 2. Meta Info (No Transaksi, Jam, Pemohon) -->
                        <div class="d-flex flex-wrap align-items-center gap-1.5 py-1.5 px-2 rounded-2 mb-2 pc-mobile-card-meta" style="font-size: 11px;">
                            <span class="font-monospace fw-semibold text-body-secondary">
                                <i class="bi bi-hash text-muted"></i>{{ $trx->nomor_transaksi }}
                            </span>
                            @if(!empty($trx->created_at))
                                <span class="text-muted">&bull;</span>
                                <span class="text-muted font-monospace">
                                    <i class="bi bi-clock me-0.5 opacity-75"></i>{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}
                                </span>
                            @endif
                            @if($trx->nama_pengaju)
                                <span class="text-muted">&bull;</span>
                                <span class="text-muted text-truncate" style="max-width: 140px;">
                                    <i class="bi bi-person me-0.5"></i>{{ $trx->nama_pengaju }}
                                </span>
                            @endif
                        </div>

                        <!-- 3. Keterangan / Keperluan -->
                        @if($trx->keterangan)
                            <p class="small text-body-secondary mb-2 lh-sm text-truncate-2" style="font-size: 12px;">
                                {{ $trx->keterangan }}
                            </p>
                        @endif

                        <!-- Alasan Pembatalan (Jika Batal) -->
                        @if($trx->status === 'Dibatalkan' && !empty($trx->alasan_pembatalan))
                            <div class="small text-danger mb-2 p-2 rounded-2" style="background: rgba(225, 29, 72, 0.08); border-left: 3px solid #e11d48; font-size: 11px;">
                                <strong><i class="bi bi-exclamation-octagon-fill me-1"></i>Alasan Batal:</strong> {{ $trx->alasan_pembatalan }}
                            </div>
                        @endif

                        <!-- 4. Baris Bawah: Tag Tender + Action Pill (Nota & Detail) -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top pc-border-adaptive mt-1">
                            <!-- Left: Tender Tag (jika ada) atau Akun Kas -->
                            <div class="d-flex align-items-center gap-1 flex-wrap min-w-0">
                                @if($trx->tender)
                                    <span class="badge pc-badge-tender rounded-pill font-monospace" style="font-size: 10.5px;">
                                        🎯 {{ $trx->tender->nomor_kontrak }}
                                    </span>
                                @else
                                    <span class="badge pc-badge-kas rounded-pill font-monospace text-truncate" style="font-size: 10.5px; max-width: 160px;">
                                        <i class="bi bi-wallet2 me-0.5"></i>{{ $trx->coaKas->name ?? 'Kas Kecil' }}
                                    </span>
                                @endif
                            </div>

                            <!-- Right: Action Buttons Group (Nota & Detail) -->
                            <div class="d-flex align-items-center gap-1.5 flex-shrink-0 pc-action-group">
                                @if($trx->lampiran_nota)
                                    <button type="button" 
                                            class="badge pc-badge-bukti-mobile btn-preview-bukti border-0 d-inline-flex align-items-center gap-1 text-decoration-none py-1 px-2.5" 
                                            data-url="{{ asset('storage/' . $trx->lampiran_nota) }}" 
                                            data-nomor="{{ $trx->nomor_transaksi }}" 
                                            data-kategori="{{ $trx->kategori }}" 
                                            data-nominal="- Rp {{ $nominalFormatted }}"
                                            onclick="previewBukti(this, event);" 
                                            title="Lihat Bukti Nota / Struk">
                                        <i class="bi bi-paperclip"></i> Nota
                                    </button>
                                @endif

                                @can('detail-petty-cash')
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-0.5 d-inline-flex align-items-center gap-1 fw-semibold btn-detail-pc" data-id="{{ $trx->id }}" title="Detail Voucher &amp; Jurnal" onclick="loadPettyCashDetail({{ $trx->id }})" style="font-size: 11px;">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-body">
                    <i class="bi bi-inbox fs-1 text-muted opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-muted mb-1">Belum Ada Pengeluaran Kas Kecil</h6>
                    <p class="small text-muted mb-3">Tidak ditemukan catatan pengeluaran kas kecil untuk {{ $activeEntityName }}.</p>
                    @can('catat-petty-cash')
                    <div>
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 py-1.5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none;">
                            <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran Baru
                        </button>
                    </div>
                    @endcan
                </div>
            @endforelse

            <!-- Mobile Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <!-- =========================================================================
             5. DESKTOP & TABLET DATA TABLE
             ========================================================================= -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 d-none d-lg-block bg-body">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text text-success"></i> Riwayat Pembukuan Kas Kecil &bull; {{ $activeEntityName }}
                </h6>
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="small text-muted fw-semibold" style="font-size: 12px;">Tampilkan:</span>
                        <select class="form-select form-select-sm rounded-pill select-per-page shadow-sm fw-semibold" style="width: 78px; font-size: 12px; padding-top: 3px; padding-bottom: 3px;" title="Pilih jumlah baris yang ditampilkan">
                            <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="small text-muted" style="font-size: 12px;">baris</span>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill font-monospace px-3 py-1.5" id="badgeTotalData">Total: {{ $transactions->total() }} Data</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="desktopTablePettyCash">
                        <thead class="pc-table-header">
                            <tr>
                                <th class="ps-4" style="min-width: 170px;">
                                    <a href="{{ route('accounting.petty-cash.index', array_merge(request()->query(), ['sort_by' => 'nomor_transaksi', 'sort_order' => ($sortBy === 'nomor_transaksi' && $sortOrder === 'desc') ? 'asc' : 'desc'])) }}" class="pc-th-sort text-decoration-none" title="Urutkan Nomor Transaksi">
                                        <span>No. Transaksi</span>
                                        @if($sortBy === 'nomor_transaksi')
                                            <i class="bi {{ $sortOrder === 'asc' ? 'bi-sort-numeric-down text-info fw-bold' : 'bi-sort-numeric-down-alt text-info fw-bold' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up opacity-25"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 110px;">
                                    <a href="{{ route('accounting.petty-cash.index', array_merge(request()->query(), ['sort_by' => 'tanggal', 'sort_order' => ($sortBy === 'tanggal' && $sortOrder === 'desc') ? 'asc' : 'desc'])) }}" class="pc-th-sort text-decoration-none" title="Urutkan Tanggal Transaksi">
                                        <span>Tanggal</span>
                                        @if($sortBy === 'tanggal' || empty($sortBy))
                                            <i class="bi {{ ($sortOrder ?? 'desc') === 'asc' ? 'bi-sort-up text-info fw-bold' : 'bi-sort-down text-info fw-bold' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up opacity-25"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 240px;">
                                    <a href="{{ route('accounting.petty-cash.index', array_merge(request()->query(), ['sort_by' => 'kategori', 'sort_order' => ($sortBy === 'kategori' && $sortOrder === 'asc') ? 'desc' : 'asc'])) }}" class="pc-th-sort text-decoration-none" title="Urutkan Kategori (A-Z / Z-A)">
                                        <span>Kategori &amp; Keterangan</span>
                                        @if($sortBy === 'kategori')
                                            <i class="bi {{ $sortOrder === 'asc' ? 'bi-sort-alpha-down text-info fw-bold' : 'bi-sort-alpha-up-alt text-info fw-bold' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up opacity-25"></i>
                                        @endif
                                    </a>
                                </th>
                                <th style="min-width: 170px;">Akun Kas (COA)</th>
                                <th style="min-width: 150px;">Tender/Proyek</th>
                                <th class="text-end" style="min-width: 140px;">
                                    <a href="{{ route('accounting.petty-cash.index', array_merge(request()->query(), ['sort_by' => 'nominal', 'sort_order' => ($sortBy === 'nominal' && $sortOrder === 'desc') ? 'asc' : 'desc'])) }}" class="pc-th-sort justify-content-end text-decoration-none w-100" title="Urutkan Nominal Rupiah">
                                        <span>Nominal</span>
                                        @if($sortBy === 'nominal')
                                            <i class="bi {{ $sortOrder === 'asc' ? 'bi-sort-numeric-down text-info fw-bold' : 'bi-sort-numeric-down-alt text-info fw-bold' }}"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up opacity-25"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-center" style="min-width: 90px;">Bukti</th>
                                <th class="text-center pe-4" style="min-width: 95px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                @php
                                    $isExpense = ($trx->tipe === 'Pengeluaran' || $trx->tipe === 'Klaim');
                                    $catMeta = $categoryMapping[$trx->kategori] ?? null;
                                    $catBadge = $catMeta['badge'] ?? 'primary';
                                    $catIcon = $catMeta['icon'] ?? 'bi-tag';
                                @endphp
                                <tr class="pc-trx-row" data-id="{{ $trx->id }}" title="Klik baris untuk melihat detail &amp; Lawan COA">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <span class="font-monospace fw-bold pc-text-code {{ $trx->status === 'Dibatalkan' ? 'text-decoration-line-through opacity-75' : '' }}">{{ $trx->nomor_transaksi }}</span>
                                            @if($trx->status === 'Dibatalkan')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 9px; padding: 2px 6px;">Batal</span>
                                            @endif
                                        </div>
                                        <div class="pc-text-sub small"><i class="bi bi-person me-1"></i>{{ $trx->nama_pengaju ?: 'Admin' }}</div>
                                    </td>
                                    <td>
                                        <div class="font-monospace pc-text-date small fw-semibold">{{ date('d/m/Y', strtotime($trx->tanggal)) }}</div>
                                        <div class="font-monospace small text-muted d-flex align-items-center gap-1 mt-0.5" style="font-size: 11px;">
                                            <i class="bi bi-clock opacity-75"></i>
                                            <span>{{ !empty($trx->created_at) ? \Carbon\Carbon::parse($trx->created_at)->format('H:i') . ' WIB' : '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                                            <span class="badge pc-badge-kategori pc-badge-{{ $catBadge }} rounded-pill px-2.5 py-1">
                                                @if(!empty($catIcon) && str_starts_with($catIcon, 'bi-'))
                                                    <i class="bi {{ $catIcon }} me-1"></i>
                                                @endif
                                                {{ $trx->kategori }}
                                            </span>
                                        </div>
                                        <div class="pc-text-keterangan small">{{ $trx->keterangan ?: '-' }}</div>
                                        @if($trx->status === 'Dibatalkan' && !empty($trx->alasan_pembatalan))
                                            <div class="small text-danger mt-1 p-1 rounded-1 d-inline-block" style="background: rgba(225, 29, 72, 0.08); border-left: 2px solid #e11d48; font-size: 10.5px; max-width: 260px;">
                                                <i class="bi bi-info-circle me-1"></i><strong>Alasan Batal:</strong> {{ $trx->alasan_pembatalan }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                            <span class="badge pc-badge-kas rounded-pill font-monospace px-2 py-0.5">
                                                {{ $trx->coaKas->code ?? '-' }}
                                            </span>
                                        </div>
                                        <div class="pc-text-sub small text-truncate" style="max-width: 160px;" title="{{ $trx->coaKas->name ?? '-' }}">
                                            {{ $trx->coaKas->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($trx->tender)
                                            <span class="badge pc-badge-tender rounded-pill font-monospace px-2.5 py-1">
                                                🎯 {{ $trx->tender->nomor_kontrak }}
                                            </span>
                                        @else
                                            <span class="pc-text-sub small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="font-monospace fw-bold fs-6 pc-text-nominal {{ $trx->status === 'Dibatalkan' ? 'text-decoration-line-through text-muted' : '' }}">
                                            - Rp {{ number_format($trx->nominal, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($trx->lampiran_nota)
                                            <button type="button" 
                                                    class="btn-bukti-badge btn-preview-bukti" 
                                                    data-url="{{ asset('storage/' . $trx->lampiran_nota) }}" 
                                                    data-nomor="{{ $trx->nomor_transaksi }}" 
                                                    data-kategori="{{ $trx->kategori }}" 
                                                    data-nominal="- Rp {{ number_format($trx->nominal, 2, ',', '.') }}"
                                                    onclick="previewBukti(this, event);" 
                                                    title="Klik untuk Preview Bukti Nota / Struk">
                                                <i class="bi bi-paperclip"></i>
                                                <span>Nota</span>
                                            </button>
                                        @else
                                            <span class="pc-text-sub small opacity-40">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4" onclick="event.stopPropagation();">
                                        <div class="pc-action-group justify-content-center">
                                            @can('detail-petty-cash')
                                            <button type="button" class="btn-action-modern btn-action-detail btn-detail-pc" data-id="{{ $trx->id }}" title="Detail Voucher &amp; Lawan COA" onclick="loadPettyCashDetail({{ $trx->id }})">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 pc-text-sub">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                        <div class="fw-semibold">Belum ada transaksi pengeluaran kas kecil untuk {{ $activeEntityName }}.</div>
                                        @can('catat-petty-cash')
                                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 py-1.5 fw-bold mt-2" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none;">
                                            <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran Baru
                                        </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Desktop Pagination -->
                <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-top gap-2">
                    <span class="small text-muted">Menampilkan {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi</span>
                    <div>
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- FLOATING ACTION BUTTON (FAB) -->
    @can('catat-petty-cash')
    <button type="button" class="btn btn-danger rounded-circle shadow-lg d-flex d-md-none align-items-center justify-content-center pc-fab-btn" data-bs-toggle="modal" data-bs-target="#modalInputPettyCash" title="Catat Pengeluaran" style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); border: none;">
        <i class="bi bi-dash-lg fs-4"></i>
    </button>
    @endcan

    <!-- PUSH MODALS -->
    @push('modals')
        @include('modul_accounting.petty_cash._modal_input')
        @include('modul_accounting.petty_cash._modal_lookup_pemohon')
        @include('modul_accounting.petty_cash._modal_detail')
        @include('modul_accounting.petty_cash._modal_preview_bukti')
        @include('modul_accounting.petty_cash._modal_filter')
    @endpush

    <!-- PUSH CUSTOM STYLES & SCRIPTS -->
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_akunting/patty_cash.css') }}?v={{ file_exists(public_path('modul_akunting/patty_cash.css')) ? filemtime(public_path('modul_akunting/patty_cash.css')) : '1.0' }}">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF Token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Quick Date Presets di Modal Filter
            $(document).on('click', '.btn-filter-date-preset', function() {
                const preset = $(this).data('preset');
                const now = new Date();
                let start, end;

                const formatDate = (d) => {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (preset === 'today') {
                    start = formatDate(now);
                    end = formatDate(now);
                } else if (preset === 'this_month') {
                    start = formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
                    end = formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
                } else if (preset === 'last_month') {
                    start = formatDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
                    end = formatDate(new Date(now.getFullYear(), now.getMonth(), 0));
                } else if (preset === 'this_year') {
                    start = formatDate(new Date(now.getFullYear(), 0, 1));
                    end = formatDate(new Date(now.getFullYear(), 11, 31));
                }

                if (start && end) {
                    $('#filter_start_date').val(start);
                    $('#filter_end_date').val(end);
                    $('.btn-filter-date-preset').removeClass('active');
                    $(this).addClass('active');
                }
            });

            // ===== REAL-TIME CLIENT SEARCH (LANGSUNG MEMFILTER REAL-TIME TANPA RELOAD HALAMAN) =====
            function filterPettyCashRealtime() {
                const rawQuery = ($('#inputSearchPettyCash').val() || '').trim().toLowerCase();

                // Toggle Tombol Clear (X)
                if (rawQuery.length > 0) {
                    $('#btnClearSearchPettyCash').removeClass('d-none');
                } else {
                    $('#btnClearSearchPettyCash').addClass('d-none');
                }

                // 1. Filter Baris Tabel Desktop
                const $desktopRows = $('#desktopTablePettyCash tbody tr.pc-trx-row');
                let desktopMatchCount = 0;

                $desktopRows.each(function() {
                    const rowText = $(this).text().toLowerCase();
                    if (!rawQuery || rowText.indexOf(rawQuery) !== -1) {
                        $(this).show();
                        desktopMatchCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Tampilkan pesan kosong di tabel desktop jika tidak ada yang cocok
                $('#clientSearchEmptyRow').remove();
                if (rawQuery && desktopMatchCount === 0 && $desktopRows.length > 0) {
                    const safeKeyword = $('<div>').text(rawQuery).html();
                    const emptyHtml = `
                        <tr id="clientSearchEmptyRow">
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-search fs-4 d-block mb-2 text-info opacity-75"></i>
                                    <span>Tidak ditemukan transaksi yang cocok dengan kata kunci "<strong>${safeKeyword}</strong>".</span>
                                </div>
                            </td>
                        </tr>`;
                    $('#desktopTablePettyCash tbody').append(emptyHtml);
                }

                // 2. Filter Kartu Tampilan Mobile
                const $mobileCards = $('#mobileListPettyCash .pc-trx-mobile-card');
                let mobileMatchCount = 0;

                $mobileCards.each(function() {
                    const cardText = $(this).text().toLowerCase();
                    if (!rawQuery || cardText.indexOf(rawQuery) !== -1) {
                        $(this).show();
                        mobileMatchCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Tampilkan pesan kosong di tampilan mobile jika tidak ada yang cocok
                $('#clientSearchEmptyMobile').remove();
                if (rawQuery && mobileMatchCount === 0 && $mobileCards.length > 0) {
                    const safeKeyword = $('<div>').text(rawQuery).html();
                    const emptyMobileHtml = `
                        <div id="clientSearchEmptyMobile" class="col-12 text-center py-4 bg-body rounded-4 shadow-sm my-2">
                            <i class="bi bi-search fs-4 d-block mb-2 text-info opacity-75"></i>
                            <p class="text-muted small mb-0">Tidak ada transaksi yang cocok dengan "<strong>${safeKeyword}</strong>".</p>
                        </div>`;
                    $('#mobileListPettyCash').append(emptyMobileHtml);
                }

                // 3. Update Badge Jumlah Data
                const totalData = $desktopRows.length;
                if (rawQuery) {
                    $('#badgeTotalData').text(`Ditemukan: ${desktopMatchCount} dari ${totalData} Data`);
                } else {
                    $('#badgeTotalData').text(`Total: ${totalData} Data`);
                }
            }

            // Jalankan filter real-time seketika saat pengguna mengetik (0ms delay, tanpa reload)
            $('#inputSearchPettyCash').on('input keyup', function() {
                filterPettyCashRealtime();
            });

            // Cegah form reload saat tekan Enter atau tombol Cari
            $('#formSearchPettyCash').on('submit', function(e) {
                e.preventDefault();
                filterPettyCashRealtime();
            });

            $('#btnSubmitSearchPettyCash').on('click', function(e) {
                e.preventDefault();
                filterPettyCashRealtime();
            });

            // Tombol Clear (X) untuk reset pencarian secara instan
            $('#btnClearSearchPettyCash').on('click', function(e) {
                e.preventDefault();
                $('#inputSearchPettyCash').val('');
                filterPettyCashRealtime();
                $('#inputSearchPettyCash').focus();

                // Bersihkan query string search di URL secara silent (tanpa reload)
                const url = new URL(window.location.href);
                if (url.searchParams.has('search')) {
                    url.searchParams.delete('search');
                    window.history.replaceState({}, document.title, url.toString());
                }
            });

            // Jalankan filter saat halaman dimuat jika sudah ada value search
            if ($('#inputSearchPettyCash').val() && $('#inputSearchPettyCash').val().trim()) {
                filterPettyCashRealtime();
            }

            // 0. Inisialisasi Select2 Searchable Dropdown di Modal Input & Edit
            $('#modalInputPettyCash').on('shown.bs.modal', function () {
                if ($.fn.select2) {
                    $('#input_kategori').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalInputPettyCash'),
                        placeholder: '-- Cari / Pilih Kategori --',
                        width: '100%'
                    });

                    $('#input_lawan_coa_id').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalInputPettyCash'),
                        placeholder: '-- Cari & Pilih Akun Beban / Biaya --',
                        width: '100%'
                    });

                    $('#input_tender_id').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalInputPettyCash'),
                        placeholder: '-- Cari Tender / Proyek --',
                        allowClear: true,
                        width: '100%'
                    });

                    if ($('#input_coa_kas_id option').length > 1) {
                        $('#input_coa_kas_id').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalInputPettyCash'),
                            placeholder: '-- Pilih Akun Kas --',
                            width: '100%'
                        });
                    }

                    // Otomatis pilih COA dari dropdown kategori default saat modal dibuka jika lawan COA belum terisi
                    if (!$('#input_lawan_coa_id').val() && $('#input_kategori').val()) {
                        $('#input_kategori').trigger('change');
                    }
                }
            });



            // Inisialisasi Select2 Searchable Dropdown di Modal Filter
            $('#modalFilterPettyCash').on('shown.bs.modal', function () {
                if ($.fn.select2) {
                    $('#filter_kategori').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterPettyCash'),
                        placeholder: '-- Semua Kategori --',
                        width: '100%'
                    });

                    $('#filter_coa_kas_id').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterPettyCash'),
                        placeholder: '-- Pilih Akun Kas (Wajib) --',
                        width: '100%'
                    });

                    $('#filter_nama_pengaju').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterPettyCash'),
                        placeholder: '-- Semua Pemohon --',
                        tags: true,
                        allowClear: true,
                        width: '100%'
                    });

                    $('#filter_tender_id').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterPettyCash'),
                        placeholder: '-- Semua Tender / Proyek --',
                        width: '100%'
                    });

                    $('#filter_tipe').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalFilterPettyCash'),
                        placeholder: '-- Semua Tipe Transaksi --',
                        width: '100%',
                        minimumResultsForSearch: Infinity
                    });

                    if (!$('#filter_sort_by').hasClass('select2-hidden-accessible')) {
                        $('#filter_sort_by').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalFilterPettyCash'),
                            width: '100%',
                            minimumResultsForSearch: Infinity
                        });
                    }

                    if (!$('#filter_sort_order').hasClass('select2-hidden-accessible')) {
                        $('#filter_sort_order').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalFilterPettyCash'),
                            width: '100%',
                            minimumResultsForSearch: Infinity
                        });
                    }
                }
            });

            // Sinkronisasi dinamis label opsi Urutan (ASC/DESC) berdasarkan kolom yang dipilih
            function syncSortOrderOptions(sortByVal, keepSelectedVal = null) {
                const $orderSelect = $('#filter_sort_order');
                if (!$orderSelect.length) return;

                const currentVal = keepSelectedVal || $orderSelect.val();
                let optionsHtml = '';
                let defaultVal = 'desc';

                if (sortByVal === 'kategori') {
                    defaultVal = 'asc';
                    optionsHtml = `
                        <option value="asc">A ke Z (Abjad A-Z)</option>
                        <option value="desc">Z ke A (Terbalik)</option>
                    `;
                } else if (sortByVal === 'nominal') {
                    defaultVal = 'desc';
                    optionsHtml = `
                        <option value="desc">Terbesar ke Terkecil</option>
                        <option value="asc">Terkecil ke Terbesar</option>
                    `;
                } else if (sortByVal === 'nomor_transaksi') {
                    defaultVal = 'desc';
                    optionsHtml = `
                        <option value="desc">Terbaru / Terbesar</option>
                        <option value="asc">Terlama / Terkecil</option>
                    `;
                } else {
                    // tanggal
                    defaultVal = 'desc';
                    optionsHtml = `
                        <option value="desc">Terbaru ke Terlama (Tanggal & Jam &darr;)</option>
                        <option value="asc">Terlama ke Terbaru (Tanggal & Jam &uarr;)</option>
                    `;
                }

                $orderSelect.html(optionsHtml);
                const targetVal = (keepSelectedVal && (keepSelectedVal === 'asc' || keepSelectedVal === 'desc')) 
                    ? keepSelectedVal 
                    : defaultVal;

                $orderSelect.val(targetVal);

                if ($orderSelect.hasClass('select2-hidden-accessible')) {
                    $orderSelect.trigger('change.select2');
                } else {
                    $orderSelect.trigger('change');
                }
            }

            $(document).on('change', '#filter_sort_by', function() {
                syncSortOrderOptions($(this).val());
            });

            // Validasi Wajib Pilih Akun Kas pada Submit Filter Modal
            $('#formModalFilterPettyCash').on('submit', function(e) {
                const coaKasVal = $('#filter_coa_kas_id').val();
                if (!coaKasVal || coaKasVal === 'all') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akun Kas Wajib Dipilih!',
                        text: 'Silakan tentukan Akun Kas (COA Kas) terlebih dahulu sebelum menerapkan filter.',
                        confirmButtonColor: '#0ea5e9',
                        confirmButtonText: 'Pilih Akun Kas'
                    });
                    setTimeout(() => {
                        if ($.fn.select2 && $('#filter_coa_kas_id').hasClass('select2-hidden-accessible')) {
                            $('#filter_coa_kas_id').select2('open');
                        } else {
                            $('#filter_coa_kas_id').focus();
                        }
                    }, 250);
                    return false;
                }
            });

            // Handler Cetak PDF Laporan Kas Kecil dari Modal Filter (Wajib Akun Kas)
            $(document).on('click', '#btnCetakPdfPettyCash', function(e) {
                e.preventDefault();
                const coaKasVal = $('#filter_coa_kas_id').val();
                if (!coaKasVal || coaKasVal === 'all') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akun Kas Wajib Dipilih!',
                        text: 'Silakan tentukan Akun Kas (COA Kas) terlebih dahulu untuk mencetak Laporan PDF Kas Kecil.',
                        confirmButtonColor: '#0ea5e9',
                        confirmButtonText: 'Pilih Akun Kas'
                    });
                    setTimeout(() => {
                        if ($.fn.select2 && $('#filter_coa_kas_id').hasClass('select2-hidden-accessible')) {
                            $('#filter_coa_kas_id').select2('open');
                        } else {
                            $('#filter_coa_kas_id').focus();
                        }
                    }, 250);
                    return false;
                }
                const form = $('#formModalFilterPettyCash');
                const params = form.serialize();
                const printUrl = `{{ route('accounting.petty-cash.print-pdf') }}?` + params;
                window.open(printUrl, '_blank');
            });

            // Handler Cetak Excel Laporan Kas Kecil dari Modal Filter (Wajib Akun Kas)
            $(document).on('click', '#btnCetakExcelPettyCash', function(e) {
                e.preventDefault();
                const coaKasVal = $('#filter_coa_kas_id').val();
                if (!coaKasVal || coaKasVal === 'all') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akun Kas Wajib Dipilih!',
                        text: 'Silakan tentukan Akun Kas (COA Kas) terlebih dahulu untuk mendownload Laporan Excel Kas Kecil.',
                        confirmButtonColor: '#0ea5e9',
                        confirmButtonText: 'Pilih Akun Kas'
                    });
                    setTimeout(() => {
                        if ($.fn.select2 && $('#filter_coa_kas_id').hasClass('select2-hidden-accessible')) {
                            $('#filter_coa_kas_id').select2('open');
                        } else {
                            $('#filter_coa_kas_id').focus();
                        }
                    }, 250);
                    return false;
                }
                const form = $('#formModalFilterPettyCash');
                const params = form.serialize();
                const excelUrl = `{{ route('accounting.petty-cash.export-excel') }}?` + params;
                window.location.href = excelUrl;
            });

            // 1. Toggle Show / Hide Balance
            let isBalanceHidden = false;
            $('#btnToggleBalance').on('click', function() {
                isBalanceHidden = !isBalanceHidden;
                $('.pc-balance-val').each(function() {
                    const rawNominal = $(this).data('nominal') || 0;
                    if (isBalanceHidden) {
                        $(this).text('Rp ••••••••');
                        $('#iconEyeBalance').removeClass('bi-eye').addClass('bi-eye-slash');
                    } else {
                        $(this).text('Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rawNominal));
                        $('#iconEyeBalance').removeClass('bi-eye-slash').addClass('bi-eye');
                    }
                });
            });

            // 2. Quick Action Buttons on Wallet Header
            $('.btn-quick-action').on('click', function() {
                $('#modalInputPettyCash').modal('show');
            });

            // 3. Format Rupiah Input Helper
            function formatRupiah(value) {
                const numberString = value.replace(/[^,\d]/g, '').toString();
                const split = numberString.split(',');
                const sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    const separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            }

            $('#input_nominal, #edit_input_nominal').on('input', function() {
                const formatted = formatRupiah($(this).val());
                $(this).val(formatted);
            });

            // 4. Quick Nominal Chips (+20k, +50k, etc.)
            $('.btn-quick-nominal').on('click', function() {
                const addAmount = parseInt($(this).data('amount')) || 0;
                let currentVal = parseInt($('#input_nominal').val().replace(/[^0-9]/g, '')) || 0;
                currentVal += addAmount;
                $('#input_nominal').val(formatRupiah(currentVal.toString())).trigger('input');
            });

            $('.btn-clear-nominal').on('click', function() {
                $('#input_nominal').val('').trigger('input');
            });

            // 5. Category Dropdown Change Handler with Auto COA Mapping
            $('#input_kategori').on('change', function() {
                const val = $(this).val();
                if (!val) {
                    $('#kategoriDescHint').html('<i class="bi bi-info-circle me-1"></i>Otomatis menghubungkan Akun Beban (COA Lawan).');
                    $('#infoCoaAutoMappedInput').addClass('d-none');
                    return;
                }

                const selectedOpt = $(this).find('option:selected');
                const coaId = selectedOpt.data('coa-id');
                const coaCode = selectedOpt.data('coa-code');
                const desc = selectedOpt.data('desc');

                if (desc) {
                    $('#kategoriDescHint').html(`<i class="bi bi-info-circle me-1 text-info"></i>${$('<div>').text(desc).html()}`);
                } else {
                    $('#kategoriDescHint').html('<i class="bi bi-info-circle me-1"></i>Otomatis menghubungkan Akun Beban (COA Lawan).');
                }

                // Auto Select Lawan COA sesuai pemetaan (Prioritas: coa_id, Fallback: coa_code)
                if (coaId && $('#input_lawan_coa_id option[value="' + coaId + '"]').length) {
                    $('#input_lawan_coa_id').val(coaId).trigger('change');
                    const $matchingOption = $('#input_lawan_coa_id option[value="' + coaId + '"]');
                    $('#textCoaAutoMappedInput').text($matchingOption.text().trim());
                    $('#infoCoaAutoMappedInput').removeClass('d-none');
                } else if (coaCode) {
                    const $matchingOption = $('#input_lawan_coa_id option[data-code="' + coaCode + '"]');
                    if ($matchingOption.length) {
                        $('#input_lawan_coa_id').val($matchingOption.val()).trigger('change');
                        $('#textCoaAutoMappedInput').text($matchingOption.text().trim());
                        $('#infoCoaAutoMappedInput').removeClass('d-none');
                    }
                } else {
                    $('#infoCoaAutoMappedInput').addClass('d-none');
                }
            });

            // Edit Category Dropdown Change Handler
            $('#edit_input_kategori').on('change', function() {
                const selectedOpt = $(this).find('option:selected');
                const coaId = selectedOpt.data('coa-id');
                const coaCode = selectedOpt.data('coa-code');
                const desc = selectedOpt.data('desc');

                if (desc) {
                    $('#editKategoriDescHint').html(`<i class="bi bi-info-circle me-1 text-info"></i>${$('<div>').text(desc).html()}`);
                } else {
                    $('#editKategoriDescHint').html('<i class="bi bi-info-circle me-1"></i>Otomatis menghubungkan Akun Beban (COA Lawan).');
                }

                // Auto Select Lawan COA pada modal edit (Prioritas: coa_id, Fallback: coa_code)
                if (coaId && $('#edit_input_lawan_coa_id option[value="' + coaId + '"]').length) {
                    $('#edit_input_lawan_coa_id').val(coaId).trigger('change');
                    const $matchingOption = $('#edit_input_lawan_coa_id option[value="' + coaId + '"]');
                    $('#textCoaAutoMappedEdit').text($matchingOption.text().trim());
                    $('#infoCoaAutoMappedEdit').removeClass('d-none');
                } else if (coaCode) {
                    const $matchingOption = $('#edit_input_lawan_coa_id option[data-code="' + coaCode + '"]');
                    if ($matchingOption.length) {
                        $('#edit_input_lawan_coa_id').val($matchingOption.val()).trigger('change');
                        $('#textCoaAutoMappedEdit').text($matchingOption.text().trim());
                        $('#infoCoaAutoMappedEdit').removeClass('d-none');
                    }
                }
            });

            // Feedback visual saat Lawan COA dipilih
            $('#input_lawan_coa_id').on('change', function() {
                const selectedText = $(this).find('option:selected').text();
                if ($(this).val()) {
                    $('#textCoaAutoMappedInput').text(selectedText.trim());
                    $('#infoCoaAutoMappedInput').removeClass('d-none');
                } else {
                    $('#infoCoaAutoMappedInput').addClass('d-none');
                }
            });

            $('#edit_input_lawan_coa_id').on('change', function() {
                const selectedText = $(this).find('option:selected').text();
                if ($(this).val()) {
                    $('#textCoaAutoMappedEdit').text(selectedText.trim());
                    $('#infoCoaAutoMappedEdit').removeClass('d-none');
                } else {
                    $('#infoCoaAutoMappedEdit').addClass('d-none');
                }
            });

            // 6. Image Receipt Live Preview & Size Indicator
            const pdfIconDataUri = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='64' height='64' fill='%23dc2626' class='bi bi-file-earmark-pdf' viewBox='0 0 16 16'><path d='M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z'/><path d='M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.143.244-.427.721-.698 1.155-.933.257-.14.512-.266.764-.377.169-.074.33-.144.482-.21a19.68 19.68 0 0 0-.077-.42c-.08-.387-.156-.818-.156-1.21 0-.61.267-1.127.608-1.42.341-.293.74-.385 1.1-.285.357.099.555.457.575.822.02.366-.118.86-.33 1.399-.17.433-.404.935-.688 1.488.217.145.44.296.666.452.338.234.65.485.928.751.278.267.458.557.48.869.023.313-.107.643-.362.883-.255.24-.593.332-.93.252-.338-.08-.636-.328-.868-.646a7.284 7.284 0 0 1-.58-.962 18.06 18.06 0 0 1-1.3-.284c-.45-.098-.867-.2-1.24-.308a9.426 9.426 0 0 1-.689.96z'/></svg>";

            $('#input_lampiran_nota').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileSizeKb = (file.size / 1024).toFixed(1);
                    const fileSizeMb = (file.size / (1024 * 1024)).toFixed(2);
                    const sizeStr = file.size >= 1024 * 1024 ? `${fileSizeMb} MB` : `${fileSizeKb} KB`;

                    $('#previewReceiptFileName').text(file.name);
                    $('#previewReceiptOriginalSize').text(`Ukuran asal: ${sizeStr}`);

                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            $('#imgReceiptPreview').attr('src', evt.target.result);
                            $('#previewReceiptContainer').removeClass('d-none');
                        };
                        reader.readAsDataURL(file);
                    } else if (file.type === 'application/pdf') {
                        $('#imgReceiptPreview').attr('src', pdfIconDataUri);
                        $('#previewReceiptContainer').removeClass('d-none');
                    } else {
                        $('#previewReceiptContainer').removeClass('d-none');
                    }
                } else {
                    $('#previewReceiptContainer').addClass('d-none');
                }
            });

            $('#btnRemoveReceiptPreview').on('click', function() {
                $('#input_lampiran_nota').val('');
                $('#previewReceiptContainer').addClass('d-none');
            });

            // Nonaktifkan autocomplete pada seluruh form kas kecil
            $('#formInputPettyCash, #formEditPettyCash').attr('autocomplete', 'off');
            $('#formInputPettyCash input, #formInputPettyCash textarea, #formEditPettyCash input, #formEditPettyCash textarea').attr('autocomplete', 'off');

            // 6b. Sinkronisasi Dinamis Banner Saldo Kas Sesuai Akun Kas yang Dipilih
            function syncBannerSaldoKas() {
                const $select = $('#input_coa_kas_id');
                const $opt = $select.find(':selected');
                const hasOption = $opt.length && $opt.val() !== '';
                const rawSaldo = parseFloat($opt.attr('data-saldo') || $opt.data('saldo')) || 0;
                const namaAkun = $opt.attr('data-nama') || $opt.data('nama') || 'Kas';

                const $banner = $('#bannerAlertSaldoKas');
                const $icon = $('#bannerIconSaldoKas');
                const $label = $('#bannerLabelSaldoKas');
                const $nominal = $('#bannerNominalSaldoKas');
                const $keterangan = $('#bannerKeteranganSaldoKas');
                const $btnTopUp = $('#bannerLinkTopUpKas');

                if (!hasOption) {
                    $banner.removeClass('bg-info-subtle text-info-emphasis border-info-subtle alert-info')
                           .addClass('bg-danger-subtle text-danger-emphasis border-danger-subtle alert-danger');
                    $icon.removeClass('bi-info-circle-fill text-info').addClass('bi-exclamation-triangle-fill text-danger');
                    $label.text('Belum Memilih Akun Kas:');
                    $nominal.text('(Rp 0)');
                    $keterangan.text('Silakan pilih akun kas pengeluaran terlebih dahulu.');
                    $btnTopUp.removeClass('btn-xs btn-outline-info').addClass('btn-sm btn-danger text-white fw-bold').html('Top Up Saldo <i class="bi bi-arrow-right ms-1"></i>');
                    return;
                }

                if (rawSaldo <= 0) {
                    $banner.removeClass('bg-info-subtle text-info-emphasis border-info-subtle alert-info')
                           .addClass('bg-danger-subtle text-danger-emphasis border-danger-subtle alert-danger');
                    $icon.removeClass('bi-info-circle-fill text-info').addClass('bi-exclamation-triangle-fill text-danger');
                    $label.text(`Saldo ${namaAkun} Kosong:`);
                    $nominal.text(`(Rp 0,00)`);
                    $keterangan.html(`Belum dapat mencatat pengeluaran. Harap lakukan <strong>Top Up</strong> via Transfer Kas &amp; Bank terlebih dahulu.`);
                    $btnTopUp.removeClass('btn-xs btn-outline-info').addClass('btn-sm btn-danger text-white fw-bold').html('Top Up Saldo <i class="bi bi-arrow-right ms-1"></i>');
                } else {
                    $banner.removeClass('bg-danger-subtle text-danger-emphasis border-danger-subtle alert-danger')
                           .addClass('bg-info-subtle text-info-emphasis border-info-subtle alert-info');
                    $icon.removeClass('bi-exclamation-triangle-fill text-danger').addClass('bi-info-circle-fill text-info');
                    $label.text(`Sisa Saldo ${namaAkun}:`);
                    $nominal.text(`Rp ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rawSaldo)}`);
                    $keterangan.text('(Khusus pengeluaran operasional).');
                    $btnTopUp.removeClass('btn-sm btn-danger text-white fw-bold').addClass('btn-xs btn-outline-info').html('Transfer Kas &amp; Bank <i class="bi bi-box-arrow-up-right ms-1"></i>');
                }
            }

            $(document).on('change', '#input_coa_kas_id', function() {
                syncBannerSaldoKas();
            });

            $('#modalInputPettyCash').on('shown.bs.modal', function() {
                syncBannerSaldoKas();
            });

            // 7. Keamanan Validasi Karakter & Alert Peringatan Real-time di Tengah Layar
            function setupInputCharacterLimit(selector, maxLen, fieldLabel) {
                const $el = $(selector);
                $el.attr('maxlength', maxLen).attr('autocomplete', 'off');
                let alertTimer = null;

                $el.on('input', function() {
                    const currentLen = $(this).val().length;
                    if (currentLen >= maxLen) {
                        clearTimeout(alertTimer);
                        alertTimer = setTimeout(function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Batas Karakter Maksimal!',
                                text: `${fieldLabel} telah mencapai batas maksimal (${maxLen} karakter).`,
                                position: 'center',
                                confirmButtonColor: '#00bcd4',
                                confirmButtonText: 'Mengerti',
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }, 120);
                    }
                });
            }

            // Daftarkan listener batas karakter
            setupInputCharacterLimit('#input_nominal', 20, 'Nominal Pengeluaran');
            setupInputCharacterLimit('#input_nama_pengaju', 40, 'Nama Pemohon');
            setupInputCharacterLimit('#input_kategori', 40, 'Kategori Transaksi');
            setupInputCharacterLimit('#input_keterangan', 50, 'Catatan / Keperluan');

            setupInputCharacterLimit('#edit_input_nominal', 20, 'Nominal Pengeluaran');
            setupInputCharacterLimit('#edit_input_nama_pengaju', 40, 'Nama Pemohon');
            setupInputCharacterLimit('#edit_input_kategori', 40, 'Kategori Transaksi');
            setupInputCharacterLimit('#edit_input_keterangan', 50, 'Catatan / Keperluan');

            // 8. Submit Form Input Petty Cash
            $('#formInputPettyCash').on('submit', function(e) {
                e.preventDefault();

                // Validasi Keamanan Karakter Input
                const rawNominalStr = $('#input_nominal').val() || '';
                const namaPengajuStr = ($('#input_nama_pengaju').val() || '').trim();
                const kategoriStr = ($('#input_kategori').val() || '').trim();
                const keteranganStr = ($('#input_keterangan').val() || '').trim();

                if (rawNominalStr.length > 20) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Melebihi Batas!',
                        text: 'Nominal pengeluaran tidak boleh lebih dari 20 karakter.',
                        position: 'center',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Mengerti'
                    });
                    $('#input_nominal').focus();
                    return false;
                }

                if (namaPengajuStr.length > 40) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama Pemohon Terlalu Panjang!',
                        text: 'Nama pemohon tidak boleh lebih dari 40 karakter.',
                        position: 'center',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Mengerti'
                    });
                    $('#input_nama_pengaju').focus();
                    return false;
                }

                if (kategoriStr.length > 40) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kategori Terlalu Panjang!',
                        text: 'Kategori transaksi tidak boleh lebih dari 40 karakter.',
                        position: 'center',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Mengerti'
                    });
                    $('#input_kategori').focus();
                    return false;
                }

                if (keteranganStr.length > 50) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Catatan Melebihi Batas!',
                        text: 'Catatan / keperluan tidak boleh lebih dari 50 karakter.',
                        position: 'center',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Mengerti'
                    });
                    $('#input_keterangan').focus();
                    return false;
                }

                // Validasi Foto / Bukti Struk (Wajib Diisi)
                const fileInput = document.getElementById('input_lampiran_nota');
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Struk Wajib Diisi!',
                        text: 'Harap lampirkan foto nota, struk, atau bukti pembayaran sebelum menyimpan transaksi.',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Upload Foto'
                    });
                    setTimeout(() => {
                        $('#input_lampiran_nota').focus();
                    }, 250);
                    return false;
                }

                // Ambil saldo dan nama akun kas secara dinamis sesuai option yang dipilih
                const $selectedKasOpt = $('#input_coa_kas_id').find(':selected');
                const availableBalance = parseFloat($selectedKasOpt.attr('data-saldo') || $selectedKasOpt.data('saldo')) || 0;
                const namaAkunKas = $selectedKasOpt.attr('data-nama') || $selectedKasOpt.data('nama') || 'Kas';
                const rawNominal = parseFloat(rawNominalStr.replace(/[^0-9]/g, '')) || 0;

                if (availableBalance <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: `Saldo ${namaAkunKas} Kosong (Rp 0)`,
                        text: `Akun ${namaAkunKas} belum memiliki saldo. Harap lakukan Top Up saldo via menu Transfer Kas & Bank terlebih dahulu.`,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                if (rawNominal > availableBalance) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Saldo Tidak Mencukupi!',
                        text: `Sisa saldo ${namaAkunKas} saat ini Rp ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(availableBalance)}, sedangkan pengeluaran yang diinput Rp ${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(rawNominal)}.`,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Perbaiki'
                    });
                    return false;
                }

                const formData = new FormData(this);
                const submitBtn = $('#btnSubmitPettyCash');

                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                $.ajax({
                    url: "{{ route('accounting.petty-cash.store') }}",
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i><span>Simpan &amp; Jurnal</span>');
                        if (response.success) {
                            $('#modalInputPettyCash').modal('hide');
                            $('#formInputPettyCash')[0].reset();
                            $('#previewReceiptContainer').addClass('d-none');
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: response.message,
                                confirmButtonColor: '#059669',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i><span>Simpan &amp; Jurnal</span>');
                        let errMsg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan!',
                            text: errMsg,
                            confirmButtonColor: '#e11d48'
                        });
                    }
                });
            });

            // 9. Click Mobile Card or Table Row to Open Detail
            const canDetailPettyCash = {{ auth()->user()->can('detail-petty-cash') ? 'true' : 'false' }};
            $(document).on('click', '.pc-trx-mobile-card, .pc-trx-row', function(e) {
                if (!canDetailPettyCash) return;
                if ($(e.target).closest('button, a, .btn-preview-bukti, .pc-action-group').length) {
                    return;
                }
                const id = $(this).data('id');
                if (id) {
                    loadPettyCashDetail(id);
                }
            });

            $(document).on('click', '.btn-detail-pc', function(e) {
                e.stopPropagation();
                if (!canDetailPettyCash) return;
                const id = $(this).data('id');
                loadPettyCashDetail(id);
            });

            // 9b. Handler Preview Modal Bukti / Nota
            window.previewBukti = function(elem, e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                const $btn = $(elem);
                const url = $btn.attr('data-url') || $btn.data('url');
                const nomor = $btn.attr('data-nomor') || $btn.data('nomor') || 'PC-TRX';
                const kategori = $btn.attr('data-kategori') || $btn.data('kategori') || '-';
                const nominal = $btn.attr('data-nominal') || $btn.data('nominal') || 'Rp 0';

                if (!url) return;

                $('#previewBuktiNomor').text(nomor);
                $('#previewBuktiKategori').text(kategori);
                $('#previewBuktiNominal').text(nominal);
                $('#previewBuktiDownloadBtn').attr('href', url);

                const isPdf = url.toLowerCase().split('?')[0].endsWith('.pdf');
                if (isPdf) {
                    $('#previewBuktiImg').addClass('d-none').attr('src', '');
                    $('#previewBuktiPdfPlaceholder').addClass('d-none');
                    $('#previewBuktiPdfIframe').removeClass('d-none').attr('src', url);
                    $('#previewBuktiPdfBtn').attr('href', url);
                } else {
                    $('#previewBuktiPdfIframe').addClass('d-none').attr('src', '');
                    $('#previewBuktiPdfPlaceholder').addClass('d-none');
                    $('#previewBuktiImg').removeClass('d-none').attr('src', url);
                }

                const modalEl = document.getElementById('modalPreviewBukti');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    } else {
                        $('#modalPreviewBukti').modal('show');
                    }
                } else {
                    window.open(url, '_blank');
                }
            };

            $(document).on('click', '.btn-preview-bukti', function(e) {
                window.previewBukti(this, e);
            });

            $('#modalPreviewBukti').on('hidden.bs.modal', function () {
                $('#previewBuktiPdfIframe').attr('src', '');
                $('#previewBuktiImg').attr('src', '');
            });

            // 9c. Handler Per-Page Selector (10, 25, 50, 100 Baris)
            $(document).on('change', '.select-per-page', function() {
                const newPerPage = $(this).val();
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('per_page', newPerPage);
                currentUrl.searchParams.set('page', '1'); // Reset ke halaman pertama saat jumlah baris diubah
                window.location.href = currentUrl.toString();
            });

            const escHtml = s => $('<div>').text(s ?? '').html();

        // Load Detail Function
            window.loadPettyCashDetail = function(id) {
                $('#modalDetailPettyCash').modal('show');
                $('#detail_nomor_header').text('Memuat...');
                $('#detail_nominal').text('Rp ...');

                $.ajax({
                    url: `/accounting/petty-cash/${id}`,
                    method: 'GET',
                    success: function(res) {
                        if (res.success && res.data) {
                            const d = res.data;
                            const isExpense = (d.tipe === 'Pengeluaran' || d.tipe === 'Klaim');

                            $('#detail_nomor_header').text(d.nomor_transaksi);
                            $('#detail_nomor').text(d.nomor_transaksi);
                            $('#detail_company_name').text(d.branch ? d.branch.nama_cabang : (d.company ? d.company.nama_perusahaan : 'PT Pusat'));
                            $('#detail_entitas_badge').text(d.branch ? 'Cabang' : 'Pusat');
                            
                            if (d.tender) {
                                $('#detail_tender_container').removeClass('d-none');
                                $('#detail_tender_title').text(d.tender.nomor_kontrak + ' - ' + d.tender.nama_tender);
                            } else {
                                $('#detail_tender_container').addClass('d-none');
                            }

                            // Nominal
                            $('#detail_tipe_label').text('TOTAL ' + d.tipe.toUpperCase());
                            $('#detail_nominal').removeClass('text-danger text-success')
                                .addClass(isExpense ? 'text-danger' : 'text-success')
                                .text((isExpense ? '- ' : '+ ') + 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(d.nominal));
                            
                            const timeStr = d.created_at ? (' ' + new Date(d.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) + ' WIB') : '';
                            $('#detail_tanggal').text('Tanggal: ' + (d.tanggal ? (new Date(d.tanggal).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) + timeStr) : '-'));
                            $('#detail_kategori').text(d.kategori);
                            $('#detail_pengaju').text(d.nama_pengaju || '-');
                            $('#detail_creator').text(d.creator ? (d.creator.nama_lengkap || d.creator.username) : 'System');
                            $('#detail_keterangan').text(d.keterangan || 'Tidak ada catatan tambahan.');

                            // COA Breakdown
                            $('#detail_coa_kas').text((d.coa_kas ? d.coa_kas.code + ' - ' + d.coa_kas.name : '-'));
                            $('#detail_lawan_coa').text((d.lawan_coa ? d.lawan_coa.code + ' - ' + d.lawan_coa.name : '-'));
                            
                            // Jurnal breakdown
                            if (d.jurnal) {
                                $('#detail_nomor_jurnal').text(d.jurnal.nomor_jurnal);
                                let tbodyHtml = '';
                                if (d.jurnal.details && d.jurnal.details.length > 0) {
                                    d.jurnal.details.forEach(function(item) {
                                        tbodyHtml += `
                                            <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.15);">
                                                <td class="ps-2.5 py-2"><span class="font-monospace fw-bold text-info">${item.account ? escHtml(item.account.code) : ''}</span> <span class="pc-text-muted-adaptive">${item.account ? escHtml(item.account.name) : ''}</span></td>
                                                <td class="text-end font-monospace py-2 text-success fw-semibold">${item.debit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(item.debit) : '-'}</td>
                                                <td class="text-end pe-2.5 font-monospace pc-text-muted-adaptive py-2">${item.kredit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(item.kredit) : '-'}</td>
                                            </tr>
                                        `;
                                    });
                                }
                                $('#detail_jurnal_tbody').html(tbodyHtml);
                            } else {
                                $('#detail_nomor_jurnal').text('Tidak ada jurnal');
                                $('#detail_jurnal_tbody').html('<tr><td colspan="3" class="text-center text-muted py-3">Jurnal otomatis belum tercatat.</td></tr>');
                            }

                            // Receipt Photo
                            if (res.lampiran_url) {
                                $('#detail_receipt_photo_container').removeClass('d-none');
                                $('#detail_receipt_img').attr('src', res.lampiran_url);
                                $('#detail_receipt_download_btn').attr('href', res.lampiran_url);
                            } else {
                                $('#detail_receipt_photo_container').addClass('d-none');
                            }

                            // Reversal Jurnal breakdown (Jika Transaksi Dibatalkan)
                            if (d.reversal_jurnal) {
                                $('#detail_reversal_jurnal_container').removeClass('d-none');
                                $('#detail_nomor_reversal_jurnal').text(d.reversal_jurnal.nomor_jurnal);
                                let revBodyHtml = '';
                                if (d.reversal_jurnal.details && d.reversal_jurnal.details.length > 0) {
                                    d.reversal_jurnal.details.forEach(function(item) {
                                        revBodyHtml += `
                                            <tr style="border-bottom: 1px solid rgba(244, 63, 94, 0.2);">
                                                <td class="ps-2.5 py-2"><span class="font-monospace fw-bold text-danger">${item.account ? escHtml(item.account.code) : ''}</span> <span class="pc-text-muted-adaptive">${item.account ? escHtml(item.account.name) : ''}</span></td>
                                                <td class="text-end font-monospace py-2 text-danger fw-semibold">${item.debit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(item.debit) : '-'}</td>
                                                <td class="text-end pe-2.5 font-monospace pc-text-muted-adaptive py-2">${item.kredit > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(item.kredit) : '-'}</td>
                                            </tr>
                                        `;
                                    });
                                } else {
                                    revBodyHtml = '<tr><td colspan="3" class="text-center text-muted py-2">Tidak ada detail baris pembalik.</td></tr>';
                                }
                                $('#detail_reversal_jurnal_tbody').html(revBodyHtml);
                            } else {
                                $('#detail_reversal_jurnal_container').addClass('d-none');
                            }

                            if (d.status === 'Dibatalkan') {
                                $('#detail_status_badge').removeClass('bg-success-subtle text-success border-success-subtle')
                                    .addClass('bg-danger-subtle text-danger border-danger-subtle')
                                    .text('Dibatalkan');
                                $('#btnBatalFromDetail').addClass('d-none');
                                if (d.alasan_pembatalan) {
                                    $('#detail_container_alasan_batal').removeClass('d-none');
                                    $('#detail_alasan_batal').text(d.alasan_pembatalan);
                                } else {
                                    $('#detail_container_alasan_batal').addClass('d-none');
                                }
                            } else {
                                $('#detail_status_badge').removeClass('bg-danger-subtle text-danger border-danger-subtle')
                                    .addClass('bg-success-subtle text-success border-success-subtle')
                                    .text('Disetujui');
                                $('#btnBatalFromDetail').removeClass('d-none').attr('data-id', d.id).data('nomor', d.nomor_transaksi);
                                $('#detail_container_alasan_batal').addClass('d-none');
                            }
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memuat detail transaksi.', 'error');
                    }
                });
            };

            // Preview Struk / Bukti dari Modal Detail Voucher
            $(document).on('click', '#detail_receipt_img, #detail_receipt_download_btn', function(e) {
                e.preventDefault();
                const url = $(this).attr('src') || $(this).attr('href');
                if (!url || url === '#' || url === '') return;
                const nomor = $('#detail_nomor').text() || 'PC-TRX';
                const nominal = $('#detail_nominal').text() || 'Rp 0';
                const kategori = $('#detail_kategori').text() || '-';

                $('#previewBuktiNomor').text(nomor);
                $('#previewBuktiKategori').text(kategori);
                $('#previewBuktiNominal').text(nominal);
                $('#previewBuktiDownloadBtn').attr('href', url);

                const isPdf = url.toLowerCase().split('?')[0].endsWith('.pdf');
                if (isPdf) {
                    $('#previewBuktiImg').addClass('d-none').attr('src', '');
                    $('#previewBuktiPdfPlaceholder').addClass('d-none');
                    $('#previewBuktiPdfIframe').removeClass('d-none').attr('src', url);
                    $('#previewBuktiPdfBtn').attr('href', url);
                } else {
                    $('#previewBuktiPdfIframe').addClass('d-none').attr('src', '');
                    $('#previewBuktiPdfPlaceholder').addClass('d-none');
                    $('#previewBuktiImg').removeClass('d-none').attr('src', url);
                }

                const modalEl = document.getElementById('modalPreviewBukti');
                if (modalEl) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    } else {
                        $('#modalPreviewBukti').modal('show');
                    }
                }
            });

            // 10. Handler Batal Transaksi (Void) dengan Input Alasan Pembatalan
            // 10. Handler Batal Transaksi (Void) dengan Input Alasan Pembatalan (Maks. 40 Karakter)
            $(document).on('click', '.btn-batal-pc, #btnBatalFromDetail', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id') || $(this).data('id');
                const nomor = $(this).data('nomor') || $(this).attr('data-nomor') || 'transaksi ini';
                const detailModalEl = document.getElementById('modalDetailPettyCash');

                // Matikan focus trap Bootstrap 5 agar modal detail TIDAK perlu di-close dan keyboard bisa mengetik bebas di SweetAlert
                if (detailModalEl) {
                    detailModalEl.removeAttribute('tabindex');
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const inst = bootstrap.Modal.getInstance(detailModalEl) || bootstrap.Modal.getOrCreateInstance(detailModalEl);
                        if (inst && inst._focustrap) {
                            inst._focustrap.deactivate();
                        }
                    }
                }
                $(document).off('focusin.bs.modal');

                Swal.fire({
                    title: 'Batalkan Transaksi?',
                    html: `
                        <div class="text-start">
                            <div class="p-3 rounded-3 mb-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-6"></i>
                                    <span style="color: #ffffff; font-weight: 700; font-size: 13.5px;">Nomor: ${escHtml(nomor)}</span>
                                </div>
                                <div style="color: #cbd5e1; font-size: 12px; line-height: 1.5;">
                                    Saldo kas kecil akan dikembalikan ke saldo aktif dan jurnal akuntansi otomatis dibatalkan.
                                </div>
                            </div>
                            <label for="swal_input_alasan_batal" class="d-block mb-1.5 fw-bold" style="color: #ffffff !important; font-size: 13px;">
                                Keterangan / Alasan Pembatalan <span style="color: #fb7185;">*</span>
                            </label>
                            <textarea id="swal_input_alasan_batal" class="form-control" rows="2" 
                                placeholder="Contoh: Salah input nominal / nota batal..."
                                maxlength="40" style="color: #ffffff !important; background-color: #0b1320 !important; border: 1px solid rgba(255, 255, 255, 0.28) !important; font-size: 13px; border-radius: 8px; line-height: 1.5; padding: 10px 12px; resize: none;"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1.5" style="color: #94a3b8; font-size: 11.5px;">
                                <span><i class="bi bi-info-circle me-1 text-info"></i>Wajib diisi (maks. 40 karakter)</span>
                                <span id="swal_alasan_char_counter" style="font-family: monospace; color: #e2e8f0; font-weight: 600;">0 / 40</span>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    background: '#111b2b',
                    color: '#f8fafc',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#475569',
                    confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Ya, Batalkan Transaksi',
                    cancelButtonText: '<i class="bi bi-arrow-left me-1"></i> Kembali',
                    reverseButtons: true,
                    focusConfirm: false,
                    didOpen: () => {
                        const textarea = document.getElementById('swal_input_alasan_batal');
                        const counter = document.getElementById('swal_alasan_char_counter');
                        if (textarea) {
                            setTimeout(() => {
                                textarea.focus();
                            }, 50);

                            // Cegah event bubbling agar pengetikan keyboard tidak diintervensi oleh modal di belakang
                            textarea.addEventListener('keydown', function(ev) {
                                ev.stopPropagation();
                            });
                            textarea.addEventListener('focusin', function(ev) {
                                ev.stopPropagation();
                            });
                            textarea.addEventListener('input', function() {
                                if (counter) counter.textContent = `${this.value.length} / 40`;
                            });
                        }
                    },
                    willClose: () => {
                        // Aktifkan kembali fokus normal modal detail saat SweetAlert ditutup
                        if (detailModalEl) {
                            detailModalEl.setAttribute('tabindex', '-1');
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const inst = bootstrap.Modal.getInstance(detailModalEl);
                                if (inst && inst._focustrap && $(detailModalEl).hasClass('show')) {
                                    inst._focustrap.activate();
                                }
                            }
                        }
                    },
                    preConfirm: () => {
                        const textarea = document.getElementById('swal_input_alasan_batal');
                        const alasan = textarea ? textarea.value.trim() : '';
                        if (!alasan) {
                            Swal.showValidationMessage('Keterangan / alasan pembatalan wajib diisi!');
                            return false;
                        }
                        if (alasan.length > 40) {
                            Swal.showValidationMessage('Keterangan alasan pembatalan maksimal 40 karakter.');
                            return false;
                        }
                        return alasan;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const alasanPembatalan = result.value;

                        Swal.fire({
                            title: 'Memproses Pembatalan...',
                            html: '<span style="color: #cbd5e1;">Mengembalikan saldo kas dan membatalkan jurnal akuntansi...</span>',
                            background: '#111b2b',
                            color: '#f8fafc',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/accounting/petty-cash/${id}/batal`,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                alasan_pembatalan: alasanPembatalan
                            },
                            success: function(res) {
                                $('#modalDetailPettyCash').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Transaksi Berhasil Dibatalkan!',
                                    html: `<span style="color: #cbd5e1;">${res.message || 'Transaksi telah dibatalkan dan saldo telah dikembalikan.'}</span>`,
                                    background: '#111b2b',
                                    color: '#f8fafc',
                                    confirmButtonColor: '#059669',
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Membatalkan!',
                                    text: xhr.responseJSON?.message || 'Gagal membatalkan transaksi.',
                                    background: '#111b2b',
                                    color: '#f8fafc',
                                });
                            }
                        });
                    }
                });
            });

            // ===== 11B. MODAL LOOKUP PEMOHON / PENGAJU PETTY CASH =====
            window.bukaModalLookupPemohon = function() {
                const currentVal = $('#input_nama_pengaju').val() || '';
                
                $('#searchLookupPemohonInput').val(currentVal);
                $('#modalLookupPemohon').modal('show');

                setTimeout(function() {
                    $('#searchLookupPemohonInput').trigger('input').focus().select();
                }, 350);
            };

            window.pilihPemohonLookup = function(nama) {
                $('#input_nama_pengaju').val(nama).trigger('input');
                $('#input_nama_pengaju').focus();
                $('#modalLookupPemohon').modal('hide');
            };

            // Live Search Filter di Modal Lookup Pemohon
            $('#searchLookupPemohonInput').on('input', function() {
                const q = ($(this).val() || '').trim().toLowerCase();
                const rawVal = ($(this).val() || '').trim();

                if (rawVal.length > 0) {
                    $('#btnClearSearchLookupPemohon').removeClass('d-none');
                    $('#lookupPemohonCustomNotice').removeClass('d-none');
                    $('#lookupPemohonTypedName').text(rawVal);
                } else {
                    $('#btnClearSearchLookupPemohon').addClass('d-none');
                    $('#lookupPemohonCustomNotice').addClass('d-none');
                }

                let visibleCount = 0;
                $('.row-pemohon-lookup').each(function() {
                    const nama = $(this).data('nama') || '';
                    if (!q || nama.indexOf(q) !== -1) {
                        $(this).removeClass('d-none');
                        visibleCount++;
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                if (visibleCount === 0 && $('.row-pemohon-lookup').length > 0) {
                    $('#noMatchPemohonRow').removeClass('d-none');
                } else {
                    $('#noMatchPemohonRow').addClass('d-none');
                }

                $('#lookupPemohonCountDisplay').text(visibleCount);
            });

            // Klik tombol "Pilih" pada baris tabel lookup pemohon
            $(document).on('click', '.btn-pilih-pemohon-item', function() {
                const nama = $(this).data('nama');
                pilihPemohonLookup(nama);
            });

            // Klik tombol "Pakai Nama Ini" dari kata kunci yang diketik
            $('#btnUseCustomTypedName').on('click', function() {
                const typedName = ($('#searchLookupPemohonInput').val() || '').trim();
                if (typedName) {
                    pilihPemohonLookup(typedName);
                }
            });

            // Enter key on searchLookupPemohonInput selects the typed name
            $('#searchLookupPemohonInput').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const typed = ($(this).val() || '').trim();
                    if (typed) {
                        pilihPemohonLookup(typed);
                    }
                }
            });

            // Bootstrap 5 Stacked Modal Scroll Fix
            $('#modalLookupPemohon').on('hidden.bs.modal', function() {
                if ($('#modalInputPettyCash').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // 12. Print Voucher Function
            window.printPettyCashVoucher = function() {
                const printContents = document.getElementById('receiptPrintArea').innerHTML;
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Voucher Kas Kecil</title>');
                printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
                printWindow.document.write('</head><body class="p-4">');
                printWindow.document.write(printContents);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(function() {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            };
        });
    </script>
    @endpush
</x-layouts>
