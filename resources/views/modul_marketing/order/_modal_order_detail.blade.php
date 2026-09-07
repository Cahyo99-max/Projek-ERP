<!-- Modal Detail Surat Pesanan / Sales Invoice (Cinematic Print-Ready & Responsive) -->
<div class="modal modal-blur fade" id="modalDetailOrder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center" style="background: rgba(37, 99, 235, 0.04);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Faktur &amp; Detail Surat Pesanan (SO)</h5>
                        <div class="text-muted small">Tinjau rincian transaksi, item barang, dan status keuangan.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm" id="btn_print_order" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-printer-fill"></i> Cetak Faktur
                    </button>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"></button>
                </div>
            </div>
            
            <div class="modal-body p-4" id="printable_order_area">
                
                <!-- Kop Surat Resmi Perusahaan -->
                <div class="d-flex justify-content-between align-items-center pb-3 mb-3" style="border-bottom: 2.5px solid #1e40af; position: relative;">
                    <div class="d-flex align-items-center gap-3">
                        <img id="so_detail_company_logo" src="{{ asset('gambar/logo.png') }}" alt="Logo Perusahaan" style="max-height: 60px; max-width: 140px; object-fit: contain;">
                    </div>
                    <div class="text-end">
                        <h5 class="fw-bold mb-1" id="so_detail_company_nama" style="color: #1e3a8a; letter-spacing: 0.3px;">PT. PERSADA AMAN SENTOSA</h5>
                        <div class="text-body-secondary small" id="so_detail_company_alamat" style="font-size: 0.82rem; max-width: 480px;">Jl Cinere Raya No 40-41, Depok, Jawa Barat</div>
                        <div class="text-body-secondary small" id="so_detail_company_kontak" style="font-size: 0.78rem;">-</div>
                    </div>
                </div>

                <!-- Judul Dokumen & Status Grid -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold fs-7" id="so_detail_nomor">
                            Nomor SO: -
                        </span>
                        <span class="text-muted small ms-2" id="so_detail_tanggal">
                            <i class="bi bi-calendar3 me-1"></i> Tanggal: -
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div id="so_detail_status_badge"></div>
                        <span class="badge bg-secondary-subtle text-body border px-2.5 py-1 rounded-pill small font-monospace">Tier: <strong id="so_detail_tier">-</strong></span>
                    </div>
                </div>

                <!-- Info Pelanggan & Pembayaran Grid -->
                <div class="row g-3 mb-4">
                    <!-- Kartu Informasi Pembeli -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body-tertiary h-100 shadow-sm" style="border-color: rgba(37, 99, 235, 0.15) !important;">
                            <div class="fw-bold text-primary small text-uppercase mb-2.5 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-person-fill"></i> Informasi Pembeli &amp; Pengiriman
                            </div>
                            <div class="d-flex flex-column gap-1.5 small">
                                <div class="d-flex justify-content-between" id="so_detail_instansi_wrap" style="display: none;">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Instansi / PT:</span>
                                    <span class="fw-bold text-primary text-end" id="so_detail_customer_pt">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Nama Pemesan:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="so_detail_customer_nama">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">No. Telepon:</span>
                                    <span class="text-body text-end font-monospace" id="so_detail_customer_telepon">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Alamat Kirim:</span>
                                    <span class="text-body text-end" id="so_detail_customer_alamat">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Pembayaran -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body-tertiary h-100 shadow-sm" style="border-color: rgba(37, 99, 235, 0.15) !important;">
                            <div class="fw-bold text-primary small text-uppercase mb-2.5 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-receipt"></i> Rincian Transaksi &amp; Pembayaran
                            </div>
                            <div class="d-flex flex-column gap-1.5 small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Metode Bayar:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="so_detail_metode_bayar">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Termin / Tenor:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="so_detail_termin">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Kasir / Sales:</span>
                                    <span class="text-body text-end" id="so_detail_sales_info">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 110px; flex-shrink: 0;">Catatan:</span>
                                    <span class="text-body-secondary text-end fst-italic" id="so_detail_catatan">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang -->
                <div class="table-responsive mb-4 border rounded-3 overflow-hidden shadow-sm" style="border-color: #cbd5e1 !important;">
                    <table class="table table-hover align-middle mb-0" id="so_detail_table" style="width: 100%;">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                                <th style="width: 45px;" class="text-center py-2.5">No</th>
                                <th class="py-2.5">Barang / Deskripsi Produk</th>
                                <th class="text-center py-2.5" style="width: 110px;">Qty</th>
                                <th class="text-end py-2.5" style="width: 140px;">Harga Satuan</th>
                                <th class="text-end py-2.5" style="width: 120px;">Diskon</th>
                                <th class="text-end py-2.5" style="width: 150px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="so_detail_items_tbody">
                            <!-- Items Injected Dynamically by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Total & Tanda Tangan -->
                <div class="row g-3">
                    <!-- Box Kiri: Ketentuan & Tanda Tangan -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body-tertiary h-100 d-flex flex-column justify-content-between shadow-sm">
                            <div class="small text-body-secondary mb-3 p-2.5 rounded-3 bg-body border">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                <em>Barang yang sudah dibeli / dipesan telah sesuai dengan spesifikasi faktur. Pembayaran dianggap sah setelah dana efektif masuk ke rekening perusahaan.</em>
                            </div>
                            <div class="d-flex justify-content-around text-center pt-3 border-top">
                                <div>
                                    <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.75rem;">Penerima / Pembeli</div>
                                    <div class="fw-bold small text-body-emphasis" id="so_detail_sig_buyer">( .................................... )</div>
                                </div>
                                <div>
                                    <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.75rem;">Hormat Kami,</div>
                                    <div class="fw-bold small text-body-emphasis" id="so_detail_sig_company">( Marketing / Kasir )</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Kanan: Rincian Finansial -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-body-tertiary shadow-sm">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Subtotal Produk:</span>
                                <span class="font-monospace fw-bold text-body-emphasis" id="so_detail_subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Diskon Order:</span>
                                <span class="font-monospace fw-bold text-danger" id="so_detail_diskon">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Pajak (PPN):</span>
                                <span class="font-monospace fw-bold text-info" id="so_detail_pajak">+ Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2.5" id="so_detail_ongkir_row" style="display: none;">
                                <span class="text-muted">Ongkir / Biaya Lain:</span>
                                <span class="font-monospace fw-bold text-primary" id="so_detail_ongkir">+ Rp 0</span>
                            </div>

                            <!-- Grand Total Box -->
                            <div class="d-flex justify-content-between align-items-center p-2.5 rounded-3 text-white mb-2 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);">
                                <span class="fw-bold fs-6">Grand Total:</span>
                                <span class="font-monospace fw-bold fs-4 text-white" id="so_detail_grand_total">Rp 0</span>
                            </div>
                            
                            {{-- Baris DP & Sisa Tagihan (Muncul jika ada DP) --}}
                            <div id="so_detail_dp_section" style="display: none;">
                                <div class="d-flex justify-content-between small px-2 py-1 mb-1">
                                    <span class="text-muted">Uang Muka (DP) <span id="so_detail_dp_label" class="font-monospace text-primary" style="font-size:0.75rem;"></span>:</span>
                                    <span class="font-monospace fw-bold text-primary" id="so_detail_dp_nominal">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 border" style="background: #fffbeb; border-color: #fde68a !important;">
                                    <span class="fw-bold small text-warning-emphasis"><i class="bi bi-clock-history me-1"></i> Sisa Tagihan:</span>
                                    <span class="font-monospace fw-bold fs-5 text-warning-emphasis" id="so_detail_sisa_tagihan">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex gap-2" id="so_detail_action_buttons">
                    <a href="{{ route('marketing.delivery-order.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn_do_from_order_detail" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-truck"></i> Kirim / Buat Surat Jalan
                    </a>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold d-none shadow-sm" id="btn_edit_order_from_detail">
                        <i class="bi bi-pencil-square me-1"></i> Edit Pesanan
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-1.5 fw-bold d-none shadow-sm" id="btn_cancel_order_from_detail">
                        <i class="bi bi-x-circle-fill me-1"></i> Batalkan
                    </button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
