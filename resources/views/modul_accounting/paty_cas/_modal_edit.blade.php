<!-- MODAL EDIT TRANSAKSI KAS KECIL -->
<div class="modal fade" id="modalEditPettyCash" tabindex="-1" aria-labelledby="modalEditPettyCashLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form id="formEditPettyCash" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" id="edit_pc_id">

            <!-- Modal Header -->
            <div class="modal-header border-0 text-white px-3 px-md-4 py-3 flex-shrink-0" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-white bg-opacity-10 d-flex align-items-center justify-content-center">
                        <i class="bi bi-pencil-square fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalEditPettyCashLabel">Edit Transaksi Kas Kecil</h5>
                        <p class="text-white-50 small mb-0 font-monospace" id="edit_pc_nomor_display">PC-xxxx-xxxx</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="modal-body px-3 px-md-4 py-3 bg-body-tertiary" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(100vh - 170px);">

                <!-- Tipe Transaksi -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">1. Jenis Transaksi</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="edit_tipe_pengeluaran" value="Pengeluaran">
                            <label class="btn btn-outline-danger w-100 py-2 rounded-3 d-flex flex-column align-items-center gap-1" for="edit_tipe_pengeluaran">
                                <i class="bi bi-arrow-up-right-circle fs-5"></i>
                                <span class="fw-bold small">Pengeluaran</span>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="edit_tipe_pemasukan" value="Pemasukan">
                            <label class="btn btn-outline-success w-100 py-2 rounded-3 d-flex flex-column align-items-center gap-1" for="edit_tipe_pemasukan">
                                <i class="bi bi-arrow-down-left-circle fs-5"></i>
                                <span class="fw-bold small">Pengisian</span>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="edit_tipe_klaim" value="Klaim">
                            <label class="btn btn-outline-warning w-100 py-2 rounded-3 d-flex flex-column align-items-center gap-1" for="edit_tipe_klaim">
                                <i class="bi bi-receipt-cutoff fs-5"></i>
                                <span class="fw-bold small">Klaim / Bon</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nominal -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">2. Nominal (Rupiah) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text bg-light fw-bold border-end-0 text-muted">Rp</span>
                            <input type="text" name="nominal" id="edit_input_nominal" class="form-control form-control-lg fw-bold font-monospace text-primary border-start-0 fs-4" placeholder="0" inputmode="numeric" required>
                        </div>
                    </div>
                </div>

                <!-- Rincian Transaksi & Tanggal -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="edit_input_tanggal" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Pemohon / Pengaju</label>
                                <input type="text" name="nama_pengaju" id="edit_input_nama_pengaju" class="form-control rounded-3" placeholder="Nama karyawan / PIC">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Kategori Transaksi <span class="text-danger">*</span></label>
                                <input type="text" name="kategori" id="edit_input_kategori" class="form-control rounded-3" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COA & Tender -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-success text-uppercase mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> Integrasi COA &amp; Tender
                        </label>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1">Akun Kas Kecil <span class="text-danger">*</span></label>
                                <select name="coa_kas_id" id="edit_input_coa_kas_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Akun Kas --</option>
                                    @foreach($coaKasAccounts as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1">Akun Lawan (Biaya / Sumber) <span class="text-danger">*</span></label>
                                <select name="lawan_coa_id" id="edit_input_lawan_coa_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Akun Beban / Sumber --</option>
                                    @foreach($coaAccounts as $coa)
                                        <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted fw-semibold mb-1">Hubungkan ke Tender / Proyek (Opsional)</label>
                                <select name="tender_id" id="edit_input_tender_id" class="form-select rounded-3">
                                    <option value="">-- Tidak Terhubung ke Tender --</option>
                                    @foreach($tenders as $t)
                                        <option value="{{ $t->id }}">{{ $t->nomor_kontrak }} - {{ $t->nama_tender }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan & Lampiran Nota -->
                <div class="card border-0 shadow-sm rounded-3 bg-body">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Catatan / Keperluan</label>
                            <textarea name="keterangan" id="edit_input_keterangan" rows="2" class="form-control rounded-3"></textarea>
                        </div>
                        <div>
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1 d-flex align-items-center gap-1.5">
                                <i class="bi bi-camera-fill text-info"></i> Ganti Foto Struk / Nota (Opsional)
                            </label>
                            <input type="file" name="lampiran_nota" id="edit_input_lampiran_nota" class="form-control rounded-3" accept="image/*,application/pdf" capture="environment">
                            <div id="edit_existingReceiptContainer" class="mt-2 d-none">
                                <p class="small text-muted mb-1">Nota Saat Ini:</p>
                                <a id="edit_linkExistingReceipt" href="#" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1">
                                    <i class="bi bi-file-earmark-image"></i> Lihat Nota Tersimpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-0 px-3 px-md-4 py-3 bg-body flex-shrink-0">
                <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnUpdatePettyCash">
                    <i class="bi bi-check-lg fs-5"></i>
                    <span>Update Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>
