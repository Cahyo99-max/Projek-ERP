<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JurnalUmumController extends Controller
{
    /**
     * Halaman Utama Jurnal Umum (General Journal)
     */
    public function index(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if (!$startDate || !$endDate) {
            $hasJurnalThisMonth = Jurnal::forActiveCompany()
                ->forActiveBranch()
                ->whereBetween('tanggal_jurnal', [now()->startOfMonth()->toDateString(), now()->toDateString()])
                ->exists();

            if ($hasJurnalThisMonth) {
                $startDate = now()->startOfMonth()->toDateString();
                $endDate   = now()->toDateString();
            } else {
                // Jika bulan berjalan belum ada jurnal, default ke 30 hari terakhir agar riwayat transaksi langsung tampil
                $startDate = now()->subDays(30)->toDateString();
                $endDate   = now()->toDateString();
            }
        }

        // Akun COA untuk Form Input Jurnal Manual
        $coaAccounts = ChartOfAccount::forActiveCompany()
            ->where('is_header', false)
            ->orderBy('code', 'asc')
            ->get();

        // Stat Card Metrics - Sesuai PT & Cabang Aktif
        $baseQuery = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal_jurnal', [$startDate, $endDate]);
        
        $totalJurnal = (clone $baseQuery)->count();
        $totalDebit  = (clone $baseQuery)->sum('total_debit');
        $totalKredit = (clone $baseQuery)->sum('total_kredit');
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;

        return view('modul_accounting.jurnal_umum.index', compact(
            'coaAccounts',
            'startDate',
            'endDate',
            'totalJurnal',
            'totalDebit',
            'totalKredit',
            'isBalanced'
        ));
    }

    /**
     * AJAX DataTables Server-Side Provider
     */
    public function getData(Request $request)
    {
        $query = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->with(['details.account', 'creator', 'branch', 'company'])
            ->select('jurnals.*');

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_jurnal', [$request->start_date, $request->end_date]);
        }

        // Filter Tipe Referensi (Sumber Transaksi)
        if ($request->filled('referensi_tipe')) {
            $query->where('referensi_tipe', $request->referensi_tipe);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nomor_jurnal', function ($row) {
                return '<span class="fw-bold font-monospace text-body-emphasis">' . e($row->nomor_jurnal) . '</span>';
            })
            ->editColumn('tanggal_jurnal', function ($row) {
                return '<span class="font-monospace text-muted small">' . date('d/m/Y', strtotime($row->tanggal_jurnal)) . '</span>';
            })
            ->addColumn('sumber_transaksi', function ($row) {
                $ref = $row->referensi_tipe ?: 'Manual';
                $badgeClass = 'bg-secondary-subtle text-secondary border';
                $icon = 'bi-journal-text';

                if (str_contains($ref, 'Reversal') || str_contains($row->keterangan ?? '', '[REVERSAL]')) {
                    $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                    $icon = 'bi-arrow-counterclockwise';
                    $ref = 'Jurnal Pembalik (Reversal)';
                } elseif (str_contains($ref, 'KasBank') || str_contains($ref, 'KasMutasi')) {
                    $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                    $icon = 'bi-cash-coin';
                    $ref = 'Kas & Bank';
                } elseif (str_contains($ref, 'Sales') || str_contains($ref, 'Order')) {
                    $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                    $icon = 'bi-cart-check';
                    $ref = 'Penjualan';
                } elseif (str_contains($ref, 'Purchase') || str_contains($ref, 'Beli')) {
                    $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                    $icon = 'bi-bag-check';
                    $ref = 'Pembelian';
                } elseif (str_contains($ref, 'Stock') || str_contains($ref, 'Gudang') || str_contains($ref, 'Receipt') || str_contains($ref, 'Issue')) {
                    $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                    $icon = 'bi-boxes';
                    $ref = 'Persediaan Gudang';
                } elseif ($ref === 'Manual' || $ref === 'Adjustment') {
                    $badgeClass = 'bg-purple-subtle text-purple border';
                    $icon = 'bi-pencil-square';
                    $ref = 'Jurnal Penyesuaian';
                }

                return '<span class="badge ' . $badgeClass . ' rounded-pill font-monospace px-2.5 py-1">'
                    . '<i class="bi ' . $icon . ' me-1"></i>' . e($ref) . '</span>';
            })
            ->editColumn('keterangan', function ($row) {
                $ket = $row->keterangan ?: '-';
                $detailsCount = $row->details->count();
                return '<div class="text-body-emphasis fw-semibold small">' . e($ket) . '</div>'
                    . '<div class="text-secondary font-monospace" style="font-size: 0.72rem;"><i class="bi bi-list-nested me-1"></i>' . $detailsCount . ' Entri Baris Akun</div>';
            })
            ->editColumn('total_debit', function ($row) {
                return '<span class="font-monospace fw-bold text-success text-end d-block">Rp ' . number_format($row->total_debit, 2, ',', '.') . '</span>';
            })
            ->editColumn('total_kredit', function ($row) {
                return '<span class="font-monospace fw-bold text-primary text-end d-block">Rp ' . number_format($row->total_kredit, 2, ',', '.') . '</span>';
            })
            ->addColumn('status_balance', function ($row) {
                $isBalanced = abs($row->total_debit - $row->total_kredit) < 0.01;
                if ($isBalanced) {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>Balance</span>';
                }
                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle me-1"></i>Unbalanced</span>';
            })
            ->addColumn('raw_details', function ($row) {
                return $row->details;
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-view-jurnal shadow-none" style="width: 30px; height: 30px;" data-id="' . $row->id . '" title="Lihat Modal Voucher Jurnal">
                            <i class="bi bi-eye" style="font-size: 0.75rem;"></i>
                        </button>
                        <a href="' . route('accounting.jurnal-umum.print', $row->id) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 30px; height: 30px;" title="Cetak Voucher Jurnal (PDF)">
                            <i class="bi bi-printer" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['expand_btn', 'nomor_jurnal', 'tanggal_jurnal', 'sumber_transaksi', 'keterangan', 'total_debit', 'total_kredit', 'status_balance', 'action'])
            ->make(true);
    }

    /**
     * Menampilkan Rincian Jurnal JSON
     */
    public function show($id)
    {
        $jurnal = Jurnal::with(['details.account', 'creator', 'branch'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $jurnal
        ]);
    }

    /**
     * Simpan Jurnal Manual / Penyesuaian
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'tanggal_jurnal' => 'required|date',
            'keterangan'     => 'required|string|max:255',
            'details'        => 'required|array|min:2',
            'details.*.account_id' => 'required|exists:chart_of_accounts,id',
            'details.*.debit'      => 'nullable|string',
            'details.*.kredit'     => 'nullable|string',
        ]);

        // Sanitize & Validate Balance
        $totalDebit = 0;
        $totalKredit = 0;
        $processedDetails = [];

        foreach ($request->details as $item) {
            $debitVal = 0;
            if (!empty($item['debit'])) {
                $cleanDebit = str_replace('.', '', $item['debit']);
                $cleanDebit = str_replace(',', '.', $cleanDebit);
                $debitVal   = (float)$cleanDebit;
            }

            $kreditVal = 0;
            if (!empty($item['kredit'])) {
                $cleanKredit = str_replace('.', '', $item['kredit']);
                $cleanKredit = str_replace(',', '.', $cleanKredit);
                $kreditVal   = (float)$cleanKredit;
            }

            if ($debitVal <= 0 && $kreditVal <= 0) {
                continue; // Abaikan baris kosong
            }

            $totalDebit  += $debitVal;
            $totalKredit += $kreditVal;

            $processedDetails[] = [
                'account_id' => $item['account_id'],
                'debit'      => $debitVal,
                'kredit'     => $kreditVal,
                'keterangan' => $item['keterangan'] ?? $request->keterangan,
            ];
        }

        if (count($processedDetails) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal minimal harus memiliki 2 baris akun (Satu Debit & Satu Kredit).'
            ], 422);
        }

        // Cek Keseimbangan Debit vs Kredit
        if (abs($totalDebit - $totalKredit) >= 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal TIDAK SEIMBANG (Unbalanced)! Total Debit (Rp ' . number_format($totalDebit, 0, ',', '.') . ') harus sama dengan Total Kredit (Rp ' . number_format($totalKredit, 0, ',', '.') . ').'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate Nomor Jurnal: JU-YYMM-XXXX
            $prefix = 'JU-' . date('ym', strtotime($request->tanggal_jurnal)) . '-';
            $lastRecord = DB::table('jurnals')
                ->where('nomor_jurnal', 'LIKE', "{$prefix}%")
                ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_jurnal, "-", -1) AS UNSIGNED) DESC')
                ->first();

            $nextSequence = 1;
            if ($lastRecord) {
                $parts = explode('-', $lastRecord->nomor_jurnal);
                $nextSequence = ((int) end($parts)) + 1;
            }
            do {
                $nomorJurnal = $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('jurnals')->where('nomor_jurnal', $nomorJurnal)->exists();
                if ($exists) {
                    $nextSequence++;
                }
            } while ($exists);

            $rawBranchId = session('active_branch_id'); // NULL jika di sesi PT Pusat, ID jika di Cabang
            $isBranch = !empty($rawBranchId);
            $jurnalCompanyId = $isBranch ? null : (session('active_company_id') ?: (Auth::user()->company_id ?? 1));
            $jurnalBranchId  = $isBranch ? $rawBranchId : null;

            // Create Master Jurnal
            $jurnal = Jurnal::create([
                'nomor_jurnal'   => $nomorJurnal,
                'tanggal_jurnal' => $request->tanggal_jurnal,
                'referensi_tipe' => 'Manual',
                'referensi_id'   => null,
                'company_id'     => $jurnalCompanyId,
                'branch_id'      => $jurnalBranchId,
                'total_debit'    => $totalDebit,
                'total_kredit'   => $totalKredit,
                'keterangan'     => $request->keterangan,
                'created_by'     => Auth::id() ?: 1,
            ]);

            // Create Details
            foreach ($processedDetails as $detail) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $detail['account_id'],
                    'debit'      => $detail['debit'],
                    'kredit'     => $detail['kredit'],
                    'keterangan' => $detail['keterangan'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Jurnal Manual {$nomorJurnal} berhasil dibukukan dengan seimbang!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memposting jurnal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cetak Voucher Jurnal Satuan (PDF/Print View)
     */
    public function print($id)
    {
        $jurnal = Jurnal::with(['details.account', 'creator', 'branch', 'company'])->findOrFail($id);

        $activeCompanyId = $jurnal->company_id ?: (session('active_company_id') ?: (Auth::user()->company_id ?? 1));
        $company = \App\Models\Company::find($activeCompanyId);

        $companyLogo = null;
        if ($company && $company->logo) {
            $companyLogo = \Illuminate\Support\Str::startsWith($company->logo, ['http://', 'https://', '/'])
                ? $company->logo
                : asset('storage/' . $company->logo);
        }
        if (!$companyLogo && file_exists(public_path('gambar/logo.png'))) {
            $companyLogo = asset('gambar/logo.png');
        }

        return view('modul_accounting.jurnal_umum.print', compact('jurnal', 'company', 'companyLogo'));
    }

    /**
     * Cetak Laporan Lengkap Buku Jurnal Umum (Periode Bulanan / Tahunan)
     */
    public function printAll(Request $request)
    {
        $startDate = $request->start_date ?: now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?: now()->toDateString();
        $referensiTipe = $request->referensi_tipe;

        $query = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->with(['details.account', 'creator', 'branch', 'company'])
            ->whereBetween('tanggal_jurnal', [$startDate, $endDate])
            ->orderBy('tanggal_jurnal', 'asc')
            ->orderBy('nomor_jurnal', 'asc');

        if ($referensiTipe) {
            $query->where('referensi_tipe', $referensiTipe);
        }

        $jurnals = $query->get();

        $totalDebit  = $jurnals->sum('total_debit');
        $totalKredit = $jurnals->sum('total_kredit');
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $company = \App\Models\Company::find($activeCompanyId);

        $companyLogo = null;
        if ($company && $company->logo) {
            $companyLogo = \Illuminate\Support\Str::startsWith($company->logo, ['http://', 'https://', '/'])
                ? $company->logo
                : asset('storage/' . $company->logo);
        }
        if (!$companyLogo && file_exists(public_path('gambar/logo.png'))) {
            $companyLogo = asset('gambar/logo.png');
        }

        return view('modul_accounting.jurnal_umum.print_all', compact(
            'jurnals',
            'startDate',
            'endDate',
            'referensiTipe',
            'totalDebit',
            'totalKredit',
            'isBalanced',
            'company',
            'companyLogo'
        ));
    }
}
