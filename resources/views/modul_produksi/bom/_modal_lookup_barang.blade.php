{{-- MODAL LOOKUP CARI BARANG BOM --}}
<div class="modal fade bom-modal-secondary" id="modalLookupBarangBom" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 850px;">
        <div class="modal-content bom-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header bom-modal-header-blue p-3 p-md-3.5 border-0 position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0284c7 100%);">
                <!-- Ambient Glow -->
                <div style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.15); pointer-events: none;"></div>

                <div class="d-flex align-items-center gap-2.5 position-relative" style="z-index: 2;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-search fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-extrabold text-white mb-0" id="titleLookupBom" style="font-size: 1.15rem; letter-spacing: -0.2px;">Cari & Pilih Barang</h5>
                        <small class="text-white-50 d-block mt-0.5" id="subTitleLookupBom" style="font-size: 11.5px;">Ketik kode atau nama barang garmen</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close" style="z-index: 2;"></button>
            </div>

            <div class="modal-body bom-modal-body p-3 p-md-3.5" style="background: var(--bg-main, #0f172a);">
                {{-- Search Input Bar --}}
                <div class="input-group shadow-xs rounded-pill overflow-hidden mb-3" style="border: 1px solid var(--border-color, rgba(255,255,255,0.12)); background: var(--bg-card, #1e293b);">
                    <span class="input-group-text border-0 bg-transparent ps-3.5">
                        <i class="bi bi-search fs-6 text-primary"></i>
                    </span>
                    <input type="text" id="searchBarangBomInput" class="form-control border-0 bg-transparent ps-1 fs-6"
                           placeholder="Ketik kode barang atau nama barang..." autocomplete="off" style="color: var(--text-main, #fff);">
                    <button type="button" class="btn btn-link text-muted border-0 pe-3 d-none" id="btnClearSearchLookupBom" onclick="$('#searchBarangBomInput').val('').trigger('input').focus();">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                {{-- Result Table / Cards --}}
                <div id="resultLookupBarangBom" class="rounded-4 border shadow-xs" 
                     style="max-height: 480px; overflow-y: auto !important; -webkit-overflow-scrolling: touch; background: var(--bg-card, #1e293b); border-color: rgba(255,255,255,0.08) !important;">
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 d-block mb-2 text-primary opacity-50"></i>
                        <span class="text-theme-muted">Ketik kode atau nama barang di atas untuk mencari...</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer bom-modal-footer justify-content-between p-3 border-0" 
                 style="background: var(--bg-card, #1e293b); border-top: 1px solid var(--border-color, rgba(255,255,255,0.08)) !important;">
                <small class="text-theme-muted" style="font-size: 11.5px;"><i class="bi bi-hand-index-thumb me-1 text-primary"></i>Klik atau sentuh baris barang untuk memilih.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Global safe HTML escaping for BOM modules
    window.escapeHtmlBom = function(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    let activeBomTargetRowId = null;
    let activeBomFormContext = 'input'; // 'input', 'edit', 'input_jadi', 'edit_jadi'
    let searchBomTimeout = null;

    function bukaModalLookupBarangBom(rowIdOrType, context = 'input') {
        if (context === 'input') {
            const barangJadiId = $('#inp_barang_jadi_id').val();
            const namaBom = $('#inp_nama_bom').val().trim();

            if (!barangJadiId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Barang Jadi Dahulu!',
                    text: 'Silakan pilih Produk Barang Jadi (Hasil Produksi) terlebih dahulu sebelum memilih bahan baku.',
                    confirmButtonText: '<i class="bi bi-box-seam me-1"></i> Pilih Barang Jadi'
                }).then(() => {
                    bukaModalLookupBarangBom('input_jadi', 'input_jadi');
                });
                return;
            }

            if (!namaBom) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Isi Nama Resep Dahulu!',
                    text: 'Silakan isi Nama Resep Produksi terlebih dahulu sebelum memilih bahan baku.'
                }).then(() => {
                    $('#inp_nama_bom').focus();
                });
                return;
            }
        }

        activeBomTargetRowId = rowIdOrType;
        activeBomFormContext = context;

        if (context.includes('jadi')) {
            $('#titleLookupBom').text('Cari & Pilih Barang Jadi (Hasil Produksi)');
            $('#subTitleLookupBom').text('Pilih produk kemeja, baju, celana atau barang jadi hasil jahit');
        } else {
            $('#titleLookupBom').text('Cari & Pilih Bahan Baku / Aksesoris');
            $('#subTitleLookupBom').text('Pilih kain, benang, kancing, zipper, atau aksesoris jahit');
        }

        $('#searchBarangBomInput').val('');
        $('#modalLookupBarangBom').modal('show');
    }

    $('#modalLookupBarangBom').on('show.bs.modal', function () {
        setTimeout(function() {
            const $backdrops = $('.modal-backdrop');
            if ($backdrops.length > 1) {
                $backdrops.last().addClass('backdrop-secondary');
            }
        }, 15);
    });

    $('#modalLookupBarangBom').on('shown.bs.modal', function () {
        $('#searchBarangBomInput').focus();
        loadDataLookupBarangBom('');
    });

    $('#modalLookupBarangBom').on('hidden.bs.modal', function () {
        $('.modal-backdrop').removeClass('backdrop-secondary');
        if ($('.modal.show').length > 0) {
            setTimeout(function() {
                $(document.body).addClass('modal-open').css('overflow', 'hidden');
            }, 50);
        }
    });

    $('#searchBarangBomInput').on('input', function() {
        clearTimeout(searchBomTimeout);
        const q = $(this).val().trim();

        if (q.length > 0) {
            $('#btnClearSearchLookupBom').removeClass('d-none');
        } else {
            $('#btnClearSearchLookupBom').addClass('d-none');
        }

        $('#resultLookupBarangBom').html(`
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <div class="text-theme-muted">Mencari barang...</div>
            </div>
        `);

        searchBomTimeout = setTimeout(() => {
            loadDataLookupBarangBom(q);
        }, 250);
    });

    function loadDataLookupBarangBom(q) {
        let barangType = activeBomFormContext.includes('jadi') ? 'jadi' : 'mentah';

        $.ajax({
            url: '{{ route("produksi.bom.search-barang") }}',
            method: 'GET',
            data: {
                q: q,
                type: barangType
            },
            success: function(res) {
                if (!res || res.length === 0) {
                    $('#resultLookupBarangBom').html(`
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <span class="text-theme-muted">Tidak ditemukan barang dengan kata kunci "<strong>${escapeHtmlBom(q)}</strong>".</span>
                        </div>
                    `);
                    return;
                }

                const showHppCol = !activeBomFormContext.includes('jadi');

                let tableHtml = `
                    <div class="d-md-none px-3 py-1.5 d-flex align-items-center justify-content-between border-bottom" style="background: rgba(37, 99, 235, 0.08); border-color: rgba(255,255,255,0.08) !important;">
                        <span class="text-theme-muted" style="font-size: 11px;">
                            <i class="bi bi-arrows-expand-vertical me-1 text-primary"></i> Geser tabel ke samping ➔
                        </span>
                        <span class="badge bg-primary bg-opacity-20 text-primary rounded-pill" style="font-size: 10px;">${res.length} Barang</span>
                    </div>

                    <div class="table-responsive lookup-table-responsive" style="overflow-x: auto; overflow-y: visible !important; -webkit-overflow-scrolling: touch;">
                        <table class="table table-hover align-middle mb-0 text-nowrap" style="min-width: ${showHppCol ? '720px' : '620px'}; font-size: 0.88rem;">
                            <thead class="sticky-top bg-light-theme" style="top: 0; border-bottom: 2px solid rgba(255,255,255,0.1); z-index: 10;">
                                <tr>
                                    <th class="text-center" style="width: 50px;">FOTO</th>
                                    <th style="min-width: 110px;">KODE BARANG</th>
                                    <th style="min-width: 180px;">NAMA BARANG</th>
                                    <th class="text-center" style="min-width: 120px;">STOK SAAT INI</th>
                                    <th class="text-center" style="min-width: 90px;">SATUAN</th>
                                    ${showHppCol ? '<th class="text-end" style="min-width: 120px;">ESTIMASI HPP</th>' : ''}
                                    <th class="text-center" style="width: 90px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                res.forEach(b => {
                    const thumbFotoHtml = b.foto_url
                        ? `<img src="${b.foto_url}" class="rounded border" style="width: 36px; height: 36px; object-fit: cover;">`
                        : `<div class="rounded border bg-light-theme d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-image text-muted"></i></div>`;

                    const safeKode = escapeHtmlBom(b.kode_barang);
                    const safeNama = escapeHtmlBom(b.nama_barang);
                    const safeSatuan = escapeHtmlBom(b.satuan_name);
                    const safeFoto = b.foto_url ? escapeHtmlBom(b.foto_url) : '';
                    const satuanIdVal = b.satuan_id ? b.satuan_id : 'null';
                    const hppVal = b.hpp_satuan || 0;
                    const stokVal = parseFloat(b.stok_total) || 0;
                    const fotosJson = JSON.stringify(b.fotos_url || (b.foto_url ? [b.foto_url] : []));

                    const stokBadgeHtml = stokVal > 0 
                        ? `<span class="badge bg-success text-white font-monospace px-2.5 py-1.5 shadow-xs" style="color: #ffffff !important; font-size: 11.5px;"><i class="bi bi-box-seam me-1"></i>${numberFormatID(stokVal)} ${safeSatuan}</span>`
                        : `<span class="badge bg-danger text-white font-monospace px-2.5 py-1.5 shadow-xs" style="color: #ffffff !important; font-size: 11.5px;"><i class="bi bi-exclamation-triangle me-1"></i>Habis (0)</span>`;

                    tableHtml += `
                        <tr class="cursor-pointer row-select-barang-bom" 
                            onclick='pilihBarangLookupBom(${b.id}, "${safeKode}", "${safeNama}", ${satuanIdVal}, "${safeSatuan}", ${hppVal}, "${safeFoto}", ${fotosJson})'>
                            <td class="text-center">${thumbFotoHtml}</td>
                            <td class="fw-bold text-primary font-monospace" style="font-size: 13px;">${safeKode}</td>
                            <td class="fw-semibold text-theme-main">${safeNama}</td>
                            <td class="text-center">${stokBadgeHtml}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-15 text-theme-main border-0 px-2.5 py-1.5 fw-semibold" style="font-size: 11.5px;">${safeSatuan}</span>
                            </td>
                            ${showHppCol ? `<td class="text-end font-monospace text-theme-main fw-semibold">Rp ${numberFormatID(hppVal)}</td>` : ''}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1">
                                    <span>Pilih</span> <i class="bi bi-check2"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                tableHtml += `</tbody></table></div>`;

                if (res.length >= 50) {
                    tableHtml += `
                        <div class="py-2 px-3 bg-light-theme border-top text-center text-muted small">
                            <i class="bi bi-info-circle me-1 text-info"></i> Menampilkan 50 barang teratas. Ketik kata kunci yang lebih spesifik jika barang belum muncul.
                        </div>
                    `;
                }
                $('#resultLookupBarangBom').html(tableHtml);
                if (typeof initTableDragScroll === 'function') {
                    initTableDragScroll();
                }
            },
            error: function() {
                $('#resultLookupBarangBom').html(`
                    <div class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                        <span>Gagal mengambil data barang dari server.</span>
                    </div>
                `);
            }
        });
    }

    function pilihBarangLookupBom(id, kode, nama, satuanId, satuanName, hpp, foto, fotosArr) {
        const fotos = Array.isArray(fotosArr) && fotosArr.length > 0 ? fotosArr : (foto ? [foto] : []);

        if (activeBomFormContext === 'input_jadi') {
            $('#inp_barang_jadi_id').val(id);
            $('#inp_barang_jadi_name').val(`[${kode}] ${nama}`);
            if (satuanId) {
                $('#inp_satuan_hasil_id').val(satuanId);
                $('#inp_satuan_hasil_name').val(satuanName);
            }
            if (!$('#inp_nama_bom').val().trim()) {
                $('#inp_nama_bom').val(`BOM ${nama}`);
            }
            if (typeof updatePreviewFotoInput === 'function') updatePreviewFotoInput(fotos);

        } else if (activeBomFormContext === 'edit_jadi') {
            $('#edit_barang_jadi_id').val(id);
            $('#edit_barang_jadi_name').val(`[${kode}] ${nama}`);
            if (satuanId) {
                $('#edit_satuan_hasil_id').val(satuanId);
                $('#edit_satuan_hasil_name').val(satuanName);
            }
            if (typeof updatePreviewFotoEdit === 'function') updatePreviewFotoEdit(fotos);

        } else if (activeBomFormContext === 'input') {
            const rowId = activeBomTargetRowId;
            $(`#${rowId} .inp-barang-mentah-id`).val(id);
            $(`#${rowId} .inp-barang-name`).val(`[${kode}] ${nama}`);
            if (satuanId) {
                $(`#${rowId} .inp-satuan-id`).val(satuanId);
                $(`#${rowId} .inp-satuan-name`).val(satuanName || '');
            }
            $(`#${rowId} .inp-hpp-formatted`).val(formatRibuanID(hpp));
            if (foto) {
                $(`#img_thumb_${rowId}`).attr('src', foto).removeClass('d-none');
                $(`.icon-no-photo-${rowId}`).addClass('d-none');
            }
            hitungkanSubtotalRowBomInput(rowId);

        } else if (activeBomFormContext === 'edit') {
            const rowId = activeBomTargetRowId;
            $(`#${rowId} .inp-barang-mentah-id`).val(id);
            $(`#${rowId} .inp-barang-name`).val(`[${kode}] ${nama}`);
            if (satuanId) {
                $(`#${rowId} .inp-satuan-id`).val(satuanId);
                $(`#${rowId} .inp-satuan-name`).val(satuanName || '');
            }
            $(`#${rowId} .inp-hpp-formatted`).val(formatRibuanID(hpp));
            if (foto) {
                $(`#img_thumb_${rowId}`).attr('src', foto).removeClass('d-none');
                $(`.icon-no-photo-${rowId}`).addClass('d-none');
            }
            hitungkanSubtotalRowBomEdit(rowId);
        }

        $('#modalLookupBarangBom').modal('hide');
    }

    function escapeHtmlBom(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
