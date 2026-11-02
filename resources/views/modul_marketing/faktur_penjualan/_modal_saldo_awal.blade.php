{{-- =========================================================================
    MODAL PENCATATAN SALDO AWAL PIUTANG USAHA (MIGRASI DATA HISTORIS)
    File: resources/views/modul_marketing/faktur_penjualan/_modal_saldo_awal.blade.php
    ========================================================================= --}}

<div class="modal fade" id="modalSaldoAwalPiutang" tabindex="-1" aria-labelledby="modalSaldoAwalPiutangLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="background: #0f172a; color: #f8fafc; border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.1);">
            <!-- MODAL HEADER -->
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3 d-flex align-items-center justify-content-between" style="background: #1e293b;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35);">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalSaldoAwalPiutangLabel">Pencatatan Saldo Awal Piutang Usaha</h5>
                        <p class="small mb-0" style="color: #94a3b8;">Catat saldo faktur piutang masa lalu (Rupiah / IDR) sebelum migrasi ke sistem ERP.</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL BODY -->
            <div class="modal-body p-4" style="background: #0b1120; max-height: calc(85vh - 120px); overflow-y: auto;">
                <form id="formSaldoAwalPiutang" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="source_type" value="saldo_awal">
                    <input type="hidden" name="is_saldo_awal" value="1">
                    <input type="hidden" name="action_type" id="sa_action_type" value="post">
                    {{-- Khusus mata uang Rupiah (IDR) Kurs 1 --}}
                    <input type="hidden" name="currency_id" id="sa_currency_id" value="1">
                    <input type="hidden" name="kurs" id="sa_kurs" value="1">

                    <!-- ALERT INFORMASI MIGRASI SALDO AWAL -->
                    <div class="alert border d-flex align-items-start gap-3 py-3 px-3.5 mb-4 rounded-3" style="background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.35) !important; color: #fef3c7;">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-0.5" style="color: #fbbf24;"></i>
                        <div class="small leading-relaxed">
                            <strong style="color: #fbbf24;">Mode Migrasi Saldo Awal Piutang (Rupiah):</strong>
                            Gunakan form ini untuk mencatat sisa tagihan piutang customer dari transaksi masa lalu sebelum sistem ERP aktif.
                            Transaksi ini <strong class="text-white">tidak mengurangi stok gudang</strong> dan <strong class="text-white">tidak mempengaruhi saldo kas/bank berjalan</strong>.
                            Jurnal akan otomatis mengakui <em style="color: #6ee7b7;">Piutang Usaha (Debet)</em> dan <em style="color: #fcd34d;">Modal / Ekuitas Saldo Awal (Kredit)</em>.
                        </div>
                    </div>

                    <!-- BAGIAN 1: INFORMASI IDENTITAS & FAKTUR -->
                    <div class="card border rounded-3 mb-4" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                        <div class="card-header border-bottom py-2.5 px-3 d-flex align-items-center gap-2" style="background: #162032; border-color: rgba(148, 163, 184, 0.2) !important;">
                            <i class="bi bi-person-badge text-primary"></i>
                            <span class="fw-bold small text-white">1. Identitas Customer & Penomoran Faktur</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <!-- Customer -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="sa_customer_id" class="form-select select2-sa" required style="width: 100%;">
                                        <option value="">-- Pilih Customer --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">
                                                {{ $c->nama_perusahaan ?: $c->nama_customer }} ({{ $c->kode_customer ?: 'CUST-'.$c->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Pilih pihak customer penanggung piutang usaha.</div>
                                </div>

                                <!-- No. Faktur Pajak (NSFP) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">No. Faktur Pajak (NSFP) <span class="fw-normal" style="color: #94a3b8;">(Opsional)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border" style="background: #0f172a; color: #94a3b8; border-color: rgba(148, 163, 184, 0.25);"><i class="bi bi-tag"></i></span>
                                        <input type="text" name="nomor_faktur_pajak" id="sa_nomor_faktur_pajak" class="form-control font-monospace" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" placeholder="Contoh: 010.000-24.12345678">
                                    </div>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Nomor seri faktur pajak standar jika ada.</div>
                                </div>

                                <!-- No. Faktur Internal -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <label class="form-label fw-bold small mb-0" style="color: #f1f5f9;">No. Faktur Internal</label>
                                        <span class="small d-flex align-items-center gap-1" style="color: #38bdf8; font-size: 12px;">
                                            <i class="bi bi-pencil-square"></i> Bisa diisi manual / otomatis sistem
                                        </span>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text font-monospace border" style="background: #0f172a; color: #94a3b8; border-color: rgba(148, 163, 184, 0.25);">#</span>
                                        <input type="text" name="nomor_faktur" id="sa_nomor_faktur" class="form-control font-monospace" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" placeholder="[Otomatis dari Sistem]" autocomplete="off">
                                        <button type="button" class="btn btn-outline-info px-3 d-flex align-items-center gap-1.5" id="btn_sa_generate_nomor" title="Generate nomor otomatis sistem">
                                            <i class="bi bi-magic"></i> Otomatis
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <div style="color: #94a3b8; font-size: 12px;">
                                            Kosongkan untuk penomoran otomatis sistem (<code style="color: #f472b6;">INV-SLS/...</code>) atau ketik nomor manual faktur lama.
                                        </div>
                                        <div id="sa_nomor_status" class="font-monospace" style="font-size: 12px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 2: TANGGAL & KONTRAK / TENDER -->
                    <div class="card border rounded-3 mb-4" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                        <div class="card-header border-bottom py-2.5 px-3 d-flex align-items-center gap-2" style="background: #162032; border-color: rgba(148, 163, 184, 0.2) !important;">
                            <i class="bi bi-calendar-event text-info"></i>
                            <span class="fw-bold small text-white">2. Tanggal Transaksi & Keterangan Kontrak/Tender</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <!-- Tanggal Faktur -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Tanggal Faktur Asli <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_faktur" id="sa_tanggal_faktur" class="form-control" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" value="{{ date('Y-m-d') }}" required>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Tanggal penerbitan nota/faktur historis.</div>
                                </div>

                                <!-- Tanggal Jatuh Tempo -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_jatuh_tempo" id="sa_tanggal_jatuh_tempo" class="form-control" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Batas akhir pembayaran piutang.</div>
                                </div>

                                <!-- Tender / Proyek (Sesuai Cabang/PT Aktif) -->
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Keterangan Proyek / Tender <span class="fw-normal" style="color: #94a3b8;">(Opsional)</span></label>
                                    <select name="tender_id" id="sa_tender_id" class="form-select select2-sa" style="width: 100%;">
                                        <option value="">-- Tanpa Kontrak / Bebas --</option>
                                        @foreach($tenders as $t)
                                            <option value="{{ $t->id }}">
                                                {{ $t->kode_tender ? $t->kode_tender.' - ' : '' }}{{ $t->nama_tender }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Pilih jika piutang terkait kontrak/tender aktif.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 3: TIGA KARTU KALKULASI SALDO AWAL PIUTANG (IDR / RUPIAH) -->
                    <div class="card border rounded-3 mb-4" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                        <div class="card-header border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: #162032; border-color: rgba(148, 163, 184, 0.2) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-coin text-warning"></i>
                                <span class="fw-bold small text-white">3. Nominal Saldo Awal Piutang (Mata Uang Rupiah - IDR)</span>
                            </div>
                            <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.35); border-radius: 9999px; padding: 2px 10px; font-size: 11px; font-weight: 700;">
                                RUPIAH (IDR)
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <!-- Kartu 1: Nilai Faktur Asli -->
                                <div class="col-12 col-md-4">
                                    <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: #0f172a; border-color: rgba(56, 189, 248, 0.35) !important;">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold small" style="color: #38bdf8;">
                                                    <i class="bi bi-receipt-cutoff me-1"></i> Nilai Faktur Asli
                                                </span>
                                                <span style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 9999px; padding: 2px 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                                                    GROSS
                                                </span>
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text border fw-bold" style="background: #1e293b; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);">Rp</span>
                                                <input type="text" name="nilai_faktur_asli" id="sa_nilai_faktur_asli" class="form-control text-end fw-bold fs-5 font-monospace" style="background: #090e17; color: #38bdf8; border-color: rgba(148, 163, 184, 0.25);" placeholder="0" required autocomplete="off">
                                            </div>
                                        </div>
                                        <div style="color: #cbd5e1; font-size: 12px; line-height: 1.4;">
                                            Total nominal awal pada lembar faktur lama.
                                        </div>
                                    </div>
                                </div>

                                <!-- Kartu 2: Pembayaran / DP Masa Lalu -->
                                <div class="col-12 col-md-4">
                                    <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: #0f172a; border-color: rgba(251, 191, 36, 0.35) !important;">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold small" style="color: #fbbf24;">
                                                    <i class="bi bi-wallet2 me-1"></i> DP / Cicilan Masa Lalu
                                                </span>
                                                <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); border-radius: 9999px; padding: 2px 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                                                    PENGURANG
                                                </span>
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text border fw-bold" style="background: #1e293b; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);">Rp</span>
                                                <input type="text" name="uang_muka" id="sa_uang_muka" class="form-control text-end fw-bold fs-5 font-monospace" style="background: #090e17; color: #fbbf24; border-color: rgba(148, 163, 184, 0.25);" placeholder="0" autocomplete="off">
                                            </div>
                                        </div>
                                        <div style="color: #cbd5e1; font-size: 12px; line-height: 1.4;">
                                            Nominal yang sudah dilunasi customer sebelum ERP.
                                        </div>
                                    </div>
                                </div>

                                <!-- Kartu 3: Sisa Piutang yang Diakui (Net Piutang) -->
                                <div class="col-12 col-md-4">
                                    <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(52, 211, 153, 0.45) !important;">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold small" style="color: #34d399;">
                                                    <i class="bi bi-check2-circle me-1"></i> Sisa Piutang Diakui
                                                </span>
                                                <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); border-radius: 9999px; padding: 2px 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                                                    NET TAGIHAN
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-baseline gap-1 py-1.5 px-2 rounded mb-2" style="background: #090e17; border: 1px solid rgba(52, 211, 153, 0.3);">
                                                <span class="fw-bold fs-6" style="color: #94a3b8;">Rp</span>
                                                <span class="fw-bold fs-4 font-monospace ms-1" id="sa_sisa_piutang_display" style="color: #34d399;">0</span>
                                            </div>
                                        </div>
                                        <div style="color: #cbd5e1; font-size: 12px; line-height: 1.4;">
                                            Saldo bersih yang akan dicatat di buku besar AR.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 4: PEMETAAN AKUN AKUNTANSI (COA JURNAL) -->
                    <div class="card border rounded-3 mb-4" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                        <div class="card-header border-bottom py-2.5 px-3 d-flex align-items-center gap-2" style="background: #162032; border-color: rgba(148, 163, 184, 0.2) !important;">
                            <i class="bi bi-journal-check text-warning"></i>
                            <span class="fw-bold small text-white">4. Pemetaan Akun Jurnal (Debet Piutang & Kredit Saldo Awal)</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <!-- Akun Piutang Usaha (Debit) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">
                                        <i class="bi bi-arrow-up-right me-1" style="color: #34d399;"></i> Akun Piutang Usaha (Debit) <span class="text-danger">*</span>
                                    </label>
                                    <select name="piutang_coa_id" id="sa_piutang_coa_id" class="form-select select2-sa" style="width: 100%;">
                                        <option value="">-- Gunakan Default Pemetaan AR --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ ($defaultArCoaId ?? null) == $acc->id ? 'selected' : '' }}>
                                                {{ $acc->code }} - {{ $acc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Default: Akun Piutang Usaha / Dagang.</div>
                                </div>

                                <!-- Akun Penyeimbang Saldo Awal (Kredit) -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">
                                        <i class="bi bi-arrow-down-left me-1" style="color: #fbbf24;"></i> Akun Penyeimbang Saldo Awal (Kredit) <span class="text-danger">*</span>
                                    </label>
                                    <select name="saldo_awal_coa_id" id="sa_saldo_awal_coa_id" class="form-select select2-sa" style="width: 100%;">
                                        <option value="">-- Gunakan Default Ekuitas Pembukaan --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ ($defaultSaldoAwalCoaId ?? null) == $acc->id ? 'selected' : '' }}>
                                                {{ $acc->code }} - {{ $acc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Default: Akun Saldo Awal / Ekuitas Pembukaan / Modal Disetor.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN 5: CATATAN & BUKTI LAMPIRAN -->
                    <div class="card border rounded-3 mb-2" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                        <div class="card-header border-bottom py-2.5 px-3 d-flex align-items-center gap-2" style="background: #162032; border-color: rgba(148, 163, 184, 0.2) !important;">
                            <i class="bi bi-paperclip text-secondary"></i>
                            <span class="fw-bold small text-white">5. Catatan Tambahan & Bukti Fisik Nota/Faktur</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Catatan / Keterangan Tambahan</label>
                                    <textarea name="catatan" id="sa_catatan" class="form-control" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" rows="3" placeholder="Contoh: Migrasi saldo piutang proyek tahun 2024, sisa tagihan termin 2..."></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small" style="color: #f1f5f9;">Unggah Scan Nota / Faktur Fisik <span class="fw-normal" style="color: #94a3b8;">(Opsional)</span></label>
                                    <input type="file" name="file_pendukung" id="sa_file_pendukung" class="form-control" style="background: #0f172a; color: #f8fafc; border-color: rgba(148, 163, 184, 0.25);" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                    <div class="mt-1" style="color: #94a3b8; font-size: 12px;">Format: PDF, JPG, JPEG, PNG, WEBP. Maksimal ukuran 10 MB.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- MODAL FOOTER -->
            <div class="modal-footer border-top px-4 py-3 d-flex align-items-center justify-content-between" style="background: #1e293b; border-color: rgba(148, 163, 184, 0.2) !important;">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-warning rounded-pill px-4 fw-bold shadow-sm" id="btn_sa_save_draft">
                        <i class="bi bi-file-earmark me-1"></i> Simpan Draf
                    </button>
                    <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark" id="btn_sa_save_post">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Terbitkan Piutang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Penyesuaian tema Select2 di dalam Modal Saldo Awal */
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-selection {
    background-color: #0f172a !important;
    border-color: rgba(148, 163, 184, 0.25) !important;
    color: #f8fafc !important;
    min-height: 38px;
    padding-top: 4px;
}
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-selection__rendered {
    color: #f8fafc !important;
}
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-selection__placeholder {
    color: #94a3b8 !important;
}
#modalSaldoAwalPiutang .select2-dropdown {
    background-color: #1e293b !important;
    border-color: rgba(148, 163, 184, 0.25) !important;
    color: #f8fafc !important;
}
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-search__field {
    background-color: #0f172a !important;
    border-color: rgba(148, 163, 184, 0.25) !important;
    color: #f8fafc !important;
}
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-results__option {
    color: #f8fafc !important;
}
#modalSaldoAwalPiutang .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}
</style>

{{-- SCRIPT KHUSUS UNTUK MODAL SALDO AWAL PIUTANG --}}
@push('scripts')
<script>
$(document).ready(function() {
    const $modalSa = $('#modalSaldoAwalPiutang');
    const $formSa  = $('#formSaldoAwalPiutang');

    // Inisialisasi Select2 di dalam modal
    $modalSa.on('shown.bs.modal', function() {
        $('.select2-sa').select2({
            dropdownParent: $modalSa,
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Set default nomor jika kosong
        if (!$('#sa_nomor_faktur').val()) {
            $('#sa_nomor_status').html('<span style="color: #94a3b8;"><i class="bi bi-info-circle me-1"></i>Otomatis Sistem</span>');
        }
    });

    // Format angka ribuan
    function formatRibuan(val) {
        if (!val) return '0';
        let parts = val.toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return parts.join(',');
    }

    // Unformat angka ke float
    function unformatNumber(str) {
        if (!str) return 0;
        let cleaned = str.toString().replace(/\./g, '').replace(/,/g, '.');
        let parsed = parseFloat(cleaned);
        return isNaN(parsed) ? 0 : parsed;
    }

    // Format input saat diketik
    function handleNumericInput($el) {
        let val = $el.val().replace(/[^\d]/g, '');
        if (val === '') {
            $el.val('');
        } else {
            $el.val(formatRibuan(val));
        }
    }

    $('#sa_nilai_faktur_asli, #sa_uang_muka').on('input', function() {
        handleNumericInput($(this));
        kalkulasiSaldoAwal();
    });

    // Kalkulasi Live Saldo Awal Piutang (Khusus Rupiah)
    function kalkulasiSaldoAwal() {
        let nilaiAsli = unformatNumber($('#sa_nilai_faktur_asli').val());
        let dp        = unformatNumber($('#sa_uang_muka').val());

        if (dp > nilaiAsli && nilaiAsli > 0) {
            $('#sa_uang_muka').addClass('is-invalid');
            $('#sa_sisa_piutang_display').text('0').css('color', '#ef4444');
            return;
        } else {
            $('#sa_uang_muka').removeClass('is-invalid');
            $('#sa_sisa_piutang_display').css('color', '#34d399');
        }

        let sisaPiutang = Math.max(0, nilaiAsli - dp);
        $('#sa_sisa_piutang_display').text(formatRibuan(sisaPiutang));
    }

    // Tombol Otomatis Generate Nomor Faktur
    $('#btn_sa_generate_nomor').on('click', function() {
        let tgl = $('#sa_tanggal_faktur').val();
        let $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengambil...');

        $.ajax({
            url: "{{ route('modul_marketing.faktur_penjualan.generate_nomor') }}",
            type: 'GET',
            data: { tanggal_faktur: tgl },
            success: function(res) {
                if (res.status === 'success' && res.nomor_faktur) {
                    $('#sa_nomor_faktur').val(res.nomor_faktur);
                    $('#sa_nomor_status').html('<span style="color: #34d399;"><i class="bi bi-check-circle me-1"></i>Nomor Otomatis Tergenerate</span>');
                }
            },
            error: function() {
                $('#sa_nomor_status').html('<span style="color: #ef4444;"><i class="bi bi-exclamation-triangle me-1"></i>Gagal generate otomatis</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-magic"></i> Otomatis');
            }
        });
    });

    // Cek Keunikan Nomor Manual (Debounce 500ms)
    let saNomorTimer = null;
    $('#sa_nomor_faktur').on('input', function() {
        clearTimeout(saNomorTimer);
        let val = $(this).val().trim();

        if (val === '') {
            $('#sa_nomor_status').html('<span style="color: #94a3b8;"><i class="bi bi-info-circle me-1"></i>Otomatis Sistem</span>');
            return;
        }

        $('#sa_nomor_status').html('<span style="color: #38bdf8;"><i class="bi bi-hourglass-split me-1"></i>Mengecek ketersediaan...</span>');

        saNomorTimer = setTimeout(function() {
            $.ajax({
                url: "{{ route('modul_marketing.faktur_penjualan.check_nomor') }}",
                type: 'GET',
                data: { nomor: val },
                success: function(res) {
                    if (res.is_unique) {
                        $('#sa_nomor_status').html(`<span style="color: #34d399;"><i class="bi bi-check-circle me-1"></i>${res.message}</span>`);
                        $('#sa_nomor_faktur').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#sa_nomor_status').html(`<span style="color: #ef4444;"><i class="bi bi-x-circle me-1"></i>${res.message}</span>`);
                        $('#sa_nomor_faktur').removeClass('is-valid').addClass('is-invalid');
                    }
                },
                error: function() {
                    $('#sa_nomor_status').html('<span style="color: #94a3b8;">Gagal memvalidasi nomor faktur.</span>');
                }
            });
        }, 500);
    });

    // Submit Saldo Awal (Draft atau Post)
    $('#btn_sa_save_draft').on('click', function() {
        submitFormSaldoAwal('draft');
    });

    $('#btn_sa_save_post').on('click', function() {
        submitFormSaldoAwal('post');
    });

    function submitFormSaldoAwal(actionType) {
        $('#sa_action_type').val(actionType);

        let customerId = $('#sa_customer_id').val();
        if (!customerId) {
            Swal.fire({
                icon: 'warning',
                title: 'Customer Belum Dipilih',
                text: 'Harap pilih customer yang memiliki piutang masa lalu ini!',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        let nilaiAsli = unformatNumber($('#sa_nilai_faktur_asli').val());
        if (nilaiAsli <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nilai Faktur Kosong',
                text: 'Harap masukkan Nilai Faktur Asli yang valid (lebih besar dari 0)!',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        let dp = unformatNumber($('#sa_uang_muka').val());
        if (dp >= nilaiAsli) {
            Swal.fire({
                icon: 'warning',
                title: 'Nominal DP Tidak Valid',
                text: 'Nominal DP/cicilan masa lalu tidak boleh melebihi atau sama dengan Nilai Faktur Asli!',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        let tglFaktur = $('#sa_tanggal_faktur').val();
        let tglJatuhTempo = $('#sa_tanggal_jatuh_tempo').val();
        if (!tglFaktur || !tglJatuhTempo) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Belum Lengkap',
                text: 'Tanggal Faktur dan Tanggal Jatuh Tempo wajib diisi.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        let confirmTitle = actionType === 'draft' ? 'Simpan Sebagai Draf?' : 'Simpan & Terbitkan Piutang?';
        let confirmText  = actionType === 'draft'
            ? 'Faktur saldo awal akan disimpan dengan status Draft dan belum diakui di jurnal akuntansi.'
            : 'Faktur saldo awal akan langsung diterbitkan (Unpaid) dan jurnal akuntansi pengakuan Piutang Usaha akan otomatis terbentuk.';

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: actionType === 'draft' ? '#6b7280' : '#f59e0b',
            cancelButtonColor: '#374151',
            confirmButtonText: actionType === 'draft' ? 'Ya, Simpan Draf' : 'Ya, Terbitkan Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData($formSa[0]);

                // Pastikan format angka integer/float murni dikirim ke server
                formData.set('nilai_faktur_asli', nilaiAsli);
                formData.set('uang_muka', dp);
                formData.set('currency_id', 1);
                formData.set('kurs', 1);

                Swal.fire({
                    title: 'Menyimpan Saldo Awal...',
                    text: 'Mohon tunggu sejenak...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('modul_marketing.faktur_penjualan.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Pencatatan Saldo Awal Piutang berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $modalSa.modal('hide');
                            $formSa[0].reset();
                            $('#sa_customer_id').val('').trigger('change');
                            $('#sa_tender_id').val('').trigger('change');
                            $('#sa_nomor_status').empty();

                            // Reload DataTables Faktur Penjualan
                            if ($.fn.DataTable.isDataTable('#tableSalesInvoices')) {
                                $('#tableSalesInvoices').DataTable().ajax.reload(null, false);
                            } else if (typeof window.tableFaktur !== 'undefined') {
                                window.tableFaktur.ajax.reload(null, false);
                            } else {
                                location.reload();
                            }
                        });
                    },
                    error: function(xhr) {
                        let errMsg = 'Terjadi kesalahan saat menyimpan Saldo Awal Piutang.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = Object.values(xhr.responseJSON.errors).flat();
                            errMsg = errors.join('<br>');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            html: errMsg,
                            confirmButtonColor: '#ef4444'
                        });
                    }
                });
            }
        });
    }
});
</script>
@endpush
