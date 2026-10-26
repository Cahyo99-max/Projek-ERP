<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\PettyCash;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    /**
     * Display the Dedicated Financial & Accounting Dashboard with Strict Multi-Tenant Isolation
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization check: Super Admin, Owner, or explicit financial permission
        $canFinance = false;
        try {
            $canFinance = $user->hasRole('Super Admin') 
                       || $user->hasRole('Owner') 
                       || $user->hasPermissionTo('lihat-dashboard-keuangan')
                       || $user->hasPermissionTo('lihat-kas-bank')
                       || $user->hasPermissionTo('lihat-jurnal-umum')
                       || $user->hasPermissionTo('lihat-petty-cash');
        } catch (\Throwable $e) {}

        if (!$canFinance) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk melihat Dashboard Keuangan & Akuntansi.');
        }

        // Cross-Dashboard Permissions for Header Tab Switcher
        $canAdmin = false;
        $canInv   = false;
        $canPur   = false;
        $canProd  = false;
        $canSales = false;

        try { $canAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard'); } catch (\Throwable $e) {}
        try { $canInv   = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-inventori'); } catch (\Throwable $e) {}
        try { $canPur   = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-purchasing'); } catch (\Throwable $e) {}
        try { $canProd  = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-produksi'); } catch (\Throwable $e) {}
        try { $canSales = $user->hasRole('Super Admin') || $user->hasRole('Owner') || $user->hasPermissionTo('lihat-dashboard-sales'); } catch (\Throwable $e) {}

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: ($user->company_id ?? 1);

        // Security Check: Zero-Trust Multi-Tenant Authorization
        $isSuperUser = false;
        try {
            $isSuperUser = $user->hasRole('Super Admin') || $user->hasRole('Owner');
        } catch (\Throwable $e) {}

        if (!$isSuperUser) {
            if (!empty($activeBranchId)) {
                $hasBranchAccess = $user->branches()->where('branches.id', $activeBranchId)->exists();
                if (!$hasBranchAccess) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses data keuangan cabang ini.');
                }
            } elseif (!empty($activeCompanyId)) {
                $hasCompanyAccess = $user->company_id == $activeCompanyId 
                    || $user->companies()->where('companies.id', $activeCompanyId)->exists();
                if (!$hasCompanyAccess) {
                    abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengakses data keuangan perusahaan ini.');
                }
            }
        }

        $userFullName    = $user->nama_lengkap ?? $user->username ?? 'User';
        $appName         = config('app.name', 'ERP Pastac');

        // Optional cache refresh trigger
        if ($request->has('refresh')) {
            Cache::forget("finance_dashboard_metrics_c{$activeCompanyId}_b{$activeBranchId}");
        }

        // 2. Compute / Retrieve Scoped Finance Dashboard Data
        $financeData = $this->getFinanceDashboardData($activeCompanyId, $activeBranchId);

        return view('main_dashboard.keuangan.dashboard_keuangan', array_merge([
            'appName'                 => $appName,
            'userFullName'            => $userFullName,
            'dashboardType'           => 'finance',
            'hasAdminPermission'      => $canAdmin,
            'hasPurchasingPermission' => $canPur,
            'hasInventoryPermission'  => $canInv,
            'hasProductionPermission' => $canProd,
            'hasSalesPermission'      => $canSales,
            'hasFinancePermission'    => true,
        ], $financeData));
    }

    /**
     * Compute Finance Analytics with Strict Multi-Tenant Separation (PT Pusat vs Cabang)
     */
    public function getFinanceDashboardData($activeCompanyId, $activeBranchId): array
    {
        $cacheKey = "finance_dashboard_metrics_c{$activeCompanyId}_b{$activeBranchId}";

        return Cache::remember($cacheKey, 180, function () use ($activeCompanyId, $activeBranchId) {
            $isBranchSession = !empty($activeBranchId);
            $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
            $activeCompany   = $activeCompanyId ? Company::find($activeCompanyId) : null;

            $entityName = $isBranchSession
                ? ($activeBranch ? $activeBranch->nama_cabang : session('active_branch_name', 'Kantor Cabang'))
                : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));

            // Strict Multi-Tenant Scoper Helper (Hermetic Database Isolation)
            $scopeTenant = function ($query) use ($activeBranchId, $activeCompanyId) {
                $table = $query->getModel()->getTable();
                if (!empty($activeBranchId)) {
                    return $query->where($table . '.branch_id', $activeBranchId)
                                 ->whereNull($table . '.company_id');
                } else {
                    return $query->whereNull($table . '.branch_id')
                                 ->where($table . '.company_id', $activeCompanyId ?: 1);
                }
            };

            $today            = now()->toDateString();
            $startOfMonth     = now()->startOfMonth()->toDateString();
            $endOfMonth       = now()->endOfMonth()->toDateString();
            $startOfLastMonth = now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth   = now()->subMonth()->endOfMonth()->toDateString();

            // ── 1. Kas & Bank Position ──────────────────────────────────────────────
            $kasBankQuery = KasBank::query();
            $scopeTenant($kasBankQuery);
            
            $totalKasBankSaldo  = (float) (clone $kasBankQuery)->where('is_active', 1)->sum('saldo_berjalan');
            $countKasBankActive = (int) (clone $kasBankQuery)->where('is_active', 1)->count();
            
            $kasBankAccounts = (clone $kasBankQuery)
                ->where('is_active', 1)
                ->orderByDesc('saldo_berjalan')
                ->get(['id', 'kode_akun', 'nama_akun', 'nama_bank', 'nomor_rekening', 'atas_nama', 'saldo_berjalan', 'tipe']);

            // ── 2. Arus Kas (Cash Flow) Bulan Ini & Bulan Lalu ─────────────────────
            $mutasiBase = KasMutasi::query()->whereIn('status', ['Disetujui', 'Posted', 'Selesai']);
            $scopeTenant($mutasiBase);

            $kasMasukMonth = (float) (clone $mutasiBase)
                ->where('tipe', 'Masuk')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('jumlah');

            $kasKeluarMonth = (float) (clone $mutasiBase)
                ->where('tipe', 'Keluar')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('jumlah');

            $netCashFlowMonth = $kasMasukMonth - $kasKeluarMonth;

            $kasMasukLastMonth = (float) (clone $mutasiBase)
                ->where('tipe', 'Masuk')
                ->whereBetween('tanggal', [$startOfLastMonth, $endOfLastMonth])
                ->sum('jumlah');

            $kasKeluarLastMonth = (float) (clone $mutasiBase)
                ->where('tipe', 'Keluar')
                ->whereBetween('tanggal', [$startOfLastMonth, $endOfLastMonth])
                ->sum('jumlah');

            $netCashFlowLastMonth = $kasMasukLastMonth - $kasKeluarLastMonth;

            $netCashFlowGrowth = 0.0;
            if ($netCashFlowLastMonth != 0) {
                $netCashFlowGrowth = round((($netCashFlowMonth - $netCashFlowLastMonth) / abs($netCashFlowLastMonth)) * 100, 1);
            }

            // ── 3. Piutang Usaha (AR) & Hutang Usaha (AP) ──────────────────────────
            $siBase = SalesInvoice::query();
            $scopeTenant($siBase);

            $totalOutstandingAr = (float) (clone $siBase)
                ->whereNotIn('status', ['Paid', 'Lunas', 'Cancelled', 'Draft'])
                ->where('sisa_tagihan', '>', 0)
                ->sum('sisa_tagihan');

            $totalArOverdue = (float) (clone $siBase)
                ->whereNotIn('status', ['Paid', 'Lunas', 'Cancelled', 'Draft'])
                ->where('sisa_tagihan', '>', 0)
                ->where('tanggal_jatuh_tempo', '<', $today)
                ->sum('sisa_tagihan');

            $countArOverdue = (int) (clone $siBase)
                ->whereNotIn('status', ['Paid', 'Lunas', 'Cancelled', 'Draft'])
                ->where('sisa_tagihan', '>', 0)
                ->where('tanggal_jatuh_tempo', '<', $today)
                ->count();

            // AP: Purchase Invoices (Hutang yang belum lunas dan bukan draft/cancelled)
            $piBase = PurchaseInvoice::query();
            $scopeTenant($piBase);

            $openPiQuery = (clone $piBase)
                ->whereNotIn('status', ['Paid', 'Lunas', 'Cancelled', 'Draft']);

            $rawApGross = (float) (clone $openPiQuery)->sum(DB::raw('grand_total - COALESCE(uang_muka, 0)'));
            $paidApSub = (float) DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'pp.id', '=', 'ppd.purchase_payment_id')
                ->where('pp.status', 'Posted')
                ->whereNull('pp.deleted_at')
                ->whereIn('ppd.purchase_invoice_id', (clone $openPiQuery)->select('id'))
                ->sum(DB::raw('ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)'));

            $totalOutstandingAp = max(0, $rawApGross - $paidApSub);

            $overduePiQuery = (clone $openPiQuery)
                ->where('tanggal_jatuh_tempo', '<', $today);

            $countApOverdue = (int) (clone $overduePiQuery)->count();

            $overdueApGross = (float) (clone $overduePiQuery)->sum(DB::raw('grand_total - COALESCE(uang_muka, 0)'));
            $overdueApPaidSub = (float) DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'pp.id', '=', 'ppd.purchase_payment_id')
                ->where('pp.status', 'Posted')
                ->whereNull('pp.deleted_at')
                ->whereIn('ppd.purchase_invoice_id', (clone $overduePiQuery)->select('id'))
                ->sum(DB::raw('ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)'));

            $totalApOverdue = max(0, $overdueApGross - $overdueApPaidSub);

            // ── 4. Kas Kecil (Petty Cash) ──────────────────────────────────────────
            $pettyBase = PettyCash::query();
            $scopeTenant($pettyBase);

            $pettyCashExpenseMonth = (float) (clone $pettyBase)
                ->where('status', 'Disetujui')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->sum('nominal');

            $pendingPettyCashCount = (int) (clone $pettyBase)
                ->where('status', 'Diajukan')
                ->count();

            // ── 5. Trend Arus Kas 6 Bulan Terakhir (Chart 1) ───────────────────────
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth()->toDateString();
            $rawMonthlyCash = (clone $mutasiBase)
                ->whereIn('tipe', ['Masuk', 'Keluar'])
                ->where('tanggal', '>=', $sixMonthsAgo)
                ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as ym, tipe, SUM(jumlah) as total")
                ->groupBy('ym', 'tipe')
                ->get();

            $trendLabels   = [];
            $trendCashIn   = [];
            $trendCashOut  = [];
            $trendNetCash  = [];

            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $ym        = $monthDate->format('Y-m');
                $label     = $monthDate->translatedFormat('M Y');

                $inRow = $rawMonthlyCash->first(fn($item) => $item->ym === $ym && $item->tipe === 'Masuk');
                $outRow = $rawMonthlyCash->first(fn($item) => $item->ym === $ym && $item->tipe === 'Keluar');

                $inVal  = $inRow ? (float) $inRow->total : 0.0;
                $outVal = $outRow ? (float) $outRow->total : 0.0;
                $netVal = $inVal - $outVal;

                $trendLabels[]  = $label;
                $trendCashIn[]  = $inVal;
                $trendCashOut[] = $outVal;
                $trendNetCash[] = $netVal;
            }

            // ── 6. Komposisi Distribusi Kas & Bank (Chart 2 Donut) ──────────────────
            $donutLabels = [];
            $donutData   = [];
            $donutColors = [
                '#6366f1', '#10b981', '#f59e0b', '#06b6d4', 
                '#ec4899', '#8b5cf6', '#3b82f6', '#14b8a6', 
                '#f97316', '#64748b'
            ];

            foreach ($kasBankAccounts->take(6) as $account) {
                $donutLabels[] = ($account->nama_bank ?: $account->nama_akun) . ' - ' . ($account->nomor_rekening ?: 'Kas');
                $donutData[]   = (float) $account->saldo_berjalan;
            }

            if ($kasBankAccounts->count() > 6) {
                $otherSaldo = $kasBankAccounts->slice(6)->sum('saldo_berjalan');
                if ($otherSaldo > 0) {
                    $donutLabels[] = 'Rekening Lainnya (' . ($kasBankAccounts->count() - 6) . ')';
                    $donutData[]   = (float) $otherSaldo;
                }
            }

            // ── 7. Overdue Tagihan Prioritas (Tabel Monitoring AR & AP) ─────────────
            $overdueArList = (clone $siBase)
                ->whereNotIn('status', ['Paid', 'Lunas', 'Cancelled', 'Draft'])
                ->where('sisa_tagihan', '>', 0)
                ->where('tanggal_jatuh_tempo', '<', $today)
                ->with('customer:id,nama_customer')
                ->orderBy('tanggal_jatuh_tempo')
                ->limit(5)
                ->get(['id', 'nomor_faktur', 'customer_id', 'tanggal_faktur', 'tanggal_jatuh_tempo', 'sisa_tagihan', 'status']);

            $overdueApList = (clone $overduePiQuery)
                ->with(['paymentDetails.payment', 'supplier:id,nama_supplier'])
                ->orderBy('tanggal_jatuh_tempo')
                ->limit(5)
                ->get();

            // ── 8. Mutasi Kas & Bank Terkini (8 Transaksi) ─────────────────────────
            $recentMutasiQuery = KasMutasi::query();
            $scopeTenant($recentMutasiQuery);
            $recentMutasi = $recentMutasiQuery
                ->with('kasBank:id,nama_bank,nama_akun,nomor_rekening')
                ->latest('tanggal')
                ->latest('id')
                ->limit(8)
                ->get(['id', 'nomor_transaksi', 'kas_bank_id', 'tipe', 'kategori', 'jumlah', 'tanggal', 'keterangan', 'status']);

            // ── 9. Transaksi Kas Kecil Terkini (6 Transaksi) ───────────────────────
            $recentPettyCash = (clone $pettyBase)
                ->latest('tanggal')
                ->latest('id')
                ->limit(6)
                ->get(['id', 'nomor_transaksi', 'kategori', 'nominal', 'tanggal', 'keterangan', 'status']);

            return [
                'isBranchSession'        => $isBranchSession,
                'entityName'             => $entityName,
                'activeBranchId'         => $activeBranchId,
                'activeCompanyId'        => $activeCompanyId,

                // Top KPI Metrics
                'totalKasBankSaldo'      => $totalKasBankSaldo,
                'countKasBankActive'     => $countKasBankActive,
                'kasMasukMonth'          => $kasMasukMonth,
                'kasKeluarMonth'         => $kasKeluarMonth,
                'netCashFlowMonth'       => $netCashFlowMonth,
                'netCashFlowGrowth'      => $netCashFlowGrowth,
                'totalOutstandingAr'     => $totalOutstandingAr,
                'countArOverdue'         => $countArOverdue,
                'totalArOverdue'         => $totalArOverdue,
                'totalOutstandingAp'     => $totalOutstandingAp,
                'countApOverdue'         => $countApOverdue,
                'totalApOverdue'         => $totalApOverdue,
                'pettyCashExpenseMonth'  => $pettyCashExpenseMonth,
                'pendingPettyCashCount'  => $pendingPettyCashCount,

                // Chart Datasets
                'trendLabels'            => $trendLabels,
                'trendCashIn'            => $trendCashIn,
                'trendCashOut'           => $trendCashOut,
                'trendNetCash'           => $trendNetCash,
                'donutLabels'            => $donutLabels,
                'donutData'              => $donutData,
                'donutColors'            => array_slice($donutColors, 0, count($donutLabels)),

                // Table Datasets
                'kasBankAccounts'        => $kasBankAccounts,
                'overdueArList'          => $overdueArList,
                'overdueApList'          => $overdueApList,
                'recentMutasi'           => $recentMutasi,
                'recentPettyCash'        => $recentPettyCash,
            ];
        });
    }
}
