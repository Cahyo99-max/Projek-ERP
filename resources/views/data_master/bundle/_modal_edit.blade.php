<div class="modal fade" id="modalEditBundle" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1240px; width: 95vw;">
        <div class="modal-content spk-modal-content rounded-4 border border-secondary border-opacity-25 shadow-2xl overflow-hidden" style="box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.08);">
            
            {{-- MODAL HEADER (CINEMATIC GRADIENT) --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3.5" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 text-warning border border-warning border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 20px rgba(245, 158, 11, 0.35); width: 44px; height: 44px;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Edit Master Paket Bundle</h5>
                            <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 font-monospace px-2 py-0.5" style="font-size: 0.68rem; letter-spacing: 0.5px;">BUNDLE STUDIO</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.8rem;">Perbarui informasi master, komposisi komponen, foto, dan skema harga jual.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <form id="formEditBundle" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_bundle_id">

                <div class="modal-body p-4" style="max-height: 80vh; overflow-y: auto;">
                    
                    {{-- SECTION I: INFORMASI UTAMA & FOTO PAKET BUNDLE --}}
                    <div class="spk-card-section p-4 rounded-4 mb-3.5 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5);">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 border-bottom pb-2.5">
                            <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-box-seam-fill"></i> I. INFORMASI UTAMA PAKET BUNDLE
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2.5 py-1" style="font-size: 0.72rem;">Master Data</span>
                        </div>

                        {{-- Baris 1: Kode, Nama, Alias --}}
                        <div class="row g-3.5">
                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Kode Paket Bundle <span class="text-danger">*</span></label>
                                <input type="text" name="kode_barang" id="edit_bundle_kode" class="form-control form-control-sm spk-form-input font-monospace fw-bold" required autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Nama Paket Bundle <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" id="edit_bundle_nama" class="form-control form-control-sm spk-form-input fw-bold" required maxlength="150" autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Nama Alias / Komersial</label>
                                <input type="text" name="nama_alias" id="edit_bundle_alias" class="form-control form-control-sm spk-form-input" maxlength="100" autocomplete="off">
                            </div>

                            {{-- Baris 2: Kategori, Brand, Tipe, Satuan --}}
                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Kategori Barang <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="kategori_barang_id" id="edit_kategori_id" required>
                                    <input type="text" class="form-control form-control-sm spk-form-input fw-bold" id="edit_kategori_nama" placeholder="-- Pilih Kategori --" readonly onclick="openLookupKategori('edit')" style="cursor: pointer;" required>
                                    <button type="button" class="btn btn-outline-info px-2.5" onclick="openLookupKategori('edit')" title="Cari Kategori">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Brand / Merk</label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="brand_id" id="edit_brand_id">
                                    <input type="text" class="form-control form-control-sm spk-form-input" id="edit_brand_nama" placeholder="-- Tanpa Merk --" readonly onclick="openLookupBrand('edit')" style="cursor: pointer;">
                                    <button type="button" class="btn btn-outline-info px-2" onclick="openLookupBrand('edit')" title="Cari Brand">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Tipe Barang <span class="text-danger">*</span></label>
                                <select name="tipe_barang_id" id="edit_bundle_tipe" class="form-select form-select-sm spk-form-input" required>
                                    @foreach($tipes as $tipe)
                                        <option value="{{ $tipe->id }}">{{ $tipe->nama_tipe }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Satuan Dasar <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="hidden" name="satuan_id" id="edit_satuan_id" required>
                                    <input type="text" class="form-control form-control-sm spk-form-input fw-bold" id="edit_satuan_nama" placeholder="-- Pilih Satuan --" readonly onclick="openLookupSatuan('edit')" style="cursor: pointer;" required>
                                    <button type="button" class="btn btn-outline-info px-2" onclick="openLookupSatuan('edit')" title="Cari Satuan">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Baris 3: Deskripsi & Status --}}
                            <div class="col-12 col-md-8">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Spesifikasi / Deskripsi Paket</label>
                                <input type="text" name="spesifikasi" id="edit_bundle_spesifikasi" class="form-control form-control-sm spk-form-input" maxlength="500" autocomplete="off">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label spk-input-label small fw-semibold mb-1">Status Paket <span class="text-danger">*</span></label>
                                <select name="status" id="edit_bundle_status" class="form-select form-select-sm spk-form-input fw-semibold" required>
                                    <option value="Active">Aktif (Siap Jual)</option>
                                    <option value="Inactive">Nonaktif</option>
                                </select>
                            </div>

                            {{-- Foto Paket Bundle Section (Integrated Inside Section I) --}}
                            <div class="col-12 mt-3.5 pt-3 border-top border-secondary border-opacity-15">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label spk-input-label small fw-bold mb-0">
                                            <i class="bi bi-images text-warning me-1"></i> Foto Paket Bundle
                                        </label>
                                        <span class="badge bg-warning-subtle text-dark border font-monospace" style="font-size: 0.68rem;">Maks. 4 Foto Total</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold text-white shadow-sm" id="btn_edit_bundle_remove_bg" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); font-size: 0.8rem; border: none; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);">
                                            <i class="bi bi-magic me-1"></i>Hapus Background AI
                                        </button>
                                    </div>
                                </div>

                                {{-- Existing Photos Gallery --}}
                                <label class="form-label spk-input-label small fw-semibold mb-1.5 text-muted">Foto Tersimpan Saat Ini:</label>
                                <div class="d-flex flex-wrap gap-2 mb-3 p-2.5 rounded-3 border bg-body-tertiary shadow-2xs" id="edit_bundle_fotos_gallery" style="min-height: 50px;">
                                    <span class="text-muted small">Belum ada foto yang diupload.</span>
                                </div>

                                {{-- FilePond Upload --}}
                                <label class="form-label spk-input-label small fw-semibold mb-1 text-muted">Upload / Tambah Foto Baru:</label>
                                <div class="filepond-container mb-1">
                                    <input type="file" id="edit_bundle_foto_input" class="filepond" multiple>
                                </div>

                                <div id="edit_bundle_bg_status" class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-3 mt-2" style="display:none;">
                                    <div class="d-flex align-items-center justify-content-between text-primary small mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                            <span id="edit_bundle_bg_status_text" class="fw-semibold">Menghapus background foto paket baru...</span>
                                        </div>
                                        <span id="edit_bundle_bg_counter_text" class="badge bg-primary text-white font-monospace">0%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div id="edit_bundle_bg_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION II: SKEMA HARGA JUAL & REALTIME MARGIN --}}
                    <div class="spk-card-section p-4 rounded-4 mb-3.5 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5);">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 text-info fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-tag-fill"></i> II. SKEMA HARGA JUAL &amp; KALKULASI MARGIN REAL-TIME
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2" id="edit_margin_badges">
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="edit_badge_margin_retail">Margin Retail: 0%</span>
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="edit_badge_margin_grosir">Margin Grosir: 0%</span>
                                <span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1" id="edit_badge_margin_tender">Margin Tender: 0%</span>
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
                                    <div class="text-white-50" style="font-size: 11.5px;">Dihitung otomatis dari stok gudang barang penyusun tanpa disimpan statis</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-white-50 font-monospace text-uppercase" style="font-size: 0.72rem; letter-spacing: 1px;">TOTAL ESTIMASI HPP</div>
                                <span class="fs-4 fw-bold font-monospace" id="edit_display_hpp_realtime" style="color: #38bdf8; text-shadow: 0 0 12px rgba(56, 189, 248, 0.5);">Rp 0</span>
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
                                        <input type="text" name="harga_jual_retail" id="edit_harga_jual_retail" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateEditMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-primary fs-7" id="edit_profit_retail">Rp 0</span>
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
                                        <input type="text" name="harga_jual_grosir" id="edit_harga_jual_grosir" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateEditMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-success fs-7" id="edit_profit_grosir">Rp 0</span>
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
                                        <input type="text" name="harga_jual_tender" id="edit_harga_jual_tender" class="form-control form-control-sm spk-form-input font-monospace fw-bold fs-6" placeholder="0" value="0" required oninput="formatRupiahMask(this); calculateEditMargins();" autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small font-monospace">
                                        <span class="text-muted" style="font-size: 0.75rem;">Estimasi Profit:</span>
                                        <span class="fw-bold font-monospace text-warning fs-7" id="edit_profit_tender">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION III: RINCIAN BARANG PENYUSUN PAKET (DYNAMIC ROWS) --}}
                    <div class="spk-card-section p-4 rounded-4 border border-secondary border-opacity-15 shadow-sm" style="background: rgba(248, 250, 252, 0.5);">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.92rem; letter-spacing: 0.3px;">
                                <i class="bi bi-boxes"></i> III. KOMPOSISI BARANG PENYUSUN PAKET (CHILD ITEMS)
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3.5 py-1.5 font-monospace fw-semibold" id="btnEditTambahRowKomponen" style="font-size: 0.8rem;">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Barang Penyusun
                            </button>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-sm table-bordered align-middle mb-0" id="tableEditRowKomponenBundle">
                                <thead>
                                    <tr class="spk-table-header-custom small font-monospace" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white;">
                                        <th style="width: 45px;" class="text-center py-2.5">NO</th>
                                        <th class="py-2.5">BARANG JADI / PENYUSUN <span class="text-danger">*</span></th>
                                        <th style="width: 180px;" class="text-center py-2.5">QTY / PAKET <span class="text-danger">*</span></th>
                                        <th style="width: 60px;" class="text-center py-2.5">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyEditRowKomponenBundle">
                                    {{-- Row dimasukkan via JS --}}
                                </tbody>
                            </table>
                        </div>
                        <small class="spk-input-subtext mt-2.5 d-block text-muted" style="font-size: 11.5px;">
                            <i class="bi bi-info-circle me-1 text-info"></i>Klik ikon cari / kolom barang penyusun untuk memilih barang jadi beserta jumlahnya.
                        </small>
                    </div>

                </div>

                {{-- MODAL FOOTER (CINEMATIC) --}}
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3 justify-content-between" style="background: rgba(15, 23, 42, 0.03);">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 font-monospace" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-4.5 py-2 font-monospace fw-bold text-dark shadow-md" id="btnUpdateBundle" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border: none; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                        <i class="bi bi-check2-circle me-1.5 fs-6"></i> Perbarui Master Bundle
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let editKomponenIndex = 0;

    function addEditKomponenRow(selectedChildId = '', selectedKode = '', selectedNama = '', qtyVal = 1, hppVal = 0, satSingkat = 'Pcs') {
        const currentCount = $('#tbodyEditRowKomponenBundle tr').length;
        if (currentCount >= 100) {
            Swal.fire({
                title: 'Batas Maksimal!',
                text: 'Maksimal hanya dapat menambahkan hingga 100 item barang penyusun dalam 1 paket bundle.',
                icon: 'warning'
            });
            return;
        }

        editKomponenIndex++;
        
        let displayText = '';
        if (selectedKode || selectedNama) {
            displayText = `[${selectedKode}] ${selectedNama}`;
        }

        const rowId = `edit_row_komponen_${editKomponenIndex}`;
        const trHtml = `
            <tr id="${rowId}">
                <td class="text-center font-monospace text-muted edit-row-num">${$('#tbodyEditRowKomponenBundle tr').length + 1}</td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="hidden" name="komponen[${editKomponenIndex}][child_id]" class="child-id-input" value="${selectedChildId}" required>
                        <input type="hidden" class="child-hpp-val" value="${hppVal}">
                        <input type="text" class="form-control form-control-sm spk-form-input child-name-input fw-semibold" placeholder="-- Klik / Cari Barang Penyusun --" value="${displayText}" readonly onclick="openLookupKomponenBarang(this)" style="cursor: pointer;">
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
                        <input type="number" name="komponen[${editKomponenIndex}][qty]" class="form-control form-control-sm spk-form-input text-center font-monospace fw-bold edit-komponen-qty-input" value="${qtyVal}" min="0.01" step="any" required oninput="autoCalculateEditHppFromKomponen()">
                        <span class="input-group-text spk-form-addon edit-satuan-label-text">${satSingkat}</span>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" onclick="removeEditKomponenRow('${rowId}')" title="Hapus Baris">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyEditRowKomponenBundle').append(trHtml);
        reindexEditRows();
    }

    function removeEditKomponenRow(rowId) {
        if ($('#tbodyEditRowKomponenBundle tr').length <= 1) {
            Swal.fire('Peringatan', 'Minimal 1 barang penyusun paket WAJIB ada!', 'warning');
            return;
        }
        $(`#${rowId}`).remove();
        reindexEditRows();
        autoCalculateEditHppFromKomponen();
    }

    function reindexEditRows() {
        $('#tbodyEditRowKomponenBundle tr').each(function(idx) {
            $(this).find('.edit-row-num').text(idx + 1);
        });
    }

    let currentEditHpp = 0;

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

    function autoCalculateEditHppFromKomponen() {
        let totalHpp = 0;
        $('#tbodyEditRowKomponenBundle tr').each(function() {
            const hpp = parseFloat($(this).find('.child-hpp-val').val() || 0);
            const qty = parseFloat($(this).find('.edit-komponen-qty-input').val() || 0);
            totalHpp += (hpp * qty);
        });

        currentEditHpp = totalHpp;
        const formattedHpp = currentEditHpp.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        $('#edit_display_hpp_realtime').text('Rp ' + formattedHpp);
        calculateEditMargins();
    }

    function calculateEditMargins() {
        const hpp = currentEditHpp;
        const retail = parseRupiahNumber($('#edit_harga_jual_retail').val());
        const grosir = parseRupiahNumber($('#edit_harga_jual_grosir').val());
        const tender = parseRupiahNumber($('#edit_harga_jual_tender').val());

        const updateTier = function(price, profitElemId, badgeElemId, label) {
            if (price <= 0) {
                $(`#${profitElemId}`).text('Rp 0').removeClass('text-success text-warning text-danger').addClass('text-muted');
                $(`#${badgeElemId}`).attr('class', 'badge bg-secondary rounded-pill font-monospace').text(`Margin ${label}: 0%`);
                return;
            }

            const profit = price - hpp;
            const marginPct = (profit / price) * 100;
            const profitSign = profit >= 0 ? '+' : '';
            const colorClass = marginPct >= 20 ? 'text-success' : (marginPct >= 0 ? 'text-warning' : 'text-danger');
            const badgeClass = marginPct >= 20 ? 'bg-success' : (marginPct >= 0 ? 'bg-warning text-dark' : 'bg-danger');

            const formattedProfit = Math.abs(profit).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            $(`#${profitElemId}`).text(`${profitSign}Rp ${formattedProfit}`).removeClass('text-success text-warning text-danger text-muted').addClass(colorClass);
            $(`#${badgeElemId}`).attr('class', `badge ${badgeClass} rounded-pill font-monospace`).text(`Margin ${label}: ${marginPct.toFixed(2)}%`);
        };

        updateTier(retail, 'edit_profit_retail', 'edit_badge_margin_retail', 'Retail');
        updateTier(grosir, 'edit_profit_grosir', 'edit_badge_margin_grosir', 'Grosir');
        updateTier(tender, 'edit_profit_tender', 'edit_badge_margin_tender', 'Tender');
    }

    let pondEditBundle;

    $(document).ready(function() {
        if (document.querySelector('#edit_bundle_foto_input')) {
            pondEditBundle = FilePond.create(document.querySelector('#edit_bundle_foto_input'), {
                allowMultiple: true,
                maxFiles: 4,
                maxTotalFileSize: '10MB',
                acceptedFileTypes: ['image/*'],
                labelIdle: `<i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 1.75rem; display: block; margin-bottom: 2px; opacity: 0.85;"></i>Seret &amp; lepas foto baru atau <span class="filepond--label-action">Browse</span><br><span style="font-size: 0.78rem; color: #64748b;">Format PNG, JPG, WEBP &bull; Maks. 4 foto total</span>`,
                allowPaste: true,
                allowDrop: true,
                allowReorder: true,
            });
        }

        // AI Background Removal Handler for Edit Bundle
        $('#btn_edit_bundle_remove_bg').on('click', async function() {
            if (!pondEditBundle) return;
            const files = pondEditBundle.getFiles();
            if (files.length === 0) {
                Swal.fire({ title: 'Kosong!', text: 'Silakan masukkan minimal 1 foto paket baru terlebih dahulu.', icon: 'warning' });
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

            $('#btn_edit_bundle_remove_bg').prop('disabled', true);
            $('#edit_bundle_bg_status').show();
            $('#edit_bundle_bg_progress_bar').css('width', '0%');
            $('#edit_bundle_bg_counter_text').text('0%');

            const processedFiles = [];

            try {
                const total = files.length;
                for (let i = 0; i < total; i++) {
                    const currentStep = i + 1;
                    const startPercent = Math.round((i / total) * 100);
                    $('#edit_bundle_bg_progress_bar').css('width', startPercent + '%');
                    $('#edit_bundle_bg_counter_text').text(`${startPercent}%`);
                    $('#edit_bundle_bg_status_text').text(`[AI Studio] Memproses foto paket ${currentStep} dari ${total}...`);

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
                    $('#edit_bundle_bg_progress_bar').css('width', endPercent + '%');
                    $('#edit_bundle_bg_counter_text').text(`${endPercent}%`);
                }

                // Replace files in FilePond with AI cutout files
                pondEditBundle.removeFiles();
                processedFiles.forEach(file => {
                    pondEditBundle.addFile(file);
                });

                $('#edit_bundle_bg_status').hide();
                Swal.fire({
                    title: 'Berhasil!',
                    text: `${total} foto paket baru berhasil dibersihkan background-nya secara otomatis.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            } catch (err) {
                $('#edit_bundle_bg_status').hide();
                Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan saat memproses background: ' + (err.message || err), icon: 'error' });
            } finally {
                $('#btn_edit_bundle_remove_bg').prop('disabled', false);
            }
        });
    });

    function editBundleModal(id) {
        if (pondEditBundle) {
            pondEditBundle.removeFiles();
        }

        $.ajax({
            url: `/master-bundle/${id}/edit`,
            method: 'GET',
            success: function(res) {
                if (res.success && res.bundle) {
                    const b = res.bundle;
                    $('#edit_bundle_id').val(b.id);
                    $('#edit_bundle_kode').val(b.kode_barang);
                    $('#edit_bundle_nama').val(b.nama_barang);
                    $('#edit_bundle_alias').val(b.nama_alias || '');
                    
                    $('#edit_kategori_id').val(b.kategori_barang_id);
                    $('#edit_kategori_nama').val(b.kategori ? b.kategori.nama_kategori : '');

                    $('#edit_brand_id').val(b.brand_id || '');
                    $('#edit_brand_nama').val(b.brand ? b.brand.nama_brand : '-- Tanpa Merk --');

                    $('#edit_bundle_tipe').val(b.tipe_barang_id);

                    $('#edit_satuan_id').val(b.satuan_id);
                    $('#edit_satuan_nama').val(b.satuan ? `${b.satuan.nama_satuan} (${b.satuan.singkatan})` : '');

                    $('#edit_harga_jual_retail').val(parseInt(b.harga_jual_retail || 0, 10).toLocaleString('id-ID'));
                    $('#edit_harga_jual_grosir').val(parseInt(b.harga_jual_grosir || 0, 10).toLocaleString('id-ID'));
                    $('#edit_harga_jual_tender').val(parseInt(b.harga_jual_tender || 0, 10).toLocaleString('id-ID'));
                    $('#edit_bundle_spesifikasi').val(b.spesifikasi || '');
                    $('#edit_bundle_status').val(b.status);

                    // Render Existing Fotos
                    let galleryHtml = '';
                    if (b.fotos && b.fotos.length > 0) {
                        b.fotos.forEach(foto => {
                            let primaryBadge = foto.is_primary ? '<span class="primary-badge">Cover</span>' : '';
                            galleryHtml += `
                                <div class="bundle-photo-gallery-item" id="bundle-foto-item-${foto.id}">
                                    <img src="/storage/${foto.foto_path}" alt="Foto Paket">
                                    ${primaryBadge}
                                    <div class="btn-actions">
                                        <button type="button" class="btn-action-icon text-danger btn-delete-bundle-foto" data-id="${foto.id}" title="Hapus Foto">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        galleryHtml = '<span class="text-muted small p-2">Belum ada foto yang diupload untuk paket ini.</span>';
                    }
                    $('#edit_bundle_fotos_gallery').html(galleryHtml);

                    $('#tbodyEditRowKomponenBundle').empty();
                    editKomponenIndex = 0;

                    if (res.components && res.components.length > 0) {
                        res.components.forEach(comp => {
                            const satSingkat = comp.satuan || 'Pcs';
                            const hppVal = parseFloat(comp.hpp_satuan || 0);
                            addEditKomponenRow(comp.child_id, comp.kode_barang, comp.nama_barang, comp.qty_per_bundle, hppVal, satSingkat);
                        });
                    } else {
                        addEditKomponenRow();
                    }

                    autoCalculateEditHppFromKomponen();
                    $('#modalEditBundle').modal('show');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat data Master Bundle.', 'error');
            }
        });
    }

    // Delete Foto Handler
    $(document).on('click', '.btn-delete-bundle-foto', function() {
        const fotoId = $(this).data('id');
        Swal.fire({
            title: 'Hapus foto ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/barang/foto/${fotoId}/delete`,
                    type: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        $(`#bundle-foto-item-${fotoId}`).remove();
                        if ($('#edit_bundle_fotos_gallery .bundle-photo-gallery-item').length === 0) {
                            $('#edit_bundle_fotos_gallery').html('<span class="text-muted small p-2">Belum ada foto yang diupload.</span>');
                        }
                        if (typeof tableBundle !== 'undefined') tableBundle.ajax.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus foto.', 'error');
                    }
                });
            }
        });
    });

    $('#btnEditTambahRowKomponen').on('click', function() {
        addEditKomponenRow();
    });

    $('#formEditBundle').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#edit_kategori_id').val()) {
            Swal.fire('Peringatan', 'Kategori barang WAJIB dipilih!', 'warning');
            return;
        }

        if (!$('#edit_satuan_id').val()) {
            Swal.fire('Peringatan', 'Satuan dasar WAJIB dipilih!', 'warning');
            return;
        }

        let hasValidItem = false;
        $('#tbodyEditRowKomponenBundle .child-id-input').each(function() {
            if ($(this).val()) hasValidItem = true;
        });

        if (!hasValidItem) {
            Swal.fire('Peringatan', 'Silakan pilih minimal 1 barang penyusun paket!', 'warning');
            return;
        }

        const id = $('#edit_bundle_id').val();
        const $btn = $('#btnUpdateBundle');
        const originalText = $btn.html();

        // Use FormData to support new photos upload
        const formData = new FormData(this);

        // Normalize formatted price inputs to clean raw numbers
        formData.set('harga_jual_retail', parseRupiahNumber($('#edit_harga_jual_retail').val()));
        formData.set('harga_jual_grosir', parseRupiahNumber($('#edit_harga_jual_grosir').val()));
        formData.set('harga_jual_tender', parseRupiahNumber($('#edit_harga_jual_tender').val()));

        if (pondEditBundle) {
            formData.delete('fotos[]');
            const pondFiles = pondEditBundle.getFiles();
            pondFiles.forEach(item => {
                formData.append('fotos[]', item.file);
            });
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memperbarui Bundle...');

        $.ajax({
            url: `/master-bundle/${id}/update`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Master Bundle Diperbarui!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#modalEditBundle').modal('hide');
                    if (typeof tableBundle !== 'undefined') tableBundle.ajax.reload();
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html(originalText);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memperbarui Bundle',
                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                });
            }
        });
    });
</script>
@endpush
