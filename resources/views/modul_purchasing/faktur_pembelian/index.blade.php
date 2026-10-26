<x-layouts>

<x-slot:title>Faktur Pembelian (Purchase Invoice)</x-slot:title>

@push('styles')
<!-- Select2 Searchable Dropdown CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- Faktur Pembelian Custom CSS -->
<link href="{{ asset('css/faktur_pembelian.css') }}?v={{ file_exists(public_path('css/faktur_pembelian.css')) ? filemtime(public_path('css/faktur_pembelian.css')) : time() }}" rel="stylesheet" />
@endpush

<div class="container-fluid py-2 py-md-3 inv-page-container">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3 mb-md-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">Faktur Pembelian</h4>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
            @can('tambah-purchase-invoice')
            <button type="button" class="btn btn-primary fw-semibold w-100 w-md-auto rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahInvoice">
                <i class="bi bi-plus-lg me-1"></i> Buat Faktur Pembelian
            </button>
            @endcan
        </div>
    </div>

    <!-- SUMMARY KPI CARDS (BENTO GLOW CINEMATIC WITH MULTI-CURRENCY BREAKDOWN) -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-blue text-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="inv-cine-title">TOTAL FAKTUR</div>
                    <div class="inv-cine-icon">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
                <div class="inv-cine-value" id="kpi_total_invoice_count">{{ number_format($totalInvoiceCount ?? 0) }}</div>
                <div class="inv-cine-sub d-flex flex-wrap gap-1 align-items-center mt-1" id="kpi_total_invoice_sub">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;" id="kpi_count_idr_badge">
                        <i class="bi bi-tag-fill me-1"></i><span id="kpi_count_idr_val">{{ $countIdr ?? 0 }}</span> IDR
                    </span>
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-0.5 {{ ($countForeign ?? 0) > 0 ? '' : 'd-none' }}" style="font-size: 0.72rem;" id="kpi_count_foreign_badge">
                        <i class="bi bi-currency-exchange me-1"></i><span id="kpi_count_foreign_val">{{ $countForeign ?? 0 }}</span> USD / Valas
                    </span>
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-amber text-white h-100 position-relative overflow-hidden d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="inv-cine-title">HUTANG BELUM LUNAS (UNPAID)</div>
                        <div class="inv-cine-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>

                    <!-- DUAL CURRENCY VALUE DISPLAY -->
                    <div class="d-flex flex-column gap-1 mb-2">
                        <!-- Nominal Utang Rupiah (IDR) -->
                        <div class="d-flex align-items-baseline justify-content-between">
                            <span class="badge bg-black bg-opacity-25 text-white-50 rounded px-2 py-0.5" style="font-size: 0.7rem; font-weight: 600;">
                                <i class="bi bi-cash-stack me-1 text-warning"></i>IDR
                            </span>
                            <div class="inv-cine-value mb-0 text-end" id="kpi_unpaid_idr" style="font-size: 1.35rem; letter-spacing: -0.02em;">
                                Rp {{ number_format($unpaidIdr ?? 0, 2, ',', '.') }}
                            </div>
                        </div>

                        <!-- Nominal Utang Valas (Mata Uang Asing) -->
                        <div class="d-flex align-items-baseline justify-content-between pt-1 border-top border-white border-opacity-15" id="kpi_unpaid_foreign_row" style="{{ ($unpaidForeign ?? 0) > 0 ? '' : 'opacity: 0.65;' }}">
                            <span class="badge bg-white bg-opacity-25 text-white rounded px-2 py-0.5 font-monospace" style="font-size: 0.7rem; font-weight: 600;">
                                <i class="bi bi-currency-exchange me-1"></i><span id="kpi_unpaid_foreign_code">{{ $unpaidForeignCode ?? 'USD' }}</span>
                            </span>
                            <div class="fw-bold font-monospace text-end" id="kpi_unpaid_foreign_val" style="font-size: 1.15rem; color: #fef08a;">
                                <span id="kpi_unpaid_foreign_symbol">{{ $unpaidForeignSymbol ?? '$' }}</span> {{ number_format($unpaidForeign ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Subtitle: Estimasi Total Setelah Kurs atau Status -->
                <div class="inv-cine-sub pt-1 border-top border-white border-opacity-10" id="kpi_unpaid_sub">
                    @if(($unpaidForeign ?? 0) > 0)
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 0.73rem;">
                        <span class="text-white-50"><i class="bi bi-calculator me-1"></i>Estimasi Total (Kurs):</span>
                        <span class="fw-bold text-white font-monospace" id="kpi_total_unpaid_amount">Rp {{ number_format($totalUnpaidAmount ?? 0, 2, ',', '.') }}</span>
                    </div>
                    @else
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 0.73rem;">
                        <span class="text-white-50"><i class="bi bi-clock-history me-1"></i>Draft & Menunggu Pembayaran</span>
                        <span class="fw-bold text-white font-monospace d-none" id="kpi_total_unpaid_amount">Rp {{ number_format($totalUnpaidAmount ?? 0, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-emerald text-white h-100 position-relative overflow-hidden d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="inv-cine-title">PEMBAYARAN BULAN INI</div>
                        <div class="inv-cine-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                    </div>

                    <!-- DUAL CURRENCY VALUE DISPLAY -->
                    <div class="d-flex flex-column gap-1 mb-2">
                        <div class="d-flex align-items-baseline justify-content-between">
                            <span class="badge bg-black bg-opacity-25 text-white-50 rounded px-2 py-0.5" style="font-size: 0.7rem; font-weight: 600;">
                                <i class="bi bi-cash-stack me-1 text-success-emphasis"></i>IDR
                            </span>
                            <div class="inv-cine-value mb-0 text-end" id="kpi_total_paid_this_month" style="font-size: 1.35rem; letter-spacing: -0.02em;">
                                Rp {{ number_format($paidThisMonthIdr ?? ($totalPaidThisMonth ?? 0), 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="d-flex align-items-baseline justify-content-between pt-1 border-top border-white border-opacity-15" id="kpi_paid_foreign_row" style="{{ ($paidThisMonthForeign ?? 0) > 0 ? '' : 'opacity: 0.65;' }}">
                            <span class="badge bg-white bg-opacity-25 text-white rounded px-2 py-0.5 font-monospace" style="font-size: 0.7rem; font-weight: 600;">
                                <i class="bi bi-currency-exchange me-1"></i><span id="kpi_paid_foreign_code">{{ $paidThisMonthForeignCode ?? 'USD' }}</span>
                            </span>
                            <div class="fw-bold font-monospace text-end" id="kpi_paid_foreign_val" style="font-size: 1.15rem; color: #a7f3d0;">
                                <span id="kpi_paid_foreign_symbol">{{ $paidThisMonthForeignSymbol ?? '$' }}</span> {{ number_format($paidThisMonthForeign ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inv-cine-sub pt-1 border-top border-white border-opacity-10" id="kpi_paid_this_month_sub">
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 0.73rem;">
                        <span class="text-white-50"><i class="bi bi-calendar3 me-1"></i>Periode {{ $currentMonthName ?? \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                    </div>
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
    </div>

    <!-- MAIN DATATABLE LIST -->
    <div class="erp-card">
        <div class="erp-card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-primary"></i>
                <h6 class="fw-bold mb-0 text-body">Daftar Tagihan Supplier (AP Invoices)</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-none d-lg-block text-muted small me-1">
                    <i class="bi bi-info-circle me-1"></i> Geser horizontal untuk melihat rincian kolom
                </div>
                <button type="button" class="btn inv-filter-trigger position-relative" id="btnOpenInvFilter" data-bs-toggle="modal" data-bs-target="#modalFilterInvoice" title="Filter Pencarian">
                    <i class="bi bi-funnel-fill"></i>
                    <span class="d-none d-sm-inline ms-1">Filter</span>
                    <span class="inv-filter-count d-none" id="invFilterCount">0</span>
                </button>
            </div>
        </div>
        <div class="card-body p-2 p-md-3">

            <!-- Active Filter Chips -->
            <div class="inv-active-filters d-none" id="invActiveFilters">
                <span class="inv-active-filters-label"><i class="bi bi-funnel me-1"></i>Filter aktif:</span>
                <div class="d-flex flex-wrap gap-1" id="invActiveFilterChips"></div>
                <button type="button" class="btn btn-link btn-sm inv-clear-all-filter" id="btnClearAllInvFilter">
                    <i class="bi bi-x-circle me-1"></i>Reset semua
                </button>
            </div>
            
            <!-- Mobile Scroll Indicator Badge -->
            <div class="d-md-none text-center my-1">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace" style="font-size:0.72rem;">
                    <i class="bi bi-arrows-expand me-1"></i>Geser tabel ke samping
                </span>
            </div>

            <!-- Top Dual-Scroll for Touch Mobile & Desktop -->
            <div class="inv-top-scroll-container mb-1" id="invTopScrollContainer">
                <div class="inv-top-scroll-inner" id="invTopScrollInner"></div>
            </div>

            <div class="inv-table-scroll-wrapper" id="invTableScrollWrapper">
                <table class="table align-middle table-hover w-100 inv-wide-table" id="tableInvoice">
                    <thead>
                        <tr>
                            <th width="3%" class="text-center">NO</th>
                            <th width="16%">NO. FAKTUR</th>
                            <th width="10%">TANGGAL</th>
                            <th width="11%">JATUH TEMPO</th>
                            <th width="20%">SUPPLIER</th>
                            <th width="14%" class="text-end">TOTAL TAGIHAN</th>
                            <th width="14%" class="text-end">SISA HUTANG</th>
                            <th width="6%" class="text-center">STATUS</th>
                            <th width="6%" class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ===== INCLUDE MODALS ===== -->
@include('modul_purchasing.faktur_pembelian.partials.modal_create')
@include('modul_purchasing.faktur_pembelian.partials.modal_edit')
@include('modul_purchasing.faktur_pembelian.partials.modal_view')
@include('modul_purchasing.faktur_pembelian.partials.modal_jurnal')
@include('modul_purchasing.faktur_pembelian.partials.modal_browse_gr')
@include('modul_purchasing.faktur_pembelian.partials.modal_browse_po')
@include('modul_purchasing.faktur_pembelian.partials.modal_preview_lampiran')
@include('modul_purchasing.faktur_pembelian.partials.modal_filter')

@push('scripts')
    <!-- Select2 Searchable Dropdown Library -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Shared Searchable Pajak (PPN) Select2 Initializer
        window.initPajakSelect2 = function($elem, $modal, opts) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            opts = opts || {};
            $elem.each(function() {
                let $this = $(this);
                let targetParent = ($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.off('select2:open.pjk select2:select.pjk');

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: opts.placeholder || 'Non PPN (0%)',
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0, // Always show search field so user can type to search
                    closeOnSelect: true
                });

                $this.on('select2:open.pjk', function() {
                    let data = $(this).data('select2');
                    if (data && data.$dropdown) {
                        data.$dropdown.css('z-index', 1065);
                    }
                    setTimeout(function() {
                        let searchField = document.querySelector('.select2-container--open .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', opts.searchPlaceholder || 'Cari PPN (misal: 11%, 12%)...');
                            searchField.focus();
                        }
                    }, 40);
                });

                $this.on('select2:select.pjk', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Shared Searchable Syarat Pembayaran (Termin) Select2 Initializer
        window.initTerminSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let targetParent = ($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.off('select2:open.trm select2:select.trm');

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: 'Pilih Syarat Pembayaran...',
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true
                });

                $this.on('select2:open.trm', function() {
                    let data = $(this).data('select2');
                    if (data && data.$dropdown) {
                        data.$dropdown.css('z-index', 1065);
                    }
                    setTimeout(function() {
                        let searchField = document.querySelector('.select2-container--open .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Cari termin (misal: 30 Hari, Net, Cash)...');
                            searchField.focus();
                        }
                    }, 40);
                });

                $this.on('select2:select.trm', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Shared Searchable Akun Uang Muka (COA) Select2 Initializer
        window.initUangMukaCoaSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let targetParent = ($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.off('select2:open.dp select2:select.dp');

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: 'Cari & Pilih Akun Uang Muka...',
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true,
                    matcher: function(params, data) {
                        // Empty query -> show all
                        if (!params.term || $.trim(params.term) === '') {
                            return data;
                        }
                        if (typeof data.text === 'undefined') {
                            return null;
                        }
                        // Multi-word flexible case-insensitive matching across code and name
                        let term = params.term.toLowerCase().trim();
                        let text = data.text.toLowerCase();
                        let words = term.split(/\s+/).filter(Boolean);
                        let matched = words.every(function(word) {
                            return text.indexOf(word) > -1;
                        });
                        return matched ? data : null;
                    },
                    templateResult: function(item) {
                        if (!item.id) return item.text;
                        let text = item.text;
                        let sepIdx = text.indexOf(' - ');
                        if (sepIdx > -1) {
                            let code = text.substring(0, sepIdx).trim();
                            let name = text.substring(sepIdx + 3).trim();
                            let safeCode = $('<div>').text(code).html();
                            let safeName = $('<div>').text(name).html();
                            return $('<span><span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-monospace me-1 px-1">' + safeCode + '</span> <span class="fw-medium">' + safeName + '</span></span>');
                        }
                        return item.text;
                    },
                    templateSelection: function(item) {
                        if (!item.id) return item.text;
                        let text = item.text;
                        let sepIdx = text.indexOf(' - ');
                        if (sepIdx > -1) {
                            let code = text.substring(0, sepIdx).trim();
                            let name = text.substring(sepIdx + 3).trim();
                            let safeCode = $('<div>').text(code).html();
                            let safeName = $('<div>').text(name).html();
                            return $('<span><span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-monospace me-1 px-1">' + safeCode + '</span> ' + safeName + '</span>');
                        }
                        return item.text;
                    },
                    escapeMarkup: function(m) { return m; }
                });

                $this.on('select2:open.dp', function() {
                    let data = $(this).data('select2');
                    if (data && data.$dropdown) {
                        data.$dropdown.css('z-index', 1070);
                    }
                    setTimeout(function() {
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') || document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Ketik kode atau nama akun (misal: 1111, Uang Muka, DP)...');
                            searchField.focus();
                        }
                    }, 40);
                });

                $this.on('select2:select.dp', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Shared Searchable Akun Biaya Tambahan & Akun Alokasi (COA) Select2 Initializer
        window.initCoaSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let targetParent = ($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.off('select2:open.coa select2:select.coa');

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: '-- Cari & Pilih Akun (COA) --',
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true,
                    matcher: function(params, data) {
                        if (!params.term || $.trim(params.term) === '') {
                            return data;
                        }
                        if (!data || typeof data.text === 'undefined' || data.text === null) {
                            return null;
                        }
                        let term = params.term.toLowerCase().trim();
                        let text = String(data.text).toLowerCase();
                        let words = term.split(/\s+/).filter(Boolean);
                        let matched = words.every(function(word) {
                            return text.indexOf(word) > -1;
                        });
                        return matched ? data : null;
                    },
                    templateResult: function(item) {
                        if (!item || !item.id || !item.text) {
                            return item ? (item.text || '') : '';
                        }
                        let text = String(item.text);
                        let sepIdx = text.indexOf(' - ');
                        if (sepIdx > -1) {
                            let code = text.substring(0, sepIdx).trim();
                            let name = text.substring(sepIdx + 3).trim();
                            let safeCode = $('<div>').text(code).html();
                            let safeName = $('<div>').text(name).html();
                            return $('<span><span class="badge bg-secondary bg-opacity-20 text-body border font-monospace me-1 px-1">' + safeCode + '</span> <span class="fw-medium">' + safeName + '</span></span>');
                        }
                        return text;
                    },
                    templateSelection: function(item) {
                        if (!item || !item.id || !item.text) {
                            return item ? (item.text || '') : '';
                        }
                        let text = String(item.text);
                        let sepIdx = text.indexOf(' - ');
                        if (sepIdx > -1) {
                            let code = text.substring(0, sepIdx).trim();
                            let name = text.substring(sepIdx + 3).trim();
                            let safeCode = $('<div>').text(code).html();
                            let safeName = $('<div>').text(name).html();
                            return $('<span><span class="badge bg-secondary bg-opacity-20 text-body border font-monospace me-1 px-1">' + safeCode + '</span> ' + safeName + '</span>');
                        }
                        return text;
                    },
                    escapeMarkup: function(m) { return m; }
                });

                $this.on('select2:open.coa', function() {
                    let data = $(this).data('select2');
                    if (data && data.$dropdown) {
                        data.$dropdown.css('z-index', 1070);
                    }
                    setTimeout(function() {
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') || document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Ketik kode atau nama akun (misal: 5103, Biaya Angkut)...');
                            searchField.focus();
                        }
                    }, 40);
                });

                $this.on('select2:select.coa', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // Shared Searchable Kas / Bank Select2 Initializer
        window.initKasBankSelect2 = function($elem, $modal) {
            if (!$.fn.select2 || !$elem || !$elem.length) return;
            $elem.each(function() {
                let $this = $(this);
                let targetParent = ($modal && $modal.length) ? $modal : ($this.closest('.modal').length ? $this.closest('.modal') : $(document.body));

                if ($this.data('select2')) {
                    $this.select2('destroy');
                }
                $this.off('select2:open.kb select2:select.kb');

                $this.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: targetParent,
                    placeholder: '-- Pilih Kas / Bank Pengeluaran DP --',
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0,
                    closeOnSelect: true,
                    escapeMarkup: function(m) { return m; }
                });

                $this.on('select2:open.kb', function() {
                    let data = $(this).data('select2');
                    if (data && data.$dropdown) {
                        data.$dropdown.css('z-index', 1070);
                    }
                    setTimeout(function() {
                        let searchField = document.querySelector('.select2-container--open .select2-search__field') || document.querySelector('.select2-dropdown .select2-search__field');
                        if (searchField) {
                            searchField.setAttribute('placeholder', 'Ketik nama akun / bank (misal: BCA, Mandiri, Kas Kecil)...');
                            searchField.focus();
                        }
                    }, 40);
                });

                $this.on('select2:select.kb', function() {
                    let self = this;
                    setTimeout(function() {
                        try {
                            $(self).select2('close');
                        } catch (e) {}
                    }, 10);
                });
            });
        };

        // ===== Shared PPh (Withholding Tax) & Expense Type Helpers =====
        window.expenseTypeCoaMap = @json($expenseTypeCoaMap ?? new \stdClass());
        window.pphCoaMap = @json($pphCoaMap ?? new \stdClass());
        window.defaultPphCoaId = {{ $defaultPphCoaId ?? 'null' }};

        function pphEls(prefix) {
            return {
                sel:   $('#' + prefix + '_pph_pajak_id'),
                tarif: $('#' + prefix + '_tarif_pph'),
                nom:   $('#' + prefix + '_pph_nominal'),
                coa:   $('#' + prefix + '_pph_coa_id'),
                label: $('#' + prefix + '_label_pph_tarif'),
                sum:   $('#' + prefix + '_sum_pph')
            };
        }

        function pphToggle(el, enabled) {
            el.nom.prop('disabled', true).prop('readonly', true).addClass('bg-body-tertiary');
            el.coa.prop('disabled', !enabled);
            if (el.coa.data('select2')) el.coa.trigger('change.select2');
        }

        // Bind events once per modal. recalcFn = the modal's summary recalculation.
        window.initPphPanel = function(prefix, $modal, recalcFn) {
            let el = pphEls(prefix);
            if (!el.sel.length) return;

            window.initPajakSelect2(el.sel, $modal, { placeholder: 'Tanpa PPh', searchPlaceholder: 'Cari PPh (misal: PPh 23, 2%)...' });
            window.initPajakSelect2(el.coa, $modal, { placeholder: '-- Pilih Akun PPh --', searchPlaceholder: 'Cari akun (kode / nama)...' });

            el.sel.on('change', function() {
                let id = $(this).val();
                let tarif = parseFloat($(this).find('option:selected').data('tarif')) || 0;
                el.tarif.val(id ? tarif : 0);
                el.nom.data('manual', false);

                if (!id) {
                    el.nom.val('0,00');
                    el.coa.val('').data('userPicked', false);
                    pphToggle(el, false);
                } else {
                    pphToggle(el, true);
                    if (!el.coa.data('userPicked') || !el.coa.val()) {
                        let autoCoa = window.pphCoaMap[id] || window.defaultPphCoaId;
                        if (autoCoa) el.coa.val(String(autoCoa)).trigger('change.select2');
                    }
                }
                if (typeof recalcFn === 'function') recalcFn();
            });

            el.coa.on('select2:select', function() { el.coa.data('userPicked', true); });
        };

        // Returns PPh nominal for given DPP and refreshes the panel display.
        window.computePph = function(prefix, dpp, currencyCode) {
            let el = pphEls(prefix);
            if (!el.sel.length) return 0;

            let id = el.sel.val();
            let tarif = parseFloat(el.tarif.val()) || 0;
            let nominal = 0;

            if (id && tarif > 0) {
                nominal = Math.round(dpp * (tarif / 100) * 100) / 100;
                el.nom.val(formatNumber(nominal));
            } else {
                el.nom.val('0,00');
            }

            el.label.text(id ? '(' + String(tarif).replace('.', ',') + '%)' : '');
            el.sum.text(nominal > 0 ? '- ' + formatNumberOnly(nominal, currencyCode) : '0,00');
            return nominal;
        };

        // Set panel state (reset / populate when editing)
        window.setPphState = function(prefix, pajakId, tarif, nominal, coaId) {
            let el = pphEls(prefix);
            if (!el.sel.length) return;

            el.sel.val(pajakId ? String(pajakId) : '').trigger('change.select2');
            el.tarif.val(pajakId ? (parseFloat(tarif) || parseFloat(el.sel.find('option:selected').data('tarif')) || 0) : 0);
            el.coa.val(coaId ? String(coaId) : '').data('userPicked', !!coaId).trigger('change.select2');

            el.nom.data('manual', false);
            pphToggle(el, !!pajakId);
        };

        window.userInvoicePermissions = {
            canCreate: {{ auth()->user()?->can('tambah-purchase-invoice') ? 'true' : 'false' }},
            canEdit: {{ auth()->user()?->can('edit-purchase-invoice') ? 'true' : 'false' }},
            canDelete: {{ auth()->user()?->can('hapus-purchase-invoice') ? 'true' : 'false' }},
            canCancel: {{ (auth()->user()?->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()?->can('batalkan-purchase-invoice')) ? 'true' : 'false' }},
            isApprover: {{ (auth()->user() && (function() { try { return auth()->user()->hasPermissionTo('approve-purchase-invoice'); } catch (\Throwable $e) { return false; } })()) ? 'true' : 'false' }},
            canPrint: {{ auth()->user()?->can('cetak-purchase-invoice') ? 'true' : 'false' }},
            canViewJurnal: {{ (auth()->user()?->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()?->can('lihat-jurnal-purchase-invoice') || auth()->user()?->can('lihat-jurnal-umum')) ? 'true' : 'false' }},
            currentUserId: {{ auth()->id() ?? 'null' }}
        };
    </script>
    <!-- ===== INCLUDE JAVASCRIPTS ===== -->
    @include('modul_purchasing.faktur_pembelian.scripts.index_js')
    @include('modul_purchasing.faktur_pembelian.scripts.create_js')
    @include('modul_purchasing.faktur_pembelian.scripts.edit_js')
    @include('modul_purchasing.faktur_pembelian.scripts.view_js')
@endpush

</x-layouts>
