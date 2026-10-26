{{-- MODAL EDIT SPK PRODUKSI --}}
<div class="modal fade spk-modal-primary" id="modalEditSpk" tabindex="-1" aria-labelledby="modalEditSpkLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-amber py-2.5 py-sm-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto" style="min-width: 0;">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.18);">
                        <i class="bi bi-pencil-square fs-5 fs-sm-4"></i>
                    </div>
                    <div class="text-truncate">
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-sm-5 text-truncate" id="modalEditSpkLabel">Perbarui SPK Produksi</h5>
                        <small class="text-white-50 d-none d-sm-block">Sesuaikan BOM acuan, target qty, tahapan proses, deadline, atau instruksi kerja</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- FORM EDIT --}}
            <form id="formEditSpk">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_spk_id">
                <div class="modal-body spk-modal-body p-3 p-sm-4">

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 1: INFORMASI UTAMA & RESEP ACUAN                     --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-3 mb-sm-4">
                        <div class="spk-card-header border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main fs-6">
                                    <i class="bi bi-journal-text me-2 text-primary"></i>Informasi Utama & Resep Acuan
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Ubah Resep BOM atau Target Qty untuk menghitung ulang kebutuhan bahan</small>
                            </div>
                        </div>
                        <div class="card-body p-3 p-sm-4">
                            <div class="row g-3">
                                {{-- Nomor SPK --}}
                                <div class="col-12 col-md-4 col-xl-3">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-hash text-primary me-1"></i>Nomor SPK
                                    </label>
                                    <input type="text" id="edit_nomor_spk"
                                           class="form-control spk-input spk-readonly fw-bold font-monospace" readonly>
                                </div>

                                {{-- Acuan Resep BOM --}}
                                <div class="col-12 col-md-8 col-xl-9">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-box-seam text-primary me-1"></i>Acuan Resep BOM (Approved) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs">
                                        <input type="hidden" name="bom_id" id="edit_bom_id" required>
                                        <input type="hidden" name="tender_id" id="edit_tender_id">
                                        <input type="hidden" name="barang_jadi_id" id="edit_barang_jadi_id">
                                        <input type="hidden" name="satuan_id" id="edit_satuan_id">
                                        <input type="text" id="edit_bom_name_display"
                                               class="form-control spk-input spk-readonly rounded-start-3 fw-semibold"
                                               placeholder="-- Klik 🔍 untuk memilih/mengganti Resep BOM --"
                                               readonly required onclick="bukaModalLookupBomSpk('edit')">
                                        <button type="button" class="btn btn-primary rounded-end-3 px-3 fw-bold" onclick="bukaModalLookupBomSpk('edit')">
                                            <i class="bi bi-search me-1"></i> Cari BOM
                                        </button>
                                    </div>
                                    <div id="edit_disp_tender_info" class="mt-2 d-none">
                                        <span class="badge rounded-pill px-3 py-1.5 fw-semibold" style="background:rgba(139,92,246,0.12);color:#7c3aed;border:1px solid rgba(139,92,246,0.3);font-size:12px;">
                                            <i class="bi bi-file-earmark-text-fill me-1"></i> Terkait Tender / Kontrak: <strong id="edit_lbl_tender_name"></strong>
                                        </span>
                                    </div>
                                </div>

                                {{-- Barang Jadi + Target Qty --}}
                                <div class="col-12 col-md-5 col-xl-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-box2 text-primary me-1"></i>Produk Barang Jadi (Hasil)
                                    </label>
                                    <input type="text" id="edit_barang_jadi_display"
                                           class="form-control spk-input spk-readonly fw-bold text-primary" readonly>
                                </div>
                                <div class="col-12 col-md-3 col-xl-2">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calculator text-primary me-1"></i>Target Qty Produksi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group shadow-xs">
                                        <input type="number" step="1" min="1" name="jumlah_rencana" id="edit_jumlah_rencana"
                                               class="form-control spk-input fw-extrabold text-center text-primary fs-5"
                                               placeholder="0" required oninput="onInputSpkEditTargetQty()">
                                        <span class="input-group-text spk-input-group-text fw-semibold" id="edit_label_satuan_spk">Satuan</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2 col-xl-3">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calendar-event text-primary me-1"></i>Tanggal Terbit <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_spk" id="edit_tanggal_spk"
                                           class="form-control spk-input" required>
                                </div>
                                <div class="col-12 col-md-2 col-xl-3">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-calendar-check text-primary me-1"></i>Target Selesai
                                    </label>
                                    <input type="date" name="target_selesai" id="edit_target_selesai"
                                           class="form-control spk-input">
                                </div>

                                {{-- Tipe, Prioritas, Mandor --}}
                                <div class="col-12 col-md-4 col-xl-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-diagram-2 text-primary me-1"></i>Tipe SPK
                                    </label>
                                    <select name="tipe_spk" id="edit_tipe_spk" class="form-select spk-select">
                                        <option value="Make to Stock">Make to Stock (Stok Gudang)</option>
                                        <option value="Make to Order">Make to Order (Pesanan/Tender)</option>
                                        <option value="Custom/Sampel">Custom / Sampel (Prototype)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 col-xl-4">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-flag text-primary me-1"></i>Prioritas Produksi
                                    </label>
                                    <select name="prioritas" id="edit_prioritas" class="form-select spk-select">
                                        <option value="Normal">Normal (Standard Process)</option>
                                        <option value="Tinggi (Urgent)">Tinggi (Urgent / Prioritas Utama)</option>
                                        <option value="Rendah">Rendah (Fleksibel)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 col-xl-4">
                                     <label class="form-label fw-semibold spk-text-muted small">
                                         <i class="bi bi-person-badge text-primary me-1"></i>PIC / Penanggung Jawab <span class="text-danger">*</span>
                                     </label>
                                     <select name="penanggung_jawab_id" id="edit_penanggung_jawab_id" class="form-select spk-select" required>
                                         <option value="">-- Pilih PIC Produksi --</option>
                                        @foreach($picUsers as $pic)
                                            <option value="{{ $pic->id }}">{{ $pic->nama_lengkap }} {{ $pic->position ? '('.$pic->position->nama_posisi.')' : ($pic->kode_karyawan ? '('.$pic->kode_karyawan.')' : '') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold spk-text-muted small">
                                        <i class="bi bi-card-text text-primary me-1"></i>Instruksi Khusus / Catatan Pengerjaan
                                    </label>
                                    <textarea name="keterangan" id="edit_keterangan_spk" rows="2"
                                              class="form-control spk-input"
                                              placeholder="Catatan khusus pemotongan, warna benang, bordir, kemasan..."></textarea>
                                </div>

                                {{-- Nomor Batch / Lot & Serial Numbers (SN) --}}
                                <div class="col-12 col-lg-5">
                                    <div class="p-3 p-md-3.5 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: rgba(59, 130, 246, 0.03); border-color: rgba(59, 130, 246, 0.2) !important;">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                                                <label class="form-label fw-bold spk-text-main mb-0 small text-truncate">
                                                    <i class="bi bi-upc-scan text-primary me-1.5"></i>Nomor Batch / Lot
                                                </label>
                                                <button type="button" id="btnAutoLotEditSpk" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 text-nowrap flex-shrink-0" style="font-size:11.5px;" onclick="generateSpkEditBatchNo()" title="Generate Nomor Batch Otomatis">
                                                    <i class="bi bi-magic me-1"></i>Auto Lot
                                                </button>
                                            </div>
                                            <input type="text" name="nomor_batch" id="edit_nomor_batch"
                                                   class="form-control spk-input fw-bold font-monospace mb-2"
                                                   placeholder="Ketik manual atau klik Auto Lot (Maks. 50 Karakter)" maxlength="50" oninput="handleSpkEditBatchNoInput(this)" style="font-size: 13px; min-height: 38px;">
                                        </div>
                                        <div class="form-text small spk-text-muted mt-auto" style="font-size:11px; line-height: 1.4;">
                                            <i class="bi bi-info-circle me-1 text-primary"></i>Nomor Lot dicetak di SPK & diteruskan ke Penerimaan Barang Jadi (Maks. 50 Karakter).
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-7">
                                    <div class="p-3 p-md-3.5 rounded-3 border h-100" style="background: rgba(16, 185, 129, 0.03); border-color: rgba(16, 185, 129, 0.2) !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                            <label class="form-label fw-bold spk-text-main mb-0 small">
                                                <i class="bi bi-qr-code text-success me-1.5"></i>Serial Number (SN) / Barcode Produk
                                            </label>
                                            <div class="d-flex align-items-center gap-1.5 ms-auto">
                                                <button type="button" id="btnGenerateSnEditSpk" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap d-inline-flex align-items-center gap-1" style="font-size:11.5px;" onclick="generateSpkEditSerials(true)" title="Generate Serial Numbers sesuai Target Qty">
                                                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                                                    <span>Generate SN</span>
                                                </button>
                                                <button type="button" id="btnClearSnEditSpk" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" style="font-size:11.5px;" onclick="clearSpkEditSerials()" title="Kosongkan">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <textarea name="serial_numbers" id="edit_serial_numbers" rows="5"
                                                  class="form-control spk-input font-monospace small mb-2 custom-scrollbar"
                                                  placeholder="Scan barcode atau isi daftar SN (1 nomor per baris/koma)&#10;Contoh:&#10;LOT-BRG-20260914-001&#10;LOT-BRG-20260914-002&#10;LOT-BRG-20260914-003..." style="font-size: 12px; line-height: 1.6; min-height: 140px; resize: vertical;" oninput="updateSpkEditSnCount()"></textarea>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <span class="small spk-text-muted" style="font-size:11px;">
                                                <i class="bi bi-tag me-1 text-success"></i>Dibuat untuk penempelan barcode label saat proses pengerjaan.
                                            </span>
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border font-monospace px-2.5 py-1" id="edit_disp_sn_count" style="font-size:11px;">0 SN terdaftar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 2: TAHAPAN PRODUKSI (CHECKLIST INTERAKTIF EDIT)    --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-3 mb-sm-4">
                        <div class="spk-card-header border-bottom py-2.5 py-sm-3 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main fs-6">
                                    <i class="bi bi-diagram-3-fill me-2 text-primary"></i>Pilih Tahapan Proses Produksi <span class="text-danger">*</span>
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Centang tahapan yang berlaku untuk SPK ini. Urutan otomatis muncul saat dicentang.</small>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 gap-sm-2 ms-auto">
                                <button type="button" id="btnEditPilihSemuaTahapan" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 px-sm-3 py-1" style="font-size:12px;" onclick="pilihSemuaTahapanEdit()">
                                    <i class="bi bi-check-all me-1"></i> Pilih Semua
                                </button>
                                <button type="button" id="btnEditHapusSemuaTahapan" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 px-sm-3 py-1" style="font-size:12px;" onclick="hapusSemuaTahapanEdit()">
                                    <i class="bi bi-x me-1"></i> Batal Semua
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3 p-sm-4">
                            {{-- GRID TAHAPAN CARDS (MAX 3 BARIS DENGAN SCROLL) --}}
                            <div class="tahapan-scroll-wrapper custom-scrollbar">
                                <div id="editGridTahapanSpk" class="row g-3">
                                    <div class="col-12 text-center py-3 spk-text-muted small">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Memuat daftar tahapan...
                                    </div>
                                </div>
                            </div>

                            {{-- PREVIEW URUTAN YANG DIPILIH EDIT --}}
                            <div id="editPreviewTahapanDipilih" class="mt-4 d-none">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="fw-bold spk-text-main small">Urutan Tahapan yang Akan Digunakan:</span>
                                </div>
                                <div id="editListTahapanDipilih" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    {{-- SECTION 3: BIAYA & ANALISIS MATERIAL                        --}}
                    {{-- ═══════════════════════════════════════════════════════════ --}}
                    <div class="spk-card border-0 rounded-4 shadow-xs mb-4 overflow-hidden">
                        <div class="spk-card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 spk-text-main">
                                    <i class="bi bi-stack me-2 text-primary"></i> Analisis Kebutuhan Bahan Baku & Stok Gudang
                                </h6>
                                <small class="spk-text-muted d-none d-sm-block">Kalkulasi ulang otomatis dari Resep BOM & Target Qty</small>
                            </div>
                            <div id="editBadgeStatusStokKeseluruhan" class="d-none">
                                <span class="badge stock-pill-sufficient rounded-pill px-3 py-1.5 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Semua Stok Cukup
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 350px; overflow-x: auto; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                                <table class="table table-hover align-middle mb-0 text-nowrap spk-table" id="editTblSpkMaterials" style="min-width: 760px;">
                                    <thead>
                                        <tr class="small text-uppercase fw-bold">
                                            <th class="text-center sticky-top bg-body" style="width:45px;">No</th>
                                            <th class="sticky-top bg-body">Bahan Baku</th>
                                            <th class="text-center sticky-top bg-body">Kebutuhan/Unit</th>
                                            <th class="text-center sticky-top bg-body">Waste %</th>
                                            <th class="text-center sticky-top bg-body" title="Rumus: (Target SPK ÷ Target BOM) × Qty Resep × (1 + Waste %)">
                                                Total Kebutuhan <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-center sticky-top bg-body">Stok Gudang</th>
                                            <th class="text-center sticky-top bg-body" title="Status ketersediaan stok fisik gudang">
                                                Status <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-end sticky-top bg-body" title="HPP acuan dari master barang / resep BOM">
                                                HPP/Satuan <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                            <th class="text-end sticky-top bg-body" title="Rumus: Total Kebutuhan × HPP / Satuan">
                                                Est. Subtotal <i class="bi bi-info-circle text-primary" style="font-size: 11px;"></i>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="editTbodySpkMaterials">
                                        <tr>
                                            <td colspan="9" class="text-center py-4 spk-text-muted small">Memuat bahan baku...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- PANDUAN RUMUS KALKULASI OTOMATIS SPK --}}
                    <div class="p-2.5 px-3 rounded-3 mb-3 border d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2" style="background: rgba(37,99,235,0.04); border-color: rgba(37,99,235,0.2) !important; font-size: 11.5px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                <i class="bi bi-calculator me-1"></i> Rumus Kalkulasi SPK
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 spk-text-muted small">
                            <span><strong class="text-primary">Kebutuhan Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(37,99,235,0.08); color:var(--spk-accent);">Target SPK × Qty Resep × (1 + Waste%)</code></span>
                            <span><strong class="text-success">Subtotal Bahan:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(16,185,129,0.08); color:#059669;">Total Kebutuhan × HPP</code></span>
                            <span><strong class="text-warning">Grand Total SPK:</strong> <code class="px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.08); color:#d97706;">Bahan + TK + Overhead</code></span>
                        </div>
                    </div>

                    {{-- SECTION 4: ESTIMASI BIAYA (2x2 Grid di HP) --}}
                    <div class="row g-2 g-sm-3">
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Total Biaya Bahan</span>
                                <h5 class="fw-bold text-primary mb-0 mt-1 fs-6 fs-sm-5" id="edit_disp_total_bahan">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Σ Subtotal Seluruh Bahan</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Biaya Tenaga Kerja</span>
                                <h5 class="fw-bold spk-text-main mb-0 mt-1 fs-6 fs-sm-5" id="edit_disp_total_tk">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Target SPK × Tarif TK BOM</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="spk-cost-card p-3 rounded-3">
                                <span class="spk-cost-label small text-uppercase">Biaya Overhead</span>
                                <h5 class="fw-bold spk-text-main mb-0 mt-1 fs-6 fs-sm-5" id="edit_disp_total_oh">Rp 0</h5>
                                <span class="text-muted d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Target SPK × Tarif OH BOM</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 text-white shadow-xs" style="background: linear-gradient(135deg, #1e40af, #2563eb);">
                                <span class="text-white-50 fw-bold text-uppercase d-block" style="font-size: 10px;">Est. Total Biaya SPK</span>
                                <h5 class="fw-extrabold text-white mb-0 mt-1 fs-6 fs-sm-5" id="edit_disp_grand_total">Rp 0</h5>
                                <span class="text-white-50 d-block mt-1" style="font-size: 9.5px;"><i class="bi bi-calculator me-1"></i>Bahan + TK + Overhead</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer spk-modal-footer d-flex flex-column-reverse flex-sm-row justify-content-sm-between align-items-stretch align-items-sm-center gap-2 p-3 pb-3 p-sm-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btnSimpanUpdateSpk" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2.5 shadow-sm w-100 w-sm-auto order-1 order-sm-2 mb-1 mb-sm-0" onclick="submitFormEditSpk()">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editLoadedBomMaterials = [];
    let editAllMasterTahapans = [];
    let editSelectedTahapans = [];

    function editSpk(id) {
        Swal.fire({ title: 'Memuat data SPK...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: `/produksi/spk/${id}`,
            method: 'GET',
            success: function(res) {
                Swal.close();
                const s = res.spk;
                $('#edit_spk_id').val(s.id);
                $('#edit_nomor_spk').val(s.nomor_spk);
                
                $('#edit_bom_id').val(s.bom_id || '');
                $('#edit_barang_jadi_id').val(s.barang_jadi_id || '');
                $('#edit_satuan_id').val(s.satuan_id || '');
                
                const bomKode = s.bom ? s.bom.kode_bom : '';
                const bomNama = s.bom ? s.bom.nama_bom : '';
                $('#edit_bom_name_display').val(s.bom ? `[${bomKode}] ${bomNama}` : '-');

                const t = s.tender || (s.bom ? s.bom.tender : null);
                if (t) {
                    $('#edit_tender_id').val(t.id || s.tender_id || '');
                    $('#edit_lbl_tender_name').text(t.nama_tender || '');
                    $('#edit_disp_tender_info').removeClass('d-none');
                } else {
                    $('#edit_tender_id').val('');
                    $('#edit_disp_tender_info').addClass('d-none');
                }

                const bjKode = s.barang_jadi ? s.barang_jadi.kode_barang : '';
                const bjNama = s.barang_jadi ? s.barang_jadi.nama_barang : '';
                $('#edit_barang_jadi_display').val(s.barang_jadi ? `[${bjKode}] ${bjNama}` : '-');

                $('#edit_jumlah_rencana').val(parseFloat(s.jumlah_rencana || 0));
                $('#edit_label_satuan_spk').text(s.satuan ? s.satuan.nama_satuan : (s.barang_jadi && s.barang_jadi.satuan ? s.barang_jadi.satuan.nama_satuan : 'Satuan'));
                
                $('#edit_tanggal_spk').val(s.tanggal_spk ? s.tanggal_spk.substring(0, 10) : '');
                $('#edit_target_selesai').val(s.target_selesai ? s.target_selesai.substring(0, 10) : '');
                $('#edit_tipe_spk').val(s.tipe_spk || 'Make to Stock');
                $('#edit_prioritas').val(s.prioritas || 'Normal');
                $('#edit_penanggung_jawab_id').val(s.penanggung_jawab_id || '');
                $('#edit_keterangan_spk').val(s.keterangan || '');
                $('#edit_nomor_batch').val(s.nomor_batch || '');
                $('#edit_serial_numbers').val(s.serial_numbers || '');
                updateSpkEditSnCount();

                // Load Master Tahapan & set existing selected tahapans
                loadMasterTahapansForEditSpk(s.bom_id, s.tahapans || [], s.bom ? s.bom.nama_bom : '', s.bom ? s.bom.kode_bom : '');

                // Re-calculate / render materials & cost cards
                hitungUlangBahanEditSpk();

                $('#modalEditSpk').modal('show');
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak dapat memuat data SPK.', 'error');
            }
        });
    }

    function loadMasterTahapansForEditSpk(bomId, existingTahapans = [], bomNama = '', bomKode = '') {
        if (!bomId) {
            $('#editGridTahapanSpk').html('<div class="col-12 text-center py-4 spk-text-muted small">Pilih Resep BOM terlebih dahulu.</div>');
            return;
        }

        $('#btnEditPilihSemuaTahapan, #btnEditHapusSemuaTahapan').prop('disabled', true);
        $('#btnSimpanUpdateSpk').prop('disabled', true);
        $('#editPreviewTahapanDipilih').addClass('d-none');

        $('#editGridTahapanSpk').html(`
            <div class="col-12 text-center py-3 spk-text-muted small">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Memuat alur tahapan produksi BOM...
            </div>
        `);

        $.ajax({
            url: '{{ route("produksi.master-tahapan.all-active") }}',
            method: 'GET',
            data: { bom_id: bomId },
            success: function(res) {
                const bName = res.nama_bom || bomNama || ('BOM #' + bomId);
                const bKode = res.kode_bom || bomKode || '';
                const bLabel = bKode ? `[${bKode}] ${bName}` : bName;

                if (!res.has_tahapan || !res.tahapans || res.tahapans.length === 0) {
                    editAllMasterTahapans = [];
                    editSelectedTahapans = [];

                    $('#editGridTahapanSpk').html(`
                        <div class="col-12">
                            <div class="alert alert-warning border-0 rounded-4 p-3.5 p-sm-4 mb-0 shadow-xs" style="background: rgba(245,158,11,0.09); border: 1.5px dashed rgba(245,158,11,0.4) !important;">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle p-2.5 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245,158,11,0.2);">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-warning mb-1" style="font-size: 15px;">
                                            Tahapan Produksi Belum Dibuat!
                                        </h6>
                                        <p class="spk-text-muted small mb-3 leading-relaxed">
                                            Resep BOM <strong class="text-dark">${bLabel}</strong> belum memiliki konfigurasi tahapan produksi di Master Tahapan.
                                        </p>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <a href="{{ route('produksi.master-tahapan.index') }}" target="_blank" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold text-dark shadow-xs">
                                                <i class="bi bi-plus-circle-fill me-1"></i> Buat Tahapan di Master Tahapan
                                            </a>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold" onclick="bukaModalLookupBomSpk('edit')">
                                                <i class="bi bi-arrow-repeat me-1"></i> Ganti Resep BOM Lain
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    $('#btnEditPilihSemuaTahapan, #btnEditHapusSemuaTahapan').prop('disabled', true);
                    $('#btnSimpanUpdateSpk').prop('disabled', true);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Tahapan Produksi Belum Dibuat!',
                        html: `Resep BOM <strong>${bLabel}</strong> belum dibuatkan tahapan produksi.<br><br><span class="text-muted small">Silakan buat tahapan produksi terlebih dahulu di menu <strong>Master Tahapan Produksi</strong> agar SPK dapat disimpan.</span>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-plus-circle me-1"></i> Buat Tahapan Sekarang',
                        cancelButtonText: 'Pilih BOM Lain',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('{{ route("produksi.master-tahapan.index") }}', '_blank');
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            bukaModalLookupBomSpk('edit');
                        }
                    });
                    return;
                }

                editAllMasterTahapans = res.tahapans;
                editSelectedTahapans = [];

                // Match existing stages from SPK
                if (existingTahapans && existingTahapans.length > 0) {
                    existingTahapans.sort((a, b) => (a.urutan || 0) - (b.urutan || 0)).forEach(existing => {
                        const idx = editAllMasterTahapans.findIndex(m => m.nama_tahapan === existing.nama_tahapan);
                        if (idx !== -1) {
                            editSelectedTahapans.push({
                                nama_tahapan: existing.nama_tahapan,
                                urutan: editSelectedTahapans.length + 1,
                                cardIndex: idx
                            });
                        }
                    });
                }

                // If no matching existing stages (e.g. BOM changed or new), select all stages from this BOM by default
                if (editSelectedTahapans.length === 0) {
                    editSelectedTahapans = editAllMasterTahapans.map((t, idx) => ({
                        nama_tahapan: t.nama_tahapan,
                        urutan: idx + 1,
                        cardIndex: idx
                    }));
                }

                $('#btnEditPilihSemuaTahapan, #btnEditHapusSemuaTahapan').prop('disabled', false);
                $('#btnSimpanUpdateSpk').prop('disabled', false);

                renderEditTahapanGridInteractive();
            },
            error: function() {
                $('#editGridTahapanSpk').html('<div class="col-12 text-center py-3 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat tahapan dari server.</div>');
            }
        });
    }

    function renderEditTahapanGridInteractive() {
        if (!editAllMasterTahapans || editAllMasterTahapans.length === 0) {
            $('#editGridTahapanSpk').html('<div class="col-12 text-center py-3 spk-text-muted small">Belum ada tahapan produksi untuk resep BOM ini.</div>');
            return;
        }

        let html = '';
        editAllMasterTahapans.forEach((t, idx) => {
            const tipeLabel = t.tipe_spk 
                ? `<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2" style="font-size:10px;border-color:var(--spk-border-color)!important;">${t.tipe_spk}</span>` 
                : `<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2" style="font-size:10px;border-color:var(--spk-border-color)!important;">Semua</span>`;
            const fotoThumb = t.foto_url 
                ? `<img src="${t.foto_url}" class="rounded-2 me-2 border shadow-xs flex-shrink-0" style="width:38px;height:38px;object-fit:cover;">` 
                : `<div class="rounded-2 me-2 d-flex align-items-center justify-content-center border flex-shrink-0" style="width:38px;height:38px;background:rgba(255,255,255,0.04);"><i class="bi bi-diagram-3 text-primary opacity-50"></i></div>`;

            html += `
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 col-xxl-2">
                    <div class="tahapan-card-item" id="editTahapanCard_${idx}" onclick="toggleTahapanEdit(${idx})">
                        <div class="urutan-badge" id="editUrtBadge_${idx}">?</div>
                        <div class="check-icon" id="editChkIcon_${idx}">
                            <i class="bi bi-check2"></i>
                        </div>
                        <div class="d-flex align-items-center mb-1 pe-4">
                            ${fotoThumb}
                            <div class="fw-bold spk-text-main text-truncate" style="font-size:13px;" title="${t.nama_tahapan}">${t.nama_tahapan}</div>
                        </div>
                        <div class="mt-1 ps-1">${tipeLabel}</div>
                    </div>
                </div>
            `;
        });

        $('#editGridTahapanSpk').html(html);
        refreshAllBadgesEdit();
        updatePreviewChipsEdit();
    }

    function toggleTahapanEdit(idx) {
        const t = editAllMasterTahapans[idx];
        const card = document.getElementById('editTahapanCard_' + idx);
        const existing = editSelectedTahapans.findIndex(s => s.cardIndex === idx);

        if (existing !== -1) {
            editSelectedTahapans.splice(existing, 1);
            editSelectedTahapans.forEach((s, i) => { s.urutan = i + 1; });
            if (card) card.classList.remove('checked');
        } else {
            const urutanBaru = editSelectedTahapans.length + 1;
            editSelectedTahapans.push({
                nama_tahapan: t.nama_tahapan,
                urutan: urutanBaru,
                cardIndex: idx
            });
            if (card) card.classList.add('checked');
        }

        refreshAllBadgesEdit();
        updatePreviewChipsEdit();
    }

    function refreshAllBadgesEdit() {
        editAllMasterTahapans.forEach((_, idx) => {
            const badge = document.getElementById('editUrtBadge_' + idx);
            const card  = document.getElementById('editTahapanCard_' + idx);
            if (badge) badge.textContent = '?';
            if (card)  card.classList.remove('checked');
        });

        editSelectedTahapans.forEach((s) => {
            if (s.cardIndex !== -1) {
                const badge = document.getElementById('editUrtBadge_' + s.cardIndex);
                const card  = document.getElementById('editTahapanCard_' + s.cardIndex);
                if (badge) badge.textContent = s.urutan;
                if (card)  card.classList.add('checked');
            }
        });
    }

    function updatePreviewChipsEdit() {
        const preview = $('#editPreviewTahapanDipilih');
        const list    = $('#editListTahapanDipilih');

        if (editSelectedTahapans.length === 0) {
            preview.addClass('d-none');
            return;
        }

        preview.removeClass('d-none');
        let html = '';
        [...editSelectedTahapans].sort((a, b) => a.urutan - b.urutan).forEach((s) => {
            html += `
                <div class="tahapan-preview-chip">
                    <span class="chip-num">${s.urutan}</span>
                    ${s.nama_tahapan}
                </div>
            `;
        });
        list.html(html);
    }

    function pilihSemuaTahapanEdit() {
        if (!editAllMasterTahapans || editAllMasterTahapans.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tahapan Produksi Belum Ada',
                text: 'Resep BOM ini belum memiliki tahapan produksi di Master Tahapan. Silakan buat tahapan produksi terlebih dahulu.',
                confirmButtonText: '<i class="bi bi-plus-circle me-1"></i> Buka Master Tahapan',
                showCancelButton: true,
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('{{ route("produksi.master-tahapan.index") }}', '_blank');
                }
            });
            return;
        }

        editSelectedTahapans = editAllMasterTahapans.map((t, idx) => ({
            nama_tahapan: t.nama_tahapan,
            urutan: idx + 1,
            cardIndex: idx
        }));
        refreshAllBadgesEdit();
        updatePreviewChipsEdit();
    }

    function hapusSemuaTahapanEdit() {
        editSelectedTahapans = [];
        refreshAllBadgesEdit();
        updatePreviewChipsEdit();
    }

    function pilihBomEditSpk(id, kode, nama, bjNama, bjKode, bjId, satId, satNama, tipeBom, tenderObj) {
        $('#modalLookupBomSpk').modal('hide');
        $('#edit_bom_id').val(id);
        $('#edit_barang_jadi_id').val(bjId);
        $('#edit_satuan_id').val(satId);
        $('#edit_bom_name_display').val(`[${kode}] ${nama}`);
        $('#edit_barang_jadi_display').val(`[${bjKode}] ${bjNama}`);
        
        // Auto-sync Satuan
        $('#edit_label_satuan_spk').text(satNama || 'Pcs');

        // Auto-sync Tender & Tipe SPK
        if (tenderObj && (tenderObj.id || tenderObj.nama_tender)) {
            $('#edit_tender_id').val(tenderObj.id || '');
            $('#edit_lbl_tender_name').text(tenderObj.nama_tender || '');
            $('#edit_disp_tender_info').removeClass('d-none');
            $('#edit_tipe_spk').val('Make to Order');
        } else {
            $('#edit_tender_id').val('');
            $('#edit_disp_tender_info').addClass('d-none');
            $('#edit_lbl_tender_name').text('');
            if (tipeBom) {
                let tipeVal = tipeBom;
                if (tipeVal === 'Stok' || tipeVal === 'Stok (Make to Stock)') tipeVal = 'Make to Stock';
                if (tipeVal === 'Tender / Project' || tipeVal === 'Tender') tipeVal = 'Make to Order';
                $('#edit_tipe_spk').val(tipeVal);
            }
        }

        // Auto-sync Tahapan Produksi jika BOM berganti
        loadMasterTahapansForEditSpk(id, [], nama, kode);

        hitungUlangBahanEditSpk();
    }

    function onInputSpkEditTargetQty() {
        syncSpkEditSerialsInstantly();
        hitungUlangBahanEditSpk();
    }

    function hitungUlangBahanEditSpk() {
        const bomId = $('#edit_bom_id').val();
        const targetQty = parseFloat($('#edit_jumlah_rencana').val()) || 0;

        if (!bomId || targetQty <= 0) {
            $('#editTbodySpkMaterials').html('<tr><td colspan="8" class="text-center py-4 spk-text-muted small">Silakan masukkan Target Qty > 0.</td></tr>');
            $('#edit_disp_total_bahan').text('Rp 0');
            $('#edit_disp_total_tk').text('Rp 0');
            $('#edit_disp_total_oh').text('Rp 0');
            $('#edit_disp_grand_total').text('Rp 0');
            editLoadedBomMaterials = [];
            return;
        }

        $.ajax({
            url: `/produksi/spk/bom/${bomId}/materials`,
            method: 'GET',
            data: { target_qty: targetQty },
            success: function(res) {
                editLoadedBomMaterials = res.materials || [];
                renderEditMaterialsTable(editLoadedBomMaterials, res.all_sufficient);

                if (res.estimasi_biaya) {
                    $('#edit_disp_total_bahan').text(formatRupiahEdit(res.estimasi_biaya.total_bahan));
                    $('#edit_disp_total_tk').text(formatRupiahEdit(res.estimasi_biaya.total_tk));
                    $('#edit_disp_total_oh').text(formatRupiahEdit(res.estimasi_biaya.total_overhead));
                    $('#edit_disp_grand_total').text(formatRupiahEdit(res.estimasi_biaya.grand_total));
                }
            },
            error: function() {
                $('#editTbodySpkMaterials').html('<tr><td colspan="8" class="text-center py-3 text-danger small">Gagal menghitung bahan baku.</td></tr>');
            }
        });
    }

    function renderEditMaterialsTable(materials, allSufficient) {
        const $tbody = $('#editTbodySpkMaterials');
        
        if (allSufficient) {
            $('#editBadgeStatusStokKeseluruhan').removeClass('d-none').html(
                `<span class="badge stock-pill-sufficient rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Semua Stok Cukup</span>`
            );
        } else {
            $('#editBadgeStatusStokKeseluruhan').removeClass('d-none').html(
                `<span class="badge stock-pill-deficit rounded-pill px-3 py-1.5 fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Ada Bahan Kurang</span>`
            );
        }

        if (!materials || materials.length === 0) {
            $tbody.html('<tr><td colspan="9" class="text-center py-4 spk-text-muted small">Tidak ada rincian bahan baku.</td></tr>');
            return;
        }

        let html = '';
        materials.forEach((m, idx) => {
            const namaBahan = `[${m.kode_barang}] ${m.nama_barang}`;
            const satuanNama = m.satuan_nama || m.nama_satuan || (m.satuan ? m.satuan.nama_satuan : (m.barang_mentah?.satuan?.nama_satuan || m.barangMentah?.satuan?.nama_satuan || 'Pcs'));
            const kebUnit = Number(m.kebutuhan_per_unit || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
            const wastePct = parseFloat(m.waste_percent || 0) + '%';
            const totKeb = Number(m.total_kebutuhan || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
            const stokAktual = Number(m.stok_aktual || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
            const hppSatuan = formatRupiahEdit(m.estimasi_harga_satuan || 0);
            const subtotal = formatRupiahEdit(m.estimasi_subtotal || 0);

            const isSuff = m.is_sufficient;
            const statusPill = isSuff
                ? `<span class="badge stock-pill-sufficient rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Cukup</span>`
                : `<span class="badge stock-pill-deficit rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Kurang ${Number(m.deficit || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 })} ${satuanNama}</span>`;

            html += `
                <tr>
                    <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                    <td class="fw-bold text-body">${namaBahan}</td>
                    <td class="text-center font-monospace">${kebUnit} ${satuanNama}</td>
                    <td class="text-center font-monospace small text-muted">${wastePct}</td>
                    <td class="text-center font-monospace fw-bold text-primary">${totKeb} ${satuanNama}</td>
                    <td class="text-center font-monospace fw-semibold">${stokAktual} ${satuanNama}</td>
                    <td class="text-center">${statusPill}</td>
                    <td class="text-end font-monospace text-muted">${hppSatuan}</td>
                    <td class="text-end font-monospace fw-bold">${subtotal}</td>
                </tr>
            `;
        });
        $tbody.html(html);
    }

    function formatRupiahEdit(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function submitFormEditSpk() {
        const id  = $('#edit_spk_id').val();
        const bomId = $('#edit_bom_id').val();
        const targetQty = parseFloat($('#edit_jumlah_rencana').val()) || 0;

        if (!bomId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Pilih Resep BOM terlebih dahulu.' });
            return;
        }
        if (targetQty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Target Qty Produksi harus lebih dari 0.' });
            return;
        }
        const pjId = $('#edit_penanggung_jawab_id').val();
        if (!pjId) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Penanggung Jawab (PIC Produksi) wajib diisi!' });
            return;
        }

        if (!editAllMasterTahapans || editAllMasterTahapans.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tahapan Produksi Belum Dibuat!',
                text: 'Resep BOM ini belum memiliki konfigurasi tahapan produksi di Master Tahapan. SPK tidak dapat disimpan.',
                confirmButtonText: '<i class="bi bi-plus-circle me-1"></i> Buat Tahapan Sekarang',
                showCancelButton: true,
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('{{ route("produksi.master-tahapan.index") }}', '_blank');
                }
            });
            return;
        }

        if (!editSelectedTahapans || editSelectedTahapans.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Peringatan!', text: 'Tahapan proses produksi wajib dipilih minimal 1 tahapan!' });
            return;
        }

        const batchVal = ($('#edit_nomor_batch').val() || '').trim();
        if (batchVal.length > 50) {
            Swal.fire({
                icon: 'warning',
                title: 'Nomor Batch / Lot Terlalu Panjang!',
                text: `Nomor Batch / Lot maksimal 50 karakter! (Saat ini: ${batchVal.length} karakter). Silakan perpendek nomor lot.`
            });
            return;
        }

        const $btn = $('#btnSimpanUpdateSpk');
        $btn.prop('disabled', true);

        const payload = {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            bom_id: bomId,
            tender_id: $('#edit_tender_id').val() || null,
            barang_jadi_id: $('#edit_barang_jadi_id').val(),
            satuan_id: $('#edit_satuan_id').val(),
            jumlah_rencana: targetQty,
            nomor_batch: $('#edit_nomor_batch').val() || null,
            serial_numbers: $('#edit_serial_numbers').val() || null,
            tanggal_spk: $('#edit_tanggal_spk').val(),
            target_selesai: $('#edit_target_selesai').val(),
            tipe_spk: $('#edit_tipe_spk').val(),
            prioritas: $('#edit_prioritas').val(),
            penanggung_jawab_id: $('#edit_penanggung_jawab_id').val() || null,
            keterangan: $('#edit_keterangan_spk').val(),
            materials: editLoadedBomMaterials,
            selected_tahapans: editSelectedTahapans
        };

        $.ajax({
            url: `/produksi/spk/${id}`,
            method: 'POST',
            data: payload,
            success: function(res) {
                $('#modalEditSpk').modal('hide');
                $btn.prop('disabled', false);
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message || 'Data SPK berhasil diperbarui.',
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => { reloadTableSpk(); });
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal Memperbarui', res.message || 'Terjadi kesalahan sistem.', 'error');
            }
        });
    }

    // ── Edit Batch & Serial Number Generator Helpers ──────────────
    function validateSpkEditBatchNo(el) {
        if (el && el.value && el.value.length > 50) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan Karakter Lot!',
                text: `Nomor Batch / Lot maksimal 50 karakter! (Saat ini: ${el.value.length} karakter). Karakter selebihnya akan dipotong otomatis.`,
                confirmButtonColor: '#3085d6'
            });
            el.value = el.value.substring(0, 50);
        }
    }

    function syncSpkEditSerialsInstantly() {
        const bjId = $('#edit_barang_jadi_id').val();
        if (!bjId) return;

        const qty = parseInt($('#edit_jumlah_rencana').val()) || 0;
        if (qty <= 0) {
            $('#edit_serial_numbers').val('');
            updateSpkEditSnCount();
            return;
        }

        let batchNo = ($('#edit_nomor_batch').val() || '').trim();
        if (!batchNo) {
            let bjDisplay = $('#edit_barang_jadi_display').val();
            let code = 'PRD';
            if (bjDisplay && bjDisplay.includes('[')) {
                cleanCode = bjDisplay.split('[')[1].split(']')[0];
            }
            const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
            batchNo = `LOT-${code}-${today}`;
        }

        const cleanPrefix = batchNo.replace(/[^A-Za-z0-9\-_]/g, '');
        const padLen = Math.max(3, String(qty).length);

        let serials = [];
        for (let i = 1; i <= qty; i++) {
            serials.push(cleanPrefix + '-' + String(i).padStart(padLen, '0'));
        }
        $('#edit_serial_numbers').val(serials.join('\n'));
        updateSpkEditSnCount();
    }

    function handleSpkEditBatchNoInput(el) {
        validateSpkEditBatchNo(el);
        syncSpkEditSerialsInstantly();
    }

    function generateSpkEditBatchNo() {
        const bjDisplay = $('#edit_barang_jadi_display').val();
        let code = 'PRD';
        if (bjDisplay && bjDisplay.includes('[')) {
            code = bjDisplay.split('[')[1].split(']')[0];
        }
        const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const lotVal = `LOT-${code}-${today}`.substring(0, 50);
        $('#edit_nomor_batch').val(lotVal);
        syncSpkEditSerialsInstantly();
    }

    function generateSpkEditSerials(isManual = false) {
        const bjId = $('#edit_barang_jadi_id').val();
        const qty = parseInt($('#edit_jumlah_rencana').val()) || 0;
        const batchNo = ($('#edit_nomor_batch').val() || '').trim();

        if (!bjId) {
            if (isManual) Swal.fire('Info', 'Pilih Resep BOM terlebih dahulu.', 'info');
            return;
        }
        if (qty <= 0) {
            if (isManual) Swal.fire('Info', 'Masukkan Target Qty Produksi terlebih dahulu.', 'info');
            return;
        }

        $('#btnGenerateSnEditSpk').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>...');

        $.ajax({
            url: '{{ route("produksi.spk.suggest-serials") }}',
            method: 'GET',
            data: {
                barang_jadi_id: bjId,
                qty: qty,
                nomor_batch: batchNo,
                lot: batchNo,
                prefix: batchNo
            },
            success: function(res) {
                $('#btnGenerateSnEditSpk').prop('disabled', false).html('<i class="bi bi-lightning-charge-fill text-warning"></i><span>Generate SN</span>');
                if (res.serials && res.serials.length > 0) {
                    $('#edit_serial_numbers').val(res.serials.join('\n'));
                    updateSpkEditSnCount();
                }
            },
            error: function() {
                $('#btnGenerateSnEditSpk').prop('disabled', false).html('<i class="bi bi-lightning-charge-fill text-warning"></i><span>Generate SN</span>');
                let serials = [];
                let bjDisplay = $('#edit_barang_jadi_display').val();
                let cleanCode = 'PRD';
                if (bjDisplay && bjDisplay.includes('[')) {
                    cleanCode = bjDisplay.split('[')[1].split(']')[0];
                }
                let cleanPrefix = batchNo ? batchNo : 'LOT-' + cleanCode;
                const padLen = Math.max(3, String(qty).length);
                for (let i = 1; i <= qty; i++) {
                    serials.push(cleanPrefix + '-' + String(i).padStart(padLen, '0'));
                }
                $('#edit_serial_numbers').val(serials.join('\n'));
                updateSpkEditSnCount();
            }
        });
    }

    function updateSpkEditSnCount() {
        const val = $('#edit_serial_numbers').val() ? $('#edit_serial_numbers').val().trim() : '';
        $('#edit_sn_duplicate_warning').remove();
        if (!val) {
            $('#edit_disp_sn_count').text('0 SN terdaftar').removeClass('bg-success-subtle text-success bg-danger-subtle text-danger').addClass('bg-secondary-subtle text-secondary');
            return;
        }
        const items = val.split(/[\n,]+/).map(s => s.trim()).filter(Boolean);
        const count = items.length;

        // Cek duplikasi internal di dalam textarea
        const counts = {};
        const duplicates = [];
        items.forEach(item => {
            counts[item] = (counts[item] || 0) + 1;
            if (counts[item] === 2) duplicates.push(item);
        });

        if (duplicates.length > 0) {
            $('#edit_disp_sn_count').text(`${count} SN (${duplicates.length} Ganda!)`).removeClass('bg-secondary-subtle text-secondary bg-success-subtle text-success').addClass('bg-danger-subtle text-danger');
            $('#edit_serial_numbers').after(`
                <div id="edit_sn_duplicate_warning" class="alert alert-danger py-1.5 px-2.5 mt-1.5 mb-0 rounded-3 font-monospace small d-flex align-items-center gap-1.5" style="font-size: 11px;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-danger"></i>
                    <span>Terdapat Serial Number ganda: <strong>${duplicates.slice(0, 3).join(', ')}${duplicates.length > 3 ? '...' : ''}</strong></span>
                </div>
            `);
        } else {
            $('#edit_disp_sn_count').text(`${count} SN terdaftar`).removeClass('bg-secondary-subtle text-secondary bg-danger-subtle text-danger').addClass('bg-success-subtle text-success');
        }
    }

    function clearSpkEditSerials() {
        $('#edit_serial_numbers').val('');
        updateSpkEditSnCount();
    }
</script>
@endpush
