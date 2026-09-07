<div class="modal fade" id="modalLookupKomponenBarang" tabindex="-1" aria-hidden="true" style="z-index: 1075;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1080px; width: 95vw;">
        <div class="modal-content spk-lookup-modal-content rounded-4 border overflow-hidden shadow-2xl">
            
            {{-- MODAL HEADER (CINEMATIC GRADIENT) --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3.5" style="background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); box-shadow: 0 0 20px rgba(14, 165, 233, 0.35); width: 44px; height: 44px;">
                        <i class="bi bi-search fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Cari &amp; Pilih Barang Penyusun Paket</h5>
                            <span class="badge font-monospace px-2.5 py-0.5" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.68rem; letter-spacing: 0.5px;">ITEM SELECTOR</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.8rem;">Pilih barang jadi / barang stok untuk dijadikan komponen paket bundle.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body spk-lookup-modal-body p-4">
                
                {{-- FILTER TOOLBAR (MODERN FLOATING BAR) --}}
                <div class="p-3 rounded-3 mb-3.5 spk-lookup-card-section shadow-sm">
                    <div class="row g-2.5 align-items-center">
                        <div class="col-12 col-md-7">
                            <div class="input-group">
                                <span class="input-group-text spk-form-input border-end-0 text-info"><i class="bi bi-search"></i></span>
                                <input type="text" id="lookupKomponenSearch" class="form-control spk-form-input border-start-0 ps-0 fw-semibold" placeholder="Ketik Kode, Nama Barang, atau Merk..." autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="btnResetLookupSearch" title="Hapus Pencarian">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text spk-form-input border-end-0 text-muted"><i class="bi bi-funnel-fill"></i></span>
                                <select id="lookupKomponenCategoryFilter" class="form-select spk-form-input border-start-0 fw-semibold">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach($kategoriOptions as $kat)
                                        <option value="{{ strtolower($kat->nama_kategori) }}">{{ $kat->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-1 d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" id="btnResetAllLookupFilter" title="Reset Semua Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TABLE DATA --}}
                <div class="table-responsive rounded-3 border border-secondary border-opacity-15 shadow-sm" style="max-height: 54vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="tableLookupKomponen">
                        <thead class="sticky-top font-monospace small spk-lookup-table-header" style="z-index: 10;">
                            <tr>
                                <th style="width: 55px;" class="text-center py-2.5">FOTO</th>
                                <th class="py-2.5">KODE &amp; NAMA BARANG</th>
                                <th style="width: 170px;" class="py-2.5">KATEGORI &amp; MERK</th>
                                <th style="width: 90px;" class="text-center py-2.5">SATUAN</th>
                                <th style="width: 115px;" class="text-end py-2.5">STOK TOTAL</th>
                                <th style="width: 135px;" class="text-end py-2.5">HPP GUDANG</th>
                                <th style="width: 100px;" class="text-center py-2.5">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupKomponen">
                            @forelse($availableComponents as $comp)
                                @php
                                    $fotoPath = $comp->fotos->first()?->foto_path;
                                    $katNama = $comp->kategori ? $comp->kategori->nama_kategori : '-';
                                    $brandNama = $comp->brand ? $comp->brand->nama_brand : '-';
                                    $satSingkat = $comp->satuan ? $comp->satuan->singkatan : 'Pcs';
                                    $hppVal  = (float)($comp->hpp_dari_gudang ?? 0);
                                    $stokVal = (float)($comp->stok_total ?? 0);

                                    // Format Qty: Integer jika bulat (1, 2, 3), desimal jika pecahan (1.5)
                                    $stokFormatted = ((float)$stokVal == (int)$stokVal) ? number_format($stokVal, 0, ',', '.') : number_format($stokVal, 2, ',', '.');
                                    $hppFormatted  = ((float)$hppVal == (int)$hppVal) ? number_format($hppVal, 0, ',', '.') : number_format($hppVal, 2, ',', '.');
                                @endphp
                                <tr class="lookup-item-row spk-detail-table-row" 
                                    data-search="{{ strtolower($comp->kode_barang . ' ' . $comp->nama_barang . ' ' . $comp->nama_alias . ' ' . $brandNama . ' ' . $katNama) }}"
                                    data-category-name="{{ strtolower($katNama) }}"
                                    data-id="{{ $comp->id }}"
                                    data-kode="{{ $comp->kode_barang }}"
                                    data-nama="{{ $comp->nama_barang }}"
                                    data-satuan="{{ $satSingkat }}"
                                    data-stok="{{ $stokVal }}"
                                    data-hpp="{{ $hppVal }}">
                                    
                                    <td class="text-center">
                                        @if($fotoPath)
                                            <img src="{{ asset('storage/' . $fotoPath) }}" class="rounded-3 border shadow-xs" style="width:38px; height:38px; object-fit:cover;">
                                        @else
                                            <div class="rounded-3 border bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center text-muted" style="width:38px; height:38px;">
                                                <i class="bi bi-box-seam fs-6"></i>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="mb-0.5">
                                                <span class="badge spk-badge-bundle-code font-monospace px-2 py-0.5"><i class="bi bi-qr-code me-1"></i>{{ $comp->kode_barang }}</span>
                                            </div>
                                            <span class="fw-bold spk-bundle-title small">{{ $comp->nama_barang }}</span>
                                            @if($comp->nama_alias)
                                                <small class="spk-bundle-meta" style="font-size: 0.73rem;">Alias: {{ $comp->nama_alias }}</small>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span class="fw-semibold spk-bundle-meta"><i class="bi bi-folder2-open me-1 text-info"></i>{{ $katNama }}</span>
                                            <small class="spk-bundle-meta opacity-75">{{ $brandNama }}</small>
                                        </div>
                                    </td>

                                    <td class="text-center font-monospace small fw-bold">
                                        <span class="badge spk-badge-bundle-unit rounded-pill px-2.5 py-1">{{ $satSingkat }}</span>
                                    </td>

                                    <td class="text-end font-monospace small">
                                        @php
                                            $stokClass = $stokVal > 0 ? 'text-success' : 'text-danger';
                                        @endphp
                                        <span class="fw-bold {{ $stokClass }}">{{ $stokFormatted }}</span>
                                    </td>

                                    <td class="text-end font-monospace small">
                                        @if($hppVal > 0)
                                            <span class="fw-bold text-success font-monospace">Rp {{ $hppFormatted }}</span>
                                        @else
                                            <span class="text-muted fst-italic small">Belum ada</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($stokVal > 0)
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 font-monospace fw-bold btn-pilih-komponen shadow-xs" onclick="selectKomponenFromLookup(this)">
                                                <i class="bi bi-plus-lg me-1"></i>Pilih
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-2.5 py-1 font-monospace small opacity-60" disabled title="Stok barang ini 0 di gudang, tidak dapat dipilih">
                                                <i class="bi bi-lock-fill me-1"></i>Stok 0
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="rowEmptyInitial">
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data barang jadi / stok yang siap dipilih.</td>
                                </tr>
                            @endforelse
                            <tr id="rowNoLookupResults" style="display: none;">
                                <td colspan="7" class="text-center text-muted py-4">
                                    <div class="p-3">
                                        <i class="bi bi-search text-secondary fs-3 d-block mb-1 opacity-50"></i>
                                        <span class="fw-semibold">Tidak ada barang yang cocok dengan kata kunci pencarian / filter kategori.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2.5 border-top border-secondary border-opacity-15">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge font-monospace px-2.5 py-1" id="lookupKomponenCountInfo" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4);">Menampilkan {{ $availableComponents->count() }} barang</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 font-monospace" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* LOOKUP MODAL THEME STYLING */
    html:not([data-bs-theme="dark"]) .spk-lookup-modal-content {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-modal-content {
        background-color: #0b132b !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(56, 189, 248, 0.15) !important;
    }

    html:not([data-bs-theme="dark"]) .spk-lookup-modal-body {
        background-color: #f8fafc !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-modal-body {
        background-color: #070d19 !important;
    }

    html:not([data-bs-theme="dark"]) .spk-lookup-card-section {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-card-section {
        background-color: #0b132b !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    html:not([data-bs-theme="dark"]) .spk-lookup-table-header th {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-table-header th {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: #f1f5f9 !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.15) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let activeLookupTargetRow = null;

    window.openLookupKomponenBarang = function(triggerElem) {
        activeLookupTargetRow = $(triggerElem).closest('tr');
        $('#lookupKomponenSearch').val('');
        $('#lookupKomponenCategoryFilter').val('');
        filterLookupKomponenTable();
        
        $('#modalLookupKomponenBarang').modal('show');
        
        setTimeout(() => {
            $('#lookupKomponenSearch').focus();
        }, 300);
    };

    window.filterLookupKomponenTable = function() {
        const query = ($('#lookupKomponenSearch').val() || '').toLowerCase().trim();
        const selectedCat = ($('#lookupKomponenCategoryFilter').val() || '').toLowerCase().trim();
        
        let visibleCount = 0;
        $('#tbodyLookupKomponen tr.lookup-item-row').each(function() {
            const rowText = $(this).text().toLowerCase();
            const searchAttr = ($(this).attr('data-search') || '').toLowerCase();
            const rowCat = ($(this).attr('data-category-name') || '').toLowerCase();

            // Match query: check full text in row and data-search attribute
            let matchesQuery = true;
            if (query !== '') {
                matchesQuery = (rowText.indexOf(query) !== -1) || (searchAttr.indexOf(query) !== -1);
            }

            // Match Category: check if row category matches selected category
            let matchesCat = true;
            if (selectedCat !== '') {
                matchesCat = (rowCat.indexOf(selectedCat) !== -1) || (selectedCat.indexOf(rowCat) !== -1);
            }

            if (matchesQuery && matchesCat) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        if (visibleCount === 0 && $('#tbodyLookupKomponen tr.lookup-item-row').length > 0) {
            $('#rowNoLookupResults').show();
        } else {
            $('#rowNoLookupResults').hide();
        }

        $('#lookupKomponenCountInfo').text(`Menampilkan ${visibleCount} barang penyusun`);
    };

    $(document).ready(function() {
        $(document).on('input keyup search', '#lookupKomponenSearch', function() {
            filterLookupKomponenTable();
        });

        $(document).on('change', '#lookupKomponenCategoryFilter', function() {
            filterLookupKomponenTable();
        });

        $(document).on('click', '#btnResetLookupSearch', function() {
            $('#lookupKomponenSearch').val('');
            filterLookupKomponenTable();
            $('#lookupKomponenSearch').focus();
        });

        $(document).on('click', '#btnResetAllLookupFilter', function() {
            $('#lookupKomponenSearch').val('');
            $('#lookupKomponenCategoryFilter').val('');
            filterLookupKomponenTable();
            $('#lookupKomponenSearch').focus();
        });
    });

    window.selectKomponenFromLookup = function(btnElem) {
        if (!activeLookupTargetRow) return;

        const row = $(btnElem).closest('tr');
        const compId = row.attr('data-id');
        const compKode = row.attr('data-kode');
        const compNama = row.attr('data-nama');
        const compSatuan = row.attr('data-satuan') || 'Pcs';
        const compHpp = parseFloat(row.attr('data-hpp') || 0);
        const compStok = parseFloat(row.attr('data-stok') || 0);

        if (compStok <= 0) {
            Swal.fire({
                title: 'Stok Kosong!',
                text: `Barang '${compNama}' (${compKode}) tidak memiliki stok di gudang (Stok: 0). Barang ini tidak dapat dijadikan komponen paket bundle.`,
                icon: 'warning'
            });
            return;
        }

        const displayText = `[${compKode}] ${compNama}`;

        // Populate target row
        activeLookupTargetRow.find('.child-id-input').val(compId);
        activeLookupTargetRow.find('.child-name-input').val(displayText);
        activeLookupTargetRow.find('.satuan-label-text').text(compSatuan);
        activeLookupTargetRow.find('.edit-satuan-label-text').text(compSatuan);
        activeLookupTargetRow.find('.child-hpp-val').val(compHpp);

        // Update HPP display badge with 2 decimals
        const hppDisplay = activeLookupTargetRow.find('.child-hpp-display');
        if (hppDisplay.length) {
            hppDisplay.removeClass('text-success text-secondary');
            if (compHpp > 0) {
                hppDisplay.addClass('text-success').text('Rp ' + compHpp.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }));
            } else {
                hppDisplay.addClass('text-secondary').text('-');
            }
        }

        // Hide modal
        $('#modalLookupKomponenBarang').modal('hide');

        // Trigger auto calculation of HPP if modal Tambah / Edit is open
        if (typeof autoCalculateHppFromKomponen === 'function') {
            autoCalculateHppFromKomponen();
        }
        if (typeof autoCalculateEditHppFromKomponen === 'function') {
            autoCalculateEditHppFromKomponen();
        }
    };
</script>
@endpush
