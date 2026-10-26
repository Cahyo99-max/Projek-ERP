<!-- Modal Tambah / Edit Master Tipe Akun -->
<div class="modal fade" id="modalTambahMasterTipeAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="modal-title fw-bold" id="modalTypeTitle">Tambah Master Tipe Akun</h5>
                    <div class="text-muted small mt-0.5">Perusahaan: <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">{{ session('active_company_name') ?? 'Perusahaan Aktif' }}</span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMasterTipeAkun" autocomplete="off">
                @csrf
                <input type="hidden" id="type_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0">Kode Tipe <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fs-8 text-primary fw-semibold" id="btnAutoCodeType" title="Generate kode otomatis">
                                <i class="bi bi-magic me-1"></i>Otomatis
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-3 font-monospace text-uppercase" id="type_kode" name="kode_tipe" placeholder="Misal: AST, REV, atau terisi otomatis" autocomplete="off" required>
                            <button class="btn btn-outline-secondary rounded-end-3" type="button" id="btnRefreshCodeType" title="Generate Kode Otomatis">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <div class="form-text fs-9 text-muted mt-1">Dapat diisi manual atau terisi otomatis dari singkatan Nama Tipe Akun.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Tipe Akun <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="type_nama" name="nama_tipe" placeholder="Misal: Aset Lancar / Kas" autocomplete="off" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kategori Laporan</label>
                            <select class="form-select rounded-3" id="type_kategori" name="kategori_laporan">
                                <option value="neraca">Neraca (Balance Sheet)</option>
                                <option value="laba_rugi">Laba Rugi (Profit & Loss)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Saldo Normal Default</label>
                            <select class="form-select rounded-3" id="type_saldo" name="saldo_normal_default">
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select class="form-select rounded-3" id="type_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <input type="text" class="form-control rounded-3" id="type_ket" name="keterangan" placeholder="Keterangan opsional" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSubmitType">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
