<!-- Modal Lookup / Pencarian Faktur Penjualan Belum Lunas -->
<div class="modal fade" id="modalLookupSalesInvoice" tabindex="-1" aria-labelledby="modalLookupSalesInvoiceLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px; width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                    <div class="pay-card-icon text-white d-inline-flex shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-0.5 flex-wrap">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-sm-5 text-truncate" id="modalLookupSalesInvoiceLabel">
                                <span class="d-none d-sm-inline">Pencarian Faktur Penjualan (Piutang)</span>
                                <span class="d-inline d-sm-none">Cari Faktur Piutang</span>
                            </h5>
                            @if(session('active_branch_id'))
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 text-truncate font-monospace" style="font-size: 0.7rem; max-width: 170px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ session('active_branch_name', 'Cabang Aktif') }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 text-truncate font-monospace" style="font-size: 0.7rem; max-width: 170px;">
                                    <i class="bi bi-building me-1"></i>{{ session('active_company_name', 'PT Pusat') }}
                                </span>
                            @endif
                        </div>
                        <small class="text-body-secondary d-block text-truncate" style="font-size: 0.75rem;">Pilih faktur untuk memuat data customer &amp; sisa piutang otomatis</small>
                    </div>
                </div>
                <button type="button" class="pay-btn-close flex-shrink-0 ms-2" data-bs-dismiss="modal" aria-label="Close" title="Tutup">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-sm-4 bg-body">
                <!-- Filter Bar -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 mb-3 bg-body-tertiary pay-form-card">
                    <div class="card-body p-2.5 p-sm-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-end-0 text-success"><i class="bi bi-search"></i></span>
                                    <input type="text" id="lookup_search_query" class="form-control border-start-0 bg-body" placeholder="Cari no. faktur / customer...">
                                </div>
                            </div>
                            <div class="col-8 col-md-4">
                                <select id="lookup_customer_filter" class="form-select bg-body">
                                    <option value="">-- Semua Customer / PT --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ !empty($c->nama_perusahaan) ? $c->nama_perusahaan : $c->nama_customer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 col-md-3">
                                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill d-flex align-items-center justify-content-center gap-1.5 shadow-2xs" id="btn_refresh_lookup" style="font-size: 0.82rem;">
                                    <i class="bi bi-arrow-clockwise"></i> <span>Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 overflow-hidden pay-form-card">
                    <!-- Loading state -->
                    <div class="text-center py-5 d-none" id="lookup_loading_state">
                        <div class="spinner-border text-success" role="status" style="width: 2.2rem; height: 2.2rem;">
                            <span class="visually-hidden">Memuat data...</span>
                        </div>
                        <p class="text-body-secondary small mt-2 mb-0">Memuat daftar faktur piutang...</p>
                    </div>

                    <!-- Empty state -->
                    <div class="text-center py-5 d-none" id="lookup_empty_state">
                        <div class="p-3 bg-secondary-subtle text-secondary rounded-circle d-inline-flex mb-2">
                            <i class="bi bi-inbox fs-3"></i>
                        </div>
                        <h6 class="fw-semibold text-body-emphasis mb-1">Tidak Ada Faktur Belum Lunas</h6>
                        <p class="text-body-secondary small mb-0">Tidak ditemukan faktur yang sesuai dengan kriteria pencarian.</p>
                    </div>

                    <div class="table-responsive" style="max-height: 440px; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;" id="lookup_table_wrapper">
                        <!-- Mobile Swipe Hint Banner -->
                        <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 m-2 rounded-3 pay-mobile-swipe-banner shadow-2xs">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left-right text-success"></i>
                                <span class="fw-semibold small">Geser tabel untuk memilih faktur</span>
                            </div>
                            <span class="badge rounded-pill px-2.5 py-1 fw-bold pay-swipe-pill">Swipe &rarr;</span>
                        </div>

                        <table class="table table-hover align-middle mb-0 w-100" id="table_lookup_invoices" style="min-width: 860px;">
                            <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.74rem; position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th class="ps-3 py-2.5" style="min-width: 170px;">No. Faktur</th>
                                    <th style="min-width: 200px;">Customer / Nama PT</th>
                                    <th style="min-width: 105px;">Tanggal</th>
                                    <th style="min-width: 110px;">Jatuh Tempo</th>
                                    <th class="text-end" style="min-width: 130px;">Total Faktur</th>
                                    <th class="text-end" style="min-width: 130px;">Sisa Piutang</th>
                                    <th class="text-center" style="min-width: 90px;">Status</th>
                                    <th class="text-center pe-3" style="min-width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="lookup_invoices_tbody">
                                <!-- Rendered dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-body-secondary font-monospace" id="lookup_count_info" style="font-size: 0.78rem;">Menampilkan 0 faktur</small>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
