<!-- Modal Pengeluaran Kas Keluar (Cash Out) -->
<div class="modal modal-blur fade" id="modalCashOut" tabindex="-1" aria-labelledby="modalCashOutTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formCashOut" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off">
            @csrf
            
            <!-- Modal Header -->
            <div class="modal-header px-3.5 px-md-4 py-3 py-md-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
                        <i class="bi bi-arrow-up-right fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-md-5" id="modalCashOutTitle">Pengeluaran Kas Keluar (Cash Out)</h5>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;">PENGELUARAN</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.76rem;">Catat biaya operasional, pembayaran vendor, atau pengeluaran kas perusahaan.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-3.5 p-md-4 kb-modal-body">
                <div class="row g-3">
                    
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Ambil dari Rekening / Kas <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-bank2 text-danger"></i></span>
                            <select class="form-select kb-form-control fw-semibold" name="kas_bank_id" id="cash_out_kas_bank_id" required>
                                <option value="">-- Pilih Rekening Kas/Bank --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" data-saldo="{{ (float)$acc->saldo_berjalan }}">[{{ $acc->entitas_label }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-calendar3 text-muted"></i></span>
                            <input type="date" class="form-control kb-form-control font-monospace" name="tanggal" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-tag text-muted"></i></span>
                            <select class="form-select kb-form-control" name="kategori" required>
                                <option value="Pembayaran Supplier">Pembayaran Supplier / Vendor</option>
                                <option value="Biaya Operasional & Umum">Biaya Operasional &amp; Umum</option>
                                <option value="Biaya Logistik & Ongkir">Biaya Logistik &amp; Ongkos Kirim</option>
                                <option value="Biaya Gaji & Upah">Biaya Gaji &amp; Upah Karyawan</option>
                                <option value="Biaya Listrik, Air & Internet">Biaya Listrik, Air &amp; Internet</option>
                                <option value="Biaya Pajak & Legal">Biaya Pajak &amp; Legalitas</option>
                                <option value="Pengeluaran Kas Kecil">Pengeluaran Kas Kecil (Petty Cash)</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Akun Lawan COA (Debit Beban/Hutang) <span class="text-danger">*</span></label>
                        <select class="form-select select2-coa" name="lawan_coa_id" id="cash_out_lawan_coa_id" style="width: 100%;" required>
                            <option value="">-- Pilih Akun Lawan COA (Wajib) --</option>
                            @foreach($coaAccounts as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Nominal Jumlah (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon font-monospace fw-bold text-danger">Rp</span>
                            <input type="text" inputmode="numeric" class="form-control kb-form-control font-monospace fw-bold text-danger fs-5 input-currency" name="jumlah" id="cash_out_nominal_input" placeholder="0" autocomplete="off" required>
                        </div>
                        <div class="small fw-semibold mt-1.5 text-danger" id="cash_out_saldo_warning" style="display: none;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <span id="cash_out_saldo_warning_text">Nominal melebihi saldo kas/bank yang tersedia! Saldo tidak boleh minus.</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Dibayarkan Kepada (Penerima)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control" name="nama_kontak" placeholder="Nama vendor / karyawan / pihak penerima...">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Nomor Referensi (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-receipt text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control font-monospace" name="nomor_referensi" placeholder="No. PO / No. Tagihan / Kuitansi...">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Keterangan / Uraian Pengeluaran</label>
                        <textarea class="form-control kb-form-control" name="keterangan" rows="2" placeholder="Catatan rincian keperluan pengeluaran kas..."></textarea>
                    </div>

                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0 order-1 order-sm-2" id="btn_submit_cash_out" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border: none;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Simpan Kas Keluar</span>
                </button>
            </div>

        </form>
    </div>
</div>
