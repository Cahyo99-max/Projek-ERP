<!-- Modal Edit Akun Rekening Kas & Bank -->
<div class="modal modal-blur fade" id="modalEditAccount" tabindex="-1" aria-labelledby="modalEditAccountTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditAccount" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            
            <!-- Modal Header -->
            <div class="modal-header px-3.5 px-md-4 py-3 py-md-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge shadow-sm text-dark rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff !important;">
                        <i class="bi bi-pencil-square fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalEditAccountTitle">Edit Rekening Kas &amp; Bank</h5>
                        <div class="text-muted small" style="font-size: 0.76rem;">Perbarui informasi akun rekening kas &amp; bank perusahaan.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-3.5 p-md-4 kb-modal-body">
                <input type="hidden" id="edit_acc_id" name="id">

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Nama Akun / Buku Kas <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-wallet2 text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control fw-semibold" id="edit_acc_nama_akun" name="nama_akun" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tipe Akun <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-tag text-muted"></i></span>
                            <select class="form-select kb-form-control" name="tipe" id="edit_acc_tipe_select" required>
                                <option value="Kas Tunai">Kas Tunai (Cash)</option>
                                <option value="Bank">Rekening Bank</option>
                                <option value="E-Wallet / Giro">E-Wallet / Giro</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Hubungkan ke Akun COA <span class="text-danger">*</span></label>
                        <select class="form-select select2-coa" id="edit_acc_coa_id" name="coa_id" style="width: 100%;" required>
                            <option value="">-- Pilih Akun COA (Wajib) --</option>
                            @php
                                $assetCoas = $coaAccounts->filter(function($c) {
                                    return $c->type === 'asset' || str_starts_with((string)$c->code, '1') || str_contains(strtolower($c->name), 'kas') || str_contains(strtolower($c->name), 'bank');
                                });
                                if ($assetCoas->isEmpty()) $assetCoas = $coaAccounts;
                            @endphp
                            @foreach($assetCoas as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Kode Akun</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-hash text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control font-monospace" id="edit_acc_kode_akun" name="kode_akun" autocomplete="off">
                        </div>
                    </div>

                    <!-- Fields Khusus Bank -->
                    <div class="col-12 edit-bank-fields" style="display: none;">
                        <div class="p-3 rounded-4 border bg-body-tertiary shadow-sm">
                            <div class="row g-2.5">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Nama Bank</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm" id="edit_acc_nama_bank" name="nama_bank" placeholder="BCA / Mandiri / BRI / BNI" autocomplete="off">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Nomor Rekening</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm font-monospace" id="edit_acc_nomor_rekening" name="nomor_rekening" placeholder="800-1234567" autocomplete="off">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">Atas Nama (Rekening Holder)</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm" id="edit_acc_atas_nama" name="atas_nama" placeholder="Contoh: PT. Persada Aman Sentosa" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Deskripsi / Catatan</label>
                        <textarea class="form-control kb-form-control" id="edit_acc_deskripsi" name="deskripsi" rows="2" placeholder="Keterangan peruntukan akun kas / rekening ini..." autocomplete="off"></textarea>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning text-white btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0 order-1 order-sm-2" id="btn_submit_edit_account" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none;">
                    <i class="bi bi-check2-circle fs-6 text-white"></i>
                    <span class="text-white">Perbarui Rekening</span>
                </button>
            </div>

        </form>
    </div>
</div>
