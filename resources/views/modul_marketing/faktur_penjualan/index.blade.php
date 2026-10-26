<x-layouts>
    <x-slot:title>Faktur Penjualan (Sales Invoice)</x-slot:title>

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Page Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </span>
                    Faktur Penjualan 
                </h4>
                <p class="text-body-secondary small mb-0 mt-1">Kelola penerbitan faktur penagihan resmi, piutang customer, dan integrasi Surat Jalan (DO).</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2" id="btn_reload_table" title="Segarkan Data Tabel">
                    <i class="bi bi-arrow-clockwise fs-6 text-primary"></i>
                    <span>Segarkan</span>
                </button>

                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2 position-relative" id="btn_open_filter" data-bs-toggle="modal" data-bs-target="#modalFilterInvoice">
                    <i class="bi bi-funnel-fill text-success fs-6"></i>
                    <span>Filter Data</span>
                    <span id="badge_active_filter" class="badge bg-success rounded-pill px-2 py-0.5 small d-none" style="font-size: 0.72rem;">0</span>
                </button>

                @can('tambah-sales-invoice')
                <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" id="btn_open_modal_create" data-bs-toggle="modal" data-bs-target="#modalCreateInvoice">
                    <i class="bi bi-plus-circle-fill fs-6"></i> Buat Faktur Penjualan
                </button>
                @endcan
            </div>
        </div>

        <!-- KPI Cards Summary Row -->
        <div class="row g-3 mb-4">
            <!-- Total Faktur -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-body-secondary small fw-semibold font-monospace text-uppercase d-block mb-1">Total</span>
                            <h4 class="fw-bold mb-0 text-body-emphasis font-monospace" id="kpi_total_faktur">{{ number_format($totalFaktur, 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-success-subtle text-success fs-4">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Piutang Belum Lunas -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-warning small fw-semibold font-monospace text-uppercase d-block mb-1">Piutang Belum Lunas</span>
                            <h4 class="fw-bold mb-0 text-warning font-monospace" id="kpi_piutang_unpaid">Rp {{ number_format($totalPiutangUnpaid, 2, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-warning-subtle text-warning fs-4">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faktur Jatuh Tempo -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-danger small fw-semibold font-monospace text-uppercase d-block mb-1">Jatuh Tempo</span>
                            <h4 class="fw-bold mb-0 text-danger font-monospace"><span id="kpi_jatuh_tempo">{{ number_format($totalJatuhTempo, 0, ',', '.') }}</span> <small class="fs-6 text-body-secondary">Faktur</small></h4>
                        </div>
                        <div class="p-3 rounded-4 bg-danger-subtle text-danger fs-4">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Terbayar Bulan Ini -->
            <div class="col-6 col-md-3">
                <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 overflow-hidden bg-body-tertiary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-success small fw-semibold font-monospace text-uppercase d-block mb-1">Lunas Bulan Ini</span>
                            <h4 class="fw-bold mb-0 text-success font-monospace" id="kpi_terbayar_bulan_ini">Rp {{ number_format($totalTerbayarBulanIni, 2, ',', '.') }}</h4>
                        </div>
                        <div class="p-3 rounded-4 bg-success-subtle text-success fs-4">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border border-body-tertiary shadow-sm rounded-4 overflow-hidden bg-body-tertiary">
            <div class="card-header bg-body-secondary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-success"></i> Daftar Faktur Penjualan
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle w-100" id="tableSalesInvoices">
                        <thead>
                            <tr class="small text-uppercase fw-bold bg-body-secondary text-body-secondary">
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>No. Faktur</th>
                                <th>Customer</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total </th>
                                <th class="text-end">Sisa Tagihan</th>
                                <th class="text-center">Status</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Include Modal Component Blade Files -->
    @include('modul_marketing.faktur_penjualan._modal_filter')
    @include('modul_marketing.faktur_penjualan._modal_create')
    @include('modul_marketing.faktur_penjualan._modal_detail')
    @include('modul_marketing.faktur_penjualan._modal_edit')
    @include('modul_marketing.faktur_penjualan._modal_jurnal')

    @push('styles')
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        .select2-container--bootstrap-5 .select2-selection__rendered {
            color: #1e293b !important;
            line-height: normal !important;
        }
        .select2-container--bootstrap-5 .select2-selection__placeholder {
            color: #64748b !important;
        }

        /* Dark Mode Select2 & Input Adjustments (#0f172a / #182234) */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__rendered {
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection__placeholder {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .select2-dropdown {
            background-color: #182234 !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.6) !important;
        }
        [data-bs-theme="dark"] .select2-results__option {
            color: #cbd5e1 !important;
            background-color: #182234 !important;
        }
        [data-bs-theme="dark"] .select2-results__option--highlighted[aria-selected] {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .select2-results__option[aria-selected="true"] {
            background-color: #047857 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .select2-search__field {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: rgba(255, 255, 255, 0.16) !important;
        }

        .table .select2-container--bootstrap-5 .select2-selection {
            min-height: 31px !important;
            padding: 0.15rem 0.5rem !important;
            font-size: 0.8rem !important;
        }

        /* Sembunyikan box loading processing agar live reload berjalan 100% mulus di latar belakang */
        #tableSalesInvoices_processing,
        .dataTables_processing {
            display: none !important;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        .spin-animation { display: inline-block; animation: spin 0.8s linear infinite; }


        tr.row-invoice-overdue td {
            background-color: rgba(239, 68, 68, 0.05) !important;
        }

        /* ════════════════════════════════════════════════════════════════
           RESPONSIVE & MODERN MODAL STYLES FOR FAKTUR PENJUALAN
           Mobile (HP), Tablet, & Desktop Optimizations
           ════════════════════════════════════════════════════════════════ */
        .faktur-modal-dialog,
        .invoice-modal-dialog {
            max-width: 95vw !important;
            width: 95vw !important;
            margin: 1.25rem auto;
            transition: max-width 0.25s ease, margin 0.25s ease;
        }
        @media (max-width: 991.98px) {
            .faktur-modal-dialog,
            .invoice-modal-dialog {
                max-width: 98vw !important;
                width: 98vw !important;
                margin: 0.75rem auto;
            }
        }
        @media (max-width: 575.98px) {
            .faktur-modal-dialog,
            .invoice-modal-dialog {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                min-height: 100vh !important;
            }
            .faktur-modal-dialog .modal-content,
            .invoice-modal-dialog .modal-content {
                min-height: 100vh !important;
                border-radius: 0 !important;
            }
            .faktur-modal-dialog .modal-body,
            .invoice-modal-dialog .modal-body {
                padding: 0.85rem !important;
            }
            .faktur-modal-dialog .modal-header,
            .faktur-modal-dialog .modal-footer,
            .invoice-modal-dialog .modal-header,
            .invoice-modal-dialog .modal-footer {
                padding: 0.75rem 0.85rem !important;
            }
        }

        /* Smooth touch scrolling for responsive tables */
        .faktur-modal-dialog .table-responsive {
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .faktur-modal-dialog .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .faktur-modal-dialog .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 4px;
        }

        /* Desktop min-width for full table presentation */
        /* Desktop min-width for full table presentation */
        .faktur-items-table {
            min-width: 880px;
        }
        .faktur-costs-table {
            min-width: 720px;
        }

        /* Mobile Cell Labels: hidden by default on desktop/tablet */
        .mobile-cell-label {
            display: none;
            font-size: 0.73rem;
            font-weight: 600;
            color: var(--bs-secondary-color, #64748b);
            margin-bottom: 0.25rem;
            text-transform: none;
            letter-spacing: 0.2px;
        }

        /* Subtotal item box styling */
        .item-subtotal-box {
            background-color: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease;
        }

        /* ════════════════════════════════════════════════════════════════
           RESPONSIVE MOBILE CARDS FOR SCREENS <= 767.98px (Phones & small tablets)
           ════════════════════════════════════════════════════════════════ */
        @media (max-width: 767.98px) {
            .faktur-items-table,
            .faktur-costs-table {
                min-width: 100% !important;
            }
            .faktur-items-table thead,
            .faktur-costs-table thead {
                display: none !important;
            }
            .faktur-items-table,
            .faktur-items-table tbody,
            .faktur-costs-table,
            .faktur-costs-table tbody {
                display: block !important;
                width: 100% !important;
            }

            /* --- Rincian Barang Item Card --- */
            .faktur-items-table tr.item-row,
            .faktur-items-table tr.edit-item-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                grid-template-areas:
                    "nama nama"
                    "qty harga"
                    "diskon ppn"
                    "subtotal subtotal";
                gap: 0.65rem 0.75rem;
                background: var(--bs-body-bg, #ffffff);
                border: 1px solid var(--bs-border-color, #e2e8f0) !important;
                border-radius: 1rem;
                padding: 1rem !important;
                margin-bottom: 0.85rem;
                position: relative;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            }
            .faktur-items-table tr.item-row > td,
            .faktur-items-table tr.edit-item-row > td {
                padding: 0 !important;
                border: none !important;
            }
            .faktur-items-table tr.item-row > td.col-nama-barang,
            .faktur-items-table tr.edit-item-row > td.col-nama-barang {
                grid-area: nama;
                padding-right: 2.25rem !important; /* ruang tombol hapus */
            }
            .faktur-items-table tr.item-row > td.col-aksi,
            .faktur-items-table tr.edit-item-row > td.col-aksi {
                position: absolute;
                top: 0.75rem;
                right: 0.75rem;
            }
            /* Hide separate col-sisa on mobile because delivery badge is cleanly integrated into col-nama-barang */
            .faktur-items-table tr.item-row > td.col-sisa,
            .faktur-items-table tr.edit-item-row > td.col-sisa {
                display: none !important;
            }
            .faktur-items-table tr.item-row > td.col-qty,
            .faktur-items-table tr.edit-item-row > td.col-qty {
                grid-area: qty;
            }
            .faktur-items-table tr.item-row > td.col-qty input,
            .faktur-items-table tr.edit-item-row > td.col-qty input {
                max-width: 100% !important;
                text-align: start !important;
                padding-left: 0.75rem !important;
            }
            .faktur-items-table tr.item-row > td.col-harga,
            .faktur-items-table tr.edit-item-row > td.col-harga {
                grid-area: harga;
            }
            .faktur-items-table tr.item-row > td.col-diskon,
            .faktur-items-table tr.edit-item-row > td.col-diskon {
                grid-area: diskon;
            }
            .faktur-items-table tr.item-row > td.col-ppn,
            .faktur-items-table tr.edit-item-row > td.col-ppn {
                grid-area: ppn;
            }
            .faktur-items-table tr.item-row > td.col-subtotal,
            .faktur-items-table tr.edit-item-row > td.col-subtotal {
                grid-area: subtotal;
                padding-top: 0.4rem !important;
                margin-top: 0.2rem;
            }
            .faktur-items-table .mobile-cell-label {
                display: block !important;
            }

            /* --- Biaya Tambahan Card (Stacked vertical layout so numbers and COA are never squished) --- */
            .faktur-costs-table tr.cost-row,
            .faktur-costs-table tr.edit-cost-row {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.65rem;
                background: var(--bs-body-bg, #ffffff);
                border: 1px solid var(--bs-border-color, #e2e8f0) !important;
                border-radius: 1rem;
                padding: 1rem !important;
                margin-bottom: 0.85rem;
                position: relative;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            }
            .faktur-costs-table tr.cost-row > td,
            .faktur-costs-table tr.edit-cost-row > td {
                padding: 0 !important;
                border: none !important;
                width: 100% !important;
            }
            .faktur-costs-table tr.cost-row > td.col-cost-jenis,
            .faktur-costs-table tr.edit-cost-row > td.col-cost-jenis {
                padding-right: 2.25rem !important; /* ruang tombol hapus */
            }
            .faktur-costs-table tr.cost-row > td.col-cost-aksi,
            .faktur-costs-table tr.edit-cost-row > td.col-cost-aksi {
                position: absolute;
                top: 0.75rem;
                right: 0.75rem;
                width: auto !important;
            }
            .faktur-costs-table .mobile-cell-label {
                display: block !important;
            }

            /* Empty state rows on mobile */
            .faktur-items-table tr#row_empty_item,
            .faktur-items-table tr#edit_row_empty_item,
            .faktur-costs-table tr#row_empty_cost,
            .faktur-costs-table tr#edit_row_empty_cost {
                display: block !important;
                width: 100% !important;
                padding: 1.25rem 0.75rem !important;
                text-align: center;
            }
            .faktur-items-table tr#row_empty_item td,
            .faktur-items-table tr#edit_row_empty_item td,
            .faktur-costs-table tr#row_empty_cost td,
            .faktur-costs-table tr#edit_row_empty_cost td {
                display: block !important;
                width: 100% !important;
                border: none !important;
                padding: 0 !important;
            }
        }

        /* Desktop Table View Styling for Subtotal (when screen >= 768px) */
        @media (min-width: 768px) {
            .faktur-items-table td.col-subtotal .item-subtotal-box {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                justify-content: flex-end !important;
            }
            .faktur-items-table td.col-subtotal .mobile-subtotal-label {
                display: none !important;
            }
            .faktur-items-table td.col-subtotal .item-subtotal-display {
                color: inherit !important;
                font-size: 0.85rem !important;
            }
        }

        /* Dark Mode Card & Input Polish */
        [data-bs-theme="dark"] .faktur-items-table tr.item-row,
        [data-bs-theme="dark"] .faktur-items-table tr.edit-item-row,
        [data-bs-theme="dark"] .faktur-costs-table tr.cost-row,
        [data-bs-theme="dark"] .faktur-costs-table tr.edit-cost-row {
            background-color: #172133 !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.35) !important;
        }
        [data-bs-theme="dark"] .item-subtotal-box {
            background-color: rgba(5, 150, 105, 0.15) !important;
            border-color: rgba(5, 150, 105, 0.35) !important;
        }
        [data-bs-theme="dark"] .mobile-cell-label {
            color: #94a3b8 !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            function escapeHtml(text) {
                if (!text) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Shared View Toggle handler (Auto / Tabel / Kartu) for Faktur Penjualan modals
            $(document).on('click', '.invoice-view-toggle button', function () {
                let mode = $(this).data('mode');
                let targetId = $(this).data('target');
                let $table = $('#' + targetId);
                let $btnGroup = $(this).closest('.invoice-view-toggle');

                $btnGroup.find('button').removeClass('active btn-primary text-white').addClass('text-body-secondary');
                $(this).addClass('active btn-primary text-white').removeClass('text-body-secondary');

                if (mode === 'cards') {
                    $table.addClass('force-cards').removeClass('force-table');
                } else if (mode === 'table') {
                    $table.addClass('force-table').removeClass('force-cards');
                } else {
                    $table.removeClass('force-cards force-table');
                }
            });
            let table = $('#tableSalesInvoices').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('modul_marketing.faktur_penjualan.index') }}",
                    data: function (d) {
                        d.status = $('#filter_status').val();
                        d.customer_id = $('#filter_customer_id').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center small font-monospace' },
                    { data: 'nomor_faktur', name: 'sales_invoices.nomor_faktur' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'tanggal_jatuh_tempo_fmt', name: 'sales_invoices.tanggal_jatuh_tempo' },
                    { data: 'grand_total_fmt', name: 'sales_invoices.grand_total', className: 'text-end' },
                    { data: 'sisa_tagihan_fmt', name: 'sales_invoices.sisa_tagihan', className: 'text-end' },
                    { data: 'status_badge', name: 'sales_invoices.status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                createdRow: function (row, data, dataIndex) {
                    if (data.is_overdue) {
                        $(row).addClass('row-invoice-overdue');
                    }
                },
                order: [[1, 'desc']],
                language: {
                    search: "Cari Faktur:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ faktur",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "<i class='bi bi-chevron-right'></i>",
                        previous: "<i class='bi bi-chevron-left'></i>"
                    }
                }
            });

            // Update KPI metrics on every ajax response
            table.on('xhr', function (e, settings, json) {
                if (json) {
                    if (json.totalFaktur !== undefined) $('#kpi_total_faktur').text(json.totalFaktur);
                    if (json.totalPiutangUnpaid !== undefined) $('#kpi_piutang_unpaid').text(json.totalPiutangUnpaid);
                    if (json.totalJatuhTempo !== undefined) $('#kpi_jatuh_tempo').text(json.totalJatuhTempo);
                    if (json.totalTerbayarBulanIni !== undefined) $('#kpi_terbayar_bulan_ini').text(json.totalTerbayarBulanIni);
                }
            });

            // Tombol Segarkan Data Tabel
            $('#btn_reload_table').on('click', function () {
                let $btn = $(this);
                let $icon = $btn.find('i');
                $icon.addClass('spin-animation');
                $btn.prop('disabled', true);

                table.ajax.reload(function () {
                    $icon.removeClass('spin-animation');
                    $btn.prop('disabled', false);
                }, false);
            });

            // Debounce input pencarian DataTables agar tidak membebani server/database jutaan baris pada tiap ketikan
            let searchDebounceTimer = null;
            $('#tableSalesInvoices_filter input').unbind('keyup search input').bind('input', function () {
                let val = this.value;
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function () {
                    table.search(val).draw();
                }, 400);
            });

            // ── Background Engine Real-time Live Sync Cerdas ───────────────────
            let lastInvoiceFingerprint = null;
            let isRealtimeActive = true;
            let activeDetailInvoiceId = null;

            function triggerLiveSync(force = false) {
                if (!isRealtimeActive) return;
                if (!force && document.visibilityState !== 'visible') return;

                let isSearching = $('#tableSalesInvoices_filter input').is(':focus');
                let isFormOpen = $('#modalCreateInvoice').hasClass('show') || $('#modalEditInvoice').hasClass('show');
                if (isSearching || isFormOpen) return;

                $.ajax({
                    url: "{{ route('modul_marketing.faktur_penjualan.check_sync') }}",
                    method: 'GET',
                    cache: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.fingerprint) {
                            if (lastInvoiceFingerprint === null) {
                                lastInvoiceFingerprint = res.fingerprint;
                            } else if (lastInvoiceFingerprint !== res.fingerprint || force) {
                                lastInvoiceFingerprint = res.fingerprint;

                                // Reload DataTables secara senyap di latar belakang tanpa mereset pagination
                                table.ajax.reload(null, false);

                                // Jika modal detail sedang terbuka, perbarui konten modalnya otomatis
                                if ($('#modalDetailInvoice').hasClass('show') && activeDetailInvoiceId) {
                                    $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + activeDetailInvoiceId, function (html) {
                                        $('#modalDetailInvoiceContent').html(html);
                                    });
                                }
                            }
                        }
                    }
                });
            }

            // Polling cepat tiap 2.5 detik saat tab aktif
            const invoicePollInterval = setInterval(function () {
                triggerLiveSync();
            }, 2500);

            // Deteksi saat tab browser kembali aktif dari background
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    triggerLiveSync(true);
                }
            });

            // Inisialisasi Select2 pada filter modal
            $('#modalFilterInvoice').on('shown.bs.modal', function () {
                $('#filter_customer_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalFilterInvoice'),
                    placeholder: '-- Semua Customer --',
                    allowClear: true,
                    width: '100%'
                });
            });

            // Update badge dan status tombol filter aktif
            function updateFilterBadge() {
                let count = 0;
                if ($('#filter_status').val()) count++;
                if ($('#filter_customer_id').val()) count++;
                if ($('#filter_start_date').val() || $('#filter_end_date').val()) count++;

                if (count > 0) {
                    $('#badge_active_filter').text(count).removeClass('d-none');
                    $('#btn_open_filter').removeClass('btn-outline-secondary').addClass('btn-success text-white');
                    $('#btn_open_filter i').removeClass('text-success').addClass('text-white');
                } else {
                    $('#badge_active_filter').text('0').addClass('d-none');
                    $('#btn_open_filter').removeClass('btn-success text-white').addClass('btn-outline-secondary');
                    $('#btn_open_filter i').removeClass('text-white').addClass('text-success');
                }
            }

            // Filter Apply
            $('#btn_apply_filter').on('click', function () {
                updateFilterBadge();
                table.draw();
                $('#modalFilterInvoice').modal('hide');
            });

            // Filter Reset
            $('#btn_reset_filter').on('click', function () {
                $('#filter_status').val('');
                $('#filter_customer_id').val('').trigger('change');
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
                updateFilterBadge();
                table.draw();
                $('#modalFilterInvoice').modal('hide');
            });

            // View Invoice Modal
            $(document).on('click', '.btn-view-invoice', function () {
                let id = $(this).data('id');
                activeDetailInvoiceId = id;

                $('#modalDetailInvoiceContent').html(`
                    <div class="p-5 text-center text-body-secondary">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 font-monospace">Memuat detail faktur penjualan...</p>
                    </div>
                `);
                $('#modalDetailInvoice').modal('show');

                $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id, function (html) {
                    $('#modalDetailInvoiceContent').html(html);
                }).fail(function() {
                    $('#modalDetailInvoiceContent').html(`
                        <div class="p-5 text-center text-danger">
                            <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                            Gagal memuat detail faktur penjualan.
                        </div>
                    `);
                });
            });

            $('#modalDetailInvoice').on('hidden.bs.modal', function () {
                activeDetailInvoiceId = null;
            });

            // Print Invoice Action
            $(document).on('click', '.btn-print-invoice', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                window.open("{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/print", "_blank");
            });

            // View Accounting Journal Modal Action
            $(document).on('click', '.btn-view-jurnal', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur';

                Swal.fire({
                    title: 'Memuat Jurnal Akuntansi...',
                    text: 'Mengambil data voucher jurnal untuk faktur ' + nomor,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/jurnal", function (res) {
                    Swal.close();
                    if (!res.success || !res.jurnal) {
                        Swal.fire('Info', res.message || 'Data jurnal tidak ditemukan.', 'info');
                        return;
                    }

                    let jurnal = res.jurnal;
                    let invoice = res.invoice;

                    $('#inv_jurnal_nomor').text(jurnal.nomor_jurnal || '-');
                    $('#btn_print_inv_jurnal').attr('href', `/accounting/jurnal-umum/${jurnal.id}/print`);

                    let tglStr = '-';
                    if (jurnal.tanggal_jurnal) {
                        let raw = String(jurnal.tanggal_jurnal);
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
                    $('#inv_jurnal_tanggal').text(tglStr);

                    // Entitas: Cabang vs PT
                    let entitasText = 'PT PUSAT';
                    let entitasBadgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                    if (jurnal.branch_id && jurnal.branch) {
                        entitasText = `Cabang: ${jurnal.branch.nama_cabang}`;
                        entitasBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
                    } else if (jurnal.company) {
                        entitasText = jurnal.company.nama_perusahaan || 'PT PUSAT';
                    }
                    $('#inv_jurnal_entitas_badge').html(`<span class="badge ${entitasBadgeClass} px-2.5 py-1 rounded-pill fw-bold"><i class="bi bi-building me-1"></i>${escapeHtml(entitasText)}</span>`);

                    $('#inv_jurnal_ref_faktur').text(invoice.nomor_faktur || '-');
                    let custName = invoice.customer ? (invoice.customer.nama_perusahaan || invoice.customer.nama_customer) : '-';
                    $('#inv_jurnal_customer').text(custName).attr('title', custName);
                    $('#inv_jurnal_keterangan').text(jurnal.keterangan || '-');

                    // Baris Rincian Debit & Kredit
                    let rowsHtml = '';
                    let totalDebit = 0;
                    let totalKredit = 0;

                    (jurnal.details || []).forEach((row, idx) => {
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

                    $('#inv_jurnal_rows_container').html(rowsHtml || '<tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada baris akun.</td></tr>');
                    $('#inv_jurnal_total_debit').text('Rp ' + Number(totalDebit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#inv_jurnal_total_kredit').text('Rp ' + Number(totalKredit).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                    let diff = Math.abs(totalDebit - totalKredit);
                    let isBalanced = diff < 0.01 && totalDebit > 0;
                    if (isBalanced) {
                        $('#inv_jurnal_balance_container').html(`
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1">
                                <i class="bi bi-check2-circle me-1"></i>Balance
                            </span>
                        `);
                    } else {
                        $('#inv_jurnal_balance_container').html(`
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-3 py-1">
                                <i class="bi bi-exclamation-triangle me-1"></i>Unbalanced
                            </span>
                        `);
                    }

                    $('#modalJurnalSalesInvoice').modal('show');
                }).fail(function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Memuat Jurnal',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat mengambil data jurnal.',
                        confirmButtonColor: '#dc2626'
                    });
                });
            });

            // Ensure proper modal-open class when closing nested modals
            $(document).on('hidden.bs.modal', '#modalJurnalSalesInvoice', function () {
                if ($('#modalDetailInvoice').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // View / Preview Document in Modal
            $(document).on('click', '.btn-preview-document', function (e) {
                e.preventDefault();
                let fileUrl = $(this).data('url');
                let fileName = $(this).data('filename') || 'Dokumen Pendukung';
                let ext = ($(this).data('ext') || '').toLowerCase();
                let downloadUrl = $(this).data('download') || fileUrl;

                $('#preview_doc_title').text(fileName);
                $('#preview_doc_subtitle').text('Format: .' + (ext ? ext.toUpperCase() : 'FILE'));
                $('#btn_download_from_preview').attr('href', downloadUrl);
                $('#preview_doc_info').text('Nama Dokumen: ' + fileName);

                let isImage = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(ext);
                let isPdf = ext === 'pdf';

                let safeFileUrl = escapeHtml(fileUrl);
                let safeFileName = escapeHtml(fileName);
                let safeDownloadUrl = escapeHtml(downloadUrl);

                let contentHtml = '';
                if (isImage) {
                    contentHtml = `
                        <div class="p-3 w-100 text-center">
                            <img src="${safeFileUrl}" alt="${safeFileName}" class="img-fluid rounded-3 shadow border" style="max-height: 75vh; object-fit: contain;">
                        </div>
                    `;
                } else if (isPdf) {
                    contentHtml = `
                        <iframe src="${safeFileUrl}#toolbar=1" style="width: 100%; height: 75vh; border: none;" allowfullscreen></iframe>
                    `;
                } else {
                    contentHtml = `
                        <div class="p-5 text-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 4rem;"></i>
                            <h5 class="fw-bold mt-3 mb-2 text-body-emphasis">${safeFileName}</h5>
                            <p class="text-body-secondary mb-3">Tipe file ini tidak dapat dipratinjau langsung di dalam browser.</p>
                            <a href="${safeDownloadUrl}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm btn-download-document">
                                <i class="bi bi-download me-1"></i> Unduh File Sekarang
                            </a>
                        </div>
                    `;
                }

                $('#preview_doc_body').html(contentHtml);
                $('#modalPreviewDokumen').modal('show');
            });

            // Ensure backdrop & body scroll remain proper when preview modal is closed over detail/edit modal
            $(document).on('hidden.bs.modal', '#modalPreviewDokumen', function () {
                if ($('#modalDetailInvoice').hasClass('show') || $('#modalEditInvoice').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // AJAX Download Document with SweetAlert notification
            $(document).on('click', '.btn-download-document', function (e) {
                e.preventDefault();
                let btn = $(this);
                let url = btn.attr('href');
                if (!url || url === '#' || url.startsWith('javascript:')) return;

                let originalHtml = btn.html();
                btn.addClass('disabled').css('pointer-events', 'none');
                btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunduh...');

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal mengunduh file (HTTP ' + response.status + ')');
                        }

                        let filename = 'Dokumen_Faktur';
                        let disposition = response.headers.get('content-disposition');
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        return response.blob().then(blob => ({ blob, filename }));
                    })
                    .then(({ blob, filename }) => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.className = 'no-loader';
                        a.href = blobUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        setTimeout(() => {
                            window.URL.revokeObjectURL(blobUrl);
                            a.remove();
                        }, 500);

                        btn.removeClass('disabled').css('pointer-events', '');
                        btn.html(originalHtml);

                        if (typeof window.hideLoader === 'function') {
                            window.hideLoader();
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diunduh!',
                                text: 'Dokumen ' + filename + ' berhasil diunduh ke komputer Anda.',
                                confirmButtonColor: '#10b981',
                                confirmButtonText: 'OK',
                                timer: 3500
                            });
                        } else {
                            alert('Dokumen berhasil diunduh!');
                        }
                    })
                    .catch(err => {
                        btn.removeClass('disabled').css('pointer-events', '');
                        btn.html(originalHtml);

                        if (typeof window.hideLoader === 'function') {
                            window.hideLoader();
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Download Gagal',
                                text: err.message || 'Terjadi kesalahan saat mengunduh dokumen.',
                                confirmButtonColor: '#dc2626'
                            });
                        } else {
                            alert('Gagal mengunduh dokumen: ' + err.message);
                        }
                    });
            });

            // Send / Resend WhatsApp Notification
            $(document).on('click', '.btn-send-wa', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur Penjualan';
                let btn = $(this);

                Swal.fire({
                    title: 'Kirim Notifikasi WhatsApp?',
                    text: 'Notifikasi rincian tagihan faktur ' + nomor + ' akan dikirimkan melalui WhatsApp Gateway.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-whatsapp me-1"></i> Ya, Kirim Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let originalHtml = btn.html();
                        btn.prop('disabled', true);

                        Swal.fire({
                            title: 'Mengirim WhatsApp...',
                            text: 'Mohon tunggu sebentar, pesan sedang diproses oleh gateway.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/send-whatsapp",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                btn.prop('disabled', false).html(originalHtml);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Notifikasi Terkirim!',
                                    text: res.message || 'Pesan notifikasi WhatsApp berhasil dikirim.',
                                    confirmButtonColor: '#10b981'
                                });
                            },
                            error: function (xhr) {
                                btn.prop('disabled', false).html(originalHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Mengirim',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengirim notifikasi WhatsApp.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }
                        });
                    }
                });
            });

            // Submit Invoice for Approval
            $(document).on('click', '.btn-submit-invoice', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur Penjualan';

                Swal.fire({
                    title: 'Ajukan Approval Faktur?',
                    text: 'Faktur ' + nomor + ' akan diajukan ke Approver dan notifikasi WhatsApp akan dikirim secara otomatis ke pihak yang berwenang.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses Pengajuan...',
                            text: 'Mohon tunggu, sistem sedang mengirim notifikasi WhatsApp ke pihak approver.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/submit",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Diajukan!',
                                    text: res.message,
                                    confirmButtonColor: '#2563eb'
                                });
                                table.draw(false);
                                triggerLiveSync(true);
                                if ($('#modalDetailInvoice').hasClass('show')) {
                                    $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id, function (html) {
                                        $('#modalDetailInvoiceContent').html(html);
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Mengajukan',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengajukan faktur penjualan.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }
                        });
                    }
                });
            });

            // Approve Invoice
            $(document).on('click', '.btn-approve-invoice', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur Penjualan';

                Swal.fire({
                    title: 'Setujui (Approve) Faktur?',
                    text: 'Faktur ' + nomor + ' akan disetujui, jurnal akuntansi terbentuk otomatis, dan notifikasi WhatsApp konfirmasi akan dikirim ke pembuat faktur.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Ya, Setujui Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyetujui Faktur...',
                            text: 'Mohon tunggu, memproses persetujuan dan mengirim notifikasi WhatsApp...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/approve",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Disetujui!',
                                    text: res.message,
                                    confirmButtonColor: '#059669'
                                });
                                table.draw(false);
                                triggerLiveSync(true);
                                if ($('#modalDetailInvoice').hasClass('show')) {
                                    $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id, function (html) {
                                        $('#modalDetailInvoiceContent').html(html);
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menyetujui',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui faktur penjualan.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }
                        });
                    }
                });
            });

            // Reject Invoice
            $(document).on('click', '.btn-reject-invoice', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur Penjualan';

                Swal.fire({
                    title: 'Tolak Faktur Penjualan?',
                    html: `
                        <p class="text-body-secondary small mb-3">
                            Berikan alasan penolakan untuk faktur <b>${nomor}</b>. Alasan ini akan dikirimkan otomatis melalui WhatsApp ke pembuat faktur agar dapat diperbaiki.
                        </p>
                        <div class="text-start">
                            <label class="form-label fw-semibold small text-body-emphasis">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea id="swal_alasan_penolakan" class="form-control rounded-3" rows="3" placeholder="Contoh: Nominal diskon tidak sesuai kesepakatan, tolong revisi PO terkait..."></textarea>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Ya, Tolak & Kirim WA',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const alasan = $('#swal_alasan_penolakan').val().trim();
                        if (!alasan) {
                            Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                            return false;
                        }
                        return alasan;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        let alasanPenolakan = result.value;

                        Swal.fire({
                            title: 'Memproses Penolakan...',
                            text: 'Mohon tunggu, mengirim notifikasi WhatsApp ke pembuat faktur...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/reject",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                alasan_penolakan: alasanPenolakan
                            },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Faktur Ditolak',
                                    text: res.message,
                                    confirmButtonColor: '#dc2626'
                                });
                                table.draw(false);
                                triggerLiveSync(true);
                                if ($('#modalDetailInvoice').hasClass('show')) {
                                    $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id, function (html) {
                                        $('#modalDetailInvoiceContent').html(html);
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menolak',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menolak faktur penjualan.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }
                        });
                    }
                });
            });

            // Unapprove / Kembalikan ke Draf (Khusus Super Admin)
            $(document).on('click', '.btn-unapprove-invoice', function () {
                let id = $(this).data('id');
                let nomor = $(this).data('nomor') || 'Faktur Penjualan';

                Swal.fire({
                    title: 'Batal Setujui Faktur?',
                    html: `
                        <div class="text-start small">
                            <p class="mb-2">Anda akan membatalkan persetujuan untuk faktur <b>${nomor}</b> dan mengembalikannya ke status <b>Draft</b>.</p>
                            <div class="alert alert-warning py-2 px-3 mb-0 border-0 rounded-3">
                                <ul class="mb-0 ps-3">
                                    <li>Jurnal akuntansi otomatis piutang usaha akan <b>dihapus</b>.</li>
                                    <li>Faktur dapat diedit kembali oleh pembuatnya.</li>
                                    <li>Hanya dapat dilakukan jika <b>belum ada pembayaran</b> yang tercatat.</li>
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Kembalikan ke Draf',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Membatalkan persetujuan dan menghapus jurnal...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id + "/unapprove",
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dikembalikan ke Draf!',
                                    text: res.message,
                                    confirmButtonColor: '#059669'
                                });
                                table.draw(false);
                                triggerLiveSync(true);
                                if ($('#modalDetailInvoice').hasClass('show')) {
                                    $.get("{{ url('modul-marketing/faktur-penjualan') }}/" + id, function (html) {
                                        $('#modalDetailInvoiceContent').html(html);
                                    });
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Membatalkan Persetujuan',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat membatalkan persetujuan faktur.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }
                        });
                    }
                });
            });

            // Delete / Cancel Invoice
            $(document).on('click', '.btn-delete-invoice', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Batalkan Faktur Penjualan?',
                    text: 'Faktur yang dibatalkan akan menghapus jurnal piutang usaha terkait.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('modul-marketing/faktur-penjualan') }}/" + id,
                            type: 'DELETE',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                Swal.fire('Berhasil!', res.message, 'success');
                                table.draw();
                                triggerLiveSync(true);
                            },
                            error: function (xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-layouts>
