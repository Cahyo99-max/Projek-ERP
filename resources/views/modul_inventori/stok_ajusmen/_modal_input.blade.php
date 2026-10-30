{{-- MODAL TAMBAH STOCK ADJUSTMENT --}}
<div class="modal fade" id="modalTambahAdjustment" tabindex="-1" aria-labelledby="modalTambahAdjustmentLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 96vw; width: 96vw;">
        <form id="formTambahAdjustment" autocomplete="off" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <div class="modal-header bg-primary text-white p-3.5 rounded-top-4 border-0">
                <h5 class="modal-title fw-bold" id="modalTambahAdjustmentLabel">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Penyesuaian Stok (Stock Adjustment)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-3 mb-4">
                    {{-- Gudang --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Gudang <span class="text-danger">*</span></label>
                        <select name="gudang_id" id="inp_gudang_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($gudangs as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_gudang }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Adjustment --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Tanggal Adjustment <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_adjustment" id="inp_tanggal" class="form-control rounded-3"
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- Tipe Adjustment --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Tipe Penyesuaian <span class="text-danger">*</span></label>
                        <select name="tipe_adjustment" id="inp_tipe" class="form-select rounded-3" required>
                            <option value="Pengurangan">Pengurangan Stok (Keluar)</option>
                            <option value="Penambahan">Penambahan Stok (Masuk)</option>
                        </select>
                    </div>

                    {{-- Kategori Alasan --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Kategori Alasan <span class="text-danger">*</span></label>
                        <select name="kategori_alasan" id="inp_kategori" class="form-select rounded-3" required>
                            <option value="Barang Rusak">Barang Rusak (Damaged)</option>
                            <option value="Barang Hilang">Barang Hilang (Lost/Shrinkage)</option>
                            <option value="Barang Sampel/Promosi">Barang Sampel / Promosi</option>
                            <option value="Internal Use">Penggunaan Internal</option>
                            <option value="Koreksi Input">Koreksi Input Data</option>
                        </select>
                    </div>

                    {{-- Nomor Adjustment (Auto vs Manual) --}}
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-secondary small mb-0">Nomor Adjustment <span class="text-danger">*</span></label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="inp_auto_number" checked>
                                <label class="form-check-label small text-muted" for="inp_auto_number">Otomatis</label>
                            </div>
                        </div>
                        <input type="text" name="nomor_adjustment" id="inp_nomor" class="form-control rounded-3 bg-light"
                               placeholder="Otomatis (Sistem)" readonly required>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold text-secondary small">Keterangan / Catatan</label>
                        <input type="text" name="keterangan" id="inp_keterangan" class="form-control rounded-3"
                               placeholder="Misal: Pecah saat penataan rak A2">
                    </div>
                </div>

                {{-- TABEL ITEM DRAFT --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                    <div>
                        <h6 class="fw-bold text-body mb-0">
                            <i class="bi bi-box-seam me-2 text-primary"></i>Daftar Barang Penyesuaian
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-2 font-monospace" id="badgeItemCountInput">1 Item</span>
                        </h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="tambahBarisItemInput()">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>
                </div>

@php
    $canSeeHpp = auth()->check() && (auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-hpp-stok-adjustment'));
@endphp

                <div class="table-responsive rounded-3 border mb-3 shadow-xs">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tabelItemInput" style="min-width: 820px;">
                        <thead class="small">
                            <tr class="text-uppercase text-nowrap">
                                <th style="min-width: 290px; width: {{ $canSeeHpp ? '34%' : '60%' }};">BARANG / SPESIFIKASI <span class="text-danger">*</span></th>
                                <th style="min-width: 105px; width: {{ $canSeeHpp ? '12%' : '18%' }};" class="text-center">STOK SAAT INI</th>
                                <th style="min-width: 125px; width: {{ $canSeeHpp ? '14%' : '16%' }};" class="text-center">QTY ADJUSTMENT <span class="text-danger">*</span></th>
                                @if($canSeeHpp)
                                    <th style="min-width: 145px; width: 18%;" class="text-end">HPP SATUAN (RP)</th>
                                    <th style="min-width: 135px; width: 16%;" class="text-end">NILAI TOTAL (RP)</th>
                                @endif
                                <th style="min-width: 45px; width: 6%;" class="text-center"><i class="bi bi-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItemInput">
                            {{-- Rows populated dynamically by JS --}}
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr class="align-middle">
                                <td colspan="2" class="text-end text-muted small text-uppercase pe-3">TOTAL:</td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 rounded-pill fw-bold font-monospace" id="lblTotalQtyInput">0 Qty</span>
                                </td>
                                @if($canSeeHpp)
                                    <td class="text-end text-muted small text-uppercase pe-2">TOTAL NILAI:</td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success font-monospace fs-6" id="lblTotalNilaiInput">Rp 0</span>
                                    </td>
                                @endif
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
                        <button type="button" id="btnDraftInputAdjustment" class="btn btn-outline-primary rounded-pill px-4 fw-bold" onclick="submitFormInputAdjustment('draft')">
                            <i class="bi bi-file-earmark-text me-1"></i> Simpan Draft
                        </button>
                        <button type="button" id="btnPosInputAdjustment" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="submitFormInputAdjustment('pending')">
                            <i class="bi bi-send-fill me-1"></i> Pos / Ajukan (Pending)
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- INPUT JAVASCRIPT INLINE --}}
@push('scripts')
<script>
    let inputRowIndex = 0;
    const canSeeHppInput = @json($canSeeHpp);

    function bukaModalTambahAdjustment() {
        $('#btnDraftInputAdjustment').prop('disabled', false).html('<i class="bi bi-file-earmark-text me-1"></i> Simpan Draft');
        $('#btnPosInputAdjustment').prop('disabled', false).html('<i class="bi bi-send-fill me-1"></i> Pos / Ajukan (Pending)');
        $('#formTambahAdjustment')[0].reset();
        $('#tbodyItemInput').empty();
        inputRowIndex = 0;

        $('#inp_auto_number').prop('checked', true);
        $('#inp_nomor').val('Otomatis (Sistem)').addClass('bg-light').prop('readonly', true);
        
        tambahBarisItemInput();
        kalkulasiTotalInput();
        $('#modalTambahAdjustment').modal('show');
    }

    $('#modalTambahAdjustment').on('show.bs.modal', function () {
        if ($('#tbodyItemInput tr').length === 0) {
            tambahBarisItemInput();
        }
    });

    $('#inp_auto_number').on('change', function() {
        if ($(this).is(':checked')) {
            $('#inp_nomor').val('Otomatis (Sistem)').addClass('bg-light').prop('readonly', true);
        } else {
            $('#inp_nomor').val('').removeClass('bg-light').prop('readonly', false).focus();
        }
    });

    function tambahBarisItemInput() {
        if ($('#tbodyItemInput tr').length >= 25) {
            Swal.fire({
                icon: 'warning',
                title: 'Mencapai Batas Maksimal!',
                text: 'Maksimal 25 baris barang per dokumen adjustment demi kestabilan & keamanan transaksi.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        inputRowIndex++;
        const rowId = `row_inp_${inputRowIndex}`;

        const hppColsHtml = canSeeHppInput ? `
            <td class="text-end">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted px-2 small">Rp</span>
                    <input type="text" name="items[${inputRowIndex}][harga_satuan]" class="form-control form-control-sm text-end font-monospace fw-semibold inp-hpp-input inp-hpp-formatted rounded-end-2" placeholder="0" required data-row="${rowId}">
                </div>
            </td>
            <td class="text-end fw-bold text-success font-monospace inp-total-${rowId}">Rp 0</td>
        ` : `
            <input type="hidden" name="items[${inputRowIndex}][harga_satuan]" class="inp-hpp-input" value="0" data-row="${rowId}">
        `;

        const trHtml = `
            <tr id="${rowId}">
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="product-thumb-box border rounded-3 d-flex align-items-center justify-content-center bg-light cursor-pointer shadow-xs position-relative flex-shrink-0"
                             style="width: 38px; height: 38px; overflow: hidden;"
                             onclick="triggerPreviewFotoRow('${rowId}')"
                             title="Klik untuk lihat foto barang">
                            <img src="" id="img_thumb_${rowId}" class="product-img d-none" style="width: 100%; height: 100%; object-fit: cover;">
                            <i class="bi bi-image text-muted fs-6 icon-no-photo-${rowId}"></i>
                        </div>

                        <div class="d-flex flex-column flex-grow-1">
                            <div class="input-group input-group-sm shadow-xs">
                                <input type="hidden" name="items[${inputRowIndex}][barang_id]" class="inp-barang-id" required data-row="${rowId}">
                                <input type="hidden" name="items[${inputRowIndex}][serial_numbers]" class="inp-serial-numbers" value="[]" data-row="${rowId}">
                                <input type="hidden" class="inp-has-serials" value="0">
                                <input type="hidden" id="foto_url_${rowId}" class="inp-foto-url">
                                <input type="text" class="form-control form-control-sm bg-light inp-barang-name cursor-pointer" 
                                       placeholder="-- Klik 🔍 untuk cari barang --" readonly required data-row="${rowId}"
                                       onclick="bukaModalLookupBarangAdj('${rowId}', 'input')">
                                <button type="button" class="btn btn-primary btn-search-barang-adj px-2.5" onclick="bukaModalLookupBarangAdj('${rowId}', 'input')" title="Cari Barang">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <div class="mt-1 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-manage-sn rounded-pill px-2.5 py-0.5 fw-semibold d-none" style="font-size: 0.72rem;" onclick="openModalManageSerialsAdj('${rowId}', 'input')">
                                    <i class="bi bi-upc-scan me-1"></i><span class="sn-btn-text">Kelola Serial (0/0)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-10 px-2 py-1.5 rounded-2 fw-semibold font-monospace" id="stok_aktual_${rowId}">-</span>
                </td>
                <td>
                    <input type="number" step="any" min="0.0001" name="items[${inputRowIndex}][jumlah]" class="form-control form-control-sm text-center fw-semibold inp-qty-input rounded-2" placeholder="0" required data-row="${rowId}">
                </td>
                ${hppColsHtml}
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1.5" onclick="hapusBarisItemInput('${rowId}')" title="Hapus Baris">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbodyItemInput').append(trHtml);
        updateRowCounterInput();
    }

    function hapusBarisItemInput(rowId) {
        if ($('#tbodyItemInput tr').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 item barang!', 'info');
            return;
        }
        $(`#${rowId}`).remove();
        kalkulasiTotalInput();
        updateRowCounterInput();
    }

    function updateRowCounterInput() {
        const count = $('#tbodyItemInput tr').length;
        $('#badgeItemCountInput').text(count + ' Item');
    }

    function validateRowStokInput(rowId) {
        const tipe = $('#inp_tipe').val();
        const stok = parseFloat($(`#${rowId}`).attr('data-stok')) || 0;
        const qty = parseFloat($(`#${rowId} .inp-qty-input`).val()) || 0;
        const $inputQty = $(`#${rowId} .inp-qty-input`);
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

    $(document).on('input', '.inp-qty-input', function() {
        const rowId = $(this).data('row');
        validateRowStokInput(rowId);
        hitungkanSubtotalRowInput(rowId);
    });

    $('#inp_tipe').on('change', function() {
        $('#tbodyItemInput tr').each(function() {
            validateRowStokInput($(this).attr('id'));
        });
    });

    $(document).on('input', '.inp-hpp-formatted', function() {
        let clean = this.value.replace(/[^0-9,]/g, '');
        let parts = clean.split(',');
        let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        let decimalPart = parts.length > 1 ? ',' + parts[1].substring(0, 2) : '';

        this.value = integerPart + decimalPart;

        const rowId = $(this).data('row');
        if ($(this).hasClass('inp-hpp-input')) {
            hitungkanSubtotalRowInput(rowId);
        } else if ($(this).hasClass('inp-hpp-edit')) {
            hitungkanSubtotalRowEdit(rowId);
        }
    });

    function unformatNumberID(val) {
        if (val === null || val === undefined || val === '') return 0;
        // Strip thousand-separator dots, convert comma (Indonesian decimal) → dot
        let str = val.toString().replace(/\./g, '').replace(',', '.');
        let num = parseFloat(str);
        return isNaN(num) ? 0 : num;
    }

    function formatRibuanID(val) {
        if (val === null || val === undefined || val === '') return '0';
        let num;
        if (typeof val === 'number') {
            // Already a JS number — use directly
            num = val;
        } else {
            let str = String(val).trim();
            if (str.indexOf(',') >= 0) {
                // Indonesian format (user-input): comma = decimal separator
                // e.g. "785.000,50" or "1.250,75"
                num = unformatNumberID(str);
            } else {
                // Standard/API format: dot = decimal separator
                // e.g. "785000.50" from PHP JSON serialization
                num = parseFloat(str) || 0;
            }
        }
        if (isNaN(num) || num < 0) return '0';
        let parts = num.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return parts[1] === '00' ? parts[0] : parts[0] + ',' + parts[1];
    }

    function hitungkanSubtotalRowInput(rowId) {
        const qty = parseFloat($(`#${rowId} .inp-qty-input`).val()) || 0;
        const hpp = unformatNumberID($(`#${rowId} .inp-hpp-input`).val());
        const total = qty * hpp;

        $(`.inp-total-${rowId}`).text('Rp ' + numberFormatCurrencyID(total));
        kalkulasiTotalInput();
    }

    let isCalcInputPending = false;
    function kalkulasiTotalInput() {
        if (isCalcInputPending) return;
        isCalcInputPending = true;

        requestAnimationFrame(() => {
            let totalQty = 0;
            let totalNilai = 0;

            $('#tbodyItemInput tr').each(function() {
                const qty = parseFloat($(this).find('.inp-qty-input').val()) || 0;
                const hpp = unformatNumberID($(this).find('.inp-hpp-input').val());
                totalQty += qty;
                totalNilai += (qty * hpp);
            });

            $('#lblTotalQtyInput').text(numberFormatID(totalQty) + ' Qty');
            $('#lblTotalNilaiInput').text('Rp ' + numberFormatCurrencyID(totalNilai));
            isCalcInputPending = false;
        });
    }

    function numberFormatCurrencyID(val) {
        let num = (typeof val === 'number') ? val : (parseFloat(val) || 0);
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    function numberFormatID(val) {
        // Safely convert string from API (e.g. "785000.50") to number before formatting
        let num = (typeof val === 'number') ? val : (parseFloat(val) || 0);
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 4 }).format(num);
    }

    // Submit Form Input AJAX
    function submitFormInputAdjustment(submitAction = 'draft') {
        const $form = $('#formTambahAdjustment');
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        const $btnDraft = $('#btnDraftInputAdjustment');
        const $btnPos   = $('#btnPosInputAdjustment');

        // Cegah klik ganda / duplikasi request
        if ($btnDraft.prop('disabled') || $btnPos.prop('disabled')) {
            return;
        }

        const oldDraftHtml = '<i class="bi bi-file-earmark-text me-1"></i> Simpan Draft';
        const oldPosHtml   = '<i class="bi bi-send-fill me-1"></i> Pos / Ajukan (Pending)';
        const $activeBtn   = (submitAction === 'draft') ? $btnDraft : $btnPos;

        const restoreBtns = function() {
            $btnDraft.prop('disabled', false).html(oldDraftHtml);
            $btnPos.prop('disabled', false).html(oldPosHtml);
        };

        // Kunci kedua tombol & munculkan loading spinner pada tombol aktif
        $btnDraft.prop('disabled', true);
        $btnPos.prop('disabled', true);
        const spinnerText = (submitAction === 'draft') ? 'Menyimpan Draft...' : 'Mengajukan...';
        $activeBtn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${spinnerText}`);

        const formData = {
            _token: '{{ csrf_token() }}',
            submit_action: submitAction,
            gudang_id: $('#inp_gudang_id').val(),
            tanggal_adjustment: $('#inp_tanggal').val(),
            tipe_adjustment: $('#inp_tipe').val(),
            kategori_alasan: $('#inp_kategori').val(),
            auto_number: $('#inp_auto_number').is(':checked') ? 1 : 0,
            nomor_adjustment: $('#inp_nomor').val(),
            keterangan: $('#inp_keterangan').val(),
            items: []
        };

        // Validasi Keamanan: Pengurangan Stok Tidak Boleh Melebihi Stok Tersedia
        if (formData.tipe_adjustment === 'Pengurangan') {
            let adaMinus = false;
            let barangMinus = '';
            let stokTersedia = 0;
            let qtyDiminta = 0;

            $('#tbodyItemInput tr').each(function() {
                const barangId = $(this).find('.inp-barang-id').val();
                const barangName = $(this).find('.inp-barang-name').val() || 'Barang';
                const qty = parseFloat($(this).find('.inp-qty-input').val()) || 0;
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
                restoreBtns();
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Tidak Mencukupi!',
                    html: `Jumlah pengurangan untuk <b>${barangMinus}</b> melebihi stok yang ada di gudang.<br><br><span class="text-danger fw-bold">Stok Tersedia: ${numberFormatID(stokTersedia)} | Diminta: ${numberFormatID(qtyDiminta)}</span><br><br><small class="text-muted">Sistem tidak mengizinkan stok bernilai minus/negatif.</small>`,
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
        }

        let invalidHpp = false;
        let invalidBarangName = '';
        let invalidSn = false;
        let invalidSnMessage = '';

        $('#tbodyItemInput tr').each(function() {
            const barangId = $(this).find('.inp-barang-id').val();
            const barangName = $(this).find('.inp-barang-name').val() || 'Barang';
            const qty = parseFloat($(this).find('.inp-qty-input').val()) || 0;
            const hpp = unformatNumberID($(this).find('.inp-hpp-input').val());
            const hasSerials = $(this).find('.inp-has-serials').val() == '1';

            let serials = [];
            const rawSns = $(this).find('.inp-serial-numbers').val();
            if (rawSns) {
                try {
                    serials = Array.isArray(rawSns) ? rawSns : JSON.parse(rawSns);
                } catch(e) {
                    serials = [];
                }
            }

            if (barangId && qty > 0) {
                if (hasSerials && serials.length !== parseInt(qty, 10)) {
                    invalidSn = true;
                    invalidSnMessage = `Barang <b>${barangName}</b> memiliki pelacakan Serial Number. Diperlukan <b>${parseInt(qty, 10)}</b> serial, namun baru <b>${serials.length}</b> serial yang dialokasikan. Harap lengkapi nomor seri terlebih dahulu!`;
                    $(this).find('.btn-manage-sn').removeClass('btn-outline-success text-success').addClass('btn-outline-danger text-danger');
                }

                if (formData.tipe_adjustment === 'Penambahan' && canSeeHppInput && hpp <= 0) {
                    invalidHpp = true;
                    invalidBarangName = barangName;
                    $(this).find('.inp-hpp-input').addClass('is-invalid border-danger');
                } else {
                    $(this).find('.inp-hpp-input').removeClass('is-invalid border-danger');
                }

                formData.items.push({
                    barang_id: barangId,
                    jumlah: qty,
                    harga_satuan: hpp,
                    serial_numbers: (serials.length > 0) ? serials : null
                });
            }
        });

        if (invalidSn) {
            restoreBtns();
            Swal.fire({
                icon: 'warning',
                title: 'Serial Number Belum Lengkap!',
                html: invalidSnMessage,
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        if (invalidHpp) {
            restoreBtns();
            Swal.fire({
                icon: 'warning',
                title: 'HPP Wajib Diisi!',
                text: `HPP untuk barang ${invalidBarangName} tidak boleh Rp 0 pada penambahan barang masuk. Silakan masukkan nilai HPP yang valid.`,
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        if (formData.items.length === 0) {
            restoreBtns();
            Swal.fire('Validasi Gagal', 'Silakan pilih minimal 1 item barang yang valid!', 'warning');
            return;
        }

        $.ajax({
            url: '{{ route("stock-adjustment.store") }}',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(res) {
                if (typeof window.hideLoader === 'function') window.hideLoader();
                $('#modalTambahAdjustment').modal('hide');
                reloadTableAdjustment();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false,
                    position: 'center'
                });
            },
            error: function(xhr) {
                const res = xhr.responseJSON ?? {};
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan server.', 'error');
            },
            complete: function() {
                restoreBtns();
            }
        });
    }

    // Sync serial button counter saat QTY berubah
    $(document).on('input change', '.inp-qty-input', function() {
        const $row = $(this).closest('tr');
        const hasSerials = ($row.find('.inp-has-serials').val() == '1');
        if (hasSerials) {
            const targetQty = parseInt($(this).val() || 0, 10);
            let sns = [];
            try {
                sns = JSON.parse($row.find('.inp-serial-numbers').val() || '[]');
            } catch(e) {}
            const count = sns.length;
            const btnClass = (count === targetQty && targetQty > 0) ? 'btn-outline-success text-success' : 'btn-outline-danger text-danger';
            $row.find('.btn-manage-sn').removeClass('btn-outline-success text-success btn-outline-danger text-danger')
                .addClass(btnClass)
                .find('.sn-btn-text').text(`Serial (${count}/${targetQty})`);
        }
    });
</script>
@endpush
