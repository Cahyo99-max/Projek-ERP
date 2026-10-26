<!-- Modal Transfer Antar Rekening Kas & Bank -->
<div class="modal modal-blur fade" id="modalTransfer" tabindex="-1" aria-labelledby="modalTransferTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formTransfer" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            
            <!-- Modal Header -->
            <div class="modal-header px-3.5 px-md-4 py-3 py-md-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalTransferTitle">Transfer Antar Rekening</h5>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">MUTASI SALDO</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.76rem;">Pindahkan saldo antar akun kas tunai, bank operasional, atau petty cash.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-3.5 p-md-4 kb-modal-body">
                <div class="row g-3">
                    
                    <!-- Rekening Asal & Tujuan Bento Cards with Visual Connector -->
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary h-100 position-relative shadow-sm" style="border-left: 4px solid #ef4444 !important;">
                            <label class="form-label small fw-bold text-danger mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-box-arrow-up-right"></i> Rekening Asal (Sumber Dana) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-transfer-from fw-semibold" name="from_kas_bank_id" id="transfer_from_select" style="width: 100%;" required>
                                <option value="">-- Cari & Pilih Rekening Asal --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" data-saldo="{{ (float)$acc->saldo_berjalan }}">[{{ $acc->entitas_label }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 2, ',', '.') }})</option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1.5 d-flex align-items-center gap-1" id="transfer_from_saldo_info" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle text-muted"></i> Saldo tersedia akan dipotong otomatis.
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary h-100 position-relative shadow-sm" style="border-left: 4px solid #22c55e !important;">
                            <label class="form-label small fw-bold text-success mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-box-arrow-in-down-left"></i> Rekening Tujuan (Penerima) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2-transfer-to fw-semibold" name="to_kas_bank_id" id="transfer_to_select" style="width: 100%;" required>
                                <option value="">-- Cari & Pilih Rekening Tujuan --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" data-saldo="{{ (float)$acc->saldo_berjalan }}">[{{ $acc->entitas_label }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 2, ',', '.') }})</option>
                                @endforeach
                            </select>
                            <div class="small text-muted mt-1.5 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <i class="bi bi-check-circle text-success"></i> Saldo tujuan akan bertambah otomatis.
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Transfer <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-calendar3 text-muted"></i></span>
                            <input type="date" class="form-control kb-form-control font-monospace" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-body-emphasis mb-0">Nominal Jumlah Transfer (Rp) <span class="text-danger">*</span></label>
                            <span class="small text-muted font-monospace" id="transfer_nominal_counter" style="font-size: 0.72rem;">0/20 kar</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon font-monospace fw-bold text-primary">Rp</span>
                            <input type="text" inputmode="numeric" class="form-control kb-form-control font-monospace fw-bold text-primary fs-5 input-currency" name="jumlah" id="transfer_nominal_input" placeholder="0" maxlength="20" autocomplete="off" required>
                        </div>
                        <div class="small fw-semibold mt-1.5 text-danger" id="transfer_saldo_warning" style="display: none;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <span id="transfer_saldo_warning_text">Nominal melebihi saldo rekening asal! Saldo tidak boleh minus.</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-muted mb-0">Keterangan / Alasan Transfer</label>
                            <span class="small text-muted font-monospace" id="transfer_keterangan_counter" style="font-size: 0.72rem;">0/40 karakter</span>
                        </div>
                        <textarea class="form-control kb-form-control" name="keterangan" id="transfer_keterangan_input" rows="2" maxlength="40" placeholder="Contoh: Pengisian kas operasional / Setor tunai penjualan ke rekening bank..."></textarea>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0 order-1 order-sm-2" id="btn_submit_transfer" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Proses Transfer Saldo</span>
                </button>
            </div>

        </form>
    </div>
</div>
