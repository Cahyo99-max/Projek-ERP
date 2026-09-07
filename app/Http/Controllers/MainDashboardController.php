<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MasterSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\StokGudang;
use App\Models\UserActivityLog;
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

        // 1. Authorization check
        if (!$user->hasRole('Super Admin') && !$user->can('lihat-dashboard') && !$user->can('lihat-dashboard-purchasing')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Dashboard Utama.');
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $userFullName    = $user->nama_lengkap ?? $user->username ?? 'User';
        $appName         = config('app.name', 'ERP Pastac');

        // 2. Generate / Retrieve Welcome Summary
        $summaryText = $this->getWelcomeSummary($user, $activeBranchId, $appName);

        // 3. Determine Active Dashboard View
        $hasPurchasingPermission = false;
        try {
            $hasPurchasingPermission = $user->hasPermissionTo('lihat-dashboard-purchasing');
        } catch (\Throwable $e) {
            $hasPurchasingPermission = false;
        }

        if ($user->hasRole('Super Admin')) {
            // Super Admin defaults to Admin Dashboard unless ?role=purchasing is passed or explicitly configured
            $isPurchasingDashboard = ($request->get('role') === 'purchasing');
        } else {
            // Regular user/role
            $isPurchasingDashboard = $hasPurchasingPermission || ($request->get('role') === 'purchasing');
        }

        // 4. Load Purchasing Metrics if relevant
        $purchasingData = [];
        if ($isPurchasingDashboard) {
            $purchasingData = $this->getPurchasingDashboardData($activeCompanyId, $activeBranchId);
        }

        return view('main_dashboard.dashboard', array_merge([
            'appName'               => $appName,
            'userFullName'          => $userFullName,
            'summaryText'           => $summaryText,
            'isPurchasingDashboard' => $isPurchasingDashboard,
        ], $purchasingData));
    }

    /**
     * Compute / Cache Dynamic Welcome Summary for User
     */
    private function getWelcomeSummary($user, $activeBranchId, $appName): string
    {
        $cacheKey = "dashboard_welcome_summary_v2_user_{$user->id}_branch_{$activeBranchId}";

        return Cache::remember($cacheKey, 120, function () use ($user, $activeBranchId, $appName) {
            $summaryItems = [];

            // 1. Purchasing Summary (PR Pending)
            if ($user->can('lihat-pr') || $user->hasRole('Super Admin')) {
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
            if ($user->can('lihat-barang') || $user->hasRole('Super Admin')) {
                try {
                    $lowStockCount = StokGudang::join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                        ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
                        ->when(!empty($activeBranchId), fn($q) => $q->where('master_gudang.cabang_id', $activeBranchId))
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
     * Fetch & Cache Purchasing Analytics Data
     */
    public function getPurchasingDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "purchasing_controller_v4_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 180, function () use ($activeCompanyId, $activeBranchId) {

            // ── 1. Date Boundaries (Current Month) ──
            $startOfMonth     = now()->startOfMonth()->toDateString();
            $endOfMonth       = now()->endOfMonth()->toDateString();
            $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth   = now()->subMonth()->endOfMonth()->toDateString();

            // ── 2. Base PR Aggregation (Current Month for high performance on large datasets) ──
            $prBase = DB::table('purchase_requisitions')
                ->whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'));

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

            // ── 3. Base PO Aggregation (Strictly Received & Completed for Realized Spend) ──
            $poBase = DB::table('purchase_orders')
                ->whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'));

            $poMonthAgg = (clone $poBase)
                ->whereBetween('tanggal_po', [$startOfMonth, $endOfMonth])
                ->selectRaw(
                    "COUNT(CASE WHEN status IN ('Received', 'Completed') THEN 1 END) as completed_orders,"
                    . " SUM(CASE WHEN status IN ('Received', 'Completed') THEN grand_total_idr ELSE 0 END) as total_spend,"
                    . " SUM(CASE WHEN status IN ('Ordered', 'Partially Received') THEN 1 ELSE 0 END) as active_orders,"
                    . " COUNT(CASE WHEN status NOT IN ('Draft', 'Rejected', 'Pending') THEN 1 END) as total_released"
                )
                ->first();

            $poReleasedThisMonth = (int)   ($poMonthAgg->total_released ?? 0);
            $poSpendThisMonth    = (float) ($poMonthAgg->total_spend    ?? 0.0);
            $poActiveOrders      = (int)   ($poMonthAgg->active_orders   ?? 0);
            $poCompletedOrders   = (int)   ($poMonthAgg->completed_orders ?? 0);

            // PO Last Month Spend for Growth % (Strictly Received & Completed)
            $poSpendLastMonth = (float) ((clone $poBase)
                ->whereBetween('tanggal_po', [$startOfLastMonth, $endOfLastMonth])
                ->whereIn('status', ['Received', 'Completed'])
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
            $recentPrs = PurchaseRequisition::with([
                'department:id,department_name',
                'requester:id,nama_lengkap',
                'creator:id,username,nama_lengkap'
            ])
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'))
                ->orderBy('tanggal_pr', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // ── 6. Recent POs (10 Rows, sorted latest date & ID) ──
            $recentPos = PurchaseOrder::with([
                'supplier:id,nama_supplier',
                'currency:id,kode_currency,simbol'
            ])
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'))
                ->orderBy('tanggal_po', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // ── 7. Critical Low-Stock (10 Rows) ──
            $criticalStock = DB::table('stok_gudangs')
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
                ->when(!empty($activeBranchId), fn($q) => $q->where('master_gudang.cabang_id', $activeBranchId))
                ->when($activeCompanyId, fn($q) => $q->where('master_barang.company_id', $activeCompanyId))
                ->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
                ->where('master_barang.minimum_stok', '>', 0)
                ->whereNull('master_barang.deleted_at')
                ->orderBy('stok_gudangs.stok_aktual', 'asc')
                ->limit(10)
                ->get();

            $criticalStockCount = DB::table('stok_gudangs')
                ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
                ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
                ->when(!empty($activeBranchId), fn($q) => $q->where('master_gudang.cabang_id', $activeBranchId))
                ->when($activeCompanyId, fn($q) => $q->where('master_barang.company_id', $activeCompanyId))
                ->whereColumn('stok_gudangs.stok_aktual', '<=', 'master_barang.minimum_stok')
                ->where('master_barang.minimum_stok', '>', 0)
                ->whereNull('master_barang.deleted_at')
                ->count();

            // ── 8. Top 10 Suppliers by Total Spend (Strictly Received & Completed) ──
            $popularSuppliers = DB::table('purchase_orders as po')
                ->join('master_suppliers as ms', 'po.supplier_id', '=', 'ms.id')
                ->select(
                    'po.supplier_id',
                    'ms.nama_supplier',
                    'ms.kode_supplier',
                    DB::raw("COUNT(*) as total_orders"),
                    DB::raw("SUM(po.grand_total_idr) as total_spend")
                )
                ->whereNull('po.deleted_at')
                ->whereIn('po.status', ['Received', 'Completed'])
                ->when($activeCompanyId, fn($q) => $q->where('po.company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('po.branch_id', $activeBranchId), fn($q) => $q->whereNull('po.branch_id'))
                ->groupBy('po.supplier_id', 'ms.nama_supplier', 'ms.kode_supplier')
                ->having('total_spend', '>', 0)
                ->orderByDesc('total_spend')
                ->limit(10)
                ->get();

            // ── 9. Monthly 6-Month Chart Data (Strictly Received & Completed) ──
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth()->toDateString();
            $monthlyStats = DB::table('purchase_orders')
                ->whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', $activeBranchId), fn($q) => $q->whereNull('branch_id'))
                ->where('tanggal_po', '>=', $sixMonthsAgo)
                ->whereIn('status', ['Received', 'Completed'])
                ->selectRaw(
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
            $deptStats = DB::table('purchase_requisitions as pr')
                ->leftJoin('departments as d', 'pr.departemen_id', '=', 'd.id')
                ->whereNull('pr.deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('pr.company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('pr.branch_id', $activeBranchId), fn($q) => $q->whereNull('pr.branch_id'))
                ->where(function($q) use ($startOfMonth, $endOfMonth) {
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

            return [
                'poSpendThisMonth'    => $poSpendThisMonth,
                'poSpendLastMonth'    => $poSpendLastMonth,
                'poLastMonthSpend'    => $poSpendLastMonth,
                'spendGrowthPct'      => $spendGrowthPct,
                'poReleasedThisMonth' => $poReleasedThisMonth,
                'poActiveOrders'      => $poActiveOrders,
                'poCompletedOrders'   => $poCompletedOrders,
                'prPending'           => $prPending,
                'prApproved'          => $prApproved,
                'prDraft'             => $prDraft,
                'prRejected'          => $prRejected,
                'prTotal'             => $prTotal,
                'supplierCount'       => $supplierCount,
                'recentPrs'           => $recentPrs,
                'recentPos'           => $recentPos,
                'criticalStock'       => $criticalStock,
                'criticalStockCount'  => $criticalStockCount,
                'popularSuppliers'    => $popularSuppliers,
                'chartLabels'         => $chartLabels,
                'chartData'           => $chartData,
                'chartOrderCounts'    => $chartOrderCounts,
                'deptLabels'          => $deptLabels,
                'deptCounts'          => $deptCounts,
            ];
        });
    }

    /**
     * AJAX Endpoint: System Health & Security Metrics for Admin Dashboard
     */
    public function getSystemHealth()
    {
        $dbStatus = 'Disconnected';
        $dbLatency = 0;
        $dbVersion = 'Unknown';
        $dbName = config('database.connections.mysql.database', 'ERP DB');

        try {
            DB::purge('mysql');
            $queryStart = microtime(true);
            $versionResult = DB::select("SELECT VERSION() as v");
            $dbLatency = round((microtime(true) - $queryStart) * 1000, 2);
            $dbVersion = $versionResult[0]->v ?? 'MySQL';
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
                'name'       => $dbName,
            ],
            'server' => [
                'php_version'     => PHP_VERSION,
                'laravel_version' => app()->version(),
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
