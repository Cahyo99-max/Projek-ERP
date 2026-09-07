{{-- MODAL FILTER PENGELUARAN BAHAN BAKU SPK --}}
<div class="modal fade spk-modal-secondary" id="modalFilterPengeluaran" tabindex="-1" aria-labelledby="modalFilterPengeluaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- HEADER MODAL --}}
            <div class="modal-header spk-modal-header-dark py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-funnel-fill fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6" id="modalFilterPengeluaranLabel">Filter Data Pengeluaran Bahan</h5>
                        <small class="text-white-50">Saring transaksi berdasarkan gudang atau rentang tanggal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY MODAL --}}
            <div class="modal-body spk-modal-body p-4">
                <div class="row g-3">
                    {{-- Gudang Bahan Baku --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-building text-primary me-1"></i>Gudang Asal Bahan Baku
                        </label>
                        <select id="modal_filter_gudang" class="form-select spk-select rounded-3">
                            <option value="all">-- Semua Gudang Bahan --</option>
                            @foreach($gudangList as $g)
                                <option value="{{ $g->id }}">[{{ $g->kode_gudang }}] {{ $g->nama_gudang }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-calendar-event text-primary me-1"></i>Tanggal Mulai
                        </label>
                        <input type="date" id="modal_filter_start_date" class="form-control spk-input rounded-3" autocomplete="off">
                    </div>

                    {{-- Tanggal Sampai --}}
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold spk-text-muted small">
                            <i class="bi bi-calendar-event text-primary me-1"></i>Tanggal Sampai
                        </label>
                        <input type="date" id="modal_filter_end_date" class="form-control spk-input rounded-3" autocomplete="off">
                    </div>
                </div>
            </div>

            {{-- FOOTER MODAL --}}
            <div class="modal-footer spk-modal-footer d-flex justify-content-between p-3 p-sm-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" onclick="resetFilterPengeluaranModal()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="terapkanFilterPengeluaranModal()">
                    <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function terapkanFilterPengeluaranModal() {
        $('#modalFilterPengeluaran').modal('hide');
        reloadTablePengeluaran();
    }

    function resetFilterPengeluaranModal() {
        $('#modal_filter_gudang').val('all');
        $('#modal_filter_start_date').val('');
        $('#modal_filter_end_date').val('');
        $('#modalFilterPengeluaran').modal('hide');
        reloadTablePengeluaran();
    }
</script>
@endpush
