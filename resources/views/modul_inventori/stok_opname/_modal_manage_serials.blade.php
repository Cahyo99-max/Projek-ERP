{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Verifikasi Fisik Serial Number - Stock Opname         --}}
{{-- Ultra-Performance (Virtualized Chunking) & Modern High-Tech  --}}
{{-- 100% Responsive Mobile & Desktop (Anti-Collapse Layout)      --}}
{{-- ════════════════════════════════════════════════════════════ --}}

<style>
/* ─── Ultra-Modern & Responsive SaaS Design (Dark & Light Mode) ─── */
:root {
    --modal-sn-bg: #ffffff;
    --modal-sn-card-bg: #f8fafc;
    --modal-sn-border: #e2e8f0;
    --modal-sn-border-focus: #3b82f6;
    --modal-sn-text: #0f172a;
    --modal-sn-text-muted: #64748b;
    --modal-sn-item-bg: #ffffff;
    --modal-sn-item-hover: #f1f5f9;
    --modal-sn-item-border: #e2e8f0;
    --modal-sn-item-active-bg: #ecfdf5;
    --modal-sn-item-active-border: #10b981;
    --modal-sn-toolbar: #ffffff;
    --modal-sn-sticky: #f8fafc;
    --modal-sn-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.15);
}

[data-bs-theme="dark"] {
    --modal-sn-bg: #0b1329;
    --modal-sn-card-bg: #111e38;
    --modal-sn-border: #1e293b;
    --modal-sn-border-focus: #60a5fa;
    --modal-sn-text: #f8fafc;
    --modal-sn-text-muted: #94a3b8;
    --modal-sn-item-bg: #132240;
    --modal-sn-item-hover: #1a2c52;
    --modal-sn-item-border: #243556;
    --modal-sn-item-active-bg: rgba(16, 185, 129, 0.15);
    --modal-sn-item-active-border: #10b981;
    --modal-sn-toolbar: #111e38;
    --modal-sn-sticky: #0d172c;
    --modal-sn-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
}

/* Modal Frame */
.sn-modern-modal-content {
    background-color: var(--modal-sn-bg) !important;
    color: var(--modal-sn-text) !important;
    border: 1px solid var(--modal-sn-border) !important;
    border-radius: 1.5rem !important;
    box-shadow: var(--modal-sn-shadow) !important;
    overflow: hidden;
    transition: all 0.25s ease;
}

/* Header */
.sn-modern-header {
    background: linear-gradient(135deg, #091a36 0%, #112a58 60%, #1e3a8a 100%);
    position: relative;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(59, 130, 246, 0.25);
}
@media (min-width: 576px) {
    .sn-modern-header {
        padding: 1.25rem 1.75rem;
    }
}
.sn-modern-header::before {
    content: '';
    position: absolute;
    top: -50px; right: -30px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, transparent 70%);
    pointer-events: none;
}
.sn-modern-header::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.6), rgba(16, 185, 129, 0.6), transparent);
}

.sn-header-icon-box {
    width: 42px; height: 42px;
    background: rgba(15, 23, 42, 0.55);
    border: 1.5px solid rgba(56, 189, 248, 0.4);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
    color: #38bdf8;
}
@media (min-width: 576px) {
    .sn-header-icon-box {
        width: 48px; height: 48px;
        border-radius: 14px;
    }
}

.sn-header-close-btn {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.35);
    border-radius: 9999px;
    padding: 0.4rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
    cursor: pointer;
    flex-shrink: 0;
}
@media (min-width: 576px) {
    .sn-header-close-btn {
        padding: 0.45rem 1rem;
        font-size: 0.82rem;
    }
}
.sn-header-close-btn:hover {
    background: #ef4444;
    color: #ffffff;
    border-color: #ef4444;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
}

/* Modal Body: Natural Block Flow to prevent flex-shrink zero collapse */
.sn-modern-modal-body {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    max-height: calc(85vh - 120px);
    display: block !important;
}
.sn-section-spacing {
    margin-bottom: 1rem;
}
@media (min-width: 576px) {
    .sn-section-spacing {
        margin-bottom: 1.25rem;
    }
}

/* Bento Hero Card */
.sn-bento-hero {
    background: var(--modal-sn-card-bg);
    border: 1px solid var(--modal-sn-border);
    border-radius: 1.25rem;
    padding: 1rem 1.15rem;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
    width: 100%;
}
@media (min-width: 576px) {
    .sn-bento-hero {
        padding: 1.15rem 1.4rem;
    }
}

/* Metric Capsules (Fintech/SaaS style) */
.sn-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.6rem;
    width: 100%;
}
@media (min-width: 992px) {
    .sn-metric-grid {
        width: auto;
        min-width: 380px;
        gap: 0.75rem;
    }
}

.sn-metric-capsule {
    background: var(--modal-sn-item-bg);
    border: 1px solid var(--modal-sn-border);
    border-radius: 0.85rem;
    padding: 0.55rem 0.65rem;
    text-align: center;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
@media (min-width: 576px) {
    .sn-metric-capsule {
        border-radius: 1rem;
        padding: 0.65rem 0.9rem;
    }
}
.sn-metric-capsule:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
}
.sn-metric-capsule.metric-sistem {
    border-left: 3px solid #3b82f6;
}
.sn-metric-capsule.metric-verified {
    border-left: 3px solid #10b981;
    background: rgba(16, 185, 129, 0.04);
}
.sn-metric-capsule.metric-selisih {
    border-left: 3px solid #f59e0b;
}

