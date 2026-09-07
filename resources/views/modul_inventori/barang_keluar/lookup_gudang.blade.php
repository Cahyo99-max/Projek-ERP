{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL LOOKUP GUDANG (Cari Gudang via Modal & Search Table) --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLookupGudang" tabindex="-1" aria-labelledby="modalLookupGudangLabel" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="z-index: 1061 !important;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-danger bg-gradient text-white rounded-top-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-house-door fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalLookupGudangLabel">Cari & Pilih Gudang</h5>
                        <small class="text-white-50">Menampilkan gudang penyimpanan aktif</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closeLookupGudangModal()"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Input Pencarian --}}
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="lookupGudangSearchInput" class="form-control border-start-0 ps-0" placeholder="Ketik kode atau nama gudang untuk menyaring...">
                </div>

                {{-- Tabel Daftar Gudang --}}
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover table-modern align-middle mb-0" id="tableLookupGudang">
                        <thead class="sticky-top">
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Kode Gudang</th>
                                <th>Nama Gudang</th>
                                <th width="25%">Cabang / Lokasi</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lookupGudangTbody">
                            @forelse($gudangs as $idx => $g)
                                <tr class="lookup-gudang-row" data-kode="{{ strtolower($g->kode_gudang) }}" data-nama="{{ strtolower($g->nama_gudang) }}">
                                    <td class="text-muted small">{{ $idx + 1 }}</td>
                                    <td><span class="fw-bold text-body-emphasis">{{ $g->kode_gudang }}</span></td>
                                    <td><span class="fw-semibold text-body">{{ $g->nama_gudang }}</span></td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal px-2.5 py-1 rounded-pill">
                                            {{ $g->cabang ? $g->cabang->nama_cabang : 'PT Pusat' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold shadow-sm"
                                                onclick="selectLookupGudang({{ $g->id }}, '{{ addslashes($g->kode_gudang) }}', '{{ addslashes($g->nama_gudang) }}')">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Tidak ada gudang aktif tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" onclick="closeLookupGudangModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Open Lookup Gudang Modal
function openLookupGudangModal() {
    $('#lookupGudangSearchInput').val('');
    $('#lookupGudangTbody tr.lookup-gudang-row').show();

    const modalEl = document.getElementById('modalLookupGudang');
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }
}

function closeLookupGudangModal() {
    const modalEl = document.getElementById('modalLookupGudang');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
}

// Select Gudang from modal table
function selectLookupGudang(id, kode, nama) {
    const currentId = $('#gi_gudang_id').val();
    if (currentId != id) {
        $('#gi_gudang_id').val(id);
        $('#gi_gudang_display').val(`${kode} - ${nama}`);
        
        // Reset items when warehouse changes
        $('#gi_items_container').empty();
        window.availableBarangsForSelectedGudang = [];
        
        // Pre-fetch available stock for selected gudang
        $.get(`/goods-issue/barangs-by-gudang/${id}`, function (res) {
            window.availableBarangsForSelectedGudang = res.barangs || [];
        });
    }

    closeLookupGudangModal();
}

// Live search filter inside gudang modal table
$(document).ready(function () {
    $('#lookupGudangSearchInput').on('keyup', function () {
        const term = $(this).val().toLowerCase().trim();
        $('#lookupGudangTbody tr.lookup-gudang-row').each(function () {
            const kode = $(this).data('kode') || '';
            const nama = $(this).data('nama') || '';
            if (kode.includes(term) || nama.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
