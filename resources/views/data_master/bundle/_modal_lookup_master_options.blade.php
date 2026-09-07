{{-- ===== MODAL LOOKUP KATEGORI BARANG ===== --}}
<div class="modal fade" id="modalLookupKategori" tabindex="-1" aria-hidden="true" style="z-index: 1075;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content rounded-4 border-0 shadow-2xl">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 text-info bg-info bg-opacity-15">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.05rem;">Cari & Pilih Kategori Barang</h5>
                        <small class="text-white-50" style="font-size: 0.8rem;">Pilih kategori utama untuk klasifikasi paket bundle.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text spk-form-addon text-info"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchKategoriInput" class="form-control form-control-sm spk-form-input fw-semibold" placeholder="Ketik nama kategori..." autocomplete="off">
                </div>
                <div class="table-responsive" style="max-height: 45vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top font-monospace small" style="background-color: var(--spk-table-header-bg, #0f172a); color: var(--spk-table-header-color, #94a3b8);">
                            <tr>
                                <th>KODE & NAMA KATEGORI</th>
                                <th style="width: 80px;" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupKategori">
                            @forelse($kategoriOptions as $kat)
                                <tr class="kategori-row" data-search="{{ strtolower(($kat->kode_kategori ?? '') . ' ' . $kat->nama_kategori) }}" data-id="{{ $kat->id }}" data-nama="{{ $kat->nama_kategori }}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if(!empty($kat->kode_kategori))
                                                <span class="fw-bold font-monospace text-primary small">{{ $kat->kode_kategori }}</span>
                                            @endif
                                            <span class="fw-semibold text-body">{{ $kat->nama_kategori }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="selectKategoriFromLookup(this)">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Belum ada data kategori aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL LOOKUP BRAND / MERK ===== --}}
<div class="modal fade" id="modalLookupBrand" tabindex="-1" aria-hidden="true" style="z-index: 1075;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content rounded-4 border-0 shadow-2xl">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 text-warning bg-warning bg-opacity-15">
                        <i class="bi bi-award-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.05rem;">Cari & Pilih Brand / Merk</h5>
                        <small class="text-white-50" style="font-size: 0.8rem;">Pilih brand produsen atau pilih Tanpa Merk.</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text spk-form-addon text-warning"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchBrandInput" class="form-control form-control-sm spk-form-input fw-semibold" placeholder="Ketik nama brand/merk..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 font-monospace text-nowrap" onclick="selectBrandFromLookup('', '-- Tanpa Merk --')">
                        Tanpa Merk
                    </button>
                </div>
                <div class="table-responsive" style="max-height: 45vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top font-monospace small" style="background-color: var(--spk-table-header-bg, #0f172a); color: var(--spk-table-header-color, #94a3b8);">
                            <tr>
                                <th>KODE & NAMA BRAND</th>
                                <th style="width: 80px;" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupBrand">
                            @forelse($brands as $brand)
                                <tr class="brand-row" data-search="{{ strtolower(($brand->kode_brand ?? '') . ' ' . $brand->nama_brand) }}" data-id="{{ $brand->id }}" data-nama="{{ $brand->nama_brand }}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if(!empty($brand->kode_brand))
                                                <span class="fw-bold font-monospace text-warning small">{{ $brand->kode_brand }}</span>
                                            @endif
                                            <span class="fw-semibold text-body">{{ $brand->nama_brand }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="selectBrandFromLookupFromRow(this)">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Belum ada data brand aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL LOOKUP SATUAN DASAR ===== --}}
