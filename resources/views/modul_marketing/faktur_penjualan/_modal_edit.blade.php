<!-- Modal Edit Invoice Penjualan -->
<div class="modal fade" id="modalEditInvoice" tabindex="-1" aria-labelledby="modalEditInvoiceLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable faktur-modal-dialog" style="max-width: 95vw; width: 95vw;">
        <form id="formEditInvoice" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 bg-body">
            @csrf
            <input type="hidden" name="invoice_id" id="edit_invoice_id">
            <input type="hidden" name="action_type" id="edit_invoice_action_type" value="post">
            <input type="hidden" name="sales_order_id" id="edit_sales_order_id">
            <input type="hidden" name="delivery_order_id" id="edit_delivery_order_id">

            <div class="modal-header border-bottom py-2.5 py-md-3 px-3 px-md-4 bg-body-secondary d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-md-5" id="modalEditInvoiceLabel">Edit Faktur Penjualan</h5>
                        <p class="text-body-secondary small mb-0 font-monospace d-none d-sm-block" style="font-size: 0.78rem;">Perbarui data faktur penjualan draf atau rincian transaksi.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                <!-- Header Info Row -->
                <div class="card border border-body-tertiary bg-body-tertiary rounded-4 p-3 p-md-3.5 mb-3 mb-md-4 shadow-xs">
                    <div class="row g-2 g-md-3">
                        <!-- Nomor Faktur Penjualan -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-body-emphasis mb-1">Nomor Faktur</label>
                            <input type="text" name="nomor_faktur" id="edit_nomor_faktur" class="form-control form-control-sm rounded-3 font-monospace bg-body-secondary" readonly>
                        </div>

                        <!-- Nomor Faktur Pajak (NSFP) -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-body-emphasis d-flex justify-content-between mb-1">
                                <span>No. Faktur Pajak</span>
                                <small class="text-muted font-monospace" style="font-size: 0.7rem;">Opsional</small>
                            </label>
                            <input type="text" name="nomor_faktur_pajak" id="edit_nomor_faktur_pajak" class="form-control form-control-sm rounded-3 font-monospace" placeholder="010.000-26.00000001">
                        </div>

                        <!-- Tanggal Faktur -->
                        <div class="col-6 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Faktur <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_faktur" id="edit_tanggal_faktur" class="form-control form-control-sm rounded-3 font-monospace" required>
                        </div>

                        <!-- Tanggal Jatuh Tempo -->
                        <div class="col-6 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Jatuh Tempo</label>
                            <input type="date" name="tanggal_jatuh_tempo" id="edit_tanggal_jatuh_tempo" class="form-control form-control-sm rounded-3 font-monospace">
                        </div>

                        <!-- Customer (Readonly / Terkunci di Edit) -->
                        <div class="col-12 col-sm-6 col-lg-4">
                            <label class="form-label small fw-bold text-body-emphasis d-flex justify-content-between align-items-center mb-1">
                                <span>Customer <span class="text-danger">*</span></span>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.65rem;"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                            </label>
                            <input type="hidden" name="customer_id" id="edit_customer_id" required>
                            <div class="input-group input-group-sm">
                                <input type="text" id="edit_customer_name_display" class="form-control rounded-3 bg-body-secondary font-monospace fw-semibold text-body-emphasis" readonly placeholder="Nama Customer">
                                <span class="input-group-text bg-body-secondary text-muted"><i class="bi bi-lock-fill"></i></span>
                            </div>
                        </div>

                        <!-- Ref SO & DO Info Badge -->
                        <div class="col-12 col-sm-6 col-lg-5">
                            <label class="form-label small fw-bold text-success d-flex align-items-center justify-content-between mb-1">
                                <span><i class="bi bi-link-45deg me-1"></i> Referensi Dokumen (SO / DO)</span>
                            </label>
                            <div class="p-1.5 px-2 rounded-3 bg-body-secondary border d-flex align-items-center justify-content-between" id="edit_ref_doc_badge" style="min-height: 31px;">
                                <span class="small text-body-secondary font-monospace" style="font-size: 0.75rem;">Tidak terhubung ke SO / DO</span>
                            </div>
                        </div>

                        <!-- Tender / Proyek Terkait -->
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label small fw-bold text-body-emphasis d-flex align-items-center justify-content-between mb-1">
                                <span><i class="bi bi-briefcase me-1 text-info"></i> Tender / Proyek</span>
                                <small class="text-muted font-monospace" style="font-size:0.7rem;">Opsional</small>
                            </label>
                            <select name="tender_id" id="edit_tender_id" class="form-select rounded-3">
                                <option value="">-- Pilih Tender / Proyek --</option>
                                @if(isset($tenders))
                                    @foreach($tenders as $tend)
                                        <option value="{{ $tend->id }}">
                                            {{ $tend->nama_tender ?? 'Tender #'.$tend->id }}{{ !empty($tend->nomor_kontrak) && $tend->nomor_kontrak !== '-' ? ' ('.$tend->nomor_kontrak.')' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Upload File Pendukung -->
                        <div class="col-12 col-sm-6 col-lg-6">
                            <label class="form-label small fw-bold text-body-emphasis d-flex align-items-center justify-content-between mb-1">
                                <span><i class="bi bi-paperclip text-primary"></i> Upload Dokumen / Foto Pendukung</span>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.68rem;"><i class="bi bi-shield-check text-success me-0.5"></i>Maks 10 MB</span>
                            </label>
                            <input type="file" name="file_pendukung" id="edit_file_pendukung" class="form-control form-control-sm rounded-3 input-validate-filesize" accept=".pdf,.jpg,.jpeg,.png,.webp" data-max-size="10485760">
                            <div class="form-text text-body-secondary small mt-1" style="font-size: 0.72rem;">
                                <i class="bi bi-info-circle text-primary me-1"></i>Format: PDF, JPG, PNG, WEBP (Maks. 10 MB). Foto otomatis dikompres ke ukuran <strong>KB</strong> saat disimpan agar hemat server.
                            </div>
                            <div id="edit_file_preview_info" class="mt-1 d-none"></div>
                            <div id="edit_current_file_container" class="mt-1"></div>
                        </div>

                        <!-- Payment Term & Due Date -->
                        <div class="col-12 col-sm-6 col-lg-6">
                            <label class="form-label small fw-bold text-body-emphasis mb-1">Syarat Pembayaran</label>
                            <select name="payment_term_id" id="edit_payment_term_id" class="form-select rounded-3">
                                <option value="">-- Cash / COD --</option>
                                @foreach($paymentTerms as $term)
                                    <option value="{{ $term->id }}" data-days="{{ $term->jumlah_hari }}">{{ $term->nama_term }} ({{ $term->jumlah_hari }} hari)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden Currency ID & Kurs (Fokus Rupiah / IDR) -->
                        <input type="hidden" name="currency_id" id="edit_currency_id" value="1">
                        <input type="hidden" name="kurs" id="edit_kurs" value="1.0000">
                    </div>
                </div>

                <!-- Items Table Header & Action -->
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2.5">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex">
                            <i class="bi bi-box-seam fs-6"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold text-body-emphasis mb-0" style="font-size: 0.95rem;">Rincian Barang / Jasa Penjualan</h6>
                            <span class="text-body-secondary small d-none d-md-inline" style="font-size: 0.75rem;">Perbarui harga, diskon, atau kuantitas</span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 text-nowrap" id="btn_edit_add_item_row">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <div class="card border border-body-tertiary rounded-4 overflow-hidden mb-3 mb-md-4 shadow-sm bg-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 faktur-items-table" id="tableEditInvoiceItems">
                            <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary border-bottom">
                                <tr>
                                    <th style="min-width: 220px;" class="py-2.5 px-3">Nama Produk / Barang</th>
                                    <th style="width: 100px;" class="text-center py-2.5">Qty Faktur</th>
                                    <th style="width: 135px;" class="text-center py-2.5">Sisa Kirim SO</th>
                                    <th style="width: 155px;" class="text-end py-2.5">Harga Satuan</th>
                                    <th style="width: 95px;" class="text-center py-2.5">Diskon</th>
                                    <th style="width: 135px;" class="text-center py-2.5">PPN</th>
                                    <th style="width: 150px;" class="text-end py-2.5 pe-3">Subtotal</th>
                                    <th style="width: 45px;" class="text-center py-2.5">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="edit_invoice_items_body">
                                <tr id="edit_row_empty_item">
                                    <td colspan="8" class="text-center py-4 text-body-secondary small">
                                        Belum ada item faktur. Klik tombol <strong>"Tambah Baris"</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 3: Biaya Tambahan & Alokasi Akun (COA) -->
                <div class="card border border-body-tertiary rounded-4 p-3 p-md-3.5 mb-3 mb-md-4 bg-body-tertiary shadow-sm">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="p-1.5 rounded-2 bg-info-subtle text-info d-inline-flex">
                                <i class="bi bi-truck fs-6"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold text-body-emphasis mb-0" style="font-size: 0.95rem;">3. Biaya Tambahan &amp; Alokasi Akun (COA)</h6>
                                <span class="text-body-secondary small d-none d-md-inline" style="font-size: 0.75rem;">Ongkir, asuransi, atau beban operasional lainnya</span>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 text-nowrap" id="btn_edit_add_cost_row">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambah Biaya</span>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0 faktur-costs-table" id="tableEditInvoiceCosts">
                            <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                                <tr>
                                    <th style="min-width: 170px;">Jenis Biaya</th>
                                    <th style="width: 160px;" class="text-end">Nominal</th>
                                    <th style="min-width: 220px;">Akun Alokasi (COA)</th>
                                    <th style="min-width: 160px;">Keterangan</th>
                                    <th style="width: 50px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="edit_invoice_costs_body">
                                <tr id="edit_row_empty_cost">
                                    <td colspan="5" class="text-center py-3 text-body-secondary small">
                                        Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Calculation Footer Row -->
                <div class="row justify-content-end">
                    <div class="col-12 col-md-8 col-lg-5 ms-auto">
                        <div class="card border border-body-tertiary bg-body-tertiary rounded-4 p-3 p-sm-4 shadow-sm">
                            <!-- Subtotal Kotor (Sebelum Diskon) -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="small fw-semibold text-body-secondary font-monospace">Subtotal Items (Bruto)</span>
                                <span class="fw-bold font-monospace text-body-emphasis" id="edit_txt_subtotal">Rp 0</span>
                                <input type="hidden" name="subtotal" id="edit_input_subtotal" value="0">
                            </div>

                            <!-- Total Diskon (Dihitung dari Item Tabel) -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="small fw-semibold text-body-secondary font-monospace">Total Diskon</span>
                                <span class="fw-bold font-monospace text-danger" id="edit_txt_diskon_nominal">- Rp 0</span>
                                <input type="hidden" name="diskon_nominal" id="edit_input_diskon_nominal" value="0">
                                <input type="hidden" name="diskon_persen" id="edit_input_diskon_persen" value="0">
                            </div>

                            <!-- DPP (Dasar Pengenaan Pajak) -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5 py-2 px-3 rounded-3 bg-body-secondary border border-body-tertiary">
                                <span class="small fw-bold text-primary font-monospace"><i class="bi bi-calculator me-1"></i> DPP (Dasar Pajak)</span>
                                <span class="fw-bold font-monospace text-primary fs-6" id="edit_txt_dpp">Rp 0</span>
                            </div>

                            <!-- Total PPN -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="small fw-semibold text-body-secondary font-monospace">Total PPN (Per Item)</span>
                                <span class="fw-bold font-monospace text-body-emphasis" id="edit_txt_pajak_nominal">+ Rp 0</span>
                                <input type="hidden" name="pajak_nominal" id="edit_input_pajak_nominal" value="0">
                            </div>

                            <!-- Potongan PPh (Pajak Penghasilan) -->
                            <div class="mb-2.5 p-2 bg-body-tertiary rounded-3 border border-body-tertiary">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-semibold text-body-secondary font-monospace"><i class="bi bi-shield-minus me-1 text-danger"></i> Potongan PPh</span>
                                    <span class="fw-bold font-monospace text-danger" id="edit_txt_pph_nominal">- Rp 0</span>
                                    <input type="hidden" name="pph_nominal" id="edit_input_pph_nominal" value="0">
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-sm-6">
                                        <select name="pph_pajak_id" id="edit_pph_pajak_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;">
                                            <option value="" data-tarif="0">-- Tanpa PPh (0%) --</option>
                                            @if(isset($pajaks))
                                                @foreach($pajaks as $p)
                                                    @if(str_contains(strtolower($p->nama_pajak), 'pph') || str_contains(strtolower($p->kode_pajak ?? ''), 'pph'))
                                                        <option value="{{ $p->id }}" data-tarif="{{ $p->tarif }}">{{ $p->nama_pajak }} ({{ (float)$p->tarif }}%)</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="hidden" name="tarif_pph" id="edit_input_tarif_pph" value="0">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <select name="pph_coa_id" id="edit_pph_coa_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Akun Uang Muka PPh / PPh Dibayar Dimuka">
                                            <option value="">-- Auto (Uang Muka PPh) --</option>
                                            @if(isset($accounts))
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Biaya Tambahan (Lainnya) -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="small fw-semibold text-body-secondary font-monospace"><i class="bi bi-truck me-1 text-primary"></i> Total Biaya Tambahan</span>
                                <span class="fw-bold font-monospace text-body-emphasis" id="edit_txt_biaya_lain">+ Rp 0</span>
                                <input type="hidden" name="biaya_lain" id="edit_input_biaya_lain" value="0">
                            </div>

                            <hr class="my-2.5 border-secondary-subtle">

                            <!-- Grand Total (Nilai Faktur) -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="small fw-bold text-body-secondary font-monospace">TOTAL FAKTUR</span>
                                <span class="fw-bold font-monospace text-body-emphasis fs-6" id="edit_txt_grand_total">Rp 0</span>
                                <input type="hidden" name="grand_total" id="edit_input_grand_total" value="0">
                            </div>

                            <!-- Uang Muka / DP Penjualan -->
                            <div class="p-2.5 mb-2.5 rounded-3 bg-body-tertiary border border-body-tertiary" id="edit_container_uang_muka">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="small fw-bold text-warning font-monospace">
                                        <i class="bi bi-cash-stack me-1"></i> Uang Muka / DP
                                    </span>
                                    <span class="small text-muted font-monospace" id="edit_txt_dp_info" style="font-size:0.72rem;"></span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 mb-1">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text font-monospace bg-body-secondary fw-semibold">- Rp</span>
                                            <input type="text" name="uang_muka" id="edit_uang_muka" inputmode="decimal" autocomplete="off" maxlength="20" class="form-control text-end font-monospace mask-number fw-bold text-danger edit-input-number-formatted" value="0" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label mb-1 text-body-secondary font-monospace" style="font-size: 0.73rem;">
                                            <i class="bi bi-bank me-1"></i>Masuk Ke Kas / Bank:
                                        </label>
                                        <select name="kas_bank_id" id="edit_kas_bank_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Rekening Kas / Bank Penerima DP">
                                            <option value="">-- Auto (Kas / Bank Utama) --</option>
                                            @if(isset($kasBanks) && count($kasBanks) > 0)
                                                @foreach($kasBanks as $kb)
                                                    <option value="{{ $kb->id }}">{{ $kb->nama_akun }} {{ $kb->nomor_rekening ? '('.$kb->nomor_rekening.')' : '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label mb-1 text-body-secondary font-monospace" style="font-size: 0.73rem;">
                                            <i class="bi bi-journal-bookmark me-1"></i>Akun Uang Muka (COA):
                                        </label>
                                        <select name="uang_muka_coa_id" id="edit_uang_muka_coa_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Akun Uang Muka Penjualan / Deposit Customer">
                                            <option value="">-- Auto (2106 - Uang Muka Penjualan) --</option>
                                            @if(isset($accounts))
                                                @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisa Tagihan (Piutang Bersih) -->
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle">
                                <span class="fw-bold text-body-emphasis font-monospace fs-6">SISA TAGIHAN</span>
                                <h4 class="fw-bold font-monospace text-success mb-0" id="edit_txt_sisa_tagihan">Rp 0</h4>
                                <input type="hidden" name="sisa_tagihan" id="edit_input_sisa_tagihan" value="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-2.5 py-md-3 px-3 px-md-4 bg-body-secondary d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 px-md-4 fw-semibold order-2 order-sm-1 w-100 w-sm-auto" data-bs-dismiss="modal">
                    Batal
                </button>
                <div class="d-flex gap-2 order-1 order-sm-2 w-100 w-sm-auto">
                    <button type="button" class="btn btn-warning text-dark rounded-pill px-3 px-md-4 fw-bold flex-fill flex-sm-grow-0 text-nowrap" id="btn_edit_save_draft">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Simpan Draft
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-3 px-md-4 fw-bold flex-fill flex-sm-grow-0 text-nowrap" id="btn_edit_post_invoice">
                        <i class="bi bi-send me-1"></i> <span>Simpan &amp; Ajukan</span><span class="d-none d-sm-inline"> Approval</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        let editItemIndex = 0;
        let editCostIndex = 0;
        const EDIT_PAJAK_OPTIONS = @json($pajaks ?? []);
        const EDIT_ACCOUNT_OPTIONS = @json($accounts ?? []);

        function initEditSelect2() {
            if ($.fn.select2) {
                $('#edit_payment_term_id, #edit_pph_pajak_id, #edit_pph_coa_id, #edit_uang_muka_coa_id, #edit_kas_bank_id, #edit_tender_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalEditInvoice'),
                    width: '100%',
                    placeholder: '-- Pilih --',
                    allowClear: true
                });
            }
        }

        $('#modalEditInvoice').on('shown.bs.modal', function () {
            initEditSelect2();
        });


        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildEditPajakSelectHtml(index, selectedId, selectedTarif) {
            let html = `<select name="items[${index}][ppn_pajak_id]" class="form-select form-select-sm rounded-2 font-monospace edit-input-ppn text-center">`;
            html += `<option value="" data-tarif="0" ${!selectedId ? 'selected' : ''}>Non PPN (0%)</option>`;
            EDIT_PAJAK_OPTIONS.forEach(function (p) {
                let isSel = (selectedId == p.id) || (!selectedId && parseFloat(selectedTarif) === parseFloat(p.tarif));
                html += `<option value="${p.id}" data-tarif="${p.tarif}" ${isSel ? 'selected' : ''}>${p.nama_pajak} (${p.tarif}%)</option>`;
            });
            html += `</select>`;
            return html;
        }

        function buildEditCoaSelectHtml(index, selectedId) {
            let html = `<select name="costs[${index}][coa_id]" class="form-select form-select-sm rounded-2 edit-input-cost-coa">`;
            html += `<option value="">-- Pilih Akun COA --</option>`;
            EDIT_ACCOUNT_OPTIONS.forEach(function (acc) {
                let isSel = (selectedId == acc.id);
                html += `<option value="${acc.id}" ${isSel ? 'selected' : ''}>${acc.code} - ${acc.name}</option>`;
            });
            html += `</select>`;
            return html;
        }
        const buildEditCostCoaSelectHtml = buildEditCoaSelectHtml;

        function addEditItemRow(data = {}) {
            $('#edit_row_empty_item').remove();
            let i = editItemIndex++;

            let barangId = data.barang_id || '';
            let kodeBarang = data.kode_barang || '';
            let namaBarang = data.nama_barang || '';
            let qty = data.qty !== undefined ? parseRawNumber(data.qty) : 1;
            let hargaSatuan = data.harga_satuan !== undefined ? parseRawNumber(data.harga_satuan) : 0;
            let diskonPersen = data.diskon_persen !== undefined ? parseRawNumber(data.diskon_persen) : 0;
            let ppnPajakId = data.ppn_pajak_id || '';
            let tarifPpn = data.tarif_ppn !== undefined ? parseRawNumber(data.tarif_ppn) : 0;
            let satuanNama = data.satuan_nama || (data.satuan ? data.satuan.nama_satuan : 'Pcs');
            let qtySo = data.qty_so !== undefined ? parseRawNumber(data.qty_so) : 0;
            let qtyDelivered = data.qty_delivered !== undefined ? parseRawNumber(data.qty_delivered) : 0;
            let sisaKurang = data.sisa_kurang !== undefined ? parseRawNumber(data.sisa_kurang) : (qtySo > 0 ? Math.max(0, qtySo - qty) : 0);

            let hargaFmt = formatRupiahInput(hargaSatuan);
            let diskonFmt = parseRawNumber(diskonPersen);

            let sisaBadgeHtml = '';
            let sisaPillClass = '';
            let sisaPillIcon = '';

            if (qtySo > 0) {
                if (sisaKurang > 0) {
                    sisaPillClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                    sisaPillIcon = 'bi-exclamation-triangle-fill';
                    sisaBadgeHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-1.5 py-0.5" style="font-size:0.65rem;" title="Pesanan SO: ${qtySo} | Terkirim: ${qtyDelivered}"><i class="bi bi-truck me-0.5"></i>Kirim: ${qtyDelivered}/${qtySo} | Kurang ${sisaKurang}</span>`;
                } else {
                    sisaPillClass = 'bg-success-subtle text-success border border-success-subtle';
                    sisaPillIcon = 'bi-check-circle-fill';
                    sisaBadgeHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1.5 py-0.5" style="font-size:0.65rem;" title="Pesanan SO: ${qtySo} | Terkirim: ${qtyDelivered}"><i class="bi bi-check2-all me-0.5"></i>Kirim: ${qtyDelivered}/${qtySo} | Lengkap</span>`;
                }
            } else {
                sisaPillClass = 'bg-body-secondary text-body-secondary border';
                sisaPillIcon = 'bi-dash-circle';
                sisaBadgeHtml = `<span class="badge bg-body-secondary text-body-secondary border font-monospace px-1.5 py-0.5" style="font-size:0.65rem;">Manual</span>`;
            }

            // Nama Barang: Jika nama sudah ada, tampilkan secara elegan dan non-editable (terkunci)
            let safeNama = escapeHtml(namaBarang);
            let safeKode = escapeHtml(kodeBarang);
            let safeSatuan = escapeHtml(satuanNama);

            let namaBarangColHtml = '';
            if (namaBarang) {
                namaBarangColHtml = `
                    <div class="d-flex align-items-start gap-2.5 py-1 px-1">
                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 shadow-xs" style="width: 34px; height: 34px;">
                            <i class="bi bi-box-seam fs-6"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-body-emphasis small text-truncate" title="${safeNama}">
                                ${safeNama}
                            </div>
                            <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                                ${safeKode ? `<span class="badge bg-body-secondary text-body-secondary font-monospace border" style="font-size:0.65rem;"><i class="bi bi-tag-fill me-0.5 text-primary"></i>${safeKode}</span>` : ''}
                                <span class="badge bg-body-secondary text-body-secondary font-monospace border" style="font-size:0.65rem;">Satuan: ${safeSatuan}</span>
                                <span class="d-md-none edit-container-sisa-badge-mobile">
                                    ${sisaBadgeHtml}
                                </span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${i}][nama_barang]" class="edit-input-nama-barang" value="${safeNama}">
                `;
            } else {
                namaBarangColHtml = `
                    <div class="p-1">
                        <input type="text" name="items[${i}][nama_barang]" class="form-control form-control-sm rounded-2 edit-input-nama-barang" placeholder="Ketik nama produk / jasa manual..." required>
                    </div>
                `;
            }

            let rowHtml = `
                <tr id="edit_item_row_${i}" class="edit-item-row" data-index="${i}">
                    <td class="col-nama-barang py-2 px-3">
                        <input type="hidden" name="items[${i}][barang_id]" value="${barangId}">
                        <input type="hidden" name="items[${i}][satuan_id]" value="${data.satuan_id || ''}">
                        <input type="hidden" name="items[${i}][tarif_ppn]" class="edit-input-tarif-ppn" value="${tarifPpn}">
                        <input type="hidden" name="items[${i}][nilai_ppn]" class="edit-input-nilai-ppn" value="0">
                        <input type="hidden" name="items[${i}][diskon_nominal]" class="edit-input-diskon-nominal" value="0">
                        <input type="hidden" name="items[${i}][total_harga]" class="edit-input-total-harga" value="0">
                        <input type="hidden" class="edit-data-qty-so" value="${qtySo}">
                        <input type="hidden" class="edit-data-qty-delivered" value="${qtyDelivered}">
                        <input type="hidden" class="edit-data-satuan-nama" value="${safeSatuan}">
                        ${namaBarangColHtml}
                    </td>
                    <td class="col-qty py-2 text-center align-middle">
                        <label class="mobile-cell-label d-md-none">Jumlah (${safeSatuan}):</label>
                        <input type="number" step="0.01" min="0.01" name="items[${i}][qty]" class="form-control form-control-sm rounded-2 text-center font-monospace fw-bold edit-input-qty" value="${qty}" required style="max-width: 85px; margin: 0 auto;">
                    </td>
                    <td class="col-sisa py-2 text-center align-middle d-none d-md-table-cell">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <span class="badge ${sisaPillClass} rounded-pill px-2.5 py-1 font-monospace fw-bold edit-pill-sisa d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.78rem;">
                                <i class="bi ${sisaPillIcon} edit-icon-sisa"></i>
                                <span class="edit-txt-sisa-val">${sisaKurang > 0 ? sisaKurang + ' Belum Kirim' : 'Lunas Kirim'}</span>
                            </span>
                            <input type="hidden" name="items[${i}][sisa_pengiriman]" class="edit-input-sisa-kurang" value="${sisaKurang}">
                            <div class="mt-1 edit-container-sisa-badge">
                                ${sisaBadgeHtml}
                            </div>
                        </div>
                    </td>
                    <td class="col-harga py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Harga Satuan:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-monospace bg-body-secondary text-body-secondary py-1 px-1.5" style="font-size:0.72rem;">Rp</span>
                            <input type="text" name="items[${i}][harga_satuan]" class="form-control form-control-sm text-end font-monospace edit-input-harga mask-number" value="${hargaFmt}" required>
                        </div>
                    </td>
                    <td class="col-diskon py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Diskon (%):</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="items[${i}][diskon_persen]" class="form-control form-control-sm text-center font-monospace edit-input-diskon mask-number" value="${diskonFmt}">
                            <span class="input-group-text font-monospace bg-body-secondary text-body-secondary py-1 px-1.5" style="font-size:0.72rem;">%</span>
                        </div>
                    </td>
                    <td class="col-ppn py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Pajak PPN:</label>
                        ${buildEditPajakSelectHtml(i, ppnPajakId, tarifPpn)}
                    </td>
                    <td class="col-subtotal text-end font-monospace fw-bold edit-cell-subtotal text-body-emphasis align-middle py-2 pe-3">
                        <div class="item-subtotal-box d-flex justify-content-between align-items-center p-2 rounded-2">
                            <span class="mobile-subtotal-label d-md-none small fw-semibold text-body-secondary">Subtotal:</span>
                            <span class="fw-bold font-monospace text-success edit-subtotal-val">Rp 0</span>
                        </div>
                        <input type="hidden" name="items[${i}][total_harga]" class="edit-input-total-harga" value="0">
                    </td>
                    <td class="col-aksi text-center align-middle py-2">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger border-0 rounded-circle btn-remove-edit-item-row" data-row="${i}" title="Hapus Item">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#edit_invoice_items_body').append(rowHtml);
            calculateEditTotals();
        }

        function addEditCostRow(data = {}) {
            $('#edit_row_empty_cost').remove();
            let i = editCostIndex++;

            let jenisBiaya = data.jenis_biaya || '';
            let nominal = data.nominal !== undefined ? data.nominal : 0;
            let coaId = data.coa_id || '';
            let keterangan = data.keterangan || '';
            let nominalFmt = formatRupiahInput(nominal);

            let rowHtml = `
                <tr id="edit_cost_row_${i}" class="edit-cost-row">
                    <td class="col-cost-jenis">
                        <label class="mobile-cell-label d-md-none">Jenis Biaya:</label>
                        <input type="text" name="costs[${i}][jenis_biaya]" class="form-control form-control-sm rounded-2 edit-input-cost-jenis" value="${jenisBiaya}" placeholder="Contoh: Ongkir, Asuransi..." required>
                    </td>
                    <td class="col-cost-nominal">
                        <label class="mobile-cell-label d-md-none">Nominal (Rp):</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-monospace small bg-body-secondary text-body-secondary">Rp</span>
                            <input type="text" name="costs[${i}][nominal]" class="form-control form-control-sm rounded-2 text-end font-monospace edit-input-cost-nominal mask-number" value="${nominalFmt}" required>
                        </div>
                    </td>
                    <td class="col-cost-coa">
                        <label class="mobile-cell-label d-md-none">Akun Alokasi (COA):</label>
                        ${buildEditCoaSelectHtml(i, coaId)}
                    </td>
                    <td class="col-cost-ket">
                        <label class="mobile-cell-label d-md-none">Keterangan (Catatan Opsional):</label>
                        <input type="text" name="costs[${i}][keterangan]" class="form-control form-control-sm rounded-2 edit-input-cost-ket" value="${keterangan}" placeholder="Catatan opsional...">
                    </td>
                    <td class="col-cost-aksi text-center align-middle">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger border-0 rounded-circle btn-remove-edit-cost-row" data-row="${i}" title="Hapus Biaya">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
            `;

            let $row = $(rowHtml);
            $('#edit_invoice_costs_body').append($row);

            $row.find('.edit-input-cost-coa').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditInvoice'),
                width: '100%',
                placeholder: '-- Pilih COA --'
            });

            calculateEditTotals();
        }

        $('#btn_edit_add_item_row').on('click', function () {
            addEditItemRow();
        });

        $('#btn_edit_add_cost_row').on('click', function () {
            addEditCostRow();
        });

        $(document).on('click', '.btn-remove-edit-item-row', function () {
            let rowId = $(this).data('row');
            $(`#edit_item_row_${rowId}`).remove();
            if ($('#edit_invoice_items_body tr').length === 0) {
                $('#edit_invoice_items_body').html(`
                    <tr id="edit_row_empty_item">
                        <td colspan="8" class="text-center py-4 text-body-secondary small">
                            Belum ada item faktur. Klik tombol <strong>"Tambah Baris"</strong>.
                        </td>
                    </tr>
                `);
            }
            calculateEditTotals();
        });

        $(document).on('click', '.btn-remove-edit-cost-row', function () {
            let rowId = $(this).data('row');
            $(`#edit_cost_row_${rowId}`).remove();
            if ($('#edit_invoice_costs_body tr').length === 0) {
                $('#edit_invoice_costs_body').html(`
                    <tr id="edit_row_empty_cost">
                        <td colspan="5" class="text-center py-3 text-body-secondary small">
                            Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                        </td>
                    </tr>
                `);
            }
            calculateEditTotals();
        });

        let isEditPopulating = false;

        $(document).on('input change', '.edit-input-qty, .edit-input-harga, .edit-input-diskon, .edit-input-ppn, .edit-input-cost-nominal, #edit_pph_pajak_id', function () {
            calculateEditTotals();
        });

        // Auto-calculate Due Date when Payment Term or Invoice Date changes manually
        $('#edit_payment_term_id, #edit_tanggal_faktur').on('change', function () {
            if (isEditPopulating) return;
            let termDays = parseInt($('#edit_payment_term_id option:selected').data('days')) || 0;
            let tanggalFaktur = $('#edit_tanggal_faktur').val();
            if (tanggalFaktur && termDays > 0) {
                let d = new Date(tanggalFaktur);
                d.setDate(d.getDate() + termDays);
                let dueStr = d.toISOString().split('T')[0];
                $('#edit_tanggal_jatuh_tempo').val(dueStr);
            }
        });

        function parseRawNumber(str) {
            if (typeof str === 'number') return isNaN(str) ? 0 : str;
            if (!str) return 0;
            let s = str.toString().trim();
            if (!s) return 0;

            // Remove non-numeric characters except digits, dot, comma, minus
            s = s.replace(/[^0-9.,-]/g, '');
            if (!s) return 0;

            if (s.includes('.') && s.includes(',')) {
                let lastDot = s.lastIndexOf('.');
                let lastComma = s.lastIndexOf(',');
                if (lastComma > lastDot) {
                    // Indonesian: 1.500.000,50 -> strip dot, comma to dot
                    s = s.replace(/\./g, '').replace(',', '.');
                } else {
                    // US: 1,500,000.50 -> strip comma
                    s = s.replace(/,/g, '');
                }
            } else if (s.includes('.')) {
                let parts = s.split('.');
                if (parts.length > 2) {
                    // e.g. 1.500.000
                    s = s.replace(/\./g, '');
                } else if (parts.length === 2 && parts[1].length === 3) {
                    // e.g. 170.000 or 80.000 (thousand dot)
                    s = s.replace(/\./g, '');
                }
            } else if (s.includes(',')) {
                let parts = s.split(',');
                if (parts.length > 2) {
                    s = s.replace(/,/g, '');
                } else if (parts.length === 2) {
                    if (parts[1].length === 3 && parts[0].length <= 3) {
                        s = s.replace(/,/g, '');
                    } else {
                        s = s.replace(',', '.');
                    }
                }
            }
            return parseFloat(s) || 0;
        }

        function formatRupiahInput(val) {
            let num = parseRawNumber(val);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $(document).on('focus', '.edit-input-harga, .edit-input-cost-nominal, .edit-input-number-formatted', function () {
            let raw = parseRawNumber($(this).val());
            if (raw === 0) $(this).val('');
            else $(this).val(raw);
        });

        $(document).on('blur', '.edit-input-harga, .edit-input-cost-nominal, .edit-input-number-formatted', function () {
            let raw = parseRawNumber($(this).val());
            $(this).val(formatRupiahInput(raw));
            calculateEditTotals();
        });

        // Hanya izinkan angka dan titik/koma desimal pada input Uang Muka (DP) Edit serta batasi maksimal 20 karakter
        $(document).on('keypress', '#edit_uang_muka', function (e) {
            let charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 44 && charCode !== 46) {
                e.preventDefault();
                return false;
            }
            // Batasi maksimal 20 karakter jika tidak ada teks yang diblok/diseleksi
            let inputEl = this;
            let selStart = inputEl.selectionStart;
            let selEnd = inputEl.selectionEnd;
            if ($(this).val().length >= 20 && selStart === selEnd) {
                e.preventDefault();
                return false;
            }
        });

        // Bersihkan karakter non-angka saat input/paste Edit serta potong jika melebihi 20 karakter
        $(document).on('input', '#edit_uang_muka', function () {
            let val = $(this).val();
            let sanitized = val.replace(/[^0-9.,]/g, '');
            if (sanitized.length > 20) {
                sanitized = sanitized.substring(0, 20);
            }
            if (val !== sanitized) {
                $(this).val(sanitized);
            }
            calculateEditTotals();
        });

        // Validasi saat blur / change: DP tidak boleh melebihi total faktur
        $(document).on('change blur', '#edit_uang_muka', function () {
            let grandTotal = parseFloat($('#edit_input_grand_total').val()) || 0;
            let uangMuka = parseRawNumber($(this).val()) || 0;

            if (uangMuka < 0) {
                $(this).val('0,00');
                calculateEditTotals();
                return;
            }

            if (grandTotal <= 0 && uangMuka > 0) {
                $(this).val('0,00');
                calculateEditTotals();
                Swal.fire({
                    icon: 'warning',
                    title: 'Total Faktur Rp 0',
                    text: 'Silakan isi rincian barang/jasa terlebih dahulu sebelum mengisi nominal Uang Muka (DP).',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            if (grandTotal > 0 && uangMuka > grandTotal) {
                $(this).val(formatRupiahInput(grandTotal));
                calculateEditTotals();
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Melebihi Total Faktur!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '). Nominal DP otomatis disesuaikan ke nominal maksimal.',
                    confirmButtonColor: '#059669'
                });
            }
        });

        function calculateEditTotals() {
            let totalSubtotalBruto = 0;
            let totalDiskonNominal = 0;
            let totalPpnNominal = 0;
            let totalBiayaLain = 0;

            $('.edit-item-row').each(function () {
                let qty = parseRawNumber($(this).find('.edit-input-qty').val());
                let harga = parseRawNumber($(this).find('.edit-input-harga').val());
                let diskonPersen = parseRawNumber($(this).find('.edit-input-diskon').val());
                let $ppnOpt = $(this).find('.edit-input-ppn option:selected');
                let tarifPpn = parseFloat($ppnOpt.data('tarif') || 0);

                let subBruto = qty * harga;
                let diskonNom = subBruto * (diskonPersen / 100);
                let netItem = subBruto - diskonNom;
                let ppnNom = netItem * (tarifPpn / 100);
                let subTotalLine = netItem + ppnNom;

                totalSubtotalBruto += subBruto;
                totalDiskonNominal += diskonNom;
                totalPpnNominal += ppnNom;

                $(this).find('.edit-input-tarif-ppn').val(tarifPpn);
                $(this).find('.edit-input-nilai-ppn').val(ppnNom);
                $(this).find('.edit-input-diskon-nominal').val(diskonNom);
                $(this).find('.edit-input-total-harga').val(subTotalLine);
                let $subVal = $(this).find('.edit-subtotal-val');
                let subFmt = 'Rp ' + subTotalLine.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if ($subVal.length) {
                    $subVal.text(subFmt);
                } else {
                    $(this).find('.edit-cell-subtotal').text(subFmt);
                }
            });

            $('.edit-cost-row').each(function () {
                let nom = parseRawNumber($(this).find('.edit-input-cost-nominal').val());
                totalBiayaLain += nom;
            });

            let dpp = totalSubtotalBruto - totalDiskonNominal;

            let $pphOpt = $('#edit_pph_pajak_id option:selected');
            let tarifPph = parseFloat($pphOpt.data('tarif') || 0);
            $('#edit_input_tarif_pph').val(tarifPph);

            let pphNominal = dpp * (tarifPph / 100);

            let grandTotal = dpp + totalPpnNominal - pphNominal + totalBiayaLain;
            let uangMuka = parseRawNumber($('#edit_uang_muka').val()) || 0;
            if (uangMuka < 0) uangMuka = 0;

            // Validasi visual DP tidak boleh melebihi Total Faktur
            if (grandTotal > 0 && uangMuka > grandTotal) {
                $('#edit_uang_muka').addClass('is-invalid border-danger');
                $('#edit_txt_dp_info').html('<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>DP melebihi total faktur (Maks Rp ' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')</span>');
            } else if (grandTotal <= 0 && uangMuka > 0) {
                $('#edit_uang_muka').addClass('is-invalid border-danger');
                $('#edit_txt_dp_info').html('<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Total faktur masih Rp 0</span>');
            } else {
                $('#edit_uang_muka').removeClass('is-invalid border-danger');
                if (uangMuka > 0 && grandTotal > 0) {
                    let percent = ((uangMuka / grandTotal) * 100).toFixed(1);
                    $('#edit_txt_dp_info').html('<span class="text-warning font-monospace fw-semibold">(' + percent + '% dari total)</span>');
                } else if (uangMuka === 0) {
                    $('#edit_txt_dp_info').text('');
                }
            }

            let sisaTagihan = Math.max(0, grandTotal - uangMuka);

            $('#edit_txt_subtotal').text('Rp ' + totalSubtotalBruto.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_subtotal').val(totalSubtotalBruto);

            let diskonPrefix = totalDiskonNominal > 0 ? '- Rp ' : 'Rp ';
            $('#edit_txt_diskon_nominal').text(diskonPrefix + totalDiskonNominal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_diskon_nominal').val(totalDiskonNominal);

            $('#edit_txt_dpp').text('Rp ' + dpp.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            let ppnPrefix = totalPpnNominal > 0 ? '+ Rp ' : 'Rp ';
            $('#edit_txt_pajak_nominal').text(ppnPrefix + totalPpnNominal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_pajak_nominal').val(totalPpnNominal);

            let pphPrefix = pphNominal > 0 ? '- Rp ' : 'Rp ';
            $('#edit_txt_pph_nominal').text(pphPrefix + pphNominal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_pph_nominal').val(pphNominal);

            let biayaPrefix = totalBiayaLain > 0 ? '+ Rp ' : 'Rp ';
            $('#edit_txt_biaya_lain').text(biayaPrefix + totalBiayaLain.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_biaya_lain').val(totalBiayaLain);

            $('#edit_txt_grand_total').text('Rp ' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_grand_total').val(grandTotal);

            $('#edit_txt_sisa_tagihan').text('Rp ' + sisaTagihan.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#edit_input_sisa_tagihan').val(sisaTagihan);
        }

        // Trigger Edit Invoice Data Fetching & Population
        $(document).on('click', '.btn-edit-invoice', function () {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Memuat Data Faktur...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/edit", function (res) {
                Swal.close();
                if (res.status === 'success') {
                    try {
                        let d = res.data;
                        isEditPopulating = true;

                        $('#edit_invoice_id').val(d.id);
                        $('#edit_nomor_faktur').val(d.nomor_faktur);
                        $('#edit_nomor_faktur_pajak').val(d.nomor_faktur_pajak || '');
                        $('#edit_file_pendukung').val('');
                        $('#edit_file_preview_info').addClass('d-none').html('');
                        
                        let tglFakturVal = d.tanggal_faktur_raw || (d.tanggal_faktur ? d.tanggal_faktur.substring(0, 10) : '');
                        let tglJatuhTempoVal = d.tanggal_jatuh_tempo_raw || (d.tanggal_jatuh_tempo ? d.tanggal_jatuh_tempo.substring(0, 10) : '');

                        $('#edit_tanggal_faktur').val(tglFakturVal);
                        $('#edit_tanggal_jatuh_tempo').val(tglJatuhTempoVal);
                        
                        initEditSelect2();

                        $('#edit_customer_id').val(d.customer_id);
                        let custName = d.customer ? (d.customer.nama_customer + (d.customer.kode_customer ? ' (' + d.customer.kode_customer + ')' : '')) : '-';
                        $('#edit_customer_name_display').val(custName);

                        $('#edit_payment_term_id').val(d.payment_term_id || '').trigger('change');
                        // Pastikan tanggal jatuh tempo dari database tidak tertimpa kalkulasi otomatis payment term:
                        $('#edit_tanggal_jatuh_tempo').val(tglJatuhTempoVal);
                        $('#edit_currency_id').val(d.currency_id || '1');
                        if (d.tender_id) {
                            if (!$('#edit_tender_id option[value="' + d.tender_id + '"]').length) {
                                let tName = (d.tender && d.tender.nama_tender) ? (d.tender.nama_tender + (d.tender.nomor_kontrak && d.tender.nomor_kontrak !== '-' ? ' (' + d.tender.nomor_kontrak + ')' : '')) : ('Tender #' + d.tender_id);
                                let newOption = new Option(tName, d.tender_id, true, true);
                                $('#edit_tender_id').append(newOption);
                            }
                            $('#edit_tender_id').val(d.tender_id).trigger('change');
                        } else {
                            $('#edit_tender_id').val('').trigger('change');
                        }
                        $('#edit_delivery_order_id').val(d.delivery_order_id || '');

                        // Ref Doc Badge
                        let refHtml = '';
                        if (d.delivery_order) {
                            refHtml += `<span class="badge bg-primary me-1"><i class="bi bi-truck me-1"></i>DO: ${escapeHtml(d.delivery_order.nomor_do)}</span>`;
                        }
                        if (d.sales_order) {
                            refHtml += `<span class="badge bg-info text-dark me-1"><i class="bi bi-cart me-1"></i>SO: ${escapeHtml(d.sales_order.nomor_so)}</span>`;
                        }
                        if (!refHtml) {
                            refHtml = `<span class="small text-body-secondary font-monospace">Manual (Tidak terhubung ke SO / DO)</span>`;
                        }
                        $('#edit_ref_doc_badge').html(refHtml);

                        // File Pendukung
                        if (d.file_pendukung) {
                            let fileUrl = `{{ asset('storage') }}/${d.file_pendukung}`;
                            let downloadUrl = `{{ url('modul-marketing/faktur-penjualan') }}/${d.id}/download-document`;
                            let ext = d.file_pendukung.split('.').pop().toLowerCase();
                            let fileName = d.file_pendukung.split('/').pop();
                            $('#edit_current_file_container').html(`
                                <div class="small mt-1.5 p-2 rounded-3 bg-body-secondary border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-1.5 text-truncate" style="max-width: 280px;">
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i></span>
                                        <span class="font-monospace text-truncate text-body-emphasis small" title="${fileName}">${fileName}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5 fw-bold btn-preview-document"
                                            data-url="${fileUrl}" data-filename="${fileName}" data-ext="${ext}" data-download="${downloadUrl}">
                                            <i class="bi bi-eye me-1"></i>Preview
                                        </button>
                                        <a href="${downloadUrl}" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-0.5 fw-bold btn-download-document no-loader">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            `);
                        } else {
                            $('#edit_current_file_container').empty();
                        }

                        // PPh & PPh COA
                        $('#edit_pph_pajak_id').val(d.pph_pajak_id || '').trigger('change');
                        $('#edit_pph_coa_id').val(d.pph_coa_id || '').trigger('change');

                        // Reset Tables
                        editItemIndex = 0;
                        editCostIndex = 0;
                        $('#edit_invoice_items_body').empty();
                        $('#edit_invoice_costs_body').empty();

                        if (d.details && d.details.length > 0) {
                            d.details.forEach(function (item) {
                                addEditItemRow({
                                    barang_id: item.barang_id,
                                    kode_barang: item.kode_barang || (item.barang ? item.barang.kode_barang : ''),
                                    satuan_id: item.satuan_id,
                                    satuan_nama: item.satuan_nama || (item.satuan ? item.satuan.nama_satuan : 'Pcs'),
                                    nama_barang: item.nama_barang,
                                    qty: item.qty,
                                    qty_so: item.qty_so,
                                    qty_delivered: item.qty_delivered,
                                    sisa_pengiriman: item.sisa_pengiriman,
                                    sisa_kurang: item.sisa_kurang,
                                    harga_satuan: item.harga_satuan,
                                    diskon_persen: item.diskon_persen,
                                    ppn_pajak_id: item.ppn_pajak_id,
                                    tarif_ppn: item.tarif_ppn
                                });
                            });
                        } else {
                            $('#edit_invoice_items_body').html(`
                                <tr id="edit_row_empty_item">
                                    <td colspan="8" class="text-center py-4 text-body-secondary small">
                                        Belum ada item faktur. Klik tombol <strong>"Tambah Baris Manual"</strong>.
                                    </td>
                                </tr>
                            `);
                        }

                        isEditPopulating = false;

                        if (d.costs && d.costs.length > 0) {
                            d.costs.forEach(function (cost) {
                                addEditCostRow({
                                    jenis_biaya: cost.jenis_biaya,
                                    nominal: cost.nominal,
                                    coa_id: cost.coa_id,
                                    keterangan: cost.keterangan
                                });
                            });
                        } else {
                            $('#edit_invoice_costs_body').html(`
                                <tr id="edit_row_empty_cost">
                                    <td colspan="5" class="text-center py-3 text-body-secondary small">
                                        Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                                    </td>
                                </tr>
                            `);
                        }

                        let dpVal = parseFloat(d.uang_muka || 0);
                        $('#edit_uang_muka').val(formatRupiahInput(dpVal));
                        $('#edit_uang_muka_coa_id').val(d.uang_muka_coa_id || '').trigger('change');
                        $('#edit_kas_bank_id').val(d.kas_bank_id || '').trigger('change');
                        if (d.sales_order && parseFloat(d.sales_order.dp_nominal || 0) > 0) {
                            let dpPercentStr = d.sales_order.dp_persen ? ` | ${d.sales_order.dp_persen}%` : '';
                            $('#edit_txt_dp_info').text(`(DP SO: Rp ${formatRupiahInput(d.sales_order.dp_nominal)}${dpPercentStr})`);
                        } else {
                            $('#edit_txt_dp_info').text('');
                        }

                        calculateEditTotals();

                        $('#modalEditInvoice').modal('show');
                    } catch (err) {
                        console.error('Error rendering edit invoice modal:', err);
                        Swal.fire('Error Render!', 'Terjadi kendala menampilkan data edit faktur: ' + err.message, 'error');
                    }
                } else {
                    Swal.fire('Gagal!', res.message || 'Gagal mengambil data faktur.', 'error');
                }
            }).fail(function (xhr) {
                Swal.close();
                let errMsg = 'Terjadi kesalahan sistem saat memuat data faktur.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                Swal.fire('Gagal!', errMsg, 'error');
            });
        });

        $('#btn_edit_save_draft').on('click', function () {
            $('#edit_invoice_action_type').val('draft');
            $('#formEditInvoice').submit();
        });

        $('#btn_edit_post_invoice').on('click', function () {
            $('#edit_invoice_action_type').val('submit');
        });

        // Validasi Ukuran File (Max 10MB) & Tampilan Info Kompresi (Edit Modal)
        $('#edit_file_pendukung').on('change', function () {
            let file = this.files[0];
            let $preview = $('#edit_file_preview_info');

            if (!file) {
                $preview.addClass('d-none').html('');
                return;
            }

            const maxBytes = 10 * 1024 * 1024; // 10 MB
            if (file.size > maxBytes) {
                let actualMB = (file.size / (1024 * 1024)).toFixed(2);
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar!',
                    html: `Ukuran file yang dipilih: <strong>${actualMB} MB</strong>.<br>Batas keamanan maksimal file yang diizinkan adalah <strong>10 MB</strong>.<br>Silakan pilih file yang lebih kecil.`,
                    confirmButtonColor: '#ef4444'
                });
                $(this).val('');
                $preview.addClass('d-none').html('');
                return;
            }

            let sizeFormatted = file.size > 1024 * 1024
                ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(1) + ' KB';
            let isImg = file.type.startsWith('image/');
            let icon = isImg ? 'bi-image text-info' : 'bi-file-earmark-pdf text-danger';
            let compressBadge = isImg 
                ? '<span class="badge bg-success-subtle text-success ms-auto"><i class="bi bi-magic me-1"></i>Otomatis Dikompres ke KB</span>' 
                : '<span class="badge bg-secondary-subtle text-secondary ms-auto font-monospace">' + sizeFormatted + '</span>';

            $preview.removeClass('d-none').html(`
                <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-body-secondary border border-body-tertiary small">
                    <i class="bi ${icon} fs-5"></i>
                    <div class="text-truncate" style="max-width: 220px;" title="${escapeHtml(file.name)}">
                        <span class="fw-semibold text-body-emphasis">${escapeHtml(file.name)}</span>
                        <small class="d-block text-muted font-monospace">${sizeFormatted}</small>
                    </div>
                    ${compressBadge}
                </div>
            `);
        });

        $('#modalEditInvoice').on('hidden.bs.modal', function () {
            $('#edit_file_pendukung').val('');
            $('#edit_file_preview_info').addClass('d-none').html('');
        });

        // Form Edit Submit via AJAX
        $('#formEditInvoice').on('submit', function (e) {
            e.preventDefault();
            let invoiceId = $('#edit_invoice_id').val();

            let fileInput = $('#edit_file_pendukung')[0];
            if (fileInput && fileInput.files && fileInput.files[0]) {
                if (fileInput.files[0].size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar!',
                        text: 'Ukuran file dokumen/foto pendukung maksimal 10 MB.'
                    });
                    return false;
                }
            }

            let grandTotalVal = parseFloat($('#edit_input_grand_total').val()) || 0;
            let rawDpStr = $('#edit_uang_muka').val() ? $('#edit_uang_muka').val().toString().trim() : '';
            if (rawDpStr.length > 20) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input Terlalu Panjang!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi 20 karakter.'
                });
                $('#edit_uang_muka').focus();
                return false;
            }

            let uangMukaVal = parseRawNumber($('#edit_uang_muka').val()) || 0;

            if (isNaN(uangMukaVal) || uangMukaVal < 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Tidak Valid!',
                    text: 'Nominal Uang Muka (DP) harus diisi angka yang valid.'
                });
                $('#edit_uang_muka').focus();
                return false;
            }

            if (grandTotalVal > 0 && uangMukaVal > grandTotalVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Melebihi Total Faktur!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' + grandTotalVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ').'
                });
                $('#edit_uang_muka').focus();
                return false;
            }

            if (uangMukaVal > 0) {
                let kasBankId = $('#edit_kas_bank_id').val();
                let uangMukaCoaId = $('#edit_uang_muka_coa_id').val();

                if (!kasBankId) {
                    $('#edit_kas_bank_id').select2('open');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Karena Faktur Penjualan memiliki nominal Uang Muka (DP), Rekening Kas & Bank penerima wajib dipilih!'
                    });
                    return false;
                }

                if (!uangMukaCoaId) {
                    $('#edit_uang_muka_coa_id').select2('open');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Karena Faktur Penjualan memiliki nominal Uang Muka (DP), Akun Uang Muka Penjualan (COA) wajib dipilih!'
                    });
                    return false;
                }
            }

            // Unformat masked currency inputs to pure numbers for submission
            $('.edit-input-harga').each(function () {
                $(this).val(parseRawNumber($(this).val()));
            });
            $('.edit-input-cost-nominal').each(function () {
                $(this).val(parseRawNumber($(this).val()));
            });
            $('#edit_uang_muka').val(parseRawNumber($('#edit_uang_muka').val()));

            let formData = new FormData(this);

            // Immediately reformat inputs back to Rupiah string in UI
            $('.edit-input-harga').each(function () {
                $(this).val(formatRupiahInput($(this).val()));
            });
            $('.edit-input-cost-nominal').each(function () {
                $(this).val(formatRupiahInput($(this).val()));
            });
            $('#edit_uang_muka').val(formatRupiahInput($('#edit_uang_muka').val()));

            let actionVal = $('#edit_invoice_action_type').val();
            let actionText = actionVal === 'submit' ? 'mengajukan approval' : (actionVal === 'post' ? 'menerbitkan' : 'menyimpan draf');

            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: `Apakah Anda yakin ingin ${actionText} faktur penjualan ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Faktur...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "{{ url('modul-marketing/faktur-penjualan') }}/" + invoiceId + "/update",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            Swal.close();
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                $('#modalEditInvoice').modal('hide');
                                if (typeof $('#tableSalesInvoices').DataTable === 'function') {
                                    $('#tableSalesInvoices').DataTable().ajax.reload(null, false);
                                }
                            } else {
                                Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function (xhr) {
                            Swal.close();
                            let msg = 'Terjadi kesalahan pada sistem.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
