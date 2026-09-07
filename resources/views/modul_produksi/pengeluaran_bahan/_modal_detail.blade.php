<!-- MODAL DETAIL & RIWAYAT PENGELUARAN BAHAN SPK (DUAL THEME: LIGHT & DARK READY) -->
<style>
    /* ─── LIGHT THEME (DEFAULT) ─── */
    #modalDetailPengeluaran .modal-content {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
        border-radius: 24px !important;
        color: #0f172a !important;
    }

    #modalDetailPengeluaran .spk-cinematic-header {
        background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
        position: relative;
        overflow: hidden;
    }

    #modalDetailPengeluaran .spk-cinematic-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2.5px;
        background: linear-gradient(90deg, #38bdf8, #818cf8, #38bdf8);
        background-size: 200% auto;
        animation: spkGlowGradient 4s linear infinite;
    }

    @keyframes spkGlowGradient {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }

    #modalDetailPengeluaran .modal-body {
        background: #f8fafc !important;
    }

    #modalDetailPengeluaran .modal-footer {
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
    }

    #modalDetailPengeluaran .spk-hero-metric-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px;
        padding: 1rem 1.15rem;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    #modalDetailPengeluaran .spk-hero-metric-card:hover {
        border-color: #2563eb !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -6px rgba(37, 99, 235, 0.15);
    }

    #modalDetailPengeluaran .spk-metric-label {
        color: #64748b !important;
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        display: block;
        margin-bottom: 0.25rem;
    }

    #modalDetailPengeluaran .spk-metric-value {
        color: #0a2540 !important;
        font-weight: 800 !important;
        font-size: 1.25rem !important;
        line-height: 1.2;
    }

    #modalDetailPengeluaran .spk-metric-sub {
        color: #64748b !important;
        font-size: 0.8rem !important;
    }

    #modalDetailPengeluaran .spk-cinematic-tab-btn {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #475569 !important;
        border-radius: 50rem !important;
        padding: 0.6rem 1.4rem !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
        transition: all 0.25s ease !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #modalDetailPengeluaran .spk-cinematic-tab-btn:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }

    #modalDetailPengeluaran .spk-cinematic-tab-btn.active {
        background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%) !important;
        color: #ffffff !important;
        border-color: #0a2540 !important;
        box-shadow: 0 6px 16px -4px rgba(10, 37, 64, 0.4) !important;
    }

    #modalDetailPengeluaran .spk-session-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 20px;
        padding: 1.25rem 1.4rem;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    }

    #modalDetailPengeluaran .spk-session-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4.5px;
        background: linear-gradient(180deg, #0a2540, #1e40af, #3b82f6);
    }

    #modalDetailPengeluaran .spk-session-card:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.12);
        transform: translateY(-2px);
    }

    #modalDetailPengeluaran .spk-session-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid #e2e8f0;
    }

    #modalDetailPengeluaran .spk-session-badge {
        background: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #0a2540 !important;
        font-family: var(--bs-font-monospace);
        font-weight: 800;
        font-size: 0.85rem;
        padding: 0.35rem 0.8rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        letter-spacing: 0.05em;
    }

    #modalDetailPengeluaran .spk-logistics-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        margin: 0.9rem 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    }

    #modalDetailPengeluaran .spk-logistics-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        display: block;
        margin-bottom: 2px;
    }

    #modalDetailPengeluaran .spk-logistics-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    #modalDetailPengeluaran .spk-notes-box {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 3.5px solid #f59e0b;
        border-radius: 8px;
        padding: 0.5rem 0.85rem;
        margin-top: 0.75rem;
        font-size: 0.82rem;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    #modalDetailPengeluaran .spk-table-box {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px;
        overflow: hidden;
    }

    #modalDetailPengeluaran table.spk-detail-table {
        color: #0f172a !important;
        margin-bottom: 0;
    }

    #modalDetailPengeluaran table.spk-detail-table thead th {
        background: #f1f5f9 !important;
        color: #0a2540 !important;
        border-bottom: 1.5px solid #cbd5e1 !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.85rem 0.9rem;
    }

    #modalDetailPengeluaran table.spk-detail-table tbody td {
        background: transparent !important;
        color: #0f172a !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0.85rem 0.9rem;
        vertical-align: middle;
    }

    #modalDetailPengeluaran table.spk-detail-table tbody tr:hover td {
        background: #f0f7ff !important;
    }

    .spk-text-contrast {
        color: #0f172a !important;
    }
    .spk-text-muted-contrast {
        color: #64748b !important;
    }
    .text-high-contrast {
        color: #0f172a !important;
        font-weight: 700;
    }
    .text-sub-contrast {
        color: #334155 !important;
    }
    .text-muted-contrast {
        color: #64748b !important;
    }

    /* ─── DARK THEME OVERRIDES ─── */
    html[data-bs-theme="dark"] #modalDetailPengeluaran .modal-content,
    [data-bs-theme="dark"] #modalDetailPengeluaran .modal-content,
    .dark-mode #modalDetailPengeluaran .modal-content {
        background: #0f172a !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-header,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-header,
    .dark-mode #modalDetailPengeluaran .spk-cinematic-header {
        background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #0f172a 100%) !important;
        border-bottom: 1px solid rgba(99, 102, 241, 0.3) !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .modal-body,
    [data-bs-theme="dark"] #modalDetailPengeluaran .modal-body,
    .dark-mode #modalDetailPengeluaran .modal-body {
        background: #0f172a !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .modal-footer,
    [data-bs-theme="dark"] #modalDetailPengeluaran .modal-footer,
    .dark-mode #modalDetailPengeluaran .modal-footer {
        background: #090d16 !important;
        border-top: 1px solid #334155 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-hero-metric-card,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-hero-metric-card,
    .dark-mode #modalDetailPengeluaran .spk-hero-metric-card {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        box-shadow: none;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-label,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-label,
    .dark-mode #modalDetailPengeluaran .spk-metric-label {
        color: #94a3b8 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-value,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-value,
    .dark-mode #modalDetailPengeluaran .spk-metric-value {
        color: #38bdf8 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-sub,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-metric-sub,
    .dark-mode #modalDetailPengeluaran .spk-metric-sub {
        color: #cbd5e1 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn,
    .dark-mode #modalDetailPengeluaran .spk-cinematic-tab-btn {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #cbd5e1 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn:hover,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn:hover,
    .dark-mode #modalDetailPengeluaran .spk-cinematic-tab-btn:hover {
        background: #334155 !important;
        color: #ffffff !important;
        border-color: #64748b !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn.active,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-cinematic-tab-btn.active,
    .dark-mode #modalDetailPengeluaran .spk-cinematic-tab-btn.active {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
        color: #ffffff !important;
        border-color: #818cf8 !important;
        box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.6) !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-card,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-card,
    .dark-mode #modalDetailPengeluaran .spk-session-card {
        background: #111827 !important;
        border: 1px solid rgba(99, 102, 241, 0.25) !important;
        box-shadow: none;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-header-top,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-header-top,
    .dark-mode #modalDetailPengeluaran .spk-session-header-top {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-badge,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-session-badge,
    .dark-mode #modalDetailPengeluaran .spk-session-badge {
        background: linear-gradient(135deg, rgba(30, 27, 75, 0.9), rgba(49, 46, 129, 0.9)) !important;
        border: 1px solid rgba(99, 102, 241, 0.5) !important;
        color: #38bdf8 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-bar,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-bar,
    .dark-mode #modalDetailPengeluaran .spk-logistics-bar {
        background: rgba(15, 23, 42, 0.7) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-label,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-label,
    .dark-mode #modalDetailPengeluaran .spk-logistics-label {
        color: #94a3b8 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-value,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-logistics-value,
    .dark-mode #modalDetailPengeluaran .spk-logistics-value {
        color: #ffffff !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-notes-box,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-notes-box,
    .dark-mode #modalDetailPengeluaran .spk-notes-box {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.25);
        border-left: 3.5px solid #f59e0b;
        color: #fde68a;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran .spk-table-box,
    [data-bs-theme="dark"] #modalDetailPengeluaran .spk-table-box,
    .dark-mode #modalDetailPengeluaran .spk-table-box {
        background: #0f172a !important;
        border: 1px solid #334155 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table,
    [data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table,
    .dark-mode #modalDetailPengeluaran table.spk-detail-table {
        color: #f1f5f9 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table thead th,
    [data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table thead th,
    .dark-mode #modalDetailPengeluaran table.spk-detail-table thead th {
        background: #090d16 !important;
        color: #93c5fd !important;
        border-bottom: 1px solid #334155 !important;
    }

    html[data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table tbody td,
    [data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table tbody td,
    .dark-mode #modalDetailPengeluaran table.spk-detail-table tbody td {
        color: #f1f5f9 !important;
        border-bottom: 1px solid #1e293b !important;
    }

    /* ── Dark mode: override hover row agar teks tetap keliatan ── */
    html[data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table tbody tr:hover td,
    [data-bs-theme="dark"] #modalDetailPengeluaran table.spk-detail-table tbody tr:hover td,
    .dark-mode #modalDetailPengeluaran table.spk-detail-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-bs-theme="dark"] .spk-text-contrast,
    [data-bs-theme="dark"] .spk-text-contrast,
    .dark-mode .spk-text-contrast,
    html[data-bs-theme="dark"] .text-high-contrast,
    [data-bs-theme="dark"] .text-high-contrast,
    .dark-mode .text-high-contrast {
        color: #ffffff !important;
    }

    html[data-bs-theme="dark"] .spk-text-muted-contrast,
    [data-bs-theme="dark"] .spk-text-muted-contrast,
    .dark-mode .spk-text-muted-contrast,
    html[data-bs-theme="dark"] .text-muted-contrast,
    [data-bs-theme="dark"] .text-muted-contrast,
    .dark-mode .text-muted-contrast {
        color: #94a3b8 !important;
    }

    /* ─── MODAL DETAIL JURNAL: DUAL THEME ─── */
    #modalDetailJurnalPengeluaran .modal-content {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
    }
    #modalDetailJurnalPengeluaran .modal-body {
        background: #f8fafc !important;
    }
    #modalDetailJurnalPengeluaran .jurnal-summary-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }

    html[data-bs-theme="dark"] #modalDetailJurnalPengeluaran .modal-content,
    [data-bs-theme="dark"] #modalDetailJurnalPengeluaran .modal-content,
    .dark-mode #modalDetailJurnalPengeluaran .modal-content {
        background: #0f172a !important;
        border: 1px solid rgba(99, 102, 241, 0.35) !important;
        color: #f8fafc !important;
    }
    html[data-bs-theme="dark"] #modalDetailJurnalPengeluaran .modal-body,
    [data-bs-theme="dark"] #modalDetailJurnalPengeluaran .modal-body,
    .dark-mode #modalDetailJurnalPengeluaran .modal-body {
        background: #0f172a !important;
    }
    html[data-bs-theme="dark"] #modalDetailJurnalPengeluaran .jurnal-summary-card,
    [data-bs-theme="dark"] #modalDetailJurnalPengeluaran .jurnal-summary-card,
    .dark-mode #modalDetailJurnalPengeluaran .jurnal-summary-card {
        background: #1e293b !important;
        border: 1px solid #334155 !important;
    }
