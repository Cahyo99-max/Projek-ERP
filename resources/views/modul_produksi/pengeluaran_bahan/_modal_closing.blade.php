{{-- SCOPED STYLING UNTUK MODAL CLOSING & JURNAL KONSOLIDASI (LIGHT & DARK MODE SUPPORT) --}}
<style>
/* ─── MODAL CONTAINER THEME ADAPTATION ─── */
.pbj-modal-container {
    background-color: var(--spk-modal-bg, #ffffff) !important;
    border: 1px solid var(--spk-modal-border, rgba(226, 232, 240, 0.8)) !important;
    color: var(--spk-text-main, #0f172a) !important;
    box-shadow: var(--spk-modal-shadow, 0 25px 50px -12px rgba(0, 0, 0, 0.45)) !important;
}

/* ─── PARAMETER BOX ─── */
.closing-param-box {
    background: var(--spk-bg-light, #f8fafc) !important;
    border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    border-radius: 16px;
    padding: 1rem 1.15rem;
    transition: all 0.2s ease;
}
html[data-bs-theme="dark"] .closing-param-box,
[data-bs-theme="dark"] .closing-param-box,
.dark-mode .closing-param-box {
    background: rgba(15, 23, 42, 0.65) !important;
    border-color: rgba(51, 65, 85, 0.85) !important;
}

/* ─── KPI STAT CARDS ─── */
.closing-stat-box {
    border-radius: 14px;
    padding: 0.85rem 0.6rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.closing-stat-box:hover {
    transform: translateY(-2px);
}

.closing-stat-cyan {
    background: rgba(6, 182, 212, 0.08);
    border: 1px solid rgba(6, 182, 212, 0.25);
}
.closing-stat-purple {
    background: rgba(139, 92, 246, 0.08);
    border: 1px solid rgba(139, 92, 246, 0.25);
}
.closing-stat-emerald {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.25);
}

html[data-bs-theme="dark"] .closing-stat-cyan,
[data-bs-theme="dark"] .closing-stat-cyan,
.dark-mode .closing-stat-cyan {
    background: rgba(6, 182, 212, 0.12) !important;
    border-color: rgba(6, 182, 212, 0.35) !important;
}
html[data-bs-theme="dark"] .closing-stat-purple,
[data-bs-theme="dark"] .closing-stat-purple,
.dark-mode .closing-stat-purple {
    background: rgba(139, 92, 246, 0.12) !important;
    border-color: rgba(139, 92, 246, 0.35) !important;
}
html[data-bs-theme="dark"] .closing-stat-emerald,
[data-bs-theme="dark"] .closing-stat-emerald,
.dark-mode .closing-stat-emerald {
    background: rgba(16, 185, 129, 0.12) !important;
    border-color: rgba(16, 185, 129, 0.35) !important;
}

/* ─── FORM INPUTS ─── */
.closing-form-input {
    background-color: var(--spk-input-bg, #ffffff) !important;
    color: var(--spk-text-main, #0f172a) !important;
    border: 1px solid var(--spk-border-color, #cbd5e1) !important;
    border-radius: 9px;
    font-size: 12.5px;
    transition: all 0.2s ease;
}
.closing-form-input:focus {
    border-color: #0d9488 !important;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.2) !important;
    color: var(--spk-text-main, #0f172a) !important;
}
.closing-form-input::placeholder {
    color: var(--spk-text-muted, #94a3b8) !important;
    opacity: 0.8;
}

/* ─── REKAP TABLE CARD & STYLING ─── */
.closing-table-card {
    background-color: var(--spk-card-bg, #ffffff) !important;
    border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    border-radius: 16px;
    overflow: hidden;
}

.closing-table-header {
    background-color: var(--spk-bg-light, #f8fafc) !important;
    border-bottom: 1px solid var(--spk-border-color, #e2e8f0) !important;
    color: var(--spk-text-main, #0f172a) !important;
}
html[data-bs-theme="dark"] .closing-table-header,
[data-bs-theme="dark"] .closing-table-header,
.dark-mode .closing-table-header {
    background-color: rgba(15, 23, 42, 0.85) !important;
    border-bottom-color: rgba(51, 65, 85, 0.8) !important;
    color: #f1f5f9 !important;
}

.closing-custom-table {
    color: var(--spk-text-main, #0f172a) !important;
    margin-bottom: 0;
}
.closing-custom-table thead th {
    background-color: var(--spk-table-head-bg, #f1f5f9) !important;
    color: var(--spk-text-muted, #64748b) !important;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-weight: 700;
    border-bottom: 1px solid var(--spk-border-color, #e2e8f0) !important;
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
}
html[data-bs-theme="dark"] .closing-custom-table thead th,
[data-bs-theme="dark"] .closing-custom-table thead th,
.dark-mode .closing-custom-table thead th {
    background-color: #0f172a !important;
    color: #94a3b8 !important;
    border-bottom-color: #334155 !important;
}

.closing-custom-table tbody tr {
    background-color: var(--spk-card-bg, #ffffff);
    border-bottom: 1px solid var(--spk-table-border, #f1f5f9);
    transition: background-color 0.15s ease;
}
.closing-custom-table tbody tr:hover {
    background-color: var(--spk-table-hover-bg, rgba(59, 130, 246, 0.06)) !important;
}
.closing-custom-table tfoot td {
    background-color: var(--spk-table-head-bg, #f1f5f9) !important;
    color: var(--spk-text-main, #0f172a) !important;
    border-top: 2px solid var(--spk-border-color, #cbd5e1) !important;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}
html[data-bs-theme="dark"] .closing-custom-table tfoot td,
[data-bs-theme="dark"] .closing-custom-table tfoot td,
.dark-mode .closing-custom-table tfoot td {
    background-color: #0f172a !important;
    color: #f1f5f9 !important;
    border-top-color: #334155 !important;
}

/* ─── MODAL FOOTER ─── */
.closing-modal-footer {
    background-color: var(--spk-modal-bg, #ffffff) !important;
    border-top: 1px solid var(--spk-border-color, #e2e8f0) !important;
}
html[data-bs-theme="dark"] .closing-modal-footer,
[data-bs-theme="dark"] .closing-modal-footer,
.dark-mode .closing-modal-footer {
    background-color: #1e293b !important;
    border-top-color: #334155 !important;
}

/* ─── ALERTS & STATUS BANNERS ─── */
.closing-alert-card {
    border-radius: 14px;
    padding: 1rem 1.15rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
}

.closing-alert-success {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
}
html[data-bs-theme="dark"] .closing-alert-success,
[data-bs-theme="dark"] .closing-alert-success,
.dark-mode .closing-alert-success {
    background: rgba(16, 185, 129, 0.12) !important;
    border-color: rgba(16, 185, 129, 0.4) !important;
    color: #6ee7b7 !important;
}

.closing-alert-warning {
    background: #fffbeb;
    border: 1px solid #f59e0b;
    color: #92400e;
}
html[data-bs-theme="dark"] .closing-alert-warning,
[data-bs-theme="dark"] .closing-alert-warning,
.dark-mode .closing-alert-warning {
    background: rgba(245, 158, 11, 0.12) !important;
    border-color: rgba(245, 158, 11, 0.4) !important;
    color: #fcd34d !important;
}

.closing-alert-danger {
    background: #fef2f2;
    border: 1px solid #ef4444;
    color: #991b1b;
}
html[data-bs-theme="dark"] .closing-alert-danger,
[data-bs-theme="dark"] .closing-alert-danger,
.dark-mode .closing-alert-danger {
    background: rgba(239, 68, 68, 0.12) !important;
    border-color: rgba(239, 68, 68, 0.4) !important;
    color: #fca5a5 !important;
}

.early-closing-confirm-box {
    background: #ffffff;
    border: 1px solid rgba(245, 158, 11, 0.35);
    color: #0f172a;
    border-radius: 12px;
    padding: 0.75rem 1rem;
}
html[data-bs-theme="dark"] .early-closing-confirm-box,
[data-bs-theme="dark"] .early-closing-confirm-box,
.dark-mode .early-closing-confirm-box {
    background: rgba(15, 23, 42, 0.75) !important;
    border-color: rgba(245, 158, 11, 0.45) !important;
    color: #f1f5f9 !important;
}

.early-table-wrapper {
    background: #ffffff;
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 10px;
    overflow: hidden;
}
html[data-bs-theme="dark"] .early-table-wrapper,
[data-bs-theme="dark"] .early-table-wrapper,
.dark-mode .early-table-wrapper {
    background: rgba(15, 23, 42, 0.75) !important;
    border-color: rgba(245, 158, 11, 0.4) !important;
}
.early-table thead {
    background: #fef3c7;
    color: #78350f;
}
html[data-bs-theme="dark"] .early-table thead,
[data-bs-theme="dark"] .early-table thead,
.dark-mode .early-table thead {
    background: rgba(245, 158, 11, 0.25) !important;
    color: #fde68a !important;
}
</style>

{{-- MODAL 1: AJUKAN CLOSING & REVIEW/PERSETUJUAN CLOSING PENGELUARAN BAHAN --}}
<div class="modal fade" id="modalConfirmClosingBahan" tabindex="-1" aria-labelledby="modalConfirmClosingBahanTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1060px;">
        <div class="modal-content border-0 rounded-4 overflow-hidden pbj-modal-container">
            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3 position-relative" id="modalClosingHeader"
                 style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 d-flex align-items-center justify-content-center shadow-xs"
                         style="background: rgba(255,255,255,0.2); width: 44px; height: 44px;">
                        <i class="bi bi-shield-lock-fill fs-4 text-white" id="modalClosingHeaderIcon"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalConfirmClosingBahanTitle">
                                Ajukan Closing Pengeluaran Bahan Baku SPK
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-semibold font-monospace"
                                  style="background: rgba(255, 255, 255, 0.25); font-size: 10.5px; color: #ecfdf5; letter-spacing: 0.5px;"
                                  id="modalClosingBadgeMode">
                                PENGAJUAN CLOSING
                            </span>
                        </div>
                        <small class="text-white-50" style="font-size: 12px;" id="modalClosingSpkSubtitle">SPK: —</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body px-4 py-3.5">
                {{-- LOADING STATE --}}
                <div id="closing_preview_loading" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status" style="width: 2.75rem; height: 2.75rem; color: #0d9488;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-3 spk-text-muted fw-semibold" style="font-size: 13.5px;">Mengalkulasi total pemakaian bahan & estimasi jurnal...</div>
                </div>

                {{-- CONTENT --}}
                <div id="closing_preview_content" class="d-none">
                    <form id="formClosingBahan" onsubmit="event.preventDefault();">
                        <input type="hidden" id="closing_spk_id" name="spk_id">
                        <input type="hidden" id="closing_modal_mode" value="ajukan">

                        {{-- INFO STATUS BANNER: MENUNGGU CLOSING / DITOLAK SEBELUMNYA --}}
                        <div id="closing_status_banner" class="mb-3 d-none"></div>

                        {{-- DYNAMIC WARNING CONTAINER: JIKA BELUM SELESAI / SUDAH SELESAI --}}
                        <div id="closing_warning_container" class="mb-3"></div>

                        {{-- INPUTS & KPI STATS ROW (SIDE BY SIDE) --}}
                        <div class="row g-3 mb-3 align-items-stretch">
                            {{-- PARAMETER FORM --}}
                            <div class="col-lg-7">
                                <div class="closing-param-box h-100 d-flex flex-column justify-content-center">
                                    
                                    {{-- FORM UNTUK REVIEW/SETUJUI CLOSING (ACCOUNTING) --}}
                                    <div id="container_input_review_closing">
                                        <div class="row g-2.5 align-items-center">
                                            <div class="col-sm-5">
                                                <label class="form-label fw-bold small spk-text-muted mb-1" style="font-size: 11.5px;">
                                                    <i class="bi bi-calendar-check text-success me-1"></i>Tanggal Closing Jurnal <span class="text-danger">*</span>
                                                </label>
                                                <input type="date" class="form-control form-control-sm closing-form-input" id="closing_tanggal" name="tanggal_closing" value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="col-sm-7">
                                                <label class="form-label fw-bold small spk-text-muted mb-1" style="font-size: 11.5px;">
                                                    <i class="bi bi-chat-left-text text-success me-1"></i>Catatan Jurnal / Referensi
                                                </label>
                                                <input type="text" class="form-control form-control-sm closing-form-input" id="closing_catatan" name="catatan" placeholder="Contoh: Konsolidasi bahan SPK lengkap">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FORM UNTUK PENGAJUAN CLOSING (PRODUKSI/GUDANG) --}}
                                    <div id="container_input_ajukan_closing" class="d-none">
                                        <label class="form-label fw-bold small spk-text-muted mb-1" style="font-size: 11.5px;">
                                            <i class="bi bi-chat-square-quote text-primary me-1"></i>Catatan Pengajuan Closing ke Accounting (Opsional)
                                        </label>
                                        <input type="text" class="form-control form-control-sm closing-form-input" id="closing_ajukan_catatan" name="catatan_pengajuan" placeholder="Contoh: Pemakaian bahan telah selesai 100%, mohon diverifikasi & di-closing">
                                    </div>

                                </div>
                            </div>

                            {{-- KPI BADGES --}}
                            <div class="col-lg-5">
                                <div class="row g-2.5 h-100">
                                    <div class="col-kpi-stat col-4" id="closing_kpi_col_sesi">
                                        <div class="closing-stat-box closing-stat-cyan shadow-2xs">
                                            <div class="spk-text-muted small" style="font-size: 10.5px; font-weight: 600;">Total Sesi</div>
                                            <div class="fw-bold fs-5 text-cyan font-monospace mt-0.5" id="closing_badge_total_sesi">0</div>
                                        </div>
                                    </div>
                                    <div class="col-kpi-stat col-4" id="closing_kpi_col_item">
                                        <div class="closing-stat-box closing-stat-purple shadow-2xs">
                                            <div class="spk-text-muted small" style="font-size: 10.5px; font-weight: 600;">Item Bahan</div>
                                            <div class="fw-bold fs-5 text-purple font-monospace mt-0.5" id="closing_badge_total_item">0</div>
                                        </div>
                                    </div>
                                    <div class="col-kpi-stat col-4" id="closing_kpi_col_nilai">
                                        <div class="closing-stat-box closing-stat-emerald shadow-2xs">
                                            <div class="spk-text-muted small" style="font-size: 10.5px; font-weight: 600;">Nilai HPP</div>
                                            <div class="fw-bold text-success font-monospace mt-0.5" style="font-size: 13px;" id="closing_badge_total_nilai">Rp 0</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABLE REKAP BAHAN BAKU --}}
                        <div class="closing-table-card shadow-2xs mb-0">
                            <div class="closing-table-header py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                <span class="fw-bold small d-inline-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                                    <i class="bi bi-list-check fs-6 text-success"></i>
                                    <span>Rekapitulasi Pemakaian Bahan vs Target SPK</span>
                                </span>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 rounded-pill font-monospace px-2.5 py-1" style="font-size: 10.5px;" id="closing_table_item_count">0 Bahan</span>
                            </div>
                            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                <table class="table table-sm align-middle closing-custom-table">
                                    <thead class="sticky-top" style="z-index: 2;">
                                        <tr>
                                            <th class="ps-3.5">Bahan Baku</th>
                                            <th class="text-center">Target SPK</th>
                                            <th class="text-center">Total Keluar</th>
                                            <th class="text-center">Status / Sisa</th>
                                            <th class="text-end pe-3.5 col-closing-hpp">Estimasi Nilai HPP</th>
                                        </tr>
                                    </thead>
                                    <tbody id="closing_table_tbody">
                                        {{-- Diisi via JS --}}
                                    </tbody>
                                    <tfoot class="fw-bold sticky-bottom" style="z-index: 2;">
                                        <tr>
                                            <td colspan="3" class="ps-3.5 text-uppercase small spk-text-muted" id="closing_tfoot_label">
                                                <i class="bi bi-calculator text-success me-1"></i>Total Estimasi Nilai HPP
                                            </td>
                                            <td class="text-center" id="closing_tfoot_status">-</td>
                                            <td class="text-end text-success font-monospace pe-3.5 fs-6 col-closing-hpp" id="closing_tfoot_nilai">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer px-4 py-3 closing-modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">
                    Batal
                </button>

                {{-- ACTION BUTTONS GROUP --}}
                <div class="d-flex align-items-center gap-2">
                    {{-- TOMBOL TOLAK (Hanya muncul di mode Review oleh Accounting) --}}
                    <button type="button" id="btnTolakClosingBahan" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 py-1.5 fw-bold shadow-2xs d-none align-items-center gap-1.5" onclick="rejectClosingBahan()">
                        <i class="bi bi-x-circle"></i>
                        <span>Tolak Pengajuan</span>
                    </button>

                    {{-- TOMBOL SUBMIT PENGAJUAN (Mode Ajukan oleh Produksi) --}}
                    <button type="button" id="btnSubmitAjukanClosingBahan" class="btn btn-sm btn-primary rounded-pill px-4 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="submitAjukanClosingBahan()">
                        <i class="bi bi-send-check"></i>
                        <span>Kirim Pengajuan Closing ke Accounting</span>
                    </button>

                    {{-- TOMBOL SETUJUI & CLOSING (Mode Review oleh Accounting) --}}
                    <button type="button" id="btnSubmitClosingBahan" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-bold shadow-sm d-none align-items-center gap-2" onclick="submitClosingBahan()">
                        <i class="bi bi-lock-fill"></i>
                        <span>Setujui & Terbitkan Jurnal</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: DETAIL JURNAL KONSOLIDASI CLOSING BAHAN --}}
<div class="modal fade" id="modalDetailJurnalClosing" tabindex="-1" aria-labelledby="modalDetailJurnalClosingTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 overflow-hidden pbj-modal-container">
            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative"
                 style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center shadow-xs"
                         style="background: rgba(255,255,255,0.2); width: 46px; height: 46px;">
                        <i class="bi bi-journal-check fs-4 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalDetailJurnalClosingTitle">
                                Jurnal Konsolidasi Pengeluaran Bahan SPK
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-semibold"
                                  style="background: rgba(255, 255, 255, 0.25); font-size: 10.5px; color: #eff6ff;">
                                GENERAL JOURNAL POSTED
                            </span>
                        </div>
                        <small class="text-white-50" style="font-size: 12px;" id="modalJurnalClosingSubtitle">SPK: —</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0">
                {{-- LOADING STATE --}}
                <div id="jurnal_closing_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.75rem; height: 2.75rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-3 spk-text-muted fw-semibold" style="font-size: 13.5px;">Memuat rincian jurnal konsolidasi...</div>
                </div>

                {{-- CONTENT --}}
                <div id="jurnal_closing_content" class="d-none">
                    {{-- META STRIP --}}
                    <div class="px-4 py-3 closing-table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="small spk-text-muted font-monospace" id="jurnal_closing_nomor">-</div>
                            <div class="fw-bold spk-text-main font-monospace" style="font-size: 13px;" id="jurnal_closing_tanggal">-</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace fw-bold">
                                <i class="bi bi-check-all me-1"></i>BALANCED
                            </span>
                            <div class="small spk-text-muted mt-1" id="jurnal_closing_user">-</div>
                        </div>
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="px-4 py-2.5 border-bottom" style="background: rgba(37, 99, 235, 0.05); border-color: var(--spk-border-color) !important;">
                        <span class="spk-text-muted small fw-semibold me-2">Keterangan:</span>
                        <span class="fw-medium spk-text-main small" id="jurnal_closing_keterangan">-</span>
                    </div>

                    {{-- TABLE DETAILS --}}
                    <div class="p-4">
                        <div class="closing-table-card">
                            <div class="table-responsive">
                                <table class="table align-middle closing-custom-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-3.5" style="width: 140px;">Kode Akun</th>
                                            <th>Nama Akun</th>
                                            <th class="text-end" style="width: 150px;">Debit (Rp)</th>
                                            <th class="text-end pe-3.5" style="width: 150px;">Kredit (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jurnal_closing_tbody">
                                        {{-- Diisi via JS --}}
                                    </tbody>
                                    <tfoot class="fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end ps-3.5 spk-text-muted">TOTAL:</td>
                                            <td class="text-end text-success font-monospace" id="jurnal_closing_total_debit">Rp 0</td>
                                            <td class="text-end text-primary font-monospace pe-3.5" id="jurnal_closing_total_kredit">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer px-4 py-3 closing-modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
