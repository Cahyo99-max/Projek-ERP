<!-- MODAL QUICK ADD BARANG -->
<div class="modal fade" id="modalQuickAddBarang" tabindex="-1" aria-hidden="true" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formQuickAddBarang" class="modal-content border-0 shadow-lg rounded-4" enctype="multipart/form-data">
            @csrf
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-plus-circle fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tambah Master Barang Baru</h5>
                        <small class="text-muted">Buat barang baru langsung ke database tanpa keluar halaman</small>
                    </div>
                </div>
                <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="row g-3">
                    <!-- BARIS 1: IDENTITAS UTAMA (PALING ATAS) -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                            <span>Kode Barang <span class="text-muted fw-normal small">(Manual / Otomatis)</span></span>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size:0.68rem;">Maks. 20 Karakter</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-body text-muted"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="kode_barang" id="quick_kode_barang" class="form-control form-control-custom font-monospace" placeholder="Kosongkan untuk generate otomatis..." maxlength="20" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold mb-1">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" id="quick_nama_barang" class="form-control form-control-custom" placeholder="Contoh: Kertas A4 Sinar Dunia" required>
                    </div>

                    <!-- BARIS 2: TIPE & KATEGORI -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold mb-1">Tipe Item <span class="text-danger">*</span></label>
                        <select name="tipe_item" id="quick_tipe_item" class="form-select form-control-custom" required>
                            <option value="Stok">Stok</option>
                            <option value="Jasa">Jasa</option>
                            <option value="Aset">Aset</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold mb-1">Kategori Barang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="hidden" name="kategori_barang_id" id="quick_kategori_barang_id" required>
                            <input type="text" id="quick_kategori_barang_text" class="form-control form-control-custom bg-body btn-trigger-lookup" data-lookup="kategori" placeholder="Pilih Kategori..." readonly required style="cursor: pointer;">
                            <button class="btn btn-outline-primary px-3 btn-lookup-quick" type="button" data-lookup="kategori" title="Cari Kategori">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- BARIS 3: KLASIFIKASI & SATUAN -->
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold mb-1">Tipe Barang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="hidden" name="tipe_barang_id" id="quick_tipe_barang_id" required>
                            <input type="text" id="quick_tipe_barang_text" class="form-control form-control-custom bg-body btn-trigger-lookup" data-lookup="tipe" placeholder="Pilih Tipe..." readonly required style="cursor: pointer;">
                            <button class="btn btn-outline-primary px-3 btn-lookup-quick" type="button" data-lookup="tipe" title="Cari Tipe Barang">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold mb-1">Satuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="hidden" name="satuan_id" id="quick_satuan_id" required>
                            <input type="text" id="quick_satuan_text" class="form-control form-control-custom bg-body btn-trigger-lookup" data-lookup="satuan" placeholder="Pilih Satuan..." readonly required style="cursor: pointer;">
                            <button class="btn btn-outline-primary px-3 btn-lookup-quick" type="button" data-lookup="satuan" title="Cari Satuan">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold mb-1">Brand / Merk</label>
                        <div class="input-group">
                            <input type="hidden" name="brand_id" id="quick_brand_id">
                            <input type="text" id="quick_brand_text" class="form-control form-control-custom bg-body btn-trigger-lookup" data-lookup="brand" placeholder="Tidak Ada Brand" readonly style="cursor: pointer;">
                            <button class="btn btn-outline-secondary px-2.5 btn-clear-quick-lookup" type="button" data-lookup="brand" title="Hapus Brand">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button class="btn btn-outline-primary px-3 btn-lookup-quick" type="button" data-lookup="brand" title="Cari Brand / Merk">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- BARIS 4: VARIAN & STOK -->
                    <div class="col-12 col-md-4" id="quick_warna_wrapper">
                        <label class="form-label fw-semibold mb-1">Warna</label>
                        <div class="input-group">
                            <input type="hidden" name="warna_id" id="quick_warna_id">
                            <input type="text" id="quick_warna_text" class="form-control form-control-custom bg-body btn-trigger-lookup" data-lookup="warna" placeholder="Tidak Ada / Standar" readonly style="cursor: pointer;">
                            <button class="btn btn-outline-secondary px-2.5 btn-clear-quick-lookup" type="button" data-lookup="warna" title="Hapus Warna">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <button class="btn btn-outline-primary px-3 btn-lookup-quick" type="button" data-lookup="warna" title="Cari Warna">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 quick-physical-fields">
                        <label class="form-label fw-semibold mb-1">Ukuran (Size)</label>
                        <input type="text" name="ukuran" id="quick_ukuran" class="form-control form-control-custom" placeholder="Contoh: S, M, L, XL, 42">
                    </div>

                    <div class="col-12 col-md-4 quick-stock-fields">
                        <label class="form-label fw-semibold mb-1">Min. Buffer Stok <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="minimum_stok" id="quick_minimum_stok" class="form-control form-control-custom" value="0" min="0" required placeholder="0">
                    </div>

                    <!-- BARIS 5: BERAT & DIMENSI -->
                    <div class="col-12 col-md-4 quick-physical-fields">
                        <label class="form-label fw-semibold mb-1">Berat (kg/gr)</label>
                        <input type="number" step="0.001" name="berat" id="quick_berat" class="form-control form-control-custom" placeholder="0.000" min="0">
                    </div>

                    <div class="col-12 col-md-8 quick-physical-fields">
                        <label class="form-label fw-semibold mb-1">Dimensi P &times; L &times; T (cm)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="panjang" id="quick_panjang" class="form-control form-control-custom" placeholder="Panjang (P)" min="0">
                            <span class="input-group-text px-2 text-muted">&times;</span>
                            <input type="number" step="0.01" name="lebar" id="quick_lebar" class="form-control form-control-custom" placeholder="Lebar (L)" min="0">
                            <span class="input-group-text px-2 text-muted">&times;</span>
                            <input type="number" step="0.01" name="tinggi" id="quick_tinggi" class="form-control form-control-custom" placeholder="Tinggi (T)" min="0">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold mb-2">Foto Barang (Drag &amp; Drop / Copy-Paste / Browse)</label>
                        <div class="filepond-container mb-2">
                            <input type="file" id="quick_foto_input" class="filepond" multiple>
                        </div>
                        <!-- AI Remove BG Action (Dibawah FilePond) -->
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" id="btn_quick_remove_bg">
                                <i class="bi bi-magic me-1"></i>Hapus Background AI
                            </button>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.68rem;">1-4 Foto Sekaligus</span>
                        </div>
                        <div id="quick_bg_status" class="p-3 bg-primary bg-opacity-10 border border-primary-subtle rounded-3 mt-3" style="display:none;">
                            <div class="d-flex align-items-center justify-content-between text-primary small mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span id="quick_bg_status_text" class="fw-semibold">Menghapus background foto...</span>
                                </div>
                                <span id="quick_bg_counter_text" class="badge bg-primary text-white">0%</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="quick_bg_progress_bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Spesifikasi / Keterangan Barang</label>
                        <textarea name="spesifikasi" id="quick_spesifikasi" rows="2" class="form-control form-control-custom" placeholder="Tulis rincian spesifikasi barang jika ada..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-pill px-4" id="btn-save-quick-barang">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PREVIEW AI BG -->
