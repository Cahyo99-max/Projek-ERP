{{-- MODAL LOOKUP CARI BARANG ADJUSTMENT --}}
<div class="modal fade" id="modalLookupBarangAdj" tabindex="-1" aria-hidden="true" style="z-index: 1060; background: rgba(15, 23, 42, 0.65) !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white p-3.5 rounded-top-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-25 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-search fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white">Cari & Pilih Barang Persediaan</h5>
                        <small class="text-white-50">Ketik nama atau kode barang untuk mencari barang gudang</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Search Input Bar --}}
                <div class="input-group input-group-lg shadow-sm rounded-3 mb-3">
                    <span class="input-group-text border-end-0 text-muted ps-3">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <input type="text" id="searchBarangAdjInput" class="form-control border-start-0 ps-0 fs-6"
                           placeholder="Ketik kode barang atau nama barang..." autocomplete="off">
                </div>

                {{-- Result Table / Cards --}}
                <div id="resultLookupBarangAdj" class="rounded-3 border" style="max-height: 420px; overflow-y: auto; overflow-x: auto;">
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 d-block mb-2 text-primary opacity-50"></i>
                        <span class="text-muted">Ketik kode atau nama barang di atas untuk mencari...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top rounded-bottom-4 p-3 justify-content-between">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Klik pada baris barang untuk memilih.</small>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@php
    $canSeeHpp = auth()->check() && (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-hpp-stok-adjustment'));
@endphp

@push('scripts')
<script>
    let activeTargetRowId = null;
    let activeFormContext = 'input'; // 'input' or 'edit'
    let searchAdjTimeout = null;
    const canSeeHppLookup = @json($canSeeHpp);

    function bukaModalLookupBarangAdj(rowId, context = 'input') {
        activeTargetRowId = rowId;
        activeFormContext = context;

        // Verify Gudang ID chosen first
        let gudangId = (context === 'edit') ? editGudangId : $('#inp_gudang_id').val();
        if (!gudangId && context === 'input') {
            Swal.fire('Perhatian', 'Silakan pilih Gudang terlebih dahulu!', 'warning');
            $('#inp_gudang_id').focus();
            return;
        }

        $('#searchBarangAdjInput').val('');
        $('#modalLookupBarangAdj').modal('show');
    }

    $('#modalLookupBarangAdj').on('shown.bs.modal', function () {
        $('#searchBarangAdjInput').focus();
        loadDataLookupBarangAdj('');
    });

    $('#searchBarangAdjInput').on('input', function() {
        clearTimeout(searchAdjTimeout);
        const q = $(this).val().trim();

        $('#resultLookupBarangAdj').html(`
            <div class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <div class="text-muted">Mencari barang...</div>
            </div>
        `);

        searchAdjTimeout = setTimeout(() => {
            loadDataLookupBarangAdj(q);
        }, 250);
    });

    function loadDataLookupBarangAdj(q) {
        let gudangId = (activeFormContext === 'edit') ? editGudangId : $('#inp_gudang_id').val();

        $.ajax({
            url: '{{ route("stock-adjustment.search-barang") }}',
            method: 'GET',
            data: {
                q: q,
                gudang_id: gudangId
            },
            success: function(res) {
                if (!res || res.length === 0) {
                    $('#resultLookupBarangAdj').html(`
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <span class="text-muted">Tidak ditemukan barang dengan kata kunci "<strong>${escapeHtmlAdj(q)}</strong>".</span>
                        </div>
                    `);
                    return;
                }

                let rowsHtml = `
                    <div class="table-responsive mb-0" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem; min-width: 620px;">
                            <thead class="sticky-top bg-body shadow-xs" style="z-index: 5;">
                                <tr class="text-uppercase text-nowrap">
                                    <th style="width: 40px;" class="text-center">FOTO</th>
                                    <th>KODE</th>
                                    <th>NAMA BARANG</th>
                                    <th class="text-center">SATUAN</th>
                                    <th class="text-center">STOK GUDANG</th>
                                    ${canSeeHppLookup ? '<th class="text-end">HPP SATUAN</th>' : ''}
                                    <th class="text-center" style="width: 80px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                res.forEach(b => {
                    const thumbFotoHtml = b.foto_url
                        ? `<img src="${b.foto_url}" class="rounded border" style="width: 32px; height: 32px; object-fit: cover;">`
                        : `<div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="bi bi-image text-muted"></i></div>`;

                    const hppCellHtml = canSeeHppLookup 
                        ? `<td class="text-end font-monospace text-body">Rp ${numberFormatCurrencyID(b.hpp_satuan)}</td>`
                        : ``;

                    const snBadgeHtml = b.has_serials
                        ? `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 ms-1.5 small"><i class="bi bi-upc-scan me-1"></i>Serial</span>`
                        : ``;

                    rowsHtml += `
                        <tr class="cursor-pointer row-select-barang-adj" 
                            data-id="${b.id}"
                            data-kode="${escapeHtmlAdj(b.kode_barang)}"
                            data-nama="${escapeHtmlAdj(b.nama_barang)}"
                            data-satuan="${escapeHtmlAdj(b.satuan_name)}"
                            data-stok="${b.stok_aktual}"
                            data-hpp="${b.hpp_satuan}"
                            data-foto="${b.foto_url || ''}"
                            data-has-serials="${b.has_serials ? '1' : '0'}"
                            data-tipe-pelacakan="${escapeHtmlAdj(b.tipe_pelacakan || 'None')}">
                            <td class="text-center">${thumbFotoHtml}</td>
                            <td class="fw-bold text-primary font-monospace">${escapeHtmlAdj(b.kode_barang)}</td>
                            <td class="fw-semibold text-body">
                                ${escapeHtmlAdj(b.nama_barang)}
                                ${snBadgeHtml}
                            </td>
                            <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-body border-0 px-2.5 py-1">${escapeHtmlAdj(b.satuan_name)}</span></td>
                            <td class="text-center fw-bold font-monospace ${b.stok_aktual > 0 ? 'text-success' : 'text-danger'}">${numberFormatID(b.stok_aktual)}</td>
                            ${hppCellHtml}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs">
                                    <i class="bi bi-check-lg"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });

                rowsHtml += `</tbody></table></div>`;
                $('#resultLookupBarangAdj').html(rowsHtml);
            },
            error: function() {
                $('#resultLookupBarangAdj').html(`
                    <div class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-1"></i>
                        <span>Gagal memuat data barang. Terjadi kesalahan server.</span>
                    </div>
                `);
            }
        });
    }

    // Event Handler Click Pilih Barang
    $(document).on('click', '.row-select-barang-adj', function() {
        if (!activeTargetRowId) return;

        const id      = $(this).data('id');
        const kode    = $(this).data('kode');
        const nama    = $(this).data('nama');
        const satuan  = $(this).data('satuan');
        const stok    = parseFloat($(this).data('stok')) || 0;
        const hpp     = parseFloat($(this).data('hpp')) || 0;
        const fotoUrl = $(this).data('foto') || '';

        const displayName = `[${kode}] ${nama} (${satuan})`;

        const hppFmt = formatRibuanID(hpp);

        const hasSerials = ($(this).data('has-serials') == '1');
        const tipePelacakan = $(this).data('tipe-pelacakan') || 'None';

        if (activeFormContext === 'input') {
            $(`#${activeTargetRowId} .inp-barang-id`).val(id);
            $(`#${activeTargetRowId} .inp-barang-name`).val(displayName);
            $(`#${activeTargetRowId} .inp-foto-url`).val(fotoUrl);
            $(`#${activeTargetRowId}`).attr('data-stok', stok);
            $(`#stok_aktual_${activeTargetRowId}`).text(numberFormatID(stok)).attr('data-stok', stok);
            $(`#${activeTargetRowId} .inp-hpp-input`).val(hppFmt);

            // Handle Serial Numbers button & flag
            $(`#${activeTargetRowId} .inp-has-serials`).val(hasSerials ? '1' : '0');
            $(`#${activeTargetRowId} .inp-serial-numbers`).val('[]');
            if (hasSerials) {
                const targetQty = parseInt($(`#${activeTargetRowId} .inp-qty-input`).val() || 0, 10);
                $(`#${activeTargetRowId} .btn-manage-sn`).removeClass('d-none btn-outline-success text-success')
                    .addClass('btn-outline-danger text-danger')
                    .find('.sn-btn-text').text(`Serial (0/${targetQty})`);
            } else {
                $(`#${activeTargetRowId} .btn-manage-sn`).addClass('d-none');
            }

            // LOGIKA HPP: Jika HPP > 0 kunci (readonly), jika HPP == 0 buka (bisa diisi manual)
            if (hpp > 0) {
                $(`#${activeTargetRowId} .inp-hpp-input`)
                    .prop('readonly', true)
                    .addClass('bg-light')
                    .attr('title', 'HPP terkunci otomatis dari Master/Stok Gudang');
            } else {
                $(`#${activeTargetRowId} .inp-hpp-input`)
                    .prop('readonly', false)
                    .removeClass('bg-light')
                    .attr('placeholder', 'Masukkan HPP')
                    .removeAttr('title');
            }

            updateRowFotoThumbnail(activeTargetRowId, fotoUrl);
            hitungkanSubtotalRowInput(activeTargetRowId);
            if (typeof validateRowStokInput === 'function') {
                validateRowStokInput(activeTargetRowId);
            }
        } else if (activeFormContext === 'edit') {
            $(`#${activeTargetRowId} .inp-barang-id`).val(id);
            $(`#${activeTargetRowId} .inp-barang-name`).val(displayName);
            $(`#${activeTargetRowId} .inp-foto-url`).val(fotoUrl);
            $(`#${activeTargetRowId}`).attr('data-stok', stok);
            $(`#stok_aktual_${activeTargetRowId}`).text(numberFormatID(stok)).attr('data-stok', stok);
            $(`#${activeTargetRowId} .inp-hpp-edit`).val(hppFmt);

            // Handle Serial Numbers button & flag
            $(`#${activeTargetRowId} .inp-has-serials`).val(hasSerials ? '1' : '0');
            $(`#${activeTargetRowId} .inp-serial-numbers`).val('[]');
            if (hasSerials) {
                const targetQty = parseInt($(`#${activeTargetRowId} .inp-qty-edit`).val() || 0, 10);
                $(`#${activeTargetRowId} .btn-manage-sn`).removeClass('d-none btn-outline-success text-success')
                    .addClass('btn-outline-danger text-danger')
                    .find('.sn-btn-text').text(`Serial (0/${targetQty})`);
            } else {
                $(`#${activeTargetRowId} .btn-manage-sn`).addClass('d-none');
            }

            // LOGIKA HPP: Jika HPP > 0 kunci (readonly), jika HPP == 0 buka (bisa diisi manual)
            if (hpp > 0) {
                $(`#${activeTargetRowId} .inp-hpp-edit`)
                    .prop('readonly', true)
                    .addClass('bg-light')
                    .attr('title', 'HPP terkunci otomatis dari Master/Stok Gudang');
            } else {
                $(`#${activeTargetRowId} .inp-hpp-edit`)
                    .prop('readonly', false)
                    .removeClass('bg-light')
                    .attr('placeholder', 'Masukkan HPP')
                    .removeAttr('title');
            }

            updateRowFotoThumbnail(activeTargetRowId, fotoUrl);
            hitungkanSubtotalRowEdit(activeTargetRowId);
            if (typeof validateRowStokEdit === 'function') {
                validateRowStokEdit(activeTargetRowId);
            }
        }

        $('#modalLookupBarangAdj').modal('hide');
        activeTargetRowId = null;
    });

    function updateRowFotoThumbnail(rowId, fotoUrl) {
        if (fotoUrl && fotoUrl !== '') {
            $(`#img_thumb_${rowId}`).attr('src', fotoUrl).removeClass('d-none');
            $(`.icon-no-photo-${rowId}`).addClass('d-none');
        } else {
            $(`#img_thumb_${rowId}`).attr('src', '').addClass('d-none');
            $(`.icon-no-photo-${rowId}`).removeClass('d-none');
        }
    }

    function triggerPreviewFotoRow(rowId) {
        const fotoUrl  = $(`#foto_url_${rowId}`).val() || $(`#${rowId} .product-thumb-box img`).attr('src');
        const dispName = $(`#${rowId} .inp-barang-name`).val();
        bukaModalPreviewFoto(fotoUrl, dispName);
    }

    function escapeHtmlAdj(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    if (typeof window.numberFormatCurrencyID !== 'function') {
        window.numberFormatCurrencyID = function(val) {
            if (val === null || val === undefined || isNaN(val)) return '0,00';
            let num = parseFloat(val);
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };
    }
</script>
@endpush
