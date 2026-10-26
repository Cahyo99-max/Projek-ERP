{{-- MODAL INPUT / EDIT PENERIMAAN BARANG JADI (HASIL PRODUKSI) - ULTRA SLEEK EXECUTIVE DESIGN --}}
<div class="modal fade" id="modalInputPenerimaanBarangJadi" tabindex="-1" aria-labelledby="modalInputPBJTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable pbj-dialog">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden pbj-modal-container">
            
            {{-- ULTRA SLEEK HEADER --}}
            <div class="modal-header border-0 px-4 py-3.5 position-relative pbj-header-gradient">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 text-white d-flex align-items-center justify-content-center pbj-header-icon-box">
                        <i class="bi bi-box-seam-fill fs-5 text-emerald-light"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold m-0 text-white fs-6" id="modalInputPBJTitle" style="letter-spacing: -0.2px;">
                                Penerimaan Hasil Produksi
                            </h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 pbj-header-badge">PENGAJUAN FG</span>
                        </div>
                        <!-- <small class="text-white-50" style="font-size: 11.5px;">Pengajuan penyerahan hasil barang jadi (Menunggu review & kalkulasi HPP Accounting sebelum masuk stok)</small> -->
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" onclick="closeModalInputPenerimaanBarangJadi()" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-3 p-md-4 custom-scrollbar">
                <form id="formInputPenerimaanBarangJadi" autocomplete="off">
                    @csrf
                    <input type="hidden" id="pbj_edit_id" name="pbj_edit_id" value="">

                    {{-- 1. SPK SELECTION HERO CARD --}}
                    <div class="pbj-card-section mb-4">
                        <div class="pbj-card-section-header d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="pbj-step-badge bg-primary text-white">1</span>
                                <span class="fw-bold spk-text-main small" style="font-size: 13.5px;">Dokumen SPK Produksi</span>
                                <span class="text-danger">*</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="text-muted" style="font-size: 11px;">Status WO:</span>
                                <span id="pbj_info_status_spk" class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 11px;">-</span>
                            </div>
                        </div>

                        <input type="hidden" id="pbj_spk_id" name="spk_id" value="" required>
                        <div class="input-group shadow-xs rounded-3 overflow-hidden mb-3" onclick="bukaModalLookupSpkPbj()" style="cursor: pointer;" title="Klik untuk mencari & memilih dokumen SPK">
                            <input type="text" id="pbj_spk_nomor_display" class="form-control spk-input spk-readonly border-end-0 cursor-pointer fw-semibold text-primary" placeholder="-- Klik di sini untuk Cari Dokumen SPK --" readonly required style="cursor: pointer; font-size: 13px; min-height: 40px;">
                            <button class="btn btn-primary px-3.5 d-inline-flex align-items-center gap-1.5 flex-shrink-0" type="button" onclick="event.stopPropagation(); bukaModalLookupSpkPbj();" title="Cari Dokumen SPK">
                                <i class="bi bi-search"></i>
                                <span class="small fw-semibold">Cari SPK</span>
                            </button>
                        </div>

                        {{-- 4 MINIMALIST METRIC TILES --}}
                        <div id="pbj_spk_highlight_box" class="pt-3 border-top mt-3 d-none" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <div class="row g-2.5 g-md-3 mb-3">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card h-100">
                                        <span class="pbj-metric-sub">PRODUK JADI</span>
                                        <div id="pbj_info_barang_jadi" class="pbj-metric-main text-truncate font-monospace mt-1" title="">-</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card h-100">
                                        <span class="pbj-metric-sub">TARGET QTY</span>
                                        <div id="pbj_info_target_qty" class="pbj-metric-main text-primary font-monospace mt-1">-</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card h-100">
                                        <span class="pbj-metric-sub text-emerald">SDH DITERIMA</span>
                                        <div id="pbj_info_selesai_qty" class="pbj-metric-main text-emerald font-monospace mt-1">-</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="pbj-metric-card h-100">
                                        <span class="pbj-metric-sub text-warning">SISA TARGET</span>
                                        <div id="pbj_info_sisa_target" class="pbj-metric-main text-warning font-monospace mt-1">-</div>
                                    </div>
                                </div>
                            </div>

                            {{-- REAL-TIME PROGRESS BAR --}}
                            <div class="p-2.5 px-3 rounded-3 pbj-progress-track mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 11.5px;">
                                    <span class="spk-text-muted d-flex align-items-center gap-1.5 font-semibold">
                                        <i class="bi bi-graph-up-arrow text-emerald"></i>
                                        <span>Progres Target Produksi</span>
                                    </span>
                                    <span id="pbj_info_progress_percent" class="fw-bold text-emerald font-monospace">0% Selesai</span>
                                </div>
                                <div class="progress" style="height: 7px; border-radius: 99px; background-color: rgba(128,128,128,0.15);">
                                    <div id="pbj_info_progress_bar" class="progress-bar rounded-pill bg-emerald-vibrant" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. KUANTITAS HASIL PRODUKSI (DUAL HERO TILES) --}}
                    <div class="row g-3.5 mb-4">
                        {{-- KUANTITAS DITERIMA (BAIK) --}}
                        <div class="col-12 col-md-6">
                            <div class="pbj-qty-card pbj-qty-good h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <label class="form-label small fw-bold text-emerald mb-0 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Kuantitas Diterima (Baik)</span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" id="btn_max_sisa_pbj" class="btn btn-xs btn-emerald-subtle rounded-pill px-2.5 py-0.5 fw-bold d-none" onclick="fillMaxSisaTargetPbj()" style="font-size: 11px;">
                                        <i class="bi bi-lightning-charge-fill me-0.5"></i> Sisa Target
                                    </button>
                                </div>
                                <div class="input-group shadow-xs rounded-3 overflow-hidden">
                                    <input type="number" step="any" min="0.0001" id="pbj_jumlah_hasil_baik" name="jumlah_hasil_baik" 
                                           class="form-control spk-input text-end fw-bold fs-4 text-emerald font-monospace no-spin-input" 
                                           placeholder="0" value="" autocomplete="off" required oninput="onInputPbjJumlahHasilBaik(this)" onchange="onInputPbjJumlahHasilBaik(this)" style="min-height: 44px;">
                                    <span class="input-group-text bg-emerald-subtle text-emerald-dark pbj_satuan_label fw-bold font-monospace px-3">Pieces</span>
                                </div>
                                <small class="text-muted d-flex align-items-center gap-1.5 mt-2" style="font-size: 11.5px;">
                                    <i class="bi bi-shield-check text-emerald"></i>
                                    <span>Menambah stok barang jadi siap jual di gudang.</span>
                                </small>
                            </div>
                        </div>

                        {{-- KUANTITAS REJECT (AFVAL / BS) --}}
                        <div class="col-12 col-md-6">
                            <div class="pbj-qty-card pbj-qty-reject h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2.5">
                                    <label class="form-label small fw-bold text-danger mb-0 d-flex align-items-center gap-1.5">
                                        <i class="bi bi-x-circle-fill"></i>
                                        <span>Kuantitas Reject (Afval / BS)</span>
                                    </label>
                                    <span class="badge bg-secondary-subtle text-muted rounded-pill px-2 py-0.5 font-monospace" style="font-size: 10px;">Opsional</span>
                                </div>
                                <div class="input-group shadow-xs rounded-3 overflow-hidden">
                                    <input type="number" step="any" min="0" id="pbj_jumlah_reject" name="jumlah_reject" 
                                           class="form-control spk-input text-end fw-bold fs-4 text-danger font-monospace no-spin-input" 
                                           placeholder="0" value="" autocomplete="off" style="min-height: 44px;">
                                    <span class="input-group-text bg-danger-subtle text-danger-dark pbj_satuan_label fw-bold font-monospace px-3">Pieces</span>
                                </div>
                                <small class="text-muted d-flex align-items-center gap-1.5 mt-2" style="font-size: 11.5px;">
                                    <i class="bi bi-info-circle text-danger"></i>
                                    <span>Barang reject/afval tidak masuk persediaan penjualan.</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- 3. INFORMASI BATCH, TANGGAL PEMBUATAN & SERIAL NUMBER --}}
                    <div class="pbj-card-section mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom flex-wrap gap-2" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="pbj-step-badge bg-warning text-dark">2</span>
                                <div>
                                    <span class="fw-bold spk-text-main small d-block" style="font-size: 13.5px;">Informasi Lot / Batch & Traceability</span>
                                    <small class="text-muted" style="font-size: 11px;">Identitas rombongan produksi & nomor seri penjaminan mutu</small>
                                </div>
                            </div>
                            <span class="badge bg-amber-subtle text-amber-emphasis border border-amber-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 10.5px;">
                                <i class="bi bi-qr-code me-1"></i>TRACEABILITY
                            </span>
                        </div>

                        <div class="row g-3 g-md-3.5 mb-3.5">
                            {{-- NOMOR BATCH / LOT (READONLY DARI SPK) --}}
                            <div class="col-12 col-lg-5">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-upc-scan text-primary me-1"></i>Nomor Batch / Lot</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 10px;">Dari SPK</span>
                                </label>
                                <input type="text" id="pbj_nomor_batch" name="nomor_batch" class="form-control spk-input spk-readonly font-monospace fw-bold text-primary shadow-xs" placeholder="-- Otomatis mengikuti SPK --" maxlength="50" style="font-size: 13px; letter-spacing: 0.5px; min-height: 38px;" readonly>
                                <small class="text-muted d-block mt-1.5" style="font-size: 11px;">Otomatis mengikuti Nomor Batch dari SPK yang dipilih.</small>
                            </div>

                            {{-- TANGGAL PEMBUATAN --}}
                            <div class="col-6 col-lg-4">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold d-flex align-items-center justify-content-between text-truncate">
                                    <span class="text-truncate">Tgl Produksi (Mfg)</span>
                                </label>
                                <input type="date" id="pbj_tanggal_produksi" name="tanggal_produksi" class="form-control spk-input rounded-3 font-monospace shadow-xs" value="{{ date('Y-m-d') }}" style="font-size: 12.5px; min-height: 38px;">
                                <small class="text-muted d-block mt-1.5 text-truncate" style="font-size: 11px;">Tgl selesai diproduksi.</small>
                            </div>

                            {{-- TANGGAL KEDALUWARSA --}}
                            <div class="col-6 col-lg-3">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold d-flex align-items-center justify-content-between">
                                    <span class="text-truncate">Expired Date</span>
                                    <span class="text-muted fw-normal" style="font-size: 10px;">(Opsional)</span>
                                </label>
                                <input type="date" id="pbj_tanggal_kedaluwarsa" name="tanggal_kedaluwarsa" class="form-control spk-input rounded-3 font-monospace shadow-xs" style="font-size: 12.5px; min-height: 38px;">
                                <small class="text-muted d-block mt-1.5 text-truncate" style="font-size: 11px;">Masa simpan produk.</small>
                            </div>
                        </div>

                        {{-- SERIAL NUMBER SECTION CARD (READONLY DARI SPK) --}}
                        <div class="pbj-sn-box rounded-3 overflow-hidden border mt-3.5">
                            <div class="pbj-sn-box-header px-3.5 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-1 rounded-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                        <i class="bi bi-upc-scan fs-6"></i>
                                    </div>
                                    <span class="fw-bold spk-text-main" style="font-size: 13px;">
                                        Serial Number (SN) per Unit
                                    </span>
                                    <span id="pbj_sn_count_badge" class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 11px;">0 SN</span>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 ms-auto" style="font-size: 11px;">
                                    <i class="bi bi-link-45deg"></i> Tersinkron dari SPK
                                </span>
                            </div>
                            <div class="p-3 p-md-3.5 pbj-sn-body">
                                <textarea id="pbj_serial_numbers" name="serial_numbers" class="form-control spk-input spk-readonly font-monospace rounded-2 pbj-sn-textarea mb-2.5" rows="5" placeholder="-- Serial Numbers otomatis terisi dari SPK Produksi yang dipilih --" style="font-size: 12px; line-height: 1.6; min-height: 120px;" readonly oninput="updateSnCountDisplay()"></textarea>
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted" style="font-size: 11.5px;">
                                    <span class="d-flex align-items-center gap-1.5">
                                        <i class="bi bi-info-circle-fill text-primary"></i>
                                        <span>Daftar Serial Number ini dicocokkan dengan label fisik yang terpasang dari SPK.</span>
                                    </span>
                                    <span class="badge bg-body-secondary text-secondary border font-monospace px-2.5 py-1 rounded-pill d-none d-sm-inline" style="font-size: 10.5px;">
                                        <i class="bi bi-layers me-1"></i>1 Baris = 1 Item
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. LOKASI, TANGGAL & SERAH TERIMA --}}
                    <div class="pbj-card-section mb-2">
                        <div class="pbj-card-section-header d-flex align-items-center justify-content-between mb-3.5 pb-2.5 border-bottom" style="border-color: var(--spk-border-color, #e2e8f0) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="pbj-step-badge bg-info text-white">3</span>
                                <span class="fw-bold spk-text-main small" style="font-size: 13.5px;">Lokasi Gudang & Petugas Serah Terima</span>
                            </div>
                        </div>

                        <div class="row g-3 g-md-3.5 mb-3.5">
                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold">Tanggal Penerimaan <span class="text-danger">*</span></label>
                                <input type="date" id="pbj_tanggal_penerimaan" name="tanggal_penerimaan" class="form-control spk-input rounded-3 font-monospace" value="{{ date('Y-m-d') }}" required style="font-size: 12.5px; min-height: 38px;">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold">Gudang Tujuan Hasil <span class="text-danger">*</span></label>
                                <input type="hidden" id="pbj_gudang_id" name="gudang_id" value="" required>
                                <div class="input-group shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupGudangPbj()" style="cursor: pointer;" title="Klik untuk memilih gudang">
                                    <input type="text" id="pbj_gudang_nama_display" class="form-control spk-input spk-readonly border-end-0 cursor-pointer fw-semibold text-primary" placeholder="-- Pilih Gudang Tujuan --" readonly required style="cursor: pointer; font-size: 12.5px; min-height: 38px;">
                                    <button class="btn btn-outline-primary px-3 flex-shrink-0" type="button" onclick="event.stopPropagation(); bukaModalLookupGudangPbj();" title="Cari Gudang">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold">Lokasi Rak / Bin <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="hidden" id="pbj_lokasi_rak_id" name="lokasi_rak_id" value="">
                                <div class="input-group shadow-xs rounded-3 overflow-hidden" onclick="bukaModalLookupRakPbj()" style="cursor: pointer;" title="Klik untuk memilih rak">
                                    <input type="text" id="pbj_lokasi_rak_nama_display" class="form-control spk-input spk-readonly border-end-0 cursor-pointer" placeholder="-- Tanpa Rak / Pilih Rak --" readonly style="cursor: pointer; font-size: 12.5px; min-height: 38px;">
                                    <button class="btn btn-outline-info px-3 flex-shrink-0" type="button" onclick="event.stopPropagation(); bukaModalLookupRakPbj();" title="Cari Rak">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 g-md-3.5">
                            <div class="col-12 col-md-6">
                                <label class="form-label small spk-text-muted mb-2 d-flex align-items-center justify-content-between fw-semibold">
                                    <span>Petugas Penerima (Gudang)</span>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 font-monospace" style="font-size: 10px;">
                                        <i class="bi bi-lock-fill me-0.5"></i>User Login
                                    </span>
                                </label>
                                <div class="input-group shadow-xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted">
                                        <i class="bi bi-person-fill-lock text-secondary"></i>
                                    </span>
                                    <input type="text" id="pbj_penerima_nama" name="penerima_nama" class="form-control spk-input spk-readonly border-start-0 fw-semibold" placeholder="Nama petugas gudang..." value="{{ auth()->user()->nama_lengkap }}" maxlength="40" readonly required style="font-size: 12.5px; min-height: 38px;">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold">
                                    Penyerah / Operator QC <span class="text-danger">*</span>
                                </label>
                                <div class="input-group shadow-xs rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted">
                                        <i class="bi bi-person-badge text-primary"></i>
                                    </span>
                                    <input type="text" id="pbj_penyerah_nama" name="penyerah_nama" class="form-control spk-input border-start-0 fw-semibold" placeholder="Ketik nama penyerah / operator QC..." maxlength="40" required style="font-size: 12.5px; min-height: 38px;">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small spk-text-muted mb-2 fw-semibold">Catatan Transaksi <span class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea id="pbj_catatan" name="catatan" class="form-control spk-input rounded-3" rows="2" placeholder="Catatan kondisi hasil produksi, nomor referensi fisik, catatan QC..." maxlength="150" style="font-size: 12px;"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- CINEMATIC FOOTER --}}
            <div class="modal-footer pbj-modal-footer px-4 py-3">
                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                    <span class="small text-muted d-none d-md-flex align-items-center gap-1.5" style="font-size: 11.5px;">
                        <i class="bi bi-shield-check text-emerald fs-6"></i>
                        <span>Pengajuan akan direview Accounting untuk perhitungan HPP & jurnal resmi.</span>
                    </span>
                    <div class="d-flex align-items-center justify-content-end gap-2.5 flex-fill flex-md-grow-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" onclick="closeModalInputPenerimaanBarangJadi()">
                            Batal
                        </button>
                        <button type="button" id="btn_save_pbj" class="btn btn-sm btn-emerald-cinematic rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="submitPenerimaanBarangJadiForm()">
                            <i class="bi bi-send-fill fs-6"></i>
                            <span>Kirim Pengajuan Penerimaan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* MODAL BASE STYLING */
    .pbj-modal-container {
        background-color: var(--spk-card-bg, #ffffff) !important;
        border: 1px solid var(--spk-border-color, #e2e8f0) !important;
    }

    .pbj-header-gradient {
        background: linear-gradient(135deg, #064e3b 0%, #047857 55%, #0f766e 100%);
    }

    .pbj-header-icon-box {
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        width: 44px;
        height: 44px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .pbj-header-badge {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* CARD SECTIONS */
    .pbj-card-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    html[data-bs-theme="dark"] .pbj-card-section,
    .dark-mode .pbj-card-section {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    .pbj-step-badge {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11.5px;
        font-weight: 800;
        font-family: monospace;
    }

    /* QUANTITY HERO TILES */
    .pbj-qty-card {
        border-radius: 14px;
        padding: 1.15rem 1.25rem;
        transition: all 0.2s ease;
    }
    .pbj-qty-good {
        background: rgba(16, 185, 129, 0.04);
        border: 1.5px solid rgba(16, 185, 129, 0.28);
    }
    .pbj-qty-good:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .pbj-qty-reject {
        background: rgba(239, 68, 68, 0.04);
        border: 1.5px solid rgba(239, 68, 68, 0.25);
    }
    .pbj-qty-reject:focus-within {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    .text-emerald { color: #059669 !important; }
    .text-emerald-dark { color: #065f46 !important; }
    .bg-emerald-subtle { background-color: #d1fae5 !important; border-color: #a7f3d0 !important; }
    .btn-emerald-subtle {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        transition: all 0.2s ease;
    }
    .btn-emerald-subtle:hover {
        background-color: #10b981;
        color: #ffffff;
    }

    .text-danger-dark { color: #991b1b !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; border-color: #fecaca !important; }

    .bg-amber-subtle { background-color: #fef3c7 !important; }
    .text-amber-emphasis { color: #92400e !important; }
    .border-amber-subtle { border-color: #fde68a !important; }

    /* SERIAL NUMBER BOX */
    .pbj-sn-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    html[data-bs-theme="dark"] .pbj-sn-box,
    .dark-mode .pbj-sn-box {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .pbj-sn-box-header {
        background: #f8fafc;
    }
    html[data-bs-theme="dark"] .pbj-sn-box-header,
    .dark-mode .pbj-sn-box-header {
        background: rgba(30, 41, 59, 0.6);
    }

    .pbj-sn-body {
        background: rgba(248, 250, 252, 0.6);
    }
    html[data-bs-theme="dark"] .pbj-sn-body,
    .dark-mode .pbj-sn-body {
        background: rgba(15, 23, 42, 0.4);
    }

    .pbj-sn-textarea {
        background: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        transition: all 0.2s ease;
    }
    html[data-bs-theme="dark"] .pbj-sn-textarea,
    .dark-mode .pbj-sn-textarea {
        background: rgba(15, 23, 42, 0.9) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
    }
    .pbj-sn-textarea:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18) !important;
    }

    .btn-emerald-gradient {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        transition: all 0.2s ease;
        letter-spacing: 0.2px;
    }
    .btn-emerald-gradient:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }

    /* METRIC TILES */
    .pbj-metric-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        padding: 0.5rem 0.75rem;
    }
    html[data-bs-theme="dark"] .pbj-metric-card,
    .dark-mode .pbj-metric-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .pbj-metric-sub {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.4px;
        display: block;
        color: var(--spk-text-muted, #64748b);
        margin-bottom: 2px;
    }
    .pbj-metric-main {
        font-size: 13px;
        font-weight: 700;
    }

    .pbj-progress-track {
        background: rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-bs-theme="dark"] .pbj-progress-track,
    .dark-mode .pbj-progress-track {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* NO SPINNER NUMBER INPUT */
    .no-spin-input::-webkit-outer-spin-button,
    .no-spin-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .no-spin-input {
        -moz-appearance: textfield;
    }

    .pbj-modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0 !important;
    }
    html[data-bs-theme="dark"] .pbj-modal-footer,
    .dark-mode .pbj-modal-footer {
        background: rgba(15, 23, 42, 0.8);
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .btn-emerald-cinematic {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff !important;
        border: none;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-emerald-cinematic:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff !important;
        transform: translateY(-1.5px);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    }

    @media (max-width: 576px) {
        .pbj-dialog {
            margin: 0.5rem !important;
            max-width: calc(100% - 1rem) !important;
        }
    }
</style>

@push('scripts')
<script>
    let _activePbjSpkData = null;

    function updateSnCountDisplay() {
        const val = $('#pbj_serial_numbers').val() || '';
        const lines = val.split('\n').map(s => s.trim()).filter(s => s.length > 0);
        const count = lines.length;
        $('#pbj_sn_count_badge').text(`${count} SN`);
        if (count > 0) {
            $('#pbj_sn_count_badge').removeClass('bg-secondary-subtle text-secondary').addClass('bg-primary text-white');
        } else {
            $('#pbj_sn_count_badge').removeClass('bg-primary text-white').addClass('bg-secondary-subtle text-secondary');
        }
    }

    let pbjQtyWarnTimeout = null;
    function onInputPbjJumlahHasilBaik(el) {
        if (_activePbjSpkData) {
            const sisa = Number(_activePbjSpkData.sisa_target || 0);
            const satName = _activePbjSpkData.spk?.satuan?.nama_satuan || (_activePbjSpkData.spk?.barang_jadi?.satuan?.nama_satuan || 'Pcs');
            const inputVal = parseFloat(el.value) || 0;

            if (sisa > 0 && inputVal > sisa) {
                clearTimeout(pbjQtyWarnTimeout);
                pbjQtyWarnTimeout = setTimeout(function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kuantitas Melebihi Sisa Target!',
                        html: `Kuantitas hasil baik (<strong>${inputVal.toLocaleString('id-ID')} ${satName}</strong>) melebihi sisa kuota target SPK yang belum diterima (Maksimal: <strong>${sisa.toLocaleString('id-ID')} ${satName}</strong>).<br><br><span class="text-muted small">Nilai otomatis disesuaikan ke batas maksimal sisa target SPK.</span>`,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'OK, Pahami'
                    });
                }, 300);
                el.value = sisa;
            }
        }
        syncPbjSerialsWithQty();
    }

    function syncPbjSerialsWithQty() {
        if (!_activePbjSpkData) return;
        const qty = parseInt($('#pbj_jumlah_hasil_baik').val()) || 0;
        const spkSerialsRaw = _activePbjSpkData.spk_serial_numbers || '';
        
        if (spkSerialsRaw) {
            const allSn = spkSerialsRaw.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
            if (qty > 0 && allSn.length > 0) {
                const sliced = allSn.slice(0, qty);
                $('#pbj_serial_numbers').val(sliced.join('\n'));
            } else if (qty <= 0) {
                $('#pbj_serial_numbers').val('');
            }
            updateSnCountDisplay();
        }
    }

    function generateAutoBatchNo() {
        if (_activePbjSpkData && _activePbjSpkData.spk && _activePbjSpkData.spk.barang_jadi && _activePbjSpkData.spk.barang_jadi.kode_barang) {
            const bKode = _activePbjSpkData.spk.barang_jadi.kode_barang.replace(/[^A-Za-z0-9\-_]/g, '');
            const todayStr = new Date().toISOString().slice(0,10).replace(/-/g, '');
            $('#pbj_nomor_batch').val(`${bKode}-${todayStr}`);
        } else if (_activePbjSpkData && _activePbjSpkData.suggested_batch_no) {
            $('#pbj_nomor_batch').val(_activePbjSpkData.suggested_batch_no);
        } else {
            const todayStr = new Date().toISOString().slice(0,10).replace(/-/g, '');
            const spkVal = $('#pbj_spk_nomor_display').val() || '';
            const m = spkVal.match(/\[(.*?)\]/);
            const spkCode = m ? m[1].replace(/[^A-Za-z0-9\-_]/g, '') : 'PROD';
            $('#pbj_nomor_batch').val(`LOT-${spkCode}-${todayStr}`);
        }
    }

    function autoGenerateSerialsFromQty() {
        const qty = parseFloat($('#pbj_jumlah_hasil_baik').val()) || 0;
        if (qty <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Kuantitas Belum Diisi',
                text: 'Silakan isi Kuantitas Diterima (Baik) terlebih dahulu untuk membuat Serial Number otomatis.',
                confirmButtonColor: '#1d4ed8'
            });
            $('#pbj_jumlah_hasil_baik').focus();
            return;
        }

        const intQty = Math.floor(qty);
        if (intQty > 500) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Serial Number',
                text: 'Auto-generate dibatasi maksimal 500 SN per sesi penerimaan.',
                confirmButtonColor: '#1d4ed8'
            });
            return;
        }

        const spkId = $('#pbj_spk_id').val();
        if (!spkId) {
            Swal.fire('Info', 'Silakan pilih SPK terlebih dahulu.', 'info');
            return;
        }

        // 1. Prioritaskan ambil Nomor Batch / Lot dari inputan user
        let rawBatch = ($('#pbj_nomor_batch').val() || '').trim();
        if (!rawBatch) {
            generateAutoBatchNo();
            rawBatch = ($('#pbj_nomor_batch').val() || '').trim();
        }

        // Tampilkan loading sebentar saat memeriksa ke database
        Swal.fire({
            title: 'Memeriksa Database & Membuat SN...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/suggest-serials`,
            method: 'GET',
            data: {
                lot: rawBatch,
                qty: intQty
            },
            success: function (res) {
                Swal.close();
                if (res.success && res.serials) {
                    $('#pbj_serial_numbers').val(res.raw_text);
                    updateSnCountDisplay();

                    const sampleStart = res.serials[0];
                    const sampleEnd = res.serials[res.serials.length - 1];
                    const seqInfo = res.last_seq > 0 
                        ? `<br><span class="badge bg-info-subtle text-info-emphasis mt-1.5 p-1.5 font-monospace">Melanjutkan dari urutan #${res.start_seq} s/d #${res.end_seq} (Sesi sebelumnya sudah ada #${res.last_seq})</span>`
                        : `<br><span class="badge bg-success-subtle text-success mt-1.5 p-1.5 font-monospace">Mulai dari urutan #${res.start_seq} s/d #${res.end_seq}</span>`;

                    Swal.fire({
                        icon: 'success',
                        title: 'Serial Number Berhasil Dibuat!',
                        html: `Berhasil membuat <b>${res.total} Serial Number</b> mengikuti Lot <code>${res.prefix}</code>.${seqInfo}<br><small class="text-muted font-monospace mt-2.5 d-block">${sampleStart} ${res.total > 1 ? '&hellip; ' + sampleEnd : ''}</small>`,
                        confirmButtonColor: '#1d4ed8'
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal membuat serial number.', 'error');
                }
            },
            error: function (xhr) {
                Swal.close();
                const err = xhr.responseJSON?.message || 'Gagal menghubungi server untuk generate SN.';
                Swal.fire('Peringatan', err, 'warning');
            }
        });
    }

    window.openModalInputPenerimaanBarangJadi = function(spkId = null) {
        $('#formInputPenerimaanBarangJadi')[0].reset();
        $('#pbj_edit_id').val('');
        $('#pbj_gudang_id').val('');
        $('#pbj_gudang_nama_display').val('');
        $('#pbj_lokasi_rak_id').val('');
        $('#pbj_lokasi_rak_nama_display').val('');
        $('#pbj_nomor_batch').val('');
        $('#pbj_tanggal_produksi').val("{{ date('Y-m-d') }}");
        $('#pbj_tanggal_kedaluwarsa').val('');
        $('#pbj_serial_numbers').val('');
        updateSnCountDisplay();

        $('#pbj_jumlah_hasil_baik').val('');
        $('#pbj_jumlah_reject').val('');
        $('#pbj_penerima_nama').val("{{ auth()->user()->nama_lengkap }}");
        $('#pbj_penyerah_nama').val('');
        $('#pbj_spk_id').val('');
        $('#pbj_spk_nomor_display').val('');
        $('#pbj_info_status_spk').text('-').removeClass('bg-success bg-info bg-secondary text-white text-dark').addClass('bg-secondary-subtle text-secondary');
        $('#modalInputPBJTitle').text('Penerimaan Hasil Produksi');
        $('#pbj_spk_highlight_box').addClass('d-none');
        _activePbjSpkData = null;

        if (spkId) {
            onPbjSpkChanged(spkId);
        }

        const mEl = document.getElementById('modalInputPenerimaanBarangJadi');
        if (mEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(mEl).show();
        } else {
            $('#modalInputPenerimaanBarangJadi').modal('show');
        }
    };

    window.closeModalInputPenerimaanBarangJadi = function() {
        const mEl = document.getElementById('modalInputPenerimaanBarangJadi');
        if (mEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(mEl).hide();
        } else {
            $('#modalInputPenerimaanBarangJadi').modal('hide');
        }
    };

    function onPbjSpkChanged(spkId) {
        if (!spkId) {
            $('#pbj_spk_id').val('');
            $('#pbj_spk_nomor_display').val('');
            $('#pbj_spk_highlight_box').addClass('d-none');
            $('#pbj_info_status_spk').text('-').removeClass('bg-success bg-info text-white text-dark').addClass('bg-secondary-subtle text-secondary');
            _activePbjSpkData = null;
            return;
        }

        $.ajax({
            url: `/produksi/barang-jadi/spk/${spkId}/detail`,
            method: 'GET',
            success: function (res) {
                if (!res.success) return;

                const spk = res.spk;
                _activePbjSpkData = res;

                const bKode = spk.barang_jadi ? spk.barang_jadi.kode_barang : '-';
                const bNama = spk.barang_jadi ? spk.barang_jadi.nama_barang : '-';
                const satName = spk.satuan ? spk.satuan.nama_satuan : (spk.barang_jadi?.satuan?.nama_satuan || 'Pcs');

                $('#pbj_spk_id').val(spk.id);
                $('#pbj_spk_nomor_display').val(`[${spk.nomor_spk}] ${bNama}`);

                const targetQty = Number(spk.jumlah_rencana || res.target_qty || 0);
                const selesaiQty = Number(res.jumlah_selesai || res.total_diterima || 0);
                const sisaTarget = Number(res.sisa_target || Math.max(0, targetQty - selesaiQty));

                let progressPercent = 0;
                let percentDisplay = '0%';
                if (targetQty > 0 && selesaiQty > 0) {
                    const rawPct = (selesaiQty / targetQty) * 100;
                    if (rawPct >= 100) {
                        progressPercent = 100;
                        percentDisplay = '100%';
                    } else if (rawPct < 0.1) {
                        progressPercent = Math.max(1, rawPct);
                        percentDisplay = `${rawPct.toFixed(2)}%`;
                    } else if (rawPct < 1) {
                        progressPercent = Math.max(1, rawPct);
                        percentDisplay = `${rawPct.toFixed(1)}%`;
                    } else {
                        progressPercent = Math.min(100, Math.round(rawPct));
                        percentDisplay = `${progressPercent}%`;
                    }
                }

                $('#pbj_info_barang_jadi').text(`[${bKode}] ${bNama}`).attr('title', `[${bKode}] ${bNama}`);
                $('#pbj_info_target_qty').text(`${targetQty.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_selesai_qty').text(`${selesaiQty.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_sisa_target').text(`${sisaTarget.toLocaleString('id-ID')} ${satName}`);
                $('#pbj_info_progress_percent').text(`${percentDisplay} Selesai`);
                $('#pbj_info_progress_bar').css('width', `${progressPercent}%`).attr('aria-valuenow', progressPercent);

                $('.pbj_satuan_label').text(satName);

                // Auto-fill Batch No, Serials and default warehouse if creating new
                if (!$('#pbj_edit_id').val()) {
                    if (res.spk_nomor_batch) {
                        $('#pbj_nomor_batch').val(res.spk_nomor_batch);
                    } else if (!$('#pbj_nomor_batch').val() && res.suggested_batch_no) {
                        $('#pbj_nomor_batch').val(res.suggested_batch_no);
                    }

                    if (res.spk_serial_numbers) {
                        const snArray = res.spk_serial_numbers.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
                        $('#pbj_serial_numbers').val(snArray.join('\n'));
                        updateSnCountDisplay();
                    }

                    if (res.default_tanggal_produksi) {
                        $('#pbj_tanggal_produksi').val(res.default_tanggal_produksi);
                    }
                    if (!$('#pbj_gudang_id').val() && res.default_gudang_id) {
                        $('#pbj_gudang_id').val(res.default_gudang_id);
                        const dGudangKode = res.default_gudang_kode ? `[${res.default_gudang_kode}] ` : '';
                        $('#pbj_gudang_nama_display').val(`${dGudangKode}${res.default_gudang_nama || 'Gudang Barang Jadi'}`);
                    }
                }

                if (spk.status === 'In Progress') {
                    $('#pbj_info_status_spk').html('<span class="badge spk-badge-cyan rounded-pill px-3 py-1 fw-bold"><i class="bi bi-play-circle-fill me-1"></i>In Progress</span>');
                } else if (spk.status === 'Completed') {
                    $('#pbj_info_status_spk').html('<span class="badge spk-badge-green rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>');
                } else {
                    $('#pbj_info_status_spk').html(`<span class="badge spk-badge-muted rounded-pill px-3 py-1">${spk.status}</span>`);
                }

                if (sisaTarget > 0) {
                    $('#btn_max_sisa_pbj').removeClass('d-none');
                } else {
                    $('#btn_max_sisa_pbj').addClass('d-none');
                }

                // Qty diset kosong saat tambah data baru agar diisi manual oleh user
                if (!$('#pbj_edit_id').val()) {
                    $('#pbj_jumlah_hasil_baik').val('');
                    $('#pbj_jumlah_reject').val('');
                }

                $('#pbj_spk_highlight_box').removeClass('d-none');
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Gagal memuat detail SPK.';
                Swal.fire({
                    title: 'Penerimaan Ditolak!',
                    html: msg,
                    icon: 'error',
                    confirmButtonColor: '#1d4ed8'
                });
                $('#pbj_spk_id').val('');
                $('#pbj_spk_highlight_box').addClass('d-none');
                _activePbjSpkData = null;
                closeModalInputPenerimaanBarangJadi();
            }
        });
    }

    function fillMaxSisaTargetPbj() {
        if (!_activePbjSpkData) return;
        const sisa = Number(_activePbjSpkData.sisa_target || 0);
        if (sisa > 0) {
            $('#pbj_jumlah_hasil_baik').val(sisa);
            syncPbjSerialsWithQty();
            $('#pbj_jumlah_hasil_baik').focus();
        }
    }

    window.editPenerimaanBarangJadi = function(id) {
        Swal.fire({
            title: 'Memuat Data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/produksi/barang-jadi/${id}`,
            method: 'GET',
            success: function (res) {
                Swal.close();
                if (!res.success) return;

                const p = res.penerimaan;
                $('#pbj_edit_id').val(p.id);
                $('#modalInputPBJTitle').text(`Edit Pengajuan Penerimaan #${p.nomor_penerimaan}`);

                onPbjSpkChanged(p.spk_id);
                $('#pbj_tanggal_penerimaan').val(p.tanggal_penerimaan ? p.tanggal_penerimaan.split('T')[0] : '');

                $('#pbj_nomor_batch').val(p.nomor_batch || '');
                $('#pbj_tanggal_produksi').val(p.tanggal_produksi ? p.tanggal_produksi.split('T')[0] : '');
                $('#pbj_tanggal_kedaluwarsa').val(p.tanggal_kedaluwarsa ? p.tanggal_kedaluwarsa.split('T')[0] : '');

                if (p.serials && p.serials.length > 0) {
                    const snList = p.serials.map(s => s.serial_number).join('\n');
                    $('#pbj_serial_numbers').val(snList);
                } else {
                    $('#pbj_serial_numbers').val('');
                }
                updateSnCountDisplay();

                $('#pbj_gudang_id').val(p.gudang_id);
                const gNama = p.gudang ? `[${p.gudang.kode_gudang}] ${p.gudang.nama_gudang}` : '';
                $('#pbj_gudang_nama_display').val(gNama);

                $('#pbj_lokasi_rak_id').val(p.lokasi_rak_id || '');
                if (p.lokasi_rak) {
                    const r = p.lokasi_rak;
                    let rLabel = `[${r.kode_rak}] ${r.nama_rak}`;
                    if (r.level) rLabel += ` - Lantai: ${r.level}`;
                    if (r.baris || r.kolom) rLabel += ` (Brs: ${r.baris || '-'}, Klm: ${r.kolom || '-'})`;
                    $('#pbj_lokasi_rak_nama_display').val(rLabel);
                } else {
                    $('#pbj_lokasi_rak_nama_display').val('');
                }

                $('#pbj_jumlah_hasil_baik').val(parseFloat(p.jumlah_hasil_baik) || 0);
                $('#pbj_jumlah_reject').val(parseFloat(p.jumlah_reject) || 0);
                $('#pbj_penerima_nama').val(p.penerima_nama || '');

                $('#pbj_penyerah_nama').val(p.penyerah_nama || '');
                $('#pbj_catatan').val(p.catatan || '');

                $('#modalDetailBarangJadi').modal('hide');
                $('#modalInputPenerimaanBarangJadi').modal('show');
            },
            error: function () {
                Swal.close();
                Swal.fire('Error', 'Gagal memuat detail transaksi penerimaan.', 'error');
            }
        });
    };

    window.submitPenerimaanBarangJadiForm = function() {
        const form = $('#formInputPenerimaanBarangJadi');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const editId = $('#pbj_edit_id').val();
        const url = editId ? `/produksi/barang-jadi/${editId}` : '/produksi/barang-jadi';
        const method = editId ? 'PUT' : 'POST';

        const submitBtn = $('#btn_save_pbj');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function (res) {
                if (res.success) {
                    $('#modalInputPenerimaanBarangJadi').modal('hide');
                    reloadTableBarangJadi();

                    const canApprove = @json(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('closing-penerimaan-barang-jadi'));
                    const pbjId = res.penerimaan_id;

                    if (!editId && canApprove && pbjId) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Berhasil Dicatat!',
                            html: `
                                <div class="text-start p-1">
                                    <p class="mb-2 text-secondary fw-semibold" style="font-size: 13px;">${res.message}</p>
                                    <div class="p-3 rounded-3 border" style="background-color: #fef3c7; border-color: #fde68a !important; color: #92400e;">
                                        <div class="d-flex align-items-center gap-2 mb-1 fw-bold" style="font-size: 13px;">
                                            <i class="bi bi-hourglass-split text-warning fs-5"></i>
                                            <span>Status: Menunggu Kalkulasi HPP & Approval</span>
                                        </div>
                                        <div style="font-size: 12px; line-height: 1.5;">
                                            Sebagai pemegang hak otorisasi HPP, Anda dapat langsung menghitung HPP (Upah & Overhead), menerbitkan jurnal resmi, dan memutakhirkan stok gudang sekarang.
                                        </div>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-calculator-fill me-1.5"></i> Hitung HPP & Approve Sekarang',
                            cancelButtonText: 'Tutup / Nanti Saja',
                            confirmButtonColor: '#059669',
                            cancelButtonColor: '#64748b',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                bukaModalApproveHppPbj(pbjId);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: editId ? 'Berhasil Diperbarui!' : 'Pengajuan Berhasil Dikirim!',
                            text: res.message,
                            timer: 2500,
                            showConfirmButton: false
                        });
                    }
                }
            },
            error: function (err) {
                const msg = err.responseJSON?.message || 'Gagal memproses data penerimaan barang jadi.';
                Swal.fire('Gagal!', msg, 'error');
            },
            complete: function () {
                submitBtn.prop('disabled', false).html('<i class="bi bi-send-fill fs-6 me-1"></i> Kirim Pengajuan Penerimaan');
            }
        });
    };

    window.terimaBarangJadiSpk = function(spkId) {
        openModalInputPenerimaanBarangJadi(spkId);
    };
</script>
@endpush
