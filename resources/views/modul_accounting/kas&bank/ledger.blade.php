<x-layouts>
    <x-slot:title>Buku Kas &amp; Rekening Koran (Bank Statements &amp; Ledger)</x-slot:title>

    <div class="container-fluid px-2 px-md-4 py-3 py-md-4">

    <!-- Top Header & Breadcrumb -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('accounting.kas-bank.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-none">
                    <i class="bi bi-arrow-left"></i> Dashboard Kas &amp; Bank
                </a>
                <span class="text-muted">/</span>
                @if($activeBranch)
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-3 py-1 fw-bold">
                        <i class="bi bi-shop me-1"></i> Cabang: {{ $activeBranch->nama_cabang }}
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 fw-bold">
                        <i class="bi bi-building me-1"></i> Entitas: PT Pusat ({{ $company ? $company->nama_perusahaan : 'Induk' }})
                    </span>
                @endif
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1 fw-bold">Buku Kas &amp; Bank</span>
            </div>
            <h3 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-journal-bookmark-fill text-success"></i> Buku Kas &amp; Rekening Koran
                @if($selectedAccount)
                    <span class="text-success fw-normal">({{ $selectedAccount->nama_akun }})</span>
                @else
                    <span class="text-muted fw-normal">(Semua Rekening)</span>
                @endif
            </h3>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ $selectedAccount ? route('accounting.kas-bank.ledger.account.print', $selectedAccount->id) . '?' . http_build_query(request()->all()) : route('accounting.kas-bank.ledger.print') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-printer"></i>
                <span>Cetak Rekening Koran</span>
            </a>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCashIn">
                <i class="bi bi-arrow-down-left"></i>
                <span>+ Kas Masuk</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCashOut">
                <i class="bi bi-arrow-up-right"></i>
                <span>- Kas Keluar</span>
            </button>
        </div>
    </div>

    <!-- Active Account Banner & Quick Summary -->
    <div class="row g-3 mb-4">
        
        <!-- Account Switcher Banner -->
        <div class="col-xl-5 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 kb-card-section p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1.5" style="letter-spacing: 0.5px;">
                        <i class="bi bi-wallet2 text-success me-1"></i> Pilih Akun Kas / Rekening Bank:
                    </label>
                    <select class="form-select kb-form-control fw-bold rounded-3 shadow-none" id="select_account_switcher">
                        <option value="" {{ !$selectedAccount ? 'selected' : '' }}>-- Konsolidasi (Semua Buku Kas &amp; Bank {{ $activeBranch ? $activeBranch->nama_cabang : 'PT Pusat' }}) --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $selectedAccount && $selectedAccount->id == $acc->id ? 'selected' : '' }}>
                                [{{ $acc->tipe }}] {{ $acc->nama_akun }} (Saldo: Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($selectedAccount)
                <div class="pt-3 mt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-muted small font-monospace">{{ $selectedAccount->nama_bank ?: 'Kas Fisik' }}: {{ $selectedAccount->nomor_rekening ?: '-' }}</div>
                        <div class="text-muted small fst-italic">a.n {{ $selectedAccount->atas_nama ?: ($selectedAccount->nama_akun) }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="badge {{ $selectedAccount->branch_id ? 'bg-secondary-subtle text-secondary border border-secondary-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} rounded-pill font-monospace px-2.5 py-1">
                            <i class="bi {{ $selectedAccount->branch_id ? 'bi-shop' : 'bi-building' }} me-0.5"></i> {{ $selectedAccount->entitas_label }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">
                            COA: {{ $selectedAccount->coa ? $selectedAccount->coa->code : '-' }}
                        </span>
                    </div>
                </div>
                @else
                <div class="pt-3 mt-3 border-top text-muted small fst-italic">
                    Menampilkan gabungan seluruh transaksi mutasi kas tunai dan rekening bank operasional <strong>{{ $activeBranch ? ('Cabang ' . $activeBranch->nama_cabang) : ('PT Pusat (' . ($company ? $company->nama_perusahaan : 'Induk') . ')') }}</strong>.
                </div>
                @endif
            </div>
        </div>

        <!-- 3 Summary Stat Bento Cards (Cinematic Glow) -->
        <div class="col-xl-7 col-lg-6">
            <div class="row g-3 h-100">
                
                <!-- Total Masuk (Green) -->
                <div class="col-sm-4">
                    <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-green position-relative overflow-hidden text-white p-3 d-flex flex-column justify-content-between">
                        <div class="kb-cine-circle"></div>
                        <div class="d-flex justify-content-between align-items-start mb-1 z-1">
                            <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">TOTAL PENERIMAAN</span>
                            <div class="kb-cine-icon" style="width: 34px; height: 34px; border-radius: 9px;">
                                <i class="bi bi-arrow-down-left fs-6"></i>
                            </div>
                        </div>
                        <div class="z-1 mt-auto">
                            <h4 class="fw-bold text-white font-monospace mb-0" style="font-size: 1.25rem;">
                                + Rp {{ number_format($totalMasuk, 0, ',', '.') }}
                            </h4>
                            <div class="text-white text-opacity-80 small mt-1 font-monospace" style="font-size: 0.7rem;">Periode terpilih</div>
                        </div>
                    </div>
                </div>

                <!-- Total Keluar (Amber/Orange) -->
                <div class="col-sm-4">
                    <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-amber position-relative overflow-hidden text-white p-3 d-flex flex-column justify-content-between">
                        <div class="kb-cine-circle"></div>
                        <div class="d-flex justify-content-between align-items-start mb-1 z-1">
                            <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">TOTAL PENGELUARAN</span>
                            <div class="kb-cine-icon" style="width: 34px; height: 34px; border-radius: 9px;">
                                <i class="bi bi-arrow-up-right fs-6"></i>
                            </div>
                        </div>
                        <div class="z-1 mt-auto">
                            <h4 class="fw-bold text-white font-monospace mb-0" style="font-size: 1.25rem;">
                                - Rp {{ number_format($totalKeluar, 0, ',', '.') }}
                            </h4>
                            <div class="text-white text-opacity-80 small mt-1 font-monospace" style="font-size: 0.7rem;">Termasuk transfer keluar</div>
                        </div>
                    </div>
                </div>

                <!-- Net Cashflow / Saldo Berjalan (Purple) -->
                <div class="col-sm-4">
                    <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-purple position-relative overflow-hidden text-white p-3 d-flex flex-column justify-content-between">
                        <div class="kb-cine-circle"></div>
                        <div class="d-flex justify-content-between align-items-start mb-1 z-1">
                            <span class="text-white text-opacity-85 small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                {{ $selectedAccount ? 'SALDO SAAT INI' : 'ARUS KAS BERSIH' }}
                            </span>
                            <div class="kb-cine-icon" style="width: 34px; height: 34px; border-radius: 9px;">
                                <i class="bi bi-wallet2 fs-6"></i>
                            </div>
                        </div>
                        <div class="z-1 mt-auto">
                            <h4 class="fw-bold font-monospace mb-0 text-white" style="font-size: 1.25rem;">
                                Rp {{ number_format($selectedAccount ? $selectedAccount->saldo_berjalan : $netMutasi, 0, ',', '.') }}
                            </h4>
                            <div class="text-white text-opacity-80 small mt-1 font-monospace" style="font-size: 0.7rem;">
                                {{ $selectedAccount ? 'Saldo kas berjalan' : 'Net mutasi periode' }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 kb-card-section">
        <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                
                <!-- Filter Rentang Tanggal -->
                <div class="col-md-5 col-sm-12">
                    <label class="form-label small fw-semibold text-muted mb-1">Periode Transaksi (Rentang Tanggal)</label>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control kb-form-control rounded-start-3" id="ledger_start_date" value="{{ $startDate }}">
                        <span class="input-group-text bg-body-tertiary">s/d</span>
                        <input type="date" class="form-control kb-form-control rounded-end-3" id="ledger_end_date" value="{{ $endDate }}">
                    </div>
                </div>

                <!-- Filter Jenis Mutasi -->
                <div class="col-md-4 col-sm-8">
                    <label class="form-label small fw-semibold text-muted mb-1">Jenis Transaksi</label>
                    <select class="form-select form-select-sm kb-form-control rounded-3" id="ledger_filter_tipe">
                        <option value="">Semua Jenis (Masuk / Keluar / Transfer)</option>
                        <option value="Masuk">Kas Masuk (Cash In)</option>
                        <option value="Keluar">Kas Keluar (Cash Out)</option>
                        <option value="Transfer">Transfer Antar Rekening</option>
                    </select>
                </div>

                <!-- Button Filter & Reset -->
                <div class="col-md-3 col-sm-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill w-100 fw-semibold" id="btn_reset_ledger">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Table Card: Buku Kas & Rekening Koran -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden kb-card-section">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-success"></i>
                <h6 class="fw-bold text-body-emphasis mb-0">
                    Buku Rekening Koran &amp; Mutasi Kas
                </h6>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1 fw-bold">Live Data</span>
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
@include('modul_accounting.kas&bank._modal_cash_in')
@include('modul_accounting.kas&bank._modal_cash_out')
@include('modul_accounting.kas&bank._modal_transfer')
@include('modul_accounting.kas&bank._modal_detail')
@include('modul_accounting.kas&bank._modal_cancel')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/kas_bank.css') }}?v={{ time() }}" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 COA Dropdown (Searchable & Scrollable ~10 items)
        function initSelect2Coa(modalEl) {
            $(modalEl).find('.select2-coa').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $(modalEl),
                    width: '100%',
                    placeholder: '-- Otomatis Pilih COA --',
                    allowClear: true
                });
            });
        }

        $('#modalCashIn, #modalCashOut').on('shown.bs.modal', function() {
            initSelect2Coa(this);
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
            order: [[1, 'desc']],
            language: {
                search: "Cari Transaksi:",
                searchPlaceholder: "Nomor mutasi, kontak, atau referensi...",
                lengthMenu: "_MENU_ data per hal",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ transaksi",
                paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                emptyTable: "Belum ada riwayat mutasi kas & bank.",
                processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Switcher Rekening
        $('#select_account_switcher').on('change', function() {
            let accId = $(this).val();
            if (accId) {
                window.location.href = `/accounting/kas-bank/ledger/${accId}?start_date=${$('#ledger_start_date').val()}&end_date=${$('#ledger_end_date').val()}`;
            } else {
                window.location.href = `/accounting/kas-bank/ledger?start_date=${$('#ledger_start_date').val()}&end_date=${$('#ledger_end_date').val()}`;
            }
        });

        // Filter Triggers
        $('#ledger_filter_tipe, #ledger_start_date, #ledger_end_date').on('change', function() {
            tableLedger.ajax.reload();
        });

        $('#btn_reset_ledger').on('click', function() {
            $('#ledger_filter_tipe').val('');
            $('#ledger_start_date').val("{{ now()->startOfMonth()->toDateString() }}");
            $('#ledger_end_date').val("{{ now()->toDateString() }}");
            tableLedger.ajax.reload();
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

        $(document).on('input keyup', '.input-currency', function() {
            let val = $(this).val();
            if (val) {
                $(this).val(formatRupiah(val));
            }
        });

        // Submit: Kas Masuk
        $('#formCashIn').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btn_submit_cash_in');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            $.ajax({
                url: "{{ route('accounting.kas-bank.cash-in.store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Masuk');
                    if (res.success) {
                        $('#modalCashIn').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Masuk');
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
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
                    `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-wallet2 me-1"></i> Saldo Tersedia: Rp ${formatRupiah(fromSaldo.toString())}</span>`
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
                $('#transfer_saldo_warning_text').html(`Nominal transfer (Rp ${formatRupiah(nominal.toString())}) melebihi saldo rekening asal (Rp ${formatRupiah(fromSaldo.toString())})! Saldo tidak boleh minus.`);
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
                $('#cash_out_saldo_warning_text').html(`Nominal pengeluaran (Rp ${formatRupiah(nominal.toString())}) melebihi saldo kas/bank yang tersedia (Rp ${formatRupiah(saldo.toString())})! Saldo tidak boleh minus.`);
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

            let btn = $('#btn_submit_cash_out');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');
            $.ajax({
                url: "{{ route('accounting.kas-bank.cash-out.store') }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                    if (res.success) {
                        $('#modalCashOut').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
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
                    $('#detail_jumlah_display').text(sign + 'Rp ' + Number(m.jumlah).toLocaleString('id-ID'));
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

                    $('#detail_saldo_sebelum').text('Rp ' + Number(m.saldo_sebelum).toLocaleString('id-ID'));
                    $('#detail_saldo_sesudah').text('Rp ' + Number(m.saldo_sesudah).toLocaleString('id-ID'));

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

                    $('#modalDetailMutasi').modal('show');
                }
            });
        });

        // Action: Buka Modal Batalkan Transaksi Kas
        $(document).on('click', '.btn-cancel-mutasi', function() {
            let id = $(this).data('id');
            let no = $(this).data('nomor');

            $('#cancel_mutasi_id').val(id);
            $('#cancel_mutasi_nomor').text(no);
            $('#cancel_alasan_input').val('');
            $('#cancel_char_counter').text('0/40');
            $('#modalCancelMutasi').modal('show');
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
