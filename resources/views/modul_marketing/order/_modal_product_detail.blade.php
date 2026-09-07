<!-- Modal Detail Produk E-Commerce (Cinematic Tokopedia Product Stage with Multi-Image Slider) -->
<div class="modal modal-blur fade" id="modalProductDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content cinematic-modal-content border-0 shadow-2xl overflow-hidden">
            
            <!-- Modal Header with Ambient Glow & Badges -->
            <div class="modal-header border-0 pb-0 pt-3 px-4 d-flex justify-content-between align-items-center position-relative z-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge cinematic-type-badge px-3 py-1.5 rounded-pill fw-bold" id="modal_prod_type_badge">
                        <i class="bi bi-box-seam me-1"></i> Produk Fisik
                    </span>
                    <span class="badge cinematic-stock-badge px-3 py-1.5 rounded-pill fw-bold" id="modal_prod_stock_badge">
                        <i class="bi bi-check-circle-fill me-1"></i> Stok Tersedia
                    </span>
                </div>
                <button type="button" class="btn-close cinematic-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-3">
                <div class="row g-4">
                    
                    <!-- LEFT COLUMN: CINEMATIC IMAGE STAGE & THUMBNAILS -->
                    <div class="col-12 col-lg-5">
                        <div class="position-sticky" style="top: 15px;">
                            
                            <!-- Main Stage Container (with ambient radial glow) -->
                            <div class="cinematic-stage position-relative rounded-4 overflow-hidden border shadow-sm">
                                
                                <!-- Floating Glow Aura Behind Product -->
                                <div class="cinematic-glow-aura"></div>

                                <!-- Main Image (with drop shadow & zoom transition) -->
                                <img src="{{ asset('images/no-image.png') }}" class="cinematic-main-img" id="modal_prod_main_img" alt="Product Image">
                                
                                <!-- Slider Floating Glass Buttons -->
                                <button type="button" class="btn cinematic-nav-btn start-0 ms-3 d-none" id="btn_slider_prev" aria-label="Previous image">
                                    <i class="bi bi-chevron-left fs-6"></i>
                                </button>
                                <button type="button" class="btn cinematic-nav-btn end-0 me-3 d-none" id="btn_slider_next" aria-label="Next image">
                                    <i class="bi bi-chevron-right fs-6"></i>
                                </button>

                                <!-- Cyber Counter Badge -->
                                <div class="cinematic-counter-badge d-none" id="modal_prod_img_counter">
                                    <i class="bi bi-camera me-1"></i> <span id="modal_counter_text">1 / 1</span>
                                </div>

                            </div>

                            <!-- Thumbnail Strip Horizontal (Interactive with active neon ring) -->
                            <div class="d-flex gap-2.5 mt-3 overflow-x-auto pb-2 px-1 cinematic-thumb-scroll" id="modal_prod_thumb_strip">
                                <!-- Dynamic thumbnails -->
                            </div>

                        </div>
                    </div>

                    <!-- RIGHT COLUMN: PRODUCT SPECIFICATIONS & 3-TIER PRICING -->
                    <div class="col-12 col-lg-7">
                        <div class="d-flex flex-column h-100 justify-content-between">
                            
                            <div>
                                <!-- Category & Brand Pills -->
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="cinematic-meta-pill" id="modal_prod_category">-</span>
                                    <span class="cinematic-meta-pill text-primary" id="modal_prod_brand">-</span>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2.5 py-1 rounded-pill" id="modal_prod_sku">-</span>
                                </div>

                                <!-- Product Main Title -->
                                <h2 class="fw-black mb-1 cinematic-title" id="modal_prod_title">-</h2>
                                <div class="text-muted small mb-3 fst-italic" id="modal_prod_alias"></div>

                                <!-- 3-TIER NEON PRICING GRID -->
                                <div class="cinematic-tier-container p-3.5 rounded-4 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                                        <div class="cinematic-section-label">
                                            <i class="bi bi-tags-fill me-1 text-success"></i> TINGKAT HARGA JUAL (3-TIER PRICING)
                                        </div>
                                        <span class="badge bg-dark-subtle text-muted small font-monospace" style="font-size:0.7rem;">Auto-Sync Tier</span>
                                    </div>
                                    
                                    <div class="row g-2.5">
                                        <div class="col-4">
                                            <div class="cinematic-tier-card tier-retail text-center p-2.5 rounded-3" id="modal_tier_retail">
                                                <div class="tier-label text-success"><i class="bi bi-tag-fill me-1"></i>Retail</div>
                                                <div class="tier-price text-success font-monospace" id="modal_prod_price_retail">Rp 0</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="cinematic-tier-card tier-grosir text-center p-2.5 rounded-3" id="modal_tier_grosir">
                                                <div class="tier-label text-primary"><i class="bi bi-boxes me-1"></i>Grosir</div>
                                                <div class="tier-price text-primary font-monospace" id="modal_prod_price_grosir">Rp 0</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="cinematic-tier-card tier-tender text-center p-2.5 rounded-3" id="modal_tier_tender">
                                                <div class="tier-label text-warning"><i class="bi bi-briefcase-fill me-1"></i>Tender</div>
                                                <div class="tier-price text-warning font-monospace" id="modal_prod_price_tender">Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHYSICAL SPECIFICATIONS CARDS -->
                                <div class="row g-2.5 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-2.5 rounded-3">
                                            <div class="spec-label">UKURAN</div>
                                            <div class="spec-val font-monospace" id="modal_prod_ukuran">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-2.5 rounded-3">
                                            <div class="spec-label">WARNA</div>
                                            <div class="d-flex align-items-center justify-content-center gap-1.5 mt-0.5">
                                                <span class="cinematic-color-dot rounded-circle shadow-sm" id="modal_prod_warna_dot"></span>
                                                <span class="spec-val" id="modal_prod_warna_nama">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-2.5 rounded-3">
                                            <div class="spec-label">DIMENSI (PxLxT)</div>
                                            <div class="spec-val font-monospace" id="modal_prod_dimensi">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-2.5 rounded-3">
                                            <div class="spec-label">BERAT</div>
                                            <div class="spec-val font-monospace" id="modal_prod_berat">-</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BUNDLE COMPONENTS BOX (IF APPLICABLE) -->
                                <div class="mb-3 d-none" id="modal_prod_bundle_wrap">
                                    <div class="cinematic-section-label mb-2 text-primary">
                                        <i class="bi bi-collection-fill me-1"></i> KOMPONEN ISI PAKET BUNDLE
                                    </div>
                                    <div class="cinematic-bundle-box p-2.5 rounded-3" id="modal_prod_bundle_items">
                                        <!-- Dynamic bundle items -->
                                    </div>
                                </div>

                                <!-- DESCRIPTION & PRODUCT SPECS -->
                                <div class="mb-2">
                                    <div class="cinematic-section-label mb-1.5">
                                        <i class="bi bi-info-circle-fill me-1 text-muted"></i> DESKRIPSI & SPESIFIKASI
                                    </div>
                                    <div class="cinematic-desc-box p-3 rounded-3 small" id="modal_prod_spesifikasi">
                                        -
                                    </div>
                                </div>

                            </div>

                            <!-- BOTTOM CINEMATIC ACTION BAR -->
                            <div class="pt-3.5 border-top mt-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    
                                    <!-- Stepper Quantity (Futuristic Pill) -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <span class="cinematic-qty-label">Qty:</span>
                                        <div class="cinematic-stepper shadow-sm">
                                            <button type="button" class="cinematic-stepper-btn" id="modal_btn_qty_dec">-</button>
                                            <input type="text" class="cinematic-stepper-input font-monospace fw-bold" id="modal_input_qty" value="1">
                                            <button type="button" class="cinematic-stepper-btn" id="modal_btn_qty_inc">+</button>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace" id="modal_prod_satuan_label">Pcs</span>
                                    </div>

                                    <!-- Glowing Add to Cart CTA -->
                                    <button type="button" class="btn cinematic-btn-cta fw-bold d-flex align-items-center gap-2" id="modal_btn_add_cart">
                                        <i class="bi bi-cart-plus-fill fs-5"></i>
                                        <span>+ Masukkan ke Keranjang</span>
                                    </button>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ==========================================================
       CINEMATIC MODAL & PRODUCT STAGE (DARK & LIGHT MODE)
       ========================================================== */
    
    /* MODAL CONTAINER & BACKDROP */
    .cinematic-modal-content {
        border-radius: 24px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    [data-bs-theme="dark"] .cinematic-modal-content {
        background: rgba(15, 23, 42, 0.96) !important;
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7), 0 0 50px rgba(16, 185, 129, 0.12) !important;
        color: #f1f5f9;
    }

    [data-bs-theme="light"] .cinematic-modal-content {
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 30px rgba(16, 185, 129, 0.08) !important;
        color: #1e293b;
    }

    /* CINEMATIC IMAGE STAGE */
    .cinematic-stage {
        padding-top: 100%;
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .cinematic-stage {
        background: radial-gradient(circle at center, rgba(16, 185, 129, 0.12) 0%, rgba(30, 41, 59, 0.6) 65%, rgba(15, 23, 42, 0.95) 100%) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    [data-bs-theme="light"] .cinematic-stage {
        background: radial-gradient(circle at center, rgba(16, 185, 129, 0.06) 0%, #f8fafc 85%) !important;
        border-color: rgba(0, 0, 0, 0.06) !important;
    }

    .cinematic-glow-aura {
        position: absolute;
        top: 20%;
        left: 20%;
        right: 20%;
        bottom: 20%;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
        filter: blur(30px);
        pointer-events: none;
        z-index: 1;
    }

    .cinematic-main-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 18px;
        z-index: 2;
        filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.25));
        transition: transform 0.3s ease;
    }
    .cinematic-stage:hover .cinematic-main-img {
        transform: scale(1.03);
    }

    /* SLIDER BUTTONS */
    .cinematic-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 3;
        transition: all 0.2s ease;
        backdrop-filter: blur(10px);
    }

    [data-bs-theme="dark"] .cinematic-nav-btn {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #f8fafc;
    }
    [data-bs-theme="dark"] .cinematic-nav-btn:hover {
        background: #10b981;
        border-color: #10b981;
        color: #ffffff;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
        transform: translateY(-50%) scale(1.1);
    }

    [data-bs-theme="light"] .cinematic-nav-btn {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(0, 0, 0, 0.1);
        color: #1e293b;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    [data-bs-theme="light"] .cinematic-nav-btn:hover {
        background: #03ac0e;
        border-color: #03ac0e;
        color: #ffffff;
        transform: translateY(-50%) scale(1.1);
    }

    /* COUNTER BADGE */
    .cinematic-counter-badge {
        position: absolute;
        bottom: 14px;
        right: 14px;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 0.78rem;
        font-family: var(--bs-font-monospace);
        z-index: 3;
        backdrop-filter: blur(10px);
    }
    [data-bs-theme="dark"] .cinematic-counter-badge {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #94a3b8;
    }
    [data-bs-theme="light"] .cinematic-counter-badge {
        background: rgba(0, 0, 0, 0.7);
        color: #ffffff;
    }

    /* THUMBNAIL STRIP */
    .cinematic-thumb-scroll {
        scrollbar-width: thin;
    }
    .cinematic-thumb-item {
        width: 66px;
        height: 66px;
        border-radius: 12px;
        object-fit: cover;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        flex-shrink: 0;
        padding: 4px;
    }
    [data-bs-theme="dark"] .cinematic-thumb-item {
        background: #1e293b;
        border: 2px solid rgba(255, 255, 255, 0.08);
    }
    [data-bs-theme="light"] .cinematic-thumb-item {
        background: #f1f5f9;
        border: 2px solid rgba(0, 0, 0, 0.06);
    }
    .cinematic-thumb-item:hover {
        transform: translateY(-3px) scale(1.05);
        border-color: #10b981;
    }
    .cinematic-thumb-item.active {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35), 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    /* TYPOGRAPHY & HEADINGS */
    .cinematic-title {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }
    .cinematic-meta-pill {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .cinematic-section-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tkpd-text-sub);
    }

    /* 3-TIER PRICING CARDS */
    .cinematic-tier-container {
        transition: all 0.2s ease;
    }
    [data-bs-theme="dark"] .cinematic-tier-container {
        background: rgba(30, 41, 59, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.07);
    }
    [data-bs-theme="light"] .cinematic-tier-container {
        background: rgba(248, 250, 252, 0.85);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .cinematic-tier-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px solid transparent;
        position: relative;
    }
    .cinematic-tier-card .tier-label {
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .cinematic-tier-card .tier-price {
        font-size: 1.02rem;
        font-weight: 800;
    }

    /* Retail Glow */
    .cinematic-tier-card.tier-retail {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.03) 100%);
        border-color: rgba(16, 185, 129, 0.3);
    }
    /* Grosir Glow */
    .cinematic-tier-card.tier-grosir {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.03) 100%);
        border-color: rgba(59, 130, 246, 0.3);
    }
    /* Tender Glow */
    .cinematic-tier-card.tier-tender {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.03) 100%);
        border-color: rgba(245, 158, 11, 0.3);
    }

    .cinematic-tier-card.active-selected-tier {
        box-shadow: 0 0 0 3px #10b981, 0 8px 20px rgba(16, 185, 129, 0.3) !important;
        border-color: #10b981 !important;
        transform: translateY(-2px);
    }

    /* SPEC GRID CARDS */
    .cinematic-spec-card {
        transition: all 0.2s ease;
    }
    [data-bs-theme="dark"] .cinematic-spec-card {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }
    [data-bs-theme="light"] .cinematic-spec-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .cinematic-spec-card .spec-label {
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--tkpd-text-sub);
    }
    .cinematic-spec-card .spec-val {
        font-size: 0.86rem;
        font-weight: 700;
    }
    .cinematic-color-dot {
        width: 14px;
        height: 14px;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        display: inline-block;
    }

    /* BUNDLE & DESC BOX */
    [data-bs-theme="dark"] .cinematic-bundle-box,
    [data-bs-theme="dark"] .cinematic-desc-box {
        background: rgba(30, 41, 59, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: #94a3b8;
    }
    [data-bs-theme="light"] .cinematic-bundle-box,
    [data-bs-theme="light"] .cinematic-desc-box {
        background: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.05);
        color: #475569;
    }
    .cinematic-desc-box {
        white-space: pre-wrap;
        max-height: 110px;
        overflow-y: auto;
        line-height: 1.5;
    }

    /* STEPPER */
    .cinematic-stepper {
        display: inline-flex;
        border-radius: 30px;
        overflow: hidden;
        align-items: center;
        border: 1.5px solid var(--tkpd-card-border);
    }
    [data-bs-theme="dark"] .cinematic-stepper {
        background: #0f172a;
    }
    [data-bs-theme="light"] .cinematic-stepper {
        background: #ffffff;
    }
    .cinematic-stepper-btn {
        width: 38px;
        height: 38px;
        background: transparent;
        border: none;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--tkpd-green);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .cinematic-stepper-btn:hover {
        background: var(--tkpd-green-light);
    }
    .cinematic-stepper-input {
        width: 52px;
        height: 38px;
        background: transparent;
        border: none;
        text-align: center;
        font-size: 0.95rem;
        color: var(--tkpd-text-main);
    }
    .cinematic-stepper-input:focus {
        outline: none;
    }

    /* CTA BUTTON */
    .cinematic-btn-cta {
        padding: 10px 24px;
        border-radius: 30px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: #ffffff;
        box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.45);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .cinematic-btn-cta:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 25px -4px rgba(16, 185, 129, 0.6);
        color: #ffffff;
    }
    .cinematic-btn-cta:active {
        transform: translateY(1px);
    }
</style>
