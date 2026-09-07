<div class="modal fade" id="modalLookupKategoriBarang" tabindex="-1" aria-hidden="true" style="z-index: 1075;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 860px; width: 95vw;">
        <div class="modal-content spk-lookup-modal-content rounded-4 border overflow-hidden shadow-2xl">
            
            {{-- MODAL HEADER (CINEMATIC GRADIENT) --}}
            <div class="modal-header spk-modal-header border-bottom border-secondary border-opacity-25 px-4 py-3.5" style="background: linear-gradient(135deg, #0b132b 0%, #1c2541 50%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 text-info border border-info border-opacity-30 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.15); box-shadow: 0 0 20px rgba(14, 165, 233, 0.35); width: 44px; height: 44px;">
                        <i class="bi bi-folder2-open fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.15rem; letter-spacing: -0.2px;">Cari &amp; Pilih Kategori Barang</h5>
                            <span class="badge font-monospace px-2.5 py-0.5" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 0.68rem; letter-spacing: 0.5px;">CATEGORY SELECTOR</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.8rem;">Pilih kategori yang sesuai untuk mengelompokkan produk master barang.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- MODAL BODY --}}
            <div class="modal-body spk-lookup-modal-body p-4">
                
                {{-- FILTER TOOLBAR --}}
                <div class="p-3 rounded-3 mb-3.5 spk-lookup-card-section shadow-sm">
                    <div class="input-group">
                        <span class="input-group-text spk-form-input border-end-0 text-info"><i class="bi bi-search"></i></span>
                        <input type="text" id="lookupKategoriSearch" class="form-control spk-form-input border-start-0 ps-0 fw-semibold" placeholder="Ketik Kode atau Nama Kategori..." autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="btnResetLookupKategoriSearch" title="Hapus Pencarian">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                {{-- TABLE DATA --}}
                <div class="table-responsive rounded-3 border border-secondary border-opacity-20 shadow-sm spk-lookup-table-wrap" style="max-height: 52vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 spk-kategori-table" id="tableLookupKategori">
                        <thead class="sticky-top font-monospace spk-lookup-table-header" style="z-index: 10;">
                            <tr>
                                <th style="width: 150px;" class="py-2.5 ps-3.5">KODE</th>
                                <th class="py-2.5">NAMA KATEGORI</th>
                                <th style="width: 230px;" class="py-2.5">KATEGORI INDUK (PARENT)</th>
                                <th style="width: 120px;" class="text-center py-2.5">STATUS</th>
                                <th style="width: 110px;" class="text-center py-2.5 pe-3.5">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupKategori">
                            @forelse($categoriesList ?? $kategoriOptions as $kat)
                                @php
                                    $kId = is_array($kat) ? ($kat['id'] ?? null) : $kat->id;
                                    $kKode = is_array($kat) ? ($kat['kode_kategori'] ?? ('KAT-' . $kId)) : ($kat->kode_kategori ?? ('KAT-' . $kat->id));
                                    $kNama = is_array($kat) ? ($kat['text'] ?? $kat['nama_kategori'] ?? '-') : $kat->nama_kategori;
                                    $kParent = is_array($kat) ? '-' : ($kat->parent ? $kat->parent->nama_kategori : '-');
                                    $kStatus = is_array($kat) ? 'Active' : ($kat->status ?? 'Active');
                                @endphp
                                <tr class="lookup-kategori-row"
                                    data-search="{{ strtolower($kKode . ' ' . $kNama . ' ' . $kParent) }}"
                                    data-id="{{ $kId }}"
                                    data-kode="{{ $kKode }}"
                                    data-nama="{{ $kNama }}">
                                    
                                    <td class="ps-3.5 font-monospace">
                                        <span class="badge spk-badge-kode px-2.5 py-1"><i class="bi bi-tag-fill me-1 opacity-75"></i>{{ $kKode }}</span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2 py-0.5">
                                            <i class="bi bi-folder-fill text-warning fs-5"></i>
                                            <span class="fw-bold spk-kategori-name" style="font-size: 0.88rem;">{{ $kNama }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        @if($kParent && $kParent !== '-')
                                            <span class="badge spk-badge-parent px-2.5 py-1">
                                                <i class="bi bi-diagram-3-fill me-1 text-info"></i>{{ $kParent }}
                                            </span>
                                        @else
                                            <span class="badge spk-badge-root px-2.5 py-1">
                                                <i class="bi bi-stars me-1 text-warning"></i>Kategori Utama
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($kStatus === 'Active')
                                            <span class="badge spk-badge-status-aktif px-2.5 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge spk-badge-status-inaktif px-2.5 py-1">
                                                <i class="bi bi-dash-circle-fill me-1"></i>Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-3.5">
                                        <button type="button" class="btn btn-sm btn-pilih-kategori px-3 py-1 font-monospace fw-bold" onclick="selectKategoriFromLookup(this)">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data kategori barang terdaftar.</td>
                                </tr>
                            @endforelse
                            <tr id="rowNoLookupKategoriResults" style="display: none;">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <div class="p-3">
                                        <i class="bi bi-search text-secondary fs-3 d-block mb-1 opacity-50"></i>
                                        <span class="fw-semibold">Tidak ada kategori yang cocok dengan pencarian.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2.5 border-top border-secondary border-opacity-15">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge font-monospace px-2.5 py-1" id="lookupKategoriCountInfo" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4);">
                            Menampilkan {{ count($categoriesList ?? $kategoriOptions) }} kategori
                        </span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 font-monospace" data-bs-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ===== LOOKUP KATEGORI MODAL THEME SYSTEM (DARK & LIGHT MODE) ===== */
    
    /* Light Mode */
    html:not([data-bs-theme="dark"]) .spk-lookup-modal-content {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.15) !important;
    }
    html:not([data-bs-theme="dark"]) .spk-lookup-modal-body {
        background-color: #f8fafc !important;
    }
    html:not([data-bs-theme="dark"]) .spk-lookup-card-section {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-form-input {
        background-color: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-form-input:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
    }
    html:not([data-bs-theme="dark"]) .spk-lookup-table-header th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-weight: 700;
        font-size: 0.76rem;
        letter-spacing: 0.5px;
    }
    html:not([data-bs-theme="dark"]) .lookup-kategori-row {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-bottom: 1px solid #e2e8f0 !important;
        transition: all 0.15s ease;
    }
    html:not([data-bs-theme="dark"]) .lookup-kategori-row:hover {
        background-color: #f0f9ff !important;
    }
    html:not([data-bs-theme="dark"]) .spk-kategori-name {
        color: #0f172a !important;
    }
    html:not([data-bs-theme="dark"]) .spk-badge-kode {
        background-color: #f1f5f9 !important;
        color: #0284c7 !important;
        border: 1px solid #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-badge-parent {
        background-color: #f8fafc !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-badge-root {
        background-color: #fefce8 !important;
        color: #854d0e !important;
        border: 1px solid #fef08a !important;
    }
    html:not([data-bs-theme="dark"]) .spk-badge-status-aktif {
        background-color: #ecfdf5 !important;
        color: #047857 !important;
        border: 1px solid #a7f3d0 !important;
    }
    html:not([data-bs-theme="dark"]) .spk-badge-status-inaktif {
        background-color: #f1f5f9 !important;
        color: #64748b !important;
        border: 1px solid #cbd5e1 !important;
    }
    html:not([data-bs-theme="dark"]) .btn-pilih-kategori {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 50rem !important;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3) !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }
    html:not([data-bs-theme="dark"]) .btn-pilih-kategori:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
    }

    /* Dark Mode */
    html[data-bs-theme="dark"] .spk-lookup-modal-content {
        background-color: #0b132b !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.85), 0 0 0 1px rgba(56, 189, 248, 0.18) !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-modal-body {
        background-color: #070d19 !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-card-section {
        background-color: #0b132b !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    html[data-bs-theme="dark"] .spk-form-input {
        background-color: #111c38 !important;
        color: #f8fafc !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
    }
    html[data-bs-theme="dark"] .spk-form-input:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
    }
    html[data-bs-theme="dark"] .spk-lookup-table-header th {
        background: linear-gradient(135deg, #111c38 0%, #0f172a 100%) !important;
        color: #f8fafc !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.18) !important;
        font-weight: 700;
        font-size: 0.76rem;
        letter-spacing: 0.5px;
    }
    html[data-bs-theme="dark"] .lookup-kategori-row {
        background-color: #0b132b !important;
        color: #f1f5f9 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07) !important;
        transition: all 0.15s ease;
    }
    html[data-bs-theme="dark"] .lookup-kategori-row:hover {
        background-color: rgba(14, 165, 233, 0.16) !important;
    }
    html[data-bs-theme="dark"] .spk-kategori-name {
        color: #ffffff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }
    html[data-bs-theme="dark"] .spk-badge-kode {
        background: rgba(14, 165, 233, 0.15) !important;
        color: #38bdf8 !important;
        border: 1px solid rgba(56, 189, 248, 0.4) !important;
    }
    html[data-bs-theme="dark"] .spk-badge-parent {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #e2e8f0 !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    html[data-bs-theme="dark"] .spk-badge-root {
        background: rgba(234, 179, 8, 0.15) !important;
        color: #facc15 !important;
        border: 1px solid rgba(234, 179, 8, 0.35) !important;
    }
    html[data-bs-theme="dark"] .spk-badge-status-aktif {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #34d399 !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
    }
    html[data-bs-theme="dark"] .spk-badge-status-inaktif {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    html[data-bs-theme="dark"] .btn-pilih-kategori {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 50rem !important;
        box-shadow: 0 2px 10px rgba(16, 185, 129, 0.35) !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    }
    html[data-bs-theme="dark"] .btn-pilih-kategori:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.5) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let activeLookupKategoriTargetType = 'tambah'; // 'tambah' or 'edit'

    window.openLookupKategoriBarang = function(targetType = 'tambah') {
        activeLookupKategoriTargetType = targetType;
        $('#lookupKategoriSearch').val('');
        filterLookupKategoriTable();
        
        $('#modalLookupKategoriBarang').modal('show');
        
        setTimeout(() => {
            $('#lookupKategoriSearch').focus();
        }, 300);
    };

    window.filterLookupKategoriTable = function() {
        const query = ($('#lookupKategoriSearch').val() || '').toLowerCase().trim();
        let visibleCount = 0;
        
        $('#tbodyLookupKategori tr.lookup-kategori-row').each(function() {
            const rowText = $(this).text().toLowerCase();
            const searchAttr = ($(this).attr('data-search') || '').toLowerCase();

            let matches = (query === '') || (rowText.indexOf(query) !== -1) || (searchAttr.indexOf(query) !== -1);
            if (matches) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        if (visibleCount === 0 && $('#tbodyLookupKategori tr.lookup-kategori-row').length > 0) {
            $('#rowNoLookupKategoriResults').show();
        } else {
            $('#rowNoLookupKategoriResults').hide();
        }

        $('#lookupKategoriCountInfo').text(`Menampilkan ${visibleCount} kategori`);
    };

    $(document).ready(function() {
        $(document).on('input keyup search', '#lookupKategoriSearch', function() {
            filterLookupKategoriTable();
        });

        $(document).on('click', '#btnResetLookupKategoriSearch', function() {
            $('#lookupKategoriSearch').val('');
            filterLookupKategoriTable();
            $('#lookupKategoriSearch').focus();
        });
    });

    window.selectKategoriFromLookup = function(btnElem) {
        const row = $(btnElem).closest('tr');
        const katId = row.attr('data-id');
        const katNama = row.attr('data-nama');
        const katKode = row.attr('data-kode');

        const displayText = `[${katKode}] ${katNama}`;

        if (activeLookupKategoriTargetType === 'tambah') {
            $('#tambah_kategori_barang_id').val(katId);
            $('#tambah_kategori_display').val(displayText);
        } else {
            $('#edit_kategori_barang_id').val(katId);
            $('#edit_kategori_display').val(displayText);
        }

        $('#modalLookupKategoriBarang').modal('hide');
    };
</script>
@endpush