<div class="modal fade" id="modalPreviewBG" tabindex="-1" aria-hidden="true" style="z-index: 1095;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-images text-primary me-2"></i>Preview Hasil Hapus Background</h5>
                <button type="button" class="btn-close rounded-circle" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 text-center">
                <div id="preview_bg_container" class="row justify-content-center g-3">
                    <!-- Preview images will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill px-4" id="btn_apply_bg_removal">Gunakan Hasil Ini</button>
            </div>
        </div>
    </div>
</div>

<!-- SIMPLE LIGHTBOX ZOOM GAMBAR -->
<div id="simpleLightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center; flex-direction:column;">
    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4 p-2 bg-dark rounded-circle" id="closeLightbox" style="opacity:1;"></button>
    <img id="lightboxImg" src="" class="img-fluid rounded shadow" style="max-width:90%; max-height:90%; object-fit:contain; background: repeating-conic-gradient(#80808033 0% 25%, transparent 0% 50%) 50% / 20px 20px;">
</div>

<!-- MODAL QUICK LOOKUP MASTER (Kategori, Tipe, Satuan, Warna, Brand) -->
<style>
    .quick-lookup-scroll-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
    }
    .quick-lookup-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .quick-lookup-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .quick-lookup-scroll-container::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.35);
        border-radius: 10px;
    }
    .quick-lookup-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.6);
    }
    [data-bs-theme="dark"] .quick-lookup-scroll-container::-webkit-scrollbar-thumb {
        background-color: rgba(255, 255, 255, 0.2);
    }
    [data-bs-theme="dark"] .quick-lookup-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(255, 255, 255, 0.35);
    }
    .quick-lookup-row {
        transition: background 0.15s ease;
    }
    .quick-lookup-row:hover {
        background-color: rgba(16, 185, 129, 0.06) !important;
    }
    [data-bs-theme="dark"] .quick-lookup-row:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
</style>

