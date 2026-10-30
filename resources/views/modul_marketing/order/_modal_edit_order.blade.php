{{-- Modal Edit Sales Order (Synchronized with Modul Penawaran) --}}
<div class="modal modal-blur fade" id="modalEditOrder" tabindex="-1" aria-labelledby="modalEditOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content so-edit-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- Modal Header Toolbar --}}
            <div class="modal-header so-edit-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalEditOrderLabel">Edit Pesanan (SO)</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1 rounded-pill fw-bold small" id="edit_so_nomor_badge">
                                <i class="bi bi-lock-fill me-1"></i> SO: -
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2.5 py-1 rounded-pill fw-bold small" id="edit_quotation_badge" style="display: none;"></span>
                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle font-monospace px-2.5 py-1 rounded-pill fw-bold small" id="edit_tender_badge" style="display: none;"></span>
                        </div>
                        <div class="text-body-secondary small">Perbarui data pelanggan, kuantitas item, penyesuaian harga tier, diskon, dan ketentuan pembayaran.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditOrder">
                @csrf
                <input type="hidden" id="edit_order_id" name="order_id">

                <div class="modal-body so-edit-body p-4 pb-5">

                    {{-- Alert Info Banner --}}
                    <div class="alert alert-warning border border-warning-subtle bg-warning-subtle text-warning-emphasis d-flex align-items-center gap-2.5 py-2.5 px-3.5 mb-4 rounded-3 shadow-xs" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-warning"></i>
                        <div class="small">
                            <strong>Perhatian:</strong> Nomor SO bersifat <strong>permanen</strong> (tidak dapat diubah) untuk menjaga integritas pembukuan. Perubahan berlaku untuk <strong>item pesanan aktif</strong>.
                        </div>
                    </div>

                    {{-- 1. IDENTITAS DOKUMEN & KLIEN --}}
                    <div class="so-edit-card p-4 rounded-4 mb-4">
                        <div class="so-edit-card-title d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom">
                            <span class="d-flex align-items-center gap-2 fw-bold text-primary text-uppercase">
                                <i class="bi bi-building fs-6"></i> 1. Identitas Dokumen &amp; Klien
                            </span>
                            <span class="badge bg-secondary-subtle text-body border border-secondary-subtle font-monospace fw-semibold px-2.5 py-1 rounded-pill small" id="edit_status_badge">
                                Status: Confirmed
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label so-edit-label small">Nomor Pesanan (SO) <i class="bi bi-lock-fill text-muted ms-0.5" title="Nomor SO tidak dapat diubah"></i></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control font-monospace fw-bold so-edit-input-readonly" id="edit_so_nomor" readonly>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label so-edit-label small">Tanggal Pesanan (SO)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" class="form-control" id="edit_tanggal_so" name="tanggal_so">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label so-edit-label small">Sales PIC / Petugas</label>
                                <select class="form-select form-select-sm" id="edit_sales_id" name="sales_id">
                                    <option value="">-- Pilih Sales PIC --</option>
                                    @if(isset($salesList))
                                        @foreach($salesList as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama_lengkap }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label so-edit-label small fw-semibold">Pelanggan Terdaftar <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="edit_customer_id" name="customer_id">
                                    <option value="">-- Walk-In / Pelanggan Umum --</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}" data-telepon="{{ $cust->telepon ?? '' }}">
                                            {{ $cust->nama_customer }} {{ $cust->nama_perusahaan ? '(' . $cust->nama_perusahaan . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6" id="edit_walkin_wrap">
                                <label class="form-label so-edit-label small fw-semibold">Nama Walk-In / Pelanggan Umum <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="edit_nama_walkin" name="nama_customer_walkin" placeholder="Nama pelanggan walk-in...">
                                </div>
                            </div>

                            <div class="col-12" id="edit_ref_source_box" style="display: none;">
                                <div class="p-3 rounded-3 so-edit-ref-box small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-link-45deg fs-5 text-info"></i>
                                        <div>
                                            <span class="text-body-secondary">Asal Referensi:</span>
                                            <strong class="font-monospace text-primary ms-1" id="edit_ref_source_text">-</strong>
                                        </div>
                                    </div>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2.5 py-1" id="edit_ref_tier_text">Tier: Retail</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. RINCIAN ITEM PESANAN & HARGA --}}
                    <div class="so-edit-card p-4 rounded-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2.5 border-bottom so-edit-card-title flex-wrap gap-2">
                            <div class="fw-bold text-primary small text-uppercase d-flex align-items-center gap-2 mb-0">
                                <i class="bi bi-cart-check fs-6"></i> 2. Rincian Item Barang &amp; Harga
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs" id="btnEditAddMasterItem">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Master
                                </button>
                                <span class="badge bg-body-tertiary text-body border px-2.5 py-1 rounded-pill small font-monospace" id="edit_items_count_badge">0 Item</span>
                            </div>
                        </div>

                        <!-- Mobile Swipe Hint Banner -->
                        <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2 rounded-3 so-swipe-banner shadow-2xs">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left-right text-primary"></i>
                                <span class="fw-semibold small">Tabel barang dapat digeser ke samping</span>
                            </div>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold so-swipe-pill">Swipe &rarr;</span>
                        </div>

                        <div class="table-responsive border rounded-3 shadow-xs so-edit-table-wrap mb-1" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                            <table class="table table-hover align-middle mb-0" id="edit_items_table" style="min-width: 980px;">
                                <thead>
                                    <tr class="small text-uppercase text-secondary fw-bold">
                                        <th style="width: 40px;" class="text-center py-2.5">No</th>
                                        <th class="py-2.5">Item Barang &amp; Spesifikasi</th>
                                        <th class="text-center py-2.5" style="width: 140px;">Qty &amp; Satuan</th>
                                        <th class="py-2.5" style="width: 195px;">Harga Satuan &amp; Tier</th>
                                        <th class="py-2.5" style="width: 180px;">Diskon Item</th>
                                        <th class="text-end py-2.5" style="width: 155px;">Subtotal (Rp)</th>
                                        <th class="py-2.5" style="width: 185px;">Pajak (PPN)</th>
                                        <th class="text-center py-2.5" style="width: 45px;"><i class="bi bi-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="edit_items_tbody">
                                    {{-- Populated dynamically via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3. PEMBAYARAN, PENGIRIMAN & RINGKASAN BIAYA --}}
                    <div class="row g-4">
                        
                        {{-- Left Column: 3. Ketentuan Pembayaran & Pengiriman --}}
                        <div class="col-lg-6">
                            <div class="so-edit-card p-4 rounded-4 h-100 d-flex flex-column">
                                <div class="so-edit-card-title fw-bold text-primary small text-uppercase mb-3 pb-2.5 border-bottom d-flex align-items-center gap-2">
                                    <i class="bi bi-credit-card-2-front fs-6"></i> 3. Pembayaran &amp; Pengiriman
                                </div>

                                <div class="row g-3 flex-grow-1">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label so-edit-label small">Metode Pembayaran</label>
                                        <select class="form-select form-select-sm" id="edit_payment_method_id" name="payment_method_id">
                                            <option value="">-- Pilih Metode --</option>
                                            @foreach($paymentMethods as $pm)
                                                <option value="{{ $pm->id }}">{{ $pm->nama_metode }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-sm-6">
                                        <label class="form-label so-edit-label small">Termin Pembayaran</label>
                                        <select class="form-select form-select-sm" id="edit_payment_term_id" name="payment_term_id">
                                            <option value="">-- Pilih Termin --</option>
                                            @foreach($paymentTerms as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->nama_term }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label so-edit-label small">Alamat Pengiriman</label>
                                        <textarea class="form-control form-control-sm" id="edit_alamat_pengiriman" name="alamat_pengiriman" rows="2" placeholder="Alamat tujuan pengiriman pesanan..."></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label so-edit-label small">Catatan / Memo Pesanan</label>
                                        <textarea class="form-control form-control-sm" id="edit_catatan" name="catatan" rows="2" placeholder="Catatan instruksi atau keterangan khusus..."></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label so-edit-label small d-flex justify-content-between align-items-center">
                                            <span>Syarat &amp; Ketentuan</span>
                                            <span class="text-body-secondary font-monospace" style="font-size:0.7rem;">Dapat Disesuaikan</span>
                                        </label>
                                        <textarea class="form-control form-control-sm font-monospace" id="edit_syarat_ketentuan" name="syarat_ketentuan" rows="3" style="font-size: 0.8rem;" placeholder="1. Barang sesuai spesifikasi pesanan..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: 4. Ringkasan Nilai Pesanan --}}
                        <div class="col-lg-6">
                            <div class="so-edit-card p-4 rounded-4 h-100 d-flex flex-column">
                                <div class="so-edit-card-title fw-bold text-success small text-uppercase mb-3 pb-2.5 border-bottom d-flex align-items-center gap-2">
                                    <i class="bi bi-calculator fs-6"></i> 4. Ringkasan Nilai Pesanan
                                </div>

                                <div class="d-flex flex-column gap-3 small flex-grow-1">
                                    {{-- Subtotal --}}
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span class="text-body-secondary fw-semibold">Subtotal Item:</span>
                                        <span class="font-monospace fw-bold fs-6 text-body-emphasis" id="edit_summary_subtotal">Rp 0,00</span>
                                    </div>

                                    {{-- Diskon Global Ditiadakan (Hanya Diskon Per-Item) --}}
                                    <input type="hidden" id="edit_diskon_tipe" name="diskon_tipe" value="Nominal">
                                    <input type="hidden" id="edit_diskon_val" name="diskon_nilai" value="0">

                                    {{-- Total PPN (Akumulasi Item) --}}
                                    <div class="pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-body-secondary fw-semibold">Total Pajak (PPN):</span>
                                            <span class="font-monospace fw-bold text-info" id="edit_summary_pajak">+ Rp 0,00</span>
                                        </div>
                                        <input type="hidden" id="edit_nilai_ppn" name="nilai_ppn" value="0">
                                    </div>

                                    {{-- Biaya Pengiriman (Ongkir) --}}
                                    <div class="pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-body-secondary fw-semibold">Biaya Kirim (Ongkir):</span>
                                            <div class="input-group input-group-sm" style="max-width: 175px;">
                                                <span class="input-group-text font-monospace fw-semibold">Rp</span>
                                                <input type="text" inputmode="numeric" class="form-control text-end font-monospace edit-currency-input" id="edit_biaya_pengiriman" name="biaya_pengiriman" value="0" placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Uang Muka (DP) Section (Mirrors Penawaran Harga) --}}
                                    <div class="pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                            <span class="text-body-secondary fw-semibold">Uang Muka (DP):</span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-0.5" id="edit_dp_badge_mode" style="font-size: 0.7rem;">Tanpa DP</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <select class="form-select form-select-sm fw-semibold" id="edit_tipe_dp" name="tipe_dp">
                                                    <option value="None">Tanpa DP (0%)</option>
                                                    <option value="Persen">Persen (%)</option>
                                                    <option value="Nominal">Nominal (Rp)</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <div id="edit_dp_persen_wrap" style="display: none;">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control text-end font-monospace fw-semibold" id="edit_dp_persen" name="dp_persen" value="0" min="0" max="100" step="any" placeholder="0">
                                                        <span class="input-group-text font-monospace fw-bold">%</span>
                                                    </div>
                                                </div>
                                                <div id="edit_dp_nominal_wrap" style="display: none;">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text font-monospace fw-semibold">Rp</span>
                                                        <input type="text" inputmode="numeric" class="form-control text-end font-monospace fw-semibold edit-currency-input" id="edit_dp_nominal_input" name="dp_nominal_input" value="0" placeholder="0">
                                                    </div>
                                                </div>
                                                <input type="hidden" id="edit_dp_nominal" name="dp_nominal" value="0">
                                                <input type="hidden" id="edit_sisa_tagihan" name="sisa_tagihan" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Grand Total Box (Highlight Card) --}}
                                    <div class="so-grand-total-box p-3 rounded-3 mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold fs-6 text-body-emphasis d-block mb-0.5">Grand Total:</span>
                                                <span class="text-body-secondary" style="font-size: 0.75rem;">Total final transaksi pesanan</span>
                                            </div>
                                            <span class="font-monospace fw-bold fs-3 text-success" id="edit_summary_grand_total">Rp 0,00</span>
                                        </div>
                                    </div>

                                    {{-- DP & Sisa Tagihan Breakdown Box --}}
                                    <div class="p-2.5 rounded-3 so-edit-ref-box small mt-2" id="edit_dp_info_wrap">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-body-secondary"><i class="bi bi-wallet2 me-1 text-primary"></i> Nominal Uang Muka (DP):</span>
                                            <strong id="edit_dp_nominal_text" class="text-primary font-monospace">Rp 0,00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-body-secondary"><i class="bi bi-receipt me-1 text-danger"></i> Sisa Tagihan / Pelunasan:</span>
                                            <strong id="edit_sisa_tagihan_text" class="text-danger font-monospace">Rp 0,00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Modal Footer Toolbar --}}
                <div class="modal-footer so-edit-footer border-top px-4 py-3 d-flex justify-content-between align-items-center">
                    <div class="text-body-secondary small">
                        <i class="bi bi-shield-check text-success me-1"></i> Perubahan akan diverifikasi dengan stok gudang.
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" id="btn_submit_edit_order" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border: none;">
                            <i class="bi bi-save2-fill"></i> Simpan Perubahan SO
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Picker Master Barang untuk Edit Sales Order --}}
<div class="modal fade" id="modalPickerBarangEditOrder" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden so-edit-modal-content">
            <div class="modal-header px-4 py-3 border-bottom so-edit-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2.5 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center shadow-xs">
                        <i class="bi bi-box-seam fs-5"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-body mb-0">Pilih Barang dari Master Produk</h6>
                        <div class="text-muted small" style="font-size: 0.75rem;">Pilih produk master untuk ditambahkan ke pesanan aktif ini.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 so-edit-body" style="max-height: 540px;">
                
                {{-- Search & Counter Bar --}}
                <div class="row g-2 align-items-center mb-3">
                    <div class="col">
                        <div class="position-relative">
                            <input type="text" id="searchBarangEditInput" class="form-control rounded-pill ps-5 pe-5 py-2.5" placeholder="Ketik nama produk, SKU, atau kategori barang...">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3.5 text-primary fs-6"></i>
                            <button type="button" id="btnClearSearchBarangEdit" class="btn btn-sm btn-link position-absolute top-50 end-0 translate-middle-y me-3 text-secondary p-0 text-decoration-none d-none" style="font-size: 1.15rem;" title="Reset Pencarian">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-body-tertiary text-body border px-3 py-2 rounded-pill font-monospace" id="searchBarangEditCountBadge" style="font-size: 0.78rem;">
                            {{ isset($barangList) ? $barangList->count() : 0 }} Produk
                        </span>
                    </div>
                </div>

                {{-- Category Filter Chips --}}
                @php
                    $categories = isset($barangList) ? $barangList->pluck('kategori.nama_kategori')->filter()->unique()->values() : collect();
                @endphp
                @if($categories->count() > 0)
                <div class="d-flex align-items-center gap-1.5 flex-wrap mb-3" id="pickerBarangEditCategoryChips">
                    <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-bold btn-category-edit-chip active btn-primary" data-cat="all">
                        Semua ({{ isset($barangList) ? $barangList->count() : 0 }})
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" class="btn btn-xs rounded-pill px-3 py-1 fw-semibold btn-category-edit-chip btn-outline-secondary" data-cat="{{ strtolower($cat) }}">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
                @endif

                <!-- Mobile Swipe Hint Banner -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2 rounded-3 so-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-primary"></i>
                        <span class="fw-semibold small">Tabel barang dapat digeser ke samping</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold so-swipe-pill">Swipe &rarr;</span>
                </div>

                {{-- Table Container --}}
                <div class="table-responsive rounded-3 border so-edit-table-wrap" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                    <table class="table table-hover align-middle mb-0" id="pickerBarangEditTable" style="min-width: 820px;">
                        <thead class="sticky-top">
                            <tr class="small text-uppercase text-secondary fw-bold">
                                <th style="min-width: 260px;">Produk &amp; SKU</th>
                                <th style="min-width: 130px;">Kategori</th>
                                <th class="text-center" style="width: 90px;">Satuan</th>
                                <th class="text-end" style="min-width: 280px;">Pilihan Tier Harga</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($barangList))
                                @foreach($barangList as $b)
                                    @php
                                        $hRetail = (float)($b->harga_retail ?: ($b->harga_jual_retail ?: 0));
                                        $hGrosir = (float)($b->harga_grosir ?: ($b->harga_jual_grosir ?: 0));
                                        $hTender = (float)($b->harga_tender ?: ($b->harga_jual_tender ?: 0));
                                        $hDefault = $hTender ?: ($hGrosir ?: ($hRetail ?: 0));
                                        $catName = $b->kategori?->nama_kategori ?? '-';
                                    @endphp
                                    <tr class="barang-item-edit-row" data-search="{{ strtolower($b->kode_barang . ' ' . $b->nama_barang . ' ' . $catName) }}" data-category="{{ strtolower($catName) }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $b->resolved_photo_url }}" alt="{{ $b->nama_barang }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 10px;" class="border shadow-xs flex-shrink-0" onerror="this.src='/uploads/barang/placeholder_garment.png'">
                                                <div class="overflow-hidden">
                                                    <div class="fw-bold text-body-emphasis fs-8 mb-0.5" title="{{ $b->nama_barang }}">{{ $b->nama_barang }}</div>
                                                    <span class="badge bg-secondary-subtle text-body border font-monospace px-2 py-0.5" style="font-size:0.72rem;">{{ $b->kode_barang }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size:0.75rem;">{{ $catName }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-secondary-subtle text-body border font-monospace px-2 py-0.5" style="font-size:0.72rem;">{{ $b->satuan?->nama_satuan ?? 'Unit' }}</span>
                                                @if($b->tipe_item === 'Jasa')
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-1.5 py-0.5" style="font-size:0.68rem;">Layanan</span>
                                                @elseif((float)($b->total_stok ?? 0) > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size:0.68rem;">Stok: {{ (float)$b->total_stok }}</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5" style="font-size:0.68rem;">Stok Habis</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-wrap font-monospace">
                                                @if($hTender > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.74rem;" title="Harga Tender">
                                                        🏷️ Tender: <strong>Rp {{ number_format($hTender, 2, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                @if($hGrosir > 0)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 0.74rem;" title="Harga Grosir">
                                                        📦 Grosir: <strong>Rp {{ number_format($hGrosir, 2, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                @if($hRetail > 0)
                                                    <span class="badge bg-secondary-subtle text-body border px-2 py-1" style="font-size: 0.74rem;" title="Harga Retail">
                                                        🛍️ Retail: <strong>Rp {{ number_format($hRetail, 2, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                @if($hTender <= 0 && $hGrosir <= 0 && $hRetail <= 0)
                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.74rem;">
                                                        Rp 0,00 (Manual)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 btn-select-edit-master-item shadow-sm fw-bold d-inline-flex align-items-center gap-1"
                                                data-id="{{ $b->id }}"
                                                data-kode="{{ $b->kode_barang }}"
                                                data-nama="{{ $b->nama_barang }}"
                                                data-satuan-id="{{ $b->satuan_id }}"
                                                data-satuan-nama="{{ $b->satuan?->nama_satuan ?? 'Unit' }}"
                                                data-kategori-id="{{ $b->kategori_barang_id }}"
                                                data-tipe="{{ $b->tipe_item ?? 'Stok' }}"
                                                data-stock="{{ (float)($b->total_stok ?? 0) }}"
                                                data-harga="{{ $hDefault }}"
                                                data-harga-retail="{{ $hRetail }}"
                                                data-harga-grosir="{{ $hGrosir }}"
                                                data-harga-tender="{{ $hTender }}"
                                                data-photo-url="{{ $b->resolved_photo_url }}"
                                                style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border: none; font-size: 0.8rem;">
                                                <i class="bi bi-plus-lg"></i> Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr id="emptyBarangEditNotice" style="display: none;">
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="bi bi-search fs-1 d-block mb-2 text-muted opacity-50"></i>
                                    <div class="fw-bold fs-6 mb-1">Produk Tidak Ditemukan</div>
                                    <div class="small text-muted">Coba kata kunci pencarian lain atau pilih kategori Semua.</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   MODAL EDIT SALES ORDER - DUAL THEME & ERGONOMIC SCROLLING STYLES
   ========================================================================== */

/* Light Theme Variables (Default) */
#modalEditOrder .modal-dialog {
    max-width: 95vw !important;
    width: 95vw !important;
}
#modalEditOrder .so-edit-modal-content {
    background: #f8fafc;
}
#modalEditOrder .so-edit-header,
#modalEditOrder .so-edit-footer {
    background: #ffffff;
    border-color: #e2e8f0 !important;
}
#modalEditOrder .so-edit-body {
    background: #f8fafc;
    max-height: calc(88vh - 120px);
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}
#modalEditOrder .so-edit-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
#modalEditOrder .so-edit-card-title {
    border-color: #f1f5f9 !important;
}
#modalEditOrder .so-edit-label {
    margin-bottom: 0.4rem;
    font-weight: 600;
    color: #334155;
    letter-spacing: 0.2px;
}
#modalEditOrder .so-edit-ref-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
}
#modalEditOrder .so-grand-total-box {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
#modalEditOrder .so-edit-input-readonly {
    background-color: #f1f5f9 !important;
    color: #475569 !important;
}
.so-swipe-banner {
    background: rgba(37, 99, 235, 0.08);
    border: 1px dashed rgba(37, 99, 235, 0.28);
    color: #1d4ed8;
}
[data-bs-theme="dark"] .so-swipe-banner {
    background: rgba(59, 130, 246, 0.12);
    border: 1px dashed rgba(96, 165, 250, 0.35);
    color: #93c5fd;
}
.so-swipe-pill {
    background: rgba(37, 99, 235, 0.15);
    color: #1d4ed8;
}
[data-bs-theme="dark"] .so-swipe-pill {
    background: rgba(59, 130, 246, 0.22);
    color: #bfdbfe;
}
#modalEditOrder .so-edit-table-wrap {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}
#modalEditOrder #edit_items_table {
    min-width: 980px;
}
#modalEditOrder #pickerBarangEditTable {
    min-width: 820px;
}
#modalEditOrder .so-edit-table-wrap::-webkit-scrollbar {
    height: 6px;
}
#modalEditOrder .so-edit-table-wrap::-webkit-scrollbar-track {
    background: transparent;
}
#modalEditOrder .so-edit-table-wrap::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.45);
    border-radius: 4px;
}
#modalEditOrder .so-edit-table-wrap::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.7);
}
#modalEditOrder .so-edit-table-wrap thead tr {
    background-color: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
}
#modalEditOrder .so-edit-table-wrap table {
    background: #ffffff;
}
#modalEditOrder .so-edit-table-wrap th,
#modalEditOrder .so-edit-table-wrap td {
    padding: 12px 14px;
}
#modalEditOrder .edit-item-subtotal {
    color: #2563eb !important;
    font-size: 0.92rem;
}
#modalEditOrder .edit-item-row:hover td {
    background-color: rgba(0, 0, 0, 0.015);
}
#modalEditOrder .so-edit-diskon-select {
    width: 82px !important;
    min-width: 82px !important;
    max-width: 82px !important;
    padding-left: 10px;
    padding-right: 22px;
    font-weight: 700;
}

