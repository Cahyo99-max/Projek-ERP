{{-- Modal Tambah Kategori Kas Kecil --}}
<div class="modal fade" id="modalTambahPettyCashMapping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis m-0">Tambah Kategori Kas Kecil</h5>
                        <p class="text-secondary small m-0">Buat kategori baru & tautkan ke COA Beban default</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahPettyCashMapping" autocomplete="off">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="tambah_petty_nama_kategori" class="form-control rounded-3" placeholder="Contoh: Operasional & ATK, Konsumsi, BBM & Tol..." maxlength="40" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace" id="tambah_petty_nama_count">0/40</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Akun Beban (COA Lawan) <span class="text-danger">*</span></label>
                        <select name="coa_id" id="tambah_petty_coa_id" class="form-select rounded-3 select2-petty-coa" required>
                            <option value="">-- Pilih Akun COA Beban (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                            @endforeach
                        </select>
                        <small class="text-secondary d-block mt-1">Akun COA ini otomatis terpilih saat kategori ini di-klik pada formulir Kas Kecil.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-body-emphasis small">Warna Badge</label>
                            <select name="warna_badge" id="tambah_petty_warna_badge" class="form-select rounded-3">
                                <option value="primary">Biru (Primary)</option>
                                <option value="success">Hijau (Success)</option>
                                <option value="danger">Merah (Danger)</option>
                                <option value="warning">Kuning (Warning)</option>
                                <option value="info">Cyan (Info)</option>
                                <option value="secondary">Abu-abu (Secondary)</option>
                                <option value="dark">Gelap (Dark)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-body-emphasis small">Icon Shortcut</label>
                            <select name="icon" id="tambah_petty_icon" class="form-select rounded-3">
                                <option value="bi-tag">🏷️ Tag Default</option>
                                <option value="bi-paperclip">📁 ATK / Dokumen (bi-paperclip)</option>
                                <option value="bi-fuel-pump">⛽ BBM / Transportasi (bi-fuel-pump)</option>
                                <option value="bi-cup-hot">☕ Konsumsi / Makanan (bi-cup-hot)</option>
                                <option value="bi-box-seam">📦 Kurir / Ekspedisi (bi-box-seam)</option>
                                <option value="bi-tools">🧹 Servis / Kebersihan (bi-tools)</option>
                                <option value="bi-lightning-charge">💡 Listrik / Utilitas (bi-lightning-charge)</option>
                                <option value="bi-megaphone">📢 Iklan / Promosi (bi-megaphone)</option>
                                <option value="bi-briefcase">💼 Operasional / Dinas (bi-briefcase)</option>
                                <option value="bi-cart">🛒 Belanja Toko (bi-cart)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Deskripsi / Contoh Penggunaan</label>
                        <textarea name="deskripsi" id="tambah_petty_deskripsi" class="form-control rounded-3" rows="2" maxlength="60" placeholder="Contoh: Kertas, pulpen, materai, map, tinta printer..."></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 60 karakter</small>
                            <small class="text-secondary font-monospace" id="tambah_petty_deskripsi_count">0/60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 fw-semibold" id="btnSubmitTambahPettyCashMapping">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Kategori Kas Kecil --}}
<div class="modal fade" id="modalEditPettyCashMapping" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis m-0">Edit Kategori Kas Kecil</h5>
                        <p class="text-secondary small m-0">Perbarui mapping kategori kas kecil & akun COA lawan</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditPettyCashMapping" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_petty_mapping_id">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="edit_petty_nama_kategori" class="form-control rounded-3" maxlength="40" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace" id="edit_petty_nama_count">0/40</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Akun Beban (COA Lawan) <span class="text-danger">*</span></label>
                        <select name="coa_id" id="edit_petty_coa_id" class="form-select rounded-3 select2-petty-coa" required>
                            <option value="">-- Pilih Akun COA Beban (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-body-emphasis small">Warna Badge</label>
                            <select name="warna_badge" id="edit_petty_warna_badge" class="form-select rounded-3">
                                <option value="primary">Biru (Primary)</option>
                                <option value="success">Hijau (Success)</option>
                                <option value="danger">Merah (Danger)</option>
                                <option value="warning">Kuning (Warning)</option>
                                <option value="info">Cyan (Info)</option>
                                <option value="secondary">Abu-abu (Secondary)</option>
                                <option value="dark">Gelap (Dark)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-body-emphasis small">Icon Shortcut</label>
                            <select name="icon" id="edit_petty_icon" class="form-select rounded-3">
                                <option value="bi-tag">🏷️ Tag Default</option>
                                <option value="bi-paperclip">📁 ATK / Dokumen (bi-paperclip)</option>
                                <option value="bi-fuel-pump">⛽ BBM / Transportasi (bi-fuel-pump)</option>
                                <option value="bi-cup-hot">☕ Konsumsi / Makanan (bi-cup-hot)</option>
                                <option value="bi-box-seam">📦 Kurir / Ekspedisi (bi-box-seam)</option>
                                <option value="bi-tools">🧹 Servis / Kebersihan (bi-tools)</option>
                                <option value="bi-lightning-charge">💡 Listrik / Utilitas (bi-lightning-charge)</option>
                                <option value="bi-megaphone">📢 Iklan / Promosi (bi-megaphone)</option>
                                <option value="bi-briefcase">💼 Operasional / Dinas (bi-briefcase)</option>
                                <option value="bi-cart">🛒 Belanja Toko (bi-cart)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Deskripsi / Contoh Penggunaan</label>
                        <textarea name="deskripsi" id="edit_petty_deskripsi" class="form-control rounded-3" rows="2" maxlength="60"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 60 karakter</small>
                            <small class="text-secondary font-monospace" id="edit_petty_deskripsi_count">0/60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" id="btnSubmitEditPettyCashMapping">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
