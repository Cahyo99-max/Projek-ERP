<!-- Modal Edit Pembayaran Utang -->
<div class="modal fade" id="modalEditPayment" tabindex="-1" aria-labelledby="modalEditPaymentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-payment-wide modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header pay-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-pencil-square text-warning fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalEditPaymentLabel">Edit Pembayaran Utang (Draft)</h5>
                        <div class="text-subtitle">Perbaiki alokasi pembayaran faktur pembelian sebelum diposting</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pay-modal-body p-4">
                <form id="formEditPayment" novalidate>
                    @csrf
                    <input type="hidden" name="payment_id" id="editPaymentId">
                    <input type="hidden" name="action_type" id="editActionType" value="draft">

                    <!-- SECTION 1: Informasi Header Pembayaran -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="pay-section-title">
                            <div class="pay-section-num" style="background: #d97706;">1</div>
                            <h6>Informasi Pembayaran & Rekening</h6>
                            <div class="pay-section-line"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label">Nomor Bukti Bayar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control" name="nomor_pembayaran" id="editNomorPembayaran" placeholder="Nomor Bukti Bayar">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" class="form-control" name="tanggal_bayar" id="editTanggalBayar" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Supplier</label>
                                <select class="form-select form-select-sm" name="supplier_id" id="editSupplierId" required>
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
                                <div id="editSupplierBankInfo" class="mt-1" style="display: none; font-size: 0.75rem;"></div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label required">Akun Kas / Bank <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="kas_bank_id" id="editKasBankId" required>
                                    @foreach($kasBanks as $kb)
                                        <option value="{{ $kb->id }}" data-saldo="{{ (float)$kb->saldo_berjalan }}" data-nama="{{ $kb->nama_akun }}">
                                            {{ $kb->nama_akun }} (Saldo: Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div id="editKasBankSaldoInfo" class="mt-1" style="display: none; font-size: 0.75rem;"></div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select class="form-select form-select-sm" name="payment_method_id" id="editPaymentMethodId">
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->nama_metode }} ({{ $pm->kode_metode }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">No. Referensi / Cek / Giro</label>
                                <input type="text" class="form-control form-control-sm" name="nomor_referensi" id="editNomorReferensi">
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Mata Uang</label>
                                <select class="form-select form-select-sm" name="currency_id" id="editCurrencyId">
                                    @foreach($currencies as $cur)
                                        <option value="{{ $cur->id }}" data-code="{{ $cur->kode_currency }}" data-symbol="{{ $cur->simbol ?? ($cur->kode_currency === 'IDR' ? 'Rp' : $cur->kode_currency) }}" {{ $cur->kode_currency === 'IDR' ? 'selected' : '' }}>
                                            {{ $cur->kode_currency }} - {{ $cur->nama_currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label">Kurs Valuta</label>
                                <input type="number" step="any" class="form-control form-control-sm text-end" name="kurs" id="editKurs" value="1.0000">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label d-flex align-items-center justify-content-between mb-1.5">
                                    <span class="fw-bold"><i class="bi bi-file-earmark-arrow-up text-warning me-1.5"></i>Dokumen Bukti Pembayaran</span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-0.5" style="font-size: 0.7rem; font-weight: 500;">
                                        <i class="bi bi-shield-check me-1"></i>Kompresi WebP Otomatis
                                    </span>
                                </label>
                                <input type="hidden" name="remove_lampiran" id="editRemoveLampiran" value="0">

                                <!-- Hidden Inputs -->
                                <input type="file" class="d-none" name="lampiran" id="editLampiran" accept="image/jpeg,image/png,image/webp,image/jpg">
                                <input type="file" class="d-none" id="editLampiranCameraInput" accept="image/*" capture="environment">

                                <!-- Upload Actions -->
                                <div id="editUploadActions" class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnTriggerEditFile">
                                        <i class="bi bi-folder2-open text-warning"></i>
                                        <span>Pilih dari Berkas</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnTriggerEditCamera">
                                        <i class="bi bi-camera-fill"></i>
                                        <span>Ambil Foto Langsung</span>
                                    </button>
                                </div>

                                <!-- Professional Document Preview Card -->
                                <div id="editLampiranPreviewWrapper" class="mt-2 p-2.5 border rounded-3 bg-body-tertiary d-none shadow-sm" style="max-width: 440px;">
                                    <div class="d-flex align-items-center justify-content-between gap-2.5">
                                        <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                            <div class="position-relative flex-shrink-0" style="cursor: pointer;" id="editLampiranImgContainer" title="Klik untuk melihat foto penuh">
                                                <img id="editLampiranPreviewImg" src="" alt="Bukti Pembayaran" class="rounded-3 border bg-white shadow-xs" style="width: 54px; height: 54px; object-fit: cover;">
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-truncate small text-body-emphasis" id="editLampiranFileName" style="font-size: 0.82rem;"></div>
                                                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-0.5" id="editLampiranFileSize" style="font-size: 0.68rem; font-weight: 600;"></span>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-0.5" style="font-size: 0.68rem; font-weight: 600;"><i class="bi bi-check2 me-0.5"></i>Optimal WebP</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-1.5" id="btnChangeEditLampiran" title="Ganti Dokumen" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-arrow-repeat" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1.5" id="btnDeleteExistingLampiran" title="Hapus Dokumen" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Anti-Minus Kas/Bank -->
                    <div id="editSaldoWarning" class="alert alert-danger border-danger p-2.5 mb-4 align-items-center gap-2 rounded-3 shadow-sm d-none">
                        <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0"></i>
                        <div class="small fw-semibold" id="editSaldoWarningText"></div>
                    </div>

                    <!-- SECTION 2: Alokasi Faktur Pembelian -->
                    <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <div class="pay-section-title mb-0">
                                <div class="pay-section-num" style="background: #d97706;">2</div>
                                <h6>Alokasi Faktur Pembelian (AP)</h6>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2" id="btnEditPayAllSelected">
                                    <i class="bi bi-check-all me-1"></i> Lunaskan Semua Terpilih
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2 text-dark" id="btnEditSelectOverdueOnly">
                                    <i class="bi bi-clock-history me-1"></i> Pilih Jatuh Tempo
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" id="btnEditResetAllocation">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Nilai
                                </button>
                            </div>
                        </div>

                        <!-- Live Search Input in Edit Invoice Table -->
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-12 col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-filter"></i></span>
                                    <input type="text" id="searchTableEditInvoice" class="form-control" placeholder="Cari No. Faktur / Ref Supplier dalam daftar...">
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Table Container -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-invoices align-middle mb-0" id="tableEditInvoices">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 38px;">
                                            <input class="form-check-input" type="checkbox" id="checkAllEditInvoices" title="Pilih Semua">
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
                                <tbody id="editInvoicesTbody">
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
                                <textarea class="form-control form-control-sm" name="keterangan" id="editKeterangan" rows="4" placeholder="Catatan tambahan pembayaran utang ini..."></textarea>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm rounded-3 p-3">
                                <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em;">Ringkasan Pembayaran</h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Faktur Terpilih</span>
                                    <span class="fw-bold" id="editSummaryCount">0 Faktur</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Total Sisa Utang Terpilih</span>
                                    <span class="fw-bold" id="editSummaryTotalUtang">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                                    <span class="text-muted">Total Potongan / Diskon</span>
                                    <span class="fw-bold text-danger" id="editSummaryTotalPotongan">Rp 0</span>
                                </div>

                                <div class="calc-grand-total mt-2" id="editGrandTotalBox">
                                    <div class="label">Total Pengeluaran Kas / Bank</div>
                                    <div class="value" id="editSummaryTotalBayar">Rp 0</div>
                                    <div id="editSummaryKasWarning" class="mt-2 text-danger fw-bold small text-center rounded p-1" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);"></div>
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
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold" id="btnSubmitEditDraft">
                        <i class="bi bi-save me-1"></i> Simpan Draf
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 text-white fw-bold shadow-sm" id="btnSubmitEditSubmit">
                        <i class="bi bi-send-fill me-1"></i> Simpan &amp; Ajukan
                    </button>
                    @can('approve-pembayaran-utang')
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 text-white fw-bold shadow-sm" id="btnSubmitEditPost">
                        <i class="bi bi-shield-check me-1"></i> Simpan &amp; Posting
                    </button>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
