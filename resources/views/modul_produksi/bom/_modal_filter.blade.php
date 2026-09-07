{{-- MODAL FILTER LANJUTAN DATA BOM --}}
<div class="modal fade bom-modal-primary" id="modalFilterBom" tabindex="-1" aria-labelledby="modalFilterBomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bom-modal-content border-0 shadow-lg">
            <div class="modal-header bom-modal-header-blue">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-25 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-funnel-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalFilterBomLabel">Filter Lanjutan Master BOM</h5>
                        <small class="text-white-50">Filter resep berdasarkan status, tipe, dan rentang HPP</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body bom-modal-body p-4">
                <form id="formFilterBom">
                    {{-- Periode Bulan Pembuatan --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                            <i class="bi bi-calendar-month me-1 text-primary"></i> Periode Bulan Pembuatan
                        </label>
                        <input type="month" id="filterPeriodeBomModal" class="form-control rounded-3">
                        <small class="text-theme-muted" style="font-size: 11px;">Kosongkan jika ingin melihat seluruh periode (All-Time).</small>
                    </div>

                    {{-- Status BOM --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                            <i class="bi bi-toggle-on me-1 text-primary"></i> Status Resep BOM
                        </label>
                        <select id="filterStatusBom" class="form-select rounded-3">
                            <option value="">-- Semua Status --</option>
                            <option value="Draft">Draft (Hanya Pembuat)</option>
                            <option value="Pending">Pending (Menunggu Approval)</option>
                            <option value="Approved">Approved (Disetujui)</option>
                            <option value="Completed">Selesai (Completed)</option>
                            <option value="Rejected">Rejected (Ditolak)</option>
                            <option value="Inactive">Inactive (Non-Aktif)</option>
                        </select>
                    </div>

                    {{-- Tipe Produksi --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                            <i class="bi bi-tags-fill me-1 text-primary"></i> Tipe Produksi Garmen
                        </label>
                        <select id="filterTipeBom" class="form-select rounded-3">
                            <option value="">-- Semua Tipe Produksi --</option>
                            <option value="Stok (Make to Stock)">Stok (Make to Stock)</option>
                            <option value="Tender / Project">Tender / Project (Make to Order)</option>
                            <option value="Garmen">Garmen (Konveksi / Textile)</option>
                            <option value="Umum">Umum / Assembler</option>
                        </select>
                    </div>

                    {{-- Rentang HPP --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-theme-muted small mb-1">
                            <i class="bi bi-cash-stack me-1 text-primary"></i> Rentang Estimasi HPP (Rp)
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text text-theme-muted">Min</span>
                                    <input type="text" id="filterMinHpp" class="form-control inp-hpp-formatted text-end" placeholder="0">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text text-theme-muted">Max</span>
                                    <input type="text" id="filterMaxHpp" class="form-control inp-hpp-formatted text-end" placeholder="Tak Terbatas">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bom-modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="resetFilterBomModal()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="terapkanFilterBomModal()">
                    <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalFilterBom() {
        $('#filterPeriodeBomModal').val($('#filterPeriodeBom').val() || '');
        $('#modalFilterBom').modal('show');
    }

    function terapkanFilterBomModal() {
        const modalPeriode = $('#filterPeriodeBomModal').val() || BULAN_INI_STR;
        $('#filterPeriodeBom').val(modalPeriode);
        gantiPeriodeBom(modalPeriode);
        updateActiveFilterBadge();
        $('#modalFilterBom').modal('hide');
    }

    function resetFilterBomModal() {
        $('#filterPeriodeBomModal').val(BULAN_INI_STR);
        $('#filterStatusBom').val('');
        $('#filterTipeBom').val('');
        $('#filterMinHpp').val('');
        $('#filterMaxHpp').val('');
        resetKeBulanIni();
        updateActiveFilterBadge();
        $('#modalFilterBom').modal('hide');
    }

    function updateActiveFilterBadge() {
        let count = 0;
        if ($('#filterPeriodeBom').val() && $('#filterPeriodeBom').val() !== BULAN_INI_STR) count++;
        if ($('#filterStatusBom').val()) count++;
        if ($('#filterTipeBom').val()) count++;
        if ($('#filterMinHpp').val()) count++;
        if ($('#filterMaxHpp').val()) count++;

        if (count > 0) {
            $('#badgeActiveFilterCount').text(count).removeClass('d-none');
            $('#btnFilterBom').addClass('btn-primary').removeClass('btn-outline-secondary');
        } else {
            $('#badgeActiveFilterCount').text('0').addClass('d-none');
            $('#btnFilterBom').addClass('btn-outline-secondary').removeClass('btn-primary');
        }
    }
</script>
@endpush
