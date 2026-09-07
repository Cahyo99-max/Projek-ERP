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
                <div class="card whatsapp-card mb-4 h-100">
                    <div class="card-body py-4">
                        <h6 class="card-header-title mb-4">Log Aktivitas Pengiriman</h6>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped table-hover small">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Event / Aksi</th>
                                        <th>Penerima</th>
                                        <th>Status</th>
                                        <th>Respon API</th>
                                    </tr>
                                </thead>
                                <tbody id="logTableBody">
                                    @forelse($logs as $log)
                                        <tr>
                                            <td class="text-nowrap">{{ $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</td>
                                            <td class="fw-semibold">{{ $log->action }}</td>
                                            <td class="text-nowrap">{{ $log->recipient }}</td>
                                            <td>
                                                @if($log->status === 'Success')
                                                    <span class="badge bg-green text-green-fg">Success</span>
                                                @elseif($log->status === 'Failed')
                                                    <span class="badge bg-red text-red-fg">Failed</span>
                                                @else
                                                    <span class="badge bg-orange text-orange-fg">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $log->response }}">{{ $log->response }}</code>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat aktivitas pengiriman.</td>
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

        // Helper: Reload activity logs table
        function reloadActivityLogs() {
            $.ajax({
                url: '{{ route("settings.whatsapp.index") }}',
                method: 'GET',
                success: function(html) {
                    // Extract table body from response
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let newRows = doc.getElementById('logTableBody').innerHTML;
                    $('#logTableBody').html(newRows);
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
    </script>
    @endpush
</x-layouts>
