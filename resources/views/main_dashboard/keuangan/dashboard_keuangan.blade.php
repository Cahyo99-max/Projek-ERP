<x-layouts title="Dashboard Keuangan & Akuntansi - {{ $appName ?? 'ERP Pastac' }}">

    @push('styles')
        <style>
            /* ══════════════════════════════════════════════════════════════════════════
               DASHBOARD KEUANGAN: MODERN CINEMATIC & HIGH-CONTRAST DESIGN TOKENS
               ══════════════════════════════════════════════════════════════════════════ */
            :root {
                --fin-bg-card: #ffffff;
                --fin-text-heading: #0f172a;
                --fin-text-sub: #475569;
                --fin-border-card: rgba(226, 232, 240, 0.9);
                --fin-glass-card: rgba(255, 255, 255, 0.96);
                --fin-th-bg: #f8fafc;
                --fin-tr-hover: rgba(241, 245, 249, 0.85);
            }

            [data-bs-theme="dark"] {
                --fin-bg-card: #141c2e;
                --fin-text-heading: #f8fafc;
                --fin-text-sub: #94a3b8;
                --fin-border-card: rgba(255, 255, 255, 0.08);
                --fin-glass-card: rgba(20, 28, 46, 0.96);
                --fin-th-bg: #0f172a;
                --fin-tr-hover: rgba(255, 255, 255, 0.045);
            }

            /* Container & Cinematic Boxes with generous spacing */
            .cinematic-box {
                background: var(--fin-glass-card);
                border: 1px solid var(--fin-border-card);
                border-radius: 1.25rem;
                box-shadow: 0 4px 24px -2px rgba(15, 23, 42, 0.05);
                backdrop-filter: blur(14px);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
                padding: 1.5rem;
            }
            @media (min-width: 992px) {
                .cinematic-box {
                    padding: 1.75rem;
                }
            }
            .cinematic-box:hover {
                box-shadow: 0 10px 32px -4px rgba(15, 23, 42, 0.09);
            }

            /* High-Contrast Typo Helpers */
            .fin-heading {
                color: var(--fin-text-heading) !important;
            }
            .fin-subtext {
                color: var(--fin-text-sub) !important;
            }
            .fin-num {
                font-variant-numeric: tabular-nums;
                letter-spacing: -0.01em;
            }

            /* ── MODERN FUTURISTIC KPI CARDS ── */
            .kpi-card-modern {
                position: relative;
                border-radius: 1.25rem;
                padding: 1.65rem 1.5rem;
                overflow: hidden;
                transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .kpi-card-modern:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12);
            }

            /* 1. Indigo Theme (Total Kas & Bank) */
            .kpi-card-indigo {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.09) 0%, rgba(99, 102, 241, 0.02) 100%);
                border: 1px solid rgba(79, 70, 229, 0.18);
            }
            [data-bs-theme="dark"] .kpi-card-indigo {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.18) 0%, rgba(99, 102, 241, 0.05) 100%);
                border-color: rgba(99, 102, 241, 0.32);
            }

            /* 2. Emerald Theme (Net Cash Flow) */
            .kpi-card-emerald {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.09) 0%, rgba(5, 150, 105, 0.02) 100%);
                border: 1px solid rgba(16, 185, 129, 0.18);
            }
            [data-bs-theme="dark"] .kpi-card-emerald {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(5, 150, 105, 0.05) 100%);
                border-color: rgba(16, 185, 129, 0.32);
            }

            /* 3. Amber Theme (AR & AP) */
            .kpi-card-amber {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.09) 0%, rgba(217, 119, 6, 0.02) 100%);
                border: 1px solid rgba(245, 158, 11, 0.22);
            }
            [data-bs-theme="dark"] .kpi-card-amber {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.18) 0%, rgba(217, 119, 6, 0.05) 100%);
                border-color: rgba(245, 158, 11, 0.32);
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
                opacity: 0.11;
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
            .kpi-icon-indigo  { background: rgba(79, 70, 229, 0.12); color: #4f46e5; }
            .kpi-icon-emerald { background: rgba(16, 185, 129, 0.12); color: #10b981; }
            .kpi-icon-amber   { background: rgba(245, 158, 11, 0.14); color: #d97706; }
            .kpi-icon-purple  { background: rgba(168, 85, 247, 0.14); color: #9333ea; }
            .kpi-icon-rose    { background: rgba(244, 63, 94, 0.14);  color: #e11d48; }

            /* Pill Badges */
            .fin-pill-badge {
                display: inline-flex;
                align-items: center;
                font-size: 0.74rem;
                font-weight: 600;
                padding: 0.28rem 0.75rem;
                border-radius: 9999px;
                letter-spacing: 0.01em;
            }
            .fin-pill-indigo  { background: rgba(79, 70, 229, 0.12); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.24); }
            .fin-pill-emerald { background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.24); }
            .fin-pill-amber   { background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.24); }
            .fin-pill-rose    { background: rgba(244, 63, 94, 0.12); color: #e11d48; border: 1px solid rgba(244, 63, 94, 0.24); }
            .fin-pill-slate   { background: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.2); }

            [data-bs-theme="dark"] .fin-pill-indigo  { color: #a5b4fc; }
            [data-bs-theme="dark"] .fin-pill-emerald { color: #6ee7b7; }
            [data-bs-theme="dark"] .fin-pill-amber   { color: #fcd34d; }
            [data-bs-theme="dark"] .fin-pill-rose    { color: #fda4af; }
            [data-bs-theme="dark"] .fin-pill-slate   { color: #94a3b8; }

            /* Sleek Monospace Badge for Transaction Numbers */
            .badge-tx-code {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                font-size: 0.77rem;
                font-weight: 700;
                padding: 0.25rem 0.6rem;
                border-radius: 6px;
                background: rgba(99, 102, 241, 0.08);
                color: var(--fin-text-heading);
                border: 1px solid rgba(99, 102, 241, 0.22);
                display: inline-block;
                letter-spacing: 0.025em;
                transition: all 0.18s ease;
            }
            [data-bs-theme="dark"] .badge-tx-code {
                background: rgba(99, 102, 241, 0.15);
                border-color: rgba(99, 102, 241, 0.32);
                color: #e0e7ff;
            }
            .badge-tx-code:hover {
                transform: translateY(-1px);
                border-color: rgba(99, 102, 241, 0.55);
            }

            /* Custom Sleek Scrollbar for Responsive Tables */
            .table-responsive::-webkit-scrollbar {
                height: 6px;
                width: 6px;
            }
            .table-responsive::-webkit-scrollbar-track {
                background: rgba(15, 23, 42, 0.04);
                border-radius: 9999px;
            }
            [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.03);
            }
            .table-responsive::-webkit-scrollbar-thumb {
                background: rgba(99, 102, 241, 0.25);
                border-radius: 9999px;
            }
            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: rgba(99, 102, 241, 0.5);
            }

            /* Premium Modern Table Styles */
            .table-modern {
                width: 100%;
                min-width: 580px;
                border-collapse: separate;
                border-spacing: 0;
            }
            .table-modern thead th {
                background: var(--fin-th-bg);
                color: var(--fin-text-sub);
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                padding: 0.95rem 1.15rem;
                border-bottom: 1px solid var(--fin-border-card);
                white-space: nowrap;
            }
            .table-modern tbody td {
                padding: 1rem 1.15rem;
                border-bottom: 1px solid var(--fin-border-card);
                font-size: 0.84rem;
                vertical-align: middle;
                line-height: 1.45;
            }
            .table-modern tbody tr {
                transition: background-color 0.18s ease;
            }
            .table-modern tbody tr:last-child td {
                border-bottom: none;
            }
            .table-modern tbody tr:hover td {
                background: var(--fin-tr-hover);
            }

            /* Donut Legend Styling */
            .donut-legend-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.5rem 0.8rem;
                border-radius: 0.7rem;
                transition: background 0.18s ease;
            }
            .donut-legend-item:hover {
                background: var(--fin-tr-hover);
            }
            .donut-color-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                flex-shrink: 0;
                box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
            }

            .fin-tab-wrapper {
                background: rgba(15, 23, 42, 0.04);
                border: 1px solid var(--fin-border-card);
            }
            [data-bs-theme="dark"] .fin-tab-wrapper {
                background: rgba(255, 255, 255, 0.03);
            }
        </style>
    @endpush

    <div class="content">
        <div class="container-fluid px-2 px-md-3 py-3">

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="cinematic-box p-3 p-md-4 mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-lg-7">
                        @php
                            $hour = (int) now()->format('H');
                            $greeting = match(true) {
                                $hour >= 4 && $hour < 11 => 'Selamat Pagi',
                                $hour >= 11 && $hour < 15 => 'Selamat Siang',
                                $hour >= 15 && $hour < 18 => 'Selamat Sore',
                                default => 'Selamat Malam',
                            };
                            $userName = $userFullName ?? (auth()->user()->nama_lengkap ?? (auth()->user()->name ?? 'User'));
                        @endphp

                        <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                            {{-- Tenant Entity Badge --}}
                            @if($isBranchSession ?? false)
                                <span class="fin-pill-badge fin-pill-indigo">
                                    <i class="bi bi-geo-alt-fill me-1"></i> {{ $entityName ?? 'Kantor Cabang' }}
                                </span>
                            @else
                                <span class="fin-pill-badge fin-pill-slate">
                                    <i class="bi bi-building-fill me-1"></i> {{ $entityName ?? 'PT PERSADA AMAN SENTOSA' }}
                                </span>
                            @endif

                            <span class="fin-pill-badge fin-pill-slate">
                                <i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('d F Y') }}
                            </span>

                            <span class="fin-pill-badge fin-pill-indigo">
                                <i class="bi bi-person-fill me-1"></i> {{ $userName }}
                            </span>
                        </div>

                        <h3 class="fw-bold fin-heading mb-1" style="letter-spacing: -0.5px;">
                            {{ $greeting }}, <span class="text-primary">{{ $userName }}</span>! 👋
                        </h3>
                        <p class="fin-subtext mb-0 fs-7 d-flex align-items-center gap-1.5 flex-wrap">
                          
                            <span class="d-none d-md-inline opacity-50">•</span>
                            <span class="opacity-75">Pantau arus kas, laba rugi, dan kesehatan finansial perusahaan secara real-time.</span>
                        </p>
                    </div>

                    {{-- Cross-Dashboard Switcher Tabs & Refresh --}}
                    <div class="col-12 col-lg-5 text-lg-end">
                        <div class="d-inline-flex flex-wrap gap-1.5 p-1 rounded-pill fin-tab-wrapper shadow-2xs align-items-center">
                            @if($hasAdminPermission ?? false)
                                <a href="{{ route('dashboard', ['role' => 'admin']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-speedometer me-1"></i> Admin
                                </a>
                            @endif
                            @if($hasPurchasingPermission ?? false)
                                <a href="{{ route('dashboard', ['role' => 'purchasing']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-cart3 me-1"></i> Purchasing
                                </a>
                            @endif
                            @if($hasInventoryPermission ?? false)
                                <a href="{{ route('dashboard', ['role' => 'inventori']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-box-seam me-1"></i> Inventori
                                </a>
                            @endif
                            @if($hasProductionPermission ?? false)
                                <a href="{{ route('produksi.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-tools me-1"></i> Produksi
                                </a>
                            @endif
                            @if($hasSalesPermission ?? false)
                                <a href="{{ route('sales.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Sales
                                </a>
                            @endif
                            <a href="{{ route('finance.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold btn-primary shadow-xs" style="font-size: 0.8rem;">
                                <i class="bi bi-wallet2 me-1"></i> Keuangan
                            </a>
                            <a href="{{ route('finance.dashboard', ['refresh' => 1]) }}" class="btn btn-sm btn-icon rounded-circle btn-outline-secondary ms-1" title="Perbarui Data Real-Time" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 2. 3 TOP EXECUTIVE KPI CARDS                                              --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 g-xl-4 mb-4 mb-xl-5">
                
                {{-- KPI 1: Total Saldo Kas & Bank --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-indigo h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-bank2"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #4f46e5; letter-spacing: 0.06em;">Kas &amp; Bank</span>
                                    <h6 class="fin-subtext mb-0 fs-7">Total Likuiditas Berjalan</h6>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-indigo">
                                    <i class="bi bi-wallet-fill"></i>
                                </div>
                            </div>
                            
                            <h2 class="fw-black fin-heading mb-1" style="letter-spacing: -0.8px;">
                                Rp {{ number_format($totalKasBankSaldo, 0, ',', '.') }}
                            </h2>

                            <div class="d-flex align-items-center gap-2 mt-2">
                                <span class="badge rounded-pill bg-indigo bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8 fw-semibold px-2 py-0.5">
                                    {{ $countKasBankActive }} Rekening Aktif
                                </span>
                                <span class="fs-8 fin-subtext">Terhubung Ledger</span>
                            </div>
                        </div>

                        <div class="pt-3 mt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <span class="fs-8 fin-subtext">Kas Kecil Bln Ini: <strong class="fin-heading">Rp {{ number_format($pettyCashExpenseMonth, 0, ',', '.') }}</strong></span>
                            <a href="{{ route('accounting.kas-bank.index') }}" class="fs-8 fw-bold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                Buku Kas <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Net Cash Flow Bulan Ini --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-emerald h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #059669; letter-spacing: 0.06em;">Arus Kas Masuk - Keluar</span>
                                    <h6 class="fin-subtext mb-0 fs-7">Net Cash Flow ({{ now()->translatedFormat('F Y') }})</h6>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-emerald">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>

                            <h2 class="fw-black fin-heading mb-1 {{ $netCashFlowMonth >= 0 ? 'text-success' : 'text-danger' }}" style="letter-spacing: -0.8px;">
                                {{ $netCashFlowMonth >= 0 ? '+' : '' }}Rp {{ number_format($netCashFlowMonth, 0, ',', '.') }}
                            </h2>

                            <div class="d-flex align-items-center gap-2 mt-2">
                                @if($netCashFlowGrowth >= 0)
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8 fw-semibold px-2 py-0.5">
                                        <i class="bi bi-arrow-up-right me-0.5"></i> +{{ $netCashFlowGrowth }}%
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8 fw-semibold px-2 py-0.5">
                                        <i class="bi bi-arrow-down-right me-0.5"></i> {{ $netCashFlowGrowth }}%
                                    </span>
                                @endif
                                <span class="fs-8 fin-subtext">vs Bulan Lalu</span>
                            </div>
                        </div>

                        <div class="pt-3 mt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center fs-8">
                            <span class="text-success"><i class="bi bi-arrow-down-left me-1"></i>In: <strong>Rp {{ number_format($kasMasukMonth, 0, ',', '.') }}</strong></span>
                            <span class="text-danger"><i class="bi bi-arrow-up-right me-1"></i>Out: <strong>Rp {{ number_format($kasKeluarMonth, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Tagihan Piutang (AR) vs Hutang (AP) --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-amber h-100 d-flex flex-column justify-content-between">
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #d97706; letter-spacing: 0.06em;">Piutang &amp; Hutang</span>
                                    <h6 class="fin-subtext mb-0 fs-7">Posisi AR &amp; AP Belum Lunas</h6>
                                </div>
                                <div class="kpi-icon-pill kpi-icon-amber">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>

                            <div class="d-flex align-items-baseline justify-content-between mb-1">
                                <span class="fs-8 fw-semibold fin-subtext">Piutang :</span>
                                <span class="fw-bold fin-heading fs-6 text-primary">Rp {{ number_format($totalOutstandingAr, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex align-items-baseline justify-content-between mb-2">
                                <span class="fs-8 fw-semibold fin-subtext">Hutang :</span>
                                <span class="fw-bold fin-heading fs-6 text-warning">Rp {{ number_format($totalOutstandingAp, 0, ',', '.') }}</span>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-2">
                                @if($countArOverdue > 0 || $countApOverdue > 0)
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8 fw-semibold px-2 py-0.5">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $countArOverdue + $countApOverdue }} Faktur Overdue
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8 fw-semibold px-2 py-0.5">
                                        <i class="bi bi-check-circle-fill me-1"></i> Tidak Ada Overdue
                                    </span>
                                @endif
                                <span class="fs-8 fin-subtext">Tenggat Lewat</span>
                            </div>
                        </div>

                        <div class="pt-3 mt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center fs-8">
                            <span class="text-danger">Overdue AR: <strong>Rp {{ number_format($totalArOverdue, 0, ',', '.') }}</strong></span>
                            <span class="text-danger">Overdue AP: <strong>Rp {{ number_format($totalApOverdue, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 3. 2 ANALYTICS CHARTS                                                     --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 g-xl-4 mb-4 mb-xl-5">
                
                {{-- Chart 1: Tren Arus Kas 6 Bulan Terakhir --}}
                <div class="col-12 col-xl-8">
                    <div class="cinematic-box p-3 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold fin-heading mb-0 fs-6">Tren Arus Kas (6 Bulan Terakhir)</h6>
                                <p class="fin-subtext fs-8 mb-0">Perbandingan pergerakan Kas Masuk vs Kas Keluar &amp; Net Arus Kas</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fin-pill-badge fin-pill-emerald fs-8"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Kas Masuk</span>
                                <span class="fin-pill-badge fin-pill-rose fs-8"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Kas Keluar</span>
                                <span class="fin-pill-badge fin-pill-indigo fs-8"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Net Cash</span>
                            </div>
                        </div>
                        <div style="position: relative; height: 320px; width: 100%;">
                            <canvas id="cashFlowTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Chart 2: Komposisi Distribusi Kas & Bank (Donut Chart) --}}
                <div class="col-12 col-xl-4">
                    <div class="cinematic-box p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold fin-heading mb-0 fs-6">Distribusi Kas &amp; Bank</h6>
                                    <p class="fin-subtext fs-8 mb-0">Komposisi saldo per rekening aktif</p>
                                </div>
                                <span class="fin-pill-badge fin-pill-indigo">{{ $countKasBankActive }} Rekening</span>
                            </div>

                            <div style="position: relative; height: 215px; width: 100%;" class="d-flex align-items-center justify-content-center">
                                <canvas id="kasBankDonutChart"></canvas>
                            </div>
                        </div>

                        {{-- Legend List with Color Indicators --}}
                        <div class="mt-3.5 pt-2.5 border-top border-secondary border-opacity-10">
                            <div class="d-flex flex-column gap-1" style="max-height: 125px; overflow-y: auto;">
                                @forelse($kasBankAccounts->take(4) as $idx => $acc)
                                    @php
                                        $dotColor = $donutColors[$idx] ?? '#6366f1';
                                    @endphp
                                    <div class="donut-legend-item">
                                        <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 65%;">
                                            <span class="donut-color-dot" style="background-color: {{ $dotColor }};"></span>
                                            <span class="fin-heading text-truncate fs-8 fw-medium">
                                                {{ $acc->nama_bank ?: $acc->nama_akun }}
                                                <span class="fin-subtext">({{ $acc->nomor_rekening ?: 'Kas' }})</span>
                                            </span>
                                        </div>
                                        <span class="fw-bold fin-heading fin-num fs-8">Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="text-center py-2 fin-subtext fs-8">Belum ada rekening kas/bank</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 4. ACTIONABLE DATA TABLES                                                 --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 g-xl-4 mb-4 mb-xl-5">

                {{-- Table 1: Posisi Rekening Kas & Bank --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-3 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold fin-heading mb-0 fs-6">Posisi Rekening Kas &amp; Bank</h6>
                                <p class="fin-subtext fs-8 mb-0">Daftar akun kas dan bank resmi terdaftar</p>
                            </div>
                            <a href="{{ route('accounting.kas-bank.index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                Kelola Rekening <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table-modern" style="min-width: 580px;">
                                <thead>
                                    <tr>
                                        <th>Nama Rekening / Bank</th>
                                        <th>No. Rekening</th>
                                        <th>Tipe</th>
                                        <th class="text-end">Saldo Berjalan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kasBankAccounts as $kb)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="kpi-icon-pill kpi-icon-indigo" style="width: 36px; height: 36px; font-size: 1rem;">
                                                        <i class="bi bi-{{ strtolower($kb->tipe) === 'bank' ? 'bank2' : 'cash-coin' }}"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold fin-heading d-block fs-8">{{ $kb->nama_akun }}</span>
                                                        <span class="fs-9 fin-subtext">{{ $kb->nama_bank ?: 'Kas Tunai' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-tx-code">{{ $kb->nomor_rekening ?: 'KAS' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-body fs-9 px-2.5 py-1 border border-secondary border-opacity-15">
                                                    {{ $kb->tipe }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold fin-heading fin-num fs-7">Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('accounting.kas-bank.ledger', $kb->id) }}" class="btn btn-xs btn-light rounded-pill px-2.5 py-1 text-primary border shadow-2xs" title="Buka Buku Kas" style="font-size: 0.74rem;">
                                                    <i class="bi bi-journal-text me-1"></i> Buku Kas
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 fin-subtext">Belum ada akun kas/bank pada entitas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 2: Monitoring Overdue (AR & AP Jatuh Tempo) --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-3 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold fin-heading mb-0 fs-6">Prioritas Tagihan Jatuh Tempo</h6>
                                <p class="fin-subtext fs-8 mb-0">Piutang customer &amp; hutang supplier yang melewati tenggat</p>
                            </div>
                            <span class="fin-pill-badge {{ ($countArOverdue + $countApOverdue) > 0 ? 'fin-pill-rose' : 'fin-pill-emerald' }}">
                                {{ $countArOverdue + $countApOverdue }} Tagihan
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table-modern" style="min-width: 600px;">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>No. Faktur</th>
                                        <th>Kontak / Entitas</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Nominal Belum Lunas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Overdue Piutang (AR) --}}
                                    @php $hasOverdueRows = false; @endphp
                                    @foreach($overdueArList as $ar)
                                        @if(($ar->sisa_tagihan ?? 0) > 0)
                                            @php $hasOverdueRows = true; @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fs-9 fw-semibold px-2.5 py-1">
                                                        Piutang 
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge-tx-code">{{ $ar->nomor_faktur }}</span>
                                                </td>
                                                <td>
                                                    <span class="fin-heading fs-8 d-block text-truncate fw-medium" style="max-width: 160px;" title="{{ $ar->customer->nama_customer ?? '-' }}">
                                                        {{ $ar->customer->nama_customer ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-danger fw-semibold fs-8">
                                                        {{ \Carbon\Carbon::parse($ar->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-danger fin-num fs-8">Rp {{ number_format($ar->sisa_tagihan, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    {{-- Overdue Hutang (AP) --}}
                                    @foreach($overdueApList as $ap)
                                        @php
                                            $apBalance = (float) ($ap->remaining_balance ?? $ap->grand_total);
                                        @endphp
                                        @if($apBalance > 0)
                                            @php $hasOverdueRows = true; @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fs-9 fw-semibold px-2.5 py-1">
                                                        Hutang 
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge-tx-code">{{ $ap->nomor_faktur }}</span>
                                                </td>
                                                <td>
                                                    <span class="fin-heading fs-8 d-block text-truncate fw-medium" style="max-width: 160px;" title="{{ $ap->supplier->nama_supplier ?? '-' }}">
                                                        {{ $ap->supplier->nama_supplier ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-danger fw-semibold fs-8">
                                                        {{ \Carbon\Carbon::parse($ap->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="fw-bold text-danger fin-num fs-8">Rp {{ number_format($apBalance, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    @if(!$hasOverdueRows)
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-success fw-semibold">
                                                <i class="bi bi-shield-check fs-4 d-block mb-1"></i>
                                                Luar biasa! Tidak ada piutang atau hutang jatuh tempo yang belum lunas.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 5. MUTASI KAS TERKINI & MONITORING PETTY CASH                             --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 g-xl-4 mb-4">

                {{-- Table 3: 8 Transaksi Mutasi Kas & Bank Terkini --}}
                <div class="col-12 col-xl-7">
                    <div class="cinematic-box p-3.5 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold fin-heading mb-1 fs-6">8 Mutasi Kas &amp; Bank Terkini</h6>
                                <p class="fin-subtext fs-8 mb-0">Pencatatan uang masuk, keluar, dan transfer antar kas</p>
                            </div>
                            <span class="fin-pill-badge fin-pill-slate"><i class="bi bi-activity me-1 text-success"></i> Live Feed</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table-modern" style="min-width: 620px;">
                                <thead>
                                    <tr>
                                        <th>No. Transaksi</th>
                                        <th>Rekening</th>
                                        <th class="text-center">Tipe</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMutasi as $m)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <div><span class="badge-tx-code">{{ $m->nomor_transaksi }}</span></div>
                                                    <span class="fs-9 fin-subtext" style="max-width: 220px; line-height: 1.35;" title="{{ $m->keterangan }}">
                                                        {{ $m->keterangan ?: '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="kpi-icon-pill {{ $m->tipe === 'Masuk' ? 'kpi-icon-emerald' : ($m->tipe === 'Keluar' ? 'kpi-icon-rose' : 'kpi-icon-indigo') }}" style="width: 34px; height: 34px; font-size: 0.95rem; border-radius: 9px; flex-shrink: 0;">
                                                        <i class="bi bi-{{ strtolower($m->kasBank?->tipe ?? '') === 'bank' ? 'bank2' : 'cash' }}"></i>
                                                    </div>
                                                    <div class="text-truncate" style="max-width: 170px;">
                                                        <span class="fin-heading fs-8 d-block fw-semibold text-truncate">{{ $m->kasBank->nama_bank ?? $m->kasBank->nama_akun ?? '-' }}</span>
                                                        <span class="fs-9 fin-subtext">{{ $m->kasBank->nomor_rekening ?: 'KAS TUNAI' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($m->tipe === 'Masuk')
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-9 fw-semibold px-2.5 py-1">
                                                        <i class="bi bi-arrow-down-left me-0.5"></i> Masuk
                                                    </span>
                                                @elseif($m->tipe === 'Keluar')
                                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-9 fw-semibold px-2.5 py-1">
                                                        <i class="bi bi-arrow-up-right me-0.5"></i> Keluar
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 fs-9 fw-semibold px-2.5 py-1">
                                                        <i class="bi bi-arrow-left-right me-0.5"></i> Transfer
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fs-8 fin-subtext">
                                                    {{ \Carbon\Carbon::parse($m->tanggal)->translatedFormat('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold fs-8 fin-num {{ $m->tipe === 'Masuk' ? 'text-success' : ($m->tipe === 'Keluar' ? 'text-danger' : 'text-info') }}">
                                                    {{ $m->tipe === 'Masuk' ? '+' : ($m->tipe === 'Keluar' ? '-' : '') }}Rp {{ number_format($m->jumlah, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 fin-subtext">Belum ada catatan mutasi kas &amp; bank.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 4: Monitoring Kas Kecil (Petty Cash) --}}
                <div class="col-12 col-xl-5">
                    <div class="cinematic-box p-3.5 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold fin-heading mb-1 fs-6">Kas Kecil</h6>
                                <p class="fin-subtext fs-8 mb-0">Pengeluaran operasional harian</p>
                            </div>
                            <a href="{{ route('accounting.petty-cash.index') }}" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1" style="font-size: 0.76rem;">
                                Buka Kas Kecil <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        {{-- Quick Petty Cash Highlights with better spacing & borders --}}
                        <div class="row g-2.5 mb-4">
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-15 h-100">
                                    <span class="fs-9 fin-subtext d-block mb-1.5"><i class="bi bi-wallet2 text-danger me-1"></i> Pengeluaran Bln Ini</span>
                                    <span class="fw-bold fin-heading fin-num fs-7 text-danger">Rp {{ number_format($pettyCashExpenseMonth, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-15 h-100">
                                    <span class="fs-9 fin-subtext d-block mb-1.5"><i class="bi bi-hourglass-split text-warning me-1"></i> Menunggu ACC</span>
                                    <span class="fw-bold fin-heading fin-num fs-7 text-warning">{{ $pendingPettyCashCount }} Pengajuan</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table-modern" style="min-width: 520px;">
                                <thead>
                                    <tr>
                                        <th>No. Transaksi</th>
                                        <th>Kategori</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPettyCash as $pc)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <div><span class="badge-tx-code">{{ $pc->nomor_transaksi }}</span></div>
                                                    <span class="fs-9 fin-subtext" style="max-width: 180px; line-height: 1.35;" title="{{ $pc->keterangan }}">
                                                        {{ $pc->keterangan ?: '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-body border border-secondary border-opacity-20 fs-9 fw-semibold px-2.5 py-1">
                                                    {{ $pc->kategori ?: 'Operasional' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fs-8 fin-subtext">
                                                    {{ \Carbon\Carbon::parse($pc->tanggal)->translatedFormat('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold fin-heading fin-num fs-8">Rp {{ number_format($pc->nominal, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 fin-subtext">Belum ada transaksi kas kecil.</td>
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(15, 23, 42, 0.06)';
                const textColor = isDark ? '#94a3b8' : '#64748b';

                // Currency formatter helper
                const formatRupiah = (val) => {
                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                };

                // ── 1. Chart Tren Arus Kas (6 Bulan Terakhir) ──────────────────────────
                const trendCtx = document.getElementById('cashFlowTrendChart');
                if (trendCtx) {
                    const ctx = trendCtx.getContext('2d');
                    const trendLabels  = @json($trendLabels);
                    const trendCashIn  = @json($trendCashIn);
                    const trendCashOut = @json($trendCashOut);
                    const trendNetCash = @json($trendNetCash);

                    // Soft cinematic gradients
                    const gradientIn = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientIn.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
                    gradientIn.addColorStop(0.65, 'rgba(16, 185, 129, 0.08)');
                    gradientIn.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

                    const gradientOut = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientOut.addColorStop(0, 'rgba(244, 63, 94, 0.30)');
                    gradientOut.addColorStop(0.65, 'rgba(244, 63, 94, 0.06)');
                    gradientOut.addColorStop(1, 'rgba(244, 63, 94, 0.0)');

                    const gradientNet = ctx.createLinearGradient(0, 0, 0, 320);
                    gradientNet.addColorStop(0, 'rgba(99, 102, 241, 0.22)');
                    gradientNet.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [
                                {
                                    label: 'Kas Masuk',
                                    data: trendCashIn,
                                    borderColor: '#10b981',
                                    backgroundColor: gradientIn,
                                    fill: true,
                                    tension: 0.42,
                                    borderWidth: 2.8,
                                    pointRadius: 4.5,
                                    pointHoverRadius: 8,
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: isDark ? '#141c2e' : '#ffffff',
                                    pointBorderWidth: 2.5,
                                },
                                {
                                    label: 'Kas Keluar',
                                    data: trendCashOut,
                                    borderColor: '#f43f5e',
                                    backgroundColor: gradientOut,
                                    fill: true,
                                    tension: 0.42,
                                    borderWidth: 2.8,
                                    pointRadius: 4.5,
                                    pointHoverRadius: 8,
                                    pointBackgroundColor: '#f43f5e',
                                    pointBorderColor: isDark ? '#141c2e' : '#ffffff',
                                    pointBorderWidth: 2.5,
                                },
                                {
                                    label: 'Net Cash Flow',
                                    data: trendNetCash,
                                    borderColor: '#818cf8',
                                    backgroundColor: gradientNet,
                                    borderDash: [6, 4],
                                    fill: false,
                                    tension: 0.42,
                                    borderWidth: 2.2,
                                    pointRadius: 3.5,
                                    pointHoverRadius: 7,
                                    pointBackgroundColor: '#818cf8',
                                    pointBorderColor: isDark ? '#141c2e' : '#ffffff',
                                    pointBorderWidth: 2,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 1100,
                                easing: 'easeOutQuart'
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                                    titleColor: isDark ? '#f8fafc' : '#0f172a',
                                    bodyColor: isDark ? '#cbd5e1' : '#334155',
                                    borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.1)',
                                    borderWidth: 1,
                                    padding: 13,
                                    cornerRadius: 10,
                                    boxPadding: 6,
                                    usePointStyle: true,
                                    callbacks: {
                                        label: function (ctx) {
                                            return ` ${ctx.dataset.label}: ${formatRupiah(ctx.parsed.y)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: textColor,
                                        font: { size: 11, weight: '500' }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    suggestedMin: 0,
                                    suggestedMax: 5000000,
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        color: textColor,
                                        font: { size: 11, weight: '500' },
                                        callback: function (val) {
                                            if (val === 0) return 'Rp 0';
                                            if (Math.abs(val) >= 1000000000) return 'Rp ' + (val / 1000000000).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' M';
                                            if (Math.abs(val) >= 1000000) return 'Rp ' + (val / 1000000).toLocaleString('id-ID', {maximumFractionDigits: 1}) + ' Jt';
                                            if (Math.abs(val) >= 1000) return 'Rp ' + (val / 1000).toLocaleString('id-ID', {maximumFractionDigits: 0}) + ' Rb';
                                            return 'Rp ' + Number(val).toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // ── 2. Chart Donut Distribusi Kas & Bank ─────────────────────────────
                const donutCtx = document.getElementById('kasBankDonutChart');
                if (donutCtx) {
                    const donutLabels = @json($donutLabels);
                    const donutData   = @json($donutData);
                    const donutColors = @json($donutColors);

                    if (donutData.length === 0 || donutData.every(v => v === 0)) {
                        // Placeholder if empty
                        donutLabels.push('Tidak Ada Saldo');
                        donutData.push(1);
                        donutColors.push('#94a3b8');
                    }

                    new Chart(donutCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: donutLabels,
                            datasets: [{
                                data: donutData,
                                backgroundColor: donutColors,
                                borderWidth: 2.5,
                                borderColor: isDark ? '#141c2e' : '#ffffff',
                                hoverOffset: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            animation: {
                                animateRotate: true,
                                animateScale: true,
                                duration: 1100
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(255, 255, 255, 0.98)',
                                    titleColor: isDark ? '#f8fafc' : '#0f172a',
                                    bodyColor: isDark ? '#cbd5e1' : '#334155',
                                    borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.1)',
                                    borderWidth: 1,
                                    padding: 12,
                                    cornerRadius: 10,
                                    boxPadding: 6,
                                    usePointStyle: true,
                                    callbacks: {
                                        label: function (ctx) {
                                            const val = ctx.parsed;
                                            return ` ${ctx.label}: ${formatRupiah(val)}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush

</x-layouts>
