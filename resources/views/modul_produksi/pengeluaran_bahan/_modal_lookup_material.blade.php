{{-- MODAL LOOKUP CARI BAHAN SPK (ULTRA MODERN & CINEMATIC) --}}
<div class="modal fade spk-modal-secondary" id="modalLookupBahanSpk" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1090;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content spk-modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header spk-modal-header-dark py-3 px-3 px-sm-4">
                <div class="d-flex align-items-center gap-2 gap-sm-3 me-auto">
                    <div class="p-2 rounded-3 text-white flex-shrink-0" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0 fs-6">Pilih Bahan Baku dari SPK</h5>
                        <small class="text-white-50">Klik pada baris bahan yang ingin dikeluarkan dari gudang</small>
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
                    <input type="text" id="searchBahanSpkInput" class="form-control spk-input border-start-0 fs-6"
                           placeholder="Ketik kode atau nama bahan untuk memfilter..." autocomplete="off" oninput="filterLookupBahanSpk(this.value)">
                </div>

                {{-- Result Table / Cards --}}
                <div class="spk-table-wrapper rounded-3" style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="table table-hover align-middle mb-0 spk-table">
                        <thead>
                            <tr class="small text-uppercase fw-bold spk-text-muted">
                                <th class="ps-3">KODE & NAMA BAHAN</th>
                                <th class="text-center" style="width: 110px;">TOTAL SPK</th>
                                <th class="text-center" style="width: 110px;">SDH DIAMBIL</th>
                                <th class="text-center" style="width: 120px;">SISA KEBUTUHAN</th>
                                <th class="text-center" style="width: 120px;">STOK GUDANG</th>
                                <th class="text-center pe-3" style="width: 90px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLookupBahanSpk" class="spk-text-main">
                            {{-- Populated dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer spk-modal-footer d-flex justify-content-between p-3 p-sm-4">
                <small class="spk-text-muted"><i class="bi bi-info-circle me-1"></i>Hanya bahan yang masih memiliki sisa kuantitas & stok tersedia di gudang yang dapat dipilih.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeTargetRowIdx = null;

    function bukaModalLookupBahan(rowIdx) {
        const spkId = $('#inp_spk_id').val();
        if (!spkId || !currentSpkMaterials || currentSpkMaterials.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih SPK Dahulu!',
                text: 'Silakan pilih SPK Produksi terlebih dahulu di bagian atas.',
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Pilih SPK'
            }).then((result) => {
                if (result.isConfirmed) {
                    bukaModalLookupSpk();
                }
            });
            return;
        }

        activeTargetRowIdx = (typeof rowIdx === 'number') ? rowIdx : null;
        $('#searchBahanSpkInput').val('');
        renderLookupBahanTable(currentSpkMaterials);
        $('#modalLookupBahanSpk').modal('show');
    }

    function renderLookupBahanTable(materials) {
        if (!materials || materials.length === 0) {
            $('#tbodyLookupBahanSpk').html(`
                <tr>
                    <td colspan="6" class="text-center py-4 spk-text-muted">
                        Tidak ada data bahan pada SPK ini.
                    </td>
                </tr>
            `);
            return;
        }

        // Get list of already added material IDs
        const existingSpkMatIds = [];
        $('.row-pengeluaran').each(function() {
            const idVal = $(this).find('.inp-spk-mat-id').val();
            if (idVal) existingSpkMatIds.push(Number(idVal));
        });

        let html = '';
        materials.forEach((m) => {
            const isFull = (m.qty_sisa_kebutuhan || 0) <= 0;
            const isAlreadyAdded = existingSpkMatIds.includes(Number(m.spk_material_id));
            const stokAda = (m.stok_tersedia_gudang || 0) > 0;
            const stokBadge = stokAda 
                ? `<span class="badge spk-badge-green font-monospace px-2 py-0.5" style="font-size: 11px;">${Number(m.stok_tersedia_gudang).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}</span>`
                : `<span class="badge spk-badge-muted font-monospace px-2 py-0.5" style="font-size: 11px;">0 (Kosong)</span>`;

            html += `
                <tr class="cursor-pointer hover-row ${isAlreadyAdded ? 'opacity-75 bg-light' : ''}"
                    onclick="pilihBahanBySpkMatId(${m.spk_material_id})" style="cursor: pointer;">
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-box-seam fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold spk-text-main">
                                    [${m.kode_barang}] ${m.nama_barang}
                                    ${isAlreadyAdded ? '<span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill px-2 py-0.5 ms-1" style="font-size: 10.5px;"><i class="bi bi-check2"></i> Di Tabel</span>' : ''}
                                </div>
                                <small class="spk-text-muted">Satuan: <b>${m.satuan_nama}</b></small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center font-monospace small">${Number(m.qty_total_spk).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}</td>
                    <td class="text-center font-monospace small spk-text-muted">${Number(m.qty_sudah_diambil).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}</td>
                    <td class="text-center font-monospace small fw-bold ${isFull ? 'text-success' : 'text-primary'}">
                        ${Number(m.qty_sisa_kebutuhan).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}
                        ${isFull ? '<br><span class="badge spk-badge-green rounded-pill px-2 py-0.5" style="font-size: 10px;">Lengkap</span>' : ''}
                    </td>
                    <td class="text-center">
                        ${stokBadge}
                    </td>
                    <td class="text-center pe-3">
                        <button type="button" class="btn btn-sm ${isAlreadyAdded ? 'btn-outline-secondary' : 'btn-primary'} rounded-pill px-3 py-1 fw-semibold shadow-xs"
                                onclick="event.stopPropagation(); pilihBahanBySpkMatId(${m.spk_material_id});">
                            <i class="bi ${isAlreadyAdded ? 'bi-check2' : 'bi-plus-lg'} me-1"></i> ${isAlreadyAdded ? 'Pilih' : 'Pilih'}
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tbodyLookupBahanSpk').html(html);
    }

    function filterLookupBahanSpk(keyword) {
        const q = (keyword || '').toLowerCase().trim();
        if (!q) {
            renderLookupBahanTable(currentSpkMaterials);
            return;
        }

        const filtered = (currentSpkMaterials || []).filter(m => {
            return (m.kode_barang || '').toLowerCase().includes(q) ||
                   (m.nama_barang || '').toLowerCase().includes(q);
        });

        renderLookupBahanTable(filtered);
    }

    function pilihBahanBySpkMatId(spkMatId) {
        if (!currentSpkMaterials || currentSpkMaterials.length === 0) return;

        const m = currentSpkMaterials.find(item => Number(item.spk_material_id) === Number(spkMatId));
        if (!m) return;

        const stokGudang = Number(m.stok_tersedia_gudang || 0);
        const namaGudang = $('#display_gudang_pilihan').val() || 'Gudang Terpilih';

        // Peringatan Keamanan: Cek ketersediaan stok fisik di gudang yang dipilih
        if (stokGudang <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Gudang Kosong!',
                html: `Bahan <b>[${m.kode_barang}] ${m.nama_barang}</b> saat ini <b>tidak memiliki stok fisik (0 ${m.satuan_nama})</b> di <u>${namaGudang}</u>.<br><br><small class="text-muted">Silakan pilih bahan lain atau sesuaikan gudang asal pengeluaran.</small>`,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            return;
        }

        let targetRowIdx = activeTargetRowIdx;

        // Check if material already exists in another row
        let duplicateRow = null;
        $('.row-pengeluaran').each(function() {
            const thisRowId = $(this).attr('id');
            const existingSpkMatId = $(this).find('.inp-spk-mat-id').val();
            if (existingSpkMatId && Number(existingSpkMatId) === Number(m.spk_material_id)) {
                if (targetRowIdx === null || thisRowId !== `row_pengeluaran_${targetRowIdx}`) {
                    duplicateRow = thisRowId;
                }
            }
        });

        if (duplicateRow) {
            Swal.fire({
                icon: 'warning',
                title: 'Bahan Sudah Ada di Daftar!',
                text: `Item bahan "[${m.kode_barang}] ${m.nama_barang}" sudah ada pada daftar pengeluaran. Silakan sesuaikan kuantitas pada baris tersebut.`,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Mengerti'
            });
            $('#modalLookupBahanSpk').modal('hide');
            return;
        }

        // If target row is specified and exists, update that row
        if (targetRowIdx !== null && $(`#row_pengeluaran_${targetRowIdx}`).length > 0) {
            const sisa = Number(m.qty_sisa_kebutuhan || 0);
            $(`#spk_mat_id_${targetRowIdx}`).val(m.spk_material_id);
            $(`#barang_mentah_id_${targetRowIdx}`).val(m.barang_mentah_id);
            $(`#stok_gudang_${targetRowIdx}`).val(stokGudang);
            $(`#txt_mat_display_${targetRowIdx}`).val(`[${m.kode_barang}] ${m.nama_barang}`).addClass('fw-bold text-primary');
            $(`#lbl_total_spk_${targetRowIdx}`).text(`${Number(m.qty_total_spk).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}`);
            $(`#lbl_sdh_diambil_${targetRowIdx}`).text(`${Number(m.qty_sudah_diambil).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}`);
            $(`#lbl_sisa_spk_${targetRowIdx}`).html(`
                ${Number(m.qty_sisa_kebutuhan).toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}
                ${m.qty_sisa_kebutuhan <= 0 ? '<br><span class="badge spk-badge-green rounded-pill px-2 py-0.5" style="font-size: 10px;">Lengkap</span>' : ''}
            `);
            $(`#lbl_stok_gudang_${targetRowIdx}`).html(`
                <span class="badge spk-badge-green font-monospace px-2 py-0.5" style="font-size: 11px;">
                    ${stokGudang.toLocaleString('id-ID', {maximumFractionDigits: 4})} ${m.satuan_nama}
                </span>
            `);
            $(`#lbl_satuan_${targetRowIdx}`).text(m.satuan_nama);

            $(`#inp_qty_taken_${targetRowIdx}`)
                .prop('disabled', false)
                .val('')
                .attr('placeholder', '0')
                .focus();

            if (sisa > 0) {
                $(`#btn_max_${targetRowIdx}`)
                    .removeClass('d-none')
                    .off('click')
                    .on('click', function() {
                        const takeVal = Math.min(sisa, stokGudang);
                        $(`#inp_qty_taken_${targetRowIdx}`).val(takeVal).trigger('input').focus();
                    });
            } else {
                $(`#btn_max_${targetRowIdx}`).addClass('d-none');
            }
        } else {
            // Find first empty row without material or create a new row
            let emptyRowIdx = null;
            $('.row-pengeluaran').each(function() {
                const idVal = $(this).find('.inp-spk-mat-id').val();
                if (!idVal) {
                    const rowIdStr = $(this).attr('id');
                    emptyRowIdx = parseInt(rowIdStr.replace('row_pengeluaran_', ''), 10);
                    return false;
                }
            });

            if (emptyRowIdx !== null) {
                activeTargetRowIdx = emptyRowIdx;
                pilihBahanBySpkMatId(spkMatId);
                return;
            } else {
                tambahBarisPengeluaran(false);
                activeTargetRowIdx = rowCounter - 1;
                pilihBahanBySpkMatId(spkMatId);
                return;
            }
        }

        $('#modalLookupBahanSpk').modal('hide');
        activeTargetRowIdx = null;
    }
</script>
@endpush
