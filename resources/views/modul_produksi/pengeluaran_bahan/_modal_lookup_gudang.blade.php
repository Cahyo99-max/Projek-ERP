{{-- MODAL LOOKUP PILIH GUDANG (ULTRA MODERN & CINEMATIC) --}}
<div class="modal fade spk-modal-secondary" id="modalLookupGudang" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header spk-modal-header-dark py-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6">Pilih Gudang Asal Bahan Baku</h5>
                        <small class="text-white-50">Pilih gudang tempat penyimpanan bahan mentah yang dikeluarkan</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body spk-modal-body p-3 p-sm-4">
                {{-- Search Input Bar --}}
                <div class="input-group mb-3 shadow-xs rounded-3 overflow-hidden">
                    <span class="input-group-text spk-input-group-text border-end-0">
                        <i class="bi bi-search text-primary"></i>
                    </span>
                    <input type="text" id="searchGudangLookupInput" class="form-control spk-input border-start-0 fs-6"
                           placeholder="Ketik kode atau nama gudang..." autocomplete="off" oninput="filterLookupGudang(this.value)">
                </div>

                {{-- Result Table / Cards --}}
                <div class="spk-table-wrapper rounded-3" style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0 spk-table">
                        <thead>
                            <tr class="small text-uppercase fw-bold spk-text-muted">
                                <th class="ps-3">KODE & NAMA GUDANG</th>
                                <th>LOKASI / CABANG</th>
                                <th class="text-center pe-3" style="width: 90px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupGudang" class="spk-text-main">
                            @foreach($gudangList as $g)
                                <tr class="cursor-pointer hover-row row-gudang-item"
                                    data-gudang-id="{{ $g->id }}"
                                    data-kode="{{ strtolower($g->kode_gudang) }}"
                                    data-nama="{{ strtolower($g->nama_gudang) }}"
                                    onclick="pilihGudangFromLookup({{ $g->id }}, '{{ $g->kode_gudang }}', '{{ addslashes($g->nama_gudang) }}')"
                                    style="cursor: pointer;">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi bi-building fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-primary font-monospace fs-6">[{{ $g->kode_gudang }}]</div>
                                                <div class="spk-text-main fw-semibold">{{ $g->nama_gudang }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small spk-text-muted">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i>{{ $g->lokasi ?? $g->branch->nama_cabang ?? 'Gudang Utama' }}
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-xs"
                                                onclick="event.stopPropagation(); pilihGudangFromLookup({{ $g->id }}, '{{ $g->kode_gudang }}', '{{ addslashes($g->nama_gudang) }}')">
                                            <i class="bi bi-check-lg me-1"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer spk-modal-footer d-flex justify-content-between p-3 p-sm-4">
                <small class="spk-text-muted"><i class="bi bi-info-circle me-1"></i>Stok bahan akan otomatis dipotong dari gudang yang dipilih.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalLookupGudang() {
        $('#searchGudangLookupInput').val('');
        $('.row-gudang-item').show();
        $('#modalLookupGudang').modal('show');
    }

    function filterLookupGudang(keyword) {
        const q = (keyword || '').toLowerCase().trim();
        $('.row-gudang-item').each(function() {
            const kode = $(this).data('kode') || '';
            const nama = $(this).data('nama') || '';
            if (!q || kode.includes(q) || nama.includes(q)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
</script>
@endpush
