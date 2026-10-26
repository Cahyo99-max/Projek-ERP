<div class="modal fade" id="modalDetailBundle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1240px; width: 95vw;">
        <div class="modal-content spk-detail-modal-content rounded-4 border overflow-hidden shadow-2xl">
            
            {{-- MODAL HEADER (CINEMATIC GRADIENT) --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3.5" style="background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); box-shadow: 0 0 20px rgba(14, 165, 233, 0.35); width: 44px; height: 44px;">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="detail_bundle_title" style="font-size: 1.15rem; letter-spacing: -0.2px;">Rincian Master Paket Bundle</h5>
                            <span class="badge font-monospace px-2.5 py-0.5" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.68rem; letter-spacing: 0.5px;">DETAIL STUDIO</span>
                        </div>
                        <small class="text-white-50" id="detail_bundle_subtitle" style="font-size: 0.8rem;">Struktur skema harga jual 3-tier, HPP real-time, dan komposisi barang penyusun.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body spk-detail-modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                
                {{-- SUMMARY CARD --}}
                <div class="spk-detail-card-section p-4 rounded-4 mb-3.5">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-7 d-flex align-items-center gap-3.5">
                            <div id="detail_bundle_foto_container" class="flex-shrink-0 position-relative">
                                <div class="rounded-3 border bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted" style="width: 76px; height: 76px;">
                                    <i class="bi bi-box-seam fs-2"></i>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge spk-badge-bundle-code font-monospace px-2 py-0.5" id="detail_kode_bundle" style="font-size: 0.8rem;">BDL-XXXXX</span>
                                    <span class="badge rounded-pill font-monospace px-2.5 py-0.5 fw-bold" id="detail_status_bundle" style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4);">Aktif (Siap Jual)</span>
                                </div>
                                <h5 class="fw-bold spk-bundle-title mb-1" id="detail_nama_bundle">Nama Paket Bundle</h5>
                                <div class="spk-bundle-meta small" id="detail_meta_bundle">Kategori: - | Satuan: Set</div>
                                <div class="spk-bundle-meta small mt-1 fst-italic" id="detail_spesifikasi_bundle" style="font-size: 0.78rem;"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-5 text-md-end border-top border-md-top-0 pt-3 pt-md-0">
                            <small class="spk-bundle-meta font-monospace small d-block mb-1 text-uppercase" style="letter-spacing: 0.5px;">KAPASITAS PERAKITAN MAKSIMAL (VIRTUAL STOCK)</small>
                            <div id="detail_badge_stok_bundle" class="mt-1">
                                <span class="badge spk-badge-cyan rounded-pill px-3.5 py-2 font-monospace fs-6 fw-bold shadow-xs">
                                    <i class="bi bi-box-seam me-1"></i>0 Set Siap Dirakit
                                </span>
                            </div>
                            <small class="spk-bundle-meta mt-1.5 d-block text-white-50" style="font-size: 11px;">
                                Kapasitas perakitan dihitung secara real-time berdasarkan komponen penyusun dengan ketersediaan stok paling terbatas <i>(bottleneck item)</i>.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- SECTION II: SKEMA HARGA JUAL 3-TIER & REALTIME HPP --}}
                <div class="spk-detail-card-section p-4 rounded-4 mb-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2 text-info fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                            <i class="bi bi-tag-fill"></i> SKEMA HARGA JUAL &amp; REAL-TIME HPP
                        </div>
                        <span class="badge font-monospace px-2.5 py-1" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.72rem;">Multi-Tier Pricing</span>
                    </div>

                    {{-- HUD Real-time HPP Banner --}}
                    <div class="p-3.5 rounded-3 mb-3.5 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-md" style="background: linear-gradient(135deg, #091e3a 0%, #102a45 50%, #081b2f 100%); border: 1px solid rgba(56, 189, 248, 0.35);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 p-2 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.2); width: 40px; height: 40px;">
                                <i class="bi bi-cpu-fill fs-5"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info text-dark font-monospace fw-bold px-1.5 py-0.5" style="font-size: 0.65rem;">REAL-TIME ENGINE</span>
                                    <span class="text-white fw-semibold small">Akumulasi HPP Gudang Komponen</span>
                                </div>
                                <div class="text-white-50" style="font-size: 11px;">Dihitung dari harga pokok stok barang penyusun tanpa disimpan statis</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-white-50 font-monospace text-uppercase" style="font-size: 0.7rem;">TOTAL ESTIMASI HPP</div>
                            <span class="fs-5 fw-bold font-monospace" id="detail_harga_hpp" style="color: #38bdf8; text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);">Rp 0</span>
                        </div>
                    </div>

                    {{-- 3 TIER PRICING CARDS --}}
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="spk-detail-tier-card p-3 rounded-3 shadow-2xs" style="border-top: 3px solid #3b82f6 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small" style="color: #3b82f6;"><i class="bi bi-person-fill me-1"></i> Tier 1: Retail / Satuan</span>
                                    <span class="badge font-monospace" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.4); font-size: 0.65rem;">RETAIL</span>
                                </div>
                                <div class="fw-bold font-monospace spk-detail-price-text fs-5 mb-1" id="detail_harga_retail">Rp 0</div>
                                <div class="d-flex justify-content-between align-items-center font-monospace" style="font-size: 0.78rem;">
                                    <span class="spk-bundle-meta">Margin / Profit:</span>
                                    <div id="detail_margin_retail_badge"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="spk-detail-tier-card p-3 rounded-3 shadow-2xs" style="border-top: 3px solid #10b981 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small" style="color: #10b981;"><i class="bi bi-people-fill me-1"></i> Tier 2: Grosir / Agen</span>
                                    <span class="badge font-monospace" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); font-size: 0.65rem;">GROSIR</span>
                                </div>
                                <div class="fw-bold font-monospace spk-detail-price-text fs-5 mb-1" id="detail_harga_grosir">Rp 0</div>
                                <div class="d-flex justify-content-between align-items-center font-monospace" style="font-size: 0.78rem;">
                                    <span class="spk-bundle-meta">Margin / Profit:</span>
                                    <div id="detail_margin_grosir_badge"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="spk-detail-tier-card p-3 rounded-3 shadow-2xs" style="border-top: 3px solid #f59e0b !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small" style="color: #f59e0b;"><i class="bi bi-briefcase-fill me-1"></i> Tier 3: Tender / Proyek</span>
                                    <span class="badge font-monospace" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 0.65rem;">TENDER</span>
                                </div>
                                <div class="fw-bold font-monospace spk-detail-price-text fs-5 mb-1" id="detail_harga_tender">Rp 0</div>
                                <div class="d-flex justify-content-between align-items-center font-monospace" style="font-size: 0.78rem;">
                                    <span class="spk-bundle-meta">Margin / Profit:</span>
                                    <div id="detail_margin_tender_badge"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION III: RINCIAN KOMPONEN PENYUSUN (TABLE) --}}
                <div class="spk-detail-card-section p-4 rounded-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                            <i class="bi bi-boxes"></i> RINCIAN KOMPOSISI BARANG PENYUSUN PAKET
                        </div>
                        <span class="badge font-monospace px-3 py-1" id="detail_count_komponen" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); border-radius: 20px;">0 Item</span>
                    </div>

                    <div class="table-responsive rounded-3 border border-secondary border-opacity-15">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="spk-detail-table-header font-monospace">
                                <tr>
                                    <th class="text-center py-2.5" style="width: 45px;">NO</th>
                                    <th class="py-2.5" style="min-width: 230px;">BARANG JADI / PENYUSUN</th>
                                    <th class="text-center py-2.5" style="width: 110px;">QTY / PAKET</th>
                                    <th class="text-end py-2.5" style="width: 130px;">HPP GUDANG</th>
                                    <th class="text-end py-2.5" style="width: 140px;">SUBTOTAL HPP</th>
                                    <th class="text-center py-2.5" style="width: 120px;">STOK GUDANG</th>
                                    <th class="text-center py-2.5" style="width: 130px;">KAPASITAS RAKIT</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDetailKomponenBundle">
                                {{-- Rows via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- MODAL FOOTER (CINEMATIC QUICK ACTIONS) --}}
            <div class="modal-footer spk-detail-modal-footer px-4 py-3 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 font-monospace" data-bs-dismiss="modal">
                    Tutup
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-4 py-2 font-monospace fw-bold shadow-xs" id="btnDetailEditBundle">
                        <i class="bi bi-pencil-square me-1"></i> Edit Master Bundle
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL LIGHTBOX / SLIDER FOTO POPUP --}}
<div class="modal fade" id="modalBundlePhotoGallery" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(14px); z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 860px;">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-2xl" style="background: #0b132b; border: 1px solid rgba(255, 255, 255, 0.15) !important;">
            
            {{-- Header Gallery --}}
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: linear-gradient(135deg, #070d19 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); width: 36px; height: 36px;">
                        <i class="bi bi-images fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="galleryModalTitle">Galeri Foto Produk</h6>
                        <small class="text-info font-monospace" id="galleryModalCounter" style="font-size: 11px;">Foto 1 dari 1</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Carousel Body --}}
            <div class="modal-body p-3 p-md-4 text-center position-relative" style="background: #020617;">
                <div id="carouselBundleGallery" class="carousel slide carousel-fade" data-bs-ride="false">
                    
                    {{-- Carousel Inner Slides --}}
                    <div class="carousel-inner rounded-3 overflow-hidden" id="carouselBundleInner" style="background: #030712; min-height: 350px;">
                        {{-- Items injected via JS --}}
                    </div>

                    {{-- Navigation Arrows (Visible if 2 or more photos) --}}
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselBundleGallery" data-bs-slide="prev" id="galleryPrevBtn" style="width: 48px; height: 48px; top: 50%; transform: translateY(-50%); left: 16px; border-radius: 50%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.25); opacity: 0.95; z-index: 10;">
                        <span class="carousel-control-prev-icon" aria-hidden="true" style="width: 20px; height: 20px;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselBundleGallery" data-bs-slide="next" id="galleryNextBtn" style="width: 48px; height: 48px; top: 50%; transform: translateY(-50%); right: 16px; border-radius: 50%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.25); opacity: 0.95; z-index: 10;">
                        <span class="carousel-control-next-icon" aria-hidden="true" style="width: 20px; height: 20px;"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>

                {{-- Thumbnail Nav Bar --}}
                <div class="d-flex align-items-center justify-content-center gap-2 mt-3 flex-wrap" id="galleryThumbnailsContainer">
                    {{-- Thumbs injected via JS --}}
                </div>
            </div>

            {{-- Footer Gallery --}}
            <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-2.5 justify-content-between" style="background: #070d19;">
                <small class="text-white-50 font-monospace" style="font-size: 11px;">
                    <i class="bi bi-info-circle me-1 text-info"></i>Gunakan panah kiri/kanan keyboard atau geser untuk navigasi foto.
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 font-monospace" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .spk-photo-hover-zoom {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
        cursor: pointer;
    }
    .spk-photo-hover-zoom:hover {
        transform: scale(1.08);
        box-shadow: 0 0 15px rgba(14, 165, 233, 0.6) !important;
        border-color: #38bdf8 !important;
    }
    .spk-gallery-thumb {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        opacity: 0.5;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }
    .spk-gallery-thumb:hover {
        opacity: 0.85;
        transform: scale(1.05);
    }
    .spk-gallery-thumb.active {
        opacity: 1;
        border-color: #38bdf8;
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.6);
    }
