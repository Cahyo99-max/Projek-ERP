{{-- MODAL INPUT REALISASI HARIAN PRODUKSI --}}
<div class="modal fade spk-modal-primary" id="modalInputRealisasi" tabindex="-1" aria-labelledby="modalInputRealisasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content border-0 shadow-lg">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header spk-modal-header border-bottom border-light border-opacity-10 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="spk-header-icon bg-primary bg-opacity-25 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-calendar-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalInputRealisasiLabel">Catat Hasil Produksi Harian</h5>
                        <small class="text-white-50" style="font-size: 11.5px;">Input laporan progres pengerjaan per hari & persentase tahapan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <form id="formInputRealisasi" novalidate>
                @csrf
                <input type="hidden" id="real_tahapan_id">
                <input type="hidden" id="real_spk_id">

                <div class="modal-body spk-modal-body p-4">
                    
                    {{-- INFO CARD TAHAPAN & KUMPULAN PROGRESS --}}
                    <div class="card border-0 spk-modal-summary-card rounded-3 p-3 mb-3 border-start border-4 border-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-info bg-opacity-25 text-info font-monospace fw-bold px-2.5 py-1" id="real_info_spk" style="font-size: 11.5px;">SPK-0000</span>
                            <span id="real_info_status"></span>
                        </div>
                        <h6 class="fw-bold mb-1 spk-card-title-text" id="real_info_nama_tahapan" style="font-size: 15px; color: #ffffff;">Nama Tahapan</h6>
                        <small class="d-block mb-2 spk-card-sub-text" id="real_info_barang" style="color: #cbd5e1; font-size: 12.5px;">Produk: -</small>

                        {{-- VISUAL PROGRESS BAR HARIAN --}}
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small style="color: #94a3b8; font-weight: 600; font-size: 11.5px;">Kumulatif Selesai</small>
                                <small class="fw-bold font-monospace" id="real_info_percent_text" style="color: #38bdf8; font-size: 12.5px;">0 / 0 Pcs (0%)</small>
                            </div>
                            <div class="progress bg-secondary bg-opacity-25" style="height: 8px;">
                                <div id="real_info_percent_bar" class="progress-bar bg-info" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- INPUT FIELDS --}}
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label spk-input-label small fw-semibold mb-1" style="color: #e2e8f0 !important; font-size: 12.5px;">Tanggal Laporan <span class="text-danger">*</span></label>
                            <input type="date" id="real_tanggal" name="tanggal" class="form-control form-control-sm spk-form-input fw-bold" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label spk-input-label small fw-semibold mb-1" style="color: #e2e8f0 !important; font-size: 12.5px;">Target Total Qty</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="real_target_qty" class="form-control spk-form-input fw-bold" readonly>
                                <span class="input-group-text spk-form-addon real_satuan_label">Pcs</span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label spk-input-label small fw-semibold mb-1" style="color: #e2e8f0 !important; font-size: 12.5px;">Hasil Produksi Hari Ini <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-info bg-opacity-25 text-info border-info fw-bold">+</span>
                                <input type="number" id="real_qty_hasil_hari_ini" name="qty_hasil_hari_ini" class="form-control spk-form-input border-info fw-bold qty-limit-6" placeholder="Maks 6 digit" min="0" step="any" required maxlength="6">
                                <span class="input-group-text spk-form-addon real_satuan_label">Pcs</span>
                            </div>
                            <small class="spk-input-subtext d-block font-monospace fw-semibold mt-1" id="real_subtext_sisa_target" style="font-size: 11.5px; color: #94a3b8 !important;">Sisa Boleh Diisi: -</small>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label spk-input-label small fw-semibold mb-1" style="color: #e2e8f0 !important; font-size: 12.5px;">Reject / Defect Hari Ini</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-danger bg-opacity-25 text-danger border-danger fw-bold">+</span>
                                <input type="number" id="real_qty_reject_hari_ini" name="qty_reject_hari_ini" class="form-control spk-form-input border-danger fw-bold qty-limit-6" value="0" min="0" step="any" maxlength="6">
                                <span class="input-group-text spk-form-addon real_satuan_label">Pcs</span>
                            </div>
                            <small class="spk-input-subtext d-block mt-1" style="font-size: 11.5px; color: #94a3b8 !important;">Maksimal 6 karakter (angka)</small>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label spk-input-label small fw-semibold mb-0" style="color: #e2e8f0 !important; font-size: 12.5px;">Catatan Pengerjaan Hari Ini</label>
                                <small id="counter_real_catatan" class="fw-bold" style="font-size: 11px; color: #38bdf8 !important;">0 / 40 Karakter</small>
                            </div>
                            <textarea id="real_catatan" name="catatan" class="form-control spk-form-input catatan-limit-40" rows="2" placeholder="Catatan spesifik pengerjaan hari ini (maks 40 karakter)..." maxlength="40"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label spk-input-label small fw-semibold mb-0" style="color: #e2e8f0 !important; font-size: 12.5px;">
                                    <i class="bi bi-camera-fill me-1.5 text-info"></i>Foto Bukti Hasil Pengerjaan
                                </label>
                                <span class="badge bg-danger text-white fw-bold px-2.5 py-1 shadow-sm" style="font-size: 10.5px; letter-spacing: 0.3px;">
                                    <i class="bi bi-asterisk me-0.5" style="font-size: 8.5px;"></i> Wajib
                                </span>
                            </div>
                            
                            <input type="hidden" id="real_foto_bukti_base64" name="foto_bukti_base64">

                            {{-- KARTU AKSI KAMERA SLEEK DUAL-ACTION --}}
                            <div id="containerBtnKamera" class="cinematic-camera-card">
                                <div class="row g-2 align-items-center">
                                    <div class="col-7 col-sm-8">
                                        <button type="button" class="btn btn-cinematic-camera w-100 py-2.5 px-2 px-sm-3 d-flex align-items-center justify-content-center gap-2" onclick="openLiveCameraStream()">
                                            <i class="bi bi-camera-fill fs-6"></i>
                                            <span class="fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">AMBIL FOTO KAMERA</span>
                                        </button>
                                    </div>
                                    <div class="col-5 col-sm-4">
                                        <button type="button" class="btn btn-cinematic-file w-100 py-2.5 px-2 px-sm-3 d-flex align-items-center justify-content-center gap-2" onclick="$('#real_foto_bukti').click()">
                                            <i class="bi bi-folder2-open fs-6"></i>
                                            <span class="fw-bold" style="font-size: 12px;">Pilih File</span>
                                        </button>
                                        <input type="file" id="real_foto_bukti" name="foto_bukti" class="d-none" accept="image/*" onchange="previewFotoBuktiInput(this)">
                                    </div>
                                </div>
                            </div>

                            {{-- BOX WEBCAM LIVE STREAM HUD VIEW FINDER --}}
                            <div id="containerWebcamBox" class="mt-2 text-center d-none p-3 hud-viewfinder-box">
                                <div class="hud-live-badge">
                                    <span class="spinner-grow spinner-grow-sm me-1 text-emerald-400" style="width: 8px; height: 8px;" role="status"></span>
                                    LIVE CAMERA HUD
                                </div>
                                <video id="videoWebcam" autoplay playsinline class="rounded-3 w-100 mb-3" style="max-height: 240px; object-fit: cover; background: #000;"></video>
                                <canvas id="canvasWebcam" class="d-none"></canvas>
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    {{-- SHUTTER BUTTON --}}
                                    <button type="button" class="btn btn-snap-photo px-3.5 py-2 d-flex align-items-center gap-1.5" onclick="capturePhotoFromWebcam()" title="Jepret Foto Bukti">
                                        <i class="bi bi-camera-fill fs-6"></i>
                                        <span class="fw-bold text-nowrap" style="font-size:12px; letter-spacing:0.5px;">JEPRET FOTO</span>
                                    </button>
                                    
                                    {{-- SWITCH CAMERA ICON BUTTON --}}
                                    <button type="button" class="btn btn-outline-info rounded-circle d-flex align-items-center justify-content-center p-0" onclick="switchCameraFacing()" title="Putar / Ganti Kamera" style="width:38px; height:38px;">
                                        <i class="bi bi-arrow-repeat fs-5"></i>
                                    </button>
                                    
                                    {{-- CLOSE CAMERA ICON BUTTON --}}
                                    <button type="button" class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center p-0" onclick="closeLiveCameraStream()" title="Tutup Kamera" style="width:38px; height:38px;">
                                        <i class="bi bi-x-lg fs-6"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- BOX PREVIEW HASIL JEPRET FOTO CINEMATIC --}}
                            <div id="containerPreviewFoto" class="mt-2 text-center d-none preview-cinematic-box">
                                <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2">
                                    <span class="badge px-3 py-1 font-monospace fw-bold" style="color:#34d399!important; background:rgba(16,185,129,0.18)!important; border:1px solid rgba(16,185,129,0.4)!important; border-radius:20px;">
                                        <i class="bi bi-check-circle-fill me-1"></i> FOTO BUKTI TERVERIFIKASI
                                    </span>
                                </div>
                                <img id="imgPreviewFoto" src="" class="img-fluid preview-img-frame mb-3">
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="openLiveCameraStream()">
                                        <i class="bi bi-camera-fill me-1"></i> Foto Ulang / Ambil Lagi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="real_mark_as_selesai" name="mark_as_selesai" value="1">
                                <label class="form-check-label spk-switch-label small fw-semibold" for="real_mark_as_selesai" style="color: #e2e8f0 !important; font-size: 12.5px;">
                                    Tandai Tahapan Ini Selesai 100% (Serahkan ke Divisi Berikutnya)
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer spk-modal-footer d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" id="btnSimpanRealisasi" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs w-100 w-sm-auto order-1 order-sm-2">
                        <i class="bi bi-save me-1"></i> Simpan Laporan Harian
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
    /* ULTRA-MODERN GLASSMORPHISM STYLING FOR LOG HISTORY, INPUT & EDIT MODALS */
    #modalInputRealisasi .modal-content,
    #modalRiwayatLogHarian .modal-content,
    #modalEditLogHarian .modal-content {
        background: linear-gradient(145deg, #0b1329 0%, #1e293b 100%);
        border: 1px solid rgba(56, 189, 248, 0.35);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85), 0 0 35px rgba(14, 165, 233, 0.25);
        border-radius: 20px;
        overflow: hidden;
    }

    #modalInputRealisasi .modal-header,
    #modalRiwayatLogHarian .modal-header,
    #modalEditLogHarian .modal-header {
        background: linear-gradient(135deg, #0c1a2e 0%, #0f2040 100%) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 18px 24px !important;
    }

    /* THEME RESPONSIVE INPUTS, CARDS, LABELS */
    .spk-modal-summary-card {
        background: rgba(15, 23, 42, 0.7);
        border-color: rgba(56, 189, 248, 0.35) !important;
    }
    .spk-card-title-text { color: #ffffff !important; font-weight: 700 !important; }
    .spk-card-sub-text   { color: #cbd5e1 !important; font-weight: 500 !important; }

    .spk-form-input {
        background-color: #0f172a !important;
        color: #ffffff !important;
        border-color: rgba(56, 189, 248, 0.3) !important;
    }
    .spk-form-input:focus {
        background-color: #0f172a !important;
        color: #ffffff !important;
        border-color: #38bdf8 !important;
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.4) !important;
    }
    .spk-form-input[readonly] {
        background-color: rgba(15, 23, 42, 0.7) !important;
        color: #cbd5e1 !important;
        font-weight: 600 !important;
    }
    .spk-form-input::placeholder {
        color: #64748b !important;
        opacity: 0.85 !important;
    }

    .spk-form-addon {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: rgba(56, 189, 248, 0.3) !important;
        font-weight: 600 !important;
    }

    .spk-input-label   { color: #e2e8f0 !important; font-weight: 600 !important; font-size: 12.5px !important; }
    .spk-input-subtext { color: #94a3b8 !important; font-weight: 500 !important; font-size: 11.5px !important; }
    .spk-switch-label  { color: #e2e8f0 !important; font-weight: 600 !important; font-size: 12.5px !important; }

    /* ═══════════════════════════════════════════════════════
       LIGHT MODE OVERRIDES FOR ALL MODALS
       ═══════════════════════════════════════════════════════ */
    html:not([data-bs-theme="dark"]) #modalInputRealisasi .modal-content,
    html:not([data-bs-theme="dark"]) #modalEditLogHarian .modal-content {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18) !important;
    }

    html:not([data-bs-theme="dark"]) #modalInputRealisasi .modal-body,
    html:not([data-bs-theme="dark"]) #modalEditLogHarian .modal-body {
        background: #ffffff !important;
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) .spk-modal-summary-card {
        background: #f8fafc !important;
        border-color: #0284c7 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-card-title-text { color: #0f172a !important; }
    html:not([data-bs-theme="dark"]) .spk-card-sub-text   { color: #64748b !important; }

    html:not([data-bs-theme="dark"]) .spk-form-input {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-color: #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-form-input:focus {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border-color: #0284c7 !important;
        box-shadow: 0 0 10px rgba(2, 132, 199, 0.25) !important;
    }
    html:not([data-bs-theme="dark"]) .spk-form-input[readonly] {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        border-color: #cbd5e1 !important;
    }

    html:not([data-bs-theme="dark"]) .spk-form-addon {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }

    html:not([data-bs-theme="dark"]) .spk-input-label   { color: #0f172a !important; font-weight: 700 !important; }
    html:not([data-bs-theme="dark"]) .spk-input-subtext { color: #475569 !important; }
    html:not([data-bs-theme="dark"]) .spk-switch-label  { color: #0f172a !important; font-weight: 700 !important; }

    html:not([data-bs-theme="dark"]) .cinematic-camera-card {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: none !important;
    }
    html:not([data-bs-theme="dark"]) .btn-cinematic-file {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #0284c7 !important;
    }
    html:not([data-bs-theme="dark"]) .btn-cinematic-file:hover {
        background: #e0f2fe !important;
        border-color: #0284c7 !important;
        color: #0369a1 !important;
    }

    html:not([data-bs-theme="dark"]) #modalInputRealisasi .modal-footer,
    html:not([data-bs-theme="dark"]) #modalEditLogHarian .modal-footer {
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }
    html:not([data-bs-theme="dark"]) #modalInputRealisasi .btn-outline-secondary,
    html:not([data-bs-theme="dark"]) #modalEditLogHarian .btn-outline-secondary {
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) #modalInputRealisasi .btn-outline-secondary:hover,
    html:not([data-bs-theme="dark"]) #modalEditLogHarian .btn-outline-secondary:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    /* Header text contrast protection (header is always dark navy gradient in both modes) */
    .spk-modal-header .modal-title,
    #modalEditLogHarian .modal-header .modal-title,
    #modalInputRealisasi .modal-header .modal-title,
    #modalRiwayatLogHarian .modal-header .modal-title,
    .riwayat-modal-header .modal-title { 
        color: #ffffff !important; 
    }

    .spk-modal-header small,
    #modalEditLogHarian .modal-header small,
    #modalInputRealisasi .modal-header small,
    #modalRiwayatLogHarian .modal-header small,
    .riwayat-modal-header p,
    .riwayat-modal-header small { 
        color: rgba(226, 232, 240, 0.9) !important; 
    }

    /* ═══════════════════════════════════════════════════════
       LIGHT MODE OVERRIDES FOR RIWAYAT MODAL & TABLES
       ═══════════════════════════════════════════════════════ */
    html:not([data-bs-theme="dark"]) .riwayat-modal-content {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18) !important;
    }

    html:not([data-bs-theme="dark"]) .riwayat-modal-body {
        background: #ffffff !important;
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) .riwayat-stat-strip {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    html:not([data-bs-theme="dark"]) .riwayat-stat-strip #log_info_tahapan {
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) .riwayat-stat-strip .stat-divider {
        background: #cbd5e1 !important;
    }

    html:not([data-bs-theme="dark"]) .riwayat-table-wrap {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark {
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark thead th {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark tbody tr {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark tbody tr:hover {
        background: #f8fafc !important;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1), inset 3px 0 0 #0284c7;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark tbody td {
        border-top: 1px solid #f1f5f9 !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark tbody tr td:first-child {
        border-left: 1px solid #e2e8f0 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark tbody tr td:last-child {
        border-right: 1px solid #e2e8f0 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark td .text-white,
    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark td div.text-white {
        color: #0f172a !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .table-modern-dark td .text-white-50 {
        color: #475569 !important;
    }

    /* Light mode badge colors inside table */
    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .badge-hasil-qty {
        color: #15803d !important;
        background: #dcfce7 !important;
        border: 1px solid #86efac !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .badge-reject-qty {
        color: #b91c1c !important;
        background: #fee2e2 !important;
        border: 1px solid #fca5a5 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .badge-reject-zero {
        color: #94a3b8 !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .btn-edit-modern {
        background: #fef3c7 !important;
        color: #b45309 !important;
        border: 1px solid #fde68a !important;
    }

    html:not([data-bs-theme="dark"]) #modalRiwayatLogHarian .btn-edit-modern:hover {
        background: #f59e0b !important;
        color: #ffffff !important;
    }

    /* CINEMATIC HIGH-TECH CAMERA CONTROL STYLING */
    .cinematic-camera-card {
        background: linear-gradient(145deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.85)) !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
        border-radius: 14px !important;
        padding: 12px 14px !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 8px 20px rgba(0, 0, 0, 0.35);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cinematic-camera-card:hover {
        border-color: rgba(56, 189, 248, 0.45) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 10px 25px rgba(14, 165, 233, 0.15);
    }

    .btn-cinematic-camera {
        background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 10px 18px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
        white-space: nowrap !important;
        box-shadow: 0 0 18px rgba(14, 165, 233, 0.4), 0 4px 12px rgba(0, 0, 0, 0.3) !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-cinematic-camera:hover {
        transform: translateY(-1.5px) scale(1.01) !important;
        box-shadow: 0 0 26px rgba(14, 165, 233, 0.75), 0 6px 18px rgba(0, 0, 0, 0.4) !important;
        background: linear-gradient(135deg, #38bdf8 0%, #3b82f6 100%) !important;
        color: #ffffff !important;
    }

    .btn-cinematic-file {
        background: rgba(30, 41, 59, 0.85) !important;
        border: 1px solid rgba(56, 189, 248, 0.3) !important;
        color: #38bdf8 !important;
        border-radius: 50px !important;
        padding: 10px 16px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
    }

    .btn-cinematic-file:hover {
        background: rgba(56, 189, 248, 0.2) !important;
        border-color: #38bdf8 !important;
        color: #ffffff !important;
    }

    .hud-viewfinder-box {
        position: relative;
        background: #030712 !important;
        border: 2px solid #0ea5e9 !important;
        border-radius: 16px !important;
        overflow: hidden;
        box-shadow: 0 0 25px rgba(14, 165, 233, 0.4), inset 0 0 30px rgba(0, 0, 0, 0.8);
    }

    .hud-live-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 20;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(16, 185, 129, 0.5);
        color: #34d399;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 20px;
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }

    .btn-snap-photo {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 10px 24px !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        letter-spacing: 0.5px !important;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.6) !important;
        transition: all 0.2s ease !important;
    }

    .btn-snap-photo:hover {
        transform: scale(1.05) !important;
        box-shadow: 0 0 28px rgba(16, 185, 129, 0.8) !important;
    }

    .preview-cinematic-box {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(15, 23, 42, 0.8)) !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
        border-radius: 16px !important;
        padding: 16px !important;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
    }

    .preview-img-frame {
        max-height: 180px;
        object-fit: contain;
        border-radius: 12px;
        border: 2px solid rgba(56, 189, 248, 0.5);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }

    .spk-header-icon-gradient {
        background: linear-gradient(135deg, #0284c7, #2563eb) !important;
        box-shadow: 0 0 20px rgba(2, 132, 199, 0.55), 0 0 40px rgba(37, 99, 235, 0.25) !important;
        color: #ffffff !important;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ── RIWAYAT MODAL PROFESSIONAL HEADER ── */
    .riwayat-modal-header {
        background: linear-gradient(135deg, #0c1a2e 0%, #0f2040 45%, #0a1628 100%) !important;
        border-bottom: 1px solid rgba(56, 189, 248, 0.2) !important;
        padding: 18px 24px !important;
        position: relative;
        overflow: hidden;
    }

    .riwayat-modal-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, #60a5fa, #38bdf8, transparent);
    }

    .riwayat-modal-header::after {
        content: '';
        position: absolute;
        top: -60px; right: -40px;
        width: 160px; height: 160px;
        background: radial-gradient(circle, rgba(56,189,248,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .riwayat-stat-strip {
        background: rgba(15, 32, 64, 0.8);
        border-bottom: 1px solid rgba(56, 189, 248, 0.12);
        padding: 10px 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .riwayat-stat-strip .stat-divider {
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.2);
        flex-shrink: 0;
    }

    .riwayat-modal-content {
        background: #0d1b2e !important;
        border: 1px solid rgba(56, 189, 248, 0.18) !important;
        border-radius: 20px !important;
        overflow: hidden;
        box-shadow: 0 32px 80px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(56, 189, 248, 0.1) !important;
    }

    .riwayat-modal-body {
        background: #0d1b2e !important;
        padding: 20px 24px !important;
    }

    /* Scrollbar for riwayat table container */
    .riwayat-table-wrap {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 440px;
        border-radius: 14px;
        border: 1px solid rgba(56, 189, 248, 0.12);
        background: rgba(15, 23, 42, 0.6);
        scrollbar-width: thin;
        scrollbar-color: rgba(56, 189, 248, 0.4) rgba(12, 24, 44, 0.8);
    }

    .riwayat-table-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
    .riwayat-table-wrap::-webkit-scrollbar-track { background: rgba(12, 24, 44, 0.8); }
    .riwayat-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(56, 189, 248, 0.45);
        border-radius: 4px;
    }
    .riwayat-table-wrap::-webkit-scrollbar-thumb:hover { background: #38bdf8; }

    .spk-header-icon-warning {
        background: linear-gradient(135deg, #d97706, #f59e0b) !important;
        box-shadow: 0 0 16px rgba(245, 158, 11, 0.6) !important;
        color: #ffffff !important;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stage-summary-card {
        background: rgba(15, 23, 42, 0.75) !important;
        backdrop-filter: blur(12px);
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
        border-left: 5px solid #38bdf8 !important;
        border-radius: 14px !important;
        padding: 16px 20px !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 8px 20px rgba(0, 0, 0, 0.25);
    }

    .spk-chip-code {
        background: rgba(14, 165, 233, 0.18) !important;
        border: 1px solid rgba(14, 165, 233, 0.45) !important;
        color: #38bdf8 !important;
        font-family: var(--bs-font-monospace);
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 13px;
    }

    /* SCROLLABLE TABLE CONTAINER WITH STICKY HEADER (~10 ROWS VISIBLE) */
    #modalRiwayatLogHarian .table-responsive-scrollable {
        max-height: 480px;
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
        scrollbar-color: rgba(56, 189, 248, 0.4) rgba(15, 23, 42, 0.6);
    }

    #modalRiwayatLogHarian .table-responsive-scrollable::-webkit-scrollbar {
        width: 6px;
    }
    #modalRiwayatLogHarian .table-responsive-scrollable::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.6);
        border-radius: 4px;
    }
    #modalRiwayatLogHarian .table-responsive-scrollable::-webkit-scrollbar-thumb {
        background: rgba(56, 189, 248, 0.4);
        border-radius: 4px;
    }
    #modalRiwayatLogHarian .table-responsive-scrollable::-webkit-scrollbar-thumb:hover {
        background: #38bdf8;
    }

    .table-modern-dark {
        --bs-table-bg: transparent;
        --bs-table-accent-bg: transparent;
        color: #f8fafc;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .table-modern-dark thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #0f172a !important;
        backdrop-filter: blur(8px);
        color: #94a3b8;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 14px 16px;
        border: none !important;
    }

    .table-modern-dark tbody tr {
        background: rgba(30, 41, 59, 0.65) !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
    }

    .table-modern-dark tbody tr:hover {
        background: rgba(30, 41, 59, 0.98) !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35), inset 3px 0 0 #38bdf8;
    }

    .table-modern-dark tbody td {
        padding: 14px 16px;
        border: none !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .table-modern-dark tbody tr td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        border-left: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .table-modern-dark tbody tr td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
        border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    .thumb-frame-modern {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid rgba(56, 189, 248, 0.4);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        transition: all 0.25s ease;
    }

    .thumb-frame-modern:hover {
        transform: scale(1.18) rotate(2deg);
        border-color: #38bdf8;
        box-shadow: 0 0 16px rgba(56, 189, 248, 0.6);
    }

    .btn-edit-modern {
        background: rgba(245, 158, 11, 0.15);
        border: 1px solid rgba(245, 158, 11, 0.45);
        color: #fbbf24;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-edit-modern:hover {
        background: #f59e0b;
        color: #0f172a;
        transform: scale(1.12);
        box-shadow: 0 0 14px rgba(245, 158, 11, 0.7);
    }

    /* ── FOTO LIGHTBOX ── */
    #modalFotoLightbox .modal-dialog {
        max-width: 92vw;
        max-height: 92vh;
        margin: auto;
    }

    .lightbox-overlay {
        background: rgba(5, 10, 22, 0.97) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(56, 189, 248, 0.15) !important;
        border-radius: 20px !important;
        overflow: hidden;
        box-shadow: 0 40px 100px rgba(0,0,0,0.8), 0 0 0 1px rgba(56,189,248,0.12) !important;
    }

    .lightbox-header {
        background: linear-gradient(135deg, #0c1a2e 0%, #0f2040 100%) !important;
        border-bottom: 1px solid rgba(56, 189, 248, 0.18) !important;
        padding: 14px 20px !important;
        position: relative;
    }

    .lightbox-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, #60a5fa, #38bdf8, transparent);
    }

    .lightbox-body {
        background: #050a16 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px !important;
        min-height: 280px;
    }

    #lightboxImage {
        max-width: 100%;
        max-height: 72vh;
        object-fit: contain;
        border-radius: 14px;
        border: 1px solid rgba(56, 189, 248, 0.2);
        box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 30px rgba(56,189,248,0.08);
        transition: transform 0.3s ease;
    }

    .lightbox-footer {
        background: linear-gradient(180deg, #0a1628 0%, #0c1a2e 100%) !important;
        border-top: 1px solid rgba(56, 189, 248, 0.1) !important;
        padding: 12px 20px !important;
    }

    .btn-lightbox-download {
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.4);
        color: #38bdf8;
        border-radius: 10px;
        padding: 7px 18px;
        font-size: 12.5px;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-lightbox-download:hover {
        background: #38bdf8;
        color: #0c1a2e;
        box-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
        text-decoration: none;
    }

    .thumb-frame-modern {
        cursor: zoom-in;
    }

    /* MOBILE ERGONOMICS & RESPONSIVE DESIGN (< 576px) */
    @media (max-width: 575.98px) {
        .modal-dialog {
            margin: 4px auto !important;
            max-width: calc(100% - 8px) !important;
        }

        .spk-modal-content {
            border-radius: 14px !important;
            margin: 0 !important;
        }

        #modalInputRealisasi .modal-header,
        #modalRiwayatLogHarian .modal-header,
        #modalEditLogHarian .modal-header {
            padding: 14px 16px !important;
        }

        #modalInputRealisasi .modal-body,
        #modalRiwayatLogHarian .modal-body,
        #modalEditLogHarian .modal-body {
            padding: 16px 14px !important;
        }

        .cinematic-camera-card {
            padding: 14px 12px !important;
            border-radius: 14px !important;
        }

        .btn-cinematic-camera {
            padding: 10px 14px !important;
            font-size: 11.5px !important;
            letter-spacing: 0.5px !important;
        }

        .btn-cinematic-file {
            width: 100% !important;
            text-align: center !important;
            padding: 8px 14px !important;
        }

        .stage-summary-card {
            padding: 12px 14px !important;
        }

        .hud-viewfinder-box {
            padding: 10px !important;
            border-radius: 14px !important;
        }

        #modalRiwayatLogHarian .table-responsive-scrollable {
            max-height: 380px !important;
        }

        .table-modern-dark th, 
        .table-modern-dark td {
            padding: 10px 8px !important;
            font-size: 11.5px !important;
        }
    }
</style>

{{-- MODAL RIWAYAT LOG HARIAN TAHAPAN --}}
<div class="modal fade spk-modal-primary" id="modalRiwayatLogHarian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content riwayat-modal-content border-0">

            {{-- ── PREMIUM GRADIENT HEADER ── --}}
            <div class="riwayat-modal-header d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="spk-header-icon-gradient">
                        <i class="bi bi-clock-history" style="font-size:18px;"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white text-nowrap" style="font-size:15px; letter-spacing:0.2px;">Riwayat Laporan Produksi Harian</h5>
                            <span class="badge text-nowrap" style="background:rgba(56,189,248,0.18); border:1px solid rgba(56,189,248,0.35); color:#38bdf8; font-size:10.5px; font-weight:600; letter-spacing:0.4px;">LOG HARIAN</span>
                        </div>
                        <p class="mb-0 text-nowrap" style="color:rgba(148,163,184,0.75); font-size:11.5px; margin-top:3px;">Progres realisasi harian per tahapan pengerjaan</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0.6); transition:filter 0.2s;" onmouseenter="this.style.filter='brightness(1)'" onmouseleave="this.style.filter='brightness(0.6)'"></button>
            </div>

            {{-- ── STAT STRIP (SPK + Tahapan + Total) ── --}}
            <div class="riwayat-stat-strip">
                <i class="bi bi-tag-fill" style="color:#38bdf8; font-size:12px; flex-shrink:0;"></i>
                <span class="font-monospace fw-bold text-nowrap" id="log_info_spk" style="color:#38bdf8; font-size:12px;">SPK-0000</span>
                <div class="stat-divider"></div>
                <i class="bi bi-layers-fill" style="color:#94a3b8; font-size:11px; flex-shrink:0;"></i>
                <span class="fw-semibold text-white text-nowrap" id="log_info_tahapan" style="font-size:12.5px;">Nama Tahapan</span>
                <div class="stat-divider ms-auto"></div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <i class="bi bi-check-circle-fill" style="color:#34d399; font-size:12px;"></i>
                    <span class="text-white-50 text-uppercase fw-semibold" style="font-size:9.5px; letter-spacing:0.5px;">Total Selesai</span>
                    <span class="fw-bold font-monospace" id="log_info_total" style="color:#38bdf8; font-size:13px; margin-left:4px;">0 Pcs</span>
                </div>
            </div>

            {{-- ── MODAL BODY ── --}}
            <div class="riwayat-modal-body modal-body">
                <div class="riwayat-table-wrap">
                    <table class="table table-modern-dark align-middle mb-0" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th style="min-width:165px;"><i class="bi bi-calendar3 me-1 opacity-60"></i>Tanggal</th>
                                <th class="text-center" style="min-width:130px;"><i class="bi bi-check2-circle me-1 opacity-60"></i>Hasil Hari Ini</th>
                                <th class="text-center" style="min-width:105px;"><i class="bi bi-x-circle me-1 opacity-60"></i>Reject</th>
                                <th class="text-center" style="min-width:80px;"><i class="bi bi-camera me-1 opacity-60"></i>Foto</th>
                                <th style="min-width:145px;"><i class="bi bi-person me-1 opacity-60"></i>PIC</th>
                                <th style="min-width:170px; white-space:normal;"><i class="bi bi-chat-left-text me-1 opacity-60"></i>Catatan</th>
                                <th class="text-center" style="min-width:60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRiwayatLogs">
                            <tr>
                                <td colspan="7" class="text-center py-4 text-white-50">Memuat riwayat log harian...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL FOTO LIGHTBOX --}}
<div class="modal fade" id="modalFotoLightbox" tabindex="-1" aria-hidden="true" style="z-index: 10080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content lightbox-overlay border-0">

            {{-- Header --}}
            <div class="lightbox-header d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="background:linear-gradient(135deg,#0284c7,#2563eb); width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 16px rgba(2,132,199,0.5); flex-shrink:0;">
                        <i class="bi bi-image text-white" style="font-size:16px;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white text-nowrap" style="font-size:14px;">Foto Bukti Pengerjaan</div>
                        <div id="lightboxMeta" class="text-nowrap" style="color:rgba(148,163,184,0.7); font-size:11px;">—</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    style="filter:brightness(0.5); transition:filter 0.2s;"
                    onmouseenter="this.style.filter='brightness(1)'" onmouseleave="this.style.filter='brightness(0.5)'"></button>
            </div>

            {{-- Image display --}}
            <div class="lightbox-body">
                <img id="lightboxImage" src="" alt="Foto Bukti">
            </div>

            {{-- Footer --}}
            <div class="lightbox-footer d-flex align-items-center justify-content-between">
                <span style="color:rgba(148,163,184,0.6); font-size:11px;"><i class="bi bi-info-circle me-1"></i>Klik di luar foto untuk menutup</span>
                <a id="lightboxDownload" href="#" download target="_blank" class="btn-lightbox-download">
                    <i class="bi bi-download"></i> Unduh Foto
                </a>
            </div>

        </div>
    </div>
</div>

{{-- MODAL EDIT LOG HARIAN TAHAPAN --}}
<div class="modal fade spk-modal-primary" id="modalEditLogHarian" tabindex="-1" aria-hidden="true" style="z-index: 10060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content border-0 shadow-lg">
            <div class="modal-header spk-modal-header border-bottom border-light border-opacity-10 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="spk-header-icon bg-warning bg-opacity-25 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6">Edit Catatan Laporan Harian</h5>
                        <small class="text-white-50" style="font-size: 11.5px;">Koreksi kesalahan data inputan pengerjaan harian</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLogHarian" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="edit_log_id" name="log_id">
                <input type="hidden" id="edit_log_tahapan_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label spk-input-label small mb-1">Tanggal Pengerjaan</label>
                        <input type="date" id="edit_tanggal" name="tanggal" class="form-control spk-form-input" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label spk-input-label small mb-1">Hasil Bagus (Qty)</label>
                            <input type="number" step="any" min="0" id="edit_qty_hasil" name="qty_hasil_hari_ini" class="form-control spk-form-input border-info fw-bold qty-limit-6" required maxlength="6">
                            <small class="spk-input-subtext" style="font-size: 10.5px;">Maksimal 6 karakter</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label spk-input-label small mb-1">Reject / Rusak (Qty)</label>
                            <input type="number" step="any" min="0" id="edit_qty_reject" name="qty_reject_hari_ini" class="form-control spk-form-input border-danger fw-bold qty-limit-6" value="0" maxlength="6">
                            <small class="spk-input-subtext" style="font-size: 10.5px;">Maksimal 6 karakter</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label spk-input-label small mb-1"><i class="bi bi-lock-fill me-1 text-warning"></i>PIC (Tidak Dapat Diubah)</label>
                        <input type="text" id="edit_pic_operator" name="pic_operator" class="form-control spk-form-input fw-semibold" readonly tabindex="-1">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label spk-input-label small mb-0">Catatan Pengerjaan</label>
                            <small id="counter_edit_catatan" class="text-info fw-bold" style="font-size: 10.5px;">0 / 40 Karakter</small>
                        </div>
                        <textarea id="edit_catatan" name="catatan" class="form-control spk-form-input catatan-limit-40" rows="2" placeholder="Catatan tambahan (maks 40 karakter)..." maxlength="40"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label spk-input-label small fw-semibold mb-0">
                                <i class="bi bi-camera-fill me-1 text-info"></i>Foto Bukti Pengerjaan (Opsional jika tidak diganti)
                            </label>
                        </div>
                        
                        <input type="hidden" id="edit_foto_bukti_base64" name="foto_bukti_base64">

                        {{-- KARTU AKSI KAMERA SLEEK DUAL-ACTION --}}
                        <div id="containerBtnKameraEdit" class="cinematic-camera-card">
                            <div class="row g-2 align-items-center">
                                <div class="col-7 col-sm-8">
                                    <button type="button" class="btn btn-cinematic-camera w-100 py-2.5 px-2 px-sm-3 d-flex align-items-center justify-content-center gap-1.5" onclick="openLiveCameraStreamEdit()">
                                        <i class="bi bi-camera-fill fs-6"></i>
                                        <span>AMBIL FOTO KAMERA</span>
                                    </button>
                                </div>
                                <div class="col-5 col-sm-4">
                                    <button type="button" class="btn btn-cinematic-file w-100 py-2.5 px-2 px-sm-3 d-flex align-items-center justify-content-center gap-1.5" onclick="$('#edit_foto_bukti').click()">
                                        <i class="bi bi-folder2-open fs-6"></i>
                                        <span>Pilih File</span>
                                    </button>
                                    <input type="file" id="edit_foto_bukti" name="foto_bukti" class="d-none" accept="image/*" onchange="previewFotoBuktiInputEdit(this)">
                                </div>
                            </div>
                        </div>

                        {{-- BOX WEBCAM LIVE STREAM HUD VIEW FINDER --}}
                        <div id="containerWebcamBoxEdit" class="mt-2 text-center d-none p-3 hud-viewfinder-box">
                            <div class="hud-live-badge">
                                <span class="spinner-grow spinner-grow-sm me-1 text-emerald-400" style="width: 8px; height: 8px;" role="status"></span>
                                LIVE CAMERA HUD
                            </div>
                            <video id="videoWebcamEdit" autoplay playsinline class="rounded-3 w-100 mb-3" style="max-height: 240px; object-fit: cover; background: #000;"></video>
                            <canvas id="canvasWebcamEdit" class="d-none"></canvas>
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <button type="button" class="btn btn-snap-photo px-3.5 py-2 d-flex align-items-center gap-1.5" onclick="capturePhotoFromWebcamEdit()" title="Jepret Foto Bukti">
                                    <i class="bi bi-camera-fill fs-6"></i>
                                    <span class="fw-bold text-nowrap" style="font-size:12px; letter-spacing:0.5px;">JEPRET FOTO</span>
                                </button>
                                <button type="button" class="btn btn-outline-info rounded-circle d-flex align-items-center justify-content-center p-0" onclick="switchCameraFacingEdit()" title="Putar / Ganti Kamera" style="width:38px; height:38px;">
                                    <i class="bi bi-arrow-repeat fs-5"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger rounded-circle d-flex align-items-center justify-content-center p-0" onclick="closeLiveCameraStreamEdit()" title="Tutup Kamera" style="width:38px; height:38px;">
                                    <i class="bi bi-x-lg fs-6"></i>
                                </button>
                            </div>
                        </div>

                        {{-- BOX PREVIEW HASIL JEPRET FOTO CINEMATIC --}}
                        <div id="containerPreviewFotoEdit" class="mt-2 text-center d-none preview-cinematic-box">
                            <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2">
                                <span class="badge px-3 py-1 font-monospace fw-bold" style="color:#34d399!important; background:rgba(16,185,129,0.18)!important; border:1px solid rgba(16,185,129,0.4)!important; border-radius:20px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> FOTO BARU DIPILIH
                                </span>
                            </div>
                            <img id="imgPreviewFotoEdit" src="" class="img-fluid preview-img-frame mb-3">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" onclick="openLiveCameraStreamEdit()">
                                    <i class="bi bi-camera-fill me-1"></i> Foto Ulang / Ambil Lagi
                                </button>
                            </div>
                        </div>

                        <div id="edit_foto_preview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer spk-modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-xs">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let mediaStreamInstance = null;
    let currentCameraFacing = 'environment';

    async function openLiveCameraStream() {
        $('#containerPreviewFoto').addClass('d-none');
        $('#containerBtnKamera').addClass('d-none');
        $('#containerWebcamBox').removeClass('d-none');

        if (mediaStreamInstance) {
            mediaStreamInstance.getTracks().forEach(track => track.stop());
        }

        try {
            const constraints = {
                video: {
                    facingMode: currentCameraFacing,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            mediaStreamInstance = await navigator.mediaDevices.getUserMedia(constraints);
            const videoEl = document.getElementById('videoWebcam');
            videoEl.srcObject = mediaStreamInstance;
            await videoEl.play();
        } catch (err) {
            console.error("Camera access error:", err);
            try {
                mediaStreamInstance = await navigator.mediaDevices.getUserMedia({ video: true });
                const videoEl = document.getElementById('videoWebcam');
                videoEl.srcObject = mediaStreamInstance;
                await videoEl.play();
            } catch (errFallback) {
                $('#containerWebcamBox').addClass('d-none');
                $('#containerBtnKamera').removeClass('d-none');
                Swal.fire({
                    icon: 'warning',
                    title: 'Izin Kamera Diperlukan',
                    text: 'Tidak dapat mengakses kamera. Silakan izinkan akses kamera di browser Anda atau gunakan opsi Pilih File Foto.',
                    confirmButtonText: 'Pilih File Foto'
                }).then(() => {
                    $('#real_foto_bukti').click();
                });
            }
        }
    }

    function switchCameraFacing() {
        currentCameraFacing = (currentCameraFacing === 'environment') ? 'user' : 'environment';
        openLiveCameraStream();
    }

    function capturePhotoFromWebcam() {
        const video = document.getElementById('videoWebcam');
        const canvas = document.getElementById('canvasWebcam');
        if (!video || !mediaStreamInstance) return;

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataBase64 = canvas.toDataURL('image/jpeg', 0.85);
        $('#real_foto_bukti_base64').val(dataBase64);
        $('#real_foto_bukti').val(''); // Clear file input if captured via camera
        $('#imgPreviewFoto').attr('src', dataBase64);

        closeLiveCameraStream();
        $('#containerPreviewFoto').removeClass('d-none');
    }

    function closeLiveCameraStream() {
        if (mediaStreamInstance) {
            mediaStreamInstance.getTracks().forEach(track => track.stop());
            mediaStreamInstance = null;
        }
        $('#containerWebcamBox').addClass('d-none');
        if ($('#containerPreviewFoto').hasClass('d-none')) {
            $('#containerBtnKamera').removeClass('d-none');
        }
    }

    // Stop webcam when modal closes
    $('#modalInputRealisasi').on('hidden.bs.modal', function() {
        closeLiveCameraStream();
    });

    /* ── WEBCAM LIVE STREAMING LOGIC FOR EDIT MODAL ── */
    let mediaStreamInstanceEdit = null;
    let currentCameraFacingEdit = 'environment';

    async function openLiveCameraStreamEdit() {
        $('#containerPreviewFotoEdit').addClass('d-none');
        $('#containerBtnKameraEdit').addClass('d-none');
        $('#containerWebcamBoxEdit').removeClass('d-none');

        if (mediaStreamInstanceEdit) {
            mediaStreamInstanceEdit.getTracks().forEach(track => track.stop());
        }

        try {
            const constraints = {
                video: {
                    facingMode: currentCameraFacingEdit,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };

            mediaStreamInstanceEdit = await navigator.mediaDevices.getUserMedia(constraints);
            const videoEl = document.getElementById('videoWebcamEdit');
            videoEl.srcObject = mediaStreamInstanceEdit;
            await videoEl.play();
        } catch (err) {
            console.error("Camera access error (Edit):", err);
            try {
                mediaStreamInstanceEdit = await navigator.mediaDevices.getUserMedia({ video: true });
                const videoEl = document.getElementById('videoWebcamEdit');
                videoEl.srcObject = mediaStreamInstanceEdit;
                await videoEl.play();
            } catch (errFallback) {
                $('#containerWebcamBoxEdit').addClass('d-none');
                $('#containerBtnKameraEdit').removeClass('d-none');
                Swal.fire({
                    icon: 'warning',
                    title: 'Izin Kamera Diperlukan',
                    text: 'Tidak dapat mengakses kamera. Silakan izinkan akses kamera di browser Anda atau gunakan opsi Pilih File Foto.',
                    confirmButtonText: 'Pilih File Foto'
                }).then(() => {
                    $('#edit_foto_bukti').click();
                });
            }
        }
    }

    function switchCameraFacingEdit() {
        currentCameraFacingEdit = (currentCameraFacingEdit === 'environment') ? 'user' : 'environment';
        openLiveCameraStreamEdit();
    }

    function capturePhotoFromWebcamEdit() {
        const video = document.getElementById('videoWebcamEdit');
        const canvas = document.getElementById('canvasWebcamEdit');
        if (!video || !mediaStreamInstanceEdit) return;

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataBase64 = canvas.toDataURL('image/jpeg', 0.85);
        $('#edit_foto_bukti_base64').val(dataBase64);
        $('#edit_foto_bukti').val('');
        $('#imgPreviewFotoEdit').attr('src', dataBase64);

        closeLiveCameraStreamEdit();
        $('#containerPreviewFotoEdit').removeClass('d-none');
    }

    function closeLiveCameraStreamEdit() {
        if (mediaStreamInstanceEdit) {
            mediaStreamInstanceEdit.getTracks().forEach(track => track.stop());
            mediaStreamInstanceEdit = null;
        }
        $('#containerWebcamBoxEdit').addClass('d-none');
        if ($('#containerPreviewFotoEdit').hasClass('d-none')) {
            $('#containerBtnKameraEdit').removeClass('d-none');
        }
    }

    $('#modalEditLogHarian').on('hidden.bs.modal', function() {
        closeLiveCameraStreamEdit();
    });

    function previewFotoBuktiInputEdit(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#edit_foto_bukti_base64').val('');
                $('#imgPreviewFotoEdit').attr('src', e.target.result);
                $('#containerBtnKameraEdit').addClass('d-none');
                $('#containerPreviewFotoEdit').removeClass('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#containerPreviewFotoEdit').addClass('d-none');
            $('#containerBtnKameraEdit').removeClass('d-none');
        }
    }

    function previewFotoBuktiInput(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#real_foto_bukti_base64').val(''); // Clear base64 if picked via file input
                $('#imgPreviewFoto').attr('src', e.target.result);
                $('#containerBtnKamera').addClass('d-none');
                $('#containerPreviewFoto').removeClass('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            $('#containerPreviewFoto').addClass('d-none');
            $('#containerBtnKamera').removeClass('d-none');
        }
    }

    $(document).ready(function() {
        // LIVE VALIDATION FOR QTY (MAX 6 CHARACTERS)
        $(document).on('input keyup change', '.qty-limit-6', function() {
            let val = $(this).val();
            if (val.length > 6) {
                $(this).val(val.substring(0, 6));
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Batas Karakter Terlampaui',
                    html: '<span class="text-warning fw-bold">Hasil/Reject produksi harian maksimal 6 karakter (angka)!</span>',
                    timer: 2200,
                    showConfirmButton: false
                });
            }
        });

        // LIVE VALIDATION & COUNTER FOR CATATAN (MAX 40 CHARACTERS)
        $(document).on('input keyup change', '#real_catatan', function() {
            let val = $(this).val();
            if (val.length > 40) {
                $(this).val(val.substring(0, 40));
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Catatan Terlalu Panjang',
                    html: '<span class="text-warning fw-bold">Catatan pengerjaan harian maksimal 40 karakter!</span>',
                    timer: 2200,
                    showConfirmButton: false
                });
            }
            $('#counter_real_catatan').text(`${$(this).val().length} / 40 Karakter`);
        });

        $(document).on('input keyup change', '#edit_catatan', function() {
            let val = $(this).val();
            if (val.length > 40) {
                $(this).val(val.substring(0, 40));
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Catatan Terlalu Panjang',
                    html: '<span class="text-warning fw-bold">Catatan pengerjaan harian maksimal 40 karakter!</span>',
                    timer: 2200,
                    showConfirmButton: false
                });
            }
            $('#counter_edit_catatan').text(`${$(this).val().length} / 40 Karakter`);
        });

        $('#formInputRealisasi').off('submit').on('submit', function(e) {
            e.preventDefault();
            const formElem = this;
            const tahapanId = $('#real_tahapan_id').val();
            const isMarkSelesai = $('#real_mark_as_selesai').is(':checked');

            const qtyHasilRaw = $('#real_qty_hasil_hari_ini').val() || '';
            const qtyRejectRaw = $('#real_qty_reject_hari_ini').val() || '';
            const catatanRaw = $('#real_catatan').val() || '';

            if (qtyHasilRaw.length > 6 || qtyRejectRaw.length > 6) {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Batas Karakter Terlampaui',
                    text: 'Jumlah Hasil atau Reject produksi harian maksimal 6 karakter (angka)!',
                });
                return;
            }

            if (catatanRaw.length > 40) {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Catatan Terlalu Panjang',
                    text: 'Catatan pengerjaan harian maksimal 40 karakter!',
                });
                return;
            }

            const hasBase64Photo = $('#real_foto_bukti_base64').val() !== '';
            const hasFileInput = $('#real_foto_bukti')[0].files && $('#real_foto_bukti')[0].files.length > 0;

            if (!hasBase64Photo && !hasFileInput) {
                Swal.fire({
                    icon: 'warning',
                    title: '📸 Foto Bukti Wajib Diisi!',
                    text: 'Silakan jepret foto langsung dari kamera atau pilih file foto bukti pengerjaan hari ini.',
                    confirmButtonColor: '#0ea5e9',
                    confirmButtonText: '<i class="bi bi-camera-fill me-1"></i> Buka Kamera Sekarang'
                }).then(() => {
                    openLiveCameraStream();
                });
                return;
            }

            const targetQty = parseFloat($('#real_target_qty').val() || 0);
            const currentQty = parseFloat($('#real_qty_selesai_current').val() || 0);
            const todayInput = parseFloat($('#real_qty_hasil_hari_ini').val() || 0);
            const totalAfterInput = currentQty + todayInput;
            const satuan = $('#real_satuan_text').text() || 'Pcs';

            const maxAllowedToday = Math.max(0, targetQty - currentQty);
            if (targetQty > 0 && todayInput > maxAllowedToday) {
                Swal.fire({
                    title: '<div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-5 fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i> Batas Target Terlampaui</div>',
                    html: `<div class="text-start mb-3" style="font-size: 0.93rem; line-height: 1.45;">
                                Input hasil produksi hari ini (<b>${todayInput} ${satuan}</b>) melebihi sisa target pengerjaan yang diizinkan (<b>${maxAllowedToday} ${satuan}</b>).
                           </div>
                           <div class="p-3.5 rounded-3 border mb-3 text-start shadow-xs" style="background-color: var(--spk-card-bg, rgba(245, 158, 11, 0.06)); border-color: rgba(245, 158, 11, 0.25) !important;">
                               <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.85rem;">
                                   <i class="bi bi-shield-exclamation fs-6"></i> RINGKASAN BATAS TARGET:
                               </div>
                               <div class="spk-text-muted mt-1.5" style="font-size: 0.84rem; line-height: 1.5;">
                                   • Target Total Qty: <b>${targetQty} ${satuan}</b><br>
                                   • Kumulatif Selesai Lalu: <b>${currentQty} ${satuan}</b><br>
                                   • Sisa Maksimal Boleh Diisi: <b>${maxAllowedToday} ${satuan}</b>
                               </div>
                           </div>
                           <div class="spk-text-muted text-start" style="font-size: 0.82rem;">
                               <i class="bi bi-info-circle me-1 text-info"></i>Total akumulasi pengerjaan tidak boleh melebihi Target Total Qty SPK.
                           </div>`,
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                        confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-semibold'
                    }
                });
                return;
            }

            const executeFormSubmit = () => {
                const $btn = $('#btnSimpanRealisasi');
                const originalText = $btn.html();
                const formData = new FormData(formElem);

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan Laporan & Foto...');

                $.ajax({
                    url: `/produksi/realisasi-produksi/${tahapanId}/store-log`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.prop('disabled', false).html(originalText);
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Laporan & Foto Disimpan!',
                                text: res.message,
                                timer: 1600,
                                showConfirmButton: false
                            });
                            $('#modalInputRealisasi').modal('hide');
                            if (typeof dtRealisasi !== 'undefined' && dtRealisasi) {
                                dtRealisasi.ajax.reload(null, false);
                            } else {
                                setTimeout(() => location.reload(), 1200);
                            }
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan laporan harian.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            };

            if (isMarkSelesai && targetQty > 0 && totalAfterInput < targetQty) {
                const sisa = targetQty - totalAfterInput;
                Swal.fire({
                    title: '❌ Target Pengerjaan Belum Tercapai!',
                    html: `
                        <div class="text-start">
                            <p class="mb-2 text-white-50">Total pengerjaan setelah input hari ini baru mencapai <strong>${totalAfterInput} / ${targetQty} ${satuan}</strong>.</p>
                            <div class="alert alert-danger text-white py-2 mb-3 font-monospace fw-bold" style="font-size: 13px;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Masih Kurang: ${sisa} ${satuan}!
                            </div>
                            <p class="small text-white-50 mb-0">Tahapan belum dapat ditandai <strong>Selesai 100%</strong>. Hilangkan centang "Tandai Selesai" atau tambah jumlah hasil produksi hari ini.</p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#0ea5e9',
                    confirmButtonText: 'Mengerti / Perbaiki Input'
                });
                return;
            } else {
                executeFormSubmit();
            }
        });
    });

    function openInputDailyLogModal(spkId, tahapanId) {
        $.ajax({
            url: `/produksi/spk/${spkId}/progress`,
            method: 'GET',
            success: function(res) {
                if (!res.success || !res.spk) return;
                const spk = res.spk;
                const tahap = (spk.tahapans || []).find(t => t.id == tahapanId);
                if (!tahap) return;

                const satuan = spk.satuan ? spk.satuan.nama_satuan : 'Pcs';
                const target = parseFloat(tahap.target_qty || 0);
                const selesai = parseFloat(tahap.qty_selesai || 0);
                const percent = target > 0 ? Math.round((selesai / target) * 100) : 0;

                $('#real_spk_id').val(spk.id);
                $('#real_tahapan_id').val(tahap.id);
                $('#real_qty_selesai_current').val(selesai);
                
                $('#real_info_spk').text(spk.nomor_spk);
                $('#real_info_nama_tahapan').text(`#${tahap.urutan} ${tahap.nama_tahapan}`);
                $('#real_info_barang').text(`Produk: [${spk.barang_jadi ? spk.barang_jadi.kode_barang : '-'}] ${spk.barang_jadi ? spk.barang_jadi.nama_barang : '-'}`);
                
                $('#real_target_qty').val(target);
                $('.real_satuan_label').text(satuan);
                
                $('#real_info_percent_text').text(`${selesai} / ${target} ${satuan} (${percent}%)`);
                $('#real_info_percent_bar').css('width', `${percent}%`);

                const maxSisa = Math.max(0, target - selesai);
                const sisaValHtml = maxSisa > 0 
                    ? `<b style="color: #38bdf8; font-weight: 700;">${maxSisa} ${satuan}</b>` 
                    : `<b style="color: #f87171; font-weight: 700;">0 ${satuan}</b> <span style="color: #34d399; font-weight: 700;">(Sudah 100% Selesai)</span>`;
                $('#real_subtext_sisa_target').html(`Sisa Maksimal Boleh Diisi: ${sisaValHtml}`);

                $('#real_qty_hasil_hari_ini').removeAttr('max').val('');
                $('#real_qty_reject_hari_ini').val('0');
                $('#real_pic_operator').val(tahap.pic_operator || '');
                $('#real_catatan').val('');
                $('#real_foto_bukti').val('');
                $('#containerPreviewFoto').addClass('d-none');
                $('#real_mark_as_selesai').prop('checked', false);

                $('#real_qty_hasil_hari_ini').off('input change').on('input change', function() {
                    const val = parseFloat($(this).val() || 0);
                    const targetQty = parseFloat($('#real_target_qty').val() || 0);
                    const currentQty = parseFloat($('#real_qty_selesai_current').val() || 0);
                    const maxAllowedToday = Math.max(0, targetQty - currentQty);
                    const satuanText = $('#real_satuan_text').text() || 'Pcs';

                    if (targetQty > 0 && val > maxAllowedToday) {
                        $(this).val(maxAllowedToday > 0 ? maxAllowedToday : 0);
                        Swal.fire({
                            title: '<div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-5 fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i> Batas Target Terlampaui</div>',
                            html: `<div class="text-start mb-3" style="font-size: 0.93rem; line-height: 1.45;">
                                        Input hasil produksi (<b>${val} ${satuanText}</b>) melebihi sisa target pengerjaan (<b>${maxAllowedToday} ${satuanText}</b>).
                                   </div>
                                   <div class="p-3.5 rounded-3 border mb-3 text-start shadow-xs" style="background-color: var(--spk-card-bg, rgba(245, 158, 11, 0.06)); border-color: rgba(245, 158, 11, 0.25) !important;">
                                       <div class="d-flex align-items-center gap-2 text-warning fw-bold font-monospace" style="font-size: 0.85rem;">
                                           <i class="bi bi-shield-exclamation fs-6"></i> RINGKASAN TARGET PRODUKSI:
                                       </div>
                                       <div class="spk-text-muted mt-1.5" style="font-size: 0.84rem; line-height: 1.5;">
                                           • Target Total Qty: <b>${targetQty} ${satuanText}</b><br>
                                           • Kumulatif Selesai Lalu: <b>${currentQty} ${satuanText}</b><br>
                                           • Sisa Maksimal Boleh Diisi: <b>${maxAllowedToday} ${satuanText}</b>
                                       </div>
                                   </div>
                                   <div class="spk-text-muted text-start" style="font-size: 0.82rem;">
                                       <i class="bi bi-info-circle me-1 text-info"></i>Nilai otomatis disesuaikan ke sisa maksimal (${maxAllowedToday} ${satuanText}).
                                   </div>`,
                            confirmButtonColor: '#3b82f6',
                            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
                            customClass: {
                                popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25',
                                confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-semibold'
                            }
                        });
                    }
                });

                $('#modalInputRealisasi').modal('show');
            },
            error: function() {
                Swal.fire('Gagal', 'Tidak dapat memuat detail tahapan.', 'error');
            }
        });
    }

    function viewLogHistory(tahapanId) {
        const $tbody = $('#tbodyRiwayatLogs');
        $tbody.html('<tr><td colspan="7" class="text-center py-4 text-white-50"><span class="spinner-border spinner-border-sm me-2 text-info"></span> Memuat riwayat log harian...</td></tr>');
        $('#modalRiwayatLogHarian').modal('show');

        $.ajax({
            url: `/produksi/realisasi-produksi/${tahapanId}/logs`,
            method: 'GET',
            success: function(res) {
                if (!res || !res.success || !res.tahap) {
                    $tbody.html('<tr><td colspan="7" class="text-center py-4 text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Data riwayat log harian tidak dapat dimuat.</td></tr>');
                    return;
                }
                const tahap = res.tahap;
                const spk = res.spk || (tahap ? tahap.spk : null);
                const logs = res.logs || [];
                const satuan = (spk && spk.satuan) ? spk.satuan.nama_satuan : ((spk && spk.barang_jadi && spk.barang_jadi.satuan) ? spk.barang_jadi.satuan.nama_satuan : 'Pcs');

                $('#log_info_spk').text(spk ? spk.nomor_spk : '-');
                $('#log_info_tahapan').text(`#${tahap.urutan || ''} ${tahap.nama_tahapan || 'Tahapan'}`);
                $('#log_info_total').text(`${parseFloat(tahap.qty_selesai || 0).toLocaleString('id-ID')} / ${parseFloat(tahap.target_qty || 0).toLocaleString('id-ID')} ${satuan} (${res.persentase || 0}%)`);

                $tbody.empty();
                if (logs.length === 0) {
                    $tbody.html('<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-info-circle fs-4 text-info d-block mb-2"></i> Belum ada riwayat laporan harian yang dicatat untuk tahapan ini.</td></tr>');
                    return;
                }

                // Show notice if total logs > 100 (display is limited for performance)
                const totalLogs = res.total_logs || logs.length;
                if (totalLogs > 100) {
                    $tbody.append(`<tr><td colspan="7" class="text-center py-2" style="background:rgba(245,158,11,0.08); border-bottom:1px solid rgba(245,158,11,0.2);">
                        <i class="bi bi-lightning-charge-fill me-1" style="color:#fbbf24;"></i>
                        <span style="color:#fbbf24; font-size:12px; font-weight:600;">Menampilkan 100 log terbaru dari total ${totalLogs.toLocaleString('id-ID')} catatan untuk performa optimal</span>
                    </td></tr>`);
                }

                let html = '';
                logs.forEach(log => {
                    const daysIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const d = log.tanggal ? new Date(log.tanggal) : null;
                    const hariStr = d ? daysIndo[d.getDay()] : '';
                    const tglFmt = d ? `${hariStr}, ${d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })}` : '-';
                    
                    let fotoHtml = '<span class="text-white-50 opacity-50 small" style="font-size:18px;">—</span>';
                    if (log.foto_bukti_url) {
                        const safeUrl  = log.foto_bukti_url.replace(/'/g, "\\'");
                        const safeMeta = `${tglFmt} · ${log.pic_operator || '-'}`.replace(/'/g, "\\'");
                        fotoHtml = `<img src="${log.foto_bukti_url}" class="thumb-frame-modern"
                            onclick="openFotoLightbox('${safeUrl}', '${safeMeta}')"
                            title="Klik untuk melihat foto"
                            style="cursor:zoom-in;">`;
                    }

                    const qtyHasil = parseFloat(log.qty_hasil_hari_ini || 0);
                    const qtyReject = parseFloat(log.qty_reject_hari_ini || 0);

                    const creatorName = log.creator ? (log.creator.nama_lengkap || log.creator.name || 'System') : 'System';

                    html += `
                        <tr>
                            <td style="white-space:nowrap;">
                                <div class="fw-bold text-white"><i class="bi bi-calendar-event me-1 text-info opacity-75"></i> ${tglFmt}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-hasil-qty font-monospace fw-bold px-3 py-1.5" style="color:#34d399; background:rgba(16,185,129,0.15); border:1px solid rgba(52,211,153,0.35); font-size:13px;">+${qtyHasil} ${satuan}</span>
                            </td>
                            <td class="text-center">
                                ${qtyReject > 0 
                                    ? `<span class="badge badge-reject-qty font-monospace fw-bold px-3 py-1.5" style="color:#f87171; background:rgba(239,68,68,0.15); border:1px solid rgba(248,113,113,0.35);">+${qtyReject} ${satuan}</span>`
                                    : `<span class="badge-reject-zero small">+0 ${satuan}</span>`}
                            </td>
                            <td class="text-center py-2">${fotoHtml}</td>
                            <td style="white-space:nowrap;">
                                <div>
                                    <div class="fw-bold text-white spk-op-name" style="font-size:13px;">
                                        <i class="bi bi-person-circle text-info me-1"></i>
                                        <span>${log.pic_operator || '-'}</span>
                                    </div>
                                    <div class="small spk-creator-subtext" style="font-size:11px; margin-top:2px;">
                                        <i class="bi bi-laptop me-1 opacity-70"></i>Input: <span class="fw-semibold">${creatorName}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="white-space:normal; min-width:160px;">
                                <span class="text-white-50 small" style="font-size:12.5px;">${log.catatan || '<span class="opacity-40">-</span>'}</span>
                            </td>
                            <td class="text-center">
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('input-hasil-harian-produksi') || auth()->user()->can('input-realisasi-produksi'))
                                <button type="button" class="btn btn-edit-modern" onclick="openEditLogModal(${log.id})" title="Edit / Koreksi catatan harian ini">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @else
                                <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                        </tr>
                    `;
                });

                $tbody.append(html);
            },
            error: function() {
                $tbody.html('<tr><td colspan="7" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat riwayat log harian.</td></tr>');
            }
        });
    }

    /* ── FOTO LIGHTBOX FUNCTION ── */
    function openFotoLightbox(url, meta) {
        $('#lightboxImage').attr('src', url);
        $('#lightboxDownload').attr('href', url);
        $('#lightboxMeta').text(meta || '—');
        const lb = new bootstrap.Modal(document.getElementById('modalFotoLightbox'), {
            backdrop: true,
            keyboard: true
        });
        lb.show();
    }

    function openEditLogModal(logId) {
        $.ajax({
            url: `/produksi/realisasi-produksi/log/${logId}`,
            method: 'GET',
            success: function(res) {
                if (res.success && res.log) {
                    const l = res.log;
                    $('#edit_log_id').val(l.id);
                    $('#edit_log_tahapan_id').val(l.spk_tahapan_id);
                    $('#edit_tanggal').val(l.tanggal ? l.tanggal.substring(0, 10) : '');
                    $('#edit_qty_hasil').val(parseFloat(l.qty_hasil_hari_ini || 0));
                    $('#edit_qty_reject').val(parseFloat(l.qty_reject_hari_ini || 0));
                    $('#edit_pic_operator').val(l.pic_operator || '');
                    $('#edit_catatan').val(l.catatan || '');
                    $('#counter_edit_catatan').text(`${(l.catatan || '').length} / 40 Karakter`);
                    $('#edit_foto_bukti_base64').val('');
                    $('#edit_foto_bukti').val('');
                    closeLiveCameraStreamEdit();
                    $('#containerPreviewFotoEdit').addClass('d-none');
                    $('#containerBtnKameraEdit').removeClass('d-none');

                    if (res.foto_url) {
                        $('#edit_foto_preview').html(`<div class="small spk-input-label mb-1">Foto Saat Ini:</div><img src="${res.foto_url}" class="rounded border border-info shadow-xs" style="max-height: 90px; object-fit:cover;">`);
                    } else {
                        $('#edit_foto_preview').html('');
                    }

                    $('#modalEditLogHarian').modal('show');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat detail log harian.', 'error');
            }
        });
    }

    $(document).ready(function() {
        $('#formEditLogHarian').on('submit', function(e) {
            e.preventDefault();
            const logId = $('#edit_log_id').val();
            const tahapanId = $('#edit_log_tahapan_id').val();

            const editQtyHasilRaw = $('#edit_qty_hasil').val() || '';
            const editQtyRejectRaw = $('#edit_qty_reject').val() || '';
            const editCatatanRaw = $('#edit_catatan').val() || '';

            if (editQtyHasilRaw.length > 6 || editQtyRejectRaw.length > 6) {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Batas Karakter Terlampaui',
                    text: 'Jumlah Hasil atau Reject produksi harian maksimal 6 karakter (angka)!',
                });
                return;
            }

            if (editCatatanRaw.length > 40) {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Catatan Terlalu Panjang',
                    text: 'Catatan pengerjaan harian maksimal 40 karakter!',
                });
                return;
            }

            const formData = new FormData(this);

            $.ajax({
                url: `/produksi/realisasi-produksi/log/${logId}/update`,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.success) {
                        $('#modalEditLogHarian').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Diperbarui!',
                            text: res.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        viewLogHistory(tahapanId);
                        setTimeout(() => location.reload(), 1200);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui log harian.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
</script>
@endpush
