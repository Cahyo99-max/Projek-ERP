{{-- ===== MODAL TAMBAH PO ===== --}}
<div class="modal fade" id="modalTambahPO" aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl po-modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Buat Purchase Order (PO)</h5>
                        <small class="text-muted">Buat pesanan pembelian baru berdasarkan PR atau manual</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahPO" autocomplete="off">
                @csrf
                <div class="modal-body p-4">
                    <!-- HEADER FIELDS -->
                    <div class="row g-4 mb-4">
                        <!-- Left Column: PO General Information Card -->
                        <div class="col-lg-6">
                            <div class="form-card h-100">
                                <div class="form-card-title text-primary">
                                    <i class="bi bi-info-circle-fill"></i> Informasi Umum PO
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Nomor PO <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0" style="border: 1.5px solid #cbd5e1; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                                <input class="form-check-input mt-0" type="checkbox" id="auto_number_toggle" checked value="1">
                                                <label class="form-check-label ms-1.5 small fw-bold text-secondary" for="auto_number_toggle">Auto</label>
                                            </span>
                                            <input type="text" name="nomor_po" id="nomor_po_input" class="form-control form-control-custom" placeholder="Draft" disabled autocomplete="new-password" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" maxlength="15">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Tanggal PO <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_po" id="tanggal_po" class="form-control form-control-custom" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Tanggal Pengiriman</label>
                                        <input type="date" name="tanggal_pengiriman" id="tanggal_pengiriman" class="form-control form-control-custom">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                                        <input type="hidden" name="supplier_id" id="supplier_id" required>
                                        <div class="input-group">
                                            <input type="text" id="supplier_display" class="form-control form-control-custom" placeholder="Cari & pilih supplier..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                            <button type="button" class="btn btn-primary px-3" id="btnOpenSupplierPicker" title="Cari Supplier" style="border-radius: 0 10px 10px 0;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Sumber PR (Pengajuan)</label>
                                        <input type="hidden" name="pr_id" id="pr_id">
                                        <div class="input-group">
                                            <input type="text" name="manual_pr_number" id="pr_display" class="form-control form-control-custom" placeholder="Cari & pilih PR..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                            <button type="button" class="btn btn-info px-3" id="btnOpenPRPicker" title="Cari PR" style="border-radius: 0 10px 10px 0;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted mt-1 d-inline-block"><i class="bi bi-info-circle me-1"></i>Kosongkan jika PO manual tanpa PR</small>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label fw-semibold text-primary">
                                            <i class="bi bi-briefcase me-1"></i> Referensi Tender / Kontrak Project
                                        </label>
                                        <input type="hidden" name="tender_id" id="tender_id">
                                        <div class="input-group">
                                            <input type="text" id="tender_display" class="form-control form-control-custom" placeholder="Cari & pilih Tender / Kontrak Project..." readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                            <button type="button" class="btn btn-primary px-3" id="btnOpenTenderPicker" title="Cari Tender" style="border-radius: 0 10px 10px 0;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Kosongkan jika pembelian operasional biasa tanpa tender.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Financial & Payment Terms Information Card -->
                        <div class="col-lg-6">
                            <div class="form-card h-100">
                                <div class="form-card-title text-success">
                                    <i class="bi bi-cash-coin"></i> Keuangan & Syarat Pembayaran
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Mata Uang <span class="text-danger">*</span></label>
                                        <select name="currency_id" id="currency_id" class="form-select select2-init" required>
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->id }}" data-symbol="{{ $curr->simbol }}" data-code="{{ $curr->kode_currency }}" {{ $curr->kode_currency === 'IDR' ? 'selected' : '' }}>
                                                    {{ $curr->kode_currency }} ({{ $curr->simbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nilai Kurs Rupiah <span class="text-danger">*</span></label>
                                        <input type="text" name="kurs" id="kurs_input" class="form-control form-control-custom text-end fw-bold number-format" value="1" readonly required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tenor Pembayaran</label>
                                        <input type="hidden" name="payment_term_id" id="payment_term_id">
                                        <div class="input-group">
                                            <input type="text" id="payment_term_display" class="form-control form-control-custom" value="Cash / COD" readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                            <button type="button" class="btn btn-warning px-3 text-white" id="btnOpenTenorPicker" title="Cari Tenor" style="border-radius: 0 10px 10px 0;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Metode Pembayaran</label>
                                        <input type="hidden" name="payment_method_id" id="payment_method_id">
                                        <div class="input-group">
                                            <input type="text" id="payment_method_display" class="form-control form-control-custom" value="Belum Ditentukan" readonly style="cursor:pointer; border-top-right-radius:0; border-bottom-right-radius:0;">
                                            <button type="button" class="btn btn-warning px-3 text-white" id="btnOpenMetodePicker" title="Cari Metode" style="border-radius: 0 10px 10px 0;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="po_lower_form_wrapper">
                        <!-- PR ITEMS SECTION (VISIBLE ONLY WHEN PR SELECTED) -->
                    <div id="pr_items_card" class="card border-0 shadow-sm p-3 mb-4 d-none" style="background: var(--card-bg); border-radius: 12px;">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-list-check"></i> Daftar Item dari PR Asal (Pilih & Centang)</h6>
                        <div class="table-responsive">
                            <table class="table align-middle table-modern" style="min-width: 850px;">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; min-width: 40px; text-align: center;"><input type="checkbox" id="check_all_pr_items"></th>
                                        <th style="min-width: 250px;">Nama Barang</th>
                                        <th style="min-width: 100px; text-align: center;">Satuan</th>
                                        <th style="min-width: 90px; text-align: center;">Qty PR</th>
                                        <th style="min-width: 90px; text-align: center;">Qty Sisa</th>
                                        <th style="min-width: 130px; text-align: right;">Est. Harga</th>
                                        <th style="min-width: 150px;">Keterangan PR</th>
                                    </tr>
                                </thead>
                                <tbody id="pr_items_tbody">
                                    <!-- Loaded dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PO DETAILS SECTION (WRAPPED IN CARD) -->
                    <div class="card border-0 shadow-sm p-4 mb-4" style="background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-color) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold m-0 text-dark">
                                <i class="bi bi-cart-fill text-primary me-2"></i> Detail Transaksi Barang PO
                            </h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-1.5 rounded-pill" id="po_item_count_badge">
                                0 / 25 Item
                            </span>
                        </div>
                        <div class="table-responsive table-po-details-wrapper mb-2">
                            <table class="table align-middle responsive-po-table table-modern" id="tableDetailsPO">
                                <thead>
                                    <tr>
                                        <th width="26%">Nama Barang</th>
                                        <th width="9%" class="text-center">Satuan</th>
                                        <th width="10%" class="text-center">Jumlah Qty</th>
                                        <th width="13%" class="text-end">Harga Satuan</th>
                                        <th width="14%" class="text-center">Diskon Item</th>
                                        <th width="12%" class="text-end">Subtotal</th>
                                        <th width="13%" class="text-center">PPN</th>
                                        <th width="3%" class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="po_details_tbody">
                                    <!-- Rows added dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btn_add_manual_row">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Item Manual
                            </button>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Maksimal 25 item barang per transaksi PO.</small>
                        </div>
                        <div id="manual_row_info" class="text-muted small mt-2 d-none">
                            <i class="bi bi-info-circle text-warning me-1"></i> Tambah item manual dinonaktifkan karena Anda memilih PR sebagai sumber pengajuan. Silakan centang barang di daftar PR di atas.
                        </div>
                    </div>

                    <!-- SUMMARY & BIAYA LAIN SECTION -->
                    <div class="row g-4">
                        <!-- Left Side: Card Biaya Lain-lain & Informasi Pengiriman -->
                        <div class="col-lg-7">
                            <!-- Card Biaya Lain-lain (Exact User Image Design) -->
                            <div class="card border-0 shadow-sm p-4 mb-4" style="background: var(--card-bg); border-radius: 16px; border: 1px solid #dbeafe !important;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold m-0 text-primary"><i class="bi bi-tag-fill me-1"></i> Biaya Lain-lain</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3 px-3 fw-semibold" id="btn_add_expense_row">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Biaya
                                    </button>
                                </div>
                                <div class="table-responsive border-0 rounded-3 mb-3" style="overflow: visible !important;">
                                    <table class="table align-middle table-sm mb-0 table-akuntansi responsive-expense-table">
                                        <thead>
                                            <tr class="bg-light">
                                                <th width="30%">JENIS BIAYA</th>
                                                <th width="35%">KETERANGAN</th>
                                                <th width="18%">NOMINAL (<span class="exp-currency-label">IDR</span>)</th>
                                                <th width="17%">MASUK HPP</th>
                                                <th width="40px" class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="expense_items_tbody">
                                            <!-- Loaded Dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info border-0 rounded-3 py-2 px-3 m-0 small d-flex align-items-center gap-2 alert-biaya-info">
                                    <i class="bi bi-info-circle fs-5"></i>
                                    <span>Biaya lain-lain yang masuk HPP akan menambah nilai persediaan.</span>
                                </div>
                            </div>

                            <!-- Card Catatan PO -->
                            <div class="card border-0 shadow-sm p-4" style="background: var(--card-bg); border-radius: 16px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold m-0" style="color: var(--text-main);"><i class="bi bi-chat-left-text me-1"></i> Catatan / Keterangan PO</h6>
                                    <small class="text-muted fw-medium" id="ket_char_count">0 / 40 Karakter</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <textarea name="keterangan" id="keterangan" class="form-control form-control-custom" rows="3" maxlength="40" placeholder="Syarat pembayaran, lokasi pengiriman, catatan tambahan, dll..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Card Perhitungan (Exact User Image Design) -->
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm p-4 h-100 card-perhitungan" style="border-radius: 16px;">
                                <h6 class="fw-bold mb-3 text-dark fs-6">Perhitungan</h6>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Sub Total Barang</span>
                                    <span class="fw-bold text-dark" id="label_subtotal">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Total Diskon Barang</span>
                                    <span class="fw-bold text-danger" id="label_total_diskon">- 0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Biaya Menambah Persediaan (Inventory)</span>
                                    <span class="fw-bold text-success" id="label_biaya_inventory">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Biaya Operasional (Expense)</span>
                                    <span class="fw-bold text-warning" id="label_biaya_expense">0.00</span>
                                </div>

                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-dark fw-bold small">DPP (Dasar Pengenaan Pajak)</span>
                                    <span class="fw-bold text-dark" id="label_dpp">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small" id="label_pajak_title">Total PPN (Pajak Barang)</span>
                                    <span class="fw-bold text-dark" id="label_pajak">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2" id="row_pph_summary" style="display: none;">
                                    <span class="text-warning small fw-semibold" id="label_pph_title">Potongan PPh</span>
                                    <span class="fw-bold text-warning" id="label_pph">- 0.00</span>
                                </div>

                                <!-- PANEL POTONGAN PPh (WITHHOLDING TAX) -->
                                <div class="p-3 my-3 rounded-3 po-pph-box" style="background: rgba(245, 158, 11, 0.06); border: 1px solid rgba(245, 158, 11, 0.25);">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-percent text-warning fs-6"></i>
                                        <span class="fw-bold small text-dark">Potongan PPh (Withholding Tax)</span>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill ms-auto extra-small" style="font-size:0.65rem;">Potongan Pembelian</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 position-relative po-pph-col">
                                            @php
                                                $pphList = collect($pphPajaks ?? $pajaks ?? [])->filter(function($p) {
                                                    $n = strtolower($p->nama_pajak ?? '');
                                                    $k = strtolower($p->kode_pajak ?? '');
                                                    return str_contains($n, 'pph') || str_contains($k, 'pph');
                                                });
                                                if ($pphList->isEmpty()) {
                                                    $pphList = collect($pajaks ?? [])->filter(function($p) {
                                                        return !str_contains(strtolower($p->nama_pajak ?? ''), 'ppn');
                                                    });
                                                }
                                            @endphp
                                            <select class="form-select form-select-sm select-pph-pajak" id="pph_pajak_id" name="pph_pajak_id">
                                                <option value="" data-tarif="0">Tanpa PPh (0%)</option>
                                                @foreach($pphList as $pj)
                                                    <option value="{{ $pj->id }}" data-tarif="{{ (float) $pj->tarif }}">{{ $pj->nama_pajak }} ({{ rtrim(rtrim(number_format($pj->tarif, 2, ',', '.'), '0'), ',') }}%)</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="tarif_pph" id="tarif_pph" value="0">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted mb-1" style="font-size: 0.73rem;">Nominal PPh</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text currency-symbol-addon py-0 px-2 small bg-light font-monospace">Rp</span>
                                                <input type="text" class="form-control form-control-sm text-end fw-bold number-format input-pph-nominal bg-light" id="pph_nominal" name="pph_nominal" value="0.00" readonly>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2 position-relative po-pph-col">
                                            <label class="form-label text-muted mb-1 d-flex justify-content-between align-items-center" style="font-size: 0.73rem;">
                                                <span>Akun Utang PPh (COA)</span>
                                                <small class="text-primary fw-normal" style="font-size: 0.68rem;"><i class="bi bi-search me-0.5"></i>Bisa dicari & diubah</small>
                                            </label>
                                            <select class="form-select form-select-sm select-pph-coa" id="pph_coa_id" name="pph_coa_id" disabled>
                                                <option value="">-- Pilih Akun PPh --</option>
                                                @if(isset($coas) && count($coas))
                                                    @foreach($coas as $c)
                                                        <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-dark fw-bold fs-6">Grand Total</span>
                                    <span class="fw-bold text-primary fs-4" id="label_grand_total">0.00</span>
                                </div>

                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-6">
                                        <span class="text-secondary small">Uang Muka (DP):</span>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text currency-symbol-addon py-0 px-2 small bg-light font-monospace">Rp</span>
                                            <input type="text" name="down_payment" id="down_payment" class="form-control form-control-sm text-end number-format" value="0">
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="biaya_lain" id="biaya_lain" value="0">
                                <input type="hidden" id="grand_total_calc" value="0">

                                <hr class="my-2 text-muted opacity-25">

                                <div class="d-flex justify-content-between align-items-center pt-1">
                                    <span class="text-dark fw-bold fs-6">Sisa Pembayaran</span>
                                    <span class="fw-bold text-success fs-5" id="label_sisa_bayar">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-2 mt-2" id="idr_summary_wrapper" style="display: none;">
                                    <span class="text-dark fw-semibold small">Grand Total (Rupiah):</span>
                                    <span class="fw-bold text-dark fs-5" id="label_grand_total_idr">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer po-modal-footer border-0 pb-4 px-4 justify-content-between align-items-center flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 text-nowrap" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button type="button" class="btn btn-outline-warning rounded-pill px-4 text-nowrap d-inline-flex align-items-center gap-1.5" id="btn_save_draft_po">
                            <i class="bi bi-file-earmark-text"></i> Simpan Draft
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 text-nowrap d-inline-flex align-items-center gap-1.5" id="btn_save_po">
                            <i class="bi bi-send-check"></i> Simpan & Ajukan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        let addDiscountType = 'percent';
        let pajakOptionsHtml = `<option value="" data-tarif="0">Non PPN (0%)</option>`;
        @foreach($pajaks as $pj)
            pajakOptionsHtml += `<option value="{{ $pj->id }}" data-tarif="{{ (float)$pj->tarif }}">{{ $pj->nama_pajak }} ({{ rtrim(rtrim(number_format($pj->tarif, 2, ',', '.'), '0'), ',') }}%)</option>`;
        @endforeach

        // Initialize Select2 inside Create Modal
        function initSelect2() {
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalTambahPO')
            });
        }
        initSelect2();

        // ===== BIAYA LAIN-LAIN DYNAMIC ROWS =====
        function addExpenseRow(data = {}, isDefault = false) {
            if (!data || data instanceof Event || data.originalEvent || typeof data !== 'object') {
                data = {};
            }

            let currentExpRows = $('#expense_items_tbody tr').length;
            if (currentExpRows >= 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum 5 Biaya!',
                    text: 'Jumlah rincian biaya lain-lain dalam 1 Purchase Order (PO) dibatasi maksimal 5 biaya.',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                });
                return false;
            }

            let expId = data.master_jenis_biaya_id || '';
            let ket = data.keterangan || '';
            let nom = data.nominal || 0;
            let masukHpp = data.masuk_hpp !== undefined ? data.masuk_hpp : '1';
            let customName = data.nama_jenis_biaya || '';

            let isCustom = (expId === 'custom' || (!expId && customName));
            if (isCustom) expId = 'custom';

            let rowId = 'exp_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            let customStyle = isCustom ? 'display: block;' : 'display: none;';
            let currText = $('#currency_id').find('option:selected').text() || 'IDR';

            let optionsHtml = '<option value="">-- Pilih / Cari Jenis Biaya --</option>';
            optionsHtml += `<option value="custom" ${expId === 'custom' ? 'selected' : ''}>+ Biaya Custom (Ketik Manual)...</option>`;
            @foreach($expenseTypes as $exp)
                optionsHtml += `<option value="{{ $exp->id }}" data-name="{{ $exp->nama_jenis_biaya }}" data-hpp="{{ $exp->masuk_hpp ? 1 : 0 }}" ${expId == '{{ $exp->id }}' ? 'selected' : ''}>{{ $exp->nama_jenis_biaya }}</option>`;
            @endforeach

            let html = `
                <tr id="${rowId}" class="expense-row">
                    <td data-label="Jenis Biaya" style="position: relative;">
                        <select class="form-select form-select-sm exp-jenis-select select2-expense-type rounded-3">
                            ${optionsHtml}
                        </select>
                        <input type="text" class="form-control form-control-sm exp-custom-name-input mt-1.5 rounded-3" placeholder="Ketik nama biaya custom..." value="${customName}" style="${customStyle}">
                    </td>
                    <td data-label="Keterangan">
                        <input type="text" class="form-control form-control-sm exp-ket-input rounded-3" placeholder="Misal: Pengiriman dari Bandung" value="${ket}">
                    </td>
                    <td data-label="Nominal (${currText})">
                        <input type="text" class="form-control form-control-sm exp-nom-input text-end rounded-3 number-format" value="${formatNumber(nom)}">
                    </td>
                    <td data-label="Masuk HPP">
                        <select class="form-select form-select-sm exp-hpp-select rounded-3">
                            <option value="1" ${masukHpp == '1' || masukHpp === true ? 'selected' : ''}>Ya (Masuk HPP)</option>
                            <option value="0" ${masukHpp == '0' || masukHpp === false ? 'selected' : ''}>Tidak (Beban)</option>
                        </select>
                    </td>
                    <td class="text-center" data-label="Aksi">
                        <button type="button" class="btn btn-action-circle text-danger btn-remove-exp" title="Hapus Biaya">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </td>
                </tr>
            `;

            let $row = $(html);
            $('#expense_items_tbody').append($row);

            initSelect2Safely($row.find('.select2-expense-type'), $('#modalTambahPO'), '-- Pilih / Cari Jenis Biaya --');

            calculateTotals();
        }

        $(document).on('change', '#expense_items_tbody .exp-jenis-select', function() {
            let val = $(this).val();
            let selectedOpt = $(this).find('option:selected');
            let isHpp = selectedOpt.data('hpp');
            let row = $(this).closest('tr');
            let customInput = row.find('.exp-custom-name-input');

            try {
                $(this).select2('close');
            } catch (e) {}

            if (val === 'custom') {
                customInput.show();
                setTimeout(() => customInput.focus(), 150);
            } else {
                customInput.hide().val('');
                if (isHpp !== undefined && isHpp !== '') {
                    row.find('.exp-hpp-select').val(isHpp ? '1' : '0');
                }
            }
            calculateTotals();
        });

        $(document).on('input keyup change', '#expense_items_tbody .exp-nom-input, #expense_items_tbody .exp-hpp-select, #expense_items_tbody .exp-custom-name-input', function() {
            calculateTotals();
        });

        $(document).on('click', '#expense_items_tbody .btn-remove-exp', function() {
            let row = $(this).closest('tr');
            row.find('.select2-expense-type').select2('destroy');
            row.remove();
            calculateTotals();
        });

        // Toggle Custom/Auto PO number
        $('#auto_number_toggle').on('change', function () {
            if ($(this).is(':checked')) {
                $('#nomor_po_input').prop('disabled', true).val('').attr('placeholder', 'Draft (Otomatis)');
            } else {
                $('#nomor_po_input').prop('disabled', false).attr('placeholder', 'Contoh: PO-2026-0099').focus();
            }
        });

        // Real-Time Limit Enforcement for Custom Nomor PO
        let poNumAlertShown = false;
        $(document).on('input', '#nomor_po_input', function () {
            let val = $(this).val();
            if (val.length >= 15) {
                if (val.length > 15) {
                    $(this).val(val.substring(0, 15));
                }
                if (!poNumAlertShown) {
                    poNumAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: 'Nomor PO tidak boleh melebihi 15 karakter yang telah ditentukan.',
                        confirmButtonColor: '#0d6efd',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        poNumAlertShown = false;
                    });
                }
            }
        });

        // Real-Time Limit Enforcement for Catatan PO (Max 40 Karakter)
        let ketAlertShown = false;
        $(document).on('input', '#keterangan', function () {
            let val = $(this).val();
            let len = val.length;
            $('#ket_char_count').text(len + ' / 40 Karakter');
            if (len >= 40) {
                $('#ket_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
                if (len > 40) {
                    $(this).val(val.substring(0, 40));
                }
                if (!ketAlertShown) {
                    ketAlertShown = true;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai!',
                        text: 'Catatan / Keterangan PO tidak boleh melebihi 40 karakter.',
                        confirmButtonColor: '#0d6efd',
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    }).then(() => {
                        ketAlertShown = false;
                    });
                }
            } else {
                $('#ket_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
            }
        });

        // Toggle Currency Exchange Rate (Kurs) input
        $('#currency_id').on('change', function () {
            let selectedOpt = $(this).find(':selected');
            let code = selectedOpt.data('code') || selectedOpt.text().trim().split(' ')[0] || 'IDR';
            let symbol = selectedOpt.data('symbol') || (code === 'IDR' ? 'Rp' : code);
            $('.exp-currency-label').text(code);
            $('#modalTambahPO .currency-symbol-addon').text(symbol);

            // Update discount buttons that are in nominal mode
            $('#po_details_tbody tr').each(function() {
                let discType = $(this).find('.discount-type-input').val();
                if (discType === 'nominal') {
                    $(this).find('.btn-disc-type-toggle').text(symbol).attr('title', 'Tipe Diskon: Nominal (' + symbol + ')');
                }
            });

            if (code === 'IDR') {
                $('#kurs_input').val('1').prop('readonly', true).addClass('bg-light');
                $('#idr_summary_wrapper').hide();
            } else {
                $('#kurs_input').prop('readonly', false).removeClass('bg-light');
                if ($('#kurs_input').val() === '1' || $('#kurs_input').val() === '') {
                    $('#kurs_input').val('');
                }
                $('#idr_summary_wrapper').show();
                setTimeout(() => $('#kurs_input').focus(), 150);
            }
            calculateTotals();
        });

        // Calculate totals dynamically on inputs
        $(document).on('input', '#modalTambahPO .qty-input, #modalTambahPO .price-input, #modalTambahPO .discount-val-input, #kurs_input, #biaya_lain, #down_payment', function () {
            calculateTotals();
        });
        $(document).on('change', '#modalTambahPO .select-item-ppn', function () {
            calculateTotals();
        });
        
        $('#pr_display').on('input', function() {
            if ($('#pr_id').val()) {
                $('#pr_id').val('').trigger('change');
            }
        });

        // Calculate totals function (Strictly per-item discount & PPN)
        function calculateTotals() {
            let subtotalGross = 0;
            let totalDiskon = 0;
            let totalPajak = 0;
            let itemCount = $('#po_details_tbody tr.po-row').length;
            let badgeEl = $('#po_item_count_badge');
            badgeEl.text(itemCount + ' / 25 Item');
            if (itemCount >= 25) {
                badgeEl.removeClass('bg-primary bg-opacity-10 text-primary').addClass('bg-danger text-white');
            } else {
                badgeEl.removeClass('bg-danger text-white').addClass('bg-primary bg-opacity-10 text-primary');
            }

            let symbol = $('#currency_id').find(':selected').data('symbol') || 'Rp';

            $('#po_details_tbody tr').each(function () {
                let qty = Math.round((parseFloat($(this).find('.qty-input').val()) || 0) * 10000) / 10000;
                let price = Math.round(unformatNumber($(this).find('.price-input').val()) * 100) / 100;
                let discVal = Math.round((unformatNumber($(this).find('.discount-val-input').val()) || 0) * 100) / 100;
                let discType = $(this).find('.discount-type-input').val() || 'percent';
                
                let discPerUnit = 0;
                if (discType === 'percent') {
                    if (discVal > 100) {
                        discVal = 100;
                        $(this).find('.discount-val-input').val(100);
                    }
                    discPerUnit = price * (discVal / 100);
                } else {
                    if (discVal > price) {
                        discVal = price;
                        $(this).find('.discount-val-input').val(formatNumber(price));
                    }
                    discPerUnit = discVal;
                }
                discPerUnit = Math.round(discPerUnit * 100) / 100;
                $(this).find('.discount-input').val(discPerUnit);

                let faktor = parseFloat($(this).find('.faktor-pengali-val').val()) || 1;
                if (faktor !== 1) {
                    let totalDasar = qty * faktor;
                    $(this).find('.conversion-preview-text').text(formatQty(totalDasar));
                }

                let lineGross = Math.round(qty * price * 100) / 100;
                let lineDiskon = Math.round(qty * discPerUnit * 100) / 100;
                let lineNet = Math.max(0, lineGross - lineDiskon);
                lineNet = Math.round(lineNet * 100) / 100;

                // Per-item PPN calculation
                let $ppnSel = $(this).find('.select-item-ppn option:selected');
                let tarifPpn = parseFloat($ppnSel.attr('data-tarif')) || parseFloat($ppnSel.data('tarif')) || 0;
                let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;
                $(this).find('.item-ppn-nominal-preview').text(linePpn > 0 ? '+ ' + symbol + ' ' + formatNumber(linePpn) : '+ 0');

                subtotalGross += lineGross;
                totalDiskon += lineDiskon;
                totalPajak += linePpn;

                $(this).find('.row-subtotal').text(formatNumber(lineNet));
            });

            subtotalGross = Math.round(subtotalGross * 100) / 100;
            totalDiskon = Math.round(totalDiskon * 100) / 100;
            totalPajak = Math.round(totalPajak * 100) / 100;

            // DPP = Subtotal Barang Gross - Total Diskon Barang
            let dpp = Math.max(0, subtotalGross - totalDiskon);
            dpp = Math.round(dpp * 100) / 100;

            // Calculate Expense Items (Inventory vs Expense)
            let totalBiayaInventory = 0;
            let totalBiayaExpense = 0;
            let totalBiayaLain = 0;

            $('#expense_items_tbody tr').each(function() {
                let nom = unformatNumber($(this).find('.exp-nom-input').val()) || 0;
                let isHpp = $(this).find('.exp-hpp-select').val();
                
                totalBiayaLain += nom;
                if (isHpp === '1' || isHpp === 1 || isHpp === 'true') {
                    totalBiayaInventory += nom;
                } else {
                    totalBiayaExpense += nom;
                }
            });

            $('#biaya_lain').val(totalBiayaLain);

            // Calculate PPh (Withholding Tax - Potongan Pajak Penghasilan)
            let pphPajakId = $('#pph_pajak_id').val();
            let tarifPph = 0;
            let totalPph = 0;

            if (pphPajakId) {
                let $pphOpt = $('#pph_pajak_id').find('option:selected');
                tarifPph = parseFloat($pphOpt.attr('data-tarif')) || parseFloat($pphOpt.data('tarif')) || 0;
                totalPph = Math.round(dpp * (tarifPph / 100) * 100) / 100;
                $('#tarif_pph').val(tarifPph);
                $('#pph_nominal').val(formatNumber(totalPph));

                let pphName = $pphOpt.text().split('(')[0].trim() || 'PPh';
                let tarifTxt = tarifPph > 0 ? ` (${String(tarifPph).replace('.', ',')}%)` : '';
                $('#label_pph_title').text(`Potongan ${pphName}${tarifTxt}`);
                $('#label_pph').text('- ' + symbol + ' ' + formatNumber(totalPph));
                $('#row_pph_summary').show();
            } else {
                $('#tarif_pph').val(0);
                $('#pph_nominal').val('0.00');
                $('#row_pph_summary').hide();
            }

            // Grand Total = DPP + Total PPN + Biaya Lain-Lain - Potongan PPh
            let grandTotal = Math.max(0, dpp + totalPajak + totalBiayaLain - totalPph);
            grandTotal = Math.round(grandTotal * 100) / 100;

            let downPayment = unformatNumber($('#down_payment').val()) || 0;

            // Down Payment Validation (Frontend warning)
            if (downPayment > grandTotal) {
                $('#down_payment').addClass('is-invalid');
                if (!$('#dp_error_msg').length) {
                    $('#down_payment').after('<div class="invalid-feedback" id="dp_error_msg">Uang muka tidak boleh melebihi Grand Total!</div>');
                }
            } else {
                $('#down_payment').removeClass('is-invalid');
                $('#dp_error_msg').remove();
            }

            let sisaBayar = Math.max(0, grandTotal - downPayment);
            sisaBayar = Math.round(sisaBayar * 100) / 100;

            let code = $('#currency_id').find(':selected').data('code') || $('#currency_id').find(':selected').text().trim().split(' ')[0] || 'IDR';
            let kurs = 1;
            if (code !== 'IDR') {
                kurs = unformatNumber($('#kurs_input').val()) || 0;
            }
            let grandTotalIdr = grandTotal * (code === 'IDR' ? 1 : kurs);
            grandTotalIdr = Math.round(grandTotalIdr * 100) / 100;

            // Update Labels
            $('#label_subtotal').text(symbol + ' ' + formatNumber(subtotalGross));
            $('#label_total_diskon').text('- ' + symbol + ' ' + formatNumber(totalDiskon));
            $('#label_biaya_inventory').text(symbol + ' ' + formatNumber(totalBiayaInventory));
            $('#label_biaya_expense').text(symbol + ' ' + formatNumber(totalBiayaExpense));
            $('#label_dpp').text(symbol + ' ' + formatNumber(dpp));
            $('#label_pajak').text(symbol + ' ' + formatNumber(totalPajak));
            $('#label_grand_total').text(symbol + ' ' + formatNumber(grandTotal));
            $('#grand_total_calc').val(grandTotal);
            $('#label_sisa_bayar').text(symbol + ' ' + formatNumber(sisaBayar));
            $('#label_grand_total_idr').text('Rp ' + formatNumber(grandTotalIdr));
        }

        // ===== PULL ITEMS FROM PR SELECTION =====
        window.loadPrItemsTambah = function (prId) {
            $('#po_details_tbody').empty(); // clear existing PO items

            if (!prId) {
                $('#pr_items_card').addClass('d-none');
                $('#pr_items_tbody').empty();
                $('#btn_add_manual_row').prop('disabled', false).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                $('#manual_row_info').addClass('d-none');
                calculateTotals();
                return;
            }

            // Disable manual row addition since PR is chosen
            $('#btn_add_manual_row').prop('disabled', true).removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            $('#manual_row_info').removeClass('d-none');

            $('#pr_items_tbody').html('<tr><td colspan="7" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat daftar item dari PR...</td></tr>');
            $('#pr_items_card').removeClass('d-none');

            $.ajax({
                url: '/purchase-order/pr-details/' + prId,
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    let items = res.items || [];
                    let html = '';

                    if (items.length === 0) {
                        html = '<tr><td colspan="7" class="text-center py-3 text-muted">Seluruh barang di PR ini sudah dibuatkan PO atau tidak memiliki item aktif.</td></tr>';
                    } else {
                        items.forEach(function (item) {
                            let namaClean = escapeHtml(item.nama_barang || '');
                            let kodeClean = escapeHtml(item.kode_barang || '');
                            let satuanClean = escapeHtml(item.satuan || '-');
                            let tipeClean = escapeHtml(item.tipe_item || 'Stok');
                            let ketClean = item.keterangan ? escapeHtml(item.keterangan) : '-';
                            let fotosJson = escapeHtml(JSON.stringify(item.fotos || []));
                            let fotoUrl = item.foto ? escapeHtml(item.foto) : '';

                            let satuanId = item.satuan_id || '';
                            let satuanDasar = item.satuan_dasar || item.satuan || '-';
                            let faktorPengali = parseFloat(item.faktor_pengali) || 1;

                            let satuanDisplay = satuanClean;
                            if (faktorPengali !== 1) {
                                satuanDisplay = `<span class="badge bg-light text-secondary border px-2 py-0.5">${satuanClean}</span><small class="text-muted d-block" style="font-size:0.7rem;">(x${faktorPengali} ${escapeHtml(satuanDasar)})</small>`;
                            }

                            html += `
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="chk-pr-item" 
                                            data-id="${item.barang_id}" 
                                            data-pr-detail-id="${item.pr_detail_id}"
                                            data-nama="${namaClean}"
                                            data-kode="${kodeClean}"
                                            data-satuan-id="${satuanId}"
                                            data-satuan="${satuanClean}"
                                            data-satuan-dasar="${escapeHtml(satuanDasar)}"
                                            data-faktor-pengali="${faktorPengali}"
                                            data-remaining="${item.qty_remaining}"
                                            data-harga="${item.harga_estimasi || 0}"
                                            data-foto="${fotoUrl}"
                                            data-fotos='${fotosJson}'
                                            data-tipe="${tipeClean}">
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">${namaClean}</span>
                                        <span class="ms-1 badge small px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; ${item.tipe_item === 'Stok' ? 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;' : (item.tipe_item === 'Aset' ? 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;' : 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;') }">${tipeClean.toUpperCase()}</span>
                                        <br>
                                        <small class="text-muted">${kodeClean}</small>
                                    </td>
                                    <td class="text-center">${satuanDisplay}</td>
                                    <td class="text-center fw-medium">${parseFloat(item.qty_pr || 0)}</td>
                                    <td class="text-center text-primary fw-bold">${parseFloat(item.qty_remaining || 0)}</td>
                                    <td class="text-end fw-semibold text-success">Rp ${formatNumber(item.harga_estimasi || 0)}</td>
                                    <td class="text-secondary">${ketClean}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#pr_items_tbody').html(html);
                    $('#pr_items_card').removeClass('d-none');
                    if (items.length > 0) {
                        $('#check_all_pr_items').prop('checked', true).trigger('change');
                    } else {
                        $('#check_all_pr_items').prop('checked', false);
                    }
                    calculateTotals();
                },
                error: function (err) {
                    $('#pr_items_tbody').html('<tr><td colspan="7" class="text-center py-3 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat rincian item PR.</td></tr>');
                }
            });
        };

        $(document).on('change', '#pr_id', function () {
            window.loadPrItemsTambah($(this).val());
        });

        // Master PR items Select-All
        $(document).on('change', '#check_all_pr_items', function () {
            let checked = $(this).is(':checked');
            $('.chk-pr-item').prop('checked', checked).trigger('change');
        });

        // Live calculation for alternate unit change in manual row
        $(document).on('change', '.satuan-select-input', function () {
            let tr = $(this).closest('tr');
            let selectedOpt = $(this).find('option:selected');
            let satuanId = $(this).val();
            let factor = parseFloat(selectedOpt.data('factor')) || 1;
            tr.find('.satuan-id-val').val(satuanId);
            tr.find('.faktor-pengali-val').val(factor);
            calculateTotals();
        });

        // Append item from PR list to PO details when checked
        $(document).on('change', '.chk-pr-item', function () {
            let barangId = $(this).attr('data-id') || $(this).data('id');
            let prDetailId = $(this).attr('data-pr-detail-id') || $(this).data('pr-detail-id') || $(this).data('prDetailId');
            let nama = $(this).attr('data-nama') || $(this).data('nama');
            let satuanId = $(this).attr('data-satuan-id') || $(this).data('satuan-id') || '';
            let satuan = $(this).attr('data-satuan') || $(this).data('satuan');
            let satuanDasar = $(this).attr('data-satuan-dasar') || $(this).data('satuan-dasar') || satuan;
            let faktorPengali = parseFloat($(this).attr('data-faktor-pengali') || $(this).data('faktor-pengali') || 1);
            let remaining = parseFloat($(this).attr('data-remaining') || $(this).data('remaining') || 0);
            let harga = parseFloat($(this).attr('data-harga') || $(this).data('harga') || 0);
            let foto = $(this).attr('data-foto') || $(this).data('foto') || '';
            let fotosData = $(this).attr('data-fotos') || $(this).data('fotos') || [];
            let tipe = $(this).attr('data-tipe') || $(this).data('tipe') || 'Stok';

            let existingRow = $(`#po_details_tbody tr[data-pr-detail-id="${prDetailId}"]`);

            if ($(this).is(':checked')) {
                let currentRows = $('#po_details_tbody tr.po-row').length;
                if (existingRow.length === 0 && currentRows >= 25) {
                    $(this).prop('checked', false);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Maksimum 25 Item!',
                        text: 'Jumlah item barang dalam 1 Purchase Order (PO) dibatasi maksimal 25 item.',
                        confirmButtonColor: '#0d6efd',
                        didOpen: () => {
                            const c = document.querySelector('.swal2-container');
                            if (c) c.style.setProperty('z-index', '999999', 'important');
                        }
                    });
                    return false;
                }
                if (existingRow.length === 0) {
                    let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
                    if (tipe === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
                    else if (tipe === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';

                    let konversiBadge = '';
                    if (faktorPengali !== 1) {
                        let totalDasar = remaining * faktorPengali;
                        let fmtDasar = formatQty(totalDasar);
                        konversiBadge = `<small class="text-muted d-block mt-1" style="font-size:0.75rem;">Setara <span class="conversion-preview-text">${fmtDasar}</span> ${escapeHtml(satuanDasar)}</small>`;
                    }

                    let rowHtml = `
                        <tr data-pr-detail-id="${prDetailId}" class="po-row">
                            <td data-label="Nama Barang">
                                <input type="hidden" class="barang-id-val" value="${barangId}">
                                <input type="hidden" class="pr-detail-id-val" value="${prDetailId}">
                                <input type="hidden" class="satuan-id-val" value="${satuanId}">
                                <input type="hidden" class="faktor-pengali-val" value="${faktorPengali}">
                                <input type="hidden" class="satuan-dasar-val" value="${escapeHtml(satuanDasar)}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="product-img-wrapper cursor-pointer" data-fotos='${escapeHtml(typeof fotosData === "string" ? fotosData : JSON.stringify(fotosData))}' style="cursor: pointer;">
                                        ${foto ? `<img src="${escapeHtml(foto)}" alt="${escapeHtml(nama)}" class="product-img">` : `<i class="bi bi-image text-muted"></i>`}
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-dark">${escapeHtml(nama)}</span>
                                        <div class="mt-1">
                                            <span class="badge small px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; ${bg}">${escapeHtml(tipe.toUpperCase())}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" data-label="Satuan">
                                <span class="badge bg-light text-secondary border px-2 py-1">${escapeHtml(satuan)}</span>
                                ${konversiBadge}
                            </td>
                            <td data-label="Jumlah Qty">
                                <input type="number" class="form-control form-control-sm text-center qty-input" value="${parseFloat(remaining)}" min="0.0001" max="${remaining}" step="0.0001" required>
                                <small class="text-primary fw-semibold text-center d-block">Sisa: ${parseFloat(remaining)}</small>
                            </td>
                            <td data-label="Harga Satuan">
                                <input type="text" class="form-control form-control-sm text-end number-format price-input" value="${formatNumber(harga)}" required>
                            </td>
                            <td data-label="Diskon Item">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm text-end discount-val-input number-format" value="0" min="0">
                                    <button type="button" class="btn btn-outline-secondary btn-disc-type-toggle px-2" title="Tipe Diskon: Persen (%)" style="font-size: 0.75rem; font-weight: 700; min-width: 32px;">
                                        %
                                    </button>
                                    <input type="hidden" class="discount-type-input" value="percent">
                                    <input type="hidden" class="discount-input" value="0">
                                </div>
                            </td>
                            <td class="text-end fw-bold text-dark" data-label="Subtotal">
                                <span class="row-subtotal">${formatNumber(remaining * harga)}</span>
                            </td>
                            <td data-label="PPN">
                                <select class="form-select form-select-sm select-item-ppn">
                                    ${pajakOptionsHtml}
                                </select>
                                <small class="text-muted d-block text-end item-ppn-nominal-preview mt-0.5" style="font-size: 0.72rem;">+ 0</small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-row-pr" data-pr-detail-id="${prDetailId}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#po_details_tbody').append(rowHtml);
                    let newRow = $(`#po_details_tbody tr[data-pr-detail-id="${prDetailId}"]`);
                    window.initPajakSelect2(newRow.find('.select-item-ppn'), $('#modalTambahPO'));
                }
            } else {
                if (existingRow.length > 0) {
                    existingRow.find('.select-item-ppn').select2('destroy');
                    existingRow.remove();
                }
            }
            $('#po_item_count_badge').text($('#po_details_tbody tr.po-row').length + ' / 25 Item');
            calculateTotals();
        });

        // Remove a PO row synced to PR
        $(document).on('click', '.btn-remove-row-pr', function () {
            let prDetailId = $(this).attr('data-pr-detail-id') || $(this).data('pr-detail-id');
            $(`.chk-pr-item[data-pr-detail-id="${prDetailId}"]`).prop('checked', false);
            let rowToDel = $(`#po_details_tbody tr[data-pr-detail-id="${prDetailId}"]`);
            rowToDel.find('.select-item-ppn').select2('destroy');
            rowToDel.remove();
            $('#po_item_count_badge').text($('#po_details_tbody tr.po-row').length + ' / 25 Item');
            calculateTotals();
        });

        // ===== ADD EXPENSE ROW BUTTON =====
        $(document).on('click', '#btn_add_expense_row', function () {
            let currentExpRows = $('#expense_items_tbody tr').length;
            if (currentExpRows >= 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum 5 Biaya!',
                    text: 'Jumlah rincian biaya lain-lain dalam 1 Purchase Order (PO) dibatasi maksimal 5 biaya.',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                });
                return false;
            }

            if (!$('#supplier_id').val()) {
                Swal.fire({
                    title: 'Supplier Belum Dipilih!',
                    text: 'Silakan cari & pilih Supplier terlebih dahulu sebelum menambah biaya lain-lain.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Supplier Sekarang',
                    confirmButtonColor: '#0d6efd'
                }).then((res) => {
                    if (res.isConfirmed) {
                        $('#btnOpenSupplierPicker').trigger('click');
                    }
                });
                return false;
            }
            addExpenseRow();
        });

        // ===== ADD MANUAL ITEM ROW =====
        $(document).on('click', '#btn_add_manual_row', function (e) {
            e.preventDefault();
            if (!$('#supplier_id').val()) {
                Swal.fire({
                    title: 'Supplier Belum Dipilih!',
                    text: 'Silakan cari & pilih Supplier terlebih dahulu sebelum menambah item barang.',
                    icon: 'warning',
                    confirmButtonText: 'Pilih Supplier Sekarang',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        $('#btnOpenSupplierPicker').trigger('click');
                    }
                });
                return false;
            }

            let currentRows = $('#po_details_tbody tr.po-row').length;
            if (currentRows >= 25) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum 25 Item!',
                    text: 'Jumlah item barang dalam 1 Purchase Order (PO) dibatasi maksimal 25 item.',
                    confirmButtonColor: '#0d6efd',
                    didOpen: () => {
                        const c = document.querySelector('.swal2-container');
                        if (c) c.style.setProperty('z-index', '999999', 'important');
                    }
                });
                return false;
            }
            let rowId = 'manual_' + Date.now();
            let rowHtml = `
                <tr class="po-row" data-row-id="${rowId}">
                    <td data-label="Nama Barang">
                        <div class="d-flex align-items-center gap-2">
                            <div class="product-img-wrapper" id="img_wrapper_${rowId}">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="input-group input-group-sm select-product-container">
                                    <input type="hidden" class="select-barang-manual select-product-input" required>
                                    <input type="text" class="form-control form-control-custom label-product-name" placeholder="Pilih barang..." readonly required style="cursor: pointer;">
                                    <button type="button" class="btn btn-outline-primary btn-search-barang-modal" title="Cari Barang"><i class="bi bi-search"></i></button>
                                </div>
                                <div class="mt-1 badge-container">
                                    <span class="badge small px-2 py-0.5 rounded-pill label-tipe-item d-none" style="font-size: 0.65rem;"></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center label-satuan-manual" data-label="Satuan">-</td>
                    <td data-label="Jumlah Qty">
                        <input type="number" class="form-control form-control-sm text-center qty-input" value="1" min="0.01" step="0.01" required>
                    </td>
                    <td data-label="Harga Satuan">
                        <input type="text" class="form-control form-control-sm text-end number-format price-input" value="0,00" required>
                    </td>
                    <td data-label="Diskon Item">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm text-end discount-val-input number-format" value="0" min="0">
                            <button type="button" class="btn btn-outline-secondary btn-disc-type-toggle px-2" title="Tipe Diskon: Persen (%)" style="font-size: 0.75rem; font-weight: 700; min-width: 32px;">
                                %
                            </button>
                            <input type="hidden" class="discount-type-input" value="percent">
                            <input type="hidden" class="discount-input" value="0">
                        </div>
                    </td>
                    <td class="text-end fw-bold text-dark" data-label="Subtotal">
                        <span class="row-subtotal">0.00</span>
                    </td>
                    <td data-label="PPN">
                        <select class="form-select form-select-sm select-item-ppn">
                            ${pajakOptionsHtml}
                        </select>
                        <small class="text-muted d-block text-end item-ppn-nominal-preview mt-0.5" style="font-size: 0.72rem;">+ 0</small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-remove-row-manual">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#po_details_tbody').append(rowHtml);
            window.initPajakSelect2($('#po_details_tbody tr:last .select-item-ppn'), $('#modalTambahPO'));
            $('#po_item_count_badge').text($('#po_details_tbody tr.po-row').length + ' / 25 Item');
            calculateTotals();
        });

        // Toggle per-item discount type (% / Nominal)
        $(document).on('click', '#modalTambahPO .btn-disc-type-toggle', function () {
            let tr = $(this).closest('tr');
            let currentType = tr.find('.discount-type-input').val() || 'percent';
            let newType = currentType === 'percent' ? 'nominal' : 'percent';
            let selectedOpt = $('#currency_id').find(':selected');
            let code = selectedOpt.text().trim().split(' ')[0] || 'IDR';
            let symbol = selectedOpt.data('symbol') || (code === 'IDR' ? 'Rp' : code);
            
            tr.find('.discount-type-input').val(newType);
            $(this).text(newType === 'percent' ? '%' : symbol);
            $(this).attr('title', 'Tipe Diskon: ' + (newType === 'percent' ? 'Persen (%)' : 'Nominal (' + symbol + ')'));
            
            calculateTotals();
        });

        $(document).on('input change', '#modalTambahPO .discount-val-input', function () {
            calculateTotals();
        });

        // Remove manual row
        $(document).on('click', '.btn-remove-row-manual', function () {
            let tr = $(this).closest('tr');
            tr.find('.select-item-ppn').select2('destroy');
            tr.remove();
            $('#po_item_count_badge').text($('#po_details_tbody tr.po-row').length + ' / 25 Item');
            calculateTotals();
        });

        // Reset modals on closing
        $('#modalTambahPO').on('hidden.bs.modal', function () {
            $('#formTambahPO')[0].reset();
            $('#po_details_tbody').empty();
            $('#pr_items_tbody').empty();
            $('#pr_items_card').addClass('d-none');
            $('#expense_items_tbody').empty();

            // Reset popup picker inputs + hidden values
            $('#supplier_id').val('');
            $('#supplier_display').val('');
            $('#btnClearSupplier').addClass('d-none');
            $('#pr_id').val('');
            $('#pr_display').val('');
            $('#btnClearPR').addClass('d-none');
            $('#tender_id').val('');
            $('#tender_display').val('');
            $('#btnClearTender').addClass('d-none');

            // Reset currency selector back to default IDR option
            let defaultCurrId = $('#currency_id option').filter(function() {
                return $(this).text().includes('IDR');
            }).val() || '';
            
            $('#currency_id').val(defaultCurrId).trigger('change');
            $('#payment_term_id').val('');
            $('#payment_term_display').val('Cash / COD');
            $('#payment_method_id').val('');
            $('#payment_method_display').val('Belum Ditentukan');
            $('#tanggal_pengiriman').val('');
            $('#kurs_input').val('1').prop('readonly', true);
            $('#auto_number_toggle').prop('checked', true).trigger('change');
            $('#biaya_lain').val(0);
            $('#deskripsi_biaya_lain').val('');
            $('#wrapper_deskripsi_biaya_lain').hide();
            $('#alokasi_biaya_lain').val('Beban');
            $('#wrapper_alokasi_biaya_lain').hide();
            $('#down_payment').val(0);
            $('#label_sisa_bayar').text('0.00');
            $('#btn_add_manual_row').prop('disabled', false).removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            $('#manual_row_info').addClass('d-none');
            addDiscountType = 'percent';
            $('#po_item_count_badge').text('0 / 25 Item');
            calculateTotals();
        });

        $(document).on('click', '#btnClearSupplier', function() {
            $('#supplier_id').val('').trigger('change');
            $('#supplier_display').val('');
            $(this).addClass('d-none');
        });

        // ===== SUBMIT FORM PO =====
        let isDraftSubmission = false;

        $('#btn_save_draft_po').on('click', function () {
            isDraftSubmission = true;
            $('#formTambahPO').submit();
        });

        $('#btn_save_po').on('click', function () {
            isDraftSubmission = false;
        });

        $('#formTambahPO').on('submit', function (e) {
            e.preventDefault();
            let submitBtn = isDraftSubmission ? $('#btn_save_draft_po') : $('#btn_save_po');
            let rows = $('#po_details_tbody tr');
            if (rows.length === 0) {
                Swal.fire('Gagal!', 'Minimal harus ada 1 item barang yang dipesan!', 'warning');
                return;
            }

            let items = [];
            let hasError = false;
            let zeroQtyError = false;
            let zeroPriceError = false;

            rows.each(function () {
                let barangId = '';
                let prDetailId = '';

                if ($(this).attr('data-pr-detail-id')) {
                    barangId = $(this).find('.barang-id-val').val();
                    prDetailId = $(this).find('.pr-detail-id-val').val();
                } else {
                    barangId = $(this).find('.select-barang-manual').val();
                }

                if (!barangId) {
                    hasError = true;
                    return false;
                }

                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                let price = unformatNumber($(this).find('.price-input').val());

                if (qty <= 0) {
                    zeroQtyError = true;
                    return false;
                }

                if (price <= 0) {
                    zeroPriceError = true;
                    return false;
                }

                let satuanId = $(this).find('.satuan-id-val').val() || null;
                let faktorPengali = parseFloat($(this).find('.faktor-pengali-val').val()) || 1;

                let discType = $(this).find('.discount-type-input').val() || 'percent';
                let discVal = unformatNumber($(this).find('.discount-val-input').val()) || 0;
                let discPerUnit = parseFloat($(this).find('.discount-input').val()) || 0;
                let discPersen = discType === 'percent' ? discVal : 0;

                let ppnPajakId = $(this).find('.select-item-ppn').val() || null;
                let tarifPpn = parseFloat($(this).find('.select-item-ppn option:selected').data('tarif')) || 0;
                
                let lineGross = qty * price;
                let lineDiskon = qty * discPerUnit;
                let lineNet = Math.max(0, lineGross - lineDiskon);
                let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;

                items.push({
                    barang_id: barangId,
                    pr_detail_id: prDetailId,
                    satuan_id: satuanId,
                    faktor_pengali: faktorPengali,
                    jumlah: qty,
                    harga_satuan: price,
                    diskon_item: discPerUnit,
                    diskon_persen: discPersen,
                    diskon_tipe: discType,
                    ppn_pajak_id: ppnPajakId,
                    tarif_ppn: tarifPpn,
                    ppn_nominal: linePpn,
                    keterangan: ''
                });
            });

            if (hasError) {
                Swal.fire('Gagal!', 'Pastikan seluruh barang di baris detail sudah dipilih!', 'warning');
                return;
            }

            if (zeroQtyError) {
                Swal.fire('Gagal!', 'Jumlah (Qty) setiap barang harus lebih besar dari 0!', 'warning');
                return;
            }

            if (zeroPriceError) {
                Swal.fire('Gagal!', 'Harga satuan setiap barang tidak boleh 0 atau kosong!', 'warning');
                return;
            }

            let isAutoNumber = $('#auto_number_toggle').is(':checked');
            let customPoNumber = ($('#nomor_po_input').val() || '').trim();
            if (!isAutoNumber && !customPoNumber) {
                Swal.fire('Gagal!', 'Silakan masukkan Nomor PO manual atau centang opsi Auto!', 'warning');
                return;
            }

            // Kurs validation for foreign currency
            let currCode = $('#currency_id').find(':selected').data('code') || $('#currency_id').find(':selected').text().trim().split(' ')[0] || 'IDR';
            let kursVal = unformatNumber($('#kurs_input').val());
            if (currCode !== 'IDR' && kursVal <= 0) {
                Swal.fire('Gagal!', 'Nilai Kurs untuk mata uang asing (' + currCode + ') harus lebih besar dari 0!', 'warning');
                $('#kurs_input').focus();
                return;
            }

            let downPayment = unformatNumber($('#down_payment').val()) || 0;
            let grandTotal = parseFloat($('#grand_total_calc').val()) || 0;
            if (downPayment > grandTotal) {
                Swal.fire('Gagal!', 'Nilai Uang Muka (DP) tidak boleh melebihi Grand Total!', 'warning');
                return;
            }

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            let expense_items = [];
            $('#expense_items_tbody tr').each(function() {
                let jId = $(this).find('.exp-jenis-id-val').val() || $(this).find('.exp-jenis-select').val() || '';
                let customName = $(this).find('.exp-custom-name-input').val() || '';
                let selectedOpt = $(this).find('.exp-jenis-select option:selected');
                let optName = selectedOpt.data('name') || selectedOpt.text().trim();
                let namaJenis = jId === 'custom' ? customName : (optName || $(this).find('.exp-jenis-name-val').val() || $(this).find('.exp-jenis-display').val() || '');
                let ket = $(this).find('.exp-ket-input').val() || '';
                let nom = unformatNumber($(this).find('.exp-nom-input').val()) || 0;
                let hpp = $(this).find('.exp-hpp-select').val();

                if ((jId && jId !== '') || customName.trim() !== '' || nom > 0) {
                    expense_items.push({
                        master_jenis_biaya_id: (jId && jId !== 'custom') ? jId : null,
                        nama_jenis_biaya: namaJenis,
                        keterangan: ket,
                        nominal: nom,
                        masuk_hpp: hpp
                    });
                }
            });

            let payload = {
                tanggal_po: $('#tanggal_po').val(),
                supplier_id: $('#supplier_id').val(),
                currency_id: $('#currency_id').val(),
                kurs: unformatNumber($('#kurs_input').val()),
                pajak_id: null,
                payment_term_id: $('#payment_term_id').val(),
                payment_method_id: $('#payment_method_id').val(),
                tanggal_pengiriman: $('#tanggal_pengiriman').val(),
                pr_id: $('#pr_id').val(),
                tender_id: $('#tender_id').val(),
                diskon_persen: 0,
                diskon_nominal: 0,
                pph_pajak_id: $('#pph_pajak_id').val() || null,
                tarif_pph: parseFloat($('#tarif_pph').val()) || 0,
                pph_nominal: unformatNumber($('#pph_nominal').val()) || 0,
                pph_coa_id: $('#pph_coa_id').val() || null,
                biaya_lain: unformatNumber($('#biaya_lain').val()) || 0,
                expense_items: expense_items,
                deskripsi_biaya_lain: $('#deskripsi_biaya_lain').val(),
                alokasi_biaya_lain: $('#alokasi_biaya_lain').val(),
                down_payment: downPayment,
                keterangan: $('#keterangan').val(),
                auto_number: $('#auto_number_toggle').is(':checked') ? 1 : 0,
                nomor_po: $('#nomor_po_input').val(),
                is_draft: isDraftSubmission ? 1 : 0,
                items: items
            };

            $.ajax({
                url: "{{ route('po.store') }}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    $('#modalTambahPO').modal('hide');
                    $('#btn_save_po, #btn_save_draft_po').prop('disabled', false);
                    $('#btn_save_po').html('<i class="bi bi-send-check me-1"></i> Simpan & Ajukan');
                    $('#btn_save_draft_po').html('<i class="bi bi-file-earmark-text me-1"></i> Simpan Draft');
                    isDraftSubmission = false;
                    if (typeof window.reloadTablePO === 'function') {
                        window.reloadTablePO();
                    }
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                },
                error: function (err) {
                    $('#btn_save_po, #btn_save_draft_po').prop('disabled', false);
                    $('#btn_save_po').html('<i class="bi bi-send-check me-1"></i> Simpan & Ajukan');
                    $('#btn_save_draft_po').html('<i class="bi bi-file-earmark-text me-1"></i> Simpan Draft');
                    isDraftSubmission = false;
                    let errorMsg = 'Gagal menyimpan Purchase Order.';
                    if (err.status === 403) {
                        errorMsg = '<b>Akses Ditolak:</b> Akun Anda tidak memiliki izin untuk membuat Purchase Order.';
                    } else if (err.status === 422 && err.responseJSON) {
                        if (err.responseJSON.errors) {
                            errorMsg = '<b>Validasi Gagal:</b><br>';
                            $.each(err.responseJSON.errors, function (key, val) {
                                errorMsg += '• ' + val[0] + '<br>';
                            });
                        } else if (err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                    }
                    Swal.fire({ title: 'Gagal!', html: errorMsg, icon: 'error' });
                }
            });
        });

        // Inisialisasi Select2 Jenis PPh & COA PPh saat modal dibuka
        if (typeof window.initPphPajakSelect2 === 'function') {
            window.initPphPajakSelect2($('#pph_pajak_id'), $('#modalTambahPO'));
        }
        if (typeof window.initCoaSelect2 === 'function') {
            window.initCoaSelect2($('#pph_coa_id'), $('#modalTambahPO'));
        }

        // Handler perubahan dropdown PPh
        $(document).on('change', '#pph_pajak_id', function() {
            let pphId = $(this).val();
            let $coa = $('#pph_coa_id');

            if (!pphId) {
                $coa.val('').prop('disabled', true).trigger('change.select2');
            } else {
                $coa.prop('disabled', false);
                // Auto-select COA jika belum dipilih user
                if (!$coa.data('userPicked') || !$coa.val()) {
                    let autoCoaId = (window.pphCoaMap && window.pphCoaMap[pphId]) ? window.pphCoaMap[pphId] : window.defaultPphCoaId;
                    if (autoCoaId) {
                        $coa.val(String(autoCoaId)).trigger('change.select2');
                    }
                }
            }
            calculateTotals();
        });

        // Tandai jika user secara manual memilih akun COA
        $(document).on('select2:select', '#pph_coa_id', function() {
            $(this).data('userPicked', true);
        });

        $('#modalTambahPO').on('shown.bs.modal', function () {
            if (typeof window.initPphPajakSelect2 === 'function') {
                window.initPphPajakSelect2($('#pph_pajak_id'), $('#modalTambahPO'));
            }
            if (typeof window.initCoaSelect2 === 'function') {
                window.initCoaSelect2($('#pph_coa_id'), $('#modalTambahPO'));
            }
        });

        $('#modalTambahPO').on('hidden.bs.modal show.bs.modal', function () {
            $('#btn_save_po').html('<i class="bi bi-send-check me-1"></i> Simpan & Ajukan');
            $('#btn_save_draft_po').html('<i class="bi bi-file-earmark-text me-1"></i> Simpan Draft');
            isDraftSubmission = false;
        });

        $('#modalTambahPO').on('hidden.bs.modal', function () {
            // Reset PPh State
            $('#pph_pajak_id').val('').trigger('change.select2');
            $('#tarif_pph').val(0);
            $('#pph_nominal').val('0.00');
            $('#row_pph_summary').hide();
            $('#pph_coa_id').val('').data('userPicked', false).prop('disabled', true).trigger('change.select2');
        });
    });
</script>
@endpush
