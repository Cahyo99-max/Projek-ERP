<!-- Modal Edit Penerimaan Pembayaran Penjualan -->
<div class="modal fade" id="modalEditPayment" tabindex="-1" aria-labelledby="modalEditPaymentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1400px; width: 95%;">
        <form id="formEditPayment" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 bg-body overflow-hidden">
            @csrf
            <input type="hidden" id="edit_payment_id" name="payment_id">
            <input type="hidden" id="edit_status" name="status" value="Draft">

            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalEditPaymentLabel">Edit Penerimaan Pembayaran</h5>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace px-2 py-0.5" id="edit_nomor_badge">Draft</span>
                        </div>
                        <small class="text-body-secondary">Perbaiki informasi dan alokasi pembayaran piutang sebelum diposting</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-body" style="overflow-y: auto;">
                <!-- General Information Card -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-warning"></i>
                            <h6 class="fw-bold mb-0 text-body-emphasis" style="font-size: 0.9rem;">Informasi Penerimaan</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-pencil me-1"></i>Mode Edit Draft
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3.5">
                        <div class="row g-3">
                            <!-- Nomor Pembayaran -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">
                                    <i class="bi bi-hash text-secondary me-1"></i>No. Kwitansi / Bukti Bayar
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-receipt"></i></span>
                                    <input type="text" class="form-control bg-body-secondary font-monospace fw-bold" id="edit_nomor_pembayaran" readonly>
                                </div>
                            </div>

                            <!-- Customer / Nama PT -->
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">
                                    <i class="bi bi-building text-primary me-1"></i>Customer / Nama PT <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary text-primary"><i class="bi bi-person-badge"></i></span>
                                    <select class="form-select" id="edit_customer_id" name="customer_id" required>
                                        <option value="">-- Pilih Customer / Nama PT --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ !empty($c->nama_perusahaan) ? $c->nama_perusahaan . ' (' . $c->nama_customer . ')' : $c->nama_customer }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tanggal Bayar -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" class="form-control" id="edit_tanggal_bayar" name="tanggal_bayar" required>
                                </div>
                            </div>

                            <!-- Akun Kas / Bank Tujuan -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Kas / Bank Tujuan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary text-success"><i class="bi bi-bank"></i></span>
                                    <select class="form-select" id="edit_kas_bank_id" name="kas_bank_id" required>
                                        <option value="">-- Pilih Rekening Kas/Bank --</option>
                                        @foreach($kasBanks as $kb)
                                            <option value="{{ $kb->id }}">{{ $kb->nama_akun }} ({{ $kb->nomor_rekening ?? 'Kas Tunai' }}) - Saldo: Rp {{ number_format($kb->saldo_berjalan, 2, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Metode Pembayaran</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-credit-card"></i></span>
                                    <select class="form-select" id="edit_payment_method_id" name="payment_method_id">
                                        <option value="">-- Pilih Metode --</option>
                                        @foreach($paymentMethods as $pm)
                                            <option value="{{ $pm->id }}">{{ $pm->nama_metode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Nomor Referensi -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Nomor Referensi (No. Cek/Giro/Transfer)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control" id="edit_nomor_referensi" name="nomor_referensi" placeholder="Contoh: TRF-BCA-98212">
                                </div>
                            </div>

                            <!-- Bukti Pembayaran / Lampiran -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Bukti Transfer / Lampiran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-paperclip"></i></span>
                                    <input type="file" class="form-control" id="edit_lampiran" name="lampiran" accept="image/*,.pdf">
                                    <button type="button" class="btn btn-outline-info d-none align-items-center gap-1.5 px-3" id="btn_preview_edit_lampiran" title="Lihat Bukti Transfer">
                                        <i class="bi bi-eye-fill"></i>
                                        <span class="small fw-semibold">Lihat Bukti</span>
                                    </button>
                                </div>
                                <small class="text-body-secondary" style="font-size: 0.74rem;"><i class="bi bi-shield-check text-success me-1"></i>Format: JPG, PNG, WEBP, PDF (<strong class="text-danger">Wajib Disertakan</strong> &bull; Maks 10MB &bull; Kompres Otomatis ke KB). Biarkan kosong jika tidak ingin mengubah berkas yang ada.</small>
                            </div>

                            <!-- Keterangan -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-body-secondary mb-1">Keterangan / Memo Pembayaran</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-chat-left-text"></i></span>
                                    <input type="text" class="form-control" id="edit_keterangan" name="keterangan" placeholder="Contoh: Pembayaran faktur penjualan via transfer BCA">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Section Card -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 mb-4 bg-body-tertiary">
                    <div class="card-header bg-transparent border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-warning"></i>
                            <h6 class="fw-bold mb-0 text-body-emphasis" style="font-size: 0.9rem;">Daftar Faktur yang Dialokasikan</h6>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="edit_badge_count_invoices">0 Faktur</span>
                    </div>
                    <div class="card-body p-0">
                        <!-- Invoices Loading spinner -->
                        <div class="text-center py-5 d-none" id="edit_invoices_loading_state">
                            <div class="spinner-border text-warning" role="status" style="width: 2.2rem; height: 2.2rem;">
                                <span class="visually-hidden">Memuat faktur...</span>
                            </div>
                            <p class="text-body-secondary small mt-2 mb-0">Memuat rincian alokasi piutang...</p>
                        </div>

                        <!-- Invoices Table -->
                        <div class="table-responsive border border-body-tertiary rounded-3 shadow-sm overflow-hidden" id="edit_invoices_table_container">
                            <table class="table table-hover align-middle mb-0" id="table_edit_invoices">
                                <thead class="bg-body-secondary bg-opacity-75 text-body-secondary text-uppercase border-bottom" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="ps-3 py-3 text-center" style="width: 46px;">
                                            <input type="checkbox" class="form-check-input" id="edit_check_all_invoices" title="Pilih Semua Faktur">
                                        </th>
                                        <th style="min-width: 220px;">
                                            <i class="bi bi-receipt text-primary me-1"></i>No. Faktur & Tanggal
                                        </th>
                                        <th class="text-end" style="min-width: 140px;">
                                            <i class="bi bi-tag text-secondary me-1"></i>Total Faktur
                                        </th>
                                        <th class="text-end" style="min-width: 140px;">
                                            <i class="bi bi-exclamation-circle text-danger me-1"></i>Sisa Piutang
                                        </th>
                                        <th class="text-end" style="min-width: 185px;">
                                            <i class="bi bi-cash-stack text-success me-1"></i>Nominal Bayar (Rp)
                                        </th>
                                        <th class="text-end" style="min-width: 165px;">
                                            <i class="bi bi-percent text-warning me-1"></i>Potongan (Rp)
                                        </th>
                                        <th class="text-end pe-3" style="min-width: 125px;">
                                            <i class="bi bi-calculator me-1"></i>Sisa Akhir
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="edit_unpaid_invoices_tbody">
                                    <!-- Rendered dynamically via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="card border-0 shadow-lg rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                    <div class="card-body p-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-4 border-end border-secondary border-opacity-25 pe-md-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-receipt text-info"></i>
                                    <small class="text-white-50 text-uppercase font-monospace fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Faktur Dipilih</small>
                                </div>
                                <h4 class="fw-bold mb-0 text-white font-monospace"><span id="edit_sum_selected_count">0</span> <small class="fs-6 text-white-50 fw-normal">Faktur</small></h4>
                            </div>
                            <div class="col-md-4 border-end border-secondary border-opacity-25 px-md-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-percent text-warning"></i>
                                    <small class="text-white-50 text-uppercase font-monospace fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Potongan / Diskon</small>
                                </div>
                                <h4 class="fw-bold mb-0 text-warning font-monospace" id="edit_sum_total_potongan">Rp 0,00</h4>
                            </div>
                            <div class="col-md-4 ps-md-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-cash-stack text-success"></i>
                                    <small class="text-white-50 text-uppercase font-monospace fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Penerimaan Kas/Bank</small>
                                </div>
                                <h3 class="fw-bold mb-0 text-success font-monospace" id="edit_sum_total_bayar">Rp 0,00</h3>
                                <div id="edit_sum_lebih_bayar_container" class="d-none mt-1">
                                    <span class="badge bg-info text-dark font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.74rem;">
                                        <i class="bi bi-arrow-up-right-circle me-1"></i>Lebih Transfer: <span id="edit_sum_total_lebih" class="fw-bold">Rp 0,00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Alokasi Akun COA Potongan (Edit) -->
                        <div id="edit_container_potongan_coa" class="d-none mt-3 pt-3 border-top border-secondary border-opacity-25">
                            <div class="p-3 rounded-3" style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(245, 158, 11, 0.35); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);">
                                <div class="row align-items-center g-3">
                                    <div class="col-lg-5 col-md-12">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 flex-shrink-0" style="width: 44px; height: 44px;">
                                                <i class="bi bi-journal-arrow-down fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="text-warning fw-bold" style="font-size: 0.88rem;">Akun Jurnal Potongan</span>
                                                    <span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; letter-spacing: 0.5px;">DEBIT JURNAL</span>
                                                </div>
                                                <small class="text-white-50 d-block" style="font-size: 0.73rem; line-height: 1.3;">
                                                    Pilih COA beban/diskon untuk mencatat potongan ini ke ayat jurnal pembukuan.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7 col-md-12">
                                        <div class="dark-select2-container">
                                            <select class="form-select select2-potongan-coa" id="edit_potongan_coa_id" name="potongan_coa_id" style="width: 100%;">
                                                <option value="">-- Cari Kode atau Nama Akun COA --</option>
                                                @if(isset($potonganCoas))
                                                    @foreach($potonganCoas as $coa)
                                                        <option value="{{ $coa->id }}">
                                                            {{ $coa->code }} &mdash; {{ $coa->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top py-3 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark shadow-sm" id="btn_update_draft">
                        <i class="bi bi-save me-1"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn btn-info rounded-pill px-4 fw-bold text-dark shadow-sm" id="btn_update_submit">
                        <i class="bi bi-send me-1"></i> Simpan & Ajukan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
