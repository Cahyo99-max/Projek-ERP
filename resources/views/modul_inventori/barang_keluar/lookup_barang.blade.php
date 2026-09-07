{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL LOOKUP BARANG (Cari Barang via Modal & Search Table) --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLookupBarang" tabindex="-1" aria-labelledby="modalLookupBarangLabel" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="z-index: 1061 !important;">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-danger bg-gradient text-white rounded-top-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalLookupBarangLabel">Cari & Pilih Barang</h5>
                        <small class="text-white-50" id="lookupGudangSubtitle">Menampilkan stok barang tersedia</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closeLookupBarangModal()"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Input Pencarian --}}
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="lookupBarangSearchInput" class="form-control border-start-0 ps-0" placeholder="Ketik kode, nama barang, atau kategori untuk menyaring...">
                </div>

                {{-- Tabel Daftar Barang --}}
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover table-modern align-middle mb-0" id="tableLookupBarang">
                        <thead class="sticky-top">
                            <tr>
                                <th width="5%">No</th>
                                <th width="22%">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="20%">Kategori</th>
                                <th class="text-center" width="18%">Stok Tersedia</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lookupBarangTbody">
                            {{-- Content loaded dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" onclick="closeLookupBarangModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentLookupRowIdx = null;
let currentLookupIsEdit = false;

// Open Lookup Barang Modal for specific row (supports both Input & Edit modal)
function openLookupBarangModal(rowIdx, isEdit = false) {
    currentLookupRowIdx = rowIdx;
    currentLookupIsEdit = isEdit;

    const gudangId = isEdit ? $('#edit_gi_gudang_id').val() : $('#gi_gudang_id').val();
    if (!gudangId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Gudang Terlebih Dahulu',
            text: 'Silakan pilih gudang sebelum mencari barang.',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const gudangName = isEdit 
        ? ($('#edit_gi_gudang_display').val() || 'Gudang')
        : ($('#gi_gudang_id option:selected').text() || 'Gudang');

    $('#lookupGudangSubtitle').text(`Stok barang tersedia di: ${gudangName}`);
    $('#lookupBarangSearchInput').val('');

    $('#lookupBarangTbody').html(`
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-danger me-2" role="status"></div>
                Memuat stok barang tersedia…
            </td>
        </tr>
    `);

    const modalEl = document.getElementById('modalLookupBarang');
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    $.get(`/goods-issue/barangs-by-gudang/${gudangId}`, function (res) {
        if (isEdit) {
            window.availableBarangsForSelectedGudangEdit = res.barangs || [];
            renderLookupBarangTable(window.availableBarangsForSelectedGudangEdit);
        } else {
            window.availableBarangsForSelectedGudang = res.barangs || [];
            renderLookupBarangTable(window.availableBarangsForSelectedGudang);
        }
    }).fail(function (xhr) {
        const msg = xhr.responseJSON?.error || 'Gagal memuat barang gudang.';
        $('#lookupBarangTbody').html(`<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${msg}</td></tr>`);
    });
}

function closeLookupBarangModal() {
    const modalEl = document.getElementById('modalLookupBarang');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }
}

function formatGIQty(val) {
    if (val === null || val === undefined || isNaN(val)) return '0';
    const num = parseFloat(val);
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 });
}

// Render dynamic table inside lookup modal
function renderLookupBarangTable(items) {
    const tbody = $('#lookupBarangTbody');
    tbody.empty();

    if (!items || items.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    Tidak ada barang dengan stok tersedia di gudang ini.
                </td>
            </tr>
        `);
        return;
    }

    items.forEach((b, idx) => {
        const stokFormatted = formatGIQty(b.stok_aktual);
        const tr = `
            <tr class="lookup-item-row" data-kode="${(b.kode || b.kode_barang || '').toLowerCase()}" data-nama="${(b.nama || b.nama_barang || '').toLowerCase()}" data-kategori="${(b.kategori || '').toLowerCase()}">
                <td class="text-muted small">${idx + 1}</td>
                <td><span class="fw-bold text-body-emphasis">${b.kode || b.kode_barang}</span></td>
                <td><span class="fw-semibold text-body">${b.nama || b.nama_barang}</span></td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal px-2.5 py-1 rounded-pill">${b.kategori || '-'}</span></td>
                <td class="text-center">
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1.5 rounded-pill" style="font-size: 0.82rem;">
                        ${stokFormatted} ${b.satuan || b.nama_satuan || ''}
                    </span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold shadow-sm"
                        onclick="selectLookupBarang(${b.id}, '${escapeJsStr(b.kode || b.kode_barang)}', '${escapeJsStr(b.nama || b.nama_barang)}', '${escapeJsStr(b.satuan || b.nama_satuan)}', ${b.stok_aktual}, '${escapeJsStr(b.foto)}')">
                        <i class="bi bi-check-lg me-1"></i>Pilih
                    </button>
                </td>
            </tr>
        `;
        tbody.append(tr);
    });
}

// Escape helper for string parameters
function escapeJsStr(str) {
    if (!str) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// Select item from modal table
function selectLookupBarang(id, kode, nama, satuan, stok, foto) {
    if (currentLookupRowIdx === null) return;
    const idx = currentLookupRowIdx;
    const isEdit = currentLookupIsEdit;

    // Check duplicate selection
    let isDuplicate = false;

    if (!isEdit) {
        $('#gi_items_container .item-row').each(function () {
            const rowId = $(this).attr('id'); // item-row-0
            const rowIdxStr = rowId ? rowId.replace('item-row-', '') : null;
            if (rowIdxStr !== null && parseInt(rowIdxStr) !== parseInt(idx)) {
                const chosenId = $(this).find('input[type=hidden]').val();
                if (chosenId && parseInt(chosenId) === parseInt(id)) {
                    isDuplicate = true;
                }
            }
        });
    } else {
        $('#edit_gi_items_container .item-row').each(function () {
            const rowId = $(this).attr('id'); // edit_item_row_1
            const rowIdxStr = rowId ? rowId.replace('edit_item_row_', '') : null;
            if (rowIdxStr !== null && parseInt(rowIdxStr) !== parseInt(idx)) {
                const chosenId = $(this).find('.edit-gi-barang-id').val();
                if (chosenId && parseInt(chosenId) === parseInt(id)) {
                    isDuplicate = true;
                }
            }
        });
    }

    if (isDuplicate) {
        Swal.fire({
            icon: 'warning',
            title: 'Barang Sudah Dipilih!',
            html: `Barang <b>${kode} - ${nama}</b> sudah ada dalam daftar rincian barang keluar.<br><br><small class="text-muted">Setiap barang hanya boleh diinput 1 kali per transaksi. Silakan sesuaikan jumlah pada baris yang sudah ada.</small>`,
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    if (!isEdit) {
        $(`#barang_id_${idx}`).val(id);
        $(`#barang_display_${idx}`).val(`${kode} - ${nama}`);
        $(`#satuan_label_${idx}`).text(satuan || '-');

        if (foto) {
            $(`#foto_preview_img_${idx}`).attr('src', foto);
            $(`#foto_preview_wrap_${idx}`).off('click').on('click', function (e) {
                e.stopPropagation();
                openPreviewFoto(foto, `${kode} - ${nama}`);
            }).show();
        } else {
            $(`#foto_preview_wrap_${idx}`).hide();
        }

        closeLookupBarangModal();
        onBarangChange(idx);
    } else {
        $(`#edit_barang_id_${idx}`).val(id);
        $(`#edit_barang_display_${idx}`).val(`${kode} - ${nama}`);
        $(`#edit_gi_satuan_${idx}`).text(satuan || '-');

        if (foto) {
            $(`#edit_foto_preview_img_${idx}`).attr('src', foto);
            $(`#edit_foto_preview_wrap_${idx}`).off('click').on('click', function (e) {
                e.stopPropagation();
                openPreviewFoto(foto, `${kode} - ${nama}`);
            }).show();
        } else {
            $(`#edit_foto_preview_wrap_${idx}`).hide();
        }

        closeLookupBarangModal();
        onEditGIBarangChange(idx);
    }
}

// Live search filter inside modal table
$(document).ready(function () {
    $('#lookupBarangSearchInput').on('keyup', function () {
        const term = $(this).val().toLowerCase().trim();
        $('#lookupBarangTbody tr.lookup-item-row').each(function () {
            const kode = $(this).data('kode') || '';
            const nama = $(this).data('nama') || '';
            const kat  = $(this).data('kategori') || '';
            if (kode.includes(term) || nama.includes(term) || kat.includes(term)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush
