{{-- MODAL FILTER POPUP --}}
<div class="modal fade" id="modalFilterAdjustment" tabindex="-1" aria-labelledby="modalFilterAdjustmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-body p-3.5 border-bottom rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalFilterAdjustmentLabel">Filter Data Adjustment</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="formFilterAdjustment" autocomplete="off">
                    <div class="row g-3">
                        {{-- Filter Periode Bulan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Periode Bulan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-month"></i></span>
                                <input type="month" id="modal_filter_bulan" class="form-control rounded-end-3" value="{{ date('Y-m') }}">
                            </div>
                        </div>

                        {{-- Filter Gudang --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Gudang</label>
                            <select id="filter_gudang_id" class="form-select rounded-3">
                                <option value="">-- Semua Gudang --</option>
                                @foreach($gudangs as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Tipe Adjustment --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Tipe Penyesuaian</label>
                            <select id="filter_tipe" class="form-select rounded-3">
                                <option value="">-- Semua Tipe --</option>
                                <option value="Pengurangan">Pengurangan (Keluar)</option>
                                <option value="Penambahan">Penambahan (Masuk)</option>
                            </select>
                        </div>

                        {{-- Filter Status --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Status Dokumen</label>
                            <select id="filter_status" class="form-select rounded-3">
                                <option value="">-- Semua Status --</option>
                                <option value="Draft">Draft</option>
                                <option value="Pending">Pending Approval</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        {{-- Filter Kategori Alasan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Kategori Alasan</label>
                            <select id="filter_kategori" class="form-select rounded-3">
                                <option value="">-- Semua Alasan --</option>
                                <option value="Barang Rusak">Barang Rusak (Damaged)</option>
                                <option value="Barang Hilang">Barang Hilang (Lost/Shrinkage)</option>
                                <option value="Barang Sampel/Promosi">Barang Sampel / Promosi</option>
                                <option value="Internal Use">Penggunaan Internal</option>
                                <option value="Koreksi Input">Koreksi Input Data</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top-0 p-4 pt-0 justify-content-between">
                <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="resetFilterAdjustment()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="terapkanFilterAdjustment()">
                    <i class="bi bi-funnel me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function terapkanFilterAdjustment() {
        const modalBulan = $('#modal_filter_bulan').val();
        if (modalBulan) {
            $('#filter_bulan').val(modalBulan);
        }

        let activeCount = 0;
        if ($('#filter_gudang_id').val()) activeCount++;
        if ($('#filter_tipe').val()) activeCount++;
        if ($('#filter_status').val()) activeCount++;
        if ($('#filter_kategori').val()) activeCount++;

        if (activeCount > 0) {
            $('#badgeFilterActive').text(activeCount).removeClass('d-none');
            $('#btnOpenFilter').addClass('border-primary text-primary');
        } else {
            $('#badgeFilterActive').addClass('d-none');
            $('#btnOpenFilter').removeClass('border-primary text-primary');
        }

        $('#modalFilterAdjustment').modal('hide');
        reloadTableAdjustment();
    }

    function resetFilterAdjustment() {
        $('#formFilterAdjustment')[0].reset();
        const currentMonth = '{{ date('Y-m') }}';
        $('#modal_filter_bulan').val(currentMonth);
        $('#filter_bulan').val(currentMonth);
        $('#badgeFilterActive').addClass('d-none');
        $('#btnOpenFilter').removeClass('border-primary text-primary');
        $('#modalFilterAdjustment').modal('hide');
        reloadTableAdjustment();
    }
</script>
@endpush
