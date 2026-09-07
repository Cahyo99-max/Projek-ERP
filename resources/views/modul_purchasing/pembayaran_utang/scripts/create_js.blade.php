<script>
$(document).ready(function () {

    // ===== NUMBER & CURRENCY FORMATTING HELPERS =====
    function formatRp(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getSelectedCurrencyInfo() {
        let opt = $('#createCurrencyId').find(':selected');
        let code = opt.data('code') || 'IDR';
        let symbol = opt.data('symbol') || (code === 'IDR' ? 'Rp' : code);
        let kurs = parseFloat($('#createKurs').val() || 1);
        if (isNaN(kurs) || kurs <= 0) kurs = 1;
        let isForeign = (code !== 'IDR');

        return { code, symbol, kurs, isForeign };
    }

    function formatMoneyVal(val) {
        if (val === undefined || val === null || val === '') return '';
        let num = typeof val === 'number' ? val : parseFloat(val);
        if (isNaN(num) || num <= 0) return '';

        let parts = num.toFixed(2).split('.');
        let integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        let decimalPart = parts[1];

        if (decimalPart === '00') {
            return integerPart;
        }
        return integerPart + ',' + decimalPart.replace(/0+$/, '');
    }

    function parseMoney(str) {
        if (!str) return 0;
        if (typeof str === 'number') return str;
        let clean = str.toString().replace(/\./g, '').replace(/,/g, '.').replace(/[^\d.-]/g, '');
        let val = parseFloat(clean);
        return isNaN(val) ? 0 : val;
    }

    function maskMoneyInput(str) {
        if (!str) return '';
        let clean = str.toString().replace(/[^\d,]/g, '');
        let commaParts = clean.split(',');
        let intPart = commaParts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        if (commaParts.length > 1) {
            let decPart = commaParts.slice(1).join('').substring(0, 2);
            return intPart + ',' + decPart;
        }
        return intPart;
    }

    function applyLiveMoneyMask(elem) {
        let input = elem[0];
        let orig = elem.val();
        if (!orig) return;

        let caretPos = input.selectionStart || orig.length;
        let numDigitsBeforeCaret = orig.substring(0, caretPos).replace(/[^\d,]/g, '').length;

        let masked = maskMoneyInput(orig);
        elem.val(masked);

        if (input.setSelectionRange) {
            let newPos = masked.length;
            let digitsCount = 0;
            for (let i = 0; i < masked.length; i++) {
                if (/[\d,]/.test(masked[i])) {
                    digitsCount++;
                }
                if (digitsCount >= numDigitsBeforeCaret) {
                    newPos = i + 1;
                    break;
                }
            }
            input.setSelectionRange(newPos, newPos);
        }
    }

    // ===== SELECT2 INITIALIZATION =====
    function initCreateSelect2() {
        if ($.fn.select2) {
            $('#createSupplierId').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreatePayment'),
                placeholder: '-- Cari & Pilih Supplier --',
                allowClear: true,
                width: '100%'
            });

            $('#createKasBankId').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalCreatePayment'),
                placeholder: '-- Cari & Pilih Akun Kas / Bank --',
                allowClear: true,
                width: '100%'
            });
        }
    }

    function initLookupSelect2() {
        if ($.fn.select2) {
            $('#lookupInvoiceSupplierFilter').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalLookupInvoice'),
                placeholder: '-- Semua Supplier --',
                width: '100%'
            });
        }
    }

    $('#modalCreatePayment').on('shown.bs.modal', function () {
        initCreateSelect2();
    });

    $('#modalLookupInvoice').on('shown.bs.modal', function () {
        initLookupSelect2();
    });

    // Trigger hidden file inputs
    $('#btnTriggerCreateFile, #btnChangeCreateLampiran').on('click', function () {
        $('#createLampiran').click();
    });

    // ===== BUKTI TRANSFER FILE PREVIEW LISTENER =====
    $('#createLampiran').on('change', function () {
        let file = this.files[0];
        if (file) {
            let sizeKb = (file.size / 1024).toFixed(1);
            let sizeStr = (sizeKb > 1024) ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            $('#createLampiranFileName').text(file.name);
            $('#createLampiranFileSize').text(sizeStr);

            let reader = new FileReader();
            reader.onload = function (e) {
                $('#createLampiranPreviewImg').attr('src', e.target.result);
                $('#createLampiranPreviewWrapper').removeClass('d-none');
                $('#createUploadActions').addClass('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            $('#createLampiranPreviewWrapper').addClass('d-none');
            $('#createUploadActions').removeClass('d-none');
        }
    });

    // Fallback: When a photo is taken from direct file input
    $('#createLampiranCameraInput').on('change', function () {
        let file = this.files[0];
        if (file) {
            if (window.DataTransfer) {
                try {
                    let dt = new DataTransfer();
                    dt.items.add(file);
                    $('#createLampiran')[0].files = dt.files;
                } catch (e) {
                    console.log(e);
                }
            }

            let sizeKb = (file.size / 1024).toFixed(1);
            let sizeStr = (sizeKb > 1024) ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            $('#createLampiranFileName').text(file.name || 'Dokumen_Bukti_Kamera.jpg');
            $('#createLampiranFileSize').text(sizeStr);

            let reader = new FileReader();
            reader.onload = function (e) {
                $('#createLampiranPreviewImg').attr('src', e.target.result);
                $('#createLampiranPreviewWrapper').removeClass('d-none');
                $('#createUploadActions').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    $('#btnClearCreateLampiran').on('click', function () {
        $('#createLampiran').val('');
        $('#createLampiranCameraInput').val('');
        $('#createLampiranPreviewWrapper').addClass('d-none');
        $('#createUploadActions').removeClass('d-none');
    });

    // ===== OPEN CREATE MODAL =====
    $(document).on('click', '#btnOpenModalCreate, #btnOpenCreateModal', function () {
        $('#formCreatePayment')[0].reset();
        $('#createInvoicesTbody').empty();
        $('#createInvoiceActionBtns').attr('style', 'display: none !important');
        $('#createInvoiceFilterRow').hide();
        $('#createInvoicesTableWrapper').hide();
        $('#createInvoicesEmptyState').show().html(`
            <i class="bi bi-search fs-2 text-primary opacity-50 d-block mb-2"></i>
            <div class="fw-semibold">Pilih Supplier atau Klik "Cari &amp; Pilih Faktur"</div>
            <small>Daftar faktur pembelian yang belum lunas akan dimuat secara otomatis di tabel ini.</small>
        `);
        $('#createKasBankSaldoInfo').hide().empty();
        $('#createSupplierBankInfo').hide().empty();
        $('#createSaldoWarning').addClass('d-none').removeClass('d-flex');
        $('#createSaldoWarningText').empty();
        $('#createSummaryKasWarning').hide().empty();
        $('#createKasBankId').removeClass('is-invalid border-danger');
        $('#btnSubmitCreatePost').prop('disabled', false).removeClass('opacity-50');
        $('#createSupplierId').val('').trigger('change');
        $('#createKasBankId').val('').trigger('change');
        $('#createPaymentMethodId').val('');
        $('#createTanggalBayar').val(new Date().toISOString().substring(0, 10));
        $('#createCurrencyId').val($('#createCurrencyId option[data-code="IDR"]').val() || $('#createCurrencyId option:first').val());
        $('#createKurs').val('1.0000');
        $('#createLampiran').val('');
        $('#createLampiranCameraInput').val('');
        $('#createLampiranPreviewWrapper').addClass('d-none');
        $('#createUploadActions').removeClass('d-none');
        $('#searchTableCreateInvoice').val('');
        calculateCreateSummary();

        // Fetch new payment voucher number
        $.get('/purchase-payments/generate-code', function (res) {
            if (res.code) {
                $('#createNomorPembayaran').val(res.code);
            }
        });

        $('#modalCreatePayment').modal('show');
    });

    // ===== KAS / BANK CHANGE -> UPDATE SALDO INFO & VALIDATION =====
    $('#createKasBankId').on('change', function () {
        let opt = $(this).find(':selected');
        let saldo = parseFloat(opt.data('saldo') || 0);

        if ($(this).val()) {
            $('#createKasBankSaldoInfo').html(`
                <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                    <i class="bi bi-wallet2 me-1"></i>Saldo Tersedia: <strong>${formatRp(saldo)}</strong>
                </span>
            `).show();
        } else {
            $('#createKasBankSaldoInfo').hide().empty();
        }

        validateCreateKasBalance();
    });

    // ===== CURRENCY OR KURS CHANGE -> UPDATE SUMMARY =====
    $('#createCurrencyId, #createKurs').on('input change', function () {
        calculateCreateSummary();
    });

    // ===== ON SUPPLIER CHANGE -> FETCH UNPAID INVOICES & SHOW BANK INFO =====
    $('#createSupplierId').on('change', function () {
        let opt = $(this).find(':selected');
        let bank = opt.data('bank') || '';
        let supplierId = $(this).val();

        if (bank) {
            $('#createSupplierBankInfo').html(`
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                    <i class="bi bi-bank me-1"></i>Rek. Tujuan: <strong>${bank}</strong>
                </span>
            `).show();
        } else {
            $('#createSupplierBankInfo').hide().empty();
        }

        loadSupplierUnpaidInvoices(supplierId);
    });

    function loadSupplierUnpaidInvoices(supplierId, autoCheckIds) {
        if (!supplierId) {
            $('#createInvoiceActionBtns').attr('style', 'display: none !important');
            $('#createInvoiceFilterRow').hide();
            $('#createInvoicesTableWrapper').hide();
            $('#createInvoicesEmptyState').show().html(`
                <i class="bi bi-search fs-2 text-primary opacity-50 d-block mb-2"></i>
                <div class="fw-semibold">Pilih Supplier atau Klik "Cari &amp; Pilih Faktur"</div>
                <small>Daftar faktur pembelian yang belum lunas akan dimuat secara otomatis di tabel ini.</small>
            `);
            calculateCreateSummary();
            return;
        }

        $('#createInvoicesEmptyState').hide();
        $('#createInvoicesTableWrapper').show();
        $('#createInvoiceActionBtns').removeAttr('style').addClass('d-flex align-items-center gap-1');
        $('#createInvoiceFilterRow').show();
        $('#createInvoicesLoading').show();

        $.ajax({
            url: `/purchase-payments/unpaid-invoices/${supplierId}`,
            type: 'GET',
            success: function (res) {
                $('#createInvoicesLoading').hide();
                let tbody = $('#createInvoicesTbody');
                tbody.empty();

                if (!res.invoices || res.invoices.length === 0) {
                    $('#createInvoicesEmptyState').show().html(`
                        <i class="bi bi-check-circle-fill fs-2 text-success opacity-75 d-block mb-2"></i>
                        <div class="fw-bold text-success">Tidak Ada Utang Beredar</div>
                        <small class="text-muted">Semua faktur pembelian untuk supplier ini telah lunas atau belum ada faktur yang diposting.</small>
                    `);
                    calculateCreateSummary();
                    return;
                }

                // Auto-sync currency, kurs & payment method from invoice
                if (res.invoices[0]) {
                    if (res.invoices[0].currency_id) {
                        $('#createCurrencyId').val(res.invoices[0].currency_id);
                    }
                    if (res.invoices[0].kurs) {
                        $('#createKurs').val(res.invoices[0].kurs);
                    }
                    if (res.invoices[0].payment_method_id) {
                        $('#createPaymentMethodId').val(res.invoices[0].payment_method_id);
                    }
                }

                let curInfo = getSelectedCurrencyInfo();

                // Render invoices
                res.invoices.forEach(function (inv) {
                    let overdueBadge = inv.is_overdue 
                        ? `<span class="overdue-chip"><i class="bi bi-exclamation-triangle-fill"></i> Lewat ${inv.days_overdue} hari</span>` 
                        : `<span class="text-muted" style="font-size: 0.75rem;">${inv.tanggal_jatuh_tempo}</span>`;

                    let shouldCheck = autoCheckIds && autoCheckIds.includes(inv.id.toString());
                    let invKurs = parseFloat(inv.kurs || 1);
                    let isForeign = inv.currency_id && inv.currency_id != $('#createCurrencyId option[data-code="IDR"]').val() && invKurs > 1;

                    let totalFakturDisplay = isForeign 
                        ? `<div class="fw-bold">${inv.currency_symbol || '$'} ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.grand_total * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let sisaUtangDisplay = isForeign 
                        ? `<div class="fw-bold text-dark">${inv.currency_symbol || '$'} ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.remaining_balance * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let tr = `
                        <tr data-invoice-id="${inv.id}" data-currency-id="${inv.currency_id || ''}" data-kurs="${inv.kurs || 1}" data-payment-method-id="${inv.payment_method_id || ''}" data-search="${(inv.nomor_faktur + ' ' + (inv.nomor_faktur_supplier || '')).toLowerCase()}" data-sisa-utang="${inv.remaining_balance}" data-is-overdue="${inv.is_overdue ? '1' : '0'}" class="${shouldCheck ? 'table-primary' : ''}">
                            <td class="text-center">
                                <input class="form-check-input check-create-invoice" type="checkbox" data-id="${inv.id}" ${shouldCheck ? 'checked' : ''}>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${inv.nomor_faktur}</div>
                                <small class="text-muted">No. Supp: ${inv.nomor_faktur_supplier}</small>
                            </td>
                            <td>${inv.tanggal_faktur}</td>
                            <td>${overdueBadge}</td>
                            <td class="text-end fw-semibold">${totalFakturDisplay}</td>
                            <td class="text-end fw-bold text-dark col-sisa-utang">${sisaUtangDisplay}</td>
                            <td class="text-end">
                                <input type="text" 
                                    class="form-control form-control-sm text-end input-bayar money-input" 
                                    value="" placeholder="0">
                            </td>
                            <td class="text-end">
                                <input type="text" 
                                    class="form-control form-control-sm text-end input-potongan money-input" 
                                    value="" placeholder="0">
                            </td>
                            <td class="text-end fw-bold col-sisa-akhir text-muted">
                                ${sisaUtangDisplay}
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm input-catatan" 
                                    placeholder="Catatan...">
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });

                $('#createInvoiceFilterCount').text(`Total ${res.invoices.length} faktur tersedia`);
                calculateCreateSummary();
            },
            error: function (xhr) {
                $('#createInvoicesLoading').hide();
                let msg = xhr.responseJSON?.message || 'Gagal memuat faktur supplier!';
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: msg });
            }
        });
    }

    // ===== OPEN INVOICE LOOKUP MODAL =====
    $(document).on('click', '#btnLookupInvoices, #btnLookupInvoicesTop, #btnOpenLookupFromCreate, .btn-lookup-invoices', function () {
        let currentSupId = $('#createSupplierId').val() || 'all';
        $('#lookupInvoiceSupplierFilter').val(currentSupId).trigger('change');
        $('#lookupInvoiceSearch').val('');
        $('#modalLookupInvoice').modal('show');
        loadLookupInvoices();
    });

    // ===== LOOKUP MODAL: LOAD INVOICES VIA AJAX =====
    function loadLookupInvoices() {
        let supId = $('#lookupInvoiceSupplierFilter').val();
        let q     = $('#lookupInvoiceSearch').val();
        let tbody = $('#lookupInvoicesTbody');

        tbody.html(`
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span>Memuat daftar faktur pembelian...</span>
                </td>
            </tr>
        `);
        $('#btnApplyLookupInvoices').prop('disabled', true);
        $('#checkAllLookupInvoices').prop('checked', false);
        $('#lookupSelectedCount').text('0 Faktur');
        $('#lookupSelectedSupplierInfo').empty();

        $.ajax({
            url: '/purchase-payments/lookup-invoices',
            type: 'GET',
            data: { supplier_id: supId, q: q },
            success: function (res) {
                tbody.empty();

                if (!res.invoices || res.invoices.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 opacity-50 d-block mb-2"></i>
                                <div class="fw-semibold">Tidak Ditemukan Faktur Pembelian Belum Lunas</div>
                                <small>Coba sesuaikan filter kata kunci pencarian atau supplier.</small>
                            </td>
                        </tr>
                    `);
                    return;
                }

                res.invoices.forEach(function (inv) {
                    let overdueBadge = inv.is_overdue 
                        ? `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger font-monospace px-2 py-0.5"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat ${inv.days_overdue} hari</span>` 
                        : `<span class="text-muted">${inv.tanggal_jatuh_tempo}</span>`;

                    let invKurs = parseFloat(inv.kurs || 1);
                    let isForeign = inv.currency_id && inv.currency_id != $('#createCurrencyId option[data-code="IDR"]').val() && invKurs > 1;

                    let totalFakturDisplay = isForeign 
                        ? `<div class="fw-bold">${inv.currency_symbol || '$'} ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.grand_total * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let sisaUtangDisplay = isForeign 
                        ? `<div class="fw-bold text-primary">${inv.currency_symbol || '$'} ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.remaining_balance * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let tr = `
                        <tr data-supplier-id="${inv.supplier_id}" data-supplier-name="${inv.supplier_name}" data-currency-id="${inv.currency_id || ''}" data-kurs="${inv.kurs || 1}" data-payment-method-id="${inv.payment_method_id || ''}" data-sisa-utang="${inv.remaining_balance}">
                            <td class="text-center">
                                <input class="form-check-input check-lookup-item" type="checkbox" value="${inv.id}" data-supplier-id="${inv.supplier_id}" data-supplier-name="${inv.supplier_name}" data-currency-id="${inv.currency_id || ''}" data-kurs="${inv.kurs || 1}" data-payment-method-id="${inv.payment_method_id || ''}">
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${inv.nomor_faktur}</div>
                            </td>
                            <td>
                                <span class="text-muted font-monospace">${inv.nomor_faktur_supplier || '-'}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">${inv.supplier_name}</div>
                                <small class="text-muted">${inv.supplier_code}</small>
                            </td>
                            <td>${inv.tanggal_faktur}</td>
                            <td>${overdueBadge}</td>
                            <td class="text-end fw-semibold">${totalFakturDisplay}</td>
                            <td class="text-end fw-bold text-primary">${sisaUtangDisplay}</td>
                            <td class="text-center">${overdueBadge}</td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memuat data faktur!';
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i> ${msg}
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ===== LOOKUP FILTER LISTENERS =====
    $('#lookupInvoiceSupplierFilter').on('change', loadLookupInvoices);
    $('#btnRefreshLookupInvoices').on('click', loadLookupInvoices);
    let lookupSearchTimer;
    $('#lookupInvoiceSearch').on('input keyup', function () {
        clearTimeout(lookupSearchTimer);
        lookupSearchTimer = setTimeout(loadLookupInvoices, 350);
    });

    // ===== LOOKUP CHECK ALL TOGGLE =====
    $('#checkAllLookupInvoices').on('change', function () {
        let isChecked = $(this).is(':checked');
        $('.check-lookup-item').prop('checked', isChecked);
        updateLookupSelectionState();
    });

    $(document).on('change', '.check-lookup-item', function () {
        updateLookupSelectionState();
    });

    function updateLookupSelectionState() {
        let checked = $('.check-lookup-item:checked');
        let count = checked.length;
        $('#lookupSelectedCount').text(`${count} Faktur`);
        $('#btnApplyLookupInvoices').prop('disabled', count === 0);

        if (count > 0) {
            let supplierIds = [];
            let supplierNames = [];
            checked.each(function () {
                supplierIds.push($(this).data('supplier-id'));
                supplierNames.push($(this).data('supplier-name'));
            });
            let uniqueSuppliers = [...new Set(supplierIds)];

            if (uniqueSuppliers.length > 1) {
                $('#lookupSelectedSupplierInfo').html(`<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Peringatan: Faktur dari supplier berbeda terpilih!</span>`);
                Swal.fire({
                    icon: 'warning',
                    title: 'Supplier Berbeda Terdeteksi',
                    text: 'Satu bukti pembayaran utang hanya dapat dialokasikan untuk 1 supplier yang sama. Silakan pilih faktur dari supplier yang sama.',
                    confirmButtonColor: '#3b82f6'
                });
                $('#btnApplyLookupInvoices').prop('disabled', true);
            } else {
                $('#lookupSelectedSupplierInfo').html(`<span class="text-success"><i class="bi bi-check2-circle me-1"></i>Supplier: <strong>${supplierNames[0]}</strong></span>`);
            }
        } else {
            $('#lookupSelectedSupplierInfo').empty();
        }
    }

    // ===== USE SELECTED INVOICES FROM LOOKUP MODAL =====
    $('#btnApplyLookupInvoices').on('click', function () {
        let checked = $('.check-lookup-item:checked');
        if (checked.length === 0) return;

        let firstChecked = checked.first();
        let selectedSupplierId = firstChecked.data('supplier-id');
        let invCurrencyId      = firstChecked.data('currency-id');
        let invKurs            = firstChecked.data('kurs');
        let invPaymentMethodId = firstChecked.data('payment-method-id');
        let selectedInvoiceIds = [];

        checked.each(function () {
            selectedInvoiceIds.push($(this).val());
        });

        $('#modalLookupInvoice').modal('hide');

        // Set currency & kurs from selected invoice
        if (invCurrencyId) {
            $('#createCurrencyId').val(invCurrencyId);
        }
        if (invKurs) {
            $('#createKurs').val(invKurs);
        }
        if (invPaymentMethodId) {
            $('#createPaymentMethodId').val(invPaymentMethodId);
        }

        // Set supplier dropdown & load invoices with empty initial amount
        $('#createSupplierId').val(selectedSupplierId).trigger('change');

        // Reload supplier invoices and pre-check selected ones
        loadSupplierUnpaidInvoices(selectedSupplierId, selectedInvoiceIds);
    });

    // ===== LIVE SEARCH FILTER IN CREATE INVOICE TABLE =====
    $('#searchTableCreateInvoice').on('input keyup', function () {
        let q = $(this).val().toLowerCase().trim();
        $('#createInvoicesTbody tr').each(function () {
            let searchData = $(this).data('search') || '';
            if (!q || searchData.includes(q)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ===== CHECK ALL CREATE INVOICES TOGGLE =====
    $('#checkAllCreateInvoices').on('change', function () {
        let isChecked = $(this).is(':checked');
        $('#createInvoicesTbody tr:visible').find('.check-create-invoice').each(function () {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });

    // ===== INDIVIDUAL INVOICE CHECKBOX TOGGLE =====
    $(document).on('change', '.check-create-invoice', function () {
        let tr = $(this).closest('tr');
        let isChecked = $(this).is(':checked');

        if (isChecked) {
            tr.addClass('table-primary');
            let invCur  = tr.data('currency-id');
            let invKurs = tr.data('kurs');
            let invPm   = tr.data('payment-method-id');
            if (invCur) {
                $('#createCurrencyId').val(invCur);
            }
            if (invKurs) {
                $('#createKurs').val(invKurs);
            }
            if (invPm) {
                $('#createPaymentMethodId').val(invPm);
            }
            tr.find('.input-bayar').focus();
        } else {
            tr.removeClass('table-primary');
            tr.find('.input-bayar').val('');
            tr.find('.input-potongan').val('');
            tr.find('.input-catatan').val('');
        }

        updateRowCalculations(tr);
        calculateCreateSummary();
    });

    // ===== PAY ALL SELECTED (QUICK ACTION LUNASKAN TERPILIH) =====
    $('#btnPayAllSelected').on('click', function () {
        if ($('.check-create-invoice:checked').length === 0) {
            $('#createInvoicesTbody tr:visible .check-create-invoice').prop('checked', true).closest('tr').addClass('table-primary');
        }

        $('.check-create-invoice:checked').each(function () {
            let tr = $(this).closest('tr');
            let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
            let potong    = parseMoney(tr.find('.input-potongan').val());
            let maxBayar  = Math.max(0, sisaUtang - potong);
            tr.find('.input-bayar').val(formatMoneyVal(maxBayar));
            updateRowCalculations(tr);
        });
        calculateCreateSummary();
    });

    // ===== SELECT OVERDUE ONLY (QUICK ACTION) =====
    $('#btnSelectOverdueOnly').on('click', function () {
        $('#createInvoicesTbody tr').each(function () {
            let tr = $(this);
            let isOverdue = tr.data('is-overdue') == '1';
            let chk = tr.find('.check-create-invoice');
            if (isOverdue) {
                chk.prop('checked', true).trigger('change');
            } else {
                chk.prop('checked', false).trigger('change');
            }
        });
    });

    // ===== RESET ALLOCATION (QUICK ACTION) =====
    $('#btnResetAllocation').on('click', function () {
        $('.check-create-invoice:checked').each(function () {
            let tr = $(this).closest('tr');
            tr.find('.input-bayar').val('');
            tr.find('.input-potongan').val('');
            updateRowCalculations(tr);
        });
        calculateCreateSummary();
    });

    // ===== REAL-TIME INPUT EVENT HANDLER (MONEY MASK & INSTANT CALCULATION) =====
    $(document).on('input keyup change paste propertychange', '.money-input', function () {
        applyLiveMoneyMask($(this));

        let tr = $(this).closest('tr');
        let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
        let bayarInput = tr.find('.input-bayar');
        let potongInput = tr.find('.input-potongan');

        let bayarVal = parseMoney(bayarInput.val());
        let potongVal = parseMoney(potongInput.val());

        // Auto check checkbox & auto-sync currency and payment method if user enters amount > 0
        if ((bayarVal + potongVal) > 0) {
            tr.find('.check-create-invoice').prop('checked', true);
            tr.addClass('table-primary');
            let invCur  = tr.data('currency-id');
            let invKurs = tr.data('kurs');
            let invPm   = tr.data('payment-method-id');
            if (invCur) {
                $('#createCurrencyId').val(invCur);
            }
            if (invKurs) {
                $('#createKurs').val(invKurs);
            }
            if (invPm) {
                $('#createPaymentMethodId').val(invPm);
            }
        } else {
            tr.find('.check-create-invoice').prop('checked', false);
            tr.removeClass('table-primary');
        }

        // Clamping to avoid exceeding remaining debt
        if ((bayarVal + potongVal) > sisaUtang) {
            if ($(this).hasClass('input-bayar')) {
                bayarVal = Math.max(0, sisaUtang - potongVal);
                bayarInput.val(formatMoneyVal(bayarVal));
            } else {
                potongVal = Math.max(0, sisaUtang - bayarVal);
                potongInput.val(formatMoneyVal(potongVal));
            }
        }

        updateRowCalculations(tr);
        calculateCreateSummary();
    });

    // ===== ROW CALCULATION HELPER (INSTANT SISA AKHIR & DUAL CURRENCY BADGE) =====
    function updateRowCalculations(tr) {
        let isChecked = tr.find('.check-create-invoice').is(':checked');
        let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);

        let bayar  = parseMoney(tr.find('.input-bayar').val());
        let potong = parseMoney(tr.find('.input-potongan').val());
        let totalAlokasi = bayar + potong;
        let sisaAkhir = Math.max(0, sisaUtang - totalAlokasi);

        let curInfo = getSelectedCurrencyInfo();
        let cell = tr.find('.col-sisa-akhir');

        if (totalAlokasi > 0 && sisaAkhir <= 0.01) {
            cell.html(`<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (${curInfo.symbol} 0)</span>`);
        } else if (totalAlokasi > 0) {
            if (curInfo.isForeign && curInfo.kurs > 1) {
                cell.html(`
                    <div class="text-warning fw-bold">${curInfo.symbol} ${sisaAkhir.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${(sisaAkhir * curInfo.kurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>
                `);
            } else {
                cell.html(`<span class="text-warning fw-bold">${formatRp(sisaAkhir)}</span>`);
            }
        } else {
            if (curInfo.isForeign && curInfo.kurs > 1) {
                cell.html(`
                    <div class="text-muted">${curInfo.symbol} ${sisaUtang.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div>
                    <small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${(sisaUtang * curInfo.kurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>
                `);
            } else {
                cell.html(`<span class="text-muted">${formatRp(sisaUtang)}</span>`);
            }
        }
    }

    // ===== SUMMARY TOTALS & BALANCE VALIDATION (INSTANT DUAL CURRENCY BOTTOM BAR) =====
    function calculateCreateSummary() {
        let selectedCount = 0;
        let totalUtang    = 0;
        let totalPotongan = 0;
        let totalBayar    = 0;

        $('#createInvoicesTbody tr').each(function () {
            let tr = $(this);
            let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
            let bayar     = parseMoney(tr.find('.input-bayar').val());
            let potong    = parseMoney(tr.find('.input-potongan').val());
            let isChecked = tr.find('.check-create-invoice').is(':checked') || (bayar + potong) > 0;

            if (isChecked) {
                selectedCount++;
                totalUtang    += sisaUtang;
                totalPotongan += potong;
                totalBayar    += bayar;
            }
        });

        let curInfo = getSelectedCurrencyInfo();
        let totalUtangIdr    = totalUtang * curInfo.kurs;
        let totalPotonganIdr = totalPotongan * curInfo.kurs;
        let totalBayarIdr    = totalBayar * curInfo.kurs;

        $('#createSummaryCount').text(`${selectedCount} Faktur`);

        if (curInfo.isForeign) {
            $('#createSummaryTotalUtang').html(`
                <div class="text-end">
                    <span class="fw-bold">${curInfo.symbol} ${totalUtang.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <small class="text-muted d-block" style="font-size:0.75rem;">≈ ${formatRp(totalUtangIdr)}</small>
                </div>
            `);
            $('#createSummaryTotalPotongan').html(`
                <div class="text-end">
                    <span class="fw-bold text-danger">${curInfo.symbol} ${totalPotongan.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <small class="text-muted d-block" style="font-size:0.75rem;">≈ ${formatRp(totalPotonganIdr)}</small>
                </div>
            `);
            $('#createSummaryTotalBayar').html(`
                <div class="d-flex align-items-baseline justify-content-between">
                    <span class="fs-4 fw-bold">${curInfo.symbol} ${totalBayar.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size:0.7rem;">Kurs: ${curInfo.kurs.toLocaleString('id-ID')}</span>
                </div>
                <div class="small fw-semibold mt-1" style="font-size: 0.82rem; opacity: 0.95;">
                    <i class="bi bi-arrow-left-right me-1"></i>Ekuivalen IDR: <strong>${formatRp(totalBayarIdr)}</strong>
                </div>
            `);
        } else {
            $('#createSummaryTotalUtang').text(formatRp(totalUtang));
            $('#createSummaryTotalPotongan').text(formatRp(totalPotongan));
            $('#createSummaryTotalBayar').text(formatRp(totalBayar));
        }

        validateCreateKasBalance(totalBayar);
    }

    // ===== REAL-TIME ANTI-MINUS KAS/BANK VALIDATION =====
    function validateCreateKasBalance(currentTotalBayar) {
        if (currentTotalBayar === undefined) {
            let total = 0;
            $('#createInvoicesTbody tr').each(function () {
                total += parseMoney($(this).find('.input-bayar').val());
            });
            currentTotalBayar = total;
        }

        let curInfo = getSelectedCurrencyInfo();
        let opt = $('#createKasBankId').find(':selected');
        let kasBankId = $('#createKasBankId').val();
        let saldoKas = parseFloat(opt.data('saldo') || 0);
        let namaKas  = opt.data('nama') || 'Kas / Bank';

        // Outflow converted to IDR for comparison against IDR Kas/Bank
        let totalOutflowIdr = currentTotalBayar * curInfo.kurs;

        if (kasBankId && totalOutflowIdr > saldoKas && currentTotalBayar > 0) {
            let shortageIdr = totalOutflowIdr - saldoKas;
            let displayBayar = curInfo.isForeign 
                ? `${curInfo.symbol} ${currentTotalBayar.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})} (≈ ${formatRp(totalOutflowIdr)})` 
                : formatRp(currentTotalBayar);

            // 1. Top Warning Alert Banner
            $('#createSaldoWarningText').html(`
                <span class="fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Peringatan: Saldo Kas/Bank Tidak Mencukupi!</span><br>
                Nominal pembayaran (<strong>${displayBayar}</strong>) melebihi saldo akun <strong>${namaKas}</strong> yang tersedia (<strong>${formatRp(saldoKas)}</strong>). Kekurangan dana: <strong class="text-danger">${formatRp(shortageIdr)}</strong>. Saldo tidak boleh minus saat posting!
            `);
            $('#createSaldoWarning').removeClass('d-none').addClass('d-flex');

            // 2. Select input danger state & badge
            $('#createKasBankId').addClass('is-invalid border-danger');
            $('#createKasBankSaldoInfo').html(`
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Saldo Kurang: Tersedia <strong>${formatRp(saldoKas)}</strong> (Kurang: ${formatRp(shortageIdr)})
                </span>
            `).show();

            // 3. Summary grand total warning
            $('#createSummaryKasWarning').html(`
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Saldo Kas Kurang: ${formatRp(shortageIdr)}
            `).show();

            // 4. Disable submit post button
            $('#btnSubmitCreatePost').prop('disabled', true).addClass('opacity-50');
            return false;
        } else {
            $('#createSaldoWarning').addClass('d-none').removeClass('d-flex');
            $('#createSaldoWarningText').empty();
            $('#createSummaryKasWarning').hide().empty();
            $('#createKasBankId').removeClass('is-invalid border-danger');

            if (kasBankId) {
                $('#createKasBankSaldoInfo').html(`
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                        <i class="bi bi-wallet2 me-1"></i>Saldo Tersedia: <strong>${formatRp(saldoKas)}</strong>
                    </span>
                `).show();
            } else {
                $('#createKasBankSaldoInfo').hide().empty();
            }

            $('#btnSubmitCreatePost').prop('disabled', false).removeClass('opacity-50');
            return true;
        }
    }

    // ===== SUBMIT CREATE DRAFT =====
    $('#btnSubmitCreateDraft').on('click', function () {
        $('#createActionType').val('draft');
        submitCreateForm();
    });

    // ===== SUBMIT CREATE AJUKAN (SUBMISSION FOR APPROVAL) =====
    $('#btnSubmitCreateSubmit').on('click', function () {
        $('#createActionType').val('submit');

        Swal.fire({
            title: 'Konfirmasi Pengajuan Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Transaksi pembayaran utang akan disimpan dan diajukan untuk proses persetujuan (<em>approval workflow</em>).</p>
                   <div class="alert alert-light border py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-info-circle-fill text-primary me-1"></i> Notifikasi otomatis akan dikirimkan melalui WhatsApp kepada pihak approver berwenang.
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Ajukan Pembayaran',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                submitCreateForm();
            }
        });
    });

    // ===== SUBMIT CREATE POST =====
    $('#btnSubmitCreatePost').on('click', function () {
        $('#createActionType').val('post');

        let isBalanceValid = validateCreateKasBalance();
        if (!isBalanceValid) {
            Swal.fire({
                icon: 'error',
                title: 'Saldo Kas / Bank Tidak Mencukupi',
                text: 'Nominal pembayaran melebihi saldo kas/bank yang tersedia. Silakan sesuaikan pembayaran atau pilih akun kas/bank lain.'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Posting Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Transaksi pembayaran utang akan langsung diposting ke sistem akuntansi dan kas/bank.</p>
                   <div class="alert alert-light border py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-shield-check text-success me-1"></i> Jurnal Akuntansi dan Mutasi Pengeluaran Kas/Bank akan langsung diterbitkan secara otomatis.
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Ya, Simpan &amp; Posting',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                submitCreateForm();
            }
        });
    });

    // ===== FORM SUBMISSION AJAX WITH MULTIPART FORMDATA =====
    function submitCreateForm() {
        let supplierId = $('#createSupplierId').val();
        let kasBankId  = $('#createKasBankId').val();
        let tglBayar   = $('#createTanggalBayar').val();

        if (!supplierId) {
            Swal.fire({ icon: 'warning', title: 'Supplier Wajib Dipilih', text: 'Silakan pilih supplier terlebih dahulu.' });
            return;
        }
        if (!kasBankId) {
            Swal.fire({ icon: 'warning', title: 'Kas / Bank Wajib Dipilih', text: 'Silakan pilih akun Kas / Bank sumber pembayaran.' });
            return;
        }
        if (!tglBayar) {
            Swal.fire({ icon: 'warning', title: 'Tanggal Pembayaran Wajib Diisi', text: 'Tentukan tanggal pembayaran.' });
            return;
        }

        // Collect checked invoices
        let invoices = [];
        let totalBayar = 0;

        $('#createInvoicesTbody tr').each(function () {
            let tr = $(this);
            let invId  = tr.data('invoice-id');
            let bayar  = parseMoney(tr.find('.input-bayar').val());
            let potong = parseMoney(tr.find('.input-potongan').val());
            let cat    = tr.find('.input-catatan').val() || '';

            if ((bayar + potong) > 0) {
                invoices.push({
                    purchase_invoice_id: invId,
                    jumlah_bayar: bayar,
                    potongan: potong,
                    catatan: cat
                });
                totalBayar += bayar;
            }
        });

        if (invoices.length === 0 || totalBayar <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Alokasi Pembayaran Kosong',
                text: 'Pilih minimal 1 faktur dan isi nominal pembayaran (atau klik tombol "Lunaskan Terpilih") lebih besar dari 0.'
            });
            return;
        }

        let formData = new FormData();
        formData.append('nomor_pembayaran', $('#createNomorPembayaran').val());
        formData.append('supplier_id', supplierId);
        formData.append('kas_bank_id', kasBankId);
        formData.append('tanggal_bayar', tglBayar);
        formData.append('payment_method_id', $('#createPaymentMethodId').val() || '');
        formData.append('nomor_referensi', $('#createNomorReferensi').val() || '');
        formData.append('currency_id', $('#createCurrencyId').val() || '');
        formData.append('kurs', $('#createKurs').val() || 1);
        formData.append('keterangan', $('#createKeterangan').val() || '');
        formData.append('action_type', $('#createActionType').val());

        let lampiranFile = $('#createLampiran')[0].files[0] || $('#createLampiranCameraInput')[0].files[0];
        if (lampiranFile) {
            formData.append('lampiran', lampiranFile);
        }

        invoices.forEach((inv, index) => {
            formData.append(`invoices[${index}][purchase_invoice_id]`, inv.purchase_invoice_id);
            formData.append(`invoices[${index}][jumlah_bayar]`, inv.jumlah_bayar);
            formData.append(`invoices[${index}][potongan]`, inv.potongan);
            formData.append(`invoices[${index}][catatan]`, inv.catatan);
        });

        Swal.fire({
            title: 'Menyimpan & Mengompres Bukti Bayar...',
            text: 'Foto bukti transfer dikonversi otomatis ke format KB yang efisien.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '/purchase-payments',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
                $('#modalCreatePayment').modal('hide');
                $('#tablePayments').DataTable().ajax.reload(null, false);
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal menyimpan pembayaran utang!';
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: msg });
            }
        });
    }

});
</script>