</style>

<div class="modal fade spk-modal-secondary" id="modalDetailPengeluaran" tabindex="-1" aria-labelledby="modalDetailPengeluaranLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1050;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1520px, 96vw); width: 96vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- MODAL HEADER (CINEMATIC) --}}
            <div class="modal-header spk-cinematic-header py-3 px-3 px-sm-4 text-white">
                <div class="d-flex align-items-center gap-3 me-auto" style="min-width: 0;">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(99, 102, 241, 0.5); width: 44px; height: 44px;">
                        <i class="bi bi-layers-half fs-4 text-info"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-sm-5 text-truncate" id="det_spk_title">Detail & Riwayat Pengeluaran</h5>
                            <span class="badge badge-status-in-progress rounded-pill px-3 py-1 font-monospace" id="det_highlight_spk_status" style="font-size: 11.5px;">
                                In Progress
                            </span>
                        </div>
                        <p class="small mb-0 text-white-50 text-truncate" id="det_spk_subtitle">Monitoring pemenuhan material BOM dan riwayat serah terima bertahap</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-sm-4">
                {{-- HERO METRICS GRID (4 TILES) --}}
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="spk-hero-metric-card h-100">
                            <span class="spk-metric-label">Nomor SPK Produksi</span>
                            <div class="spk-metric-value font-monospace" id="det_highlight_spk_nomor">-</div>
                            <small class="spk-metric-sub d-block mt-0.5" id="det_highlight_spk_dates">-</small>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="spk-hero-metric-card h-100">
                            <span class="spk-metric-label">Barang Jadi (Output)</span>
                            <div class="text-high-contrast text-truncate fs-6" id="det_highlight_barang_jadi">-</div>
                            <div class="d-flex align-items-center gap-1.5 mt-0.5 small">
                                <span class="text-muted-contrast">Target:</span>
                                <b class="text-info font-monospace" id="det_highlight_target_qty">-</b>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <div class="spk-hero-metric-card h-100">
                            <span class="spk-metric-label">Frekuensi Sesi</span>
                            <div class="spk-metric-value font-monospace mb-0" id="det_highlight_total_sesi">0 Sesi</div>
                            <small class="spk-metric-sub">Dokumen Pengeluaran</small>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="spk-hero-metric-card h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="spk-metric-label mb-0">Status Pemenuhan</span>
                                <span class="fw-bold font-monospace text-success fs-6" id="det_highlight_overall_percent">0%</span>
                            </div>
                            <div class="progress mb-1.5" style="height: 7px; border-radius: 6px; background-color: rgba(148, 163, 184, 0.2);">
                                <div id="det_highlight_overall_progressbar" class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small class="spk-metric-sub d-block" id="det_highlight_overall_status">Memuat rekap...</small>
                        </div>
                    </div>
                </div>

                {{-- MODERN NAV PILLS TABS --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                    <ul class="nav nav-pills gap-2" id="detailPengeluaranTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="spk-cinematic-tab-btn active" id="tab-rekap-tab" data-bs-toggle="pill" data-bs-target="#tab-rekap" type="button" role="tab">
                                <i class="bi bi-stack"></i>
                                <span>Rekapitulasi Bahan SPK</span>
                                <span class="badge bg-primary bg-opacity-20 text-primary rounded-pill px-2 py-0.5 font-monospace" id="badge_count_bahan_tab" style="font-size: 10.5px;">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="spk-cinematic-tab-btn" id="tab-riwayat-tab" data-bs-toggle="pill" data-bs-target="#tab-riwayat" type="button" role="tab">
                                <i class="bi bi-clock-history"></i>
                                <span>Riwayat Sesi Pengeluaran</span>
                                <span class="badge bg-primary bg-opacity-20 text-primary rounded-pill px-2 py-0.5 font-monospace" id="badge_count_sesi_tab" style="font-size: 10.5px;">0</span>
                            </button>
                        </li>
                    </ul>

                    @php
                        $_u = auth()->user();
                        $_canSeeJurnalFullInModal = $_u ? ($_u->hasAnyRole(['Super Admin', 'Owner']) || $_u->can('lihat-jurnal-full-pengeluaran-bahan') || $_u->can('lihat-jurnal-pengeluaran-bahan')) : false;
                    @endphp
                    @if($_canSeeJurnalFullInModal)
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs"
                                onclick="showSpkJurnalFullPbModal(_currentSpkId)" title="Lihat Rekapitulasi Jurnal Full Pengeluaran Bahan SPK Ini">
                            <i class="bi bi-journal-bookmark-fill" style="color:#0284c7;"></i>
                            <span>Jurnal Full Pengeluaran</span>
                        </button>
                    </div>
                    @endif
                </div>

                {{-- TAB CONTENTS --}}
                <div class="tab-content" id="detailPengeluaranTabContent">
                    {{-- TAB 1: REKAPITULASI BAHAN --}}
                    <div class="tab-pane fade show active" id="tab-rekap" role="tabpanel">
                        <div class="spk-table-box shadow-xs">
                            <div class="spk-table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table align-middle spk-detail-table w-100" style="min-width: 1050px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 55px;" class="text-center ps-3">No</th>
                                            <th style="min-width: 320px;">Item Bahan Mentah (BOM)</th>
                                            <th class="text-center" style="width: 170px;">Total SPK</th>
                                            <th class="text-center" style="width: 170px;">Sudah Diambil</th>
                                            <th class="text-center" style="width: 170px;">Sisa Kebutuhan</th>
                                            <th class="text-center pe-3" style="width: 260px;">Progres Penyerahan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_rekap_materials">
                                        {{-- Populated dynamically --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: RIWAYAT SESI PENGELUARAN --}}
                    <div class="tab-pane fade" id="tab-riwayat" role="tabpanel">
                        <div id="container_riwayat_sessions" class="d-flex flex-column gap-3">
                            {{-- Populated dynamically with timeline session cards --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer d-flex justify-content-end p-3 p-sm-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETAIL JURNAL AKUNTANSI OTOMATIS (CINEMATIC ERP VIEWER) -->
<div class="modal fade spk-modal-secondary" id="modalDetailJurnalPengeluaran" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1085;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- MODAL HEADER --}}
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%) !important;">
                <div class="d-flex align-items-center gap-3 me-auto">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); width: 44px; height: 44px;">
                        <i class="bi bi-journal-check fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-white mb-0 fs-5" id="mjurnal_title">Jurnal Akuntansi Otomatis</h5>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 11px;">
                                <i class="bi bi-check-circle-fill me-1"></i>Seimbang (Balanced)
                            </span>
                        </div>
                        <p class="small mb-0 text-white-50" id="mjurnal_subtitle">Pencatatan mutasi persediaan barang & biaya operasional SPK</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-sm-4">
                {{-- TOP SUMMARY STRIP --}}
                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-4">
                        <div class="p-3 rounded-3 jurnal-summary-card">
                            <span class="spk-metric-label">Nomor Jurnal</span>
                            <div class="fw-bold text-primary font-monospace fs-6 text-truncate" id="mjurnal_nomor">-</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="p-3 rounded-3 jurnal-summary-card">
                            <span class="spk-metric-label">Total Debit</span>
                            <div class="fw-bold spk-text-contrast font-monospace fs-6" id="mjurnal_total_debit">Rp 0</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="p-3 rounded-3 jurnal-summary-card">
                            <span class="spk-metric-label">Total Kredit</span>
                            <div class="fw-bold spk-text-contrast font-monospace fs-6" id="mjurnal_total_kredit">Rp 0</div>
                        </div>
                    </div>
                </div>

                {{-- JURNAL ENTRIES TABLE --}}
                <div class="spk-table-box shadow-xs">
                    <div class="spk-table-wrapper" style="overflow-x: auto;">
                        <table class="table align-middle spk-detail-table w-100" style="min-width: 650px;">
                            <thead>
                                <tr>
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th style="min-width: 250px;">Akun Rekening (COA)</th>
                                    <th style="min-width: 200px;">Keterangan</th>
                                    <th class="text-end" style="width: 150px;">Debit</th>
                                    <th class="text-end pe-3" style="width: 150px;">Kredit</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_modal_jurnal_rows">
                                {{-- Populated dynamically --}}
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold spk-text-contrast border-top border-secondary border-opacity-25" style="background: rgba(148, 163, 184, 0.1);">
                                    <td colspan="3" class="text-end ps-3 text-primary">TOTAL KESEIMBANGAN:</td>
                                    <td class="text-end font-monospace text-primary fs-6" id="mjurnal_foot_debit">Rp 0</td>
                                    <td class="text-end font-monospace text-primary pe-3 fs-6" id="mjurnal_foot_kredit">Rp 0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

