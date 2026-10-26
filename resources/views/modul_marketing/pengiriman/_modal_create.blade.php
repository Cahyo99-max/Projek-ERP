<!-- Modal Buat Surat Jalan Baru (Delivery Order) - Enterprise & Responsive Edition -->
<div class="modal modal-blur fade" id="modalCreateDO" tabindex="-1" aria-labelledby="modalCreateDOTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl do-modal-create-dialog">
        <form id="formCreateDO" class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden do-modal-container">
            @csrf
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header px-3 px-md-4 py-3 d-flex justify-content-between align-items-center do-modal-header border-bottom flex-shrink-0">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="do-icon-badge shadow-sm flex-shrink-0" style="width: 38px; height: 38px; border-radius: 10px;">
                        <i class="bi bi-truck-flatbed fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalCreateDOTitle">Penerbitan Surat Jalan</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">DELIVERY ORDER</span>
                        </div>
                        <div class="text-muted small mt-0.5 d-none d-sm-block" style="font-size: 0.76rem;">Keluarkan stok fisik barang dari gudang berdasarkan Sales Order (SO).</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4 do-modal-body" style="overflow-y: auto; max-height: calc(100vh - 160px);">
                
                <!-- Section 1: Bento Card Dokumen Pengiriman, SO, & Gudang Asal -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 do-card-section">
                    <div class="card-header bg-transparent py-2.5 px-3 px-md-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="fw-bold small text-body-emphasis d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-file-earmark-ruled-fill"></i></span>
                            <span>Dokumen Pengiriman &amp; Gudang Asal</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(!empty(session('active_branch_id')))
                                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Cabang Terpilih
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                    <i class="bi bi-building-fill me-1"></i>Kantor Pusat
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-3.5">
                        <div class="row g-3">
                            <!-- Kolom 1: Nomor Surat Jalan (DO) -->
                            <div class="col-lg-4 col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1.5 gap-2">
                                    <label class="form-label small fw-semibold text-muted mb-0 d-flex align-items-center gap-1.5 text-nowrap">
                                        <i class="bi bi-upc-scan text-primary"></i>
                                        <span>No. Surat Jalan (DO)</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    
                                    <!-- Modern Pill Segmented Switcher -->
                                    <div class="do-pill-switch rounded-pill d-inline-flex align-items-center flex-shrink-0" role="group">
                                        <input type="radio" class="btn-check" name="mode_nomor_do" id="mode_do_auto" value="auto" checked autocomplete="off">
                                        <label class="do-pill-btn rounded-pill" for="mode_do_auto">
                                            <i class="bi bi-magic me-1"></i>Auto
                                        </label>

                                        <input type="radio" class="btn-check" name="mode_nomor_do" id="mode_do_manual" value="manual" autocomplete="off">
                                        <label class="do-pill-btn rounded-pill" for="mode_do_manual">
                                            <i class="bi bi-pencil me-1"></i>Manual
                                        </label>
                                    </div>
                                </div>

                                <div class="input-group input-group-sm">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-receipt-cutoff text-muted"></i></span>
                                    <input type="text" class="form-control do-form-control font-monospace fw-bold" id="do_nomor_do" name="nomor_do" placeholder="[Otomatis dari Sistem]" readonly>
                                    <span class="input-group-text do-input-badge font-monospace" id="do_nomor_badge">
                                        <i class="bi bi-shield-check text-success me-1"></i>AUTO
                                    </span>
                                </div>

                                <div class="do-help-text mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <span id="do_nomor_hint" class="text-truncate"></span>
                                    <span id="do_nomor_feedback" class="fw-semibold"></span>
                                </div>
                            </div>

                            <!-- Kolom 2: Surat Pesanan Penjualan (SO) -->
                            <div class="col-lg-4 col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <label class="form-label small fw-semibold text-muted mb-0 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-cart-check text-primary"></i>
                                        <span>Surat Pesanan (SO)</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                </div>

                                <div class="w-100 position-relative">
                                    <select class="form-select form-select-sm do-form-control fw-semibold" id="do_select_so" name="sales_order_id" style="width: 100%;">
                                        <option value="">-- Memuat Sales Order... --</option>
                                    </select>
                                </div>

                                <div class="do-help-text mt-1 text-truncate d-flex align-items-center justify-content-between" style="min-height: 18px;">
                                    <span id="do_so_help_text"><i class="bi bi-search me-1"></i>Ketik nomor SO atau nama pelanggan</span>
                                    <span id="do_so_tender_badge" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill fw-bold" style="display: none; font-size: 0.7rem;"></span>
                                </div>
                            </div>

                            <!-- Kolom 3: Gudang Asal Pengeluaran Barang -->
                            <div class="col-lg-4 col-md-12">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <label class="form-label small fw-semibold text-muted mb-0 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-building-check text-primary"></i>
                                        <span>Gudang Asal Pengeluaran</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                </div>

                                <div class="input-group input-group-sm">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-box-seam text-muted"></i></span>
                                    <select class="form-select do-form-control fw-semibold" id="do_gudang_id" name="gudang_id" required>
                                        <option value="">-- Pilih Gudang Asal --</option>
                                        @foreach($gudangs as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="do-help-text mt-1 text-truncate" style="min-height: 18px;">
                                    <i class="bi bi-layers-half me-1"></i>Potong stok fisik langsung dari gudang ini
                                </div>
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
                    <div class="card-header bg-transparent py-2.5 px-3 px-md-3.5 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-body-emphasis small d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-boxes"></i></span>
                            <span>Checklist Item Produk &amp; Alokasi Pengiriman</span>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 0.72rem;">MENDUKUNG PARSIAL</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1" id="do_items_count_badge">0 Item</span>
                        </div>
                    </div>

                    <!-- Mobile Scroll Hint -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-1.5 bg-body-tertiary border-bottom small text-muted font-monospace" style="font-size: 0.72rem;">
                        <span><i class="bi bi-arrow-left-right me-1 text-primary"></i>Geser tabel ke samping untuk rincian &amp; aksi</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill font-monospace" style="font-size: 0.65rem;">Geser &rarr;</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table_do_items" style="min-width: 1080px;">
                            <thead>
                                <tr class="do-table-header">
                                    <th style="width: 45px;" class="text-center py-2.5">No</th>
                                    <th class="py-2.5" style="min-width: 230px;">Nama Produk / Barang</th>
                                    <th class="text-center py-2.5" style="width: 105px;">Total Pesanan</th>
                                    <th class="text-center py-2.5" style="width: 105px;">Sudah Kirim</th>
                                    <th class="text-center py-2.5" style="width: 110px;">Sisa Belum Kirim</th>
                                    <th class="text-center py-2.5" style="width: 125px;">Stok Gudang Asal</th>
                                    <th class="text-center py-2.5" style="width: 145px;">Kirim Sekarang <span class="text-warning">*</span></th>
                                    <th class="py-2.5" style="min-width: 150px;">Catatan Koli</th>
                                    <th class="text-center py-2.5" style="width: 210px;">Aksi / Serial Number</th>
                                </tr>
                            </thead>
                            <tbody id="do_items_tbody">
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small fst-italic">
                                        Silakan pilih Sales Order (SO) di atas untuk memuat daftar item barang.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Toolbar (Responsif Mobile & PC) -->
            <div class="modal-footer px-3 px-md-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2.5 do-modal-footer border-top flex-shrink-0">
                <div class="d-flex align-items-center justify-content-between justify-content-sm-start gap-2">
                    <input type="hidden" name="status" value="Menunggu Persetujuan">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill font-monospace small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-clock-history"></i> Menunggu Persetujuan Atasan
                    </span>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-none flex-grow-1 flex-sm-grow-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 do-btn-primary flex-grow-1 flex-sm-grow-0" id="btn_submit_do">
                        <i class="bi bi-send-check fs-6"></i>
                        <span>Ajukan Pengiriman</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
