{{-- MODAL LOOKUP RAK GUDANG BARANG JADI (LENGKAP DENGAN LANTAI / LEVEL / BARIS / KOLOM) --}}
<div class="modal fade" id="modalLookupRakPbj" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4" style="background: var(--spk-card-bg, #ffffff);">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); width: 40px; height: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <i class="bi bi-grid-3x3-gap fs-5 text-emerald-light"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0 text-white fs-6">Pilih Lokasi Rak & Posisi Gudang</h5>
                        <small class="text-white-50" style="font-size: 11.5px;">Daftar nomor rak, lantai (level), baris, dan kolom penyimpanan barang jadi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-3 p-md-4">
                <div class="mb-3">
                    <div class="input-group shadow-2xs rounded-3 overflow-hidden">
                        <span class="input-group-text bg-body-tertiary border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="search_lookup_rak_pbj" class="form-control spk-input border-start-0" placeholder="Ketik Kode Rak / Nama Rak / Lantai / Gudang..." onkeyup="filterTableLookupRakPbj()">
                    </div>
                </div>

                <div class="table-responsive rounded-3 border">
                    <table class="table align-middle spk-table-main mb-0 w-100" id="table_lookup_rak_pbj">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="ps-3" style="min-width: 140px;">Kode & Nama Rak</th>
                                <th style="min-width: 120px;">Lantai (Level)</th>
                                <th style="min-width: 130px;">Baris / Kolom</th>
                                <th style="min-width: 150px;">Gudang Penampung</th>
                                <th class="text-center pe-3" style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rakList as $r)
                                <tr data-gudang-id="{{ $r->gudang_id }}" class="row-rak-pbj">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-2 px-2 py-0.5 font-monospace fw-bold" style="font-size: 11px;">
                                                {{ $r->kode_rak }}
                                            </span>
                                            <span class="fw-bold spk-text-main">{{ $r->nama_rak }}</span>
                                        </div>
                                        @if($r->deskripsi)
                                            <small class="text-muted d-block" style="font-size: 11px;">{{ $r->deskripsi }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-emerald bg-opacity-15 text-emerald border border-emerald border-opacity-30 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                            <i class="bi bi-layers-fill me-1"></i>Lantai: {{ $r->level ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                            Brs: {{ $r->baris ?: '-' }} | Klm: {{ $r->kolom ?: '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold spk-text-main">{{ $r->gudang ? $r->gudang->nama_gudang : '-' }}</div>
                                        <small class="text-muted font-monospace" style="font-size: 10.5px;">{{ $r->gudang ? '[' . $r->gudang->kode_gudang . ']' : '' }}</small>
                                    </td>
                                    <td class="text-center pe-3">
                                        @php
                                            $levelTxt = $r->level ? "Lantai {$r->level}" : "";
                                            $posTxt = "";
                                            if ($r->baris || $r->kolom) {
                                                $posTxt = " (Brs {$r->baris}, Klm {$r->kolom})";
                                            }
                                            $fullLabel = "[{$r->kode_rak}] {$r->nama_rak}";
                                            if ($levelTxt) {
                                                $fullLabel .= " - {$levelTxt}{$posTxt}";
                                            }
                                        @endphp
                                        <button type="button" class="btn btn-sm btn-emerald-vibrant rounded-pill px-3 py-1 fw-bold shadow-2xs" 
                                                onclick="pilihRakFromLookupPbj({{ $r->id }}, '{{ addslashes($r->kode_rak) }}', '{{ addslashes($r->nama_rak) }}', '{{ addslashes($r->level ?? '') }}', '{{ addslashes($r->baris ?? '') }}', '{{ addslashes($r->kolom ?? '') }}')">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($rakList) === 0)
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                                        <span>Belum ada master lokasi rak terdaftar.</span>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer border-top px-4 py-2.5 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" onclick="pilihRakFromLookupPbj('', '', '', '', '', '')">
                    <i class="bi bi-x-circle me-1"></i>Kosongkan Pilihan Rak
                </button>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 576px) {
        #modalLookupRakPbj .modal-dialog {
            margin: 0.5rem !important;
            max-width: calc(100% - 1rem) !important;
        }
    }
</style>

@push('scripts')
<script>
    function bukaModalLookupRakPbj() {
        const gudangId = $('#pbj_gudang_id').val();
        if (gudangId) {
            $('.row-rak-pbj').each(function () {
                const gId = $(this).data('gudang-id');
                if (!gId || gId == gudangId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $('.row-rak-pbj').show();
        }
        $('#modalLookupRakPbj').modal('show');
    }

    function filterTableLookupRakPbj() {
        const val = $('#search_lookup_rak_pbj').val().toLowerCase();
        const gudangId = $('#pbj_gudang_id').val();

        $('.row-rak-pbj').each(function () {
            const gId = $(this).data('gudang-id');
            const text = $(this).text().toLowerCase();
            const matchesGudang = !gudangId || !gId || gId == gudangId;
            const matchesText = text.indexOf(val) > -1;
            $(this).toggle(matchesGudang && matchesText);
        });
    }

    function pilihRakFromLookupPbj(rakId, kodeRak, namaRak, level = '', baris = '', kolom = '') {
        $('#pbj_lokasi_rak_id').val(rakId);
        if (rakId && kodeRak) {
            let label = `[${kodeRak}] ${namaRak}`;
            if (level) {
                label += ` - Lantai: ${level}`;
            }
            if (baris || kolom) {
                label += ` (Brs: ${baris || '-'}, Klm: ${kolom || '-'})`;
            }
            $('#pbj_lokasi_rak_nama_display').val(label);
        } else {
            $('#pbj_lokasi_rak_nama_display').val('');
        }
        $('#modalLookupRakPbj').modal('hide');
    }
</script>
@endpush
