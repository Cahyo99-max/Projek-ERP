<!-- Modal Detail Alokasi Biaya (View) -->
<div class="modal fade" id="modalViewAlokasi" tabindex="-1" aria-labelledby="modalViewAlokasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-alokasi modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content erp-card border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden; overflow-x: hidden !important; max-width: 100%; min-width: 0;">
            
            <!-- GRADIENT MODAL HEADER -->
            <div class="modal-header border-0 px-3 px-md-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                    <div class="p-2 p-md-2.5 rounded-3 shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); width: 40px; height: 40px;">
                        <i class="bi bi-file-earmark-text-fill fs-5 text-info"></i>
                    </div>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6 fs-md-5 text-truncate" id="modalViewAlokasiLabel">Rincian Dokumen Alokasi Biaya</h5>
                        <small class="d-block text-truncate" style="color: rgba(255, 255, 255, 0.75); font-size: 0.78rem;" id="viewSubTitle">Nomor Alokasi: -</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL BODY -->
            <div class="modal-body p-2.5 p-md-4" style="background-color: var(--erp-bg); overflow-x: hidden !important; max-width: 100%; min-width: 0; touch-action: pan-y;">
                <!-- Info Header Box -->
                <div class="card border mb-3 shadow-sm" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; border-radius: 0.95rem;">
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Nomor Alokasi</span>
                                <span class="fw-bold font-monospace fs-6 text-primary" id="viewNomorAlokasi">-</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Tanggal Alokasi</span>
                                <span class="fw-semibold" style="color: var(--erp-text-main);" id="viewTanggalAlokasi">-</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Status Dokumen</span>
                                <span id="viewStatusBadge">-</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Dibuat Oleh</span>
                                <span class="fw-semibold" style="color: var(--erp-text-main);" id="viewCreatedBy">-</span>
                            </div>

                            <div class="col-12"><hr class="my-1" style="border-color: var(--erp-border);"></div>

                            <div class="col-12 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">PO Jasa Ekspedisi</span>
                                <span class="fw-bold font-monospace" style="color: var(--erp-text-main);" id="viewPoEkspedisi">-</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Vendor / Ekspedisi</span>
                                <span class="fw-semibold" style="color: var(--erp-text-main);" id="viewVendorEkspedisi">-</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Proyek / Tender Terkait</span>
                                <span id="viewTender">-</span>
                            </div>
                            <div class="col-12 col-md-3">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Total Biaya Masuk HPP (DPP)</span>
                                <span class="fw-bold fs-6 text-success font-monospace" id="viewTotalBiaya">-</span>
                                <div id="viewPphNote"></div>
                            </div>

                            <div class="col-12 col-md-4">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Metode Alokasi</span>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" id="viewMetodeAlokasi">-</span>
                            </div>
                            <div class="col-12 col-md-8">
                                <span class="small d-block fw-semibold" style="color: var(--erp-text-muted);">Keterangan</span>
                                <span style="color: var(--erp-text-main);" id="viewKeterangan">-</span>
                            </div>

                            <div class="col-12" id="viewAlasanBatalContainer" style="display: none;">
                                <div class="p-2.5 rounded-3 bg-danger-subtle border border-danger-subtle text-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                    <div>
                                        <span class="small fw-bold d-block">Alasan Pembatalan:</span>
                                        <span class="fw-semibold" id="viewAlasanBatal">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Detail Items -->
                <div class="card border mb-3 shadow-sm overflow-hidden" style="background-color: var(--erp-card-bg); border-color: var(--erp-border) !important; border-radius: 0.95rem;">
                    <div class="card-header border-0 py-2.5 px-3 px-md-4 d-flex align-items-center justify-content-between" style="background-color: var(--erp-table-header);">
                        <h6 class="fw-bold mb-0 small text-uppercase" style="color: var(--erp-text-muted); letter-spacing: 0.5px;">
                            <i class="bi bi-boxes me-1 text-primary"></i> Daftar Item Barang &amp; Kapitalisasi HPP
                        </h6>
                    </div>

                    <!-- Mobile swipe hint -->
                    <div class="d-flex d-md-none align-items-center gap-1.5 text-muted small px-3 pt-2 pb-1">
                        <i class="bi bi-arrows-expand-vertical text-primary"></i>
                        <span>Geser tabel ke samping untuk melihat seluruh data</span>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; overflow-x: auto !important; width: 100%; max-width: 100%; min-width: 0; -webkit-overflow-scrolling: touch; touch-action: pan-x pan-y; overscroll-behavior-x: contain;">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0" style="border-color: var(--erp-border);">
                            <thead class="sticky-top" style="background-color: var(--erp-table-header);">
                                <tr class="small text-center align-middle" style="color: var(--erp-text-muted); font-size: 0.74rem;">
                                    <th rowspan="2" class="text-nowrap" style="width: 38px; min-width: 38px; background-color: var(--erp-table-header);">#</th>
                                    <th rowspan="2" class="text-nowrap" style="min-width: 125px; background-color: var(--erp-table-header);">No. GR</th>
                                    <th rowspan="2" class="text-nowrap" style="background-color: var(--erp-table-header); min-width: 220px;">Barang</th>
                                    <th rowspan="2" class="text-end text-nowrap" style="min-width: 105px; background-color: var(--erp-table-header);">Qty Diterima</th>
                                    <th rowspan="2" class="text-end text-nowrap" style="min-width: 130px; background-color: var(--erp-table-header);">Nilai Beli (Rp)</th>
                                    <th rowspan="2" class="text-end text-nowrap" style="min-width: 85px; background-color: var(--erp-table-header);">Porsi (%)</th>
                                    <th colspan="2" class="text-center fw-bold text-nowrap" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border-bottom: 2px solid #f59e0b;">Alokasi Biaya Ekspedisi</th>
                                    <th colspan="2" class="text-center fw-bold text-nowrap" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border-bottom: 2px solid #10b981;">Hasil HPP Persediaan</th>
                                </tr>
                                <tr class="small align-middle" style="color: var(--erp-text-muted); font-size: 0.73rem;">
                                    <th class="text-end text-nowrap" style="min-width: 125px; background: rgba(245, 158, 11, 0.08); color: #f59e0b;">Total (Rp)</th>
                                    <th class="text-end text-nowrap" style="min-width: 110px; background: rgba(245, 158, 11, 0.08); color: #f59e0b;">Per Unit (Rp)</th>
                                    <th class="text-end text-nowrap" style="min-width: 120px; background: rgba(16, 185, 129, 0.08); color: #10b981;">HPP Awal (Rp)</th>
                                    <th class="text-end fw-bold text-nowrap" style="min-width: 130px; background: rgba(16, 185, 129, 0.18); color: #10b981;">HPP Baru (Rp)</th>
                                </tr>
                            </thead>
                            <tbody id="viewTbodyDetails">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Jurnal Info Alert -->
                <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-0 py-2.5 px-3 rounded-3" id="viewJurnalAlert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
                        <div class="small">
                            Dokumen ini <b>Posted</b>: Nilai persediaan di modul stok gudang telah diperbarui otomatis dan jurnal kapitalisasi HPP telah dicatat ke buku besar akuntansi.
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 shadow-sm fw-semibold d-inline-flex align-items-center gap-1.5 btn-open-jurnal" id="btnViewJurnalAlert">
                        <i class="bi bi-journal-text"></i> Lihat Jurnal
                    </button>
                </div>
            </div>

            <div class="modal-footer border-0 px-3 px-md-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2" style="background-color: var(--erp-card-bg); border-top: 1px solid var(--erp-border) !important;">
                <button type="button" class="btn btn-outline-secondary px-3.5 py-2 rounded-pill w-100 w-sm-auto order-2 order-sm-1" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-sm-auto order-1 order-sm-2" id="viewActionContainer">
                    <!-- Dynamic Buttons: Lihat Jurnal / Post / Cancel -->
                </div>
            </div>
        </div>
    </div>
</div>
