<x-layouts title="Dashboard Inventori & Gudang - {{ $appName ?? 'SatriaERP' }}">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('dashboard/purchasing_dashboard.css') }}">
        <style>
            /* Additional Mesh Gradients & Welcome Card Pulse */
            .premium-welcome-card {
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.06) 0%, rgba(99, 102, 241, 0.02) 100%) !important;
                border: 1px solid rgba(37, 99, 235, 0.12) !important;
                border-radius: 20px !important;
                position: relative;
                overflow: hidden;
            }
            .premium-welcome-card::after {
                content: '';
                position: absolute;
                width: 250px;
                height: 250px;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 75%);
                top: -80px;
                right: -40px;
                pointer-events: none;
            }
            [data-bs-theme="dark"] .premium-welcome-card {
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.12) 0%, rgba(99, 102, 241, 0.04) 100%) !important;
                border: 1px solid rgba(37, 99, 235, 0.22) !important;
            }
            
            @keyframes pulse-primary {
                0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); }
                70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
                100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
            }
            .animate-pulse-primary {
                animation: pulse-primary 2.5s infinite ease-in-out;
            }

            .badge-habis-solid {
                background-color: #ef4444 !important;
                color: #ffffff !important;
                font-weight: 700;
                padding: 0.3rem 0.65rem;
                border-radius: 9999px;
            }
            .badge-kritis-solid {
                background-color: #f59e0b !important;
                color: #ffffff !important;
                font-weight: 700;
                padding: 0.3rem 0.65rem;
                border-radius: 9999px;
            }

            .pch-subtext-high {
                color: #64748b;
            }
            [data-bs-theme="dark"] .pch-subtext-high {
                color: #cbd5e1 !important;
            }
            [data-bs-theme="dark"] .pch-subtext {
                color: #94a3b8 !important;
            }

            /* ── Responsive Horizontal Scroll for Mobile Tables (Panjang ke Samping) ── */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            .table-responsive::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .table-responsive::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.04);
                border-radius: 4px;
            }
            [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.04);
            }
            .table-responsive::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.45);
                border-radius: 4px;
            }
            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: rgba(148, 163, 184, 0.7);
            }

            .cinematic-table th,
            .cinematic-table td {
                white-space: nowrap !important;
            }
            .cinematic-table td .text-truncate {
                display: inline-block;
                vertical-align: middle;
            }
        </style>
    @endpush

    <!-- Breadcrumb & Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 pch-heading">
                Dashboard Inventori & Stok Gudang
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none pch-subtext">{{ $appName ?? 'SatriaERP' }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="d-inline-flex flex-wrap gap-1.5 p-1 rounded-pill bg-body-tertiary border shadow-2xs me-md-2">
                @if($hasAdminPermission ?? false)
                    <a href="{{ route('dashboard', ['role' => 'admin']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? '') === 'admin' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
                        <i class="bi bi-speedometer me-1"></i> Admin
                    </a>
                @endif
                @if($hasPurchasingPermission ?? false)
                    <a href="{{ route('dashboard', ['role' => 'purchasing']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? '') === 'purchasing' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
                        <i class="bi bi-cart3 me-1"></i> Purchasing
                    </a>
                @endif
                <a href="{{ route('dashboard', ['role' => 'inventori']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold btn-primary shadow-xs" style="font-size: 0.8rem;">
                    <i class="bi bi-box-seam me-1"></i> Inventori
                </a>
                @if($hasProductionPermission ?? false)
                    <a href="{{ route('produksi.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? '') === 'produksi' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
                        <i class="bi bi-tools me-1"></i> Produksi
                    </a>
                @endif
                @if($hasSalesPermission ?? false)
                    <a href="{{ route('sales.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                        <i class="bi bi-graph-up-arrow me-1"></i> Sales
                    </a>
                @endif
                @if($hasFinancePermission ?? false)
                    <a href="{{ route('finance.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                        <i class="bi bi-wallet2 me-1"></i> Keuangan
                    </a>
                @endif
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-2xs fw-semibold d-inline-flex align-items-center gap-1.5" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh Data</span>
            </button>
            <a href="{{ route('stock-overview.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-2xs fw-bold d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-box-seam"></i>
                <span>Lihat Stok Terkini</span>
            </a>
        </div>
    </div>

    <!-- Welcome Alert -->
    <div class="card border-0 shadow-sm premium-welcome-card d-flex flex-row align-items-center mb-4 p-4 position-relative overflow-hidden">
        <div class="me-4 d-none d-md-block" style="z-index: 2;">
            <div class="bg-primary bg-opacity-10 rounded-4 p-3 d-flex align-items-center justify-content-center animate-pulse-primary" style="width: 60px; height: 60px;">
                <i class="bi bi-rocket-takeoff text-primary fs-3"></i>
            </div>
        </div>
        <div style="z-index: 2;">
            <h5 class="fw-bold mb-1 text-primary">Selamat datang kembali, {{ $userFullName ?? (auth()->user()->nama_lengkap ?? 'User') }}! 👋</h5>
            <p class="mb-0 pch-subtext-high fs-7 fw-medium">{!! $summaryText ?? 'Selamat datang di modul Inventori & Persediaan Gudang.' !!}</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. CINEMATIC 3-KPI STAT CARDS GRID (OPERASIONAL GUDANG)                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">

        {{-- KPI 1: Total Stok Fisik --}}
        <div class="col">
            <div class="cinematic-kpi-card kpi-blue">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="kpi-title-label">Total Stok Fisik</div>
                        <div class="kpi-main-metric">
                            {{ number_format(($totalStokFisik ?? 0) >= 1000 ? ($totalStokFisik ?? 0) / 1000 : ($totalStokFisik ?? 0), ($totalStokFisik ?? 0) >= 1000 ? 1 : 0, ',', '.') }} {{ ($totalStokFisik ?? 0) >= 1000 ? 'Rb' : '' }} <span class="fs-7 pch-subtext fw-semibold">Unit</span>
                        </div>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="pill-badge pill-badge-blue">
                        <i class="bi bi-check2-circle"></i> Siap Pakai
                    </span>
                    <small class="pch-subtext-high font-monospace fs-9" title="Kuantitas Riil">{{ number_format($totalStokFisik ?? 0, 0, ',', '.') }} Pcs</small>
                </div>
            </div>
        </div>

        {{-- KPI 2: Total Varian SKU --}}
        <div class="col">
            <div class="cinematic-kpi-card kpi-amber">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="kpi-title-label">Total Varian SKU</div>
                        <div class="kpi-main-metric">
                            {{ number_format($totalSku ?? 0, 0, ',', '.') }} <span class="fs-7 pch-subtext fw-semibold">Item</span>
                        </div>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="pill-badge pill-badge-amber">
                        <i class="bi bi-grid-fill"></i> Katalog Master
                    </span>
                    <small class="pch-subtext-high font-monospace fs-9">Item Terdaftar</small>
                </div>
            </div>
        </div>

        {{-- KPI 3: Defisit Stok Kritis --}}
        <div class="col">
            <div class="cinematic-kpi-card kpi-rose">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="kpi-title-label">Defisit Stok Kritis</div>
                        <div class="kpi-main-metric {{ ($totalLowStock ?? 0) > 0 ? 'text-danger' : '' }}">
                            {{ number_format($totalLowStock ?? 0, 0, ',', '.') }} <span class="fs-7 pch-subtext fw-semibold">Stok</span>
                        </div>
                    </div>
                    <div class="kpi-icon-bubble">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    @if(($totalLowStock ?? 0) > 0)
                        <span class="pill-badge pill-badge-rose">
                            <i class="bi bi-bell-fill"></i> Reorder Segera!
                        </span>
                    @else
                        <span class="pill-badge pill-badge-emerald">
                            <i class="bi bi-shield-check"></i> Stok Aman
                        </span>
                    @endif
                    <small class="pch-subtext-high font-monospace fs-9">Reorder Point</small>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. CINEMATIC CHARTS ROW --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        
        {{-- Chart 1: Tren Mutasi Barang Masuk vs Keluar --}}
        <div class="col-xl-8">
            <div class="cinematic-box h-100">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up-arrow text-primary"></i> Tren Mutasi Stok Masuk vs Keluar (Bulanan)
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">Perbandingan volume barang masuk gudang vs barang keluar dari kartu stok real-time</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="pill-badge pill-badge-blue">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Barang Masuk
                        </span>
                        <span class="pill-badge pill-badge-rose">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i> Barang Keluar
                        </span>
                    </div>
                </div>
                <div style="height: 310px; position: relative;">
                    <div id="chart-mutasi-inventori" style="min-height: 310px;"></div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Komposisi Kategori Barang --}}
        <div class="col-xl-4">
            <div class="cinematic-box h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-pie-chart-fill text-info"></i> Komposisi Kategori
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">Distribusi nilai aset berdasarkan master kategori</p>
                    </div>
                </div>
                <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                    <div id="chart-kategori-inventori" style="width: 100%;"></div>
                </div>
                <div class="mt-3 pt-2 border-top border-secondary border-opacity-10">
                    @php
                        $colorPalette = ['#0284c7', '#10b981', '#f59e0b', '#06b6d4', '#6366f1'];
                    @endphp
                    @foreach($kategoriLabels ?? [] as $index => $label)
                        <div class="d-flex justify-content-between align-items-center fs-8 py-1">
                            <span class="pch-body-text d-flex align-items-center">
                                <i class="bi bi-circle-fill me-2" style="font-size: 8px; color: {{ $colorPalette[$index % count($colorPalette)] }};"></i>
                                <span class="text-truncate" style="max-width: 180px;">{{ $label }}</span>
                            </span>
                            <span class="fw-bold pch-heading font-monospace">{{ $kategoriData[$index] ?? 0 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. CINEMATIC TABLES ROW --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        
        {{-- Table 1: Distribusi Stok per Gudang --}}
        <div class="col-12 col-xl-7">
            <div class="cinematic-box p-0 h-100 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                            <i class="bi bi-buildings text-primary"></i> Distribusi Stok per Fasilitas Gudang
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">Matriks persebaran item pada setiap fasilitas penyimpanan aktif</p>
                    </div>
                    <a href="{{ route('gudang.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fs-8 fw-bold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                        <span>Kelola Gudang</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
                    <table class="table cinematic-table align-middle mb-0" style="min-width: 600px;">
                        <thead>
                            <tr>
                                <th class="ps-4">Gudang & Lokasi</th>
                                <th>Tipe Fasilitas</th>
                                <th class="text-center">Total Item SKU</th>
                                <th class="text-end pe-4">Nilai Persediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distribusiGudang ?? [] as $gudang)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold pch-heading">[{{ $gudang->kode_gudang }}] {{ $gudang->nama_gudang }}</div>
                                        <small class="pch-subtext-high">{{ $gudang->alamat ?? 'Lokasi Penyimpanan' }}</small>
                                    </td>
                                    <td>
                                        <span class="pill-badge pill-badge-cyan">Gudang Aktif</span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold pch-heading">{{ number_format($gudang->total_item ?? 0, 0, ',', '.') }} Item</td>
                                    <td class="text-end pe-4 font-monospace fw-bold text-success">Rp {{ number_format($gudang->total_nilai ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 pch-subtext">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Belum ada data distribusi stok gudang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Table 2: Peringatan Stok Kritis (Safety Stock - 10 Items Default) --}}
        <div class="col-12 col-xl-5">
            <div class="cinematic-box p-0 h-100 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 text-danger fs-6 fw-bold">
                            <i class="bi bi-exclamation-octagon-fill"></i> Peringatan Reorder Point (Kritis)
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">10 item teratas yang stoknya mendekati atau habis</p>
                    </div>
                    <a href="{{ route('pr.index') }}" class="btn btn-sm btn-danger rounded-pill px-3 py-1.5 fs-8 fw-bold text-nowrap flex-shrink-0 shadow-2xs d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-plus-circle"></i>
                        <span>Buat PR</span>
                    </a>
                </div>
                <div class="table-responsive" style="max-height: 560px; overflow-y: auto; overflow-x: auto;">
                    <table class="table cinematic-table align-middle mb-0" style="min-width: 560px;">
                        <thead>
                            <tr>
                                <th class="ps-4">Barang</th>
                                <th class="text-center">Stok Saat Ini</th>
                                <th class="text-center">Min. Buffer</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($criticalStockList ?? [] as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold pch-heading text-truncate" style="max-width: 170px;" title="{{ $item->nama_barang }}">
                                            {{ $item->nama_barang }}
                                        </div>
                                        <div class="pch-subtext-high font-monospace fs-9">{{ $item->kode_barang }}</div>
                                    </td>
                                    <td class="text-center font-monospace">
                                        @if(($item->stok_aktual ?? 0) <= 0)
                                            <span class="fw-bold text-danger">{{ number_format($item->stok_aktual ?? 0, 0, ',', '.') }}</span>
                                        @else
                                            <span class="fw-bold text-warning">{{ number_format($item->stok_aktual ?? 0, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="pch-subtext-high fs-9 d-block">{{ $item->satuan ?? 'Pcs' }}</span>
                                    </td>
                                    <td class="text-center font-monospace pch-heading fw-semibold">
                                        {{ number_format($item->minimum_stok ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        @if(($item->stok_aktual ?? 0) <= 0)
                                            <span class="badge-habis-solid">Habis</span>
                                        @else
                                            <span class="badge-kritis-solid">Kritis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 pch-subtext">
                                        <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                                        Seluruh stok barang dalam batas aman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 4. RADAR AUDIT & AKURASI FISIK GUDANG (STOCK OPNAME INTELLIGENCE)          --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        {{-- Radar Card: Ringkasan Akurasi Fisik --}}
        <div class="col-12 col-xl-4">
            <div class="cinematic-box p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: var(--pch-card-border) !important;">
                        <h5 class="pch-heading mb-0 d-flex align-items-center gap-2 fs-6 fw-bold">
                            <i class="bi bi-clipboard2-check-fill text-primary"></i> Radar Stock Opname
                        </h5>
                        @if(($opnameStats['activeOpnameCount'] ?? 0) > 0)
                            <span class="pill-badge pill-badge-amber">
                                <i class="bi bi-hourglass-split"></i> {{ $opnameStats['activeOpnameCount'] }} Berjalan
                            </span>
                        @else
                            <span class="pill-badge pill-badge-cyan">
                                <i class="bi bi-check-all"></i> Terkendali
                            </span>
                        @endif
                    </div>

                    @if($latestOpname)
                        <!-- Metric Akurasi Fisik -->
                        <div class="p-3 rounded-4 mb-3" style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.15);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fs-8 fw-semibold pch-subtext">Tingkat Akurasi Fisik</span>
                                @if(($opnameStats['accuracyRatio'] ?? 100) >= 90)
                                    <span class="pill-badge pill-badge-emerald">Akurasi Tinggi</span>
                                @elseif(($opnameStats['accuracyRatio'] ?? 100) >= 75)
                                    <span class="pill-badge pill-badge-amber">Akurasi Sedang</span>
                                @else
                                    <span class="pill-badge pill-badge-rose">Perlu Audit</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="fw-bold pch-heading font-monospace mb-0">{{ number_format($opnameStats['accuracyRatio'] ?? 100, 1, ',', '.') }}%</h3>
                                <small class="pch-subtext fs-9">({{ $opnameStats['matchingItems'] ?? 0 }}/{{ $opnameStats['totalItemOpname'] ?? 0 }} SKU Cocok)</small>
                            </div>
                            <div class="progress" style="height: 6px; background: rgba(148, 163, 184, 0.2);">
                                <div class="progress-bar {{ ($opnameStats['accuracyRatio'] ?? 100) >= 90 ? 'bg-success' : (($opnameStats['accuracyRatio'] ?? 100) >= 75 ? 'bg-warning' : 'bg-danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $opnameStats['accuracyRatio'] ?? 100 }}%"></div>
                            </div>
                        </div>

                        <!-- Info Opname Terakhir -->
                        <div class="small pch-subtext mb-3">
                            <div class="d-flex justify-content-between py-1 border-bottom border-opacity-10" style="border-color: var(--pch-card-border) !important;">
                                <span>No. Opname</span>
                                <span class="font-monospace fw-bold pch-heading">{{ $latestOpname->nomor_opname }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-opacity-10" style="border-color: var(--pch-card-border) !important;">
                                <span>Fasilitas Gudang</span>
                                <span class="fw-semibold pch-heading">{{ $latestOpname->gudang->nama_gudang ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-opacity-10" style="border-color: var(--pch-card-border) !important;">
                                <span>Tanggal Audit</span>
                                <span class="font-monospace">{{ $latestOpname->tanggal_opname ? $latestOpname->tanggal_opname->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span>Status Terakhir</span>
                                <span class="pill-badge pill-badge-cyan">
                                    {{ $latestOpname->status }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 pch-subtext">
                            <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-muted"></i>
                            <p class="mb-0 fs-8">Belum ada riwayat stock opname pada entitas ini.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-top mt-2" style="border-color: var(--pch-card-border) !important;">
                    <a href="{{ route('opname.index') }}" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold fs-8 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Buka Rekap Stock Opname</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Discrepancy Terbesar --}}
        <div class="col-12 col-xl-8">
            <div class="cinematic-box p-0 h-100 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                            <i class="bi bi-arrow-left-right text-warning"></i> Selisih Stok Audit Fisik Terbesar
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">10 item dengan deviasi terbesar antara stok buku sistem vs perhitungan fisik pada opname terakhir</p>
                    </div>
                    @if($latestOpname)
                        <div class="d-flex flex-wrap gap-2 mt-1 mt-sm-0 flex-shrink-0">
                            <span class="pill-badge pill-badge-rose">
                                <i class="bi bi-dash-circle me-1"></i>{{ $opnameStats['selisihKurang'] ?? 0 }} Kurang
                            </span>
                            <span class="pill-badge pill-badge-emerald">
                                <i class="bi bi-plus-circle me-1"></i>{{ $opnameStats['selisihLebih'] ?? 0 }} Lebih
                            </span>
                        </div>
                    @endif
                </div>
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto; overflow-x: auto;">
                    <table class="table cinematic-table align-middle mb-0" style="min-width: 680px;">
                        <thead>
                            <tr>
                                <th class="ps-4">Item Barang</th>
                                <th class="text-center">Stok Sistem</th>
                                <th class="text-center">Stok Fisik</th>
                                <th class="text-center">Selisih</th>
                                <th class="text-end pe-4">Status Deviasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topDiscrepancies ?? [] as $disc)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold pch-heading text-truncate" style="max-width: 200px;" title="{{ $disc->barang->nama_barang ?? '-' }}">
                                            {{ $disc->barang->nama_barang ?? '-' }}
                                        </div>
                                        <div class="pch-subtext-high font-monospace fs-9">{{ $disc->barang->kode_barang ?? '-' }}</div>
                                    </td>
                                    <td class="text-center font-monospace fw-semibold pch-heading">
                                        {{ number_format($disc->stok_sistem ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold pch-heading">
                                        {{ number_format($disc->stok_fisik ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-monospace fw-bold">
                                        @if(($disc->selisih ?? 0) < 0)
                                            <span class="text-danger">{{ number_format($disc->selisih ?? 0, 0, ',', '.') }}</span>
                                        @elseif(($disc->selisih ?? 0) > 0)
                                            <span class="text-success">+{{ number_format($disc->selisih ?? 0, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if(($disc->selisih ?? 0) < 0)
                                            <span class="pill-badge pill-badge-rose">
                                                Selisih Kurang
                                            </span>
                                        @elseif(($disc->selisih ?? 0) > 0)
                                            <span class="pill-badge pill-badge-emerald">
                                                Selisih Lebih
                                            </span>
                                        @else
                                            <span class="pill-badge pill-badge-cyan">
                                                Sesuai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 pch-subtext">
                                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                        Tidak ditemukan selisih fisik pada stock opname terakhir atau belum ada audit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    {{-- 5. LIVE LOGISTICS FEED: INBOUND (MASUK) & OUTBOUND (KELUAR) TERKINI        --}}
    {{-- ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        
        {{-- Feed Inbound (Penerimaan Barang Masuk) --}}
        <div class="col-12 col-xl-6">
            <div class="cinematic-box p-0 h-100 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                            <i class="bi bi-box-arrow-in-down text-success"></i> Penerimaan Masuk (Inbound Terkini)
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">Surat Jalan / Goods Receipt terbaru dari supplier ke gudang</p>
                    </div>
                    <a href="{{ route('gr.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fs-8 fw-bold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                        <span>Semua GR</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive" style="max-height: 420px; overflow-y: auto; overflow-x: auto;">
                    <table class="table cinematic-table align-middle mb-0" style="min-width: 620px;">
                        <thead>
                            <tr>
                                <th class="ps-4">No. Penerimaan &amp; PO</th>
                                <th>Supplier &amp; Gudang</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInbound ?? [] as $gr)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold font-monospace pch-heading">{{ $gr->nomor_gr }}</div>
                                        <small class="pch-subtext-high font-monospace fs-9">PO: {{ $gr->po->nomor_po ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold pch-heading text-truncate" style="max-width: 170px;" title="{{ $gr->po->supplier->nama_supplier ?? '-' }}">
                                            {{ $gr->po->supplier->nama_supplier ?? '-' }}
                                        </div>
                                        <small class="pch-subtext-high font-monospace fs-9"><i class="bi bi-geo-alt me-0.5 text-primary"></i>{{ $gr->gudang->nama_gudang ?? '-' }}</small>
                                    </td>
                                    <td class="text-center font-monospace fs-9 pch-subtext-high">
                                        {{ $gr->tanggal_gr ? $gr->tanggal_gr->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($gr->status === 'Posted')
                                            <span class="pill-badge pill-badge-emerald"><i class="bi bi-check2-circle me-1"></i>Posted</span>
                                        @elseif($gr->status === 'Draft')
                                            <span class="pill-badge pill-badge-slate"><i class="bi bi-pencil me-1"></i>Draft</span>
                                        @elseif($gr->status === 'Dibatalkan')
                                            <span class="pill-badge pill-badge-rose"><i class="bi bi-x-circle me-1"></i>Batal</span>
                                        @else
                                            <span class="pill-badge pill-badge-blue">{{ $gr->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 pch-subtext">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Belum ada penerimaan barang masuk terkini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Feed Outbound (Pengeluaran Barang Keluar) --}}
        <div class="col-12 col-xl-6">
            <div class="cinematic-box p-0 h-100 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                    <div>
                        <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                            <i class="bi bi-box-arrow-up-right text-primary"></i> Pengeluaran &amp; Kirim (Outbound Terkini)
                        </h5>
                        <p class="pch-subtext mb-0 fs-8">Surat Jalan / Delivery Order pengiriman barang ke customer</p>
                    </div>
                    <a href="{{ route('marketing.delivery-order.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fs-8 fw-bold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                        <span>Semua DO</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="table-responsive" style="max-height: 420px; overflow-y: auto; overflow-x: auto;">
                    <table class="table cinematic-table align-middle mb-0" style="min-width: 640px;">
                        <thead>
                            <tr>
                                <th class="ps-4">No. DO / Surat Jalan</th>
                                <th>Customer &amp; Gudang Asal</th>
                                <th class="text-center">Tgl Kirim</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOutbound ?? [] as $do)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold font-monospace pch-heading">{{ $do->nomor_do }}</div>
                                        <small class="pch-subtext-high fs-9"><i class="bi bi-truck me-0.5 text-info"></i>{{ $do->nama_ekspedisi ?? ($do->metode_pengiriman ?? 'Kurir Internal') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold pch-heading text-truncate" style="max-width: 170px;" title="{{ $do->customer->nama_customer ?? '-' }}">
                                            {{ $do->customer->nama_customer ?? '-' }}
                                        </div>
                                        <small class="pch-subtext-high font-monospace fs-9"><i class="bi bi-buildings me-0.5 text-primary"></i>{{ $do->gudang->nama_gudang ?? '-' }}</small>
                                    </td>
                                    <td class="text-center font-monospace fs-9 pch-subtext-high">
                                        {{ $do->tanggal_kirim ? $do->tanggal_kirim->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        @if(in_array($do->status, ['Terkirim', 'Selesai']))
                                            <span class="pill-badge pill-badge-emerald"><i class="bi bi-check2-all me-1"></i>{{ $do->status }}</span>
                                        @elseif($do->status === 'Disetujui')
                                            <span class="pill-badge pill-badge-cyan"><i class="bi bi-check-lg me-1"></i>Disetujui</span>
                                        @elseif($do->status === 'Menunggu Persetujuan')
                                            <span class="pill-badge pill-badge-amber"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                        @elseif($do->status === 'Dibatalkan')
                                            <span class="pill-badge pill-badge-rose"><i class="bi bi-x-circle me-1"></i>Batal</span>
                                        @else
                                            <span class="pill-badge pill-badge-slate">{{ $do->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 pch-subtext">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Belum ada pengeluaran barang terkini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.body.getAttribute('data-bs-theme') === 'dark';
            const axisLabelColor = isDark ? '#cbd5e1' : '#64748b';
            const gridBorderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : '#e2e8f0';
            const totalTextColor = isDark ? '#f8fafc' : '#1e293b';

            // Chart Mutasi Bulanan
            const mutasiOptions = {
                series: [{
                    name: 'Barang Masuk',
                    data: {!! json_encode($chartMutasiMasuk ?? array_fill(0, 12, 0)) !!}
                }, {
                    name: 'Barang Keluar',
                    data: {!! json_encode($chartMutasiKeluar ?? array_fill(0, 12, 0)) !!}
                }],
                chart: {
                    type: 'area',
                    height: 310,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                colors: ['#0284c7', '#ef4444'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: isDark ? 0.45 : 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    labels: { style: { colors: axisLabelColor, fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: axisLabelColor, fontSize: '11px' } }
                },
                grid: {
                    borderColor: gridBorderColor,
                    strokeDashArray: 4
                },
                legend: { show: false },
                tooltip: {
                    theme: isDark ? 'dark' : 'light'
                }
            };
            const mutasiChart = new ApexCharts(document.querySelector("#chart-mutasi-inventori"), mutasiOptions);
            mutasiChart.render();

            // Chart Komposisi Kategori
            const kategoriOptions = {
                series: {!! json_encode($kategoriData ?? [45, 35, 20]) !!},
                labels: {!! json_encode($kategoriLabels ?? ['Bahan Baku', 'Barang Jadi', 'Kemas & Penolong']) !!},
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                stroke: {
                    show: true,
                    colors: [isDark ? '#1e293b' : '#ffffff'],
                    width: 2
                },
                colors: ['#0284c7', '#10b981', '#f59e0b', '#06b6d4', '#6366f1'],
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function (val) {
                            return val + '%';
                        }
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Aset',
                                    fontSize: '12px',
                                    color: axisLabelColor,
                                    formatter: function () {
                                        return '100%';
                                    }
                                },
                                value: {
                                    color: totalTextColor,
                                    fontSize: '16px',
                                    fontWeight: 700
                                }
                            }
                        }
                    }
                }
            };
            const kategoriChart = new ApexCharts(document.querySelector("#chart-kategori-inventori"), kategoriOptions);
            kategoriChart.render();
        });
    </script>
    @endpush

</x-layouts>
