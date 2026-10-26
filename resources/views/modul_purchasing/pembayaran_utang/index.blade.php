<x-layouts title="Pembayaran Utang Supplier (Purchase Payment)">

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- Modul Pembayaran Utang CSS -->
<link rel="stylesheet" href="{{ asset('css/utang.css') }}?v={{ file_exists(public_path('css/utang.css')) ? filemtime(public_path('css/utang.css')) : '1.0' }}">
@endpush

<div class="container-fluid px-1 px-sm-2 px-md-4 py-2 py-md-3">

    <!-- Page Header & Action Button -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 mb-md-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                <span class="p-1.5 p-md-2 rounded-3 text-white d-inline-flex shadow-sm flex-shrink-0" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
                    <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                </span>
                <span>Pembayaran Utang Supplier</span>
            </h4>
        </div>
        
        <!-- Tombol Tambah Data Compact Mepet ke Kanan -->
        @can('tambah-pembayaran-utang')
        <div class="header-action-btn-wrapper ms-md-auto">
            <button type="button" class="btn btn-primary text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2 text-nowrap" id="btnOpenModalCreate" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                <i class="bi bi-plus-circle-fill fs-6"></i>
                <span>Buat Pembayaran Utang</span>
            </button>
        </div>
        @endcan
    </div>

    <!-- Navigation Tabs: Switcher antara Pembayaran dan Rekap Utang -->
    <div class="mb-4">
        <div class="nav-cinematic">
            <a href="{{ route('purchase-payments.index') }}" class="nav-link active">
                <i class="bi bi-credit-card-2-front-fill flex-shrink-0"></i>
                <span class="nav-label-full">Riwayat Pembayaran Utang</span>
                <span class="nav-label-short">Pembayaran</span>
            </a>
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('lihat-hutang'))
            <a href="{{ route('modul_purchasing.hutang.index') }}" class="nav-link">
                <i class="bi bi-wallet2 flex-shrink-0"></i>
                <span class="nav-label-full">Rekap Sisa Tagihan &amp; Utang Supplier</span>
                <span class="nav-label-short">Rekap Utang</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Cinematic Bento Glow Stat Cards (Full Width Stack on Mobile, 4 Cols on Desktop) -->
    <div class="row g-3 gy-3 gy-md-4 mb-4">
        <!-- Card 1: Pembayaran Bulan Ini (Blue) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-blue position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        PEMBAYARAN BULAN INI
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="kpiMonthlyIdr" class="fw-bold font-monospace text-white fs-6 text-nowrap">Rp {{ number_format($totalPaymentIdrThisMonth, 2, ',', '.') }}</span>
                            <span class="badge bg-white bg-opacity-25 text-white font-monospace" style="font-size: 0.68rem;">IDR</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="kpiMonthlyUsd" class="fw-bold font-monospace text-warning fs-6 text-nowrap">$ {{ number_format($totalPaymentUsdThisMonth, 2, ',', '.') }}</span>
                            <span class="badge bg-warning text-dark fw-bold font-monospace" style="font-size: 0.68rem;">USD</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1.5 pt-1 border-top border-white border-opacity-20" style="font-size: 0.72rem;">
                        Est. Realisasi: <strong id="kpiMonthlyEquiv">≈ Rp {{ number_format($totalPaymentEquivalentIdrThisMonth, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Utang Beredar (Green) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-green position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        TOTAL UTANG BEREDAR
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-exclamation-octagon"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="kpiOutstandingIdr" class="fw-bold font-monospace text-white fs-6 text-nowrap">Rp {{ number_format($totalOutstandingApIdr, 2, ',', '.') }}</span>
                            <span class="badge bg-white bg-opacity-25 text-white font-monospace" style="font-size: 0.68rem;">IDR</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span id="kpiOutstandingUsd" class="fw-bold font-monospace text-warning fs-6 text-nowrap">$ {{ number_format($totalOutstandingApUsd, 2, ',', '.') }}</span>
                            <span class="badge bg-warning text-dark fw-bold font-monospace" style="font-size: 0.68rem;">USD</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1.5 pt-1 border-top border-white border-opacity-20" style="font-size: 0.72rem;">
                        Est. Total: <strong id="kpiOutstandingEquiv">≈ Rp {{ number_format($totalOutstandingApEquivalentIdr, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Faktur Lewat Tempo (Purple) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-purple position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        LEWAT TEMPO
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div id="kpiOverdueCount" class="fw-bold font-monospace text-white pay-cine-value text-nowrap">
                        {{ $totalOverdueCount }} <span class="fs-6 fw-normal text-white text-opacity-75">Faktur</span>
                    </div>
                    <div id="kpiOverdueSub" class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1">
                        @if($totalOverdueCount > 0)
                            @if($overdueIdrAmount > 0 && $overdueUsdAmount > 0)
                                Rp {{ number_format($overdueIdrAmount, 2, ',', '.') }} + ${{ number_format($overdueUsdAmount, 2, ',', '.') }}
                            @elseif($overdueIdrAmount > 0)
                                Rp {{ number_format($overdueIdrAmount, 2, ',', '.') }} (IDR)
                            @elseif($overdueUsdAmount > 0)
                                ${{ number_format($overdueUsdAmount, 2, ',', '.') }} (USD)
                            @endif
                        @else
                            Semua jatuh tempo aman
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Transaksi Bayar (Amber) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 pay-cinematic-card pay-cine-amber position-relative overflow-hidden text-white d-flex flex-column justify-content-between h-100 shadow-sm">
                <div class="pay-cine-circle"></div>
                <div class="d-flex justify-content-between align-items-center mb-2 z-1">
                    <span class="text-white text-opacity-85 fw-bold text-uppercase pay-cine-title">
                        TOTAL TRANSAKSI
                    </span>
                    <div class="pay-cine-icon shadow-sm">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div class="z-1 mt-auto">
                    <div id="kpiTotalPaymentCount" class="fw-bold font-monospace text-white pay-cine-value text-nowrap">
                        {{ $totalPaymentCount }} <span class="fs-6 fw-normal text-white text-opacity-75">Voucher</span>
                    </div>
                    <div id="kpiTotalPaymentSub" class="text-white text-opacity-85 font-monospace pay-cine-sub text-truncate mt-1">
                        {{ $totalPostedCount }} Diposting • {{ $totalPendingCount }} Diajukan • {{ $totalDraftCount }} Draft
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card with Search & Filter Modal Trigger -->
    <div class="card border-0 shadow-sm rounded-3 rounded-md-4 overflow-hidden mb-4 w-100">
        <div class="card-header border-bottom p-3 p-md-3.5">
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2.5 gap-md-3">
                <!-- Left: Search Box -->
                <div class="input-group input-group-sm flex-grow-1" style="max-width: 420px;">
                    <span class="input-group-text border-end-0 py-2 px-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="filterSearch" class="form-control border-start-0 py-2" placeholder="Cari No. Bayar / Supplier / Ref / Tender...">
                </div>

                <!-- Right: Filter Modal Button & Reset Button -->
                <div class="d-flex align-items-center gap-2 pt-1 pt-sm-0 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold d-none" id="btnResetFilters" title="Reset Semua Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset Filter
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterPayments">
                        <i class="bi bi-funnel-fill text-primary"></i>
                        <span>Filter Data</span>
                        <span class="badge bg-primary text-white rounded-pill ms-1 d-none" id="activeFilterBadge">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Filter Indicator Banner (appears only when filters are active) -->
        <div id="activeFilterInfoBanner" class="px-3 py-1.5 bg-body-tertiary border-bottom small d-none d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-1.5 flex-wrap" id="activeFilterBadgesContainer">
                <!-- Populated dynamically via JS -->
            </div>
            <a href="javascript:void(0)" class="text-danger small fw-semibold text-decoration-none" id="btnClearAllFilterChips">
                <i class="bi bi-trash me-1"></i>Hapus Filter
            </a>
        </div>

        <!-- DataTable Container with Wide Table and Smooth Horizontal Scroll -->
        <div class="card-body p-0">
            <!-- Top Synchronized Scrollbar Track (Visible for Direct Mobile Touch & Drag) -->
            <div class="pay-top-scroll-container w-100" id="payTopScrollContainer" title="Geser untuk melihat kolom tabel lainnya">
                <div id="payTopScrollDummy" style="height: 11px; min-width: 1200px; width: 1200px;"></div>
            </div>

            <div class="table-responsive pay-table-scroll-wrapper">
                <table class="table table-hover align-middle mb-0 text-nowrap pay-wide-table" id="tablePayments" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th class="text-center text-nowrap" style="width: 45px;">#</th>
                            <th class="text-nowrap" style="min-width: 155px;">No. Pembayaran</th>
                            <th class="text-nowrap" style="min-width: 110px;">Tanggal</th>
                            <th class="text-nowrap" style="min-width: 180px;">Supplier</th>
                            <th class="text-nowrap" style="min-width: 160px;">Akun Kas / Bank</th>
                            <th class="text-nowrap" style="min-width: 140px;">Metode Bayar</th>
                            <th class="text-end text-nowrap" style="min-width: 140px;">Total Bayar</th>
                            <th class="text-end text-nowrap" style="min-width: 120px;">Potongan</th>
                            <th class="text-center text-nowrap" style="min-width: 110px;">Status</th>
                            <th class="text-center text-nowrap" style="min-width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modular Modal Partials -->
@include('modul_purchasing.pembayaran_utang.partials.modal_filter')
@can('tambah-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_create')
@include('modul_purchasing.pembayaran_utang.partials.modal_lookup_invoice')
@endcan
@if(auth()->user()->can('tambah-pembayaran-utang') || auth()->user()->can('edit-pembayaran-utang') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
@include('modul_purchasing.pembayaran_utang.partials.modal_camera')
@endif
@can('edit-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_edit')
@endcan
@can('detail-pembayaran-utang')
@include('modul_purchasing.pembayaran_utang.partials.modal_view')
@endcan
@include('modul_purchasing.pembayaran_utang.partials.modal_preview_image')

@push('scripts')
    <!-- Select2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Pass permission flags ke JavaScript --}}
    <script>
        window.payPermissions = @json($permissions);
    </script>

    <!-- Modular JavaScript Files -->
    @include('modul_purchasing.pembayaran_utang.scripts.index_js')
    @can('tambah-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.create_js')
    @endcan
    @if(auth()->user()->can('tambah-pembayaran-utang') || auth()->user()->can('edit-pembayaran-utang') || auth()->user()->hasAnyRole(['Super Admin', 'Owner']))
    @include('modul_purchasing.pembayaran_utang.scripts.camera_js')
    @endif
    @can('edit-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.edit_js')
    @endcan
    @can('detail-pembayaran-utang')
    @include('modul_purchasing.pembayaran_utang.scripts.view_js')
    @endcan
@endpush

</x-layouts>
