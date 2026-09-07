{{-- ===== MODAL DETAIL TENDER (DUAL THEME ADAPTIVE: LIGHT & DARK) ===== --}}
<style>
    .modal-custom-xxl {
        max-width: 1580px !important;
        width: 96vw !important;
    }
    @media (min-width: 1600px) {
        .modal-custom-xxl {
            max-width: 1640px !important;
        }
    }

    /* ═══════════════════════════════════════════════════════════
       LIGHT THEME (Default) - Crisp, Clean, High Contrast
       ═══════════════════════════════════════════════════════════ */
    .tender-modal-cinematic {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.15), 0 0 30px rgba(59, 130, 246, 0.06) !important;
        color: #0f172a !important;
    }
    .tender-modal-cinematic .modal-header,
    .tender-modal-cinematic .modal-footer {
        background: #ffffff !important;
        border-color: #f1f5f9 !important;
    }
    .tender-banner-cinematic {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
    }
    .tender-banner-cinematic h3,
    .tender-banner-cinematic .tender-title {
        color: #0f172a !important;
    }
    .tender-banner-cinematic .tender-customer-text {
        color: #334155 !important;
    }
    .tender-card-metric {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        padding: 16px 20px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03) !important;
        transition: all 0.25s ease;
    }
    .tender-card-metric:hover {
        transform: translateY(-2px);
        border-color: #93c5fd !important;
        box-shadow: 0 8px 24px -4px rgba(59, 130, 246, 0.12) !important;
    }
    .tender-card-metric .metric-label {
        color: #64748b !important;
    }
    .tender-card-metric .metric-value {
        color: #0f172a !important;
    }
    .tender-workflow-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
    }
    .tender-pagu-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
        border: 1px solid #e2e8f0 !important;
    }
    .tender-rab-table-cinematic {
        background: #ffffff !important;
        border-radius: 14px;
        overflow: hidden;
    }
    .tender-rab-table-cinematic th {
        background: #f8fafc !important;
        text-transform: uppercase;
        font-size: 0.73rem;
        letter-spacing: 0.06em;
        padding: 14px 16px;
        color: #475569 !important;
        font-weight: 700;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .tender-rab-table-cinematic td {
        padding: 14px 16px;
        color: #1e293b !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background: transparent !important;
    }
    .tender-rab-table-cinematic tr:hover td {
        background: #f8fafc !important;
    }

    /* ═══════════════════════════════════════════════════════════
       DARK THEME - Sleek Slate Glassmorphism & High Legibility
       ═══════════════════════════════════════════════════════════ */
    [data-bs-theme="dark"] .tender-modal-cinematic {
        background: radial-gradient(120% 120% at 50% 0%, #171f2e 0%, #0d121c 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.8), 0 0 40px rgba(59, 130, 246, 0.12) !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .tender-modal-cinematic .modal-header,
    [data-bs-theme="dark"] .tender-modal-cinematic .modal-footer {
        background: transparent !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] .tender-banner-cinematic {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.02) 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        backdrop-filter: blur(12px);
    }
    [data-bs-theme="dark"] .tender-banner-cinematic h3,
    [data-bs-theme="dark"] .tender-banner-cinematic .tender-title {
        color: #ffffff !important;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }
    [data-bs-theme="dark"] .tender-banner-cinematic .tender-customer-text {
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .tender-card-metric {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.015) 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2) !important;
    }
    [data-bs-theme="dark"] .tender-card-metric:hover {
        border-color: rgba(96, 165, 250, 0.45) !important;
        box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.5) !important;
    }
    [data-bs-theme="dark"] .tender-card-metric .metric-label {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .tender-card-metric .metric-value {
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .tender-workflow-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.015) 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25) !important;
    }
    [data-bs-theme="dark"] .tender-pagu-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    [data-bs-theme="dark"] .tender-rab-table-cinematic {
        background: transparent !important;
    }
    [data-bs-theme="dark"] .tender-rab-table-cinematic th {
        background: rgba(255, 255, 255, 0.04) !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    [data-bs-theme="dark"] .tender-rab-table-cinematic td {
        color: #f1f5f9 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    }
    [data-bs-theme="dark"] .tender-rab-table-cinematic tr:hover td {
        background: rgba(255, 255, 255, 0.03) !important;
    }

    /* ═══════════════════════════════════════════════════════════
       STEPPER NODES (Theme Adaptive)
       ═══════════════════════════════════════════════════════════ */
    .stepper-node {
        opacity: 0.45;
        transition: all 0.25s ease;
    }
    .stepper-node.active {
        opacity: 1;
    }
    .stepper-node.completed {
        opacity: 0.95;
    }
    .stepper-text {
        font-weight: 700;
        font-size: 0.82rem;
    }
    .stepper-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
        border: 1.5px solid rgba(100, 116, 139, 0.25);
    }
    [data-bs-theme="dark"] .stepper-dot {
        background: rgba(255, 255, 255, 0.06);
        color: #94a3b8;
        border-color: rgba(255, 255, 255, 0.15);
    }
    .stepper-node.active .stepper-dot {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #60a5fa !important;
        box-shadow: 0 0 14px rgba(37, 99, 235, 0.5) !important;
    }
    .stepper-node.completed .stepper-dot {
        background: #10b981 !important;
        color: #ffffff !important;
        border-color: #34d399 !important;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.35) !important;
    }
    .stepper-node.rejected .stepper-dot {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #f87171 !important;
        box-shadow: 0 0 10px rgba(239, 68, 68, 0.35) !important;
    }
    .hover-scale {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-scale:hover {
        transform: scale(1.04);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }
</style>

<div class="modal modal-blur fade" id="modalDetailTender" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-custom-xxl modal-dialog-centered">
        <div class="modal-content tender-modal-content tender-modal-cinematic rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold font-monospace shadow-xs" id="detail_nomor_kontrak">
                        TND-000
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill shadow-xs" id="detail_entitas_badge">
                        <i class="bi bi-building me-1"></i> PT (Pusat)
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill shadow-xs" id="detail_kategori_instansi">
                        Pemerintah / B2G
                    </span>
                    <span class="badge bg-secondary px-3 py-1.5 rounded-pill shadow-xs" id="detail_status_badge">
                        Draft
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill shadow-xs" id="detail_priority_badge">
                        ⚡ Sedang (Medium)
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-2.5">
                <div class="row g-3.5">
                    
                    <!-- Proyek Title & Summary Banner (Ultra-Wide) -->
                    <div class="col-12">
                        <div class="p-4 rounded-4 tender-banner-cinematic d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 shadow-sm">
                            <div>
                                <h3 class="fw-black mb-1.5 tender-title text-truncate" id="detail_nama_tender" style="letter-spacing: -0.02em;">-</h3>
                                <div class="d-flex align-items-center gap-2 fs-6">
                                    <span class="p-1 px-2 rounded-3 bg-primary bg-opacity-10 text-primary font-monospace small"><i class="bi bi-building"></i></span>
                                    <span id="detail_customer" class="fw-bold tender-customer-text">-</span>
                                </div>
                            </div>
                            <div class="text-lg-end tender-pagu-card p-3 px-4 rounded-4 border shadow-xs">
                                <div class="text-success small fw-bold text-uppercase font-monospace" style="font-size:0.75rem; letter-spacing: 0.08em;">TOTAL ESTIMASI NILAI PROYEK:</div>
                                <h1 class="fw-black text-success font-monospace mb-0 display-6" id="detail_nilai_estimasi">Rp 0</h1>
                            </div>
                        </div>
                    </div>

                    <!-- WORKFLOW STEPPER & ACTION CONTROL CARD (CINEMATIC) -->
                    <div class="col-12">
                        <div class="tender-workflow-card p-3.5 shadow-xs" id="detail_workflow_card">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 pb-3 border-bottom">
                                <!-- Stepper Nodes -->
                                <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap flex-grow-1" id="tender_stepper_container">
                                    <div class="stepper-node d-flex align-items-center gap-2" id="step_draft">
                                        <span class="stepper-dot"><i class="bi bi-pencil-fill"></i></span>
                                        <span class="stepper-text">1. Draft</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small opacity-50"></i>
                                    <div class="stepper-node d-flex align-items-center gap-2" id="step_approval">
                                        <span class="stepper-dot"><i class="bi bi-shield-check"></i></span>
                                        <span class="stepper-text">2. Approval Atasan</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small opacity-50"></i>
                                    <div class="stepper-node d-flex align-items-center gap-2" id="step_sent">
                                        <span class="stepper-dot"><i class="bi bi-send-fill"></i></span>
                                        <span class="stepper-text">3. Kirim Lelang</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small opacity-50"></i>
                                    <div class="stepper-node d-flex align-items-center gap-2" id="step_negotiation">
                                        <span class="stepper-dot"><i class="bi bi-chat-dots-fill"></i></span>
                                        <span class="stepper-text">4. Aanwijzing & Nego</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small opacity-50"></i>
                                    <div class="stepper-node d-flex align-items-center gap-2" id="step_decision">
                                        <span class="stepper-dot"><i class="bi bi-trophy-fill"></i></span>
                                        <span class="stepper-text">5. Keputusan Final</span>
                                    </div>
                                </div>

                                <!-- Dynamic Action Buttons for current state -->
                                <div class="d-flex align-items-center gap-2 flex-wrap" id="detail_workflow_actions">
                                    <!-- Populated dynamically based on status & permissions -->
                                </div>
                            </div>

                            <!-- Contextual Banner / Notification for Current Stage -->
                            <div class="pt-3" id="detail_workflow_status_banner">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- Wide Cinematic Metric Cards Grid (4 Columns) -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-3">
                            <span class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing: 0.05em;">TANGGAL MULAI</div>
                                <div class="metric-value fw-bold fs-5 font-monospace text-truncate" id="detail_tanggal">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-3 border-danger border-opacity-25">
                            <span class="p-3 rounded-3 bg-danger bg-opacity-10 text-danger fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                                <i class="bi bi-calendar-x"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="text-danger small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing: 0.05em;">BATAS DEADLINE</div>
                                <div class="fw-bold fs-5 text-danger font-monospace text-truncate" id="detail_deadline">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-3">
                            <span class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                                <i class="bi bi-hourglass-split"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing: 0.05em;">ESTIMASI CLOSING</div>
                                <div class="metric-value fw-bold fs-5 font-monospace text-truncate" id="detail_estimasi_closing">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-3">
                            <span class="p-3 rounded-3 bg-info bg-opacity-10 text-info fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 52px; height: 52px;">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.7rem; letter-spacing: 0.05em;">SALES PIC / OWNER</div>
                                <div class="metric-value fw-bold fs-5 text-truncate" id="detail_sales">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tabs for Sub-relations -->
                    <div class="col-12 mt-2">
                        <ul class="nav nav-tabs nav-tabs-custom mb-3" id="detailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold px-4 py-2.5" id="detail-item-tab" data-bs-toggle="tab" data-bs-target="#detail-item" type="button" role="tab">
                                    <i class="bi bi-list-stars me-1.5"></i> 1. Rincian Item / Spesifikasi Kebutuhan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-4 py-2.5" id="detail-dokumen-tab" data-bs-toggle="tab" data-bs-target="#detail-dokumen" type="button" role="tab">
                                    <i class="bi bi-paperclip me-1.5"></i> 2. Berkas Dokumen Lelang
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-4 py-2.5" id="detail-followup-tab" data-bs-toggle="tab" data-bs-target="#detail-followup" type="button" role="tab">
                                    <i class="bi bi-journal-text me-1.5"></i> 3. Catatan & Log Aktivitas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-4 py-2.5" id="detail-approval-tab" data-bs-toggle="tab" data-bs-target="#detail-approval" type="button" role="tab">
                                    <i class="bi bi-shield-check me-1.5"></i> 4. Persetujuan (Approval)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="detailTabsContent">
                            
                            <!-- TAB 1: ITEM SPESIFIKASI & RAB -->
                            <div class="tab-pane fade show active" id="detail-item" role="tabpanel">
                                <div class="table-responsive border rounded-4 tender-workflow-card mb-3.5 shadow-xs overflow-hidden">
                                    <table class="table align-middle mb-0 tender-rab-table-cinematic">
                                        <thead>
                                            <tr>
                                                <th width="6%" class="text-center">No</th>
                                                <th width="64%">Nama Barang / Jasa & Spesifikasi Kebutuhan</th>
                                                <th class="text-center" width="15%">Kuantiti (Qty)</th>
                                                <th class="text-center" width="15%">Satuan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detail_items_tbody">
                                            <!-- Dynamic via JS -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- RINGKASAN PAGU & STATUS PENAWARAN -->
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="p-4 rounded-4 border tender-pagu-card shadow-xs h-100">
                                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                                <span class="fw-bold small d-flex align-items-center gap-1.5">
                                                    <i class="bi bi-cash-stack text-success"></i> ESTIMASI PAGU ANGGARAN / HPS
                                                </span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1 rounded-pill" id="detail_currency_badge">IDR</span>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Pagu HPS Panitia Lelang:</span>
                                                    <span class="fw-extrabold font-monospace text-success fs-5" id="detail_grand_total_label">Rp 0</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Total Kebutuhan Item:</span>
                                                    <span class="fw-bold font-monospace" id="detail_total_items_count">0 Item</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: LAMPIRAN BERKAS DOKUMEN -->
                            <div class="tab-pane fade" id="detail-dokumen" role="tabpanel">
                                <div id="detail_documents_list" class="d-flex flex-column gap-2.5">
                                    <!-- Dynamic via JS -->
                                </div>
                            </div>

                            <!-- TAB 3: LOG FOLLOW-UP -->
                            <div class="tab-pane fade" id="detail-followup" role="tabpanel">
                                <div class="card p-4 border rounded-4 mb-3.5 tender-workflow-card shadow-xs">
                                    <h6 class="fw-bold small mb-3"><i class="bi bi-plus-circle-fill text-primary me-1.5"></i> Tambah Catatan Aktivitas / Follow-Up Baru</h6>
                                    <form id="form_add_followup">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <input type="text" id="followup_aktivitas" class="form-control tender-input" placeholder="Judul aktivitas (mis: Aanwijzing Kantor...)" required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" id="followup_keterangan" class="form-control tender-input" placeholder="Catatan hasil pertemuan/progress..." required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold h-100 shadow-xs">Kirim</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div id="detail_followup_timeline" class="d-flex flex-column gap-2.5">
                                    <!-- Timeline items -->
                                </div>
                            </div>

                            <!-- TAB 4: APPROVAL LOG -->
                            <div class="tab-pane fade" id="detail-approval" role="tabpanel">
                                <div id="detail_approval_history">
                                    <!-- Approvals -->
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end align-items-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

{{-- ===== MODAL POPUP DETAIL KOMPONEN BUNDLE ===== --}}
<div class="modal modal-blur fade" id="modalDetailKomponenBundle" tabindex="-1" aria-hidden="true" style="z-index: 99998 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tender-modal-content border-0 shadow-2xl overflow-hidden rounded-4">
            
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2.5 rounded-3 bg-warning-subtle text-warning d-inline-flex align-items-center shadow-sm">
                        <i class="bi bi-boxes fs-4"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-black mb-0 text-body" id="bundle_modal_title">Rincian Komponen Paket</h5>
                        <div class="text-muted small font-monospace" id="bundle_modal_sku" style="font-size:0.75rem;">SKU: BDL-000</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-3">
                <div class="p-3 rounded-4 bg-primary-subtle border border-primary-subtle d-flex align-items-center justify-content-between mb-3.5 shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                        <span class="small fw-bold text-primary">Daftar item / bahan jadi yang menyusun 1 unit paket bundle ini:</span>
                    </div>
                    <span class="badge bg-primary text-white font-monospace px-3 py-1.5 rounded-pill" id="bundle_modal_count">0 Komponen</span>
                </div>

                <div class="row g-3" id="bundle_modal_components_grid">
                    <!-- Dynamic component cards -->
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 pb-3.5 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

{{-- ===== MODAL PREVIEW FOTO BARANG (CAROUSEL LIGHTBOX) ===== --}}
<div class="modal modal-blur fade" id="modalPreviewFotoBarang" tabindex="-1" aria-hidden="true" style="z-index: 100000 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content tender-modal-content border-0 shadow-2xl overflow-hidden rounded-4">
            
            <div class="modal-header border-0 pb-2 pt-3.5 px-4 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center shadow-xs">
                        <i class="bi bi-images fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-black mb-0 text-body" id="preview_foto_nama">-</h5>
                        <div class="text-muted small font-monospace" id="preview_foto_sku" style="font-size:0.75rem;">SKU: -</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2.5">
                    <span class="badge bg-dark-subtle text-body font-monospace px-3 py-1.5 rounded-pill" id="preview_foto_counter">1 / 1</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4 text-center bg-dark bg-opacity-75 position-relative">
                <div id="carouselPreviewBarang" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner rounded-3 bg-black bg-opacity-40" id="carousel_preview_inner" style="min-height: 380px;">
                        <!-- Dynamic carousel items -->
                    </div>
                    
                    <!-- Carousel Controls -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPreviewBarang" data-bs-slide="prev" id="preview_btn_prev" style="width: 12%;">
                        <span class="p-2.5 rounded-circle bg-dark bg-opacity-50 text-white d-inline-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-chevron-left fs-4"></i>
                        </span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselPreviewBarang" data-bs-slide="next" id="preview_btn_next" style="width: 12%;">
                        <span class="p-2.5 rounded-circle bg-dark bg-opacity-50 text-white d-inline-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-chevron-right fs-4"></i>
                        </span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                <!-- Thumbnail Navigation Strip -->
                <div class="d-flex align-items-center justify-content-center gap-2 mt-3 flex-wrap" id="carousel_thumbnails_strip">
                    <!-- Dynamic Thumbnails -->
                </div>
            </div>

            <div class="modal-footer border-0 py-2.5 px-4 bg-body-tertiary d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Klik thumbnail atau panah navigasi untuk menggeser foto</small>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<script>
    // Global function to resolve product photos accurately across storage / uploads paths
    window.resolveTenderProductPhotoUrl = function(obj) {
        if (!obj) return '/uploads/barang/placeholder_garment.png';
        let path = '';
        if (typeof obj === 'string') {
            path = obj;
        } else if (obj.foto_path) {
            path = obj.foto_path;
        } else if (obj.primary_foto && obj.primary_foto.foto_path) {
            path = obj.primary_foto.foto_path;
        } else if (obj.primaryFoto && obj.primaryFoto.foto_path) {
            path = obj.primaryFoto.foto_path;
        } else if (obj.fotos && obj.fotos.length > 0 && obj.fotos[0].foto_path) {
            path = obj.fotos[0].foto_path;
        }

        if (!path) return '/uploads/barang/placeholder_garment.png';
        if (path.startsWith('http')) return path;
        if (path.startsWith('/storage/')) return path;
        if (path.startsWith('storage/')) return '/' + path;
        if (path.startsWith('/uploads/')) return path;
        if (path.startsWith('uploads/')) return '/' + path;
        if (path.startsWith('barang/')) return '/storage/' + path;
        if (path.startsWith('/barang/')) return '/storage' + path;
        if (path.startsWith('/')) return path;
        return `/storage/${path}`;
    };

    // Helper to collect all photo URLs from a product object
    window.extractProductPhotosList = function(obj) {
        let list = [];
        if (!obj) return ['/uploads/barang/placeholder_garment.png'];

        if (obj.fotos && Array.isArray(obj.fotos) && obj.fotos.length > 0) {
            obj.fotos.forEach(f => {
                let url = window.resolveTenderProductPhotoUrl(f);
                if (url && !list.includes(url)) list.push(url);
            });
        }

        if (obj.primary_foto || obj.primaryFoto) {
            let pUrl = window.resolveTenderProductPhotoUrl(obj.primary_foto || obj.primaryFoto);
            if (pUrl && !list.includes(pUrl)) list.unshift(pUrl);
        }

        if (list.length === 0) {
            list.push(window.resolveTenderProductPhotoUrl(obj));
        }

        return list;
    };

    // Direct modal photo viewer with carousel & swipe
    window.showProductPhotoDirect = function(data, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (!data) return;

        let name = data.name || 'Foto Produk';
        let sku = data.sku || '';
        let photos = data.photos || ['/uploads/barang/placeholder_garment.png'];

        if (!Array.isArray(photos) || photos.length === 0) {
            photos = [data.photo_url || '/uploads/barang/placeholder_garment.png'];
        }

        $('#preview_foto_nama').text(name);
        $('#preview_foto_sku').text(sku ? `KODE SKU: ${sku}` : '');
        $('#preview_foto_counter').text(photos.length > 1 ? `Foto 1 dari ${photos.length}` : '1 Foto');

        let carouselInnerHtml = '';
        let thumbsHtml = '';

        photos.forEach((photoUrl, idx) => {
            let activeCls = idx === 0 ? 'active' : '';
            carouselInnerHtml += `
                <div class="carousel-item ${activeCls}" data-index="${idx + 1}">
                    <div class="d-flex align-items-center justify-content-center w-100 p-2" style="min-height: 380px; height: 460px;">
                        <img src="${photoUrl}" style="max-height: 440px; max-width: 95%; object-fit: contain; border-radius: 12px; margin: 0 auto; display: block;" class="shadow-sm" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                    </div>
                </div>
            `;

            if (photos.length > 1) {
                thumbsHtml += `
                    <button type="button" class="btn p-0 rounded-3 border-2 ${idx === 0 ? 'border-primary' : 'border-transparent'} overflow-hidden preview-thumb-btn" 
                        data-bs-target="#carouselPreviewBarang" 
                        data-bs-slide-to="${idx}" 
                        title="Foto ${idx + 1}"
                        style="width: 54px; height: 54px; opacity: ${idx === 0 ? '1' : '0.5'}; transition: all 0.2s;">
                        <img src="${photoUrl}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                    </button>
                `;
            }
        });

        $('#carousel_preview_inner').html(carouselInnerHtml);
        $('#carousel_thumbnails_strip').html(thumbsHtml);

        if (photos.length > 1) {
            $('#preview_btn_prev, #preview_btn_next').removeClass('d-none');
            $('#carousel_thumbnails_strip').removeClass('d-none');
        } else {
            $('#preview_btn_prev, #preview_btn_next').addClass('d-none');
            $('#carousel_thumbnails_strip').addClass('d-none');
        }

        let modalEl = document.getElementById('modalPreviewFotoBarang');
        if (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }

        let carouselEl = document.getElementById('carouselPreviewBarang');
        if (carouselEl) {
            let bsCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
                interval: false,
                ride: false,
                touch: true,
                wrap: true
            });
            bsCarousel.to(0);

            carouselEl.removeEventListener('slid.bs.carousel', window._handlePreviewCarouselSlide);
            window._handlePreviewCarouselSlide = function(e) {
                let toIdx = e.to;
                $('#preview_foto_counter').text(`Foto ${toIdx + 1} dari ${photos.length}`);
                $('.preview-thumb-btn').each(function(i) {
                    if (i === toIdx) {
                        $(this).addClass('border-primary').removeClass('border-transparent').css('opacity', '1');
                    } else {
                        $(this).removeClass('border-primary').addClass('border-transparent').css('opacity', '0.5');
                    }
                });
            };
            carouselEl.addEventListener('slid.bs.carousel', window._handlePreviewCarouselSlide);

            $(document).off('keydown.previewCarousel').on('keydown.previewCarousel', function(e) {
                if ($('#modalPreviewFotoBarang').hasClass('show') && photos.length > 1) {
                    if (e.key === 'ArrowLeft') {
                        bsCarousel.prev();
                    } else if (e.key === 'ArrowRight') {
                        bsCarousel.next();
                    }
                }
            });
        }
    };

    // Open Lightbox Carousel for Product Photos by Map Key
    window.showProductPhotoPreview = function(key, event) {
        let data = (window._tenderProductPhotosMap && window._tenderProductPhotosMap[key]) ? window._tenderProductPhotosMap[key] : null;
        if (data) {
            window.showProductPhotoDirect(data, event);
        }
    };

    // Open Lightbox Carousel for Product Photos from Input Table Row
    window.showProductPhotoPreviewFromRow = function(el, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        let row = $(el).closest('tr');
        let img = $(el).find('img').attr('src');
        let photos = row.data('photos');
        if (!photos || !Array.isArray(photos) || photos.length === 0) {
            if (img && !img.includes('placeholder_garment')) {
                photos = [img];
            } else {
                photos = ['/uploads/barang/placeholder_garment.png'];
            }
        }
        let name = row.find('.item-barang-name').val() || row.find('.item-barang-name-input').val() || 'Foto Produk';
        window.showProductPhotoDirect({
            name: name,
            sku: '',
            photos: photos
        }, event);
    };

    // Open Dedicated Bundle Component Modal
    window.showBundleComponentsModal = function (idx, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        let info = (window._tenderItemsBundleMap && window._tenderItemsBundleMap[idx]) ? window._tenderItemsBundleMap[idx] : null;
        if (!info) {
            console.warn('Bundle components info not found for index:', idx);
            return;
        }

        let bName = info.name;
        let bSku = info.sku;
        let comps = info.components || [];

        $('#bundle_modal_title').text(bName);
        $('#bundle_modal_sku').text(`KODE SKU PAKET: ${bSku}`);
        $('#bundle_modal_count').text(`${comps.length} Komponen Produk`);

        let gridHtml = '';
        if (comps && comps.length > 0) {
            comps.forEach((c, cIdx) => {
                let cBrg = c.child_barang || c.childBarang || c;
                let cName = cBrg ? (cBrg.nama_barang || c.nama_barang) : (c.nama_barang || 'Komponen');
                let cSku = cBrg ? (cBrg.kode_barang || c.kode_barang) : (c.kode_barang || '-');
                let cSatuan = (cBrg && cBrg.satuan) ? (cBrg.satuan.nama_satuan || cBrg.satuan) : (c.satuan || 'Pcs');
                let cPhoto = window.resolveTenderProductPhotoUrl(cBrg || c);
                let cPhotosList = window.extractProductPhotosList(cBrg || c);
                let cQty = Number(c.qty) || 1;

                let compPhotoKey = `bundle_${idx}_comp_${cIdx}`;
                window._tenderProductPhotosMap[compPhotoKey] = {
                    name: cName,
                    sku: cSku,
                    photos: cPhotosList
                };

                gridHtml += `
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center gap-3 p-3 bg-body rounded-4 border shadow-xs h-100">
                            <div class="position-relative flex-shrink-0 cursor-pointer" onclick="window.showProductPhotoPreview('${compPhotoKey}', event)" title="${cPhotosList.length > 1 ? `Ada ${cPhotosList.length} foto - klik untuk geser` : 'Klik untuk memperbesar foto'}">
                                <img src="${cPhoto}" style="width:56px;height:56px;border-radius:12px;object-fit:cover;" class="border shadow-xs hover-scale" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                ${cPhotosList.length > 1 ? `
                                    <span class="position-absolute top-0 end-0 badge bg-dark bg-opacity-85 text-white border border-light border-opacity-25 rounded-pill font-monospace" style="font-size:0.62rem; padding: 2px 5px; transform: translate(15%, -15%); box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                                        <i class="bi bi-images me-0.5"></i>${cPhotosList.length}
                                    </span>
                                ` : `
                                    <span class="position-absolute bottom-0 end-0 p-1 bg-dark bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center" style="width:18px;height:18px;font-size:0.58rem;">
                                        <i class="bi bi-search"></i>
                                    </span>
                                `}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-body small text-truncate fs-6">${escapeHtml(cName)}</div>
                                <div class="text-muted font-monospace small" style="font-size:0.75rem;">${escapeHtml(cSku)}</div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="badge bg-primary text-white font-monospace px-3 py-1.5 rounded-pill fs-7">${cQty} ${escapeHtml(cSatuan)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            gridHtml = '<div class="col-12 text-center text-muted py-4">Tidak ada komponen rincian untuk paket ini.</div>';
        }

        $('#bundle_modal_components_grid').html(gridHtml);

        let modalEl = document.getElementById('modalDetailKomponenBundle');
        if (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }
    };
</script>

<!-- Modal Tolak Proposal Tender -->
<div class="modal fade" id="modalTolakProposalTender" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 bg-danger-subtle text-danger"><i class="bi bi-x-circle-fill fs-5"></i></span>
                    <h5 class="modal-title fw-bold text-danger mb-0">Tolak Proposal Tender</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <p class="text-muted small mb-3">Masukkan alasan penolakan proposal tender ini agar Sales PIC dapat melakukan revisi atau penyesuaian yang diperlukan.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea id="input_alasan_penolakan_tender" class="form-control rounded-3" rows="4" placeholder="Contoh: Estimasi margin terlalu rendah / Perlu perbaikan spesifikasi teknis barang..."></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3.5" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btn_confirm_reject_tender">
                        <i class="bi bi-x-lg me-1"></i> Tolak Proposal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Tender Menang (Won) -->
<div class="modal fade" id="modalTenderMenang" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 bg-success-subtle text-success"><i class="bi bi-trophy-fill fs-5"></i></span>
                    <h5 class="modal-title fw-bold text-success mb-0">Penetapan Pemenang Tender (Won)</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <p class="text-muted small mb-3">Selamat! Silakan konfirmasi nilai final kontrak kesepakatan pemenang lelang tender ini.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nilai Kontrak / Kesepakatan Final (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text font-monospace fw-bold">Rp</span>
                        <input type="text" id="input_nilai_deal_tender_mask" class="form-control text-end font-monospace fw-bold text-success fs-6" placeholder="0">
                        <input type="hidden" id="input_nilai_deal_tender">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3.5" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" id="btn_confirm_won_tender">
                        <i class="bi bi-trophy-fill me-1"></i> Tetapkan Pemenang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tender Kalah (Lost) -->
<div class="modal fade" id="modalTenderKalah" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-xl">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 bg-danger-subtle text-danger"><i class="bi bi-x-octagon-fill fs-5"></i></span>
                    <h5 class="modal-title fw-bold text-danger mb-0">Evaluasi Tender Kalah (Lost)</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <p class="text-muted small mb-3">Catat alasan kekalahan tender ini untuk bahan evaluasi strategi marketing di masa depan.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Alasan Kekalahan <span class="text-danger">*</span></label>
                    <textarea id="input_lost_reason_tender" class="form-control rounded-3" rows="3" placeholder="Contoh: Kalah penawaran harga dari kompetitor / Evaluasi teknis tidak memenuhi syarat..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Kompetitor Pemenang (Opsional)</label>
                    <input type="text" id="input_winning_competitor_tender" class="form-control rounded-3" placeholder="Contoh: PT Sumber Rezeki Makmur">
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3.5" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btn_confirm_lost_tender">
                        <i class="bi bi-check-lg me-1"></i> Simpan Status Kalah
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentDetailTenderId = null;

    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatRp(amount) {
        if (!amount && amount !== 0) return 'Rp 0';
        return 'Rp ' + Number(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    window.loadTenderDetail = function (id, showModal = false, callback = null) {
        if (!id) return;
        currentDetailTenderId = id;

        $.get(`/tender/${id}/detail-json`, function (res) {
            let t = res.tender;
            if (!t) return;

                $('#detail_nomor_kontrak').text(t.nomor_kontrak || '-');
                let entitasTxt = t.branch ? `<i class="bi bi-shop me-1"></i>Cabang ${escapeHtml(t.branch.nama_cabang)}` : `<i class="bi bi-building me-1"></i>${escapeHtml(t.company ? t.company.nama_perusahaan + ' (Pusat)' : 'PT (Pusat)')}`;
                $('#detail_entitas_badge').html(entitasTxt);
                $('#detail_kategori_instansi').text(t.kategori_instansi || 'Pemerintah / B2G');

                let pBadge = '';
                if (t.priority === 'High') {
                    pBadge = '<span class="badge bg-danger text-white px-3 py-1.5 rounded-pill shadow-xs">🔥 Prioritas Tinggi</span>';
                } else if (t.priority === 'Medium') {
                    pBadge = '<span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill shadow-xs">⚡ Prioritas Sedang</span>';
                } else {
                    pBadge = '<span class="badge bg-success text-white px-3 py-1.5 rounded-pill shadow-xs">🌱 Prioritas Rendah</span>';
                }
                $('#detail_priority_badge').html(pBadge);

                $('#detail_nama_tender').text(t.nama_tender);
                $('#detail_customer').text(t.customer ? (t.customer.nama_perusahaan || t.customer.nama_customer) : '-');
                $('#detail_nilai_estimasi').text(formatRp(t.nilai_estimasi));
                $('#detail_tanggal').text(t.tanggal ? t.tanggal.split('T')[0] : '-');
                $('#detail_deadline').text(t.deadline ? t.deadline.split('T')[0] : '-');
                $('#detail_estimasi_closing').text(t.estimasi_closing ? t.estimasi_closing.split('T')[0] : '-');
                $('#detail_sales').text(t.sales ? t.sales.nama_lengkap : '-');

                // Render Workflow Stepper Nodes
                $('.stepper-node').removeClass('active completed rejected');
                if (t.status === 'Draft') {
                    $('#step_draft').addClass('active');
                } else if (t.status === 'Pending Approval') {
                    $('#step_draft').addClass('completed');
                    $('#step_approval').addClass('active');
                } else if (t.status === 'Rejected') {
                    $('#step_draft').addClass('completed');
                    $('#step_approval').addClass('rejected');
                } else if (t.status === 'Approved') {
                    $('#step_draft, #step_approval').addClass('completed');
                    $('#step_sent').addClass('active');
                } else if (t.status === 'Sent') {
                    $('#step_draft, #step_approval, #step_sent').addClass('completed');
                    $('#step_negotiation').addClass('active');
                } else if (t.status === 'Negotiation') {
                    $('#step_draft, #step_approval, #step_sent, #step_negotiation').addClass('completed');
                    $('#step_decision').addClass('active');
                } else if (t.status === 'Won') {
                    $('#step_draft, #step_approval, #step_sent, #step_negotiation, #step_decision').addClass('completed');
                } else if (t.status === 'Lost' || t.status === 'Cancelled') {
                    $('#step_draft, #step_approval, #step_sent, #step_negotiation').addClass('completed');
                    $('#step_decision').addClass('rejected');
                }

                // Render Action Buttons & Stage Banners
                let actionsHtml = '';
                let bannerHtml = '';

                if (t.status === 'Draft') {
                    actionsHtml = `
                        <button type="button" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_submit_approval">
                            <i class="bi bi-send-check fs-6"></i>
                            <span>🚀 Ajukan Approval Atasan</span>
                        </button>
                    `;
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle d-flex align-items-center gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                <i class="bi bi-pencil-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-info fs-6">Status Saat Ini: Draft Proposal</div>
                                <div class="text-secondary small">Proposal tender ini masih dalam status <strong>Draft</strong>. Silakan periksa rincian item & berkas sebelum mengajukan persetujuan ke Atasan/Manager.</div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Pending Approval') {
                    if (res.can_approve) {
                        actionsHtml = `
                            <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_approve_tender">
                                <i class="bi bi-check-circle-fill fs-6"></i>
                                <span>Setujui (Approve)</span>
                            </button>
                            <button type="button" class="btn btn-danger rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_trigger_reject_modal">
                                <i class="bi bi-x-circle-fill fs-6"></i>
                                <span>Tolak (Reject)</span>
                            </button>
                        `;
                    } else {
                        actionsHtml = `
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill fw-bold">
                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Atasan
                            </span>
                        `;
                    }
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle d-flex align-items-center gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center shadow-xs">
                                <span class="spinner-grow spinner-grow-sm text-dark" role="status"></span>
                            </span>
                            <div>
                                <div class="fw-bold text-warning-emphasis fs-6">Menunggu Persetujuan (Approval) Atasan</div>
                                <div class="text-secondary small">Proposal tender telah diajukan ke atasan. Notifikasi WhatsApp otomatis telah diteruskan ke Approver berwenang.</div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Approved') {
                    actionsHtml = `
                        <button type="button" class="btn btn-info text-white rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_advance_to_sent">
                            <i class="bi bi-send-fill fs-6"></i>
                            <span>📤 Kirim Penawaran ke Klien / Panitia</span>
                        </button>
                    `;
                    let approverName = t.approver ? (t.approver.nama_lengkap || t.approver.name) : 'Atasan';
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle d-flex align-items-center gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                <i class="bi bi-patch-check-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-success fs-6">Proposal Disetujui (Approved) oleh ${escapeHtml(approverName)}</div>
                                <div class="text-secondary small">Dokumen siap diajukan ke panitia lelang / klien. Klik tombol <strong>Kirim Penawaran</strong> saat berkas telah diserahkan.</div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Rejected') {
                    actionsHtml = `
                        <button type="button" class="btn btn-warning rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_submit_approval">
                            <i class="bi bi-arrow-repeat fs-6"></i>
                            <span>🔄 Ajukan Ulang Persetujuan</span>
                        </button>
                    `;
                    let rejectorName = t.rejector ? (t.rejector.nama_lengkap || t.rejector.name) : 'Atasan';
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle d-flex align-items-start gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center shadow-xs mt-0.5">
                                <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-danger fs-6">Proposal Ditolak oleh ${escapeHtml(rejectorName)}</div>
                                <div class="text-secondary small mt-0.5">Alasan Penolakan: <strong class="text-danger">${escapeHtml(t.rejected_reason || 'Perlu perbaikan spesifikasi atau harga penawaran.')}</strong></div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Sent') {
                    actionsHtml = `
                        <button type="button" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_advance_to_negotiation">
                            <i class="bi bi-chat-dots-fill fs-6"></i>
                            <span>🤝 Mulai Tahap Aanwijzing & Negosiasi</span>
                        </button>
                    `;
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle d-flex align-items-center gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                <i class="bi bi-send-check-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-info fs-6">Dokumen Penawaran Terkirim ke Panitia / Klien</div>
                                <div class="text-secondary small">Dokumen penawaran lelang telah diserahkan. Menunggu jadwal evaluasi administrasi & Aanwijzing teknis.</div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Negotiation') {
                    actionsHtml = `
                        <button type="button" class="btn btn-success rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_trigger_won_modal">
                            <i class="bi bi-trophy-fill fs-6"></i>
                            <span>🏆 Tender Menang (Won)</span>
                        </button>
                        <button type="button" class="btn btn-danger rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_trigger_lost_modal">
                            <i class="bi bi-x-circle-fill fs-6"></i>
                            <span>❌ Tender Kalah (Lost)</span>
                        </button>
                    `;
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle d-flex align-items-center gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                <i class="bi bi-chat-quote-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-primary fs-6">Tahap Aanwijzing & Negosiasi Harga Sedang Berlangsung</div>
                                <div class="text-secondary small">Proses lelang sedang dalam tahap klarifikasi teknis dan negosiasi penawaran harga. Tetapkan keputusan akhir setelah pengumuman resmi.</div>
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Won') {
                    actionsHtml = '';
                    let soActionBtn = '';
                    if (t.sales_order_id) {
                        soActionBtn = `
                            <a href="{{ route('marketing.sales-order.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs">
                                <i class="bi bi-box-arrow-up-right"></i> Buka Modul Order Penjualan
                            </a>
                        `;
                    } else {
                        soActionBtn = `
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_convert_tender_to_so">
                                <i class="bi bi-file-earmark-check-fill"></i> <span>🏆 Konversi ke Order Penjualan (SO)</span>
                            </button>
                        `;
                    }

                    bannerHtml = `
                        <div class="p-3.5 rounded-3 bg-success-subtle border border-success border-opacity-30 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-xs">
                            <div class="d-flex align-items-center gap-3">
                                <span class="p-2.5 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                    <i class="bi bi-trophy-fill fs-4"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-success fs-6">Tender Resmi Berhasil Dimenangkan (Won)! 🎉</div>
                                    <div class="text-secondary small mt-0.5">
                                        Nilai Kesepakatan Final: <strong class="text-body">${formatRp(t.nilai_deal || t.nilai_estimasi)}</strong> • 
                                        ${t.sales_order_id ? `Sales Order: <strong class="text-success font-monospace">SO-${t.sales_order_id}</strong>` : `Belum dikonversi ke Sales Order.`}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                ${soActionBtn}
                            </div>
                        </div>
                    `;
                } else if (t.status === 'Lost') {
                    actionsHtml = '';
                    bannerHtml = `
                        <div class="p-3 rounded-3 bg-danger-subtle border border-danger-subtle d-flex align-items-start gap-3 shadow-2xs">
                            <span class="p-2 rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center shadow-xs mt-0.5">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                            </span>
                            <div>
                                <div class="fw-bold text-danger fs-6">Tender Dinyatakan Kalah (Lost)</div>
                                <div class="text-secondary small mt-0.5">
                                    Alasan: <strong class="text-danger">${escapeHtml(t.lost_reason || '-')}</strong>
                                    ${t.winning_competitor ? ` • Pemenang: <strong class="text-body">${escapeHtml(t.winning_competitor)}</strong>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }

                $('#detail_workflow_actions').html(actionsHtml);
                $('#detail_workflow_status_banner').html(bannerHtml);

                // Priority Badge
                let prioMap = {
                    'High': 'badge-priority-high',
                    'Medium': 'badge-priority-medium',
                    'Low': 'badge-priority-low'
                };
                $('#detail_priority_badge').html(`<span class="badge ${prioMap[t.priority] || 'bg-secondary'} px-2.5 py-1 rounded-pill">${t.priority}</span>`);
                $('#detail_quick_stage_select').val(t.status);

                // Populate Items Table
                window._tenderItemsBundleMap = {};
                window._tenderProductPhotosMap = {};
                let itemsHtml = '';
                if (t.items && t.items.length > 0) {
                    t.items.forEach((item, idx) => {
                        let brg = item.barang;
                        let brgNama = brg ? brg.nama_barang : 'Item';
                        let brgSku = brg ? brg.kode_barang : '-';
                        let photoUrl = window.resolveTenderProductPhotoUrl(brg);
                        let photosList = window.extractProductPhotosList(brg);
                        let isBundle = brg ? (brg.tipe_item === 'Bundle') : false;
                        let bundleComps = (brg && brg.bundle_components) ? brg.bundle_components : ((brg && brg.bundleComponents) ? brg.bundleComponents : []);

                        let itemPhotoKey = `item_${idx}`;
                        window._tenderProductPhotosMap[itemPhotoKey] = {
                            name: brgNama,
                            sku: brgSku,
                            photos: photosList
                        };

                        let bundleIconBtnHtml = '';

                        if (isBundle && bundleComps.length > 0) {
                            window._tenderItemsBundleMap[idx] = {
                                name: brgNama,
                                sku: brgSku,
                                components: bundleComps
                            };

                            bundleIconBtnHtml = `
                                <button type="button" 
                                    class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs btn-trigger-bundle-modal cursor-pointer" 
                                    data-idx="${idx}"
                                    onclick="window.showBundleComponentsModal(${idx}, event)"
                                    title="Lihat rincian ${bundleComps.length} item komponen paket"
                                    style="font-size: 0.72rem; line-height: 1.3;">
                                    <i class="bi bi-boxes text-warning"></i>
                                    <span>${bundleComps.length} Komponen Rincian</span>
                                    <i class="bi bi-chevron-right fs-8 opacity-75"></i>
                                </button>
                            `;
                        }

                        itemsHtml += `
                            <tr>
                                <td class="text-center align-middle fw-bold text-muted">${idx + 1}</td>
                                <td class="py-3 align-middle">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative flex-shrink-0 cursor-pointer" onclick="window.showProductPhotoPreview('${itemPhotoKey}', event)" title="${photosList.length > 1 ? `Ada ${photosList.length} foto - klik untuk geser` : 'Klik untuk memperbesar foto'}">
                                            <img src="${photoUrl}" style="width:54px;height:54px;border-radius:12px;object-fit:cover;" class="border shadow-xs hover-scale" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                                            ${photosList.length > 1 ? `
                                                <span class="position-absolute top-0 end-0 badge bg-dark bg-opacity-85 text-white border border-light border-opacity-25 rounded-pill font-monospace" style="font-size:0.62rem; padding: 2px 5px; transform: translate(15%, -15%); box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                                                    <i class="bi bi-images me-0.5"></i>${photosList.length}
                                                </span>
                                            ` : `
                                                <span class="position-absolute bottom-0 end-0 p-1 bg-dark bg-opacity-75 text-white rounded-circle d-flex align-items-center justify-content-center" style="width:18px;height:18px;font-size:0.58rem;">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                            `}
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold text-body fs-6 d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                                <span>${escapeHtml(brgNama)}</span>
                                                ${isBundle ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle py-0.5 px-2 font-monospace" style="font-size:0.68rem;"><i class="bi bi-boxes me-1"></i> Paket Bundle</span>' : ''}
                                            </div>
                                            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                                                <span class="text-muted font-monospace small">${escapeHtml(brgSku)}</span>
                                                ${bundleIconBtnHtml}
                                                ${item.catatan_item ? `<div class="text-muted small mt-1 fst-italic"><i class="bi bi-info-circle me-1"></i>${escapeHtml(item.catatan_item)}</div>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center font-monospace align-middle fw-bold fs-6">${Number(item.qty)}</td>
                                <td class="text-center align-middle">${escapeHtml(item.satuan)}</td>
                            </tr>
                        `;
                    });
                } else {
                    itemsHtml = '<tr><td colspan="4" class="text-center py-4 text-muted fst-italic">Belum ada rincian item pekerjaan.</td></tr>';
                }
                $('#detail_items_tbody').html(itemsHtml);

                // Populate Pagu & Summary
                let paguAnggaran = Number(t.nilai_deal || t.nilai_estimasi || t.grand_total || 0);
                $('#detail_grand_total_label').text(formatRp(paguAnggaran));
                $('#detail_total_items_count').text(`${t.items ? t.items.length : 0} Item`);
                $('#detail_currency_badge').text(t.currency || 'IDR');



                // Populate Documents
                let docsHtml = '';
                if (t.documents && t.documents.length > 0) {
                    t.documents.forEach(doc => {
                        docsHtml += `
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-body small shadow-xs">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                    <div>
                                        <div class="fw-bold fs-6">${escapeHtml(doc.nama_file)}</div>
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.72rem;">${escapeHtml(doc.jenis_dokumen)}</span>
                                    </div>
                                </div>
                                <a href="/${doc.path || doc.file_path}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3.5">
                                    <i class="bi bi-download me-1"></i> Buka / Download
                                </a>
                            </div>
                        `;
                    });
                } else {
                    docsHtml = '<div class="text-muted small fst-italic py-3 text-center">Belum ada berkas dokumen yang dilampirkan.</div>';
                }
                $('#detail_documents_list').html(docsHtml);

                // Populate Followups
                let fHtml = '';
                if (t.followups && t.followups.length > 0) {
                    t.followups.forEach(f => {
                        fHtml += `
                            <div class="border-start border-3 border-primary ps-3 py-2 bg-body rounded-2 p-2.5 shadow-xs">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-primary fs-6">${escapeHtml(f.aktivitas)}</strong>
                                    <span class="text-muted font-monospace small">${f.tanggal ? f.tanggal.split('T')[0] : (f.tanggal_followup ? f.tanggal_followup.split('T')[0] : '')}</span>
                                </div>
                                <p class="mb-1 small text-secondary mt-1">${escapeHtml(f.keterangan || '')}</p>
                                <span class="badge bg-body-tertiary text-muted font-monospace" style="font-size:0.68rem;">Oleh: ${f.user ? f.user.name : 'Sistem'}</span>
                            </div>
                        `;
                    });
                } else {
                    fHtml = '<div class="text-muted small fst-italic py-2 text-center">Belum ada aktivitas follow-up yang tercatat.</div>';
                }
                $('#detail_followup_timeline').html(fHtml);

                // Populate Approvals
                let aHtml = '';
                if (t.approvals && t.approvals.length > 0) {
                    t.approvals.forEach(a => {
                        let aBadge = a.status === 'Approved' ? 'bg-success' : (a.status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark');
                        aHtml += `
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-body small mb-2 shadow-xs">
                                <div>
                                    <div class="fw-bold fs-6">${a.user ? a.user.name : 'Approver'}</div>
                                    <div class="text-muted small">${escapeHtml(a.catatan || 'Tidak ada catatan khusus.')}</div>
                                </div>
                                <span class="badge ${aBadge} px-3 py-1.5 rounded-pill fs-7">${a.status}</span>
                            </div>
                        `;
                    });
                } else {
                    aHtml = '<div class="text-muted small fst-italic py-2 text-center">Belum ada log approval.</div>';
                }
                $('#detail_approval_history').html(aHtml);

                if (showModal) {
                    $('#modalDetailTender').modal('show');
                }
                if (typeof callback === 'function') {
                    callback(res);
                }
            });
        };

        // Open Detail Modal & Populate Data on Click
        $(document).on('click', '.btn-view-detail', function (e) {
            e.stopPropagation();
            let id = $(this).data('id');
            if (!id) return;
            window.loadTenderDetail(id, true);
        });

        // Dedicated click listener for bundle modal
        $(document).on('click', '.btn-trigger-bundle-modal', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let idx = $(this).data('idx');
            window.showBundleComponentsModal(idx, e);
        });

        // Ensure parent detail modal retains scroll on stacked modal close
        $('#modalDetailKomponenBundle, #modalPreviewFotoBarang, #modalTolakProposalTender, #modalTenderMenang, #modalTenderKalah').on('hidden.bs.modal', function () {
            if ($('#modalDetailTender').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        // Action: Submit for Approval
        $(document).on('click', '#btn_submit_approval', function () {
            if (!currentDetailTenderId) return;

            Swal.fire({
                title: 'Ajukan Persetujuan?',
                text: 'Proposal tender akan dikunci untuk review atasan dan notifikasi WhatsApp akan dikirimkan ke Approver.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(`/tender/${currentDetailTenderId}/submit-approval`, {
                        _token: '{{ csrf_token() }}'
                    }, function (data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diajukan!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.loadTenderDetail(currentDetailTenderId, false);
                        refreshTenderViews();
                    }).fail(function (xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal mengajukan approval.';
                        Swal.fire('Gagal', msg, 'error');
                    });
                }
            });
        });

        // Action: Approve Tender
        $(document).on('click', '#btn_approve_tender', function () {
            if (!currentDetailTenderId) return;

            Swal.fire({
                title: 'Setujui Proposal Tender?',
                text: 'Dengan menyetujui tender ini, dokumen penawaran resmi siap dikirimkan ke panitia pengadaan lelang/klien.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui (Approve)',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(`/tender/${currentDetailTenderId}/approve`, {
                        _token: '{{ csrf_token() }}'
                    }, function (data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Proposal Disetujui!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        window.loadTenderDetail(currentDetailTenderId, false);
                        refreshTenderViews();
                    }).fail(function (xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyetujui tender.';
                        Swal.fire('Gagal', msg, 'error');
                    });
                }
            });
        });

        // Action: Trigger Reject Modal
        $(document).on('click', '#btn_trigger_reject_modal', function () {
            $('#input_alasan_penolakan_tender').val('');
            $('#modalTolakProposalTender').modal('show');
        });

        // Action: Confirm Reject Tender
        $(document).on('click', '#btn_confirm_reject_tender', function () {
            if (!currentDetailTenderId) return;
            let alasan = $('#input_alasan_penolakan_tender').val();
            if (!alasan || !alasan.trim()) {
                Swal.fire('Peringatan', 'Alasan penolakan tender wajib diisi.', 'warning');
                return;
            }

            $.post(`/tender/${currentDetailTenderId}/reject`, {
                _token: '{{ csrf_token() }}',
                alasan: alasan
            }, function (data) {
                $('#modalTolakProposalTender').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Proposal Ditolak',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            }).fail(function (xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menolak tender.';
                Swal.fire('Gagal', msg, 'error');
            });
        });

        // Action: Advance Stage to Sent
        $(document).on('click', '#btn_advance_to_sent', function () {
            if (!currentDetailTenderId) return;

            $.post(`/tender/${currentDetailTenderId}/update-stage`, {
                _token: '{{ csrf_token() }}',
                status: 'Sent'
            }, function (data) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tahapan Diperbarui',
                    text: 'Status tender telah dipindahkan ke Penawaran Dikirim.',
                    timer: 1500,
                    showConfirmButton: false
                });
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            });
        });

        // Action: Advance Stage to Negotiation
        $(document).on('click', '#btn_advance_to_negotiation', function () {
            if (!currentDetailTenderId) return;

            $.post(`/tender/${currentDetailTenderId}/update-stage`, {
                _token: '{{ csrf_token() }}',
                status: 'Negotiation'
            }, function (data) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tahapan Diperbarui',
                    text: 'Status tender telah dipindahkan ke Aanwijzing & Negosiasi.',
                    timer: 1500,
                    showConfirmButton: false
                });
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            });
        });

        // Action: Trigger Won Modal
        $(document).on('click', '#btn_trigger_won_modal', function () {
            let currentVal = $('#detail_nilai_estimasi').text().replace(/[^0-9]/g, '');
            $('#input_nilai_deal_tender_mask').val(formatRp(currentVal).replace('Rp ', ''));
            $('#input_nilai_deal_tender').val(currentVal);
            $('#modalTenderMenang').modal('show');
        });

        // Action: Confirm Won Tender
        $(document).on('click', '#btn_confirm_won_tender', function () {
            if (!currentDetailTenderId) return;
            let val = $('#input_nilai_deal_tender').val();

            $.post(`/tender/${currentDetailTenderId}/mark-as-won`, {
                _token: '{{ csrf_token() }}',
                nilai_deal: val
            }, function (data) {
                $('#modalTenderMenang').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: '🏆 Selamat! Tender Menang!',
                    text: data.message,
                    confirmButtonText: 'Tutup'
                });
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            }).fail(function (xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan status tender menang.';
                Swal.fire('Gagal', msg, 'error');
            });
        });

        // Action: Trigger Lost Modal
        $(document).on('click', '#btn_trigger_lost_modal', function () {
            $('#input_lost_reason_tender').val('');
            $('#input_winning_competitor_tender').val('');
            $('#modalTenderKalah').modal('show');
        });

        // Action: Confirm Lost Tender
        $(document).on('click', '#btn_confirm_lost_tender', function () {
            if (!currentDetailTenderId) return;
            let reason = $('#input_lost_reason_tender').val();
            let competitor = $('#input_winning_competitor_tender').val();

            if (!reason || !reason.trim()) {
                Swal.fire('Peringatan', 'Alasan kekalahan tender wajib diisi.', 'warning');
                return;
            }

            $.post(`/tender/${currentDetailTenderId}/mark-as-lost`, {
                _token: '{{ csrf_token() }}',
                lost_reason: reason,
                winning_competitor: competitor
            }, function (data) {
                $('#modalTenderKalah').modal('hide');
                Swal.fire({
                    icon: 'info',
                    title: 'Status Diperbarui',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            }).fail(function (xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan status tender kalah.';
                Swal.fire('Gagal', msg, 'error');
            });
        });

        function refreshTenderViews() {
            if (typeof loadKanbanBoard === 'function') loadKanbanBoard();
            if (window.renderKanbanBoard) window.renderKanbanBoard();
            if ($.fn.DataTable.isDataTable('#tableTender')) {
                $('#tableTender').DataTable().ajax.reload(null, false);
            }
        }

        // Number format handler for Won deal price input
        $('#input_nilai_deal_tender_mask').on('input', function () {
            let val = $(this).val().replace(/[^0-9]/g, '');
            let num = parseInt(val) || 0;
            $('#input_nilai_deal_tender').val(num);
            $(this).val(num > 0 ? num.toLocaleString('id-ID') : '');
        });

        // Fast Stage Changer Inside Modal Detail
        $('#detail_quick_stage_select').on('change', function () {
            let newStatus = $(this).val();
            if (!currentDetailTenderId) return;

            $.ajax({
                url: `/tender/${currentDetailTenderId}/update-stage`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Berhasil Diubah!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    window.loadTenderDetail(currentDetailTenderId, false);
                    refreshTenderViews();
                },
                error: function (xhr) {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem saat update status.', 'error');
                }
            });
        });

        // Add Follow-Up Submit
        $('#form_add_followup').on('submit', function (e) {
            e.preventDefault();
            if (!currentDetailTenderId) return;

            let act = $('#followup_aktivitas').val();
            let ket = $('#followup_keterangan').val();

            $.post(`/tender/${currentDetailTenderId}/followup`, {
                _token: '{{ csrf_token() }}',
                aktivitas: act,
                keterangan: ket
            }, function (res) {
                $('#followup_aktivitas').val('');
                $('#followup_keterangan').val('');
                Swal.fire({
                    icon: 'success',
                    title: 'Aktivitas Dicatat!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                // Refresh Detail
                window.loadTenderDetail(currentDetailTenderId, false);
                refreshTenderViews();
            });
        });

        // Convert Tender to Sales Order Click
        $(document).on('click', '#btn_convert_tender_to_so', function () {
            if (!currentDetailTenderId) return;

            Swal.fire({
                title: 'Konversi Menjadi Sales Order?',
                text: 'Tender ini akan dibuatkan dokumen Sales Order (SPK) resmi di sistem penjualan untuk segera diproses gudang & produksi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Konversi Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/tender/${currentDetailTenderId}/convert-to-so`, {
                        _token: '{{ csrf_token() }}'
                    }, function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dikonversi ke Sales Order!',
                            text: res.message,
                            showCancelButton: true,
                            confirmButtonText: 'Lihat Sales Order',
                            cancelButtonText: 'Tetap di Tender'
                        }).then((choice) => {
                            if (choice.isConfirmed && res.redirect_url) {
                                window.location.href = res.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        });
                    }).fail(function (xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal konversi ke Sales Order.';
                        Swal.fire('Gagal', msg, 'error');
                    });
                }
            });
        });
</script>
@endpush
