<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Tender;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\PettyCashKategoriMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Shuchkin\SimpleXLSXGen;

class PettyCashController extends Controller
{
    /**
     * Konfigurasi Pemetaan (Mapping) Kategori Kas Kecil ke COA Beban
     * Diambil secara dinamis dari database (PettyCashKategoriMapping) per PT aktif
     */
    public static function getCategoryMapping(): array
    {
        try {
            $dbMappings = PettyCashKategoriMapping::forActiveCompany()
                ->active()
                ->with('coa')
                ->orderBy('nama_kategori')
                ->get();

            if ($dbMappings->isNotEmpty()) {
                $result = [];
                foreach ($dbMappings as $map) {
                    $result[$map->nama_kategori] = [
                        'id'          => $map->id,
                        'label'       => $map->nama_kategori,
                        'coa_id'      => $map->coa_id,
                        'coa_code'    => $map->coa?->code ?? '',
                        'coa_name'    => $map->coa?->name ?? '',
                        'description' => $map->deskripsi ?? '',
                        'icon'        => $map->icon ?: 'bi-tag',
                        'badge'       => $map->warna_badge ?: 'primary'
                    ];
                }
                return $result;
            }
        } catch (\Exception $e) {
            Log::warning('Gagal load dynamic PettyCashKategoriMapping: ' . $e->getMessage());
        }

        // Fallback default jika database kosong / belum di-seed
        return [
            'Operasional & ATK' => [
                'label'       => 'Operasional & ATK',
                'coa_code'    => '6105',
                'coa_name'    => 'Beban Perlengkapan & ATK',
                'description' => 'Kertas, pulpen, materai, map, tinta printer, fotocopy',
                'icon'        => 'bi-paperclip',
                'badge'       => 'info'
            ],
            'BBM & Transportasi' => [
                'label'       => 'BBM & Transportasi',
                'coa_code'    => '6108',
                'coa_name'    => 'Beban Perjalanan Dinas & Entertainment',
                'description' => 'Bensin motor/mobil kantor, kartu e-toll, parkir, tiket',
                'icon'        => 'bi-fuel-pump',
                'badge'       => 'danger'
            ],
            'Konsumsi & Jamuan' => [
                'label'       => 'Konsumsi & Jamuan',
                'coa_code'    => '6108',
                'coa_name'    => 'Beban Perjalanan Dinas & Entertainment',
                'description' => 'Snack meeting, konsumsi lembur, air galon, kopi & teh kantor',
                'icon'        => 'bi-cup-hot',
                'badge'       => 'warning'
            ],
            'Kurir & Ekspedisi' => [
                'label'       => 'Kurir & Ekspedisi',
                'coa_code'    => '6104',
                'coa_name'    => 'Beban Pengiriman Penjualan (Freight-Out)',
                'description' => 'JNE, J&T, SiCepat, GoSend, GrabExpress, pengiriman dokumen',
                'icon'        => 'bi-box-seam',
                'badge'       => 'primary'
            ],
            'Maintenance & Kebersihan' => [
                'label'       => 'Maintenance & Kebersihan',
                'coa_code'    => '6103',
                'coa_name'    => 'Beban Sewa & Pemeliharaan Kantor/Gudang',
                'description' => 'Sabun cuci, sapu, servis AC, perbaikan kran/lampu, tukang',
                'icon'        => 'bi-tools',
                'badge'       => 'success'
            ],
            'Perlengkapan & Utilitas' => [
                'label'       => 'Perlengkapan & Utilitas',
                'coa_code'    => '6102',
                'coa_name'    => 'Beban Listrik, Air & Telepon',
                'description' => 'Token PLN darurat, tagihan air PAM, pulsa telepon kantor',
                'icon'        => 'bi-lightning-charge',
                'badge'       => 'warning'
            ],
            'Pemasaran & Promosi' => [
                'label'       => 'Pemasaran & Promosi',
                'coa_code'    => '6107',
                'coa_name'    => 'Beban Pemasaran & Promosi',
                'description' => 'Cetak banner kecil, brosur, promosi lokal, merchandise',
                'icon'        => 'bi-megaphone',
                'badge'       => 'secondary'
            ],
            'Beban Operasional Lainnya' => [
                'label'       => 'Beban Operasional Lainnya',
                'coa_code'    => '6100',
                'coa_name'    => 'Beban Penjualan & Operasional',
                'description' => 'Biaya operasional kas kecil lainnya yang belum terdaftar',
                'icon'        => 'bi-briefcase',
                'badge'       => 'dark'
            ],
        ];
    }

