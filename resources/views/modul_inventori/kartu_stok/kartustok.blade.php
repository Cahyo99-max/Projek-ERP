<x-layouts title="Kartu Stok & Mutasi Gudang">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/kartu_stok.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stok_adjustment.css') }}">

    <div class="container-fluid py-3 px-2 px-md-4">
        
        {{-- ── PAGE HEADER & ACTIONS ────────────────────────────────────────── --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-extrabold page-title mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill text-primary"></i>
                    <span>Kartu Stok & Mutasi Gudang</span>
                </h4>
                <div class="text-muted small mb-0 d-flex align-items-center gap-2 flex-wrap mt-1">
                    <!-- <span>Histori buku besar mutasi persediaan & arus keluar-masuk stok.</span> -->
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 font-monospace fw-bold shadow-xs" style="font-size: 0.78rem;">
                        <i class="bi bi-calendar-range me-1"></i>Periode: {{ date('d/m/Y', strtotime($startDate)) }} — {{ date('d/m/Y', strtotime($endDate)) }}
                    </span>
                </div>
            </div>
            
            @php
                $hasCustomDates = (request('start_date') && request('start_date') != date('Y-m-01')) || (request('end_date') && request('end_date') != date('Y-m-d'));
                $isFiltered = !empty($barangId) || !empty($gudangId) || !empty($tipeMutasi) || $hasCustomDates;
                $activeFilterCount = ($barangId ? 1 : 0) + ($gudangId ? 1 : 0) + ($tipeMutasi ? 1 : 0) + ($hasCustomDates ? 1 : 0);
            @endphp

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalFilter">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter & Cetak</span>
                    @if($isFiltered)
                        <span class="badge bg-white text-primary rounded-pill fw-bold" style="font-size:0.75rem;">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            </div>
        </div>

        {{-- ── ACTIVE FILTER BAR (IF FILTER IS ACTIVE) ─────────────────────── --}}
        @if($isFiltered)
            <div class="active-filter-alert p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm">
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <span class="fw-bold text-info me-1"><i class="bi bi-funnel-fill me-1"></i>Filter Aktif:</span>
                    <span class="badge bg-secondary text-white border-0 rounded-pill px-3 py-1.5 shadow-sm fw-bold" style="background-color: #475569 !important; font-size: 0.78rem;">
                        <i class="bi bi-calendar-range me-1"></i>Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
                    </span>
                    @if($tipeMutasi)
                        <span class="badge text-white border-0 rounded-pill px-3 py-1.5 shadow-sm fw-bold" style="background-color: #0284c7 !important; font-size: 0.78rem;">
                            <i class="bi bi-arrow-left-right me-1"></i>Tipe: {{ strtoupper(str_replace('_', ' ', $tipeMutasi)) }}
                        </span>
                    @endif
                    @if($barangId && ($bSelected = $barangs->firstWhere('id', $barangId)))
                        <span class="badge text-white border-0 rounded-pill px-3 py-1.5 shadow-sm fw-bold" style="background-color: #4f46e5 !important; font-size: 0.78rem;">
                            <i class="bi bi-box-seam me-1"></i>Barang: {{ $bSelected->nama_barang }}
                        </span>
                    @endif
                    @if($gudangId && ($gSelected = $gudangs->firstWhere('id', $gudangId)))
                        <span class="badge text-dark border-0 rounded-pill px-3 py-1.5 shadow-sm fw-bold" style="background-color: #f59e0b !important; font-size: 0.78rem;">
                            <i class="bi bi-building me-1"></i>Gudang: {{ $gSelected->nama_gudang }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('stock-card.index') }}" class="btn btn-sm btn-link text-decoration-none text-danger fw-bold p-0 ms-auto d-inline-flex align-items-center gap-1" style="font-size:0.82rem;">
                    <i class="bi bi-x-circle-fill"></i> Reset Filter
                </a>
            </div>
        @endif

        {{-- ── SUMMARY KPI CARDS (CINEMATIC GRADIENT CARDS) ────────────────── --}}
        <div class="row g-3 mb-4">
            <!-- 1. Stok Awal -->
            <div class="col-6 col-xl-3">
                <div class="card card-kpi rounded-4 h-100" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div class="overflow-hidden">
                            <div class="kpi-title">STOK AWAL PERIODE</div>
                            @php
                                $rAwal = round($stokAwalPeriode, 2);
                                $formattedStokAwal = (floor($rAwal) == $rAwal) ? number_format($rAwal, 0, ',', '.') : rtrim(rtrim(number_format($rAwal, 2, ',', '.'), '0'), ',');
                            @endphp
                            <div class="kpi-value text-truncate">{{ $formattedStokAwal }}</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-box-seam"></i> Awal Periode
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Total Masuk -->
            <div class="col-6 col-xl-3">
                <div class="card card-kpi rounded-4 h-100" style="background: linear-gradient(135deg, #047857 0%, #10b981 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div class="overflow-hidden">
                            <div class="kpi-title">TOTAL MASUK</div>
                            @php
                                $rMasuk = round($totalMasuk, 2);
                                $formattedTotalMasuk = (floor($rMasuk) == $rMasuk) ? number_format($rMasuk, 0, ',', '.') : rtrim(rtrim(number_format($rMasuk, 2, ',', '.'), '0'), ',');
                            @endphp
                            <div class="kpi-value text-truncate">+{{ $formattedTotalMasuk }}</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-arrow-down-circle"></i> Pemasukan
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-arrow-down-left-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Total Keluar -->
            <div class="col-6 col-xl-3">
                <div class="card card-kpi rounded-4 h-100" style="background: linear-gradient(135deg, #be123c 0%, #e11d48 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div class="overflow-hidden">
                            <div class="kpi-title">TOTAL KELUAR</div>
                            @php
                                $rKeluar = round($totalKeluar, 2);
                                $formattedTotalKeluar = (floor($rKeluar) == $rKeluar) ? number_format($rKeluar, 0, ',', '.') : rtrim(rtrim(number_format($rKeluar, 2, ',', '.'), '0'), ',');
                            @endphp
                            <div class="kpi-value text-truncate">-{{ $formattedTotalKeluar }}</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-arrow-up-circle"></i> Pengeluaran
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-arrow-up-right-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Stok Akhir -->
            <div class="col-6 col-xl-3">
                <div class="card card-kpi rounded-4 h-100" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                    <div class="d-flex justify-content-between align-items-start text-white">
                        <div class="overflow-hidden">
                            <div class="kpi-title">STOK AKHIR PERIODE</div>
                            @php
                                $rAkhir = round($stokAkhirPeriode, 2);
                                $formattedStokAkhir = (floor($rAkhir) == $rAkhir) ? number_format($rAkhir, 0, ',', '.') : rtrim(rtrim(number_format($rAkhir, 2, ',', '.'), '0'), ',');
                            @endphp
                            <div class="kpi-value text-truncate">{{ $formattedStokAkhir }}</div>
                            <div>
                                <span class="kpi-pill-badge">
                                    <i class="bi bi-archive-fill"></i> Akhir Periode
                                </span>
                            </div>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi bi-archive"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── MAIN MUTASI TABLE CARD ──────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            
            {{-- Card Header: Title & Horizontal Quick Filter Pills --}}
            <div class="card-header bg-transparent border-bottom p-3 p-md-3.5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <h6 class="fw-bold mb-0 text-body d-flex align-items-center gap-2">
                        <i class="bi bi-list-columns-reverse text-primary fs-5"></i>
                        <span>Histori Mutasi Stok</span>
                    </h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold font-monospace">
                        {{ $mutasi->count() }} Baris
                    </span>
                </div>

                {{-- Quick Filter Pills (Horizontal Scroll on Mobile) --}}
                <div class="quick-filter-scroll-wrapper">
                    @php
                        $activeTipe = $tipeMutasi ?? '';
                        function filterUrl($t) {
                            $query = request()->all();
                            if ($t === '') {
                                unset($query['tipe_mutasi']);
                            } else {
                                $query['tipe_mutasi'] = $t;
                            }
                            return route('stock-card.index', $query);
                        }
                    @endphp
                    
                    <a href="{{ filterUrl('') }}" class="quick-filter-pill {{ $activeTipe === '' ? 'btn btn-primary text-white shadow-sm' : 'btn btn-outline-secondary' }}">
                        <i class="bi bi-layers"></i> Semua
                    </a>
                    <a href="{{ filterUrl('masuk') }}" class="quick-filter-pill {{ $activeTipe === 'masuk' ? 'btn btn-success text-white shadow-sm' : 'btn btn-outline-success' }}">
                        <i class="bi bi-arrow-down-left-circle"></i> Barang Masuk
                    </a>
                    <a href="{{ filterUrl('keluar') }}" class="quick-filter-pill {{ $activeTipe === 'keluar' ? 'btn btn-danger text-white shadow-sm' : 'btn btn-outline-danger' }}">
                        <i class="bi bi-arrow-up-right-circle"></i> Barang Keluar
                    </a>
                    <a href="{{ filterUrl('transfer') }}" class="quick-filter-pill {{ $activeTipe === 'transfer' ? 'btn btn-info text-white shadow-sm' : 'btn btn-outline-info' }}">
                        <i class="bi bi-arrow-left-right"></i> Transfer Gudang
                    </a>
                    <a href="{{ filterUrl('opname') }}" class="quick-filter-pill {{ $activeTipe === 'opname' ? 'btn text-white shadow-sm' : 'btn' }}" style="{{ $activeTipe === 'opname' ? 'background-color: #9333ea; border-color: #9333ea;' : 'border-color: #9333ea; color: #9333ea;' }}">
                        <i class="bi bi-clipboard-check"></i> Stok Opname
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($mutasi->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="avatar-lg bg-light rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                        </div>
                        <h6 class="fw-bold text-body">Tidak ada data mutasi stok</h6>
                        <p class="text-muted small mb-0">Coba ubah rentang tanggal atau filter barang dan gudang.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-modern align-middle mb-0 w-100" id="tableKartuStok">
                            <thead>
                                <tr>
                                    <th style="width: 40px;" class="text-center">NO</th>
                                    <th style="min-width: 110px;">TANGGAL & WAKTU</th>
                                    <th style="min-width: 170px;">NAMA BARANG</th>
                                    <th style="min-width: 130px;">GUDANG</th>
                                    <th style="min-width: 90px;" class="text-center">TIPE</th>
                                    <th style="min-width: 85px;" class="text-center text-nowrap">STOK AWAL</th>
                                    <th style="min-width: 120px;" class="text-end text-nowrap">HARGA SATUAN</th>
                                    <th style="min-width: 85px;" class="text-center text-nowrap">JUMLAH</th>
                                    <th style="min-width: 130px;" class="text-end text-nowrap">TOTAL NILAI</th>
                                    <th style="min-width: 85px;" class="text-center text-nowrap">STOK AKHIR</th>
                                    <th style="min-width: 150px;">REFERENSI</th>
                                    <th style="min-width: 140px;">KETERANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mutasi as $index => $m)
                                <tr>
                                    <td class="small text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                    <td class="text-nowrap">
                                        <span class="fw-bold d-block text-body font-monospace" style="font-size:0.82rem;">{{ $m->created_at->format('d/m/Y') }}</span>
                                        <small class="text-muted font-monospace" style="font-size:0.73rem;"><i class="bi bi-clock me-1"></i>{{ $m->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold d-block text-body" style="font-size:0.84rem;">{{ $m->barang ? $m->barang->nama_barang : '-' }}</span>
                                        <span class="badge font-monospace px-2 py-0.5 mt-0.5 rounded-pill" style="font-size:0.70rem; background-color: var(--bg-light); color: var(--text-muted); border: 1px solid var(--border-color);">
                                            {{ $m->barang ? $m->barang->kode_barang : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-body d-block" style="font-size:0.82rem;">{{ $m->gudang ? $m->gudang->nama_gudang : '-' }}</span>
                                        @if($m->gudang && $m->gudang->cabang)
                                            <small class="text-muted d-block" style="font-size:0.72rem;"><i class="bi bi-geo-alt text-primary me-1"></i>{{ $m->gudang->cabang->nama_cabang }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($m->tipe === 'Masuk')
                                            <span class="badge-masuk"><i class="bi bi-arrow-down-left-circle-fill me-1"></i>Masuk</span>
                                        @else
                                            <span class="badge-keluar"><i class="bi bi-arrow-up-right-circle-fill me-1"></i>Keluar</span>
                                        @endif
                                    </td>
                                    @php
                                        $rStokAwal = round($m->stok_awal, 2);
                                        $rowStokAwal = (floor($rStokAwal) == $rStokAwal) ? number_format($rStokAwal, 0, ',', '.') : rtrim(rtrim(number_format($rStokAwal, 2, ',', '.'), '0'), ',');
                                        $rJumlah = round($m->jumlah, 2);
                                        $rowJumlah = (floor($rJumlah) == $rJumlah) ? number_format($rJumlah, 0, ',', '.') : rtrim(rtrim(number_format($rJumlah, 2, ',', '.'), '0'), ',');
                                        $rStokAkhir = round($m->stok_akhir, 2);
                                        $rowStokAkhir = (floor($rStokAkhir) == $rStokAkhir) ? number_format($rStokAkhir, 0, ',', '.') : rtrim(rtrim(number_format($rStokAkhir, 2, ',', '.'), '0'), ',');
                                    @endphp
                                    <td class="text-center font-monospace text-muted">{{ $rowStokAwal }}</td>
                                    <td class="text-end fw-semibold font-monospace text-nowrap text-body">
                                        @if($m->harga_satuan > 0)
                                            Rp {{ number_format($m->harga_satuan, 2, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold font-monospace text-nowrap {{ $m->tipe === 'Masuk' ? 'text-success' : 'text-danger' }}">
                                        {{ $m->tipe === 'Masuk' ? '+' : '-' }}{{ $rowJumlah }}
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-nowrap {{ $m->tipe === 'Masuk' ? 'text-success' : 'text-danger' }}">
                                        @if($m->nilai_total > 0)
                                            Rp {{ number_format($m->nilai_total, 2, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold font-monospace text-body">{{ $rowStokAkhir }}</td>
                                    <td>
                                        @php
                                            $refJenis = $m->referensi_jenis ?? 'Mutasi';
                                            $badgeClass = 'badge-ref-default';
                                            $icon = 'bi-file-earmark-text';
                                            $refLower = strtolower($refJenis);
                                            $clickAction = '';
                                            $linkUrl = '';

                                            if (str_contains($refLower, 'adjustment') || str_contains($refLower, 'penyesuaian')) {
                                                $badgeClass = 'badge-ref-adj';
                                                $icon = 'bi-sliders';
                                                if ($m->referensi_id) {
                                                    $clickAction = "showDetailAdjustment({$m->referensi_id})";
                                                } else {
                                                    $linkUrl = route('stock-adjustment.index');
                                                }
                                            } elseif (str_contains($refLower, 'inter-pt')) {
                                                $badgeClass = 'badge-ref-inter-pt';
                                                $icon = 'bi-buildings';
                                                $linkUrl = route('transfer.index');
                                            } elseif (str_contains($refLower, 'inter-cabang')) {
                                                $badgeClass = 'badge-ref-inter-cabang';
                                                $icon = 'bi-diagram-3';
                                                $linkUrl = route('transfer.index');
                                            } elseif (str_contains($refLower, 'internal') || str_contains($refLower, 'transfer')) {
                                                $badgeClass = 'badge-ref-internal';
                                                $icon = 'bi-arrow-left-right';
                                                $linkUrl = route('transfer.index');
                                            } elseif (str_contains($refLower, 'issue')) {
                                                $badgeClass = 'badge-ref-gi';
                                                $icon = 'bi-box-arrow-up';
                                                $linkUrl = route('gi.index');
                                            } elseif (str_contains($refLower, 'receipt')) {
                                                $badgeClass = 'badge-ref-gr';
                                                $icon = 'bi-box-arrow-in-down';
                                                $linkUrl = route('gr.index');
                                            } elseif (str_contains($refLower, 'opname')) {
                                                $badgeClass = 'badge-ref-opname';
                                                $icon = 'bi-clipboard-check';
                                                $linkUrl = route('opname.index');
                                            } elseif (str_contains($refLower, 'saldo awal')) {
                                                $badgeClass = 'badge-ref-default';
                                                $icon = 'bi-database-fill-check';
                                                $linkUrl = route('opening-stock.index');
                                            }
                                        @endphp

                                        <div>
                                            @if($clickAction)
                                                <a href="javascript:void(0)" onclick="{{ $clickAction }}" class="badge {{ $badgeClass }} rounded-pill small px-2.5 py-1 text-nowrap text-decoration-none shadow-xs d-inline-flex align-items-center gap-1 cursor-pointer ref-badge-hover" title="Klik untuk lihat detail transaksi">
                                                    <i class="bi {{ $icon }}"></i>{{ $refJenis }} <i class="bi bi-box-arrow-up-right opacity-75" style="font-size: 0.62rem;"></i>
                                                </a>
                                            @elseif($linkUrl)
                                                <a href="{{ $linkUrl }}" target="_blank" class="badge {{ $badgeClass }} rounded-pill small px-2.5 py-1 text-nowrap text-decoration-none shadow-xs d-inline-flex align-items-center gap-1 cursor-pointer ref-badge-hover" title="Buka modul {{ $refJenis }}">
                                                    <i class="bi {{ $icon }}"></i>{{ $refJenis }} <i class="bi bi-box-arrow-up-right opacity-75" style="font-size: 0.62rem;"></i>
                                                </a>
                                            @else
                                                <span class="badge {{ $badgeClass }} rounded-pill small px-2.5 py-1 text-nowrap" style="font-size:0.72rem">
                                                    <i class="bi {{ $icon }} me-1"></i>{{ $refJenis }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($m->referensi_nomor)
                                            @if($clickAction)
                                                <a href="javascript:void(0)" onclick="{{ $clickAction }}" class="text-primary fw-bold font-monospace d-inline-flex align-items-center gap-1 text-nowrap mt-0.5 text-decoration-none ref-link-hover" style="font-size:0.78rem" title="Klik untuk melihat dokumen {{ $m->referensi_nomor }}">
                                                    <span>{{ $m->referensi_nomor }}</span>
                                                    <i class="bi bi-eye-fill text-muted opacity-75" style="font-size:0.7rem;"></i>
                                                </a>
                                            @elseif($linkUrl)
                                                <a href="{{ $linkUrl }}" target="_blank" class="text-primary fw-bold font-monospace d-inline-flex align-items-center gap-1 text-nowrap mt-0.5 text-decoration-none ref-link-hover" style="font-size:0.78rem" title="Buka modul {{ $m->referensi_nomor }}">
                                                    <span>{{ $m->referensi_nomor }}</span>
                                                    <i class="bi bi-box-arrow-up-right text-muted opacity-75" style="font-size:0.62rem;"></i>
                                                </a>
                                            @else
                                                <span class="text-primary fw-bold font-monospace d-block text-nowrap mt-0.5" style="font-size:0.78rem">{{ $m->referensi_nomor }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <span class="d-block text-muted" style="font-size:0.78rem; line-height: 1.35;">{{ $m->keterangan ?? '-' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end text-muted small fw-bold">TOTAL MUTASI PERIODE:</th>
                                    @php
                                        $rFootMasuk = round($totalMasuk, 2);
                                        $footTotalMasuk = (floor($rFootMasuk) == $rFootMasuk) ? number_format($rFootMasuk, 0, ',', '.') : rtrim(rtrim(number_format($rFootMasuk, 2, ',', '.'), '0'), ',');
                                        $rFootKeluar = round($totalKeluar, 2);
                                        $footTotalKeluar = (floor($rFootKeluar) == $rFootKeluar) ? number_format($rFootKeluar, 0, ',', '.') : rtrim(rtrim(number_format($rFootKeluar, 2, ',', '.'), '0'), ',');
                                    @endphp
                                    <th class="text-center font-monospace">
                                        <span class="text-success fw-bold">+{{ $footTotalMasuk }}</span>
                                        <span class="text-muted mx-0.5">/</span>
                                        <span class="text-danger fw-bold">-{{ $footTotalKeluar }}</span>
                                    </th>
                                    <th></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── MODAL FILTER & PRINT ─────────────────────────────────────────── --}}
    <div class="modal fade" id="modalFilter" tabindex="-1" aria-labelledby="modalFilterLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom px-4 py-3 bg-light bg-opacity-50">
                    <h5 class="modal-title fw-bold text-body d-flex align-items-center gap-2" id="modalFilterLabel">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Filter & Cetak Kartu Stok</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="GET" action="{{ route('stock-card.index') }}" id="filterForm">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold d-flex justify-content-between align-items-center mb-1.5">
                                    <span><i class="bi bi-clock-history me-1 text-primary"></i>Pilihan Cepat Rentang Waktu:</span>
                                </label>
                                <div class="d-flex flex-wrap gap-1.5">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" onclick="setFilterPeriode('bulan_ini')">Bulan Ini</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" onclick="setFilterPeriode('bulan_lalu')">Bulan Lalu (Agustus)</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" onclick="setFilterPeriode('3_bulan')">3 Bulan Terakhir</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" onclick="setFilterPeriode('tahun_ini')">Tahun Ini</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" onclick="setFilterPeriode('semua')">Semua Waktu</button>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-calendar-event me-1"></i>Dari Tanggal</label>
                                <input type="date" id="input_start_date" name="start_date" class="form-control rounded-pill px-3" value="{{ $startDate }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-calendar-check me-1"></i>Sampai Tanggal</label>
                                <input type="date" id="input_end_date" name="end_date" class="form-control rounded-pill px-3" value="{{ $endDate }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-arrow-left-right me-1"></i>Jenis Mutasi / Transaksi</label>
                                <select name="tipe_mutasi" class="form-select select2-modal">
                                    <option value="">-- Semua Jenis Mutasi --</option>
                                    <option value="masuk" {{ ($tipeMutasi ?? '') == 'masuk' ? 'selected' : '' }}>📥 Barang Masuk (Penerimaan)</option>
                                    <option value="keluar" {{ ($tipeMutasi ?? '') == 'keluar' ? 'selected' : '' }}>📤 Barang Keluar (Pengeluaran)</option>
                                    <option value="transfer" {{ ($tipeMutasi ?? '') == 'transfer' ? 'selected' : '' }}>🔄 Semua Transfer Gudang</option>
                                    <option value="transfer_inter_pt" {{ ($tipeMutasi ?? '') == 'transfer_inter_pt' ? 'selected' : '' }}>🏢 Transfer Inter-PT</option>
                                    <option value="transfer_inter_cabang" {{ ($tipeMutasi ?? '') == 'transfer_inter_cabang' ? 'selected' : '' }}>🌐 Transfer Inter-Cabang</option>
                                    <option value="opname" {{ ($tipeMutasi ?? '') == 'opname' ? 'selected' : '' }}>📋 Stok Opname</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-box-seam me-1"></i>Nama Barang</label>
                                <select name="barang_id" class="form-select select2-modal">
                                    <option value="">-- Semua Barang --</option>
                                    @foreach($barangs as $b)
                                        <option value="{{ $b->id }}" {{ $barangId == $b->id ? 'selected' : '' }}>[{{ $b->kode_barang }}] {{ $b->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small fw-bold"><i class="bi bi-building me-1"></i>Gudang</label>
                                <select name="gudang_id" class="form-select select2-modal">
                                    <option value="">-- Semua Gudang --</option>
                                    @foreach($gudangs as $g)
                                        <option value="{{ $g->id }}" {{ $gudangId == $g->id ? 'selected' : '' }}>{{ $g->nama_gudang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <a href="{{ route('stock-card.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                        </a>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" onclick="printPdfFromModal()" class="btn btn-outline-danger rounded-pill px-3.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Cetak PDF
                            </button>
                            <button type="button" onclick="exportExcelFromModal()" class="btn btn-outline-success rounded-pill px-3.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Excel
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-search"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- MODAL DETAIL & FOTO PREVIEW FOR TRANSACTION DRILL-DOWN --}}
    @include('modul_inventori.stok_ajusmen._modal_detail')
    @include('modul_inventori.stok_ajusmen._modal_preview_foto')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Init Select2 inside Bootstrap Modal
            $('#modalFilter').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalFilter'),
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Pilih --'
                });
            });

            $('.select2-modal').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalFilter'),
                width: '100%',
                allowClear: true,
                placeholder: '-- Pilih --'
            });

            // Initialize DataTable on the mutasi table
            @if($mutasi->count())
            var dt = $('#tableKartuStok').DataTable({
                dom: '<"row align-items-center px-3 py-3"<"col-12 col-md-6"l><"col-12 col-md-6"f>>rt<"dt-bottom-toolbar d-flex flex-column flex-md-row align-items-center justify-content-between px-3 py-3 gap-3"<"dt-info-wrapper"i><"dt-paginate-wrapper"p>>',
                order: [],          // preserve chronological ledger order
                ordering: false,    // disable manual column sorting so ledger running balance remains physically coherent
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                columnDefs: [
                    { targets: 0, searchable: false, orderable: false }
                ],
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 data',
                    search: 'Cari:',
                    paginate: { 
                        previous: '<i class="bi bi-chevron-left" style="font-size:0.75rem;"></i>', 
                        next: '<i class="bi bi-chevron-right" style="font-size:0.75rem;"></i>' 
                    },
                    emptyTable: 'Tidak ada data mutasi stok.'
                }
            });

            // Recalculate row numbers correctly on every page draw
            dt.on('draw', function () {
                var info = dt.page.info();
                dt.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            }).draw(false);
            @endif
        });

        function printPdfFromModal() {
            let formData = $('#filterForm').serialize();
            let printUrl = "{{ route('stock-card.print-pdf') }}?" + formData;
            window.open(printUrl, '_blank');
        }

        function exportExcelFromModal() {
            let formData = $('#filterForm').serialize();
            let exportUrl = "{{ route('stock-card.export-excel') }}?" + formData;
            window.location.href = exportUrl;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Ekspor Excel!',
                    text: 'File Laporan Kartu Stok (.xlsx) sedang di-download berdasarkan filter pilihan.',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#10b981',
                    timer: 3500,
                    timerProgressBar: true
                });
            } else {
                alert('Berhasil Ekspor Excel! File sedang di-download.');
            }
        }

        function exportToExcel() {
            exportExcelFromModal();
        }

        // Quick Period Preset Setter
        function setFilterPeriode(preset) {
            const today = new Date();
            const y = today.getFullYear();
            const m = today.getMonth(); // 0-indexed (0=Jan, 8=Sept)

            function pad(n) { return n < 10 ? '0' + n : n; }
            function fmt(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

            let start, end;
            if (preset === 'bulan_ini') {
                start = new Date(y, m, 1);
                end = today;
            } else if (preset === 'bulan_lalu') {
                start = new Date(y, m - 1, 1);
                end = new Date(y, m, 0); // last day of prev month
            } else if (preset === '3_bulan') {
                start = new Date(y, m - 2, 1);
                end = today;
            } else if (preset === 'tahun_ini') {
                start = new Date(y, 0, 1);
                end = today;
            } else if (preset === 'semua') {
                start = new Date(2020, 0, 1);
                end = today;
            }

            if (start && end) {
                $('#input_start_date').val(fmt(start));
                $('#input_end_date').val(fmt(end));
            }
        }
    </script>
    @endpush
</x-layouts>
