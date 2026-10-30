<!-- Modal Detail Produk E-Commerce (Cinematic Tokopedia Product Stage with Multi-Image Slider) -->
<div class="modal modal-blur fade" id="modalProductDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content cinematic-modal-content border-0 shadow-2xl overflow-hidden">
            
            <!-- Modal Header with Ambient Glow & Badges -->
            <div class="modal-header border-0 pb-2 pt-3 px-3 px-sm-4 d-flex justify-content-between align-items-center position-relative z-3">
                <div class="d-flex flex-wrap align-items-center gap-2 pe-2 min-w-0 flex-grow-1">
                    <span class="badge cinematic-type-badge rounded-pill fw-bold" id="modal_prod_type_badge">
                        <i class="bi bi-box-seam me-1"></i> Produk Fisik
                    </span>
                    <span class="badge cinematic-stock-badge rounded-pill fw-bold" id="modal_prod_stock_badge">
                        <i class="bi bi-check-circle-fill me-1"></i> Stok Tersedia
                    </span>
                </div>
                <button type="button" class="btn cinematic-btn-close rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body p-4 pt-2">
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
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2.5">
                                    <span class="cinematic-meta-pill" id="modal_prod_category">-</span>
                                    <span class="cinematic-meta-pill text-primary" id="modal_prod_brand">-</span>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace px-2.5 py-1 rounded-pill" id="modal_prod_sku">-</span>
                                </div>

                                <!-- Product Main Title -->
                                <h2 class="fw-black mb-1 cinematic-title" id="modal_prod_title">-</h2>
                                <div class="text-muted small mb-3.5 fst-italic" id="modal_prod_alias"></div>

                                <!-- 3-TIER NEON PRICING (VERTICAL STACK & SPACED) -->
                                <div class="cinematic-tier-container p-3 p-sm-3.5 rounded-4 mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="cinematic-section-label">
                                            <i class="bi bi-tags-fill me-1.5 text-success"></i> TINGKAT HARGA JUAL (3-TIER PRICING)
                                        </div>
                                        <span class="badge bg-dark-subtle text-muted small font-monospace" style="font-size:0.72rem;">Auto-Sync Tier</span>
                                    </div>
                                    
                                    <!-- Susunan Card Harga Menurun Vertikal dengan Jarak Tegas -->
                                    <div class="cinematic-tier-list d-flex flex-column" style="gap: 12px !important;">
                                        <!-- Tier Retail -->
                                        <div class="cinematic-tier-card tier-retail p-3 rounded-3" id="modal_tier_retail">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="tier-icon-wrap rounded-2 d-inline-flex align-items-center justify-content-center text-success bg-success bg-opacity-10" style="width: 34px; height: 34px; flex-shrink: 0;">
                                                        <i class="bi bi-tag-fill fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="tier-label text-success fw-bold mb-0" style="font-size: 0.88rem;">Harga Retail</div>
                                                        <div class="text-body-secondary small" style="font-size: 0.72rem;">Eceran &amp; Umum</div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="tier-price text-success font-monospace fw-bold" id="modal_prod_price_retail">Rp 0,00</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tier Grosir -->
                                        <div class="cinematic-tier-card tier-grosir p-3 rounded-3" id="modal_tier_grosir">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="tier-icon-wrap rounded-2 d-inline-flex align-items-center justify-content-center text-primary bg-primary bg-opacity-10" style="width: 34px; height: 34px; flex-shrink: 0;">
                                                        <i class="bi bi-boxes fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="tier-label text-primary fw-bold mb-0" style="font-size: 0.88rem;">Harga Grosir</div>
                                                        <div class="text-body-secondary small" style="font-size: 0.72rem;">Jumlah Partai / Toko</div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="tier-price text-primary font-monospace fw-bold" id="modal_prod_price_grosir">Rp 0,00</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tier Tender -->
                                        <div class="cinematic-tier-card tier-tender p-3 rounded-3" id="modal_tier_tender">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="tier-icon-wrap rounded-2 d-inline-flex align-items-center justify-content-center text-warning bg-warning bg-opacity-10" style="width: 34px; height: 34px; flex-shrink: 0;">
                                                        <i class="bi bi-briefcase-fill fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="tier-label text-warning fw-bold mb-0" style="font-size: 0.88rem;">Harga Tender</div>
                                                        <div class="text-body-secondary small" style="font-size: 0.72rem;">Proyek Khusus &amp; Institusi</div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="tier-price text-warning font-monospace fw-bold" id="modal_prod_price_tender">Rp 0,00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHYSICAL SPECIFICATIONS CARDS -->
                                <div class="row g-3 mb-4">
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-3 rounded-3">
                                            <div class="spec-label">UKURAN</div>
                                            <div class="spec-val font-monospace" id="modal_prod_ukuran">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-3 rounded-3">
                                            <div class="spec-label">WARNA</div>
                                            <div class="d-flex align-items-center justify-content-center gap-1.5 mt-0.5">
                                                <span class="cinematic-color-dot rounded-circle shadow-sm" id="modal_prod_warna_dot"></span>
                                                <span class="spec-val" id="modal_prod_warna_nama">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-3 rounded-3">
                                            <div class="spec-label">DIMENSI (PxLxT)</div>
                                            <div class="spec-val font-monospace" id="modal_prod_dimensi">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="cinematic-spec-card text-center p-3 rounded-3">
                                            <div class="spec-label">BERAT</div>
                                            <div class="spec-val font-monospace" id="modal_prod_berat">-</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BUNDLE COMPONENTS BOX (IF APPLICABLE) -->
                                <div class="mb-4 d-none" id="modal_prod_bundle_wrap">
                                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                                        <div class="cinematic-section-label text-primary d-flex align-items-center gap-1.5">
                                            <i class="bi bi-collection-fill fs-6"></i>
                                            <span>KOMPONEN ISI PAKET BUNDLE</span>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary font-monospace rounded-pill px-2.5 py-1" style="font-size:0.7rem;">Paket Rakitan</span>
                                    </div>
                                    <!-- Dynamic Individual Bundle Cards Container -->
                                    <div class="d-flex flex-column" style="gap: 12px !important;" id="modal_prod_bundle_items">
                                        <!-- Dynamic bundle cards rendered here by JS -->
                                    </div>
                                </div>

                                <!-- DESCRIPTION & PRODUCT SPECS -->
                                <div class="mb-3">
                                    <div class="cinematic-section-label mb-2 text-secondary d-flex align-items-center gap-1.5">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>DESKRIPSI &amp; SPESIFIKASI</span>
                                    </div>
                                    <div class="cinematic-desc-box p-3.5 rounded-3 small" id="modal_prod_spesifikasi">
                                        -
                                    </div>
                                </div>

                            </div>

                            <!-- BOTTOM CINEMATIC ACTION BAR -->
                            <div class="pt-3.5 border-top mt-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    
                                    <!-- Stepper Quantity (Futuristic Pill) -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <span class="cinematic-qty-label fw-bold">Qty:</span>
                                        <div class="cinematic-stepper shadow-sm">
                                            <button type="button" class="cinematic-stepper-btn" id="modal_btn_qty_dec">-</button>
                                            <input type="text" class="cinematic-stepper-input font-monospace fw-bold" id="modal_input_qty" value="1">
                                            <button type="button" class="cinematic-stepper-btn" id="modal_btn_qty_inc">+</button>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace px-2.5 py-1.5 rounded-pill" id="modal_prod_satuan_label">Pcs</span>
                                    </div>

                                    <!-- Glowing Add to Cart CTA -->
                                    <button type="button" class="btn cinematic-btn-cta fw-bold d-flex align-items-center gap-2" id="modal_btn_add_cart">
                                        <i class="bi bi-cart-plus-fill fs-5"></i>
                                        <span>+ Tambah ke Keranjang</span>
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
       CINEMATIC MODAL & PRODUCT STAGE (MODERN & SPACED STYLING)
       ========================================================== */
    
    /* MODAL CONTAINER & BACKDROP */
    .cinematic-modal-content {
        border-radius: 24px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    [data-bs-theme="dark"] .cinematic-modal-content {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7), 0 0 50px rgba(16, 185, 129, 0.12) !important;
        color: #f1f5f9;
    }

    [data-bs-theme="light"] .cinematic-modal-content {
        background: #ffffff !important;
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 30px rgba(16, 185, 129, 0.08) !important;
        color: #0f172a;
    }

    /* HEADER BADGES */
    .cinematic-type-badge {
        font-size: 0.78rem;
        padding: 6px 14px;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe !important;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
    }
    [data-bs-theme="dark"] .cinematic-type-badge {
        background: rgba(37, 99, 235, 0.2) !important;
        color: #60a5fa !important;
        border: 1px solid rgba(96, 165, 250, 0.35) !important;
    }

    .cinematic-stock-badge {
        font-size: 0.78rem;
        padding: 6px 14px;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
    }

    .cinematic-btn-close {
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
    .cinematic-btn-close:hover {
        background: rgba(239, 68, 68, 0.12) !important;
        color: #ef4444 !important;
        border-color: rgba(239, 68, 68, 0.25) !important;
        transform: rotate(90deg) scale(1.05);
    }
    [data-bs-theme="dark"] .cinematic-btn-close {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .cinematic-btn-close:hover {
        background: rgba(239, 68, 68, 0.2) !important;
        color: #f87171 !important;
        border-color: rgba(239, 68, 68, 0.4) !important;
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
        filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.15));
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
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }
    .cinematic-meta-pill {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 3px 8px;
        background: rgba(0,0,0,0.04);
        border-radius: 6px;
    }
    [data-bs-theme="dark"] .cinematic-meta-pill {
        background: rgba(255,255,255,0.08);
    }
    .cinematic-section-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    [data-bs-theme="dark"] .cinematic-section-label {
        color: #94a3b8;
    }

    /* 3-TIER PRICING CARDS */
    .cinematic-tier-container {
        transition: all 0.2s ease;
    }
    [data-bs-theme="dark"] .cinematic-tier-container {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    [data-bs-theme="light"] .cinematic-tier-container {
        background: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .cinematic-tier-list {
        display: flex;
        flex-direction: column;
        gap: 14px !important;
    }

    .cinematic-tier-card {
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px solid transparent;
        border-radius: 14px;
        position: relative;
    }
    .cinematic-tier-card:hover {
        transform: translateY(-1.5px);
    }
    .cinematic-tier-card .tier-label {
        letter-spacing: 0.02em;
    }
    .cinematic-tier-card .tier-price {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .cinematic-type-badge, .cinematic-stock-badge {
            font-size: 0.72rem !important;
            padding: 5px 10px !important;
        }
        .cinematic-btn-close {
            width: 30px !important;
            height: 30px !important;
            font-size: 0.78rem !important;
            margin-left: 6px !important;
        }
        .cinematic-tier-container {
            padding: 14px !important;
            margin-bottom: 22px !important;
        }
        .cinematic-tier-list {
            gap: 12px !important;
        }
        .cinematic-tier-card {
            padding: 12px 14px !important;
            border-radius: 12px !important;
        }
        .cinematic-tier-card .tier-price {
            font-size: 1.05rem !important;
            letter-spacing: -0.2px !important;
        }
        .cinematic-spec-card {
            padding: 12px 10px !important;
            border-radius: 12px !important;
        }
        .cinematic-spec-card .spec-label {
            font-size: 0.65rem !important;
            margin-bottom: 4px !important;
        }
        .cinematic-spec-card .spec-val {
            font-size: 0.85rem !important;
        }
        .cinematic-title {
            font-size: 1.25rem !important;
        }
        .cinematic-stage {
            padding-top: 75% !important;
            max-height: 250px !important;
        }
        .cinematic-component-card {
            padding: 12px 14px !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px !important;
            border-radius: 12px !important;
        }
        .cinematic-component-card .comp-stock-badge {
            font-size: 0.72rem !important;
            padding: 4px 10px !important;
        }
    }

    /* Retail Glow */
    .cinematic-tier-card.tier-retail {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.03) 100%);
        border-color: rgba(16, 185, 129, 0.35);
    }
    /* Grosir Glow */
    .cinematic-tier-card.tier-grosir {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0.03) 100%);
        border-color: rgba(59, 130, 246, 0.35);
    }
    /* Tender Glow */
    .cinematic-tier-card.tier-tender {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.03) 100%);
        border-color: rgba(245, 158, 11, 0.35);
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
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    [data-bs-theme="light"] .cinematic-spec-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .cinematic-spec-card .spec-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }
    [data-bs-theme="dark"] .cinematic-spec-card .spec-label {
        color: #94a3b8;
    }
    .cinematic-spec-card .spec-val {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }
    [data-bs-theme="dark"] .cinematic-spec-card .spec-val {
        color: #f1f5f9;
    }
    .cinematic-color-dot {
        width: 14px;
        height: 14px;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        display: inline-block;
    }

    /* INDIVIDUAL BUNDLE COMPONENT CARDS */
    .cinematic-component-card {
        padding: 12px 16px;
        border-radius: 12px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    [data-bs-theme="dark"] .cinematic-component-card {
        background: rgba(30, 41, 59, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    [data-bs-theme="dark"] .cinematic-component-card:hover {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(56, 189, 248, 0.3);
    }
    [data-bs-theme="light"] .cinematic-component-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.025);
    }
    [data-bs-theme="light"] .cinematic-component-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }

    .cinematic-component-card .comp-thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 2px;
    }
    [data-bs-theme="dark"] .cinematic-component-card .comp-thumb {
        border-color: rgba(255, 255, 255, 0.1);
        background: #1e293b;
    }

    .cinematic-component-card .comp-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    [data-bs-theme="dark"] .cinematic-component-card .comp-title {
        color: #f8fafc;
    }

    .cinematic-component-card .comp-meta {
        font-size: 0.75rem;
        color: #64748b;
        font-family: var(--bs-font-monospace);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    [data-bs-theme="dark"] .cinematic-component-card .comp-meta {
        color: #94a3b8;
    }

    .cinematic-component-card .comp-stock-badge {
        font-size: 0.75rem;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-family: var(--bs-font-monospace);
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    /* DESC BOX */
    [data-bs-theme="dark"] .cinematic-desc-box {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #94a3b8;
    }
    [data-bs-theme="light"] .cinematic-desc-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    .cinematic-desc-box {
        white-space: pre-wrap;
        max-height: 120px;
        overflow-y: auto;
        line-height: 1.6;
    }

    /* STEPPER */
    .cinematic-stepper {
        display: inline-flex;
        border-radius: 30px;
        overflow: hidden;
        align-items: center;
        border: 1.5px solid #cbd5e1;
    }
    [data-bs-theme="dark"] .cinematic-stepper {
        background: #0f172a;
        border-color: rgba(255, 255, 255, 0.15);
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
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .cinematic-stepper-btn:hover {
        background: rgba(16, 185, 129, 0.12);
    }
    .cinematic-stepper-input {
        width: 52px;
        height: 38px;
        background: transparent;
        border: none;
        text-align: center;
        font-size: 0.95rem;
        color: inherit;
    }
    .cinematic-stepper-input:focus {
        outline: none;
    }

    /* CTA BUTTON */
    .cinematic-btn-cta {
        padding: 10px 26px;
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
