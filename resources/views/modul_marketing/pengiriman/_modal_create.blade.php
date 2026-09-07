<!-- Modal Buat Surat Jalan Baru (Delivery Order) - Enterprise Edition -->
<div class="modal modal-blur fade" id="modalCreateDO" tabindex="-1" aria-labelledby="modalCreateDOTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <form id="formCreateDO" class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden do-modal-container">
            @csrf
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header px-4 py-3.5 d-flex justify-content-between align-items-center do-modal-header border-bottom flex-shrink-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="do-icon-badge shadow-sm">
                        <i class="bi bi-truck-flatbed fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalCreateDOTitle">Penerbitan Surat Jalan Pengiriman</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 0.72rem;">DELIVERY ORDER</span>
                        </div>
                        <div class="text-muted small mt-0.5">Keluarkan stok fisik barang dari gudang berdasarkan pesanan penjualan resmi (SO).</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 do-modal-body" style="overflow-y: auto; max-height: calc(100vh - 210px);">
                
                <!-- Section 1: Bento Card Pemilihan Sales Order & Gudang Asal -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 do-card-section">
                    <div class="card-body p-3.5">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                                    <i class="bi bi-receipt text-primary fs-6"></i> 
                                    <span>Surat Pesanan Penjualan (SO)</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-search text-primary"></i></span>
                                    <select class="form-select do-form-control fw-semibold" id="do_select_so" name="sales_order_id">
                                        <option value="">-- Memuat Daftar Sales Order... --</option>
                                    </select>
                                </div>
                                <div class="do-help-text mt-1">Pilih nomor SO untuk mengisi rincian pembeli dan daftar produk secara otomatis.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-body-emphasis mb-1.5 d-flex align-items-center gap-1.5">
                                    <i class="bi bi-building-check text-primary fs-6"></i> 
                                    <span>Gudang Asal Pengeluaran Barang</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-box-seam text-primary"></i></span>
                                    <select class="form-select do-form-control fw-semibold" id="do_gudang_id" name="gudang_id" required>
                                        <option value="">-- Pilih Gudang Asal --</option>
                                        @foreach($gudangs as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama_gudang }} &nbsp;({{ $g->kode_gudang }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="do-help-text mt-1">Stok fisik barang akan dipotong secara aktual &amp; otomatis dari kartu stok gudang ini.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Info Penerima & Logistik Bento Grid -->
                <div class="row g-3 mb-4">
                    <!-- Box Kiri: Penerima & Alamat Tujuan -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100 do-card-section">
                            <div class="card-header bg-transparent py-2.5 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                                <div class="fw-bold small text-body-emphasis d-flex align-items-center gap-2">
                                    <span class="do-mini-badge"><i class="bi bi-person-fill"></i></span>
                                    <span>Tujuan &amp; Identitas Penerima</span>
                                </div>
                                <span class="badge bg-secondary-subtle text-muted rounded-pill small font-monospace" style="font-size: 0.7rem;">DESTINASI</span>
                            </div>
                            <div class="card-body p-3.5 d-flex flex-column gap-2.5">
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Nama Instansi / Penerima</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon"><i class="bi bi-building text-muted"></i></span>
                                        <input type="text" class="form-control do-form-control" id="do_nama_penerima" name="nama_penerima" placeholder="Contoh: PT. Sumber Makmur / Bpk. Rudi">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Nomor Telepon / WhatsApp</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon"><i class="bi bi-telephone text-muted"></i></span>
                                        <input type="text" class="form-control do-form-control font-monospace" id="do_telepon_penerima" name="telepon_penerima" placeholder="Contoh: 081234567890">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Alamat Lengkap Pengiriman</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon align-items-start pt-2"><i class="bi bi-geo-alt text-muted"></i></span>
                                        <textarea class="form-control do-form-control" id="do_alamat_tujuan" name="alamat_tujuan" rows="2" placeholder="Alamat lengkap tujuan fisik barang..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Kanan: Ekspedisi & Logistik -->
                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100 do-card-section">
                            <div class="card-header bg-transparent py-2.5 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                                <div class="fw-bold small text-body-emphasis d-flex align-items-center gap-2">
                                    <span class="do-mini-badge"><i class="bi bi-truck"></i></span>
                                    <span>Ekspedisi &amp; Armada Kurir</span>
                                </div>
                                <span class="badge bg-secondary-subtle text-muted rounded-pill small font-monospace" style="font-size: 0.7rem;">PENGIRIMAN</span>
                            </div>
                            <div class="card-body p-3.5 d-flex flex-column gap-2.5">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Tgl Pengiriman <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-calendar3 text-muted"></i></span>
                                            <input type="date" class="form-control do-form-control font-monospace" id="do_tanggal_kirim" name="tanggal_kirim" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Metode Pengiriman</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-diagram-2 text-muted"></i></span>
                                            <select class="form-select do-form-control" id="do_metode_pengiriman" name="metode_pengiriman">
                                                <option value="Kurir Internal">Kurir Internal</option>
                                                <option value="Ekspedisi / Logistik">Ekspedisi / Kargo</option>
                                                <option value="Ambil Sendiri">Ambil Sendiri (Self-Pickup)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Supir / Kurir</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-person-badge text-muted"></i></span>
                                            <input type="text" class="form-control do-form-control" id="do_nama_sopir" name="nama_sopir" placeholder="Nama sopir / JNE / J&T...">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">No. Resi / Plat Mobil</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-upc-scan text-muted"></i></span>
                                            <input type="text" class="form-control do-form-control font-monospace" id="do_nomor_resi" name="nomor_resi" placeholder="Plat nomor / No Resi AWB...">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Instruksi Khusus / Catatan</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon"><i class="bi bi-chat-left-text text-muted"></i></span>
                                        <input type="text" class="form-control do-form-control" id="do_catatan_pengiriman" name="catatan_pengiriman" placeholder="Contoh: Titip di pos security, barang rentan benturan.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Tabel Item Barang yang Dikirim (Mendukung Pengiriman Parsial) -->
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden do-card-section mb-2">
                    <div class="card-header bg-transparent py-2.5 px-3.5 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-body-emphasis small d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-boxes"></i></span>
                            <span>Checklist Item Produk &amp; Alokasi Pengiriman</span>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 0.72rem;">MENDUKUNG PARSIAL</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1" id="do_items_count_badge">0 Item</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table_do_items">
                            <thead>
                                <tr class="do-table-header">
                                    <th style="width: 40px;" class="text-center py-2.5">No</th>
                                    <th class="py-2.5">Nama Produk / Barang</th>
                                    <th class="text-center py-2.5" style="width: 120px;">Total Pesanan</th>
                                    <th class="text-center py-2.5" style="width: 120px;">Sudah Dikirim</th>
                                    <th class="text-center py-2.5" style="width: 130px;">Sisa Belum Kirim</th>
                                    <th class="text-center py-2.5" style="width: 160px;">Kirim Sekarang <span class="text-warning">*</span></th>
                                    <th class="py-2.5" style="width: 170px;">Catatan Koli</th>
                                </tr>
                            </thead>
                            <tbody id="do_items_tbody">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small fst-italic">
                                        Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Toolbar -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center do-modal-footer border-top flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label small fw-semibold text-muted mb-0">Status Awal:</label>
                    <select class="form-select form-select-sm do-form-control rounded-pill font-monospace fw-bold" name="status" id="do_status_create" style="width: 160px;">
                        <option value="Siap Dikirim">Siap Dikirim</option>
                        <option value="Dalam Perjalanan">Dalam Perjalanan</option>
                        <option value="Draft">Draft</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-2 do-btn-primary" id="btn_submit_do">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Simpan Surat Jalan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
