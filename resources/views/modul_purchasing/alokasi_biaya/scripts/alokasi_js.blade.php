<script>
$(document).ready(function () {
    let eligibleGrsData = [];
    let eligiblePosData = [];
    let currentPreviewItems = [];
    let selectedGrIdsSet = new Set();
    let grCurrentPage = 1;
    let grPageSize = 10; // Default 10 data per halaman
    let grSearchQuery = '';

    // ==========================================
    // SECURITY: XSS ESCAPING HELPER
    // ==========================================
    function escapeHtml(text) {
        if (!text && text !== 0) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==========================================
    // PRECISE INDONESIAN CURRENCY & NUMBER FORMATTER
    // Titik (.) untuk ribuan, Koma (,) untuk desimal
    // ==========================================
    function formatNominal(num, forceDecimals = true) {
        let val = parseFloat(num);
        if (isNaN(val)) return '0,00';

        let fixed = (Math.round(val * 100) / 100).toFixed(2);
        let parts = fixed.split('.');
        let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        let decPart = parts[1];

        return intPart + ',' + decPart;
    }

    function formatRupiah(num, forceDecimals = true) {
        return 'Rp ' + formatNominal(num, forceDecimals);
    }

    function formatNumber(num, decimals = 2) {
        let val = parseFloat(num);
        if (isNaN(val)) return '0';
        let parts = val.toFixed(decimals).split('.');
        let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        let decPart = parts[1];
        if (parseInt(decPart, 10) === 0) {
            return intPart;
        }
        return intPart + ',' + decPart.replace(/0+$/, '');
    }

    // ==========================================
    // LIVE RUPIAH INPUT MASK (TITIK . RIBUAN & KOMA , DESIMAL)
    // ==========================================
    function parseRupiahInput(valStr) {
        if (!valStr) return 0;
        let clean = valStr.toString().replace(/\./g, '').replace(/,/g, '.');
        return parseFloat(clean) || 0;
    }

    $('#create_total_biaya_display').on('input', function () {
        let rawVal = $(this).val().replace(/[^0-9,]/g, '');
        let parts = rawVal.split(',');
        let intPart = parts[0].replace(/^0+(?!$)/, '');
        if (intPart === '') intPart = '0';
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        let formatted = intPart;
        if (parts.length > 1) {
            formatted += ',' + parts[1].slice(0, 2);
        }

        $(this).val(formatted);
        $('#create_total_biaya').val(parseRupiahInput(formatted));
    });

    $('#create_total_biaya_display').on('blur', function () {
        let val = parseRupiahInput($(this).val());
        if (val > 0) {
            $(this).val(formatNominal(val, true));
        }
    });

    function setTotalBiayaValue(num) {
        let n = parseFloat(num) || 0;
        $('#create_total_biaya').val(n);
        $('#create_total_biaya_display').val(formatNominal(n, true));
    }

    // ==========================================
    // MODAL FILTER & QUICK DATE PRESETS
    // ==========================================
    $('#btnOpenFilterModal').on('click', function () {
        let currentSearch = $('#quickSearchInput').val();
        $('#filter_search_hidden').val(currentSearch);
    });

    $(document).on('click', '.btn-preset-date', function () {
        let preset = $(this).data('preset');
        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');

        let fmtDate = (d) => {
            let year = d.getFullYear();
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (preset === 'today') {
            let todayStr = `${yyyy}-${mm}-${dd}`;
            $('#filter_start_date').val(todayStr);
            $('#filter_end_date').val(todayStr);
        } else if (preset === 'this_month') {
            let firstDay = new Date(yyyy, today.getMonth(), 1);
            let lastDay = new Date(yyyy, today.getMonth() + 1, 0);
            $('#filter_start_date').val(fmtDate(firstDay));
            $('#filter_end_date').val(fmtDate(lastDay));
        } else if (preset === 'last_month') {
            let firstDay = new Date(yyyy, today.getMonth() - 1, 1);
            let lastDay = new Date(yyyy, today.getMonth(), 0);
            $('#filter_start_date').val(fmtDate(firstDay));
            $('#filter_end_date').val(fmtDate(lastDay));
        } else if (preset === 'clear') {
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
        }
    });

    // ==========================================
    // INITIALIZE SELECT2 WITH MODERN TEMPLATE
    // ==========================================
    $('#create_po_biaya_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalCreateAlokasi'),
        placeholder: '-- Pilih PO Jasa Ekspedisi --',
        allowClear: true,
        width: '100%',
        templateResult: formatPoOption,
        templateSelection: formatPoSelection
    });

    $('#create_tender_id').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalCreateAlokasi'),
        placeholder: '-- Tanpa Proyek / Tender --',
        allowClear: true,
        width: '100%'
    });

    function formatPoOption(po) {
        if (!po.id) return po.text;
        let dpp = $(po.element).data('dpp');
        let total = $(po.element).data('total');
        let pajak = $(po.element).data('pajak');
        let supplier = $(po.element).data('supplier');
        let jasa = $(po.element).data('jasa');
        let curr = $(po.element).data('currency') || 'IDR';
        let simbol = $(po.element).data('simbol') || 'Rp';
        let kurs = parseFloat($(po.element).data('kurs')) || 1;
        let dppValas = parseFloat($(po.element).data('dpp-valas')) || 0;
        let pphNominal = parseFloat($(po.element).data('pph-nominal')) || 0;
        let namaPph = $(po.element).data('nama-pph') || 'PPh';
        let noPo = $(po.element).text().split(' - ')[0];

        let formattedDpp = dpp ? formatRupiah(dpp) : (total ? formatRupiah(total) : '');

        let currBadge = curr !== 'IDR'
            ? `<span class="badge font-monospace fw-bold" style="background-color: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); font-size: 0.65rem;">${curr}</span>`
            : '';

        let valasInfo = (curr !== 'IDR' && dppValas > 0)
            ? `<div class="font-monospace text-primary mt-0.5" style="font-size: 0.72rem;">${simbol} ${formatNominal(dppValas, true)} &times; ${formatNominal(kurs, true)}</div>`
            : '';

        return $(`
            <div class="d-flex justify-content-between align-items-center py-1">
                <div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="badge font-monospace fw-bold" style="background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; font-size: 0.68rem; border: 1px solid rgba(59, 130, 246, 0.3);">JASA</span>
                        ${currBadge}
                        <span class="fw-bold font-monospace text-primary">${escapeHtml(noPo)}</span>
                    </div>
                    <div class="small mt-0.5" style="color: var(--erp-text-muted);">
                        <span class="fw-semibold" style="color: var(--erp-text-main);">${escapeHtml(supplier || '-')}</span>
                        ${jasa ? ` &bull; <i class="bi bi-tag text-info me-0.5"></i><span class="text-info">${escapeHtml(jasa)}</span>` : ''}
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success-subtle text-success font-monospace fw-bold">DPP Masuk HPP: ${formattedDpp}</span>
                    ${valasInfo}
                    ${pajak > 0 ? `<div class="font-monospace text-warning mt-0.5" style="font-size: 0.7rem;">+ PPN ${formatRupiah(pajak)}</div>` : ''}
                    ${pphNominal > 0 ? `<div class="font-monospace mt-0.5" style="font-size: 0.7rem; color: #d97706;" title="Potongan PPh tidak dihitung ke HPP">- ${escapeHtml(namaPph)} ${formatRupiah(pphNominal)} (Tidak Masuk HPP)</div>` : ''}
                </div>
            </div>
        `);
    }

    function formatPoSelection(po) {
        if (!po.id) return po.text;
        let noPo = $(po.element).text().split(' - ')[0];
        let supplier = $(po.element).data('supplier') || '';
        let jasa = $(po.element).data('jasa');
        let curr = $(po.element).data('currency') || 'IDR';
        let dpp = $(po.element).data('dpp');
        let total = $(po.element).data('total');
        let dppStr = dpp ? ` (DPP IDR: ${formatRupiah(dpp)})` : (total ? ` (${formatRupiah(total)})` : '');
        let jasaStr = jasa ? ` - ${jasa}` : '';
        let currTag = curr !== 'IDR' ? `[${curr}] ` : '';
        return `[JASA] ${currTag}${noPo} - ${supplier}${jasaStr}${dppStr}`;
    }

    // ==========================================
    // OPEN CREATE MODAL
    // ==========================================
    $('#btnTambahAlokasi').on('click', function () {
        $('#formCreateAlokasi')[0].reset();
        $('#create_alokasi_id').val('');
        $('#modalCreateAlokasiLabel').html('Buat Alokasi Biaya Pengiriman (Landed Cost)');
        $('#btnSimpanDraft').html('<i class="bi bi-pencil-square me-1"></i> Simpan Draft');
        $('#create_tender_id').val('').trigger('change');
        $('#create_tanggal_alokasi').val(new Date().toISOString().split('T')[0]);
        setTotalBiayaValue(0);
        $('#create_total_biaya_display').val('');
        $('#create_keterangan').val('');
        $('#keterangan_char_count').text('0/40 karakter').removeClass('text-danger fw-bold').addClass('text-muted');
        $('#po_selected_info').html('');
        currentPreviewItems = [];
        selectedGrIdsSet.clear();
        grCurrentPage = 1;
        grPageSize = 10;
        grSearchQuery = '';
        $('#grPageSizeSelect').val('10');
        $('#searchGrInput').val('');
        $('#btnClearSearchGr').hide();
        $('#realtimeCalcIndicator').hide();
        $('#tbodyPreviewAlokasi').html(`
            <tr>
                <td colspan="10" class="text-center py-5" style="color: var(--erp-text-muted);">
                    <i class="bi bi-info-circle fs-4 d-block mb-2 text-warning opacity-75"></i>
                    Centang GR di atas untuk melihat hasil simulasi kalkulasi per unit barang secara otomatis (real-time).
                </td>
            </tr>
        `);
        $('#tfootPreviewAlokasi').hide();

        $('#modalCreateAlokasi').modal('show');

        loadEligiblePos();
        loadEligibleGrs();
    });

    // ==========================================
    // LOAD ELIGIBLE POS (KHUSUS KATEGORI JASA / EKSPEDISI)
    // ==========================================
    function loadEligiblePos(alokasiId = null, selectedPoId = null) {
        $('#create_po_biaya_id').html('<option value="">-- Memuat daftar PO Jasa... --</option>');
        $.ajax({
            url: "{{ route('alokasi-biaya.eligible-pos') }}",
            type: "GET",
            data: alokasiId ? { alokasi_id: alokasiId } : {},
            dataType: "json",
            success: function (res) {
                if (res.success && res.data) {
                    eligiblePosData = res.data;
                    if (res.data.length === 0) {
                        $('#create_po_biaya_id').html('<option value="">-- Tidak ada PO Kategori Jasa Ekspedisi --</option>').trigger('change');
                        return;
                    }
                    let options = '<option value="">-- Pilih PO Jasa Ekspedisi --</option>';
                    res.data.forEach(function (po) {
                        let dppDisplay = formatRupiah(po.dpp || po.grand_total);
                        options += `<option value="${po.id}" 
                            data-dpp="${po.dpp}" 
                            data-total="${po.grand_total}" 
                            data-pajak="${po.total_pajak}" 
                            data-pph-nominal="${po.pph_nominal || 0}" 
                            data-tarif-pph="${po.tarif_pph || 0}" 
                            data-nama-pph="${escapeHtml(po.nama_pph || 'PPh')}" 
                            data-biaya-lain="${po.biaya_lain}" 
                            data-supplier="${po.supplier_nama}" 
                            data-jasa="${po.nama_jasa || ''}" 
                            data-currency="${po.currency_kode || 'IDR'}" 
                            data-simbol="${po.currency_simbol || 'Rp'}" 
                            data-kurs="${po.kurs || 1}" 
                            data-dpp-valas="${po.dpp_valas || 0}" 
                            data-tender-id="${po.tender_id || ''}" 
                            data-tender-name="${po.nomor_tender || ''}">
                            ${po.nomor_po} - ${po.supplier_nama} (DPP: ${dppDisplay})
                        </option>`;
                    });
                    $('#create_po_biaya_id').html(options);
                    if (selectedPoId) {
                        $('#create_po_biaya_id').val(selectedPoId).trigger('change');
                    } else {
                        $('#create_po_biaya_id').trigger('change');
                    }
                }
            },
            error: function () {
                $('#create_po_biaya_id').html('<option value="">Gagal memuat daftar PO</option>');
            }
        });
    }

    // ON PO SELECTED
    $('#create_po_biaya_id').on('change', function () {
        let poId = $(this).val();
        if (!poId) {
            $('#po_selected_info').html('');
            setTotalBiayaValue(0);
            scheduleRealtimeCalculate(50);
            return;
        }

        let selected = eligiblePosData.find(p => p.id == poId);
        if (selected) {
            let valasBadge = (selected.currency_kode && selected.currency_kode !== 'IDR')
                ? `<span class="badge font-monospace" style="background-color: rgba(168, 85, 247, 0.15); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3);"><i class="bi bi-globe me-1"></i>Valas: ${selected.currency_kode} ${selected.currency_simbol} ${formatNominal(selected.dpp_valas, true)} @ Kurs Rp ${formatNominal(selected.kurs, true)}</span>`
                : '';

            let pphBadge = (selected.pph_nominal && selected.pph_nominal > 0)
                ? `<span class="badge font-monospace" style="background-color: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25);" title="Potongan PPh (${escapeHtml(selected.nama_pph || 'PPh')}) tidak dimasukkan ke HPP persediaan"><i class="bi bi-percent me-1"></i>${escapeHtml(selected.nama_pph || 'PPh')}: -${formatRupiah(selected.pph_nominal)} (Tidak Masuk HPP)</span>`
                : '';

            let info = `
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-1.5">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-person me-1"></i>${escapeHtml(selected.supplier_nama)}</span>
                    ${valasBadge}
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" title="Murni nilai DPP Jasa Ekspedisi yang dialokasikan ke HPP persediaan"><i class="bi bi-wallet2 me-1"></i>DPP Masuk HPP: ${formatRupiah(selected.dpp)}</span>
                    ${selected.total_pajak > 0 ? `<span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace" title="PPN Masukan dapat dikreditkan, tidak dihitung ke HPP"><i class="bi bi-receipt me-1"></i>PPN: ${formatRupiah(selected.total_pajak)} (Pajak Masukan - Tidak Masuk HPP)</span>` : ''}
                    ${pphBadge}
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace" title="Total Tagihan Bersih PO = DPP + PPN - PPh">Tagihan PO: ${formatRupiah(selected.grand_total)}</span>
                    ${selected.nomor_tender ? `<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-briefcase me-1"></i>${escapeHtml(selected.nomor_tender)}</span>` : ''}
                </div>
            `;
            $('#po_selected_info').html(info);

            // Auto-select Tender jika PO memiliki tender
            if (selected.tender_id) {
                $('#create_tender_id').val(selected.tender_id).trigger('change');
            }

            // Auto-fill total biaya: Nilai terkunci otomatis SESUAI DPP PO Jasa (Murni Tanpa PPN & Tanpa PPh)
            let defaultCost = selected.dpp > 0 ? selected.dpp : (selected.subtotal > 0 ? selected.subtotal : 0);
            setTotalBiayaValue(defaultCost);
            scheduleRealtimeCalculate(100);
        }
    });

    // Real-time recalculation saat Metode Alokasi Biaya diubah
    $('#create_metode_alokasi').on('change', function () {
        scheduleRealtimeCalculate(50);
    });

    // Character Counter Keterangan (Maks 40 Karakter)
    $('#create_keterangan').on('input', function () {
        let len = $(this).val().length;
        $('#keterangan_char_count').text(`${len}/40 karakter`);
        if (len >= 40) {
            $('#keterangan_char_count').addClass('text-danger fw-bold').removeClass('text-muted');
        } else {
            $('#keterangan_char_count').removeClass('text-danger fw-bold').addClass('text-muted');
        }
    });

    // ==========================================
    // LOAD ELIGIBLE GRS & PAGINASI + FILTER PENCARIAN
    // ==========================================
    function loadEligibleGrs(selectedGrIds = null, callback = null) {
        if (selectedGrIds && Array.isArray(selectedGrIds)) {
            selectedGrIdsSet.clear();
            selectedGrIds.forEach(id => selectedGrIdsSet.add(parseInt(id, 10)));
        }

        $('#tbodyEligibleGr').html(`
            <tr>
                <td colspan="9" class="text-center py-4" style="color: var(--erp-text-muted);">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    Memuat data penerimaan barang...
                </td>
            </tr>
        `);

        let ajaxData = {};
        if (selectedGrIds && selectedGrIds.length > 0) {
            ajaxData.selected_gr_ids = selectedGrIds;
        } else if (selectedGrIdsSet.size > 0) {
            ajaxData.selected_gr_ids = Array.from(selectedGrIdsSet);
        }

        $.ajax({
            url: "{{ route('alokasi-biaya.eligible-grs') }}",
            type: "GET",
            data: ajaxData,
            dataType: "json",
            success: function (res) {
                if (res.success && res.data) {
                    eligibleGrsData = res.data;
                    renderEligibleGrsTable();
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            },
            error: function () {
                $('#tbodyEligibleGr').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            Gagal memuat data Penerimaan Barang. Silakan coba lagi.
                        </td>
                    </tr>
                `);
                $('#grPaginationInfo').text('Gagal memuat data');
                $('#grPaginationList').html('');
            }
        });
    }

    $('#btnRefreshGr').on('click', function () {
        loadEligibleGrs();
    });

    // Helper untuk menyaring GR sesuai query pencarian
    function getFilteredGrs() {
        let q = (grSearchQuery || '').trim().toLowerCase();
        if (!q) {
            return eligibleGrsData;
        }
        return eligibleGrsData.filter(function (gr) {
            let noGr = (gr.nomor_gr || '').toLowerCase();
            let tgl = (gr.tanggal_gr || '').toLowerCase();
            let noPo = (gr.nomor_po || '').toLowerCase();
            let supplier = (gr.supplier_nama || '').toLowerCase();
            let tender = (gr.nomor_tender || '').toLowerCase();
            let gudang = (gr.nama_gudang || '').toLowerCase();
            let curr = (gr.currency_kode || '').toLowerCase();
            return noGr.includes(q) || tgl.includes(q) || noPo.includes(q) || supplier.includes(q) || tender.includes(q) || gudang.includes(q) || curr.includes(q);
        });
    }

    function renderEligibleGrsTable() {
        let filtered = getFilteredGrs();
        let totalFiltered = filtered.length;

        if (totalFiltered === 0) {
            if (eligibleGrsData.length === 0) {
                $('#tbodyEligibleGr').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color: var(--erp-text-muted);">
                            Tidak ada Penerimaan Barang (Posted) yang tersedia.
                        </td>
                    </tr>
                `);
            } else {
                $('#tbodyEligibleGr').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color: var(--erp-text-muted);">
                            <i class="bi bi-search me-1 text-warning"></i> Tidak ada data GR yang cocok dengan kata kunci "<b>${escapeHtml(grSearchQuery)}</b>".
                        </td>
                    </tr>
                `);
            }
            $('#grPaginationInfo').text('Menampilkan 0 data');
            $('#grPaginationList').html('');
            updateSelectedGrCount();
            $('#checkAllGr').prop('checked', false).prop('disabled', true);
            return;
        }

        $('#checkAllGr').prop('disabled', false);

        // Hitung paginasi (Default tampil 10)
        let pageSize = (grPageSize === 'all') ? totalFiltered : parseInt(grPageSize, 10);
        let totalPages = (grPageSize === 'all') ? 1 : Math.ceil(totalFiltered / pageSize);
        if (grCurrentPage > totalPages) grCurrentPage = totalPages;
        if (grCurrentPage < 1) grCurrentPage = 1;

        let startIndex = (grPageSize === 'all') ? 0 : (grCurrentPage - 1) * pageSize;
        let endIndex = (grPageSize === 'all') ? totalFiltered : Math.min(startIndex + pageSize, totalFiltered);
        let pageData = filtered.slice(startIndex, endIndex);

        let html = '';
        let visibleIds = [];
        pageData.forEach(function (gr) {
            let grId = parseInt(gr.id, 10);
            visibleIds.push(grId);
            let isChecked = selectedGrIdsSet.has(grId) ? 'checked' : '';
            let tenderBadge = gr.nomor_tender
                ? `<span class="badge bg-info-subtle text-info border border-info-subtle small"><i class="bi bi-briefcase me-1"></i>${escapeHtml(gr.nomor_tender)}</span>`
                : `<span class="text-muted small">-</span>`;

            let valasGrBadge = (gr.currency_kode && gr.currency_kode !== 'IDR')
                ? `<div class="mt-0.5"><span class="badge small font-monospace" style="font-size: 0.65rem; background-color: rgba(168, 85, 247, 0.12); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.25);">${escapeHtml(gr.currency_kode)} @ Rp ${formatNominal(gr.kurs, true)}</span></div>`
                : '';

            html += `
                <tr>
                    <td class="text-center text-nowrap">
                        <input type="checkbox" class="form-check-input check-gr-item" value="${gr.id}" ${isChecked} style="cursor: pointer;">
                    </td>
                    <td class="fw-bold text-primary font-monospace text-nowrap">${escapeHtml(gr.nomor_gr)}</td>
                    <td class="text-nowrap" style="color: var(--erp-text-main);">${escapeHtml(gr.tanggal_gr)}</td>
                    <td class="text-nowrap">
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace">${escapeHtml(gr.nomor_po)}</span>
                        ${valasGrBadge}
                    </td>
                    <td class="fw-semibold text-nowrap" style="color: var(--erp-text-main);">${escapeHtml(gr.supplier_nama)}</td>
                    <td class="text-nowrap">${tenderBadge}</td>
                    <td class="text-nowrap"><span class="badge bg-info-subtle text-info border border-info-subtle">${escapeHtml(gr.nama_gudang)}</span></td>
                    <td class="text-end fw-semibold font-monospace text-nowrap" style="color: var(--erp-text-main);">${formatNumber(gr.total_qty, 2)}</td>
                    <td class="text-end pe-3 fw-bold text-success font-monospace text-nowrap">${formatRupiah(gr.total_nilai)}</td>
                </tr>
            `;
        });

        $('#tbodyEligibleGr').html(html);

        // Update Info Paginasi
        let infoText = `Menampilkan <b>${startIndex + 1}</b> - <b>${endIndex}</b> dari <b>${totalFiltered}</b> GR`;
        if (grSearchQuery) {
            infoText += ` (difilter dari ${eligibleGrsData.length} GR)`;
        }
        $('#grPaginationInfo').html(infoText);

        // Render Tombol Halaman
        renderGrPaginationButtons(totalPages);

        // Update status Check All untuk halaman yang sedang aktif
        let allPageChecked = visibleIds.length > 0 && visibleIds.every(id => selectedGrIdsSet.has(id));
        $('#checkAllGr').prop('checked', allPageChecked);

        updateSelectedGrCount();
    }

    // Helper render pagination buttons
    function renderGrPaginationButtons(totalPages) {
        if (totalPages <= 1) {
            $('#grPaginationList').html('');
            return;
        }

        let html = '';
        // Previous button
        html += `
            <li class="page-item ${grCurrentPage === 1 ? 'disabled' : ''}">
                <button class="page-link py-1 px-2.5 gr-page-btn" data-page="${grCurrentPage - 1}" aria-label="Sebelumnya" ${grCurrentPage === 1 ? 'tabindex="-1"' : ''}>
                    <i class="bi bi-chevron-left" style="font-size: 0.75rem;"></i>
                </button>
            </li>
        `;

        let startPage = Math.max(1, grCurrentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            html += `<li class="page-item"><button class="page-link py-1 px-2.5 gr-page-btn" data-page="1">1</button></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link py-1 px-2">...</span></li>`;
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            let activeClass = (p === grCurrentPage) ? 'active fw-bold' : '';
            html += `<li class="page-item ${activeClass}"><button class="page-link py-1 px-2.5 gr-page-btn" data-page="${p}">${p}</button></li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link py-1 px-2">...</span></li>`;
            }
            html += `<li class="page-item"><button class="page-link py-1 px-2.5 gr-page-btn" data-page="${totalPages}">${totalPages}</button></li>`;
        }

        // Next button
        html += `
            <li class="page-item ${grCurrentPage === totalPages ? 'disabled' : ''}">
                <button class="page-link py-1 px-2.5 gr-page-btn" data-page="${grCurrentPage + 1}" aria-label="Selanjutnya" ${grCurrentPage === totalPages ? 'tabindex="-1"' : ''}>
                    <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
                </button>
            </li>
        `;

        $('#grPaginationList').html(html);
    }

    // Event Klik Paginasi
    $(document).on('click', '.gr-page-btn', function (e) {
        e.preventDefault();
        let targetPage = parseInt($(this).data('page'), 10);
        if (!isNaN(targetPage) && targetPage !== grCurrentPage) {
            grCurrentPage = targetPage;
            renderEligibleGrsTable();
        }
    });

    // Event Ganti Page Size (Default 10)
    $('#grPageSizeSelect').on('change', function () {
        grPageSize = $(this).val();
        grCurrentPage = 1;
        renderEligibleGrsTable();
    });

    // Event Input Pencarian Cepat (dengan debounce)
    let grSearchDebounce;
    $('#searchGrInput').on('input', function () {
        let val = $(this).val();
        grSearchQuery = val;
        if (val.trim()) {
            $('#btnClearSearchGr').show();
        } else {
            $('#btnClearSearchGr').hide();
        }
        clearTimeout(grSearchDebounce);
        grSearchDebounce = setTimeout(function () {
            grCurrentPage = 1;
            renderEligibleGrsTable();
        }, 150);
    });

    // Event Tombol Clear Search
    $('#btnClearSearchGr').on('click', function () {
        $('#searchGrInput').val('');
        grSearchQuery = '';
        $(this).hide();
        grCurrentPage = 1;
        renderEligibleGrsTable();
        $('#searchGrInput').focus();
    });

    // CHECK ALL GRS (Hanya untuk halaman yang sedang aktif)
    $('#checkAllGr').on('change', function () {
        let isChecked = $(this).is(':checked');
        $('.check-gr-item').each(function () {
            let id = parseInt($(this).val(), 10);
            $(this).prop('checked', isChecked);
            if (isChecked) {
                selectedGrIdsSet.add(id);
            } else {
                selectedGrIdsSet.delete(id);
            }
        });
        updateSelectedGrCount();
        scheduleRealtimeCalculate(150);
    });

    // CHECK ITEM PER ROW (Preserve di Set global)
    $(document).on('change', '.check-gr-item', function () {
        let id = parseInt($(this).val(), 10);
        if ($(this).is(':checked')) {
            selectedGrIdsSet.add(id);
        } else {
            selectedGrIdsSet.delete(id);
        }

        let allPageChecked = $('.check-gr-item').length > 0 && $('.check-gr-item:not(:checked)').length === 0;
        $('#checkAllGr').prop('checked', allPageChecked);
        updateSelectedGrCount();

        // Jika Tender belum dipilih dan GR ini punya tender, otomatis pilih tendernya
        if (!($('#create_tender_id').val())) {
            let grObj = eligibleGrsData.find(g => g.id == id);
            if (grObj && grObj.tender_id) {
                $('#create_tender_id').val(grObj.tender_id).trigger('change');
            }
        }

        scheduleRealtimeCalculate(150);
    });

    function updateSelectedGrCount() {
        let count = selectedGrIdsSet.size;
        $('#selectedGrCountInfo').html(`<b>${count}</b> GR terpilih`);
    }

    // ==========================================
    // REAL-TIME KALKULASI ALOKASI BIAYA & SIMULASI PREVIEW
    // ==========================================
    let realtimeCalcDebounceTimer = null;
    let currentCalcXhr = null;

    function scheduleRealtimeCalculate(delay = 150) {
        clearTimeout(realtimeCalcDebounceTimer);
        realtimeCalcDebounceTimer = setTimeout(function () {
            triggerRealtimeCalculate(false);
        }, delay);
    }

    function triggerRealtimeCalculate(isManualClick = false) {
        let totalBiaya = parseRupiahInput($('#create_total_biaya_display').val());
        $('#create_total_biaya').val(totalBiaya);

        let metode = $('#create_metode_alokasi').val();
        let selectedGrIds = Array.from(selectedGrIdsSet);

        // Validasi jika diklik manual
        if (isManualClick) {
            if (totalBiaya <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Total Biaya Belum Diisi',
                    text: 'Pilih PO Jasa Ekspedisi / Pengiriman terlebih dahulu (total biaya harus lebih dari 0).'
                });
                $('#create_po_biaya_id').focus();
                return;
            }

            if (selectedGrIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada GR yang Dipilih',
                    text: 'Centang minimal satu Penerimaan Barang (Goods Receipt) yang ingin dialokasikan biaya kirimnya.'
                });
                return;
            }
        }

        // Jika belum ada GR yang dipilih
        if (selectedGrIds.length === 0) {
            currentPreviewItems = [];
            $('#realtimeCalcIndicator').hide();
            $('#tbodyPreviewAlokasi').html(`
                <tr>
                    <td colspan="10" class="text-center py-5" style="color: var(--erp-text-muted);">
                        <i class="bi bi-info-circle fs-4 d-block mb-2 text-warning opacity-75"></i>
                        Centang GR di atas untuk melihat hasil simulasi kalkulasi per unit barang secara otomatis (real-time).
                    </td>
                </tr>
            `);
            $('#tfootPreviewAlokasi').hide();
            return;
        }

        // Jika GR sudah dipilih tapi PO belum dipilih (total biaya <= 0)
        if (totalBiaya <= 0) {
            currentPreviewItems = [];
            $('#realtimeCalcIndicator').hide();
            $('#tbodyPreviewAlokasi').html(`
                <tr>
                    <td colspan="10" class="text-center py-5" style="color: var(--erp-text-muted);">
                        <i class="bi bi-truck fs-4 d-block mb-2 text-info opacity-75"></i>
                        Pilih <b>PO Jasa Ekspedisi</b> pada Step 1 di atas untuk mengalokasikan biaya ke <b>${selectedGrIds.length} GR</b> yang telah dipilih.
                    </td>
                </tr>
            `);
            $('#tfootPreviewAlokasi').hide();
            return;
        }

        // Syarat terpenuhi -> Jalankan kalkulasi real-time
        $('#realtimeCalcIndicator').show();

        let $btn = $('#btnHitungPreview');
        let origBtnHtml = '<i class="bi bi-cpu-fill me-1.5"></i> Hitung Ulang Alokasi HPP';
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5"></span> Mengkalkulasi Real-time...');

        // Jika tabel sebelumnya kosong, tampilkan spinner
        if (!currentPreviewItems || currentPreviewItems.length === 0) {
            $('#tbodyPreviewAlokasi').html(`
                <tr>
                    <td colspan="10" class="text-center py-4" style="color: var(--erp-text-muted);">
                        <div class="spinner-border spinner-border-sm text-warning me-2"></div>
                        Mengkalkulasi alokasi HPP secara real-time...
                    </td>
                </tr>
            `);
        } else {
            $('#tbodyPreviewAlokasi').css('opacity', '0.5');
        }

        // Batalkan AJAX kalkulasi sebelumnya jika masih jalan
        if (currentCalcXhr && currentCalcXhr.readyState !== 4) {
            currentCalcXhr.abort();
        }

        currentCalcXhr = $.ajax({
            url: "{{ route('alokasi-biaya.calculate-preview') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                total_biaya: totalBiaya,
                metode_alokasi: metode,
                gr_ids: selectedGrIds
            },
            dataType: "json",
            success: function (res) {
                $('#tbodyPreviewAlokasi').css('opacity', '1');
                $btn.prop('disabled', false).html(origBtnHtml);
                if (res.success && res.items) {
                    currentPreviewItems = res.items;
                    renderPreviewTable(res.items);
                }
            },
            error: function (xhr, status) {
                $('#tbodyPreviewAlokasi').css('opacity', '1');
                $btn.prop('disabled', false).html(origBtnHtml);
                if (status === 'abort') {
                    return; // Request dibatalkan karena ada kalkulasi baru
                }
                if (isManualClick) {
                    let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghitung simulasi alokasi.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghitung',
                        text: msg
                    });
                }
            }
        });
    }

    // Tombol Hitung Ulang Manual
    $('#btnHitungPreview').on('click', function () {
        triggerRealtimeCalculate(true);
    });

    function renderPreviewTable(items) {
        if (!items || items.length === 0) {
            $('#tbodyPreviewAlokasi').html(`
                <tr>
                    <td colspan="10" class="text-center py-4" style="color: var(--erp-text-muted);">
                        Tidak ada item barang yang dapat dialokasikan.
                    </td>
                </tr>
            `);
            $('#tfootPreviewAlokasi').hide();
            return;
        }

        let html = '';
        let totQty = 0;
        let totNilai = 0;
        let totBiaya = 0;

        items.forEach(function (item, idx) {
            totQty += parseFloat(item.qty) || 0;
            totNilai += parseFloat(item.nilai_barang) || 0;
            totBiaya += parseFloat(item.biaya_dialokasikan) || 0;

            html += `
                <tr>
                    <td class="text-center small font-monospace text-nowrap" style="color: var(--erp-text-muted);">${idx + 1}</td>
                    <td class="font-monospace small fw-bold text-primary text-nowrap">${item.nomor_gr}</td>
                    <td class="text-nowrap" style="min-width: 220px;">
                        <span class="fw-bold d-block text-truncate" style="color: var(--erp-text-main); font-size: 0.88rem; max-width: 260px;" title="${escapeHtml(item.nama_barang)}">${escapeHtml(item.nama_barang)}</span>
                        <span class="small font-monospace text-muted">${escapeHtml(item.kode_barang)}</span>
                    </td>
                    <td class="text-end fw-semibold font-monospace text-nowrap" style="color: var(--erp-text-main);">${formatNumber(item.qty, 2)} <small class="text-muted">${item.satuan_nama || ''}</small></td>
                    <td class="text-end font-monospace text-nowrap" style="color: var(--erp-text-main);">${formatRupiah(item.nilai_barang)}</td>
                    <td class="text-end fw-bold font-monospace text-info text-nowrap">${formatNominal(item.porsi_persen, true)}%</td>
                    <td class="text-end font-monospace fw-bold text-nowrap" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">${formatRupiah(item.biaya_dialokasikan)}</td>
                    <td class="text-end font-monospace small text-nowrap" style="background: rgba(245, 158, 11, 0.08); color: var(--erp-text-muted);">${formatRupiah(item.biaya_per_unit, true)}</td>
                    <td class="text-end font-monospace small text-nowrap" style="background: rgba(16, 185, 129, 0.08); color: var(--erp-text-muted);">${formatRupiah(item.hpp_awal, true)}</td>
                    <td class="text-end font-monospace fw-bold text-nowrap" style="background: rgba(16, 185, 129, 0.18); color: #10b981; font-size: 0.92rem;">${formatRupiah(item.hpp_baru, true)}</td>
                </tr>
            `;
        });

        $('#tbodyPreviewAlokasi').html(html);
        $('#footTotalQty').text(formatNumber(totQty, 2));
        $('#footTotalNilai').text(formatRupiah(totNilai));
        $('#footTotalBiaya').text(formatRupiah(totBiaya));
        $('#tfootPreviewAlokasi').show();
    }

    // ==========================================
    // SIMPAN ACTION (DRAFT OR POST)
    // ==========================================
    function submitAlokasi(isDraft) {
        let editId = $('#create_alokasi_id').val();
        let targetUrl = editId 
            ? "{{ route('alokasi-biaya.update', ':id') }}".replace(':id', editId)
            : "{{ route('alokasi-biaya.store') }}";

        let poBiayaId = $('#create_po_biaya_id').val();
        let tenderId = $('#create_tender_id').val() || null;
        let totalBiaya = parseRupiahInput($('#create_total_biaya_display').val());
        $('#create_total_biaya').val(totalBiaya);

        let tanggalAlokasi = $('#create_tanggal_alokasi').val();
        let metodeAlokasi = $('#create_metode_alokasi').val();
        let keterangan = $('#create_keterangan').val();

        if (!poBiayaId) {
            Swal.fire({ icon: 'warning', title: 'PO Jasa Belum Dipilih', text: 'Pilih PO Jasa Ekspedisi / Pengiriman terlebih dahulu.' });
            return;
        }

        if (totalBiaya <= 0) {
            Swal.fire({ icon: 'warning', title: 'Total Biaya Kosong', text: 'Total biaya pengiriman harus lebih dari 0.' });
            return;
        }

        if (!currentPreviewItems || currentPreviewItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Simulasi Belum Dihitung',
                text: 'Harap klik tombol "Hitung & Simulasikan Alokasi HPP" sebelum menyimpan.'
            });
            return;
        }

        let confirmMsg = isDraft
            ? (editId ? 'Perubahan dokumen alokasi biaya akan disimpan sebagai Draft.' : 'Dokumen akan disimpan sebagai Draft dan belum mempengaruhi stok persediaan maupun jurnal.')
            : 'Dokumen akan langsung di-POSTING: Nilai HPP persediaan stok gudang akan bertambah dan jurnal akuntansi otomatis dicatat. Lanjutkan?';

        let confirmTitle = isDraft 
            ? (editId ? 'Simpan Perubahan Draft?' : 'Simpan sebagai Draft?') 
            : 'Simpan & Posting ke HPP?';

        let confirmBtnText = isDraft 
            ? (editId ? 'Ya, Simpan Perubahan' : 'Ya, Simpan Draft') 
            : 'Ya, Posting Sekarang!';

        Swal.fire({
            title: confirmTitle,
            text: confirmMsg,
            icon: isDraft ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isDraft ? '#0d6efd' : '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menyimpan alokasi biaya...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let payload = {
                    _token: "{{ csrf_token() }}",
                    is_draft: isDraft ? 1 : 0,
                    po_biaya_id: poBiayaId,
                    tender_id: tenderId,
                    total_biaya: totalBiaya,
                    tanggal_alokasi: tanggalAlokasi,
                    metode_alokasi: metodeAlokasi,
                    keterangan: keterangan,
                    items: currentPreviewItems
                };
                if (editId) {
                    payload._method = 'PUT';
                }

                $.ajax({
                    url: targetUrl,
                    type: "POST",
                    data: payload,
                    dataType: "json",
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Alokasi biaya berhasil disimpan.'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan alokasi.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: msg
                        });
                    }
                });
            }
        });
    }

    $('#btnSimpanDraft').on('click', function () {
        submitAlokasi(true);
    });

    $('#btnSimpanPost').on('click', function () {
        submitAlokasi(false);
    });

    // ==========================================
    // VIEW DETAIL MODAL
    // ==========================================
    $(document).on('click', '.btn-view', function () {
        let id = $(this).data('id');
        let url = "{{ route('alokasi-biaya.show', ':id') }}".replace(':id', id);

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success && res.data) {
                    let d = res.data;
                    $('#viewNomorAlokasi').text(d.nomor_alokasi);
                    $('#viewSubTitle').text(`Nomor Alokasi: ${d.nomor_alokasi}`);
                    $('#viewTanggalAlokasi').text(d.tanggal_alokasi ? new Date(d.tanggal_alokasi).toLocaleDateString('id-ID') : '-');

                    if (d.status === 'Posted') {
                        let canViewJurnal = {{ auth()->user() && auth()->user()->can('lihat-jurnal-alokasi-biaya') ? 'true' : 'false' }};
                        let canCancel = {{ auth()->user() && auth()->user()->can('batalkan-alokasi-biaya') ? 'true' : 'false' }};

                        let alertBtn = canViewJurnal ? `
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 shadow-sm fw-semibold d-inline-flex align-items-center gap-1.5 btn-open-jurnal" data-id="${d.id}">
                                <i class="bi bi-journal-text"></i> Lihat Jurnal
                            </button>
                        ` : '';

                        $('#viewStatusBadge').html('<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Posted</span>');
                        $('#viewJurnalAlert').html(`
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                                <div class="small">Dokumen ini <b>Posted</b>: Nilai persediaan di modul stok gudang telah diperbarui otomatis dan jurnal kapitalisasi HPP telah dicatat ke buku besar akuntansi.</div>
                            </div>
                            ${alertBtn}
                        `).removeClass('alert-danger alert-warning').addClass('alert-info').show();

                        let footerActions = '';
                        if (canViewJurnal) {
                            footerActions += `
                                <button type="button" class="btn btn-primary px-3.5 py-2 rounded-pill fw-semibold shadow-sm btn-open-jurnal" data-id="${d.id}">
                                    <i class="bi bi-journal-text me-1.5"></i> Lihat Jurnal Akuntansi
                                </button>
                            `;
                        }
                        if (canCancel) {
                            footerActions += `
                                <button type="button" class="btn btn-outline-danger px-3.5 py-2 rounded-pill fw-semibold btn-modal-cancel ms-2" data-id="${d.id}" data-nomor="${d.nomor_alokasi}" data-status="Posted">
                                    <i class="bi bi-slash-circle me-1"></i> Batalkan / Void Dokumen
                                </button>
                            `;
                        }
                        $('#viewActionContainer').html(footerActions);
                    } else if (d.status === 'Batal') {
                        $('#viewStatusBadge').html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Batal</span>');
                        $('#viewJurnalAlert').html('<i class="bi bi-x-circle-fill fs-5 text-danger"></i><div class="small text-danger">Dokumen ini telah <b>Dibatalkan</b> dan tidak mempengaruhi persediaan stok maupun jurnal akuntansi.</div>').removeClass('alert-info alert-warning').addClass('alert-danger').show();
                        $('#viewActionContainer').html('');
                        if (d.alasan_batal) {
                            $('#viewAlasanBatal').text(d.alasan_batal);
                            $('#viewAlasanBatalContainer').show();
                        } else {
                            $('#viewAlasanBatalContainer').hide();
                        }
                    } else {
                        $('#viewAlasanBatalContainer').hide();
                        $('#viewStatusBadge').html('<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-clock-fill me-1"></i> Draft</span>');
                        $('#viewJurnalAlert').hide();
                        let currentAuthId = {{ auth()->id() ?? 'null' }};
                        let isSuperAdminOrAdmin = {{ auth()->user() && auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']) ? 'true' : 'false' }};
                        let canPost = {{ auth()->user() && auth()->user()->can('posting-alokasi-biaya') ? 'true' : 'false' }};
                        let canCancel = {{ auth()->user() && auth()->user()->can('batalkan-alokasi-biaya') ? 'true' : 'false' }};
                        let canDelete = {{ auth()->user() && (auth()->user()->can('hapus-alokasi-biaya') || auth()->user()->can('batalkan-alokasi-biaya')) ? 'true' : 'false' }};
                        let canEdit = {{ auth()->user() && (auth()->user()->can('edit-alokasi-biaya') || auth()->user()->can('tambah-alokasi-biaya')) ? 'true' : 'false' }};

                        let actionsHtml = '';
                        if (d.created_by == currentAuthId || isSuperAdminOrAdmin) {
                            if (canDelete) {
                                actionsHtml += `
                                    <button type="button" class="btn btn-outline-danger px-3.5 py-2 rounded-pill fw-semibold me-2 btn-delete" data-id="${d.id}" data-nomor="${d.nomor_alokasi}">
                                        <i class="bi bi-trash3 me-1"></i> Hapus Draft
                                    </button>
                                `;
                            }
                            if (canEdit) {
                                actionsHtml += `
                                    <button type="button" class="btn btn-outline-warning px-3.5 py-2 rounded-pill fw-semibold me-2 btn-edit" data-id="${d.id}" data-nomor="${d.nomor_alokasi}">
                                        <i class="bi bi-pencil me-1"></i> Edit Dokumen Draft
                                    </button>
                                `;
                            }
                            if (canPost) {
                                actionsHtml += `
                                    <button type="button" class="btn text-white px-3.5 py-2 rounded-pill fw-bold shadow btn-modal-post" data-id="${d.id}" data-nomor="${d.nomor_alokasi}"
                                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                        <i class="bi bi-check-circle-fill me-1.5"></i> Posting Sekarang ke HPP
                                    </button>
                                `;
                            }
                        }
                        $('#viewActionContainer').html(actionsHtml);
                    }

                    $('#viewCreatedBy').text(d.creator ? d.creator.name : 'System');
                    $('#viewPoEkspedisi').text(d.po_biaya ? d.po_biaya.nomor_po : '-');
                    $('#viewVendorEkspedisi').text(d.po_biaya?.supplier ? d.po_biaya.supplier.nama_supplier : '-');

                    let tenderObj = d.tender || d.po_biaya?.tender;
                    if (tenderObj) {
                        let tName = (tenderObj.nomor_kontrak && tenderObj.nomor_kontrak !== '-')
                            ? (tenderObj.nomor_kontrak + (tenderObj.nama_tender ? ' - ' + tenderObj.nama_tender : ''))
                            : (tenderObj.nama_tender || ('Tender #' + tenderObj.id));
                        $('#viewTender').html(`<span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-briefcase me-1"></i>${escapeHtml(tName)}</span>`);
                    } else {
                        $('#viewTender').html('<span class="text-muted">-</span>');
                    }

                    $('#viewTotalBiaya').text(formatRupiah(d.total_biaya));
                    if (d.po_biaya) {
                        let pphNom = parseFloat(d.po_biaya.pph_nominal || d.po_biaya.total_pph || 0);
                        let pphName = d.po_biaya.pph_pajak ? d.po_biaya.pph_pajak.nama_pajak : 'PPh';
                        let kursPo = parseFloat(d.po_biaya.kurs || 1);
                        let pphNomIdr = pphNom * (kursPo > 0 ? kursPo : 1);
                        if (pphNomIdr > 0) {
                            $('#viewPphNote').html(`<span class="badge mt-1" style="background-color: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.68rem;" title="Potongan PPh adalah withholding tax yang dipotong dari tagihan vendor ekspedisi, tidak menambah maupun mengurangi HPP persediaan"><i class="bi bi-info-circle me-1"></i>${escapeHtml(pphName)} (-${formatRupiah(pphNomIdr)}) tidak dihitung ke HPP</span>`);
                        } else {
                            $('#viewPphNote').html('<small class="text-success d-block mt-0.5" style="font-size:0.68rem;"><i class="bi bi-shield-check me-1"></i>Murni DPP Jasa (Tanpa Pajak)</small>');
                        }
                    } else {
                        $('#viewPphNote').html('');
                    }
                    $('#viewMetodeAlokasi').text(d.metode_alokasi);
                    $('#viewKeterangan').text(d.keterangan || '-');

                    // Details table
                    let html = '';
                    if (d.details && d.details.length > 0) {
                        d.details.forEach(function (item, idx) {
                            let bName = escapeHtml(item.barang ? item.barang.nama_barang : 'Barang');
                            let bCode = escapeHtml(item.barang ? item.barang.kode_barang : '-');
                            let grNum = escapeHtml(item.goods_receipt ? item.goods_receipt.nomor_gr : '-');
                            let uName = escapeHtml(item.satuan_nama || '');

                            html += `
                                <tr>
                                    <td class="text-center small font-monospace text-nowrap" style="color: var(--erp-text-muted);">${idx + 1}</td>
                                    <td class="font-monospace small fw-bold text-primary text-nowrap">${grNum}</td>
                                    <td class="text-nowrap" style="min-width: 220px;">
                                        <span class="fw-bold d-block text-truncate" style="color: var(--erp-text-main); font-size: 0.88rem; max-width: 260px;" title="${bName}">${bName}</span>
                                        <span class="small font-monospace text-muted">${bCode}</span>
                                    </td>
                                    <td class="text-end fw-semibold font-monospace text-nowrap" style="color: var(--erp-text-main);">${formatNumber(item.qty_diterima, 2)} <small class="text-muted">${uName}</small></td>
                                    <td class="text-end font-monospace text-nowrap" style="color: var(--erp-text-main);">${formatRupiah(item.nilai_barang)}</td>
                                    <td class="text-end fw-bold font-monospace text-info text-nowrap">${formatNominal(item.porsi_persen, true)}%</td>
                                    <td class="text-end font-monospace fw-bold text-nowrap" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">${formatRupiah(item.biaya_dialokasikan)}</td>
                                    <td class="text-end font-monospace small text-nowrap" style="background: rgba(245, 158, 11, 0.08); color: var(--erp-text-muted);">${formatRupiah(item.biaya_per_unit, true)}</td>
                                    <td class="text-end font-monospace small text-nowrap" style="background: rgba(16, 185, 129, 0.08); color: var(--erp-text-muted);">${formatRupiah(item.hpp_awal, true)}</td>
                                    <td class="text-end font-monospace fw-bold text-nowrap" style="background: rgba(16, 185, 129, 0.18); color: #10b981; font-size: 0.92rem;">${formatRupiah(item.hpp_baru, true)}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada rincian item</td></tr>';
                    }
                    $('#viewTbodyDetails').html(html);

                    $('#modalViewAlokasi').modal('show');
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengambil rincian dokumen alokasi.' });
            }
        });
    });

    // ==========================================
    // POST ACTION
    // ==========================================
    function postAlokasi(id, nomor) {
        Swal.fire({
            title: `Posting ${nomor}?`,
            text: 'Biaya pengiriman akan ditambahkan ke nilai stok persediaan dan jurnal akuntansi otomatis dicatat. Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Posting!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Posting...',
                    text: 'Sedang memperbarui nilai persediaan dan buku besar...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let url = "{{ route('alokasi-biaya.post', ':id') }}".replace(':id', id);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    dataType: "json",
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Posting Berhasil!',
                            text: res.message || 'Alokasi biaya berhasil di-Posting.'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal melakukan posting alokasi.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-post', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');
        postAlokasi(id, nomor);
    });

    $(document).on('click', '.btn-modal-post', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');
        $('#modalViewAlokasi').modal('hide');
        postAlokasi(id, nomor);
    });

    // ==========================================
    // EDIT DRAFT ACTION (KHUSUS STATUS DRAFT)
    // ==========================================
    $(document).on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor') || '';

        Swal.fire({
            title: 'Memuat Data Draft...',
            text: 'Mengambil rincian dokumen alokasi biaya...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Tutup modal view jika sebelumnya sedang terbuka
        $('#modalViewAlokasi').modal('hide');

        $.ajax({
            url: "{{ route('alokasi-biaya.edit-data', ':id') }}".replace(':id', id),
            type: "GET",
            dataType: "json",
            success: function (res) {
                Swal.close();
                if (res.success && res.data) {
                    let d = res.data;
                    if (d.status !== 'Draft') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Dapat Diedit',
                            text: 'Dokumen ini berstatus ' + d.status + '. Edit hanya diperbolehkan jika status dokumen masih Draft.'
                        });
                        return;
                    }

                    // Reset form dan aktifkan mode edit
                    $('#formCreateAlokasi')[0].reset();
                    $('#create_alokasi_id').val(d.id);
                    $('#modalCreateAlokasiLabel').html(`<i class="bi bi-pencil-square text-warning me-2"></i>Edit Alokasi Biaya Pengiriman (#${d.nomor_alokasi})`);
                    $('#btnSimpanDraft').html('<i class="bi bi-pencil-square me-1"></i> Simpan Perubahan Draft');

                    let tglStr = d.tanggal_alokasi ? d.tanggal_alokasi.split('T')[0] : new Date().toISOString().split('T')[0];
                    $('#create_tanggal_alokasi').val(tglStr);
                    $('#create_metode_alokasi').val(d.metode_alokasi || 'Berdasarkan Nilai Barang');
                    $('#create_keterangan').val(d.keterangan || '');
                    let len = (d.keterangan || '').length;
                    $('#keterangan_char_count').text(`${len}/40 karakter`).removeClass('text-danger fw-bold').addClass('text-muted');

                    $('#create_tender_id').val(d.tender_id || '').trigger('change');

                    // Load PO Jasa khusus (mencakup PO milik dokumen ini)
                    loadEligiblePos(d.id, d.po_biaya_id);

                    // Reset search input & paginasi untuk mode edit
                    grCurrentPage = 1;
                    grPageSize = 10;
                    grSearchQuery = '';
                    $('#grPageSizeSelect').val('10');
                    $('#searchGrInput').val('');
                    $('#btnClearSearchGr').hide();

                    // Load GR dan centang yang sebelumnya dipilih, lalu otomatis hitung simulasi
                    let selectedGrIds = res.selected_gr_ids || [];
                    loadEligibleGrs(selectedGrIds, function () {
                        setTimeout(() => {
                            $('#btnHitungPreview').trigger('click');
                        }, 250);
                    });

                    $('#modalCreateAlokasi').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat',
                        text: res.message || 'Terjadi kesalahan saat memuat data alokasi.'
                    });
                }
            },
            error: function (xhr) {
                Swal.close();
                let msg = xhr.responseJSON?.message || 'Gagal memuat data alokasi biaya untuk diedit.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg
                });
            }
        });
    });

    // ==========================================
    // CANCEL ACTION (BATALKAN DOKUMEN DRAFT & VOID POSTED)
    // ==========================================
    function cancelAlokasi(id, nomor, status) {
        let isPosted = (status === 'Posted');
        let infoWarning = isPosted
            ? '<div class="alert alert-warning text-start small mb-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Dokumen berstatus <b>Posted</b>. Pembatalan (Void) akan membatalkan penambahan nilai persediaan HPP stok gudang &amp; jurnal akuntansi terkait.</div>'
            : '<div class="alert alert-secondary text-start small mb-3 py-2"><i class="bi bi-info-circle-fill me-1"></i> Status dokumen draft ini akan diubah menjadi Batal.</div>';

        Swal.fire({
            title: `Batalkan Dokumen ${nomor}?`,
            html: `
                ${infoWarning}
                <div class="text-start">
                    <label class="form-label small fw-semibold text-muted mb-1">Alasan Pembatalan <span class="text-danger">*</span> (Maks. 30 Karakter):</label>
                    <input type="text" id="swalAlasanBatal" class="form-control" maxlength="30" placeholder="Contoh: Salah pilih vendor ekspedisi" autocomplete="off">
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-danger d-none" id="swalAlasanError">Alasan pembatalan wajib diisi!</small>
                        <small class="text-muted ms-auto"><span id="swalCharCount">0</span>/30</small>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-slash-circle me-1"></i> Ya, Batalkan!',
            cancelButtonText: 'Kembali',
            didOpen: () => {
                const input = document.getElementById('swalAlasanBatal');
                const counter = document.getElementById('swalCharCount');
                const err = document.getElementById('swalAlasanError');
                input.focus();
                input.addEventListener('input', function() {
                    counter.textContent = this.value.length;
                    if (this.value.trim().length > 0) {
                        err.classList.add('d-none');
                        input.classList.remove('is-invalid');
                    }
                });
            },
            preConfirm: () => {
                const input = document.getElementById('swalAlasanBatal');
                const val = input.value.trim();
                if (!val) {
                    document.getElementById('swalAlasanError').classList.remove('d-none');
                    input.classList.add('is-invalid');
                    return false;
                }
                if (val.length > 30) {
                    Swal.showValidationMessage('Alasan pembatalan maksimal 30 karakter.');
                    return false;
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let alasan = result.value;
                Swal.fire({
                    title: 'Memproses Pembatalan...',
                    text: isPosted ? 'Sedang me-rollback persediaan stok gudang & jurnal...' : 'Sedang memperbarui status dokumen...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let url = "{{ route('alokasi-biaya.cancel', ':id') }}".replace(':id', id);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        alasan_batal: alasan
                    },
                    dataType: "json",
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dibatalkan!',
                            text: res.message || 'Alokasi biaya berhasil dibatalkan.'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal membatalkan alokasi biaya.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-cancel', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');
        let status = $(this).data('status') || '';
        cancelAlokasi(id, nomor, status);
    });

    $(document).on('click', '.btn-modal-cancel', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');
        let status = $(this).data('status') || '';
        $('#modalViewAlokasi').modal('hide');
        cancelAlokasi(id, nomor, status);
    });

    // ==========================================
    // HAPUS DOKUMEN ALOKASI BIAYA (HANYA DRAFT)
    // ==========================================
    function deleteAlokasi(id, nomor) {
        Swal.fire({
            title: 'Hapus Dokumen Draft?',
            html: `Apakah Anda yakin ingin menghapus permanen dokumen alokasi <b class="text-danger font-monospace">${escapeHtml(nomor)}</b>?<br><small class="text-muted">Data rincian alokasi ini akan dihapus dari sistem dan tidak dapat dikembalikan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Ya, Hapus Permanen!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Dokumen...',
                    text: 'Sedang memproses penghapusan data draft...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let url = "{{ route('alokasi-biaya.destroy', ':id') }}".replace(':id', id);

                $.ajax({
                    url: url,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Dihapus!',
                            text: res.message || 'Dokumen alokasi draft berhasil dihapus.'
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Gagal menghapus dokumen alokasi biaya.';
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');
        if ($('#modalViewAlokasi').hasClass('show')) {
            $('#modalViewAlokasi').modal('hide');
        }
        deleteAlokasi(id, nomor);
    });

    // ==========================================
    // LIHAT JURNAL AKUNTANSI MODAL
    // ==========================================
    function openModalJurnal(alokasiId) {
        $('#alokasi_jurnal_loading').removeClass('d-none');
        $('#alokasi_jurnal_content').addClass('d-none');
        $('#alokasi_jurnal_nomor').text('-');
        $('#modalJurnalAlokasi').modal('show');

        let url = "{{ route('alokasi-biaya.jurnal', ':id') }}".replace(':id', alokasiId);

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success && res.jurnal) {
                    let j = res.jurnal;
                    let a = res.alokasi;
                    let t = res.tender;

                    // Header Info
                    $('#alokasi_jurnal_nomor').text(j.nomor_jurnal || '-');

                    let tglStr = '-';
                    if (j.tanggal_jurnal) {
                        let raw = String(j.tanggal_jurnal);
                        if (raw.includes('T')) {
                            let d = new Date(raw);
                            tglStr = !isNaN(d.getTime()) 
                                ? d.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'}) 
                                : raw.split('T')[0];
                        } else {
                            let p = raw.split('-');
                            tglStr = p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : raw;
                        }
                    }
                    $('#alokasi_jurnal_tanggal').text(tglStr);

                    // Entitas Pembukuan (PT vs Cabang)
                    let entitasText = 'PT PUSAT';
                    let entitasBadgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                    if (j.branch_id && j.branch) {
                        entitasText = `Cabang: ${j.branch.nama_cabang}`;
                        entitasBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
                    } else if (j.company) {
                        entitasText = j.company.nama_perusahaan || 'PT PUSAT';
                    }
                    $('#alokasi_jurnal_entitas_badge').html(`<span class="badge ${entitasBadgeClass} px-2.5 py-1 rounded-pill fw-bold"><i class="bi bi-building me-1"></i>${escapeHtml(entitasText)}</span>`);

                    $('#alokasi_jurnal_ref_alokasi').text(a.nomor_alokasi || '-');
                    $('#alokasi_jurnal_vendor').text(`${a.vendor || 'Ekspedisi'} (PO: ${a.nomor_po || '-'})`);
                    $('#alokasi_jurnal_keterangan').text(j.keterangan || '-');

                    // Tender Info Handling: TAMPILKAN JIKA TERKAIT TENDER
                    if (t) {
                        $('#alokasi_jurnal_tender_title').text(t.display || t.nama_tender || '-');
                        $('#alokasi_jurnal_tender_customer').text(t.customer || '-');
                        $('#alokasi_jurnal_tender_card').removeClass('d-none');
                    } else if (j.no_tender) {
                        $('#alokasi_jurnal_tender_title').text(j.no_tender);
                        $('#alokasi_jurnal_tender_customer').text('Tender Terkait');
                        $('#alokasi_jurnal_tender_card').removeClass('d-none');
                    } else {
                        $('#alokasi_jurnal_tender_card').addClass('d-none');
                    }

                    // Print Voucher Link
                    let printUrl = "{{ route('accounting.jurnal-umum.print', ':id') }}".replace(':id', j.id);
                    $('#btn_print_alokasi_jurnal').attr('href', printUrl);

                    // Details Rows
                    let rowsHtml = '';
                    let totalDebit = 0;
                    let totalKredit = 0;

                    (j.details || []).forEach(function (row, idx) {
                        let debit = parseFloat(row.debit) || 0;
                        let kredit = parseFloat(row.kredit) || 0;
                        totalDebit += debit;
                        totalKredit += kredit;

                        let coaCode = row.account ? row.account.code : '-';
                        let coaName = row.account ? row.account.name : 'Akun Tidak Ditemukan';
                        let isDebit = debit > 0;

                        let debitFmt = debit > 0 ? 'Rp ' + Number(debit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
                        let kreditFmt = kredit > 0 ? 'Rp ' + Number(kredit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';

                        let safeCoaCode = escapeHtml(coaCode);
                        let safeCoaName = escapeHtml(coaName);
                        let safeKet = escapeHtml(row.keterangan || '-');

                        rowsHtml += `
                            <tr>
                                <td class="text-center text-body-secondary small">${idx + 1}</td>
                                <td style="${isDebit ? '' : 'padding-left: 28px !important;'}">
                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace me-2">${safeCoaCode}</span>
                                    <span class="${isDebit ? 'fw-bold text-body-emphasis' : 'text-body-secondary'}">${!isDebit ? '<i class="bi bi-arrow-return-right text-muted me-1"></i>' : ''}${safeCoaName}</span>
                                </td>
                                <td class="text-end font-monospace ${debit > 0 ? 'text-success fw-bold' : 'text-body-secondary opacity-50'}">
                                    ${debitFmt}
                                </td>
                                <td class="text-end font-monospace ${kredit > 0 ? 'text-primary fw-bold' : 'text-body-secondary opacity-50'}">
                                    ${kreditFmt}
                                </td>
                                <td class="text-body-secondary text-truncate small" style="max-width: 250px;" title="${safeKet}">${safeKet}</td>
                            </tr>
                        `;
                    });

                    $('#alokasi_jurnal_rows_container').html(rowsHtml || '<tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada baris akun jurnal.</td></tr>');
                    $('#alokasi_jurnal_total_debit').text('Rp ' + Number(totalDebit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#alokasi_jurnal_total_kredit').text('Rp ' + Number(totalKredit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    let diff = Math.abs(totalDebit - totalKredit);
                    let isBalanced = diff < 0.01 && totalDebit > 0;
                    if (isBalanced) {
                        $('#alokasi_jurnal_balance_container').html(`
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1">
                                <i class="bi bi-check2-circle me-1"></i>Balance
                            </span>
                        `);
                    } else {
                        $('#alokasi_jurnal_balance_container').html(`
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-3 py-1">
                                <i class="bi bi-exclamation-triangle me-1"></i>Unbalanced
                            </span>
                        `);
                    }

                    $('#alokasi_jurnal_loading').addClass('d-none');
                    $('#alokasi_jurnal_content').removeClass('d-none');
                } else {
                    $('#modalJurnalAlokasi').modal('hide');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Jurnal Belum Ada',
                        text: res.message || 'Jurnal belum terbentuk untuk alokasi ini.'
                    });
                }
            },
            error: function (xhr) {
                $('#modalJurnalAlokasi').modal('hide');
                let msg = xhr.responseJSON?.message || 'Gagal memuat jurnal akuntansi.';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Jurnal',
                    text: msg
                });
            }
        });
    }

    $(document).on('click', '.btn-open-jurnal', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        if (id) {
            openModalJurnal(id);
        }
    });

    // Ensure parent modal remains scrollable when stacked modal is closed
    $(document).on('hidden.bs.modal', '#modalJurnalAlokasi', function () {
        if ($('#modalViewAlokasi').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // ==========================================
    // INITIALIZE BOOTSTRAP TOOLTIPS
    // ==========================================
    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover',
                    boundary: 'window'
                });
            });
        }
    }
    initTooltips();

    $(document).on('click', '.action-btn, .btn-view', function () {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            let tip = bootstrap.Tooltip.getInstance(this);
            if (tip) {
                tip.hide();
            }
        }
    });

    // ==========================================
    // PER PAGE / JUMLAH BARIS TABEL HANDLER (10, 25, 50, 100)
    // ==========================================
    $(document).on('change', '.select-per-page', function () {
        const newPerPage = $(this).val();
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', newPerPage);
        currentUrl.searchParams.set('page', '1'); // Reset ke halaman pertama saat jumlah baris diubah
        window.location.href = currentUrl.toString();
    });

});
</script>
