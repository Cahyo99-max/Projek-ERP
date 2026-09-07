<x-modal_form id="modalEditBarang" formId="formEditBarang" size="modal-xl" title="<i class='bi bi-pencil-square me-2'></i> Edit Barang">
    <x-slot name="slot">
        <input type="hidden" id="edit_barang_id">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" name="kode_barang" id="edit_kode_barang"
                        class="form-control form-control-custom"
                        placeholder="Kode Barang" autocomplete="off" required>
                    <button class="btn btn-outline-primary btn-scan-barcode" type="button" data-target="#edit_kode_barang" title="Scan via Kamera">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                <input type="text" name="nama_barang" id="edit_nama_barang"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Kertas A4, Semen Gresik" autocomplete="off" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">Nama Alias / Dagang</label>
                <input type="text" name="nama_alias" id="edit_nama_alias"
                    class="form-control form-control-custom"
                    placeholder="Contoh: Kertas Fotokopi" autocomplete="off">
            </div>
            <div class="col-12 col-md-8">
                <label class="form-label fw-semibold">Tipe Item / Kontrol Keuangan <span class="text-danger">*</span></label>
                <select name="tipe_item" id="edit_tipe_item" class="form-select form-control-custom" required>
                    <option value="Stok">Stok (Inventory)</option>
                    <option value="Aset">Aset Tetap (Laptop, Kendaraan)</option>
                    <option value="Jasa">Jasa / Biaya (Lisensi Software, Maintenance)</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end" id="edit_wrapper_has_variants">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="has_variants" id="edit_has_variants" value="1" style="cursor:pointer;">
                    <label class="form-check-label fw-semibold" for="edit_has_variants" style="cursor:pointer;">
                        Memiliki Varian (Ukuran)
                    </label>
                </div>
            </div>

            <!-- Varian Form Section -->
            <div class="col-12" id="edit_varian_wrapper" style="display: none;">
                <hr class="my-2">
                <div class="uom-conversion-box mb-2">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2.5">
                        <div>
                            <label class="form-label fw-bold text-primary mb-0 fs-6 d-flex align-items-center">
                                <i class="bi bi-tags-fill me-2"></i> Daftar Varian Produk (Ukuran & Warna)
                            </label>
                            <div class="text-muted small mt-0.5" style="font-size: 0.76rem;">
                                Daftarkan kombinasi ukuran, tipe/generasi, atau warna untuk produk ini.
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs fw-semibold flex-shrink-0" id="btn_edit_varian_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Baris Varian
                        </button>
                    </div>
                    <div class="table-responsive uom-table-container">
                        <table class="table table-sm uom-table align-middle mb-0" id="table_edit_varian">
                            <thead>
                                <tr>
                                    <th style="width: 220px;">Ukuran / Tipe Varian <span class="text-danger">*</span></th>
                                    <th style="width: 260px;">Warna</th>
                                    <th style="width: 200px;">Kode SKU Varian</th>
                                    <th style="width: 65px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_edit_varian">
                                <!-- baris varian akan dimasukkan via js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="col-12 col-md-12">
                <label class="form-label fw-semibold">Kategori Barang <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="hidden" name="kategori_barang_id" id="edit_kategori_barang_id" required>
                    <input type="text" id="edit_kategori_display" class="form-control form-control-custom fw-semibold" placeholder="-- Klik / Cari Kategori Barang --" readonly onclick="openLookupKategoriBarang('edit')" style="cursor: pointer;">
                    <button type="button" class="btn btn-outline-primary px-3.5 fw-semibold shadow-xs" onclick="openLookupKategoriBarang('edit')" title="Cari Kategori Barang">
                        <i class="bi bi-search me-1.5"></i>Cari Kategori
                    </button>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Klik tombol cari untuk memilih kategori pengelompokan barang.</small>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Brand / Merk</label>
                <select name="brand_id" id="edit_brand_id" class="form-select form-control-custom">
                    <option value="">-- Tanpa Merk --</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Tipe Barang <span class="text-danger">*</span></label>
                <select name="tipe_barang_id" id="edit_tipe_barang_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Tipe --</option>
                    @foreach ($tipes as $tipe)
                        <option value="{{ $tipe->id }}">{{ $tipe->nama_tipe }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold mb-1">Satuan Dasar <span class="text-danger">*</span></label>
                <select name="satuan_id" id="edit_satuan_id" class="form-select form-control-custom" required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuans as $sat)
                        <option value="{{ $sat->id }}">{{ $sat->nama_satuan }} ({{ $sat->singkatan }})</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1" style="font-size: 0.72rem; line-height: 1.2;">
                    <i class="bi bi-box2 text-primary me-0.5"></i> Satuan terkecil untuk <b>stok gudang & produksi</b>.
                </small>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Pelacakan Khusus (SN / Batch) <span class="text-danger">*</span></label>
                <select name="tipe_pelacakan" id="edit_tipe_pelacakan" class="form-select form-control-custom" required>
                    <option value="None" selected>Tanpa Serial Number (Standar)</option>
                    <option value="Serial Number">Serial Number (Wajib SN per Unit)</option>
                    <option value="Batch">Batch / Lot Number</option>
                </select>
            </div>
            
            <div class="col-12 edit-physical-fields"><hr class="my-1"></div>

            <!-- Seksi Konversi Satuan (Multi-Satuan) -->
            <div class="col-12 edit-stock-fields" id="edit_konversi_wrapper">
                <div class="uom-conversion-box mb-1">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2.5">
                        <div>
                            <label class="form-label fw-bold text-primary mb-0 fs-6 d-flex align-items-center">
                                <i class="bi bi-arrow-left-right me-2"></i> Satuan Alternatif & Konversi
                            </label>
                            <div class="text-muted small mt-0.5" style="font-size: 0.73rem;">
                                Satuan kemasan besar untuk <b>Pembelian (PO)</b> atau <b>Penjualan</b> (misal: Rol, Dus, Box).
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs fw-semibold flex-shrink-0" id="btn_edit_konversi_row">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Satuan Alternatif
                        </button>
                    </div>
                    <div class="table-responsive uom-table-container">
                        <table class="table table-sm uom-table align-middle mb-0" id="table_edit_konversi">
                            <thead>
                                <tr>
                                    <th style="width: 230px;">Satuan Alternatif (Beli / Jual) <span class="text-danger">*</span></th>
                                    <th style="width: 270px;">Isi / Rasio ke Satuan Dasar <span class="text-danger">*</span></th>
                                    <th style="width: 200px;">Aktif Untuk</th>
                                    <th style="width: 65px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_edit_konversi">
                                <tr class="empty-konversi-row">
                                    <td colspan="4" class="text-center text-muted py-3 small">
                                        <i class="bi bi-info-circle me-1"></i> Belum ada satuan alternatif. Klik tombol <b>Tambah Satuan Alternatif</b> jika barang dibeli/dijual per kemasan (misal: Rol, Dus, Box).
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 edit-physical-fields"><hr class="my-1"></div>

            <div class="col-12 col-md-3 edit-warna-field">
                <label class="form-label fw-semibold">Warna</label>
                <select name="warna_id" id="edit_warna_id" class="form-select form-control-custom">
                    <option value="">-- Tanpa Warna --</option>
                    @foreach ($warnas as $warna)
                        <option value="{{ $warna->id }}">{{ $warna->nama_warna }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 edit-physical-fields">
                <label class="form-label fw-semibold">Ukuran (Size)</label>
                <input type="text" name="ukuran" id="edit_ukuran" class="form-control form-control-custom" placeholder="Contoh: S, M, L, XL, 42, All Size" autocomplete="off">
            </div>
            <div class="col-12 col-sm-4 col-md-2 edit-physical-fields">
                <label class="form-label fw-semibold">Berat (kg/gr)</label>
                <input type="number" step="0.001" name="berat" id="edit_berat" class="form-control form-control-custom text-center" placeholder="0.000" min="0">
            </div>
            <div class="col-12 col-sm-8 col-md-4 edit-physical-fields">
                <label class="form-label fw-semibold">Dimensi P &times; L &times; T (cm)</label>
                <div class="input-group flex-nowrap">
                    <input type="number" step="0.01" name="panjang" id="edit_panjang" class="form-control form-control-custom text-center" placeholder="P" min="0" title="Panjang (cm)">
                    <span class="input-group-text px-2 text-muted fw-bold">&times;</span>
                    <input type="number" step="0.01" name="lebar" id="edit_lebar" class="form-control form-control-custom text-center" placeholder="L" min="0" title="Lebar (cm)">
                    <span class="input-group-text px-2 text-muted fw-bold">&times;</span>
                    <input type="number" step="0.01" name="tinggi" id="edit_tinggi" class="form-control form-control-custom text-center" placeholder="T" min="0" title="Tinggi (cm)">
                </div>
            </div>

            <div class="col-12 edit-physical-fields"><hr class="my-1"></div>

            <div class="col-12 col-md-6 edit-stock-fields">
                <label class="form-label fw-semibold">Minimum Buffer Stok <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-body text-warning"><i class="bi bi-shield-exclamation"></i></span>
                    <input type="number" step="0.01" name="minimum_stok" id="edit_minimum_stok" class="form-control form-control-custom font-monospace fw-bold" value="0" min="0" required placeholder="0">
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">Batas peringatan restock saat stok menipis di gudang.</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Status Barang <span class="text-danger">*</span></label>
                <select name="status" id="edit_status" class="form-select form-control-custom fw-semibold" required>
                    <option value="Active">Aktif (Siap Digunakan)</option>
                    <option value="Inactive">Nonaktif</option>
                </select>
                <small class="text-muted" style="font-size: 0.72rem;">Hanya barang aktif yang dapat ditransaksikan.</small>
            </div>

            <!-- Penetapan Harga Jual Satuan (Tier: Retail, Grosir, Tender) -->
            <div class="col-12"><hr class="my-1"></div>
            <div class="col-12">
                <div class="p-3 rounded-3 border shadow-2xs" style="background: rgba(14, 165, 233, 0.02); border-color: rgba(14, 165, 233, 0.2) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-tag-fill text-success fs-5"></i>
                            <span class="fw-bold text-body" style="font-size: 0.95rem;">Harga Jual Satuan</span>
                        </div>
                        <span class="badge font-monospace px-2 py-0.5" style="background: rgba(14, 165, 233, 0.12); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.25); font-size: 0.68rem;">3 TIER HARGA</span>
                    </div>

                    <!-- HPP ACUAN GUDANG (READ-ONLY REFERENCE) -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 px-3 rounded-2 mb-2.5" style="background: rgba(14, 165, 233, 0.06); border: 1px solid rgba(14, 165, 233, 0.2);">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calculator text-info"></i>
                            <span class="small fw-semibold text-muted">HPP Modal Gudang:</span>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info font-monospace fs-6 px-2.5 py-0.5 fw-bold" id="edit_display_hpp_referensi">Rp 0</span>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-success mb-1"><i class="bi bi-cart-check me-1"></i>Retail (Eceran)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold text-success bg-success bg-opacity-10 border-success border-opacity-25 font-monospace">Rp</span>
                                <input type="text" name="harga_jual_retail" id="edit_harga_jual_retail" class="form-control font-monospace fw-bold format-rupiah" placeholder="0" onkeyup="formatRupiahInput(this)" style="font-size: 0.95rem;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-info mb-1"><i class="bi bi-boxes me-1"></i>Grosir (Partai)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold text-info bg-info bg-opacity-10 border-info border-opacity-25 font-monospace">Rp</span>
                                <input type="text" name="harga_jual_grosir" id="edit_harga_jual_grosir" class="form-control font-monospace fw-bold format-rupiah" placeholder="0" onkeyup="formatRupiahInput(this)" style="font-size: 0.95rem;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-warning mb-1"><i class="bi bi-briefcase me-1"></i>Tender (B2B / Proyek)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold text-warning bg-warning bg-opacity-10 border-warning border-opacity-25 font-monospace">Rp</span>
                                <input type="text" name="harga_jual_tender" id="edit_harga_jual_tender" class="form-control font-monospace fw-bold format-rupiah" placeholder="0" onkeyup="formatRupiahInput(this)" style="font-size: 0.95rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Spesifikasi Detail / Deskripsi</label>
                <textarea name="spesifikasi" id="edit_spesifikasi" rows="3"
                    class="form-control form-control-custom"
                    placeholder="Cantumkan spesifikasi lengkap material, dimensi, grade, dll"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold mb-1.5">Foto Barang Saat Ini</label>
                <div class="d-flex flex-wrap gap-2.5 mb-2.5 p-2.5 bg-light rounded-3" id="edit_fotos_gallery" style="min-height: 50px;">
                    <span class="text-muted small">Belum ada foto yang diupload.</span>
                </div>
                
                <label class="form-label fw-semibold mb-1.5 mt-2">Unggah Foto Baru</label>
                <div class="filepond-container mb-2">
                    <input type="file" id="edit_foto_input" class="filepond" multiple>
                </div>
                <!-- AI Remove BG Action (Dibawah FilePond) -->
                <div class="d-flex align-items-center gap-2 mt-2 pt-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs" id="btn_edit_remove_bg">
                        <i class="bi bi-magic me-1"></i>Hapus Background AI
                    </button>
                    <small class="text-muted" style="font-size: 0.72rem;">Maksimal 4 foto (JPEG/PNG/WebP)</small>
                </div>
                <div id="edit_bg_status" class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-3 mt-3" style="display:none;">
                    <div class="d-flex align-items-center justify-content-between text-primary small mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <span id="edit_bg_status_text" class="fw-semibold">Menghapus background foto...</span>
                        </div>
                        <span id="edit_bg_counter_text" class="badge bg-primary text-white">0%</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div id="edit_bg_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal_form>
