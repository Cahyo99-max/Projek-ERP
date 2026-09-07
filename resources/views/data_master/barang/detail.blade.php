<div class="modal modal-blur fade" id="modalDetailBarang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold fs-4 d-flex align-items-center">
                    <span class="p-2 bg-info-subtle text-info rounded-3 me-2.5 d-inline-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                    </span>
                    Detail Fisik & Spesifikasi
                </h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    
                    <!-- Identitas Utama -->
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Kode SKU</div>
                            <div class="detail-card-value text-primary font-monospace" id="detail_sku">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Barcode</div>
                            <div class="detail-card-value font-monospace" id="detail_barcode">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Pelacakan / SN</div>
                            <div class="detail-card-value" id="detail_tipe_pelacakan">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Satuan Dasar (Stok)</div>
                            <div class="detail-card-value fw-bold text-success" id="detail_satuan_nama">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Ukuran</div>
                            <div class="detail-card-value" id="detail_ukuran">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Warna</div>
                            <div class="d-flex align-items-center mt-1.5">
                                <span class="d-inline-block rounded-circle border shadow-sm me-2" id="detail_warna_circle" style="width: 16px; height: 16px; flex-shrink:0;"></span>
                                <span class="fw-bold fs-6" id="detail_warna_nama">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dimensi Fisik -->
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Panjang</div>
                            <div class="detail-card-value" id="detail_panjang">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Lebar</div>
                            <div class="detail-card-value" id="detail_lebar">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Tinggi</div>
                            <div class="detail-card-value" id="detail_tinggi">-</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="detail-card-info">
                            <div class="detail-card-label">Berat</div>
                            <div class="detail-card-value" id="detail_berat">-</div>
                        </div>
                    </div>

                    <!-- Tier Harga Jual Satuan -->
                    <div class="col-12 col-md-4">
                        <div class="detail-card-info card-tier-retail">
                            <div class="detail-card-label"><i class="bi bi-cart-check me-1"></i>Retail (Eceran)</div>
                            <div class="detail-card-value font-monospace" id="detail_harga_retail">-</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="detail-card-info card-tier-grosir">
                            <div class="detail-card-label"><i class="bi bi-boxes me-1"></i>Grosir (Partai)</div>
                            <div class="detail-card-value font-monospace" id="detail_harga_grosir">-</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="detail-card-info card-tier-tender">
                            <div class="detail-card-label"><i class="bi bi-briefcase me-1"></i>Tender (Proyek)</div>
                            <div class="detail-card-value font-monospace" id="detail_harga_tender">-</div>
                        </div>
                    </div>

                    <!-- Section Konversi Satuan (Multi-Satuan) -->
                    <div class="col-12 d-none" id="detail_konversi_section">
                        <div class="fw-bold text-primary mt-2 mb-2 d-flex align-items-center" style="font-size:0.88rem;">
                            <i class="bi bi-arrow-left-right me-2"></i> Daftar Multi-Satuan & Rasio Konversi
                        </div>
                        <div class="detail-table-container">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small detail-table">
                                    <thead>
                                        <tr>
                                            <th>Satuan Alternatif (UoM)</th>
                                            <th>Rasio Konversi ke Satuan Dasar</th>
                                            <th>Peruntukan Transaksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_konversi_table_body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Section Varian (Parent product variants) -->
                    <div class="col-12 d-none" id="detail_varian_section">
                        <div class="fw-bold text-primary mt-2 mb-2 d-flex align-items-center" style="font-size:0.88rem;">
                            <i class="bi bi-tags-fill me-2"></i> Daftar Varian Ukuran
                        </div>
                        <div class="detail-table-container">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small detail-table">
                                    <thead>
                                        <tr>
                                            <th>Kode Varian (SKU)</th>
                                            <th>Ukuran / Tipe</th>
                                            <th>Warna</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_varian_table_body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Galeri Foto Barang -->
                    <div class="col-12">
                        <div class="fw-bold text-secondary mb-1.5" style="font-size:0.82rem; letter-spacing:0.02em;">GALERI FOTO BARANG</div>
                        <div class="d-flex flex-wrap gap-2.5 p-3 rounded-3 detail-gallery-box" id="detail_fotos_gallery">
                            <span class="text-muted small">Memuat galeri foto...</span>
                        </div>
                    </div>

                    <!-- Spesifikasi Detail -->
                    <div class="col-12">
                        <div class="fw-bold text-secondary mb-1.5" style="font-size:0.82rem; letter-spacing:0.02em;">SPESIFIKASI DETAIL</div>
                        <div class="detail-spec-box small" id="detail_spesifikasi" style="white-space: pre-wrap;">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Carousel Modal -->
