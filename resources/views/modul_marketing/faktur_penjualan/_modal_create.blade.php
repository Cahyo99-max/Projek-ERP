<!-- Modal Create Invoice Penjualan -->
<div class="modal fade" id="modalCreateInvoice" tabindex="-1" aria-labelledby="modalCreateInvoiceLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable faktur-modal-dialog" style="max-width: 95vw; width: 95vw;">
        <form id="formCreateInvoice" action="{{ route('modul_marketing.faktur_penjualan.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 bg-body">
            @csrf
            <input type="hidden" name="action_type" id="invoice_action_type" value="post">
            <input type="hidden" name="sales_order_id" id="create_sales_order_id">

            <div class="modal-header border-bottom py-2.5 py-md-3 px-3 px-md-4 bg-body-secondary d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-receipt fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-md-5" id="modalCreateInvoiceLabel">Buat Faktur Penjualan Baru</h5>
                        <p class="text-body-secondary small mb-0 font-monospace d-none d-sm-block" style="font-size: 0.78rem;">Terbitkan Faktur Penjualan resmi berdasarkan Sales Order (SO) atau manual.</p>
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
                                <label class="form-label small fw-bold text-body-emphasis d-flex justify-content-between mb-1">
                                    <span>Nomor Faktur</span>
                                    <small class="text-muted font-monospace" style="font-size: 0.7rem;">Kosong = Auto</small>
                                </label>
                                <input type="text" name="nomor_faktur" id="create_nomor_faktur" class="form-control form-control-sm rounded-3 font-monospace" placeholder="INV-SLS/YYYY/MM/XXXX">
                            </div>

                            <!-- Nomor Faktur Pajak (NSFP) -->
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label small fw-bold text-body-emphasis d-flex justify-content-between mb-1">
                                    <span>No. Faktur Pajak</span>
                                    <small class="text-muted font-monospace" style="font-size: 0.7rem;">Opsional</small>
                                </label>
                                <input type="text" name="nomor_faktur_pajak" id="create_nomor_faktur_pajak" class="form-control form-control-sm rounded-3 font-monospace" placeholder="010.000-26.00000001">
                            </div>

                            <!-- Tanggal Faktur -->
                            <div class="col-6 col-sm-6 col-lg-3">
                                <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Faktur <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_faktur" id="create_tanggal_faktur" class="form-control form-control-sm rounded-3 font-monospace" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Tanggal Jatuh Tempo -->
                            <div class="col-6 col-sm-6 col-lg-3">
                                <label class="form-label small fw-bold text-body-emphasis mb-1">Tanggal Jatuh Tempo</label>
                                <input type="date" name="tanggal_jatuh_tempo" id="create_tanggal_jatuh_tempo" class="form-control form-control-sm rounded-3 font-monospace" value="{{ date('Y-m-d') }}">
                            </div>

                            <!-- Customer -->
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-bold text-body-emphasis mb-1">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="create_customer_id" class="form-select rounded-3" required>
                                    <option value="">-- Pilih Customer --</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}" data-payment-term="{{ $cust->payment_term_id ?? '' }}">{{ $cust->nama_customer }} ({{ $cust->kode_customer ?? 'CUST' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tarik Data dari Sales Order (SO) -->
                            <div class="col-12 col-sm-6 col-lg-5">
                                <label class="form-label small fw-bold text-success d-flex align-items-center justify-content-between mb-1">
                                    <span><i class="bi bi-cart-check me-1"></i> Tarik Sales Order (SO)</span>
                                    <small class="text-body-secondary d-none d-sm-inline" style="font-size:0.72rem;">Isi otomatis item &amp; pengiriman</small>
                                </label>
                                <select name="sales_order_select_id" id="create_sales_order_select_id" class="form-select rounded-3 border-success-subtle">
                                    <option value="">-- Pilih Sales Order (SO) --</option>
                                </select>
                            </div>

                            <!-- Tender / Proyek Terkait -->
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label small fw-bold text-body-emphasis d-flex align-items-center justify-content-between mb-1">
                                    <span><i class="bi bi-briefcase me-1 text-info"></i> Tender / Proyek</span>
                                    <small class="text-muted font-monospace" style="font-size:0.7rem;">Opsional</small>
                                </label>
                                <select name="tender_id" id="create_tender_id" class="form-select rounded-3">
                                    <option value="">-- Pilih Tender --</option>
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
                                <input type="file" name="file_pendukung" id="create_file_pendukung" class="form-control form-control-sm rounded-3 input-validate-filesize" accept=".pdf,.jpg,.jpeg,.png,.webp" data-max-size="10485760">
                                <div class="form-text text-body-secondary small mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-info-circle text-primary me-1"></i>Format: PDF, JPG, PNG, WEBP (Maks. 10 MB). Foto otomatis dikompres ke ukuran <strong>KB</strong> saat disimpan agar hemat server.
                                </div>
                                <div id="create_file_preview_info" class="mt-1 d-none"></div>
                            </div>

                            <!-- Payment Term & Due Date -->
                            <div class="col-12 col-sm-6 col-lg-6">
                                <label class="form-label small fw-bold text-body-emphasis mb-1">Syarat Pembayaran</label>
                                <select name="payment_term_id" id="create_payment_term_id" class="form-select rounded-3">
                                    <option value="">-- Cash / COD --</option>
                                    @foreach($paymentTerms as $term)
                                        <option value="{{ $term->id }}" data-days="{{ $term->jumlah_hari }}">{{ $term->nama_term }} ({{ $term->jumlah_hari }} hari)</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Hidden Currency ID & Kurs (Fokus Rupiah / IDR) -->
                            <input type="hidden" name="currency_id" id="create_currency_id" value="1">
                            <input type="hidden" name="kurs" id="create_kurs" value="1.0000">
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
                                <span class="text-body-secondary small d-none d-md-inline" style="font-size: 0.75rem;">Item produk atau jasa yang ditagihkan</span>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 text-nowrap" id="btn_add_item_row">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah Baris</span>
                        </button>
                    </div>

                    <div class="card border border-body-tertiary rounded-4 overflow-hidden mb-3 mb-md-4 shadow-sm bg-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 faktur-items-table" id="tableInvoiceItems">
                                <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary border-bottom">
                                    <tr>
                                        <th style="min-width: 220px;" class="py-2.5 px-3">Nama Produk / Barang</th>
                                        <th style="width: 100px;" class="text-center py-2.5">Qty Faktur</th>
                                        <th style="width: 135px;" class="text-center py-2.5">Sisa Kirim SO</th>
                                        <th style="width: 155px;" class="text-end py-2.5">Harga Satuan</th>
                                        <th style="width: 95px;" class="text-center py-2.5">Diskon</th>
                                        <th style="width: 135px;" class="text-center py-2.5">PPN</th>
                                        <th style="width: 150px;" class="text-end py-2.5 pe-3">Subtotal</th>
                                        <th style="width: 48px;" class="text-center py-2.5">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice_items_body">
                                    <tr id="row_empty_item">
                                        <td colspan="8" class="text-center py-4 text-body-secondary small">
                                            Pilih <strong>Sales Order (SO)</strong> di atas atau klik tombol <strong>"Tambah Baris"</strong> untuk mengisi item faktur.
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
                            
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5 text-nowrap" id="btn_add_cost_row">
                                <i class="bi bi-plus-circle"></i>
                                <span>Tambah Biaya</span>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0 faktur-costs-table" id="tableInvoiceCosts">
                                <thead class="bg-body-secondary small text-uppercase font-monospace fw-bold text-body-secondary">
                                    <tr>
                                        <th style="min-width: 170px;">Jenis Biaya</th>
                                        <th style="width: 160px;" class="text-end">Nominal</th>
                                        <th style="min-width: 220px;">Akun Alokasi</th>
                                        <th style="min-width: 160px;">Keterangan</th>
                                        <th style="width: 50px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice_costs_body">
                                    <tr id="row_empty_cost">
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
                                    <span class="fw-bold font-monospace text-body-emphasis" id="txt_subtotal">Rp 0</span>
                                    <input type="hidden" name="subtotal" id="input_subtotal" value="0">
                                </div>

                                <!-- Total Diskon (Dihitung dari Item Tabel) -->
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <span class="small fw-semibold text-body-secondary font-monospace">Total Diskon</span>
                                    <span class="fw-bold font-monospace text-danger" id="txt_diskon_nominal">- Rp 0</span>
                                    <input type="hidden" name="diskon_nominal" id="input_diskon_nominal" value="0">
                                    <input type="hidden" name="diskon_persen" id="input_diskon_persen" value="0">
                                </div>

                                <!-- DPP (Dasar Pengenaan Pajak) -->
                                <div class="d-flex justify-content-between align-items-center mb-2.5 py-2 px-3 rounded-3 bg-body-secondary border border-body-tertiary">
                                    <span class="small fw-bold text-primary font-monospace"><i class="bi bi-calculator me-1"></i> DPP (Dasar Pajak)</span>
                                    <span class="fw-bold font-monospace text-primary fs-6" id="txt_dpp">Rp 0</span>
                                </div>

                                <!-- Total PPN -->
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <span class="small fw-semibold text-body-secondary font-monospace">Total PPN (Per Item)</span>
                                    <span class="fw-bold font-monospace text-body-emphasis" id="txt_pajak_nominal">+ Rp 0</span>
                                    <input type="hidden" name="pajak_nominal" id="input_pajak_nominal" value="0">
                                </div>

                                <!-- Potongan PPh (Pajak Penghasilan) -->
                                <div class="mb-2.5 p-2 bg-body-tertiary rounded-3 border border-body-tertiary">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-semibold text-body-secondary font-monospace"><i class="bi bi-shield-minus me-1 text-danger"></i> Potongan PPh</span>
                                        <span class="fw-bold font-monospace text-danger" id="txt_pph_nominal">- Rp 0</span>
                                        <input type="hidden" name="pph_nominal" id="input_pph_nominal" value="0">
                                    </div>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-sm-6">
                                            <select name="pph_pajak_id" id="create_pph_pajak_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;">
                                                <option value="" data-tarif="0">-- Tanpa PPh (0%) --</option>
                                                @if(isset($pajaks))
                                                    @foreach($pajaks as $p)
                                                        @if(str_contains(strtolower($p->nama_pajak), 'pph') || str_contains(strtolower($p->kode_pajak ?? ''), 'pph'))
                                                            <option value="{{ $p->id }}" data-tarif="{{ $p->tarif }}">{{ $p->nama_pajak }} ({{ (float)$p->tarif }}%)</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="hidden" name="tarif_pph" id="input_tarif_pph" value="0">
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <select name="pph_coa_id" id="create_pph_coa_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Akun Uang Muka PPh / PPh Dibayar Dimuka">
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
                                    <span class="fw-bold font-monospace text-body-emphasis" id="txt_biaya_lain">+ Rp 0</span>
                                    <input type="hidden" name="biaya_lain" id="input_biaya_lain" value="0">
                                </div>

                                <hr class="my-2.5 border-secondary-subtle">

                                <!-- Grand Total (Nilai Faktur) -->
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <span class="small fw-bold text-body-secondary font-monospace">TOTAL FAKTUR</span>
                                    <span class="fw-bold font-monospace text-body-emphasis fs-6" id="txt_grand_total">Rp 0</span>
                                    <input type="hidden" name="grand_total" id="input_grand_total" value="0">
                                </div>

                                <!-- Uang Muka / DP Penjualan -->
                                <div class="p-2.5 mb-2.5 rounded-3 bg-body-tertiary border border-body-tertiary" id="container_uang_muka">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <span class="small fw-bold text-warning font-monospace">
                                            <i class="bi bi-cash-stack me-1"></i> Uang Muka / DP
                                        </span>
                                        <span class="small text-muted font-monospace" id="txt_dp_info" style="font-size:0.72rem;"></span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12 mb-1">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text font-monospace bg-body-secondary fw-semibold">- Rp</span>
                                                <input type="text" name="uang_muka" id="create_uang_muka" inputmode="decimal" autocomplete="off" maxlength="20" class="form-control text-end font-monospace mask-number fw-bold text-danger input-number-formatted" value="0" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label mb-1 text-body-secondary font-monospace" style="font-size: 0.73rem;">
                                                <i class="bi bi-bank me-1"></i>Masuk Ke Kas / Bank:
                                            </label>
                                            <select name="kas_bank_id" id="create_kas_bank_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Rekening Kas / Bank Penerima DP">
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
                                            <select name="uang_muka_coa_id" id="create_uang_muka_coa_id" class="form-select form-select-sm rounded-2 font-monospace" style="font-size: 0.78rem;" title="Akun Uang Muka Penjualan / Deposit Customer">
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
                                    <h4 class="fw-bold font-monospace text-success mb-0" id="txt_sisa_tagihan">Rp 0</h4>
                                    <input type="hidden" name="sisa_tagihan" id="input_sisa_tagihan" value="0">
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
                        <button type="button" class="btn btn-warning text-dark rounded-pill px-3 px-md-4 fw-bold flex-fill flex-sm-grow-0 text-nowrap" id="btn_save_draft">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Simpan Draft
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-3 px-md-4 fw-bold flex-fill flex-sm-grow-0 text-nowrap" id="btn_post_invoice">
                            <i class="bi bi-send me-1"></i> Ajukan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
    $(document).ready(function () {
        let itemIndex = 0;
        const PAJAK_OPTIONS = @json($pajaks);

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildPajakSelectHtml(index, selectedId, selectedTarif) {
            let html = `<select name="items[${index}][ppn_pajak_id]" class="form-select form-select-sm rounded-2 font-monospace input-ppn text-center">`;
            html += `<option value="" data-tarif="0" ${!selectedId ? 'selected' : ''}>Non PPN (0%)</option>`;
            PAJAK_OPTIONS.forEach(function (p) {
                let isSel = (selectedId == p.id) || (!selectedId && parseFloat(selectedTarif) === parseFloat(p.tarif));
                html += `<option value="${p.id}" data-tarif="${p.tarif}" ${isSel ? 'selected' : ''}>${p.nama_pajak} (${p.tarif}%)</option>`;
            });
            html += `</select>`;
            return html;
        }

        function initSelect2() {
            $('#create_customer_id, #create_payment_term_id, #create_pph_pajak_id, #create_pph_coa_id, #create_uang_muka_coa_id, #create_kas_bank_id, #create_tender_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreateInvoice'),
                width: '100%',
                placeholder: '-- Pilih --',
                allowClear: true
            });

            $('#create_sales_order_select_id').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreateInvoice'),
                width: '100%',
                placeholder: '-- Pilih Sales Order (SO) --',
                allowClear: true
            });
        }

        function resetItemsAndCosts() {
            $('#create_sales_order_id').val('');
            $('#create_uang_muka').val('0');
            $('#create_uang_muka_coa_id, #create_kas_bank_id').val('').trigger('change.select2');
            $('#txt_dp_info').text('');
            $('#invoice_costs_body').html(`
                <tr id="row_empty_cost">
                    <td colspan="5" class="text-center py-3 text-body-secondary small">
                        Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                    </td>
                </tr>
            `);
            $('#invoice_items_body').html(`
                <tr id="row_empty_item">
                    <td colspan="7" class="text-center py-4 text-body-secondary small">
                        Pilih <strong>Sales Order (SO)</strong> di atas atau klik tombol <strong>"Tambah Baris Manual"</strong> untuk mengisi item faktur.
                    </td>
                </tr>
            `);
            calculateTotals();
        }

        function loadOptions(customerId = '', autoSelectIfSingle = false) {
            let $soSelect = $('#create_sales_order_select_id');
            $soSelect.html('<option value="">-- Memuat Sales Order... --</option>');
            $soSelect.trigger('change.select2');

            let params = {};
            if (customerId) {
                params.customer_id = customerId;
            }

            $.get("{{ route('modul_marketing.faktur_penjualan.get_sales_orders') }}", params, function (res) {
                let html = '<option value="">-- Pilih Sales Order (SO) --</option>';
                if (res.data && res.data.length > 0) {
                    res.data.forEach(function (soItem) {
                        let custName = soItem.customer_name ? ` - ${soItem.customer_name}` : '';
                        let statusTag = soItem.shipping_status ? ` [${soItem.shipping_status}]` : '';
                        let totalStr = soItem.grand_total ? ` (Rp ${new Intl.NumberFormat('id-ID').format(soItem.grand_total)})` : '';
                        let tenderAttr = soItem.tender_id ? ` data-tender-id="${soItem.tender_id}" data-tender-name="${escapeHtml(soItem.tender_name || '')}"` : '';
                        html += `<option value="${soItem.id}" data-customer-id="${soItem.customer_id}"${tenderAttr}>${soItem.nomor_so}${custName}${statusTag}${totalStr}</option>`;
                    });
                    $soSelect.html(html);
                    $soSelect.trigger('change.select2');

                    // Jika customer dipilih dan hanya memiliki tepat 1 Sales Order aktif, otomatis pilih SO tersebut
                    if (autoSelectIfSingle && customerId && res.data.length === 1) {
                        $soSelect.val(res.data[0].id).trigger('change');
                    }
                } else {
                    if (customerId) {
                        html = '<option value="" disabled selected>-- Tidak Ada SO Tersedia untuk Customer Ini --</option>';
                    } else {
                        html = '<option value="" disabled selected>-- Tidak Ada Sales Order yang Siap Difakturkan --</option>';
                    }
                    $soSelect.html(html);
                    $soSelect.trigger('change.select2');
                }
            }).fail(function () {
                $soSelect.html('<option value="" disabled selected>-- Gagal Memuat Sales Order --</option>');
                $soSelect.trigger('change.select2');
            });
        }

        let isProgrammaticChange = false;

        // When Customer changes manually
        $('#create_customer_id').on('change', function () {
            if (isProgrammaticChange) return;

            let custId = $(this).val() || '';

            // Bersihkan rincian barang, biaya tambahan, tender, dan SO dari customer sebelumnya
            resetItemsAndCosts();
            $('#create_tender_id').val('').trigger('change.select2');

            let paymentTerm = $(this).find(':selected').data('payment-term');
            if (paymentTerm) {
                $('#create_payment_term_id').val(paymentTerm).trigger('change');
            } else {
                $('#create_payment_term_id').val('').trigger('change');
            }

            // Muat opsi SO milik customer baru (otomatis pilih jika cuma ada 1 SO)
            loadOptions(custId, true);
        });

        // Load SO list initially when modal is shown
        $('#modalCreateInvoice').on('shown.bs.modal', function () {
            initSelect2();
            loadOptions($('#create_customer_id').val() || '', false);
        });

        // When Sales Order (SO) is selected
        $('#create_sales_order_select_id').on('change', function () {
            let soId = $(this).val();
            if (!soId) {
                $('#create_sales_order_id').val('');
                $('#create_tender_id').val('').trigger('change.select2');
                resetItemsAndCosts();
                return;
            }

            // Langsung sinkronkan tender secara instan jika sudah ada di option data SO
            let $selectedOpt = $(this).find('option:selected');
            let initialTenderId = $selectedOpt.data('tender-id');
            let initialTenderName = $selectedOpt.data('tender-name');
            if (initialTenderId) {
                if (!$('#create_tender_id option[value="' + initialTenderId + '"]').length) {
                    let opt = new Option(initialTenderName || ('Tender #' + initialTenderId), initialTenderId, true, true);
                    $('#create_tender_id').append(opt);
                }
                $('#create_tender_id').val(initialTenderId).trigger('change.select2');
            }

            isProgrammaticChange = true;

            $.get("{{ url('modul-marketing/faktur-penjualan/so-details') }}/" + soId, function (res) {
                if (res.status === 'success') {
                    let data = res.data;
                    $('#create_sales_order_id').val(data.id || '');
                    if (data.customer_id && $('#create_customer_id').val() != data.customer_id) {
                        $('#create_customer_id').val(data.customer_id).trigger('change.select2');
                    }
                    if (data.payment_term_id) $('#create_payment_term_id').val(data.payment_term_id).trigger('change');
                    $('#create_currency_id').val('1');
                    $('#create_kurs').val('1.0000');
                    if (data.tender_id) {
                        let tenderLabel = data.tender_text || (data.nama_tender ? (data.nama_tender + (data.nomor_kontrak && data.nomor_kontrak !== '-' ? ' (' + data.nomor_kontrak + ')' : '')) : ('Tender #' + data.tender_id));
                        if (!$('#create_tender_id option[value="' + data.tender_id + '"]').length) {
                            let newOpt = new Option(tenderLabel, data.tender_id, true, true);
                            $('#create_tender_id').append(newOpt);
                        }
                        $('#create_tender_id').val(data.tender_id).trigger('change.select2');
                    } else if (!initialTenderId) {
                        $('#create_tender_id').val('').trigger('change.select2');
                    }
                    $('#invoice_costs_body').empty();
                    if (typeof data.biaya_pengiriman !== 'undefined' && data.biaya_pengiriman > 0) {
                        addCostRow({
                            jenis_biaya: 'Ongkos Kirim',
                            nominal: data.biaya_pengiriman,
                            keterangan: 'Biaya Pengiriman SO'
                        });
                    } else {
                        $('#invoice_costs_body').html(`
                            <tr id="row_empty_cost">
                                <td colspan="5" class="text-center py-3 text-body-secondary small">
                                    Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                                </td>
                            </tr>
                        `);
                    }

                    $('#invoice_items_body').empty();

                    if (data.items && data.items.length > 0) {
                        data.items.slice(0, 50).forEach(function (item) {
                            addInvoiceItemRow(item);
                        });
                        if (data.items.length > 50) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Item Dibatasi',
                                text: `Sales Order memiliki ${data.items.length} item. Hanya 50 item pertama yang dimasukkan ke faktur.`,
                                confirmButtonColor: '#059669'
                            });
                        }
                    } else {
                        $('#invoice_items_body').html(`
                            <tr id="row_empty_item">
                                <td colspan="7" class="text-center py-4 text-body-secondary small">
                                    Tidak ada item barang dalam Sales Order ini.
                                </td>
                            </tr>
                        `);
                    }

                    if (typeof data.uang_muka !== 'undefined' && data.uang_muka > 0) {
                        $('#create_uang_muka').val(formatNumber(data.uang_muka));
                        let dpPercentStr = data.dp_persen ? ` | ${data.dp_persen}%` : '';
                        $('#txt_dp_info').text(`(DP SO: Rp ${formatNumber(data.uang_muka)}${dpPercentStr})`);
                    } else {
                        $('#create_uang_muka').val('0');
                        $('#txt_dp_info').text('');
                    }

                    calculateTotals();
                }
                isProgrammaticChange = false;
            }).fail(function() {
                isProgrammaticChange = false;
            });
        });

        // Add manual item row
        $('#btn_add_item_row').on('click', function () {
            addInvoiceItemRow({});
        });

        // Multi-Row Biaya Tambahan Management
        let costIndex = 0;

        function addCostRow(data = {}) {
            if ($('#invoice_costs_body .cost-row').length >= 15) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum Tercapai',
                    text: 'Maksimal rincian biaya tambahan adalah 15 item.',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            $('#row_empty_cost').remove();
            costIndex++;

            let coaOptionsHtml = `<option value="">-- Auto (Pendapatan Ongkir/Lainnya) --</option>`;
            @if(isset($accounts))
                @foreach($accounts as $acc)
                    let isSelCoa_{{ $acc->id }} = (data.coa_id == {{ $acc->id }}) ? 'selected' : '';
                    coaOptionsHtml += `<option value="{{ $acc->id }}" ${isSelCoa_{{ $acc->id }}}>{{ $acc->code }} - {{ $acc->name }}</option>`;
                @endforeach
            @endif

            let jenisBiayaVal = data.jenis_biaya || 'Ongkos Kirim';

            let rowHtml = `
                <tr class="cost-row" data-index="${costIndex}">
                    <td class="col-cost-jenis">
                        <label class="mobile-cell-label d-md-none">Jenis Biaya:</label>
                        <input type="text" name="costs[${costIndex}][jenis_biaya]" class="form-control form-control-sm rounded-2 input-cost-jenis" value="${jenisBiayaVal}" placeholder="Misal: Ongkos Kirim, Asuransi..." required>
                    </td>
                    <td class="col-cost-nominal">
                        <label class="mobile-cell-label d-md-none">Nominal (Rp):</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-monospace small bg-body-secondary text-body-secondary">Rp</span>
                            <input type="text" name="costs[${costIndex}][nominal]" class="form-control form-control-sm rounded-2 font-monospace text-end input-cost-nominal input-number-formatted" value="${formatNumber(data.nominal || 0)}" required>
                        </div>
                    </td>
                    <td class="col-cost-coa">
                        <label class="mobile-cell-label d-md-none">Akun Alokasi (COA):</label>
                        <select name="costs[${costIndex}][coa_id]" class="form-select form-select-sm rounded-2 input-cost-coa">
                            ${coaOptionsHtml}
                        </select>
                    </td>
                    <td class="col-cost-ket">
                        <label class="mobile-cell-label d-md-none">Keterangan (Catatan Opsional):</label>
                        <input type="text" name="costs[${costIndex}][keterangan]" class="form-control form-control-sm rounded-2 input-cost-ket" value="${data.keterangan || ''}" placeholder="Catatan tambahan...">
                    </td>
                    <td class="col-cost-aksi text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-cost-row" title="Hapus Biaya"><i class="bi bi-trash3-fill"></i></button>
                    </td>
                </tr>
            `;

            let $row = $(rowHtml);
            $('#invoice_costs_body').append($row);

            $row.find('.input-cost-coa').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreateInvoice'),
                width: '100%',
                placeholder: '-- Auto (Pendapatan Ongkir/Lainnya) --',
                allowClear: true
            });

            calculateTotals();
        }

        $('#btn_add_cost_row').on('click', function () {
            addCostRow({});
        });

        $(document).on('click', '.btn-remove-cost-row', function () {
            $(this).closest('tr').remove();
            if ($('#invoice_costs_body tr').length === 0) {
                $('#invoice_costs_body').html(`
                    <tr id="row_empty_cost">
                        <td colspan="5" class="text-center py-3 text-body-secondary small">
                            Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                        </td>
                    </tr>
                `);
            }
            calculateTotals();
        });

        $(document).on('input change blur', '.input-cost-nominal', function () {
            calculateTotals();
        });

        function formatNumber(num) {
            if (num === null || num === undefined || num === '') return '0,00';
            let val = parseFloat(num);
            if (isNaN(val)) return '0,00';
            return val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function parseNumber(str) {
            if (typeof str === 'number') return isNaN(str) ? 0 : str;
            if (!str) return 0;
            let s = str.toString().trim();
            if (!s) return 0;

            if (s.includes(',')) {
                s = s.replace(/\./g, '').replace(',', '.');
            } else {
                let parts = s.split('.');
                if (parts.length > 2) {
                    s = s.replace(/\./g, '');
                } else if (parts.length === 2) {
                    if (parts[1].length === 3) {
                        s = s.replace(/\./g, '');
                    }
                }
            }
            return parseFloat(s) || 0;
        }

        $(document).on('focus', '.input-number-formatted', function () {
            let raw = parseNumber($(this).val());
            if (raw === 0) $(this).val('');
            else $(this).val(raw);
        });

        $(document).on('blur', '.input-number-formatted', function () {
            let raw = parseNumber($(this).val());
            $(this).val(formatNumber(raw));
        });

        function addInvoiceItemRow(data = {}) {
            if ($('#invoice_items_body .item-row').length >= 50) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimum Tercapai',
                    text: 'Maksimal rincian barang/jasa adalah 50 baris.',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            $('#row_empty_item').remove();
            itemIndex++;

            let pajakSelectHtml = buildPajakSelectHtml(itemIndex, data.ppn_pajak_id, data.tarif_ppn);

            let satuanNama = data.satuan_nama || 'Pcs';
            let qtySo = data.qty_so !== undefined ? parseNumber(data.qty_so) : 0;
            let qtyDelivered = data.qty_delivered !== undefined ? parseNumber(data.qty_delivered) : 0;
            let qtyFaktur = data.qty !== undefined ? parseNumber(data.qty) : 1;
            let sisaKurang = data.sisa_unshipped !== undefined ? parseNumber(data.sisa_unshipped) : (qtySo > 0 ? Math.max(0, qtySo - qtyDelivered) : 0);

            let safeNama = escapeHtml(data.nama_barang || '');
            let safeKode = escapeHtml(data.kode_barang || '');
            let safeSatuan = escapeHtml(satuanNama);

            let sisaBadgeHtml = '';
            let sisaPillClass = '';
            let sisaPillIcon = '';

            if (qtySo > 0) {
                if (sisaKurang > 0) {
                    sisaPillClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                    sisaPillIcon = 'bi-exclamation-triangle-fill';
                    sisaBadgeHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-1.5 py-0.5" style="font-size:0.65rem;" title="Detail SO: Pesanan ${qtySo}, Terkirim ${qtyDelivered}"><i class="bi bi-truck me-0.5"></i>SO: ${qtyDelivered}/${qtySo} (Kurang ${sisaKurang})</span>`;
                } else {
                    sisaPillClass = 'bg-success-subtle text-success border border-success-subtle';
                    sisaPillIcon = 'bi-check-circle-fill';
                    sisaBadgeHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1.5 py-0.5" style="font-size:0.65rem;" title="Detail SO: Pesanan ${qtySo}, Terkirim ${qtyDelivered}"><i class="bi bi-check2-all me-0.5"></i>SO: ${qtyDelivered}/${qtySo} (Lengkap)</span>`;
                }
            } else {
                sisaPillClass = 'bg-body-secondary text-body-secondary border';
                sisaPillIcon = 'bi-dash-circle';
                sisaBadgeHtml = `<span class="badge bg-body-secondary text-body-secondary border font-monospace px-1.5 py-0.5" style="font-size:0.65rem;">Manual</span>`;
            }

            let namaBarangColHtml = '';
            if (data.nama_barang) {
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
                                <span class="d-md-none create-container-sisa-badge-mobile">
                                    ${sisaBadgeHtml}
                                </span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${itemIndex}][nama_barang]" class="input-nama-barang" value="${safeNama}">
                `;
            } else {
                namaBarangColHtml = `
                    <div class="p-1">
                        <input type="text" name="items[${itemIndex}][nama_barang]" class="form-control form-control-sm rounded-2 input-nama-barang" placeholder="Ketik nama produk / jasa manual..." required>
                    </div>
                `;
            }

            let rowHtml = `
                <tr class="item-row" data-index="${itemIndex}">
                    <td class="col-nama-barang py-2 px-3">
                        <input type="hidden" name="items[${itemIndex}][barang_id]" value="${data.barang_id || ''}">
                        <input type="hidden" name="items[${itemIndex}][satuan_id]" value="${data.satuan_id || ''}">
                        <input type="hidden" name="items[${itemIndex}][tarif_ppn]" class="input-tarif-ppn" value="${data.tarif_ppn || 0}">
                        <input type="hidden" name="items[${itemIndex}][nilai_ppn]" class="input-nilai-ppn" value="${data.nilai_ppn || 0}">
                        <input type="hidden" class="create-data-qty-so" value="${qtySo}">
                        <input type="hidden" class="create-data-qty-delivered" value="${qtyDelivered}">
                        <input type="hidden" class="create-data-satuan-nama" value="${safeSatuan}">
                        ${namaBarangColHtml}
                    </td>
                    <td class="col-qty py-2 text-center align-middle">
                        <label class="mobile-cell-label d-md-none">Jumlah (${safeSatuan}):</label>
                        <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][qty]" class="form-control form-control-sm rounded-2 font-monospace text-center fw-bold input-qty" value="${qtyFaktur}" required style="max-width: 85px; margin: 0 auto;">
                    </td>
                    <td class="col-sisa py-2 text-center align-middle d-none d-md-table-cell">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <span class="badge ${sisaPillClass} rounded-pill px-2.5 py-1 font-monospace fw-bold create-pill-sisa d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.78rem;">
                                <i class="bi ${sisaPillIcon} create-icon-sisa"></i>
                                <span class="create-txt-sisa-val">${sisaKurang > 0 ? sisaKurang + ' Belum Kirim' : (qtySo > 0 ? 'Lunas Kirim' : '-')}</span>
                            </span>
                            <input type="hidden" name="items[${itemIndex}][sisa_pengiriman]" class="create-input-sisa-kurang" value="${sisaKurang}">
                            <div class="mt-1 create-container-sisa-badge">
                                ${sisaBadgeHtml}
                            </div>
                        </div>
                    </td>
                    <td class="col-harga py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Harga Satuan:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text font-monospace bg-body-secondary text-body-secondary py-1 px-1.5" style="font-size:0.72rem;">Rp</span>
                            <input type="text" name="items[${itemIndex}][harga_satuan]" class="form-control form-control-sm rounded-2 font-monospace text-end input-harga input-number-formatted" value="${formatNumber(data.harga_satuan || 0)}" required>
                        </div>
                    </td>
                    <td class="col-diskon py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Diskon (%):</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0" max="100" name="items[${itemIndex}][diskon_persen]" class="form-control form-control-sm rounded-2 font-monospace text-center input-diskon" value="${data.diskon_persen || 0}">
                            <span class="input-group-text font-monospace bg-body-secondary text-body-secondary py-1 px-1.5" style="font-size:0.72rem;">%</span>
                        </div>
                    </td>
                    <td class="col-ppn py-2 align-middle">
                        <label class="mobile-cell-label d-md-none">Pajak PPN:</label>
                        ${pajakSelectHtml}
                    </td>
                    <td class="col-subtotal py-2 align-middle pe-3">
                        <div class="item-subtotal-box d-flex justify-content-between align-items-center p-2 rounded-2">
                            <span class="mobile-subtotal-label d-md-none small fw-semibold text-body-secondary">Subtotal:</span>
                            <span class="fw-bold font-monospace text-success item-subtotal-display">Rp ${formatNumber(data.total_harga || 0)}</span>
                        </div>
                        <input type="hidden" name="items[${itemIndex}][total_harga]" class="input-subtotal" value="${data.total_harga || 0}">
                    </td>
                    <td class="col-aksi text-center align-middle py-2">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger border-0 rounded-circle btn-remove-row" title="Hapus Item"><i class="bi bi-trash3-fill"></i></button>
                    </td>
                </tr>
            `;

            $('#invoice_items_body').append(rowHtml);
            calculateTotals();
        }

        // Remove row
        $(document).on('click', '.btn-remove-row', function () {
            $(this).closest('tr').remove();
            if ($('#invoice_items_body tr').length === 0) {
                $('#invoice_items_body').html(`
                    <tr id="row_empty_item">
                        <td colspan="8" class="text-center py-4 text-body-secondary small">
                            Klik tombol <strong>"Tambah Baris"</strong> untuk mengisi item faktur.
                        </td>
                    </tr>
                `);
            }
            calculateTotals();
        });

        // Dynamic Calculations
        $(document).on('input change blur', '.input-qty, .input-harga, .input-diskon, .input-ppn', function () {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('.input-qty').val()) || 0;
            let harga = parseNumber(row.find('.input-harga').val());
            let diskon = parseFloat(row.find('.input-diskon').val()) || 0;

            let gross = qty * harga;
            let diskonNom = gross * (diskon / 100);
            let net = gross - diskonNom;

            let tarifPpn = parseFloat(row.find('.input-ppn option:selected').data('tarif')) || 0;
            let nilaiPpn = net * (tarifPpn / 100);
            let total = net + nilaiPpn;

            row.find('.input-tarif-ppn').val(tarifPpn);
            row.find('.input-nilai-ppn').val(nilaiPpn.toFixed(2));
            row.find('.input-subtotal').val(total);
            row.find('.item-subtotal-display').text('Rp ' + formatNumber(total));

            calculateTotals();
        });

        $('#input_diskon_persen, #create_pph_pajak_id').on('input change blur', function () {
            calculateTotals();
        });

        // Hanya izinkan angka dan titik/koma desimal pada input Uang Muka (DP) serta batasi maksimal 20 karakter
        $(document).on('keypress', '#create_uang_muka', function (e) {
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

        // Bersihkan karakter non-angka saat input/paste serta potong jika melebihi 20 karakter
        $(document).on('input', '#create_uang_muka', function () {
            let val = $(this).val();
            let sanitized = val.replace(/[^0-9.,]/g, '');
            if (sanitized.length > 20) {
                sanitized = sanitized.substring(0, 20);
            }
            if (val !== sanitized) {
                $(this).val(sanitized);
            }
            calculateTotals();
        });

        // Validasi saat blur / change: DP tidak boleh melebihi total faktur
        $(document).on('change blur', '#create_uang_muka', function () {
            let grandTotal = parseFloat($('#input_grand_total').val()) || 0;
            let uangMuka = parseNumber($(this).val()) || 0;

            if (uangMuka < 0) {
                $(this).val('0,00');
                calculateTotals();
                return;
            }

            if (grandTotal <= 0 && uangMuka > 0) {
                $(this).val('0,00');
                calculateTotals();
                Swal.fire({
                    icon: 'warning',
                    title: 'Total Faktur Rp 0',
                    text: 'Silakan isi rincian barang/jasa terlebih dahulu sebelum mengisi nominal Uang Muka (DP).',
                    confirmButtonColor: '#059669'
                });
                return;
            }

            if (grandTotal > 0 && uangMuka > grandTotal) {
                $(this).val(formatNumber(grandTotal));
                calculateTotals();
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Melebihi Total Faktur!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' + formatNumber(grandTotal) + '). Nominal DP otomatis disesuaikan ke nominal maksimal.',
                    confirmButtonColor: '#059669'
                });
            }
        });

        // Payment Term Due Date calculation
        $('#create_payment_term_id, #create_tanggal_faktur').on('change', function () {
            let termDays = parseInt($('#create_payment_term_id option:selected').data('days')) || 0;
            let tanggalFaktur = $('#create_tanggal_faktur').val();
            if (tanggalFaktur && termDays > 0) {
                let d = new Date(tanggalFaktur);
                d.setDate(d.getDate() + termDays);
                let dueStr = d.toISOString().split('T')[0];
                $('#create_tanggal_jatuh_tempo').val(dueStr);
            }
        });

        function calculateTotals() {
            let totalGross = 0;
            let totalDiskonItem = 0;
            let totalPpn = 0;

            $('.item-row').each(function () {
                let row = $(this);
                let qty = parseFloat(row.find('.input-qty').val()) || 0;
                let harga = parseNumber(row.find('.input-harga').val());
                let diskonPersen = parseFloat(row.find('.input-diskon').val()) || 0;

                let grossItem = qty * harga;
                let diskonItemNominal = grossItem * (diskonPersen / 100);

                totalGross += grossItem;
                totalDiskonItem += diskonItemNominal;

                let nilaiPpn = parseFloat(row.find('.input-nilai-ppn').val()) || 0;
                totalPpn += nilaiPpn;
            });

            let diskonHeaderPersen = parseFloat($('#input_diskon_persen').val()) || 0;
            let diskonHeaderNominal = (totalGross - totalDiskonItem) * (diskonHeaderPersen / 100);
            let totalDiskonKeseluruhan = totalDiskonItem + diskonHeaderNominal;

            let dpp = totalGross - totalDiskonKeseluruhan;

            let tarifPph = parseFloat($('#create_pph_pajak_id option:selected').data('tarif')) || 0;
            let pphNominal = dpp * (tarifPph / 100);

            let totalBiayaLain = 0;
            $('.cost-row').each(function () {
                let nom = parseNumber($(this).find('.input-cost-nominal').val());
                totalBiayaLain += nom;
            });

            let grandTotal = dpp + totalPpn + totalBiayaLain - pphNominal;

            $('#txt_subtotal').text('Rp ' + formatNumber(totalGross));
            $('#input_subtotal').val(totalGross.toFixed(2));

            $('#txt_diskon_nominal').text('- Rp ' + formatNumber(totalDiskonKeseluruhan));
            $('#input_diskon_nominal').val(totalDiskonKeseluruhan.toFixed(2));

            $('#txt_dpp').text('Rp ' + formatNumber(dpp));

            $('#txt_pajak_nominal').text('+ Rp ' + formatNumber(totalPpn));
            $('#input_pajak_nominal').val(totalPpn.toFixed(2));

            $('#txt_pph_nominal').text('- Rp ' + formatNumber(pphNominal));
            $('#input_pph_nominal').val(pphNominal.toFixed(2));
            $('#input_tarif_pph').val(tarifPph);

            $('#txt_biaya_lain').text('+ Rp ' + formatNumber(totalBiayaLain));
            $('#input_biaya_lain').val(totalBiayaLain.toFixed(2));

            let uangMuka = parseNumber($('#create_uang_muka').val()) || 0;
            if (uangMuka < 0) {
                uangMuka = 0;
            }

            // Validasi visual DP tidak boleh melebihi Total Faktur
            if (grandTotal > 0 && uangMuka > grandTotal) {
                $('#create_uang_muka').addClass('is-invalid border-danger');
                $('#txt_dp_info').html('<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>DP melebihi total faktur (Maks Rp ' + formatNumber(grandTotal) + ')</span>');
            } else if (grandTotal <= 0 && uangMuka > 0) {
                $('#create_uang_muka').addClass('is-invalid border-danger');
                $('#txt_dp_info').html('<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Total faktur masih Rp 0</span>');
            } else {
                $('#create_uang_muka').removeClass('is-invalid border-danger');
                if (uangMuka > 0 && grandTotal > 0) {
                    let percent = ((uangMuka / grandTotal) * 100).toFixed(1);
                    $('#txt_dp_info').html('<span class="text-warning font-monospace fw-semibold">(' + percent + '% dari total)</span>');
                } else if (uangMuka === 0) {
                    $('#txt_dp_info').text('');
                }
            }

            let sisaTagihan = Math.max(0, grandTotal - uangMuka);

            $('#txt_grand_total').text('Rp ' + formatNumber(grandTotal));
            $('#input_grand_total').val(grandTotal.toFixed(2));

            $('#txt_sisa_tagihan').text('Rp ' + formatNumber(sisaTagihan));
            $('#input_sisa_tagihan').val(sisaTagihan.toFixed(2));
        }

        function resetForm() {
            let form = $('#formCreateInvoice');
            form[0].reset();

            // Reset action type and SO hidden ID
            $('#invoice_action_type').val('submit');
            $('#create_sales_order_id').val('');
            $('#create_uang_muka').val('0');
            $('#txt_dp_info').text('');

            // Reset Select2 fields
            $('#create_customer_id, #create_sales_order_select_id, #create_payment_term_id, #create_pph_pajak_id, #create_pph_coa_id, #create_uang_muka_coa_id, #create_tender_id').val('').trigger('change.select2');

            // Reset Item Table
            itemIndex = 0;
            $('#invoice_items_body').html(`
                <tr id="row_empty_item">
                    <td colspan="7" class="text-center py-4 text-body-secondary small">
                        Pilih <strong>Surat Jalan (DO)</strong> atau <strong>Sales Order (SO)</strong> di atas atau klik tombol <strong>"Tambah Baris Manual"</strong> untuk mengisi item faktur.
                    </td>
                </tr>
            `);

            // Reset Cost Table
            costIndex = 0;
            $('#invoice_costs_body').html(`
                <tr id="row_empty_cost">
                    <td colspan="5" class="text-center py-3 text-body-secondary small">
                        Belum ada biaya tambahan. Klik <strong>"Tambah Biaya"</strong> jika ada ongkir, asuransi, atau biaya lainnya.
                    </td>
                </tr>
            `);

            // Reset Submit & Draft Buttons State
            let submitBtn = $('#btn_post_invoice');
            submitBtn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Ajukan Approval (Kirim WA)');

            let draftBtn = $('#btn_save_draft');
            draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-arrow-down me-1"></i> Simpan Draft');

            // Reset Currency & Kurs (IDR default)
            $('#create_currency_id').val('1');
            $('#create_kurs').val('1.0000');

            // Recalculate Totals & Summaries
            calculateTotals();

            // Reset File Preview
            $('#create_file_preview_info').addClass('d-none').html('');
        }

        $('#modalCreateInvoice').on('hidden.bs.modal', function () {
            resetForm();
        });

        // Validasi Ukuran File (Max 10MB) & Tampilan Info Kompresi
        $('#create_file_pendukung').on('change', function () {
            let file = this.files[0];
            let $preview = $('#create_file_preview_info');

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

        // Save Draft Button
        $('#btn_save_draft').on('click', function () {
            $('#invoice_action_type').val('draft');
            $('#formCreateInvoice').submit();
        });

        $('#btn_post_invoice').on('click', function () {
            $('#invoice_action_type').val('submit');
        });

        // Submit Form AJAX
        $('#formCreateInvoice').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);

            let fileInput = $('#create_file_pendukung')[0];
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

            let grandTotalVal = parseFloat($('#input_grand_total').val()) || 0;
            let rawDpStr = $('#create_uang_muka').val() ? $('#create_uang_muka').val().toString().trim() : '';
            if (rawDpStr.length > 20) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Input Terlalu Panjang!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi 20 karakter.'
                });
                $('#create_uang_muka').focus();
                return false;
            }

            let uangMukaVal = parseNumber($('#create_uang_muka').val()) || 0;

            if (isNaN(uangMukaVal) || uangMukaVal < 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Tidak Valid!',
                    text: 'Nominal Uang Muka (DP) harus diisi angka yang valid.'
                });
                $('#create_uang_muka').focus();
                return false;
            }

            if (grandTotalVal > 0 && uangMukaVal > grandTotalVal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nominal DP Melebihi Total Faktur!',
                    text: 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' + formatNumber(grandTotalVal) + ').'
                });
                $('#create_uang_muka').focus();
                return false;
            }

            if (uangMukaVal > 0) {
                let kasBankId = $('#create_kas_bank_id').val();
                let uangMukaCoaId = $('#create_uang_muka_coa_id').val();

                if (!kasBankId) {
                    $('#create_kas_bank_id').select2('open');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Karena Faktur Penjualan memiliki nominal Uang Muka (DP), Rekening Kas & Bank penerima wajib dipilih!'
                    });
                    return false;
                }

                if (!uangMukaCoaId) {
                    $('#create_uang_muka_coa_id').select2('open');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian!',
                        text: 'Karena Faktur Penjualan memiliki nominal Uang Muka (DP), Akun Uang Muka Penjualan (COA) wajib dipilih!'
                    });
                    return false;
                }
            }

            // Unformat number inputs for PHP numeric validation
            $('.input-harga, .input-subtotal, .input-cost-nominal').each(function () {
                $(this).val(parseNumber($(this).val()));
            });
            $('#input_biaya_lain').val(parseNumber($('#input_biaya_lain').val()));
            $('#create_uang_muka').val(parseNumber($('#create_uang_muka').val()));

            let formData = new FormData(form[0]);

            // Restore formatted numbers for UI display
            $('.input-harga, .input-subtotal, .input-cost-nominal').each(function () {
                $(this).val(formatNumber($(this).val()));
            });
            $('#input_biaya_lain').val(formatNumber($('#input_biaya_lain').val()));
            $('#create_uang_muka').val(formatNumber($('#create_uang_muka').val()));

            let submitBtn = $('#btn_post_invoice');
            let draftBtn = $('#btn_save_draft');

            let isDraft = $('#invoice_action_type').val() === 'draft';
            if (isDraft) {
                draftBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Draft...');
                submitBtn.prop('disabled', true);
            } else {
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                draftBtn.prop('disabled', true);
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#modalCreateInvoice').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#tableSalesInvoices').DataTable().ajax.reload();
                    resetForm();
                },
                error: function (xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                },
                complete: function () {
                    submitBtn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Ajukan Approval (Kirim WA)');
                    draftBtn.prop('disabled', false).html('<i class="bi bi-file-earmark-arrow-down me-1"></i> Simpan Draft');
                }
            });
        });
    });
</script>
@endpush
