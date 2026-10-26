<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Shuchkin\SimpleXLSXGen;

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

        // Stat Card Metrics - Sesuai PT & Cabang Aktif (1 Single Covering Query)
        $statRow = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal_jurnal', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_jurnal, COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_kredit), 0) as total_kredit')
            ->first();

        $totalJurnal = (int) ($statRow->total_jurnal ?? 0);
        $totalDebit  = (float) ($statRow->total_debit ?? 0);
        $totalKredit = (float) ($statRow->total_kredit ?? 0);
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
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal_jurnal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal_jurnal', '<=', $request->end_date);
        }

        // Filter Tipe Referensi (Sumber Transaksi)
        if ($request->filled('referensi_tipe')) {
            $this->applyReferensiFilter($query, $request->referensi_tipe);
        }

        // Default urutan tanggal terbaru jika belum di-sort
        if (!$request->has('order')) {
            $query->orderBy('tanggal_jurnal', 'desc')->orderBy('nomor_jurnal', 'desc');
        }

        // Dynamic stats calculation untuk 4 kartu metrik sesuai filter yang aktif (1 Single Covering Query)
        $statRow = (clone $query)->reorder()->select(DB::raw('COUNT(*) as total_jurnal, COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_kredit), 0) as total_kredit'))->first();
        $totalJurnal = (int) ($statRow->total_jurnal ?? 0);
        $totalDebit  = (float) ($statRow->total_debit ?? 0);
        $totalKredit = (float) ($statRow->total_kredit ?? 0);
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;
        $selisih     = abs($totalDebit - $totalKredit);

        return DataTables::of($query)
            ->with([
                'stats' => [
                    'total_jurnal'     => number_format($totalJurnal, 0, ',', '.'),
                    'total_debit'      => 'Rp ' . number_format($totalDebit, 2, ',', '.'),
                    'total_kredit'     => 'Rp ' . number_format($totalKredit, 2, ',', '.'),
                    'is_balanced'      => $isBalanced,
                    'balance_text'     => $isBalanced ? '100% BALANCE' : 'ADA SELISIH',
                    'balance_sub'      => $isBalanced ? 'Double-Entry Verification' : ('Selisih: Rp ' . number_format($selisih, 2, ',', '.')),
                    'raw_total_jurnal' => $totalJurnal,
                    'raw_total_debit'  => $totalDebit,
                    'raw_total_kredit' => $totalKredit,
                ]
            ])
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
                } elseif (str_contains($ref, 'Delivery') || str_contains($ref, 'Pengiriman')) {
                    $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                    $icon = 'bi-truck';
                    $ref = 'Pengiriman Barang (DO)';
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
                } elseif (str_contains($ref, 'Alokasi')) {
                    $badgeClass = 'bg-cyan-subtle text-cyan border border-cyan-subtle';
                    $icon = 'bi-box-seam';
                    $ref = 'Alokasi Biaya';
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
                $tenderBadge = !empty($row->no_tender)
                    ? '<div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 0.72rem;"><i class="bi bi-briefcase me-1"></i>Tender: ' . e($row->no_tender) . '</span></div>'
                    : '';
                return '<div class="text-body-emphasis fw-semibold small">' . e($ket) . '</div>'
                    . $tenderBadge
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
                $user = auth()->user();
                $canPrint = $user ? $user->can('cetak-jurnal-umum') : false;
                $printBtn = $canPrint ? '
                        <a href="' . route('accounting.jurnal-umum.print', $row->id) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 30px; height: 30px;" title="Cetak Voucher Jurnal (PDF)">
                            <i class="bi bi-printer" style="font-size: 0.75rem;"></i>
                        </a>' : '';

                return '
                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center btn-view-jurnal shadow-none" style="width: 30px; height: 30px;" data-id="' . $row->id . '" title="Lihat Modal Voucher Jurnal">
                            <i class="bi bi-eye" style="font-size: 0.75rem;"></i>
                        </button>
                        ' . $printBtn . '
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
        $u = auth()->user();
        $canAccess = $u && (
            $u->hasAnyRole(['Super Admin', 'Owner']) ||
            $u->can('lihat-jurnal-umum') ||
            $u->can('lihat-jurnal-purchase-invoice') ||
            $u->can('lihat-jurnal-sales-invoice') ||
            $u->can('lihat-jurnal-gr') ||
            $u->can('lihat-jurnal-gi') ||
            $u->can('lihat-jurnal-delivery-order') ||
            $u->can('lihat-jurnal-sales-payment') ||
            $u->can('lihat-jurnal-alokasi-biaya')
        );

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat rincian jurnal akuntansi.'
            ], 403);
        }

        $jurnal = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->with(['details.account', 'creator', 'branch'])
            ->findOrFail($id);

        // Auto-resolve no_tender jika masih kosong
        if (empty($jurnal->no_tender)) {
            try {
                if ($jurnal->referensi_tipe === 'Purchase Invoice' && $jurnal->referensi_id) {
                    $inv = \App\Models\PurchaseInvoice::with(['tender', 'goodsReceipt.tender', 'po.tender'])->find($jurnal->referensi_id);
                    if ($inv) {
                        $t = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                        if ($t) {
                            $jurnal->no_tender = trim(($t->nomor_kontrak ?? '') . (!empty($t->nama_tender) ? ' - ' . $t->nama_tender : ''));
                            $jurnal->saveQuietly();
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

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
        $activeCompanyId = session('active_company_id') ?: (Auth::user()?->company_id ?? 1);

        $request->validate([
            'tanggal_jurnal' => 'required|date',
            'keterangan'     => 'required|string|max:255',
            'details'        => 'required|array|min:2',
            'details.*.account_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')->where(function ($q) use ($activeCompanyId) {
                    $q->where('is_header', false);
                    if ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                })
            ],
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
        if (!auth()->user()?->can('cetak-jurnal-umum')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak voucher jurnal.');
        }

        $jurnal = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->with(['details.account', 'creator', 'branch.company', 'company'])
            ->findOrFail($id);

        $isBranch = !empty($jurnal->branch_id);
        $branch = null;
        $company = null;
        $entityName = '';
        $entitySubName = '';
        $entityCode = '';
        $entityType = '';
        $entityLogo = null;
        $entityAddress = '';
        $entityPhone = '';
        $entityEmail = '';

        if ($isBranch) {
            $branch = $jurnal->branch ?: \App\Models\Branch::with('company')->find($jurnal->branch_id);
            $company = $branch?->company ?: \App\Models\Company::find($branch?->company_id ?: 1);

            $entityName    = $branch?->nama_cabang ?: 'Cabang';
            $entitySubName = $branch?->nama_perusahaan ?: ($company?->nama_perusahaan ?: config('app.name', 'PT PERSADA AMAN SENTOSA'));
            $entityCode    = $branch?->kode_cabang ?: 'CABANG';
            $entityType    = 'Cabang';

            // Logo Cabang
            if ($branch && $branch->logo) {
                $entityLogo = \Illuminate\Support\Str::startsWith($branch->logo, ['http://', 'https://', '/'])
                    ? $branch->logo
                    : asset('storage/' . $branch->logo);
            } elseif ($company && $company->logo) {
                $entityLogo = \Illuminate\Support\Str::startsWith($company->logo, ['http://', 'https://', '/'])
                    ? $company->logo
                    : asset('storage/' . $company->logo);
            }

            $entityAddress = $branch?->address ?: ($company?->address ?: '');
            $entityPhone   = $branch?->phone ?: ($company?->phone ?: '');
            $entityEmail   = $branch?->email ?: ($company?->email ?: '');
        } else {
            $activeCompanyId = $jurnal->company_id ?: (session('active_company_id') ?: (Auth::user()->company_id ?? 1));
            $company = $jurnal->company ?: \App\Models\Company::find($activeCompanyId);

            $entityName    = $company?->nama_perusahaan ?: config('app.name', 'PT PERSADA AMAN SENTOSA');
            $entitySubName = 'Kantor Pusat';
            $entityCode    = $company?->kode_perusahaan ?: 'PUSAT';
            $entityType    = 'Kantor Pusat / PT';

            // Logo PT
            if ($company && $company->logo) {
                $entityLogo = \Illuminate\Support\Str::startsWith($company->logo, ['http://', 'https://', '/'])
                    ? $company->logo
                    : asset('storage/' . $company->logo);
            }

            $entityAddress = $company?->address ?: '';
            $entityPhone   = $company?->phone ?: '';
            $entityEmail   = $company?->email ?: '';
        }

        if (!$entityLogo && file_exists(public_path('gambar/logo.png'))) {
            $entityLogo = asset('gambar/logo.png');
        }

        // Ambil data referensi spesifik jika ada
        $refData = null;
        if ($jurnal->referensi_tipe === 'Delivery Order' && $jurnal->referensi_id) {
            $refData = \App\Models\DeliveryOrder::with(['salesOrder', 'customer'])->find($jurnal->referensi_id);
        } elseif ($jurnal->referensi_tipe === 'Sales Order' && $jurnal->referensi_id) {
            $refData = \App\Models\SalesOrder::with(['customer', 'tender'])->find($jurnal->referensi_id);
        } elseif ($jurnal->referensi_tipe === 'Sales Invoice' && $jurnal->referensi_id) {
            $refData = \App\Models\SalesInvoice::with(['customer', 'tender', 'salesOrder.tender'])->find($jurnal->referensi_id);
        } elseif ($jurnal->referensi_tipe === 'Sales Payment' && $jurnal->referensi_id) {
            $refData = \App\Models\SalesPayment::with(['customer', 'details.invoice.tender', 'details.invoice.salesOrder.tender'])->find($jurnal->referensi_id);
        } elseif (in_array($jurnal->referensi_tipe, ['Pengeluaran Bahan SPK', 'Penerimaan Barang Jadi SPK']) && $jurnal->referensi_id) {
            $refData = \App\Models\SpkProduksi::with('tender')->find($jurnal->referensi_id);
        }

        // Backward compatibility
        $companyLogo = $entityLogo;

        return view('modul_accounting.jurnal_umum.print', compact(
            'jurnal',
            'isBranch',
            'branch',
            'company',
            'entityName',
            'entitySubName',
            'entityCode',
            'entityType',
            'entityLogo',
            'entityAddress',
            'entityPhone',
            'entityEmail',
            'companyLogo',
            'refData'
        ));
    }

    /**
     * Cetak Laporan Lengkap Buku Jurnal Umum (Periode Bulanan / Tahunan)
     */
    public function printAll(Request $request)
    {
        if (!auth()->user()?->can('cetak-jurnal-umum')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak laporan jurnal umum.');
        }

        try {
            $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->toDateString() : now()->startOfMonth()->toDateString();
        } catch (\Exception $e) {
            $startDate = now()->startOfMonth()->toDateString();
        }

        try {
            $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->toDateString() : now()->toDateString();
        } catch (\Exception $e) {
            $endDate = now()->toDateString();
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $referensiTipe = $request->referensi_tipe;

        $query = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal_jurnal', [$startDate, $endDate]);

        if ($referensiTipe) {
            $this->applyReferensiFilter($query, $referensiTipe);
        }

        // Cek total data & kalkulasi grand total langsung via SQL agregasi (anti memory leak pada skala 7M)
        $statRow = (clone $query)->reorder()->select(DB::raw('COUNT(*) as total_count, COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_kredit), 0) as total_kredit'))->first();
        $totalCount  = (int) ($statRow->total_count ?? 0);
        $totalDebit  = (float) ($statRow->total_debit ?? 0);
        $totalKredit = (float) ($statRow->total_kredit ?? 0);
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;

        // Safety limit untuk cetak PDF dokumen A4 (maksimal 3.000 transaksi agar browser & web server tidak crash/freeze)
        $maxPrintLimit = 3000;
        $isTruncated   = $totalCount > $maxPrintLimit;

        $jurnals = $query->with([
                'details' => function($q) {
                    $q->select('id', 'jurnal_id', 'account_id', 'debit', 'kredit', 'keterangan');
                },
                'details.account:id,code,name',
                'creator:id,nama_lengkap',
                'branch:id,nama_cabang,kode_cabang',
                'company:id,nama_perusahaan,kode_perusahaan'
            ])
            ->orderBy('tanggal_jurnal', 'asc')
            ->orderBy('nomor_jurnal', 'asc')
            ->limit($maxPrintLimit)
            ->get();

        $activeBranchId  = session('active_branch_id') ?? Auth::user()?->branch_id;
        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);

        $branch  = $activeBranchId ? \App\Models\Branch::find($activeBranchId) : null;
        $company = \App\Models\Company::find($activeCompanyId);

        // Jika login di Cabang -> tampilkan Nama Cabang
        // Jika login di PT -> tampilkan Nama PT
        if ($branch) {
            $entityName    = $branch->nama_cabang ?: ($branch->nama_perusahaan ?: 'Cabang Perusahaan');
            $entityAddress = $branch->address ?: ($branch->city ?: ($company?->address ?: 'Sistem Informasi Manajemen Akuntansi & Keuangan ERP'));
            $entityPhone   = $branch->phone ?: $company?->phone;
        } else {
            $entityName    = $company?->nama_perusahaan ?: config('app.name', 'PT Persada Aman Sentosa');
            $entityAddress = $company?->address ?: ($company?->city ?: 'Sistem Informasi Manajemen Akuntansi & Keuangan ERP');
            $entityPhone   = $company?->phone;
        }

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
            'totalCount',
            'isTruncated',
            'maxPrintLimit',
            'company',
            'branch',
            'entityName',
            'entityAddress',
            'entityPhone',
            'companyLogo'
        ));
    }

    /**
     * Export Excel Laporan Lengkap Buku Jurnal Umum (Sesuai Desain Format PDF)
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()?->can('cetak-jurnal-umum')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor laporan jurnal umum.');
        }

        try {
            $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->toDateString() : now()->startOfMonth()->toDateString();
        } catch (\Exception $e) {
            $startDate = now()->startOfMonth()->toDateString();
        }

        try {
            $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date)->toDateString() : now()->toDateString();
        } catch (\Exception $e) {
            $endDate = now()->toDateString();
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $referensiTipe = $request->referensi_tipe;

        $query = Jurnal::forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal_jurnal', [$startDate, $endDate]);

        if ($referensiTipe) {
            $this->applyReferensiFilter($query, $referensiTipe);
        }

        // Agregasi SQL akurat tanpa memory overhead
        $statRow = (clone $query)->reorder()->select(DB::raw('COUNT(*) as total_count, COALESCE(SUM(total_debit), 0) as total_debit, COALESCE(SUM(total_kredit), 0) as total_kredit'))->first();
        $totalCount  = (int) ($statRow->total_count ?? 0);
        $totalDebit  = (float) ($statRow->total_debit ?? 0);
        $totalKredit = (float) ($statRow->total_kredit ?? 0);
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;

        // Batas transaksi untuk file Excel (10.000 transaksi jurnal)
        $maxExportLimit = 10000;

        $jurnals = $query->with([
                'details' => function($q) {
                    $q->select('id', 'jurnal_id', 'account_id', 'debit', 'kredit', 'keterangan');
                },
                'details.account:id,code,name',
            ])
            ->orderBy('tanggal_jurnal', 'asc')
            ->orderBy('nomor_jurnal', 'asc')
            ->limit($maxExportLimit)
            ->get();

        $filename = 'Laporan_Jurnal_Umum_' . date('Ymd_His') . '.xlsx';

        // Bangun Baris Spreadsheet Menyerupai Format PDF (Cyber Royal Blue & Clean Typography)
        $rows = [];

        // 1. Header Judul Laporan
        $rows[] = [
            '<style bgcolor="#1E40AF" color="#FFFFFF" font-size="14"><b>LAPORAN JURNAL UMUM</b></style>',
            '', '', '', ''
        ];

        // 2. Info Periode, Sumber Modul, Total Transaksi, Waktu Ekspor
        $infoSubtitle = 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate))
            . '  |  Sumber: ' . ($referensiTipe ?: 'Semua Modul')
            . '  |  Total: ' . number_format($totalCount, 0, ',', '.') . ' Transaksi'
            . '  |  Dicetak: ' . now()->translatedFormat('d F Y H:i') . ' WIB';

        $rows[] = [
            '<style color="#334155" font-size="10"><b>' . $this->xlsSafe($infoSubtitle) . '</b></style>',
            '', '', '', ''
        ];

        $rows[] = [];

        // 3. Header Tabel (5 Kolom Sesuai PDF: Tanggal, No. Jurnal, Nama Akun COA / Keterangan, Debit, Kredit)
        $rows[] = [
            '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>TANGGAL</b></center></style>',
            '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>NO. JURNAL</b></center></style>',
            '<style bgcolor="#1E40AF" color="#FFFFFF"><b>NAMA AKUN COA / KETERANGAN TRANSAKSI</b></style>',
            '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>DEBIT (RP)</b></right></style>',
            '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>KREDIT (RP)</b></right></style>',
        ];

        // 4. Data Transaksi & Rincian Akun
        $sumDebit = 0;
        $sumKredit = 0;

        foreach ($jurnals as $j) {
            $parsed = $this->parseJurnalKeteranganAndTender($j);
            $tglStr = date('d/m/Y', strtotime($j->tanggal_jurnal));
            $ketStr = $this->xlsSafe($parsed['keterangan']);
            if (!empty($parsed['tender'])) {
                $ketStr .= '  [Tender: ' . $this->xlsSafe($parsed['tender']) . ']';
            }
            $modulStr = '[' . ($j->referensi_tipe ?: 'Manual') . ']';

            // Baris Header Grup Jurnal (Latar Biru Muda #EFF6FF, Teks Biru #1E3A8A)
            $rows[] = [
                '<style bgcolor="#EFF6FF" color="#1D4ED8"><center><b>' . $tglStr . '</b></center></style>',
                '<style bgcolor="#EFF6FF" color="#1E40AF"><center><b>' . $this->xlsSafe($j->nomor_jurnal) . '</b></center></style>',
                '<style bgcolor="#EFF6FF" color="#1E3A8A"><b>' . $ketStr . '</b></style>',
                '<style bgcolor="#EFF6FF" color="#1E3A8A"><right><b>' . $this->xlsSafe($modulStr) . '</b></right></style>',
                '<style bgcolor="#EFF6FF"></style>',
            ];

            // Rincian Akun Debit & Kredit
            foreach ($j->details as $d) {
                $accountCode = $d->account ? $d->account->code : '-';
                $accountName = $d->account ? $d->account->name : 'Akun Tidak Ditemukan';
                $isKredit = (float) $d->kredit > 0;
                $debitVal = (float) ($d->debit ?? 0);
                $kreditVal = (float) ($d->kredit ?? 0);

                $sumDebit += $debitVal;
                $sumKredit += $kreditVal;

                // Indentasi spasi untuk baris kredit agar identik dengan tampilan PDF
                $indentSpaces = $isKredit ? '        ' : '';
                $accountLabel = $indentSpaces . '[' . $accountCode . '] ' . $this->xlsSafe($accountName);

                // Format angka native Excel (nf="#,##0.00") agar bisa di-SUM dan dihitung oleh rumus Excel
                $cellDebit = $debitVal > 0
                    ? '<style nf="#,##0.00" color="#16A34A"><right><b>' . number_format($debitVal, 2, '.', '') . '</b></right></style>'
                    : '<style color="#94A3B8"><right>-</right></style>';

                $cellKredit = $kreditVal > 0
                    ? '<style nf="#,##0.00" color="#1D4ED8"><right><b>' . number_format($kreditVal, 2, '.', '') . '</b></right></style>'
                    : '<style color="#94A3B8"><right>-</right></style>';

                $rows[] = [
                    '',
                    '<style color="#94A3B8"><center>↳</center></style>',
                    $accountLabel,
                    $cellDebit,
                    $cellKredit
                ];
            }
        }

        if ($jurnals->isEmpty()) {
            $rows[] = [
                '', '', '<center><i>Tidak ada transaksi jurnal akuntansi pada rentang periode ini.</i></center>', '', ''
            ];
        }

        // 5. Grand Total Periode & Status Balance (Dihitung 100% presisi dari seluruh baris data)
        $totalDebit  = $sumDebit;
        $totalKredit = $sumKredit;
        $isBalanced  = abs($totalDebit - $totalKredit) < 0.01;

        $balanceLabel = $isBalanced
            ? 'GRAND TOTAL PERIODE: [100% BALANCE]'
            : 'GRAND TOTAL PERIODE: [SELISIH: Rp ' . number_format(abs($totalDebit - $totalKredit), 2, ',', '.') . ']';

        $rows[] = [
            '<style bgcolor="#F1F5F9" color="#0F172A"><center><b>TOTAL</b></center></style>',
            '<style bgcolor="#F1F5F9"></style>',
            '<style bgcolor="#F1F5F9" color="#0F172A"><right><b>' . $balanceLabel . '</b></right></style>',
            '<style bgcolor="#F1F5F9" nf="#,##0.00" color="#16A34A"><right><b>' . number_format($totalDebit, 2, '.', '') . '</b></right></style>',
            '<style bgcolor="#F1F5F9" nf="#,##0.00" color="#1D4ED8"><right><b>' . number_format($totalKredit, 2, '.', '') . '</b></right></style>',
        ];

        // 6. Tanda Tangan Otorisasi Resmi
        $rows[] = [];
        $rows[] = [];
        $rows[] = [
            '',
            '<center><b>Dibuat Oleh:</b></center>',
            '<center><b>Diperiksa Oleh:</b></center>',
            '',
            '<center><b>Disetujui Oleh:</b></center>',
        ];
        $rows[] = [];
        $rows[] = [];
        $rows[] = [];
        $rows[] = [
            '',
            '<center><b>( Staff Accounting )</b></center>',
            '<center><b>( Accounting Manager )</b></center>',
            '',
            '<center><b>( Direktur Keuangan )</b></center>',
        ];

        if (ob_get_length()) {
            ob_end_clean();
        }

        $xlsx = SimpleXLSXGen::fromArray($rows);
        $xlsx->downloadAs($filename);
        exit();
    }

    /**
     * Helper: Membersihkan keterangan jurnal & ekstrak tender agar format tampilan rapi
     */
    private function parseJurnalKeteranganAndTender($jurnal): array
    {
        $text   = $jurnal->keterangan ?: '-';
        $tender = $jurnal->no_tender ?? null;

        // 1. Ekstrak tender jika ada di dalam teks
        if (!$tender && preg_match('/(?:Tender|TDN)[\s:]*([A-Za-z0-9\-_]+)/i', $text, $matches)) {
            $tender = $matches[1];
        }

        // 2. Bersihkan tag tender dalam kurung dari teks agar tidak berulang
        $clean = preg_replace('/\s*\((?:Tender)[\s:][^)]*\)/i', '', $text);
        $clean = preg_replace('/\s*\[(?:Tender)[\s:][^\]]*\]/i', '', $clean);

        // 3. Hapus kurung siku nama PT / Cabang di awal keterangan
        $clean = preg_replace('/^\[[^\]]+\]\s*/i', '', $clean);

        // 4. Hapus referensi kode dokumen / nomor ref yang panjang
        $clean = preg_replace('/\b(?:PC|DO|INV|CR|CD|PO|SO|GR|GI|KM|KK|SPK|TF|ADJ|OPN)(?:-[A-Z0-9\/\-_]+|\/[A-Z0-9\/\-_]+)\b/i', '', $clean);

        // 5. Bersihkan nomor ref dalam kurung jika masih ada
        $clean = preg_replace('/\s*\((?:SO|PO|DO|CR|CD|INV|GR|GI|SPK|PC)[^)]*\)/i', '', $clean);
        $clean = preg_replace('/\s*\([A-Z0-9\/\-_]{6,}\)/i', '', $clean);

        // 6. Bersihkan duplikasi "PT PT" menjadi "PT"
        $clean = preg_replace('/\bPT\s+PT\b/i', 'PT', $clean);

        // 7. Ubah variasi kata sambung " dari " atau " ke " menjadi separator " - "
        $clean = preg_replace('/\s+(?:ke|dari)\s+/i', ' - ', $clean);

        // 8. Rapikan pemisah ganda
        $clean = preg_replace('/\s*[-:]\s*[-:]+\s*/', ' - ', $clean);
        $clean = preg_replace('/\s*:\s*/', ': ', $clean);
        $clean = preg_replace('/\s*\(\s*\)/', '', $clean);
        $clean = preg_replace('/\s*#\s*/', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean, " -:,#");

        return [
            'keterangan' => $clean ?: $text,
            'tender'     => $tender
        ];
    }

    /**
     * Helper: Sanitasi teks user untuk mencegah CWE-1236 Formula Injection & SimpleXLSXGen tag injection
     */
    private function xlsSafe($value): string
    {
        $clean = str_replace(['<', '>'], '', strip_tags((string) $value));
        $trimmed = trim($clean);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $clean;
        }
        return $clean;
    }

    /**
     * Helper untuk Filter Tipe / Sumber Referensi Jurnal
     */
    private function applyReferensiFilter($query, $refFilter)
    {
        if (empty($refFilter)) {
            return;
        }

        if ($refFilter === 'Reversal') {
            $query->where(function ($q) {
                $q->where('referensi_tipe', 'LIKE', '%Reversal%')
                  ->orWhere('keterangan', 'LIKE', '%[REVERSAL%');
            });
        } elseif ($refFilter === 'Delivery Order' || $refFilter === 'DeliveryOrder') {
            $query->whereIn('referensi_tipe', ['Delivery Order', 'DeliveryOrder']);
        } elseif ($refFilter === 'Kas Mutasi' || $refFilter === 'KasBank') {
            $query->where(function ($q) {
                $q->whereIn('referensi_tipe', ['Kas Mutasi', 'KasMutasi', 'KasBank'])
                  ->where('referensi_tipe', 'NOT LIKE', '%Reversal%');
            });
        } elseif ($refFilter === 'PettyCash') {
            $query->where('referensi_tipe', 'PettyCash');
        } elseif ($refFilter === 'Sales') {
            $query->whereIn('referensi_tipe', ['Sales Invoice', 'Sales Payment']);
        } elseif ($refFilter === 'Purchase') {
            $query->where(function ($q) {
                $q->whereIn('referensi_tipe', ['Purchase Invoice', 'Purchase Payment', 'Alokasi Biaya'])
                  ->where('referensi_tipe', 'NOT LIKE', '%Reversal%');
            });
        } elseif ($refFilter === 'Alokasi Biaya' || $refFilter === 'Alokasi') {
            $query->where('referensi_tipe', 'Alokasi Biaya');
        } elseif ($refFilter === 'Gudang' || $refFilter === 'Stok') {
            $query->whereIn('referensi_tipe', ['Goods Receipt', 'Goods Issue', 'Stock Opname', 'Penyesuaian Stok']);
        } elseif ($refFilter === 'Manufaktur' || $refFilter === 'SPK') {
            $query->whereIn('referensi_tipe', ['Penerimaan Barang Jadi SPK', 'Pengeluaran Bahan SPK']);
        } elseif ($refFilter === 'Saldo Awal') {
            $query->where('referensi_tipe', 'Saldo Awal');
        } elseif ($refFilter === 'Manual') {
            $query->where(function ($q) {
                $q->whereNull('referensi_tipe')
                  ->orWhere('referensi_tipe', '')
                  ->orWhere('referensi_tipe', 'Manual');
            });
        } else {
            $query->where('referensi_tipe', $refFilter);
        }
    }
}
