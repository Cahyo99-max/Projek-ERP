<script>
    // Safe HTML Escape Helper
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    window.escapeHtml = escapeHtml;

    // Number & Currency Formatting Helpers
    function formatMoney(amount, code, symbol) {
        let num = parseFloat(amount) || 0;
        let currCode = code || 'IDR';
        let currSymbol = symbol || (currCode === 'USD' ? '$' : 'Rp');

        if (currCode === 'USD') {
            return currSymbol + ' ' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        } else {
            return currSymbol + ' ' + new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    }

    function formatNumberOnly(amount, code) {
        let num = parseFloat(amount) || 0;
        let currCode = code || 'IDR';

        if (currCode === 'USD') {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        } else {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    }

    function formatSmartKurs(val) {
        let num = parseFloat(val) || 1;
        if (num === 1 || Math.abs(num - 1.0) < 0.0001) {
            return '1';
        }
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 4
        }).format(num);
    }

    // High-Precision Number & Currency Formatting Helpers (Indonesian ERP Standard: . thousands, , decimal)
    window.formatNumber = function(num) {
        if (num === null || num === undefined || isNaN(num)) return '0,00';
        return parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    window.unformatNumber = function(str) {
        if (str === null || str === undefined || str === '') return 0;
        if (typeof str === 'number') return isNaN(str) ? 0 : str;
        let s = str.toString().trim();
        if (s === '') return 0;
        // Clean out currency symbols (Rp, $, etc.) and spaces, keep digits, separators, minus
        s = s.replace(/[^\d.,-]/g, '').trim();
        if (s === '' || s === '-') return 0;

        let hasComma = s.includes(',');
        let hasDot = s.includes('.');

        if (hasComma && hasDot) {
            let lastComma = s.lastIndexOf(',');
            let lastDot = s.lastIndexOf('.');
            if (lastDot > lastComma) {
                // US / Dollar format: comma is thousands, dot is decimal (e.g. 1,250.50 or 1,250,000.00)
                s = s.replace(/,/g, '');
            } else {
                // Indonesian / EU format: dot is thousands, comma is decimal (e.g. 1.250,50 or 1.250.000,00)
                s = s.replace(/\./g, '').replace(/,/g, '.');
            }
        } else if (hasComma) {
            // Only comma present -> decimal comma in Indonesian ERP (e.g. 1250,50 or 0,00)
            s = s.replace(/,/g, '.');
        } else if (hasDot) {
            // Only dot present:
            let dotCount = (s.match(/\./g) || []).length;
            if (dotCount > 1) {
                s = s.replace(/\./g, '');
            } else if (/^\d{1,3}\.\d{3}$/.test(s)) {
                // Exactly 3 digits like 50.000 -> Indonesian thousands separator
                s = s.replace(/\./g, '');
            }
            // Otherwise, keep dot as standard decimal point
        }

        let res = parseFloat(s);
        return isNaN(res) ? 0 : res;
    };

    // Prevent Bootstrap modal focus trap from hijacking Select2 searchbox focus
    if ($.fn && $.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
    }

    // Convert dot key to decimal comma automatically for .number-format inputs
    $(document).on('keydown', '.number-format', function(e) {
        if (e.key === '.' || e.key === 'Decimal') {
            let val = $(this).val();
            if (!val.includes(',')) {
                e.preventDefault();
                let start = this.selectionStart !== null ? this.selectionStart : val.length;
                let end = this.selectionEnd !== null ? this.selectionEnd : val.length;
                let newVal = val.substring(0, start) + ',' + val.substring(end);
                $(this).val(newVal);
                if (this.setSelectionRange) {
                    this.setSelectionRange(start + 1, start + 1);
                }
                $(this).trigger('input');
            } else {
                e.preventDefault();
            }
        }
    });

    // Auto-select or clear 0 on focus/click for convenient editing
    $(document).on('focus', '.number-format', function() {
        if ($(this).prop('readonly') || $(this).prop('disabled')) return;
        let val = ($(this).val() || '').trim();
        if (val === '0,00' || val === '0' || val === '0.00' || unformatNumber(val) === 0) {
            $(this).val('');
        } else {
            $(this).select();
        }
    });

    $(document).on('click', '.number-format', function() {
        if ($(this).prop('readonly') || $(this).prop('disabled')) return;
        let val = ($(this).val() || '').trim();
        if (val === '0,00' || val === '0' || val === '0.00' || unformatNumber(val) === 0) {
            $(this).val('');
        }
    });

    // Reformat with 2 decimals on blur
    $(document).on('blur', '.number-format', function() {
        let val = ($(this).val() || '').trim();
        if (val === '' || unformatNumber(val) === 0) {
            $(this).val('0,00');
        } else {
            $(this).val(formatNumber(unformatNumber(val)));
        }
        $(this).trigger('change');
    });

    // Real-time Thousands Separator (.) and Decimal Comma (,) Masking
    $(document).on('input', '.number-format', function() {
        let raw = $(this).val();
        if (raw === undefined || raw === null || raw === '') return;

        let cursor = this.selectionStart;
        let originalLen = raw.length;

        let parts = raw.split(',');
        let intStr = parts[0].replace(/\D/g, '');
        let decStr = parts.length > 1 ? parts.slice(1).join('').replace(/\D/g, '').substring(0, 4) : null;

        if (intStr === '' && decStr === null) {
            $(this).val('');
            return;
        }

        let cleanInt = intStr.replace(/^0+(?=\d)/, '');
        let formattedInt = cleanInt !== '' ? cleanInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : (intStr === '' ? '' : '0');
        let result = formattedInt;
        if (decStr !== null) {
            result += ',' + decStr;
        }
        $(this).val(result);

        if (cursor !== null && this.setSelectionRange) {
            let newLen = result.length;
            let newCursor = cursor + (newLen - originalLen);
            if (newCursor < 0) newCursor = 0;
            if (newCursor > newLen) newCursor = newLen;
            this.setSelectionRange(newCursor, newCursor);
        }
    });

    // Global Autocomplete Enforcer across Purchase Invoice module
    $(document).on('focus', 'input:not([type="hidden"]), textarea', function () {
        if ($(this).attr('autocomplete') !== 'off') {
            $(this).attr('autocomplete', 'off');
        }
    });

    $(document).on('select2:open', function () {
        setTimeout(function () {
            $('.select2-search__field').attr('autocomplete', 'off');
        }, 10);
    });

    $(document).ready(function () {
        // Matikan autocomplete browser secara menyeluruh di modul faktur pembelian
        $('form').attr('autocomplete', 'off');
        $('input:not([type="hidden"]), textarea').attr('autocomplete', 'off');

        $(document).on('show.bs.modal shown.bs.modal', function (e) {
            $(e.target).find('form').attr('autocomplete', 'off');
            $(e.target).find('input:not([type="hidden"]), textarea').attr('autocomplete', 'off');
        });

        // 1. DataTables Server-Side List View
        var table = $('#tableInvoice').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('purchase-invoices.index') }}",
                data: function (d) {
                    // Kirim parameter Filter Pencarian (modal) ke server
                    $.each(window.invActiveFilterParams || {}, function (k, v) {
                        if (v !== '' && v !== null && v !== undefined) d[k] = v;
                    });
                }
            },
            columns: [
                { 
                    data: 'DT_RowIndex', 
                    name: 'DT_RowIndex', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-center text-muted small' 
                },
                { 
                    data: 'nomor_faktur', 
                    name: 'purchase_invoices.nomor_faktur', 
                    className: 'fw-bold' 
                },
                { 
                    data: 'tanggal_faktur_fmt', 
                    name: 'purchase_invoices.tanggal_faktur' 
                },
                { 
                    data: 'tanggal_jatuh_tempo_fmt', 
                    name: 'purchase_invoices.tanggal_jatuh_tempo',
                    render: function(data) {
                        return data || '-';
                    }
                },
                { 
                    data: 'supplier_name', 
                    name: 'supplier_name', 
                    className: 'fw-semibold' 
                },
                { 
                    data: 'grand_total_fmt', 
                    name: 'purchase_invoices.grand_total', 
                    className: 'text-end fw-bold text-body' 
                },
                { 
                    data: 'sisa_hutang_fmt', 
                    name: 'purchase_invoices.grand_total', 
                    orderable: false,
                    className: 'text-end' 
                },
                { 
                    data: 'status_badge', 
                    name: 'purchase_invoices.status', 
                    className: 'text-center' 
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-center' 
                }
            ],
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[2, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari nomor faktur / supplier...",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                zeroRecords: "Tidak ada data faktur yang sesuai",
                emptyTable: "Belum ada data faktur pembelian",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>'
                }
            }
        });

        // ===== FILTER PENCARIAN (MODAL) =====
        window.invActiveFilterParams = {};
        const $filterForm = $('#formFilterInvoice');
        const $filterModal = $('#modalFilterInvoice');

        const PILL_LABELS = {
            f_status: { Draft: 'Draft', Approval: 'Menunggu Approval', Posted: 'Posted', Rejected: 'Ditolak', Cancelled: 'Dibatalkan' },
            f_payment: { belum: 'Belum Bayar', sebagian: 'Sebagian', lunas: 'Lunas' },
            f_due_state: { overdue: 'Lewat Jatuh Tempo', today: 'Jatuh Tempo Hari Ini', week: '7 Hari ke Depan' }
        };

        function fmtDateId(iso) {
            if (!iso) return '';
            const p = iso.split('-');
            return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : iso;
        }
        function toIso(dt) {
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const dd = String(dt.getDate()).padStart(2, '0');
            return dt.getFullYear() + '-' + m + '-' + dd;
        }
        function escHtml(s) { return $('<div>').text(s == null ? '' : s).html(); }

        // Init Select2 supplier di dalam modal filter
        if ($.fn.select2) {
            $('#f_supplier_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $filterModal,
                placeholder: 'Semua Supplier',
                allowClear: true
            });
        }

        // Quick date presets
        $filterModal.on('click', '.inv-quick-date button', function () {
            const $wrap = $(this).closest('.inv-quick-date');
            const now = new Date();
            let from, to;
            switch ($(this).data('range')) {
                case 'today': from = to = now; break;
                case '7d': from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6); to = now; break;
                case 'month': from = new Date(now.getFullYear(), now.getMonth(), 1); to = new Date(now.getFullYear(), now.getMonth() + 1, 0); break;
                case 'lastmonth': from = new Date(now.getFullYear(), now.getMonth() - 1, 1); to = new Date(now.getFullYear(), now.getMonth(), 0); break;
                case 'nextmonth': from = new Date(now.getFullYear(), now.getMonth() + 1, 1); to = new Date(now.getFullYear(), now.getMonth() + 2, 0); break;
                case 'year': from = new Date(now.getFullYear(), 0, 1); to = new Date(now.getFullYear(), 11, 31); break;
            }
            if (from && to) {
                $($wrap.data('from')).val(toIso(from));
                $($wrap.data('to')).val(toIso(to));
            }
        });

        function collectFilterParams() {
            const params = {};
            $.each($filterForm.serializeArray(), function (_, f) {
                if (f.value !== '') params[f.name] = f.value;
            });
            return params;
        }

        function renderActiveFilters() {
            const p = window.invActiveFilterParams;
            const chips = [];
            const addChip = (keys, label, value) => chips.push(
                '<span class="inv-chip">' + escHtml(label) + ': <b>' + escHtml(value) + '</b>' +
                '<button type="button" class="inv-chip-x" data-keys="' + keys.join(',') + '" title="Hapus filter"><i class="bi bi-x-lg"></i></button></span>'
            );

            if (p.f_supplier_id) addChip(['f_supplier_id'], 'Supplier', $('#f_supplier_id option:selected').text().trim());
            if (p.f_status) addChip(['f_status'], 'Status', PILL_LABELS.f_status[p.f_status] || p.f_status);
            if (p.f_payment) addChip(['f_payment'], 'Pembayaran', PILL_LABELS.f_payment[p.f_payment] || p.f_payment);
            if (p.f_tgl_from || p.f_tgl_to) addChip(['f_tgl_from', 'f_tgl_to'], 'Tgl Faktur', (fmtDateId(p.f_tgl_from) || '…') + ' – ' + (fmtDateId(p.f_tgl_to) || '…'));
            if (p.f_due_from || p.f_due_to) addChip(['f_due_from', 'f_due_to'], 'Jatuh Tempo', (fmtDateId(p.f_due_from) || '…') + ' – ' + (fmtDateId(p.f_due_to) || '…'));
            if (p.f_due_state) addChip(['f_due_state'], 'Kondisi', PILL_LABELS.f_due_state[p.f_due_state] || p.f_due_state);

            $('#invActiveFilterChips').html(chips.join(''));
            $('#invActiveFilters').toggleClass('d-none', chips.length === 0);
            $('#invFilterCount').text(chips.length).toggleClass('d-none', chips.length === 0);
            $('#btnOpenInvFilter').toggleClass('is-active', chips.length > 0);
        }

        function resetFilterForm(keys) {
            const all = !keys;
            const has = k => all || keys.indexOf(k) !== -1;
            if (has('f_supplier_id')) $('#f_supplier_id').val('').trigger('change');
            ['f_status', 'f_payment', 'f_due_state'].forEach(function (n) {
                if (has(n)) $filterForm.find('input[name="' + n + '"][value=""]').prop('checked', true);
            });
            ['f_tgl_from', 'f_tgl_to', 'f_due_from', 'f_due_to'].forEach(function (n) {
                if (has(n)) $('#' + n).val('');
            });
        }

        function applyFilters() {
            window.invActiveFilterParams = collectFilterParams();
            renderActiveFilters();
            table.ajax.reload();
        }

        $filterForm.on('submit', function (e) {
            e.preventDefault();
            const p = collectFilterParams();
            if ((p.f_tgl_from && p.f_tgl_to && p.f_tgl_from > p.f_tgl_to) || (p.f_due_from && p.f_due_to && p.f_due_from > p.f_due_to)) {
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Rentang tanggal tidak valid', text: 'Tanggal awal tidak boleh melebihi tanggal akhir.' });
                } else {
                    alert('Tanggal awal tidak boleh melebihi tanggal akhir.');
                }
                return;
            }
            applyFilters();
            bootstrap.Modal.getOrCreateInstance($filterModal[0]).hide();
        });

        $('#btnResetInvFilter').on('click', function () {
            resetFilterForm();
        });

        $('#btnClearAllInvFilter').on('click', function () {
            resetFilterForm();
            applyFilters();
        });

        $(document).on('click', '.inv-chip-x', function () {
            resetFilterForm(String($(this).data('keys')).split(','));
            applyFilters();
        });

        // Saat modal dibuka tanpa diterapkan, kembalikan form ke filter yang sedang aktif
        $filterModal.on('show.bs.modal', function () {
            const p = window.invActiveFilterParams;
            resetFilterForm();
            if (p.f_supplier_id) $('#f_supplier_id').val(p.f_supplier_id).trigger('change');
            ['f_status', 'f_payment', 'f_due_state'].forEach(function (n) {
                if (p[n]) $filterForm.find('input[name="' + n + '"][value="' + p[n] + '"]').prop('checked', true);
            });
            ['f_tgl_from', 'f_tgl_to', 'f_due_from', 'f_due_to'].forEach(function (n) {
                if (p[n]) $('#' + n).val(p[n]);
            });
        });

        // ===== REAL-TIME LIVE UPDATE ENGINE (< 1 DETIK) =====
        let isSilentReload = false;
        let isRealtimeFetching = false;
        const REALTIME_POLL_INTERVAL = 900; // 900ms (< 1 detik)
        let realtimeTimer = null;

        function updateKpiDisplay(kpi) {
            if (!kpi) return;

            // 1. Total Faktur
            if (kpi.totalInvoiceCount !== undefined) {
                $('#kpi_total_invoice_count').text(new Intl.NumberFormat('id-ID').format(kpi.totalInvoiceCount));
            }
            if (kpi.countIdr !== undefined) {
                $('#kpi_count_idr_val').text(kpi.countIdr);
            }
            if (kpi.countForeign !== undefined) {
                $('#kpi_count_foreign_val').text(kpi.countForeign);
                if (kpi.countForeign > 0) {
                    $('#kpi_count_foreign_badge').removeClass('d-none');
                } else {
                    $('#kpi_count_foreign_badge').addClass('d-none');
                }
            }

            // 2. Hutang Belum Lunas (Unpaid)
            if (kpi.unpaidIdr !== undefined) {
                $('#kpi_unpaid_idr').text('Rp ' + formatNumber(kpi.unpaidIdr));
            }
            if (kpi.unpaidForeign !== undefined) {
                let fVal = parseFloat(kpi.unpaidForeign) || 0;
                let fSym = kpi.unpaidForeignSymbol || '$';
                let fCode = kpi.unpaidForeignCode || 'USD';
                $('#kpi_unpaid_foreign_code').text(fCode);
                $('#kpi_unpaid_foreign_symbol').text(fSym);
                $('#kpi_unpaid_foreign_val').html(escapeHtml(fSym) + ' ' + formatNumber(fVal));

                if (fVal > 0) {
                    $('#kpi_unpaid_foreign_row').css('opacity', '1');
                    let estHtml = '<div class="d-flex align-items-center justify-content-between" style="font-size: 0.73rem;">' +
                        '<span class="text-white-50"><i class="bi bi-calculator me-1"></i>Estimasi Total (Kurs):</span>' +
                        '<span class="fw-bold text-white font-monospace" id="kpi_total_unpaid_amount">Rp ' + formatNumber(kpi.totalUnpaidAmount || 0) + '</span>' +
                    '</div>';
                    $('#kpi_unpaid_sub').html(estHtml);
                } else {
                    $('#kpi_unpaid_foreign_row').css('opacity', '0.65');
                    $('#kpi_unpaid_sub').html('<div class="d-flex align-items-center justify-content-between" style="font-size: 0.73rem;"><span class="text-white-50"><i class="bi bi-clock-history me-1"></i>Draft & Menunggu Pembayaran</span><span class="fw-bold text-white font-monospace d-none" id="kpi_total_unpaid_amount">Rp ' + formatNumber(kpi.totalUnpaidAmount || 0) + '</span></div>');
                }
            }
            if (kpi.totalUnpaidAmount !== undefined) {
                $('#kpi_total_unpaid_amount').text('Rp ' + formatNumber(kpi.totalUnpaidAmount));
            }

            // 3. Pembayaran Bulan Ini
            if (kpi.paidThisMonthIdr !== undefined || kpi.totalPaidThisMonth !== undefined) {
                let paidIdr = kpi.paidThisMonthIdr !== undefined ? kpi.paidThisMonthIdr : kpi.totalPaidThisMonth;
                $('#kpi_total_paid_this_month').text('Rp ' + formatNumber(paidIdr));
            }
            if (kpi.paidThisMonthForeign !== undefined) {
                let pFVal = parseFloat(kpi.paidThisMonthForeign) || 0;
                let pFSym = kpi.paidThisMonthForeignSymbol || '$';
                let pFCode = kpi.paidThisMonthForeignCode || 'USD';
                $('#kpi_paid_foreign_code').text(pFCode);
                $('#kpi_paid_foreign_symbol').text(pFSym);
                $('#kpi_paid_foreign_val').html(escapeHtml(pFSym) + ' ' + formatNumber(pFVal));

                if (pFVal > 0) {
                    $('#kpi_paid_foreign_row').css('opacity', '1');
                } else {
                    $('#kpi_paid_foreign_row').css('opacity', '0.65');
                }
            }
        }

        // Silent Reload Handler to eliminate blinking / flickering on background updates
        table.on('preXhr.dt', function() {
            isRealtimeFetching = true;
        });

        table.on('processing.dt', function(e, settings, processing) {
            let $proc = $('#tableInvoice_processing');
            if (isSilentReload) {
                $proc.css({ 'display': 'none', 'visibility': 'hidden' });
            } else {
                $proc.css({ 'visibility': 'visible' });
            }
        });

        table.on('xhr.dt', function(e, settings, json, xhr) {
            isRealtimeFetching = false;
            isSilentReload = false;
            if (json && json.kpi) {
                updateKpiDisplay(json.kpi);
            }
        });

        function canRunRealtimePoll() {
            // Cek apakah tab browser sedang aktif
            if (document.hidden) return false;

            // Cek apakah ada modal Bootstrap yang sedang terbuka (Create/Edit/View/Jurnal/Browse)
            if ($('.modal.show').length > 0) return false;

            // Cek apakah ada SweetAlert konfirmasi/alert yang sedang terbuka
            if ($('.swal2-container').length > 0) return false;

            // Cek apakah dropdown Select2 sedang terbuka
            if ($('.select2-container--open').length > 0) return false;

            // Cek apakah user sedang fokus mengetik di input pencarian DataTables
            let activeEl = document.activeElement;
            if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
                return false;
            }

            // Cek apakah request sebelumnya masih berjalan
            if (isRealtimeFetching) return false;

            return true;
        }

        function triggerRealtimePoll() {
            if (canRunRealtimePoll()) {
                isSilentReload = true;
                table.ajax.reload(function() {
                    isSilentReload = false;
                }, false);
            }
        }

        // Start background polling loop (< 1 detik)
        if (realtimeTimer) clearInterval(realtimeTimer);
        realtimeTimer = setInterval(triggerRealtimePoll, REALTIME_POLL_INTERVAL);

        // Resume polling immediately when tab becomes visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                triggerRealtimePoll();
            }
        });

        // Dual Horizontal Scroll Sync for Touch Mobile & Desktop Table
        const topScroll = document.getElementById('invTopScrollContainer');
        const topInner = document.getElementById('invTopScrollInner');
        const tableWrapper = document.getElementById('invTableScrollWrapper');
        const tableEl = document.getElementById('tableInvoice');

        function syncTableScrollWidth() {
            if (topInner && tableEl) {
                topInner.style.width = tableEl.scrollWidth + 'px';
            }
        }

        if (topScroll && tableWrapper) {
            topScroll.addEventListener('scroll', function() {
                tableWrapper.scrollLeft = topScroll.scrollLeft;
            });
            tableWrapper.addEventListener('scroll', function() {
                topScroll.scrollLeft = tableWrapper.scrollLeft;
            });
        }

        table.on('draw', function() {
            setTimeout(syncTableScrollWidth, 100);
        });
        window.addEventListener('resize', syncTableScrollWidth);
        setTimeout(syncTableScrollWidth, 250);

        // Datatable Approve & Post Jurnal Action (Atasan / Approver)
        $(document).on('click', '.btn-approve-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Setujui & Posting Jurnal (ACC)?',
                text: 'Faktur pembelian ini akan disetujui (ACC) dan otomatis menerbitkan Jurnal Utang Usaha ke Akuntansi. Notifikasi konfirmasi akan dikirimkan ke pembuat faktur via WhatsApp.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Setujui & Posting',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/purchase-invoices/' + id + '/approve', { _token: '{{ csrf_token() }}' }, function (res) {
                        table.ajax.reload();
                        Swal.fire('Berhasil Disetujui!', res.message, 'success');
                    }).fail(function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menyetujui faktur.', 'error');
                    });
                }
            });
        });

        // Datatable Reject Action (Atasan / Approver)
        $(document).on('click', '.btn-reject-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Tolak Pengajuan Faktur?',
                text: 'Masukkan alasan penolakan agar pembuat faktur dapat memperbaikinya:',
                input: 'textarea',
                inputPlaceholder: 'Contoh: Nomor faktur supplier tidak sesuai dokumen fisik...',
                inputAttributes: {
                    'aria-label': 'Alasan penolakan faktur'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tolak Faktur',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'Alasan penolakan wajib diisi!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/purchase-invoices/' + id + '/reject', {
                        _token: '{{ csrf_token() }}',
                        alasan: result.value
                    }, function (res) {
                        table.ajax.reload();
                        Swal.fire('Pengajuan Ditolak', res.message, 'info');
                    }).fail(function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menolak faktur.', 'error');
                    });
                }
            });
        });

        // Datatable Send / Resend WhatsApp Notification
        $(document).on('click', '.btn-wa-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Kirim Ulang WhatsApp ke Atasan?',
                text: 'Notifikasi rincian tagihan faktur akan dikirimkan kembali ke WhatsApp Atasan / Approver.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#25D366',
                confirmButtonText: '<i class="bi bi-whatsapp me-1"></i> Kirim WhatsApp',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/purchase-invoices/' + id + '/send-whatsapp', { _token: '{{ csrf_token() }}' }, function (res) {
                        table.ajax.reload();
                        Swal.fire('Terkirim!', res.message, 'success');
                    }).fail(function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal mengirim pesan WhatsApp.', 'error');
                    });
                }
            });
        });

        // Datatable Submit Draft to Superior Action
        $(document).on('click', '.btn-submit-approval-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Ajukan Faktur ke Atasan?',
                text: 'Status faktur akan diubah menjadi Menunggu Persetujuan dan notifikasi WhatsApp akan dikirim ke Atasan untuk persetujuan (ACC).',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('/purchase-invoices/' + id + '/send-whatsapp', { _token: '{{ csrf_token() }}' }, function (res) {
                        table.ajax.reload();
                        Swal.fire('Berhasil Diajukan!', res.message, 'success');
                    }).fail(function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal mengajukan faktur.', 'error');
                    });
                }
            });
        });

        // Datatable Delete Action
        $(document).on('click', '.btn-delete-invoice', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Faktur Pembelian?',
                text: 'Faktur yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/purchase-invoices/' + id + '/delete',
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            table.ajax.reload();
                            Swal.fire('Berhasil!', res.message, 'success');
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus faktur.', 'error');
                        }
                    });
                }
            });
        });

        // ===== CANCEL / VOID INVOICE ACTION (PEMBATALAN TRANSAKSI FAKTUR PEMBELIAN) =====
        window.promptCancelInvoice = function (id, nomor) {
            nomor = nomor || 'Faktur Pembelian';

            // Proteksi Keamanan Akuntansi: Cegah pembatalan jika faktur memiliki transaksi Pembayaran Utang yang masih aktif
            if (typeof currentViewInvoice !== 'undefined' && currentViewInvoice && currentViewInvoice.id == id && currentViewInvoice.has_active_payment) {
                let vouchers = currentViewInvoice.active_payment_vouchers || [];
                let vListHtml = vouchers.map(v => `<li class="font-monospace fw-bold text-dark py-0.5"><i class="bi bi-receipt me-1 text-primary"></i>${escapeHtml(v.nomor_pembayaran)} <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-1.5 py-0.5">${escapeHtml(v.status)}</span></li>`).join('');

                Swal.fire({
                    title: 'Tidak Dapat Dibatalkan!',
                    html: `Faktur Pembelian <strong>${escapeHtml(nomor)}</strong> tidak dapat dibatalkan karena telah memiliki transaksi <strong>Pembayaran Utang</strong> yang masih aktif:<br><br>` +
                          `<div class="alert alert-warning text-start py-2 px-3 small border border-warning-subtle rounded-3 mb-2">` +
                          `  <ul class="mb-0 ps-3">${vListHtml || '<li>Pembayaran Utang aktif terdeteksi</li>'}</ul>` +
                          `</div>` +
                          `<div class="small text-danger fw-semibold"><i class="bi bi-shield-exclamation me-1"></i>Demi keamanan akuntansi, Anda wajib <strong>membatalkan transaksi Pembayaran Utang</strong> di atas terlebih dahulu!</div>`,
                    icon: 'warning',
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Saya Mengerti',
                });
                return;
            }

            // Mencegah Bootstrap Modal focus trap mencuri fokus dari SweetAlert input
            const stopFocusTrap = (e) => {
                if (e.target && (e.target.closest('.swal2-container') || e.target.classList.contains('swal2-input') || e.target.classList.contains('swal2-textarea'))) {
                    e.stopPropagation();
                }
            };
            document.addEventListener('focusin', stopFocusTrap, true);
            document.addEventListener('focus', stopFocusTrap, true);

            Swal.fire({
                title: 'Batalkan Transaksi Faktur (Void)?',
                html: `Masukkan alasan pembatalan faktur <strong>${escapeHtml(nomor)}</strong>:<br>` +
                      `<small class="text-muted d-block mb-2">Jurnal pembalik (reversal) akan diterbitkan ke Buku Besar dan mutasi DP (jika ada) akan dikembalikan.</small>` +
                      `<div class="d-flex justify-content-between align-items-center mb-1">` +
                      `  <span class="small text-muted"><i class="bi bi-shield-check text-primary me-1"></i>Alasan Pembatalan (Maks 255 Karakter):</span>` +
                      `  <span id="swalCancelInvoiceCharCount" class="small text-muted font-monospace fw-semibold">0/255</span>` +
                      `</div>`,
                input: 'textarea',
                inputPlaceholder: 'Tuliskan alasan pembatalan (maks 255 karakter)...',
                inputAttributes: {
                    maxlength: '255',
                    'aria-label': 'Tuliskan alasan pembatalan (maks 255 karakter)',
                    style: 'resize: none; height: 80px; font-size: 0.9rem;'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-x-octagon me-1"></i> Batalkan Faktur',
                cancelButtonText: 'Tutup',
                reverseButtons: true,
                didOpen: () => {
                    $('#modalDetailInvoice').removeAttr('tabindex');
                    const textarea = Swal.getInput();
                    if (textarea) {
                        textarea.focus();
                        $(textarea).on('input keyup paste', function () {
                            let len = $(this).val().length;
                            $('#swalCancelInvoiceCharCount').text(`${len}/255`);
                            if (len >= 255) {
                                $('#swalCancelInvoiceCharCount').addClass('text-danger fw-bold').removeClass('text-muted');
                            } else {
                                $('#swalCancelInvoiceCharCount').removeClass('text-danger fw-bold').addClass('text-muted');
                            }
                        });
                    }
                },
                willClose: () => {
                    document.removeEventListener('focusin', stopFocusTrap, true);
                    document.removeEventListener('focus', stopFocusTrap, true);
                    $('#modalDetailInvoice').attr('tabindex', '-1');
                },
                preConfirm: (alasan) => {
                    let trimmed = (alasan || '').trim();
                    if (!trimmed) {
                        Swal.showValidationMessage('Alasan pembatalan wajib diisi!');
                        return false;
                    }
                    if (trimmed.length > 30) {
                        Swal.showValidationMessage('Alasan pembatalan maksimal 30 karakter!');
                        return false;
                    }
                    return trimmed;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Membatalkan Transaksi...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: `/purchase-invoices/${id}/cancel`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            alasan_pembatalan: result.value
                        },
                        success: function (res) {
                            if ($('#modalDetailInvoice').hasClass('show')) {
                                $('#modalDetailInvoice').modal('hide');
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Faktur Dibatalkan!',
                                text: res.message,
                                timer: 2200,
                                showConfirmButton: false,
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON?.message || 'Gagal membatalkan Faktur Pembelian!';
                            Swal.fire({
                                icon: 'warning',
                                title: 'Pembatalan Ditolak Sistem',
                                html: `<div class="text-start small">${escapeHtml(msg)}</div>`,
                                confirmButtonColor: '#0d6efd',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });
        };

        $(document).on('click', '.btn-cancel-invoice', function () {
            let id = $(this).data('id');
            let nomor = $(this).data('nomor') || ('Faktur #' + id);
            window.promptCancelInvoice(id, nomor);
        });

        // Expose table reload helper
        window.reloadInvoiceTable = function() {
            table.ajax.reload(null, false);
        };
    });
</script>
