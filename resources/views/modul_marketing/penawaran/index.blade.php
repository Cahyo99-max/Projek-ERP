<x-layouts title="Penawaran Harga (Sales Quotation)">

    @push('styles')
    <!-- Select2 CSS with Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('modul_smd/penawaran.css') }}?v={{ time() }}">
    <style>
        /* ─── Select2 Styling for Filter Modal (Dual Theme) ─── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.625rem;
            min-height: 40px;
            font-size: 0.85rem;
            padding: 4px 10px;
            border-color: var(--bs-border-color, #dee2e6);
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--bs-body-color, #212529);
            line-height: 28px;
            padding-left: 2px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--bs-secondary-color, #6c757d);
        }
        .select2-dropdown {
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--bs-border-color, #dee2e6);
            background-color: var(--bs-body-bg, #ffffff);
            font-size: 0.84rem;
            z-index: 1070 !important;
            overflow: hidden;
        }
        .select2-container--bootstrap-5 .select2-search {
            padding: 8px;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 0.5rem;
            font-size: 0.82rem;
            padding: 6px 12px;
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            border-color: var(--bs-border-color, #dee2e6);
        }
        .select2-container--bootstrap-5 .select2-results__options {
            max-height: 220px;
            padding: 4px;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 7px 12px;
            border-radius: 0.45rem;
            margin-bottom: 2px;
            color: var(--bs-body-color, #212529);
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            font-weight: 600;
        }

        /* Dark Mode Select2 Overrides */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection,
        .dark-mode .select2-container--bootstrap-5 .select2-selection {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        .dark-mode .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .select2-dropdown,
        html[data-theme="dark"] .select2-dropdown,
        .dark-mode .select2-dropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-search__field,
        .dark-mode .select2-container--bootstrap-5 .select2-search__field {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option {
            color: #f1f5f9 !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--selected,
        html[data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--selected,
        .dark-mode .select2-container--bootstrap-5 .select2-results__option--selected {
            background-color: rgba(59, 130, 246, 0.25) !important;
            color: #93c5fd !important;
        }
    </style>
    @endpush

    <div class="container-fluid px-3 px-md-4 py-4">

        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0 text-body">Penawaran Harga</h3>
                </div>
                <p class="text-muted fs-8 mb-0 mt-0.5">Kelola dokumen penawaran harga resmi, multi-revisi negosiasi, persetujuan, dan konversi ke Sales Order.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Filter Trigger Button (Opens Modal) -->
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3.5 shadow-xs d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalFilterQuotation" id="btnFilterModalTrigger">
                    <i class="bi bi-funnel text-primary"></i>
                    <span class="fw-semibold">Filter</span>
                    <span id="filterCountBadge" class="badge bg-primary rounded-pill fs-9 px-1.5 py-0.5" style="display:none;">0</span>
                </button>

                @can('tambah-penawaran')
                <a href="{{ route('penawaran.create') }}" class="btn btn-primary rounded-pill px-3.5 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span class="fw-semibold">Buat Penawaran Baru</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Active Filter Indicator Pill (Shown when filter is applied) -->
        <div id="activeFilterContainer" class="d-flex flex-wrap align-items-center gap-2 mb-3" style="display:none !important;">
            <span class="fs-9 text-muted fw-semibold"><i class="bi bi-funnel-fill text-primary me-1"></i> Filter Aktif:</span>
            <div id="activeFilterTags" class="d-flex flex-wrap gap-1.5"></div>
            <button type="button" class="btn btn-link text-danger p-0 fs-9 text-decoration-none fw-semibold ms-1" id="btnClearAllFilters">
                <i class="bi bi-x-circle me-0.5"></i> Reset Semua
            </button>
        </div>

        <!-- KPI Metric Cards (Modern & Cinematic) -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- Total Penawaran -->
            <div class="col-6 col-md-3">
                <div class="sq-kpi-card sq-kpi-primary h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="sq-kpi-label">Total Penawaran</span>
                            <div class="sq-kpi-icon sq-kpi-icon-primary">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-body font-monospace fs-4 fs-md-3">{{ number_format($kpis['total_penawaran'] ?? 0) }}</h3>
                    </div>
                    <div class="mt-2.5 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-file-text me-1"></i>Dokumen Aktif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Draft & Terkirim -->
            <div class="col-6 col-md-3">
                <div class="sq-kpi-card sq-kpi-sky h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="sq-kpi-label">Draft & Terkirim</span>
                            <div class="sq-kpi-icon sq-kpi-icon-sky">
                                <i class="bi bi-send-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-info font-monospace fs-4 fs-md-3">{{ number_format($kpis['menunggu'] ?? 0) }}</h3>
                    </div>
                    <div class="mt-2.5 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-hourglass-split me-1"></i>Menunggu Respon
                        </span>
                    </div>
                </div>
            </div>

            <!-- Disetujui -->
            <div class="col-6 col-md-3">
                <div class="sq-kpi-card sq-kpi-emerald h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="sq-kpi-label">Disetujui</span>
                            <div class="sq-kpi-icon sq-kpi-icon-emerald">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-success font-monospace fs-4 fs-md-3">{{ number_format($kpis['approved'] ?? 0) }}</h3>
                    </div>
                    <div class="mt-2.5 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-check-circle me-1"></i>Siap Jadi SO
                        </span>
                    </div>
                </div>
            </div>

            <!-- Terkonversi SO -->
            <div class="col-6 col-md-3">
                <div class="sq-kpi-card sq-kpi-purple h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="sq-kpi-label">Jadi SO</span>
                            <div class="sq-kpi-icon sq-kpi-icon-purple">
                                <i class="bi bi-cart-check-fill"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-0 text-purple font-monospace fs-4 fs-md-3">{{ number_format($kpis['so'] ?? 0) }}</h3>
                    </div>
                    <div class="mt-2.5 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between">
                        <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">
                            <i class="bi bi-bag-check me-1"></i>Tahap SO
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTables Main Card -->
        <div class="sq-main-card">
            <!-- Mobile Swipe Hint Banner -->
            <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-3 rounded-3 text-primary shadow-2xs sq-mobile-swipe-banner">
                <div class="d-flex align-items-center gap-1.5">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Tabel penawaran dapat digeser ke samping</span>
                </div>
                <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
            </div>

            <div class="sq-table-responsive table-responsive">
                <table id="quotationTable" class="table align-middle w-100 mb-0">
                    <thead>
                        <tr>
                            <th>No. Penawaran</th>
                            <th>Customer & Proyek</th>
                            <th>Tgl Penawaran</th>
                            <th class="text-end">Total Nilai</th>
                            <th>HPP & Margin</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Filter Penawaran -->
    <div class="modal fade" id="modalFilterQuotation" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="badge-soft-indigo p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-funnel-fill text-primary fs-6"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-body fs-6">Filter Dokumen Penawaran</h5>
                            <span class="fs-9 text-muted">Saring penawaran harga berdasarkan kriteria</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-building text-primary"></i> Filter Customer / Klien
                            </label>
                            <select id="filterCustomer" class="form-select form-select-sm select2-filter-modal" data-placeholder="-- Semua Customer / Klien --">
                                <option value=""></option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_perusahaan ?: $c->nama_customer }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-tag text-info"></i> Filter Status Penawaran
                            </label>
                            <select id="filterStatus" class="form-select form-select-sm select2-filter-modal" data-placeholder="-- Semua Status Aktif --">
                                <option value=""></option>
                                <option value="Draft">📝 Draft (Konsep Internal)</option>
                                <option value="Sent">📨 Terkirim / Pending Approval</option>
                                <option value="Approved">✅ Disetujui (Approved)</option>
                                <option value="Converted_to_SO">🛒 Terkonversi ke Sales Order (SO)</option>
                                <option value="Rejected">❌ Ditolak (Rejected)</option>
                                <option value="Revised">🔄 Direvisi (Arsip Revisi Lama)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fs-8 fw-semibold text-secondary mb-1.5 d-flex align-items-center gap-1.5">
                                <i class="bi bi-briefcase text-warning"></i> Filter Proyek / Tender Terkait
                            </label>
                            <select id="filterTender" class="form-select form-select-sm select2-filter-modal" data-placeholder="-- Semua Proyek / Non-Tender --">
                                <option value=""></option>
                                @foreach($tenders as $t)
                                    <option value="{{ $t->id }}">{{ $t->nomor_kontrak }} - {{ $t->nama_tender }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 pt-2 border-top">
                            <div class="form-check form-switch fs-8">
                                <input class="form-check-input" type="checkbox" role="switch" id="checkIncludeRevisions">
                                <label class="form-check-label text-muted" for="checkIncludeRevisions">
                                    Tampilkan riwayat revisi lama yang sudah digantikan (Status Direvisi)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3.5 rounded-pill" id="btnResetFilterModal">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-4 rounded-pill fw-semibold shadow-xs" id="btnApplyFilterModal" data-bs-dismiss="modal">
                        <i class="bi bi-check-lg me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penawaran -->
    <div class="modal fade" id="modalDetailQuotation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2.5 min-w-0 flex-grow-1 pe-2">
                        <div class="sq-kpi-icon-primary p-2 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 40px; height: 40px;">
                            <i class="bi bi-file-earmark-text-fill fs-5 text-white"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold mb-0 text-body fs-6 font-monospace" id="detailNomor">SQ/2026/...</h5>
                                <div id="detailStatusBadge"></div>
                            </div>
                            <span class="fs-8 text-muted text-truncate d-block mt-0.5" id="detailPerihal">Perihal Penawaran</span>
                        </div>
                    </div>
                    <button type="button" class="btn sq-btn-close rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-xs flex-shrink-0" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body p-4" id="detailBodyContent">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data detail penawaran...
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div id="detailActionLeft" class="d-flex flex-wrap gap-2"></div>
                    <div class="d-flex gap-2" id="detailActionRight">
                        <button type="button" class="btn btn-sm btn-secondary px-3.5 rounded-pill" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Helper to format date cleanly
        function formatIndoDate(dateStr) {
            if (!dateStr) return '-';
            let cleanStr = dateStr.replace('T', ' ').split('.')[0];
            let parts = cleanStr.split(' ')[0].split('-');
            if (parts.length === 3) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                let year = parts[0];
                let month = parseInt(parts[1], 10) - 1;
                let day = parseInt(parts[2], 10);
                if (month >= 0 && month < 12) {
                    return `${day} ${months[month]} ${year}`;
                }
            }
            return dateStr;
        }

        $(document).ready(function() {
            // Initialize Select2 inside filter modal
            function initFilterSelect2() {
                if ($.fn.select2) {
                    $('.select2-filter-modal').each(function() {
                        let placeholder = $(this).data('placeholder') || '-- Pilih --';
                        if (!$(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2({
                                dropdownParent: $('#modalFilterQuotation'),
                                theme: 'bootstrap-5',
                                width: '100%',
                                placeholder: placeholder,
                                allowClear: true
                            });
                        }
                    });
                }
            }

            initFilterSelect2();

            $('#modalFilterQuotation').on('shown.bs.modal', function() {
                initFilterSelect2();
            });

            // Auto-focus search input immediately when Select2 dropdown opens
            $(document).on('select2:open', function() {
                setTimeout(function() {
                    let searchField = document.querySelector('.select2-container--open .select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 30);
            });

            // Initialize DataTables
            const table = $('#quotationTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: "<'row mb-3 align-items-center gy-2'<'col-12 col-md-6'l><'col-12 col-md-6 text-md-end'f>>" +
                     "<'row'<'col-12'tr>>" +
                     "<'row mt-3 align-items-center gy-2'<'col-12 col-md-5 text-center text-md-start'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",
                ajax: {
                    url: "{{ route('penawaran.data') }}",
                    data: function(d) {
                        d.customer_id = $('#filterCustomer').val();
                        d.status = $('#filterStatus').val();
                        d.tender_id = $('#filterTender').val();
                        d.include_revisions = $('#checkIncludeRevisions').is(':checked') ? 1 : 0;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables AJAX error:', xhr);
                    }
                },
                columns: [
                    { data: 'nomor_formatted', name: 'nomor_formatted' },
                    { data: 'customer_info', name: 'customer_info' },
                    { data: 'tanggal_formatted', name: 'tanggal_formatted' },
                    { data: 'grand_total_formatted', name: 'grand_total_formatted', className: 'text-end fw-bold text-body font-monospace' },
                    { data: 'hpp_margin', name: 'hpp_margin' },
                    { data: 'status_badge', name: 'status_badge', className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[2, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Cari nomor SQ, customer...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ penawaran",
                    infoEmpty: "Tidak ada data penawaran",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data penawaran yang sesuai",
                    emptyTable: "Belum ada dokumen penawaran harga",
                    processing: '<div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat data...',
                    paginate: {
                        first: '<i class="bi bi-chevron-double-left"></i>',
                        last: '<i class="bi bi-chevron-double-right"></i>',
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            });

            // Update Active Filter Pills & Badges
            function updateFilterBadges() {
                let activeCount = 0;
                let tagsHtml = '';

                let custVal = $('#filterCustomer').val();
                let custText = $('#filterCustomer option:selected').text();
                if (custVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-indigo fs-9 px-2 py-1 rounded-pill">Customer: ${custText}</span>`;
                }

                let statVal = $('#filterStatus').val();
                if (statVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-sky fs-9 px-2 py-1 rounded-pill">Status: ${statVal}</span>`;
                }

                let tenderVal = $('#filterTender').val();
                let tenderText = $('#filterTender option:selected').text();
                if (tenderVal) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-amber fs-9 px-2 py-1 rounded-pill">Proyek: ${tenderText}</span>`;
                }

                let incRev = $('#checkIncludeRevisions').is(':checked');
                if (incRev) {
                    activeCount++;
                    tagsHtml += `<span class="badge badge-soft-neutral fs-9 px-2 py-1 rounded-pill">Termasuk Revisi Lama</span>`;
                }

                if (activeCount > 0) {
                    $('#filterCountBadge').text(activeCount).show();
                    $('#activeFilterTags').html(tagsHtml);
                    $('#activeFilterContainer').attr('style', 'display: flex !important;');
                } else {
                    $('#filterCountBadge').hide();
                    $('#activeFilterTags').empty();
                    $('#activeFilterContainer').attr('style', 'display: none !important;');
                }
            }

            // Apply Filter from Modal
            $('#btnApplyFilterModal').on('click', function() {
                updateFilterBadges();
                table.ajax.reload();
            });

            // Reset Filter Function
            function resetAllFilters() {
                $('#filterCustomer').val('').trigger('change');
                $('#filterStatus').val('').trigger('change');
                $('#filterTender').val('').trigger('change');
                $('#checkIncludeRevisions').prop('checked', false);
                updateFilterBadges();
                table.ajax.reload();
            }

            $('#btnResetFilterModal').on('click', function() {
                resetAllFilters();
                $('#modalFilterQuotation').modal('hide');
            });

            $('#btnClearAllFilters').on('click', function() {
                resetAllFilters();
            });

            // View Detail Function
            window.loadQuotationDetail = function(id) {
                $('#modalDetailQuotation').modal('show');
                $('#detailBodyContent').html(`
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat data detail penawaran...
                    </div>
                `);

                $.ajax({
                    url: `/penawaran/${id}`,
                    method: 'GET',
                    success: function(resp) {
                        const res = resp;
                        const data = resp.data || resp;
                        const allRevisions = resp.all_revisions || [];
                        const canSeeHpp = resp.can_see_hpp || false;
                        const canApprove = (resp.can_approve || resp.can_approve == 1 || resp.can_approve === true);
                        const canConvertSo = (resp.can_convert_so || resp.can_convert_so == 1 || resp.can_convert_so === true);
                        const canPrint = (resp.can_print || resp.can_print == 1 || resp.can_print === true);

                        // Header info
                        let revBadge = data.revisi > 0 
                            ? `<span class="badge badge-soft-indigo fs-9 ms-1 rounded-pill"><i class="bi bi-clock-history me-0.5"></i>Rev.${data.revisi}</span>`
                            : `<span class="badge badge-soft-neutral fs-9 ms-1 rounded-pill text-muted">Rev.0</span>`;

                        $('#detailNomor').html(`<span class="font-monospace">${data.nomor_quotation}</span> ${revBadge}`);
                        $('#detailPerihal').text(data.perihal || 'Penawaran Harga');

                        // Status Badge in Header
                        let statusBadges = {
                            'Draft': '<span class="badge badge-soft-neutral rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-pencil-square me-1"></i>Draft</span>',
                            'Sent': '<span class="badge badge-soft-sky rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-send me-1"></i>Terkirim</span>',
                            'Revised': '<span class="badge badge-soft-amber rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-arrow-repeat me-1"></i>Direvisi</span>',
                            'Approved': '<span class="badge badge-soft-emerald rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>',
                            'Rejected': '<span class="badge badge-soft-rose rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>',
                            'Converted_to_SO': '<span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-cart-check-fill me-1"></i>Jadi SO</span>'
                        };
                        $('#detailStatusBadge').html(statusBadges[data.status] || `<span class="badge bg-secondary">${data.status}</span>`);

                        // Status & Workflow Banner
                        let approvalBannerHtml = '';
                        if (data.status === 'Draft') {
                            let isCreatorOrAdmin = (data.created_by == {{ auth()->id() ?? 'null' }} || {{ (auth()->user() && auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) ? 'true' : 'false' }});
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-draft">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-neutral p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-pencil-square fs-5 text-secondary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-body d-flex align-items-center gap-2">
                                                Status: Draf Internal
                                                <span class="badge badge-soft-neutral rounded-pill fs-9">Tahap Konseptual</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Dokumen penawaran masih berupa draf internal dan belum diajukan untuk proses approval/pengiriman ke klien.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        ${isCreatorOrAdmin ? `
                                            <button type="button" class="btn btn-sm btn-primary px-3.5 py-1.5 rounded-pill btn-ajukan-modal shadow-xs" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                                <i class="bi bi-send-fill me-1"></i> Ajukan Penawaran
                                            </button>
                                        ` : `
                                            <span class="badge badge-soft-neutral fs-8 py-1.5 px-3 rounded-pill text-muted">
                                                <i class="bi bi-clock me-1"></i> Menunggu Diajukan oleh Pembuat
                                            </span>
                                        `}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Sent') {
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-sent">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-sky p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-hourglass-split fs-5 text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-primary d-flex align-items-center gap-2">
                                                Menunggu Persetujuan (Pending Approval)
                                                <span class="badge bg-primary text-white fs-9 rounded-pill px-2.5 py-0.5">Need Action</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Penawaran telah diajukan dan sedang menunggu tinjauan serta persetujuan dari Manajer / Direktur.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${canApprove ? `
                                            <button type="button" class="btn btn-sm btn-success px-3.5 py-1.5 rounded-pill btn-approve-sq shadow-sm" data-id="${data.id}" data-status="Approved">
                                                <i class="bi bi-check-circle-fill me-1"></i> Setujui (Approve)
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Rejected">
                                                <i class="bi bi-x-circle me-1"></i> Tolak
                                            </button>
                                        ` : `
                                            <span class="badge badge-soft-neutral fs-8 py-2 px-3 rounded-pill text-muted">
                                                <i class="bi bi-lock me-1"></i> Menunggu Otorisasi Approver
                                            </span>
                                        `}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Approved') {
                            let approverName = data.approver ? (data.approver.nama_lengkap || data.approver.name) : 'Manajer/Direktur';
                            let approvedTime = data.approved_at ? formatIndoDate(data.approved_at) : '';
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-approved">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-emerald p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-patch-check-fill fs-5 text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-success d-flex align-items-center gap-2">
                                                Penawaran Telah Disetujui (Approved)
                                                <span class="badge badge-soft-emerald rounded-pill fs-9 px-2.5 py-0.5">Resmi Valid</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Disetujui oleh <strong class="text-body">${approverName}</strong> ${approvedTime ? `pada ${approvedTime}` : ''}. Dokumen siap dicetak atau dikonversi ke Sales Order.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        ${!data.sales_order_id && canConvertSo ? `
                                            <button type="button" class="btn btn-sm btn-success px-3.5 py-1.5 rounded-pill btn-convert-so shadow-sm" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                                <i class="bi bi-cart-check-fill me-1"></i> Konversi ke Sales Order (SO)
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Rejected') {
                            let rejectorName = data.approver ? (data.approver.nama_lengkap || data.approver.name) : 'Manajer';
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-rejected">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-rose p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-x-octagon-fill fs-5 text-danger"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-danger d-flex align-items-center gap-2">
                                                Penawaran Ditolak (Rejected)
                                                <span class="badge badge-soft-rose rounded-pill fs-9 px-2.5 py-0.5">Perlu Penyesuaian</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Ditinjau oleh <strong class="text-body">${rejectorName}</strong>. Silakan buat revisi baru untuk menyesuaikan harga atau spesifikasi.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-warning px-3.5 py-1.5 rounded-pill btn-revisi shadow-xs" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                            <i class="bi bi-arrow-repeat me-1"></i> Buat Revisi Baru
                                        </button>
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Converted_to_SO') {
                            let soNumber = data.sales_order ? data.sales_order.nomor_so : 'SO Terbit';
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-so">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-indigo p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-bag-check-fill fs-5 text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-primary d-flex align-items-center gap-2">
                                                Terkonversi ke Sales Order (SO)
                                                <span class="badge badge-soft-indigo rounded-pill fs-9 px-2.5 py-0.5">Terbit Transaksi</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Transaksi resmi diproses dengan nomor SO <strong class="text-body font-monospace">${soNumber}</strong>.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('marketing.sales-order.index') }}" class="btn btn-sm btn-outline-primary px-3.5 py-1.5 rounded-pill">
                                            <i class="bi bi-arrow-right-circle me-1"></i> Buka Menu Sales Order
                                        </a>
                                    </div>
                                </div>
                            `;
                        } else if (data.status === 'Revised') {
                            approvalBannerHtml = `
                                <div class="sq-status-banner banner-draft">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="badge-soft-amber p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-arrow-counterclockwise fs-5 text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold fs-7 text-body d-flex align-items-center gap-2">
                                                Arsip Revisi Lama
                                                <span class="badge badge-soft-amber rounded-pill fs-9">Superseded</span>
                                            </div>
                                            <div class="fs-8 text-muted mt-0.5">Versi penawaran ini telah digantikan oleh nomor revisi yang lebih baru.</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }

                        // Quick Metric Strip Calculations
                        let totalItemCount = data.items ? data.items.length : 0;
                        let totalQtyCount = 0;
                        let totalHpp = 0;
                        if (data.items && data.items.length > 0) {
                            data.items.forEach(it => {
                                totalQtyCount += parseFloat(it.qty || 0);
                                totalHpp += (parseFloat(it.hpp_satuan || 0) * parseFloat(it.qty || 0));
                            });
                        }

                        let tipePpn = data.tipe_ppn || data.tipe_pajak || 'Non';
                        let tarifPpn = parseFloat(data.tarif_ppn || 0);
                        let nilaiPpn = parseFloat(data.nilai_ppn || 0);

                        let pajakLabel = 'Non-PPN';
                        if (tipePpn === 'Include') {
                            pajakLabel = `PPN ${tarifPpn > 0 ? tarifPpn + '%' : '11%'} (Incl)`;
                        } else if (tipePpn === 'Exclude') {
                            pajakLabel = `PPN ${tarifPpn > 0 ? tarifPpn + '%' : '11%'} (Excl)`;
                        } else if (nilaiPpn > 0) {
                            pajakLabel = `PPN ${tarifPpn > 0 ? tarifPpn + '%' : '11%'}`;
                        }

                        let marginHtml = '';
                        if (canSeeHpp && totalHpp > 0) {
                            let netRevenue = (tipePpn === 'Include') ? (parseFloat(data.dpp || data.subtotal) - nilaiPpn) : parseFloat(data.dpp || data.subtotal);
                            let totalMargin = data.estimasi_laba_kotor !== null && data.estimasi_laba_kotor !== undefined ? parseFloat(data.estimasi_laba_kotor) : (netRevenue - totalHpp);
                            let marginPercent = data.margin_persen !== null && data.margin_persen !== undefined ? parseFloat(data.margin_persen).toFixed(1) : (netRevenue > 0 ? ((totalMargin / netRevenue) * 100).toFixed(1) : 0);
                            let marginColor = totalMargin >= 0 ? 'text-success' : 'text-danger';
                            marginHtml = `
                                <div class="sq-metric-item">
                                    <div class="sq-metric-icon sq-kpi-icon-emerald">
                                         <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fs-9 text-muted text-uppercase fw-semibold text-truncate" style="letter-spacing: 0.04em; font-size: 0.65rem;">MARGIN (${marginPercent}%)</div>
                                        <div class="fw-bold fs-7 ${marginColor} font-monospace text-truncate">Rp ${Math.round(totalMargin).toLocaleString('id-ID')}</div>
                                    </div>
                                </div>
                            `;
                        }

                        let metricStripHtml = `
                            <div class="sq-detail-metric-strip">
                                <div class="sq-metric-item">
                                    <div class="sq-metric-icon sq-kpi-icon-primary">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fs-9 text-muted text-uppercase fw-semibold text-truncate" style="letter-spacing: 0.04em; font-size: 0.65rem;">TOTAL</div>
                                        <div class="fw-bold fs-7 font-monospace text-primary text-truncate">Rp ${parseFloat(data.grand_total).toLocaleString('id-ID')}</div>
                                    </div>
                                </div>

                                <div class="sq-metric-item">
                                    <div class="sq-metric-icon sq-kpi-icon-sky">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fs-9 text-muted text-uppercase fw-semibold text-truncate" style="letter-spacing: 0.04em; font-size: 0.65rem;">ITEM</div>
                                        <div class="fw-bold fs-7 text-body text-truncate">${totalItemCount} Item <span class="text-muted fw-normal fs-9">(${totalQtyCount} Qty)</span></div>
                                    </div>
                                </div>

                                <div class="sq-metric-item">
                                    <div class="sq-metric-icon sq-kpi-icon-purple">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fs-9 text-muted text-uppercase fw-semibold text-truncate" style="letter-spacing: 0.04em; font-size: 0.65rem;">PAJAK</div>
                                        <div class="fw-bold fs-7 text-body text-truncate">${pajakLabel}</div>
                                    </div>
                                </div>

                                ${marginHtml}
                            </div>
                        `;

                        // Revision Timeline Card (if multiple revisions exist)
                        let revisionTimelineHtml = '';
                        if (allRevisions.length > 1) {
                            let revListHtml = '';
                            allRevisions.forEach((rev) => {
                                let isCurrent = (rev.id === data.id);
                                let dotClass = isCurrent ? '' : 'is-old';
                                let btnAction = isCurrent 
                                    ? `<span class="badge badge-soft-indigo fs-9 px-2.5 py-1 rounded-pill fw-semibold">Versi Aktif Saat Ini</span>`
                                    : `<button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2.5 fs-9" onclick="loadQuotationDetail(${rev.id})">Buka Versi Ini <i class="bi bi-arrow-right ms-0.5"></i></button>`;

                                revListHtml += `
                                    <div class="revision-timeline-item pb-3">
                                        <div class="revision-timeline-dot ${dotClass}"></div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <div>
                                                <span class="fw-bold fs-8 ${isCurrent ? 'text-primary' : 'text-body'}">Revisi ${rev.revisi}</span>
                                                <span class="text-muted fs-9 ms-1">(${formatIndoDate(rev.tanggal_quotation)})</span>
                                                <span class="ms-1">${statusBadges[rev.status] || rev.status}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold font-monospace fs-8">Rp ${parseFloat(rev.grand_total).toLocaleString('id-ID')}</span>
                                                ${btnAction}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            revisionTimelineHtml = `
                                <div class="sq-bento-card mb-4">
                                    <div class="sq-bento-title">
                                        <i class="bi bi-clock-history"></i> Riwayat Rantai Revisi (${allRevisions.length} Versi)
                                    </div>
                                    <div class="pt-1">
                                        ${revListHtml}
                                    </div>
                                </div>
                            `;
                        }

                        // Items breakdown table
                        let itemsHtml = '';
                        if (data.items && data.items.length > 0) {
                            data.items.forEach((item, idx) => {
                                let namaItem = item.nama_barang_custom || (item.barang ? item.barang.nama_barang : '-');
                                let kodeItem = item.barang ? item.barang.kode_barang : 'CUSTOM';
                                let satuan = item.nama_satuan || (item.barang && item.barang.satuan ? item.barang.satuan.nama_satuan : 'Pcs');

                                let thumbHtml = item.photo_url 
                                    ? `<img src="${item.photo_url}" class="sq-item-thumb me-2.5" alt="${namaItem}">`
                                    : `<div class="sq-item-thumb-placeholder me-2.5"><i class="bi bi-box-seam"></i></div>`;

                                let diskonHtml = '<span class="text-muted">-</span>';
                                if (parseFloat(item.diskon_persen) > 0) {
                                    diskonHtml = `<span class="badge bg-danger bg-opacity-10 text-danger fw-bold fs-9 px-2 py-0.5 rounded-pill">-${parseFloat(item.diskon_persen)}%</span>`;
                                } else if (parseFloat(item.diskon_nominal) > 0) {
                                    diskonHtml = `<span class="badge bg-danger bg-opacity-10 text-danger font-monospace fs-9 px-2 py-0.5 rounded-pill">-Rp ${parseFloat(item.diskon_nominal).toLocaleString('id-ID')}</span>`;
                                }

                                itemsHtml += `
                                    <tr>
                                        <td class="text-center text-muted fs-8">${idx + 1}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                ${thumbHtml}
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold text-body fs-8">${namaItem}</div>
                                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                                        <code class="badge badge-soft-neutral fs-9 px-1.5 py-0.5">${kodeItem}</code>
                                                        ${item.kategori ? `<span class="badge badge-soft-sky fs-9 px-1.5 py-0.5">${item.kategori.nama_kategori}</span>` : ''}
                                                    </div>
                                                    ${item.deskripsi_item ? `<div class="fs-9 text-muted mt-1">${item.deskripsi_item}</div>` : ''}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fs-8 fw-semibold">
                                            ${parseFloat(item.qty).toLocaleString('id-ID')}
                                            <span class="badge badge-soft-neutral fs-9 px-1.5 py-0.5 ms-0.5">${satuan}</span>
                                        </td>
                                        <td class="text-end fs-8 font-monospace">Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}</td>
                                        <td class="text-end fs-8">${diskonHtml}</td>
                                        <td class="text-end fw-bold text-body fs-8 font-monospace">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</td>
                                    </tr>
                                `;
                            });
                        }

                        // Customer info
                        let custAddress = '';
                        let custPhone = '';
                        let custEmail = '';
                        if (data.customer) {
                            if (data.customer.default_address) {
                                custAddress = data.customer.default_address.alamat || '';
                                if (data.customer.default_address.kota) {
                                    custAddress += (custAddress ? ', ' : '') + data.customer.default_address.kota;
                                }
                            }
                            if (!custAddress) {
                                custAddress = data.customer.alamat || '-';
                            }
                            custPhone = data.customer.telepon || '';
                            custEmail = data.customer.email || '';
                        }

                        // Payment Term display
                        let termText = 'Cash / Tunai';
                        if (data.payment_term) {
                            termText = `${data.payment_term.nama_term || data.payment_term.nama_termin} (${data.payment_term.jumlah_hari || 0} Hari)`;
                        } else if (data.catatan_pembayaran) {
                            termText = data.catatan_pembayaran;
                        }

                        // Assemble modern modal content
                        let html = `
                            ${approvalBannerHtml}
                            ${metricStripHtml}
                            ${revisionTimelineHtml}

                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <div class="sq-bento-card h-100">
                                        <div class="sq-bento-title">
                                            <i class="bi bi-building"></i> Informasi Klien & Proyek
                                        </div>
                                        <div class="fw-bold text-body fs-7 d-flex align-items-center gap-1.5">
                                            <i class="bi bi-patch-check text-primary"></i>
                                            ${data.customer ? (data.customer.nama_perusahaan || data.customer.nama_customer) : '-'}
                                        </div>
                                        <div class="fs-8 text-muted mt-1"><i class="bi bi-geo-alt me-1 text-secondary"></i>${custAddress}</div>
                                        ${custPhone || custEmail ? `
                                            <div class="fs-9 text-muted mt-1 d-flex flex-wrap gap-2">
                                                ${custPhone ? `<span><i class="bi bi-telephone me-1"></i>${custPhone}</span>` : ''}
                                                ${custEmail ? `<span><i class="bi bi-envelope me-1"></i>${custEmail}</span>` : ''}
                                            </div>
                                        ` : ''}

                                        ${data.tender ? `
                                            <div class="sq-detail-tender-box">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fs-9 fw-bold text-uppercase text-primary"><i class="bi bi-briefcase-fill me-1"></i> Tender Terkait</span>
                                                    <span class="badge badge-soft-sky fs-9 font-monospace">${data.tender.nomor_kontrak || 'Non-Kontrak'}</span>
                                                </div>
                                                <div class="fw-bold text-body fs-8">${data.tender.nama_tender}</div>
                                                ${data.tender.nilai_pagu ? `<div class="fs-9 text-muted mt-0.5">Pagu Proyek: <span class="font-monospace fw-semibold text-body">Rp ${parseFloat(data.tender.nilai_pagu).toLocaleString('id-ID')}</span></div>` : ''}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="sq-bento-card h-100">
                                        <div class="sq-bento-title">
                                            <i class="bi bi-sliders"></i> Parameter & Ketentuan Transaksi
                                        </div>
                                        <div class="sq-detail-info-row">
                                            <span class="text-muted"><i class="bi bi-calendar3 me-1.5 text-secondary"></i>Tgl Penawaran:</span>
                                            <span class="fw-semibold text-body">${formatIndoDate(data.tanggal_quotation)}</span>
                                        </div>
                                        <div class="sq-detail-info-row">
                                            <span class="text-muted"><i class="bi bi-calendar-check me-1.5 text-secondary"></i>Masa Berlaku:</span>
                                            <span class="fw-semibold text-body">${formatIndoDate(data.berlaku_hingga) || '30 Hari'}</span>
                                        </div>
                                        <div class="sq-detail-info-row">
                                            <span class="text-muted"><i class="bi bi-person-badge me-1.5 text-secondary"></i>Sales / PIC:</span>
                                            <span class="fw-semibold text-body">${data.sales ? data.sales.nama_lengkap : (data.creator ? data.creator.name : '-')}</span>
                                        </div>
                                        <div class="sq-detail-info-row">
                                            <span class="text-muted"><i class="bi bi-credit-card me-1.5 text-secondary"></i>Term Pembayaran:</span>
                                            <span class="fw-semibold text-body">${termText}</span>
                                        </div>
                                        <div class="sq-detail-info-row">
                                            <span class="text-muted"><i class="bi bi-cash-coin me-1.5 text-secondary"></i>Uang Muka (DP):</span>
                                            <span class="fw-semibold text-body">
                                                ${parseFloat(data.dp_nominal) > 0 
                                                    ? `<span class="badge badge-soft-indigo font-monospace fs-9">Rp ${parseFloat(data.dp_nominal).toLocaleString('id-ID')} ${parseFloat(data.dp_persen) > 0 ? `(${parseFloat(data.dp_persen)}%)` : ''}</span>`
                                                    : `<span class="badge badge-soft-neutral fs-9">Tanpa DP (0%)</span>`}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold fs-7 mb-0 text-body"><i class="bi bi-box-seam me-1 text-primary"></i> Rincian Item Penawaran</h6>
                                <span class="badge badge-soft-neutral fs-9 rounded-pill px-2.5 py-1">${totalItemCount} Item Terdaftar</span>
                            </div>

                            <!-- Mobile Swipe Hint Banner -->
                            <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-1.5 mb-2 rounded-3 text-primary shadow-2xs sq-mobile-swipe-banner">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-arrow-left-right"></i>
                                    <span>Tabel rincian item dapat digeser ke samping</span>
                                </div>
                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.7rem;">Swipe 👉</span>
                            </div>

                            <div class="table-responsive mb-4 rounded-3 border" style="-webkit-overflow-scrolling: touch !important;">
                                <table class="table table-sm sq-detail-table align-middle mb-0" style="min-width: 680px; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 45px; min-width: 45px;">#</th>
                                            <th style="min-width: 250px;">Item &amp; Spesifikasi</th>
                                            <th class="text-center" style="width: 100px; min-width: 90px; white-space: nowrap;">Qty</th>
                                            <th class="text-end" style="width: 130px; min-width: 115px; white-space: nowrap;">Harga Satuan</th>
                                            <th class="text-end" style="width: 105px; min-width: 95px; white-space: nowrap;">Diskon</th>
                                            <th class="text-end" style="width: 145px; min-width: 130px; white-space: nowrap;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold fs-8 text-nowrap">Subtotal Item:</td>
                                            <td class="text-end fw-bold fs-8 font-monospace text-nowrap">Rp ${parseFloat(data.subtotal).toLocaleString('id-ID')}</td>
                                        </tr>
                                        ${parseFloat(data.diskon_nominal) > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8 text-nowrap">Diskon Tambahan (${parseFloat(data.diskon_persen) > 0 ? parseFloat(data.diskon_persen) + '%' : 'Nominal'}):</td>
                                                <td class="text-end text-danger fs-8 font-monospace text-nowrap">- Rp ${parseFloat(data.diskon_nominal).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        ${(parseFloat(data.nilai_ppn) > 0 || (data.tipe_ppn && data.tipe_ppn !== 'Non')) ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8 text-nowrap">PPN (${parseFloat(data.tarif_ppn || 11)}%${(data.tipe_ppn === 'Include') ? ' - Termasuk' : ''}):</td>
                                                <td class="text-end fs-8 font-monospace text-nowrap">${(data.tipe_ppn === 'Include') ? `<span class="badge badge-soft-sky">Termasuk (Rp ${parseFloat(data.nilai_ppn || 0).toLocaleString('id-ID')})</span>` : `+ Rp ${parseFloat(data.nilai_ppn || 0).toLocaleString('id-ID')}`}</td>
                                            </tr>
                                        ` : ''}
                                        ${parseFloat(data.nilai_pph) > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8 text-nowrap">PPh (${parseFloat(data.tarif_pph || 0)}%):</td>
                                                <td class="text-end fs-8 font-monospace text-nowrap">+ Rp ${parseFloat(data.nilai_pph).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        ${parseFloat(data.biaya_pengiriman) > 0 ? `
                                            <tr>
                                                <td colspan="5" class="text-end text-muted fs-8 text-nowrap">Biaya Pengiriman:</td>
                                                <td class="text-end fs-8 font-monospace text-nowrap">+ Rp ${parseFloat(data.biaya_pengiriman).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                        <tr class="sq-grand-total-row">
                                            <td colspan="5" class="text-end text-white text-nowrap">Grand Total Penawaran:</td>
                                            <td class="text-end text-white font-monospace text-nowrap">Rp ${parseFloat(data.grand_total).toLocaleString('id-ID')}</td>
                                        </tr>
                                        ${parseFloat(data.dp_nominal) > 0 ? `
                                            <tr style="background: rgba(99, 102, 241, 0.05);">
                                                <td colspan="5" class="text-end fw-semibold text-primary fs-8 text-nowrap">
                                                    <i class="bi bi-cash-coin me-1"></i> Minimal Uang Muka (DP ${parseFloat(data.dp_persen) > 0 ? parseFloat(data.dp_persen) + '%' : 'Nominal'}):
                                                </td>
                                                <td class="text-end fw-bold text-primary fs-8 font-monospace text-nowrap">Rp ${parseFloat(data.dp_nominal).toLocaleString('id-ID')}</td>
                                            </tr>
                                        ` : ''}
                                    </tfoot>
                                </table>
                            </div>

                            ${resp.terbilang ? `
                                <div class="sq-terbilang-banner mb-3">
                                    <i class="bi bi-chat-quote-fill me-1"></i>
                                    <strong>Terbilang:</strong> <em>${resp.terbilang}</em>
                                </div>
                            ` : ''}

                            ${data.catatan || data.syarat_ketentuan ? `
                                <div class="row g-3 mb-2">
                                    ${data.catatan ? `
                                        <div class="col-12 col-md-6">
                                            <div class="sq-bento-card h-100">
                                                <div class="sq-bento-title">
                                                    <i class="bi bi-sticky"></i> Catatan Internal / Klien
                                                </div>
                                                <div class="fs-8 text-body">${data.catatan}</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                    ${data.syarat_ketentuan ? `
                                        <div class="col-12 col-md-6">
                                            <div class="sq-bento-card h-100">
                                                <div class="sq-bento-title">
                                                    <i class="bi bi-shield-check"></i> Syarat & Ketentuan
                                                </div>
                                                <div class="fs-8 text-body">${data.syarat_ketentuan}</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        `;

                        $('#detailBodyContent').html(html);

                        // Actions in modal footer
                        let leftBtns = '';
                        if (resp.can_print) {
                            leftBtns += `<a href="/penawaran/${data.id}/print" target="_blank" class="btn btn-sm btn-outline-secondary px-3.5 rounded-pill"><i class="bi bi-printer me-1"></i> Cetak PDF</a>`;
                        }

                        // Approval buttons (Only visible for users with approve-penawaran permission / Super Admin / Owner)
                        const isPending = (data.status === 'Sent' || data.status === 'Pending Approval');
                        if (isPending && (resp.can_approve || resp.can_approve == 1 || resp.can_approve === true)) {
                            leftBtns += `
                                <button type="button" class="btn btn-sm btn-success px-3.5 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Approved">
                                    <i class="bi bi-check-circle me-1"></i> Setujui (Approve)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger px-3.5 rounded-pill btn-approve-sq" data-id="${data.id}" data-status="Rejected">
                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                </button>
                            `;
                        } else if (data.status === 'Draft') {
                            let isCreatorOrAdmin = (data.created_by == {{ auth()->id() ?? 'null' }} || {{ (auth()->user() && auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) ? 'true' : 'false' }});
                            if (isCreatorOrAdmin) {
                                leftBtns += `
                                    <button type="button" class="btn btn-sm btn-primary px-3.5 rounded-pill btn-ajukan-modal" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                        <i class="bi bi-send-fill me-1"></i> Ajukan Penawaran
                                    </button>
                                `;
                            }
                        } else if (data.status === 'Approved' && !data.sales_order_id && (resp.can_convert_so || resp.can_convert_so == 1 || resp.can_convert_so === true)) {
                            leftBtns += `
                                <button type="button" class="btn btn-sm btn-success px-3.5 rounded-pill btn-convert-so" data-id="${data.id}" data-nomor="${data.nomor_quotation}">
                                    <i class="bi bi-cart-plus me-1"></i> Konversi ke Sales Order (SO)
                                </button>
                            `;
                        }
                        $('#detailActionLeft').html(leftBtns);
                    },
                    error: function(xhr) {
                        $('#detailBodyContent').html(`
                            <div class="alert alert-danger m-4 text-center rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Gagal memuat data detail: ${xhr.responseJSON?.message || xhr.statusText || 'Terjadi kesalahan sistem.'}
                            </div>
                        `);
                    }
                });
            };

            // View Detail Click
            $(document).on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                loadQuotationDetail(id);
            });

            // Create Revision Handler
            $(document).on('click', '.btn-revisi', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Buat Revisi Penawaran?',
                    text: `Sistem akan membuat salinan penawaran ${nomor} dengan nomor revisi baru dan menandai penawaran lama sebagai 'Revised'.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Buat Revisi',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/revisi`,
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Revisi Berhasil Dibuat!',
                                    text: resp.message || 'Penawaran revisi baru siap disunting.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                                const targetUrl = resp.redirect || resp.redirect_url;
                                if (targetUrl) {
                                    window.location.href = targetUrl;
                                }
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Membuat Revisi',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Handle Ajukan Penawaran (Draft -> Sent)
            $(document).on('click', '.btn-ajukan-sq, .btn-ajukan-modal', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor') || 'penawaran ini';

                Swal.fire({
                    title: 'Ajukan Penawaran?',
                    html: `Apakah Anda yakin ingin mengajukan penawaran <strong>${nomor}</strong>?<br><small class="text-muted">Setelah diajukan, status akan berubah menjadi <em>Menunggu Persetujuan (Sent)</em> dan siap ditinjau oleh Manajer/Direktur.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Ajukan Sekarang',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang mengajukan penawaran...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: `/penawaran/${id}/ajukan`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalDetailQuotation').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Diajukan!',
                                    text: res.message || 'Penawaran berhasil diajukan.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Mengajukan',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengajukan penawaran.'
                                });
                            }
                        });
                    }
                });
            });

            // Approve / Reject Quotation Handler
            $(document).on('click', '.btn-approve-sq', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');
                const isApproved = (status === 'Approved');

                Swal.fire({
                    title: isApproved ? 'Setujui Penawaran Ini?' : 'Tolak Penawaran Ini?',
                    text: isApproved ? 'Status penawaran akan diubah menjadi Approved dan siap diterbitkan Sales Order (SO).' : 'Status penawaran akan ditandai sebagai Rejected.',
                    icon: isApproved ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isApproved ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: isApproved ? 'Ya, Setujui' : 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/approve`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: status
                            },
                            success: function(resp) {
                                $('#modalDetailQuotation').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Status Berhasil Diperbarui',
                                    text: resp.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Memproses Status',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Convert to SO Handler
            $(document).on('click', '.btn-convert-so', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Konversi ke Sales Order (SO)?',
                    text: `Dokumen penawaran ${nomor} akan diterbitkan menjadi Sales Order resmi.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Terbitkan SO',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/convert-so`,
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                $('#modalDetailQuotation').modal('hide');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sales Order Berhasil Diterbitkan!',
                                    text: resp.message,
                                    confirmButtonText: 'Buka Sales Order',
                                    showCancelButton: true,
                                    cancelButtonText: 'Tetap di Sini'
                                }).then((navResult) => {
                                    table.ajax.reload();
                                    const targetUrl = resp.redirect || resp.redirect_url;
                                    if (navResult.isConfirmed && targetUrl) {
                                        window.location.href = targetUrl;
                                    }
                                });
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Konversi SO',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });

            // Delete Quotation
            $(document).on('click', '.btn-delete-sq, .btn-delete', function() {
                const id = $(this).data('id');
                const nomor = $(this).data('nomor');

                Swal.fire({
                    title: 'Hapus Penawaran?',
                    text: `Apakah Anda yakin ingin menghapus draft penawaran ${nomor}? Tindakan ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/penawaran/${id}/delete`,
                            method: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(resp) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus',
                                    text: resp.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menghapus',
                                    text: err.responseJSON?.message || 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts>
