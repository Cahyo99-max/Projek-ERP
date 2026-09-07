<script>
$(document).ready(function () {
    // Options HTML for COAs
    let coaOptionsHtml = '';
    @foreach($coas as $c)
    coaOptionsHtml += '<option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>';
    @endforeach

    let defaultGrniCoaId = {{ $defaultGrniCoaId ?? 'null' }};
    let defaultPpnCoaId = {{ $defaultPpnCoaId ?? 'null' }};
    let defaultFreightCoaId = {{ $defaultFreightCoaId ?? 'null' }};
    let createExtraCostCount = 0;

    // Currency state for create modal
    let createCurrencyCode = 'IDR';
    let createCurrencySymbol = 'Rp';
    let createKurs = 1.0;

    function detectCoaForExtraCost(jenis, isHpp) {
        if (isHpp === '1' || isHpp === 1 || isHpp === true) {
            return defaultGrniCoaId;
        }
        let j = (jenis || '').toLowerCase().trim();
        if (j.includes('kirim') || j.includes('ongkir') || j.includes('freight') || j.includes('transport') || j.includes('antar') || j.includes('ekspedisi')) {
            return defaultFreightCoaId || defaultGrniCoaId;
        }
        if (j.includes('bongkar') || j.includes('muat') || j.includes('handling') || j.includes('buruh') || j.includes('kuli')) {
            return defaultFreightCoaId || defaultGrniCoaId;
        }
        if (j.includes('admin') || j.includes('bank') || j.includes('provisi') || j.includes('transfer')) {
            let adminCoa = null;
            @foreach($coas as $c)
                @if(str_contains(strtolower($c->name), 'administrasi') || str_starts_with($c->code, '8101') || str_starts_with($c->code, '6105'))
                    if (!adminCoa) adminCoa = {{ $c->id }};
                @endif
            @endforeach
            return adminCoa || defaultFreightCoaId || defaultGrniCoaId;
        }
        if (j.includes('asuransi') || j.includes('insurance')) {
            let insCoa = null;
            @foreach($coas as $c)
                @if(str_contains(strtolower($c->name), 'asuransi') || str_starts_with($c->code, '6103'))
                    if (!insCoa) insCoa = {{ $c->id }};
                @endif
            @endforeach
            return insCoa || defaultFreightCoaId || defaultGrniCoaId;
        }
        return defaultFreightCoaId || defaultGrniCoaId;
    }

    function updateCoaOriginHint(container, isAuto, text, type) {
        let colorClass = type === 'info' ? 'text-info' : (type === 'warning' ? 'text-warning-emphasis' : (type === 'manual' ? 'text-secondary' : 'text-primary'));
        let iconClass = type === 'info' ? 'bi-cpu' : (type === 'manual' ? 'bi-pencil-square' : 'bi-magic');
        let badgeText = text || (isAuto ? 'Otomatis dari Pemetaan Akun' : 'Dipilih Manual');
        
        container.html('<small class="d-block mt-1 coa-mapping-hint ' + colorClass + '" style="font-size:0.68rem; line-height:1.2;">' +
            '<i class="bi ' + iconClass + ' me-1"></i><span class="badge-coa-origin fw-medium">' + badgeText + '</span>' +
        '</small>');
    }

    function refreshExtraCostCoaHint(tr) {
        let isHpp = tr.find('.select-hpp-extra').val();
        let jenisVal = tr.find('input[name$="[jenis]"]').val();
        let currentCoa = tr.find('.select-coa-extra').val();
        let suggestedCoa = detectCoaForExtraCost(jenisVal, isHpp);
        let hint = tr.find('.hint-coa-extra');

        if (currentCoa == suggestedCoa) {
            if (isHpp === '1' || isHpp === 1) {
                updateCoaOriginHint(hint, true, 'Otomatis dari Pemetaan GRNI', 'info');
            } else {
                updateCoaOriginHint(hint, true, 'Otomatis dari Pemetaan Biaya', 'warning');
            }
        } else {
            updateCoaOriginHint(hint, false, 'Dipilih Manual', 'manual');
        }
    }

    function updateCreateCurrencyDisplay(code, symbol, kurs) {
        createCurrencyCode = code || 'IDR';
        createCurrencySymbol = symbol || (createCurrencyCode === 'USD' ? '$' : 'Rp');
        createKurs = parseFloat(kurs) || 1.0;

        $('#create_disp_currency').val(createCurrencyCode + (createCurrencyCode === 'USD' ? ' (US Dollar)' : ' (Rupiah)'));
        $('#create_disp_kurs').val(formatSmartKurs(createKurs));
        $('#formTambahInvoice .currency-symbol-addon').text(createCurrencySymbol);
    }

    function resetCreateInvoiceModal() {
        $('#formTambahInvoice')[0].reset();
        $('#create_gr_id').val('');
        $('#create_disp_gr_ref').val('');
        $('#create_items_tbody').html('<tr id="create_empty_item_row"><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-box-arrow-in-down fs-3 d-block mb-2 text-secondary opacity-50"></i>Pilih Dokumen GRN terlebih dahulu untuk memuat daftar barang yang ditagihkan.</td></tr>');
        $('#create_extra_costs_tbody').html('<tr id="create_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');
        createExtraCostCount = 0;
        updateCreateCurrencyDisplay('IDR', 'Rp', 1.0);
        recalculateCreateSummary();
    }

    // Browse GRN Click
    $('#create_btn_browse_gr, #create_disp_gr_ref').on('click', function () {
        $('#modalBrowseGR').modal('show');
    });

    $('#search_gr_lookup').on('keyup', function () {
        let val = $(this).val().toLowerCase();
        $('#tableBrowseGR tbody tr.row-gr-item').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    $(document).on('click', '.btn-select-gr-row, .row-gr-item', function (e) {
        let tr = $(this).hasClass('row-gr-item') ? $(this) : $(this).closest('tr.row-gr-item');
        let grId = tr.data('id');
        let grNomor = tr.data('nomor');
        let supplierName = tr.data('supplier');
        let gudangName = tr.data('gudang');
        let poNomor = tr.data('po');
        let curCode = tr.data('currency-code') || 'IDR';
        let curSymbol = tr.data('currency-symbol') || (curCode === 'USD' ? '$' : 'Rp');
        let kurs = tr.data('kurs') || 1.0;
        let pTerm = tr.data('payment-term');
        let pajakId = tr.data('pajak');

        $('#create_gr_id').val(grId);
        $('#create_disp_gr_ref').val(grNomor + ' - ' + supplierName + ' (PO: ' + poNomor + ')');
        $('#create_disp_supplier').val(supplierName);
        $('#create_disp_supplier_txt').text(supplierName);
        $('#create_sb_supplier').text(supplierName);
        $('#create_disp_gudang').val(gudangName);
        $('#create_disp_gudang_txt').text(gudangName || '-');
        $('#create_disp_po_nomor').val(poNomor);
        $('#create_disp_po_nomor_txt').text(poNomor || '-');
        $('#create_sb_po').text(poNomor || '-');
        $('#create_sb_grn').text(grNomor || '-');

        updateCreateCurrencyDisplay(curCode, curSymbol, kurs);

        if (pTerm) $('#create_payment_term_id').val(pTerm).trigger('change');
        if (pajakId) $('#create_pajak_id').val(pajakId).trigger('change');

        // Fetch GR Detail items & pre-allocated costs
        $.get('/purchase-invoices/gr/' + grId + '/details', function (res) {
            if (res && res.gr && res.gr.details) {
                let tbodyHtml = '';
                let totalItemTypes = 0;
                let totalQty = 0;

                res.gr.details.forEach(function (d, idx) {
                    totalItemTypes++;
                    let qty = parseFloat(d.jumlah || 0);
                    totalQty += qty;
                    let barangKode = d.barang ? d.barang.kode_barang : '-';
                    let barangNama = d.barang ? d.barang.nama_barang : (d.keterangan || 'Barang');
                    let satuan = d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'pcs';
                    let poDetail = d.po_detail || d.poDetail || {};
                    let hargaSatuan = parseFloat(poDetail.harga_satuan || 0);
                    let diskonItem = parseFloat(poDetail.diskon_item || 0);

                    // PO Header Global Discount fallback
                    if (diskonItem <= 0 && res.gr.po) {
                        let po = res.gr.po;
                        if (parseFloat(po.diskon_persen || 0) > 0) {
                            diskonItem = hargaSatuan * (parseFloat(po.diskon_persen) / 100);
                        } else if (parseFloat(po.total_diskon || 0) > 0 && parseFloat(po.subtotal || 0) > 0) {
                            diskonItem = (parseFloat(po.total_diskon) / parseFloat(po.subtotal)) * hargaSatuan;
                        }
                    }

                    let lineSubtotalGross = qty * hargaSatuan;
                    let lineDiskonTotal = qty * diskonItem;
                    let lineSubtotalNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;

                    tbodyHtml += '<tr class="row-item-barang" data-row="' + totalItemTypes + '">' +
                        '<td class="text-center">' + totalItemTypes + '</td>' +
                        '<td class="fw-semibold text-primary">' + escapeHtml(barangKode) + '</td>' +
                        '<td class="fw-semibold text-body">' + escapeHtml(barangNama) + '<input type="hidden" name="items[' + d.id + '][gr_detail_id]" value="' + d.id + '"></td>' +
                        '<td>' +
                            '<select class="form-select form-select-sm rounded-2 select-item-coa" name="items[' + d.id + '][coa_id]">' + coaOptionsHtml + '</select>' +
                            '<div class="hint-coa-item">' +
                                '<small class="d-block mt-1 coa-mapping-hint text-primary" style="font-size:0.68rem; line-height:1.2;">' +
                                    '<i class="bi bi-magic me-1"></i><span class="badge-coa-origin fw-medium">Otomatis dari Pemetaan Akun</span>' +
                                '</small>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center font-monospace fw-bold">' + qty + '</td>' +
                        '<td class="small text-muted">' + escapeHtml(satuan) + '</td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm">' +
                                '<span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                '<input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold input-nominal-item rounded-end-2" name="items[' + d.id + '][harga_satuan]" value="' + hargaSatuan + '" data-qty="' + qty + '" data-diskon-item="' + diskonItem + '">' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-end text-danger fw-semibold val-item-diskon">' + (lineDiskonTotal > 0 ? '- ' + formatMoney(lineDiskonTotal, createCurrencyCode, createCurrencySymbol) : formatNumberOnly(0, createCurrencyCode)) + '</td>' +
                        '<td class="text-end fw-bold text-success val-item-subtotal">' + formatMoney(lineSubtotalNet, createCurrencyCode, createCurrencySymbol) + '</td>' +
                    '</tr>';
                });

                $('#create_sb_total_barang').text(totalItemTypes);
                $('#create_sb_total_qty').text(totalQty);
                $('#create_items_tbody').html(tbodyHtml);

                // Set pre-resolved COA
                res.gr.details.forEach(function (d) {
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;
                    let row = $('#create_items_tbody tr.row-item-barang').filter(function() {
                        return $(this).find('input[name$="[gr_detail_id]"]').val() == d.id;
                    });
                    row.find('.select-item-coa').val(resolvedCoaId);
                });

                // Load extra costs from PO if any
                if (res.biaya_lain_details && res.biaya_lain_details.length > 0) {
                    $('#create_empty_extra_cost_row').remove();
                    let extraHtml = '';
                    res.biaya_lain_details.forEach(function (ex) {
                        createExtraCostCount++;
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);

                        extraHtml += '<tr class="row-extra-cost" data-row="' + createExtraCostCount + '">' +
                            '<td>' +
                                '<input type="text" class="form-control form-control-sm rounded-2 fw-semibold input-extra-jenis" name="extra_costs[' + createExtraCostCount + '][jenis]" value="' + escapeHtml(jenisVal) + '">' +
                            '</td>' +
                            '<td>' +
                                '<div class="input-group input-group-sm">' +
                                    '<span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                    '<input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold rounded-end-2 input-nominal-extra" name="extra_costs[' + createExtraCostCount + '][nominal]" value="' + parseFloat(ex.nominal || 0) + '">' +
                                '</div>' +
                            '</td>' +
                            '<td class="text-center">' +
                                '<select class="form-select form-select-sm rounded-2 select-hpp-extra" name="extra_costs[' + createExtraCostCount + '][masuk_hpp]">' +
                                    '<option value="0"' + (isHpp === 0 ? ' selected' : '') + '>Tidak</option>' +
                                    '<option value="1"' + (isHpp === 1 ? ' selected' : '') + '>Ya (HPP)</option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="cell-coa-extra">' +
                                '<select class="form-select form-select-sm rounded-2 select-coa-extra" name="extra_costs[' + createExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                '<div class="hint-coa-extra"></div>' +
                            '</td>' +
                            '<td><input type="text" class="form-control form-control-sm rounded-2" name="extra_costs[' + createExtraCostCount + '][keterangan]" value="' + escapeHtml(ex.keterangan || '') + '"></td>' +
                            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                        '</tr>';
                    });
                    $('#create_extra_costs_tbody').html(extraHtml);

                    // Set extra cost COAs
                    let extraRows = $('#create_extra_costs_tbody tr.row-extra-cost');
                    res.biaya_lain_details.forEach(function (ex, idx) {
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);
                        let tr = extraRows.eq(idx);
                        tr.find('.select-coa-extra').val(autoCoa);
                        refreshExtraCostCoaHint(tr);
                    });
                }

                recalculateCreateSummary();
            }
        });

        $('#modalBrowseGR').modal('hide');
    });

    // Payment Term change calculates Due Date
    $('#create_payment_term_id, #create_tanggal_faktur').on('change', function () {
        let tglFaktur = $('#create_tanggal_faktur').val();
        let termDays = parseInt($('#create_payment_term_id option:selected').data('hari')) || 0;
        if (tglFaktur) {
            let d = new Date(tglFaktur);
            d.setDate(d.getDate() + termDays);
            let yyyy = d.getFullYear();
            let mm = String(d.getMonth() + 1).padStart(2, '0');
            let dd = String(d.getDate()).padStart(2, '0');
            $('#create_tanggal_jatuh_tempo').val(yyyy + '-' + mm + '-' + dd);
        }
    });

    // Add Extra Cost Row
    $('#create_btn_add_extra_cost').on('click', function () {
        $('#create_empty_extra_cost_row').remove();
        createExtraCostCount++;

        let defaultExtraCoa = detectCoaForExtraCost('Ongkos Kirim', 1);

        let newRow = '<tr class="row-extra-cost" data-row="' + createExtraCostCount + '">' +
            '<td>' +
                '<input type="text" class="form-control form-control-sm rounded-2 fw-semibold input-extra-jenis" name="extra_costs[' + createExtraCostCount + '][jenis]" placeholder="Jenis Biaya..." value="Ongkos Kirim">' +
            '</td>' +
            '<td>' +
                '<div class="input-group input-group-sm">' +
                    '<span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(createCurrencySymbol) + '</span>' +
                    '<input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold rounded-end-2 input-nominal-extra" name="extra_costs[' + createExtraCostCount + '][nominal]" value="0">' +
                '</div>' +
            '</td>' +
            '<td class="text-center">' +
                '<select class="form-select form-select-sm rounded-2 select-hpp-extra" name="extra_costs[' + createExtraCostCount + '][masuk_hpp]">' +
                    '<option value="0">Tidak</option>' +
                    '<option value="1" selected>Ya (HPP)</option>' +
                '</select>' +
            '</td>' +
            '<td class="cell-coa-extra">' +
                '<select class="form-select form-select-sm rounded-2 select-coa-extra" name="extra_costs[' + createExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                '<div class="hint-coa-extra"></div>' +
            '</td>' +
            '<td><input type="text" class="form-control form-control-sm rounded-2" name="extra_costs[' + createExtraCostCount + '][keterangan]" placeholder="Keterangan..."></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

        $('#create_extra_costs_tbody').append(newRow);
        let tr = $('#create_extra_costs_tbody tr.row-extra-cost').last();
        if (defaultExtraCoa) {
            tr.find('.select-coa-extra').val(defaultExtraCoa);
        }
        refreshExtraCostCoaHint(tr);
        recalculateCreateSummary();
    });

    $(document).on('change', '#modalTambahInvoice .select-item-coa', function () {
        let val = $(this).val();
        let hint = $(this).closest('td').find('.hint-coa-item');
        if (val == defaultGrniCoaId) {
            updateCoaOriginHint(hint, true, 'Otomatis dari Pemetaan Akun', 'primary');
        } else {
            updateCoaOriginHint(hint, false, 'Dipilih Manual', 'manual');
        }
    });

    $(document).on('change', '#modalTambahInvoice .select-hpp-extra', function () {
        let tr = $(this).closest('tr');
        let isHpp = $(this).val();
        let jenisVal = tr.find('input[name$="[jenis]"]').val();
        let suggestedCoa = detectCoaForExtraCost(jenisVal, isHpp);
        if (suggestedCoa) {
            tr.find('.select-coa-extra').val(suggestedCoa);
        }
        refreshExtraCostCoaHint(tr);
        recalculateCreateSummary();
    });

    $(document).on('input change', '#modalTambahInvoice input[name$="[jenis]"]', function () {
        let tr = $(this).closest('tr.row-extra-cost');
        let isHpp = tr.find('.select-hpp-extra').val();
        let jenisVal = $(this).val();
        let suggestedCoa = detectCoaForExtraCost(jenisVal, isHpp);
        if (suggestedCoa) {
            tr.find('.select-coa-extra').val(suggestedCoa);
        }
        refreshExtraCostCoaHint(tr);
    });

    $(document).on('change', '#modalTambahInvoice .select-coa-extra', function () {
        let tr = $(this).closest('tr.row-extra-cost');
        refreshExtraCostCoaHint(tr);
    });

    $(document).on('click', '#modalTambahInvoice .btn-remove-extra-cost', function () {
        $(this).closest('tr').remove();
        if ($('#create_extra_costs_tbody tr').length === 0) {
            $('#create_extra_costs_tbody').html('<tr id="create_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');
        }
        recalculateCreateSummary();
    });

    $(document).on('input change', '#modalTambahInvoice .input-nominal-item, #modalTambahInvoice .input-nominal-extra, #create_pajak_id', function () {
        recalculateCreateSummary();
    });

    function recalculateCreateSummary() {
        let subtotalBarangGross = 0;
        let totalDiskon = 0;

        $('#create_items_tbody tr.row-item-barang').each(function () {
            let price = parseFloat($(this).find('.input-nominal-item').val()) || 0;
            let qty = parseFloat($(this).find('.input-nominal-item').data('qty')) || 0;
            let diskonItem = parseFloat($(this).find('.input-nominal-item').data('diskon-item')) || 0;

            let lineGross = price * qty;
            let lineDiskon = diskonItem * qty;
            let lineNet = Math.max(0, lineGross - lineDiskon);

            $(this).find('.val-item-diskon').text(lineDiskon > 0 ? '- ' + formatMoney(lineDiskon, createCurrencyCode, createCurrencySymbol) : formatNumberOnly(0, createCurrencyCode));
            $(this).find('.val-item-subtotal').text(formatMoney(lineNet, createCurrencyCode, createCurrencySymbol));

            subtotalBarangGross += lineGross;
            totalDiskon += lineDiskon;
        });

        let dpp = Math.max(0, subtotalBarangGross - totalDiskon);

        let biayaHppTotal = 0;
        let biayaNonHppTotal = 0;
        $('#create_extra_costs_tbody tr.row-extra-cost').each(function () {
            let nom = parseFloat($(this).find('.input-nominal-extra').val()) || 0;
            let isHpp = $(this).find('.select-hpp-extra').val() === '1';
            if (isHpp) {
                biayaHppTotal += nom;
            } else {
                biayaNonHppTotal += nom;
            }
        });

        let totalBiayaLain = biayaHppTotal + biayaNonHppTotal;

        let selectedPajakOpt = $('#create_pajak_id option:selected');
        let tarif = selectedPajakOpt.data('tarif') !== undefined ? parseFloat(selectedPajakOpt.data('tarif')) : 0;
        if ($('#create_pajak_id').val() === '') {
            tarif = 0;
        }
        let ppnNominal = dpp * (tarif / 100);

        $('#create_label_ppn').text('PPN Masukan (' + tarif + '%)');

        let totalInvoice = dpp + totalBiayaLain + ppnNominal;

        $('#create_sum_subtotal').text(formatNumberOnly(subtotalBarangGross, createCurrencyCode));
        $('#create_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, createCurrencyCode));
        $('#create_sum_dpp').text(formatNumberOnly(dpp, createCurrencyCode));
        $('#create_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, createCurrencyCode));
        $('#create_sum_ppn').text(formatNumberOnly(ppnNominal, createCurrencyCode));
        $('#create_sum_total_invoice').text(formatMoney(totalInvoice, createCurrencyCode, createCurrencySymbol));

        if (createCurrencyCode !== 'IDR' && createKurs > 1) {
            let totalIdr = totalInvoice * createKurs;
            $('#create_sum_total_invoice_idr').html('<i class="bi bi-arrow-repeat me-1"></i>Ekuivalen: <strong>Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totalIdr) + '</strong> (Kurs: ' + formatSmartKurs(createKurs) + ')').show();
        } else {
            $('#create_sum_total_invoice_idr').hide().empty();
        }
    }

    // Save Draft Button
    $('#btn_save_draft_invoice').on('click', function () {
        $('#create_action_type').val('draft');
        $('#formTambahInvoice').submit();
    });

    // Save & Post Direct Button
    $('#btn_submit_post_invoice').on('click', function () {
        $('#create_action_type').val('post');
        Swal.fire({
            title: 'Simpan & Posting ke Akuntansi?',
            text: 'Faktur akan disimpan dan langsung menerbitkan Jurnal Utang Usaha otomatis.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Posting Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#formTambahInvoice').submit();
            }
        });
    });

    // Form Submit Create Handler
    $('#formTambahInvoice').on('submit', function (e) {
        e.preventDefault();
        let actionUrl = $(this).attr('action');
        let formData = new FormData(this);

        $.ajax({
            url: actionUrl,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                $('#modalTambahInvoice').modal('hide');
                resetCreateInvoiceModal();
                if (typeof window.reloadInvoiceTable === 'function') {
                    window.reloadInvoiceTable();
                }
                Swal.fire('Berhasil!', res.message, 'success');
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal menyimpan faktur.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });

    // Reset when modal opens
    $('#modalTambahInvoice').on('show.bs.modal', function () {
        resetCreateInvoiceModal();
    });
});
</script>
