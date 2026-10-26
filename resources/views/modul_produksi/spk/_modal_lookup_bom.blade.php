{{-- MODAL LOOKUP RESEP BOM (APPROVED) --}}
<div class="modal fade spk-modal-secondary" id="modalLookupBomSpk" tabindex="-1" aria-labelledby="modalLookupBomSpkLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- HEADER --}}
            <div class="modal-header spk-modal-header-dark py-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white fs-6 fs-sm-5" id="modalLookupBomSpkLabel">Pilih Resep BOM Produksi (Approved)</h5>
                        <small class="text-white-50 d-none d-sm-block">Hanya resep BOM berstatus Disetujui (Approved) yang dapat dibuatkan SPK</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body spk-modal-body p-3 p-sm-4">
                {{-- Search Box --}}
                <div class="input-group mb-3 shadow-xs">
                    <span class="input-group-text spk-input-group-text rounded-start-3 border">
                        <i class="bi bi-search text-primary"></i>
                    </span>
                    <input type="text" id="inputSearchBomLookup"
                           class="form-control spk-input border-start-0 rounded-end-3"
                           placeholder="Cari berdasarkan Kode BOM, Nama Resep, atau Nama Barang Jadi..."
                           autocomplete="off"
                           onkeyup="cariBomLookup(this.value)">
                </div>

                {{-- Table BOM (Horizontal Swipe & Vertical Scrollable) --}}
                <div class="spk-table-wrapper rounded-3" style="border: 1px solid var(--spk-border-color); max-height: 420px; overflow-x: auto; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0 text-nowrap spk-table" style="min-width: 680px;">
                        <thead>
                            <tr class="small text-uppercase fw-bold">
                                <th style="width: 50px;" class="text-center sticky-top bg-body">No</th>
                                <th class="sticky-top bg-body">Kode & Nama BOM</th>
                                <th class="sticky-top bg-body">Barang Jadi (Hasil)</th>
                                <th class="text-center sticky-top bg-body">Tipe</th>
                                <th class="text-end sticky-top bg-body">Est. HPP / Unit</th>
                                <th style="width: 100px;" class="text-center sticky-top bg-body">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupBom">
                            <tr>
                                <td colspan="6" class="text-center py-5 spk-text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Memuat daftar resep BOM...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer spk-modal-footer d-flex justify-content-between p-3 p-sm-4">
                <small class="spk-text-muted d-none d-sm-inline">
                    <i class="bi bi-arrows-arrows text-primary me-1"></i> Geser tabel ke samping jika kolom terpotong.
                </small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 ms-auto" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let lookupBomTimeout = null;
    let currentBomLookupTargetMode = 'input';

    function bukaModalLookupBomSpk(mode = 'input') {
        currentBomLookupTargetMode = mode;
        $('#inputSearchBomLookup').val('');
        $('#modalLookupBomSpk').modal('show');
        loadBomLookupData('');
        setTimeout(() => $('#inputSearchBomLookup').focus(), 400);
    }

    function handlePilihBomLookup(id, kode, nama, bjNama, bjKode, bjId, satId, satNama, tipeBom, tenderObj) {
        if (currentBomLookupTargetMode === 'edit') {
            if (typeof pilihBomEditSpk === 'function') {
                pilihBomEditSpk(id, kode, nama, bjNama, bjKode, bjId, satId, satNama, tipeBom, tenderObj);
            }
        } else {
            if (typeof pilihBomSpk === 'function') {
                pilihBomSpk(id, kode, nama, bjNama, bjKode, bjId, satId, satNama, tipeBom, tenderObj);
            }
        }
    }

    function cariBomLookup(val) {
        clearTimeout(lookupBomTimeout);
        lookupBomTimeout = setTimeout(function() {
            loadBomLookupData(val);
        }, 300);
    }

    function loadBomLookupData(q) {
        $('#tbodyLookupBom').html(`
            <tr>
                <td colspan="6" class="text-center py-4 spk-text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Mencari resep BOM...
                </td>
            </tr>
        `);

        $.ajax({
            url: '{{ route("produksi.spk.search-bom") }}',
            method: 'GET',
            data: { q: q },
            success: function(res) {
                const boms = res.boms || [];
                if (boms.length === 0) {
                    $('#tbodyLookupBom').html(`
                        <tr>
                            <td colspan="6" class="text-center py-5 spk-text-muted">
                                <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                                Tidak ada resep BOM Approved yang ditemukan.
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                boms.forEach((b, index) => {
                    const hppFmt = 'Rp ' + Number(b.total_hpp).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    const tipeBadge = b.tipe_bom
                        ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1">${b.tipe_bom}</span>`
                        : `<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-1">Garmen</span>`;

                    const tenderBadge = b.nama_tender
                        ? `<div class="mt-1"><span class="badge rounded-pill px-2 py-0.5" style="background:rgba(139,92,246,0.12);color:#7c3aed;border:1px solid rgba(139,92,246,0.3);font-size:11px;"><i class="bi bi-file-earmark-text me-1"></i>${b.nama_tender}</span></div>`
                        : '';

                    const tenderJson = JSON.stringify({
                        id: b.tender_id,
                        nomor_kontrak: b.nomor_kontrak || '',
                        nama_tender: b.nama_tender || '',
                        nama_customer: b.nama_customer || ''
                    }).replace(/'/g, "&apos;");

                    html += `
                        <tr>
                            <td class="text-center spk-text-muted font-monospace">${index + 1}</td>
                            <td>
                                <div class="fw-bold text-primary font-monospace" style="font-size: 13.5px;">${b.kode_bom}</div>
                                <small class="spk-text-muted d-block">${b.nama_bom}</small>
                                ${tenderBadge}
                            </td>
                            <td>
                                <div class="fw-semibold spk-text-main">[${b.barang_jadi_kode || '-'}] ${b.barang_jadi_nama || '-'}</div>
                                <small class="spk-text-muted"><i class="bi bi-layers me-1 text-primary"></i>${b.items_count} komposisi bahan</small>
                            </td>
                            <td class="text-center">
                                ${tipeBadge}
                            </td>
                            <td class="text-end fw-bold text-success font-monospace" style="font-size: 13.5px;">
                                ${hppFmt}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs fw-bold"
                                        onclick='handlePilihBomLookup(${b.id}, "${b.kode_bom}", "${(b.nama_bom || '').replace(/"/g, '&quot;')}", "${(b.barang_jadi_nama || '').replace(/"/g, '&quot;')}", "${b.barang_jadi_kode || ''}", ${b.barang_jadi_id}, ${b.satuan_id}, "${b.satuan_nama || 'Pcs'}", "${b.tipe_bom || ''}", ${tenderJson})'>
                                    <i class="bi bi-check-lg me-1"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#tbodyLookupBom').html(html);
            },
            error: function() {
                $('#tbodyLookupBom').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data resep BOM.
                        </td>
                    </tr>
                `);
            }
        });
    }
</script>
@endpush
