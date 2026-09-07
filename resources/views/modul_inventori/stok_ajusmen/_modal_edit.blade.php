{{-- MODAL EDIT STOCK ADJUSTMENT --}}
<div class="modal fade" id="modalEditAdjustment" tabindex="-1" aria-labelledby="modalEditAdjustmentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form id="formEditAdjustment" autocomplete="off" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <input type="hidden" id="edit_adjustment_id">
            <div class="modal-header bg-warning text-dark p-3.5 rounded-top-4 border-0">
                <h5 class="modal-title fw-bold" id="modalEditAdjustmentLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Penyesuaian Stok (Draft)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-3 mb-4">
                    {{-- Gudang (Disabled on Edit) --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Gudang</label>
                        <input type="text" id="edit_gudang_name" class="form-control rounded-3 bg-light" readonly>
                    </div>

                    {{-- Tanggal Adjustment --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Tanggal Adjustment <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_adjustment" id="edit_tanggal" class="form-control rounded-3" required>
                    </div>

                    {{-- Tipe Adjustment --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Tipe Penyesuaian <span class="text-danger">*</span></label>
                        <select name="tipe_adjustment" id="edit_tipe" class="form-select rounded-3" required>
                            <option value="Pengurangan">Pengurangan Stok (Keluar)</option>
                            <option value="Penambahan">Penambahan Stok (Masuk)</option>
                        </select>
                    </div>

                    {{-- Kategori Alasan --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Kategori Alasan <span class="text-danger">*</span></label>
                        <select name="kategori_alasan" id="edit_kategori" class="form-select rounded-3" required>
                            <option value="Barang Rusak">Barang Rusak (Damaged)</option>
                            <option value="Barang Hilang">Barang Hilang (Lost/Shrinkage)</option>
                            <option value="Barang Sampel/Promosi">Barang Sampel / Promosi</option>
                            <option value="Internal Use">Penggunaan Internal</option>
                            <option value="Koreksi Input">Koreksi Input Data</option>
                        </select>
                    </div>

                    {{-- Nomor Adjustment (Readonly) --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Nomor Adjustment</label>
                        <input type="text" id="edit_nomor" class="form-control rounded-3 bg-light" readonly>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Keterangan / Catatan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control rounded-3" placeholder="Catatan tambahan...">
                    </div>
                </div>

                {{-- TABEL ITEM DRAFT EDIT --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold text-body mb-0">
                            <i class="bi bi-box-seam me-2 text-warning"></i>Daftar Barang Penyesuaian
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 rounded-pill ms-2 font-monospace" id="badgeItemCountEdit">1 Item</span>
                        </h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="tambahBarisItemEdit()">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded-3 border mb-3 shadow-xs">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tabelItemEdit" style="min-width: 820px;">
                        <thead class="small">
                            <tr class="text-uppercase text-nowrap">
                                <th style="min-width: 290px; width: 34%;">BARANG / SPESIFIKASI <span class="text-danger">*</span></th>
                                <th style="min-width: 105px; width: 12%;" class="text-center">STOK SAAT INI</th>
                                <th style="min-width: 125px; width: 14%;" class="text-center">QTY ADJUSTMENT <span class="text-danger">*</span></th>
                                <th style="min-width: 145px; width: 18%;" class="text-end">HPP SATUAN (RP)</th>
                                <th style="min-width: 135px; width: 16%;" class="text-end">NILAI TOTAL (RP)</th>
                                <th style="min-width: 45px; width: 6%;" class="text-center"><i class="bi bi-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItemEdit">
                            {{-- Rows populated dynamically by Edit JS --}}
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr class="align-middle">
                                <td colspan="2" class="text-end text-muted small text-uppercase pe-3">TOTAL:</td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 px-2.5 py-1.5 rounded-pill fw-bold font-monospace" id="lblTotalQtyEdit">0 Qty</span>
                                </td>
                                <td class="text-end text-muted small text-uppercase pe-2">TOTAL NILAI:</td>
                                <td class="text-end">
                                    <span class="fw-bold text-success font-monospace fs-6" id="lblTotalNilaiEdit">Rp 0</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light border-top p-3 p-md-3.5">
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 w-100">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 text-white order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                    <div class="d-flex flex-column flex-sm-row gap-2 order-1 order-sm-2">
                        <button type="button" id="btnDraftEditAdjustment" class="btn btn-outline-warning fw-bold rounded-pill px-4" onclick="submitFormEditAdjustment('draft')">
                            <i class="bi bi-file-earmark-text me-1"></i> Simpan Sebagai Draft
                        </button>
                        <button type="button" id="btnPosEditAdjustment" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm" onclick="submitFormEditAdjustment('pending')">
                            <i class="bi bi-send-fill me-1"></i> Pos / Ajukan (Pending)
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT JAVASCRIPT INLINE --}}
@push('scripts')
<script>
    let editRowIndex = 0;
    let editGudangId = null;

    function editAdjustment(id) {
        $('#btnDraftEditAdjustment, #btnPosEditAdjustment').prop('disabled', false);
        $.ajax({
            url: `/stock-adjustment/${id}/edit`,
            method: 'GET',
            success: function(res) {
                $('#btnDraftEditAdjustment, #btnPosEditAdjustment').prop('disabled', false);
                $('#edit_adjustment_id').val(res.id);
                $('#edit_gudang_name').val(res.gudang ? res.gudang.nama_gudang : '-');
                editGudangId = res.gudang_id;
                
                $('#edit_tanggal').val(res.tanggal_adjustment ? res.tanggal_adjustment.substring(0, 10) : '');
                $('#edit_tipe').val(res.tipe_adjustment);
                $('#edit_kategori').val(res.kategori_alasan);
                $('#edit_nomor').val(res.nomor_adjustment);
                $('#edit_keterangan').val(res.keterangan || '');

                $('#tbodyItemEdit').empty();
                editRowIndex = 0;

                if (res.details && res.details.length > 0) {
                    res.details.forEach(item => {
                        tambahBarisItemEdit(item);
                    });
                } else {
                    tambahBarisItemEdit();
                }

                kalkulasiTotalEdit();
                updateRowCounterEdit();
                $('#modalEditAdjustment').modal('show');
            },
            error: function(xhr) {
                $('#btnDraftEditAdjustment, #btnPosEditAdjustment').prop('disabled', false);
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal memuat data adjustment.', 'error');
            }
        });
    }

    function tambahBarisItemEdit(itemData = null) {
        if ($('#tbodyItemEdit tr').length >= 25) {
            Swal.fire({
                icon: 'warning',
                title: 'Mencapai Batas Maksimal!',
                text: 'Maksimal 25 baris barang per dokumen adjustment demi kestabilan & keamanan transaksi.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        editRowIndex++;
        const rowId = `row_edit_${editRowIndex}`;
        const dispName = itemData && itemData.barang ? `[${itemData.barang.kode_barang}] ${itemData.barang.nama_barang}` : '';
        const fotoUrl = itemData && itemData.barang && itemData.barang.primary_foto ? `/storage/${itemData.barang.primary_foto.foto_path}` : '';
        const stokAktual = (itemData && itemData.stok_aktual !== undefined) ? itemData.stok_aktual : 0;

        // Parse HPP & nilai_total safely — API returns standard format (dot=decimal)
        const hppNum       = parseFloat(itemData ? itemData.harga_satuan : 0) || 0;
        const nilaiNum     = parseFloat(itemData ? itemData.nilai_total   : 0) || 0;
        const qtyNum       = parseFloat(itemData ? itemData.jumlah        : 0) || 0;
        const hppFormatted = formatRibuanID(hppNum);

        // LOGIKA HPP: Jika HPP > 0 kunci (readonly), jika HPP == 0 buka (bisa diisi manual)
        const isHppLocked  = (hppNum > 0);
        const readonlyAttr = isHppLocked ? 'readonly' : '';
        const bgClass      = isHppLocked ? 'bg-light' : '';
        const titleAttr    = isHppLocked ? 'HPP terkunci otomatis dari Master/Stok Gudang' : '';

        const trHtml = `
            <tr id="${rowId}" data-stok="${stokAktual}">
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="product-thumb-box border rounded-3 d-flex align-items-center justify-content-center bg-light cursor-pointer shadow-xs position-relative flex-shrink-0"
                             style="width: 38px; height: 38px; overflow: hidden;"
                             onclick="triggerPreviewFotoRow('${rowId}')"
                             title="Klik untuk lihat foto barang">
                            <img src="${fotoUrl}" id="img_thumb_${rowId}" class="product-img ${fotoUrl ? '' : 'd-none'}" style="width: 100%; height: 100%; object-fit: cover;">
                            <i class="bi bi-image text-muted fs-6 icon-no-photo-${rowId} ${fotoUrl ? 'd-none' : ''}"></i>
                        </div>

                        <div class="input-group input-group-sm flex-grow-1 shadow-xs">
                            <input type="hidden" name="items[${editRowIndex}][barang_id]" class="inp-barang-id" value="${itemData ? itemData.barang_id : ''}" required data-row="${rowId}">
                            <input type="hidden" id="foto_url_${rowId}" class="inp-foto-url" value="${fotoUrl}">
                            <input type="text" class="form-control form-control-sm bg-light inp-barang-name cursor-pointer" 
                                   value="${dispName}" placeholder="-- Klik 🔍 untuk cari barang --" readonly required data-row="${rowId}"
                                   onclick="bukaModalLookupBarangAdj('${rowId}', 'edit')">
                            <button type="button" class="btn btn-warning text-dark btn-search-barang-adj px-2.5" onclick="bukaModalLookupBarangAdj('${rowId}', 'edit')" title="Cari Barang">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-10 px-2 py-1.5 rounded-2 fw-semibold font-monospace" id="stok_aktual_${rowId}" data-stok="${stokAktual}">${itemData ? numberFormatID(stokAktual) : '-'}</span>
                </td>
                <td>
                    <input type="number" step="any" min="0.0001" name="items[${editRowIndex}][jumlah]" class="form-control form-control-sm text-center fw-semibold inp-qty-edit rounded-2" value="${qtyNum || ''}" placeholder="0" required data-row="${rowId}">
                </td>
                <td class="text-end">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted px-2 small">Rp</span>
                        <input type="text" name="items[${editRowIndex}][harga_satuan]" class="form-control form-control-sm text-end font-monospace fw-semibold inp-hpp-edit inp-hpp-formatted rounded-end-2 ${bgClass}" value="${hppFormatted}" placeholder="0" required data-row="${rowId}" ${readonlyAttr} title="${titleAttr}">
                    </div>
                </td>
                <td class="text-end fw-bold text-success font-monospace inp-total-${rowId}">Rp ${numberFormatID(nilaiNum)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1.5" onclick="hapusBarisItemEdit('${rowId}')" title="Hapus Baris">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyItemEdit').append(trHtml);
        // Recalculate to ensure consistency with loaded data
        hitungkanSubtotalRowEdit(rowId);
        validateRowStokEdit(rowId);
        updateRowCounterEdit();
    }

    function hapusBarisItemEdit(rowId) {
        if ($('#tbodyItemEdit tr').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 item barang!', 'info');
            return;
        }
        $(`#${rowId}`).remove();
        kalkulasiTotalEdit();
        updateRowCounterEdit();
    }

    function updateRowCounterEdit() {
        const count = $('#tbodyItemEdit tr').length;
        $('#badgeItemCountEdit').text(count + ' Item');
    }

    function validateRowStokEdit(rowId) {
        const tipe = $('#edit_tipe').val();
        const stok = parseFloat($(`#${rowId}`).attr('data-stok')) || 0;
        const qty = parseFloat($(`#${rowId} .inp-qty-edit`).val()) || 0;
        const $inputQty = $(`#${rowId} .inp-qty-edit`);
        const $stokBadge = $(`#stok_aktual_${rowId}`);
        const barangId = $(`#${rowId} .inp-barang-id`).val();

        if (tipe === 'Pengurangan' && barangId && qty > stok) {
            $inputQty.addClass('is-invalid border-danger text-danger bg-danger bg-opacity-10');
            $stokBadge.addClass('bg-danger text-white border-danger').removeClass('bg-secondary-subtle text-secondary');
            return false;
        } else {
            $inputQty.removeClass('is-invalid border-danger text-danger bg-danger bg-opacity-10');
            $stokBadge.removeClass('bg-danger text-white border-danger').addClass('bg-secondary-subtle text-secondary');
            return true;
        }
    }

    $(document).on('input', '.inp-qty-edit', function() {
        const rowId = $(this).data('row');
        validateRowStokEdit(rowId);
        hitungkanSubtotalRowEdit(rowId);
    });

    $('#edit_tipe').on('change', function() {
        $('#tbodyItemEdit tr').each(function() {
            validateRowStokEdit($(this).attr('id'));
        });
    });

    function hitungkanSubtotalRowEdit(rowId) {
        const qty = parseFloat($(`#${rowId} .inp-qty-edit`).val()) || 0;
        const hpp = unformatNumberID($(`#${rowId} .inp-hpp-edit`).val());
        const total = qty * hpp;

        $(`.inp-total-${rowId}`).text('Rp ' + numberFormatID(total));
        kalkulasiTotalEdit();
    }

    let isCalcEditPending = false;
    function kalkulasiTotalEdit() {
        if (isCalcEditPending) return;
        isCalcEditPending = true;

        requestAnimationFrame(() => {
            let totalQty = 0;
            let totalNilai = 0;

            $('#tbodyItemEdit tr').each(function() {
                const qty = parseFloat($(this).find('.inp-qty-edit').val()) || 0;
                const hpp = unformatNumberID($(this).find('.inp-hpp-edit').val());
                totalQty += qty;
                totalNilai += (qty * hpp);
            });

            $('#lblTotalQtyEdit').text(numberFormatID(totalQty) + ' Qty');
            $('#lblTotalNilaiEdit').text('Rp ' + numberFormatID(totalNilai));
            isCalcEditPending = false;
        });
    }

    // Submit Form Edit AJAX
    function submitFormEditAdjustment(submitAction = 'draft') {
        const $form = $('#formEditAdjustment');
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        const id = $('#edit_adjustment_id').val();
        const $btnDraft = $('#btnDraftEditAdjustment');
        const $btnPos = $('#btnPosEditAdjustment');
        
        $btnDraft.prop('disabled', true);
        $btnPos.prop('disabled', true);

        const formData = {
            _token: '{{ csrf_token() }}',
            submit_action: submitAction,
            tanggal_adjustment: $('#edit_tanggal').val(),
            tipe_adjustment: $('#edit_tipe').val(),
            kategori_alasan: $('#edit_kategori').val(),
            keterangan: $('#edit_keterangan').val(),
            items: []
        };

        // Validasi Keamanan: Pengurangan Stok Tidak Boleh Melebihi Stok Tersedia
        if (formData.tipe_adjustment === 'Pengurangan') {
            let adaMinus = false;
            let barangMinus = '';
            let stokTersedia = 0;
            let qtyDiminta = 0;

            $('#tbodyItemEdit tr').each(function() {
                const barangId = $(this).find('.inp-barang-id').val();
                const barangName = $(this).find('.inp-barang-name').val() || 'Barang';
                const qty = parseFloat($(this).find('.inp-qty-edit').val()) || 0;
                const stok = parseFloat($(this).attr('data-stok')) || 0;

                if (barangId && qty > stok) {
                    adaMinus = true;
                    barangMinus = barangName;
                    stokTersedia = stok;
                    qtyDiminta = qty;
                    return false;
                }
            });

            if (adaMinus) {
                $btnDraft.prop('disabled', false);
                $btnPos.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Tidak Mencukupi!',
                    html: `Jumlah pengurangan untuk <b>${barangMinus}</b> melebihi stok yang ada di gudang.<br><br><span class="text-danger fw-bold">Stok Tersedia: ${numberFormatID(stokTersedia)} | Diminta: ${numberFormatID(qtyDiminta)}</span><br><br><small class="text-muted">Sistem tidak mengizinkan stok bernilai minus/negatif.</small>`,
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
        }

        $('#tbodyItemEdit tr').each(function() {
            const barangId = $(this).find('.inp-barang-id').val();
            const qty = parseFloat($(this).find('.inp-qty-edit').val()) || 0;
            const hpp = unformatNumberID($(this).find('.inp-hpp-edit').val());

            if (barangId && qty > 0) {
                formData.items.push({
                    barang_id: barangId,
                    jumlah: qty,
                    harga_satuan: hpp
                });
            }
        });

        if (formData.items.length === 0) {
            $btnDraft.prop('disabled', false);
            $btnPos.prop('disabled', false);
            Swal.fire('Validasi Gagal', 'Silakan pilih minimal 1 item barang yang valid!', 'warning');
            return;
        }

        $.ajax({
            url: `/stock-adjustment/${id}`,
            method: 'PUT',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(res) {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                $('#modalEditAdjustment').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2200,
                    showConfirmButton: false,
                    position: 'center'
                }).then(() => {
                    reloadTableAdjustment();
                });
            },
            error: function(xhr) {
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan server.', 'error');
            },
            complete: function() {
                $btnDraft.prop('disabled', false);
                $btnPos.prop('disabled', false);
            }
        });
    }
</script>
@endpush
