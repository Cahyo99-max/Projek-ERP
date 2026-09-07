<!-- MODAL INPUT TRANSAKSI KAS KECIL (MOBILE-OPTIMIZED) -->
<div class="modal fade" id="modalInputPettyCash" tabindex="-1" aria-labelledby="modalInputPettyCashLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form id="formInputPettyCash" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" enctype="multipart/form-data">
            @csrf
            
            <!-- Modal Header with Gradient -->
            <div class="modal-header border-0 text-white px-3 px-md-4 py-3 flex-shrink-0" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-white bg-opacity-10 d-flex align-items-center justify-content-center">
                        <i class="bi bi-wallet2 fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalInputPettyCashLabel">Input Kas Kecil (Petty Cash)</h5>
                        <p class="text-white-50 small mb-0 font-monospace">{{ $activeEntityName }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="modal-body px-3 px-md-4 py-3 bg-body-tertiary" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(100vh - 170px);">

                <!-- STEP 1: Pilih Tipe Transaksi (Pills Touch-Friendly) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-2">1. Jenis Transaksi</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="tipe_pengeluaran" value="Pengeluaran" checked>
                            <label class="btn btn-outline-danger w-100 py-2.5 px-2 rounded-3 d-flex flex-column align-items-center gap-1 pc-type-btn" for="tipe_pengeluaran">
                                <i class="bi bi-arrow-up-right-circle fs-5"></i>
                                <span class="fw-bold small">Pengeluaran</span>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="tipe_pemasukan" value="Pemasukan">
                            <label class="btn btn-outline-success w-100 py-2.5 px-2 rounded-3 d-flex flex-column align-items-center gap-1 pc-type-btn" for="tipe_pemasukan">
                                <i class="bi bi-arrow-down-left-circle fs-5"></i>
                                <span class="fw-bold small">Pengisian</span>
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="tipe" id="tipe_klaim" value="Klaim">
                            <label class="btn btn-outline-warning w-100 py-2.5 px-2 rounded-3 d-flex flex-column align-items-center gap-1 pc-type-btn" for="tipe_klaim">
                                <i class="bi bi-receipt-cutoff fs-5"></i>
                                <span class="fw-bold small">Klaim / Bon</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Nominal Transaksi & Quick Nominal Chips -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">2. Nominal (Rupiah) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text bg-light fw-bold border-end-0 text-muted">Rp</span>
                            <input type="text" name="nominal" id="input_nominal" class="form-control form-control-lg fw-bold font-monospace text-primary border-start-0 fs-4" placeholder="0" inputmode="numeric" required autocomplete="off">
                        </div>
                        
                        <!-- Quick Nominal Chips for fast phone typing -->
                        <div class="d-flex flex-wrap gap-1.5">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="20000">+20rb</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="50000">+50rb</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="100000">+100rb</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="250000">+250rb</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="500000">+500rb</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted small btn-quick-nominal" data-amount="1000000">+1jt</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 text-muted small btn-clear-nominal" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Kategori Cepat & Tanggal -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="input_tanggal" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Pemohon / Pengaju</label>
                                <input type="text" name="nama_pengaju" id="input_nama_pengaju" class="form-control rounded-3" placeholder="Contoh: Budi (Driver), Ani (Admin)" value="{{ auth()->user()->nama_lengkap ?? auth()->user()->username ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Kategori Transaksi <span class="text-danger">*</span></label>
                                <input type="text" name="kategori" id="input_kategori" class="form-control rounded-3 mb-2" placeholder="Pilih chip di bawah atau ketik manual..." value="Operasional & ATK" required>
                                <!-- Category Chips -->
                                <div class="d-flex flex-wrap gap-1.5" id="categoryChips">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="Operasional & ATK">📁 ATK & Kantor</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="BBM & Transportasi">⛽ Bensin & Tol</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="Konsumsi & Jamuan">☕ Konsumsi / Makan</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="Kurir & Ekspedisi">📦 Ongkir / Kurir</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="Maintenance & Kebersihan">🧹 Kebersihan / Servis</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small btn-cat-chip" data-cat="Pengisian Kas Kecil">💵 Pengisian Kas</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Koneksi COA & Hubungkan Tender -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-body">
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-success text-uppercase mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-journal-bookmark-fill"></i> 4. Integrasi Chart of Accounts (COA) &amp; Tender
                        </label>
                        
                        <div class="row g-3">
                            <!-- Akun Kas Kecil (COA Kas) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1" id="labelCoaKas">
                                    Akun Kas Kecil <span class="text-danger">*</span>
                                </label>
                                <select name="coa_kas_id" id="input_coa_kas_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Akun Kas --</option>
                                    @foreach($coaKasAccounts as $coa)
                                        <option value="{{ $coa->id }}" {{ str_contains(strtolower($coa->name), 'kas kecil') ? 'selected' : '' }}>
                                            {{ $coa->code }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Akun Aset / Kas yang terpotong/bertambah.</small>
                            </div>

                            <!-- Lawan COA (Biaya/Beban atau Kas Sumber) -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small text-muted fw-semibold mb-1" id="labelLawanCoa">
                                    Akun Biaya / Beban (Lawan COA) <span class="text-danger">*</span>
                                </label>
                                <select name="lawan_coa_id" id="input_lawan_coa_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Akun Beban / Biaya --</option>
                                    @foreach($coaAccounts as $coa)
                                        <option value="{{ $coa->id }}" {{ str_starts_with((string)$coa->code, '6') || str_starts_with((string)$coa->code, '5') ? 'data-expense="1"' : '' }}>
                                            {{ $coa->code }} - {{ $coa->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Akun lawan jurnal untuk pencatatan beban/sumber.</small>
                            </div>

                            <!-- Koneksi ke Tender / Proyek (Opsional) -->
                            <div class="col-12">
                                <label class="form-label small text-muted fw-semibold mb-1 d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-briefcase-fill text-warning me-1"></i> Hubungkan ke Tender / Proyek (Opsional)</span>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill fw-normal">Opsional</span>
                                </label>
                                <select name="tender_id" id="input_tender_id" class="form-select rounded-3">
                                    <option value="">-- Tidak Terhubung ke Tender / Umum --</option>
                                    @foreach($tenders as $t)
                                        <option value="{{ $t->id }}">
                                            {{ $t->nomor_kontrak }} - {{ $t->nama_tender }} ({{ $t->status }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 11px;">Jika biaya ini dikeluarkan untuk kebutuhan tender/proyek tertentu.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Keterangan & Kamera / Upload Foto Nota -->
                <div class="card border-0 shadow-sm rounded-3 bg-body">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Catatan / Keperluan</label>
                            <textarea name="keterangan" id="input_keterangan" rows="2" class="form-control rounded-3" placeholder="Tuliskan rincian pembelian atau keperluan transaksi..."></textarea>
                        </div>

                        <!-- Upload / Camera Struk -->
                        <div>
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1 d-flex align-items-center gap-1.5">
                                <i class="bi bi-camera-fill text-info"></i> Foto Struk / Nota Bukti (Kamera HP)
                            </label>
                            <input type="file" name="lampiran_nota" id="input_lampiran_nota" class="form-control rounded-3" accept="image/*,application/pdf" capture="environment">
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Bisa langsung foto struk lewat kamera HP atau upload file JPG/PNG/PDF (Maks 10MB).</small>
                            
                            <!-- Preview Image Container -->
                            <div id="previewReceiptContainer" class="mt-2 d-none">
                                <div class="position-relative d-inline-block border rounded-3 overflow-hidden shadow-sm" style="max-width: 140px;">
                                    <img id="imgReceiptPreview" src="#" alt="Preview Nota" class="img-fluid" style="max-height: 140px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1 lh-1" id="btnRemoveReceiptPreview" title="Hapus foto">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer with Touch Friendly Buttons -->
            <div class="modal-footer border-0 px-3 px-md-4 py-3 bg-body flex-shrink-0">
                <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btnSubmitPettyCash" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
                    <i class="bi bi-check2-circle fs-5"></i>
                    <span>Simpan &amp; Jurnal</span>
                </button>
            </div>
        </form>
    </div>
</div>
