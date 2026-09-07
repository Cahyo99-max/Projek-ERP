{{-- ===== MODAL TAMBAH TENDER (ULTRA-CINEMATIC DUAL-THEME DARK & LIGHT) ===== --}}
<style>
    /* ==========================================================
       DUAL THEME COMPATIBILITY (DARK & LIGHT MODE)
       ========================================================== */
    
    /* Default / Light Theme */
    .tender-modal-content {
        border-radius: 24px !important;
        background: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25) !important;
        transition: all 0.3s ease;
    }
    .tender-modal-title {
        color: #0f172a !important;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .tender-modal-subtitle {
        color: #64748b !important;
    }
    .tender-tabs-container {
        background: #f1f5f9 !important;
        padding: 6px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }
    .tender-nav-pills .tender-tab-btn {
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.82rem;
        color: #64748b !important;
        padding: 9px 16px;
        border: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tender-nav-pills .tender-tab-btn:hover {
        color: #0f172a !important;
        background: rgba(255, 255, 255, 0.6);
    }
    .tender-nav-pills .tender-tab-btn.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35) !important;
    }
    
    /* Section Cards Light */
    .tender-form-card {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 18px !important;
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .tender-form-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    }
    .tender-card-label {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tender-form-label {
        color: #334155 !important;
        font-weight: 600;
        font-size: 0.82rem;
        margin-bottom: 5px;
    }
    .tender-input {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        font-size: 0.85rem;
        padding: 7px 12px;
        transition: all 0.2s ease;
    }
    .tender-input:focus {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
    }
    .tender-input-addon {
        background: #f1f5f9 !important;
        color: #10b981 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px 0 0 10px !important;
        font-size: 0.82rem;
    }
    .tender-modal-footer {
        border-top: 1px solid #e2e8f0 !important;
        background: transparent;
    }

    /* Product Photo Frame */
    .tender-product-img-frame {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(100, 116, 139, 0.08);
        border: 1px solid rgba(100, 116, 139, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    /* ==========================================================
       DARK THEME OVERRIDES (Cinematic Glow & Ultra-Readable)
       ========================================================== */
    [data-bs-theme="dark"] .tender-modal-content,
    .dark .tender-modal-content,
    body.dark-mode .tender-modal-content {
        background: radial-gradient(130% 120% at 50% 0%, #1e293b 0%, #0f172a 100%) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.8), 0 0 40px rgba(16, 185, 129, 0.08) !important;
    }
    [data-bs-theme="dark"] .tender-modal-title,
    .dark .tender-modal-title,
    body.dark-mode .tender-modal-title {
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .tender-modal-subtitle,
    .dark .tender-modal-subtitle,
    body.dark-mode .tender-modal-subtitle {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .tender-tabs-container,
    .dark .tender-tabs-container,
    body.dark-mode .tender-tabs-container {
        background: rgba(15, 23, 42, 0.85) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] .tender-nav-pills .tender-tab-btn,
    .dark .tender-nav-pills .tender-tab-btn,
    body.dark-mode .tender-nav-pills .tender-tab-btn {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .tender-nav-pills .tender-tab-btn:hover,
    .dark .tender-nav-pills .tender-tab-btn:hover,
    body.dark-mode .tender-nav-pills .tender-tab-btn:hover {
        color: #f8fafc !important;
        background: rgba(255, 255, 255, 0.06) !important;
    }
    [data-bs-theme="dark"] .tender-nav-pills .tender-tab-btn.active,
    .dark .tender-nav-pills .tender-tab-btn.active,
    body.dark-mode .tender-nav-pills .tender-tab-btn.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.45) !important;
    }

    /* Section Cards Dark */
    [data-bs-theme="dark"] .tender-form-card,
    .dark .tender-form-card,
    body.dark-mode .tender-form-card {
        background: rgba(30, 41, 59, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(14px);
    }
    [data-bs-theme="dark"] .tender-form-card:hover,
    .dark .tender-form-card:hover,
    body.dark-mode .tender-form-card:hover {
        border-color: rgba(255, 255, 255, 0.16) !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.35);
    }
    [data-bs-theme="dark"] .tender-form-label,
    .dark .tender-form-label,
    body.dark-mode .tender-form-label {
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .tender-input,
    .dark .tender-input,
    body.dark-mode .tender-input {
        background: #0f172a !important;
        color: #f8fafc !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
    }
    [data-bs-theme="dark"] .tender-input:focus,
    .dark .tender-input:focus,
    body.dark-mode .tender-input:focus {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3) !important;
    }
    [data-bs-theme="dark"] .tender-input-addon,
    .dark .tender-input-addon,
    body.dark-mode .tender-input-addon {
        background: #1e293b !important;
        color: #34d399 !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
    }
    [data-bs-theme="dark"] .tender-product-img-frame,
    .dark .tender-product-img-frame,
    body.dark-mode .tender-product-img-frame {
        background: rgba(15, 23, 42, 0.6) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }
    [data-bs-theme="dark"] .btn-tender-cancel,
    .dark .btn-tender-cancel,
    body.dark-mode .btn-tender-cancel {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #cbd5e1 !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
    }

    /* Glow Accents */
    .glow-tag-indigo {
        color: #818cf8 !important;
        background: rgba(99, 102, 241, 0.12);
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(99, 102, 241, 0.25);
    }
    .glow-tag-emerald {
        color: #34d399 !important;
        background: rgba(16, 185, 129, 0.12);
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .glow-tag-amber {
        color: #fbbf24 !important;
        background: rgba(245, 158, 11, 0.12);
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .glow-tag-purple {
        color: #c084fc !important;
        background: rgba(168, 85, 247, 0.12);
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(168, 85, 247, 0.25);
    }
</style>

<div class="modal modal-blur fade" id="modalTambahTender" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content tender-modal-content overflow-hidden">
            
            <!-- Modal Header with Ambient Glow -->
            <div class="modal-header border-0 pb-1 pt-4 px-4 d-flex justify-content-between align-items-center position-relative z-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2.5 rounded-3 d-inline-flex align-items-center shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;">
                        <i class="bi bi-briefcase-fill fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title tender-modal-title mb-0">Buat Prospek Tender & Proyek Baru</h5>
                        <div class="tender-modal-subtitle small fs-8 mt-0.5">Input data lelang, instansi klien, nilai pagu HPS, estimasi kebutuhan barang, dan berkas lelang.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formTambahTender" autocomplete="off" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body p-4 pt-3">

                    <!-- CINEMATIC SEGMENTED TABS (3 TABS) -->
                    <div class="tender-tabs-container mb-4">
                        <ul class="nav nav-pills tender-nav-pills gap-2" id="tambahTabs" role="tablist">
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn active w-100" id="tambah-umum-tab" data-bs-toggle="tab" data-bs-target="#tambah-umum" type="button" role="tab">
                                    <i class="bi bi-info-circle-fill me-1.5"></i> 1. Informasi Proyek & Klien
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn w-100" id="tambah-barang-tab" data-bs-toggle="tab" data-bs-target="#tambah-barang" type="button" role="tab">
                                    <i class="bi bi-cart-check-fill me-1.5"></i> 2. Kebutuhan Barang / Jasa <span class="badge bg-white text-dark rounded-pill ms-1 font-monospace" id="badge_tambah_item_count">0</span>
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link tender-tab-btn w-100" id="tambah-dokumen-tab" data-bs-toggle="tab" data-bs-target="#tambah-dokumen" type="button" role="tab">
                                    <i class="bi bi-folder-fill me-1.5"></i> 3. Berkas Dokumen Lelang (RKS)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- TABS CONTENT -->
                    <div class="tab-content" id="tambahTabsContent">
                        
                        <!-- ==========================================
                             TAB 1: INFO UTAMA & INSTANSI KLIEN
                             ========================================== -->
                        <div class="tab-pane fade show active" id="tambah-umum" role="tabpanel">
                            
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
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_tambah_nama_tender">0/50</span>
                                        </div>
                                        <input type="text" name="nama_tender" id="tambah_nama_tender" class="form-control tender-input char-limited-input" maxlength="50" data-max="50" data-name="Nama Proyek / Tender" placeholder="Contoh: Pengadaan Seragam Dinas Lapangan TNI 2026" required>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label tender-form-label">Kategori Instansi / Klien <span class="text-danger">*</span></label>
                                        <select name="kategori_instansi" id="tambah_kategori_instansi" class="form-select tender-input" required>
                                            <option value="Pemerintah / B2G">🏛️ Pemerintah / B2G (Dinas/Kementerian)</option>
                                            <option value="TNI / POLRI">🎖️ TNI / POLRI</option>
                                            <option value="BUMN / BUMD">🏢 BUMN / BUMD</option>
                                            <option value="Swasta / B2B">💼 Swasta / B2B Corporate</option>
                                            <option value="Lainnya">🌐 Lainnya</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Customer / Instansi Klien <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="hidden" name="customer_id" id="tambah_customer_id" required>
                                            <input type="text" id="tambah_customer_name" class="form-control tender-input rounded-start-3" placeholder="Pilih Customer..." readonly required>
                                            <button type="button" class="btn btn-primary btn-lookup-customer px-3.5 rounded-end-3" data-prefix="tambah">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Sales Owner / PIC Proyek <span class="text-danger">*</span></label>
                                        <select name="sales_id" id="tambah_sales_id" class="form-select tender-input" required>
                                            <option value="">-- Pilih Sales PIC --</option>
                                            @foreach($salesList as $sales)
                                                <option value="{{ $sales->id }}">{{ $sales->nama_lengkap }} {{ $sales->department ? '(' . $sales->department->nama_departemen . ')' : '' }}</option>
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
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_tambah_nilai_estimasi_mask">0/20</span>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text tender-input-addon font-monospace fw-bold">Rp</span>
                                            <input type="text" id="tambah_nilai_estimasi_mask" class="form-control tender-input text-end font-monospace fw-bold text-success fs-6 char-limited-input" maxlength="20" data-max="20" data-name="Nilai Pagu Anggaran" placeholder="0" required>
                                            <input type="hidden" name="nilai_estimasi" id="tambah_nilai_estimasi" value="0">
                                            <input type="hidden" name="nilai_deal" id="tambah_nilai_deal" value="0">
                                        </div>
                                        <div class="form-text fs-9 text-muted mt-1">Estimasi pagu anggaran dari panitia lelang / HPS instansi klien.</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label tender-form-label mb-0">No. Tender / No. Registrasi Proyek <span class="text-danger">*</span></label>
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_tambah_nomor_kontrak">0/40</span>
                                        </div>
                                        <input type="text" name="nomor_kontrak" id="tambah_nomor_kontrak" class="form-control tender-input font-monospace char-limited-input" maxlength="40" data-max="40" data-name="Nomor Tender / Registrasi" placeholder="Contoh: TND-2026-088 / PL-TNI-2026/01" required>
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
                                        <input type="date" name="tanggal" id="tambah_tanggal" class="form-control tender-input font-monospace" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label tender-form-label">Batas Deadline Dokumen <span class="text-danger">*</span></label>
                                        <input type="date" name="deadline" id="tambah_deadline" class="form-control tender-input font-monospace text-danger fw-bold" required>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label tender-form-label">Estimasi Pengumuman / Deal <span class="text-danger">*</span></label>
                                        <input type="date" name="estimasi_closing" id="tambah_estimasi_closing" class="form-control tender-input font-monospace" required>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Tingkat Prioritas <span class="text-danger">*</span></label>
                                        <select name="priority" id="tambah_priority" class="form-select tender-input" required>
                                            <option value="High">🔥 Tinggi (High) - Prioritas Utama</option>
                                            <option value="Medium" selected>⚡ Sedang (Medium)</option>
                                            <option value="Low">🌱 Rendah (Low)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label tender-form-label">Mata Uang (Currency)</label>
                                        <select name="currency" id="tambah_currency" class="form-select tender-input font-monospace">
                                            @foreach($currencies as $curr)
                                                <option value="{{ $curr->kode_currency }}">{{ $curr->kode_currency }} ({{ $curr->simbol }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <input type="hidden" name="status" id="tambah_status" value="Draft">
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
                                            <span class="badge bg-secondary-subtle text-secondary small font-monospace" id="counter_tambah_catatan">0/50</span>
                                        </div>
                                        <textarea name="catatan" id="tambah_catatan" rows="3" class="form-control tender-input char-limited-input" maxlength="50" data-max="50" data-name="Catatan Khusus" placeholder="Syarat kualifikasi lelang, jadwal Aanwijzing, spesifikasi khusus, atau catatan internal tim sales..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ==========================================
                             TAB 2: DAFTAR BARANG YANG DIAJUKAN (PERSIS FORMAT STANDAR)
                             ========================================== -->
                        <div class="tab-pane fade" id="tambah-barang" role="tabpanel">
                            
                            <div class="tender-form-card p-3 p-md-4 mb-3.5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                            <i class="bi bi-list-task fs-6"></i>
                                        </span>
                                        <h6 class="fw-bold mb-0 text-body fs-6">Daftar Barang yang Diajukan</h6>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_tambah_tender_item_row">
                                        <i class="bi bi-plus-circle-fill"></i> <span>Tambah Barang</span>
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0 w-100" id="table_tambah_items">
                                        <thead>
                                            <tr class="border-bottom text-muted small" style="font-size: 0.75rem; font-weight: 600;">
                                                <th width="4%" class="text-center py-2">No</th>
                                                <th width="12%">Tipe</th>
                                                <th width="44%">Nama Barang</th>
                                                <th width="10%" class="text-center">Satuan</th>
                                                <th width="12%">Jumlah</th>
                                                <th width="14%">Catatan</th>
                                                <th width="4%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_tambah_items">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- ==========================================
                             TAB 3: BERKAS DOKUMEN LELANG
                             ========================================== -->
                        <div class="tab-pane fade" id="tambah-dokumen" role="tabpanel">
                            
                            <div class="tender-form-card p-3 p-md-4 mb-3.5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-1.5 rounded-2 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                            <i class="bi bi-paperclip fs-6"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-body fs-6">Lampiran Dokumen Lelang (RKS / KAK)</h6>
                                            <div class="text-muted small" style="font-size: 0.72rem;">Maksimal 30 berkas (PDF, Word, Excel, ZIP).</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_tambah_dokumen_row">
                                        <i class="bi bi-plus-circle-fill"></i> <span>Tambah Lampiran</span>
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0 w-100" id="table_tambah_dokumen">
                                        <thead>
                                            <tr class="border-bottom text-muted small" style="font-size: 0.75rem; font-weight: 600;">
                                                <th width="5%" class="text-center py-2">No</th>
                                                <th width="42%">Nama Dokumen <span class="text-danger">*</span></th>
                                                <th width="48%">Pilih Berkas (PDF / Word / Excel / ZIP) <span class="text-danger">*</span></th>
                                                <th width="5%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_tambah_dokumen">
                                            <!-- Dynamic Docs -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- MODAL FOOTER ACTION BAR -->
                <div class="modal-footer tender-modal-footer pt-3 pb-4 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-tender-cancel rounded-pill px-4 py-2" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-secondary rounded-pill px-3.5 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm btn-submit-tender-action" data-status="Draft">
                            <i class="bi bi-file-earmark-text fs-6"></i>
                            <span>Simpan sebagai Draft</span>
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm btn-submit-tender-action" data-status="Pending Approval" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border:none;">
                            <i class="bi bi-send-check-fill fs-6"></i>
                            <span>Simpan & Ajukan Approval 🚀</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let tenderItemIdxTambah = 0;
    let docIndexTambah = 0;

    function safeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str).replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function renumberTenderRows(prefix) {
        $(`#tbody_${prefix}_items tr.item-row`).each(function(idx) {
            $(this).find('.row-number').text(idx + 1);
        });
        $(`#badge_${prefix}_item_count`).text($(`#tbody_${prefix}_items tr.item-row`).length);
    }

    function renumberDocRows(prefix) {
        $(`#tbody_${prefix}_dokumen tr`).each(function(idx) {
            $(this).find('.doc-row-number').text(idx + 1);
        });
    }

    // Add Item Row Function (Matches PR/Standard Grid Layout)
    window.addTenderItemRowTambah = function(data = null) {
        tenderItemIdxTambah++;
        let uniqueId = tenderItemIdxTambah;

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
            <tr class="item-row" id="tnd_item_row_tambah_${uniqueId}" data-index="${uniqueId}">
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
                            <button type="button" class="btn btn-outline-primary btn-open-tender-barang-lookup" data-row="${uniqueId}" data-prefix="tambah" title="Cari Katalog">
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
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-del-tnd-item" data-id="${uniqueId}">
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
        $('#tbody_tambah_items').append($newRow);
        renumberTenderRows('tambah');
    };

    // Button "+ Tambah Barang" Click
    $(document).on('click', '#btn_tambah_tender_item_row', function(e) {
        e.preventDefault();
        window.addTenderItemRowTambah();
    });

    // Delete Item Row
    $(document).on('click', '.btn-del-tnd-item', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $(`#tnd_item_row_tambah_${id}`).remove();
        renumberTenderRows('tambah');
    });

    // Trigger Product Search Modal on magnifying glass or text click
    $(document).on('click', '.btn-open-tender-barang-lookup, .item-barang-name', function(e) {
        e.preventDefault();
        let row = $(this).closest('tr');
        let rowId = row.data('index');
        let prefix = $(this).data('prefix') || (row.closest('#tbody_edit_items').length ? 'edit' : 'tambah');

        $('#modalCariBarangTender').data('target-action', 'fill-row').data('target-prefix', prefix).data('target-row', rowId);
        $('#search_barang_tender_input').val('');
        $('.btn-kategori-tender-chip').removeClass('active');
        $('.btn-kategori-tender-chip[data-kategori-id="all"]').addClass('active');
        $('#table_lookup_barang_tender tbody tr').show();
        $('#modalCariBarangTender').modal('show');
    });

    // Format mask for Nilai Estimasi
    $(document).on('input', '#tambah_nilai_estimasi_mask', function() {
        let val = $(this).val().replace(/\D/g, '');
        let num = Number(val) || 0;
        $(this).val(num > 0 ? num.toLocaleString('id-ID') : '');
        $('#tambah_nilai_estimasi').val(num);
    });

    // Add Document Row
    $(document).on('click', '#btn_tambah_dokumen_row', function(e) {
        e.preventDefault();
        docIndexTambah++;
        let rowHtml = `
            <tr id="doc_row_tambah_${docIndexTambah}">
                <td class="text-center fw-semibold doc-row-number py-2"></td>
                <td>
                    <input type="text" name="document_names[]" class="form-control tender-input form-control-sm" placeholder="Contoh: RKS Teknis / Spesifikasi" maxlength="40" required>
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
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 btn-del-doc" data-id="${docIndexTambah}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tbody_tambah_dokumen').append(rowHtml);
        renumberDocRows('tambah');
    });

    $(document).on('click', '.btn-del-doc', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $(`#doc_row_tambah_${id}`).remove();
        renumberDocRows('tambah');
    });

    // Customer Lookup
    $(document).on('click', '.btn-lookup-customer', function() {
        $('#modalLookupCustomer').modal('show');
    });

    // Initial default row on modal show if empty
    $(document).on('show.bs.modal', '#modalTambahTender', function() {
        if ($('#tbody_tambah_items tr').length === 0) {
            window.addTenderItemRowTambah();
        }
    });

    // Handle Click on Action Buttons (Draft vs Direct Approval)
    $(document).on('click', '.btn-submit-tender-action', function(e) {
        e.preventDefault();
        let targetStatus = $(this).data('status') || 'Draft';
        $('#tambah_status').val(targetStatus);
        $('#formTambahTender').trigger('submit');
    });

    // Handle Form Submit
    $(document).on('submit', '#formTambahTender', function(e) {
        e.preventDefault();
        
        let form = this;
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Security / Character limit checks
        let namaTender = ($('#tambah_nama_tender').val() || '').trim();
        let nomorKontrak = ($('#tambah_nomor_kontrak').val() || '').trim();
        let nilaiMask = ($('#tambah_nilai_estimasi_mask').val() || '').trim();
        let catatan = ($('#tambah_catatan').val() || '').trim();

        if (namaTender.length > 50) {
            Swal.fire('Peringatan Karakter', 'Nama Proyek / Tender maksimal 50 karakter!', 'warning');
            $('#tambah-umum-tab').tab('show');
            $('#tambah_nama_tender').focus();
            return;
        }
        if (nomorKontrak.length > 40) {
            Swal.fire('Peringatan Karakter', 'Nomor Tender / Registrasi Proyek maksimal 40 karakter!', 'warning');
            $('#tambah-umum-tab').tab('show');
            $('#tambah_nomor_kontrak').focus();
            return;
        }
        if (nilaiMask.length > 20) {
            Swal.fire('Peringatan Karakter', 'Nilai Pagu Anggaran maksimal 20 karakter / digit!', 'warning');
            $('#tambah-umum-tab').tab('show');
            $('#tambah_nilai_estimasi_mask').focus();
            return;
        }
        if (catatan.length > 50) {
            Swal.fire('Peringatan Karakter', 'Catatan Tender maksimal 50 karakter!', 'warning');
            $('#tambah-umum-tab').tab('show');
            $('#tambah_catatan').focus();
            return;
        }

        let chosenStatus = $('#tambah_status').val() || 'Draft';
        let formData = new FormData(form);
        let actionBtns = $(this).find('.btn-submit-tender-action');
        let activeBtn = $(this).find(`.btn-submit-tender-action[data-status="${chosenStatus}"]`);
        let origBtnHtml = activeBtn.html();

        actionBtns.prop('disabled', true);
        activeBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

        $.ajax({
            url: "{{ route('tender.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                actionBtns.prop('disabled', false);
                activeBtn.html(origBtnHtml);

                if (response.success || response.status === 'success') {
                    $('#modalTambahTender').modal('hide');
                    form.reset();
                    $('#tambah_status').val('Draft');
                    $('#tbody_tambah_dokumen').empty();
                    $('#tbody_tambah_items').empty();
                    renumberTenderRows('tambah');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan!',
                        text: response.message || 'Prospek Tender berhasil diproses.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (typeof window.tenderTable !== 'undefined') {
                        window.tenderTable.ajax.reload(null, false);
                    }
                    if ($.fn.DataTable.isDataTable('#tableTender')) {
                        $('#tableTender').DataTable().ajax.reload(null, false);
                    }
                    if (typeof loadKanbanBoard === 'function') {
                        loadKanbanBoard();
                    }
                    if (typeof window.renderKanbanBoard === 'function') {
                        window.renderKanbanBoard();
                    }
                } else {
                    Swal.fire('Perhatian', response.message || 'Gagal menyimpan tender', 'warning');
                }
            },
            error: function(xhr) {
                actionBtns.prop('disabled', false);
                activeBtn.html(origBtnHtml);
                let err = xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat menyimpan tender.';
                Swal.fire('Error', err, 'error');
            }
        });
    });
</script>
@endpush