/* Custom Sleek Scrollbar */
#modalEditOrder .so-edit-body::-webkit-scrollbar {
    width: 7px;
}
#modalEditOrder .so-edit-body::-webkit-scrollbar-track {
    background: transparent;
}
#modalEditOrder .so-edit-body::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.5);
    border-radius: 10px;
}
#modalEditOrder .so-edit-body::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.8);
}

#modalEditOrder .btn-diskon-mode,
#modalEditOrder .btn-global-diskon-mode {
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
    font-weight: 700;
}
#modalEditOrder .btn-diskon-mode.active,
#modalEditOrder .btn-global-diskon-mode.active {
    background-color: #2563eb !important;
    color: #ffffff !important;
}
#modalEditOrder .btn-diskon-mode:not(.active) {
    background-color: #f1f5f9;
    color: #64748b;
}
#modalEditOrder .btn-diskon-mode:not(.active):hover {
    background-color: #e2e8f0;
    color: #1e293b;
}
#modalEditOrder .edit-item-diskon-group {
    border-color: #cbd5e1 !important;
}

/* Dark Theme Overrides ([data-bs-theme="dark"]) */
[data-bs-theme="dark"] #modalEditOrder .btn-diskon-mode:not(.active) {
    background-color: #1e293b !important;
    color: #94a3b8 !important;
}
[data-bs-theme="dark"] #modalEditOrder .btn-diskon-mode:not(.active):hover {
    background-color: #334155 !important;
    color: #f8fafc !important;
}
[data-bs-theme="dark"] #modalEditOrder .edit-item-diskon-group {
    border-color: rgba(255, 255, 255, 0.16) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-modal-content {
    background: #0f172a;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-header,
[data-bs-theme="dark"] #modalEditOrder .so-edit-footer {
    background: #182234 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-body {
    background: #0f172a !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-card {
    background: #182234 !important;
    border: 1px solid rgba(255, 255, 255, 0.09) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    color: #f8fafc;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-card-title {
    border-color: rgba(255, 255, 255, 0.08) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-label {
    color: #e2e8f0 !important;
}
[data-bs-theme="dark"] #modalEditOrder .form-control,
[data-bs-theme="dark"] #modalEditOrder .form-select {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
}
[data-bs-theme="dark"] #modalEditOrder .form-control:focus,
[data-bs-theme="dark"] #modalEditOrder .form-select:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
}
[data-bs-theme="dark"] #modalEditOrder .input-group-text {
    background-color: #1e293b !important;
    color: #94a3b8 !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-ref-box {
    background: #0f172a !important;
    border: 1px solid rgba(56, 189, 248, 0.25) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-grand-total-box {
    background: rgba(16, 185, 129, 0.12) !important;
    border: 1px solid rgba(16, 185, 129, 0.28) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-input-readonly {
    background-color: #1e293b !important;
    color: #94a3b8 !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-table-wrap {
    border-color: rgba(255, 255, 255, 0.09) !important;
    background: #182234 !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-table-wrap table {
    background: #182234 !important;
    color: #f8fafc !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-table-wrap thead tr {
    background-color: #0f172a !important;
    border-bottom: 1.5px solid rgba(255, 255, 255, 0.1) !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-table-wrap thead th {
    color: #94a3b8 !important;
    background-color: #0f172a !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-table-wrap td {
    border-bottom-color: rgba(255, 255, 255, 0.06) !important;
}
[data-bs-theme="dark"] #modalEditOrder .edit-item-row:hover td {
    background-color: rgba(255, 255, 255, 0.02) !important;
}
[data-bs-theme="dark"] #modalEditOrder .edit-item-subtotal {
    color: #60a5fa !important;
}
#modalEditOrder .select2-container--bootstrap-5 .select2-selection {
    min-height: 31px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}
