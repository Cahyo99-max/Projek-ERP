<!-- Modal Tambah Akun Rekening Kas & Bank Baru -->
<div class="modal modal-blur fade" id="modalAddAccount" tabindex="-1" aria-labelledby="modalAddAccountTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formAddAccount" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            
            <!-- Modal Header -->
            <div class="modal-header px-3.5 px-md-4 py-3 py-md-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-bank2 fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalAddAccountTitle">Tambah Rekening Kas &amp; Bank</h5>
                        <div class="text-muted small" style="font-size: 0.76rem;">Daftarkan buku kas tunai atau rekening bank perusahaan.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-3.5 p-md-4 kb-modal-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Nama Akun / Buku Kas <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-wallet2 text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control fw-semibold" name="nama_akun" placeholder="Contoh: Kas Kecil Operasional / Bank BCA Operasional" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tipe Akun <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-tag text-muted"></i></span>
                            <select class="form-select kb-form-control" name="tipe" id="acc_tipe_select" required>
                                <option value="Kas Tunai">Kas Tunai (Cash)</option>
                                <option value="Bank">Rekening Bank</option>
                                <option value="E-Wallet / Giro">E-Wallet / Giro</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Hubungkan ke Akun COA <span class="text-danger">*</span></label>
                        <select class="form-select select2-coa" name="coa_id" id="add_acc_coa_id" style="width: 100%;" required>
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

                    <!-- Fields Khusus Bank (Muncul jika tipe = Bank) -->
                    <div class="col-12 bank-fields" style="display: none;">
                        <div class="p-3 rounded-4 border bg-body-tertiary shadow-sm">
                            <div class="row g-2.5">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Nama Bank</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm" name="nama_bank" placeholder="BCA / Mandiri / BRI / BNI" autocomplete="off">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Nomor Rekening</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm font-monospace" name="nomor_rekening" placeholder="Contoh: 800-1234567" autocomplete="off">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">Atas Nama (Rekening Holder)</label>
                                    <input type="text" class="form-control kb-form-control form-control-sm" name="atas_nama" placeholder="Contoh: PT. Persada Aman Sentosa" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Saldo Awal (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon font-monospace fw-bold text-success">Rp</span>
                            <input type="text" inputmode="numeric" class="form-control kb-form-control font-monospace fw-bold text-success fs-5 input-currency" name="saldo_awal" id="add_acc_saldo_awal" value="0" placeholder="0" autocomplete="off">
                        </div>
                        <div class="form-text small text-muted">Isi saldo awal jika buku kas/rekening ini sudah memiliki dana saat didaftarkan.</div>
                    </div>

                    <!-- Akun Lawan Saldo Awal (Muncul jika Saldo Awal > 0) -->
                    <div class="col-12" id="box_lawan_saldo_awal" style="display: none;">
                        <div class="p-3 rounded-4 border border-warning-subtle bg-warning-subtle bg-opacity-25 shadow-sm">
                            <label class="form-label small fw-bold text-body-emphasis mb-1">
                                Akun Lawan Saldo Awal (Kredit) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-coa" name="lawan_coa_id" id="add_acc_lawan_coa_id" style="width: 100%;">
                                <option value="">-- Pilih Akun Lawan (Default: Modal / Ekuitas) --</option>
                                @php
                                    $equityCoas = $coaAccounts->filter(function($c) {
                                        return $c->type === 'equity' || str_starts_with((string)$c->code, '3') || str_contains(strtolower($c->name), 'modal') || str_contains(strtolower($c->name), 'ekuitas');
                                    });
                                    $otherCoas = $coaAccounts->reject(function($c) use ($equityCoas) {
                                        return $equityCoas->contains('id', $c->id);
                                    });
                                @endphp
                                @if($equityCoas->isNotEmpty())
                                    <optgroup label="Akun Modal &amp; Ekuitas (Direkomendasikan)">
                                        @foreach($equityCoas as $coa)
                                            <option value="{{ $coa->id }}" {{ (str_contains(strtolower($coa->name), 'modal') || str_contains(strtolower($coa->name), 'ekuitas')) ? 'selected' : '' }}>
                                                {{ $coa->code }} - {{ $coa->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if($otherCoas->isNotEmpty())
                                    <optgroup label="Akun Lainnya (Hutang / Liabilitas / RAK)">
                                        @foreach($otherCoas as $coa)
                                            <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <div class="form-text small text-muted mt-1">
                                <i class="bi bi-info-circle text-warning me-1"></i>
                                Akun penyeimbang jurnal saldo awal (Debit: Kas/Bank, Kredit: Akun Lawan).
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Deskripsi / Catatan</label>
                        <textarea class="form-control kb-form-control" name="deskripsi" rows="2" placeholder="Keterangan peruntukan akun kas / rekening ini..." autocomplete="off"></textarea>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0 order-1 order-sm-2" id="btn_submit_account" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Simpan Rekening</span>
                </button>
            </div>

        </form>
    </div>
</div>
