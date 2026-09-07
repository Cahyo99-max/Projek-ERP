<x-layouts title="Data Barang / Produk">

    @push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Barang Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('css/barang_dashboard.css?v=' . time()) }}">
    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <style>
        /* Custom styles for gallery */
        .photo-gallery-item {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            cursor: move;
            transition: transform 0.2s ease, opacity 0.2s ease, border-color 0.2s ease;
            user-select: none;
        }
        .photo-gallery-item.dragging {
            opacity: 0.4;
            transform: scale(0.95);
        }
        .photo-gallery-item.drag-over {
            border-color: #3b82f6;
            transform: scale(1.05);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.35);
        }
        .photo-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-gallery-item .btn-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: space-around;
            padding: 2px 0;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .photo-gallery-item:hover .btn-actions {
            opacity: 1;
        }
        .photo-gallery-item .btn-action-icon {
            color: #fff;
            font-size: 0.8rem;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }
        .photo-gallery-item .btn-action-icon:hover {
            color: #3b82f6;
        }
        .photo-gallery-item .btn-action-icon.text-danger:hover {
            color: #ef4444;
        }
        .photo-gallery-item .primary-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #e11d48;
            color: white;
            font-size: 0.55rem;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: bold;
            box-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }
        .detail-photo-thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .detail-photo-thumb:hover {
            transform: scale(1.05);
        }
        
        /* Custom styles for FilePond to make it premium */
        .filepond--root {
            font-family: inherit;
            margin-bottom: 0.5rem !important;
            min-height: 90px !important;
        }
        .filepond--credits {
            display: none !important;
        }
        .filepond-container {
            position: relative;
            z-index: 1;
            margin-bottom: 0.5rem !important;
            clear: both;
        }
        .filepond--panel-root {
            border: 2px dashed #cbd5e1;
            background-color: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .filepond--root:hover .filepond--panel-root {
            border-color: #3b82f6;
            background-color: #f0f9ff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        }
        .filepond--drop-label {
            color: #475569;
            cursor: pointer;
            padding: 12px 10px !important;
            min-height: 80px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .filepond--drop-label label {
            line-height: 1.4;
            font-size: 0.88rem;
            margin-bottom: 0;
            cursor: pointer;
        }
        .filepond--label-action {
            color: #3b82f6;
            text-decoration: underline;
            font-weight: 600;
        }
        .filepond--label-action:hover {
            color: #1d4ed8;
        }

        html[data-bs-theme="dark"] .filepond--panel-root {
            background-color: #0b132b !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        html[data-bs-theme="dark"] .filepond--drop-label {
            color: #94a3b8 !important;
        }
    </style>
    @endpush

    {{-- ===== SUMMARY CARDS ===== --}}
    <div class="container-fluid py-2 py-md-3 px-1 px-sm-2 px-md-4">
        <div class="brg-summary-row">
            {{-- Total Barang --}}
            <div class="brg-summary-card brg-card-total">
                <div class="brg-card-top">
                    <span class="brg-card-label">Total Semua Item</span>
                    <div class="brg-card-icon">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
                <div class="brg-card-number" id="sum-count-all">
                    <div class="spinner-border spinner-border-sm opacity-50" role="status" style="width:1rem;height:1rem;border-width:0.15em;"></div>
                </div>
                <div class="brg-card-sub" id="sum-count-active"><span class="placeholder col-6 placeholder-xs"></span></div>
            </div>

            {{-- Barang Stok --}}
            <div class="brg-summary-card brg-card-stok">
                <div class="brg-card-top">
                    <span class="brg-card-label">Barang Stok</span>
                    <div class="brg-card-icon">
                        <i class="bi bi-archive-fill"></i>
                    </div>
                </div>
                <div class="brg-card-number" id="sum-count-stok">
                    <div class="spinner-border spinner-border-sm opacity-50" role="status" style="width:1rem;height:1rem;border-width:0.15em;"></div>
                </div>
                <div class="brg-card-sub">Inventory gudang</div>
            </div>

            {{-- Jasa / Biaya --}}
            <div class="brg-summary-card brg-card-jasa">
                <div class="brg-card-top">
                    <span class="brg-card-label">Jasa / Biaya</span>
                    <div class="brg-card-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                </div>
                <div class="brg-card-number" id="sum-count-jasa">
                    <div class="spinner-border spinner-border-sm opacity-50" role="status" style="width:1rem;height:1rem;border-width:0.15em;"></div>
                </div>
                <div class="brg-card-sub">Layanan & biaya</div>
            </div>

            {{-- Aset Tetap --}}
            <div class="brg-summary-card brg-card-aset">
                <div class="brg-card-top">
                    <span class="brg-card-label">Aset Tetap</span>
                    <div class="brg-card-icon">
                        <i class="bi bi-building-fill"></i>
                    </div>
                </div>
                <div class="brg-card-number" id="sum-count-aset">
                    <div class="spinner-border spinner-border-sm opacity-50" role="status" style="width:1rem;height:1rem;border-width:0.15em;"></div>
                </div>
                <div class="brg-card-sub">Peralatan & kendaraan</div>
            </div>
        </div>
    </div>


    {{-- ===== TAB FILTER + TABLE ===== --}}
    <x-table_card tableId="tableBarang" title="Data Barang / Produk" icon="bi-box-seam"
        btnTarget="{{ auth()->user()->can('tambah-barang') ? '#modalTambahBarang' : '' }}"
        btnText="Tambah Barang">

        <x-slot name="extraActions">
            @can('lihat-barang')
                <a href="{{ route('barang.export') }}" class="btn btn-sm btn-outline-success rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold no-loader btn-export-excel" style="font-size: 0.8rem; border-color: #10b981 !important; color: #10b981 !important; background: transparent;">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    <span>Export Excel</span>
                </a>
            @endcan
            @can('tambah-barang')
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-flex align-items-center gap-2 px-3 py-1.5 fw-semibold" style="font-size: 0.8rem; border-color: #3b82f6 !important; color: #3b82f6 !important; background: transparent;" data-bs-toggle="modal" data-bs-target="#modalImportBarang">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    <span>Import Excel</span>
                </button>
            @endcan
        </x-slot>

        {{-- Tab Filter Pills (injected before table via slot) --}}
        <x-slot name="thead">
            <th width="4%" class="text-center py-3">No</th>
            <th class="py-3" width="30%">Produk</th>
            <th class="py-3" width="14%">Kode</th>
            <th class="py-3" width="16%">Kategori & Brand</th>
            <th class="py-3" width="12%">Satuan & Tipe</th>
            <th class="py-3 text-center" width="10%">Status</th>
            <th width="14%" class="text-center py-3">Aksi</th>
        </x-slot>

        <!-- Server-side loaded data will be injected here via DataTables AJAX -->

    </x-table_card>


    {{-- ===== MODALS PARTIALS ===== --}}
    @include('data_master.barang.detail')
    @include('data_master.barang.qr')
    @include('data_master.barang.tambah')
    @include('data_master.barang.edit')
    @include('data_master.barang.import')
    @include('data_master.barang.scanner')
    @include('data_master.barang._modal_lookup_kategori')

    <!-- MODAL PREVIEW AI BG (SHARED FOR TAMBAH & EDIT) -->
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

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- FilePond JS & Plugins -->
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <!-- Html5Qrcode Library for Camera Scan -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <!-- QRious Library for local QR Code generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <!-- Force ONNX Runtime to single-thread to avoid SharedArrayBuffer issues on non-cross-origin isolated sites -->
    <script>
        window.ort = {
            env: {
                wasm: {
                    numThreads: 1,
                    proxy: false
                }
            }
        };
    </script>
    <!-- State-of-the-art BRIA RMBG-1.4 AI Background Removal (Commercial E-Commerce Product AI Engine) -->
    <script type="module">
        import { pipeline, env, RawImage } from 'https://cdn.jsdelivr.net/npm/@xenova/transformers@2.17.2';
        
        env.allowLocalModels = false;
        env.useBrowserCache = true;
        
        let segmentator = null;
        let segmentatorLoading = null;

        async function initRmbgModel(onProgress) {
            if (segmentator) return segmentator;
            if (segmentatorLoading) return await segmentatorLoading;
            
            segmentatorLoading = pipeline('image-segmentation', 'Xenova/bria-rmbg-1.4', {
                progress_callback: (p) => {
                    if (typeof onProgress === 'function' && p.status === 'progress') {
                        onProgress(p);
                    }
                }
            });
            segmentator = await segmentatorLoading;
            segmentatorLoading = null;
            return segmentator;
        }

        window._rmbgAiReady = true;

        window._executeStudioAiRemoveBg = async function (blob, onProgressCallback) {
            try {
                const seg = await initRmbgModel(onProgressCallback);
                const url = URL.createObjectURL(blob);
                try {
                    const rawImg = await RawImage.fromURL(url);
                    const output = await seg(rawImg);
                    
                    const mask = Array.isArray(output) ? (output[0].mask || output[0]) : (output.mask || output);
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = rawImg.width;
                    canvas.height = rawImg.height;
                    const ctx = canvas.getContext('2d');
                    
                    // Draw original high-res image
                    ctx.drawImage(rawImg.toCanvas(), 0, 0);
                    
                    // Apply BRIA AI alpha mask for entire object (fabrics, metals, edges)
                    ctx.globalCompositeOperation = 'destination-in';
                    const maskCanvas = mask.toCanvas ? mask.toCanvas() : mask;
                    ctx.drawImage(maskCanvas, 0, 0);
                    
                    return new Promise((resolve, reject) => {
                        canvas.toBlob((resultBlob) => {
                            if (resultBlob) resolve(resultBlob);
                            else reject(new Error('Canvas export failed'));
                        }, 'image/png');
                    });
                } finally {
                    URL.revokeObjectURL(url);
                }
            } catch (err) {
                console.warn('BRIA RMBG error, attempting fallback:', err);
                if (window._imglyRemoveBg) {
                    return await window._imglyRemoveBg(blob, {
                        publicPath: 'https://staticimgly.com/@imgly/background-removal-data/1.4.5/dist/',
                        model: 'medium',
                        output: { format: 'image/png', quality: 1.0 }
                    });
                }
                throw err;
            }
        };

        // Preload / pre-warm model on idle
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                initRmbgModel().catch(() => {});
            });
        }
    </script>
    <!-- Fallback imgly background removal -->
    <script type="module">
        try {
            const module = await import('https://esm.sh/@imgly/background-removal@1.4.5?bundle');
            window._imglyRemoveBg = module.removeBackground || module.default;
            window._imglyReady = true;
        } catch (e) {
            console.warn("Fallback imgly load info:", e);
        }
    </script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        // Silence default browser alert popups on DataTables errors (like network drop)
        $.fn.dataTable.ext.errMode = 'none';

        // Hide loading spinner if DataTables request fails
        $(document).on('error.dt', function (e, settings, techNote, message) {
            console.warn('DataTables error captured:', message);
            $('.dataTables_processing').hide();
        });

        // ===== XSS PROTECTION HELPER =====
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return $('<div>').text(str).html();
        }

        $(document).ready(function () {
            // ===== EXPORT EXCEL ALERT =====
            $(document).on('click', '.btn-export-excel', function (e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                
                const countText = $('#sum-count-all').text() || '0';
                const totalCount = parseInt(countText.replace(/[^0-9]/g, '')) || 0;
                
                if (totalCount > 50000) {
                    Swal.fire({
                        title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem; color: #ef4444;">Data Terlalu Besar!</span>',
                        html: `<div style="font-family: 'Inter', sans-serif; font-size: 0.85rem; color: ${isDark ? '#cbd5e1' : '#64748b'}; line-height: 1.5; margin-top: 5px;">` +
                              `Jumlah data Anda sangat besar (<b>${totalCount.toLocaleString()} data</b>).<br>` +
                              `Ekspor langsung ke Excel (.xlsx) dinonaktifkan sementara untuk menjaga stabilitas server agar tidak overload/crash.<br><br>` +
                              `Sistem secara otomatis akan mengunduh data dalam format <b>CSV (.csv)</b> yang instan dan aman.` +
                              `</div>`,
                        icon: 'warning',
                        iconColor: '#ef4444',
                        background: isDark ? '#1e293b' : '#ffffff',
                        color: isDark ? '#f8fafc' : '#1e293b',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Unduh CSV',
                        cancelButtonText: 'Batal',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-danger rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7 me-2',
                            cancelButton: 'btn btn-secondary rounded-pill px-4 py-2 shadow-sm fs-7'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url + '?format=csv';
                        }
                    });
                } else {
                    Swal.fire({
                        title: '<span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 1.2rem;">Ekspor Berhasil!</span>',
                        html: '<div style="font-family: \'Inter\', sans-serif; font-size: 0.85rem; color: ' + (isDark ? '#cbd5e1' : '#64748b') + '; line-height: 1.5; margin-top: 5px;">Data sedang diproses. File Excel Anda akan terunduh secara otomatis secara background.</div>',
                        icon: 'success',
                        iconColor: '#10b981',
                        background: isDark ? '#1e293b' : '#ffffff',
                        color: isDark ? '#f8fafc' : '#1e293b',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-success rounded-pill px-4 fw-semibold py-2 shadow-sm fs-7'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url + '?format=xlsx';
                        }
                    });
                }
            });

            // ===== LAZY LOAD SUMMARY COUNTS =====
            $.get("{{ route('barang.summary-counts') }}", function (data) {
                // Dashboard summary cards
                $('#sum-count-all').text(data.countAll);
                $('#sum-count-active').text(`${data.countActive} aktif dari total`);
                $('#sum-count-stok').text(data.countStok);
                $('#sum-count-jasa').text(data.countJasa);
                $('#sum-count-aset').text(data.countAset);

                // Tab filter pills counts
                $('#tab-count-all').text(data.countAll);
                $('#tab-count-stok').text(data.countStok);
                $('#tab-count-jasa').text(data.countJasa);
                $('#tab-count-aset').text(data.countAset);
            });

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
                return new Promise((resolve) => {
                    if (!file.type.startsWith('image/')) { resolve(file); return; }
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
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
                                resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                            }, 'image/jpeg', quality);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            // ===== HELPER: CANVASH ALPHA DEFRINGE & EDGE REFINEMENT =====
            function refineCutoutEdges(blob) {
                return new Promise((resolve) => {
                    const img = new Image();
                    const url = URL.createObjectURL(blob);
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const d = imgData.data;
                        // Clean only near-zero background residual noise (< 5) while preserving full gradient & dark object alpha
                        for (let i = 3; i < d.length; i += 4) {
                            if (d[i] < 5) {
                                d[i] = 0;
                            }
                        }
                        ctx.putImageData(imgData, 0, 0);
                        canvas.toBlob((b) => resolve(b || blob), 'image/png');
                    };
                    img.onerror = () => resolve(blob);
                    img.src = url;
                });
            }

            // ===== HELPER: PROSES GAMBAR (REMOVE BG + COMPRESS) =====
            async function processImage(file, removeBg = false, statusEl = null, statusTextEl = null, index = 0, total = 1) {
                if (removeBg) {
                    try {
                        if (statusEl) {
                            $(statusEl).show();
                            $(statusTextEl).text(`[AI Studio] Menghapus background foto ${index + 1}/${total}...`);
                        }
                        
                        const resultBlob = await window._executeStudioAiRemoveBg(file, (p) => {
                            if (p.total && statusTextEl) {
                                const dlPercent = Math.round((p.loaded / p.total) * 100);
                                $(statusTextEl).text(`[AI Studio] Mengunduh neural weights (${dlPercent}%)...`);
                            }
                        });
                        
                        return new File([resultBlob], file.name.replace(/\.[^.]+$/, '') + '_nobg.png', { type: 'image/png' });
                    } catch (err) {
                        console.error('Background removal failed:', err);
                        throw new Error(`Gagal memproses hapus background pada foto ke-${index + 1}. Detail: ${err.message || err}. Silakan matikan opsi ini jika ingin menyimpan foto asli.`);
                    }
                }
                return await compressImage(file);
            }

            const MAX_FOTO = 4;

            // Tracking photo change state
            let photoChanged = false;

            $('#modalEditBarang').on('hidden.bs.modal', function () {
                if (photoChanged) {
                    photoChanged = false;
                    location.reload();
                }
            });

            // ===== OPTION DATA & STATE VARIABLES =====
            let activeFilter = 'Semua';
            let activeKategoriFilter = 'Semua';
            let tambahVarianIndex = 0;
            let tambahBundleIndex = 0;
            let editVarianIndex = 0;
            let editBundleIndex = 0;
            let tambahKonversiIndex = 0;
            let editKonversiIndex = 0;

            const satuanOptions = [
                @foreach($satuans as $sat)
                    { id: {{ $sat->id }}, name: {!! json_encode($sat->nama_satuan . ' (' . $sat->singkatan . ')') !!}, singkatan: {!! json_encode($sat->singkatan) !!} },
                @endforeach
            ];

            const warnaOptions = [
                @foreach($warnas as $warna)
                    { id: {{ $warna->id }}, name: {!! json_encode($warna->nama_warna) !!}, hex: {!! json_encode($warna->kode_hex) !!} },
                @endforeach
            ];

            const componentOptions = [
                @foreach($componentItems as $comp)
                    { id: {{ $comp->id }}, name: {!! json_encode($comp->nama_barang . ' (' . $comp->kode_barang . ')') !!} },
                @endforeach
            ];

            // ===== SHARED FUNCTIONS =====
            window.initSelect2Safely = function($elem, $modal, placeholder = '-- Pilih --') {
                if (!$.fn.select2 || !$elem || !$elem.length) return;
                $elem.each(function() {
                    let $this = $(this);
                    let parentModal = $modal || $this.closest('.modal');
                    let modalContent = parentModal.find('.modal-content');
                    let targetParent = modalContent.length ? modalContent : parentModal;

                    if ($this.data('select2')) {
                        $this.select2('destroy');
                    }
                    $this.select2({
                        theme: 'bootstrap-5',
                        dropdownParent: targetParent,
                        placeholder: placeholder,
                        width: '100%',
                        dropdownAutoWidth: false
                    });

                    $this.on('select2:open', function() {
                        let data = $(this).data('select2');
                        if (data && data.$dropdown) {
                            data.$dropdown.removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
                        }
                    });
                });
            };

            window.formatRupiahInput = function(el) {
                let val = el.value.replace(/[^0-9]/g, '');
                if (val === '') {
                    el.value = '';
                } else {
                    el.value = parseInt(val, 10).toLocaleString('id-ID');
                }
            };

            function generateWarnaSelectOptions(selectedId = null) {
                let html = '<option value="">-- Tanpa Warna --</option>';
                warnaOptions.forEach(opt => {
                    let sel = selectedId && parseInt(selectedId) === parseInt(opt.id) ? 'selected' : '';
                    html += `<option value="${opt.id}" ${sel}>${opt.name}</option>`;
                });
                return html;
            }

            function generateComponentSelectOptions(selectedId = null) {
                let html = '<option value="">-- Pilih Barang Komponen --</option>';
                componentOptions.forEach(opt => {
                    let sel = selectedId && parseInt(selectedId) === parseInt(opt.id) ? 'selected' : '';
                    html += `<option value="${opt.id}" ${sel}>${opt.name}</option>`;
                });
                return html;
            }

            function addVarianRow(prefix, variant = null) {
                let index = prefix === 'tambah' ? tambahVarianIndex++ : editVarianIndex++;
                let tbody = $(`#tbody_${prefix}_varian`);
                let varId = variant && variant.id ? variant.id : '';
                let varUkuran = variant && variant.ukuran ? variant.ukuran : '';
                let varWarnaId = variant && variant.warna_id ? variant.warna_id : '';
                let varKode = variant && variant.kode_barang ? variant.kode_barang : '';
                let varBarcode = variant && variant.barcode ? variant.barcode : '';
                let varStatus = variant && variant.status ? variant.status : 'Active';

                let initialHex = '#e2e8f0';
                if (varWarnaId) {
                    let opt = warnaOptions.find(o => parseInt(o.id) === parseInt(varWarnaId));
                    if (opt && opt.hex) {
                        initialHex = opt.hex;
                    }
                }

                let rowHtml = `
                    <tr class="variant-row">
                        <input type="hidden" name="variants[${index}][id]" value="${varId}">
                        <input type="hidden" name="variants[${index}][barcode]" value="${varBarcode}">
                        <td>
                            <input type="text" name="variants[${index}][ukuran]" class="form-control form-control-sm form-control-custom text-uppercase fw-semibold" value="${varUkuran}" required placeholder="Misal: M, L, Gen-1, Gen-2">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="warna-dot-indicator border shadow-sm" style="display:inline-block;width:18px;height:18px;border-radius:50%;background:${initialHex};flex-shrink:0;transition: background-color 0.2s ease;"></span>
                                <div class="flex-grow-1">
                                    <select name="variants[${index}][warna_id]" class="form-select form-select-sm form-control-custom select-warna-input">
                                        ${generateWarnaSelectOptions(varWarnaId)}
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="text" name="variants[${index}][kode_barang]" class="form-control form-control-sm form-control-custom var-kode-input font-monospace" value="${varKode}" placeholder="Otomatis jika kosong">
                        </td>
                        <input type="hidden" name="variants[${index}][status]" value="${varStatus}">
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row rounded-2" title="Hapus Varian"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                tbody.append(rowHtml);

                // Initialize Select2 with live search on the new row if modal is currently open
                let modalParent = prefix === 'tambah' ? $('#modalTambahBarang') : $('#modalEditBarang');
                if (modalParent.hasClass('show')) {
                    initSelect2Safely(tbody.find('tr:last-child .select-warna-input'), modalParent, '-- Tanpa Warna --');
                }
            }

            // Remove Row Handler
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Live update warna dot indicator
            $(document).on('change select2:select', '.select-warna-input', function() {
                let selectVal = $(this).val();
                let dot = $(this).closest('tr').find('.warna-dot-indicator');
                if (selectVal) {
                    let opt = warnaOptions.find(o => parseInt(o.id) === parseInt(selectVal));
                    if (opt && opt.hex) {
                        dot.css('background-color', opt.hex);
                        return;
                    }
                }
                dot.css('background-color', '#e2e8f0');
            });

            function generateSatuanSelectOptions(selectedId = null, excludeId = null) {
                let html = '<option value="">-- Pilih Satuan --</option>';
                satuanOptions.forEach(opt => {
                    if (excludeId && parseInt(opt.id) === parseInt(excludeId)) return;
                    let sel = selectedId && parseInt(selectedId) === parseInt(opt.id) ? 'selected' : '';
                    html += `<option value="${opt.id}" data-singkatan="${opt.singkatan}" ${sel}>${opt.name}</option>`;
                });
                return html;
            }

            function addKonversiRow(prefix, konv = null) {
                let index = prefix === 'tambah' ? tambahKonversiIndex++ : editKonversiIndex++;
                let tbody = $(`#tbody_${prefix}_konversi`);
                tbody.find('.empty-konversi-row').remove();

                let konvId = konv && konv.id ? konv.id : '';
                let satuanId = konv && konv.satuan_id ? konv.satuan_id : '';
                let faktor = konv && konv.faktor_pengali ? parseFloat(konv.faktor_pengali) : '';
                let isBeli = konv ? (konv.is_satuan_beli ? 'checked' : '') : 'checked';
                let isJual = konv ? (konv.is_satuan_jual ? 'checked' : '') : '';

                let baseId = $(`#${prefix}_satuan_id`).val();
                let baseOpt = satuanOptions.find(o => parseInt(o.id) === parseInt(baseId));
                let baseDisplayName = baseOpt ? (baseOpt.singkatan || baseOpt.name) : 'Satuan Dasar';

                let altOpt = satuanOptions.find(o => parseInt(o.id) === parseInt(satuanId));
                let altDisplayName = altOpt ? (altOpt.singkatan || altOpt.name) : 'Satuan';

                let rowHtml = `
                    <tr class="konversi-row">
                        <input type="hidden" name="konversi_satuan[${index}][id]" value="${konvId}">
                        <td>
                            <select name="konversi_satuan[${index}][satuan_id]" class="form-select form-select-sm select-konversi-satuan form-control-custom" required>
                                ${generateSatuanSelectOptions(satuanId, baseId)}
                            </select>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.0001" min="0.0001" name="konversi_satuan[${index}][faktor_pengali]" class="form-control form-control-sm text-end fw-bold uom-input-field konversi-faktor-input" value="${faktor}" placeholder="Contoh: 50" required>
                                <span class="input-group-text uom-input-addon fw-bold base-satuan-label">${baseDisplayName}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1.5 py-1">
                                <div class="form-check form-check-inline mb-0" style="font-size: 0.76rem;">
                                    <input class="form-check-input" type="checkbox" name="konversi_satuan[${index}][is_satuan_beli]" value="1" id="konv_beli_${prefix}_${index}" ${isBeli}>
                                    <label class="form-check-label fw-medium" for="konv_beli_${prefix}_${index}">Pengadaan / Beli (PO)</label>
                                </div>
                                <div class="form-check form-check-inline mb-0" style="font-size: 0.76rem;">
                                    <input class="form-check-input" type="checkbox" name="konversi_satuan[${index}][is_satuan_jual]" value="1" id="konv_jual_${prefix}_${index}" ${isJual}>
                                    <label class="form-check-label fw-medium" for="konv_jual_${prefix}_${index}">Penjualan / Jual (SO)</label>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-konversi rounded-2" title="Hapus Satuan Alternatif"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                tbody.append(rowHtml);

                // Initialize Select2 with live search on the new row if modal is currently open
                let modalParent = prefix === 'tambah' ? $('#modalTambahBarang') : $('#modalEditBarang');
                if (modalParent.hasClass('show')) {
                    initSelect2Safely(tbody.find('tr:last-child .select-konversi-satuan'), modalParent, '-- Pilih Satuan Alternatif --');
                }
            }

            // Remove Konversi Row
            $(document).on('click', '.btn-remove-konversi', function() {
                let tbody = $(this).closest('tbody');
                $(this).closest('tr').remove();
                if (tbody.find('tr.konversi-row').length === 0) {
                    tbody.html(`
                        <tr class="empty-konversi-row">
                            <td colspan="4" class="text-center text-muted py-3 small">
                                <i class="bi bi-info-circle me-1"></i> Belum ada satuan konversi alternatif. Klik tombol <b>Tambah Satuan Alternatif</b> jika barang memiliki satuan kemasan/grosir (misal: Roll, Dus, Bal).
                            </td>
                        </tr>
                    `);
                }
            });

            // Update base unit label on change
            function updateBaseSatuanLabels(prefix) {
                let baseId = $(`#${prefix}_satuan_id`).val();
                let baseOpt = satuanOptions.find(o => parseInt(o.id) === parseInt(baseId));
                let baseDisplayName = baseOpt ? (baseOpt.singkatan || baseOpt.name) : 'Satuan Dasar';
                $(`#table_${prefix}_konversi .base-satuan-label`).text(baseDisplayName);
                
                // Refresh dropdown options in existing rows to exclude base unit
                let modalParent = prefix === 'tambah' ? $('#modalTambahBarang') : $('#modalEditBarang');
                $(`#table_${prefix}_konversi select.select-konversi-satuan`).each(function() {
                    let curVal = $(this).val();
                    $(this).html(generateSatuanSelectOptions(curVal, baseId));
                    $(this).val(curVal);
                    if (modalParent.hasClass('show')) {
                        initSelect2Safely($(this), modalParent, '-- Pilih Satuan Alternatif --');
                    }
                });
            }

            $(document).on('change select2:select', '#tambah_satuan_id', () => updateBaseSatuanLabels('tambah'));
            $(document).on('change select2:select', '#edit_satuan_id', () => updateBaseSatuanLabels('edit'));

            // Add Row Buttons Click Handlers
            $('#btn_tambah_varian_row').on('click', () => addVarianRow('tambah'));
            $('#btn_edit_varian_row').on('click', () => addVarianRow('edit'));
            $('#btn_tambah_konversi_row').on('click', () => addKonversiRow('tambah'));
            $('#btn_edit_konversi_row').on('click', () => addKonversiRow('edit'));

            // Toggle logic for Varian Checkbox
            function toggleVarianWrapper(prefix) {
                let hasVariants = $(`#${prefix}_has_variants`).is(':checked');
                if (hasVariants) {
                    $(`#${prefix}_varian_wrapper`).show();
                    $(`.${prefix}-warna-field`).addClass('d-none');
                } else {
                    $(`#${prefix}_varian_wrapper`).hide();
                    $(`#tbody_${prefix}_varian`).empty();
                    let type = $(`#${prefix}_tipe_item`).val();
                    if (type === 'Stok' || type === 'Bundle' || type === 'Aset') {
                        $(`.${prefix}-warna-field`).removeClass('d-none');
                    }
                }
            }

            $(document).on('change', '#tambah_has_variants', () => toggleVarianWrapper('tambah'));
            $(document).on('change', '#edit_has_variants', () => toggleVarianWrapper('edit'));

            function toggleTipeItemFields(type, prefix) {
                if (type === 'Stok' || type === 'Bundle' || type === 'Aset') {
                    if (type === 'Aset') {
                        $(`.${prefix}-physical-fields`).addClass('d-none');
                        $(`.${prefix}-stock-fields`).addClass('d-none');
                    } else {
                        $(`.${prefix}-physical-fields`).removeClass('d-none');
                        $(`.${prefix}-stock-fields`).removeClass('d-none');
                    }
                    $(`.${prefix}-warna-field`).removeClass('d-none');
                    if (type === 'Stok') {
                        $(`#${prefix}_wrapper_has_variants`).removeClass('d-none');
                        toggleVarianWrapper(prefix);
                    } else {
                        $(`#${prefix}_wrapper_has_variants`).addClass('d-none');
                        $(`#${prefix}_has_variants`).prop('checked', false).trigger('change');
                    }
                } else { // Jasa
                    $(`.${prefix}-physical-fields`).addClass('d-none');
                    $(`.${prefix}-stock-fields`).addClass('d-none');
                    $(`.${prefix}-warna-field`).addClass('d-none');
                    $(`#${prefix}_wrapper_has_variants`).addClass('d-none');
                    $(`#${prefix}_has_variants`).prop('checked', false).trigger('change');
                }
            }

            $(document).on('change', '#tambah_tipe_item', function() {
                toggleTipeItemFields($(this).val(), 'tambah');
            });

            $(document).on('change', '#edit_tipe_item', function() {
                toggleTipeItemFields($(this).val(), 'edit');
            });

            // ===== INCLUDES SUB-MODULES =====
            @include('data_master.barang.js.datatable')
            @include('data_master.barang.js.tambah')
            @include('data_master.barang.js.edit')
            @include('data_master.barang.js.detail')
            @include('data_master.barang.js.scanner')

            // ===== HAPUS BARANG =====
            $(document).on('click', '.btn-hapus', function () {
                let id = $(this).data('id');
                let nama = $(this).data('nama');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Menghapus barang \"" + nama + "\"?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/barang/' + id + '/delete',
                            type: 'DELETE',
                            success: function (res) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: res.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (err) {
                                let errorMsg = 'Gagal menghapus data.';
                                if (err.responseJSON && err.responseJSON.message) {
                                    errorMsg = err.responseJSON.message;
                                }
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: errorMsg,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
    @endpush
</x-layouts>
