<!-- Modal Riwayat Mutasi Rekening Kas & Bank (Klik Kartu) -->
<div class="modal modal-blur fade" id="modalCardMutasi" tabindex="-1" aria-labelledby="modalCardMutasiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable kb-modal-wide">
        <div class="modal-content border-0 shadow-lg kb-modal-glass-container">
            
            <!-- Hidden inputs to store active account ID and filter tipe -->
            <input type="hidden" id="card_mutasi_acc_id" value="">
            <input type="hidden" id="card_mutasi_filter_tipe" value="">

            <!-- Modal Header (Sleek Ultra-Modern Fintech Bento Header) -->
            <div class="modal-header p-3.5 p-md-4 kb-modal-hero-header border-bottom d-block">
                
                <!-- Row 1: Account Title, Badges & Close Button -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="cardMutasiIconBadge" style="width: 48px; height: 48px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                            <i class="bi bi-bank2 fs-4" id="cardMutasiIcon"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <h4 class="modal-title fw-bold text-body-emphasis mb-0" id="cardMutasiNamaAkun" style="letter-spacing: -0.3px;">
                                    Nama Rekening
                                </h4>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace d-inline-flex align-items-center gap-1 px-2.5 py-0.5" style="font-size: 0.72rem;">
                                    <span class="kb-pulse-dot"></span>
                                    <span>Aktif</span>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-2.5 py-0.5" id="cardMutasiEntitas" style="font-size: 0.7rem;">
                                    <i class="bi bi-building me-1"></i> PT Pusat
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2.5 py-0.5" id="cardMutasiTipe" style="font-size: 0.7rem;">
                                    Bank
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                        <a href="#" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm text-nowrap" id="cardMutasiLedgerLink" target="_blank">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <span class="d-none d-sm-inline">Buka di Buku kas</span>
                        </a>
                        <button type="button" class="btn-close text-reset shadow-none p-2 ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Row 2: Bento Strip Info Box (3 Columns Responsive) -->
                <div class="p-3 p-md-3.5 kb-bento-strip">
                    <div class="row g-3 align-items-center">
                        
                        <!-- Col 1: Nomor Rekening & Copy -->
                        <div class="col-12 col-md-4 border-end-md">
                            <div class="text-muted small fw-semibold text-uppercase font-monospace mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                <i class="bi bi-credit-card me-1 text-primary"></i> NOMOR REKENING / AKUN
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-monospace fw-bold text-body-emphasis fs-6" id="cardMutasiNoRek">-</span>
                                <button type="button" class="btn btn-xs rounded-pill px-2 py-0.5 kb-chip-copy" id="btnCopyRekening" title="Salin Nomor Rekening">
                                    <i class="bi bi-clipboard text-success" id="btnCopyRekeningIcon"></i>
                                    <span class="small" style="font-size: 0.7rem;">Salin</span>
                                </button>
                            </div>
                        </div>

                        <!-- Col 2: Integrasi COA & Atas Nama -->
                        <div class="col-12 col-md-4 border-end-md">
                            <div class="text-muted small fw-semibold text-uppercase font-monospace mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                <i class="bi bi-journal-code me-1 text-info"></i> COA AKUNTANSI TERINTEGRASI
                            </div>
                            <div class="font-monospace fw-semibold text-success small text-truncate" id="cardMutasiCoa" title="Kode dan Nama COA">
                                -
                            </div>
                            <div class="text-muted small mt-0.5 text-truncate" id="cardMutasiAtasNama">
                                <span class="fst-italic" style="font-size: 0.75rem;">Atas Nama: -</span>
                            </div>
                        </div>

                        <!-- Col 3: Saldo Berjalan (Live Running Balance Box) -->
                        <div class="col-12 col-md-4 text-md-end d-flex flex-row flex-md-column justify-content-between align-items-center align-items-md-end">
                            <div class="text-muted small fw-bold text-uppercase font-monospace mb-0.5" id="cardMutasiSaldoLabel" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                                SALDO BERJALAN SAAT INI
                            </div>
                            <div class="font-monospace fw-bold text-success text-nowrap" id="cardMutasiSaldo" style="font-size: 1.55rem; letter-spacing: -0.5px;">
                                Rp 0
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-0 kb-modal-body">
                
                <!-- Quick Filter Tabs & Action Toolbar -->
                <div class="px-3.5 px-md-4 py-2.5 border-bottom bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    
                    <!-- Quick Filter Interactive Nav Pills -->
                    <ul class="nav nav-pills kb-quick-filter-pills gap-1" id="quickFilterPills">
                        <li class="nav-item">
                            <button type="button" class="nav-link active btn-quick-tipe" data-tipe="">
                                <i class="bi bi-list-ul me-1"></i> Semua Mutasi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link btn-quick-tipe" data-tipe="Masuk">
                                <i class="bi bi-arrow-down-left text-success me-1"></i> Kas Masuk
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link btn-quick-tipe" data-tipe="Keluar">
                                <i class="bi bi-arrow-up-right text-danger me-1"></i> Kas Keluar
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link btn-quick-tipe" data-tipe="Transfer">
                                <i class="bi bi-arrow-left-right text-info me-1"></i> Transfer
                            </button>
                        </li>
                    </ul>

                    <!-- Controls: Tanggal Filter & Reset -->
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <!-- Geser Tabel Mobile Indicator -->
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace small d-inline-flex d-md-none align-items-center gap-1" style="font-size: 0.68rem;">
                            <i class="bi bi-arrow-left-right"></i> Geser Tabel &raquo;
                        </span>

                        <!-- Filter Button -->
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" id="btn_open_card_filter">
                            <i class="bi bi-calendar-range text-success"></i>
                            <span>Filter Tanggal</span>
                            <span class="badge bg-success text-white rounded-pill font-monospace d-none" id="card_filter_count_badge" style="font-size: 0.65rem;">1 Filter</span>
                        </button>

                        <!-- Reset Filter Button (Shown when filters active) -->
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 fw-semibold d-none align-items-center gap-1 shadow-sm" id="btn_card_mutasi_reset" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                            <span class="d-none d-sm-inline">Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Table Mutasi Rekening (Scrollable Horizontal & Vertical managed by DataTables DOM) -->
                <table class="table table-hover align-middle mb-0 w-100" id="tableCardMutasi">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="min-width: 140px;">No. Mutasi</th>
                            <th style="min-width: 110px;">Tanggal</th>
                            <th style="min-width: 260px;">Kontak / Keterangan</th>
                            <th class="text-end" style="min-width: 150px;">Jumlah (Rp)</th>
                            <th class="text-end" style="min-width: 150px;">Saldo Akhir</th>
                            <th class="text-center" style="min-width: 110px;">Status</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Server-side populated via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top bg-body-tertiary">
                <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.78rem;">
                    <i class="bi bi-shield-check text-success fs-6"></i>
                    <span>Mutasi terurut otomatis dari transaksi paling baru ke terdahulu.</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Sub-Modal: Filter Data Riwayat Mutasi -->
