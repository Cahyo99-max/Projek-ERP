<script>
$(document).ready(function () {

    // ===== NUMBER & CURRENCY FORMATTING HELPERS =====
    function formatRp(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getSelectedCurrencyInfo() {
        let opt = $('#editCurrencyId').find(':selected');
        let code = opt.data('code') || 'IDR';
        let symbol = opt.data('symbol') || (code === 'IDR' ? 'Rp' : code);
        let kurs = parseFloat($('#editKurs').val() || 1);
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

    // ===== SELECT2 INITIALIZATION (EDIT) =====
    function initEditSelect2() {
        if ($.fn.select2) {
            $('#editSupplierId').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditPayment'),
                placeholder: '-- Cari & Pilih Supplier --',
                width: '100%'
            });

            $('#editKasBankId').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditPayment'),
                placeholder: '-- Cari & Pilih Akun Kas / Bank --',
                width: '100%'
            });
        }
    }

    $('#modalEditPayment').on('shown.bs.modal', function () {
        initEditSelect2();
    });

    // Trigger hidden file inputs (Edit)
    $('#btnTriggerEditFile, #btnChangeEditLampiran').on('click', function () {
        $('#editLampiran').click();
    });

    // ===== BUKTI TRANSFER FILE PREVIEW LISTENERS (EDIT) =====
    $('#editLampiran').on('change', function () {
        let file = this.files[0];
        if (file) {
            let sizeKb = (file.size / 1024).toFixed(1);
            let sizeStr = (sizeKb > 1024) ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            $('#editLampiranFileName').text(file.name);
            $('#editLampiranFileSize').text(sizeStr);
            $('#editRemoveLampiran').val('0');

            let reader = new FileReader();
            reader.onload = function (e) {
                $('#editLampiranPreviewImg').attr('src', e.target.result);
                $('#editLampiranPreviewWrapper').removeClass('d-none');
                $('#editUploadActions').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    // Fallback: When a photo is taken from direct file input (Edit)
    $('#editLampiranCameraInput').on('change', function () {
        let file = this.files[0];
        if (file) {
            if (window.DataTransfer) {
                try {
                    let dt = new DataTransfer();
                    dt.items.add(file);
                    $('#editLampiran')[0].files = dt.files;
                } catch (e) {
                    console.log(e);
                }
            }

            let sizeKb = (file.size / 1024).toFixed(1);
            let sizeStr = (sizeKb > 1024) ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            $('#editLampiranFileName').text(file.name || 'Dokumen_Bukti_Kamera.jpg');
            $('#editLampiranFileSize').text(sizeStr);
            $('#editRemoveLampiran').val('0');

            let reader = new FileReader();
            reader.onload = function (e) {
                $('#editLampiranPreviewImg').attr('src', e.target.result);
                $('#editLampiranPreviewWrapper').removeClass('d-none');
                $('#editUploadActions').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    $('#btnDeleteExistingLampiran').on('click', function () {
        $('#editLampiran').val('');
        $('#editLampiranCameraInput').val('');
        $('#editRemoveLampiran').val('1');
        $('#editLampiranPreviewWrapper').addClass('d-none');
        $('#editUploadActions').removeClass('d-none');
    });

    // ===== OPEN EDIT MODAL =====
    $(document).on('click', '.btn-edit-payment', function () {
        let paymentId = $(this).data('id');

        Swal.fire({
            title: 'Memuat Data Pembayaran...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/purchase-payments/${paymentId}/edit`,
            type: 'GET',
            success: function (res) {
                Swal.close();
                let p = res.payment;
                let invoices = res.invoices;

                $('#editPaymentId').val(p.id);
                $('#editNomorPembayaran').val(p.nomor_pembayaran);
                $('#editTanggalBayar').val(p.tanggal_bayar ? p.tanggal_bayar.substring(0, 10) : '');
                $('#editSupplierId').val(p.supplier_id).trigger('change');
                let editSupBank = $('#editSupplierId').find(':selected').data('bank') || '';
                if (editSupBank) {
                    $('#editSupplierBankInfo').html(`
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                            <i class="bi bi-bank me-1"></i>Rek. Tujuan: <strong>${editSupBank}</strong>
                        </span>
                    `).show();
                } else {
                    $('#editSupplierBankInfo').hide().empty();
                }

                $('#editKasBankId').val(p.kas_bank_id).trigger('change');
                $('#editPaymentMethodId').val(p.payment_method_id || (invoices[0]?.payment_method_id || ''));
                $('#editNomorReferensi').val(p.nomor_referensi || '');
                $('#editCurrencyId').val(p.currency_id || '');
                $('#editKurs').val(p.kurs || 1);
                $('#editKeterangan').val(p.keterangan || '');
                $('#searchTableEditInvoice').val('');
                $('#editSaldoWarning').addClass('d-none').removeClass('d-flex');
                $('#editSaldoWarningText').empty();
                $('#editSummaryKasWarning').hide().empty();

                // Handle Bukti Transfer Lampiran
                $('#editLampiran').val('');
                $('#editLampiranCameraInput').val('');
                $('#editRemoveLampiran').val('0');

                if (p.lampiran_url) {
                    $('#editLampiranPreviewImg').attr('src', p.lampiran_url);
                    $('#editLampiranFileName').text('Dokumen Bukti Tersimpan');
                    $('#editLampiranFileSize').text('Tersimpan di Arsip Server');
                    $('#editLampiranPreviewWrapper').removeClass('d-none');
                    $('#editUploadActions').addClass('d-none');
                } else {
                    $('#editLampiranPreviewWrapper').addClass('d-none');
                    $('#editUploadActions').removeClass('d-none');
                }

                let tbody = $('#editInvoicesTbody');
                tbody.empty();

                let curInfo = getSelectedCurrencyInfo();

                invoices.forEach(function (inv) {
                    let isChecked = inv.is_allocated;
                    let bayarVal  = isChecked ? formatMoneyVal(inv.allocated_bayar) : '';
                    let potongVal = isChecked ? formatMoneyVal(inv.allocated_potongan) : '';
                    let rawBayar  = isChecked ? parseFloat(inv.allocated_bayar || 0) : 0;
                    let rawPotong = isChecked ? parseFloat(inv.allocated_potongan || 0) : 0;
                    let sisaAkhir = Math.max(0, inv.remaining_balance - (rawBayar + rawPotong));

                    let invKurs = parseFloat(inv.kurs || 1);
                    let isForeign = inv.currency_id && inv.currency_id != $('#editCurrencyId option[data-code="IDR"]').val() && invKurs > 1;

                    let totalFakturDisplay = isForeign 
                        ? `<div class="fw-bold">${inv.currency_symbol || '$'} ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.grand_total * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.grand_total).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let sisaUtangDisplay = isForeign 
                        ? `<div class="fw-bold text-dark">${inv.currency_symbol || '$'} ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</div><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ${Number(inv.remaining_balance * invKurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</small>`
                        : `Rp ${Number(inv.remaining_balance).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

                    let tr = `
                        <tr data-invoice-id="${inv.id}" data-currency-id="${inv.currency_id || ''}" data-kurs="${inv.kurs || 1}" data-payment-method-id="${inv.payment_method_id || ''}" data-search="${(inv.nomor_faktur + ' ' + (inv.nomor_faktur_supplier || '')).toLowerCase()}" data-sisa-utang="${inv.remaining_balance}" data-is-overdue="${inv.is_overdue ? '1' : '0'}" class="${isChecked ? 'table-primary' : ''}">
                            <td class="text-center">
                                <input class="form-check-input check-edit-invoice" type="checkbox" data-id="${inv.id}" ${isChecked ? 'checked' : ''}>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${inv.nomor_faktur}</div>
                                <small class="text-muted">No. Supp: ${inv.nomor_faktur_supplier}</small>
                            </td>
                            <td>${inv.tanggal_faktur}</td>
                            <td><span class="text-muted" style="font-size: 0.75rem;">${inv.tanggal_jatuh_tempo}</span></td>
                            <td class="text-end fw-semibold">${totalFakturDisplay}</td>
                            <td class="text-end fw-bold text-dark col-sisa-utang">${sisaUtangDisplay}</td>
                            <td class="text-end">
                                <input type="text" 
                                    class="form-control form-control-sm text-end edit-input-bayar edit-money-input" 
                                    value="${bayarVal}" placeholder="0">
                            </td>
                            <td class="text-end">
                                <input type="text" 
                                    class="form-control form-control-sm text-end edit-input-potongan edit-money-input" 
                                    value="${potongVal}" placeholder="0">
                            </td>
                            <td class="text-end fw-bold col-sisa-akhir">
                                ${isChecked && (rawBayar + rawPotong) > 0 && sisaAkhir <= 0.01 
                                    ? '<span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Lunas (' + curInfo.symbol + ' 0)</span>' 
                                    : (isChecked && (rawBayar + rawPotong) > 0 
                                        ? (curInfo.isForeign && curInfo.kurs > 1 
                                            ? '<span class="text-warning fw-bold">' + curInfo.symbol + ' ' + sisaAkhir.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span><small class="text-muted d-block" style="font-size:0.72rem;">≈ Rp ' + (sisaAkhir * curInfo.kurs).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</small>'
                                            : '<span class="text-warning fw-bold">' + formatRp(sisaAkhir) + '</span>') 
                                        : sisaUtangDisplay)}
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm edit-input-catatan" 
                                    value="${inv.catatan || ''}" placeholder="Catatan...">
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });

                calculateEditSummary();
                $('#modalEditPayment').modal('show');
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memuat data pembayaran!';
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: msg });
            }
        });
    });

    // ===== LIVE SEARCH FILTER IN EDIT INVOICE TABLE =====
    $('#searchTableEditInvoice').on('input keyup', function () {
        let q = $(this).val().toLowerCase().trim();
        $('#editInvoicesTbody tr').each(function () {
            let searchData = $(this).data('search') || '';
            if (!q || searchData.includes(q)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // ===== KAS / BANK CHANGE (EDIT) -> UPDATE SALDO INFO =====
    $('#editKasBankId').on('change', function () {
        let opt = $(this).find(':selected');
        let saldo = parseFloat(opt.data('saldo') || 0);

        if ($(this).val()) {
            $('#editKasBankSaldoInfo').html(`
                <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                    <i class="bi bi-wallet2 me-1"></i>Saldo Tersedia: <strong>${formatRp(saldo)}</strong>
                </span>
            `).show();
        } else {
            $('#editKasBankSaldoInfo').hide().empty();
        }

        validateEditKasBalance();
    });

    // ===== CURRENCY OR KURS CHANGE (EDIT) -> UPDATE SUMMARY =====
    $('#editCurrencyId, #editKurs').on('input change', function () {
        calculateEditSummary();
    });

    // ===== CHECK ALL EDIT INVOICES TOGGLE =====
    $('#checkAllEditInvoices').on('change', function () {
        let isChecked = $(this).is(':checked');
        $('#editInvoicesTbody tr:visible').find('.check-edit-invoice').each(function () {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });

    // ===== INDIVIDUAL EDIT INVOICE CHECKBOX TOGGLE =====
    $(document).on('change', '.check-edit-invoice', function () {
        let tr = $(this).closest('tr');
        let isChecked = $(this).is(':checked');

        if (isChecked) {
            tr.addClass('table-primary');
            let invCur  = tr.data('currency-id');
            let invKurs = tr.data('kurs');
            let invPm   = tr.data('payment-method-id');
            if (invCur) {
                $('#editCurrencyId').val(invCur);
            }
            if (invKurs) {
                $('#editKurs').val(invKurs);
            }
            if (invPm) {
                $('#editPaymentMethodId').val(invPm);
            }
            tr.find('.edit-input-bayar').focus();
        } else {
            tr.removeClass('table-primary');
            tr.find('.edit-input-bayar').val('');
            tr.find('.edit-input-potongan').val('');
            tr.find('.edit-input-catatan').val('');
        }

        updateEditRowCalculations(tr);
        calculateEditSummary();
    });

    // ===== PAY ALL SELECTED (QUICK ACTION EDIT) =====
    $('#btnEditPayAllSelected').on('click', function () {
        if ($('.check-edit-invoice:checked').length === 0) {
            $('#editInvoicesTbody tr:visible .check-edit-invoice').prop('checked', true).closest('tr').addClass('table-primary');
        }

        $('.check-edit-invoice:checked').each(function () {
            let tr = $(this).closest('tr');
            let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
            let potong    = parseMoney(tr.find('.edit-input-potongan').val());
            let maxBayar  = Math.max(0, sisaUtang - potong);
            tr.find('.edit-input-bayar').val(formatMoneyVal(maxBayar));
            updateEditRowCalculations(tr);
        });
        calculateEditSummary();
    });

    // ===== SELECT OVERDUE ONLY (QUICK ACTION EDIT) =====
    $('#btnEditSelectOverdueOnly').on('click', function () {
        $('#editInvoicesTbody tr').each(function () {
            let tr = $(this);
            let isOverdue = tr.data('is-overdue') == '1';
            let chk = tr.find('.check-edit-invoice');
            if (isOverdue) {
                chk.prop('checked', true).trigger('change');
            } else {
                chk.prop('checked', false).trigger('change');
            }
        });
    });

    // ===== RESET ALLOCATION (QUICK ACTION EDIT) =====
    $('#btnEditResetAllocation').on('click', function () {
        $('.check-edit-invoice:checked').each(function () {
            let tr = $(this).closest('tr');
            tr.find('.edit-input-bayar').val('');
            tr.find('.edit-input-potongan').val('');
            updateEditRowCalculations(tr);
        });
        calculateEditSummary();
    });

    // ===== REAL-TIME INPUT EVENT HANDLER (EDIT) =====
    $(document).on('input keyup change paste propertychange', '.edit-money-input', function () {
        applyLiveMoneyMask($(this));

        let tr = $(this).closest('tr');
        let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
        let bayarInput = tr.find('.edit-input-bayar');
        let potongInput = tr.find('.edit-input-potongan');

        let bayarVal = parseMoney(bayarInput.val());
        let potongVal = parseMoney(potongInput.val());

        // Auto check checkbox & auto-sync currency and payment method if user enters amount > 0
        if ((bayarVal + potongVal) > 0) {
            tr.find('.check-edit-invoice').prop('checked', true);
            tr.addClass('table-primary');
            let invCur  = tr.data('currency-id');
            let invKurs = tr.data('kurs');
            let invPm   = tr.data('payment-method-id');
            if (invCur) {
                $('#editCurrencyId').val(invCur);
            }
            if (invKurs) {
                $('#editKurs').val(invKurs);
            }
            if (invPm) {
                $('#editPaymentMethodId').val(invPm);
            }
        } else {
            tr.find('.check-edit-invoice').prop('checked', false);
            tr.removeClass('table-primary');
        }

        // Clamping to avoid exceeding remaining debt
        if ((bayarVal + potongVal) > sisaUtang) {
            if ($(this).hasClass('edit-input-bayar')) {
                bayarVal = Math.max(0, sisaUtang - potongVal);
                bayarInput.val(formatMoneyVal(bayarVal));
            } else {
                potongVal = Math.max(0, sisaUtang - bayarVal);
                potongInput.val(formatMoneyVal(potongVal));
            }
        }

        updateEditRowCalculations(tr);
        calculateEditSummary();
    });

    // ===== ROW CALCULATION HELPER (EDIT) =====
    function updateEditRowCalculations(tr) {
        let isChecked = tr.find('.check-edit-invoice').is(':checked');
        let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);

        let bayar  = parseMoney(tr.find('.edit-input-bayar').val());
        let potong = parseMoney(tr.find('.edit-input-potongan').val());
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

    // ===== SUMMARY TOTALS & BALANCE VALIDATION (EDIT) =====
    function calculateEditSummary() {
        let selectedCount = 0;
        let totalUtang    = 0;
        let totalPotongan = 0;
        let totalBayar    = 0;

        $('#editInvoicesTbody tr').each(function () {
            let tr = $(this);
            let sisaUtang = parseFloat(tr.data('sisa-utang') || 0);
            let bayar     = parseMoney(tr.find('.edit-input-bayar').val());
            let potong    = parseMoney(tr.find('.edit-input-potongan').val());
            let isChecked = tr.find('.check-edit-invoice').is(':checked') || (bayar + potong) > 0;

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

        $('#editSummaryCount').text(`${selectedCount} Faktur`);

        if (curInfo.isForeign) {
            $('#editSummaryTotalUtang').html(`
                <div class="text-end">
                    <span class="fw-bold">${curInfo.symbol} ${totalUtang.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <small class="text-muted d-block" style="font-size:0.75rem;">≈ ${formatRp(totalUtangIdr)}</small>
                </div>
            `);
            $('#editSummaryTotalPotongan').html(`
                <div class="text-end">
                    <span class="fw-bold text-danger">${curInfo.symbol} ${totalPotongan.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <small class="text-muted d-block" style="font-size:0.75rem;">≈ ${formatRp(totalPotonganIdr)}</small>
                </div>
            `);
            $('#editSummaryTotalBayar').html(`
                <div class="d-flex align-items-baseline justify-content-between">
                    <span class="fs-4 fw-bold">${curInfo.symbol} ${totalBayar.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size:0.7rem;">Kurs: ${curInfo.kurs.toLocaleString('id-ID')}</span>
                </div>
                <div class="small fw-semibold mt-1" style="font-size: 0.82rem; opacity: 0.95;">
                    <i class="bi bi-arrow-left-right me-1"></i>Ekuivalen IDR: <strong>${formatRp(totalBayarIdr)}</strong>
                </div>
            `);
        } else {
            $('#editSummaryTotalUtang').text(formatRp(totalUtang));
            $('#editSummaryTotalPotongan').text(formatRp(totalPotongan));
            $('#editSummaryTotalBayar').text(formatRp(totalBayar));
        }

        validateEditKasBalance(totalBayar);
    }

    // ===== REAL-TIME ANTI-MINUS KAS/BANK VALIDATION (EDIT) =====
    function validateEditKasBalance(currentTotalBayar) {
        if (currentTotalBayar === undefined) {
            let total = 0;
            $('#editInvoicesTbody tr').each(function () {
                total += parseMoney($(this).find('.edit-input-bayar').val());
            });
            currentTotalBayar = total;
        }

        let curInfo = getSelectedCurrencyInfo();
        let opt = $('#editKasBankId').find(':selected');
        let kasBankId = $('#editKasBankId').val();
        let saldoKas = parseFloat(opt.data('saldo') || 0);
        let namaKas  = opt.data('nama') || 'Kas / Bank';

        let totalOutflowIdr = currentTotalBayar * curInfo.kurs;

        if (kasBankId && totalOutflowIdr > saldoKas && currentTotalBayar > 0) {
            let shortageIdr = totalOutflowIdr - saldoKas;
            let displayBayar = curInfo.isForeign 
                ? `${curInfo.symbol} ${currentTotalBayar.toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2})} (≈ ${formatRp(totalOutflowIdr)})` 
                : formatRp(currentTotalBayar);

            // 1. Top Warning Alert Banner
            $('#editSaldoWarningText').html(`
                <span class="fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Peringatan: Saldo Kas/Bank Tidak Mencukupi!</span><br>
                Nominal pembayaran (<strong>${displayBayar}</strong>) melebihi saldo akun <strong>${namaKas}</strong> yang tersedia (<strong>${formatRp(saldoKas)}</strong>). Kekurangan dana: <strong class="text-danger">${formatRp(shortageIdr)}</strong>. Saldo tidak boleh minus saat posting!
            `);
            $('#editSaldoWarning').removeClass('d-none').addClass('d-flex');

            // 2. Select input danger state & badge
            $('#editKasBankId').addClass('is-invalid border-danger');
            $('#editKasBankSaldoInfo').html(`
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Saldo Kurang: Tersedia <strong>${formatRp(saldoKas)}</strong> (Kurang: ${formatRp(shortageIdr)})
                </span>
            `).show();

            // 3. Summary grand total warning
            $('#editSummaryKasWarning').html(`
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Saldo Kas Kurang: ${formatRp(shortageIdr)}
            `).show();

            // 4. Disable submit post button
            $('#btnSubmitEditPost').prop('disabled', true).addClass('opacity-50');
            return false;
        } else {
            $('#editSaldoWarning').addClass('d-none').removeClass('d-flex');
            $('#editSaldoWarningText').empty();
            $('#editSummaryKasWarning').hide().empty();
            $('#editKasBankId').removeClass('is-invalid border-danger');

            if (kasBankId) {
                $('#editKasBankSaldoInfo').html(`
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                        <i class="bi bi-wallet2 me-1"></i>Saldo Tersedia: <strong>${formatRp(saldoKas)}</strong>
                    </span>
                `).show();
            } else {
                $('#editKasBankSaldoInfo').hide().empty();
            }

            $('#btnSubmitEditPost').prop('disabled', false).removeClass('opacity-50');
            return true;
        }
    }

    // ===== SUBMIT EDIT DRAFT =====
    $('#btnSubmitEditDraft').on('click', function () {
        $('#editActionType').val('draft');
        submitEditForm();
    });

    // ===== SUBMIT EDIT AJUKAN (SIMPAN & AJUKAN ULANG / APPROVAL) =====
    $('#btnSubmitEditSubmit').on('click', function () {
        $('#editActionType').val('submit');

        Swal.fire({
            title: 'Konfirmasi Pengajuan Pembayaran',
            html: `<p class="mb-2 text-secondary text-start">Perubahan data pembayaran utang akan disimpan dan diteruskan ke tahap persetujuan (<em>approval workflow</em>).</p>
                   <div class="alert alert-light border py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-info-circle-fill text-primary me-1"></i> Notifikasi otomatis akan dikirimkan melalui WhatsApp kepada pihak approver berwenang.
                   </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-send-check me-1"></i> Ya, Simpan &amp; Ajukan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                submitEditForm();
            }
        });
    });

    // ===== SUBMIT EDIT POST =====
    $('#btnSubmitEditPost').on('click', function () {
        $('#editActionType').val('post');

        let isBalanceValid = validateEditKasBalance();
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
            html: `<p class="mb-2 text-secondary text-start">Perubahan data pembayaran utang akan disimpan dan langsung diposting ke sistem keuangan.</p>
                   <div class="alert alert-light border py-2 px-3 text-start small mb-0 rounded-3">
                       <i class="bi bi-shield-check text-success me-1"></i> Jurnal Akuntansi otomatis diperbarui dan mutasi Kas/Bank akan disesuaikan.
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
                submitEditForm();
            }
        });
    });

    // ===== FORM SUBMISSION AJAX WITH MULTIPART FORMDATA (EDIT) =====
    function submitEditForm() {
        let paymentId  = $('#editPaymentId').val();
        let supplierId = $('#editSupplierId').val();
        let kasBankId  = $('#editKasBankId').val();
        let tglBayar   = $('#editTanggalBayar').val();

        if (!supplierId || !kasBankId || !tglBayar) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pastikan Supplier, Kas/Bank, dan Tanggal sudah terisi.' });
            return;
        }

        let invoices = [];
        let totalBayar = 0;

        $('#editInvoicesTbody tr').each(function () {
            let tr = $(this);
            let invId  = tr.data('invoice-id');
            let bayar  = parseMoney(tr.find('.edit-input-bayar').val());
            let potong = parseMoney(tr.find('.edit-input-potongan').val());
            let cat    = tr.find('.edit-input-catatan').val() || '';

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
        formData.append('nomor_pembayaran', $('#editNomorPembayaran').val());
        formData.append('supplier_id', supplierId);
        formData.append('kas_bank_id', kasBankId);
        formData.append('tanggal_bayar', tglBayar);
        formData.append('payment_method_id', $('#editPaymentMethodId').val() || '');
        formData.append('nomor_referensi', $('#editNomorReferensi').val() || '');
        formData.append('currency_id', $('#editCurrencyId').val() || '');
        formData.append('kurs', $('#editKurs').val() || 1);
        formData.append('keterangan', $('#editKeterangan').val() || '');
        formData.append('action_type', $('#editActionType').val());
        formData.append('remove_lampiran', $('#editRemoveLampiran').val() || '0');

        let lampiranFile = $('#editLampiran')[0].files[0] || $('#editLampiranCameraInput')[0].files[0];
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
            title: 'Menyimpan Perubahan...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: `/purchase-payments/${paymentId}/update`,
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
                $('#modalEditPayment').modal('hide');
                $('#tablePayments').DataTable().ajax.reload(null, false);
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || 'Gagal memperbarui pembayaran utang!';
                Swal.fire({ icon: 'error', title: 'Terjadi Kesalahan', text: msg });
            }
        });
    }

});
</script>