    /**
     * Tampilan Utama Mobile-First Modul Petty Cash (Otomatis Sesuai Sesi PT / Cabang Aktif)
     */
    public function index(Request $request)
    {
        $this->authorize('lihat-petty-cash');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id ?? 1;

        if (!empty($activeBranchId)) {
            $currentBranch = Branch::find($activeBranchId);
            if ($currentBranch) {
                $activeCompanyId = $currentBranch->company_id;
            }
        } else {
            $currentBranch = null;
        }

        $currentCompany   = Company::find($activeCompanyId);
        $activeEntityName = $currentBranch ? $currentBranch->nama_cabang : ($currentCompany?->nama_perusahaan ?? 'PT Pusat');
        $isCabang         = !empty($activeBranchId);

        // Hak akses filter: jika user tidak memiliki izin 'filter-petty-cash', gunakan nilai default agar halaman awal tetap dapat dibuka tanpa error 403
        $canFilter = auth()->user()->can('filter-petty-cash');

        $tipeFilter     = $canFilter ? $request->get('tipe', 'all') : 'all'; // 'all', 'Pengeluaran', 'Pemasukan', 'Klaim'
        $rawStartDate   = $canFilter ? $request->get('start_date') : null;
        $rawEndDate     = $canFilter ? $request->get('end_date') : null;
        $startDate      = (is_string($rawStartDate) && strtotime($rawStartDate)) ? date('Y-m-d', strtotime($rawStartDate)) : now()->startOfMonth()->toDateString();
        $endDate        = (is_string($rawEndDate) && strtotime($rawEndDate)) ? date('Y-m-d', strtotime($rawEndDate)) : now()->endOfMonth()->toDateString();
        $search         = $canFilter ? $request->get('search') : null;
        $tenderFilter   = $canFilter ? $request->get('tender_id') : null;
        $kategoriFilter = $canFilter ? $request->get('kategori') : null;
        $sortBy         = $request->get('sort_by', 'tanggal');
        $sortOrder      = $request->get('sort_order', 'desc');
        $coaKasFilter   = $canFilter ? $request->get('coa_kas_id') : null;
        $pemohonFilter  = $canFilter ? $request->get('nama_pengaju') : null;

        // Query transaksi Kas Kecil otomatis sesuai sesi PT / Cabang aktif (Strict Isolation)
        $query = PettyCash::with([
            'coaKas:id,code,name',
            'lawanCoa:id,code,name',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,kode_cabang,nama_cabang',
            'company:id,kode_perusahaan,nama_perusahaan',
            'creator:id,nama_lengkap,username'
        ]);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
        } else {
            $query->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        // Filter Tipe
        if ($tipeFilter !== 'all' && !empty($tipeFilter)) {
            $query->where('tipe', $tipeFilter);
        }

        // Filter Kategori Transaksi
        if (!empty($kategoriFilter) && $kategoriFilter !== 'all') {
            $query->where('kategori', $kategoriFilter);
        }

        // Filter Tanggal
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        // Filter Tender
        if (!empty($tenderFilter)) {
            $query->where('tender_id', $tenderFilter);
        }

        // Filter Akun Kas (COA Kas)
        if (!empty($coaKasFilter) && $coaKasFilter !== 'all') {
            $query->where('coa_kas_id', $coaKasFilter);
        }

        // Filter Nama Pemohon
        if (!empty($pemohonFilter) && $pemohonFilter !== 'all') {
            $query->where('nama_pengaju', 'like', "%{$pemohonFilter}%");
        }

        // Filter Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('nama_pengaju', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        // Pengurutan (Sorting) Transaksi
        if (in_array($sortBy, ['tanggal', 'kategori', 'nominal', 'nomor_transaksi'])) {
            $direction = $sortOrder === 'asc' ? 'asc' : 'desc';
            if ($sortBy === 'tanggal') {
                $query->orderBy('tanggal', $direction)
                      ->orderBy('created_at', $direction)
                      ->orderBy('id', $direction);
            } elseif ($sortBy === 'kategori') {
                $query->orderBy('kategori', $direction)
                      ->orderBy('tanggal', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->orderBy('id', 'desc');
            } elseif ($sortBy === 'nominal') {
                $query->orderBy('nominal', $direction)
                      ->orderBy('tanggal', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->orderBy('id', 'desc');
            } elseif ($sortBy === 'nomor_transaksi') {
                $query->orderBy('nomor_transaksi', $direction)
                      ->orderBy('tanggal', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('tanggal', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->orderBy('id', 'desc');
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        $transactions = $query->paginate($perPage)->withQueryString();

        $categoryMapping = self::getCategoryMapping();

        // Master Data COA untuk Dropdown
        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        // Ambil HANYA akun Kas (Kas Tunai / Kas Kecil) terdaftar di KasBank untuk Entitas Ini (Strict Isolation)
        $kasAccountsQuery = KasBank::with('coa')
            ->where(function ($q) {
                $q->where('tipe', 'Kas Tunai')
                  ->orWhere('nama_akun', 'like', '%Kas Kecil%')
                  ->orWhere('nama_akun', 'like', '%Kas Tunai%')
                  ->orWhere('nama_akun', 'like', '%Kasir%');
            })
            ->where('is_active', true);

        if (!empty($activeBranchId)) {
            $kasAccountsQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
        } else {
            $kasAccountsQuery->where('company_id', $activeCompanyId)->whereNull('branch_id');
        }
        $kasAccounts = $kasAccountsQuery->get();

        $kasKecilAccount = $kasAccounts->first();
        $saldoKasKecil   = $kasKecilAccount ? max(0, (float) $kasKecilAccount->saldo_berjalan) : 0;
        $coaKasAccounts  = $kasAccounts; // Alias kompatibilitas view

        // Statistik Pengeluaran Tanggal Berjalan (Hari Ini) untuk Entitas Ini
        $todayDate = now()->toDateString();
        $statOutQuery = PettyCash::where('status', 'Disetujui')
            ->whereIn('tipe', ['Pengeluaran', 'Klaim'])
            ->whereDate('tanggal', $todayDate);

        if (!empty($activeBranchId)) {
            $statOutQuery->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
        } else {
            $statOutQuery->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        // Statistik Pengeluaran Bulan Ini
        $statMonthQuery = PettyCash::where('status', 'Disetujui')
            ->whereIn('tipe', ['Pengeluaran', 'Klaim'])
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        if (!empty($activeBranchId)) {
            $statMonthQuery->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
        } else {
            $statMonthQuery->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        // Jika filter akun kas aktif, sesuaikan card saldo dan statistik pengeluaran dengan akun kas yang dipilih
        if (!empty($coaKasFilter) && $coaKasFilter !== 'all') {
            $filteredKas = $kasAccounts->firstWhere('coa_id', $coaKasFilter);
            if ($filteredKas) {
                $kasKecilAccount = $filteredKas;
                $saldoKasKecil   = max(0, (float) $kasKecilAccount->saldo_berjalan);
            }
            $statOutQuery->where('coa_kas_id', $coaKasFilter);
            $statMonthQuery->where('coa_kas_id', $coaKasFilter);
        }

        $todayOut = $statOutQuery->sum('nominal');
        $todayCount = (clone $statOutQuery)->count();

        $thisMonthOut = $statMonthQuery->sum('nominal');
        $thisMonthCount = (clone $statMonthQuery)->count();

        // Data Top Up Kas Kecil dari Menu Transfer Kas & Bank (Hari Ini & Bulan Ini)
        $todayTopUp = 0;
        $thisMonthTopUp = 0;
        $lastTopUp = null;
        if ($kasKecilAccount) {
            $topUpQuery = KasMutasi::where('kas_bank_id', $kasKecilAccount->id)
                ->where('tipe', 'Masuk')
                ->where(function ($q) {
                    $q->where('kategori', 'like', '%Transfer%')
                      ->orWhereNotNull('transfer_to_kas_bank_id');
                })
                ->where(function ($q) {
                    $q->whereNull('nomor_referensi')
                      ->orWhere('nomor_referensi', 'not like', 'PC-%');
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
                });

            if (!empty($activeBranchId)) {
                $topUpQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $topUpQuery->where('company_id', $activeCompanyId)->whereNull('branch_id');
            }

            $todayTopUp = (clone $topUpQuery)
                ->whereDate('tanggal', $todayDate)
                ->sum('jumlah');

            $thisMonthTopUp = (clone $topUpQuery)
                ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('jumlah');

            $lastTopUp = (clone $topUpQuery)
                ->with(['transferToKasBank'])
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->first();
        }

        // Master Tender Aktif (Strict Entity Isolation: PT Pusat vs Cabang)
        $tenderQuery = Tender::with(['branch:id,nama_cabang', 'company:id,nama_perusahaan']);
        if (!empty($activeBranchId)) {
            // Aktif di Cabang -> Hanya tender milik cabang aktif ini
            $tenderQuery->where('branch_id', $activeBranchId);
        } else {
            // Aktif di PT Pusat -> Hanya tender milik PT (branch_id IS NULL)
            $tenderQuery->where('company_id', $activeCompanyId)
                        ->whereNull('branch_id');
        }
        $tenders = $tenderQuery->orderBy('nomor_kontrak', 'desc')->get();

        // Data PT / Cabang aktif
        $currentCompany = Company::find($activeCompanyId);
        $currentBranch  = $activeBranchId ? Branch::find($activeBranchId) : null;
        $activeEntityName = $currentBranch ? ('Cabang ' . $currentBranch->nama_cabang) : ($currentCompany->nama_perusahaan ?? 'PT Pusat');
        $isCabang = !empty($activeBranchId);

        // Daftar Nama Pemohon untuk Dropdown Filter & Lookup Modal (Dichache 1 Jam & Dioptimasi Skala 7M+)
        $cacheKeyList  = "pc_pemohon_list_" . ($activeBranchId ? "br_{$activeBranchId}" : "co_{$activeCompanyId}");
        $cacheKeyStats = "pc_pemohon_stats_" . ($activeBranchId ? "br_{$activeBranchId}" : "co_{$activeCompanyId}");

        $pemohonList = Cache::remember($cacheKeyList, 3600, function () use ($activeBranchId, $activeCompanyId) {
            $pemohonQuery = PettyCash::whereNotNull('nama_pengaju')
                ->where('nama_pengaju', '!=', '');
            if (!empty($activeBranchId)) {
                $pemohonQuery->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
            } else {
                $pemohonQuery->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
            }
            return $pemohonQuery->distinct()->orderBy('nama_pengaju', 'asc')->pluck('nama_pengaju');
        });

        $pemohonStats = Cache::remember($cacheKeyStats, 3600, function () use ($activeBranchId, $activeCompanyId) {
            $pemohonQuery = PettyCash::whereNotNull('nama_pengaju')
                ->where('nama_pengaju', '!=', '');
            if (!empty($activeBranchId)) {
                $pemohonQuery->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
            } else {
                $pemohonQuery->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
            }
            return $pemohonQuery->selectRaw('nama_pengaju, count(*) as total_trx, max(tanggal) as terakhir_tgl')
                ->groupBy('nama_pengaju')
                ->orderBy('nama_pengaju', 'asc')
                ->get();
        });

        return view('modul_accounting.petty_cash.index', compact(
            'transactions',
            'perPage',
            'saldoKasKecil',
            'kasKecilAccount',
            'thisMonthOut',
            'thisMonthCount',
            'thisMonthTopUp',
            'todayDate',
            'todayOut',
            'todayCount',
            'todayTopUp',
            'lastTopUp',
            'coaAccounts',
            'coaKasAccounts',
            'tenders',
            'currentCompany',
            'currentBranch',
            'activeEntityName',
            'isCabang',
            'tipeFilter',
            'startDate',
            'endDate',
            'search',
            'tenderFilter',
            'kategoriFilter',
            'categoryMapping',
            'sortBy',
            'sortOrder',
            'coaKasFilter',
            'pemohonFilter',
            'pemohonList',
            'pemohonStats'
        ));
    }

    /**
     * Helper: Siapkan Data Laporan Kas Kecil (Dipakai Bersama oleh Cetak PDF & Ekspor Excel)
     */
    protected function getReportData(Request $request): array
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id ?? 1;

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            if ($branch) {
                $activeCompanyId = $branch->company_id;
            }
        } else {
            $branch = null;
        }

        $company          = Company::find($activeCompanyId);
        $activeEntityName = $branch ? $branch->nama_cabang : ($company?->nama_perusahaan ?? 'PT Pusat');
        $isCabang         = !empty($activeBranchId);

        $tipeFilter     = $request->get('tipe', 'all');
        $rawStartDate   = $request->get('start_date');
        $rawEndDate     = $request->get('end_date');
        $startDate      = (is_string($rawStartDate) && strtotime($rawStartDate)) ? date('Y-m-d', strtotime($rawStartDate)) : now()->startOfMonth()->toDateString();
        $endDate        = (is_string($rawEndDate) && strtotime($rawEndDate)) ? date('Y-m-d', strtotime($rawEndDate)) : now()->endOfMonth()->toDateString();
        $search         = $request->get('search');
        $tenderFilter   = $request->get('tender_id');
        $kategoriFilter = $request->get('kategori');
        $sortBy         = $request->get('sort_by', 'tanggal');
        $sortOrder      = $request->get('sort_order', 'asc');
        $coaKasFilter   = $request->get('coa_kas_id');
        $pemohonFilter  = $request->get('nama_pengaju');

        // Akun Kas Info (Strict Isolation PT vs Cabang)
        $kasAccount = null;
        if (!empty($coaKasFilter) && $coaKasFilter !== 'all') {
            $kasAccQ = KasBank::where('coa_id', $coaKasFilter);
            if (!empty($activeBranchId)) {
                $kasAccQ->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $kasAccQ->where('company_id', $activeCompanyId)->whereNull('branch_id');
            }
            $kasAccount = $kasAccQ->first();
        }
        if (!$kasAccount) {
            $kasAccQ = KasBank::where(function($q) {
                $q->where('tipe', 'Kas Tunai')->orWhere('nama_akun', 'like', '%Kas Kecil%');
            });
            if (!empty($activeBranchId)) {
                $kasAccQ->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $kasAccQ->where('company_id', $activeCompanyId)->whereNull('branch_id');
            }
            $kasAccount = $kasAccQ->first();
        }

        // Query SEMUA transaksi Kas Kecil periode ini untuk entitas aktif (tanpa filter tampilan dulu agar running balance akurat)
        $query = PettyCash::with([
            'coaKas:id,code,name',
            'lawanCoa:id,code,name',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,kode_cabang,nama_cabang',
            'company:id,kode_perusahaan,nama_perusahaan',
            'creator:id,nama_lengkap,username'
        ]);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
        } else {
            $query->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        // Jangan sertakan transaksi yang dibatalkan pada laporan buku kas karena dana sudah dikembalikan
        $query->where('status', '!=', 'Dibatalkan');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        if (!empty($coaKasFilter) && $coaKasFilter !== 'all') {
            $query->where('coa_kas_id', $coaKasFilter);
        }

        $allPettyTransactions = $query->get();

        // Siapkan sort_key untuk pettyTransactions secara presisi (Tanggal & Jam Transaksi ASC)
        foreach ($allPettyTransactions as $pt) {
            $pt->is_transfer = false;
            $dateStr = \Carbon\Carbon::parse($pt->tanggal)->format('Y-m-d');
            $timeStr = !empty($pt->created_at) ? \Carbon\Carbon::parse($pt->created_at)->format('H:i:s') : '00:00:00';
            $typePriority = ($pt->tipe === 'Pemasukan' || $pt->kategori === 'Top Up Kas Kecil') ? '0' : '1';
            $pt->sort_key = "{$dateStr}_{$timeStr}_{$typePriority}_{$pt->id}";
        }

        // Ambil Transfer Mutasi (Top Up Masuk & Pengosongan Keluar) dari KasMutasi untuk Akun Kas Kecil
        $transferItems = collect();
        if ($kasAccount) {
            $transferQuery = KasMutasi::where('kas_bank_id', $kasAccount->id)
                ->where(function ($q) {
                    $q->where('kategori', 'like', '%Transfer%')
                      ->orWhereNotNull('transfer_to_kas_bank_id');
                })
                ->where(function ($q) {
                    $q->whereNull('nomor_referensi')
                      ->orWhere('nomor_referensi', 'not like', 'PC-%');
                })
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
                });

            if (!empty($activeBranchId)) {
                $transferQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $transferQuery->where('company_id', $activeCompanyId)->whereNull('branch_id');
            }

            if ($startDate && $endDate) {
                $transferQuery->whereBetween('tanggal', [$startDate, $endDate]);
            }

            $rawTransfers = $transferQuery->with(['transferToKasBank', 'creator'])->get();

            foreach ($rawTransfers as $tm) {
                $isTopUp = ($tm->tipe === 'Masuk');
                $item = new \stdClass();
                $item->id = 'tm_' . $tm->id;
                $item->nomor_transaksi = $tm->nomor_transaksi;
                $item->tanggal = $tm->tanggal;
                $item->tipe = $isTopUp ? 'Pemasukan' : 'Pengeluaran';
                $item->kategori = $isTopUp ? 'Top Up Kas Kecil' : 'Pengosongan Kas Kecil';
                $item->nominal = (float) $tm->jumlah;
                $item->saldo_sebelum = (float) $tm->saldo_sebelum;
                $item->saldo_sesudah = (float) $tm->saldo_sesudah;
                $item->keterangan = $tm->keterangan ?: ($isTopUp ? 'Penerimaan transfer dari ' . ($tm->transferToKasBank?->nama_akun ?? 'Bank') : 'Transfer pengosongan kas kecil');
                $item->nama_pengaju = $tm->creator?->nama_lengkap ?? 'Finance / Kasir';
                $item->tender = null;
                $item->tender_id = null;
                $item->is_transfer = true;
                $item->status = 'Disetujui';
                $item->alasan_pembatalan = null;
                $item->created_at = $tm->created_at;

                $dateStr = \Carbon\Carbon::parse($tm->tanggal)->format('Y-m-d');
                $timeStr = !empty($tm->created_at) ? \Carbon\Carbon::parse($tm->created_at)->format('H:i:s') : '00:00:00';
                $typePriority = $isTopUp ? '0' : '1';
                $item->sort_key = "{$dateStr}_{$timeStr}_{$typePriority}_{$tm->id}";
                $transferItems->push($item);
            }
        }

        // 1. Urutkan SEMUA transaksi secara kronologis terlebih dahulu (Tanggal & Jam Transaksi ASC)
        $chronologicalTransactions = $allPettyTransactions->concat($transferItems)->sortBy('sort_key')->values();

        // Hitung Saldo Awal Kas Kecil sebelum startDate
        $saldoAwal = 0;
        if ($kasAccount && $startDate) {
            $lastBeforeQ = KasMutasi::where('kas_bank_id', $kasAccount->id)
                ->where('tanggal', '<', $startDate)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
                });

            if (!empty($activeBranchId)) {
                $lastBeforeQ->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $lastBeforeQ->where('company_id', $activeCompanyId)->whereNull('branch_id');
            }

            $lastBefore = $lastBeforeQ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
            if ($lastBefore) {
                $saldoAwal = (float) $lastBefore->saldo_sesudah;
            } else {
                $firstMutasi = KasMutasi::where('kas_bank_id', $kasAccount->id)
                    ->where('tanggal', '>=', $startDate)
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
                    })
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('id', 'asc')
                    ->first();
                if ($firstMutasi) {
                    $saldoAwal = (float) $firstMutasi->saldo_sebelum;
                } else {
                    $firstTrx = $chronologicalTransactions->first();
                    $saldoAwal = $firstTrx ? (float) ($firstTrx->saldo_sebelum ?? 0) : ($kasAccount ? (float)$kasAccount->saldo_berjalan : 0);
                }
            }
        }

        // Kalkulasi running balance kronologis aktual pada SEMUA transaksi
        $currentRunning = (float) $saldoAwal;
        $totalMasukPeriod = 0;
        $totalKeluarPeriod = 0;

        foreach ($chronologicalTransactions as $item) {
            $isMasuk = ($item->tipe === 'Pemasukan');
            $nom = (float) $item->nominal;
            if ($isMasuk) {
                $currentRunning += $nom;
                $totalMasukPeriod += $nom;
            } else {
                $currentRunning -= $nom;
                $totalKeluarPeriod += $nom;
            }
            $item->running_balance = $currentRunning;
        }
        $saldoAkhirPeriod = $currentRunning;

        // 2. Sekarang filter baris transaksi HANYA untuk kebutuhan tampilan laporan (Display Filtering)
        $filteredTransactions = $chronologicalTransactions;

        if ($tipeFilter !== 'all' && !empty($tipeFilter)) {
            $filteredTransactions = $filteredTransactions->filter(function ($item) use ($tipeFilter) {
                return $item->tipe === $tipeFilter;
            });
        }

        if (!empty($kategoriFilter) && $kategoriFilter !== 'all') {
            $filteredTransactions = $filteredTransactions->filter(function ($item) use ($kategoriFilter) {
                return strcasecmp(trim($item->kategori ?? ''), trim($kategoriFilter)) === 0;
            });
        }

        if (!empty($tenderFilter)) {
            $filteredTransactions = $filteredTransactions->filter(function ($item) use ($tenderFilter) {
                return !empty($item->tender_id) && (string)$item->tender_id === (string)$tenderFilter;
            });
        }

        if (!empty($pemohonFilter) && $pemohonFilter !== 'all') {
            $filteredTransactions = $filteredTransactions->filter(function ($item) use ($pemohonFilter) {
                return stripos($item->nama_pengaju ?? '', $pemohonFilter) !== false;
            });
        }

        if (!empty($search)) {
            $kw = strtolower($search);
            $filteredTransactions = $filteredTransactions->filter(function ($item) use ($kw) {
                return str_contains(strtolower($item->nomor_transaksi ?? ''), $kw)
                    || str_contains(strtolower($item->keterangan ?? ''), $kw)
                    || str_contains(strtolower($item->nama_pengaju ?? ''), $kw)
                    || str_contains(strtolower($item->kategori ?? ''), $kw);
            });
        }

        // 3. Terapkan Pengurutan (Sorting) Laporan Sesuai Pilihan Filter Pengguna
        if ($sortBy === 'tanggal') {
            $direction = strtolower($sortOrder ?? 'desc') === 'asc' ? 'asc' : 'desc';
            $transactions = ($direction === 'asc')
                ? $filteredTransactions->sortBy('sort_key')->values()
                : $filteredTransactions->sortByDesc('sort_key')->values();
        } elseif ($sortBy === 'kategori') {
            $direction = strtolower($sortOrder) === 'desc' ? 'desc' : 'asc';
            $transactions = $filteredTransactions->sort(function ($a, $b) use ($direction) {
                $catA = trim($a->kategori ?? '');
                $catB = trim($b->kategori ?? '');
                $cmp = strcasecmp($catA, $catB);
                if ($cmp !== 0) {
                    return $direction === 'desc' ? -$cmp : $cmp;
                }
                return strcmp($a->sort_key ?? '', $b->sort_key ?? '');
            })->values();
        } elseif ($sortBy === 'nominal') {
            $direction = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $transactions = $filteredTransactions->sort(function ($a, $b) use ($direction) {
                $cmp = ((float) $a->nominal) <=> ((float) $b->nominal);
                if ($cmp !== 0) {
                    return $direction === 'asc' ? $cmp : -$cmp;
                }
                return strcmp($a->sort_key ?? '', $b->sort_key ?? '');
            })->values();
        } elseif ($sortBy === 'nomor_transaksi') {
            $direction = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            $transactions = $filteredTransactions->sort(function ($a, $b) use ($direction) {
                $cmp = strcasecmp($a->nomor_transaksi ?? '', $b->nomor_transaksi ?? '');
                if ($cmp !== 0) {
                    return $direction === 'asc' ? $cmp : -$cmp;
                }
                return strcmp($a->sort_key ?? '', $b->sort_key ?? '');
            })->values();
        } else {
            $transactions = $filteredTransactions->sortBy('sort_key')->values();
        }

        $tenderObj = !empty($tenderFilter) ? Tender::find($tenderFilter) : null;

        return compact(
            'transactions',
            'company',
            'branch',
            'activeEntityName',
            'startDate',
            'endDate',
            'kategoriFilter',
            'pemohonFilter',
            'kasAccount',
            'tenderObj',
            'tipeFilter',
            'saldoAwal',
            'isCabang',
            'sortBy',
            'sortOrder',
            'totalMasukPeriod',
            'totalKeluarPeriod',
            'saldoAkhirPeriod'
        );
    }

