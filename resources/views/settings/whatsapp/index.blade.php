<x-layouts title="WhatsApp Gateway - ">

    @push('styles')
    <!-- Tabler Core CSS -->
    <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet" />
    <style>
        /* Tabler Accent Integration */
        :root {
            --tblr-primary: #206bc4;
        }

        .whatsapp-card {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--tblr-border-color, #e6e7e9);
            background: var(--tblr-card-bg, #ffffff);
            transition: all 0.25s ease;
        }

        .whatsapp-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        [data-bs-theme="dark"] .whatsapp-card {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.08);
            background: #1b2434;
        }

        .copy-btn {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .copy-btn:hover {
            background-color: var(--tblr-bg-surface-secondary, #f1f5f9) !important;
        }

        [data-bs-theme="dark"] .copy-btn:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .wa-illustration-container {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
        }

        .wa-illustration {
            max-width: 100%;
            height: auto;
            max-height: 180px;
        }

        /* QR Code Scanner Placeholder styling */
        .qr-placeholder-wrapper {
            width: 220px;
            height: 220px;
            border: 2px dashed var(--tblr-border-color, #cbd5e1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tblr-bg-surface-secondary, #f8fafc);
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .qr-placeholder-wrapper {
            background: rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .qr-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            padding: 8px;
        }

        .qr-placeholder-icon {
            color: var(--tblr-text-muted, #64748b);
        }

        .form-switch .form-check-input {
            width: 2.5rem;
            height: 1.25rem;
            cursor: pointer;
        }

        .tracking-wider {
            letter-spacing: 0.05em;
        }

        /* Custom spacing adjustments for premium ERP panel feel */
        .card-header-title {
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--tblr-text-muted, #64748b);
        }

        .badge-status {
            padding: 0.35em 0.8em;
            font-weight: 600;
            font-size: 0.72rem;
            border-radius: 50px;
        }

        /* ===== DARK MODE OVERRIDES ===== */
        [data-bs-theme="dark"] .text-dark,
        html[data-theme="dark"] .text-dark,
        .dark-mode .text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted,
        html[data-theme="dark"] .text-muted,
        .dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .text-body,
        html[data-theme="dark"] .text-body,
        .dark-mode .text-body {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .bg-body,
        html[data-theme="dark"] .bg-body,
        .dark-mode .bg-body {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .card-header-title,
        html[data-theme="dark"] .card-header-title,
        .dark-mode .card-header-title {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-control.bg-light,
        html[data-theme="dark"] .form-control.bg-light,
        .dark-mode .form-control.bg-light {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table,
        html[data-theme="dark"] .table,
        .dark-mode .table {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .table thead th,
        html[data-theme="dark"] .table thead th,
        .dark-mode .table thead th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table tbody tr td,
        html[data-theme="dark"] .table tbody tr td,
        .dark-mode .table tbody tr td {
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > *,
        html[data-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #151e2e !important;
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) > *,
        html[data-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) > * {
            background-color: #1b2434 !important;
            color: #cbd5e1 !important;
        }

        /* ─── Prune Modal Modern High-End Styling ─── */
        #modalPruneWaLogs .modal-content {
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            background: #0f172a !important;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.9) !important;
            border-radius: 22px !important;
        }
        [data-bs-theme="light"] #modalPruneWaLogs .modal-content,
        html[data-theme="light"] #modalPruneWaLogs .modal-content {
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
        [data-bs-theme="light"] .prune-warning-box,
        html[data-theme="light"] .prune-warning-box {
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
        [data-bs-theme="light"] .prune-option-card,
        html[data-theme="light"] .prune-option-card {
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
        [data-bs-theme="light"] .prune-option-title,
        html[data-theme="light"] .prune-option-title {
            color: #0f172a !important;
        }

        .prune-option-desc {
            color: #cbd5e1 !important;
            font-size: 0.78rem !important;
            margin-top: 2px;
            font-weight: 400;
        }
        [data-bs-theme="light"] .prune-option-desc,
        html[data-theme="light"] .prune-option-desc {
            color: #64748b !important;
        }

        .prune-danger-title {
            color: #fca5a5 !important;
            font-size: 0.9rem !important;
            font-weight: 700;
        }
        [data-bs-theme="light"] .prune-danger-title,
        html[data-theme="light"] .prune-danger-title {
            color: #dc2626 !important;
        }
        .prune-danger-sub {
            color: #fecaca !important;
            font-size: 0.78rem !important;
            margin-top: 2px;
            opacity: 0.95;
        }
        [data-bs-theme="light"] .prune-danger-sub,
        html[data-theme="light"] .prune-danger-sub {
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
        [data-bs-theme="light"] .prune-swal-popup,
        html[data-theme="light"] .prune-swal-popup {
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
        [data-bs-theme="light"] .prune-swal-popup .swal2-title,
        html[data-theme="light"] .prune-swal-popup .swal2-title {
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
    </style>
    @endpush

    <div class="container-fluid py-4 px-3 px-md-4">
        <!-- TOP HEADER -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-success-lt rounded-3 text-success">
                    <!-- SVG: Brand Whatsapp -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="36" height="36" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                        <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                    </svg>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-dark">WhatsApp Gateway</h3>
                    <p class="text-muted mb-0 small">Connect your ERP with Fonnte WhatsApp Gateway.</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <div class="border px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2 bg-body">
                    <i class="bi bi-building text-primary fs-5"></i>
                    <div>
                        <small class="text-muted d-block lh-1" style="font-size: 0.7rem;">Pengaturan Perusahaan (PT Active)</small>
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ session('active_company_name') ?: (auth()->user()->company->nama_perusahaan ?? 'PT Akuntansi ERP') }}</span>
                    </div>
                </div>
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

        <div class="row g-4">
            <!-- LEFT COLUMN -->
            <div class="col-12 col-lg-8">
                <!-- SECTION 1: CONNECTION STATUS -->
                <div class="card whatsapp-card mb-4">
                    <div class="card-body py-4">
                        <div class="row align-items-center g-4">
                            <div class="col-12 col-md-7">
                                <h6 class="card-header-title mb-3">Status Koneksi Perangkat</h6>
                                <div class="mb-4">
                                    <span class="badge-status {{ $status === 'Connected' ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }} d-inline-flex align-items-center gap-1.5" id="lblConnectionStatus">
                                        <span class="status-dot {{ $status === 'Connected' ? 'status-green' : 'status-red' }} status-dot-animated"></span>
                                        {{ $status === 'Connected' ? 'Connected' : 'Disconnected' }}
                                    </span>
                                </div>
                                <div class="space-y-2 mt-3">
                                    <div class="row small">
                                        <div class="col-5 text-muted">Nomor Terhubung:</div>
                                        <div class="col-7 fw-semibold text-body" id="lblConnectedPhone">{{ $phoneNumber }}</div>
                                    </div>
                                    <div class="row small">
                                        <div class="col-5 text-muted">Nama Perangkat:</div>
                                        <div class="col-7 fw-semibold text-body" id="lblConnectedDevice">{{ $deviceName }}</div>
                                    </div>
                                    <div class="row small">
                                        <div class="col-5 text-muted">Terakhir Sinkron:</div>
                                        <div class="col-7 fw-semibold text-body" id="lblConnectedTime">{{ $lastConnected }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5 wa-illustration-container d-none d-md-flex">
                                <!-- PREMIUM SVG ILLUSTRATION -->
                                <svg class="wa-illustration" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="100" cy="100" r="80" fill="#206bc4" fill-opacity="0.04"/>
                                    <circle cx="100" cy="100" r="60" fill="#206bc4" fill-opacity="0.08"/>
                                    <rect x="70" y="30" width="60" height="110" rx="10" fill="#1b2434" stroke="#64748b" stroke-width="2.5"/>
                                    <rect x="75" y="40" width="50" height="85" rx="4" fill="#0f172a"/>
                                    <circle cx="100" cy="133" r="3.5" fill="#64748b"/>
                                    <rect x="90" y="34" width="20" height="2" rx="1" fill="#64748b"/>
                                    <g filter="drop-shadow(0 4px 6px rgba(0,0,0,0.1))">
                                        <circle cx="125" cy="70" r="24" fill="#25d366"/>
                                        <path d="M125 58c-6.6 0-12 5.4-12 12 0 2.1.5 4.1 1.6 5.8l-1.6 6.1 6.2-1.6c1.6.9 3.5 1.4 5.8 1.4 6.6 0 12-5.4 12-12s-5.4-12-12-12zm6.2 17.1c-.2.7-1.4 1.3-2 1.4-.5.1-1.2.2-3.5-.8-2.3-.9-3.7-3.1-3.8-3.3-.1-.2-.8-1.1-.8-2 0-.9.5-1.4.7-1.6.2-.2.5-.3.6-.3.1 0 .3 0 .5.1.2 0 .4-.1.5.3.2.4.8 1.8.9 2 .1.2.1.3 0 .5-.1.2-.2.3-.2.5-.1.2-.2.3-.4.5-.2.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.1 1 2.1 1.3 2.4 1.5.3.1.5.1.7-.2.2-.2.9-1.1 1.1-1.4.2-.4.5-.3.8-.2.3.1 1.9.9 2.2 1.1.3.1.5.2.6.3.1.2.1.8-.2 1.5z" fill="white"/>
                                    </g>
                                    <path d="M135 120c8 0 12-4 12-12" stroke="#25d366" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="2 3"/>
                                    <path d="M143 120c12 0 20-8 20-20" stroke="#25d366" stroke-width="2.5" stroke-linecap="round" stroke-dasharray="2 3"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: API CONFIGURATION -->
                <div class="card whatsapp-card mb-4">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Konfigurasi API Gateway</h6>
                        <form id="formWhatsAppSettings">
                            @csrf
                            <input type="hidden" name="form_type" value="api_config">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Provider / Layanan</label>
                                    <select class="form-select" name="wa_provider" id="wa_provider">
                                        <option value="Fonnte" {{ $provider === 'Fonnte' ? 'selected' : '' }}>Fonnte WhatsApp Gateway</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">API Token</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="wa_api_token" id="wa_api_token" placeholder="Masukkan API Token Fonnte..." value="{{ $apiToken }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleTokenVisibility()">
                                            <!-- SVG: Eye -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" id="eyeIcon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                            </svg>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" title="Copy Token" onclick="copyTokenToClipboard()">
                                            <!-- SVG: Copy -->
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                                                <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 mt-4 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <!-- SVG: Device Floppy -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
                                        </svg>
                                        Simpan Konfigurasi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- SECTION 4: WEBHOOK CONFIGURATION -->
                <div class="card whatsapp-card mb-4">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Konfigurasi Webhook & API URL</h6>
                        <div class="space-y-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Webhook URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-muted" id="urlWebhook" readonly value="{{ url('/api/whatsapp/webhook') }}">
                                    <button class="btn btn-outline-secondary copy-btn" type="button" onclick="copyInputText('urlWebhook')">
                                        <!-- SVG: Copy -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Send Endpoint</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-muted" id="urlSendEndpoint" readonly value="https://api.fonnte.com/send">
                                    <button class="btn btn-outline-secondary copy-btn" type="button" onclick="copyInputText('urlSendEndpoint')">
                                        <!-- SVG: Copy -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Callback URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-muted" id="urlCallback" readonly value="{{ url('/api/whatsapp/callback') }}">
                                    <button class="btn btn-outline-secondary copy-btn" type="button" onclick="copyInputText('urlCallback')">
                                        <!-- SVG: Copy -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-12 col-lg-4">
                <!-- SECTION 3: QR CODE PAIRING -->
                <div class="card whatsapp-card mb-4 text-center">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Pemindaian QR Code Perangkat</h6>
                        
                        <div class="qr-placeholder-wrapper mb-3" id="qrWrapper">
                            <div class="text-center p-3" id="qrTextPlaceholder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-qrcode qr-placeholder-icon" width="64" height="64" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                    <path d="M14 4m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                    <path d="M4 14m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                    <path d="M14 14l0 .01" />
                                    <path d="M18 14l0 .01" />
                                    <path d="M14 18l0 .01" />
                                    <path d="M18 18l0 .01" />
                                    <path d="M14 20l0 .01" />
                                    <path d="M18 20l0 .01" />
                                    <path d="M20 14l0 .01" />
                                    <path d="M20 18l0 .01" />
                                    <path d="M20 20l0 .01" />
                                </svg>
                                <p class="small text-muted mt-2">Klik tombol di bawah untuk menghasilkan QR Code</p>
                            </div>
                            <img src="" alt="Pairing QR Code" class="qr-image d-none" id="qrImageElement">
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-warning-lt text-warning fw-semibold px-3 py-1.5" id="lblQrStatus">
                                Waiting for Scan...
                            </span>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-outline-primary rounded-pill px-3" type="button" id="btnRefreshQr" onclick="loadPairingQr()">
                                <!-- SVG: Refresh -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -5v5h5" />
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 5v-5h-5" />
                                </svg>
                                Generate QR Code
                            </button>
                            <button class="btn btn-outline-danger rounded-pill px-3" type="button" id="btnDisconnectQr" onclick="disconnectDevice()">
                                <!-- SVG: Power -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-power" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M7 6a7.75 7.75 0 1 0 10 0" />
                                    <path d="M12 4l0 8" />
                                </svg>
                                Reset Koneksi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: NOTIFICATION SETTINGS -->
                <div class="card whatsapp-card mb-4">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Notifikasi & Peringatan Otomatis</h6>
                        <form id="formNotificationSettings">
                            @csrf
                            <input type="hidden" name="form_type" value="notifications">
                            <div class="space-y-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wa_enable_notification" id="wa_enable_notification" value="1" {{ $enableNotification == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 fw-semibold" for="wa_enable_notification">
                                        Aktifkan WhatsApp Gateway
                                        <small class="form-text text-muted d-block font-weight-normal mt-0.5">Aktifkan seluruh fungsi pengiriman notifikasi via WA.</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wa_enable_approval" id="wa_enable_approval" value="1" {{ $enableApproval == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 fw-semibold" for="wa_enable_approval">
                                        Notifikasi Persetujuan (Approval)
                                        <small class="form-text text-muted d-block font-weight-normal mt-0.5">Kirim notifikasi ke supervisor saat ada dokumen PO/PR baru yang membutuhkan persetujuan.</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wa_enable_purchase" id="wa_enable_purchase" value="1" {{ $enablePurchase == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 fw-semibold" for="wa_enable_purchase">
                                        Notifikasi Pembelian (Purchase)
                                        <small class="form-text text-muted d-block font-weight-normal mt-0.5">Kirim pesan otomatis ke supplier saat Purchase Order (PO) diterbitkan.</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wa_enable_stock_alert" id="wa_enable_stock_alert" value="1" {{ $enableStockAlert == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 fw-semibold" for="wa_enable_stock_alert">
                                        Peringatan Stok Kritis (Stock Alert)
                                        <small class="form-text text-muted d-block font-weight-normal mt-0.5">Kirim peringatan harian jika ada barang yang jumlahnya di bawah minimum stok.</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wa_enable_reminder" id="wa_enable_reminder" value="1" {{ $enableReminder == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 fw-semibold" for="wa_enable_reminder">
                                        Reminder Pembayaran / Tagihan
                                        <small class="form-text text-muted d-block font-weight-normal mt-0.5">Kirim reminder otomatis ke customer terkait invoice jatuh tempo.</small>
                                    </label>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        Simpan Notifikasi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <!-- SECTION 6: SEND TEST MESSAGE -->
            <div class="col-12 col-lg-5">
                <div class="card whatsapp-card mb-4 h-100">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Kirim Pesan Uji Coba</h6>
                        <form id="formSendTestMessage">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nomor Penerima</label>
                                <input type="text" class="form-control" name="phone_number" id="testPhone" placeholder="Contoh: 628123456789" required>
                                <div class="form-text text-muted">Gunakan kode negara (misal 62) di depan nomor telepon.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Isi Pesan</label>
                                <textarea class="form-control" name="message" id="testMessage" rows="4" placeholder="Ketik isi pesan uji coba di sini..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-outline-success rounded-pill px-4">
                                    <!-- SVG: Send -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-telegram me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" />
                                    </svg>
                                    Kirim Pesan Test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SECTION 7: ACTIVITY LOG -->
            <div class="col-12 col-lg-7">
                <div class="card whatsapp-card mb-4 h-100" id="logActivityContainer">
                    <div class="card-body py-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                            <div>
                                <h6 class="card-header-title mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    <span>Log Aktivitas Pengiriman</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                                        {{ number_format($logs->total()) }} Log
                                    </span>
                                </h6>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @can('manage-whatsapp-gateway')
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalPruneWaLogs" style="font-size: 0.78rem;">
                                    <i class="bi bi-trash3-fill"></i>
                                    <span>Bersihkan Log</span>
                                </button>
                                @endcan
                            </div>
                        </div>

                        <!-- Search & Filter Toolbar -->
                        <form action="{{ route('settings.whatsapp.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-3" id="formFilterLog">
                            <!-- Search Input -->
                            <div class="input-group input-group-sm flex-grow-1" style="min-width: 160px;">
                                <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-pill ps-3 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search_log" id="searchLogInput" value="{{ $searchLog ?? '' }}" 
                                       class="form-control bg-body-tertiary border-start-0 pe-3" 
                                       placeholder="Cari aksi, penerima, respon..." style="font-size: 0.8rem;"
                                       onkeyup="liveFilterLogs(this.value)">
                            </div>

                            <!-- Filter Status -->
                            <select name="status_log" class="form-select form-select-sm rounded-pill" style="width: auto; min-width: 110px; font-size: 0.8rem;" onchange="this.form.submit();">
                                <option value="">Semua Status</option>
                                <option value="Success" {{ ($statusLog ?? '') === 'Success' ? 'selected' : '' }}>Success</option>
                                <option value="Failed" {{ ($statusLog ?? '') === 'Failed' ? 'selected' : '' }}>Failed</option>
                                <option value="Pending" {{ ($statusLog ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            </select>

                            <!-- Per Page (Default 10) -->
                            <select name="per_page_log" class="form-select form-select-sm rounded-pill" style="width: auto; font-size: 0.8rem;" onchange="this.form.submit();" title="Jumlah data per halaman">
                                <option value="10" {{ ($perPageLog ?? 10) == 10 ? 'selected' : '' }}>10 / hal</option>
                                <option value="25" {{ ($perPageLog ?? 10) == 25 ? 'selected' : '' }}>25 / hal</option>
                                <option value="50" {{ ($perPageLog ?? 10) == 50 ? 'selected' : '' }}>50 / hal</option>
                                <option value="100" {{ ($perPageLog ?? 10) == 100 ? 'selected' : '' }}>100 / hal</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>

                            @if(!empty($searchLog) || !empty($statusLog) || ($perPageLog ?? 10) != 10)
                                <a href="{{ route('settings.whatsapp.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 shadow-xs" title="Reset Pencarian">
                                    <i class="bi bi-x-circle me-1"></i> Reset
                                </a>
                            @endif
                        </form>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-vcenter card-table table-striped table-hover small mb-0" id="tableWhatsappLog">
                                <thead>
                                    <tr>
                                        <th class="py-2.5">Waktu</th>
                                        <th class="py-2.5">Event / Aksi</th>
                                        <th class="py-2.5">Penerima</th>
                                        <th class="py-2.5 text-center">Status</th>
                                        <th class="py-2.5">Respon API</th>
                                    </tr>
                                </thead>
                                <tbody id="logTableBody">
                                    @forelse($logs as $log)
                                        <tr class="log-row">
                                            <td class="text-nowrap" style="font-size: 0.76rem;">
                                                <i class="bi bi-clock me-1 text-muted"></i>
                                                {{ $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="fw-semibold text-body-emphasis log-action">{{ $log->action }}</td>
                                            <td class="text-nowrap log-recipient font-monospace" style="font-size: 0.78rem;">{{ $log->recipient }}</td>
                                            <td class="text-center">
                                                @if($log->status === 'Success')
                                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 font-monospace" 
                                                          style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.72rem;">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span>Success</span>
                                                    </span>
                                                @elseif($log->status === 'Failed')
                                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 font-monospace" 
                                                          style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35); font-size: 0.72rem;">
                                                        <i class="bi bi-x-circle-fill"></i>
                                                        <span>Failed</span>
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 font-monospace" 
                                                          style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35); font-size: 0.72rem;">
                                                        <i class="bi bi-hourglass-split"></i>
                                                        <span>Pending</span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="text-truncate d-inline-block font-monospace log-response" style="max-width: 140px; font-size: 0.74rem;" title="{{ $log->response }}">{{ $log->response }}</code>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-2 d-block text-secondary opacity-50 mb-1"></i>
                                                Belum ada riwayat aktivitas pengiriman.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Footer -->
                        <div class="mt-3 pt-2 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                            <span class="text-muted small" style="font-size: 0.76rem;">
                                Menampilkan <b>{{ $logs->firstItem() ?? 0 }}</b> - <b>{{ $logs->lastItem() ?? 0 }}</b> dari <b>{{ number_format($logs->total()) }}</b> log
                            </span>
                            <div>
                                {{ $logs->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bersihkan / Hapus Log WhatsApp Permanen -->
    <div class="modal fade" id="modalPruneWaLogs" tabindex="-1" aria-labelledby="modalPruneWaLogsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form action="{{ route('settings.whatsapp.prune') }}" method="POST" id="formPruneWaLogs">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-bottom border-light-subtle pb-3 px-4 pt-3.5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="prune-header-icon flex-shrink-0">
                                <i class="bi bi-trash3-fill fs-5 text-white"></i>
                            </div>
                            <div>
                                <h6 class="modal-title fw-bold text-body-emphasis mb-0" id="modalPruneWaLogsLabel">Bersihkan Log Pengiriman WhatsApp</h6>
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
                                <strong style="color: #fca5a5;">Peringatan:</strong> Data riwayat log pengiriman WhatsApp yang dipilih akan dihapus secara <strong>permanen</strong> dari database untuk menghemat ruang penyimpanan server. Pesan yang sudah terkirim ke nomor penerima tidak terpengaruh.
                            </div>
                        </div>

                        <label class="form-label fw-bold text-body-emphasis small mb-2.5">Pilih Kriteria Pembersihan:</label>
                        <div class="d-flex flex-column gap-2 mb-3">
                            <!-- Opsi 30 Hari -->
                            <label class="form-check-label d-flex align-items-center justify-content-between cursor-pointer prune-option-card">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="waMode30" value="30_days" checked onchange="toggleWaCustomRange(false)">
                                    <div>
                                        <div class="prune-option-title">Lebih lama dari 30 hari yang lalu</div>
                                        <div class="prune-option-desc">Menyimpan riwayat pengiriman 1 bulan terakhir (Disarankan)</div>
                                    </div>
                                </div>
                                <span class="prune-badge-recommend">Rekomendasi</span>
                            </label>

                            <!-- Opsi 60 Hari -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="waMode60" value="60_days" onchange="toggleWaCustomRange(false)">
                                <div>
                                    <div class="prune-option-title">Lebih lama dari 60 hari yang lalu</div>
                                    <div class="prune-option-desc">Menyimpan riwayat pengiriman 2 bulan terakhir</div>
                                </div>
                            </label>

                            <!-- Opsi 90 Hari -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="waMode90" value="90_days" onchange="toggleWaCustomRange(false)">
                                <div>
                                    <div class="prune-option-title">Lebih lama dari 90 hari yang lalu</div>
                                    <div class="prune-option-desc">Menyimpan riwayat pengiriman 3 bulan terakhir</div>
                                </div>
                            </label>

                            <!-- Opsi Custom Range -->
                            <label class="form-check-label d-flex align-items-center gap-2.5 cursor-pointer prune-option-card">
                                <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="waModeCustom" value="custom_range" onchange="toggleWaCustomRange(true)">
                                <div>
                                    <div class="prune-option-title">Pilih Rentang Tanggal Spesifik</div>
                                    <div class="prune-option-desc">Hapus log dari tanggal tertentu sampai tanggal tertentu</div>
                                </div>
                            </label>

                            <!-- Form Input Custom Range (Hidden by default) -->
                            <div id="waCustomRangeFields" class="p-3 bg-body-tertiary border rounded-3 d-none">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="wa_start_date" class="form-label small fw-semibold mb-1 text-body-emphasis">Dari Tanggal:</label>
                                        <input type="date" class="form-control form-control-sm rounded-3" name="start_date" id="wa_start_date" max="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="wa_end_date" class="form-label small fw-semibold mb-1 text-body-emphasis">Sampai Tanggal:</label>
                                        <input type="date" class="form-control form-control-sm rounded-3" name="end_date" id="wa_end_date" max="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Opsi Semua Log -->
                            <label class="form-check-label d-flex align-items-center justify-content-between cursor-pointer prune-option-card card-danger">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input class="form-check-input flex-shrink-0 m-0" type="radio" name="prune_mode" id="waModeAll" value="all" onchange="toggleWaCustomRange(false)">
                                    <div>
                                        <div class="prune-danger-title">Kosongkan Semua Log (Reset Total)</div>
                                        <div class="prune-danger-sub">Menghapus seluruh rekaman log pengiriman tanpa terkecuali</div>
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
                        <button type="button" onclick="handlePruneWaSubmit()" class="btn btn-danger btn-sm rounded-pill px-4 py-1.5 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border: none;">
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
        let qrPollInterval = null;

        $(document).ready(function() {
            // Intercept formWhatsAppSettings submit
            $('#formWhatsAppSettings').on('submit', function(e) {
                e.preventDefault();
                saveConfig($(this), '{{ route("settings.whatsapp.save") }}');
            });

            // Intercept formNotificationSettings submit
            $('#formNotificationSettings').on('submit', function(e) {
                e.preventDefault();
                saveConfig($(this), '{{ route("settings.whatsapp.save") }}');
            });

            // Intercept formSendTestMessage submit
            $('#formSendTestMessage').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Mengirim...');

                $.ajax({
                    url: '{{ route("settings.whatsapp.test") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-telegram me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" /></svg>Kirim Pesan Test');
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim!',
                            text: res.message,
                            timer: 2500,
                            showConfirmButton: false
                        });

                        form.find('input[name="phone_number"]').val('');
                        form.find('textarea[name="message"]').val('');
                        
                        // Reload activity logs
                        reloadActivityLogs();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-telegram me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" /></svg>Kirim Pesan Test');
                        
                        let msg = 'Terjadi kesalahan saat mengirim pesan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim!',
                            text: msg
                        });
                        
                        reloadActivityLogs();
                    }
                });
            });
        });

        // Helper: Save general config forms
        function saveConfig(formElement, url) {
            let btn = formElement.find('button[type="submit"]');
            let originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...');

            $.ajax({
                url: url,
                method: 'POST',
                data: formElement.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html(originalText);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Reload connection details dynamically if we saved the API settings form
                    if (formElement.attr('id') === 'formWhatsAppSettings') {
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    let msg = 'Gagal menyimpan pengaturan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                }
            });
        }

        // Helper: Show/Hide API Token
        function toggleTokenVisibility() {
            let input = $('#wa_api_token');
            let eyeIcon = $('#eyeIcon');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                eyeIcon.html('<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M3 3l18 18" />');
            } else {
                input.attr('type', 'password');
                eyeIcon.html('<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />');
            }
        }

        // Helper: Copy input text to clipboard
        function copyInputText(elementId) {
            let input = document.getElementById(elementId);
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(input.value);

            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'Teks berhasil disalin ke clipboard.',
                timer: 1200,
                showConfirmButton: false
            });
        }

        // Helper: Copy Token to clipboard
        function copyTokenToClipboard() {
            let tokenVal = $('#wa_api_token').val();
            if (!tokenVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kosong!',
                    text: 'API Token belum diisi.',
                    timer: 1500,
                    showConfirmButton: false
                });
                return;
            }
            navigator.clipboard.writeText(tokenVal);
            Swal.fire({
                icon: 'success',
                title: 'Disalin!',
                text: 'API Token berhasil disalin.',
                timer: 1200,
                showConfirmButton: false
            });
        }

        // Action: Load pairing QR Code from Fonnte
        function loadPairingQr() {
            let btn = $('#btnRefreshQr');
            let originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memuat...');

            $('#lblQrStatus').removeClass('bg-success-lt text-success bg-warning-lt text-warning').addClass('bg-warning-lt text-warning').text('Generating QR Code...');

            $.ajax({
                url: '{{ route("settings.whatsapp.refresh-qr") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    btn.prop('disabled', false).html(originalText);

                    if (res.status === 'Connected') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Terhubung!',
                            text: res.message
                        });
                        $('#lblQrStatus').removeClass('bg-warning-lt text-warning').addClass('bg-success-lt text-success').text('Connected');
                        $('#qrImageElement').addClass('d-none');
                        $('#qrTextPlaceholder').removeClass('d-none');
                        location.reload();
                    } else if (res.qr_url) {
                        $('#qrTextPlaceholder').addClass('d-none');
                        $('#qrImageElement').removeClass('d-none').attr('src', res.qr_url);
                        $('#lblQrStatus').text('Scan to Connect...');
                        
                        // Start polling connection status every 10 seconds (avoids rate limits)
                        if (qrPollInterval) clearInterval(qrPollInterval);
                        qrPollInterval = setInterval(checkConnectionStatus, 10000);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalText);
                    let msg = 'Gagal memuat QR Code. Pastikan API Token sudah valid.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                    $('#lblQrStatus').removeClass('bg-success-lt text-success').addClass('bg-danger-lt text-danger').text('Failed to Generate');
                }
            });
        }

        // Live client-side instant filtering on current rows
        function liveFilterLogs(query) {
            let term = query.toLowerCase().trim();
            $('#logTableBody tr.log-row').each(function() {
                let rowText = $(this).text().toLowerCase();
                if (!term || rowText.indexOf(term) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Helper: Reload activity logs table
        function reloadActivityLogs() {
            let currentUrl = new URL(window.location.href);
            $.ajax({
                url: currentUrl.toString(),
                method: 'GET',
                success: function(html) {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let newContainer = doc.getElementById('logActivityContainer');
                    if (newContainer) {
                        $('#logActivityContainer').html(newContainer.innerHTML);
                    }
                }
            });
        }

        // Action: Disconnect / Reset Device connection
        function disconnectDevice() {
            let btn = $('#btnDisconnectQr');
            let originalText = btn.html();
            
            Swal.fire({
                title: 'Putuskan Koneksi?',
                text: "Koneksi WhatsApp Gateway Anda dengan Fonnte akan diputus dan di-reset.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Putuskan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Resetting...');
                    
                    $.ajax({
                        url: '{{ route("settings.whatsapp.disconnect") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            btn.prop('disabled', false).html(originalText);
                            if (qrPollInterval) clearInterval(qrPollInterval);
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reset UI elements immediately
                            $('#lblConnectionStatus').removeClass('bg-green-lt text-green').addClass('bg-red-lt text-red').html('<span class="status-dot status-red"></span>Disconnected');
                            $('#lblConnectedPhone').text('-');
                            $('#lblConnectedDevice').text('-');
                            $('#lblConnectedTime').text('-');
                            $('#qrImageElement').addClass('d-none');
                            $('#qrTextPlaceholder').removeClass('d-none');
                            $('#lblQrStatus').removeClass('bg-success-lt text-success bg-danger-lt text-danger').addClass('bg-warning-lt text-warning').text('Waiting for Scan...');
                            
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(originalText);
                            let msg = 'Gagal memutus koneksi perangkat.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: msg
                            });
                        }
                    });
                }
            });
        }

        // Action: Check device connection status programmatically (Polling)
        function checkConnectionStatus() {
            $.ajax({
                url: '{{ route("settings.whatsapp.status") }}',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'Connected') {
                        if (qrPollInterval) clearInterval(qrPollInterval);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhubung!',
                            text: 'WhatsApp Gateway berhasil tersambung!',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#lblConnectionStatus').removeClass('bg-red-lt text-red').addClass('bg-green-lt text-green').html('<span class="status-dot status-green"></span>Connected');
                        $('#lblQrStatus').removeClass('bg-warning-lt text-warning').addClass('bg-success-lt text-success').text('Connected');
                        
                        setTimeout(function() {
                            location.reload();
                        }, 1200);
                    }
                }
            });
        }

        // Toggle custom date range inputs in WhatsApp Log Prune modal
        function toggleWaCustomRange(show) {
            const container = document.getElementById('waCustomRangeFields');
            const startDate = document.getElementById('wa_start_date');
            const endDate = document.getElementById('wa_end_date');
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

        // Confirm and submit WhatsApp delivery log pruning
        function handlePruneWaSubmit() {
            const mode = document.querySelector('input[name="prune_mode"]:checked').value;
            let title = 'Konfirmasi Pembersihan Log WhatsApp';
            let htmlText = '';

            if (mode === 'all') {
                title = 'Kosongkan Semua Log?';
                htmlText = '<div style="text-align: left; background: rgba(239, 68, 68, 0.18); border: 1.5px solid rgba(239, 68, 68, 0.55); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;">'
                         + '<div style="color: #ffffff; font-weight: 700; font-size: 0.88rem; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">'
                         + '<span style="background: #ef4444; color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; letter-spacing: 0.3px;">PERINGATAN</span> <span style="color: #fca5a5;">Tindakan Permanen!</span>'
                         + '</div>'
                         + '<div style="color: #fecaca; font-size: 0.82rem; line-height: 1.5;">'
                         + 'Anda akan menghapus <strong>SELURUH</strong> riwayat log pengiriman WhatsApp tanpa terkecuali. Data tidak dapat dipulihkan kembali.'
                         + '</div>'
                         + '</div>'
                         + '<p style="color: #e2e8f0; font-size: 0.86rem; margin: 0; font-weight: 500;">Apakah Anda yakin ingin mengosongkan seluruh log WhatsApp?</p>';
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
                const s = document.getElementById('wa_start_date').value;
                const e = document.getElementById('wa_end_date').value;
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
                         + 'Semua data log pengiriman WhatsApp dari tanggal <strong style="color: #93c5fd;">' + s + '</strong> sampai <strong style="color: #93c5fd;">' + e + '</strong> akan dihapus permanen dari sistem.'
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
                        document.getElementById('formPruneWaLogs').submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus log ini secara permanen?')) {
                    document.getElementById('formPruneWaLogs').submit();
                }
            }
        }
    </script>
    @endpush
</x-layouts>
