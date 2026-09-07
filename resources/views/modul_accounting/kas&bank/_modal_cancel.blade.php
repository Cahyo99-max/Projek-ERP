<!-- Modal Konfirmasi Batalkan Transaksi Kas & Bank -->
<div class="modal modal-blur fade" id="modalCancelMutasi" tabindex="-1" aria-labelledby="modalCancelMutasiTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <form id="formCancelMutasi" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            <input type="hidden" id="cancel_mutasi_id" name="id">

            <!-- Modal Body -->
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 60px; height: 60px; background: rgba(220, 38, 38, 0.12); color: #dc2626;">
                    <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                </div>
                
                <h5 class="fw-bold text-body-emphasis mb-1 fs-5" id="modalCancelMutasiTitle">Batalkan Transaksi?</h5>
                <p class="text-muted small mb-3">
                    Transaksi <strong class="font-monospace text-danger" id="cancel_mutasi_nomor">-</strong> akan dibatalkan. Saldo rekening dan jurnal akuntansi terkait akan otomatis dikembalikan ke posisi semula.
                </p>

                <!-- Input Alasan Pembatalan (Maks. 40 Karakter) -->
                <div class="text-start bg-body-tertiary p-3 rounded-3 border mb-1">
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <label for="cancel_alasan_input" class="form-label small fw-bold text-body-emphasis mb-0">
                            Alasan / Catatan Pembatalan <span class="text-danger">*</span>
                        </label>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;" id="cancel_char_counter">0/40</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text kb-input-icon"><i class="bi bi-chat-left-dots text-muted"></i></span>
                        <input type="text" class="form-control kb-form-control fw-semibold" id="cancel_alasan_input" name="alasan_pembatalan" maxlength="40" placeholder="Contoh: Salah input nominal / Nota revisi..." required autocomplete="off">
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        <i class="bi bi-info-circle me-1"></i> Wajib diisi untuk audit log pembukuan (maks. 40 karakter).
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-3 bg-body-tertiary d-flex justify-content-between align-items-center border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn_submit_cancel">
                    <i class="bi bi-x-circle fs-6"></i>
                    <span>Ya, Batalkan Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>