/* Scanner HUD Panel */
.sn-scanner-panel {
    background: var(--modal-sn-toolbar);
    border: 1px solid var(--modal-sn-border);
    border-radius: 1.25rem;
    padding: 1rem 1.15rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    width: 100%;
}
@media (min-width: 576px) {
    .sn-scanner-panel {
        padding: 1.15rem 1.4rem;
    }
}

.sn-scan-input-group {
    position: relative;
    display: flex;
    align-items: stretch;
    width: 100%;
}
.sn-scan-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #3b82f6;
    z-index: 4;
    font-size: 1.15rem;
    pointer-events: none;
}
.sn-terminal-input {
    background: var(--modal-sn-item-bg) !important;
    color: var(--modal-sn-text) !important;
    border: 1.5px solid var(--modal-sn-border) !important;
    border-radius: 12px !important;
    height: 46px;
    padding-left: 42px !important;
    padding-right: 110px !important;
    font-size: 0.88rem;
    transition: all 0.25s ease;
}
@media (min-width: 576px) {
    .sn-terminal-input {
        border-radius: 14px !important;
        height: 48px;
        padding-left: 44px !important;
        padding-right: 125px !important;
        font-size: 0.92rem;
    }
}
.sn-terminal-input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2) !important;
}
.sn-btn-inline-submit {
    position: absolute;
    right: 5px;
    top: 5px;
    bottom: 5px;
    z-index: 5;
    border-radius: 10px !important;
    padding: 0 1rem;
    font-weight: 600;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    color: white;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
}
@media (min-width: 576px) {
    .sn-btn-inline-submit {
        padding: 0 1.25rem;
        font-size: 0.85rem;
    }
}
.sn-btn-inline-submit:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    color: white;
}

/* Quick Action Buttons */
.sn-action-pill-btn {
    border-radius: 9999px;
    padding: 0.45rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.2s ease;
}

/* ══════════════════════════════════════════════════════════════ */
/* Grid & Cards Container (Guaranteed Visible on All Devices)    */
/* ══════════════════════════════════════════════════════════════ */
.sn-card-grid {
    background: var(--modal-sn-card-bg);
    border: 1px solid var(--modal-sn-border);
    border-radius: 1.25rem;
    display: block !important;
    width: 100% !important;
    position: relative;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    margin-bottom: 1rem;
}
.sn-sticky-filter {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--modal-sn-sticky);
    border-bottom: 1px solid var(--modal-sn-border);
    padding: 0.75rem 1rem;
    border-radius: 1.25rem 1.25rem 0 0;
}
@media (min-width: 576px) {
    .sn-sticky-filter {
        padding: 0.85rem 1.25rem;
    }
}

.sn-scroll-viewport {
    max-height: 420px;
    min-height: 180px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    padding: 0.85rem;
}
@media (min-width: 576px) {
    .sn-scroll-viewport {
        max-height: 440px;
        min-height: 220px;
        padding: 1rem;
    }
}

/* Interactive Hardware-Asset Card */
.sn-card-box {
    background: var(--modal-sn-item-bg);
    border: 1.5px solid var(--modal-sn-item-border);
    color: var(--modal-sn-text);
    border-radius: 0.85rem;
    padding: 0.75rem 0.9rem;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    min-height: 48px; /* Ergonomic touch target */
}
@media (min-width: 576px) {
    .sn-card-box {
        border-radius: 1rem;
        padding: 0.85rem 1rem;
        gap: 0.75rem;
    }
}
.sn-card-box:hover {
    background: var(--modal-sn-item-hover);
    border-color: #94a3b8;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
}
.sn-card-box:active {
    transform: scale(0.98);
}
.sn-card-box.is-verified {
    background: var(--modal-sn-item-active-bg) !important;
    border-color: var(--modal-sn-item-active-border) !important;
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.16) !important;
}

/* Custom Check Indicator */
.sn-custom-check {
    width: 22px; height: 22px;
    border-radius: 6px;
    border: 2px solid #cbd5e1;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
    font-size: 0.85rem;
    background: transparent;
}
@media (min-width: 576px) {
    .sn-custom-check {
        width: 24px; height: 24px;
        border-radius: 8px;
        font-size: 0.9rem;
    }
}
.sn-card-box.is-verified .sn-custom-check {
    background: #10b981;
    border-color: #10b981;
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
}
.sn-card-box:not(.is-verified) .sn-custom-check {
    color: transparent;
}
[data-bs-theme="dark"] .sn-card-box:not(.is-verified) .sn-custom-check {
    border-color: #475569;
}

