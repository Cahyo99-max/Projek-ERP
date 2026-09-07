{{-- MODAL LOOKUP PILIH SPK PRODUKSI (ULTRA MODERN & CINEMATIC) --}}
<div class="modal fade spk-modal-secondary" id="modalLookupSpk" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header spk-modal-header-dark py-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-file-earmark-ruled fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6">Pilih Surat Perintah Kerja (SPK)</h5>
                        <small class="text-white-50">Pilih SPK yang sedang berjalan (In Progress) untuk dikeluarkan bahan bakunya</small>
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
                    <input type="text" id="searchSpkLookupInput" class="form-control spk-input border-start-0 fs-6"
                           placeholder="Ketik nomor SPK atau nama barang jadi..." autocomplete="off" oninput="filterLookupSpk(this.value)">
                </div>

                {{-- Result Table / Cards --}}
                <div class="spk-table-wrapper rounded-3" style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0 spk-table">
                        <thead>
                            <tr class="small text-uppercase fw-bold spk-text-muted">
                                <th class="ps-3">NO. SPK & BARANG JADI</th>
                                <th class="text-center" style="width: 130px;">TARGET QTY</th>
                                <th class="text-center" style="width: 120px;">STATUS</th>
                                <th class="text-center pe-3" style="width: 90px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupSpk" class="spk-text-main">
                            @if($spkList->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-5 spk-text-muted">
                                        <i class="bi bi-info-circle fs-3 d-block mb-2 text-warning"></i>
                                        Tidak ada SPK Produksi yang dapat dipilih saat ini.
                                    </td>
                                </tr>
                            @endif
                            @foreach($spkList as $sIdx => $s)
                                @php
                                    $bName = $s->barangJadi ? "[{$s->barangJadi->kode_barang}] {$s->barangJadi->nama_barang}" : '-';
                                    $satName = $s->satuan ? $s->satuan->nama_satuan : ($s->barangJadi && $s->barangJadi->satuan ? $s->barangJadi->satuan->nama_satuan : 'Pcs');
                                    $statusClass = 'bg-info text-info border-info';
                                @endphp
                                <tr class="cursor-pointer hover-row row-spk-item"
                                    data-spk-id="{{ $s->id }}"
                                    data-nomor="{{ strtolower($s->nomor_spk) }}"
                                    data-barang="{{ strtolower($bName) }}"
                                    onclick="pilihSpkFromLookup({{ $s->id }}, '{{ $s->nomor_spk }}', '{{ addslashes($bName) }}', '{{ $s->status }}')"
                                    style="cursor: pointer;">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi bi-file-earmark-text fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-primary font-monospace fs-6">{{ $s->nomor_spk }}</div>
                                                <small class="spk-text-main fw-semibold">{{ $bName }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace small">
                                        <b>{{ number_format($s->jumlah_rencana, 0, ',', '.') }}</b> {{ $satName }}
                                    </td>
                                    <td class="text-center">
                                        @if($s->status === 'Completed')
                                            <span class="badge spk-badge-green rounded-pill px-3 py-1 font-monospace" style="font-size: 11.5px;">
                                                <i class="bi bi-check-circle-fill me-1"></i>Completed
                                            </span>
                                        @else
                                            <span class="badge spk-badge-cyan rounded-pill px-3 py-1 font-monospace" style="font-size: 11.5px;">
                                                <i class="bi bi-play-circle-fill me-1"></i>{{ $s->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-xs"
                                                onclick="event.stopPropagation(); pilihSpkFromLookup({{ $s->id }}, '{{ $s->nomor_spk }}', '{{ addslashes($bName) }}', '{{ $s->status }}')">
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
                <small class="spk-text-muted"><i class="bi bi-info-circle me-1"></i>Pilih salah satu SPK untuk menampilkan resep bahan bakunya.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bukaModalLookupSpk() {
        $('#searchSpkLookupInput').val('');
        $('.row-spk-item').show();
        $('#modalLookupSpk').modal('show');
    }

    function filterLookupSpk(keyword) {
        const q = (keyword || '').toLowerCase().trim();
        $('.row-spk-item').each(function() {
            const no = $(this).data('nomor') || '';
            const brg = $(this).data('barang') || '';
            if (!q || no.includes(q) || brg.includes(q)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
</script>
@endpush
