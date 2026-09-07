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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PettyCashController extends Controller
{
    /**
     * Tampilan Utama Mobile-First Modul Petty Cash (Otomatis Sesuai Sesi PT / Cabang Aktif)
     */
    public function index(Request $request)
    {
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        $tipeFilter    = $request->get('tipe', 'all'); // 'all', 'Pengeluaran', 'Pemasukan', 'Klaim'
        $startDate     = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate       = $request->get('end_date', now()->endOfMonth()->toDateString());
        $search        = $request->get('search');
        $tenderFilter  = $request->get('tender_id');

        // Query transaksi Kas Kecil otomatis sesuai sesi PT / Cabang aktif
        $query = PettyCash::with([
            'coaKas:id,code,name',
            'lawanCoa:id,code,name',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,kode_cabang,nama_cabang',
            'company:id,kode_perusahaan,nama_perusahaan',
            'creator:id,nama_lengkap,username'
        ])
        ->forActiveCompany();

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
        } else {
            $query->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        // Filter Tipe
        if ($tipeFilter !== 'all' && !empty($tipeFilter)) {
            $query->where('tipe', $tipeFilter);
        }

        // Filter Tanggal
        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        // Filter Tender
        if (!empty($tenderFilter)) {
            $query->where('tender_id', $tenderFilter);
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

        $transactions = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Master Data COA untuk Dropdown
        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        // Filter Akun Kas / Aset Lancar untuk COA Kas
        $coaKasAccounts = $coaAccounts->filter(function ($coa) {
            $code = (string)$coa->code;
            $name = strtolower($coa->name);
            return str_starts_with($code, '11') || str_contains($name, 'kas') || str_contains($name, 'petty') || str_contains($name, 'bank');
        });
        if ($coaKasAccounts->isEmpty()) {
            $coaKasAccounts = $coaAccounts;
        }

        // Hitung Saldo Kas Kecil untuk PT / Cabang Aktif (Terhubung langsung ke KasBank & Mutasi)
        $kasKecilAccount = KasBank::forActiveCompany()
            ->forActiveBranch()
            ->where(function ($q) use ($coaKasAccounts) {
                $q->whereIn('coa_id', $coaKasAccounts->pluck('id'))
                  ->orWhere('nama_akun', 'like', '%Kas Kecil%')
                  ->orWhere('tipe', 'Kas Tunai');
            })
            ->first();

        if ($kasKecilAccount) {
            $saldoKasKecil = (float) $kasKecilAccount->saldo_berjalan;
        } else {
            $baseSaldoIn = PettyCash::forActiveCompany()
                ->where('status', 'Disetujui')
                ->where('tipe', 'Pemasukan');

            $baseSaldoOut = PettyCash::forActiveCompany()
                ->where('status', 'Disetujui')
                ->whereIn('tipe', ['Pengeluaran', 'Klaim']);

            if (!empty($activeBranchId)) {
                $baseSaldoIn->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
                $baseSaldoOut->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
            } else {
                $baseSaldoIn->whereNull('branch_id')->where('tipe_entitas', 'PT');
                $baseSaldoOut->whereNull('branch_id')->where('tipe_entitas', 'PT');
            }

            $saldoKasKecil = $baseSaldoIn->sum('nominal') - $baseSaldoOut->sum('nominal');
        }

        // Statistik Bulan Ini untuk Entitas Aktif
        $statInQuery = PettyCash::forActiveCompany()
            ->where('status', 'Disetujui')
            ->where('tipe', 'Pemasukan')
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        $statOutQuery = PettyCash::forActiveCompany()
            ->where('status', 'Disetujui')
            ->whereIn('tipe', ['Pengeluaran', 'Klaim'])
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        if (!empty($activeBranchId)) {
            $statInQuery->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
            $statOutQuery->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
        } else {
            $statInQuery->whereNull('branch_id')->where('tipe_entitas', 'PT');
            $statOutQuery->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }

        $thisMonthIn  = $statInQuery->sum('nominal');
        $thisMonthOut = $statOutQuery->sum('nominal');

        $statCountQuery = PettyCash::forActiveCompany()
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        if (!empty($activeBranchId)) {
            $statCountQuery->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
        } else {
            $statCountQuery->whereNull('branch_id')->where('tipe_entitas', 'PT');
        }
        $thisMonthCount = $statCountQuery->count();

        // Master Tender Aktif
        $tenders = Tender::where('company_id', $activeCompanyId)
            ->orderBy('nomor_kontrak', 'desc')
            ->get();

        // Data PT / Cabang aktif
        $currentCompany = Company::find($activeCompanyId);
        $currentBranch  = $activeBranchId ? Branch::find($activeBranchId) : null;
        $activeEntityName = $currentBranch ? $currentBranch->nama_cabang : ($currentCompany->nama_perusahaan ?? 'PT Pusat');
        $isCabang = !empty($activeBranchId);

        return view('modul_accounting.paty_cas.index', compact(
            'transactions',
            'saldoKasKecil',
            'thisMonthIn',
            'thisMonthOut',
            'thisMonthCount',
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
            'tenderFilter'
        ));
    }

    /**
     * AJAX Data Provider (Optional JSON Feed / DataTables)
     */
    public function data(Request $request)
    {
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
            $query->where('branch_id', $activeBranchId)->where('tipe_entitas', 'Cabang');
        } else {
            $query->whereNull('branch_id')->where('tipe_entitas', 'PT');
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
                $color = $row->tipe === 'Pemasukan' ? 'text-success' : 'text-danger';
                $prefix = $row->tipe === 'Pemasukan' ? '+ ' : '- ';
                return '<span class="fw-bold font-monospace ' . $color . '">' . $prefix . 'Rp ' . number_format($row->nominal, 0, ',', '.') . '</span>';
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
        // Bersihkan formatting nominal Rupiah (contoh: "200.000" atau "1.500.000")
        if ($request->has('nominal')) {
            $rawNominal = (string)$request->nominal;
            if (str_contains($rawNominal, ',') && str_contains($rawNominal, '.')) {
                $cleaned = str_replace('.', '', $rawNominal);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (str_contains($rawNominal, '.')) {
                $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            } else {
                $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            }
            $request->merge(['nominal' => $cleaned ? (float)$cleaned : 0]);
        }

        $request->validate([
            'tanggal'       => 'required|date',
            'tipe'          => 'required|in:Pengeluaran,Pemasukan,Klaim',
            'kategori'      => 'required|string|max:100',
            'coa_kas_id'    => 'required|exists:chart_of_accounts,id',
            'lawan_coa_id'  => 'required|exists:chart_of_accounts,id',
            'tender_id'     => 'nullable|exists:tenders,id',
            'nominal'       => 'required|numeric|min:1',
            'nama_pengaju'  => 'nullable|string|max:150',
            'keterangan'    => 'nullable|string',
            'lampiran_nota' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'coa_kas_id.required'   => 'Akun Kas Kecil (COA Kas) wajib dipilih.',
            'lawan_coa_id.required' => 'Akun Lawan / Biaya (COA) wajib dipilih.',
            'nominal.min'           => 'Nominal transaksi minimal Rp 1.',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $tipeEntitas     = !empty($activeBranchId) ? 'Cabang' : 'PT';
        $branchId        = $activeBranchId ?: null;
        $nominal         = (float) $request->nominal;

        DB::beginTransaction();
        try {
            // 1. Cari Akun KasBank yang terhubung dengan COA Kas
            $kasBank = KasBank::where('coa_id', $request->coa_kas_id)
                ->forActiveCompany()
                ->forActiveBranch()
                ->first();

            if (!$kasBank) {
                $kasBank = KasBank::where('coa_id', $request->coa_kas_id)->first();
            }

            // Hitung Saldo Sebelum & Sesudah
            if ($kasBank) {
                $saldoSebelum = (float) $kasBank->saldo_berjalan;
            } else {
                $lastTrx = PettyCash::forActiveCompany()
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId), fn($q) => $q->whereNull('branch_id'))
                    ->orderBy('id', 'desc')
                    ->first();
                $saldoSebelum = $lastTrx ? (float)$lastTrx->saldo_sesudah : 0;
            }

            if ($request->tipe === 'Pengeluaran' || $request->tipe === 'Klaim') {
                $saldoSesudah = $saldoSebelum - $nominal;
            } else {
                $saldoSesudah = $saldoSebelum + $nominal;
            }

            // Update Saldo Berjalan pada KasBank jika ada
            if ($kasBank) {
                $kasBank->saldo_berjalan = $saldoSesudah;
                $kasBank->save();
            }

            // Jika Pemasukan / Pengisian dan Lawan COA adalah Bank/Kas Sumber, potong saldo bank sumber
            if ($request->tipe === 'Pemasukan') {
                $sourceBank = KasBank::where('coa_id', $request->lawan_coa_id)
                    ->forActiveCompany()
                    ->forActiveBranch()
                    ->first();
                if ($sourceBank) {
                    $sourceBank->saldo_berjalan = (float)$sourceBank->saldo_berjalan - $nominal;
                    $sourceBank->save();
                }
            }

            // 2. Generate Nomor Transaksi Kas Kecil Unik (Termasuk deleted agar tidak duplikat)
            $prefix = ($tipeEntitas === 'PT') ? 'PC-PT-' : 'PC-CBG-';
            $datePrefix = date('ymd', strtotime($request->tanggal));
            
            $lastRecord = PettyCash::withTrashed()
                ->where('nomor_transaksi', 'like', "{$prefix}{$datePrefix}-%")
                ->orderBy('id', 'desc')
                ->first();

            $sequence = 1;
            if ($lastRecord && preg_match('/-(\d+)$/', $lastRecord->nomor_transaksi, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }
            $nomorTransaksi = $prefix . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // 3. Handle Upload Bukti / Struk Nota
            $lampiranPath = null;
            if ($request->hasFile('lampiran_nota')) {
                $file = $request->file('lampiran_nota');
                $filename = 'nota_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $lampiranPath = $file->storeAs('petty_cash_receipts', $filename, 'public');
            }

            // 4. Simpan Petty Cash dengan Saldo Sebelum & Sesudah
            $pettyCash = PettyCash::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal'         => $request->tanggal,
                'tipe'            => $request->tipe,
                'kategori'        => $request->kategori,
                'tipe_entitas'    => $tipeEntitas,
                'company_id'      => $activeCompanyId,
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
                    'tipe'            => ($request->tipe === 'Pemasukan') ? 'Masuk' : 'Keluar',
                    'kategori'        => $request->kategori,
                    'lawan_coa_id'    => $request->lawan_coa_id,
                    'jumlah'          => $nominal,
                    'saldo_sebelum'   => $saldoSebelum,
                    'saldo_sesudah'   => $saldoSesudah,
                    'tanggal'         => $request->tanggal,
                    'nomor_referensi' => $nomorTransaksi,
                    'nama_kontak'     => $request->nama_pengaju,
                    'keterangan'      => "[Kas Kecil] " . ($request->keterangan ?: $request->kategori),
                    'status'          => 'Disetujui',
                    'company_id'      => $activeCompanyId,
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

            return response()->json([
                'success' => true,
                'message' => "Transaksi Kas Kecil <strong>{$nomorTransaksi}</strong> sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil disimpan. Saldo kas kecil kini menjadi Rp " . number_format($saldoSesudah, 0, ',', '.') . ".",
                'data'    => $pettyCash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
        $pettyCash->load([
            'coaKas:id,code,name,type',
            'lawanCoa:id,code,name,type',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,kode_cabang,nama_cabang',
            'company:id,kode_perusahaan,nama_perusahaan',
            'creator:id,nama_lengkap,username',
            'jurnal.details.account:id,code,name'
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
        if ($request->has('nominal')) {
            $rawNominal = (string)$request->nominal;
            if (str_contains($rawNominal, ',') && str_contains($rawNominal, '.')) {
                $cleaned = str_replace('.', '', $rawNominal);
                $cleaned = str_replace(',', '.', $cleaned);
            } elseif (str_contains($rawNominal, '.')) {
                $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            } else {
                $cleaned = preg_replace('/[^0-9]/', '', $rawNominal);
            }
            $request->merge(['nominal' => $cleaned ? (float)$cleaned : 0]);
        }

        $request->validate([
            'tanggal'       => 'required|date',
            'tipe'          => 'required|in:Pengeluaran,Pemasukan,Klaim',
            'kategori'      => 'required|string|max:100',
            'coa_kas_id'    => 'required|exists:chart_of_accounts,id',
            'lawan_coa_id'  => 'required|exists:chart_of_accounts,id',
            'tender_id'     => 'nullable|exists:tenders,id',
            'nominal'       => 'required|numeric|min:1',
            'nama_pengaju'  => 'nullable|string|max:150',
            'keterangan'    => 'nullable|string',
            'lampiran_nota' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $oldNominal = (float) $pettyCash->nominal;
        $newNominal = (float) $request->nominal;
        $oldTipe    = $pettyCash->tipe;
        $newTipe    = $request->tipe;

        DB::beginTransaction();
        try {
            // Revert dampak saldo lama pada KasBank
            $kasBank = KasBank::where('coa_id', $pettyCash->coa_kas_id)
                ->forActiveCompany()
                ->forActiveBranch()
                ->first() ?: KasBank::where('coa_id', $pettyCash->coa_kas_id)->first();

            if ($kasBank) {
                // Revert dampak lama
                if ($oldTipe === 'Pengeluaran' || $oldTipe === 'Klaim') {
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan + $oldNominal;
                } else {
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan - $oldNominal;
                }

                // Terapkan dampak baru
                if ($newTipe === 'Pengeluaran' || $newTipe === 'Klaim') {
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan - $newNominal;
                } else {
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan + $newNominal;
                }
                $kasBank->save();
            }

            // Update lampiran jika ada upload baru
            $lampiranPath = $pettyCash->lampiran_nota;
            if ($request->hasFile('lampiran_nota')) {
                if ($lampiranPath && Storage::disk('public')->exists($lampiranPath)) {
                    Storage::disk('public')->delete($lampiranPath);
                }
                $file = $request->file('lampiran_nota');
                $filename = 'nota_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $lampiranPath = $file->storeAs('petty_cash_receipts', $filename, 'public');
            }

            $pettyCash->update([
                'tanggal'       => $request->tanggal,
                'tipe'          => $request->tipe,
                'kategori'      => $request->kategori,
                'coa_kas_id'    => $request->coa_kas_id,
                'lawan_coa_id'  => $request->lawan_coa_id,
                'tender_id'     => $request->tender_id,
                'nominal'       => $newNominal,
                'nama_pengaju'  => $request->nama_pengaju,
                'keterangan'    => $request->keterangan,
                'lampiran_nota' => $lampiranPath,
                'updated_by'    => Auth::id(),
            ]);

            // Update Mutasi Kas jika ada
            $mutasi = KasMutasi::where('nomor_referensi', $pettyCash->nomor_transaksi)->first();
            if ($mutasi) {
                $mutasi->update([
                    'jumlah'     => $newNominal,
                    'tanggal'    => $request->tanggal,
                    'kategori'   => $request->kategori,
                    'keterangan' => "[Kas Kecil] " . ($request->keterangan ?: $request->kategori),
                ]);
            }

            // Sinkronisasi ulang Jurnal Umum
            $this->updatePettyCashJournal($pettyCash);

            DB::commit();

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
     * Hapus / Batalkan Transaksi Kas Kecil + Reversal Jurnal & Pengembalian Saldo Kas
     */
    public function destroy(PettyCash $pettyCash)
    {
        DB::beginTransaction();
        try {
            $nominal = (float) $pettyCash->nominal;

            // Kembalikan Saldo KasBank
            $kasBank = KasBank::where('coa_id', $pettyCash->coa_kas_id)
                ->forActiveCompany()
                ->forActiveBranch()
                ->first() ?: KasBank::where('coa_id', $pettyCash->coa_kas_id)->first();

            if ($kasBank) {
                if ($pettyCash->tipe === 'Pengeluaran' || $pettyCash->tipe === 'Klaim') {
                    // Pengeluaran dihapus -> Saldo kas dikembalikan (ditambah)
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan + $nominal;
                } else {
                    // Pemasukan dihapus -> Saldo kas dikurangi
                    $kasBank->saldo_berjalan = (float)$kasBank->saldo_berjalan - $nominal;
                }
                $kasBank->save();
            }

            // Jika Pemasukan dan ada sumber bank, kembalikan saldo bank sumber
            if ($pettyCash->tipe === 'Pemasukan') {
                $sourceBank = KasBank::where('coa_id', $pettyCash->lawan_coa_id)
                    ->forActiveCompany()
                    ->forActiveBranch()
                    ->first();
                if ($sourceBank) {
                    $sourceBank->saldo_berjalan = (float)$sourceBank->saldo_berjalan + $nominal;
                    $sourceBank->save();
                }
            }

            // Hapus KasMutasi
            KasMutasi::where('nomor_referensi', $pettyCash->nomor_transaksi)->delete();

            // Hapus Jurnal Terkait
            if ($pettyCash->jurnal_id) {
                $jurnal = Jurnal::find($pettyCash->jurnal_id);
                if ($jurnal) {
                    JurnalDetail::where('jurnal_id', $jurnal->id)->delete();
                    $jurnal->delete();
                }
            }

            // Hapus File Nota jika ada
            if ($pettyCash->lampiran_nota && Storage::disk('public')->exists($pettyCash->lampiran_nota)) {
                Storage::disk('public')->delete($pettyCash->lampiran_nota);
            }

            $nomor = $pettyCash->nomor_transaksi;
            $pettyCash->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Transaksi Kas Kecil <strong>{$nomor}</strong> berhasil dihapus dan saldo kas telah disesuaikan kembali.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete PettyCash: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Buat Jurnal Akuntansi Berpasangan
     */
    protected function createPettyCashJournal(PettyCash $pettyCash): ?int
    {
        try {
            $companyId = $pettyCash->company_id;
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
            $companyId = $pettyCash->company_id;
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
}
