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

    // Initialize PPh Withholding Tax Panel for Edit Modal
    if (typeof window.initPphPanel === 'function') {
        window.initPphPanel('edit', $('#modalEditInvoice'), function() {
            recalculateEditSummary();
        });
    }

    function buildPajakOptions(selectedId, selectedTarif) {
        let selTarifNum = selectedTarif !== undefined && selectedTarif !== null ? parseFloat(selectedTarif) : null;
        let html = '<option value="" data-tarif="0"' + ((!selectedId && (selTarifNum === null || selTarifNum === 0)) ? ' selected' : '') + '>Non PPN (0%)</option>';
        let isSel = false;
        @foreach($pajaks as $pj)
        isSel = (selectedId && selectedId == {{ $pj->id }}) || (!selectedId && selTarifNum !== null && selTarifNum === {{ (float)$pj->tarif }});
        html += '<option value="{{ $pj->id }}" data-tarif="{{ (float)$pj->tarif }}"' + (isSel ? ' selected' : '') + '>{{ $pj->nama_pajak }} ({{ rtrim(rtrim(number_format($pj->tarif, 2, ',', '.'), '0'), ',') }}%)</option>';
        @endforeach
        return html;
    }

    function detectCoaForExtraCost(jenis, isHpp) {
        let j = (jenis || '').toLowerCase().trim();
        if (j && window.expenseTypeCoaMap) {
            if (window.expenseTypeCoaMap[j]) {
                return window.expenseTypeCoaMap[j];
            }
            for (let key in window.expenseTypeCoaMap) {
                if (key && (j.includes(key) || key.includes(j))) {
                    return window.expenseTypeCoaMap[key];
                }
            }
        }
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

        if (currentCoa && currentCoa == suggestedCoa) {
            updateCoaOriginHint(hint, true, 'Otomatis dari Master Biaya / Pemetaan', 'info');
        } else if (currentCoa) {
            updateCoaOriginHint(hint, false, 'Dipilih Manual', 'manual');
        } else {
            hint.empty();
        }
    }

    function updateEditCurrencyDisplay(code, symbol, kurs) {
        editCurrencyCode = (code || 'IDR').toUpperCase();
        editCurrencySymbol = symbol || (['IDR', 'RP'].includes(editCurrencyCode) ? 'Rp' : '$');

        let isRupiah = ['IDR', 'RP'].includes(editCurrencyCode);
        if (isRupiah) {
            editKurs = 1.0;
            $('#edit_input_kurs').val('1').prop('readonly', true).addClass('bg-body-tertiary');
        } else {
            editKurs = parseFloat(kurs) || 1.0;
            $('#edit_input_kurs').val(formatNumber(editKurs)).prop('readonly', false).removeClass('bg-body-tertiary');
        }

        $('#edit_disp_currency').val(editCurrencyCode + (editCurrencyCode === 'USD' ? ' (US Dollar)' : (isRupiah ? ' (Rupiah)' : ' (' + editCurrencyCode + ')')));
        $('#edit_kurs_symbol_addon').text(editCurrencyCode);
        $('#edit_disp_currency_txt').text(editCurrencyCode + ' (Kurs: ' + formatSmartKurs(editKurs) + ')');
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
                let editNoFakturLen = (inv.nomor_faktur_supplier || '').length;
                $('#edit_no_faktur_sup_char_count').text(editNoFakturLen + '/100');
                if (editNoFakturLen >= 100) {
                    $('#edit_no_faktur_sup_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
                } else {
                    $('#edit_no_faktur_sup_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
                }

                $('#edit_tanggal_faktur').val(inv.tanggal_faktur ? String(inv.tanggal_faktur).substring(0, 10) : '');
                if (typeof window.initTerminSelect2 === 'function') {
                    window.initTerminSelect2($('#edit_payment_term_id'), $('#modalEditInvoice'));
                }
                if (typeof window.initUangMukaCoaSelect2 === 'function') {
                    window.initUangMukaCoaSelect2($('#edit_uang_muka_coa_id'), $('#modalEditInvoice'));
                }
                if (inv.payment_term_id) {
                    $('#edit_payment_term_id').val(inv.payment_term_id).trigger('change.select2');
                } else {
                    $('#edit_payment_term_id').val('').trigger('change.select2');
                }
                $('#edit_tanggal_jatuh_tempo').val(inv.tanggal_jatuh_tempo ? String(inv.tanggal_jatuh_tempo).substring(0, 10) : '');
                $('#edit_keterangan').val(inv.keterangan || '');
                let editKetLen = (inv.keterangan || '').length;
                $('#edit_keterangan_char_count').text(editKetLen + '/1000');
                if (editKetLen >= 1000) {
                    $('#edit_keterangan_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
                } else {
                    $('#edit_keterangan_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
                }

                let sup = inv.supplier || (inv.goods_receipt && inv.goods_receipt.po ? inv.goods_receipt.po.supplier : {});
                let po = inv.po || (inv.goods_receipt ? inv.goods_receipt.po : {});
                let gdg = (inv.goods_receipt && inv.goods_receipt.gudang) ? inv.goods_receipt.gudang.nama_gudang : '-';
                let grnNum = inv.goods_receipt ? inv.goods_receipt.nomor_gr : '-';

                if (inv.goods_receipt) {
                    $('#edit_disp_gr_ref').val(grnNum + ' - ' + (sup.nama_supplier || '-') + ' (PO: ' + (po ? po.nomor_po || '-' : '-') + ')');
                    $('#edit_disp_gudang').val(gdg);
                    $('#edit_disp_gudang_txt').text(gdg);
                    $('#edit_sb_grn').text(grnNum);
                } else {
                    $('#edit_disp_gr_ref').val('— (Langsung dari PO ' + (po ? po.nomor_po || '-' : '-') + ')');
                    $('#edit_disp_gudang').val('—');
                    $('#edit_disp_gudang_txt').text('— (Langsung dari PO)');
                    $('#edit_sb_grn').text('—');
                }

                $('#edit_disp_supplier').val(sup.nama_supplier || '-');
                $('#edit_disp_supplier_txt').text(sup.nama_supplier || '-');
                $('#edit_sb_supplier').text(sup.nama_supplier || '-');
                $('#edit_disp_po_nomor').val(po ? po.nomor_po || '-' : '-');
                $('#edit_disp_po_nomor_txt').text(po ? po.nomor_po || '-' : '-');
                $('#edit_sb_po').text(po ? po.nomor_po || '-' : '-');
                if (sup.npwp && String(sup.npwp).trim() !== '' && String(sup.npwp).trim() !== '-') {
                    $('#edit_disp_npwp_txt').html('<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-monospace px-2 py-1"><i class="bi bi-card-text me-1"></i>' + escapeHtml(sup.npwp) + '</span>');
                } else {
                    $('#edit_disp_npwp_txt').html('<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle font-monospace px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Tidak Ada NPWP (Non-PKP)</span>');
                }

                // Resolve Tender Info
                let tenderObj = inv.tender || (inv.goods_receipt && inv.goods_receipt.tender ? inv.goods_receipt.tender : ((po && po.tender) ? po.tender : (inv.goods_receipt && inv.goods_receipt.po ? inv.goods_receipt.po.tender : null)));
                let tenderId = inv.tender_id || (tenderObj ? tenderObj.id : (inv.goods_receipt ? inv.goods_receipt.tender_id : (po ? po.tender_id : '')));
                let tenderNomor = tenderObj ? (tenderObj.nomor_tender || tenderObj.nomor_kontrak || ('Tender #' + tenderObj.id)) : '';
                if (tenderObj && tenderObj.nama_tender && !tenderNomor.includes(tenderObj.nama_tender)) {
                    tenderNomor += ' - ' + tenderObj.nama_tender;
                }

                if (tenderNomor || tenderId) {
                    $('#edit_tender_id').val(tenderId || '');
                    $('#edit_disp_tender_txt').text(tenderNomor || ('Tender #' + tenderId));
                    $('#edit_box_tender').show();
                } else {
                    $('#edit_tender_id').val('');
                    $('#edit_disp_tender_txt').text('—');
                    $('#edit_box_tender').hide();
                }

                let curCode = (inv.currency && inv.currency.kode_currency) 
                    ? inv.currency.kode_currency 
                    : ((po && po.currency && po.currency.kode_currency) ? po.currency.kode_currency : 'IDR');
                let curSymbol = (inv.currency && inv.currency.simbol) 
                    ? inv.currency.simbol 
                    : ((po && po.currency && po.currency.simbol) ? po.currency.simbol : (curCode === 'USD' ? '$' : 'Rp'));
                let kursVal = (inv.kurs && parseFloat(inv.kurs) > 0)
                    ? parseFloat(inv.kurs)
                    : ((inv.goods_receipt && inv.goods_receipt.kurs && parseFloat(inv.goods_receipt.kurs) > 0)
                        ? parseFloat(inv.goods_receipt.kurs)
                        : ((po && po.kurs) ? parseFloat(po.kurs) : 1.0));

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
                            let poDetail = d.gr_detail ? (d.gr_detail.po_detail || d.gr_detail.poDetail || {}) : (d.po_detail || d.poDetail || {});
                            let diskonItem = parseFloat(d.diskon_item !== undefined && d.diskon_item !== null ? d.diskon_item : (poDetail.diskon_item || 0));
                            let lineSubtotalGross = qty * hargaSatuan;
                            let lineDiskonTotal = qty * diskonItem;
                            let lineSubtotalNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);

                            let itemPpnId = d.ppn_pajak_id || (poDetail.ppn_pajak_id || '');
                            let itemTarifPpn = d.tarif_ppn !== undefined && d.tarif_ppn !== null
                                ? parseFloat(d.tarif_ppn)
                                : (poDetail.tarif_ppn !== undefined && poDetail.tarif_ppn !== null ? parseFloat(poDetail.tarif_ppn) : 0);
                            let linePpn = Math.round(lineSubtotalNet * (itemTarifPpn / 100) * 100) / 100;

                            let receiptInfoHtml = '';
                            if (inv.goods_receipt_id || d.gr_detail_id) {
                                receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size:0.68rem; line-height:1.2;">' +
                                    '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-1.5 py-0.5 rounded" title="Kuantitas dari Penerimaan Barang (GR)">' +
                                        '<i class="bi bi-box-seam me-1"></i>Diterima (GR)' +
                                    '</span>' +
                                '</div>';
                            } else if (inv.po_id || d.po_detail_id) {
                                let grDetails = poDetail.gr_details || poDetail.grDetails || [];
                                let qtyReceived = 0;
                                if (Array.isArray(grDetails)) {
                                    grDetails.forEach(function(grd) {
                                        let grObj = grd.goods_receipt || grd.goodsReceipt;
                                        if (!grObj || grObj.status !== 'Dibatalkan') {
                                            qtyReceived += parseFloat(grd.jumlah || 0);
                                        }
                                    });
                                }
                                let qtyOrdered = parseFloat(poDetail.jumlah != null ? poDetail.jumlah : qty);
                                let qtyRemaining = Math.max(0, qtyOrdered - qtyReceived);

                                if (qtyReceived > 0) {
                                    let isFull = qtyReceived >= qtyOrdered;
                                    receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size: 0.68rem; line-height: 1.2;">' +
                                        '<span class="badge ' + (isFull ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-info bg-opacity-10 text-info border border-info-subtle') + ' px-1.5 py-0.5 rounded" title="Sudah diterima di gudang">' +
                                            '<i class="bi bi-box-seam me-1"></i>Diterima: ' + qtyReceived + ' ' + escapeHtml(satuan) +
                                        '</span>' +
                                        (qtyRemaining > 0 ? '<div class="text-warning-emphasis fw-medium extra-small mt-0.5" style="font-size:0.65rem;"><i class="bi bi-hourglass-split me-0.5"></i>Sisa: ' + qtyRemaining + ' ' + escapeHtml(satuan) + '</div>' : '') +
                                    '</div>';
                                } else {
                                    receiptInfoHtml = '<div class="mt-1 font-monospace" style="font-size: 0.68rem; line-height: 1.2;">' +
                                        '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-1.5 py-0.5 rounded" title="Belum ada penerimaan barang">' +
                                            '<i class="bi bi-clock-history me-1"></i>Belum diterima' +
                                        '</span>' +
                                    '</div>';
                                }
                            }

                            let detailKey = d.gr_detail_id || d.po_detail_id || d.id;
                            let hiddenDetailInput = d.gr_detail_id
                                ? '<input type="hidden" name="items[' + detailKey + '][gr_detail_id]" value="' + d.gr_detail_id + '">'
                                : '<input type="hidden" name="items[' + detailKey + '][po_detail_id]" value="' + (d.po_detail_id || d.id) + '">';

                            let maxQty = (d.gr_detail && d.gr_detail.jumlah)
                                ? parseFloat(d.gr_detail.jumlah)
                                : (poDetail && poDetail.jumlah ? parseFloat(poDetail.jumlah) : qty);

                            tbodyHtml += '<tr class="row-item-barang" data-row="' + totalItemTypes + '">' +
                                '<td class="text-center"><span class="inv-row-num">' + totalItemTypes + '</span></td>' +
                                '<td><span class="inv-badge-code">' + escapeHtml(barangKode) + '</span></td>' +
                                '<td><div class="inv-item-name">' + escapeHtml(barangNama) + '</div>' + hiddenDetailInput + '</td>' +
                                '<td>' +
                                    '<select class="form-select form-select-sm select-item-coa" name="items[' + detailKey + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                    '<div class="hint-coa-item d-flex align-items-center gap-1 mt-1 text-primary-emphasis opacity-75" style="font-size:0.68rem; line-height:1.2;">' +
                                        '<i class="bi bi-magic text-primary"></i><span class="badge-coa-origin fw-medium">Otomatis dari Pemetaan</span>' +
                                    '</div>' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<div class="input-group input-group-sm justify-content-center" style="max-width: 105px; margin: 0 auto;">' +
                                        '<input type="number" step="any" min="0.01" max="' + maxQty + '" class="form-control form-control-sm text-center fw-bold font-monospace input-item-qty" name="items[' + detailKey + '][qty]" value="' + qty + '" data-max-qty="' + maxQty + '" data-item-id="' + detailKey + '">' +
                                    '</div>' +
                                    receiptInfoHtml +
                                    '<div class="qty-warning text-danger extra-small mt-0.5 fw-medium" style="display:none; font-size:0.65rem;"></div>' +
                                '</td>' +
                                '<td class="text-center"><span class="inv-badge-unit">' + escapeHtml(satuan) + '</span></td>' +
                                '<td>' +
                                    '<div class="input-group input-group-sm inv-price-group">' +
                                        '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(editCurrencySymbol) + '</span>' +
                                        '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-item" name="items[' + detailKey + '][harga_satuan]" value="' + formatNumber(hargaSatuan) + '" data-qty="' + qty + '" data-diskon-item="' + diskonItem + '" autocomplete="off">' +
                                    '</div>' +
                                    '<div class="val-item-price-idr extra-small text-muted font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                                '</td>' +
                                '<td class="text-end">' +
                                    (lineDiskonTotal > 0
                                        ? '<span class="inv-badge-discount val-item-diskon">- ' + formatMoney(lineDiskonTotal, editCurrencyCode, editCurrencySymbol) + '</span>'
                                        : '<span class="val-item-diskon text-muted opacity-50 font-monospace small">-</span>') +
                                    '<div class="val-item-diskon-idr extra-small text-danger font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                                '</td>' +
                                '<td>' +
                                    '<select class="form-select form-select-sm select-item-ppn font-monospace fw-semibold" name="items[' + detailKey + '][ppn_pajak_id]">' +
                                        buildPajakOptions(itemPpnId, itemTarifPpn) +
                                    '</select>' +
                                    '<input type="hidden" name="items[' + detailKey + '][tarif_ppn]" class="input-tarif-ppn" value="' + itemTarifPpn + '">' +
                                    '<input type="hidden" name="items[' + detailKey + '][ppn_nominal]" class="input-ppn-nominal" value="' + linePpn + '">' +
                                    '<div class="text-end mt-1">' +
                                        '<span class="inv-badge-ppn-preview item-ppn-nominal-preview font-monospace">' + (linePpn > 0 ? '+ ' + formatMoney(linePpn, editCurrencyCode, editCurrencySymbol) : '+ 0') + '</span>' +
                                        '<div class="item-ppn-idr-preview extra-small text-muted font-monospace" style="display:none; font-size:0.68rem;"></div>' +
                                    '</div>' +
                                '</td>' +
                                '<td class="text-end">' +
                                    '<span class="inv-val-subtotal val-item-subtotal">' + formatMoney(lineSubtotalNet, editCurrencyCode, editCurrencySymbol) + '</span>' +
                                    '<div class="val-item-subtotal-idr small text-muted font-monospace mt-1" style="display:none; font-size:0.72rem;"></div>' +
                                '</td>' +
                                '<td class="text-end col-item-alokasi-biaya">' +
                                    '<span class="val-item-alokasi-biaya font-monospace text-primary fw-semibold small">-</span>' +
                                    '<div class="val-item-alokasi-biaya-unit text-muted font-monospace opacity-75 mt-0.5" style="font-size: 0.68rem;"></div>' +
                                '</td>' +
                                '<td class="text-end col-item-hpp-akhir bg-success bg-opacity-10">' +
                                    '<span class="val-item-hpp-akhir inv-badge-hpp" style="background: rgba(148, 163, 184, 0.12) !important; color: inherit !important; border-color: rgba(148, 163, 184, 0.25) !important;">-</span>' +
                                    '<div class="val-item-hpp-akhir-total text-muted font-monospace opacity-75 mt-0.5" style="font-size: 0.68rem;"></div>' +
                                '</td>' +
                            '</tr>';
                        } else if (d.jenis !== 'PPN') {
                            editExtraCostCount++;
                            let isHpp = d.masuk_hpp ? 1 : 0;
                            let jenisVal = d.jenis || d.keterangan || 'Biaya Tambahan';

                            let partialHintHtml = '';
                            if (res.tracking && res.tracking.is_partial) {
                                partialHintHtml = '<div class="extra-cost-partial-hint small text-primary font-monospace mt-1" style="font-size:0.68rem; line-height: 1.2;">' +
                                    '<i class="bi bi-pie-chart me-1"></i>Penerimaan Parsial (' + res.tracking.gr_ratio_pct + '% dari PO)' +
                                '</div>';
                            }

                            extraHtml += '<tr class="row-extra-cost" data-row="' + editExtraCostCount + '">' +
                                '<td>' +
                                    '<input type="text" class="form-control form-control-sm fw-semibold input-extra-jenis" name="extra_costs[' + editExtraCostCount + '][jenis]" value="' + escapeHtml(jenisVal) + '" autocomplete="off">' +
                                '</td>' +
                                '<td>' +
                                    '<div class="input-group input-group-sm inv-price-group">' +
                                        '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(editCurrencySymbol) + '</span>' +
                                        '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-extra" name="extra_costs[' + editExtraCostCount + '][nominal]" value="' + formatNumber(d.harga_satuan || d.subtotal || 0) + '" autocomplete="off">' +
                                    '</div>' +
                                    partialHintHtml +
                                    '<div class="val-extra-cost-idr extra-small text-muted font-monospace text-end mt-1" style="display:none; font-size:0.7rem;"></div>' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<select class="form-select form-select-sm select-hpp-extra fw-semibold" name="extra_costs[' + editExtraCostCount + '][masuk_hpp]">' +
                                        '<option value="0"' + (isHpp === 0 ? ' selected' : '') + '>Tidak</option>' +
                                        '<option value="1"' + (isHpp === 1 ? ' selected' : '') + '>Ya (HPP)</option>' +
                                    '</select>' +
                                '</td>' +
                                '<td class="cell-coa-extra">' +
                                    '<select class="form-select form-select-sm select-coa-extra" name="extra_costs[' + editExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                    '<div class="hint-coa-extra"></div>' +
                                '</td>' +
                                '<td><input type="text" class="form-control form-control-sm" name="extra_costs[' + editExtraCostCount + '][keterangan]" value="' + escapeHtml(d.keterangan || '') + '" autocomplete="off"></td>' +
                                '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost shadow-sm" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                            '</tr>';
                        }
                    });
                }

                $('#edit_sb_total_barang').text(totalItemTypes);
                $('#edit_sb_total_qty').text(totalQty);
                $('#edit_items_tbody').html(tbodyHtml);
                $('#edit_extra_costs_tbody').html(extraHtml || '<tr id="edit_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');

                // Initialize Searchable Select2 for PPN dropdowns
                if (typeof window.initPajakSelect2 === 'function') {
                    window.initPajakSelect2($('#edit_items_tbody .select-item-ppn'), $('#modalEditInvoice'));
                }

                // Set saved COAs
                if (inv.details && inv.details.length > 0) {
                    let extraIdx = 0;
                    inv.details.forEach(function (d) {
                        if (d.jenis === 'Barang') {
                            let itemCoa = d.coa_id || defaultGrniCoaId;
                            let row = $('#edit_items_tbody tr.row-item-barang').filter(function() {
                                if (d.gr_detail_id) {
                                    return $(this).find('input[name$="[gr_detail_id]"]').val() == d.gr_detail_id;
                                } else if (d.po_detail_id) {
                                    return $(this).find('input[name$="[po_detail_id]"]').val() == d.po_detail_id;
                                }
                                return false;
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

                if (typeof window.initCoaSelect2 === 'function') {
                    window.initCoaSelect2($('#edit_items_tbody .select-item-coa, #edit_extra_costs_tbody .select-coa-extra'), $('#modalEditInvoice'));
                }

                // Populate PPh Withholding Tax state
                if (typeof window.setPphState === 'function') {
                    window.setPphState('edit', inv.pph_pajak_id, inv.tarif_pph, inv.pph_nominal, inv.pph_coa_id);
                }

                // Populate Uang Muka (DP), Kas / Bank, & COA
                let dpVal = (inv.uang_muka !== null && inv.uang_muka !== undefined) ? parseFloat(inv.uang_muka) : 0;
                let dpIdrVal = (inv.uang_muka_idr !== null && inv.uang_muka_idr !== undefined) ? parseFloat(inv.uang_muka_idr) : (dpVal * kursVal);
                window.editInitialKasBankId = inv.kas_bank_id || null;
                window.editInitialDpIdr = dpIdrVal;

                $('#edit_uang_muka').val(formatNumber(dpVal));
                if (dpVal > 0.005) {
                    $('#edit_kas_bank_required_star').show();
                } else {
                    $('#edit_kas_bank_required_star').hide();
                }

                if (res.tracking && res.tracking.is_partial && res.tracking.po_dp > 0) {
                    $('#edit_uang_muka_partial_hint').html(
                        '<i class="bi bi-pie-chart me-1"></i>Penerimaan Parsial (' + res.tracking.gr_ratio_pct + '% dari DP PO: ' + formatMoney(res.tracking.po_dp, editCurrencyCode, editCurrencySymbol) + ' | Sisa kuota: ' + formatMoney(res.tracking.remaining_dp, editCurrencyCode, editCurrencySymbol) + ')'
                    ).show();
                } else {
                    $('#edit_uang_muka_partial_hint').hide().empty();
                }

                if (inv.kas_bank_id) {
                    if ($('#edit_kas_bank_id').data('select2')) {
                        $('#edit_kas_bank_id').val(String(inv.kas_bank_id)).trigger('change.select2');
                    } else {
                        $('#edit_kas_bank_id').val(String(inv.kas_bank_id));
                    }
                } else {
                    if ($('#edit_kas_bank_id').data('select2')) {
                        $('#edit_kas_bank_id').val('').trigger('change.select2');
                    } else {
                        $('#edit_kas_bank_id').val('');
                    }
                }

                if (dpVal > 0 && inv.uang_muka_coa_id) {
                    if ($('#edit_uang_muka_coa_id').data('select2')) {
                        $('#edit_uang_muka_coa_id').val(String(inv.uang_muka_coa_id)).trigger('change.select2');
                    } else {
                        $('#edit_uang_muka_coa_id').val(String(inv.uang_muka_coa_id));
                    }
                } else {
                    if ($('#edit_uang_muka_coa_id').data('select2')) {
                        $('#edit_uang_muka_coa_id').val('').trigger('change.select2');
                    } else {
                        $('#edit_uang_muka_coa_id').val('');
                    }
                }

                // Populate Lampiran Faktur
                $('#edit_lampiran_faktur').val('');
                if (inv.lampiran_faktur || inv.lampiran_url) {
                    let fileUrl = inv.lampiran_url || ('/' + inv.lampiran_faktur.replace(/^\//, ''));
                    $('#edit_lampiran_existing_link').data('url', fileUrl).attr('data-url', fileUrl).data('title', inv.nomor_faktur || 'Faktur Pembelian');
                    $('#edit_lampiran_existing_container').show();
                } else {
                    $('#edit_lampiran_existing_container').hide();
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
            '<td><input type="text" class="form-control form-control-sm fw-semibold input-extra-jenis" name="extra_costs[' + editExtraCostCount + '][jenis]" placeholder="Jenis Biaya..." value="Ongkos Kirim" autocomplete="off"></td>' +
            '<td><div class="input-group input-group-sm inv-price-group"><span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(editCurrencySymbol) + '</span><input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-extra" name="extra_costs[' + editExtraCostCount + '][nominal]" value="0,00" autocomplete="off"></div><div class="val-extra-cost-idr extra-small text-muted font-monospace text-end mt-1" style="display:none; font-size:0.7rem;"></div></td>' +
            '<td class="text-center"><select class="form-select form-select-sm select-hpp-extra fw-semibold" name="extra_costs[' + editExtraCostCount + '][masuk_hpp]"><option value="0">Tidak</option><option value="1" selected>Ya (HPP)</option></select></td>' +
            '<td class="cell-coa-extra"><select class="form-select form-select-sm select-coa-extra" name="extra_costs[' + editExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select><div class="hint-coa-extra"></div></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="extra_costs[' + editExtraCostCount + '][keterangan]" placeholder="Keterangan..." autocomplete="off"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost shadow-sm" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

        $('#edit_extra_costs_tbody').append(newRow);
        let tr = $('#edit_extra_costs_tbody tr.row-extra-cost').last();
        if (defaultExtraCoa) tr.find('.select-coa-extra').val(defaultExtraCoa);
        refreshEditExtraCostCoaHint(tr);
        if (typeof window.initCoaSelect2 === 'function') {
            window.initCoaSelect2(tr.find('.select-coa-extra'), $('#modalEditInvoice'));
        }
        recalculateEditSummary();
    });

    $(document).on('change', '#modalEditInvoice .select-hpp-extra', function () {
        let tr = $(this).closest('tr');
        let isHpp = $(this).val();
        let jenisVal = tr.find('input[name$="[jenis]"]').val();
        let suggestedCoa = detectCoaForExtraCost(jenisVal, isHpp);
        if (suggestedCoa) {
            if (tr.find('.select-coa-extra').data('select2')) {
                tr.find('.select-coa-extra').val(suggestedCoa).trigger('change.select2');
            } else {
                tr.find('.select-coa-extra').val(suggestedCoa);
            }
        }
        refreshEditExtraCostCoaHint(tr);
        recalculateEditSummary();
    });

    $(document).on('input change', '#modalEditInvoice input[name$="[jenis]"]', function () {
        let tr = $(this).closest('tr.row-extra-cost');
        let isHpp = tr.find('.select-hpp-extra').val();
        let jenisVal = $(this).val();
        let suggestedCoa = detectCoaForExtraCost(jenisVal, isHpp);
        if (suggestedCoa) {
            if (tr.find('.select-coa-extra').data('select2')) {
                tr.find('.select-coa-extra').val(suggestedCoa).trigger('change.select2');
            } else {
                tr.find('.select-coa-extra').val(suggestedCoa);
            }
        }
        refreshEditExtraCostCoaHint(tr);
    });

    $(document).on('change', '#modalEditInvoice .select-coa-extra', function () {
        let tr = $(this).closest('tr.row-extra-cost');
        refreshEditExtraCostCoaHint(tr);
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

    $(document).on('input keyup change blur', '#modalEditInvoice .input-item-qty', function () {
        let $inp = $(this);
        let val = parseFloat($inp.val());
        let max = parseFloat($inp.data('max-qty'));
        let tr = $inp.closest('tr');
        let warn = tr.find('.qty-warning');

        if (isNaN(val) || val <= 0) {
            $inp.addClass('is-invalid');
            warn.html('<i class="bi bi-exclamation-circle me-1"></i>Min. 0.01').show();
        } else if (!isNaN(max) && val > max) {
            $inp.addClass('is-invalid');
            warn.html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Maksimal ' + max).show();
            $inp.val(max);
            setTimeout(function() {
                warn.fadeOut(1500);
                $inp.removeClass('is-invalid');
            }, 1800);
        } else {
            $inp.removeClass('is-invalid');
            warn.hide().empty();
        }

        let effectiveQty = parseFloat($inp.val()) || 0;
        tr.find('.input-nominal-item').data('qty', effectiveQty);
        recalculateEditSummary();
    });

    $(document).on('input change', '#modalEditInvoice .input-nominal-item, #modalEditInvoice .input-nominal-extra, #modalEditInvoice .select-item-ppn, #modalEditInvoice .select-hpp-extra', function () {
        recalculateEditSummary();
    });

    $(document).on('input change keyup paste', '#edit_input_kurs', function () {
        if (['IDR', 'RP'].includes(editCurrencyCode.toUpperCase())) {
            editKurs = 1.0;
            $(this).val('1');
            return;
        }
        let val = unformatNumber($(this).val());
        editKurs = val > 0 ? val : 1.0;
        $('#edit_disp_currency_txt').text(editCurrencyCode + ' (Kurs: ' + formatSmartKurs(editKurs) + ')');
        recalculateEditSummary();
    });

    $(document).on('blur', '#edit_input_kurs', function () {
        if (['IDR', 'RP'].includes(editCurrencyCode.toUpperCase())) {
            editKurs = 1.0;
            $(this).val('1');
            return;
        }
        let val = unformatNumber($(this).val());
        if (val <= 0) val = 1.0;
        editKurs = val;
        $(this).val(formatNumber(val));
        $('#edit_disp_currency_txt').text(editCurrencyCode + ' (Kurs: ' + formatSmartKurs(editKurs) + ')');
        recalculateEditSummary();
    });

    $(document).on('click', '#modalEditInvoice .btn-remove-extra-cost', function () {
        $(this).closest('tr').remove();
        if ($('#edit_extra_costs_tbody tr').length === 0) {
            $('#edit_extra_costs_tbody').html('<tr id="edit_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');
        }
        recalculateEditSummary();
    });


    $(document).on('change', '#edit_kas_bank_id', function () {
        validateEditKasBankDp();
    });

    $(document).on('input change keyup blur', '#edit_uang_muka', function () {
        let dp = unformatNumber($(this).val());
        validateEditKasBankDp();

        if (dp > 0.005) {
            if (!$('#edit_uang_muka_coa_id').val()) {
                let defCoa = '{{ $defaultUangMukaCoaId ?? '' }}';
                if (defCoa) {
                    if ($('#edit_uang_muka_coa_id').data('select2')) {
                        $('#edit_uang_muka_coa_id').val(String(defCoa)).trigger('change.select2');
                    } else {
                        $('#edit_uang_muka_coa_id').val(String(defCoa));
                    }
                }
            }
        } else {
            if ($('#edit_uang_muka_coa_id').data('select2')) {
                $('#edit_uang_muka_coa_id').val('').trigger('change.select2');
            } else {
                $('#edit_uang_muka_coa_id').val('');
            }
        }
        recalculateEditSummary();
    });

    function recalculateEditSummary() {
        let subtotalBarangGross = 0;
        let totalDiskon = 0;
        let totalPpn = 0;
        let showIdrEquiv = (editCurrencyCode !== 'IDR' || Math.abs(editKurs - 1.0) > 0.0001);

        let totalQtySum = 0;
        $('#edit_items_tbody tr.row-item-barang').each(function () {
            let price = unformatNumber($(this).find('.input-nominal-item').val());
            let qtyInput = $(this).find('.input-item-qty');
            let qty = qtyInput.length ? (parseFloat(qtyInput.val()) || 0) : (parseFloat($(this).find('.input-nominal-item').data('qty')) || 0);
            let diskonItem = parseFloat($(this).find('.input-nominal-item').data('diskon-item')) || 0;
            totalQtySum += qty;
            $(this).find('.input-nominal-item').data('qty', qty);

            let lineGross = Math.round(price * qty * 100) / 100;
            let lineDiskon = Math.round(diskonItem * qty * 100) / 100;
            let lineNet = Math.max(0, Math.round((lineGross - lineDiskon) * 100) / 100);

            let tarifPpn = parseFloat($(this).find('.select-item-ppn option:selected').data('tarif')) || 0;
            $(this).find('.input-tarif-ppn').val(tarifPpn);

            let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;
            $(this).find('.input-ppn-nominal').val(linePpn);

            $(this).find('.item-ppn-nominal-preview').text(linePpn > 0 ? '+ ' + formatMoney(linePpn, editCurrencyCode, editCurrencySymbol) : '+ 0');
            if (lineDiskon > 0) {
                $(this).find('.val-item-diskon').removeClass('text-muted opacity-50 small').addClass('inv-badge-discount').text('- ' + formatMoney(lineDiskon, editCurrencyCode, editCurrencySymbol));
            } else {
                $(this).find('.val-item-diskon').removeClass('inv-badge-discount').addClass('text-muted opacity-50 small').text('-');
            }
            $(this).find('.val-item-subtotal').text(formatMoney(lineNet, editCurrencyCode, editCurrencySymbol));

            // Real-time IDR equivalents per item
            if (showIdrEquiv) {
                let priceIdr = Math.round(price * editKurs * 100) / 100;
                let diskonIdr = Math.round(lineDiskon * editKurs * 100) / 100;
                let ppnIdr = Math.round(linePpn * editKurs * 100) / 100;
                let subtotalIdr = Math.round(lineNet * editKurs * 100) / 100;

                $(this).find('.val-item-price-idr').text('≈ Rp ' + formatNumber(priceIdr) + ' / unit').show();
                if (lineDiskon > 0) {
                    $(this).find('.val-item-diskon-idr').text('≈ -Rp ' + formatNumber(diskonIdr)).show();
                } else {
                    $(this).find('.val-item-diskon-idr').hide().empty();
                }
                if (linePpn > 0) {
                    $(this).find('.item-ppn-idr-preview').text('≈ +Rp ' + formatNumber(ppnIdr)).show();
                } else {
                    $(this).find('.item-ppn-idr-preview').hide().empty();
                }
                $(this).find('.val-item-subtotal-idr').html('≈ <strong>Rp ' + formatNumber(subtotalIdr) + '</strong>').show();
            } else {
                $(this).find('.val-item-price-idr, .val-item-diskon-idr, .item-ppn-idr-preview, .val-item-subtotal-idr').hide().empty();
            }

            subtotalBarangGross += lineGross;
            totalDiskon += lineDiskon;
            totalPpn += linePpn;
        });

        subtotalBarangGross = Math.round(subtotalBarangGross * 100) / 100;
        totalDiskon = Math.round(totalDiskon * 100) / 100;
        let dpp = Math.max(0, Math.round((subtotalBarangGross - totalDiskon) * 100) / 100);

        let biayaHppTotal = 0;
        let biayaNonHppTotal = 0;
        $('#edit_extra_costs_tbody tr.row-extra-cost').each(function () {
            let nom = Math.round(unformatNumber($(this).find('.input-nominal-extra').val()) * 100) / 100;
            let isHpp = $(this).find('.select-hpp-extra').val() === '1';
            if (isHpp) {
                biayaHppTotal += nom;
            } else {
                biayaNonHppTotal += nom;
            }

            // Real-time IDR for extra costs
            if (showIdrEquiv && nom > 0) {
                let nomIdr = Math.round(nom * editKurs * 100) / 100;
                $(this).find('.val-extra-cost-idr').text('≈ Rp ' + formatNumber(nomIdr)).show();
            } else {
                $(this).find('.val-extra-cost-idr').hide().empty();
            }
        });

        biayaHppTotal = Math.round(biayaHppTotal * 100) / 100;
        biayaNonHppTotal = Math.round(biayaNonHppTotal * 100) / 100;
        let totalBiayaLain = Math.round((biayaHppTotal + biayaNonHppTotal) * 100) / 100;
        totalPpn = Math.round(totalPpn * 100) / 100;

        // Update live alokasi biaya pengiriman/HPP & Landing Cost (HPP Akhir per unit)
        $('#edit_items_tbody tr.row-item-barang').each(function () {
            let price = unformatNumber($(this).find('.input-nominal-item').val());
            let qtyInput = $(this).find('.input-item-qty');
            let qty = qtyInput.length ? (parseFloat(qtyInput.val()) || 0) : (parseFloat($(this).find('.input-nominal-item').data('qty')) || 0);
            let diskonItem = parseFloat($(this).find('.input-nominal-item').data('diskon-item')) || 0;
            let lineGross = Math.round(price * qty * 100) / 100;
            let lineDiskon = Math.round(diskonItem * qty * 100) / 100;
            let lineNet = Math.max(0, Math.round((lineGross - lineDiskon) * 100) / 100);

            let alokasiBiayaLine = 0;
            if (dpp > 0 && biayaHppTotal > 0) {
                alokasiBiayaLine = Math.round(((lineNet / dpp) * biayaHppTotal) * 100) / 100;
            }

            let alokasiPerUnit = (qty > 0) ? (alokasiBiayaLine / qty) : 0;
            let hppAkhirPerUnit = (qty > 0) ? ((lineNet + alokasiBiayaLine) / qty) : 0;
            let satuanText = $(this).find('.inv-badge-unit').text().trim() || 'unit';

            if (biayaHppTotal > 0) {
                $(this).find('.val-item-alokasi-biaya').html('<span class="inv-badge-alokasi">+ ' + formatMoney(alokasiBiayaLine, editCurrencyCode, editCurrencySymbol) + '</span>');
                $(this).find('.val-item-alokasi-biaya-unit').text('≈ +' + formatMoney(alokasiPerUnit, editCurrencyCode, editCurrencySymbol) + ' / ' + satuanText).show();
            } else {
                $(this).find('.val-item-alokasi-biaya').html('<span class="text-muted opacity-50">-</span>');
                $(this).find('.val-item-alokasi-biaya-unit').hide().empty();
            }

            if (biayaHppTotal > 0) {
                $(this).find('.val-item-hpp-akhir').html('<span class="inv-badge-hpp">' + formatMoney(hppAkhirPerUnit, editCurrencyCode, editCurrencySymbol) + '</span>');
                $(this).find('.val-item-hpp-akhir-total').html('Total: <strong>' + formatMoney(lineNet + alokasiBiayaLine, editCurrencyCode, editCurrencySymbol) + '</strong>').show();
            } else {
                $(this).find('.val-item-hpp-akhir').html('<span class="inv-badge-hpp" style="background: rgba(148, 163, 184, 0.12) !important; color: inherit !important; border-color: rgba(148, 163, 184, 0.25) !important;">' + formatMoney(hppAkhirPerUnit, editCurrencyCode, editCurrencySymbol) + '</span>');
                $(this).find('.val-item-hpp-akhir-total').hide().empty();
            }
        });

        let pphNominal = (typeof window.computePph === 'function')
            ? window.computePph('edit', dpp, editCurrencyCode)
            : 0;
        pphNominal = Math.round(pphNominal * 100) / 100;

        let totalInvoice = Math.max(0, Math.round((dpp + totalBiayaLain + totalPpn - pphNominal) * 100) / 100);
        window.currentEditTotalInvoice = totalInvoice;

        let uangMukaVal = unformatNumber($('#edit_uang_muka').val());
        if (totalInvoice > 0 && uangMukaVal > totalInvoice + 0.005) {
            $('#edit_uang_muka').addClass('is-invalid');
            $('#edit_uang_muka_error_msg').html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Nominal Uang Muka tidak boleh melebihi Total Tagihan (' + formatMoney(totalInvoice, editCurrencyCode, editCurrencySymbol) + ')!').show();
            $('#btn_simpan_draft_edit, #btn_ajukan_edit').prop('disabled', true);
        } else {
            $('#edit_uang_muka').removeClass('is-invalid');
            $('#edit_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_edit, #btn_ajukan_edit').prop('disabled', false);
        }

        let effectiveDp = Math.min(uangMukaVal, totalInvoice);
        let sisaHutang = Math.max(0, Math.round((totalInvoice - effectiveDp) * 100) / 100);

        $('#edit_label_ppn').text('Total PPN Masukan (Item)');
        $('#edit_sum_subtotal').text(formatNumberOnly(subtotalBarangGross, editCurrencyCode));
        $('#edit_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, editCurrencyCode));
        $('#edit_sum_dpp').text(formatNumberOnly(dpp, editCurrencyCode));
        $('#edit_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, editCurrencyCode));
        $('#edit_sum_ppn').text(formatNumberOnly(totalPpn, editCurrencyCode));
        $('#edit_sum_total_invoice').text(formatMoney(totalInvoice, editCurrencyCode, editCurrencySymbol));

        if (uangMukaVal > 0) {
            $('#edit_box_sisa_hutang').show();
            $('#edit_sum_sisa_hutang').text(formatMoney(sisaHutang, editCurrencyCode, editCurrencySymbol));
        } else {
            $('#edit_box_sisa_hutang').hide();
        }

        if (showIdrEquiv) {
            let subtotalIdr = Math.round(subtotalBarangGross * editKurs * 100) / 100;
            let diskonTotalIdr = Math.round(totalDiskon * editKurs * 100) / 100;
            let dppIdr = Math.round(dpp * editKurs * 100) / 100;
            let ppnTotalIdr = Math.round(totalPpn * editKurs * 100) / 100;
            let biayaLainIdr = Math.round(totalBiayaLain * editKurs * 100) / 100;
            let pphIdr = Math.round(pphNominal * editKurs * 100) / 100;
            let grandTotalIdr = Math.round(totalInvoice * editKurs * 100) / 100;

            $('#edit_sum_subtotal_idr').text('≈ Rp ' + formatNumber(subtotalIdr)).show();
            $('#edit_sum_diskon_idr').text(totalDiskon > 0 ? '≈ -Rp ' + formatNumber(diskonTotalIdr) : '').toggle(totalDiskon > 0);
            $('#edit_sum_dpp_idr').text('≈ Rp ' + formatNumber(dppIdr)).show();
            $('#edit_sum_ppn_idr').text('≈ Rp ' + formatNumber(ppnTotalIdr)).show();
            $('#edit_sum_biaya_lain_idr').text('≈ Rp ' + formatNumber(biayaLainIdr)).show();
            $('#edit_sum_pph_idr').text(pphNominal > 0 ? '≈ -Rp ' + formatNumber(pphIdr) : '').toggle(pphNominal > 0);

            $('#edit_box_total_idr').show();
            $('#edit_sum_total_invoice_idr').text('Rp ' + formatNumber(grandTotalIdr));
            $('#edit_sum_kurs_info').text('Kurs: ' + formatSmartKurs(editKurs));

            if (uangMukaVal > 0) {
                let sisaHutangIdr = Math.round(sisaHutang * editKurs * 100) / 100;
                $('#edit_box_sisa_hutang_idr').show();
                $('#edit_sum_sisa_hutang_idr').text('Rp ' + formatNumber(sisaHutangIdr));
            } else {
                $('#edit_box_sisa_hutang_idr').hide();
            }

            if (editCurrencyCode !== 'IDR') {
                $('#edit_label_total_tagihan').html('Grand Total (' + escapeHtml(editCurrencyCode) + ')');
            } else {
                $('#edit_label_total_tagihan').text('Grand Total Tagihan');
            }
        } else {
            $('#edit_sum_subtotal_idr, #edit_sum_diskon_idr, #edit_sum_dpp_idr, #edit_sum_ppn_idr, #edit_sum_biaya_lain_idr, #edit_sum_pph_idr').hide().empty();
            $('#edit_box_total_idr').hide();
            $('#edit_box_sisa_hutang_idr').hide();
            $('#edit_label_total_tagihan').text('Grand Total Tagihan');
        }
        $('#edit_sb_total_qty').text(totalQtySum % 1 === 0 ? totalQtySum : formatNumber(totalQtySum));
    }

    // Helper validasi wajib Kas / Bank jika Uang Muka (DP) > 0 & Cek Saldo Cukup (Edit Modal)
    function validateEditKasBankDp() {
        let dpVal = unformatNumber($('#edit_uang_muka').val());
        let $select2Container = $('#edit_kas_bank_id').next('.select2-container').find('.select2-selection');
        if (dpVal > 0.005) {
            $('#edit_kas_bank_required_star').show();
            if (!$('#edit_kas_bank_id').val()) {
                $('#edit_kas_bank_error_msg').show();
                $('#edit_kas_bank_saldo_error_msg').hide();
                $select2Container.addClass('border-danger bg-danger bg-opacity-10');
                return false;
            } else {
                $('#edit_kas_bank_error_msg').hide();

                // Cek Saldo Kas / Bank
                let $selectedOpt = $('#edit_kas_bank_id option:selected');
                let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
                let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
                let kurs = (typeof editKurs !== 'undefined' && editKurs > 0) ? editKurs : 1;
                let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

                // Tambahkan kredit DP lama jika rekening sama dengan sebelum edit
                let selectedKasBankId = $('#edit_kas_bank_id').val();
                let saldoTersedia = saldoKasBank;
                if (window.editInitialKasBankId && String(window.editInitialKasBankId) === String(selectedKasBankId)) {
                    saldoTersedia += (parseFloat(window.editInitialDpIdr) || 0);
                }

                if (saldoTersedia < dpNominalIdr) {
                    $('#edit_kas_bank_saldo_error_msg')
                        .html('<i class="bi bi-slash-circle-fill me-1"></i>Saldo rekening tidak cukup! Saldo tersedia: <strong>Rp ' + formatMoney(saldoTersedia, 'IDR', 'Rp') + '</strong>, DP dibutuhkan: <strong>Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>')
                        .show();
                    $select2Container.addClass('border-danger bg-danger bg-opacity-10');
                    return false;
                } else {
                    $('#edit_kas_bank_saldo_error_msg').hide();
                    $select2Container.removeClass('border-danger bg-danger bg-opacity-10');
                    return true;
                }
            }
        } else {
            $('#edit_kas_bank_required_star').hide();
            $('#edit_kas_bank_error_msg').hide();
            $('#edit_kas_bank_saldo_error_msg').hide();
            $select2Container.removeClass('border-danger bg-danger bg-opacity-10');
            return true;
        }
    }

    $(document).on('change', '#edit_kas_bank_id', function () {
        validateEditKasBankDp();
    });

    // Event listener for Uang Muka DP in Edit Modal: Live validate on input without breaking typing cursor
    $('#edit_uang_muka').on('input keyup paste', function () {
        let total = (typeof window.currentEditTotalInvoice !== 'undefined')
            ? window.currentEditTotalInvoice
            : unformatNumber($('#edit_sum_total_invoice').text());
        let val = unformatNumber($(this).val());

        validateEditKasBankDp();

        if (total > 0 && val > total + 0.005) {
            $(this).addClass('is-invalid');
            $('#edit_uang_muka_error_msg').html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Nominal Uang Muka tidak boleh melebihi Total Tagihan (' + formatMoney(total, editCurrencyCode, editCurrencySymbol) + ')!').show();
            $('#btn_simpan_draft_edit, #btn_ajukan_edit').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $('#edit_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_edit, #btn_ajukan_edit').prop('disabled', false);
        }
        recalculateEditSummary();
    });

    $('#edit_uang_muka').on('blur', function () {
        let total = (typeof window.currentEditTotalInvoice !== 'undefined')
            ? window.currentEditTotalInvoice
            : unformatNumber($('#edit_sum_total_invoice').text());
        let val = unformatNumber($(this).val());

        if (total > 0 && val > total + 0.005) {
            // When leaving input, gracefully clamp to maximum invoice total
            $(this).val(formatNumber(total));
            $(this).removeClass('is-invalid');
            $('#edit_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_edit, #btn_ajukan_edit').prop('disabled', false);
            recalculateEditSummary();
        }
    });

    // Event listener for Foto Bukti Lampiran File Input in Edit Modal
    $('#edit_lampiran_faktur').on('change', function () {
        let file = this.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Ukuran File Terlalu Besar', 'Maksimal ukuran file lampiran adalah 5 MB.', 'warning');
                $(this).val('');
                return;
            }
        }
    });

    // Live Character Counter Listeners in Edit Modal
    $(document).on('input keyup', '#edit_nomor_faktur_supplier', function () {
        let len = ($(this).val() || '').length;
        $('#edit_no_faktur_sup_char_count').text(len + '/100');
        if (len >= 100) {
            $('#edit_no_faktur_sup_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
        } else {
            $('#edit_no_faktur_sup_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    $(document).on('input keyup', '#edit_keterangan', function () {
        let len = ($(this).val() || '').length;
        $('#edit_keterangan_char_count').text(len + '/1000');
        if (len >= 1000) {
            $('#edit_keterangan_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
        } else {
            $('#edit_keterangan_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    // State to prevent duplicate submissions & manage button loading in edit modal
    let isEditingInvoice = false;

    function setEditButtonsLoading(isLoading, actionType) {
        isEditingInvoice = isLoading;
        let $btnDraft = $('#edit_btn_save_draft_invoice');
        let $btnSubmit = $('#edit_btn_submit_post_invoice');
        let $btnCancel = $('#modalEditInvoice [data-bs-dismiss="modal"]');

        if (isLoading) {
            $btnDraft.prop('disabled', true);
            $btnSubmit.prop('disabled', true);
            $btnCancel.prop('disabled', true);

            if (actionType === 'draft') {
                $btnDraft.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan Draft...');
            } else {
                $btnSubmit.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim & Menyimpan...');
            }
        } else {
            $btnDraft.prop('disabled', false).html('<i class="bi bi-file-earmark-code me-1"></i> Simpan Draft');
            $btnSubmit.prop('disabled', false).html('<i class="bi bi-send-check me-1"></i> Simpan & Ajukan');
            $btnCancel.prop('disabled', false);
        }
    }

    // Submit Edit
    $('#formEditInvoice').on('submit', function (e) {
        e.preventDefault();

        if (isEditingInvoice) return false;

        // Validasi Nomor Faktur Supplier (Wajib & Maks 100 Karakter)
        let noFakturSup = ($('#edit_nomor_faktur_supplier').val() || '').trim();
        if (!noFakturSup) {
            Swal.fire({
                title: 'Nomor Faktur Supplier Wajib Diisi',
                text: 'Silakan masukkan nomor faktur resmi yang tertera pada dokumen supplier.',
                icon: 'warning'
            });
            $('#edit_nomor_faktur_supplier').focus();
            return false;
        }
        if (noFakturSup.length > 100) {
            Swal.fire({
                title: 'Nomor Faktur Terlalu Panjang',
                text: 'Nomor faktur supplier maksimal 100 karakter (saat ini ' + noFakturSup.length + ' karakter).',
                icon: 'warning'
            });
            $('#edit_nomor_faktur_supplier').focus();
            return false;
        }

        // Validasi Catatan / Keterangan (Maks 1000 Karakter)
        let keteranganEdit = ($('#edit_keterangan').val() || '').trim();
        if (keteranganEdit.length > 1000) {
            Swal.fire({
                title: 'Keterangan Terlalu Panjang',
                text: 'Catatan / keterangan faktur maksimal 1000 karakter (saat ini ' + keteranganEdit.length + ' karakter).',
                icon: 'warning'
            });
            $('#edit_keterangan').focus();
            return false;
        }

        // Validasi Uang Muka tidak boleh melebihi Grand Total
        let dpVal = unformatNumber($('#edit_uang_muka').val());
        let currentTotal = (typeof window.currentEditTotalInvoice !== 'undefined')
            ? window.currentEditTotalInvoice
            : unformatNumber($('#edit_sum_total_invoice').text());
        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka (DP)',
                html: 'Nominal Uang Muka (DP) sebesar <b>' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b> tidak boleh melebihi Total Pembelian sebesar <b>' + formatMoney(currentTotal, editCurrencyCode, editCurrencySymbol) + '</b>!',
                icon: 'warning'
            });
            $('#edit_uang_muka').focus();
            return false;
        }

        // Validasi Kas / Bank Pengeluaran DP & Kecukupan Saldo jika Uang Muka > 0
        if (dpVal > 0.005) {
            if (!$('#edit_kas_bank_id').val()) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih!',
                    html: 'Faktur memiliki <b>Uang Muka (DP) sebesar ' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b>.<br><br>Anda <b>wajib</b> memilih Rekening Kas / Bank yang digunakan untuk pembayaran DP tersebut.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Pilih Kas / Bank Sekarang'
                }).then(() => {
                    $('#edit_kas_bank_id').select2('open');
                });
                return false;
            }

            let $selectedOpt = $('#edit_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof editKurs !== 'undefined' && editKurs > 0) ? editKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            let selectedKasBankId = $('#edit_kas_bank_id').val();
            let saldoTersedia = saldoKasBank;
            if (window.editInitialKasBankId && String(window.editInitialKasBankId) === String(selectedKasBankId)) {
                saldoTersedia += (parseFloat(window.editInitialDpIdr) || 0);
            }

            if (saldoTersedia < dpNominalIdr) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank tersedia: <strong class="text-danger">Rp ' + formatMoney(saldoTersedia, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (editCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        let actionUrl = $(this).attr('action');
        let formData = new FormData(this);
        let actionType = $('#edit_action_type').val() || 'draft';

        // Aktifkan loading spinner & disable tombol simpan untuk cegah duplikasi data
        setEditButtonsLoading(true, actionType);

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
                if (xhr.responseJSON?.errors) {
                    let errList = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    if (errList) msg = errList;
                }
                Swal.fire({
                    title: 'Gagal!',
                    html: msg,
                    icon: 'error'
                });
            },
            complete: function () {
                setEditButtonsLoading(false);
            }
        });
    });

    // Save Draft Button (Edit Modal)
    $('#edit_btn_save_draft_invoice').on('click', function () {
        if (isEditingInvoice) return false;

        let dpVal = unformatNumber($('#edit_uang_muka').val());
        let currentTotal = (typeof window.currentEditTotalInvoice !== 'undefined')
            ? window.currentEditTotalInvoice
            : unformatNumber($('#edit_sum_total_invoice').text());

        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka (DP)',
                html: 'Nominal Uang Muka (DP) sebesar <b>' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b> tidak boleh melebihi Total Pembelian sebesar <b>' + formatMoney(currentTotal, editCurrencyCode, editCurrencySymbol) + '</b>!',
                icon: 'warning'
            });
            $('#edit_uang_muka').focus();
            return false;
        }

        if (dpVal > 0.005) {
            if (!$('#edit_kas_bank_id').val()) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih!',
                    html: 'Faktur memiliki <b>Uang Muka (DP) sebesar ' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b>.<br><br>Anda <b>wajib</b> memilih Rekening Kas / Bank yang digunakan untuk pembayaran DP tersebut sebelum menyimpan.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Pilih Kas / Bank Sekarang'
                }).then(() => {
                    $('#edit_kas_bank_id').select2('open');
                });
                return false;
            }

            let $selectedOpt = $('#edit_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof editKurs !== 'undefined' && editKurs > 0) ? editKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            let selectedKasBankId = $('#edit_kas_bank_id').val();
            let saldoTersedia = saldoKasBank;
            if (window.editInitialKasBankId && String(window.editInitialKasBankId) === String(selectedKasBankId)) {
                saldoTersedia += (parseFloat(window.editInitialDpIdr) || 0);
            }

            if (saldoTersedia < dpNominalIdr) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank tersedia: <strong class="text-danger">Rp ' + formatMoney(saldoTersedia, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (editCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        $('#edit_action_type').val('draft');
        $('#formEditInvoice').submit();
    });

    // Save & Ajukan ke Atasan (Edit Modal)
    $('#edit_btn_submit_post_invoice').on('click', function () {
        if (isEditingInvoice) return false;

        let dpVal = unformatNumber($('#edit_uang_muka').val());
        let currentTotal = (typeof window.currentEditTotalInvoice !== 'undefined')
            ? window.currentEditTotalInvoice
            : unformatNumber($('#edit_sum_total_invoice').text());

        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka (DP)',
                html: 'Nominal Uang Muka (DP) sebesar <b>' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b> tidak boleh melebihi Total Pembelian sebesar <b>' + formatMoney(currentTotal, editCurrencyCode, editCurrencySymbol) + '</b>!',
                icon: 'warning'
            });
            $('#edit_uang_muka').focus();
            return false;
        }

        if (dpVal > 0.005) {
            if (!$('#edit_kas_bank_id').val()) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih!',
                    html: 'Faktur memiliki <b>Uang Muka (DP) sebesar ' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + '</b>.<br><br>Anda <b>wajib</b> memilih Rekening Kas / Bank yang digunakan untuk pembayaran DP tersebut sebelum mengajukan.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Pilih Kas / Bank Sekarang'
                }).then(() => {
                    $('#edit_kas_bank_id').select2('open');
                });
                return false;
            }

            let $selectedOpt = $('#edit_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof editKurs !== 'undefined' && editKurs > 0) ? editKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            let selectedKasBankId = $('#edit_kas_bank_id').val();
            let saldoTersedia = saldoKasBank;
            if (window.editInitialKasBankId && String(window.editInitialKasBankId) === String(selectedKasBankId)) {
                saldoTersedia += (parseFloat(window.editInitialDpIdr) || 0);
            }

            if (saldoTersedia < dpNominalIdr) {
                validateEditKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank tersedia: <strong class="text-danger">Rp ' + formatMoney(saldoTersedia, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (editCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, editCurrencyCode, editCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        $('#edit_action_type').val('submit');
        Swal.fire({
            title: 'Simpan Perubahan & Ajukan ke Atasan?',
            text: 'Faktur akan diperbarui dengan status Menunggu Persetujuan dan notifikasi WhatsApp akan dikirim ke Atasan untuk persetujuan (ACC). Jurnal akuntansi akan diterbitkan setelah disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Ya, Simpan & Ajukan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (isEditingInvoice) return false;
                $('#formEditInvoice').submit();
            }
        });
    });

    // Initialize Select2 for Edit Uang Muka COA & Kas Bank immediately on document ready
    if (typeof window.initUangMukaCoaSelect2 === 'function') {
        window.initUangMukaCoaSelect2($('#edit_uang_muka_coa_id'), $('#modalEditInvoice'));
    }
    if (typeof window.initKasBankSelect2 === 'function') {
        window.initKasBankSelect2($('#edit_kas_bank_id'), $('#modalEditInvoice'));
    }

    // Prepare and initialize Select2 when modal opens
    $('#modalEditInvoice').on('show.bs.modal', function () {
        if (typeof window.initTerminSelect2 === 'function') {
            window.initTerminSelect2($('#edit_payment_term_id'), $('#modalEditInvoice'));
        }
        if (typeof window.initUangMukaCoaSelect2 === 'function') {
            window.initUangMukaCoaSelect2($('#edit_uang_muka_coa_id'), $('#modalEditInvoice'));
        }
        if (typeof window.initKasBankSelect2 === 'function') {
            window.initKasBankSelect2($('#edit_kas_bank_id'), $('#modalEditInvoice'));
        }
    });

    // Ensure Select2 is correctly rendered & focused when modal animation completes
    $('#modalEditInvoice').on('shown.bs.modal', function () {
        if (typeof window.initTerminSelect2 === 'function') {
            window.initTerminSelect2($('#edit_payment_term_id'), $('#modalEditInvoice'));
        }
        if (typeof window.initUangMukaCoaSelect2 === 'function') {
            window.initUangMukaCoaSelect2($('#edit_uang_muka_coa_id'), $('#modalEditInvoice'));
        }
        if (typeof window.initKasBankSelect2 === 'function') {
            window.initKasBankSelect2($('#edit_kas_bank_id'), $('#modalEditInvoice'));
        }
        if (typeof window.initCoaSelect2 === 'function') {
            window.initCoaSelect2($('#edit_items_tbody .select-item-coa, #edit_extra_costs_tbody .select-coa-extra'), $('#modalEditInvoice'));
        }
    });

    // Destroy dynamic row Select2 on modal hide to prevent memory leak & reset button state
    $('#modalEditInvoice').on('hidden.bs.modal', function () {
        if (typeof setEditButtonsLoading === 'function') {
            setEditButtonsLoading(false);
        }
        $('#edit_items_tbody .select-item-ppn, #edit_items_tbody .select-item-coa, #edit_extra_costs_tbody .select-coa-extra').each(function () {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
    });
});
</script>
