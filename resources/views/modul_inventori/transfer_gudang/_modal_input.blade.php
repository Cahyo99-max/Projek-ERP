{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Tambah & Edit Transfer Gudang (Warehouse Transfer)  --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@canany(['tambah-transfer', 'edit-transfer'])

<style>
/* ─── Modern Cinematic Theme Tokens for Transfer Gudang ────────────── */
:root {
    --trf-modal-bg: #ffffff;
    --trf-card-bg: #f8fafc;
    --trf-card-border: rgba(226, 232, 240, 0.8);
    --trf-text-main: #0f172a;
    --trf-text-muted: #64748b;
    --trf-input-bg: #ffffff;
    --trf-footer-bg: rgba(255, 255, 255, 0.96);
    --trf-accent-blue: #2563eb;
    --trf-accent-cyan: #06b6d4;
    --trf-accent-amber: #f59e0b;
    --trf-accent-emerald: #10b981;
    --trf-accent-rose: #f43f5e;
}

[data-bs-theme="dark"],
html[data-theme="dark"],
.dark-mode {
    --trf-modal-bg: #0f172a;
    --trf-card-bg: #1e293b;
    --trf-card-border: rgba(255, 255, 255, 0.08);
    --trf-text-main: #f8fafc;
    --trf-text-muted: #94a3b8;
    --trf-input-bg: #1e293b;
    --trf-footer-bg: rgba(15, 23, 42, 0.95);
}

/* Modal Content & Glass Backdrop */
#modalTambahTransfer .modal-content {
    background: var(--trf-modal-bg);
    color: var(--trf-text-main);
    border: 1px solid var(--trf-card-border);
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Cinematic Header */
.trf-modal-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%);
    padding: 1.15rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    position: relative;
}
.trf-header-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Route Node Grid */
.trf-route-grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 0.85rem;
    align-items: center;
}
@media (max-width: 768px) {
    .trf-route-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}

.trf-route-card {
    background: var(--trf-card-bg);
    border: 1px solid var(--trf-card-border);
    border-radius: 1rem;
    padding: 1rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    cursor: pointer;
}
.trf-route-card:hover {
    transform: translateY(-2px);
    border-color: rgba(37, 99, 235, 0.4);
    box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.12);
}
.trf-route-card.active-asal {
    border-left: 4px solid var(--trf-accent-rose);
}
.trf-route-card.active-tujuan {
    border-left: 4px solid var(--trf-accent-emerald);
}

.trf-route-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Route Center Connector */
.trf-route-connector {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}
@media (max-width: 768px) {
    .trf-route-connector {
        padding: 0.25rem 0;
    }
}

/* Form Input Enhancements */
.trf-form-card {
    background: var(--trf-card-bg);
    border: 1px solid var(--trf-card-border);
    border-radius: 1rem;
    padding: 1.15rem;
}

#modalTambahTransfer .form-control,
#modalTambahTransfer .form-select {
    border-radius: 0.75rem;
    padding: 0.6rem 0.85rem;
    font-size: 0.9rem;
    transition: all 0.15s ease;
}
[data-bs-theme="dark"] #modalTambahTransfer .form-control,
[data-bs-theme="dark"] #modalTambahTransfer .form-select,
html[data-theme="dark"] #modalTambahTransfer .form-control,
html[data-theme="dark"] #modalTambahTransfer .form-select,
.dark-mode #modalTambahTransfer .form-control,
.dark-mode #modalTambahTransfer .form-select {
    background-color: rgba(30, 41, 59, 0.9) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}

/* Item Card (Cinematic Mobile Cards) */
.trf-item-card {
    background: var(--trf-card-bg);
    border: 1px solid var(--trf-card-border);
    border-radius: 1rem;
    padding: 1rem;
    margin-bottom: 0.85rem;
    transition: all 0.2s ease;
    position: relative;
}
.trf-item-card:hover {
    border-color: rgba(37, 99, 235, 0.35);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.trf-item-num-badge {
    background: rgba(37, 99, 235, 0.12);
    color: var(--trf-accent-blue);
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 0.35rem 0.65rem;
}
[data-bs-theme="dark"] .trf-item-num-badge,
html[data-theme="dark"] .trf-item-num-badge,
.dark-mode .trf-item-num-badge {
    background: rgba(37, 99, 235, 0.3);
    color: #93c5fd;
}

/* Stock status indicators */
.stok-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    padding: 0.4rem 0.85rem;
    border-radius: 0.65rem;
    font-weight: 500;
}
.stok-ok {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.stok-warn {
    background: rgba(245, 158, 11, 0.12);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.25);
}
.stok-empty {
    background: rgba(244, 63, 94, 0.12);
    color: #e11d48;
    border: 1px solid rgba(244, 63, 94, 0.25);
    animation: trfPulse 1.8s infinite;
}
@keyframes trfPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.65; }
}

[data-bs-theme="dark"] .stok-ok,
html[data-theme="dark"] .stok-ok,
.dark-mode .stok-ok {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}
[data-bs-theme="dark"] .stok-warn,
html[data-theme="dark"] .stok-warn,
.dark-mode .stok-warn {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}
[data-bs-theme="dark"] .stok-empty,
html[data-theme="dark"] .stok-empty,
.dark-mode .stok-empty {
    background: rgba(244, 63, 94, 0.2);
    color: #f87171;
}

/* Floating Action Footer (Mobile Optimized) */
.trf-modal-footer-sticky {
    position: sticky;
    bottom: 0;
    z-index: 1040;
    background: var(--trf-footer-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid var(--trf-card-border);
    padding: 0.85rem 1.25rem calc(0.85rem + env(safe-area-inset-bottom, 0px));
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.08);
}
[data-bs-theme="dark"] .trf-modal-footer-sticky,
html[data-theme="dark"] .trf-modal-footer-sticky,
.dark-mode .trf-modal-footer-sticky {
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.4);
}

.btn-glow-pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
    transition: all 0.2s ease;
}
.btn-glow-pending:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
    color: #ffffff !important;
}

.btn-soft-draft {
    background: rgba(100, 116, 139, 0.12);
    color: var(--trf-text-main);
    border: 1px solid var(--trf-card-border);
    font-weight: 600;
    transition: all 0.2s ease;
}
.btn-soft-draft:hover {
    background: rgba(100, 116, 139, 0.2);
    color: var(--trf-text-main);
}

.btn-soft-cancel {
    background: transparent;
    color: var(--trf-text-muted);
    border: 1px solid var(--trf-card-border);
    font-weight: 500;
}
.btn-soft-cancel:hover {
    background: rgba(0, 0, 0, 0.05);
    color: var(--trf-text-main);
}
[data-bs-theme="dark"] .btn-soft-cancel:hover,
html[data-theme="dark"] .btn-soft-cancel:hover,
.dark-mode .btn-soft-cancel:hover {
    background: rgba(255, 255, 255, 0.08);
}

/* ─── Search Modal Specific Styling ───────────────────────────── */
#modalPilihBarang .modal-content,
#modalLookupGudang .modal-content {
    background: var(--trf-modal-bg);
    color: var(--trf-text-main);
    border: 1px solid var(--trf-card-border);
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
}

.lookup-hero-search-wrapper {
    position: relative;
    margin-bottom: 1rem;
}
.lookup-hero-search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.85rem;
    border-radius: 9999px;
    border: 1px solid var(--trf-card-border);
    background: var(--trf-input-bg);
    color: var(--trf-text-main);
    font-size: 0.92rem;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.lookup-hero-search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}
.lookup-hero-search-icon {
    position: absolute;
    left: 1.1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--trf-text-muted);
    font-size: 1.05rem;
    pointer-events: none;
}