<div class="modal fade" id="modalQuickLookupMaster" tabindex="-1" aria-hidden="true" style="z-index: 1100;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom p-3.5 bg-body">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 rounded-3 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" id="quickLookupHeaderIcon" style="width: 40px; height: 40px;">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-body mb-0" id="quickLookupModalTitle">Pilih Data</h6>
                        <small class="text-muted" id="quickLookupModalSubtitle" style="font-size:0.75rem;">Pilih salah satu item dari daftar master</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5">
                <div class="position-relative mb-3">
                    <input type="text" id="quick_lookup_search_input" class="form-control rounded-pill ps-5 pe-5 py-2.5" placeholder="Ketik kata kunci untuk mencari..." autocomplete="off">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <button type="button" class="btn btn-link text-muted position-absolute top-50 end-0 translate-middle-y me-2 p-0 text-decoration-none d-none" id="btn_clear_lookup_search" style="font-size: 1.1rem;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
                
                <div class="border rounded-3 quick-lookup-scroll-container bg-body" style="max-height: 380px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-hover align-middle mb-0" id="tableQuickLookupList" style="cursor: pointer;">
                        <tbody id="tbody_quick_lookup_list">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                    <div id="quick_lookup_empty_state" class="text-center py-4 text-muted d-none">
                        <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                        <span class="small">Tidak ada data yang cocok dengan pencarian</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-2.5 bg-body-tertiary d-flex justify-content-between align-items-center">
                <span class="text-muted small ms-2 font-monospace" id="quick_lookup_count_info">0 data</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none" id="btn_quick_lookup_reset_val">
                        <i class="bi bi-x-circle me-1"></i> Kosongkan
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module">
    console.log("Mengunduh library Hapus Background...");
    try {
        const module = await import('https://esm.sh/@imgly/background-removal@1.4.5?bundle');
        window._imglyRemoveBg = module.removeBackground || module.default;
        window._imglyReady = true;
        console.log("Library Hapus Background berhasil dimuat!");
        document.dispatchEvent(new CustomEvent('imglyReady'));
    } catch (e) {
        console.error("Gagal memuat library Hapus Background:", e);
        window._imglyError = e;
        window._imglyErrorMessage = e.message || String(e);
    }
