<!-- Modal Penerimaan Kas Masuk (Cash In) -->
<div class="modal modal-blur fade" id="modalCashIn" tabindex="-1" aria-labelledby="modalCashInTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formCashIn" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container" autocomplete="off" enctype="multipart/form-data">
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
                            <select class="form-select kb-form-control fw-semibold" name="kas_bank_id" id="cash_in_kas_bank_id" required>
                                <option value="">-- Pilih Kas/Bank --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ (isset($selectedAccount) && $selectedAccount && $selectedAccount->id == $acc->id) ? 'selected' : '' }}>[{{ $acc->entitas_label }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 2, ',', '.') }})</option>
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
                        <select class="form-select select2-kategori" name="kategori" id="cash_in_kategori" style="width: 100%;" required>
                            <option value="">-- Pilih Kategori Penerimaan --</option>
                            @if(isset($kasMasukCategories) && $kasMasukCategories->isNotEmpty())
                                @foreach($kasMasukCategories as $cat)
                                    <option value="{{ $cat->nama_kategori }}" data-coa-id="{{ $cat->coa_id }}">
                                        {{ $cat->nama_kategori }}
                                    </option>
                                @endforeach
                            @else
                                <option value="Pembayaran Pelanggan">Pembayaran Pelanggan (Customer)</option>
                                <option value="Penerimaan Penjualan Tunai">Penerimaan Penjualan Tunai</option>
                                <option value="Penerimaan Modal / Investor">Penerimaan Modal / Investor</option>
                                <option value="Pendapatan Jasa / Lainnya">Pendapatan Jasa / Lainnya</option>
                                <option value="Pengembalian Uang Muka / Piutang">Pengembalian Uang Muka / Piutang</option>
                                <option value="Lain-lain">Lain-lain</option>
                            @endif
                        </select>
                        <small class="text-muted d-block mt-1 fst-italic" id="cash_in_kategori_info">
                            <i class="bi bi-info-circle me-1"></i>Pilih kategori untuk otomatisasi akun COA lawan.
                        </small>
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
                            <input type="text" inputmode="numeric" class="form-control kb-form-control font-monospace fw-bold text-success fs-5 input-currency" name="jumlah" id="cash_in_nominal_input" placeholder="0" autocomplete="off" required>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary text-xxs">Maks. 26 karakter</small>
                            <small class="text-secondary font-monospace text-xxs" id="cash_in_jumlah_count">0/26</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Diterima Dari (Nama Pembayar)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control" name="nama_kontak" id="cash_in_nama_kontak" placeholder="Nama pelanggan / pihak penyetor...">
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary text-xxs">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace text-xxs" id="cash_in_nama_kontak_count">0/40</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Nomor Referensi (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-receipt text-muted"></i></span>
                            <input type="text" class="form-control kb-form-control font-monospace" name="nomor_referensi" id="cash_in_nomor_referensi" placeholder="No. Faktur / No. SO / Bukti Transfer...">
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary text-xxs">Maks. 50 karakter</small>
                            <small class="text-secondary font-monospace text-xxs" id="cash_in_nomor_referensi_count">0/50</small>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-briefcase-fill text-warning me-1"></i> Hubungkan ke Tender (Opsional)</span>
                        </label>
                        <select class="form-select select2-tender" name="tender_id" id="cash_in_tender_id" style="width: 100%;">
                            <option value="">-- Tidak Terhubung ke Tender / Umum --</option>
                            @if(isset($tenders) && $tenders->isNotEmpty())
                                @foreach($tenders as $t)
                                    @php
                                        $ownerLabel = $t->branch ? ('Cabang ' . $t->branch->nama_cabang) : ($t->company ? $t->company->nama_perusahaan : ($company->nama_perusahaan ?? 'PT Pusat'));
                                    @endphp
                                    <option value="{{ $t->id }}">
                                        {{ $t->nomor_kontrak }} - {{ $t->nama_tender }} [{{ $ownerLabel }}]
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>-- Tidak ada tender terdaftar untuk {{ !empty($activeBranch) ? ('Cabang ' . $activeBranch->nama_cabang) : ($company->nama_perusahaan ?? 'PT Pusat') }} --</option>
                            @endif
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                            <i class="bi bi-info-circle me-1"></i>Hanya menampilkan tender milik entitas aktif ({{ !empty($activeBranch) ? ('Cabang ' . $activeBranch->nama_cabang) : ($company->nama_perusahaan ?? 'PT Pusat') }}).
                        </small>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Keterangan / Uraian Transaksi</label>
                        <textarea class="form-control kb-form-control" name="keterangan" id="cash_in_keterangan" rows="2" placeholder="Catatan rincian penerimaan uang..."></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-secondary text-xxs">Maks. 40 karakter</small>
                            <small class="text-secondary font-monospace text-xxs" id="cash_in_keterangan_count">0/40</small>
                        </div>
                    </div>

                    <!-- Bukti Transaksi / Foto Struk / Bukti Transfer (Wajib) -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-camera-fill text-success me-1"></i> Foto / Bukti Penerimaan Kas <span class="text-danger">*</span></span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 10px;">
                                <i class="bi bi-lightning-charge-fill me-0.5"></i> Auto-Kompres ke KB
                            </span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text kb-input-icon"><i class="bi bi-paperclip text-success"></i></span>
                            <input type="file" class="form-control kb-form-control" name="lampiran" id="cash_in_lampiran" accept="image/jpeg,image/png,image/webp,application/pdf" capture="environment" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="bi bi-asterisk text-danger me-1"></i>Bukti pembayaran/setoran wajib dilampirkan (JPG, PNG, PDF maks. 10MB).
                            </small>
                            <small class="text-success font-monospace" style="font-size: 11px;">
                                <i class="bi bi-file-earmark-zip me-1"></i>Kompresi Otomatis
                            </small>
                        </div>

                        <!-- Preview Foto Nota / Dokumen -->
                        <div id="cash_in_preview_container" class="mt-2 d-none">
                            <div class="p-2 rounded-3 border bg-body-tertiary d-flex align-items-center gap-2.5">
                                <div class="position-relative border rounded-2 overflow-hidden bg-body flex-shrink-0" style="width: 70px; height: 70px;">
                                    <img id="cash_in_preview_img" src="#" alt="Preview Bukti" class="w-100 h-100" style="object-fit: cover;">
                                    <div id="cash_in_preview_pdf_icon" class="w-100 h-100 d-none d-flex align-items-center justify-content-center bg-danger-subtle text-danger fs-3">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-0.5 p-0.5 rounded-circle lh-1" id="btn_remove_cash_in_preview" title="Hapus Bukti" style="width: 20px; height: 20px; font-size: 10px;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="small flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate text-body-emphasis" id="cash_in_preview_filename" style="max-width: 240px;"></div>
                                    <div class="text-muted font-monospace" id="cash_in_preview_size" style="font-size: 11px;"></div>
                                    <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 10px;">
                                        <i class="bi bi-check2-circle me-1"></i>Siap dikompres otomatis ke KB
                                    </div>
                                </div>
                            </div>
                        </div>
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
