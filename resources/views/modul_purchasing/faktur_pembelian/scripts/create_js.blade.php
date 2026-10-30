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

    // Initialize PPh Withholding Tax Panel for Create Modal
    if (typeof window.initPphPanel === 'function') {
        window.initPphPanel('create', $('#modalTambahInvoice'), function() {
            recalculateCreateSummary();
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

    function refreshExtraCostCoaHint(tr) {
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

    function updateCreateCurrencyDisplay(code, symbol, kurs) {
        createCurrencyCode = (code || 'IDR').toUpperCase();
        createCurrencySymbol = symbol || (['IDR', 'RP'].includes(createCurrencyCode) ? 'Rp' : '$');

        let isRupiah = ['IDR', 'RP'].includes(createCurrencyCode);
        if (isRupiah) {
            createKurs = 1.0;
            $('#create_input_kurs').val('1').prop('readonly', true).addClass('bg-body-tertiary');
        } else {
            createKurs = parseFloat(kurs) || 1.0;
            $('#create_input_kurs').val(formatNumber(createKurs)).prop('readonly', false).removeClass('bg-body-tertiary');
        }

        $('#create_disp_currency').val(createCurrencyCode + (createCurrencyCode === 'USD' ? ' (US Dollar)' : (isRupiah ? ' (Rupiah)' : ' (' + createCurrencyCode + ')')));
        $('#create_kurs_symbol_addon').text(createCurrencyCode);
        $('#create_disp_currency_txt').text(createCurrencyCode + ' (Kurs: ' + formatSmartKurs(createKurs) + ')');
        $('#formTambahInvoice .currency-symbol-addon').text(createCurrencySymbol);
    }

    function updateSupplierNpwpDisplay(npwp) {
        let txtEl = $('#create_disp_npwp_txt');
        let hiddenEl = $('#create_disp_npwp');
        if (npwp && String(npwp).trim() !== '' && String(npwp).trim() !== '-') {
            let cleanNpwp = String(npwp).trim();
            hiddenEl.val(cleanNpwp);
            txtEl.html('<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle font-monospace px-2 py-1"><i class="bi bi-card-text me-1"></i>' + escapeHtml(cleanNpwp) + '</span>');
        } else {
            hiddenEl.val('');
            txtEl.html('<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle font-monospace px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Tidak Ada NPWP (Non-PKP)</span>');
        }
    }

    $(document).on('click', '#create_btn_auto_nomor_faktur', function () {
        $('#create_disp_nomor_faktur_internal').val('').attr('placeholder', '[Otomatis dari Sistem]').focus();
    });

    $(document).on('input keyup', '#create_nomor_faktur_supplier', function () {
        let len = ($(this).val() || '').length;
        $('#create_no_faktur_sup_char_count').text(len + '/100');
        if (len >= 100) {
            $('#create_no_faktur_sup_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
        } else {
            $('#create_no_faktur_sup_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    $(document).on('input keyup', '#create_keterangan', function () {
        let len = ($(this).val() || '').length;
        $('#create_keterangan_char_count').text(len + '/1000');
        if (len >= 1000) {
            $('#create_keterangan_char_count').removeClass('text-muted').addClass('text-danger fw-bold');
        } else {
            $('#create_keterangan_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    function resetCreateInvoiceModal() {
        $('#create_items_tbody .select-item-ppn, #create_items_tbody .select-item-coa, #create_extra_costs_tbody .select-coa-extra').each(function () {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
        $('#formTambahInvoice')[0].reset();
        $('#create_disp_nomor_faktur_internal').val('').attr('placeholder', '[Otomatis dari Sistem]');
        $('#create_nomor_faktur_supplier').val('');
        $('#create_no_faktur_sup_char_count').text('0/100').removeClass('text-danger fw-bold').addClass('text-muted');
        $('#create_keterangan').val('');
        $('#create_keterangan_char_count').text('0/1000').removeClass('text-danger fw-bold').addClass('text-muted');
        $('#create_source_type_gr').prop('checked', true);
        $('#create_container_select_gr').show();
        $('#create_container_select_po').hide();
        $('#create_card_items_title').html('<i class="bi bi-box-seam text-primary"></i> 2. Rincian Barang yang Ditagihkan');
        $('#create_gr_id').val('');
        $('#create_disp_gr_ref').val('');
        $('#create_po_id').val('');
        $('#create_disp_po_ref').val('');
        $('#create_disp_supplier').val('');
        $('#create_disp_supplier_txt').text('-');
        $('#create_sb_supplier').text('-');
        $('#create_disp_npwp_txt').html('—');
        $('#create_disp_npwp').val('');
        $('#create_disp_gudang').val('');
        $('#create_disp_gudang_txt').text('-');
        $('#create_disp_po_nomor').val('');
        $('#create_disp_po_nomor_txt').text('-');
        $('#create_sb_po').text('-');
        $('#create_sb_grn').text('-');
        $('#create_sb_total_barang').text('0');
        $('#create_sb_total_qty').text('0');
        $('#create_box_tender').hide();
        $('#create_disp_tender_txt').text('—');
        $('#create_tender_id').val('');
        if ($('#create_payment_term_id').data('select2')) {
            $('#create_payment_term_id').val('').trigger('change.select2');
        } else {
            $('#create_payment_term_id').val('');
        }
        $('#create_items_tbody').html('<tr id="create_empty_item_row"><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-box-arrow-in-down fs-3 d-block mb-2 text-secondary opacity-50"></i>Pilih Dokumen Penerimaan (GRN) atau Purchase Order (PO) terlebih dahulu untuk memuat daftar barang yang ditagihkan.</td></tr>');
        $('#create_extra_costs_tbody').html('<tr id="create_empty_extra_cost_row"><td colspan="6" class="text-center text-muted py-3 small">Belum ada biaya tambahan. Klik "+ Tambah Biaya" untuk menambahkan.</td></tr>');
        createExtraCostCount = 0;
        updateCreateCurrencyDisplay('IDR', 'Rp', 1.0);
        if (typeof window.setPphState === 'function') {
            window.setPphState('create', null, 0, 0, null);
        }
        $('#create_uang_muka').val('0,00');
        if ($('#create_uang_muka_coa_id').data('select2')) {
            $('#create_uang_muka_coa_id').val('').trigger('change.select2');
        } else {
            $('#create_uang_muka_coa_id').val('');
        }
        if ($('#create_kas_bank_id').data('select2')) {
            $('#create_kas_bank_id').val('').trigger('change.select2');
        } else {
            $('#create_kas_bank_id').val('');
        }
        $('#create_kas_bank_required_star').hide();
        $('#create_kas_bank_error_msg').hide();
        $('#create_kas_bank_id').next('.select2-container').find('.select2-selection').removeClass('border-danger bg-danger bg-opacity-10');
        $('#create_lampiran_faktur').val('');
        $('#create_lampiran_preview_hint').hide();
        $('#create_uang_muka_partial_hint').hide().empty();
        if (typeof setCreateButtonsLoading === 'function') {
            setCreateButtonsLoading(false);
        }
        recalculateCreateSummary();
    }

    // Toggle Source Type (GRN vs Direct PO)
    $('input[name="source_type"]').on('change', function () {
        let src = $(this).val();
        if (src === 'po') {
            $('#create_container_select_gr').hide();
            $('#create_container_select_po').slideDown(200);
            $('#create_card_items_title').html('<i class="bi bi-box-seam text-success"></i> 2. Rincian Barang yang Ditagihkan (dari PO Langsung)');
            if (!$('#create_po_id').val()) {
                $('#create_items_tbody').html('<tr id="create_empty_item_row"><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-box-arrow-in-down fs-3 d-block mb-2 text-secondary opacity-50"></i>Pilih Dokumen Purchase Order (PO) terlebih dahulu untuk memuat daftar barang yang ditagihkan.</td></tr>');
                recalculateCreateSummary();
            }
        } else {
            $('#create_container_select_po').hide();
            $('#create_container_select_gr').slideDown(200);
            $('#create_card_items_title').html('<i class="bi bi-box-seam text-primary"></i> 2. Rincian Barang yang Ditagihkan (dari GRN / BPB)');
            if (!$('#create_gr_id').val()) {
                $('#create_items_tbody').html('<tr id="create_empty_item_row"><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-box-arrow-in-down fs-3 d-block mb-2 text-secondary opacity-50"></i>Pilih Dokumen Penerimaan Barang (GRN) terlebih dahulu untuk memuat daftar barang yang ditagihkan.</td></tr>');
                recalculateCreateSummary();
            }
        }
    });

    // Function to render GR row HTML
    function buildGrRowHtml(gr) {
        let tenderBadgeHtml = '';
        if (gr.nomor_tender) {
            tenderBadgeHtml = `<div class="mt-1.5"><span class="badge font-monospace px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 0.74rem; font-weight: 600;" title="Terkait Proyek Tender"><i class="bi bi-briefcase-fill text-warning"></i><span>${escapeHtml(gr.nomor_tender)}</span></span></div>`;
        }

        let npwpBadgeHtml = (gr.supplier_npwp && String(gr.supplier_npwp).trim() !== '' && String(gr.supplier_npwp).trim() !== '-')
            ? `<div class="mt-1"><span class="badge font-monospace px-2 py-0.5 rounded-pill" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; font-weight: 600;" title="NPWP Terdaftar"><i class="bi bi-card-text me-1 text-success"></i>NPWP: ${escapeHtml(gr.supplier_npwp)}</span></div>`
            : '';

        return `<tr class="row-gr-item"
                    data-id="${gr.id}"
                    data-nomor="${escapeHtml(gr.nomor_gr)}"
                    data-supplier="${escapeHtml(gr.supplier_name)}"
                    data-supplier-npwp="${escapeHtml(gr.supplier_npwp || '')}"
                    data-gudang="${escapeHtml(gr.gudang_name)}"
                    data-po="${escapeHtml(gr.nomor_po)}"
                    data-currency="${escapeHtml(gr.currency_label)}"
                    data-currency-code="${escapeHtml(gr.currency_code)}"
                    data-currency-symbol="${escapeHtml(gr.currency_symbol)}"
                    data-kurs="${escapeHtml(gr.kurs)}"
                    data-payment-term="${gr.payment_term_id || ''}"
                    data-pajak="${gr.pajak_id || ''}"
                    data-tender-id="${gr.tender_id || ''}"
                    data-tender-nomor="${escapeHtml(gr.nomor_tender || '')}"
                    style="cursor: pointer;">
                    <td class="px-3">
                        <span class="badge font-monospace fw-bold px-2.5 py-1 rounded-pill" style="background: rgba(37, 99, 235, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.82rem;">
                            ${escapeHtml(gr.nomor_gr)}
                        </span>
                    </td>
                    <td class="px-3">
                        <span class="font-monospace fw-medium text-body"><i class="bi bi-calendar3 me-1 text-primary small"></i>${escapeHtml(gr.tanggal_gr_fmt)}</span>
                    </td>
                    <td class="px-3">
                        <div class="fw-bold text-body fs-6 mb-0.5">${escapeHtml(gr.supplier_name)}</div>
                        ${npwpBadgeHtml}
                        <div class="mt-1 small" style="color: #94a3b8; font-size: 0.75rem;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Gudang: <strong class="text-body-secondary">${escapeHtml(gr.gudang_name)}</strong></div>
                    </td>
                    <td class="px-3">
                        <div>
                            <span class="badge font-monospace fw-semibold px-2.5 py-1 rounded-pill" style="background: rgba(100, 116, 139, 0.18); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.3); font-size: 0.8rem;">
                                <i class="bi bi-file-earmark-text me-1 text-info"></i>${escapeHtml(gr.nomor_po)}
                            </span>
                        </div>
                        ${tenderBadgeHtml}
                    </td>
                    <td class="px-3 text-end">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm btn-select-gr-row d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg fs-6"></i>
                            <span>Pilih</span>
                        </button>
                    </td>
                </tr>`;
    }

    // Dynamic Loader for Eligible GRNs
    let grSearchDebounce = null;
    function reloadBrowseGrTable(searchQuery = '', showLoading = false) {
        let tbody = $('#tableBrowseGR_tbody');
        let loadingRow = $('#browse_gr_loading_row');

        if (showLoading || tbody.find('tr.row-gr-item').length === 0) {
            loadingRow.show();
        }

        $.ajax({
            url: '{{ route("purchase-invoices.eligible-grs") }}',
            type: 'GET',
            data: { q: searchQuery },
            dataType: 'json',
            success: function (res) {
                loadingRow.hide();
                if (res && res.status === 'success' && res.data) {
                    tbody.find('tr.row-gr-item, tr.no-gr-row').remove();
                    if (res.data.length > 0) {
                        let html = '';
                        res.data.forEach(function (gr) {
                            html += buildGrRowHtml(gr);
                        });
                        tbody.append(html);
                    } else {
                        tbody.append('<tr class="no-gr-row"><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i><div class="fw-semibold">Tidak ada dokumen GRN/BPB yang sesuai pencarian</div><small class="text-muted">Coba ubah kata kunci pencarian Anda.</small></td></tr>');
                    }
                }
            },
            error: function (xhr) {
                loadingRow.hide();
                console.error('Gagal memuat dokumen GR:', xhr);
            }
        });
    }

    // Browse GRN Click (stacked modal on top of modalTambahInvoice)
    $(document).on('click', '#create_btn_browse_gr, #create_disp_gr_ref', function (e) {
        e.preventDefault();
        $('#modalBrowseGR').modal('show');
    });

    // Auto-refresh when browse modal opens
    $('#modalBrowseGR').on('show.bs.modal', function () {
        let curQuery = $('#search_gr_lookup').val();
        reloadBrowseGrTable(curQuery, false);
    });

    // When modalBrowseGR is closed/hidden, preserve modal-open on body so modalTambahInvoice stays open and scrollable
    $(document).on('hidden.bs.modal', '#modalBrowseGR', function () {
        if ($('#modalTambahInvoice').is(':visible') || $('#modalTambahInvoice').hasClass('show')) {
            setTimeout(function () {
                $('body').addClass('modal-open');
            }, 50);
        }
    });

    // Manual Refresh Button in browse modal
    $(document).on('click', '#btn_refresh_browse_gr', function () {
        let curQuery = $('#search_gr_lookup').val();
        reloadBrowseGrTable(curQuery, true);
    });

    // Live Instant Filter + Debounced Server Search
    $('#search_gr_lookup').on('input keyup', function () {
        let val = $(this).val().toLowerCase().trim();

        // 1. Instant client-side filtering on loaded rows
        $('#tableBrowseGR tbody tr.row-gr-item').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });

        // 2. Debounced server-side query to ensure no matches are missed
        clearTimeout(grSearchDebounce);
        grSearchDebounce = setTimeout(function () {
            reloadBrowseGrTable(val, false);
        }, 350);
    });

    // Function to render PO row HTML
    function buildPoRowHtml(po) {
        let tenderBadgeHtml = '';
        if (po.nomor_tender) {
            tenderBadgeHtml = `<div class="mt-1.5"><span class="badge font-monospace px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 0.74rem; font-weight: 600;" title="Terkait Proyek Tender"><i class="bi bi-briefcase-fill text-warning"></i><span>${escapeHtml(po.nomor_tender)}</span></span></div>`;
        }

        let npwpBadgeHtml = (po.supplier_npwp && String(po.supplier_npwp).trim() !== '' && String(po.supplier_npwp).trim() !== '-')
            ? `<div class="mt-1"><span class="badge font-monospace px-2 py-0.5 rounded-pill" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; font-weight: 600;" title="NPWP Terdaftar"><i class="bi bi-card-text me-1 text-success"></i>NPWP: ${escapeHtml(po.supplier_npwp)}</span></div>`
            : '';

        let receiptBadgeHtml = '';
        if (po.receipt_label) {
            receiptBadgeHtml = `<div class="mt-1">
                <span class="badge font-monospace px-2.5 py-0.5 rounded-pill" style="background: rgba(14, 165, 233, 0.18); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.45); font-size: 0.7rem;" title="Status Penerimaan Barang">
                    <i class="bi ${escapeHtml(po.receipt_icon || 'bi-box-seam')} me-1"></i>${escapeHtml(po.receipt_label)}
                </span>
            </div>`;
        }

        return `<tr class="row-po-item"
                    data-id="${po.id}"
                    data-nomor="${escapeHtml(po.nomor_po)}"
                    data-supplier="${escapeHtml(po.supplier_name)}"
                    data-supplier-npwp="${escapeHtml(po.supplier_npwp || '')}"
                    data-currency="${escapeHtml(po.currency_label)}"
                    data-currency-code="${escapeHtml(po.currency_code)}"
                    data-currency-symbol="${escapeHtml(po.currency_symbol)}"
                    data-kurs="${escapeHtml(po.kurs)}"
                    data-payment-term="${po.payment_term_id || ''}"
                    data-pajak="${po.pajak_id || ''}"
                    data-tender-id="${po.tender_id || ''}"
                    data-tender-nomor="${escapeHtml(po.nomor_tender || '')}"
                    style="cursor: pointer;">
                    <td class="px-3">
                        <span class="badge font-monospace fw-bold px-2.5 py-1 rounded-pill" style="background: rgba(16, 185, 129, 0.16); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.82rem;">
                            ${escapeHtml(po.nomor_po)}
                        </span>
                    </td>
                    <td class="px-3">
                        <span class="font-monospace fw-medium text-body"><i class="bi bi-calendar3 me-1 text-success small"></i>${escapeHtml(po.tanggal_po_fmt)}</span>
                    </td>
                    <td class="px-3">
                        <div class="fw-bold text-body fs-6 mb-0.5">${escapeHtml(po.supplier_name)}</div>
                        ${npwpBadgeHtml}
                        <div class="mt-1 d-flex flex-wrap align-items-center gap-1.5">
                            <span class="badge bg-secondary bg-opacity-25 text-body border border-secondary-subtle px-2 py-0.5 rounded-pill font-monospace" style="font-size:0.7rem;">
                                ${escapeHtml(po.status)}
                            </span>
                            <span class="text-muted extra-small" style="font-size: 0.74rem;">
                                <i class="bi bi-boxes me-1"></i>${po.total_items} item barang
                            </span>
                        </div>
                        ${receiptBadgeHtml}
                        ${tenderBadgeHtml}
                    </td>
                    <td class="px-3 text-end">
                        <div class="fw-bold font-monospace text-body fs-6">${escapeHtml(po.grand_total_fmt)}</div>
                    </td>
                    <td class="px-3 text-end">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-sm btn-select-po-row d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-lg fs-6"></i>
                            <span>Pilih</span>
                        </button>
                    </td>
                </tr>`;
    }

    // Dynamic Loader for Eligible POs
    let poSearchDebounce = null;
    function reloadBrowsePoTable(searchQuery = '', showLoading = false) {
        let tbody = $('#tableBrowsePO_tbody');
        let loadingRow = $('#browse_po_loading_row');

        if (showLoading || tbody.find('tr.row-po-item').length === 0) {
            loadingRow.show();
        }

        $.ajax({
            url: '{{ route("purchase-invoices.eligible-pos") }}',
            type: 'GET',
            data: { q: searchQuery },
            dataType: 'json',
            success: function (res) {
                loadingRow.hide();
                if (res && res.status === 'success' && res.data) {
                    tbody.find('tr.row-po-item, tr.no-po-row').remove();
                    if (res.data.length > 0) {
                        let html = '';
                        res.data.forEach(function (po) {
                            html += buildPoRowHtml(po);
                        });
                        tbody.append(html);
                    } else {
                        tbody.append('<tr class="no-po-row"><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>Tidak ada dokumen Purchase Order (PO) yang siap ditagihkan.</td></tr>');
                    }
                }
            },
            error: function (xhr) {
                loadingRow.hide();
                console.error('Gagal memuat dokumen PO:', xhr);
            }
        });
    }

    // Browse PO Click (stacked modal on top of modalTambahInvoice)
    $(document).on('click', '#create_btn_browse_po, #create_disp_po_ref', function (e) {
        e.preventDefault();
        $('#modalBrowsePO').modal('show');
    });

    // Auto-refresh when browse PO modal opens
    $('#modalBrowsePO').on('show.bs.modal', function () {
        let curQuery = $('#search_po_lookup').val();
        reloadBrowsePoTable(curQuery, false);
    });

    // When modalBrowsePO is closed/hidden, preserve modal-open on body so modalTambahInvoice stays open and scrollable
    $(document).on('hidden.bs.modal', '#modalBrowsePO', function () {
        if ($('#modalTambahInvoice').is(':visible') || $('#modalTambahInvoice').hasClass('show')) {
            setTimeout(function () {
                $('body').addClass('modal-open');
            }, 50);
        }
    });

    // Manual Refresh Button in browse PO modal
    $(document).on('click', '#btn_refresh_browse_po', function () {
        let curQuery = $('#search_po_lookup').val();
        reloadBrowsePoTable(curQuery, true);
    });

    // Live Instant Filter + Debounced Server Search for PO
    $('#search_po_lookup').on('input keyup', function () {
        let val = $(this).val().toLowerCase().trim();

        // 1. Instant client-side filtering on loaded rows
        $('#tableBrowsePO tbody tr.row-po-item').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });

        // 2. Debounced server-side query to ensure no matches are missed
        clearTimeout(poSearchDebounce);
        poSearchDebounce = setTimeout(function () {
            reloadBrowsePoTable(val, false);
        }, 350);
    });

    $(document).on('click', '.btn-select-gr-row, .row-gr-item', function (e) {
        e.stopPropagation();
        let tr = $(this).hasClass('row-gr-item') ? $(this) : $(this).closest('tr.row-gr-item');
        let grId = tr.data('id');
        let grNomor = tr.data('nomor');
        let supplierName = tr.data('supplier');
        let supplierNpwp = tr.data('supplier-npwp');
        let gudangName = tr.data('gudang');
        let poNomor = tr.data('po');
        let curCode = tr.data('currency-code') || 'IDR';
        let curSymbol = tr.data('currency-symbol') || (curCode === 'USD' ? '$' : 'Rp');
        let kurs = tr.data('kurs') || 1.0;
        let pTerm = tr.data('payment-term');
        let pajakId = tr.data('pajak');
        let tenderId = tr.data('tender-id');
        let tenderNomor = tr.data('tender-nomor');

        $('#create_gr_id').val(grId);
        $('#create_disp_gr_ref').val(grNomor + ' - ' + supplierName + ' (PO: ' + poNomor + ')');
        $('#create_disp_supplier').val(supplierName);
        $('#create_disp_supplier_txt').text(supplierName);
        $('#create_sb_supplier').text(supplierName);
        updateSupplierNpwpDisplay(supplierNpwp);
        $('#create_disp_gudang').val(gudangName);
        $('#create_disp_gudang_txt').text(gudangName || '-');
        $('#create_disp_po_nomor').val(poNomor);
        $('#create_disp_po_nomor_txt').text(poNomor || '-');
        $('#create_sb_po').text(poNomor || '-');
        $('#create_sb_grn').text(grNomor || '-');

        if (tenderNomor) {
            $('#create_tender_id').val(tenderId || '');
            $('#create_disp_tender_txt').text(tenderNomor);
            $('#create_box_tender').slideDown(150);
        } else {
            $('#create_tender_id').val('');
            $('#create_disp_tender_txt').text('—');
            $('#create_box_tender').hide();
        }

        updateCreateCurrencyDisplay(curCode, curSymbol, kurs);

        if (pTerm) $('#create_payment_term_id').val(pTerm).trigger('change');

        // Tampilkan indikator loading pada tabel barang
        $('#create_items_tbody').html('<tr><td colspan="12" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat rincian barang dari <strong>' + escapeHtml(grNomor) + '</strong>...</td></tr>');

        // Fetch GR Detail items & pre-allocated costs
        $.get('/purchase-invoices/gr/' + grId + '/details', function (res) {
            if (res && res.gr && res.gr.details && res.gr.details.length > 0) {
                // Priority kurs from Goods Receipt (Penerimaan Barang)
                let grKurs = (res.gr.kurs && parseFloat(res.gr.kurs) > 0)
                    ? parseFloat(res.gr.kurs)
                    : ((res.gr.po && res.gr.po.kurs && parseFloat(res.gr.po.kurs) > 0)
                        ? parseFloat(res.gr.po.kurs)
                        : parseFloat(kurs));

                updateCreateCurrencyDisplay(curCode, curSymbol, grKurs);

                // Update tender info from API response if available
                let resTenderNomor = res.nomor_tender || tenderNomor;
                let resTenderId = res.tender_id || tenderId;
                if (resTenderNomor) {
                    $('#create_tender_id').val(resTenderId || '');
                    $('#create_disp_tender_txt').text(resTenderNomor);
                    $('#create_box_tender').slideDown(150);
                }

                let supObj = (res.gr.po && res.gr.po.supplier) ? res.gr.po.supplier : null;
                if (supObj) {
                    updateSupplierNpwpDisplay(supObj.npwp);
                }

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

                    // PPN Per Item from PO Detail (or fallback to PO Header Pajak for older POs)
                    let itemPpnId = poDetail.ppn_pajak_id || (res.gr.po ? res.gr.po.pajak_id : '');
                    let itemTarifPpn = poDetail.tarif_ppn !== undefined && poDetail.tarif_ppn !== null
                        ? parseFloat(poDetail.tarif_ppn)
                        : ((res.gr.po && res.gr.po.pajak) ? parseFloat(res.gr.po.pajak.tarif || 0) : 0);

                    let lineSubtotalGross = qty * hargaSatuan;
                    let lineDiskonTotal = qty * diskonItem;
                    let lineSubtotalNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);
                    let linePpn = Math.round(lineSubtotalNet * (itemTarifPpn / 100) * 100) / 100;
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;

                    tbodyHtml += '<tr class="row-item-barang" data-row="' + totalItemTypes + '">' +
                        '<td class="text-center"><span class="inv-row-num">' + totalItemTypes + '</span></td>' +
                        '<td><span class="inv-badge-code">' + escapeHtml(barangKode) + '</span></td>' +
                        '<td><div class="inv-item-name">' + escapeHtml(barangNama) + '</div><input type="hidden" name="items[' + d.id + '][gr_detail_id]" value="' + d.id + '"></td>' +
                        '<td>' +
                            '<select class="form-select form-select-sm select-item-coa" name="items[' + d.id + '][coa_id]">' + coaOptionsHtml + '</select>' +
                            '<div class="hint-coa-item d-flex align-items-center gap-1 mt-1 text-primary-emphasis opacity-75" style="font-size:0.68rem; line-height:1.2;">' +
                                '<i class="bi bi-magic text-primary"></i><span class="badge-coa-origin fw-medium">Otomatis dari Pemetaan</span>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<div class="input-group input-group-sm justify-content-center" style="max-width: 105px; margin: 0 auto;">' +
                                '<input type="number" step="any" min="0.01" max="' + qty + '" class="form-control form-control-sm text-center fw-bold font-monospace input-item-qty" name="items[' + d.id + '][qty]" value="' + qty + '" data-max-qty="' + qty + '" data-item-id="' + d.id + '">' +
                            '</div>' +
                            '<div class="mt-1 font-monospace" style="font-size:0.68rem; line-height:1.2;">' +
                                '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-1.5 py-0.5 rounded" title="Kuantitas Penerimaan (GR): Maks ' + qty + '">' +
                                    '<i class="bi bi-box-seam me-1"></i>Maks: ' + qty + ' ' + escapeHtml(satuan) +
                                '</span>' +
                            '</div>' +
                            '<div class="qty-warning text-danger extra-small mt-0.5 fw-medium" style="display:none; font-size:0.65rem;"></div>' +
                        '</td>' +
                        '<td class="text-center"><span class="inv-badge-unit">' + escapeHtml(satuan) + '</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm inv-price-group">' +
                                '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-item" name="items[' + d.id + '][harga_satuan]" value="' + formatNumber(hargaSatuan) + '" data-qty="' + qty + '" data-diskon-item="' + diskonItem + '" autocomplete="off">' +
                            '</div>' +
                            '<div class="val-item-price-idr extra-small text-muted font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                        '</td>' +
                        '<td class="text-end">' +
                            (lineDiskonTotal > 0
                                ? '<span class="inv-badge-discount val-item-diskon">- ' + formatMoney(lineDiskonTotal, createCurrencyCode, createCurrencySymbol) + '</span>'
                                : '<span class="val-item-diskon text-muted opacity-50 font-monospace small">-</span>') +
                            '<div class="val-item-diskon-idr extra-small text-danger font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                        '</td>' +
                        '<td>' +
                            '<select class="form-select form-select-sm select-item-ppn font-monospace fw-semibold" name="items[' + d.id + '][ppn_pajak_id]">' +
                                buildPajakOptions(itemPpnId, itemTarifPpn) +
                            '</select>' +
                            '<input type="hidden" name="items[' + d.id + '][tarif_ppn]" class="input-tarif-ppn" value="' + itemTarifPpn + '">' +
                            '<input type="hidden" name="items[' + d.id + '][ppn_nominal]" class="input-ppn-nominal" value="' + linePpn + '">' +
                            '<div class="text-end mt-1">' +
                                '<span class="inv-badge-ppn-preview item-ppn-nominal-preview font-monospace">' + (linePpn > 0 ? '+ ' + formatMoney(linePpn, createCurrencyCode, createCurrencySymbol) : '+ 0') + '</span>' +
                                '<div class="item-ppn-idr-preview extra-small text-muted font-monospace" style="display:none; font-size:0.68rem;"></div>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-end">' +
                            '<span class="inv-val-subtotal val-item-subtotal">' + formatMoney(lineSubtotalNet, createCurrencyCode, createCurrencySymbol) + '</span>' +
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
                });

                $('#create_sb_total_barang').text(totalItemTypes);
                $('#create_sb_total_qty').text(totalQty);
                $('#create_items_tbody').html(tbodyHtml);

                // Initialize Searchable Select2 for PPN dropdowns
                if (typeof window.initPajakSelect2 === 'function') {
                    window.initPajakSelect2($('#create_items_tbody .select-item-ppn'), $('#modalTambahInvoice'));
                }

                // Set pre-resolved COA
                res.gr.details.forEach(function (d) {
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;
                    let row = $('#create_items_tbody tr.row-item-barang').filter(function() {
                        return $(this).find('input[name$="[gr_detail_id]"]').val() == d.id;
                    });
                    row.find('.select-item-coa').val(resolvedCoaId);
                });

                if (typeof window.initCoaSelect2 === 'function') {
                    window.initCoaSelect2($('#create_items_tbody .select-item-coa'), $('#modalTambahInvoice'));
                }

                // Load extra costs from PO if any
                if (res.biaya_lain_details && res.biaya_lain_details.length > 0) {
                    $('#create_empty_extra_cost_row').remove();
                    let extraHtml = '';
                    res.biaya_lain_details.forEach(function (ex) {
                        createExtraCostCount++;
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);

                        let partialHintHtml = '';
                        if (ex.is_partial) {
                            if (ex.remaining_unbilled > 0) {
                                partialHintHtml = '<div class="extra-cost-partial-hint small text-primary font-monospace mt-1" style="font-size:0.68rem; line-height: 1.2;">' +
                                    '<i class="bi bi-pie-chart me-1"></i>Pro-rata Parsial (' + ex.gr_ratio_pct + '% dari PO ' + formatMoney(ex.po_original_nominal, createCurrencyCode, createCurrencySymbol) + ' | Sisa kuota PO: ' + formatMoney(ex.remaining_unbilled, createCurrencyCode, createCurrencySymbol) + ')' +
                                '</div>';
                            } else {
                                partialHintHtml = '<div class="extra-cost-partial-hint small text-success font-monospace mt-1" style="font-size:0.68rem; line-height: 1.2;">' +
                                    '<i class="bi bi-check2-circle me-1"></i>Biaya PO ini sudah 100% ditagihkan pada faktur sebelumnya (Sisa: 0)' +
                                '</div>';
                            }
                        }

                        extraHtml += '<tr class="row-extra-cost" data-row="' + createExtraCostCount + '">' +
                            '<td>' +
                                '<input type="text" class="form-control form-control-sm fw-semibold input-extra-jenis" name="extra_costs[' + createExtraCostCount + '][jenis]" value="' + escapeHtml(jenisVal) + '">' +
                            '</td>' +
                            '<td>' +
                                '<div class="input-group input-group-sm inv-price-group">' +
                                    '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                    '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-extra" name="extra_costs[' + createExtraCostCount + '][nominal]" value="' + formatNumber(ex.nominal || 0) + '">' +
                                '</div>' +
                                partialHintHtml +
                                '<div class="val-extra-cost-idr extra-small text-muted font-monospace text-end mt-1" style="display:none; font-size:0.7rem;"></div>' +
                            '</td>' +
                            '<td class="text-center">' +
                                '<select class="form-select form-select-sm select-hpp-extra fw-semibold" name="extra_costs[' + createExtraCostCount + '][masuk_hpp]">' +
                                    '<option value="0"' + (isHpp === 0 ? ' selected' : '') + '>Tidak</option>' +
                                    '<option value="1"' + (isHpp === 1 ? ' selected' : '') + '>Ya (HPP)</option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="cell-coa-extra">' +
                                '<select class="form-select form-select-sm select-coa-extra" name="extra_costs[' + createExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                '<div class="hint-coa-extra"></div>' +
                            '</td>' +
                            '<td><input type="text" class="form-control form-control-sm" name="extra_costs[' + createExtraCostCount + '][keterangan]" value="' + escapeHtml(ex.keterangan || '') + '"></td>' +
                            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost shadow-sm" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                        '</tr>';
                    });
                    $('#create_extra_costs_tbody').html(extraHtml);

                    // Set extra cost COAs & initialize Select2
                    let extraRows = $('#create_extra_costs_tbody tr.row-extra-cost');
                    res.biaya_lain_details.forEach(function (ex, idx) {
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);
                        let tr = extraRows.eq(idx);
                        tr.find('.select-coa-extra').val(autoCoa);
                        refreshExtraCostCoaHint(tr);
                    });

                    if (typeof window.initCoaSelect2 === 'function') {
                        window.initCoaSelect2($('#create_extra_costs_tbody .select-coa-extra'), $('#modalTambahInvoice'));
                    }
                }

                // Prefill Down Payment (Uang Muka / DP) from PO if available
                let grDpVal = (res.down_payment !== undefined && parseFloat(res.down_payment) > 0) ? parseFloat(res.down_payment) : 0;
                if (grDpVal > 0) {
                    $('#create_uang_muka').val(formatNumber(grDpVal));
                    let dpCoa = res.default_uang_muka_coa_id || '{{ $defaultUangMukaCoaId ?? '' }}';
                    if (dpCoa) {
                        if ($('#create_uang_muka_coa_id').data('select2')) {
                            $('#create_uang_muka_coa_id').val(String(dpCoa)).trigger('change.select2');
                        } else {
                            $('#create_uang_muka_coa_id').val(String(dpCoa));
                        }
                    }

                    if (res.tracking && res.tracking.is_partial && res.tracking.po_dp > 0) {
                        $('#create_uang_muka_partial_hint').html(
                            '<i class="bi bi-pie-chart me-1"></i>Pro-rata DP Parsial: ' + formatMoney(grDpVal, createCurrencyCode, createCurrencySymbol) +
                            ' (' + res.tracking.gr_ratio_pct + '% dari DP PO: ' + formatMoney(res.tracking.po_dp, createCurrencyCode, createCurrencySymbol) +
                            ' | Sisa kuota PO: ' + formatMoney(res.tracking.remaining_dp, createCurrencyCode, createCurrencySymbol) + ')'
                        ).show();
                    } else {
                        $('#create_uang_muka_partial_hint').hide().empty();
                    }
                } else {
                    $('#create_uang_muka').val('0,00');
                    if ($('#create_uang_muka_coa_id').data('select2')) {
                        $('#create_uang_muka_coa_id').val('').trigger('change.select2');
                    } else {
                        $('#create_uang_muka_coa_id').val('');
                    }

                    if (res.tracking && res.tracking.po_dp > 0 && res.tracking.remaining_dp <= 0) {
                        $('#create_uang_muka_partial_hint').html(
                            '<i class="bi bi-check2-circle text-success me-1"></i>Uang Muka PO sudah 100% dipotong pada Faktur sebelumnya (Sisa: 0)'
                        ).show();
                    } else {
                        $('#create_uang_muka_partial_hint').hide().empty();
                    }
                }

                recalculateCreateSummary();
            } else {
                $('#create_items_tbody').html('<tr><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2 text-secondary opacity-50"></i>Dokumen GR ini tidak memiliki rincian barang.</td></tr>');
                recalculateCreateSummary();
            }
        }).fail(function (xhr) {
            console.error('Gagal mengambil rincian GR:', xhr);
            let errMsg = xhr.responseJSON?.message || 'Gagal memuat rincian dokumen penerimaan (GR).';
            $('#create_items_tbody').html('<tr><td colspan="12" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>' + escapeHtml(errMsg) + '</td></tr>');
            Swal.fire('Gagal Memuat Barang!', errMsg, 'error');
        });

        let browseModalEl = document.getElementById('modalBrowseGR');
        if (browseModalEl) {
            let m = bootstrap.Modal.getInstance(browseModalEl);
            if (m) m.hide();
            else $(browseModalEl).modal('hide');
        }
    });

    $(document).on('click', '.btn-select-po-row, .row-po-item', function (e) {
        e.stopPropagation();
        let tr = $(this).hasClass('row-po-item') ? $(this) : $(this).closest('tr.row-po-item');
        let poId = tr.data('id');
        let poNomor = tr.data('nomor');
        let supplierName = tr.data('supplier');
        let supplierNpwp = tr.data('supplier-npwp');
        let curCode = tr.data('currency-code') || 'IDR';
        let curSymbol = tr.data('currency-symbol') || (curCode === 'USD' ? '$' : 'Rp');
        let kurs = tr.data('kurs') || 1.0;
        let pTerm = tr.data('payment-term');
        let pajakId = tr.data('pajak');
        let tenderId = tr.data('tender-id');
        let tenderNomor = tr.data('tender-nomor');

        $('#create_po_id').val(poId);
        $('#create_gr_id').val(''); // Clear GR reference
        $('#create_disp_po_ref').val(poNomor + ' - ' + supplierName);
        $('#create_disp_gr_ref').val('');
        $('#create_disp_supplier').val(supplierName);
        $('#create_disp_supplier_txt').text(supplierName);
        $('#create_sb_supplier').text(supplierName);
        updateSupplierNpwpDisplay(supplierNpwp);
        $('#create_disp_gudang').val('— (Langsung dari PO)');
        $('#create_disp_gudang_txt').text('— (Langsung dari PO)');
        $('#create_disp_po_nomor').val(poNomor);
        $('#create_disp_po_nomor_txt').text(poNomor || '-');
        $('#create_sb_po').text(poNomor || '-');
        $('#create_sb_grn').text('—');

        if (tenderNomor) {
            $('#create_tender_id').val(tenderId || '');
            $('#create_disp_tender_txt').text(tenderNomor);
            $('#create_box_tender').slideDown(150);
        } else {
            $('#create_tender_id').val('');
            $('#create_disp_tender_txt').text('—');
            $('#create_box_tender').hide();
        }

        updateCreateCurrencyDisplay(curCode, curSymbol, kurs);

        if (pTerm) $('#create_payment_term_id').val(pTerm).trigger('change');

        // Tampilkan indikator loading pada tabel barang
        $('#create_items_tbody').html('<tr><td colspan="12" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-success me-2"></div>Memuat rincian barang dari PO <strong>' + escapeHtml(poNomor) + '</strong>...</td></tr>');

        // Fetch PO Detail items & pre-allocated costs
        $.get('/purchase-invoices/po-detail/' + poId, function (res) {
            if (res && res.po && res.po.details && res.po.details.length > 0) {
                let poKurs = (res.po.kurs && parseFloat(res.po.kurs) > 0) ? parseFloat(res.po.kurs) : parseFloat(kurs);
                updateCreateCurrencyDisplay(curCode, curSymbol, poKurs);

                // Update tender info from API response if available
                let resTenderNomor = res.nomor_tender || tenderNomor;
                let resTenderId = res.tender_id || tenderId;
                if (resTenderNomor) {
                    $('#create_tender_id').val(resTenderId || '');
                    $('#create_disp_tender_txt').text(resTenderNomor);
                    $('#create_box_tender').slideDown(150);
                }

                let supObj = res.po ? res.po.supplier : null;
                if (supObj) {
                    updateSupplierNpwpDisplay(supObj.npwp);
                }

                let tbodyHtml = '';
                let totalItemTypes = 0;
                let totalQty = 0;

                res.po.details.forEach(function (d, idx) {
                    totalItemTypes++;
                    let qty = parseFloat(d.jumlah || 0);
                    totalQty += qty;
                    let barangKode = d.barang ? d.barang.kode_barang : '-';
                    let barangNama = d.barang ? d.barang.nama_barang : (d.deskripsi || d.keterangan || 'Barang');
                    let satuan = d.satuan ? d.satuan.nama_satuan : (d.barang && d.barang.satuan ? d.barang.satuan.nama_satuan : 'pcs');
                    let hargaSatuan = parseFloat(d.harga_satuan || 0);
                    let diskonItem = parseFloat(d.diskon_item || 0);

                    // PO Header Global Discount fallback
                    if (diskonItem <= 0 && res.po) {
                        let po = res.po;
                        if (parseFloat(po.diskon_persen || 0) > 0) {
                            diskonItem = hargaSatuan * (parseFloat(po.diskon_persen) / 100);
                        } else if (parseFloat(po.total_diskon || 0) > 0 && parseFloat(po.subtotal || 0) > 0) {
                            diskonItem = (parseFloat(po.total_diskon) / parseFloat(po.subtotal)) * hargaSatuan;
                        }
                    }

                    // PPN Per Item from PO Detail
                    let itemPpnId = d.ppn_pajak_id || (res.po.pajak_id || '');
                    let itemTarifPpn = (d.tarif_ppn !== undefined && d.tarif_ppn !== null)
                        ? parseFloat(d.tarif_ppn)
                        : (res.po.pajak ? parseFloat(res.po.pajak.tarif || 0) : 0);

                    let lineSubtotalGross = qty * hargaSatuan;
                    let lineDiskonTotal = qty * diskonItem;
                    let lineSubtotalNet = Math.max(0, lineSubtotalGross - lineDiskonTotal);
                    let linePpn = Math.round(lineSubtotalNet * (itemTarifPpn / 100) * 100) / 100;
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;

                    let qtyOrdered = parseFloat(d.qty_ordered != null ? d.qty_ordered : (d.jumlah || 0));
                    let qtyReceived = parseFloat(d.qty_received != null ? d.qty_received : 0);
                    let qtyRemaining = parseFloat(d.qty_remaining != null ? d.qty_remaining : Math.max(0, qtyOrdered - qtyReceived));

                    let receiptInfoHtml = '';
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

                    tbodyHtml += '<tr class="row-item-barang" data-row="' + totalItemTypes + '">' +
                        '<td class="text-center"><span class="inv-row-num">' + totalItemTypes + '</span></td>' +
                        '<td><span class="inv-badge-code">' + escapeHtml(barangKode) + '</span></td>' +
                        '<td><div class="inv-item-name">' + escapeHtml(barangNama) + '</div><input type="hidden" name="items[' + d.id + '][po_detail_id]" value="' + d.id + '"></td>' +
                        '<td>' +
                            '<select class="form-select form-select-sm select-item-coa" name="items[' + d.id + '][coa_id]">' + coaOptionsHtml + '</select>' +
                            '<div class="hint-coa-item d-flex align-items-center gap-1 mt-1 text-success-emphasis opacity-75" style="font-size:0.68rem; line-height:1.2;">' +
                                '<i class="bi bi-magic text-success"></i><span class="badge-coa-origin fw-medium">Otomatis dari Akun Barang</span>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-center">' +
                            '<div class="input-group input-group-sm justify-content-center" style="max-width: 105px; margin: 0 auto;">' +
                                '<input type="number" step="any" min="0.01" max="' + qty + '" class="form-control form-control-sm text-center fw-bold font-monospace input-item-qty" name="items[' + d.id + '][qty]" value="' + qty + '" data-max-qty="' + qty + '" data-item-id="' + d.id + '">' +
                            '</div>' +
                            receiptInfoHtml +
                            '<div class="qty-warning text-danger extra-small mt-0.5 fw-medium" style="display:none; font-size:0.65rem;"></div>' +
                        '</td>' +
                        '<td class="text-center"><span class="inv-badge-unit">' + escapeHtml(satuan) + '</span></td>' +
                        '<td>' +
                            '<div class="input-group input-group-sm inv-price-group">' +
                                '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-item" name="items[' + d.id + '][harga_satuan]" value="' + formatNumber(hargaSatuan) + '" data-qty="' + qty + '" data-diskon-item="' + diskonItem + '" autocomplete="off">' +
                            '</div>' +
                            '<div class="val-item-price-idr extra-small text-muted font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                        '</td>' +
                        '<td class="text-end">' +
                            (lineDiskonTotal > 0
                                ? '<span class="inv-badge-discount val-item-diskon">- ' + formatMoney(lineDiskonTotal, createCurrencyCode, createCurrencySymbol) + '</span>'
                                : '<span class="val-item-diskon text-muted opacity-50 font-monospace small">-</span>') +
                            '<div class="val-item-diskon-idr extra-small text-danger font-monospace mt-1 text-end" style="display:none; font-size:0.7rem;"></div>' +
                        '</td>' +
                        '<td>' +
                            '<select class="form-select form-select-sm select-item-ppn font-monospace fw-semibold" name="items[' + d.id + '][ppn_pajak_id]">' +
                                buildPajakOptions(itemPpnId, itemTarifPpn) +
                            '</select>' +
                            '<input type="hidden" name="items[' + d.id + '][tarif_ppn]" class="input-tarif-ppn" value="' + itemTarifPpn + '">' +
                            '<input type="hidden" name="items[' + d.id + '][ppn_nominal]" class="input-ppn-nominal" value="' + linePpn + '">' +
                            '<div class="text-end mt-1">' +
                                '<span class="inv-badge-ppn-preview item-ppn-nominal-preview font-monospace">' + (linePpn > 0 ? '+ ' + formatMoney(linePpn, createCurrencyCode, createCurrencySymbol) : '+ 0') + '</span>' +
                                '<div class="item-ppn-idr-preview extra-small text-muted font-monospace" style="display:none; font-size:0.68rem;"></div>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-end">' +
                            '<span class="inv-val-subtotal val-item-subtotal">' + formatMoney(lineSubtotalNet, createCurrencyCode, createCurrencySymbol) + '</span>' +
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
                });

                $('#create_sb_total_barang').text(totalItemTypes);
                $('#create_sb_total_qty').text(totalQty);
                $('#create_items_tbody').html(tbodyHtml);

                // Initialize Searchable Select2 for PPN dropdowns
                if (typeof window.initPajakSelect2 === 'function') {
                    window.initPajakSelect2($('#create_items_tbody .select-item-ppn'), $('#modalTambahInvoice'));
                }

                // Set pre-resolved COA
                res.po.details.forEach(function (d) {
                    let resolvedCoaId = d.resolved_coa_id || defaultGrniCoaId;
                    let row = $('#create_items_tbody tr.row-item-barang').filter(function() {
                        return $(this).find('input[name$="[po_detail_id]"]').val() == d.id;
                    });
                    row.find('.select-item-coa').val(resolvedCoaId);
                });

                if (typeof window.initCoaSelect2 === 'function') {
                    window.initCoaSelect2($('#create_items_tbody .select-item-coa'), $('#modalTambahInvoice'));
                }

                // Load extra costs from PO if any
                if (res.biaya_lain_details && res.biaya_lain_details.length > 0) {
                    $('#create_empty_extra_cost_row').remove();
                    let extraHtml = '';
                    res.biaya_lain_details.forEach(function (ex) {
                        createExtraCostCount++;
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);

                        let partialHintHtml = '';
                        if (ex.is_partial) {
                            if (ex.remaining_unbilled > 0) {
                                partialHintHtml = '<div class="extra-cost-partial-hint small text-primary font-monospace mt-1" style="font-size:0.68rem; line-height: 1.2;">' +
                                    '<i class="bi bi-pie-chart me-1"></i>Pro-rata Parsial (' + ex.gr_ratio_pct + '% dari PO ' + formatMoney(ex.po_original_nominal, createCurrencyCode, createCurrencySymbol) + ' | Sisa kuota PO: ' + formatMoney(ex.remaining_unbilled, createCurrencyCode, createCurrencySymbol) + ')' +
                                '</div>';
                            } else {
                                partialHintHtml = '<div class="extra-cost-partial-hint small text-success font-monospace mt-1" style="font-size:0.68rem; line-height: 1.2;">' +
                                    '<i class="bi bi-check2-circle me-1"></i>Biaya PO ini sudah 100% ditagihkan pada faktur sebelumnya (Sisa: 0)' +
                                '</div>';
                            }
                        }

                        extraHtml += '<tr class="row-extra-cost" data-row="' + createExtraCostCount + '">' +
                            '<td>' +
                                '<input type="text" class="form-control form-control-sm fw-semibold input-extra-jenis" name="extra_costs[' + createExtraCostCount + '][jenis]" value="' + escapeHtml(jenisVal) + '">' +
                            '</td>' +
                            '<td>' +
                                '<div class="input-group input-group-sm inv-price-group">' +
                                    '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(createCurrencySymbol) + '</span>' +
                                    '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-extra" name="extra_costs[' + createExtraCostCount + '][nominal]" value="' + formatNumber(ex.nominal || 0) + '">' +
                                '</div>' +
                                partialHintHtml +
                                '<div class="val-extra-cost-idr extra-small text-muted font-monospace text-end mt-1" style="display:none; font-size:0.7rem;"></div>' +
                            '</td>' +
                            '<td class="text-center">' +
                                '<select class="form-select form-select-sm select-hpp-extra fw-semibold" name="extra_costs[' + createExtraCostCount + '][masuk_hpp]">' +
                                    '<option value="0"' + (isHpp === 0 ? ' selected' : '') + '>Tidak</option>' +
                                    '<option value="1"' + (isHpp === 1 ? ' selected' : '') + '>Ya (HPP)</option>' +
                                '</select>' +
                            '</td>' +
                            '<td class="cell-coa-extra">' +
                                '<select class="form-select form-select-sm select-coa-extra" name="extra_costs[' + createExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                                '<div class="hint-coa-extra"></div>' +
                            '</td>' +
                            '<td><input type="text" class="form-control form-control-sm" name="extra_costs[' + createExtraCostCount + '][keterangan]" value="' + escapeHtml(ex.keterangan || '') + '"></td>' +
                            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost shadow-sm" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
                        '</tr>';
                    });
                    $('#create_extra_costs_tbody').html(extraHtml);

                    // Set extra cost COAs & initialize Select2
                    let extraRows = $('#create_extra_costs_tbody tr.row-extra-cost');
                    res.biaya_lain_details.forEach(function (ex, idx) {
                        let isHpp = ex.masuk_hpp ? 1 : 0;
                        let jenisVal = ex.jenis || ex.keterangan || 'Biaya Tambahan';
                        let autoCoa = ex.coa_id || detectCoaForExtraCost(jenisVal, isHpp);
                        let tr = extraRows.eq(idx);
                        tr.find('.select-coa-extra').val(autoCoa);
                        refreshExtraCostCoaHint(tr);
                    });

                    if (typeof window.initCoaSelect2 === 'function') {
                        window.initCoaSelect2($('#create_extra_costs_tbody .select-coa-extra'), $('#modalTambahInvoice'));
                    }
                }

                // Prefill Down Payment (Uang Muka / DP) from PO if available
                let poDpVal = (res.down_payment !== undefined && parseFloat(res.down_payment) > 0) ? parseFloat(res.down_payment) : 0;
                if (poDpVal > 0) {
                    $('#create_uang_muka').val(formatNumber(poDpVal));
                    let dpCoa = res.default_uang_muka_coa_id || '{{ $defaultUangMukaCoaId ?? '' }}';
                    if (dpCoa) {
                        if ($('#create_uang_muka_coa_id').data('select2')) {
                            $('#create_uang_muka_coa_id').val(String(dpCoa)).trigger('change.select2');
                        } else {
                            $('#create_uang_muka_coa_id').val(String(dpCoa));
                        }
                    }

                    if (res.tracking && res.tracking.is_partial && res.tracking.po_dp > 0) {
                        $('#create_uang_muka_partial_hint').html(
                            '<i class="bi bi-pie-chart me-1"></i>Pro-rata DP Parsial: ' + formatMoney(poDpVal, createCurrencyCode, createCurrencySymbol) +
                            ' (' + res.tracking.gr_ratio_pct + '% dari DP PO: ' + formatMoney(res.tracking.po_dp, createCurrencyCode, createCurrencySymbol) +
                            ' | Sisa kuota PO: ' + formatMoney(res.tracking.remaining_dp, createCurrencyCode, createCurrencySymbol) + ')'
                        ).show();
                    } else {
                        $('#create_uang_muka_partial_hint').hide().empty();
                    }
                } else {
                    $('#create_uang_muka').val('0,00');
                    if ($('#create_uang_muka_coa_id').data('select2')) {
                        $('#create_uang_muka_coa_id').val('').trigger('change.select2');
                    } else {
                        $('#create_uang_muka_coa_id').val('');
                    }

                    if (res.tracking && res.tracking.po_dp > 0 && res.tracking.remaining_dp <= 0) {
                        $('#create_uang_muka_partial_hint').html(
                            '<i class="bi bi-check2-circle text-success me-1"></i>Uang Muka PO sudah 100% dipotong pada Faktur sebelumnya (Sisa: 0)'
                        ).show();
                    } else {
                        $('#create_uang_muka_partial_hint').hide().empty();
                    }
                }

                // Prefill PPh from PO if exists
                if (res.po.pph_pajak_id && typeof window.setPphState === 'function') {
                    let pphTarif = res.po.pph_pajak ? parseFloat(res.po.pph_pajak.tarif || 0) : 0;
                    window.setPphState('create', res.po.pph_pajak_id, pphTarif, 0, null);
                }

                recalculateCreateSummary();
            } else {
                $('#create_items_tbody').html('<tr><td colspan="12" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2 text-secondary opacity-50"></i>Dokumen PO ini tidak memiliki rincian barang.</td></tr>');
                recalculateCreateSummary();
            }
        }).fail(function (xhr) {
            console.error('Gagal mengambil rincian PO:', xhr);
            let errMsg = xhr.responseJSON?.message || 'Gagal memuat rincian dokumen Purchase Order (PO).';
            $('#create_items_tbody').html('<tr><td colspan="12" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>' + escapeHtml(errMsg) + '</td></tr>');
            Swal.fire('Gagal Memuat Barang!', errMsg, 'error');
        });

        let browsePoModalEl = document.getElementById('modalBrowsePO');
        if (browsePoModalEl) {
            let m = bootstrap.Modal.getInstance(browsePoModalEl);
            if (m) m.hide();
            else $(browsePoModalEl).modal('hide');
        }
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
                '<input type="text" class="form-control form-control-sm fw-semibold input-extra-jenis" name="extra_costs[' + createExtraCostCount + '][jenis]" placeholder="Jenis Biaya..." value="Ongkos Kirim" autocomplete="off">' +
            '</td>' +
            '<td>' +
                '<div class="input-group input-group-sm inv-price-group">' +
                    '<span class="input-group-text currency-symbol-addon font-monospace">' + escapeHtml(createCurrencySymbol) + '</span>' +
                    '<input type="text" class="form-control form-control-sm text-end fw-bold font-monospace number-format input-nominal-extra" name="extra_costs[' + createExtraCostCount + '][nominal]" value="0,00" autocomplete="off">' +
                '</div>' +
                '<div class="val-extra-cost-idr extra-small text-muted font-monospace text-end mt-1" style="display:none; font-size:0.7rem;"></div>' +
            '</td>' +
            '<td class="text-center">' +
                '<select class="form-select form-select-sm select-hpp-extra fw-semibold" name="extra_costs[' + createExtraCostCount + '][masuk_hpp]">' +
                    '<option value="0">Tidak</option>' +
                    '<option value="1" selected>Ya (HPP)</option>' +
                '</select>' +
            '</td>' +
            '<td class="cell-coa-extra">' +
                '<select class="form-select form-select-sm select-coa-extra" name="extra_costs[' + createExtraCostCount + '][coa_id]">' + coaOptionsHtml + '</select>' +
                '<div class="hint-coa-extra"></div>' +
            '</td>' +
            '<td><input type="text" class="form-control form-control-sm" name="extra_costs[' + createExtraCostCount + '][keterangan]" placeholder="Keterangan..." autocomplete="off"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-remove-extra-cost shadow-sm" title="Hapus"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

        $('#create_extra_costs_tbody').append(newRow);
        let tr = $('#create_extra_costs_tbody tr.row-extra-cost').last();
        if (defaultExtraCoa) {
            tr.find('.select-coa-extra').val(defaultExtraCoa);
        }
        refreshExtraCostCoaHint(tr);
        if (typeof window.initCoaSelect2 === 'function') {
            window.initCoaSelect2(tr.find('.select-coa-extra'), $('#modalTambahInvoice'));
        }
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
            if (tr.find('.select-coa-extra').data('select2')) {
                tr.find('.select-coa-extra').val(suggestedCoa).trigger('change.select2');
            } else {
                tr.find('.select-coa-extra').val(suggestedCoa);
            }
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
            if (tr.find('.select-coa-extra').data('select2')) {
                tr.find('.select-coa-extra').val(suggestedCoa).trigger('change.select2');
            } else {
                tr.find('.select-coa-extra').val(suggestedCoa);
            }
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

    $(document).on('input keyup change blur', '#modalTambahInvoice .input-item-qty', function () {
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
        recalculateCreateSummary();
    });

    $(document).on('input change', '#modalTambahInvoice .input-nominal-item, #modalTambahInvoice .input-nominal-extra, #modalTambahInvoice .select-item-ppn, #modalTambahInvoice .select-hpp-extra', function () {
        recalculateCreateSummary();
    });

    $(document).on('input change keyup blur', '#create_uang_muka', function () {
        let dp = unformatNumber($(this).val());
        if (dp > 0) {
            if (!$('#create_uang_muka_coa_id').val()) {
                let defCoa = '{{ $defaultUangMukaCoaId ?? '' }}';
                if (defCoa) {
                    if ($('#create_uang_muka_coa_id').data('select2')) {
                        $('#create_uang_muka_coa_id').val(String(defCoa)).trigger('change.select2');
                    } else {
                        $('#create_uang_muka_coa_id').val(String(defCoa));
                    }
                }
            }
        } else {
            if ($('#create_uang_muka_coa_id').data('select2')) {
                $('#create_uang_muka_coa_id').val('').trigger('change.select2');
            } else {
                $('#create_uang_muka_coa_id').val('');
            }
        }
        recalculateCreateSummary();
    });

    $(document).on('input change keyup paste', '#create_input_kurs', function () {
        if (['IDR', 'RP'].includes(createCurrencyCode.toUpperCase())) {
            createKurs = 1.0;
            $(this).val('1');
            return;
        }
        let val = unformatNumber($(this).val());
        createKurs = val > 0 ? val : 1.0;
        $('#create_disp_currency_txt').text(createCurrencyCode + ' (Kurs: ' + formatSmartKurs(createKurs) + ')');
        recalculateCreateSummary();
    });

    $(document).on('blur', '#create_input_kurs', function () {
        if (['IDR', 'RP'].includes(createCurrencyCode.toUpperCase())) {
            createKurs = 1.0;
            $(this).val('1');
            return;
        }
        let val = unformatNumber($(this).val());
        if (val <= 0) val = 1.0;
        createKurs = val;
        $(this).val(formatNumber(val));
        $('#create_disp_currency_txt').text(createCurrencyCode + ' (Kurs: ' + formatSmartKurs(createKurs) + ')');
        recalculateCreateSummary();
    });

    function recalculateCreateSummary() {
        let subtotalBarangGross = 0;
        let totalDiskon = 0;
        let totalPpn = 0;
        let totalQtySum = 0;
        let showIdrEquiv = (createCurrencyCode !== 'IDR' || Math.abs(createKurs - 1.0) > 0.0001);

        $('#create_items_tbody tr.row-item-barang').each(function () {
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

            $(this).find('.item-ppn-nominal-preview').text(linePpn > 0 ? '+ ' + formatMoney(linePpn, createCurrencyCode, createCurrencySymbol) : '+ 0');
            if (lineDiskon > 0) {
                $(this).find('.val-item-diskon').removeClass('text-muted opacity-50 small').addClass('inv-badge-discount').text('- ' + formatMoney(lineDiskon, createCurrencyCode, createCurrencySymbol));
            } else {
                $(this).find('.val-item-diskon').removeClass('inv-badge-discount').addClass('text-muted opacity-50 small').text('-');
            }
            $(this).find('.val-item-subtotal').text(formatMoney(lineNet, createCurrencyCode, createCurrencySymbol));

            // Real-time IDR equivalents per item
            if (showIdrEquiv) {
                let priceIdr = Math.round(price * createKurs * 100) / 100;
                let diskonIdr = Math.round(lineDiskon * createKurs * 100) / 100;
                let ppnIdr = Math.round(linePpn * createKurs * 100) / 100;
                let subtotalIdr = Math.round(lineNet * createKurs * 100) / 100;

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
        $('#create_extra_costs_tbody tr.row-extra-cost').each(function () {
            let nom = Math.round(unformatNumber($(this).find('.input-nominal-extra').val()) * 100) / 100;
            let isHpp = $(this).find('.select-hpp-extra').val() === '1';
            if (isHpp) {
                biayaHppTotal += nom;
            } else {
                biayaNonHppTotal += nom;
            }

            // Real-time IDR for extra costs
            if (showIdrEquiv && nom > 0) {
                let nomIdr = Math.round(nom * createKurs * 100) / 100;
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
        $('#create_items_tbody tr.row-item-barang').each(function () {
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
                $(this).find('.val-item-alokasi-biaya').html('<span class="inv-badge-alokasi">+ ' + formatMoney(alokasiBiayaLine, createCurrencyCode, createCurrencySymbol) + '</span>');
                $(this).find('.val-item-alokasi-biaya-unit').text('≈ +' + formatMoney(alokasiPerUnit, createCurrencyCode, createCurrencySymbol) + ' / ' + satuanText).show();
            } else {
                $(this).find('.val-item-alokasi-biaya').html('<span class="text-muted opacity-50">-</span>');
                $(this).find('.val-item-alokasi-biaya-unit').hide().empty();
            }

            if (biayaHppTotal > 0) {
                $(this).find('.val-item-hpp-akhir').html('<span class="inv-badge-hpp">' + formatMoney(hppAkhirPerUnit, createCurrencyCode, createCurrencySymbol) + '</span>');
                $(this).find('.val-item-hpp-akhir-total').html('Total: <strong>' + formatMoney(lineNet + alokasiBiayaLine, createCurrencyCode, createCurrencySymbol) + '</strong>').show();
            } else {
                $(this).find('.val-item-hpp-akhir').html('<span class="inv-badge-hpp" style="background: rgba(148, 163, 184, 0.12) !important; color: inherit !important; border-color: rgba(148, 163, 184, 0.25) !important;">' + formatMoney(hppAkhirPerUnit, createCurrencyCode, createCurrencySymbol) + '</span>');
                $(this).find('.val-item-hpp-akhir-total').hide().empty();
            }
        });

        let pphNominal = (typeof window.computePph === 'function')
            ? window.computePph('create', dpp, createCurrencyCode)
            : 0;
        pphNominal = Math.round(pphNominal * 100) / 100;

        let totalInvoice = Math.max(0, Math.round((dpp + totalBiayaLain + totalPpn - pphNominal) * 100) / 100);
        window.currentCreateTotalInvoice = totalInvoice;

        let uangMukaVal = unformatNumber($('#create_uang_muka').val());
        if (totalInvoice > 0 && uangMukaVal > totalInvoice + 0.005) {
            $('#create_uang_muka').addClass('is-invalid');
            $('#create_uang_muka_error_msg').html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Nominal Uang Muka tidak boleh melebihi Total Tagihan (' + formatMoney(totalInvoice, createCurrencyCode, createCurrencySymbol) + ')!').show();
            $('#btn_simpan_draft_create, #btn_ajukan_create').prop('disabled', true);
        } else {
            $('#create_uang_muka').removeClass('is-invalid');
            $('#create_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_create, #btn_ajukan_create').prop('disabled', false);
        }

        let effectiveDp = Math.min(uangMukaVal, totalInvoice);
        let sisaHutang = Math.max(0, Math.round((totalInvoice - effectiveDp) * 100) / 100);

        $('#create_label_ppn').text('Total PPN Masukan (Item)');
        $('#create_sum_subtotal').text(formatNumberOnly(subtotalBarangGross, createCurrencyCode));
        $('#create_sum_diskon').text('- ' + formatNumberOnly(totalDiskon, createCurrencyCode));
        $('#create_sum_dpp').text(formatNumberOnly(dpp, createCurrencyCode));
        $('#create_sum_biaya_lain').text(formatNumberOnly(totalBiayaLain, createCurrencyCode));
        $('#create_sum_ppn').text(formatNumberOnly(totalPpn, createCurrencyCode));
        $('#create_sum_total_invoice').text(formatMoney(totalInvoice, createCurrencyCode, createCurrencySymbol));

        if (uangMukaVal > 0) {
            $('#create_box_sisa_hutang').show();
            $('#create_sum_sisa_hutang').text(formatMoney(sisaHutang, createCurrencyCode, createCurrencySymbol));
        } else {
            $('#create_box_sisa_hutang').hide();
        }

        if (showIdrEquiv) {
            let subtotalIdr = Math.round(subtotalBarangGross * createKurs * 100) / 100;
            let diskonTotalIdr = Math.round(totalDiskon * createKurs * 100) / 100;
            let dppIdr = Math.round(dpp * createKurs * 100) / 100;
            let ppnTotalIdr = Math.round(totalPpn * createKurs * 100) / 100;
            let biayaLainIdr = Math.round(totalBiayaLain * createKurs * 100) / 100;
            let pphIdr = Math.round(pphNominal * createKurs * 100) / 100;
            let grandTotalIdr = Math.round(totalInvoice * createKurs * 100) / 100;

            $('#create_sum_subtotal_idr').text('≈ Rp ' + formatNumber(subtotalIdr)).show();
            $('#create_sum_diskon_idr').text(totalDiskon > 0 ? '≈ -Rp ' + formatNumber(diskonTotalIdr) : '').toggle(totalDiskon > 0);
            $('#create_sum_dpp_idr').text('≈ Rp ' + formatNumber(dppIdr)).show();
            $('#create_sum_ppn_idr').text('≈ Rp ' + formatNumber(ppnTotalIdr)).show();
            $('#create_sum_biaya_lain_idr').text('≈ Rp ' + formatNumber(biayaLainIdr)).show();
            $('#create_sum_pph_idr').text(pphNominal > 0 ? '≈ -Rp ' + formatNumber(pphIdr) : '').toggle(pphNominal > 0);

            $('#create_box_total_idr').show();
            $('#create_sum_total_invoice_idr').text('Rp ' + formatNumber(grandTotalIdr));
            $('#create_sum_kurs_info').text('Kurs: ' + formatSmartKurs(createKurs));

            if (uangMukaVal > 0) {
                let sisaHutangIdr = Math.round(sisaHutang * createKurs * 100) / 100;
                $('#create_box_sisa_hutang_idr').show();
                $('#create_sum_sisa_hutang_idr').text('Rp ' + formatNumber(sisaHutangIdr));
            } else {
                $('#create_box_sisa_hutang_idr').hide();
            }

            if (createCurrencyCode !== 'IDR') {
                $('#create_label_total_tagihan').html('Grand Total (' + escapeHtml(createCurrencyCode) + ')');
            } else {
                $('#create_label_total_tagihan').text('Grand Total Tagihan');
            }
        } else {
            $('#create_sum_subtotal_idr, #create_sum_diskon_idr, #create_sum_dpp_idr, #create_sum_ppn_idr, #create_sum_biaya_lain_idr, #create_sum_pph_idr').hide().empty();
            $('#create_box_total_idr').hide();
            $('#create_box_sisa_hutang_idr').hide();
            $('#create_label_total_tagihan').text('Grand Total Tagihan');
        }
        $('#create_sb_total_qty').text(totalQtySum % 1 === 0 ? totalQtySum : formatNumber(totalQtySum));
    }

    // Helper validasi wajib Kas / Bank jika Uang Muka (DP) > 0 & Cek Saldo Cukup
    function validateCreateKasBankDp() {
        let dpVal = unformatNumber($('#create_uang_muka').val());
        let $select2Container = $('#create_kas_bank_id').next('.select2-container').find('.select2-selection');
        if (dpVal > 0.005) {
            $('#create_kas_bank_required_star').show();
            if (!$('#create_kas_bank_id').val()) {
                $('#create_kas_bank_error_msg').show();
                $('#create_kas_bank_saldo_error_msg').hide();
                $select2Container.addClass('border-danger bg-danger bg-opacity-10');
                return false;
            } else {
                $('#create_kas_bank_error_msg').hide();

                // Cek Saldo Kas / Bank
                let $selectedOpt = $('#create_kas_bank_id option:selected');
                let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
                let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
                let kurs = (typeof createKurs !== 'undefined' && createKurs > 0) ? createKurs : 1;
                let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

                if (saldoKasBank < dpNominalIdr) {
                    $('#create_kas_bank_saldo_error_msg')
                        .html('<i class="bi bi-slash-circle-fill me-1"></i>Saldo rekening tidak cukup! Saldo saat ini: <strong>Rp ' + formatMoney(saldoKasBank, 'IDR', 'Rp') + '</strong>, DP dibutuhkan: <strong>Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>')
                        .show();
                    $select2Container.addClass('border-danger bg-danger bg-opacity-10');
                    return false;
                } else {
                    $('#create_kas_bank_saldo_error_msg').hide();
                    $select2Container.removeClass('border-danger bg-danger bg-opacity-10');
                    return true;
                }
            }
        } else {
            $('#create_kas_bank_required_star').hide();
            $('#create_kas_bank_error_msg').hide();
            $('#create_kas_bank_saldo_error_msg').hide();
            $select2Container.removeClass('border-danger bg-danger bg-opacity-10');
            return true;
        }
    }

    $(document).on('change', '#create_kas_bank_id', function () {
        validateCreateKasBankDp();
    });

    // Event listener for Uang Muka DP: Live validate on input without breaking typing cursor
    $('#create_uang_muka').on('input keyup paste', function () {
        let total = (typeof window.currentCreateTotalInvoice !== 'undefined')
            ? window.currentCreateTotalInvoice
            : unformatNumber($('#create_sum_total_invoice').text());
        let val = unformatNumber($(this).val());

        validateCreateKasBankDp();

        if (total > 0 && val > total + 0.005) {
            $(this).addClass('is-invalid');
            $('#create_uang_muka_error_msg').html('<i class="bi bi-exclamation-triangle-fill me-1"></i>Nominal Uang Muka tidak boleh melebihi Total Tagihan (' + formatMoney(total, createCurrencyCode, createCurrencySymbol) + ')!').show();
            $('#btn_simpan_draft_create, #btn_ajukan_create').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $('#create_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_create, #btn_ajukan_create').prop('disabled', false);
        }
        recalculateCreateSummary();
    });

    $('#create_uang_muka').on('blur', function () {
        let total = (typeof window.currentCreateTotalInvoice !== 'undefined')
            ? window.currentCreateTotalInvoice
            : unformatNumber($('#create_sum_total_invoice').text());
        let val = unformatNumber($(this).val());

        validateCreateKasBankDp();

        if (total > 0 && val > total + 0.005) {
            // When leaving input, gracefully clamp to maximum invoice total
            $(this).val(formatNumber(total));
            $(this).removeClass('is-invalid');
            $('#create_uang_muka_error_msg').hide();
            $('#btn_simpan_draft_create, #btn_ajukan_create').prop('disabled', false);
            recalculateCreateSummary();
        }
    });


    // Event listener for Foto Bukti Lampiran File Input
    $('#create_lampiran_faktur').on('change', function () {
        let file = this.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Ukuran File Terlalu Besar', 'Maksimal ukuran file lampiran adalah 5 MB.', 'warning');
                $(this).val('');
                $('#create_lampiran_preview_hint').hide();
                return;
            }
            $('#create_lampiran_preview_hint').html('<i class="bi bi-check-circle me-1"></i>' + escapeHtml(file.name)).show();
        } else {
            $('#create_lampiran_preview_hint').hide();
        }
    });

    // State to prevent duplicate submissions & manage button loading
    let isCreatingInvoice = false;

    function setCreateButtonsLoading(isLoading, actionType) {
        isCreatingInvoice = isLoading;
        let $btnDraft = $('#btn_save_draft_invoice');
        let $btnSubmit = $('#btn_submit_post_invoice');
        let $btnCancel = $('#modalTambahInvoice [data-bs-dismiss="modal"]');

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

    // Save Draft Button
    $('#btn_save_draft_invoice').on('click', function () {
        if (isCreatingInvoice) return false;

        let dpVal = unformatNumber($('#create_uang_muka').val());
        let currentTotal = (typeof window.currentCreateTotalInvoice !== 'undefined')
            ? window.currentCreateTotalInvoice
            : unformatNumber($('#create_sum_total_invoice').text());

        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka (DP)',
                html: 'Nominal Uang Muka (DP) sebesar <b>' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + '</b> tidak boleh melebihi Total Pembelian sebesar <b>' + formatMoney(currentTotal, createCurrencyCode, createCurrencySymbol) + '</b>!',
                icon: 'warning'
            });
            $('#create_uang_muka').focus();
            return false;
        }

        if (dpVal > 0.005) {
            if (!$('#create_kas_bank_id').val()) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih!',
                    html: 'Faktur memiliki <b>Uang Muka (DP) sebesar ' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + '</b>.<br><br>Anda <b>wajib</b> memilih rekening Kas / Bank yang digunakan untuk pembayaran DP tersebut sebelum menyimpan.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Pilih Kas / Bank Sekarang'
                }).then(() => {
                    $('#create_kas_bank_id').select2('open');
                });
                return false;
            }

            let $selectedOpt = $('#create_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof createKurs !== 'undefined' && createKurs > 0) ? createKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            if (saldoKasBank < dpNominalIdr) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank saat ini: <strong class="text-danger">Rp ' + formatMoney(saldoKasBank, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (createCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        $('#create_action_type').val('draft');
        $('#formTambahInvoice').submit();
    });

    // Save & Ajukan ke Atasan Button
    $('#btn_submit_post_invoice').on('click', function () {
        if (isCreatingInvoice) return false;

        let dpVal = unformatNumber($('#create_uang_muka').val());
        let currentTotal = (typeof window.currentCreateTotalInvoice !== 'undefined')
            ? window.currentCreateTotalInvoice
            : unformatNumber($('#create_sum_total_invoice').text());

        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka (DP)',
                html: 'Nominal Uang Muka (DP) sebesar <b>' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + '</b> tidak boleh melebihi Total Pembelian sebesar <b>' + formatMoney(currentTotal, createCurrencyCode, createCurrencySymbol) + '</b>!',
                icon: 'warning'
            });
            $('#create_uang_muka').focus();
            return false;
        }

        if (dpVal > 0.005) {
            if (!$('#create_kas_bank_id').val()) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih!',
                    html: 'Faktur memiliki <b>Uang Muka (DP) sebesar ' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + '</b>.<br><br>Anda <b>wajib</b> memilih rekening Kas / Bank yang digunakan untuk pembayaran DP tersebut sebelum mengajukan.',
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Pilih Kas / Bank Sekarang'
                }).then(() => {
                    $('#create_kas_bank_id').select2('open');
                });
                return false;
            }

            let $selectedOpt = $('#create_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof createKurs !== 'undefined' && createKurs > 0) ? createKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            if (saldoKasBank < dpNominalIdr) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank saat ini: <strong class="text-danger">Rp ' + formatMoney(saldoKasBank, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (createCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        $('#create_action_type').val('submit');
        Swal.fire({
            title: 'Simpan & Ajukan ke Atasan?',
            text: 'Faktur akan disimpan dengan status Menunggu Persetujuan dan notifikasi WhatsApp akan dikirim ke Atasan untuk persetujuan (ACC). Jurnal akuntansi akan diterbitkan setelah disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Ya, Simpan & Ajukan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (isCreatingInvoice) return false;
                $('#formTambahInvoice').submit();
            }
        });
    });

    // Form Submit Create Handler
    $('#formTambahInvoice').on('submit', function (e) {
        e.preventDefault();

        if (isCreatingInvoice) return false;

        // Validasi Nomor Faktur Supplier (Wajib & Maks 100 Karakter)
        let noFakturSup = ($('#create_nomor_faktur_supplier').val() || '').trim();
        if (!noFakturSup) {
            Swal.fire({
                title: 'Nomor Faktur Supplier Wajib Diisi',
                text: 'Silakan masukkan nomor faktur resmi yang tertera pada dokumen supplier.',
                icon: 'warning'
            });
            $('#create_nomor_faktur_supplier').focus();
            return false;
        }
        if (noFakturSup.length > 100) {
            Swal.fire({
                title: 'Nomor Faktur Terlalu Panjang',
                text: 'Nomor faktur supplier maksimal 100 karakter (saat ini ' + noFakturSup.length + ' karakter).',
                icon: 'warning'
            });
            $('#create_nomor_faktur_supplier').focus();
            return false;
        }

        // Validasi Catatan / Keterangan (Maks 1000 Karakter)
        let keteranganCreate = ($('#create_keterangan').val() || '').trim();
        if (keteranganCreate.length > 1000) {
            Swal.fire({
                title: 'Keterangan Terlalu Panjang',
                text: 'Catatan / keterangan faktur maksimal 1000 karakter (saat ini ' + keteranganCreate.length + ' karakter).',
                icon: 'warning'
            });
            $('#create_keterangan').focus();
            return false;
        }

        // Validasi Pemilihan Dokumen Sumber (GRN vs Direct PO)
        let sourceType = $('input[name="source_type"]:checked').val() || 'gr';
        if (sourceType === 'gr' && !$('#create_gr_id').val()) {
            Swal.fire({
                title: 'Dokumen GRN Belum Dipilih',
                text: 'Silakan pilih dokumen Penerimaan Barang (GRN/BPB) terlebih dahulu.',
                icon: 'warning'
            });
            return false;
        }
        if (sourceType === 'po' && !$('#create_po_id').val()) {
            Swal.fire({
                title: 'Dokumen PO Belum Dipilih',
                text: 'Silakan pilih dokumen Purchase Order (PO) terlebih dahulu.',
                icon: 'warning'
            });
            return false;
        }
        if ($('#create_items_tbody tr.row-item-barang').length === 0) {
            Swal.fire({
                title: 'Item Barang Kosong',
                text: 'Faktur harus memiliki setidaknya satu rincian barang yang ditagihkan.',
                icon: 'warning'
            });
            return false;
        }

        // Validasi Uang Muka tidak boleh melebihi Grand Total
        let dpVal = unformatNumber($('#create_uang_muka').val());
        let currentTotal = (typeof window.currentCreateTotalInvoice !== 'undefined')
            ? window.currentCreateTotalInvoice
            : unformatNumber($('#create_sum_total_invoice').text());
        if (currentTotal > 0 && dpVal > currentTotal + 0.005) {
            Swal.fire({
                title: 'Validasi Uang Muka',
                text: 'Nominal Uang Muka (DP) sebesar ' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + ' tidak boleh melebihi Total Pembelian sebesar ' + formatMoney(currentTotal, createCurrencyCode, createCurrencySymbol) + '!',
                icon: 'warning'
            });
            $('#create_uang_muka').focus();
            return false;
        }

        // Validasi Kas / Bank Pengeluaran DP jika Uang Muka > 0
        if (dpVal > 0.005) {
            if (!$('#create_kas_bank_id').val()) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Kas / Bank Wajib Dipilih',
                    text: 'Karena terdapat Uang Muka (DP) sebesar ' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + ', silakan pilih Rekening Kas / Bank untuk pengeluaran uang muka.',
                    icon: 'warning'
                });
                $('#create_kas_bank_id').select2('open');
                return false;
            }

            let $selectedOpt = $('#create_kas_bank_id option:selected');
            let saldoKasBank = parseFloat($selectedOpt.data('saldo')) || 0;
            let namaRekening = $selectedOpt.text().trim() || 'Kas/Bank';
            let kurs = (typeof createKurs !== 'undefined' && createKurs > 0) ? createKurs : 1;
            let dpNominalIdr = Math.round(dpVal * kurs * 100) / 100;

            if (saldoKasBank < dpNominalIdr) {
                validateCreateKasBankDp();
                Swal.fire({
                    title: 'Saldo Kas / Bank Tidak Cukup!',
                    html: 'Saldo pada rekening <strong>' + escapeHtml(namaRekening) + '</strong> tidak mencukupi untuk pembayaran Uang Muka (DP).<br><br>' +
                          '<div class="text-start bg-light p-3 rounded border">' +
                          '• Saldo Kas/Bank saat ini: <strong class="text-danger">Rp ' + formatMoney(saldoKasBank, 'IDR', 'Rp') + '</strong><br>' +
                          '• Total DP yang harus dipotong: <strong class="text-primary">Rp ' + formatMoney(dpNominalIdr, 'IDR', 'Rp') + '</strong>' +
                          (createCurrencyCode !== 'IDR' ? ' (' + formatMoney(dpVal, createCurrencyCode, createCurrencySymbol) + ' @ kurs ' + formatMoney(kurs, 'IDR', 'Rp') + ')' : '') +
                          '</div><br>Silakan pilih rekening lain yang memiliki saldo mencukupi atau sesuaikan nominal DP.',
                    icon: 'error'
                });
                return false;
            }
        }

        let actionUrl = $(this).attr('action');
        let formData = new FormData(this);
        let actionType = $('#create_action_type').val() || 'draft';

        // Aktifkan loading spinner & disable tombol simpan untuk cegah duplikasi data
        setCreateButtonsLoading(true, actionType);

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
                setCreateButtonsLoading(false);
            }
        });
    });

    // Initialize Select2 for Uang Muka COA & Kas Bank immediately on document ready
    if (typeof window.initUangMukaCoaSelect2 === 'function') {
        window.initUangMukaCoaSelect2($('#create_uang_muka_coa_id'), $('#modalTambahInvoice'));
    }
    if (typeof window.initKasBankSelect2 === 'function') {
        window.initKasBankSelect2($('#create_kas_bank_id'), $('#modalTambahInvoice'));
    }

    // Reset and prepare Select2 when modal opens
    $('#modalTambahInvoice').on('show.bs.modal', function () {
        resetCreateInvoiceModal();
        if (typeof window.initTerminSelect2 === 'function') {
            window.initTerminSelect2($('#create_payment_term_id'), $('#modalTambahInvoice'));
        }
        if (typeof window.initUangMukaCoaSelect2 === 'function') {
            window.initUangMukaCoaSelect2($('#create_uang_muka_coa_id'), $('#modalTambahInvoice'));
        }
        if (typeof window.initKasBankSelect2 === 'function') {
            window.initKasBankSelect2($('#create_kas_bank_id'), $('#modalTambahInvoice'));
        }
    });

    // Ensure Select2 is correctly rendered & focused when modal animation completes
    $('#modalTambahInvoice').on('shown.bs.modal', function () {
        if (typeof window.initTerminSelect2 === 'function') {
            window.initTerminSelect2($('#create_payment_term_id'), $('#modalTambahInvoice'));
        }
        if (typeof window.initUangMukaCoaSelect2 === 'function') {
            window.initUangMukaCoaSelect2($('#create_uang_muka_coa_id'), $('#modalTambahInvoice'));
        }
        if (typeof window.initKasBankSelect2 === 'function') {
            window.initKasBankSelect2($('#create_kas_bank_id'), $('#modalTambahInvoice'));
        }
        if (typeof window.initCoaSelect2 === 'function') {
            window.initCoaSelect2($('#create_items_tbody .select-item-coa, #create_extra_costs_tbody .select-coa-extra'), $('#modalTambahInvoice'));
        }
    });

    // Reset loading state if modal is closed
    $('#modalTambahInvoice').on('hidden.bs.modal', function () {
        if (typeof setCreateButtonsLoading === 'function') {
            setCreateButtonsLoading(false);
        }
        $('#create_items_tbody .select-item-ppn, #create_items_tbody .select-item-coa, #create_extra_costs_tbody .select-coa-extra').each(function () {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
    });
});
</script>
