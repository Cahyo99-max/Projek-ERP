<x-layouts title="Dashboard - ">
    @push('styles')
        <style>
            /* Mesh Gradients & Glassmorphism Welcome Card */
            .premium-welcome-card {
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(99, 102, 241, 0.04) 50%, rgba(14, 165, 233, 0.05) 100%) !important;
                border: 1px solid rgba(37, 99, 235, 0.15) !important;
                border-radius: 22px !important;
                position: relative;
                overflow: hidden;
                box-shadow: 0 10px 30px -10px rgba(37, 99, 235, 0.08);
            }
            .premium-welcome-card::after {
                content: '';
                position: absolute;
                width: 320px;
                height: 320px;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
                top: -100px;
                right: -60px;
                pointer-events: none;
            }
            [data-bs-theme="dark"] .premium-welcome-card {
                background: linear-gradient(135deg, rgba(30, 58, 138, 0.3) 0%, rgba(15, 23, 42, 0.8) 50%, rgba(30, 41, 59, 0.65) 100%) !important;
                border: 1px solid rgba(59, 130, 246, 0.25) !important;
                box-shadow: 0 16px 40px -12px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(12px);
            }
            
            @keyframes pulse-primary {
                0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); }
                70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
                100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
            }
            .animate-pulse-primary {
                animation: pulse-primary 2.5s infinite ease-in-out;
            }
        </style>
    @endpush

    <!-- DASHBOARD CONTENT -->

    <!-- Breadcrumb & Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                @if(($dashboardType ?? 'admin') === 'purchasing')
                    Dashboard Purchasing & Pengadaan
                @else
                    Dashboard Analytics & System Control
                @endif
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">{{ $appName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>

        {{-- Role Dashboard Switcher Tabs --}}
        <div class="text-md-end">
            <div class="d-inline-flex flex-wrap gap-1.5 p-1 rounded-pill bg-body-tertiary border shadow-2xs">
                @if($hasAdminPermission ?? false)
                    <a href="{{ route('dashboard', ['role' => 'admin']) }}" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ ($dashboardType ?? 'admin') === 'admin' ? 'btn-primary shadow-xs' : 'text-body-secondary' }}" style="font-size: 0.8rem;">
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

    <!-- Welcome Alert -->
    <div class="card border-0 shadow-sm premium-welcome-card mb-4 p-4 p-md-4.5 position-relative overflow-hidden">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-center">
                <div class="me-3.5 flex-shrink-0 d-none d-sm-block">
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-white" 
                         style="width: 58px; height: 58px; background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45);">
                        <i class="bi bi-rocket-takeoff-fill fs-3"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h5 class="fw-bold mb-0 text-body">Selamat datang kembali, <span class="text-primary">{{ $userFullName ?? (auth()->user()->nama_lengkap ?? 'User') }}</span>! 👋</h5>
                        <span class="badge badge-soft-emerald fs-9 rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
                            <span class="rounded-circle animate-pulse-emerald" style="width: 7px; height: 7px; background-color: #10b981;"></span>
                            Online
                        </span>
                    </div>
                    <p class="mb-0 text-secondary fs-7 fw-medium">{!! $summaryText ?? 'Selamat datang di sistem ERP.' !!}</p>
                </div>
            </div>

            <!-- Right Quick Telemetry Chips -->
            <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
                <div class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-body-tertiary border border-secondary-subtle shadow-2xs">
                    <i class="bi bi-calendar3 text-primary fs-8"></i>
                    <span class="fs-8 fw-semibold text-body-secondary">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-body-tertiary border border-secondary-subtle shadow-2xs">
                    <i class="bi bi-shield-check text-success fs-8"></i>
                    <span class="fs-8 fw-semibold text-body-secondary">System Active</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Role-based Dashboard Views --}}
    @if(!empty($isPurchasingDashboard))
        @include('main_dashboard.partials.purchasing_dashboard')
    @else
        @include('main_dashboard.partials.admin_dashboard')
    @endif

</x-layouts>
