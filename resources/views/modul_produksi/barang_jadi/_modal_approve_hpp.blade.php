{{-- MODAL REVIEW, HITUNG HPP & APPROVAL PENERIMAAN BARANG JADI (SESI LEVEL) --}}
<style>
    /* Styling Terang & Gelap untuk Modal Hitung HPP */
    .appr-hero-card {
        background: rgba(245, 158, 11, 0.05);
        border: 1px solid rgba(245, 158, 11, 0.22) !important;
        border-radius: 16px;
    }
    html[data-bs-theme="dark"] .appr-hero-card,
    .dark-mode .appr-hero-card {
        background: rgba(245, 158, 11, 0.08) !important;
        border-color: rgba(245, 158, 11, 0.3) !important;
    }

    .appr-hero-divider {
        border-right: 1px dashed rgba(245, 158, 11, 0.3);
    }
    @media (max-width: 767.98px) {
        .appr-hero-divider {
            border-right: none;
            border-bottom: 1px dashed rgba(245, 158, 11, 0.3);
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
        }
    }

    .appr-breakdown-box {
        background-color: var(--spk-bg-light, #f8fafc);
        border: 1px solid var(--spk-border-color, #e2e8f0);
        border-radius: 18px;
    }

    .appr-cost-tile {
        background-color: var(--spk-card-bg, #ffffff);
        border: 1px solid var(--spk-border-color, #e2e8f0);
        border-radius: 14px;
        transition: all 0.22s ease-in-out;
    }
    .appr-cost-tile:hover {
        border-color: rgba(59, 130, 246, 0.45);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    .appr-input-group {
        width: 250px;
        max-width: 100%;
    }

    .appr-input-prefix {
        background-color: var(--spk-bg-tertiary, #f1f5f9) !important;
        color: var(--spk-text-muted, #64748b) !important;
        border: 1px solid var(--spk-border-color, #cbd5e1) !important;
        font-family: monospace;
        font-weight: 700;
        font-size: 0.85rem;
        padding-left: 0.85rem;
        padding-right: 0.85rem;
    }

    .appr-input-control {
        background-color: var(--spk-input-bg, #ffffff) !important;
        color: var(--spk-text-main, #0f172a) !important;
        border: 1px solid var(--spk-border-color, #cbd5e1) !important;
        font-family: monospace;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
    }
    .appr-input-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        color: var(--spk-text-main, #0f172a) !important;
    }

    .appr-result-tile {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.03) 100%);
        border: 1.5px solid rgba(16, 185, 129, 0.35) !important;
        border-radius: 16px;
    }
    html[data-bs-theme="dark"] .appr-result-tile,
    .dark-mode .appr-result-tile {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.14) 0%, rgba(5, 150, 105, 0.06) 100%) !important;
        border-color: rgba(16, 185, 129, 0.45) !important;
    }

    .appr-metric-box {
        background: var(--spk-card-bg, #ffffff);
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 12px;
        padding: 1rem 1.15rem;
    }
    html[data-bs-theme="dark"] .appr-metric-box,
    .dark-mode .appr-metric-box {
        background: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }

    .appr-callout-info {
        background: rgba(14, 165, 233, 0.06);
        border: 1px solid rgba(14, 165, 233, 0.22) !important;
        color: var(--spk-text-main, #0f172a);
        border-radius: 14px;
    }
    html[data-bs-theme="dark"] .appr-callout-info,
    .dark-mode .appr-callout-info {
        background: rgba(14, 165, 233, 0.1) !important;
        border-color: rgba(14, 165, 233, 0.3) !important;
        color: #e0f2fe !important;
    }
</style>

<div class="modal fade" id="modalApproveHppPbj" tabindex="-1" aria-labelledby="modalApproveHppPbjTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background-color: var(--spk-card-bg, #ffffff); border: 1px solid var(--spk-border-color, #e2e8f0) !important;">
            
            {{-- HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative" style="background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.2); width: 44px; height: 44px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="bi bi-calculator-fill fs-5 text-warning-light"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalApproveHppPbjTitle">
                                Review & Hitung HPP Penerimaan
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 bg-black bg-opacity-25 text-white font-monospace" style="font-size: 11px;">APPROVAL ACCOUNTING</span>
                        </div>
                        <small class="text-white-50" style="font-size: 11.5px;" id="modalApproveHppPbjSubtitle">Valuasi Biaya Produksi & Otorisasi Mutasi Stok Masuk</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-3.5 p-md-4 custom-scrollbar">
                <form id="formApproveHppPbj" autocomplete="off">
                    @csrf
                    <input type="hidden" id="appr_pbj_id" name="pbj_id" value="">

                    {{-- 1. TRANSACTION INFO HERO CARD --}}
                    <div class="appr-hero-card p-3.5 p-md-4 mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-5 appr-hero-divider pe-md-3">
                                <span class="spk-text-muted small d-flex align-items-center gap-1.5 font-monospace fw-semibold mb-1" style="font-size: 11px;">
                                    <i class="bi bi-file-earmark-text text-primary"></i> DOKUMEN PENERIMAAN:
                                </span>
                                <div class="fw-bold font-monospace text-primary fs-6 mb-1" id="appr_info_nomor_pbj">-</div>
                                <div class="small fw-bold spk-text-main text-truncate" id="appr_info_barang_jadi" title="">-</div>
                            </div>
                            <div class="col-6 col-md-3 appr-hero-divider px-md-3">
                                <span class="spk-text-muted small d-flex align-items-center gap-1.5 font-monospace fw-semibold mb-1" style="font-size: 11px;">
                                    <i class="bi bi-check2-circle text-emerald"></i> HASIL BAIK (GOOD):
                                </span>
                                <div class="fw-bold fs-5 text-emerald font-monospace" id="appr_info_qty_baik">-</div>
                            </div>
                            <div class="col-6 col-md-4 ps-md-3">
                                <span class="spk-text-muted small d-flex align-items-center gap-1.5 font-monospace fw-semibold mb-1" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt-fill text-warning"></i> GUDANG TUJUAN:
                                </span>
                                <div class="fw-bold small spk-text-main font-monospace" id="appr_info_gudang">-</div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. COST BREAKDOWN & INPUT TILES --}}
                    <div class="appr-breakdown-box p-3.5 p-md-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3.5 pb-2.5 border-bottom" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <h6 class="fw-bold m-0 d-flex align-items-center gap-2 spk-text-main" style="font-size: 14px;">
                                <i class="bi bi-pie-chart-fill text-warning"></i>
                                <span>Rincian Biaya Pokok Produksi</span>
                            </h6>
                            <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-semibold" style="font-size: 10.5px; background: rgba(59, 130, 246, 0.12); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.25);">
                                <i class="bi bi-magic me-1"></i>Otomatis
                            </span>
                        </div>

                        {{-- A. BIAYA BAHAN BAKU RIIL --}}
                        <div class="appr-cost-tile p-3.5 p-md-4 mb-3.5">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="pe-md-2">
                                    <div class="d-inline-block mb-1.5">
                                        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">
                                            1. BIAYA BAHAN BAKU 
                                        </span>
                                    </div>
                                    <div class="small spk-text-muted" style="font-size: 12px; line-height: 1.5;">Total riil historis bahan baku yang dikeluarkan dari gudang untuk SPK ini.</div>
                                </div>
                                <div class="appr-input-group ms-md-auto flex-shrink-0">
                                    <div class="input-group">
                                        <span class="input-group-text appr-input-prefix">Rp</span>
                                        <div class="form-control appr-input-control text-end d-flex align-items-center justify-content-end text-primary fw-bold" id="appr_display_biaya_bahan" style="cursor: not-allowed; background-color: var(--spk-bg-tertiary, #f1f5f9) !important; min-height: 38px;">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="appr_biaya_bahan_val" value="0">
                        </div>

                        {{-- B. BIAYA TENAGA KERJA (DIRECT LABOR) --}}
                        <div class="appr-cost-tile p-3.5 p-md-4 mb-3.5">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="pe-md-2">
                                    <div class="d-inline-block mb-1.5">
                                        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                                            2. UPAH TENAGA KERJA LANGSUNG
                                        </span>
                                    </div>
                                    <div class="small spk-text-muted" style="font-size: 12px; line-height: 1.5;">Biaya upah / ongkos kerja produksi SPK (otomatis dari tarif upah/SPK).</div>
                                </div>
                                <div class="appr-input-group ms-md-auto flex-shrink-0">
                                    <div class="input-group">
                                        <span class="input-group-text appr-input-prefix">Rp</span>
                                        <input type="text" inputmode="numeric" class="form-control appr-input-control text-end appr-currency-input" id="appr_biaya_tk" name="biaya_tenaga_kerja" value="0" placeholder="0" style="min-height: 38px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- C. BIAYA OVERHEAD PABRIK (FACTORY OVERHEAD) --}}
                        <div class="appr-cost-tile p-3.5 p-md-4 mb-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="pe-md-2">
                                    <div class="d-inline-block mb-1.5">
                                        <span class="badge rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px; background: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3);">
                                            3. BIAYA OVERHEAD PABRIK 
                                        </span>
                                    </div>
                                    <div class="small spk-text-muted" style="font-size: 12px; line-height: 1.5;">Biaya listrik, mesin, kemasan, atau pendukung operasional pabrik.</div>
                                </div>
                                <div class="appr-input-group ms-md-auto flex-shrink-0">
                                    <div class="input-group">
                                        <span class="input-group-text appr-input-prefix">Rp</span>
                                        <input type="text" inputmode="numeric" class="form-control appr-input-control text-end appr-currency-input" id="appr_biaya_overhead" name="biaya_overhead" value="0" placeholder="0" style="min-height: 38px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- D. HASIL PERHITUNGAN HPP PER UNIT FINAL --}}
                        <div class="appr-result-tile p-3.5 p-md-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2.5 border-bottom border-emerald border-opacity-25">
                                <span class="fw-bold text-emerald small d-flex align-items-center gap-1.5 font-monospace">
                                    <i class="bi bi-check2-circle fs-5"></i>
                                    <span>HASIL KALKULASI HPP FINAL:</span>
                                </span>
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace bg-emerald bg-opacity-10 text-emerald border border-emerald border-opacity-25" id="appr_formula_display" style="font-size: 11px;">
                                    Total Biaya / Target Qty
                                </span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="appr-metric-box h-100">
                                        <span class="spk-text-muted small font-monospace fw-semibold d-block mb-1.5" style="font-size: 11px;">TOTAL BIAYA PRODUKSI:</span>
                                        <div class="fw-bold fs-5 spk-text-main font-monospace" id="appr_total_biaya_display">Rp 0</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="appr-metric-box h-100" style="border-color: rgba(16, 185, 129, 0.45) !important;">
                                        <span class="text-emerald fw-bold small font-monospace d-block mb-1.5" style="font-size: 11px;">HPP PER UNIT (SATUAN):</span>
                                        <div class="fw-bold fs-4 text-emerald font-monospace" id="appr_hpp_satuan_display">Rp 0 / Pcs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. JURNAL PREVIEW ALERT --}}
                    <div class="appr-callout-info p-3.5 p-md-4 mb-1 d-flex align-items-start gap-3">
                        <i class="bi bi-info-circle-fill fs-5 text-info flex-shrink-0 mt-0.5"></i>
                        <div class="small" style="line-height: 1.6;">
                            <b class="text-info-emphasis">Otorisasi Otomatis:</b> Setelah disetujui, sistem akan otomatis:
                            <ol class="mb-0 ps-3 mt-2 spk-text-main d-flex flex-column gap-1.5">
                                <li>Menambahkan fisik <b id="appr_alert_qty" class="text-emerald">0 item</b> ke <b>Stok Gudang</b> dengan HPP final.</li>
                                <li>Mencatat mutasi Masuk di <b>Kartu Stok</b>.</li>
                                <li>Memposting <b>Jurnal Akuntansi Resmi</b></li>
                            </ol>
                        </div>
                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center" style="border-color: var(--spk-border-color, #e2e8f0) !important; background-color: var(--spk-card-bg, #ffffff);">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="rejectPbjFromModal()">
                    <i class="bi bi-x-circle"></i>
                    <span>Tolak Pengajuan</span>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btn_submit_approve_hpp" class="btn btn-sm btn-emerald-vibrant rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" onclick="submitApproveHppPbj()">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Hitung HPP & Setujui Masuk Stok</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
