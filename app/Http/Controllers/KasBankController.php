<?php

namespace App\Http\Controllers;

use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Company;
use App\Models\Branch;
use App\Models\KasKategoriMapping;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class KasBankController extends Controller
{
    /**
     * Tampilan Dashboard Utama Kas & Bank
     */
    public function index()
    {
        $this->authorize('lihat-kas-bank');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $company         = Company::find($activeCompanyId);

        // Ambil cabang-cabang aktif untuk dropdown filter/penugasan (jika di tingkat PT Pusat)
        $branches = Branch::where('company_id', $activeCompanyId)
            ->where('status', 'Active')
            ->orderBy('nama_cabang', 'asc')
            ->get();

        $accounts = KasBank::with(['coa:id,code,name', 'branch:id,kode_cabang,nama_cabang'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        // Ambil daftar COA aktif yang bukan header (hanya akun aktiva/beban/pendapatan)
        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        // Hitung ringkasan saldo untuk entitas aktif
        $totalKas = $accounts->where('tipe', 'Kas Tunai')->sum('saldo_berjalan');
        $totalBank = $accounts->where('tipe', '!=', 'Kas Tunai')->sum('saldo_berjalan');
        $totalSemua = $totalKas + $totalBank;

        // Hitung mutasi bulan ini
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $cashInThisMonth = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Disetujui')
            ->where('tipe', 'Masuk')
            ->where('kategori', '!=', 'Transfer Antar Rekening')
            ->where('kategori', '!=', 'Saldo Awal Rekening')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        $cashOutThisMonth = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Disetujui')
            ->where('tipe', 'Keluar')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        // 8 Mutasi Terbaru untuk Widget Dashboard
        $recentMutasis = KasMutasi::with(['kasBank.coa', 'transferToKasBank.coa', 'lawanCoa', 'branch', 'creator'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        // Kategori Kas Masuk & Kas Keluar Mapped
        $kasMasukCategories = KasKategoriMapping::forActiveCompany()
            ->active()
            ->masuk()
            ->with('coa')
            ->orderBy('nama_kategori', 'asc')
            ->get();

        $kasKeluarCategories = KasKategoriMapping::forActiveCompany()
            ->active()
            ->keluar()
            ->with('coa')
            ->orderBy('nama_kategori', 'asc')
            ->get();

        // Master Tender Aktif (Strict Entity Isolation: PT Pusat vs Cabang)
        $tenderQuery = Tender::with(['branch:id,nama_cabang', 'company:id,nama_perusahaan']);
        if (!empty($activeBranchId)) {
            $tenderQuery->where('branch_id', $activeBranchId);
        } else {
            $tenderQuery->where('company_id', $activeCompanyId)->whereNull('branch_id');
        }
        $tenders = $tenderQuery->orderBy('nomor_kontrak', 'desc')->get();

        return view('modul_accounting.kas&bank.index', compact(
            'accounts',
            'coaAccounts',
            'totalKas',
            'totalBank',
            'totalSemua',
            'cashInThisMonth',
            'cashOutThisMonth',
            'recentMutasis',
            'activeBranch',
            'company',
            'branches',
            'kasMasukCategories',
            'kasKeluarCategories',
            'tenders'
        ));
    }

    /**
     * Real-time Live Sync Polling (< 1 Detik)
     * Endpoint sangat ringan untuk sinkronisasi otomatis di latar belakang tanpa reload halaman.
     */
    public function liveSync(Request $request)
    {
        $this->authorize('lihat-kas-bank');

        $clientFingerprint = $request->query('fingerprint');

        // Query metadata sangat cepat (O(1) Indexed) untuk cek apakah ada mutasi baru atau perubahan saldo
        $maxMutasiId = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->max('id') ?? 0;

        $accountMeta = KasBank::forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->selectRaw('MAX(updated_at) as last_updated, SUM(saldo_berjalan) as total_saldo, COUNT(id) as total_acc')
            ->first();

        $currentFingerprint = md5(
            $maxMutasiId . '_' .
            ($accountMeta->total_saldo ?? 0) . '_' .
            ($accountMeta->last_updated ?? '0') . '_' .
            ($accountMeta->total_acc ?? 0)
        );

        // Jika fingerprint sama persis, tidak ada mutasi baru -> return langsung < 3ms
        if ($clientFingerprint && $clientFingerprint === $currentFingerprint) {
            return response()->json([
                'changed' => false,
                'fingerprint' => $currentFingerprint,
            ]);
        }

        // Ambil data terbaru untuk di-render client-side secara halus
        $accounts = KasBank::with(['coa:id,code,name', 'branch:id,kode_cabang,nama_cabang'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $totalKas = $accounts->where('tipe', 'Kas Tunai')->sum('saldo_berjalan');
        $totalBank = $accounts->where('tipe', '!=', 'Kas Tunai')->sum('saldo_berjalan');
        $totalSemua = $totalKas + $totalBank;

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $cashInThisMonth = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Disetujui')
            ->where('tipe', 'Masuk')
            ->where('kategori', '!=', 'Transfer Antar Rekening')
            ->where('kategori', '!=', 'Saldo Awal Rekening')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        $cashOutThisMonth = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Disetujui')
            ->where('tipe', 'Keluar')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        $fmt = fn($val) => number_format((float)$val, 2, ',', '.');

        $accountsData = $accounts->map(function($acc) use ($fmt) {
            return [
                'id' => $acc->id,
                'nama_akun' => $acc->nama_akun,
                'kode_akun' => $acc->kode_akun ?: ('KB-' . $acc->id),
                'tipe' => $acc->tipe,
                'saldo_berjalan' => (float)$acc->saldo_berjalan,
                'formatted_saldo' => 'Rp ' . $fmt($acc->saldo_berjalan),
                'raw_saldo' => (float)$acc->saldo_berjalan,
                'entitas_label' => $acc->entitas_label,
            ];
        });

        return response()->json([
            'changed' => true,
            'fingerprint' => $currentFingerprint,
            'stats' => [
                'total_kas' => 'Rp ' . $fmt($totalKas),
                'total_bank' => 'Rp ' . $fmt($totalBank),
                'total_semua' => 'Rp ' . $fmt($totalSemua),
                'cash_in' => '+ Rp ' . $fmt($cashInThisMonth),
                'cash_out' => '- Rp ' . $fmt($cashOutThisMonth),
                'kas_count' => $accounts->where('tipe', 'Kas Tunai')->count(),
                'bank_count' => $accounts->where('tipe', '!=', 'Kas Tunai')->count(),
            ],
            'accounts' => $accountsData,
        ]);
    }

    /**
     * Halaman Buku Kas & Rekening Koran (Bank Statements & Ledger)
     */
    public function ledger(Request $request, $accountId = null)
    {
        $this->authorize('lihat-mutasi-kas-bank');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $company         = Company::find($activeCompanyId);

        $branches = Branch::where('company_id', $activeCompanyId)
            ->where('status', 'Active')
            ->orderBy('nama_cabang', 'asc')
            ->get();

        $accounts = KasBank::with(['coa', 'branch'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('tipe', 'asc')
            ->orderBy('nama_akun', 'asc')
            ->get();

        $selectedAccount = null;
        if ($accountId) {
            $selectedAccount = KasBank::with(['coa', 'branch'])
                ->forActiveCompany()
                ->forActiveBranch()
                ->find($accountId);
        } elseif ($request->filled('kas_bank_id') || $request->filled('account_id')) {
            $targetId = $request->kas_bank_id ?: $request->account_id;
            $selectedAccount = KasBank::with(['coa', 'branch'])
                ->forActiveCompany()
                ->forActiveBranch()
                ->find($targetId);
        }

        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?: now()->toDateString();

        // Hitung Ringkasan Saldo Periode (Saldo Awal, Masuk, Keluar, Saldo Akhir)
        $summary = $this->calculateLedgerPeriodSummary($selectedAccount?->id, $startDate, $endDate);
        $totalMasuk        = $summary['total_masuk'];
        $totalKeluar       = $summary['total_keluar'];
        $netMutasi         = $summary['net_mutasi'];
        $saldoAwalPeriode  = $summary['saldo_awal'];
        $saldoAkhirPeriode = $summary['saldo_akhir'];

        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        // Kategori Kas Masuk & Kas Keluar Mapped
        $kasMasukCategories = KasKategoriMapping::forActiveCompany()
            ->active()
            ->masuk()
            ->with('coa')
            ->orderBy('nama_kategori', 'asc')
            ->get();

        $kasKeluarCategories = KasKategoriMapping::forActiveCompany()
            ->active()
            ->keluar()
            ->with('coa')
            ->orderBy('nama_kategori', 'asc')
            ->get();

        // Master Tender Aktif (Strict Entity Isolation: PT Pusat vs Cabang)
        $tenderQuery = Tender::with(['branch:id,nama_cabang', 'company:id,nama_perusahaan']);
        if (!empty($activeBranchId)) {
            $tenderQuery->where('branch_id', $activeBranchId);
        } else {
            $tenderQuery->where('company_id', $activeCompanyId)->whereNull('branch_id');
        }
        $tenders = $tenderQuery->orderBy('nomor_kontrak', 'desc')->get();

        return view('modul_accounting.kas&bank.ledger', compact(
            'accounts',
            'coaAccounts',
            'selectedAccount',
            'startDate',
            'endDate',
            'summary',
            'saldoAwalPeriode',
            'saldoAkhirPeriode',
            'totalMasuk',
            'totalKeluar',
            'netMutasi',
            'activeBranch',
            'company',
            'branches',
            'kasMasukCategories',
            'kasKeluarCategories',
            'tenders'
        ));
    }

    /**
     * Cetak Rekening Koran / Buku Kas (Printable View)
     */
    public function printLedger(Request $request, $accountId = null)
    {
        if (!auth()->user()->can('cetak-rekening-koran') && !auth()->user()->can('lihat-mutasi-kas-bank') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak rekening koran.');
        }

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $company         = Company::find($activeCompanyId);

        $selectedAccount = null;
        if ($accountId) {
            $selectedAccount = KasBank::with(['coa', 'branch'])
                ->forActiveCompany()
                ->forActiveBranch()
                ->find($accountId);
            if (!$selectedAccount) {
                abort(404, 'Rekening Kas & Bank tidak ditemukan atau Anda tidak memiliki akses ke rekening ini.');
            }
        } elseif ($request->filled('kas_bank_id') || $request->filled('account_id')) {
            $targetId = $request->kas_bank_id ?: $request->account_id;
            $selectedAccount = KasBank::with(['coa', 'branch'])
                ->forActiveCompany()
                ->forActiveBranch()
                ->find($targetId);
            if (!$selectedAccount) {
                abort(404, 'Rekening Kas & Bank tidak ditemukan atau Anda tidak memiliki akses ke rekening ini.');
            }
        }

        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?: now()->toDateString();

        // Validasi urutan tanggal jika terbalik
        if ($startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Optimasi skala data besar: Select kolom esensial saja untuk hemat memori
        $query = KasMutasi::with(['tender:id,nomor_kontrak,nama_tender'])
            ->select([
                'id', 'nomor_transaksi', 'kas_bank_id', 'tipe', 'kategori',
                'jumlah', 'saldo_sebelum', 'saldo_sesudah', 'tanggal',
                'nama_kontak', 'keterangan', 'tender_id', 'status', 'company_id', 'branch_id'
            ])
            ->forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');

        if ($selectedAccount) {
            $query->where('kas_bank_id', $selectedAccount->id);
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Safety Limit Proteksi Memori & Anti-Lag Browser untuk Skala Jutaan Data:
        // Dokumen cetak HTML wajar dibatasi maksimal 3.000 transaksi agar tab browser tidak crash/freeze.
        $maxPrintRows = 3000;
        $totalRowsCount = (clone $query)->count();
        $isTruncated = false;
        if ($totalRowsCount > $maxPrintRows) {
            $query->limit($maxPrintRows);
            $isTruncated = true;
        }

        $mutasis = $query->get();

        // Ringkasan Periode untuk Rekening Koran (Saldo Awal, Masuk, Keluar, Saldo Akhir)
        $summary = $this->calculateLedgerPeriodSummary($selectedAccount?->id, $startDate, $endDate);
        $totalMasuk        = $summary['total_masuk'];
        $totalKeluar       = $summary['total_keluar'];
        $saldoAwalPeriode  = $summary['saldo_awal'];
        $saldoAkhirPeriode = $summary['saldo_akhir'];

        return view('modul_accounting.kas&bank.print_ledger', compact(
            'selectedAccount',
            'startDate',
            'endDate',
            'mutasis',
            'summary',
            'saldoAwalPeriode',
            'saldoAkhirPeriode',
            'totalMasuk',
            'totalKeluar',
            'company',
            'activeBranch',
            'isTruncated',
            'totalRowsCount',
            'maxPrintRows'
        ));
    }

    /**
     * Cetak / Print Daftar Rekening Kas & Bank (PDF / Printable View)
     */
    public function printAccountsReport(Request $request)
    {
        $this->authorize('lihat-kas-bank');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $company         = Company::find($activeCompanyId);

        $accounts = KasBank::with(['coa', 'branch'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('tipe', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalSaldoAwal  = $accounts->sum('saldo_awal');
        $totalSaldoAkhir = $accounts->sum('saldo_berjalan');

        return view('modul_accounting.kas&bank.print_accounts', compact(
            'accounts',
            'company',
            'activeBranch',
            'totalSaldoAwal',
            'totalSaldoAkhir'
        ));
    }

    /**
     * Export Daftar Rekening Kas & Bank ke File Excel (.xlsx)
     */
    public function exportAccountsExcel()
    {
        $this->authorize('lihat-kas-bank');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        $company         = Company::find($activeCompanyId);

        $accounts = KasBank::with(['coa', 'branch'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $entitasTitle = $activeBranch ? "CABANG: " . strtoupper($activeBranch->nama_cabang) : "PT PUSAT: " . strtoupper($company?->nama_perusahaan ?: 'PERUSAHAAN');

        $excelData = [
            ['<center><b>LAPORAN DAFTAR REKENING KAS & BANK</b></center>'],
            ['<center><b>' . $entitasTitle . '</b></center>'],
            ['<center>Tanggal Cetak: ' . date('d/m/Y H:i') . '</center>'],
            [],
            [
                '<b>NO</b>',
                '<b>KODE AKUN</b>',
                '<b>NAMA REKENING / BUKU KAS</b>',
                '<b>ENTITAS</b>',
                '<b>TIPE</b>',
                '<b>BANK & NO. REKENING</b>',
                '<b>ATAS NAMA</b>',
                '<b>AKUN COA TERHUBUNG</b>',
                '<b>SALDO AWAL (RP)</b>',
                '<b>SALDO SAAT INI (RP)</b>',
                '<b>STATUS</b>'
            ]
        ];

        $totalSaldoAwal  = 0;
        $totalSaldoAkhir = 0;

        foreach ($accounts as $idx => $acc) {
            $totalSaldoAwal += $acc->saldo_awal;
            $totalSaldoAkhir += $acc->saldo_berjalan;

            $rekText = $acc->nomor_rekening ? ($acc->nama_bank . ' - ' . $acc->nomor_rekening) : 'Kas Tunai Fisik';
            $coaText = $acc->coa ? ($acc->coa->code . ' - ' . $acc->coa->name) : '-';

            $excelData[] = [
                $idx + 1,
                $acc->kode_akun ?: ('KB-' . $acc->id),
                $acc->nama_akun,
                $acc->branch ? $acc->branch->nama_cabang : 'PT Pusat',
                $acc->tipe,
                $rekText,
                $acc->atas_nama ?: '-',
                $coaText,
                $acc->saldo_awal,
                $acc->saldo_berjalan,
                $acc->is_active ? 'Aktif' : 'Non-Aktif'
            ];
        }

        $excelData[] = [
            '<b>TOTAL</b>',
            '', '', '', '', '', '', '',
            '<b>' . $totalSaldoAwal . '</b>',
            '<b>' . $totalSaldoAkhir . '</b>',
            ''
        ];

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($excelData);
        $fileName = 'Daftar_Rekening_Kas_Bank_' . date('Ymd_His') . '.xlsx';
        return response($xlsx->output(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Endpoint DataTables AJAX untuk Riwayat Mutasi Kas & Bank
     */
    public function getData(Request $request)
    {
        if (!auth()->user()->can('lihat-kas-bank') && !auth()->user()->can('lihat-mutasi-kas-bank') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat mutasi kas & bank.');
        }

        $query = KasMutasi::with(['kasBank.coa', 'kasBank.branch', 'transferToKasBank.coa', 'lawanCoa', 'tender:id,nomor_kontrak,nama_tender', 'creator:id,nama_lengkap,username', 'branch:id,nama_cabang,kode_cabang'])
            ->forActiveCompany()
            ->forActiveBranch();

        // Filter Rekening
        if ($request->filled('kas_bank_id')) {
            $query->where('kas_bank_id', $request->kas_bank_id);
        }

        // Filter Tipe Transaksi (Cerdas & Cepat: Memanfaatkan Index B-Tree Tanpa Full-Scan LIKE)
        if ($request->filled('tipe')) {
            $tipeFilter = trim($request->tipe);
            if (strcasecmp($tipeFilter, 'Transfer') === 0) {
                $query->where(function ($q) {
                    $q->where('tipe', 'Transfer')
                      ->orWhere('kategori', 'Transfer Antar Rekening')
                      ->orWhere('nomor_transaksi', 'LIKE', 'TR-%');
                });
            } elseif (strcasecmp($tipeFilter, 'Masuk') === 0) {
                $query->where('tipe', 'Masuk')
                      ->where(function ($q) {
                          $q->where('kategori', '!=', 'Transfer Antar Rekening')
                            ->orWhereNull('kategori');
                      })
                      ->where('nomor_transaksi', 'NOT LIKE', 'TR-%');
            } elseif (strcasecmp($tipeFilter, 'Keluar') === 0) {
                $query->where('tipe', 'Keluar')
                      ->where(function ($q) {
                          $q->where('kategori', '!=', 'Transfer Antar Rekening')
                            ->orWhereNull('kategori');
                      });
            } else {
                $query->where('tipe', $tipeFilter);
            }
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        // Hitung Ringkasan Saldo Berdasarkan Filter Aktif (Live Balance Follows Filter)
        $activeAccount = null;
        if ($request->filled('kas_bank_id')) {
            $activeAccount = KasBank::find($request->kas_bank_id);
        }

        $hasCustomFilter = $request->filled('tipe') || $request->filled('start_date') || $request->filled('end_date');
        $filteredSum = 0;
        if ($hasCustomFilter || !$activeAccount) {
            $sumQuery = (clone $query)->where('kas_mutasis.status', 'Disetujui');
            $filteredSum = (float) $sumQuery->sum('kas_mutasis.jumlah');
        }

        $statLabel = 'SALDO BERJALAN SAAT INI';
        $statValue = $activeAccount ? ('Rp ' . $this->formatRupiah($activeAccount->saldo_berjalan)) : ('Rp ' . $this->formatRupiah($filteredSum));
        $statColor = 'text-success';

        if ($request->filled('tipe')) {
            $t = strtolower(trim($request->tipe));
            if ($t === 'masuk') {
                $statLabel = 'TOTAL KAS MASUK (FILTER)';
                $statValue = '+ Rp ' . $this->formatRupiah($filteredSum);
                $statColor = 'text-success';
            } elseif ($t === 'keluar') {
                $statLabel = 'TOTAL KAS KELUAR (FILTER)';
                $statValue = '- Rp ' . $this->formatRupiah($filteredSum);
                $statColor = 'text-danger';
            } elseif ($t === 'transfer') {
                $statLabel = 'TOTAL TRANSFER (FILTER)';
                $statValue = 'Rp ' . $this->formatRupiah($filteredSum);
                $statColor = 'text-info';
            }
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            $statLabel = 'TOTAL MUTASI (PERIODE FILTER)';
            $statValue = 'Rp ' . $this->formatRupiah($filteredSum);
            $statColor = 'text-success';
        }

        // Ringkasan Periode untuk Card Bento (Saldo Awal, Masuk, Keluar, Saldo Akhir)
        $periodSummary = $this->calculateLedgerPeriodSummary($request->kas_bank_id, $request->start_date, $request->end_date);

        // Pre-check permission untuk performa tinggi pada DataTables (Cegah N+1 Auth Check)
        $canCancel = auth()->user()?->can('batalkan-mutasi-kas-bank') || auth()->user()?->hasRole('Super Admin');

        return DataTables::of($query)
            ->with([
                'summary' => $periodSummary,
                'period_summary' => $periodSummary,
                'filter_summary' => [
                    'label'     => $statLabel,
                    'value'     => $statValue,
                    'color'     => $statColor,
                    'raw_total' => $filteredSum,
                ]
            ])
            ->order(function ($q) use ($request) {
                if ($request->has('order')) {
                    $orders = $request->input('order');
                    $columns = $request->input('columns');
                    if (!empty($orders) && is_array($orders)) {
                        foreach ($orders as $ord) {
                            $colIdx = $ord['column'] ?? null;
                            $colDir = strtolower($ord['dir'] ?? 'desc');
                            if ($colIdx !== null && isset($columns[$colIdx])) {
                                $colData = $columns[$colIdx]['data'] ?? null;
                                $colName = $columns[$colIdx]['name'] ?? null;
                                $target = $colName ?: $colData;
                                if ($target === 'tanggal') {
                                    $q->orderBy('kas_mutasis.tanggal', $colDir)
                                      ->orderBy('kas_mutasis.id', $colDir);
                                    return;
                                } elseif ($target && in_array($target, ['nomor_transaksi', 'tipe', 'jumlah', 'saldo_sesudah', 'status', 'created_at'])) {
                                    $q->orderBy("kas_mutasis.{$target}", $colDir);
                                    return;
                                }
                            }
                        }
                    }
                }
                $q->orderBy('kas_mutasis.tanggal', 'desc')->orderBy('kas_mutasis.id', 'desc');
            })
            ->addIndexColumn()
            ->editColumn('nomor_transaksi', function ($m) {
                $icon = $m->tipe === 'Masuk' ? 'bi-arrow-down-left text-success' : ($m->tipe === 'Keluar' ? 'bi-arrow-up-right text-danger' : 'bi-arrow-left-right text-info');
                $lampiranBadge = '';
                if ($m->lampiran) {
                    $lampiranUrl = asset('storage/' . $m->lampiran);
                    $nomFmt = ($m->tipe === 'Masuk' ? '+ ' : '- ') . 'Rp ' . $this->formatRupiah($m->jumlah);
                    $accName = $m->kasBank ? $m->kasBank->nama_akun : '-';
                    $lampiranBadge = '<button type="button" class="btn btn-xs badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace ms-1 px-2 py-0.5 btn-preview-bukti-kb" data-url="' . $lampiranUrl . '" data-nomor="' . e($m->nomor_transaksi) . '" data-nominal="' . $nomFmt . '" data-tipe="' . e($m->tipe) . '" data-kategori="' . e($m->kategori) . '" data-akun="' . e($accName) . '" title="Lihat Foto Bukti"><i class="bi bi-paperclip me-0.5"></i> Bukti</button>';
                }
                return '<div class="d-flex align-items-center gap-2">
                            <span class="p-1 rounded-2 bg-body-tertiary border d-inline-flex"><i class="bi ' . $icon . '"></i></span>
                            <div>
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="fw-bold font-monospace text-body-emphasis">' . e($m->nomor_transaksi) . '</span>
                                    ' . $lampiranBadge . '
                                </div>
                                <div class="text-muted small">' . e($m->kategori) . '</div>
                            </div>
                        </div>';
            })
            ->editColumn('kas_bank_nama', function ($m) {
                $acc = $m->kasBank;
                if (!$acc) return '-';
                $coaCode = $acc->coa ? ' [' . e($acc->coa->code) . ']' : '';
                $entitasBadge = $acc->branch 
                    ? '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-shop me-0.5"></i> ' . e($acc->branch->nama_cabang) . '</span>'
                    : '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.68rem;"><i class="bi bi-building me-0.5"></i> PT Pusat</span>';

                return '<div class="d-flex flex-column gap-0.5">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="fw-semibold text-body">' . e($acc->nama_akun) . '</span>
                                ' . $entitasBadge . '
                            </div>
                            <div class="text-muted small font-monospace">' . e($acc->tipe) . $coaCode . '</div>
                        </div>';
            })
            ->editColumn('tanggal', function ($m) {
                return '<span class="font-monospace small text-muted">' . date('d/m/Y', strtotime($m->tanggal)) . '</span>';
            })
            ->editColumn('pihak_ketiga', function ($m) {
                if ($m->tipe === 'Transfer' && $m->transferToKasBank) {
                    return '<span class="text-info fw-semibold"><i class="bi bi-arrow-right me-1"></i> Ke: ' . e($m->transferToKasBank->nama_akun) . '</span>' . ($m->keterangan ? '<div class="text-muted small">' . e($m->keterangan) . '</div>' : '');
                }
                if ($m->tipe === 'Masuk' && $m->kategori === 'Transfer Antar Rekening' && $m->transferToKasBank) {
                    return '<span class="text-success fw-semibold"><i class="bi bi-arrow-left me-1"></i> Dari: ' . e($m->transferToKasBank->nama_akun) . '</span>' . ($m->keterangan ? '<div class="text-muted small">' . e($m->keterangan) . '</div>' : '');
                }
                $lawanText = $m->lawanCoa ? '<div class="text-muted small font-monospace"><i class="bi bi-link-45deg"></i> COA: ' . e($m->lawanCoa->code . ' - ' . $m->lawanCoa->name) . '</div>' : '';
                $tenderBadge = $m->tender ? '<div class="mt-1"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace" style="font-size: 0.68rem;" title="Terhubung ke Tender Proyek"><i class="bi bi-briefcase-fill me-1"></i>' . e($m->tender->nomor_kontrak . ' - ' . $m->tender->nama_tender) . '</span></div>' : '';
                return '<span class="text-body-emphasis fw-semibold">' . e($m->nama_kontak ?: '-') . '</span>' . ($m->nomor_referensi ? '<div class="text-muted small font-monospace">Ref: ' . e($m->nomor_referensi) . '</div>' : '') . $lawanText . $tenderBadge;
            })
            ->editColumn('jumlah', function ($m) {
                $valFmt = $this->formatRupiah($m->jumlah);
                if ($m->tipe === 'Masuk') {
                    return '<span class="fw-bold text-success font-monospace">+ Rp ' . $valFmt . '</span>';
                } elseif ($m->tipe === 'Keluar') {
                    return '<span class="fw-bold text-danger font-monospace">- Rp ' . $valFmt . '</span>';
                } else {
                    return '<span class="fw-bold text-danger font-monospace">- Rp ' . $valFmt . ' <span class="badge bg-info-subtle text-info font-monospace" style="font-size:0.68rem;">TRF</span></span>';
                }
            })
            ->editColumn('saldo_sesudah', function ($m) {
                return '<span class="font-monospace fw-semibold text-body small">Rp ' . $this->formatRupiah($m->saldo_sesudah) . '</span>';
            })
            ->editColumn('status', function ($m) {
                if ($m->status === 'Disetujui') {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">Disetujui</span>';
                }
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2.5 py-1">Dibatalkan</span>';
            })
            ->addColumn('action', function ($m) use ($canCancel) {
                $btns = '<div class="d-flex align-items-center justify-content-center gap-1.5">';
                $btns .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 btn-detail-mutasi" data-id="' . $m->id . '" title="Rincian Bukti Transaksi">
                            <i class="bi bi-eye"></i>
                          </button>';
                if ($m->lampiran) {
                    $lampiranUrl = asset('storage/' . $m->lampiran);
                    $nomFmt = ($m->tipe === 'Masuk' ? '+ ' : '- ') . 'Rp ' . $this->formatRupiah($m->jumlah);
                    $accName = $m->kasBank ? $m->kasBank->nama_akun : '-';
                    $btns .= '<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1 btn-preview-bukti-kb" data-url="' . $lampiranUrl . '" data-nomor="' . e($m->nomor_transaksi) . '" data-nominal="' . $nomFmt . '" data-tipe="' . e($m->tipe) . '" data-kategori="' . e($m->kategori) . '" data-akun="' . e($accName) . '" title="Pratinjau Foto Bukti di Modal">
                                <i class="bi bi-image"></i>
                              </button>';
                }
                $btns .= '</div>';
                return $btns;
            })
            ->rawColumns(['nomor_transaksi', 'kas_bank_nama', 'tanggal', 'pihak_ketiga', 'jumlah', 'saldo_sesudah', 'status', 'action'])
            ->make(true);
    }

    /**
     * Tambah Akun Rekening Kas & Bank Baru
     */
    public function storeAccount(Request $request)
    {
        $this->authorize('tambah-kas-bank');

        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('saldo_awal')) {
            $request->merge(['saldo_awal' => $this->cleanNominal($request->saldo_awal)]);
        }

        $request->validate([
            'nama_akun'      => 'required|string|max:100',
            'tipe'           => 'required|in:Kas Tunai,Bank,E-Wallet / Giro',
            'coa_id'         => 'required|exists:chart_of_accounts,id',
            'saldo_awal'     => 'nullable|numeric|min:0',
            'lawan_coa_id'   => 'nullable|exists:chart_of_accounts,id',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama'      => 'nullable|string|max:100',
            'nama_bank'      => 'nullable|string|max:100',
            'branch_id'      => 'nullable|exists:branches,id',
        ], [
            'coa_id.required' => 'Akun COA wajib dipilih untuk integrasi pembukuan akuntansi.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        // Otomatis sesuai sesi entitas yang sedang aktif:
        // Jika Cabang: branch_id terisi, company_id = NULL
        // Jika PT Pusat: branch_id = NULL, company_id terisi
        $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : ($request->filled('branch_id') ? $request->branch_id : null);
        $targetCompanyId = !empty($targetBranchId) ? null : $activeCompanyId;

        $saldoAwal = (float) ($request->saldo_awal ?: 0);

        DB::beginTransaction();
        try {
            // Validasi akun COA utama (wajib milik entitas aktif, aktif, & bukan header)
            $mainCoa = ChartOfAccount::forActiveCompany()
                ->where('id', $request->coa_id)
                ->where('is_active', true)
                ->where('is_header', false)
                ->first();

            if (!$mainCoa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun COA kas/bank yang dipilih tidak aktif, berupa header, atau tidak ditemukan di entitas perusahaan ini.',
                ], 422);
            }
            $coaId = $mainCoa->id;

            // Generate Kode Akun jika kosong (O(1) Indexed Scan)
            $prefixCode = 'KB-';
            $lastAccount = DB::table('kas_banks')->where('kode_akun', 'LIKE', $prefixCode . '%')->orderBy('id', 'desc')->first();
            $nextAccNum = 1;
            if ($lastAccount) {
                $parts = explode('-', $lastAccount->kode_akun);
                $nextAccNum = ((int) end($parts)) + 1;
            }
            do {
                $kodeAkun = $request->kode_akun ?: ($prefixCode . str_pad($nextAccNum, 3, '0', STR_PAD_LEFT));
                $exists = DB::table('kas_banks')->where('kode_akun', $kodeAkun)->exists();
                if ($exists) {
                    $nextAccNum++;
                }
            } while ($exists && !$request->filled('kode_akun'));

            $account = KasBank::create([
                'kode_akun'      => $kodeAkun,
                'nama_akun'      => $request->nama_akun,
                'tipe'           => $request->tipe,
                'coa_id'         => $coaId,
                'nama_bank'      => $request->nama_bank,
                'nomor_rekening' => $request->nomor_rekening,
                'atas_nama'      => $request->atas_nama,
                'saldo_awal'     => $saldoAwal,
                'saldo_berjalan' => $saldoAwal,
                'deskripsi'      => $request->deskripsi,
                'is_active'      => true,
                'company_id'     => $targetCompanyId,
                'branch_id'      => $targetBranchId,
                'created_by'     => auth()->id(),
            ]);

            // Jika ada saldo awal > 0, catat mutasi saldo awal dan auto journal
            if ($saldoAwal > 0) {
                $prefixSA = 'SA-' . date('ym') . '-';
                $lastSA = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefixSA . '%')->orderBy('id', 'desc')->first();
                $nextSANum = 1;
                if ($lastSA) {
                    $parts = explode('-', $lastSA->nomor_transaksi);
                    $nextSANum = ((int) end($parts)) + 1;
                }
                do {
                    $nomorTransaksi = $prefixSA . str_pad($nextSANum, 4, '0', STR_PAD_LEFT);
                    $exists = DB::table('kas_mutasis')->where('nomor_transaksi', $nomorTransaksi)->exists();
                    if ($exists) {
                        $nextSANum++;
                    }
                } while ($exists);
                
                // Cari atau validasi akun lawan saldo awal
                $lawanCoa = null;
                if ($request->filled('lawan_coa_id')) {
                    $lawanCoa = ChartOfAccount::forActiveCompany()
                        ->where('id', $request->lawan_coa_id)
                        ->where('is_active', true)
                        ->where('is_header', false)
                        ->first();

                    if (!$lawanCoa) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Akun lawan saldo awal tidak valid, nonaktif, atau merupakan header.',
                        ], 422);
                    }

                    if ($lawanCoa->id == $coaId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Akun lawan saldo awal tidak boleh sama dengan akun COA kas/bank itu sendiri.',
                        ], 422);
                    }
                }

                // Jika user tidak memilih akun lawan, fallback ke akun Ekuitas / Modal default
                if (!$lawanCoa) {
                    $lawanCoa = ChartOfAccount::forActiveCompany()
                        ->where(function($q) {
                            $q->where('name', 'LIKE', '%Modal%')
                              ->orWhere('name', 'LIKE', '%Ekuitas%')
                              ->orWhere('name', 'LIKE', '%Saldo Awal%')
                              ->orWhere('code', 'LIKE', '3%');
                        })
                        ->where('is_header', false)
                        ->where('is_active', true)
                        ->where('id', '!=', $coaId)
                        ->first();
                }

                $lawanCoaId = $lawanCoa?->id;

                $jurnalId = $this->postJurnal(
                    $nomorTransaksi,
                    now()->toDateString(),
                    'Saldo Awal Kas: ' . $account->nama_akun,
                    $account->coa_id,
                    $lawanCoaId,
                    $saldoAwal,
                    $targetBranchId
                );

                $mutasi = KasMutasi::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'kas_bank_id'     => $account->id,
                    'tipe'            => 'Masuk',
                    'kategori'        => 'Saldo Awal Rekening',
                    'lawan_coa_id'    => $lawanCoaId,
                    'jumlah'          => $saldoAwal,
                    'saldo_sebelum'   => 0,
                    'saldo_sesudah'   => $saldoAwal,
                    'tanggal'         => now()->toDateString(),
                    'keterangan'      => 'Pencatatan saldo awal saat pembuatan akun rekening.',
                    'status'          => 'Disetujui',
                    'jurnal_id'       => $jurnalId,
                    'company_id'      => $targetCompanyId,
                    'branch_id'       => $targetBranchId,
                    'created_by'      => auth()->id(),
                ]);

                // Update referensi_id pada jurnal agar bidirectional
                if ($jurnalId) {
                    DB::table('jurnals')->where('id', $jurnalId)->update(['referensi_id' => $mutasi->id]);
                }
            }

            DB::commit();

            $entitasText = $targetBranchId ? 'Cabang' : 'PT Pusat';

            return response()->json([
                'success' => true,
                'message' => "Akun rekening \"{$account->nama_akun}\" ({$entitasText}) berhasil ditambahkan dan terhubung dengan COA.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storeAccount KasBank: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambah rekening: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil data Rincian Rekening Kas & Bank (Detail JSON & Statistik Mutasi)
     */
    public function showAccount(KasBank $account)
    {
        $this->authorize('detail-kas-bank');

        if (!$this->checkAccountEntityAccess($account)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening bukan milik entitas aktif.'], 403);
        }

        $account->load(['branch', 'company', 'coa', 'creator']);

        // Ringkasan transaksi rekening (1 query cepat dengan conditional aggregation)
        $statsAgg = $account->mutasis()
            ->selectRaw("
                COUNT(id) as total_mutasi,
                COALESCE(SUM(CASE WHEN tipe = 'Masuk' AND kategori != 'Saldo Awal Rekening' AND status = 'Disetujui' THEN jumlah ELSE 0 END), 0) as total_masuk,
                COALESCE(SUM(CASE WHEN tipe IN ('Keluar', 'Transfer') AND status = 'Disetujui' THEN jumlah ELSE 0 END), 0) as total_keluar
            ")
            ->first();

        $totalMutasi = (int) ($statsAgg?->total_mutasi ?? 0);
        $totalMasuk  = (float) ($statsAgg?->total_masuk ?? 0);
        $totalKeluar = (float) ($statsAgg?->total_keluar ?? 0);

        return response()->json([
            'success'        => true,
            'account'        => $account,
            'entitas_label'  => $account->entitas_label,
            'stats'          => [
                'total_mutasi' => $totalMutasi,
                'total_masuk'  => $totalMasuk,
                'total_keluar' => $totalKeluar,
            ],
        ]);
    }

    /**
     * Ambil data Akun Rekening Kas & Bank untuk form Edit (JSON)
     */
    public function editAccount(KasBank $account)
    {
        $this->authorize('edit-kas-bank');

        if (!$this->checkAccountEntityAccess($account)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening bukan milik entitas aktif.'], 403);
        }

        $account->load('branch');
        return response()->json([
            'success' => true,
            'account' => $account,
        ]);
    }

    /**
     * Update Akun Rekening Kas & Bank
     */
    public function updateAccount(Request $request, KasBank $account)
    {
        $this->authorize('edit-kas-bank');

        if (!$this->checkAccountEntityAccess($account)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening bukan milik entitas aktif.'], 403);
        }

        $request->validate([
            'nama_akun'      => 'required|string|max:100',
            'tipe'           => 'required|in:Kas Tunai,Bank,E-Wallet / Giro',
            'coa_id'         => 'required|exists:chart_of_accounts,id',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama'      => 'nullable|string|max:100',
            'nama_bank'      => 'nullable|string|max:100',
            'branch_id'      => 'nullable|exists:branches,id',
        ], [
            'coa_id.required' => 'Akun COA wajib dipilih untuk integrasi pembukuan akuntansi.',
        ]);

        // Validasi akun COA utama (wajib milik entitas aktif, aktif, & bukan header)
        $mainCoa = ChartOfAccount::forActiveCompany()
            ->where('id', $request->coa_id)
            ->where('is_active', true)
            ->where('is_header', false)
            ->first();

        if (!$mainCoa) {
            return response()->json([
                'success' => false,
                'message' => 'Akun COA kas/bank yang dipilih tidak aktif, berupa header, atau tidak ditemukan di entitas perusahaan ini.',
            ], 422);
        }

        try {
            $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
            $activeBranchId  = session('active_branch_id');
            $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : ($request->has('branch_id') ? $request->branch_id : $account->branch_id);
            $targetCompanyId = !empty($targetBranchId) ? null : $activeCompanyId;

            $account->update([
                'nama_akun'      => $request->nama_akun,
                'tipe'           => $request->tipe,
                'coa_id'         => $request->coa_id,
                'kode_akun'      => $request->kode_akun ?: $account->kode_akun,
                'nama_bank'      => $request->nama_bank,
                'nomor_rekening' => $request->nomor_rekening,
                'atas_nama'      => $request->atas_nama,
                'deskripsi'      => $request->deskripsi,
                'company_id'     => $targetCompanyId,
                'branch_id'      => $targetBranchId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Akun rekening \"{$account->nama_akun}\" berhasil diperbarui.",
            ]);
        } catch (\Exception $e) {
            Log::error('Error updateAccount KasBank: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui rekening: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Catat Penerimaan Kas Masuk (Cash In) + Auto Journal
     */
    public function storeCashIn(Request $request)
    {
        $this->authorize('tambah-kas-masuk');

        // Validasi batas maksimal 26 karakter input jumlah (mendukung hingga ratusan triliun Rupiah beserta sen)
        if ($request->has('jumlah')) {
            if (strlen((string) $request->jumlah) > 26) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal Jumlah tidak boleh lebih dari 26 karakter.',
                ], 422);
            }
            $request->merge(['jumlah' => $this->cleanNominal($request->jumlah)]);
        }

        $request->validate([
            'kas_bank_id'      => 'required|exists:kas_banks,id',
            'lawan_coa_id'     => 'required|exists:chart_of_accounts,id',
            'tanggal'          => 'required|date',
            'kategori'         => 'required|string|max:100',
            'jumlah'           => 'required|numeric|min:1',
            'nama_kontak'      => 'nullable|string|max:40',
            'nomor_referensi'  => 'nullable|string|max:50',
            'tender_id'        => 'nullable|exists:tenders,id',
            'keterangan'       => 'nullable|string|max:40',
            'lampiran'         => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'lawan_coa_id.required'    => 'Akun Lawan COA (Kredit) wajib dipilih untuk integrasi jurnal.',
            'nama_kontak.max'          => 'Nama pembayar maksimal 40 karakter.',
            'nomor_referensi.max'      => 'Nomor referensi maksimal 50 karakter.',
            'keterangan.max'           => 'Keterangan maksimal 40 karakter.',
            'lampiran.required'        => 'Foto / bukti transaksi penerimaan kas wajib dilampirkan.',
            'lampiran.file'            => 'Berkas bukti transaksi harus berupa file yang valid.',
            'lampiran.mimes'           => 'Format bukti transaksi harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'lampiran.max'             => 'Ukuran file bukti transaksi maksimal 10 MB.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $account = KasBank::lockForUpdate()->find($request->kas_bank_id);
            if (!$account || !$this->checkAccountEntityAccess($account)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening kas bukan milik entitas aktif.'], 403);
            }

            if (!$account->is_active) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Rekening kas/bank \"{$account->nama_akun}\" berstatus non-aktif. Tidak dapat menerima mutasi."], 422);
            }

            if (!$account->coa_id) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Rekening kas/bank \"{$account->nama_akun}\" belum dihubungkan dengan Akun COA di pembukuan akuntansi. Harap hubungkan COA terlebih dahulu."], 422);
            }

            // Validasi Akun Lawan COA (Strict Anti-IDOR, Non-Header, Aktif)
            $lawanCoa = ChartOfAccount::forActiveCompany()
                ->where('id', $request->lawan_coa_id)
                ->where('is_active', true)
                ->where('is_header', false)
                ->first();

            if (!$lawanCoa) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Lawan COA tidak valid, berstatus header, non-aktif, atau bukan milik perusahaan aktif.'
                ], 422);
            }

            if ((int) $account->coa_id === (int) $lawanCoa->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Lawan COA tidak boleh sama dengan Akun COA dari Rekening Kas/Bank yang dipilih.'
                ], 422);
            }

            $lawanCoaId = $lawanCoa->id;
            $saldoSebelum = (float) $account->saldo_berjalan;
            $jumlah = (float) $request->jumlah;
            $saldoSesudah = round($saldoSebelum + $jumlah, 2);

            // Validasi kepemilikan tender (Strict Entity Isolation: PT vs Cabang)
            $tenderId = null;
            if ($request->filled('tender_id')) {
                $tender = Tender::find($request->tender_id);
                if ($tender) {
                    $activeBranchId = session('active_branch_id');
                    if (!empty($activeBranchId)) {
                        if ((int)$tender->branch_id !== (int)$activeBranchId) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Akses ditolak: Tender yang dipilih bukan milik Cabang aktif ini.'], 403);
                        }
                    } else {
                        if ((int)$tender->company_id !== (int)$activeCompanyId || !empty($tender->branch_id)) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Akses ditolak: Tender yang dipilih bukan milik PT Pusat aktif ini.'], 403);
                        }
                    }
                    $tenderId = $tender->id;
                }
            }

            $branchId = $account->branch_id;
            $companyId = !empty($branchId) ? null : $activeCompanyId;

            $prefix = 'KM-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($lastMutasi) {
                $parts = explode('-', $lastMutasi->nomor_transaksi);
                $nextNum = ((int) end($parts)) + 1;
            }
            do {
                $nomorTransaksi = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('kas_mutasis')->where('nomor_transaksi', $nomorTransaksi)->exists();
                if ($exists) {
                    $nextNum++;
                }
            } while ($exists);

            // Auto Journal: Debit Kas/Bank, Kredit Akun Lawan (Pendapatan/Piutang)
            $jurnalId = $this->postJurnal(
                $nomorTransaksi,
                $request->tanggal,
                "Kas Masuk [{$request->kategori}]: " . ($request->keterangan ?: "Diterima dari {$request->nama_kontak}"),
                $account->coa_id,
                $lawanCoaId,
                $jumlah,
                $branchId
            );

            // Simpan bukti foto yang otomatis dikompres ke ukuran KB
            $lampiranPath = $this->handleReceiptUpload($request->file('lampiran'));

            $mutasi = KasMutasi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'kas_bank_id'     => $account->id,
                'tipe'            => 'Masuk',
                'kategori'        => $request->kategori,
                'lawan_coa_id'    => $lawanCoaId,
                'jumlah'          => $jumlah,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'tanggal'         => $request->tanggal,
                'nomor_referensi' => $request->nomor_referensi,
                'tender_id'       => $tenderId,
                'nama_kontak'     => $request->nama_kontak,
                'keterangan'      => $request->keterangan,
                'lampiran'        => $lampiranPath,
                'status'          => 'Disetujui',
                'jurnal_id'       => $jurnalId,
                'company_id'      => $companyId,
                'branch_id'       => $branchId,
                'created_by'      => auth()->id(),
            ]);

            if ($jurnalId) {
                Jurnal::where('id', $jurnalId)->update(['referensi_id' => $mutasi->id]);
            }

            $account->saldo_berjalan = $saldoSesudah;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Penerimaan kas sebesar Rp " . number_format($jumlah, 2, ',', '.') . " berhasil dicatat dan diposting ke Jurnal Akuntansi ({$nomorTransaksi}).",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storeCashIn: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mencatat kas masuk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Catat Pengeluaran Kas Keluar (Cash Out) + Auto Journal
     */
    public function storeCashOut(Request $request)
    {
        $this->authorize('tambah-kas-keluar');

        // Validasi batas maksimal 26 karakter input jumlah (mendukung hingga ratusan triliun Rupiah beserta sen)
        if ($request->has('jumlah')) {
            if (strlen((string) $request->jumlah) > 26) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal Jumlah tidak boleh lebih dari 26 karakter.',
                ], 422);
            }
            $request->merge(['jumlah' => $this->cleanNominal($request->jumlah)]);
        }

        $request->validate([
            'kas_bank_id'      => 'required|exists:kas_banks,id',
            'lawan_coa_id'     => 'required|exists:chart_of_accounts,id',
            'tanggal'          => 'required|date',
            'kategori'         => 'required|string|max:100',
            'jumlah'           => 'required|numeric|min:1',
            'nama_kontak'      => 'nullable|string|max:40',
            'nomor_referensi'  => 'nullable|string|max:50',
            'tender_id'        => 'nullable|exists:tenders,id',
            'keterangan'       => 'nullable|string|max:40',
            'lampiran'         => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'lawan_coa_id.required'    => 'Akun Lawan COA (Debit Beban/Hutang) wajib dipilih untuk integrasi jurnal.',
            'nama_kontak.max'          => 'Nama penerima maksimal 40 karakter.',
            'nomor_referensi.max'      => 'Nomor referensi maksimal 50 karakter.',
            'keterangan.max'           => 'Keterangan maksimal 40 karakter.',
            'lampiran.required'        => 'Foto / bukti pengeluaran kas (kuitansi/nota) wajib dilampirkan.',
            'lampiran.file'            => 'Berkas bukti transaksi harus berupa file yang valid.',
            'lampiran.mimes'           => 'Format bukti transaksi harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'lampiran.max'             => 'Ukuran file bukti transaksi maksimal 10 MB.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $account = KasBank::lockForUpdate()->find($request->kas_bank_id);
            if (!$account || !$this->checkAccountEntityAccess($account)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening kas bukan milik entitas aktif.'], 403);
            }

            if (!$account->is_active) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Rekening kas/bank \"{$account->nama_akun}\" berstatus non-aktif. Tidak dapat memproses pengeluaran."], 422);
            }

            if (!$account->coa_id) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => "Rekening kas/bank \"{$account->nama_akun}\" belum dihubungkan dengan Akun COA di pembukuan akuntansi. Harap hubungkan COA terlebih dahulu."], 422);
            }

            // Validasi Akun Lawan COA (Strict Anti-IDOR, Non-Header, Aktif)
            $lawanCoa = ChartOfAccount::forActiveCompany()
                ->where('id', $request->lawan_coa_id)
                ->where('is_active', true)
                ->where('is_header', false)
                ->first();

            if (!$lawanCoa) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Lawan COA tidak valid, berstatus header, non-aktif, atau bukan milik perusahaan aktif.'
                ], 422);
            }

            if ((int) $account->coa_id === (int) $lawanCoa->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Lawan COA tidak boleh sama dengan Akun COA dari Rekening Kas/Bank yang dipilih.'
                ], 422);
            }

            $lawanCoaId = $lawanCoa->id;
            $saldoSebelum = (float) $account->saldo_berjalan;
            $jumlah = (float) $request->jumlah;

            // Validasi kecukupan saldo kas/bank
            if ($jumlah > $saldoSebelum) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo kas \"{$account->nama_akun}\" tidak mencukupi! Saldo saat ini: Rp " . number_format($saldoSebelum, 2, ',', '.') . ", pengeluaran yang diminta: Rp " . number_format($jumlah, 2, ',', '.') . ".",
                ], 422);
            }

            $saldoSesudah = round($saldoSebelum - $jumlah, 2);
            $branchId = $account->branch_id;
            $companyId = !empty($branchId) ? null : $activeCompanyId;

            // Validasi kepemilikan tender (Strict Entity Isolation: PT vs Cabang)
            $tenderId = null;
            if ($request->filled('tender_id')) {
                $tender = Tender::find($request->tender_id);
                if ($tender) {
                    $activeBranchId = session('active_branch_id');
                    if (!empty($activeBranchId)) {
                        if ((int)$tender->branch_id !== (int)$activeBranchId) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Akses ditolak: Tender yang dipilih bukan milik Cabang aktif ini.'], 403);
                        }
                    } else {
                        if ((int)$tender->company_id !== (int)$activeCompanyId || !empty($tender->branch_id)) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Akses ditolak: Tender yang dipilih bukan milik PT Pusat aktif ini.'], 403);
                        }
                    }
                    $tenderId = $tender->id;
                }
            }

            $prefix = 'KK-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($lastMutasi) {
                $parts = explode('-', $lastMutasi->nomor_transaksi);
                $nextNum = ((int) end($parts)) + 1;
            }
            do {
                $nomorTransaksi = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('kas_mutasis')->where('nomor_transaksi', $nomorTransaksi)->exists();
                if ($exists) {
                    $nextNum++;
                }
            } while ($exists);

            // Auto Journal: Debit Beban/Hutang (Akun Lawan), Kredit Kas/Bank
            $jurnalId = $this->postJurnal(
                $nomorTransaksi,
                $request->tanggal,
                "Kas Keluar [{$request->kategori}]: " . ($request->keterangan ?: "Dibayarkan ke {$request->nama_kontak}"),
                $lawanCoaId,
                $account->coa_id,
                $jumlah,
                $branchId
            );

            // Simpan bukti foto yang otomatis dikompres ke ukuran KB
            $lampiranPath = $this->handleReceiptUpload($request->file('lampiran'));

            $mutasi = KasMutasi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'kas_bank_id'     => $account->id,
                'tipe'            => 'Keluar',
                'kategori'        => $request->kategori,
                'lawan_coa_id'    => $lawanCoaId,
                'jumlah'          => $jumlah,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'tanggal'         => $request->tanggal,
                'nomor_referensi' => $request->nomor_referensi,
                'tender_id'       => $tenderId,
                'nama_kontak'     => $request->nama_kontak,
                'keterangan'      => $request->keterangan,
                'lampiran'        => $lampiranPath,
                'status'          => 'Disetujui',
                'jurnal_id'       => $jurnalId,
                'company_id'      => $companyId,
                'branch_id'       => $branchId,
                'created_by'      => auth()->id(),
            ]);

            if ($jurnalId) {
                Jurnal::where('id', $jurnalId)->update(['referensi_id' => $mutasi->id]);
            }

            $account->saldo_berjalan = $saldoSesudah;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran kas sebesar Rp " . number_format($jumlah, 2, ',', '.') . " berhasil dicatat dan diposting ke Jurnal Akuntansi ({$nomorTransaksi}).",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storeCashOut: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mencatat kas keluar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Transfer Antar Rekening Kas & Bank + Auto Journal
     */
    public function storeTransfer(Request $request)
    {
        $this->authorize('transfer-kas-bank');

        // Validasi panjang karakter nominal mentah sebelum di-clean (keamanan: max 20 karakter)
        $rawJumlah = (string) $request->input('jumlah', '');
        if (strlen($rawJumlah) > 20) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal jumlah transfer maksimal 20 karakter!'
            ], 422);
        }

        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('jumlah')) {
            $cleaned = $this->cleanNominal($request->jumlah);
            if (strlen((string) $cleaned) > 20) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal jumlah transfer maksimal 20 karakter!'
                ], 422);
            }
            $request->merge(['jumlah' => $cleaned]);
        }

        // Sanitasi teks keterangan (cegah XSS & batasi max 40 karakter)
        $keterangan = $request->keterangan ? strip_tags(trim($request->keterangan)) : null;
        if ($keterangan && mb_strlen($keterangan) > 40) {
            return response()->json([
                'success' => false,
                'message' => 'Keterangan transfer maksimal 40 karakter!'
            ], 422);
        }
        $request->merge(['keterangan' => $keterangan]);

        $request->validate([
            'from_kas_bank_id' => 'required|exists:kas_banks,id|different:to_kas_bank_id',
            'to_kas_bank_id'   => 'required|exists:kas_banks,id',
            'tanggal'          => 'required|date',
            'jumlah'           => 'required|numeric|min:1|max_digits:20',
            'keterangan'       => 'nullable|string|max:40',
        ], [
            'from_kas_bank_id.different' => 'Rekening asal dan rekening tujuan tidak boleh sama!',
            'from_kas_bank_id.required'  => 'Rekening asal wajib dipilih.',
            'to_kas_bank_id.required'    => 'Rekening tujuan wajib dipilih.',
            'jumlah.required'            => 'Nominal jumlah transfer wajib diisi.',
            'jumlah.numeric'             => 'Nominal jumlah transfer harus berupa angka yang valid.',
            'jumlah.min'                 => 'Nominal jumlah transfer minimal Rp 1.',
            'jumlah.max_digits'          => 'Nominal jumlah transfer maksimal 20 digit!',
            'keterangan.max'             => 'Keterangan transfer maksimal 40 karakter!',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $fromAcc = KasBank::lockForUpdate()->find($request->from_kas_bank_id);
            $toAcc   = KasBank::lockForUpdate()->find($request->to_kas_bank_id);

            if (!$fromAcc || !$this->checkAccountEntityAccess($fromAcc)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening pengirim bukan milik entitas aktif.'], 403);
            }

            if (!$toAcc || !$this->checkAccountEntityAccess($toAcc)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening penerima bukan milik entitas aktif.'], 403);
            }

            if (!$toAcc->is_active || !$fromAcc->is_active) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Salah satu rekening kas/bank berstatus non-aktif atau tidak ditemukan.'], 422);
            }

            if ($fromAcc->id === $toAcc->id) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Rekening asal dan tujuan tidak boleh sama.'], 422);
            }

            $jumlah = (float) $request->jumlah;

            if ($jumlah > (float) $fromAcc->saldo_berjalan) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo pada rekening asal \"{$fromAcc->nama_akun}\" tidak mencukupi untuk transfer! Saldo tersedia: Rp " . number_format($fromAcc->saldo_berjalan, 2, ',', '.') . ".",
                ], 422);
            }

            $prefix = 'TR-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($lastMutasi) {
                $parts = explode('-', $lastMutasi->nomor_transaksi);
                $nextNum = ((int) end($parts)) + 1;
            }
            do {
                $nomorTransaksi = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('kas_mutasis')->where('nomor_transaksi', $nomorTransaksi)->orWhere('nomor_transaksi', $nomorTransaksi . '-IN')->exists();
                if ($exists) {
                    $nextNum++;
                }
            } while ($exists);

            // Auto Journal: Debit Kas/Bank Tujuan, Kredit Kas/Bank Asal
            $jurnalId = $this->postJurnal(
                $nomorTransaksi,
                $request->tanggal,
                "Transfer Saldo: Dari {$fromAcc->nama_akun} ke {$toAcc->nama_akun} - " . ($request->keterangan ?: ''),
                $toAcc->coa_id,
                $fromAcc->coa_id,
                $jumlah,
                $fromAcc->branch_id
            );

            // Mutasi Keluar pada Akun Asal (Presisi Round 2 Desimal)
            $fromSaldoSebelum = (float) $fromAcc->saldo_berjalan;
            $fromSaldoSesudah = round($fromSaldoSebelum - $jumlah, 2);
            $fromAcc->saldo_berjalan = $fromSaldoSesudah;
            $fromAcc->save();

            $fromBranchId  = $fromAcc->branch_id ?: null;
            $fromCompanyId = !empty($fromBranchId) ? null : $activeCompanyId;

            $toBranchId    = $toAcc->branch_id ?: null;
            $toCompanyId   = !empty($toBranchId) ? null : $activeCompanyId;

            $fromMutasi = KasMutasi::create([
                'nomor_transaksi'        => $nomorTransaksi,
                'kas_bank_id'            => $fromAcc->id,
                'transfer_to_kas_bank_id'=> $toAcc->id,
                'tipe'                   => 'Transfer',
                'kategori'               => 'Transfer Antar Rekening',
                'jumlah'                 => $jumlah,
                'saldo_sebelum'          => $fromSaldoSebelum,
                'saldo_sesudah'          => $fromSaldoSesudah,
                'tanggal'                => $request->tanggal,
                'nama_kontak'            => $toAcc->nama_akun,
                'keterangan'             => $request->keterangan ?: "Transfer saldo ke {$toAcc->nama_akun}",
                'status'                 => 'Disetujui',
                'jurnal_id'              => $jurnalId,
                'company_id'             => $fromCompanyId,
                'branch_id'              => $fromBranchId,
                'created_by'             => auth()->id(),
            ]);

            if ($jurnalId) {
                Jurnal::where('id', $jurnalId)->update(['referensi_id' => $fromMutasi->id]);
            }

            // Mutasi Masuk pada Akun Tujuan (Presisi Round 2 Desimal)
            $toSaldoSebelum = (float) $toAcc->saldo_berjalan;
            $toSaldoSesudah = round($toSaldoSebelum + $jumlah, 2);
            $toAcc->saldo_berjalan = $toSaldoSesudah;
            $toAcc->save();

            KasMutasi::create([
                'nomor_transaksi'        => $nomorTransaksi . '-IN',
                'kas_bank_id'            => $toAcc->id,
                'transfer_to_kas_bank_id'=> $fromAcc->id,
                'tipe'                   => 'Masuk',
                'kategori'               => 'Transfer Antar Rekening',
                'jumlah'                 => $jumlah,
                'saldo_sebelum'          => $toSaldoSebelum,
                'saldo_sesudah'          => $toSaldoSesudah,
                'tanggal'                => $request->tanggal,
                'nama_kontak'            => $fromAcc->nama_akun,
                'keterangan'             => "Penerimaan transfer dari {$fromAcc->nama_akun}",
                'status'                 => 'Disetujui',
                'jurnal_id'              => $jurnalId,
                'company_id'             => $toCompanyId,
                'branch_id'              => $toBranchId,
                'created_by'             => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transfer saldo sebesar Rp " . number_format($jumlah, 2, ',', '.') . " dari {$fromAcc->nama_akun} ke {$toAcc->nama_akun} berhasil diproses dan dijurnal.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storeTransfer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal melakukan transfer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lihat Rincian Bukti Mutasi Kas / Voucher
     */
    public function show(KasMutasi $mutasi)
    {
        if (!$this->checkMutasiEntityAccess($mutasi)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Bukti mutasi bukan milik entitas aktif.'], 403);
        }

        $mutasi->load([
            'kasBank.coa', 
            'kasBank.branch', 
            'transferToKasBank.coa', 
            'lawanCoa', 
            'jurnal.details.account', 
            'company', 
            'branch', 
            'tender:id,nomor_kontrak,nama_tender,status,branch_id,company_id',
            'tender.branch:id,nama_cabang',
            'tender.company:id,nama_perusahaan',
            'creator:id,nama_lengkap,username',
            'canceler:id,nama_lengkap,username'
        ]);

        $lampiranUrl = $mutasi->lampiran ? asset('storage/' . $mutasi->lampiran) : null;

        $canCancel = (auth()->user()?->can('batalkan-mutasi-kas-bank') || auth()->user()?->hasRole('Super Admin'))
            && $mutasi->status === 'Disetujui'
            && $mutasi->kategori !== 'Saldo Awal Rekening'
            && !str_starts_with($mutasi->nomor_transaksi, 'SA-');

        return response()->json([
            'success'      => true,
            'mutasi'       => $mutasi,
            'lampiran_url' => $lampiranUrl,
            'can_cancel'   => $canCancel,
        ]);
    }

    /**
     * Batalkan Transaksi Kas & Rollback Saldo serta Posting Jurnal Pembalik (Atomic Reversal)
     */
    public function cancel(Request $request, KasMutasi $mutasi)
    {
        $this->authorize('batalkan-mutasi-kas-bank');

        if (!$this->checkMutasiEntityAccess($mutasi)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Mutasi bukan milik entitas aktif.'], 403);
        }

        if ($mutasi->status === 'Dibatalkan') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah berstatus Dibatalkan.'], 400);
        }

        if ($mutasi->kategori === 'Saldo Awal Rekening' || str_starts_with($mutasi->nomor_transaksi, 'SA-')) {
            return response()->json([
                'success' => false,
                'message' => 'Mutasi Saldo Awal pembukaan rekening tidak dapat dibatalkan melalui fitur ini. Silakan ubah data rekening jika diperlukan.',
            ], 422);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:40',
        ], [
            'alasan_pembatalan.required' => 'Alasan / catatan pembatalan wajib diisi.',
            'alasan_pembatalan.max'      => 'Alasan pembatalan maksimal 40 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $jumlah = (float) $mutasi->jumlah;
            $today = now()->toDateString();
            $userId = auth()->id();
            $cancelledAt = now();
            $alasan = mb_substr(trim($request->input('alasan_pembatalan')), 0, 40);

            $cancelData = [
                'status'            => 'Dibatalkan',
                'cancelled_by'      => $userId,
                'cancelled_at'      => $cancelledAt,
                'alasan_pembatalan' => $alasan,
            ];

            // Kasus 1: Transaksi Transfer Antar Rekening
            if ($mutasi->tipe === 'Transfer' || (str_ends_with($mutasi->nomor_transaksi, '-IN') && $mutasi->kategori === 'Transfer Antar Rekening')) {
                $baseNo = str_replace('-IN', '', $mutasi->nomor_transaksi);
                $outMutasi = KasMutasi::where('nomor_transaksi', $baseNo)->first();
                $inMutasi  = KasMutasi::where('nomor_transaksi', $baseNo . '-IN')->first();

                if ($outMutasi && $inMutasi) {
                    // Cegah double-reversal jika salah satu atau keduanya sudah berstatus Dibatalkan
                    if ($outMutasi->status === 'Dibatalkan' || $inMutasi->status === 'Dibatalkan') {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Transaksi transfer ini sudah pernah dibatalkan sebelumnya.',
                        ], 400);
                    }

                    $fromAcc = KasBank::lockForUpdate()->find($outMutasi->kas_bank_id);
                    $toAcc   = KasBank::lockForUpdate()->find($inMutasi->kas_bank_id);

                    // Cek apakah saldo rekening tujuan cukup untuk ditarik kembali
                    if ($jumlah > (float) $toAcc->saldo_berjalan) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Saldo rekening tujuan \"{$toAcc->nama_akun}\" tidak mencukupi untuk membatalkan transfer ini (Saldo: Rp " . number_format($toAcc->saldo_berjalan, 2, ',', '.') . ").",
                        ], 422);
                    }

                    // Revert Saldo: Kembalikan ke pengirim, kurangi dari penerima (Presisi Round 2 Desimal)
                    $fromAcc->saldo_berjalan = round((float) $fromAcc->saldo_berjalan + $jumlah, 2);
                    $fromAcc->save();

                    $toAcc->saldo_berjalan = round((float) $toAcc->saldo_berjalan - $jumlah, 2);
                    $toAcc->save();

                    // Set Status Dibatalkan untuk kedua record mutasi
                    $outMutasi->update($cancelData);
                    $inMutasi->update($cancelData);

                    // Buat Jurnal Pembalik (Reversing Entry) jika kedua akun punya COA
                    if ($fromAcc->coa_id && $toAcc->coa_id) {
                        // Original Transfer: Debit ToAccount (Penerima), Kredit FromAccount (Pengirim)
                        // Reversal Transfer: Debit FromAccount (Pengirim), Kredit ToAccount (Penerima)
                        $revJurnalId = $this->postJurnal(
                            $baseNo,
                            $today,
                            "[REVERSAL: {$alasan}] Pembatalan Transfer Saldo {$baseNo}: {$fromAcc->nama_akun} -> {$toAcc->nama_akun}",
                            $fromAcc->coa_id, // Debit (kembalikan ke pengirim)
                            $toAcc->coa_id,   // Kredit (tarik dari penerima)
                            $jumlah,
                            $outMutasi->branch_id,
                            'Kas Mutasi Reversal'
                        );
                        if ($revJurnalId) {
                            Jurnal::where('id', $revJurnalId)->update(['referensi_id' => $outMutasi->id]);
                        }
                    }
                } else {
                    // Fallback jika salah satu record tidak ditemukan
                    $account = KasBank::lockForUpdate()->find($mutasi->kas_bank_id);
                    if ($mutasi->tipe === 'Transfer') {
                        $account->saldo_berjalan = round((float) $account->saldo_berjalan + $jumlah, 2);
                    } else {
                        if ($jumlah > (float) $account->saldo_berjalan) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Saldo saat ini tidak mencukupi untuk membatalkan transaksi.'], 422);
                        }
                        $account->saldo_berjalan = round((float) $account->saldo_berjalan - $jumlah, 2);
                    }
                    $account->save();
                    $mutasi->update($cancelData);
                }

            // Kasus 2: Kas Masuk Biasa
            } elseif ($mutasi->tipe === 'Masuk') {
                $account = KasBank::lockForUpdate()->find($mutasi->kas_bank_id);
                if ($jumlah > (float) $account->saldo_berjalan) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Saldo saat ini tidak mencukupi untuk membatalkan penerimaan kas ini.'], 422);
                }
                $account->saldo_berjalan = round((float) $account->saldo_berjalan - $jumlah, 2);
                $account->save();
                $mutasi->update($cancelData);

                // Buat Jurnal Pembalik (Reversing Entry)
                if ($account->coa_id && $mutasi->lawan_coa_id) {
                    // Original Masuk: Debit Kas/Bank, Kredit Lawan COA
                    // Reversal Masuk: Debit Lawan COA, Kredit Kas/Bank
                    $revJurnalId = $this->postJurnal(
                        $mutasi->nomor_transaksi,
                        $today,
                        "[REVERSAL: {$alasan}] Pembatalan Kas Masuk {$mutasi->nomor_transaksi}: " . ($mutasi->keterangan ?: $mutasi->kategori),
                        $mutasi->lawan_coa_id, // Debit
                        $account->coa_id,      // Kredit
                        $jumlah,
                        $mutasi->branch_id,
                        'Kas Mutasi Reversal'
                    );
                    if ($revJurnalId) {
                        Jurnal::where('id', $revJurnalId)->update(['referensi_id' => $mutasi->id]);
                    }
                }

            // Kasus 3: Kas Keluar Biasa
            } elseif ($mutasi->tipe === 'Keluar') {
                $account = KasBank::lockForUpdate()->find($mutasi->kas_bank_id);
                $account->saldo_berjalan = round((float) $account->saldo_berjalan + $jumlah, 2);
                $account->save();
                $mutasi->update($cancelData);

                // Buat Jurnal Pembalik (Reversing Entry)
                if ($account->coa_id && $mutasi->lawan_coa_id) {
                    // Original Keluar: Debit Lawan COA, Kredit Kas/Bank
                    // Reversal Keluar: Debit Kas/Bank, Kredit Lawan COA
                    $revJurnalId = $this->postJurnal(
                        $mutasi->nomor_transaksi,
                        $today,
                        "[REVERSAL: {$alasan}] Pembatalan Kas Keluar {$mutasi->nomor_transaksi}: " . ($mutasi->keterangan ?: $mutasi->kategori),
                        $account->coa_id,      // Debit
                        $mutasi->lawan_coa_id, // Kredit
                        $jumlah,
                        $mutasi->branch_id,
                        'Kas Mutasi Reversal'
                    );
                    if ($revJurnalId) {
                        Jurnal::where('id', $revJurnalId)->update(['referensi_id' => $mutasi->id]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transaksi {$mutasi->nomor_transaksi} berhasil dibatalkan. Saldo telah disesuaikan kembali dan Jurnal Pembalik (Reversal Entry) otomatis dicatat.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancel KasMutasi: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Posting Transaksi Kas & Bank ke Jurnal Umum Akuntansi
     */
    private function postJurnal($nomorRef, $tanggal, $keterangan, $debitCoaId, $kreditCoaId, $nominal, $branchId = null, $refTipe = 'Kas Mutasi')
    {
        if (empty($debitCoaId) || empty($kreditCoaId) || $nominal <= 0) {
            return null;
        }

        $prefixJurnal = 'JU-' . date('ym', strtotime($tanggal)) . '-';
        $lastJurnal = DB::table('jurnals')
            ->where('nomor_jurnal', 'LIKE', $prefixJurnal . '%')
            ->orderBy('id', 'desc')
            ->first();
        $nextJurnalNum = 1;
        if ($lastJurnal) {
            $parts = explode('-', $lastJurnal->nomor_jurnal);
            $nextJurnalNum = ((int) end($parts)) + 1;
        }
        do {
            $nomorJurnal = $prefixJurnal . str_pad($nextJurnalNum, 4, '0', STR_PAD_LEFT);
            $exists = DB::table('jurnals')->where('nomor_jurnal', $nomorJurnal)->exists();
            if ($exists) {
                $nextJurnalNum++;
            }
        } while ($exists);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $jurnalCompanyId = !empty($branchId) ? null : ($activeCompanyId ?: 1);
        $jurnalBranchId  = !empty($branchId) ? $branchId : null;

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $tanggal,
            'referensi_tipe' => $refTipe,
            'referensi_id'   => null,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $nominal,
            'total_kredit'   => $nominal,
            'keterangan'     => $keterangan,
            'created_by'     => auth()->id(),
        ]);

        // Detail 1: Debit
        JurnalDetail::create([
            'jurnal_id'   => $jurnal->id,
            'account_id'  => $debitCoaId,
            'debit'       => $nominal,
            'kredit'      => 0,
            'keterangan'  => $keterangan,
        ]);

        // Detail 2: Kredit
        JurnalDetail::create([
            'jurnal_id'   => $jurnal->id,
            'account_id'  => $kreditCoaId,
            'debit'       => 0,
            'kredit'      => $nominal,
            'keterangan'  => $keterangan,
        ]);

        return $jurnal->id;
    }

    /**
     * Helper: Validasi kepemilikan / isolasi entitas untuk KasBank (Anti-IDOR)
     */
    private function checkAccountEntityAccess(KasBank $account): bool
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        if (!empty($activeBranchId)) {
            // Sesi Cabang: Hanya boleh akses rekening milik cabang aktif
            return (int) $account->branch_id === (int) $activeBranchId;
        } else {
            // Sesi PT Pusat: Hanya boleh akses rekening PT Pusat (branch_id IS NULL)
            if (!empty($account->branch_id)) {
                return false;
            }
            if ($account->company_id && (int) $account->company_id !== (int) $activeCompanyId) {
                return false;
            }
            return true;
        }
    }

    /**
     * Helper: Validasi kepemilikan / isolasi entitas untuk KasMutasi (Anti-IDOR)
     */
    private function checkMutasiEntityAccess(KasMutasi $mutasi): bool
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        if (!empty($activeBranchId)) {
            // Sesi Cabang: Hanya boleh akses mutasi milik cabang aktif
            return (int) $mutasi->branch_id === (int) $activeBranchId;
        } else {
            // Sesi PT Pusat: Hanya boleh akses mutasi PT Pusat (branch_id IS NULL)
            if (!empty($mutasi->branch_id)) {
                return false;
            }
            if ($mutasi->company_id && (int) $mutasi->company_id !== (int) $activeCompanyId) {
                return false;
            }
            return true;
        }
    }

    /**
     * Helper: Bersihkan format Rupiah / Currency Indonesia menjadi float murni
     * Contoh: "500.000" -> 500000, "1.500.000,50" -> 1500000.50, "1500000.50" -> 1500000.50
     */
    private function cleanNominal($value)
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }
        $str = trim((string) $value);
        $str = str_ireplace(['rp', ' '], '', $str);

        // Jika ada koma dan titik (e.g. 1.500.000,50): titik = ribuan, koma = desimal
        if (str_contains($str, '.') && str_contains($str, ',')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, ',')) {
            if (substr_count($str, ',') > 1) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        } elseif (str_contains($str, '.')) {
            if (substr_count($str, '.') > 1) {
                // Lebih dari satu titik, pasti pemisah ribuan (1.500.000)
                $str = str_replace('.', '', $str);
            } else {
                // Tepat satu titik: jika diikuti tepat 3 digit (format ribuan 500.000), buang titik
                $parts = explode('.', $str);
                if (isset($parts[1]) && strlen($parts[1]) === 3) {
                    $str = str_replace('.', '', $str);
                }
            }
        }

        $str = preg_replace('/[^0-9.-]/', '', $str);
        return (float) $str;
    }

    /**
     * Helper: Format angka moneter Rupiah presisi dengan 2 desimal di belakang koma
     */
    private function formatRupiah($val): string
    {
        return number_format((float) $val, 2, ',', '.');
    }

    /**
     * Hitung Ringkasan Saldo Periode Ledger: Saldo Awal, Total Masuk, Total Keluar, Saldo Akhir
     */
    public function calculateLedgerPeriodSummary($accountId = null, $startDate = null, $endDate = null): array
    {
        $accountsQuery = KasBank::forActiveCompany()->forActiveBranch()->where('is_active', true);
        if ($accountId) {
            $accountsQuery->where('id', $accountId);
        }
        $accounts = $accountsQuery->get();
        $accountIds = $accounts->pluck('id')->toArray();

        if (empty($accountIds)) {
            return [
                'saldo_awal'             => 0,
                'total_masuk'            => 0,
                'total_keluar'           => 0,
                'saldo_akhir'            => 0,
                'net_mutasi'             => 0,
                'formatted_saldo_awal'   => 'Rp ' . $this->formatRupiah(0),
                'formatted_total_masuk'  => '+ Rp ' . $this->formatRupiah(0),
                'formatted_total_keluar' => '- Rp ' . $this->formatRupiah(0),
                'formatted_saldo_akhir'  => 'Rp ' . $this->formatRupiah(0),
                'label_saldo_awal'       => 'Saldo awal per ' . ($startDate ? date('d/m/Y', strtotime($startDate)) : '-'),
                'label_saldo_akhir'      => 'Saldo akhir per ' . ($endDate ? date('d/m/Y', strtotime($endDate)) : '-'),
            ];
        }

        // Akun-akun yang memiliki saldo_awal > 0 tapi belum memiliki transaksi SA- (Optimasi 1 Query Tanpa N+1)
        $baseOpeningBalance = 0;
        $accountsWithOpening = $accounts->where('saldo_awal', '>', 0);
        if ($accountsWithOpening->isNotEmpty()) {
            $existingSaAccountIds = KasMutasi::forActiveCompany()->forActiveBranch()
                ->whereIn('kas_bank_id', $accountsWithOpening->pluck('id'))
                ->where(function($q) {
                    $q->where('kategori', 'Saldo Awal Rekening')
                      ->orWhere('nomor_transaksi', 'LIKE', 'SA-%');
                })
                ->pluck('kas_bank_id')
                ->unique()
                ->toArray();

            foreach ($accountsWithOpening as $acc) {
                if (!in_array($acc->id, $existingSaAccountIds)) {
                    $baseOpeningBalance += (float) $acc->saldo_awal;
                }
            }
        }

        // 1. Saldo Awal Periode: Semua mutasi sebelum $startDate (1 query efisien dengan conditional aggregation)
        $saldoAwalPeriode = $baseOpeningBalance;
        if ($startDate) {
            $beforeAgg = KasMutasi::forActiveCompany()->forActiveBranch()
                ->whereIn('kas_bank_id', $accountIds)
                ->where('status', 'Disetujui')
                ->where('tanggal', '<', $startDate)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN tipe = 'Masuk' THEN jumlah ELSE 0 END), 0) as total_masuk,
                    COALESCE(SUM(CASE WHEN tipe IN ('Keluar', 'Transfer') THEN jumlah ELSE 0 END), 0) as total_keluar
                ")
                ->first();

            $saldoAwalPeriode += ((float) ($beforeAgg?->total_masuk ?? 0) - (float) ($beforeAgg?->total_keluar ?? 0));
        }

        // 2. Mutasi Dalam Periode [$startDate s/d $endDate] (1 query efisien dengan conditional aggregation)
        $mutasiPeriodQuery = KasMutasi::forActiveCompany()->forActiveBranch()
            ->whereIn('kas_bank_id', $accountIds)
            ->where('status', 'Disetujui');

        if ($startDate && $endDate) {
            $mutasiPeriodQuery->whereBetween('tanggal', [$startDate, $endDate]);
        } elseif ($startDate) {
            $mutasiPeriodQuery->where('tanggal', '>=', $startDate);
        } elseif ($endDate) {
            $mutasiPeriodQuery->where('tanggal', '<=', $endDate);
        }

        $periodAgg = (clone $mutasiPeriodQuery)->selectRaw("
            COALESCE(SUM(CASE WHEN tipe = 'Masuk' THEN jumlah ELSE 0 END), 0) as total_masuk,
            COALESCE(SUM(CASE WHEN tipe IN ('Keluar', 'Transfer') THEN jumlah ELSE 0 END), 0) as total_keluar
        ")->first();

        $totalMasuk = (float) ($periodAgg?->total_masuk ?? 0);
        $totalKeluar = (float) ($periodAgg?->total_keluar ?? 0);
        $netMutasi = $totalMasuk - $totalKeluar;

        // 3. Saldo Akhir Periode = Saldo Awal Periode + Total Masuk - Total Keluar
        $saldoAkhirPeriode = $saldoAwalPeriode + $totalMasuk - $totalKeluar;

        $labelAwal = $startDate ? ('Per ' . date('d/m/Y', strtotime($startDate))) : 'Awal Pembukuan';
        $labelAkhir = $endDate ? ('Per ' . date('d/m/Y', strtotime($endDate))) : 'Posisi Saat Ini';

        return [
            'saldo_awal'             => $saldoAwalPeriode,
            'total_masuk'            => $totalMasuk,
            'total_keluar'           => $totalKeluar,
            'saldo_akhir'            => $saldoAkhirPeriode,
            'net_mutasi'             => $netMutasi,
            'formatted_saldo_awal'   => 'Rp ' . $this->formatRupiah($saldoAwalPeriode),
            'formatted_total_masuk'  => '+ Rp ' . $this->formatRupiah($totalMasuk),
            'formatted_total_keluar' => '- Rp ' . $this->formatRupiah($totalKeluar),
            'formatted_saldo_akhir'  => 'Rp ' . $this->formatRupiah($saldoAkhirPeriode),
            'label_saldo_awal'       => $labelAwal,
            'label_saldo_akhir'      => $labelAkhir,
        ];
    }

    /**
     * Helper: Kompresi Gambar Bukti / Kuitansi Kas Masuk & Kas Keluar ke Ukuran KB (WebP/JPEG)
     * Mengurangi ukuran foto kamera HP/struk (misal 3-10 MB) menjadi berukuran KB (~60-200 KB)
     * dengan tetap menjaga teks dan angka bukti transaksi tajam dan terbaca jelas.
     */
    private function handleReceiptUpload($file, $oldPath = null): ?string
    {
        if (!$file || !$file->isValid()) {
            return $oldPath;
        }

        // Hapus file lama jika ada (saat update)
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $mime = $file->getMimeType();
        $realExt = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());

        // Jika berupa dokumen PDF, verifikasi magic header %PDF-
        if ($realExt === 'pdf' || $mime === 'application/pdf') {
            $header = @file_get_contents($file->getRealPath(), false, null, 0, 5);
            if ($header !== '%PDF-') {
                throw new \InvalidArgumentException('Berkas PDF tidak valid atau rusak.');
            }
            $filename = 'bukti_kb_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
            return $file->storeAs('kas_bank_receipts', $filename, 'public');
        }

        $directory = 'kas_bank_receipts';
        $fullDirPath = storage_path('app/public/' . $directory);

        if (!file_exists($fullDirPath)) {
            @mkdir($fullDirPath, 0755, true);
        }

        $filename = 'bukti_kb_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.webp';
        $targetPath = $fullDirPath . '/' . $filename;
        $relativePath = $directory . '/' . $filename;

        // Cek dimensi & validitas gambar dengan getimagesize
        $imageInfo = @getimagesize($file->getRealPath());
        if (!$imageInfo) {
            throw new \InvalidArgumentException('Format gambar tidak valid atau berkas rusak.');
        }

        $imageMime = $imageInfo['mime'];
        $srcImage = null;

        switch ($imageMime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($file->getRealPath());
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($file->getRealPath());
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($file->getRealPath());
                break;
            default:
                throw new \InvalidArgumentException('Tipe gambar tidak didukung untuk kompresi bukti transaksi.');
        }

        if (!$srcImage) {
            throw new \InvalidArgumentException('Gagal memproses gambar bukti transaksi.');
        }

        // Koreksi orientasi foto smartphone via metadata EXIF agar tidak terbalik / miring
        if (function_exists('exif_read_data') && ($imageMime === 'image/jpeg' || $imageMime === 'image/jpg')) {
            try {
                $exif = @exif_read_data($file->getRealPath());
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $srcImage = imagerotate($srcImage, 180, 0);
                            break;
                        case 6:
                            $srcImage = imagerotate($srcImage, -90, 0);
                            break;
                        case 8:
                            $srcImage = imagerotate($srcImage, 90, 0);
                            break;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore failure in reading EXIF metadata
            }
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Resize proporsional: batas maksimal sisi terpanjang 1600px (sangat tajam untuk teks nota dan angka)
        $maxDimension = 1600;
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }

            $dstImage = imagecreatetruecolor($newWidth, $newHeight);

            // Pertahankan transparansi bila ada (PNG/WebP)
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($srcImage);
            $srcImage = $dstImage;
        }

        // Simpan kompresi berkualitas tinggi dalam format WebP (Quality 80)
        $saved = false;
        if (function_exists('imagewebp')) {
            $saved = @imagewebp($srcImage, $targetPath, 80);
        }

        // Fallback JPEG jika imagewebp gagal atau tidak tersedia di modul GD
        if (!$saved) {
            $filename = 'bukti_kb_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.jpg';
            $targetPath = $fullDirPath . '/' . $filename;
            $relativePath = $directory . '/' . $filename;
            $saved = @imagejpeg($srcImage, $targetPath, 82);
        }

        imagedestroy($srcImage);

        if (!$saved || !file_exists($targetPath)) {
            throw new \RuntimeException('Gagal menyimpan berkas gambar terkompresi.');
        }

        return $relativePath;
    }
}
