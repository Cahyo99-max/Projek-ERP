<!-- Modal Lookup / Pencarian Faktur Penjualan Belum Lunas -->
<div class="modal fade" id="modalLookupSalesInvoice" tabindex="-1" aria-labelledby="modalLookupSalesInvoiceLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalLookupSalesInvoiceLabel">Pencarian Faktur Penjualan (Piutang)</h5>
                        <small class="text-body-secondary">Pilih faktur untuk menarik Nama PT / Customer dan nominal tagihan secara otomatis</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(session('active_branch_id'))
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-geo-alt-fill me-1"></i>Cabang: {{ session('active_branch_name', 'Cabang Aktif') }}
                        </span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.72rem;">
                            <i class="bi bi-building me-1"></i>Entitas: {{ session('active_company_name', 'PT Pusat') }}
                        </span>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-body">
                <!-- Filter Bar -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 mb-3 bg-body-tertiary">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-end-0 text-body-secondary"><i class="bi bi-search"></i></span>
                                    <input type="text" id="lookup_search_query" class="form-control border-start-0 bg-body" placeholder="Cari No. Faktur / Nama PT / Customer...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="lookup_customer_filter" class="form-select bg-body">
                                    <option value="">-- Semua Customer / PT --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill d-flex align-items-center justify-content-center gap-1.5" id="btn_refresh_lookup">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card border border-body-tertiary shadow-sm rounded-4 overflow-hidden">
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

                    <div class="table-responsive" style="max-height: 420px;" id="lookup_table_wrapper">
                        <table class="table table-hover align-middle mb-0" id="table_lookup_invoices">
                            <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem; position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th class="ps-3 py-2.5">No. Faktur</th>
                                    <th>Customer / Nama PT</th>
                                    <th>Tanggal</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-end">Total Faktur</th>
                                    <th class="text-end">Sisa Piutang</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center pe-3" style="width: 110px;">Aksi</th>
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
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-4 d-flex justify-content-between align-items-center">
                <small class="text-body-secondary" id="lookup_count_info">Menampilkan 0 faktur</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
