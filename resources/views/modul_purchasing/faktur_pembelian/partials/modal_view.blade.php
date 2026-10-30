<!-- ===== MODAL DETAIL / VIEW FAKTUR PEMBELIAN (CINEMATIC COMMAND DECK) ===== -->
<style>
/* ── CINEMATIC DETAIL INVOICE STYLES ── */
#modalDetailInvoice .modal-content {
    border-radius: 1.25rem !important;
    overflow: hidden !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* Gradient Header */
#modalDetailInvoice .inv-modal-header {
    background: linear-gradient(135deg, #060d1d 0%, #0b1c3a 45%, #172554 100%) !important;
    color: #fff !important;
    padding: 1.15rem 1.75rem !important;
    border-bottom: 1px solid rgba(59, 130, 246, 0.25) !important;
    box-shadow: 0 8px 24px -4px rgba(6, 13, 29, 0.6) !important;
}

#modalDetailInvoice .inv-modal-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25) 0%, rgba(37, 99, 235, 0.12) 100%);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(147, 197, 253, 0.3);
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.25);
    flex-shrink: 0;
}

/* Sticky Action Bar */
#modalDetailInvoice .inv-action-bar {
    position: sticky;
    top: 0;
    z-index: 1020;
    background: var(--erp-card-bg, #ffffff) !important;
    border-bottom: 1px solid var(--erp-border, #e2e8f0) !important;
    padding: 0.75rem 1.5rem !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    backdrop-filter: blur(12px);
    box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.04);
}

/* Cinematic Cards & Hero */
.cinematic-hero-card {
    background: var(--erp-card-bg, #ffffff);
    border: 1px solid var(--erp-border, #e2e8f0);
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease-in-out;
}
[data-bs-theme="dark"] .cinematic-hero-card {
    background: linear-gradient(145deg, #111827 0%, #0f172a 100%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 8px 25px -4px rgba(0, 0, 0, 0.4);
}

.cinematic-vendor-box {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.04) 0%, rgba(241, 245, 249, 0.8) 100%);
    border: 1px solid var(--erp-border, #e2e8f0);
    border-radius: 0.85rem;
    padding: 1.15rem;
}
[data-bs-theme="dark"] .cinematic-vendor-box {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%);
    border-color: rgba(255, 255, 255, 0.08);
}

.cinematic-vendor-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    flex-shrink: 0;
}

.cinematic-cell {
    background: var(--erp-card-bg, #ffffff);
    border: 1px solid var(--erp-border, #e2e8f0);
    border-radius: 0.75rem;
    padding: 0.65rem 0.85rem;
    height: 100%;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.cinematic-cell:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.04);
}
[data-bs-theme="dark"] .cinematic-cell {
    background: rgba(15, 23, 42, 0.6);
    border-color: rgba(255, 255, 255, 0.08);
}

.cinematic-cell-label {
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--erp-text-muted, #64748b);
    display: block;
    margin-bottom: 0.25rem;
}

/* Workflow Ribbon Banner */
.cinematic-workflow-banner {
    border-radius: 0.85rem;
    padding: 0.85rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    border: 1px solid transparent;
    border-left-width: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}
.cinematic-workflow-banner.draft {
    background: rgba(100, 116, 139, 0.08);
    border-color: rgba(100, 116, 139, 0.2);
    border-left-color: #64748b;
}
.cinematic-workflow-banner.pending {
    background: rgba(245, 158, 11, 0.09);
    border-color: rgba(245, 158, 11, 0.25);
    border-left-color: #f59e0b;
}
.cinematic-workflow-banner.approved {
    background: rgba(16, 185, 129, 0.09);
    border-color: rgba(16, 185, 129, 0.25);
    border-left-color: #10b981;
}
.cinematic-workflow-banner.rejected,
.cinematic-workflow-banner.cancelled {
    background: rgba(239, 68, 68, 0.09);
    border-color: rgba(239, 68, 68, 0.25);
    border-left-color: #ef4444;
}

/* Glowing Indicator Pulse */
.inv-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.inv-pulse-dot.pulse-amber {
    background: #f59e0b;
    box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
    animation: inv-pulse-amber 1.8s infinite;
}
.inv-pulse-dot.pulse-green {
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: inv-pulse-green 1.8s infinite;
}
.inv-pulse-dot.pulse-red {
    background: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: inv-pulse-red 1.8s infinite;
}
@keyframes inv-pulse-amber {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
}
@keyframes inv-pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
@keyframes inv-pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Tables inside modal */
#modalDetailInvoice .cinematic-table thead th {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
    color: #94a3b8 !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.06em !important;
    text-transform: uppercase !important;
    padding: 0.85rem 0.75rem !important;
    border-bottom: 2px solid rgba(59, 130, 246, 0.4) !important;
    border-top: none !important;
    vertical-align: middle;
}
[data-bs-theme="light"] #modalDetailInvoice .cinematic-table thead th {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
    color: #334155 !important;
    border-bottom: 2px solid #cbd5e1 !important;
}

