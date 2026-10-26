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
            <div class="d-flex align-items-center gap-2 kb-header-actions ms-md-auto flex-wrap">
                @can('tambah-kas-masuk')
                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCashIn">
                    <i class="bi bi-arrow-down-left fs-6"></i>
                    <span>+ Kas Masuk</span>
                </button>
                @endcan
                @can('tambah-kas-keluar')
                <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCashOut">
                    <i class="bi bi-arrow-up-right fs-6"></i>
                    <span>- Kas Keluar</span>
                </button>
                @endcan
                @can('transfer-kas-bank')
                <button type="button" class="btn btn-info text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTransfer" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                    <i class="bi bi-arrow-left-right fs-6"></i>
                    <span>Transfer Antar Bank</span>
                </button>
                @endcan
                @can('tambah-kas-bank')
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
                    <i class="bi bi-plus-circle fs-6 text-success"></i>
                    <span>Tambah Rekening</span>
                </button>
                @endcan
            </div>
        </div>

        @php
            $fmtRupiah = fn($val) => number_format((float)$val, 2, ',', '.');
        @endphp

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
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap" id="stat_total_kas">
                            Rp {{ $fmtRupiah($totalKas) }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub" id="stat_sub_kas">
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
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap" id="stat_total_bank">
                            Rp {{ $fmtRupiah($totalBank) }}
                        </h3>
                        <div class="text-white text-opacity-80 small font-monospace text-truncate kb-cine-sub" id="stat_sub_bank">
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
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap" id="stat_cash_in">
                            + Rp {{ $fmtRupiah($cashInThisMonth) }}
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
                        <h3 class="fw-bold font-monospace mb-1 text-white kb-cine-value text-nowrap" id="stat_cash_out">
                            - Rp {{ $fmtRupiah($cashOutThisMonth) }}
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
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 px-md-3 py-1 fw-bold small" id="badge_total_semua">Total: Rp {{ $fmtRupiah($totalSemua) }}</span>
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
                             id="card_container_{{ $acc->id }}"
                             data-id="{{ $acc->id }}"
                             data-nama="{{ $acc->nama_akun }}"
                             data-tipe="{{ $acc->tipe }}"
                             data-saldo="Rp {{ $fmtRupiah($acc->saldo_berjalan) }}"
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
                                    @can('detail-kas-bank')
                                    <button type="button" class="btn btn-sm kb-btn-glass rounded-circle d-inline-flex align-items-center justify-content-center btn-detail-account shadow-none" data-id="{{ $acc->id }}" title="Detail Rekening">
                                        <i class="bi bi-eye-fill" style="font-size: 0.75rem;"></i>
                                    </button>
                                    @endcan
                                    @can('edit-kas-bank')
                                    <button type="button" class="btn btn-sm kb-btn-glass rounded-circle d-inline-flex align-items-center justify-content-center btn-edit-account shadow-none" data-id="{{ $acc->id }}" title="Edit Rekening">
                                        <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                                    </button>
                                    @endcan
                                </div>
                            </div>

                            <!-- Card Middle: Account Name & Balance -->
                            <div class="z-1 my-2">
                                <div class="text-white text-opacity-80 fw-bold small text-uppercase mb-1" style="letter-spacing: 1.2px; font-size: 0.78rem; text-shadow: 0 1px 3px rgba(0,0,0,0.6);">
                                    {{ $acc->nama_akun }}
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mb-2">
                                    <h3 class="fw-bold font-monospace mb-0 text-white kb-balance-text" style="letter-spacing: 0.8px; font-size: 1.65rem;" id="card_saldo_{{ $acc->id }}">
                                        Rp {{ $fmtRupiah($acc->saldo_berjalan) }}
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace small d-inline-flex d-md-none align-items-center gap-1" style="font-size: 0.68rem;">
                        <i class="bi bi-arrow-left-right"></i> Geser Tabel &raquo;
                    </span>
                    <a href="{{ route('accounting.kas-bank.print-rekening') }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" title="Cetak Dokumen atau Simpan PDF">
                        <i class="bi bi-printer"></i>
                        <span>Cetak PDF</span>
                    </a>
                    @can('tambah-kas-bank')
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Rekening</span>
                    </button>
                    @endcan
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
                            <th class="text-center" style="width: 95px;">Aksi</th>
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
                                Rp {{ $fmtRupiah($acc->saldo_awal) }}
                            </td>
                            <td class="text-end font-monospace fw-bold {{ (float)$acc->saldo_berjalan > 0 ? 'text-success' : 'text-danger' }}" id="table_saldo_{{ $acc->id }}">
                                Rp {{ $fmtRupiah($acc->saldo_berjalan) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.72rem;">
                                    Aktif
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1.5">
                                    @can('detail-kas-bank')
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-detail-account shadow-none" style="width: 32px; height: 32px;" data-id="{{ $acc->id }}" title="Detail Rekening">
                                        <i class="bi bi-eye-fill" style="font-size: 0.8rem;"></i>
                                    </button>
                                    @endcan
                                    @can('edit-kas-bank')
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-edit-account shadow-none" style="width: 32px; height: 32px;" data-id="{{ $acc->id }}" title="Edit Rekening">
                                        <i class="bi bi-pencil-fill" style="font-size: 0.75rem;"></i>
                                    </button>
                                    @endcan
                                    @cannot('detail-kas-bank')
                                        @cannot('edit-kas-bank')
                                        <span class="text-muted small">-</span>
                                        @endcannot
                                    @endcannot
                                </div>
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
    @include('modul_accounting.kas&bank._modal_detail_account')
    @include('modul_accounting.kas&bank._modal_cash_in')
    @include('modul_accounting.kas&bank._modal_cash_out')
    @include('modul_accounting.kas&bank._modal_transfer')
    @include('modul_accounting.kas&bank._modal_card_mutasi')
    @include('modul_accounting.kas&bank._modal_detail')
    @include('modul_accounting.kas&bank._modal_cancel')
    @include('modul_accounting.kas&bank._modal_preview_bukti')

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/kas_bank.css') }}?v={{ time() }}" />
    <style>
        @keyframes kbLiveFlash {
            0% {
                transform: scale(1.06);
                filter: brightness(1.7) drop-shadow(0 0 12px rgba(56, 189, 248, 0.9));
                color: #38bdf8 !important;
            }
            100% {
                transform: scale(1);
                filter: none;
            }
        }
        .kb-live-updated {
            animation: kbLiveFlash 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.3s ease;
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

                // Dropdown Rekening Transfer (Searchable & Scrollable)
                $(modalEl).find('.select2-transfer-from').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $(modalEl),
                            width: '100%',
                            placeholder: '-- Cari & Pilih Rekening Asal --'
                        });
                    }
                });

                $(modalEl).find('.select2-transfer-to').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $(modalEl),
                            width: '100%',
                            placeholder: '-- Cari & Pilih Rekening Tujuan --'
                        });
                    }
                });
            }

            $('#modalAddAccount, #modalEditAccount, #modalCashIn, #modalCashOut, #modalTransfer').on('shown.bs.modal', function() {
                initSelect2Dropdowns(this);
            });

            $('#modalTransfer').on('show.bs.modal', function() {
                let nomLen = $('#transfer_nominal_input').val().length;
                $('#transfer_nominal_counter').text(nomLen + '/20 kar').removeClass('text-danger fw-bold').addClass('text-muted');
                let ketLen = $('#transfer_keterangan_input').val().length;
                $('#transfer_keterangan_counter').text(ketLen + '/40 karakter').removeClass('text-danger fw-bold').addClass('text-muted');
                $('#transfer_saldo_warning').hide();
                $('#transfer_nominal_input').removeClass('is-invalid border-danger');
            });

            // Inisialisasi DataTable Master Rekening Kas & Bank (Default Tampil 10 Data)
            let tableMaster = $('#tableMasterKasBank').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [-1] }
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

            // Helper Currency Formatter (Pemisah Ribuan Titik Koma)
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

            // Quick Action Buttons on Bank Cards & Sub-Modal (Odoo Style)
            $(document).on('click', '.btn-quick-cash-in, .btn-card-quick-cash-in', function(e) {
                e.preventDefault();
                let accId = $(this).data('id') || $('#card_mutasi_acc_id').val();
                if ($('#modalCardMutasi').hasClass('show')) {
                    $('#modalCardMutasi').modal('hide');
                }
                $('#cash_in_kas_bank_id').val(accId).trigger('change');
                $('#modalCashIn').modal('show');
            });

            $(document).on('click', '.btn-quick-cash-out, .btn-card-quick-cash-out', function(e) {
                e.preventDefault();
                let accId = $(this).data('id') || $('#card_mutasi_acc_id').val();
                if ($('#modalCardMutasi').hasClass('show')) {
                    $('#modalCardMutasi').modal('hide');
                }
                $('#cash_out_kas_bank_id').val(accId).trigger('change');
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

            // Toggle Akun Lawan Saldo Awal on Add Account Modal
            $('#add_acc_saldo_awal').on('input keyup change', function() {
                let rawVal = $(this).val() || '0';
                let nominal = parseFloat(rawVal.replace(/\./g, '').replace(/,/g, '.')) || 0;
                if (nominal > 0) {
                    $('#box_lawan_saldo_awal').slideDown(200);
                    $('#add_acc_lawan_coa_id').prop('required', true);
                } else {
                    $('#box_lawan_saldo_awal').slideUp(200);
                    $('#add_acc_lawan_coa_id').prop('required', false);
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

            // Trigger Modal Detail Rekening Kas & Bank
            $(document).on('click', '.btn-detail-account', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let accId = $(this).data('id');
                if (!accId) return;

                // Reset / Loading State
                $('#detail_acc_kode').text('KB-...');
                $('#detail_acc_nama').text('Memuat data...');
                $('#detail_acc_tipe_badge').text('-').attr('class', 'badge rounded-pill font-monospace px-2 py-0.5 bg-secondary-subtle text-secondary');
                $('#detail_acc_entitas').text('Memuat...');
                $('#detail_acc_saldo_berjalan').html('<span class="spinner-border spinner-border-sm"></span>');
                $('#detail_acc_saldo_awal').text('Rp -');
                $('#detail_acc_total_masuk').text('Rp -');
                $('#detail_acc_total_keluar').text('Rp -');
                $('#detail_acc_tipe_val').text('-');
                $('#detail_acc_nama_bank').text('-');
                $('#detail_acc_no_rek').text('-');
                $('#detail_acc_atas_nama').text('-');
                $('#detail_acc_coa_badge').text('Memuat integrasi COA...');
                $('#detail_acc_deskripsi').text('-');
                $('#modalDetailAccount').modal('show');

                $.ajax({
                    url: `/accounting/kas-bank/account/${accId}/detail`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.account) {
                            let a = res.account;
                            let s = res.stats || {};

                            const formatIdr = (val) => {
                                let num = parseFloat(val) || 0;
                                return 'Rp ' + num.toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            };

                            $('#detail_acc_kode').text(a.kode_akun || ('KB-' + a.id));
                            $('#detail_acc_nama').text(a.nama_akun);
                            $('#detail_acc_entitas').text(res.entitas_label || (a.branch ? a.branch.nama_cabang : 'PT Pusat'));

                            if (a.tipe === 'Kas Tunai') {
                                $('#detail_acc_tipe_badge').text(a.tipe).attr('class', 'badge rounded-pill font-monospace px-2.5 py-0.5 bg-success-subtle text-success border border-success-subtle');
                                $('#detail_acc_icon_box').css('background', 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)');
                                $('#detail_acc_icon').attr('class', 'bi bi-cash-stack fs-4');
                            } else if (a.tipe === 'Bank') {
                                $('#detail_acc_tipe_badge').text(a.tipe).attr('class', 'badge rounded-pill font-monospace px-2.5 py-0.5 bg-primary-subtle text-primary border border-primary-subtle');
                                $('#detail_acc_icon_box').css('background', 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)');
                                $('#detail_acc_icon').attr('class', 'bi bi-bank2 fs-4');
                            } else {
                                $('#detail_acc_tipe_badge').text(a.tipe).attr('class', 'badge rounded-pill font-monospace px-2.5 py-0.5 bg-purple-subtle text-purple border');
                                $('#detail_acc_icon_box').css('background', 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)');
                                $('#detail_acc_icon').attr('class', 'bi bi-wallet2 fs-4');
                            }

                            let saldoVal = parseFloat(a.saldo_berjalan) || 0;
                            $('#detail_acc_saldo_berjalan').text(formatIdr(saldoVal))
                                .attr('class', `fs-5 fw-bold font-monospace text-nowrap ${saldoVal >= 0 ? 'text-success' : 'text-danger'}`);
                            $('#detail_acc_saldo_awal').text(formatIdr(a.saldo_awal));
                            $('#detail_acc_total_masuk').text(formatIdr(s.total_masuk));
                            $('#detail_acc_total_keluar').text(formatIdr(s.total_keluar));

                            $('#detail_acc_tipe_val').text(a.tipe);
                            $('#detail_acc_nama_bank').text(a.nama_bank || (a.tipe === 'Kas Tunai' ? 'Kas Fisik Tunai' : '-'));
                            $('#detail_acc_no_rek').text(a.nomor_rekening || (a.tipe === 'Kas Tunai' ? 'Kas Tunai Fisik' : '-'));
                            $('#detail_acc_atas_nama').text(a.atas_nama || '-');

                            if (a.coa) {
                                $('#detail_acc_coa_badge').html(`<i class="bi bi-check-circle-fill me-1"></i> [${a.coa.code}] ${a.coa.name}`);
                            } else {
                                $('#detail_acc_coa_badge').html('<span class="text-muted fst-italic">Belum terhubung ke Akun COA</span>');
                            }
                            $('#detail_acc_deskripsi').text(a.deskripsi || 'Tidak ada catatan tambahan.');
                        } else {
                            $('#modalDetailAccount').modal('hide');
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal mengambil data rincian rekening.' });
                        }
                    },
                    error: function(xhr) {
                        $('#modalDetailAccount').modal('hide');
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat memuat rincian rekening.' });
                    }
                });
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

            // Realtime Character Limit untuk Tambah / Edit Rekening Kas Bank
            $(document).on('input keyup paste', '#formAddAccount input[name="nama_akun"], #formEditAccount input[name="nama_akun"]', function() {
                let val = $(this).val() || '';
                if (val.length > 40) {
                    $(this).val(val.substring(0, 40));
                    if (!Swal.isVisible()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Karakter Tercapai',
                            text: 'Nama Akun / Buku Kas tidak boleh lebih dari 40 karakter!',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#2563eb'
                        });
                    }
                }
            });

            $(document).on('input keyup paste', '#formAddAccount textarea[name="deskripsi"], #formEditAccount textarea[name="deskripsi"]', function() {
                let val = $(this).val() || '';
                if (val.length > 60) {
                    $(this).val(val.substring(0, 60));
                    if (!Swal.isVisible()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Batas Karakter Tercapai',
                            text: 'Deskripsi tidak boleh lebih dari 60 karakter!',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#2563eb'
                        });
                    }
                }
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

            // Reset Form & Select2 State when Modal Hidden
            $('#modalAddAccount').on('hidden.bs.modal', function() {
                $('#formAddAccount')[0].reset();
                $('#add_acc_coa_id').val('').trigger('change');
                $('#add_acc_lawan_coa_id').val('').trigger('change');
                $('#box_lawan_saldo_awal').hide();
                $('#add_acc_lawan_coa_id').prop('required', false);
                $('.bank-fields').hide();
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

            $('#modalCashIn').on('hidden.bs.modal show.bs.modal', function() {
                $('#formCashIn')[0].reset();
                $('#cash_in_preview_container').addClass('d-none');
                $('#cash_in_preview_img').attr('src', '#');
                $('#cash_in_lawan_coa_id').val('').trigger('change');
                $('#cash_in_kategori').val('').trigger('change');
                $('#cash_in_jumlah_count').text('0/20');
                $('#cash_in_nama_kontak_count').text('0/40');
                $('#cash_in_nomor_referensi_count').text('0/50');
                $('#cash_in_keterangan_count').text('0/40');
            });

            $('#modalCashOut').on('hidden.bs.modal show.bs.modal', function() {
                $('#formCashOut')[0].reset();
                $('#cash_out_preview_container').addClass('d-none');
                $('#cash_out_preview_img').attr('src', '#');
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

            // ─── LIVE PREVIEW BUKTI FOTO KAS MASUK (MAKS 10MB) ───
            $('#cash_in_lampiran').on('change', function() {
                const file = this.files[0];
                if (!file) {
                    $('#cash_in_preview_container').addClass('d-none');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Berkas Terlalu Besar',
                        text: 'Ukuran foto/berkas bukti maksimal 10 MB. Harap pilih berkas lain.',
                        confirmButtonColor: '#dc2626'
                    });
                    $(this).val('');
                    $('#cash_in_preview_container').addClass('d-none');
                    return;
                }

                const sizeKb = (file.size / 1024).toFixed(1);
                $('#cash_in_preview_filename').text(file.name);
                $('#cash_in_preview_size').text(`Ukuran Asli: ${sizeKb} KB`);

                if (file.type === 'application/pdf') {
                    $('#cash_in_preview_img').addClass('d-none');
                    $('#cash_in_preview_pdf_icon').removeClass('d-none');
                    $('#cash_in_preview_container').removeClass('d-none');
                } else if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#cash_in_preview_img').attr('src', e.target.result).removeClass('d-none');
                        $('#cash_in_preview_pdf_icon').addClass('d-none');
                        $('#cash_in_preview_container').removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#cash_in_preview_container').addClass('d-none');
                }
            });

            $('#btn_remove_cash_in_preview').on('click', function(e) {
                e.preventDefault();
                $('#cash_in_lampiran').val('');
                $('#cash_in_preview_container').addClass('d-none');
                $('#cash_in_preview_img').attr('src', '#');
            });

            // ─── LIVE PREVIEW BUKTI FOTO KAS KELUAR (MAKS 10MB) ───
            $('#cash_out_lampiran').on('change', function() {
                const file = this.files[0];
                if (!file) {
                    $('#cash_out_preview_container').addClass('d-none');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Berkas Terlalu Besar',
                        text: 'Ukuran kuitansi/nota bukti maksimal 10 MB. Harap pilih berkas lain.',
                        confirmButtonColor: '#dc2626'
                    });
                    $(this).val('');
                    $('#cash_out_preview_container').addClass('d-none');
                    return;
                }

                const sizeKb = (file.size / 1024).toFixed(1);
                $('#cash_out_preview_filename').text(file.name);
                $('#cash_out_preview_size').text(`Ukuran Asli: ${sizeKb} KB`);

                if (file.type === 'application/pdf') {
                    $('#cash_out_preview_img').addClass('d-none');
                    $('#cash_out_preview_pdf_icon').removeClass('d-none');
                    $('#cash_out_preview_container').removeClass('d-none');
                } else if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#cash_out_preview_img').attr('src', e.target.result).removeClass('d-none');
                        $('#cash_out_preview_pdf_icon').addClass('d-none');
                        $('#cash_out_preview_container').removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#cash_out_preview_container').addClass('d-none');
                }
            });

            $('#btn_remove_cash_out_preview').on('click', function(e) {
                e.preventDefault();
                $('#cash_out_lampiran').val('');
                $('#cash_out_preview_container').addClass('d-none');
                $('#cash_out_preview_img').attr('src', '#');
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

            // Submit: Kas Masuk (Cash In)
            $('#formCashIn').on('submit', function(e) {
                e.preventDefault();

                // Pastikan berkas bukti terlampir
                let fileInput = document.getElementById('cash_in_lampiran');
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bukti Foto Wajib Diunggah',
                        text: 'Harap lampirkan foto struk, slip setoran, atau bukti transfer penerimaan kas!',
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
                            $('#formCashIn')[0].reset();
                            $('#cash_in_preview_container').addClass('d-none');
                            $('#cash_in_preview_img').attr('src', '#');
                            $('#cash_in_lawan_coa_id').val('').trigger('change');
                            $('#cash_in_tender_id').val('').trigger('change');
                            executeLiveSync(true);
                            Swal.fire({ icon: 'success', title: 'Berhasil Disimpan', text: res.message, timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Masuk');
                        let errMsg = xhr.responseJSON ? (xhr.responseJSON.message || 'Terjadi kesalahan sistem.') : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan Kas Masuk', text: errMsg });
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

            $('#transfer_from_select, #transfer_to_select').on('change select2:select select2:clear', validateTransferBalance);

            // ─── Real-time Batasan & Counter: Nominal Transfer (Max 20 Kar) & Keterangan (Max 40 Kar) ───
            $('#transfer_nominal_input').on('input keyup change', function() {
                let val = $(this).val();
                if (val.length > 20) {
                    val = val.substring(0, 20);
                    $(this).val(val);
                }
                let len = val.length;
                $('#transfer_nominal_counter').text(len + '/20 kar');
                if (len >= 20) {
                    $('#transfer_nominal_counter').addClass('text-danger fw-bold').removeClass('text-muted');
                } else {
                    $('#transfer_nominal_counter').removeClass('text-danger fw-bold').addClass('text-muted');
                }
                validateTransferBalance();
            });

            $('#transfer_keterangan_input').on('input keyup change', function() {
                let val = $(this).val();
                if (val.length > 40) {
                    val = val.substring(0, 40);
                    $(this).val(val);
                }
                let len = val.length;
                $('#transfer_keterangan_counter').text(len + '/40 karakter');
                if (len >= 40) {
                    $('#transfer_keterangan_counter').addClass('text-danger fw-bold').removeClass('text-muted');
                } else {
                    $('#transfer_keterangan_counter').removeClass('text-danger fw-bold').addClass('text-muted');
                }
            });

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
                            $('#formCashOut')[0].reset();
                            $('#cash_out_preview_container').addClass('d-none');
                            $('#cash_out_preview_img').attr('src', '#');
                            $('#cash_out_lawan_coa_id').val('').trigger('change');
                            $('#cash_out_tender_id').val('').trigger('change');
                            $('#cash_out_saldo_warning').hide();
                            $('#cash_out_nominal_input').removeClass('is-invalid border-danger');
                            executeLiveSync(true);
                            Swal.fire({ icon: 'success', title: 'Berhasil Disimpan', text: res.message, timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-6"></i> Simpan Kas Keluar');
                        let errMsg = xhr.responseJSON ? (xhr.responseJSON.message || 'Terjadi kesalahan sistem.') : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan Kas Keluar', text: errMsg });
                    }
                });
            });

            // Submit: Transfer Antar Rekening (Keamanan Tinggi & Validasi Ketat)
            $('#formTransfer').on('submit', function(e) {
                e.preventDefault();

                let fromId = $('#transfer_from_select').val();
                let toId   = $('#transfer_to_select').val();

                if (!fromId) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan pilih Rekening Asal terlebih dahulu!' });
                    return false;
                }
                if (!toId) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan pilih Rekening Tujuan terlebih dahulu!' });
                    return false;
                }
                if (fromId === toId) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Rekening asal dan rekening tujuan tidak boleh sama!' });
                    return false;
                }

                let rawNominal = $('#transfer_nominal_input').val().trim();
                let cleanDigits = rawNominal.replace(/[^0-9]/g, '');
                if (!cleanDigits || parseFloat(cleanDigits) <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Tidak Valid',
                        text: 'Nominal transfer wajib diisi dan harus lebih besar dari Rp 0!',
                        confirmButtonColor: '#0284c7'
                    });
                    return false;
                }

                if (rawNominal.length > 20 || cleanDigits.length > 20) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Terlalu Panjang',
                        text: 'Nominal transfer maksimal 20 karakter!',
                        confirmButtonColor: '#0284c7'
                    });
                    return false;
                }

                let ket = $('#transfer_keterangan_input').val() ? $('#transfer_keterangan_input').val().trim() : '';
                if (ket.length > 40) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Keterangan Terlalu Panjang',
                        text: 'Keterangan transfer maksimal 40 karakter!',
                        confirmButtonColor: '#0284c7'
                    });
                    return false;
                }

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
                let origText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses Transfer...');

                $.ajax({
                    url: "{{ route('accounting.kas-bank.transfer.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html(origText);
                        if (res.success) {
                            $('#modalTransfer').modal('hide');
                            $('#formTransfer')[0].reset();
                            $('#transfer_from_select').val('').trigger('change');
                            $('#transfer_to_select').val('').trigger('change');
                            $('#transfer_nominal_counter').text('0/20 kar').removeClass('text-danger fw-bold').addClass('text-muted');
                            $('#transfer_keterangan_counter').text('0/40 karakter').removeClass('text-danger fw-bold').addClass('text-muted');
                            executeLiveSync(true);
                            Swal.fire({ icon: 'success', title: 'Transfer Berhasil Diproses', text: res.message, timer: 1400, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(origText);
                        let errMsg = xhr.responseJSON ? (xhr.responseJSON.message || 'Terjadi kesalahan sistem.') : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Gagal Memproses Transfer', text: errMsg });
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

                        // ─── TAMPILKAN BUKTI TRANSAKSI (FOTO / NOTA / PDF) ───
                        if (res.lampiran_url) {
                            $('#detail_lampiran_container').removeClass('d-none');
                            $('#detail_lampiran_download_btn').attr('href', res.lampiran_url);
                            let isPdf = res.lampiran_url.toLowerCase().split('?')[0].endsWith('.pdf');
                            if (isPdf) {
                                $('#detail_lampiran_preview_img').addClass('d-none');
                                $('#detail_lampiran_preview_pdf').removeClass('d-none');
                            } else {
                                $('#detail_lampiran_preview_pdf').addClass('d-none');
                                $('#detail_lampiran_preview_img').removeClass('d-none').attr('src', res.lampiran_url);
                            }

                            // Bind context data untuk tombol perbesar bukti & foto klik
                            let previewPayload = {
                                url: res.lampiran_url,
                                nomor: m.nomor_transaksi || '-',
                                nominal: 'Rp ' + formatCurrency(m.jumlah),
                                tipe: m.tipe,
                                kategori: m.kategori || '-',
                                akun: m.kas_bank_account ? m.kas_bank_account.nama_rekening : '-'
                            };
                            $('#detail_lampiran_preview_btn').data(previewPayload);
                            $('#detail_lampiran_preview_img').data(previewPayload);
                            $('#detail_lampiran_preview_pdf').data(previewPayload);
                        } else {
                            $('#detail_lampiran_container').addClass('d-none');
                        }

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
                        executeLiveSync(true);
                        Swal.fire({ icon: 'success', title: 'Berhasil Dibatalkan', text: res.message, timer: 1200, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        $btn.prop('disabled', false).html(origText);
                    }
                }).fail(function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.' });
                    $btn.prop('disabled', false).html(origText);
                });
            });

            // ─── MODAL PREVIEW BUKTI TRANSAKSI MODEREN KAS & BANK ───
            window.openPreviewBuktiKasBank = function(data) {
                if (!data || !data.url) return;

                let isPdf = data.url.toLowerCase().split('?')[0].endsWith('.pdf');
                
                $('#previewKbNomor').text(data.nomor || '-');
                $('#previewKbNominal').text(data.nominal || '-');
                $('#previewKbKategori').text(data.kategori || '-');
                $('#previewKbAkun').text(data.akun || '-');
                $('#previewKbDownload').attr('href', data.url);

                let badge = $('#previewKbTipeBadge');
                if (data.tipe === 'Masuk') {
                    badge.removeClass('bg-danger-subtle text-danger bg-info-subtle text-info')
                         .addClass('bg-success-subtle text-success')
                         .html('<i class="bi bi-arrow-down-left me-1"></i>Kas Masuk');
                } else if (data.tipe === 'Keluar') {
                    badge.removeClass('bg-success-subtle text-success bg-info-subtle text-info')
                         .addClass('bg-danger-subtle text-danger')
                         .html('<i class="bi bi-arrow-up-right me-1"></i>Kas Keluar');
                } else {
                    badge.removeClass('bg-success-subtle text-success bg-danger-subtle text-danger')
                         .addClass('bg-info-subtle text-info')
                         .html('<i class="bi bi-arrow-left-right me-1"></i>Transfer');
                }

                if (isPdf) {
                    $('#previewKbImg').addClass('d-none').attr('src', '');
                    $('#previewKbPdfPlaceholder').addClass('d-none');
                    $('#previewKbPdfIframe').removeClass('d-none').attr('src', data.url);
                } else {
                    $('#previewKbPdfIframe').addClass('d-none').attr('src', '');
                    $('#previewKbPdfPlaceholder').addClass('d-none');
                    $('#previewKbImg').removeClass('d-none').attr('src', data.url);
                }

                $('#modalPreviewBuktiKasBank').modal('show');
            };

            // Event listener klik tombol bukti di tabel mutasi & aksi
            $(document).on('click', '.btn-preview-bukti-kb', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let $btn = $(this);
                window.openPreviewBuktiKasBank({
                    url: $btn.data('url'),
                    nomor: $btn.data('nomor'),
                    nominal: $btn.data('nominal'),
                    tipe: $btn.data('tipe'),
                    kategori: $btn.data('kategori'),
                    akun: $btn.data('akun')
                });
            });

            // Event listener klik tombol atau foto di Modal Detail
            $(document).on('click', '#detail_lampiran_preview_btn, #detail_lampiran_preview_img, #detail_lampiran_preview_pdf', function(e) {
                e.preventDefault();
                let d = $(this).data();
                if (d && d.url) {
                    window.openPreviewBuktiKasBank(d);
                }
            });

            // Cleanup saat modal preview ditutup & jaga scrollbar jika ditumpuk di atas modal detail / card mutasi
            $('#modalPreviewBuktiKasBank').on('hidden.bs.modal', function() {
                $('#previewKbImg').addClass('d-none').attr('src', '');
                $('#previewKbPdfIframe').addClass('d-none').attr('src', '');
                if ($('#modalDetailMutasi').hasClass('show') || $('#modalCardMutasi').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });

            // ─── Klik Kartu Rekening: Buka Modal Mutasi Rekening ───
            let tableCardMutasi = null;

            $(document).on('click', '.kb-clickable-card', function(e) {
                if ($(e.target).closest('.btn-edit-account, .btn-detail-account').length) {
                    return; // Jangan buka modal mutasi jika klik tombol edit atau detail
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
                $('#cardMutasiSaldoLabel').text('SALDO BERJALAN SAAT INI');
                $('#cardMutasiSaldo').removeClass('text-danger text-info text-warning text-primary').addClass('text-success').text(accSaldo);
                $('#cardMutasiTipe').text(accTipe);
                $('#cardMutasiEntitas').html(`<i class="bi bi-building me-1"></i> ${accEntitas}`);
                $('#cardMutasiCoa').text(accCoa);
                $('#cardMutasiNoRek').text(accRek !== '-' ? accRek : 'KAS FISIK');
                $('#cardMutasiAtasNama').html(accAtasNama !== '-' ? `<span class="fw-semibold text-body-secondary"><i class="bi bi-person me-1"></i>a/n ${accAtasNama}</span>` : '<span class="fst-italic text-muted">-</span>');
                $('#cardMutasiLedgerLink').attr('href', `/accounting/kas-bank/ledger?account_id=${accId}`);

                // Reset filter tipe ke "Semua"
                $('.btn-quick-tipe').removeClass('active');
                $('.btn-quick-tipe[data-tipe=""]').addClass('active');
                $('#card_mutasi_filter_tipe').val('');

                // Dynamic Bank Icon & Color Theme
                let lowerNama = (accNama + ' ' + accTipe).toLowerCase();
                let $iconBadge = $('#cardMutasiIconBadge');
                let $icon = $('#cardMutasiIcon');

                if (lowerNama.includes('mandiri')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)');
                    $icon.attr('class', 'bi bi-bank2 fs-4');
                } else if (lowerNama.includes('bca')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)');
                    $icon.attr('class', 'bi bi-bank fs-4');
                } else if (lowerNama.includes('bri')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #0284c7 0%, #0f172a 100%)');
                    $icon.attr('class', 'bi bi-bank fs-4');
                } else if (lowerNama.includes('bni')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #f97316 0%, #c2410c 100%)');
                    $icon.attr('class', 'bi bi-bank2 fs-4');
                } else if (accTipe === 'Kas Tunai' || lowerNama.includes('kas')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)');
                    $icon.attr('class', 'bi bi-cash-coin fs-4');
                } else if (accTipe === 'E-Wallet / Giro' || lowerNama.includes('wallet') || lowerNama.includes('giro')) {
                    $iconBadge.css('background', 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)');
                    $icon.attr('class', 'bi bi-wallet2 fs-4');
                } else {
                    $iconBadge.css('background', 'linear-gradient(135deg, #475569 0%, #334155 100%)');
                    $icon.attr('class', 'bi bi-credit-card-2-front fs-4');
                }

                $('#modalCardMutasi').modal('show');
            });

            // Salin Nomor Rekening
            $('#btnCopyRekening').on('click', function() {
                let rek = $('#cardMutasiNoRek').text().trim();
                if (rek && rek !== '-' && rek !== 'KAS FISIK') {
                    navigator.clipboard.writeText(rek).then(function() {
                        let $icon = $('#btnCopyRekeningIcon');
                        $icon.removeClass('bi-clipboard').addClass('bi-check2');
                        setTimeout(() => {
                            $icon.removeClass('bi-check2').addClass('bi-clipboard');
                        }, 1500);

                        Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        }).fire({
                            icon: 'success',
                            title: `No. Rekening ${rek} berhasil disalin!`
                        });
                    });
                }
            });

            // Quick Filter Pills (Semua, Masuk, Keluar, Transfer)
            $(document).on('click', '.btn-quick-tipe', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $('.btn-quick-tipe').removeClass('active');
                $(this).addClass('active');

                let tipe = $(this).attr('data-tipe');
                if (tipe === undefined || tipe === null) {
                    tipe = $(this).data('tipe') || '';
                }
                $('#card_mutasi_filter_tipe').val(tipe);
                updateCardFilterBadge();

                if (tableCardMutasi && typeof tableCardMutasi.ajax !== 'undefined') {
                    tableCardMutasi.ajax.reload(null, true);
                } else if ($.fn.DataTable.isDataTable('#tableCardMutasi')) {
                    $('#tableCardMutasi').DataTable().ajax.reload(null, true);
                }
            });

            $('#modalCardMutasi').on('shown.bs.modal', function() {
                if (!tableCardMutasi || !$.fn.DataTable.isDataTable('#tableCardMutasi')) {
                    tableCardMutasi = $('#tableCardMutasi').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                        dom: "<'row px-3.5 py-2.5 align-items-center g-2 kb-dt-toolbar'<'col-12 col-md-6 d-flex align-items-center gap-2'l><'col-12 col-md-6 d-flex justify-content-md-end'f>><'table-responsive w-100 kb-dt-scroll'tr><'d-flex justify-content-between align-items-center px-3.5 px-md-4 py-2.5 border-top bg-body-tertiary w-100 flex-wrap gap-2'<'text-muted small font-monospace'i><'ms-auto d-flex justify-content-end'p>>",
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
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-monospace small text-muted' },
                            { data: 'nomor_transaksi', name: 'nomor_transaksi' },
                            { data: 'tanggal', name: 'tanggal' },
                            { data: 'pihak_ketiga', name: 'nama_kontak' },
                            { data: 'jumlah', name: 'jumlah', className: 'text-end' },
                            { data: 'saldo_sesudah', name: 'saldo_sesudah', className: 'text-end font-monospace' },
                            { data: 'status', name: 'status', className: 'text-center' },
                            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                        ],
                        order: [[2, 'desc']],
                        language: {
                            search: "Cari Mutasi:",
                            searchPlaceholder: "Nomor, pihak kontak, keterangan...",
                            lengthMenu: "_MENU_ data per hal",
                            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ mutasi",
                            paginate: { first: "Awal", last: "Akhir", next: "&raquo;", previous: "&laquo;" },
                            emptyTable: "Belum ada riwayat mutasi untuk rekening ini.",
                            processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>'
                        }
                    });

                    // Live Update Saldo & Label Mengikuti Hasil Filter
                    tableCardMutasi.on('xhr.dt', function(e, settings, json) {
                        if (json && json.filter_summary) {
                            $('#cardMutasiSaldoLabel').text(json.filter_summary.label);
                            $('#cardMutasiSaldo')
                                .removeClass('text-success text-danger text-info text-warning text-primary')
                                .addClass(json.filter_summary.color)
                                .text(json.filter_summary.value);
                        }
                    });
                } else {
                    tableCardMutasi.ajax.reload(null, true);
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
                }
            });

            function updateCardFilterBadge() {
                let count = 0;
                if ($('#card_mutasi_filter_tipe').val()) count++;
                if ($('#card_mutasi_start_date').val() || $('#card_mutasi_end_date').val()) count++;

                if (count > 0) {
                    $('#card_filter_count_badge').text(`${count} Filter`).removeClass('d-none');
                    $('#btn_card_mutasi_reset').removeClass('d-none').addClass('d-flex');
                } else {
                    $('#card_filter_count_badge').addClass('d-none');
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
                $('.btn-quick-tipe').removeClass('active');
                $('.btn-quick-tipe[data-tipe=""]').addClass('active');
                updateCardFilterBadge();
                if (tableCardMutasi) tableCardMutasi.ajax.reload();
                $('#modalFilterMutasiCard').modal('hide');
            });

            // =========================================================================
            // ─── BACKGROUND REAL-TIME LIVE SYNC (< 1 DETIK / 800MS) ──────────────────
            // =========================================================================
            let liveFingerprint = null;
            let isLiveSyncing = false;
            const LIVE_SYNC_INTERVAL_MS = 2500; // Polling cerdas 2.5 detik (Optimal untuk skala jutaan data)

            function executeLiveSync(force = false) {
                // Jangan kirim request jika tab browser sedang tidak aktif/diminimize (Hemat ribuan query)
                if (document.hidden && !force) return;
                if (isLiveSyncing && !force) return;
                isLiveSyncing = true;

                let dataParams = {};
                if (liveFingerprint && !force) {
                    dataParams.fingerprint = liveFingerprint;
                }

                $.ajax({
                    url: "{{ route('accounting.kas-bank.live-sync') }}",
                    type: 'GET',
                    data: dataParams,
                    cache: false,
                    success: function(res) {
                        isLiveSyncing = false;
                        if (!res) return;

                        if (res.fingerprint) {
                            liveFingerprint = res.fingerprint;
                        }

                        if (res.changed && res.stats && res.accounts) {
                            // 1. Update 4 Metrik Kartu Ringkasan Keuangan (Bento Cards)
                            $('#stat_total_kas').text(res.stats.total_kas).addClass('kb-live-updated');
                            $('#stat_total_bank').text(res.stats.total_bank).addClass('kb-live-updated');
                            $('#stat_cash_in').text(res.stats.cash_in).addClass('kb-live-updated');
                            $('#stat_cash_out').text(res.stats.cash_out).addClass('kb-live-updated');
                            $('#badge_total_semua').text('Total: ' + res.stats.total_semua).addClass('kb-live-updated');
                            $('#stat_sub_kas').text(res.stats.kas_count + ' Akun Kas Tunai Aktif');
                            $('#stat_sub_bank').text(res.stats.bank_count + ' Rekening Bank Terhubung');

                            // 2. Update ATM Cards, Master Table, dan Dropdown Pilihan di Modal
                            res.accounts.forEach(function(acc) {
                                // Update Saldo pada Kartu ATM
                                let $cardSaldo = $(`#card_saldo_${acc.id}`);
                                if ($cardSaldo.length && $cardSaldo.text().trim() !== acc.formatted_saldo) {
                                    $cardSaldo.text(acc.formatted_saldo).addClass('kb-live-updated');
                                }
                                let $cardContainer = $(`#card_container_${acc.id}`);
                                if ($cardContainer.length) {
                                    $cardContainer.attr('data-saldo', acc.formatted_saldo);
                                    $cardContainer.data('saldo', acc.formatted_saldo);
                                }

                                // Update Saldo pada Tabel Master Rekening
                                let $tableSaldo = $(`#table_saldo_${acc.id}`);
                                if ($tableSaldo.length && $tableSaldo.text().trim() !== acc.formatted_saldo) {
                                    $tableSaldo.text(acc.formatted_saldo).addClass('kb-live-updated');
                                    if (acc.raw_saldo > 0) {
                                        $tableSaldo.removeClass('text-danger').addClass('text-success');
                                    } else {
                                        $tableSaldo.removeClass('text-success').addClass('text-danger');
                                    }
                                }

                                // Update atribut data-saldo pada opsi select Kas Masuk, Kas Keluar & Transfer
                                let optText = `[${acc.entitas_label}] ${acc.nama_akun} (Saldo: ${acc.formatted_saldo})`;
                                $(`#cash_in_kas_bank_id option[value="${acc.id}"]`).text(optText);
                                $(`#cash_out_kas_bank_id option[value="${acc.id}"]`).attr('data-saldo', acc.raw_saldo).data('saldo', acc.raw_saldo).text(optText);
                                $(`#transfer_from_select option[value="${acc.id}"]`).attr('data-saldo', acc.raw_saldo).data('saldo', acc.raw_saldo).text(optText);
                                $(`#transfer_to_select option[value="${acc.id}"]`).attr('data-saldo', acc.raw_saldo).data('saldo', acc.raw_saldo).text(optText);
                            });

                            // 3. Jika Sub-Modal Mutasi Rekening sedang terbuka, update juga saldonya & reload tabel mutasinya secara instan
                            if ($('#modalCardMutasi').hasClass('show')) {
                                let activeAccId = $('#card_mutasi_acc_id').val();
                                let activeAcc = res.accounts.find(a => a.id == activeAccId);
                                if (activeAcc) {
                                    $('#cardMutasiSaldo').text(activeAcc.formatted_saldo).addClass('kb-live-updated');
                                }
                                if (tableCardMutasi) {
                                    tableCardMutasi.ajax.reload(null, false);
                                }
                            }

                            // Bersihkan efek animasi glow
                            setTimeout(function() {
                                $('.kb-live-updated').removeClass('kb-live-updated');
                            }, 750);
                        }
                    },
                    error: function() {
                        isLiveSyncing = false;
                    }
                });
            }

            // Jalankan polling real-time secara berkelanjutan di latar belakang setiap 2.5 detik
            setInterval(function() {
                executeLiveSync(false);
            }, LIVE_SYNC_INTERVAL_MS);

            // Saat pengguna membuka kembali tab browser setelah diminimize, langsung sinkronkan seketika
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    executeLiveSync(true);
                }
            });

            // Inisialisasi sinkronisasi awal saat halaman selesai dimuat
            setTimeout(function() {
                executeLiveSync(true);
            }, 100);

        });
    </script>
    @endpush
</x-layouts>
