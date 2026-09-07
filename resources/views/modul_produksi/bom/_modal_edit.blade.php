{{-- MODAL EDIT BOM PRODUKSI GARMEN --}}
<div class="modal fade bom-modal-primary" id="modalEditBom" tabindex="-1" aria-labelledby="modalEditBomLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" style="max-width: 1350px;">
        <div class="modal-content bom-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- MODAL HEADER (CINEMATIC AMBER GRADIENT) --}}
            <div class="modal-header bom-modal-header-amber p-3.5 p-md-4 border-0 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);">
                <!-- Decorative Ambient Glow -->
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>
                
                <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-pencil-square fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-extrabold text-white mb-0" id="modalEditBomLabel" style="letter-spacing: -0.2px;">
                            Edit Resep Produksi Garmen <span class="opacity-75 fs-6 fw-normal">(Bill of Materials)</span>
                        </h5>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 12px;">
                            Perbarui komposisi bahan baku, waste %, alokasi CMT, dan estimasi HPP resep
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            <form id="formEditBom" autocomplete="off">
                @csrf
                <input type="hidden" id="edit_bom_id">
                <div class="modal-body bom-modal-body p-3 p-md-4" style="background: var(--bg-main, #0f172a);">
                    
                    {{-- BANNER REVISI JIKA RESEP DITOLAK --}}
                    <div id="alert_rejected_revisi" class="alert alert-danger d-none border-danger d-flex align-items-center gap-2 mb-3.5 p-3 rounded-4 shadow-sm" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35);">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0"></i>
                        <div>
                            <div class="fw-bold text-danger">Pengajuan Resep Sebelumnya Ditolak (Revisi Diperlukan)</div>
                            <small id="text_alasan_revisi" class="text-white-50 fw-semibold"></small>
                        </div>
                    </div>

                    {{-- SECTION 1: HEADER RESEP & FOTO PREVIEW CARD --}}
                    <div class="card border-0 rounded-4 shadow-sm mb-3.5 p-3 p-md-3.5 position-relative overflow-hidden" 
                         style="background: var(--bg-card, #1e293b); border: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: rgba(255,255,255,0.08) !important;">
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                <i class="bi bi-info-circle-fill" style="font-size: 12px;"></i>
                            </span>
                            <span class="fw-bold text-theme-main small text-uppercase" style="letter-spacing: 0.5px;">1. Data Pokok Resep Produk</span>
                        </div>

                        <div class="row g-3">
                            {{-- LEFT COL: INPUT FORM FIELDS --}}
                            <div class="col-12 col-lg-9">
                                <div class="row g-2.5 g-md-3">
                                    {{-- Barang Jadi --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-box-seam text-warning me-1"></i> Produk Barang Jadi <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="hidden" name="barang_jadi_id" id="edit_barang_jadi_id" required>
                                            <input type="text" id="edit_barang_jadi_name" class="form-control rounded-start-3 bg-light-theme cursor-pointer"
                                                   placeholder="-- Klik 🔍 untuk pilih Barang Jadi --" readonly required onclick="bukaModalLookupBarangBom('jadi', 'edit_jadi')">
                                            <button type="button" class="btn btn-warning text-white rounded-end-3 px-3 shadow-xs" onclick="bukaModalLookupBarangBom('jadi', 'edit_jadi')" title="Cari Barang Jadi" style="background-color: #d97706;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Nama Resep BOM --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-card-text text-warning me-1"></i> Nama Resep BOM <span class="text-danger">*</span> <small class="text-muted">(Maks 40)</small>
                                        </label>
                                        <input type="text" name="nama_bom" id="edit_nama_bom" class="form-control rounded-3" maxlength="40" required>
                                    </div>

                                    {{-- Target Qty Hasil --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-bullseye text-warning me-1"></i> Qty Hasil
                                        </label>
                                        <input type="text" name="jumlah_hasil" id="edit_jumlah_hasil" class="form-control rounded-3 text-center fw-bold bg-light-theme"
                                               value="1" readonly style="cursor: not-allowed;" title="Resep BOM dihitung per 1 Pcs / Satuan">
                                    </div>

                                    {{-- Satuan Hasil --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-tag text-warning me-1"></i> Satuan Hasil <span class="text-danger">*</span>
                                        </label>
                                        <input type="hidden" name="satuan_hasil_id" id="edit_satuan_hasil_id" required>
                                        <input type="text" id="edit_satuan_hasil_name" class="form-control rounded-3 bg-light-theme text-center fw-semibold"
                                               placeholder="Otomatis dari barang" readonly
                                               style="cursor: default; color: var(--bom-text-muted);">
                                    </div>

                                    {{-- Kode BOM (Readonly) --}}
                                    <div class="col-12 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-upc-scan text-warning me-1"></i> Kode BOM <small class="text-muted">(Maks 20)</small>
                                        </label>
                                        <input type="text" id="edit_kode_bom" class="form-control rounded-3 bg-light-theme font-monospace" maxlength="20" readonly>
                                    </div>

                                    {{-- Tipe BOM --}}
                                    <div class="col-12 col-md-3">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-diagram-3 text-warning me-1"></i> Tipe Produksi
                                        </label>
                                        <select name="tipe_bom" id="edit_tipe_bom" class="form-select rounded-3">
                                            <option value="Stok (Make to Stock)">Stok (Make to Stock)</option>
                                            <option value="Tender / Project">Tender / Project (Make to Order)</option>
                                            <option value="Garmen">Garmen (Konveksi / Textile)</option>
                                            <option value="Umum">Umum / Assembler</option>
                                        </select>
                                    </div>

                                    {{-- Link Tender / Project --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                                            <i class="bi bi-briefcase text-warning me-1"></i> Tender / Project Terkait <small class="text-muted">(Opsional)</small>
                                        </label>
                                        <select name="tender_id" id="edit_tender_id" class="form-select rounded-3">
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
                                    <i class="bi bi-image text-warning me-1"></i> Pratinjau Foto Produk
                                </label>
                                <div class="card border rounded-3 bg-light-theme shadow-xs overflow-hidden p-2 text-center" 
                                     style="min-height: 190px; border-color: rgba(255,255,255,0.08) !important;">
                                    {{-- Empty State --}}
                                    <div id="preview_box_no_image_edit" class="d-flex flex-column align-items-center justify-content-center h-100 py-3">
                                        <i class="bi bi-image text-muted opacity-50 display-5 d-block mb-1"></i>
                                        <small class="text-theme-muted" style="font-size: 11px;">Pilih Produk Barang Jadi untuk melihat foto</small>
                                    </div>

                                    {{-- Has Image State / Carousel --}}
                                    <div id="preview_box_has_image_edit" class="d-none h-100 d-flex flex-column justify-content-center align-items-center">
                                        <div id="container_img_preview_edit" class="position-relative w-100 h-100 d-flex align-items-center justify-content-center cursor-pointer"
                                             style="min-height: 165px; background: transparent !important; border-radius: 10px; overflow: hidden;"
                                             onclick="navigasiFotoEdit(1)">
                                            <img id="img_preview_barang_jadi_edit" src="" class="rounded" style="max-height: 165px; width: 100%; object-fit: contain; background: transparent !important;">

                                            {{-- Carousel Nav Controls --}}
                                            <button type="button" id="btn_prev_foto_edit" class="btn btn-sm btn-dark bg-opacity-75 border-0 rounded-circle position-absolute start-0 ms-1 d-none" onclick="event.stopPropagation(); navigasiFotoEdit(-1);" style="width: 28px; height: 28px; padding: 0; z-index: 10;">
                                                <i class="bi bi-chevron-left text-white"></i>
                                            </button>
                                            <button type="button" id="btn_next_foto_edit" class="btn btn-sm btn-dark bg-opacity-75 border-0 rounded-circle position-absolute end-0 me-1 d-none" onclick="event.stopPropagation(); navigasiFotoEdit(1);" style="width: 28px; height: 28px; padding: 0; z-index: 10;">
                                                <i class="bi bi-chevron-right text-white"></i>
                                            </button>
                                            <span id="badge_foto_counter_edit" class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 end-0 m-1 px-2 py-1 rounded-pill d-none" style="font-size: 10px; z-index: 10;">1/1</span>
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
                                <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                    <i class="bi bi-box-seam-fill" style="font-size: 12px;"></i>
                                </span>
                                <h6 class="fw-bold text-theme-main mb-0 small text-uppercase" style="letter-spacing: 0.5px;">
                                    2. Komposisi Bahan Baku & Aksesoris Jahit
                                </h6>
                                <span class="badge bg-secondary bg-opacity-10 text-theme-main border-0 ms-1 px-2.5 py-1 rounded-pill font-monospace" id="badge_count_items_edit" style="font-size: 11px;">0 / 50</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning text-white rounded-pill px-3.5 py-1.5 fw-bold shadow-sm flex-shrink-0 text-nowrap mt-2 mt-sm-0" onclick="tambahBarisItemBomEdit()" 
                                    style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); width: auto; max-width: max-content;">
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
                                <tbody id="tbodyItemBomEdit">
                                    {{-- Populated via AJAX --}}
                                </tbody>
                                <tfoot class="fw-bold bg-light-theme">
                                    <tr>
                                        <td colspan="5" class="text-end text-theme-muted">SUBTOTAL BAHAN BAKU:</td>
                                        <td class="text-end text-warning font-monospace fs-6" id="lblSubtotalBahanEdit">Rp 0</td>
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
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-30 rounded-circle p-1.5 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                <i class="bi bi-cash-stack" style="font-size: 12px;"></i>
                            </span>
                            <span class="fw-bold text-theme-main small text-uppercase" style="letter-spacing: 0.5px;">3. Alokasi Biaya & Estimasi HPP Resep</span>
                        </div>

                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-person-gear text-warning me-1"></i> Biaya Tenaga Kerja / CMT (Rp)
                                </label>
                                <input type="text" name="biaya_tenaga_kerja" id="edit_biaya_tk" class="form-control text-end inp-hpp-formatted"
                                       maxlength="15" placeholder="0" oninput="kalkulasiTotalBomEdit()">
                                <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Estimasi ongkos jahit / pola / obras</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-lightning-charge text-warning me-1"></i> Biaya Overhead Produksi (Rp)
                                </label>
                                <input type="text" name="biaya_overhead" id="edit_biaya_oh" class="form-control text-end inp-hpp-formatted"
                                       maxlength="15" placeholder="0" oninput="kalkulasiTotalBomEdit()">
                                <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Estimasi listrik, plastik, hangtag, dll</small>
                            </div>

                            {{-- GLOWING NEON HPP CARD --}}
                            <div class="col-12 col-md-4">
                                <div class="p-3 rounded-4 position-relative overflow-hidden text-end shadow-sm" 
                                     style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.22) 100%); border: 1px solid rgba(16, 185, 129, 0.35); box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.25) !important;">
                                    <small class="text-uppercase fw-bold text-success d-block" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="bi bi-check2-circle me-1"></i> Estimasi Total HPP / Satuan
                                    </small>
                                    <span class="fs-4 fw-extrabold text-success font-monospace d-block mt-1" id="lblTotalHppEdit">Rp 0 / Pcs</span>
                                    <small class="text-theme-muted d-block mt-0.5" style="font-size: 10.5px;">Bahan + CMT + Overhead</small>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan & Status Resep --}}
                        <div class="row g-3 mb-0">
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-chat-left-text text-warning me-1"></i> Keterangan & Catatan Spesifikasi Jahit
                                </label>
                                <input type="text" name="keterangan" id="edit_keterangan_bom" class="form-control rounded-3"
                                       placeholder="Catatan tambahan spesifikasi jahit...">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-theme-muted small mb-1">
                                    <i class="bi bi-flag-fill text-warning me-1"></i> Status Resep BOM
                                </label>
                                <select name="status" id="edit_status_bom" class="form-select rounded-3">
                                    <option value="Approved">Disetujui (Approved)</option>
                                    <option value="Completed">Selesai (Completed)</option>
                                    <option value="Pending">Pending Approval</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Inactive">Non-Aktif (Inactive)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer bom-modal-footer justify-content-between p-3 p-md-3.5 border-0" 
                     style="background: var(--bg-card, #1e293b); border-top: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> Batal
                    </button>
                    <button type="button" id="btnSimpanBomEdit" class="btn text-white rounded-pill px-4 py-2 fw-bold shadow-lg" onclick="submitFormEditBom()" 
                            style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 6px 20px -2px rgba(217, 119, 6, 0.5) !important;">
                        <i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let bomEditRowIndex = 0;

    function cleanNumberInputValue(val) {
        if (val === null || val === undefined || val === '') return '';
        let num = parseFloat(val);
        if (isNaN(num)) return '';
        return parseFloat(num.toFixed(4)).toString();
    }

    let currentPreviewFotosEdit = [];
    let currentFotoIndexEdit = 0;

    function updatePreviewFotoEdit(fotosArray) {
        currentPreviewFotosEdit = (fotosArray && fotosArray.length > 0) ? fotosArray : [];
        currentFotoIndexEdit = 0;

        if (currentPreviewFotosEdit.length > 0) {
            renderFotoCarouselEdit();
            $('#preview_box_no_image_edit').addClass('d-none');
            $('#preview_box_has_image_edit').removeClass('d-none').addClass('d-flex');
        } else {
            $('#preview_box_has_image_edit').addClass('d-none').removeClass('d-flex');
            $('#preview_box_no_image_edit').removeClass('d-none');
        }
    }

    function renderFotoCarouselEdit() {
        if (currentPreviewFotosEdit.length === 0) return;
        const url = currentPreviewFotosEdit[currentFotoIndexEdit];
        $('#img_preview_barang_jadi_edit').attr('src', url);

        if (currentPreviewFotosEdit.length > 1) {
            $('#btn_prev_foto_edit, #btn_next_foto_edit, #badge_foto_counter_edit').removeClass('d-none');
            $('#badge_foto_counter_edit').text(`${currentFotoIndexEdit + 1}/${currentPreviewFotosEdit.length}`);
        } else {
            $('#btn_prev_foto_edit, #btn_next_foto_edit, #badge_foto_counter_edit').addClass('d-none');
        }
    }

    function navigasiFotoEdit(direction) {
        if (currentPreviewFotosEdit.length <= 1) return;
        currentFotoIndexEdit += direction;
        if (currentFotoIndexEdit < 0) {
            currentFotoIndexEdit = currentPreviewFotosEdit.length - 1;
        } else if (currentFotoIndexEdit >= currentPreviewFotosEdit.length) {
            currentFotoIndexEdit = 0;
        }
        renderFotoCarouselEdit();
    }

    function updateCountItemsEdit() {
        const count = $('#tbodyItemBomEdit tr').length;
        $('#badge_count_items_edit').text(`${count} / 50`);
        if (count >= 50) {
            $('#badge_count_items_edit').removeClass('bg-secondary bg-opacity-10 text-theme-main').addClass('bg-danger text-white');
        } else {
            $('#badge_count_items_edit').removeClass('bg-danger text-white').addClass('bg-secondary bg-opacity-10 text-theme-main');
        }
    }

    function editBom(id) {
        $('#formEditBom')[0].reset();
        $('#tbodyItemBomEdit').empty();
        updatePreviewFotoEdit([]);
        bomEditRowIndex = 0;
        $('#edit_bom_id').val(id);
        $('#btnSimpanBomEdit').prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM');

        $.ajax({
            url: `/produksi/bom/${id}/edit`,
            method: 'GET',
            success: function(res) {
                const b = res.bom;
                $('#edit_kode_bom').val(b.kode_bom);
                $('#edit_nama_bom').val(b.nama_bom);
                $('#edit_barang_jadi_id').val(b.barang_jadi_id);
                $('#edit_barang_jadi_name').val(b.barang_jadi ? `[${b.barang_jadi.kode_barang}] ${b.barang_jadi.nama_barang}` : '-');
                $('#edit_jumlah_hasil').val(cleanNumberInputValue(b.jumlah_hasil) || '1');
                $('#edit_satuan_hasil_id').val(b.satuan_hasil_id);
                $('#edit_satuan_hasil_name').val(b.satuan_hasil ? b.satuan_hasil.nama_satuan : '');
                $('#edit_tipe_bom').val(b.tipe_bom || 'Garmen');
                $('#edit_tender_id').val(b.tender_id || '');
                
                let currentStatus = b.status || 'Draft';
                if (currentStatus === 'Rejected') {
                    $('#alert_rejected_revisi').removeClass('d-none');
                    $('#text_alasan_revisi').text(b.keterangan || 'Resep ditolak oleh atasan, silakan perbaiki dan ajukan kembali.');
                    $('#edit_status_bom').html(`
                        <option value="Pending" selected>Ajukan Kembali (Pending Approval)</option>
                        <option value="Approved">Disetujui (Approved)</option>
                        <option value="Draft">Simpan Revisi (Draft)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                } else if (currentStatus === 'Draft') {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="Draft" selected>Tetap Draft (Hanya Pembuat)</option>
                        <option value="Pending">Ajukan ke Pending Approval</option>
                        <option value="Approved">Disetujui Langsung (Approved)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                } else if (currentStatus === 'Pending') {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="Pending" selected>Tetap Pending Approval</option>
                        <option value="Approved">Disetujui (Approved)</option>
                        <option value="Draft">Kembalikan ke Draft</option>
                        <option value="Rejected">Tolak Resep (Rejected)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                } else if (currentStatus === 'Completed' || currentStatus === 'Selesai') {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="Completed" selected>Tetap Selesai (Completed)</option>
                        <option value="Approved">Ubah ke Disetujui (Approved / Aktif)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                } else if (currentStatus === 'Approved' || currentStatus === 'Active') {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="Approved" selected>Disetujui (Approved)</option>
                        <option value="Completed">Tandai Selesai (Completed)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                } else if (currentStatus === 'Inactive') {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="Inactive" selected>Tetap Non-Aktif (Inactive)</option>
                        <option value="Approved">Aktifkan (Disetujui / Approved)</option>
                        <option value="Draft">Aktifkan Kembali (Draft)</option>
                        <option value="Pending">Aktifkan & Ajukan ke Pending</option>
                    `);
                } else {
                    $('#alert_rejected_revisi').addClass('d-none');
                    $('#edit_status_bom').html(`
                        <option value="${currentStatus}" selected>Tetap (${currentStatus})</option>
                        <option value="Approved">Disetujui (Approved)</option>
                        <option value="Completed">Selesai (Completed)</option>
                        <option value="Inactive">Non-Aktifkan Resep (Inactive)</option>
                    `);
                }

                $('#edit_biaya_tk').val(formatRibuanID(b.biaya_tenaga_kerja));
                $('#edit_biaya_oh').val(formatRibuanID(b.biaya_overhead));
                $('#edit_keterangan_bom').val(b.keterangan);

                let fotosArr = [];
                if (b.barang_jadi) {
                    if (b.barang_jadi.primary_foto) {
                        fotosArr.push(`/storage/${b.barang_jadi.primary_foto.foto_path}`);
                    }
                    if (b.barang_jadi.fotos && b.barang_jadi.fotos.length > 0) {
                        b.barang_jadi.fotos.forEach(f => {
                            const u = `/storage/${f.foto_path}`;
                            if (!fotosArr.includes(u)) fotosArr.push(u);
                        });
                    }
                }
                updatePreviewFotoEdit(fotosArr);

                if (res.items && res.items.length > 0) {
                    res.items.forEach(item => {
                        tambahBarisItemBomEdit(item);
                    });
                } else {
                    tambahBarisItemBomEdit();
                }

                updateCountItemsEdit();
                kalkulasiTotalBomEdit();
                $('#modalEditBom').modal('show');
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat data BOM.', 'error');
            }
        });
    }

    function tambahBarisItemBomEdit(itemData = null) {
        if ($('#tbodyItemBomEdit tr').length >= 50) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Maksimal Bahan Tercapai!',
                text: 'Maksimal bahan baku / aksesoris adalah 50 item. Anda tidak dapat menambahkan lebih dari 50 item!',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        bomEditRowIndex++;
        const rowId = `row_edit_${bomEditRowIndex}_${Date.now()}`;

        let satuanOptionsHtml = '<option value="">-- Satuan --</option>';
        @foreach($satuans as $s)
            satuanOptionsHtml += `<option value="{{ $s->id }}">{{ $s->nama_satuan }}</option>`;
        @endforeach

        const barangMentahId = itemData ? itemData.barang_mentah_id : '';
        const barangMentahName = itemData && itemData.barang_mentah ? `[${itemData.barang_mentah.kode_barang}] ${itemData.barang_mentah.nama_barang}` : '';
        const qty = itemData ? cleanNumberInputValue(itemData.jumlah_kebutuhan) : '';
        const satuanId = itemData ? itemData.satuan_id : '';
        const waste = itemData ? cleanNumberInputValue(itemData.waste_percent) : '0';
        const hpp = itemData ? formatRibuanID(itemData.estimasi_harga_satuan) : '0';
        let foto = null;
        if (itemData && itemData.barang_mentah) {
            const bm = itemData.barang_mentah;
            if (bm.foto_url) {
                foto = bm.foto_url;
            } else if (bm.primary_foto && bm.primary_foto.foto_path) {
                foto = bm.primary_foto.foto_path.startsWith('http') ? bm.primary_foto.foto_path : `/storage/${bm.primary_foto.foto_path}`;
            } else if (bm.primaryFoto && bm.primaryFoto.foto_path) {
                foto = bm.primaryFoto.foto_path.startsWith('http') ? bm.primaryFoto.foto_path : `/storage/${bm.primaryFoto.foto_path}`;
            } else if (bm.fotos && bm.fotos.length > 0 && bm.fotos[0].foto_path) {
                foto = bm.fotos[0].foto_path.startsWith('http') ? bm.fotos[0].foto_path : `/storage/${bm.fotos[0].foto_path}`;
            }
        }

        const trHtml = `
            <tr id="${rowId}">
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="product-thumb-box border rounded-3 d-flex align-items-center justify-content-center bg-light-theme cursor-pointer shadow-sm"
                             style="width: 32px; height: 32px; flex-shrink: 0; overflow: hidden;"
                             title="Foto Barang">
                            <img src="${foto || ''}" id="img_thumb_${rowId}" class="product-img ${foto ? '' : 'd-none'}" style="width: 100%; height: 100%; object-fit: cover;">
                            <i class="bi bi-image text-muted fs-6 icon-no-photo-${rowId} ${foto ? 'd-none' : ''}"></i>
                        </div>

                        <div class="input-group input-group-sm flex-grow-1">
                            <input type="hidden" name="items[${bomEditRowIndex}][barang_mentah_id]" class="inp-barang-mentah-id" value="${barangMentahId}" required data-row="${rowId}">
                            <input type="text" class="form-control form-control-sm bg-light-theme inp-barang-name cursor-pointer" 
                                   value="${escapeHtmlBom(barangMentahName)}" placeholder="-- Klik 🔍 cari bahan --" readonly required data-row="${rowId}"
                                   onclick="bukaModalLookupBarangBom('${rowId}', 'edit')">
                            <button type="button" class="btn btn-warning text-white btn-search-barang-bom" onclick="bukaModalLookupBarangBom('${rowId}', 'edit')" title="Cari Bahan" style="background-color: #d97706;">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="number" step="any" min="0" name="items[${bomEditRowIndex}][jumlah_kebutuhan]" class="form-control form-control-sm text-center inp-qty-bom" value="${qty}" placeholder="0" required data-row="${rowId}" oninput="hitungkanSubtotalRowBomEdit('${rowId}')">
                    <div class="text-center mt-1 font-monospace inp-bruto-${rowId}" style="font-size: 11px; color: #38bdf8;" title="Kebutuhan kotor termasuk waste %">
                        Bruto: <span class="val-bruto fw-bold" style="color: #fbbf24;">0</span>
                    </div>
                </td>
                <td>
                    <input type="hidden" name="items[${bomEditRowIndex}][satuan_id]" class="inp-satuan-id select-satuan" value="${satuanId}" required>
                    <input type="text" class="form-control form-control-sm text-center bg-light-theme inp-satuan-name" value="${itemData && itemData.satuan ? escapeHtmlBom(itemData.satuan.nama_satuan) : ''}" placeholder="Otomatis" readonly style="cursor: default;">
                </td>
                <td>
                    <input type="number" step="any" min="0" max="100" name="items[${bomEditRowIndex}][waste_percent]" class="form-control form-control-sm text-center inp-waste-bom" value="${waste}" placeholder="0" data-row="${rowId}" oninput="hitungkanSubtotalRowBomEdit('${rowId}')">
                </td>
                <td>
                    <input type="text" name="items[${bomEditRowIndex}][estimasi_harga_satuan]" class="form-control form-control-sm text-end inp-hpp-formatted inp-hpp-bom" value="${hpp}" placeholder="0" data-row="${rowId}" oninput="hitungkanSubtotalRowBomEdit('${rowId}')">
                </td>
                <td class="text-end fw-bold text-warning font-monospace inp-subtotal-${rowId}">Rp 0</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="hapusBarisItemBomEdit('${rowId}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyItemBomEdit').append(trHtml);
        if (satuanId) {
            $(`#${rowId} .select-satuan`).val(satuanId);
        }
        updateCountItemsEdit();
        hitungkanSubtotalRowBomEdit(rowId);
    }

    function hapusBarisItemBomEdit(rowId) {
        if ($('#tbodyItemBomEdit tr').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 bahan baku!', 'info');
            return;
        }
        $(`#${rowId}`).remove();
        updateCountItemsEdit();
        kalkulasiTotalBomEdit();
    }

    function hitungkanSubtotalRowBomEdit(rowId) {
        const qty = parseFloat($(`#${rowId} .inp-qty-bom`).val()) || 0;
        const waste = parseFloat($(`#${rowId} .inp-waste-bom`).val()) || 0;
        const hpp = unformatNumberID($(`#${rowId} .inp-hpp-bom`).val());

        const grossQty = qty * (1 + (waste / 100));
        const subtotal = grossQty * hpp;

        $(`#${rowId} .inp-bruto-${rowId} .val-bruto`).text(cleanNumberInputValue(grossQty));
        $(`.inp-subtotal-${rowId}`).text('Rp ' + numberFormatID(subtotal));
        kalkulasiTotalBomEdit();
    }

    let isCalcBomEditPending = false;
    function kalkulasiTotalBomEdit() {
        if (isCalcBomEditPending) return;
        isCalcBomEditPending = true;

        requestAnimationFrame(() => {
            let totalSubtotalBahan = 0;

            $('#tbodyItemBomEdit tr').each(function() {
                const qty = parseFloat($(this).find('.inp-qty-bom').val()) || 0;
                const waste = parseFloat($(this).find('.inp-waste-bom').val()) || 0;
                const hpp = unformatNumberID($(this).find('.inp-hpp-bom').val());

                const grossQty = qty * (1 + (waste / 100));
                totalSubtotalBahan += (grossQty * hpp);
            });

            const targetHasil = parseFloat($('#edit_jumlah_hasil').val()) || 1;
            const tk = unformatNumberID($('#edit_biaya_tk').val());
            const oh = unformatNumberID($('#edit_biaya_oh').val());

            const totalHpp = totalSubtotalBahan + tk + oh;
            const hppPerUnit = targetHasil > 0 ? (totalHpp / targetHasil) : totalHpp;

            $('#lblSubtotalBahanEdit').text('Rp ' + numberFormatID(totalSubtotalBahan));
            $('#lblTotalHppEdit').text('Rp ' + numberFormatID(hppPerUnit) + ' / Unit');
            isCalcBomEditPending = false;
        });
    }

    function submitFormEditBom() {
        const id = $('#edit_bom_id').val();
        const barangJadiId = $('#edit_barang_jadi_id').val();
        const namaBomVal = ($('#edit_nama_bom').val() || '').trim();
        const kodeBomVal = ($('#edit_kode_bom').val() || '').trim();

        if (!barangJadiId) {
            Swal.fire({
                icon: 'warning',
                title: 'Barang Jadi Wajib Dipilih!',
                text: 'Silakan pilih Produk Barang Jadi (Hasil Produksi) terlebih dahulu!',
                confirmButtonText: '<i class="bi bi-box-seam me-1"></i> Pilih Barang Jadi'
            }).then(() => {
                bukaModalLookupBarangBom('edit_jadi', 'edit_jadi');
            });
            return;
        }

        if (!namaBomVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Nama Resep BOM Wajib Diisi!',
                text: 'Silakan ketikkan Nama Resep Produksi / BOM terlebih dahulu!'
            }).then(() => {
                $('#edit_nama_bom').focus();
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

        const $btn = $('#btnSimpanBomEdit');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...');

        const formData = {
            _token: '{{ csrf_token() }}',
            nama_bom: $('#edit_nama_bom').val(),
            barang_jadi_id: $('#edit_barang_jadi_id').val(),
            tender_id: $('#edit_tender_id').val(),
            jumlah_hasil: $('#edit_jumlah_hasil').val(),
            satuan_hasil_id: $('#edit_satuan_hasil_id').val(),
            tipe_bom: $('#edit_tipe_bom').val(),
            biaya_tenaga_kerja: unformatNumberID($('#edit_biaya_tk').val()),
            biaya_overhead: unformatNumberID($('#edit_biaya_oh').val()),
            keterangan: $('#edit_keterangan_bom').val(),
            status: $('#edit_status_bom').val(),
            items: []
        };

        $('#tbodyItemBomEdit tr').each(function() {
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
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM');
            Swal.fire('Validasi Gagal', 'Silakan masukkan minimal 1 bahan baku yang valid!', 'warning');
            return;
        }

        if (formData.items.length > 50) {
            $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM');
            Swal.fire({
                icon: 'warning',
                title: 'Batas Maksimal Bahan!',
                text: `Maksimal bahan baku / aksesoris adalah 50 item! (Saat ini: ${formData.items.length} item). Silakan hapus item yang berlebih.`,
                confirmButtonText: 'OK'
            });
            return;
        }

        $.ajax({
            url: `/produksi/bom/${id}`,
            method: 'PUT',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(res) {
                $('#modalEditBom').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    if (typeof reloadTableBom === 'function') {
                        reloadTableBom();
                    } else {
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat memperbarui BOM.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM');
            }
        });
    }

    $('#modalEditBom').on('show.bs.modal hidden.bs.modal', function() {
        $('#btnSimpanBomEdit').prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Update Resep BOM');
    });

    if (typeof window.formatLiveRibuanID !== 'function') {
        window.formatLiveRibuanID = function(val) {
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
        };
    }

    $(document).on('input', '#modalEditBom .inp-hpp-formatted', function() {
        let cursorPosition = this.selectionStart;
        let oldLength = this.value.length;
        let val = $(this).val();
        let formatted = window.formatLiveRibuanID(val);
        $(this).val(formatted);
        let newLength = formatted.length;
        let newPos = Math.max(0, cursorPosition + (newLength - oldLength));
        if (this.setSelectionRange) {
            this.setSelectionRange(newPos, newPos);
        }
    });
</script>
@endpush
