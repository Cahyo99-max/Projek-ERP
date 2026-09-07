<x-layouts>

<x-slot:title>Faktur Pembelian (Purchase Invoice)</x-slot:title>

@push('styles')
<style>
    /* ===== THEME ADAPTIVE CSS VARIABLES ===== */
    :root {
        --erp-bg: #f8fafc;
        --erp-card-bg: #ffffff;
        --erp-border: #e2e8f0;
        --erp-table-header: #f1f5f9;
        --erp-table-stripe: #f8fafc;
        --erp-table-hover: #f1f5f9;
        --erp-text-main: #0f172a;
        --erp-text-muted: #64748b;
    }

    [data-bs-theme="dark"] {
        --erp-bg: #0b1120;
        --erp-card-bg: #111827;
        --erp-border: rgba(255, 255, 255, 0.08);
        --erp-table-header: #1f2937;
        --erp-table-stripe: #131d2e;
        --erp-table-hover: #1e293b;
        --erp-text-main: #f8fafc;
        --erp-text-muted: #94a3b8;
    }

    .erp-card {
        background-color: var(--erp-card-bg) !important;
        border: 1px solid var(--erp-border) !important;
        border-radius: 0.75rem;
    }
    .erp-card-header {
        background-color: transparent !important;
        border-bottom: 1px solid var(--erp-border) !important;
        padding: 0.85rem 1.25rem;
    }

    /* ===== CINEMATIC BENTO GLOW CARDS ===== */
    .inv-cinematic-card {
        border-radius: 1.15rem;
        padding: 1.35rem 1.45rem;
        position: relative;
        overflow: hidden;
        border: none !important;
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1;
    }
    .inv-cinematic-card:hover {
        transform: translateY(-3px);
    }
    .inv-cine-blue {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4), 0 8px 10px -6px rgba(37, 99, 235, 0.2);
    }
    .inv-cine-amber {
        background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);
        box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.4), 0 8px 10px -6px rgba(217, 119, 6, 0.2);
    }
    .inv-cine-emerald {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);
        box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.4), 0 8px 10px -6px rgba(5, 150, 105, 0.2);
    }
    .inv-cine-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }
    .inv-cine-value {
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: #ffffff;
        margin-bottom: 0.4rem;
        font-family: system-ui, -apple-system, sans-serif;
    }
    .inv-cine-sub {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.85);
    }
    .inv-cine-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .inv-cine-circle {
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }

    /* ===== DUAL TOUCH SCROLLBARS FOR MOBILE & TABLET ===== */
    .inv-top-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        height: 12px;
        width: 100%;
        border-radius: 4px;
        background: rgba(0,0,0,0.02);
    }
    .inv-top-scroll-inner {
        height: 12px;
    }
    .inv-table-scroll-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }
    .inv-top-scroll-container::-webkit-scrollbar,
    .inv-table-scroll-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .inv-top-scroll-container::-webkit-scrollbar-thumb,
    .inv-table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.35);
        border-radius: 6px;
    }
    .inv-top-scroll-container::-webkit-scrollbar-thumb:hover,
    .inv-table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.6);
    }

    /* ===== THEMED TABLE STYLES ===== */
    #tableInvoice {
        min-width: 980px;
    }
    #tableInvoice thead th {
        background-color: var(--erp-table-header) !important;
        color: var(--erp-text-main) !important;
        border-bottom: 2px solid var(--erp-border) !important;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.75rem 0.65rem;
    }
    #tableInvoice tbody tr {
        background-color: transparent !important;
        transition: background-color 0.15s ease-in-out;
    }
    #tableInvoice tbody tr:hover {
        background-color: var(--erp-table-hover) !important;
    }
    #tableInvoice td {
        padding: 0.7rem 0.65rem;
        font-size: 0.84rem;
        border-bottom: 1px solid var(--erp-border);
        vertical-align: middle;
    }

    /* ===== MODAL FULLSCREEN STYLES ===== */
    .modal-dialog-invoice-wide {
        max-width: 1400px;
    }
    .inv-modal-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: #fff;
        padding: 1.15rem 1.5rem;
        border-bottom: 0;
    }
    .inv-modal-header .modal-title {
        font-weight: 800;
        font-size: 1.15rem;
        color: #fff;
        letter-spacing: -0.01em;
    }
    .inv-modal-header .text-subtitle {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.75);
    }
    .inv-modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    .inv-action-bar {
        position: sticky;
        top: 0;
        z-index: 1020;
        background: var(--erp-card-bg);
        border-bottom: 1px solid var(--erp-border);
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        backdrop-filter: blur(8px);
    }
    .inv-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .inv-status-pill.draft {
        background: rgba(245, 158, 11, 0.2);
        color: #fef08a;
        border: 1px solid rgba(245, 158, 11, 0.4);
    }

    /* ===== SUMMARY SIDEBAR ===== */
    .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.4rem 0;
        border-bottom: 1px dashed var(--erp-border);
        font-size: 0.8rem;
    }
    .calc-row:last-child {
        border-bottom: none;
    }
    .calc-label {
        color: var(--erp-text-muted);
    }
    .calc-value {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 600;
    }
    .calc-grand-total {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
        border-radius: 0.625rem;
        padding: 1rem 1.25rem;
        color: #fff;
        margin-top: 0.75rem;
    }
    .calc-grand-total .label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.75);
        font-weight: 600;
    }
    .calc-grand-total .value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .inv-page-container {
            padding-left: 0.35rem !important;
            padding-right: 0.35rem !important;
        }
        .inv-cinematic-card {
            padding: 1rem 1.15rem;
            margin-bottom: 0.5rem;
        }
        .inv-cine-value {
            font-size: 1.35rem;
        }
    }
