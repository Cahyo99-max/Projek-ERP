{{-- Offcanvas Cart Drawer (Tokopedia POS Style) --}}
<div class="offcanvas offcanvas-end tkpd-offcanvas-cart" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header border-bottom py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="p-2 bg-success-subtle text-success rounded-3 d-inline-flex align-items-center">
                <i class="bi bi-cart3 fs-5"></i>
            </span>
            <div>
                <h5 class="offcanvas-title fw-bold mb-0" id="offcanvasCartLabel">Keranjang Pesanan</h5>
                <span class="text-body-secondary small"><span id="cart_badge_count" class="fw-bold">0</span> Macam Produk</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" id="btn_clear_cart" title="Kosongkan Keranjang">
                <i class="bi bi-trash me-1"></i> Kosongkan
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3 gap-3">
        
        <!-- Pricing Tier Switcher inside Cart -->
        <div>
            <label class="tkpd-section-label mb-1.5">
                <i class="bi bi-tags-fill me-1 text-success"></i> Tingkat Harga Jual (Pricing Tier)
            </label>
            <div class="tkpd-tier-switcher w-100">
                <button type="button" class="tkpd-tier-btn active-retail flex-fill" data-tier="Retail">
                    <i class="bi bi-tag-fill me-1"></i> Retail
                </button>
                <button type="button" class="tkpd-tier-btn flex-fill" data-tier="Grosir">
                    <i class="bi bi-boxes me-1"></i> Grosir
                </button>
                <button type="button" class="tkpd-tier-btn flex-fill" data-tier="Tender">
                    <i class="bi bi-briefcase-fill me-1"></i> Tender
                </button>
            </div>
        </div>

        <!-- Customer & Tenor (Payment Term) Section Card -->
        <div class="tkpd-cart-section-card">
            <div class="tkpd-section-label">
                <i class="bi bi-person-badge-fill me-1.5 text-success"></i> Pelanggan &amp; Tenor
            </div>
            
            <div class="row g-2.5">
                <div class="col-12">
                    <label class="form-label small mb-1" style="font-size:0.75rem;">Pelanggan / Customer</label>
                    <select class="form-select form-select-sm" id="cart_customer_id">
                        <option value="">-- Pelanggan Umum (Walk-in) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" data-nama="{{ $c->nama_customer }}" data-telepon="{{ $c->telepon }}" data-alamat="{{ $c->alamat }}">
                                {{ $c->nama_customer }} ({{ $c->kode_customer }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Field Walk-in Customer (Muncul jika Walk-in dipilih) --}}
                <div class="col-12" id="cart_walkin_wrap">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="text" class="form-control form-control-sm" id="cart_nama_walkin" placeholder="Nama Pelanggan" value="Pelanggan Umum">
                        </div>
                        <div class="col-6">
                            <input type="text" class="form-control form-control-sm" id="cart_telepon_walkin" placeholder="No. WhatsApp">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label small mb-1" style="font-size:0.75rem;">Tenor Pembayaran (Termin)</label>
                    <select class="form-select form-select-sm" id="cart_payment_term_id">
                        <option value="">-- Pilih Tenor (Cash / Kredit) --</option>
                        @foreach($paymentTerms as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} hari)</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Cart Items List Container -->
        <div>
            <label class="tkpd-section-label mb-1.5">
                <i class="bi bi-bag-check me-1 text-success"></i> Daftar Barang Pesanan
            </label>
            <div class="tkpd-cart-items-list" id="cart_items_list" style="max-height:220px; overflow-y:auto;">
                <!-- Cart items rendered dynamically by JS -->
            </div>
        </div>

        {{-- Discount & Tax Controls Card --}}
        <div class="tkpd-cart-section-card">

            {{-- Diskon --}}
            <div class="mb-3">
                <label class="tkpd-section-label mb-2">
                    <i class="bi bi-scissors me-1 text-danger"></i> Diskon Order
                </label>
                <div class="d-flex gap-1 align-items-stretch">
                    {{-- Toggle Rp / % --}}
                    <div class="btn-group btn-group-sm flex-shrink-0" role="group" id="cart_diskon_toggle">
                        <input type="radio" class="btn-check" name="diskon_mode" id="diskon_mode_rp" value="Nominal" checked>
                        <label class="btn btn-outline-secondary fw-bold px-2" for="diskon_mode_rp" style="min-width:42px;">Rp</label>
                        <input type="radio" class="btn-check" name="diskon_mode" id="diskon_mode_pct" value="Persen">
                        <label class="btn btn-outline-secondary fw-bold px-2" for="diskon_mode_pct" style="min-width:42px;">%</label>
                    </div>
                    <div class="input-group input-group-sm flex-grow-1">
                        <input type="text" class="form-control text-end" id="cart_diskon_val" value="0" placeholder="0">
                        <span class="input-group-text font-monospace" id="cart_diskon_suffix">Rp</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1" id="cart_diskon_preview_row" style="display:none!important;">
                    <span class="text-body-secondary" style="font-size:0.75rem;">Nilai diskon:</span>
                    <span class="fw-bold text-danger font-monospace" style="font-size:0.75rem;" id="cart_diskon_preview">- Rp 0</span>
                </div>
            </div>

            {{-- PPN dari Master Pajak --}}
            <div class="mb-3">
                <label class="tkpd-section-label mb-2">
                    <i class="bi bi-receipt me-1 text-info"></i> Pajak (PPN)
                </label>
                <select class="form-select form-select-sm" id="cart_pajak_type">
                    <option value="none" data-tarif="0">Non PPN (0%)</option>
                    @foreach($ppnList as $ppn)
                        <option value="{{ $ppn->id }}" data-tarif="{{ $ppn->tarif }}">
                            {{ $ppn->nama_pajak }} ({{ number_format($ppn->tarif, 0) }}%)
                        </option>
                    @endforeach
                    @if($ppnList->isEmpty())
                        <option value="ppn11" data-tarif="11">PPN 11%</option>
                    @endif
                </select>
                <div class="d-flex justify-content-between mt-1" id="cart_pajak_preview_row" style="display:none!important;">
                    <span class="text-body-secondary" style="font-size:0.75rem;">Nilai PPN:</span>
                    <span class="fw-bold text-info font-monospace" style="font-size:0.75rem;" id="cart_pajak_preview">+ Rp 0</span>
                </div>
            </div>

            {{-- Biaya Pengiriman / Ongkir / Biaya Lain --}}
            <div class="mb-3">
                <label class="tkpd-section-label mb-2">
                    <i class="bi bi-truck me-1 text-primary"></i> Ongkir / Biaya Lain
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text font-monospace">Rp</span>
                    <input type="text" class="form-control text-end" id="cart_biaya_pengiriman" value="0" placeholder="0">
                </div>
            </div>

            {{-- Down Payment (DP / Uang Muka) --}}
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="tkpd-section-label mb-0">
                        <i class="bi bi-wallet2 me-1 text-warning"></i> Uang Muka (DP)
                    </label>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="cart_use_dp">
                        <label class="form-check-label small" for="cart_use_dp" style="font-size:0.75rem;">Gunakan DP</label>
                    </div>
                </div>
                <div id="cart_dp_container" style="display: none;">
                    <div class="d-flex gap-1 align-items-stretch">
                        {{-- Toggle Rp / % DP --}}
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group" id="cart_dp_toggle">
                            <input type="radio" class="btn-check" name="dp_mode" id="dp_mode_rp" value="Nominal" checked>
                            <label class="btn btn-outline-secondary fw-bold px-2" for="dp_mode_rp" style="min-width:42px;">Rp</label>
                            <input type="radio" class="btn-check" name="dp_mode" id="dp_mode_pct" value="Persen">
                            <label class="btn btn-outline-secondary fw-bold px-2" for="dp_mode_pct" style="min-width:42px;">%</label>
                        </div>
                        <div class="input-group input-group-sm flex-grow-1">
                            <input type="text" class="form-control text-end" id="cart_dp_val" value="0" placeholder="0">
                            <span class="input-group-text font-monospace" id="cart_dp_suffix">Rp</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 pt-2 border-top" id="cart_dp_preview_row">
                        <span class="text-body-secondary small" style="font-size:0.75rem;">Sisa Tagihan (Piutang):</span>
                        <span class="fw-bold text-warning font-monospace small" id="cart_sisa_tagihan_preview">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cart Calculations Summary Card --}}
        <div class="tkpd-cart-section-card">
            <div class="tkpd-summary-row">
                <span class="text-body-secondary small">Subtotal</span>
                <strong id="cart_summary_subtotal">Rp 0</strong>
            </div>
            <div class="tkpd-summary-row">
                <span class="text-body-secondary small">Diskon <span id="cart_summary_diskon_label" class="text-muted font-monospace" style="font-size:0.72rem;"></span></span>
                <strong class="text-danger" id="cart_summary_diskon">- Rp 0</strong>
            </div>
            <div class="tkpd-summary-row">
                <span class="text-body-secondary small">Pajak <span id="cart_summary_pajak_label" class="text-muted font-monospace" style="font-size:0.72rem;"></span></span>
                <strong class="text-info" id="cart_summary_pajak">+ Rp 0</strong>
            </div>
            <div class="tkpd-summary-row">
                <span class="text-body-secondary small">Ongkir / Biaya Lain</span>
                <strong class="text-primary" id="cart_summary_ongkir">+ Rp 0</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
                <span class="fw-bold fs-6">Grand Total</span>
                <span class="tkpd-summary-total" id="cart_summary_total">Rp 0</span>
            </div>

            {{-- Summary DP & Sisa Tagihan (Muncul jika DP aktif) --}}
            <div id="cart_summary_dp_section" style="display: none;">
                <div class="tkpd-summary-row mt-2 pt-2 border-top">
                    <span class="text-body-secondary small">Uang Muka (DP) <span id="cart_summary_dp_label" class="text-muted font-monospace" style="font-size:0.72rem;"></span></span>
                    <strong class="text-success font-monospace" id="cart_summary_dp_nominal">Rp 0</strong>
                </div>
                <div class="tkpd-summary-row">
                    <span class="text-body-secondary small fw-bold">Sisa Tagihan</span>
                    <strong class="text-warning font-monospace" id="cart_summary_sisa_tagihan">Rp 0</strong>
                </div>
            </div>
        </div>

    </div>

    {{-- Offcanvas Footer: Checkout Button --}}
    <div class="p-3 border-top bg-body-tertiary">
        @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-sales-order'))
        <button type="button" class="tkpd-btn-checkout w-100" id="btn_open_checkout" disabled>
            <i class="bi bi-bag-check-fill fs-5"></i>
            <span>Buat Pesanan &amp; Checkout</span>
        </button>
        @endif
    </div>
</div>

{{-- Floating Cart Trigger Button (Bottom Right) --}}
<button type="button" class="tkpd-floating-cart-btn" id="btn_floating_cart" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCart" aria-controls="offcanvasCart" title="Buka Keranjang Pesanan">
    <div class="position-relative d-inline-flex align-items-center">
        <i class="bi bi-cart-fill fs-5" id="cart_icon_bounce"></i>
        <span class="cart-badge-pill" id="cart_badge_floating">0</span>
    </div>
    <span class="d-none d-sm-inline font-monospace" id="cart_floating_nominal">Rp 0</span>
</button>
