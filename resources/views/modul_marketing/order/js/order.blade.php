@php
    $user = auth()->user();
    $isSuper = $user && $user->hasAnyRole(['Super Admin', 'Owner', 'Admin']);
    $isSuperAdminOnly = $user && $user->hasAnyRole(['Super Admin', 'Owner']);
    $canLihat   = $isSuper || ($user && $user->can('lihat-sales-order'));
    $canTambah  = $isSuper || ($user && $user->can('tambah-sales-order'));
    $canEdit    = $isSuper || ($user && $user->can('edit-sales-order'));
    $canHapus   = $isSuper || ($user && ($user->can('hapus-sales-order') || $user->can('batal-sales-order')));
    $canCetak   = $isSuper || ($user && ($user->can('cetak-sales-order') || $user->can('print-sales-order')));
    $canDO      = ($user && $user->hasRole('Super Admin')) || ($user && $user->can('tambah-delivery-order'));

    // Strict multi-entity cart storage isolation (PT vs Cabang)
    $activeCompanyId = $activeCompany->id ?? session('active_company_id') ?? 1;
    $activeBranchId  = $activeBranch->id ?? session('active_branch_id') ?? 0;
    $cartStorageKey  = 'erp_sales_order_cart_' . auth()->id() . '_' . $activeCompanyId . '_' . $activeBranchId;
@endphp
<script>
    window.ORDER_PPN_LIST = @json($ppnList ?? []);
    const SO_PERMISSIONS = {
        isSuperAdmin: @json($isSuperAdminOnly),
        canLihat:  @json($canLihat),
        canTambah: @json($canTambah),
        canEdit:   @json($canEdit),
        canHapus:  @json($canHapus),
        canCetak:  @json($canCetak),
        canDO:     @json($canDO),
    };