/* Grand Total Cinematic Box */
.cinematic-grand-box {
    background: linear-gradient(135deg, #091326 0%, #0f2b5c 50%, #1e40af 100%);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 0.85rem;
    padding: 1.15rem 1.35rem;
    color: #fff;
    box-shadow: 0 6px 20px -2px rgba(15, 23, 42, 0.35);
}
</style>

<div class="modal fade" id="modalDetailInvoice" tabindex="-1" aria-labelledby="modalDetailInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">

            <!-- ── GRADIENT CINEMATIC HEADER ── -->
            <div class="modal-header inv-modal-header border-0 px-3 px-md-4 py-2.5 py-md-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #060d1d 0%, #0b1c3a 45%, #172554 100%) !important; color: #fff;">
                <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0" style="min-width: 0; flex: 1 1 auto; overflow: hidden;">
                    <div class="inv-modal-icon-badge flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bi bi-receipt-cutoff fs-5 text-white"></i>
                    </div>
                    <div style="min-width: 0; overflow: hidden;">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title mb-0 fs-6 fs-md-5 fw-bold text-truncate text-white" id="modalDetailInvoiceLabel">Detail Faktur Pembelian</h5>
                            <span class="badge bg-white bg-opacity-15 text-white border border-white border-opacity-15 rounded-pill px-2 py-0.5 font-monospace d-none d-sm-inline-block" style="font-size:0.65rem; letter-spacing:0.05em;">AP INVOICE</span>
                        </div>
                        <div class="text-subtitle mt-0.5 text-white-50 text-truncate" id="view_nomor_faktur_sub" style="font-size:0.72rem;">Purchase Invoice (AP) — Rincian Tagihan &amp; Pelunasan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                    <div id="view_status_badge"></div>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close" title="Tutup Modal"></button>
                </div>
            </div>

            <!-- ── MODAL BODY ── -->
            <div class="modal-body p-0 bg-body-tertiary" style="overflow-x: hidden !important; max-width: 100%; min-width: 0; touch-action: pan-y;">

                <!-- ── STICKY ACTION BAR ── -->
                <div class="inv-action-bar">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left"></i>
                            <span>Tutup</span>
                        </button>
                        <div class="vr mx-1 d-none d-sm-block"></div>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold shadow-xs" id="view_btn_edit_trigger" style="display:none;">
                            <i class="bi bi-pencil-square me-1"></i> Edit Faktur
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="view_btn_submit_approval_trigger" style="display:none;">
                            <i class="bi bi-send-fill me-1"></i> Ajukan ke Atasan
                        </button>
                        @php
                            $userCanApproveInvoice = false;
                            try {
                                $userCanApproveInvoice = auth()->user() && auth()->user()->hasPermissionTo('approve-purchase-invoice');
                            } catch (\Throwable $e) {
                                $userCanApproveInvoice = false;
                            }
                        @endphp
                        @if($userCanApproveInvoice)
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="view_btn_approve_trigger" style="display:none;">
                            <i class="bi bi-check-circle-fill me-1"></i> Setujui &amp; Posting (ACC)
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="view_btn_reject_trigger" style="display:none;">
                            <i class="bi bi-x-circle me-1"></i> Tolak
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="view_btn_wa_trigger" style="display:none;">
                            <i class="bi bi-whatsapp me-1"></i> Kirim Ulang WA
                        </button>
                        @if(auth()->user()?->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()?->can('batalkan-purchase-invoice'))
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-xs" id="view_btn_cancel_trigger" style="display:none;">
                            <i class="bi bi-x-octagon me-1"></i> Batalkan Transaksi
                        </button>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <a href="#" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-2" id="view_btn_print_pdf">
                            <i class="bi bi-printer-fill fs-6"></i>
                            <span>Cetak Bukti Faktur (PDF)</span>
                        </a>
                    </div>
                </div>

                <!-- ── MAIN CONTENT CONTAINER ── -->
                <div class="p-3 p-md-4" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">

                    <!-- APPROVAL STATUS BANNER CONTAINER -->
                    <div id="view_approval_banner_container"></div>

                    <!-- ── 1. CINEMATIC HERO COMMAND DECK (BENTO DOSSIER) ── -->
                    <div class="row g-3 mb-3 mb-md-4">
                        
                        <!-- CARD KIRI: VENDOR & IDENTITAS DOKUMEN (7 COLS) -->
                        <div class="col-12 col-lg-7">
                            <div class="cinematic-hero-card h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Vendor Profile Header -->
                                    <div class="cinematic-vendor-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                            <div class="d-flex align-items-center gap-3 overflow-hidden">
                                                <div class="cinematic-vendor-avatar">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-0.5 font-monospace" style="font-size:0.65rem;">VENDOR / SUPPLIER</span>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-0.5 font-monospace" style="font-size:0.65rem;">DOKUMEN SAH</span>
                                                    </div>
                                                    <h5 class="fw-bold text-body text-truncate mb-1" id="view_disp_supplier_name" title="Nama Supplier">-</h5>
                                                    <div id="view_disp_supplier_npwp">
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle font-monospace px-2 py-0.5">NPWP: -</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4-Cell Telemetry Grid -->
                                    <div class="row g-2 mb-2">
                                        <div class="col-6 col-sm-3">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-hash me-1"></i>Faktur Internal</span>
                                                <span class="fw-bold text-primary font-monospace text-truncate d-block" style="font-size:0.84rem;" id="view_disp_nomor_faktur">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-receipt me-1"></i>No. Faktur Supplier</span>
                                                <span class="fw-bold text-body font-monospace text-truncate d-block" style="font-size:0.84rem;" id="view_disp_faktur_supplier">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-box-seam me-1"></i>No. Penerimaan (GRN)</span>
                                                <strong class="text-info font-monospace text-truncate d-block" style="font-size:0.82rem;" id="view_disp_ref_gr">-</strong>
                                            </div>
                                        </div>
                                        <div class="col-6 col-sm-3">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-cart-check me-1"></i>Ref. Purchase Order</span>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace text-truncate d-block" style="font-size:0.75rem;" id="view_disp_ref_po">PO: -</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Proyek Tender (Jika Ada) -->
                                <div id="view_container_tender" style="display:none;" class="mt-2">
                                    <div class="p-2 px-2.5 rounded-3 border border-warning-subtle bg-warning bg-opacity-10 d-flex align-items-center justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <i class="bi bi-briefcase-fill text-warning-emphasis fs-6 flex-shrink-0"></i>
                                            <div class="text-truncate">
                                                <span class="extra-small text-warning-emphasis fw-bold text-uppercase me-1" style="font-size:0.67rem;">PROYEK TENDER:</span>
                                                <span class="badge bg-warning text-dark font-monospace text-truncate" style="font-size:0.75rem;" id="view_disp_tender">-</span>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark font-monospace extra-small px-2 py-0.5 flex-shrink-0">TENDER</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD KANAN: JADWAL, TERMIN & LAMPIRAN (5 COLS) -->
                        <div class="col-12 col-lg-5">
                            <div class="cinematic-hero-card h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-1.5 rounded-2 bg-warning bg-opacity-10 text-warning-emphasis">
                                                <i class="bi bi-calendar-check fs-6"></i>
                                            </div>
                                            <span class="fw-bold text-body" style="font-size:0.85rem;">JADWAL &amp; KETENTUAN BAYAR</span>
                                        </div>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-2 py-0.5 font-monospace" style="font-size:0.65rem;">FINANCIAL TERMS</span>
                                    </div>

                                    <!-- Telemetry Schedule Grid -->
                                    <div class="row g-2 mb-2.5">
                                        <div class="col-6">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-calendar-event me-1"></i>Tgl Faktur Tagihan</span>
                                                <span class="fw-semibold text-body d-block" style="font-size:0.85rem;" id="view_disp_tanggal_faktur">-</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="cinematic-cell border-warning-subtle bg-warning bg-opacity-10">
                                                <span class="cinematic-cell-label text-warning-emphasis fw-bold"><i class="bi bi-clock-history me-1"></i>Jatuh Tempo</span>
                                                <span class="fw-bold text-warning-emphasis font-monospace d-block" style="font-size:0.85rem;" id="view_disp_tanggal_jatuh_tempo">-</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-credit-card-2-front me-1"></i>Syarat Pembayaran / Pajak</span>
                                                <span class="fw-semibold text-body d-block text-truncate" style="font-size:0.82rem;" id="view_disp_term_pajak">-</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="cinematic-cell">
                                                <span class="cinematic-cell-label"><i class="bi bi-currency-exchange me-1"></i>Mata Uang &amp; Kurs Transaksi</span>
                                                <span class="fw-semibold text-body font-monospace d-block" style="font-size:0.82rem;" id="view_disp_currency_kurs">IDR (Kurs: 1.0000)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dokumen Bukti Lampiran -->
                                <div id="view_container_lampiran">
                                    <div id="view_disp_lampiran_content">
                                        <div class="p-2 rounded-3 bg-body-tertiary border text-center text-muted small fst-italic"><i class="bi bi-file-earmark-x me-1"></i>Tidak ada berkas diunggah</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FULL-WIDTH: CATATAN / KETERANGAN FAKTUR (JIKA ADA) -->
                        <div class="col-12" id="view_container_keterangan">
                            <div class="p-2.5 px-3 rounded-3 bg-body border d-flex align-items-center gap-2.5 shadow-xs">
                                <div class="p-1.5 rounded-2 bg-secondary bg-opacity-10 text-secondary flex-shrink-0">
                                    <i class="bi bi-chat-quote fs-6"></i>
                                </div>
                                <div class="overflow-hidden flex-grow-1">
                                    <span class="extra-small text-muted fw-bold text-uppercase d-block" style="font-size:0.65rem; letter-spacing:0.04em;">Catatan / Keterangan Tagihan:</span>
                                    <div class="small text-body text-truncate fst-italic" id="view_disp_keterangan">-</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ── 2. TABEL RINCIAN ITEM BARANG DITERIMA ── -->
                    <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                        <div class="erp-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-box-seam-fill text-primary"></i>
                                <h6 class="fw-bold mb-0 text-body">Rincian Item Barang Diterima</h6>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;">
                                Barang Diterima (PO / GR)
                            </span>
                        </div>
                        <!-- Mobile Swipe Hint -->
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-tertiary border-bottom d-md-none text-muted" style="font-size: 0.72rem;">
                            <span><i class="bi bi-arrows-expand me-1 text-primary"></i>Geser tabel ke samping untuk melihat seluruh rincian</span>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">11 Kolom</span>
                        </div>
                        <div class="card-body p-0" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                            <div class="table-responsive inv-table-responsive" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                                <table class="table align-middle table-hover mb-0 cinematic-table inv-modern-table" id="tableViewItems" style="min-width: 1450px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 45px;" class="text-center">#</th>
                                            <th style="width: 110px;"><i class="bi bi-upc-scan me-1 opacity-75"></i> Kode</th>
                                            <th style="min-width: 180px;"><i class="bi bi-box-seam me-1 opacity-75"></i> Nama Barang</th>
                                            <th style="width: 75px;" class="text-center"><i class="bi bi-hash me-1 opacity-75"></i> Qty</th>
                                            <th style="width: 85px;" class="text-center">Satuan</th>
                                            <th style="min-width: 135px;" class="text-end"><i class="bi bi-tag me-1 opacity-75"></i> Harga Satuan</th>
                                            <th style="min-width: 105px;" class="text-end">Diskon</th>
                                            <th style="min-width: 130px;" class="text-center">PPN</th>
                                            <th style="min-width: 135px;" class="text-end"><i class="bi bi-wallet2 me-1 opacity-75"></i> Subtotal Net</th>
                                            <th style="min-width: 150px;" class="text-end text-primary"><i class="bi bi-truck me-1 opacity-75"></i> Alokasi Biaya (HPP)</th>
                                            <th style="min-width: 155px;" class="text-end text-success"><i class="bi bi-graph-up-arrow me-1 opacity-75"></i> HPP Akhir / Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_items_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ── 3. BIAYA TAMBAHAN & PPN ── -->
                    <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" id="view_card_extra_costs" style="display:none; overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                        <div class="erp-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-primary"></i>
                                <h6 class="fw-bold mb-0 text-body">Biaya Tambahan &amp; Pajak Terkait</h6>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size:0.7rem;">
                                Additional Charges
                            </span>
                        </div>
                        <!-- Mobile Swipe Hint -->
                        <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-body-tertiary border-bottom d-md-none text-muted" style="font-size: 0.72rem;">
                            <span><i class="bi bi-arrows-expand me-1 text-primary"></i>Geser tabel ke samping untuk rincian biaya</span>
                        </div>
                        <div class="card-body p-0" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                            <div class="table-responsive inv-table-responsive" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                                <table class="table align-middle table-hover mb-0 cinematic-table inv-modern-table" id="tableViewExtraCosts" style="min-width: 780px;">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 250px;">Jenis Biaya / Pajak</th>
                                            <th style="width: 140px;" class="text-center">Alokasi HPP</th>
                                            <th style="min-width: 240px;">Akun Akuntansi (COA)</th>
                                            <th style="min-width: 150px;" class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_extra_costs_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ── 4. RIWAYAT PEMBAYARAN UTANG ── -->
                    <div class="erp-card mb-3 mb-md-4 bg-body shadow-sm" id="view_card_payments" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                        <div class="erp-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack text-success"></i>
                                <h6 class="fw-bold mb-0 text-body">Riwayat Pembayaran Utang</h6>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace" id="view_pay_count_badge">
                                0 Pembayaran
                            </span>
                        </div>
                        <div class="card-body p-0" style="overflow-x: hidden !important; max-width: 100%; min-width: 0;">
                            <div class="table-responsive inv-table-responsive" style="overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                                <table class="table align-middle table-hover mb-0 cinematic-table inv-modern-table" id="tableViewPayments" style="min-width: 780px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">No. Bukti Bayar</th>
                                            <th style="width: 130px;">Tanggal</th>
                                            <th style="min-width: 200px;">Kas / Bank</th>
                                            <th style="min-width: 150px;" class="text-end">Jumlah Bayar</th>
                                            <th style="width: 130px;" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_payments_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ── 5. BOTTOM SECTION: STATUS PEMBAYARAN, RINGKASAN NILAI & JURNAL ── -->
                    <div class="row g-3 g-md-4 mt-1">

                        <!-- STATUS PEMBAYARAN & SISA HUTANG -->
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="erp-card bg-body shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="erp-card-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-wallet2 text-success"></i>
                                            <h6 class="fw-bold mb-0 text-body" style="font-size:0.85rem;">STATUS PEMBAYARAN UTANG</h6>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-0.5 font-monospace" style="font-size:0.7rem;">
                                            Cashflow
                                        </span>
                                    </div>
                                    <div class="card-body p-3 p-md-4">
                                        <!-- Pelunasan Progress Bar -->
                                        <div class="mb-3 p-2.5 rounded-3 bg-body-tertiary border">
                                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                                <span class="extra-small fw-semibold text-muted text-uppercase" style="font-size:0.68rem; letter-spacing:0.04em;">Tingkat Pelunasan</span>
                                                <span class="badge bg-secondary bg-opacity-10 text-body font-monospace fw-bold" id="view_pay_progress_text" style="font-size:0.72rem;">0%</span>
                                            </div>
                                            <div class="progress" style="height: 6px; border-radius: 9999px; background: rgba(0,0,0,0.06);">
                                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="view_pay_progress_bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted small">Total Tagihan</span>
                                            <span class="fw-bold text-body font-monospace" id="view_pay_tagihan">Rp 0,00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" id="view_pay_row_dp" style="display:none;">
                                            <div>
                                                <span class="text-muted small"><i class="bi bi-wallet2 text-success me-1"></i>Uang Muka (DP)</span>
                                                <div class="extra-small text-muted font-monospace" id="view_pay_kas_bank_info" style="font-size:0.7rem; display:none;"></div>
                                            </div>
                                            <span class="fw-bold text-success font-monospace" id="view_pay_uang_muka">Rp 0,00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted small">Sudah Dibayar</span>
                                            <span class="fw-bold text-success font-monospace" id="view_pay_terbayar">Rp 0,00</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted small">Potongan Pembayaran</span>
                                            <span class="fw-bold text-info font-monospace" id="view_pay_potongan">Rp 0,00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 p-md-4 pt-0">
                                    <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning-subtle d-flex justify-content-between align-items-center shadow-xs">
                                        <div>
                                            <span class="extra-small text-warning-emphasis fw-bold text-uppercase d-block" style="font-size:0.68rem; letter-spacing:0.04em;">Sisa Hutang Tagihan</span>
                                            <div class="fw-bolder fs-5 text-warning-emphasis font-monospace" id="view_pay_sisa">Rp 0,00</div>
                                        </div>
                                        <div class="p-2 rounded-circle bg-warning bg-opacity-20 text-warning-emphasis">
                                            <i class="bi bi-hourglass-bottom fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RINGKASAN NILAI FAKTUR -->
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="erp-card bg-body shadow-sm h-100">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-receipt text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body" style="font-size:0.85rem;">RINGKASAN NILAI FAKTUR</h6>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-0.5 font-monospace" style="font-size:0.7rem;">
                                        Rekap Finansial
                                    </span>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div class="calc-row">
                                        <span class="calc-label">Subtotal Barang (Gross)</span>
                                        <div class="text-end">
                                            <span class="calc-value text-body" id="view_sum_subtotal">0,00</span>
                                            <small class="text-muted font-monospace d-block extra-small" id="view_sum_subtotal_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">Total Diskon Item</span>
                                        <div class="text-end">
                                            <span class="calc-value text-danger" id="view_sum_diskon">0,00</span>
                                            <small class="text-danger font-monospace d-block extra-small" id="view_sum_diskon_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label fw-bold">DPP (Net Barang)</span>
                                        <div class="text-end">
                                            <span class="calc-value text-primary fw-bold" id="view_sum_dpp">0,00</span>
                                            <small class="text-primary font-monospace d-block extra-small" id="view_sum_dpp_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label" id="view_label_ppn">PPN Masukan</span>
                                        <div class="text-end">
                                            <span class="calc-value text-body" id="view_sum_ppn">0,00</span>
                                            <small class="text-muted font-monospace d-block extra-small" id="view_sum_ppn_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">Total Biaya Tambahan</span>
                                        <div class="text-end">
                                            <span class="calc-value text-body" id="view_sum_biaya_lain">0,00</span>
                                            <small class="text-muted font-monospace d-block extra-small" id="view_sum_biaya_lain_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row" id="view_row_pph" style="display:none;">
                                        <span class="calc-label"><span id="view_label_pph_title">Potongan PPh</span> <small class="text-muted" id="view_label_pph_tarif"></small></span>
                                        <div class="text-end">
                                            <span class="calc-value text-warning" id="view_sum_pph">0,00</span>
                                            <small class="text-warning font-monospace d-block extra-small" id="view_sum_pph_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="calc-row" id="view_row_uang_muka" style="display:none;">
                                        <span class="calc-label text-success fw-medium"><i class="bi bi-wallet2 me-1"></i>Uang Muka (DP)</span>
                                        <div class="text-end">
                                            <span class="calc-value text-success font-monospace" id="view_sum_uang_muka">- 0,00</span>
                                            <small class="text-success font-monospace d-block extra-small" id="view_sum_uang_muka_idr" style="display:none; font-size:0.7rem;"></small>
                                        </div>
                                    </div>
                                    <div class="cinematic-grand-box mt-3">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>
                                                <div class="label text-white-50 extra-small fw-bold text-uppercase" id="view_label_total_tagihan" style="letter-spacing:0.06em;">TOTAL TAGIHAN (GRAND TOTAL)</div>
                                                <div class="value fs-4 fw-bold font-monospace text-white" id="view_sum_total_invoice">Rp 0,00</div>
                                                <div id="view_box_sisa_tagihan" class="mt-2 pt-2 border-top border-white border-opacity-25" style="display:none;">
                                                    <div class="label text-warning-emphasis fw-bold" style="font-size:0.75rem;"><i class="bi bi-exclamation-circle me-1"></i>Sisa Tagihan Hutang (Net AP)</div>
                                                    <div class="fs-4 fw-bolder text-warning font-monospace" id="view_sum_sisa_tagihan">Rp 0,00</div>
                                                </div>
                                            </div>
                                            <div class="text-end p-2 px-3 rounded-3" id="view_box_total_idr" style="display:none; background: rgba(0, 0, 0, 0.28); border: 1px solid rgba(255, 255, 255, 0.18);">
                                                <div class="label text-warning-emphasis fw-bold" style="font-size:0.68rem; letter-spacing:0.5px;"><i class="bi bi-cash-stack me-1"></i>TOTAL EKUIVALEN (IDR)</div>
                                                <div class="fs-5 fw-bold text-warning font-monospace" id="view_sum_total_invoice_idr">Rp 0,00</div>
                                                <div class="extra-small text-white-50 font-monospace" id="view_sum_kurs_info" style="font-size:0.65rem;">Kurs: 1,00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INFORMASI JURNAL AKUNTANSI OTOMATIS -->
                        <div class="col-12 col-xl-4">
                            <div class="erp-card bg-body shadow-sm h-100" id="view_card_jurnal">
                                <div class="erp-card-header d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-journal-bookmark-fill text-primary"></i>
                                        <h6 class="fw-bold mb-0 text-body" style="font-size:0.85rem;">JURNAL AKUNTANSI</h6>
                                    </div>
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-2.5 py-0.5 font-monospace fw-semibold d-inline-flex align-items-center gap-1 shadow-xs btn-open-modal-jurnal-pi d-none" id="view_btn_open_jurnal_header" title="Klik untuk membuka voucher jurnal akuntansi lengkap" style="font-size: 0.72rem;">
                                        <i class="bi bi-arrows-fullscreen"></i>
                                        <span>Buku Besar (GL)</span>
                                    </button>
                                    <span class="badge bg-info bg-opacity-10 text-info border px-2 py-0.5 font-monospace" id="view_badge_gl_default" style="font-size:0.7rem;">
                                        Buku Besar (GL)
                                    </span>
                                </div>
                                <div class="card-body p-3 p-md-4">
                                    <div id="view_jurnal_content">
                                        <div class="small text-muted text-center py-3">Belum ada jurnal diterbitkan (Status Draft).</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
