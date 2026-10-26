{{-- SCOPED STYLING UNTUK MODAL CLOSING & JURNAL KONSOLIDASI BARANG JADI (LIGHT & DARK MODE SUPPORT) --}}
<style>
/* ─── MODAL CONTAINER THEME ADAPTATION ─── */
.pbj-modal-container {
    background-color: var(--spk-modal-bg, #ffffff) !important;
    border: 1px solid var(--spk-modal-border, rgba(226, 232, 240, 0.8)) !important;
    color: var(--spk-text-main, #0f172a) !important;
    box-shadow: var(--spk-modal-shadow, 0 25px 50px -12px rgba(0, 0, 0, 0.45)) !important;
}

/* ─── PARAMETER & SUMMARY BOX ─── */
.closing-param-box {
    background: var(--spk-bg-light, #f8fafc) !important;
    border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    border-radius: 16px;
    padding: 1rem 1.15rem;
    transition: all 0.2s ease;
    color: var(--spk-text-main, #0f172a) !important;
}
html[data-bs-theme="dark"] .closing-param-box,
[data-bs-theme="dark"] .closing-param-box,
.dark-mode .closing-param-box {
    background: rgba(15, 23, 42, 0.65) !important;
    border-color: rgba(51, 65, 85, 0.85) !important;
    color: #f1f5f9 !important;
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
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
    color: var(--spk-text-main, #0f172a) !important;
}
.closing-form-input::placeholder {
    color: var(--spk-text-muted, #94a3b8) !important;
    opacity: 0.8;
}
.closing-input-group-text {
    background-color: var(--spk-bg-tertiary, #f1f5f9) !important;
    color: var(--spk-text-muted, #64748b) !important;
    border: 1px solid var(--spk-border-color, #cbd5e1) !important;
}
html[data-bs-theme="dark"] .closing-input-group-text,
[data-bs-theme="dark"] .closing-input-group-text,
.dark-mode .closing-input-group-text {
    background-color: #0f172a !important;
    color: #94a3b8 !important;
    border-color: #334155 !important;
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
    color: var(--spk-text-main, #0f172a);
}
html[data-bs-theme="dark"] .closing-custom-table tbody tr,
[data-bs-theme="dark"] .closing-custom-table tbody tr,
.dark-mode .closing-custom-table tbody tr {
    background-color: #1e293b !important;
    border-bottom-color: #334155 !important;
    color: #f1f5f9 !important;
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

/* ─── ALERTS & EARLY CLOSING BOX ─── */
.early-closing-confirm-box {
    background: #fffbeb;
    border: 1px solid rgba(245, 158, 11, 0.4);
    color: #92400e;
    border-radius: 14px;
    padding: 0.9rem 1.1rem;
}
html[data-bs-theme="dark"] .early-closing-confirm-box,
[data-bs-theme="dark"] .early-closing-confirm-box,
.dark-mode .early-closing-confirm-box {
    background: rgba(245, 158, 11, 0.12) !important;
    border-color: rgba(245, 158, 11, 0.45) !important;
    color: #fde68a !important;
}

.closing-simulasi-card {
    background: #ffffff;
    border: 1px solid var(--spk-border-color, #e2e8f0);
    border-radius: 12px;
}
html[data-bs-theme="dark"] .closing-simulasi-card,
[data-bs-theme="dark"] .closing-simulasi-card,
.dark-mode .closing-simulasi-card {
    background: rgba(15, 23, 42, 0.8) !important;
    border-color: rgba(51, 65, 85, 0.8) !important;
}
</style>

{{-- 1. MODAL AJUKAN / REVIEW CLOSING PENERIMAAN BARANG JADI --}}
<div class="modal fade" id="modalConfirmClosingFg" tabindex="-1" aria-labelledby="modalConfirmClosingFgLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">
            {{-- HEADER --}}
            <div class="modal-header px-4 py-3.5 text-white" id="modalClosingFgHeader" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-box-seam-fill fs-5 text-white" id="modalClosingFgIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalConfirmClosingFgLabel">Ajukan Penutupan (Closing) Hasil Produksi</h5>
                        <p class="small text-white text-opacity-85 mb-0" id="modalConfirmClosingFgSubtitle" style="font-size: 11.5px;">SPK: #SPK/2026/09/00001</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body px-4 py-3.5">
                <input type="hidden" id="closing_fg_spk_id" value="">
                
                {{-- LOADING STATE --}}
                <div id="closing_fg_preview_loading" class="text-center py-5">
                    <div class="spinner-border text-emerald" role="status" style="width: 2.75rem; height: 2.75rem; color: #10b981;">
                        <span class="visually-hidden">Memuat Data SPK...</span>
                    </div>
                    <p class="mt-3 spk-text-muted fw-semibold" style="font-size: 13.5px;">Memuat rincian hasil penerimaan & biaya produksi SPK...</p>
                </div>

                {{-- CONTENT CONTAINER --}}
                <div id="closing_fg_preview_content" class="d-none">
                    {{-- INFO ALERT CARD --}}
                    <div class="p-3.5 mb-4 rounded-3 d-flex align-items-start gap-3 border shadow-2xs" id="closing_fg_alert_box" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
                        <i class="bi bi-info-circle-fill fs-4 mt-0.5" id="closing_fg_alert_icon" style="color: #059669;"></i>
                        <div class="small spk-text-main" id="closing_fg_alert_text" style="line-height: 1.6;">
                            <b class="text-emerald d-block mb-1" id="closing_fg_alert_title">Konfirmasi Penyelesaian Hasil Produksi SPK</b>
                            Pengajuan closing penerimaan barang jadi akan dikirimkan ke bagian Accounting untuk peninjauan biaya riil, perhitungan HPP aktual, dan penerbitan jurnal akuntansi persediaan.
                        </div>
                    </div>

                    {{-- RINGKASAN PRODUK & TARGET PROGRESI --}}
                    <div class="closing-param-box mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6 border-end" style="border-color: var(--spk-border-color) !important;">
                                <span class="spk-text-muted small d-block mb-1.5 font-semibold" style="font-size: 11px;">PRODUK JADI:</span>
                                <h6 class="fw-bold mb-1.5 text-primary font-monospace" id="closing_fg_preview_barang" style="font-size: 14px;">-</h6>
                                <div class="small font-monospace spk-text-muted" id="closing_fg_preview_satuan" style="font-size: 11.5px;">Satuan: Pcs</div>
                            </div>
                            <div class="col-md-6 font-monospace" style="font-size: 12.5px;">
                                <div class="d-flex justify-content-between mb-1.5">
                                    <span class="spk-text-muted small">Target SPK:</span>
                                    <span class="fw-bold spk-text-main" id="closing_fg_preview_target">0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5">
                                    <span class="spk-text-muted small">Hasil Diterima:</span>
                                    <span class="fw-bold text-success" id="closing_fg_preview_selesai">0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1.5">
                                    <span class="spk-text-muted small">Total Reject:</span>
                                    <span class="fw-bold text-danger" id="closing_fg_preview_reject">0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="spk-text-muted small">Sesi Penyerahan:</span>
                                    <span class="badge bg-primary rounded-pill px-2.5 py-0.5 font-monospace" id="closing_fg_preview_sesi" style="font-size: 10.5px;">0 Sesi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- EARLY CLOSING WARNING IF LESS THAN 100% --}}
                    <div id="boxConfirmEarlyClosingFg" class="early-closing-confirm-box mb-4 d-none">
                        <div class="d-flex gap-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <div class="fw-bold mb-1.5" style="font-size: 13px;">Peringatan: Hasil Produksi Belum 100% Terpenuhi!</div>
                                <p class="small mb-2.5" id="early_closing_fg_desc" style="font-size: 12px; line-height: 1.55;">
                                    Jumlah penerimaan hasil produksi masih kurang dari target SPK. Pengajuan closing akan menyelesaikan SPK lebih awal (<i>Early Closing</i>).
                                </p>
                                <div class="form-check">
                                    <input class="form-check-input border-warning" type="checkbox" id="checkConfirmEarlyClosingFg" style="cursor: pointer;">
                                    <label class="form-check-label small fw-semibold" for="checkConfirmEarlyClosingFg" style="cursor: pointer;">
                                        Saya menyetujui pengajuan closing hasil produksi lebih awal (<i>Early Closing</i>).
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FORM KHUSUS REVIEW & HITUNG HPP (ACCOUNTING / APPROVER) --}}
                    <div id="sectionReviewHitungHppFg" class="d-none">
                        <div class="closing-param-box mb-4" style="border-color: rgba(245, 158, 11, 0.4) !important;">
                            <h6 class="fw-bold spk-text-main mb-3.5 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 13.5px; border-color: rgba(245, 158, 11, 0.25) !important;">
                                <i class="bi bi-calculator-fill text-warning"></i>
                                <span>Kalkulasi Komponen HPP Aktual</span>
                            </h6>
                            
                            <div class="row g-3.5 mb-3">
                                {{-- 1. Biaya Bahan Baku Riil --}}
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold spk-text-muted mb-2" style="font-size: 11.5px;">Biaya Bahan Riil (Otomatis):</label>
                                    <div class="input-group">
                                        <span class="input-group-text closing-input-group-text fw-bold font-monospace px-2.5">Rp</span>
                                        <input type="text" class="form-control closing-form-input font-monospace fw-bold text-end" id="closing_fg_biaya_bahan" readonly value="0" style="min-height: 38px;">
                                    </div>
                                    <span class="spk-text-muted small d-block mt-1.5" style="font-size: 10.5px;">*Dari mutasi kartu stok pengeluaran</span>
                                </div>

                                {{-- 2. Biaya Tenaga Kerja (TK) --}}
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold spk-text-main mb-2" style="font-size: 11.5px;">Biaya Tenaga Kerja (TK):</label>
                                    <div class="input-group">
                                        <span class="input-group-text closing-input-group-text fw-bold font-monospace px-2.5">Rp</span>
                                        <input type="text" inputmode="numeric" class="form-control closing-form-input font-monospace fw-bold text-end" id="closing_fg_biaya_tk" value="0" placeholder="0" oninput="onClosingCostInput(this)" style="min-height: 38px;">
                                    </div>
                                    <span class="spk-text-muted small d-block mt-1.5" style="font-size: 10.5px;">Gaji / Upah operator produksi</span>
                                </div>

                                {{-- 3. Biaya Overhead Pabrik (BOP) --}}
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold spk-text-main mb-2" style="font-size: 11.5px;">Biaya Overhead (BOP):</label>
                                    <div class="input-group">
                                        <span class="input-group-text closing-input-group-text fw-bold font-monospace px-2.5">Rp</span>
                                        <input type="text" inputmode="numeric" class="form-control closing-form-input font-monospace fw-bold text-end" id="closing_fg_biaya_overhead" value="0" placeholder="0" oninput="onClosingCostInput(this)" style="min-height: 38px;">
                                    </div>
                                    <span class="spk-text-muted small d-block mt-1.5" style="font-size: 10.5px;">Listrik, utilitas, penyusutan dll</span>
                                </div>
                            </div>

                            <hr class="my-3.5 opacity-25" style="border-color: var(--spk-border-color) !important;">

                            {{-- HASIL SIMULASI KALKULASI HPP --}}
                            <div class="row g-2 align-items-center closing-simulasi-card p-3">
                                <div class="col-md-6 border-end" style="border-color: var(--spk-border-color) !important;">
                                    <span class="spk-text-muted small d-block mb-1" style="font-size: 11px;">Total Biaya Produksi:</span>
                                    <h5 class="fw-bold font-monospace spk-text-main mb-0" id="closing_fg_total_biaya_simulasi" style="font-size: 16px;">Rp 0</h5>
                                </div>
                                <div class="col-md-6 ps-md-3">
                                    <span class="spk-text-muted small d-block mb-1" style="font-size: 11px;">Estimasi HPP per Unit (<span id="closing_fg_divisor_label">0 Pcs</span>):</span>
                                    <h5 class="fw-bold font-monospace text-emerald mb-0" id="closing_fg_hpp_unit_simulasi" style="color: #059669; font-size: 16px;">Rp 0 / Unit</h5>
                                </div>
                            </div>
                        </div>

                        {{-- TANGGAL CLOSING JURNAL --}}
                        <div class="row g-3.5 mb-3.5">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold spk-text-main mb-2" style="font-size: 12px;">Tanggal Jurnal Closing: <span class="text-danger">*</span></label>
                                <input type="date" class="form-control closing-form-input font-monospace" id="closing_fg_tanggal" value="{{ date('Y-m-d') }}" style="min-height: 38px;">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold spk-text-main mb-2" style="font-size: 12px;">Catatan Persetujuan Closing:</label>
                                <input type="text" class="form-control closing-form-input" id="closing_fg_catatan_approval" placeholder="Catatan otorisasi closing HPP (opsional)..." style="min-height: 38px;">
                            </div>
                        </div>
                    </div>

                    {{-- FORM INPUT CATATAN (TIM PENGAJU) --}}
                    <div id="sectionCatatanPengajuFg" class="mb-3">
                        <label class="form-label small fw-bold spk-text-main mb-2" style="font-size: 12px;">Catatan Pengajuan (Opsional):</label>
                        <textarea class="form-control closing-form-input" id="closing_fg_ajukan_catatan" rows="2" placeholder="Tuliskan catatan atau keterangan untuk bagian Accounting jika ada..."></textarea>
                    </div>

                    {{-- INFO PENGAJU JIKA SEDANG DI-REVIEW --}}
                    <div id="sectionInfoPengajuFg" class="d-none closing-param-box p-3 small mt-3">
                        <i class="bi bi-person-check-fill text-primary me-1"></i>
                        Diajukan oleh: <b class="spk-text-main" id="closing_fg_info_requester">-</b> pada <span id="closing_fg_info_requested_at">-</span>
                        <div class="mt-1.5" id="closing_fg_info_note_wrapper">
                            <i>Catatan pengajuan: "<span id="closing_fg_info_note">-</span>"</i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer px-4 py-3 closing-modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal">
                    Batal
                </button>

                <div class="d-flex align-items-center gap-2">
                    {{-- Tombol Tolak Pengajuan (Hanya tampil di mode Review) --}}
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 py-1.5 fw-bold d-none align-items-center gap-1.5 shadow-2xs" id="btnRejectClosingFg" onclick="rejectClosingFg()">
                        <i class="bi bi-x-circle"></i>
                        <span>Tolak Pengajuan</span>
                    </button>

                    {{-- Tombol Kirim Pengajuan Closing (Tim Produksi) --}}
                    <button type="button" class="btn btn-sm btn-emerald-vibrant rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-white shadow-sm" id="btnSubmitAjukanClosingFg" onclick="submitAjukanClosingFg()" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-send-check"></i>
                        <span>Kirim Pengajuan Closing ke Accounting</span>
                    </button>

                    {{-- Tombol Setujui & Terbitkan Jurnal HPP (Accounting) --}}
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-4 py-1.5 fw-bold d-none align-items-center gap-1.5 text-dark shadow-sm" id="btnSubmitClosingFg" onclick="submitClosingFg()" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border-color: #d97706;">
                        <i class="bi bi-lock-fill"></i>
                        <span>Setujui & Terbitkan Jurnal HPP</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. MODAL DETAIL JURNAL KONSOLIDASI PENERIMAAN BARANG JADI --}}
<div class="modal fade" id="modalDetailJurnalClosingFg" tabindex="-1" aria-labelledby="modalDetailJurnalClosingFgLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">
            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; background: rgba(255,255,255,0.15);">
                        <i class="bi bi-journal-bookmark-fill fs-5 text-info"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalDetailJurnalClosingFgLabel">Jurnal Konsolidasi Penerimaan Barang Jadi SPK</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-semibold" style="background: rgba(255, 255, 255, 0.2); font-size: 10px; color: #c7d2fe;">GENERAL JOURNAL POSTED</span>
                        </div>
                        <p class="small text-white text-opacity-85 mb-0" id="modalJurnalClosingFgSubtitle" style="font-size: 11.5px;">SPK: #SPK/2026/09/00001</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0">
                {{-- LOADING SPINNER --}}
                <div id="jurnal_closing_fg_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.75rem; height: 2.75rem;">
                        <span class="visually-hidden">Memuat Jurnal...</span>
                    </div>
                    <p class="mt-3 spk-text-muted fw-semibold" style="font-size: 13px;">Mengambil data jurnal akuntansi konsolidasi...</p>
                </div>

                {{-- CONTENT CONTAINER --}}
                <div id="jurnal_closing_fg_content" class="d-none">
                    {{-- META STRIP --}}
                    <div class="px-4 py-3 closing-table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="badge spk-badge-blue rounded-pill px-3 py-1 font-monospace fw-bold fs-6" id="jurnal_closing_fg_nomor">
                                JRN/FG/2026/09/00001
                            </span>
                            <span class="spk-text-muted small ms-2 font-monospace" id="jurnal_closing_fg_tanggal">12/09/2026</span>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace fw-bold">
                                <i class="bi bi-check-all me-1"></i>BALANCED
                            </span>
                            <div class="small spk-text-muted mt-1" id="jurnal_closing_fg_user">-</div>
                        </div>
                    </div>

                    {{-- KETERANGAN --}}
                    <div class="px-4 py-2.5 border-bottom" style="background: rgba(99, 102, 241, 0.05); border-color: var(--spk-border-color) !important;">
                        <span class="spk-text-muted small fw-semibold me-2" style="font-size: 11.5px;">Keterangan:</span>
                        <span class="fw-medium spk-text-main small" id="jurnal_closing_fg_keterangan" style="font-size: 12px;">-</span>
                    </div>

                    {{-- TABEL DEBIT KREDIT --}}
                    <div class="p-4">
                        <div class="closing-table-card shadow-2xs">
                            <div class="table-responsive">
                                <table class="table align-middle closing-custom-table font-monospace" style="font-size: 12.5px;">
                                    <thead>
                                        <tr>
                                            <th class="ps-3.5" style="width: 140px;">Kode Akun</th>
                                            <th>Nama Akun</th>
                                            <th class="text-end" style="width: 150px;">Debit (Rp)</th>
                                            <th class="text-end pe-3.5" style="width: 150px;">Kredit (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jurnal_closing_fg_tbody">
                                        {{-- Dynamic Content via JS --}}
                                    </tbody>
                                    <tfoot class="fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end ps-3.5 spk-text-muted">TOTAL:</td>
                                            <td class="text-end text-success font-monospace" id="jurnal_closing_fg_total_debit">Rp 0</td>
                                            <td class="text-end text-primary font-monospace pe-3.5" id="jurnal_closing_fg_total_kredit">Rp 0</td>
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
