<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\StokGudang;
use App\Models\GoodsReceipt;
use App\Models\DeliveryOrder;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\UserActivityLog;
use App\Models\SpkProduksi;
use App\Models\SpkProduksiTahapan;
use App\Models\SpkPenerimaanBarangJadi;
use App\Models\SpkPengeluaranBahan;
use App\Models\SpkProduksiLogHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MainDashboardController extends Controller
{
    /**
     * Display the Main Dashboard with Role-Based Intelligence
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization check (Admin / Purchasing / Inventory / Produksi / Sales / Keuangan)
        $canAdmin   = false;
        $canInv     = false;
        $canPur     = false;
        $canProd    = false;
        $canSales   = false;
        $canFinance = false;

        try { $canAdmin   = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard'); } catch (\Throwable $e) {}
        try { $canInv     = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-inventori'); } catch (\Throwable $e) {}
        try { $canPur     = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-purchasing'); } catch (\Throwable $e) {}
        try { $canProd    = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-produksi'); } catch (\Throwable $e) {}
        try { $canSales   = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-sales'); } catch (\Throwable $e) {}
        try { $canFinance = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-keuangan'); } catch (\Throwable $e) {}

        if (!$canAdmin && !$canInv && !$canPur && !$canProd && !$canSales && !$canFinance) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Dashboard ERP.');
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $userFullName    = $user->nama_lengkap ?? $user->username ?? 'User';
        $appName         = config('app.name', 'ERP Pastac');

        // 2. Generate / Retrieve Welcome Summary
        $summaryText = $this->getWelcomeSummary($user, $activeBranchId, $appName);

        // 3. Determine Active Dashboard View Strictly by User's Permissions
        $roleParam = $request->get('role');

        if ($user->hasRole('Super Admin') || $user->hasRole('Owner')) {
            if ($roleParam && in_array($roleParam, ['inventori', 'inventory', 'gudang'])) {
                $dashboardType = 'inventori';
            } elseif ($roleParam === 'purchasing') {
                $dashboardType = 'purchasing';
            } elseif ($roleParam === 'produksi') {
                $dashboardType = 'produksi';
            } elseif ($roleParam === 'sales') {
                return redirect()->route('sales.dashboard');
            } elseif ($roleParam === 'keuangan' || $roleParam === 'finance') {
                return redirect()->route('finance.dashboard');
            } else {
                $dashboardType = 'admin';
            }
        } else {
            // User Reguler: Strictly tampilkan dashboard sesuai hak akses dan cegah akses tanpa izin
            $userHasAdmin   = $canAdmin;
            $userHasInv     = $canInv;
            $userHasPur     = $canPur;
            $userHasProd    = $canProd;
            $userHasSales   = $canSales;
            $userHasFinance = $canFinance;

            if ($roleParam === 'inventori' && $userHasInv) {
                $dashboardType = 'inventori';
            } elseif ($roleParam === 'purchasing' && $userHasPur) {
                $dashboardType = 'purchasing';
            } elseif ($roleParam === 'produksi' && $userHasProd) {
                $dashboardType = 'produksi';
            } elseif ($roleParam === 'sales' && $userHasSales) {
                return redirect()->route('sales.dashboard');
            } elseif (($roleParam === 'keuangan' || $roleParam === 'finance') && $userHasFinance) {
                return redirect()->route('finance.dashboard');
            } elseif ($roleParam === 'admin' && $userHasAdmin) {
                $dashboardType = 'admin';
            } else {
                if ($userHasAdmin) {
                    $dashboardType = 'admin';
                } elseif ($userHasInv) {
                    $dashboardType = 'inventori';
                } elseif ($userHasPur) {
                    $dashboardType = 'purchasing';
                } elseif ($userHasProd) {
                    $dashboardType = 'produksi';
                } elseif ($userHasSales) {
                    return redirect()->route('sales.dashboard');
                } elseif ($userHasFinance) {
                    return redirect()->route('finance.dashboard');
                } else {
                    abort(403, 'Anda tidak memiliki hak akses untuk melihat Dashboard ERP.');
                }
            }
        }

        // 4. Load Specific Dashboard Data
        if ($dashboardType === 'inventori') {
            $inventoryData = $this->getInventoryDashboardData($activeCompanyId, $activeBranchId);

            // Context-aware inventory summary
            $lowStock = (int) ($inventoryData['totalLowStock'] ?? 0);
            if ($lowStock > 0) {
                $summaryText = 'Ringkasan Operasional: Terdapat <strong class="text-warning fw-bold">' . $lowStock . '</strong> item stok menipis yang memerlukan perhatian dan tindak lanjut Anda.';
            } else {
                $summaryText = 'Seluruh stok persediaan barang berada dalam batas aman dan terkendali. Belum ada antrian restock mendesak.';
            }

            return view('main_dashboard.inventori.index', array_merge([
                'appName'                 => $appName,
                'userFullName'            => $userFullName,
                'summaryText'             => $summaryText,
                'dashboardType'           => $dashboardType,
                'hasAdminPermission'      => $canAdmin,
                'hasPurchasingPermission' => $canPur,
                'hasInventoryPermission'  => $canInv,
                'hasProductionPermission' => $canProd,
                'hasSalesPermission'      => $canSales,
                'hasFinancePermission'    => $canFinance,
            ], $inventoryData));
        }

        if ($dashboardType === 'produksi') {
            $productionData = $this->getProductionDashboardData($activeCompanyId, $activeBranchId);
            $summaryText = $productionData['summaryText'] ?? 'Pusat komando dan monitoring realisasi SPK, efisiensi output, dan kontrol mutu produksi.';

            return view('main_dashboard.produksi.dashboard_produksi', array_merge([
                'appName'                 => $appName,
                'userFullName'            => $userFullName,
                'summaryText'             => $summaryText,
                'dashboardType'           => $dashboardType,
                'hasAdminPermission'      => $canAdmin,
                'hasPurchasingPermission' => $canPur,
                'hasInventoryPermission'  => $canInv,
                'hasProductionPermission' => $canProd,
                'hasSalesPermission'      => $canSales,
                'hasFinancePermission'    => $canFinance,
            ], $productionData));
        }

        $purchasingData = [];
        if ($dashboardType === 'purchasing') {
            $purchasingData = $this->getPurchasingDashboardData($activeCompanyId, $activeBranchId);
        }

        return view('main_dashboard.dashboard', array_merge([
            'appName'                 => $appName,
            'userFullName'            => $userFullName,
            'summaryText'             => $summaryText,
            'dashboardType'           => $dashboardType,
            'isPurchasingDashboard'   => ($dashboardType === 'purchasing'),
            'hasAdminPermission'      => $canAdmin,
            'hasPurchasingPermission' => $canPur,
            'hasInventoryPermission'  => $canInv,
            'hasProductionPermission' => $canProd,
            'hasSalesPermission'      => $canSales,
            'hasFinancePermission'    => $canFinance,
        ], $purchasingData));
    }

    /**
     * Dedicated Endpoint for Production Dashboard
     */
    public function dashboardProduksi(Request $request)
    {
        $user = auth()->user();
        $canProd = false;
        try {
            $canProd = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-produksi');
        } catch (\Throwable $e) {}

        if (!$canProd) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk melihat Dashboard Produksi.');
        }

        $request->merge(['role' => 'produksi']);
        return $this->index($request);
    }

    /**
     * Compute / Cache Dynamic Welcome Summary for User
     */
    private function getWelcomeSummary($user, $activeBranchId, $appName): string
    {
        $cacheKey = "dashboard_welcome_summary_v3_user_{$user->id}_branch_{$activeBranchId}";

        return Cache::remember($cacheKey, 60, function () use ($user, $activeBranchId, $appName) {
            $summaryItems = [];

            // 1. Purchasing Summary (PR Pending)
            if ($user->can('lihat-pr') || $user->can('lihat-dashboard-purchasing') || $user->hasRole('Super Admin')) {
                try {
                    $pendingPrCount = DB::table('purchase_requisitions')
                        ->whereNull('deleted_at')
                        ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'))
                        ->where('status', 'Pending')
                        ->count();

                    if ($pendingPrCount > 0) {
                        $summaryItems[] = '<strong class="text-danger fw-bold">' . $pendingPrCount . '</strong> pengajuan PR pending';
                    }
                } catch (\Throwable $e) {
                    // Ignore gracefully
                }
            }

            // 2. Inventory Summary (Low Stock)
            if ($user->can('lihat-barang') || $user->can('lihat-dashboard-inventori') || $user->can('lihat-stok-gudang') || $user->can('lihat-dashboard') || $user->hasRole('Super Admin')) {
                try {
                    $gudangQuery = MasterGudang::query()->where('status', 'Active');
                    if (!empty($activeBranchId)) {
                        $gudangQuery->where('cabang_id', $activeBranchId);
                    } else {
                        $gudangQuery->whereNull('cabang_id');
                    }
                    $gudangIds = $gudangQuery->pluck('id')->toArray();

                    $lowStockCount = StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                        ->whereIn('stok_gudangs.gudang_id', $gudangIds)
                        ->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
                        ->where('master_barang.minimum_stok', '>', 0)
                        ->whereNull('master_barang.deleted_at')
                        ->count();

                    if ($lowStockCount > 0) {
                        $summaryItems[] = '<strong class="text-warning fw-bold">' . $lowStockCount . '</strong> item stok menipis';
                    }
                } catch (\Throwable $e) {
                    // Ignore gracefully
                }
            }

            // 3. Sales Summary (Tenders Pending)
            if ($user->can('lihat-tender') || $user->hasRole('Super Admin')) {
                try {
                    $pendingTenders = DB::table('tenders')
                        ->whereNull('deleted_at')
                        ->where('status', 'Pending')
                        ->count();

                    if ($pendingTenders > 0) {
                        $summaryItems[] = '<strong class="text-primary fw-bold">' . $pendingTenders . '</strong> penawaran tender aktif';
                    }
                } catch (\Throwable $e) {
                    // Ignore gracefully
                }
            }

            // 4. Security Summary (Failed Logins)
            if ($user->can('lihat-hak-akses') || $user->hasRole('Super Admin')) {
                try {
                    $failedLogins = UserActivityLog::where('activity', 'Login')
                        ->where('status', 'Failed')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->count();

                    if ($failedLogins > 0) {
                        $summaryItems[] = '<strong class="text-danger fw-bold">' . $failedLogins . '</strong> login gagal (24 jam)';
                    }
                } catch (\Throwable $e) {
                    // Ignore gracefully
                }
            }

            // Build Professional Indonesian Executive Sentence
            if (count($summaryItems) > 0) {
                if (count($summaryItems) === 1) {
                    return 'Ringkasan Operasional: Terdapat ' . $summaryItems[0] . ' yang memerlukan perhatian dan tindak lanjut Anda.';
                } else {
                    $lastItem = array_pop($summaryItems);
                    return 'Ringkasan Operasional: Terdapat ' . implode(', ', $summaryItems) . ', serta ' . $lastItem . ' yang memerlukan perhatian dan tindak lanjut Anda.';
                }
            }

            return 'Seluruh sistem operasional berjalan lancar dan terkendali. Belum ada antrian tugas mendesak yang membutuhkan tindakan Anda saat ini.';
        });
    }

    /**
     * Apply Tenant (Cabang vs PT Pusat) Scope safely to any query
     */
    private function applyTenantScope($query, ?int $activeBranchId, ?int $activeCompanyId, string $branchCol = 'branch_id', string $compCol = 'company_id')
    {
        if (!empty($activeBranchId)) {
            return $query->where($branchCol, $activeBranchId);
        }

        $query->whereNull($branchCol);
        if (!empty($activeCompanyId)) {
            $query->where($compCol, $activeCompanyId);
        } else {
            // Defense-in-depth: Cegah kebocoran data antar-PT jika sesi active_company_id kosong
            $query->where($compCol, 0);
        }

        return $query;
    }

    /**
     * Fetch & Cache Purchasing Analytics Data
     */
    public function getPurchasingDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "purchasing_controller_v7_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 180, function () use ($activeCompanyId, $activeBranchId) {

            // ── 1. Date Boundaries (Current Month) ──
            $startOfMonth     = now()->startOfMonth()->toDateString();
            $endOfMonth       = now()->endOfMonth()->toDateString();
            $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth   = now()->subMonth()->endOfMonth()->toDateString();

            // ── 2. Base PR Aggregation (Current Month for high performance on large datasets) ──
            $prBase = DB::table('purchase_requisitions')->whereNull('deleted_at');
            $this->applyTenantScope($prBase, $activeBranchId, $activeCompanyId, 'branch_id', 'company_id');

            $prMonthBase = (clone $prBase)
                ->where(function($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('tanggal_pr', [$startOfMonth, $endOfMonth])
                      ->orWhere(function($sq) use ($startOfMonth, $endOfMonth) {
                          $sq->whereNull('tanggal_pr')->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59']);
                      });
                });

            $prStats = (clone $prMonthBase)
                ->selectRaw("status, COUNT(*) as cnt")
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $prPending  = (int) ($prStats['Pending']  ?? 0);
            $prApproved = (int) ($prStats['Approved'] ?? 0);
            $prDraft    = (int) ($prStats['Draft'] ?? 0);
            $prRejected = (int) ($prStats['Rejected'] ?? 0);
            $prTotal    = (int) $prStats->sum();

            // ── 3. Base PO Aggregation (Strictly Scoped for Realized & Released Spend) ──
            $poBase = DB::table('purchase_orders')->whereNull('deleted_at');
            $this->applyTenantScope($poBase, $activeBranchId, $activeCompanyId, 'branch_id', 'company_id');

            $poMonthAgg = (clone $poBase)
                ->whereBetween('tanggal_po', [$startOfMonth, $endOfMonth])
                ->selectRaw(
                    "COUNT(CASE WHEN status IN ('Received', 'Completed') THEN 1 END) as completed_orders,"
                    . " SUM(CASE WHEN status IN ('Received', 'Completed') THEN grand_total_idr ELSE 0 END) as completed_spend,"
                    . " SUM(CASE WHEN status NOT IN ('Draft', 'Rejected', 'Pending') THEN grand_total_idr ELSE 0 END) as total_released_spend,"
                    . " SUM(CASE WHEN status IN ('Ordered', 'Partially Received') THEN 1 ELSE 0 END) as active_orders,"
                    . " COUNT(CASE WHEN status NOT IN ('Draft', 'Rejected', 'Pending') THEN 1 END) as total_released"
                )
                ->first();

            $poReleasedThisMonth = (int)   ($poMonthAgg->total_released ?? 0);
            $poSpendThisMonth    = (float) (($poMonthAgg->total_released_spend ?? 0) > 0 ? $poMonthAgg->total_released_spend : ($poMonthAgg->completed_spend ?? 0.0));
            $poActiveOrders      = (int)   ($poMonthAgg->active_orders   ?? 0);
            $poCompletedOrders   = (int)   ($poMonthAgg->completed_orders ?? 0);

            // PO Last Month Spend for Growth % (Released Orders)
            $poSpendLastMonth = (float) ((clone $poBase)
                ->whereBetween('tanggal_po', [$startOfLastMonth, $endOfLastMonth])
                ->whereNotIn('status', ['Draft', 'Rejected', 'Pending'])
                ->sum('grand_total_idr') ?? 0.0);
            $poLastMonthSpend = $poSpendLastMonth;

            $spendGrowthPct = 0;
            if ($poSpendLastMonth > 0) {
                $spendGrowthPct = round((($poSpendThisMonth - $poSpendLastMonth) / $poSpendLastMonth) * 100, 1);
            }

            // ── 4. Supplier Ecosystem ──
            $supplierCount = DB::table('master_suppliers')
                ->whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->count();

            // ── 5. Recent PRs (10 Rows, sorted latest date & ID) ──
            $recentPrsQuery = PurchaseRequisition::with([
                'department:id,department_name',
                'requester:id,nama_lengkap',
                'creator:id,username,nama_lengkap'
            ])->whereNull('deleted_at');
            $this->applyTenantScope($recentPrsQuery, $activeBranchId, $activeCompanyId, 'branch_id', 'company_id');

            $recentPrs = $recentPrsQuery
                ->orderBy('tanggal_pr', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // ── 6. Recent POs (10 Rows, sorted latest date & ID) ──
            $recentPosQuery = PurchaseOrder::with([
                'supplier:id,nama_supplier',
                'currency:id,kode_currency,simbol'
            ])->whereNull('deleted_at');
            $this->applyTenantScope($recentPosQuery, $activeBranchId, $activeCompanyId, 'branch_id', 'company_id');

            $recentPos = $recentPosQuery
                ->orderBy('tanggal_po', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // ── 7. Critical Low-Stock (10 Rows, Strict Branch vs Pusat Warehouse) ──
            $criticalStockQuery = DB::table('stok_gudangs')
                ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
                ->select(
                    'master_barang.id as barang_id',
                    'master_barang.nama_barang',
                    'master_barang.kode_barang',
                    'master_barang.minimum_stok',
                    'stok_gudangs.stok_aktual',
                    'master_gudang.nama_gudang'
                )
                ->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
                ->where('master_barang.minimum_stok', '>', 0)
                ->whereNull('master_barang.deleted_at');

            if (!empty($activeBranchId)) {
                $criticalStockQuery->where('master_gudang.cabang_id', $activeBranchId);
            } else {
                $criticalStockQuery->whereNull('master_gudang.cabang_id');
                if (!empty($activeCompanyId)) {
                    $criticalStockQuery->where('master_gudang.company_id', $activeCompanyId);
                } else {
                    $criticalStockQuery->where('master_gudang.company_id', 0);
                }
            }

            $criticalStock = (clone $criticalStockQuery)
                ->orderBy('stok_gudangs.stok_aktual', 'asc')
                ->limit(10)
                ->get();

            $criticalStockCount = (clone $criticalStockQuery)->count();

            // ── 8. Top 10 Suppliers by Total Spend (Ordered, Partially Received, Received, Completed) ──
            $popularSuppliersQuery = DB::table('purchase_orders as po')
                ->join('master_suppliers as ms', 'po.supplier_id', '=', 'ms.id')
                ->select(
                    'po.supplier_id',
                    'ms.nama_supplier',
                    'ms.kode_supplier',
                    DB::raw("COUNT(*) as total_orders"),
                    DB::raw("SUM(po.grand_total_idr) as total_spend")
                )
                ->whereNull('po.deleted_at')
                ->whereNotIn('po.status', ['Draft', 'Rejected', 'Cancelled', 'Pending']);
            $this->applyTenantScope($popularSuppliersQuery, $activeBranchId, $activeCompanyId, 'po.branch_id', 'po.company_id');

            $popularSuppliers = $popularSuppliersQuery
                ->groupBy('po.supplier_id', 'ms.nama_supplier', 'ms.kode_supplier')
                ->having('total_spend', '>', 0)
                ->orderByDesc('total_spend')
                ->limit(10)
                ->get();

            // ── 9. Monthly 6-Month Chart Data (All Released Orders: Ordered, Received, Completed) ──
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth()->toDateString();
            $monthlyStatsQuery = DB::table('purchase_orders')
                ->whereNull('deleted_at')
                ->where('tanggal_po', '>=', $sixMonthsAgo)
                ->whereNotIn('status', ['Draft', 'Rejected', 'Cancelled', 'Pending']);
            $this->applyTenantScope($monthlyStatsQuery, $activeBranchId, $activeCompanyId, 'branch_id', 'company_id');

            $monthlyStats = $monthlyStatsQuery->selectRaw(
                    "DATE_FORMAT(tanggal_po, '%Y-%m') as po_month,"
                    . " COUNT(*) as total_orders,"
                    . " SUM(grand_total_idr) as total_spend"
                )
                ->groupByRaw("DATE_FORMAT(tanggal_po, '%Y-%m')")
                ->get()
                ->keyBy('po_month');

            $chartLabels      = [];
            $chartData        = [];
            $chartOrderCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key  = $date->format('Y-m');
                $chartLabels[]      = $date->translatedFormat('M Y');
                $stat = $monthlyStats->get($key);
                $chartData[]        = $stat ? (float) $stat->total_spend  : 0.0;
                $chartOrderCounts[] = $stat ? (int)   $stat->total_orders : 0;
            }

            // ── 10. Department Spend Distribution (Current Month for optimal performance) ──
            $deptStatsQuery = DB::table('purchase_requisitions as pr')
                ->leftJoin('departments as d', 'pr.departemen_id', '=', 'd.id')
                ->whereNull('pr.deleted_at');
            $this->applyTenantScope($deptStatsQuery, $activeBranchId, $activeCompanyId, 'pr.branch_id', 'pr.company_id');

            $deptStats = $deptStatsQuery->where(function($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('pr.tanggal_pr', [$startOfMonth, $endOfMonth])
                      ->orWhere(function($sq) use ($startOfMonth, $endOfMonth) {
                          $sq->whereNull('pr.tanggal_pr')->whereBetween('pr.created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59']);
                      });
                })
                ->selectRaw("COALESCE(d.department_name, 'Umum / Lainnya') as dept_name, COUNT(*) as pr_count")
                ->groupBy('dept_name')
                ->orderByDesc('pr_count')
                ->limit(5)
                ->get();

            $deptLabels = $deptStats->pluck('dept_name')->toArray();
            $deptCounts = $deptStats->pluck('pr_count')->toArray();
            if (empty($deptLabels)) {
                $deptLabels = ['Produksi', 'Gudang & Logistik', 'Maintenance', 'Umum & GA'];
                $deptCounts = [0, 0, 0, 0];
            }

            // ── 11. Supplier Payables & Due Invoices Radar (Accounts Payable Intelligence) ──
            $ppdSub = DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
                ->whereNull('pp.deleted_at')
                ->where('pp.status', 'Posted');
            $this->applyTenantScope($ppdSub, $activeBranchId, $activeCompanyId, 'pp.branch_id', 'pp.company_id');
            $ppdSub->groupBy('ppd.purchase_invoice_id')
                ->select('ppd.purchase_invoice_id', DB::raw("SUM(ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)) as total_bayar"));

            $payableQuery = DB::table('purchase_invoices as pi')
                ->leftJoinSub($ppdSub, 'pay_inv', 'pi.id', '=', 'pay_inv.purchase_invoice_id')
                ->join('master_suppliers as ms', 'pi.supplier_id', '=', 'ms.id')
                ->whereNull('pi.deleted_at')
                ->whereNotIn('pi.status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval']);
            $this->applyTenantScope($payableQuery, $activeBranchId, $activeCompanyId, 'pi.branch_id', 'pi.company_id');

            $payableSummary = (clone $payableQuery)
                ->selectRaw("
                    COALESCE(SUM(GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1))), 0) as total_utang,
                    COALESCE(SUM(CASE WHEN pi.tanggal_jatuh_tempo < CURDATE() THEN GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1)) ELSE 0 END), 0) as total_overdue,
                    COUNT(CASE WHEN pi.tanggal_jatuh_tempo < CURDATE() AND (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN 1 END) as count_overdue,
                    COUNT(CASE WHEN pi.tanggal_jatuh_tempo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN 1 END) as count_due_soon
                ")
                ->first();

            $totalUtangBelumLunas = (float) ($payableSummary->total_utang ?? 0);
            $totalUtangOverdue    = (float) ($payableSummary->total_overdue ?? 0);
            $countOverdueInvoices = (int)   ($payableSummary->count_overdue ?? 0);
            $countDueSoonInvoices = (int)   ($payableSummary->count_due_soon ?? 0);

            // 5 Tagihan Prioritas (Jatuh tempo terdekat / sudah lewat jatuh tempo dan belum lunas)
            $dueInvoices = (clone $payableQuery)
                ->select(
                    'pi.id',
                    'pi.nomor_faktur',
                    'pi.nomor_faktur_supplier',
                    'pi.tanggal_faktur',
                    'pi.tanggal_jatuh_tempo',
                    'ms.nama_supplier',
                    DB::raw("GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1)) as sisa_tagihan"),
                    DB::raw("DATEDIFF(pi.tanggal_jatuh_tempo, CURDATE()) as days_diff")
                )
                ->having('sisa_tagihan', '>', 0.01)
                ->orderBy('pi.tanggal_jatuh_tempo', 'asc')
                ->limit(10)
                ->get();

            return [
                'poSpendThisMonth'     => $poSpendThisMonth,
                'poSpendLastMonth'     => $poSpendLastMonth,
                'poLastMonthSpend'     => $poSpendLastMonth,
                'spendGrowthPct'       => $spendGrowthPct,
                'poReleasedThisMonth'  => $poReleasedThisMonth,
                'poActiveOrders'       => $poActiveOrders,
                'poCompletedOrders'    => $poCompletedOrders,
                'prPending'            => $prPending,
                'prApproved'           => $prApproved,
                'prDraft'              => $prDraft,
                'prRejected'           => $prRejected,
                'prTotal'              => $prTotal,
                'supplierCount'        => $supplierCount,
                'recentPrs'            => $recentPrs,
                'recentPos'            => $recentPos,
                'criticalStock'        => $criticalStock,
                'criticalStockCount'   => $criticalStockCount,
                'popularSuppliers'     => $popularSuppliers,
                'chartLabels'          => $chartLabels,
                'chartData'            => $chartData,
                'chartOrderCounts'     => $chartOrderCounts,
                'deptLabels'           => $deptLabels,
                'deptCounts'           => $deptCounts,
                'totalUtangBelumLunas' => $totalUtangBelumLunas,
                'totalUtangOverdue'    => $totalUtangOverdue,
                'countOverdueInvoices' => $countOverdueInvoices,
                'countDueSoonInvoices' => $countDueSoonInvoices,
                'dueInvoices'          => $dueInvoices,
            ];
        });
    }

    /**
     * Compute Inventory Dashboard Metrics & Analytics (Safe with Caching & B-Tree Index)
     */
    public function getInventoryDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "inventory_controller_v5_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 180, function () use ($activeCompanyId, $activeBranchId) {
            $isBranchSession = !empty($activeBranchId);
            $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
            $activeCompany   = $activeCompanyId ? Company::find($activeCompanyId) : null;

        $entityName = $isBranchSession
            ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
            : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

        // Query Gudang sesuai sesi (PT Pusat vs Cabang)
        $gudangQuery = MasterGudang::query()->where('status', 'Active');
        if ($isBranchSession) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
            if (!empty($activeCompanyId)) {
                $gudangQuery->where('company_id', $activeCompanyId);
            } else {
                $gudangQuery->where('company_id', 0);
            }
        }
        $gudangIds = $gudangQuery->pluck('id')->toArray();
        $totalGudang = count($gudangIds);

        // Total Master SKU Aktif (Strictly scoped by Company)
        $totalSku = MasterBarang::whereNull('deleted_at')
            ->where('status', 'Active')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->count();

        // Jika tidak ada gudang aktif di entitas ini, kembalikan metrik nol yang aman dan cepat
        if (empty($gudangIds)) {
            return [
                'isBranchSession'      => $isBranchSession,
                'entityName'           => $entityName,
                'totalValuasi'         => 0.0,
                'totalStokFisik'       => 0,
                'totalSku'             => $totalSku,
                'totalLowStock'        => 0,
                'totalGudang'          => 0,
                'distribusiGudang'     => collect(),
                'criticalStockList'    => collect(),
                'kategoriLabels'       => [],
                'kategoriData'         => [],
                'chartMutasiMasuk'     => array_fill(0, 12, 0),
                'chartMutasiKeluar'    => array_fill(0, 12, 0),
                'recentInbound'        => collect(),
                'recentOutbound'       => collect(),
                'latestOpname'         => null,
                'opnameStats'          => [
                    'totalItemOpname'   => 0,
                    'matchingItems'     => 0,
                    'selisihLebih'      => 0,
                    'selisihKurang'     => 0,
                    'accuracyRatio'     => 100.0,
                    'activeOpnameCount' => 0,
                ],
                'topDiscrepancies'     => collect(),
            ];
        }

        // Valuasi & Total Kuantitas Stok Fisik
        $stokStats = StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->whereNull('master_barang.deleted_at')
            ->whereIn('stok_gudangs.gudang_id', $gudangIds)
            ->selectRaw("
                COALESCE(SUM(stok_gudangs.stok_aktual), 0) as total_qty,
                COALESCE(SUM(CASE WHEN stok_gudangs.nilai_persediaan > 0 THEN stok_gudangs.nilai_persediaan ELSE (stok_gudangs.stok_aktual * COALESCE(stok_gudangs.harga_pokok_rata_rata, 0)) END), 0) as total_valuasi,
                COUNT(DISTINCT stok_gudangs.barang_id) as total_sku_in_stock
            ")->first();

        $totalValuasi = (float) ($stokStats->total_valuasi ?? 0);
        $totalStokFisik = (int) ($stokStats->total_qty ?? 0);

        // Safety Stock / Reorder Point Kritis (Stok <= Minimum Buffer)
        $criticalBase = StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->whereNull('master_barang.deleted_at')
            ->whereIn('stok_gudangs.gudang_id', $gudangIds)
            ->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
            ->where('master_barang.minimum_stok', '>', 0);

        $totalLowStock = (clone $criticalBase)->count();
        $criticalStockList = (clone $criticalBase)
            ->leftJoin('master_satuan', 'master_barang.satuan_id', '=', 'master_satuan.id')
            ->select([
                'master_barang.kode_barang',
                'master_barang.nama_barang',
                'master_barang.minimum_stok',
                'stok_gudangs.stok_aktual',
                'master_satuan.nama_satuan as satuan'
            ])
            ->orderBy('stok_gudangs.stok_aktual', 'asc')
            ->limit(10)
            ->get();

        // Distribusi Nilai per Fasilitas Gudang (Single Aggregated Query, No N+1)
        $gudangAggStats = StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->whereNull('master_barang.deleted_at')
            ->whereIn('stok_gudangs.gudang_id', $gudangIds)
            ->groupBy('stok_gudangs.gudang_id')
            ->selectRaw("
                stok_gudangs.gudang_id,
                COUNT(DISTINCT stok_gudangs.barang_id) as total_item,
                COALESCE(SUM(CASE WHEN stok_gudangs.nilai_persediaan > 0 THEN stok_gudangs.nilai_persediaan ELSE (stok_gudangs.stok_aktual * COALESCE(stok_gudangs.harga_pokok_rata_rata, 0)) END), 0) as total_nilai
            ")
            ->get()
            ->keyBy('gudang_id');

        $distribusiGudang = MasterGudang::whereIn('id', $gudangIds)
            ->take(10)
            ->get()
            ->map(function ($g) use ($gudangAggStats) {
                $stat = $gudangAggStats->get($g->id);
                $g->total_item = (int) ($stat->total_item ?? 0);
                $g->total_nilai = (float) ($stat->total_nilai ?? 0);
                return $g;
            });

        // Komposisi Kategori Barang
        $kategoriLabels = [];
        $kategoriData   = [];
        try {
            $catStats = DB::table('stok_gudangs')
                ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                ->leftJoin('master_kategori_barang', 'master_barang.kategori_barang_id', '=', 'master_kategori_barang.id')
                ->whereIn('stok_gudangs.gudang_id', $gudangIds)
                ->whereNull('master_barang.deleted_at')
                ->selectRaw("
                    COALESCE(master_kategori_barang.nama_kategori, 'Umum / Lainnya') as cat_name,
                    COALESCE(SUM(CASE WHEN stok_gudangs.nilai_persediaan > 0 THEN stok_gudangs.nilai_persediaan ELSE (stok_gudangs.stok_aktual * COALESCE(stok_gudangs.harga_pokok_rata_rata, 0)) END), 0) as total_val
                ")
                ->groupBy('cat_name')
                ->orderByDesc('total_val')
                ->limit(4)
                ->get();

            $sumCatVal = $catStats->sum('total_val');
            if ($catStats->isNotEmpty() && $sumCatVal > 0) {
                foreach ($catStats as $cs) {
                    $kategoriLabels[] = $cs->cat_name;
                    $kategoriData[]   = round(($cs->total_val / $sumCatVal) * 100, 1);
                }
            }
        } catch (\Throwable $e) {}

        // ── Real Monthly Inflow & Outflow Trend from Kartu Stok (Using Index-Seek Date Range) ──
        $chartMutasiMasuk  = array_fill(0, 12, 0);
        $chartMutasiKeluar = array_fill(0, 12, 0);
        $currentYear       = (int) date('Y');
        $startOfYear       = "{$currentYear}-01-01 00:00:00";
        $endOfYear         = "{$currentYear}-12-31 23:59:59";

        try {
            $mutasiMonthly = DB::table('kartu_stoks')
                ->whereIn('gudang_id', $gudangIds)
                ->whereBetween('created_at', [$startOfYear, $endOfYear])
                ->selectRaw("
                    MONTH(created_at) as bln,
                    COALESCE(SUM(CASE WHEN LOWER(tipe) = 'masuk' THEN jumlah ELSE 0 END), 0) as total_masuk,
                    COALESCE(SUM(CASE WHEN LOWER(tipe) = 'keluar' THEN jumlah ELSE 0 END), 0) as total_keluar
                ")
                ->groupBy('bln')
                ->get();

            foreach ($mutasiMonthly as $row) {
                $monthIndex = ((int) $row->bln) - 1;
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $chartMutasiMasuk[$monthIndex]  = round((float) $row->total_masuk, 2);
                    $chartMutasiKeluar[$monthIndex] = round((float) $row->total_keluar, 2);
                }
            }
        } catch (\Throwable $e) {}

        // ── 7. Live Feed Logistik: Inbound (Barang Masuk) & Outbound (Barang Keluar) ──
        $recentInbound = GoodsReceipt::with([
            'po.supplier:id,nama_supplier',
            'gudang:id,nama_gudang,kode_gudang'
        ])
        ->whereNull('deleted_at')
        ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId))
        ->when(empty($activeBranchId), function($q) use ($activeCompanyId) {
            $q->whereNull('branch_id');
            if (!empty($activeCompanyId)) {
                $q->where('company_id', $activeCompanyId);
            } else {
                $q->where('company_id', 0);
            }
        })
        ->orderBy('tanggal_gr', 'desc')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

        $recentOutbound = DeliveryOrder::with([
            'customer:id,nama_customer',
            'gudang:id,nama_gudang,kode_gudang'
        ])
        ->whereNull('deleted_at')
        ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId))
        ->when(empty($activeBranchId), function($q) use ($activeCompanyId) {
            $q->whereNull('branch_id');
            if (!empty($activeCompanyId)) {
                $q->where('company_id', $activeCompanyId);
            } else {
                $q->where('company_id', 0);
            }
        })
        ->orderBy('tanggal_kirim', 'desc')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();

        // ── 8. Radar Audit & Akurasi Fisik Gudang (Stock Opname Intelligence - High Scale Aggregation) ──
        $opnameBaseQuery = StockOpname::whereNull('deleted_at')
            ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId))
            ->when(empty($activeBranchId), function($q) use ($activeCompanyId) {
                $q->whereNull('branch_id');
                if (!empty($activeCompanyId)) {
                    $q->where('company_id', $activeCompanyId);
                } else {
                    $q->where('company_id', 0);
                }
            });

        $activeOpnameCount = (clone $opnameBaseQuery)
            ->whereNotIn('status', ['Completed', 'Closed', 'Selesai', 'Dibatalkan', 'Rejected'])
            ->count();

        $latestOpname = (clone $opnameBaseQuery)
            ->with([
                'gudang:id,nama_gudang,kode_gudang',
            ])
            ->orderBy('tanggal_opname', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $totalItemOpname = 0;
        $matchingItems = 0;
        $selisihLebih = 0;
        $selisihKurang = 0;
        $accuracyRatio = 100.0;
        $topDiscrepancies = collect();

        if ($latestOpname) {
            // Aggregated query on database engine to prevent OOM when opname has 50k+ items
            $detailStats = DB::table('stock_opname_details')
                ->where('opname_id', $latestOpname->id)
                ->selectRaw("
                    COUNT(*) as total_item,
                    COALESCE(SUM(CASE WHEN ABS(COALESCE(selisih, 0)) < 0.0001 THEN 1 ELSE 0 END), 0) as matching_items,
                    COALESCE(SUM(CASE WHEN COALESCE(selisih, 0) > 0.0001 THEN 1 ELSE 0 END), 0) as selisih_lebih,
                    COALESCE(SUM(CASE WHEN COALESCE(selisih, 0) < -0.0001 THEN 1 ELSE 0 END), 0) as selisih_kurang
                ")
                ->first();

            $totalItemOpname = (int) ($detailStats->total_item ?? 0);
            $matchingItems   = (int) ($detailStats->matching_items ?? 0);
            $selisihLebih    = (int) ($detailStats->selisih_lebih ?? 0);
            $selisihKurang   = (int) ($detailStats->selisih_kurang ?? 0);
            $accuracyRatio   = $totalItemOpname > 0 ? round(($matchingItems / $totalItemOpname) * 100, 1) : 100.0;

            // Load top 10 discrepancies only (sub-millisecond index seek)
            $topDiscrepancies = StockOpnameDetail::with('barang:id,nama_barang,kode_barang')
                ->where('opname_id', $latestOpname->id)
                ->whereRaw('ABS(COALESCE(selisih, 0)) > 0.0001')
                ->orderByRaw('ABS(COALESCE(selisih, 0)) DESC')
                ->limit(10)
                ->get();
        }

        $opnameStats = [
            'totalItemOpname'   => $totalItemOpname,
            'matchingItems'     => $matchingItems,
            'selisihLebih'      => $selisihLebih,
            'selisihKurang'     => $selisihKurang,
            'accuracyRatio'     => $accuracyRatio,
            'activeOpnameCount' => $activeOpnameCount,
        ];

        return [
            'isBranchSession'      => $isBranchSession,
            'entityName'           => $entityName,
            'totalValuasi'         => $totalValuasi,
            'totalStokFisik'       => $totalStokFisik,
            'totalSku'             => $totalSku,
            'totalLowStock'        => $totalLowStock,
            'totalGudang'          => $totalGudang,
            'distribusiGudang'     => $distribusiGudang,
            'criticalStockList'    => $criticalStockList,
            'kategoriLabels'       => $kategoriLabels,
            'kategoriData'         => $kategoriData,
            'chartMutasiMasuk'     => $chartMutasiMasuk,
            'chartMutasiKeluar'    => $chartMutasiKeluar,
            'recentInbound'        => $recentInbound,
            'recentOutbound'       => $recentOutbound,
            'latestOpname'         => $latestOpname,
            'opnameStats'          => $opnameStats,
            'topDiscrepancies'     => $topDiscrepancies,
        ];
    });
}

    /**
     * Compute Production Dashboard Metrics & Analytics with Strict Multi-Tenant Isolation
     */
    public function getProductionDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "production_dashboard_v2_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 60, function () use ($activeCompanyId, $activeBranchId) {
            $isBranchSession = !empty($activeBranchId);
            $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
            $activeCompany   = $activeCompanyId ? Company::find($activeCompanyId) : null;

            $entityName = $isBranchSession
                ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
                : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

            $today        = now()->toDateString();
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth   = now()->endOfMonth()->toDateString();

            // 1. Base SPK query strictly scoped by active company/branch
            $spkBase = SpkProduksi::query()->forActiveCompany();

            // 2. High-Level KPI aggregates
            $totalSpkActive = (clone $spkBase)->whereIn('status', ['In Progress', 'Sedang Dikerjakan'])->count();
            $totalSpkWaiting = (clone $spkBase)->whereIn('status', ['Pending', 'Pending Approval', 'Approved', 'Draft'])->count();
            $totalSpkCompleted = (clone $spkBase)->where('status', 'Completed')->count();
            $totalSpkCompletedMonth = (clone $spkBase)->where('status', 'Completed')
                ->whereBetween('updated_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
                ->count();

            // Volumes & Output Realization (Performance Scoped for 7M+ Extreme Scale)
            // Scoped to Active SPKs (WIP) or SPKs created this month to avoid full table scanning 7M historical rows
            $qtySummary = (clone $spkBase)
                ->where(function($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereIn('status', ['In Progress', 'Sedang Dikerjakan'])
                      ->orWhereBetween('tanggal_spk', [$startOfMonth, $endOfMonth]);
                })
                ->selectRaw('
                    COALESCE(SUM(jumlah_rencana), 0) as total_rencana,
                    COALESCE(SUM(jumlah_selesai), 0) as total_selesai,
                    COALESCE(SUM(jumlah_reject), 0) as total_reject
                ')->first();

            $totalPlannedQty   = (float) ($qtySummary->total_rencana ?? 0);
            $totalFinishedQty  = (float) ($qtySummary->total_selesai ?? 0);
            $totalRejectQty    = (float) ($qtySummary->total_reject ?? 0);

            $outputRate = ($totalPlannedQty > 0) ? round(($totalFinishedQty / $totalPlannedQty) * 100, 1) : 0;
            $rejectRate = (($totalFinishedQty + $totalRejectQty) > 0) ? round(($totalRejectQty / ($totalFinishedQty + $totalRejectQty)) * 100, 1) : 0;

            // Overdue & High Priority SPKs (Index-Friendly using active statuses)
            $openStatuses = ['In Progress', 'Sedang Dikerjakan', 'Approved', 'Pending', 'Pending Approval', 'Draft'];

            $spkOverdueCount = (clone $spkBase)
                ->whereIn('status', $openStatuses)
                ->whereNotNull('target_selesai')
                ->where('target_selesai', '<', $today)
                ->count();

            $spkUrgentCount = (clone $spkBase)
                ->whereIn('status', $openStatuses)
                ->whereIn('prioritas', ['Tinggi', 'Urgent'])
                ->count();

            // Production metrics strictly focus on quantities, output rate, and quality QC (zero financial data leakage)

            // 3. Status Distribution for Donut Chart
            $statusRaw = (clone $spkBase)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $statusChart = [
                'In Progress' => ($statusRaw['In Progress'] ?? 0) + ($statusRaw['Sedang Dikerjakan'] ?? 0),
                'Approved'    => $statusRaw['Approved'] ?? 0,
                'Pending'     => ($statusRaw['Pending'] ?? 0) + ($statusRaw['Pending Approval'] ?? 0),
                'Completed'   => $statusRaw['Completed'] ?? 0,
                'Draft'       => $statusRaw['Draft'] ?? 0,
                'Cancelled'   => ($statusRaw['Cancelled'] ?? 0) + ($statusRaw['Dibatalkan'] ?? 0),
            ];

            // 4. 7-Day Output & Defect Trend (Area/Bar Chart)
            $trendDates   = [];
            $trendOutputs = [];
            $trendRejects = [];

            $last7Days = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->toDateString());

            $logQuery = DB::table('spk_produksi_log_harians as log')
                ->join('spk_produksi as spk', 'log.spk_id', '=', 'spk.id')
                ->whereNull('spk.deleted_at')
                ->whereBetween('log.tanggal', [now()->subDays(6)->toDateString(), $today]);

            if (!empty($activeBranchId)) {
                $logQuery->where('spk.branch_id', $activeBranchId)->whereNull('spk.company_id');
            } else {
                $logQuery->whereNull('spk.branch_id');
                if (!empty($activeCompanyId)) {
                    $logQuery->where('spk.company_id', $activeCompanyId);
                } else {
                    $logQuery->where('spk.company_id', 0);
                }
            }

            $logsByDate = $logQuery
                ->groupBy('log.tanggal')
                ->select(
                    'log.tanggal',
                    DB::raw('COALESCE(SUM(log.qty_hasil_hari_ini), 0) as hasil'),
                    DB::raw('COALESCE(SUM(log.qty_reject_hari_ini), 0) as reject')
                )
                ->get()
                ->keyBy('tanggal');

            foreach ($last7Days as $date) {
                $trendDates[]   = date('d M', strtotime($date));
                $trendOutputs[] = (float) ($logsByDate[$date]->hasil ?? 0);
                $trendRejects[] = (float) ($logsByDate[$date]->reject ?? 0);
            }

            // 5. Priority Breakdown Chart
            $priorityRaw = (clone $spkBase)
                ->whereNotIn('status', ['Completed', 'Cancelled', 'Dibatalkan'])
                ->select('prioritas', DB::raw('count(*) as count'))
                ->groupBy('prioritas')
                ->pluck('count', 'prioritas')
                ->toArray();

            $priorityChart = [
                'Urgent' => $priorityRaw['Urgent'] ?? 0,
                'Tinggi' => $priorityRaw['Tinggi'] ?? 0,
                'Sedang' => $priorityRaw['Sedang'] ?? 0,
                'Rendah' => $priorityRaw['Rendah'] ?? 0,
            ];

            // 6. Actionable Table 1: SPK Prioritas & Kritis / Mendekati Deadline (Top 10)
            $criticalSpkList = (clone $spkBase)
                ->with(['barangJadi.satuan', 'satuan', 'penanggungJawab', 'bom'])
                ->whereIn('status', $openStatuses)
                ->orderByRaw("CASE WHEN target_selesai < CURDATE() THEN 1 WHEN prioritas = 'Urgent' THEN 2 WHEN prioritas = 'Tinggi' THEN 3 ELSE 4 END")
                ->orderBy('target_selesai', 'asc')
                ->limit(10)
                ->get();

            // 7. Actionable Table 2: Realisasi Tahapan Produksi Berjalan (Top 10)
            $activeTahapanList = SpkProduksiTahapan::query()
                ->join('spk_produksi as spk', 'spk_produksi_tahapans.spk_id', '=', 'spk.id')
                ->whereNull('spk.deleted_at')
                ->whereIn('spk.status', ['In Progress', 'Sedang Dikerjakan'])
                ->whereIn('spk_produksi_tahapans.status', ['In Progress', 'Sedang Dikerjakan', 'Pending'])
                ->when(!empty($activeBranchId), function($q) use ($activeBranchId) {
                    $q->where('spk.branch_id', $activeBranchId)->whereNull('spk.company_id');
                }, function($q) use ($activeCompanyId) {
                    $q->whereNull('spk.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('spk.company_id', $activeCompanyId);
                    } else {
                        $q->where('spk.company_id', 0);
                    }
                })
                ->select(
                    'spk_produksi_tahapans.*',
                    'spk.nomor_spk',
                    'spk.target_selesai as spk_target_selesai'
                )
                ->orderBy('spk_produksi_tahapans.urutan', 'asc')
                ->limit(10)
                ->get();

            // 8. Actionable Table 3: Penerimaan Barang Jadi Terkini (Top 10)
            $recentFinishedGoods = SpkPenerimaanBarangJadi::query()
                ->forActiveCompany()
                ->with(['spk', 'barangJadi', 'gudang'])
                ->orderBy('tanggal_penerimaan', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // 9. Actionable Table 4: Pengeluaran Bahan Baku Terkini (Top 10)
            $recentMaterialIssues = SpkPengeluaranBahan::query()
                ->forActiveCompany()
                ->with(['spk', 'gudang'])
                ->orderBy('tanggal_pengeluaran', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // Context Summary text (Clean & Minimalist)
            if ($spkOverdueCount > 0) {
                $summaryText = '<span class="text-danger fw-semibold">' . $spkOverdueCount . ' SPK melewati tenggat.</span> Memerlukan percepatan di lini pengerjaan.';
            } elseif ($totalSpkActive > 0) {
                $summaryText = '<span class="text-primary fw-semibold">' . $totalSpkActive . ' SPK aktif</span> berjalan dengan efisiensi output <span class="text-success fw-semibold">' . $outputRate . '%</span>.';
            } else {
                $summaryText = 'Operasional lini produksi stabil dan sesuai jadwal.';
            }

            return [
                'isBranchSession'        => $isBranchSession,
                'entityName'             => $entityName,
                'summaryText'            => $summaryText,
                'totalSpkActive'         => $totalSpkActive,
                'totalSpkWaiting'        => $totalSpkWaiting,
                'totalSpkCompleted'      => $totalSpkCompleted,
                'totalSpkCompletedMonth' => $totalSpkCompletedMonth,
                'totalPlannedQty'        => $totalPlannedQty,
                'totalFinishedQty'       => $totalFinishedQty,
                'totalRejectQty'         => $totalRejectQty,
                'outputRate'             => $outputRate,
                'rejectRate'             => $rejectRate,
                'spkOverdueCount'        => $spkOverdueCount,
                'spkUrgentCount'         => $spkUrgentCount,
                'statusChart'            => $statusChart,
                'trendDates'             => $trendDates,
                'trendOutputs'           => $trendOutputs,
                'trendRejects'           => $trendRejects,
                'priorityChart'          => $priorityChart,
                'criticalSpkList'        => $criticalSpkList,
                'activeTahapanList'      => $activeTahapanList,
                'recentFinishedGoods'    => $recentFinishedGoods,
                'recentMaterialIssues'   => $recentMaterialIssues,
            ];
        });
    }

    /**
     * AJAX Endpoint: System Health & Security Metrics for Admin Dashboard
     */
    public function getSystemHealth()
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('lihat-dashboard'))) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $dbStatus = 'Disconnected';
        $dbLatency = 0;
        $dbVersion = 'Unknown';
        $dbName = config('database.connections.mysql.database', 'ERP DB');

        try {
            DB::purge('mysql');
            $queryStart = microtime(true);
            $versionResult = DB::select("SELECT VERSION() as v");
            $dbLatency = round((microtime(true) - $queryStart) * 1000, 2);
            $rawVersion = $versionResult[0]->v ?? '';
            
            // Mask database engine version for security
            if (stripos($rawVersion, 'mariadb') !== false) {
                $dbVersion = 'MariaDB Engine';
            } elseif (stripos($rawVersion, 'mysql') !== false || stripos(config('database.default'), 'mysql') !== false) {
                $dbVersion = 'MySQL Engine';
            } else {
                $dbVersion = 'SQL Database Engine';
            }
            $dbStatus = 'Connected';
        } catch (\Throwable $e) {
            $dbStatus = 'Disconnected';
            $dbLatency = 0;
            $dbVersion = 'Offline (No Connection)';
        }

        $successLogins = 0;
        $failedLogins = 0;
        $hasBruteForce = false;
        $recentFailedLogs = [];

        if ($dbStatus === 'Connected') {
            try {
                // 24 Hours Security Stats from UserActivityLog
                $last24h = now()->subHours(24);
                
                $loginLogs24h = UserActivityLog::where('created_at', '>=', $last24h)
                    ->where('activity', 'Login')
                    ->get();

                $successLogins = $loginLogs24h->where('status', 'Success')->count();
                $failedLogins = $loginLogs24h->where('status', '!=', 'Success')->count();

                // Failed attempts grouped by IP to detect brute force
                $failedByIp = $loginLogs24h->where('status', '!=', 'Success')
                    ->groupBy('ip_address')
                    ->map(fn($group) => $group->count());

                $hasBruteForce = $failedByIp->contains(fn($count) => $count >= 3) || $failedLogins >= 5;

                // Recent failed logins
                $recentFailedLogs = UserActivityLog::where('created_at', '>=', $last24h)
                    ->where('activity', 'Login')
                    ->where('status', '!=', 'Success')
                    ->orderBy('created_at', 'desc')
                    ->limit(4)
                    ->get()
                    ->map(function($log) {
                        return [
                            'username'   => $log->username ?: 'Unknown',
                            'ip'         => $log->ip_address,
                            'time'       => $log->created_at->diffForHumans(),
                            'exact_time' => $log->created_at->format('d/m/Y H:i:s'),
                            'message'    => $log->status_message ?: 'Password salah / user tidak ditemukan'
                        ];
                    });
            } catch (\Throwable $e) {
                // Silent fallback if query fails
            }
        }

        // Server Memory
        $memoryUsage = round(memory_get_usage(true) / (1024 * 1024), 2);
        $memoryLimit = ini_get('memory_limit');

        // Threat level
        $threatLevel = 'Aman';
        $threatBadge = 'bg-success-soft text-success';
        $threatIcon = 'bi-shield-check';

        if ($dbStatus === 'Disconnected') {
            $threatLevel = 'Database Offline';
            $threatBadge = 'bg-danger-soft text-danger';
            $threatIcon = 'bi-database-slash';
        } elseif ($failedLogins >= 5 || $hasBruteForce) {
            $threatLevel = 'Potensi Serangan';
            $threatBadge = 'bg-danger-soft text-danger';
            $threatIcon = 'bi-shield-slash-fill';
        } elseif ($failedLogins > 0) {
            $threatLevel = 'Perlu Perhatian';
            $threatBadge = 'bg-warning-soft text-warning';
            $threatIcon = 'bi-shield-exclamation';
        }

        return response()->json([
            'database' => [
                'status'     => $dbStatus,
                'latency_ms' => $dbLatency,
                'version'    => $dbVersion,
            ],
            'server' => [
                'runtime_name'    => 'PHP Engine',
                'environment'     => config('app.env', 'production'),
                'memory_usage_mb' => $memoryUsage,
                'memory_limit'    => $memoryLimit,
                'server_time'     => now()->format('H:i:s') . ' WIB',
            ],
            'security' => [
                'threat_level'       => $threatLevel,
                'threat_badge'       => $threatBadge,
                'threat_icon'        => $threatIcon,
                'success_logins_24h' => $successLogins,
                'failed_logins_24h'  => $failedLogins,
                'has_brute_force'    => $hasBruteForce,
                'recent_failed'      => $recentFailedLogs,
            ]
        ]);
    }
}