    /**
     * Cetak Laporan PDF Buku Kas Kecil (Petty Cash) Sesuai Filter
     */
    public function printPdf(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses laporan ini.');
        }

        if (!auth()->user()->can('cetak-pdf-petty-cash')) {
            abort(403, 'Akses Ditolak: Akun Anda tidak memiliki hak akses untuk mencetak PDF Kas Kecil.');
        }

        $this->authorize('cetak-pdf-petty-cash');

        $data = $this->getReportData($request);

        // Safety Guard Skala 7M+: Cegah browser freeze atau out-of-memory jika data lebih dari 2.000 transaksi
        if (isset($data['transactions']) && count($data['transactions']) > 2000) {
            $totalData = number_format(count($data['transactions']), 0, ',', '.');
            return response("<div style='font-family:system-ui,-apple-system,sans-serif; text-align:center; padding:60px 20px; max-width:600px; margin:40px auto; border-radius:16px; background:#fff; box-shadow:0 10px 25px -5px rgba(0,0,0,0.1); border:1px solid #e2e8f0;'>
                <div style='font-size:48px; color:#e11d48; margin-bottom:12px;'>⚠️</div>
                <h3 style='color:#0f172a; margin-bottom:8px;'>Data Terlalu Besar untuk PDF ({$totalData} Transaksi)</h3>
                <p style='color:#64748b; font-size:14px; line-height:1.6;'>Format cetak PDF direkomendasikan maksimal <strong>2.000 data</strong> agar tidak memberatkan browser.<br>Silakan persempit rentang tanggal atau gunakan tombol <strong>Cetak Excel</strong> untuk mengekspor data dalam jumlah besar.</p>
                <button onclick='window.close()' style='padding:10px 24px; background:#0ea5e9; color:#fff; border:none; border-radius:9999px; font-weight:600; cursor:pointer; margin-top:16px;'>Tutup Jendela</button>
            </div>", 422);
        }

        return view('modul_accounting.petty_cash.print_pdf', $data);
    }

