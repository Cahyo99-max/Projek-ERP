{{-- Modal Checkout & Buat Pesanan (Tokopedia POS Style) --}}
<div class="modal modal-blur fade" id="modalCheckoutOrder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold fs-5 d-flex align-items-center">
                    <span class="p-2 bg-success-subtle text-success rounded-3 me-2 d-inline-flex align-items-center">
                        <i class="bi bi-bag-check-fill fs-5"></i>
                    </span>
                    Konfirmasi Checkout &amp; Buat Pesanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formCheckoutOrder">
                <div class="modal-body p-4">
                    <div class="row g-3">

                        {{-- Info Customer & Kontak --}}
                        <div class="col-12">
                            <div class="tkpd-cart-section-card">
                                <div class="tkpd-section-label">
                                    <i class="bi bi-person-badge-fill me-2 text-success"></i> Data Pelanggan / Pembeli
                                </div>
                                <div class="row g-2.5">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tipe Pelanggan</label>
                                        <select class="form-select form-select-sm" id="checkout_customer_select">
                                            <option value="">-- Pelanggan Umum (Walk-in) --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" data-nama="{{ $c->nama_customer }}" data-telepon="{{ $c->telepon }}" data-alamat="{{ $c->alamat }}">{{ $c->nama_customer }} ({{ $c->kode_customer }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="checkout_walkin_wrap">
                                        <label class="form-label small fw-bold">Nama Pelanggan Walk-In</label>
                                        <input type="text" class="form-control form-control-sm" id="checkout_nama_walkin" placeholder="Contoh: Bpk. Bambang" value="Pelanggan Umum">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nomor WhatsApp / Telepon</label>
                                        <input type="text" class="form-control form-control-sm" id="checkout_telepon" placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Alamat Pengiriman (Opsional)</label>
                                        <input type="text" class="form-control form-control-sm" id="checkout_alamat" placeholder="Jl. Raya No...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Pembayaran & Termin --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Metode Pembayaran</label>
                            <select class="form-select form-select-sm" id="checkout_payment_method_id">
                                <option value="">-- Pilih Metode Pembayaran --</option>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->nama_metode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Termin / Tenor Pembayaran</label>
                            <select class="form-select form-select-sm" id="checkout_payment_term_id">
                                <option value="">-- Pilih Tenor (Cash / Kredit) --</option>
                                @foreach($paymentTerms as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->nama_term }} ({{ $pt->jumlah_hari }} hari)</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Catatan Pesanan --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold">Catatan Khusus Pesanan</label>
                            <textarea class="form-control form-control-sm" id="checkout_catatan" rows="2" placeholder="Instruksi packing, nomor PO langganan, atau keterangan tambahan..."></textarea>
                        </div>

                        {{-- Ringkasan Nilai Transaksi (readonly, dari keranjang) --}}
                        <div class="col-12">
                            <div class="tkpd-cart-section-card">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-body-secondary small">Total Item Barang:</span>
                                    <span class="fw-bold" id="checkout_summary_items">0 Item</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-body-secondary small">Subtotal:</span>
                                    <span class="font-monospace fw-bold" id="checkout_summary_subtotal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-body-secondary small">Diskon:</span>
                                    <span class="font-monospace fw-bold text-danger" id="checkout_summary_diskon">- Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-body-secondary small">Pajak (PPN) <span id="checkout_summary_pajak_pct" class="font-monospace" style="font-size:0.75rem;"></span>:</span>
                                    <span class="font-monospace fw-bold text-info" id="checkout_summary_pajak">+ Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-body-secondary small">Ongkir / Biaya Lain:</span>
                                    <span class="font-monospace fw-bold text-primary" id="checkout_summary_ongkir">+ Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="fw-bold fs-6">Grand Total:</span>
                                    <span class="font-monospace fw-bold fs-4 text-success" id="checkout_summary_grand_total">Rp 0</span>
                                </div>
                                <div id="checkout_summary_dp_wrap" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-1">
                                        <span class="text-body-secondary small">Uang Muka (DP) <span id="checkout_summary_dp_pct" class="font-monospace" style="font-size:0.75rem;"></span>:</span>
                                        <span class="font-monospace fw-bold text-success" id="checkout_summary_dp_nominal">Rp 0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-6 text-warning">Sisa Tagihan (Piutang):</span>
                                        <span class="font-monospace fw-bold fs-5 text-warning" id="checkout_summary_sisa_tagihan">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="btn_submit_checkout">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>Proses Pesanan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
