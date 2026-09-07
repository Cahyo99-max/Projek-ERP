{{-- MODAL LOOKUP PILIH KARYAWAN  --}}
<div class="modal fade spk-modal-secondary" id="modalLookupKaryawan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header spk-modal-header-dark py-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6" id="titleLookupKaryawan">Pilih Karyawan</h5>
                        <small class="text-white-50" id="subtitleLookupKaryawan">Pilih staf penyerah atau penerima pengeluaran bahan</small>
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
                    <input type="text" id="searchKaryawanLookupInput" class="form-control spk-input border-start-0 fs-6"
                           placeholder="Ketik NIK atau nama karyawan..." autocomplete="off" oninput="filterLookupKaryawan(this.value)">
                </div>

                {{-- Result Table / Cards --}}
                <div class="spk-table-wrapper rounded-3" style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0 spk-table">
                        <thead>
                            <tr class="small text-uppercase fw-bold spk-text-muted">
                                <th class="ps-3">KARYAWAN</th>
                                <th>JABATAN / DIVISI</th>
                                <th class="text-center pe-3" style="width: 90px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupKaryawan" class="spk-text-main">
                            @foreach($karyawanList as $k)
                                @php
                                    $jabatan = $k->position->nama_jabatan ?? 'Staf';
                                    $initials = strtoupper(substr($k->nama_lengkap, 0, 2));
                                @endphp
                                <tr class="cursor-pointer hover-row row-karyawan-item"
                                    data-karyawan-id="{{ $k->id }}"
                                    data-nama="{{ strtolower($k->nama_lengkap) }}"
                                    data-nik="{{ strtolower($k->nik ?? '') }}"
                                    data-jabatan="{{ strtolower($jabatan) }}"
                                    onclick="pilihKaryawanFromLookup({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}', '{{ addslashes($jabatan) }}')"
                                    style="cursor: pointer;">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 13px;">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <div class="fw-bold spk-text-main">{{ $k->nama_lengkap }}</div>
                                                <small class="spk-text-muted font-monospace">NIK: {{ $k->nik ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1" style="font-size: 11px;">
                                            {{ $jabatan }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-xs"
                                                onclick="event.stopPropagation(); pilihKaryawanFromLookup({{ $k->id }}, '{{ addslashes($k->nama_lengkap) }}', '{{ addslashes($jabatan) }}')">
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
                <small class="spk-text-muted"><i class="bi bi-info-circle me-1"></i>Pilih salah satu karyawan untuk ditugaskan.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeKaryawanTargetField = 'penyerah'; // 'penyerah' or 'penerima'

    function bukaModalLookupKaryawan(targetField = 'penyerah') {
        activeKaryawanTargetField = targetField;
        if (targetField === 'penyerah') {
            $('#titleLookupKaryawan').text('Pilih Petugas Gudang (Penyerah)');
            $('#subtitleLookupKaryawan').text('Petugas gudang yang menyerahkan bahan baku');
        } else {
            $('#titleLookupKaryawan').text('Pilih Penerima / Operator Line');
            $('#subtitleLookupKaryawan').text('Operator / mandor line produksi yang menerima bahan');
        }

        $('#searchKaryawanLookupInput').val('');
        $('.row-karyawan-item').show();
        $('#modalLookupKaryawan').modal('show');
    }

    function filterLookupKaryawan(keyword) {
        const q = (keyword || '').toLowerCase().trim();
        $('.row-karyawan-item').each(function() {
            const nama = $(this).data('nama') || '';
            const nik = $(this).data('nik') || '';
            const jabatan = $(this).data('jabatan') || '';
            if (!q || nama.includes(q) || nik.includes(q) || jabatan.includes(q)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function pilihKaryawanFromLookup(karyawanId, namaLengkap, jabatan) {
        if (activeKaryawanTargetField === 'penyerah') {
            $('#inp_penyerah_karyawan_id').val(karyawanId);
            $('#display_penyerah_pilihan').val(`${namaLengkap} (${jabatan})`).addClass('fw-bold text-primary');
        } else {
            $('#inp_penerima_karyawan_id').val(karyawanId);
            $('#display_penerima_pilihan').val(`${namaLengkap} (${jabatan})`).addClass('fw-bold text-primary');
        }
        $('#modalLookupKaryawan').modal('hide');
    }
</script>
@endpush
