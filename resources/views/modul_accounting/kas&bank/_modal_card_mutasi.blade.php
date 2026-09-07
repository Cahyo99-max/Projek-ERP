<!-- Modal Riwayat Mutasi Rekening Kas & Bank (Klik Kartu) -->
<div class="modal modal-blur fade" id="modalCardMutasi" tabindex="-1" aria-labelledby="modalCardMutasiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <!-- Hidden input to store active account ID -->
            <input type="hidden" id="card_mutasi_acc_id" value="">

            <!-- Modal Header (Spacious, Fintech-grade Layout) -->
            <div class="modal-header p-3.5 p-md-4 kb-modal-header border-bottom d-block">
                <!-- Row 1: Account Title, Entity Badge & Close Button -->
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-1 text-body-emphasis" id="cardMutasiNamaAkun" style="font-size: 1.15rem; line-height: 1.2;">Nama Rekening</h5>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" id="cardMutasiEntitas" style="font-size: 0.68rem;">PT Pusat</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" id="cardMutasiTipe" style="font-size: 0.68rem;">Bank</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close text-reset shadow-none ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Row 2: Account Details & Live Balance Card (Clean 2-Column Responsive Bento Strip) -->
                <div class="p-3 rounded-4 border bg-body-tertiary shadow-sm">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-7">
                            <div class="d-flex align-items-center gap-2 mb-1 text-truncate">
                                <i class="bi bi-credit-card text-muted"></i>
                                <span class="small text-muted font-monospace">No. Rek:</span>
                                <span class="small font-monospace fw-bold text-body text-truncate" id="cardMutasiNoRek">-</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <i class="bi bi-journal-bookmark text-muted"></i>
                                <span class="small text-muted font-monospace">COA:</span>
                                <span class="small font-monospace fw-bold text-success text-truncate" id="cardMutasiCoa">-</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-5 text-sm-end pt-2 pt-sm-0 border-top border-sm-top-0 d-flex flex-row flex-sm-column justify-content-between align-items-center align-items-sm-end">
                            <div class="text-muted small mb-0.5 font-monospace" style="font-size: 0.7rem; letter-spacing: 0.5px;">SALDO BERJALAN</div>
                            <div class="font-monospace fw-bold text-success fs-5 text-nowrap" id="cardMutasiSaldo">
                                Rp 0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-0 kb-modal-body">
                
                <!-- Action Toolbar: Filter Button & Links -->
                <div class="px-3.5 px-md-4 py-2.5 border-bottom bg-body-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-clock-history text-success"></i>
                        <span class="fw-bold text-body-emphasis small">Riwayat Transaksi</span>
                        <span class="badge bg-success text-white rounded-pill font-monospace d-none" id="card_filter_count_badge" style="font-size: 0.65rem;">1 Filter</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- Geser Tabel Mobile Indicator -->
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace small d-inline-flex d-md-none align-items-center gap-1" style="font-size: 0.68rem;">
                            <i class="bi bi-arrow-left-right"></i> Geser Tabel &raquo;
                        </span>

                        <!-- Filter Button -->
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" id="btn_open_card_filter">
                            <i class="bi bi-funnel-fill text-success"></i>
                            <span>Filter Data</span>
                        </button>

                        <!-- Reset Filter Button (Shown when filters active) -->
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5 fw-semibold d-none align-items-center gap-1 shadow-sm" id="btn_card_mutasi_reset" title="Reset Filter">
                            <i class="bi bi-x-circle"></i>
                            <span class="d-none d-sm-inline">Reset</span>
                        </button>

                        <!-- Ledger Page Link -->
                        <a href="#" class="btn btn-success btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm text-nowrap" id="cardMutasiLedgerLink" target="_blank" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none;">
                            <i class="bi bi-book"></i>
                            <span class="d-none d-sm-inline">Buku Besar</span>
                        </a>
                    </div>
                </div>

                <!-- Table Mutasi Rekening (Scrollable Horizontal & Vertical) -->
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto; scrollbar-width: thin;">
                    <table class="table table-hover align-middle mb-0 w-100" id="tableCardMutasi">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);">
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>No. Mutasi</th>
                                <th>Tanggal</th>
                                <th>Kontak / Keterangan</th>
                                <th class="text-end">Jumlah (Rp)</th>
                                <th class="text-end">Saldo Akhir</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 75px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Server-side populated via AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-3.5 px-md-4 py-2.5 d-flex justify-content-between align-items-center flex-wrap gap-2 kb-modal-footer border-top">
                <span class="text-muted small" style="font-size: 0.76rem;">
                    <i class="bi bi-info-circle me-1"></i> Riwayat mutasi terfilter khusus untuk rekening ini.
                </span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<!-- Sub-Modal: Filter Data Riwayat Mutasi -->
<div class="modal modal-blur fade" id="modalFilterMutasiCard" tabindex="-1" aria-labelledby="modalFilterMutasiCardTitle" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            <div class="modal-header px-3.5 py-3 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-funnel-fill fs-6"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalFilterMutasiCardTitle">Filter Mutasi</h6>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 kb-modal-body">
                <div class="row g-3">
                    
                    <div class="col-12">
                        <label class="form-label small fw-bold text-body-emphasis mb-1">Tipe Mutasi</label>
                        <select class="form-select kb-form-control" id="card_mutasi_filter_tipe">
                            <option value="">Semua Mutasi</option>
                            <option value="Masuk">Kas Masuk (Inflow)</option>
                            <option value="Keluar">Kas Keluar (Outflow)</option>
                            <option value="Transfer">Transfer Antar Rekening</option>
                        </select>
                    </div>

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
