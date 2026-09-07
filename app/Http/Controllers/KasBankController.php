<?php

namespace App\Http\Controllers;

use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class KasBankController extends Controller
{
    /**
     * Tampilan Dashboard Utama Kas & Bank
     */
    public function index()
    {
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
            'branches'
        ));
    }

    /**
     * Halaman Buku Kas & Rekening Koran (Bank Statements & Ledger)
     */
    public function ledger(Request $request, $accountId = null)
    {
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

        // Hitung Ringkasan untuk Akun yang Dipilih (atau Semua)
        $summaryQuery = KasMutasi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Disetujui')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($selectedAccount) {
            $summaryQuery->where('kas_bank_id', $selectedAccount->id);
        }

        $totalMasuk  = (clone $summaryQuery)->where('tipe', 'Masuk')->sum('jumlah');
        // Total keluar mencakup tipe Keluar dan tipe Transfer (pengeluaran dari rekening asal)
        $totalKeluar = (clone $summaryQuery)->whereIn('tipe', ['Keluar', 'Transfer'])->sum('jumlah');
        $netMutasi   = $totalMasuk - $totalKeluar;

        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        return view('modul_accounting.kas&bank.ledger', compact(
            'accounts',
            'coaAccounts',
            'selectedAccount',
            'startDate',
            'endDate',
            'totalMasuk',
            'totalKeluar',
            'netMutasi',
            'activeBranch',
            'company',
            'branches'
        ));
    }

    /**
     * Cetak Rekening Koran / Buku Kas (Printable View)
     */
    public function printLedger(Request $request, $accountId = null)
    {
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
        } elseif ($request->filled('kas_bank_id') || $request->filled('account_id')) {
            $targetId = $request->kas_bank_id ?: $request->account_id;
            $selectedAccount = KasBank::with(['coa', 'branch'])
                ->forActiveCompany()
                ->forActiveBranch()
                ->find($targetId);
        }

        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?: now()->toDateString();

        $query = KasMutasi::with(['kasBank.coa', 'transferToKasBank.coa', 'lawanCoa', 'creator', 'branch'])
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

        $mutasis = $query->get();

        $totalMasuk  = $mutasis->where('tipe', 'Masuk')->where('status', 'Disetujui')->sum('jumlah');
        $totalKeluar = $mutasis->whereIn('tipe', ['Keluar', 'Transfer'])->where('status', 'Disetujui')->sum('jumlah');

        return view('modul_accounting.kas&bank.print_ledger', compact(
            'selectedAccount',
            'startDate',
            'endDate',
            'mutasis',
            'totalMasuk',
            'totalKeluar',
            'company',
            'activeBranch'
        ));
    }

    /**
     * Endpoint DataTables AJAX untuk Riwayat Mutasi Kas & Bank
     */
    public function getData(Request $request)
    {
        $query = KasMutasi::with(['kasBank.coa', 'kasBank.branch', 'transferToKasBank.coa', 'lawanCoa', 'creator:id,nama_lengkap,username', 'branch:id,nama_cabang,kode_cabang'])
            ->forActiveCompany()
            ->forActiveBranch();

        // Filter Rekening
        if ($request->filled('kas_bank_id')) {
            $query->where('kas_bank_id', $request->kas_bank_id);
        }

        // Filter Tipe Transaksi
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nomor_transaksi', function ($m) {
                $icon = $m->tipe === 'Masuk' ? 'bi-arrow-down-left text-success' : ($m->tipe === 'Keluar' ? 'bi-arrow-up-right text-danger' : 'bi-arrow-left-right text-info');
                return '<div class="d-flex align-items-center gap-2">
                            <span class="p-1 rounded-2 bg-body-tertiary border d-inline-flex"><i class="bi ' . $icon . '"></i></span>
                            <div>
                                <span class="fw-bold font-monospace text-body-emphasis">' . e($m->nomor_transaksi) . '</span>
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
                return '<span class="text-body-emphasis fw-semibold">' . e($m->nama_kontak ?: '-') . '</span>' . ($m->nomor_referensi ? '<div class="text-muted small font-monospace">Ref: ' . e($m->nomor_referensi) . '</div>' : '') . $lawanText;
            })
            ->editColumn('jumlah', function ($m) {
                if ($m->tipe === 'Masuk') {
                    return '<span class="fw-bold text-success font-monospace">+ Rp ' . number_format($m->jumlah, 0, ',', '.') . '</span>';
                } elseif ($m->tipe === 'Keluar') {
                    return '<span class="fw-bold text-danger font-monospace">- Rp ' . number_format($m->jumlah, 0, ',', '.') . '</span>';
                } else {
                    return '<span class="fw-bold text-danger font-monospace">- Rp ' . number_format($m->jumlah, 0, ',', '.') . ' <span class="badge bg-info-subtle text-info font-monospace" style="font-size:0.68rem;">TRF</span></span>';
                }
            })
            ->editColumn('saldo_sesudah', function ($m) {
                return '<span class="font-monospace fw-semibold text-body small">Rp ' . number_format($m->saldo_sesudah, 0, ',', '.') . '</span>';
            })
            ->editColumn('status', function ($m) {
                if ($m->status === 'Disetujui') {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">Disetujui</span>';
                }
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2.5 py-1">Dibatalkan</span>';
            })
            ->addColumn('action', function ($m) {
                $btns = '<div class="d-flex align-items-center justify-content-center gap-1.5">';
                $btns .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 btn-detail-mutasi" data-id="' . $m->id . '" title="Rincian Bukti Transaksi">
                            <i class="bi bi-eye"></i>
                          </button>';
                if ($m->status === 'Disetujui') {
                    $btns .= '<button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1 btn-cancel-mutasi" data-id="' . $m->id . '" data-nomor="' . e($m->nomor_transaksi) . '" title="Batalkan Transaksi">
                                <i class="bi bi-x-circle"></i>
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
        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('saldo_awal')) {
            $request->merge(['saldo_awal' => $this->cleanNominal($request->saldo_awal)]);
        }

        $request->validate([
            'nama_akun'      => 'required|string|max:100',
            'tipe'           => 'required|in:Kas Tunai,Bank,E-Wallet / Giro',
            'coa_id'         => 'required|exists:chart_of_accounts,id',
            'saldo_awal'     => 'nullable|numeric|min:0',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama'      => 'nullable|string|max:100',
            'nama_bank'      => 'nullable|string|max:100',
            'branch_id'      => 'nullable|exists:branches,id',
        ], [
            'coa_id.required' => 'Akun COA wajib dipilih untuk integrasi pembukuan akuntansi.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        // Otomatis sesuai sesi entitas yang sedang aktif (NULL jika di PT Pusat, ID jika di Cabang)
        $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : null;

        $saldoAwal = (float) ($request->saldo_awal ?: 0);

        DB::beginTransaction();
        try {
            // Jika coa_id kosong, cari akun COA default berdasarkan tipe
            $coaId = $request->coa_id;
            if (!$coaId) {
                $defaultCoa = ChartOfAccount::forActiveCompany()
                    ->where('name', 'LIKE', $request->tipe === 'Kas Tunai' ? '%Kas%' : '%Bank%')
                    ->where('is_header', false)
                    ->first();
                $coaId = $defaultCoa?->id;
            }

            // Generate Kode Akun jika kosong
            $prefixCode = 'KB-';
            $lastAccount = DB::table('kas_banks')->where('kode_akun', 'LIKE', $prefixCode . '%')->orderByRaw('CAST(SUBSTRING_INDEX(kode_akun, "-", -1) AS UNSIGNED) DESC')->first();
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
                'company_id'     => $activeCompanyId,
                'branch_id'      => $targetBranchId,
                'created_by'     => auth()->id(),
            ]);

            // Jika ada saldo awal > 0, catat mutasi saldo awal dan auto journal
            if ($saldoAwal > 0) {
                $prefixSA = 'SA-' . date('ym') . '-';
                $lastSA = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefixSA . '%')->orderByRaw('CAST(SUBSTRING_INDEX(nomor_transaksi, "-", -1) AS UNSIGNED) DESC')->first();
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
                
                // Cari akun Ekuitas / Modal untuk lawan saldo awal
                $modalCoa = ChartOfAccount::forActiveCompany()
                    ->where(function($q) {
                        $q->where('name', 'LIKE', '%Modal%')->orWhere('name', 'LIKE', '%Ekuitas%')->orWhere('code', 'LIKE', '3%');
                    })
                    ->where('is_header', false)
                    ->first();

                $jurnalId = $this->postJurnal(
                    $nomorTransaksi,
                    now()->toDateString(),
                    'Saldo Awal Kas: ' . $account->nama_akun,
                    $account->coa_id,
                    $modalCoa?->id,
                    $saldoAwal,
                    $targetBranchId
                );

                KasMutasi::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'kas_bank_id'     => $account->id,
                    'tipe'            => 'Masuk',
                    'kategori'        => 'Saldo Awal Rekening',
                    'lawan_coa_id'    => $modalCoa?->id,
                    'jumlah'          => $saldoAwal,
                    'saldo_sebelum'   => 0,
                    'saldo_sesudah'   => $saldoAwal,
                    'tanggal'         => now()->toDateString(),
                    'keterangan'      => 'Pencatatan saldo awal saat pembuatan akun rekening.',
                    'status'          => 'Disetujui',
                    'jurnal_id'       => $jurnalId,
                    'company_id'      => $activeCompanyId,
                    'branch_id'       => $targetBranchId,
                    'created_by'      => auth()->id(),
                ]);
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
     * Ambil data Akun Rekening Kas & Bank untuk form Edit (JSON)
     */
    public function editAccount(KasBank $account)
    {
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        if ($account->company_id && $account->company_id != $activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening bukan milik perusahaan aktif.'], 403);
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
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        if ($account->company_id && $account->company_id != $activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Rekening bukan milik perusahaan aktif.'], 403);
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

        try {
            $activeBranchId = session('active_branch_id');
            $targetBranchId = !empty($activeBranchId) ? $activeBranchId : ($request->has('branch_id') ? $request->branch_id : $account->branch_id);

            $account->update([
                'nama_akun'      => $request->nama_akun,
                'tipe'           => $request->tipe,
                'coa_id'         => $request->coa_id,
                'kode_akun'      => $request->kode_akun ?: $account->kode_akun,
                'nama_bank'      => $request->nama_bank,
                'nomor_rekening' => $request->nomor_rekening,
                'atas_nama'      => $request->atas_nama,
                'deskripsi'      => $request->deskripsi,
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
        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('jumlah')) {
            $request->merge(['jumlah' => $this->cleanNominal($request->jumlah)]);
        }

        $request->validate([
            'kas_bank_id'  => 'required|exists:kas_banks,id',
            'lawan_coa_id' => 'required|exists:chart_of_accounts,id',
            'tanggal'      => 'required|date',
            'kategori'     => 'required|string|max:100',
            'jumlah'       => 'required|numeric|min:1',
            'nama_kontak'  => 'nullable|string|max:150',
            'keterangan'   => 'nullable|string',
        ], [
            'lawan_coa_id.required' => 'Akun Lawan COA (Kredit) wajib dipilih untuk integrasi jurnal.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $account = KasBank::lockForUpdate()->find($request->kas_bank_id);
            $saldoSebelum = (float) $account->saldo_berjalan;
            $jumlah = (float) $request->jumlah;
            $saldoSesudah = $saldoSebelum + $jumlah;

            $branchId = $account->branch_id;

            $prefix = 'KM-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderByRaw('CAST(SUBSTRING_INDEX(nomor_transaksi, "-", -1) AS UNSIGNED) DESC')->first();
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
            $lawanCoaId = $request->lawan_coa_id;
            if (!$lawanCoaId) {
                $defaultLawan = ChartOfAccount::forActiveCompany()
                    ->where(function($q) {
                        $q->where('code', 'LIKE', '4%')->orWhere('name', 'LIKE', '%Pendapatan%')->orWhere('name', 'LIKE', '%Penjualan%');
                    })
                    ->where('is_header', false)
                    ->first();
                $lawanCoaId = $defaultLawan?->id;
            }

            $jurnalId = $this->postJurnal(
                $nomorTransaksi,
                $request->tanggal,
                "Kas Masuk [{$request->kategori}]: " . ($request->keterangan ?: "Diterima dari {$request->nama_kontak}"),
                $account->coa_id,
                $lawanCoaId,
                $jumlah,
                $branchId
            );

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
                'nama_kontak'     => $request->nama_kontak,
                'keterangan'      => $request->keterangan,
                'status'          => 'Disetujui',
                'jurnal_id'       => $jurnalId,
                'company_id'      => $activeCompanyId,
                'branch_id'       => $branchId,
                'created_by'      => auth()->id(),
            ]);

            $account->saldo_berjalan = $saldoSesudah;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Penerimaan kas sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil dicatat dan diposting ke Jurnal Akuntansi ({$nomorTransaksi}).",
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
        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('jumlah')) {
            $request->merge(['jumlah' => $this->cleanNominal($request->jumlah)]);
        }

        $request->validate([
            'kas_bank_id'  => 'required|exists:kas_banks,id',
            'lawan_coa_id' => 'required|exists:chart_of_accounts,id',
            'tanggal'      => 'required|date',
            'kategori'     => 'required|string|max:100',
            'jumlah'       => 'required|numeric|min:1',
            'nama_kontak'  => 'nullable|string|max:150',
            'keterangan'   => 'nullable|string',
        ], [
            'lawan_coa_id.required' => 'Akun Lawan COA (Debit Beban/Hutang) wajib dipilih untuk integrasi jurnal.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $account = KasBank::lockForUpdate()->find($request->kas_bank_id);
            $saldoSebelum = (float) $account->saldo_berjalan;
            $jumlah = (float) $request->jumlah;

            // Validasi kecukupan saldo kas/bank
            if ($jumlah > $saldoSebelum) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo kas \"{$account->nama_akun}\" tidak mencukupi! Saldo saat ini: Rp " . number_format($saldoSebelum, 0, ',', '.') . ", pengeluaran yang diminta: Rp " . number_format($jumlah, 0, ',', '.') . ".",
                ], 422);
            }

            $saldoSesudah = $saldoSebelum - $jumlah;
            $branchId = $account->branch_id;

            $prefix = 'KK-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderByRaw('CAST(SUBSTRING_INDEX(nomor_transaksi, "-", -1) AS UNSIGNED) DESC')->first();
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
            $lawanCoaId = $request->lawan_coa_id;
            if (!$lawanCoaId) {
                $defaultBeban = ChartOfAccount::forActiveCompany()
                    ->where(function($q) {
                        $q->where('code', 'LIKE', '6%')->orWhere('code', 'LIKE', '5%')->orWhere('name', 'LIKE', '%Beban%')->orWhere('name', 'LIKE', '%Biaya%');
                    })
                    ->where('is_header', false)
                    ->first();
                $lawanCoaId = $defaultBeban?->id;
            }

            $jurnalId = $this->postJurnal(
                $nomorTransaksi,
                $request->tanggal,
                "Kas Keluar [{$request->kategori}]: " . ($request->keterangan ?: "Dibayarkan ke {$request->nama_kontak}"),
                $lawanCoaId,
                $account->coa_id,
                $jumlah,
                $branchId
            );

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
                'nama_kontak'     => $request->nama_kontak,
                'keterangan'      => $request->keterangan,
                'status'          => 'Disetujui',
                'jurnal_id'       => $jurnalId,
                'company_id'      => $activeCompanyId,
                'branch_id'       => $branchId,
                'created_by'      => auth()->id(),
            ]);

            $account->saldo_berjalan = $saldoSesudah;
            $account->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran kas sebesar Rp " . number_format($jumlah, 0, ',', '.') . " berhasil dicatat dan diposting ke Jurnal Akuntansi ({$nomorTransaksi}).",
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
        // Bersihkan format currency Rupiah (titik pemisah ribuan)
        if ($request->has('jumlah')) {
            $request->merge(['jumlah' => $this->cleanNominal($request->jumlah)]);
        }

        $request->validate([
            'from_kas_bank_id' => 'required|exists:kas_banks,id|different:to_kas_bank_id',
            'to_kas_bank_id'   => 'required|exists:kas_banks,id',
            'tanggal'          => 'required|date',
            'jumlah'           => 'required|numeric|min:1',
            'keterangan'       => 'nullable|string',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        DB::beginTransaction();
        try {
            $fromAcc = KasBank::lockForUpdate()->find($request->from_kas_bank_id);
            $toAcc   = KasBank::lockForUpdate()->find($request->to_kas_bank_id);

            $jumlah = (float) $request->jumlah;

            if ($jumlah > (float) $fromAcc->saldo_berjalan) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Saldo pada rekening asal \"{$fromAcc->nama_akun}\" tidak mencukupi untuk transfer! Saldo tersedia: Rp " . number_format($fromAcc->saldo_berjalan, 0, ',', '.') . ".",
                ], 422);
            }

            $prefix = 'TR-' . date('ym', strtotime($request->tanggal)) . '-';
            $lastMutasi = DB::table('kas_mutasis')->where('nomor_transaksi', 'LIKE', $prefix . '%')->orderByRaw('CAST(SUBSTRING_INDEX(nomor_transaksi, "-", -1) AS UNSIGNED) DESC')->first();
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

            // Mutasi Keluar pada Akun Asal
            $fromSaldoSebelum = (float) $fromAcc->saldo_berjalan;
            $fromSaldoSesudah = $fromSaldoSebelum - $jumlah;
            $fromAcc->saldo_berjalan = $fromSaldoSesudah;
            $fromAcc->save();

            KasMutasi::create([
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
                'company_id'             => $activeCompanyId,
                'branch_id'              => $fromAcc->branch_id,
                'created_by'             => auth()->id(),
            ]);

            // Mutasi Masuk pada Akun Tujuan
            $toSaldoSebelum = (float) $toAcc->saldo_berjalan;
            $toSaldoSesudah = $toSaldoSebelum + $jumlah;
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
                'company_id'             => $activeCompanyId,
                'branch_id'              => $toAcc->branch_id,
                'created_by'             => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transfer saldo sebesar Rp " . number_format($jumlah, 0, ',', '.') . " dari {$fromAcc->nama_akun} ke {$toAcc->nama_akun} berhasil diproses dan dijurnal.",
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
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        if ($mutasi->company_id && $mutasi->company_id != $activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Bukti mutasi bukan milik perusahaan aktif.'], 403);
        }

        $mutasi->load([
            'kasBank.coa', 
            'kasBank.branch', 
            'transferToKasBank.coa', 
            'lawanCoa', 
            'jurnal.details.account', 
            'company', 
            'branch', 
            'creator:id,nama_lengkap,username',
            'canceler:id,nama_lengkap,username'
        ]);

        return response()->json([
            'success' => true,
            'mutasi'  => $mutasi,
        ]);
    }

    /**
     * Batalkan Transaksi Kas & Rollback Saldo serta Posting Jurnal Pembalik (Atomic Reversal)
     */
    public function cancel(Request $request, KasMutasi $mutasi)
    {
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        if ($mutasi->company_id && $mutasi->company_id != $activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Mutasi bukan milik perusahaan aktif.'], 403);
        }

        if ($mutasi->status === 'Dibatalkan') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah berstatus Dibatalkan.'], 400);
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
            if ($mutasi->tipe === 'Transfer' || str_ends_with($mutasi->nomor_transaksi, '-IN') && $mutasi->kategori === 'Transfer Antar Rekening') {
                $baseNo = str_replace('-IN', '', $mutasi->nomor_transaksi);
                $outMutasi = KasMutasi::where('nomor_transaksi', $baseNo)->first();
                $inMutasi  = KasMutasi::where('nomor_transaksi', $baseNo . '-IN')->first();

                if ($outMutasi && $inMutasi) {
                    $fromAcc = KasBank::lockForUpdate()->find($outMutasi->kas_bank_id);
                    $toAcc   = KasBank::lockForUpdate()->find($inMutasi->kas_bank_id);

                    // Cek apakah saldo rekening tujuan cukup untuk ditarik kembali
                    if ($jumlah > (float) $toAcc->saldo_berjalan) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Saldo rekening tujuan \"{$toAcc->nama_akun}\" tidak mencukupi untuk membatalkan transfer ini (Saldo: Rp " . number_format($toAcc->saldo_berjalan, 0, ',', '.') . ").",
                        ], 422);
                    }

                    // Revert Saldo: Kembalikan ke pengirim, kurangi dari penerima
                    $fromAcc->saldo_berjalan += $jumlah;
                    $fromAcc->save();

                    $toAcc->saldo_berjalan -= $jumlah;
                    $toAcc->save();

                    // Set Status Dibatalkan untuk kedua record mutasi
                    $outMutasi->update($cancelData);
                    $inMutasi->update($cancelData);

                    // Buat Jurnal Pembalik (Reversing Entry) jika kedua akun punya COA
                    if ($fromAcc->coa_id && $toAcc->coa_id) {
                        // Original Transfer: Debit ToAccount (Penerima), Kredit FromAccount (Pengirim)
                        // Reversal Transfer: Debit FromAccount (Pengirim), Kredit ToAccount (Penerima)
                        $this->postJurnal(
                            $baseNo,
                            $today,
                            "[REVERSAL: {$alasan}] Pembatalan Transfer Saldo {$baseNo}: {$fromAcc->nama_akun} -> {$toAcc->nama_akun}",
                            $fromAcc->coa_id, // Debit (kembalikan ke pengirim)
                            $toAcc->coa_id,   // Kredit (tarik dari penerima)
                            $jumlah,
                            $outMutasi->branch_id,
                            'Kas Mutasi Reversal'
                        );
                    }
                } else {
                    // Fallback jika salah satu record tidak ditemukan
                    $account = KasBank::lockForUpdate()->find($mutasi->kas_bank_id);
                    if ($mutasi->tipe === 'Transfer') {
                        $account->saldo_berjalan += $jumlah;
                    } else {
                        if ($jumlah > (float) $account->saldo_berjalan) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Saldo saat ini tidak mencukupi untuk membatalkan transaksi.'], 422);
                        }
                        $account->saldo_berjalan -= $jumlah;
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
                $account->saldo_berjalan -= $jumlah;
                $account->save();
                $mutasi->update($cancelData);

                // Buat Jurnal Pembalik (Reversing Entry)
                if ($account->coa_id && $mutasi->lawan_coa_id) {
                    // Original Masuk: Debit Kas/Bank, Kredit Lawan COA
                    // Reversal Masuk: Debit Lawan COA, Kredit Kas/Bank
                    $this->postJurnal(
                        $mutasi->nomor_transaksi,
                        $today,
                        "[REVERSAL: {$alasan}] Pembatalan Kas Masuk {$mutasi->nomor_transaksi}: " . ($mutasi->keterangan ?: $mutasi->kategori),
                        $mutasi->lawan_coa_id, // Debit
                        $account->coa_id,      // Kredit
                        $jumlah,
                        $mutasi->branch_id,
                        'Kas Mutasi Reversal'
                    );
                }

            // Kasus 3: Kas Keluar Biasa
            } elseif ($mutasi->tipe === 'Keluar') {
                $account = KasBank::lockForUpdate()->find($mutasi->kas_bank_id);
                $account->saldo_berjalan += $jumlah;
                $account->save();
                $mutasi->update($cancelData);

                // Buat Jurnal Pembalik (Reversing Entry)
                if ($account->coa_id && $mutasi->lawan_coa_id) {
                    // Original Keluar: Debit Lawan COA, Kredit Kas/Bank
                    // Reversal Keluar: Debit Kas/Bank, Kredit Lawan COA
                    $this->postJurnal(
                        $mutasi->nomor_transaksi,
                        $today,
                        "[REVERSAL: {$alasan}] Pembatalan Kas Keluar {$mutasi->nomor_transaksi}: " . ($mutasi->keterangan ?: $mutasi->kategori),
                        $account->coa_id,      // Debit
                        $mutasi->lawan_coa_id, // Kredit
                        $jumlah,
                        $mutasi->branch_id,
                        'Kas Mutasi Reversal'
                    );
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
            ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_jurnal, "-", -1) AS UNSIGNED) DESC')
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

        $companyId = session('active_company_id') ?? auth()->user()?->company_id;

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $tanggal,
            'referensi_tipe' => $refTipe,
            'referensi_id'   => null,
            'company_id'     => $companyId,
            'branch_id'      => $branchId,
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
     * Helper: Bersihkan format Rupiah / Currency Indonesia menjadi float murni
     * Contoh: "500.000" -> 500000, "1.500.000,50" -> 1500000.50
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
        // Hapus 'Rp', 'rp', spasi, dan titik pemisah ribuan
        $str = str_ireplace(['rp', ' ', '.'], '', $str);
        // Ubah koma desimal menjadi titik
        $str = str_replace(',', '.', $str);
        // Hapus sisa karakter non-numerik selain minus dan titik
        $str = preg_replace('/[^0-9.-]/', '', $str);
        return (float) $str;
    }
}