/* Modern Lookup Table */
.table-modern-lookup {
    border-collapse: separate !important;
    border-spacing: 0 0.45rem !important;
    width: 100% !important;
}
.table-modern-lookup thead th {
    background: transparent !important;
    border: none !important;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--trf-text-muted);
    padding: 0.5rem 0.85rem;
}
.table-modern-lookup tbody tr {
    background: var(--trf-card-bg);
    border: 1px solid var(--trf-card-border);
    border-radius: 0.85rem;
    transition: all 0.15s ease;
    cursor: pointer;
}
.table-modern-lookup tbody tr td {
    padding: 0.85rem 0.95rem;
    border-top: 1px solid var(--trf-card-border);
    border-bottom: 1px solid var(--trf-card-border);
    vertical-align: middle;
}
.table-modern-lookup tbody tr td:first-child {
    border-left: 1px solid var(--trf-card-border);
    border-top-left-radius: 0.85rem;
    border-bottom-left-radius: 0.85rem;
}
.table-modern-lookup tbody tr td:last-child {
    border-right: 1px solid var(--trf-card-border);
    border-top-right-radius: 0.85rem;
    border-bottom-right-radius: 0.85rem;
}
.table-modern-lookup tbody tr:hover {
    background: rgba(37, 99, 235, 0.05);
    border-color: rgba(37, 99, 235, 0.35);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}
[data-bs-theme="dark"] .table-modern-lookup tbody tr:hover,
html[data-theme="dark"] .table-modern-lookup tbody tr:hover,
.dark-mode .table-modern-lookup tbody tr:hover {
    background: rgba(30, 41, 59, 0.95);
    border-color: rgba(59, 130, 246, 0.4);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.badge-kode-barang {
    background: rgba(37, 99, 235, 0.12);
    color: #2563eb;
    border: 1px solid rgba(37, 99, 235, 0.25);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    padding: 0.35rem 0.65rem;
    border-radius: 0.5rem;
    font-size: 0.82rem;
    display: inline-block;
}
[data-bs-theme="dark"] .badge-kode-barang,
html[data-theme="dark"] .badge-kode-barang,
.dark-mode .badge-kode-barang {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border-color: rgba(96, 165, 250, 0.35);
}

.badge-stock-available {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-weight: 700;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
[data-bs-theme="dark"] .badge-stock-available,
html[data-theme="dark"] .badge-stock-available,
.dark-mode .badge-stock-available {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border-color: rgba(52, 211, 153, 0.35);
}

.badge-hpp-pill {
    background: rgba(100, 116, 139, 0.12);
    color: var(--trf-text-muted);
    font-family: ui-monospace, monospace;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 0.4rem;
    border: 1px solid var(--trf-card-border);
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-action-pilih-modern {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 9999px;
    padding: 0.45rem 1.15rem;
    font-weight: 600;
    font-size: 0.85rem;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.btn-action-pilih-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
    color: #ffffff !important;
}
.btn-action-pilih-modern:active {
    transform: translateY(0);
}

/* Custom DataTables Styling in Lookup Modal */
#modalPilihBarang .dataTables_wrapper .dataTables_filter,
#modalLookupGudang .dataTables_wrapper .dataTables_filter {
    display: none;
}
#modalPilihBarang .dataTables_wrapper .dataTables_length,
#modalLookupGudang .dataTables_wrapper .dataTables_length {
    margin-bottom: 0.5rem;
    font-size: 0.82rem;
    color: var(--trf-text-muted);
}
#modalPilihBarang .dataTables_wrapper .dataTables_length select,
#modalLookupGudang .dataTables_wrapper .dataTables_length select {
    border-radius: 0.5rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.82rem;
    border: 1px solid var(--trf-card-border);
    background-color: var(--trf-card-bg);
    color: var(--trf-text-main);
}
#modalPilihBarang .dataTables_wrapper .dataTables_info,
#modalLookupGudang .dataTables_wrapper .dataTables_info {
    font-size: 0.8rem;
    color: var(--trf-text-muted);
    padding-top: 0.85rem;
}
#modalPilihBarang .dataTables_wrapper .dataTables_paginate,
#modalLookupGudang .dataTables_wrapper .dataTables_paginate {
    padding-top: 0.75rem;
}
#modalPilihBarang .dataTables_wrapper .dataTables_paginate .paginate_button,
#modalLookupGudang .dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0.5rem !important;
    padding: 0.35rem 0.75rem !important;
    margin: 0 2px !important;
    border: 1px solid var(--trf-card-border) !important;
    background: var(--trf-card-bg) !important;
    color: var(--trf-text-main) !important;
}
#modalPilihBarang .dataTables_wrapper .dataTables_paginate .paginate_button.current,
#modalLookupGudang .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
}
</style>

