@php
    // Fallback: If partial is rendered directly, load data via MainDashboardController
    if (!isset($prPending)) {
        $dashController = app(\App\Http\Controllers\MainDashboardController::class);
        $dashData = $dashController->getPurchasingDashboardData(session('active_company_id'), session('active_branch_id'));
        extract($dashData);
    }
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/purchasing_dashboard.css') }}">
@endpush

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 1. CINEMATIC 5-KPI STAT CARDS GRID --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-5 g-3 mb-4">
    
    {{-- KPI 1: Total Realisasi Belanja PO (Bulan Ini) --}}
    <div class="col">
        <div class="cinematic-kpi-card kpi-emerald">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="kpi-title-label">Belanja PO (Bulan Ini)</div>
                    <div class="kpi-main-metric text-truncate" title="Rp {{ number_format($poSpendThisMonth, 0, ',', '.') }}">
                        Rp {{ number_format($poSpendThisMonth >= 1000000 ? $poSpendThisMonth / 1000000 : $poSpendThisMonth / 1000, 1, ',', '.') }} {{ $poSpendThisMonth >= 1000000 ? 'Jt' : 'Rb' }}
                    </div>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                @if($spendGrowthPct >= 0)
                    <span class="pill-badge pill-badge-emerald">
                        <i class="bi bi-graph-up-arrow"></i> +{{ $spendGrowthPct }}% MoM
                    </span>
                @else
                    <span class="pill-badge pill-badge-rose">
                        <i class="bi bi-graph-down-arrow"></i> {{ $spendGrowthPct }}% MoM
                    </span>
                @endif
                <small class="pch-subtext fs-9 fw-bold">{{ $poCompletedOrders }} PO Diterima</small>
            </div>
        </div>
    </div>

    {{-- KPI 2: PO Aktif & Selesai --}}
    <div class="col">
        <div class="cinematic-kpi-card kpi-blue">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="kpi-title-label">PO Berjalan / Aktif</div>
                    <div class="kpi-main-metric">
                        {{ $poActiveOrders }} <span class="fs-7 pch-subtext fw-semibold">PO</span>
                    </div>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-cart-check-fill"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="pill-badge pill-badge-blue">
                    <i class="bi bi-hourglass-split"></i> {{ $poCompletedOrders }} PO Selesai
                </span>
                <small class="pch-subtext fs-9 fw-bold">Bulan Berjalan</small>
            </div>
        </div>
    </div>

    {{-- KPI 3: PR Pending Approval --}}
    <div class="col">
        <div class="cinematic-kpi-card kpi-amber">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="kpi-title-label">Antrian PR Pending</div>
                    <div class="kpi-main-metric">
                        {{ $prPending }} <span class="fs-7 pch-subtext fw-semibold">Item</span>
                    </div>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="pill-badge pill-badge-amber">
                    <i class="bi bi-shield-exclamation"></i> Butuh Review
                </span>
                <small class="pch-subtext fs-9 fw-bold">{{ $prApproved }} Disetujui</small>
            </div>
        </div>
    </div>

    {{-- KPI 4: Mitra Vendor --}}
    <div class="col">
        <div class="cinematic-kpi-card kpi-cyan">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="kpi-title-label">Mitra Supplier</div>
                    <div class="kpi-main-metric">
                        {{ $supplierCount }} <span class="fs-7 pch-subtext fw-semibold">Vendor</span>
                    </div>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="pill-badge pill-badge-cyan">
                    <i class="bi bi-check-circle-fill"></i> Terverifikasi
                </span>
                <small class="pch-subtext fs-9 fw-bold">Katalog Master</small>
            </div>
        </div>
    </div>

    {{-- KPI 5: Defisit / SKU Kritis --}}
    <div class="col">
        <div class="cinematic-kpi-card kpi-rose">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="kpi-title-label">Defisit Stok Kritis</div>
                    <div class="kpi-main-metric {{ $criticalStockCount > 0 ? 'text-danger' : '' }}">
                        {{ $criticalStockCount }} <span class="fs-7 pch-subtext fw-semibold">Stok</span>
                    </div>
                </div>
                <div class="kpi-icon-bubble">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                @if($criticalStockCount > 0)
                    <span class="pill-badge pill-badge-rose">
                        <i class="bi bi-bell-fill"></i> Reorder Segera!
                    </span>
                @else
                    <span class="pill-badge pill-badge-emerald">
                        <i class="bi bi-shield-check"></i> Stok Aman
                    </span>
                @endif
                <small class="pch-subtext fs-9 fw-bold">&le; Min. Stok</small>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 2. CINEMATIC CHARTS ROW --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    
    {{-- Left Chart: Spend Trend & Volume Analytics (Col 8) --}}
    <div class="col-xl-8">
        <div class="cinematic-box h-100">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
                <div>
                    <h5 class="pch-heading mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-primary"></i> Tren Belanja & Frekuensi PO 6 Bulan Terakhir
                    </h5>
                    <p class="pch-subtext mb-0 fs-8">Analisis pergerakan total nominal pengadaan (Garis) dan volume pesanan (Batang)</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="pill-badge pill-badge-blue">
                        <i class="bi bi-layers-fill me-1"></i> Multi-Axis AI Stream
                    </span>
                </div>
            </div>
            <div style="height: 330px; position: relative;">
                <canvas id="purchasingTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Right Chart: Department Spend Breakdown (Col 4) --}}
    <div class="col-xl-4">
        <div class="cinematic-box h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="pch-heading mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-info"></i> Alokasi per Departemen
                    </h5>
                    <p class="pch-subtext mb-0 fs-8">Proporsi pengajuan PR bulan ini</p>
                </div>
                <span class="pill-badge pill-badge-cyan">
                    Top 5 Divisi
                </span>
            </div>
            
            <div style="height: 240px; position: relative;" class="my-2">
                <canvas id="deptDonutChart"></canvas>
            </div>

            <div class="border-top pt-3 mt-2" style="border-color: var(--pch-card-border) !important;">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="p-2 rounded-3" style="background: var(--pch-surface-sub); border: 1px solid var(--pch-card-border);">
                            <small class="pch-subtext d-block fs-9 fw-bold">Total Pengajuan (Bln Ini)</small>
                            <span class="pch-heading fs-7">{{ $prTotal }} PR</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3" style="background: var(--pch-surface-sub); border: 1px solid var(--pch-card-border);">
                            <small class="pch-subtext d-block fs-9 fw-bold">Approval Rate</small>
                            <span class="fw-bold text-success fs-7">{{ $prTotal > 0 ? round(($prApproved / $prTotal) * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 3. INTERACTIVE PROCUREMENT PIPELINE & SMART REORDER RADAR --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    
    {{-- Left: Live Pipeline Stream (Col 7) --}}
    <div class="col-xl-7">
        <div class="cinematic-box h-100">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h5 class="pch-heading mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-activity text-primary"></i> Live Procurement Pipeline
                    </h5>
                    <p class="pch-subtext mb-0 fs-8">Riwayat transaksi pengadaan terkini di sistem</p>
                </div>
                
                {{-- Interactive Switch Tabs --}}
                <div class="pipeline-tabs-wrapper">
                    <button type="button" class="pipeline-tab-btn active" id="btnTabPr">
                        <i class="bi bi-file-earmark-text me-1"></i> Pengajuan PR
                    </button>
                    <button type="button" class="pipeline-tab-btn" id="btnTabPo">
                        <i class="bi bi-cart-check me-1"></i> Order PO
                    </button>
                </div>
            </div>

            {{-- Table PR Container --}}
            <div id="containerPrStream" class="table-responsive">
                <table class="table align-middle cinematic-table mb-0">
                    <thead>
                        <tr>
                            <th>No. Pengajuan</th>
                            <th>Tanggal</th>
                            <th>Departemen</th>
                            <th>Pemohon</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPrs as $pr)
                            <tr>
                                <td>
                                    <a href="/purchase-requisition" class="pch-link">
                                        {{ $pr->nomor_pr }}
                                    </a>
                                </td>
                                <td class="pch-body-text">{{ optional($pr->tanggal_pr)->format('d/m/Y') ?? $pr->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="pill-badge pill-badge-slate">
                                        {{ $pr->department->department_name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="pch-heading fs-8">{{ $pr->requester->nama_lengkap ?? ($pr->diminta_oleh ?? '-') }}</div>
                                </td>
                                <td class="text-end">
                                    @if($pr->status === 'Pending')
                                        <span class="pill-badge pill-badge-amber">
                                            <i class="bi bi-clock me-1"></i>Pending
                                        </span>
                                    @elseif($pr->status === 'Approved')
                                        <span class="pill-badge pill-badge-emerald">
                                            <i class="bi bi-check-circle me-1"></i>Approved
                                        </span>
                                    @elseif($pr->status === 'Rejected')
                                        <span class="pill-badge pill-badge-rose">
                                            <i class="bi bi-x-circle me-1"></i>Rejected
                                        </span>
                                    @else
                                        <span class="pill-badge pill-badge-slate">
                                            {{ $pr->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 pch-subtext">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    Belum ada data pengajuan pembelian (PR).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color: var(--pch-card-border) !important;">
                    <small class="pch-subtext">Menampilkan 10 PR terbaru</small>
                    <a href="/purchase-requisition" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold fs-8">
                        Buka Modul PR <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            {{-- Table PO Container (Hidden by default) --}}
            <div id="containerPoStream" class="table-responsive d-none">
                <table class="table align-middle cinematic-table mb-0">
                    <thead>
                        <tr>
                            <th>No. Order PO</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Total Nilai</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPos as $po)
                            <tr>
                                <td>
                                    <a href="/purchase-order" class="pch-link">
                                        {{ $po->nomor_po }}
                                    </a>
                                </td>
                                <td class="pch-body-text">{{ optional($po->tanggal_po)->format('d/m/Y') ?? $po->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="pch-heading fs-8 text-truncate" style="max-width: 140px;" title="{{ $po->supplier->nama_supplier ?? '-' }}">
                                        {{ $po->supplier->nama_supplier ?? '-' }}
                                    </div>
                                </td>
                                <td class="fw-bold text-success">
                                    {{ $po->currency->simbol ?? 'Rp' }} {{ number_format($po->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    @if($po->status === 'Ordered')
                                        <span class="pill-badge pill-badge-emerald">Ordered</span>
                                    @elseif($po->status === 'Pending')
                                        <span class="pill-badge pill-badge-amber">Pending</span>
                                    @elseif($po->status === 'Partially Received')
                                        <span class="pill-badge pill-badge-cyan">Partial</span>
                                    @elseif($po->status === 'Received')
                                        <span class="pill-badge pill-badge-blue">Received</span>
                                    @elseif($po->status === 'Rejected')
                                        <span class="pill-badge pill-badge-rose">Rejected</span>
                                    @else
                                        <span class="pill-badge pill-badge-slate">{{ $po->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 pch-subtext">
                                    <i class="bi bi-cart-x fs-2 d-block mb-2 opacity-50"></i>
                                    Belum ada data Purchase Order (PO).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color: var(--pch-card-border) !important;">
                    <small class="pch-subtext">Menampilkan 10 PO terbaru</small>
                    <a href="/purchase-order" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold fs-8">
                        Buka Modul PO <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- Right: Smart Reorder Radar & Vendor Leaderboard (Col 5) --}}
    <div class="col-xl-5" id="reorderRadarSection">
        
        {{-- Card: Smart Reorder Radar --}}
        <div class="cinematic-box mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="pch-heading mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-radar text-danger"></i> Smart Reorder Radar
                    </h5>
                </div>
                <span class="pill-badge pill-badge-rose">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Prioritas Restock
                </span>
            </div>
            <p class="pch-subtext fs-8 mb-3">Item stok di bawah kuota minimum yang mendesak untuk pengadaan</p>

            <div class="radar-items-container">
                @forelse($criticalStock as $stock)
                    @php
                        $minStok = max(1, (float)$stock->minimum_stok);
                        $stokAktual = (float)$stock->stok_aktual;
                        $pctStock = min(100, round(($stokAktual / $minStok) * 100));
                        $barColor = $pctStock <= 30 ? '#ef4444' : ($pctStock <= 70 ? '#f59e0b' : '#3b82f6');
                    @endphp
                    <div class="radar-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div style="min-width: 0;">
                                <div class="radar-item-name text-truncate" title="{{ $stock->nama_barang }}">
                                    {{ $stock->nama_barang }}
                                </div>
                                <div class="pch-subtext fs-9 font-monospace">{{ $stock->kode_barang }} &bull; {{ $stock->nama_gudang }}</div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="pill-badge pill-badge-rose" style="font-size: 0.72rem; padding: 0.15rem 0.55rem;">
                                    Sisa: {{ number_format($stock->stok_aktual, 0, ',', '.') }}
                                </span>
                                <small class="pch-subtext d-block fs-9 mt-0.5 fw-semibold">Min: {{ number_format($stock->minimum_stok, 0, ',', '.') }}</small>
                            </div>
                        </div>
                        <div class="radar-progress-bar">
                            <div class="radar-progress-fill" style="width: {{ $pctStock }}%; background-color: {{ $barColor }};"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 pch-subtext">
                        <i class="bi bi-shield-check fs-2 d-block mb-1 text-success opacity-75"></i>
                        Kondisi aman! Tidak ada item di bawah batas minimum stok.
                    </div>
                @endforelse
            </div>

            @if(count($criticalStock) > 0)
                <a href="/purchase-requisition" class="btn btn-outline-danger w-100 rounded-pill btn-sm fw-bold mt-2">
                    <i class="bi bi-plus-circle-fill me-1"></i> Buat Pengajuan PR untuk Item Kritis
                </a>
            @endif
        </div>

        {{-- Card: Top Vendor Leaderboard --}}
        <div class="cinematic-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="pch-heading mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill text-warning"></i> Top Mitra Vendor
                    </h5>
                </div>
                <span class="pill-badge pill-badge-amber">
                    Peringkat Belanja
                </span>
            </div>
            <p class="pch-subtext fs-8 mb-3">Pemasok dengan alokasi transaksi terbesar</p>

            <div class="vendor-leaderboard">
                @php
                    $maxVendorSpend = $popularSuppliers->max('total_spend') ?: 1;
                @endphp
                @forelse($popularSuppliers as $idx => $vendor)
                    @php
                        $vendorPercentage = ($vendor->total_spend / $maxVendorSpend) * 100;
                    @endphp
                    <div class="vendor-row-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2.5" style="min-width: 0;">
                            @if($idx === 0)
                                <div class="rank-medal-box rank-gold"><i class="bi bi-trophy-fill"></i></div>
                            @elseif($idx === 1)
                                <div class="rank-medal-box rank-silver">2</div>
                            @elseif($idx === 2)
                                <div class="rank-medal-box rank-bronze">3</div>
                            @else
                                <div class="rank-medal-box rank-other">{{ $idx + 1 }}</div>
                            @endif
                            <div style="min-width: 0;">
                                <div class="vendor-name-title text-truncate" style="max-width: 140px;" title="{{ $vendor->nama_supplier }}">
                                    {{ $vendor->nama_supplier }}
                                </div>
                                <small class="pch-subtext fs-9 fw-semibold">{{ $vendor->total_orders }} Transaksi PO</small>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="fw-bold text-success fs-7">
                                Rp {{ number_format($vendor->total_spend / 1000000, 1, ',', '.') }} Jt
                            </span>
                            <div class="mini-progress mt-1" style="width: 80px;">
                                <div class="mini-progress-fill" style="width: {{ $vendorPercentage }}%; background: {{ $idx === 0 ? '#f59e0b' : ($idx === 1 ? '#94a3b8' : ($idx === 2 ? '#ea580c' : '#3b82f6')) }};"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 pch-subtext">
                        Belum ada data transaksi vendor.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    $(document).ready(function() {
        
        // ── PIPELINE STREAM TAB TOGGLE ──
        $('#btnTabPr').on('click', function() {
            $(this).addClass('active');
            $('#btnTabPo').removeClass('active');
            $('#containerPrStream').removeClass('d-none');
            $('#containerPoStream').addClass('d-none');
        });

        $('#btnTabPo').on('click', function() {
            $(this).addClass('active');
            $('#btnTabPr').removeClass('active');
            $('#containerPoStream').removeClass('d-none');
            $('#containerPrStream').addClass('d-none');
        });

        let purchasingTrendChartInstance = null;
        let deptDonutChartInstance = null;

        function getIsDarkMode() {
            return document.documentElement.getAttribute('data-bs-theme') === 'dark' || 
                   document.body.getAttribute('data-bs-theme') === 'dark' || 
                   localStorage.getItem('theme') === 'dark';
        }

        function getChartThemeColors() {
            const isDark = getIsDarkMode();
            return {
                isDark: isDark,
                gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)',
                tickColor: isDark ? '#cbd5e1' : '#334155',
                legendColor: isDark ? '#f8fafc' : '#0f172a'
            };
        }

        function applyChartsTheme() {
            const theme = getChartThemeColors();
            if (purchasingTrendChartInstance) {
                purchasingTrendChartInstance.options.plugins.legend.labels.color = theme.legendColor;
                if (purchasingTrendChartInstance.options.scales.x) {
                    purchasingTrendChartInstance.options.scales.x.ticks.color = theme.tickColor;
                }
                if (purchasingTrendChartInstance.options.scales.y) {
                    purchasingTrendChartInstance.options.scales.y.ticks.color = theme.tickColor;
                    purchasingTrendChartInstance.options.scales.y.grid.color = theme.gridColor;
                }
                if (purchasingTrendChartInstance.options.scales.y1) {
                    purchasingTrendChartInstance.options.scales.y1.ticks.color = theme.tickColor;
                }
                purchasingTrendChartInstance.update();
            }
            if (deptDonutChartInstance) {
                deptDonutChartInstance.options.plugins.legend.labels.color = theme.legendColor;
                deptDonutChartInstance.update();
            }
        }

        // ── SPEND & FREQUENCY MULTI-AXIS CHART ──
        const trendCanvas = document.getElementById('purchasingTrendChart');
        if (trendCanvas) {
            const chartCtx = trendCanvas.getContext('2d');
            const theme = getChartThemeColors();
            
            // Glowing Blue Area Gradient
            const gradientBlue = chartCtx.createLinearGradient(0, 0, 0, 320);
            gradientBlue.addColorStop(0, theme.isDark ? 'rgba(59, 130, 246, 0.45)' : 'rgba(59, 130, 246, 0.28)');
            gradientBlue.addColorStop(0.6, theme.isDark ? 'rgba(59, 130, 246, 0.12)' : 'rgba(59, 130, 246, 0.05)');
            gradientBlue.addColorStop(1, 'rgba(59, 130, 246, 0.00)');
            
            purchasingTrendChartInstance = new Chart(chartCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Total Belanja PO (Rp)',
                            data: @json($chartData),
                            borderColor: '#3b82f6',
                            borderWidth: 3.5,
                            backgroundColor: gradientBlue,
                            fill: true,
                            tension: 0.38,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: '#1d4ed8',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'Volume Order (PO)',
                            data: @json($chartOrderCounts),
                            backgroundColor: theme.isDark ? 'rgba(6, 182, 212, 0.35)' : 'rgba(6, 182, 212, 0.25)',
                            hoverBackgroundColor: 'rgba(6, 182, 212, 0.65)',
                            borderColor: 'rgba(6, 182, 212, 0.7)',
                            borderWidth: 1.5,
                            borderRadius: 8,
                            barThickness: 26,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                color: theme.legendColor,
                                font: { size: 11, weight: '700', family: 'Inter, system-ui, sans-serif' },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            padding: 14,
                            cornerRadius: 14,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#ffffff',
                            titleFont: { size: 13, weight: '700' },
                            bodyColor: '#e2e8f0',
                            bodyFont: { size: 12, weight: '500' },
                            borderColor: 'rgba(59, 130, 246, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw;
                                    if (context.datasetIndex === 0) {
                                        return ' Total Belanja: Rp ' + val.toLocaleString('id-ID');
                                    } else {
                                        return ' Volume Dipesan: ' + val + ' Dokumen PO';
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: theme.gridColor },
                            ticks: {
                                color: theme.tickColor,
                                font: { size: 10.5, weight: '600' },
                                callback: function(val) {
                                    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                                    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' Jt';
                                    return 'Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                color: theme.tickColor,
                                font: { size: 10.5, weight: '600' },
                                stepSize: 1,
                                callback: function(val) {
                                    return val + ' PO';
                                }
                            }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { color: theme.tickColor, font: { size: 11, weight: '700' } }
                        }
                    }
                }
            });
        }

        // ── DEPARTMENT DONUT CHART ──
        const deptCanvas = document.getElementById('deptDonutChart');
        if (deptCanvas) {
            const theme = getChartThemeColors();
            deptDonutChartInstance = new Chart(deptCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($deptLabels),
                    datasets: [{
                        data: @json($deptCounts),
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#8b5cf6',
                            '#06b6d4',
                            '#ec4899'
                        ],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                color: theme.legendColor,
                                font: { size: 10.5, weight: '700', family: 'Inter, system-ui, sans-serif' },
                                usePointStyle: true,
                                padding: 12
                            }
                        },
                        tooltip: {
                            padding: 12,
                            cornerRadius: 12,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.raw + ' Pengajuan PR';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Observer for live theme toggle
        const observer = new MutationObserver(function() {
            applyChartsTheme();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        $('#themeToggle').on('click', function() {
            setTimeout(applyChartsTheme, 50);
        });

    });
</script>
@endpush