    /**
     * Cetak / Ekspor Laporan Excel (.xlsx) Buku Kas Kecil dengan Tampilan Elegan Selaras Format PDF
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses fitur ini.');
        }

        if (!auth()->user()->can('export-excel-petty-cash')) {
            abort(403, 'Akses Ditolak: Akun Anda tidak memiliki hak akses untuk mengekspor Excel Kas Kecil.');
        }

        $this->authorize('export-excel-petty-cash');

        $data = $this->getReportData($request);
        extract($data);

        $cleanSlug = \Illuminate\Support\Str::slug($activeEntityName, '_');
        $filename  = 'Laporan_Buku_Kas_Kecil_' . ($cleanSlug ? $cleanSlug . '_' : '') . date('Ymd_His') . '.xlsx';

        // Bangun baris spreadsheet agar tampilannya elegan dan selaras dengan format PDF
        $rows = [
            // 1. Kop Surat Resmi
            ['<style bgcolor="#1E3A8A" color="#FFFFFF" font-size="14"><b>' . mb_strtoupper($activeEntityName) . '</b>'],
            ['<style color="#475569">' . ($branch->alamat ?? $company->alamat ?? '-') . '</style>'],
            [],
            // 2. Judul Laporan & Informasi Periode
            ['<style bgcolor="#1D4ED8" color="#FFFFFF" font-size="12"><b>LAPORAN BUKU KAS KECIL (PETTY CASH)</b>'],
            ['<b>Periode:</b> ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)) . ' | <b>Akun:</b> ' . ($kasAccount?->nama_akun ?: ($kasAccount?->coa?->name ?? 'Kas Kecil')) . ($pemohonFilter ? ' | <b>Pemohon:</b> ' . $this->xlsSafe($pemohonFilter) : '') . ($kategoriFilter && $kategoriFilter !== 'all' ? ' | <b>Kategori:</b> ' . $this->xlsSafe($kategoriFilter) : '') . (!empty($sortBy) && $sortBy !== 'tanggal' ? ' | <b>Urutan:</b> ' . ucfirst(str_replace('_', ' ', $sortBy)) . ' (' . strtoupper($sortOrder) . ')' : '') . ' | <b>Total:</b> ' . count($transactions) . ' Transaksi | <b>Dicetak:</b> ' . now()->translatedFormat('d F Y H:i')],
            [],
            // 3. Header Tabel (Sesuai Skema Warna Biru & Struktur PDF)
            [
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>NO.</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>TGL & JAM</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>NO. REF</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>KATEGORI</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>KETERANGAN</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>PEMOHON / PIC</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>TENDER / PROYEK</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>MASUK (DEBIT)</b></right>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>KELUAR (KREDIT)</b></right>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>SALDO</b></right>',
            ]
        ];

        $runningBalance = (float) ($saldoAwal ?? 0);
        $totalMasuk = 0;
        $totalKeluar = 0;

        $rowSaldoAwal = [
            '<center>-</center>',
            '<center>' . date('d/m/Y', strtotime($startDate)) . '</center>',
            '<center><b>SALDO AWAL</b></center>',
            '<style bgcolor="#E0F2FE" color="#0369A1"><center><b>Saldo Awal</b></center></style>',
            'Saldo Awal Kas Kecil per ' . date('d/m/Y', strtotime($startDate)),
            '-',
            '-',
            '<center>-</center>',
            '<center>-</center>',
            '<style color="#0F172A"><right><b>' . number_format($saldoAwal, 2, ',', '.') . '</b></right></style>'
        ];

        $isChronologicalAsc = strtolower($sortOrder ?? 'desc') === 'asc';

        if ($isChronologicalAsc) {
            $rows[] = $rowSaldoAwal;
        }

        foreach ($transactions as $idx => $trx) {
            $isMasuk = ($trx->tipe === 'Pemasukan');
            $masuk = $isMasuk ? (float)$trx->nominal : 0;
            $keluar = !$isMasuk ? (float)$trx->nominal : 0;
            $totalMasuk += $masuk;
            $totalKeluar += $keluar;
            $rowBalance = isset($trx->running_balance) ? $trx->running_balance : ($runningBalance = $runningBalance + $masuk - $keluar);

            $tglStr = date('d/m/Y', strtotime($trx->tanggal));
            $jamStr = !empty($trx->created_at) ? \Carbon\Carbon::parse($trx->created_at)->format('H:i') . ' WIB' : '';
            $tglJam = $jamStr ? "{$tglStr} {$jamStr}" : $tglStr;
            $tenderStr = !empty($trx->tender) ? $this->xlsSafe($trx->tender->nomor_kontrak . ' - ' . $trx->tender->nama_tender) : '-';
            $picStr = $this->xlsSafe($trx->nama_pengaju ?: 'Admin');

            // Kategori Cell Styling (Badge Warna Hijau / Merah Sesuai PDF)
            $catCell = $this->xlsSafe($trx->kategori ?: 'Operasional');
            if ($trx->kategori === 'Top Up Kas Kecil') {
                $catCell = '<style bgcolor="#DCFCE7" color="#15803D"><center><b>' . $catCell . '</b></center></style>';
            } elseif ($trx->kategori === 'Pengosongan Kas Kecil') {
                $catCell = '<style bgcolor="#FEE2E2" color="#B91C1C"><center><b>' . $catCell . '</b></center></style>';
            }

            // Nominal Cell Styling (Hijau untuk Masuk, Merah untuk Keluar)
            $cellMasuk = $masuk > 0 ? '<style color="#15803D"><right><b>' . number_format($masuk, 2, ',', '.') . '</b></right></style>' : '<center>-</center>';
            $cellKeluar = $keluar > 0 ? '<style color="#B91C1C"><right><b>' . number_format($keluar, 2, ',', '.') . '</b></right></style>' : '<center>-</center>';
            $cellSaldo = '<style color="#0F172A"><right><b>' . number_format($rowBalance, 2, ',', '.') . '</b></right></style>';

            $ketStr = $this->xlsSafe($trx->keterangan ?: '-');

            $rows[] = [
                '<center>' . ($idx + 1) . '</center>',
                '<center>' . $tglJam . '</center>',
                '<center><b>' . $trx->nomor_transaksi . '</b></center>',
                $catCell,
                $ketStr,
                $picStr,
                $tenderStr,
                $cellMasuk,
                $cellKeluar,
                $cellSaldo
            ];
        }

        if (!$isChronologicalAsc) {
            $rows[] = $rowSaldoAwal;
        }

        if (count($transactions) === 0) {
            $rows[] = [
                '', '', '', '', '<center><i>Tidak ditemukan catatan transaksi kas kecil untuk filter periode ini</i></center>', '', '', '', '', ''
            ];
        }

        // 4. Baris Footer Total (Sesuai Format PDF)
        $fmtTotMasuk = $totalMasuk > 0 ? 'Rp ' . number_format($totalMasuk, 2, ',', '.') : 'Rp 0,00';
        $fmtTotKeluar = $totalKeluar > 0 ? 'Rp ' . number_format($totalKeluar, 2, ',', '.') : 'Rp 0,00';
        $fmtTotSaldo = 'Rp ' . number_format($saldoAkhirPeriod ?? ($saldoAwal + $totalMasuk - $totalKeluar), 2, ',', '.');

        $rows[] = [
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><center><b>TOTAL</b></center></style>',
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><b>TOTAL MUTASI PERIODE INI:</b></style>',
            '', '', '', '', '',
            '<style bgcolor="#DBEAFE" color="#15803D"><right><b>' . $fmtTotMasuk . '</b></right></style>',
            '<style bgcolor="#DBEAFE" color="#B91C1C"><right><b>' . $fmtTotKeluar . '</b></right></style>',
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><right><b>' . $fmtTotSaldo . '</b></right></style>'
        ];

        // 5. Lembar Tanda Tangan / Otorisasi Resmi
        $rows[] = [];
        $rows[] = [];
        $rows[] = [
            '',
            '<center><b>Dibuat:</b></center>',
            '',
            '',
            '<center><b>Diperiksa:</b></center>',
            '',
            '',
            '',
            '<center><b>Disetujui:</b></center>',
            ''
        ];
        $rows[] = [];
        $rows[] = [];
        $rows[] = [];
        $rows[] = [
            '',
            '<center><b>( Staff Kasir )</b></center>',
            '',
            '',
            '<center><b>( Supervisor Keuangan )</b></center>',
            '',
            '',
            '',
            '<center><b>( Direktur )</b></center>',
            ''
        ];

        if (ob_get_length()) {
            ob_end_clean();
        }

        $xlsx = SimpleXLSXGen::fromArray($rows);
        $xlsx->downloadAs($filename);
        exit();
    }

    /**
     * AJAX Data Provider (Optional JSON Feed / DataTables)
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-petty-cash');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $query = PettyCash::with([
            'coaKas:id,code,name',
            'lawanCoa:id,code,name',
            'tender:id,nomor_kontrak,nama_tender',
            'creator:id,nama_lengkap,username'
        ])
        ->forActiveCompany();

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId)->whereNull('company_id')->where('tipe_entitas', 'Cabang');
        } else {
            $query->where('company_id', $activeCompanyId)->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('tender_id')) {
            $query->where('tender_id', $request->tender_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nomor_transaksi', function ($row) {
                return '<span class="fw-bold font-monospace text-body-emphasis">' . e($row->nomor_transaksi) . '</span>';
            })
            ->editColumn('tanggal', function ($row) {
                return '<span class="font-monospace text-muted small">' . date('d/m/Y', strtotime($row->tanggal)) . '</span>';
            })
            ->editColumn('nominal', function ($row) {
                return '<span class="fw-bold font-monospace text-danger">- Rp ' . number_format($row->nominal, 2, ',', '.') . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-info btn-detail-pc" data-id="' . $row->id . '" title="Lihat Detail"><i class="bi bi-eye"></i></button>
                        <button type="button" class="btn btn-outline-warning btn-edit-pc" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-delete-pc" data-id="' . $row->id . '" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>
                ';
            })
            ->rawColumns(['nomor_transaksi', 'tanggal', 'nominal', 'action'])
            ->make(true);
    }

    /**
     * Simpan Transaksi Kas Kecil Baru (Memotong / Menambah Saldo Kas Kecil & Menjurnal)
     */
    public function store(Request $request)
    {
        $this->authorize('catat-petty-cash');

        // Bersihkan formatting nominal Rupiah dengan helper parseNominal
        if ($request->has('nominal')) {
            $rawNominal = (string)$request->nominal;
            if (strlen($rawNominal) > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal pengeluaran tidak boleh lebih dari 30 karakter!'
                ], 422);
            }
            $request->merge(['nominal' => $this->parseNominal($rawNominal)]);
        }

        $request->validate([
            'tanggal'       => 'required|date',
            'tipe'          => 'nullable|string',
            'kategori'      => 'required|string|max:40',
            'coa_kas_id'    => 'required|exists:chart_of_accounts,id',
            'lawan_coa_id'  => 'required|exists:chart_of_accounts,id',
            'tender_id'     => 'nullable|exists:tenders,id',
            'nominal'       => 'required|numeric|min:1|max:999999999999',
            'nama_pengaju'  => 'required|string|max:40',
            'keterangan'    => 'nullable|string|max:50',
            'lampiran_nota' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'nama_pengaju.required'  => 'Nama Pemohon / Pengaju wajib diisi.',
            'nama_pengaju.max'       => 'Nama Pemohon maksimal 40 karakter.',
            'kategori.required'      => 'Kategori Transaksi wajib diisi.',
            'kategori.max'           => 'Kategori Transaksi maksimal 40 karakter.',
            'keterangan.max'         => 'Catatan / Keperluan maksimal 50 karakter.',
            'coa_kas_id.required'    => 'Akun Kas (COA Kas) wajib dipilih.',
            'lawan_coa_id.required'  => 'Akun Biaya / Beban (Lawan COA) wajib dipilih.',
            'nominal.min'            => 'Nominal transaksi minimal Rp 1.',
            'lampiran_nota.required' => 'Foto / lampiran bukti nota pembayaran wajib diunggah.',
            'lampiran_nota.file'     => 'Berkas bukti nota harus berupa file yang valid.',
            'lampiran_nota.mimes'    => 'Format bukti nota harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'lampiran_nota.max'      => 'Ukuran file bukti nota maksimal 10 MB.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $scopeError = $this->validateScopedReferences($request, $activeCompanyId, $activeBranchId);
        if ($scopeError) {
            return $scopeError;
        }
        $tipeEntitas     = !empty($activeBranchId) ? 'Cabang' : 'PT';
        $branchId        = !empty($activeBranchId) ? $activeBranchId : null;
        $companyId       = !empty($activeBranchId) ? null : ($activeCompanyId ?: 1);
        $nominal         = (float) $request->nominal;

        DB::beginTransaction();
        try {
            // 1. Cari Akun KasBank yang terhubung dengan COA Kas (Strict Isolation PT vs Cabang)
            $kasBankQuery = KasBank::where('coa_id', $request->coa_kas_id);
            if (!empty($activeBranchId)) {
                $kasBankQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
            } else {
                $kasBankQuery->where('company_id', $companyId)->whereNull('branch_id');
            }
            $kasBank = $kasBankQuery->lockForUpdate()->first();
            $namaKas = $kasBank ? $kasBank->nama_akun : 'Kas';

            // Hitung Saldo Sebelum & Sesudah (Kas berkurang karena pengeluaran)
            if ($kasBank) {
                $saldoSebelum = (float) $kasBank->saldo_berjalan;
            } else {
                $lastTrxQuery = PettyCash::query();
                if (!empty($activeBranchId)) {
                    $lastTrxQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
                } else {
                    $lastTrxQuery->where('company_id', $companyId)->whereNull('branch_id');
                }
                $lastTrx = $lastTrxQuery->orderBy('id', 'desc')->first();
                $saldoSebelum = $lastTrx ? (float)$lastTrx->saldo_sesudah : 0;
            }

            // Validasi: Kas tidak boleh minus dan tidak boleh keluar jika saldo 0 atau kurang
            if ($saldoSebelum <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo {$namaKas} saat ini Rp 0 (Belum ada saldo). Harap lakukan Top Up / Transfer Kas dari menu Kas & Bank terlebih dahulu sebelum mencatat pengeluaran!"
                ], 422);
            }

            if ($nominal > $saldoSebelum) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo {$namaKas} tidak mencukupi! Sisa saldo saat ini Rp " . number_format($saldoSebelum, 2, ',', '.') . ", sedangkan pengeluaran yang diinput Rp " . number_format($nominal, 2, ',', '.') . ". Harap lakukan Top Up saldo terlebih dahulu."
                ], 422);
            }

            $saldoSesudah = $saldoSebelum - $nominal;

            // Update Saldo Berjalan pada KasBank jika ada
            if ($kasBank) {
                $kasBank->saldo_berjalan = $saldoSesudah;
                $kasBank->save();
            }

            // 2. Generate Nomor Transaksi Kas Kecil Unik (Termasuk deleted agar tidak duplikat)
            $prefix = ($tipeEntitas === 'PT') ? 'PC-PT-' : 'PC-CBG-';
            $datePrefix = date('ymd', strtotime($request->tanggal));
            
            $lastRecord = PettyCash::withTrashed()
                ->where('nomor_transaksi', 'like', "{$prefix}{$datePrefix}-%")
                ->orderBy('nomor_transaksi', 'desc')
                ->first();

            $sequence = 1;
            if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->nomor_transaksi, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }
            $nomorTransaksi = $prefix . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 3. Handle Upload Bukti / Struk Nota (Otomatis Kompres ke ukuran KB)
            $lampiranPath = null;
            if ($request->hasFile('lampiran_nota')) {
                $lampiranPath = $this->handleReceiptUpload($request->file('lampiran_nota'));
            }

            // 4. Simpan Petty Cash dengan Saldo Sebelum & Sesudah
            $pettyCash = PettyCash::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal'         => $request->tanggal,
                'tipe'            => 'Pengeluaran',
                'kategori'        => $request->kategori,
                'tipe_entitas'    => $tipeEntitas,
                'company_id'      => $companyId,
                'branch_id'       => $branchId,
                'coa_kas_id'      => $request->coa_kas_id,
                'lawan_coa_id'    => $request->lawan_coa_id,
                'tender_id'       => $request->tender_id,
                'nominal'         => $nominal,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'nama_pengaju'    => $request->nama_pengaju,
                'keterangan'      => $request->keterangan,
                'lampiran_nota'   => $lampiranPath,
                'status'          => 'Disetujui',
                'created_by'      => Auth::id(),
                'updated_by'      => Auth::id(),
            ]);

            // 5. Catat Mutasi pada KasMutasi (Buku Kas & Bank)
            if ($kasBank) {
                KasMutasi::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'kas_bank_id'     => $kasBank->id,
                    'tipe'            => 'Keluar',
                    'kategori'        => $request->kategori ?: 'Pengeluaran Kas Kecil',
                    'lawan_coa_id'    => $request->lawan_coa_id,
                    'jumlah'          => $nominal,
                    'saldo_sebelum'   => $saldoSebelum,
                    'saldo_sesudah'   => $saldoSesudah,
                    'tanggal'         => $request->tanggal,
                    'nomor_referensi' => $nomorTransaksi,
                    'nama_kontak'     => $request->nama_pengaju,
                    'keterangan'      => "[Kas Kecil] " . ($request->keterangan ?: $request->kategori),
                    'status'          => 'Disetujui',
                    'company_id'      => $companyId,
                    'branch_id'       => $branchId,
                    'created_by'      => Auth::id(),
                ]);
            }

            // 6. Auto Posting Jurnal Umum
            $jurnalId = $this->createPettyCashJournal($pettyCash);
            if ($jurnalId) {
                $pettyCash->update(['jurnal_id' => $jurnalId]);
            }

            DB::commit();

            // Refresh Cache Pemohon
            Cache::forget("pc_pemohon_list_" . ($branchId ? "br_{$branchId}" : "co_{$companyId}"));
            Cache::forget("pc_pemohon_stats_" . ($branchId ? "br_{$branchId}" : "co_{$companyId}"));

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran Kas Kecil <strong>{$nomorTransaksi}</strong> sebesar Rp " . number_format($nominal, 2, ',', '.') . " berhasil dicatat. Sisa saldo kas kecil: Rp " . number_format($saldoSesudah, 2, ',', '.') . ".",
                'data'    => $pettyCash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($lampiranPath)) {
                Storage::disk('public')->delete($lampiranPath);
            }
            Log::error('Error store PettyCash: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi Kas Kecil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil Detail Transaksi untuk Modal / Bottom Sheet Mobile
     */
    public function show(PettyCash $pettyCash)
    {
        $this->authorize('detail-petty-cash');
        $this->assertOwnsRecord($pettyCash);

        $pettyCash->load([
            'coaKas:id,code,name,type',
            'lawanCoa:id,code,name,type',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,kode_cabang,nama_cabang',
            'company:id,kode_perusahaan,nama_perusahaan',
            'creator:id,nama_lengkap,username',
            'jurnal.details.account:id,code,name',
            'reversalJurnal.details.account:id,code,name'
        ]);

        return response()->json([
            'success' => true,
            'data'    => $pettyCash,
            'lampiran_url' => $pettyCash->lampiran_nota ? asset('storage/' . $pettyCash->lampiran_nota) : null,
        ]);
    }

    /**
     * Ambil Data untuk Form Edit
     */
    public function edit(PettyCash $pettyCash)
    {
        $this->authorize('catat-petty-cash');
        $this->assertOwnsRecord($pettyCash);

        return response()->json([
            'success' => true,
            'data'    => $pettyCash,
            'lampiran_url' => $pettyCash->lampiran_nota ? asset('storage/' . $pettyCash->lampiran_nota) : null,
        ]);
    }

    /**
     * Update Transaksi Kas Kecil & Sinkronisasi Jurnal serta Saldo Kas
     */
    public function update(Request $request, PettyCash $pettyCash)
    {
        $this->authorize('catat-petty-cash');
        $this->assertOwnsRecord($pettyCash);

        // Bersihkan formatting nominal Rupiah dengan helper parseNominal
        if ($request->has('nominal')) {
            $rawNominal = (string)$request->nominal;
            if (strlen($rawNominal) > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal pengeluaran tidak boleh lebih dari 30 karakter!'
                ], 422);
            }
            $request->merge(['nominal' => $this->parseNominal($rawNominal)]);
        }

        $request->validate([
            'tanggal'       => 'required|date',
            'tipe'          => 'nullable|string',
            'kategori'      => 'required|string|max:40',
            'coa_kas_id'    => 'required|exists:chart_of_accounts,id',
            'lawan_coa_id'  => 'required|exists:chart_of_accounts,id',
            'tender_id'     => 'nullable|exists:tenders,id',
            'nominal'       => 'required|numeric|min:1|max:999999999999',
            'nama_pengaju'  => 'required|string|max:40',
            'keterangan'    => 'nullable|string|max:50',
            'lampiran_nota' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'nama_pengaju.required' => 'Nama Pemohon / Pengaju wajib diisi.',
            'nama_pengaju.max'      => 'Nama Pemohon maksimal 40 karakter.',
            'kategori.required'     => 'Kategori Transaksi wajib diisi.',
            'kategori.max'          => 'Kategori Transaksi maksimal 40 karakter.',
            'keterangan.max'        => 'Catatan / Keperluan maksimal 50 karakter.',
            'coa_kas_id.required'   => 'Akun Kas Kecil (COA Kas) wajib dipilih.',
            'lawan_coa_id.required' => 'Akun Biaya / Beban (Lawan COA) wajib dipilih.',
            'nominal.min'           => 'Nominal transaksi minimal Rp 1.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $scopeError = $this->validateScopedReferences($request, $activeCompanyId, $activeBranchId);
        if ($scopeError) {
            return $scopeError;
        }

        if ($pettyCash->status === 'Dibatalkan') {
            return response()->json([
                'success' => false,
                'message' => "Transaksi <strong>{$pettyCash->nomor_transaksi}</strong> sudah berstatus Dibatalkan dan tidak dapat diedit."
            ], 422);
        }

        $oldNominal  = (float) $pettyCash->nominal;
        $newNominal  = (float) $request->nominal;
        $oldCoaKasId = $pettyCash->coa_kas_id;
        $newCoaKasId = $request->coa_kas_id;

        DB::beginTransaction();
        try {
            $pettyCash = PettyCash::whereKey($pettyCash->id)->lockForUpdate()->firstOrFail();
            if ($pettyCash->status === 'Dibatalkan') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Transaksi <strong>{$pettyCash->nomor_transaksi}</strong> sudah berstatus Dibatalkan dan tidak dapat diedit."
                ], 422);
            }

            $newKasBankRecord = null;
            $newSaldoSesudah  = null;

            if ($oldCoaKasId == $newCoaKasId) {
                // Skenario A: Akun Kas Tetap Sama -> Kembalikan nominal lama, potong nominal baru
                $kasBankQuery = KasBank::where('coa_id', $oldCoaKasId);
                if (!empty($pettyCash->branch_id)) {
                    $kasBankQuery->where('branch_id', $pettyCash->branch_id)->whereNull('company_id');
                } else {
                    $kasBankQuery->where('company_id', $pettyCash->company_id)->whereNull('branch_id');
                }
                $kasBank = $kasBankQuery->lockForUpdate()->first();

                if ($kasBank) {
                    $saldoTersedia = (float)$kasBank->saldo_berjalan + $oldNominal;
                    if ($newNominal > $saldoTersedia) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Saldo {$kasBank->nama_akun} tidak mencukupi! Saldo tersedia Rp " . number_format($saldoTersedia, 2, ',', '.') . ", sedangkan pengeluaran baru Rp " . number_format($newNominal, 2, ',', '.') . ". Harap lakukan Top Up terlebih dahulu."
                        ], 422);
                    }
                    $kasBank->saldo_berjalan = $saldoTersedia - $newNominal;
                    $kasBank->save();
                    $newSaldoSesudah = $kasBank->saldo_berjalan;
                    $newKasBankRecord = $kasBank;
                }
            } else {
                // Skenario B: Pengguna Memindahkan Transaksi ke Akun Kas Baru -> Kembalikan ke kas lama, potong kas baru
                $oldKasBankQuery = KasBank::where('coa_id', $oldCoaKasId);
                if (!empty($pettyCash->branch_id)) {
                    $oldKasBankQuery->where('branch_id', $pettyCash->branch_id)->whereNull('company_id');
                } else {
                    $oldKasBankQuery->where('company_id', $pettyCash->company_id)->whereNull('branch_id');
                }
                $oldKasBank = $oldKasBankQuery->lockForUpdate()->first();
                if ($oldKasBank) {
                    $oldKasBank->saldo_berjalan = (float)$oldKasBank->saldo_berjalan + $oldNominal;
                    $oldKasBank->save();
                }

                $newKasBankQuery = KasBank::where('coa_id', $newCoaKasId);
                if (!empty($pettyCash->branch_id)) {
                    $newKasBankQuery->where('branch_id', $pettyCash->branch_id)->whereNull('company_id');
                } else {
                    $newKasBankQuery->where('company_id', $pettyCash->company_id)->whereNull('branch_id');
                }
                $newKasBank = $newKasBankQuery->lockForUpdate()->first();
                $namaKasBaru = $newKasBank ? $newKasBank->nama_akun : 'Akun Kas Baru';

                if (!$newKasBank || (float)$newKasBank->saldo_berjalan < $newNominal) {
                    DB::rollBack();
                    $sisaKasBaru = $newKasBank ? (float)$newKasBank->saldo_berjalan : 0;
                    return response()->json([
                        'success' => false,
                        'message' => "Saldo {$namaKasBaru} tidak mencukupi! Sisa saldo saat ini Rp " . number_format($sisaKasBaru, 2, ',', '.') . ", sedangkan pengeluaran yang diinput Rp " . number_format($newNominal, 2, ',', '.') . ". Harap lakukan Top Up saldo akun tersebut terlebih dahulu."
                    ], 422);
                }

                $newKasBank->saldo_berjalan = (float)$newKasBank->saldo_berjalan - $newNominal;
                $newKasBank->save();
                $newSaldoSesudah = $newKasBank->saldo_berjalan;
                $newKasBankRecord = $newKasBank;
            }

            // Update lampiran jika ada upload baru (Otomatis Kompres ke ukuran KB)
            $lampiranPath = $pettyCash->lampiran_nota;
            if ($request->hasFile('lampiran_nota')) {
                $lampiranPath = $this->handleReceiptUpload($request->file('lampiran_nota'), $pettyCash->lampiran_nota);
            }

            $updatePayload = [
                'tanggal'       => $request->tanggal,
                'tipe'          => 'Pengeluaran',
                'kategori'      => $request->kategori,
                'coa_kas_id'    => $newCoaKasId,
                'lawan_coa_id'  => $request->lawan_coa_id,
                'tender_id'     => $request->tender_id,
                'nominal'       => $newNominal,
                'nama_pengaju'  => $request->nama_pengaju,
                'keterangan'    => $request->keterangan,
                'lampiran_nota' => $lampiranPath,
                'updated_by'    => Auth::id(),
            ];
            if ($newSaldoSesudah !== null) {
                $updatePayload['saldo_sesudah'] = $newSaldoSesudah;
            }

            $pettyCash->update($updatePayload);

            // Update Mutasi Kas jika ada
            $mutasi = KasMutasi::where('nomor_referensi', $pettyCash->nomor_transaksi)->first();
            if ($mutasi) {
                $mutasiUpdateData = [
                    'jumlah'       => $newNominal,
                    'tanggal'      => $request->tanggal,
                    'kategori'     => $request->kategori,
                    'lawan_coa_id' => $request->lawan_coa_id,
                    'nama_kontak'  => $request->nama_pengaju,
                    'keterangan'   => "[Kas Kecil] " . ($request->keterangan ?: $request->kategori),
                ];
                if ($newKasBankRecord) {
                    $mutasiUpdateData['kas_bank_id'] = $newKasBankRecord->id;
                    $mutasiUpdateData['saldo_sesudah'] = $newSaldoSesudah;
                }
                $mutasi->update($mutasiUpdateData);
            }

            // Sinkronisasi ulang Jurnal Umum
            $this->updatePettyCashJournal($pettyCash);

            DB::commit();

            // Refresh Cache Pemohon
            Cache::forget("pc_pemohon_list_" . ($pettyCash->branch_id ? "br_{$pettyCash->branch_id}" : "co_{$pettyCash->company_id}"));
            Cache::forget("pc_pemohon_stats_" . ($pettyCash->branch_id ? "br_{$pettyCash->branch_id}" : "co_{$pettyCash->company_id}"));

            return response()->json([
                'success' => true,
                'message' => "Transaksi <strong>{$pettyCash->nomor_transaksi}</strong> berhasil diperbarui.",
                'data'    => $pettyCash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update PettyCash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan Transaksi Kas Kecil (Void) + Reversal Jurnal, Pengembalian Saldo Kas & Pencatatan Alasan
     */
    public function batal(Request $request, PettyCash $pettyCash)
    {
        $this->authorize('batalkan-petty-cash');
        $this->assertOwnsRecord($pettyCash);

        if ($pettyCash->status === 'Dibatalkan') {
            return response()->json([
                'success' => false,
                'message' => "Transaksi <strong>{$pettyCash->nomor_transaksi}</strong> sudah berstatus Dibatalkan sebelumnya."
            ], 422);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:40',
        ], [
            'alasan_pembatalan.required' => 'Keterangan alasan pembatalan transaksi wajib diisi.',
            'alasan_pembatalan.max'      => 'Keterangan alasan pembatalan maksimal 40 karakter.',
        ]);

        $alasan = trim($request->input('alasan_pembatalan', ''));

        DB::beginTransaction();
        try {
            // Kunci baris & cek ulang status (cegah double-refund karena double-click / request paralel)
            $pettyCash = PettyCash::whereKey($pettyCash->id)->lockForUpdate()->firstOrFail();
            if ($pettyCash->status === 'Dibatalkan') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Transaksi <strong>{$pettyCash->nomor_transaksi}</strong> sudah berstatus Dibatalkan sebelumnya."
                ], 422);
            }

            $nominal = (float) $pettyCash->nominal;

            // 1. Kembalikan Saldo KasBank (Strict Isolation PT vs Cabang)
            if ($pettyCash->tipe === 'Pengeluaran' || $pettyCash->tipe === 'Klaim') {
                $kasBankQuery = KasBank::where('coa_id', $pettyCash->coa_kas_id);
                if (!empty($pettyCash->branch_id)) {
                    $kasBankQuery->where('branch_id', $pettyCash->branch_id)->whereNull('company_id');
                } else {
                    $kasBankQuery->where('company_id', $pettyCash->company_id)->whereNull('branch_id');
                }
                $kasBank = $kasBankQuery->lockForUpdate()->first();

                if ($kasBank) {
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan + $nominal;
                    $kasBank->save();
                }
            } elseif ($pettyCash->tipe === 'Pemasukan') {
                $kasBankQuery = KasBank::where('coa_id', $pettyCash->coa_kas_id);
                if (!empty($pettyCash->branch_id)) {
                    $kasBankQuery->where('branch_id', $pettyCash->branch_id)->whereNull('company_id');
                } else {
                    $kasBankQuery->where('company_id', $pettyCash->company_id)->whereNull('branch_id');
                }
                $kasBank = $kasBankQuery->lockForUpdate()->first();

                if ($kasBank) {
                    if ((float)$kasBank->saldo_berjalan < $nominal) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Saldo Kas saat ini tidak mencukupi untuk membatalkan penerimaan kas ini (Saldo Kas: Rp ' . number_format($kasBank->saldo_berjalan, 2, ',', '.') . ').'
                        ], 422);
                    }
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan - $nominal;
                    $kasBank->save();
                }
            }

            // 2. Tandai KasMutasi sebagai Dibatalkan (bukan hard delete agar histori mutasi kas tetap utuh & ada jejak audit)
            KasMutasi::where('nomor_referensi', $pettyCash->nomor_transaksi)->update([
                'status'            => 'Dibatalkan',
                'cancelled_by'      => Auth::id(),
                'cancelled_at'      => now(),
                'alasan_pembatalan' => $alasan,
            ]);

            // 3. Buat Jurnal Pembalik (Reversing Entry) otomatis
            if ($pettyCash->coa_kas_id && $pettyCash->lawan_coa_id) {
                // Tandai keterangan pada jurnal asli jika ada
                if ($pettyCash->jurnal_id) {
                    $origJurnal = Jurnal::find($pettyCash->jurnal_id);
                    if ($origJurnal && !str_starts_with($origJurnal->keterangan, '[DIBATALKAN]')) {
                        $origJurnal->keterangan = "[DIBATALKAN] " . $origJurnal->keterangan;
                        $origJurnal->save();
                    }
                }

                $companyId = ($pettyCash->tipe_entitas === 'PT') ? $pettyCash->company_id : null;
                $branchId  = ($pettyCash->tipe_entitas === 'Cabang') ? $pettyCash->branch_id : null;

                $branchName = 'PT Pusat';
                if ($branchId) {
                    $branch = Branch::find($branchId);
                    $branchName = $branch ? $branch->nama_cabang : 'Cabang';
                }

                $prefixJurnalRev = ($pettyCash->tipe_entitas === 'PT') ? 'JV-REV-PT-' : 'JV-REV-CBG-';
                $nomorJurnalReversal = $prefixJurnalRev . date('ymd') . '-' . str_pad($pettyCash->id, 4, '0', STR_PAD_LEFT);
                $suffix = 1;
                while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnalReversal)->exists()) {
                    $nomorJurnalReversal = $prefixJurnalRev . date('ymd') . '-' . str_pad($pettyCash->id, 4, '0', STR_PAD_LEFT) . "-{$suffix}";
                    $suffix++;
                }

                $tagEntitas = ($pettyCash->tipe_entitas === 'PT') ? '[Kas Kecil PT]' : "[Kas Kecil Cabang - {$branchName}]";
                $keteranganReversal = "[JURNAL PEMBALIK - Alasan: {$alasan}] Pembatalan {$tagEntitas} {$pettyCash->nomor_transaksi}: " . ($pettyCash->keterangan ?: $pettyCash->kategori);

                // Buat Header Jurnal Pembalik
                $reversalJurnal = Jurnal::create([
                    'nomor_jurnal'   => $nomorJurnalReversal,
                    'tanggal_jurnal' => now()->toDateString(),
                    'referensi_tipe' => 'PettyCashReversal',
                    'referensi_id'   => $pettyCash->id,
                    'company_id'     => $companyId,
                    'branch_id'      => $branchId,
                    'total_debit'    => $nominal,
                    'total_kredit'   => $nominal,
                    'keterangan'     => $keteranganReversal,
                    'created_by'     => Auth::id(),
                    'updated_by'     => Auth::id(),
                ]);

                // Buat Detail Jurnal Pembalik (Membalikkan Posisi Debit & Kredit Transaksi Asli)
                if ($pettyCash->tipe === 'Pengeluaran' || $pettyCash->tipe === 'Klaim') {
                    // Original: Debit Lawan COA (Beban), Kredit Kas Kecil
                    // Reversal: Debit Kas Kecil (Kembali Kas), Kredit Lawan COA (Pembalikan Beban)
                    JurnalDetail::create([
                        'jurnal_id'   => $reversalJurnal->id,
                        'account_id'  => $pettyCash->coa_kas_id,
                        'debit'       => $nominal,
                        'kredit'      => 0,
                        'keterangan'  => "Pembalikan Kas: Pengembalian Dana Kas Kecil ({$pettyCash->nomor_transaksi})",
                    ]);

                    JurnalDetail::create([
                        'jurnal_id'   => $reversalJurnal->id,
                        'account_id'  => $pettyCash->lawan_coa_id,
                        'debit'       => 0,
                        'kredit'      => $nominal,
                        'keterangan'  => "Pembalikan Beban: Pembatalan Pengeluaran ({$alasan})",
                    ]);
                } else {
                    // Original: Debit Kas Kecil, Kredit Lawan COA
                    // Reversal: Debit Lawan COA, Kredit Kas Kecil
                    JurnalDetail::create([
                        'jurnal_id'   => $reversalJurnal->id,
                        'account_id'  => $pettyCash->lawan_coa_id,
                        'debit'       => $nominal,
                        'kredit'      => 0,
                        'keterangan'  => "Pembalikan Sumber Dana ({$pettyCash->nomor_transaksi})",
                    ]);

                    JurnalDetail::create([
                        'jurnal_id'   => $reversalJurnal->id,
                        'account_id'  => $pettyCash->coa_kas_id,
                        'debit'       => 0,
                        'kredit'      => $nominal,
                        'keterangan'  => "Pembalikan Kas Kecil ({$alasan})",
                    ]);
                }
            }

            // 4. Update status transaksi menjadi 'Dibatalkan' dan simpan alasan pembatalan
            $pettyCash->status = 'Dibatalkan';
            $pettyCash->alasan_pembatalan = $alasan;
            $pettyCash->updated_by = Auth::id();
            $pettyCash->save();

            DB::commit();

            // Refresh Cache Pemohon
            Cache::forget("pc_pemohon_list_" . ($pettyCash->branch_id ? "br_{$pettyCash->branch_id}" : "co_{$pettyCash->company_id}"));
            Cache::forget("pc_pemohon_stats_" . ($pettyCash->branch_id ? "br_{$pettyCash->branch_id}" : "co_{$pettyCash->company_id}"));

            return response()->json([
                'success' => true,
                'message' => "Transaksi Kas Kecil <strong>{$pettyCash->nomor_transaksi}</strong> berhasil dibatalkan.<br>Saldo kas telah dikembalikan dan Jurnal Pembalik (Reversal Entry) otomatis diterbitkan.<br><small class='text-muted'>Alasan: {$alasan}</small>",
                'data'    => $pettyCash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batal PettyCash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, PettyCash $pettyCash)
    {
        return $this->batal($request, $pettyCash);
    }

    /**
     * Helper: Pastikan record milik entitas aktif (cabang / PT) — cegah IDOR lintas tenant.
     */
    protected function assertOwnsRecord(PettyCash $pettyCash): void
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        if (!empty($activeBranchId)) {
            $ok = (int) $pettyCash->branch_id === (int) $activeBranchId && empty($pettyCash->company_id);
        } else {
            $ok = (int) $pettyCash->company_id === (int) $activeCompanyId && empty($pettyCash->branch_id);
        }

        if (!$ok) {
            abort(404);
        }
    }

    /**
     * Helper: Validasi COA & Tender harus milik entitas aktif (PT Pusat / Cabang). Return JSON 422 bila gagal.
     */
    protected function validateScopedReferences(Request $request, $companyId, $activeBranchId = null)
    {
        foreach (['coa_kas_id', 'lawan_coa_id'] as $field) {
            $valid = ChartOfAccount::forActiveCompany()
                ->where('is_active', true)
                ->where('is_header', false)
                ->whereKey($request->input($field))
                ->exists();
            if (!$valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun COA yang dipilih tidak valid untuk entitas aktif.',
                ], 422);
            }
        }

        if ($request->filled('tender_id')) {
            $tenderQuery = Tender::query()->whereKey($request->input('tender_id'));
            if (!empty($activeBranchId)) {
                // Cabang Aktif -> Harus milik cabang tersebut
                $tenderQuery->where('branch_id', $activeBranchId);
            } else {
                // PT Pusat -> Harus milik PT (branch_id IS NULL)
                $tenderQuery->where('company_id', $companyId)->whereNull('branch_id');
            }

            if (!$tenderQuery->exists()) {
                $entitasLabel = !empty($activeBranchId) ? 'cabang' : 'perusahaan';
                return response()->json([
                    'success' => false,
                    'message' => "Tender yang dipilih tidak valid untuk {$entitasLabel} aktif.",
                ], 422);
            }
        }

        return null;
    }

    /**
     * Helper: Bersihkan teks user sebelum masuk Excel (cegah injeksi tag formula/hyperlink SimpleXLSXGen & CWE-1236 CSV Injection).
     */
    protected function xlsSafe($value): string
    {
        $clean = str_replace(['<', '>'], '', strip_tags((string) $value));
        $trimmed = trim($clean);
        // Cegah CSV / Excel Formula Injection (CWE-1236): karakter diawali =, +, -, @, \t, \r
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $clean;
        }
        return $clean;
    }

    /**
     * Helper: Parsing nominal angka dengan toleransi format Rupiah (titik ribuan, koma desimal, prefix Rp, float standar).
     */
    protected function parseNominal($raw): float
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 0.0;
        }

        // Buang prefix seperti 'Rp', 'rp', spasi
        $raw = preg_replace('/^[rR][pP]\.?\s*/', '', $raw);
        $raw = trim($raw);

        // Jika ada koma dan titik (contoh: 1.500.000,50 atau 1,500,000.50)
        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            $lastDot = strrpos($raw, '.');
            $lastComma = strrpos($raw, ',');
            if ($lastComma > $lastDot) {
                // Format Indonesia: 1.500.000,50 -> buang titik, ganti koma jadi titik
                $cleaned = str_replace('.', '', $raw);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // Format US: 1,500,000.50 -> buang koma
                $cleaned = str_replace(',', '', $raw);
            }
            return (float) $cleaned;
        }

        // Jika hanya mengandung koma (contoh: 1500000,50 atau 1,500,000)
        if (str_contains($raw, ',')) {
            if (substr_count($raw, ',') > 1) {
                return (float) str_replace(',', '', $raw);
            }
            $parts = explode(',', $raw);
            if (isset($parts[1]) && strlen($parts[1]) === 3) {
                // Pemisah ribuan (1,500,000)
                return (float) str_replace(',', '', $raw);
            }
            // Desimal Indonesia (1500,50)
            return (float) str_replace(',', '.', $raw);
        }

        // Jika hanya mengandung titik (contoh: 1.500.000 atau 150.000 atau 1500.50)
        if (str_contains($raw, '.')) {
            if (substr_count($raw, '.') > 1) {
                // Lebih dari 1 titik pasti pemisah ribuan (1.500.000)
                return (float) str_replace('.', '', $raw);
            }
            $parts = explode('.', $raw);
            if (isset($parts[1]) && strlen($parts[1]) === 3) {
                // Pemisah ribuan Indonesia: 150.000 -> 150000
                return (float) str_replace('.', '', $raw);
            }
            // Desimal float standar: 1500.50 atau 25.5
            return (float) $raw;
        }

        return (float) preg_replace('/[^0-9]/', '', $raw);
    }

    /**
     * Helper: Buat Jurnal Akuntansi Berpasangan
     */
    protected function createPettyCashJournal(PettyCash $pettyCash): ?int
    {
        try {
            $companyId = ($pettyCash->tipe_entitas === 'PT') ? $pettyCash->company_id : null;
            $branchId  = ($pettyCash->tipe_entitas === 'Cabang') ? $pettyCash->branch_id : null;

            $branchName = 'PT Pusat';
            if ($branchId) {
                $branch = Branch::find($branchId);
                $branchName = $branch ? $branch->nama_cabang : 'Cabang';
            }

            $prefixJurnal = ($pettyCash->tipe_entitas === 'PT') ? 'JV-PC-PT-' : 'JV-PC-CBG-';
            $nomorJurnal = $prefixJurnal . date('ymd', strtotime($pettyCash->tanggal)) . '-' . str_pad($pettyCash->id, 4, '0', STR_PAD_LEFT);

            $tagEntitas = ($pettyCash->tipe_entitas === 'PT') ? '[Kas Kecil PT]' : "[Kas Kecil Cabang - {$branchName}]";
            $tenderTag  = $pettyCash->tender ? ' (Tender: ' . $pettyCash->tender->nomor_kontrak . ')' : '';
            $keterangan = "{$tagEntitas} {$pettyCash->nomor_transaksi} - {$pettyCash->kategori}{$tenderTag}: " . ($pettyCash->keterangan ?: 'Kas Kecil');

            $jurnal = Jurnal::create([
                'nomor_jurnal'   => $nomorJurnal,
                'tanggal_jurnal' => $pettyCash->tanggal,
                'referensi_tipe' => 'PettyCash',
                'referensi_id'   => $pettyCash->id,
                'company_id'     => $companyId,
                'branch_id'      => $branchId,
                'total_debit'    => $pettyCash->nominal,
                'total_kredit'   => $pettyCash->nominal,
                'keterangan'     => $keterangan,
                'created_by'     => Auth::id(),
                'updated_by'     => Auth::id(),
            ]);

            // Setup Baris Debit & Kredit
            if ($pettyCash->tipe === 'Pengeluaran' || $pettyCash->tipe === 'Klaim') {
                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->lawan_coa_id,
                    'debit'       => $pettyCash->nominal,
                    'kredit'      => 0,
                    'keterangan'  => "Beban/Biaya: " . ($pettyCash->keterangan ?: $pettyCash->kategori),
                ]);

                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->coa_kas_id,
                    'debit'       => 0,
                    'kredit'      => $pettyCash->nominal,
                    'keterangan'  => "Pengeluaran Kas Kecil {$tagEntitas}",
                ]);
            } else {
                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->coa_kas_id,
                    'debit'       => $pettyCash->nominal,
                    'kredit'      => 0,
                    'keterangan'  => "Penerimaan / Pengisian Kas Kecil {$tagEntitas}",
                ]);

                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->lawan_coa_id,
                    'debit'       => 0,
                    'kredit'      => $pettyCash->nominal,
                    'keterangan'  => "Sumber Dana Pengisian: " . ($pettyCash->keterangan ?: 'Kas Besar / Bank'),
                ]);
            }

            return $jurnal->id;

        } catch (\Exception $e) {
            Log::error('Gagal membuat Jurnal Petty Cash: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper: Update Jurnal Akuntansi saat Transaksi Kas Kecil diubah
     */
    protected function updatePettyCashJournal(PettyCash $pettyCash): void
    {
        try {
            $companyId = ($pettyCash->tipe_entitas === 'PT') ? $pettyCash->company_id : null;
            $branchId  = ($pettyCash->tipe_entitas === 'Cabang') ? $pettyCash->branch_id : null;

            $branchName = 'PT Pusat';
            if ($branchId) {
                $branch = Branch::find($branchId);
                $branchName = $branch ? $branch->nama_cabang : 'Cabang';
            }

            $tagEntitas = ($pettyCash->tipe_entitas === 'PT') ? '[Kas Kecil PT]' : "[Kas Kecil Cabang - {$branchName}]";
            $tenderTag  = $pettyCash->tender ? ' (Tender: ' . $pettyCash->tender->nomor_kontrak . ')' : '';
            $keterangan = "{$tagEntitas} {$pettyCash->nomor_transaksi} - {$pettyCash->kategori}{$tenderTag}: " . ($pettyCash->keterangan ?: 'Kas Kecil');

            $jurnal = Jurnal::find($pettyCash->jurnal_id);
            if (!$jurnal) {
                $jurnalId = $this->createPettyCashJournal($pettyCash);
                if ($jurnalId) {
                    $pettyCash->update(['jurnal_id' => $jurnalId]);
                }
                return;
            }

            $jurnal->update([
                'tanggal_jurnal' => $pettyCash->tanggal,
                'company_id'     => $companyId,
                'branch_id'      => $branchId,
                'total_debit'    => $pettyCash->nominal,
                'total_kredit'   => $pettyCash->nominal,
                'keterangan'     => $keterangan,
                'updated_by'     => Auth::id(),
            ]);

            JurnalDetail::where('jurnal_id', $jurnal->id)->delete();

            if ($pettyCash->tipe === 'Pengeluaran' || $pettyCash->tipe === 'Klaim') {
                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->lawan_coa_id,
                    'debit'       => $pettyCash->nominal,
                    'kredit'      => 0,
                    'keterangan'  => "Beban/Biaya: " . ($pettyCash->keterangan ?: $pettyCash->kategori),
                ]);

                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->coa_kas_id,
                    'debit'       => 0,
                    'kredit'      => $pettyCash->nominal,
                    'keterangan'  => "Pengeluaran Kas Kecil {$tagEntitas}",
                ]);
            } else {
                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->coa_kas_id,
                    'debit'       => $pettyCash->nominal,
                    'kredit'      => 0,
                    'keterangan'  => "Penerimaan / Pengisian Kas Kecil {$tagEntitas}",
                ]);

                JurnalDetail::create([
                    'jurnal_id'   => $jurnal->id,
                    'account_id'  => $pettyCash->lawan_coa_id,
                    'debit'       => 0,
                    'kredit'      => $pettyCash->nominal,
                    'keterangan'  => "Sumber Dana Pengisian: " . ($pettyCash->keterangan ?: 'Kas Besar / Bank'),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Gagal update Jurnal Petty Cash: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Kompresi Gambar Bukti / Struk Nota Kas Kecil ke Ukuran KB (WebP/JPEG)
     * Mengurangi ukuran foto kamera HP/struk (misal 3-10 MB) menjadi berukuran KB (~80-250 KB)
     * dengan tetap menjaga teks struk tajam dan terbaca jelas.
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
            $filename = 'nota_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
            return $file->storeAs('petty_cash_receipts', $filename, 'public');
        }

        $directory = 'petty_cash_receipts';
        $fullDirPath = storage_path('app/public/' . $directory);

        if (!file_exists($fullDirPath)) {
            @mkdir($fullDirPath, 0755, true);
        }

        $filename = 'nota_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.webp';
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
                throw new \InvalidArgumentException('Tipe gambar tidak didukung untuk kompresi nota.');
        }

        if (!$srcImage) {
            throw new \InvalidArgumentException('Gagal memproses gambar bukti nota.');
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
                // Abaikan jika EXIF tidak dapat dibaca
            }
        }

        $origWidth  = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Batas resolusi maksimal 1280px (proporsional) - sangat tajam untuk huruf nota kecil dan hemat ukuran
        $maxWidth  = 1280;
        $maxHeight = 1280;

        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $newWidth  = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);
        } else {
            $newWidth  = $origWidth;
            $newHeight = $origHeight;
        }

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Pertahankan transparansi bila gambar memiliki alpha channel
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Kompresi ke format WebP kualitas 78% (mengubah file kamera 3-10 MB menjadi ~80-250 KB)
        if (function_exists('imagewebp')) {
            imagewebp($dstImage, $targetPath, 78);
        } else {
            $filename = str_replace('.webp', '.jpg', $filename);
            $targetPath = $fullDirPath . '/' . $filename;
            $relativePath = $directory . '/' . $filename;
            imagejpeg($dstImage, $targetPath, 80);
        }

        @imagedestroy($srcImage);
        @imagedestroy($dstImage);

        return $relativePath;
    }
}

