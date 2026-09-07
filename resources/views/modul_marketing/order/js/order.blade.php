<script>
$(document).ready(function () {
    // ===== CSRF SETUP FOR ALL AJAX =====
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });
    // ===== STATE VARIABLES =====
    let currentPricingTier = 'Retail'; // 'Retail' | 'Grosir' | 'Tender'
    let currentCategoryFilter = 'all';
    let currentBrandFilter = 'all';
    let currentTipeFilter = 'all';
    let currentSearchKeyword = '';
    let currentPage = 1;
    let cart = []; // Array of { id, kode, nama, photo_url, satuan, stock, harga_retail, harga_grosir, harga_tender, qty, diskon_nominal, subtotal }

    // ===== CART STORAGE PERSISTENCE (PREVENTS DATA LOSS ON REFRESH / PAGE CHANGE) =====
    const CART_STORAGE_KEY = 'erp_sales_order_cart_{{ auth()->id() }}_{{ $activeCompany->id ?? 1 }}';

    function saveCartToStorage() {
        try {
            let data = {
                cart: cart,
                tier: currentPricingTier,
                customer_id: $('#cart_customer_id').val() || '',
                nama_walkin: $('#cart_nama_walkin').val() || '',
                telepon_walkin: $('#cart_telepon_walkin').val() || '',
                payment_term_id: $('#cart_payment_term_id').val() || '',
                diskon_mode: $('input[name="diskon_mode"]:checked').val() || 'Nominal',
                diskon_val: $('#cart_diskon_val').val() || 0,
                pajak_type: $('#cart_pajak_type').val() || 'none',
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
                    if (parsed.diskon_mode) {
                        $(`input[name="diskon_mode"][value="${parsed.diskon_mode}"]`).prop('checked', true);
                        $('#cart_diskon_suffix').text(parsed.diskon_mode === 'Persen' ? '%' : 'Rp');
                    }
                    if (parsed.diskon_val) {
                        $('#cart_diskon_val').val(parsed.diskon_val);
                    }
                    if (parsed.pajak_type) {
                        $('#cart_pajak_type').val(parsed.pajak_type);
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
    }

    // ===== GLOBAL IMAGE PLACEHOLDER (PREVENTS INFINITE LOOP ON 404) =====
    const PLACEHOLDER_IMG = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300' fill='%23f1f5f9'><rect width='300' height='300' fill='%23f1f5f9'/><g transform='translate(110, 100)'><rect x='0' y='0' width='80' height='80' rx='16' fill='%23e2e8f0'/><path d='M25 50 L38 35 L48 45 L58 32 L68 46 L68 56 L25 56 Z' fill='%2394a3b8'/><circle cx='34' cy='28' r='6' fill='%23cbd5e1'/></g><text x='150' y='210' font-family='sans-serif' font-size='13' font-weight='600' fill='%2394a3b8' text-anchor='middle'>Tidak Ada Foto</text></svg>";

    // ===== FORMAT RUPIAH & RIBUAN HELPER =====
    function formatRp(amount) {
        let num = Number(amount) || 0;
        return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatThousands(val) {
        if (val === undefined || val === null || val === '') return '';
        let clean = val.toString().replace(/[^0-9]/g, '');
        if (!clean) return '';
        return Number(clean).toLocaleString('id-ID');
    }

    function unformatThousands(val) {
        if (!val) return 0;
        let clean = val.toString().replace(/[^0-9]/g, '');
        return Number(clean) || 0;
    }

    // ===== GET UNIT PRICE BASED ON ACTIVE TIER =====
    function getItemUnitPrice(item, tier) {
        if (tier === 'Grosir') return Number(item.harga_grosir) || Number(item.harga_retail) || 0;
        if (tier === 'Tender') return Number(item.harga_tender) || Number(item.harga_retail) || 0;
        return Number(item.harga_retail) || 0;
    }

    // ===== LOAD PRODUCTS (TOKOPEDIA CATALOG GRID) =====
    function loadProducts(page = 1) {
        currentPage = page;
        $('#product_grid_container').html(`
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-success" role="status"></div>
                <div class="text-muted small mt-2">Memuat katalog produk...</div>
            </div>
        `);

        $.ajax({
            url: "{{ route('marketing.sales-order.products') }}",
            type: "GET",
            data: {
                page: currentPage,
                search: currentSearchKeyword,
                kategori_id: currentCategoryFilter,
                brand_id: currentBrandFilter,
                tipe_item: currentTipeFilter,
                per_page: 24
            },
            success: function (res) {
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
                    let photoSrc = p.photo_url ? p.photo_url : PLACEHOLDER_IMG;
                    let isOutOfStock = (Number(p.total_stok) <= 0);
                    let stockBadge = !isOutOfStock 
                        ? `<span class="tkpd-badge-stock bg-success text-white">Stok: ${Number(p.total_stok)}</span>`
                        : `<span class="tkpd-badge-stock bg-danger text-white">Stok Habis</span>`;
                    
                    let typeBadge = p.tipe_item === 'Bundle' 
                        ? `<span class="tkpd-badge-type bg-primary text-white">Bundle</span>`
                        : '';

                    let activePrice = getItemUnitPrice(p, currentPricingTier);
                    let priceClass = currentPricingTier === 'Retail' ? 'tkpd-price-retail' : (currentPricingTier === 'Grosir' ? 'tkpd-price-grosir' : 'tkpd-price-tender');

                    let btnAction = isOutOfStock
                        ? `<button class="tkpd-btn-add disabled" disabled title="Stok barang sedang kosong">
                               <i class="bi bi-slash-circle"></i> Stok Habis
                           </button>`
                        : `<button class="tkpd-btn-add btn-add-cart" 
                                data-id="${p.id}" 
                                data-kode="${p.kode_barang}" 
                                data-nama="${p.nama_barang}" 
                                data-satuan="${p.satuan_name}" 
                                data-photo="${photoSrc}" 
                                data-retail="${p.harga_retail}" 
                                data-grosir="${p.harga_grosir}" 
                                data-tender="${p.harga_tender}" 
                                data-stock="${p.total_stok}">
                                <i class="bi bi-cart-plus-fill"></i> + Keranjang
                           </button>`;

                    html += `
                        <div class="tkpd-card ${isOutOfStock ? 'out-of-stock' : ''}">
                            <div class="tkpd-card-img-wrap btn-show-prod-detail cursor-pointer" data-id="${p.id}" title="Klik untuk lihat detail & foto">
                                ${stockBadge}
                                ${typeBadge}
                                <img src="${photoSrc}" class="tkpd-card-img" alt="${p.nama_barang}" onerror="this.onerror=null; this.src=PLACEHOLDER_IMG;">
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
            },
            error: function () {
                $('#product_grid_container').html(`
                    <div class="col-12 text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                        <p class="mt-2">Gagal memuat katalog produk. Periksa koneksi Anda.</p>
                    </div>
                `);
            }
        });
    }

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
            loadProducts(1);
        }, 400);
    });

    // ===== CATEGORY PILLS CLICK =====
    $(document).on('click', '.tkpd-cat-pill', function () {
        $('.tkpd-cat-pill').removeClass('active');
        $(this).addClass('active');
        currentCategoryFilter = $(this).data('cat-id');
        loadProducts(1);
    });

    // ===== BRAND & TIPE FILTERS =====
    $('#catalog_brand_filter').on('change', function () {
        currentBrandFilter = $(this).val();
        loadProducts(1);
    });

    $('#catalog_tipe_filter').on('change', function () {
        currentTipeFilter = $(this).val();
        loadProducts(1);
    });

    // ===== PRICING TIER SWITCHER (RETAIL / GROSIR / TENDER) =====
    $('.tkpd-tier-btn').on('click', function () {
        let tier = $(this).data('tier');
        if (tier === currentPricingTier) return;

        currentPricingTier = tier;
        $('.tkpd-tier-btn').removeClass('active-retail active-grosir active-tender');
        $(this).addClass('active-' + tier.toLowerCase());
        $('#cart_tier_badge').text(tier);

        // Update product catalog view prices
        loadProducts(currentPage);

        // Recalculate cart item prices
        updateCartTierPrices();
    });

    // ===== CART MANAGEMENT FUNCTIONS =====
    function updateCartTierPrices() {
        cart.forEach(item => {
            let unitPrice = getItemUnitPrice(item, currentPricingTier);
            item.harga_satuan = unitPrice;
            item.subtotal = (unitPrice * item.qty) - (item.diskon_nominal || 0);
        });
        renderCart();
    }

    function addToCart(product) {
        let stock = Number(product.stock) || 0;
        if (stock <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Persediaan produk "${product.nama}" saat ini tidak tersedia di gudang.`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let existing = cart.find(c => c.id === product.id);
        let unitPrice = getItemUnitPrice(product, currentPricingTier);

        if (existing) {
            if (existing.qty + 1 > stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Mencukupi',
                    text: `Kuantitas pesanan untuk "${product.nama}" melebihi sisa persediaan yang tersedia (${stock} ${product.satuan || 'Pcs'}).`,
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }
            existing.qty += 1;
            existing.subtotal = (existing.harga_satuan * existing.qty) - (existing.diskon_nominal || 0);
        } else {
            cart.push({
                id: product.id,
                kode: product.kode,
                nama: product.nama,
                satuan: product.satuan,
                photo_url: product.photo,
                harga_retail: product.retail,
                harga_grosir: product.grosir,
                harga_tender: product.tender,
                harga_satuan: unitPrice,
                qty: 1,
                diskon_nominal: 0,
                subtotal: unitPrice,
                stock: stock
            });
        }

        renderCart();

        // Bounce animation on floating cart icon
        $('#cart_icon_bounce, #btn_floating_cart').addClass('cart-animate-bounce');
        setTimeout(() => {
            $('#cart_icon_bounce, #btn_floating_cart').removeClass('cart-animate-bounce');
        }, 500);

        // Micro-toast notification
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1200,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: `+1 ${product.nama} masuk keranjang`
        });
    }

    $(document).on('click', '.btn-add-cart', function () {
        let p = {
            id: $(this).data('id'),
            kode: $(this).data('kode'),
            nama: $(this).data('nama'),
            satuan: $(this).data('satuan'),
            photo: $(this).data('photo'),
            retail: $(this).data('retail'),
            grosir: $(this).data('grosir'),
            tender: $(this).data('tender'),
            stock: $(this).data('stock')
        };
        addToCart(p);
    });

    function renderCart() {
        let totalItems = 0;
        let totalQty = 0;
        let subtotal = 0;
        let html = '';

        if (cart.length === 0) {
            html = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 opacity-50 text-secondary"></i>
                    <div class="fw-bold mt-2">Keranjang masih kosong</div>
                    <div class="text-body-secondary small">Klik tombol <strong>+ Keranjang</strong> pada produk di katalog.</div>
                </div>
            `;
            $('#btn_open_checkout').prop('disabled', true);
        } else {
            $('#btn_open_checkout').prop('disabled', false);

            cart.forEach((item, index) => {
                totalQty += item.qty;
                subtotal += item.subtotal;
                let itemPhoto = item.photo_url || PLACEHOLDER_IMG;

                html += `
                    <div class="tkpd-cart-item">
                        <img src="${itemPhoto}" class="tkpd-cart-item-img" onerror="this.onerror=null; this.src=PLACEHOLDER_IMG;">
                        <div class="tkpd-cart-item-info">
                            <div class="tkpd-cart-item-name" title="${item.nama}">${item.nama}</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="tkpd-cart-item-price">${formatRp(item.harga_satuan)}</div>
                                <div class="tkpd-stepper">
                                    <button type="button" class="tkpd-stepper-btn btn-cart-dec" data-index="${index}">-</button>
                                    <input type="text" class="tkpd-stepper-input cart-qty-input" data-index="${index}" value="${item.qty}">
                                    <button type="button" class="tkpd-stepper-btn btn-cart-inc" data-index="${index}">+</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-link text-danger p-0 ms-1 btn-cart-remove" data-index="${index}" title="Hapus item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
            });
        }

        $('#cart_items_list').html(html);
        $('#cart_badge_count').text(cart.length);
        $('#cart_badge_header').text(totalQty);
        $('#cart_badge_floating').text(totalQty);
        $('#cart_summary_subtotal').text(formatRp(subtotal));

        // Calculate Cart Totals
        calculateCartTotals(subtotal);
    }

    function calculateCartTotals(subtotal) {
        // Diskon — pakai radio button mode
        let diskonType = $('input[name="diskon_mode"]:checked').val() || 'Nominal';
        let diskonVal  = (diskonType === 'Persen')
            ? (parseFloat($('#cart_diskon_val').val()) || 0)
            : unformatThousands($('#cart_diskon_val').val());
        let diskonNominal = 0;

        if (diskonType === 'Persen') {
            diskonNominal = (subtotal * diskonVal) / 100;
            $('#cart_diskon_suffix').text('%');
            $('#cart_summary_diskon_label').text(diskonVal > 0 ? `(${diskonVal}%)` : '');
        } else {
            diskonNominal = diskonVal;
            $('#cart_diskon_suffix').text('Rp');
            $('#cart_summary_diskon_label').text('');
        }

        let afterDiscount = Math.max(0, subtotal - diskonNominal);

        // PPN — baca tarif dari data-tarif master pajak
        let selectedPpnOption = $('#cart_pajak_type option:selected');
        let pajakTarif   = parseFloat(selectedPpnOption.data('tarif')) || 0;
        let pajakNominal = pajakTarif > 0 ? (afterDiscount * pajakTarif / 100) : 0;
        let pajakLabel   = pajakTarif > 0 ? `(${pajakTarif}%)` : '';
        $('#cart_summary_pajak_label').text(pajakLabel);

        // Biaya Pengiriman (Ongkir / Biaya Lain)
        let biayaPengiriman = unformatThousands($('#cart_biaya_pengiriman').val());

        let grandTotal = afterDiscount + pajakNominal + biayaPengiriman;

        // Down Payment (DP / Uang Muka) Calculation
        let useDp = $('#cart_use_dp').is(':checked');
        let dpMode = $('input[name="dp_mode"]:checked').val() || 'Nominal';
        let dpVal = (dpMode === 'Persen')
            ? (parseFloat($('#cart_dp_val').val()) || 0)
            : unformatThousands($('#cart_dp_val').val());
        let dpNominal = 0;

        if (useDp && grandTotal > 0) {
            if (dpMode === 'Persen') {
                dpNominal = (grandTotal * dpVal) / 100;
                $('#cart_dp_suffix').text('%');
                $('#cart_summary_dp_label').text(dpVal > 0 ? `(${dpVal}%)` : '');
            } else {
                dpNominal = Math.min(grandTotal, dpVal);
                $('#cart_dp_suffix').text('Rp');
                $('#cart_summary_dp_label').text('');
            }

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

        $('#cart_summary_diskon').text('- ' + formatRp(diskonNominal));
        $('#cart_summary_pajak').text('+ ' + formatRp(pajakNominal));
        $('#cart_summary_ongkir').text('+ ' + formatRp(biayaPengiriman));
        $('#cart_summary_total').text(formatRp(grandTotal));
        $('#cart_floating_nominal').text(formatRp(grandTotal));

        // Jika diskon persen, tampilkan preview nilai nominalnya
        if (diskonType === 'Persen' && diskonVal > 0 && subtotal > 0) {
            $('#cart_diskon_preview').text('- ' + formatRp(diskonNominal));
            $('#cart_diskon_preview_row').css('display', 'flex');
        } else {
            $('#cart_diskon_preview_row').css('display', 'none');
        }

        // Tampilkan preview nilai PPN jika ada
        if (pajakTarif > 0 && pajakNominal > 0) {
            $('#cart_pajak_preview').text('+ ' + formatRp(pajakNominal));
            $('#cart_pajak_preview_row').css('display', 'flex');
        } else {
            $('#cart_pajak_preview_row').css('display', 'none');
        }

        // Store for checkout submission
        let pajakId = selectedPpnOption.val();
        let sisaFinal = useDp ? Math.max(0, grandTotal - dpNominal) : grandTotal;
        window._currentCartCalculations = {
            subtotal:               subtotal,
            diskon_tipe:            diskonType,
            diskon_nilai:           diskonVal,
            diskon_nominal:         diskonNominal,
            ppn_pajak_id:           (pajakId !== 'none') ? pajakId : null,
            pajak_tipe:             pajakTarif > 0 ? 'PPN_Exclude' : 'None',
            pajak_persen:           pajakTarif,
            pajak_nominal:          pajakNominal,
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

        // Simpan state keranjang ke localStorage agar tidak hilang saat navigasi/refresh
        saveCartToStorage();
    }

    // Stepper Handlers
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
        cart[idx].subtotal = (cart[idx].harga_satuan * cart[idx].qty) - (cart[idx].diskon_nominal || 0);
        renderCart();
    });

    $(document).on('click', '.btn-cart-dec', function () {
        let idx = $(this).data('index');
        if (cart[idx].qty > 1) {
            cart[idx].qty -= 1;
            cart[idx].subtotal = (cart[idx].harga_satuan * cart[idx].qty) - (cart[idx].diskon_nominal || 0);
        } else {
            cart.splice(idx, 1);
        }
        renderCart();
    });

    $(document).on('change', '.cart-qty-input', function () {
        let idx = $(this).data('index');
        let val = Number($(this).val()) || 1;
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
        cart[idx].subtotal = (cart[idx].harga_satuan * cart[idx].qty) - (cart[idx].diskon_nominal || 0);
        renderCart();
    });

    $(document).on('click', '.btn-cart-remove', function () {
        let idx = $(this).data('index');
        cart.splice(idx, 1);
        renderCart();
    });

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
                cart = [];
                localStorage.removeItem(CART_STORAGE_KEY);
                renderCart();
            }
        });
    });

    // Update diskon suffix saat toggle Rp/% berubah
    $('input[name="diskon_mode"]').on('change', function () {
        let mode = $(this).val();
        $('#cart_diskon_suffix').text(mode === 'Persen' ? '%' : 'Rp');
        $('#cart_diskon_val').val(0);
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $(document).on('input', '#cart_diskon_val', function () {
        let diskonMode = $('input[name="diskon_mode"]:checked').val() || 'Nominal';
        if (diskonMode === 'Nominal') {
            let raw = unformatThousands($(this).val());
            $(this).val(raw > 0 ? formatThousands(raw) : (raw === 0 && $(this).val().trim() !== '' ? '0' : ''));
        }
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $(document).on('input', '#cart_biaya_pengiriman', function () {
        let raw = unformatThousands($(this).val());
        $(this).val(raw > 0 ? formatThousands(raw) : (raw === 0 && $(this).val().trim() !== '' ? '0' : ''));
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $(document).on('input', '#cart_dp_val', function () {
        let dpMode = $('input[name="dp_mode"]:checked').val() || 'Nominal';
        if (dpMode === 'Nominal') {
            let raw = unformatThousands($(this).val());
            $(this).val(raw > 0 ? formatThousands(raw) : (raw === 0 && $(this).val().trim() !== '' ? '0' : ''));
        }
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $('#cart_pajak_type').on('change', function () {
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    // Customer & Walk-in listeners in Cart Drawer
    $('#cart_customer_id').on('change', function () {
        let custId = $(this).val();
        if (custId) {
            let opt = $(this).find('option:selected');
            $('#cart_walkin_wrap').hide();
            $('#checkout_customer_select').val(custId);
            $('#checkout_walkin_wrap').hide();
            $('#checkout_telepon').val(opt.data('telepon') || '');
            $('#checkout_alamat').val(opt.data('alamat') || '');
        } else {
            $('#cart_walkin_wrap').show();
            $('#checkout_customer_select').val('');
            $('#checkout_walkin_wrap').show();
            $('#checkout_nama_walkin').val($('#cart_nama_walkin').val() || 'Pelanggan Umum');
            $('#checkout_telepon').val($('#cart_telepon_walkin').val() || '');
        }
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $('#cart_nama_walkin, #cart_telepon_walkin, #cart_payment_term_id').on('input change', function () {
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    // Down Payment (DP) Toggle Switch & Listeners
    $('#cart_use_dp').on('change', function () {
        if ($(this).is(':checked')) {
            $('#cart_dp_container').slideDown(150);
        } else {
            $('#cart_dp_container').slideUp(150);
        }
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    $('input[name="dp_mode"]').on('change', function () {
        let mode = $(this).val();
        $('#cart_dp_suffix').text(mode === 'Persen' ? '%' : 'Rp');
        $('#cart_dp_val').val(0);
        let subtotal = cart.reduce((acc, c) => acc + c.subtotal, 0);
        calculateCartTotals(subtotal);
    });

    // ===== CHECKOUT PROCESS =====
    $('#btn_open_checkout').on('click', function () {
        if (cart.length === 0) return;

        // Tutup offcanvas drawer cart jika terbuka
        const offcanvasEl = document.getElementById('offcanvasCart');
        if (offcanvasEl) {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvasInstance) {
                offcanvasInstance.hide();
            }
        }

        let calc = window._currentCartCalculations;
        let diskonLabel = calc.diskon_tipe === 'Persen'
            ? `(${calc.diskon_nilai}%)`
            : '';
        let pajakLabel = calc.pajak_persen > 0 ? `(${calc.pajak_persen}%)` : '';

        // Sinkronkan pilihan Customer & Tenor dari Drawer ke Modal Checkout
        let custId = $('#cart_customer_id').val();
        $('#checkout_customer_select').val(custId).trigger('change.select2');
        if (custId) {
            let opt = $('#cart_customer_id').find('option:selected');
            $('#checkout_walkin_wrap').hide();
            $('#checkout_telepon').val(opt.data('telepon') || '');
            $('#checkout_alamat').val(opt.data('alamat') || '');
        } else {
            $('#checkout_walkin_wrap').show();
            $('#checkout_nama_walkin').val($('#cart_nama_walkin').val() || 'Pelanggan Umum');
            $('#checkout_telepon').val($('#cart_telepon_walkin').val() || '');
        }

        let termId = $('#cart_payment_term_id').val();
        if (termId) {
            $('#checkout_payment_term_id').val(termId);
        }

        $('#checkout_summary_items').text(cart.length + ' Macam Produk');
        $('#checkout_summary_subtotal').text(formatRp(calc.subtotal));
        $('#checkout_summary_diskon').text('- ' + formatRp(calc.diskon_nominal) + (diskonLabel ? ' ' + diskonLabel : ''));
        $('#checkout_summary_pajak').text('+ ' + formatRp(calc.pajak_nominal));
        $('#checkout_summary_pajak_pct').text(pajakLabel);
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

        $('#modalCheckoutOrder').modal('show');
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
        let walkinName = $('#checkout_nama_walkin').val();
        let paymentTermId = $('#checkout_payment_term_id').val() || $('#cart_payment_term_id').val() || null;

        let orderData = {
            pricing_tier: currentPricingTier,
            customer_id: customerId || null,
            nama_customer_walkin: customerId ? null : (walkinName || 'Pelanggan Umum'),
            telepon_customer_walkin: $('#checkout_telepon').val(),
            alamat_pengiriman: $('#checkout_alamat').val(),
            payment_method_id: $('#checkout_payment_method_id').val() || null,
            payment_term_id: paymentTermId,
            catatan: $('#checkout_catatan').val(),
            subtotal:               calc.subtotal,
            diskon_tipe:            calc.diskon_tipe,
            diskon_nilai:           calc.diskon_nilai,
            diskon_nominal:         calc.diskon_nominal,
            ppn_pajak_id:           calc.pajak_id || null,
            pajak_tipe:             calc.pajak_tipe,
            pajak_persen:           calc.pajak_persen,
            pajak_nominal:          calc.pajak_nominal,
            biaya_pengiriman:       calc.biaya_pengiriman || 0,
            total:                  calc.total,
            grand_total:            calc.grand_total,
            tipe_dp:                calc.use_dp ? calc.tipe_dp : null,
            dp_persen:              calc.use_dp ? calc.dp_persen : 0,
            dp_nominal:             calc.use_dp ? calc.dp_nominal : 0,
            sisa_tagihan:           calc.use_dp ? calc.sisa_tagihan : calc.total,
            items: cart.map(item => ({
                barang_id: item.id,
                qty: item.qty,
                harga_satuan: item.harga_satuan,
                diskon_nominal: item.diskon_nominal || 0,
                subtotal: item.subtotal
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
                $('#modalCheckoutOrder').modal('hide');

                // Clear Cart & LocalStorage
                cart = [];
                localStorage.removeItem(CART_STORAGE_KEY);
                renderCart();

                Swal.fire({
                    title: 'Pesanan Berhasil Dibuat!',
                    text: `Nomor Surat Pesanan: ${res.order.nomor_so}`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-printer"></i> Lihat & Cetak Faktur',
                    cancelButtonText: 'Tutup'
                }).then(action => {
                    if (action.isConfirmed) {
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
    const tableOrders = $('#tableSalesOrders').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('marketing.sales-order.history-data') }}",
            data: function(d) {
                d.status_filter = $('#history_status_filter').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX Error:', xhr.status, xhr.responseText);
                let msg = 'Gagal memuat data.';
                try {
                    let resp = JSON.parse(xhr.responseText);
                    msg = resp.message || msg;
                } catch(e) {}
                Swal.fire({
                    icon: 'error',
                    title: 'Error Memuat Data',
                    text: msg,
                    confirmButtonColor: '#10b981'
                });
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
                    let canEdit   = !['Completed', 'Cancelled'].includes(row.status);
                    let editBtn   = canEdit
                        ? `<button class="btn btn-sm btn-outline-warning btn-edit-so" data-id="${row.id}" title="Edit Pesanan"><i class="bi bi-pencil-square"></i></button>`
                        : '';
                    let cancelBtn = canEdit
                        ? `<button class="btn btn-sm btn-outline-danger btn-cancel-so" data-id="${row.id}" data-nomor="${row.nomor_so}" title="Batalkan Pesanan"><i class="bi bi-x-circle"></i></button>`
                        : '';
                    return `
                        <div class="action-btn-group">
                            <button class="btn btn-sm btn-outline-primary btn-view-so" data-id="${row.id}" title="Lihat Faktur">
                                <i class="bi bi-eye me-1"></i> Detail
                            </button>
                            <a href="/marketing/sales-order/${row.id}/print-invoice" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak Faktur (Tab Baru)">
                                <i class="bi bi-printer"></i>
                            </a>
                            ${editBtn}
                            ${cancelBtn}
                        </div>
                    `;
                }
            }
        ]
    });

    $('#history_status_filter').on('change', function() {
        tableOrders.ajax.reload();
    });

    // ===== HELPER: SHOW/HIDE EDIT & CANCEL BUTTONS IN DETAIL MODAL =====
    function setDetailModalActions(order) {
        const editable   = !['Completed', 'Cancelled'].includes(order.status);
        const cancellable = !['Completed', 'Cancelled'].includes(order.status);

        // Store current order id on the buttons
        $('#btn_print_order').data('id', order.id);
        $('#btn_edit_order_from_detail').data('id', order.id).toggleClass('d-none', !editable);
        $('#btn_cancel_order_from_detail').data('id', order.id).toggleClass('d-none', !cancellable);
    }

    // ===== SHOW ORDER DETAIL MODAL =====
    function showOrderDetail(orderId) {
        $.get(`/marketing/sales-order/${orderId}/detail`, function (res) {
            let o = res.order;
            if (!o) return;

            let compNama = o.company ? o.company.nama_perusahaan : (o.branch ? o.branch.nama_cabang : 'PT. PERSADA AMAN SENTOSA');
            let compAlmt = o.company && o.company.address ? o.company.address : (o.branch && o.branch.address ? o.branch.address : 'Jl Cinere Raya No 40-41, Depok, Jawa Barat');
            
            let compPhone = o.company ? (o.company.phone || o.company.telepon) : (o.branch ? (o.branch.phone || o.branch.telepon) : '');
            let compEmail = o.company ? o.company.email : (o.branch ? o.branch.email : '');
            let compNpwp  = o.company ? o.company.npwp : (o.branch ? o.branch.npwp : '');
            
            let kontakArr = [];
            if (compPhone) kontakArr.push(`Telp: ${compPhone}`);
            if (compEmail) kontakArr.push(`Email: ${compEmail}`);
            if (compNpwp) kontakArr.push(`NPWP: ${compNpwp}`);
            
            $('#so_detail_company_nama').text(compNama);
            $('#so_detail_company_alamat').text(compAlmt);
            $('#so_detail_company_kontak').text(kontakArr.length > 0 ? kontakArr.join(' | ') : '');

            let compLogo = o.company && o.company.logo ? `/storage/${o.company.logo}` : '{{ asset("gambar/logo.png") }}';
            $('#so_detail_company_logo').attr('src', compLogo);

            $('#so_detail_nomor').text(`Nomor SO: ${o.nomor_so}`);
            let tgl = o.tanggal_so ? new Date(o.tanggal_so).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) : '-';
            $('#so_detail_tanggal').html(`<i class="bi bi-calendar3 me-1"></i> Tanggal: ${tgl}`);
            $('#so_detail_tier').text(o.pricing_tier || 'Retail');
            let salesName = (o.creator && o.creator.name) ? o.creator.name : 'Marketing';
            $('#so_detail_sales').text(salesName);
            $('#so_detail_sales_info').text(salesName);

            let statusMap = {
                'Draft': 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                'Confirmed': 'bg-primary-subtle text-primary border border-primary-subtle',
                'Processing': 'bg-warning-subtle text-warning border border-warning-subtle',
                'Delivered': 'bg-info-subtle text-info border border-info-subtle',
                'Completed': 'bg-success-subtle text-success border border-success-subtle',
                'Cancelled': 'bg-danger-subtle text-danger border border-danger-subtle'
            };
            let statusLabel = {
                'Draft': 'Draft',
                'Confirmed': 'Terkonfirmasi',
                'Processing': 'Diproses',
                'Delivered': 'Dikirim',
                'Completed': 'Selesai',
                'Cancelled': 'Dibatalkan'
            };
            let stCls = statusMap[o.status] || 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            $('#so_detail_status_badge').html(`<span class="badge ${stCls} px-3 py-1.5 rounded-pill fw-semibold font-monospace">${statusLabel[o.status] || o.status}</span>`);

            if (o.customer && o.customer.nama_perusahaan) {
                $('#so_detail_customer_pt').text(o.customer.nama_perusahaan);
                $('#so_detail_instansi_wrap').show();
            } else {
                $('#so_detail_instansi_wrap').hide();
            }

            let custName = o.customer ? o.customer.nama_customer : (o.nama_customer_walkin || 'Pelanggan Umum');
            $('#so_detail_customer_nama').text(custName);
            $('#so_detail_customer_telepon').text(o.telepon_customer_walkin || (o.customer && o.customer.telepon ? o.customer.telepon : '-'));
            $('#so_detail_customer_alamat').text(o.alamat_pengiriman || (o.customer && o.customer.alamat ? o.customer.alamat : 'Diambil di Tempat'));

            $('#so_detail_sig_buyer').text(`( ${custName} )`);
            $('#so_detail_sig_company').text(`( ${salesName} )`);

            $('#so_detail_metode_bayar').text(o.payment_method ? o.payment_method.nama_metode : 'Cash / Tunai');
            $('#so_detail_termin').text(o.payment_term ? o.payment_term.nama_term : 'Langsung (Cash)');
            $('#so_detail_catatan').text(o.catatan || '-');

            let tbodyHtml = '';
            (o.details || []).forEach((d, idx) => {
                let brgNama = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : 'Item');
                let brgSku  = d.barang ? d.barang.kode_barang : '-';
                let satuanNama = d.nama_satuan || (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'Pcs');
                tbodyHtml += `
                    <tr>
                        <td class="text-center font-monospace text-muted small">${idx + 1}</td>
                        <td>
                            <div class="fw-bold text-body-emphasis">${brgNama}</div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5 rounded" style="font-size: 0.75rem;">SKU: ${brgSku}</span>
                            ${d.deskripsi_item ? `<div class="text-body-secondary small mt-1">${d.deskripsi_item}</div>` : ''}
                        </td>
                        <td class="text-center font-monospace fw-semibold">${Number(d.qty)} ${satuanNama}</td>
                        <td class="text-end font-monospace">Rp ${formatThousands(d.harga_satuan)}</td>
                        <td class="text-end font-monospace text-danger">${Number(d.diskon_nominal) > 0 ? '- Rp ' + formatThousands(d.diskon_nominal) : '-'}</td>
                        <td class="text-end font-monospace fw-bold text-body-emphasis">Rp ${formatThousands(d.subtotal)}</td>
                    </tr>
                `;
            });
            $('#so_detail_items_tbody').html(tbodyHtml);

            // Kalkulasi total & rincian finansial
            let grandTotal   = parseFloat(o.grand_total || 0);
            let total        = parseFloat(o.total || 0);
            let nilaiPpn     = parseFloat(o.nilai_ppn || 0);
            let pajakNominal = parseFloat(o.pajak_nominal || 0);
            let biayaKirim   = parseFloat(o.biaya_pengiriman || 0);
            let dpNominal    = parseFloat(o.dp_nominal || 0);
            let dpPersen     = parseFloat(o.dp_persen || 0);
            let sisaTagihan  = parseFloat(o.sisa_tagihan || 0);

            let effectiveTotal = grandTotal > 0 ? grandTotal : total;
            let effectivePpn   = nilaiPpn > 0 ? nilaiPpn : pajakNominal;

            $('#so_detail_subtotal').text(formatRp(o.subtotal));
            $('#so_detail_diskon').text('- ' + formatRp(o.diskon_nominal));
            $('#so_detail_pajak').text(effectivePpn > 0 ? '+ ' + formatRp(effectivePpn) : 'Tidak ada PPN (0%)');

            // Tampilkan info ongkir jika ada
            if (biayaKirim > 0) {
                $('#so_detail_ongkir').text('+ ' + formatRp(biayaKirim));
                $('#so_detail_ongkir_row').show();
            } else {
                $('#so_detail_ongkir_row').hide();
            }

            $('#so_detail_grand_total').text(formatRp(effectiveTotal));

            // Tampilkan rincian DP jika ada
            if (dpNominal > 0) {
                let dpLabel = (o.tipe_dp === 'Persen' && dpPersen > 0) ? `(${dpPersen}%)` : '';
                $('#so_detail_dp_label').text(dpLabel);
                $('#so_detail_dp_nominal').text(formatRp(dpNominal));
                $('#so_detail_sisa_tagihan').text(formatRp(sisaTagihan));
                $('#so_detail_dp_section').show();
            } else {
                $('#so_detail_dp_section').hide();
            }

            // Show/hide edit & cancel buttons based on status
            setDetailModalActions(o);

            $('#modalDetailOrder').modal('show');
        }).fail(function(xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat detail pesanan.', confirmButtonColor: '#10b981' });
        });
    }

    // ===== OPEN EDIT ORDER MODAL =====
    let _editOrderData = null; // cache order data for recalculation

    function openEditModal(orderId) {
        $.get(`/marketing/sales-order/${orderId}/detail`, function (res) {
            let o = res.order;
            if (!o) return;
            _editOrderData = o;

            $('#edit_order_id').val(o.id);
            $('#edit_so_nomor').text(o.nomor_so);

            // Customer
            if (o.customer_id) {
                $('#edit_customer_id').val(o.customer_id);
                $('#edit_walkin_wrap').hide();
            } else {
                $('#edit_customer_id').val('');
                $('#edit_nama_walkin').val(o.nama_customer_walkin || '');
                $('#edit_walkin_wrap').show();
            }

            // Payment
            $('#edit_payment_method_id').val(o.payment_method_id || '');
            $('#edit_payment_term_id').val(o.payment_term_id || '');
            $('#edit_catatan').val(o.catatan || '');

            // Diskon
            let diskonTipe = o.diskon_tipe || 'Nominal';
            $('#edit_diskon_tipe').val(diskonTipe);
            let diskonVal = diskonTipe === 'Persen' ? (parseFloat(o.diskon_persen || 0)) : (parseFloat(o.diskon_nilai || o.diskon_nominal || 0));
            $('#edit_diskon_val').val(diskonVal);

            // Pajak
            let pajakTipe = o.pajak_tipe || 'None';
            if (parseFloat(o.tarif_ppn || 0) > 0 && pajakTipe === 'None') pajakTipe = 'PPN11';
            $('#edit_pajak_tipe').val(pajakTipe);

            // Build item rows
            let tbodyHtml = '';
            (o.details || []).forEach((d, idx) => {
                let brgNama    = d.nama_barang_custom || (d.barang ? d.barang.nama_barang : 'Item');
                let brgSku     = d.barang ? d.barang.kode_barang : '-';
                let satuanNama = d.nama_satuan || (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : '');
                tbodyHtml += `
                    <tr data-detail-id="${d.id}">
                        <td class="text-center text-muted small">${idx + 1}</td>
                        <td>
                            <div class="fw-bold small">${brgNama}</div>
                            <div class="text-muted font-monospace" style="font-size:0.72rem;">${brgSku}</div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-center edit-item-qty" value="${parseFloat(d.qty)}" min="0.0001" step="any" style="max-width:70px;">
                                <span class="input-group-text" style="font-size:0.75rem;">${satuanNama}</span>
                            </div>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end font-monospace edit-item-harga" value="${parseFloat(d.harga_satuan)}" min="0" step="any">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm text-end font-monospace edit-item-diskon" value="${parseFloat(d.diskon_nominal || 0)}" min="0" step="any">
                        </td>
                        <td class="text-end fw-bold font-monospace small edit-item-subtotal">${formatRp(d.subtotal)}</td>
                    </tr>
                `;
            });
            $('#edit_items_tbody').html(tbodyHtml);

            recalcEditTotals();
            $('#modalDetailOrder').modal('hide');
            $('#modalEditOrder').modal('show');
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data pesanan.' });
        });
    }

    // ===== RECALC EDIT ORDER TOTALS =====
    function recalcEditTotals() {
        let subtotal = 0;
        $('#edit_items_tbody tr').each(function () {
            let qty    = parseFloat($(this).find('.edit-item-qty').val()) || 0;
            let harga  = parseFloat($(this).find('.edit-item-harga').val()) || 0;
            let diskon = parseFloat($(this).find('.edit-item-diskon').val()) || 0;
            let sub    = (qty * harga) - diskon;
            if (sub < 0) sub = 0;
            $(this).find('.edit-item-subtotal').text(formatRp(sub));
            subtotal += sub;
        });

        let diskonTipe = $('#edit_diskon_tipe').val();
        let diskonVal  = parseFloat($('#edit_diskon_val').val()) || 0;
        let diskonNominal = diskonTipe === 'Persen' ? (subtotal * diskonVal / 100) : diskonVal;

        let pajakTipe    = $('#edit_pajak_tipe').val();
        let afterDiskon  = subtotal - diskonNominal;
        let pajakNominal = pajakTipe === 'PPN11' ? (afterDiskon * 0.11) : 0;
        let grandTotal   = afterDiskon + pajakNominal;

        $('#edit_summary_subtotal').text(formatRp(subtotal));
        $('#edit_summary_diskon').text('- ' + formatRp(diskonNominal));
        $('#edit_summary_pajak').text(pajakTipe !== 'None' ? '+ ' + formatRp(pajakNominal) : 'Tidak ada PPN');
        $('#edit_summary_grand_total').text(formatRp(grandTotal));

        // Store for submit
        $('#formEditOrder').data('subtotal', subtotal);
        $('#formEditOrder').data('diskon_nominal', diskonNominal);
        $('#formEditOrder').data('pajak_nominal', pajakNominal);
        $('#formEditOrder').data('grand_total', grandTotal);
    }

    // Auto-recalc when any edit item field changes
    $(document).on('input change', '#edit_items_tbody .edit-item-qty, #edit_items_tbody .edit-item-harga, #edit_items_tbody .edit-item-diskon, #edit_diskon_tipe, #edit_diskon_val, #edit_pajak_tipe', function () {
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
            let detailId = $(this).data('detail-id');
            let qty      = parseFloat($(this).find('.edit-item-qty').val()) || 0;
            let harga    = parseFloat($(this).find('.edit-item-harga').val()) || 0;
            let diskon   = parseFloat($(this).find('.edit-item-diskon').val()) || 0;
            let sub      = Math.max((qty * harga) - diskon, 0);

            if (qty <= 0) { valid = false; return false; }

            items.push({
                detail_id:      detailId,
                qty:            qty,
                harga_satuan:   harga,
                diskon_nominal: diskon,
                subtotal:       sub,
            });
        });

        if (!valid) {
            Swal.fire({ icon: 'warning', title: 'Qty Tidak Valid', text: 'Qty semua item harus lebih dari 0.', confirmButtonColor: '#f59e0b' });
            return;
        }

        let form     = $('#formEditOrder');
        let payload  = {
            customer_id:        $('#edit_customer_id').val() || null,
            nama_customer_walkin: $('#edit_customer_id').val() ? null : $('#edit_nama_walkin').val(),
            payment_method_id:  $('#edit_payment_method_id').val() || null,
            payment_term_id:    $('#edit_payment_term_id').val() || null,
            catatan:            $('#edit_catatan').val(),
            diskon_tipe:        $('#edit_diskon_tipe').val(),
            diskon_nilai:       parseFloat($('#edit_diskon_val').val()) || 0,
            diskon_nominal:     form.data('diskon_nominal') || 0,
            pajak_tipe:         $('#edit_pajak_tipe').val(),
            pajak_persen:       $('#edit_pajak_tipe').val() === 'PPN11' ? 11 : 0,
            pajak_nominal:      form.data('pajak_nominal') || 0,
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
                $('#btn_submit_edit_order').prop('disabled', false).html('<i class="bi bi-save2-fill me-1"></i> Simpan Perubahan');
                $('#modalEditOrder').modal('hide');
                tableOrders.ajax.reload();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#10b981', timer: 2500, timerProgressBar: true });
            },
            error: function (xhr) {
                $('#btn_submit_edit_order').prop('disabled', false).html('<i class="bi bi-save2-fill me-1"></i> Simpan Perubahan');
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
                url:  `/marketing/sales-order/${orderId}/cancel`,
                type: 'POST',
                data: JSON.stringify({ alasan_pembatalan: result.value }),
                contentType: 'application/json',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    $('#modalDetailOrder').modal('hide');
                    tableOrders.ajax.reload();
                    Swal.fire({ icon: 'success', title: 'Pesanan Dibatalkan', text: res.message, confirmButtonColor: '#10b981', timer: 2000, timerProgressBar: true });
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal membatalkan pesanan.';
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });
    }

    // ===== BUTTON EVENTS FROM DETAIL MODAL =====
    $('#btn_edit_order_from_detail').on('click', function () {
        let orderId = $(this).data('id');
        openEditModal(orderId);
    });

    $('#btn_cancel_order_from_detail').on('click', function () {
        let orderId = $(this).data('id');
        let nomorSo = $('#so_detail_nomor').text().replace('Nomor SO: ', '');
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
        e.stopPropagation();
        let prodId = $(this).data('id');
        if (!prodId) return;

        // Reset fields
        $('#modal_prod_title').text('Memuat detail...');
        $('#modal_prod_category').text('-');
        $('#modal_prod_brand').text('-');
        $('#modal_prod_sku').text('-');
        $('#modal_prod_price_retail').text('Rp 0');
        $('#modal_prod_price_grosir').text('Rp 0');
        $('#modal_prod_price_tender').text('Rp 0');
        $('#modal_input_qty').val(1);
        $('#modal_prod_thumb_strip').empty();

        $.get(`/marketing/sales-order/product/${prodId}/detail`, function (res) {
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

            // Bundle Components
            if (p.tipe_item === 'Bundle' && p.bundle_components && p.bundle_components.length > 0) {
                let compHtml = '';
                p.bundle_components.forEach((bc) => {
                    let bPhoto = bc.photo_url || PLACEHOLDER_IMG;
                    compHtml += `
                        <div class="d-flex align-items-center justify-content-between p-2 mb-1.5 rounded-2 small" style="background: rgba(0,0,0,0.02);">
                            <div class="d-flex align-items-center gap-2">
                                <img src="${bPhoto}" style="width:32px;height:32px;border-radius:8px;object-fit:cover;" onerror="this.onerror=null; this.src=PLACEHOLDER_IMG;">
                                <div>
                                    <span class="fw-bold">${bc.nama_barang}</span>
                                    <span class="text-muted font-monospace small">(${bc.kode_barang})</span>
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary font-monospace">${bc.qty} ${bc.satuan}</span>
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
                        <img src="${foto.url}" class="cinematic-thumb-item ${fIdx === 0 ? 'active' : ''}" data-index="${fIdx}" alt="Thumbnail" onerror="this.onerror=null; this.src=PLACEHOLDER_IMG;">
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

            $('#modalProductDetail').modal('show');
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

        let stock = Number(currentModalProduct.total_stok) || 0;
        if (stock <= 0) {
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

        let existing = cart.find(c => c.id === currentModalProduct.id);
        if (existing && (existing.qty + qty) > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Tidak Mencukupi',
                text: `Akumulasi kuantitas pesanan (${existing.qty} + ${qty}) melebihi sisa persediaan yang tersedia (${stock} ${currentModalProduct.satuan_name || 'Pcs'}).`,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (!existing && qty > stock) {
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
        let unitPrice = getItemUnitPrice(currentModalProduct, currentPricingTier);

        if (existing) {
            existing.qty += qty;
            existing.subtotal = (existing.harga_satuan * existing.qty) - (existing.diskon_nominal || 0);
        } else {
            cart.push({
                id: currentModalProduct.id,
                kode: currentModalProduct.kode_barang,
                nama: currentModalProduct.nama_barang,
                satuan: currentModalProduct.satuan_name,
                photo_url: photoSrc,
                harga_retail: currentModalProduct.harga_retail,
                harga_grosir: currentModalProduct.harga_grosir,
                harga_tender: currentModalProduct.harga_tender,
                harga_satuan: unitPrice,
                qty: qty,
                diskon_nominal: 0,
                subtotal: unitPrice * qty,
                stock: stock
            });
        }

        renderCart();
        $('#modalProductDetail').modal('hide');

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

    // ===== INITIALIZE SELECT2 FOR CUSTOMER SEARCHABLE DROPDOWNS =====
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

            // Select2 inside Checkout Modal
            $('#checkout_customer_select').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCheckoutOrder'),
                placeholder: '-- Pelanggan Umum (Walk-in) --',
                allowClear: true,
                width: '100%'
            });

            // Select2 inside Edit Modal
            $('#edit_customer_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditOrder'),
                placeholder: '-- Pilih Pelanggan --',
                allowClear: true,
                width: '100%'
            });
        }
    }

    // Initial load
    initCustomerSelect2();
    loadProducts(1);
    loadCartFromStorage();
});
</script>
