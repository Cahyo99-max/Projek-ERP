{{-- Modal Tambah Kategori Kas & Bank --}}
<div class="modal fade" id="modalTambahKasKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis m-0">Tambah Kategori Kas & Bank</h5>
                        <p class="text-secondary small m-0">Buat kategori baru untuk Kas Masuk atau Kas Keluar</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahKasKategori" autocomplete="off">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Tipe Aliran Kas <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check card-radio flex-fill p-2.5 rounded-3 text-center">
                                <input class="form-check-input d-none" type="radio" name="tipe" id="tambah_tipe_masuk" value="Masuk" checked>
                                <label class="form-check-label w-100 cursor-pointer" for="tambah_tipe_masuk">
                                    <i class="bi bi-arrow-down-left-circle text-success fs-5 d-block mb-1"></i>
                                    <span class="fw-bold d-block text-success">Kas Masuk</span>
                                    <small class="text-secondary text-xxs">Penerimaan Dana</small>
                                </label>
                            </div>
                            <div class="form-check card-radio flex-fill p-2.5 rounded-3 text-center">
                                <input class="form-check-input d-none" type="radio" name="tipe" id="tambah_tipe_keluar" value="Keluar">
                                <label class="form-check-label w-100 cursor-pointer" for="tambah_tipe_keluar">
                                    <i class="bi bi-arrow-up-right-circle text-danger fs-5 d-block mb-1"></i>
                                    <span class="fw-bold d-block text-danger">Kas Keluar</span>
                                    <small class="text-secondary text-xxs">Pengeluaran Biaya</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="tambah_kas_nama_kategori" class="form-control rounded-3" placeholder="Contoh: Pembayaran Pelanggan, Biaya Listrik, dll" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace" id="tambah_kas_nama_count">0/40</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Akun Lawan (COA Default) <span class="text-danger">*</span></label>
                        <select name="coa_id" id="tambah_kas_coa_id" class="form-select rounded-3 select2-kas-coa" required>
                            <option value="">-- Pilih Akun COA Lawan (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                            @endforeach
                        </select>
                        <small class="text-secondary d-block mt-1">Akun COA ini akan otomatis terpilih di formulir Kas Masuk/Keluar saat kategori ini dipilih.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Deskripsi / Kegunaan</label>
                        <textarea name="deskripsi" id="tambah_kas_deskripsi" class="form-control rounded-3" rows="2" placeholder="Catatan penjelasan penggunaan kategori (opsional)..."></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 60 karakter</small>
                            <small class="text-secondary font-monospace" id="tambah_kas_deskripsi_count">0/60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" id="btnSubmitTambahKasKategori">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Kategori Kas & Bank --}}
<div class="modal fade" id="modalEditKasKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis m-0">Edit Kategori Kas & Bank</h5>
                        <p class="text-secondary small m-0">Perbarui mapping kategori dan akun COA lawan</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditKasKategori" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_kas_kategori_id">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Tipe Aliran Kas <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check card-radio flex-fill p-2.5 rounded-3 text-center">
                                <input class="form-check-input d-none" type="radio" name="tipe" id="edit_tipe_masuk" value="Masuk">
                                <label class="form-check-label w-100 cursor-pointer" for="edit_tipe_masuk">
                                    <i class="bi bi-arrow-down-left-circle text-success fs-5 d-block mb-1"></i>
                                    <span class="fw-bold d-block text-success">Kas Masuk</span>
                                    <small class="text-secondary text-xxs">Penerimaan Dana</small>
                                </label>
                            </div>
                            <div class="form-check card-radio flex-fill p-2.5 rounded-3 text-center">
                                <input class="form-check-input d-none" type="radio" name="tipe" id="edit_tipe_keluar" value="Keluar">
                                <label class="form-check-label w-100 cursor-pointer" for="edit_tipe_keluar">
                                    <i class="bi bi-arrow-up-right-circle text-danger fs-5 d-block mb-1"></i>
                                    <span class="fw-bold d-block text-danger">Kas Keluar</span>
                                    <small class="text-secondary text-xxs">Pengeluaran Biaya</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="edit_kas_nama_kategori" class="form-control rounded-3" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace" id="edit_kas_nama_count">0/40</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Akun Lawan (COA Default) <span class="text-danger">*</span></label>
                        <select name="coa_id" id="edit_kas_coa_id" class="form-select rounded-3 select2-kas-coa" required>
                            <option value="">-- Pilih Akun COA Lawan (Wajib) --</option>
                            @foreach($detailCoas as $coa)
                                <option value="{{ $coa->id }}">[{{ $coa->code }}] {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-emphasis small">Deskripsi / Kegunaan</label>
                        <textarea name="deskripsi" id="edit_kas_deskripsi" class="form-control rounded-3" rows="2"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary">Maks. 60 karakter</small>
                            <small class="text-secondary font-monospace" id="edit_kas_deskripsi_count">0/60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" id="btnSubmitEditKasKategori">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card-radio {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background-color: #ffffff;
    border: 1px solid #cbd5e1 !important;
}
.card-radio:hover {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.04);
}
.card-radio:has(input:checked) {
    border-color: #2563eb !important;
    background-color: rgba(37, 99, 235, 0.08) !important;
    box-shadow: 0 0 0 1px #2563eb;
}

/* Dark Mode Card Radio */
[data-bs-theme="dark"] .card-radio,
html[data-theme="dark"] .card-radio,
.dark-mode .card-radio {
    background-color: #1e293b !important;
    border-color: #334155 !important;
}
[data-bs-theme="dark"] .card-radio:hover,
html[data-theme="dark"] .card-radio:hover,
.dark-mode .card-radio:hover {
    border-color: #38bdf8 !important;
    background-color: rgba(56, 189, 248, 0.1) !important;
}
[data-bs-theme="dark"] .card-radio:has(input:checked),
html[data-theme="dark"] .card-radio:has(input:checked),
.dark-mode .card-radio:has(input:checked) {
    border-color: #3b82f6 !important;
    background-color: rgba(37, 99, 235, 0.25) !important;
    box-shadow: 0 0 0 1px #3b82f6;
}
</style>
