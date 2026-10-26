{{-- ===== MODAL EDIT TENDER (ULTRA-CINEMATIC DUAL-THEME DARK & LIGHT) ===== --}}


<div class="modal modal-blur fade" id="modalEditTender" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content tender-modal-content overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-1 pt-4 px-4 d-flex justify-content-between align-items-center position-relative z-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2.5 rounded-3 d-inline-flex align-items-center shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title tender-modal-title mb-0">Perbarui Data Prospek Tender</h5>
                        <div class="tender-modal-subtitle small fs-8 mt-0.5">Edit spesifikasi proyek, instansi klien, nilai pagu HPS, kebutuhan barang, dan berkas lelang.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditTender" autocomplete="off" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="edit_tender_id">

                <div class="modal-body p-4 pt-3">

                    <!-- CINEMATIC SEGMENTED TABS (3 TABS) -->
                    <div class="tender-tabs-container mb-4">
                        <ul class="nav nav-pills tender-nav-pills gap-2" id="editTabs" role="tablist">
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn active w-100" id="edit-umum-tab" data-bs-toggle="tab" data-bs-target="#edit-umum" type="button" role="tab">
                                    <i class="bi bi-info-circle-fill me-1.5"></i> 1. Info Utama & Klien
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn w-100" id="edit-barang-tab" data-bs-toggle="tab" data-bs-target="#edit-barang" type="button" role="tab">
                                    <i class="bi bi-cart-check-fill me-1.5"></i> 2. Kebutuhan Barang / Jasa <span class="text-danger fw-bold">*</span> <span class="badge bg-white text-dark rounded-pill ms-1 font-monospace" id="badge_edit_item_count">0</span>
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn w-100" id="edit-dokumen-tab" data-bs-toggle="tab" data-bs-target="#edit-dokumen" type="button" role="tab">
                                    <i class="bi bi-folder-fill me-1.5"></i> 3. Berkas Dokumen Lelang <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1 font-monospace" id="badge_edit_doc_count">0/30</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- TABS CONTENT -->
                    <div class="tab-content" id="editTabsContent">
                        
                        <!-- ==========================================
                             TAB 1: INFO UTAMA & INSTANSI KLIEN
                             ========================================== -->
                        <div class="tab-pane fade show active" id="edit-umum" role="tabpanel">
                            
                            <!-- Section 1: Identitas Tender -->
                            <div class="tender-form-card mb-3.5">
                                <div class="tender-card-label mb-3">
                                    <span class="glow-tag-indigo">
                                        <i class="bi bi-building me-1"></i> IDENTITAS PROYEK & KATEGORI INSTANSI
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-8">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label tender-form-label mb-0">Nama Proyek / Pengadaan Tender <span class="text-danger">*</span></label>
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_edit_nama_tender">0/50</span>
                                        </div>
                                        <input type="text" name="nama_tender" id="edit_nama_tender" class="form-control tender-input char-limited-input" maxlength="50" data-max="50" data-name="Nama Proyek / Tender" required>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label tender-form-label">Kategori Instansi / Klien <span class="text-danger">*</span></label>
                                        <select name="kategori_instansi" id="edit_kategori_instansi" class="form-select tender-input" required>
                                            <option value="Pemerintah / B2G">🏛️ Pemerintah (Dinas/Kementerian)</option>
                                            <option value="TNI / POLRI">🎖️ TNI / POLRI</option>
                                            <option value="BUMN / BUMD">🏢 BUMN / BUMD</option>
                                            <option value="Swasta / B2B">💼 Swasta</option>
                                            <option value="Lainnya">🌐 Lainnya</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Customer / Instansi Klien <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="hidden" name="customer_id" id="edit_customer_id" required>
                                            <input type="text" id="edit_customer_name" class="form-control tender-input rounded-start-3" readonly required>
                                            <button type="button" class="btn btn-primary btn-lookup-customer px-3.5 rounded-end-3" data-prefix="edit">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Sales Owner / PIC Proyek <span class="text-danger">*</span></label>
                                        <select name="sales_id" id="edit_sales_id" class="form-select tender-input" required>
                                            <option value="">-- Pilih Sales PIC --</option>
                                            @foreach($salesList as $sales)
                                                @php
                                                    $deptOrPos = $sales->department?->department_name ?: $sales->position?->position_name;
                                                @endphp
                                                <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }}{{ !empty($deptOrPos) ? ' (' . $deptOrPos . ')' : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Nilai Pagu & Anggaran -->
                            <div class="tender-form-card mb-3.5">
                                <div class="tender-card-label mb-3">
                                    <span class="glow-tag-emerald">
                                        <i class="bi bi-cash-stack me-1"></i> ESTIMASI ANGGARAN & PAGU HPS KLIEN
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label tender-form-label mb-0">Nilai Pagu Anggaran / HPS Proyek (Rp) <span class="text-danger">*</span></label>
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_edit_nilai_estimasi_mask">0/20</span>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text tender-input-addon font-monospace fw-bold">Rp</span>
                                            <input type="text" id="edit_nilai_estimasi_mask" class="form-control tender-input text-end font-monospace fw-bold text-success fs-6 char-limited-input" maxlength="20" data-max="20" data-name="Nilai Pagu Anggaran" placeholder="0" required>
                                            <input type="hidden" name="nilai_estimasi" id="edit_nilai_estimasi" value="0">
                                            <input type="hidden" name="nilai_deal" id="edit_nilai_deal" value="0">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label tender-form-label mb-0">No. Tender / No. Registrasi Proyek <span class="text-danger">*</span></label>
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_edit_nomor_kontrak">0/40</span>
                                        </div>
                                        <input type="text" name="nomor_kontrak" id="edit_nomor_kontrak" class="form-control tender-input font-monospace char-limited-input" maxlength="40" data-max="40" data-name="Nomor Tender / Registrasi" placeholder="Contoh: TND-2026-088 / PL-TNI-2026/01" required>
                                        <div class="form-text fs-9 text-muted mt-1">Nomor lelang / tender resmi yang didapatkan dari panitia atau dokumen proyek.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Timeline & Jadwal Lelang -->
                            <div class="tender-form-card mb-3.5">
                                <div class="tender-card-label mb-3">
                                    <span class="glow-tag-amber">
                                        <i class="bi bi-calendar-event-fill me-1"></i> JADWAL & STATUS TAHAP LELANG
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label tender-form-label">Tanggal Pembukaan <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control tender-input font-monospace" required>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label tender-form-label">Batas Deadline Dokumen <span class="text-danger">*</span></label>
                                        <input type="date" name="deadline" id="edit_deadline" class="form-control tender-input font-monospace text-danger fw-bold" required>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label tender-form-label">Estimasi Pengumuman / Deal <span class="text-danger">*</span></label>
                                        <input type="date" name="estimasi_closing" id="edit_estimasi_closing" class="form-control tender-input font-monospace" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Tingkat Prioritas <span class="text-danger">*</span></label>
                                        <select name="priority" id="edit_priority" class="form-select tender-input" required>
                                            <option value="High">🔥 Tinggi (High) - Prioritas Utama</option>
                                            <option value="Medium">⚡ Sedang (Medium)</option>
                                            <option value="Low">🌱 Rendah (Low)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Mata Uang (Currency)</label>
                                        <select name="currency" id="edit_currency" class="form-select tender-input font-monospace">
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }} ({{ $curr->simbol }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <input type="hidden" name="status" id="edit_status">
                                </div>
                            </div>

                            <!-- Section 4: Catatan Khusus -->
                            <div class="tender-form-card">
                                <div class="tender-card-label mb-2">
                                    <span class="glow-tag-purple">
                                        <i class="bi bi-file-earmark-text-fill me-1"></i> CATATAN TEKNIS & SYARAT KUALIFIKASI
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label tender-form-label mb-0">Catatan Khusus / Teknis</label>
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_edit_catatan">0/50</span>
                                        </div>
                                        <textarea name="catatan" id="edit_catatan" rows="3" class="form-control tender-input char-limited-input" maxlength="50" data-max="50" data-name="Catatan Khusus" placeholder="Syarat kualifikasi lelang, jadwal Aanwijzing, atau catatan internal..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ==========================================
                             TAB 2: DAFTAR BARANG YANG DIAJUKAN (PERSIS FORMAT STANDAR)
                             ========================================== -->
                        <div class="tab-pane fade" id="edit-barang" role="tabpanel">
                            
                            <div class="tender-form-card p-3 p-md-4 mb-3.5">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                            <i class="bi bi-list-task fs-6"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-body fs-6">Daftar Barang yang Diajukan</h6>
                                            <div class="text-muted small d-none d-md-block" style="font-size: 0.72rem;">Kebutuhan item barang, jasa, atau aset yang akan diajukan dalam lelang.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-body-tertiary text-muted border d-md-none" style="font-size: 0.7rem;">
                                            <i class="bi bi-arrow-left-right me-1"></i>Geser tabel
                                        </span>
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_edit_tender_item_row">
                                            <i class="bi bi-plus-circle-fill"></i> <span>Tambah Barang</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive tender-table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0 w-100 tender-modal-table" id="table_edit_items">
                                        <thead>
                                            <tr class="border-bottom text-muted small" style="font-size: 0.75rem; font-weight: 600;">
                                                <th style="width: 45px;" class="text-center py-2">No</th>
                                                <th style="width: 110px;">Tipe</th>
                                                <th style="min-width: 280px;">Nama Barang <span class="text-danger">*</span></th>
                                                <th style="width: 90px;" class="text-center">Satuan</th>
                                                <th style="width: 100px;">Jumlah <span class="text-danger">*</span></th>
                                                <th style="width: 150px;">Catatan</th>
                                                <th style="width: 45px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_edit_items">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- ==========================================
                             TAB 3: BERKAS DOKUMEN LELANG
                             ========================================== -->
                        <div class="tab-pane fade" id="edit-dokumen" role="tabpanel">
                            
                            <!-- Existing Documents -->
                            <div class="mb-3.5">
                                <div class="fw-bold tender-form-label fs-7 mb-2"><i class="bi bi-paperclip me-1"></i> Berkas Dokumen yang Sudah Tersimpan:</div>
                                <div id="edit_existing_documents_container" class="d-flex flex-column gap-1.5"></div>
                            </div>

                            <div class="tender-form-card p-3 p-md-4 mb-3.5 border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                            <i class="bi bi-paperclip fs-6"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-body fs-6">Unggah Berkas Baru / Tambahan</h6>
                                            <div class="text-muted small" style="font-size: 0.72rem;">Maksimal 30 berkas (PDF, Word, Excel, ZIP).</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-body-tertiary text-muted border d-md-none" style="font-size: 0.7rem;">
                                            <i class="bi bi-arrow-left-right me-1"></i>Geser tabel
                                        </span>
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_edit_dokumen_row">
                                            <i class="bi bi-plus-circle-fill"></i> <span>Tambah Lampiran</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive tender-table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0 w-100 tender-modal-doc-table" id="table_edit_dokumen">
                                        <thead>
                                            <tr class="border-bottom text-muted small" style="font-size: 0.75rem; font-weight: 600;">
                                                <th style="width: 45px;" class="text-center py-2">No</th>
                                                <th style="min-width: 200px;">Nama Dokumen</th>
                                                <th style="min-width: 240px;">Pilih Berkas</th>
                                                <th style="width: 45px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_edit_dokumen">
                                            <!-- Dynamic Docs -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- MODAL FOOTER ACTION BAR -->
                <div class="modal-footer tender-modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-tender-cancel rounded-pill px-4 py-2.5" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm tender-btn-submit text-dark" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border:none;">
                        <i class="bi bi-check-circle-fill fs-6"></i>
                        <span>Perbarui Prospek Tender</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let tenderItemIdxEdit = 0;
    let docIndexEdit = 0;

    if (typeof window.safeHtml !== 'function') {
        window.safeHtml = function(str) {
            if (!str && str !== 0) return '';
            return String(str).replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        };
    }

    if (typeof window.renumberTenderRows !== 'function') {
        window.renumberTenderRows = function(prefix) {
            $(`#tbody_${prefix}_items tr.item-row`).each(function(idx) {
                $(this).find('.row-number').text(idx + 1);
            });
            $(`#badge_${prefix}_item_count`).text($(`#tbody_${prefix}_items tr.item-row`).length);
        };
    }

    if (typeof window.renumberDocRows !== 'function') {
        window.renumberDocRows = function(prefix) {
            let count = $(`#tbody_${prefix}_dokumen tr`).length;
            if (prefix === 'edit') {
                let existingCount = $('#edit_existing_documents_container .existing-doc-card').length;
                let total = existingCount + count;
                $(`#badge_${prefix}_doc_count`).text(`${total}/30`);
                if (total >= 30) {
                    $(`#badge_${prefix}_doc_count`).removeClass('bg-secondary-subtle text-secondary').addClass('bg-danger text-white');
                } else {
                    $(`#badge_${prefix}_doc_count`).removeClass('bg-danger text-white').addClass('bg-secondary-subtle text-secondary');
                }
            } else {
                $(`#badge_${prefix}_doc_count`).text(`${count}/30`);
                if (count >= 30) {
                    $(`#badge_${prefix}_doc_count`).removeClass('bg-secondary-subtle text-secondary').addClass('bg-danger text-white');
                } else {
                    $(`#badge_${prefix}_doc_count`).removeClass('bg-danger text-white').addClass('bg-secondary-subtle text-secondary');
                }
            }
            $(`#tbody_${prefix}_dokumen tr`).each(function(idx) {
                $(this).find('.doc-row-number').text(idx + 1);
            });
        };
    }

    window.addTenderItemRowEdit = function(data = null) {
        tenderItemIdxEdit++;
        let uniqueId = tenderItemIdxEdit;

        let barangId = data ? (data.barang_id || '') : '';
        let barangKode = data ? (data.kode || '') : '';
        let barangNama = data ? (data.nama || '') : '';
        let satuan = data ? (data.satuan || 'Pcs') : '-';
        let qty = data ? (parseFloat(data.qty) || 1) : 1;
        let catatan = data ? (data.catatan_item || '') : '';
        let tipe = data ? (data.tipe || 'Stok') : 'Stok';

        let photoUrl = (data && data.photo_url) ? data.photo_url : (data && data.barang ? window.resolveTenderProductPhotoUrl(data.barang) : '');
        let photosList = (data && data.photos) ? data.photos : (data && data.barang ? window.extractProductPhotosList(data.barang) : []);
        if (photoUrl && photosList.length === 0) {
            photosList = [photoUrl];
        }

        let displayName = barangNama ? (barangKode ? `[${barangKode}] ${barangNama}` : barangNama) : '';

        let imgFrameHtml = '';
        if (photoUrl) {
            imgFrameHtml = `
                <div class="tender-product-img-frame img-preview-box position-relative cursor-pointer border rounded-2 overflow-hidden shadow-xs hover-scale" 
                    onclick="window.showProductPhotoPreviewFromRow(this, event)" 
                    title="${photosList.length > 1 ? `Klik untuk melihat & geser ${photosList.length} foto` : 'Klik untuk memperbesar foto'}"
                    style="width: 36px; height: 36px; flex-shrink: 0; background: #fff; cursor: pointer;">
                    <img src="${photoUrl}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='/uploads/barang/placeholder_garment.png'">
                    ${photosList.length > 1 ? `
                        <span class="position-absolute top-0 end-0 badge bg-dark bg-opacity-85 text-white font-monospace" style="font-size:0.52rem; padding: 1px 3px; transform: translate(15%, -15%);">
                            ${photosList.length}
                        </span>
                    ` : ''}
                </div>
            `;
        } else {
            imgFrameHtml = `
                <div class="tender-product-img-frame img-preview-box d-flex align-items-center justify-content-center border rounded-2 bg-body-tertiary text-muted" 
                    title="Foto Produk" 
                    style="width: 36px; height: 36px; flex-shrink: 0;">
                    <i class="bi bi-image fs-6"></i>
                </div>
            `;
        }

        let rowHtml = `
            <tr class="item-row" id="tnd_item_row_edit_${uniqueId}" data-index="${uniqueId}">
                <td class="text-center fw-semibold row-number py-2"></td>
                <td>
                    <select name="items[${uniqueId}][tipe_item]" class="form-select form-select-sm tender-input select-tipe-item" style="border-radius: 8px;">
                        <option value="Stok" ${tipe === 'Stok' ? 'selected' : ''}>Stok</option>
                        <option value="Jasa" ${tipe === 'Jasa' ? 'selected' : ''}>Jasa</option>
                        <option value="Aset" ${tipe === 'Aset' ? 'selected' : ''}>Aset</option>
                    </select>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1.5">
                        ${imgFrameHtml}
                        <div class="input-group input-group-sm flex-grow-1">
                            <input type="hidden" name="items[${uniqueId}][barang_id]" class="item-barang-id" value="${barangId}">
                            <input type="text" name="items[${uniqueId}][nama_barang_display]" class="form-control tender-input item-barang-name" value="${safeHtml(displayName)}" placeholder="Pilih barang..." readonly style="cursor: pointer; background: transparent;">
                            <button type="button" class="btn btn-outline-primary btn-open-tender-barang-lookup" data-row="${uniqueId}" data-prefix="edit" title="Cari Katalog">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill font-monospace item-satuan-badge">${safeHtml(satuan)}</span>
                    <input type="hidden" name="items[${uniqueId}][satuan]" class="item-satuan-input" value="${safeHtml(satuan)}">
                </td>
                <td>
                    <input type="number" step="any" min="0.01" name="items[${uniqueId}][qty]" class="form-control form-control-sm tender-input text-end font-monospace fw-bold item-qty-input" value="${qty}" required>
                </td>
                <td>
                    <input type="text" name="items[${uniqueId}][catatan_item]" class="form-control form-control-sm tender-input" placeholder="Catatan (Max 40)..." maxlength="40" value="${safeHtml(catatan)}">
                </td>
                <td class="text-center py-2">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-del-tnd-item-edit" data-id="${uniqueId}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        let $newRow = $(rowHtml);
        if (photosList && photosList.length > 0) {
            $newRow.data('photos', photosList);
            $newRow.data('photo-url', photoUrl);
        }
        $('#tbody_edit_items').append($newRow);
        renumberTenderRows('edit');
    };

    $(document).on('click', '#btn_edit_tender_item_row', function(e) {
        e.preventDefault();
        window.addTenderItemRowEdit();
    });

    $(document).on('click', '.btn-del-tnd-item-edit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $(`#tnd_item_row_edit_${id}`).remove();
        renumberTenderRows('edit');
    });

    // Format mask for Nilai Estimasi in Edit
    $(document).on('input', '#edit_nilai_estimasi_mask', function() {
        let val = $(this).val().replace(/\D/g, '');
        let num = Number(val) || 0;
        $(this).val(num > 0 ? num.toLocaleString('id-ID') : '');
        $('#edit_nilai_estimasi').val(num);
    });

    // Helper functions for Document rows in Edit (Delegated)
    $(document).on('click', '#btn_edit_dokumen_row', function(e) {
        e.preventDefault();
        let existingCount = $('#edit_existing_documents_container .existing-doc-card').length;
        let newCount = $('#tbody_edit_dokumen tr').length;
        if ((existingCount + newCount) >= 30) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Maksimal Berkas',
                text: `Maksimal 30 berkas dokumen yang dapat dilampirkan! (Sudah tersimpan: ${existingCount}, Berkas baru: ${newCount})`,
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        docIndexEdit++;
        let rowHtml = `
            <tr id="doc_row_edit_${docIndexEdit}">
                <td class="text-center fw-semibold doc-row-number py-2"></td>
                <td>
                    <input type="text" name="document_names[]" class="form-control tender-input form-control-sm" placeholder="Nama dokumen..." maxlength="40" required>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-muted"><i class="bi bi-file-earmark-arrow-up"></i></span>
                        <input type="file" name="documents[]" class="form-control tender-input tender-doc-file-input form-control-sm" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                        <span class="text-muted" style="font-size: 0.68rem;"><i class="bi bi-shield-check text-success me-0.5"></i> Max 10 MB (.pdf, .doc, .xls, .zip)</span>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace d-none file-size-badge" style="font-size: 0.65rem;"></span>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-del-doc-edit" data-id="${docIndexEdit}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tbody_edit_dokumen').append(rowHtml);
        renumberDocRows('edit');
    });

    $(document).on('click', '.btn-del-doc-edit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $(`#doc_row_edit_${id}`).remove();
        renumberDocRows('edit');
    });

    // Trigger Edit Click
    $(document).on('click', '.btn-edit-tender', function (e) {
        e.preventDefault();

        if (window.userPermissions && window.userPermissions.canEdit === false) {
            Swal.fire({
                icon: 'warning',
                title: 'Akses Ditolak',
                text: 'Anda tidak memiliki hak akses (Role & Permission) untuk mengubah proposal tender.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        let id = $(this).data('id');
        if (!id) return;

        $.get(`/tender/${id}/detail-json`, function (res) {
            if (res.can_edit === false) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Ditolak',
                    text: 'Anda tidak memiliki hak akses untuk mengubah proposal tender ini.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            if (!res.tender) return;
            let t = res.tender;

            $('#edit_tender_id').val(t.id);
            $('#edit_nama_tender').val(t.nama_tender);
            $('#edit_kategori_instansi').val(t.kategori_instansi || 'Pemerintah / B2G');
            $('#edit_customer_id').val(t.customer_id);
            $('#edit_customer_name').val(t.customer ? (t.customer.nama_perusahaan || t.customer.nama_customer) : '');
            $('#edit_sales_id').val(t.sales_id);
            $('#edit_nomor_kontrak').val(t.nomor_kontrak || '');
            $('#edit_tanggal').val(t.tanggal ? t.tanggal.substring(0, 10) : '');
            $('#edit_deadline').val(t.deadline ? t.deadline.substring(0, 10) : '');
            $('#edit_estimasi_closing').val(t.estimasi_closing ? t.estimasi_closing.substring(0, 10) : '');
            $('#edit_priority').val(t.priority || 'Medium');
            $('#edit_status').val(t.status || 'Draft');
            $('#edit_currency').val(t.currency || 'IDR');
            
            let pagu = Number(t.nilai_estimasi || 0);
            $('#edit_nilai_estimasi').val(pagu);
            $('#edit_nilai_estimasi_mask').val(pagu > 0 ? pagu.toLocaleString('id-ID') : '');
            $('#edit_catatan').val(t.catatan || '');

            // Refresh character counters
            $('#edit_nama_tender, #edit_nomor_kontrak, #edit_nilai_estimasi_mask, #edit_catatan').trigger('input');

            // Populate Existing Items
            $('#tbody_edit_items').empty();
            tenderItemIdxEdit = 0;
            if (t.items && t.items.length > 0) {
                t.items.forEach(it => {
                    window.addTenderItemRowEdit({
                        barang_id: it.barang_id,
                        barang: it.barang,
                        photo_url: it.barang ? (it.barang.resolved_photo_url || window.resolveTenderProductPhotoUrl(it.barang)) : '',
                        photos: it.barang ? (it.barang.resolved_photos || window.extractProductPhotosList(it.barang)) : [],
                        kode: it.barang ? it.barang.kode_barang : '',
                        nama: it.nama_barang_custom ? it.nama_barang_custom : (it.barang ? it.barang.nama_barang : ''),
                        satuan: it.satuan || (it.barang?.satuan?.nama_satuan ?? 'Pcs'),
                        qty: Number(it.qty) || 1,
                        catatan_item: it.catatan_item || '',
                        tipe: it.barang ? (it.barang.tipe_item || 'Stok') : 'Stok'
                    });
                });
            } else {
                window.addTenderItemRowEdit();
            }
            renumberTenderRows('edit');

            // Populate Existing Documents
            let docsHtml = '';
            if (t.documents && t.documents.length > 0) {
                t.documents.forEach(doc => {
                    docsHtml += `
                        <div class="existing-doc-card d-flex align-items-center justify-content-between p-2.5 mb-1.5 border rounded-3 bg-body small shadow-xs">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                <div>
                                    <div class="fw-bold">${safeHtml(doc.nama_file)}</div>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.7rem;">${safeHtml(doc.jenis_dokumen)}</span>
                                </div>
                            </div>
                            <a href="/${doc.path || doc.file_path}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-download me-1"></i> Unduh
                            </a>
                        </div>
                    `;
                });
            } else {
                docsHtml = '<div class="text-muted small fst-italic">Belum ada lampiran berkas yang diunggah.</div>';
            }
            $('#edit_existing_documents_container').html(docsHtml);
            $('#tbody_edit_dokumen').empty();
            renumberDocRows('edit');

            $('#edit-umum-tab').tab('show');
            $('#modalEditTender').modal('show');
        });
    });

    // Form Edit Submit
    $(document).on('submit', '#formEditTender', function (e) {
        e.preventDefault();

        let form = this;
        let tenderId = $('#edit_tender_id').val();
        if (!tenderId) return;

        // Security / Character limit checks
        let namaTender = ($('#edit_nama_tender').val() || '').trim();
        let nomorKontrak = ($('#edit_nomor_kontrak').val() || '').trim();
        let nilaiMask = ($('#edit_nilai_estimasi_mask').val() || '').trim();
        let catatan = ($('#edit_catatan').val() || '').trim();

        if (namaTender.length > 50) {
            Swal.fire('Peringatan Karakter', 'Nama Proyek / Tender maksimal 50 karakter!', 'warning');
            $('#edit-umum-tab').tab('show');
            $('#edit_nama_tender').focus();
            return;
        }
        if (nomorKontrak.length > 40) {
            Swal.fire('Peringatan Karakter', 'Nomor Tender / Registrasi Proyek maksimal 40 karakter!', 'warning');
            $('#edit-umum-tab').tab('show');
            $('#edit_nomor_kontrak').focus();
            return;
        }
        if (nilaiMask.length > 20) {
            Swal.fire('Peringatan Karakter', 'Nilai Pagu Anggaran maksimal 20 karakter / digit!', 'warning');
            $('#edit-umum-tab').tab('show');
            $('#edit_nilai_estimasi_mask').focus();
            return;
        }
        if (catatan.length > 50) {
            Swal.fire('Peringatan Karakter', 'Catatan Tender maksimal 50 karakter!', 'warning');
            $('#edit-umum-tab').tab('show');
            $('#edit_catatan').focus();
            return;
        }

        // Mandatory Item Checks
        let itemRows = $('#tbody_edit_items tr.item-row');
        if (itemRows.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Daftar Barang Wajib Diisi',
                text: 'Silakan tambahkan minimal 1 barang / jasa kebutuhan lelang!',
                confirmButtonColor: '#0d6efd'
            });
            $('#edit-barang-tab').tab('show');
            return;
        }

        let hasEmptyBarang = false;
        let hasEmptyQty = false;
        itemRows.each(function() {
            let bId = $(this).find('.item-barang-id').val();
            let bName = $(this).find('.item-barang-name').val();
            let qty = parseFloat($(this).find('.item-qty-input').val()) || 0;
            if (!bId && !bName) {
                hasEmptyBarang = true;
            }
            if (qty <= 0) {
                hasEmptyQty = true;
            }
        });

        if (hasEmptyBarang) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Barang Katalog',
                text: 'Ada baris barang yang belum dipilih produknya. Silakan pilih barang dari katalog!',
                confirmButtonColor: '#0d6efd'
            });
            $('#edit-barang-tab').tab('show');
            return;
        }

        if (hasEmptyQty) {
            Swal.fire({
                icon: 'warning',
                title: 'Jumlah Barang Tidak Valid',
                text: 'Jumlah / Kuantitas barang pada setiap baris harus lebih dari 0!',
                confirmButtonColor: '#0d6efd'
            });
            $('#edit-barang-tab').tab('show');
            return;
        }

        let existingDocCount = $('#edit_existing_documents_container .existing-doc-card').length;
        let newDocCount = $('#tbody_edit_dokumen tr').length;
        if ((existingDocCount + newDocCount) > 30) {
            Swal.fire('Peringatan Berkas', `Total lampiran berkas dokumen maksimal 30 berkas! (Sudah tersimpan: ${existingDocCount}, Berkas baru: ${newDocCount})`, 'warning');
            $('#edit-dokumen-tab').tab('show');
            return;
        }

        let formData = new FormData(form);
        let btnSubmit = $(form).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: `/tender/${tenderId}/update`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                $('#modalEditTender').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Tender Berhasil Diperbarui!',
                    text: res.message || 'Data tender berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                });
                if (typeof loadKanbanBoard === 'function') loadKanbanBoard();
                if ($.fn.DataTable.isDataTable('#tableTender')) {
                    $('#tableTender').DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                Swal.fire('Gagal Menyimpan', msg, 'error');
            },
            complete: function () {
                btnSubmit.prop('disabled', false).html('<i class="bi bi-check-circle-fill fs-6 me-1"></i> Perbarui Prospek Tender');
            }
        });
    });
</script>
@endpush
