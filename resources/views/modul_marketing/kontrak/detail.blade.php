{{-- ===== MODAL DETAIL TENDER (DUAL THEME ADAPTIVE: LIGHT & DARK) ===== --}}

<style>
    /* ─── TENDER MODAL CINEMATIC & RESPONSIVE REFINEMENTS ─── */
    .tender-btn-close {
        width: 34px;
        height: 34px;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 0.85rem;
        cursor: pointer;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: rgba(0, 0, 0, 0.04);
        color: #64748b;
        flex-shrink: 0;
        margin-left: 8px;
    }
    .tender-btn-close:hover {
        background: rgba(239, 68, 68, 0.12) !important;
        color: #ef4444 !important;
        border-color: rgba(239, 68, 68, 0.25) !important;
        transform: rotate(90deg) scale(1.05);
    }
    [data-bs-theme="dark"] .tender-btn-close {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .tender-btn-close:hover {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #f87171 !important;
        border-color: rgba(239, 68, 68, 0.4) !important;
    }

    /* Stepper Scrollable Mobile */
    @media (max-width: 991.98px) {
        .tender-stepper-scrollable {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 8px !important;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .tender-stepper-scrollable::-webkit-scrollbar {
            display: none;
        }
        .tender-stepper-scrollable .stepper-node {
            flex-shrink: 0 !important;
        }
        .tender-stepper-scrollable .stepper-text {
            white-space: nowrap !important;
            font-size: 0.78rem !important;
        }
        .tender-stepper-scrollable > i.bi-chevron-right {
            flex-shrink: 0 !important;
        }

        #detail_workflow_actions {
            width: 100% !important;
            margin-top: 8px;
        }
        #detail_workflow_actions .btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 10px 16px !important;
            font-size: 0.88rem !important;
        }
    }

    /* Tabs Scrollable Mobile */
    @media (max-width: 768px) {
        .tender-tabs-scrollable {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 4px !important;
            scrollbar-width: none;
            border-bottom: 2px solid rgba(0, 0, 0, 0.06) !important;
        }
        .tender-tabs-scrollable::-webkit-scrollbar {
            display: none;
        }
        .tender-tabs-scrollable .nav-item {
            flex-shrink: 0 !important;
        }
        .tender-tabs-scrollable .nav-link {
            padding: 8px 14px !important;
            font-size: 0.8rem !important;
            white-space: nowrap !important;
        }

        /* Metric cards in mobile 2x2 grid */
        .tender-card-metric {
            padding: 10px 12px !important;
            border-radius: 12px !important;
        }
        .tender-card-metric span.d-inline-flex {
            width: 38px !important;
            height: 38px !important;
            font-size: 1.15rem !important;
            border-radius: 8px !important;
        }
        .tender-card-metric .metric-label {
            font-size: 0.62rem !important;
        }
        .tender-card-metric .metric-value {
            font-size: 0.88rem !important;
        }
        .tender-btn-close {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.78rem !important;
        }

        /* Modal Full Width on Mobile */
        #modalDetailTender {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        #modalDetailTender .modal-dialog {
            width: calc(100% - 14px) !important;
            max-width: calc(100% - 14px) !important;
            margin: 7px auto !important;
        }
        #modalDetailTender .modal-content {
            width: 100% !important;
            border-radius: 20px !important;
        }
    }

    /* Modal Full / Ultra Wide on Desktop */
    @media (min-width: 769px) {
        #modalDetailTender .modal-dialog {
            width: 96vw !important;
            max-width: 96vw !important;
            margin: 0.5rem auto !important;
        }
    }
</style>

