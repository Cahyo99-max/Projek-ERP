<x-layouts>
    <x-slot:title>Buku Jurnal Umum (General Journal)</x-slot:title>

    <div class="container-fluid py-3 px-3 px-md-4">
        
        <!-- Header Toolbar Modern Glass -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden ju-header-card position-relative">
            <div class="ju-card-shine"></div>
            <div class="card-body p-4 position-relative z-1 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-white text-opacity-75">Akuntansi &amp; Keuangan</a></li>
                            <li class="breadcrumb-item active text-white fw-bold" aria-current="page">Jurnal Umum</li>
                        </ol>
                    </nav>
                    <h3 class="fw-bold text-white mb-1 d-flex align-items-center gap-2.5">
                        <i class="bi bi-journal-bookmark-fill" style="color: #60a5fa;"></i>
                        <span>Buku Jurnal Umum</span>
                        <span class="badge rounded-pill font-monospace fw-normal" style="background: rgba(255,255,255,0.18); font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.25);">General Journal</span>
                    </h3>
                    <p class="text-white text-opacity-80 small mb-0 font-monospace">Pusat pembukuan double-entry akuntansi (Debit &amp; Kredit) seluruh aktivitas bisnis perusahaan secara real-time.</p>
                </div>
                
                <!-- Tombol Action Header -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @can('tambah-jurnal-umum')
                    <button type="button" class="btn btn-light text-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow d-flex align-items-center gap-2 ju-btn-create" data-bs-toggle="modal" data-bs-target="#modalCreateJurnal">
                        <i class="bi bi-plus-circle-fill text-primary"></i>
                        <span>+ Buat Jurnal Manual</span>
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Section 1: 4 Metrik Kartu Ringkasan Jurnal (Cyber Royal Blue Bento Grid) -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Transaksi Jurnal -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 ju-stat-card ju-stat-blue">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Total Entri Jurnal</div>
                            <h3 class="fw-bold font-monospace mb-0 text-primary" id="stat_total_jurnal">{{ number_format($totalJurnal, 0, ',', '.') }}</h3>
                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.75rem;">Transaksi Terposting</div>
                        </div>
                        <div class="ju-icon-badge bg-primary-subtle text-primary shadow-sm">
                            <i class="bi bi-journal-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Debit Terposting -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 ju-stat-card ju-stat-green">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Total Debit Terposting</div>
                            <h3 class="fw-bold font-monospace mb-0 text-success" id="stat_total_debit">Rp {{ number_format($totalDebit, 2, ',', '.') }}</h3>
                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.75rem;">Arus Debet Aktiva/Beban</div>
                        </div>
                        <div class="ju-icon-badge bg-success-subtle text-success shadow-sm">
                            <i class="bi bi-arrow-down-left-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Kredit Terposting -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 ju-stat-card ju-stat-indigo">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Total Kredit Terposting</div>
                            <h3 class="fw-bold font-monospace mb-0 text-info" id="stat_total_kredit">Rp {{ number_format($totalKredit, 2, ',', '.') }}</h3>
                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.75rem;">Arus Kredit Pasiva/Pendapatan</div>
                        </div>
                        <div class="ju-icon-badge bg-info-subtle text-info shadow-sm">
                            <i class="bi bi-arrow-up-right-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Status Keseimbangan (Balance Status) -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 ju-stat-card {{ $isBalanced ? 'ju-stat-balanced' : 'ju-stat-unbalanced' }}" id="card_stat_balance">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.75rem;">Status Keseimbangan</div>
                            <h4 class="fw-bold font-monospace mb-0 {{ $isBalanced ? 'text-success' : 'text-danger' }}" id="stat_balance_title">
                                <i class="bi {{ $isBalanced ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} me-1" id="stat_balance_icon"></i>
                                {{ $isBalanced ? '100% BALANCE' : 'ADA SELISIH' }}
                            </h4>
                            <div class="small text-muted mt-1 font-monospace" style="font-size: 0.75rem;" id="stat_balance_sub">Double-Entry Verification</div>
                        </div>
                        <div class="ju-icon-badge {{ $isBalanced ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} shadow-sm" id="stat_balance_badge">
                            <i class="bi {{ $isBalanced ? 'bi-shield-check' : 'bi-shield-exclamation' }} fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Active Filter Status Strip (Modern & Compact) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-body" id="active_filter_strip">
            <div class="card-body py-2.5 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1">
                        <i class="bi bi-funnel-fill me-1"></i> Filter Aktif
                    </span>
                    <span class="badge bg-body-tertiary border text-body-emphasis rounded-pill px-3 py-1 font-monospace" id="chip_active_period">
                        <i class="bi bi-calendar3 me-1.5 text-primary"></i> <span id="label_active_period">{{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</span>
                    </span>
                    <span class="badge bg-body-tertiary border text-body-emphasis rounded-pill px-3 py-1 font-monospace" id="chip_active_source">
                        <i class="bi bi-tags me-1.5 text-success"></i> <span id="label_active_source">Semua Sumber</span>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" id="btn_open_filter_modal" data-bs-toggle="modal" data-bs-target="#modalFilterJurnal" title="Buka Pengaturan Filter Periode, Modul & Cetak Laporan">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Filter</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-secondary text-decoration-none px-2 py-1 fw-semibold d-none" id="btn_quick_reset_filter" title="Reset ke default (Bulan ini)">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 3: Tabel Data Jurnal Umum Modern Blue -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden kb-card-section mb-4">
            <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="kb-icon-badge bg-primary-subtle text-primary rounded-3" style="width: 36px; height: 36px;">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-body-emphasis mb-0">Daftar Jurnal Umum Terposting</h6>
                        <div class="text-muted small">Pencatatan pembukuan akuntansi otomatis dan memorial</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 fw-bold">
                        <i class="bi bi-shield-lock-fill me-1"></i> Double-Entry Live
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0 w-100" id="tableJurnalUmum">
                        <thead>
                            <tr class="small text-uppercase fw-bold text-white ju-table-header">
                                <th style="width: 45px;" class="text-center">No</th>
                                <th>No. Jurnal</th>
                                <th>Tanggal</th>
                                <th>Sumber Modul</th>
                                <th>Keterangan / Transaksi</th>
                                <th class="text-end">Total Debit (Rp)</th>
                                <th class="text-end">Total Kredit (Rp)</th>
                                <th class="text-center">Keseimbangan</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals -->
    @can('tambah-jurnal-umum')
    @include('modul_accounting.jurnal_umum._modal_create')
    @endcan
    @include('modul_accounting.jurnal_umum._modal_detail')
    @include('modul_accounting.jurnal_umum._modal_filter')

    @push('styles')
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        /* Select2 Bootstrap-5 Customization for Modal */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem !important;
            min-height: 38px !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
            border-color: var(--bs-border-color) !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            z-index: 1065 !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
            border-color: var(--bs-border-color) !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            border-radius: 0.375rem !important;
            font-size: 0.85rem !important;
        }

        /* Header Hero Card - Modern Royal Blue Gradient */
        .ju-header-card {
            background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 50%, #2563eb 100%) !important;
            border: 1px solid rgba(59, 130, 246, 0.3) !important;
            box-shadow: 0 15px 35px -10px rgba(37, 99, 235, 0.4) !important;
        }

        .ju-card-shine {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(115deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0.05) 30%, transparent 60%);
            pointer-events: none;
        }

        .ju-btn-glass {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            backdrop-filter: blur(8px);
            transition: all 0.25s ease;
        }

        .ju-btn-glass:hover {
            background: rgba(255, 255, 255, 0.28) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .ju-btn-create {
            transition: all 0.25s ease;
        }

        .ju-btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.3) !important;
        }

        /* Modern Bento Stat Cards */
        .ju-stat-card {
            border: 1px solid rgba(0, 0, 0, 0.07) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(12px);
        }

        .ju-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.12) !important;
            border-color: rgba(37, 99, 235, 0.4) !important;
        }

        .ju-stat-blue {
            border-left: 4px solid #2563eb !important;
        }

        .ju-stat-green {
            border-left: 4px solid #16a34a !important;
        }

        .ju-stat-indigo {
            border-left: 4px solid #06b6d4 !important;
        }

        .ju-stat-balanced {
            border-left: 4px solid #10b981 !important;
        }

        .ju-stat-unbalanced {
            border-left: 4px solid #ef4444 !important;
        }

        .ju-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        .ju-stat-card:hover .ju-icon-badge {
            transform: scale(1.1) rotate(5deg);
        }

        /* Modern Vivid Electric Blue Table Header (Sesuai Gambar Referensi) */
        #tableJurnalUmum thead tr th {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 55%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 0.78rem !important;
            letter-spacing: 0.6px !important;
            text-transform: uppercase !important;
            padding: 12px 14px !important;
            border-top: none !important;
            border-bottom: 2px solid #1e3a8a !important;
            border-right: 1px solid rgba(255, 255, 255, 0.2) !important;
            vertical-align: middle !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }

        #tableJurnalUmum thead tr th:first-child {
            border-top-left-radius: 10px;
        }

        #tableJurnalUmum thead tr th:last-child {
            border-top-right-radius: 10px;
            border-right: none !important;
        }

        #tableJurnalUmum tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.05) !important;
        }

        .kb-card-section {
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
        }

        .kb-modal-container {
            background-color: #ffffff;
        }

        .kb-modal-header {
            background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%) !important;
            color: #ffffff !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .kb-modal-header .modal-title,
        .kb-modal-header p {
            color: #ffffff !important;
        }

        .kb-modal-header .btn-close {
            filter: invert(1);
        }

        .kb-modal-footer {
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
        }

        .kb-form-control {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            transition: all 0.2s ease;
        }

        .kb-form-control:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] .kb-card-section, 
        [data-bs-theme="dark"] .ju-stat-card, 
        [data-bs-theme="dark"] .kb-modal-container {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .kb-modal-footer {
            background: #0b1120 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Modal Filter Pro FinTech Design */
        .ju-modal-filter-content {
            border: 1px solid rgba(59, 130, 246, 0.25) !important;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.35) !important;
        }
        .ju-modal-filter-header {
            background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 50%, #2563eb 100%) !important;
        }
        .ju-filter-header-icon {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(6px);
        }
        .ju-filter-section-card {
            border: 1px solid var(--bs-border-color) !important;
            transition: all 0.2s ease;
        }
        .ju-filter-section-card:hover {
            border-color: rgba(37, 99, 235, 0.35) !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.06);
        }
        .btn-modal-preset {
            transition: all 0.2s ease;
            font-size: 0.78rem;
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        .btn-modal-preset:hover {
            border-color: #2563eb;
            color: #2563eb;
            background-color: rgba(37, 99, 235, 0.06);
            transform: translateY(-1px);
        }
        .btn-modal-preset.active {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            border-color: #1d4ed8 !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .ju-stepper-btn {
            transition: all 0.2s ease;
        }
        .ju-stepper-btn:hover {
            background-color: rgba(37, 99, 235, 0.1) !important;
            color: #2563eb;
            transform: scale(1.05);
        }
        .ju-btn-apply {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
            border: none !important;
            transition: all 0.2s ease;
        }
        .ju-btn-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4) !important;
        }
        [data-bs-theme="dark"] .ju-modal-filter-content {
            background-color: #0f172a !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        [data-bs-theme="dark"] .ju-filter-section-card {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
    </style>
    @endpush

    @push('scripts')
    <!-- Select2 Searchable Dropdown JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            
            // Inisialisasi Searchable Select2 pada Dropdown Sumber Transaksi di dalam Modal
            $('#modal_filter_referensi_tipe').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalFilterJurnal'),
                placeholder: '-- Semua Sumber Transaksi --',
                allowClear: true,
                width: '100%'
            });

            // State Filter Aktif
            let currentFilter = {
                start_date: $('#modal_filter_start_date').val() || '{{ $startDate }}',
                end_date:   $('#modal_filter_end_date').val() || '{{ $endDate }}',
                referensi_tipe: $('#modal_filter_referensi_tipe').val() || ''
            };

            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            function pad2(n) {
                return n < 10 ? '0' + n : n;
            }

            function formatDateYMD(d) {
                return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
            }

            function formatDateDMY(dStr) {
                if (!dStr) return '-';
                let p = dStr.split('-');
                if (p.length === 3) return p[2] + '/' + p[1] + '/' + p[0];
                return dStr;
            }

            function updateMonthStepperDisplay(d) {
                let mName = monthNames[d.getMonth()] + ' ' + d.getFullYear();
                let firstD = new Date(d.getFullYear(), d.getMonth(), 1);
                let lastD  = new Date(d.getFullYear(), d.getMonth() + 1, 0);
                
                $('#display_modal_stepper_month').text(mName);
                $('#display_modal_stepper_range').text(formatDateDMY(formatDateYMD(firstD)) + ' s/d ' + formatDateDMY(formatDateYMD(lastD)));
                $('#modal_month_input').val(d.getFullYear() + '-' + pad2(d.getMonth() + 1));
            }

            let stepperDate = new Date(currentFilter.start_date || new Date());
            if (isNaN(stepperDate.getTime())) {
                stepperDate = new Date();
            }
            updateMonthStepperDisplay(stepperDate);

            // Inisialisasi DataTables Jurnal Umum
            let tableJurnal = $('#tableJurnalUmum').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('accounting.jurnal-umum.data') }}",
                    data: function(d) {
                        d.start_date = currentFilter.start_date;
                        d.end_date   = currentFilter.end_date;
                        d.referensi_tipe = currentFilter.referensi_tipe;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small text-muted' },
                    { data: 'nomor_jurnal', name: 'nomor_jurnal' },
                    { data: 'tanggal_jurnal', name: 'tanggal_jurnal' },
                    { data: 'sumber_transaksi', name: 'referensi_tipe' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'total_debit', name: 'total_debit' },
                    { data: 'total_kredit', name: 'total_kredit' },
                    { data: 'status_balance', name: 'status_balance', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[2, 'desc'], [1, 'desc']],
                language: {
                    search: "Cari Jurnal:",
                    searchPlaceholder: "Nomor jurnal, keterangan...",
                    lengthMenu: "_MENU_ data per hal",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ jurnal",
                    paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                    emptyTable: "Belum ada riwayat jurnal akuntansi terposting.",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                }
            });

            // Update Dynamic Stat Cards saat DataTables reload via XHR
            tableJurnal.on('xhr.dt', function(e, settings, json) {
                if (json && json.stats) {
                    $('#stat_total_jurnal').text(json.stats.total_jurnal);
                    $('#stat_total_debit').text(json.stats.total_debit);
                    $('#stat_total_kredit').text(json.stats.total_kredit);

                    const $card = $('#card_stat_balance');
                    const $title = $('#stat_balance_title');
                    const $sub = $('#stat_balance_sub');
                    const $badge = $('#stat_balance_badge');

                    if (json.stats.is_balanced) {
                        $card.removeClass('ju-stat-unbalanced').addClass('ju-stat-balanced');
                        $title.removeClass('text-danger').addClass('text-success').html('<i class="bi bi-check-circle-fill me-1" id="stat_balance_icon"></i>' + json.stats.balance_text);
                        $sub.text(json.stats.balance_sub);
                        $badge.removeClass('bg-danger-subtle text-danger').addClass('bg-success-subtle text-success').html('<i class="bi bi-shield-check fs-4"></i>');
                    } else {
                        $card.removeClass('ju-stat-balanced').addClass('ju-stat-unbalanced');
                        $title.removeClass('text-success').addClass('text-danger').html('<i class="bi bi-exclamation-triangle-fill me-1" id="stat_balance_icon"></i>' + json.stats.balance_text);
                        $sub.text(json.stats.balance_sub);
                        $badge.removeClass('bg-success-subtle text-success').addClass('bg-danger-subtle text-danger').html('<i class="bi bi-shield-exclamation fs-4"></i>');
                    }
                }
            });

            // Handler Preset Tanggal Cepat pada Modal Filter
            $(document).on('click', '.btn-modal-preset', function() {
                $('.btn-modal-preset').removeClass('active');
                $(this).addClass('active');

                let preset = $(this).data('preset');
                let now = new Date();
                let startStr = '';
                let endStr = '';

                if (preset === 'today') {
                    startStr = formatDateYMD(now);
                    endStr   = formatDateYMD(now);
                    stepperDate = new Date(now);
                } else if (preset === 'last7') {
                    let past7 = new Date();
                    past7.setDate(past7.getDate() - 6);
                    startStr = formatDateYMD(past7);
                    endStr   = formatDateYMD(now);
                    stepperDate = new Date(now);
                } else if (preset === 'this_month') {
                    let firstD = new Date(now.getFullYear(), now.getMonth(), 1);
                    let lastD  = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    startStr = formatDateYMD(firstD);
                    endStr   = formatDateYMD(lastD);
                    stepperDate = new Date(now);
                } else if (preset === 'last_month') {
                    let firstD = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    let lastD  = new Date(now.getFullYear(), now.getMonth(), 0);
                    startStr = formatDateYMD(firstD);
                    endStr   = formatDateYMD(lastD);
                    stepperDate = new Date(firstD);
                } else if (preset === '30_days') {
                    let past30 = new Date();
                    past30.setDate(past30.getDate() - 30);
                    startStr = formatDateYMD(past30);
                    endStr   = formatDateYMD(now);
                    stepperDate = new Date(now);
                } else if (preset === 'this_year') {
                    let firstD = new Date(now.getFullYear(), 0, 1);
                    let lastD  = new Date(now.getFullYear(), 11, 31);
                    startStr = formatDateYMD(firstD);
                    endStr   = formatDateYMD(lastD);
                    stepperDate = new Date(now);
                } else if (preset === 'all') {
                    startStr = '2020-01-01';
                    endStr   = '2030-12-31';
                    stepperDate = new Date(now);
                }

                $('#modal_filter_start_date').val(startStr);
                $('#modal_filter_end_date').val(endStr);
                updateMonthStepperDisplay(stepperDate);
            });

            // Handler Interactive Month Stepper Navigator
            $('#btn_modal_month_prev').on('click', function() {
                stepperDate.setMonth(stepperDate.getMonth() - 1);
                let firstD = new Date(stepperDate.getFullYear(), stepperDate.getMonth(), 1);
                let lastD  = new Date(stepperDate.getFullYear(), stepperDate.getMonth() + 1, 0);

                $('#modal_filter_start_date').val(formatDateYMD(firstD));
                $('#modal_filter_end_date').val(formatDateYMD(lastD));
                updateMonthStepperDisplay(stepperDate);
                $('.btn-modal-preset').removeClass('active');
            });

            $('#btn_modal_month_next').on('click', function() {
                stepperDate.setMonth(stepperDate.getMonth() + 1);
                let firstD = new Date(stepperDate.getFullYear(), stepperDate.getMonth(), 1);
                let lastD  = new Date(stepperDate.getFullYear(), stepperDate.getMonth() + 1, 0);

                $('#modal_filter_start_date').val(formatDateYMD(firstD));
                $('#modal_filter_end_date').val(formatDateYMD(lastD));
                updateMonthStepperDisplay(stepperDate);
                $('.btn-modal-preset').removeClass('active');
            });

            $('#modal_month_input').on('change', function() {
                let val = $(this).val(); // 'YYYY-MM'
                if (val) {
                    let parts = val.split('-');
                    let y = parseInt(parts[0]);
                    let m = parseInt(parts[1]) - 1;
                    stepperDate = new Date(y, m, 1);
                    let firstD = new Date(y, m, 1);
                    let lastD  = new Date(y, m + 1, 0);

                    $('#modal_filter_start_date').val(formatDateYMD(firstD));
                    $('#modal_filter_end_date').val(formatDateYMD(lastD));
                    updateMonthStepperDisplay(stepperDate);
                    $('.btn-modal-preset').removeClass('active');
                }
            });

            // Terapkan Filter Button
            $('#btn_modal_apply_filter').on('click', function() {
                currentFilter.start_date = $('#modal_filter_start_date').val();
                currentFilter.end_date   = $('#modal_filter_end_date').val();
                currentFilter.referensi_tipe = $('#modal_filter_referensi_tipe').val();

                // Update Active Filter Strip
                let periodText = formatDateDMY(currentFilter.start_date) + ' s/d ' + formatDateDMY(currentFilter.end_date);
                $('#label_active_period').text(periodText);

                let sourceText = $('#modal_filter_referensi_tipe option:selected').text();
                if (!currentFilter.referensi_tipe) {
                    sourceText = 'Semua Sumber';
                } else {
                    sourceText = sourceText.replace(/^[^\w\s]+/, '').trim(); // clean leading emoji if any
                }
                $('#label_active_source').text(sourceText);

                // Indicator badges
                let now = new Date();
                let thisMonthStart = formatDateYMD(new Date(now.getFullYear(), now.getMonth(), 1));
                let thisMonthEnd   = formatDateYMD(new Date(now.getFullYear(), now.getMonth() + 1, 0));
                let isCustom = (currentFilter.start_date !== thisMonthStart || currentFilter.end_date !== thisMonthEnd || currentFilter.referensi_tipe !== '');

                if (isCustom) {
                    $('#badge_filter_indicator').removeClass('d-none');
                    $('#btn_quick_reset_filter').removeClass('d-none');
                } else {
                    $('#badge_filter_indicator').addClass('d-none');
                    $('#btn_quick_reset_filter').addClass('d-none');
                }

                tableJurnal.ajax.reload();
                $('#modalFilterJurnal').modal('hide');
            });

            // Reset Filter Handler
            function resetFilterToDefault() {
                $('.btn-modal-preset[data-preset="this_month"]').trigger('click');
                $('#modal_filter_referensi_tipe').val('').trigger('change');
                $('#btn_modal_apply_filter').trigger('click');
            }

            $('#btn_modal_reset_filter').on('click', function() {
                $('.btn-modal-preset[data-preset="this_month"]').trigger('click');
                $('#modal_filter_referensi_tipe').val('').trigger('change');
            });

            $('#btn_quick_reset_filter').on('click', function() {
                resetFilterToDefault();
            });

            // Cetak Laporan Periode Jurnal Umum (PDF Full Period) dari Strip Filter
            $('#btn_print_period').on('click', function() {
                let url = `{{ route('accounting.jurnal-umum.print-all') }}?start_date=${currentFilter.start_date}&end_date=${currentFilter.end_date}`;
                if (currentFilter.referensi_tipe) {
                    url += `&referensi_tipe=${encodeURIComponent(currentFilter.referensi_tipe)}`;
                }

                window.open(url, '_blank');
            });

            // Cetak Laporan Periode Jurnal Umum Langsung dari dalam Modal Filter
            $('#btn_modal_print_period').on('click', function() {
                let sDate = $('#modal_filter_start_date').val() || currentFilter.start_date;
                let eDate = $('#modal_filter_end_date').val() || currentFilter.end_date;
                let refTipe = $('#modal_filter_referensi_tipe').val() || '';

                let url = `{{ route('accounting.jurnal-umum.print-all') }}?start_date=${sDate}&end_date=${eDate}`;
                if (refTipe) {
                    url += `&referensi_tipe=${encodeURIComponent(refTipe)}`;
                }

                window.open(url, '_blank');
            });

            // Export Excel Laporan Periode Jurnal Umum dari Strip Filter
            $('#btn_export_excel').on('click', function() {
                let url = `{{ route('accounting.jurnal-umum.export-excel') }}?start_date=${currentFilter.start_date}&end_date=${currentFilter.end_date}`;
                if (currentFilter.referensi_tipe) {
                    url += `&referensi_tipe=${encodeURIComponent(currentFilter.referensi_tipe)}`;
                }

                window.location.href = url;
            });

            // Export Excel Laporan Periode Jurnal Umum Langsung dari dalam Modal Filter
            $('#btn_modal_export_excel').on('click', function() {
                let sDate = $('#modal_filter_start_date').val() || currentFilter.start_date;
                let eDate = $('#modal_filter_end_date').val() || currentFilter.end_date;
                let refTipe = $('#modal_filter_referensi_tipe').val() || '';

                let url = `{{ route('accounting.jurnal-umum.export-excel') }}?start_date=${sDate}&end_date=${eDate}`;
                if (refTipe) {
                    url += `&referensi_tipe=${encodeURIComponent(refTipe)}`;
                }

                window.location.href = url;
            });

            // Helper Currency Formatter
            function formatRupiah(val) {
                let numberString = val.replace(/[^,\d]/g, '').toString();
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            }

            function parseRupiah(val) {
                if (!val) return 0;
                let clean = val.replace(/\./g, '').replace(',', '.');
                return parseFloat(clean) || 0;
            }

            $(document).on('input keyup', '.input-currency', function() {
                let val = $(this).val();
                if (val) {
                    $(this).val(formatRupiah(val));
                }
                calculateBalance();
            });

            // Dynamic Rows Management
            let rowIndex = 2;
            $('#btn_add_jurnal_row').on('click', function() {
                let optionsCoa = '';
                @foreach($coaAccounts as $coa)
                    optionsCoa += `<option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }} ({{ $coa->account_type }})</option>`;
                @endforeach

                let newRow = `
                    <tr class="jurnal-row">
                        <td>
                            <select class="form-select form-select-sm kb-form-control select-coa" name="details[${rowIndex}][account_id]" required>
                                <option value="">-- Pilih Akun COA --</option>
                                ${optionsCoa}
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-debit" name="details[${rowIndex}][debit]" placeholder="0" value="">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm kb-form-control text-end font-monospace input-currency input-kredit" name="details[${rowIndex}][kredit]" placeholder="0" value="">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm kb-form-control" name="details[${rowIndex}][keterangan]" placeholder="Opsional...">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-remove-row shadow-none" style="width: 28px; height: 28px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#jurnal_rows_container').append(newRow);
                rowIndex++;
                updateRemoveButtons();
                calculateBalance();
            });

            $(document).on('click', '.btn-remove-row', function() {
                if ($('#jurnal_rows_container tr').length > 2) {
                    $(this).closest('tr').remove();
                    updateRemoveButtons();
                    calculateBalance();
                }
            });

            function updateRemoveButtons() {
                let rowsCount = $('#jurnal_rows_container tr').length;
                if (rowsCount <= 2) {
                    $('.btn-remove-row').addClass('disabled');
                } else {
                    $('.btn-remove-row').removeClass('disabled');
                }
            }

            // Calculation & Balance Checker
            function calculateBalance() {
                let totalDebit = 0;
                let totalKredit = 0;

                $('.jurnal-row').each(function() {
                    let debit = parseRupiah($(this).find('.input-debit').val());
                    let kredit = parseRupiah($(this).find('.input-kredit').val());

                    totalDebit += debit;
                    totalKredit += kredit;
                });

                $('#calc_total_debit').text('Rp ' + Number(totalDebit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#calc_total_kredit').text('Rp ' + Number(totalKredit).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                let badge = $('#badge_balance_status');
                let btnSave = $('#btn_save_jurnal');

                if (totalDebit === 0 && totalKredit === 0) {
                    badge.removeClass('bg-success-subtle text-success border-success-subtle bg-danger-subtle text-danger border-danger-subtle')
                        .addClass('bg-secondary-subtle text-secondary border')
                        .html('Masukkan Nominal');
                    btnSave.prop('disabled', true);
                } else if (Math.abs(totalDebit - totalKredit) < 0.01) {
                    badge.removeClass('bg-secondary-subtle text-secondary border bg-danger-subtle text-danger border-danger-subtle')
                        .addClass('bg-success-subtle text-success border border-success-subtle')
                        .html('<i class="bi bi-check-circle-fill me-1"></i> 100% BALANCE');
                    btnSave.prop('disabled', false);
                } else {
                    let selisih = Math.abs(totalDebit - totalKredit);
                    badge.removeClass('bg-secondary-subtle text-secondary border bg-success-subtle text-success border-success-subtle')
                        .addClass('bg-danger-subtle text-danger border border-danger-subtle')
                        .html('<i class="bi bi-exclamation-triangle-fill me-1"></i> Selisih: Rp ' + Number(selisih).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    btnSave.prop('disabled', true);
                }
            }

            // Submit Form Jurnal Manual
            $('#formCreateJurnal').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                let btn = $('#btn_save_jurnal');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('accounting.jurnal-umum.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Posting Jurnal');
                        if (res.success) {
                            $('#modalCreateJurnal').modal('hide');
                            $('#formCreateJurnal')[0].reset();
                            Swal.fire('Berhasil!', res.message, 'success');
                            tableJurnal.ajax.reload();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Posting Jurnal');
                        let msg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Validasi Gagal!', msg, 'warning');
                    }
                });
            });

            // View Detail Voucher Jurnal
            $(document).on('click', '.btn-view-jurnal', function() {
                let id = $(this).data('id');
                $.get(`/accounting/jurnal-umum/${id}`, function(res) {
                    if (res.success) {
                        let j = res.data;
                        $('#detail_nomor_jurnal').text(j.nomor_jurnal);
                        $('#detail_tanggal').text(j.tanggal_jurnal);
                        $('#detail_sumber').text(j.referensi_tipe || 'Manual');
                        if (j.no_tender) {
                            $('#detail_no_tender_box').html(`<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill"><i class="bi bi-briefcase me-1"></i>${j.no_tender}</span>`);
                        } else {
                            $('#detail_no_tender_box').html(`<span class="text-muted fst-italic">-</span>`);
                        }
                        $('#detail_creator').text(j.creator ? (j.creator.nama_lengkap || j.creator.username || j.creator.name) : 'System Accounting');
                        $('#detail_keterangan').text(j.keterangan || '-');
                        $('#btn_print_detail_jurnal').attr('href', `/accounting/jurnal-umum/${j.id}/print`);

                        let rows = '';
                        let no = 1;
                        j.details.forEach(d => {
                            let coaCode = d.account ? d.account.code : '-';
                            let coaName = d.account ? d.account.name : 'Akun Tidak Ditemukan';
                            let debitVal = parseFloat(d.debit) || 0;
                            let kreditVal = parseFloat(d.kredit) || 0;
                            let debit = debitVal > 0 ? ('+ Rp ' + Number(debitVal).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '-';
                            let kredit = kreditVal > 0 ? ('- Rp ' + Number(kreditVal).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '-';

                            rows += `
                                <tr>
                                    <td class="text-center font-monospace text-muted small">${no++}</td>
                                    <td>
                                        <div class="font-monospace fw-bold text-body-emphasis">${coaCode}</div>
                                        <div class="text-muted small">${coaName}</div>
                                    </td>
                                    <td class="text-end font-monospace ${debitVal > 0 ? 'text-success fw-bold' : 'text-muted'}">${debit}</td>
                                    <td class="text-end font-monospace ${kreditVal > 0 ? 'text-primary fw-bold' : 'text-muted'}">${kredit}</td>
                                    <td class="small text-muted">${d.keterangan || '-'}</td>
                                </tr>
                            `;
                        });

                        $('#detail_rows_container').html(rows);
                        $('#detail_total_debit').text('Rp ' + Number(parseFloat(j.total_debit) || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#detail_total_kredit').text('Rp ' + Number(parseFloat(j.total_kredit) || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                        $('#modalDetailJurnal').modal('show');
                    }
                });
            });

        });
    </script>
    @endpush

</x-layouts>