</script>
<script>
    $(document).ready(function () {
        // ===== REGISTER FILEPOND PLUGINS =====
        FilePond.registerPlugin(
            FilePondPluginFileValidateType,
            FilePondPluginImagePreview,
            FilePondPluginFileValidateSize
        );

        // ===== HELPER: RESIZE GAMBAR VIA CANVAS =====
        function resizeImage(blob, maxWidthHeight = 1200) {
            return new Promise((resolve) => {
                const url = URL.createObjectURL(blob instanceof Blob ? blob : new Blob([blob]));
                const img = new Image();
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    let w = img.width, h = img.height;
                    if (w > maxWidthHeight || h > maxWidthHeight) {
                        if (w > h) { h = Math.round(h * maxWidthHeight / w); w = maxWidthHeight; }
                        else       { w = Math.round(w * maxWidthHeight / h); h = maxWidthHeight; }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    canvas.toBlob(resolve, 'image/png');
                };
                img.src = url;
            });
        }

        // ===== HELPER: KOMPRES GAMBAR VIA CANVAS (JPEG) =====
        function compressImage(file, maxWidthHeight = 1200, quality = 0.65) {
            return new Promise((resolve, reject) => {
                // Accept both File and Blob objects
                const fileObj = file instanceof File ? file : new File([file], 'image.jpg', { type: file.type || 'image/jpeg' });
                if (!fileObj.type.startsWith('image/')) { resolve(fileObj); return; }
                
                // Safe filename: use original name if available, else generate one
                const safeName = (fileObj.name && fileObj.name !== 'undefined')
                    ? fileObj.name.replace(/\.[^.]+$/, '') + '.jpg'
                    : 'foto_' + Date.now() + '.jpg';

                const reader = new FileReader();
                reader.onerror = (err) => reject(new Error('FileReader error: ' + err));
                reader.onload = (e) => {
                    const img = new Image();
                    img.onerror = () => reject(new Error('Gagal memuat gambar untuk kompresi.'));
                    img.onload = () => {
                        let w = img.width, h = img.height;
                        if (w > maxWidthHeight || h > maxWidthHeight) {
                            if (w > h) { h = Math.round(h * maxWidthHeight / w); w = maxWidthHeight; }
                            else       { w = Math.round(w * maxWidthHeight / h); h = maxWidthHeight; }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w; canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        // Fill pure white background so transparent PNGs/cutouts don't turn black in JPEG
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, w, h);
                        ctx.drawImage(img, 0, 0, w, h);
                        canvas.toBlob((blob) => {
                            if (!blob) { reject(new Error('Canvas toBlob gagal menghasilkan file.')); return; }
                            resolve(new File([blob], safeName, { type: 'image/jpeg' }));
                        }, 'image/jpeg', quality);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(fileObj);
            });
        }

        // ===== INITIALIZE FILEPOND QUICK ADD =====
        const pondQuick = FilePond.create(document.querySelector('#quick_foto_input'), {
            allowMultiple: true,
            maxFiles: 4,
            maxTotalFileSize: '10MB',
            acceptedFileTypes: ['image/*'],
            labelIdle: `<i class="bi bi-cloud-upload-fill text-primary" style="font-size: 2.2rem; display: block; margin-bottom: 8px; opacity: 0.85;"></i>Seret &amp; lepas foto atau <span class="filepond--label-action">Browse</span><br><span style="font-size: 0.8rem; color: #64748b;">Maks. 4 foto &bull; Otomatis dikompresi</span>`,
            labelMaxFileSizeExceeded: 'File terlalu besar',
            labelMaxTotalFileSizeExceeded: `Maksimal hanya 4 foto`,
            allowPaste: true,
            allowDrop: true,
        });

        // ===== AI BACKGROUND REMOVAL LOGIC =====
        let temporaryAiBgFiles = []; // To hold the processed blobs temporarily

        $('#btn_quick_remove_bg').on('click', async function () {
            const files = pondQuick.getFiles();
            if (files.length === 0) {
                Swal.fire({ title: 'Kosong!', text: 'Silakan masukkan minimal 1 foto terlebih dahulu.', icon: 'warning' });
                return;
            }

            if (!window._imglyReady || !window._imglyRemoveBg) {
                if (window._imglyError) {
                    Swal.fire({ title: 'Error AI', text: window._imglyErrorMessage || window._imglyError, icon: 'error' });
                } else {
                    Swal.fire({ title: 'Menyiapkan AI', text: 'AI Hapus Background sedang dimuat (~30MB), harap tunggu beberapa detik lalu coba lagi.', icon: 'info' });
                }
                return;
            }

            $('#btn_quick_remove_bg').prop('disabled', true);
            $('#quick_bg_status').show();
            $('#quick_bg_progress_bar').css('width', '0%');
            $('#quick_bg_counter_text').text('0%');
            $('#preview_bg_container').empty();
            temporaryAiBgFiles = [];

            try {
                const total = files.length;
                for (let i = 0; i < total; i++) {
                    const currentStep = i + 1;
                    const startPercent = Math.round((i / total) * 100);
                    $('#quick_bg_progress_bar').css('width', startPercent + '%');
                    $('#quick_bg_counter_text').text(`${startPercent}%`);
                    $('#quick_bg_status_text').text(`[AI Studio] Memproses foto ${currentStep} dari ${total}...`);

                    const originalBlob = files[i].file;
                    // Pre-resize to 1000px HD: crisp product details with fast tensor math
                    const optimizedBlob = await resizeImage(originalBlob, 1024);
                    
                    const rawResultBlob = await window._imglyRemoveBg(optimizedBlob, {
                        publicPath: 'https://staticimgly.com/@imgly/background-removal-data/1.4.5/dist/',
                        model: 'medium', 
                        debug: false,
                        output: { format: 'image/png', quality: 1.0 }
                    });

                    // Refine edges: clear semi-transparent halo noise for clean cutouts
                    const resultBlob = (typeof refineCutoutEdges === 'function') ? await refineCutoutEdges(rawResultBlob) : rawResultBlob;
                    
                    const safeName = (originalBlob.name || 'foto').replace(/\.[^.]+$/, '') + '_nobg.png';
                    const newFile = new File([resultBlob], safeName, { type: 'image/png' });
                    temporaryAiBgFiles.push({ file: newFile });
                    
                    const endPercent = Math.round((currentStep / total) * 100);
                    $('#quick_bg_progress_bar').css('width', endPercent + '%');
                    $('#quick_bg_counter_text').text(`${endPercent}%`);
                    $('#quick_bg_status_text').text(`[AI Studio] Selesai foto ${currentStep} dari ${total}!`);

                    const html = `
                        <div class="col-12 col-md-6 mb-2">
                            <div class="card border border-light-subtle shadow-sm">
                                <div class="card-body p-2 d-flex">
                                    <div class="w-50 pe-2 border-end text-center d-flex flex-column align-items-center justify-content-center">
                                        <small class="d-block mb-1 text-muted fw-semibold">Asli (Foto ${currentStep})</small>
                                        <img src="${URL.createObjectURL(originalBlob)}" class="img-fluid rounded img-bg-preview" style="max-height:140px; object-fit:contain; cursor: zoom-in;" title="Klik untuk memperbesar">
                                    </div>
                                    <div class="w-50 ps-2 text-center d-flex flex-column align-items-center justify-content-center" style="background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAMUlEQVQ4T2NkYNgfQEhD/oMBQ1Q1jDAagPGBkQEBo//Hh4gDDEYDMDAgMIyGgYECAwAAd4kH0X42a78AAAAASUVORK5CYII=');">
                                        <small class="d-block mb-1 text-success fw-bold bg-white bg-opacity-90 px-2 py-0.5 rounded shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Hasil AI Foto ${currentStep}</small>
                                        <img src="${URL.createObjectURL(newFile)}" class="img-fluid rounded img-bg-preview" style="max-height:140px; object-fit:contain; cursor: zoom-in;" title="Klik untuk memperbesar">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#preview_bg_container').append(html);
                }

                $('#quick_bg_status').hide();
                $('#modalPreviewBG').modal('show');
                
                // Bind click event for zooming images using simple lightbox
                $('.img-bg-preview').on('click', function() {
                    $('#lightboxImg').attr('src', $(this).attr('src'));
                    $('#simpleLightbox').css('display', 'flex').hide().fadeIn('fast');
                });
                
                $('#closeLightbox').on('click', function() {
                    $('#simpleLightbox').fadeOut('fast');
                });
                
                $('#simpleLightbox').on('click', function(e) {
                    if (e.target === this) {
                        $(this).fadeOut('fast');
                    }
                });
            } catch (err) {
                $('#quick_bg_status').hide();
                Swal.fire({ title: 'Proses AI Gagal', text: err.message || err, icon: 'error' });
            } finally {
                $('#btn_quick_remove_bg').prop('disabled', false);
            }
        });

        $('#btn_apply_bg_removal').on('click', function () {
            pondQuick.removeFiles(); 
            temporaryAiBgFiles.forEach(item => {
                pondQuick.addFile(item.file);
            });
            $('#modalPreviewBG').modal('hide');
        });

        // ===== QUICK ADD BARANG COMPONENT LOGIC =====
        function toggleQuickFields() {
            let tipe = $('#quick_tipe_item').val();
            if (tipe === 'Stok') {
                $('#quick_warna_wrapper').show();
                $('.quick-physical-fields').show();
                $('.quick-stock-fields').show();
                $('.quick-stock-fields input[name="minimum_stok"]').prop('required', true);
            } else if (tipe === 'Aset') {
                $('#quick_warna_wrapper').show();
                $('.quick-physical-fields').hide();
                $('.quick-stock-fields').hide();
                $('.quick-stock-fields input[name="minimum_stok"]').prop('required', false);
            } else if (tipe === 'Jasa') {
                $('#quick_warna_wrapper').hide();
                $('.quick-physical-fields').hide();
                $('.quick-stock-fields').hide();
                $('.quick-stock-fields input[name="minimum_stok"]').prop('required', false);
            }
        }

        // ===== DYNAMIC QUICK LOOKUP MASTER DATA =====
        const quickLookupData = {
            kategori: {
                title: 'Pilih Kategori Barang',
                subtitle: 'Daftar master kategori barang operasional & proyek',
                icon: 'bi-grid-fill',
                targetHidden: '#quick_kategori_barang_id',
                targetText: '#quick_kategori_barang_text',
                allowClear: false,
                items: [
                    @foreach($kategoris as $k)
                    { id: {{ $k->id }}, name: @json($k->nama_kategori), code: @json($k->kode_kategori ?? '') },
                    @endforeach
                ]
            },
            tipe: {
                title: 'Pilih Tipe Barang',
                subtitle: 'Daftar master klasifikasi tipe barang',
                icon: 'bi-tags-fill',
                targetHidden: '#quick_tipe_barang_id',
                targetText: '#quick_tipe_barang_text',
                allowClear: false,
                items: [
                    @foreach($tipeBarangs as $t)
                    { id: {{ $t->id }}, name: @json($t->nama_tipe), code: @json($t->kode_tipe ?? '') },
                    @endforeach
                ]
            },
            satuan: {
                title: 'Pilih Satuan Pengukuran',
                subtitle: 'Daftar satuan unit barang (Pcs, Box, Kg, Mtr, dll)',
                icon: 'bi-rulers',
                targetHidden: '#quick_satuan_id',
                targetText: '#quick_satuan_text',
                allowClear: false,
                items: [
                    @foreach($satuans as $s)
                    { id: {{ $s->id }}, name: @json($s->nama_satuan), code: @json($s->simbol ?? '') },
                    @endforeach
                ]
            },
            warna: {
                title: 'Pilih Warna Barang',
                subtitle: 'Daftar variasi warna master barang',
                icon: 'bi-palette-fill',
                targetHidden: '#quick_warna_id',
                targetText: '#quick_warna_text',
                allowClear: true,
                defaultText: 'Tidak Ada / Standar',
                items: [
                    @foreach($warnas as $w)
                    { id: {{ $w->id }}, name: @json($w->nama_warna), hex: @json($w->kode_hex ?? '#cccccc') },
                    @endforeach
                ]
            },
            brand: {
                title: 'Pilih Brand / Merk',
                subtitle: 'Daftar produsen atau merk dagang barang',
                icon: 'bi-award-fill',
                targetHidden: '#quick_brand_id',
                targetText: '#quick_brand_text',
                allowClear: true,
                defaultText: 'Tidak Ada Brand',
                items: [
                    @foreach($brands as $b)
                    { id: {{ $b->id }}, name: @json($b->nama_brand), code: @json($b->kode_brand ?? '') },
                    @endforeach
                ]
            }
        };

        let currentActiveLookup = null;

        function openQuickLookupModal(type) {
            const config = quickLookupData[type];
            if (!config) return;

            currentActiveLookup = type;
            $('#quickLookupModalTitle').text(config.title);
            $('#quickLookupModalSubtitle').text(config.subtitle);
            $('#quickLookupHeaderIcon').html(`<i class="bi ${config.icon} fs-5"></i>`);
            $('#quick_lookup_search_input').val('');
            $('#btn_clear_lookup_search').addClass('d-none');

            if (config.allowClear) {
                $('#btn_quick_lookup_reset_val').removeClass('d-none');
            } else {
                $('#btn_quick_lookup_reset_val').addClass('d-none');
            }

            renderQuickLookupList(config.items, type);
            $('#modalQuickLookupMaster').modal('show');
            setTimeout(() => $('#quick_lookup_search_input').focus(), 300);
        }

        function renderQuickLookupList(items, type) {
            const tbody = $('#tbody_quick_lookup_list');
            tbody.empty();

            if (!items || items.length === 0) {
                $('#quick_lookup_empty_state').removeClass('d-none');
                $('#quick_lookup_count_info').text('0 data');
                return;
            }

            $('#quick_lookup_empty_state').addClass('d-none');
            $('#quick_lookup_count_info').text(`${items.length} data`);

            const currentVal = $(quickLookupData[type].targetHidden).val();

            items.forEach(item => {
                const isSelected = (currentVal && currentVal == item.id);
                let extraBadge = '';
                if (type === 'warna') {
                    extraBadge = `<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${item.hex || '#ccc'};margin-right:8px;border:1px solid rgba(0,0,0,0.15);vertical-align:middle;"></span>`;
                }
                let codeBadge = item.code ? `<span class="badge bg-secondary-subtle text-secondary font-monospace ms-1.5" style="font-size:0.7rem;">${escapeHtml(item.code)}</span>` : '';

                let activeClass = isSelected ? 'table-active border-start border-3 border-primary' : '';

                tbody.append(`
                    <tr class="quick-lookup-row ${activeClass}" data-id="${item.id}" data-name="${escapeHtml(item.name)}">
                        <td class="ps-3 py-2.5">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fw-semibold text-body d-flex align-items-center">
                                    ${extraBadge}
                                    <span>${escapeHtml(item.name)}</span>
                                    ${codeBadge}
                                </div>
                                <div>
                                    ${isSelected ? '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small me-2"><i class="bi bi-check-lg me-0.5"></i> Terpilih</span>' : ''}
                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 fw-bold btn-select-lookup-item">Pilih</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        // Search in lookup modal
        $('#quick_lookup_search_input').on('input', function () {
            const query = $(this).val().toLowerCase().trim();
            if (query.length > 0) {
                $('#btn_clear_lookup_search').removeClass('d-none');
            } else {
                $('#btn_clear_lookup_search').addClass('d-none');
            }

            if (!currentActiveLookup) return;
            const allItems = quickLookupData[currentActiveLookup].items;
            const filtered = allItems.filter(item => {
                return (item.name && item.name.toLowerCase().includes(query)) ||
                       (item.code && item.code.toLowerCase().includes(query));
            });

            renderQuickLookupList(filtered, currentActiveLookup);
        });

        $('#btn_clear_lookup_search').on('click', function () {
            $('#quick_lookup_search_input').val('').trigger('input').focus();
        });

        // Trigger open lookup modal
        $(document).on('click', '.btn-lookup-quick, .btn-trigger-lookup', function (e) {
            e.preventDefault();
            const type = $(this).data('lookup');
            openQuickLookupModal(type);
        });

        // Select item in lookup modal
        $(document).on('click', '.quick-lookup-row, .btn-select-lookup-item', function (e) {
            e.stopPropagation();
            const row = $(this).closest('tr');
            const id = row.data('id');
            const name = row.data('name');

            if (currentActiveLookup && quickLookupData[currentActiveLookup]) {
                const config = quickLookupData[currentActiveLookup];
                $(config.targetHidden).val(id).trigger('change');
                $(config.targetText).val(name).trigger('change');
            }

            $('#modalQuickLookupMaster').modal('hide');
        });

        // Clear button for optional fields in form
        $(document).on('click', '.btn-clear-quick-lookup', function (e) {
            e.preventDefault();
            const type = $(this).data('lookup');
            if (type && quickLookupData[type]) {
                const config = quickLookupData[type];
                $(config.targetHidden).val('').trigger('change');
                $(config.targetText).val(config.defaultText || '').trigger('change');
            }
        });

        // Reset button in lookup modal footer
        $('#btn_quick_lookup_reset_val').on('click', function () {
            if (currentActiveLookup && quickLookupData[currentActiveLookup]) {
                const config = quickLookupData[currentActiveLookup];
                $(config.targetHidden).val('').trigger('change');
                $(config.targetText).val(config.defaultText || '').trigger('change');
            }
            $('#modalQuickLookupMaster').modal('hide');
        });

        // Maintain scrolling when closing nested child modal
        $('#modalQuickLookupMaster, #modalPreviewBG').on('hidden.bs.modal', function () {
            if ($('#modalQuickAddBarang').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        $(document).on('change', '#quick_tipe_item', function () {
            toggleQuickFields();
        });

        $(document).on('click', '#btn-quick-add-barang', function () {
            // Set default tipe item to match currently searched type (if it is Stok/Jasa/Aset)
            let defaultType = (typeof currentSearchTipe !== 'undefined' ? currentSearchTipe : 'Stok') || 'Stok';
            if (defaultType === 'Bundle') defaultType = 'Stok';
            $('#formQuickAddBarang select[name="tipe_item"]').val(defaultType);
            
            toggleQuickFields();
            $('#modalQuickAddBarang').modal('show');
        });

        $('#modalQuickAddBarang').on('hidden.bs.modal', function () {
            $('#formQuickAddBarang')[0].reset();
            $('#quick_kategori_barang_id, #quick_tipe_barang_id, #quick_satuan_id, #quick_warna_id, #quick_brand_id').val('');
            $('#quick_kategori_barang_text, #quick_tipe_barang_text, #quick_satuan_text').val('');
            $('#quick_warna_text').val('Tidak Ada / Standar');
            $('#quick_brand_text').val('Tidak Ada Brand');
            $('#quick_ukuran').val('');
            if (typeof pondQuick !== 'undefined') {
                pondQuick.removeFiles();
            }
        });

        $('#formQuickAddBarang').on('submit', async function (e) {
            e.preventDefault();
            
            // Validasi panjang/karakter
            const kodeVal = $('#quick_kode_barang').val() || '';
            const namaVal = $('#quick_nama_barang').val() || '';
            const spesifikasiVal = $('#quick_spesifikasi').val() || '';
            const ukuranVal = $('#quick_ukuran').val() || '';
            const beratVal = $('#quick_berat').val() || '';
            const panjangVal = $('#quick_panjang').val() || '';
            const lebarVal = $('#quick_lebar').val() || '';
            const tinggiVal = $('#quick_tinggi').val() || '';
            const minStokVal = $('#quick_minimum_stok').val() || '';

            if (kodeVal.length > 20) {
                Swal.fire({ title: 'Peringatan!', text: 'Kode Barang tidak boleh melebihi 20 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (namaVal.length > 50) {
                Swal.fire({ title: 'Peringatan!', text: 'Nama Barang tidak boleh melebihi 50 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (spesifikasiVal.length > 20) {
                Swal.fire({ title: 'Peringatan!', text: 'Spesifikasi/Deskripsi tidak boleh melebihi 20 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (ukuranVal.length > 50) {
                Swal.fire({ title: 'Peringatan!', text: 'Ukuran (Size) tidak boleh melebihi 50 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (beratVal.length > 5) {
                Swal.fire({ title: 'Peringatan!', text: 'Berat tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (panjangVal.length > 5) {
                Swal.fire({ title: 'Peringatan!', text: 'Panjang tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (lebarVal.length > 5) {
                Swal.fire({ title: 'Peringatan!', text: 'Lebar tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (tinggiVal.length > 5) {
                Swal.fire({ title: 'Peringatan!', text: 'Tinggi tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            if (minStokVal.length > 5) {
                Swal.fire({ title: 'Peringatan!', text: 'Min. Buffer Stok tidak boleh melebihi 5 karakter.', icon: 'warning', confirmButtonColor: '#eab308' });
                return;
            }
            
            const filesToUpload = typeof pondQuick !== 'undefined' ? pondQuick.getFiles() : [];
            if (filesToUpload.length > 4) {
                Swal.fire({ title: 'Batas Foto!', text: `Maksimal hanya 4 foto yang bisa diupload.`, icon: 'warning', confirmButtonText: 'OK' });
                return;
            }

            let formData = new FormData(this);
            formData.append('status', 'Active');

            let saveBtn = $('#btn-save-quick-barang');
            saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses Foto...');

            let fotoErrors = [];
            for (let i = 0; i < filesToUpload.length; i++) {
                try {
                    const rawFile = filesToUpload[i].file;
                    console.log('[QuickAdd] Processing foto', i + 1, ':', rawFile?.name, rawFile?.type, rawFile?.size);
                    const processed = await compressImage(rawFile);
                    console.log('[QuickAdd] Compressed foto', i + 1, ':', processed.name, processed.size);
                    formData.append('fotos[]', processed, processed.name);
                } catch (err) {
                    console.error('[QuickAdd] Foto compress error:', err);
                    fotoErrors.push(`Foto ${i + 1}: ${err.message}`);
                }
            }

            if (fotoErrors.length > 0 && filesToUpload.length > 0 && formData.getAll('fotos[]').length === 0) {
                // All fotos failed — warn but still allow saving without fotos
                saveBtn.prop('disabled', false).text('Simpan Barang');
                Swal.fire({ title: 'Gagal Memproses Foto!', html: fotoErrors.join('<br>'), icon: 'error' });
                return;
            }

            saveBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            // Log FormData entries for debugging
            console.log('[QuickAdd] FormData fotos count:', formData.getAll('fotos[]').length);

            $.ajax({
                url: "{{ route('barang.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    saveBtn.prop('disabled', false).text('Simpan Barang');
                    $('#modalQuickAddBarang').modal('hide');
                    
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Master Barang baru berhasil ditambahkan.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    let b = res.barang;

                    // 1. Add new product to local activeBarang array
                    let newBarang = {
                        id: b.id,
                        kode: b.kode_barang,
                        nama: b.nama_barang,
                        ukuran: b.ukuran || '',
                        tipe_item: b.tipe_item,
                        satuan: b.satuan ? b.satuan.nama_satuan : '-',
                        foto: b.primary_foto ? `/storage/${b.primary_foto.foto_path}` : '',
                        semua_foto: b.fotos ? b.fotos.map(f => `/storage/${f.foto_path}`) : [],
                        warna_nama: b.warna ? b.warna.nama_warna : '',
                        warna_hex: b.warna ? b.warna.kode_hex : '',
                        spesifikasi: b.spesifikasi || ''
                    };
                    activeBarang.push(newBarang);

                    // 2. Add row to tablePopupCariBarang DataTable
                    let popupTable = $('#tablePopupCariBarang').DataTable();
                    let bg = 'background-color: rgba(100, 116, 139, 0.1); color: #64748b;';
                    if (newBarang.tipe_item === 'Stok') bg = 'background-color: rgba(16, 185, 129, 0.1); color: #10b981;';
                    else if (newBarang.tipe_item === 'Aset') bg = 'background-color: rgba(245, 158, 11, 0.1); color: #f59e0b;';
                    let tBadge = `<span class="badge small px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; ${bg}">${newBarang.tipe_item.toUpperCase()}</span>`;

                    let warnaBadge = '';
                    if (newBarang.warna_nama) {
                        warnaBadge = `
                            <span class="badge bg-light text-dark border px-2 py-0.5 rounded small d-inline-flex align-items-center" style="font-size:0.75rem;font-weight:600; margin-left:4px;">
                                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${newBarang.warna_hex || '#e2e8f0'};margin-right:6px;border:1px solid rgba(0,0,0,0.15);"></span>
                                ${escapeHtml(newBarang.warna_nama)}
                            </span>
                        `;
                    }

                    // Add new row to DataTable
                    let addedRowNode = popupTable.row.add([
                        `<div class="border rounded d-flex align-items-center justify-content-center bg-light img-thumbnail-preview" style="width: 40px; height: 40px; overflow: hidden; cursor: pointer;" title="Klik untuk memperbesar foto">
                            ${newBarang.foto ? `<img src="${newBarang.foto}" style="width:100%;height:100%;object-fit:cover;">` : `<i class="bi bi-image text-muted"></i>`}
                        </div>`,
                        escapeHtml(newBarang.kode),
                        `<span class="fw-semibold text-dark">${escapeHtml(newBarang.nama)}</span>${warnaBadge}`,
                        tBadge,
                        escapeHtml(newBarang.satuan),
                        `<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-pilih-barang-row">Pilih</button>`
                    ]).draw(false).node();

                    // Set custom data attributes on the newly created row element for pilih-barang functionality
                    $(addedRowNode).attr({
                        'data-id': newBarang.id,
                        'data-kode': newBarang.kode,
                        'data-nama': newBarang.nama,
                        'data-ukuran': newBarang.ukuran,
                        'data-satuan': newBarang.satuan,
                        'data-tipe': newBarang.tipe_item,
                        'data-foto': newBarang.foto,
                        'data-semua-foto': newBarang.semua_foto.join(','),
                        'data-warna-nama': newBarang.warna_nama,
                        'data-warna-hex': newBarang.warna_hex,
                        'data-spesifikasi': newBarang.spesifikasi
                    });
                },
                error: function (err) {
                    saveBtn.prop('disabled', false).text('Simpan Barang');
                    console.error('[QuickAdd] barang.store error:', err.status, err.responseJSON);
                    let errorMsg = 'Gagal menyimpan barang baru.';
                    if (err.status === 419) {
                        errorMsg = 'Sesi telah habis (CSRF). Silakan refresh halaman dan coba lagi.';
                    } else if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        errorMsg = '<b>Data tidak valid:</b><br>' + 
                            Object.entries(err.responseJSON.errors).map(([k, v]) => `• ${v[0]}`).join('<br>');
                    } else if (err.responseJSON && err.responseJSON.message) {
                        errorMsg = err.responseJSON.message;
                    } else if (err.status === 0) {
                        errorMsg = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                    }
                    Swal.fire({ title: 'Gagal Menyimpan Barang!', html: errorMsg, icon: 'error' });
                }
            });
        });

        // Real-time length validation for Quick Add
        const fieldsQuick = [
            { id: '#quick_kode_barang', max: 20, name: 'Kode Barang' },
            { id: '#quick_nama_barang', max: 50, name: 'Nama Barang' },
            { id: '#quick_spesifikasi', max: 255, name: 'Deskripsi / Spesifikasi' },
            { id: '#quick_ukuran', max: 50, name: 'Ukuran (Size)' },
            { id: '#quick_berat', max: 5, name: 'Berat' },
            { id: '#quick_panjang', max: 5, name: 'Panjang' },
            { id: '#quick_lebar', max: 5, name: 'Lebar' },
            { id: '#quick_tinggi', max: 5, name: 'Tinggi' },
            { id: '#quick_minimum_stok', max: 5, name: 'Min. Buffer Stok' }
        ];

        fieldsQuick.forEach(field => {
            $(field.id).on('input', function () {
                if ($(this).val().length > field.max) {
                    $(this).val($(this).val().substring(0, field.max));
                    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    Swal.fire({
                        title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Batas Karakter Terlampaui!</span>',
                        html: `<div style="font-family: 'Inter', sans-serif; font-size: 0.85rem; color: ${isDark ? '#cbd5e1' : '#64748b'}; line-height: 1.5; margin-top: 5px;">Input <b>${field.name}</b> dibatasi maksimal <b>${field.max} karakter</b>.</div>`,
                        icon: 'warning',
                        iconColor: '#f59e0b',
                        background: isDark ? '#1e293b' : '#ffffff',
                        color: isDark ? '#f8fafc' : '#1e293b',
                        confirmButtonText: 'Saya Mengerti',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-warning rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
