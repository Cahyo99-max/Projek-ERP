{{-- MODAL LOOKUP GUDANG HASIL PRODUKSI --}}
<div class="modal fade" id="modalLookupGudangPbj" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4" style="background: var(--spk-card-bg, #ffffff);">
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); width: 38px; height: 38px;">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0 text-white fs-6">Pilih Gudang Barang Jadi</h5>
                        <small class="text-white-50" style="font-size: 11px;">Gudang tujuan penampungan stok hasil produksi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" id="search_lookup_gudang_pbj" class="form-control form-control-sm font-monospace" placeholder="Ketik Kode / Nama Gudang..." onkeyup="filterTableLookupGudangPbj()">
                </div>

                <div class="table-responsive">
                    <table class="table align-middle spk-detail-table w-100" id="table_lookup_gudang_pbj">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 110px;">Kode</th>
                                <th>Nama Gudang</th>
                                <th class="text-center pe-3" style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gudangList as $g)
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-primary">[{{ $g->kode_gudang }}]</td>
                                    <td class="fw-bold spk-text-contrast">{{ $g->nama_gudang }}</td>
                                    <td class="text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" onclick="pilihGudangFromLookupPbj({{ $g->id }}, '{{ addslashes($g->kode_gudang) }}', '{{ addslashes($g->nama_gudang) }}')">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-2.5">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 576px) {
        #modalLookupGudangPbj .modal-dialog {
            margin: 0.5rem !important;
            max-width: calc(100% - 1rem) !important;
        }
    }
</style>

@push('scripts')
<script>
    function bukaModalLookupGudangPbj() {
        $('#modalLookupGudangPbj').modal('show');
    }

    function filterTableLookupGudangPbj() {
        const val = $('#search_lookup_gudang_pbj').val().toLowerCase();
        $('#table_lookup_gudang_pbj tbody tr').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    }

    function pilihGudangFromLookupPbj(gudangId, kodeGudang, namaGudang) {
        $('#pbj_gudang_id').val(gudangId);
        if (gudangId && kodeGudang) {
            $('#pbj_gudang_nama_display').val(`[${kodeGudang}] ${namaGudang}`);
        } else {
            $('#pbj_gudang_nama_display').val('');
        }
        // Reset Rak location when warehouse changes
        $('#pbj_lokasi_rak_id').val('');
        $('#pbj_lokasi_rak_nama_display').val('');

        $('#modalLookupGudangPbj').modal('hide');
    }
</script>
@endpush
