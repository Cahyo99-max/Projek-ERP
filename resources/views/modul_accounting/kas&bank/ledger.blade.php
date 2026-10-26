<x-layouts>
    <x-slot:title>Buku Kas &amp; Rekening Koran</x-slot:title>

    <div class="container-fluid px-2 px-md-4 py-3 py-md-4">

    <!-- Top Header & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
          
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill text-success"></i> Buku Kas &amp; Rekening Koran
                </h3>
                @if($selectedAccount)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1 fw-bold fs-6">
                        {{ $selectedAccount->nama_akun }}
                    </span>
                @else
                    <span class="badge bg-body-secondary text-secondary border rounded-pill font-monospace px-3 py-1 fw-semibold">
                        Semua Rekening (Konsolidasi)
                    </span>
                @endif
            </div>
        </div>

    </div>

    <!-- Quick Summary Stat Cards (4 Symmetrical Bento Cards: Saldo Awal, Masuk, Keluar, Saldo Akhir) -->
    <div class="row g-3 mb-4">
        
        @php
            $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
        @endphp

        <!-- 1. Saldo Awal Periode (Ocean Blue) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-blue position-relative overflow-hidden text-white p-3.5 d-flex flex-column justify-content-between shadow-sm">
                <div class="kb-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-start mb-2 z-1">
                    <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.6px;">SALDO AWAL PERIODE</span>
                    <div class="kb-cine-icon" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history fs-6"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <h3 class="fw-bold text-white font-monospace mb-0" id="stat_saldo_awal" style="font-size: 1.35rem;">
                        Rp {{ $fmtRupiah($saldoAwalPeriode ?? ($summary['saldo_awal'] ?? 0)) }}
                    </h3>
                    <div class="text-white text-opacity-80 small mt-1 font-monospace" id="stat_label_saldo_awal" style="font-size: 0.74rem;">
                        <i class="bi bi-calendar2-range me-1"></i>{{ $summary['label_saldo_awal'] ?? ('Per ' . date('d/m/Y', strtotime($startDate))) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Masuk (Emerald Green) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-green position-relative overflow-hidden text-white p-3.5 d-flex flex-column justify-content-between shadow-sm">
                <div class="kb-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-start mb-2 z-1">
                    <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.6px;">TOTAL PENERIMAAN</span>
                    <div class="kb-cine-icon" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-down-left fs-6"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <h3 class="fw-bold text-white font-monospace mb-0" id="stat_total_masuk" style="font-size: 1.35rem;">
                        + Rp {{ $fmtRupiah($totalMasuk) }}
                    </h3>
                    <div class="text-white text-opacity-80 small mt-1 font-monospace" id="stat_label_total_masuk" style="font-size: 0.74rem;">
                        <i class="bi bi-arrow-down-circle me-1"></i>Penerimaan periode ini
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Keluar (Warm Amber) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-amber position-relative overflow-hidden text-white p-3.5 d-flex flex-column justify-content-between shadow-sm">
                <div class="kb-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-start mb-2 z-1">
                    <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.6px;">TOTAL PENGELUARAN</span>
                    <div class="kb-cine-icon" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-arrow-up-right fs-6"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <h3 class="fw-bold text-white font-monospace mb-0" id="stat_total_keluar" style="font-size: 1.35rem;">
                        - Rp {{ $fmtRupiah($totalKeluar) }}
                    </h3>
                    <div class="text-white text-opacity-80 small mt-1 font-monospace" id="stat_label_total_keluar" style="font-size: 0.74rem;">
                        <i class="bi bi-arrow-up-circle me-1"></i>Pengeluaran &amp; transfer keluar
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Saldo Akhir Periode (Royal Violet) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-purple position-relative overflow-hidden text-white p-3.5 d-flex flex-column justify-content-between shadow-sm">
                <div class="kb-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-start mb-2 z-1">
                    <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.6px;">SALDO AKHIR PERIODE</span>
                    <div class="kb-cine-icon" style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-wallet2 fs-6"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <h3 class="fw-bold font-monospace mb-0 text-white" id="stat_saldo_akhir" style="font-size: 1.35rem;">
                        Rp {{ $fmtRupiah($saldoAkhirPeriode ?? ($summary['saldo_akhir'] ?? 0)) }}
                    </h3>
                    <div class="text-white text-opacity-80 small mt-1 font-monospace" id="stat_label_saldo_akhir" style="font-size: 0.74rem;">
                        <i class="bi bi-check-circle me-1"></i>{{ $summary['label_saldo_akhir'] ?? ('Per ' . date('d/m/Y', strtotime($endDate))) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Hidden Filter State Inputs for DataTables & Query Sync -->
    <input type="hidden" id="ledger_start_date" value="{{ $startDate }}">
    <input type="hidden" id="ledger_end_date" value="{{ $endDate }}">
    <input type="hidden" id="ledger_filter_tipe" value="">

    <!-- Main Table Card: Buku Kas & Rekening Koran -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden kb-card-section mb-4">
        <div class="card-header bg-transparent border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2.5">
            <div class="d-flex align-items-center gap-2.5">
                <div class="kb-icon-badge bg-success-subtle text-success rounded-3" style="width: 36px; height: 36px;">
                    <i class="bi bi-journal-text fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h6 class="fw-bold text-body-emphasis mb-0">
                            Buku Rekening Koran &amp; Mutasi Kas
                        </h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-0.5 fw-bold small d-inline-flex align-items-center gap-1">
                            <span class="kb-pulse-dot"></span> Live Data
                        </span>
                    </div>
                    @if($selectedAccount)
                        <div class="text-muted small mt-0.5 font-monospace" style="font-size: 0.78rem;">
                            {{ $selectedAccount->nama_bank ?: 'Kas Tunai' }}: <strong class="text-body">{{ $selectedAccount->nomor_rekening ?: '-' }}</strong> 
                            <span class="mx-1">•</span> a.n {{ $selectedAccount->atas_nama ?: $selectedAccount->nama_akun }}
                            <span class="mx-1">•</span> COA: {{ $selectedAccount->coa?->code }}
                        </div>
                    @else
                        <div class="text-muted small mt-0.5" style="font-size: 0.78rem;">
                            Konsolidasi seluruh buku kas tunai dan rekening bank operasional
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Table Header Toolbar: Dropdown Rekening + Active Filter Chips + Filter Modal Trigger -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                
                <!-- Dropdown Pilih Buku Kas / Rekening Bank (Tepat di Sebelah Tombol Filter) -->
                <div class="d-flex align-items-center" style="min-width: 250px; max-width: 360px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-success rounded-start-pill ps-3 pe-2">
                            <i class="bi bi-bank2"></i>
                        </span>
                        <select class="form-select kb-form-control form-select-sm rounded-end-pill fw-semibold shadow-none border-start-0 pe-4" id="select_account_switcher" title="Pilih Akun Kas / Rekening Bank">
                            <option value="" {{ !$selectedAccount ? 'selected' : '' }}>-- Konsolidasi (Semua Rekening) --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $selectedAccount && $selectedAccount->id == $acc->id ? 'selected' : '' }}>
                                    [{{ $acc->tipe }}] {{ $acc->nama_akun }} (Rp {{ $fmtRupiah($acc->saldo_berjalan) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Modern Filter Button with Icon & Modal Trigger -->
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFilterLedger" id="btn_open_filter_modal" title="Buka Pengaturan Filter">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                    <span class="badge bg-success text-white rounded-pill font-monospace small px-1.5 py-0.5 d-none" id="filter_badge_count" style="font-size: 0.68rem;">0</span>
                </button>

                <!-- Tombol Cetak / Export PDF Langsung di Toolbar Tabel -->
                @if(auth()->user()->can('cetak-rekening-koran') || auth()->user()->can('lihat-mutasi-kas-bank') || auth()->user()->hasRole('Super Admin'))
                <a href="{{ $selectedAccount ? route('accounting.kas-bank.ledger.account.print', $selectedAccount->id) . '?' . http_build_query(request()->all()) : route('accounting.kas-bank.ledger.print') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn_quick_print_ledger" title="Cetak Rekening Koran Sesuai Filter / Export PDF">
                    <i class="bi bi-printer"></i>
                    <span>Cetak PDF</span>
                </a>
                @endif

                <!-- Active Filter Indicator Pills -->
                <div class="d-flex align-items-center gap-1 flex-wrap" id="active_filter_chips_wrap">
                    <span class="badge bg-body-secondary text-secondary-emphasis border rounded-pill px-2.5 py-1.5 font-monospace small d-inline-flex align-items-center gap-1 shadow-none" id="chip_active_date" title="Periode Tanggal Aktif">
                        <i class="bi bi-calendar3 text-success"></i>
                        <span id="label_active_date">{{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</span>
                    </span>

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5 font-monospace small d-none align-items-center gap-1 shadow-none" id="chip_active_tipe">
                        <i class="bi bi-funnel"></i>
                        <span id="label_active_tipe"></span>
                    </span>

                    <!-- Quick Reset Button -->
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle d-none align-items-center justify-content-center shadow-none p-0" id="btn_quick_reset_filter" style="width: 26px; height: 26px;" title="Reset Filter ke Default">
                        <i class="bi bi-x fs-6"></i>
                    </button>
                </div>

            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle w-100" id="tableLedgerKas">
                <thead>
                    <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);">
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>No. Transaksi &amp; Kategori</th>
                        <th>Akun Kas / Bank &amp; Entitas</th>
                        <th>Tanggal</th>
                        <th>Pihak / Referensi / COA</th>
                        <th class="text-end">Jumlah (Rp)</th>
                        <th class="text-end">Saldo Akhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loaded via AJAX DataTables -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modals Include -->
@include('modul_accounting.kas&bank._modal_filter_ledger')
@include('modul_accounting.kas&bank._modal_cash_in')
@include('modul_accounting.kas&bank._modal_cash_out')
@include('modul_accounting.kas&bank._modal_transfer')
@include('modul_accounting.kas&bank._modal_detail')
@include('modul_accounting.kas&bank._modal_cancel')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/kas_bank.css') }}?v={{ time() }}" />
<style>
    .btn-date-preset {
        font-size: 0.76rem !important;
        transition: all 0.2s ease;
    }
    .btn-date-preset.active {
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3) !important;
    }
    .kb-cinematic-card {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .kb-cinematic-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
    }
    #active_filter_chips_wrap .badge {
        font-size: 0.74rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 COA & Kategori Dropdown (Searchable & Scrollable)
        function initSelect2Dropdowns(modalEl) {
            $(modalEl).find('.select2-coa').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $(modalEl),
                        width: '100%',
                        placeholder: '-- Otomatis Pilih COA --',
                        allowClear: true
                    });
                }
            });

            $(modalEl).find('.select2-kategori').each(function() {
                let placeholder = $(this).attr('id') === 'cash_out_kategori' ? '-- Pilih Kategori Pengeluaran --' : '-- Pilih Kategori Penerimaan --';
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $(modalEl),
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: true
                    });
                }
            });

            $(modalEl).find('.select2-tender').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $(modalEl),
                        width: '100%',
                        placeholder: '-- Tidak Terhubung ke Tender / Umum --',
                        allowClear: true
                    });
                }
            });
        }

        $('#modalCashIn, #modalCashOut').on('shown.bs.modal', function() {
            initSelect2Dropdowns(this);
        });

        let currentAccountId = "{{ $selectedAccount ? $selectedAccount->id : '' }}";

        // Inisialisasi DataTable Ledger (Default Tampil 10 Data)
        let tableLedger = $('#tableLedgerKas').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            processing: true,
            serverSide: true,
            dom: "<'row px-3 py-2.5 align-items-center'<'col-sm-6'l><'col-sm-6'f>><'table-responsive'tr><'d-flex justify-content-between align-items-center px-3 px-md-4 py-2.5 py-md-3 border-top bg-body-tertiary w-100 flex-wrap gap-2'<'text-muted small font-monospace'i><'ms-auto d-flex justify-content-end'p>>",
            ajax: {
                url: "{{ route('accounting.kas-bank.data') }}",
                data: function (d) {
                    d.kas_bank_id = currentAccountId;
                    d.tipe = $('#ledger_filter_tipe').val();
                    d.start_date = $('#ledger_start_date').val();
                    d.end_date = $('#ledger_end_date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small' },
                { data: 'nomor_transaksi', name: 'nomor_transaksi' },
                { data: 'kas_bank_nama', name: 'kasBank.nama_akun' },
                { data: 'tanggal', name: 'tanggal' },
                { data: 'pihak_ketiga', name: 'nama_kontak' },
                { data: 'jumlah', name: 'jumlah', className: 'text-end' },
                { data: 'saldo_sesudah', name: 'saldo_sesudah', className: 'text-end' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[3, 'desc']],
            language: {
                search: "Cari Transaksi:",
                searchPlaceholder: "Nomor mutasi, kontak, atau referensi...",
                lengthMenu: "_MENU_ data per hal",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ transaksi",
                paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                emptyTable: "Belum ada riwayat mutasi kas & bank.",
                processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            drawCallback: function(settings) {
                // Update 4 Kartu Bento Saldo Periode secara Realtime mengikuti Filter
                if (settings.json && (settings.json.period_summary || settings.json.summary)) {
                    let s = settings.json.period_summary || settings.json.summary;
                    if (s) {
                        $('#stat_saldo_awal').text(s.formatted_saldo_awal);
                        $('#stat_label_saldo_awal').html('<i class="bi bi-calendar2-range me-1"></i>' + s.label_saldo_awal);

                        $('#stat_total_masuk').text(s.formatted_total_masuk);
                        $('#stat_total_keluar').text(s.formatted_total_keluar);

                        $('#stat_saldo_akhir').text(s.formatted_saldo_akhir);
                        $('#stat_label_saldo_akhir').html('<i class="bi bi-check-circle me-1"></i>' + s.label_saldo_akhir);
                    }
                }
            }
        });

        // Switcher Rekening
        $('#select_account_switcher').on('change', function() {
            let accId = $(this).val();
            let start = $('#ledger_start_date').val();
            let end = $('#ledger_end_date').val();
            let tipe = $('#ledger_filter_tipe').val();
            let query = `?start_date=${start}&end_date=${end}` + (tipe ? `&tipe=${tipe}` : '');
            if (accId) {
                window.location.href = `/accounting/kas-bank/ledger/${accId}${query}`;
            } else {
                window.location.href = `/accounting/kas-bank/ledger${query}`;
            }
        });

        // ─── LOGIKA FILTER MODAL, PRESETS & ACTIVE CHIPS ───
        const monthNamesId = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        function getLocalDateString(d) {
            let year = d.getFullYear();
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateId(dateStr) {
            if (!dateStr) return '';
            let parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return dateStr;
        }

        function getFriendlyPeriodLabel(startDate, endDate) {
            if (!startDate && !endDate) return 'Semua Waktu';
            if (startDate && endDate) {
                let sParts = startDate.split('-');
                let eParts = endDate.split('-');
                if (sParts[0] === eParts[0] && sParts[1] === eParts[1]) {
                    let year = parseInt(sParts[0], 10);
                    let mIdx = parseInt(sParts[1], 10) - 1;
                    let lastDay = new Date(year, mIdx + 1, 0).getDate();
                    if (parseInt(sParts[2], 10) === 1 && parseInt(eParts[2], 10) === lastDay) {
                        return `${monthNamesId[mIdx]} ${year}`;
                    }
                }
                return `${formatDateId(startDate)} - ${formatDateId(endDate)}`;
            }
            if (startDate) return `Dari ${formatDateId(startDate)}`;
            if (endDate) return `s/d ${formatDateId(endDate)}`;
            return 'Semua Waktu';
        }

        function updateFilterUI() {
            let startDate = $('#ledger_start_date').val();
            let endDate = $('#ledger_end_date').val();
            let tipe = $('#ledger_filter_tipe').val();
            let defaultStart = "{{ now()->startOfMonth()->toDateString() }}";
            let defaultEnd = "{{ now()->toDateString() }}";

            // Date Label Chip
            $('#label_active_date').text(getFriendlyPeriodLabel(startDate, endDate));
            $('#chip_active_date').removeClass('d-none');

            // Tipe Label Chip
            let countActive = 0;
            if (tipe) {
                let labelTipe = tipe === 'Masuk' ? 'Kas Masuk' : (tipe === 'Keluar' ? 'Kas Keluar' : 'Transfer Antar Rekening');
                let iconClass = tipe === 'Masuk' ? 'bi-arrow-down-left text-success' : (tipe === 'Keluar' ? 'bi-arrow-up-right text-danger' : 'bi-arrow-left-right text-primary');
                $('#chip_active_tipe').html(`<i class="bi ${iconClass}"></i> <span>${labelTipe}</span>`).removeClass('d-none').addClass('d-inline-flex');
                countActive++;
            } else {
                $('#chip_active_tipe').addClass('d-none').removeClass('d-inline-flex');
            }

            let isDateCustom = (startDate !== defaultStart || endDate !== defaultEnd);
            if (isDateCustom) {
                countActive++;
            }

            if (countActive > 0) {
                $('#filter_badge_count').text(countActive).removeClass('d-none');
                $('#btn_quick_reset_filter').removeClass('d-none').addClass('d-inline-flex');
            } else {
                $('#filter_badge_count').addClass('d-none');
                $('#btn_quick_reset_filter').addClass('d-none').removeClass('d-inline-flex');
            }

            // Sinkronkan Link Cetak Rekening Koran Secara Real-time (Semua Tombol Cetak)
            $('#btn_print_ledger, #btn_quick_print_ledger').each(function() {
                let baseUrl = $(this).attr('href').split('?')[0];
                let params = new URLSearchParams();
                if (startDate) params.set('start_date', startDate);
                if (endDate) params.set('end_date', endDate);
                if (tipe) params.set('tipe', tipe);
                $(this).attr('href', baseUrl + (params.toString() ? '?' + params.toString() : ''));
            });
        }

        // Jalankan sinkronisasi awal UI filter saat halaman selesai dimuat
        updateFilterUI();

        // ─── LOGIKA FILTER MODAL, SMART MONTH STEPPER & LIVE PREVIEW ───
        function updateModalPreviewAndStepper() {
            let start = $('#modal_filter_start_date').val();
            let end = $('#modal_filter_end_date').val();
            let tipe = $('input[name="modal_filter_tipe_radio"]:checked').val() || '';

            // Update Month Stepper Display
            if (start) {
                let sParts = start.split('-');
                if (sParts.length === 3) {
                    let year = parseInt(sParts[0], 10);
                    let mIdx = parseInt(sParts[1], 10) - 1;
                    $('#display_stepper_month').text(`${monthNamesId[mIdx]} ${year}`);
                    $('#modal_filter_month_input').val(`${year}-${String(mIdx + 1).padStart(2, '0')}`);
                }
            } else {
                $('#display_stepper_month').text('Semua Waktu');
            }

            if (start && end) {
                $('#display_stepper_range').text(`${formatDateId(start)} s/d ${formatDateId(end)}`);
            } else if (start) {
                $('#display_stepper_range').text(`Mulai ${formatDateId(start)}`);
            } else if (end) {
                $('#display_stepper_range').text(`s/d ${formatDateId(end)}`);
            } else {
                $('#display_stepper_range').text('Seluruh riwayat mutasi');
            }

            // Update Live Preview Bar
            let previewText = getFriendlyPeriodLabel(start, end);
            if (start && end && !previewText.includes('(')) {
                previewText += ` (${formatDateId(start)} - ${formatDateId(end)})`;
            }
            $('#modal_live_preview_period').text(previewText);

            let tipeText = 'Semua Transaksi';
            if (tipe === 'Masuk') tipeText = 'Kas Masuk Saja';
            else if (tipe === 'Keluar') tipeText = 'Kas Keluar Saja';
            else if (tipe === 'Transfer') tipeText = 'Transfer Antar Rekening';
            $('#modal_live_preview_tipe').text(tipeText);
        }

        function shiftStepperMonth(step) {
            let currentMonthVal = $('#modal_filter_month_input').val() || "{{ date('Y-m') }}";
            let parts = currentMonthVal.split('-');
            let year = parseInt(parts[0], 10);
            let month = parseInt(parts[1], 10);

            let d = new Date(year, month - 1 + step, 1);
            let newYear = d.getFullYear();
            let newMonth = d.getMonth() + 1;
            let lastDay = new Date(newYear, newMonth, 0).getDate();

            let start = `${newYear}-${String(newMonth).padStart(2, '0')}-01`;
            let end = `${newYear}-${String(newMonth).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

            $('#modal_filter_start_date').val(start);
            $('#modal_filter_end_date').val(end);
            $('#modal_filter_month_input').val(`${newYear}-${String(newMonth).padStart(2, '0')}`);

            // Update state pill preset jika pas bulan ini
            let now = new Date();
            let isThisMonth = (newYear === now.getFullYear() && newMonth === (now.getMonth() + 1));
            $('.btn-date-preset').removeClass('active');
            if (isThisMonth) {
                $('.btn-date-preset[data-preset="thisMonth"]').addClass('active');
            }

            updateModalPreviewAndStepper();
        }

        $('#btn_month_prev').on('click', function() {
            shiftStepperMonth(-1);
        });

        $('#btn_month_next').on('click', function() {
            shiftStepperMonth(1);
        });

        $('#btn_stepper_pick_trigger').on('click', function() {
            let input = document.getElementById('modal_filter_month_input');
            if (input) {
                if (typeof input.showPicker === 'function') {
                    input.showPicker();
                } else {
                    $(input).focus().click();
                }
            }
        });

        $('#modal_filter_month_input').on('change', function() {
            let val = $(this).val();
            if (val) {
                let parts = val.split('-');
                let year = parseInt(parts[0], 10);
                let month = parseInt(parts[1], 10);
                let lastDay = new Date(year, month, 0).getDate();
                let start = `${year}-${String(month).padStart(2, '0')}-01`;
                let end = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;

                $('#modal_filter_start_date').val(start);
                $('#modal_filter_end_date').val(end);

                let now = new Date();
                let isThisMonth = (year === now.getFullYear() && month === (now.getMonth() + 1));
                $('.btn-date-preset').removeClass('active');
                if (isThisMonth) {
                    $('.btn-date-preset[data-preset="thisMonth"]').addClass('active');
                }

                updateModalPreviewAndStepper();
            }
        });

        // Preset Cepat Periode Tanggal di Dalam Modal
        $('.btn-date-preset').on('click', function() {
            $('.btn-date-preset').removeClass('active');
            $(this).addClass('active');

            let preset = $(this).data('preset');
            let now = new Date();
            let start = '', end = '';

            if (preset === 'today') {
                start = getLocalDateString(now);
                end = start;
            } else if (preset === 'last7') {
                let d7 = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6);
                start = getLocalDateString(d7);
                end = getLocalDateString(now);
            } else if (preset === 'thisMonth') {
                let firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                start = getLocalDateString(firstDay);
                end = getLocalDateString(now);
            } else if (preset === 'lastMonth') {
                let firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                let lastDayLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                start = getLocalDateString(firstDayLastMonth);
                end = getLocalDateString(lastDayLastMonth);
            } else if (preset === 'thisYear') {
                start = `${now.getFullYear()}-01-01`;
                end = getLocalDateString(now);
            } else if (preset === 'all') {
                start = '';
                end = '';
            }

            $('#modal_filter_start_date').val(start);
            $('#modal_filter_end_date').val(end);
            updateModalPreviewAndStepper();
        });

        // Jika user ubah manual input tanggal di modal
        $('#modal_filter_start_date, #modal_filter_end_date').on('change', function() {
            $('.btn-date-preset').removeClass('active');
            updateModalPreviewAndStepper();
        });

        // Ubah jenis transaksi radio
        $('input[name="modal_filter_tipe_radio"]').on('change', function() {
            updateModalPreviewAndStepper();
        });

        // Buka Modal Filter: sinkronkan input di modal dengan filter yang sedang aktif
        $('#modalFilterLedger').on('show.bs.modal', function() {
            let activeStart = $('#ledger_start_date').val();
            let activeEnd = $('#ledger_end_date').val();
            let activeTipe = $('#ledger_filter_tipe').val() || '';

            $('#modal_filter_start_date').val(activeStart);
            $('#modal_filter_end_date').val(activeEnd);

            if (activeTipe === 'Masuk') {
                $('#modal_tipe_in').prop('checked', true);
            } else if (activeTipe === 'Keluar') {
                $('#modal_tipe_out').prop('checked', true);
            } else if (activeTipe === 'Transfer') {
                $('#modal_tipe_transfer').prop('checked', true);
            } else {
                $('#modal_tipe_all').prop('checked', true);
            }

            updateModalPreviewAndStepper();
        });

        // Terapkan Filter dari Modal
        $('#btn_modal_apply_filter').on('click', function() {
            let start = $('#modal_filter_start_date').val();
            let end = $('#modal_filter_end_date').val();
            let tipe = $('input[name="modal_filter_tipe_radio"]:checked').val() || '';

            $('#ledger_start_date').val(start);
            $('#ledger_end_date').val(end);
            $('#ledger_filter_tipe').val(tipe);

            updateFilterUI();
            tableLedger.ajax.reload();
            $('#modalFilterLedger').modal('hide');
        });

        // Reset Filter ke Default (Bulan Ini, Semua Jenis)
        function resetLedgerFilters() {
            let defaultStart = "{{ now()->startOfMonth()->toDateString() }}";
            let defaultEnd = "{{ now()->toDateString() }}";

            $('#ledger_start_date').val(defaultStart);
            $('#ledger_end_date').val(defaultEnd);
            $('#ledger_filter_tipe').val('');

            $('#modal_filter_start_date').val(defaultStart);
            $('#modal_filter_end_date').val(defaultEnd);
            $('#modal_tipe_all').prop('checked', true);

            $('.btn-date-preset').removeClass('active');
            $('.btn-date-preset[data-preset="thisMonth"]').addClass('active');

            updateModalPreviewAndStepper();
            updateFilterUI();
            tableLedger.ajax.reload();
        }

        $('#btn_modal_reset_filter').on('click', function() {
            resetLedgerFilters();
            $('#modalFilterLedger').modal('hide');
        });

        $('#btn_quick_reset_filter').on('click', function(e) {
            e.preventDefault();
            resetLedgerFilters();
        });

        // Helper Currency Formatter
        function formatCurrency(val) {
            return Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

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

        $(document).on('input keyup', '.input-currency', function() {
            let val = $(this).val();
            if (val) {
                $(this).val(formatRupiah(val));
            }
        });

        // Realtime Character Limit untuk Kas Masuk & Kas Keluar (Nominal maks 26, Keterangan maks 40, Nama Kontak maks 40, No Referensi maks 50)
        $(document).on('input keyup paste', '#cash_in_nominal_input, #cash_out_nominal_input', function() {
            let val = $(this).val() || '';
            if (val.length > 26) {
                val = val.substring(0, 26);
                $(this).val(val);
                if (!Swal.isVisible()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai',
                        text: 'Nominal Jumlah tidak boleh lebih dari 26 karakter!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }
            let counterId = $(this).attr('id') === 'cash_in_nominal_input' ? '#cash_in_jumlah_count' : '#cash_out_jumlah_count';
            $(counterId).text(`${val.length}/26`);
        });

        $(document).on('input keyup paste', '#cash_in_nama_kontak, #cash_out_nama_kontak', function() {
            let val = $(this).val() || '';
            let label = $(this).attr('id') === 'cash_out_nama_kontak' ? 'Dibayarkan Kepada (Penerima)' : 'Diterima Dari (Pembayar)';
            if (val.length > 40) {
                val = val.substring(0, 40);
                $(this).val(val);
                if (!Swal.isVisible()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai',
                        text: `${label} tidak boleh lebih dari 40 karakter!`,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }
            let counterId = $(this).attr('id') === 'cash_in_nama_kontak' ? '#cash_in_nama_kontak_count' : '#cash_out_nama_kontak_count';
            $(counterId).text(`${val.length}/40`);
        });

        $(document).on('input keyup paste', '#cash_in_nomor_referensi, #cash_out_nomor_referensi', function() {
            let val = $(this).val() || '';
            if (val.length > 50) {
                val = val.substring(0, 50);
                $(this).val(val);
                if (!Swal.isVisible()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai',
                        text: 'Nomor Referensi tidak boleh lebih dari 50 karakter!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }
            let counterId = $(this).attr('id') === 'cash_in_nomor_referensi' ? '#cash_in_nomor_referensi_count' : '#cash_out_nomor_referensi_count';
            $(counterId).text(`${val.length}/50`);
        });

        $(document).on('input keyup paste', '#cash_in_keterangan, #cash_out_keterangan', function() {
            let val = $(this).val() || '';
            if (val.length > 40) {
                val = val.substring(0, 40);
                $(this).val(val);
                if (!Swal.isVisible()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Batas Karakter Tercapai',
                        text: 'Keterangan tidak boleh lebih dari 40 karakter!',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2563eb'
                    });
                }
            }
            let counterId = $(this).attr('id') === 'cash_in_keterangan' ? '#cash_in_keterangan_count' : '#cash_out_keterangan_count';
            $(counterId).text(`${val.length}/40`);
        });

        // Reset Form & Select2 State when Modal Hidden / Shown
        $('#modalCashIn').on('hidden.bs.modal show.bs.modal', function() {
            $('#formCashIn')[0].reset();
            $('#cash_in_lawan_coa_id').val('').trigger('change');
            $('#cash_in_kategori').val('').trigger('change');
            $('#cash_in_jumlah_count').text('0/20');
            $('#cash_in_nama_kontak_count').text('0/40');
            $('#cash_in_nomor_referensi_count').text('0/50');
            $('#cash_in_keterangan_count').text('0/40');
        });

        $('#modalCashOut').on('hidden.bs.modal show.bs.modal', function() {
            $('#formCashOut')[0].reset();
            $('#cash_out_lawan_coa_id').val('').trigger('change');
            $('#cash_out_kategori').val('').trigger('change');
            $('#cash_out_saldo_warning').hide();
            $('#cash_out_nominal_input').removeClass('is-invalid border-danger');
            $('#btn_submit_cash_out').prop('disabled', false);
            $('#cash_out_jumlah_count').text('0/20');
            $('#cash_out_nama_kontak_count').text('0/40');
            $('#cash_out_nomor_referensi_count').text('0/50');
            $('#cash_out_keterangan_count').text('0/40');
        });

        // Smart Auto-Select COA based on Kategori for Cash In
        $('#cash_in_kategori').on('change', function() {
            let kat = $(this).val();
            let $select = $('#cash_in_lawan_coa_id');
            let targetCoaId = $(this).find(':selected').data('coa-id');

            if (!targetCoaId && kat) {
                $select.find('option').each(function() {
                    let text = $(this).text().toLowerCase();
                    let val = $(this).val();
                    if (!val) return;

                    if (kat === 'Pembayaran Pelanggan' && (text.includes('piutang') || text.includes('penjualan'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Penerimaan Penjualan Tunai' && (text.includes('penjualan') || text.includes('pendapatan'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Penerimaan Modal / Investor' && (text.includes('modal') || text.includes('ekuitas'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Pendapatan Jasa / Lainnya' && (text.includes('pendapatan') || text.includes('jasa'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Pengembalian Uang Muka / Piutang' && (text.includes('uang muka') || text.includes('piutang'))) {
                        targetCoaId = val; return false;
                    }
                });
            }

            if (targetCoaId) {
                $select.val(targetCoaId).trigger('change');
                $('#cash_in_kategori_info').html('<span class="text-success"><i class="bi bi-check-circle me-1"></i>Akun Lawan otomatis terpilih berdasarkan mapping kategori.</span>');
            } else {
                $('#cash_in_kategori_info').html('<i class="bi bi-info-circle me-1"></i>Pilih kategori untuk otomatisasi akun COA lawan.');
            }
        });

        // Smart Auto-Select COA based on Kategori for Cash Out
        $('#cash_out_kategori').on('change', function() {
            let kat = $(this).val();
            let $select = $('#cash_out_lawan_coa_id');
            let targetCoaId = $(this).find(':selected').data('coa-id');

            if (!targetCoaId && kat) {
                $select.find('option').each(function() {
                    let text = $(this).text().toLowerCase();
                    let val = $(this).val();
                    if (!val) return;

                    if (kat === 'Pembayaran Supplier' && (text.includes('hutang') || text.includes('utang') || text.includes('pembelian'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Biaya Operasional & Umum' && (text.includes('operasional') || text.includes('umum') || text.includes('beban'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Biaya Logistik & Ongkir' && (text.includes('ongkir') || text.includes('kirim') || text.includes('ekspedisi') || text.includes('logistik') || text.includes('transport'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Biaya Gaji & Upah' && (text.includes('gaji') || text.includes('upah'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Biaya Listrik, Air & Internet' && (text.includes('listrik') || text.includes('air') || text.includes('internet') || text.includes('telepon'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Biaya Pajak & Legal' && (text.includes('pajak') || text.includes('legal'))) {
                        targetCoaId = val; return false;
                    } else if (kat === 'Pengeluaran Kas Kecil' && (text.includes('kas kecil') || text.includes('petty cash'))) {
                        targetCoaId = val; return false;
                    }
                });
            }

            if (targetCoaId) {
                $select.val(targetCoaId).trigger('change');
                $('#cash_out_kategori_info').html('<span class="text-success"><i class="bi bi-check-circle me-1"></i>Akun Lawan otomatis terpilih berdasarkan mapping kategori.</span>');
            } else {
                $('#cash_out_kategori_info').html('<i class="bi bi-info-circle me-1"></i>Pilih kategori untuk otomatisasi akun COA lawan.');
            }
        });

        // Submit: Kas Masuk
        $('#formCashIn').on('submit', function(e) {
            e.preventDefault();

            // Pastikan berkas bukti terlampir
            let fileInput = document.getElementById('cash_in_lampiran');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bukti Foto Wajib Diunggah',
                    text: 'Harap lampirkan foto kuitansi, nota transfer, atau slip setoran kas!',
                    confirmButtonColor: '#16a34a'
                });
                return false;
            }

            let btn = $('#btn_submit_cash_in');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan & Mengompres...');
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('accounting.kas-bank.cash-in.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Masuk');
                    if (res.success) {
                        $('#modalCashIn').modal('hide');
                        $('#cash_in_lawan_coa_id').val('').trigger('change');
                        $('#cash_in_tender_id').val('').trigger('change');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Masuk');
                    let errMsg = xhr.responseJSON ? (xhr.responseJSON.message || 'Terjadi kesalahan sistem.') : 'Terjadi kesalahan sistem.';
                    Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                }
            });
        });

        // ─── Real-time Anti-Minus Balance Validation for Transfer ───
        function validateTransferBalance() {
            let fromOption = $('#transfer_from_select').find(':selected');
            let fromSaldo = parseFloat(fromOption.data('saldo') || 0);
            let rawNominal = $('#transfer_nominal_input').val().replace(/[^0-9]/g, '');
            let nominal = parseFloat(rawNominal) || 0;
            let fromId = $('#transfer_from_select').val();
            let toId = $('#transfer_to_select').val();

            if (fromId) {
                $('#transfer_from_saldo_info').html(
                    `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-wallet2 me-1"></i> Saldo Tersedia: Rp ${formatCurrency(fromSaldo)}</span>`
                );
            } else {
                $('#transfer_from_saldo_info').text('Saldo tersedia akan dipotong otomatis.');
            }

            if (fromId && toId && fromId === toId) {
                $('#transfer_saldo_warning_text').text('Rekening asal dan rekening tujuan tidak boleh sama!');
                $('#transfer_saldo_warning').slideDown(150);
                $('#transfer_nominal_input').addClass('is-invalid border-danger');
                $('#btn_submit_transfer').prop('disabled', true);
                return false;
            }

            if (fromId && nominal > fromSaldo) {
                $('#transfer_saldo_warning_text').html(`Nominal transfer (Rp ${formatCurrency(nominal)}) melebihi saldo rekening asal (Rp ${formatCurrency(fromSaldo)})! Saldo tidak boleh minus.`);
                $('#transfer_saldo_warning').slideDown(150);
                $('#transfer_nominal_input').addClass('is-invalid border-danger');
                $('#btn_submit_transfer').prop('disabled', true);
                return false;
            } else {
                $('#transfer_saldo_warning').slideUp(150);
                $('#transfer_nominal_input').removeClass('is-invalid border-danger');
                $('#btn_submit_transfer').prop('disabled', false);
                return true;
            }
        }

        $('#transfer_from_select, #transfer_to_select').on('change', validateTransferBalance);
        $('#transfer_nominal_input').on('input keyup', validateTransferBalance);

        // ─── Real-time Anti-Minus Balance Validation for Cash Out ───
        function validateCashOutBalance() {
            let option = $('#cash_out_kas_bank_id').find(':selected');
            let saldo = parseFloat(option.data('saldo') || 0);
            let rawNominal = $('#cash_out_nominal_input').val().replace(/[^0-9]/g, '');
            let nominal = parseFloat(rawNominal) || 0;
            let accId = $('#cash_out_kas_bank_id').val();

            if (accId && nominal > saldo) {
                $('#cash_out_saldo_warning_text').html(`Nominal pengeluaran (Rp ${formatCurrency(nominal)}) melebihi saldo kas/bank yang tersedia (Rp ${formatCurrency(saldo)})! Saldo tidak boleh minus.`);
                $('#cash_out_saldo_warning').slideDown(150);
                $('#cash_out_nominal_input').addClass('is-invalid border-danger');
                $('#btn_submit_cash_out').prop('disabled', true);
                return false;
            } else {
                $('#cash_out_saldo_warning').slideUp(150);
                $('#cash_out_nominal_input').removeClass('is-invalid border-danger');
                $('#btn_submit_cash_out').prop('disabled', false);
                return true;
            }
        }

        $('#cash_out_kas_bank_id').on('change', validateCashOutBalance);
        $('#cash_out_nominal_input').on('input keyup', validateCashOutBalance);

        // Submit: Kas Keluar
        $('#formCashOut').on('submit', function(e) {
            e.preventDefault();
            if (!validateCashOutBalance()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Saldo Tidak Mencukupi',
                    text: 'Nominal pengeluaran melebihi saldo kas/bank yang tersedia! Saldo tidak boleh minus.',
                    confirmButtonColor: '#dc2626'
                });
                return false;
            }

            // Pastikan berkas bukti terlampir
            let fileInput = document.getElementById('cash_out_lampiran');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Bukti Foto Wajib Diunggah',
                    text: 'Harap lampirkan foto kuitansi, nota belanja, atau bukti pembayaran pengeluaran kas!',
                    confirmButtonColor: '#dc2626'
                });
                return false;
            }

            let btn = $('#btn_submit_cash_out');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan & Mengompres...');
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('accounting.kas-bank.cash-out.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                    if (res.success) {
                        $('#modalCashOut').modal('hide');
                        $('#cash_out_lawan_coa_id').val('').trigger('change');
                        $('#cash_out_tender_id').val('').trigger('change');
                        $('#cash_out_saldo_warning').hide();
                        $('#cash_out_nominal_input').removeClass('is-invalid border-danger');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                    let errMsg = xhr.responseJSON ? (xhr.responseJSON.message || 'Terjadi kesalahan sistem.') : 'Terjadi kesalahan sistem.';
                    Swal.fire({ icon: 'error', title: 'Error', text: errMsg });
                }
            });
        });

        // Submit: Transfer Antar Rekening
        $('#formTransfer').on('submit', function(e) {
            e.preventDefault();
            if (!validateTransferBalance()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Transfer Tidak Dapat Diproses',
                    text: 'Saldo pada rekening asal tidak mencukupi untuk nominal transfer ini, atau rekening tujuan sama dengan rekening asal. Saldo tidak boleh minus!',
                    confirmButtonColor: '#0284c7'
                });
                return false;
            }

            let btn = $('#btn_submit_transfer');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');
            $.ajax({
                url: "{{ route('accounting.kas-bank.transfer.store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Proses Transfer Saldo');
                    if (res.success) {
                        $('#modalTransfer').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Proses Transfer Saldo');
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                }
            });
        });

        // Detail Voucher & Audit Trail
        $(document).on('click', '.btn-detail-mutasi', function() {
            let id = $(this).data('id');
            $.get(`/accounting/kas-bank/${id}`, function(res) {
                if (res.success && res.mutasi) {
                    let m = res.mutasi;
                    let acc = m.kas_bank;

                    let tipeColor = m.tipe === 'Masuk' ? 'bg-success text-white' : (m.tipe === 'Keluar' ? 'bg-danger text-white' : 'bg-info text-white');
                    $('#detail_tipe_badge').attr('class', `badge px-3 py-1 rounded-pill font-monospace fw-bold ${tipeColor}`).text(m.tipe.toUpperCase());
                    
                    let statusColor = m.status === 'Disetujui' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle';
                    $('#detail_status_badge').attr('class', `badge px-2.5 py-1 rounded-pill font-monospace fw-semibold ${statusColor}`).text(m.status);

                    $('#detail_nomor_transaksi').text(m.nomor_transaksi);
                    $('#detail_tanggal_transaksi').html('<i class="bi bi-calendar-event text-secondary"></i> Tanggal: ' + new Date(m.tanggal).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}));

                    $('#detail_nama_akun').text(acc ? acc.nama_akun : '-');
                    $('#detail_nomor_rekening').text(acc && acc.nomor_rekening ? acc.nomor_rekening : (acc ? acc.tipe : '-'));

                    let sign = m.tipe === 'Masuk' ? '+ ' : (m.tipe === 'Keluar' ? '- ' : '');
                    $('#detail_jumlah_display').text(sign + 'Rp ' + formatCurrency(m.jumlah));
                    if (m.tipe === 'Masuk') {
                        $('#detail_jumlah_display').css('color', '#16a34a');
                        $('#detail_nominal_container').css({
                            'background': 'linear-gradient(135deg, rgba(22, 163, 74, 0.08) 0%, rgba(22, 163, 74, 0.02) 100%)',
                            'border-color': 'rgba(22, 163, 74, 0.25)'
                        });
                    } else if (m.tipe === 'Keluar') {
                        $('#detail_jumlah_display').css('color', '#dc2626');
                        $('#detail_nominal_container').css({
                            'background': 'linear-gradient(135deg, rgba(220, 38, 38, 0.08) 0%, rgba(220, 38, 38, 0.02) 100%)',
                            'border-color': 'rgba(220, 38, 38, 0.25)'
                        });
                    } else {
                        $('#detail_jumlah_display').css('color', '#0284c7');
                        $('#detail_nominal_container').css({
                            'background': 'linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(2, 132, 199, 0.02) 100%)',
                            'border-color': 'rgba(2, 132, 199, 0.25)'
                        });
                    }

                    $('#detail_kategori').text(m.kategori);
                    $('#detail_kontak').text(m.nama_kontak || '-');
                    $('#detail_referensi').text(m.nomor_referensi || '-');
                    
                    let jurnalInfo = m.jurnal ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1"><i class="bi bi-journal-check me-1"></i> ${m.jurnal.nomor_jurnal}</span>` : '<span class="text-muted small fst-italic">Auto-Journal Linked</span>';
                    if (m.lawan_coa) {
                        jurnalInfo += `<div class="small text-muted font-monospace mt-1">Lawan COA: ${m.lawan_coa.code} - ${m.lawan_coa.name}</div>`;
                    }
                    $('#detail_nomor_jurnal').html(jurnalInfo);

                    $('#detail_keterangan').text(m.keterangan || '-');

                    // ─── TAMPILKAN HUBUNGAN TENDER / PROYEK ───
                    if (m.tender) {
                        $('#detail_tender_container').show();
                        $('#detail_tender_kontrak').text(m.tender.nomor_kontrak);
                        $('#detail_tender_nama').text(m.tender.nama_tender);
                        let entitasLabel = m.tender.branch ? ('Cabang ' + (m.tender.branch.nama_cabang || '')) : (m.tender.company ? m.tender.company.nama_perusahaan : 'PT Pusat');
                        $('#detail_tender_entitas').text(entitasLabel);
                    } else {
                        $('#detail_tender_container').hide();
                    }

                    $('#detail_saldo_sebelum').text('Rp ' + formatCurrency(m.saldo_sebelum));
                    $('#detail_saldo_sesudah').text('Rp ' + formatCurrency(m.saldo_sesudah));

                    // ─── JEJAK AUDIT SISTEM (CREATED BY & CANCELLED BY) ───
                    let creatorName = m.creator ? (m.creator.nama_lengkap || m.creator.username) : 'System / Administrator';
                    let createdTime = m.created_at ? new Date(m.created_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-';
                    $('#detail_audit_creator').text(creatorName);
                    $('#detail_audit_created_at').text('Dibuat: ' + createdTime);

                    if (m.status === 'Dibatalkan') {
                        let cancelerName = m.canceler ? (m.canceler.nama_lengkap || m.canceler.username) : (m.creator ? (m.creator.nama_lengkap || m.creator.username) : 'Auditor');
                        let cancelTime = m.cancelled_at ? new Date(m.cancelled_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : (m.updated_at ? new Date(m.updated_at).toLocaleString('id-ID', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '-');
                        
                        $('#detail_audit_cancel_box').removeClass('bg-body').addClass('bg-danger-subtle border-danger-subtle');
                        $('#detail_audit_cancel_icon').removeClass('bg-secondary-subtle text-secondary').addClass('bg-danger text-white').html('<i class="bi bi-x-circle-fill fs-6"></i>');
                        $('#detail_audit_cancel_title').text('DIBATALKAN OLEH:').addClass('text-danger fw-semibold');
                        $('#detail_audit_canceler').text(cancelerName).addClass('text-danger');
                        $('#detail_audit_cancelled_at').text('Dibatalkan: ' + cancelTime).addClass('text-danger');

                        if (m.alasan_pembatalan) {
                            $('#detail_audit_alasan_row').removeClass('d-none');
                            $('#detail_audit_alasan_text').text(m.alasan_pembatalan);
                        } else {
                            $('#detail_audit_alasan_row').addClass('d-none');
                        }
                    } else {
                        $('#detail_audit_cancel_box').removeClass('bg-danger-subtle border-danger-subtle').addClass('bg-body');
                        $('#detail_audit_cancel_icon').removeClass('bg-danger text-white').addClass('bg-success-subtle text-success').html('<i class="bi bi-patch-check-fill fs-6"></i>');
                        $('#detail_audit_cancel_title').text('STATUS AUDIT:').removeClass('text-danger fw-semibold');
                        $('#detail_audit_canceler').text('Disetujui & Valid (Aktif)').removeClass('text-danger');
                        $('#detail_audit_cancelled_at').text('Status: Pembukuan Sah').removeClass('text-danger');
                        $('#detail_audit_alasan_row').addClass('d-none');
                    }

                    $('#detail_sig_operator').text(`( ${creatorName} )`);
                    $('#detail_sig_kontak').text(`( ${m.nama_kontak || 'Pihak Terkait'} )`);

                    // ─── TAMPILKAN TOMBOL BATALKAN DI MODAL DETAIL ───
                    if (res.can_cancel) {
                        $('#btn_detail_cancel_mutasi')
                            .removeClass('d-none')
                            .data('id', m.id)
                            .data('nomor', m.nomor_transaksi);
                    } else {
                        $('#btn_detail_cancel_mutasi')
                            .addClass('d-none')
                            .data('id', '')
                            .data('nomor', '');
                    }

                    $('#modalDetailMutasi').modal('show');
                }
            });
        });

        // Action: Buka Modal Batalkan Transaksi Kas (dari Modal Detail)
        $(document).on('click', '#btn_detail_cancel_mutasi, .btn-cancel-mutasi', function() {
            let id = $(this).data('id');
            let no = $(this).data('nomor');
            if (!id) return;

            $('#modalDetailMutasi').modal('hide');
            $('#cancel_mutasi_id').val(id);
            $('#cancel_mutasi_nomor').text(no);
            $('#cancel_alasan_input').val('');
            $('#cancel_char_counter').text('0/40');
            setTimeout(function() {
                $('#modalCancelMutasi').modal('show');
            }, 300);
        });

        $('#modalCancelMutasi').on('shown.bs.modal', function() {
            $('#cancel_alasan_input').focus();
        });

        $('#cancel_alasan_input').on('input', function() {
            let len = $(this).val().length;
            $('#cancel_char_counter').text(len + '/40');
        });

        // Submit Form Pembatalan Transaksi
        $('#formCancelMutasi').on('submit', function(e) {
            e.preventDefault();
            let id = $('#cancel_mutasi_id').val();
            let alasan = $('#cancel_alasan_input').val().trim();

            if (!alasan) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan pembatalan transaksi wajib diisi!' });
                return;
            }
            if (alasan.length > 40) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Alasan pembatalan maksimal 40 karakter!' });
                return;
            }

            let $btn = $('#btn_submit_cancel');
            let origText = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Membatalkan...');

            $.post(`/accounting/kas-bank/${id}/cancel`, {
                _token: '{{ csrf_token() }}',
                alasan_pembatalan: alasan
            }, function(res) {
                $('#modalCancelMutasi').modal('hide');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil Dibatalkan', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    $btn.prop('disabled', false).html(origText);
                }
            }).fail(function(xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan saat membatalkan transaksi.' });
                $btn.prop('disabled', false).html(origText);
            });
        });
    });
</script>
@endpush
</x-layouts>
