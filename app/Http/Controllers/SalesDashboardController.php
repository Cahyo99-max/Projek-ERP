<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DeliveryOrder;
use App\Models\MasterCustomer;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SalesDashboardController extends Controller
{
    /**
     * Display the Dedicated Sales & Marketing Dashboard with Strict Multi-Tenant Isolation
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization check: Super Admin, Owner, or explicit sales permission
        $canSales = false;
        try {
            $canSales = $user->hasRole('Super Admin') 
                     || $user->hasRole('Owner') 
                     || $user->hasPermissionTo('lihat-dashboard-sales')
                     || $user->hasPermissionTo('lihat-sales-order')
                     || $user->hasPermissionTo('akses-sales');
        } catch (\Throwable $e) {}

        if (!$canSales) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk melihat Dashboard Sales & Marketing.');
        }

        // Cross-Dashboard Permissions for Header Tab Switcher
        $canAdmin   = false;
        $canInv     = false;
        $canPur     = false;
        $canProd    = false;
        $canFinance = false;

        try { $canAdmin   = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard'); } catch (\Throwable $e) {}
        try { $canInv     = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-inventori'); } catch (\Throwable $e) {}
        try { $canPur     = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-purchasing'); } catch (\Throwable $e) {}
        try { $canProd    = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-produksi'); } catch (\Throwable $e) {}
        try { $canFinance = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-keuangan'); } catch (\Throwable $e) {}

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $userFullName    = $user->nama_lengkap ?? $user->username ?? 'User';
        $appName         = config('app.name', 'ERP Pastac');

        // Optional cache refresh trigger
        if ($request->has('refresh')) {
            Cache::forget("sales_dashboard_metrics_c{$activeCompanyId}_b{$activeBranchId}");
        }

        // 2. Compute / Retrieve Scoped Sales Dashboard Data
        $salesData = $this->getSalesDashboardData($activeCompanyId, $activeBranchId);

        return view('main_dashboard.dashboard_sales.dashboard_sales', array_merge([
            'appName'                 => $appName,
            'userFullName'            => $userFullName,
            'dashboardType'           => 'sales',
            'hasAdminPermission'      => $canAdmin,
            'hasPurchasingPermission' => $canPur,
            'hasInventoryPermission'  => $canInv,
            'hasProductionPermission' => $canProd,
            'hasSalesPermission'      => true,
            'hasFinancePermission'    => $canFinance,
        ], $salesData));
    }

    /**
     * Compute Sales Analytics with Strict Multi-Tenant Separation (PT Pusat vs Cabang)
     */
    public function getSalesDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "sales_dashboard_metrics_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 180, function () use ($activeCompanyId, $activeBranchId) {
            $isBranchSession = !empty($activeBranchId);
            $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
            $activeCompany   = $activeCompanyId ? Company::find($activeCompanyId) : null;

            $entityName = $isBranchSession
                ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
                : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

            $today            = now()->toDateString();
            $startOfMonth     = now()->startOfMonth()->toDateString();
            $endOfMonth       = now()->endOfMonth()->toDateString();
            $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth   = now()->subMonth()->endOfMonth()->toDateString();

            // ── Base Queries (Strictly Multi-Tenant Scoped) ───────────────────────────
            $soBase = SalesOrder::query()->forActiveCompany();
            $siBase = SalesInvoice::query()->forActiveCompany();
            $spBase = SalesPayment::query()->forActiveCompany();
            $doBase = DeliveryOrder::query()->forActiveCompany();

            // ── KPI 1: Omset Penjualan Bulan Ini (Revenue) ────────────────────────────
            $validOrderStatuses = [
                'Confirmed', 'Processing', 'Delivered', 'Completed', 
                'Approved', 'Diproses', 'Selesai', 'Parsial'
            ];

            $revenueThisMonth = (float) (clone $soBase)
                ->whereIn('status', $validOrderStatuses)
                ->whereBetween('tanggal_so', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            $revenueLastMonth = (float) (clone $soBase)
                ->whereIn('status', $validOrderStatuses)
                ->whereBetween('tanggal_so', [$startOfLastMonth, $endOfLastMonth])
                ->sum('grand_total');

            $revenueGrowthPct = ($revenueLastMonth > 0)
                ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                : 0.0;

            // ── KPI 2: Pesanan Masuk (Order Inflow) ────────────────────────────────────
            $totalSoMonth = (clone $soBase)
                ->whereBetween('tanggal_so', [$startOfMonth, $endOfMonth])
                ->count();

            $activeSoCount = (clone $soBase)
                ->whereIn('status', ['Confirmed', 'Processing', 'Approved', 'Diproses', 'In Progress', 'Parsial'])
                ->count();

            $pendingSoCount = (clone $soBase)
                ->whereIn('status', ['Pending', 'Draft', 'Menunggu Persetujuan', 'Diajukan'])
                ->count();

            $totalItemSoldMonth = (float) DB::table('sales_order_details as sod')
                ->join('sales_orders as so', 'sod.sales_order_id', '=', 'so.id')
                ->whereNull('so.deleted_at')
                ->when($isBranchSession, function ($q) use ($activeBranchId) {
                    $q->where('so.branch_id', $activeBranchId)->whereNull('so.company_id');
                }, function ($q) use ($activeCompanyId) {
                    $q->whereNull('so.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('so.company_id', $activeCompanyId);
                    } else {
                        $q->where('so.company_id', 0);
                    }
                })
                ->whereIn('so.status', $validOrderStatuses)
                ->whereBetween('so.tanggal_so', [$startOfMonth, $endOfMonth])
                ->sum('sod.qty');

            // ── KPI 3: Piutang Usaha Belum Tertagih (Outstanding AR) ──────────────────
            $activeArQuery = (clone $siBase)
                ->whereNotIn('status', ['Draft', 'Cancelled', 'Dibatalkan', 'Lunas', 'Paid'])
                ->where('sisa_tagihan', '>', 0);

            $totalOutstandingAr = (float) (clone $activeArQuery)->sum('sisa_tagihan');

            $overdueArQuery = (clone $activeArQuery)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->where('tanggal_jatuh_tempo', '<', $today);

            $totalArOverdue = (float) (clone $overdueArQuery)->sum('sisa_tagihan');
            $countArOverdue = (clone $overdueArQuery)->count();

            // ── Chart 1: Tren Omset vs Pelunasan Kas (6 Bulan Terakhir) ───────────────
            $sixMonthsAgoStart = now()->subMonths(5)->startOfMonth()->toDateString();

            $revenueByMonth = (clone $soBase)
                ->whereIn('status', $validOrderStatuses)
                ->where('tanggal_so', '>=', $sixMonthsAgoStart)
                ->selectRaw("DATE_FORMAT(tanggal_so, '%Y-%m') as ym, SUM(grand_total) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->toArray();

            $receiptsByMonth = (clone $spBase)
                ->whereNotIn('status', ['Cancelled', 'Dibatalkan', 'Draft'])
                ->where('tanggal_bayar', '>=', $sixMonthsAgoStart)
                ->selectRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') as ym, SUM(total_bayar) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym')
                ->toArray();

            $chartMonths   = [];
            $chartRevenue  = [];
            $chartReceipts = [];

            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $ym = $dt->format('Y-m');
                $chartMonths[]   = $dt->translatedFormat('M Y');
                $chartRevenue[]  = (float) ($revenueByMonth[$ym] ?? 0);
                $chartReceipts[] = (float) ($receiptsByMonth[$ym] ?? 0);
            }

            // ── Chart 2: Status Pesanan Penjualan (Donut Chart) ───────────────────────
            $statusCountsRaw = (clone $soBase)
                ->select('status', DB::raw('count(*) as aggregate'))
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->toArray();

            $statusChart = [
                'Terkirim'     => ($statusCountsRaw['Delivered'] ?? 0) + ($statusCountsRaw['Completed'] ?? 0) + ($statusCountsRaw['Selesai'] ?? 0),
                'Diproses'     => ($statusCountsRaw['Processing'] ?? 0) + ($statusCountsRaw['Approved'] ?? 0) + ($statusCountsRaw['Diproses'] ?? 0) + ($statusCountsRaw['In Progress'] ?? 0) + ($statusCountsRaw['Parsial'] ?? 0),
                'Dikonfirmasi' => $statusCountsRaw['Confirmed'] ?? 0,
                'Pending'      => ($statusCountsRaw['Pending'] ?? 0) + ($statusCountsRaw['Draft'] ?? 0) + ($statusCountsRaw['Diajukan'] ?? 0) + ($statusCountsRaw['Menunggu Persetujuan'] ?? 0),
                'Batal'        => ($statusCountsRaw['Cancelled'] ?? 0) + ($statusCountsRaw['Dibatalkan'] ?? 0) + ($statusCountsRaw['Rejected'] ?? 0),
            ];

            // ── Table 1: Pesanan Menunggu Pengiriman (Unfulfilled SO - Top 8) ─────────
            $unfulfilledOrders = (clone $soBase)
                ->whereIn('status', ['Confirmed', 'Processing', 'Approved', 'Diproses', 'In Progress', 'Parsial'])
                ->with(['customer:id,nama_customer,kode_customer'])
                ->orderBy('tanggal_so', 'asc')
                ->limit(8)
                ->get();

            // ── Table 2: Faktur Piutang Jatuh Tempo / Mendekati Jatuh Tempo (Top 8) ───
            $dueInvoices = (clone $siBase)
                ->whereNotIn('status', ['Draft', 'Cancelled', 'Dibatalkan', 'Lunas', 'Paid'])
                ->where('sisa_tagihan', '>', 0)
                ->with(['customer:id,nama_customer,kode_customer'])
                ->orderByRaw('CASE WHEN tanggal_jatuh_tempo < CURDATE() THEN 1 ELSE 2 END')
                ->orderBy('tanggal_jatuh_tempo', 'asc')
                ->limit(8)
                ->get();

            $startOfYear = now()->startOfYear()->toDateString();

            // ── Table 3: Top 5 Pelanggan Terbesar (Year-to-Date Pruned for 7M Scale) ─
            $topCustomers = DB::table('sales_orders as so')
                ->join('master_customers as c', 'so.customer_id', '=', 'c.id')
                ->whereNull('so.deleted_at')
                ->when($isBranchSession, function ($q) use ($activeBranchId) {
                    $q->where('so.branch_id', $activeBranchId)->whereNull('so.company_id');
                }, function ($q) use ($activeCompanyId) {
                    $q->whereNull('so.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('so.company_id', $activeCompanyId);
                    } else {
                        $q->where('so.company_id', 0);
                    }
                })
                ->whereIn('so.status', $validOrderStatuses)
                ->where('so.tanggal_so', '>=', $startOfYear)
                ->groupBy('c.id', 'c.nama_customer', 'c.kode_customer')
                ->select(
                    'c.nama_customer',
                    'c.kode_customer',
                    DB::raw('COUNT(so.id) as total_transaksi'),
                    DB::raw('SUM(so.grand_total) as total_kontribusi')
                )
                ->orderByDesc('total_kontribusi')
                ->limit(5)
                ->get();

            // ── Table 4: Top 5 Produk / SKU Terlaris (Year-to-Date Pruned for 7M Scale) 
            $topProducts = DB::table('sales_order_details as sod')
                ->join('sales_orders as so', 'sod.sales_order_id', '=', 'so.id')
                ->leftJoin('master_barang as b', 'sod.barang_id', '=', 'b.id')
                ->whereNull('so.deleted_at')
                ->when($isBranchSession, function ($q) use ($activeBranchId) {
                    $q->where('so.branch_id', $activeBranchId)->whereNull('so.company_id');
                }, function ($q) use ($activeCompanyId) {
                    $q->whereNull('so.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('so.company_id', $activeCompanyId);
                    } else {
                        $q->where('so.company_id', 0);
                    }
                })
                ->whereIn('so.status', $validOrderStatuses)
                ->where('so.tanggal_so', '>=', $startOfYear)
                ->groupBy('sod.barang_id', 'b.nama_barang', 'b.kode_barang', 'sod.nama_barang_custom', 'sod.nama_satuan')
                ->select(
                    DB::raw("COALESCE(b.nama_barang, sod.nama_barang_custom, 'Produk Kustom') as item_name"),
                    DB::raw("COALESCE(b.kode_barang, '-') as item_code"),
                    'sod.nama_satuan',
                    DB::raw('SUM(sod.qty) as total_qty'),
                    DB::raw('SUM(sod.subtotal) as total_penjualan')
                )
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();

            // ── Dynamic Context Summary ──────────────────────────────────────────────
            if ($countArOverdue > 0) {
                $summaryText = '<span class="text-danger fw-semibold">' . $countArOverdue . ' faktur piutang jatuh tempo</span> senilai <span class="text-danger fw-bold">Rp ' . number_format($totalArOverdue, 0, ',', '.') . '</span> memerlukan penagihan segera.';
            } elseif ($revenueThisMonth > 0) {
                $summaryText = '<span class="text-primary fw-semibold">' . $totalSoMonth . ' pesanan masuk</span> membukukan omset <span class="text-success fw-semibold">Rp ' . number_format($revenueThisMonth, 0, ',', '.') . '</span> (' . ($revenueGrowthPct >= 0 ? '+' : '') . $revenueGrowthPct . '% vs bln lalu).';
            } else {
                $summaryText = 'Aktivitas penjualan stabil. Pantau realisasi pesanan dan pengiriman hari ini.';
            }

            return [
                'isBranchSession'      => $isBranchSession,
                'entityName'           => $entityName,
                'summaryText'          => $summaryText,
                'revenueThisMonth'     => $revenueThisMonth,
                'revenueLastMonth'     => $revenueLastMonth,
                'revenueGrowthPct'     => $revenueGrowthPct,
                'totalSoMonth'         => $totalSoMonth,
                'activeSoCount'        => $activeSoCount,
                'pendingSoCount'       => $pendingSoCount,
                'totalItemSoldMonth'   => $totalItemSoldMonth,
                'totalOutstandingAr'   => $totalOutstandingAr,
                'totalArOverdue'       => $totalArOverdue,
                'countArOverdue'       => $countArOverdue,
                'chartMonths'          => $chartMonths,
                'chartRevenue'         => $chartRevenue,
                'chartReceipts'        => $chartReceipts,
                'statusChart'          => $statusChart,
                'unfulfilledOrders'    => $unfulfilledOrders,
                'dueInvoices'          => $dueInvoices,
                'topCustomers'         => $topCustomers,
                'topProducts'          => $topProducts,
            ];
        });
    }
}