<div class="modal-footer d-flex justify-content-between p-3 px-sm-4">
                <small class="spk-text-muted-contrast"><i class="bi bi-shield-lock-fill me-1 text-primary"></i>Jurnal otomatis diposting oleh modul Produksi ERP.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
    $user = auth()->user();
    $canSeeJurnal = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-pengeluaran-bahan')) : false;
    $canCetak     = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('cetak-pengeluaran-bahan')) : false;
    $canEdit      = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('edit-pengeluaran-bahan')) : false;
@endphp
<script>
    const CAN_SEE_JURNAL_PB = @json($canSeeJurnal);
    const CAN_CETAK_PB     = @json($canCetak);
    const CAN_EDIT_PB      = @json($canEdit);

    // Cache sessions per spkId untuk modal jurnal (hanya halaman aktif)
    let _currentSpkId = null;
    let _sessionCache = {};     // sessionId => session object
    let _historyPagination = { current: 1, last: 1, total: 0, per_page: 25, from: 0, to: 0 };

    function showSpkHistoryModal(spkId) {
        _currentSpkId = spkId;
        _sessionCache = {};

        Swal.fire({
            title: 'Memuat Data SPK...',
            text: 'Mengambil rekapitulasi material & metadata SPK.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Step 1: Load metadata SPK + rekap bahan (tanpa sessions)
        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/history`,
            method: 'GET',
            success: function(res) {
                Swal.close();
                const spk = res.spk;
                const rekap = res.materials_rekap || [];
                const totalSesi = res.total_sesi || 0;

                // ─── Update header metrics ───
                const bKode = spk.barang_jadi ? spk.barang_jadi.kode_barang : '-';
                const bNama = spk.barang_jadi ? spk.barang_jadi.nama_barang : '-';
                const satName = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');

                $('#det_spk_title').text(`[${spk.nomor_spk}] - ${bNama}`);
                $('#det_highlight_spk_nomor').text(spk.nomor_spk);
                $('#det_highlight_barang_jadi').text(`[${bKode}] ${bNama}`);
                $('#det_highlight_target_qty').text(`${Number(spk.jumlah_rencana).toLocaleString('id-ID')} ${satName}`);
                $('#det_highlight_total_sesi').text(`${totalSesi} Sesi`);
                $('#badge_count_sesi_tab').text(totalSesi);
                $('#badge_count_bahan_tab').text(rekap.length);

                const rawDate = spk.tanggal_spk || spk.created_at;
                const tglMulai = rawDate ? new Date(rawDate).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';
                const targetDate = spk.target_selesai ? new Date(spk.target_selesai).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : null;
                $('#det_highlight_spk_dates').text(targetDate ? `Tgl Terbit: ${tglMulai} • Target: ${targetDate}` : `Tgl Terbit: ${tglMulai}`);

                if (spk.status === 'In Progress') {
                    $('#det_highlight_spk_status').attr('class','badge badge-status-in-progress rounded-pill px-3 py-1 font-monospace').html('<i class="bi bi-play-circle-fill me-1"></i>In Progress');
                } else if (spk.status === 'Completed') {
                    $('#det_highlight_spk_status').attr('class','badge badge-status-completed rounded-pill px-3 py-1 font-monospace').html('<i class="bi bi-check-circle-fill me-1"></i>Completed');
                } else {
                    $('#det_highlight_spk_status').attr('class','badge spk-badge-muted rounded-pill px-3 py-1 font-monospace').text(spk.status);
                }

                // ─── Overall progress dari rekap ───
                let totalReq = 0, totalTaken = 0;
                rekap.forEach(m => {
                    totalReq   += Number(m.qty_total_spk || 0);
                    totalTaken += Number(m.qty_sudah_diambil || 0);
                });
                const rawOverall = totalReq > 0 ? (totalTaken / totalReq) * 100 : 0;
                let overallPercentDisplay = '0%';
                if (totalTaken >= totalReq && totalReq > 0) {
                    overallPercentDisplay = '100%';
                } else if (totalTaken > 0) {
                    overallPercentDisplay = rawOverall < 1 ? rawOverall.toFixed(2) + '%' : rawOverall.toFixed(1) + '%';
                }
                const overallBarWidth = totalTaken >= totalReq && totalReq > 0 ? 100 : (totalTaken > 0 ? Math.max(4, Math.min(99, rawOverall)) : 0);
                $('#det_highlight_overall_percent').text(overallPercentDisplay);
                $('#det_highlight_overall_progressbar').css('width', `${overallBarWidth}%`);
                if (totalTaken >= totalReq && totalReq > 0) {
                    $('#det_highlight_overall_status').html('<span class="text-success-vibrant fw-bold"><i class="bi bi-check2-all me-1"></i>Bahan Baku Lengkap</span>');
                    $('#det_highlight_overall_progressbar').removeClass('bg-primary').addClass('bg-success');
                } else {
                    $('#det_highlight_overall_status').html(`<span class="spk-text-contrast font-monospace">${totalTaken.toLocaleString('id-ID',{maximumFractionDigits:2})} / ${totalReq.toLocaleString('id-ID',{maximumFractionDigits:2})} Total Qty</span>`);
                    $('#det_highlight_overall_progressbar').removeClass('bg-success').addClass('bg-primary');
                }

                // ─── Render tab rekap bahan ───
                let rekapHtml = '';
                rekap.forEach((m, idx) => {
                    const isFull = m.qty_sisa_kebutuhan <= 0 || m.qty_sudah_diambil >= m.qty_total_spk;
                    const rawItemPct = m.qty_total_spk > 0 ? (m.qty_sudah_diambil / m.qty_total_spk) * 100 : 0;
                    let itemPercentDisplay = '0%';
                    if (isFull) {
                        itemPercentDisplay = '100%';
                    } else if (m.qty_sudah_diambil > 0) {
                        itemPercentDisplay = rawItemPct < 1 ? rawItemPct.toFixed(2) + '%' : rawItemPct.toFixed(1) + '%';
                    }
                    const itemBarWidth = isFull ? 100 : (m.qty_sudah_diambil > 0 ? Math.max(4, Math.min(99, rawItemPct)) : 0);
                    const barClass = isFull ? 'bg-success' : (m.qty_sudah_diambil > 0 ? 'bg-info' : 'bg-warning');
                    rekapHtml += `
                        <tr>
                            <td class="text-center font-monospace small ps-3 spk-text-muted-contrast">${idx + 1}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="p-2 rounded-3 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="background:rgba(37,99,235,0.12);width:36px;height:36px;">
                                        <i class="bi bi-box-seam fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold spk-text-contrast fs-6">[${m.kode_barang}] ${m.nama_barang}</div>
                                        <small class="spk-text-muted-contrast">Satuan: <b class="spk-text-contrast">${m.satuan_nama}</b></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center font-monospace small spk-text-contrast">${Number(m.qty_total_spk).toLocaleString('id-ID',{maximumFractionDigits:4})} ${m.satuan_nama}</td>
                            <td class="text-center font-monospace small text-primary fw-bold">${Number(m.qty_sudah_diambil).toLocaleString('id-ID',{maximumFractionDigits:4})} ${m.satuan_nama}</td>
                            <td class="text-center font-monospace small ${isFull?'text-success-vibrant':'text-warning-vibrant'}">
                                ${Number(m.qty_sisa_kebutuhan).toLocaleString('id-ID',{maximumFractionDigits:4})} ${m.satuan_nama}
                            </td>
                            <td class="pe-3">
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:11px;">
                                    <span class="fw-bold ${isFull?'text-success-vibrant':'text-primary'}">${itemPercentDisplay}</span>
                                    <span class="badge ${isFull?'badge-sisa-green':'badge-status-in-progress'} rounded-pill px-2.5 py-0.5" style="font-size:10.5px;">
                                        ${isFull ? '<i class="bi bi-check2"></i> Lengkap' : (m.qty_sudah_diambil > 0 ? 'Sebagian' : 'Belum')}
                                    </span>
                                </div>
                                <div class="progress" style="height:6px;border-radius:6px;background-color:rgba(148,163,184,0.2);">
                                    <div class="progress-bar ${barClass}" role="progressbar" style="width:${itemBarWidth}%;"></div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                if (rekap.length === 0) {
                    rekapHtml = '<tr><td colspan="6" class="text-center py-5 spk-text-muted-contrast">Tidak ada resep bahan pada SPK ini.</td></tr>';
                }
                $('#tbody_rekap_materials').html(rekapHtml);

                // ─── Siapkan tab riwayat (load halaman 1) ───
                $('#container_riwayat_sessions').html(`
                    <div class="text-center py-5 spk-text-muted-contrast">
                        <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
                        <div class="small">Memuat riwayat sesi...</div>
                    </div>
                `);

                $('#tab-rekap-tab').tab('show');
                $('#modalDetailPengeluaran').modal('show');

                // Load sesi halaman 1
                loadHistorySessions(spkId, 1);
            },
            error: function(err) {
                Swal.close();
                Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal memuat data SPK.', 'error');
            }
        });
    }

    function loadHistorySessions(spkId, page) {
        // Tampilkan loading state di container riwayat
        if (page === 1) {
            $('#container_riwayat_sessions').html(`
                <div class="text-center py-5 spk-text-muted-contrast">
                    <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
                    <div class="small">Memuat riwayat sesi halaman ${page}...</div>
                </div>
            `);
        }

        $.ajax({
            url: `/produksi/pengeluaran-bahan/spk/${spkId}/history/sessions`,
            method: 'GET',
            data: { page: page, per_page: 25 },
            success: function(res) {
                if (!res.success) {
                    $('#container_riwayat_sessions').html(`<div class="alert alert-warning">Gagal memuat sesi: ${res.message}</div>`);
                    return;
                }

                // Simpan ke cache untuk modal jurnal
                (res.data || []).forEach(s => { _sessionCache[s.id] = s; });

                // Update pagination state
                _historyPagination = {
                    current: res.current_page,
                    last:    res.last_page,
                    total:   res.total,
                    per_page: res.per_page,
                    from:    res.from || 0,
                    to:      res.to   || 0,
                };

                // Render session cards
                let sessHtml = '';
                const sessions = res.data || [];
                const totalCount = res.total;

                if (sessions.length === 0 && page === 1) {
                    sessHtml = `
                        <div class="spk-session-card text-center p-5">
                            <div class="p-3 rounded-circle bg-primary bg-opacity-20 text-primary d-inline-block mb-3">
                                <i class="bi bi-box-seam fs-2"></i>
                            </div>
                            <h6 class="fw-bold spk-text-contrast mb-1 fs-5">Belum Ada Riwayat Sesi Pengeluaran</h6>
                            <p class="spk-text-muted-contrast small mb-0">Belum ada dokumen penyerahan material yang dicatat untuk SPK ini.</p>
                        </div>
                    `;
                } else {
                    // Nomor sesi dari atas (descending): sesi ke-N = total - from + 1
                    sessions.forEach((s, sIdx) => {
                        const globalIdx = _historyPagination.from + sIdx;         // 1-based from server
                        const sesiNum   = totalCount - globalIdx + 1;              // nomor sesi terbalik
                        const gudangName = s.gudang ? `[${s.gudang.kode_gudang}] ${s.gudang.nama_gudang}` : 'Gudang Utama';

                        let penyerahName = s.penyerah?.nama_lengkap || s.penyerah_nama || s.creator?.nama_lengkap || 'Petugas Gudang';
                        if (!penyerahName || penyerahName === 'undefined') penyerahName = 'Petugas Gudang';

                        let penerimaName = s.penerima?.nama_lengkap || s.penerima_nama || 'Tim Produksi';
                        if (!penerimaName || penerimaName === 'undefined') penerimaName = 'Tim Produksi';

                        const tgl = s.tanggal_pengeluaran ? new Date(s.tanggal_pengeluaran).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';

                        let itemsRows = '';
                        const details = s.details || [];
                        if (details.length === 0) {
                            itemsRows = '<tr><td colspan="5" class="text-center py-3 spk-text-muted-contrast">Tidak ada rincian item bahan pada dokumen ini.</td></tr>';
                        } else {
                            details.forEach((d, dIdx) => {
                                const bm = d.barang_mentah || {};
                                const sat = d.satuan?.nama_satuan || bm.satuan?.nama_satuan || 'Pcs';
                                const totalSpk = Number(d.qty_total_spk || 0);
                                const sisaBefore = Number(d.qty_sisa_kebutuhan != null ? d.qty_sisa_kebutuhan : totalSpk);
                                const qtyTaken = Number(d.qty_diambil_sekarang || 0);
                                const sisaAfter = Math.max(0, sisaBefore - qtyTaken);
                                itemsRows += `
                                    <tr>
                                        <td class="text-center font-monospace small spk-text-muted-contrast ps-3" style="width:55px;">${dIdx+1}</td>
                                        <td>
                                            <div class="fw-bold spk-text-contrast fs-6">[${bm.kode_barang||'-'}] ${bm.nama_barang||'-'}</div>
                                            <small class="spk-text-muted-contrast">Satuan: <b class="spk-text-contrast">${sat}</b></small>
                                        </td>
                                        <td class="text-center font-monospace small spk-text-contrast">${totalSpk.toLocaleString('id-ID',{maximumFractionDigits:4})} ${sat}</td>
                                        <td class="text-center font-monospace small fw-bold text-primary fs-6">${qtyTaken.toLocaleString('id-ID',{maximumFractionDigits:4})} ${sat}</td>
                                        <td class="text-center font-monospace small pe-3">
                                            ${sisaAfter <= 0
                                                ? '<span class="badge badge-sisa-green rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>0 (Lengkap)</span>'
                                                : `<span class="badge badge-sisa-amber rounded-pill px-3 py-1 fw-bold"><i class="bi bi-hourglass-split me-1"></i>Sisa: ${sisaAfter.toLocaleString('id-ID',{maximumFractionDigits:4})} ${sat}</span>`
                                            }
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        const hasJurnal = s.jurnal && s.jurnal.details && s.jurnal.details.length > 0;
                        let sessionActionsHtml = '';
                        if (hasJurnal && CAN_SEE_JURNAL_PB) {
                            sessionActionsHtml += `<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="bukaModalJurnalDetail(${s.id})"><i class="bi bi-journal-bookmark-fill"></i><span>Jurnal Akuntansi</span></button>`;
                        }
                        if (CAN_CETAK_PB) {
                            sessionActionsHtml += `<a href="/produksi/pengeluaran-bahan/${s.id}/print" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs text-white border-0" style="background:linear-gradient(135deg,#0a2540,#1e40af);"><i class="bi bi-printer-fill"></i><span>Cetak Dokumen</span></a>`;
                        }
                        if (CAN_EDIT_PB) {
                            sessionActionsHtml += `<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="editPengeluaranFromDetail(${s.id}, ${_currentSpkId})"><i class="bi bi-pencil-square"></i><span>Edit Sesi</span></button>`;
                        }

                        sessHtml += `
                            <div class="spk-session-card">
                                <div class="spk-session-header-top">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="spk-session-badge">
                                            <i class="bi bi-layers-fill"></i>
                                            <span>SESI #${sesiNum}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold font-monospace text-primary fs-5">${s.nomor_pengeluaran}</div>
                                            <div class="d-flex align-items-center gap-1.5 spk-text-muted-contrast small mt-0.5">
                                                <i class="bi bi-calendar3 text-primary"></i>
                                                <span>Tanggal:</span>
                                                <b class="spk-text-contrast font-monospace">${tgl}</b>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                        ${sessionActionsHtml}
                                    </div>
                                </div>

                                <div class="spk-logistics-bar">
                                    <div class="spk-logistics-item">
                                        <div class="spk-logistics-icon" style="background:rgba(37,99,235,0.12);color:#2563eb;"><i class="bi bi-building"></i></div>
                                        <div class="spk-logistics-info">
                                            <span class="spk-logistics-label">Gudang Asal</span>
                                            <span class="spk-logistics-value" title="${gudangName}">${gudangName}</span>
                                        </div>
                                    </div>
                                    <div class="spk-logistics-item">
                                        <div class="spk-logistics-icon" style="background:rgba(124,58,237,0.12);color:#7c3aed;"><i class="bi bi-person-badge"></i></div>
                                        <div class="spk-logistics-info">
                                            <span class="spk-logistics-label">Petugas Penyerah</span>
                                            <span class="spk-logistics-value" title="${penyerahName}">${penyerahName}</span>
                                        </div>
                                    </div>
                                    <div class="spk-logistics-item">
                                        <div class="spk-logistics-icon" style="background:rgba(16,185,129,0.12);color:#059669;"><i class="bi bi-person-check-fill"></i></div>
                                        <div class="spk-logistics-info">
                                            <span class="spk-logistics-label">Diterima Oleh</span>
                                            <span class="spk-logistics-value" title="${penerimaName}">${penerimaName}</span>
                                        </div>
                                    </div>
                                </div>

                                ${s.catatan ? `
                                    <div class="spk-notes-box mb-3">
                                        <i class="bi bi-chat-left-quote-fill text-warning"></i>
                                        <div><b>Catatan:</b> <i>${s.catatan}</i></div>
                                    </div>
                                ` : ''}

                                <div class="spk-table-box">
                                    <div class="spk-table-wrapper" style="overflow-x:auto;">
                                        <table class="table align-middle spk-detail-table w-100" style="min-width:1050px;">
                                            <thead>
                                                <tr>
                                                    <th class="ps-3 text-center" style="width:55px;">No</th>
                                                    <th style="min-width:320px;">Item Bahan Mentah</th>
                                                    <th class="text-center" style="width:190px;">Total SPK</th>
                                                    <th class="text-center" style="width:200px;">Qty Dikeluarkan</th>
                                                    <th class="text-center pe-3" style="width:220px;">Sisa Selesai Sesi</th>
                                                </tr>
                                            </thead>
                                            <tbody>${itemsRows}</tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }

                // ─── Render pagination controls ───
                let paginationHtml = '';
                if (_historyPagination.last > 1) {
                    const p = _historyPagination;
                    const spkId = _currentSpkId;

                    let pages = '';
                    const maxBtn = 5;
                    let startPage = Math.max(1, p.current - Math.floor(maxBtn / 2));
                    let endPage   = Math.min(p.last, startPage + maxBtn - 1);
                    if (endPage - startPage < maxBtn - 1) startPage = Math.max(1, endPage - maxBtn + 1);

                    if (startPage > 1) pages += `<button class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" onclick="loadHistorySessions(${spkId},1)" style="font-size:12px;">1</button><span class="text-muted px-1">…</span>`;
                    for (let i = startPage; i <= endPage; i++) {
                        const isActive = i === p.current;
                        pages += `<button class="btn btn-sm ${isActive ? 'btn-primary' : 'btn-outline-secondary'} rounded-2 px-2 py-1" onclick="loadHistorySessions(${spkId},${i})" ${isActive ? 'disabled' : ''} style="font-size:12px;">${i}</button>`;
                    }
                    if (endPage < p.last) pages += `<span class="text-muted px-1">…</span><button class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" onclick="loadHistorySessions(${spkId},${p.last})" style="font-size:12px;">${p.last}</button>`;

                    paginationHtml = `
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 px-1">
                            <small class="spk-text-muted-contrast font-monospace">
                                Menampilkan <b class="spk-text-contrast">${p.from}–${p.to}</b> dari <b class="spk-text-contrast">${p.total}</b> sesi
                            </small>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                <button class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" ${p.current<=1?'disabled':''} onclick="loadHistorySessions(${spkId},${p.current-1})" style="font-size:12px;"><i class="bi bi-chevron-left"></i></button>
                                ${pages}
                                <button class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" ${p.current>=p.last?'disabled':''} onclick="loadHistorySessions(${spkId},${p.current+1})" style="font-size:12px;"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    `;
                } else if (_historyPagination.total > 0) {
                    paginationHtml = `
                        <div class="d-flex justify-content-end mt-2 px-1">
                            <small class="spk-text-muted-contrast font-monospace">
                                Total: <b class="spk-text-contrast">${_historyPagination.total}</b> sesi
                            </small>
                        </div>
                    `;
                }

                $('#container_riwayat_sessions').html(sessHtml + paginationHtml);
            },
            error: function(err) {
                $('#container_riwayat_sessions').html(`<div class="alert alert-danger m-2">Gagal memuat sesi: ${err.responseJSON?.message || 'Server error'}</div>`);
            }
        });
    }

    function bukaModalJurnalDetail(sessionId) {
        if (!CAN_SEE_JURNAL_PB) {
            Swal.fire('Akses Ditolak', 'Anda tidak memiliki hak akses untuk melihat Jurnal Akuntansi.', 'warning');
            return;
        }

        const s = _sessionCache[sessionId];
        if (!s || !s.jurnal) {
            Swal.fire('Info', 'Data jurnal akuntansi tidak ditemukan untuk sesi ini.', 'info');
            return;
        }

        const j = s.jurnal;
        const tgl = s.tanggal_pengeluaran ? new Date(s.tanggal_pengeluaran).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-';

        $('#mjurnal_title').text(`Jurnal: ${j.nomor_jurnal || 'Pengeluaran Bahan'}`);
        $('#mjurnal_subtitle').text(`Ref: ${s.nomor_pengeluaran} | Tanggal: ${tgl}`);
        $('#mjurnal_nomor').text(j.nomor_jurnal || '-');

        let jRows = '', totDeb = 0, totKred = 0;
        const details = j.details || [];

        details.forEach((jd, idx) => {
            const acc  = jd.account || {};
            const dVal = Number(jd.debit   || 0);
            const kVal = Number(jd.kredit  || 0);
            totDeb  += dVal;
            totKred += kVal;
            jRows += `
                <tr>
                    <td class="text-center font-monospace small spk-text-muted-contrast ps-3">${idx+1}</td>
                    <td>
                        <div class="fw-bold text-primary font-monospace">[${acc.code||'-'}]</div>
                        <div class="spk-text-contrast small">${acc.name||'-'}</div>
                    </td>
                    <td class="small spk-text-muted-contrast">${jd.keterangan||'-'}</td>
                    <td class="text-end font-monospace small ${dVal>0?'fw-bold spk-text-contrast fs-6':'spk-text-muted-contrast'}">
                        ${dVal>0 ? 'Rp '+dVal.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2}) : '-'}
                    </td>
                    <td class="text-end font-monospace small pe-3 ${kVal>0?'fw-bold spk-text-contrast fs-6':'spk-text-muted-contrast'}">
                        ${kVal>0 ? 'Rp '+kVal.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2}) : '-'}
                    </td>
                </tr>
            `;
        });

        if (details.length === 0) {
            jRows = '<tr><td colspan="5" class="text-center py-4 spk-text-muted-contrast">Tidak ada rincian baris jurnal.</td></tr>';
        }

        $('#tbody_modal_jurnal_rows').html(jRows);
        const fmtDeb  = 'Rp '+totDeb.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2});
        const fmtKred = 'Rp '+totKred.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2});
        $('#mjurnal_total_debit').text(fmtDeb);
        $('#mjurnal_total_kredit').text(fmtKred);
        $('#mjurnal_foot_debit').text(fmtDeb);
        $('#mjurnal_foot_kredit').text(fmtKred);

        $('#modalDetailJurnalPengeluaran').modal('show');
    }

    // Preserve scroll saat modal jurnal ditutup di atas modal detail
    $(document).on('hidden.bs.modal', '#modalDetailJurnalPengeluaran', function () {
        if ($('#modalDetailPengeluaran').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    function editPengeluaranFromDetail(id, spkId) {
        if (!CAN_EDIT_PB) {
            Swal.fire('Akses Ditolak', 'Anda tidak memiliki hak akses untuk mengedit sesi pengeluaran bahan.', 'warning');
            return;
        }
        if (typeof openModalEditPengeluaran === 'function') {
            openModalEditPengeluaran(id, spkId);
        }
    }

    function deletePengeluaranFromDetail(id, spkId) {
        Swal.fire({
            title: 'Batalkan Sesi Pengeluaran Ini?',
            text: "Stok barang di gudang akan dikembalikan (revert) dan sisa kebutuhan SPK akan dihitung ulang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/pengeluaran-bahan/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ icon:'success', title:'Dibatalkan!', text:res.message, timer:2000, showConfirmButton:false });
                        reloadTablePengeluaran();
                        // Refresh modal dari awal
                        showSpkHistoryModal(spkId);
                    },
                    error: function(err) {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
