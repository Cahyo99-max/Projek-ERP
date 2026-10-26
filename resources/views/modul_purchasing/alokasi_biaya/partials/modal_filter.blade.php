<!-- Modal Filter Alokasi Biaya -->
<div class="modal fade" id="modalFilterAlokasi" tabindex="-1" aria-labelledby="modalFilterAlokasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content erp-card border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            
            <!-- GRADIENT MODAL HEADER -->
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 shadow-sm d-flex align-items-center justify-content-center" 
                         style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); width: 42px; height: 42px;">
                        <i class="bi bi-funnel-fill fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="modalFilterAlokasiLabel">Filter Dokumen Alokasi Biaya</h6>
                        <small style="color: rgba(255, 255, 255, 0.75); font-size: 0.78rem;">Saring data berdasarkan status, metode, dan rentang tanggal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- MODAL FORM -->
            <form action="{{ route('alokasi-biaya.index') }}" method="GET" id="formFilterAlokasi">
                <!-- Keep search and pagination query if exists -->
                <input type="hidden" name="search" id="filter_search_hidden" value="{{ request('search') }}">
                <input type="hidden" name="per_page" id="filter_per_page_hidden" value="{{ request('per_page', 10) }}">

                <div class="modal-body p-4" style="background-color: var(--erp-bg);">
                    
                    <!-- 1. STATUS DOKUMEN -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase mb-2" style="color: var(--erp-text-muted); font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-tag-fill text-primary me-1"></i> Status Dokumen
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_all" value="" {{ !request('status') ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 filter-status-pill" for="status_all">
                                    <span class="small fw-semibold">Semua Status</span>
                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem; opacity: 0.5;"></i>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_posted" value="Posted" {{ request('status') === 'Posted' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 filter-status-pill" for="status_posted">
                                    <span class="small fw-semibold"><i class="bi bi-check-circle-fill me-1.5 text-success"></i> Posted</span>
                                    <i class="bi bi-check-circle text-success small"></i>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_draft" value="Draft" {{ request('status') === 'Draft' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 filter-status-pill" for="status_draft">
                                    <span class="small fw-semibold"><i class="bi bi-clock-fill me-1.5 text-warning"></i> Draft</span>
                                    <i class="bi bi-hourglass-split text-warning small"></i>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="status" id="status_batal" value="Batal" {{ request('status') === 'Batal' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger w-100 py-2 text-start d-flex align-items-center justify-content-between rounded-3 filter-status-pill" for="status_batal">
                                    <span class="small fw-semibold"><i class="bi bi-slash-circle me-1.5 text-danger"></i> Batal</span>
                                    <i class="bi bi-x-circle text-danger small"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. METODE ALOKASI -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase mb-2" style="color: var(--erp-text-muted); font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-calculator text-primary me-1"></i> Metode Alokasi
                        </label>
                        <select name="metode" class="form-select fw-semibold">
                            <option value="">Semua Metode Alokasi</option>
                            <option value="Berdasarkan Nilai Barang" {{ request('metode') === 'Berdasarkan Nilai Barang' ? 'selected' : '' }}>Berdasarkan Nilai Barang (Proporsional Harga)</option>
                            <option value="Berdasarkan Qty" {{ request('metode') === 'Berdasarkan Qty' ? 'selected' : '' }}>Berdasarkan Kuantitas (Bagi Rata per Qty)</option>
                        </select>
                    </div>

                    <!-- 3. RENTANG TANGGAL -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold small text-uppercase mb-0" style="color: var(--erp-text-muted); font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="bi bi-calendar3 text-primary me-1"></i> Rentang Tanggal Alokasi
                            </label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-preset-date" data-preset="today" style="font-size: 0.72rem;">Hari Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-preset-date" data-preset="this_month" style="font-size: 0.72rem;">Bulan Ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-preset-date" data-preset="last_month" style="font-size: 0.72rem;">Bulan Lalu</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-preset-date" data-preset="clear" style="font-size: 0.72rem;">Hapus</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1" style="color: var(--erp-text-muted);">Dari Tanggal</label>
                                <input type="date" name="start_date" id="filter_start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1" style="color: var(--erp-text-muted);">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="filter_end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- MODAL FOOTER -->
                <div class="modal-footer border-0 px-4 py-3 d-flex justify-content-between" style="background-color: var(--erp-card-bg); border-top: 1px solid var(--erp-border) !important;">
                    <a href="{{ route('alokasi-biaya.index', ['search' => request('search')]) }}" class="btn btn-outline-secondary px-3 rounded-pill fw-semibold small">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1.5"
                            style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-check2-circle fs-6"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
