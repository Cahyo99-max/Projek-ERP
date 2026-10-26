<!-- Modal Detail Surat Pesanan / Sales Invoice -->
<div class="modal modal-blur fade" id="modalDetailOrder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="max-width: 95vw; width: 95vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis">Detail Pesanan (SO)</h5>
                        <div class="text-body-secondary small">Ringkasan transaksi dan rincian produk pesanan.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm" id="btn_print_order" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-printer-fill"></i> Cetak SO
                    </a>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"></button>
                </div>
            </div>
            
            <div class="modal-body p-4" id="printable_order_area">
                
                <!-- Judul Dokumen & Status Grid -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom border-secondary-subtle">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold fs-7" id="so_detail_nomor">
                            SO: -
                        </span>
                        <span class="text-body-secondary small" id="so_detail_tanggal">
                            <i class="bi bi-calendar3 me-1"></i> -
                        </span>
                        <span id="so_detail_quotation_badge" style="display: none;"></span>
                        <span id="so_detail_tender_badge" style="display: none;"></span>
                        <span id="so_detail_invoice_badge" style="display: none;"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div id="so_detail_status_badge"></div>
                        <span class="badge bg-secondary-subtle text-body border border-secondary-subtle px-2.5 py-1 rounded-pill small font-monospace" id="so_detail_tier_badge">Tier: <strong id="so_detail_tier">-</strong></span>
                    </div>
                </div>

                <!-- Info Pelanggan & Pembayaran Grid -->
                <div class="row g-3.5 mb-4">
                    <!-- Kartu Informasi Pembeli -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 border border-secondary-subtle bg-body-tertiary h-100 shadow-xs">
                            <div class="fw-bold text-primary small text-uppercase mb-3.5 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                <i class="bi bi-person-fill"></i> Info Pelanggan
                            </div>
                            <div class="d-flex flex-column gap-3 small px-1">
                                <div class="d-flex justify-content-between align-items-center" id="so_detail_instansi_wrap" style="display: none;">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-building me-1.5"></i> Instansi:</span>
                                    <span class="fw-bold text-primary text-end text-break ps-2" id="so_detail_customer_pt">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-person me-1.5"></i> Pemesan:</span>
                                    <span class="fw-bold text-body-emphasis text-end text-break ps-2" id="so_detail_customer_nama">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-telephone me-1.5"></i> Telepon:</span>
                                    <span class="text-body text-end font-monospace ps-2" id="so_detail_customer_telepon">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-geo-alt me-1.5"></i> Alamat:</span>
                                    <span class="text-body text-end text-break ps-2" id="so_detail_customer_alamat">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Pembayaran -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 border border-secondary-subtle bg-body-tertiary h-100 shadow-xs">
                            <div class="fw-bold text-primary small text-uppercase mb-3.5 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                <i class="bi bi-credit-card-fill"></i> Info Pembayaran
                            </div>
                            <div class="d-flex flex-column gap-3 small px-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-wallet2 me-1.5"></i> Metode:</span>
                                    <span class="fw-bold text-body-emphasis text-end ps-2" id="so_detail_metode_bayar">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-hourglass-split me-1.5"></i> Termin:</span>
                                    <span class="fw-bold text-body-emphasis text-end ps-2" id="so_detail_termin">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-person-badge me-1.5"></i> Sales:</span>
                                    <span class="text-body text-end ps-2" id="so_detail_sales_info">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-body-secondary" style="width: 95px; flex-shrink: 0;"><i class="bi bi-person-check me-1.5"></i> Dibuat:</span>
                                    <span class="text-body text-end font-monospace small ps-2" id="so_detail_creator_info">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang -->
                <div class="table-responsive mb-4 border border-secondary-subtle rounded-4 overflow-hidden shadow-xs">
                    <table class="table table-hover align-middle mb-0" id="so_detail_table" style="width: 100%;">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                                <th style="width: 45px;" class="text-center py-3">No</th>
                                <th style="width: 55px;" class="text-center py-3">Foto</th>
                                <th class="py-3 px-3">Produk</th>
                                <th class="text-center py-3" style="width: 100px;">Qty</th>
                                <th class="text-end py-3 px-3" style="width: 130px;">Harga</th>
                                <th class="text-center py-3" style="width: 110px;">Diskon</th>
                                <th class="text-end py-3 px-3" style="width: 135px;">Subtotal</th>
                                <th class="text-center py-3" style="width: 110px;">Pajak (PPN)</th>
                            </tr>
                        </thead>
                        <tbody id="so_detail_items_tbody">
                            <!-- Items Injected Dynamically by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Total & Ketentuan -->
                <div class="row g-3.5">
                    <!-- Box Kiri: Syarat & Ketentuan, Catatan, Terbilang -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 border border-secondary-subtle bg-body-tertiary h-100 d-flex flex-column justify-content-between shadow-xs">
                            <div class="d-flex flex-column flex-grow-1">
                                <!-- Syarat & Ketentuan -->
                                <div class="mb-3 d-flex flex-column flex-grow-1" id="so_detail_syarat_ketentuan_wrap">
                                    <div class="fw-bold small text-uppercase text-body-secondary mb-2.5 d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                        <i class="bi bi-file-earmark-text text-primary"></i> Syarat &amp; Ketentuan:
                                    </div>
                                    <div class="small text-body p-3.5 px-4 rounded-3 bg-body border border-secondary-subtle font-sans flex-grow-1" id="so_detail_syarat_ketentuan" style="font-size: 0.82rem; line-height: 1.65; white-space: pre-line; min-height: 105px;">
                                        -
                                    </div>
                                </div>

                                <!-- Catatan Khusus (jika ada) -->
                                <div class="mb-3" id="so_detail_catatan_wrap" style="display: none;">
                                    <div class="fw-bold small text-uppercase text-body-secondary mb-2 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                                        <i class="bi bi-sticky text-warning"></i> Catatan:
                                    </div>
                                    <div class="small text-body p-3.5 px-4 rounded-3 bg-body border border-secondary-subtle fst-italic" id="so_detail_catatan" style="font-size: 0.8rem; line-height: 1.5;">
                                        -
                                    </div>
                                </div>
                            </div>

                            <!-- Terbilang Box Pinned at Bottom -->
                            <div class="p-3.5 px-4 rounded-3 bg-body border border-secondary-subtle mt-3 shadow-xs">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-chat-quote-fill text-primary"></i>
                                    <span class="small fw-bold text-uppercase text-body-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Terbilang:</span>
                                </div>
                                <div class="fw-bold fst-italic text-body-emphasis ps-1" id="so_detail_terbilang" style="font-size: 0.86rem; line-height: 1.5;">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Kanan: Rincian Finansial -->
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 border border-secondary-subtle bg-body-tertiary shadow-xs">
                            <div class="fw-bold text-primary small text-uppercase mb-3.5 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                <i class="bi bi-calculator"></i> Ringkasan Biaya
                            </div>

                            <div class="d-flex flex-column gap-2 mb-4 px-1">
                                <div class="d-flex justify-content-between align-items-center py-2.5 border-bottom border-secondary-subtle">
                                    <span class="text-body-secondary fw-semibold">Subtotal Item:</span>
                                    <span class="font-monospace fw-bold text-body-emphasis fs-6" id="so_detail_subtotal">Rp 0,00</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-2.5 border-bottom border-secondary-subtle" id="so_detail_pajak_row" style="display: none;">
                                    <span class="text-body-secondary fw-semibold" id="so_detail_pajak_label">Total Pajak (PPN):</span>
                                    <span class="font-monospace fw-bold text-info fs-6" id="so_detail_pajak">+ Rp 0,00</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-2.5" id="so_detail_ongkir_row" style="display: none;">
                                    <span class="text-body-secondary fw-semibold">Biaya Kirim (Ongkir):</span>
                                    <span class="font-monospace fw-bold text-primary fs-6" id="so_detail_ongkir">+ Rp 0,00</span>
                                </div>
                            </div>

                            <!-- Grand Total Box -->
                            <div class="d-flex justify-content-between align-items-center p-3.5 px-4 rounded-3 text-white mb-3 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);">
                                <div>
                                    <span class="fw-bold fs-6 d-block text-white mb-0.5">Grand Total:</span>
                                    <span class="small text-white-50" style="font-size: 0.73rem;">Total Akhir Tagihan</span>
                                </div>
                                <span class="font-monospace fw-bold fs-3 text-white" id="so_detail_grand_total">Rp 0,00</span>
                            </div>
                            
                            {{-- Baris DP (Muncul jika ada DP) --}}
                            <div id="so_detail_dp_section" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center small p-3 px-4 rounded-3 bg-body border border-secondary-subtle">
                                    <span class="text-body-secondary fw-semibold">Uang Muka (DP) <span id="so_detail_dp_label" class="font-monospace text-primary fw-semibold" style="font-size:0.75rem;"></span>:</span>
                                    <span class="font-monospace fw-bold text-primary fs-6" id="so_detail_dp_nominal">Rp 0,00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex gap-2" id="so_detail_action_buttons">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_edit_order_from_detail">
                        <i class="bi bi-pencil-square me-1"></i> Edit Pesanan
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_cancel_order_from_detail">
                        <i class="bi bi-x-circle me-1"></i> Batalkan
                    </button>
                    @can('tambah-delivery-order')
                    <a href="{{ route('marketing.delivery-order.index') }}" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn_do_from_order_detail" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-truck"></i> Buat Surat Jalan
                    </a>
                    @endcan
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