/* Status Badges */
.sn-badge-verified {
    background: rgba(16, 185, 129, 0.16) !important;
    color: #059669 !important;
    border: 1px solid rgba(16, 185, 129, 0.35) !important;
    font-weight: 700;
    font-size: 0.72rem;
    letter-spacing: 0.4px;
    border-radius: 9999px;
    padding: 0.25rem 0.55rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
[data-bs-theme="dark"] .sn-badge-verified {
    background: rgba(16, 185, 129, 0.22) !important;
    color: #34d399 !important;
}

.sn-badge-unverified {
    background: rgba(100, 116, 139, 0.12) !important;
    color: #64748b !important;
    border: 1px solid rgba(100, 116, 139, 0.25) !important;
    font-weight: 600;
    font-size: 0.72rem;
    letter-spacing: 0.3px;
    border-radius: 9999px;
    padding: 0.25rem 0.55rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
[data-bs-theme="dark"] .sn-badge-unverified {
    background: rgba(148, 163, 184, 0.12) !important;
    color: #94a3b8 !important;
    border-color: rgba(148, 163, 184, 0.25) !important;
}

/* Custom Scrollbar */
.sn-scroll-viewport::-webkit-scrollbar {
    width: 6px;
}
.sn-scroll-viewport::-webkit-scrollbar-track {
    background: transparent;
}
.sn-scroll-viewport::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
[data-bs-theme="dark"] .sn-scroll-viewport::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>

<div class="modal fade" id="modalManageSerialsOpname" tabindex="-1" aria-labelledby="modalManageSerialsOpnameLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1075 !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1120px; width: 95vw; z-index: 1076 !important;">
        <div class="modal-content sn-modern-modal-content border-0">
            
            {{-- Header (100% Anti-Truncate on Mobile) --}}
            <div class="modal-header sn-modern-header text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 gap-sm-3 position-relative z-1 flex-grow-1 overflow-hidden me-2">
                    <div class="sn-header-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                            <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                            <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                            <path d="M3 17v2a2 2 0 0 1 2 2h2"></path>
                            <line x1="7" y1="12" x2="17" y2="12"></line>
                            <rect x="7" y="7" width="10" height="10" rx="1"></rect>
                        </svg>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-1.5 gap-sm-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white text-truncate fs-6" id="modalManageSerialsOpnameLabel">Verifikasi Fisik Serial Number</h5>
                            <span class="badge bg-white text-primary border border-white rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px;">⚡ LIVE OPNAME</span>
                        </div>
                        <div class="text-white-50 small mt-0.5 d-none d-sm-block" style="font-size: 0.78rem;">
                            Cek fisik dan scan nomor seri yang nyata ditemukan di rak gudang.
                        </div>
                    </div>
                </div>
                <button type="button" class="sn-header-close-btn position-relative z-1" data-bs-dismiss="modal" title="Tutup Modal">
                    <i class="bi bi-x-lg"></i> Tutup
                </button>
            </div>

            {{-- Modal Body: Block Layout (No Flexbox Shrink Trap) --}}
            <div class="modal-body p-2.5 p-sm-3.5 p-md-4 sn-modern-modal-body">
                
                {{-- 1. Hero Bento Box: Info Barang & Metrik --}}
                <div class="sn-bento-hero sn-section-spacing">
                    <div class="row g-2.5 g-sm-3 align-items-center">
                        <div class="col-12 col-lg-6">
                            <div class="d-flex align-items-center gap-2.5 gap-sm-3">
                                <div class="rounded-4 d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                    <i class="bi bi-box-seam-fill fs-4"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.8px;">Barang yang Dihitung:</div>
                                    <h5 class="fw-bold mb-0.5 text-truncate fs-6" id="opname_sn_barang_nama" style="color: var(--modal-sn-text);">-</h5>
                                    <div class="d-flex align-items-center gap-1.5 gap-sm-2 flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5" id="opname_sn_barang_kode" style="font-size: 0.72rem;">-</span>
                                        <span class="text-muted small" style="font-size: 0.75rem;"><i class="bi bi-geo-alt-fill me-1 text-danger"></i><span id="opname_sn_gudang_name">-</span></span>
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill font-monospace px-1.5 py-0.5" style="font-size: 0.65rem;">Pelacakan SN</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 pt-2 pt-lg-0 border-top border-top-lg-0 d-flex justify-content-lg-end">
                            <div class="sn-metric-grid">
                                <div class="sn-metric-capsule metric-sistem">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.62rem;">Stok Sistem</small>
                                    <span class="fw-bold font-monospace fs-5 text-primary" id="opname_sn_sistem_qty">0</span>
                                </div>
                                <div class="sn-metric-capsule metric-verified">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.62rem;">Fisik Ada</small>
                                    <span class="fw-bold font-monospace fs-5 text-success" id="opname_sn_verified_qty">0</span>
                                </div>
                                <div class="sn-metric-capsule metric-selisih">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.62rem;">Selisih</small>
                                    <span class="fw-bold font-monospace fs-5" id="opname_sn_selisih_qty">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Toolbar Scan & Aksi Cepat --}}
                <div class="sn-scanner-panel sn-section-spacing">
                    <div class="row g-2.5 align-items-center">
                        <div class="col-12 col-xl-7">
                            <label class="form-label small fw-bold mb-1.5 d-flex align-items-center gap-1.5" style="color: var(--modal-sn-text);">
                                <i class="bi bi-qr-code-scan text-primary"></i>Ketik / Scan QR Code & Barcode Serial:
                            </label>
                            <div class="sn-scan-input-group">
                                <span class="sn-scan-input-icon"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" id="opname_sn_scan_input" class="form-control font-monospace sn-terminal-input" placeholder="Scan QR / Barcode atau ketik serial..." autocomplete="off">
                                <button type="button" class="btn sn-btn-inline-submit shadow-xs" id="btn_opname_sn_manual_add" onclick="submitOpnameSnScanInput()">
                                    <i class="bi bi-check-circle-fill"></i> Verifikasi <kbd class="bg-white bg-opacity-25 text-white border-0 px-1 py-0.5 rounded small d-none d-sm-inline" style="font-size: 0.65rem;">↵</kbd>
                                </button>
                            </div>
                            <small class="text-muted mt-1.5 d-block" style="font-size: 0.72rem;">Mendukung scanner fisik USB/Bluetooth, kamera barcode/QR, atau ketik serial manual.</small>
                        </div>
                        <div class="col-12 col-xl-5 text-xl-end d-flex justify-content-start justify-content-xl-end gap-2 flex-wrap align-items-end pt-1">
                            <button type="button" class="btn btn-sm btn-outline-success sn-action-pill-btn shadow-xs" onclick="opnameVerifikasiSemuaSn()" title="Centang semua serial yang ada di sistem">
                                <i class="bi bi-check-all fs-5 text-success"></i>Verifikasi Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary sn-action-pill-btn shadow-xs" onclick="opnameResetSemuaSn()" title="Reset semua centangan menjadi belum ada">
                                <i class="bi bi-arrow-counterclockwise fs-6"></i>Reset Centang
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 3. Card & Grid Serial Numbers (Guaranteed Visible on All Devices) --}}
                <div class="sn-card-grid sn-section-spacing" id="section_daftar_serial_unit">
                    
                    {{-- Sticky Filter Header (Responsive Mobile Stacking) --}}
                    <div class="sn-sticky-filter d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <span class="fw-bold small d-flex align-items-center gap-1.5" style="color: var(--modal-sn-text);">
                                <i class="bi bi-grid-3x3-gap-fill text-primary"></i>Daftar Serial Unit:
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1" id="badge_opname_sn_counter">
                                0 / 0 Unit
                            </span>
                        </div>
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-md-auto">
                            <div class="input-group input-group-sm shadow-xs flex-grow-1" style="min-width: 170px;">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="opname_sn_search_filter" placeholder="Cari nomor seri..." oninput="filterOpnameSnListDebounced()" style="background: var(--modal-sn-item-bg); color: var(--modal-sn-text); border-color: var(--modal-sn-border);">
                                <button type="button" class="btn btn-sm btn-outline-secondary border-start-0 d-none" id="btn_clear_sn_search" onclick="clearOpnameSnSearch()">
                                    <i class="bi bi-x fs-6"></i>
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm shadow-xs w-100 w-sm-auto flex-shrink-0" role="group">
                                <button type="button" class="btn btn-outline-secondary active btn-filter-sn-tab fw-semibold px-2.5" data-filter="all" onclick="filterOpnameSnTab('all', this)">Semua</button>
                                <button type="button" class="btn btn-outline-success btn-filter-sn-tab fw-semibold px-2.5" data-filter="verified" onclick="filterOpnameSnTab('verified', this)">Ada (<span id="tab_count_verified">0</span>)</button>
                                <button type="button" class="btn btn-outline-danger btn-filter-sn-tab fw-semibold px-2.5" data-filter="missing" onclick="filterOpnameSnTab('missing', this)">Belum (<span id="tab_count_missing">0</span>)</button>
                            </div>
                        </div>
                    </div>

                    {{-- Scrollable Virtual Grid Container --}}
                    <div class="sn-scroll-viewport" id="opname_sn_scroll_container">
                        <div class="row g-2 g-sm-2.5" id="opname_sn_cards_container">
                            {{-- Injected dynamically by High-Performance Virtual Chunker --}}
                        </div>

                        {{-- Load More Sentinel for Infinite Scroll --}}
                        <div id="opname_sn_load_more_wrapper" class="text-center py-3 d-none">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold shadow-xs" onclick="renderNextSnChunk()" id="btn_load_more_sn">
                                <i class="bi bi-chevron-double-down me-1"></i>Muat Lebih Banyak Unit (<span id="load_more_sn_count">0</span> tersisa)
                            </button>
                        </div>

                        {{-- Empty State --}}
                        <div id="opname_sn_empty_placeholder" class="text-center py-5 d-none">
                            <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-10 rounded-circle mb-3 text-muted" style="width: 58px; height: 58px;">
                                <i class="bi bi-inbox fs-2"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="color: var(--modal-sn-text);">Tidak Ada Nomor Seri</h6>
                            <p class="text-muted small mb-0" id="opname_sn_empty_text">Tidak ada nomor seri yang cocok dengan kata kunci pencarian atau filter tab.</p>
                        </div>
                    </div>

                    {{-- Footer Info strip --}}
                    <div class="px-3 py-2 border-top small d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem; background: var(--modal-sn-sticky); border-color: var(--modal-sn-border) !important; border-radius: 0 0 1.25rem 1.25rem;">
                        <span id="opname_sn_render_info">Menampilkan 0 unit</span>
                        <span><i class="bi bi-lightning-charge-fill text-warning me-1"></i>High-Velocity In-Memory Index</span>
                    </div>
                </div>

                {{-- 4. Alert Info Kebijakan Opname --}}
                <div class="alert border shadow-xs py-2.5 px-3 mb-0 small d-flex align-items-center gap-2.5 text-secondary rounded-4" style="background: var(--modal-sn-card-bg); border-color: var(--modal-sn-border) !important;">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                        <i class="bi bi-shield-check fs-6"></i>
                    </div>
                    <div>
                        <strong>Catatan Opname:</strong> Nomor seri yang <strong>tercentang (hijau)</strong> diakui sebagai stok fisik riil di gudang. Nomor seri yang <strong>tidak tercentang</strong> saat Closing Opname akan otomatis direkonsiliasi menjadi status <code>Hilang (Selisih Kurang)</code>.
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer px-3 px-sm-4 py-3 border-top d-flex justify-content-between align-items-center" style="background-color: var(--modal-sn-card-bg); border-color: var(--modal-sn-border) !important;">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-md d-inline-flex align-items-center gap-2" onclick="terapkanSerialOpnameKeFisik()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                    <i class="bi bi-check2-circle fs-5"></i> Simpan & Terapkan Hitungan Fisik
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ══════════════════════════════════════════════════════════════════
    // ULTRA-PERFORMANCE SERIAL NUMBER OPNAME ENGINE
    // - O(1) Set Verification & Fast Filtering
    // - Virtualized Chunked Lazy Rendering (Scales to 100k+ with 0 lag)
    // - Modern Interactive Cards with Custom Toggle Indicators
    // ══════════════════════════════════════════════════════════════════

    let activeOpnameSnBarangId = null;
    let opnameSnList = [];             // Master list: [{ serial: string, is_system: boolean }]
    let opnameVerifiedSet = new Set();  // Set for instantaneous O(1) status lookup
    let filteredSnList = [];           // Filtered indices / items for current search/tab
    let activeOpnameSnFilter = 'all';  // 'all' | 'verified' | 'missing'
    let currentRenderedIndex = 0;      // Virtualization pointer
    const SN_CHUNK_SIZE = 60;          // Instant DOM slice
    let searchDebounceTimer = null;

    // Helper: Safe HTML Escaping
    if (typeof window.escapeHtml !== 'function') {
        window.escapeHtml = function(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };
    }
    window.escapeHtmlAdj = window.escapeHtml;

    // Helper: Smart QR / Barcode String Parser
    if (typeof window.parseQrOrBarcodeString !== 'function') {
        window.parseQrOrBarcodeString = function(str) {
            str = (str || '').toString().trim();
            let sn = null;
            let kode = null;

            if (str.includes('sn=')) {
                let m = str.match(/[?&]sn=([^&]+)/i);
                if (m) sn = decodeURIComponent(m[1].trim());
            }

            if (!sn) {
                let m = str.match(/SN\s*:\s*(.+?)(?=(?:(?:KODE|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
                if (m) sn = m[1].trim();
            }

            let mKode = str.match(/KODE\s*:\s*(.+?)(?=(?:(?:SN|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i);
            if (mKode) kode = mKode[1].trim();

            if (!sn) {
                let m = str.match(/^SN\s*[:\-\s]\s*(.+)$/i);
                if (m) sn = m[1].trim();
            }

            return { sn: sn, kode: kode, raw: str };
        };
    }

    // Helper: Audio beeps
    function playScannerBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } catch(e) {}
    }

    function playWarningBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(320, ctx.currentTime);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.start();
            osc.stop(ctx.currentTime + 0.2);
        } catch(e) {}
    }

    // ──────────────────────────────────────────────────────────────────
    // 1. OPEN MODAL & FETCH DATA
    // ──────────────────────────────────────────────────────────────────
    function openModalManageSerialsOpname(barangId) {
        activeOpnameSnBarangId = barangId;
        const opnameId = $('#input_so_id').val();
        if (!opnameId || !barangId) return;

        // Ambil info barang dari currentOpnameDetails
        let item = currentOpnameDetails.find(d => d.barang_id == barangId);
        if (!item) return;

        $('#opname_sn_barang_nama').text(item.barang_nama);
        $('#opname_sn_barang_kode').text(item.barang_kode);
        $('#opname_sn_gudang_name').text($('#input_so_gudang').text());

        let sisVal = parseFloat(item.stok_sistem || 0);
        let sisFmt = (sisVal % 1 === 0) ? sisVal.toFixed(0) : sisVal.toFixed(2);
        $('#opname_sn_sistem_qty').text(sisFmt);

        // Ambil serial numbers yang saat ini sudah diverifikasi di baris
        let currentVerifiedSns = [];
        let rawSns = $(`#serials_${barangId}`).val();
        if (rawSns) {
            try {
                currentVerifiedSns = Array.isArray(rawSns) ? rawSns : JSON.parse(rawSns);
            } catch(e) {
                currentVerifiedSns = [];
            }
        } else if (item.serial_numbers && Array.isArray(item.serial_numbers)) {
            currentVerifiedSns = item.serial_numbers;
        }

        // Inisialisasi Set O(1)
        opnameVerifiedSet = new Set(currentVerifiedSns);

        // Reset filter & input
        $('#opname_sn_search_filter').val('');
        $('#btn_clear_sn_search').addClass('d-none');
        activeOpnameSnFilter = 'all';
        $('.btn-filter-sn-tab').removeClass('active');
        $('.btn-filter-sn-tab[data-filter="all"]').addClass('active');

        // Tampilkan loading modern di dalam container
        $('#opname_sn_cards_container').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary me-2" role="status" style="width: 2.2rem; height: 2.2rem;"></div>
                <div class="text-muted small mt-2 fw-semibold">Memuat daftar serial number gudang secara instan...</div>
            </div>
        `);
        $('#opname_sn_empty_placeholder').addClass('d-none');
        $('#opname_sn_load_more_wrapper').addClass('d-none');
        $('#modalManageSerialsOpname').modal('show');

        // Fetch serial numbers dari endpoint gudang
        $.ajax({
            url: `/stock-opname/${opnameId}/available-serials`,
            method: 'GET',
            data: { barang_id: barangId },
            timeout: 15000,
            success: function(res) {
                try {
                    let systemSerials = res.serials || [];
                    opnameSnList = [];
                    let systemSerialsSet = new Set(systemSerials);

                    // 1. Masukkan semua serial number sistem
                    systemSerials.forEach(sn => {
                        opnameSnList.push({
                            serial: sn,
                            is_system: true
                        });
                    });

                    // 2. Jika ada SN yang diverifikasi tapi belum tercatat di sistem (kelebihan fisik)
                    currentVerifiedSns.forEach(sn => {
                        if (!systemSerialsSet.has(sn)) {
                            opnameSnList.push({
                                serial: sn,
                                is_system: false
                            });
                        }
                    });

                    // Terapkan filter & render awal
                    applyOpnameSnFilters();
                    updateOpnameSnHeaderStats();

                    setTimeout(() => { $('#opname_sn_scan_input').focus(); }, 350);
                } catch(err) {
                    console.error("Error processing serials:", err);
                    $('#opname_sn_cards_container').html(`
                        <div class="col-12 text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>
                            Gagal memproses data nomor seri: ${window.escapeHtml(err.message)}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                $('#opname_sn_cards_container').html(`
                    <div class="col-12 text-center py-5 text-danger">
                        <i class="bi bi-cloud-slash fs-1 d-block mb-2"></i>
                        <h6 class="fw-bold mb-1">Gagal Memuat Serial Number</h6>
                        <small class="text-muted d-block mb-3">Terjadi kendala koneksi atau sesi berakhir. Silakan coba lagi.</small>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" onclick="openModalManageSerialsOpname(${barangId})">
                            <i class="bi bi-arrow-clockwise me-1"></i>Coba Muat Ulang
                        </button>
                    </div>
                `);
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // 2. VIRTUAL CHUNKED RENDERING ENGINE
    // ──────────────────────────────────────────────────────────────────
    function applyOpnameSnFilters() {
        let rawFilter = ($('#opname_sn_search_filter').val() || '').trim();
        let parsed = window.parseQrOrBarcodeString(rawFilter);
        const query = (parsed.sn || parsed.kode || rawFilter).toLowerCase().trim();

        if (query) {
            $('#btn_clear_sn_search').removeClass('d-none');
        } else {
            $('#btn_clear_sn_search').addClass('d-none');
        }

        // Fast Filter Memory List
        filteredSnList = [];
        for (let i = 0; i < opnameSnList.length; i++) {
            const item = opnameSnList[i];
            const isVerified = opnameVerifiedSet.has(item.serial);

            // Tab filter
            if (activeOpnameSnFilter === 'verified' && !isVerified) continue;
            if (activeOpnameSnFilter === 'missing' && isVerified) continue;

            // Search query
            if (query && !item.serial.toLowerCase().includes(query)) continue;

            filteredSnList.push(item);
        }

        currentRenderedIndex = 0;
        $('#opname_sn_cards_container').empty();

        if (filteredSnList.length === 0) {
            $('#opname_sn_empty_placeholder').removeClass('d-none');
            $('#opname_sn_load_more_wrapper').addClass('d-none');
            $('#opname_sn_render_info').text('Menampilkan 0 unit');
            return;
        }

        $('#opname_sn_empty_placeholder').addClass('d-none');
        renderNextSnChunk();
    }

    function renderNextSnChunk() {
        if (currentRenderedIndex >= filteredSnList.length) {
            $('#opname_sn_load_more_wrapper').addClass('d-none');
            return;
        }

        const nextLimit = Math.min(currentRenderedIndex + SN_CHUNK_SIZE, filteredSnList.length);
        let htmlChunk = '';

        for (let i = currentRenderedIndex; i < nextLimit; i++) {
            const item = filteredSnList[i];
            const isVerified = opnameVerifiedSet.has(item.serial);
            const verifiedClass = isVerified ? 'is-verified' : '';
            const safeSerial = window.escapeHtml(item.serial);
            
            const badgeHtml = isVerified 
                ? '<span class="sn-badge-verified"><i class="bi bi-check-circle-fill"></i>Ada</span>'
                : '<span class="sn-badge-unverified"><i class="bi bi-dash-circle"></i>Belum</span>';

            const tagExtra = !item.is_system 
                ? '<span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning border-opacity-50 font-monospace px-1.5 py-0.5 rounded ms-1" style="font-size:0.65rem;">BARU</span>' 
                : '';

            htmlChunk += `
                <div class="col-12 col-sm-6 col-md-4 col-xl-3" id="col_sn_${i}">
                    <div class="sn-card-box ${verifiedClass}" 
                         data-sn="${safeSerial}" 
                         onclick="toggleOpnameSnCard('${safeSerial}', this)">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="sn-custom-check">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="fw-bold font-monospace d-block text-truncate" style="font-size: 0.85rem;" title="${safeSerial}">
                                    ${safeSerial} ${tagExtra}
                                </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 sn-badge-wrapper">
                            ${badgeHtml}
                        </div>
                    </div>
                </div>
            `;
        }

        currentRenderedIndex = nextLimit;
        $('#opname_sn_cards_container').append(htmlChunk);

        // Update Infinite Scroll Sentinel
        const remaining = filteredSnList.length - currentRenderedIndex;
        if (remaining > 0) {
            $('#load_more_sn_count').text(remaining);
            $('#opname_sn_load_more_wrapper').removeClass('d-none');
        } else {
            $('#opname_sn_load_more_wrapper').addClass('d-none');
        }

        $('#opname_sn_render_info').text(`Menampilkan ${currentRenderedIndex} dari ${filteredSnList.length} unit (${opnameSnList.length} Total Gudang)`);
    }

    // ──────────────────────────────────────────────────────────────────
    // 3. INFINITE SCROLL LISTENER (THROTTLED)
    // ──────────────────────────────────────────────────────────────────
    let isScrollChecking = false;
    $('#opname_sn_scroll_container').on('scroll', function() {
        if (isScrollChecking) return;
        isScrollChecking = true;

        requestAnimationFrame(() => {
            const container = $('#opname_sn_scroll_container')[0];
            if (container.scrollHeight - (container.scrollTop + container.clientHeight) < 120) {
                if (currentRenderedIndex < filteredSnList.length) {
                    renderNextSnChunk();
                }
            }
            isScrollChecking = false;
        });
    });

    // ──────────────────────────────────────────────────────────────────
    // 4. INSTANT TOGGLE SERIAL ITEM (O(1) SPEED)
    // ──────────────────────────────────────────────────────────────────
    function toggleOpnameSnCard(serial, cardElem) {
        const $card = $(cardElem);
        const $badgeWrap = $card.find('.sn-badge-wrapper');

        const nowVerified = !opnameVerifiedSet.has(serial);

        if (nowVerified) {
            opnameVerifiedSet.add(serial);
            $card.addClass('is-verified');
            $badgeWrap.html('<span class="sn-badge-verified"><i class="bi bi-check-circle-fill"></i>Ada</span>');
            playScannerBeep();
        } else {
            opnameVerifiedSet.delete(serial);
            $card.removeClass('is-verified');
            $badgeWrap.html('<span class="sn-badge-unverified"><i class="bi bi-dash-circle"></i>Belum</span>');
        }

        // If active tab is 'verified' or 'missing', refresh filter smoothly
        if (activeOpnameSnFilter !== 'all') {
            $card.closest('[id^="col_sn_"]').fadeOut(150, function() {
                $(this).remove();
            });
        }

        updateOpnameSnHeaderStats();
    }

    // ──────────────────────────────────────────────────────────────────
    // 5. BULK ACTIONS (VERIFIKASI SEMUA & RESET CENTANG)
    // ──────────────────────────────────────────────────────────────────
    function opnameVerifikasiSemuaSn() {
        if (opnameSnList.length === 0) return;

        opnameSnList.forEach(item => {
            opnameVerifiedSet.add(item.serial);
        });

        playScannerBeep();
        applyOpnameSnFilters();
        updateOpnameSnHeaderStats();
    }

    function opnameResetSemuaSn() {
        if (opnameSnList.length === 0) return;

        opnameVerifiedSet.clear();
        applyOpnameSnFilters();
        updateOpnameSnHeaderStats();
    }

    // ──────────────────────────────────────────────────────────────────
    // 6. SUBMIT INPUT SCAN / MANUAL BARCODE
    // ──────────────────────────────────────────────────────────────────
    function submitOpnameSnScanInput() {
        let raw = ($('#opname_sn_scan_input').val() || '').trim();
        if (!raw) return;

        let parsed = window.parseQrOrBarcodeString(raw);
        let clean = parsed.sn || raw;

        let existingItem = opnameSnList.find(item => item.serial.toLowerCase() === clean.toLowerCase());

        if (existingItem) {
            const officialSn = existingItem.serial;
            if (opnameVerifiedSet.has(officialSn)) {
                playWarningBeep();
                Swal.fire({
                    icon: 'info',
                    title: 'Sudah Terverifikasi',
                    html: `Nomor seri <strong class="text-primary font-monospace">${window.escapeHtml(officialSn)}</strong> sudah tercentang fisik!`,
                    timer: 1400,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                opnameVerifiedSet.add(officialSn);
                playScannerBeep();
                applyOpnameSnFilters();
                updateOpnameSnHeaderStats();

                Swal.fire({
                    icon: 'success',
                    title: 'Terverifikasi!',
                    html: `Nomor seri <strong class="text-success font-monospace">${window.escapeHtml(officialSn)}</strong> berhasil dicentang fisik.`,
                    timer: 1200,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } else {
            // SN Baru yang tidak ada di sistem (kelebihan fisik opname)
            opnameSnList.unshift({
                serial: clean,
                is_system: false
            });
            opnameVerifiedSet.add(clean);

            playScannerBeep();
            applyOpnameSnFilters();
            updateOpnameSnHeaderStats();

            Swal.fire({
                icon: 'success',
                title: 'Serial Baru Ditambahkan!',
                html: `Nomor seri <strong class="text-warning font-monospace">${window.escapeHtml(clean)}</strong> ditambahkan sebagai fisik lebih.`,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }

        $('#opname_sn_scan_input').val('').focus();
    }

    $(document).on('keypress', '#opname_sn_scan_input', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            submitOpnameSnScanInput();
        }
    });

    // ──────────────────────────────────────────────────────────────────
    // 7. FILTER & SEARCH HANDLERS
    // ──────────────────────────────────────────────────────────────────
    function filterOpnameSnListDebounced() {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            applyOpnameSnFilters();
        }, 80);
    }

    function clearOpnameSnSearch() {
        $('#opname_sn_search_filter').val('').focus();
        applyOpnameSnFilters();
    }

    function filterOpnameSnTab(filter, btn) {
        activeOpnameSnFilter = filter;
        $('.btn-filter-sn-tab').removeClass('active');
        $(btn).addClass('active');
        applyOpnameSnFilters();
    }

    // ──────────────────────────────────────────────────────────────────
    // 8. UPDATE STATS COUNTER
    // ──────────────────────────────────────────────────────────────────
    function updateOpnameSnHeaderStats() {
        const verifiedCount = opnameVerifiedSet.size;
        const totalCount = opnameSnList.length;
        const missingCount = Math.max(0, totalCount - verifiedCount);

        $('#opname_sn_verified_qty').text(verifiedCount);
        $('#badge_opname_sn_counter').text(`${verifiedCount} / ${totalCount} Unit`);
        $('#tab_count_verified').text(verifiedCount);
        $('#tab_count_missing').text(missingCount);

        const sistemVal = parseFloat($('#opname_sn_sistem_qty').text()) || 0;
        const selisih = verifiedCount - sistemVal;
        let selisihFmt = (selisih > 0) ? `+${selisih}` : `${selisih}`;
        const $lblSelisih = $('#opname_sn_selisih_qty');
        $lblSelisih.text(selisihFmt);

        if (selisih > 0) {
            $lblSelisih.removeClass('text-danger text-dark').addClass('text-success');
        } else if (selisih < 0) {
            $lblSelisih.removeClass('text-success text-dark').addClass('text-danger');
        } else {
            $lblSelisih.removeClass('text-success text-danger').addClass('text-dark');
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // 9. APPLY SERIALS TO OPNAME DETAIL ROW
    // ──────────────────────────────────────────────────────────────────
    function terapkanSerialOpnameKeFisik() {
        if (!activeOpnameSnBarangId) return;

        let verifiedSns = Array.from(opnameVerifiedSet);
        let count = verifiedSns.length;

        // 1. Simpan ke hidden input di tabel baris
        $(`#serials_${activeOpnameSnBarangId}`).val(JSON.stringify(verifiedSns));

        // 2. Set kuantitas fisik baris tepat sama dengan jumlah serial fisik
        $(`#input_fisik_${activeOpnameSnBarangId}`).val(count);
        hitungDiffFisik(activeOpnameSnBarangId);

        // 3. Update data di currentOpnameDetails memory
        let match = currentOpnameDetails.find(d => d.barang_id == activeOpnameSnBarangId);
        if (match) {
            match.serial_numbers = verifiedSns;
            match.stok_fisik = count;
            let sistemVal = parseFloat(match.stok_sistem || 0);
            match.selisih = count - sistemVal;
        }

        // 4. Update tampilan badge tombol Serial
        updateOpnameSnRowBadge(activeOpnameSnBarangId, count, verifiedSns);

        $('#modalManageSerialsOpname').modal('hide');

        Swal.fire({
            icon: 'success',
            title: 'Hitungan Serial Diterapkan!',
            html: `Total <strong>${count} unit</strong> serial number fisik berhasil disimpan untuk barang ini.`,
            timer: 1600,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    function updateOpnameSnRowBadge(barangId, count, snList) {
        let sistemVal = parseFloat($(`#input_sistem_${barangId}`).val()) || 0;
        let sistemFmt = (sistemVal % 1 === 0) ? sistemVal.toFixed(0) : sistemVal.toFixed(2).replace(/\.?0+$/, '');
        let $btn = $(`#btn_sn_${barangId}`);

        if (!$btn.length) return;

        $btn.find('.sn-btn-text').text(`Serial (${count}/${sistemFmt})`);

        $btn.removeClass('btn-outline-info text-info btn-outline-success text-success btn-outline-danger text-danger btn-outline-warning text-dark');

        if (count === sistemVal && sistemVal > 0) {
            $btn.addClass('btn-outline-success text-success');
        } else if (count < sistemVal) {
            $btn.addClass('btn-outline-danger text-danger');
        } else if (count > sistemVal) {
            $btn.addClass('btn-outline-warning text-dark');
        } else {
            $btn.addClass('btn-outline-info text-info');
        }
    }
</script>
@endpush
