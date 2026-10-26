<?php

namespace App\Http\Controllers;

use App\Models\SalesPayment;
use App\Models\SalesPaymentDetail;
use App\Models\SalesInvoice;
use App\Models\MasterCustomer;
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
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\WhatsAppGatewayController;

class SalesPaymentController extends Controller
{
    /**
     * Endpoint super cepat (sub-millisecond) untuk real-time status & fingerprint sync.
    /**
     * Cari pembayaran dengan isolasi multi-tenant yang ketat (Cabang vs PT Pusat)
     */
    private function findPaymentForTenant($id, array $withRelations = []): SalesPayment
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesPayment::query();
        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        return $query->findOrFail($id);
    }

    /**
     * Live Polling: Cek apakah ada perubahan data di tabel sales_payments
     * Menggunakan index database tanpa rendering HTML sehingga server tetap sangat ringan.
     */
    public function checkSync(Request $request)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $userId          = auth()->id();

        $query = SalesPayment::query();
        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        // Strict Draft Isolation: Status Draft HANYA bisa dilihat oleh pembuatnya
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('status', '!=', 'Draft')
                  ->orWhere('created_by', $userId);
            });
        }

        $meta = $query->selectRaw('MAX(updated_at) as last_updated, MAX(id) as max_id')->first();
        $fingerprint = md5(($meta->last_updated ?? '0') . '_' . ($meta->max_id ?? '0'));

        return response()->json([
            'fingerprint'  => $fingerprint,
            'last_updated' => $meta->last_updated,
        ]);
    }

    /**
     * Halaman Utama & DataTables Penerimaan Pembayaran Penjualan
     */
    public function index(Request $request)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $userId          = auth()->id();

        if ($request->ajax()) {
            $query = SalesPayment::query()
                ->select([
                    'sales_payments.id',
                    'sales_payments.nomor_pembayaran',
                    'sales_payments.tanggal_bayar',
                    'sales_payments.customer_id',
                    'sales_payments.kas_bank_id',
                    'sales_payments.payment_method_id',
                    'sales_payments.nomor_referensi',
                    'sales_payments.branch_id',
                    'sales_payments.company_id',
                    'sales_payments.currency_id',
                    'sales_payments.kurs',
                    'sales_payments.total_faktur',
                    'sales_payments.total_potongan',
                    'sales_payments.total_bayar',
                    'sales_payments.status',
                    'sales_payments.keterangan',
                    'sales_payments.lampiran',
                    'sales_payments.created_by',
                    'sales_payments.posted_by',
                    'sales_payments.created_at',
                    'sales_payments.updated_at'
                ])
                ->with([
                    'customer:id,nama_customer,nama_perusahaan,kode_customer',
                    'kasBank:id,nama_akun,nomor_rekening,tipe',
                    'paymentMethod:id,nama_metode',
                    'branch:id,nama_cabang',
                    'currency:id,kode_currency,simbol',
                    'creator:id,nama_lengkap,username',
                    'jurnal:id,nomor_jurnal,referensi_tipe,referensi_id,no_tender',
                    'details.invoice:id,nomor_faktur,tender_id',
                    'details.invoice.tender:id,nomor_kontrak,nama_tender',
                    'details.invoice.salesOrder.tender:id,nomor_kontrak,nama_tender',
                    'details.invoice.deliveryOrder.salesOrder.tender:id,nomor_kontrak,nama_tender'
                ]);

            if (!empty($activeBranchId)) {
                $query->where('sales_payments.branch_id', $activeBranchId);
            } else {
                $query->whereNull('sales_payments.branch_id')->where('sales_payments.company_id', $activeCompanyId);
            }

            // Strict Draft Isolation
            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('sales_payments.status', '!=', 'Draft')
                      ->orWhere('sales_payments.created_by', $userId);
                });
            }

            // Filter Customer
            if ($request->filled('customer_id')) {
                $query->where('sales_payments.customer_id', $request->customer_id);
            }

            // Filter Kas/Bank
            if ($request->filled('kas_bank_id')) {
                $query->where('sales_payments.kas_bank_id', $request->kas_bank_id);
            }

            // Filter Status
            if ($request->filled('status')) {
                $query->where('sales_payments.status', $request->status);
            }

            // Filter Tanggal
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('sales_payments.tanggal_bayar', [$request->start_date, $request->end_date]);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->orderColumn('nomor_pembayaran', 'sales_payments.nomor_pembayaran $1')
                ->orderColumn('tanggal_bayar', 'sales_payments.tanggal_bayar $1')
                ->orderColumn('total_bayar', 'sales_payments.total_bayar $1')
                ->orderColumn('status', 'sales_payments.status $1')
                ->filterColumn('nomor_pembayaran', function ($q, $keyword) {
                    $q->where('sales_payments.nomor_pembayaran', 'like', "%{$keyword}%");
                })
                ->filterColumn('tanggal_bayar', function ($q, $keyword) {
                    $q->where('sales_payments.tanggal_bayar', 'like', "%{$keyword}%");
                })
                ->filterColumn('status', function ($q, $keyword) {
                    $q->where('sales_payments.status', 'like', "%{$keyword}%");
                })
                ->editColumn('nomor_pembayaran', function ($row) {
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-primary font-monospace">' . e($row->nomor_pembayaran) . '</span>
                                <small class="text-muted">' . ($row->branch?->nama_cabang ?? '-') . '</small>
                            </div>';
                })
                ->editColumn('tanggal_bayar', function ($row) {
                    return Carbon::parse($row->tanggal_bayar)->format('d M Y');
                })
                ->editColumn('customer', function ($row) {
                    $instansi = trim($row->customer?->nama_perusahaan ?? '');
                    $person   = trim($row->customer?->nama_customer ?? '');

                    if ($instansi && $person && strcasecmp($instansi, $person) !== 0) {
                        return '<div class="fw-bold text-body-emphasis">' . e($instansi) . '</div>'
                            . '<small class="text-body-secondary d-block font-monospace" style="font-size: 0.76rem;"><i class="bi bi-person me-1"></i>' . e($person) . '</small>';
                    }

                    $mainName = $instansi ?: ($person ?: '-');
                    return '<div class="fw-semibold text-body-emphasis">' . e($mainName) . '</div>';
                })
                ->filterColumn('customer', function ($q, $keyword) {
                    $q->whereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('nama_perusahaan', 'like', "%{$keyword}%")
                           ->orWhere('nama_customer', 'like', "%{$keyword}%")
                           ->orWhere('kode_customer', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('customer', function ($q, $order) {
                    $q->orderBy(
                        MasterCustomer::select(DB::raw('COALESCE(NULLIF(nama_perusahaan, ""), nama_customer)'))
                            ->whereColumn('master_customers.id', 'sales_payments.customer_id')
                            ->limit(1),
                        $order
                    );
                })
                ->editColumn('kas_bank', function ($row) {
                    $nama = $row->kasBank?->nama_akun ?? '-';
                    $tipe = $row->kasBank?->tipe ? '<span class="badge bg-info-subtle text-info me-1">' . e($row->kasBank->tipe) . '</span>' : '';
                    return $tipe . ' ' . e($nama);
                })
                ->filterColumn('kas_bank', function ($q, $keyword) {
                    $q->whereHas('kasBank', function ($kq) use ($keyword) {
                        $kq->where('nama_akun', 'like', "%{$keyword}%")
                           ->orWhere('nomor_rekening', 'like', "%{$keyword}%")
                           ->orWhere('tipe', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('kas_bank', function ($q, $order) {
                    $q->orderBy(
                        KasBank::select('nama_akun')
                            ->whereColumn('kas_banks.id', 'sales_payments.kas_bank_id')
                            ->limit(1),
                        $order
                    );
                })
                ->editColumn('faktur_terkait', function ($row) {
                    $count = $row->details->count();
                    if ($count === 0) return '<span class="text-muted">-</span>';
                    $first = $row->details->first()->invoice?->nomor_faktur ?? '-';
                    $fakturBadge = $count === 1
                        ? '<span class="badge bg-light text-dark border">' . e($first) . '</span>'
                        : '<span class="badge bg-light text-dark border me-1">' . e($first) . '</span> <span class="badge bg-secondary">+' . ($count - 1) . ' lainnya</span>';

                    $tenders = collect();
                    foreach ($row->details as $d) {
                        $t = $d->invoice?->tender ?? $d->invoice?->salesOrder?->tender ?? $d->invoice?->deliveryOrder?->salesOrder?->tender;
                        if ($t) {
                            $lbl = !empty($t->nomor_kontrak) && $t->nomor_kontrak !== '-'
                                ? ($t->nomor_kontrak . (!empty($t->nama_tender) ? ' - ' . $t->nama_tender : ''))
                                : ($t->nama_tender ?: ('Tender #' . $t->id));
                            if ($lbl) $tenders->push($lbl);
                        }
                    }
                    if ($tenders->isEmpty() && $row->jurnal && !empty($row->jurnal->no_tender)) {
                        $tenders->push($row->jurnal->no_tender);
                    }
                    $tenderStr = $tenders->unique()->filter()->implode(', ');
                    $tenderBadge = !empty($tenderStr)
                        ? '<div class="mt-1"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-briefcase me-1"></i>' . e($tenderStr) . '</span></div>'
                        : '';

                    return $fakturBadge . $tenderBadge;
                })
                ->filterColumn('faktur_terkait', function ($q, $keyword) {
                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->whereHas('details.invoice', function ($iq) use ($keyword) {
                            $iq->where('nomor_faktur', 'like', "%{$keyword}%")
                               ->orWhereHas('tender', function ($tq) use ($keyword) {
                                   $tq->where('nomor_kontrak', 'like', "%{$keyword}%")
                                      ->orWhere('nama_tender', 'like', "%{$keyword}%");
                               });
                        })
                        ->orWhereHas('jurnal', function ($jq) use ($keyword) {
                            $jq->where('no_tender', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->editColumn('total_bayar', function ($row) {
                    $lebihBayar = 0;
                    if ($row->details && $row->details->count() > 0) {
                        $lebihBayar = $row->details->sum(function ($d) {
                            return max(0, ((float)$d->jumlah_bayar + (float)$d->potongan) - (float)$d->sisa_piutang);
                        });
                    }
                    if ($lebihBayar <= 0.01) {
                        $netTagihan = max(0, (float)$row->total_faktur - (float)$row->total_potongan);
                        $lebihBayar = max(0, (float)$row->total_bayar - $netTagihan);
                    }

                    $html = '<div class="text-end">';
                    $html .= '<div class="fw-bold font-monospace text-success" style="font-size: 0.95rem;">Rp ' . number_format($row->total_bayar, 2, ',', '.') . '</div>';
                    if ($row->total_potongan > 0) {
                        $html .= '<div class="text-danger small font-monospace" style="font-size: 0.72rem;">Potongan: Rp ' . number_format($row->total_potongan, 2, ',', '.') . '</div>';
                    }
                    if ($lebihBayar > 0.01) {
                        $html .= '<div class="mt-0.5"><span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-0.5" style="font-size: 0.72rem;"><i class="bi bi-arrow-up-right-circle me-1"></i>Lebih: Rp ' . number_format($lebihBayar, 2, ',', '.') . '</span></div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    $status = $row->status;
                    $badgeStyle = match ($status) {
                        'Posted'    => 'background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);',
                        'Diajukan'  => 'background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); box-shadow: 0 0 10px rgba(245, 158, 11, 0.2);',
                        'Draft'     => 'background: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3);',
                        'Cancelled' => 'background: rgba(244, 63, 94, 0.12); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.35); box-shadow: 0 0 10px rgba(244, 63, 94, 0.2);',
                        default     => 'background: rgba(255, 255, 255, 0.08); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.15);',
                    };
                    $label = match ($status) {
                        'Posted'    => '<i class="bi bi-check2-circle me-1"></i> Posted',
                        'Diajukan'  => '<i class="bi bi-hourglass-split me-1"></i> Menunggu',
                        'Draft'     => '<i class="bi bi-pencil-square me-1"></i> Draft',
                        'Cancelled' => '<i class="bi bi-x-circle me-1"></i> Batal',
                        default     => $status,
                    };
                    return '<span class="badge rounded-pill px-2.5 py-1.5 fw-semibold" style="' . $badgeStyle . '">' . $label . '</span>';
                })
                ->addColumn('jurnal', function ($row) {
                    $user = auth()->user();
                    $canViewJurnal = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-sales-payment');
                    if ($row->jurnal && $canViewJurnal) {
                        return '<div class="d-flex align-items-center justify-content-center">
                                    <button type="button" class="jurnal-cinematic-badge btn-view-jurnal" data-id="' . $row->id . '" title="Lihat Jurnal: ' . e($row->jurnal->nomor_jurnal) . '" data-bs-toggle="tooltip">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </button>
                                </div>';
                    }
                    return '<span class="text-body-tertiary small font-monospace">-</span>';
                })
                ->addColumn('actions', function ($row) {
                    $user = auth()->user();
                    $canView        = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-sales-payment');
                    $canEdit        = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('edit-sales-payment');
                    $canSubmit      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-sales-payment') || $user->can('edit-sales-payment');
                    $canApprove     = $user->hasAnyRole(['Super Admin', 'Owner', 'Manager Finance']) || $user->can('approve-sales-payment');
                    $canDeleteDraft = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('hapus-sales-payment');
                    $canCancel      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('batalkan-sales-payment');
                    $canPrint       = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('cetak-sales-payment');

                    $actions = '<div class="d-flex align-items-center justify-content-center">';
                    $actions .= '<div class="action-cinematic-group">';

                    // Detail Button
                    if ($canView) {
                        $actions .= '<button type="button" class="btn-action-cinematic action-btn-view btn-detail-payment" data-id="' . $row->id . '" title="Detail Pembayaran" data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                     </button>';
                    }
                    // Print Kwitansi Button
                    if ($canPrint) {
                        $actions .= '<a href="' . route('modul_marketing.pembayaran.print_kwitansi', $row->id) . '" target="_blank" class="btn-action-cinematic action-btn-print" title="Cetak Kwitansi" data-bs-toggle="tooltip">
                                        <i class="bi bi-printer"></i>
                                     </a>';
                    }

                    if ($row->status === 'Draft') {
                        // Edit Draft
                        if ($canEdit) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-edit btn-edit-payment" data-id="' . $row->id . '" title="Edit Draft Pembayaran" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                         </button>';
                        }
                        // Ajukan Pembayaran
                        if ($canSubmit) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-submit btn-submit-payment" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_pembayaran) . '" title="Ajukan Pembayaran ke Atasan" data-bs-toggle="tooltip">
                                            <i class="bi bi-send"></i>
                                         </button>';
                        }
                        // Hapus Draft
                        if ($canDeleteDraft) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-delete btn-delete-payment" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_pembayaran) . '" title="Hapus Draft" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                         </button>';
                        }
                    } elseif ($row->status === 'Diajukan') {
                        // Edit saat status Menunggu ACC
                        if ($canEdit) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-edit btn-edit-payment" data-id="' . $row->id . '" title="Edit Pembayaran (Menunggu ACC)" data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                         </button>';
                        }
                        // Konfirmasi & Posting (ACC oleh Atasan)
                        if ($canApprove) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-post btn-post-payment" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_pembayaran) . '" title="Konfirmasi & Posting (ACC)" data-bs-toggle="tooltip">
                                            <i class="bi bi-check2-circle"></i>
                                         </button>';
                        }
                        // Hapus / Tolak Pengajuan
                        if ($canDeleteDraft) {
                            $actions .= '<button type="button" class="btn-action-cinematic action-btn-delete btn-delete-payment" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_pembayaran) . '" title="Tolak / Hapus Pengajuan" data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                         </button>';
                        }
                    }
                    $actions .= '</div>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['nomor_pembayaran', 'customer', 'kas_bank', 'faktur_terkait', 'total_bayar', 'status', 'jurnal', 'actions'])
                ->make(true);
        }

        // Summary Stats
        $statsQuery = SalesPayment::query()
            ->where('status', 'Posted')
            ->where(function ($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('branch_id', $activeBranchId);
                } else {
                    $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
                }
            });

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();
        $today        = now()->toDateString();

        // Konsolidasi 4 query kalkulasi dengan indeks komposit bulan berjalan (menghindari scan jutaan baris historis)
        $stats = $statsQuery
            ->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth])
            ->selectRaw("
                COALESCE(SUM(total_bayar), 0) as total_bulan_ini,
                COALESCE(SUM(CASE WHEN tanggal_bayar = ? THEN total_bayar ELSE 0 END), 0) as total_hari_ini,
                COALESCE(SUM(total_potongan), 0) as total_potongan,
                COUNT(id) as count_trx
            ", [$today])
            ->first();

        $totalBulanIni = (float) ($stats->total_bulan_ini ?? 0);
        $totalHariIni  = (float) ($stats->total_hari_ini ?? 0);
        $countTrx      = (int) ($stats->count_trx ?? 0);
        $totalPotongan = (float) ($stats->total_potongan ?? 0);

        // Master Data dropdowns - Customer scoped to active company
        $customers = MasterCustomer::forActiveCompany()->active()->orderBy('nama_customer')->get(['id', 'nama_customer', 'kode_customer']);
        
        // Kas/Bank Tujuan - Strict Isolation: hanya milik Cabang jika aktif, atau PT Pusat jika di Pusat
        $kasBanksQuery = KasBank::where('is_active', true);
        if (!empty($activeBranchId)) {
            $kasBanksQuery->where('branch_id', $activeBranchId);
        } else {
            $kasBanksQuery->whereNull('branch_id');
            if (!empty($activeCompanyId)) {
                $kasBanksQuery->where('company_id', $activeCompanyId);
            }
        }
        $kasBanks = $kasBanksQuery->orderBy('nama_akun')->get(['id', 'nama_akun', 'nomor_rekening', 'tipe', 'saldo_berjalan']);

        $paymentMethods = PaymentMethod::where('status', 'Active')->orWhereNull('status')->orderBy('nama_metode')->get();
        $currencies = MasterCurrency::where('status', 'Active')->orWhereNull('status')->orderBy('kode_currency')->get();

        // Daftar Faktur Penjualan yang belum lunas (Unpaid & Partial) untuk tenant aktif
        $unpaidInvoicesQuery = SalesInvoice::query()
            ->whereIn('status', ['Unpaid', 'Partial']);

        if (!empty($activeBranchId)) {
            $unpaidInvoicesQuery->where('branch_id', $activeBranchId);
        } else {
            $unpaidInvoicesQuery->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $unpaidInvoices = $unpaidInvoicesQuery
            ->with(['customer:id,nama_customer,kode_customer', 'paymentDetails.payment'])
            ->orderBy('tanggal_faktur', 'desc')
            ->take(50)
            ->get()
            ->filter(function($inv) {
                return (float)$inv->remaining_balance > 0;
            })
            ->values();

        $potonganCoas = ChartOfAccount::forActiveCompany()
            ->detailOnly()
            ->active()
            ->orderBy('code')
            ->get();

        $defaultPotonganCoaId = AccountMappingHelper::getCoaId('sales', 'sales_discount', 'default_sales_discount', $activeCompanyId);
        if (!$defaultPotonganCoaId) {
            $defaultPotonganCoa = $potonganCoas->first(function($c) {
                return str_contains(strtolower($c->name), 'potongan penjualan') 
                    || str_contains(strtolower($c->name), 'diskon penjualan');
            });
            $defaultPotonganCoaId = $defaultPotonganCoa?->id;
        }

        return view('modul_marketing.pembayaran.index', compact(
            'totalBulanIni',
            'totalHariIni',
            'countTrx',
            'totalPotongan',
            'customers',
            'kasBanks',
            'paymentMethods',
            'currencies',
            'unpaidInvoices',
            'potonganCoas',
            'defaultPotonganCoaId'
        ));
    }

    /**
     * AJAX: Ambil daftar faktur penjualan yang belum lunas (Unpaid & Partial) untuk customer tertentu
     */
    public function getUnpaidInvoices(Request $request, $customerId)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['Unpaid', 'Partial']);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $invoices = $query
            ->with(['paymentDetails.payment'])
            ->orderBy('tanggal_faktur', 'asc')
            ->get();

        $data = $invoices->map(function ($inv) {
            $paid = (float) $inv->paid_amount;
            $remaining = (float) $inv->remaining_balance;
            return [
                'id'                   => $inv->id,
                'nomor_faktur'         => $inv->nomor_faktur,
                'tanggal_faktur'       => Carbon::parse($inv->tanggal_faktur)->format('d M Y'),
                'tanggal_jatuh_tempo'  => $inv->tanggal_jatuh_tempo ? Carbon::parse($inv->tanggal_jatuh_tempo)->format('d M Y') : '-',
                'grand_total'          => (float) $inv->grand_total,
                'grand_total_formated' => 'Rp ' . number_format($inv->grand_total, 2, ',', '.'),
                'paid_amount'          => $paid,
                'paid_formated'        => 'Rp ' . number_format($paid, 2, ',', '.'),
                'remaining_balance'    => $remaining,
                'remaining_formated'   => 'Rp ' . number_format($remaining, 2, ',', '.'),
                'status'               => $inv->status,
            ];
        })->filter(function($item) {
            return $item['remaining_balance'] > 0;
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * AJAX: Ambil semua daftar faktur penjualan yang belum lunas untuk tenant aktif (bisa filter customer/search)
     */
    public function getAllUnpaidInvoices(Request $request)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesInvoice::query()
            ->whereIn('status', ['Unpaid', 'Partial']);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $query->with(['customer:id,nama_customer,kode_customer', 'paymentDetails.payment'])
            ->orderBy('tanggal_faktur', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_faktur', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('nama_customer', 'like', "%{$search}%")
                         ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        // Batasi maksimal 100 data untuk modal lookup agar performa responsif dan bebas memory leak
        $invoices = $query->limit(100)->get();

        $data = $invoices->map(function ($inv) {
            $paid = (float) $inv->paid_amount;
            $remaining = (float) $inv->remaining_balance;
            return [
                'id'                   => $inv->id,
                'nomor_faktur'         => $inv->nomor_faktur,
                'tanggal_faktur'       => Carbon::parse($inv->tanggal_faktur)->format('d M Y'),
                'tanggal_jatuh_tempo'  => $inv->tanggal_jatuh_tempo ? Carbon::parse($inv->tanggal_jatuh_tempo)->format('d M Y') : '-',
                'customer_id'          => $inv->customer_id,
                'customer_name'        => $inv->customer?->nama_customer ?? '-',
                'customer_code'        => $inv->customer?->kode_customer ?? '',
                'grand_total'          => (float) $inv->grand_total,
                'grand_total_formated' => 'Rp ' . number_format($inv->grand_total, 2, ',', '.'),
                'paid_amount'          => $paid,
                'paid_formated'        => 'Rp ' . number_format($paid, 2, ',', '.'),
                'remaining_balance'    => $remaining,
                'remaining_formated'   => 'Rp ' . number_format($remaining, 2, ',', '.'),
                'status'               => $inv->status,
            ];
        })->filter(function ($item) {
            return $item['remaining_balance'] > 0;
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Simpan data penerimaan pembayaran piutang
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mencatat penerimaan pembayaran baru.',
            ], 403);
        }

        if ($request->input('status') === 'Posted') {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan pembayaran harus disimpan sebagai Draft atau Diajukan terlebih dahulu sebelum dapat disetujui (ACC / Posting).',
            ], 422);
        }

        $request->validate([
            'tanggal_bayar'     => 'required|date',
            'customer_id'       => 'required|exists:master_customers,id',
            'kas_bank_id'       => 'required|exists:kas_banks,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'nomor_referensi'   => 'nullable|string|max:100',
            'keterangan'        => 'nullable|string',
            'status'            => 'required|in:Draft,Diajukan',
            'lampiran'          => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'invoices'          => 'required|array|min:1',
            'invoices.*.id'     => 'required|distinct|exists:sales_invoices,id',
            'invoices.*.bayar'  => 'required|numeric|min:0',
            'invoices.*.potongan' => 'nullable|numeric|min:0',
            'potongan_coa_id'   => 'nullable|exists:chart_of_accounts,id',
        ], [
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'customer_id.required'   => 'Customer wajib dipilih.',
            'kas_bank_id.required'   => 'Akun Kas / Bank tujuan wajib dipilih.',
            'lampiran.required'      => 'Dokumen atau foto bukti transfer pembayaran wajib disertakan.',
            'lampiran.max'           => 'Ukuran berkas bukti transfer / lampiran maksimal 10 MB.',
            'lampiran.mimes'         => 'Format berkas bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'invoices.required'      => 'Minimal pilih satu faktur untuk dibayar.',
            'invoices.*.id.distinct' => 'Terdapat faktur duplikat dalam rincian pembayaran.',
            'invoices.*.bayar.min'   => 'Nominal bayar tidak boleh negatif.',
        ]);

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        // Strict Mutual Exclusivity:
        // Cabang aktif: branch_id = $activeBranchId, company_id = NULL
        // PT Pusat aktif: branch_id = NULL, company_id = $activeCompanyId
        $saveBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
        $saveCompanyId = !empty($activeBranchId) ? null : $activeCompanyId;

        // Pastikan Kas/Bank tujuan sesuai dengan tenant aktif
        $kasBankQuery = KasBank::query();
        if (!empty($saveBranchId)) {
            $kasBankQuery->where('branch_id', $saveBranchId);
        } else {
            $kasBankQuery->whereNull('branch_id')->where('company_id', $saveCompanyId);
        }
        $kasBank = $kasBankQuery->findOrFail($request->kas_bank_id);

        DB::beginTransaction();
        try {
            $nomorPembayaran = SalesPayment::generateNomorPembayaran($saveCompanyId, $saveBranchId, $request->tanggal_bayar);

            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $this->compressAndSaveFile($request->file('lampiran'));
            }

            $totalFaktur   = 0;
            $totalPotongan = 0;
            $totalBayar    = 0;

            foreach ($request->invoices as $invItem) {
                $totalBayar    += round((float) $invItem['bayar'], 2);
                $totalPotongan += round((float) ($invItem['potongan'] ?? 0), 2);
            }
            $totalBayar    = round($totalBayar, 2);
            $totalPotongan = round($totalPotongan, 2);

            $payment = SalesPayment::create([
                'nomor_pembayaran'  => $nomorPembayaran,
                'tanggal_bayar'     => $request->tanggal_bayar,
                'customer_id'       => $request->customer_id,
                'kas_bank_id'       => $request->kas_bank_id,
                'payment_method_id' => $request->payment_method_id,
                'nomor_referensi'   => $request->nomor_referensi,
                'branch_id'         => $saveBranchId,
                'company_id'        => $saveCompanyId,
                'kurs'              => 1,
                'total_faktur'      => 0, // diupdate setelah detail
                'total_potongan'    => $totalPotongan,
                'potongan_coa_id'   => $request->potongan_coa_id,
                'total_bayar'       => $totalBayar,
                'status'            => $request->status,
                'keterangan'        => $request->keterangan,
                'lampiran'          => $lampiranPath,
                'posted_at'         => $request->status === 'Posted' ? now() : null,
                'posted_by'         => $request->status === 'Posted' ? auth()->id() : null,
            ]);

            $calculatedTotalFaktur = 0;

            foreach ($request->invoices as $invItem) {
                $invoiceQuery = SalesInvoice::with('paymentDetails.payment');
                if (!empty($saveBranchId)) {
                    $invoiceQuery->where('branch_id', $saveBranchId);
                } else {
                    $invoiceQuery->whereNull('branch_id')->where('company_id', $saveCompanyId);
                }
                $invoice = $invoiceQuery->lockForUpdate()->findOrFail($invItem['id']);

                // Proteksi integritas: Pastikan faktur benar-benar milik pelanggan yang dipilih
                if ((int) $invoice->customer_id !== (int) $request->customer_id) {
                    throw new \Exception("Faktur {$invoice->nomor_faktur} bukan milik pelanggan yang dipilih.");
                }

                $sisaPiutang = round((float) $invoice->remaining_balance, 2);
                if ($sisaPiutang <= 0.009) {
                    throw new \Exception("Faktur {$invoice->nomor_faktur} sudah lunas atau sudah memiliki alokasi pembayaran penuh.");
                }
                $bayar = round((float) $invItem['bayar'], 2);
                $potongan = round((float) ($invItem['potongan'] ?? 0), 2);

                if (($bayar + $potongan) <= 0) {
                    throw new \Exception("Setiap faktur yang dipilih harus memiliki nominal alokasi bayar atau potongan lebih besar dari 0.");
                }

                if (($bayar + $potongan) > ($sisaPiutang + 0.009)) {
                    $totalInputFmt = number_format($bayar + $potongan, 2, ',', '.');
                    $sisaPiutangFmt = number_format($sisaPiutang, 2, ',', '.');
                    throw new \Exception("Nominal bayar + potongan untuk Faktur {$invoice->nomor_faktur} (Rp {$totalInputFmt}) melebihi sisa piutang (Rp {$sisaPiutangFmt}).");
                }

                $sisaAkhir = max(0, round($sisaPiutang - ($bayar + $potongan), 2));

                SalesPaymentDetail::create([
                    'sales_payment_id' => $payment->id,
                    'sales_invoice_id' => $invoice->id,
                    'total_faktur'     => round((float) $invoice->grand_total, 2),
                    'sisa_piutang'     => $sisaPiutang,
                    'jumlah_bayar'     => $bayar,
                    'potongan'         => $potongan,
                    'sisa_akhir'       => $sisaAkhir,
                    'catatan'          => $invItem['catatan'] ?? null,
                ]);

                $calculatedTotalFaktur += round((float) $invoice->grand_total, 2);

                // Jika status Posted, update status invoice
                if ($payment->status === 'Posted') {
                    if ($sisaAkhir <= 0.01) {
                        $invoice->status = 'Paid';
                        $invoice->sisa_tagihan = 0;
                    } else {
                        $invoice->status = 'Partial';
                        $invoice->sisa_tagihan = $sisaAkhir;
                    }
                    $invoice->save();
                }
            }

            $payment->total_faktur = $calculatedTotalFaktur;
            $payment->save();

            // Jika status Posted, buat Jurnal Otomatis & KasMutasi
            if ($payment->status === 'Posted') {
                $this->createAutomaticJournalAndCashMutation($payment);
            }

            DB::commit();

            // Trigger WhatsApp Notification
            if ($payment->status === 'Diajukan') {
                WhatsAppGatewayController::sendSalesPaymentSubmissionNotification($payment);
            } elseif ($payment->status === 'Posted') {
                WhatsAppGatewayController::sendSalesPaymentPostedNotification($payment, auth()->user());
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ' . $payment->nomor_pembayaran . ' berhasil disimpan!',
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan SalesPayment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan data untuk Modal Edit (Khusus status Draft / belum Posting)
     */
    public function edit($id)
    {
        $payment = $this->findPaymentForTenant($id, [
            'customer',
            'kasBank',
            'paymentMethod',
            'branch',
            'details.invoice'
        ]);

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengedit pembayaran ini.',
            ], 403);
        }

        if ($payment->status === 'Posted') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah diposting tidak dapat diedit!',
            ], 422);
        }

        if ($payment->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah dibatalkan tidak dapat diedit!',
            ], 422);
        }

        // Ambil semua faktur belum lunas untuk customer ini
        // Termasuk faktur yang sudah dialokasikan di pembayaran draft ini!
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $invoicesQuery = SalesInvoice::query()
            ->where('customer_id', $payment->customer_id)
            ->whereIn('status', ['Unpaid', 'Partial', 'Paid'])
            ->with(['paymentDetails.payment'])
            ->orderBy('tanggal_faktur', 'desc');

        if (!empty($activeBranchId)) {
            $invoicesQuery->where('branch_id', $activeBranchId);
        } else {
            $invoicesQuery->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $allInvoices = $invoicesQuery->get();

        // Map existing payment details by sales_invoice_id
        $existingDetails = $payment->details->keyBy('sales_invoice_id');

        $invoicesData = [];
        foreach ($allInvoices as $inv) {
            $isAllocated = $existingDetails->has($inv->id);
            $currentDetail = $isAllocated ? $existingDetails->get($inv->id) : null;

            // Hitung remaining balance untuk modal edit:
            // remaining_balance saat ini di DB (yang sudah dikurangi draft ini) + alokasi draft ini
            $allocatedInThis = $currentDetail ? ((float)$currentDetail->jumlah_bayar + (float)$currentDetail->potongan) : 0;
            $initialReceivable = max(0, (float)$inv->grand_total - (float)($inv->uang_muka ?? 0));
            $availableBalance = min($initialReceivable, round((float)$inv->remaining_balance + $allocatedInThis, 2));

            // Tampilkan faktur jika: masih ada sisa (> 0) ATAU faktur ini memang sedang dialokasikan di pembayaran ini
            if ($availableBalance > 0.01 || $isAllocated) {
                $invoicesData[] = [
                    'id'                   => $inv->id,
                    'nomor_faktur'         => $inv->nomor_faktur,
                    'tanggal_faktur'       => Carbon::parse($inv->tanggal_faktur)->format('d M Y'),
                    'tanggal_jatuh_tempo'  => $inv->tanggal_jatuh_tempo ? Carbon::parse($inv->tanggal_jatuh_tempo)->format('d M Y') : '-',
                    'grand_total'          => (float) $inv->grand_total,
                    'grand_total_formated' => 'Rp ' . number_format($inv->grand_total, 2, ',', '.'),
                    'available_balance'    => $availableBalance,
                    'available_formated'   => 'Rp ' . number_format($availableBalance, 2, ',', '.'),
                    'is_selected'          => $isAllocated,
                    'current_bayar'        => $currentDetail ? (float)$currentDetail->jumlah_bayar : 0,
                    'current_potongan'     => $currentDetail ? (float)$currentDetail->potongan : 0,
                    'current_catatan'      => $currentDetail ? $currentDetail->catatan : '',
                    'current_sisa_akhir'   => $currentDetail ? (float)$currentDetail->sisa_akhir : $availableBalance,
                ];
            }
        }

        return response()->json([
            'success'  => true,
            'payment'  => $payment,
            'invoices' => $invoicesData,
        ]);
    }

    /**
     * Ajukan Pembayaran Penjualan untuk Persetujuan Atasan (Draft -> Diajukan)
     */
    public function submitPayment(Request $request, $id)
    {
        $payment = $this->findPaymentForTenant($id, ['customer', 'details.invoice']);

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-sales-payment') && !$user->can('edit-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengajukan pembayaran ini.',
            ], 403);
        }

        if ($payment->status !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembayaran dengan status Draft yang dapat diajukan.',
            ], 422);
        }

        $payment->status = 'Diajukan';
        $payment->save();

        // Kirim Notifikasi WhatsApp ke Atasan
        WhatsAppGatewayController::sendSalesPaymentSubmissionNotification($payment);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran ' . $payment->nomor_pembayaran . ' berhasil diajukan ke atasan untuk persetujuan (ACC)!',
            'data'    => $payment,
        ]);
    }

    /**
     * Update data penerimaan pembayaran piutang (Khusus status Draft / Diajukan)
     */
    public function update(Request $request, $id)
    {
        $payment = $this->findPaymentForTenant($id, ['details.invoice']);

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengubah pembayaran ini.',
            ], 403);
        }

        if ($request->input('status') === 'Posted') {
            return response()->json([
                'success' => false,
                'message' => 'Penerimaan pembayaran harus disimpan sebagai Draft atau Diajukan terlebih dahulu sebelum dapat disetujui (ACC / Posting).',
            ], 422);
        }

        if ($payment->status === 'Posted') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah diposting tidak dapat diubah!',
            ], 422);
        }

        if ($payment->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah dibatalkan tidak dapat diubah!',
            ], 422);
        }

        $request->validate([
            'tanggal_bayar'     => 'required|date',
            'customer_id'       => 'required|exists:master_customers,id',
            'kas_bank_id'       => 'required|exists:kas_banks,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'nomor_referensi'   => 'nullable|string|max:100',
            'keterangan'        => 'nullable|string',
            'status'            => 'required|in:Draft,Diajukan',
            'lampiran'          => (!empty($payment->lampiran) ? 'nullable' : 'required') . '|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'invoices'          => 'required|array|min:1',
            'invoices.*.id'     => 'required|distinct|exists:sales_invoices,id',
            'invoices.*.bayar'  => 'required|numeric|min:0',
            'invoices.*.potongan' => 'nullable|numeric|min:0',
            'potongan_coa_id'   => 'nullable|exists:chart_of_accounts,id',
        ], [
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'customer_id.required'   => 'Customer wajib dipilih.',
            'kas_bank_id.required'   => 'Akun Kas / Bank tujuan wajib dipilih.',
            'lampiran.required'      => 'Dokumen atau foto bukti transfer pembayaran wajib disertakan.',
            'lampiran.max'           => 'Ukuran berkas bukti transfer / lampiran maksimal 10 MB.',
            'lampiran.mimes'         => 'Format berkas bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'invoices.required'      => 'Minimal pilih satu faktur untuk dibayar.',
            'invoices.*.id.distinct' => 'Terdapat faktur duplikat dalam rincian pembayaran.',
            'invoices.*.bayar.min'   => 'Nominal bayar tidak boleh negatif.',
        ]);

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $saveBranchId    = !empty($activeBranchId) ? $activeBranchId : null;
        $saveCompanyId   = !empty($activeBranchId) ? null : $activeCompanyId;

        // Pastikan Kas/Bank tujuan sesuai dengan tenant aktif
        $kasBankQuery = KasBank::query();
        if (!empty($saveBranchId)) {
            $kasBankQuery->where('branch_id', $saveBranchId);
        } else {
            $kasBankQuery->whereNull('branch_id')->where('company_id', $saveCompanyId);
        }
        $kasBank = $kasBankQuery->findOrFail($request->kas_bank_id);

        DB::beginTransaction();
        try {
            // Upload lampiran baru jika ada (kompres ke KB)
            if ($request->hasFile('lampiran')) {
                if ($payment->lampiran && Storage::disk('public')->exists($payment->lampiran)) {
                    Storage::disk('public')->delete($payment->lampiran);
                }
                $payment->lampiran = $this->compressAndSaveFile($request->file('lampiran'));
            }

            $totalBayar    = 0;
            $totalPotongan = 0;
            foreach ($request->invoices as $invItem) {
                $totalBayar    += round((float) $invItem['bayar'], 2);
                $totalPotongan += round((float) ($invItem['potongan'] ?? 0), 2);
            }
            $totalBayar    = round($totalBayar, 2);
            $totalPotongan = round($totalPotongan, 2);

            // Update header payment
            $payment->tanggal_bayar     = $request->tanggal_bayar;
            $payment->customer_id       = $request->customer_id;
            $payment->kas_bank_id       = $request->kas_bank_id;
            $payment->payment_method_id = $request->payment_method_id;
            $payment->nomor_referensi   = $request->nomor_referensi;
            $payment->keterangan        = $request->keterangan;
            $payment->total_bayar       = $totalBayar;
            $payment->total_potongan    = $totalPotongan;
            $payment->potongan_coa_id   = $request->potongan_coa_id;
            $payment->updated_by        = auth()->id();

            // Simpan detail lama untuk referensi alokasi
            $oldDetails = $payment->details->keyBy('sales_invoice_id');

            // Hapus detail lama
            $payment->details()->delete();

            $calculatedTotalFaktur = 0;

            foreach ($request->invoices as $invItem) {
                $invoiceQuery = SalesInvoice::with('paymentDetails.payment');
                if (!empty($saveBranchId)) {
                    $invoiceQuery->where('branch_id', $saveBranchId);
                } else {
                    $invoiceQuery->whereNull('branch_id')->where('company_id', $saveCompanyId);
                }
                $invoice = $invoiceQuery->lockForUpdate()->findOrFail($invItem['id']);

                // Proteksi integritas: Pastikan faktur benar-benar milik pelanggan yang dipilih
                if ((int) $invoice->customer_id !== (int) $request->customer_id) {
                    throw new \Exception("Faktur {$invoice->nomor_faktur} bukan milik pelanggan yang dipilih.");
                }

                // Hitung piutang awal faktur
                $initialReceivable = max(0, round((float)$invoice->grand_total - (float)($invoice->uang_muka ?? 0), 2));

                // Sisa piutang tersedia: karena $payment->details()->delete() sudah dieksekusi di atas,
                // $invoice->remaining_balance fresh dari DB sudah merefleksikan sisa piutang tanpa alokasi lama dari pembayaran ini.
                // Batasi agar tidak pernah melampaui piutang awal ($initialReceivable).
                $availablePiutang = min($initialReceivable, round((float)$invoice->remaining_balance, 2));

                $bayar = round((float) $invItem['bayar'], 2);
                $potongan = round((float) ($invItem['potongan'] ?? 0), 2);

                if (($bayar + $potongan) <= 0) {
                    throw new \Exception("Setiap faktur yang dipilih harus memiliki nominal alokasi bayar atau potongan lebih besar dari 0.");
                }

                if (($bayar + $potongan) > ($availablePiutang + 0.009)) {
                    $totalInputFmt = number_format($bayar + $potongan, 2, ',', '.');
                    $sisaPiutangFmt = number_format($availablePiutang, 2, ',', '.');
                    throw new \Exception("Nominal bayar + potongan untuk Faktur {$invoice->nomor_faktur} (Rp {$totalInputFmt}) melebihi sisa piutang yang tersedia (Rp {$sisaPiutangFmt}).");
                }

                $sisaAkhir = max(0, round($availablePiutang - ($bayar + $potongan), 2));

                SalesPaymentDetail::create([
                    'sales_payment_id' => $payment->id,
                    'sales_invoice_id' => $invoice->id,
                    'total_faktur'     => round((float) $invoice->grand_total, 2),
                    'sisa_piutang'     => $availablePiutang,
                    'jumlah_bayar'     => $bayar,
                    'potongan'         => $potongan,
                    'sisa_akhir'       => $sisaAkhir,
                    'catatan'          => $invItem['catatan'] ?? null,
                ]);

                $calculatedTotalFaktur += round((float) $invoice->grand_total, 2);

                // Jika user langsung memilih Posting saat update
                if ($request->status === 'Posted') {
                    if ($sisaAkhir <= 0.01) {
                        $invoice->status = 'Paid';
                        $invoice->sisa_tagihan = 0;
                    } else {
                        $invoice->status = 'Partial';
                        $invoice->sisa_tagihan = $sisaAkhir;
                    }
                    $invoice->saveQuietly();
                }
            }

            $payment->total_faktur = $calculatedTotalFaktur;

            // Simpan status baru
            $oldStatus = $payment->status;
            if ($request->status === 'Posted') {
                $payment->status    = 'Posted';
                $payment->posted_at = now();
                $payment->posted_by = auth()->id();
                $payment->save();

                // Buat Jurnal Otomatis & Kas Mutasi
                $this->createAutomaticJournalAndCashMutation($payment);

                // Kirim notifikasi WhatsApp posting
                WhatsAppGatewayController::sendSalesPaymentPostedNotification($payment, auth()->user());
            } else {
                $payment->status = $request->status;
                $payment->save();

                if ($request->status === 'Diajukan' && $oldStatus !== 'Diajukan') {
                    WhatsAppGatewayController::sendSalesPaymentSubmissionNotification($payment);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ' . $payment->nomor_pembayaran . ' berhasil diperbarui!',
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengupdate SalesPayment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buat Jurnal Otomatis & Kas Mutasi untuk Sales Payment yang Posted
     */
    private function createAutomaticJournalAndCashMutation(SalesPayment $payment)
    {
        // Untuk lookup COA, gunakan parent company
        $coaCompanyId = $payment->company_id ?: (session('active_company_id') ?: 1);

        // Strict Mutual Exclusivity pada Jurnal & KasMutasi:
        // Cabang -> branch_id = $payment->branch_id, company_id = NULL
        // PT Pusat -> branch_id = NULL, company_id = $payment->company_id
        $jurnalBranchId  = !empty($payment->branch_id) ? $payment->branch_id : null;
        $jurnalCompanyId = !empty($payment->branch_id) ? null : ($payment->company_id ?: $coaCompanyId);

        // 1. Dapatkan COA
        // Kas/Bank COA
        $kasBank = KasBank::lockForUpdate()->find($payment->kas_bank_id);
        $kasBankCoaId = $kasBank?->coa_id;
        if (!$kasBankCoaId) {
            $kasBankCoaId = AccountMappingHelper::getCoaId('payment_method', 'pm_cash', 'default_cash', $coaCompanyId);
            if (!$kasBankCoaId) {
                $kasBankCoaId = ChartOfAccount::where(function($q) use ($coaCompanyId) {
                        $q->where('company_id', $coaCompanyId)->orWhereNull('company_id');
                    })
                    ->where(function($q) {
                        $q->where('name', 'like', '%kas%')->orWhere('name', 'like', '%bank%');
                    })
                    ->first()?->id;
            }
        }

        // Piutang Usaha (AR) COA
        $arCoaId = AccountMappingHelper::getCoaId('default', 'default_ar', 'accounts_receivable', $coaCompanyId);
        if (!$arCoaId) {
            $arCoa = ChartOfAccount::where(function($q) use ($coaCompanyId) {
                    $q->where('company_id', $coaCompanyId)->orWhereNull('company_id');
                })
                ->where('name', 'like', '%piutang usaha%')
                ->first();
            $arCoaId = $arCoa?->id;
        }

        // Potongan Penjualan COA (jika ada diskon)
        $discountCoaId = null;
        if ($payment->total_potongan > 0) {
            // 1. Prioritas utama: Akun COA yang dipilih user saat input/edit
            if (!empty($payment->potongan_coa_id)) {
                $discountCoaId = $payment->potongan_coa_id;
            }

            // 2. Prioritas kedua: Mapping default akuntansi
            if (!$discountCoaId) {
                $discountCoaId = AccountMappingHelper::getCoaId('sales', 'sales_discount', 'default_sales_discount', $coaCompanyId);
            }

            // 3. Fallback: Cari COA dengan nama 'potongan penjualan' atau 'diskon penjualan'
            if (!$discountCoaId) {
                $discountCoa = ChartOfAccount::where(function($q) use ($coaCompanyId) {
                        $q->where('company_id', $coaCompanyId)->orWhereNull('company_id');
                    })
                    ->where(function($q) {
                        $q->where('name', 'like', '%potongan penjualan%')
                          ->orWhere('name', 'like', '%diskon penjualan%');
                    })
                    ->first();
                $discountCoaId = $discountCoa?->id;
            }

            // Mencegah jurnal asimetris / pincang: Potongan wajib punya akun lawan debit
            if (!$discountCoaId) {
                Log::error("COA Potongan Penjualan tidak ditemukan untuk transaksi {$payment->nomor_pembayaran}.");
                throw new \Exception("Akun Perkiraan (COA) untuk Potongan Penjualan belum diatur. Silakan pilih akun COA Potongan atau lakukan pemetaan akun terlebih dahulu agar jurnal akuntansi seimbang.");
            }
        }

        $payment->loadMissing('customer');
        $instansi = trim($payment->customer?->nama_perusahaan ?? '');
        $person   = trim($payment->customer?->nama_customer ?? '');
        if ($instansi && $person && strcasecmp($instansi, $person) !== 0) {
            $customerName = "{$instansi} - {$person}";
        } else {
            $customerName = $instansi ?: ($person ?: 'Customer');
        }
        $totalBayar = (float) $payment->total_bayar;
        $totalPotongan = (float) $payment->total_potongan;
        $totalKreditAr = $totalBayar + $totalPotongan;

        if (!$arCoaId || !$kasBankCoaId) {
            Log::warning("Accounting Mapping missing for Sales Payment {$payment->nomor_pembayaran}. Skipped journal creation.");
            throw new \Exception("Akun Perkiraan (COA) untuk Kas/Bank atau Piutang Usaha belum diatur. Silakan lengkapi pemetaan akun akuntansi.");
        }

        // Generate Journal Number
        $now = Carbon::parse($payment->tanggal_bayar);
        $jurnalPrefix = "JRN-CR/" . $now->format('Y/m') . "/";
        $lastJurnal = Jurnal::withTrashed()->where('nomor_jurnal', 'like', $jurnalPrefix . '%')->orderByDesc('id')->first();
        $nextJurnalNum = 1;
        if ($lastJurnal) {
            $parts = explode('/', $lastJurnal->nomor_jurnal);
            $nextJurnalNum = ((int) end($parts)) + 1;
        }
        $nomorJurnal = $jurnalPrefix . str_pad($nextJurnalNum, 4, '0', STR_PAD_LEFT);

        while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextJurnalNum++;
            $nomorJurnal = $jurnalPrefix . str_pad($nextJurnalNum, 4, '0', STR_PAD_LEFT);
        }

        // Bersihkan jurnal lama jika ada (idempotency)
        $existingJurnal = Jurnal::withTrashed()->where('referensi_tipe', 'Sales Payment')
            ->where('referensi_id', $payment->id)
            ->first();
        if ($existingJurnal) {
            $existingJurnal->details()->delete();
            $existingJurnal->forceDelete();
        }

        $payment->loadMissing(['details.invoice.tender', 'details.invoice.salesOrder.tender', 'details.invoice.deliveryOrder.salesOrder.tender']);
        $tenders = collect();
        $tenderIds = collect();
        foreach ($payment->details as $d) {
            if ($d->invoice) {
                $tenderObj = $d->invoice->tender 
                    ?? $d->invoice->salesOrder?->tender 
                    ?? $d->invoice->deliveryOrder?->salesOrder?->tender;

                if ($tenderObj) {
                    $tenderIds->push($tenderObj->id);
                    $label = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                        ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                        : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));
                    $tenders->push($label);

                    // Pastikan faktur terhubung memiliki tender_id jika sebelumnya null
                    if (empty($d->invoice->tender_id)) {
                        $d->invoice->update(['tender_id' => $tenderObj->id]);
                    }
                }
            }
        }
        $nomorTender = $tenders->unique()->filter()->implode(', ') ?: null;
        $primaryTenderId = $tenderIds->unique()->filter()->first() ?: null;
        $tenderSuffix = $nomorTender ? " [Tender: {$nomorTender}]" : "";

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $payment->tanggal_bayar,
            'referensi_tipe' => 'Sales Payment',
            'referensi_id'   => $payment->id,
            'no_tender'      => $nomorTender,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $totalKreditAr,
            'total_kredit'   => $totalKreditAr,
            'keterangan'     => "Penerimaan Pembayaran Piutang dari {$customerName} ({$payment->nomor_pembayaran}){$tenderSuffix}",
            'created_by'     => auth()->id(),
        ]);

        // 1. DEBIT: Kas / Bank (hanya jika ada dana kas/bank masuk)
        if ($totalBayar > 0) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $kasBankCoaId,
                'debit'      => $totalBayar,
                'kredit'     => 0,
                'keterangan' => "Penerimaan Kas/Bank ({$payment->nomor_pembayaran}){$tenderSuffix}",
            ]);
        }

        // 2. DEBIT: Potongan Penjualan (jika ada potongan)
        if ($totalPotongan > 0 && $discountCoaId) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $discountCoaId,
                'debit'      => $totalPotongan,
                'kredit'     => 0,
                'keterangan' => "Potongan Pelunasan Piutang ({$payment->nomor_pembayaran}){$tenderSuffix}",
            ]);
        }

        // 3. KREDIT: Piutang Usaha
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $arCoaId,
            'debit'      => 0,
            'kredit'     => $totalKreditAr,
            'keterangan' => "Pelunasan Piutang Usaha {$customerName} ({$payment->nomor_pembayaran}){$tenderSuffix}",
        ]);

        // 2. Kas Mutasi & Update Saldo KasBank (hanya jika ada dana kas/bank masuk)
        $existingMutasi = KasMutasi::where('nomor_referensi', $payment->nomor_pembayaran)->first();
        if ($existingMutasi) {
            $existingMutasi->forceDelete();
        }

        if ($totalBayar > 0) {
            $mPrefix = "KM-" . date('ym') . "-";
            $latestM = KasMutasi::withTrashed()->where('nomor_transaksi', 'like', $mPrefix . '%')->orderByDesc('id')->first();
            $mNum = 1;
            if ($latestM && preg_match('/(\d+)$/', $latestM->nomor_transaksi, $matches)) {
                $mNum = ((int) $matches[1]) + 1;
            }
            $nomorTransaksi = $mPrefix . str_pad($mNum, 4, '0', STR_PAD_LEFT);

            $saldoSebelum = (float) ($kasBank?->saldo_berjalan ?? 0);
            $saldoSesudah = $saldoSebelum + $totalBayar;

            KasMutasi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'kas_bank_id'     => $payment->kas_bank_id,
                'tipe'            => 'Masuk',
                'kategori'        => 'Penerimaan Pelanggan',
                'lawan_coa_id'    => $arCoaId,
                'jumlah'          => $totalBayar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'tanggal'         => $payment->tanggal_bayar,
                'nomor_referensi' => $payment->nomor_pembayaran,
                'kontak_tipe'     => 'Customer',
                'kontak_id'       => $payment->customer_id,
                'nama_kontak'     => $customerName,
                'tender_id'       => $primaryTenderId,
                'keterangan'      => "Penerimaan Piutang dari {$customerName} (Ref: {$payment->nomor_pembayaran}){$tenderSuffix}",
                'lampiran'        => $payment->lampiran,
                'company_id'      => $jurnalCompanyId,
                'branch_id'       => $jurnalBranchId,
                'created_by'      => auth()->id(),
            ]);

            // Update Saldo Kas Bank
            if ($kasBank) {
                $kasBank->saldo_berjalan = $saldoSesudah;
                $kasBank->save();
            }
        }
    }

    /**
     * Detail Pembayaran JSON untuk Modal
     */
    public function show($id)
    {
        $payment = $this->findPaymentForTenant($id, [
            'customer',
            'kasBank',
            'paymentMethod',
            'potonganCoa',
            'branch',
            'creator',
            'updater',
            'poster',
            'canceller',
            'jurnal.details.account',
            'details.invoice.tender',
            'details.invoice.salesOrder.tender',
            'details.invoice.deliveryOrder.salesOrder.tender'
        ]);

        $isEdited = false;
        if (!empty($payment->updated_by)) {
            // Jika ada updated_by, pastikan bukan semata-mata karena approval/posting
            if (!empty($payment->posted_at) && $payment->updated_by == $payment->posted_by) {
                // Jika waktu updated_at sama atau berselisih kurang dari 5 detik dengan posted_at, itu akibat aksi posting, bukan edit data
                $diff = abs($payment->updated_at->diffInSeconds($payment->posted_at));
                if ($diff > 5) {
                    $isEdited = true;
                }
            } else {
                $isEdited = true;
            }
        }

        $paymentData = $payment->toArray();
        $paymentData['is_edited'] = $isEdited;
        $paymentData['tanggal_bayar_formatted'] = $payment->tanggal_bayar ? \Carbon\Carbon::parse($payment->tanggal_bayar)->translatedFormat('d F Y') : '-';
        $paymentData['created_at_formatted'] = $payment->created_at ? $payment->created_at->translatedFormat('d M Y, H:i') . ' WIB' : '-';
        $paymentData['updated_at_formatted'] = ($payment->updated_at && $isEdited) ? $payment->updated_at->translatedFormat('d M Y, H:i') . ' WIB' : '-';
        $paymentData['posted_at_formatted'] = $payment->posted_at ? $payment->posted_at->translatedFormat('d M Y, H:i') . ' WIB' : '-';
        $paymentData['cancelled_at_formatted'] = $payment->cancelled_at ? $payment->cancelled_at->translatedFormat('d M Y, H:i') . ' WIB' : '-';

        return response()->json([
            'success' => true,
            'data'    => $paymentData,
        ]);
    }

    /**
     * Dapatkan detail Jurnal untuk Voucher modal
     */
    public function getJurnal($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-jurnal-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat voucher jurnal akuntansi pembayaran.',
            ], 403);
        }

        $payment = $this->findPaymentForTenant($id);
        $jurnal = Jurnal::where('referensi_tipe', 'Sales Payment')
            ->where('referensi_id', $payment->id)
            ->with(['details.account', 'creator'])
            ->first();

        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal akuntansi belum dibuat atau transaksi berstatus Draft.',
            ], 404);
        }

        // Auto-sinkronisasi nomor tender pada jurnal lama jika belum terisi
        if (empty($jurnal->no_tender)) {
            $payment->loadMissing(['details.invoice.tender', 'details.invoice.salesOrder.tender', 'details.invoice.deliveryOrder.salesOrder.tender']);
            $tenders = collect();
            foreach ($payment->details as $d) {
                if ($d->invoice) {
                    $tenderObj = $d->invoice->tender 
                        ?? $d->invoice->salesOrder?->tender 
                        ?? $d->invoice->deliveryOrder?->salesOrder?->tender;

                    if ($tenderObj) {
                        $label = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                            ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                            : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));
                        $tenders->push($label);
                    }
                }
            }
            $resolvedTender = $tenders->unique()->filter()->implode(', ') ?: null;
            if ($resolvedTender) {
                $jurnal->no_tender = $resolvedTender;
                $jurnal->saveQuietly();
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $jurnal,
        ]);
    }

    /**
     * Konfirmasi Posting Pembayaran Penjualan (Draft -> Posted)
     * Mengupdate status invoice, menerbitkan Jurnal Otomatis & Kas Mutasi, serta mengirim Notifikasi WhatsApp.
     */
    public function postPayment(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner', 'Manager Finance']) && !$user->can('approve-sales-payment')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menyetujui (ACC) atau memposting pembayaran ini.',
            ], 403);
        }

        $payment = $this->findPaymentForTenant($id, ['details.invoice', 'customer']);

        if (!in_array($payment->status, ['Diajukan', 'Draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembayaran dengan status Menunggu Persetujuan atau Draft yang dapat diposting.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment->status    = 'Posted';
            $payment->posted_by = auth()->id();
            $payment->posted_at = now();
            $payment->save();

            // Update status invoice terkait (recalculate realtime saat posting)
            foreach ($payment->details as $detail) {
                // Reload invoice fresh dari DB dengan payment details terkini dan row locking.
                // Hindari pakai $detail->sisa_akhir (snapshot stale saat Draft dibuat),
                // karena bisa ada payment lain yang masuk setelah Draft dibuat.
                $invoice = SalesInvoice::with('paymentDetails.payment')->lockForUpdate()->find($detail->sales_invoice_id);
                if ($invoice) {
                    // Validasi concurrency: Pastikan faktur tidak over-paid akibat transaksi lain yang masuk saat draft menunggu persetujuan
                    $otherAllocated = (float) $invoice->paymentDetails
                        ->filter(function($d) use ($payment) {
                            return (int)$d->sales_payment_id !== (int)$payment->id
                                && $d->payment
                                && $d->payment->status !== 'Cancelled'
                                && empty($d->payment->deleted_at);
                        })
                        ->sum(function($d) { return (float) $d->jumlah_bayar + (float) $d->potongan; });

                    $initialReceivable = max(0, round((float) $invoice->grand_total - (float) ($invoice->uang_muka ?? 0), 2));
                    $thisAllocation = round((float) $detail->jumlah_bayar + (float) $detail->potongan, 2);

                    if (($otherAllocated + $thisAllocation) > ($initialReceivable + 0.01)) {
                        $available = max(0, round($initialReceivable - $otherAllocated, 2));
                        throw new \Exception("Faktur {$invoice->nomor_faktur} telah memiliki pembayaran lain senilai Rp " . number_format($otherAllocated, 2, ',', '.') . ". Sisa piutang tersedia saat ini hanya Rp " . number_format($available, 2, ',', '.') . ", sedangkan alokasi draft ini sebesar Rp " . number_format($thisAllocation, 2, ',', '.') . ". Silakan sesuaikan draft pembayaran terlebih dahulu sebelum disetujui.");
                    }

                    // remaining_balance sudah memperhitungkan semua pembayaran aktif
                    // (non-cancelled, termasuk payment yang baru saja diposting ini)
                    $sisaAkhir = (float) $invoice->remaining_balance;

                    if ($sisaAkhir <= 0.01) {
                        $invoice->status       = 'Paid';
                        $invoice->sisa_tagihan = 0;
                        $sisaAkhir             = 0;
                    } else {
                        $invoice->status       = 'Partial';
                        $invoice->sisa_tagihan = $sisaAkhir;
                    }
                    $invoice->saveQuietly();

                    // Sinkronkan snapshot sisa_akhir di detail agar akurat dengan nilai aktual saat posting
                    $detail->sisa_akhir = $sisaAkhir;
                    $detail->saveQuietly();
                }
            }

            // Buat Jurnal Otomatis & Kas Mutasi
            $this->createAutomaticJournalAndCashMutation($payment);

            DB::commit();

            // Kirim Notifikasi WhatsApp Posting
            WhatsAppGatewayController::sendSalesPaymentPostedNotification($payment, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ' . $payment->nomor_pembayaran . ' berhasil diposting dan jurnal otomatis telah terbit!',
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memposting SalesPayment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batalkan atau Hapus Pembayaran
     */
    public function destroy(Request $request, $id)
    {
        $payment = $this->findPaymentForTenant($id, ['details.invoice']);

        $user = auth()->user();
        if ($payment->status === 'Posted') {
            if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('batalkan-sales-payment') && !$user->can('hapus-sales-payment')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk membatalkan pembayaran yang sudah diposting.',
                ], 403);
            }
        } else {
            // Draft atau Diajukan
            $isCreator = ($payment->created_by == $user->id);
            if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('hapus-sales-payment') && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk menghapus pembayaran ini.',
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // 1. Rollback Invoice status & balance
            foreach ($payment->details as $detail) {
                $invoice = SalesInvoice::lockForUpdate()->find($detail->sales_invoice_id);
                if ($invoice) {
                    // Recalculate invoice status based on remaining payments
                    $otherPaid = (float) $invoice->paymentDetails()
                        ->where('sales_payment_id', '!=', $payment->id)
                        ->whereHas('payment', function($q) {
                            $q->where('status', 'Posted');
                        })
                        ->sum(DB::raw('jumlah_bayar + potongan'));

                    $initialReceivable = max(0, (float) $invoice->grand_total - (float) ($invoice->uang_muka ?? 0));
                    $remBalance = max(0, $initialReceivable - $otherPaid);

                    if ($otherPaid <= 0) {
                        $invoice->status = 'Unpaid';
                    } elseif ($remBalance <= 0.01) {
                        $invoice->status = 'Paid';
                    } else {
                        $invoice->status = 'Partial';
                    }
                    $invoice->sisa_tagihan = $remBalance;
                    $invoice->save();
                }
            }

            // 2. Rollback KasBank & KasMutasi
            if ($payment->status === 'Posted') {
                $kasBank = KasBank::lockForUpdate()->find($payment->kas_bank_id);
                if ($kasBank) {
                    $kasBank->saldo_berjalan = (float) $kasBank->saldo_berjalan - (float) $payment->total_bayar;
                    $kasBank->save();
                }

                $mutasi = KasMutasi::where('nomor_referensi', $payment->nomor_pembayaran)->first();
                if ($mutasi) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('kas_mutasis', 'status')) {
                        $mutasi->status = 'Dibatalkan';
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('kas_mutasis', 'alasan_pembatalan')) {
                        $mutasi->alasan_pembatalan = 'Pembatalan Pembayaran Penjualan ' . $payment->nomor_pembayaran;
                    }
                    $mutasi->save();
                    $mutasi->delete();
                }

                // 3. Rollback Jurnal
                $jurnal = Jurnal::where('referensi_tipe', 'Sales Payment')
                    ->where('referensi_id', $payment->id)
                    ->first();
                if ($jurnal) {
                    $jurnal->details()->delete();
                    $jurnal->forceDelete();
                }
            }

            // 4. Update status ke Cancelled atau Soft Delete
            $payment->status = 'Cancelled';
            $payment->cancelled_by = auth()->id();
            $payment->cancelled_at = now();
            $payment->alasan_pembatalan = $request->alasan ?? 'Dibatalkan oleh user';
            $payment->save();
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ' . $payment->nomor_pembayaran . ' berhasil dibatalkan dan jurnal telah disesuaikan!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membatalkan SalesPayment: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cetak Kwitansi Penerimaan Pembayaran
     */
    public function printKwitansi($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-sales-payment') && !$user->can('lihat-sales-payment')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak kwitansi pembayaran.');
        }

        $payment = $this->findPaymentForTenant($id, [
            'customer',
            'kasBank',
            'paymentMethod',
            'currency',
            'branch',
            'company',
            'creator',
            'details.invoice.tender',
            'details.invoice.salesOrder.tender',
            'details.invoice.deliveryOrder.salesOrder.tender',
        ]);

        $sessionBranchId  = session('active_branch_id');
        $sessionCompanyId = session('active_company_id');

        if (!empty($sessionBranchId)) {
            $activeBranch  = \App\Models\Branch::find($sessionBranchId);
            $activeCompany = $activeBranch?->company ?? ($sessionCompanyId ? \App\Models\Company::find($sessionCompanyId) : null);
        } elseif (!empty($sessionCompanyId)) {
            $activeBranch  = null;
            $activeCompany = \App\Models\Company::find($sessionCompanyId);
        } else {
            $activeBranch  = $payment->branch;
            $activeCompany = $payment->company ?? $user->companies()->first();
        }

        if (!empty($payment->branch_id)) {
            if (!$activeBranch || (int)$activeBranch->id !== (int)$payment->branch_id) {
                $activeBranch  = $payment->branch;
                $activeCompany = $activeBranch?->company ?? $payment->company;
            }
        } elseif (!empty($payment->company_id)) {
            $activeBranch = null;
            if (!$activeCompany || (int)$activeCompany->id !== (int)$payment->company_id) {
                $activeCompany = $payment->company;
            }
        }

        $totalBayar = (float) $payment->total_bayar;
        $intPart    = (int) floor($totalBayar);
        $fraction   = (int) round(($totalBayar - $intPart) * 100);

        $terbilang  = $this->terbilang($intPart) . ' Rupiah';
        if ($fraction > 0) {
            $terbilang .= ' ' . $this->terbilang($fraction) . ' Sen';
        }

        return view('modul_marketing.pembayaran.print_kwitansi', compact('payment', 'terbilang', 'activeCompany', 'activeBranch'));
    }

    /**
     * Helper Konversi Angka ke Huruf Terbilang Bahasa Indonesia
     */
    private function terbilang($angka)
    {
        $angka = abs((float) $angka);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $hasil = "";

        if ($angka < 12) {
            $hasil = " " . $huruf[(int)$angka];
        } elseif ($angka < 20) {
            $hasil = $this->terbilang($angka - 10) . " Belas";
        } elseif ($angka < 100) {
            $hasil = $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $hasil = " Seratus" . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $hasil = $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $hasil = " Seribu" . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $hasil = $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $hasil = $this->terbilang($angka / 1000000) . " Juta" . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $hasil = $this->terbilang($angka / 1000000000) . " Miliar" . $this->terbilang(fmod($angka, 1000000000));
        } elseif ($angka < 1000000000000000) {
            $hasil = $this->terbilang($angka / 1000000000000) . " Triliun" . $this->terbilang(fmod($angka, 1000000000000));
        }

        return trim($hasil);
    }

    /**
     * Kompres dan simpan file bukti transfer / lampiran
     * Gambar (JPG, PNG, WEBP) dikompres ke WebP/JPG kualitas optimal ukuran KB agar server tidak terbebani
     * Dokumen (PDF) disimpan aman dengan penamaan unik terenkripsi
     */
    private function compressAndSaveFile($file, $destinationDir = 'sales_payments', $maxDimension = 1600, $quality = 75)
    {
        $mime = $file->getMimeType();
        $safeExtension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        
        // Strict whitelist: hanya izinkan format dokumen/gambar resmi (mencegah double extension / PHP injection)
        if (!in_array($safeExtension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
            $safeExtension = ($mime === 'application/pdf') ? 'pdf' : 'jpg';
        }

        $isImage = in_array($safeExtension, ['jpg', 'jpeg', 'png', 'webp']) && str_starts_with($mime, 'image/');

        if ($isImage && extension_loaded('gd')) {
            $storagePath = storage_path('app/public/' . $destinationDir);

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $sourceImage = null;
            switch ($safeExtension) {
                case 'jpeg':
                case 'jpg':
                    $sourceImage = @imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'png':
                    $sourceImage = @imagecreatefrompng($file->getRealPath());
                    break;
                case 'webp':
                    $sourceImage = @imagecreatefromwebp($file->getRealPath());
                    break;
            }

            if ($sourceImage) {
                // Auto-rotate berdasarkan orientasi EXIF kamera ponsel
                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($file->getRealPath());
                    if ($exif && !empty($exif['Orientation'])) {
                        switch ($exif['Orientation']) {
                            case 3:
                                $sourceImage = imagerotate($sourceImage, 180, 0);
                                break;
                            case 6:
                                $sourceImage = imagerotate($sourceImage, -90, 0);
                                break;
                            case 8:
                                $sourceImage = imagerotate($sourceImage, 90, 0);
                                break;
                        }
                    }
                }

                $origWidth = imagesx($sourceImage);
                $origHeight = imagesy($sourceImage);

                $width = $origWidth;
                $height = $origHeight;

                if ($width > $maxDimension || $height > $maxDimension) {
                    if ($width > $height) {
                        $height = (int) round(($height / $width) * $maxDimension);
                        $width = $maxDimension;
                    } else {
                        $width = (int) round(($width / $height) * $maxDimension);
                        $height = $maxDimension;
                    }
                }

                $resizedImage = imagecreatetruecolor($width, $height);

                // Preserve alpha channel transparency untuk PNG / WEBP
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $width, $height, $transparent);

                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

                $filename = 'pay_' . time() . '_' . uniqid() . '.webp';
                $fullPath = $storagePath . '/' . $filename;

                if (function_exists('imagewebp')) {
                    imagewebp($resizedImage, $fullPath, $quality);
                } else {
                    $filename = 'pay_' . time() . '_' . uniqid() . '.jpg';
                    $fullPath = $storagePath . '/' . $filename;
                    imagejpeg($resizedImage, $fullPath, $quality);
                }

                imagedestroy($sourceImage);
                imagedestroy($resizedImage);

                return $destinationDir . '/' . $filename;
            }
        }

        // Penyimpanan default untuk dokumen PDF atau fallback yang aman
        $finalExtension = ($safeExtension === 'pdf' || $mime === 'application/pdf') ? 'pdf' : 'jpg';
        $filename = 'pay_' . time() . '_' . uniqid() . '.' . $finalExtension;
        return $file->storeAs($destinationDir, $filename, 'public');
    }
}
