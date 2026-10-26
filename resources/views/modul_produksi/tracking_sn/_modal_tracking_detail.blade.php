<!-- MODAL DETAIL TIMELINE PELACAKAN SERIAL NUMBER -->
<div class="modal fade" id="modalTrackingDetail" tabindex="-1" aria-labelledby="modalTrackingDetailLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-2xl border-0 rounded-4 overflow-hidden" style="background: var(--sn-card-bg, #ffffff); color: var(--sn-text-main, #0f172a);">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.12) 0%, rgba(59, 130, 246, 0.05) 100%); border-color: var(--sn-card-border, #e2e8f0) !important;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                        <i class="bi bi-upc-scan fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-extrabold mb-0" id="modalTrackingDetailLabel" style="color: var(--sn-text-main, #0f172a);">
                            Jejak Riwayat Unit (Life-Cycle Timeline)
                        </h6>
                        <div class="d-flex align-items-center gap-2 mt-0.5">
                            <span class="badge bg-primary text-white font-monospace px-2.5 py-0.5 fw-bold" id="modal_sn_title" style="font-size: 11.5px; letter-spacing: 0.5px;">-</span>
                            <span class="badge rounded-pill fw-bold" id="modal_sn_status_badge">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-4 custom-scrollbar">
                
                {{-- LOADING SPINNER --}}
                <div id="modal_tracking_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat riwayat...</span>
                    </div>
                    <div class="item-sub-text fw-semibold mt-2 small">Melacak rekam jejak serial number...</div>
                </div>

                {{-- CONTENT WRAPPER --}}
                <div id="modal_tracking_content" style="display: none;">
                    
                    {{-- PRODUCT SUMMARY CARD (SN, LOD/LOT, KODE BARANG, WARNA, QR CODE) --}}
                    <div class="card border rounded-3 mb-4 p-3 shadow-xs" style="background: var(--sn-input-bg, #f8fafc); border-color: var(--sn-card-border, #e2e8f0) !important;">
                        <div class="row g-3 align-items-center">
                            
                            {{-- Info Produk & Warna --}}
                            <div class="col-md-5">
                                <small class="item-sub-text text-uppercase fw-bold fs-8 d-block mb-1">
                                    <i class="bi bi-box-seam text-primary me-1"></i> Produk Barang Jadi
                                </small>
                                <div class="fw-bold fs-6 item-title-text text-truncate mb-1" id="modal_product_name">-</div>
                                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-1">
                                    <span class="badge product-code-badge font-monospace" id="modal_product_code">-</span>
                                    <span class="item-sub-text fs-8 fw-semibold" id="modal_product_kategori">-</span>
                                    <span class="badge rounded-pill bg-light text-dark border py-0.5 px-2 fs-8" id="modal_product_warna_badge" style="display: none;">
                                        <span id="modal_product_warna_dot" style="display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:4px;"></span>
                                        <span id="modal_product_warna_text">-</span>
                                    </span>
                                </div>
                            </div>

                            {{-- Nomor SN & Batch --}}
                            <div class="col-md-4 border-start-md ps-md-3">
                                <div class="mb-1.5">
                                    <small class="item-sub-text fs-8 d-block mb-0.5">🏷️ Nomor SN:</small>
                                    <span class="badge sn-code-badge font-monospace fs-8" id="modal_sn_box">-</span>
                                </div>
                                <div>
                                    <small class="item-sub-text fs-8 d-block mb-0.5">🏷️ No. Batch / Lot (LOD):</small>
                                    <span class="badge batch-code-badge font-monospace fs-8" id="modal_batch_no">-</span>
                                </div>
                            </div>

                            {{-- QR Code Thumbnail & Action --}}
                            <div class="col-md-3 border-start-md ps-md-3 text-md-center">
                                <div class="d-inline-flex flex-column align-items-center">
                                    <div class="p-1.5 bg-white border rounded-3 shadow-xs cursor-pointer mb-1" onclick="openQrLabelModalFromCurrentDetail()" title="Klik untuk Cetak Label Stiker QR Code">
                                        <img id="modal_qr_thumb_img" src="" alt="QR Code" style="width: 64px; height: 64px; object-fit: contain;">
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 fw-bold fs-8 d-inline-flex align-items-center gap-1 shadow-xs" onclick="openQrLabelModalFromCurrentDetail()">
                                        <i class="bi bi-qr-code"></i>
                                        <span>Label QR</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- TIMELINE STEPPER --}}
                    <div class="timeline-container px-2" id="modal_timeline_wrapper">
                        <!-- Dynamic Stepper Items injected by JS -->
                    </div>

                </div>

            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer border-top px-4 py-2.5 d-flex justify-content-between" style="border-color: var(--sn-card-border, #e2e8f0) !important;">
                <small class="item-sub-text fs-8">
                    <i class="bi bi-shield-check text-success me-1"></i> Data riwayat terekam otomatis oleh sistem ERP
                </small>
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill fw-semibold" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    /* PRODUCT CODE BADGE */
    .product-code-badge {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1.5px solid #93c5fd !important;
        font-weight: 800 !important;
        letter-spacing: 0.4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.78rem !important;
        padding: 3.5px 8px !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px;
    }
    [data-bs-theme="dark"] .product-code-badge,
    .dark-mode .product-code-badge {
        background-color: rgba(30, 58, 138, 0.4) !important;
        color: #93c5fd !important;
        border-color: rgba(59, 130, 246, 0.5) !important;
    }

    /* TIMELINE STYLING */
    .timeline-container {
        position: relative;
        padding-left: 28px;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        top: 14px;
        bottom: 14px;
        left: 11px;
        width: 2.5px;
        background: linear-gradient(180deg, #3b82f6 0%, #10b981 100%);
        border-radius: 2px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .timeline-item:last-child {
        margin-bottom: 8px;
    }
    .timeline-node {
        position: absolute;
        left: -28px;
        top: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--sn-card-bg, #ffffff);
        border: 2.5px solid #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        z-index: 2;
    }
    .timeline-node.node-success {
        border-color: #10b981;
        color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }
    .timeline-card {
        background: var(--sn-card-bg, #ffffff);
        border: 1px solid var(--sn-card-border, #e2e8f0);
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    .timeline-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
    }
</style>
