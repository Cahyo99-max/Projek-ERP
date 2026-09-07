<!-- Modal Tambah / Edit Master Tipe Akun -->
<div class="modal fade" id="modalTambahMasterTipeAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTypeTitle">Tambah Master Tipe Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formMasterTipeAkun" autocomplete="off">
                @csrf
                <input type="hidden" id="type_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Tipe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="type_kode" name="kode_tipe" placeholder="Misal: AST, REV, EXP" autocomplete="off" required>
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
