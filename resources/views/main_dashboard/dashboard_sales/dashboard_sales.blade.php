<x-layouts title="Dashboard Sales & Marketing - {{ $appName ?? 'ERP Pastac' }}">

    @push('styles')
        <style>
            /* ══════════════════════════════════════════════════════════════════════════
               DASHBOARD SALES: MODERN CINEMATIC & HIGH-CONTRAST DESIGN TOKENS
               ══════════════════════════════════════════════════════════════════════════ */
            :root {
                --sls-bg-card: #ffffff;
                --sls-text-heading: #0f172a;
                --sls-text-sub: #475569;
                --sls-border-card: rgba(226, 232, 240, 0.9);
                --sls-glass-card: rgba(255, 255, 255, 0.95);
                --sls-th-bg: #f8fafc;
                --sls-tr-hover: rgba(241, 245, 249, 0.65);
            }

            [data-bs-theme="dark"] {
                --sls-bg-card: #141c2e;
                --sls-text-heading: #f8fafc;
                --sls-text-sub: #94a3b8;
                --sls-border-card: rgba(255, 255, 255, 0.08);
                --sls-glass-card: rgba(20, 28, 46, 0.95);
                --sls-th-bg: #0f172a;
                --sls-tr-hover: rgba(255, 255, 255, 0.035);
            }

            /* Container & Cinematic Boxes */
            .cinematic-box {
                background: var(--sls-glass-card);
                border: 1px solid var(--sls-border-card);
                border-radius: 1.25rem;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
                backdrop-filter: blur(12px);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }
            .cinematic-box:hover {
                box-shadow: 0 10px 28px -4px rgba(15, 23, 42, 0.09);
            }

            /* High-Contrast Typo Helpers */
            .sls-heading {
                color: var(--sls-text-heading) !important;
            }
            .sls-subtext {
                color: var(--sls-text-sub) !important;
            }

            /* ── MODERN FUTURISTIC KPI CARDS ── */
            .kpi-card-modern {
                position: relative;
                border-radius: 1.25rem;
                padding: 1.5rem;
                overflow: hidden;
                transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .kpi-card-modern:hover {
                transform: translateY(-3px);
            }

            /* 1. Indigo / Blue Theme (Revenue) */
            .kpi-card-indigo {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.07) 0%, rgba(99, 102, 241, 0.02) 100%);
                border: 1px solid rgba(79, 70, 229, 0.18);
            }
            [data-bs-theme="dark"] .kpi-card-indigo {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.16) 0%, rgba(99, 102, 241, 0.05) 100%);
                border-color: rgba(99, 102, 241, 0.3);
            }

            /* 2. Emerald Theme (Order Inflow) */
            .kpi-card-emerald {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.07) 0%, rgba(5, 150, 105, 0.02) 100%);
                border: 1px solid rgba(16, 185, 129, 0.18);
            }
            [data-bs-theme="dark"] .kpi-card-emerald {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, rgba(5, 150, 105, 0.05) 100%);
                border-color: rgba(16, 185, 129, 0.3);
            }

            /* 3. Amber / Orange Theme (AR / Piutang) */
            .kpi-card-amber {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(217, 119, 6, 0.02) 100%);
                border: 1px solid rgba(245, 158, 11, 0.2);
            }
            [data-bs-theme="dark"] .kpi-card-amber {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(217, 119, 6, 0.05) 100%);
                border-color: rgba(245, 158, 11, 0.3);
            }

            /* 4. Purple Theme (Pipeline / Prospek) */
            .kpi-card-purple {
                background: linear-gradient(135deg, rgba(168, 85, 247, 0.08) 0%, rgba(147, 51, 234, 0.02) 100%);
                border: 1px solid rgba(168, 85, 247, 0.2);
            }
            [data-bs-theme="dark"] .kpi-card-purple {
                background: linear-gradient(135deg, rgba(168, 85, 247, 0.16) 0%, rgba(147, 51, 234, 0.05) 100%);
                border-color: rgba(168, 85, 247, 0.3);
            }

            /* Decorative Background Watermarks */
            .kpi-watermark-icon {
                position: absolute;
                right: -10px;
                bottom: -15px;
                font-size: 6.5rem;
                line-height: 1;
                opacity: 0.06;
                pointer-events: none;
                transition: transform 0.35s ease, opacity 0.35s ease;
            }
            .kpi-card-modern:hover .kpi-watermark-icon {
                transform: scale(1.08) rotate(-4deg);
                opacity: 0.1;
            }

            /* Icon Pill Container */
            .kpi-icon-pill {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
            }
            .kpi-icon-indigo { background: rgba(79, 70, 229, 0.12); color: #4f46e5; }
            .kpi-icon-emerald { background: rgba(16, 185, 129, 0.12); color: #10b981; }
            .kpi-icon-amber { background: rgba(245, 158, 11, 0.14); color: #d97706; }
            .kpi-icon-purple { background: rgba(168, 85, 247, 0.14); color: #9333ea; }

            /* Pill Badges */
            .sls-pill-badge {
                display: inline-flex;
                align-items: center;
                font-size: 0.72rem;
                font-weight: 600;
                padding: 0.22rem 0.65rem;
                border-radius: 9999px;
            }
            .sls-pill-indigo  { background: rgba(79, 70, 229, 0.12); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.2); }
            .sls-pill-emerald { background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); }
            .sls-pill-amber   { background: rgba(245, 158, 11, 0.14); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); }
            .sls-pill-rose    { background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.25); }
            .sls-pill-purple  { background: rgba(168, 85, 247, 0.14); color: #9333ea; border: 1px solid rgba(168, 85, 247, 0.25); }
            .sls-pill-slate   { background: rgba(148, 163, 184, 0.14); color: #64748b; border: 1px solid rgba(148, 163, 184, 0.25); }
            [data-bs-theme="dark"] .sls-pill-slate { color: #cbd5e1; }

            /* Header Tab Switcher Bar */
            .sls-tab-wrapper {
                background: rgba(148, 163, 184, 0.1);
                border: 1px solid var(--sls-border-card);
            }

            /* Cinematic Tables */
            .sls-table thead th {
                background: var(--sls-th-bg);
                color: var(--sls-text-sub);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
                padding: 0.85rem 1rem;
                border-bottom: 1px solid var(--sls-border-card);
            }
            .sls-table tbody tr {
                border-bottom: 1px solid var(--sls-border-card);
                transition: background 0.15s ease;
            }
            .sls-table tbody tr:hover {
                background: var(--sls-tr-hover);
            }
            .sls-table tbody td {
                padding: 0.85rem 1rem;
                font-size: 0.84rem;
                vertical-align: middle;
            }

            /* Rank Badge for Top Customers/Products */
            .rank-badge {
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.72rem;
                font-weight: 800;
            }
            .rank-gold   { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
            .rank-silver { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
            .rank-bronze { background: linear-gradient(135deg, #b45309, #78350f); color: #fff; }
            .rank-normal { background: rgba(148, 163, 184, 0.2); color: var(--sls-text-sub); }
        </style>
    @endpush

    <div class="content">
        <div class="container-fluid px-2 px-md-3 py-3">

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 1. EXECUTIVE HEADER & ROLE DASHBOARD SWITCHER TABS                         --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="cinematic-box p-3 p-md-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-lg-8">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            {{-- Tenant Entity Badge --}}
                            @if($isBranchSession ?? false)
                                <span class="sls-pill-badge sls-pill-indigo">
                                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $entityName ?? 'Kantor Cabang' }}
                                </span>
                            @else
                                <span class="sls-pill-badge sls-pill-slate">
                                    <i class="bi bi-building-fill me-1"></i> {{ $entityName ?? 'PT Pusat' }}
                                </span>
                            @endif

                            <span class="sls-pill-badge sls-pill-slate">
                                <i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('d M Y') }}
                            </span>

                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-9 fw-semibold px-2.5 py-1">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> Penjualan Aktif
                            </span>
                        </div>

                        <h3 class="fw-bold sls-heading mb-1" style="letter-spacing: -0.5px;">
                            Selamat Datang, {{ $userFullName }}!
                        </h3>
                        <p class="sls-subtext mb-0 fs-7">
                            {!! $summaryText !!}
                        </p>
                    </div>

                    {{-- Role Dashboard Switcher Tabs --}}
                    <div class="col-12 col-lg-4 text-lg-end">
                        <div class="d-inline-flex flex-wrap gap-1.5 p-1 rounded-pill sls-tab-wrapper shadow-2xs">
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
                            @if($hasInventoryPermission ?? false)
                                <a href="{{ route('dashboard', ['role' => 'inventori']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? '') === 'inventori' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
                                    <i class="bi bi-box-seam me-1"></i> Inventori
                                </a>
                            @endif
                            @if($hasProductionPermission ?? false)
                                <a href="{{ route('produksi.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? '') === 'produksi' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
                                    <i class="bi bi-tools me-1"></i> Produksi
                                </a>
                            @endif
                            <a href="{{ route('sales.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold btn-primary shadow-xs" style="font-size: 0.8rem;">
                                <i class="bi bi-graph-up-arrow me-1"></i> Sales
                            </a>
                            @if($hasFinancePermission ?? false)
                                <a href="{{ route('finance.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-wallet2 me-1"></i> Keuangan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 2. 3 TOP EXECUTIVE KPI CARDS (MODERN & FUTURISTIC)                         --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                
                {{-- KPI 1: Omset Penjualan Bulan Ini --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-indigo h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #4f46e5; letter-spacing: 0.06em;">Omset Penjualan</span>
                                    <div class="sls-subtext fs-9 mt-0.5">Realisasi pesanan bulan ini</div>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-indigo shadow-2xs">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                            </div>
                            <div class="mb-2">
                                <h3 class="fw-bolder sls-heading font-monospace mb-0" style="font-size: 1.65rem; letter-spacing: -0.5px;">
                                    Rp {{ number_format($revenueThisMonth ?? 0, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 mt-auto border-top border-opacity-10" style="border-color: rgba(79, 70, 229, 0.2) !important;">
                            <span class="sls-subtext fs-8 fw-semibold">vs Bulan Lalu</span>
                            <span class="sls-pill-badge {{ ($revenueGrowthPct ?? 0) >= 0 ? 'sls-pill-emerald' : 'sls-pill-rose' }}">
                                <i class="bi {{ ($revenueGrowthPct ?? 0) >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }} me-1"></i>
                                {{ ($revenueGrowthPct ?? 0) >= 0 ? '+' : '' }}{{ number_format($revenueGrowthPct ?? 0, 1, ',', '.') }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Pesanan Masuk (Order Inflow) --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-emerald h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #059669; letter-spacing: 0.06em;">Pesanan Masuk</span>
                                    <div class="sls-subtext fs-9 mt-0.5">Sales Order (SO) terbit</div>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-emerald shadow-2xs">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="fw-bolder sls-heading font-monospace mb-0" style="font-size: 1.65rem; letter-spacing: -0.5px;">
                                    {{ number_format($totalSoMonth ?? 0, 0, ',', '.') }}
                                </h3>
                                <span class="sls-subtext fs-8 font-monospace">SO Bulan Ini</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 mt-auto border-top border-opacity-10" style="border-color: rgba(16, 185, 129, 0.2) !important;">
                            <span class="sls-subtext fs-8 fw-semibold">{{ number_format($totalItemSoldMonth ?? 0, 0, ',', '.') }} item terjual</span>
                            <span class="sls-pill-badge sls-pill-emerald">
                                <i class="bi bi-play-circle-fill me-1"></i>{{ $activeSoCount ?? 0 }} Diproses
                            </span>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Piutang Usaha Belum Tertagih --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-amber h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #d97706; letter-spacing: 0.06em;">Sisa Piutang</span>
                                    <div class="sls-subtext fs-9 mt-0.5">Tagihan faktur beredar</div>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-amber shadow-2xs">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                            </div>
                            <div class="mb-2">
                                <h3 class="fw-bolder {{ ($countArOverdue ?? 0) > 0 ? 'text-warning' : 'sls-heading' }} font-monospace mb-0" style="font-size: 1.65rem; letter-spacing: -0.5px;">
                                    Rp {{ number_format($totalOutstandingAr ?? 0, 0, ',', '.') }}
                                </h3>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 mt-auto border-top border-opacity-10" style="border-color: rgba(245, 158, 11, 0.2) !important;">
                            <span class="sls-subtext fs-8 fw-semibold">Jatuh Tempo</span>
                            @if(($countArOverdue ?? 0) > 0)
                                <span class="sls-pill-badge sls-pill-rose font-monospace" title="Faktur lewat tanggal jatuh tempo">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i>{{ $countArOverdue }} Over
                                </span>
                            @else
                                <span class="sls-pill-badge sls-pill-emerald">
                                    <i class="bi bi-check-circle-fill me-1"></i>Lancar
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 3. INTERACTIVE VISUAL ANALYTICS (TREND & STATUS)                           --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Chart 1: Tren Omset Penjualan vs Penerimaan Kas (6 Bulan) --}}
                <div class="col-12 col-xl-8">
                    <div class="cinematic-box p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-3 pb-2 border-bottom gap-2" style="border-color: var(--sls-border-card) !important;">
                            <div>
                                <h5 class="sls-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-graph-up text-primary"></i> Tren Penjualan &amp; Arus Kas Masuk
                                </h5>
                                <p class="sls-subtext mb-0 fs-8">Perbandingan nilai Sales Order vs Realisasi Pembayaran Kas 6 bulan terakhir</p>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="sls-pill-badge sls-pill-indigo"><i class="bi bi-circle-fill me-1"></i>Omset Order</span>
                                <span class="sls-pill-badge sls-pill-emerald"><i class="bi bi-circle-fill me-1"></i>Kas Diterima</span>
                            </div>
                        </div>
                        <div style="height: 290px; position: relative;">
                            <canvas id="chartTrendSales"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Chart 2: Status Order --}}
                <div class="col-12 col-xl-4">
                    <div class="cinematic-box p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: var(--sls-border-card) !important;">
                            <h5 class="sls-heading mb-0 d-flex align-items-center gap-2 fs-6 fw-bold">
                                <i class="bi bi-pie-chart-fill text-warning"></i> Status Order
                            </h5>
                            <span class="sls-pill-badge sls-pill-slate">Distribusi</span>
                        </div>
                        <div style="height: 270px; position: relative;" class="d-flex align-items-center justify-content-center my-auto">
                            <canvas id="chartStatusSales"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 4. ACTIONABLE CINEMATIC TABLES: ROW 1 (PESANAN & PIUTANG JATUH TEMPO)       --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Table 1: Pesanan Menunggu Pengiriman --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="sls-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-truck text-primary"></i> Pesanan Siap / Dalam Pengiriman
                                </h5>
                                <p class="sls-subtext mb-0 fs-8">Order penjualan aktif yang perlu dipenuhi logistik</p>
                            </div>
                            <a href="{{ route('marketing.sales-order.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fs-8 fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                                <span>Lihat Semua SO</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table sls-table align-middle mb-0" style="min-width: 580px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No. SO &amp; Tanggal</th>
                                        <th>Pelanggan</th>
                                        <th class="text-end">Nilai Total</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unfulfilledOrders ?? [] as $so)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold font-monospace sls-heading">{{ $so->nomor_so }}</div>
                                                <small class="sls-subtext font-monospace fs-9">
                                                    {{ $so->tanggal_so ? \Carbon\Carbon::parse($so->tanggal_so)->format('d/m/Y') : '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold sls-heading text-truncate" style="max-width: 170px;" title="{{ $so->customer->nama_customer ?? '-' }}">
                                                    {{ $so->customer->nama_customer ?? '-' }}
                                                </div>
                                                <small class="sls-subtext font-monospace fs-9">{{ $so->customer->kode_customer ?? '-' }}</small>
                                            </td>
                                            <td class="text-end font-monospace fw-bold sls-heading">
                                                Rp {{ number_format($so->grand_total ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($so->status === 'Confirmed' || $so->status === 'Dikonfirmasi')
                                                    <span class="sls-pill-badge sls-pill-indigo"><i class="bi bi-patch-check-fill me-1"></i>Confirmed</span>
                                                @elseif($so->status === 'Processing' || $so->status === 'Diproses' || $so->status === 'Approved')
                                                    <span class="sls-pill-badge sls-pill-cyan"><i class="bi bi-gear-wide-connected me-1"></i>Processing</span>
                                                @elseif($so->status === 'Delivered' || $so->status === 'Terkirim')
                                                    <span class="sls-pill-badge sls-pill-emerald"><i class="bi bi-truck me-1"></i>Delivered</span>
                                                @elseif($so->status === 'Completed' || $so->status === 'Selesai')
                                                    <span class="sls-pill-badge sls-pill-emerald"><i class="bi bi-check2-all me-1"></i>Selesai</span>
                                                @elseif($so->status === 'Parsial')
                                                    <span class="sls-pill-badge sls-pill-amber"><i class="bi bi-pie-chart-fill me-1"></i>Parsial</span>
                                                @else
                                                    <span class="sls-pill-badge sls-pill-slate">{{ $so->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 sls-subtext">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                Tidak ada antrian pesanan yang tertunda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 2: Piutang Jatuh Tempo / Mendekati Deadline --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="sls-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Monitoring Piutang Pelanggan
                                </h5>
                                <p class="sls-subtext mb-0 fs-8">Faktur belum lunas  melewati batas tempo</p>
                            </div>
                           
                        </div>
                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table sls-table align-middle mb-0" style="min-width: 580px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No. Faktur</th>
                                        <th>Pelanggan</th>
                                        <th class="text-center">Jatuh Tempo</th>
                                        <th class="text-end">Sisa Tagihan</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dueInvoices ?? [] as $inv)
                                        @php
                                            $isOverdue = $inv->tanggal_jatuh_tempo && \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->isPast();
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold font-monospace sls-heading">{{ $inv->nomor_faktur }}</div>
                                                <small class="sls-subtext font-monospace fs-9">
                                                    {{ $inv->tanggal_faktur ? \Carbon\Carbon::parse($inv->tanggal_faktur)->format('d/m/Y') : '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold sls-heading text-truncate" style="max-width: 160px;" title="{{ $inv->customer->nama_customer ?? '-' }}">
                                                    {{ $inv->customer->nama_customer ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-center font-monospace fs-9 {{ $isOverdue ? 'text-danger fw-bold' : 'sls-subtext' }}">
                                                {{ $inv->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="text-end font-monospace fw-bold {{ $isOverdue ? 'text-danger' : 'text-warning' }}">
                                                Rp {{ number_format($inv->sisa_tagihan ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($isOverdue)
                                                    <span class="sls-pill-badge sls-pill-rose"><i class="bi bi-hourglass-bottom me-1"></i>Over</span>
                                                @else
                                                    <span class="sls-pill-badge sls-pill-amber"><i class="bi bi-clock me-1"></i>Belum Lunas</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 sls-subtext">
                                                <i class="bi bi-check2-circle fs-3 d-block mb-1 text-success"></i>
                                                Seluruh tagihan piutang dalam batas aman.
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
            {{-- 5. ACTIONABLE CINEMATIC TABLES: ROW 2 (TOP PELANGGAN & PRODUK TERLARIS)     --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Table 3: Top 5 Pelanggan Terbesar --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="sls-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-star-fill text-warning"></i> Top 5 Pelanggan Terbesar
                                </h5>
                                <p class="sls-subtext mb-0 fs-8">Kemitraan dengan kontribusi nilai transaksi tertinggi</p>
                            </div>
                            <span class="sls-pill-badge sls-pill-indigo">Pareto 80/20</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table sls-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 text-center" style="width: 50px;">#</th>
                                        <th>Nama Pelanggan</th>
                                        <th class="text-center">Total Order</th>
                                        <th class="text-end pe-4">Total Nilai Kontribusi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCustomers ?? [] as $idx => $c)
                                        <tr>
                                            <td class="ps-4 text-center">
                                                <span class="rank-badge {{ $idx === 0 ? 'rank-gold' : ($idx === 1 ? 'rank-silver' : ($idx === 2 ? 'rank-bronze' : 'rank-normal')) }}">
                                                    {{ $idx + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold sls-heading">{{ $c->nama_customer }}</div>
                                                <small class="sls-subtext font-monospace fs-9">{{ $c->kode_customer ?: '-' }}</small>
                                            </td>
                                            <td class="text-center font-monospace fs-8 sls-heading">
                                                {{ number_format($c->total_transaksi ?? 0, 0, ',', '.') }} Transaksi
                                            </td>
                                            <td class="text-end pe-4 font-monospace fw-bold text-success fs-8">
                                                Rp {{ number_format($c->total_kontribusi ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 sls-subtext">
                                                Belum ada data transaksi pelanggan bulan ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 4: Top 5 Produk / SKU Terlaris --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="sls-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-fire text-danger"></i> Top 5 Produk Terlaris (Best Seller)
                                </h5>
                                <p class="sls-subtext mb-0 fs-8">SKU dengan volume penjualan tertinggi periode berjalan</p>
                            </div>
                            <span class="sls-pill-badge sls-pill-emerald">Volume Qty</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table sls-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 text-center" style="width: 50px;">#</th>
                                        <th>Nama Produk</th>
                                        <th class="text-center">Qty Terjual</th>
                                        <th class="text-end pe-4">Nilai Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts ?? [] as $idx => $p)
                                        <tr>
                                            <td class="ps-4 text-center">
                                                <span class="rank-badge {{ $idx === 0 ? 'rank-gold' : ($idx === 1 ? 'rank-silver' : ($idx === 2 ? 'rank-bronze' : 'rank-normal')) }}">
                                                    {{ $idx + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold sls-heading text-truncate" style="max-width: 220px;" title="{{ $p->item_name }}">
                                                    {{ $p->item_name }}
                                                </div>
                                                <small class="sls-subtext font-monospace fs-9">SKU: {{ $p->item_code }}</small>
                                            </td>
                                            <td class="text-center font-monospace fw-bold sls-heading fs-8">
                                                {{ number_format($p->total_qty ?? 0, 0, ',', '.') }} {{ $p->nama_satuan ?: 'Pcs' }}
                                            </td>
                                            <td class="text-end pe-4 font-monospace fw-bold text-primary fs-8">
                                                Rp {{ number_format($p->total_penjualan ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 sls-subtext">
                                                Belum ada data barang terjual pada periode ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isDarkMode = () => document.documentElement.getAttribute('data-bs-theme') === 'dark';
                
                const getTextColor = () => isDarkMode() ? '#94a3b8' : '#64748b';
                const getGridColor = () => isDarkMode() ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';

                // ── 1. Chart Tren Omset Penjualan vs Penerimaan Kas ──
                const trendCtx = document.getElementById('chartTrendSales');
                let trendChart = null;

                if (trendCtx) {
                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($chartMonths ?? []) !!},
                            datasets: [
                                {
                                    label: 'Omset Order (SO)',
                                    data: {!! json_encode($chartRevenue ?? []) !!},
                                    borderColor: '#4f46e5',
                                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#4f46e5',
                                },
                                {
                                    label: 'Kas Masuk (Bayar)',
                                    data: {!! json_encode($chartReceipts ?? []) !!},
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#10b981',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDarkMode() ? '#1e293b' : '#0f172a',
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function (context) {
                                            return ' ' + context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: getTextColor(), font: { size: 11 } }
                                },
                                y: {
                                    grid: { color: getGridColor() },
                                    ticks: {
                                        color: getTextColor(),
                                        font: { size: 11 },
                                        callback: function (val) {
                                            if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' M';
                                            if (val >= 1000000) return (val / 1000000).toFixed(0) + ' Jt';
                                            return val;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // ── 2. Chart Donut Status Sales Order ──
                const statusCtx = document.getElementById('chartStatusSales');
                let statusChart = null;

                if (statusCtx) {
                    const statusData = {!! json_encode(array_values($statusChart ?? [])) !!};
                    const statusLabels = {!! json_encode(array_keys($statusChart ?? [])) !!};

                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusData,
                                backgroundColor: [
                                    '#10b981', // Selesai
                                    '#4f46e5', // Diproses
                                    '#f59e0b', // Pending
                                    '#94a3b8', // Draft
                                    '#ef4444'  // Batal
                                ],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: getTextColor(),
                                        font: { size: 11 },
                                        padding: 10,
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }

                // ── Dynamic Theme Switch Listener ──
                const themeObserver = new MutationObserver(function () {
                    const textColor = getTextColor();
                    const gridColor = getGridColor();

                    if (trendChart) {
                        trendChart.options.scales.x.ticks.color = textColor;
                        trendChart.options.scales.y.ticks.color = textColor;
                        trendChart.options.scales.y.grid.color = gridColor;
                        trendChart.update();
                    }
                    if (statusChart) {
                        statusChart.options.plugins.legend.labels.color = textColor;
                        statusChart.update();
                    }
                });

                themeObserver.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['data-bs-theme']
                });
            });
        </script>
    @endpush

</x-layouts>
