<!-- Modal Tambah / Edit Chart of Account (COA) -->
<div class="modal fade" id="modalTambahCoa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalCoaTitle">Tambah Akun COA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCoa" autocomplete="off">
                @csrf
                <input type="hidden" id="coa_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Akun <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="code" name="code" placeholder="Misal: 1-1310" autocomplete="off" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Maksimal 10 karakter</small>
                            <small class="text-muted"><span id="code_count">0</span>/10</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Misal: Persediaan Bahan Baku" autocomplete="off" required>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Maksimal 40 karakter</small>
                            <small class="text-muted"><span id="name_count">0</span>/40</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Master Tipe Akun</label>
                            <select class="form-select rounded-3" id="master_tipe_akun_id" name="master_tipe_akun_id">
                                <option value="">-- Pilih Tipe Akun --</option>
                                @foreach($accountTypes as $t)
                                    <option value="{{ $t->id }}">{{ $t->kode_tipe }} - {{ $t->nama_tipe }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Saldo Normal <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="normal_balance" name="normal_balance" required>
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Akun Induk (Parent)</label>
                            <select class="form-select rounded-3" id="parent_id" name="parent_id">
                                <option value="">-- Tanpa Induk --</option>
                                @foreach($parentAccounts as $pAcc)
                                    <option value="{{ $pAcc->id }}">{{ $pAcc->code }} - {{ $pAcc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">Level</label>
                            <input type="number" class="form-control rounded-3" id="level" name="level" value="1" min="1" max="5">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_header" name="is_header" value="1">
                                <label class="form-check-label small" for="is_header">Akun Induk (Header)</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label small" for="is_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" id="btnSubmitCoa">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
