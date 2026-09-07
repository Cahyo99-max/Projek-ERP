<x-layouts title="Transfer Gudang (Warehouse Transfer)">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* ─── Global Custom Variable Themes ───────────────────────── */
        :root {
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --accent-color: #3b82f6;
            --route-box-bg: #f8fafc;
            --route-box-border: #e2e8f0;
            
            --badge-draft-bg: rgba(245, 158, 11, 0.12);
            --badge-draft-text: #d97706;
            --badge-draft-border: rgba(245, 158, 11, 0.3);

            --badge-pending-bg: rgba(14, 165, 233, 0.12);
            --badge-pending-text: #0284c7;
            --badge-pending-border: rgba(14, 165, 233, 0.3);

            --badge-confirmed-bg: rgba(16, 185, 129, 0.12);
            --badge-confirmed-text: #059669;
            --badge-confirmed-border: rgba(16, 185, 129, 0.3);

            --table-header-bg: #f8fafc;
            --table-hover-bg: #f1f5f9;
        }

        [data-bs-theme="dark"], .dark-mode {
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --bg-light: #0f172a;
            --accent-color: #60a5fa;
            --route-box-bg: #0f172a;
            --route-box-border: #334155;

            --badge-draft-bg: rgba(245, 158, 11, 0.2);
            --badge-draft-text: #fbbf24;
            --badge-draft-border: rgba(245, 158, 11, 0.4);

            --badge-pending-bg: rgba(14, 165, 233, 0.2);
            --badge-pending-text: #38bdf8;
            --badge-pending-border: rgba(14, 165, 233, 0.4);

            --badge-confirmed-bg: rgba(16, 185, 129, 0.2);
            --badge-confirmed-text: #34d399;
            --badge-confirmed-border: rgba(16, 185, 129, 0.4);

            --table-header-bg: #0f172a;
            --table-hover-bg: #27354a;
        }

        /* Applying Variables */
        .card { 
            background-color: var(--card-bg) !important; 
            border: 1px solid var(--border-color) !important; 
        }
        .card-header { 
            background-color: var(--card-bg) !important; 
            border-bottom: 1px solid var(--border-color) !important; 
        }
        .modal-content { 
            background-color: var(--card-bg) !important; 
            color: var(--text-main); 
        }
        .modal-header { 
            border-bottom: 1px solid var(--border-color) !important; 
        }
        .modal-footer { 
            background-color: var(--bg-light) !important; 
            border-top: 1px solid var(--border-color) !important; 
        }
        .form-control, .form-select { 
            background-color: var(--card-bg) !important; 
            border-color: var(--border-color) !important; 
            color: var(--text-main) !important; 
        }
        .form-control:focus, .form-select:focus { 
            border-color: var(--accent-color) !important; 
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25) !important; 
        }
        .form-control:disabled { 
            background-color: var(--bg-light) !important; 
            color: var(--text-muted) !important; 
        }
        .text-dark { color: var(--text-main) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .bg-light { background-color: var(--bg-light) !important; }

        /* ─── Status Badges ─────────────────────────────────────── */
        .badge-draft { 
            background: var(--badge-draft-bg); 
            color: var(--badge-draft-text); 
            border: 1px solid var(--badge-draft-border);
            padding: .35em .85em; 
            border-radius: 50px; 
            font-size: .78rem; 
            font-weight: 700; 
            display: inline-flex;
            align-items: center;
        }
        .badge-pending { 
            background: var(--badge-pending-bg); 
            color: var(--badge-pending-text); 
            border: 1px solid var(--badge-pending-border);
            padding: .35em .85em; 
            border-radius: 50px; 
            font-size: .78rem; 
            font-weight: 700; 
            display: inline-flex;
            align-items: center;
        }
        .badge-confirmed { 
            background: var(--badge-confirmed-bg); 
            color: var(--badge-confirmed-text); 
            border: 1px solid var(--badge-confirmed-border);
            padding: .35em .85em; 
            border-radius: 50px; 
            font-size: .78rem; 
            font-weight: 700; 
            display: inline-flex;
            align-items: center;
        }

        /* ─── Count Badge ────────────────────────────────────────── */
        .trf-count-badge {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 14px;
            letter-spacing: 0.3px;
        }
        [data-bs-theme="dark"] .trf-count-badge,
        .dark-mode .trf-count-badge {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.4);
        }

        /* ─── Table Modern & Cinematic Horizontal Scroll ───────── */
        .table-responsive-trf {
            width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.4) transparent;
        }
        .table-responsive-trf::-webkit-scrollbar {
            height: 7px;
        }
        .table-responsive-trf::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
            border-radius: 4px;
        }
        [data-bs-theme="dark"] .table-responsive-trf::-webkit-scrollbar-track,
        .dark-mode .table-responsive-trf::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.03);
        }
        .table-responsive-trf::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.4);
            border-radius: 4px;
        }
        .table-responsive-trf::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }

        .trf-table-scroll-hint {
            background: rgba(59, 130, 246, 0.06);
            border-bottom: 1px solid rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            font-size: 0.76rem;
            padding: 7px 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        [data-bs-theme="dark"] .trf-table-scroll-hint,
        .dark-mode .trf-table-scroll-hint {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
        }

        #tableTransfer {
            width: 100% !important;
            margin: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        .table-modern th {
            background: var(--table-header-bg) !important; 
            color: var(--text-muted) !important; 
            font-weight: 700;
            text-transform: uppercase; 
            font-size: .72rem; 
            letter-spacing: .5px;
            padding: 14px 16px !important; 
            border-bottom: 2px solid var(--border-color) !important;
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-modern td { 
            padding: 12px 16px !important; 
            border-bottom: 1px solid var(--border-color) !important; 
            vertical-align: middle; 
            color: var(--text-main); 
            background: transparent !important;
        }
        .table-modern tbody tr { 
            cursor: pointer; 
            transition: all .15s ease-in-out; 
        }
        .table-modern tbody tr:hover { 
            background: var(--table-hover-bg) !important; 
        }

        /* ─── Route Box (Asal -> Tujuan) ─────────────────────────── */
        .trf-route-node {
            background: var(--route-box-bg) !important;
            border: 1px solid var(--route-box-border) !important;
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 0.78rem;
            line-height: 1.35;
            width: 48%;
            transition: all 0.2s ease;
        }
        .table-modern tbody tr:hover .trf-route-node {
            border-color: var(--accent-color) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        /* ─── DataTable Controls Modern Styling (Dark & Light) ────── */
        .dataTables_wrapper {
            padding: 1.25rem 1.25rem !important;
            width: 100% !important;
            box-sizing: border-box;
        }

        /* Length Select */
        .dataTables_length {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .dataTables_length select {
            padding: 6px 30px 6px 14px !important;
            border-radius: 50px !important;
            border: 1px solid var(--border-color) !important;
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            margin: 0 8px !important;
            font-size: 0.84rem !important;
            font-weight: 600 !important;
            outline: none !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dataTables_length select:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }

        /* Search Filter Input */
        .dataTables_filter {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .dataTables_filter label {
            width: 100%;
            max-width: 320px;
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 0;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
        }
        .dataTables_filter input {
            width: 100% !important;
            padding: 8px 16px 8px 38px !important;
            border-radius: 50px !important;
            font-size: 0.85rem !important;
            border: 1px solid var(--border-color) !important;
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            outline: none !important;
            transition: all 0.2s ease;
            margin-left: 8px !important;
        }
        .dataTables_filter input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }
        .dataTables_filter label::before {
            content: "\F52A";
            font-family: "bootstrap-icons";
            position: absolute;
            left: 54px;
            color: var(--text-muted);
            font-size: 0.9rem;
            pointer-events: none;
        }

        /* Info & Pagination */
        .dataTables_info {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-muted) !important;
            padding-top: 0.5rem !important;
        }

        .dataTables_paginate {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
            padding-top: 0.35rem !important;
        }
        .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid var(--border-color) !important;
            background: var(--card-bg) !important;
            color: var(--text-main) !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            padding: 5px 12px !important;
            margin: 0 2px !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
        }
        .dataTables_paginate .paginate_button:hover {
            background: var(--table-hover-bg) !important;
            color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
        }
        .dataTables_paginate .paginate_button.current,
        .dataTables_paginate .paginate_button.current:hover {
            background: var(--accent-color) !important;
            color: #ffffff !important;
            border-color: var(--accent-color) !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.35) !important;
        }
        .dataTables_paginate .paginate_button.disabled,
        .dataTables_paginate .paginate_button.disabled:hover {
            color: var(--text-muted) !important;
            background: transparent !important;
            border-color: var(--border-color) !important;
            opacity: 0.5;
            cursor: not-allowed !important;
        }

        /* ─── Item Rows in Modal ─────────────────────────────────── */
        .item-row { 
            background: var(--bg-light); 
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            padding: 12px 14px; 
            margin-bottom: 10px; 
        }
        .item-row:last-child { margin-bottom: 0; }
        .stok-indicator { font-size: .78rem; margin-top: 4px; }
        .stok-ok    { color: #16a34a; }
        .stok-warn  { color: #d97706; }
        .stok-empty { color: #dc2626; }

        /* ─── Select2 Override ───────────────────────────────────── */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px; font-size: .88rem; border: 1px solid var(--border-color);
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--text-main) !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--bg-light) !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }
        .select2-container--bootstrap-5 .select2-results__option {
            color: var(--text-main) !important;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--accent-color) !important;
            color: #ffffff !important;
        }

        /* ─── SweetAlert Z-Index Failsafe ────────────────────────── */
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- ─── Page Header ─────────────────────────────────────────── -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    Transfer Gudang (Warehouse Transfer)
                </h4>
                <small class="text-muted ms-md-5 ps-md-2 d-block">Proses mutasi pemindahan barang antar gudang</small>
            </div>
            @can('tambah-transfer')
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahTransfer" id="btnTambahTransfer">
                <i class="bi bi-plus-lg me-1"></i> Buat Transfer
            </button>
            @endcan
        </div>

        <!-- ─── Table Card ───────────────────────────────────────────── -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 py-3.5 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                        <i class="bi bi-list-ul fs-5"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0 fs-6">
                        Daftar Transfer Gudang
                    </h6>
                </div>
                <span class="badge rounded-pill trf-count-badge">
                    <i class="bi bi-file-earmark-text me-1"></i>{{ number_format($transfersCount, 0, ',', '.') }} Dokumen
                </span>
            </div>
            <div class="card-body p-0" id="transfer_card_body">
                <div class="trf-table-scroll-hint d-md-none">
                    <i class="bi bi-arrow-left-right"></i> Geser tabel ke samping untuk melihat rute & aksi
                </div>
                <div class="table-responsive table-responsive-trf">
                    <table class="table table-hover table-modern mb-0" id="tableTransfer">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th style="min-width: 170px;">Nomor Transfer</th>
                                <th style="min-width: 110px;">Tanggal</th>
                                <th style="min-width: 380px;">Rute Transfer (Asal ➔ Tujuan)</th>
                                <th style="min-width: 120px;" class="text-center">Status</th>
                                <th style="min-width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loaded dynamically via server-side AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modals --}}
    @include('modul_inventori.transfer_gudang._modal_input')
    @include('modul_inventori.transfer_gudang._modal_detail')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    const barangs = @json($barangsData);
    let dt = null;

    function reloadTableTransfer(showSpinner = true) {
        if (dt) {
            dt.ajax.reload(null, false);
        }
    }

    $(document).ready(function () {
        // DataTable initialization (Server-Side Yajra DataTables)
        if ($('#tableTransfer').length) {
            dt = $('#tableTransfer').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                scrollX: false,
                ajax: {
                    url: "{{ route('transfer.index') }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, class: 'text-muted small text-center' },
                    { 
                        data: 'nomor_transfer_fmt', 
                        name: 'nomor_transfer',
                        render: function(data, type, row) {
                            return `<span class="fw-bold text-dark font-monospace" style="font-size:.85rem">${data}</span>`;
                        }
                    },
                    { 
                        data: 'tanggal_fmt', 
                        name: 'tanggal_transfer',
                        render: function(data, type, row) {
                            return `<span style="font-size:.84rem">${data}</span>`;
                        }
                    },
                    { 
                        data: 'rute', 
                        name: 'rute',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            let typeBadge = '';
                            if (data.jenis_transfer === 'Inter-PT' || (data.asal_pt !== data.tujuan_pt && data.asal_pt !== '-' && data.tujuan_pt !== '-')) {
                                typeBadge = `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-0.5" style="font-size:0.68rem;" title="Transfer Antar PT (Inter-PT)"><i class="bi bi-arrow-left-right me-1"></i>Inter-PT</span>`;
                            } else {
                                typeBadge = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-0.5" style="font-size:0.68rem;"><i class="bi bi-building-check me-1"></i>Intra-PT</span>`;
                            }

                            let asalCabangText = (data.asal_cabang === 'Pusat (Head Office)' || data.asal_cabang === '-') ? 'Pusat' : data.asal_cabang;
                            let tujuanCabangText = (data.tujuan_cabang === 'Pusat (Head Office)' || data.tujuan_cabang === '-') ? 'Pusat' : data.tujuan_cabang;

                            return `
                            <div class="d-flex flex-column gap-1.5" style="min-width: 360px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="trf-route-node">
                                        <div class="fw-bold text-danger mb-0.5 d-flex align-items-center text-truncate">
                                            <i class="bi bi-box-arrow-up me-1"></i>${data.asal_gudang}
                                        </div>
                                        <div class="text-muted small text-truncate" style="font-size: 0.72rem;" title="${data.asal_pt} (${asalCabangText})">
                                            <i class="bi bi-building me-1 opacity-75"></i>${data.asal_pt} <span class="text-secondary fw-semibold">(${asalCabangText})</span>
                                        </div>
                                    </div>
                                    <div class="text-center px-1">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                            <i class="bi bi-arrow-right fw-bold fs-6"></i>
                                        </div>
                                    </div>
                                    <div class="trf-route-node">
                                        <div class="fw-bold text-success mb-0.5 d-flex align-items-center text-truncate">
                                            <i class="bi bi-box-arrow-in-down me-1"></i>${data.tujuan_gudang}
                                        </div>
                                        <div class="text-muted small text-truncate" style="font-size: 0.72rem;" title="${data.tujuan_pt} (${tujuanCabangText})">
                                            <i class="bi bi-building me-1 opacity-75"></i>${data.tujuan_pt} <span class="text-secondary fw-semibold">(${tujuanCabangText})</span>
                                        </div>
                                    </div>
                                </div>
                                <div>${typeBadge}</div>
                            </div>`;
                        }
                    },
                    { data: 'status_badge', name: 'status', class: 'text-center', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center' }
                ],
                dom: '<"row mb-2"<"col-sm-6"l><"col-sm-6"f>>rt<"row align-items-center mt-2"<"col-sm-5"i><"col-sm-7"p>>',
                order: [[2, 'desc']],
                pageLength: 10,
                language: {
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
                    infoFiltered: '(disaring dari _MAX_ total data)',
                    search: 'Cari:',
                    zeroRecords: 'Tidak ada data transfer yang cocok',
                    paginate: { previous: '←', next: '→' },
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('onclick', `showDetailTransfer(${data.id})`);
                    $(row).attr('id', `row-trf-${data.id}`);
                }
            });

            // Adjust table columns on window resize & sidebar toggle
            $(window).on('resize sidebar:toggle', function () {
                if (dt) {
                    dt.columns.adjust();
                }
            });
        }

        // Live real-time status poller every 3 seconds for transfer page (runs silently in the background)
        setInterval(function () {
            let isSearching = $('#tableTransfer_filter input').is(':focus');
            let isModalOpen = $('.modal.show').length > 0;
            if (!isSearching && !isModalOpen) {
                reloadTableTransfer(false); // Silent real-time reload
            }
        }, 3000);
    });
    </script>
    @endpush
</x-layouts>