</style>
@endpush

@push('scripts')
<script>
    let currentDetailBundleId = null;
    let currentDetailBundleData = null;

    function numberFormatLocal(val, maxDecimals = 2) {
        const num = Number(val || 0);
        return num.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: maxDecimals
        });
    }

    // Lightbox / Carousel Gallery Opener
    window.openPhotoGalleryModal = function(title, photosList, startIndex = 0) {
        if (!photosList || photosList.length === 0) return;

        $('#galleryModalTitle').text(title || 'Galeri Foto Produk');
        const total = photosList.length;

        let slidesHtml = '';
        let thumbsHtml = '';

        photosList.forEach((p, idx) => {
            const url = typeof p === 'string' ? p : p.url;
            const isActive = idx === startIndex ? 'active' : '';

            slidesHtml += `
                <div class="carousel-item ${isActive}" data-index="${idx}">
                    <div class="d-flex align-items-center justify-content-center p-2" style="height: 480px; width: 100%;">
                        <img src="${url}" class="rounded-3 shadow-lg" style="max-height: 460px; max-width: 100%; width: auto; height: auto; object-fit: contain; margin: 0 auto; display: block;">
                    </div>
                </div>
            `;

            if (total > 1) {
                thumbsHtml += `
                    <img src="${url}" class="spk-gallery-thumb ${isActive}" data-bs-target="#carouselBundleGallery" data-bs-slide-to="${idx}" aria-label="Slide ${idx + 1}">
                `;
            }
        });

        $('#carouselBundleInner').html(slidesHtml);
        $('#galleryThumbnailsContainer').html(thumbsHtml);
        $('#galleryModalCounter').text(`Foto ${startIndex + 1} dari ${total}`);

        if (total > 1) {
            $('#galleryPrevBtn, #galleryNextBtn, #galleryThumbnailsContainer').show();
        } else {
            $('#galleryPrevBtn, #galleryNextBtn, #galleryThumbnailsContainer').hide();
        }

        const galleryModal = new bootstrap.Modal(document.getElementById('modalBundlePhotoGallery'));
        galleryModal.show();
    };

    // Carousel Slide Event Listener (Update Counter & Active Thumb)
    $('#carouselBundleGallery').on('slid.bs.carousel', function(e) {
        const activeIdx = $(e.relatedTarget).data('index');
        const total = $('#carouselBundleInner .carousel-item').length;
        $('#galleryModalCounter').text(`Foto ${activeIdx + 1} dari ${total}`);

        $('.spk-gallery-thumb').removeClass('active');
        $(`.spk-gallery-thumb[data-bs-slide-to="${activeIdx}"]`).addClass('active');
    });

    function showBundleDetail(id) {
        currentDetailBundleId = id;

        $('#tbodyDetailKomponenBundle').html(`
            <tr>
                <td colspan="7" class="text-center py-4 text-info">
                    <span class="spinner-border spinner-border-sm me-1"></span> Memuat rincian paket bundle...
                </td>
            </tr>
        `);
        $('#modalDetailBundle').modal('show');

        $.ajax({
            url: `/master-bundle/${id}/show`,
            method: 'GET',
            success: function(res) {
                if (res.success && (res.bundle || res.data)) {
                    const b = res.bundle || res.data;
                    currentDetailBundleData = b;

                    $('#detail_kode_bundle').text(b.kode_barang);
                    $('#detail_nama_bundle').text(b.nama_barang);
                    
                    if (b.status === 'Active') {
                        $('#detail_status_bundle').text('Aktif (Siap Jual)').css({
                            'background': 'rgba(16, 185, 129, 0.2)',
                            'color': '#34d399',
                            'border-color': 'rgba(52, 211, 153, 0.4)'
                        });
                    } else {
                        $('#detail_status_bundle').text('Nonaktif').css({
                            'background': 'rgba(148, 163, 184, 0.2)',
                            'color': '#94a3b8',
                            'border-color': 'rgba(148, 163, 184, 0.4)'
                        });
                    }

                    $('#detail_spesifikasi_bundle').text(b.spesifikasi ? `"${b.spesifikasi}"` : '');
                    
                    // Bundle Photo
                    const bundleFotos = (b.fotos && b.fotos.length > 0) ? b.fotos : (b.foto_url ? [{ url: b.foto_url }] : []);
                    if (bundleFotos.length > 0) {
                        const badgeCountHtml = bundleFotos.length > 1 
                            ? `<span class="badge position-absolute bottom-0 end-0 bg-dark bg-opacity-80 text-white font-monospace px-1.5 py-0.5 rounded-2" style="font-size: 10px;"><i class="bi bi-images me-1 text-info"></i>${bundleFotos.length}</span>` 
                            : '';

                        $('#detail_bundle_foto_container').html(`
                            <div class="position-relative d-inline-block spk-photo-hover-zoom rounded-3" onclick="openBundleMainGallery()" title="Klik untuk memperbesar galeri foto">
                                <img src="${bundleFotos[0].url}" class="rounded-3 border shadow-sm" style="width: 76px; height: 76px; object-fit: cover;">
                                ${badgeCountHtml}
                            </div>
                        `);
                    } else {
                        $('#detail_bundle_foto_container').html(`
                            <div class="rounded-3 border bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted" style="width: 76px; height: 76px;">
                                <i class="bi bi-box-seam fs-2"></i>
                            </div>
                        `);
                    }

                    const kat = b.kategori_nama || '-';
                    const brand = b.brand_nama || '-';
                    const sat = b.satuan_nama || 'Set';
                    $('#detail_meta_bundle').html(`Kategori: <b>${kat}</b> &bull; Merk: <b>${brand}</b> &bull; Satuan: <b>${sat}</b>`);

                    const hppRealtime = parseFloat(b.realtime_hpp || 0);
                    const retail = parseFloat(b.harga_jual_retail || 0);
                    const grosir = parseFloat(b.harga_jual_grosir || 0);
                    const tender = parseFloat(b.harga_jual_tender || 0);

                    $('#detail_harga_hpp').text(`Rp ${numberFormatLocal(hppRealtime)}`);
                    $('#detail_harga_retail').text(`Rp ${numberFormatLocal(retail)}`);
                    $('#detail_harga_grosir').text(`Rp ${numberFormatLocal(grosir)}`);
                    $('#detail_harga_tender').text(`Rp ${numberFormatLocal(tender)}`);

                    const calcMarginHtml = function(price) {
                        if (price <= 0 || hppRealtime <= 0) return '<span class="text-muted" style="font-size:11px;">-</span>';
                        const diff = price - hppRealtime;
                        const pct = ((diff / price) * 100).toFixed(1);
                        const cls = pct >= 20 ? 'text-success' : (pct >= 0 ? 'text-warning' : 'text-danger');
                        return `<span class="fw-bold ${cls} font-monospace" style="font-size:11px;">${diff >= 0 ? '+' : ''}${pct}% (Rp ${numberFormatLocal(diff)})</span>`;
                    };

                    $('#detail_margin_retail_badge').html(calcMarginHtml(retail));
                    $('#detail_margin_grosir_badge').html(calcMarginHtml(grosir));
                    $('#detail_margin_tender_badge').html(calcMarginHtml(tender));

                    const maxAssembleable = b.max_assembleable || 0;
                    const badgeClass = maxAssembleable > 0 ? 'spk-badge-cyan' : 'spk-badge-red';
                    $('#detail_badge_stok_bundle').html(`
                        <span class="badge ${badgeClass} rounded-pill px-3.5 py-2 font-monospace fs-6 fw-bold shadow-xs">
                            <i class="bi bi-box-seam me-1"></i>${numberFormatLocal(maxAssembleable)} ${sat} Siap Dirakit
                        </span>
                    `);

                    if (b.components && b.components.length > 0) {
                        $('#detail_count_komponen').text(`${b.components.length} Jenis Barang`);
                        let html = '';

                        b.components.forEach((c, idx) => {
                            const compFotos = (c.fotos && c.fotos.length > 0) ? c.fotos : (c.foto_url ? [{ url: c.foto_url }] : []);
                            
                            let imgHtml = '';
                            if (compFotos.length > 0) {
                                const photoSrc = typeof compFotos[0] === 'string' ? compFotos[0] : (compFotos[0].url || compFotos[0]);
                                imgHtml = `
                                    <div class="position-relative d-inline-block spk-photo-hover-zoom me-2.5 flex-shrink-0" onclick="openComponentGallery(${idx})" title="Klik untuk melihat foto ${c.nama_barang}">
                                        <img src="${photoSrc}" class="rounded-3 border shadow-xs" style="width:40px; height:40px; object-fit:cover;">
                                    </div>
                                `;
                            } else {
                                imgHtml = `<div class="rounded-3 border bg-secondary bg-opacity-10 me-2.5 d-inline-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width:40px; height:40px;"><i class="bi bi-box-seam small"></i></div>`;
                            }

                            const stokGudang = c.stok_aktual_gudang !== undefined ? parseFloat(c.stok_aktual_gudang) : parseFloat(c.stok_gudang || 0);
                            const maxPacks = c.possible_bundles !== undefined ? parseFloat(c.possible_bundles) : parseFloat(c.max_bundle_packs || 0);

                            html += `
                                <tr class="spk-detail-table-row">
                                    <td class="text-center font-monospace text-muted">${idx + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            ${imgHtml}
                                            <div class="d-flex flex-column">
                                                <div class="mb-0.5">
                                                    <span class="badge spk-badge-bundle-code font-monospace px-2 py-0.5"><i class="bi bi-qr-code me-1"></i>${c.kode_barang}</span>
                                                </div>
                                                <span class="fw-bold spk-bundle-title" style="font-size:13px;">${c.nama_barang}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace fw-bold spk-bundle-title">${numberFormatLocal(c.qty_per_bundle)} ${c.satuan}</td>
                                    <td class="text-end font-monospace small ${c.hpp_satuan > 0 ? 'fw-bold' : 'text-muted'}" style="${c.hpp_satuan > 0 ? 'color: #34d399;' : ''}">
                                        ${c.hpp_satuan > 0 ? 'Rp ' + numberFormatLocal(c.hpp_satuan) : '-'}
                                    </td>
                                    <td class="text-end font-monospace small fw-bold ${c.subtotal_hpp > 0 ? '' : 'text-muted'}" style="${c.subtotal_hpp > 0 ? 'color: #38bdf8;' : ''}">
                                        ${c.subtotal_hpp > 0 ? 'Rp ' + numberFormatLocal(c.subtotal_hpp) : '-'}
                                    </td>
                                    <td class="text-center font-monospace fw-bold" style="${stokGudang > 0 ? 'color: #34d399;' : 'color: #ef4444;'}">${numberFormatLocal(stokGudang)} ${c.satuan}</td>
                                    <td class="text-center">
                                        <span class="badge font-monospace fw-bold px-2.5 py-1" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 11.5px;">
                                            ${numberFormatLocal(maxPacks)} Set
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });

                        $('#tbodyDetailKomponenBundle').html(html);
                    } else {
                        $('#detail_count_komponen').text('0 Item');
                        $('#tbodyDetailKomponenBundle').html(`
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada barang penyusun terdaftar pada paket ini.</td>
                            </tr>
                        `);
                    }
                }
            },
            error: function() {
                $('#tbodyDetailKomponenBundle').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">Gagal memuat rincian paket bundle.</td>
                    </tr>
                `);
            }
        });
    }

    function openBundleMainGallery() {
        if (!currentDetailBundleData) return;
        const b = currentDetailBundleData;
        const photos = (b.fotos && b.fotos.length > 0) ? b.fotos : (b.foto_url ? [{ url: b.foto_url }] : []);
        openPhotoGalleryModal(`${b.kode_barang} - ${b.nama_barang}`, photos, 0);
    }

    function openComponentGallery(compIndex) {
        if (!currentDetailBundleData || !currentDetailBundleData.components) return;
        const c = currentDetailBundleData.components[compIndex];
        if (!c) return;
        const photos = (c.fotos && c.fotos.length > 0) ? c.fotos : (c.foto_url ? [{ url: c.foto_url }] : []);
        openPhotoGalleryModal(`${c.kode_barang} - ${c.nama_barang}`, photos, 0);
    }

    $(document).on('click', '#btnDetailEditBundle', function() {
        $('#modalDetailBundle').modal('hide');
        if (currentDetailBundleId) {
            editBundleModal(currentDetailBundleId);
        }
    });
</script>
@endpush