$(document).ready(function () {
    // ===== CSRF SETUP FOR ALL AJAX =====
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    // ===== GLOBAL IMAGE PLACEHOLDER (PREVENTS INFINITE LOOP ON 404) =====
    const PLACEHOLDER_IMG = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300' fill='%23f1f5f9'><rect width='300' height='300' fill='%23f1f5f9'/><g transform='translate(110, 100)'><rect x='0' y='0' width='80' height='80' rx='16' fill='%23e2e8f0'/><path d='M25 50 L38 35 L48 45 L58 32 L68 46 L68 56 L25 56 Z' fill='%2394a3b8'/><circle cx='34' cy='28' r='6' fill='%23cbd5e1'/></g><text x='150' y='210' font-family='sans-serif' font-size='13' font-weight='600' fill='%2394a3b8' text-anchor='middle'>Tidak Ada Foto</text></svg>";

    window.handleImgError = function (img) {
        if (!img) return;
        img.onerror = null;
        img.src = PLACEHOLDER_IMG;
    };

    // Safe Bootstrap Modal Helpers (Compatible with pure Bootstrap 5 and jQuery)
    window.showModal = function (selector) {
        try {
            let el = document.querySelector(selector);
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let instance = bootstrap.Modal.getOrCreateInstance(el);
                instance.show();
                return;
            }
        } catch (e) {
            console.warn('showModal bootstrap error:', e);
        }
        try {
            $(selector).modal('show');
        } catch (e) {
            console.warn('showModal jquery error:', e);
        }
    };

    window.hideModal = function (selector) {
        try {
            let el = document.querySelector(selector);
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                let instance = bootstrap.Modal.getOrCreateInstance(el);
                instance.hide();
                return;
            }
        } catch (e) {
            console.warn('hideModal bootstrap error:', e);
        }
        try {
            $(selector).modal('hide');
        } catch (e) {
            console.warn('hideModal jquery error:', e);
        }
    };

    // ===== FORMAT RUPIAH & RIBUAN HELPER =====
    function formatRp(amount) {
        let num = Number(amount) || 0;
        return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatThousands(val) {
        if (val === undefined || val === null || val === '') return '';
        let num = typeof val === 'number' ? val : parseFloat(val);
        if (isNaN(num)) return '';
        return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function unformatThousands(val) {
        if (val === undefined || val === null || val === '') return 0;
        if (typeof val === 'number') return isNaN(val) ? 0 : val;
        let str = val.toString().trim();
        str = str.replace(/\./g, '').replace(',', '.');
        let clean = str.replace(/[^0-9.-]/g, '');
        return parseFloat(clean) || 0;
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ===== GET UNIT PRICE BASED ON ACTIVE TIER =====
    function getItemUnitPrice(item, tier) {
        if (!item) return 0;
        let retail = (item.harga_retail !== undefined && item.harga_retail !== null && item.harga_retail !== '')
            ? Number(item.harga_retail)
            : ((item.retail !== undefined && item.retail !== null && item.retail !== '')
                ? Number(item.retail)
                : (Number(item.harga_jual_retail) || 0));

        let grosir = (item.harga_grosir !== undefined && item.harga_grosir !== null && item.harga_grosir !== '')
            ? Number(item.harga_grosir)
            : ((item.grosir !== undefined && item.grosir !== null && item.grosir !== '')
                ? Number(item.grosir)
                : (Number(item.harga_jual_grosir) || 0));

        let tender = (item.harga_tender !== undefined && item.harga_tender !== null && item.harga_tender !== '')
            ? Number(item.harga_tender)
            : ((item.tender !== undefined && item.tender !== null && item.tender !== '')
                ? Number(item.tender)
                : (Number(item.harga_jual_tender) || 0));

        retail = Number(retail) || 0;
        grosir = Number(grosir) || 0;
        tender = Number(tender) || 0;

        let basePrice = (retail > 0) ? retail : ((grosir > 0) ? grosir : tender);

        if (tier === 'Grosir') return (grosir > 0) ? grosir : basePrice;
        if (tier === 'Tender') return (tender > 0) ? tender : basePrice;
        return (retail > 0) ? retail : basePrice;
    }

    // ===== STATE VARIABLES =====
    let currentPricingTier = 'Retail'; // 'Retail' | 'Grosir' | 'Tender'
    let currentCategoryFilter = 'all';
    let currentBrandFilter = 'all';
    let currentTipeFilter = 'all';
    let currentSearchKeyword = '';
    let currentPage = 1;
    let cart = []; // Array of { id, kode, nama, photo_url, satuan, stock, harga_retail, harga_grosir, harga_tender, qty, diskon_nominal, subtotal }
    let tableOrders = null;

    // ===== CART STORAGE PERSISTENCE (STRICT MULTI-ENTITY / BRANCH ISOLATION) =====
    const CART_STORAGE_KEY = '{{ $cartStorageKey }}';

    function saveCartToStorage() {
        try {
            let data = {
                cart: cart,
                tier: currentPricingTier,
                customer_id: $('#cart_customer_id').val() || '',
                nama_walkin: $('#cart_nama_walkin').val() || '',
                telepon_walkin: $('#cart_telepon_walkin').val() || '',
                payment_term_id: $('#cart_payment_term_id').val() || '',
                biaya_pengiriman: $('#cart_biaya_pengiriman').val() || 0,
                use_dp: $('#cart_use_dp').is(':checked'),
                dp_mode: $('input[name="dp_mode"]:checked').val() || 'Nominal',
                dp_val: $('#cart_dp_val').val() || 0
            };
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            console.warn('Gagal menyimpan keranjang ke localStorage:', e);
        }
    }

    function loadCartFromStorage() {
        try {
            let raw = localStorage.getItem(CART_STORAGE_KEY);
            if (raw) {
                let parsed = JSON.parse(raw);
                if (parsed && Array.isArray(parsed.cart) && parsed.cart.length > 0) {
                    cart = parsed.cart;
                    if (parsed.tier) {
                        currentPricingTier = parsed.tier;
                        $('.tkpd-tier-btn').removeClass('active-retail active-grosir active-tender');
                        let tierBtn = $(`.tkpd-tier-btn[data-tier="${parsed.tier}"]`);
                        if (tierBtn.length) {
                            tierBtn.addClass(parsed.tier === 'Retail' ? 'active-retail' : (parsed.tier === 'Grosir' ? 'active-grosir' : 'active-tender'));
                        }
                    }
                    if (parsed.customer_id) {
                        $('#cart_customer_id').val(parsed.customer_id).trigger('change.select2');
                        $('#cart_walkin_wrap').hide();
                    } else {
                        $('#cart_customer_id').val('').trigger('change.select2');
                        $('#cart_walkin_wrap').show();
                        if (parsed.nama_walkin) $('#cart_nama_walkin').val(parsed.nama_walkin);
                        if (parsed.telepon_walkin) $('#cart_telepon_walkin').val(parsed.telepon_walkin);
                    }
                    if (parsed.payment_term_id) {
                        $('#cart_payment_term_id').val(parsed.payment_term_id);
                    }
                    if (parsed.biaya_pengiriman) {
                        $('#cart_biaya_pengiriman').val(parsed.biaya_pengiriman);
                    }
                    if (parsed.use_dp) {
                        $('#cart_use_dp').prop('checked', true);
                        $('#cart_dp_container').show();
                        if (parsed.dp_mode) {
                            $(`input[name="dp_mode"][value="${parsed.dp_mode}"]`).prop('checked', true);
                            $('#cart_dp_suffix').text(parsed.dp_mode === 'Persen' ? '%' : 'Rp');
                        }
                        if (parsed.dp_val) {
                            $('#cart_dp_val').val(parsed.dp_val);
                        }
                    }
                }
            }
        } catch (e) {
            console.warn('Gagal memuat keranjang dari localStorage:', e);
        }
        renderCart();
        updateCatalogTierPrices();
    }

    // ===== LOAD PRODUCTS (TOKOPEDIA CATALOG GRID) =====
    function loadProducts(page = 1, showSpinner = false) {
        currentPage = page;
        if (showSpinner || $('#product_grid_container .tkpd-product-grid').length === 0) {
            $('#product_grid_container').html(`
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="text-muted small mt-2">Memuat katalog produk...</div>
                </div>
            `);
        }

        $.ajax({
            url: "{{ route('marketing.sales-order.products') }}",
            type: "GET",
            dataType: "json",
            data: {
                page: currentPage,
                search: currentSearchKeyword,
                kategori_id: currentCategoryFilter,
                brand_id: currentBrandFilter,
                tipe_item: currentTipeFilter,
                per_page: 24
            },
            success: function (res) {
                try {
                    if (!res.data || res.data.length === 0) {
                        $('#product_grid_container').html(`
                            <div class="col-12 text-center py-5">
                                <div class="mb-3 text-muted" style="font-size: 3rem;"><i class="bi bi-search"></i></div>
                                <h6 class="fw-bold text-secondary">Produk Tidak Ditemukan</h6>
                                <p class="text-muted small">Coba ubah kata kunci pencarian atau filter kategori Anda.</p>
                            </div>
                        `);
                        $('#catalog_pagination').empty();
                        return;
                    }

                    let html = '<div class="tkpd-product-grid">';
                    res.data.forEach(p => {
                        let isJasa = (p.tipe_item === 'Jasa');
                        let isBundle = (p.tipe_item === 'Bundle');
                        let isOutOfStock = (!isJasa && Number(p.total_stok) <= 0);
                        let stockBadge = isJasa
                            ? `<span class="tkpd-badge-stock bg-info text-white">Layanan / Jasa</span>`
                            : (!isOutOfStock 
                                ? `<span class="tkpd-badge-stock bg-success text-white">${isBundle ? 'Stok Bundle: ' : 'Stok: '}${Number(p.total_stok)}</span>`
                                : `<span class="tkpd-badge-stock bg-danger text-white">Stok Habis</span>`);
                        
                        let typeBadge = p.tipe_item === 'Bundle' 
                            ? `<span class="tkpd-badge-type bg-primary text-white">Bundle</span>`
                            : '';

                        let activePrice = getItemUnitPrice(p, currentPricingTier);
                        let priceClass = currentPricingTier === 'Retail' ? 'tkpd-price-retail' : (currentPricingTier === 'Grosir' ? 'tkpd-price-grosir' : 'tkpd-price-tender');
                        let photoSrc = p.photo_url || PLACEHOLDER_IMG;

                        let btnAction = isOutOfStock
                            ? `<button class="tkpd-btn-add disabled" disabled title="Stok barang sedang kosong">
                                   <i class="bi bi-slash-circle"></i> Stok Habis
                               </button>`
                            : `<button class="tkpd-btn-add btn-add-cart" 
                                    data-id="${p.id}" 
                                    data-kode="${p.kode_barang}" 
                                    data-nama="${p.nama_barang}" 
                                    data-satuan="${p.satuan_name}" 
                                    data-tipe="${p.tipe_item}"
                                    data-photo="${photoSrc}" 
                                    data-retail="${p.harga_retail}" 
                                    data-grosir="${p.harga_grosir}" 
                                    data-tender="${p.harga_tender}" 
                                    data-stock="${p.total_stok}">
                                    <i class="bi bi-cart-plus-fill"></i> + Keranjang
                               </button>`;

                        html += `
                            <div class="tkpd-card ${isOutOfStock ? 'out-of-stock' : ''}" data-retail="${p.harga_retail}" data-grosir="${p.harga_grosir}" data-tender="${p.harga_tender}">
                                <div class="tkpd-card-img-wrap btn-show-prod-detail cursor-pointer" data-id="${p.id}" title="Klik untuk lihat detail & foto">
                                    ${stockBadge}
                                    ${typeBadge}
                                    <img src="${photoSrc}" class="tkpd-card-img" alt="${p.nama_barang}" onerror="window.handleImgError(this)">
                                </div>
                                <div class="tkpd-card-body">
                                    <div>
                                        <div class="tkpd-card-meta d-flex justify-content-between">
                                            <span class="text-truncate">${p.kategori_name}</span>
                                            <span class="fw-bold">${p.kode_barang}</span>
                                        </div>
                                        <div class="tkpd-card-title btn-show-prod-detail cursor-pointer" data-id="${p.id}" title="${p.nama_barang}">${p.nama_barang}</div>
                                    </div>
                                    <div class="mt-2">
                                        <div class="tkpd-card-price ${priceClass}">
                                            ${formatRp(activePrice)}
                                        </div>
                                        ${btnAction}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';

                    $('#product_grid_container').html(html);

                    // Render Simple Pagination
                    renderPagination(res.current_page, res.last_page);
                } catch (err) {
                    console.error('Error rendering product grid:', err);
                    $('#product_grid_container').html(`
                        <div class="col-12 text-center py-5 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                            <p class="mt-2">Terjadi kesalahan saat memproses data produk.</p>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, err) {
                console.error('loadProducts AJAX error:', xhr.status, xhr.responseText);
                $('#product_grid_container').html(`
                    <div class="col-12 text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                        <p class="mt-2">Gagal memuat katalog produk (${xhr.status || 'Koneksi Terputus'}).</p>
                    </div>
                `);
            }
        });
    }

    window.loadProducts = loadProducts;

    // ===== RENDER PAGINATION =====
    function renderPagination(current, last) {
        if (last <= 1) {
            $('#catalog_pagination').empty();
            return;
        }

        let pagHtml = '<ul class="pagination pagination-sm justify-content-center mt-4 mb-0 gap-1">';
        pagHtml += `<li class="page-item ${current === 1 ? 'disabled' : ''}"><a class="page-link rounded-pill px-3 btn-page" href="javascript:void(0)" data-page="${current - 1}"><i class="bi bi-chevron-left"></i> Prev</a></li>`;
        
        for (let i = 1; i <= last; i++) {
            if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
                pagHtml += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link rounded-circle btn-page text-center" style="width:32px;height:32px;" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
            } else if (i === current - 2 || i === current + 2) {
                pagHtml += `<li class="page-item disabled"><span class="page-link rounded-circle border-0">...</span></li>`;
            }
        }

        pagHtml += `<li class="page-item ${current === last ? 'disabled' : ''}"><a class="page-link rounded-pill px-3 btn-page" href="javascript:void(0)" data-page="${current + 1}">Next <i class="bi bi-chevron-right"></i></a></li>`;
        pagHtml += '</ul>';

        $('#catalog_pagination').html(pagHtml);
    }

    $(document).on('click', '.btn-page', function () {
        let p = $(this).data('page');
        if (p) loadProducts(p);
    });

    // ===== SEARCH DEBOUNCE =====
    let searchTimeout = null;
    $('#catalog_search_input').on('input', function () {
        clearTimeout(searchTimeout);
        let val = $(this).val();
        searchTimeout = setTimeout(() => {
            currentSearchKeyword = val;
            loadProducts(1, true);
        }, 300);
    });

    // ===== CATEGORY PILLS CLICK =====
    $(document).on('click', '.tkpd-cat-pill', function (e) {
        e.preventDefault();
        let catId = $(this).attr('data-cat-id');
        if (catId === undefined || catId === null) {
            catId = $(this).data('cat-id') || 'all';
        }

        $('.tkpd-cat-pill').removeClass('active');
        $(this).addClass('active');

        currentCategoryFilter = String(catId);
        loadProducts(1, true);
    });

    // ===== BRAND & TIPE FILTERS =====
    $('#catalog_brand_filter').on('change', function () {
        currentBrandFilter = $(this).val();
        loadProducts(1, true);
    });

    $('#catalog_tipe_filter').on('change', function () {
        currentTipeFilter = $(this).val();
        loadProducts(1, true);
    });

    // ===== UPDATE CATALOG TIER PRICES (INSTANT DOM UPDATE) =====
    function updateCatalogTierPrices() {
        $('.tkpd-card').each(function () {
            let card = $(this);
            let btn = card.find('.btn-add-cart');
            let retail = Number(card.attr('data-retail') ?? btn.attr('data-retail')) || 0;
            let grosir = Number(card.attr('data-grosir') ?? btn.attr('data-grosir')) || 0;
            let tender = Number(card.attr('data-tender') ?? btn.attr('data-tender')) || 0;

            let price = getItemUnitPrice({ harga_retail: retail, harga_grosir: grosir, harga_tender: tender }, currentPricingTier);
            let priceClass = currentPricingTier === 'Retail' ? 'tkpd-price-retail' : (currentPricingTier === 'Grosir' ? 'tkpd-price-grosir' : 'tkpd-price-tender');

            card.find('.tkpd-card-price')
                .removeClass('tkpd-price-retail tkpd-price-grosir tkpd-price-tender')
                .addClass(priceClass)
                .text(formatRp(price));
        });
    }

    // ===== PRICING TIER SWITCHER (RETAIL / GROSIR / TENDER) =====
    $('.tkpd-tier-btn').on('click', function () {
        let tier = $(this).data('tier');
        if (tier === currentPricingTier) return;

        currentPricingTier = tier;
        $('.tkpd-tier-btn').removeClass('active-retail active-grosir active-tender');
        $(this).addClass('active-' + tier.toLowerCase());
        $('#cart_tier_badge').text(tier);

        // Update product catalog view prices immediately without spinner lag
        updateCatalogTierPrices();

        // Recalculate cart item prices
        updateCartTierPrices();
        saveCartToStorage();
    });

    // ===== CART MANAGEMENT FUNCTIONS =====
    function updateCartTierPrices() {
        cart.forEach(item => {
            let unitPrice = getItemUnitPrice(item, currentPricingTier);
            item.harga_satuan = unitPrice;
            calculateItemValues(item);
        });
        renderCart();
        saveCartToStorage();
    }

    // Helper to compute single cart item values (diskon & ppn)
    function calculateItemValues(item) {
        let qty = Math.max(0.0001, parseFloat(item.qty) || 1);
        let unitPrice = Math.max(0, parseFloat(item.harga_satuan) || 0);
        let gross = Math.round(qty * unitPrice);

        let diskonMode = item.diskon_tipe || 'Rp';
        let diskonVal = parseFloat(item.diskon_nilai) || 0;
        let diskonNominal = 0;
        let diskonPersen = 0;

        if (diskonMode === '%') {
            diskonPersen = Math.min(100, Math.max(0, diskonVal));
            diskonNominal = Math.round(gross * (diskonPersen / 100));
        } else {
            diskonNominal = Math.min(gross, Math.max(0, diskonVal));
            diskonPersen = gross > 0 ? Math.round((diskonNominal / gross) * 100) : 0;
        }

        let afterDiskon = Math.max(0, gross - diskonNominal);
        let tarifPpn = parseFloat(item.tarif_ppn) || 0;
        let nilaiPpn = tarifPpn > 0 ? Math.round(afterDiskon * (tarifPpn / 100)) : 0;

        item.gross = gross;
        item.diskon_nominal = diskonNominal;
        item.diskon_persen = diskonPersen;
        item.subtotal = afterDiskon;
        item.nilai_ppn = nilaiPpn;

        return item;
    }

    function addToCart(product) {
        if (!product || !product.id) return;
        let isJasa = (product.tipe === 'Jasa' || product.tipe_item === 'Jasa');
        let stock = Number(product.stock) || 0;
        if (!isJasa && stock <= 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Mencukupi',
                    text: `Persediaan produk "${product.nama}" saat ini tidak tersedia di gudang.`,
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: 'Mengerti'
                });
            } else {
                alert(`Stok produk "${product.nama}" tidak mencukupi.`);
            }
            return;
        }

        let existing = cart.find(c => String(c.id) === String(product.id));
        let unitPrice = Number(getItemUnitPrice(product, currentPricingTier));
        if (isNaN(unitPrice)) unitPrice = 0;

        if (existing) {
            if (!isJasa && (existing.qty + 1 > stock)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Mencukupi',
                        text: `Kuantitas pesanan untuk "${product.nama}" melebihi sisa persediaan yang tersedia (${stock} ${product.satuan || 'Pcs'}).`,
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    alert(`Kuantitas pesanan melebihi stok yang tersedia (${stock}).`);
                }
                return;
            }
            existing.qty += 1;
            existing.harga_satuan = unitPrice;
            calculateItemValues(existing);
        } else {
            let hRetail = (product.harga_retail !== undefined) ? Number(product.harga_retail) : (Number(product.retail) || 0);
            let hGrosir = (product.harga_grosir !== undefined) ? Number(product.harga_grosir) : (Number(product.grosir) || 0);
            let hTender = (product.harga_tender !== undefined) ? Number(product.harga_tender) : (Number(product.tender) || 0);

            let newItem = {
                id: product.id,
                kode: product.kode,
                nama: product.nama,
                satuan: product.satuan || 'Pcs',
                photo_url: product.photo,
                tipe_item: product.tipe_item || product.tipe || 'Stok',
                harga_retail: hRetail,
                harga_grosir: hGrosir,
                harga_tender: hTender,
                harga_satuan: unitPrice,
                qty: 1,
                diskon_tipe: 'Rp',
                diskon_nilai: 0,
                diskon_nominal: 0,
                diskon_persen: 0,
                ppn_pajak_id: '',
                tarif_ppn: 0,
                nilai_ppn: 0,
                subtotal: unitPrice,
                stock: stock
            };
            calculateItemValues(newItem);
            cart.push(newItem);
        }

        renderCart();
        saveCartToStorage();

        // Bounce animation on floating cart icon
        $('#cart_icon_bounce, #btn_floating_cart').addClass('cart-animate-bounce');
        setTimeout(() => {
            $('#cart_icon_bounce, #btn_floating_cart').removeClass('cart-animate-bounce');
        }, 500);

        // Micro-toast notification
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: `+1 ${product.nama} masuk keranjang`
            });
        }
    }

    $(document).on('click', '.btn-add-cart', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let btn = $(this).closest('.btn-add-cart');
        let p = {
            id: btn.attr('data-id') || btn.data('id'),
            kode: btn.attr('data-kode') || btn.data('kode'),
            nama: btn.attr('data-nama') || btn.data('nama'),
            satuan: btn.attr('data-satuan') || btn.data('satuan') || 'Pcs',
            tipe_item: btn.attr('data-tipe') || btn.data('tipe') || 'Stok',
            photo: btn.attr('data-photo') || btn.data('photo') || '',
            harga_retail: btn.attr('data-retail') !== undefined ? btn.attr('data-retail') : btn.data('retail'),
            harga_grosir: btn.attr('data-grosir') !== undefined ? btn.attr('data-grosir') : btn.data('grosir'),
            harga_tender: btn.attr('data-tender') !== undefined ? btn.attr('data-tender') : btn.data('tender'),
            retail: btn.attr('data-retail') !== undefined ? btn.attr('data-retail') : btn.data('retail'),
            grosir: btn.attr('data-grosir') !== undefined ? btn.attr('data-grosir') : btn.data('grosir'),
            tender: btn.attr('data-tender') !== undefined ? btn.attr('data-tender') : btn.data('tender'),
            stock: btn.attr('data-stock') !== undefined ? btn.attr('data-stock') : btn.data('stock')
        };
        addToCart(p);
    });

    function renderCart() {
        let totalQty = 0;
        let html = '';
        let ppnList = window.ORDER_PPN_LIST || [];

        if (cart.length === 0) {
            html = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 opacity-50 text-secondary"></i>
                    <div class="fw-bold mt-2">Keranjang masih kosong</div>
                    <div class="text-body-secondary small">Klik tombol <strong>+ Keranjang</strong> pada produk di katalog.</div>
                </div>
            `;
            $('#btn_open_checkout').prop('disabled', true);
            $('#btn_simpan_pesanan').prop('disabled', true);
        } else {
            $('#btn_open_checkout').prop('disabled', false);
            $('#btn_simpan_pesanan').prop('disabled', false);

            cart.forEach((item, index) => {
                calculateItemValues(item);
                totalQty += item.qty;
                let itemPhoto = item.photo_url || PLACEHOLDER_IMG;
                let diskonMode = item.diskon_tipe || 'Rp';
                let diskonValDisplay = diskonMode === '%' ? (item.diskon_nilai || 0) : (item.diskon_nilai > 0 ? formatThousands(item.diskon_nilai) : '');

                let ppnOptionsHtml = `<option value="" data-tarif="0" ${!item.ppn_pajak_id && (item.tarif_ppn || 0) === 0 ? 'selected' : ''}>Non PPN (0%)</option>`;
                ppnList.forEach(p => {
                    let pTarif = parseFloat(p.tarif) || 0;
                    let isSelected = (item.ppn_pajak_id == p.id) || (!item.ppn_pajak_id && item.tarif_ppn > 0 && item.tarif_ppn === pTarif);
                    ppnOptionsHtml += `<option value="${p.id}" data-tarif="${pTarif}" ${isSelected ? 'selected' : ''}>${p.nama_pajak} (${pTarif}%)</option>`;
                });

                html += `
                    <div class="tkpd-cart-item mb-2.5 rounded-3 border bg-body p-2.5" data-index="${index}" style="display: flex; flex-direction: column; gap: 8px;">
                        <!-- Baris 1: Gambar, Nama Produk, Harga, Tombol Hapus -->
                        <div class="d-flex align-items-center gap-2">
                            <img src="${itemPhoto}" class="rounded-2 border" style="width: 42px; height: 42px; object-fit: cover; flex-shrink: 0;" onerror="window.handleImgError(this)">
                            <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                                <div class="fw-bold text-truncate text-body-emphasis" title="${item.nama}" style="font-size: 0.85rem;">${item.nama}</div>
                                <div class="text-primary fw-bold font-monospace" style="font-size: 0.82rem;">${formatRp(item.harga_satuan)}</div>
                            </div>
                            <button type="button" class="btn btn-link text-danger p-0 ms-1 btn-cart-remove" data-index="${index}" title="Hapus item" style="font-size: 1.1rem; line-height: 1;">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>

                        <!-- Baris 2: Qty Stepper & Diskon Input -->
                        <div class="d-flex align-items-center justify-content-between gap-2 pt-1.5 border-top border-secondary-subtle">
                            <!-- Qty Stepper -->
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="text-muted" style="font-size: 0.72rem;">Qty:</span>
                                <div class="tkpd-stepper d-inline-flex align-items-center border rounded">
                                    <button type="button" class="btn btn-xs py-0 px-2 btn-cart-dec" data-index="${index}">-</button>
                                    <input type="text" class="form-control form-control-xs text-center border-0 p-0 font-monospace cart-qty-input" data-index="${index}" value="${item.qty}" style="width: 36px; height: 24px; font-size: 0.8rem;">
                                    <button type="button" class="btn btn-xs py-0 px-2 btn-cart-inc" data-index="${index}">+</button>
                                </div>
                            </div>

                            <!-- Diskon Group -->
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted" style="font-size: 0.72rem;">Diskon:</span>
                                <div class="input-group input-group-sm" style="width: 125px;">
                                    <div class="btn-group btn-group-sm flex-shrink-0" role="group">
                                        <button type="button" class="btn btn-xs py-0 px-1.5 btn-cart-diskon-mode ${diskonMode === 'Rp' ? 'btn-primary active text-white' : 'btn-outline-secondary'}" data-index="${index}" data-mode="Rp" style="font-size: 0.68rem; height: 24px;">Rp</button>
                                        <button type="button" class="btn btn-xs py-0 px-1.5 btn-cart-diskon-mode ${diskonMode === '%' ? 'btn-primary active text-white' : 'btn-outline-secondary'}" data-index="${index}" data-mode="%" style="font-size: 0.68rem; height: 24px;">%</button>
                                    </div>
                                    <input type="text" class="form-control form-control-xs text-end font-monospace p-1 cart-diskon-input" data-index="${index}" value="${diskonValDisplay}" placeholder="0" style="font-size: 0.72rem; height: 24px;">
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: PPN Select & Subtotal -->
                        <div class="d-flex align-items-center justify-content-between gap-2 pt-1 border-top border-secondary-subtle">
                            <!-- PPN Select -->
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted" style="font-size: 0.72rem;">PPN:</span>
                                <select class="form-select form-select-sm py-0 px-1.5 cart-ppn-select font-monospace" data-index="${index}" style="font-size: 0.72rem; height: 26px; width: auto; min-width: 110px;">
                                    ${ppnOptionsHtml}
                                </select>
                            </div>

                            <!-- Subtotal Item -->
                            <div class="text-end">
                                <div class="d-flex align-items-center gap-1 justify-content-end">
                                    <span class="text-muted" style="font-size: 0.7rem;">Subtotal:</span>
                                    <span class="fw-bold text-body-emphasis font-monospace item-subtotal-display" data-index="${index}" style="font-size: 0.82rem;">
                                        ${formatRp(item.subtotal)}
                                    </span>
                                </div>
                                <div class="text-info fw-normal item-ppn-display" data-index="${index}" style="font-size: 0.68rem; ${item.nilai_ppn > 0 ? '' : 'display:none;'}">
                                    ${item.nilai_ppn > 0 ? `(+PPN ${formatRp(item.nilai_ppn)})` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        $('#cart_items_list').html(html);
        $('#cart_badge_count').text(cart.length);
        $('#cart_badge_header').text(totalQty);
        $('#cart_badge_floating').text(totalQty);

        calculateCartTotals();
        saveCartToStorage();
    }

    $(document).on('click', '[data-bs-target="#offcanvasCart"], #btn_floating_cart', function (e) {
        let el = document.getElementById('offcanvasCart');
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
            let offcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
            offcanvas.show();
        }
    });

    // Sembunyikan floating cart button saat drawer terbuka, tampilkan kembali saat ditutup
    let offcanvasCartEl = document.getElementById('offcanvasCart');
    if (offcanvasCartEl) {
        offcanvasCartEl.addEventListener('show.bs.offcanvas', function () {
            $('#btn_floating_cart').hide();
        });
        offcanvasCartEl.addEventListener('hidden.bs.offcanvas', function () {
            $('#btn_floating_cart').show();
        });
    }

    function calculateCartTotals() {
        let totalGross = 0;
        let totalDiskon = 0;
        let totalPpn = 0;

        cart.forEach(item => {
            calculateItemValues(item);
            totalGross += (item.gross !== undefined ? item.gross : Math.round((parseFloat(item.harga_satuan) || 0) * (parseFloat(item.qty) || 1)));
            totalDiskon += (item.diskon_nominal || 0);
            totalPpn += (item.nilai_ppn || 0);
        });

        let afterDiskon = Math.max(0, totalGross - totalDiskon);
        let biayaPengiriman = Math.max(0, unformatThousands($('#cart_biaya_pengiriman').val()));
        let grandTotal = Math.max(0, afterDiskon + totalPpn + biayaPengiriman);

        // Down Payment (DP / Uang Muka) Calculation
        let useDp = $('#cart_use_dp').is(':checked');
        let dpMode = $('input[name="dp_mode"]:checked').val() || 'Nominal';
        let dpVal = (dpMode === 'Persen')
            ? (parseFloat($('#cart_dp_val').val()) || 0)
            : unformatThousands($('#cart_dp_val').val());
        let dpNominal = 0;

        if (useDp && grandTotal > 0) {
            if (dpMode === 'Persen') {
                if (dpVal > 100) {
                    dpVal = 100;
                    $('#cart_dp_val').val(100);
                } else if (dpVal < 0) {
                    dpVal = 0;
                    $('#cart_dp_val').val(0);
                }
                dpNominal = Math.round((grandTotal * dpVal) / 100);
                $('#cart_dp_suffix').text('%');
                $('#cart_summary_dp_label').text(dpVal > 0 ? `(${dpVal}%)` : '');
            } else {
                if (dpVal < 0) dpVal = 0;
                dpNominal = Math.min(grandTotal, dpVal);
                $('#cart_dp_suffix').text('Rp');
                $('#cart_summary_dp_label').text('');
            }

            if (dpNominal > 0) {
                let sisaTagihan = Math.max(0, grandTotal - dpNominal);
                $('#cart_summary_dp_section').show();
                $('#cart_summary_dp_nominal').text(formatRp(dpNominal));
                $('#cart_summary_sisa_tagihan').text(formatRp(sisaTagihan));
                $('#cart_sisa_tagihan_preview').text(formatRp(sisaTagihan));
            } else {
                $('#cart_summary_dp_section').hide();
                $('#cart_summary_dp_nominal').text('Rp 0');
                $('#cart_summary_sisa_tagihan').text(formatRp(grandTotal));
                $('#cart_sisa_tagihan_preview').text(formatRp(grandTotal));
            }
        } else {
            $('#cart_summary_dp_section').hide();
            $('#cart_summary_dp_nominal').text('Rp 0');
            $('#cart_summary_sisa_tagihan').text(formatRp(grandTotal));
            $('#cart_sisa_tagihan_preview').text(formatRp(grandTotal));
        }

        // Summary Card in Cart Drawer
        $('#cart_summary_subtotal').text(formatRp(totalGross));
        $('#cart_summary_diskon').text('- ' + formatRp(totalDiskon));
        $('#cart_summary_pajak').text('+ ' + formatRp(totalPpn));
        $('#cart_summary_ongkir').text('+ ' + formatRp(biayaPengiriman));
        $('#cart_summary_total').text(formatRp(grandTotal));
        $('#cart_floating_nominal').text(formatRp(grandTotal));

        // Summary in Checkout Modal
        $('#checkout_summary_items').text(cart.length + ' Item');
        $('#checkout_summary_subtotal').text(formatRp(totalGross));
        $('#checkout_summary_diskon').text('- ' + formatRp(totalDiskon));
        $('#checkout_summary_pajak').text('+ ' + formatRp(totalPpn));
        $('#checkout_summary_ongkir').text('+ ' + formatRp(biayaPengiriman));
        $('#checkout_summary_grand_total').text(formatRp(grandTotal));
        if (useDp && dpNominal > 0) {
            $('#checkout_summary_dp_wrap').show();
            $('#checkout_summary_dp_pct').text(dpMode === 'Persen' ? `(${dpVal}%)` : '');
            $('#checkout_summary_dp_nominal').text(formatRp(dpNominal));
            $('#checkout_summary_sisa_tagihan').text(formatRp(Math.max(0, grandTotal - dpNominal)));
        } else {
            $('#checkout_summary_dp_wrap').hide();
        }

        let sisaFinal = useDp ? Math.max(0, grandTotal - dpNominal) : grandTotal;
        window._currentCartCalculations = {
            subtotal:               totalGross,
            diskon_tipe:            'Nominal',
            diskon_nilai:           totalDiskon,
            diskon_nominal:         totalDiskon,
            ppn_pajak_id:           null,
            tipe_ppn:               totalPpn > 0 ? 'Item' : 'None',
            tarif_ppn:              totalGross > 0 ? Math.round((totalPpn / totalGross) * 100) : 0,
            nilai_ppn:              totalPpn,
            pajak_tipe:             totalPpn > 0 ? 'PPN_Exclude' : 'None',
            pajak_persen:           totalGross > 0 ? Math.round((totalPpn / totalGross) * 100) : 0,
            pajak_nominal:          totalPpn,
            pph_pajak_id:           null,
            tipe_pph:               'Non-PPh',
            tarif_pph:              0,
            nilai_pph:              0,
            biaya_pengiriman:       biayaPengiriman,
            total:                  grandTotal,
            grand_total:            grandTotal,
            customer_id:            $('#cart_customer_id').val() || null,
            nama_customer_walkin:   $('#cart_customer_id').val() ? null : ($('#cart_nama_walkin').val() || 'Pelanggan Umum'),
            telepon_customer_walkin:$('#cart_telepon_walkin').val() || '',
            payment_term_id:        $('#cart_payment_term_id').val() || null,
            use_dp:                 useDp,
            tipe_dp:                dpMode,
            dp_persen:              dpMode === 'Persen' ? dpVal : 0,
            dp_nominal:             dpNominal,
            sisa_tagihan:           sisaFinal
        };

        saveCartToStorage();
    }

    // Helper update realtime tampilan 1 kartu barang
    function updateItemCardRealtime(idx) {
        if (!cart[idx]) return;
        let item = cart[idx];
        calculateItemValues(item);

        let $subtotalSpan = $(`.item-subtotal-display[data-index="${idx}"]`);
        let $ppnDiv = $(`.item-ppn-display[data-index="${idx}"]`);

        if ($subtotalSpan.length) {
            $subtotalSpan.text(formatRp(item.subtotal));
        }
        if ($ppnDiv.length) {
            if (item.nilai_ppn > 0) {
                $ppnDiv.text(`(+PPN ${formatRp(item.nilai_ppn)})`).show();
            } else {
                $ppnDiv.hide().text('');
            }
        }
        calculateCartTotals();
    }

    // Stepper Handlers (Realtime)
    $(document).on('click', '.btn-cart-inc', function () {
        let idx = $(this).data('index');
        let stock = Number(cart[idx].stock) || 0;

        if (stock > 0 && cart[idx].qty >= stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Kuantitas pesanan tidak dapat melebihi sisa persediaan yang tersedia (${stock} ${cart[idx].satuan || 'Pcs'}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        cart[idx].qty += 1;
        $(`.cart-qty-input[data-index="${idx}"]`).val(cart[idx].qty);
        updateItemCardRealtime(idx);
    });

    $(document).on('click', '.btn-cart-dec', function () {
        let idx = $(this).data('index');
        if (cart[idx].qty > 1) {
            cart[idx].qty -= 1;
            $(`.cart-qty-input[data-index="${idx}"]`).val(cart[idx].qty);
            updateItemCardRealtime(idx);
        } else {
            cart.splice(idx, 1);
            renderCart();
        }
    });

    // Realtime Qty Input saat mengetik
    $(document).on('input keyup', '.cart-qty-input', function () {
        let idx = $(this).data('index');
        if (!cart[idx]) return;
        let raw = $(this).val().replace(/[^0-9]/g, '');
        let val = raw !== '' ? parseInt(raw, 10) : 1;
        let stock = Number(cart[idx].stock) || 0;

        if (stock > 0 && val > stock) {
            val = stock;
            $(this).val(stock);
        }

        cart[idx].qty = val > 0 ? val : 1;
        updateItemCardRealtime(idx);
    });

    $(document).on('change blur', '.cart-qty-input', function () {
        let idx = $(this).data('index');
        if (!cart[idx]) return;
        let raw = $(this).val().replace(/[^0-9]/g, '');
        let val = raw !== '' ? parseInt(raw, 10) : 1;
        let stock = Number(cart[idx].stock) || 0;

        if (val <= 0) val = 1;
        if (stock > 0 && val > stock) {
            val = stock;
            Swal.fire({
                icon: 'warning',
                title: 'Penyesuaian Kuantitas',
                text: `Kuantitas pesanan telah disesuaikan dengan batas maksimum ketersediaan stok (${stock} ${cart[idx].satuan || 'Pcs'}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
        }

        cart[idx].qty = val;
        $(this).val(val);
        updateItemCardRealtime(idx);
    });

    $(document).on('click', '.btn-cart-remove', function () {
        let idx = $(this).data('index');
        cart.splice(idx, 1);
        renderCart();
    });

    // Per-Item Diskon Mode Toggle (Rp vs %)
    $(document).on('click', '.btn-cart-diskon-mode', function () {
        let idx = $(this).data('index');
        let mode = $(this).data('mode');
        if (cart[idx]) {
            cart[idx].diskon_tipe = mode;
            cart[idx].diskon_nilai = 0;
            renderCart();
        }
    });

    // Per-Item Diskon Input (Realtime)
    $(document).on('input keyup change', '.cart-diskon-input', function () {
        let idx = $(this).data('index');
        if (!cart[idx]) return;
        let mode = cart[idx].diskon_tipe || 'Rp';
        let gross = (cart[idx].harga_satuan || 0) * (cart[idx].qty || 1);

        if (mode === 'Rp') {
            let digits = $(this).val().replace(/[^0-9]/g, '');
            let val = digits !== '' ? parseInt(digits, 10) : 0;
            if (val > gross) val = gross;
            cart[idx].diskon_nilai = val;
            $(this).val(val > 0 ? val.toLocaleString('id-ID') : '');
        } else {
            let val = parseFloat($(this).val()) || 0;
            if (val > 100) val = 100;
            if (val < 0) val = 0;
            cart[idx].diskon_nilai = val;
        }
        updateItemCardRealtime(idx);
    });

    // Per-Item PPN Select (Realtime)
    $(document).on('change', '.cart-ppn-select', function () {
        let idx = $(this).data('index');
        if (!cart[idx]) return;
        let opt = $(this).find('option:selected');
        let ppnId = opt.val() || null;
        let tarif = parseFloat(opt.data('tarif')) || 0;
        cart[idx].ppn_pajak_id = ppnId;
        cart[idx].tarif_ppn = tarif;
        updateItemCardRealtime(idx);
    });

    function clearCartFull() {
        cart = [];
        try {
            localStorage.removeItem(CART_STORAGE_KEY);
        } catch (e) {}

        // Reset semua field input ongkir, customer, dp, dan catatan
        $('#cart_biaya_pengiriman').val('0');
        $('#cart_customer_id').val('').trigger('change');
        $('#cart_walkin_wrap').show();
        $('#cart_nama_walkin').val('Pelanggan Umum');
        $('#cart_telepon_walkin').val('');
        $('#cart_alamat_pengiriman').val('');
        $('#cart_payment_method_id').val('');
        $('#cart_payment_term_id').val('');
        $('#cart_catatan').val('');
        $('#checkout_catatan').val('');
        $('#cart_use_dp').prop('checked', false).trigger('change');
        $('#cart_dp_container').hide();
        $('#cart_dp_val').val('0');

        renderCart();
    }

    $('#btn_clear_cart').on('click', function () {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: 'Semua barang di keranjang akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then(res => {
            if (res.isConfirmed) {
                clearCartFull();
            }
        });
    });

    $(document).on('input keyup change', '#cart_biaya_pengiriman', function () {
        let digits = $(this).val().replace(/[^0-9]/g, '');
        if (digits !== '') {
            $(this).val(parseInt(digits, 10).toLocaleString('id-ID'));
        }
        calculateCartTotals();
    }).on('blur', '#cart_biaya_pengiriman', function () {
        if ($(this).val().trim() === '') {
            $(this).val('0');
            calculateCartTotals();
        }
    });

    $(document).on('input keyup change', '#cart_dp_val', function () {
        let dpMode = $('input[name="dp_mode"]:checked').val() || 'Nominal';
        if (dpMode === 'Nominal') {
            let digits = $(this).val().replace(/[^0-9]/g, '');
            if (digits !== '') {
                $(this).val(parseInt(digits, 10).toLocaleString('id-ID'));
            }
        }
        calculateCartTotals();
    }).on('blur', '#cart_dp_val', function () {
        let dpMode = $('input[name="dp_mode"]:checked').val() || 'Nominal';
        let calc = window._currentCartCalculations;
        let grandTotal = calc ? (calc.grand_total || 0) : 0;
        if ($(this).val().trim() === '') {
            $(this).val('0');
        } else if (dpMode === 'Nominal') {
            let val = unformatThousands($(this).val());
            if (val > grandTotal && grandTotal > 0) {
                $(this).val(formatThousands(grandTotal));
            }
        } else if (dpMode === 'Persen') {
            let val = parseFloat($(this).val()) || 0;
            if (val > 100) $(this).val(100);
            else if (val < 0) $(this).val(0);
        }
        calculateCartTotals();
    });

    // Customer & Walk-in listeners in Cart Drawer
    $('#cart_customer_id').on('change select2:select', function () {
        let custId = $(this).val();
        if (custId) {
            let opt = $(this).find('option:selected');
            $('#cart_walkin_wrap').hide();
            let alamat = opt.data('alamat') || opt.attr('data-alamat') || '';
            let telepon = opt.data('telepon') || opt.attr('data-telepon') || '';
            // Otomatis isi alamat di Keranjang Pesanan & Modal Checkout
            $('#cart_alamat_pengiriman').val(alamat);
            $('#checkout_customer_select').val(custId);
            $('#checkout_walkin_wrap').hide();
            $('#checkout_telepon').val(telepon);
            $('#checkout_alamat').val(alamat);
        } else {
            $('#cart_walkin_wrap').show();
            $('#cart_alamat_pengiriman').val('');
            $('#checkout_customer_select').val('');
            $('#checkout_walkin_wrap').show();
            $('#checkout_nama_walkin').val($('#cart_nama_walkin').val() || 'Pelanggan Umum');
            $('#checkout_telepon').val($('#cart_telepon_walkin').val() || '');
            $('#checkout_alamat').val('');
        }

        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $('#cart_nama_walkin, #cart_telepon_walkin, #cart_payment_term_id, #cart_payment_method_id, #cart_alamat_pengiriman').on('input change', function () {
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    // Sinkronisasi Metode Pembayaran & Alamat antara Keranjang Pesanan dan Modal Checkout
    $('#cart_payment_method_id').on('change select2:select', function () {
        let val = $(this).val();
        if ($('#checkout_payment_method_id').val() !== val) {
            $('#checkout_payment_method_id').val(val).trigger('change.select2');
        }
    });
    $('#checkout_payment_method_id').on('change select2:select', function () {
        let val = $(this).val();
        if ($('#cart_payment_method_id').val() !== val) {
            $('#cart_payment_method_id').val(val).trigger('change.select2');
        }
    });

    $('#cart_payment_term_id').on('change select2:select', function () {
        let val = $(this).val();
        if ($('#checkout_payment_term_id').val() !== val) {
            $('#checkout_payment_term_id').val(val).trigger('change.select2');
        }
    });
    $('#checkout_payment_term_id').on('change select2:select', function () {
        let val = $(this).val();
        if ($('#cart_payment_term_id').val() !== val) {
            $('#cart_payment_term_id').val(val).trigger('change.select2');
        }
    });

    $('#cart_alamat_pengiriman').on('input change', function () {
        $('#checkout_alamat').val($(this).val());
    });
    $('#checkout_alamat').on('input change', function () {
        $('#cart_alamat_pengiriman').val($(this).val());
    });

    $('#cart_catatan').on('input change', function () {
        $('#checkout_catatan').val($(this).val());
    });
    $('#checkout_catatan').on('input change', function () {
        $('#cart_catatan').val($(this).val());
    });

    $('#cart_syarat_ketentuan').on('input change', function () {
        $('#checkout_syarat_ketentuan').val($(this).val());
    });
    $('#checkout_syarat_ketentuan').on('input change', function () {
        $('#cart_syarat_ketentuan').val($(this).val());
    });



    // Down Payment (DP) Toggle Switch & Listeners
    $('#cart_use_dp').on('change', function () {
        if ($(this).is(':checked')) {
            $('#cart_dp_container').slideDown(150);
        } else {
            $('#cart_dp_container').slideUp(150);
            $('#cart_dp_val').val('0');
        }
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $('input[name="dp_mode"]').on('change', function () {
        let mode = $(this).val();
        $('#cart_dp_suffix').text(mode === 'Persen' ? '%' : 'Rp');
        $('#cart_dp_val').val('0');
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    // ===== SIMPAN PESANAN LANGSUNG (Tanpa Modal Checkout) =====
    $(document).on('click', '#btn_simpan_pesanan', function () {
        if (cart.length === 0) return;

        // Validasi harga Rp 0
        let zeroPriceItems = cart.filter(c => Number(c.harga_satuan) <= 0);
        if (zeroPriceItems.length > 0) {
            let names = zeroPriceItems.map(c => `• <b>${c.nama}</b>`).join('<br>');
            Swal.fire({
                icon: 'warning',
                title: 'Harga Produk Belum Diatur',
                html: `Terdapat produk dengan harga <b>Rp 0</b>:<br><div class="text-danger my-2 text-start p-2 bg-light border border-danger-subtle rounded small">${names}</div>Silakan atur harga jual produk terlebih dahulu.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let calc = window._currentCartCalculations;
        if (!calc || Number(calc.subtotal) <= 0) {
            Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Tambahkan produk ke keranjang terlebih dahulu.', confirmButtonColor: '#f59e0b' });
            return;
        }

        // Konfirmasi simpan
        Swal.fire({
            icon: 'question',
            title: 'Simpan Pesanan?',
            html: `<div class="text-start small">
                     <div class="d-flex justify-content-between"><span>Subtotal</span><strong>${formatRp(calc.subtotal)}</strong></div>
                     ${calc.diskon_nominal > 0 ? `<div class="d-flex justify-content-between text-danger"><span>Diskon</span><strong>- ${formatRp(calc.diskon_nominal)}</strong></div>` : ''}
                     ${calc.pajak_nominal > 0 ? `<div class="d-flex justify-content-between text-info"><span>Pajak (${calc.pajak_persen}%)</span><strong>+ ${formatRp(calc.pajak_nominal)}</strong></div>` : ''}
                     ${calc.biaya_pengiriman > 0 ? `<div class="d-flex justify-content-between text-primary"><span>Ongkir</span><strong>+ ${formatRp(calc.biaya_pengiriman)}</strong></div>` : ''}
                     <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold fs-6"><span>Grand Total</span><span class="text-success">${formatRp(calc.total)}</span></div>
                   </div>`,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-floppy2-fill me-1"></i> Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            let customerId = $('#cart_customer_id').val();
            let walkinName = $('#cart_nama_walkin').val() || 'Pelanggan Umum';
            let walkinPhone = $('#cart_telepon_walkin').val() || '';
            let alamatKirim = $('#cart_alamat_pengiriman').val() || '';

            let payload = {
                pricing_tier:            currentPricingTier || 'Retail',
                customer_id:             customerId || null,
                nama_customer_walkin:    customerId ? null : walkinName,
                telepon_customer_walkin: customerId ? null : walkinPhone,
                alamat_pengiriman:       alamatKirim || null,
                payment_method_id:       $('#cart_payment_method_id').val() || null,
                payment_term_id:         $('#cart_payment_term_id').val() || null,
                subtotal:                calc.subtotal,
                diskon_tipe:             calc.diskon_tipe,
                diskon_nilai:            calc.diskon_nilai,
                diskon_nominal:          calc.diskon_nominal,
                ppn_pajak_id:            calc.ppn_pajak_id || null,
                tipe_ppn:                calc.tipe_ppn || 'None',
                tarif_ppn:               calc.tarif_ppn || 0,
                nilai_ppn:               calc.nilai_ppn || 0,
                pajak_tipe:              calc.pajak_tipe,
                pajak_persen:            calc.pajak_persen,
                pajak_nominal:           calc.pajak_nominal,
                pph_pajak_id:            calc.pph_pajak_id || null,
                tipe_pph:                calc.tipe_pph || 'Non-PPh',
                tarif_pph:               calc.tarif_pph || 0,
                nilai_pph:               calc.nilai_pph || 0,
                biaya_pengiriman:        calc.biaya_pengiriman || 0,
                total:                   calc.total,
                grand_total:             calc.grand_total || calc.total,
                tipe_dp:                 calc.use_dp ? calc.tipe_dp : null,
                dp_persen:               calc.use_dp ? calc.dp_persen : 0,
                dp_nominal:              calc.use_dp ? calc.dp_nominal : 0,
                sisa_tagihan:            calc.use_dp ? calc.sisa_tagihan : calc.total,
                catatan:                 $('#cart_catatan').val() || $('#checkout_catatan').val() || null,
                syarat_ketentuan:        $('#cart_syarat_ketentuan').val() || $('#checkout_syarat_ketentuan').val() || "1. Barang sesuai dengan spesifikasi pesanan.\n2. Pembayaran sah setelah dana efektif masuk ke rekening.",
                status:                  'Confirmed',
                items: cart.map(c => ({
                    barang_id:      c.id,
                    qty:            c.qty,
                    harga_satuan:   c.harga_satuan,
                    diskon_persen:  c.diskon_persen || 0,
                    diskon_nominal: c.diskon_nominal || 0,
                    ppn_pajak_id:   c.ppn_pajak_id || null,
                    tarif_ppn:      c.tarif_ppn || 0,
                    nilai_ppn:      c.nilai_ppn || 0,
                    subtotal:       c.subtotal,
                    catatan:        c.catatan || ''
                }))
            };

            // Disable button & tampilkan loading
            $('#btn_simpan_pesanan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');
            $('#btn_open_checkout').prop('disabled', true);

            $.ajax({
                url: '{{ route("marketing.sales-order.store") }}',
                method: 'POST',
                data: JSON.stringify(payload),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    if (res.success) {
                        // Kosongkan keranjang & reset seluruh field form (ongkir, dp, dsb)
                        clearCartFull();

                        // Tutup drawer
                        let offcanvasEl = document.getElementById('offcanvasCart');
                        if (offcanvasEl) bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Sales Order Berhasil Diterbitkan',
                            html: `
                                <div class="my-3 p-3 rounded-3 text-center border" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
                                    <div class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Nomor Dokumen Penjualan (SO)</div>
                                    <div class="font-monospace fw-bold text-success fs-4 mt-1">${res.order?.nomor_so ?? ''}</div>
                                </div>
                                <div class="text-body-secondary small">
                                    Dokumen pesanan penjualan telah berhasil diverifikasi dan tersimpan resmi ke dalam sistem ERP.
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: '<i class="bi bi-file-earmark-text me-1"></i> Lihat Dokumen SO',
                            cancelButtonText: 'Selesai'
                        }).then((action) => {
                            if (action.isConfirmed && res.order?.id) {
                                showOrderDetail(res.order.id);
                            }
                            // Reload tabel riwayat jika ada
                            if (typeof tableOrders !== 'undefined' && tableOrders) {
                                tableOrders.ajax.reload(null, false);
                            }
                        });

                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal Simpan', text: res.message || 'Terjadi kesalahan.', confirmButtonColor: '#ef4444' });
                        $('#btn_simpan_pesanan').prop('disabled', false).html('<i class="bi bi-floppy2-fill fs-5"></i> <span>Simpan Pesanan</span>');
                        $('#btn_open_checkout').prop('disabled', false);
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menghubungi server.';
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
                    $('#btn_simpan_pesanan').prop('disabled', false).html('<i class="bi bi-floppy2-fill fs-5"></i> <span>Simpan Pesanan</span>');
                    $('#btn_open_checkout').prop('disabled', false);
                }
            });
        });
    });

    // ===== CHECKOUT PROCESS (buka modal review) =====
    $('#btn_open_checkout').on('click', function () {
        if (cart.length === 0) return;

        // Validasi Harga Produk tidak boleh Rp 0
        let zeroPriceItems = cart.filter(c => Number(c.harga_satuan) <= 0);
        if (zeroPriceItems.length > 0) {
            let names = zeroPriceItems.map(c => `• <b>${c.nama}</b>`).join('<br>');
            Swal.fire({
                icon: 'warning',
                title: 'Harga Produk Belum Diatur',
                html: `Terdapat produk dengan harga <b>Rp 0</b> di dalam keranjang:<br><div class="text-danger my-2 text-start p-2 bg-light border border-danger-subtle rounded small">${names}</div>Pesanan tidak dapat diproses jika harga produk masih Rp 0. Silakan atur harga jual master barang atau pilih tingkat harga yang valid.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let calc = window._currentCartCalculations;
        if (!calc || Number(calc.subtotal) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Subtotal Tidak Valid',
                text: 'Total nilai pesanan harus lebih dari Rp 0 sebelum melanjutkan checkout.',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        // Tutup offcanvas drawer cart jika terbuka
        const offcanvasEl = document.getElementById('offcanvasCart');
        if (offcanvasEl) {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvasInstance) {
                offcanvasInstance.hide();
            }
        }

        // calc already declared above
        let diskonLabel = calc.diskon_tipe === 'Persen'
            ? `(${calc.diskon_nilai}%)`
            : '';
        let pajakLabel = calc.pajak_persen > 0 ? `(${calc.pajak_persen}%)` : '';

        // Sinkronkan pilihan Customer, Alamat, Tenor & Metode Pembayaran dari Drawer ke Modal Checkout
        let custId = $('#cart_customer_id').val();
        $('#checkout_customer_select').val(custId).trigger('change.select2');
        if (custId) {
            let opt = $('#cart_customer_id').find('option:selected');
            $('#checkout_walkin_wrap').hide();
            $('#checkout_telepon').val(opt.data('telepon') || '');
            let alamat = $('#cart_alamat_pengiriman').val() || opt.data('alamat') || '';
            $('#checkout_alamat').val(alamat);
        } else {
            $('#checkout_walkin_wrap').show();
            $('#checkout_nama_walkin').val($('#cart_nama_walkin').val() || 'Pelanggan Umum');
            $('#checkout_telepon').val($('#cart_telepon_walkin').val() || '');
            $('#checkout_alamat').val($('#cart_alamat_pengiriman').val() || '');
        }

        let termId = $('#cart_payment_term_id').val();
        if (termId) {
            $('#checkout_payment_term_id').val(termId).trigger('change.select2');
        } else {
            $('#checkout_payment_term_id').val('').trigger('change.select2');
        }
        let methodId = $('#cart_payment_method_id').val();
        if (methodId) {
            $('#checkout_payment_method_id').val(methodId).trigger('change.select2');
        } else {
            $('#checkout_payment_method_id').val('').trigger('change.select2');
        }



        $('#checkout_summary_items').text(cart.length + ' Macam Produk');
        $('#checkout_summary_subtotal').text(formatRp(calc.subtotal));
        $('#checkout_summary_diskon').text('- ' + formatRp(calc.diskon_nominal) + (diskonLabel ? ' ' + diskonLabel : ''));
        // DPP: Dasar Pengenaan Pajak
        let dppVal = Math.max(0, calc.subtotal - calc.diskon_nominal);
        if (calc.pajak_persen > 0 || (calc.tarif_pph && calc.tarif_pph > 0)) {
            $('#checkout_summary_dpp').text(formatRp(dppVal));
            $('#checkout_summary_dpp_row').css('display', 'flex');
        } else {
            $('#checkout_summary_dpp_row').hide();
        }
        $('#checkout_summary_pajak').text('+ ' + formatRp(calc.pajak_nominal));
        $('#checkout_summary_pajak_pct').text(pajakLabel);

        // PPh di Modal Checkout
        if (calc.tarif_pph > 0 && calc.nilai_pph > 0) {
            let pphLabel = `(${calc.tarif_pph}%)`;
            $('#checkout_summary_pph_pct').text(pphLabel);
            $('#checkout_summary_pph').text('- ' + formatRp(calc.nilai_pph));
            $('#checkout_summary_pph_row').css('display', 'flex').removeClass('d-none');
        } else {
            $('#checkout_summary_pph_row').hide();
        }

        $('#checkout_summary_ongkir').text('+ ' + formatRp(calc.biaya_pengiriman || 0));
        $('#checkout_summary_grand_total').text(formatRp(calc.total));

        // Rincian DP di Modal Checkout jika diaktifkan
        if (calc.use_dp && calc.dp_nominal > 0) {
            $('#checkout_summary_dp_wrap').show();
            let dpLabel = calc.tipe_dp === 'Persen' ? `(${calc.dp_persen}%)` : '';
            $('#checkout_summary_dp_pct').text(dpLabel);
            $('#checkout_summary_dp_nominal').text(formatRp(calc.dp_nominal));
            $('#checkout_summary_sisa_tagihan').text(formatRp(calc.sisa_tagihan));
        } else {
            $('#checkout_summary_dp_wrap').hide();
        }

        showModal('#modalCheckoutOrder');
    });

    $('#checkout_customer_select').on('change', function () {
        let custId = $(this).val();
        if ($('#cart_customer_id').val() !== custId) {
            $('#cart_customer_id').val(custId).trigger('change.select2');
        }
        if (custId) {
            let opt = $(this).find('option:selected');
            $('#checkout_walkin_wrap').hide();
            $('#cart_walkin_wrap').hide();
            $('#checkout_telepon').val(opt.data('telepon') || '');
            $('#checkout_alamat').val(opt.data('alamat') || '');
        } else {
            $('#checkout_walkin_wrap').show();
            $('#cart_walkin_wrap').show();
            $('#checkout_telepon').val('');
            $('#checkout_alamat').val('');
        }
    });

    $('#formCheckoutOrder').on('submit', function (e) {
        e.preventDefault();

        let calc = window._currentCartCalculations;
        let customerId = $('#checkout_customer_select').val();
        let walkinName = $('#checkout_nama_walkin').val() ? $('#checkout_nama_walkin').val().trim() : '';
        let paymentTermId = $('#checkout_payment_term_id').val() || $('#cart_payment_term_id').val() || null;

        // Validasi Harga Produk tidak boleh Rp 0
        let zeroItem = cart.find(c => Number(c.harga_satuan) <= 0);
        if (zeroItem || !calc || Number(calc.subtotal) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Harga Produk Belum Diatur',
                text: 'Terdapat produk dengan harga Rp 0 di keranjang. Setiap produk wajib memiliki harga jual lebih dari Rp 0 sebelum pesanan diproses.',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return false;
        }

        // Validasi Wajib Pelanggan / Customer
        if (!customerId && !walkinName) {
            Swal.fire({
                icon: 'warning',
                title: 'Pelanggan Wajib Diisi',
                text: 'Silakan pilih pelanggan terdaftar atau isi nama pelanggan terlebih dahulu sebelum memproses pesanan.',
                confirmButtonColor: '#2563eb'
            });
            return false;
        }

        let orderData = {
            pricing_tier: currentPricingTier,
            customer_id: customerId || null,
            nama_customer_walkin: customerId ? null : (walkinName || 'Pelanggan Umum'),
            telepon_customer_walkin: $('#checkout_telepon').val(),
            alamat_pengiriman: $('#checkout_alamat').val(),
            payment_method_id: $('#checkout_payment_method_id').val() || null,
            payment_term_id: paymentTermId,
            catatan: $('#checkout_catatan').val() || $('#cart_catatan').val() || null,
            syarat_ketentuan: $('#checkout_syarat_ketentuan').val() || $('#cart_syarat_ketentuan').val() || "1. Barang sesuai dengan spesifikasi pesanan.\n2. Pembayaran sah setelah dana efektif masuk ke rekening.",
            subtotal:               calc.subtotal,
            diskon_tipe:            calc.diskon_tipe,
            diskon_nilai:           calc.diskon_nilai,
            diskon_nominal:         calc.diskon_nominal,
            ppn_pajak_id:           calc.ppn_pajak_id || null,
            tipe_ppn:               calc.tipe_ppn || 'None',
            tarif_ppn:              calc.tarif_ppn || 0,
            nilai_ppn:              calc.nilai_ppn || 0,
            pajak_tipe:             calc.pajak_tipe || 'None',
            pajak_persen:           calc.pajak_persen || 0,
            pajak_nominal:          calc.pajak_nominal || 0,
            pph_pajak_id:           calc.pph_pajak_id || null,
            tipe_pph:               calc.tipe_pph || 'Non-PPh',
            tarif_pph:              calc.tarif_pph || 0,
            nilai_pph:              calc.nilai_pph || 0,
            biaya_pengiriman:       calc.biaya_pengiriman || 0,
            total:                  calc.total,
            grand_total:            calc.grand_total,
            tipe_dp:                calc.use_dp ? calc.tipe_dp : null,
            dp_persen:              calc.use_dp ? calc.dp_persen : 0,
            dp_nominal:             calc.use_dp ? calc.dp_nominal : 0,
            sisa_tagihan:           calc.use_dp ? calc.sisa_tagihan : calc.total,
            items: cart.map(item => ({
                barang_id:      item.id,
                qty:            item.qty,
                harga_satuan:   item.harga_satuan,
                diskon_persen:  item.diskon_persen || 0,
                diskon_nominal: item.diskon_nominal || 0,
                ppn_pajak_id:   item.ppn_pajak_id || null,
                tarif_ppn:      item.tarif_ppn || 0,
                nilai_ppn:      item.nilai_ppn || 0,
                subtotal:       item.subtotal,
                catatan:        item.catatan || ''
            }))
        };

        $('#btn_submit_checkout').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Memproses...');

        $.ajax({
            url: "{{ route('marketing.sales-order.store') }}",
            type: "POST",
            data: JSON.stringify(orderData),
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                $('#btn_submit_checkout').prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> <span>Proses Pesanan Sekarang</span>');
                hideModal('#modalCheckoutOrder');

                // Clear Cart, LocalStorage & reset seluruh field form (ongkir, dp, dsb)
                clearCartFull();

                Swal.fire({
                    icon: 'success',
                    title: 'Sales Order Berhasil Diterbitkan',
                    html: `
                        <div class="my-3 p-3 rounded-3 text-center border" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
                            <div class="text-secondary small text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Nomor Dokumen Penjualan (SO)</div>
                            <div class="font-monospace fw-bold text-success fs-4 mt-1">${res.order?.nomor_so ?? ''}</div>
                        </div>
                        <div class="text-body-secondary small">
                            Dokumen pesanan penjualan telah berhasil diverifikasi dan tersimpan resmi ke dalam sistem ERP.
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bi bi-file-earmark-text me-1"></i> Lihat Dokumen SO',
                    cancelButtonText: 'Selesai'
                }).then(action => {
                    if (action.isConfirmed && res.order?.id) {
                        showOrderDetail(res.order.id);
                    }
                });


                // Reload Order History DataTables if initialized
                if (typeof tableOrders !== 'undefined') {
                    tableOrders.ajax.reload();
                }
            },
            error: function (xhr) {
                $('#btn_submit_checkout').prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> <span>Proses Pesanan Sekarang</span>');
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat memproses pesanan.';
                Swal.fire({ title: 'Gagal', text: msg, icon: 'error' });
            }
        });
    });

    // ===== ORDER HISTORY DATATABLES =====
    try {
        if ($.fn.DataTable && $('#tableSalesOrders').length) {
            tableOrders = $('#tableSalesOrders').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [], // Preserve server-side latest input order (id DESC)
                ajax: {
                    url: "{{ route('marketing.sales-order.history-data') }}",
                    data: function(d) {
                        d.status_filter = $('#history_status_filter').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables AJAX Error:', xhr.status, xhr.responseText);
                    }
                },
                language: {
                    emptyTable: '<div class="py-4 text-muted text-center"><i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>Belum ada data pesanan.</div>',
                    zeroRecords: '<div class="py-4 text-muted text-center"><i class="bi bi-search fs-2 d-block mb-2 opacity-50"></i>Tidak ditemukan pesanan yang sesuai.</div>',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    search: 'Cari:',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ pesanan',
                    infoEmpty: 'Tidak ada data.',
                    paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data pesanan...'
                },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center text-muted small fw-semibold', orderable: false, searchable: false },
            { 
                data: 'nomor_so', 
                name: 'nomor_so',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1.5 rounded-3 fw-bold">${data}</span>`;
                }
            },
            {
                data: 'tanggal_so',
                name: 'tanggal_so',
                render: function(data) {
                    if (!data) return '-';
                    let d = new Date(data);
                    return `<span class="text-nowrap"><i class="bi bi-calendar3 me-1 text-muted"></i>${d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</span>`;
                }
            },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'tier_badge', name: 'tier_badge', className: 'text-center', orderable: false, searchable: false },
            { data: 'items_count', name: 'items_count', className: 'text-center', orderable: false, searchable: false },
            { data: 'formatted_total', name: 'formatted_total', className: 'text-end', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', className: 'text-center', orderable: false, searchable: false },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    let hasInvoice   = !!row.has_active_invoice;
                    let invoiceNo    = row.active_invoice_no || '';
                    let isRestricted = ['Processing', 'Delivered'].includes(row.status) || hasInvoice;
                    let isTerminal   = ['Completed', 'Cancelled'].includes(row.status);
                    let canEditThisRow   = !isTerminal && !hasInvoice && SO_PERMISSIONS.canEdit && (!['Processing', 'Delivered'].includes(row.status) || SO_PERMISSIONS.isSuperAdmin);
                    let canCancelThisRow = !isTerminal && !hasInvoice && SO_PERMISSIONS.canHapus && (!['Processing', 'Delivered'].includes(row.status) || SO_PERMISSIONS.isSuperAdmin);
                    
                    let detailBtn = SO_PERMISSIONS.canLihat
                        ? `<button class="btn btn-sm btn-outline-primary btn-view-so" data-id="${row.id}" title="Lihat Detail Pesanan"><i class="bi bi-eye me-1"></i> Detail</button>`
                        : '';
                    let printBtn = (SO_PERMISSIONS.canCetak && row.status !== 'Cancelled')
                        ? `<a href="/marketing/sales-order/${row.id}/print-invoice" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak Sales Order (Tab Baru)"><i class="bi bi-printer"></i></a>`
                        : '';
                    let editBtn = canEditThisRow
                        ? `<button class="btn btn-sm btn-outline-warning btn-edit-so" data-id="${row.id}" title="Edit Pesanan"><i class="bi bi-pencil-square"></i></button>`
                        : (hasInvoice ? `<button class="btn btn-sm btn-outline-secondary opacity-50 cursor-not-allowed" disabled title="Terkunci: Sudah diterbitkan Faktur Penjualan (${invoiceNo})"><i class="bi bi-lock-fill"></i></button>` : '');
                    let cancelBtn = canCancelThisRow
                        ? `<button class="btn btn-sm btn-outline-danger btn-cancel-so" data-id="${row.id}" data-nomor="${row.nomor_so}" title="Batalkan Pesanan"><i class="bi bi-x-circle"></i></button>`
                        : '';

                    if (!detailBtn && !printBtn && !editBtn && !cancelBtn) {
                        return `<span class="text-muted small">-</span>`;
                    }

                    return `
                        <div class="action-btn-group">
                            ${detailBtn}
                            ${printBtn}
                            ${editBtn}
                            ${cancelBtn}
                        </div>
                    `;
                }
            }
        ]
            });
        }
    } catch (e) {
        console.warn('DataTable initialization error:', e);
    }

    $('#history_status_filter').on('change', function() {
        if (tableOrders) tableOrders.ajax.reload();
    });

    // Re-adjust columns and reload DataTables when user switches to Riwayat Pesanan tab
    $('button[data-bs-toggle="tab"], a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        let targetId = $(e.target).attr('id') || $(e.target).data('bs-target') || $(e.target).attr('href');
        if (targetId === 'riwayat-tab' || targetId === '#tab-riwayat') {
            if (tableOrders) {
                tableOrders.columns.adjust().draw();
                tableOrders.ajax.reload(null, false);
            }
        }
    });

    // ===== HELPER: SHOW/HIDE EDIT & CANCEL BUTTONS IN DETAIL MODAL =====
    function setDetailModalActions(order, activeInvoice) {
        const hasInvoice   = !!activeInvoice || !!order.has_active_invoice;
        const isRestricted = ['Processing', 'Delivered'].includes(order.status) || hasInvoice;
        const isTerminal   = ['Completed', 'Cancelled'].includes(order.status);

        const editable    = !isTerminal && !hasInvoice && SO_PERMISSIONS.canEdit && (!['Processing', 'Delivered'].includes(order.status) || SO_PERMISSIONS.isSuperAdmin);
        const cancellable = !isTerminal && !hasInvoice && SO_PERMISSIONS.canHapus && (!['Processing', 'Delivered'].includes(order.status) || SO_PERMISSIONS.isSuperAdmin);
        const printable   = SO_PERMISSIONS.canCetak && order.status !== 'Cancelled';
        const canDO       = SO_PERMISSIONS.canDO && ['Confirmed', 'Processing'].includes(order.status);

        // Store current order id on the buttons
        $('#btn_print_order').data('id', order.id).toggleClass('d-none', !printable);
        $('#btn_edit_order_from_detail').data('id', order.id).data('nomor', order.nomor_so).toggleClass('d-none', !editable);
        $('#btn_cancel_order_from_detail').data('id', order.id).data('nomor', order.nomor_so).toggleClass('d-none', !cancellable);
        $('#btn_do_from_order_detail').toggleClass('d-none', !canDO);
    }

    // ===== SHOW ORDER DETAIL MODAL =====
    function showOrderDetail(orderId) {
        $.get(`/marketing/sales-order/${orderId}/detail`, function (res) {
            let o = res.order;
            if (!o) return;

            // Entity / Kop Surat
            let compNama = res.entity_name || (o.branch_id && o.branch ? o.branch.nama_cabang : (o.company ? o.company.nama_perusahaan : (o.branch ? o.branch.nama_cabang : 'PT. PERSADA AMAN SENTOSA')));
            let parentNama = res.parent_name || '';
            let compAlmt = res.entity_address || (o.company && o.company.address ? o.company.address : 'Jl Cinere Raya No 40-41, Depok, Jawa Barat');
            let compPhone = res.entity_phone || '';
            let compEmail = res.entity_email || '';
            let compNpwp  = res.entity_npwp || '';
            let compLogo  = res.active_logo || '{{ asset("gambar/logo.png") }}';
            
            let kontakArr = [];
            if (compPhone) kontakArr.push(`Telp: ${compPhone}`);
            if (compEmail) kontakArr.push(`Email: ${compEmail}`);
            
            $('#so_detail_company_nama').text(compNama);
            if (parentNama && parentNama !== compNama) {
                $('#so_detail_company_parent').text(`Unit Bisnis / Cabang dari ${parentNama}`).show();
            } else {
                $('#so_detail_company_parent').hide();
            }
            $('#so_detail_company_alamat').text(compAlmt);
            $('#so_detail_company_kontak').text(kontakArr.length > 0 ? kontakArr.join(' | ') : '');
            if (compNpwp) {
                $('#so_detail_company_npwp').text(`NPWP: ${compNpwp}`).show();
            } else {
                $('#so_detail_company_npwp').hide();
            }
            $('#so_detail_company_logo').attr('src', compLogo);

            // Document Header
            $('#so_detail_nomor').text(`SO: ${o.nomor_so}`);
            let tgl = o.tanggal_so ? new Date(o.tanggal_so).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-';
            $('#so_detail_tanggal').html(`<i class="bi bi-calendar3 me-1"></i> ${tgl}`);
            
            // Tier Badge
            let tier = o.pricing_tier || 'Retail';
            $('#so_detail_tier').text(tier);

            // Linked Quotation & Tender Badges
            if (o.quotation) {
                $('#so_detail_quotation_badge').html(`
                    <span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle px-2.5 py-1 rounded-pill small font-monospace">
                        <i class="bi bi-file-earmark-check me-1"></i> SQ: ${o.quotation.nomor_quotation}
                    </span>
                `).show();
            } else {
                $('#so_detail_quotation_badge').hide();
            }

            if (o.tender) {
                $('#so_detail_tender_badge').html(`
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill small font-monospace">
                        <i class="bi bi-trophy me-1"></i> Tender: ${o.tender.nomor_kontrak || o.tender.nama_tender}
                    </span>
                `).show();
            } else {
                $('#so_detail_tender_badge').hide();
            }

            if (res.active_invoice) {
                $('#so_detail_invoice_badge').html(`
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small font-monospace" title="Pesanan ini sudah dibuatkan Faktur Penjualan">
                        <i class="bi bi-receipt me-1"></i> Faktur: ${res.active_invoice.nomor_faktur}
                    </span>
                `).show();
            } else {
                $('#so_detail_invoice_badge').hide();
            }

            // Sales & Creator Info
            let salesName = o.sales ? (o.sales.nama_lengkap || o.sales.nama) : (o.creator && o.creator.name ? o.creator.name : 'Marketing');
            let creatorName = o.creator ? o.creator.name : 'System';
            $('#so_detail_sales_info').text(salesName);
            $('#so_detail_creator_info').text(creatorName);

            // Status Badge
            let isAllDoTerkirim = false;
            let hasActiveDo = false;
            let hasInTransitDo = false;

            if (o.delivery_orders && Array.isArray(o.delivery_orders) && o.delivery_orders.length > 0) {
                let activeDos = o.delivery_orders.filter(d => d.status !== 'Dibatalkan');
                if (activeDos.length > 0) {
                    hasActiveDo = true;
                    isAllDoTerkirim = activeDos.every(d => d.status === 'Terkirim');
                    hasInTransitDo = activeDos.some(d => d.status === 'Siap Dikirim' || d.status === 'Dalam Perjalanan');
                }
            }

            let badgeHtml = '';
            if (o.status === 'Cancelled') {
                badgeHtml = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-x-circle-fill me-1"></i> Batal</span>';
            } else if (o.status === 'Completed') {
                badgeHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-patch-check-fill me-1"></i> Selesai</span>';
            } else if (isAllDoTerkirim || (!hasActiveDo && o.status === 'Delivered')) {
                badgeHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-check-circle-fill me-1"></i> Terkirim</span>';
            } else if (hasInTransitDo || o.status === 'Delivered') {
                badgeHtml = '<span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-truck me-1"></i> Dikirim</span>';
            } else if (o.status === 'Processing' || hasActiveDo) {
                badgeHtml = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-arrow-repeat me-1"></i> Diproses</span>';
            } else {
                badgeHtml = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold font-monospace"><i class="bi bi-box-seam me-1"></i> Menunggu Kirim</span>';
            }

            $('#so_detail_status_badge').html(badgeHtml);

            // Customer Info
            if (o.customer && o.customer.nama_perusahaan) {
                $('#so_detail_customer_pt').text(o.customer.nama_perusahaan);
                $('#so_detail_instansi_wrap').show();
            } else {
                $('#so_detail_instansi_wrap').hide();
            }

            let custName = o.customer ? o.customer.nama_customer : (o.nama_customer_walkin || 'Pelanggan Umum');
            $('#so_detail_customer_nama').text(custName);
            let custPhone = o.telepon_customer_walkin || (o.customer && (o.customer.telepon || o.customer.hp) ? (o.customer.telepon || o.customer.hp) : '-');
            $('#so_detail_customer_telepon').text(custPhone);
            // Customer Address
            let custAddr = '';
            if (o.alamat_pengiriman && o.alamat_pengiriman.trim() !== '' && o.alamat_pengiriman !== '-') {
                custAddr = o.alamat_pengiriman;
            }
            if (!custAddr && o.customer) {
                if (o.customer.alamat && o.customer.alamat.trim() !== '') {
                    custAddr = o.customer.alamat;
                } else if (o.customer.default_address) {
                    let da = o.customer.default_address;
                    custAddr = da.alamat_lengkap || [da.alamat, da.kota, da.provinsi, da.kode_pos ? ('Kode Pos ' + da.kode_pos) : ''].filter(Boolean).join(', ');
                } else if (Array.isArray(o.customer.addresses) && o.customer.addresses.length > 0) {
                    let fa = o.customer.addresses[0];
                    custAddr = fa.alamat_lengkap || [fa.alamat, fa.kota, fa.provinsi, fa.kode_pos ? ('Kode Pos ' + fa.kode_pos) : ''].filter(Boolean).join(', ');
                }
            }
            if (!custAddr || custAddr.trim() === '') {
                custAddr = 'Diambil di Tempat';
            }
            $('#so_detail_customer_alamat').text(custAddr);

            // Signatures
            $('#so_detail_sig_buyer').text(`( ${custName} )`);
            $('#so_detail_sig_company').text(`( ${salesName} )`);
            $('#so_detail_sig_company_name').text(compNama);

            // Payment terms & Notes
            $('#so_detail_metode_bayar').text(o.payment_method ? o.payment_method.nama_metode : 'Cash / Tunai');
            $('#so_detail_termin').text(o.payment_term ? o.payment_term.nama_term : 'Langsung (Cash)');

            // Syarat & Ketentuan & Catatan
            if (o.syarat_ketentuan && o.syarat_ketentuan.trim()) {
                $('#so_detail_syarat_ketentuan').text(o.syarat_ketentuan);
                $('#so_detail_syarat_ketentuan_wrap').show();
            } else {
                $('#so_detail_syarat_ketentuan').text('1. Barang sesuai dengan spesifikasi pesanan.\n2. Pembayaran sah setelah dana efektif masuk ke rekening.');
                $('#so_detail_syarat_ketentuan_wrap').show();
            }

            if (o.catatan && o.catatan.trim()) {
                $('#so_detail_catatan').text(o.catatan);
                $('#so_detail_catatan_wrap').show();
            } else {
                $('#so_detail_catatan_wrap').hide();
            }

            // Items Table
            let tbodyHtml = '';
            (o.details || []).forEach((d, idx) => {
                let brgNamaRaw = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : 'Item');
                let brgNama    = escapeHtml(brgNamaRaw);
                let brgSku     = escapeHtml(d.barang ? d.barang.kode_barang : '-');
                let kategoriNama = escapeHtml(d.kategori ? d.kategori.nama_kategori : (d.barang && d.barang.kategori ? d.barang.kategori.nama_kategori : ''));
                let satuanNama = escapeHtml(d.nama_satuan || (d.satuan ? d.satuan.nama_satuan : (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'Pcs')));
                let photoUrl = d.photo_url || '{{ asset("uploads/barang/placeholder_garment.png") }}';
                let itemNote = escapeHtml(d.catatan || d.deskripsi_item || '');
                
                let diskonVal = parseFloat(d.diskon_nominal || 0);
                let diskonPct = parseFloat(d.diskon_persen || 0);
                let diskonHtml = '<span class="text-body-secondary">-</span>';
                if (diskonPct > 0) {
                    diskonHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace px-2 py-0.5 rounded-pill" style="font-size:0.78rem;">${diskonPct}%</span>`;
                } else if (diskonVal > 0) {
                    diskonHtml = `<span class="text-danger font-monospace fw-semibold">- ${formatRp(diskonVal)}</span>`;
                }

                let itemTarifPpn = parseFloat(d.tarif_ppn || 0);
                let itemNilaiPpn = parseFloat(d.nilai_ppn || 0);
                let ppnHtml = '<span class="text-body-secondary small">-</span>';
                if (itemTarifPpn > 0) {
                    ppnHtml = `<span class="badge bg-info-subtle text-info border border-info-subtle font-monospace fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">${itemTarifPpn}%</span>`;
                } else if (itemNilaiPpn > 0) {
                    let calcPct = d.subtotal > 0 ? parseFloat(((itemNilaiPpn / d.subtotal) * 100).toFixed(1)) : 0;
                    ppnHtml = `<span class="badge bg-info-subtle text-info border border-info-subtle font-monospace fw-bold px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">${calcPct}%</span>`;
                }

                tbodyHtml += `
                    <tr>
                        <td class="text-center font-monospace text-body-secondary small">${idx + 1}</td>
                        <td class="text-center">
                            <img src="${photoUrl}" alt="${brgNama}" class="rounded-2 border shadow-xs" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.src='{{ asset('uploads/barang/placeholder_garment.png') }}';">
                        </td>
                        <td>
                            <div class="fw-bold text-body-emphasis">${brgNama}</div>
                            <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1.5 py-0.5 rounded" style="font-size: 0.72rem;">SKU: ${brgSku}</span>
                                ${kategoriNama ? `<span class="badge bg-secondary-subtle text-body border border-secondary-subtle px-1.5 py-0.5 rounded" style="font-size: 0.72rem;">${kategoriNama}</span>` : ''}
                            </div>
                            ${itemNote ? `<div class="text-body-secondary small mt-1" style="font-size: 0.8rem;"><i class="bi bi-chat-left-text me-1"></i>${itemNote}</div>` : ''}
                        </td>
                        <td class="text-center font-monospace fw-semibold text-body">${Number(d.qty)} ${satuanNama}</td>
                        <td class="text-end font-monospace text-body">${formatRp(d.harga_satuan)}</td>
                        <td class="text-center font-monospace">${diskonHtml}</td>
                        <td class="text-end font-monospace fw-bold text-body-emphasis">${formatRp(d.subtotal)}</td>
                        <td class="text-center font-monospace">${ppnHtml}</td>
                    </tr>
                `;
            });
            $('#so_detail_items_tbody').html(tbodyHtml || '<tr><td colspan="8" class="text-center py-4 text-body-secondary">Tidak ada rincian item.</td></tr>');

            // Kalkulasi Total & Rincian Finansial
            let subtotal     = parseFloat(o.subtotal || 0);
            let nilaiPpn     = parseFloat(o.nilai_ppn || 0);
            let pajakNom     = parseFloat(o.pajak_nominal || 0);
            let biayaKirim   = parseFloat(o.biaya_pengiriman || 0);
            let grandTotal   = parseFloat(o.grand_total || (o.total || 0));
            let dpNominal    = parseFloat(o.dp_nominal || 0);
            let dpPersen     = parseFloat(o.dp_persen || 0);

            let effectivePpn = nilaiPpn > 0 ? nilaiPpn : pajakNom;

            $('#so_detail_subtotal').text(formatRp(subtotal));

            // Total Pajak PPN (Akumulasi Pajak Per-Item)
            if (effectivePpn > 0) {
                $('#so_detail_pajak_label').text('Total Pajak (PPN):');
                $('#so_detail_pajak').text('+ ' + formatRp(effectivePpn));
                $('#so_detail_pajak_row').show();
            } else {
                $('#so_detail_pajak_row').hide();
            }

            // Ongkir
            if (biayaKirim > 0) {
                $('#so_detail_ongkir').text('+ ' + formatRp(biayaKirim));
                $('#so_detail_ongkir_row').show();
            } else {
                $('#so_detail_ongkir_row').hide();
            }

            // Grand Total & Terbilang
            $('#so_detail_grand_total').text(formatRp(grandTotal));
            $('#so_detail_terbilang').text(res.terbilang || (formatRp(grandTotal) + ' Rupiah'));

            // DP (Uang Muka)
            if (dpNominal > 0) {
                let dpLabel = (o.tipe_dp === 'Persen' && dpPersen > 0) ? `(${dpPersen}%)` : '';
                $('#so_detail_dp_label').text(dpLabel);
                $('#so_detail_dp_nominal').text(formatRp(dpNominal));
                $('#so_detail_dp_section').show();
            } else {
                $('#so_detail_dp_section').hide();
            }

            // Print button URL
            $('#btn_print_order').attr('href', `/marketing/sales-order/${o.id}/print-invoice`);

            // Actions & Modal Display
            setDetailModalActions(o, res.active_invoice);
            showModal('#modalDetailOrder');
        }).fail(function(xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat detail pesanan.', confirmButtonColor: '#10b981' });
        });
    }

    // ===== OPEN EDIT ORDER MODAL (SYNCHRONIZED WITH MODUL PENAWARAN) =====
    let _editOrderData = null;

    function openEditModal(orderId) {
        $.get(`/marketing/sales-order/${orderId}/detail`, function (res) {
            let o = res.order;
            if (!o) return;

            if (res.active_invoice) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pesanan Terkunci',
                    text: `Pesanan ini sudah tercatat pada Faktur Penjualan (${res.active_invoice.nomor_faktur}) sehingga rincian pesanan tidak dapat diubah lagi.`,
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            _editOrderData = o;

            $('#edit_order_id').val(o.id);
            $('#edit_so_nomor').val(o.nomor_so || '-');
            $('#edit_so_nomor_badge').html(`<i class="bi bi-lock-fill me-1"></i> SO: ${o.nomor_so || '-'}`);
            let editLabelMap = {
                'Draft': 'Draft',
                'Confirmed': 'Menunggu Kirim',
                'Processing': 'Diproses',
                'Delivered': 'Terkirim',
                'Completed': 'Selesai',
                'Cancelled': 'Batal'
            };
            $('#edit_status_badge').text(`Status: ${editLabelMap[o.status] || o.status || 'Confirmed'}`);

            // Tanggal SO
            if (o.tanggal_so) {
                let dateStr = o.tanggal_so.split('T')[0];
                $('#edit_tanggal_so').val(dateStr);
            } else {
                $('#edit_tanggal_so').val(new Date().toISOString().split('T')[0]);
            }

            // Sales PIC
            $('#edit_sales_id').val(o.sales_id || '');

            // Quotation & Tender Reference Badges & Source Box
            let hasRef = false;
            if (o.quotation && o.quotation.nomor_quotation) {
                $('#edit_quotation_badge').html(`<i class="bi bi-file-earmark-text me-1"></i> Penawaran: ${o.quotation.nomor_quotation}`).show();
                $('#edit_ref_source_text').text(`Penawaran Resmi (${o.quotation.nomor_quotation})`);
                hasRef = true;
            } else if (o.quotation && o.quotation.nomor_penawaran) {
                $('#edit_quotation_badge').html(`<i class="bi bi-file-earmark-text me-1"></i> Penawaran: ${o.quotation.nomor_penawaran}`).show();
                $('#edit_ref_source_text').text(`Penawaran Resmi (${o.quotation.nomor_penawaran})`);
                hasRef = true;
            } else if (o.sales_quotation_id) {
                $('#edit_quotation_badge').html(`<i class="bi bi-file-earmark-text me-1"></i> Penawaran SQ #${o.sales_quotation_id}`).show();
                $('#edit_ref_source_text').text(`Penawaran SQ #${o.sales_quotation_id}`);
                hasRef = true;
            } else {
                $('#edit_quotation_badge').hide();
            }

            if (o.tender && (o.tender.nomor_tender || o.tender.nomor_kontrak)) {
                let tenderNo = o.tender.nomor_kontrak || o.tender.nomor_tender;
                $('#edit_tender_badge').html(`<i class="bi bi-trophy me-1"></i> Tender: ${tenderNo}`).show();
                if (!hasRef) {
                    $('#edit_ref_source_text').text(`Tender / Proyek (${tenderNo})`);
                    hasRef = true;
                }
            } else {
                $('#edit_tender_badge').hide();
            }

            if (hasRef) {
                $('#edit_ref_tier_text').text(`Tier: ${o.pricing_tier || 'Tender'}`);
                $('#edit_ref_source_box').show();
            } else {
                $('#edit_ref_source_box').hide();
            }

            // Customer
            if (o.customer_id) {
                $('#edit_customer_id').val(o.customer_id).trigger('change.select2');
                $('#edit_nama_walkin').val('');
                $('#edit_walkin_wrap').hide();
            } else {
                $('#edit_customer_id').val('').trigger('change.select2');
                $('#edit_nama_walkin').val(o.nama_customer_walkin || '');
                $('#edit_walkin_wrap').show();
            }

            // Payment & Shipping
            $('#edit_payment_method_id').val(o.payment_method_id || '').trigger('change.select2');
            $('#edit_payment_term_id').val(o.payment_term_id || '').trigger('change.select2');
            $('#edit_alamat_pengiriman').val(o.alamat_pengiriman || '');
            $('#edit_catatan').val(o.catatan || '');
            $('#edit_syarat_ketentuan').val(o.syarat_ketentuan || "1. Barang sesuai dengan spesifikasi pesanan.\n2. Pembayaran sah setelah dana efektif masuk ke rekening.");
            $('#edit_biaya_pengiriman').val(formatThousands(parseFloat(o.biaya_pengiriman || 0)));

            // Diskon Global ditiadakan (hanya diskon per-item)
            $('#edit_diskon_tipe').val('Nominal');
            $('#edit_diskon_val').val('0');

            // Pajak PPN (Per Item) & Reset PPh
            $('#edit_nilai_ppn').val(parseFloat(o.nilai_ppn || o.pajak_nominal || 0));


            // Uang Muka (DP) Setup
            let grandTotalAwal = parseFloat(o.grand_total || o.total || 0);
            let dpNominalAwal  = parseFloat(o.dp_nominal || 0);
            let dpPersenAwal   = parseFloat(o.dp_persen || 0);
            let tipeDpAwal     = o.tipe_dp;

            if (!tipeDpAwal) {
                if (dpNominalAwal > 0) {
                    if (dpPersenAwal > 0) {
                        tipeDpAwal = 'Persen';
                    } else if (grandTotalAwal > 0 && Math.abs((dpNominalAwal / grandTotalAwal) - 0.5) < 0.01) {
                        tipeDpAwal = 'Persen';
                        dpPersenAwal = 50;
                    } else {
                        tipeDpAwal = 'Nominal';
                    }
                } else {
                    tipeDpAwal = 'None';
                }
            }

            $('#edit_tipe_dp').val(tipeDpAwal);
            $('#edit_dp_persen').val(dpPersenAwal > 0 ? dpPersenAwal : (tipeDpAwal === 'Persen' ? 50 : 0));
            $('#edit_dp_nominal_input').val(dpNominalAwal > 0 ? formatThousands(dpNominalAwal) : '0');
            $('#edit_dp_nominal').val(dpNominalAwal);

            if (tipeDpAwal === 'Persen') {
                $('#edit_dp_persen_wrap').show();
                $('#edit_dp_nominal_wrap').hide();
                $('#edit_dp_badge_mode').removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-info-subtle text-info border-info-subtle').addClass('bg-primary-subtle text-primary border-primary-subtle').text('DP ' + (dpPersenAwal || 50) + '%');
            } else if (tipeDpAwal === 'Nominal') {
                $('#edit_dp_persen_wrap').hide();
                $('#edit_dp_nominal_wrap').show();
                $('#edit_dp_badge_mode').removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-primary-subtle text-primary border-primary-subtle').addClass('bg-info-subtle text-info border-info-subtle').text('DP Nominal');
            } else {
                $('#edit_dp_persen_wrap').hide();
                $('#edit_dp_nominal_wrap').hide();
                $('#edit_dp_badge_mode').removeClass('bg-primary-subtle text-primary border-primary-subtle bg-info-subtle text-info border-info-subtle').addClass('bg-secondary-subtle text-secondary border-secondary-subtle').text('Tanpa DP (0%)');
            }

            // Build item rows (Mirrors Penawaran Item Rows)
            let tbodyHtml = '';
            let details = o.details || [];
            $('#edit_items_count_badge').text(`${details.length} Item`);

            details.forEach((d, idx) => {
                tbodyHtml += buildEditItemRowHtml(d, idx + 1);
            });
            $('#edit_items_tbody').html(tbodyHtml);

            recalcEditTotals();
            hideModal('#modalDetailOrder');
            showModal('#modalEditOrder');
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data pesanan.' });
        });
    }

    // Helper: Build HTML for an Edit Item Row
    function buildEditItemRowHtml(d, rowNum) {
        let isCustom   = !d.barang_id && (d.nama_barang_custom || d.is_custom);
        let brgNama    = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : (d.nama || 'Item'));
        let brgSku     = d.barang ? d.barang.kode_barang : (d.kode || 'CUSTOM');
        let satuanNama = d.nama_satuan || (d.satuan_nama ? d.satuan_nama : (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'Unit'));
        let photoUrl   = d.photo_url || (d.barang && d.barang.primary_foto ? `/storage/${d.barang.primary_foto.foto_path}` : (d.photo ? d.photo : '/uploads/barang/placeholder_garment.png'));
        let itemNote   = d.catatan || d.deskripsi_item || '';

        let hRetail = parseFloat(d.barang ? (d.barang.harga_jual_retail || 0) : (d.harga_retail || 0));
        let hGrosir = parseFloat(d.barang ? (d.barang.harga_jual_grosir || 0) : (d.harga_grosir || 0));
        let hTender = parseFloat(d.barang ? (d.barang.harga_jual_tender || 0) : (d.harga_tender || 0));
        let curPrice = parseFloat(d.harga_satuan !== undefined ? d.harga_satuan : (d.harga || 0));

        let tierSelected = (d.pricing_tier || '').toLowerCase();
        if (tierSelected === 'retail') tierSelected = 'satuan';
        if (!['tender', 'grosir', 'satuan'].includes(tierSelected)) {
            if (curPrice === hTender && hTender > 0) tierSelected = 'tender';
            else if (curPrice === hGrosir && hGrosir > 0) tierSelected = 'grosir';
            else if (curPrice === hRetail && hRetail > 0) tierSelected = 'satuan';
            else tierSelected = (hTender > 0) ? 'tender' : ((hGrosir > 0) ? 'grosir' : 'satuan');
        }

        // Pastikan harga satuan terisi sesuai tier master jika curPrice sebelumnya kosong
        if (curPrice <= 0) {
            if (tierSelected === 'tender' && hTender > 0) curPrice = hTender;
            else if (tierSelected === 'grosir' && hGrosir > 0) curPrice = hGrosir;
            else if (tierSelected === 'satuan' && hRetail > 0) curPrice = hRetail;
        }

        let diskonPersen  = parseFloat(d.diskon_persen || 0);
        let diskonNominal = parseFloat(d.diskon_nominal || 0);
        let diskonTipe    = (diskonPersen > 0) ? '%' : 'Rp';
        let diskonVal     = (diskonTipe === '%') ? diskonPersen : (diskonNominal > 0 ? formatThousands(diskonNominal) : '0');

        let safeItemNote = $('<div>').text(itemNote).html();
        let safeBrgNama  = $('<div>').text(brgNama).html();
        let safeBrgSku   = $('<div>').text(brgSku).html();

        let nameColumnHtml = isCustom
            ? `
                <div class="mb-1">
                    <input type="text" class="form-control form-control-sm fw-bold edit-item-custom-name" value="${safeBrgNama}" placeholder="Nama barang / jasa..." required>
                </div>
                <input type="text" class="form-control form-control-sm edit-item-catatan" style="font-size: 0.75rem;" value="${safeItemNote}" placeholder="Catatan spesifikasi item...">
            `
            : `
                <div class="d-flex align-items-center gap-2.5 mb-1">
                    <img src="${photoUrl}" alt="${safeBrgNama}" class="rounded-2 border flex-shrink-0 object-fit-cover shadow-xs" style="width: 38px; height: 38px;" onerror="this.src='/uploads/barang/placeholder_garment.png'">
                    <div class="overflow-hidden">
                        <div class="fw-bold small text-body-emphasis text-truncate" title="${safeBrgNama}">${safeBrgNama}</div>
                        <span class="badge bg-secondary-subtle text-body border font-monospace px-1.5 py-0.5" style="font-size:0.72rem;">${safeBrgSku}</span>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm edit-item-catatan" style="font-size: 0.75rem;" value="${safeItemNote}" placeholder="Catatan spesifikasi item...">
            `;

        let stock = (d.total_stok !== undefined) ? Number(d.total_stok) : ((d.stock !== undefined) ? Number(d.stock) : null);
        let tipeItem = d.tipe_item || (d.barang ? d.barang.tipe_item : 'Stok');
        let isJasa = (tipeItem === 'Jasa');

        let stockInfoHtml = '';
        if (!isCustom && (d.barang_id || d.barang) && !isJasa && stock !== null && !isNaN(stock)) {
            let stockBadgeClass = stock > 0 ? 'text-success' : 'text-danger';
            stockInfoHtml = `
                <div class="small text-muted mt-1 d-flex align-items-center justify-content-between" style="font-size:0.69rem;">
                    <span>Sisa Stok:</span>
                    <span class="fw-bold ${stockBadgeClass} font-monospace">${stock} ${satuanNama}</span>
                </div>
            `;
        }

        // Item PPN Setup
        let ppnId    = d.ppn_pajak_id || '';
        let tarifPpn = parseFloat(d.tarif_ppn !== undefined ? d.tarif_ppn : 0);
        let nilaiPpn = parseFloat(d.nilai_ppn !== undefined ? d.nilai_ppn : 0);

        // Fallback for older orders that had global PPN but items didn't have ppn_pajak_id saved
        if (!ppnId && tarifPpn === 0 && typeof _editOrderData !== 'undefined' && _editOrderData) {
            let globalTarif = parseFloat(_editOrderData.tarif_ppn || _editOrderData.pajak_persen || 0);
            let globalPpnId = _editOrderData.ppn_pajak_id || '';
            let globalTipe  = _editOrderData.tipe_ppn || _editOrderData.pajak_tipe;
            if (globalTipe !== 'None' && (globalTarif > 0 || globalPpnId)) {
                ppnId = globalPpnId;
                tarifPpn = globalTarif;
            }
        }

        let ppnList = window.ORDER_PPN_LIST || [];
        if (ppnId && tarifPpn === 0 && ppnList.length) {
            let matched = ppnList.find(p => p.id == ppnId);
            if (matched) tarifPpn = parseFloat(matched.tarif) || 0;
        } else if (!ppnId && tarifPpn > 0 && ppnList.length) {
            let matched = ppnList.find(p => parseFloat(p.tarif) === tarifPpn);
            if (matched) ppnId = matched.id;
        }

        let ppnOptionsHtml = `<option value="" data-tarif="0" ${!ppnId && tarifPpn === 0 ? 'selected' : ''}>Non PPN (0%)</option>`;
        ppnList.forEach(p => {
            let pTarif = parseFloat(p.tarif) || 0;
            let isSelected = (ppnId == p.id) || (!ppnId && tarifPpn > 0 && tarifPpn === pTarif);
            ppnOptionsHtml += `<option value="${p.id}" data-tarif="${pTarif}" ${isSelected ? 'selected' : ''}>${p.nama_pajak} (${pTarif}%)</option>`;
        });

        return `
            <tr data-detail-id="${d.id || ''}" data-barang-id="${d.barang_id || (d.barang ? d.barang.id : '')}" data-satuan-id="${d.satuan_id || ''}" data-satuan-nama="${satuanNama}" data-stock="${stock !== null ? stock : ''}" data-tipe-item="${tipeItem}" class="edit-item-row">
                <td class="text-center text-muted small fw-semibold row-number">${rowNum}</td>
                <td>
                    ${nameColumnHtml}
                </td>
                <td>
                    <div class="input-group input-group-sm flex-nowrap" style="min-width: 125px;">
                        <input type="number" class="form-control text-center px-1 font-monospace fw-semibold edit-item-qty" value="${parseFloat(d.qty || 1)}" min="0.0001" step="any">
                        <span class="input-group-text px-2 text-secondary bg-body-tertiary small" style="font-size:0.75rem;">${satuanNama}</span>
                    </div>
                    ${stockInfoHtml}
                </td>
                <td>
                    <div class="input-group input-group-sm flex-nowrap mb-1" style="min-width: 160px;">
                        <span class="input-group-text px-2 text-secondary bg-body-tertiary small" style="font-size:0.75rem;">Rp</span>
                        <input type="text" inputmode="numeric" class="form-control text-end font-monospace fw-semibold edit-item-harga edit-currency-input" value="${formatThousands(curPrice)}" placeholder="0" readonly style="background-color: var(--bs-tertiary-bg, #f8f9fa); cursor: not-allowed;" title="Harga otomatis mengikuti Tier yang dipilih">
                    </div>
                    ${!isCustom && (d.barang_id || d.barang) ? `
                    <select class="form-select form-select-xs py-0.5 px-1 edit-item-tier-select" style="font-size: 0.72rem;"
                        data-retail="${hRetail}"
                        data-grosir="${hGrosir}"
                        data-tender="${hTender}">
                        <option value="tender" ${tierSelected === 'tender' ? 'selected' : ''}>🏷️ Tender: ${formatRp(hTender)}</option>
                        <option value="grosir" ${tierSelected === 'grosir' ? 'selected' : ''}>📦 Grosir: ${formatRp(hGrosir)}</option>
                        <option value="satuan" ${tierSelected === 'satuan' ? 'selected' : ''}>🛍️ Retail: ${formatRp(hRetail)}</option>
                    </select>
                    ` : ''}
                </td>
                <td>
                    <div class="d-flex flex-column gap-1">
                        <div class="input-group input-group-sm flex-nowrap" style="min-width: 175px;">
                            <div class="btn-group btn-group-sm flex-shrink-0 edit-item-diskon-group border rounded-start-2 overflow-hidden">
                                <button type="button" class="btn btn-xs fw-bold btn-diskon-mode ${diskonTipe === 'Rp' ? 'btn-primary active text-white' : 'btn-light text-secondary'}" data-mode="Rp" style="font-size: 0.75rem; padding: 4px 10px;">Rp</button>
                                <button type="button" class="btn btn-xs fw-bold btn-diskon-mode ${diskonTipe === '%' ? 'btn-primary active text-white' : 'btn-light text-secondary'}" data-mode="%" style="font-size: 0.75rem; padding: 4px 10px;">%</button>
                            </div>
                            <input type="hidden" class="edit-item-diskon-tipe" value="${diskonTipe}">
                            <input type="text" inputmode="numeric" class="form-control text-end font-monospace fw-semibold edit-item-diskon-val edit-diskon-input" value="${diskonVal}" placeholder="0">
                        </div>
                        <div class="d-flex justify-content-end align-items-center">
                            <span class="text-danger font-monospace edit-item-diskon-preview small" style="font-size: 0.73rem; display: ${diskonNominal > 0 ? 'inline-block' : 'none'};">- ${formatRp(diskonNominal)}</span>
                        </div>
                    </div>
                </td>
                <td class="text-end fw-bold font-monospace text-primary small edit-item-subtotal">${formatRp(d.subtotal || 0)}</td>
                <td>
                    <select class="form-select form-select-xs py-1 edit-item-ppn-select" style="font-size: 0.73rem;">
                        ${ppnOptionsHtml}
                    </select>
                    <input type="hidden" class="edit-item-ppn-id" value="${ppnId}">
                    <input type="hidden" class="edit-item-tarif-ppn" value="${tarifPpn}">
                    <input type="hidden" class="edit-item-nilai-ppn" value="${nilaiPpn}">
                    <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                        <span class="text-muted" style="font-size: 0.69rem;">Pajak:</span>
                        <span class="font-monospace fw-semibold edit-item-ppn-preview ${nilaiPpn > 0 ? 'text-info' : 'text-muted'}" style="font-size: 0.72rem;">${nilaiPpn > 0 ? '+ ' + formatRp(nilaiPpn) : 'Rp 0'}</span>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-remove-edit-item" style="width: 28px; height: 28px; padding: 0;" title="Hapus Item">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // Real-time Indonesian thousands separator (.) on typing for currency inputs
    $(document).on('input keyup change', '.edit-currency-input', function () {
        let raw = $(this).val().replace(/[^0-9]/g, '');
        if (raw !== '') {
            $(this).val(parseInt(raw, 10).toLocaleString('id-ID'));
        }
    }).on('blur', '.edit-currency-input', function () {
        if ($(this).val().trim() === '') {
            $(this).val('0');
            recalcEditTotals();
        }
    });


    // Real-time Indonesian dot formatting for Item Diskon input (if mode is Rp)
    $(document).on('input keyup change', '.edit-item-diskon-val', function () {
        let row = $(this).closest('tr');
        let mode = row.find('.edit-item-diskon-tipe').val();
        if (mode === 'Rp') {
            let raw = $(this).val().replace(/[^0-9]/g, '');
            if (raw !== '') {
                $(this).val(parseInt(raw, 10).toLocaleString('id-ID'));
            }
        }
    }).on('blur', '.edit-item-diskon-val', function () {
        if ($(this).val().trim() === '') {
            $(this).val('0');
            recalcEditTotals();
        }
    });

    // Click Handler for Item Diskon Mode Toggle [Rp | %]
    $(document).on('click', '.btn-diskon-mode', function () {
        let mode = $(this).data('mode');
        let group = $(this).closest('.edit-item-diskon-group');
        group.find('.btn-diskon-mode').removeClass('btn-primary active text-white').addClass('btn-light text-secondary');
        $(this).removeClass('btn-light text-secondary').addClass('btn-primary active text-white');
        
        let row = $(this).closest('tr');
        row.find('.edit-item-diskon-tipe').val(mode);

        let inputEl = row.find('.edit-item-diskon-val');
        let curVal = unformatThousands(inputEl.val());

        let qty = parseFloat(row.find('.edit-item-qty').val()) || 0;
        let harga = unformatThousands(row.find('.edit-item-harga').val());
        let gross = qty * harga;

        if (mode === 'Rp') {
            let nom = (curVal <= 100 && gross > 0) ? Math.round(gross * (curVal / 100)) : curVal;
            inputEl.val(nom > 0 ? formatThousands(nom) : '0');
        } else {
            let pct = (gross > 0 && curVal > 100) ? Math.min(100, parseFloat(((curVal / gross) * 100).toFixed(1))) : Math.min(100, curVal);
            inputEl.val(pct > 0 ? pct : '0');
        }

        recalcEditTotals();
    });


    // Trigger Modal Picker Master Barang in Edit Modal
    $('#btnEditAddMasterItem').on('click', function () {
        // Collect current barang_ids in edit items table
        let currentBarangIds = [];
        $('#edit_items_tbody tr').each(function () {
            let bId = $(this).attr('data-barang-id') || $(this).data('barang-id');
            if (bId) currentBarangIds.push(String(bId));
        });

        // Update picker buttons visual indicator
        $('.barang-item-edit-row').each(function () {
            let rowBtn = $(this).find('.btn-select-edit-master-item');
            let bId = String(rowBtn.data('id') || '');
            if (currentBarangIds.includes(bId)) {
                rowBtn.removeClass('btn-primary').addClass('btn-secondary opacity-75').html('<i class="bi bi-check-circle-fill"></i> Sudah Ada');
            } else {
                rowBtn.removeClass('btn-secondary opacity-75').addClass('btn-primary').html('<i class="bi bi-plus-lg"></i> Pilih');
            }
        });

        showModal('#modalPickerBarangEditOrder');
        $('#searchBarangEditInput').val('');
        $('#pickerBarangEditCategoryChips .btn-category-edit-chip').removeClass('btn-primary active text-white').addClass('btn-outline-secondary');
        $('#pickerBarangEditCategoryChips .btn-category-edit-chip[data-cat="all"]').removeClass('btn-outline-secondary').addClass('btn-primary active text-white');
        filterMasterBarangEdit();
        setTimeout(() => $('#searchBarangEditInput').focus(), 300);
    });

    // Filter Master Barang in Edit Modal (Search Text + Category Chip)
    function filterMasterBarangEdit() {
        let q = ($('#searchBarangEditInput').val() || '').toLowerCase().trim();
        let activeCat = $('#pickerBarangEditCategoryChips .btn-category-edit-chip.active').data('cat') || 'all';
        $('#btnClearSearchBarangEdit').toggleClass('d-none', !q);

        let visibleCount = 0;
        $('.barang-item-edit-row').each(function () {
            let search = ($(this).data('search') || '').toString();
            let cat = ($(this).data('category') || '').toString();
            let matchText = !q || search.indexOf(q) > -1;
            let matchCat = activeCat === 'all' || cat.indexOf(activeCat) > -1;

            if (matchText && matchCat) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        $('#emptyBarangEditNotice').toggle(visibleCount === 0);
        $('#searchBarangEditCountBadge').text(`${visibleCount} Produk`);
    }

    $('#searchBarangEditInput').on('input', filterMasterBarangEdit);

    $('#btnClearSearchBarangEdit').on('click', function () {
        $('#searchBarangEditInput').val('').focus();
        filterMasterBarangEdit();
    });

    $(document).on('click', '#pickerBarangEditCategoryChips .btn-category-edit-chip', function () {
        $('#pickerBarangEditCategoryChips .btn-category-edit-chip').removeClass('btn-primary active text-white').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary active text-white');
        filterMasterBarangEdit();
    });

    // Select Item from Master Barang Picker Modal (With Duplicate Prevention)
    $(document).on('click', '.btn-select-edit-master-item', function () {
        let b = $(this).data();
        let targetBarangId = String(b.id);

        // Check if item already exists in the table
        let existingRow = null;
        $('#edit_items_tbody tr').each(function () {
            let rowBarangId = String($(this).attr('data-barang-id') || $(this).data('barang-id') || '');
            if (rowBarangId === targetBarangId) {
                existingRow = $(this);
                return false;
            }
        });

        if (existingRow && existingRow.length > 0) {
            hideModal('#modalPickerBarangEditOrder');
            Swal.fire({
                icon: 'warning',
                title: 'Produk Sudah Ada!',
                html: `
                    <div class="text-center">
                        <p class="mb-2">Produk <strong>${b.nama || 'Item'}</strong> <span class="badge bg-secondary-subtle text-body border font-monospace">${b.kode || ''}</span> sudah ada di dalam daftar pesanan.</p>
                        <p class="small text-muted mb-0">Tidak dapat menambahkan produk yang sama. Silakan ubah kuantitas (Qty) pada baris item yang sudah ada.</p>
                    </div>
                `,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Mengerti & Sorot Item'
            }).then(() => {
                existingRow.addClass('table-warning');
                if (existingRow[0] && typeof existingRow[0].scrollIntoView === 'function') {
                    existingRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                let qtyInput = existingRow.find('.edit-item-qty');
                qtyInput.focus().select();
                setTimeout(() => {
                    existingRow.removeClass('table-warning');
                }, 2500);
            });
            return;
        }

        let stockAvailable = parseFloat(b.stock) || 0;
        let isJasa = (b.tipe === 'Jasa');
        if (!isJasa && stockAvailable <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Tersedia',
                text: `Persediaan produk "${b.nama || 'Barang'}" saat ini kosong di gudang.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let nextIndex = $('#edit_items_tbody tr').length + 1;
        let newRowHtml = buildEditItemRowHtml({
            id: '',
            barang_id: b.id,
            kode: b.kode,
            nama: b.nama,
            satuan_id: b.satuanId,
            satuan_nama: b.satuanNama,
            kategori_id: b.kategoriId,
            tipe_item: b.tipe || 'Stok',
            total_stok: stockAvailable,
            stock: stockAvailable,
            harga_satuan: b.harga,
            harga_retail: b.hargaRetail || 0,
            harga_grosir: b.hargaGrosir || 0,
            harga_tender: b.hargaTender || 0,
            pricing_tier: (b.hargaTender > 0) ? 'tender' : ((b.hargaGrosir > 0) ? 'grosir' : 'satuan'),
            photo_url: b.photoUrl || '',
            qty: 1,
            diskon_persen: 0,
            diskon_nominal: 0,
            is_custom: false
        }, nextIndex);

        $('#edit_items_tbody').append(newRowHtml);
        hideModal('#modalPickerBarangEditOrder');
        updateEditItemCounts();
        recalcEditTotals();
    });


    // Remove Item Row in Edit Modal
    $(document).on('click', '.btn-remove-edit-item', function () {
        let totalRows = $('#edit_items_tbody tr').length;
        if (totalRows <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pesanan harus memiliki minimal 1 item barang aktif.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        $(this).closest('tr').remove();
        updateEditItemCounts();
        recalcEditTotals();
    });

    function updateEditItemCounts() {
        let count = 0;
        $('#edit_items_tbody tr').each(function (idx) {
            $(this).find('.row-number').text(idx + 1);
            count++;
        });
        $('#edit_items_count_badge').text(`${count} Item`);
    }

    // Handle Tier Selector Change inside Edit Table
    $(document).on('change', '.edit-item-tier-select', function () {
        let row = $(this).closest('tr');
        let tier = $(this).val();
        let retail = parseFloat($(this).data('retail')) || 0;
        let grosir = parseFloat($(this).data('grosir')) || 0;
        let tender = parseFloat($(this).data('tender')) || 0;

        if (tier === 'tender') {
            let price = tender > 0 ? tender : (grosir > 0 ? grosir : retail);
            row.find('.edit-item-harga').val(formatThousands(price));
        } else if (tier === 'grosir') {
            let price = grosir > 0 ? grosir : (retail > 0 ? retail : tender);
            row.find('.edit-item-harga').val(formatThousands(price));
        } else if (tier === 'satuan') {
            let price = retail > 0 ? retail : (grosir > 0 ? grosir : tender);
            row.find('.edit-item-harga').val(formatThousands(price));
        }
        recalcEditTotals();
    });

    // Handle Item PPN Change in Edit Order Table
    $(document).on('change', '.edit-item-ppn-select', function () {
        let selectedOpt = $(this).find('option:selected');
        let tarif = parseFloat(selectedOpt.data('tarif')) || 0;
        let id = $(this).val() || '';
        let row = $(this).closest('tr');
        row.find('.edit-item-ppn-id').val(id);
        row.find('.edit-item-tarif-ppn').val(tarif);
        recalcEditTotals();
    });

    // Handle Tipe DP (None, Persen, Nominal) Change
    $('#edit_tipe_dp').on('change', function () {
        let val = $(this).val();
        if (val === 'Persen') {
            $('#edit_dp_persen_wrap').show();
            $('#edit_dp_nominal_wrap').hide();
            let pct = parseFloat($('#edit_dp_persen').val()) || 0;
            if (pct <= 0) {
                pct = 50;
                $('#edit_dp_persen').val(50);
            }
            $('#edit_dp_badge_mode').removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-info-subtle text-info border-info-subtle').addClass('bg-primary-subtle text-primary border-primary-subtle').text('DP ' + pct + '%');
        } else if (val === 'Nominal') {
            $('#edit_dp_persen_wrap').hide();
            $('#edit_dp_nominal_wrap').show();
            $('#edit_dp_badge_mode').removeClass('bg-secondary-subtle text-secondary border-secondary-subtle bg-primary-subtle text-primary border-primary-subtle').addClass('bg-info-subtle text-info border-info-subtle').text('DP Nominal');
        } else {
            $('#edit_dp_persen_wrap').hide();
            $('#edit_dp_nominal_wrap').hide();
            $('#edit_dp_persen').val('0');
            $('#edit_dp_nominal_input').val('0');
            $('#edit_dp_info_wrap').hide();
            $('#edit_dp_badge_mode').removeClass('bg-primary-subtle text-primary border-primary-subtle bg-info-subtle text-info border-info-subtle').addClass('bg-secondary-subtle text-secondary border-secondary-subtle').text('Tanpa DP (0%)');
        }
        recalcEditTotals();
    });

    // Handle DP Persen input change to update badge text
    $('#edit_dp_persen').on('input', function () {
        let pct = parseFloat($(this).val()) || 0;
        if ($('#edit_tipe_dp').val() === 'Persen') {
            $('#edit_dp_badge_mode').text('DP ' + pct + '%');
        }
    });

    // ===== RECALC EDIT ORDER TOTALS (PENAWARAN COMPATIBLE DUAL DISCOUNT & TAX INCL/EXCL) =====
    function recalcEditTotals() {
        let subtotal = 0;
        let totalNilaiPpn = 0;
        let taxedItemsCount = 0;

        $('#edit_items_tbody tr').each(function () {
            let qty        = parseFloat($(this).find('.edit-item-qty').val()) || 0;
            let harga      = unformatThousands($(this).find('.edit-item-harga').val());
            let gross      = qty * harga;
            let diskonTipe = $(this).find('.edit-item-diskon-tipe').val() || 'Rp';
            let diskonRaw  = $(this).find('.edit-item-diskon-val').val();
            let diskonVal  = (diskonTipe === '%') ? (parseFloat(diskonRaw) || 0) : unformatThousands(diskonRaw);
            
            if (diskonTipe === '%') {
                if (diskonVal > 100) { diskonVal = 100; $(this).find('.edit-item-diskon-val').val(100); }
                else if (diskonVal < 0) { diskonVal = 0; $(this).find('.edit-item-diskon-val').val(0); }
            } else {
                if (diskonVal < 0) diskonVal = 0;
            }

            let diskonNominal = (diskonTipe === '%') ? Math.round(gross * (diskonVal / 100)) : diskonVal;
            diskonNominal = Math.min(gross, Math.max(0, diskonNominal));
            let sub        = Math.max(0, Math.round(gross - diskonNominal));

            $(this).find('.edit-item-subtotal').text(formatRp(sub));
            
            let previewEl = $(this).find('.edit-item-diskon-preview');
            if (diskonNominal > 0) {
                previewEl.text('- ' + formatRp(diskonNominal)).show();
            } else {
                previewEl.hide();
            }

            // Per-Item PPN Calculation
            let selectedPpnOpt = $(this).find('.edit-item-ppn-select option:selected');
            let tarifPpn       = parseFloat(selectedPpnOpt.data('tarif')) || 0;
            let ppnId          = $(this).find('.edit-item-ppn-select').val() || '';
            let itemNilaiPpn   = Math.round(sub * (tarifPpn / 100));

            $(this).find('.edit-item-ppn-id').val(ppnId);
            $(this).find('.edit-item-tarif-ppn').val(tarifPpn);
            $(this).find('.edit-item-nilai-ppn').val(itemNilaiPpn);

            let ppnPreviewEl = $(this).find('.edit-item-ppn-preview');
            if (itemNilaiPpn > 0) {
                ppnPreviewEl.text('+ ' + formatRp(itemNilaiPpn)).removeClass('text-muted').addClass('text-info');
                taxedItemsCount++;
            } else {
                ppnPreviewEl.text('Rp 0').removeClass('text-info').addClass('text-muted');
            }

            $(this).data('calc-diskon-nominal', diskonNominal);
            $(this).data('calc-diskon-persen', (diskonTipe === '%') ? diskonVal : 0);
            $(this).data('calc-subtotal', sub);
            $(this).data('calc-ppn-id', ppnId);
            $(this).data('calc-tarif-ppn', tarifPpn);
            $(this).data('calc-nilai-ppn', itemNilaiPpn);

            subtotal += sub;
            totalNilaiPpn += itemNilaiPpn;
        });

        let ongkir     = unformatThousands($('#edit_biaya_pengiriman').val());
        let grandTotal = Math.max(0, subtotal + totalNilaiPpn + ongkir);

        $('#edit_nilai_ppn').val(totalNilaiPpn);
        $('#edit_summary_subtotal').text(formatRp(subtotal));

        if (totalNilaiPpn > 0) {
            $('#edit_summary_pajak').removeClass('text-muted text-body-secondary').addClass('text-info').text('+ ' + formatRp(totalNilaiPpn));
        } else {
            $('#edit_summary_pajak').removeClass('text-info').addClass('text-body-secondary').text('+ Rp 0');
        }

        $('#edit_summary_grand_total').text(formatRp(grandTotal));

        // Uang Muka (DP) & Sisa Tagihan Calculation (Dynamic & Capped)
        let tipeDp = $('#edit_tipe_dp').val() || 'None';
        let dpPersen = parseFloat($('#edit_dp_persen').val()) || 0;
        let dpNominal = 0;

        if (tipeDp === 'Persen') {
            if (dpPersen > 100) {
                dpPersen = 100;
                $('#edit_dp_persen').val(100);
            }
            dpNominal = Math.round(grandTotal * (dpPersen / 100));
            $('#edit_dp_nominal_input').val(formatThousands(dpNominal));
            $('#edit_dp_badge_mode').text('DP ' + dpPersen + '%');
        } else if (tipeDp === 'Nominal') {
            dpNominal = unformatThousands($('#edit_dp_nominal_input').val());
            if (dpNominal > grandTotal && grandTotal > 0) {
                dpNominal = grandTotal;
                $('#edit_dp_nominal_input').val(formatThousands(grandTotal));
            }
            if (grandTotal > 0 && dpNominal > 0) {
                dpPersen = parseFloat(((dpNominal / grandTotal) * 100).toFixed(1));
            }
        } else {
            dpNominal = 0;
            dpPersen = 0;
        }

        let sisaTagihan = Math.max(0, grandTotal - dpNominal);

        $('#edit_dp_nominal').val(dpNominal);
        $('#edit_sisa_tagihan').val(sisaTagihan);

        if (tipeDp !== 'None' && dpNominal > 0 && grandTotal > 0) {
            $('#edit_dp_info_wrap').show();
            $('#edit_dp_nominal_text').text(formatRp(dpNominal));
            $('#edit_sisa_tagihan_text').text(formatRp(sisaTagihan));
        } else {
            $('#edit_dp_info_wrap').hide();
            $('#edit_dp_nominal_text').text('Rp 0');
            $('#edit_sisa_tagihan_text').text(formatRp(grandTotal));
        }

        // Store for submit
        $('#formEditOrder').data('subtotal', subtotal);
        $('#formEditOrder').data('diskon_nominal', 0);
        $('#formEditOrder').data('pajak_nominal', totalNilaiPpn);
        $('#formEditOrder').data('nilai_ppn', totalNilaiPpn);
        let effTarifPpn = (subtotal > 0 && totalNilaiPpn > 0) ? parseFloat(((totalNilaiPpn / subtotal) * 100).toFixed(2)) : 0;
        $('#formEditOrder').data('tarif_ppn', effTarifPpn);
        $('#formEditOrder').data('tipe_ppn', totalNilaiPpn > 0 ? 'Item' : 'None');
        $('#formEditOrder').data('pph_nominal', 0);
        $('#formEditOrder').data('tarif_pph', 0);
        $('#formEditOrder').data('tipe_pph', 'Non-PPh');
        $('#formEditOrder').data('biaya_pengiriman', ongkir);
        $('#formEditOrder').data('grand_total', grandTotal);
        $('#formEditOrder').data('tipe_dp', tipeDp);
        $('#formEditOrder').data('dp_persen', dpPersen);
        $('#formEditOrder').data('dp_nominal', dpNominal);
        $('#formEditOrder').data('sisa_tagihan', sisaTagihan);
    }

    // Real-time stock validation and warning when edit-item-qty changes
    $(document).on('input', '#edit_items_tbody .edit-item-qty', function () {
        let row = $(this).closest('tr');
        let bId = row.attr('data-barang-id') || row.data('barang-id');
        let tipe = row.attr('data-tipe-item') || row.data('tipe-item');
        let stockRaw = row.attr('data-stock');
        if (stockRaw === undefined || stockRaw === null || stockRaw === '') {
            stockRaw = row.data('stock');
        }
        let stock = parseFloat(stockRaw);
        let qty = parseFloat($(this).val()) || 0;
        let isJasa = (tipe === 'Jasa');

        if (bId && !isJasa && !isNaN(stock) && stock >= 0) {
            let warnEl = row.find('.edit-qty-stock-warning');
            if (qty > stock) {
                $(this).addClass('is-invalid bg-danger-subtle text-danger border-danger');
                if (warnEl.length === 0) {
                    $(this).closest('td').append(`<div class="edit-qty-stock-warning text-danger fw-semibold mt-1" style="font-size:0.68rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Melebihi stok (Maks: ${stock})</div>`);
                } else {
                    warnEl.show();
                }
            } else {
                $(this).removeClass('is-invalid bg-danger-subtle text-danger border-danger');
                warnEl.remove();
            }
        }
    });

    $(document).on('change', '#edit_items_tbody .edit-item-qty', function () {
        let row = $(this).closest('tr');
        let bId = row.attr('data-barang-id') || row.data('barang-id');
        let tipe = row.attr('data-tipe-item') || row.data('tipe-item');
        let stockRaw = row.attr('data-stock');
        if (stockRaw === undefined || stockRaw === null || stockRaw === '') {
            stockRaw = row.data('stock');
        }
        let stock = parseFloat(stockRaw);
        let qty = parseFloat($(this).val()) || 0;
        let isJasa = (tipe === 'Jasa');

        if (bId && !isJasa && !isNaN(stock) && stock >= 0 && qty > stock) {
            let brgNama = row.find('.fw-bold.text-body-emphasis').text() || 'Produk';
            let satuan = row.data('satuan-nama') || 'Unit';

            Swal.fire({
                icon: 'warning',
                title: 'Kuantitas Melebihi Stok!',
                html: `
                    <div class="text-center">
                        <p class="mb-2">Kuantitas untuk <strong>${brgNama}</strong> (${qty} ${satuan}) melebihi stok yang tersedia di gudang.</p>
                        <div class="p-2 bg-warning-subtle text-warning-emphasis rounded border border-warning-subtle font-monospace small mb-2">
                            Sisa Stok Tersedia: <strong>${stock} ${satuan}</strong>
                        </div>
                        <p class="small text-muted mb-0">Apakah Anda ingin menyesuaikan kuantitas ke batas stok maksimum yang tersedia?</p>
                    </div>
                `,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Sesuaikan ke Batas Stok',
                showCancelButton: true,
                cancelButtonText: 'Tetap Lanjutkan',
                cancelButtonColor: '#6c757d'
            }).then((res) => {
                if (res.isConfirmed) {
                    $(this).val(stock > 0 ? stock : 1);
                    $(this).removeClass('is-invalid bg-danger-subtle text-danger border-danger');
                    row.find('.edit-qty-stock-warning').remove();
                    recalcEditTotals();
                }
            });
        }
    });

    // Auto-recalc when any edit item field changes (Real-time on input/keyup/change)
    $(document).on('input keyup change', '#edit_items_tbody .edit-item-qty, #edit_items_tbody .edit-item-harga, #edit_items_tbody .edit-item-diskon-val, #edit_items_tbody .edit-item-ppn-select, #edit_biaya_pengiriman, #edit_tipe_dp, #edit_dp_persen, #edit_dp_nominal_input', function () {
        recalcEditTotals();
    });

    // Toggle walkin field on customer select change
    $('#edit_customer_id').on('change', function () {
        if ($(this).val()) {
            $('#edit_walkin_wrap').hide();
        } else {
            $('#edit_walkin_wrap').show();
        }
    });

    // ===== SUBMIT EDIT ORDER =====
    $('#formEditOrder').on('submit', function (e) {
        e.preventDefault();

        let orderId = $('#edit_order_id').val();
        let items   = [];
        let valid   = true;

        $('#edit_items_tbody tr').each(function () {
            let detailId    = $(this).data('detail-id') || null;
            let barangId    = $(this).data('barang-id') || null;
            let customName  = $(this).find('.edit-item-custom-name').val() || null;
            let qty         = parseFloat($(this).find('.edit-item-qty').val()) || 0;
            let harga       = unformatThousands($(this).find('.edit-item-harga').val());
            let diskonTipe  = $(this).find('.edit-item-diskon-tipe').val() || 'Rp';
            let diskonRaw   = $(this).find('.edit-item-diskon-val').val();
            let diskonVal   = (diskonTipe === '%') ? (parseFloat(diskonRaw) || 0) : unformatThousands(diskonRaw);
            let diskonNom   = $(this).data('calc-diskon-nominal');
            if (diskonNom === undefined || isNaN(diskonNom)) {
                diskonNom = (diskonTipe === '%') ? Math.round((qty * harga) * (diskonVal / 100)) : diskonVal;
                diskonNom = Math.min(qty * harga, Math.max(0, diskonNom));
            }
            let diskonPct   = (diskonTipe === '%') ? diskonVal : 0;
            let tierVal     = $(this).find('.edit-item-tier-select').val() || 'satuan';
            let tier        = (tierVal === 'tender') ? 'Tender' : ((tierVal === 'grosir') ? 'Grosir' : 'Retail');
            let catatan     = $(this).find('.edit-item-catatan').val() || '';
            let sub         = $(this).data('calc-subtotal');
            if (sub === undefined || isNaN(sub)) {
                sub = Math.max((qty * harga) - diskonNom, 0);
            }

            if (qty <= 0) { valid = false; return false; }

            let ppnId    = $(this).find('.edit-item-ppn-select').val() || null;
            let tarifPpn = parseFloat($(this).find('.edit-item-tarif-ppn').val()) || 0;
            let nilaiPpn = parseFloat($(this).find('.edit-item-nilai-ppn').val()) || 0;

            items.push({
                detail_id:          detailId,
                barang_id:          barangId,
                nama_barang_custom: customName,
                satuan_id:          $(this).data('satuan-id') || null,
                nama_satuan:        $(this).data('satuan-nama') || 'Unit',
                pricing_tier:       tier,
                qty:                qty,
                harga_satuan:       harga,
                diskon_tipe:        diskonTipe,
                diskon_nilai:       diskonVal,
                diskon_persen:      diskonPct,
                diskon_nominal:     diskonNom,
                subtotal:           sub,
                ppn_pajak_id:       ppnId,
                tarif_ppn:          tarifPpn,
                nilai_ppn:          nilaiPpn,
                catatan:            catatan,
            });
        });

        if (!valid) {
            Swal.fire({ icon: 'warning', title: 'Qty Tidak Valid', text: 'Qty semua item harus lebih dari 0.', confirmButtonColor: '#f59e0b' });
            return;
        }

        if (items.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Item Kosong', text: 'Pesanan harus memiliki minimal 1 item.', confirmButtonColor: '#f59e0b' });
            return;
        }

        let zeroEditItem = items.find(i => Number(i.harga_satuan) <= 0);
        if (zeroEditItem) {
            let itemName = zeroEditItem.nama_barang_custom || 'Item Produk';
            Swal.fire({
                icon: 'warning',
                title: 'Harga Produk Belum Diatur',
                html: `Terdapat item dengan harga <b>Rp 0</b> pada pesanan ini.<br>Setiap produk wajib memiliki harga satuan lebih dari Rp 0 sebelum perubahan dapat disimpan.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        // Validasi Ketersediaan Stok Fisik Sebelum Submit
        let overStockItems = [];
        $('#edit_items_tbody tr').each(function () {
            let bId = $(this).attr('data-barang-id') || $(this).data('barang-id');
            let tipe = $(this).attr('data-tipe-item') || $(this).data('tipe-item');
            let stockRaw = $(this).attr('data-stock');
            if (stockRaw === undefined || stockRaw === null || stockRaw === '') {
                stockRaw = $(this).data('stock');
            }
            let stock = parseFloat(stockRaw);
            let qty = parseFloat($(this).find('.edit-item-qty').val()) || 0;
            let name = $(this).find('.edit-item-custom-name').val() || $(this).find('.fw-bold.text-body-emphasis').text() || 'Produk';
            let satuan = $(this).data('satuan-nama') || 'Unit';

            if (bId && tipe !== 'Jasa' && !isNaN(stock) && stock >= 0 && qty > stock) {
                overStockItems.push({ name: name.trim(), qty: qty, stock: stock, satuan: satuan });
            }
        });

        if (overStockItems.length > 0) {
            let listHtml = overStockItems.map(it => `
                <li class="mb-1.5">
                    <strong>${it.name}</strong><br>
                    <span class="text-danger fw-semibold">Jumlah Dipesan: ${it.qty} ${it.satuan}</span> 
                    <span class="badge bg-secondary-subtle text-body border ms-1 font-monospace">Sisa Stok: ${it.stock} ${it.satuan}</span>
                </li>
            `).join('');

            Swal.fire({
                icon: 'warning',
                title: 'Kuantitas Melebihi Stok!',
                html: `
                    <div class="text-start">
                        <p class="mb-2">Terdapat item pesanan yang kuantitasnya melebihi persediaan gudang:</p>
                        <ul class="mb-2 ps-3 small text-body">${listHtml}</ul>
                        <p class="small text-muted mb-0">Silakan sesuaikan kuantitas barang agar tidak melebihi sisa stok yang tersedia di gudang sebelum menyimpan.</p>
                    </div>
                `,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Periksa Kembali'
            });
            return;
        }

        // Validasi Wajib Pelanggan / Customer
        let editCustomerId = $('#edit_customer_id').val();
        let editWalkinName = $('#edit_nama_walkin').val() ? $('#edit_nama_walkin').val().trim() : '';
        if (!editCustomerId && !editWalkinName) {
            Swal.fire({
                icon: 'warning',
                title: 'Pelanggan Wajib Diisi',
                text: 'Silakan pilih pelanggan terdaftar atau isi nama pelanggan terlebih dahulu sebelum menyimpan perubahan pesanan.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // Validate duplicate master items
        let seenBarang = {};
        let duplicateName = null;
        $('#edit_items_tbody tr').each(function () {
            let bId = $(this).attr('data-barang-id') || $(this).data('barang-id');
            if (bId && bId !== '' && bId !== 'null' && bId !== 'undefined') {
                if (seenBarang[bId]) {
                    duplicateName = $(this).find('.text-truncate').text().trim() || 'Item Produk';
                    return false;
                }
                seenBarang[bId] = true;
            }
        });

        if (duplicateName) {
            Swal.fire({
                icon: 'warning',
                title: 'Item Duplikat Terdeteksi',
                html: `Produk <strong>${duplicateName}</strong> terinput lebih dari 1 kali dalam pesanan.<br><small class="text-muted">Harap hapus salah satu baris duplikat atau gabungkan kuantitasnya sebelum menyimpan.</small>`,
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        let form     = $('#formEditOrder');
        let nilaiPpn = form.data('pajak_nominal') || 0;
        let tarifPpn = form.data('tarif_ppn') || 0;
        let tipePpn  = form.data('tipe_ppn') || (nilaiPpn > 0 ? 'Item' : 'None');

        let tipeDp   = $('#edit_tipe_dp').val() || 'None';
        let dpPersen = parseFloat($('#edit_dp_persen').val()) || 0;
        let dpNominal = parseFloat($('#edit_dp_nominal').val()) || 0;
        let sisaTagihan = parseFloat($('#edit_sisa_tagihan').val()) || 0;

        let payload  = {
            tanggal_so:         $('#edit_tanggal_so').val(),
            sales_id:           $('#edit_sales_id').val() || null,
            customer_id:        $('#edit_customer_id').val() || null,
            nama_customer_walkin: $('#edit_customer_id').val() ? null : $('#edit_nama_walkin').val(),
            payment_method_id:  $('#edit_payment_method_id').val() || null,
            payment_term_id:    $('#edit_payment_term_id').val() || null,
            alamat_pengiriman:  $('#edit_alamat_pengiriman').val(),
            catatan:            $('#edit_catatan').val(),
            syarat_ketentuan:   $('#edit_syarat_ketentuan').val(),
            diskon_tipe:        'Nominal',
            diskon_nilai:       0,
            diskon_persen:      0,
            diskon_nominal:     0,
            tipe_ppn:           tipePpn,
            ppn_pajak_id:       null,
            tarif_ppn:          tarifPpn,
            nilai_ppn:          nilaiPpn,
            pajak_tipe:         (nilaiPpn > 0 ? 'PPN_Exclude' : 'None'),
            pajak_persen:       tarifPpn,
            pajak_nominal:      nilaiPpn,
            tipe_pph:           'Non-PPh',
            pph_pajak_id:       null,
            tarif_pph:          0,
            nilai_pph:          0,
            biaya_pengiriman:   unformatThousands($('#edit_biaya_pengiriman').val()),
            tipe_dp:            tipeDp,
            dp_persen:          dpPersen,
            dp_nominal:         dpNominal,
            sisa_tagihan:       sisaTagihan,
            subtotal:           form.data('subtotal') || 0,
            grand_total:        form.data('grand_total') || 0,
            total:              form.data('grand_total') || 0,
            items:              items,
        };

        $('#btn_submit_edit_order').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...');

        $.ajax({
            url:  `/marketing/sales-order/${orderId}/update`,
            type: 'PUT',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#btn_submit_edit_order').prop('disabled', false).html('<i class="bi bi-save2-fill me-1"></i> Simpan Perubahan SO');
                hideModal('#modalEditOrder');
                tableOrders.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#10b981', timer: 2500, timerProgressBar: true });
            },
            error: function (xhr) {
                $('#btn_submit_edit_order').prop('disabled', false).html('<i class="bi bi-save2-fill me-1"></i> Simpan Perubahan SO');
                let msg = xhr.responseJSON ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON.errors)) : 'Gagal menyimpan perubahan.';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            }
        });
    });

    // ===== CANCEL ORDER WITH REASON =====
    function cancelOrderWithReason(orderId, nomorSo) {
        Swal.fire({
            title: `Batalkan Pesanan ${nomorSo}?`,
            html: `
                <div class="text-start">
                    <p class="text-muted small mb-2">Tindakan ini tidak dapat dibatalkan. Pesanan akan berstatus <strong class="text-danger">Dibatalkan</strong>.</p>
                    <label class="form-label small fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                    <textarea id="swal_alasan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan alasan pembatalan..." style="width:100%;"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Ya, Batalkan',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            preConfirm: () => {
                let alasan = document.getElementById('swal_alasan').value.trim();
                if (!alasan) {
                    Swal.showValidationMessage('Alasan pembatalan wajib diisi.');
                    return false;
                }
                return alasan;
            }
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:  `{{ url('marketing/sales-order') }}/${orderId}/cancel`,
                type: 'POST',
                data: JSON.stringify({ alasan_pembatalan: result.value }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    hideModal('#modalDetailOrder');
                    if (typeof tableOrders !== 'undefined' && tableOrders) {
                        tableOrders.ajax.reload(null, false);
                    }
                    let quotationNotice = '';
                    if (res.quotation_reopened && res.quotation_number) {
                        quotationNotice = `
                            <div class="alert alert-info border text-start mt-3 mb-0 p-2 small">
                                <i class="bi bi-info-circle-fill me-1 text-info"></i>
                                <strong>Surat Penawaran Terkait:</strong><br>
                                Penawaran <b>${res.quotation_number}</b> telah dibuka kembali ke status <span class="badge bg-secondary">Draft</span> dan siap untuk diperbaiki/direvisi kembali oleh tim marketing.
                            </div>
                        `;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Berhasil Dibatalkan',
                        html: `
                            <div class="text-secondary small mt-1">Status pesanan <b>${nomorSo}</b> telah resmi diubah menjadi <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dibatalkan</span>.</div>
                            ${quotationNotice}
                        `,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Selesai'
                    });
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal membatalkan pesanan.';
                    Swal.fire({ icon: 'error', title: 'Gagal Membatalkan', text: msg, confirmButtonColor: '#ef4444' });
                }
            });
        });
    }

    // ===== BUTTON EVENTS: BATALKAN PESANAN (DARI TABEL RIWAYAT & DETAIL MODAL) =====
    $(document).on('click', '.btn-cancel-so', function (e) {
        e.preventDefault();
        let orderId = $(this).data('id');
        let nomorSo = $(this).data('nomor') || ('SO #' + orderId);
        cancelOrderWithReason(orderId, nomorSo);
    });

    // ===== BUTTON EVENTS FROM DETAIL MODAL =====
    $('#btn_edit_order_from_detail').on('click', function () {
        let orderId = $(this).data('id');
        openEditModal(orderId);
    });

    $('#btn_cancel_order_from_detail').on('click', function () {
        let orderId = $(this).data('id');
        let rawNomor = $('#so_detail_nomor').text() || '';
        let nomorSo = rawNomor.replace('Nomor SO: ', '').replace('SO: ', '').trim() || ('SO #' + orderId);
        cancelOrderWithReason(orderId, nomorSo);
    });


    // ===== E-COMMERCE PRODUCT DETAIL MODAL & SLIDER LOGIC =====
    let currentModalProduct = null;
    let modalPhotosList = [];
    let activeModalPhotoIndex = 0;

    function setModalActivePhoto(index) {
        if (modalPhotosList.length === 0) {
            $('#modal_prod_main_img').attr('src', PLACEHOLDER_IMG);
            $('#btn_slider_prev, #btn_slider_next, #modal_prod_img_counter').addClass('d-none');
            $('#modal_prod_thumb_strip').empty();
            return;
        }

        if (index < 0) index = modalPhotosList.length - 1;
        if (index >= modalPhotosList.length) index = 0;

        activeModalPhotoIndex = index;
        let p = modalPhotosList[index];

        $('#modal_prod_main_img').fadeOut(70, function () {
            $(this).attr('src', p.url || PLACEHOLDER_IMG).fadeIn(90);
        });

        // Update active thumbnail
        $('.cinematic-thumb-item').removeClass('active');
        $(`.cinematic-thumb-item[data-index="${index}"]`).addClass('active');

        // Update counter
        $('#modal_counter_text').text(`${index + 1} / ${modalPhotosList.length}`);
    }

    $(document).on('click', '.btn-show-prod-detail', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let prodId = $(this).attr('data-id') || $(this).data('id');
        if (!prodId) {
            prodId = $(this).closest('[data-id]').attr('data-id');
        }
        if (!prodId) return;

        // Reset fields
        $('#modal_prod_title').text('Memuat detail...');
        $('#modal_prod_category').text('-');
        $('#modal_prod_brand').text('-');
        $('#modal_prod_sku').text('-');
        $('#modal_prod_price_retail').text('Rp 0,00');
        $('#modal_prod_price_grosir').text('Rp 0,00');
        $('#modal_prod_price_tender').text('Rp 0,00');
        $('#modal_input_qty').val(1);
        $('#modal_prod_thumb_strip').empty();

        showModal('#modalProductDetail');

        $.get(`{{ url('marketing/sales-order/product') }}/${prodId}/detail`, function (res) {
            let p = res.product;
            if (!p) return;

            currentModalProduct = p;

            // Badges & Header
            $('#modal_prod_type_badge').html(
                p.tipe_item === 'Bundle' 
                    ? '<i class="bi bi-collection-fill me-1"></i> Paket / Set Bundle'
                    : '<i class="bi bi-box-seam me-1"></i> Produk Stok Fisik'
            );
            $('#modal_prod_category').text(p.kategori_name);
            $('#modal_prod_brand').text(p.brand_name);
            $('#modal_prod_sku').text(p.kode_barang);
            $('#modal_prod_title').text(p.nama_barang);
            $('#modal_prod_alias').text(p.nama_alias ? `(${p.nama_alias})` : '');
            
            // Stock Badge & Button State
            if (Number(p.total_stok) > 0) {
                $('#modal_prod_stock_badge')
                    .removeClass('bg-danger-subtle text-danger border-danger-subtle')
                    .addClass('bg-success-subtle text-success border-success-subtle')
                    .html(`<i class="bi bi-check-circle-fill me-1"></i> Stok Tersedia (${Number(p.total_stok)} ${p.satuan_name})`);
                
                $('#modal_btn_add_cart')
                    .prop('disabled', false)
                    .removeClass('disabled')
                    .html('<i class="bi bi-cart-plus-fill me-1"></i> Tambah ke Keranjang');
                $('#modal_input_qty, #modal_btn_qty_inc, #modal_btn_qty_dec').prop('disabled', false);
            } else {
                $('#modal_prod_stock_badge')
                    .removeClass('bg-success-subtle text-success border-success-subtle')
                    .addClass('bg-danger-subtle text-danger border-danger-subtle')
                    .html(`<i class="bi bi-x-circle-fill me-1"></i> Stok Habis (0 ${p.satuan_name})`);
                
                $('#modal_btn_add_cart')
                    .prop('disabled', true)
                    .addClass('disabled')
                    .html('<i class="bi bi-slash-circle me-1"></i> Stok Sedang Habis');
                $('#modal_input_qty, #modal_btn_qty_inc, #modal_btn_qty_dec').prop('disabled', true);
            }

            // 3-Tier Prices
            $('#modal_prod_price_retail').text(formatRp(p.harga_retail));
            $('#modal_prod_price_grosir').text(formatRp(p.harga_grosir));
            $('#modal_prod_price_tender').text(formatRp(p.harga_tender));

            // Highlight currently selected tier
            $('.cinematic-tier-card').removeClass('active-selected-tier');
            if (currentPricingTier === 'Retail') {
                $('#modal_tier_retail').addClass('active-selected-tier');
            } else if (currentPricingTier === 'Grosir') {
                $('#modal_tier_grosir').addClass('active-selected-tier');
            } else if (currentPricingTier === 'Tender') {
                $('#modal_tier_tender').addClass('active-selected-tier');
            }

            // Physical Specs
            $('#modal_prod_ukuran').text(p.ukuran || '-');
            $('#modal_prod_warna_nama').text(p.warna_name || '-');
            if (p.warna_hex) {
                $('#modal_prod_warna_dot').css('background-color', p.warna_hex).show();
            } else {
                $('#modal_prod_warna_dot').hide();
            }

            let dim = (p.panjang > 0 || p.lebar > 0 || p.tinggi > 0)
                ? `${p.panjang} x ${p.lebar} x ${p.tinggi} cm`
                : '-';
            $('#modal_prod_dimensi').text(dim);
            $('#modal_prod_berat').text(p.berat > 0 ? `${p.berat} kg` : '-');

            // Bundle Components (Rendered as distinct, spaced modern cards)
            if (p.tipe_item === 'Bundle' && p.bundle_components && p.bundle_components.length > 0) {
                let compHtml = '';
                p.bundle_components.forEach((bc) => {
                    let bPhoto = bc.photo_url || PLACEHOLDER_IMG;
                    let stokStatusBadge = bc.stok_cukup 
                        ? `<span class="comp-stock-badge bg-success-subtle text-success border border-success-subtle shadow-xs"><i class="bi bi-check-circle-fill me-1.5"></i>Stok: ${bc.stok_tersedia} ${bc.satuan}</span>`
                        : `<span class="comp-stock-badge bg-danger-subtle text-danger border border-danger-subtle shadow-xs"><i class="bi bi-exclamation-triangle-fill me-1.5"></i>Stok Kurang (${bc.stok_tersedia} ${bc.satuan})</span>`;
                    compHtml += `
                        <div class="cinematic-component-card d-flex align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <img src="${bPhoto}" class="comp-thumb" alt="${bc.nama_barang}" onerror="window.handleImgError(this)">
                                <div class="min-w-0">
                                    <div class="comp-title text-truncate">${bc.nama_barang}</div>
                                    <div class="comp-meta">
                                        <span class="badge bg-secondary-subtle text-secondary px-1.5 py-0.5 rounded">${bc.kode_barang}</span>
                                        <span>&bull; Kebutuhan: <strong class="text-body">${bc.qty} ${bc.satuan}</strong> / paket</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                ${stokStatusBadge}
                            </div>
                        </div>
                    `;
                });
                $('#modal_prod_bundle_items').html(compHtml);
                $('#modal_prod_bundle_wrap').removeClass('d-none');
            } else {
                $('#modal_prod_bundle_wrap').addClass('d-none');
            }

            // Description
            $('#modal_prod_spesifikasi').text(p.spesifikasi || 'Tidak ada spesifikasi khusus.');
            $('#modal_prod_satuan_label').text(p.satuan_name || 'Pcs');

            // Photos Slider Setup
            modalPhotosList = p.photos || [];
            let thumbHtml = '';
            if (modalPhotosList.length > 0) {
                modalPhotosList.forEach((foto, fIdx) => {
                    thumbHtml += `
                        <img src="${foto.url}" class="cinematic-thumb-item ${fIdx === 0 ? 'active' : ''}" data-index="${fIdx}" alt="Thumbnail" onerror="window.handleImgError(this)">
                    `;
                });
                $('#modal_prod_thumb_strip').html(thumbHtml);

                if (modalPhotosList.length > 1) {
                    $('#btn_slider_prev, #btn_slider_next, #modal_prod_img_counter').removeClass('d-none');
                } else {
                    $('#btn_slider_prev, #btn_slider_next').addClass('d-none');
                    $('#modal_prod_img_counter').removeClass('d-none');
                }
            } else {
                $('#btn_slider_prev, #btn_slider_next, #modal_prod_img_counter').addClass('d-none');
            }

            setModalActivePhoto(0);
        }).fail(function (xhr, status, error) {
            console.error('Failed to load product detail:', status, error);
            $('#modal_prod_title').text('Gagal memuat detail produk');
        });
    });

    // Slider Next & Prev Buttons
    $('#btn_slider_prev').on('click', function (e) {
        e.stopPropagation();
        setModalActivePhoto(activeModalPhotoIndex - 1);
    });

    $('#btn_slider_next').on('click', function (e) {
        e.stopPropagation();
        setModalActivePhoto(activeModalPhotoIndex + 1);
    });

    // Thumbnail Click & Hover
    $(document).on('click mouseenter', '.cinematic-thumb-item', function () {
        let idx = $(this).data('index');
        setModalActivePhoto(idx);
    });

    // Modal Qty Stepper
    $('#modal_btn_qty_inc').on('click', function () {
        let val = Number($('#modal_input_qty').val()) || 1;
        let stock = Number(currentModalProduct ? currentModalProduct.total_stok : 0);
        if (stock > 0 && val >= stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Kuantitas pesanan telah mencapai batas maksimum persediaan yang tersedia (${stock}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }
        $('#modal_input_qty').val(val + 1);
    });

    $('#modal_btn_qty_dec').on('click', function () {
        let val = Number($('#modal_input_qty').val()) || 1;
        if (val > 1) {
            $('#modal_input_qty').val(val - 1);
        }
    });

    // Add to Cart from Modal
    $('#modal_btn_add_cart').on('click', function () {
        if (!currentModalProduct) return;

        let isJasa = (currentModalProduct.tipe_item === 'Jasa');
        let stock = Number(currentModalProduct.total_stok) || 0;
        if (!isJasa && stock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Persediaan produk "${currentModalProduct.nama_barang}" saat ini tidak tersedia di gudang.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let qty = Number($('#modal_input_qty').val()) || 1;
        if (qty <= 0) qty = 1;

        let existing = cart.find(c => String(c.id) === String(currentModalProduct.id));
        if (!isJasa && existing && (existing.qty + qty) > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Akumulasi kuantitas pesanan (${existing.qty} + ${qty}) melebihi sisa persediaan yang tersedia (${stock} ${currentModalProduct.satuan_name || 'Pcs'}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (!isJasa && !existing && qty > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Kuantitas yang diminta (${qty}) melebihi sisa persediaan yang tersedia (${stock} ${currentModalProduct.satuan_name || 'Pcs'}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let photoSrc = (modalPhotosList.length > 0) ? modalPhotosList[0].url : PLACEHOLDER_IMG;
        let unitPrice = Number(getItemUnitPrice(currentModalProduct, currentPricingTier));
        if (isNaN(unitPrice)) unitPrice = 0;

        if (existing) {
            existing.qty += qty;
            existing.harga_satuan = unitPrice;
            existing.subtotal = (unitPrice * existing.qty) - (existing.diskon_nominal || 0);
        } else {
            cart.push({
                id: currentModalProduct.id,
                kode: currentModalProduct.kode_barang,
                nama: currentModalProduct.nama_barang,
                satuan: currentModalProduct.satuan_name,
                photo_url: photoSrc,
                tipe_item: currentModalProduct.tipe_item || 'Stok',
                harga_retail: Number(currentModalProduct.harga_retail) || 0,
                harga_grosir: Number(currentModalProduct.harga_grosir) || 0,
                harga_tender: Number(currentModalProduct.harga_tender) || 0,
                harga_satuan: unitPrice,
                qty: qty,
                diskon_nominal: 0,
                subtotal: unitPrice * qty,
                stock: stock
            });
        }

        renderCart();
        saveCartToStorage();
        hideModal('#modalProductDetail');

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: `+${qty} ${currentModalProduct.nama_barang} masuk keranjang`
        });
    });

    $(document).on('click', '.btn-view-so', function () {
        let id = $(this).data('id');
        showOrderDetail(id);
    });

    $(document).on('click', '.btn-edit-so', function () {
        let id = $(this).data('id');
        openEditModal(id);
    });

    $(document).on('click', '#btn_print_order', function () {
        let id = $(this).data('id');
        if (id) {
            window.open(`/marketing/sales-order/${id}/print-invoice`, '_blank');
        }
    });

    // ===== INITIALIZE SELECT2 FOR SEARCHABLE DROPDOWNS =====
    function initCustomerSelect2() {
        if ($.fn.select2) {
            // Select2 inside Cart Drawer
            $('#cart_customer_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#offcanvasCart'),
                placeholder: '-- Pelanggan Umum (Walk-in) --',
                allowClear: true,
                width: '100%'
            });

            $('#cart_payment_method_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#offcanvasCart'),
                placeholder: '-- Pilih Metode Pembayaran --',
                allowClear: true,
                width: '100%'
            });

            $('#cart_payment_term_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#offcanvasCart'),
                placeholder: '-- Pilih Tenor (Cash / Kredit) --',
                allowClear: true,
                width: '100%'
            });

            // Select2 inside Checkout Modal
            $('#checkout_customer_select').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCheckoutOrder'),
                placeholder: '-- Pelanggan Umum (Walk-in) --',
                allowClear: true,
                width: '100%'
            });

            $('#checkout_payment_method_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCheckoutOrder'),
                placeholder: '-- Pilih Metode Pembayaran --',
                allowClear: true,
                width: '100%'
            });

            $('#checkout_payment_term_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCheckoutOrder'),
                placeholder: '-- Pilih Tenor (Cash / Kredit) --',
                allowClear: true,
                width: '100%'
            });

            // Select2 inside Edit Modal - Customer
            $('#edit_customer_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditOrder'),
                placeholder: '-- Pilih Pelanggan --',
                allowClear: true,
                width: '100%'
            });

            // Select2 inside Edit Modal - Payment Method
            $('#edit_payment_method_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditOrder'),
                placeholder: '-- Pilih Metode Pembayaran --',
                allowClear: true,
                width: '100%'
            });

            // Select2 inside Edit Modal - Payment Term
            $('#edit_payment_term_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditOrder'),
                placeholder: '-- Pilih Termin Pembayaran --',
                allowClear: true,
                width: '100%'
            });
        }
    }

    // Initial load
    try {
        loadProducts(1);
    } catch (e) {
        console.error('loadProducts error:', e);
    }

    try {
        loadCartFromStorage();
    } catch (e) {
        console.error('loadCartFromStorage error:', e);
    }

    try {
        initCustomerSelect2();
        let initialCustOpt = $('#cart_customer_id').find('option:selected');
        if (initialCustOpt.val() && !$('#cart_alamat_pengiriman').val()) {
            $('#cart_alamat_pengiriman').val(initialCustOpt.data('alamat') || initialCustOpt.attr('data-alamat') || '');
        }
    } catch (e) {
        console.warn('initCustomerSelect2 warning:', e);
    }
});
</script>
