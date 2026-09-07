<!-- Modal Tambah / Edit Jenis Biaya -->
<div class="modal fade" id="modalTambahJenisBiaya" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalExpTitle">Tambah Jenis Biaya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJenisBiaya" autocomplete="off">
                @csrf
                <input type="hidden" id="exp_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Jenis Biaya <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="nama_jenis_biaya" name="nama_jenis_biaya" placeholder="Misal: Ongkir / Pengiriman" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Biaya</label>
                        <input type="text" class="form-control rounded-3" id="kode_biaya" name="kode_biaya" placeholder="Misal: EXP-001" autocomplete="off">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Alokasi Biaya</label>
                            <select class="form-select rounded-3" id="alokasi_biaya" name="alokasi_biaya">
                                <option value="Berdasarkan Nilai Barang">Berdasarkan Nilai Barang</option>
                                <option value="Berdasarkan Qty">Berdasarkan Qty</option>
                                <option value="Tanpa Alokasi">Tanpa Alokasi</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Akun COA (Mapping)</label>
                            <select class="form-select rounded-3" id="exp_coa_id" name="coa_id">
                                <option value="">-- Pilih Akun COA --</option>
                                @foreach($detailCoas as $coa)
                                    <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="masuk_hpp" name="masuk_hpp" value="1">
                                <label class="form-check-label small" for="masuk_hpp">Masuk HPP (Landed Cost)</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select class="form-select rounded-3" id="exp_status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSubmitExp">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
