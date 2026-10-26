<?php

namespace App\Http\Controllers;

use App\Models\PurchasePayment;
use App\Models\PurchasePaymentDetail;
use App\Models\PurchaseInvoice;
use App\Models\MasterSupplier;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\PaymentMethod;
use App\Models\MasterCurrency;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PurchasePaymentController extends Controller
{
    /**
     * Endpoint super cepat (sub-millisecond) untuk real-time status & fingerprint sync.
     * Menggunakan index database tanpa rendering HTML sehingga server tetap ringan bahkan pada jutaan data.
     */
    /**
     * Endpoint super cepat (sub-millisecond) untuk real-time status & fingerprint sync.
     * Menggunakan index database tanpa rendering HTML sehingga server tetap ringan bahkan pada jutaan data.
     */
    public function checkSync(Request $request)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $userId          = auth()->id();

        // 1. Cek versi pembayaran utang (PurchasePayment)
        $paymentQuery = PurchasePayment::query();
        if (!empty($activeBranchId)) {
            $paymentQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
        } else {
            $paymentQuery->whereNull('branch_id');
            if ($activeCompanyId) {
                $paymentQuery->where('company_id', $activeCompanyId);
            }
        }
        if ($userId) {
            $paymentQuery->where(function ($q) use ($userId) {
                $q->where('status', '!=', 'Draft')
                  ->orWhere('created_by', $userId);
            });
        }
        $paymentMeta = $paymentQuery->selectRaw('MAX(updated_at) as last_updated, MAX(id) as max_id, COUNT(id) as total_rows')->first();

        // 2. Cek versi faktur pembelian (PurchaseInvoice) agar perubahan utang/overdue terdeteksi instan
        $invoiceQuery = PurchaseInvoice::query();
        if (!empty($activeBranchId)) {
            $invoiceQuery->where('branch_id', $activeBranchId)->whereNull('company_id');
        } else {
            $invoiceQuery->whereNull('branch_id');
            if ($activeCompanyId) {
                $invoiceQuery->where('company_id', $activeCompanyId);
            }
        }
        $invoiceMeta = $invoiceQuery->selectRaw('MAX(updated_at) as last_updated, MAX(id) as max_id, COUNT(id) as total_rows')->first();

        $fingerprint = md5(
            ($paymentMeta->last_updated ?? '0') . '_' . ($paymentMeta->max_id ?? '0') . '_' . ($paymentMeta->total_rows ?? '0') . '|' .
            ($invoiceMeta->last_updated ?? '0') . '_' . ($invoiceMeta->max_id ?? '0') . '_' . ($invoiceMeta->total_rows ?? '0')
        );

        $clientFingerprint = $request->input('fingerprint');

        // Jika fingerprint sama dengan client, response ultra-cepat (sub-millisecond)
        if ($clientFingerprint && $clientFingerprint === $fingerprint) {
            return response()->json([
                'changed'      => false,
                'fingerprint'  => $fingerprint,
                'last_updated' => $paymentMeta->last_updated,
            ]);
        }

        // Jika fingerprint berbeda atau polling awal, berikan KPI summary terkini
        $kpi = $this->getKpiSummaryData($activeBranchId, $activeCompanyId, $userId);

        return response()->json([
            'changed'      => true,
            'fingerprint'  => $fingerprint,
            'last_updated' => $paymentMeta->last_updated,
            'kpi'          => $kpi,
        ]);
    }

    /**
     * Hitung ringkasan data KPI Bento Cards secara cepat dan aman (Direct SQL Aggregation)
     */
    public function getKpiSummaryData($activeBranchId, $activeCompanyId, $userId)
    {
        // ── 1. KPI Monthly Payments Aggregation ──
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $monthlyKpi = DB::table('purchase_payments as pp')
            ->leftJoin('master_currencies as mc', 'mc.id', '=', 'pp.currency_id')
            ->where('pp.status', 'Posted')
            ->whereNull('pp.deleted_at')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('pp.branch_id', $activeBranchId)->whereNull('pp.company_id');
                } else {
                    $q->whereNull('pp.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('pp.company_id', $activeCompanyId);
                    }
                }
            })
            ->whereBetween('pp.tanggal_bayar', [$startOfMonth, $endOfMonth])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN UPPER(COALESCE(mc.kode_currency, 'IDR')) IN ('USD', 'DOLAR') THEN pp.total_bayar ELSE 0 END), 0) as total_usd,
                COALESCE(SUM(CASE WHEN UPPER(COALESCE(mc.kode_currency, 'IDR')) NOT IN ('USD', 'DOLAR') THEN pp.total_bayar ELSE 0 END), 0) as total_idr,
                COALESCE(SUM(
                    CASE 
                        WHEN UPPER(COALESCE(mc.kode_currency, 'IDR')) IN ('USD', 'DOLAR') THEN pp.total_bayar * CASE WHEN pp.kurs > 0 THEN pp.kurs ELSE 1 END
                        ELSE pp.total_bayar 
                    END
                ), 0) as total_equivalent_idr
            ")
            ->first();

        $totalPaymentIdrThisMonth           = (float) ($monthlyKpi->total_idr ?? 0);
        $totalPaymentUsdThisMonth           = (float) ($monthlyKpi->total_usd ?? 0);
        $totalPaymentEquivalentIdrThisMonth = (float) ($monthlyKpi->total_equivalent_idr ?? 0);

        // ── 2. KPI Status Counts ──
        $statusCountsQuery = DB::table('purchase_payments as pp')
            ->whereNull('pp.deleted_at')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('pp.branch_id', $activeBranchId)->whereNull('pp.company_id');
                } else {
                    $q->whereNull('pp.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('pp.company_id', $activeCompanyId);
                    }
                }
            });

        if ($userId) {
            $statusCountsQuery->where(function ($q) use ($userId) {
                $q->where('pp.status', '!=', 'Draft')
                  ->orWhere('pp.created_by', $userId);
            });
        }

        $statusCounts = $statusCountsQuery->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'Draft' THEN 1 END) as draft,
            COUNT(CASE WHEN status = 'Diajukan' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'Ditolak' THEN 1 END) as rejected,
            COUNT(CASE WHEN status = 'Posted' THEN 1 END) as posted,
            COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) as cancelled
        ")->first();

        $totalPaymentCount   = (int) ($statusCounts->total ?? 0);
        $totalDraftCount     = (int) ($statusCounts->draft ?? 0);
        $totalPendingCount   = (int) ($statusCounts->pending ?? 0);
        $totalRejectedCount  = (int) ($statusCounts->rejected ?? 0);
        $totalPostedCount    = (int) ($statusCounts->posted ?? 0);
        $totalCancelledCount = (int) ($statusCounts->cancelled ?? 0);

        // ── 3. Outstanding AP & Overdue ──
        $today = now()->toDateString();

        $paidSub = DB::table('purchase_payment_details as ppd')
            ->join('purchase_payments as pp', function($j) {
                $j->on('pp.id', '=', 'ppd.purchase_payment_id')
                  ->where('pp.status', '=', 'Posted')
                  ->whereNull('pp.deleted_at');
            })
            ->select('ppd.purchase_invoice_id', DB::raw('SUM(ppd.jumlah_bayar + ppd.potongan) as total_paid'))
            ->groupBy('ppd.purchase_invoice_id');

        $apKpiQuery = DB::table('purchase_invoices as pi')
            ->leftJoin('master_currencies as mc', 'mc.id', '=', 'pi.currency_id')
            ->leftJoinSub($paidSub, 'paid_sub', function($j) {
                $j->on('paid_sub.purchase_invoice_id', '=', 'pi.id');
            })
            ->where('pi.status', 'Posted')
            ->whereNull('pi.deleted_at')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('pi.branch_id', $activeBranchId)->whereNull('pi.company_id');
                } else {
                    $q->whereNull('pi.branch_id');
                    if (!empty($activeCompanyId)) {
                        $q->where('pi.company_id', $activeCompanyId);
                    }
                }
            })
            ->selectRaw("
                pi.id,
                UPPER(COALESCE(mc.kode_currency, 'IDR')) as cur_code,
                CASE WHEN pi.kurs > 0 THEN pi.kurs ELSE 1 END as kurs_val,
                pi.tanggal_jatuh_tempo,
                GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(paid_sub.total_paid, 0))) as sisa_utang
            ")
            ->havingRaw("sisa_utang > 0.01")
            ->get();

        $totalOutstandingApIdr           = 0;
        $totalOutstandingApUsd           = 0;
        $totalOutstandingApEquivalentIdr = 0;
        $totalOverdueCount               = 0;
        $overdueIdrAmount                = 0;
        $overdueUsdAmount                = 0;

        foreach ($apKpiQuery as $inv) {
            $rem       = (float) $inv->sisa_utang;
            $kurs      = (float) $inv->kurs_val;
            $isOverdue = ($inv->tanggal_jatuh_tempo && $inv->tanggal_jatuh_tempo < $today);

            if ($isOverdue) {
                $totalOverdueCount++;
            }

            if (in_array($inv->cur_code, ['USD', 'DOLAR'])) {
                $totalOutstandingApUsd           += $rem;
                $totalOutstandingApEquivalentIdr += ($rem * $kurs);
                if ($isOverdue) {
                    $overdueUsdAmount += $rem;
                }
            } else {
                $totalOutstandingApIdr           += $rem;
                $totalOutstandingApEquivalentIdr += $rem;
                if ($isOverdue) {
                    $overdueIdrAmount += $rem;
                }
            }
        }

        $overdueSub = 'Semua jatuh tempo aman';
        if ($totalOverdueCount > 0) {
            if ($overdueIdrAmount > 0 && $overdueUsdAmount > 0) {
                $overdueSub = 'Rp ' . number_format($overdueIdrAmount, 2, ',', '.') . ' + $' . number_format($overdueUsdAmount, 2, ',', '.');
            } elseif ($overdueIdrAmount > 0) {
                $overdueSub = 'Rp ' . number_format($overdueIdrAmount, 2, ',', '.') . ' (IDR)';
            } elseif ($overdueUsdAmount > 0) {
                $overdueSub = '$ ' . number_format($overdueUsdAmount, 2, ',', '.') . ' (USD)';
            }
        }

        return [
            'totalPaymentIdrThisMonth'           => $totalPaymentIdrThisMonth,
            'totalPaymentUsdThisMonth'           => $totalPaymentUsdThisMonth,
            'totalPaymentEquivalentIdrThisMonth' => $totalPaymentEquivalentIdrThisMonth,
            'totalOutstandingApIdr'              => $totalOutstandingApIdr,
            'totalOutstandingApUsd'              => $totalOutstandingApUsd,
            'totalOutstandingApEquivalentIdr'    => $totalOutstandingApEquivalentIdr,
            'totalOverdueCount'                  => $totalOverdueCount,
            'overdueIdrAmount'                   => $overdueIdrAmount,
            'overdueUsdAmount'                   => $overdueUsdAmount,
            'totalPaymentCount'                  => $totalPaymentCount,
            'totalDraftCount'                    => $totalDraftCount,
            'totalPendingCount'                  => $totalPendingCount,
            'totalRejectedCount'                 => $totalRejectedCount,
            'totalPostedCount'                   => $totalPostedCount,
            'totalCancelledCount'                => $totalCancelledCount,

            // Pre-formatted string untuk update DOM instan & seamless
            'fmt_monthly_idr'        => 'Rp ' . number_format($totalPaymentIdrThisMonth, 2, ',', '.'),
            'fmt_monthly_usd'        => '$ ' . number_format($totalPaymentUsdThisMonth, 2, ',', '.'),
            'fmt_monthly_equiv'      => '≈ Rp ' . number_format($totalPaymentEquivalentIdrThisMonth, 2, ',', '.'),
            'fmt_outstanding_idr'    => 'Rp ' . number_format($totalOutstandingApIdr, 2, ',', '.'),
            'fmt_outstanding_usd'    => '$ ' . number_format($totalOutstandingApUsd, 2, ',', '.'),
            'fmt_outstanding_equiv'  => '≈ Rp ' . number_format($totalOutstandingApEquivalentIdr, 2, ',', '.'),
            'fmt_overdue_count'      => $totalOverdueCount,
            'fmt_overdue_sub'        => $overdueSub,
            'fmt_total_count'        => $totalPaymentCount,
            'fmt_total_sub'          => "{$totalPostedCount} Diposting • {$totalPendingCount} Diajukan • {$totalDraftCount} Draft",
        ];
    }

    /**
     * Tampilan Utama Daftar Pembayaran Utang
     */
    public function index(Request $request)
    {
        abort_unless(
            auth()->user()->can('lihat-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk melihat data pembayaran utang.'
        );

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $userId          = auth()->id();

        if ($request->ajax()) {
            $query = PurchasePayment::query()
                ->select([
                    'purchase_payments.id',
                    'purchase_payments.nomor_pembayaran',
                    'purchase_payments.tanggal_bayar',
                    'purchase_payments.supplier_id',
                    'purchase_payments.kas_bank_id',
                    'purchase_payments.payment_method_id',
                    'purchase_payments.nomor_referensi',
                    'purchase_payments.branch_id',
                    'purchase_payments.company_id',
                    'purchase_payments.currency_id',
                    'purchase_payments.kurs',
                    'purchase_payments.total_faktur',
                    'purchase_payments.total_potongan',
                    'purchase_payments.potongan_coa_id',
                    'purchase_payments.total_bayar',
                    'purchase_payments.status',
                    'purchase_payments.keterangan',
                    'purchase_payments.lampiran',
                    'purchase_payments.created_by',
                    'purchase_payments.posted_by',
                    'purchase_payments.submitted_by',
                    'purchase_payments.approved_by',
                    'purchase_payments.created_at',
                    'purchase_payments.updated_at'
                ])
                ->with([
                    'supplier:id,nama_supplier,kode_supplier',
                    'kasBank:id,nama_akun,nomor_rekening,tipe',
                    'paymentMethod:id,nama_metode',
                    'potonganCoa:id,code,name',
                    'branch:id,nama_cabang',
                    'currency:id,kode_currency,simbol',
                    'creator:id,nama_lengkap,username',
                    'poster:id,nama_lengkap,username',
                    'submitter:id,nama_lengkap,username',
                    'approver:id,nama_lengkap,username',
                    'details.invoice:id,nomor_faktur,tender_id,po_id,gr_id',
                    'details.invoice.tender:id,nomor_kontrak,nama_tender',
                    'details.invoice.po.tender:id,nomor_kontrak,nama_tender',
                    'details.invoice.goodsReceipt.tender:id,nomor_kontrak,nama_tender',
                ])
                ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                    if (!empty($activeBranchId)) {
                        $q->where('purchase_payments.branch_id', $activeBranchId)
                          ->whereNull('purchase_payments.company_id');
                    } else {
                        $q->whereNull('purchase_payments.branch_id');
                        if (!empty($activeCompanyId)) {
                            $q->where('purchase_payments.company_id', $activeCompanyId);
                        }
                    }
                });

            // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh user pembuatnya (tanpa kecuali)
            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('purchase_payments.status', '!=', 'Draft')
                      ->orWhere('purchase_payments.created_by', $userId);
                });
            }

            // Custom search
            if ($request->filled('search_custom')) {
                $term = $request->search_custom;
                $query->where(function ($q) use ($term) {
                    $q->where('purchase_payments.nomor_pembayaran', 'like', "%{$term}%")
                      ->orWhere('purchase_payments.nomor_referensi', 'like', "%{$term}%")
                      ->orWhere('purchase_payments.keterangan', 'like', "%{$term}%")
                      ->orWhereHas('supplier', function ($sq) use ($term) {
                          $sq->where('nama_supplier', 'like', "%{$term}%")
                             ->orWhere('kode_supplier', 'like', "%{$term}%");
                      })
                      ->orWhereHas('details.invoice', function ($invQ) use ($term) {
                          $invQ->where('nomor_faktur', 'like', "%{$term}%")
                               ->orWhere('nomor_faktur_supplier', 'like', "%{$term}%")
                               ->orWhereHas('tender', function ($tQ) use ($term) {
                                   $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                                      ->orWhere('nama_tender', 'like', "%{$term}%");
                               })
                               ->orWhereHas('po.tender', function ($tQ) use ($term) {
                                   $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                                      ->orWhere('nama_tender', 'like', "%{$term}%");
                               })
                               ->orWhereHas('goodsReceipt.tender', function ($tQ) use ($term) {
                                   $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                                      ->orWhere('nama_tender', 'like', "%{$term}%");
                               });
                      });
                });
            }

            // Filter status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('purchase_payments.status', $request->status);
            }

            // Filter supplier
            if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
                $query->where('purchase_payments.supplier_id', $request->supplier_id);
            }

            // Filter kas/bank
            if ($request->filled('kas_bank_id') && $request->kas_bank_id !== 'all') {
                $query->where('purchase_payments.kas_bank_id', $request->kas_bank_id);
            }

            // Filter tanggal
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('purchase_payments.tanggal_bayar', [$request->start_date, $request->end_date]);
            }

            // Urutkan berdasarkan data yang paling terakhir di-input
            $query->orderBy('purchase_payments.id', 'desc');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('nomor_pembayaran', function ($p) {
                    $canDetail = auth()->user()->can('detail-pembayaran-utang');
                    if ($canDetail) {
                        $html = '<a href="javascript:void(0)" class="fw-bold text-primary btn-view-payment text-decoration-none" data-id="' . $p->id . '" title="Lihat Detail">' . e($p->nomor_pembayaran) . '</a>';
                    } else {
                        $html = '<span class="fw-bold text-dark">' . e($p->nomor_pembayaran) . '</span>';
                    }
                    if (!empty($p->lampiran) && $canDetail) {
                        $imgUrl = asset('storage/' . $p->lampiran);
                        $html .= ' <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 ms-1 btn-preview-image align-middle shadow-xs" data-url="' . $imgUrl . '" data-title="Bukti Pembayaran ' . e($p->nomor_pembayaran) . '" data-subtitle="' . e(basename($p->lampiran)) . '" style="font-size:0.68rem; font-weight:600;" title="Klik untuk Lihat Foto Bukti Pembayaran"><i class="bi bi-image me-0.5"></i> Foto</button>';
                    }

                    // Cantumkan badge tender jika ada faktur terkait tender
                    $tenders = collect();
                    if ($p->relationLoaded('details') || $p->details) {
                        foreach ($p->details as $det) {
                            $inv = $det->invoice;
                            if ($inv) {
                                $tObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                                if ($tObj) {
                                    $tNomor = !empty($tObj->nomor_kontrak) && $tObj->nomor_kontrak !== '-' 
                                        ? ($tObj->nomor_kontrak . (!empty($tObj->nama_tender) ? ' - ' . $tObj->nama_tender : '')) 
                                        : ($tObj->nomor_kontrak ?: ($tObj->nama_tender ?: ('Tender #' . $tObj->id)));
                                    if (!empty($tNomor)) {
                                        $tenders->push($tNomor);
                                    }
                                }
                            }
                        }
                    }
                    $uniqueTenders = $tenders->unique();
                    if ($uniqueTenders->isNotEmpty()) {
                        foreach ($uniqueTenders as $tLabel) {
                            $html .= '<div class="mt-1"><span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;" title="Terkait Proyek Tender"><i class="bi bi-briefcase me-1"></i>' . e($tLabel) . '</span></div>';
                        }
                    }

                    return $html;
                })
                ->addColumn('tanggal_bayar_fmt', fn($p) => $p->tanggal_bayar ? $p->tanggal_bayar->format('d/m/Y') : '-')
                ->addColumn('supplier_name', fn($p) => $p->supplier?->nama_supplier ?? '-')
                ->addColumn('kas_bank_name', function ($p) {
                    if (!$p->kasBank) return '-';
                    $tipeBadge = $p->kasBank->tipe === 'Kas Tunai' ? 'text-success' : 'text-primary';
                    return '<div class="fw-semibold">' . e($p->kasBank->nama_akun) . '</div>'
                        . '<small class="text-muted"><i class="bi bi-wallet2 ' . $tipeBadge . ' me-1"></i>' . e($p->kasBank->nomor_rekening ?? 'Kas Tunai') . '</small>';
                })
                ->addColumn('payment_method_name', fn($p) => $p->paymentMethod?->nama_metode ?? 'Transfer Bank')
                ->addColumn('total_bayar_fmt', function ($p) {
                    $curCode = $p->currency?->kode_currency ?? 'IDR';
                    $curSymbol = $p->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
                    $kurs = (float) ($p->kurs ?? 1);
                    $totVal = (float) $p->total_bayar;

                    if ($curCode !== 'IDR' && $kurs > 1) {
                        $idrEquivalent = $totVal * $kurs;
                        return '<div class="fw-bold text-dark">' . e($curSymbol) . ' ' . number_format($totVal, 2, ',', '.') . '</div>'
                            . '<small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-arrow-left-right me-0.5"></i>≈ Rp ' . number_format($idrEquivalent, 2, ',', '.') . '</small>';
                    }
                    return '<span class="fw-bold text-dark">Rp ' . number_format($totVal, 2, ',', '.') . '</span>';
                })
                ->addColumn('total_potongan_fmt', function ($p) {
                    $totPot = (float) $p->total_potongan;
                    if ($totPot <= 0) return '<span class="text-muted">-</span>';

                    $curCode = $p->currency?->kode_currency ?? 'IDR';
                    $curSymbol = $p->currency?->simbol ?? ($curCode === 'IDR' ? 'Rp' : $curCode);
                    $kurs = (float) ($p->kurs ?? 1);
                    $coaSub = $p->potonganCoa ? '<small class="text-muted d-block text-truncate" style="font-size: 0.7rem; max-width: 140px;" title="' . e($p->potonganCoa->code . ' - ' . $p->potonganCoa->name) . '"><i class="bi bi-tag me-0.5"></i>' . e($p->potonganCoa->code) . '</small>' : '';

                    if ($curCode !== 'IDR' && $kurs > 1) {
                        $idrEquivalent = $totPot * $kurs;
                        return '<div class="text-danger fw-semibold">' . e($curSymbol) . ' ' . number_format($totPot, 2, ',', '.') . '</div>'
                            . '<small class="text-muted" style="font-size: 0.72rem;">≈ Rp ' . number_format($idrEquivalent, 2, ',', '.') . '</small>'
                            . $coaSub;
                    }
                    return '<span class="text-danger fw-semibold">Rp ' . number_format($totPot, 2, ',', '.') . '</span>' . $coaSub;
                })
                ->addColumn('status_badge', function ($p) {
                    if ($p->status === 'Draft') {
                        return '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } elseif ($p->status === 'Diajukan') {
                        return '<span class="badge bg-primary text-white border border-primary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-clock-history me-1"></i>Diajukan</span>';
                    } elseif ($p->status === 'Ditolak') {
                        return '<span class="badge bg-danger text-white border border-danger rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>';
                    } elseif ($p->status === 'Posted') {
                        return '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Diposting</span>';
                    } elseif ($p->status === 'Cancelled') {
                        return '<span class="badge bg-secondary text-white border border-secondary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-slash-circle me-1"></i>Dibatalkan</span>';
                    }
                    return '<span class="badge bg-secondary rounded-pill px-3 py-1">' . $p->status . '</span>';
                })
                ->addColumn('action', function ($p) {
                    $user = auth()->user();
                    $isSuper = $user->hasAnyRole(['Super Admin', 'Owner']);
                    $canDetail  = $isSuper || $user->can('detail-pembayaran-utang');
                    $canCreate  = $isSuper || $user->can('tambah-pembayaran-utang');
                    $canEdit    = $isSuper || $user->can('edit-pembayaran-utang');
                    $canSubmit  = $canCreate || $canEdit;
                    $canApprove = $isSuper || $user->can('approve-pembayaran-utang');
                    $canReject  = $canApprove;
                    $canDelete  = $isSuper || $user->can('hapus-pembayaran-utang');
                    $canCancel  = $isSuper || $user->can('batalkan-pembayaran-utang');
                    $canCetak   = $isSuper || $user->can('cetak-pembayaran-utang');

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';

                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-circle btn-view-payment" data-id="' . $p->id . '" title="Lihat Detail"><i class="bi bi-eye"></i></button>';
                    }

                    if ($canCetak) {
                        $btn .= '<a href="' . route('purchase-payments.pdf', $p->id) . '" target="_blank" class="btn btn-sm btn-icon btn-outline-secondary rounded-circle" title="Cetak Bukti Pembayaran"><i class="bi bi-printer"></i></a>';
                    }

                    if ($p->status === 'Draft') {
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-payment" data-id="' . $p->id . '" title="Edit Draft"><i class="bi bi-pencil"></i></button>';
                        }
                        if ($canSubmit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle btn-submit-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Ajukan untuk Persetujuan"><i class="bi bi-send"></i></button>';
                        }
                        if ($canDelete) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Hapus Draft"><i class="bi bi-trash"></i></button>';
                        }
                    } elseif ($p->status === 'Diajukan') {
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-payment" data-id="' . $p->id . '" title="Edit Pengajuan"><i class="bi bi-pencil"></i></button>';
                        }
                        if ($canApprove) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-circle btn-approve-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Setujui & Posting"><i class="bi bi-check-lg"></i></button>';
                        }
                        if ($canReject) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-reject-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Tolak Pengajuan"><i class="bi bi-x-lg"></i></button>';
                        }
                    } elseif ($p->status === 'Ditolak') {
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-payment" data-id="' . $p->id . '" title="Edit & Perbaiki"><i class="bi bi-pencil"></i></button>';
                        }
                        if ($canSubmit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle btn-submit-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Ajukan Ulang"><i class="bi bi-arrow-repeat"></i></button>';
                        }
                        if ($canDelete) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Hapus"><i class="bi bi-trash"></i></button>';
                        }
                    } elseif ($p->status === 'Posted') {
                        // Tombol batalkan transaksi (void) dipindahkan ke dalam Modal Detail agar lebih tersembunyi & aman
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_pembayaran', 'kas_bank_name', 'total_bayar_fmt', 'total_potongan_fmt', 'status_badge', 'action'])
                ->make(true);
        }

        // Dropdown Data (Strict Multi-Tenant Scoped)
        $suppliers = MasterSupplier::forActiveCompany()
            ->active()
            ->with(['primaryBank', 'banks'])
            ->orderBy('nama_supplier')
            ->get();
        $kasBanks = KasBank::with('coa')
            ->forActiveCompany()
            ->where('is_active', true)
            ->orderBy('tipe')
            ->orderBy('nama_akun')
            ->get();

        $paymentMethods = PaymentMethod::forActiveCompany()->where('status', 'Active')->orderBy('nama_metode')->get();
        $currencies = MasterCurrency::where('status', 'Active')->orderBy('kode_currency')->get();

        // ── KPI Summary Bento Cards (Menggunakan shared helper untuk konsistensi & performa tinggi) ──
        $kpiData = $this->getKpiSummaryData($activeBranchId, $activeCompanyId, $userId);
        extract($kpiData);

        // Pass permission flags to the view for conditional UI rendering
        $authUser = auth()->user();
        $isSuper  = $authUser->hasAnyRole(['Super Admin', 'Owner']);
        $permissions = [
            'canView'       => $isSuper || $authUser->can('lihat-pembayaran-utang'),
            'canCreate'     => $isSuper || $authUser->can('tambah-pembayaran-utang'),
            'canDetail'     => $isSuper || $authUser->can('detail-pembayaran-utang'),
            'canEdit'       => $isSuper || $authUser->can('edit-pembayaran-utang'),
            'canSubmit'     => $isSuper || $authUser->can('tambah-pembayaran-utang') || $authUser->can('edit-pembayaran-utang'),
            'canApprove'    => $isSuper || $authUser->can('approve-pembayaran-utang'),
            'canReject'     => $isSuper || $authUser->can('approve-pembayaran-utang'),
            'canPost'       => $isSuper || $authUser->can('approve-pembayaran-utang'),
            'canDelete'     => $isSuper || $authUser->can('hapus-pembayaran-utang'),
            'canCancel'     => $isSuper || $authUser->can('batalkan-pembayaran-utang'),
            'canViewJurnal' => $isSuper || $authUser->can('lihat-jurnal-pembayaran-utang'),
            'canCetak'      => $isSuper || $authUser->can('cetak-pembayaran-utang'),
        ];

        // Akun COA Potongan Pembelian
        $potonganCoas = ChartOfAccount::forActiveCompany()
            ->detailOnly()
            ->active()
            ->orderBy('code')
            ->get();

        $defaultPotonganCoaId = AccountMappingHelper::getCoaId('purchases', 'purchase_discount', 'default_purchase_discount', $activeCompanyId)
            ?? AccountMappingHelper::getCoaId('default', 'purchase_discount')
            ?? AccountMappingHelper::getCoaId('default', 'potongan_pembelian');
        if (!$defaultPotonganCoaId) {
            $defaultPotonganCoa = $potonganCoas->first(function($c) {
                return str_contains(strtolower($c->name), 'potongan pembelian') 
                    || str_contains(strtolower($c->name), 'diskon pembelian');
            });
            $defaultPotonganCoaId = $defaultPotonganCoa?->id;
        }

        return view('modul_purchasing.pembayaran_utang.index', compact(
            'suppliers',
            'kasBanks',
            'paymentMethods',
            'currencies',
            'potonganCoas',
            'defaultPotonganCoaId',
            'totalPaymentIdrThisMonth',
            'totalPaymentUsdThisMonth',
            'totalPaymentEquivalentIdrThisMonth',
            'totalOutstandingApIdr',
            'totalOutstandingApUsd',
            'totalOutstandingApEquivalentIdr',
            'totalOverdueCount',
            'overdueIdrAmount',
            'overdueUsdAmount',
            'totalPaymentCount',
            'totalDraftCount',
            'totalPendingCount',
            'totalRejectedCount',
            'totalPostedCount',
            'totalCancelledCount',
            'permissions'
        ));
    }

    /**
     * Ambil daftar ID Faktur Pembelian yang sedang aktif diinput pada dokumen Pembayaran Utang
     * yang masih berjalan (Status Draft, Diajukan, Disetujui, Ditolak).
     *
     * Hal ini mencegah duplikasi input faktur yang sama di beberapa dokumen draft/pengajuan.
     * Untuk faktur yang sudah diposting (Posted) namun BELUM LUNAS (pembayaran parsial/cicilan),
     * faktur TETAP TAMPIL dengan sisa utang terkini, selama tidak sedang tersangkut di draft lain.
     *
     * @param int|null $excludePaymentId ID pembayaran yang diabaikan (misal saat edit pembayaran itu sendiri)
     * @return array
     */
    protected function getOccupiedInvoiceIds(?int $excludePaymentId = null): array
    {
        return PurchasePaymentDetail::whereHas('payment', function ($q) use ($excludePaymentId) {
            $q->whereNull('purchase_payments.deleted_at')
              ->whereIn('purchase_payments.status', ['Draft', 'Diajukan', 'Disetujui', 'Ditolak']);
            if (!empty($excludePaymentId)) {
                $q->where('purchase_payments.id', '!=', $excludePaymentId);
            }
        })
        ->where(function ($d) {
            $d->where('jumlah_bayar', '>', 0)
              ->orWhere('potongan', '>', 0);
        })
        ->pluck('purchase_invoice_id')
        ->filter()
        ->unique()
        ->values()
        ->toArray();
    }

    /**
     * AJAX Endpoint: Pencarian faktur pembelian belum lunas (Search & Lookup)
     */
    public function lookupInvoices(Request $request)
    {
        abort_unless(
            auth()->user()->can('lihat-pembayaran-utang') || 
            auth()->user()->can('tambah-pembayaran-utang') || 
            auth()->user()->can('edit-pembayaran-utang') ||
            auth()->user()->hasAnyRole(['Super Admin', 'Owner']),
            403,
            'Anda tidak memiliki izin untuk melihat daftar faktur pembayaran utang.'
        );

        $activeBranchId  = session('active_branch_id');

        $query = PurchaseInvoice::query()
            ->with([
                'supplier',
                'po.paymentMethod',
                'paymentTerm',
                'currency',
                'tender',
                'po.tender',
                'goodsReceipt.tender'
            ])
            ->where('status', 'Posted')
            ->forActiveBranch()
            ->forActiveCompany();

        // Eksklusi faktur yang sudah diinput ke pembayaran aktif (Draft, Diajukan, Posted, dll.)
        // Faktur hanya muncul kembali jika status pembayaran dibatalkan (Cancelled) atau dihapus
        $excludePaymentId = $request->input('exclude_payment_id');
        $occupiedInvoiceIds = $this->getOccupiedInvoiceIds($excludePaymentId);
        if (!empty($occupiedInvoiceIds)) {
            $query->whereNotIn('purchase_invoices.id', $occupiedInvoiceIds);
        }

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($sq) use ($term) {
                $sq->where('nomor_faktur', 'like', "%{$term}%")
                   ->orWhere('nomor_faktur_supplier', 'like', "%{$term}%")
                   ->orWhere('keterangan', 'like', "%{$term}%")
                   ->orWhereHas('supplier', function ($supQuery) use ($term) {
                       $supQuery->where('nama_supplier', 'like', "%{$term}%")
                                ->orWhere('kode_supplier', 'like', "%{$term}%");
                   })
                   ->orWhereHas('tender', function ($tQ) use ($term) {
                       $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                          ->orWhere('nama_tender', 'like', "%{$term}%");
                   })
                   ->orWhereHas('po.tender', function ($tQ) use ($term) {
                       $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                          ->orWhere('nama_tender', 'like', "%{$term}%");
                   })
                   ->orWhereHas('goodsReceipt.tender', function ($tQ) use ($term) {
                       $tQ->where('nomor_kontrak', 'like', "%{$term}%")
                          ->orWhere('nama_tender', 'like', "%{$term}%");
                   });
            });
        }

        $paidSub = DB::table('purchase_payment_details as ppd')
            ->join('purchase_payments as pp', function($j) {
                $j->on('pp.id', '=', 'ppd.purchase_payment_id')
                  ->where('pp.status', '=', 'Posted')
                  ->whereNull('pp.deleted_at');
            })
            ->select('ppd.purchase_invoice_id', DB::raw('SUM(ppd.jumlah_bayar + ppd.potongan) as total_paid'))
            ->groupBy('ppd.purchase_invoice_id');

        $query->leftJoinSub($paidSub, 'paid_sub', function($j) {
            $j->on('paid_sub.purchase_invoice_id', '=', 'purchase_invoices.id');
        })
        ->select('purchase_invoices.*', DB::raw('COALESCE(paid_sub.total_paid, 0) as total_pelunasan'))
        ->whereRaw('(purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(paid_sub.total_paid, 0)) > 0.01');

        $invoices = $query->orderBy('purchase_invoices.tanggal_jatuh_tempo', 'asc')
            ->orderBy('purchase_invoices.tanggal_faktur', 'asc')
            ->limit(100)
            ->get();

        $today = now()->startOfDay();
        $result = [];

        foreach ($invoices as $inv) {
            $dp = (float) ($inv->uang_muka ?? 0);
            $pelunasan = (float) ($inv->total_pelunasan ?? 0);
            $paid = $dp + $pelunasan;
            $grandTotal = (float) $inv->grand_total;
            $remaining = max(0, $grandTotal - $paid);

            if ($remaining > 0.01) {
                $dueDate = $inv->tanggal_jatuh_tempo;
                $isOverdue = $dueDate && $dueDate->lt($today);
                $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                $curSym = $inv->currency?->simbol ?? ($inv->currency?->kode_currency === 'IDR' ? 'Rp' : '$');
                $curCode = $inv->currency?->kode_currency ?? 'IDR';

                $tenderObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                $nomorTender = null;
                if ($tenderObj) {
                    $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                        ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                        : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
                }

                $dp = (float) ($inv->uang_muka ?? 0);
                $pelunasan = max(0, $paid - $dp);

                $result[] = [
                    'id'                     => $inv->id,
                    'supplier_id'            => $inv->supplier_id,
                    'supplier_name'          => $inv->supplier?->nama_supplier ?? '-',
                    'supplier_code'          => $inv->supplier?->kode_supplier ?? '-',
                    'nomor_faktur'           => $inv->nomor_faktur,
                    'nomor_faktur_supplier'  => $inv->nomor_faktur_supplier ?? '-',
                    'tender_id'              => $tenderObj?->id,
                    'nomor_tender'           => $nomorTender,
                    'nama_tender'            => $tenderObj?->nama_tender,
                    'tanggal_faktur'         => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                    'tanggal_jatuh_tempo'    => $dueDate ? $dueDate->format('d/m/Y') : '-',
                    'is_overdue'             => $isOverdue,
                    'days_overdue'           => $daysOverdue,
                    'grand_total'            => $grandTotal,
                    'grand_total_fmt'        => $curSym . ' ' . number_format($grandTotal, 2, ',', '.'),
                    'uang_muka'              => $dp,
                    'uang_muka_fmt'          => $curSym . ' ' . number_format($dp, 2, ',', '.'),
                    'pelunasan_terbayar'     => $pelunasan,
                    'pelunasan_terbayar_fmt' => $curSym . ' ' . number_format($pelunasan, 2, ',', '.'),
                    'paid_amount'            => $paid,
                    'paid_amount_fmt'        => $curSym . ' ' . number_format($paid, 2, ',', '.'),
                    'remaining_balance'      => $remaining,
                    'remaining_balance_fmt'  => $curSym . ' ' . number_format($remaining, 2, ',', '.'),
                    'currency_id'            => $inv->currency_id,
                    'currency_code'          => $curCode,
                    'currency_symbol'        => $curSym,
                    'kurs'                   => (float) ($inv->kurs ?? 1),
                    'payment_method_id'      => $inv->po?->payment_method_id,
                    'payment_method_name'    => $inv->po?->paymentMethod?->nama_metode,
                ];
            }
        }

        return response()->json([
            'status'   => 'success',
            'count'    => count($result),
            'invoices' => $result,
        ]);
    }

    /**
     * AJAX Endpoint: Ambil daftar faktur pembelian belum lunas milik Supplier
     */
    public function getUnpaidInvoices($supplierId)
    {
        abort_unless(
            auth()->user()->can('lihat-pembayaran-utang') || 
            auth()->user()->can('tambah-pembayaran-utang') || 
            auth()->user()->can('edit-pembayaran-utang') ||
            auth()->user()->hasAnyRole(['Super Admin', 'Owner']),
            403,
            'Anda tidak memiliki izin untuk melihat daftar faktur pembayaran utang.'
        );

        $activeBranchId = session('active_branch_id');

        $query = PurchaseInvoice::query()
            ->with([
                'supplier',
                'po.paymentMethod',
                'paymentTerm',
                'currency',
                'tender',
                'po.tender',
                'goodsReceipt.tender'
            ])
            ->where('supplier_id', $supplierId)
            ->where('status', 'Posted')
            ->forActiveBranch()
            ->forActiveCompany();

        // Eksklusi faktur yang sudah diinput ke pembayaran aktif (Draft, Diajukan, Posted, dll.)
        // Faktur hanya muncul kembali jika status pembayaran dibatalkan (Cancelled) atau dihapus
        $excludePaymentId = request('exclude_payment_id');
        $occupiedInvoiceIds = $this->getOccupiedInvoiceIds($excludePaymentId);
        if (!empty($occupiedInvoiceIds)) {
            $query->whereNotIn('purchase_invoices.id', $occupiedInvoiceIds);
        }

        $paidSub = DB::table('purchase_payment_details as ppd')
            ->join('purchase_payments as pp', function($j) {
                $j->on('pp.id', '=', 'ppd.purchase_payment_id')
                  ->where('pp.status', '=', 'Posted')
                  ->whereNull('pp.deleted_at');
            })
            ->select('ppd.purchase_invoice_id', DB::raw('SUM(ppd.jumlah_bayar + ppd.potongan) as total_paid'))
            ->groupBy('ppd.purchase_invoice_id');

        $query->leftJoinSub($paidSub, 'paid_sub', function($j) {
            $j->on('paid_sub.purchase_invoice_id', '=', 'purchase_invoices.id');
        })
        ->select('purchase_invoices.*', DB::raw('COALESCE(paid_sub.total_paid, 0) as total_pelunasan'))
        ->whereRaw('(purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(paid_sub.total_paid, 0)) > 0.01');

        $invoices = $query->orderBy('purchase_invoices.tanggal_jatuh_tempo', 'asc')
            ->orderBy('purchase_invoices.tanggal_faktur', 'asc')
            ->get();

        $today = now()->startOfDay();
        $result = [];

        foreach ($invoices as $inv) {
            $dp = (float) ($inv->uang_muka ?? 0);
            $pelunasan = (float) ($inv->total_pelunasan ?? 0);
            $paid = $dp + $pelunasan;
            $grandTotal = (float) $inv->grand_total;
            $remaining = max(0, $grandTotal - $paid);

            if ($remaining > 0.01) {
                $dueDate = $inv->tanggal_jatuh_tempo;
                $isOverdue = $dueDate && $dueDate->lt($today);
                $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                $curSym = $inv->currency?->simbol ?? ($inv->currency?->kode_currency === 'IDR' ? 'Rp' : '$');
                $curCode = $inv->currency?->kode_currency ?? 'IDR';

                $tenderObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                $nomorTender = null;
                if ($tenderObj) {
                    $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                        ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                        : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
                }

                $dp = (float) ($inv->uang_muka ?? 0);
                $pelunasan = max(0, $paid - $dp);

                $result[] = [
                    'id'                     => $inv->id,
                    'nomor_faktur'           => $inv->nomor_faktur,
                    'nomor_faktur_supplier'  => $inv->nomor_faktur_supplier ?? '-',
                    'tender_id'              => $tenderObj?->id,
                    'nomor_tender'           => $nomorTender,
                    'nama_tender'            => $tenderObj?->nama_tender,
                    'tanggal_faktur'         => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                    'tanggal_jatuh_tempo'    => $dueDate ? $dueDate->format('d/m/Y') : '-',
                    'is_overdue'             => $isOverdue,
                    'days_overdue'           => $daysOverdue,
                    'grand_total'            => $grandTotal,
                    'grand_total_fmt'        => $curSym . ' ' . number_format($grandTotal, 2, ',', '.'),
                    'uang_muka'              => $dp,
                    'uang_muka_fmt'          => $curSym . ' ' . number_format($dp, 2, ',', '.'),
                    'pelunasan_terbayar'     => $pelunasan,
                    'pelunasan_terbayar_fmt' => $curSym . ' ' . number_format($pelunasan, 2, ',', '.'),
                    'paid_amount'            => $paid,
                    'paid_amount_fmt'        => $curSym . ' ' . number_format($paid, 2, ',', '.'),
                    'remaining_balance'      => $remaining,
                    'remaining_balance_fmt'  => $curSym . ' ' . number_format($remaining, 2, ',', '.'),
                    'currency_id'            => $inv->currency_id,
                    'currency_code'          => $curCode,
                    'currency_symbol'        => $curSym,
                    'kurs'                   => (float) ($inv->kurs ?? 1),
                    'payment_method_id'      => $inv->po?->payment_method_id,
                    'payment_method_name'    => $inv->po?->paymentMethod?->nama_metode,
                ];
            }
        }

        return response()->json([
            'status'   => 'success',
            'count'    => count($result),
            'invoices' => $result,
        ]);
    }

    /**
     * Generate Nomor Pembayaran Otomatis PAY/YYYY/MM/XXXX
     */
    public function generateCode()
    {
        $year   = date('Y');
        $month  = date('m');
        $prefix = "PAY/{$year}/{$month}/";

        $latest = PurchasePayment::withTrashed()
            ->where('nomor_pembayaran', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($latest && preg_match('/(\d+)$/', $latest->nomor_pembayaran, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        $code = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        while (PurchasePayment::withTrashed()->where('nomor_pembayaran', $code)->exists()) {
            $nextNum++;
            $code = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        return response()->json(['code' => $code]);
    }

    /**
     * Simpan Pembayaran Utang (Draft atau Langsung Post)
     */
    public function store(Request $request)
    {
        abort_unless(
            auth()->user()->can('tambah-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk membuat pembayaran utang.'
        );

        $request->validate([
            'supplier_id'       => 'required|exists:master_suppliers,id',
            'kas_bank_id'       => 'required|exists:kas_banks,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'currency_id'       => 'nullable|exists:master_currencies,id',
            'kurs'              => 'nullable|numeric|gt:0',
            'tanggal_bayar'     => 'required|date',
            'nomor_referensi'   => 'nullable|string|max:30',
            'keterangan'        => 'nullable|string|max:40',
            'lampiran'          => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
            'invoices'          => 'required|array|min:1',
            'invoices.*.purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'invoices.*.jumlah_bayar'        => 'required|numeric|min:0',
            'invoices.*.potongan'            => 'nullable|numeric|min:0',
            'invoices.*.catatan'             => 'nullable|string|max:40',
            'potongan_coa_id'                => 'nullable|exists:chart_of_accounts,id',
        ], [
            'supplier_id.required'   => 'Pilih Supplier yang akan dibayar!',
            'kas_bank_id.required'   => 'Pilih Akun Kas / Bank sumber dana pembayaran!',
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi!',
            'nomor_referensi.max'    => 'Nomor referensi / giro maksimal 30 karakter!',
            'keterangan.max'         => 'Keterangan / catatan pembayaran maksimal 40 karakter!',
            'invoices.*.catatan.max' => 'Catatan per faktur maksimal 40 karakter!',
            'invoices.required'      => 'Pilih minimal satu faktur yang akan dibayar!',
            'invoices.min'           => 'Pilih minimal satu faktur yang akan dibayar!',
            'lampiran.required'      => 'Dokumen bukti pembayaran wajib diisi / diunggah!',
            'lampiran.mimes'         => 'Format file lampiran harus JPG, PNG, WEBP, atau PDF!',
            'lampiran.max'           => 'Ukuran file lampiran maksimal 10MB!',
            'kurs.gt'                => 'Nilai kurs harus lebih besar dari 0!',
        ]);

        try {
            DB::beginTransaction();

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

            // Custom Manual or Auto Generated Payment Number
            if ($request->filled('nomor_pembayaran')) {
                $nomorPembayaran = trim($request->nomor_pembayaran);
                if (PurchasePayment::where('nomor_pembayaran', $nomorPembayaran)->exists()) {
                    DB::rollBack();
                    return response()->json(['message' => "Nomor Bukti Bayar '{$nomorPembayaran}' sudah terdaftar! Silakan gunakan nomor lain."], 422);
                }
            } else {
                $year   = date('Y', strtotime($request->tanggal_bayar));
                $month  = date('m', strtotime($request->tanggal_bayar));
                $prefix = "PAY/{$year}/{$month}/";

                $latest = PurchasePayment::withTrashed()
                    ->where('nomor_pembayaran', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/(\d+)$/', $latest->nomor_pembayaran, $matches)) {
                    $nextNum = ((int) $matches[1]) + 1;
                }

                $nomorPembayaran = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                while (PurchasePayment::withTrashed()->where('nomor_pembayaran', $nomorPembayaran)->exists()) {
                    $nextNum++;
                    $nomorPembayaran = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                }
            }

            // Validasi Anti-Duplikasi Faktur:
            // Pastikan faktur belum diinput ke pembayaran lain yang aktif (Draft/Diajukan/Posted)
            $selectedInvoiceIds = collect($request->invoices)
                ->filter(fn($row) => (floatval($row['jumlah_bayar'] ?? 0) + floatval($row['potongan'] ?? 0)) > 0)
                ->pluck('purchase_invoice_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $occupiedIds = $this->getOccupiedInvoiceIds();
            $duplicateIds = array_intersect($selectedInvoiceIds, $occupiedIds);
            if (!empty($duplicateIds)) {
                $duplicateInvoices = PurchaseInvoice::whereIn('id', $duplicateIds)->pluck('nomor_faktur')->implode(', ');
                DB::rollBack();
                return response()->json([
                    'message' => "Faktur [{$duplicateInvoices}] sedang dalam proses pembayaran lain (berstatus Draft/Diajukan). Faktur tidak dapat diduplikasi kecuali draf sebelumnya dibatalkan atau dihapus."
                ], 422);
            }

            // Calculate totals & prepare details
            $totalFaktur   = 0;
            $totalPotongan = 0;
            $totalBayar    = 0;
            $detailsPayload = [];

            foreach ($request->invoices as $row) {
                $invId   = $row['purchase_invoice_id'];
                $bayar   = floatval($row['jumlah_bayar'] ?? 0);
                $potong  = floatval($row['potongan'] ?? 0);

                if (($bayar + $potong) <= 0) {
                    continue; // Skip zero payment rows
                }

                $invoice = PurchaseInvoice::lockForUpdate()->find($invId);
                if (!$invoice) continue;

                // 1. Validasi Kepemilikan Supplier: Mencegah manipulasi ID faktur milik vendor lain
                if ((int)$invoice->supplier_id !== (int)$request->supplier_id) {
                    DB::rollBack();
                    return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' bukan milik supplier yang dipilih!"], 422);
                }

                // 2. Strict Tenancy Isolation: Memastikan faktur milik Cabang / PT yang aktif
                if (!empty($activeBranchId)) {
                    if ((int)$invoice->branch_id !== (int)$activeBranchId) {
                        DB::rollBack();
                        return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' tidak berasal dari cabang yang aktif!"], 403);
                    }
                } else {
                    if (!empty($invoice->branch_id) || ($activeCompanyId && $invoice->company_id && (int)$invoice->company_id !== (int)$activeCompanyId)) {
                        DB::rollBack();
                        return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' tidak berasal dari entitas perusahaan yang aktif!"], 403);
                    }
                }

                // 3. Validasi Status Faktur
                if ($invoice->status !== 'Posted') {
                    DB::rollBack();
                    return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' belum diposting atau sudah tidak aktif!"], 422);
                }

                $curRemaining = (float) $invoice->remaining_balance;

                // 4. Anti-Overpayment Guard (Maksimal Keamanan Akuntansi)
                if (($bayar + $potong) > ($curRemaining + 0.05)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Nominal alokasi bayar & potongan untuk faktur '{$invoice->nomor_faktur}' melebihi sisa utang yang belum dibayar!"
                    ], 422);
                }

                $totFaktur    = floatval($invoice->grand_total);
                $sisaAkhir    = max(0, $curRemaining - ($bayar + $potong));

                $totalFaktur   += $totFaktur;
                $totalPotongan += $potong;
                $totalBayar    += $bayar;

                $detailsPayload[] = [
                    'purchase_invoice_id' => $invoice->id,
                    'total_faktur'        => $totFaktur,
                    'sisa_utang'          => $curRemaining,
                    'jumlah_bayar'        => $bayar,
                    'potongan'            => $potong,
                    'sisa_akhir'          => $sisaAkhir,
                    'catatan'             => $row['catatan'] ?? null,
                ];
            }

            if (empty($detailsPayload) || $totalBayar <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Total pembayaran harus lebih besar dari 0!'], 422);
            }

            // Validasi Akun COA Potongan Pembelian jika terdapat potongan
            if ($totalPotongan > 0 && empty($request->potongan_coa_id)) {
                $fallbackCoa = AccountMappingHelper::getCoaId('purchases', 'purchase_discount', 'default_purchase_discount', $activeCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'purchase_discount', null, $activeCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'potongan_pembelian', null, $activeCompanyId)
                    ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                        $q->where('name', 'like', '%Potongan Pembelian%')->orWhere('name', 'like', '%Diskon Pembelian%');
                    })->value('id');

                if (!$fallbackCoa) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun COA Potongan Pembelian wajib dipilih jika terdapat potongan atau diskon!'], 422);
                }
            }

            // Saldo Kas/Bank Security Check (Anti-Minus Validation & Tenancy Verification):
            // Transaksi TIDAK BISA disimpan baik sebagai draf maupun diajukan jika saldo kas/bank tidak mencukupi
            $kasBank = KasBank::lockForUpdate()->find($request->kas_bank_id);
            if (!$kasBank) {
                DB::rollBack();
                return response()->json(['message' => 'Akun Kas / Bank sumber dana tidak ditemukan!'], 422);
            }

            // Verifikasi Tenancy Akun Kas/Bank
            if (!empty($activeBranchId)) {
                if ((int)$kasBank->branch_id !== (int)$activeBranchId) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun Kas / Bank tidak sesuai dengan cabang yang sedang aktif.'], 403);
                }
            } else {
                if (!empty($kasBank->branch_id) || ($activeCompanyId && $kasBank->company_id && (int)$kasBank->company_id !== (int)$activeCompanyId)) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun Kas / Bank tidak sesuai dengan entitas perusahaan yang aktif.'], 403);
                }
            }

            $kurs = (float) ($request->kurs ?? 1);
            $totalBayarIdr = $totalBayar * ($kurs > 0 ? $kurs : 1);
            $currentSaldo  = (float) $kasBank->saldo_berjalan;
            if ($currentSaldo < $totalBayarIdr) {
                $kekurangan = $totalBayarIdr - $currentSaldo;
                DB::rollBack();
                return response()->json([
                    'message' => "Saldo Akun Kas/Bank '{$kasBank->nama_akun}' tidak mencukupi! Saldo tersedia: Rp " . number_format($currentSaldo, 2, ',', '.') . ", Nominal pembayaran: Rp " . number_format($totalBayarIdr, 2, ',', '.') . " (Kurang: Rp " . number_format($kekurangan, 2, ',', '.') . "). Transaksi tidak dapat disimpan baik sebagai draf maupun diajukan demi keamanan saldo."
                ], 422);
            }

            $status = 'Draft';
            $submittedBy = null;
            $submittedAt = null;
            $postedBy = null;
            $postedAt = null;

            if ($request->action_type === 'submit' || $request->action_type === 'ajukan') {
                $status      = 'Diajukan';
                $submittedBy = auth()->id();
                $submittedAt = now();
            } elseif ($request->action_type === 'post') {
                // Hanya jika punya izin posting / approve
                if (auth()->user()->can('posting-pembayaran-utang') || auth()->user()->can('approve-pembayaran-utang')) {
                    $status   = 'Posted';
                    $postedBy = auth()->id();
                    $postedAt = now();
                } else {
                    $status      = 'Diajukan';
                    $submittedBy = auth()->id();
                    $submittedAt = now();
                }
            }

            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $this->handleImageUpload($request->file('lampiran'));
            }

            $payment = PurchasePayment::create([
                'nomor_pembayaran'  => $nomorPembayaran,
                'tanggal_bayar'     => $request->tanggal_bayar,
                'supplier_id'       => $request->supplier_id,
                'kas_bank_id'       => $request->kas_bank_id,
                'payment_method_id' => $request->payment_method_id,
                'nomor_referensi'   => $request->nomor_referensi,
                'branch_id'         => !empty($activeBranchId) ? $activeBranchId : null,
                'company_id'        => !empty($activeBranchId) ? null : ($activeCompanyId ?: 1),
                'currency_id'       => $request->currency_id,
                'kurs'              => $request->kurs ?? 1,
                'total_faktur'      => $totalFaktur,
                'total_potongan'    => $totalPotongan,
                'potongan_coa_id'   => $totalPotongan > 0 ? $request->potongan_coa_id : null,
                'total_bayar'       => $totalBayar,
                'status'            => $status,
                'keterangan'        => $request->keterangan,
                'lampiran'          => $lampiranPath,
                'submitted_by'      => $submittedBy,
                'submitted_at'      => $submittedAt,
                'posted_by'         => $postedBy,
                'posted_at'         => $postedAt,
            ]);

            // Save details
            foreach ($detailsPayload as $d) {
                $d['purchase_payment_id'] = $payment->id;
                PurchasePaymentDetail::create($d);
            }

            // If Posting immediately
            if ($status === 'Posted') {
                $this->executePostingEffects($payment);
            }

            DB::commit();

            // Send WhatsApp Notification if submitted for approval
            if ($status === 'Diajukan') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendPaymentSubmissionNotification($payment)
                );
            }

            $successMessage = match ($status) {
                'Posted'   => "Pembayaran Utang {$payment->nomor_pembayaran} berhasil diposting & Jurnal/Kas Mutasi diterbitkan!",
                'Diajukan' => "Pembayaran Utang {$payment->nomor_pembayaran} berhasil diajukan untuk persetujuan!",
                default    => "Pembayaran Utang {$payment->nomor_pembayaran} berhasil disimpan sebagai Draft!",
            };

            return response()->json([
                'status'  => 'success',
                'message' => $successMessage,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Store Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'Gagal menyimpan Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tampilkan Detail Pembayaran Utang (JSON untuk Modal View)
     */
    public function show(PurchasePayment $purchasePayment)
    {
        $user    = auth()->user();
        $isSuper = $user->hasAnyRole(['Super Admin', 'Owner']);

        abort_unless(
            $isSuper || $user->can('detail-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk melihat rincian pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh user pembuat dokumennya (kecuali Super Admin / Owner)
        if ($purchasePayment->status === 'Draft' && !$isSuper && (int)$purchasePayment->created_by !== (int)$user->id) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat dilihat oleh pembuat dokumen ini.'
            ], 403);
        }

        $canSeeJurnal = $isSuper || $user->can('lihat-jurnal-pembayaran-utang');

        $relations = [
            'supplier',
            'kasBank.coa',
            'paymentMethod',
            'potonganCoa',
            'branch',
            'company',
            'currency',
            'creator',
            'poster',
            'updater',
            'canceller',
            'submitter',
            'approver',
            'rejecter',
            'details.invoice.po.currency',
            'details.invoice.tender',
            'details.invoice.po.tender',
            'details.invoice.goodsReceipt.tender',
            'kasMutasi',
        ];

        if ($canSeeJurnal) {
            $relations[] = 'jurnal.details.account';
            $relations[] = 'reversalJurnal.details.account';
        }

        $purchasePayment->load($relations);

        if (!$canSeeJurnal) {
            $purchasePayment->unsetRelation('jurnal');
            $purchasePayment->unsetRelation('reversalJurnal');
            $purchasePayment->setRelation('jurnal', null);
            $purchasePayment->setRelation('reversalJurnal', null);
        }

        return response()->json([
            'status'         => 'success',
            'payment'        => $purchasePayment,
            'can_see_jurnal' => $canSeeJurnal,
            'can_cancel'     => $isSuper || $user->can('batalkan-pembayaran-utang'),
            'can_delete'     => $isSuper || $user->can('hapus-pembayaran-utang'),
        ]);
    }

    /**
     * Tampilkan Data untuk Modal Edit (Draft & Ditolak)
     */
    public function edit(PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('edit-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk mengubah pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa diedit oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat diedit oleh pembuat dokumen ini.'
            ], 403);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Diajukan', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft, Diajukan, atau Ditolak yang dapat diedit!'], 400);
        }

        $purchasePayment->load([
            'details.invoice.tender',
            'details.invoice.po.tender',
            'details.invoice.goodsReceipt.tender',
            'supplier',
            'kasBank',
            'potonganCoa',
            'paymentMethod'
        ]);

        // Also fetch all available unpaid invoices of this supplier (excluding invoices in other active payments)
        $activeBranchId = session('active_branch_id');
        $occupiedByOthers = $this->getOccupiedInvoiceIds($purchasePayment->id);

        $allInvoicesQuery = PurchaseInvoice::query()
            ->with([
                'po.paymentMethod',
                'currency',
                'tender',
                'po.tender',
                'goodsReceipt.tender'
            ])
            ->where('supplier_id', $purchasePayment->supplier_id)
            ->where('status', 'Posted')
            ->forActiveBranch()
            ->forActiveCompany();

        if (!empty($occupiedByOthers)) {
            $allInvoicesQuery->whereNotIn('purchase_invoices.id', $occupiedByOthers);
        }

        $paidSub = DB::table('purchase_payment_details as ppd')
            ->join('purchase_payments as pp', function($j) use ($purchasePayment) {
                $j->on('pp.id', '=', 'ppd.purchase_payment_id')
                  ->where('pp.status', '=', 'Posted')
                  ->where('pp.id', '!=', $purchasePayment->id)
                  ->whereNull('pp.deleted_at');
            })
            ->select('ppd.purchase_invoice_id', DB::raw('SUM(ppd.jumlah_bayar + ppd.potongan) as total_paid'))
            ->groupBy('ppd.purchase_invoice_id');

        $allInvoicesQuery->leftJoinSub($paidSub, 'paid_sub', function($j) {
            $j->on('paid_sub.purchase_invoice_id', '=', 'purchase_invoices.id');
        })
        ->select('purchase_invoices.*', DB::raw('COALESCE(paid_sub.total_paid, 0) as total_pelunasan_other'));

        $allInvoices = $allInvoicesQuery->get();

        $existingAllocations = $purchasePayment->details->keyBy('purchase_invoice_id');
        $invoiceList = [];

        foreach ($allInvoices as $inv) {
            $dp = (float) ($inv->uang_muka ?? 0);
            $paidWithoutThis = (float) ($inv->total_pelunasan_other ?? 0);

            $remWithoutThis = max(0, (float) $inv->grand_total - $dp - $paidWithoutThis);
            $alloc = $existingAllocations->get($inv->id);

            $tenderObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
            $nomorTender = null;
            if ($tenderObj) {
                $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
            }

            $curSym = $inv->currency?->simbol ?? ($inv->currency?->kode_currency === 'IDR' ? 'Rp' : '$');

            $invoiceList[] = [
                'id'                    => $inv->id,
                'nomor_faktur'          => $inv->nomor_faktur,
                'nomor_faktur_supplier' => $inv->nomor_faktur_supplier ?? '-',
                'tender_id'             => $tenderObj?->id,
                'nomor_tender'          => $nomorTender,
                'nama_tender'           => $tenderObj?->nama_tender,
                'tanggal_faktur'        => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                'tanggal_jatuh_tempo'   => $inv->tanggal_jatuh_tempo ? $inv->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                'grand_total'           => (float) $inv->grand_total,
                'grand_total_fmt'       => $curSym . ' ' . number_format($inv->grand_total, 2, ',', '.'),
                'uang_muka'             => $dp,
                'uang_muka_fmt'         => $curSym . ' ' . number_format($dp, 2, ',', '.'),
                'pelunasan_terbayar'    => $paidWithoutThis,
                'pelunasan_terbayar_fmt'=> $curSym . ' ' . number_format($paidWithoutThis, 2, ',', '.'),
                'paid_amount'           => $dp + $paidWithoutThis,
                'paid_amount_fmt'       => $curSym . ' ' . number_format($dp + $paidWithoutThis, 2, ',', '.'),
                'remaining_balance'     => $remWithoutThis,
                'remaining_balance_fmt' => $curSym . ' ' . number_format($remWithoutThis, 2, ',', '.'),
                'is_allocated'          => $alloc !== null,
                'allocated_bayar'       => $alloc ? (float) $alloc->jumlah_bayar : 0,
                'allocated_potongan'    => $alloc ? (float) $alloc->potongan : 0,
                'catatan'               => $alloc ? $alloc->catatan : '',
                'currency_id'           => $inv->currency_id,
                'kurs'                  => (float) ($inv->kurs ?? 1),
                'payment_method_id'     => $inv->po?->payment_method_id,
                'payment_method_name'   => $inv->po?->paymentMethod?->nama_metode,
            ];
        }

        return response()->json([
            'status'   => 'success',
            'payment'  => $purchasePayment,
            'invoices' => $invoiceList,
        ]);
    }

    /**
     * Update Pembayaran Utang (Draft & Ditolak)
     */
    public function update(Request $request, PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('edit-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk mengubah pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa diubah oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat diubah oleh pembuat dokumen ini.'
            ], 403);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Diajukan', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft, Diajukan, atau Ditolak yang dapat diedit!'], 400);
        }

        $request->validate([
            'supplier_id'       => 'required|exists:master_suppliers,id',
            'kas_bank_id'       => 'required|exists:kas_banks,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'currency_id'       => 'nullable|exists:master_currencies,id',
            'kurs'              => 'nullable|numeric|gt:0',
            'tanggal_bayar'     => 'required|date',
            'nomor_referensi'   => 'nullable|string|max:30',
            'keterangan'        => 'nullable|string|max:40',
            'lampiran'          => [
                Rule::requiredIf(function () use ($purchasePayment, $request) {
                    return empty($purchasePayment->lampiran) || $request->boolean('remove_lampiran');
                }),
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,pdf',
                'max:10240'
            ],
            'invoices'          => 'required|array|min:1',
            'invoices.*.purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'invoices.*.jumlah_bayar'        => 'required|numeric|min:0',
            'invoices.*.potongan'            => 'nullable|numeric|min:0',
            'invoices.*.catatan'             => 'nullable|string|max:40',
            'potongan_coa_id'                => 'nullable|exists:chart_of_accounts,id',
        ], [
            'supplier_id.required'   => 'Pilih Supplier yang akan dibayar!',
            'kas_bank_id.required'   => 'Pilih Akun Kas / Bank sumber dana pembayaran!',
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi!',
            'nomor_referensi.max'    => 'Nomor referensi / giro maksimal 30 karakter!',
            'keterangan.max'         => 'Keterangan / catatan pembayaran maksimal 40 karakter!',
            'invoices.*.catatan.max' => 'Catatan per faktur maksimal 40 karakter!',
            'invoices.required'      => 'Pilih minimal satu faktur yang akan dibayar!',
            'invoices.min'           => 'Pilih minimal satu faktur yang akan dibayar!',
            'lampiran.required'      => 'Dokumen bukti pembayaran wajib diisi / diunggah!',
            'lampiran.mimes'         => 'Format file lampiran harus JPG, PNG, WEBP, atau PDF!',
            'lampiran.max'           => 'Ukuran file lampiran maksimal 10MB!',
            'kurs.gt'                => 'Nilai kurs harus lebih besar dari 0!',
        ]);

        try {
            DB::beginTransaction();

            // Validasi Anti-Duplikasi Faktur:
            // Pastikan faktur yang dipilih belum diinput ke pembayaran lain yang aktif (Draft/Diajukan/Posted)
            $selectedInvoiceIds = collect($request->invoices)
                ->filter(fn($row) => (floatval($row['jumlah_bayar'] ?? 0) + floatval($row['potongan'] ?? 0)) > 0)
                ->pluck('purchase_invoice_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $occupiedIds = $this->getOccupiedInvoiceIds($purchasePayment->id);
            $duplicateIds = array_intersect($selectedInvoiceIds, $occupiedIds);
            if (!empty($duplicateIds)) {
                $duplicateInvoices = PurchaseInvoice::whereIn('id', $duplicateIds)->pluck('nomor_faktur')->implode(', ');
                DB::rollBack();
                return response()->json([
                    'message' => "Faktur [{$duplicateInvoices}] sedang dalam proses pembayaran lain (berstatus Draft/Diajukan). Faktur tidak dapat diduplikasi kecuali draf sebelumnya dibatalkan atau dihapus."
                ], 422);
            }

            $totalFaktur   = 0;
            $totalPotongan = 0;
            $totalBayar    = 0;
            $detailsPayload = [];

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

            foreach ($request->invoices as $row) {
                $invId  = $row['purchase_invoice_id'];
                $bayar  = floatval($row['jumlah_bayar'] ?? 0);
                $potong = floatval($row['potongan'] ?? 0);

                if (($bayar + $potong) <= 0) continue;

                $invoice = PurchaseInvoice::lockForUpdate()->find($invId);
                if (!$invoice) continue;

                // 1. Validasi Kepemilikan Supplier: Mencegah manipulasi ID faktur milik vendor lain
                if ((int)$invoice->supplier_id !== (int)$request->supplier_id) {
                    DB::rollBack();
                    return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' bukan milik supplier yang dipilih!"], 422);
                }

                // 2. Strict Tenancy Isolation: Memastikan faktur milik Cabang / PT yang aktif
                if (!empty($activeBranchId)) {
                    if ((int)$invoice->branch_id !== (int)$activeBranchId) {
                        DB::rollBack();
                        return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' tidak berasal dari cabang yang aktif!"], 403);
                    }
                } else {
                    if (!empty($invoice->branch_id) || ($activeCompanyId && $invoice->company_id && (int)$invoice->company_id !== (int)$activeCompanyId)) {
                        DB::rollBack();
                        return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' tidak berasal dari entitas perusahaan yang aktif!"], 403);
                    }
                }

                // 3. Validasi Status Faktur
                if ($invoice->status !== 'Posted') {
                    DB::rollBack();
                    return response()->json(['message' => "Faktur '{$invoice->nomor_faktur}' belum diposting atau sudah tidak aktif!"], 422);
                }

                $curRemaining = (float) $invoice->remaining_balance;

                // 4. Anti-Overpayment Guard (Maksimal Keamanan Akuntansi)
                if (($bayar + $potong) > ($curRemaining + 0.05)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Nominal alokasi bayar & potongan untuk faktur '{$invoice->nomor_faktur}' melebihi sisa utang yang belum dibayar!"
                    ], 422);
                }

                $totFaktur    = floatval($invoice->grand_total);
                $sisaAkhir    = max(0, $curRemaining - ($bayar + $potong));

                $totalFaktur   += $totFaktur;
                $totalPotongan += $potong;
                $totalBayar    += $bayar;

                $detailsPayload[] = [
                    'purchase_payment_id' => $purchasePayment->id,
                    'purchase_invoice_id' => $invoice->id,
                    'total_faktur'        => $totFaktur,
                    'sisa_utang'          => $curRemaining,
                    'jumlah_bayar'        => $bayar,
                    'potongan'            => $potong,
                    'sisa_akhir'          => $sisaAkhir,
                    'catatan'             => $row['catatan'] ?? null,
                ];
            }

            if (empty($detailsPayload) || $totalBayar <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Total pembayaran harus lebih besar dari 0!'], 422);
            }

            // Validasi Akun COA Potongan Pembelian jika terdapat potongan
            if ($totalPotongan > 0 && empty($request->potongan_coa_id)) {
                $fallbackCoa = AccountMappingHelper::getCoaId('purchases', 'purchase_discount', 'default_purchase_discount', $activeCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'purchase_discount', null, $activeCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'potongan_pembelian', null, $activeCompanyId)
                    ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                        $q->where('name', 'like', '%Potongan Pembelian%')->orWhere('name', 'like', '%Diskon Pembelian%');
                    })->value('id');

                if (!$fallbackCoa) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun COA Potongan Pembelian wajib dipilih jika terdapat potongan atau diskon!'], 422);
                }
            }

            // Saldo Kas/Bank Security Check (Anti-Minus Validation & Tenancy Verification):
            // Transaksi TIDAK BISA disimpan baik sebagai draf maupun diajukan jika saldo kas/bank tidak mencukupi
            $kasBank = KasBank::lockForUpdate()->find($request->kas_bank_id);
            if (!$kasBank) {
                DB::rollBack();
                return response()->json(['message' => 'Akun Kas / Bank sumber dana tidak ditemukan!'], 422);
            }

            // Verifikasi Tenancy Akun Kas/Bank
            if (!empty($activeBranchId)) {
                if ((int)$kasBank->branch_id !== (int)$activeBranchId) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun Kas / Bank tidak sesuai dengan cabang yang sedang aktif.'], 403);
                }
            } else {
                if (!empty($kasBank->branch_id) || ($activeCompanyId && $kasBank->company_id && (int)$kasBank->company_id !== (int)$activeCompanyId)) {
                    DB::rollBack();
                    return response()->json(['message' => 'Akun Kas / Bank tidak sesuai dengan entitas perusahaan yang aktif.'], 403);
                }
            }

            $kurs = (float) ($request->kurs ?? 1);
            $totalBayarIdr = $totalBayar * ($kurs > 0 ? $kurs : 1);
            $currentSaldo  = (float) $kasBank->saldo_berjalan;
            if ($currentSaldo < $totalBayarIdr) {
                $kekurangan = $totalBayarIdr - $currentSaldo;
                DB::rollBack();
                return response()->json([
                    'message' => "Saldo Akun Kas/Bank '{$kasBank->nama_akun}' tidak mencukupi! Saldo tersedia: Rp " . number_format($currentSaldo, 2, ',', '.') . ", Nominal pembayaran: Rp " . number_format($totalBayarIdr, 2, ',', '.') . " (Kurang: Rp " . number_format($kekurangan, 2, ',', '.') . "). Transaksi tidak dapat disimpan baik sebagai draf maupun diajukan demi keamanan saldo."
                ], 422);
            }

            $nomorPembayaran = $purchasePayment->nomor_pembayaran;
            if ($request->filled('nomor_pembayaran')) {
                $nomorBaru = trim($request->nomor_pembayaran);
                if ($nomorBaru !== $purchasePayment->nomor_pembayaran) {
                    if (PurchasePayment::where('nomor_pembayaran', $nomorBaru)->where('id', '!=', $purchasePayment->id)->exists()) {
                        DB::rollBack();
                        return response()->json(['message' => "Nomor Bukti Bayar '{$nomorBaru}' sudah terdaftar! Silakan gunakan nomor lain."], 422);
                    }
                    $nomorPembayaran = $nomorBaru;
                }
            }

            $status = 'Draft';
            $submittedBy = $purchasePayment->submitted_by;
            $submittedAt = $purchasePayment->submitted_at;
            $postedBy    = null;
            $postedAt    = null;
            $rejectedBy  = $purchasePayment->rejected_by;
            $rejectedAt  = $purchasePayment->rejected_at;
            $rejectionReason = $purchasePayment->rejection_reason;

            if ($request->action_type === 'submit' || $request->action_type === 'ajukan') {
                $status          = 'Diajukan';
                $submittedBy     = auth()->id();
                $submittedAt     = now();
                $rejectedBy      = null;
                $rejectedAt      = null;
                $rejectionReason = null;
            } elseif ($request->action_type === 'post') {
                if (auth()->user()->can('posting-pembayaran-utang') || auth()->user()->can('approve-pembayaran-utang')) {
                    $status   = 'Posted';
                    $postedBy = auth()->id();
                    $postedAt = now();
                } else {
                    $status          = 'Diajukan';
                    $submittedBy     = auth()->id();
                    $submittedAt     = now();
                    $rejectedBy      = null;
                    $rejectedAt      = null;
                    $rejectionReason = null;
                }
            } else {
                // Simpan draf
                $status          = 'Draft';
                $rejectedBy      = null;
                $rejectedAt      = null;
                $rejectionReason = null;
            }

            $lampiranPath = $purchasePayment->lampiran;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $this->handleImageUpload($request->file('lampiran'), $purchasePayment->lampiran);
            } elseif ($request->boolean('remove_lampiran')) {
                if ($purchasePayment->lampiran && Storage::disk('public')->exists($purchasePayment->lampiran)) {
                    Storage::disk('public')->delete($purchasePayment->lampiran);
                }
                $lampiranPath = null;
            }

            $purchasePayment->update([
                'nomor_pembayaran'  => $nomorPembayaran,
                'tanggal_bayar'     => $request->tanggal_bayar,
                'supplier_id'       => $request->supplier_id,
                'kas_bank_id'       => $request->kas_bank_id,
                'payment_method_id' => $request->payment_method_id,
                'nomor_referensi'   => $request->nomor_referensi,
                'currency_id'       => $request->currency_id,
                'kurs'              => $request->kurs ?? 1,
                'total_faktur'      => $totalFaktur,
                'total_potongan'    => $totalPotongan,
                'potongan_coa_id'   => $totalPotongan > 0 ? $request->potongan_coa_id : null,
                'total_bayar'       => $totalBayar,
                'status'            => $status,
                'keterangan'        => $request->keterangan,
                'lampiran'          => $lampiranPath,
                'submitted_by'      => $submittedBy,
                'submitted_at'      => $submittedAt,
                'posted_by'         => $postedBy,
                'posted_at'         => $postedAt,
                'rejected_by'       => $rejectedBy,
                'rejected_at'       => $rejectedAt,
                'rejection_reason'  => $rejectionReason,
            ]);

            // Recreate details
            $purchasePayment->details()->delete();
            foreach ($detailsPayload as $d) {
                PurchasePaymentDetail::create($d);
            }

            if ($status === 'Posted') {
                $this->executePostingEffects($purchasePayment);
            }

            DB::commit();

            // Send WhatsApp Notification if submitted for approval
            if ($status === 'Diajukan') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendPaymentSubmissionNotification($purchasePayment)
                );
            }

            $successMsg = match ($status) {
                'Posted'   => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil diperbarui & diposting!",
                'Diajukan' => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil diperbarui & diajukan untuk persetujuan!",
                default    => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil diperbarui!",
            };

            return response()->json([
                'status'  => 'success',
                'message' => $successMsg,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Update Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ajukan Pembayaran Utang untuk Approval (Draft/Ditolak -> Diajukan)
     */
    public function submit(PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('tambah-pembayaran-utang') || auth()->user()->can('edit-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk mengajukan pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa diajukan oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat diajukan oleh pembuat dokumen ini.'
            ], 403);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft atau Ditolak yang dapat diajukan!'], 400);
        }

        // Saldo Kas/Bank Security Check (Anti-Minus Validation):
        $kasBank = KasBank::find($purchasePayment->kas_bank_id);
        if ($kasBank) {
            $kurs = (float) ($purchasePayment->kurs ?? 1);
            $totalBayarIdr = (float) $purchasePayment->total_bayar * ($kurs > 0 ? $kurs : 1);
            $currentSaldo  = (float) $kasBank->saldo_berjalan;
            if ($currentSaldo < $totalBayarIdr) {
                $kekurangan = $totalBayarIdr - $currentSaldo;
                return response()->json([
                    'message' => "Saldo Akun Kas/Bank '{$kasBank->nama_akun}' tidak mencukupi! Saldo tersedia: Rp " . number_format($currentSaldo, 2, ',', '.') . ", Nominal pembayaran: Rp " . number_format($totalBayarIdr, 2, ',', '.') . " (Kurang: Rp " . number_format($kekurangan, 2, ',', '.') . "). Pembayaran utang tidak dapat diajukan."
                ], 422);
            }
        }

        $purchasePayment->update([
            'status'       => 'Diajukan',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
            // Reset rejection data jika diajukan ulang setelah ditolak
            'rejected_by'      => null,
            'rejected_at'      => null,
            'rejection_reason' => null,
        ]);

        // Kirim notifikasi WA ke approver (async, tidak blocking)
        WhatsAppGatewayController::runAsync(fn() =>
            WhatsAppGatewayController::sendPaymentSubmissionNotification($purchasePayment)
        );

        return response()->json([
            'status'  => 'success',
            'message' => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil diajukan untuk persetujuan!",
        ]);
    }

    /**
     * Setujui & Posting Pembayaran Utang (Diajukan -> Posted)
     * Approve = langsung posting, tidak ada status "Disetujui" terpisah.
     */
    public function approve(PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('approve-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk menyetujui pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::lockForUpdate()->find($purchasePayment->id);
            if (!$payment || $payment->status !== 'Diajukan') {
                DB::rollBack();
                return response()->json(['message' => 'Hanya pembayaran berstatus Diajukan yang dapat disetujui (atau status telah berubah)!'], 400);
            }

            // Anti-Overpayment Guard: Pastikan tidak ada faktur yang sudah dibayar oleh transaksi lain
            $payment->load('details');
            foreach ($payment->details as $det) {
                $inv = PurchaseInvoice::lockForUpdate()->find($det->purchase_invoice_id);
                if ($inv) {
                    $curRemaining = (float) $inv->remaining_balance;
                    $allocated = (float) $det->jumlah_bayar + (float) $det->potongan;
                    if ($allocated > ($curRemaining + 0.05)) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Faktur '{$inv->nomor_faktur}' telah dibayar oleh transaksi lain atau sisa utang saat ini tidak mencukupi untuk disetujui!"
                        ], 422);
                    }
                }
            }

            $approverUser = auth()->user();

            $payment->status      = 'Posted';
            $payment->approved_by = auth()->id();
            $payment->approved_at = now();
            $payment->posted_by   = auth()->id();
            $payment->posted_at   = now();
            $payment->save();

            $this->executePostingEffects($payment);

            DB::commit();

            // Kirim notifikasi WA ke creator/submitter (async)
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendPaymentApprovedNotification($payment, $approverUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Pembayaran Utang {$payment->nomor_pembayaran} telah disetujui dan diposting! Jurnal Akuntansi diterbitkan.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Approve Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyetujui Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tolak Pengajuan Pembayaran Utang (Diajukan -> Ditolak)
     * Dokumen yang ditolak bisa diedit dan diajukan kembali.
     */
    public function reject(Request $request, PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('approve-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk menolak pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::lockForUpdate()->find($purchasePayment->id);
            if (!$payment || $payment->status !== 'Diajukan') {
                DB::rollBack();
                return response()->json(['message' => 'Hanya pembayaran berstatus Diajukan yang dapat ditolak (atau status telah berubah)!'], 400);
            }

            $rejecterUser = auth()->user();
            $reason = trim($request->rejection_reason);

            $payment->update([
                'status'           => 'Ditolak',
                'rejected_by'      => auth()->id(),
                'rejected_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            DB::commit();

            // Kirim notifikasi WA ke creator/submitter (async)
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendPaymentRejectedNotification($payment, $rejecterUser, $reason)
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Pengajuan Pembayaran Utang {$payment->nomor_pembayaran} telah ditolak. Pembuat dapat mengedit dan mengajukan kembali.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Reject Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menolak Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Posting Pembayaran Utang (Posting Langsung)
     */
    public function post(PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('posting-pembayaran-utang') || auth()->user()->can('approve-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk memposting pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::lockForUpdate()->find($purchasePayment->id);
            if (!$payment || !in_array($payment->status, ['Draft', 'Diajukan'])) {
                DB::rollBack();
                return response()->json(['message' => 'Hanya pembayaran berstatus Draft atau Diajukan yang dapat diposting!'], 400);
            }

            // Anti-Overpayment Guard: Pastikan tidak ada faktur yang sudah dibayar oleh transaksi lain
            $payment->load('details');
            foreach ($payment->details as $det) {
                $inv = PurchaseInvoice::lockForUpdate()->find($det->purchase_invoice_id);
                if ($inv) {
                    $curRemaining = (float) $inv->remaining_balance;
                    $allocated = (float) $det->jumlah_bayar + (float) $det->potongan;
                    if ($allocated > ($curRemaining + 0.05)) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Faktur '{$inv->nomor_faktur}' telah dibayar oleh transaksi lain atau sisa utang saat ini tidak mencukupi untuk diposting!"
                        ], 422);
                    }
                }
            }

            $payment->status    = 'Posted';
            $payment->posted_by = auth()->id();
            $payment->posted_at = now();
            $payment->save();

            $this->executePostingEffects($payment);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Pembayaran Utang {$payment->nomor_pembayaran} berhasil diposting! Jurnal Akuntansi & Mutasi Kas diterbitkan.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Post Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memposting Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, PurchasePayment $purchasePayment)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('batalkan-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk membatalkan pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:30',
        ], [
            'alasan_pembatalan.required' => 'Alasan pembatalan wajib diisi!',
            'alasan_pembatalan.max'      => 'Alasan pembatalan maksimal 30 karakter!',
        ]);

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::lockForUpdate()->find($purchasePayment->id);
            if (!$payment || $payment->status !== 'Posted') {
                DB::rollBack();
                return response()->json(['message' => 'Hanya pembayaran yang sudah diposting yang dapat dibatalkan (atau pembayaran sudah dibatalkan)!'], 400);
            }

            $payment->status            = 'Cancelled';
            $payment->cancelled_by     = auth()->id();
            $payment->cancelled_at     = now();
            $payment->alasan_pembatalan = $request->alasan_pembatalan;
            $payment->save();

            // 1. Rollback KasMutasi & Restore KasBank Balance
            $mutasi = KasMutasi::where('nomor_referensi', $payment->nomor_pembayaran)->first();

            if ($mutasi) {
                $kasBank = KasBank::find($mutasi->kas_bank_id);
                if ($kasBank) {
                    $kasBank->saldo_berjalan += (float) $mutasi->jumlah;
                    $kasBank->save();
                }
                $mutasi->status = 'Dibatalkan';
                $mutasi->alasan_pembatalan = $request->alasan_pembatalan;
                $mutasi->cancelled_by = auth()->id();
                $mutasi->cancelled_at = now();
                $mutasi->save();
            }

            // 2. Buat Jurnal Pembalik (Reversal Journal Entry) - Inversi Akurat dari Jurnal Asli
            $payment->loadMissing(['supplier', 'kasBank', 'details.invoice']);
            $supplierName    = $payment->supplier?->nama_supplier ?? 'Supplier';
            $targetBranchId  = !empty($payment->branch_id) ? $payment->branch_id : null;
            $targetCompanyId = !empty($payment->branch_id) ? null : ($payment->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? 1));

            // Generate Journal Number for Reversal
            $year   = date('Y');
            $month  = date('m');
            $prefix = "JNL/{$year}/{$month}/";

            $latestJnl = Jurnal::withTrashed()
                ->where('nomor_jurnal', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->first();

            $nextNum = 1;
            if ($latestJnl && preg_match('/(\d+)$/', $latestJnl->nomor_jurnal, $matches)) {
                $nextNum = ((int) $matches[1]) + 1;
            }

            $nomorJurnalReversal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnalReversal)->exists()) {
                $nextNum++;
                $nomorJurnalReversal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            // Ambil Jurnal Posting Asli untuk dibalik secara eksak (Presisi Akuntansi Tertinggi)
            $origJurnal = Jurnal::with('details')
                ->where('referensi_tipe', 'Purchase Payment')
                ->where('referensi_id', $purchasePayment->id)
                ->first();

            // Resolve label tender terkait untuk no_tender jurnal pembalik
            $tenders = collect();
            if ($purchasePayment->relationLoaded('details') || $purchasePayment->details) {
                foreach ($purchasePayment->details as $det) {
                    $inv = $det->invoice;
                    if ($inv) {
                        $tObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                        if ($tObj) {
                            $lbl = !empty($tObj->nomor_kontrak) && $tObj->nomor_kontrak !== '-'
                                ? ($tObj->nomor_kontrak . (!empty($tObj->nama_tender) ? ' - ' . $tObj->nama_tender : ''))
                                : ($tObj->nomor_kontrak ?: ($tObj->nama_tender ?: ('Tender #' . $tObj->id)));
                            if (!empty($lbl)) {
                                $tenders->push($lbl);
                            }
                        }
                    }
                }
            }
            $reversalNoTender = $origJurnal?->no_tender ?: ($tenders->unique()->implode(', ') ?: null);

            if ($origJurnal && $origJurnal->details->count() > 0) {
                $reversalJurnal = Jurnal::create([
                    'nomor_jurnal'    => $nomorJurnalReversal,
                    'tanggal_jurnal'  => now()->toDateString(),
                    'referensi_tipe'  => 'Purchase Payment Reversal',
                    'referensi_id'    => $purchasePayment->id,
                    'no_tender'       => $reversalNoTender,
                    'keterangan'      => "PEMBALIKAN PEMBAYARAN UTANG ({$purchasePayment->nomor_pembayaran}) ke {$supplierName}. Alasan: {$request->alasan_pembatalan}" . ($reversalNoTender ? " [Tender: {$reversalNoTender}]" : ""),
                    'total_debit'     => $origJurnal->total_kredit,
                    'total_kredit'    => $origJurnal->total_debit,
                    'status'          => 'Posted',
                    'company_id'      => $origJurnal->company_id,
                    'branch_id'       => $origJurnal->branch_id,
                    'created_by'      => auth()->id(),
                ]);

                foreach ($origJurnal->details as $origDet) {
                    JurnalDetail::create([
                        'jurnal_id'  => $reversalJurnal->id,
                        'account_id' => $origDet->account_id,
                        'debit'      => $origDet->kredit, // Posisi dibalik: kredit menjadi debit
                        'kredit'     => $origDet->debit,  // Posisi dibalik: debit menjadi kredit
                        'keterangan' => "Pembalikan: " . $origDet->keterangan,
                    ]);
                }
            } else {
                // Fallback jika jurnal asli tidak tercatat
                $totalBayar      = (float) $purchasePayment->total_bayar;
                $totalPotongan   = (float) $purchasePayment->total_potongan;
                $totalDebitAp    = $totalBayar + $totalPotongan;

                $apCoa = ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                    $q->where('code', 'like', '2101%')
                      ->orWhere('code', 'like', '211%')
                      ->orWhere('name', 'like', '%Hutang Usaha%')
                      ->orWhere('name', 'like', '%Utang Usaha%');
                })->first();

                $apCoaId      = $apCoa ? $apCoa->id : 1;
                $kasBankCoaId = $purchasePayment->kasBank?->coa_id ?? 1;

                $discountCoa = ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                    $q->where('code', 'like', '5102%')
                      ->orWhere('name', 'like', '%Potongan Pembelian%')
                      ->orWhere('name', 'like', '%Diskon Pembelian%');
                })->first();
                $discountCoaId = $discountCoa?->id;

                $reversalJurnal = Jurnal::create([
                    'nomor_jurnal'    => $nomorJurnalReversal,
                    'tanggal_jurnal'  => now()->toDateString(),
                    'referensi_tipe'  => 'Purchase Payment Reversal',
                    'referensi_id'    => $purchasePayment->id,
                    'no_tender'       => $reversalNoTender,
                    'keterangan'      => "PEMBALIKAN PEMBAYARAN UTANG ({$purchasePayment->nomor_pembayaran}) ke {$supplierName}. Alasan: {$request->alasan_pembatalan}" . ($reversalNoTender ? " [Tender: {$reversalNoTender}]" : ""),
                    'total_debit'     => $totalDebitAp,
                    'total_kredit'    => $totalDebitAp,
                    'status'          => 'Posted',
                    'company_id'      => $targetCompanyId,
                    'branch_id'       => $targetBranchId,
                    'created_by'      => auth()->id(),
                ]);

                // DEBIT: Kas/Bank (Uang masuk/kembali ke kas/bank)
                JurnalDetail::create([
                    'jurnal_id'  => $reversalJurnal->id,
                    'account_id' => $kasBankCoaId,
                    'debit'      => $totalBayar,
                    'kredit'     => 0,
                    'keterangan' => "Pembalikan Pengeluaran Kas/Bank (Ref: {$purchasePayment->nomor_pembayaran})",
                ]);

                // DEBIT: Potongan Pembelian
                if ($totalPotongan > 0 && $discountCoaId) {
                    JurnalDetail::create([
                        'jurnal_id'  => $reversalJurnal->id,
                        'account_id' => $discountCoaId,
                        'debit'      => $totalPotongan,
                        'kredit'     => 0,
                        'keterangan' => "Pembalikan Diskon/Potongan Utang (Ref: {$purchasePayment->nomor_pembayaran})",
                    ]);
                }

                // KREDIT: Utang Usaha
                JurnalDetail::create([
                    'jurnal_id'  => $reversalJurnal->id,
                    'account_id' => $apCoaId,
                    'debit'      => 0,
                    'kredit'     => $totalDebitAp,
                    'keterangan' => "Pembalikan Pelunasan Utang Usaha {$supplierName} (Ref: {$purchasePayment->nomor_pembayaran})",
                ]);
            }

            // 3. Rollback Purchase Invoice Status if they were marked Paid
            $purchasePayment->load('details.invoice');
            foreach ($purchasePayment->details as $det) {
                $inv = $det->invoice;
                if ($inv && $inv->status === 'Paid') {
                    // Re-evaluate remaining balance after this payment cancelled
                    if ($inv->remaining_balance > 0.01) {
                        $inv->status = 'Posted';
                        $inv->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil dibatalkan & saldo kas dikembalikan!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Cancel Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membatalkan Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus Pembayaran Utang (Hanya Draft)
     */
    public function destroy(PurchasePayment $purchasePayment)
    {
        $user    = auth()->user();
        $isSuper = $user->hasAnyRole(['Super Admin', 'Owner']);

        abort_unless(
            $isSuper || $user->can('hapus-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk menghapus draf pembayaran utang.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa dihapus oleh user pembuat dokumennya (kecuali Super Admin / Owner)
        if ($purchasePayment->status === 'Draft' && !$isSuper && (int)$purchasePayment->created_by !== (int)$user->id) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat dihapus oleh pembuat dokumen ini.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $payment = PurchasePayment::lockForUpdate()->find($purchasePayment->id);
            if (!$payment) {
                DB::rollBack();
                return response()->json(['message' => 'Data pembayaran tidak ditemukan!'], 404);
            }

            if ($payment->status === 'Diajukan') {
                DB::rollBack();
                return response()->json(['message' => 'Pembayaran sedang diajukan untuk persetujuan (approval) dan tidak dapat dihapus!'], 400);
            }

            if (!in_array($payment->status, ['Draft', 'Ditolak'])) {
                DB::rollBack();
                return response()->json(['message' => 'Pembayaran yang sudah diposting tidak dapat dihapus!'], 400);
            }

            $nomorPembayaran = $payment->nomor_pembayaran;
            $payment->details()->delete();
            $payment->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Draft Pembayaran Utang {$nomorPembayaran} berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cetak Bukti Pembayaran Utang (Payment Voucher PDF)
     */
    public function pdf(PurchasePayment $purchasePayment)
    {
        $user = auth()->user();
        abort_unless(
            $user->can('cetak-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk mencetak bukti pembayaran.'
        );
        $this->authorizeTenantAccess($purchasePayment);

        // Strict Draft Isolation: Status Draft HANYA bisa dicetak oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)$user->id) {
            abort(403, 'Dokumen pembayaran utang berstatus Draft hanya dapat dicetak oleh pembuat dokumen ini.');
        }   

        $purchasePayment->load([
            'supplier',
            'kasBank.coa',
            'paymentMethod',
            'potonganCoa',
            'currency',
            'branch',
            'company',
            'creator',
            'poster',
            'submitter',
            'approver',
            'details.invoice.po.currency',
            'details.invoice.tender',
            'details.invoice.po.tender',
            'details.invoice.goodsReceipt.tender',
        ]);

        $sessionBranchId  = session('active_branch_id');
        $sessionCompanyId = session('active_company_id');

        if (!empty($sessionBranchId)) {
            // Scope Login Cabang: tampilkan identitas Cabang
            $activeBranch  = \App\Models\Branch::find($sessionBranchId);
            $activeCompany = $activeBranch?->company ?? ($sessionCompanyId ? \App\Models\Company::find($sessionCompanyId) : null);
        } elseif (!empty($sessionCompanyId)) {
            // Scope Login PT (Kantor Pusat): isolasi ketat tanpa cabang
            $activeBranch  = null;
            $activeCompany = \App\Models\Company::find($sessionCompanyId);
        } else {
            // Fallback sesuai kepemilikan dokumen jika session kosong
            $activeBranch  = $purchasePayment->branch;
            $activeCompany = $purchasePayment->company ?? $user->companies()->first();
        }

        // Cegah kebocoran/mismatch identitas kop antar-cabang:
        // Jika dokumen milik Cabang tertentu dan admin membuka dokumen ini,
        // pastikan identitas kop surat terkunci ke pemilik sah dokumen.
        if (!empty($purchasePayment->branch_id)) {
            if (!$activeBranch || (int)$activeBranch->id !== (int)$purchasePayment->branch_id) {
                $activeBranch  = $purchasePayment->branch;
                $activeCompany = $activeBranch?->company ?? $purchasePayment->company;
            }
        } elseif (!empty($purchasePayment->company_id)) {
            $activeBranch = null;
            if (!$activeCompany || (int)$activeCompany->id !== (int)$purchasePayment->company_id) {
                $activeCompany = $purchasePayment->company;
            }
        }

        return view('modul_purchasing.pembayaran_utang.pdf', compact('purchasePayment', 'activeCompany', 'activeBranch'));
    }

    /**
     * Eksekusi Efek Posting:
     * 1. Buat Jurnal Akuntansi (DEBIT: AP, KREDIT: Kas/Bank, KREDIT: Potongan)
     * 2. Catat Mutasi Kas/Bank Keluar dan kurangi saldo berjalan KasBank
     * 3. Update status Faktur Pembelian jika lunas
     */
    protected function executePostingEffects(PurchasePayment $payment)
    {
        $payment->loadMissing(['supplier', 'kasBank', 'details.invoice']);

        $targetBranchId  = !empty($payment->branch_id) ? $payment->branch_id : null;
        $targetCompanyId = !empty($payment->branch_id) ? null : ($payment->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? 1));

        $totalBayar    = (float) $payment->total_bayar;
        $totalPotongan = (float) $payment->total_potongan;
        $totalDebitAp  = $totalBayar + $totalPotongan;

        $kursBayar = (float) ($payment->kurs ?? 1);
        if ($kursBayar <= 0) $kursBayar = 1;

        $totalKasKreditIdr      = round($totalBayar * $kursBayar, 2);
        $totalPotonganKreditIdr = round($totalPotongan * $kursBayar, 2);

        // ── 0. Security Balance Validation (Anti-Minus Check) ───────────────
        if ($payment->kasBank) {
            $currentSaldo = (float) $payment->kasBank->saldo_berjalan;
            if ($currentSaldo < $totalKasKreditIdr) {
                $kekurangan = $totalKasKreditIdr - $currentSaldo;
                throw new \Exception("Saldo Kas/Bank '{$payment->kasBank->nama_akun}' tidak mencukupi! Saldo tersedia: Rp " . number_format($currentSaldo, 2, ',', '.') . ", Total pembayaran: Rp " . number_format($totalKasKreditIdr, 2, ',', '.') . " (Kurang Rp " . number_format($kekurangan, 2, ',', '.') . "). Transaksi ditolak demi keamanan.");
            }
        }

        // ── Hitung Utang Usaha (AP) Berdasarkan Kurs Faktur Masing-Masing ──
        $totalApDebitIdr = 0;
        foreach ($payment->details as $det) {
            $inv = $det->invoice;
            $kursFaktur = (float) ($inv?->kurs ?? $kursBayar);
            if ($kursFaktur <= 0) $kursFaktur = $kursBayar;

            $alokasiValas = (float) $det->jumlah_bayar + (float) $det->potongan;
            $apFakturIdr = round($alokasiValas * $kursFaktur, 2);
            $totalApDebitIdr += $apFakturIdr;
        }

        if ($totalApDebitIdr <= 0) {
            $totalApDebitIdr = $totalKasKreditIdr + $totalPotonganKreditIdr;
        }

        // Selisih Kurs: Utang Berkurang (IDR) - (Kas Keluar IDR + Potongan IDR)
        // < 0: Kas keluar lebih banyak -> Rugi Selisih Kurs (DEBIT)
        // > 0: Kas keluar lebih sedikit -> Untung Selisih Kurs (KREDIT)
        $forexDiff = round($totalApDebitIdr - ($totalKasKreditIdr + $totalPotonganKreditIdr), 2);
        $forexLossIdr = 0;
        $forexGainIdr = 0;

        if ($forexDiff < -0.01) {
            $forexLossIdr = abs($forexDiff);
        } elseif ($forexDiff > 0.01) {
            $forexGainIdr = $forexDiff;
        }

        // ── 1. COA Resolution ──────────────────────────────────────────────
        // AP / Utang Usaha COA (DEBIT)
        $apCoaId = AccountMappingHelper::getCoaId('default', 'default_ap', null, $targetCompanyId)
            ?? AccountMappingHelper::getCoaId('default', 'accounts_payable', 'ap', $targetCompanyId)
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                $q->where('code', 'like', '2101%')->orWhere('code', 'like', '211%')->orWhere('name', 'like', '%Hutang Usaha%')->orWhere('name', 'like', '%Utang Usaha%');
            })->value('id');

        // Kas / Bank COA (KREDIT)
        $kasBankCoaId = $payment->kasBank?->coa_id;
        if (!$kasBankCoaId) {
            $kasBankCoaId = ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                $q->where('code', 'like', '1101%')->orWhere('code', 'like', '111%')->orWhere('name', 'like', '%Kas%')->orWhere('name', 'like', '%Bank%');
            })->value('id');
        }

        // Potongan Pembelian COA (KREDIT if discount > 0)
        $discountCoaId = null;
        if ($totalPotonganKreditIdr > 0) {
            if (!empty($payment->potongan_coa_id)) {
                $discountCoaId = $payment->potongan_coa_id;
            } else {
                $discountCoaId = AccountMappingHelper::getCoaId('purchases', 'purchase_discount', 'default_purchase_discount', $targetCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'purchase_discount', null, $targetCompanyId)
                    ?? AccountMappingHelper::getCoaId('default', 'potongan_pembelian', null, $targetCompanyId)
                    ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                        $q->where('name', 'like', '%Potongan Pembelian%')->orWhere('name', 'like', '%Diskon Pembelian%');
                    })->value('id');
            }

            if (!$discountCoaId) {
                throw new \Exception("Akun COA Potongan Pembelian tidak ditemukan atau belum dipilih. Silakan pilih Akun Potongan Pembelian.");
            }
        }

        // Forex Gain COA (KREDIT if forex gain > 0)
        $forexGainCoaId = null;
        if ($forexGainIdr > 0) {
            $forexGainCoaId = AccountMappingHelper::getCoaId('default', 'realized_forex_gain', 'default_forex_gain', $targetCompanyId)
                ?? AccountMappingHelper::getCoaId('default', 'forex_gain', null, $targetCompanyId)
                ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                    $q->where('code', '7102')
                      ->orWhere('name', 'like', '%Keuntungan Selisih Kurs%')
                      ->orWhere('name', 'like', '%Laba Selisih Kurs%');
                })->value('id');
        }

        // Forex Loss COA (DEBIT if forex loss > 0)
        $forexLossCoaId = null;
        if ($forexLossIdr > 0) {
            $forexLossCoaId = AccountMappingHelper::getCoaId('default', 'realized_forex_loss', 'default_forex_loss', $targetCompanyId)
                ?? AccountMappingHelper::getCoaId('default', 'forex_loss', null, $targetCompanyId)
                ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                    $q->where('code', '8102')
                      ->orWhere('name', 'like', '%Kerugian Selisih Kurs%')
                      ->orWhere('name', 'like', '%Rugi Selisih Kurs%');
                })->value('id');
        }

        // Fallbacks
        $firstCoa = ChartOfAccount::forActiveCompany()->detailOnly()->first();
        if (!$apCoaId) $apCoaId = $firstCoa?->id;
        if (!$kasBankCoaId) $kasBankCoaId = $firstCoa?->id;
        if ($forexGainIdr > 0 && !$forexGainCoaId) $forexGainCoaId = $firstCoa?->id;
        if ($forexLossIdr > 0 && !$forexLossCoaId) $forexLossCoaId = $firstCoa?->id;

        // ── 2. Create Journal Entry ─────────────────────────────────────────
        // Clean existing journals for this payment (idempotent)
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Purchase Payment')
            ->where('referensi_id', $payment->id)
            ->get();
        foreach ($existingJurnals as $exJnl) {
            $exJnl->details()->delete();
            $exJnl->forceDelete();
        }

        $year   = date('Y', strtotime($payment->tanggal_bayar));
        $month  = date('m', strtotime($payment->tanggal_bayar));
        $prefix = "JNL/{$year}/{$month}/";

        $latestJnl = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($latestJnl && preg_match('/(\d+)$/', $latestJnl->nomor_jurnal, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextNum++;
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        $supplierName = $payment->supplier?->nama_supplier ?? 'Supplier';

        // Resolve label tender terkait untuk no_tender jurnal
        $tenders = collect();
        if ($payment->relationLoaded('details') || $payment->details) {
            foreach ($payment->details as $det) {
                $inv = $det->invoice;
                if ($inv) {
                    $tObj = $inv->tender ?? $inv->goodsReceipt?->tender ?? $inv->po?->tender;
                    if ($tObj) {
                        $lbl = !empty($tObj->nomor_kontrak) && $tObj->nomor_kontrak !== '-'
                            ? ($tObj->nomor_kontrak . (!empty($tObj->nama_tender) ? ' - ' . $tObj->nama_tender : ''))
                            : ($tObj->nomor_kontrak ?: ($tObj->nama_tender ?: ('Tender #' . $tObj->id)));
                        if (!empty($lbl)) {
                            $tenders->push($lbl);
                        }
                    }
                }
            }
        }
        $tenderLabel = $tenders->unique()->implode(', ') ?: null;

        $totalJurnalDebit  = round($totalApDebitIdr + $forexLossIdr, 2);
        $totalJurnalKredit = round($totalKasKreditIdr + $totalPotonganKreditIdr + $forexGainIdr, 2);

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $payment->tanggal_bayar,
            'referensi_tipe' => 'Purchase Payment',
            'referensi_id'   => $payment->id,
            'no_tender'      => $tenderLabel,
            'company_id'     => $targetCompanyId,
            'branch_id'      => $targetBranchId,
            'total_debit'    => $totalJurnalDebit,
            'total_kredit'   => $totalJurnalKredit,
            'keterangan'     => "Pembayaran Utang ke {$supplierName} ({$payment->nomor_pembayaran})" . ($tenderLabel ? " [Tender: {$tenderLabel}]" : ""),
            'created_by'     => auth()->id(),
        ]);

        // DEBIT: Utang Usaha (AP)
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $apCoaId,
            'debit'      => $totalApDebitIdr,
            'kredit'     => 0,
            'keterangan' => "Pelunasan Utang Usaha {$supplierName} ({$payment->nomor_pembayaran})" . ($tenderLabel ? " - Proyek: {$tenderLabel}" : ""),
        ]);

        // DEBIT: Kerugian Selisih Kurs (jika ada rugi kurs)
        if ($forexLossIdr > 0 && $forexLossCoaId) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $forexLossCoaId,
                'debit'      => $forexLossIdr,
                'kredit'     => 0,
                'keterangan' => "Kerugian Selisih Kurs Pembayaran Utang ({$payment->nomor_pembayaran})",
            ]);
        }

        // KREDIT: Kas / Bank
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $kasBankCoaId,
            'debit'      => 0,
            'kredit'     => $totalKasKreditIdr,
            'keterangan' => "Pengeluaran Kas/Bank ({$payment->nomor_pembayaran})",
        ]);

        // KREDIT: Potongan Pembelian (jika ada)
        if ($totalPotonganKreditIdr > 0 && $discountCoaId) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $discountCoaId,
                'debit'      => 0,
                'kredit'     => $totalPotonganKreditIdr,
                'keterangan' => "Potongan/Diskon Pelunasan Utang ({$payment->nomor_pembayaran})",
            ]);
        }

        // KREDIT: Keuntungan Selisih Kurs (jika ada laba kurs)
        if ($forexGainIdr > 0 && $forexGainCoaId) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $forexGainCoaId,
                'debit'      => 0,
                'kredit'     => $forexGainIdr,
                'keterangan' => "Keuntungan Selisih Kurs Pembayaran Utang ({$payment->nomor_pembayaran})",
            ]);
        }

        // ── 3. Create KasMutasi & Update Saldo KasBank ───────────────────────
        $existingMutasi = KasMutasi::where('nomor_referensi', $payment->nomor_pembayaran)->first();

        if ($existingMutasi) {
            $existingMutasi->forceDelete();
        }

        // Generate Transaction Number (KK-YYMM-XXXX)
        $mPrefix = "KK-" . date('ym') . "-";
        $latestM = KasMutasi::withTrashed()->where('nomor_transaksi', 'like', $mPrefix . '%')->orderByDesc('id')->first();
        $mNum = 1;
        if ($latestM && preg_match('/(\d+)$/', $latestM->nomor_transaksi, $matches)) {
            $mNum = ((int) $matches[1]) + 1;
        }
        $nomorTransaksi = $mPrefix . str_pad($mNum, 4, '0', STR_PAD_LEFT);
        while (KasMutasi::withTrashed()->where('nomor_transaksi', $nomorTransaksi)->exists()) {
            $mNum++;
            $nomorTransaksi = $mPrefix . str_pad($mNum, 4, '0', STR_PAD_LEFT);
        }

        $saldoSebelum = (float) ($payment->kasBank?->saldo_berjalan ?? 0);
        $saldoSesudah = $saldoSebelum - $totalKasKreditIdr;

        KasMutasi::create([
            'nomor_transaksi' => $nomorTransaksi,
            'kas_bank_id'     => $payment->kas_bank_id,
            'tipe'            => 'Keluar',
            'kategori'        => 'Pembayaran Supplier',
            'lawan_coa_id'    => $apCoaId,
            'jumlah'          => $totalKasKreditIdr,
            'saldo_sebelum'   => $saldoSebelum,
            'saldo_sesudah'   => $saldoSesudah,
            'tanggal'         => $payment->tanggal_bayar,
            'nomor_referensi' => $payment->nomor_pembayaran,
            'kontak_tipe'     => 'Supplier',
            'kontak_id'       => $payment->supplier_id,
            'nama_kontak'     => $supplierName,
            'keterangan'      => "Pembayaran Utang ke {$supplierName} (Ref: {$payment->nomor_pembayaran})",
            'lampiran'        => $payment->lampiran,
            'status'          => 'Disetujui',
            'jurnal_id'       => $jurnal->id,
            'company_id'      => $targetCompanyId,
            'branch_id'       => $targetBranchId,
            'created_by'      => auth()->id(),
        ]);

        // Reduce KasBank running balance
        if ($payment->kasBank) {
            $payment->kasBank->saldo_berjalan -= $totalKasKreditIdr;
            $payment->kasBank->save();
        }

        // ── 4. Update Invoice Status to 'Paid' if remaining balance <= 0 ─────
        foreach ($payment->details as $det) {
            $invoice = $det->invoice;
            if ($invoice) {
                $rem = $invoice->remaining_balance;
                if ($rem <= 0.01) {
                    $invoice->status = 'Paid';
                    $invoice->save();
                }
            }
        }
    }

    /**
     * Helper: Kompresi Gambar Bukti Transfer ke Ukuran KB (WebP/JPEG)
     */
    private function handleImageUpload($file, $oldPath = null): ?string
    {
        if (!$file || !$file->isValid()) {
            return $oldPath;
        }

        // Delete old file if updating
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $filename = 'bukti_bayar_' . date('Ymd_His') . '_' . uniqid() . '.webp';
        $directory = 'purchase_payments';
        $fullDirPath = storage_path('app/public/' . $directory);

        if (!file_exists($fullDirPath)) {
            @mkdir($fullDirPath, 0755, true);
        }

        $targetPath = $fullDirPath . '/' . $filename;
        $relativePath = $directory . '/' . $filename;

        // Process image with GD to compress to ~100-300 KB
        $imageInfo = @getimagesize($file->getRealPath());
        if (!$imageInfo) {
            return $file->store($directory, 'public');
        }

        $mime = $imageInfo['mime'];
        $srcImage = null;

        switch ($mime) {
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
                return $file->store($directory, 'public');
        }

        if (!$srcImage) {
            return $file->store($directory, 'public');
        }

        $origWidth  = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Maximum dimensions (1280px max maintaining aspect ratio)
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

        // Preserve transparency for PNG / WebP
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as WebP with 78% quality (converts large photos down into ~80-250 KB)
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

    /**
     * Isolasi Tenancy Ketat: Memastikan dokumen hanya diakses oleh PT / Cabang yang berwenang
     */
    private function authorizeTenantAccess(PurchasePayment $payment): void
    {
        $user = auth()->user();
        if (!$user) return;
        if ($user->hasRole('Super Admin') || $user->hasRole('Owner')) {
            return;
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? $user->company_id;

        if (!empty($activeBranchId)) {
            // Sesi aktif adalah Cabang -> Dokumen WAJIB milik Cabang ini (company_id WAJIB NULL)
            if (empty($payment->branch_id) || (int)$payment->branch_id !== (int)$activeBranchId) {
                abort(403, 'Anda tidak memiliki hak akses ke dokumen pembayaran utang ini (khusus cabang terkait).');
            }
        } else {
            // Sesi aktif adalah PT Pusat -> Dokumen WAJIB milik PT (branch_id WAJIB NULL)
            if (!empty($payment->branch_id)) {
                abort(403, 'Dokumen pembayaran utang ini adalah milik Cabang. Silakan beralih ke sesi Cabang terkait.');
            }
            if ($activeCompanyId && $payment->company_id && strval($activeCompanyId) !== strval($payment->company_id)) {
                abort(403, 'Anda tidak memiliki akses ke dokumen pembayaran utang perusahaan lain.');
            }
        }
    }
}
