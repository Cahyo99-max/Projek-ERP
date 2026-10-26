<x-layouts title="Log Aktivitas User - {{ $appName }}">

    @push('styles')
    <style>
        /* ─── Modern KPI Glass Cards ─── */
        .kpi-card {
            border-radius: 20px;
            padding: 1.5rem 1.65rem !important;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            backdrop-filter: blur(10px);
        }
        .kpi-card:hover {
            transform: translateY(-4px);
        }
        
        /* Blue Card */
        .kpi-card-blue {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(30, 41, 59, 0.5) 100%);
            border: 1px solid rgba(59, 130, 246, 0.28);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .kpi-card-blue:hover {
            border-color: rgba(59, 130, 246, 0.55);
            box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }
        .kpi-icon-blue {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 8px 18px -2px rgba(59, 130, 246, 0.45);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        /* Green Card */
        .kpi-card-green {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(30, 41, 59, 0.5) 100%);
            border: 1px solid rgba(16, 185, 129, 0.28);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .kpi-card-green:hover {
            border-color: rgba(16, 185, 129, 0.55);
            box-shadow: 0 12px 28px -4px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }
        .kpi-icon-green {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            box-shadow: 0 8px 18px -2px rgba(16, 185, 129, 0.45);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        /* Red Card */
        .kpi-card-red {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(30, 41, 59, 0.5) 100%);
            border: 1px solid rgba(239, 68, 68, 0.28);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .kpi-card-red:hover {
            border-color: rgba(239, 68, 68, 0.55);
            box-shadow: 0 12px 28px -4px rgba(239, 68, 68, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.12);
        }
        .kpi-icon-red {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            box-shadow: 0 8px 18px -2px rgba(239, 68, 68, 0.45);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        /* Light Mode Overrides */
        [data-bs-theme="light"] .kpi-card-blue,
        html:not([data-bs-theme="dark"]) .kpi-card-blue {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
            border-color: #dbeafe;
        }
        [data-bs-theme="light"] .kpi-card-green,
        html:not([data-bs-theme="dark"]) .kpi-card-green {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            border-color: #dcfce7;
        }
        [data-bs-theme="light"] .kpi-card-red,
        html:not([data-bs-theme="dark"]) .kpi-card-red {
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border-color: #fee2e2;
        }

        /* KPI Titles - High Contrast in Dark & Light Modes */
        .kpi-title-blue {
            color: #3b82f6 !important;
        }
        [data-bs-theme="dark"] .kpi-title-blue {
            color: #60a5fa !important;
        }
        .kpi-title-green {
            color: #059669 !important;
        }
        [data-bs-theme="dark"] .kpi-title-green {
            color: #34d399 !important;
        }
        .kpi-title-red {
            color: #dc2626 !important;
        }
        [data-bs-theme="dark"] .kpi-title-red {
            color: #f87171 !important;
        }

        /* KPI Pill Badges - Crisp, vibrant & 100% visible white text */
        .kpi-pill-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
        }
        .kpi-pill-green {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
        }
        .kpi-pill-red {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        }

        /* ─── Prune Modal Modern High-End Styling ─── */
        #modalPruneLogs .modal-content {
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            background: #0f172a !important;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.9) !important;
            border-radius: 22px !important;
        }
        [data-bs-theme="light"] #modalPruneLogs .modal-content {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.12) !important;
        }

        .prune-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45) !important;
        }

        .prune-warning-box {
            background: rgba(239, 68, 68, 0.14) !important;
            border: 1px solid rgba(239, 68, 68, 0.5) !important;
            border-radius: 14px !important;
            padding: 0.95rem 1.15rem !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="light"] .prune-warning-box {
            color: #1e293b !important;
            background: #fef2f2 !important;
            border-color: #fca5a5 !important;
        }
        .prune-warning-box strong {
            color: #fca5a5 !important;
        }
        [data-bs-theme="light"] .prune-warning-box strong {
            color: #dc2626 !important;
        }

        .prune-badge-danger {
            background: rgba(239, 68, 68, 0.25) !important;
            color: #fca5a5 !important;
            border: 1px solid rgba(239, 68, 68, 0.55) !important;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            letter-spacing: 0.3px;
        }

        .prune-badge-recommend {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.72rem;
            padding: 0.28rem 0.8rem;
            border-radius: 50rem;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            letter-spacing: 0.3px;
        }

        .prune-badge-reset {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.72rem;
            padding: 0.28rem 0.8rem;
            border-radius: 50rem;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
            letter-spacing: 0.3px;
        }

        .prune-option-card {
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(30, 41, 59, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 14px !important;
            padding: 0.9rem 1.15rem !important;
            cursor: pointer;
            user-select: none;
        }
        [data-bs-theme="light"] .prune-option-card {
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .prune-option-card:hover {
            background: rgba(59, 130, 246, 0.12) !important;
            border-color: rgba(59, 130, 246, 0.55) !important;
            transform: translateX(3px);
        }
        .prune-option-card:has(input:checked) {
            background: rgba(59, 130, 246, 0.2) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1.5px rgba(59, 130, 246, 0.5);
        }
        .prune-option-card input[type="radio"] {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: #3b82f6;
            cursor: pointer;
        }

        .prune-option-card.card-danger {
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.4) !important;
        }
        .prune-option-card.card-danger:hover {
            background: rgba(239, 68, 68, 0.18) !important;
            border-color: rgba(239, 68, 68, 0.7) !important;
            transform: translateX(3px);
        }
        .prune-option-card.card-danger:has(input:checked) {
            background: rgba(239, 68, 68, 0.25) !important;
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 1.5px rgba(239, 68, 68, 0.6);
        }
        .prune-option-card.card-danger input[type="radio"] {
            accent-color: #ef4444;
        }

        .prune-option-title {
            color: #f8fafc !important;
            font-size: 0.9rem !important;
            font-weight: 600;
        }
        [data-bs-theme="light"] .prune-option-title {
            color: #0f172a !important;
        }

        .prune-option-desc {
            color: #cbd5e1 !important;
            font-size: 0.78rem !important;
            margin-top: 2px;
            font-weight: 400;
        }
        [data-bs-theme="light"] .prune-option-desc {
            color: #64748b !important;
        }

        .prune-danger-title {
            color: #fca5a5 !important;
            font-size: 0.9rem !important;
            font-weight: 700;
        }
        [data-bs-theme="light"] .prune-danger-title {
            color: #dc2626 !important;
        }
        .prune-danger-sub {
            color: #fecaca !important;
            font-size: 0.78rem !important;
            margin-top: 2px;
            opacity: 0.95;
        }
        [data-bs-theme="light"] .prune-danger-sub {
            color: #ef4444 !important;
        }

        .swal2-container {
            z-index: 10060 !important;
        }

        /* SweetAlert2 High-End Theme for Prune */
        .prune-swal-popup {
            background: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 22px !important;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.9) !important;
            padding: 1.85rem 1.6rem !important;
        }
        [data-bs-theme="light"] .prune-swal-popup {
            background: #ffffff !important;
            border-color: rgba(0, 0, 0, 0.12) !important;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.2) !important;
        }
        .prune-swal-popup .swal2-title {
            color: #f8fafc !important;
            font-size: 1.35rem !important;
            font-weight: 700 !important;
            padding-top: 0.5rem !important;
        }
        [data-bs-theme="light"] .prune-swal-popup .swal2-title {
            color: #0f172a !important;
        }
        .prune-swal-popup .swal2-html-container {
            color: #cbd5e1 !important;
            font-size: 0.88rem !important;
            margin: 1rem 0 0.8rem 0 !important;
        }
        [data-bs-theme="light"] .prune-swal-popup .swal2-html-container {
            color: #475569 !important;
        }

        .table-logs thead th {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--bs-secondary-color);
            border-bottom: 1px solid var(--bs-border-color);
            background-color: rgba(var(--bs-tertiary-bg-rgb), 0.5);
        }
        .table-logs tbody tr {
            transition: background-color 0.15s ease;
        }
        .table-logs tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.04) !important;
        }
        .ip-chip {
            font-family: var(--bs-font-monospace);
            font-size: 0.78rem;
            padding: 0.25rem 0.55rem;
            border-radius: 8px;
            background-color: rgba(148, 163, 184, 0.12);
            color: var(--bs-body-color);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid var(--bs-border-color-translucent);
        }
        .badge-activity-login {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(6, 182, 212, 0.15) 100%);
            color: #2563eb;
            border: 1px solid rgba(37, 99, 235, 0.25);
        }
        [data-bs-theme="dark"] .badge-activity-login {
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.3);
        }
        .badge-activity-logout {
            background-color: rgba(100, 116, 139, 0.12);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.25);
        }
        [data-bs-theme="dark"] .badge-activity-logout {
            color: #94a3b8;
            border-color: rgba(148, 163, 184, 0.3);
        }
        .badge-status-success {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.35) !important;
        }
        [data-bs-theme="dark"] .badge-status-success {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border-color: rgba(52, 211, 153, 0.4) !important;
        }
        .badge-status-failed {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #dc2626 !important;
            border: 1px solid rgba(239, 68, 68, 0.35) !important;
        }
        [data-bs-theme="dark"] .badge-status-failed {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #f87171 !important;
            border-color: rgba(248, 113, 113, 0.4) !important;
        }
        .user-avatar-circle {
            width: 36px;
            height: 36px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* ─── Pagination Styling ─── */
        .pagination {
            margin-bottom: 0;
            gap: 4px;
            display: inline-flex;
            flex-wrap: wrap;
        }
        .page-item .page-link {
            border-radius: 8px !important;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
            color: var(--bs-body-color);
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            transition: all 0.2s ease-in-out;
            box-shadow: none !important;
        }
        .page-item.active .page-link {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3) !important;
        }
        .page-item.disabled .page-link {
            opacity: 0.45;
            background-color: transparent;
            border-color: var(--bs-border-color-translucent);
            color: var(--bs-secondary-color);
        }
        .page-item .page-link:hover:not(.active) {
            background-color: rgba(37, 99, 235, 0.08);
            border-color: #3b82f6;
            color: #2563eb;
        }
        /* Cegah SVG default meluber besar */
        .pagination svg,
        svg.w-5 {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
        }
    </style>
    @endpush

    <!-- Breadcrumb & Header Toolbar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm flex-shrink-0"
                     style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold m-0 text-body-emphasis">Log Aktivitas Pengguna</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.78rem;">
                            <li class="breadcrumb-item"><a href="/dashbord" class="text-decoration-none text-muted">{{ $appName }}</a></li>
                            <li class="breadcrumb-item text-muted">Audit &amp; Keamanan</li>
                            <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Log Aktivitas</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('hapus-log-aktivitas-user'))
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPruneLogs">
                <i class="bi bi-trash3-fill"></i>
                <span>Bersihkan Log</span>
            </button>
            @endif
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" onclick="window.location.reload();">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh</span>
            </button>
            <a href="/dashbord" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 shadow-sm fw-semibold d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%); border: none;">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2.5 mb-4 p-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
            <div class="fw-medium small">{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2.5 mb-4 p-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
            <div class="fw-medium small">{{ session('error') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 p-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-x-circle-fill fs-5 text-danger"></i>
                <div class="fw-bold small">Terjadi Kesalahan:</div>
            </div>
            <ul class="mb-0 small ps-4">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $successPct = $totalActivities > 0 ? round(($totalSuccessLogin / $totalActivities) * 100, 1) : 0;
        $failedPct = $totalActivities > 0 ? round(($totalFailedLogin / $totalActivities) * 100, 1) : 0;
    @endphp

    <!-- Summary Statistics KPI Cards (Modern Glass Style) -->
    <div class="row g-4 mb-4 pb-2">
        <!-- Card 1: Total Aktivitas -->
        <div class="col-12 col-md-4">
            <div class="card p-4 h-100 kpi-card kpi-card-blue">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="kpi-title-blue small text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.76rem; letter-spacing: 0.8px;">
                            <span class="spinner-grow spinner-grow-sm" role="status" style="width: 6px; height: 6px;"></span>
                            TOTAL AKTIVITAS
                        </span>
                        <h2 class="mb-0 mt-2 fw-bold text-body-emphasis font-monospace" style="letter-spacing: -0.5px; font-size: 2.25rem;">
                            {{ number_format($totalActivities) }}
                        </h2>
                    </div>
                    <div class="kpi-icon-blue flex-shrink-0">
                        <i class="bi bi-activity"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-white border-opacity-10 mt-auto">
                    <small class="text-body-secondary" style="font-size: 0.78rem;">Seluruh rekaman audit sistem</small>
                    <span class="badge rounded-pill kpi-pill-blue px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                        100% Tercatat
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Login Berhasil -->
        <div class="col-12 col-md-4">
            <div class="card p-4 h-100 kpi-card kpi-card-green">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="kpi-title-green small text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.76rem; letter-spacing: 0.8px;">
                            <i class="bi bi-shield-check"></i>
                            LOGIN BERHASIL
                        </span>
                        <h2 class="mb-0 mt-2 fw-bold text-success font-monospace" style="letter-spacing: -0.5px; font-size: 2.25rem;">
                            {{ number_format($totalSuccessLogin) }}
                        </h2>
                    </div>
                    <div class="kpi-icon-green flex-shrink-0">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-white border-opacity-10 mt-auto">
                    <small class="text-body-secondary" style="font-size: 0.78rem;">Autentikasi akun sah</small>
                    <span class="badge rounded-pill kpi-pill-green px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                        <i class="bi bi-arrow-up-right me-0.5"></i>{{ $successPct }}% Sukses
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Login Gagal -->
        <div class="col-12 col-md-4">
            <div class="card p-4 h-100 kpi-card kpi-card-red">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <span class="kpi-title-red small text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.76rem; letter-spacing: 0.8px;">
                            <i class="bi bi-shield-exclamation"></i>
                            LOGIN GAGAL
                        </span>
                        <h2 class="mb-0 mt-2 fw-bold text-danger font-monospace" style="letter-spacing: -0.5px; font-size: 2.25rem;">
                            {{ number_format($totalFailedLogin) }}
                        </h2>
                    </div>
                    <div class="kpi-icon-red flex-shrink-0">
                        <i class="bi bi-shield-fill-x"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-white border-opacity-10 mt-auto">
                    <small class="text-body-secondary" style="font-size: 0.78rem;">Percobaan tidak valid</small>
                    <span class="badge rounded-pill kpi-pill-red px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                        <i class="bi bi-exclamation-triangle me-0.5"></i>{{ $failedPct }}% Gagal
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="card border rounded-4 shadow-sm p-4" style="background-color: var(--bs-body-bg);">
        <!-- Table Header & Live Search / Filter Toolbar -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 pb-3 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center text-white rounded-3 shadow-sm flex-shrink-0"
                     style="width: 44px; height: 44px; background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 text-body-emphasis">Riwayat Autentikasi Pengguna</h5>
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 0.7rem;">Live Log</span>
                    </div>
                    <small class="text-muted" style="font-size: 0.78rem;">Daftar aktivitas login &amp; logout pengguna secara real-time.</small>
                </div>
            </div>

            <!-- Filter & Search Form -->
            <form action="{{ route('activity-logs.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 w-100 w-lg-auto justify-content-lg-end" autocomplete="off">
                <!-- Search Box -->
                <div class="input-group input-group-sm" style="min-width: 220px; max-width: 280px;">
                    <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control bg-body-tertiary border-start-0 rounded-end-pill pe-3"
                           placeholder="Cari nama, email, IP..." style="font-size: 0.8rem;">
                </div>

                <!-- Filter Aktivitas -->
                <select name="activity" class="form-select form-select-sm rounded-pill" style="width: auto; min-width: 130px; font-size: 0.8rem;" onchange="this.form.submit();">
                    <option value="">Semua Aktivitas</option>
                    <option value="Login" {{ $activityFilter === 'Login' ? 'selected' : '' }}>Login</option>
                    <option value="Logout" {{ $activityFilter === 'Logout' ? 'selected' : '' }}>Logout</option>
                </select>

                <!-- Filter Status -->
                <select name="status" class="form-select form-select-sm rounded-pill" style="width: auto; min-width: 125px; font-size: 0.8rem;" onchange="this.form.submit();">
                    <option value="">Semua Status</option>
                    <option value="Success" {{ $statusFilter === 'Success' ? 'selected' : '' }}>Berhasil</option>
                    <option value="Failed" {{ $statusFilter === 'Failed' ? 'selected' : '' }}>Gagal</option>
                </select>

                <!-- Per Page (Default 10) -->
                <select name="per_page" class="form-select form-select-sm rounded-pill" style="width: auto; font-size: 0.8rem;" onchange="this.form.submit();" title="Jumlah data per halaman">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / hal</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / hal</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / hal</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / hal</option>
                </select>

                @if(!empty($search) || !empty($activityFilter) || !empty($statusFilter) || $perPage != 10)
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 shadow-xs" title="Reset Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-responsive rounded-3 border">
            <table class="table table-logs align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center py-3" style="width: 50px;">NO</th>
                        <th class="py-3" style="min-width: 160px;">WAKTU</th>
                        <th class="py-3" style="min-width: 220px;">PENGGUNA</th>
                        <th class="py-3" style="min-width: 120px;">AKTIVITAS</th>
                        <th class="py-3" style="min-width: 140px;">IP ADDRESS</th>
                        <th class="py-3" style="min-width: 180px;">PERANGKAT / BROWSER</th>
                        <th class="py-3 text-center" style="min-width: 120px;">STATUS</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($logs as $index => $log)
                        @php
                            $rowNum = ($logs->currentPage() - 1) * $logs->perPage() + $index + 1;
                            $userName = $log->user ? $log->user->nama_lengkap : ($log->username ?: 'Guest / Anonim');
                            $initial = strtoupper(substr($userName, 0, 1));

                            // Browser icon helper
                            $browserIcon = 'bi-globe';
                            $b = strtolower($log->device_browser ?? '');
                            if (str_contains($b, 'chrome')) $browserIcon = 'bi-browser-chrome text-warning';
                            elseif (str_contains($b, 'edge')) $browserIcon = 'bi-browser-edge text-primary';
                            elseif (str_contains($b, 'firefox')) $browserIcon = 'bi-browser-firefox text-danger';
                            elseif (str_contains($b, 'safari')) $browserIcon = 'bi-browser-safari text-info';
                            elseif (str_contains($b, 'opera')) $browserIcon = 'bi-browser-chrome text-danger';

                            // OS icon helper
                            $osIcon = 'bi-laptop';
                            $os = strtolower($log->device_os ?? '');
                            if (str_contains($os, 'windows')) $osIcon = 'bi-windows text-info';
                            elseif (str_contains($os, 'mac') || str_contains($os, 'ios')) $osIcon = 'bi-apple text-secondary';
                            elseif (str_contains($os, 'android')) $osIcon = 'bi-android2 text-success';
                            elseif (str_contains($os, 'linux')) $osIcon = 'bi-terminal text-warning';
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $rowNum }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-calendar3 text-muted" style="font-size: 0.72rem;"></i>
                                    <span class="fw-semibold text-body-emphasis">{{ $log->created_at->format('d/m/Y') }}</span>
                                    <span class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $log->created_at->format('H:i') }}</span>
                                </div>
                                <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $log->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="user-avatar-circle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body-emphasis">{{ $userName }}</div>
                                        @if($log->user)
                                            <small class="text-muted d-block" style="font-size: 0.73rem;">{{ $log->user->email }}</small>
                                        @else
                                            <small class="text-muted fst-italic" style="font-size: 0.73rem;">Tidak terdaftar</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->activity === 'Login')
                                    <span class="badge badge-activity-login rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        <span>Login</span>
                                    </span>
                                @else
                                    <span class="badge badge-activity-logout rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Logout</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="ip-chip">
                                    <i class="bi bi-hdd-network text-muted"></i>
                                    <span>{{ $log->ip_address }}</span>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ $browserIcon }} fs-6"></i>
                                    <div>
                                        <span class="fw-medium text-body-emphasis d-block" style="font-size: 0.78rem;">{{ $log->device_browser }}</span>
                                        <small class="text-muted d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                            <i class="bi {{ $osIcon }}"></i>
                                            <span>{{ $log->device_os }}</span>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($log->status === 'Success')
                                    <span class="badge rounded-pill badge-status-success px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                        <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 5px; height: 5px;"></span>
                                        <span>Berhasil</span>
                                    </span>
                                @else
                                    <span class="badge rounded-pill badge-status-failed px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" 
                                          style="font-size: 0.74rem;" 
                                          title="{{ $log->status_message ?: 'Percobaan login tidak berhasil' }}">
                                        <i class="bi bi-x-circle-fill"></i>
                                        <span>{{ $log->status_message ?: 'Gagal' }}</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox fs-1 d-block text-secondary opacity-50 mb-2"></i>
                                    <h6 class="fw-bold mb-1 text-body-emphasis">Tidak Ada Data Ditemukan</h6>
                                    <p class="small text-muted mb-0">Belum ada riwayat aktivitas yang sesuai dengan parameter pencarian/filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Pagination & Entry Information Footer -->
        <div class="mt-3.5 pt-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <span class="text-muted small" style="font-size: 0.78rem;">
                Menampilkan <b>{{ $logs->firstItem() ?? 0 }}</b> - <b>{{ $logs->lastItem() ?? 0 }}</b> dari <b>{{ number_format($logs->total()) }}</b> riwayat aktivitas
            </span>
            <div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Bersihkan / Hapus Log Permanen -->
    <div class="modal fade" id="modalPruneLogs" tabindex="-1" aria-labelledby="modalPruneLogsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form action="{{ route('activity-logs.prune') }}" method="POST" id="formPruneLogs">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-bottom border-light-subtle pb-3 px-4 pt-3.5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="prune-header-icon flex-shrink-0">
                                <i class="bi bi-trash3-fill fs-5 text-white"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalPruneLogsLabel">Bersihkan Audit Log Pengguna</h6>
                                <span class="prune-badge-danger mt-1">
                                    <i class="bi bi-shield-x"></i> Hapus Permanen
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body py-3 px-4">
                        <div class="prune-warning-box d-flex align-items-start gap-2.5 mb-3.5">
                            <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-0.5" style="color: #f87171;"></i>
                            <div style="font-size: 0.84rem; line-height: 1.5;">
                                <strong style="color: #fca5a5;">Peringatan Keamanan:</strong> Data audit log yang dipilih akan dihapus secara <strong>permanen</strong> dari database untuk menghemat ruang penyimpanan. Data yang sudah dihapus tidak dapat dipulihkan kembali.
                            </div>
                        </div>

                        <label class="form-label fw-bold text-body-emphasis small mb-2.5">Pilih Kriteria Pembersihan:</label>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <!-- Opsi 30 Hari -->
                            <label class="form-check-label d-flex align-items-center justify-content-between cursor-pointer prune-option-card">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="mode30" value="30_days" checked onchange="toggleCustomRange(false)">
                                    <div>
                                        <div class="prune-option-title">Lebih lama dari 30 hari yang lalu</div>
                                        <div class="prune-option-desc">Menyimpan riwayat log 1 bulan terakhir (Disarankan)</div>
                                    </div>
                                </div>
                                <span class="prune-badge-recommend">Rekomendasi</span>
                            </label>

                            <!-- Opsi 60 Hari -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="mode60" value="60_days" onchange="toggleCustomRange(false)">
                                <div>
                                    <div class="prune-option-title">Lebih lama dari 60 hari yang lalu</div>
                                    <div class="prune-option-desc">Menyimpan riwayat log 2 bulan terakhir</div>
                                </div>
                            </label>

                            <!-- Opsi 90 Hari -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="mode90" value="90_days" onchange="toggleCustomRange(false)">
                                <div>
                                    <div class="prune-option-title">Lebih lama dari 90 hari yang lalu</div>
                                    <div class="prune-option-desc">Menyimpan riwayat log 3 bulan terakhir</div>
                                </div>
                            </label>

                            <!-- Opsi Custom Range -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="modeCustom" value="custom_range" onchange="toggleCustomRange(true)">
                                <div>
                                    <div class="prune-option-title">Pilih Rentang Tanggal Spesifik</div>
                                    <div class="prune-option-desc">Hapus log dari tanggal tertentu sampai tanggal tertentu</div>
                                </div>
                            </label>

                            <!-- Form Input Custom Range (Hidden by default) -->
                            <div id="customRangeFields" class="p-3 bg-body-tertiary border rounded-3 d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="start_date" class="form-label small fw-semibold mb-1 text-body-emphasis">Dari Tanggal:</label>
                                        <input type="date" class="form-control form-control-sm rounded-3" name="start_date" id="start_date" max="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="end_date" class="form-label small fw-semibold mb-1 text-body-emphasis">Sampai Tanggal:</label>
                                        <input type="date" class="form-control form-control-sm rounded-3" name="end_date" id="end_date" max="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Opsi Semua Log -->
                            <label class="form-check-label d-flex align-items-center justify-content-between cursor-pointer prune-option-card card-danger">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="modeAll" value="all" onchange="toggleCustomRange(false)">
                                    <div>
                                        <div class="prune-danger-title">Kosongkan Semua Log (Reset Total)</div>
                                        <div class="prune-danger-sub">Menghapus seluruh rekaman audit tanpa terkecuali</div>
                                    </div>
                                </div>
                                <span class="prune-badge-reset">Reset Total</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-light-subtle py-2.5 px-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" onclick="handlePruneSubmit()" class="btn btn-danger btn-sm rounded-pill px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: none;">
                            <i class="bi bi-trash3-fill"></i>
                            <span>Hapus Permanen</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleCustomRange(show) {
            const container = document.getElementById('customRangeFields');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            if (show) {
                container.classList.remove('d-none');
                startDate.required = true;
                endDate.required = true;
            } else {
                container.classList.add('d-none');
                startDate.required = false;
                endDate.required = false;
            }
        }

        function handlePruneSubmit() {
            const mode = document.querySelector('input[name="prune_mode"]:checked').value;
            let title = 'Konfirmasi Pembersihan Log';
            let htmlText = '';

            if (mode === 'all') {
                title = 'Kosongkan Semua Log?';
                htmlText = '<div style="text-align: left; background: rgba(239, 68, 68, 0.18); border: 1.5px solid rgba(239, 68, 68, 0.55); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-weight: 700; font-size: 0.88rem; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">'
                         + '<span style="background: #ef4444; color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; letter-spacing: 0.3px;">PERINGATAN</span> <span style="color: #fca5a5;">Tindakan Permanen!</span>'
                         + '</div>'
                         + '<div style="color: #fecaca; font-size: 0.82rem; line-height: 1.5;">'
                         + 'Anda akan menghapus <strong>SELURUH</strong> riwayat audit log aktivitas tanpa terkecuali. Tindakan ini bersifat permanen dan tidak dapat dibatalkan.'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0; font-weight: 500;">Apakah Anda yakin ingin mengosongkan seluruh database log?</p>';
            } else if (mode === '30_days') {
                title = 'Hapus Log > 30 Hari?';
                htmlText = '<div style="text-align: left; background: rgba(59, 130, 246, 0.16); border: 1.5px solid rgba(59, 130, 246, 0.45); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-size: 0.88rem; line-height: 1.5;">'
                         + 'Semua data log yang lebih lama dari <strong style="color: #93c5fd;">30 hari yang lalu</strong> akan dihapus permanen dari database.'
                         + '</div>'
                         + '<div style="color: #94a3b8; font-size: 0.78rem; margin-top: 8px; display: flex; align-items: center; gap: 5px;">'
                         + '<i class="bi bi-shield-check text-success"></i> <span style="color: #cbd5e1;">Log 1 bulan terakhir tetap tersimpan aman.</span>'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0;">Lanjutkan penghapusan permanen log?</p>';
            } else if (mode === '60_days') {
                title = 'Hapus Log > 60 Hari?';
                htmlText = '<div style="text-align: left; background: rgba(59, 130, 246, 0.16); border: 1.5px solid rgba(59, 130, 246, 0.45); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-size: 0.88rem; line-height: 1.5;">'
                         + 'Semua data log yang lebih lama dari <strong style="color: #93c5fd;">60 hari yang lalu</strong> akan dihapus permanen dari database.'
                         + '</div>'
                         + '<div style="color: #94a3b8; font-size: 0.78rem; margin-top: 8px; display: flex; align-items: center; gap: 5px;">'
                         + '<i class="bi bi-shield-check text-success"></i> <span style="color: #cbd5e1;">Log 2 bulan terakhir tetap tersimpan aman.</span>'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0;">Lanjutkan penghapusan permanen log?</p>';
            } else if (mode === '90_days') {
                title = 'Hapus Log > 90 Hari?';
                htmlText = '<div style="text-align: left; background: rgba(59, 130, 246, 0.16); border: 1.5px solid rgba(59, 130, 246, 0.45); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-size: 0.88rem; line-height: 1.5;">'
                         + 'Semua data log yang lebih lama dari <strong style="color: #93c5fd;">90 hari yang lalu</strong> akan dihapus permanen dari database.'
                         + '</div>'
                         + '<div style="color: #94a3b8; font-size: 0.78rem; margin-top: 8px; display: flex; align-items: center; gap: 5px;">'
                         + '<i class="bi bi-shield-check text-success"></i> <span style="color: #cbd5e1;">Log 3 bulan terakhir tetap tersimpan aman.</span>'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0;">Lanjutkan penghapusan permanen log?</p>';
            } else if (mode === 'custom_range') {
                const s = document.getElementById('start_date').value;
                const e = document.getElementById('end_date').value;
                if (!s || !e) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Rentang Tanggal',
                            text: 'Harap tentukan Tanggal Mulai dan Tanggal Selesai terlebih dahulu.',
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: 'Mengerti',
                            customClass: { popup: 'prune-swal-popup' }
                        });
                    } else {
                        alert('Harap tentukan Tanggal Mulai dan Tanggal Selesai.');
                    }
                    return;
                }
                title = 'Hapus Rentang Tanggal?';
                htmlText = '<div style="text-align: left; background: rgba(59, 130, 246, 0.16); border: 1.5px solid rgba(59, 130, 246, 0.45); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-size: 0.88rem; line-height: 1.5;">'
                         + 'Semua data log aktivitas dari tanggal <strong style="color: #93c5fd;">' + s + '</strong> sampai <strong style="color: #93c5fd;">' + e + '</strong> akan dihapus permanen dari sistem.'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0;">Lanjutkan penghapusan permanen log?</p>';
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    html: htmlText,
                    icon: 'warning',
                    iconColor: '#f87171',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#475569',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1.5"></i> Ya, Hapus Permanen',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    focusCancel: true,
                    backdrop: `rgba(15, 23, 42, 0.8)`,
                    customClass: {
                        popup: 'prune-swal-popup',
                        confirmButton: 'rounded-pill px-4 py-2.5 fw-semibold shadow-sm',
                        cancelButton: 'rounded-pill px-4 py-2.5 fw-semibold'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sedang Membersihkan Log...',
                            html: '<span style="color: #cbd5e1;">Mohon tunggu, proses penghapusan permanen sedang berjalan di database.</span>',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: { popup: 'prune-swal-popup' },
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        document.getElementById('formPruneLogs').submit();
                    }
                });
            } else {
                if (confirm(title)) {
                    document.getElementById('formPruneLogs').submit();
                }
            }
        }
    </script>
    @endpush

</x-layouts>