</style>
@endpush

<div class="container-fluid py-2 py-md-3 inv-page-container">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3 mb-md-4">
        <div>
            <h4 class="fw-bold mb-0 text-body">Faktur Pembelian</h4>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
            <button type="button" class="btn btn-primary fw-semibold w-100 w-md-auto rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahInvoice">
                <i class="bi bi-plus-lg me-1"></i> Buat Faktur Pembelian
            </button>
        </div>
    </div>

    <!-- SUMMARY KPI CARDS (BENTO GLOW CINEMATIC WITH MULTI-CURRENCY BREAKDOWN) -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-blue text-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="inv-cine-title">TOTAL FAKTUR</div>
                    <div class="inv-cine-icon">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
                <div class="inv-cine-value">{{ number_format($totalInvoiceCount ?? 0) }}</div>
                <div class="inv-cine-sub d-flex flex-wrap gap-1 align-items-center mt-1">
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                        <i class="bi bi-tag-fill me-1"></i>{{ $countIdr ?? 0 }} IDR
                    </span>
                    @if(($countForeign ?? 0) > 0)
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                        <i class="bi bi-currency-exchange me-1"></i>{{ $countForeign }} USD / Valas
                    </span>
                    @endif
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-amber text-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="inv-cine-title">HUTANG BELUM LUNAS (UNPAID)</div>
                    <div class="inv-cine-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div class="inv-cine-value">Rp {{ number_format($totalUnpaidAmount ?? 0, 2, ',', '.') }}</div>
                <div class="inv-cine-sub">
                    @if(($unpaidForeign ?? 0) > 0)
                    <div class="d-flex flex-wrap gap-1 align-items-center mt-1 font-monospace" style="font-size: 0.73rem;">
                        <span class="badge bg-black bg-opacity-25 text-white rounded px-2 py-0.5">IDR: Rp {{ number_format($unpaidIdr ?? 0, 2, ',', '.') }}</span>
                        <span class="badge bg-white bg-opacity-25 text-white rounded px-2 py-0.5">{{ $unpaidForeignCode ?? 'USD' }}: {{ $unpaidForeignSymbol ?? '$' }} {{ number_format($unpaidForeign, 2, ',', '.') }}</span>
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-clock-history me-1"></i> Draft & Menunggu Pembayaran
                    </div>
                    @endif
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card inv-cinematic-card inv-cine-emerald text-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="inv-cine-title">PEMBAYARAN BULAN INI</div>
                    <div class="inv-cine-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
                <div class="inv-cine-value">Rp {{ number_format($totalPaidThisMonth ?? 0, 2, ',', '.') }}</div>
                <div class="inv-cine-sub">
                    @if(($paidThisMonthForeign ?? 0) > 0)
                    <div class="d-flex flex-wrap gap-1 align-items-center mt-1 font-monospace" style="font-size: 0.73rem;">
                        <span class="badge bg-black bg-opacity-25 text-white rounded px-2 py-0.5">IDR: Rp {{ number_format($paidThisMonthIdr ?? 0, 2, ',', '.') }}</span>
                        <span class="badge bg-white bg-opacity-25 text-white rounded px-2 py-0.5">{{ $paidThisMonthForeignCode ?? 'USD' }}: {{ $paidThisMonthForeignSymbol ?? '$' }} {{ number_format($paidThisMonthForeign, 2, ',', '.') }}</span>
                    </div>
                    @else
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-calendar3 me-1"></i> Periode {{ $currentMonthName ?? \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </div>
                    @endif
                </div>
                <div class="inv-cine-circle"></div>
            </div>
        </div>
    </div>

    <!-- MAIN DATATABLE LIST -->
    <div class="erp-card">
        <div class="erp-card-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-table text-primary"></i>
                <h6 class="fw-bold mb-0 text-body">Daftar Tagihan Supplier (AP Invoices)</h6>
            </div>
            <div class="d-none d-md-block text-muted small">
                <i class="bi bi-info-circle me-1"></i> Geser horizontal untuk melihat rincian kolom
            </div>
        </div>
        <div class="card-body p-2 p-md-3">
            
            <!-- Mobile Scroll Indicator Badge -->
            <div class="d-md-none text-center my-1">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace" style="font-size:0.72rem;">
                    <i class="bi bi-arrows-expand me-1"></i>Geser tabel ke samping
                </span>
            </div>

            <!-- Top Dual-Scroll for Touch Mobile & Desktop -->
            <div class="inv-top-scroll-container mb-1" id="invTopScrollContainer">
                <div class="inv-top-scroll-inner" id="invTopScrollInner"></div>
            </div>

            <div class="inv-table-scroll-wrapper" id="invTableScrollWrapper">
                <table class="table align-middle table-hover w-100 inv-wide-table" id="tableInvoice">
                    <thead>
                        <tr>
                            <th width="3%">NO</th>
                            <th width="12%">NO. FAKTUR</th>
                            <th width="14%">NO. FAKTUR SUPPLIER</th>
                            <th width="10%">TANGGAL</th>
                            <th width="10%">JATUH TEMPO</th>
                            <th width="18%">SUPPLIER</th>
                            <th width="12%">NO. PENERIMAAN (GR)</th>
                            <th width="12%" class="text-end">GRAND TOTAL</th>
                            <th width="9%" class="text-center">STATUS</th>
                            <th width="10%" class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ===== INCLUDE MODALS ===== -->
@include('modul_purchasing.faktur_pembelian.partials.modal_create')
@include('modul_purchasing.faktur_pembelian.partials.modal_edit')
@include('modul_purchasing.faktur_pembelian.partials.modal_view')
@include('modul_purchasing.faktur_pembelian.partials.modal_browse_gr')

@push('scripts')
    <!-- ===== INCLUDE JAVASCRIPTS ===== -->
    @include('modul_purchasing.faktur_pembelian.scripts.index_js')
    @include('modul_purchasing.faktur_pembelian.scripts.create_js')
    @include('modul_purchasing.faktur_pembelian.scripts.edit_js')
    @include('modul_purchasing.faktur_pembelian.scripts.view_js')
@endpush

</x-layouts>
