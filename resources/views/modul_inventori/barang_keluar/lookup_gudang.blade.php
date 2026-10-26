{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL LOOKUP GUDANG (Cari Gudang via Modal & Search Table) --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<style>
    #modalLookupGudang .table-modern {
        min-width: 100% !important;
        width: 100% !important;
    }
    #modalLookupGudang .table-responsive {
        max-height: 440px;
        overflow-x: auto;
    }
    #modalLookupGudang .lookup-gudang-row:hover td {
        background-color: rgba(239, 68, 68, 0.08) !important;
    }
    [data-bs-theme="dark"] #modalLookupGudang .lookup-gudang-row:hover td,
    .dark-mode #modalLookupGudang .lookup-gudang-row:hover td {
        background-color: rgba(239, 68, 68, 0.16) !important;
    }
</style>

<div class="modal fade" id="modalLookupGudang" tabindex="-1" aria-labelledby="modalLookupGudangLabel" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1140px; width: 95vw; z-index: 1061 !important;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-danger bg-gradient text-white rounded-top-3 py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-house-door fs-5 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0" id="modalLookupGudangLabel">Cari &amp; Pilih Gudang</h5>
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 py-0.5 small" id="lookupGudangCountBadge">{{ count($gudangs) }} Gudang</span>
                        </div>
                        <small class="text-white-50">Menampilkan gudang penyimpanan aktif dalam sistem</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closeLookupGudangModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Input Pencarian --}}
                <div class="input-group mb-3 shadow-xs">
                    <span class="input-group-text border-end-0"><i class="bi bi-search text-danger"></i></span>
                    <input type="text" id="lookupGudangSearchInput" class="form-control border-start-0 ps-0" placeholder="Ketik kode atau nama gudang untuk menyaring...">
                    <button type="button" class="btn btn-outline-secondary border-start-0" id="btnResetSearchGudang" title="Bersihkan Pencarian" style="display: none;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Tabel Daftar Gudang --}}
                <div class="table-responsive rounded-2 border">
                    <table class="table table-hover table-modern align-middle mb-0" id="tableLookupGudang">
                        <thead class="sticky-top">
                            <tr>
                                <th width="6%" class="text-center py-3">No</th>
                                <th width="20%" class="py-3">Kode Gudang</th>
                                <th width="38%" class="py-3">Nama Gudang</th>
                                <th width="24%" class="py-3">Cabang / Lokasi</th>
                                <th width="12%" class="text-center py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lookupGudangTbody">
                            @forelse($gudangs as $idx => $g)
                                <tr class="lookup-gudang-row" data-kode="{{ strtolower($g->kode_gudang) }}" data-nama="{{ strtolower($g->nama_gudang) }}">
                                    <td class="text-center text-muted small">{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fw-bold px-2.5 py-1 rounded-pill font-monospace">
                                            {{ $g->kode_gudang }}
                                        </span>
                                    </td>
                                    <td><span class="fw-semibold text-body">{{ $g->nama_gudang }}</span></td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal px-2.5 py-1 rounded-pill">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $g->cabang ? $g->cabang->nama_cabang : 'PT Pusat' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3.5 py-1 fw-semibold shadow-sm"
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
            <div class="modal-footer py-2.5 px-4 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Klik tombol <strong>Pilih</strong> pada baris gudang yang diinginkan.</small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" onclick="closeLookupGudangModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let lookupGudangCallback = null;

// Open Lookup Gudang Modal
function openLookupGudangModal(callback = null) {
    lookupGudangCallback = typeof callback === 'function' ? callback : null;
    $('#lookupGudangSearchInput').val('');
    $('#btnResetSearchGudang').hide();
    $('#lookupGudangTbody tr.lookup-gudang-row').show();

    const totalCount = $('#lookupGudangTbody tr.lookup-gudang-row').length;
    $('#lookupGudangCountBadge').text(`${totalCount} Gudang`);

    const modalEl = document.getElementById('modalLookupGudang');
    if (modalEl) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
        setTimeout(() => {
            $('#lookupGudangSearchInput').focus();
        }, 200);
    }
}

function closeLookupGudangModal() {
    const modalEl = document.getElementById('modalLookupGudang');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
    lookupGudangCallback = null;
}

// Select Gudang from modal table
function selectLookupGudang(id, kode, nama) {
    if (typeof lookupGudangCallback === 'function') {
        lookupGudangCallback({ id: id, kode_gudang: kode, nama_gudang: nama });
        lookupGudangCallback = null;
    } else {
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
    }

    closeLookupGudangModal();
}

// Live search filter inside gudang modal table
$(document).ready(function () {
    $('#lookupGudangSearchInput').on('input keyup', function () {
        const term = $(this).val().toLowerCase().trim();
        let visibleCount = 0;

        if (term.length > 0) {
            $('#btnResetSearchGudang').show();
        } else {
            $('#btnResetSearchGudang').hide();
        }

        $('#lookupGudangTbody tr.lookup-gudang-row').each(function () {
            const kode = $(this).data('kode') || '';
            const nama = $(this).data('nama') || '';
            if (kode.includes(term) || nama.includes(term)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        $('#lookupGudangCountBadge').text(`${visibleCount} Gudang`);
    });

    $('#btnResetSearchGudang').on('click', function () {
        $('#lookupGudangSearchInput').val('').trigger('input').focus();
    });
});
</script>
@endpush
