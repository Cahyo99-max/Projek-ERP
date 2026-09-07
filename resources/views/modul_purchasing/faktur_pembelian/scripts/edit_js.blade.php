<script>
$(document).ready(function () {
    let coaOptionsHtml = '';
    @foreach($coas as $c)
    coaOptionsHtml += '<option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>';
    @endforeach

    let defaultGrniCoaId = {{ $defaultGrniCoaId ?? 'null' }};
    let defaultPpnCoaId = {{ $defaultPpnCoaId ?? 'null' }};
    let defaultFreightCoaId = {{ $defaultFreightCoaId ?? 'null' }};
    let editExtraCostCount = 0;

    let editCurrencyCode = 'IDR';
    let editCurrencySymbol = 'Rp';
    let editKurs = 1.0;

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

    function refreshEditExtraCostCoaHint(tr) {
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

    function updateEditCurrencyDisplay(code, symbol, kurs) {
        editCurrencyCode = code || 'IDR';
        editCurrencySymbol = symbol || (editCurrencyCode === 'USD' ? '$' : 'Rp');
        editKurs = parseFloat(kurs) || 1.0;

        $('#edit_disp_currency').val(editCurrencyCode + (editCurrencyCode === 'USD' ? ' (US Dollar)' : ' (Rupiah)'));
        $('#edit_disp_kurs').val(formatSmartKurs(editKurs));
        $('#formEditInvoice .currency-symbol-addon').text(editCurrencySymbol);
    }

    // Trigger Edit Click
    $(document).on('click', '.btn-edit-invoice', function () {
        let id = $(this).data('id');
        $.get('/purchase-invoices/' + id, function (res) {
            if (res && res.invoice) {
                let inv = res.invoice;

                $('#edit_invoice_id').val(inv.id);
                $('#formEditInvoice').attr('action', '/purchase-invoices/' + inv.id + '/update');
                $('#modalEditInvoiceLabel').html('Edit Faktur Pembelian (' + escapeHtml(inv.nomor_faktur) + ')');

                $('#edit_disp_nomor_faktur_internal').val(inv.nomor_faktur || '');
                $('#edit_nomor_faktur_supplier').val(inv.nomor_faktur_supplier || '');
                $('#edit_tanggal_faktur').val(inv.tanggal_faktur ? String(inv.tanggal_faktur).substring(0, 10) : '');
                $('#edit_tanggal_jatuh_tempo').val(inv.tanggal_jatuh_tempo ? String(inv.tanggal_jatuh_tempo).substring(0, 10) : '');
                if (inv.payment_term_id) $('#edit_payment_term_id').val(inv.payment_term_id);
                if (inv.pajak_id) $('#edit_pajak_id').val(inv.pajak_id);
                $('#edit_keterangan').val(inv.keterangan || '');

                let sup = inv.supplier || (inv.goods_receipt && inv.goods_receipt.po ? inv.goods_receipt.po.supplier : {});
                let po = inv.po || (inv.goods_receipt ? inv.goods_receipt.po : {});
                let gdg = (inv.goods_receipt && inv.goods_receipt.gudang) ? inv.goods_receipt.gudang.nama_gudang : '-';
                let grnNum = inv.goods_receipt ? inv.goods_receipt.nomor_gr : '-';

                $('#edit_disp_gr_ref').val(grnNum + ' - ' + (sup.nama_supplier || '-') + ' (PO: ' + (po ? po.nomor_po || '-' : '-') + ')');
                $('#edit_disp_supplier').val(sup.nama_supplier || '-');
                $('#edit_disp_supplier_txt').text(sup.nama_supplier || '-');
                $('#edit_sb_supplier').text(sup.nama_supplier || '-');
                $('#edit_disp_gudang').val(gdg);
                $('#edit_disp_gudang_txt').text(gdg);
                $('#edit_disp_po_nomor').val(po ? po.nomor_po || '-' : '-');
                $('#edit_disp_po_nomor_txt').text(po ? po.nomor_po || '-' : '-');
                $('#edit_sb_po').text(po ? po.nomor_po || '-' : '-');
                $('#edit_sb_grn').text(grnNum);
                $('#edit_disp_npwp_txt').text(sup.npwp || '-');

                let curCode = (inv.currency && inv.currency.kode_currency) 
                    ? inv.currency.kode_currency 
                    : ((po && po.currency && po.currency.kode_currency) ? po.currency.kode_currency : 'IDR');
                let curSymbol = (inv.currency && inv.currency.simbol) 
                    ? inv.currency.simbol 
                    : ((po && po.currency && po.currency.simbol) ? po.currency.simbol : (curCode === 'USD' ? '$' : 'Rp'));
                let kursVal = inv.kurs ? parseFloat(inv.kurs) : ((po && po.kurs) ? parseFloat(po.kurs) : 1.0);

                updateEditCurrencyDisplay(curCode, curSymbol, kursVal);

                // Render Item Rows
                let tbodyHtml = '';
                let extraHtml = '';
                let totalItemTypes = 0;
                let totalQty = 0;
                editExtraCostCount = 0;

                if (inv.details && inv.details.length > 0) {
                    inv.details.forEach(function (d, idx) {
                        if (d.jenis === 'Barang') {
                            totalItemTypes++;
                            let qty = parseFloat(d.jumlah || 0);
                            totalQty += qty;
                            let barangKode = d.barang ? d.barang.kode_barang : '-';
                            let barangNama = d.barang ? d.barang.nama_barang : (d.keterangan || 'Barang');
                            let satuan = d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'pcs';
                            let hargaSatuan = parseFloat(d.harga_satuan || 0);
                            let poDetail = d.gr_detail ? (d.gr_detail.po_detail || d.gr_detail.poDetail || {}) : {};
                            let diskonItem = parseFloat(poDetail.diskon_item || 0);
                            let lineSubtotal = qty * hargaSatuan;
                            let lineDiskonTotal = qty * diskonItem;

                            tbodyHtml += '<tr class="row-item-barang" data-row="' + totalItemTypes + '">' +
                                '<td class="text-center">' + totalItemTypes + '</td>' +
                                '<td class="fw-semibold text-primary">' + escapeHtml(barangKode) + '</td>' +
                                '<td class="fw-semibold text-body">' + escapeHtml(barangNama) + '<input type="hidden" name="items[' + d.gr_detail_id + '][gr_detail_id]" value="' + d.gr_detail_id + '"></td>' +
                                '<td>' +
                                    '<select class="form-select form-select-sm rounded-2 select-item-coa" name="items[' + d.gr_detail_id + '][coa_id]">' + coaOptionsHtml + '</select>' +
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
                                        '<span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(editCurrencySymbol) + '</span>' +
                                        '<input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold input-nominal-item rounded-end-2" name="items[' + d.gr_detail_id + '][harga_satuan]" value="' + hargaSatuan + '" data-qty="' + qty + '" data-diskon-item="' + diskonItem + '">' +
                                    '</div>' +
                                '</td>' +
                                '<td class="text-end text-danger fw-semibold val-item-diskon">' + (lineDiskonTotal > 0 ? '- ' + formatMoney(lineDiskonTotal, editCurrencyCode, editCurrencySymbol) : formatNumberOnly(0, editCurrencyCode)) + '</td>' +
                                '<td class="text-end fw-bold text-success val-item-subtotal">' + formatMoney(lineSubtotal, editCurrencyCode, editCurrencySymbol) + '</td>' +
                            '</tr>';
                        } else if (d.jenis !== 'PPN') {
                            editExtraCostCount++;
                            let isHpp = d.masuk_hpp ? 1 : 0;
                            let jenisVal = d.jenis || d.keterangan || 'Biaya Tambahan';

                            extraHtml += '<tr class="row-extra-cost" data-row="' + editExtraCostCount + '">' +
                                '<td>' +
                                    '<input type="text" class="form-control form-control-sm rounded-2 fw-semibold input-extra-jenis" name="extra_costs[' + editExtraCostCount + '][jenis]" value="' + escapeHtml(jenisVal) + '">' +
                                '</td>' +
                                '<td>' +
                                    '<div class="input-group input-group-sm">' +
                                        '<span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(editCurrencySymbol) + '</span>' +
                                        '<input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold rounded-end-2 input-nominal-extra" name="extra_costs[' + editExtraCostCount + '][nominal]" value="' + parseFloat(d.harga_satuan || d.subtotal || 0) + '">' +
                                    '</div>' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<select class="form-select form-select-sm rounded-2 select-hpp-extra" name="extra_costs[' + editExtraCostCount + '][masuk_hpp]">' +
                                        '<option value="0"' + (isHpp === 0 ? ' selected' : '') + '>Tidak</option>' +
                                        '<option value="1"' + (isHpp === 1 ? ' selected' : '') + '>Ya (HPP)</option>' +
                                    '</select>' +
                                '</td>' +
                                '<td class="cell-coa-extra">' +
                                    '<select class="form-select form-select-sm rounded-2 select-coa-extra" name="extra_costs[' + editExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                    '<div class="hint-coa-extra"></div>' +
                                '</td>' +
                                '<td><input type="text" class="form-control form-control-sm rounded-2" name="extra_costs[' + editExtraCostCount + '][keterangan]" value="' + escapeHtml(d.keterangan || '') + '"></td>' +
                                '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                            '</tr>';
                        }
                    });
                }

                $('#edit_sb_total_barang').text(totalItemTypes);
                $('#edit_sb_total_qty').text(totalQty);
                $('#edit_items_tbody').html(tbodyHtml);
                $('#edit_extra_costs_tbody').html(extraHtml || '<tr id="edit_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');

                // Set saved COAs
                if (inv.details && inv.details.length > 0) {
                    let extraIdx = 0;
                    inv.details.forEach(function (d) {
                        if (d.jenis === 'Barang') {
                            let itemCoa = d.coa_id || defaultGrniCoaId;
                            let row = $('#edit_items_tbody tr.row-item-barang').filter(function() {
                                return $(this).find('input[name$="[gr_detail_id]"]').val() == d.gr_detail_id;
                            });
                            row.find('.select-item-coa').val(itemCoa);
                        } else if (d.jenis !== 'PPN') {
                            let isHpp = d.masuk_hpp ? 1 : 0;
                            let extraCoa = d.coa_id || detectCoaForExtraCost(d.jenis || d.keterangan, isHpp);
                            let tr = $('#edit_extra_costs_tbody tr.row-extra-cost').eq(extraIdx);
                            tr.find('.select-coa-extra').val(extraCoa);
                            refreshEditExtraCostCoaHint(tr);
                            extraIdx++;
                        }
                    });
                }

                recalculateEditSummary();
                $('#modalEditInvoice').modal('show');
            }
        });
    });

    // Add Extra Cost in Edit Modal
    $('#edit_btn_add_extra_cost').on('click', function () {
        $('#edit_empty_extra_cost_row').remove();
        editExtraCostCount++;

        let defaultExtraCoa = detectCoaForExtraCost('Ongkos Kirim', 1);

        let newRow = '<tr class="row-extra-cost" data-row="' + editExtraCostCount + '">' +
            '<td><input type="text" class="form-control form-control-sm rounded-2 fw-semibold input-extra-jenis" name="extra_costs[' + editExtraCostCount + '][jenis]" placeholder="Jenis Biaya..." value="Ongkos Kirim"></td>' +
            '<td><div class="input-group input-group-sm"><span class="input-group-text currency-symbol-addon bg-body-tertiary px-2 fw-semibold text-muted font-monospace" style="font-size:0.75rem;">' + escapeHtml(editCurrencySymbol) + '</span><input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold rounded-end-2 input-nominal-extra" name="extra_costs[' + editExtraCostCount + '][nominal]" value="0"></div></td>' +
            '<td class="text-center"><select class="form-select form-select-sm rounded-2 select-hpp-extra" name="extra_costs[' + editExtraCostCount + '][masuk_hpp]"><option value="0">Tidak</option><option value="1" selected>Ya (HPP)</option></select></td>' +
            '<td class="cell-coa-extra"><select class="form-select form-select-sm rounded-2 select-coa-extra" name="extra_costs[' + editExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select><div class="hint-coa-extra"></div></td>' +
            '<td><input type="text" class="form-control form-control-sm rounded-2" name="extra_costs[' + editExtraCostCount + '][keterangan]" placeholder="Keterangan..."></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

        $('#edit_extra_costs_tbody').append(newRow);
        let tr = $('#edit_extra_costs_tbody tr.row-extra-cost').last();
        if (defaultExtraCoa) tr.find('.select-coa-extra').val(defaultExtraCoa);
        refreshEditExtraCostCoaHint(tr);
        recalculateEditSummary();
    });

    // Payment Term change calculates Due Date in Edit Modal
    $('#edit_payment_term_id, #edit_tanggal_faktur').on('change', function () {
        let tglFaktur = $('#edit_tanggal_faktur').val();
        let termDays = parseInt($('#edit_payment_term_id option:selected').data('hari')) || 0;
        if (tglFaktur) {
            let d = new Date(tglFaktur);
            d.setDate(d.getDate() + termDays);
            let yyyy = d.getFullYear();
            let mm = String(d.getMonth() + 1).padStart(2, '0');
            let dd = String(d.getDate()).padStart(2, '0');
            $('#edit_tanggal_jatuh_tempo').val(yyyy + '-' + mm + '-' + dd);
        }
    });

    $(document).on('input change', '#modalEditInvoice .input-nominal-item, #modalEditInvoice .input-nominal-extra, #edit_pajak_id, #modalEditInvoice .select-hpp-extra', function () {
        recalculateEditSummary();
    });

    $(document).on('click', '#modalEditInvoice .btn-remove-extra-cost', function () {
        $(this).closest('tr').remove();
        if ($('#edit_extra_costs_tbody tr').length === 0) {
            $('#edit_extra_costs_tbody').html('<tr id="edit_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');
        }
        recalculateEditSummary();
    });

    function recalculateEditSummary() {
        let subtotalBarangGross = 0;
        let totalDiskon = 0;

        $('#edit_items_tbody tr.row-item-barang').each(function () {
            let price = parseFloat($(this).find('.input-nominal-item').val()) || 0;
            let qty = parseFloat($(this).find('.input-nominal-item').data('qty')) || 0;
            let diskonItem = parseFloat($(this).find('.input-nominal-item').data('diskon-item')) || 0;

            let lineGross = price * qty;
            let lineDiskon = diskonItem * qty;
            let lineNet = Math.max(0, lineGross - lineDiskon);

            $(this).find('.val-item-diskon').text(lineDiskon > 0 ? '- ' + formatMoney(lineDiskon, editCurrencyCode, editCurrencySymbol) : formatNumberOnly(0, editCurrencyCode));
            $(this).find('.val-item-subtotal').text(formatMoney(lineNet, editCurrencyCode, editCurrencySymbol));

            subtotalBarangGross += lineGross;
            totalDiskon += lineDiskon;
        });

        let dpp = Math.max(0, subtotalBarangGross - totalDiskon);

        let biayaHppTotal = 0;
        let biayaNonHppTotal = 0;
        $('#edit_extra_costs_tbody tr.row-extra-cost').each(function () {
            let nom = parseFloat($(this).find('.input-nominal-extra').val()) || 0;
            let isHpp = $(this).find('.select-hpp-extra').val() === '1';
            if (isHpp) {
                biayaHppTotal += nom;
            } else {
                biayaNonHppTotal += nom;
            }
        });

        let totalBiayaLain = biayaHppTotal + biayaNonHppTotal;

        let selectedPajakOpt = $('#edit_pajak_id option:selected');
        let tarif = selectedPajakOpt.data('tarif') !== undefined ? parseFloat(selectedPajakOpt.data('tarif')) : 0;
        if ($('#edit_pajak_id').val() === '') tarif = 0;
        let ppnNominal = dpp * (tarif / 100);

        $('#edit_label_ppn').text('PPN Masukan (' + tarif + '%)');

        let totalInvoice = dpp + totalBiayaLain + ppnNominal;

        $('#edit_sum_subtotal').text(formatNumberOnly(subtotalBarangGross, editCurrencyCode));
        $('#edit_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, editCurrencyCode));
        $('#edit_sum_dpp').text(formatNumberOnly(dpp, editCurrencyCode));
        $('#edit_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, editCurrencyCode));
        $('#edit_sum_ppn').text(formatNumberOnly(ppnNominal, editCurrencyCode));
        $('#edit_sum_total_invoice').text(formatMoney(totalInvoice, editCurrencyCode, editCurrencySymbol));

        if (editCurrencyCode !== 'IDR' && editKurs > 1) {
            let totalIdr = totalInvoice * editKurs;
            $('#edit_sum_total_invoice_idr').html('<i class="bi bi-arrow-repeat me-1"></i>Ekuivalen: <strong>Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totalIdr) + '</strong> (Kurs: ' + formatSmartKurs(editKurs) + ')').show();
        } else {
            $('#edit_sum_total_invoice_idr').hide().empty();
        }
    }

    // Submit Edit
    $('#formEditInvoice').on('submit', function (e) {
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
                $('#modalEditInvoice').modal('hide');
                if (typeof window.reloadInvoiceTable === 'function') {
                    window.reloadInvoiceTable();
                }
                Swal.fire('Berhasil!', res.message, 'success');
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memperbarui faktur.';
                Swal.fire('Gagal!', msg, 'error');
            }
        });
    });

    // Save & Post Direct from Edit Modal
    $('#edit_btn_submit_post_invoice').on('click', function () {
        let invId = $('#edit_invoice_id').val();
        if (!invId) return;

        Swal.fire({
            title: 'Simpan Perubahan & Posting ke Akuntansi?',
            text: 'Faktur akan diperbarui dan langsung diterbitkan Jurnal Utang Usaha.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan & Posting',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData($('#formEditInvoice')[0]);
                $.ajax({
                    url: '/purchase-invoices/' + invId + '/update',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (res) {
                        // Then trigger post
                        $.post('/purchase-invoices/' + invId + '/post', { _token: '{{ csrf_token() }}' }, function (postRes) {
                            $('#modalEditInvoice').modal('hide');
                            if (typeof window.reloadInvoiceTable === 'function') {
                                window.reloadInvoiceTable();
                            }
                            Swal.fire('Berhasil!', 'Faktur berhasil diperbarui & diposting ke Akuntansi!', 'success');
                        });
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menyimpan perubahan.', 'error');
                    }
                });
            }
        });
    });
});
</script>
