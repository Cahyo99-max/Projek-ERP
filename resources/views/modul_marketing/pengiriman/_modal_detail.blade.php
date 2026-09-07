<!-- Modal Detail Surat Jalan / Delivery Order (Cinematic View) -->
<div class="modal modal-blur fade" id="modalDetailDO" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center" style="background: rgba(37, 99, 235, 0.04);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Rincian Surat Jalan &amp; Pengiriman</h5>
                        <div class="text-muted small">Informasi pengeluaran fisik barang dan pelacakan ekspedisi/kurir.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm" id="btn_print_do_modal" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-printer-fill"></i> Cetak Surat Jalan (A4)
                    </a>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"></button>
                </div>
            </div>
            
            <div class="modal-body p-4" id="printable_do_area">
                
                <!-- Kop Dokumen / Perusahaan -->
                <div class="d-flex justify-content-between align-items-center pb-3 mb-3" style="border-bottom: 2.5px solid #1e40af;">
                    <div class="d-flex align-items-center gap-3">
                        <img id="detail_do_company_logo" src="{{ asset('gambar/logo.png') }}" alt="Logo Perusahaan" style="max-height: 55px; max-width: 130px; object-fit: contain;">
                    </div>
                    <div class="text-end">
                        <h5 class="fw-bold mb-1 text-primary" id="detail_do_company_nama">PT. PERSADA AMAN SENTOSA</h5>
                        <div class="text-body-secondary small" id="detail_do_company_alamat">General Supplier, Military, Police &amp; Tactical Equipment</div>
                        <div class="text-body-secondary small" id="detail_do_company_kontak">-</div>
                    </div>
                </div>

                <!-- Nomor DO & Header Meta -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold" id="detail_do_nomor">
                            Nomor DO: -
                        </span>
                        <span class="text-muted small ms-2" id="detail_do_tanggal">
                            <i class="bi bi-calendar3 me-1"></i> Tgl Kirim: -
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle text-body border px-2.5 py-1 rounded-pill small font-monospace">
                            Gudang: <strong id="detail_do_gudang">-</strong>
                        </span>
                        <div id="detail_do_status_badge"></div>
                    </div>
                </div>

                <!-- Info Grid: Tujuan & Ekspedisi -->
                <div class="row g-3 mb-4">
                    <!-- Kartu Informasi Penerima -->
                    <div class="col-md-6">
                        <div class="p-3.5 rounded-4 border bg-body-tertiary h-100 shadow-sm" style="border-color: rgba(37, 99, 235, 0.15) !important;">
                            <div class="fw-bold text-primary small text-uppercase mb-2.5 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-person-fill"></i> Tujuan &amp; Informasi Penerima
                            </div>
                            <div class="d-flex flex-column gap-1.5 small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">No. Pesanan (SO):</span>
                                    <span class="fw-bold text-primary text-end font-monospace" id="detail_do_nomor_so">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">Nama Penerima:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="detail_do_penerima">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">No. Telepon:</span>
                                    <span class="text-body text-end font-monospace" id="detail_do_telepon">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">Alamat Kirim:</span>
                                    <span class="text-body text-end" id="detail_do_alamat">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Logistik & Kurir -->
                    <div class="col-md-6">
                        <div class="p-3.5 rounded-4 border bg-body-tertiary h-100 shadow-sm" style="border-color: rgba(37, 99, 235, 0.15) !important;">
                            <div class="fw-bold text-primary small text-uppercase mb-2.5 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-truck"></i> Detail Logistik &amp; Pengiriman
                            </div>
                            <div class="d-flex flex-column gap-1.5 small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">Metode Kirim:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="detail_do_metode">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">Ekspedisi / Supir:</span>
                                    <span class="fw-bold text-body-emphasis text-end" id="detail_do_ekspedisi">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">No. Resi / Plat:</span>
                                    <span class="text-body text-end font-monospace fw-semibold" id="detail_do_resi">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted" style="width: 120px; flex-shrink: 0;">Catatan / Intruksi:</span>
                                    <span class="text-body-secondary text-end fst-italic" id="detail_do_catatan">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang yang Dikirim -->
                <div class="table-responsive mb-4 border rounded-3 overflow-hidden shadow-sm">
                    <table class="table table-hover align-middle mb-0" id="detail_do_table">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                                <th style="width: 45px;" class="text-center py-2.5">No</th>
                                <th class="py-2.5">Nama Produk / Barang</th>
                                <th class="text-center py-2.5" style="width: 130px;">Qty Order</th>
                                <th class="text-center py-2.5" style="width: 140px;">Qty Dikirim</th>
                                <th class="py-2.5" style="width: 180px;">Keterangan Item</th>
                            </tr>
                        </thead>
                        <tbody id="detail_do_items_tbody">
                            <!-- Injected Dynamically by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Ketentuan & 4 Kolom Tanda Tangan Resmi -->
                <div class="p-3.5 rounded-4 border bg-body-tertiary shadow-sm">
                    <div class="small text-body-secondary mb-3 p-2.5 rounded-3 bg-body border">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <em>Barang telah diperiksa dan diserahkan dalam kondisi baik, lengkap, dan sesuai pesanan. Mohon periksa kembali barang sebelum menandatangani bukti tanda terima ini.</em>
                    </div>
                    <div class="row text-center pt-2 border-top g-3">
                        <div class="col-3">
                            <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.72rem;">Dibuat Oleh,</div>
                            <div class="fw-bold small text-body-emphasis" id="detail_do_sig_creator">( Staf Logistik )</div>
                        </div>
                        <div class="col-3">
                            <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.72rem;">Pengemudi / Kurir,</div>
                            <div class="fw-bold small text-body-emphasis" id="detail_do_sig_driver">( .................................... )</div>
                        </div>
                        <div class="col-3">
                            <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.72rem;">Security / Gudang,</div>
                            <div class="fw-bold small text-body-emphasis">( .................................... )</div>
                        </div>
                        <div class="col-3">
                            <div class="text-body-secondary small mb-4 text-uppercase fw-semibold" style="font-size: 0.72rem;">Penerima Barang,</div>
                            <div class="fw-bold small text-body-emphasis" id="detail_do_sig_receiver">( .................................... )</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-semibold">Ubah Status Pengiriman:</span>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 py-1 font-monospace btn-quick-status d-none" id="btn_status_siap" data-status="Siap Dikirim">Siap Dikirim</button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1 font-monospace btn-quick-status d-none" id="btn_status_kirim" data-status="Dalam Perjalanan">Kirim (In Transit)</button>
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 font-monospace btn-quick-status d-none" id="btn_status_selesai" data-status="Terkirim">Terkirim (Selesai)</button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