<div class="modal fade" id="modalLookupSatuan" tabindex="-1" aria-hidden="true" style="z-index: 1075;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content spk-modal-content rounded-4 border-0 shadow-2xl">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 text-success bg-success bg-opacity-15">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" style="font-size: 1.05rem;">Cari & Pilih Satuan Dasar</h5>
                        <small class="text-white-50" style="font-size: 0.8rem;">Pilih unit satuan kemasan paket (misal: Set, Paket, Stel).</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text spk-form-addon text-success"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchSatuanInput" class="form-control form-control-sm spk-form-input fw-semibold" placeholder="Ketik nama atau singkatan satuan..." autocomplete="off">
                </div>
                <div class="table-responsive" style="max-height: 45vh; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top font-monospace small" style="background-color: var(--spk-table-header-bg, #0f172a); color: var(--spk-table-header-color, #94a3b8);">
                            <tr>
                                <th>NAMA SATUAN</th>
                                <th style="width: 100px;" class="text-center">SINGKATAN</th>
                                <th style="width: 80px;" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupSatuan">
                            @forelse($satuans as $sat)
                                <tr class="satuan-row" data-search="{{ strtolower($sat->nama_satuan . ' ' . $sat->singkatan) }}" data-id="{{ $sat->id }}" data-nama="{{ $sat->nama_satuan }}" data-singkatan="{{ $sat->singkatan }}">
                                    <td>
                                        <span class="fw-semibold text-body">{{ $sat->nama_satuan }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-body border rounded-pill px-2.5 py-1 font-monospace fw-bold">{{ $sat->singkatan }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success text-white rounded-pill px-2.5 py-1 font-monospace fw-bold" onclick="selectSatuanFromLookup(this)">
                                            <i class="bi bi-check-lg me-1"></i>Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada data satuan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeOptionMode = 'tambah'; // 'tambah' or 'edit'

    // Kategori Lookup
    function openLookupKategori(mode = 'tambah') {
        activeOptionMode = mode;
        $('#searchKategoriInput').val('');
        filterKategoriTable();
        $('#modalLookupKategori').modal('show');
        setTimeout(() => $('#searchKategoriInput').focus(), 300);
    }

    function filterKategoriTable() {
        const q = ($('#searchKategoriInput').val() || '').toLowerCase().trim();
        $('#tbodyLookupKategori tr.kategori-row').each(function() {
            const data = $(this).data('search') || '';
            $(this).toggleClass('d-none', !!q && !data.includes(q));
        });
    }

    $('#searchKategoriInput').on('input keyup', filterKategoriTable);

    function selectKategoriFromLookup(btn) {
        const tr = $(btn).closest('tr');
        const id = tr.data('id');
        const nama = tr.data('nama');

        if (activeOptionMode === 'tambah') {
            $('#tambah_kategori_id').val(id);
            $('#tambah_kategori_nama').val(nama);
        } else {
            $('#edit_kategori_id').val(id);
            $('#edit_kategori_nama').val(nama);
        }
        $('#modalLookupKategori').modal('hide');
    }

    // Brand Lookup
    function openLookupBrand(mode = 'tambah') {
        activeOptionMode = mode;
        $('#searchBrandInput').val('');
        filterBrandTable();
        $('#modalLookupBrand').modal('show');
        setTimeout(() => $('#searchBrandInput').focus(), 300);
    }

    function filterBrandTable() {
        const q = ($('#searchBrandInput').val() || '').toLowerCase().trim();
        $('#tbodyLookupBrand tr.brand-row').each(function() {
            const data = $(this).data('search') || '';
            $(this).toggleClass('d-none', !!q && !data.includes(q));
        });
    }

    $('#searchBrandInput').on('input keyup', filterBrandTable);

    function selectBrandFromLookupFromRow(btn) {
        const tr = $(btn).closest('tr');
        selectBrandFromLookup(tr.data('id'), tr.data('nama'));
    }

    function selectBrandFromLookup(id, nama) {
        if (activeOptionMode === 'tambah') {
            $('#tambah_brand_id').val(id);
            $('#tambah_brand_nama').val(nama);
        } else {
            $('#edit_brand_id').val(id);
            $('#edit_brand_nama').val(nama);
        }
        $('#modalLookupBrand').modal('hide');
    }

    // Satuan Lookup
    function openLookupSatuan(mode = 'tambah') {
        activeOptionMode = mode;
        $('#searchSatuanInput').val('');
        filterSatuanTable();
        $('#modalLookupSatuan').modal('show');
        setTimeout(() => $('#searchSatuanInput').focus(), 300);
    }

    function filterSatuanTable() {
        const q = ($('#searchSatuanInput').val() || '').toLowerCase().trim();
        $('#tbodyLookupSatuan tr.satuan-row').each(function() {
            const data = $(this).data('search') || '';
            $(this).toggleClass('d-none', !!q && !data.includes(q));
        });
    }

    $('#searchSatuanInput').on('input keyup', filterSatuanTable);

    function selectSatuanFromLookup(btn) {
        const tr = $(btn).closest('tr');
        const id = tr.data('id');
        const nama = tr.data('nama');
        const singkatan = tr.data('singkatan');
        const textDisplay = `${nama} (${singkatan})`;

        if (activeOptionMode === 'tambah') {
            $('#tambah_satuan_id').val(id);
            $('#tambah_satuan_nama').val(textDisplay);
        } else {
            $('#edit_satuan_id').val(id);
            $('#edit_satuan_nama').val(textDisplay);
        }
        $('#modalLookupSatuan').modal('hide');
    }
</script>
