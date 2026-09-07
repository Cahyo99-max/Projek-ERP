<x-layouts>
    <x-slot:title>Kas &amp; Rekening Bank (Finance &amp; Treasury)</x-slot:title>

    <div class="container-fluid py-2.5 px-2 px-md-4">
        
        <!-- Header Toolbar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
            <div>
                <h4 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2 kb-header-title">
                    <span class="p-1.5 p-md-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-bank2 fs-5"></i>
                    </span>
                    <span>Buku Kas &amp; Rekening Bank</span>
                </h4>
            </div>
            
            <!-- Tombol Action Header (Pepet ke Kanan) -->
            <div class="d-flex align-items-center gap-2 kb-header-actions ms-md-auto">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
                    <i class="bi bi-plus-circle fs-6 text-success"></i>
                    <span>Tambah Rekening</span>
                </button>
                <button type="button" class="btn btn-info text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTransfer" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                    <i class="bi bi-arrow-left-right fs-6"></i>
                    <span>Transfer Antar Bank</span>
                </button>
            </div>
        </div>

        <!-- Section 1: 4 Metrik Kartu Ringkasan Keuangan (Cinematic Glow Bento Grid) -->
        <div class="row g-3 gy-3 mb-4">
            <!-- Card 1: Total Kas Tunai (Blue) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-blue position-relative overflow-hidden text-white p-3 p-md-3.5 d-flex flex-column justify-content-between">
                    <div class="kb-cine-circle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                        <span class="text-white text-opacity-85 small fw-bold text-uppercase kb-cine-title">
                            TOTAL KAS TUNAI
                        </span>
                        <div class="kb-cine-icon shadow-sm">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="z-1 mt-auto">
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap">
                            Rp {{ number_format($totalKas, 0, ',', '.') }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub">
                            {{ $accounts->where('tipe', 'Kas Tunai')->count() }} Akun Kas Tunai Aktif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Rekening Bank (Green) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-green position-relative overflow-hidden text-white p-3 p-md-3.5 d-flex flex-column justify-content-between">
                    <div class="kb-cine-circle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                        <span class="text-white text-opacity-85 small fw-bold text-uppercase kb-cine-title">
                            TOTAL BANK &amp; GIRO
                        </span>
                        <div class="kb-cine-icon shadow-sm">
                            <i class="bi bi-bank2"></i>
                        </div>
                    </div>
                    <div class="z-1 mt-auto">
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap">
                            Rp {{ number_format($totalBank, 0, ',', '.') }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub">
                            {{ $accounts->where('tipe', '!=', 'Kas Tunai')->count() }} Rekening Bank Terhubung
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Penerimaan Bulan Ini (Purple) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-purple position-relative overflow-hidden text-white p-3 p-md-3.5 d-flex flex-column justify-content-between">
                    <div class="kb-cine-circle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                        <span class="text-white text-opacity-85 small fw-bold text-uppercase kb-cine-title">
                            KAS MASUK (INFLOW)
                        </span>
                        <div class="kb-cine-icon shadow-sm">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                    <div class="z-1 mt-auto">
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap">
                            + Rp {{ number_format($cashInThisMonth, 0, ',', '.') }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub">
                            <i class="bi bi-arrow-up text-white"></i> Penerimaan Kas Bulan Ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Pengeluaran Bulan Ini (Amber/Orange) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 rounded-4 h-100 kb-cinematic-card kb-cine-amber position-relative overflow-hidden text-white p-3 p-md-3.5 d-flex flex-column justify-content-between">
                    <div class="kb-cine-circle"></div>
                    <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                        <span class="text-white text-opacity-85 small fw-bold text-uppercase kb-cine-title">
                            KAS KELUAR (OUTFLOW)
                        </span>
                        <div class="kb-cine-icon shadow-sm">
                            <i class="bi bi-graph-down-arrow"></i>
                        </div>
                    </div>
                    <div class="z-1 mt-auto">
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap">
                            - Rp {{ number_format($cashOutThisMonth, 0, ',', '.') }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub">
                            <i class="bi bi-arrow-down text-white"></i> Pengeluaran Kas Bulan Ini
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Daftar Kartu Rekening Kas & Bank Aktif -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 kb-card-section">
            <div class="card-header bg-transparent border-bottom py-2.5 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold text-body-emphasis mb-0 d-flex align-items-center gap-2 small fs-md-6">
                    <i class="bi bi-wallet2 text-success"></i> Rekening Kas &amp; Bank
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 px-md-3 py-1 fw-bold small">Total: Rp {{ number_format($totalSemua, 0, ',', '.') }}</span>
            </div>
            <div class="card-body p-3 p-md-4 kb-cards-scroll-container">
                <div class="row g-3 gy-3 g-xl-4">
                    @forelse($accounts as $acc)
                    @php
                        $bankText = strtolower(($acc->nama_bank ?? '') . ' ' . $acc->nama_akun);
                        if (str_contains($bankText, 'bca')) {
                            $cardTheme = 'kb-card-bca';
                            $cardLogoText = 'BCA';
                            $watermarkIcon = 'bi-bank';
                        } elseif (str_contains($bankText, 'mandiri')) {
                            $cardTheme = 'kb-card-mandiri';
                            $cardLogoText = 'MANDIRI';
                            $watermarkIcon = 'bi-bank2';
                        } elseif (str_contains($bankText, 'bri')) {
                            $cardTheme = 'kb-card-bri';
                            $cardLogoText = 'BRI';
                            $watermarkIcon = 'bi-bank';
                        } elseif (str_contains($bankText, 'bni')) {
                            $cardTheme = 'kb-card-bni';
                            $cardLogoText = 'BNI';
                            $watermarkIcon = 'bi-bank2';
                        } elseif ($acc->tipe === 'Kas Tunai' || str_contains($bankText, 'kas')) {
                            $cardTheme = 'kb-card-kas';
                            $cardLogoText = 'CASH / KAS';
                            $watermarkIcon = 'bi-cash-coin';
                        } elseif ($acc->tipe === 'E-Wallet / Giro' || str_contains($bankText, 'wallet') || str_contains($bankText, 'giro')) {
                            $cardTheme = 'kb-card-ewallet';
                            $cardLogoText = 'E-WALLET';
                            $watermarkIcon = 'bi-wallet2';
                        } else {
                            $cardTheme = 'kb-card-default';
                            $cardLogoText = 'BANK';
                            $watermarkIcon = 'bi-credit-card-2-front';
                        }
                    @endphp
                    <div class="col-xxl-4 col-xl-6 col-md-6 col-12">
                        <div class="kb-modern-card {{ $cardTheme }} rounded-4 p-4 shadow-lg position-relative overflow-hidden text-white d-flex flex-column justify-content-between kb-clickable-card"
                             data-id="{{ $acc->id }}"
                             data-nama="{{ $acc->nama_akun }}"
                             data-tipe="{{ $acc->tipe }}"
                             data-saldo="Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }}"
                             data-coa="{{ $acc->coa ? ($acc->coa->code . ' - ' . $acc->coa->name) : '-' }}"
                             data-entitas="{{ $acc->entitas_label }}"
                             data-rekening="{{ $acc->nomor_rekening ?: '-' }}"
                             data-atas-nama="{{ $acc->atas_nama ?: '-' }}"
                             title="Klik kartu untuk melihat rincian mutasi (Kas Masuk, Kas Keluar, & Transfer)">
                            
                            <!-- Ambient Light Shimmer Overlay -->
                            <div class="kb-card-shine"></div>
                            
                            <!-- Watermark Background Icon -->
                            <i class="bi {{ $watermarkIcon }} kb-card-watermark position-absolute"></i>

                            <!-- Card Top Bar: Chip & Controls -->
                            <div class="d-flex justify-content-between align-items-center z-1 mb-2">
                                <div class="d-flex align-items-center gap-2.5">
                                    <!-- Metallic EMV Chip -->
                                    <div class="kb-emv-chip shadow-sm">
                                        <div class="kb-chip-inner"></div>
                                    </div>
                                    <!-- Contactless NFC Wave -->
                                    <div class="kb-nfc-sensor" title="Contactless Active">
                                        <i class="bi bi-wifi" style="transform: rotate(90deg); display: inline-block; font-size: 1.15rem; opacity: 0.85;"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap justify-content-end">
                                    <span class="badge kb-glass-badge font-monospace px-2 py-1 fw-bold" style="font-size: 0.68rem;">
                                        <i class="bi {{ $acc->branch ? 'bi-shop' : 'bi-building' }} me-0.5"></i> {{ $acc->entitas_label }}
                                    </span>
                                    <span class="badge kb-glass-badge font-monospace px-2.5 py-1 fw-bold">
                                        {{ $cardLogoText }}
                                    </span>
                                    <button type="button" class="btn btn-sm kb-btn-glass rounded-circle d-inline-flex align-items-center justify-content-center btn-edit-account shadow-none" data-id="{{ $acc->id }}" title="Edit Rekening">
                                        <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Middle: Account Name & Balance -->
                            <div class="z-1 my-2">
                                <div class="text-white text-opacity-80 fw-bold small text-uppercase mb-1" style="letter-spacing: 1.2px; font-size: 0.78rem; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                                    {{ $acc->nama_akun }}
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mb-2">
                                    <h3 class="fw-bold font-monospace mb-0 text-white kb-balance-text" style="letter-spacing: 0.8px; font-size: 1.65rem;">
                                        Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }}
                                    </h3>
                                </div>
                                @if($acc->nomor_rekening)
                                <div class="font-monospace text-white text-opacity-90 small fw-bold kb-card-number" style="letter-spacing: 3.5px; font-size: 0.92rem;">
                                    {{ chunk_split($acc->nomor_rekening, 4, ' ') }}
                                </div>
                                @else
                                <div class="text-white text-opacity-75 small font-monospace" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                                    <i class="bi bi-shield-check text-success-subtle me-1"></i> KAS TUNAI • AKTIF
                                </div>
                                @endif
                            </div>

                            <!-- Card Bottom Bar: Cardholder & COA Info -->
                            <div class="d-flex justify-content-between align-items-end z-1 pt-2.5 border-top border-white border-opacity-15 mt-2">
                                <div class="pe-3 flex-grow-1">
                                    <div class="text-white text-opacity-70 text-uppercase fw-semibold mb-0.5" style="font-size: 0.65rem; letter-spacing: 0.8px;">CARDHOLDER / ATAS NAMA</div>
                                    <div class="fw-bold font-monospace text-white kb-holder-name" style="font-size: 0.88rem; letter-spacing: 0.5px;">
                                        {{ strtoupper($acc->atas_nama ?: ($acc->nama_akun)) }}
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <div class="text-white text-opacity-70 text-uppercase fw-semibold mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">KODE COA</div>
                                    <span class="badge kb-coa-badge font-monospace px-2.5 py-1 fw-bold">
                                        {{ $acc->coa ? $acc->coa->code : ($acc->kode_akun ?: 'KB-'.$acc->id) }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 text-muted small fst-italic">
                        Belum ada akun kas atau rekening bank yang terdaftar. Klik "+ Tambah Rekening" untuk mendaftarkan akun baru.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Section 3: Tabel Master Akun Rekening Kas & Bank -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden kb-card-section mb-4">
            <div class="card-header bg-transparent border-bottom py-2.5 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="kb-icon-badge bg-success-subtle text-success rounded-3" style="width: 32px; height: 32px;">
                        <i class="bi bi-bank2 fs-6"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-body-emphasis mb-0 small fs-md-6">Tabel Rekening Kas &amp; Bank</h6>
                        <div class="text-muted small d-none d-sm-block">Daftar buku kas tunai dan rekening bank perusahaan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace small d-inline-flex d-md-none align-items-center gap-1" style="font-size: 0.68rem;">
                        <i class="bi bi-arrow-left-right"></i> Geser Tabel &raquo;
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
                        <i class="bi bi-plus-circle text-success"></i>
                        <span>Tambah Rekening</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0 w-100" id="tableMasterKasBank">
                    <thead>
                        <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);">
                            <th style="width: 40px;" class="text-center">No</th>
                            <th>Kode</th>
                            <th>Nama Akun / Buku Kas</th>
                            <th>Tipe</th>
                            <th>Bank &amp; No. Rekening</th>
                            <th>Atas Nama</th>
                            <th>Akun COA</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $idx => $acc)
                        <tr>
                            <td class="text-center font-monospace small text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <span class="badge bg-body-tertiary text-body border font-monospace px-2 py-1">
                                    {{ $acc->kode_akun ?: 'KB-' . $acc->id }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="fw-bold text-body-emphasis">{{ $acc->nama_akun }}</span>
                                    @if($acc->branch)
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-shop me-0.5"></i> {{ $acc->branch->nama_cabang }}</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-building me-0.5"></i> PT Pusat</span>
                                    @endif
                                </div>
                                @if($acc->deskripsi)
                                    <div class="text-muted small">{{ $acc->deskripsi }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $acc->tipe === 'Kas Tunai' ? 'bg-success-subtle text-success border border-success-subtle' : ($acc->tipe === 'Bank' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-purple-subtle text-purple border') }} rounded-pill font-monospace" style="font-size: 0.72rem;">
                                    {{ $acc->tipe }}
                                </span>
                            </td>
                            <td>
                                @if($acc->nomor_rekening)
                                    <div class="font-monospace fw-semibold text-body">{{ $acc->nama_bank ?: 'Bank' }}</div>
                                    <div class="font-monospace text-muted small">{{ $acc->nomor_rekening }}</div>
                                @else
                                    <span class="text-muted small fst-italic">Kas Tunai Fisik</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-body fw-semibold">{{ $acc->atas_nama ?: '-' }}</span>
                            </td>
                            <td>
                                @if($acc->coa)
                                    <span class="badge font-monospace px-2.5 py-1" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.25);">
                                        {{ $acc->coa->code }} - {{ $acc->coa->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-muted small">
                                Rp {{ number_format($acc->saldo_awal, 0, ',', '.') }}
                            </td>
                            <td class="text-end font-monospace fw-bold {{ (float)$acc->saldo_berjalan > 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.72rem;">
                                    Aktif
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-edit-account shadow-none" style="width: 32px; height: 32px;" data-id="{{ $acc->id }}" title="Edit Rekening">
                                    <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                                 </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modals Include -->
    @include('modul_accounting.kas&bank._modal_account')
    @include('modul_accounting.kas&bank._modal_edit_account')
    @include('modul_accounting.kas&bank._modal_cash_in')
    @include('modul_accounting.kas&bank._modal_cash_out')
    @include('modul_accounting.kas&bank._modal_transfer')
    @include('modul_accounting.kas&bank._modal_card_mutasi')
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

            $('#modalAddAccount, #modalEditAccount, #modalCashIn, #modalCashOut').on('shown.bs.modal', function() {
                initSelect2Coa(this);
            });

            // Inisialisasi DataTable Master Rekening Kas & Bank (Default Tampil 10 Data)
            let tableMaster = $('#tableMasterKasBank').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [10] }
                ],
                dom: "<'row px-3 py-2.5 align-items-center'<'col-sm-6'l><'col-sm-6'f>><'table-responsive'tr><'d-flex justify-content-between align-items-center px-3 px-md-4 py-2.5 py-md-3 border-top bg-body-tertiary w-100 flex-wrap gap-2'<'text-muted small font-monospace'i><'ms-auto d-flex justify-content-end'p>>",
                language: {
                    search: "Cari Rekening:",
                    searchPlaceholder: "Nama akun, no. rek, bank, COA...",
                    lengthMenu: "_MENU_ data per hal",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ rekening",
                    paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                    emptyTable: "Belum ada akun kas atau rekening bank yang terdaftar."
                }
            });

            // Inisialisasi DataTable Mutasi Kas (Default Tampil 10 Data)
            let tableKas = $('#tableKasMutasi').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                processing: true,
                serverSide: true,
                dom: "<'row px-3 py-2.5 align-items-center'<'col-sm-6'l><'col-sm-6'f>><'table-responsive'tr><'d-flex justify-content-between align-items-center px-3 px-md-4 py-2.5 py-md-3 border-top bg-body-tertiary w-100 flex-wrap gap-2'<'text-muted small font-monospace'i><'ms-auto d-flex justify-content-end'p>>",
                ajax: {
                    url: "{{ route('accounting.kas-bank.data') }}",
                    data: function (d) {
                        d.kas_bank_id = $('#filter_kas_bank_id').val();
                        d.tipe = $('#filter_tipe').val();
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
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

            // Helper Currency Formatter (Pemisah Ribuan Titik Koma)
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

            // Quick Action Buttons on Bank Cards (Odoo Style)
            $(document).on('click', '.btn-quick-cash-in', function(e) {
                e.preventDefault();
                let accId = $(this).data('id');
                $('#modalCashIn select[name="kas_bank_id"]').val(accId);
                $('#modalCashIn').modal('show');
            });

            $(document).on('click', '.btn-quick-cash-out', function(e) {
                e.preventDefault();
                let accId = $(this).data('id');
                $('#modalCashOut select[name="kas_bank_id"]').val(accId);
                $('#modalCashOut').modal('show');
            });

            // Toggle Bank Fields on Add Account Modal
            $('#acc_tipe_select').on('change', function() {
                if ($(this).val() === 'Bank') {
                    $('.bank-fields').slideDown(200);
                } else {
                    $('.bank-fields').slideUp(200);
                }
            });

            // Toggle Bank Fields on Edit Account Modal
            $('#edit_acc_tipe_select').on('change', function() {
                if ($(this).val() === 'Bank') {
                    $('.edit-bank-fields').slideDown(200);
                } else {
                    $('.edit-bank-fields').slideUp(200);
                }
            });

            // Trigger Modal Edit Rekening
            $(document).on('click', '.btn-edit-account', function() {
                let accId = $(this).data('id');
                $.get(`/accounting/kas-bank/account/${accId}/edit`, function(res) {
                    if (res.success && res.account) {
                        let a = res.account;
                        $('#edit_acc_id').val(a.id);
                        $('#edit_acc_nama_akun').val(a.nama_akun);
                        $('#edit_acc_tipe_select').val(a.tipe);
                        $('#edit_acc_coa_id').val(a.coa_id || '').trigger('change');
                        $('#edit_acc_kode_akun').val(a.kode_akun || '');
                        $('#edit_acc_nama_bank').val(a.nama_bank || '');
                        $('#edit_acc_nomor_rekening').val(a.nomor_rekening || '');
                        $('#edit_acc_atas_nama').val(a.atas_nama || '');
                        $('#edit_acc_deskripsi').val(a.deskripsi || '');

                        if (a.tipe === 'Bank') {
                            $('.edit-bank-fields').show();
                        } else {
                            $('.edit-bank-fields').hide();
                        }

                        $('#modalEditAccount').modal('show');
                    }
                });
            });

            // Submit: Update Rekening Kas/Bank
            $('#formEditAccount').on('submit', function(e) {
                e.preventDefault();
                let accId = $('#edit_acc_id').val();
                let btn = $('#btn_submit_edit_account');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memperbarui...');

                $.ajax({
                    url: `/accounting/kas-bank/account/${accId}/update`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Perbarui Rekening');
                        if (res.success) {
                            $('#modalEditAccount').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Perbarui Rekening');
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    }
                });
            });

            // Submit: Tambah Rekening Kas/Bank
            $('#formAddAccount').on('submit', function(e) {
                e.preventDefault();
                let btn = $('#btn_submit_account');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('accounting.kas-bank.account.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Rekening');
                        if (res.success) {
                            $('#modalAddAccount').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Rekening');
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    }
                });
            });

            // Submit: Kas Masuk (Cash In)
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
                            $('#formCashIn')[0].reset();
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

            // Submit: Kas Keluar (Cash Out)
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
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('accounting.kas-bank.cash-out.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                        if (res.success) {
                            $('#modalCashOut').modal('hide');
                            $('#formCashOut')[0].reset();
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
                            $('#formTransfer')[0].reset();
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

            // Action: Buka Modal Detail Mutasi (Voucher & Audit Trail)
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
                    Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    $btn.prop('disabled', false).html(origText);
                });
            });

            // ─── Klik Kartu Rekening: Buka Modal Mutasi Rekening ───
            let tableCardMutasi = null;

            $(document).on('click', '.kb-clickable-card', function(e) {
                if ($(e.target).closest('.btn-edit-account').length) {
                    return; // Jangan buka modal mutasi jika klik tombol edit
                }

                let accId = $(this).data('id');
                let accNama = $(this).data('nama');
                let accSaldo = $(this).data('saldo');
                let accTipe = $(this).data('tipe');
                let accCoa = $(this).data('coa');
                let accEntitas = $(this).data('entitas');
                let accRek = $(this).data('rekening');
                let accAtasNama = $(this).data('atas-nama');

                $('#card_mutasi_acc_id').val(accId);
                $('#cardMutasiNamaAkun').text(accNama);
                $('#cardMutasiSaldo').text(accSaldo);
                $('#cardMutasiTipe').text(accTipe);
                $('#cardMutasiEntitas').html(`<i class="bi bi-building me-1"></i> ${accEntitas}`);
                $('#cardMutasiCoa').text(accCoa);
                $('#cardMutasiNoRek').text(accRek !== '-' ? accRek : (accAtasNama !== '-' ? accAtasNama : 'Kas Tunai'));
                $('#cardMutasiLedgerLink').attr('href', `/accounting/kas-bank/ledger?account_id=${accId}`);

                $('#modalCardMutasi').modal('show');
            });

            $('#modalCardMutasi').on('shown.bs.modal', function() {
                if (!tableCardMutasi) {
                    tableCardMutasi = $('#tableCardMutasi').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                        ajax: {
                            url: "{{ route('accounting.kas-bank.data') }}",
                            data: function (d) {
                                d.kas_bank_id = $('#card_mutasi_acc_id').val();
                                d.tipe = $('#card_mutasi_filter_tipe').val();
                                d.start_date = $('#card_mutasi_start_date').val();
                                d.end_date = $('#card_mutasi_end_date').val();
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small' },
                            { data: 'nomor_transaksi', name: 'nomor_transaksi' },
                            { data: 'tanggal', name: 'tanggal' },
                            { data: 'pihak_ketiga', name: 'nama_kontak' },
                            { data: 'jumlah', name: 'jumlah', className: 'text-end' },
                            { data: 'saldo_sesudah', name: 'saldo_sesudah', className: 'text-end' },
                            { data: 'status', name: 'status', className: 'text-center' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                        ],
                        order: [[1, 'desc']],
                        language: {
                            search: "Cari Mutasi:",
                            searchPlaceholder: "Nomor mutasi, kontak, referensi...",
                            lengthMenu: "_MENU_ data per hal",
                            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ mutasi",
                            paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                            emptyTable: "Belum ada riwayat mutasi untuk rekening ini.",
                            processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>'
                        }
                    });
                } else {
                    tableCardMutasi.ajax.reload();
                }
            });

            // ─── Filter Mutasi Sub-Modal Actions ───
            $('#btn_open_card_filter').on('click', function() {
                $('#modalFilterMutasiCard').modal('show');
            });

            $('.btn-card-preset-filter').on('click', function() {
                let preset = $(this).data('preset');
                let now = new Date();
                let today = now.toISOString().split('T')[0];

                if (preset === 'today') {
                    $('#card_mutasi_start_date').val(today);
                    $('#card_mutasi_end_date').val(today);
                } else if (preset === 'this_month') {
                    let firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                    let lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
                    $('#card_mutasi_start_date').val(firstDay);
                    $('#card_mutasi_end_date').val(lastDay);
                } else if (preset === 'all') {
                    $('#card_mutasi_start_date').val('');
                    $('#card_mutasi_end_date').val('');
                    $('#card_mutasi_filter_tipe').val('');
                }
            });

            function updateCardFilterBadge() {
                let count = 0;
                if ($('#card_mutasi_filter_tipe').val()) count++;
                if ($('#card_mutasi_start_date').val() || $('#card_mutasi_end_date').val()) count++;

                if (count > 0) {
                    $('#card_filter_count_badge').text(count).removeClass('d-none');
                    $('#card_mutasi_active_filter_badge').removeClass('d-none');
                    $('#btn_card_mutasi_reset').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#card_filter_count_badge').addClass('d-none');
                    $('#card_mutasi_active_filter_badge').addClass('d-none');
                    $('#btn_card_mutasi_reset').addClass('d-none').removeClass('d-flex');
                }
            }

            $('#btn_modal_filter_apply').on('click', function() {
                updateCardFilterBadge();
                if (tableCardMutasi) tableCardMutasi.ajax.reload();
                $('#modalFilterMutasiCard').modal('hide');
            });

            $('#btn_modal_filter_reset, #btn_card_mutasi_reset').on('click', function() {
                $('#card_mutasi_filter_tipe').val('');
                $('#card_mutasi_start_date').val('');
                $('#card_mutasi_end_date').val('');
                updateCardFilterBadge();
                if (tableCardMutasi) tableCardMutasi.ajax.reload();
                $('#modalFilterMutasiCard').modal('hide');
            });

        });
    </script>
    @endpush
</x-layouts>
