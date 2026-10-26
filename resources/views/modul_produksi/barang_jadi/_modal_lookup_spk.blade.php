{{-- MODAL LOOKUP SPK PRODUKSI UNTUK PENERIMAAN BARANG JADI --}}
<div class="modal fade" id="modalLookupSpkPbj" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-2xl rounded-4" style="background: var(--spk-card-bg, #ffffff);">
            <div class="modal-header border-0 px-4 py-3" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #0f766e 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.15); width: 38px; height: 38px;">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0 text-white fs-6">Cari & Pilih Dokumen SPK Produksi</h5>
                        <small class="text-white-50" style="font-size: 11px;">Pilih SPK yang sedang dalam proses atau siap diterima hasil jadinya</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" id="search_lookup_spk_pbj" class="form-control form-control-sm font-monospace" placeholder="Ketik Nomor SPK / Nama Produk Jadi..." onkeyup="filterTableLookupSpkPbj()">
                </div>

                <div class="table-responsive">
                    <table class="table align-middle spk-detail-table w-100" id="table_lookup_spk_pbj">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 140px;">Nomor SPK</th>
                                <th>Item Produk Jadi</th>
                                <th class="text-center" style="width: 120px;">Target Qty</th>
                                <th class="text-center" style="width: 130px;">Status</th>
                                <th class="text-center pe-3" style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spkList as $s)
                                @php
                                    $bKode = $s->barangJadi ? $s->barangJadi->kode_barang : '-';
                                    $bNama = $s->barangJadi ? $s->barangJadi->nama_barang : '-';
                                    $satName = $s->satuan ? $s->satuan->nama_satuan : ($s->barangJadi && $s->barangJadi->satuan ? $s->barangJadi->satuan->nama_satuan : 'Pcs');
                                @endphp
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-primary">{{ $s->nomor_spk }}</td>
                                    <td>
                                        <div class="fw-bold spk-text-contrast">[{{ $bKode }}] {{ $bNama }}</div>
                                        <small class="spk-text-muted-contrast">Tgl SPK: {{ $s->tanggal_spk ? $s->tanggal_spk->format('d/m/Y') : '-' }}</small>
                                    </td>
                                    <td class="text-center font-monospace small fw-bold">{{ number_format($s->jumlah_rencana, 0, ',', '.') }} {{ $satName }}</td>
                                    <td class="text-center">
                                        @if($s->status === 'In Progress')
                                            <span class="badge bg-info text-dark rounded-pill px-2.5 py-0.5">In Progress</span>
                                        @elseif($s->status === 'Completed')
                                            <span class="badge bg-success rounded-pill px-2.5 py-0.5">Completed</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2.5 py-0.5">{{ $s->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" onclick="pilihSpkFromLookupPbj({{ $s->id }})">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($spkList) === 0)
                                <tr><td colspan="5" class="text-center py-4 spk-text-muted-contrast">Tidak ada SPK aktif ditemukan.</td></tr>
                            @endif
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
        #modalLookupSpkPbj .modal-dialog {
            margin: 0.5rem !important;
            max-width: calc(100% - 1rem) !important;
        }
    }
</style>

@push('scripts')
<script>
    function bukaModalLookupSpkPbj() {
        $('#modalLookupSpkPbj').modal('show');
    }

    function filterTableLookupSpkPbj() {
        const val = $('#search_lookup_spk_pbj').val().toLowerCase();
        $('#table_lookup_spk_pbj tbody tr').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
    }

    function pilihSpkFromLookupPbj(spkId) {
        onPbjSpkChanged(spkId);
        $('#modalLookupSpkPbj').modal('hide');
    }
</script>
@endpush
