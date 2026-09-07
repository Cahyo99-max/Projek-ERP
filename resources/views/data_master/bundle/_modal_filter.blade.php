<div class="modal fade" id="modalFilterBundle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content spk-filter-modal-content rounded-4 border overflow-hidden shadow-2xl">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header spk-filter-modal-header px-4 py-3.5 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="spk-filter-icon-box rounded-3 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bi bi-funnel-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold spk-filter-title mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Filter Data Bundle</h5>
                            <span class="spk-filter-badge font-monospace px-2 py-0.5 rounded-pill">FILTER STUDIO</span>
                        </div>
                        <small class="spk-filter-subtitle" style="font-size: 0.78rem;">Saring tampilan data paket bundle berdasarkan kriteria khusus.</small>
                    </div>
                </div>
                <button type="button" class="btn-close spk-filter-btn-close ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body spk-filter-modal-body p-3.5 p-md-4">
                <form id="formFilterBundle">
                    <div class="d-flex flex-column gap-3">
                        
                        {{-- STATUS FILTER (CARD SECTION) --}}
                        <div class="spk-filter-section p-3 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label font-monospace fw-bold small spk-filter-section-label mb-0">
                                    <i class="bi bi-shield-check me-1.5 text-primary"></i> STATUS BUNDLE
                                </label>
                                <span class="text-muted small" style="font-size: 10.5px;">Pilih status paket</span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap" id="statusChipGroup">
                                <button type="button" class="btn btn-sm spk-chip-btn active flex-fill" data-status="all">
                                    <i class="bi bi-grid-fill me-1"></i> Semua
                                </button>
                                <button type="button" class="btn btn-sm spk-chip-btn flex-fill" data-status="Active">
                                    <i class="bi bi-check-circle-fill me-1 text-success"></i> Aktif
                                </button>
                                <button type="button" class="btn btn-sm spk-chip-btn flex-fill" data-status="Inactive">
                                    <i class="bi bi-x-circle-fill me-1 text-danger"></i> Nonaktif
                                </button>
                            </div>
                            <input type="hidden" id="modal_filter_status" value="all">
                        </div>

                        {{-- KATEGORI FILTER (CARD SECTION) --}}
                        <div class="spk-filter-section p-3 rounded-3">
                            <label class="form-label font-monospace fw-bold small spk-filter-section-label mb-2 d-block">
                                <i class="bi bi-folder2-open me-1.5 text-info"></i> KATEGORI BARANG
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text spk-filter-input-addon"><i class="bi bi-tags"></i></span>
                                <select id="modal_filter_kategori" class="form-select form-select-sm spk-filter-select font-monospace py-2">
                                    <option value="all">&bull; Semua Kategori Produk &bull;</option>
                                    @foreach($kategoriOptions as $kat)
                                        <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- DUAL COLUMN: SATUAN & BRAND --}}
                        <div class="row g-2.5">
                            {{-- SATUAN DASAR --}}
                            <div class="col-12 col-sm-6">
                                <div class="spk-filter-section p-3 rounded-3 h-100">
                                    <label class="form-label font-monospace fw-bold small spk-filter-section-label mb-2 d-block">
                                        <i class="bi bi-rulers me-1.5 text-warning"></i> SATUAN DASAR
                                    </label>
                                    <select id="modal_filter_satuan" class="form-select form-select-sm spk-filter-select font-monospace py-2">
                                        <option value="all">Semua Satuan</option>
                                        @foreach($satuans as $sat)
                                            <option value="{{ $sat->id }}">{{ $sat->nama_satuan }} ({{ $sat->singkatan }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- BRAND / MERK --}}
                            <div class="col-12 col-sm-6">
                                <div class="spk-filter-section p-3 rounded-3 h-100">
                                    <label class="form-label font-monospace fw-bold small spk-filter-section-label mb-2 d-block">
                                        <i class="bi bi-award me-1.5 text-success"></i> BRAND / MERK
                                    </label>
                                    <select id="modal_filter_brand" class="form-select form-select-sm spk-filter-select font-monospace py-2">
                                        <option value="all">Semua Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->nama_brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer spk-filter-modal-footer px-4 py-3 border-top justify-content-between">
                <button type="button" class="btn btn-sm spk-filter-btn-reset rounded-pill px-3.5 py-2 font-monospace" id="btnResetFilterModal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Default
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm spk-filter-btn-cancel rounded-pill px-3.5 py-2 font-monospace" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" class="btn btn-sm spk-filter-btn-apply rounded-pill px-4 py-2 font-monospace fw-bold shadow-sm" id="btnApplyFilterModal">
                        <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle Interactive Status Chip Selection
    $(document).on('click', '#statusChipGroup .spk-chip-btn', function() {
        $('#statusChipGroup .spk-chip-btn').removeClass('active');
        $(this).addClass('active');
        const statusVal = $(this).data('status');
        $('#modal_filter_status').val(statusVal);
    });

    // Sync chips when reset
    $(document).on('click', '#btnResetFilterModal, #btnQuickResetFilter', function() {
        $('#statusChipGroup .spk-chip-btn').removeClass('active');
        $('#statusChipGroup .spk-chip-btn[data-status="all"]').addClass('active');
        $('#modal_filter_status').val('all');
    });
</script>
@endpush