#modalEditOrder .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    padding-left: 0;
    line-height: 1.5;
}
[data-bs-theme="dark"] #modalEditOrder .select2-container--bootstrap-5 .select2-selection {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
}
[data-bs-theme="dark"] #modalEditOrder .select2-container--bootstrap-5 .select2-selection__rendered {
    color: #f8fafc !important;
}
[data-bs-theme="dark"] #modalEditOrder .select2-container--bootstrap-5 .select2-selection__placeholder {
    color: #94a3b8 !important;
}
[data-bs-theme="dark"] .select2-dropdown {
    background-color: #182234 !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
    color: #f8fafc !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
}
[data-bs-theme="dark"] .select2-dropdown .select2-search__field {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
}
[data-bs-theme="dark"] .select2-results__option {
    color: #cbd5e1 !important;
}
[data-bs-theme="dark"] .select2-results__option--highlighted {
    background-color: #2563eb !important;
    color: #ffffff !important;
}
[data-bs-theme="dark"] .select2-results__option--selected {
    background-color: #1e40af !important;
    color: #ffffff !important;
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.25);
}
[data-bs-theme="dark"] #modalEditOrder .so-edit-body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.45);
}
</style>

<script>
    window.ORDER_PPN_LIST = @json($ppnList ?? []);
</script>



