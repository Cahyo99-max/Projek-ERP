<x-layouts title="Dashboard Produksi Garmen - {{ $appName ?? 'ERP Pastac' }}">
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

            .pch-subtext-high {
                color: #475569;
            }
            [data-bs-theme="dark"] .pch-subtext-high {
                color: #cbd5e1 !important;
            }
            [data-bs-theme="dark"] .pch-subtext {
                color: #94a3b8 !important;
            }
            [data-bs-theme="dark"] .pch-heading {
                color: #f8fafc !important;
            }

            /* Responsive horizontal touch scroll for tables */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            .table-responsive::-webkit-scrollbar {
                height: 6px;
            }
            .table-responsive::-webkit-scrollbar-track {
                background: rgba(148, 163, 184, 0.08);
                border-radius: 999px;
            }
            .table-responsive::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.28);
                border-radius: 999px;
            }
            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: rgba(148, 163, 184, 0.48);
            }
            [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-track {
                background: rgba(15, 23, 42, 0.5);
            }
            [data-bs-theme="dark"] .table-responsive::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, 0.35);
            }

            .cinematic-table th,
            .cinematic-table td {
                white-space: nowrap !important;
            }

            .badge-urgent-solid {
                background-color: #ef4444 !important;
                color: #ffffff !important;
                font-weight: 700;
                padding: 0.28rem 0.65rem;
                border-radius: 9999px;
                font-size: 0.72rem;
            }
            .badge-tinggi-solid {
                background-color: #f59e0b !important;
                color: #ffffff !important;
                font-weight: 700;
                padding: 0.28rem 0.65rem;
                border-radius: 9999px;
                font-size: 0.72rem;
            }

            /* ── Modern Futuristic KPI Cards (Never Plain) ── */
            .kpi-card-modern {
                position: relative;
                border-radius: 20px;
                padding: 1.5rem;
                overflow: hidden;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                border: 1px solid rgba(226, 232, 240, 0.9);
                background: #ffffff;
                box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
            }

            .kpi-card-modern::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                opacity: 0.9;
                border-top-left-radius: 20px;
                border-top-right-radius: 20px;
                transition: height 0.25s ease;
            }

            .kpi-card-modern:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.12), 0 8px 16px -4px rgba(15, 23, 42, 0.04);
            }

            .kpi-card-modern:hover::before {
                height: 6px;
            }

            /* Watermark background icon for depth & personality */
            .kpi-watermark-icon {
                position: absolute;
                right: -10px;
                bottom: -15px;
                font-size: 6.2rem;
                line-height: 1;
                pointer-events: none;
                transition: transform 0.4s ease, opacity 0.4s ease;
                z-index: 1;
            }

            .kpi-card-modern:hover .kpi-watermark-icon {
                transform: scale(1.08) rotate(-4deg);
            }

            /* Card 1: Blue / SPK Aktif */
            .kpi-card-blue {
                background: linear-gradient(145deg, #ffffff 40%, #eff6ff 100%);
                border-color: rgba(191, 219, 254, 0.85);
            }
            .kpi-card-blue::before {
                background: linear-gradient(90deg, #3b82f6, #60a5fa);
            }
            .kpi-card-blue .kpi-watermark-icon {
                color: rgba(59, 130, 246, 0.08);
            }
            .kpi-card-blue:hover {
                border-color: rgba(59, 130, 246, 0.45);
            }

            /* Card 2: Emerald / Output Completion */
            .kpi-card-emerald {
                background: linear-gradient(145deg, #ffffff 40%, #ecfdf5 100%);
                border-color: rgba(167, 243, 208, 0.85);
            }
            .kpi-card-emerald::before {
                background: linear-gradient(90deg, #10b981, #34d399);
            }
            .kpi-card-emerald .kpi-watermark-icon {
                color: rgba(16, 185, 129, 0.08);
            }
            .kpi-card-emerald:hover {
                border-color: rgba(16, 185, 129, 0.45);
            }

            /* Card 3: Rose / Reject Rate */
            .kpi-card-rose {
                background: linear-gradient(145deg, #ffffff 40%, #fff1f2 100%);
                border-color: rgba(254, 205, 211, 0.85);
            }
            .kpi-card-rose::before {
                background: linear-gradient(90deg, #ef4444, #f87171);
            }
            .kpi-card-rose .kpi-watermark-icon {
                color: rgba(239, 68, 68, 0.08);
            }
            .kpi-card-rose:hover {
                border-color: rgba(239, 68, 68, 0.45);
            }

            /* Dark mode variations for KPI cards */
            [data-bs-theme="dark"] .kpi-card-modern {
                box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.6);
            }
            [data-bs-theme="dark"] .kpi-card-blue {
                background: linear-gradient(145deg, rgba(30, 41, 59, 0.85) 20%, rgba(30, 58, 138, 0.3) 100%);
                border-color: rgba(59, 130, 246, 0.28);
            }
            [data-bs-theme="dark"] .kpi-card-blue .kpi-watermark-icon {
                color: rgba(96, 165, 250, 0.09);
            }
            [data-bs-theme="dark"] .kpi-card-blue:hover {
                border-color: rgba(59, 130, 246, 0.55);
                box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.28);
            }

            [data-bs-theme="dark"] .kpi-card-emerald {
                background: linear-gradient(145deg, rgba(30, 41, 59, 0.85) 20%, rgba(6, 78, 59, 0.3) 100%);
                border-color: rgba(16, 185, 129, 0.28);
            }
            [data-bs-theme="dark"] .kpi-card-emerald .kpi-watermark-icon {
                color: rgba(52, 211, 153, 0.09);
            }
            [data-bs-theme="dark"] .kpi-card-emerald:hover {
                border-color: rgba(16, 185, 129, 0.55);
                box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.28);
            }

            [data-bs-theme="dark"] .kpi-card-rose {
                background: linear-gradient(145deg, rgba(30, 41, 59, 0.85) 20%, rgba(136, 19, 55, 0.3) 100%);
                border-color: rgba(239, 68, 68, 0.28);
            }
            [data-bs-theme="dark"] .kpi-card-rose .kpi-watermark-icon {
                color: rgba(248, 113, 113, 0.09);
            }
            [data-bs-theme="dark"] .kpi-card-rose:hover {
                border-color: rgba(239, 68, 68, 0.55);
                box-shadow: 0 15px 35px -5px rgba(239, 68, 68, 0.28);
            }

            /* Modern Gradient Icon Badges */
            .kpi-icon-pill {
                width: 44px;
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
                font-size: 1.25rem;
                transition: transform 0.25s ease;
            }
            .kpi-card-modern:hover .kpi-icon-pill {
                transform: scale(1.1);
            }

            .kpi-icon-blue {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.08) 100%);
                color: #2563eb;
                border: 1px solid rgba(59, 130, 246, 0.28);
            }
            .kpi-icon-emerald {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.08) 100%);
                color: #059669;
                border: 1px solid rgba(16, 185, 129, 0.28);
            }
            .kpi-icon-rose {
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.08) 100%);
                color: #dc2626;
                border: 1px solid rgba(239, 68, 68, 0.28);
            }

            .kpi-content-wrap {
                position: relative;
                z-index: 2;
            }

            /* Modern Pill Chips inside cards */
            .kpi-pill-chip {
                display: inline-flex;
                align-items: center;
                padding: 0.28rem 0.65rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                line-height: 1;
            }

            .kpi-chip-blue {
                background: rgba(59, 130, 246, 0.1);
                color: #2563eb;
                border: 1px solid rgba(59, 130, 246, 0.22);
            }
            [data-bs-theme="dark"] .kpi-chip-blue {
                background: rgba(59, 130, 246, 0.2);
                color: #93c5fd;
                border-color: rgba(59, 130, 246, 0.35);
            }

            .kpi-chip-emerald {
                background: rgba(16, 185, 129, 0.1);
                color: #059669;
                border: 1px solid rgba(16, 185, 129, 0.22);
            }
            [data-bs-theme="dark"] .kpi-chip-emerald {
                background: rgba(16, 185, 129, 0.2);
                color: #6ee7b7;
                border-color: rgba(16, 185, 129, 0.35);
            }

            .kpi-chip-rose {
                background: rgba(239, 68, 68, 0.1);
                color: #dc2626;
                border: 1px solid rgba(239, 68, 68, 0.22);
            }
            [data-bs-theme="dark"] .kpi-chip-rose {
                background: rgba(239, 68, 68, 0.2);
                color: #fca5a5;
                border-color: rgba(239, 68, 68, 0.35);
            }

            .kpi-chip-amber {
                background: rgba(245, 158, 11, 0.1);
                color: #d97706;
                border: 1px solid rgba(245, 158, 11, 0.22);
            }
            [data-bs-theme="dark"] .kpi-chip-amber {
                background: rgba(245, 158, 11, 0.2);
                color: #fcd34d;
                border-color: rgba(245, 158, 11, 0.35);
            }

            .kpi-chip-neutral {
                background: rgba(148, 163, 184, 0.12);
                color: #475569;
                border: 1px solid rgba(148, 163, 184, 0.22);
            }
            [data-bs-theme="dark"] .kpi-chip-neutral {
                background: rgba(148, 163, 184, 0.18);
                color: #cbd5e1;
                border-color: rgba(148, 163, 184, 0.35);
            }
        </style>
    @endpush

    <div class="content-body" style="padding-top: 1rem;">
        <div class="container-fluid px-3 px-md-4">

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 1. HEADER & ROLE DASHBOARD SWITCHER                                       --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="card premium-welcome-card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-lg-8">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                {{-- Strict Tenant Entity Badge --}}
                                @if($isBranchSession ?? false)
                                    <span class="pill-badge pill-badge-blue">
                                        <i class="bi bi-geo-alt-fill"></i> {{ $entityName ?? 'Cabang' }}
                                    </span>
                                @else
                                    <span class="pill-badge pill-badge-slate">
                                        <i class="bi bi-building-fill"></i> {{ $entityName ?? 'Pusat' }}
                                    </span>
                                @endif

                                <span class="pill-badge pill-badge-slate">
                                    <i class="bi bi-calendar3"></i> {{ now()->translatedFormat('d M Y') }}
                                </span>

                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-9 fw-semibold px-2.5 py-1">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> Pabrik Aktif
                                </span>
                            </div>

                            <h3 class="fw-bold pch-heading mb-1" style="letter-spacing: -0.5px;">
                                Selamat Bertugas, {{ $userFullName }}!
                            </h3>
                            <p class="pch-subtext mb-0 fs-7">
                                {!! $summaryText !!}
                            </p>
                        </div>

                        {{-- Role Dashboard Switcher Tabs --}}
                        <div class="col-12 col-lg-4 text-lg-end">
                            <div class="d-inline-flex flex-wrap gap-1.5 p-1 rounded-pill pch-tab-wrapper shadow-2xs">
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
                                <a href="{{ route('produksi.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold btn-primary shadow-xs" style="font-size: 0.8rem;">
                                    <i class="bi bi-tools me-1"></i> Produksi
                                </a>
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
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 2. 3 TOP KPI METRICS CARDS (MODERN & FUTURISTIC)                           --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                
                {{-- KPI 1: SPK Aktif --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-blue h-100">
                        {{-- Watermark Decorative Icon for Depth --}}
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>

                        <div class="kpi-content-wrap d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #2563eb; letter-spacing: 0.06em;">SPK Aktif</span>
                                        <div class="pch-subtext fs-9 mt-0.5">Order kerja lini garmen</div>
                                    </div>
                                    <div class="kpi-icon-pill kpi-icon-blue shadow-2xs">
                                        <i class="bi bi-gear-wide-connected"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <h2 class="fw-bolder pch-heading font-monospace mb-0" style="font-size: 2.25rem; letter-spacing: -1px;">
                                        {{ number_format($totalSpkActive ?? 0, 0, ',', '.') }}
                                    </h2>
                                    <span class="kpi-pill-chip kpi-chip-blue font-monospace">SPK Berjalan</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top border-opacity-10" style="border-color: rgba(59, 130, 246, 0.2) !important;">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="kpi-pill-chip kpi-chip-neutral">
                                        <i class="bi bi-hourglass-split me-1 text-warning"></i>{{ $totalSpkWaiting ?? 0 }} antrean
                                    </span>
                                    @if(($spkOverdueCount ?? 0) > 0)
                                        <span class="kpi-pill-chip kpi-chip-rose animate-pulse-primary">
                                            <i class="bi bi-alarm-fill me-1"></i>{{ $spkOverdueCount }} terlambat
                                        </span>
                                    @endif
                                </div>
                                <span class="kpi-pill-chip kpi-chip-emerald">
                                    <i class="bi bi-check2-circle me-1"></i>{{ $totalSpkCompletedMonth ?? 0 }} selesai bln ini
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Realisasi Output --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-emerald h-100">
                        {{-- Watermark Decorative Icon for Depth --}}
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-check2-all"></i>
                        </div>

                        <div class="kpi-content-wrap d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #059669; letter-spacing: 0.06em;">Realisasi Output</span>
                                        <div class="pch-subtext fs-9 mt-0.5">Ketercapaian target produksi</div>
                                    </div>
                                    <div class="kpi-icon-pill kpi-icon-emerald shadow-2xs">
                                        <i class="bi bi-check2-all"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline gap-2 mb-2">
                                    <h2 class="fw-bolder pch-heading font-monospace mb-0" style="font-size: 2.25rem; letter-spacing: -1px;">
                                        {{ number_format($outputRate ?? 0, 1, ',', '.') }}%
                                    </h2>
                                    <span class="kpi-pill-chip kpi-chip-emerald font-monospace">
                                        {{ number_format($totalFinishedQty ?? 0, 0, ',', '.') }} / {{ number_format($totalPlannedQty ?? 0, 0, ',', '.') }} pcs
                                    </span>
                                </div>

                                {{-- Modern Gradient Progress Bar --}}
                                <div class="progress mb-3" style="height: 7px; background: rgba(148, 163, 184, 0.18); border-radius: 999px;">
                                    <div class="progress-bar rounded-pill" 
                                         role="progressbar" 
                                         style="width: {{ min(100, max(0, $outputRate ?? 0)) }}%; background: linear-gradient(90deg, #10b981, #059669); box-shadow: 0 0 10px rgba(16, 185, 129, 0.35);">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top border-opacity-10" style="border-color: rgba(16, 185, 129, 0.2) !important;">
                                <span class="pch-subtext fs-8 fw-semibold">Target Produksi</span>
                                <span class="kpi-pill-chip {{ ($outputRate ?? 0) >= 80 ? 'kpi-chip-emerald' : (($outputRate ?? 0) >= 60 ? 'kpi-chip-amber' : 'kpi-chip-rose') }}">
                                    <i class="bi bi-bullseye me-1"></i>{{ ($outputRate ?? 0) >= 80 ? 'Optimal' : (($outputRate ?? 0) >= 60 ? 'Normal' : 'Tertinggal') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Tingkat Reject --}}
                <div class="col-12 col-md-4">
                    <div class="kpi-card-modern kpi-card-rose h-100">
                        {{-- Watermark Decorative Icon for Depth --}}
                        <div class="kpi-watermark-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <div class="kpi-content-wrap d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <span class="fs-8 fw-bold text-uppercase tracking-wider" style="color: #dc2626; letter-spacing: 0.06em;">Tingkat Reject</span>
                                        <div class="pch-subtext fs-9 mt-0.5">Kontrol kualitas afkir (QC)</div>
                                    </div>
                                    <div class="kpi-icon-pill kpi-icon-rose shadow-2xs">
                                        <i class="bi bi-shield-exclamation"></i>
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline gap-2 mb-2">
                                    <h2 class="fw-bolder {{ ($rejectRate ?? 0) > 3.0 ? 'text-danger' : 'pch-heading' }} font-monospace mb-0" style="font-size: 2.25rem; letter-spacing: -1px;">
                                        {{ number_format($rejectRate ?? 0, 1, ',', '.') }}%
                                    </h2>
                                    <span class="kpi-pill-chip {{ ($totalRejectQty ?? 0) > 0 ? 'kpi-chip-rose' : 'kpi-chip-neutral' }} font-monospace">
                                        {{ number_format($totalRejectQty ?? 0, 0, ',', '.') }} pcs afkir
                                    </span>
                                </div>

                                {{-- Modern Gradient Progress Bar --}}
                                <div class="progress mb-3" style="height: 7px; background: rgba(148, 163, 184, 0.18); border-radius: 999px;">
                                    <div class="progress-bar rounded-pill" 
                                         role="progressbar" 
                                         style="width: {{ min(100, max(0, ($rejectRate ?? 0) * 10)) }}%; background: linear-gradient(90deg, #10b981, {{ ($rejectRate ?? 0) <= 2.0 ? '#10b981' : (($rejectRate ?? 0) <= 4.0 ? '#f59e0b' : '#ef4444') }});">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top border-opacity-10" style="border-color: rgba(239, 68, 68, 0.2) !important;">
                                <span class="pch-subtext fs-8 fw-semibold">Toleransi QC (&le;2,5%)</span>
                                <span class="kpi-pill-chip {{ ($rejectRate ?? 0) <= 2.5 ? 'kpi-chip-emerald' : 'kpi-chip-rose' }}">
                                    <i class="bi {{ ($rejectRate ?? 0) <= 2.5 ? 'bi-shield-check' : 'bi-exclamation-diamond-fill' }} me-1"></i>
                                    {{ ($rejectRate ?? 0) <= 2.5 ? 'Terkendali' : 'Perlu Evaluasi' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 3. INTERACTIVE VISUAL ANALYTICS (TREND & STATUS)                           --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Chart 1: Tren Output & Reject --}}
                <div class="col-12 col-xl-8">
                    <div class="cinematic-box p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-3 pb-2 border-bottom gap-2" style="border-color: var(--pch-card-border) !important;">
                            <div>
                                <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-graph-up-arrow text-primary"></i> Tren Output &amp; Reject
                                </h5>
                                <p class="pch-subtext mb-0 fs-8">Aktivitas produksi 7 hari terakhir</p>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="pill-badge pill-badge-blue"><i class="bi bi-circle-fill me-1 text-primary"></i>Output Baik</span>
                                <span class="pill-badge pill-badge-rose"><i class="bi bi-circle-fill me-1 text-danger"></i>Reject</span>
                            </div>
                        </div>
                        <div style="height: 280px; position: relative;">
                            <canvas id="chartTrendProduksi"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Chart 2: Status SPK --}}
                <div class="col-12 col-xl-4">
                    <div class="cinematic-box p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom" style="border-color: var(--pch-card-border) !important;">
                            <h5 class="pch-heading mb-0 d-flex align-items-center gap-2 fs-6 fw-bold">
                                <i class="bi bi-pie-chart-fill text-warning"></i> Status SPK
                            </h5>
                            <span class="pill-badge pill-badge-cyan">Distribusi</span>
                        </div>
                        <div style="height: 220px; position: relative;" class="my-auto">
                            <canvas id="chartStatusSpk"></canvas>
                        </div>
                        <div class="pt-3 border-top mt-2" style="border-color: var(--pch-card-border) !important;">
                            <a href="{{ route('produksi.spk.index') }}" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold fs-8 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-list-task"></i>
                                <span>Kelola SPK</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            {{-- 4. ACTIONABLE CINEMATIC TABLES: ROW 1 (SPK PRIORITAS & TAHAPAN KERJA)      --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Table 1: SPK Prioritas & Tenggat --}}
                <div class="col-12 col-xl-7">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-exclamation-octagon-fill text-danger"></i> SPK Prioritas &amp; Tenggat
                                </h5>
                                <p class="pch-subtext mb-0 fs-8">SPK aktif yang memerlukan pengawasan segera</p>
                            </div>
                            <a href="{{ route('produksi.spk.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fs-8 fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                                <span>Lihat SPK</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto; overflow-x: auto;">
                            <table class="table cinematic-table align-middle mb-0" style="min-width: 680px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No. SPK &amp; Barang</th>
                                        <th class="text-center">Target</th>
                                        <th class="text-center">Progres</th>
                                        <th class="text-center">Tenggat</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($criticalSpkList ?? [] as $spk)
                                        @php
                                            $rencana = (float) ($spk->jumlah_rencana ?? 0);
                                            $selesai = (float) ($spk->jumlah_selesai ?? 0);
                                            $pct = $rencana > 0 ? round(($selesai / $rencana) * 100, 1) : 0;
                                            $isOverdue = $spk->target_selesai && $spk->target_selesai->isPast();
                                        @endphp
                                        <tr>
                                             <td class="ps-4">
                                                <div class="fw-bold font-monospace pch-heading">{{ $spk->nomor_spk }}</div>
                                                <small class="pch-subtext-high text-truncate d-inline-block" style="max-width: 220px;" title="{{ $spk->barangJadi->nama_barang ?? '-' }}">
                                                    {{ $spk->barangJadi->nama_barang ?? '-' }}
                                                </small>
                                            </td>
                                            <td class="text-center font-monospace fw-semibold pch-heading">
                                                {{ number_format($rencana, 0, ',', '.') }} {{ $spk->satuan->nama_satuan ?? 'Pcs' }}
                                            </td>
                                            <td class="text-center" style="min-width: 130px;">
                                                <div class="d-flex align-items-center gap-2 justify-content-center mb-1">
                                                    <span class="fs-9 font-monospace fw-bold pch-heading">{{ $pct }}%</span>
                                                    <small class="pch-subtext fs-9 font-monospace">({{ number_format($selesai, 0, ',', '.') }})</small>
                                                </div>
                                                <div class="progress" style="height: 5px; background: rgba(148, 163, 184, 0.2);">
                                                    <div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-primary' : 'bg-warning') }}" 
                                                         style="width: {{ min(100, max(0, $pct)) }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center font-monospace fs-9">
                                                @if($isOverdue)
                                                    <span class="text-danger fw-bold d-block">{{ $spk->target_selesai->format('d/m/Y') }}</span>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5 fs-9">Terlambat</span>
                                                @elseif($spk->target_selesai)
                                                    <span class="pch-heading fw-semibold d-block">{{ $spk->target_selesai->format('d/m/Y') }}</span>
                                                    <span class="pch-subtext fs-9">H-{{ now()->diffInDays($spk->target_selesai, false) }} Hari</span>
                                                @else
                                                    <span class="pch-subtext">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    @if($spk->prioritas === 'Urgent')
                                                        <span class="badge-urgent-solid"><i class="bi bi-lightning-fill"></i> Urgent</span>
                                                    @elseif($spk->prioritas === 'Tinggi')
                                                        <span class="badge-tinggi-solid">Tinggi</span>
                                                    @else
                                                        <span class="pill-badge pill-badge-slate">{{ $spk->prioritas ?? 'Normal' }}</span>
                                                    @endif

                                                    @if($spk->status === 'In Progress' || $spk->status === 'Sedang Dikerjakan')
                                                        <span class="pill-badge pill-badge-blue fs-9">In Progress</span>
                                                    @elseif($spk->status === 'Approved')
                                                        <span class="pill-badge pill-badge-emerald fs-9">Approved</span>
                                                    @else
                                                        <span class="pill-badge pill-badge-amber fs-9">{{ $spk->status }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 pch-subtext">
                                                <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                                                Seluruh SPK berjalan normal dan tepat waktu.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 2: Progres Tahapan Kerja --}}
                <div class="col-12 col-xl-5">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-clock-history text-info"></i> Progres Tahapan Kerja
                                </h5>
                                <p class="pch-subtext mb-0 fs-8">Status stasiun kerja aktif</p>
                            </div>
                            <a href="{{ route('produksi.realisasi.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fs-8 fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                                <span>Lihat Semua</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto; overflow-x: auto;">
                            <table class="table cinematic-table align-middle mb-0" style="min-width: 650px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">SPK &amp; Tahapan</th>
                                        <th>Operator / PIC</th>
                                        <th class="text-center">Target / Selesai</th>
                                        <th class="text-center">Reject</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeTahapanList ?? [] as $tahapan)
                                        @php
                                            $tTarget = (float) ($tahapan->target_qty ?? 0);
                                            $tSelesai = (float) ($tahapan->qty_selesai ?? 0);
                                            $tReject = (float) ($tahapan->qty_reject ?? 0);
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold pch-heading font-monospace fs-9">{{ $tahapan->nomor_spk }}</div>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5 fs-9">#{{ $tahapan->urutan }}</span>
                                                    <span class="fw-semibold pch-heading text-truncate" style="max-width: 140px;" title="{{ $tahapan->nama_tahapan }}">{{ $tahapan->nama_tahapan }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="pch-subtext-high"><i class="bi bi-person me-1 text-primary"></i>{{ $tahapan->pic_operator ?: 'Belum ditentukan' }}</small>
                                            </td>
                                            <td class="text-center font-monospace fs-9">
                                                <span class="fw-bold text-success">{{ number_format($tSelesai, 0, ',', '.') }}</span>
                                                <span class="pch-subtext">/ {{ number_format($tTarget, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-center font-monospace fs-9">
                                                @if($tReject > 0)
                                                    <span class="fw-bold text-danger">{{ number_format($tReject, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="pch-subtext">0</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($tahapan->status === 'In Progress' || $tahapan->status === 'Sedang Dikerjakan')
                                                    <span class="pill-badge pill-badge-cyan"><i class="bi bi-play-circle-fill me-1"></i>Berjalan</span>
                                                @elseif($tahapan->status === 'Completed' || $tahapan->status === 'Selesai')
                                                    <span class="pill-badge pill-badge-emerald"><i class="bi bi-check-all me-1"></i>Selesai</span>
                                                @else
                                                    <span class="pill-badge pill-badge-slate">{{ $tahapan->status ?: 'Pending' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 pch-subtext">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                Belum ada tahapan kerja aktif saat ini.
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
            {{-- 5. ACTIONABLE CINEMATIC TABLES: ROW 2 (BARANG JADI & BAHAN BAKU)          --}}
            {{-- ══════════════════════════════════════════════════════════════════════════ --}}
            <div class="row g-4 mb-4">
                
                {{-- Table 3: Penerimaan Barang Jadi --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-box-seam-fill text-success"></i> Penerimaan Barang Jadi
                                </h5>
                                <p class="pch-subtext mb-0 fs-8">Hasil inspeksi masuk ke gudang</p>
                            </div>
                            <a href="{{ route('produksi.barang-jadi.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fs-8 fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                                <span>Lihat Semua</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 440px; overflow-y: auto; overflow-x: auto;">
                            <table class="table cinematic-table align-middle mb-0" style="min-width: 640px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No. Terima &amp; SPK</th>
                                        <th>Barang &amp; Gudang</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Qty Masuk</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentFinishedGoods ?? [] as $fg)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold font-monospace pch-heading">{{ $fg->nomor_penerimaan }}</div>
                                                <small class="pch-subtext-high font-monospace fs-9">SPK: {{ $fg->spk->nomor_spk ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold pch-heading text-truncate" style="max-width: 170px;" title="{{ $fg->barangJadi->nama_barang ?? '-' }}">
                                                    {{ $fg->barangJadi->nama_barang ?? '-' }}
                                                </div>
                                                <small class="pch-subtext-high font-monospace fs-9"><i class="bi bi-geo-alt me-0.5 text-primary"></i>{{ $fg->gudang->nama_gudang ?? '-' }}</small>
                                            </td>
                                            <td class="text-center font-monospace fs-9 pch-subtext-high">
                                                {{ $fg->tanggal_penerimaan ? $fg->tanggal_penerimaan->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="text-center font-monospace fw-bold text-success">
                                                {{ number_format($fg->jumlah_hasil_baik ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($fg->status === 'Approved')
                                                    <span class="pill-badge pill-badge-emerald"><i class="bi bi-check2-circle me-1"></i>Approved</span>
                                                @elseif($fg->status === 'Pending')
                                                    <span class="pill-badge pill-badge-amber"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                                @elseif($fg->status === 'Rejected')
                                                    <span class="pill-badge pill-badge-rose"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                                @else
                                                    <span class="pill-badge pill-badge-slate">{{ $fg->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 pch-subtext">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                Belum ada penerimaan barang jadi terbaru.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Table 4: Pengeluaran Bahan Baku --}}
                <div class="col-12 col-xl-6">
                    <div class="cinematic-box p-0 h-100 overflow-hidden">
                        <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5" style="padding: 1.25rem 1.5rem !important;">
                            <div>
                                <h5 class="pch-heading mb-1 d-flex align-items-center gap-2 fs-6 fw-bold">
                                    <i class="bi bi-box-arrow-right text-primary"></i> Pengeluaran Bahan Baku
                                </h5>
                                <p class="pch-subtext mb-0 fs-8">Alokasi material ke lini produksi</p>
                            </div>
                            <a href="{{ route('produksi.pengeluaran-bahan.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fs-8 fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center gap-1.5">
                                <span>Lihat Semua</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive" style="max-height: 440px; overflow-y: auto; overflow-x: auto;">
                            <table class="table cinematic-table align-middle mb-0" style="min-width: 600px;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">No. Keluar &amp; SPK</th>
                                        <th>Gudang Asal</th>
                                        <th class="text-center">Tanggal</th>
                                        <th>Penyerah &amp; Penerima</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMaterialIssues ?? [] as $issue)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold font-monospace pch-heading">{{ $issue->nomor_pengeluaran }}</div>
                                                <small class="pch-subtext-high font-monospace fs-9">SPK: {{ $issue->spk->nomor_spk ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold pch-heading text-truncate" style="max-width: 150px;" title="{{ $issue->gudang->nama_gudang ?? '-' }}">
                                                    {{ $issue->gudang->nama_gudang ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-center font-monospace fs-9 pch-subtext-high">
                                                {{ $issue->tanggal_pengeluaran ? $issue->tanggal_pengeluaran->format('d/m/Y') : '-' }}
                                            </td>
                                            <td>
                                                <div class="fs-9 pch-heading"><i class="bi bi-arrow-right-short text-primary"></i> {{ $issue->penyerah_nama ?: '-' }}</div>
                                                <small class="pch-subtext fs-9"><i class="bi bi-arrow-left-short text-success"></i> {{ $issue->penerima_nama ?: '-' }}</small>
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($issue->status === 'Posted' || $issue->status === 'Completed' || $issue->status === 'Selesai')
                                                    <span class="pill-badge pill-badge-emerald"><i class="bi bi-check2-circle me-1"></i>Selesai</span>
                                                @elseif($issue->status === 'Draft')
                                                    <span class="pill-badge pill-badge-slate"><i class="bi bi-pencil me-1"></i>Draft</span>
                                                @else
                                                    <span class="pill-badge pill-badge-blue">{{ $issue->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 pch-subtext">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                Belum ada pengeluaran bahan baku terbaru.
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

                // ── 1. Chart Trend Realisasi Output vs Reject (7 Hari) ──
                const trendCtx = document.getElementById('chartTrendProduksi');
                let trendChart = null;

                if (trendCtx) {
                    const trendLabels  = @json($trendDates ?? []);
                    const outputData   = @json($trendOutputs ?? []);
                    const rejectData   = @json($trendRejects ?? []);

                    trendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [
                                {
                                    label: 'Output Baik',
                                    data: outputData,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#3b82f6'
                                },
                                {
                                    label: 'Reject',
                                    data: rejectData,
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    fill: true,
                                    tension: 0.35,
                                    borderWidth: 2,
                                    pointRadius: 3.5,
                                    pointBackgroundColor: '#ef4444'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    padding: 10,
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: getGridColor() },
                                    ticks: { color: getTextColor(), font: { size: 11 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: getGridColor() },
                                    ticks: { color: getTextColor(), font: { size: 11 } }
                                }
                            }
                        }
                    });
                }

                // ── 2. Chart Status SPK (Doughnut) ──
                const statusCtx = document.getElementById('chartStatusSpk');
                let statusChart = null;

                if (statusCtx) {
                    const statusObj = @json($statusChart ?? []);
                    const labels = Object.keys(statusObj);
                    const values = Object.values(statusObj);

                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: [
                                    '#3b82f6', // In Progress (Blue)
                                    '#10b981', // Approved (Green)
                                    '#f59e0b', // Pending (Amber)
                                    '#06b6d4', // Completed (Cyan)
                                    '#64748b', // Draft (Slate)
                                    '#ef4444'  // Cancelled (Red)
                                ],
                                borderWidth: 2,
                                borderColor: isDarkMode() ? '#1e293b' : '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: getTextColor(),
                                        boxWidth: 12,
                                        padding: 12,
                                        font: { size: 11 }
                                    }
                                },
                                tooltip: {
                                    padding: 10,
                                    cornerRadius: 8
                                }
                            },
                            cutout: '68%'
                        }
                    });
                }

                // Theme observer to dynamically update chart colors on light/dark mode switch
                const observer = new MutationObserver(function () {
                    const textColor = getTextColor();
                    const gridColor = getGridColor();

                    if (trendChart) {
                        trendChart.options.scales.x.grid.color = gridColor;
                        trendChart.options.scales.x.ticks.color = textColor;
                        trendChart.options.scales.y.grid.color = gridColor;
                        trendChart.options.scales.y.ticks.color = textColor;
                        trendChart.update();
                    }

                    if (statusChart) {
                        statusChart.data.datasets[0].borderColor = isDarkMode() ? '#1e293b' : '#ffffff';
                        statusChart.options.plugins.legend.labels.color = textColor;
                        statusChart.update();
                    }
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['data-bs-theme']
                });
            });
        </script>
    @endpush
</x-layouts>
