{{-- MODAL LOOKUP CARI NAMA PEMOHON / PENGAJU PETTY CASH --}}
<div class="modal fade" id="modalLookupPemohon" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1085;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 720px;">
        <div class="modal-content pc-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header p-3 p-md-3.5 border-0 position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #0e7490 0%, #0891b2 50%, #06b6d4 100%);">
                <div style="position: absolute; right: -20px; top: -20px; width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-people-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Cari Nama Pemohon / Pengaju</h5>
                        <small class="text-white-50 d-block mt-0.5" style="font-size: 11.5px;">Pilih dari riwayat yang pernah dimasukkan ke Petty Cash atau gunakan nama baru</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body p-3 p-md-3.5 pc-modal-body-canvas">
                
                {{-- Search Bar & Action Bar --}}
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-12">
                        <div class="input-group shadow-sm rounded-3 overflow-hidden border pc-border-adaptive">
                            <span class="input-group-text border-0 pc-input-adaptive ps-3 text-info">
                                <i class="bi bi-search fs-6"></i>
                            </span>
                            <input type="text" id="searchLookupPemohonInput" class="form-control border-0 pc-input-adaptive ps-1"
                                   placeholder="Ketik untuk mencari nama pemohon..." autocomplete="off">
                            <button type="button" class="btn btn-link pc-text-muted-adaptive border-0 pe-3 d-none" id="btnClearSearchLookupPemohon" onclick="$('#searchLookupPemohonInput').val('').trigger('input').focus();">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Quick Pick Baru dari yang diketik --}}
                <div id="lookupPemohonCustomNotice" class="alert alert-info border border-info rounded-3 p-2.5 mb-3 d-none d-flex justify-content-between align-items-center bg-info-subtle text-info-emphasis">
                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                        <i class="bi bi-person-plus text-info fs-5 flex-shrink-0"></i>
                        <div class="text-truncate">
                            <span class="pc-text-muted-adaptive small d-block">Gunakan nama yang Anda ketik:</span>
                            <strong class="pc-text-title-adaptive text-truncate" id="lookupPemohonTypedName"></strong>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 flex-shrink-0 fw-semibold" id="btnUseCustomTypedName">
                        <i class="bi bi-check2 me-1"></i>Pakai Nama Ini
                    </button>
                </div>

                {{-- Result Table Wrapper --}}
                <div class="rounded-3 border shadow-sm overflow-hidden pc-table-jurnal-wrapper" 
                     style="max-height: 400px; overflow-y: auto !important; -webkit-overflow-scrolling: touch;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 pc-table-jurnal">
                            <thead class="small text-uppercase fw-bold sticky-top" style="font-size: 11px; letter-spacing: 0.5px;">
                                <tr>
                                    <th style="width: 45px;" class="text-center py-2.5 pc-text-muted-adaptive">No</th>
                                    <th class="py-2.5 pc-text-muted-adaptive">Nama Pemohon</th>
                                    <th class="py-2.5 text-center pc-text-muted-adaptive">Riwayat Pengajuan</th>
                                    <th class="py-2.5 text-center pc-text-muted-adaptive">Terakhir</th>
                                    <th style="width: 100px;" class="text-center py-2.5 pc-text-muted-adaptive">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyLookupPemohon">
                                @if(isset($pemohonStats) && $pemohonStats->isNotEmpty())
                                    @foreach($pemohonStats as $index => $ps)
                                        <tr class="row-pemohon-lookup" data-nama="{{ strtolower($ps->nama_pengaju) }}">
                                            <td class="text-center py-2.5 pc-text-muted-adaptive small row-number">{{ $index + 1 }}</td>
                                            <td class="py-2.5">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-info fw-bold"
                                                         style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.3); font-size: 12px;">
                                                        {{ strtoupper(substr($ps->nama_pengaju, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold pc-text-title-adaptive pemohon-name-display">{{ $ps->nama_pengaju }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center py-2.5">
                                                <span class="badge rounded-pill bg-info-subtle border border-info-subtle text-info px-2.5 py-1 small">
                                                    <i class="bi bi-receipt me-1"></i>{{ $ps->total_trx }}x Transaksi
                                                </span>
                                            </td>
                                            <td class="text-center py-2.5 small pc-text-muted-adaptive">
                                                <i class="bi bi-clock-history me-1 pc-text-muted-adaptive"></i>
                                                {{ $ps->terakhir_tgl ? \Carbon\Carbon::parse($ps->terakhir_tgl)->translatedFormat('d M Y') : '-' }}
                                            </td>
                                            <td class="text-center py-2.5">
                                                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-xs fw-semibold btn-pilih-pemohon-item"
                                                        data-nama="{{ $ps->nama_pengaju }}">
                                                    <i class="bi bi-check2-circle me-1"></i>Pilih
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyPemohonRowInitial">
                                        <td colspan="5" class="text-center py-5 pc-text-muted-adaptive">
                                            <i class="bi bi-people fs-1 d-block mb-2 pc-text-muted-adaptive opacity-50"></i>
                                            Belum ada riwayat nama pemohon pada entitas ini.<br>
                                            <small class="pc-text-muted-adaptive">Ketik nama pada kolom pencarian di atas untuk memasukkan nama baru.</small>
                                        </td>
                                    </tr>
                                @endif
                                <tr id="noMatchPemohonRow" class="d-none">
                                    <td colspan="5" class="text-center py-4 pc-text-muted-adaptive">
                                        <i class="bi bi-search fs-2 d-block mb-2 text-warning opacity-75"></i>
                                        Tidak ada riwayat nama yang cocok dengan kata kunci.<br>
                                        <small class="pc-text-muted-adaptive">Klik tombol <strong>"Pakai Nama Ini"</strong> di atas untuk tetap menggunakan nama tersebut.</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer pc-modal-footer p-2.5 px-3 border-0 d-flex justify-content-between align-items-center">
                <div class="small pc-text-muted-adaptive">
                    <span id="lookupPemohonCountDisplay">{{ isset($pemohonStats) ? $pemohonStats->count() : 0 }}</span> riwayat pemohon terdaftar
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