<div class="modal fade" id="modalTambahTransfer" tabindex="-1" aria-labelledby="modalTambahTransferLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            
            {{-- Modern Cinematic Header --}}
            <div class="trf-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="trf-header-icon-box">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalTambahTransferLabel">
                            Buat Transfer Gudang
                        </h5>
                        <small class="text-white-50" style="font-size: 0.78rem;">Mutasi Stok Antar Gudang & Antar Perusahaan (PT)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                <form id="formTambahTransfer" novalidate>
                    @csrf
                    <input type="hidden" id="trf_edit_id" name="trf_edit_id">

                    {{-- ── SECTION 1: ROUTE DESTINATION CARDS ── --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-uppercase" style="letter-spacing: 0.6px; font-size: 0.72rem; color: var(--trf-text-muted);">
                                <i class="bi bi-signpost-split me-1 text-primary"></i>Rute Distribusi Mutasi
                            </span>
                            <div id="badge_jenis_transfer_container">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle me-1"></i>Pilih Gudang Asal & Tujuan
                                </span>
                            </div>
                        </div>

                        <div class="trf-route-grid">
                            {{-- Card Gudang Asal --}}
                            <div class="trf-route-card active-asal" onclick="openLookupGudangModal('asal')">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="trf-route-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </div>
                                        <div>
                                            <small class="d-block fw-bold text-danger text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Gudang Asal (Pengirim)</small>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Stok Keluar</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 28px; height: 28px; padding: 0;">
                                        <i class="bi bi-search" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>
                                <input type="text" id="trf_gudang_asal_display" class="form-control form-control-sm bg-transparent border-0 p-0 fw-bold" readonly placeholder="Klik untuk memilih Gudang Asal..." required style="font-size: 0.95rem; cursor: pointer;">
                                <input type="hidden" name="gudang_asal_id" id="trf_gudang_asal_id">
                                <input type="hidden" id="trf_gudang_asal_company_id">
                                <div class="invalid-feedback d-block" id="err-gudang_asal_id"></div>
                            </div>

                            {{-- Animated Connector --}}
                            <div class="trf-route-connector">
                                <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-arrow-right fs-6 d-none d-md-block"></i>
                                    <i class="bi bi-arrow-down fs-6 d-md-none"></i>
                                </div>
                            </div>

                            {{-- Card Gudang Tujuan --}}
                            <div class="trf-route-card active-tujuan" onclick="openLookupGudangModal('tujuan')">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="trf-route-icon bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-box-arrow-in-down-left"></i>
                                        </div>
                                        <div>
                                            <small class="d-block fw-bold text-success text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Gudang Tujuan (Penerima)</small>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Stok Masuk</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-circle" style="width: 28px; height: 28px; padding: 0;">
                                        <i class="bi bi-search" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>
                                <input type="text" id="trf_gudang_tujuan_display" class="form-control form-control-sm bg-transparent border-0 p-0 fw-bold" readonly placeholder="Klik untuk memilih Gudang Tujuan..." required style="font-size: 0.95rem; cursor: pointer;">
                                <input type="hidden" name="gudang_tujuan_id" id="trf_gudang_tujuan_id">
                                <input type="hidden" id="trf_gudang_tujuan_company_id">
                                <div class="invalid-feedback d-block" id="err-gudang_tujuan_id"></div>
                            </div>
                        </div>
                        <input type="hidden" name="jenis_transfer" id="trf_jenis_transfer" value="Intra-PT">
                    </div>

                    {{-- ── SECTION 2: DOKUMEN & KETERANGAN ── --}}
                    <div class="trf-form-card mb-4">
                        <div class="row g-3">
                            {{-- Tanggal --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold small mb-1" style="color: var(--trf-text-muted);">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>Tanggal Transfer <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="tanggal_transfer" id="trf_tanggal" class="form-control fw-medium"
                                       value="{{ now()->format('Y-m-d') }}" required>
                                <div class="invalid-feedback" id="err-tanggal_transfer"></div>
                            </div>

                            {{-- Nomor Transfer --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold small mb-1" style="color: var(--trf-text-muted);">
                                    <i class="bi bi-hash me-1 text-primary"></i>Nomor Dokumen
                                </label>
                                <div class="input-group">
                                    <div class="input-group-text bg-body-tertiary">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="trf_auto_number" name="auto_number" checked>
                                            <label class="form-check-label small" for="trf_auto_number">Auto</label>
                                        </div>
                                    </div>
                                    <input type="text" name="nomor_transfer" id="trf_nomor_transfer" class="form-control font-monospace"
                                           placeholder="TRF/YYYY/MM/XXXX" maxlength="20" disabled>
                                </div>
                                <div class="invalid-feedback d-block" id="err-nomor_transfer"></div>
                            </div>

                            {{-- Keterangan --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold small mb-1" style="color: var(--trf-text-muted);">
                                    <i class="bi bi-chat-left-text me-1 text-primary"></i>Keterangan (Maks 40 Karakter)
                                </label>
                                <input type="text" name="keterangan" id="trf_keterangan" class="form-control" maxlength="40"
                                       placeholder="Catatan keperluan transfer (opsional)">
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 3: COA INTER-PT (DYNAMIC) ── --}}
                    <div id="container_coa_transfer" class="trf-form-card mb-4 border-warning border-opacity-50" style="display:none; background: rgba(245, 158, 11, 0.05);">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-journal-check text-warning fs-5"></i>
                            <div>
                                <h6 class="fw-bold mb-0 text-warning">Mapping Akun Jurnal (Transfer Antar PT / Inter-PT)</h6>
                                <small class="text-muted" style="font-size: 0.78rem;">Otomatis diposting ke Piutang/Hutang Intercompany dan Persediaan kedua entitas</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small mb-1">Akun Debit (Piutang Intercompany) <span class="text-danger">*</span></label>
                                <select name="coa_debit_id" id="trf_coa_debit_id" class="form-select select2-trf">
                                    <option value="">-- Pilih Akun Debit --</option>
                                    @if(isset($coas))
                                        @foreach($coas as $c)
                                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback d-block" id="err-coa_debit_id"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small mb-1">Akun Kredit (Persediaan Keluar) <span class="text-danger">*</span></label>
                                <select name="coa_kredit_id" id="trf_coa_kredit_id" class="form-select select2-trf">
                                    <option value="">-- Pilih Akun Kredit --</option>
                                    @if(isset($coas))
                                        @foreach($coas as $c)
                                            <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="invalid-feedback d-block" id="err-coa_kredit_id"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 4: DETAIL BARANG MUTASI ── --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold mb-0" style="color: var(--trf-text-main);">
                                    <i class="bi bi-boxes me-2 text-primary"></i>Daftar Barang Transfer
                                </h6>
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fw-bold" id="trf_item_count_badge">0</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1.5" onclick="tambahItemTrf()">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Barang
                            </button>
                        </div>

                        <div id="trf_items_container">
                            {{-- Item Cards rendered dynamically --}}
                        </div>
                        <div id="err-items" class="text-danger small mt-2" style="display:none;"></div>
                    </div>

                </form>
            </div>

            {{-- ── STICKY FLOATING ACTION BAR (MOBILE OPTIMIZED) ── --}}
            <div class="trf-modal-footer-sticky">
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-soft-cancel rounded-pill px-4 py-2 flex-fill flex-md-grow-0" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" class="btn btn-soft-draft rounded-pill px-4 py-2 flex-fill flex-md-grow-0" id="btnSimpanDraft">
                            <i class="bi bi-pencil-square me-1"></i> Simpan Draft
                        </button>
                    </div>
                    <button type="button" class="btn btn-glow-pending rounded-pill px-4 py-2 d-inline-flex align-items-center justify-content-center gap-2" id="btnSimpanPending">
                        <i class="bi bi-send-check-fill fs-5"></i> <span>Ajukan Transfer (Pending Approval)</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endcanany

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Lookup Gudang (Cari & Pilih Gudang Asal/Tujuan)    --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLookupGudang" tabindex="-1" aria-labelledby="modalLookupGudangLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="trf-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="trf-header-icon-box" style="width: 36px; height: 36px;">
                        <i class="bi bi-house-door fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalLookupGudangTitle" style="font-size: 1.05rem;">Pilih Gudang</h5>
                        <small class="text-white-50" id="modalLookupGudangSubtitle" style="font-size: 0.75rem;">Menampilkan daftar gudang penyimpanan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="lookup-hero-search-wrapper">
                    <i class="bi bi-search lookup-hero-search-icon"></i>
                    <input type="text" id="lookupGudangSearchInput" class="lookup-hero-search-input" placeholder="Cari berdasarkan kode, nama gudang, PT, atau cabang..." autocomplete="off">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-modern-lookup align-middle mb-0 w-100" id="tableLookupGudang">
                        <thead>
                            <tr>
                                <th width="15%">Kode</th>
                                <th width="32%">Nama Gudang</th>
                                <th width="26%">PT (Perusahaan)</th>
                                <th width="15%">Cabang</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupGudangAsal" class="tbody-lookup-gudang" style="display:none;">
                            @foreach($gudangsAsal as $g)
                                <tr class="row-gudang-item align-middle" data-search="{{ strtolower($g->kode_gudang . ' ' . $g->nama_gudang . ' ' . ($g->company ? $g->company->nama_perusahaan : '') . ' ' . ($g->cabang ? $g->cabang->nama_cabang : '')) }}" onclick="selectGudangFromModal('asal', {{ $g->id }}, '{{ addslashes($g->nama_gudang) }}', '{{ addslashes($g->company ? $g->company->nama_perusahaan : '-') }}', '{{ addslashes($g->cabang ? $g->cabang->nama_cabang : 'Pusat (Head Office)') }}', {{ $g->company_id ?: 0 }})">
                                    <td>
                                        <span class="badge-kode-barang">
                                            {{ $g->kode_gudang }}
                                        </span>
                                    </td>
                                    <td><span class="fw-bold d-block" style="font-size:0.92rem; color: var(--trf-text-main);">{{ $g->nama_gudang }}</span></td>
                                    <td>
                                        @if($g->company)
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-building me-1"></i>{{ $g->company->nama_perusahaan }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($g->cabang)
                                            <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $g->cabang->nama_cabang }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-building me-1"></i>Pusat (Head Office)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <button type="button" class="btn-action-pilih-modern" onclick="selectGudangFromModal('asal', {{ $g->id }}, '{{ addslashes($g->nama_gudang) }}', '{{ addslashes($g->company ? $g->company->nama_perusahaan : '-') }}', '{{ addslashes($g->cabang ? $g->cabang->nama_cabang : 'Pusat (Head Office)') }}', {{ $g->company_id ?: 0 }})">
                                            <i class="bi bi-check-lg"></i> <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tbody id="tbodyLookupGudangTujuan" class="tbody-lookup-gudang" style="display:none;">
                            @foreach($gudangsTujuan as $g)
                                <tr class="row-gudang-item align-middle" data-search="{{ strtolower($g->kode_gudang . ' ' . $g->nama_gudang . ' ' . ($g->company ? $g->company->nama_perusahaan : '') . ' ' . ($g->cabang ? $g->cabang->nama_cabang : '')) }}" onclick="selectGudangFromModal('tujuan', {{ $g->id }}, '{{ addslashes($g->nama_gudang) }}', '{{ addslashes($g->company ? $g->company->nama_perusahaan : '-') }}', '{{ addslashes($g->cabang ? $g->cabang->nama_cabang : 'Pusat (Head Office)') }}', {{ $g->company_id ?: 0 }})">
                                    <td>
                                        <span class="badge-kode-barang">
                                            {{ $g->kode_gudang }}
                                        </span>
                                    </td>
                                    <td><span class="fw-bold d-block" style="font-size:0.92rem; color: var(--trf-text-main);">{{ $g->nama_gudang }}</span></td>
                                    <td>
                                        @if($g->company)
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-building me-1"></i>{{ $g->company->nama_perusahaan }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($g->cabang)
                                            <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $g->cabang->nama_cabang }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2.5 py-1.5 rounded-pill">
                                                <i class="bi bi-building me-1"></i>Pusat (Head Office)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <button type="button" class="btn-action-pilih-modern" onclick="selectGudangFromModal('tujuan', {{ $g->id }}, '{{ addslashes($g->nama_gudang) }}', '{{ addslashes($g->company ? $g->company->nama_perusahaan : '-') }}', '{{ addslashes($g->cabang ? $g->cabang->nama_cabang : 'Pusat (Head Office)') }}', {{ $g->company_id ?: 0 }})">
                                            <i class="bi bi-check-lg"></i> <span>Pilih</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Pilih Barang (Search & Add Popup)                   --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalPilihBarang" tabindex="-1" aria-labelledby="modalPilihBarangLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="trf-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="trf-header-icon-box" style="width: 36px; height: 36px;">
                        <i class="bi bi-box-seam fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalPilihBarangLabel" style="font-size: 1.05rem;">Pilih Barang untuk Transfer</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Hanya menampilkan barang dengan stok tersedia di Gudang Asal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                {{-- Hero Live Search Input --}}
                <div class="lookup-hero-search-wrapper">
                    <i class="bi bi-search lookup-hero-search-icon"></i>
                    <input type="text" id="pilihBarangLiveSearch" class="lookup-hero-search-input" placeholder="Cari nama barang, kode SKU, atau satuan..." autocomplete="off">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-modern-lookup align-middle mb-0 w-100" id="tablePilihBarang">
                        <thead>
                            <tr>
                                <th width="20%">Kode SKU</th>
                                <th width="42%">Nama Barang</th>
                                <th width="23%">Stok Tersedia</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loaded dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemCounter = 0;
let gudangAsalItems = [];
let activeRowId = null;
let pilihBarangModalInstance = null;
let currentLookupTarget = null;
let lookupGudangModalInstance = null;

$(document).ready(function () {
    // Init Select2 for COA Selects
    if ($.fn.select2) {
        $('.select2-trf').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            placeholder: '-- Pilih --',
            dropdownParent: $('#modalTambahTransfer'),
        });
    }

    // Auto Number toggle
    $('#trf_auto_number').on('change', function () {
        const manual = !this.checked;
        $('#trf_nomor_transfer').prop('disabled', !manual);
        if (!manual) $('#trf_nomor_transfer').val('');
    });
});

// ── Open Lookup Gudang Modal ──────────────────────────────────────────
function openLookupGudangModal(target) {
    currentLookupTarget = target;
    $('#lookupGudangSearchInput').val('');
    $('.tbody-lookup-gudang').hide();

    if (target === 'asal') {
        $('#modalLookupGudangTitle').text('Pilih Gudang Asal (Pengirim)');
        $('#modalLookupGudangSubtitle').text('Menampilkan gudang aktif pada PT & Cabang yang sedang login');
        $('#tbodyLookupGudangAsal').show();
        $('#tbodyLookupGudangAsal .row-gudang-item').show();
    } else {
        $('#modalLookupGudangTitle').text('Pilih Gudang Tujuan (Penerima)');
        $('#modalLookupGudangSubtitle').text('Menampilkan seluruh gudang aktif (lintas cabang & antar PT)');
        $('#tbodyLookupGudangTujuan').show();
        $('#tbodyLookupGudangTujuan .row-gudang-item').show();
    }

    if (!lookupGudangModalInstance) {
        lookupGudangModalInstance = new bootstrap.Modal(document.getElementById('modalLookupGudang'));
    }
    lookupGudangModalInstance.show();
}

// Live search inside Lookup Gudang modal
$('#lookupGudangSearchInput').on('input keyup', function () {
    let q = $(this).val().toLowerCase().trim();
    let targetTbody = currentLookupTarget === 'asal' ? '#tbodyLookupGudangAsal' : '#tbodyLookupGudangTujuan';
    
    $(targetTbody + ' .row-gudang-item').each(function () {
        let text = $(this).attr('data-search') || '';
        if (text.includes(q)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

// Select Warehouse from Lookup Modal
function selectGudangFromModal(target, id, nama, pt, cabang, companyId) {
    let displayText = `${nama} (PT: ${pt} | Cabang: ${cabang})`;
    
    if (target === 'asal') {
        let currentTujuan = $('#trf_gudang_tujuan_id').val();
        if (currentTujuan && currentTujuan == id) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Gudang asal tidak boleh sama dengan gudang tujuan!',
                icon: 'warning',
                target: document.getElementById('modalLookupGudang')
            });
            return;
        }
        
        $('#trf_gudang_asal_id').val(id);
        $('#trf_gudang_asal_display').val(displayText);
        $('#trf_gudang_asal_company_id').val(companyId);

        // Reset items list when Gudang Asal changes
        $('#trf_items_container').empty();
        itemCounter = 0;
        gudangAsalItems = [];
        updateRowNumbers();
        
        // Fetch items & COA options for Gudang Asal's company
        $.ajax({
            url: `/warehouse-transfer/gudang-items/${id}`,
            method: 'GET',
            success: function (res) {
                gudangAsalItems = res.items || [];
                if ($.fn.DataTable.isDataTable('#tablePilihBarang')) {
                    $('#tablePilihBarang').DataTable().destroy();
                    buildPilihBarangRows();
                }
            }
        });

        $.ajax({
            url: `/warehouse-transfer/company-coas/${companyId}`,
            method: 'GET',
            success: function (res) {
                let debitOpts = '<option value="">-- Pilih Akun Debit --</option>';
                let kreditOpts = '<option value="">-- Pilih Akun Kredit --</option>';
                if (res.coas && res.coas.length > 0) {
                    res.coas.forEach(c => {
                        let opt = `<option value="${c.id}">${c.code} - ${c.name}</option>`;
                        debitOpts += opt;
                        kreditOpts += opt;
                    });
                }
                $('#trf_coa_debit_id').html(debitOpts);
                $('#trf_coa_kredit_id').html(kreditOpts);

                if (res.default_debit) {
                    $('#trf_coa_debit_id').val(res.default_debit).trigger('change');
                }
                if (res.default_kredit && res.default_kredit != res.default_debit) {
                    $('#trf_coa_kredit_id').val(res.default_kredit).trigger('change');
                } else {
                    $('#trf_coa_kredit_id').val('').trigger('change');
                }
            }
        });
    } else if (target === 'tujuan') {
        let currentAsal = $('#trf_gudang_asal_id').val();
        if (currentAsal && currentAsal == id) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Gudang tujuan tidak boleh sama dengan gudang asal!',
                icon: 'warning',
                target: document.getElementById('modalLookupGudang')
            });
            return;
        }
        
        $('#trf_gudang_tujuan_id').val(id);
        $('#trf_gudang_tujuan_display').val(displayText);
        $('#trf_gudang_tujuan_company_id').val(companyId);
    }

    checkJenisTransfer();

    if (lookupGudangModalInstance) {
        lookupGudangModalInstance.hide();
    }
}

const defaultCoaDebitId = @json($defaultCoaDebitId ?? null);
const defaultCoaKreditId = @json($defaultCoaKreditId ?? null);
const canSeeHppGlobal = @json($canSeeHpp ?? (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Owner') || auth()->user()->hasRole('Admin') || auth()->user()->can('lihat-hpp-transfer') || auth()->user()->can('akses-accounting')));

// Automatically detect whether transfer is Intra-PT or Inter-PT
function checkJenisTransfer() {
    let asalComp = $('#trf_gudang_asal_company_id').val();
    let tujuanComp = $('#trf_gudang_tujuan_company_id').val();

    if (!asalComp || !tujuanComp) {
        $('#container_coa_transfer').slideUp(200);
        $('#trf_jenis_transfer').val('Intra-PT');
        $('#badge_jenis_transfer_container').html(`
            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
                <i class="bi bi-info-circle me-1"></i>Pilih Gudang Asal & Tujuan
            </span>
        `);
        return;
    }

    if (asalComp != tujuanComp) {
        // Inter-PT Transfer (Requires COA)
        $('#badge_jenis_transfer_container').html(`
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-30 px-3 py-1.5 rounded-pill font-semibold" style="font-size: 0.78rem;">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Inter-PT (Antar Perusahaan)
            </span>
        `);
        $('#container_coa_transfer').slideDown(200);
        $('#trf_jenis_transfer').val('Inter-PT');

        // Auto-select mapped default COAs from Account Mapping settings
        if (!$('#trf_coa_debit_id').val() && defaultCoaDebitId) {
            $('#trf_coa_debit_id').val(defaultCoaDebitId).trigger('change');
        }
        if (!$('#trf_coa_kredit_id').val() && defaultCoaKreditId && defaultCoaKreditId != $('#trf_coa_debit_id').val()) {
            $('#trf_coa_kredit_id').val(defaultCoaKreditId).trigger('change');
        }
    } else {
        // Intra-PT Transfer (No COA needed)
        $('#badge_jenis_transfer_container').html(`
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-30 px-3 py-1.5 rounded-pill font-semibold" style="font-size: 0.78rem;">
                <i class="bi bi-building-check me-1"></i> Transfer Intra-PT (Internal Perusahaan)
            </span>
        `);
        $('#container_coa_transfer').slideUp(200);
        $('#trf_jenis_transfer').val('Intra-PT');
        $('#trf_coa_debit_id, #trf_coa_kredit_id').val('').trigger('change');
    }
}

// ── Update Item Row Numbers and Counter ────────────────────────────────
function updateRowNumbers() {
    $('#trf_items_container .trf-item-card').each(function (i) {
        $(this).find('.trf-item-num-badge').text(`# Item ${i + 1}`);
    });
    $('#trf_item_count_badge').text($('#trf_items_container .trf-item-card').length);
}

// ── Add Item Row ───────────────────────────────────────────────────────
function tambahItemTrf() {
    const asalId = $('#trf_gudang_asal_id').val();
    const tujuanId = $('#trf_gudang_tujuan_id').val();

    if (!asalId && !tujuanId) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Mohon isi Gudang Asal dan Gudang Tujuan terlebih dahulu!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    if (!asalId) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Mohon isi Gudang Asal terlebih dahulu!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    if (!tujuanId) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Mohon isi Gudang Tujuan terlebih dahulu!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    const currentItemCount = $('#trf_items_container .trf-item-card').length;
    if (currentItemCount >= 25) {
        Swal.fire({
            title: 'Batas Maksimum Barang',
            text: 'Jumlah item transfer tidak boleh melebihi 25 item!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    const id = itemCounter++;

    const html = `
    <div class="trf-item-card" id="item-row-${id}">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge rounded-pill trf-item-num-badge"># Item ${currentItemCount + 1}</span>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" onclick="hapusRowTrf(${id})" title="Hapus Barang Ini">
                <i class="bi bi-trash3 me-1"></i> <span class="small">Hapus</span>
            </button>
        </div>
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-7">
                <label class="small text-secondary fw-semibold mb-1">Nama / Kode Barang <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control bg-white" id="barang-display-${id}" readonly placeholder="Klik untuk Cari & Pilih Barang..." required onclick="pilihBarangForBtn(${id})" style="cursor: pointer;">
                    <button class="btn btn-primary px-3" type="button" onclick="pilihBarangForBtn(${id})" title="Cari Barang">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="hidden" class="select-barang-input" id="select-barang-${id}">
                </div>
            </div>
            <div class="col-12 col-md-5">
                <label class="small text-secondary fw-semibold mb-1">Jumlah Transfer <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control form-control-sm input-jumlah text-center font-monospace fw-bold" id="jumlah-${id}" min="0.01" step="any" placeholder="0" required>
                    <span class="input-group-text bg-body-tertiary text-muted small fw-semibold" id="satuan-label-${id}">-</span>
                </div>
            </div>
        </div>
        <div class="stok-indicator mt-2" id="stok-indicator-${id}" style="display:none;"></div>
    </div>`;

    $('#trf_items_container').append(html);
    updateRowNumbers();
}

function hapusRowTrf(id) {
    $(`#item-row-${id}`).remove();
    updateRowNumbers();
}

// ── Search & Select Barang Popup Modal Logic ─────────────────────────
function pilihBarangForBtn(rowId) {
    const asalId = $('#trf_gudang_asal_id').val();
    const tujuanId = $('#trf_gudang_tujuan_id').val();

    if (!asalId || !tujuanId) {
        Swal.fire({
            title: 'Peringatan',
            text: 'Mohon isi Gudang Asal dan Gudang Tujuan terlebih dahulu!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    if (gudangAsalItems.length === 0) {
        Swal.fire({
            title: 'Stok Kosong',
            text: 'Tidak ada barang dengan stok tersedia pada gudang asal yang dipilih.',
            icon: 'info',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    activeRowId = rowId;

    // Lazy initialize the pilih barang table
    if (!$.fn.DataTable.isDataTable('#tablePilihBarang')) {
        initPilihBarangTable();
    }

    if (!pilihBarangModalInstance) {
        pilihBarangModalInstance = new bootstrap.Modal(document.getElementById('modalPilihBarang'));
    }
    pilihBarangModalInstance.show();
}

function buildPilihBarangRows() {
    let rowsHtml = '';
    gudangAsalItems.forEach(b => {
        const hppFmt = parseFloat(b.harga_pokok || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const hppDiv = canSeeHppGlobal && parseFloat(b.harga_pokok || 0) > 0
            ? `<div class="mt-1"><span class="badge-hpp-pill"><i class="bi bi-tag-fill me-1 text-secondary"></i>HPP: Rp ${hppFmt}</span></div>` 
            : '';

        rowsHtml += `
        <tr class="align-middle" onclick="selectBarangFromModal(${b.id})">
            <td>
                <span class="badge-kode-barang">
                    ${b.kode}
                </span>
            </td>
            <td>
                <span class="fw-bold text-wrap d-block" style="font-size:0.92rem; color: var(--trf-text-main);">${b.nama}</span>
            </td>
            <td>
                <span class="badge-stock-available">
                    <i class="bi bi-box-seam"></i> ${parseFloat(b.stok_aktual || 0).toLocaleString('id-ID')} ${b.satuan}
                </span>
                ${hppDiv}
            </td>
            <td class="text-center" onclick="event.stopPropagation();">
                <button type="button" class="btn-action-pilih-modern" onclick="selectBarangFromModal(${b.id})">
                    <i class="bi bi-check-lg"></i> <span>Pilih</span>
                </button>
            </td>
        </tr>`;
    });
    $('#tablePilihBarang tbody').html(rowsHtml);
    
    let dt = $('#tablePilihBarang').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        dom: "<'row g-2 align-items-center mb-2'<'col-12 col-sm-6'l><'col-12 col-sm-6 text-end'i>>t<'row g-2 align-items-center mt-2'<'col-12'p>>",
        language: {
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ barang',
            infoEmpty: 'Tidak ada barang ditemukan',
            infoFiltered: '(difilter dari _MAX_ total)',
            paginate: { previous: '‹', next: '›' }
        }
    });

    $('#pilihBarangLiveSearch').val('');
}

// Live search inside Pilih Barang modal
$(document).on('input keyup', '#pilihBarangLiveSearch', function () {
    let q = $(this).val();
    if ($.fn.DataTable.isDataTable('#tablePilihBarang')) {
        $('#tablePilihBarang').DataTable().search(q).draw();
    }
});

function initPilihBarangTable() {
    buildPilihBarangRows();
}

function selectBarangFromModal(barangId) {
    if (activeRowId === null) return;

    // Check if this item is already selected in another row
    let isDuplicate = false;
    $('.select-barang-input').each(function () {
        if ($(this).attr('id') !== `select-barang-${activeRowId}` && $(this).val() == barangId) {
            isDuplicate = true;
            return false;
        }
    });

    if (isDuplicate) {
        Swal.fire({
            title: 'Barang Ganda',
            text: 'Barang ini sudah ditambahkan ke daftar transfer!',
            icon: 'warning',
            target: document.getElementById('modalPilihBarang')
        });
        return;
    }

    const b = gudangAsalItems.find(item => item.id == barangId);
    if (b) {
        $(`#select-barang-${activeRowId}`).val(b.id);
        $(`#barang-display-${activeRowId}`).val(`${b.kode} - ${b.nama}`);
        
        // Auto-update Debit & Credit COAs (category inventory account or default mapping)
        let coaKreditToSet = b.cat_coa_id || defaultCoaKreditId;
        if (coaKreditToSet) {
            $('#trf_coa_kredit_id').val(coaKreditToSet).trigger('change');
        }
        if (defaultCoaDebitId && !$('#trf_coa_debit_id').val()) {
            $('#trf_coa_debit_id').val(defaultCoaDebitId).trigger('change');
        }

        // Reset old qty if product changes
        $(`#jumlah-${activeRowId}`).removeAttr('data-old-qty');

        // Trigger stock level lookup
        onBarangChange(activeRowId);
        
        // Close modal
        if (pilihBarangModalInstance) {
            pilihBarangModalInstance.hide();
        }
        activeRowId = null;
    }
}

// ── Handle product selection & fetch live stock ──────────────────────
function onBarangChange(id) {
    const barangId = $(`#select-barang-${id}`).val();
    const asalId = $('#trf_gudang_asal_id').val();
    const indicator = $(`#stok-indicator-${id}`);
    const label = $(`#satuan-label-${id}`);

    if (!barangId) {
        indicator.hide().empty();
        label.text('-');
        return;
    }

    if (!asalId) {
        indicator.show().html('<i class="bi bi-exclamation-circle-fill text-warning me-1"></i>Pilih gudang asal terlebih dahulu untuk melihat stok!').attr('class', 'stok-indicator stok-warn');
        return;
    }

    indicator.show().html('<span class="spinner-border spinner-border-sm text-secondary me-1"></span> Mengambil data stok...');

    $.ajax({
        url: `/warehouse-transfer/stok/${asalId}/${barangId}`,
        method: 'GET',
        success: function (res) {
            label.text(res.satuan);
            const oldQty = parseFloat($(`#jumlah-${id}`).attr('data-old-qty')) || 0;
            const rawStok = parseFloat(res.stok_aktual) || 0;
            const effectiveStok = rawStok + oldQty;

            $(`#jumlah-${id}`).attr('data-stok', effectiveStok);
            $(`#jumlah-${id}`).attr('data-raw-stok', rawStok);
            $(`#jumlah-${id}`).attr('data-hpp', res.harga_pokok);
            updateRowEstimasiNilai(id);
        },
        error: function () {
            $(`#jumlah-${id}`).removeAttr('data-stok').removeAttr('data-hpp').removeAttr('data-raw-stok');
            indicator.html('<i class="bi bi-x-circle-fill text-danger me-1"></i>Gagal mengambil stok.').attr('class', 'stok-indicator stok-empty');
        }
    });
}

function updateRowEstimasiNilai(id) {
    const qty = parseFloat($(`#jumlah-${id}`).val()) || 0;
    const stok = parseFloat($(`#jumlah-${id}`).attr('data-stok')) || 0;
    const hpp = parseFloat($(`#jumlah-${id}`).attr('data-hpp')) || 0;
    const sat = $(`#satuan-label-${id}`).text();
    const indicator = $(`#stok-indicator-${id}`);
    const oldQty = parseFloat($(`#jumlah-${id}`).attr('data-old-qty')) || 0;
    const rawStok = parseFloat($(`#jumlah-${id}`).attr('data-raw-stok')) || 0;

    if (isNaN(stok) || !sat || sat === '-') return;

    let text = `Tersedia: <strong>${stok.toLocaleString('id-ID')} ${sat}</strong>`;
    if (oldQty > 0) {
        text = `Tersedia: <strong>${stok.toLocaleString('id-ID')} ${sat}</strong> <small class="text-secondary opacity-75">(${rawStok.toLocaleString('id-ID')} gudang + ${oldQty.toLocaleString('id-ID')} transaksi ini)</small>`;
    }
    
    if (canSeeHppGlobal && !isNaN(hpp) && hpp > 0) {
        let hppFormatted = hpp.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        text += ` <span class="mx-1">•</span> HPP: <strong>Rp ${hppFormatted}</strong>`;
        if (qty > 0) {
            let totalValue = qty * hpp;
            text += ` <span class="mx-1">•</span> Total: <strong>Rp ${totalValue.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>`;
        }
    }

    let badgeClass = 'stok-ok';
    if (stok <= 0) {
        badgeClass = 'stok-empty';
        text += ' <span class="ms-1 fw-bold">(Stok Habis!)</span>';
    } else if (qty > stok) {
        badgeClass = 'stok-empty';
        text += ' <span class="ms-1 fw-bold">(Jumlah melebihi stok!)</span>';
    } else if (stok < 10) {
        badgeClass = 'stok-warn';
    }
    indicator.html(`<i class="bi bi-shield-check me-1"></i>${text}`).attr('class', `stok-indicator ${badgeClass}`);
}

$(document).on('input change', '.input-jumlah', function () {
    const idAttr = $(this).attr('id');
    const idx = idAttr.split('-').pop();
    const qty = parseFloat($(this).val()) || 0;
    const availableStok = parseFloat($(this).attr('data-stok'));

    if (!isNaN(availableStok) && qty > availableStok) {
        $(this).val(availableStok);
        const barangText = $(`#barang-display-${idx}`).val() || 'Barang';
        Swal.fire({
            title: 'Peringatan Stok Limit',
            text: `Jumlah transfer untuk "${barangText}" tidak boleh melebihi stok yang tersedia (${availableStok.toLocaleString('id-ID')})!`,
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
    }
    updateRowEstimasiNilai(idx);
});

// Live input limit handlers with alert warnings
$('#trf_keterangan').on('input', function () {
    if ($(this).val().length > 40) {
        $(this).val($(this).val().substring(0, 40));
        Swal.fire({
            title: 'Batas Karakter',
            text: 'Keterangan tidak boleh melebihi 40 karakter!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
    }
});

$('#trf_nomor_transfer').on('input', function () {
    if ($(this).val().length > 20) {
        $(this).val($(this).val().substring(0, 20));
        Swal.fire({
            title: 'Batas Karakter',
            text: 'Nomor transfer tidak boleh melebihi 20 karakter!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
    }
});

// ── Submit Transfer Form Handler (Draft / Pending / Posting) ─────────
function submitTransferForm(submitAction) {
    const form = $('#formTambahTransfer')[0];

    clearErrors();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Keterangan length validation check
    const keteranganVal = $('#trf_keterangan').val() || '';
    if (keteranganVal.length > 40) {
        Swal.fire({
            title: 'Batas Karakter',
            text: 'Keterangan tidak boleh melebihi 40 karakter!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        $('#trf_keterangan').addClass('is-invalid');
        return;
    }

    // Nomor transfer length validation check
    const isAutoNum = $('#trf_auto_number').is(':checked');
    const nomorTransferVal = $('#trf_nomor_transfer').val() || '';
    if (!isAutoNum && nomorTransferVal.length > 20) {
        Swal.fire({
            title: 'Batas Karakter',
            text: 'Nomor transfer tidak boleh melebihi 20 karakter!',
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        $('#trf_nomor_transfer').addClass('is-invalid');
        return;
    }

    // Inter-PT COA validation check
    const jenisTrf = $('#trf_jenis_transfer').val();
    const coaDebitId = $('#trf_coa_debit_id').val();
    const coaKreditId = $('#trf_coa_kredit_id').val();

    if (jenisTrf === 'Inter-PT' && (!coaDebitId || !coaKreditId)) {
        Swal.fire('Validasi Gagal', 'Transfer Antar PT (Inter-PT) wajib memilih Akun Debit & Kredit Jurnal COA!', 'error');
        if (!coaDebitId) $('#trf_coa_debit_id').addClass('is-invalid');
        if (!coaKreditId) $('#trf_coa_kredit_id').addClass('is-invalid');
        return;
    }

    // Validate items count
    const rows = $('#trf_items_container .trf-item-card');
    if (rows.length === 0) {
        $('#err-items').text('Minimal masukkan 1 barang untuk ditransfer.').show();
        return;
    }
    $('#err-items').hide();

    let items = [];
    let valid = true;
    let validationMsg = '';

    rows.each(function () {
        const idAttr = $(this).attr('id');
        const idx = idAttr.split('-').pop();
        const bid = $(`#select-barang-${idx}`).val();
        const qty = parseFloat($(`#jumlah-${idx}`).val());
        const rawStok = $(`#jumlah-${idx}`).attr('data-stok');
        const availableStok = rawStok !== undefined ? parseFloat(rawStok) : Infinity;

        if (!bid || isNaN(qty) || qty <= 0) {
            valid = false;
            $(`#barang-display-${idx}`).addClass('is-invalid');
            $(`#jumlah-${idx}`).addClass('is-invalid');
            validationMsg = 'Mohon lengkapi seluruh kolom barang dan jumlah!';
        } else if (submitAction !== 'draft' && !isNaN(availableStok) && availableStok !== Infinity && qty > availableStok) {
            valid = false;
            $(`#jumlah-${idx}`).addClass('is-invalid');
            let barangText = $(`#barang-display-${idx}`).val() || `Baris #${parseInt(idx) + 1}`;
            validationMsg = `Jumlah transfer untuk "${barangText}" melebihi stok yang tersedia (${availableStok.toLocaleString('id-ID')})!`;
            return false;
        } else {
            items.push({ barang_id: bid, jumlah: qty });
        }
    });

    if (!valid) {
        Swal.fire({
            title: 'Peringatan Stok Limit',
            text: validationMsg,
            icon: 'warning',
            target: document.getElementById('modalTambahTransfer')
        });
        return;
    }

    const $allBtns = $('#btnSimpanDraft, #btnSimpanPending');
    $allBtns.prop('disabled', true);

    const asalId = $('#trf_gudang_asal_id').val();
    const tujuanId = $('#trf_gudang_tujuan_id').val();
    const tanggal = $('#trf_tanggal').val();
    const keterangan = $('#trf_keterangan').val();
    const autoNum = $('#trf_auto_number').is(':checked') ? 1 : 0;
    const nomor = $('#trf_nomor_transfer').val();

    const editId = $('#trf_edit_id').val();
    const targetUrl = editId ? `/warehouse-transfer/${editId}` : '/warehouse-transfer/store';
    const targetMethod = editId ? 'PUT' : 'POST';

    $.ajax({
        url: targetUrl,
        method: targetMethod,
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data: JSON.stringify({
            gudang_asal_id: asalId,
            gudang_tujuan_id: tujuanId,
            tanggal_transfer: tanggal,
            keterangan: keterangan,
            auto_number: autoNum,
            nomor_transfer: nomor,
            jenis_transfer: jenisTrf,
            coa_debit_id: coaDebitId,
            coa_kredit_id: coaKreditId,
            items: items,
            submit_action: submitAction
        }),
        success: function (res) {
            $allBtns.prop('disabled', false);
            $('#modalTambahTransfer').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => reloadTableTransfer());
        },
        error: function (xhr) {
            $allBtns.prop('disabled', false);
            const res = xhr.responseJSON ?? {};
            if (xhr.status === 422 && res.errors) {
                Object.keys(res.errors).forEach(k => {
                    showFieldError(k, res.errors[k][0]);
                });
                if (res.message && res.message !== 'Validasi gagal!') {
                    Swal.fire({
                        title: 'Validasi Transfer',
                        text: res.message,
                        icon: 'warning',
                        target: document.getElementById('modalTambahTransfer')
                    });
                }
            } else {
                Swal.fire({
                    title: 'Validasi Transfer',
                    text: res.message || 'Terjadi kesalahan server.',
                    icon: 'warning',
                    target: document.getElementById('modalTambahTransfer')
                });
            }
        }
    });
}

$('#btnSimpanDraft').on('click', function () { submitTransferForm('draft'); });
$('#btnSimpanPending').on('click', function () { submitTransferForm('pending'); });

// ── Edit Transfer Modal Populator ─────────────────────────────────────
function editTransfer(id) {
    clearErrors();
    $('#trf_edit_id').val(id);
    $('#btnSimpanDraft, #btnSimpanPending').prop('disabled', false);

    $.ajax({
        url: `/warehouse-transfer/${id}/edit`,
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function (res) {
            const trf = res.transfer;
            $('#btnSimpanDraft, #btnSimpanPending').prop('disabled', false);

            if (trf.status === 'Confirmed') {
                $('#modalTambahTransferLabel').html('<i class="bi bi-shield-check me-2 text-warning"></i>Edit Transfer Gudang (Super Admin Override)');
                $('#btnSimpanPending').html('<i class="bi bi-check-circle-fill fs-5"></i> <span>Simpan Perubahan (Super Admin)</span>');
                $('#btnSimpanDraft').hide();
            } else if (trf.status === 'Pending') {
                $('#modalTambahTransferLabel').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Edit Pengajuan Transfer Gudang');
                $('#btnSimpanPending').html('<i class="bi bi-send-check-fill fs-5"></i> <span>Simpan Pengajuan (Pending)</span>');
                $('#btnSimpanDraft').html('<i class="bi bi-arrow-counterclockwise fs-5"></i> <span>Kembalikan ke Draft</span>').show();
            } else {
                $('#modalTambahTransferLabel').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Edit Draft Transfer Gudang');
                $('#btnSimpanPending').html('<i class="bi bi-send-check-fill fs-5"></i> <span>Ajukan Transfer (Pending Approval)</span>');
                $('#btnSimpanDraft').html('<i class="bi bi-file-earmark-arrow-down fs-5"></i> <span>Simpan Draft</span>').show();
            }

            if (trf.tanggal_transfer) {
                let d = new Date(trf.tanggal_transfer);
                if (!isNaN(d.getTime())) {
                    let month = '' + (d.getMonth() + 1);
                    let day = '' + d.getDate();
                    let year = d.getFullYear();
                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;
                    $('#trf_tanggal').val([year, month, day].join('-'));
                }
            }

            $('#trf_auto_number').prop('checked', false);
            $('#trf_nomor_transfer').prop('disabled', false).val(trf.nomor_transfer);
            $('#trf_keterangan').val(trf.keterangan || '');

            if (trf.gudang_asal || trf.gudangAsal) {
                const ga = trf.gudang_asal || trf.gudangAsal;
                $('#trf_gudang_asal_id').val(ga.id);
                $('#trf_gudang_asal_company_id').val(ga.company_id);
                const companyAsalNama = ga.company ? ga.company.nama_perusahaan : 'PT Asal';
                const cabangAsalNama = ga.cabang ? ga.cabang.nama_cabang : 'Cabang Utama';
                $('#trf_gudang_asal_display').val(`${ga.nama_gudang} (PT: ${companyAsalNama} | Cabang: ${cabangAsalNama})`);
            }

            if (trf.gudang_tujuan || trf.gudangTujuan) {
                const gt = trf.gudang_tujuan || trf.gudangTujuan;
                $('#trf_gudang_tujuan_id').val(gt.id);
                $('#trf_gudang_tujuan_company_id').val(gt.company_id);
                const companyTujuanNama = gt.company ? gt.company.nama_perusahaan : 'PT Tujuan';
                const cabangTujuanNama = gt.cabang ? gt.cabang.nama_cabang : 'Cabang Utama';
                $('#trf_gudang_tujuan_display').val(`${gt.nama_gudang} (PT: ${companyTujuanNama} | Cabang: ${cabangTujuanNama})`);
            }

            checkJenisTransfer();

            if (trf.coa_debit_id) $('#trf_coa_debit_id').val(trf.coa_debit_id).trigger('change');
            if (trf.coa_kredit_id) $('#trf_coa_kredit_id').val(trf.coa_kredit_id).trigger('change');

            $('#trf_items_container').empty();
            itemCounter = 0;

            const asalGudangId = $('#trf_gudang_asal_id').val();

            if (asalGudangId) {
                $.ajax({
                    url: `/warehouse-transfer/gudang-items/${asalGudangId}`,
                    method: 'GET',
                    success: function (itemsRes) {
                        gudangAsalItems = itemsRes.items || [];
                        if ($.fn.DataTable.isDataTable('#tablePilihBarang')) {
                            $('#tablePilihBarang').DataTable().destroy();
                        }
                        buildPilihBarangRows();

                        (trf.details || []).forEach(d => {
                            const idx = itemCounter++;
                            let initialBarangText = d.barang ? `${d.barang.kode_barang} - ${d.barang.nama_barang}` : 'Pilih Barang';
                            let satuanNama = d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : '-';
                            let itemQty = parseFloat(d.jumlah) || 0;
                            let oldQtyAttr = (trf.status === 'Confirmed') ? `data-old-qty="${itemQty}"` : '';

                            const html = `
                            <div class="trf-item-card" id="item-row-${idx}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge rounded-pill trf-item-num-badge"># Item ${idx + 1}</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" onclick="hapusRowTrf(${idx})" title="Hapus Barang Ini">
                                        <i class="bi bi-trash3 me-1"></i> <span class="small">Hapus</span>
                                    </button>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-7">
                                        <label class="small text-secondary fw-semibold mb-1">Nama / Kode Barang <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control bg-white" id="barang-display-${idx}" value="${initialBarangText}" readonly placeholder="Klik untuk Cari & Pilih Barang..." required onclick="pilihBarangForBtn(${idx})" style="cursor: pointer;">
                                            <button class="btn btn-primary px-3" type="button" onclick="pilihBarangForBtn(${idx})" title="Cari Barang">
                                                <i class="bi bi-search"></i>
                                            </button>
                                            <input type="hidden" class="select-barang-input" id="select-barang-${idx}" value="${d.barang_id}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <label class="small text-secondary fw-semibold mb-1">Jumlah Transfer <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control form-control-sm input-jumlah text-center font-monospace fw-bold" id="jumlah-${idx}" value="${itemQty}" ${oldQtyAttr} min="0.01" step="any" placeholder="0" required>
                                            <span class="input-group-text bg-body-tertiary text-muted small fw-semibold" id="satuan-label-${idx}">${satuanNama}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="stok-indicator mt-2" id="stok-indicator-${idx}"></div>
                            </div>`;
                            $('#trf_items_container').append(html);

                            onBarangChange(idx);
                        });
                        updateRowNumbers();
                    }
                });
            }

            $('#modalTambahTransfer').modal('show');
        },
        error: function () {
            Swal.fire('Gagal', 'Tidak dapat mengambil data transfer.', 'error');
        }
    });
}

// ── Reset modal on close ───────────────────────────────────────────────
document.getElementById('modalTambahTransfer')?.addEventListener('hidden.bs.modal', function () {
    $('#trf_edit_id').val('');
    $('#modalTambahTransferLabel').html('Buat Transfer Gudang');
    $('#btnSimpanPending').html('<i class="bi bi-send-check-fill fs-5"></i> <span>Ajukan Transfer (Pending Approval)</span>');
    $('#btnSimpanDraft').show();
    $('#btnSimpanDraft, #btnSimpanPending').prop('disabled', false);
    document.getElementById('formTambahTransfer').reset();
    $('#trf_gudang_asal_id, #trf_gudang_tujuan_id').val('');
    $('#trf_gudang_asal_display, #trf_gudang_tujuan_display').val('');
    $('#trf_gudang_asal_company_id, #trf_gudang_tujuan_company_id').val('');
    $('#trf_jenis_transfer').val('Intra-PT');
    $('#container_coa_transfer').hide();
    $('#trf_coa_debit_id, #trf_coa_kredit_id').val('').trigger('change');
    $('#badge_jenis_transfer_container').html(`
        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
            <i class="bi bi-info-circle me-1"></i>Pilih Gudang Asal & Tujuan
        </span>
    `);
    $('#trf_items_container').empty();
    itemCounter = 0;
    updateRowNumbers();
    clearErrors();
    $('#trf_auto_number').prop('checked', true);
    $('#trf_nomor_transfer').prop('disabled', true).val('');
    $('#err-items').hide();
});

// ── Helpers ────────────────────────────────────────────────────────────
function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function showFieldError(field, msg) {
    let parts = field.split('.');
    if (parts.length === 3 && parts[0] === 'items') {
        const idx = parts[1];
        const col = parts[2];
        if (col === 'jumlah') {
            $(`#jumlah-${idx}`).addClass('is-invalid');
        } else if (col === 'barang_id') {
            $(`#barang-display-${idx}`).addClass('is-invalid');
        }
        Swal.fire('Validasi Gagal', 'Mohon periksa baris barang yang Anda input!', 'error');
    } else {
        const el = document.getElementById(`err-${field}`);
        if (el) {
            el.textContent = msg;
            el.style.display = 'block';
        }
        const input = document.querySelector(`[name="${field}"]`);
        if (input) input.classList.add('is-invalid');
    }
}
</script>
@endpush
