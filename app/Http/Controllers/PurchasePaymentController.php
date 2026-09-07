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
    public function checkSync(Request $request)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $userId          = auth()->id();

        $query = PurchasePayment::query();
        if ($activeBranchId) {
            $query->where('branch_id', $activeBranchId);
        } elseif ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        }

        // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh user pembuatnya (tanpa kecuali)
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('status', '!=', 'Draft')
                  ->orWhere('created_by', $userId);
            });
        }

        $meta = $query->selectRaw('MAX(updated_at) as last_updated, MAX(id) as max_id, COUNT(id) as total_rows')->first();
        $fingerprint = md5(($meta->last_updated ?? '0') . '_' . ($meta->max_id ?? '0') . '_' . ($meta->total_rows ?? '0'));

        return response()->json([
            'fingerprint' => $fingerprint,
            'last_updated' => $meta->last_updated,
        ]);
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
                    'id', 'nomor_pembayaran', 'tanggal_bayar', 'supplier_id',
                    'kas_bank_id', 'payment_method_id', 'nomor_referensi',
                    'branch_id', 'company_id', 'currency_id', 'kurs',
                    'total_faktur', 'total_potongan', 'total_bayar',
                    'status', 'keterangan', 'lampiran', 'created_by', 'posted_by',
                    'submitted_by', 'approved_by', 'created_at', 'updated_at'
                ])
                ->with([
                    'supplier:id,nama_supplier,kode_supplier',
                    'kasBank:id,nama_akun,nomor_rekening,tipe',
                    'paymentMethod:id,nama_metode',
                    'branch:id,nama_cabang',
                    'currency:id,kode_currency,simbol',
                    'creator:id,nama_lengkap,username',
                    'poster:id,nama_lengkap,username',
                    'submitter:id,nama_lengkap,username',
                    'approver:id,nama_lengkap,username'
                ])
                ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                    if ($activeBranchId) {
                        $q->where('branch_id', $activeBranchId);
                    } elseif ($activeCompanyId) {
                        $q->where('company_id', $activeCompanyId);
                    }
                });

            // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh user pembuatnya (tanpa kecuali)
            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $userId);
                });
            }

            // Custom search
            if ($request->filled('search_custom')) {
                $term = $request->search_custom;
                $query->where(function ($q) use ($term) {
                    $q->where('nomor_pembayaran', 'like', "%{$term}%")
                      ->orWhere('nomor_referensi', 'like', "%{$term}%")
                      ->orWhere('keterangan', 'like', "%{$term}%")
                      ->orWhereHas('supplier', function ($sq) use ($term) {
                          $sq->where('nama_supplier', 'like', "%{$term}%")
                             ->orWhere('kode_supplier', 'like', "%{$term}%");
                      });
                });
            }

            // Filter status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter supplier
            if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Filter kas/bank
            if ($request->filled('kas_bank_id') && $request->kas_bank_id !== 'all') {
                $query->where('kas_bank_id', $request->kas_bank_id);
            }

            // Filter tanggal
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tanggal_bayar', [$request->start_date, $request->end_date]);
            }

            // Urutkan berdasarkan data yang paling terakhir di-input
            $query->orderBy('id', 'desc');

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

                    if ($curCode !== 'IDR' && $kurs > 1) {
                        $idrEquivalent = $totPot * $kurs;
                        return '<div class="text-danger fw-semibold">' . e($curSymbol) . ' ' . number_format($totPot, 2, ',', '.') . '</div>'
                            . '<small class="text-muted" style="font-size: 0.72rem;">≈ Rp ' . number_format($idrEquivalent, 2, ',', '.') . '</small>';
                    }
                    return '<span class="text-danger fw-semibold">Rp ' . number_format($totPot, 2, ',', '.') . '</span>';
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
                    $canDetail  = $user->can('detail-pembayaran-utang');
                    $canCreate  = $user->can('tambah-pembayaran-utang');
                    $canEdit    = $user->can('edit-pembayaran-utang');
                    $canSubmit  = $canCreate || $canEdit;
                    $canApprove = $user->can('approve-pembayaran-utang');
                    $canReject  = $canApprove;
                    $canDelete  = $user->can('batalkan-pembayaran-utang');
                    $canCancel  = $user->can('batalkan-pembayaran-utang');
                    $canCetak   = $user->can('cetak-pembayaran-utang');

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
                        if ($canCancel) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-cancel-payment" data-id="' . $p->id . '" data-nomor="' . e($p->nomor_pembayaran) . '" title="Batalkan / Void Pembayaran"><i class="bi bi-x-octagon"></i></button>';
                        }
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_pembayaran', 'kas_bank_name', 'total_bayar_fmt', 'total_potongan_fmt', 'status_badge', 'action'])
                ->make(true);
        }

        // Dropdown Data
        $suppliers = MasterSupplier::with(['primaryBank', 'banks'])->orderBy('nama_supplier')->get();
        $kasBankQuery = KasBank::with('coa')
            ->forActiveCompany()
            ->where('is_active', true);

        if ($activeBranchId) {
            $kasBankQuery->where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            });
        }

        $kasBanks = $kasBankQuery->orderBy('tipe')->orderBy('nama_akun')->get();

        if ($kasBanks->isEmpty()) {
            $kasBanks = KasBank::with('coa')
                ->where('is_active', true)
                ->orderBy('tipe')
                ->orderBy('nama_akun')
                ->get();
        }
        $paymentMethods = PaymentMethod::where('status', 'Active')->orderBy('nama_metode')->get();
        $currencies = MasterCurrency::where('status', 'Active')->orderBy('kode_currency')->get();

        // KPI Summary Multi-Currency Calculations
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $kpiBaseQuery = PurchasePayment::query()
            ->with('currency')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if ($activeBranchId) {
                    $q->where('branch_id', $activeBranchId);
                } elseif ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId);
                }
            });

        $postedPaymentsThisMonth = (clone $kpiBaseQuery)
            ->where('status', 'Posted')
            ->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth])
            ->get();

        $totalPaymentIdrThisMonth = 0;
        $totalPaymentUsdThisMonth = 0;
        $totalPaymentEquivalentIdrThisMonth = 0;

        foreach ($postedPaymentsThisMonth as $pay) {
            $curCode = strtoupper($pay->currency?->kode_currency ?? 'IDR');
            $totVal  = (float) $pay->total_bayar;
            $kurs    = (float) ($pay->kurs ?? 1);
            if ($kurs <= 0) $kurs = 1;

            if ($curCode === 'USD' || $curCode === 'DOLAR' || str_contains($curCode, 'USD') || str_contains($curCode, 'DOLAR')) {
                $totalPaymentUsdThisMonth += $totVal;
                $totalPaymentEquivalentIdrThisMonth += ($totVal * $kurs);
            } else {
                $totalPaymentIdrThisMonth += $totVal;
                $totalPaymentEquivalentIdrThisMonth += $totVal;
            }
        }

        $allPayments = (clone $kpiBaseQuery)->get(['id', 'status']);
        $totalPaymentCount   = $allPayments->count();
        $totalDraftCount     = $allPayments->where('status', 'Draft')->count();
        $totalPendingCount   = $allPayments->where('status', 'Diajukan')->count();
        $totalRejectedCount  = $allPayments->where('status', 'Ditolak')->count();
        $totalPostedCount    = $allPayments->where('status', 'Posted')->count();
        $totalCancelledCount = $allPayments->where('status', 'Cancelled')->count();

        // Calculate Outstanding AP from Posted Purchase Invoices separated by Currency
        $invoiceBaseQuery = PurchaseInvoice::query()
            ->with(['currency', 'po.currency'])
            ->where('status', 'Posted')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if ($activeBranchId) {
                    $q->where('branch_id', $activeBranchId);
                }
            });

        $postedInvoices = (clone $invoiceBaseQuery)->get();
        $totalOutstandingApIdr = 0;
        $totalOutstandingApUsd = 0;
        $totalOutstandingApEquivalentIdr = 0;

        $totalOverdueCount = 0;
        $overdueIdrAmount  = 0;
        $overdueUsdAmount  = 0;
        $today = now()->toDateString();

        foreach ($postedInvoices as $inv) {
            $rem = (float) $inv->remaining_balance;
            if ($rem > 0.01) {
                $curCode = strtoupper($inv->currency?->kode_currency ?? $inv->po?->currency?->kode_currency ?? 'IDR');
                $kurs    = (float) ($inv->kurs ?? $inv->po?->kurs ?? 1);
                if ($kurs <= 0) $kurs = 1;

                $isOverdue = ($inv->tanggal_jatuh_tempo && $inv->tanggal_jatuh_tempo->toDateString() < $today);
                if ($isOverdue) {
                    $totalOverdueCount++;
                }

                if ($curCode === 'USD' || $curCode === 'DOLAR' || str_contains($curCode, 'USD') || str_contains($curCode, 'DOLAR')) {
                    $totalOutstandingApUsd += $rem;
                    $totalOutstandingApEquivalentIdr += ($rem * $kurs);
                    if ($isOverdue) {
                        $overdueUsdAmount += $rem;
                    }
                } else {
                    $totalOutstandingApIdr += $rem;
                    $totalOutstandingApEquivalentIdr += $rem;
                    if ($isOverdue) {
                        $overdueIdrAmount += $rem;
                    }
                }
            }
        }

        // Pass permission flags to the view for conditional UI rendering
        $authUser = auth()->user();
        $permissions = [
            'canView'    => $authUser->can('lihat-pembayaran-utang'),
            'canCreate'  => $authUser->can('tambah-pembayaran-utang'),
            'canDetail'  => $authUser->can('detail-pembayaran-utang'),
            'canEdit'    => $authUser->can('edit-pembayaran-utang'),
            'canSubmit'  => $authUser->can('tambah-pembayaran-utang') || $authUser->can('edit-pembayaran-utang'),
            'canApprove' => $authUser->can('approve-pembayaran-utang'),
            'canReject'  => $authUser->can('approve-pembayaran-utang'),
            'canPost'    => $authUser->can('approve-pembayaran-utang'),
            'canCancel'  => $authUser->can('batalkan-pembayaran-utang'),
            'canCetak'   => $authUser->can('cetak-pembayaran-utang'),
        ];

        return view('modul_purchasing.pembayaran_utang.index', compact(
            'suppliers',
            'kasBanks',
            'paymentMethods',
            'currencies',
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
     * AJAX Endpoint: Pencarian faktur pembelian belum lunas (Search & Lookup)
     */
    public function lookupInvoices(Request $request)
    {
        $activeBranchId  = session('active_branch_id');

        $query = PurchaseInvoice::query()
            ->with(['supplier', 'po.paymentMethod', 'paymentTerm', 'currency'])
            ->where('status', 'Posted');

        if ($activeBranchId) {
            $query->where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            });
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
                   });
            });
        }

        $invoices = $query->orderBy('tanggal_jatuh_tempo', 'asc')
            ->orderBy('tanggal_faktur', 'asc')
            ->limit(100)
            ->get();

        $today = now()->startOfDay();
        $result = [];

        foreach ($invoices as $inv) {
            $paid = $inv->paid_amount;
            $grandTotal = (float) $inv->grand_total;
            $remaining = max(0, $grandTotal - $paid);

            if ($remaining > 0.01) {
                $dueDate = $inv->tanggal_jatuh_tempo;
                $isOverdue = $dueDate && $dueDate->lt($today);
                $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;

                $result[] = [
                    'id'                     => $inv->id,
                    'supplier_id'            => $inv->supplier_id,
                    'supplier_name'          => $inv->supplier?->nama_supplier ?? '-',
                    'supplier_code'          => $inv->supplier?->kode_supplier ?? '-',
                    'nomor_faktur'           => $inv->nomor_faktur,
                    'nomor_faktur_supplier'  => $inv->nomor_faktur_supplier ?? '-',
                    'tanggal_faktur'         => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                    'tanggal_jatuh_tempo'    => $dueDate ? $dueDate->format('d/m/Y') : '-',
                    'is_overdue'             => $isOverdue,
                    'days_overdue'           => $daysOverdue,
                    'grand_total'            => $grandTotal,
                    'grand_total_fmt'        => 'Rp ' . number_format($grandTotal, 2, ',', '.'),
                    'paid_amount'            => $paid,
                    'paid_amount_fmt'        => 'Rp ' . number_format($paid, 2, ',', '.'),
                    'remaining_balance'      => $remaining,
                    'remaining_balance_fmt'  => 'Rp ' . number_format($remaining, 2, ',', '.'),
                    'currency_id'            => $inv->currency_id,
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
        $activeBranchId = session('active_branch_id');

        $query = PurchaseInvoice::query()
            ->with(['supplier', 'po.paymentMethod', 'paymentTerm', 'currency'])
            ->where('supplier_id', $supplierId)
            ->where('status', 'Posted');

        if ($activeBranchId) {
            $query->where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            });
        }

        $invoices = $query->orderBy('tanggal_jatuh_tempo', 'asc')
            ->orderBy('tanggal_faktur', 'asc')
            ->get();

        $today = now()->startOfDay();
        $result = [];

        foreach ($invoices as $inv) {
            $paid = $inv->paid_amount;
            $grandTotal = (float) $inv->grand_total;
            $remaining = max(0, $grandTotal - $paid);

            if ($remaining > 0.01) {
                $dueDate = $inv->tanggal_jatuh_tempo;
                $isOverdue = $dueDate && $dueDate->lt($today);
                $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;

                $result[] = [
                    'id'                     => $inv->id,
                    'nomor_faktur'           => $inv->nomor_faktur,
                    'nomor_faktur_supplier'  => $inv->nomor_faktur_supplier ?? '-',
                    'tanggal_faktur'         => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                    'tanggal_jatuh_tempo'    => $dueDate ? $dueDate->format('d/m/Y') : '-',
                    'is_overdue'             => $isOverdue,
                    'days_overdue'           => $daysOverdue,
                    'grand_total'            => $grandTotal,
                    'grand_total_fmt'        => 'Rp ' . number_format($grandTotal, 2, ',', '.'),
                    'paid_amount'            => $paid,
                    'paid_amount_fmt'        => 'Rp ' . number_format($paid, 2, ',', '.'),
                    'remaining_balance'      => $remaining,
                    'remaining_balance_fmt'  => 'Rp ' . number_format($remaining, 2, ',', '.'),
                    'currency_id'            => $inv->currency_id,
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
            'supplier_id'     => 'required|exists:master_suppliers,id',
            'kas_bank_id'     => 'required|exists:kas_banks,id',
            'tanggal_bayar'   => 'required|date',
            'nomor_referensi' => 'nullable|string|max:100',
            'keterangan'      => 'nullable|string|max:1000',
            'lampiran'        => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
            'invoices'        => 'required|array|min:1',
            'invoices.*.purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'invoices.*.jumlah_bayar'        => 'required|numeric|min:0',
            'invoices.*.potongan'            => 'nullable|numeric|min:0',
            'invoices.*.catatan'             => 'nullable|string|max:255',
        ], [
            'supplier_id.required'   => 'Pilih Supplier yang akan dibayar!',
            'kas_bank_id.required'   => 'Pilih Akun Kas / Bank sumber dana pembayaran!',
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi!',
            'invoices.required'      => 'Pilih minimal satu faktur yang akan dibayar!',
            'invoices.min'           => 'Pilih minimal satu faktur yang akan dibayar!',
            'lampiran.mimes'         => 'Format file lampiran harus JPG, PNG, WEBP, atau PDF!',
            'lampiran.max'           => 'Ukuran file lampiran maksimal 10MB!',
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

                $curRemaining = $invoice->remaining_balance;
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
                'branch_id'         => $activeBranchId,
                'company_id'        => $activeCompanyId,
                'currency_id'       => $request->currency_id,
                'kurs'              => $request->kurs ?? 1,
                'total_faktur'      => $totalFaktur,
                'total_potongan'    => $totalPotongan,
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
        abort_unless(
            auth()->user()->can('detail-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk melihat rincian pembayaran utang.'
        );

        // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat dilihat oleh pembuat dokumen ini.'
            ], 403);
        }

        $purchasePayment->load([
            'supplier',
            'kasBank.coa',
            'paymentMethod',
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
            'jurnal.details.account',
            'reversalJurnal.details.account',
            'kasMutasi',
        ]);

        return response()->json([
            'status'  => 'success',
            'payment' => $purchasePayment,
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

        // Strict Draft Isolation: Status Draft HANYA bisa diedit oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat diedit oleh pembuat dokumen ini.'
            ], 403);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Diajukan', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft, Diajukan, atau Ditolak yang dapat diedit!'], 400);
        }

        $purchasePayment->load(['details.invoice', 'supplier', 'kasBank', 'paymentMethod']);

        // Also fetch all available unpaid invoices of this supplier
        $activeBranchId = session('active_branch_id');
        $allInvoicesQuery = PurchaseInvoice::query()
            ->with(['po.paymentMethod', 'currency'])
            ->where('supplier_id', $purchasePayment->supplier_id)
            ->where('status', 'Posted');

        if ($activeBranchId) {
            $allInvoicesQuery->where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)->orWhereNull('branch_id');
            });
        }

        $allInvoices = $allInvoicesQuery->get();

        $existingAllocations = $purchasePayment->details->keyBy('purchase_invoice_id');
        $invoiceList = [];

        foreach ($allInvoices as $inv) {
            $paidWithoutThis = (float) $inv->paymentDetails()
                ->where('purchase_payment_id', '!=', $purchasePayment->id)
                ->whereHas('payment', function ($q) {
                    $q->where('status', 'Posted');
                })
                ->sum(DB::raw('jumlah_bayar + potongan'));

            $remWithoutThis = max(0, (float) $inv->grand_total - $paidWithoutThis);
            $alloc = $existingAllocations->get($inv->id);

            $invoiceList[] = [
                'id'                    => $inv->id,
                'nomor_faktur'          => $inv->nomor_faktur,
                'nomor_faktur_supplier' => $inv->nomor_faktur_supplier ?? '-',
                'tanggal_faktur'        => $inv->tanggal_faktur ? $inv->tanggal_faktur->format('d/m/Y') : '-',
                'tanggal_jatuh_tempo'   => $inv->tanggal_jatuh_tempo ? $inv->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                'grand_total'           => (float) $inv->grand_total,
                'grand_total_fmt'       => 'Rp ' . number_format($inv->grand_total, 2, ',', '.'),
                'remaining_balance'     => $remWithoutThis,
                'remaining_balance_fmt' => 'Rp ' . number_format($remWithoutThis, 2, ',', '.'),
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
            'supplier_id'     => 'required|exists:master_suppliers,id',
            'kas_bank_id'     => 'required|exists:kas_banks,id',
            'tanggal_bayar'   => 'required|date',
            'nomor_referensi' => 'nullable|string|max:100',
            'keterangan'      => 'nullable|string|max:1000',
            'lampiran'        => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
            'invoices'        => 'required|array|min:1',
            'invoices.*.purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'invoices.*.jumlah_bayar'        => 'required|numeric|min:0',
            'invoices.*.potongan'            => 'nullable|numeric|min:0',
            'invoices.*.catatan'             => 'nullable|string|max:255',
        ], [
            'supplier_id.required'   => 'Pilih Supplier yang akan dibayar!',
            'kas_bank_id.required'   => 'Pilih Akun Kas / Bank sumber dana pembayaran!',
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi!',
            'invoices.required'      => 'Pilih minimal satu faktur yang akan dibayar!',
            'invoices.min'           => 'Pilih minimal satu faktur yang akan dibayar!',
            'lampiran.mimes'         => 'Format file lampiran harus JPG, PNG, WEBP, atau PDF!',
            'lampiran.max'           => 'Ukuran file lampiran maksimal 10MB!',
        ]);

        try {
            DB::beginTransaction();

            $totalFaktur   = 0;
            $totalPotongan = 0;
            $totalBayar    = 0;
            $detailsPayload = [];

            foreach ($request->invoices as $row) {
                $invId  = $row['purchase_invoice_id'];
                $bayar  = floatval($row['jumlah_bayar'] ?? 0);
                $potong = floatval($row['potongan'] ?? 0);

                if (($bayar + $potong) <= 0) continue;

                $invoice = PurchaseInvoice::lockForUpdate()->find($invId);
                if (!$invoice) continue;

                $curRemaining = $invoice->remaining_balance;
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

        // Strict Draft Isolation: Status Draft HANYA bisa diajukan oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat diajukan oleh pembuat dokumen ini.'
            ], 403);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Ditolak'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft atau Ditolak yang dapat diajukan!'], 400);
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

        if ($purchasePayment->status !== 'Diajukan') {
            return response()->json(['message' => 'Hanya pembayaran berstatus Diajukan yang dapat disetujui!'], 400);
        }

        try {
            DB::beginTransaction();

            $approverUser = auth()->user();

            $purchasePayment->status      = 'Posted';
            $purchasePayment->approved_by = auth()->id();
            $purchasePayment->approved_at = now();
            $purchasePayment->posted_by   = auth()->id();
            $purchasePayment->posted_at   = now();
            $purchasePayment->save();

            $this->executePostingEffects($purchasePayment);

            DB::commit();

            // Kirim notifikasi WA ke creator/submitter (async)
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendPaymentApprovedNotification($purchasePayment, $approverUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} telah disetujui dan diposting! Jurnal Akuntansi diterbitkan.",
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

        if ($purchasePayment->status !== 'Diajukan') {
            return response()->json(['message' => 'Hanya pembayaran berstatus Diajukan yang dapat ditolak!'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            $rejecterUser = auth()->user();
            $reason = trim($request->rejection_reason);

            $purchasePayment->update([
                'status'           => 'Ditolak',
                'rejected_by'      => auth()->id(),
                'rejected_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            DB::commit();

            // Kirim notifikasi WA ke creator/submitter (async)
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendPaymentRejectedNotification($purchasePayment, $rejecterUser, $reason)
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Pengajuan Pembayaran Utang {$purchasePayment->nomor_pembayaran} telah ditolak. Pembuat dapat mengedit dan mengajukan kembali.",
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

        if (!in_array($purchasePayment->status, ['Draft', 'Diajukan'])) {
            return response()->json(['message' => 'Hanya pembayaran berstatus Draft atau Diajukan yang dapat diposting!'], 400);
        }

        try {
            DB::beginTransaction();

            $purchasePayment->status    = 'Posted';
            $purchasePayment->posted_by = auth()->id();
            $purchasePayment->posted_at = now();
            $purchasePayment->save();

            $this->executePostingEffects($purchasePayment);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil diposting! Jurnal Akuntansi & Mutasi Kas diterbitkan.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchasePayment Post Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memposting Pembayaran Utang: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batalkan / Void Pembayaran Utang (Posted -> Cancelled)
     */
    public function cancel(Request $request, PurchasePayment $purchasePayment)
    {
        abort_unless(
            auth()->user()->can('batalkan-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk membatalkan pembayaran utang.'
        );

        if ($purchasePayment->status !== 'Posted') {
            return response()->json(['message' => 'Hanya pembayaran yang sudah diposting yang dapat dibatalkan!'], 400);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:500',
        ], [
            'alasan_pembatalan.required' => 'Alasan pembatalan wajib diisi!',
        ]);

        try {
            DB::beginTransaction();

            $purchasePayment->status            = 'Cancelled';
            $purchasePayment->cancelled_by     = auth()->id();
            $purchasePayment->cancelled_at     = now();
            $purchasePayment->alasan_pembatalan = $request->alasan_pembatalan;
            $purchasePayment->save();

            // 1. Rollback KasMutasi & Restore KasBank Balance
            $mutasi = KasMutasi::where('nomor_referensi', $purchasePayment->nomor_pembayaran)->first();

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

            // 2. Buat Jurnal Pembalik (Reversal Journal Entry) - Simpan Jurnal Asli & Tambahkan Jurnal Pembalik
            $purchasePayment->loadMissing(['supplier', 'kasBank', 'details.invoice']);
            $totalBayar      = (float) $purchasePayment->total_bayar;
            $totalPotongan   = (float) $purchasePayment->total_potongan;
            $totalDebitAp    = $totalBayar + $totalPotongan;
            $supplierName    = $purchasePayment->supplier?->nama_supplier ?? 'Supplier';
            $activeCompanyId = session('active_company_id') ?? $purchasePayment->company_id ?? auth()->user()?->company_id;
            $activeBranchId  = session('active_branch_id') ?? $purchasePayment->branch_id;

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

            // Temukan Akun Hutang Usaha (Trade Accounts Payable)
            $apCoa = ChartOfAccount::where('kode_akun', 'like', '2-11%')
                ->orWhere('nama_akun', 'like', '%Hutang Usaha%')
                ->orWhere('nama_akun', 'like', '%Utang Usaha%')
                ->orWhere('tipe_akun', 'Hutang Lancar')
                ->orderBy('kode_akun')
                ->first();

            $apCoaId = $apCoa ? $apCoa->id : 1;
            $kasBankCoaId = $purchasePayment->kasBank?->coa_id ?? 1;

            // Cari COA Potongan Pembelian
            $discountCoa = ChartOfAccount::where('kode_akun', 'like', '5-%')
                ->where(function($q) {
                    $q->where('nama_akun', 'like', '%Potongan Pembelian%')
                      ->orWhere('nama_akun', 'like', '%Diskon Pembelian%');
                })
                ->first();
            $discountCoaId = $discountCoa?->id;

            $reversalJurnal = Jurnal::create([
                'nomor_jurnal'    => $nomorJurnalReversal,
                'tanggal_jurnal'  => now()->toDateString(),
                'referensi_tipe'  => 'PurchasePaymentReversal',
                'referensi_id'    => $purchasePayment->id,
                'keterangan'      => "PEMBALIKAN PEMBAYARAN UTANG ({$purchasePayment->nomor_pembayaran}) ke {$supplierName}. Alasan: {$request->alasan_pembatalan}",
                'total_debit'     => $totalDebitAp,
                'total_kredit'    => $totalDebitAp,
                'status'          => 'Posted',
                'company_id'      => $activeCompanyId,
                'branch_id'       => $activeBranchId,
                'created_by'      => auth()->id(),
            ]);

            // DEBIT: Kas/Bank (Uang masuk/kembali ke rekening kas/bank)
            JurnalDetail::create([
                'jurnal_id'  => $reversalJurnal->id,
                'account_id' => $kasBankCoaId,
                'debit'      => $totalBayar,
                'kredit'     => 0,
                'keterangan' => "Pembalikan Pengeluaran Kas/Bank (Ref: {$purchasePayment->nomor_pembayaran})",
            ]);

            // DEBIT: Potongan Pembelian (jika ada diskon sebelumnya)
            if ($totalPotongan > 0 && $discountCoaId) {
                JurnalDetail::create([
                    'jurnal_id'  => $reversalJurnal->id,
                    'account_id' => $discountCoaId,
                    'debit'      => $totalPotongan,
                    'kredit'     => 0,
                    'keterangan' => "Pembalikan Diskon/Potongan Utang (Ref: {$purchasePayment->nomor_pembayaran})",
                ]);
            }

            // KREDIT: Utang Usaha (AP) (Utang timbul/terbuka kembali)
            JurnalDetail::create([
                'jurnal_id'  => $reversalJurnal->id,
                'account_id' => $apCoaId,
                'debit'      => 0,
                'kredit'     => $totalDebitAp,
                'keterangan' => "Pembalikan Pelunasan Utang Usaha {$supplierName} (Ref: {$purchasePayment->nomor_pembayaran})",
            ]);

            // 3. Rollback Purchase Invoice Status if they were marked Paid
            $purchasePayment->load('details.invoice');
            foreach ($purchasePayment->details as $det) {
                $inv = $det->invoice;
                if ($inv && $inv->status === 'Paid') {
                    // Re-evaluate remaining balance after this payment cancelled
                    $inv->status = 'Posted';
                    $inv->save();
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
        abort_unless(
            auth()->user()->can('batalkan-pembayaran-utang') || auth()->user()->can('edit-pembayaran-utang'),
            403,
            'Anda tidak memiliki hak akses untuk menghapus draf pembayaran utang.'
        );

        // Strict Draft Isolation: Status Draft HANYA bisa dihapus oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)auth()->id()) {
            return response()->json([
                'message' => 'Dokumen pembayaran utang berstatus Draft hanya dapat dihapus oleh pembuat dokumen ini.'
            ], 403);
        }

        if ($purchasePayment->status === 'Diajukan') {
            return response()->json(['message' => 'Pembayaran sedang diajukan untuk persetujuan (approval) dan tidak dapat dihapus!'], 400);
        }

        if (!in_array($purchasePayment->status, ['Draft', 'Ditolak'])) {
            return response()->json(['message' => 'Pembayaran yang sudah diposting tidak dapat dihapus!'], 400);
        }

        try {
            DB::beginTransaction();

            $purchasePayment->details()->delete();
            $purchasePayment->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Draft Pembayaran Utang {$purchasePayment->nomor_pembayaran} berhasil dihapus!",
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

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $isPrivileged    = $user->hasRole('Super Admin') || $user->hasRole('Owner');

        // Multi-company isolation check
        if ($activeCompanyId && $purchasePayment->company_id && strval($activeCompanyId) !== strval($purchasePayment->company_id) && !$isPrivileged) {
            abort(403, 'Anda tidak memiliki akses ke dokumen pembayaran utang perusahaan lain.');
        }

        // Multi-branch isolation check
        if ($activeBranchId && $purchasePayment->branch_id && strval($activeBranchId) !== strval($purchasePayment->branch_id) && !$isPrivileged) {
            abort(403, 'Anda tidak memiliki akses ke dokumen pembayaran utang cabang lain.');
        }

        // Strict Draft Isolation: Status Draft HANYA bisa dicetak oleh user pembuat dokumennya
        if ($purchasePayment->status === 'Draft' && (int)$purchasePayment->created_by !== (int)$user->id) {
            abort(403, 'Dokumen pembayaran utang berstatus Draft hanya dapat dicetak oleh pembuat dokumen ini.');
        }   

        $purchasePayment->load([
            'supplier',
            'kasBank.coa',
            'paymentMethod',
            'currency',
            'branch',
            'company',
            'creator',
            'poster',
            'submitter',
            'approver',
            'details.invoice.po.currency',
        ]);

        $activeCompany = $activeCompanyId 
            ? \App\Models\Company::find($activeCompanyId) 
            : ($purchasePayment->company ?? $user->companies()->first());

        $activeBranch = $activeBranchId 
            ? \App\Models\Branch::find($activeBranchId) 
            : ($purchasePayment->branch ?? null);

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

        $activeBranchId  = session('active_branch_id') ?? $payment->branch_id;
        $activeCompanyId = session('active_company_id') ?? $payment->company_id ?? auth()->user()?->company_id;

        $totalBayar    = (float) $payment->total_bayar;
        $totalPotongan = (float) $payment->total_potongan;
        $totalDebitAp  = $totalBayar + $totalPotongan;

        // ── 0. Security Balance Validation (Anti-Minus Check) ───────────────
        if ($payment->kasBank) {
            $currentSaldo = (float) $payment->kasBank->saldo_berjalan;
            if ($currentSaldo < $totalBayar) {
                $kekurangan = $totalBayar - $currentSaldo;
                throw new \Exception("Saldo Kas/Bank '{$payment->kasBank->nama_akun}' tidak mencukupi! Saldo tersedia: Rp " . number_format($currentSaldo, 2, ',', '.') . ", Total pembayaran: Rp " . number_format($totalBayar, 2, ',', '.') . " (Kurang Rp " . number_format($kekurangan, 2, ',', '.') . "). Transaksi ditolak demi keamanan.");
            }
        }

        // ── 1. COA Resolution ──────────────────────────────────────────────
        // AP / Utang Usaha COA (DEBIT)
        $apCoaId = AccountMappingHelper::getCoaId('default', 'default_ap')
            ?? AccountMappingHelper::getCoaId('default', 'accounts_payable', 'ap')
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
        if ($totalPotongan > 0) {
            $discountCoaId = AccountMappingHelper::getCoaId('default', 'purchase_discount')
                ?? AccountMappingHelper::getCoaId('default', 'potongan_pembelian')
                ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function ($q) {
                    $q->where('code', 'like', '5102%')->orWhere('code', 'like', '420%')->orWhere('name', 'like', '%Potongan Pembelian%')->orWhere('name', 'like', '%Diskon Pembelian%');
                })->value('id');
        }

        // Fallbacks
        $firstCoa = ChartOfAccount::forActiveCompany()->detailOnly()->first();
        if (!$apCoaId) $apCoaId = $firstCoa?->id;
        if (!$kasBankCoaId) $kasBankCoaId = $firstCoa?->id;
        if ($totalPotongan > 0 && !$discountCoaId) $discountCoaId = $firstCoa?->id;

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

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $payment->tanggal_bayar,
            'referensi_tipe' => 'Purchase Payment',
            'referensi_id'   => $payment->id,
            'company_id'     => $activeCompanyId,
            'branch_id'      => $activeBranchId,
            'total_debit'    => $totalDebitAp,
            'total_kredit'   => $totalDebitAp,
            'keterangan'     => "Pembayaran Utang ke {$supplierName} ({$payment->nomor_pembayaran})",
            'created_by'     => auth()->id(),
        ]);

        // DEBIT: Utang Usaha (AP)
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $apCoaId,
            'debit'      => $totalDebitAp,
            'kredit'     => 0,
            'keterangan' => "Pelunasan Utang Usaha {$supplierName} ({$payment->nomor_pembayaran})",
        ]);

        // KREDIT: Kas / Bank
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $kasBankCoaId,
            'debit'      => 0,
            'kredit'     => $totalBayar,
            'keterangan' => "Pengeluaran Kas/Bank ({$payment->nomor_pembayaran})",
        ]);

        // KREDIT: Potongan Pembelian (jika ada)
        if ($totalPotongan > 0 && $discountCoaId) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $discountCoaId,
                'debit'      => 0,
                'kredit'     => $totalPotongan,
                'keterangan' => "Potongan/Diskon Pelunasan Utang ({$payment->nomor_pembayaran})",
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
        $saldoSesudah = $saldoSebelum - $totalBayar;

        KasMutasi::create([
            'nomor_transaksi' => $nomorTransaksi,
            'kas_bank_id'     => $payment->kas_bank_id,
            'tipe'            => 'Keluar',
            'kategori'        => 'Pembayaran Supplier',
            'lawan_coa_id'    => $apCoaId,
            'jumlah'          => $totalBayar,
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
            'company_id'      => $activeCompanyId,
            'branch_id'       => $activeBranchId,
            'created_by'      => auth()->id(),
        ]);

        // Reduce KasBank running balance
        if ($payment->kasBank) {
            $payment->kasBank->saldo_berjalan -= $totalBayar;
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
}
