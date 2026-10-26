<!-- Modal Edit Surat Jalan (Delivery Order) - Enterprise & Responsive Edition -->
<div class="modal modal-blur fade" id="modalEditDO" tabindex="-1" aria-labelledby="modalEditDOTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl do-modal-create-dialog">
        <form id="formEditDO" class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden do-modal-container">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_do_id" name="do_id">
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header px-3 px-md-4 py-3 d-flex justify-content-between align-items-center do-modal-header border-bottom flex-shrink-0">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="do-icon-badge shadow-sm flex-shrink-0" style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalEditDOTitle">Edit Surat Jalan</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-0.5" id="edit_do_nomor_badge" style="font-size: 0.72rem;">DO-...</span>
                            <span class="badge rounded-pill font-monospace px-2.5 py-0.5" id="edit_do_status_badge" style="font-size: 0.72rem;">-</span>
                        </div>
                        <div class="text-muted small mt-0.5 d-none d-sm-block" style="font-size: 0.76rem;">Perbarui data pengiriman, armada kurir, alamat tujuan, atau rincian item barang.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 p-md-4 do-modal-body" style="overflow-y: auto; max-height: calc(100vh - 160px);">
                
                <!-- Notice Banner jika DO sudah disetujui / dalam proses kirim -->
                <div id="edit_do_approved_alert" class="alert alert-warning py-2.5 px-3.5 rounded-3 d-none mb-3 border-warning-subtle shadow-sm" style="font-size: 0.8rem;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Pengiriman Sudah Diproses / Disetujui:</strong>
                            <div class="text-muted mt-0.5">Stok gudang dan nomor seri telah tercatat di sistem. Anda dapat memperbarui <strong>data logistik (ekspedisi, kurir, sopir, plat nomor, nomor resi, penerima, dan catatan)</strong>. Item barang &amp; gudang asal dikunci demi konsistensi stok &amp; jurnal akuntansi.</div>
                        </div>
                    </div>
                </div>

                <!-- Notice Banner jika DO sebelumnya DITOLAK / DIBATALKAN dan sedang direvisi -->
                <div id="edit_do_rejected_alert" class="alert alert-danger py-2.5 px-3.5 rounded-3 d-none mb-3 border-danger-subtle shadow-sm" style="font-size: 0.8rem;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <strong>Pengajuan Surat Jalan Sebelumnya Ditolak:</strong>
                            <div id="edit_do_rejected_reason" class="text-danger-emphasis mt-0.5 small fst-italic"></div>
                            <div class="text-muted mt-1" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>Anda dapat merevisi rincian item, kuantiti, alokasi nomor seri, atau data logistik tujuan. Setelah disimpan, pengiriman akan otomatis diajukan kembali ke status <strong>Menunggu Persetujuan</strong>.</div>
                        </div>
                    </div>
                </div>

                <!-- Section 1: Bento Card Dokumen Pengiriman, SO, & Gudang Asal -->
                <div class="card border-0 rounded-4 shadow-sm mb-4 do-card-section">
                    <div class="card-header bg-transparent py-2.5 px-3 px-md-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="fw-bold small text-body-emphasis d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-file-earmark-ruled-fill"></i></span>
                            <span>Dokumen Pengiriman &amp; Gudang Asal</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-body-secondary text-body-secondary font-monospace px-2.5 py-0.5 rounded-pill" style="font-size: 0.7rem;" id="edit_do_entity_badge">
                                Entitas Aktif
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3.5">
                        <div class="row g-3">
                            <!-- Kolom 1: Nomor Surat Jalan (DO) -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1.5 d-flex align-items-center gap-1.5 text-nowrap">
                                    <i class="bi bi-upc-scan text-primary"></i>
                                    <span>No. Surat Jalan (DO)</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-receipt-cutoff text-muted"></i></span>
                                    <input type="text" class="form-control do-form-control font-monospace fw-bold bg-body-secondary text-body-emphasis" id="edit_do_nomor_do" readonly>
                                    <span class="input-group-text do-input-badge font-monospace bg-light text-muted">
                                        <i class="bi bi-lock-fill me-1"></i>TERKUNCI
                                    </span>
                                </div>
                                <div class="do-help-text mt-1 text-muted small" style="font-size: 0.72rem;">Nomor DO bersifat permanen untuk audit dokumen.</div>
                            </div>

                            <!-- Kolom 2: Surat Pesanan Penjualan (SO) -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label small fw-semibold text-muted mb-1.5 d-flex align-items-center gap-1.5">
                                    <i class="bi bi-cart-check text-primary"></i>
                                    <span>Surat Pesanan (SO) Terkait</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-receipt text-muted"></i></span>
                                    <input type="text" class="form-control do-form-control font-monospace fw-bold bg-body-secondary text-body-emphasis" id="edit_do_so_text" readonly>
                                </div>
                                <div class="do-help-text mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1" style="min-height: 18px;">
                                    <span id="edit_do_customer_preview" class="text-truncate text-muted small" style="font-size: 0.72rem;">-</span>
                                    <span id="edit_do_so_tender_badge" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill fw-bold" style="display: none; font-size: 0.68rem;"></span>
                                </div>
                            </div>

                            <!-- Kolom 3: Gudang Asal Pengeluaran Barang -->
                            <div class="col-lg-4 col-md-12">
                                <label class="form-label small fw-semibold text-muted mb-1.5 d-flex align-items-center gap-1.5">
                                    <i class="bi bi-building-check text-primary"></i>
                                    <span>Gudang Asal Pengeluaran</span>
                                    <span class="text-danger" id="edit_do_gudang_required">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text do-input-icon"><i class="bi bi-box-seam text-muted"></i></span>
                                    <select class="form-select do-form-control fw-semibold" id="edit_do_gudang_id" name="gudang_id">
                                        <option value="">-- Pilih Gudang Asal --</option>
                                        @foreach($gudangs as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama_gudang }} ({{ $g->kode_gudang }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="do-help-text mt-1 text-truncate" id="edit_do_gudang_hint" style="min-height: 18px; font-size: 0.72rem;">
                                    <i class="bi bi-layers-half me-1"></i>Gudang fisik asal stok dikeluarkan
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
                                        <input type="text" class="form-control do-form-control" id="edit_do_nama_penerima" name="nama_penerima" placeholder="Contoh: PT. Sumber Makmur / Bpk. Rudi">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Nomor Telepon / WhatsApp</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon"><i class="bi bi-telephone text-muted"></i></span>
                                        <input type="text" class="form-control do-form-control font-monospace" id="edit_do_telepon_penerima" name="telepon_penerima" placeholder="Contoh: 081234567890">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Alamat Lengkap Pengiriman</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon align-items-start pt-2"><i class="bi bi-geo-alt text-muted"></i></span>
                                        <textarea class="form-control do-form-control" id="edit_do_alamat_tujuan" name="alamat_tujuan" rows="2" placeholder="Alamat lengkap tujuan fisik barang..."></textarea>
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
                                <span class="badge bg-secondary-subtle text-muted rounded-pill small font-monospace" style="font-size: 0.7rem;">LOGISTIK</span>
                            </div>
                            <div class="card-body p-3.5 d-flex flex-column gap-2.5">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Tgl Pengiriman <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-calendar3 text-muted"></i></span>
                                            <input type="date" class="form-control do-form-control font-monospace" id="edit_do_tanggal_kirim" name="tanggal_kirim" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Metode Pengiriman</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-diagram-2 text-muted"></i></span>
                                            <select class="form-select do-form-control" id="edit_do_metode_pengiriman" name="metode_pengiriman">
                                                <option value="Kurir Internal">Kurir Internal</option>
                                                <option value="Ekspedisi / Logistik">Ekspedisi / Kargo</option>
                                                <option value="Ambil Sendiri">Ambil Sendiri</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Supir / Ekspedisi</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-person-badge text-muted"></i></span>
                                            <input type="text" class="form-control do-form-control" id="edit_do_nama_sopir" name="nama_sopir" placeholder="Nama sopir / JNE / J&T...">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">No. Resi / Plat Mobil</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text do-input-icon"><i class="bi bi-upc-scan text-muted"></i></span>
                                            <input type="text" class="form-control do-form-control font-monospace" id="edit_do_nomor_resi" name="nomor_resi" placeholder="Plat nomor / No Resi AWB...">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-semibold text-muted mb-1">Instruksi Khusus / Catatan</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text do-input-icon"><i class="bi bi-chat-left-text text-muted"></i></span>
                                        <input type="text" class="form-control do-form-control" id="edit_do_catatan_pengiriman" name="catatan_pengiriman" placeholder="Contoh: Titip di pos security, barang rentan benturan.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Tabel Item Barang yang Dikirim -->
                <div class="card border-0 rounded-4 shadow-sm overflow-hidden do-card-section mb-2">
                    <div class="card-header bg-transparent py-2.5 px-3 px-md-3.5 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-body-emphasis small d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-boxes"></i></span>
                            <span>Rincian Item Produk &amp; Alokasi Pengiriman</span>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1" id="edit_do_items_count_badge">0 Item</span>
                        </div>
                    </div>

                    <!-- Mobile Scroll Hint -->
                    <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-1.5 bg-body-tertiary border-bottom small text-muted font-monospace" style="font-size: 0.72rem;">
                        <span><i class="bi bi-arrow-left-right me-1 text-primary"></i>Geser tabel ke samping untuk rincian &amp; aksi</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill font-monospace" style="font-size: 0.65rem;">Geser &rarr;</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table_edit_do_items" style="min-width: 1080px;">
                            <thead>
                                <tr class="do-table-header">
                                    <th style="width: 45px;" class="text-center py-2.5">No</th>
                                    <th class="py-2.5" style="min-width: 230px;">Nama Produk / Barang</th>
                                    <th class="text-center py-2.5" style="width: 105px;">Total Pesanan</th>
                                    <th class="text-center py-2.5" style="width: 110px;">Batas Maksimal</th>
                                    <th class="text-center py-2.5" style="width: 125px;">Stok Gudang</th>
                                    <th class="text-center py-2.5" style="width: 145px;">Jumlah Kirim <span class="text-warning">*</span></th>
                                    <th class="py-2.5" style="min-width: 150px;">Catatan </th>
                                    <th class="text-center py-2.5" style="width: 210px;">Aksi / Serial Number</th>
                                </tr>
                            </thead>
                            <tbody id="edit_do_items_tbody">
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted small fst-italic">
                                        <i class="bi bi-hourglass-split me-1"></i>Memuat rincian item pengiriman...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Toolbar -->
            <div class="modal-footer px-3 px-md-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2.5 do-modal-footer border-top flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small" id="edit_do_footer_hint" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check text-success me-1"></i>Pastikan seluruh perubahan telah diperiksa sebelum disimpan.
                    </span>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 fw-semibold shadow-none flex-grow-1 flex-sm-grow-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 do-btn-primary flex-grow-1 flex-sm-grow-0" id="btn_submit_edit_do">
                        <i class="bi bi-check-circle-fill fs-6"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
