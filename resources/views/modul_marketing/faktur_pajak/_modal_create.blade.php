<!-- Modal Buat Faktur Pajak Baru / Gabungan -->
<div class="modal fade" id="modalCreateFakturPajak" tabindex="-1" aria-labelledby="modalCreateFakturPajakLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 92%;">
        <form id="formCreateFakturPajak" action="{{ route('modul_marketing.faktur_pajak.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4 bg-body">
            @csrf
            
            <div class="modal-header border-bottom py-3 px-4 bg-body-secondary">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-file-earmark-check-fill fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalCreateFakturPajakLabel">Terbitkan Faktur Pajak Keluaran (PPN)</h5>
                        <p class="text-body-secondary small mb-0 font-monospace">Pilih customer, tentukan faktur penjualan (tunggal / gabungan), dan alokasikan NSFP resmi DJP.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Row 1: Informasi Pajak & NSFP -->
                <div class="card border-0 bg-body-tertiary rounded-3 p-3 mb-4 border">
                    <h6 class="fw-bold text-body-emphasis mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i> Parameter Faktur Pajak &amp; Alokasi NSFP
                    </h6>
                    <div class="row g-3">
                        <!-- Customer -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-body-emphasis">Customer Wajib Pajak <span class="text-danger">*</span></label>
                            <select name="customer_id" id="fp_create_customer_id" class="form-select rounded-3" required>
                                <option value="">-- Pilih Customer --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-npwp="{{ $cust->npwp ?? '' }}">
                                        {{ $cust->nama_customer }} ({{ $cust->kode_customer ?? 'CUST' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small font-monospace" id="fp_cust_tax_info">NPWP: -</div>
                        </div>

                        <!-- Tanggal Faktur Pajak -->
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-body-emphasis">Tanggal Pajak <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_faktur_pajak" id="fp_create_tanggal" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Masa & Tahun Pajak -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-emphasis">Masa / Tahun Pajak <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="masa_pajak" id="fp_create_masa" class="form-select rounded-start-3" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>Masa {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                                <input type="number" name="tahun_pajak" id="fp_create_tahun" class="form-control rounded-end-3 font-monospace" value="{{ $currentYear }}" required min="2020" max="2099">
                            </div>
                        </div>

                        <!-- Nomor Seri Faktur Pajak (NSFP) -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-emphasis d-flex justify-content-between">
                                <span>No. Seri Faktur (NSFP) <span class="text-danger">*</span></span>
                                <a href="javascript:void(0)" id="btn_fetch_nsfp" class="text-success text-decoration-none small"><i class="bi bi-magic"></i> Auto</a>
                            </label>
                            <input type="text" name="nomor_faktur_pajak" id="fp_create_nomor" class="form-control rounded-3 font-monospace fw-bold" placeholder="010.000-26.00000001" required>
                        </div>

                        <!-- Jenis Transaksi -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-emphasis">Kode Transaksi DJP <span class="text-danger">*</span></label>
                            <select name="jenis_transaksi" id="fp_create_jenis_transaksi" class="form-select rounded-3" required>
                                <option value="01" selected>01 - Kepada Pihak Bukan Pemungut PPN</option>
                                <option value="02">02 - Kepada Pemungut Bendaharawan</option>
                                <option value="03">03 - Kepada Pemungut BUMN / Non Bendaharawan</option>
                                <option value="04">04 - DPP Nilai Lain</option>
                                <option value="07">07 - Penyerahan Bebas PPN</option>
                                <option value="08">08 - Penyerahan Dibebaskan PPN</option>
                            </select>
                        </div>

                        <!-- Jenis Faktur -->
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-body-emphasis">Jenis Faktur</label>
                            <select name="jenis_faktur" id="fp_create_jenis_faktur" class="form-select rounded-3">
                                <option value="Normal" selected>Normal</option>
                                <option value="Pengganti">Pengganti</option>
                            </select>
                        </div>

                        <!-- Status Gabungan Indicator -->
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="p-2.5 rounded-3 bg-body border w-100 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="small fw-bold text-body-emphasis d-block" id="fp_gabungan_title">Tipe Faktur: Tunggal</span>
                                    <small class="text-body-secondary" id="fp_gabungan_desc">Faktur pajak akan ditautkan ke 1 faktur komersial.</small>
                                </div>
                                <span class="badge bg-secondary font-monospace" id="fp_gabungan_badge">1 Faktur</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Seleksi Faktur Penjualan Belum Ber-Faktur Pajak -->
                <div class="card border-0 bg-body-tertiary rounded-3 p-3 mb-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-primary"></i> Pilih Faktur Penjualan (Commercial Invoices)
                        </h6>
                        <span class="small text-body-secondary">Hanya faktur Approved/Posted yang belum memiliki Faktur Pajak</span>
                    </div>

                    <div id="fp_invoices_loading" class="text-center py-4 d-none">
                        <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                        <span class="small ms-2 text-body-secondary">Memuat daftar faktur penjualan customer...</span>
                    </div>

                    <div id="fp_invoices_empty" class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Silakan pilih customer terlebih dahulu untuk memunculkan faktur yang tersedia.
                    </div>

                    <div id="fp_invoices_wrapper" class="d-none">
                        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" id="table_selectable_invoices">
                                <thead class="bg-body-secondary sticky-top small text-uppercase fw-bold text-body-secondary">
                                    <tr>
                                        <th style="width: 35px;" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="check_all_invoices">
                                        </th>
                                        <th>No. Faktur Penjualan</th>
                                        <th>Tanggal</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">DPP</th>
                                        <th class="text-end">PPN</th>
                                        <th class="text-end">Grand Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_selectable_invoices">
                                    <!-- Injected via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Rincian Barang / Objek Faktur (OF) DJP Preview -->
                <div class="card border-0 bg-body-tertiary rounded-3 p-3 mb-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-box-seam text-warning"></i> Rincian Barang / Jasa Penyerahan (OF Baris e-Faktur)
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1" id="btn_recalculate_details">
                            <i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Item dari Faktur Terpilih
                        </button>
                    </div>

                    <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                        <table class="table table-sm table-bordered align-middle mb-0" id="table_fp_items">
                            <thead class="bg-body-secondary sticky-top small text-uppercase fw-bold text-body-secondary">
                                <tr>
                                    <th style="width: 30px;" class="text-center">No</th>
                                    <th>Nama Barang / Jasa Kena Pajak</th>
                                    <th style="width: 90px;" class="text-center">Qty</th>
                                    <th style="width: 140px;" class="text-end">Harga Satuan</th>
                                    <th style="width: 110px;" class="text-end">Diskon</th>
                                    <th style="width: 140px;" class="text-end">DPP</th>
                                    <th style="width: 130px;" class="text-end">PPN</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_fp_items">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3 small">Belum ada item faktur yang dipilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Row 4: Summary DPP & PPN Card -->
                <div class="row g-3 justify-content-end">
                    <div class="col-md-5">
                        <div class="card border-0 bg-body-secondary rounded-4 p-3.5 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold text-body-secondary">Jumlah DPP (Dasar Pengenaan Pajak):</span>
                                <span class="fw-bold font-monospace" id="label_total_dpp">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold text-body-secondary">Jumlah PPN (Pajak Pertambahan Nilai):</span>
                                <span class="fw-bold text-success font-monospace" id="label_total_ppn">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-body-emphasis">Total Penyerahan + PPN:</span>
                                <span class="fs-5 fw-bold text-primary font-monospace" id="label_total_keseluruhan">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-3 px-4 bg-body-secondary d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" id="btn_save_faktur_pajak">
                    <i class="bi bi-save2-fill"></i> Simpan Faktur Pajak
                </button>
            </div>
        </form>
    </div>
</div>
