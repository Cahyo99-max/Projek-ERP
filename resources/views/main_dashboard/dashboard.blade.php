<x-layouts title="Dashboard - ">
    @push('styles')
        <style>
            /* Mesh Gradients & Glassmorphism Welcome Card */
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
        </style>
    @endpush

    <!-- DASHBOARD CONTENT -->

    <!-- Breadcrumb & Page Title -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Dashboard Analytics</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">{{ $appName }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
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
            <p class="mb-0 text-secondary fs-7 fw-medium">{!! $summaryText ?? 'Selamat datang di sistem ERP.' !!}</p>
        </div>
    </div>

    {{-- Role-based Dashboard Views --}}
    @if(!empty($isPurchasingDashboard))
        @include('main_dashboard.partials.purchasing_dashboard')
    @else
        @include('main_dashboard.partials.admin_dashboard')
    @endif

</x-layouts>