<div class="modal modal-blur fade" id="modalDetailTender" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-custom-xxl">
        <div class="modal-content tender-modal-content tender-modal-cinematic rounded-4 overflow-hidden w-100">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0 pt-3.5 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 flex-wrap pe-2 min-w-0 flex-grow-1">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill fw-bold font-monospace shadow-xs" id="detail_nomor_kontrak" style="font-size: 0.78rem;">
                        TND-000
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill shadow-xs" id="detail_entitas_badge" style="font-size: 0.75rem;">
                        <i class="bi bi-building me-1"></i> PT (Pusat)
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1.5 rounded-pill shadow-xs" id="detail_kategori_instansi" style="font-size: 0.75rem;">
                        Pemerintah / B2G
                    </span>
                    <span class="badge bg-secondary px-2.5 py-1.5 rounded-pill shadow-xs" id="detail_status_badge" style="font-size: 0.75rem;">
                        Draft
                    </span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill shadow-xs" id="detail_priority_badge" style="font-size: 0.75rem;">
                        ⚡ Sedang (Medium)
                    </span>
                </div>
                <button type="button" class="btn tender-btn-close rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body p-3 p-sm-4 pt-2.5">
                <div class="row g-3 g-sm-3.5">
                    
                    <!-- Proyek Title & Summary Banner (Ultra-Wide) -->
                    <div class="col-12">
                        <div class="p-3.5 p-sm-4 rounded-4 tender-banner-cinematic d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 shadow-sm">
                            <div>
                                <h3 class="fw-black mb-1.5 tender-title text-truncate" id="detail_nama_tender" style="letter-spacing: -0.02em;">-</h3>
                                <div class="d-flex align-items-center gap-2 fs-6">
                                    <span class="p-1 px-2 rounded-3 bg-primary bg-opacity-10 text-primary font-monospace small"><i class="bi bi-building"></i></span>
                                    <span id="detail_customer" class="fw-bold tender-customer-text">-</span>
                                </div>
                            </div>
                            <div class="text-lg-end tender-pagu-card p-3 px-4 rounded-4 border shadow-xs">
                                <div class="text-success small fw-bold text-uppercase font-monospace" id="detail_pagu_label" style="font-size:0.75rem; letter-spacing: 0.08em;">TOTAL ESTIMASI NILAI PROYEK:</div>
                                <h1 class="fw-black text-success font-monospace mb-0 fs-3 fs-md-1" id="detail_nilai_estimasi">Rp 0</h1>
                                <div class="text-muted font-monospace small mt-0.5" id="detail_pagu_subtext" style="display:none; font-size:0.75rem;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- WORKFLOW STEPPER & ACTION CONTROL CARD (CINEMATIC) -->
                    <div class="col-12">
                        <div class="tender-workflow-card p-3.5 shadow-xs" id="detail_workflow_card">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 pb-3 border-bottom">
                                <!-- Stepper Nodes (Horizontal Swipeable di Mobile) -->
                                <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1 tender-stepper-scrollable" id="tender_stepper_container">
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

                    <!-- Wide Cinematic Metric Cards Grid (2x2 di Mobile, 4 Kolom di Desktop) -->
                    <div class="col-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-2.5 gap-sm-3">
                            <span class="p-2 p-sm-3 rounded-3 bg-primary bg-opacity-10 text-primary fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.68rem; letter-spacing: 0.05em;">TANGGAL MULAI</div>
                                <div class="metric-value fw-bold fs-6 fs-sm-5 font-monospace text-truncate" id="detail_tanggal">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-2.5 gap-sm-3 border-danger border-opacity-25">
                            <span class="p-2 p-sm-3 rounded-3 bg-danger bg-opacity-10 text-danger fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="bi bi-calendar-x"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="text-danger small fw-bold text-uppercase" style="font-size:0.68rem; letter-spacing: 0.05em;">BATAS DEADLINE</div>
                                <div class="fw-bold fs-6 fs-sm-5 text-danger font-monospace text-truncate" id="detail_deadline">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-2.5 gap-sm-3">
                            <span class="p-2 p-sm-3 rounded-3 bg-warning bg-opacity-10 text-warning fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="bi bi-hourglass-split"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.68rem; letter-spacing: 0.05em;">ESTIMASI CLOSING</div>
                                <div class="metric-value fw-bold fs-6 fs-sm-5 font-monospace text-truncate" id="detail_estimasi_closing">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="tender-card-metric d-flex align-items-center gap-2.5 gap-sm-3">
                            <span class="p-2 p-sm-3 rounded-3 bg-info bg-opacity-10 text-info fs-4 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="metric-label small fw-bold text-uppercase" style="font-size:0.68rem; letter-spacing: 0.05em;">SALES PIC / OWNER</div>
                                <div class="metric-value fw-bold fs-6 fs-sm-5 text-truncate" id="detail_sales">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tabs for Sub-relations (Horizontal Scrollable di Mobile) -->
                    <div class="col-12 mt-2">
                        <ul class="nav nav-tabs nav-tabs-custom mb-3 tender-tabs-scrollable" id="detailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold px-3.5 py-2" id="detail-item-tab" data-bs-toggle="tab" data-bs-target="#detail-item" type="button" role="tab">
                                    <i class="bi bi-list-stars me-1.5"></i> 1. Rincian Item / Spesifikasi Kebutuhan
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-3.5 py-2" id="detail-dokumen-tab" data-bs-toggle="tab" data-bs-target="#detail-dokumen" type="button" role="tab">
                                    <i class="bi bi-paperclip me-1.5"></i> 2. Berkas Dokumen Lelang
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-3.5 py-2" id="detail-followup-tab" data-bs-toggle="tab" data-bs-target="#detail-followup" type="button" role="tab">
                                    <i class="bi bi-journal-text me-1.5"></i> 3. Catatan & Log Aktivitas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-3.5 py-2" id="detail-approval-tab" data-bs-toggle="tab" data-bs-target="#detail-approval" type="button" role="tab">
                                    <i class="bi bi-shield-check me-1.5"></i> 4. Persetujuan (Approval)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="detailTabsContent">
                            
                            <!-- TAB 1: ITEM SPESIFIKASI & RAB -->
                            <div class="tab-pane fade show active" id="detail-item" role="tabpanel">
                                <!-- Mobile Swipe Hint Banner -->
                                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-1.5 mb-2 rounded-3 text-primary shadow-2xs" style="background: rgba(37, 99, 235, 0.08); font-size: 0.775rem; border: 1px dashed rgba(37, 99, 235, 0.25);">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <i class="bi bi-arrow-left-right"></i>
                                        <span>Tabel rincian item dapat digeser ke samping</span>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
                                </div>

                                <div class="table-responsive border rounded-4 tender-workflow-card mb-3.5 shadow-xs" style="-webkit-overflow-scrolling: touch !important;">
                                    <table class="table align-middle mb-0 tender-rab-table-cinematic" style="min-width: 620px; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px; min-width: 50px;" class="text-center">No</th>
                                                <th style="min-width: 280px;">Nama Barang / Jasa &amp; Spesifikasi Kebutuhan</th>
                                                <th class="text-center" style="width: 130px; min-width: 110px;">Kuantiti (Qty)</th>
                                                <th class="text-center" style="width: 110px; min-width: 90px;">Satuan</th>
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
                                <div class="card p-4 border rounded-4 mb-4 tender-workflow-card shadow-xs">
                                    <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2">
                                        <h6 class="fw-bold fs-6 mb-0 d-flex align-items-center gap-2.5">
                                            <span class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                <i class="bi bi-journal-plus fs-5"></i>
                                            </span>
                                            <span>Tambah Catatan Aktivitas / Log Follow-Up Baru</span>
                                        </h6>
                                        <span class="text-muted small" style="font-size: 0.78rem;">Catat riwayat pertemuan, progress negosiasi, atau hasil Aanwijzing</span>
                                    </div>
                                    <form id="form_add_followup" autocomplete="off">
                                        <input type="hidden" id="followup_tanggal" value="{{ date('Y-m-d') }}">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-12 col-md-5">
                                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                                    <label class="form-label small fw-bold text-muted mb-0"><i class="bi bi-tag-fill me-1 text-primary"></i>Aktivitas / Kegiatan</label>
                                                    <span id="counter_followup_aktivitas" class="badge bg-body-tertiary text-muted border font-monospace" style="font-size: 0.7rem;">0/60</span>
                                                </div>
                                                <input type="text" id="followup_aktivitas" class="form-control tender-input" placeholder="Contoh: Bertemu Panitia, Klarifikasi Teknis..." maxlength="60" autocomplete="off" required>
                                            </div>
                                            <div class="col-12 col-md-7">
                                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                                    <label class="form-label small fw-bold text-muted mb-0"><i class="bi bi-card-text me-1 text-primary"></i>Catatan / Keterangan</label>
                                                    <span id="counter_followup_keterangan" class="badge bg-body-tertiary text-muted border font-monospace" style="font-size: 0.7rem;">0/80</span>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <input type="text" id="followup_keterangan" class="form-control tender-input flex-grow-1" placeholder="Rincian hasil pertemuan/progress..." maxlength="80" autocomplete="off" required>
                                                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4 shadow-sm d-inline-flex align-items-center gap-1.5 flex-shrink-0" id="btn_submit_followup" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border:none;">
                                                        <i class="bi bi-send-fill"></i>
                                                        <span>Kirim</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                                    <h6 class="fw-bold mb-0 text-body fs-6 d-flex align-items-center gap-2">
                                        <i class="bi bi-clock-history text-primary"></i>
                                        <span>Riwayat Catatan Aktivitas Follow-Up</span>
                                    </h6>
                                    <span class="text-muted small" id="followup_total_count" style="font-size:0.78rem;"></span>
                                </div>

                                <div id="detail_followup_timeline" class="d-flex flex-column gap-3">
                                    <!-- Timeline items -->
                                </div>
                            </div>

                            <!-- TAB 4: APPROVAL LOG -->
                            <div class="tab-pane fade" id="detail-approval" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-body fs-6"><i class="bi bi-shield-check text-success me-1.5"></i>Riwayat Persetujuan & Lifecycle Tender</h6>
                                        <div class="text-muted small" style="font-size:0.78rem;">Catatan lengkap seluruh tahapan tender, pengajuan, persetujuan atasan, penawaran, negosiasi, hingga keputusan akhir.</div>
                                    </div>
                                </div>
                                <div id="detail_approval_history" class="d-flex flex-column gap-3">
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
                        <input type="text" id="input_nilai_deal_tender_mask" class="form-control text-end font-monospace fw-bold text-success fs-6" placeholder="0" autocomplete="off">
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
                    <textarea id="input_lost_reason_tender" class="form-control rounded-3" rows="3" placeholder="Contoh: Kalah penawaran harga dari kompetitor / Evaluasi teknis tidak memenuhi syarat..." autocomplete="off"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Kompetitor Pemenang (Opsional)</label>
                    <input type="text" id="input_winning_competitor_tender" class="form-control rounded-3" placeholder="Contoh: PT Sumber Rezeki Makmur" autocomplete="off">
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
                
                let dealVal = Number(t.nilai_deal) || 0;
                let estVal = Number(t.nilai_estimasi) || 0;
                if (t.status === 'Won' && dealVal > 0) {
                    $('#detail_pagu_label').text('NILAI KESEPAKATAN FINAL (WON):');
                    $('#detail_nilai_estimasi').text(formatRp(dealVal));
                    if (estVal > 0 && dealVal !== estVal) {
                        $('#detail_pagu_subtext').html(`<i class="bi bi-info-circle me-1"></i>Pagu Anggaran Awal: <strong class="text-body">${formatRp(estVal)}</strong>`).show();
                    } else {
                        $('#detail_pagu_subtext').hide();
                    }
                } else {
                    $('#detail_pagu_label').text('TOTAL ESTIMASI NILAI PROYEK:');
                    $('#detail_nilai_estimasi').text(formatRp(estVal));
                    $('#detail_pagu_subtext').hide();
                }

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
                    if (res.can_kirim_lelang) {
                        actionsHtml = `
                            <button type="button" class="btn btn-info text-white rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_advance_to_sent">
                                <i class="bi bi-send-fill fs-6"></i>
                                <span>📤 Kirim Penawaran ke Klien / Panitia</span>
                            </button>
                        `;
                    } else {
                        actionsHtml = `
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-bold font-monospace" style="font-size:0.78rem;">
                                <i class="bi bi-patch-check-fill me-1"></i> Disetujui (Menunggu Pengiriman oleh Tim Berwenang)
                            </span>
                        `;
                    }
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
                    if (res.can_negosiasi) {
                        actionsHtml = `
                            <button type="button" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_advance_to_negotiation">
                                <i class="bi bi-chat-dots-fill fs-6"></i>
                                <span>🤝 Mulai Tahap Aanwijzing & Negosiasi</span>
                            </button>
                        `;
                    } else {
                        actionsHtml = `
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-bold font-monospace" style="font-size:0.78rem;">
                                <i class="bi bi-send-check-fill me-1"></i> Penawaran Terkirim ke Panitia
                            </span>
                        `;
                    }
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
                    if (res.can_keputusan_final) {
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
                    } else {
                        actionsHtml = `
                            <span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle px-3 py-2 rounded-pill fw-bold font-monospace" style="font-size:0.78rem;">
                                <i class="bi bi-chat-quote-fill me-1"></i> Aanwijzing & Negosiasi Sedang Berlangsung
                            </span>
                        `;
                    }
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
                    bannerHtml = `
                        <div class="p-3.5 rounded-3 bg-success-subtle border border-success border-opacity-30 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-xs">
                            <div class="d-flex align-items-center gap-3">
                                <span class="p-2.5 rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center shadow-xs">
                                    <i class="bi bi-trophy-fill fs-4"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-success fs-6">Tender Resmi Berhasil Dimenangkan (Won)! 🎉</div>
                                    <div class="text-secondary small mt-0.5">
                                        Nilai Kesepakatan Final: <strong class="text-body">${formatRp(t.nilai_deal || t.nilai_estimasi)}</strong>
                                    </div>
                                </div>
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
                let paguAnggaran = Number((t.status === 'Won' && Number(t.nilai_deal) > 0) ? t.nilai_deal : (t.nilai_estimasi || 0));
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
                    $('#followup_total_count').text(`${t.followups.length} Catatan Tersimpan`);
                    t.followups.forEach((f, idx) => {
                        let tglStr = f.tanggal ? (typeof f.tanggal === 'string' ? f.tanggal.split('T')[0] : f.tanggal) : '';
                        let userName = f.user ? (f.user.name || f.user.username || 'User') : 'Sistem';
                        let initial = (userName.substring(0, 2)).toUpperCase();
                        
                        fHtml += `
                            <div class="tender-log-card transition-all">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge tender-log-badge px-3 py-1.5 rounded-pill fw-bold font-monospace">
                                            <i class="bi bi-chat-left-dots-fill me-1.5"></i>${escapeHtml(f.aktivitas)}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge tender-log-date px-3 py-1.5 rounded-pill font-monospace">
                                            <i class="bi bi-calendar-event me-1.5 text-primary"></i>${escapeHtml(tglStr)}
                                        </span>
                                    </div>
                                </div>
                                <div class="tender-log-text my-2.5">${escapeHtml(f.keterangan || '')}</div>
                                <div class="tender-log-footer d-flex align-items-center pt-2.5 mt-2.5 border-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="tender-log-avatar">${initial}</div>
                                        <div class="small">
                                            <span class="text-muted">Dicatat oleh:</span>
                                            <strong class="text-body ms-1">${escapeHtml(userName)}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    $('#followup_total_count').text('0 Catatan');
                    fHtml = `
                        <div class="text-center py-5 border rounded-4 bg-body-tertiary">
                            <div class="p-3 rounded-circle bg-body text-muted d-inline-flex mb-2 shadow-2xs">
                                <i class="bi bi-journal-x fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Belum Ada Catatan Aktivitas</h6>
                            <p class="text-muted small mb-0">Gunakan formulir di atas untuk mencatat progress negosiasi, pertemuan, atau klarifikasi teknis.</p>
                        </div>
                    `;
                }
                $('#detail_followup_timeline').html(fHtml);

                // Populate Approvals & Stage Lifecycle History
                let aHtml = '';
                if (t.approvals && t.approvals.length > 0) {
                    t.approvals.forEach(a => {
                        let userName = a.user ? (a.user.nama_lengkap || a.user.name || a.user.username || 'User') : 'Sistem';
                        let initial = (userName.substring(0, 2)).toUpperCase();
                        
                        let dtRaw = a.approved_at || a.created_at || '';
                        let timeStr = '-';
                        if (dtRaw) {
                            try {
                                let d = new Date(dtRaw);
                                if (!isNaN(d.getTime())) {
                                    let day = String(d.getDate()).padStart(2, '0');
                                    let months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                    let month = months[d.getMonth()] || '';
                                    let year = d.getFullYear();
                                    let hours = String(d.getHours()).padStart(2, '0');
                                    let mins = String(d.getMinutes()).padStart(2, '0');
                                    timeStr = `${day} ${month} ${year}, ${hours}:${mins}`;
                                } else {
                                    timeStr = dtRaw;
                                }
                            } catch (e) {
                                timeStr = dtRaw;
                            }
                        }

                        let statusCfg = {
                            'Draft': {
                                badgeClass: 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                icon: 'bi-file-earmark-text',
                                label: 'Draft Proposal',
                                barColor: '#64748b'
                            },
                            'Pending Approval': {
                                badgeClass: 'bg-warning-subtle text-warning border border-warning-subtle',
                                icon: 'bi-hourglass-split',
                                label: 'Menunggu Approval',
                                barColor: '#f59e0b'
                            },
                            'Pending': {
                                badgeClass: 'bg-warning-subtle text-warning border border-warning-subtle',
                                icon: 'bi-hourglass-split',
                                label: 'Menunggu Approval',
                                barColor: '#f59e0b'
                            },
                            'Approved': {
                                badgeClass: 'bg-success-subtle text-success border border-success-subtle',
                                icon: 'bi-patch-check-fill',
                                label: 'Disetujui (Approved)',
                                barColor: '#10b981'
                            },
                            'Rejected': {
                                badgeClass: 'bg-danger-subtle text-danger border border-danger-subtle',
                                icon: 'bi-x-octagon-fill',
                                label: 'Ditolak (Rejected)',
                                barColor: '#ef4444'
                            },
                            'Sent': {
                                badgeClass: 'bg-info-subtle text-info border border-info-subtle',
                                icon: 'bi-send-check-fill',
                                label: 'Penawaran Dikirim',
                                barColor: '#0284c7'
                            },
                            'Negotiation': {
                                badgeClass: 'bg-indigo-subtle text-indigo border border-indigo-subtle',
                                icon: 'bi-chat-quote-fill',
                                label: 'Aanwijzing & Negosiasi',
                                barColor: '#6366f1'
                            },
                            'Won': {
                                badgeClass: 'bg-success-subtle text-success border border-success-subtle',
                                icon: 'bi-trophy-fill',
                                label: 'Pemenang (Won)',
                                barColor: '#10b981'
                            },
                            'Lost': {
                                badgeClass: 'bg-danger-subtle text-danger border border-danger-subtle',
                                icon: 'bi-x-circle-fill',
                                label: 'Kalah Lelang (Lost)',
                                barColor: '#ef4444'
                            },
                            'Converted to SO': {
                                badgeClass: 'bg-teal-subtle text-teal border border-teal-subtle',
                                icon: 'bi-trophy-fill',
                                label: 'Pemenang (SPK)',
                                barColor: '#10b981'
                            },
                            'Cancelled': {
                                badgeClass: 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                icon: 'bi-slash-circle',
                                label: 'Dibatalkan',
                                barColor: '#6b7280'
                            }
                        };

                        let cfg = statusCfg[a.status] || {
                            badgeClass: 'bg-primary-subtle text-primary border border-primary-subtle',
                            icon: 'bi-check2-circle',
                            label: a.status,
                            barColor: '#3b82f6'
                        };

                        let statusPill = '';
                        if (a.status === 'Approved') {
                            statusPill = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;"><i class="bi bi-shield-check me-1"></i>Persetujuan Sah</span>`;
                        } else if (a.status === 'Rejected') {
                            statusPill = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;"><i class="bi bi-shield-x me-1"></i>Evaluasi / Revisi</span>`;
                        } else if (a.status === 'Won' || a.status === 'Converted to SO') {
                            statusPill = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.72rem;"><i class="bi bi-trophy me-1"></i>Closing Berhasil</span>`;
                        }

                        let catatanText = a.catatan ? escapeHtml(a.catatan) : '<span class="text-muted fst-italic">Tidak ada catatan khusus.</span>';

                        aHtml += `
                            <div class="tender-log-card transition-all" style="border-left-color: ${cfg.barColor} !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge ${cfg.badgeClass} rounded-pill px-3 py-1.5 fw-bold font-monospace" style="font-size:0.8rem;">
                                            <i class="bi ${cfg.icon} me-1.5"></i>${cfg.label}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge tender-log-date px-3 py-1.5 rounded-pill font-monospace">
                                            <i class="bi bi-clock-history me-1.5 text-primary"></i>${escapeHtml(timeStr)}
                                        </span>
                                    </div>
                                </div>
                                <div class="tender-log-text my-2.5">
                                    ${catatanText}
                                </div>
                                <div class="tender-log-footer d-flex align-items-center justify-content-between pt-2.5 mt-2.5 border-top flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="tender-log-avatar">${initial}</div>
                                        <div class="small">
                                            <span class="text-muted">Pelaksana / Approver:</span>
                                            <strong class="text-body ms-1">${escapeHtml(userName)}</strong>
                                        </div>
                                    </div>
                                    <div>
                                        ${statusPill}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    aHtml = `
                        <div class="text-center py-5 border rounded-4 bg-body-tertiary">
                            <div class="p-3 rounded-circle bg-body text-muted d-inline-flex mb-2 shadow-2xs">
                                <i class="bi bi-shield-slash fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-body mb-1">Belum Ada Riwayat Persetujuan</h6>
                            <p class="text-muted small mb-0">Riwayat persetujuan, validasi atasan, dan perubahan tahapan lelang akan tercatat otomatis di sini.</p>
                        </div>
                    `;
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

        // Live character counters for Follow-Up
        $(document).on('input', '#followup_aktivitas', function () {
            let len = $(this).val().length;
            $('#counter_followup_aktivitas').text(`${len}/60`);
            if (len >= 60) {
                $('#counter_followup_aktivitas').removeClass('text-muted').addClass('text-danger fw-bold');
            } else {
                $('#counter_followup_aktivitas').removeClass('text-danger fw-bold').addClass('text-muted');
            }
        });

        $(document).on('input', '#followup_keterangan', function () {
            let len = $(this).val().length;
            $('#counter_followup_keterangan').text(`${len}/80`);
            if (len >= 80) {
                $('#counter_followup_keterangan').removeClass('text-muted').addClass('text-danger fw-bold');
            } else {
                $('#counter_followup_keterangan').removeClass('text-danger fw-bold').addClass('text-muted');
            }
        });

        // Add Follow-Up Submit
        $('#form_add_followup').on('submit', function (e) {
            e.preventDefault();
            if (!currentDetailTenderId) {
                Swal.fire('Peringatan', 'ID Tender tidak ditemukan.', 'warning');
                return;
            }

            let tgl = $('#followup_tanggal').val() || new Date().toISOString().split('T')[0];
            let act = ($('#followup_aktivitas').val() || '').trim();
            let ket = ($('#followup_keterangan').val() || '').trim();

            if (!act || !ket) {
                Swal.fire('Peringatan', 'Aktivitas dan catatan follow-up wajib diisi.', 'warning');
                return;
            }

            if (act.length > 60) {
                Swal.fire('Peringatan Karakter', 'Aktivitas / Kegiatan maksimal 60 karakter!', 'warning');
                $('#followup_aktivitas').focus();
                return;
            }

            if (ket.length > 80) {
                Swal.fire('Peringatan Karakter', 'Catatan / Keterangan maksimal 80 karakter!', 'warning');
                $('#followup_keterangan').focus();
                return;
            }

            let btn = $('#btn_submit_followup');
            let origHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');

            $.ajax({
                url: `/tender/${currentDetailTenderId}/followup`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    tanggal: tgl,
                    aktivitas: act,
                    keterangan: ket
                },
                success: function (res) {
                    $('#followup_aktivitas').val('').trigger('input');
                    $('#followup_keterangan').val('').trigger('input');
                    Swal.fire({
                        icon: 'success',
                        title: 'Aktivitas Dicatat!',
                        text: res.message || 'Catatan aktivitas berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Refresh Detail
                    window.loadTenderDetail(currentDetailTenderId, false);
                    refreshTenderViews();
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menyimpan catatan follow-up.';
                    Swal.fire('Gagal Menyimpan', msg, 'error');
                },
                complete: function () {
                    btn.prop('disabled', false).html(origHtml);
                }
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
