<div class="modal fade" id="modalTambahBundle" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1240px; width: 95vw;">
        <div class="modal-content spk-modal-content rounded-4 border border-secondary border-opacity-25 shadow-2xl overflow-hidden" style="box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.08);">
            
            {{-- MODAL HEADER (CINEMATIC GRADIENT) --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3.5" style="background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); box-shadow: 0 0 20px rgba(14, 165, 233, 0.35); width: 44px; height: 44px;">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Tambah Master Paket Bundle Baru</h5>
                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 font-monospace px-2 py-0.5" style="font-size: 0.68rem; letter-spacing: 0.5px;">BUNDLE STUDIO</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.8rem;">Buat item komersial berupa paket/set dari kombinasi barang jadi dan kustomisasi skema harga.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <form id="formTambahBundle" autocomplete="off">
                @csrf
                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    
                    {{-- SECTION I: INFORMASI UTAMA & FOTO PAKET BUNDLE --}}
                    <div class="spk-card-section p-4 rounded-4 mb-4 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5); margin-bottom: 1.75rem !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 border-bottom pb-2.5">
                            <div class="d-flex align-items-center gap-2 text-info fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-box-seam-fill"></i> I. INFORMASI UTAMA PAKET BUNDLE
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2.5 py-1" style="font-size: 0.72rem;">Master Data</span>
                        </div>

                        {{-- Baris 1: Kode, Nama, Alias --}}
                        <div class="row g-3.5">
                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Kode Paket Bundle</label>
                                <input type="text" name="kode_barang" id="tambah_bundle_kode" class="form-control form-control-sm spk-form-input font-monospace fw-bold" placeholder="BDL-XXXXX (Otomatis jika kosong)" autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Nama Paket Bundle <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" id="tambah_bundle_nama" class="form-control form-control-sm spk-form-input fw-bold" placeholder="Contoh: Paket Seragam TNI Lengkap" required maxlength="150" autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Nama Alias / Komersial</label>
                                <input type="text" name="nama_alias" class="form-control form-control-sm spk-form-input" placeholder="Contoh: Paket Perlengkapan A" maxlength="100" autocomplete="off">
                            </div>

                            {{-- Baris 2: Kategori, Brand, Tipe, Satuan --}}
                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Kategori Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="kategori_barang_id" id="tambah_kategori_id" required>
                                    <input type="text" class="form-control form-control-sm spk-form-input fw-bold" id="tambah_kategori_nama" placeholder="-- Pilih Kategori --" readonly onclick="openLookupKategori('tambah')" style="cursor: pointer;" required>
                                    <button type="button" class="btn btn-outline-info px-2.5" onclick="openLookupKategori('tambah')" title="Cari Kategori">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Brand / Merk</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="brand_id" id="tambah_brand_id">
                                    <input type="text" class="form-control form-control-sm spk-form-input" id="tambah_brand_nama" placeholder="-- Tanpa Merk --" readonly onclick="openLookupBrand('tambah')" style="cursor: pointer;">
                                    <button type="button" class="btn btn-outline-info px-2" onclick="openLookupBrand('tambah')" title="Cari Brand">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Tipe Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="tipe_barang_id" id="tambah_tipe_id" required>
                                    <input type="text" class="form-control form-control-sm spk-form-input fw-bold" id="tambah_tipe_nama" placeholder="-- Pilih Tipe --" readonly onclick="openLookupTipe('tambah')" style="cursor: pointer;" required>
                                    <button type="button" class="btn btn-outline-info px-2" onclick="openLookupTipe('tambah')" title="Cari Tipe Barang">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Satuan Dasar <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="satuan_id" id="tambah_satuan_id" required>
                                    <input type="text" class="form-control form-control-sm spk-form-input fw-bold" id="tambah_satuan_nama" placeholder="-- Pilih Satuan --" readonly onclick="openLookupSatuan('tambah')" style="cursor: pointer;" required>
                                    <button type="button" class="btn btn-outline-info px-2" onclick="openLookupSatuan('tambah')" title="Cari Satuan">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Baris 3: Deskripsi & Status --}}
                            <div class="col-12 col-md-8">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Spesifikasi / Deskripsi Paket</label>
                                <input type="text" name="spesifikasi" class="form-control form-control-sm spk-form-input" placeholder="Contoh: Paket lengkap 5 item perlengkapan lapangan" maxlength="500" autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Status Paket <span class="text-danger">*</span></label>
                                <select name="status" class="form-select form-select-sm spk-form-input fw-semibold" required>
                                    <option value="Active" selected>Aktif (Siap Dijual)</option>
                                    <option value="Inactive">Nonaktif</option>
                                </select>
                            </div>

                            {{-- Foto Paket Bundle Section (Integrated Inside Section I) --}}
                            <div class="col-12 mt-3.5 pt-3 border-top border-secondary border-opacity-15">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label spk-input-label small fw-bold mb-0">
                                            <i class="bi bi-images text-info me-1"></i> Foto Paket Bundle
                                        </label>
                                        <span class="badge rounded-pill px-2.5 py-1 font-monospace fw-bold shadow-xs" style="font-size: 0.78rem; background-color: #0369a1 !important; color: #ffffff !important; border: 1px solid #38bdf8 !important;"><i class="bi bi-info-circle-fill me-1" style="color: #38bdf8 !important;"></i>Maksimal 4 Foto Total &bull; Urutan #1 sebagai Cover</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold text-white shadow-sm" id="btn_tambah_bundle_remove_bg" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); font-size: 0.8rem; border: none; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);">
                                            <i class="bi bi-magic me-1"></i>Hapus Background AI
                                        </button>
                                    </div>
                                </div>

                                <div class="filepond-container mb-1">
                                    <input type="file" id="tambah_bundle_foto_input" class="filepond" multiple>
                                </div>

                                <div id="tambah_bundle_bg_status" class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-3 mt-2" style="display:none;">
                                    <div class="d-flex align-items-center justify-content-between text-primary small mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                            <span id="tambah_bundle_bg_status_text" class="fw-semibold">Menghapus background foto paket...</span>
                                        </div>
                                        <span id="tambah_bundle_bg_counter_text" class="badge bg-primary text-white font-monospace">0%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div id="tambah_bundle_bg_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION II: RINCIAN BARANG PENYUSUN PAKET (DYNAMIC ROWS) --}}
                    <div class="spk-card-section p-4 rounded-4 mb-4 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5); margin-bottom: 1.75rem !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-boxes"></i> II. KOMPOSISI BARANG PENYUSUN PAKET (CHILD ITEMS)
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3.5 py-1.5 font-monospace fw-semibold" id="btnTambahRowKomponen" style="font-size: 0.8rem;">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Barang Penyusun
                            </button>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-sm table-bordered align-middle mb-0" id="tableRowKomponenBundle">
                                <thead>
                                    <tr class="spk-table-header-custom small font-monospace" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white;">
                                        <th style="width: 45px;" class="text-center py-2.5">NO</th>
                                        <th class="py-2.5">BARANG JADI / PENYUSUN <span class="text-danger">*</span></th>
                                        <th style="width: 180px;" class="text-center py-2.5">QTY / PAKET <span class="text-danger">*</span></th>
                                        <th style="width: 60px;" class="text-center py-2.5">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyRowKomponenBundle">
                                    {{-- Row dimasukkan via JS --}}
                                </tbody>
                            </table>
                        </div>
                        <small class="spk-input-subtext mt-2.5 d-block text-muted" style="font-size: 11.5px;">
                            <i class="bi bi-info-circle me-1 text-info"></i>Klik ikon cari / kolom barang penyusun untuk memilih item komponen (misal: Seragam, Celana, Rompi, Helm) beserta jumlahnya.
                        </small>
                    </div>

                    {{-- SECTION III: SKEMA HARGA JUAL & REALTIME MARGIN --}}
                    <div class="spk-card-section p-4 rounded-4 mb-2 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5);">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 text-info fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-tag-fill"></i> III. SKEMA HARGA JUAL &amp; KALKULASI MARGIN REAL-TIME
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2" id="tambah_margin_badges">
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="tambah_badge_margin_retail">Margin Retail: 0%</span>
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="tambah_badge_margin_grosir">Margin Grosir: 0%</span>
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="tambah_badge_margin_tender">Margin Tender: 0%</span>
                            </div>
                        </div>

                        {{-- CINEMATIC REALTIME HPP HUD BANNER --}}
                        <div class="p-3.5 rounded-3 mb-3.5 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-md" style="background: linear-gradient(135deg, #091e3a 0%, #102a45 50%, #081b2f 100%); border: 1px solid rgba(56, 189, 248, 0.25); box-shadow: inset 0 0 20px rgba(14, 165, 233, 0.08), 0 8px 20px -4px rgba(0, 0, 0, 0.25);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2.5 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.2); box-shadow: 0 0 16px rgba(56, 189, 248, 0.35); width: 44px; height: 44px;">
                                    <i class="bi bi-cpu-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                        <span class="badge bg-info text-dark font-monospace fw-bold px-2 py-0.5" style="font-size: 0.68rem; letter-spacing: 0.5px;">REAL-TIME ENGINE</span>
                                        <span class="text-white fw-semibold small">Akumulasi HPP Gudang Komponen</span>
                                    </div>
                                    <div class="text-white-50" style="font-size: 11.5px;">Kalkulasi HPP real-time dihitung secara otomatis berdasarkan stok gudang komponen penyusun</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-white-50 font-monospace text-uppercase" style="font-size: 0.72rem; letter-spacing: 1px;">TOTAL ESTIMASI HPP</div>
                                <span class="fs-4 fw-bold font-monospace" id="tambah_display_hpp_realtime" style="color: #38bdf8; text-shadow: 0 0 12px rgba(56, 189, 248, 0.5);">Rp 0</span>
                            </div>
                        </div>

                        {{-- 3 TIER PRICING INPUTS --}}
                        <div class="row g-3.5">
                            {{-- 1. Harga Jual Retail --}}
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 border bg-body-tertiary shadow-2xs position-relative overflow-hidden" style="border-top: 3px solid #3b82f6 !important;">
                                    <label class="form-label spk-input-label small fw-bold mb-1.5 text-primary d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-person-fill me-1"></i> Harga Retail / Satuan <span class="text-danger">*</span></span>
                                        <span class="badge bg-primary-subtle text-primary font-monospace" style="font-size: 0.65rem;">TIER 1</span>
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text font-monospace bg-primary bg-opacity-10 text-primary border-primary border-opacity-25 fw-bold">Rp</span>
                                        <input type="text" name="harga_jual_retail" id="tambah_harga_jual_retail" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateTambahMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-primary fs-7" id="tambah_profit_retail">Rp 0</span>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Harga Jual Grosir --}}
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 border bg-body-tertiary shadow-2xs position-relative overflow-hidden" style="border-top: 3px solid #10b981 !important;">
                                    <label class="form-label spk-input-label small fw-bold mb-1.5 text-success d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-people-fill me-1"></i> Harga Grosir / Agen <span class="text-danger">*</span></span>
                                        <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.65rem;">TIER 2</span>
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text font-monospace bg-success bg-opacity-10 text-success border-success border-opacity-25 fw-bold">Rp</span>
                                        <input type="text" name="harga_jual_grosir" id="tambah_harga_jual_grosir" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateTambahMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-success fs-7" id="tambah_profit_grosir">Rp 0</span>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Harga Jual Tender --}}
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-3 border bg-body-tertiary shadow-2xs position-relative overflow-hidden" style="border-top: 3px solid #f59e0b !important;">
                                    <label class="form-label spk-input-label small fw-bold mb-1.5 text-warning d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-briefcase-fill me-1"></i> Harga Tender / Proyek <span class="text-danger">*</span></span>
                                        <span class="badge bg-warning-subtle text-warning font-monospace" style="font-size: 0.65rem;">TIER 3</span>
                                    </label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text font-monospace bg-warning bg-opacity-10 text-warning border-warning border-opacity-25 fw-bold">Rp</span>
                                        <input type="text" name="harga_jual_tender" id="tambah_harga_jual_tender" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateTambahMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-warning fs-7" id="tambah_profit_tender">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- MODAL FOOTER (CINEMATIC) --}}
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3 justify-content-between" style="background: rgba(15, 23, 42, 0.03);">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 font-monospace" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4.5 py-2 font-monospace fw-bold shadow-md" id="btnSimpanBundle" style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%); border: none; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);">
                        <i class="bi bi-check2-circle me-1.5 fs-6"></i> Simpan Master Bundle
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let komponenIndex = 0;

    function addKomponenRow(selectedChildId = '', selectedChildName = '', qtyVal = 1, hppVal = 0, satSingkat = 'Pcs') {
        const currentCount = $('#tbodyRowKomponenBundle tr').length;
        if (currentCount >= 100) {
            Swal.fire({
                title: 'Batas Maksimal!',
                text: 'Maksimal hanya dapat menambahkan hingga 100 item barang penyusun dalam 1 paket bundle.',
                icon: 'warning'
            });
            return;
        }

        komponenIndex++;
        
        const rowId = `row_komponen_${komponenIndex}`;

        const trHtml = `
            <tr id="${rowId}">
                <td class="text-center font-monospace text-muted row-num">${$('#tbodyRowKomponenBundle tr').length + 1}</td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="hidden" name="komponen[${komponenIndex}][child_id]" class="child-id-input" value="${selectedChildId}" required>
                        <input type="hidden" class="child-hpp-val" value="${hppVal}">
                        <input type="text" class="form-control form-control-sm spk-form-input child-name-input fw-semibold" placeholder="-- Klik / Cari Barang Penyusun --" value="${selectedChildName}" readonly onclick="openLookupKomponenBarang(this)" style="cursor: pointer;">
                        <button type="button" class="btn btn-outline-info px-2.5 btn-lookup-komponen" onclick="openLookupKomponenBarang(this)" title="Cari Barang Penyusun">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="d-flex gap-2 mt-1 komponen-hpp-info" style="font-size: 0.73rem;">
                        <span class="text-muted"><i class="bi bi-building me-1"></i>HPP Gudang:</span>
                        <span class="fw-bold child-hpp-display ${hppVal > 0 ? 'text-success' : 'text-secondary'}">${hppVal > 0 ? 'Rp ' + Number(hppVal).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : '-'}</span>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="number" name="komponen[${komponenIndex}][qty]" class="form-control form-control-sm spk-form-input text-center font-monospace fw-bold komponen-qty-input" value="${qtyVal}" min="0.01" step="any" required oninput="autoCalculateHppFromKomponen()">
                        <span class="input-group-text spk-form-addon satuan-label-text">${satSingkat}</span>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeKomponenRow('${rowId}')" title="Hapus Baris">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyRowKomponenBundle').append(trHtml);
        reindexRows();
        autoCalculateHppFromKomponen();
    }

    function removeKomponenRow(rowId) {
        if ($('#tbodyRowKomponenBundle tr').length <= 1) {
            Swal.fire('Peringatan', 'Minimal 1 barang penyusun paket WAJIB ada!', 'warning');
            return;
        }
        $(`#${rowId}`).remove();
        reindexRows();
        autoCalculateHppFromKomponen();
    }

    function reindexRows() {
        $('#tbodyRowKomponenBundle tr').each(function(idx) {
            $(this).find('.row-num').text(idx + 1);
        });
    }

    let currentTambahHpp = 0;

    function formatRupiahMask(el) {
        let val = el.value.replace(/[^0-9]/g, '');
        if (val === '') {
            el.value = '';
        } else {
            el.value = parseInt(val, 10).toLocaleString('id-ID');
        }
    }

    function parseRupiahNumber(val) {
        if (!val) return 0;
        return parseFloat(val.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    function autoCalculateHppFromKomponen() {
        let totalHpp = 0;
        $('#tbodyRowKomponenBundle tr').each(function() {
            const hpp = parseFloat($(this).find('.child-hpp-val').val() || 0);
            const qty = parseFloat($(this).find('.komponen-qty-input').val() || 0);
            totalHpp += (hpp * qty);
        });

        currentTambahHpp = totalHpp;
        const formattedHpp = currentTambahHpp.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        $('#tambah_display_hpp_realtime, #tambah_display_hpp').text('Rp ' + formattedHpp);
        calculateTambahMargins();
    }

    function calculateTambahMargins() {
        const hpp = currentTambahHpp;
        const retail = parseRupiahNumber($('#tambah_harga_jual_retail').val());
        const grosir = parseRupiahNumber($('#tambah_harga_jual_grosir').val());
        const tender = parseRupiahNumber($('#tambah_harga_jual_tender').val());

        const updateTier = function(price, profitElemId, badgeElemId, label) {
            if (price <= 0) {
                $(`#${profitElemId}`).text('Rp 0').removeClass('text-success text-warning text-danger').addClass('text-muted');
                $(`#${badgeElemId}`).attr('class', 'badge bg-secondary rounded-pill font-monospace').text(`Margin ${label}: 0%`);
                return;
            }

            const profit = price - hpp;
            const marginPct = (profit / price) * 100;
            const isRugi = profit < 0;
            const profitSign = profit >= 0 ? '+' : '-';
            const colorClass = marginPct >= 20 ? 'text-success' : (marginPct >= 0 ? 'text-warning' : 'text-danger');
            const badgeClass = marginPct >= 20 ? 'bg-success' : (marginPct >= 0 ? 'bg-warning text-dark' : 'bg-danger font-monospace px-2.5 py-1 text-white fw-bold');

            const formattedProfit = Math.abs(profit).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            const rugiText = isRugi ? ' (RUGI)' : '';
            $(`#${profitElemId}`).text(`${profitSign}Rp ${formattedProfit}${rugiText}`).removeClass('text-success text-warning text-danger text-muted').addClass(colorClass);
            $(`#${badgeElemId}`).attr('class', `badge ${badgeClass} rounded-pill font-monospace`).text(`Margin ${label}: ${marginPct.toFixed(2)}%${rugiText}`);
        };

        updateTier(retail, 'tambah_profit_retail', 'tambah_badge_margin_retail', 'Retail');
        updateTier(grosir, 'tambah_profit_grosir', 'tambah_badge_margin_grosir', 'Grosir');
        updateTier(tender, 'tambah_profit_tender', 'tambah_badge_margin_tender', 'Tender');
    }

    let pondTambahBundle;

    $(document).ready(function() {
        if (document.querySelector('#tambah_bundle_foto_input')) {
            pondTambahBundle = FilePond.create(document.querySelector('#tambah_bundle_foto_input'), {
                allowMultiple: true,
                maxFiles: 4,
                maxTotalFileSize: '10MB',
                acceptedFileTypes: ['image/*'],
                labelIdle: `<i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 1.75rem; display: block; margin-bottom: 2px; opacity: 0.85;"></i>Seret &amp; lepas foto paket atau <span class="filepond--label-action">Browse</span><br><span style="font-size: 0.82rem; font-weight: 600; color: #475569;">Format PNG, JPG, WEBP &bull; Maksimal 4 Foto Total</span>`,
                allowPaste: true,
                allowDrop: true,
                allowReorder: true,
            });
        }

        // AI Background Removal Handler for Tambah Bundle
        $('#btn_tambah_bundle_remove_bg').on('click', async function() {
            if (!pondTambahBundle) return;
            const files = pondTambahBundle.getFiles();
            if (files.length === 0) {
                Swal.fire({ title: 'Kosong!', text: 'Silakan masukkan minimal 1 foto paket terlebih dahulu.', icon: 'warning' });
                return;
            }

            if (!window._imglyReady || !window._imglyRemoveBg) {
                if (window._imglyError) {
                    Swal.fire({ title: 'Error AI', text: window._imglyErrorMessage || window._imglyError, icon: 'error' });
                } else {
                    Swal.fire({ title: 'Menyiapkan AI', text: 'AI Hapus Background sedang dimuat (~30MB), harap tunggu beberapa detik lalu coba lagi.', icon: 'info' });
                }
                return;
            }

            $('#btn_tambah_bundle_remove_bg').prop('disabled', true);
            $('#tambah_bundle_bg_status').show();
            $('#tambah_bundle_bg_progress_bar').css('width', '0%');
            $('#tambah_bundle_bg_counter_text').text('0%');

            const processedFiles = [];

            try {
                const total = files.length;
                for (let i = 0; i < total; i++) {
                    const currentStep = i + 1;
                    const startPercent = Math.round((i / total) * 100);
                    $('#tambah_bundle_bg_progress_bar').css('width', startPercent + '%');
                    $('#tambah_bundle_bg_counter_text').text(`${startPercent}%`);
                    $('#tambah_bundle_bg_status_text').text(`[AI Studio] Memproses foto paket ${currentStep} dari ${total}...`);

                    const originalBlob = files[i].file;
                    const optimizedBlob = await resizeImage(originalBlob, 1024);

                    const rawResultBlob = await window._imglyRemoveBg(optimizedBlob, {
                        publicPath: 'https://staticimgly.com/@imgly/background-removal-data/1.4.5/dist/',
                        model: 'medium',
                        debug: false,
                        output: { format: 'image/png', quality: 1.0 }
                    });

                    const safeName = (originalBlob.name || 'foto_bundle').replace(/\.[^.]+$/, '') + '_nobg.png';
                    const newFile = new File([rawResultBlob], safeName, { type: 'image/png' });
                    processedFiles.push(newFile);

                    const endPercent = Math.round((currentStep / total) * 100);
                    $('#tambah_bundle_bg_progress_bar').css('width', endPercent + '%');
                    $('#tambah_bundle_bg_counter_text').text(`${endPercent}%`);
                }

                // Replace files in FilePond with AI cutout files
                pondTambahBundle.removeFiles();
                processedFiles.forEach(file => {
                    pondTambahBundle.addFile(file);
                });

                $('#tambah_bundle_bg_status').hide();
                Swal.fire({
                    title: 'Berhasil!',
                    text: `${total} foto paket berhasil dibersihkan background-nya secara otomatis.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (err) {
                $('#tambah_bundle_bg_status').hide();
                Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan saat memproses background: ' + (err.message || err), icon: 'error' });
            } finally {
                $('#btn_tambah_bundle_remove_bg').prop('disabled', false);
            }
        });
    });

    $('#modalTambahBundle').on('show.bs.modal', function () {
        $('#formTambahBundle')[0].reset();
        if (pondTambahBundle) {
            pondTambahBundle.removeFiles();
        }
        $('#tambah_kategori_id').val('');
        $('#tambah_kategori_nama').val('');
        $('#tambah_brand_id').val('');
        $('#tambah_brand_nama').val('');
        $('#tambah_tipe_id').val('');
        $('#tambah_tipe_nama').val('');
        $('#tambah_bundle_tipe').val('');
        $('#tambah_satuan_id').val('');
        $('#tambah_satuan_nama').val('');

        $('#tbodyRowKomponenBundle').empty();
        komponenIndex = 0;
        
        // Add 2 initial empty rows
        addKomponenRow();
        addKomponenRow();
    });

    $('#btnTambahRowKomponen').on('click', function() {
        addKomponenRow();
    });

    $('#formTambahBundle').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#tambah_kategori_id').val()) {
            Swal.fire('Peringatan', 'Kategori barang WAJIB dipilih!', 'warning');
            return;
        }

        if (!$('#tambah_tipe_id').val() && !$('#tambah_bundle_tipe').val()) {
            Swal.fire('Peringatan', 'Tipe barang WAJIB dipilih!', 'warning');
            return;
        }

        if (!$('#tambah_satuan_id').val()) {
            Swal.fire('Peringatan', 'Satuan dasar WAJIB dipilih!', 'warning');
            return;
        }

        let hasValidItem = false;
        $('.child-id-input').each(function() {
            if ($(this).val()) hasValidItem = true;
        });

        if (!hasValidItem) {
            Swal.fire('Peringatan', 'Silakan pilih minimal 1 barang penyusun paket!', 'warning');
            return;
        }

        const hppVal = currentTambahHpp;
        const retailVal = parseRupiahNumber($('#tambah_harga_jual_retail').val());
        const grosirVal = parseRupiahNumber($('#tambah_harga_jual_grosir').val());
        const tenderVal = parseRupiahNumber($('#tambah_harga_jual_tender').val());

        const rugiList = [];
        if (retailVal > 0 && retailVal < hppVal) rugiList.push(`Harga Retail (Rugi Rp ${(hppVal - retailVal).toLocaleString('id-ID')})`);
        if (grosirVal > 0 && grosirVal < hppVal) rugiList.push(`Harga Grosir (Rugi Rp ${(hppVal - grosirVal).toLocaleString('id-ID')})`);
        if (tenderVal > 0 && tenderVal < hppVal) rugiList.push(`Harga Tender (Rugi Rp ${(hppVal - tenderVal).toLocaleString('id-ID')})`);

        if (rugiList.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Simpan: Terdeteksi Margin Rugi!',
                html: `<div class="text-start small">Total HPP Bundle: <b>Rp ${hppVal.toLocaleString('id-ID')}</b><br>Skema berikut lebih murah dari HPP:<br><ul class="mt-2 mb-0"><li>${rugiList.join('</li><li>')}</li></ul><br>Silakan naikkan harga jual sebelum menyimpan!</div>`
            });
            return;
        }

        const $btn = $('#btnSimpanBundle');
        const originalText = $btn.html();
        
        // Use FormData to support multi-photos upload
        const formData = new FormData(this);

        // Normalize formatted price inputs to clean raw numbers
        formData.set('harga_jual_retail', parseRupiahNumber($('#tambah_harga_jual_retail').val()));
        formData.set('harga_jual_grosir', parseRupiahNumber($('#tambah_harga_jual_grosir').val()));
        formData.set('harga_jual_tender', parseRupiahNumber($('#tambah_harga_jual_tender').val()));

        // Append FilePond files if any
        if (pondTambahBundle) {
            formData.delete('fotos[]');
            const pondFiles = pondTambahBundle.getFiles();
            pondFiles.forEach(item => {
                formData.append('fotos[]', item.file);
            });
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Paket...');

        $.ajax({
            url: "{{ route('master-bundle.store') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Master Bundle Berhasil Dibuat!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#modalTambahBundle').modal('hide');
                    if (typeof tableBundle !== 'undefined') tableBundle.ajax.reload();
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Master Bundle',
                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                });
            }
        });
    });
</script>
@endpush
