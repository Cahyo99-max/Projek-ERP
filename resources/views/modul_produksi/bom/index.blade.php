<x-layouts title="Master BOM (Bill of Materials) - Resep Produksi Garmen">
@push('styles')
<link rel="stylesheet" href="{{ asset('css/bom_produksi.css') }}?v={{ time() }}">
@endpush
<div class="container-fluid px-2 px-md-4 py-2 py-md-3">
    {{-- CINEMATIC HERO HEADER SECTION (ADAPTIVE LIGHT & DARK) --}}
    <div class="bom-hero-header card border-0 rounded-4 mb-3 mb-md-4 position-relative overflow-hidden">
        <!-- Ambient Glowing Background Lights -->
        <div class="bom-hero-glow-1 position-absolute" style="right: -30px; top: -30px; width: 220px; height: 220px; border-radius: 50%; pointer-events: none; filter: blur(20px);"></div>
        <div class="bom-hero-glow-2 position-absolute" style="left: 15%; bottom: -40px; width: 180px; height: 180px; border-radius: 50%; pointer-events: none; filter: blur(25px);"></div>

        <div class="card-body p-3 p-md-3.5 position-relative" style="z-index: 2;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2.5 gap-md-3">
                <div>
                    {{-- Frosted Breadcrumb Tag --}}
                    <div class="hero-breadcrumb d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill mb-2 shadow-xs">
                        <a href="{{ url('/dashbord') }}" class="text-decoration-none" style="font-size: 11px; color: inherit;"><i class="bi bi-house-door-fill me-1"></i>Dashboard</a>
                        <span class="opacity-50" style="font-size: 10px;">/</span>
                        <span style="font-size: 11px; color: inherit;">Produksi</span>
                        <span class="opacity-50" style="font-size: 10px;">/</span>
                        <span class="text-primary fw-bold" style="font-size: 11px;">Master BOM</span>
                    </div>

                    {{-- Title with 3D Icon & Tag --}}
                    <div class="d-flex align-items-center gap-2.5 flex-wrap">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                             style="width: 38px; height: 38px; background: linear-gradient(135deg, #2563eb 0%, #0284c7 100%); box-shadow: 0 6px 16px -2px rgba(37, 99, 235, 0.5);">
                            <i class="bi bi-layers-fill text-white fs-5"></i>
                        </div>
                        <div>
                            <h3 class="fw-extrabold mb-0 hero-title" style="font-size: 1.45rem; letter-spacing: -0.3px;">
                                Master BOM <span class="fw-semibold text-primary" style="font-size: 1.05rem;">(Bill of Materials)</span>
                            </h3>
                            <p class="text-theme-muted small mb-0 d-none d-sm-block mt-0.5" style="font-size: 12px;">
                                Pengelolaan resep komposisi bahan baku, aksesoris jahit, waste %, dan estimasi HPP garmen.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Button with Glowing Gradient --}}
                <div class="mt-2 mt-md-0 flex-shrink-0">
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('tambah-bom'))
                        <button type="button" class="btn btn-hero-create-bom d-inline-flex align-items-center justify-content-center gap-2 rounded-pill px-3.5 py-2 fw-bold text-white shadow-md text-nowrap" 
                                onclick="bukaModalTambahBom()" style="font-size: 13.5px;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 22px; height: 22px; background: rgba(255,255,255,0.25);">
                                <i class="bi bi-plus-lg text-white" style="font-size: 12px;"></i>
                            </div>
                            <span style="letter-spacing: 0.2px;">+ Buat Resep BOM Baru</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $currentYearMonth = date('Y-m');
        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $curBulanName = $bulanIndo[(int)date('n')] . ' ' . date('Y');
    @endphp

    {{-- BARIS FILTER PERIODE BULAN (UNIFIED, NEAT, NO TEXT WRAP) --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-3">
        {{-- Sisi Kiri: Indikator Ringkasan Periode --}}
        <div class="d-flex align-items-center gap-2 text-nowrap">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs text-nowrap" style="font-size: 12px;">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Ringkasan Periode:</span>
                <span class="text-primary" id="lblPeriodeAktifBom">{{ $curBulanName }}</span>
            </span>
        </div>
        
        {{-- Sisi Kanan: Stepper Bulan Interaktif + Tombol Reset --}}
        <div class="d-flex align-items-center gap-2 justify-content-between justify-content-sm-end text-nowrap flex-nowrap">
            <div class="bom-month-picker-pill d-inline-flex align-items-center rounded-pill px-1.5 py-1 shadow-xs flex-nowrap text-nowrap">
                
                {{-- Tombol Bulan Sebelumnya --}}
                <button type="button" class="btn btn-sm btn-icon-stepper rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                        onclick="geserBulanBom(-1)" title="Bulan Sebelumnya"
                        style="width: 32px; height: 32px;">
                    <i class="bi bi-chevron-left" style="font-size: 12px;"></i>
                </button>
                
                {{-- Selector Bulan Interaktif --}}
                <div class="position-relative mx-2 text-nowrap" style="min-width: 140px; text-align: center;">
                    <div class="d-flex align-items-center justify-content-center gap-1.5 px-2 py-1 rounded-pill month-label text-nowrap" style="font-size: 13px; font-weight: 700; user-select: none; cursor: pointer;">
                        <i class="bi bi-calendar3 text-primary flex-shrink-0"></i>
                        <span id="displayNamaBulanAktif" class="text-nowrap">{{ $curBulanName }}</span>
                        <i class="bi bi-chevron-down opacity-50 ms-1 flex-shrink-0" style="font-size: 10px;"></i>
                    </div>
                    <input type="month" id="filterPeriodeBom" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" 
                           value="{{ $currentYearMonth }}" onchange="gantiPeriodeBom(this.value)">
                </div>
                
                {{-- Tombol Bulan Berikutnya --}}
                <button type="button" class="btn btn-sm btn-icon-stepper rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                        onclick="geserBulanBom(1)" title="Bulan Berikutnya"
                        style="width: 32px; height: 32px;">
                    <i class="bi bi-chevron-right" style="font-size: 12px;"></i>
                </button>
            </div>

            {{-- Tombol Kembali ke Bulan Ini (Hanya muncul jika user sedang melihat bulan lain) --}}
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-none flex-shrink-0 text-nowrap shadow-xs" id="btnResetBulanIni" onclick="resetKeBulanIni()" title="Kembali ke Bulan Berjalan">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Bulan Ini
            </button>
        </div>
    </div>

    {{-- KARTU RINGKASAN KPI BOM (FULL WIDTH ON MOBILE, 3-COLUMN ON DESKTOP) --}}
    <div class="row g-2.5 g-md-3 mb-3 mb-md-4 bom-kpi-row">
        <!-- Card 1: TOTAL SEMUA ITEM (Blue) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer bom-kpi-card"
                 onclick="filterByStatus('')" title="Klik untuk menampilkan semua resep BOM"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); min-height: 105px; box-shadow: 0 8px 22px -4px rgba(37, 99, 235, 0.45) !important;">
                <div class="card-body p-3.5 d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
                    <div>
                        <span class="text-white text-uppercase fw-bold kpi-title" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">TOTAL SEMUA RESEP</span>
                        <h2 class="fw-extrabold text-white mb-0 mt-1 kpi-number" style="font-size: 32px; line-height: 1;" id="kpiTotalBom">0</h2>
                        <small class="text-white-50 d-block mt-1 kpi-subtext" style="font-size: 11.5px;" id="kpiTotalBomSub">0 disetujui dari total</small>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center kpi-icon-box shadow-xs"
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                        <i class="bi bi-box-seam fs-4 text-white"></i>
                    </div>
                </div>
                <!-- Decorative circle overlay -->
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
                <div style="position: absolute; right: 35px; top: -20px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none;"></div>
            </div>
        </div>

        <!-- Card 2: DISETUJUI (APPROVED) (Green) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer bom-kpi-card"
                 onclick="filterByStatus('Approved')" title="Klik untuk filter resep yang sudah Disetujui"
                 style="background: linear-gradient(135deg, #047857 0%, #10b981 100%); min-height: 105px; box-shadow: 0 8px 22px -4px rgba(16, 185, 129, 0.45) !important;">
                <div class="card-body p-3.5 d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
                    <div>
                        <span class="text-white text-uppercase fw-bold kpi-title" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">DISETUJUI (APPROVED)</span>
                        <h2 class="fw-extrabold text-white mb-0 mt-1 kpi-number" style="font-size: 32px; line-height: 1;" id="kpiActiveBom">0</h2>
                        <small class="text-white-50 d-block mt-1 kpi-subtext" style="font-size: 11.5px;">Siap proses produksi</small>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center kpi-icon-box shadow-xs"
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                        <i class="bi bi-check-circle-fill fs-4 text-white"></i>
                    </div>
                </div>
                <!-- Decorative circle overlay -->
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
                <div style="position: absolute; right: 35px; top: -20px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none;"></div>
            </div>
        </div>

        <!-- Card 3: PENDING & DRAFT (Amber/Purple) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 rounded-4 text-white shadow-sm position-relative overflow-hidden cursor-pointer bom-kpi-card"
                 onclick="filterByStatus('Pending')" title="Klik untuk filter resep Pending Approval"
                 style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); min-height: 105px; box-shadow: 0 8px 22px -4px rgba(245, 158, 11, 0.45) !important;">
                <div class="card-body p-3.5 d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
                    <div>
                        <span class="text-white text-uppercase fw-bold kpi-title" style="font-size: 11px; letter-spacing: 0.5px; opacity: 0.9;">PENDING / DRAFT</span>
                        <div class="d-flex align-items-baseline gap-2 mt-1">
                            <h2 class="fw-extrabold text-white mb-0 kpi-number" style="font-size: 32px; line-height: 1;" id="kpiPendingBom">0</h2>
                            <span class="badge bg-white text-dark rounded-pill px-2.5 py-1 fw-bold cursor-pointer" style="font-size: 11px;" id="kpiDraftBomBadge"
                                  onclick="event.stopPropagation(); filterByStatus('Draft')" title="Klik untuk filter hanya Draft Anda">
                                0 Draft
                            </span>
                        </div>
                        <small class="text-white-50 d-block mt-1 kpi-subtext" style="font-size: 11.5px;">Menunggu persetujuan / draft</small>
                    </div>
                    <div class="rounded-3 d-flex align-items-center justify-content-center kpi-icon-box shadow-xs"
                         style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.22); backdrop-filter: blur(4px);">
                        <i class="bi bi-hourglass-split fs-4 text-white"></i>
                    </div>
                </div>
                <!-- Decorative circle overlay -->
                <div style="position: absolute; right: -20px; bottom: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,0.1); pointer-events: none;"></div>
                <div style="position: absolute; right: 35px; top: -20px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none;"></div>
            </div>
        </div>
    </div>

    {{-- KARTU UTAMA DATATABLES & FILTER --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header p-3 p-md-3.5 border-0 rounded-top-4">
            <div class="row g-2 g-md-3 align-items-center">
                {{-- Search Bar with Instant Clear --}}
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text border-end-0 text-muted ps-3"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchTableBom" class="form-control border-start-0 border-end-0 ps-0 fs-6" placeholder="Ketik kode BOM, nama resep, atau produk...">
                        <button class="btn btn-outline-secondary border-start-0 d-none" type="button" id="btnClearSearchBom" title="Hapus Pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>

                {{-- Filter Modal Trigger & Refresh Buttons --}}
                <div class="col-12 col-md-6 col-lg-5 text-end d-flex align-items-center justify-content-between justify-content-md-end gap-2">
                    <button type="button" id="btnFilterBom" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalFilterBom">
                        <i class="bi bi-funnel-fill me-1 text-primary"></i> Filter Lanjutan
                        <span id="badgeActiveFilterCount" class="badge bg-danger rounded-pill ms-1 d-none">0</span>
                    </button>

                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold flex-grow-1 flex-md-grow-0" onclick="reloadTableBom()" title="Refresh Data Tabel">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>

                {{-- QUICK STATUS FILTER BUTTONS (SWIPEABLE ROW ON MOBILE) --}}
                <div class="col-12 pt-1">
                    <div class="bom-filter-pills-scroll no-scrollbar py-1">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold btn-quick-status" data-status="" onclick="filterByStatus('')">
                            Semua Resep
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-quick-status" data-status="Draft" onclick="filterByStatus('Draft')">
                            <i class="bi bi-pencil-square me-1"></i>Draft
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-semibold btn-quick-status" data-status="Pending" onclick="filterByStatus('Pending')">
                            <i class="bi bi-clock-history me-1"></i>Pending
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold btn-quick-status" data-status="Approved" onclick="filterByStatus('Approved')">
                            <i class="bi bi-check-circle-fill me-1"></i>Approved
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold btn-quick-status" data-status="Completed" onclick="filterByStatus('Completed')">
                            <i class="bi bi-check-all me-1"></i>Selesai
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold btn-quick-status" data-status="Rejected" onclick="filterByStatus('Rejected')">
                            <i class="bi bi-x-circle-fill me-1"></i>Rejected
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="bom-mobile-scroll-hint d-md-none">
                <i class="bi bi-arrow-left-right text-primary fs-6"></i>
                <span>Tabel dapat digeser ke samping (swipe / drag)</span>
            </div>
            <div class="table-responsive bom-table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="tabelMasterBom" style="font-size: 0.88rem;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px; min-width: 50px;">NO</th>
                            <th style="width: 150px; min-width: 150px;">KODE BOM</th>
                            <th style="min-width: 280px;">NAMA RESEP &amp; PRODUK</th>
                            <th class="text-center" style="width: 140px; min-width: 140px;">TARGET HASIL</th>
                            <th class="text-end" style="width: 160px; min-width: 160px;">ESTIMASI HPP</th>
                            <th class="text-center" style="width: 130px; min-width: 130px;">STATUS</th>
                            <th class="text-center" style="width: 150px; min-width: 150px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Server Side DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('modals')
@include('modul_produksi.bom._modal_input')
@include('modul_produksi.bom._modal_edit')
@include('modul_produksi.bom._modal_detail')
@include('modul_produksi.bom._modal_lookup_barang')
@include('modul_produksi.bom._modal_filter')
@endpush

@push('scripts')
<script>
    let tableBom = null;

    function cleanNumberInputValue(val) {
        if (val === null || val === undefined || val === '') return '';
        let num = parseFloat(val);
        if (isNaN(num)) return '';
        return parseFloat(num.toFixed(4)).toString();
    }
    window.cleanNumberInputValue = cleanNumberInputValue;
    window.formatCleanNumber = cleanNumberInputValue;

    function formatLiveRibuanID(val) {
        if (val === null || val === undefined || val === '') return '';
        let str = val.toString().replace(/[^0-9.,]/g, '');
        let parts = str.split(',');
        let intRaw = parts[0].replace(/\./g, '');
        let intFormatted = intRaw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (parts.length > 1) {
            let decRaw = parts.slice(1).join('').replace(/[^0-9]/g, '');
            return intFormatted + ',' + decRaw;
        }
        return intFormatted;
    }
    window.formatLiveRibuanID = formatLiveRibuanID;

    function unformatNumberID(val) {
        if (val === null || val === undefined || val === '') return 0;
        if (typeof val === 'number') return val;
        let str = val.toString().trim();
        if (str === '') return 0;
        if (str.includes(',')) {
            str = str.replace(/\./g, '').replace(',', '.');
        } else if (/^\d{1,3}(\.\d{3})+$/.test(str)) {
            str = str.replace(/\./g, '');
        }
        let num = parseFloat(str);
        return isNaN(num) ? 0 : num;
    }

    function formatRibuanID(num, maxDecimals = 2) {
        if (num === null || num === undefined || isNaN(num)) return '0';
        let n = parseFloat(num);
        if (isNaN(n)) return '0';

        let str = n.toFixed(maxDecimals);
        let parts = str.split('.');
        let intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        if (parts.length > 1 && parseInt(parts[1], 10) > 0) {
            let decPart = parts[1].replace(/0+$/, '');
            return decPart.length > 0 ? (intPart + ',' + decPart) : intPart;
        }
        return intPart;
    }

    function numberFormatID(val, maxDecimals = 2) {
        let num = (typeof val === 'number') ? val : (unformatNumberID(val) || parseFloat(val) || 0);
        return formatRibuanID(num, maxDecimals);
    }

    $(document).ready(function() {
        initTableBom();
        initPillsDragScroll();

        $('#searchTableBom').on('keyup input', function() {
            if (this.value.trim().length > 0) {
                $('#btnClearSearchBom').removeClass('d-none');
            } else {
                $('#btnClearSearchBom').addClass('d-none');
            }
            tableBom.search(this.value).draw();
        });

        $('#btnClearSearchBom').on('click', function() {
            $('#searchTableBom').val('').trigger('input');
            $(this).addClass('d-none');
            $('#searchTableBom').focus();
        });

        $('#filterStatusBom, #filterTipeBom').on('change', function() {
            tableBom.ajax.reload();
        });

        // Preserve body modal-open class when multiple modals close
        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal.show').length > 0) {
                $(document.body).addClass('modal-open');
            }
        });

        // ⚡ Real-Time Live Sync (Silent Background Polling setiap 3 detik)
        setInterval(function() {
            if (document.visibilityState === 'visible') {
                silentReloadTableBom();
            }
        }, 3000);

        window.addEventListener('focus', function() {
            silentReloadTableBom();
        });
    });

    let isSilentReloadBom = false;

    // Global safe HTML escaping for BOM modules
    if (typeof window.escapeHtmlBom !== 'function') {
        window.escapeHtmlBom = function(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };
    }

    function reloadTableBom() {
        if (typeof tableBom !== 'undefined' && tableBom) {
            tableBom.ajax.reload(null, false);
        }
    }

    function silentReloadTableBom() {
        // Jangan reload jika user sedang membuka modal dialog atau alert
        if ($('.modal.show').length > 0 || $('.swal2-container').length > 0) {
            return;
        }
        if (typeof tableBom !== 'undefined' && tableBom) {
            isSilentReloadBom = true;
            tableBom.ajax.reload(function() {
                isSilentReloadBom = false;
            }, false);
        }
    }

    function initPillsDragScroll() {
        $('.bom-filter-pills-scroll').each(function () {
            const slider = this;
            let isDown = false;
            let startX;
            let scrollLeft;
            let hasMoved = false;

            $(slider).off('mousedown.pills mouseleave.pills mouseup.pills mousemove.pills click.pills');

            $(slider).on('mousedown.pills', function (e) {
                isDown = true;
                hasMoved = false;
                $(slider).addClass('pills-grabbing');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            $(slider).on('mouseleave.pills', function () {
                isDown = false;
                $(slider).removeClass('pills-grabbing');
            });

            $(slider).on('mouseup.pills', function () {
                isDown = false;
                $(slider).removeClass('pills-grabbing');
            });

            $(slider).on('mousemove.pills', function (e) {
                if (!isDown) return;
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5;
                if (Math.abs(walk) > 4) {
                    hasMoved = true;
                    e.preventDefault();
                    slider.scrollLeft = scrollLeft - walk;
                }
            });

            // If user dragged horizontally, prevent accidental button click trigger
            $(slider).on('click.pills', '.btn-quick-status', function (e) {
                if (hasMoved) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    hasMoved = false;
                }
            });
        });
    }

    function initTableDragScroll() {
        $('.table-responsive, .bom-table-responsive').each(function () {
            const slider = this;
            let isDown = false;
            let startX;
            let scrollLeft;

            $(slider).off('mousedown.drag mouseleave.drag mouseup.drag mousemove.drag');

            $(slider).on('mousedown.drag', function (e) {
                if ($(e.target).closest('button, a, input, select, textarea, .btn, .badge').length) return;
                isDown = true;
                $(slider).addClass('table-grabbing');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            $(slider).on('mouseleave.drag', function () {
                isDown = false;
                $(slider).removeClass('table-grabbing');
            });

            $(slider).on('mouseup.drag', function () {
                isDown = false;
                $(slider).removeClass('table-grabbing');
            });

            $(slider).on('mousemove.drag', function (e) {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5;
                slider.scrollLeft = scrollLeft - walk;
            });
        });
    }

    function initTableBom() {
        tableBom = $('#tabelMasterBom').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route("produksi.bom.index") }}',
                data: function(d) {
                    d.status   = $('#filterStatusBom').val();
                    d.tipe_bom = $('#filterTipeBom').val();
                    d.periode  = $('#filterPeriodeBom').val() || '';
                    // ⚠️ Hanya kirim filter HPP kalau user benar2 mengisi
                    const minHppRaw = $('#filterMinHpp').val() ? $('#filterMinHpp').val().trim() : '';
                    const maxHppRaw = $('#filterMaxHpp').val() ? $('#filterMaxHpp').val().trim() : '';
                    if (minHppRaw !== '') d.min_hpp = unformatNumberID(minHppRaw);
                    if (maxHppRaw !== '') d.max_hpp = unformatNumberID(maxHppRaw);
                },
                dataSrc: function(json) {
                    if (json.kpi) {
                        $('#kpiTotalBom').text(numberFormatID(json.kpi.total_bom));
                        $('#kpiTotalBomSub').text(`${numberFormatID(json.kpi.total_active)} disetujui dari total`);
                        $('#kpiActiveBom').text(numberFormatID(json.kpi.total_active));
                        $('#kpiPendingBom').text(numberFormatID(json.kpi.total_pending));
                        $('#kpiDraftBomBadge').text(`${numberFormatID(json.kpi.total_draft)} Draft`);
                        if (json.kpi.can_view_hpp) {
                            $('#kpiAvgHpp').text('Rp ' + numberFormatID(json.kpi.total_hpp_avg));
                        } else {
                            $('#kpiAvgHpp').html('<span class="fs-6 opacity-75 text-white" title="HPP Terkunci"><i class="bi bi-lock-fill"></i></span>');
                        }
                    }
                    return json.data || [];
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables BOM Error:', xhr.status, thrown);
                    const msg = xhr.responseJSON?.message || 'Gagal memuat data tabel BOM.';
                    Swal.fire({ icon: 'error', title: 'Error ' + xhr.status, text: msg, timer: 3500, showConfirmButton: false });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode_bom_fmt', name: 'kode_bom', className: 'fw-bold' },
                { data: 'nama_bom_fmt', name: 'nama_bom' },
                { data: 'jumlah_hasil_fmt', name: 'jumlah_hasil', className: 'text-center' },
                { data: 'total_hpp_fmt', name: 'total_estimasi_hpp', className: 'text-end font-monospace' },
                { data: 'status_badge', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading BOM...',
                search: "_INPUT_",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ resep BOM",
                infoEmpty: "Tidak ada data BOM",
                emptyTable: "Belum ada resep BOM produksi yang tercatat.",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>'
                }
            },
            dom: "<'row p-2 p-md-3 align-items-center'<'col-12 col-md-6 mb-2 mb-md-0'l>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row p-2 p-md-3 align-items-center'<'col-12 col-md-5 mb-2 mb-md-0'i><'col-12 col-md-7'p>>"
        });

        $('#tabelMasterBom').on('processing.dt', function (e, settings, processing) {
            if (isSilentReloadBom) {
                $('#tabelMasterBom_processing').hide();
            } else {
                if (processing) {
                    $('#tabelMasterBom_processing').show();
                } else {
                    $('#tabelMasterBom_processing').hide();
                }
            }
        });

        $('#tabelMasterBom').on('draw.dt', function () {
            initTableDragScroll();
        });

        initTableDragScroll();
    }

    const BULAN_INDO = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const BULAN_INI_STR = '{{ date("Y-m") }}';

    function formatNamaBulanID(val) {
        if (!val) return '{{ $curBulanName }}';
        const parts = val.split('-');
        if (parts.length !== 2) return val;
        const yyyy = parts[0];
        const mm = parts[1];
        const blnIdx = parseInt(mm, 10) - 1;
        return (BULAN_INDO[blnIdx] || mm) + ' ' + yyyy;
    }

    function gantiPeriodeBom(val) {
        if (!val) {
            val = BULAN_INI_STR;
            $('#filterPeriodeBom').val(val);
        }
        const formatted = formatNamaBulanID(val);
        $('#lblPeriodeAktifBom').text(formatted);
        $('#displayNamaBulanAktif').text(formatted);

        if (val !== BULAN_INI_STR) {
            $('#btnResetBulanIni').removeClass('d-none');
        } else {
            $('#btnResetBulanIni').addClass('d-none');
        }
        reloadTableBom();
    }

    function resetKeBulanIni() {
        $('#filterPeriodeBom').val(BULAN_INI_STR);
        gantiPeriodeBom(BULAN_INI_STR);
    }

    function geserBulanBom(step) {
        let curVal = $('#filterPeriodeBom').val() || BULAN_INI_STR;
        let parts = curVal.split('-').map(Number);
        let y = parts[0];
        let m = parts[1];
        let d = new Date(y, m - 1 + step, 1);
        let ym = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        $('#filterPeriodeBom').val(ym);
        gantiPeriodeBom(ym);
    }

    function filterByStatus(status) {
        $('#filterStatusBom').val(status || '');
        $('.btn-quick-status').removeClass('btn-primary btn-secondary btn-warning btn-success btn-info btn-danger text-white text-dark active')
            .removeClass('btn-outline-primary btn-outline-secondary btn-outline-warning btn-outline-success btn-outline-info btn-outline-danger')
            .each(function() {
                const s = $(this).data('status');
                if (s === (status || '')) {
                    $(this).addClass('active');
                    if (s === 'Draft') $(this).addClass('btn-secondary text-white');
                    else if (s === 'Pending') $(this).addClass('btn-warning text-dark');
                    else if (s === 'Approved') $(this).addClass('btn-success text-white');
                    else if (s === 'Completed') $(this).addClass('btn-info text-white');
                    else if (s === 'Rejected') $(this).addClass('btn-danger text-white');
                    else $(this).addClass('btn-primary text-white');
                } else {
                    if (s === 'Draft') $(this).addClass('btn-outline-secondary');
                    else if (s === 'Pending') $(this).addClass('btn-outline-warning');
                    else if (s === 'Approved') $(this).addClass('btn-outline-success');
                    else if (s === 'Completed') $(this).addClass('btn-outline-info');
                    else if (s === 'Rejected') $(this).addClass('btn-outline-danger');
                    else $(this).addClass('btn-outline-primary');
                }
            });

        reloadTableBom();
    }

    function completeBom(id) {
        Swal.fire({
            title: 'Tandai Selesai BOM ini?',
            text: 'Resep BOM yang diselesaikan akan berstatus Selesai.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-all me-1"></i> Ya, Tandai Selesai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Menyimpan status Selesai...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `/produksi/bom/${id}/complete`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Resep BOM telah ditandai sebagai Selesai.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    }

    function reactivateBom(id) {
        Swal.fire({
            title: 'Aktifkan Kembali BOM ini?',
            text: 'Resep BOM yang diaktifkan kembali akan berstatus Approved (Aktif).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Aktifkan Kembali',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengaktifkan kembali BOM...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `/produksi/bom/${id}/reactivate`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Aktif!',
                            text: res.message || 'Resep BOM telah diaktifkan kembali.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    }

    function reloadTableBom() {
        if (tableBom) {
            tableBom.ajax.reload(null, false);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW BOM: SEND PENDING, APPROVE, REJECT
    // ══════════════════════════════════════════════════════════════

    function sendPendingBom(id) {
        Swal.fire({
            title: 'Ajukan Resep BOM?',
            text: 'Resep BOM akan diajukan ke status Pending Approval agar dapat ditinjau dan disetujui.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Ya, Ajukan (Pending)',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengirim pengajuan BOM ke atasan...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `/produksi/bom/${id}/send-pending`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diajukan!',
                            text: res.message || 'Resep BOM berhasil masuk antrean Pending Approval.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal Mengajukan', xhr.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    }

    function approveBom(id) {
        Swal.fire({
            title: 'Setujui Resep BOM ini?',
            text: 'Resep BOM yang disetujui (Approved) akan aktif dan siap digunakan untuk proses SPK & Produksi.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Setujui (Approve)',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Approval...',
                    text: 'Menyimpan persetujuan BOM...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `/produksi/bom/${id}/approve`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Disetujui!',
                            text: res.message || 'Resep BOM berhasil disetujui.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal Menyetujui', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui BOM.', 'error');
                    }
                });
            }
        });
    }

    function rejectBom(id) {
        Swal.fire({
            title: 'Tolak Resep BOM?',
            input: 'textarea',
            inputLabel: 'Alasan Penolakan (Catatan)',
            inputPlaceholder: 'Tuliskan alasan penolakan atau revisi bahan baku...',
            inputAttributes: {
                'aria-label': 'Alasan penolakan'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-x-circle-fill me-1"></i> Tolak (Reject)',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Harap masukkan alasan penolakan!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Penolakan...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `/produksi/bom/${id}/reject`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        alasan: result.value
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'info',
                            title: 'BOM Ditolak',
                            text: res.message || 'Resep BOM telah ditolak.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal Menolak', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                    }
                });
            }
        });
    }

    function deleteBom(id) {
        Swal.fire({
            title: 'Hapus Resep BOM?',
            text: 'BOM yang dihapus tidak dapat dipulihkan kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/produksi/bom/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: res.message,
                            timer: 1800,
                            showConfirmButton: false
                        });
                        reloadTableBom();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menghapus BOM.', 'error');
                    }
                });
            }
        });
    }

    function printBom(id) {
        const url = `/produksi/bom/${id}/print`;
        window.open(url, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
    }
</script>
@endpush
</x-layouts>
