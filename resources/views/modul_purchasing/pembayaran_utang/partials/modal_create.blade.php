<!-- Modal Create Pembayaran Utang -->
<div class="modal fade" id="modalCreatePayment" tabindex="-1" aria-labelledby="modalCreatePaymentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-payment-wide modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header pay-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-wallet2 text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalCreatePaymentLabel">Buat Pembayaran Utang Supplier</h5>
                        <div class="text-subtitle">Formulir pelunasan dan alokasi pembayaran faktur pembelian (AP)</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pay-modal-body p-4">
                <form id="formCreatePayment" novalidate>
                    @csrf
                    <input type="hidden" name="action_type" id="createActionType" value="draft">

                    <!-- SECTION 1: Informasi Header Pembayaran -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="pay-section-title">
                            <div class="pay-section-num">1</div>
                            <h6>Informasi Pembayaran & Rekening</h6>
                            <div class="pay-section-line"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label">Nomor Bukti Bayar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control" name="nomor_pembayaran" id="createNomorPembayaran" placeholder="Otomatis / Input Manual...">
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Diterbitkan otomatis atau isi nomor manual</small>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" class="form-control" name="tanggal_bayar" id="createTanggalBayar" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="supplier_id" id="createSupplierId" required>
                                    <option value="" selected disabled>-- Pilih Supplier --</option>
                                    @foreach($suppliers as $sup)
                                        @php
                                            $primaryBank = $sup->primaryBank ?? $sup->banks->first();
                                            $bankInfo = $primaryBank ? $primaryBank->nama_bank . ' - ' . $primaryBank->nomor_rekening . ' (a.n ' . $primaryBank->atas_nama_rekening . ')' : '';
                                        @endphp
                                        <option value="{{ $sup->id }}" data-bank="{{ $bankInfo }}">
                                            {{ $sup->kode_supplier }} - {{ $sup->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="createSupplierBankInfo" class="mt-1" style="display: none; font-size: 0.75rem;"></div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Akun Kas / Bank <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="kas_bank_id" id="createKasBankId" required>
                                    <option value="" selected disabled>-- Pilih Kas / Bank --</option>
                                    @foreach($kasBanks as $kb)
                                        <option value="{{ $kb->id }}" data-saldo="{{ (float)$kb->saldo_berjalan }}" data-nama="{{ $kb->nama_akun }}">
                                            {{ $kb->nama_akun }} (Saldo: Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="createKasBankSaldoInfo" class="mt-1" style="display: none; font-size: 0.75rem;"></div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select class="form-select form-select-sm" name="payment_method_id" id="createPaymentMethodId">
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->nama_metode }} ({{ $pm->kode_metode }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">No. Referensi / Cek / Giro</label>
                                <input type="text" class="form-control form-control-sm" name="nomor_referensi" id="createNomorReferensi" placeholder="Contoh: TRF-98231 / GIRO-001">
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Mata Uang</label>
                                <select class="form-select form-select-sm" name="currency_id" id="createCurrencyId">
                                    @foreach($currencies as $cur)
                                        <option value="{{ $cur->id }}" data-code="{{ $cur->kode_currency }}" data-symbol="{{ $cur->simbol ?? ($cur->kode_currency === 'IDR' ? 'Rp' : $cur->kode_currency) }}" {{ $cur->kode_currency === 'IDR' ? 'selected' : '' }}>
                                            {{ $cur->kode_currency }} - {{ $cur->nama_currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Kurs Valuta</label>
                                <input type="number" step="any" class="form-control form-control-sm text-end" name="kurs" id="createKurs" value="1.0000">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1.5">
                                    <span class="fw-bold"><i class="bi bi-file-earmark-arrow-up text-primary me-1.5"></i>Dokumen Bukti Pembayaran</span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-0.5" style="font-size: 0.7rem; font-weight: 500;">
                                        <i class="bi bi-shield-check me-1"></i>Kompresi WebP Otomatis
                                    </span>
                                </label>

                                <!-- Hidden Inputs -->
                                <input type="file" class="d-none" name="lampiran" id="createLampiran" accept="image/jpeg,image/png,image/webp,image/jpg">
                                <input type="file" class="d-none" id="createLampiranCameraInput" accept="image/*" capture="environment">

                                <!-- Upload Actions -->
                                <div id="createUploadActions" class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnTriggerCreateFile">
                                        <i class="bi bi-folder2-open text-primary"></i>
                                        <span>Pilih dari Berkas</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnTriggerCreateCamera">
                                        <i class="bi bi-camera-fill"></i>
                                        <span>Ambil Foto Langsung</span>
                                    </button>
                                </div>

                                <!-- Professional Document Preview Card -->
                                <div id="createLampiranPreviewWrapper" class="mt-2 p-2.5 border rounded-3 bg-body-tertiary d-none shadow-sm" style="max-width: 440px;">
                                    <div class="d-flex align-items-center justify-content-between gap-2.5">
                                        <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                            <div class="position-relative flex-shrink-0" style="cursor: pointer;" id="createLampiranImgContainer" title="Klik untuk melihat foto penuh">
                                                <img id="createLampiranPreviewImg" src="" alt="Bukti Pembayaran" class="rounded-3 border bg-white shadow-xs" style="width: 54px; height: 54px; object-fit: cover;">
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-truncate small text-body-emphasis" id="createLampiranFileName" style="font-size: 0.82rem;"></div>
                                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-0.5" id="createLampiranFileSize" style="font-size: 0.68rem; font-weight: 600;"></span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-0.5" style="font-size: 0.68rem; font-weight: 600;"><i class="bi bi-check2 me-0.5"></i>Optimal WebP</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-1.5" id="btnChangeCreateLampiran" title="Ganti Dokumen" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-arrow-repeat" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1.5" id="btnClearCreateLampiran" title="Hapus Dokumen" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Anti-Minus Kas/Bank -->
                    <div id="createSaldoWarning" class="alert alert-danger border-danger p-2.5 mb-4 align-items-center gap-2 rounded-3 shadow-sm d-none">
                        <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0"></i>
                        <div class="small fw-semibold" id="createSaldoWarningText"></div>
                    </div>

                    <!-- SECTION 2: Alokasi Faktur Pembelian (AP) -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <div class="pay-section-title mb-0">
                                <div class="pay-section-num">2</div>
                                <h6>Pilih &amp; Alokasikan Faktur Pembelian</h6>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 shadow-sm btn-lookup-invoices" id="btnLookupInvoices">
                                    <i class="bi bi-search me-1"></i> Cari &amp; Pilih Faktur
                                </button>
                                <span id="createInvoiceActionBtns" style="display: none !important;">
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2" id="btnPayAllSelected">
                                        <i class="bi bi-check-all me-1"></i> Lunaskan Terpilih
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 text-dark" id="btnSelectOverdueOnly">
                                        <i class="bi bi-clock-history me-1"></i> Jatuh Tempo
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" id="btnResetAllocation">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Nilai
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Live Search Input in Invoice Table -->
                        <div class="row g-2 align-items-center mb-3" id="createInvoiceFilterRow" style="display: none;">
                            <div class="col-12 col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-filter"></i></span>
                                    <input type="text" id="searchTableCreateInvoice" class="form-control" placeholder="Cari No. Faktur / Ref Supplier dalam daftar...">
                                </div>
                            </div>
                            <div class="col-12 col-md-7 text-md-end text-muted small" id="createInvoiceCountInfo">
                                <!-- Count info -->
                            </div>
                        </div>

                        <!-- Empty State Notice (Before Supplier is selected) -->
                        <div id="createInvoicesEmptyState" class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-2 text-primary opacity-50 d-block mb-2"></i>
                            <div class="fw-semibold">Pilih Supplier atau Klik "Cari &amp; Pilih Faktur"</div>
                            <small>Daftar faktur pembelian yang belum lunas akan dimuat secara otomatis di tabel ini.</small>
                        </div>

                        <!-- Loading State -->
                        <div id="createInvoicesLoading" class="text-center py-4 text-primary" style="display: none;">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <span>Memuat faktur pembelian...</span>
                        </div>

                        <!-- Invoice Table Container -->
                        <div class="table-responsive" id="createInvoicesTableWrapper" style="display: none;">
                            <table class="table table-bordered table-invoices align-middle mb-0" id="tableCreateInvoices">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 38px;">
                                            <input class="form-check-input" type="checkbox" id="checkAllCreateInvoices" title="Pilih Semua Faktur">
                                        </th>
                                        <th>No. Faktur</th>
                                        <th>Tgl Faktur</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Total Faktur</th>
                                        <th class="text-end">Sisa Utang</th>
                                        <th class="text-end" style="width: 175px;">Jumlah Bayar (Rp)</th>
                                        <th class="text-end" style="width: 140px;">Potongan (Rp)</th>
                                        <th class="text-end" style="width: 150px;">Sisa Akhir</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="createInvoicesTbody">
                                    <!-- Populated via dynamic JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 3: Catatan & Ringkasan Perhitungan -->
                    <div class="row g-3">
                        <div class="col-12 col-lg-7">
                            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                                <label class="form-label">Keterangan / Catatan Pembayaran</label>
                                <textarea class="form-control form-control-sm" name="keterangan" id="createKeterangan" rows="4" placeholder="Catatan tambahan pembayaran utang ini (opsional)..."></textarea>
                                <div class="mt-3 p-2 rounded border border-dashed text-muted" style="font-size: 0.75rem; background: rgba(37,99,235,0.05);">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    Ketika pembayaran <strong>Diposting</strong>, sistem akan secara otomatis membuat <strong>Jurnal Umum (Utang Usaha vs Kas/Bank)</strong> dan mencatat pengeluaran di <strong>Buku Kas / Rekening Bank</strong>.
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm rounded-3 p-3">
                                <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em;">Ringkasan Pembayaran</h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Faktur Terpilih</span>
                                    <span class="fw-bold" id="createSummaryCount">0 Faktur</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Total Sisa Utang Terpilih</span>
                                    <span class="fw-bold" id="createSummaryTotalUtang">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Total Potongan / Diskon</span>
                                    <span class="fw-bold text-danger" id="createSummaryTotalPotongan">Rp 0</span>
                                </div>

                                <div class="calc-grand-total mt-2" id="createGrandTotalBox">
                                    <div class="label">Total Pengeluaran Kas / Bank</div>
                                    <div class="value" id="createSummaryTotalBayar">Rp 0</div>
                                    <div id="createSummaryKasWarning" class="mt-2 text-danger fw-bold small text-center rounded p-1" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold" id="btnSubmitCreateDraft">
                        <i class="bi bi-save me-1"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 text-white fw-bold shadow-sm" id="btnSubmitCreateSubmit">
                        <i class="bi bi-send-fill me-1"></i> Ajukan Pembayaran
                    </button>
                    @can('approve-pembayaran-utang')
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 text-white fw-bold shadow-sm" id="btnSubmitCreatePost">
                        <i class="bi bi-shield-check me-1"></i> Setujui & Posting
                    </button>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