<div class="modal modal-blur fade" id="modalFilterMutasiCard" tabindex="-1" aria-labelledby="modalFilterMutasiCardTitle" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-glass-container">
            <div class="modal-header px-3.5 py-3 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-funnel-fill fs-6"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalFilterMutasiCardTitle">Filter Tanggal Mutasi</h6>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 kb-modal-body">
                <div class="row g-3">
                    
                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Dari Tanggal</label>
                        <input type="date" class="form-control kb-form-control font-monospace" id="card_mutasi_start_date">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Sampai Tanggal</label>
                        <input type="date" class="form-control kb-form-control font-monospace" id="card_mutasi_end_date">
                    </div>

                    <!-- Quick Preset Pills -->
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">Pilihan Cepat:</label>
                        <div class="d-flex gap-1.5 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill px-2.5 py-1 btn-card-preset-filter" data-preset="today" style="font-size: 0.72rem;">Hari Ini</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill px-2.5 py-1 btn-card-preset-filter" data-preset="this_month" style="font-size: 0.72rem;">Bulan Ini</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs rounded-pill px-2.5 py-1 btn-card-preset-filter" data-preset="all" style="font-size: 0.72rem;">Semua Waktu</button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer px-3.5 py-2.5 d-flex justify-content-between align-items-center kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold" id="btn_modal_filter_reset">Reset</button>
                <button type="button" class="btn btn-success btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1" id="btn_modal_filter_apply" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none;">
                    <i class="bi bi-check2"></i>
                    <span>Terapkan</span>
                </button>
            </div>
        </div>
    </div>
</div>
