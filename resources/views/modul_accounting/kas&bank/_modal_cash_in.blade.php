<!-- Modal Penerimaan Kas Masuk (Cash In) -->
<div class="modal modal-blur fade" id="modalCashIn" tabindex="-1" aria-labelledby="modalCashInTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formCashIn" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            
            <!-- Modal Header -->
            <div class="modal-header px-3.5 px-md-4 py-3 py-md-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-arrow-down-left fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalCashInTitle">Penerimaan Kas Masuk (Cash In)</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">PENERIMAAN</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.76rem;">Catat penerimaan uang tunai atau transfer masuk ke kas/bank perusahaan.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-3.5 p-md-4 kb-modal-body">
                <div class="row g-3">
                    
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Setor ke Rekening / Kas <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-bank2 text-success"></i></span>
                            <select class="form-select kb-form-control fw-semibold" name="kas_bank_id" required>
                                <option value="">-- Pilih Kas/Bank --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">[{{ $acc->entitas_label }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Penerimaan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-calendar3 text-muted"></i></span>
                            <input type="date" class="form-control kb-form-control font-monospace" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Kategori Penerimaan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-tag text-muted"></i></span>
                            <select class="form-select kb-form-control" name="kategori" required>
                                <option value="Pembayaran Pelanggan">Pembayaran Pelanggan (Customer)</option>
                                <option value="Penerimaan Penjualan Tunai">Penerimaan Penjualan Tunai</option>
                                <option value="Penerimaan Modal / Investor">Penerimaan Modal / Investor</option>
                                <option value="Pendapatan Jasa / Lainnya">Pendapatan Jasa / Lainnya</option>
                                <option value="Pengembalian Uang Muka / Piutang">Pengembalian Uang Muka / Piutang</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Akun Lawan COA (Kredit) <span class="text-danger">*</span></label>
                        <select class="form-select select2-coa" name="lawan_coa_id" id="cash_in_lawan_coa_id" style="width: 100%;" required>
                            <option value="">-- Pilih Akun Lawan COA (Wajib) --</option>
                            @foreach($coaAccounts as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Nominal Jumlah (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon font-monospace fw-bold text-success">Rp</span>
                            <input type="text" inputmode="numeric" class="form-control kb-form-control font-monospace fw-bold text-success fs-5 input-currency" name="jumlah" placeholder="0" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Diterima Dari (Nama Pembayar)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control" name="nama_kontak" placeholder="Nama pelanggan / pihak penyetor...">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Nomor Referensi (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-receipt text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control font-monospace" name="nomor_referensi" placeholder="No. Faktur / No. SO / Bukti Transfer...">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Keterangan / Uraian Transaksi</label>
                        <textarea class="form-control kb-form-control" name="keterangan" rows="2" placeholder="Catatan rincian penerimaan uang..."></textarea>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0 order-1 order-sm-2" id="btn_submit_cash_in" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Simpan Kas Masuk</span>
                </button>
            </div>

        </form>
    </div>
</div>
