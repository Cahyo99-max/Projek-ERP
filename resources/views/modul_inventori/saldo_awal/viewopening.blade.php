<x-layouts title="Saldo Awal Persediaan (Opening Stock)">
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

        [data-bs-theme="dark"],
        html[data-theme="dark"],
        .dark-mode {
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --bg-light: #0f172a;
            --accent-color: #60a5fa;

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

        /* ─── UI Base Styles ─────────────────────────────────────── */
        body {
            color: var(--text-main);
        }

        .card {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background-color: var(--card-bg) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        /* ─── KPI Stats Cards ────────────────────────────────────── */
        .stat-card {
            border-radius: 1rem;
            border: 1px solid var(--border-color) !important;
            background: var(--card-bg) !important;
            padding: 1.1rem 1.2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -4px rgba(0, 0, 0, 0.1);
        }
        .stat-card-total { border-top: 3px solid #3b82f6 !important; }
        .stat-card-draft { border-top: 3px solid #f59e0b !important; }
        .stat-card-pending { border-top: 3px solid #06b6d4 !important; }
        .stat-card-confirmed { border-top: 3px solid #10b981 !important; }

        .stat-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* ─── Status Badges ──────────────────────────────────────── */
        .badge-draft {
            background: var(--badge-draft-bg);
            color: var(--badge-draft-text);
            border: 1px solid var(--badge-draft-border);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            letter-spacing: 0.2px;
        }
        .badge-pending {
            background: var(--badge-pending-bg);
            color: var(--badge-pending-text);
            border: 1px solid var(--badge-pending-border);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            letter-spacing: 0.2px;
        }
        .badge-confirmed {
            background: var(--badge-confirmed-bg);
            color: var(--badge-confirmed-text);
            border: 1px solid var(--badge-confirmed-border);
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            letter-spacing: 0.2px;
        }

        /* ─── Modern Table & Horizontal Scroll ──────────────────── */
        .table-modern {
            color: var(--text-main) !important;
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }
        .table-modern thead th {
            background-color: var(--table-header-bg) !important;
            color: var(--text-muted) !important;
            border-bottom: 2px solid var(--border-color) !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table-modern tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color) !important;
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            font-size: 0.88rem;
        }
        .table-modern tbody tr {
            transition: all 0.15s ease-in-out;
        }
        .table-modern tbody tr:hover td {
            background-color: var(--table-hover-bg) !important;
            cursor: pointer;
        }

        .table-responsive-opn {
            width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.4) transparent;
        }
        .table-responsive-opn::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive-opn::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-responsive-opn::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 10px;
        }

        /* ─── Search & Controls Styling ──────────────────────────── */
        .search-box-wrapper {
            position: relative;
            min-width: 240px;
        }
        .search-box-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 0.9rem;
        }
        .search-box-wrapper input {
            padding-left: 38px !important;
            border-radius: 50px !important;
            border: 1px solid var(--border-color) !important;
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            font-size: 0.85rem;
            height: 38px;
            transition: all 0.2s ease;
        }
        .search-box-wrapper input:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }

        .length-select-wrapper select {
            border-radius: 50px !important;
            border: 1px solid var(--border-color) !important;
            background-color: var(--card-bg) !important;
            color: var(--text-main) !important;
            font-size: 0.85rem;
            padding: 6px 28px 6px 14px;
            height: 38px;
            cursor: pointer;
        }

        /* Hide default DataTables dom filter/length since we bind custom ones */
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length {
            display: none !important;
        }

        /* ─── Live Indicator Pulse ───────────────────────────────── */
        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: livePulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }
        @keyframes livePulse {
            to {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
        }

        /* ─── Mobile Responsiveness ──────────────────────────────── */
        @media (max-width: 768px) {
            .page-header-title {
                font-size: 1.15rem !important;
            }
            .stat-card {
                padding: 0.85rem 0.95rem;
            }
            .search-box-wrapper {
                width: 100% !important;
                min-width: 100% !important;
            }
        }
    </style>

    <div class="container-fluid py-3 px-2 px-md-4">

        <!-- ─── 1. Header & Actions (Clean & Compact) ────────────────── -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-4 shadow-sm" style="width: 48px; height: 48px; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0.5 page-header-title" style="color: var(--text-main);">
                        Saldo Awal Persediaan <span class="fw-normal text-muted fs-6 d-none d-sm-inline">(Opening Stock)</span>
                    </h4>
                    <small class="text-muted d-block">Inisialisasi stok fisik & nilai HPP saldo awal master persediaan gudang</small>
                </div>
            </div>
            
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Dropdown Tools Excel --}}
                <div class="dropdown">
                    <button class="btn btn-outline-info rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm dropdown-toggle d-inline-flex align-items-center gap-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Format Master Barang & Export Data">
                        <i class="bi bi-file-earmark-spreadsheet-fill text-info"></i> <span>Opsi Excel</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2" style="background: var(--card-bg); border: 1px solid var(--border-color) !important;">
                        @can('tambah-opening-stock')
                        <li>
                            <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5 small fw-semibold" href="{{ route('opening-stock.template') }}" download style="color: var(--text-main);">
                                <i class="bi bi-file-earmark-arrow-down-fill text-info fs-6"></i>
                                <div>
                                    <span class="d-block">Format Master Barang</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">Pre-populated 32 barang siap isi</small>
                                </div>
                            </a>
                        </li>
                        @endcan
                        @can('lihat-opening-stock')
                        <li>
                            <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5 small fw-semibold" href="{{ route('opening-stock.export') }}" download style="color: var(--text-main);">
                                <i class="bi bi-file-earmark-excel-fill text-primary fs-6"></i>
                                <div>
                                    <span class="d-block">Export Semua Saldo Awal</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">Unduh arsip dokumen (.xlsx)</small>
                                </div>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>

                @can('tambah-opening-stock')
                <button class="btn btn-outline-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#modalImportOpening">
                    <i class="bi bi-cloud-arrow-up-fill text-success"></i> <span>Import Excel</span>
                </button>
                <button class="btn btn-primary rounded-pill px-4 py-1.5 fw-bold shadow d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;" data-bs-toggle="modal" data-bs-target="#modalTambahOpening" id="btnTambahOpening">
                    <i class="bi bi-plus-lg fw-bold"></i> <span>Buat Saldo Awal</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- ─── 2. KPI Summary Cards ─────────────────────────────────── -->
        <div class="row g-2 g-md-3 mb-4">
            {{-- Total Dokumen --}}
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card-total">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Dokumen</span>
                            <h3 class="fw-black mb-0 mt-1" id="kpiTotalDoc" style="color: var(--text-main); letter-spacing: -0.02em;">{{ number_format($openingCount ?? 0) }}</h3>
                            <small class="text-primary fw-medium" style="font-size: 0.73rem;"><i class="bi bi-folder2-open me-1"></i>Arsip Dokumen</small>
                        </div>
                        <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-archive-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen Draft --}}
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card-draft">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Konsep Draf</span>
                            <h3 class="fw-black text-warning mb-0 mt-1" id="kpiDraftDoc" style="letter-spacing: -0.02em;">{{ number_format($draftCount ?? 0) }}</h3>
                            <small class="text-muted opacity-75" style="font-size: 0.73rem;"><i class="bi bi-pencil me-1"></i>Hanya Pembuat</small>
                        </div>
                        <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen Pending --}}
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card-pending">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Menunggu ACC</span>
                            <h3 class="fw-black text-info mb-0 mt-1" id="kpiPendingDoc" style="letter-spacing: -0.02em;">{{ number_format($pendingCount ?? 0) }}</h3>
                            <small class="text-info fw-medium" style="font-size: 0.73rem;"><i class="bi bi-hourglass-split me-1"></i>Perlu Persetujuan</small>
                        </div>
                        <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen Confirmed --}}
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card-confirmed">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Confirmed</span>
                            <h3 class="fw-black text-success mb-0 mt-1" id="kpiConfirmedDoc" style="letter-spacing: -0.02em;">{{ number_format($confirmedCount ?? 0) }}</h3>
                            <small class="text-success fw-medium" style="font-size: 0.73rem;"><i class="bi bi-check-all me-1"></i>Stok & Jurnal Aktif</small>
                        </div>
                        <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 3. Main Table Card ───────────────────────────────────── -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            {{-- Card Header --}}
            <div class="card-header py-3 px-3 px-md-4 border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                    <h6 class="fw-bold mb-0 d-inline-flex align-items-center gap-2" style="color: var(--text-main);">
                        <i class="bi bi-list-task text-primary fs-5"></i>
                        <span>Daftar Dokumen Saldo Awal</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-semibold small border border-success border-opacity-20 d-inline-flex align-items-center gap-1.5">
                            <span class="live-dot"></span> LIVE Realtime
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold border border-primary border-opacity-20" id="openingCountBadge">
                            {{ $openingCount ?? 0 }} Dokumen
                        </span>
                    </div>
                </div>

                {{-- Custom Filter & Search Controls --}}
                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                    <div class="length-select-wrapper d-none d-sm-block">
                        <select class="form-select form-select-sm" id="customPageLength" title="Jumlah baris per halaman">
                            <option value="10" selected>10 Data</option>
                            <option value="25">25 Data</option>
                            <option value="50">50 Data</option>
                            <option value="100">100 Data</option>
                        </select>
                    </div>
                    <div class="search-box-wrapper flex-grow-1 flex-md-grow-0">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control form-control-sm" id="customSearchInput" placeholder="Cari nomor dokumen / gudang..." autocomplete="off">
                    </div>
                </div>
            </div>

            {{-- Card Table Body --}}
            <div class="card-body p-0" id="opening_card_body">
                <div class="table-responsive-opn">
                    <table class="table table-hover table-modern mb-0 w-100" id="tableOpening">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="26%">Nomor Dokumen</th>
                                <th width="15%">Tanggal</th>
                                <th width="24%">Gudang Penyimpanan</th>
                                <th width="14%" class="text-center">Status</th>
                                <th width="16%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loaded dynamically via Server-Side AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Input & Detail -->
    @include('modul_inventori.saldo_awal._modal_input')
    @include('modul_inventori.saldo_awal._modal_detail')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    let dt = null;

    function reloadTableOpening() {
        if (dt) {
            dt.ajax.reload(null, false);
        }
    }

    $(document).ready(function () {
        // DataTable initialization (Server-Side Yajra DataTables)
        if ($('#tableOpening').length) {
            dt = $('#tableOpening').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                responsive: false,
                scrollX: true,
                ajax: {
                    url: "{{ route('opening-stock.index') }}",
                    type: 'GET'
                },
                columns: [
                    { 
                        data: 'DT_RowIndex', 
                        name: 'DT_RowIndex', 
                        orderable: false, 
                        searchable: false, 
                        class: 'text-muted small text-center fw-semibold' 
                    },
                    { 
                        data: 'nomor_opening_stock_fmt', 
                        name: 'nomor_opening_stock', 
                        class: 'fw-bold font-monospace' 
                    },
                    { 
                        data: 'tanggal_fmt', 
                        name: 'tanggal_opening_stock',
                        class: 'text-secondary font-monospace' 
                    },
                    { 
                        data: 'gudang_name', 
                        name: 'gudang.nama_gudang',
                        class: 'fw-semibold' 
                    },
                    { 
                        data: 'status_badge', 
                        name: 'status', 
                        class: 'text-center', 
                        orderable: false, 
                        searchable: false 
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        class: 'text-center' 
                    }
                ],
                order: [[2, 'desc']],
                pageLength: 10,
                language: {
                    emptyTable: '<div class="py-5 text-center text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>Belum ada data saldo awal persediaan</div>',
                    zeroRecords: '<div class="py-5 text-center text-muted"><i class="bi bi-search fs-1 d-block mb-2 text-secondary opacity-50"></i>Tidak ada dokumen saldo awal yang sesuai pencarian</div>',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 data',
                    paginate: { 
                        previous: '<i class="bi bi-chevron-left"></i>', 
                        next: '<i class="bi bi-chevron-right"></i>' 
                    },
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).attr('onclick', `showDetailOpening(${data.id})`);
                    $(row).attr('id', `row-opn-${data.id}`);
                }
            });

            // Bind Custom Search Box
            let searchTimeout = null;
            $('#customSearchInput').on('keyup', function () {
                const query = this.value;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    dt.search(query).draw();
                }, 300);
            });

            // Bind Custom Page Length
            $('#customPageLength').on('change', function () {
                dt.page.len(this.value).draw();
            });

            // Adjust columns dynamically on screen resize
            $(window).on('resize orientationchange', function () {
                if (dt) {
                    dt.columns.adjust();
                }
            });

            // Live Realtime Poller (Runs silently every 5 seconds without flickering)
            setInterval(function () {
                const isSearching = $('#customSearchInput').is(':focus');
                const isModalOpen = $('.modal.show').length > 0;
                const isTabActive = !document.hidden;

                if (dt && isTabActive && !isSearching && !isModalOpen) {
                    dt.ajax.reload(null, false);
                }
            }, 5000);
        }
    });

    // ── Submit Draft to Pending ───────────────────────────────────────────
    function submitOpeningDraft(id, nomor) {
        if (event && event.stopPropagation) {
            event.stopPropagation();
        }

        Swal.fire({
            title: 'Ajukan Saldo Awal?',
            html: `Dokumen <strong>${nomor}</strong> akan diajukan ke atasan untuk verifikasi & persetujuan.<br><small class="text-muted">Notifikasi WhatsApp akan otomatis dikirimkan ke atasan / supervisor.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Ajukan Sekarang!'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/opening-stock/${id}/submit`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({ icon:'success', title:'Berhasil Diajukan!', text:res.message, timer:2000, showConfirmButton:false })
                        .then(() => {
                            reloadTableOpening();
                        });
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat mengajukan dokumen.', 'error');
                }
            });
        });
    }
    </script>
    @endpush
</x-layouts>
