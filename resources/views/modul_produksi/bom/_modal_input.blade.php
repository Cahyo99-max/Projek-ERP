{{-- MODAL TAMBAH BOM PRODUKSI GARMEN --}}
<div class="modal fade bom-modal-primary" id="modalTambahBom" tabindex="-1" aria-labelledby="modalTambahBomLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" style="max-width: 1350px;">
        <div class="modal-content bom-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- MODAL HEADER (CINEMATIC BLUE GRADIENT) --}}
            <div class="modal-header bom-modal-header-blue p-3.5 p-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #0284c7 100%);">
                <!-- Decorative Ambient Glow -->
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>
                
                <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-plus-circle-fill fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-extrabold text-white mb-0" id="modalTambahBomLabel" style="letter-spacing: -0.2px;">
                            Buat Resep Produksi Garmen <span class="opacity-75 fs-6 fw-normal">(Bill of Materials)</span>
                        </h5>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 12px;">
                            Formula komposisi bahan baku, waste %, biaya konveksi (CMT), dan estimasi HPP per produk
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            <form id="formTambahBom" autocomplete="off">
                @csrf
                <div class="modal-body bom-modal-body p-3 p-md-4" style="background: var(--bg-main, #0f172a);">
                    
                    {{-- SECTION 1: HEADER RESEP & FOTO PREVIEW CARD --}}
                    <div class="card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5 position-relative overflow-hidden" 
                         style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                <i class="bi bi-info-circle-fill" style="font-size: 12px;"></i>
                            </span>
                            <span class="fw-bold text-theme-main small text-uppercase" style="letter-spacing: 0.5px;">1. Data Pokok Resep Produk</span>
                        </div>

                        <div class="row g-3">
                            {{-- LEFT COL: INPUT FORM FIELDS --}}
                            <div class="col-12 col-lg-9">
                                <div class="row g-2.5 g-md-3">
                                    {{-- Barang Jadi (Hasil Produksi) --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-box-seam text-primary me-1"></i> Produk Barang Jadi <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="hidden" name="barang_jadi_id" id="inp_barang_jadi_id" required>
                                            <input type="text" id="inp_barang_jadi_name" class="form-control rounded-start-3 bg-light-theme cursor-pointer"
                                                   placeholder="-- Klik 🔍 untuk pilih Barang Jadi --" readonly required onclick="bukaModalLookupBarangBom('jadi', 'input_jadi')">
                                            <button type="button" class="btn btn-primary rounded-end-3 px-3 shadow-xs" onclick="bukaModalLookupBarangBom('jadi', 'input_jadi')" title="Cari Barang Jadi">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Nama Resep BOM --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-card-text text-primary me-1"></i> Nama Resep BOM <span class="text-danger">*</span> <small class="text-muted">(Maks 40)</small>
                                        </label>
                                        <input type="text" name="nama_bom" id="inp_nama_bom" class="form-control rounded-3"
                                               maxlength="40" placeholder="Misal: BOM Kemeja Pria Lengan Panjang Standard" required>
                                    </div>

                                    {{-- Target Qty Hasil --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-bullseye text-primary me-1"></i> Qty Hasil
                                        </label>
                                        <input type="text" name="jumlah_hasil" id="inp_jumlah_hasil" class="form-control rounded-3 text-center fw-bold bg-light-theme"
                                               value="1" readonly style="cursor: not-allowed;" title="Resep BOM dihitung per 1 Pcs / Satuan">
                                    </div>

                                    {{-- Satuan Hasil --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-tag text-primary me-1"></i> Satuan Hasil <span class="text-danger">*</span>
                                        </label>
                                        <input type="hidden" name="satuan_hasil_id" id="inp_satuan_hasil_id" required>
                                        <input type="text" id="inp_satuan_hasil_name" class="form-control rounded-3 bg-light-theme text-center fw-semibold"
                                               placeholder="Otomatis dari barang" readonly
                                               style="cursor: default; color: var(--bom-text-muted);">
                                    </div>

                                    {{-- Kode BOM (Auto vs Manual) --}}
                                    <div class="col-12 col-md-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold text-theme-muted small mb-0">
                                                <i class="bi bi-upc-scan text-primary me-1"></i> Kode BOM <span class="text-danger">*</span>
                                            </label>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="inp_auto_number_bom" checked>
                                                <label class="form-check-label small text-theme-muted" for="inp_auto_number_bom" style="font-size: 11px;">Oto</label>
                                            </div>
                                        </div>
                                        <input type="text" name="kode_bom" id="inp_kode_bom" class="form-control rounded-3 bg-light-theme font-monospace"
                                               maxlength="20" placeholder="BOM/YYYY/MM/00001" readonly required>
                                    </div>

                                    {{-- Tipe BOM --}}
                                    <div class="col-12 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-diagram-3 text-primary me-1"></i> Tipe Produksi
                                        </label>
                                        <select name="tipe_bom" id="inp_tipe_bom" class="form-select rounded-3">
                                            <option value="Stok (Make to Stock)">Stok (Make to Stock)</option>
                                            <option value="Tender / Project">Tender / Project (Make to Order)</option>
                                            <option value="Garmen">Garmen (Konveksi / Textile)</option>
                                            <option value="Umum">Umum / Assembler</option>
                                        </select>
                                    </div>

                                    {{-- Link Tender / Project --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-briefcase text-primary me-1"></i> Tender / Project Terkait <small class="text-muted">(Opsional)</small>
                                        </label>
                                        <select name="tender_id" id="inp_tender_id" class="form-select rounded-3">
                                            <option value="">-- Tanpa Tender (Produksi Standar Mandiri) --</option>
                                            @if(isset($tenders) && count($tenders) > 0)
                                                @foreach($tenders as $t)
                                                    <option value="{{ $t->id }}">[{{ $t->nomor_kontrak ?: 'TENDER' }}] {{ $t->nama_tender }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COL: PRODUCT PHOTO PREVIEW CARD WITH CAROUSEL --}}
                            <div class="col-12 col-lg-3">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-image text-primary me-1"></i> Pratinjau Foto Produk
                                </label>
                                <div class="card border rounded-3 bg-light-theme shadow-xs overflow-hidden p-2 text-center" 
                                     style="min-height: 190px; border-color: rgba(255,255,255,0.08) !important;">
                                    {{-- Empty State --}}
                                    <div id="preview_box_no_image_input" class="d-flex flex-column align-items-center justify-content-center h-100 py-3">
                                        <i class="bi bi-image text-muted opacity-50 display-5 d-block mb-1"></i>
                                        <small class="text-theme-muted" style="font-size: 11px;">Pilih Produk Barang Jadi untuk melihat foto</small>
                                    </div>

                                    {{-- Has Image State / Carousel --}}
                                    <div id="preview_box_has_image_input" class="d-none h-100 d-flex flex-column justify-content-center align-items-center">
                                        <div id="container_img_preview_input" class="position-relative w-100 h-100 d-flex align-items-center justify-content-center cursor-pointer"
                                             style="min-height: 165px; background: transparent !important; border-radius: 10px; overflow: hidden;"
                                             onclick="navigasiFotoInput(1)">
                                            <img id="img_preview_barang_jadi_input" src="" class="rounded" style="max-height: 165px; width: 100%; object-fit: contain; background: transparent !important;">

                                            {{-- Carousel Nav Controls --}}
                                            <button type="button" id="btn_prev_foto_input" class="btn btn-sm btn-dark bg-opacity-75 border-0 rounded-circle position-absolute start-0 ms-1 d-none" onclick="event.stopPropagation(); navigasiFotoInput(-1);" style="width: 28px; height: 28px; padding: 0; z-index: 10;">
                                                <i class="bi bi-chevron-left text-white"></i>
                                            </button>
                                            <button type="button" id="btn_next_foto_input" class="btn btn-sm btn-dark bg-opacity-75 border-0 rounded-circle position-absolute end-0 me-1 d-none" onclick="event.stopPropagation(); navigasiFotoInput(1);" style="width: 28px; height: 28px; padding: 0; z-index: 10;">
                                                <i class="bi bi-chevron-right text-white"></i>
                                            </button>
                                            <span id="badge_foto_counter_input" class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 end-0 m-1 px-2 py-1 rounded-pill d-none" style="font-size: 10px; z-index: 10;">1/1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: KOMPOSISI BAHAN BAKU CARD --}}
                    <div class="card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5 position-relative overflow-hidden" 
                         style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                        
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                    <i class="bi bi-box-seam-fill" style="font-size: 12px;"></i>
                                </span>
                                <h6 class="fw-bold text-theme-main mb-0 small text-uppercase" style="letter-spacing: 0.5px;">
                                    2. Komposisi Bahan Baku & Aksesoris Jahit
                                </h6>
                                <span class="badge bg-secondary bg-opacity-10 text-theme-main border-0 ms-1 px-2.5 py-1 rounded-pill font-monospace" id="badge_count_items_input" style="font-size: 11px;">0 / 50</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-sm flex-shrink-0 text-nowrap mt-2 mt-sm-0" onclick="tambahBarisItemBomInput(true)"
                                    style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); width: auto; max-width: max-content;">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Bahan
                            </button>
                        </div>

                        {{-- Rumus Alert Box --}}
                        <div class="alert border-0 shadow-xs py-2 px-3 mb-3 d-flex align-items-center justify-content-between rounded-3" 
                             style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.2) !important;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calculator-fill text-info fs-5"></i>
                                <div class="small">
                                    <span class="fw-bold text-info">Rumus:</span> 
                                    <span class="text-theme-main"><strong>Qty Bruto</strong> = Qty Netto × (1 + Waste%)</span> &nbsp;|&nbsp; 
                                    <span class="text-theme-main"><strong>Subtotal HPP</strong> = Qty Bruto × Estimasi HPP</span>
                                </div>
                            </div>
                            <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 small fw-bold d-none d-md-inline-block"><i class="bi bi-gear-fill me-1"></i>Otomatis</span>
                        </div>

                        {{-- Table of Materials --}}
                        <div class="table-responsive rounded-3 border mb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                            <table class="table bom-table-material align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th style="min-width: 250px;">BAHAN BAKU / AKSESORIS <span class="text-danger">*</span></th>
                                        <th class="text-center" style="width: 120px;">QTY NETTO</th>
                                        <th style="width: 130px;">SATUAN</th>
                                        <th class="text-center" style="width: 110px;">WASTE (%)</th>
                                        <th class="text-end" style="width: 140px;">ESTIMASI HPP (RP)</th>
                                        <th class="text-end" style="width: 150px;">SUBTOTAL (RP)</th>
                                        <th class="text-center" style="width: 45px;"><i class="bi bi-trash text-danger"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyItemBomInput">
                                    {{-- Populated via JS --}}
                                </tbody>
                                <tfoot class="fw-bold bg-light-theme">
                                    <tr>
                                        <td colspan="5" class="text-end text-theme-muted">SUBTOTAL BAHAN BAKU:</td>
                                        <td class="text-end text-primary font-monospace fs-6" id="lblSubtotalBahanInput">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- SECTION 3: BIAYA OPERASIONAL & GLOWING ESTIMASI HPP CARD --}}
                    <div class="card border-0 rounded-4 shadow-sm p-3 p-md-3.5 position-relative overflow-hidden" 
                         style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                        
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                <i class="bi bi-cash-stack" style="font-size: 12px;"></i>
                            </span>
                            <span class="fw-bold text-theme-main small text-uppercase" style="letter-spacing: 0.5px;">3. Alokasi Biaya & Estimasi HPP Resep</span>
                        </div>

                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-person-gear text-primary me-1"></i> Biaya Tenaga Kerja / CMT (Rp)
                                </label>
                                <input type="text" name="biaya_tenaga_kerja" id="inp_biaya_tk" class="form-control text-end inp-hpp-formatted"
                                       maxlength="15" placeholder="0" oninput="kalkulasiTotalBomInput()">
                                <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Estimasi ongkos jahit / pola / obras</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-lightning-charge text-primary me-1"></i> Biaya Overhead Produksi (Rp)
                                </label>
                                <input type="text" name="biaya_overhead" id="inp_biaya_oh" class="form-control text-end inp-hpp-formatted"
                                       maxlength="15" placeholder="0" oninput="kalkulasiTotalBomInput()">
                                <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Estimasi listrik, plastik, hangtag, dll</small>
                            </div>

                            {{-- GLOWING NEON HPP CARD --}}
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-4 position-relative overflow-hidden text-end shadow-sm" 
                                     style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.22) 100%); border: 1px solid rgba(16, 185, 129, 0.35); box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.25) !important;">
                                    <small class="text-uppercase fw-bold text-success d-block" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="bi bi-check2-circle me-1"></i> Estimasi Total HPP / Satuan
                                    </small>
                                    <span class="fs-4 fw-extrabold text-success font-monospace d-block mt-1" id="lblTotalHppInput">Rp 0 / Pcs</span>
                                    <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Bahan + CMT + Overhead</small>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-theme-muted small mb-1">
                                <i class="bi bi-chat-left-text text-primary me-1"></i> Keterangan & Catatan Spesifikasi Jahit
                            </label>
                            <input type="text" name="keterangan" id="inp_keterangan_bom" class="form-control rounded-3"
                                   placeholder="Catatan tambahan spesifikasi jahit, warna benang, atau toleransi ukuran...">
                        </div>
                    </div>
                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer bom-modal-footer justify-content-between p-3 p-md-3.5 border-0" 
                     style="background: var(--bg-card, #1e293b); border-top: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> Batal
                    </button>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" id="btnSimpanDraftBomInput" class="btn text-white rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="submitFormInputBom('Draft')" title="Simpan sebagai draf (Hanya Anda yang dapat melihat)" 
                                style="background: linear-gradient(135deg, #475569 0%, #334155 100%); border: 1px solid #64748b;">
                            <i class="bi bi-file-earmark-text-fill me-1 text-warning"></i> Simpan Draft
                        </button>
                        <button type="button" id="btnSimpanPendingBomInput" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-lg" onclick="submitFormInputBom('Pending')" title="Simpan dan ajukan ke atasan untuk disetujui" 
                                style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #0ea5e9 100%); border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 6px 20px -2px rgba(37, 99, 235, 0.5) !important;">
                            <i class="bi bi-send-fill me-1"></i> Simpan & Kirim (Pending)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let bomInputRowIndex = 0;

    function cleanNumberInputValue(val) {
        if (val === null || val === undefined || val === '') return '';
        let num = parseFloat(val);
        if (isNaN(num)) return '';
        return parseFloat(num.toFixed(4)).toString();
    }

    let currentPreviewFotosInput = [];
    let currentFotoIndexInput = 0;

    function updatePreviewFotoInput(fotosArray) {
        currentPreviewFotosInput = (fotosArray && fotosArray.length > 0) ? fotosArray : [];
        currentFotoIndexInput = 0;

        if (currentPreviewFotosInput.length > 0) {
            renderFotoCarouselInput();
            $('#preview_box_no_image_input').addClass('d-none');
            $('#preview_box_has_image_input').removeClass('d-none').addClass('d-flex');
        } else {
            $('#preview_box_has_image_input').addClass('d-none').removeClass('d-flex');
            $('#preview_box_no_image_input').removeClass('d-none');
        }
    }

    function renderFotoCarouselInput() {
        if (currentPreviewFotosInput.length === 0) return;
        const url = currentPreviewFotosInput[currentFotoIndexInput];
        $('#img_preview_barang_jadi_input').attr('src', url);

        if (currentPreviewFotosInput.length > 1) {
            $('#btn_prev_foto_input, #btn_next_foto_input, #badge_foto_counter_input').removeClass('d-none');
            $('#badge_foto_counter_input').text(`${currentFotoIndexInput + 1}/${currentPreviewFotosInput.length}`);
        } else {
            $('#btn_prev_foto_input, #btn_next_foto_input, #badge_foto_counter_input').addClass('d-none');
        }
    }

    function navigasiFotoInput(direction) {
        if (currentPreviewFotosInput.length <= 1) return;
        currentFotoIndexInput += direction;
        if (currentFotoIndexInput < 0) {
            currentFotoIndexInput = currentPreviewFotosInput.length - 1;
        } else if (currentFotoIndexInput >= currentPreviewFotosInput.length) {
            currentFotoIndexInput = 0;
        }
        renderFotoCarouselInput();
    }

    function updateCountItemsInput() {
        const count = $('#tbodyItemBomInput tr').length;
        $('#badge_count_items_input').text(`${count} / 50`);
        if (count >= 50) {
            $('#badge_count_items_input').removeClass('bg-secondary bg-opacity-10 text-theme-main').addClass('bg-danger text-white');
        } else {
            $('#badge_count_items_input').removeClass('bg-danger text-white').addClass('bg-secondary bg-opacity-10 text-theme-main');
        }
    }

    function bukaModalTambahBom() {
        $('#formTambahBom')[0].reset();
        $('#inp_jumlah_hasil').val('1');
        $('#tbodyItemBomInput').empty();
        updatePreviewFotoInput([]);
        bomInputRowIndex = 0;
        $('#inp_auto_number_bom').prop('checked', true).trigger('change');
        generateAutoKodeBomInput();
        tambahBarisItemBomInput();
        updateCountItemsInput();
        $('#modalTambahBom').modal('show');
    }

    $('#inp_auto_number_bom').on('change', function() {
        if ($(this).is(':checked')) {
            $('#inp_kode_bom').prop('readonly', true).addClass('bg-light-theme');
            generateAutoKodeBomInput();
        } else {
            $('#inp_kode_bom').prop('readonly', false).removeClass('bg-light-theme').val('');
        }
    });

    function generateAutoKodeBomInput() {
        $.ajax({
            url: '{{ route("produksi.bom.generate-kode") }}',
            method: 'GET',
            success: function(res) {
                if ($('#inp_auto_number_bom').is(':checked')) {
                    $('#inp_kode_bom').val(res.kode_bom);
                }
            }
        });
    }

    function tambahBarisItemBomInput(isManual = false) {
        if ($('#tbodyItemBomInput tr').length >= 50) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Maksimal Bahan Tercapai!',
                text: 'Maksimal bahan baku / aksesoris adalah 50 item. Anda tidak dapat menambahkan lebih dari 50 item!',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        if (isManual) {
            const barangJadiId = $('#inp_barang_jadi_id').val();
            const namaBom = $('#inp_nama_bom').val().trim();

            if (!barangJadiId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Barang Jadi Dahulu!',
                    text: 'Silakan pilih Produk Barang Jadi (Hasil Produksi) terlebih dahulu sebelum menambah bahan baku.',
                    confirmButtonText: '<i class="bi bi-box-seam me-1"></i> Pilih Barang Jadi'
                }).then(() => {
                    bukaModalLookupBarangBom('input_jadi', 'input_jadi');
                });
                return;
            }

            if (!namaBom) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Isi Nama Resep Dahulu!',
                    text: 'Silakan isi Nama Resep Produksi terlebih dahulu sebelum menambah bahan baku.'
                }).then(() => {
                    $('#inp_nama_bom').focus();
                });
                return;
            }
        }

        bomInputRowIndex++;
        const rowId = `row_input_${bomInputRowIndex}_${Date.now()}`;

        let satuanOptionsHtml = '<option value="">-- Satuan --</option>';
        @foreach($satuans as $s)
            satuanOptionsHtml += `<option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>`;
        @endforeach

        const trHtml = `
            <tr id="${rowId}">
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="product-thumb-box border rounded-3 d-flex align-items-center justify-content-center bg-light-theme cursor-pointer shadow-sm"
                             style="width: 32px; height: 32px; flex-shrink: 0; overflow: hidden;"
                             title="Foto Barang">
                            <img src="" id="img_thumb_${rowId}" class="product-img d-none" style="width: 100%; height: 100%; object-fit: cover;">
                            <i class="bi bi-image text-muted fs-6 icon-no-photo-${rowId}"></i>
                        </div>

                        <div class="input-group input-group-sm flex-grow-1">
                            <input type="hidden" name="items[${bomInputRowIndex}][barang_mentah_id]" class="inp-barang-mentah-id" required data-row="${rowId}">
                            <input type="text" class="form-control form-control-sm bg-light-theme inp-barang-name cursor-pointer" 
                                   placeholder="-- Klik 🔍 cari bahan --" readonly required data-row="${rowId}"
                                   onclick="bukaModalLookupBarangBom('${rowId}', 'input')">
                            <button type="button" class="btn btn-primary btn-search-barang-bom" onclick="bukaModalLookupBarangBom('${rowId}', 'input')" title="Cari Bahan">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" step="any" min="0" name="items[${bomInputRowIndex}][jumlah_kebutuhan]" class="form-control form-control-sm text-center inp-qty-bom" placeholder="0" required data-row="${rowId}" oninput="hitungkanSubtotalRowBomInput('${rowId}')">
                    <div class="text-center mt-1 font-monospace inp-bruto-${rowId}" style="font-size: 11px; color: #38bdf8;" title="Kebutuhan kotor termasuk waste %">
                        Bruto: <span class="val-bruto fw-bold" style="color: #fbbf24;">0</span>
                    </div>
                </td>
                <td>
                    <input type="hidden" name="items[${bomInputRowIndex}][satuan_id]" class="inp-satuan-id select-satuan" required>
                    <input type="text" class="form-control form-control-sm text-center bg-light-theme inp-satuan-name" placeholder="Otomatis" readonly style="cursor: default;">
                </td>
                <td>
                    <input type="number" step="any" min="0" max="100" name="items[${bomInputRowIndex}][waste_percent]" class="form-control form-control-sm text-center inp-waste-bom" placeholder="0" value="0" data-row="${rowId}" oninput="hitungkanSubtotalRowBomInput('${rowId}')">
                </td>
                <td>
                    <input type="text" name="items[${bomInputRowIndex}][estimasi_harga_satuan]" class="form-control form-control-sm text-end inp-hpp-formatted inp-hpp-bom" placeholder="0" data-row="${rowId}" oninput="hitungkanSubtotalRowBomInput('${rowId}')">
                </td>
                <td class="text-end fw-bold text-primary font-monospace inp-subtotal-${rowId}">Rp 0</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBarisItemBomInput('${rowId}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyItemBomInput').append(trHtml);
        updateCountItemsInput();
    }

    function hapusBarisItemBomInput(rowId) {
        if ($('#tbodyItemBomInput tr').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 bahan baku!', 'info');
            return;
        }
        $(`#${rowId}`).remove();
        updateCountItemsInput();
        kalkulasiTotalBomInput();
    }

    function hitungkanSubtotalRowBomInput(rowId) {
        const qty = parseFloat($(`#${rowId} .inp-qty-bom`).val()) || 0;
        const waste = parseFloat($(`#${rowId} .inp-waste-bom`).val()) || 0;
        const hpp = unformatNumberID($(`#${rowId} .inp-hpp-bom`).val());

        const grossQty = qty * (1 + (waste / 100));
        const subtotal = grossQty * hpp;

        $(`#${rowId} .inp-bruto-${rowId} .val-bruto`).text(cleanNumberInputValue(grossQty));
        $(`.inp-subtotal-${rowId}`).text('Rp ' + numberFormatID(subtotal));
        kalkulasiTotalBomInput();
    }

    let isCalcBomInputPending = false;
    function kalkulasiTotalBomInput() {
        if (isCalcBomInputPending) return;
        isCalcBomInputPending = true;

        requestAnimationFrame(() => {
            let totalSubtotalBahan = 0;

            $('#tbodyItemBomInput tr').each(function() {
                const qty = parseFloat($(this).find('.inp-qty-bom').val()) || 0;
                const waste = parseFloat($(this).find('.inp-waste-bom').val()) || 0;
                const hpp = unformatNumberID($(this).find('.inp-hpp-bom').val());

                const grossQty = qty * (1 + (waste / 100));
                totalSubtotalBahan += (grossQty * hpp);
            });

            const targetHasil = parseFloat($('#inp_jumlah_hasil').val()) || 1;
            const tk = unformatNumberID($('#inp_biaya_tk').val());
            const oh = unformatNumberID($('#inp_biaya_oh').val());

            const totalHpp = totalSubtotalBahan + tk + oh;
            const hppPerUnit = targetHasil > 0 ? (totalHpp / targetHasil) : totalHpp;

            $('#lblSubtotalBahanInput').text('Rp ' + numberFormatID(totalSubtotalBahan));
            $('#lblTotalHppInput').text('Rp ' + numberFormatID(hppPerUnit) + ' / Unit');
            isCalcBomInputPending = false;
        });
    }

    function submitFormInputBom(statusTarget = 'Draft') {
        if (!statusTarget || typeof statusTarget !== 'string') {
            statusTarget = 'Draft';
        }

        const barangJadiId = $('#inp_barang_jadi_id').val();
        const namaBomVal = ($('#inp_nama_bom').val() || '').trim();
        const kodeBomVal = ($('#inp_kode_bom').val() || '').trim();

        if (!barangJadiId) {
            Swal.fire({
                icon: 'warning',
                title: 'Barang Jadi Wajib Dipilih!',
                text: 'Silakan pilih Produk Barang Jadi (Hasil Produksi) terlebih dahulu!',
                confirmButtonText: '<i class="bi bi-box-seam me-1"></i> Pilih Barang Jadi'
            }).then(() => {
                bukaModalLookupBarangBom('input_jadi', 'input_jadi');
            });
            return;
        }

        if (!namaBomVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Nama Resep BOM Wajib Diisi!',
                text: 'Silakan ketikkan Nama Resep Produksi / BOM terlebih dahulu!'
            }).then(() => {
                $('#inp_nama_bom').focus();
            });
            return;
        }

        if (namaBomVal.length > 40) {
            Swal.fire('Peringatan Karakter', `Nama Resep BOM tidak boleh lebih dari 40 karakter! (Saat ini: ${namaBomVal.length} karakter)`, 'warning');
            return;
        }

        if (kodeBomVal.length > 20) {
            Swal.fire('Peringatan Karakter', `Kode BOM tidak boleh lebih dari 20 karakter! (Saat ini: ${kodeBomVal.length} karakter)`, 'warning');
            return;
        }

        const $btnDraft = $('#btnSimpanDraftBomInput');
        const $btnPending = $('#btnSimpanPendingBomInput');
        $btnDraft.prop('disabled', true);
        $btnPending.prop('disabled', true);

        const formData = {
            _token: '{{ csrf_token() }}',
            status: statusTarget,
            nama_bom: $('#inp_nama_bom').val(),
            barang_jadi_id: $('#inp_barang_jadi_id').val(),
            tender_id: $('#inp_tender_id').val(),
            jumlah_hasil: $('#inp_jumlah_hasil').val(),
            satuan_hasil_id: $('#inp_satuan_hasil_id').val(),
            auto_number: $('#inp_auto_number_bom').is(':checked') ? 1 : 0,
            kode_bom: $('#inp_kode_bom').val(),
            tipe_bom: $('#inp_tipe_bom').val(),
            biaya_tenaga_kerja: unformatNumberID($('#inp_biaya_tk').val()),
            biaya_overhead: unformatNumberID($('#inp_biaya_oh').val()),
            keterangan: $('#inp_keterangan_bom').val(),
            items: []
        };

        $('#tbodyItemBomInput tr').each(function() {
            const barangMentahId = $(this).find('.inp-barang-mentah-id').val();
            const qty = parseFloat($(this).find('.inp-qty-bom').val()) || 0;
            const satuanId = $(this).find('.select-satuan').val();
            const waste = parseFloat($(this).find('.inp-waste-bom').val()) || 0;
            const hpp = unformatNumberID($(this).find('.inp-hpp-bom').val());

            if (barangMentahId && qty > 0 && satuanId) {
                formData.items.push({
                    barang_mentah_id: barangMentahId,
                    jumlah_kebutuhan: qty,
                    satuan_id: satuanId,
                    waste_percent: waste,
                    estimasi_harga_satuan: hpp
                });
            }
        });

        if (formData.items.length === 0) {
            $btnDraft.prop('disabled', false);
            $btnPending.prop('disabled', false);
            Swal.fire('Validasi Gagal', 'Silakan masukkan minimal 1 bahan baku yang valid!', 'warning');
            return;
        }

        if (formData.items.length > 50) {
            $btnDraft.prop('disabled', false);
            $btnPending.prop('disabled', false);
            Swal.fire({
                icon: 'warning',
                title: 'Batas Maksimal Bahan!',
                text: `Maksimal bahan baku / aksesoris adalah 50 item! (Saat ini: ${formData.items.length} item). Silakan hapus item yang berlebih.`,
                confirmButtonText: 'OK'
            });
            return;
        }

        $.ajax({
            url: '{{ route("produksi.bom.store") }}',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(res) {
                $('#modalTambahBom').modal('hide');
                $btnDraft.prop('disabled', false);
                $btnPending.prop('disabled', false);
                Swal.fire({
                    icon: 'success',
                    title: statusTarget === 'Draft' ? 'Draft Tersimpan!' : 'Berhasil Diajukan!',
                    text: res.message || (statusTarget === 'Draft' ? 'Resep BOM berhasil disimpan sebagai Draft.' : 'Resep BOM berhasil diajukan ke Pending Approval.'),
                    timer: 2200,
                    showConfirmButton: false
                }).then(() => {
                    reloadTableBom();
                });
            },
            error: function(xhr) {
                $btnDraft.prop('disabled', false);
                $btnPending.prop('disabled', false);
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat menyimpan BOM.', 'error');
            }
        });
    }

    $(document).on('input', '#inp_nama_bom, #edit_nama_bom', function() {
        if (this.value.length >= 40) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Maksimal!',
                text: 'Nama Resep BOM maksimal 40 karakter.',
                timer: 2200,
                showConfirmButton: false
            });
        }
    });

    $(document).on('input', '#inp_kode_bom, #edit_kode_bom', function() {
        if (this.value.length >= 20) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Maksimal!',
                text: 'Kode BOM maksimal 20 karakter.',
                timer: 2200,
                showConfirmButton: false
            });
        }
    });

    $(document).on('input', '#inp_biaya_tk, #edit_biaya_tk', function() {
        if (this.value.length >= 15) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Maksimal!',
                text: 'Biaya Tenaga Kerja / CMT maksimal 15 karakter.',
                timer: 2200,
                showConfirmButton: false
            });
        }
    });

    $(document).on('input', '#inp_biaya_oh, #edit_biaya_oh', function() {
        if (this.value.length >= 15) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Karakter Maksimal!',
                text: 'Biaya Overhead Produksi maksimal 15 karakter.',
                timer: 2200,
                showConfirmButton: false
            });
        }
    });

    function formatLiveRibuanID(val) {
        if (val === null || val === undefined || val === '') return '';
        let str = val.toString().replace(/[^0-9.,]/g, '');
        let parts = str.split(',');
        let intRaw = parts[0].replace(/\./g, '');
        let intFormatted = intRaw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (parts.length > 1) {
            let decRaw = parts.slice(1).join('').replace(/[^0-9]/g, '');
            return intFormatted + ',' + decRaw;
        }
        return intFormatted;
    }
    window.formatLiveRibuanID = formatLiveRibuanID;

    $(document).on('input', '.inp-hpp-formatted', function() {
        let cursorPosition = this.selectionStart;
        let oldLength = this.value.length;
        let val = $(this).val();
        let formatted = formatLiveRibuanID(val);
        $(this).val(formatted);
        let newLength = formatted.length;
        let newPos = Math.max(0, cursorPosition + (newLength - oldLength));
        if (this.setSelectionRange) {
            this.setSelectionRange(newPos, newPos);
        }
    });
</script>
@endpush