<div class="modal fade" id="modalPhotoLightbox" tabindex="-1" style="background: rgba(0,0,0,0.85); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 p-0 justify-content-end mb-2">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%); font-size: 1.25rem;"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <div id="lightboxCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner" id="lightbox_carousel_inner">
                        <!-- Carousel items injected dynamically -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: drop-shadow(0px 2px 5px rgba(0,0,0,0.7)); width: 2.5rem; height: 2.5rem;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#lightboxCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="filter: drop-shadow(0px 2px 5px rgba(0,0,0,0.7)); width: 2.5rem; height: 2.5rem;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Detail Modal Styling - Hardcoded Dark/Light Colors to avoid invisible text */
    #modalDetailBarang .modal-content {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.15);
        background-color: #ffffff !important;
        color: #1e293b !important;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    [data-bs-theme="dark"] #modalDetailBarang .modal-content {
        background-color: #1e1e24 !important;
        border-color: #2d2d34 !important;
        color: #f1f5f9 !important;
    }

    #modalDetailBarang .modal-title {
        color: #0f172a !important;
    }

    [data-bs-theme="dark"] #modalDetailBarang .modal-title {
        color: #f1f5f9 !important;
    }

    .detail-card-info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        height: 100%;
        transition: all 0.2s ease;
    }

    [data-bs-theme="dark"] .detail-card-info {
        background: #27272f !important;
        border-color: #373742 !important;
    }

    .detail-card-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b !important;
    }

    [data-bs-theme="dark"] .detail-card-label {
        color: #94a3b8 !important;
    }

    .detail-card-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a !important;
        margin-top: 4px;
    }

    [data-bs-theme="dark"] .detail-card-value {
        color: #f1f5f9 !important;
    }

    .detail-spec-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        color: #334155 !important;
    }

    [data-bs-theme="dark"] .detail-spec-box {
        background: #27272f !important;
        border-color: #373742 !important;
        color: #cbd5e1 !important;
    }

    .detail-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    [data-bs-theme="dark"] .detail-table-container {
        border-color: #373742;
    }

    .detail-table th {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        font-weight: 600;
        border-bottom-width: 1px;
    }

    [data-bs-theme="dark"] .detail-table th {
        background-color: #27272f !important;
        color: #94a3b8 !important;
    }

    .detail-photo-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .detail-photo-thumb:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    [data-bs-theme="dark"] .detail-photo-thumb {
        border-color: rgba(255, 255, 255, 0.15);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Handle photo thumbnail clicks inside detail gallery to trigger slider modal
        $(document).on('click', '.btn-view-photo', function(e) {
            e.preventDefault();
            
            // Get all images in the detail gallery
            const images = [];
            $('#detail_fotos_gallery img').each(function() {
                images.push($(this).attr('src'));
            });

            if (images.length === 0) return;

            const clickedSrc = $(this).find('img').attr('src');
            
            let carouselItemsHtml = '';
            images.forEach((src, idx) => {
                const activeClass = src === clickedSrc ? 'active' : '';
                carouselItemsHtml += `
                    <div class="carousel-item ${activeClass}">
                        <img src="${src}" class="d-block mx-auto img-fluid rounded-3" style="max-height: 75vh; object-fit: contain;">
                    </div>
                `;
            });

            $('#lightbox_carousel_inner').html(carouselItemsHtml);

            // Hide sliding controls if there's only 1 image
            if (images.length <= 1) {
                $('#modalPhotoLightbox .carousel-control-prev, #modalPhotoLightbox .carousel-control-next').addClass('d-none');
            } else {
                $('#modalPhotoLightbox .carousel-control-prev, #modalPhotoLightbox .carousel-control-next').removeClass('d-none');
            }

            // Initialize bootstrap carousel if needed or just trigger show
            $('#lightboxCarousel').carousel({
                interval: false, // Don't auto-rotate
                ride: false
            });

            $('#modalPhotoLightbox').modal('show');
        });
    });
</script>
