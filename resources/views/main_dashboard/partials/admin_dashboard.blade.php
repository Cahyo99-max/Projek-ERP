@push('styles')
<style>
    /* ══════════════════════════════════════════════════════════════════════ */
    /* PIXEL-PERFECT CINEMATIC ADMIN COMMAND CENTER (LIGHT & DARK ADAPTIVE)   */
    /* ══════════════════════════════════════════════════════════════════════ */

    /* Pulse Animations */
    @keyframes pulse-emerald {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .animate-pulse-emerald { animation: pulse-emerald 2s infinite ease-in-out; }

    @keyframes pulse-amber {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    .animate-pulse-amber { animation: pulse-amber 2s infinite ease-in-out; }

    @keyframes pulse-rose {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 9px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .animate-pulse-rose { animation: pulse-rose 1.7s infinite ease-in-out; }

    /* High-Contrast Adaptive Badge System */
    .badge-soft-emerald {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .badge-soft-amber {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fde68a !important;
    }
    .badge-soft-rose {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
        border: 1px solid #fecaca !important;
    }
    .badge-soft-sky {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        border: 1px solid #bae6fd !important;
    }
    .badge-soft-indigo {
        background-color: #e0e7ff !important;
        color: #3730a3 !important;
        border: 1px solid #c7d2fe !important;
    }
    .badge-soft-neutral {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
    }

    [data-bs-theme="dark"] .badge-soft-emerald {
        background-color: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
    }
    [data-bs-theme="dark"] .badge-soft-amber {
        background-color: rgba(245, 158, 11, 0.15) !important;
        color: #fbbf24 !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }
    [data-bs-theme="dark"] .badge-soft-rose {
        background-color: rgba(239, 68, 68, 0.15) !important;
        color: #f87171 !important;
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
    }
    [data-bs-theme="dark"] .badge-soft-sky {
        background-color: rgba(14, 165, 233, 0.15) !important;
        color: #38bdf8 !important;
        border: 1px solid rgba(14, 165, 233, 0.3) !important;
    }
    [data-bs-theme="dark"] .badge-soft-indigo {
        background-color: rgba(99, 102, 241, 0.15) !important;
        color: #818cf8 !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
    }
    [data-bs-theme="dark"] .badge-soft-neutral {
        background-color: rgba(148, 163, 184, 0.15) !important;
        color: #cbd5e1 !important;
        border: 1px solid rgba(148, 163, 184, 0.25) !important;
    }

    /* Main Container Glass Card */
    .cinematic-command-card {
        background: var(--bs-card-bg, #ffffff) !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        border-radius: 22px !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 30px -6px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }
    [data-bs-theme="dark"] .cinematic-command-card {
        background: linear-gradient(145deg, #0f172a 0%, #0b1120 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 20px 45px -15px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    /* Telemetry Box - Modern, spacious, high-contrast */
    .cinematic-telemetry-box {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 136px;
        position: relative;
    }
    .cinematic-telemetry-box:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 12px 26px -6px rgba(15, 23, 42, 0.08);
    }
    [data-bs-theme="dark"] .cinematic-telemetry-box {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.7) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    [data-bs-theme="dark"] .cinematic-telemetry-box:hover {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.88) 100%);
        border-color: rgba(99, 102, 241, 0.4);
        box-shadow: 0 14px 30px -8px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .telemetry-icon-bubble {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    /* Threat Status Banners - Sleek Modern Border & Subtle Tint */
    .threat-banner-safe {
        background: linear-gradient(135deg, rgba(240, 253, 244, 0.95) 0%, rgba(255, 255, 255, 0.95) 100%);
        border: 1px solid #bbf7d0;
        box-shadow: 0 4px 15px -3px rgba(16, 185, 129, 0.08);
    }
    .threat-banner-safe .threat-title { color: #15803d !important; }
    .threat-banner-safe .threat-desc { color: #374151 !important; line-height: 1.55; }

    .threat-banner-warning {
        background: linear-gradient(135deg, rgba(254, 243, 199, 0.85) 0%, rgba(255, 255, 255, 0.95) 100%);
        border: 1px solid #fde68a;
        box-shadow: 0 4px 15px -3px rgba(245, 158, 11, 0.08);
    }
    .threat-banner-warning .threat-title { color: #b45309 !important; }
    .threat-banner-warning .threat-desc { color: #4b5563 !important; line-height: 1.55; }

    .threat-banner-danger {
        background: linear-gradient(135deg, rgba(254, 226, 226, 0.85) 0%, rgba(255, 255, 255, 0.95) 100%);
        border: 1px solid #fecaca;
        box-shadow: 0 4px 15px -3px rgba(239, 68, 68, 0.08);
    }
    .threat-banner-danger .threat-title { color: #b91c1c !important; }
    .threat-banner-danger .threat-desc { color: #4b5563 !important; line-height: 1.55; }

    [data-bs-theme="dark"] .threat-banner-safe {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.09) 0%, rgba(15, 23, 42, 0.55) 100%);
        border: 1px solid rgba(16, 185, 129, 0.28);
        box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    [data-bs-theme="dark"] .threat-banner-safe .threat-title { color: #34d399 !important; }
    [data-bs-theme="dark"] .threat-banner-safe .threat-desc { color: #cbd5e1 !important; line-height: 1.55; }

    [data-bs-theme="dark"] .threat-banner-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.09) 0%, rgba(15, 23, 42, 0.55) 100%);
        border: 1px solid rgba(245, 158, 11, 0.3);
        box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    [data-bs-theme="dark"] .threat-banner-warning .threat-title { color: #fbbf24 !important; }
    [data-bs-theme="dark"] .threat-banner-warning .threat-desc { color: #cbd5e1 !important; line-height: 1.55; }

    [data-bs-theme="dark"] .threat-banner-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(15, 23, 42, 0.55) 100%);
        border: 1px solid rgba(239, 68, 68, 0.35);
        box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    [data-bs-theme="dark"] .threat-banner-danger .threat-title { color: #f87171 !important; }
    [data-bs-theme="dark"] .threat-banner-danger .threat-desc { color: #cbd5e1 !important; line-height: 1.55; }

    /* Incident Item Card */
    .incident-item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.65rem 0.9rem;
    }
    [data-bs-theme="dark"] .incident-item-card {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Custom Sleek Scrollbar for User Activity List */
    .user-activity-list::-webkit-scrollbar {
        width: 5px;
    }
    .user-activity-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .user-activity-list::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.25);
        border-radius: 10px;
    }
    .user-activity-list::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.45);
    }

    .user-activity-item {
        padding: 0.65rem 0.75rem;
        border-radius: 14px;
        transition: all 0.2s ease;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }
    .user-activity-item:hover {
        background: rgba(0, 0, 0, 0.02);
    }
    [data-bs-theme="dark"] .user-activity-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }
    [data-bs-theme="dark"] .user-activity-item:hover {
        background: rgba(255, 255, 255, 0.035);
    }
    .user-activity-item:last-child {
        border-bottom: none;
    }

    .tech-pill {
        font-family: 'JetBrains Mono', 'Fira Code', ui-monospace, SFMono-Regular, monospace;
        letter-spacing: -0.2px;
    }
</style>
@endpush

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- ADMIN / GENERAL DASHBOARD CONTENT                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div class="row g-4 mb-4">
    <!-- Active Users Monitor (col-12 col-xl-4) -->
    @can('lihat-monitor-aktif')
    <div class="col-12 col-xl-4">
        <div class="card p-4 p-md-4.5 h-100 cinematic-command-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="badge-soft-emerald p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-body">Monitor User</h5>
                        <span class="fs-8 text-muted">Sesi aktif real-time</span>
                    </div>
                </div>
                <span id="activeUsersCountBadge" class="badge badge-soft-emerald rounded-pill px-3 py-1.5 fs-8 fw-semibold animate-pulse-emerald">
                    0 Online
                </span>
            </div>
            
            <div id="activeUsersList" class="user-activity-list" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Memuat status user...
                </div>
            </div>
        </div>
    </div>
    @endcan

    <!-- System Health & Security Command Center (col-12 col-xl-8) -->
    <div class="col-12 {{ auth()->user()->can('lihat-monitor-aktif') ? 'col-xl-8' : 'col-xl-12' }}">
        <div class="card p-4 p-md-4.5 h-100 cinematic-command-card">
            
            <!-- Header Toolbar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="badge-soft-indigo p-2.5 rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px;">
                        <i class="bi bi-shield-lock-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-body">System Health & Security Summary</h5>
                            <span class="badge badge-soft-sky fs-9 px-2 py-0.5 rounded-pill tech-pill fw-semibold">TELEMETRY</span>
                        </div>
                        <span class="fs-8 text-muted">Status infrastruktur database, server, & audit proteksi login</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="threatStatusBadge" class="badge badge-soft-emerald rounded-pill px-3 py-1.5 fs-8 fw-semibold shadow-xs">
                        <i class="bi bi-shield-check me-1"></i> Memuat Status...
                    </span>
                    <button id="refreshHealthBtn" class="btn btn-sm btn-outline-secondary px-3 rounded-pill shadow-xs" title="Refresh Live Health">
                        <i class="bi bi-arrow-clockwise me-1"></i> <span class="d-none d-md-inline fs-8 fw-medium">Sync</span>
                    </button>
                </div>
            </div>

            <!-- 3 Unified Telemetry Cards Grid with Generous Spacing -->
            <div class="row g-3.5 g-xl-4 mb-4">
                
                <!-- 1. Database Engine Card -->
                <div class="col-12 col-sm-4">
                    <div class="cinematic-telemetry-box h-100">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="text-secondary fs-8 fw-bold text-uppercase" style="letter-spacing: 0.6px;">Database Engine</span>
                                <span id="dbStatusDot" class="rounded-circle animate-pulse-emerald" style="width: 10px; height: 10px; background-color: #10b981;"></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h6 id="dbStatusText" class="fw-bold mb-0 text-success fs-5">Connected</h6>
                                <span id="dbLatencyText" class="badge badge-soft-emerald fs-9 tech-pill px-2.5 py-1">0.00 ms</span>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top border-secondary border-opacity-10 mt-auto d-flex align-items-center justify-content-between">
                            <span id="dbVersionText" class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1 rounded-pill fs-9 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-database-check text-success"></i> <span>MariaDB Engine</span>
                            </span>
                            <span class="fs-9 text-muted fw-medium">Active</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Security & Login Audit Card -->
                <div class="col-12 col-sm-4">
                    <div class="cinematic-telemetry-box h-100">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="text-secondary fs-8 fw-bold text-uppercase" style="letter-spacing: 0.6px;">Audit Login (24 Jam)</span>
                                <div class="telemetry-icon-bubble badge-soft-indigo" style="width: 28px; height: 28px;">
                                    <i class="bi bi-person-lock fs-7"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h6 class="fw-bold mb-0 text-body fs-5"><span id="successLoginsCount" class="text-success">0</span> Sukses</h6>
                                <span class="text-muted fs-8">/</span>
                                <span id="failedLoginsBadge" class="badge badge-soft-amber fs-8 fw-bold px-2.5 py-1 rounded-pill">0 Gagal</span>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top border-secondary border-opacity-10 mt-auto d-flex align-items-center justify-content-between">
                            <span id="bruteForceAlertSummary" class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1 rounded-pill fs-9 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-shield-check text-success"></i> <span>Semua sukses</span>
                            </span>
                            <span class="fs-9 text-muted fw-medium">24 Jam</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Server Runtime Card (Secured: PHP Version Omitted) -->
                <div class="col-12 col-sm-4">
                    <div class="cinematic-telemetry-box h-100">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="text-secondary fs-8 fw-bold text-uppercase" style="letter-spacing: 0.6px;">Server & Runtime</span>
                                <div class="telemetry-icon-bubble badge-soft-sky" style="width: 28px; height: 28px;">
                                    <i class="bi bi-cpu-fill fs-7"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <h6 id="phpVersionText" class="fw-bold mb-0 text-body fs-5 tech-pill">PHP Engine</h6>
                                <span id="envBadge" class="badge badge-soft-sky fs-9 text-uppercase px-2.5 py-1 tech-pill fw-bold">{{ config('app.env', 'prod') }}</span>
                            </div>
                        </div>
                        <div class="pt-2.5 border-top border-secondary border-opacity-10 mt-auto d-flex align-items-center justify-content-between">
                            <span id="serverMemoryText" class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2.5 py-1 rounded-pill fs-9 fw-semibold d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-cpu text-info"></i> <span>Mem: Memuat...</span>
                            </span>
                            <span class="fs-9 text-muted fw-medium">RAM</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Threat Status Banner & Incident Detail Box with Enhanced Spacing -->
            <div id="securityAlertBox" class="p-3.5 p-md-4 rounded-4 threat-banner-safe mb-0">
                <div class="d-flex align-items-start gap-3.5">
                    <div id="securityAlertIconContainer" class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 42px; height: 42px;">
                        <i id="securityAlertIcon" class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-1.5 gap-2">
                            <h6 id="securityAlertTitle" class="fw-bold mb-0 threat-title fs-7">Sistem Keamanan Normal</h6>
                            <span id="serverClock" class="badge badge-soft-neutral fs-9 tech-pill px-2.5 py-1 align-self-start align-self-sm-auto">
                                <i class="bi bi-clock me-1"></i>{{ now()->format('H:i:s') }} WIB
                            </span>
                        </div>
                        <p id="securityAlertDesc" class="fs-8 threat-desc mb-0">
                            Semua jalur otentikasi beroperasi normal. Tidak ada aktivitas login mencurigakan atau percobaan gagal dalam 24 jam terakhir.
                        </p>

                        <!-- Dynamic Incident Log List -->
                        <div id="failedLoginsListContainer" class="mt-3.5 pt-3 border-top border-secondary border-opacity-15" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fs-8 fw-bold text-danger d-flex align-items-center gap-1.5">
                                    <i class="bi bi-exclamation-octagon-fill"></i> Rincian Percobaan Login Gagal Terakhir:
                                </span>
                            </div>
                            <div id="failedLoginsList" class="d-flex flex-column gap-2">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Log Aktivitas User Widget Row -->
@can('lihat-log-aktivitas-user')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card p-4 border-0 rounded-4 shadow-sm cinematic-command-card">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="badge-soft-indigo p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-body">Log Aktivitas User</h5>
                        <span class="fs-8 text-muted">Riwayat audit autentikasi login & logout</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button id="refreshLogsBtn" class="btn btn-sm btn-outline-secondary px-3 rounded-pill"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-primary px-3 rounded-pill shadow-xs"><i class="bi bi-arrow-right me-1"></i> Lihat Semua</a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="activityLogsTable" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2.5">Waktu</th>
                            <th class="py-2.5">Nama User</th>
                            <th class="py-2.5">Aktivitas</th>
                            <th class="py-2.5">IP Address</th>
                            <th class="py-2.5">Device / Browser</th>
                            <th class="py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody id="activityLogsBody">
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Memuat log aktivitas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
    $(document).ready(function() {
        // Function to load System Health & Security Metrics
        let isCheckingHealth = false;
        function loadSystemHealth() {
            if (isCheckingHealth) return;
            isCheckingHealth = true;

            $.ajax({
                url: "{{ route('dashboard.system-health') }}",
                method: 'GET',
                dataType: 'json',
                timeout: 3000,
                success: function(data) {
                    isCheckingHealth = false;

                    // 1. Database Telemetry
                    if (data.database.status === 'Connected') {
                        $('#dbStatusDot').css('background-color', '#10b981').removeClass('animate-pulse-rose').addClass('animate-pulse-emerald');
                        $('#dbStatusText').text('Connected').removeClass('text-danger').addClass('text-success');
                        $('#dbLatencyText').html('<i class="bi bi-lightning-charge-fill me-0.5"></i> ' + data.database.latency_ms + ' ms')
                                           .attr('class', 'badge badge-soft-emerald fs-9 tech-pill px-2.5 py-1');
                        let dbEngineLabel = (data.database.version || '').toLowerCase();
                        if (dbEngineLabel.includes('mariadb')) {
                            dbEngineLabel = 'MariaDB Engine';
                        } else if (dbEngineLabel.includes('mysql')) {
                            dbEngineLabel = 'MySQL Engine';
                        } else {
                            dbEngineLabel = data.database.version || 'SQL Engine';
                        }
                        $('#dbVersionText').html('<i class="bi bi-database-check text-success"></i> <span>' + dbEngineLabel + '</span>');
                    } else {
                        $('#dbStatusDot').css('background-color', '#ef4444').removeClass('animate-pulse-emerald').addClass('animate-pulse-rose');
                        $('#dbStatusText').text('Disconnected').removeClass('text-success').addClass('text-danger');
                        $('#dbLatencyText').text('Offline')
                                           .attr('class', 'badge badge-soft-rose fs-9 tech-pill px-2.5 py-1');
                        $('#dbVersionText').html('<i class="bi bi-database-slash text-danger"></i> <span class="text-danger fw-semibold">Disconnected</span>');
                    }

                    // 2. Server Telemetry (Secured: Version hidden)
                    $('#phpVersionText').text(data.server.runtime_name || 'PHP Engine');
                    $('#envBadge').text(data.server.environment);
                    $('#serverMemoryText').html('<i class="bi bi-cpu text-info"></i> <span>Mem: ' + data.server.memory_usage_mb + ' MB</span>');
                    $('#serverClock').html('<i class="bi bi-clock me-1"></i>' + data.server.server_time);

                    // 3. Security Telemetry & Threat Banner
                    $('#successLoginsCount').text(data.security.success_logins_24h);
                    
                    let failedCount = data.security.failed_logins_24h;
                    let failedBadge = $('#failedLoginsBadge');
                    failedBadge.text(failedCount + ' Gagal');

                    let alertBox = $('#securityAlertBox');
                    let alertIcon = $('#securityAlertIcon');
                    let alertIconContainer = $('#securityAlertIconContainer');
                    let alertTitle = $('#securityAlertTitle');
                    let alertDesc = $('#securityAlertDesc');
                    let statusBadge = $('#threatStatusBadge');

                    if (data.database.status !== 'Connected') {
                        // DB Disconnected State
                        statusBadge.attr('class', 'badge badge-soft-rose rounded-pill px-3 py-1.5 fs-8 fw-semibold animate-pulse-rose shadow-xs')
                                   .html('<i class="bi bi-exclamation-triangle-fill me-1"></i> Database Offline');
                        failedBadge.attr('class', 'badge badge-soft-neutral fs-8 fw-medium px-2 py-0.5 rounded-pill');
                        $('#bruteForceAlertSummary').html('<i class="bi bi-database-slash text-danger"></i> <span class="text-danger">DB Terputus</span>');

                        alertBox.attr('class', 'p-3.5 p-md-4 rounded-4 threat-banner-danger mb-0');
                        alertIconContainer.attr('class', 'bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs animate-pulse-rose');
                        alertIcon.attr('class', 'bi bi-database-slash fs-5');
                        alertTitle.text('Koneksi Database Terputus (Offline)');
                        alertDesc.html('Layanan database MySQL tidak merespons atau sedang berhenti. Silakan nyalakan kembali service MySQL di server / XAMPP.');
                        $('#failedLoginsListContainer').hide();
                    } else if (data.security.has_brute_force || failedCount >= 5) {
                        // Critical Alert State
                        statusBadge.attr('class', 'badge badge-soft-rose rounded-pill px-3 py-1.5 fs-8 fw-semibold animate-pulse-rose shadow-xs')
                                   .html('<i class="bi bi-shield-slash-fill me-1"></i> Potensi Serangan');
                        failedBadge.attr('class', 'badge badge-soft-rose fs-8 fw-bold px-2 py-0.5 rounded-pill');
                        $('#bruteForceAlertSummary').html('<i class="bi bi-exclamation-triangle-fill text-danger"></i> <span class="text-danger fw-bold">' + failedCount + ' Gagal</span>');

                        alertBox.attr('class', 'p-3.5 p-md-4 rounded-4 threat-banner-danger mb-0');
                        alertIconContainer.attr('class', 'bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs animate-pulse-rose');
                        alertIcon.attr('class', 'bi bi-shield-exclamation fs-5');
                        alertTitle.text('Peringatan: Terdeteksi Anomali Login (24 Jam)');
                        alertDesc.html(`Terdeteksi <strong>${failedCount} kali kegagalan otentikasi login</strong> dalam 24 jam terakhir. Sistem menyarankan verifikasi IP asal pada daftar di bawah.`);
                    } else if (failedCount > 0) {
                        // Warning State
                        statusBadge.attr('class', 'badge badge-soft-amber rounded-pill px-3 py-1.5 fs-8 fw-semibold animate-pulse-amber shadow-xs')
                                   .html('<i class="bi bi-shield-exclamation me-1"></i> ' + failedCount + ' Login Gagal');
                        failedBadge.attr('class', 'badge badge-soft-amber fs-8 fw-bold px-2 py-0.5 rounded-pill');
                        $('#bruteForceAlertSummary').html('<i class="bi bi-info-circle text-warning"></i> <span class="text-warning-emphasis fw-semibold">' + failedCount + ' Gagal</span>');

                        alertBox.attr('class', 'p-3.5 p-md-4 rounded-4 threat-banner-warning mb-0');
                        alertIconContainer.attr('class', 'bg-warning text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs');
                        alertIcon.attr('class', 'bi bi-exclamation-diamond-fill fs-5');
                        alertTitle.text('Catatan Keamanan Operasional');
                        alertDesc.html(`Terdapat <strong>${failedCount} kali kegagalan otentikasi login</strong> dalam 24 jam terakhir (kemungkinan salah ketik kata sandi).`);
                    } else {
                        // Safe State
                        statusBadge.attr('class', 'badge badge-soft-emerald rounded-pill px-3 py-1.5 fs-8 fw-semibold shadow-xs')
                                   .html('<i class="bi bi-shield-check me-1"></i> Sistem Aman & Terlindungi');
                        failedBadge.attr('class', 'badge badge-soft-neutral fs-8 fw-medium px-2 py-0.5 rounded-pill');
                        $('#bruteForceAlertSummary').html('<i class="bi bi-shield-check text-success"></i> <span class="text-success">Semua Sukses</span>');

                        alertBox.attr('class', 'p-3.5 p-md-4 rounded-4 threat-banner-safe mb-0');
                        alertIconContainer.attr('class', 'bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs');
                        alertIcon.attr('class', 'bi bi-shield-check fs-5');
                        alertTitle.text('Sistem Keamanan Normal');
                        alertDesc.text('Semua jalur otentikasi beroperasi normal. Tidak ada aktivitas login mencurigakan atau percobaan gagal dalam 24 jam terakhir.');
                    }

                    // Render Recent Failed Logins
                    if (data.database.status === 'Connected' && data.security.recent_failed && data.security.recent_failed.length > 0) {
                        let failedHtml = '';
                        data.security.recent_failed.forEach(function(item) {
                            failedHtml += `
                                <div class="incident-item-card d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-soft-rose rounded-pill px-2.5 py-0.5 fs-9 fw-bold">FAILED</span>
                                        <span class="fw-bold text-body fs-8"><i class="bi bi-person-x text-danger me-1"></i>${item.username}</span>
                                        <code class="badge-soft-neutral fs-9 px-2 py-0.5 rounded tech-pill">${item.ip}</code>
                                    </div>
                                    <div class="text-muted fs-9 d-flex align-items-center gap-2">
                                        <span title="${item.exact_time}"><i class="bi bi-clock me-1"></i>${item.time}</span>
                                        <span>•</span>
                                        <span class="text-danger fw-semibold fst-italic">${item.message}</span>
                                    </div>
                                </div>
                            `;
                        });
                        $('#failedLoginsList').html(failedHtml);
                        $('#failedLoginsListContainer').slideDown(200);
                    } else {
                        $('#failedLoginsListContainer').hide();
                    }
                },
                error: function() {
                    isCheckingHealth = false;

                    $('#dbStatusDot').css('background-color', '#ef4444').removeClass('animate-pulse-emerald').addClass('animate-pulse-rose');
                    $('#dbStatusText').text('Disconnected').removeClass('text-success').addClass('text-danger');
                    $('#dbLatencyText').text('Offline').attr('class', 'badge badge-soft-rose fs-9 tech-pill px-2.5 py-1');
                    $('#dbVersionText').html('<i class="bi bi-database-slash me-1.5 text-danger"></i><span class="text-danger fw-semibold">Database Disconnected</span>');
                    $('#threatStatusBadge').attr('class', 'badge badge-soft-rose rounded-pill px-3 py-1.5 fs-8 fw-semibold animate-pulse-rose')
                                           .html('<i class="bi bi-exclamation-triangle-fill me-1"></i> Database Offline');
                    
                    $('#securityAlertBox').attr('class', 'p-3.5 p-md-4 rounded-4 threat-banner-danger mb-0');
                    $('#securityAlertIconContainer').attr('class', 'bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs animate-pulse-rose');
                    $('#securityAlertIcon').attr('class', 'bi bi-database-slash fs-5');
                    $('#securityAlertTitle').text('Koneksi Database Terputus (Offline)');
                    $('#securityAlertDesc').html('Layanan database MySQL tidak merespons atau sedang berhenti. Silakan nyalakan kembali service MySQL di server / XAMPP.');
                    $('#failedLoginsListContainer').hide();
                }
            });
        }

        // Function to load activity logs via AJAX
        function loadActivityLogs() {
            if ($('#activityLogsBody').length === 0) return;

            $('#activityLogsBody').html(`
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat log aktivitas...
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('dashboard.activity-logs') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (data.length === 0) {
                        html = `
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada log aktivitas.
                                </td>
                            </tr>
                        `;
                    } else {
                        data.forEach(function(log) {
                            let activityBadge = log.aktivitas === 'Login' 
                                ? `<span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>`
                                : `<span class="badge badge-soft-neutral rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-box-arrow-right me-1"></i> Logout</span>`;
                            
                            let statusBadge = log.status === 'Success'
                                ? `<span class="badge badge-soft-emerald rounded-pill px-3 py-1 fw-semibold fs-8">🟢 Berhasil</span>`
                                : `<span class="badge badge-soft-rose rounded-pill px-3 py-1 fw-semibold fs-8" title="${log.status_message}">🔴 ${log.status_message || 'Gagal'}</span>`;

                            html += `
                                <tr>
                                    <td class="fs-8 text-muted tech-pill">${log.waktu}</td>
                                    <td><span class="fw-semibold text-body fs-8">${log.user}</span></td>
                                    <td>${activityBadge}</td>
                                    <td><code class="badge-soft-neutral fs-9 px-2 py-0.5 rounded tech-pill">${log.ip_address}</code></td>
                                    <td><span class="fs-8 text-muted">${log.device}</span></td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#activityLogsBody').html(html);
                },
                error: function() {
                    $('#activityLogsBody').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-danger fw-semibold">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal memuat log aktivitas. Silakan coba lagi.
                            </td>
                        </tr>
                    `);
                }
            });
        }

        // Function to load active users via AJAX
        function loadActiveUsers() {
            if ($('#activeUsersList').length === 0) return;

            $.ajax({
                url: "{{ route('dashboard.active-users') }}",
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    let onlineCount = 0;
                    
                    if (data.length === 0) {
                        html = `
                            <div class="text-center py-4 text-muted">
                                Tidak ada data user.
                            </div>
                        `;
                    } else {
                        data.forEach(function(user) {
                            if (user.is_online) {
                                onlineCount++;
                            }

                            let avatarHtml = '';
                            if (user.foto) {
                                avatarHtml = `<img src="${user.foto}" alt="${user.nama_lengkap}" class="rounded-circle object-fit-cover shadow-sm" style="width: 42px; height: 42px; box-shadow: 0 0 0 2px var(--bs-card-bg, #111827);">`;
                            } else {
                                let names = user.nama_lengkap.split(' ');
                                let initials = names.map(n => n.substring(0,1)).slice(0,2).join('').toUpperCase();
                                
                                const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'];
                                let colorIdx = user.nama_lengkap.charCodeAt(0) % colors.length;
                                let bgCol = colors[colorIdx];
                                
                                avatarHtml = `
                                    <div class="d-flex align-items-center justify-content-center text-white rounded-circle shadow-sm fw-bold" style="width: 42px; height: 42px; background-color: ${bgCol}; font-size: 0.92rem; box-shadow: 0 0 0 2px var(--bs-card-bg, #111827);">
                                        ${initials}
                                    </div>
                                `;
                            }

                            let statusDotColor = user.is_online ? '#10b981' : '#94a3b8';
                            let dotPulseClass = user.is_online ? 'animate-pulse-emerald' : '';
                            
                            let statusText = user.is_online 
                                ? `<span class="badge badge-soft-emerald fw-semibold fs-9 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-2xs">
                                       <span class="rounded-circle animate-pulse-emerald" style="width: 6px; height: 6px; background-color: #10b981;"></span>
                                       Aktif
                                   </span>
                                   <span class="text-muted fs-9 d-block mt-0.5">Menit ini</span>`
                                : `<span class="badge badge-soft-neutral fw-normal fs-9 rounded-pill px-2.5 py-0.5 d-inline-block">Offline</span>
                                   <span class="text-muted fs-9 text-nowrap d-block mt-0.5" title="${user.last_seen_title}">${user.last_seen_text}</span>`;

                            html += `
                                <div class="user-activity-item d-flex align-items-center justify-content-between mb-1">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            ${avatarHtml}
                                            <span class="position-absolute bottom-0 end-0 rounded-circle ${dotPulseClass}" 
                                                  style="width: 13px; height: 13px; background-color: ${statusDotColor}; box-shadow: 0 0 0 2px var(--bs-card-bg, #111827);">
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-truncate text-body" style="max-width: 145px; font-size: 0.9rem;">${user.nama_lengkap}</h6>
                                            <p class="text-muted mb-0 fs-8">${user.jabatan}</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        ${statusText}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    $('#activeUsersList').html(html);
                    $('#activeUsersCountBadge').text(`${onlineCount} Online`);
                },
                error: function() {
                    $('#activeUsersList').html(`
                        <div class="text-center py-4 text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal memuat data user.
                        </div>
                    `);
                }
            });
        }

        // Initial loads
        loadSystemHealth();

        // 🔄 Real-time polling: Check System Health every 5 seconds
        setInterval(loadSystemHealth, 5000);

        if ($('#activityLogsBody').length > 0) {
            loadActivityLogs();
        }

        if ($('#activeUsersList').length > 0) {
            loadActiveUsers();
            setInterval(loadActiveUsers, 30000);
        }

        // Refresh buttons
        $('#refreshHealthBtn').on('click', function(e) {
            e.preventDefault();
            let btn = $(this);
            let icon = btn.find('i');
            icon.addClass('spinner-border spinner-border-sm').removeClass('bi bi-arrow-clockwise');
            loadSystemHealth();
            setTimeout(() => {
                icon.removeClass('spinner-border spinner-border-sm').addClass('bi bi-arrow-clockwise');
            }, 600);
        });

        $('#refreshLogsBtn').on('click', function(e) {
            e.preventDefault();
            let btn = $(this);
            let icon = btn.find('i');
            icon.addClass('spinner-border spinner-border-sm').removeClass('bi bi-arrow-clockwise');
            loadActivityLogs();
            setTimeout(() => {
                icon.removeClass('spinner-border spinner-border-sm').addClass('bi bi-arrow-clockwise');
            }, 600);
        });
    });
</script>
@endpush
