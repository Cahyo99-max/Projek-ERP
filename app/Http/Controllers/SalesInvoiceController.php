<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Models\MasterCustomer;
use App\Models\MasterPajak;
use App\Models\PaymentTerm;
use App\Models\MasterCurrency;
use App\Models\Tender;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Models\ChartOfAccount;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\WhatsAppGatewayController;

class SalesInvoiceController extends Controller
{
    /**
     * Cari faktur dengan isolasi multi-tenant yang ketat (mencegah IDOR antar cabang/perusahaan)
     */
    private function findInvoiceForTenant($id, array $withRelations = []): SalesInvoice
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesInvoice::query();
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
     * Bersihkan cache metrik KPI agar data ringkasan selalu sinkron saat ada perubahan
     */
    private function clearKpiCache(): void
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $cacheKey = "kpi_sales_inv_{$activeCompanyId}_" . ($activeBranchId ?: 'all');
        Cache::forget($cacheKey);
    }

    /**
     * Endpoint Sinkronisasi Real-time Cepat (Fingerprint Status Faktur)
     * Mengembalikan hash status faktur aktif untuk memicu auto-reload instan pada klien
     */
    public function checkSync(Request $request)
    {
        $this->authorize('lihat-sales-invoice');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $userId          = auth()->id();

        $query = SalesInvoice::query();
        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        // Status Draft hanya bisa dilihat oleh pembuatnya (creator)
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('status', '!=', 'Draft')
                  ->orWhere('created_by', $userId);
            });
        }

        $cacheKey = "check_sync_meta_{$activeCompanyId}_" . ($activeBranchId ?: '0') . "_{$userId}";
        $meta = Cache::remember($cacheKey, 3, function () use ($query) {
            return $query->selectRaw('
                MAX(updated_at) as last_updated,
                MAX(id) as max_id,
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "Draft" THEN 1 ELSE 0 END) as cnt_draft,
                SUM(CASE WHEN status = "Menunggu Persetujuan" THEN 1 ELSE 0 END) as cnt_pending,
                SUM(CASE WHEN status = "Unpaid" THEN 1 ELSE 0 END) as cnt_unpaid,
                SUM(CASE WHEN status = "Partial" THEN 1 ELSE 0 END) as cnt_partial,
                SUM(CASE WHEN status = "Paid" THEN 1 ELSE 0 END) as cnt_paid,
                SUM(CASE WHEN status = "Rejected" THEN 1 ELSE 0 END) as cnt_rejected
            ')->first();
        });

        $rawFingerprint = implode('|', [
            $meta->last_updated ?? '0',
            $meta->max_id ?? '0',
            $meta->total_count ?? '0',
            $meta->cnt_draft ?? '0',
            $meta->cnt_pending ?? '0',
            $meta->cnt_unpaid ?? '0',
            $meta->cnt_partial ?? '0',
            $meta->cnt_paid ?? '0',
            $meta->cnt_rejected ?? '0',
        ]);

        $fingerprint = md5($rawFingerprint);

        return response()->json([
            'status'       => 'success',
            'fingerprint'  => $fingerprint,
            'last_updated' => $meta->last_updated,
            'total_count'  => (int) $meta->total_count,
            'pending'      => (int) $meta->cnt_pending,
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('lihat-sales-invoice');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        // Hanya pilih kolom yang dibutuhkan agar efisien memory saat data mencapai jutaan baris
        $query = SalesInvoice::query()
            ->select([
                'sales_invoices.id',
                'sales_invoices.nomor_faktur',
                'sales_invoices.nomor_faktur_pajak',
                'sales_invoices.tanggal_faktur',
                'sales_invoices.tanggal_jatuh_tempo',
                'sales_invoices.customer_id',
                'sales_invoices.delivery_order_id',
                'sales_invoices.sales_order_id',
                'sales_invoices.currency_id',
                'sales_invoices.kurs',
                'sales_invoices.grand_total',
                'sales_invoices.uang_muka',
                'sales_invoices.sisa_tagihan',
                'sales_invoices.status',
                'sales_invoices.created_by',
                'sales_invoices.branch_id',
                'sales_invoices.company_id',
            ])
            ->with([
                'customer:id,nama_customer,nama_perusahaan,kode_customer',
                'deliveryOrder:id,nomor_do',
                'salesOrder:id,nomor_so',
                'currency:id,kode_currency,simbol',
                'paymentDetails.payment',
            ])
            ->orderByDesc('sales_invoices.id');

        if (!empty($activeBranchId)) {
            $query->where('sales_invoices.branch_id', $activeBranchId);
        } else {
            $query->whereNull('sales_invoices.branch_id')->where('sales_invoices.company_id', $activeCompanyId);
        }

        // Status Draft hanya bisa dilihat oleh pembuatnya (creator)
        $userId = auth()->id();
        $query->where(function ($q) use ($userId) {
            $q->where('sales_invoices.status', '!=', 'Draft')
              ->orWhere('sales_invoices.created_by', $userId);
        });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('sales_invoices.status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('sales_invoices.customer_id', $request->customer_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sales_invoices.tanggal_faktur', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('sales_invoices.tanggal_faktur', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('sales_invoices.tanggal_faktur', '<=', $request->end_date);
        }

        // Summary metrics - Real-time & terindeks
        $today = Carbon::now()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $kpiBase = SalesInvoice::query();
        if (!empty($activeBranchId)) {
            $kpiBase->where('sales_invoices.branch_id', $activeBranchId);
        } else {
            $kpiBase->whereNull('sales_invoices.branch_id')->where('sales_invoices.company_id', $activeCompanyId);
        }

        $userId = auth()->id();
        $kpiBase->where(function ($q) use ($userId) {
            $q->where('sales_invoices.status', '!=', 'Draft')
              ->orWhere('sales_invoices.created_by', $userId);
        });

        // Eksekusi satu query terkonsolidasi (single pass) dengan caching agar performa instan meski data mencapai jutaan baris
        $kpiCacheKey = "kpi_sales_inv_{$activeCompanyId}_" . ($activeBranchId ?: 'all');
        $res = Cache::remember($kpiCacheKey, 180, function () use ($kpiBase, $today, $startOfMonth, $endOfMonth) {
            return (clone $kpiBase)
                ->selectRaw("
                    COUNT(CASE WHEN status != 'Cancelled' THEN 1 END) as total_faktur,
                    COALESCE(SUM(CASE 
                        WHEN status NOT IN ('Paid', 'Cancelled', 'Rejected') 
                        THEN (CASE WHEN sisa_tagihan > 0 THEN sisa_tagihan ELSE GREATEST(0, grand_total - COALESCE(uang_muka, 0)) END) 
                        ELSE 0 
                    END), 0) as total_piutang_unpaid,
                    COUNT(CASE 
                        WHEN status NOT IN ('Paid', 'Cancelled', 'Rejected') AND tanggal_jatuh_tempo < ? 
                        THEN 1 
                    END) as total_jatuh_tempo,
                    COALESCE(SUM(CASE 
                        WHEN status = 'Paid' AND tanggal_faktur >= ? AND tanggal_faktur <= ? 
                        THEN grand_total 
                        ELSE 0 
                    END), 0) as total_terbayar_bulan_ini
                ", [$today, $startOfMonth, $endOfMonth])
                ->first();
        });

        $totalFaktur           = (int) ($res?->total_faktur ?? 0);
        $totalPiutangUnpaid    = (float) ($res?->total_piutang_unpaid ?? 0);
        $totalJatuhTempo       = (int) ($res?->total_jatuh_tempo ?? 0);
        $totalTerbayarBulanIni = (float) ($res?->total_terbayar_bulan_ini ?? 0);

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('nomor_faktur', function ($i) {
                    $nsfp = !empty($i->nomor_faktur_pajak) ? '<div class="small text-muted font-monospace"><i class="bi bi-tag me-1"></i>' . e($i->nomor_faktur_pajak) . '</div>' : '';
                    return '<a href="javascript:void(0)" class="fw-bold text-primary btn-view-invoice text-decoration-none" data-id="' . $i->id . '" title="Lihat Detail">' . e($i->nomor_faktur) . '</a>' . $nsfp;
                })
                ->addColumn('customer_name', function ($i) {
                    $instansi = trim($i->customer?->nama_perusahaan ?? '');
                    $person   = trim($i->customer?->nama_customer ?? '');

                    if ($instansi && $person && strcasecmp($instansi, $person) !== 0) {
                        return '<div class="fw-bold text-body-emphasis">' . e($instansi) . '</div>'
                            . '<small class="text-body-secondary d-block font-monospace" style="font-size: 0.76rem;"><i class="bi bi-person me-1"></i>' . e($person) . '</small>';
                    }

                    $mainName = $instansi ?: ($person ?: '-');
                    return '<div class="fw-semibold text-body-emphasis">' . e($mainName) . '</div>';
                })
                ->filterColumn('customer_name', function ($q, $keyword) {
                    $q->whereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('nama_perusahaan', 'like', "%{$keyword}%")
                           ->orWhere('nama_customer', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('customer_name', function ($q, $order) {
                    $q->orderBy(
                        \App\Models\MasterCustomer::select(DB::raw('COALESCE(NULLIF(nama_perusahaan, ""), nama_customer)'))
                            ->whereColumn('master_customers.id', 'sales_invoices.customer_id'),
                        $order
                    );
                })
                ->addColumn('nomor_do', fn($i) => $i->deliveryOrder?->nomor_do ?? ($i->salesOrder?->nomor_so ?? '-'))
                ->addColumn('tanggal_faktur_fmt', fn($i) => $i->tanggal_faktur ? $i->tanggal_faktur->format('d/m/Y') : '-')
                ->addColumn('tanggal_jatuh_tempo_fmt', function ($i) {
                    if (!$i->tanggal_jatuh_tempo) {
                        return '<span class="text-body-secondary font-monospace">-</span>';
                    }

                    $dateStr = $i->tanggal_jatuh_tempo->format('d/m/Y');

                    // Jika faktur sudah Lunas atau Dibatalkan, tampilkan biasa
                    if (in_array($i->status, ['Paid', 'Cancelled'])) {
                        return '<span class="font-monospace text-body-secondary">' . $dateStr . '</span>';
                    }

                    $today = Carbon::today();
                    $dueDate = $i->tanggal_jatuh_tempo->copy()->startOfDay();
                    $diffDays = (int) $today->diffInDays($dueDate, false);

                    if ($dueDate->lt($today)) {
                        $daysLate = abs($diffDays);
                        return '<div>'
                            . '<div class="fw-bold text-danger font-monospace"><i class="bi bi-calendar-x me-1"></i>' . $dateStr . '</div>'
                            . '<div class="mt-1"><span class="badge bg-danger text-white font-monospace px-2 py-0.5" style="font-size:0.68rem;" title="Melewati batas tanggal jatuh tempo!"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat ' . $daysLate . ' hari</span></div>'
                            . '</div>';
                    } elseif ($dueDate->eq($today)) {
                        return '<div>'
                            . '<div class="fw-bold text-warning font-monospace"><i class="bi bi-calendar-event me-1"></i>' . $dateStr . '</div>'
                            . '<div class="mt-1"><span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-0.5" style="font-size:0.68rem;"><i class="bi bi-bell-fill me-1"></i>Hari Ini</span></div>'
                            . '</div>';
                    } else {
                        $daysLeft = abs($diffDays);
                        $badgeSubtle = $daysLeft <= 7 
                            ? '<div class="mt-1"><span class="badge bg-info-subtle text-info-emphasis font-monospace px-2 py-0.5" style="font-size:0.68rem;">Sisa ' . $daysLeft . ' hari</span></div>'
                            : '<div class="small text-body-secondary font-monospace" style="font-size:0.7rem;">Sisa ' . $daysLeft . ' hari</div>';

                        return '<div>'
                            . '<div class="font-monospace text-body">' . $dateStr . '</div>'
                            . $badgeSubtle
                            . '</div>';
                    }
                })
                ->addColumn('grand_total_fmt', function ($i) {
                    $curCode = strtoupper($i->currency?->kode_currency ?? 'IDR');
                    $isForeign = !in_array($curCode, ['IDR', 'RP']);
                    $curSymbol = $i->currency?->simbol ?? ($isForeign ? '$' : 'Rp');
                    $kurs = (float) ($i->kurs ?: 1);

                    $html = '';
                    if ($isForeign) {
                        $idrEquiv = (float) $i->grand_total * $kurs;
                        $html .= '<div class="fw-bold text-body font-monospace">' . e($curSymbol) . ' ' . number_format($i->grand_total, 2, ',', '.') . '</div>'
                            . '<small class="text-muted d-block font-monospace" style="font-size:0.72rem;">≈ Rp ' . number_format($idrEquiv, 2, ',', '.') . '</small>';
                    } else {
                        $html .= '<div class="fw-bold text-body font-monospace">Rp ' . number_format($i->grand_total, 2, ',', '.') . '</div>';
                    }

                    if ((float)($i->uang_muka ?? 0) > 0) {
                        $html .= '<small class="text-warning d-block font-monospace" style="font-size:0.72rem;" title="Uang Muka / DP"><i class="bi bi-cash-stack me-0.5"></i>DP: Rp ' . number_format($i->uang_muka, 2, ',', '.') . '</small>';
                    }

                    return $html;
                })
                ->addColumn('sisa_tagihan_fmt', function ($i) {
                    if ($i->status === 'Paid') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1"><i class="bi bi-check2-all me-1"></i>Lunas</span>';
                    }
                    if ($i->status === 'Cancelled') {
                        return '<span class="text-muted font-monospace">-</span>';
                    }

                    // Sisa tagihan resmi faktur memperhitungkan Uang Muka + Pembayaran Posted
                    $paidPosted = (float) $i->paid_amount;
                    $sisa = max(0, (float) $i->grand_total - $paidPosted);

                    $today = Carbon::today();
                    $isOverdue = $i->tanggal_jatuh_tempo && Carbon::parse($i->tanggal_jatuh_tempo)->startOfDay()->lt($today);

                    $colorClass = $isOverdue ? 'text-danger' : 'text-success';

                    $html = '<div class="fw-bold font-monospace ' . $colorClass . '">Rp ' . number_format($sisa, 2, ',', '.') . '</div>';

                    // Hitung riil pelunasan kas/bank yang sudah Posted (di luar Uang Muka DP)
                    $pelunasanPosted = (float) ($i->paymentDetails ? $i->paymentDetails
                        ->filter(function($d) {
                            return $d->payment && $d->payment->status === 'Posted' && empty($d->payment->deleted_at);
                        })
                        ->sum(function($d) {
                            return (float)$d->jumlah_bayar + (float)$d->potongan;
                        }) : 0);

                    if ($pelunasanPosted > 0) {
                        $html .= '<small class="text-muted d-block font-monospace" style="font-size:0.72rem;">Terbayar: Rp ' . number_format($pelunasanPosted, 2, ',', '.') . '</small>';
                    }

                    // Hitung riil alokasi pembayaran yang masih Draft atau Menunggu Persetujuan (belum di-ACC)
                    $pendingDetails = $i->paymentDetails ? $i->paymentDetails
                        ->filter(function($d) {
                            return $d->payment 
                                && in_array($d->payment->status, ['Draft', 'Diajukan', 'Menunggu Persetujuan'])
                                && empty($d->payment->deleted_at);
                        }) : collect();

                    $pendingAmount = (float) $pendingDetails->sum(function($d) {
                        return (float)$d->jumlah_bayar + (float)$d->potongan;
                    });

                    if ($pendingAmount > 0) {
                        $hasDiajukan = $pendingDetails->contains(function($d) {
                            return in_array($d->payment->status, ['Diajukan', 'Menunggu Persetujuan']);
                        });
                        $labelDraft = $hasDiajukan ? 'Diajukan' : 'Draft';
                        $html .= '<small class="text-warning d-block font-monospace" style="font-size:0.7rem;" title="Ada alokasi pembayaran yang masih berstatus draf / menunggu persetujuan"><i class="bi bi-hourglass-split me-0.5"></i>' . $labelDraft . ': Rp ' . number_format($pendingAmount, 2, ',', '.') . '</small>';
                    }

                    return $html;
                })
                ->addColumn('is_overdue', function ($i) {
                    return !in_array($i->status, ['Paid', 'Cancelled'])
                        && $i->tanggal_jatuh_tempo 
                        && Carbon::parse($i->tanggal_jatuh_tempo)->startOfDay()->lt(Carbon::today());
                })
                ->addColumn('status_badge', function ($i) {
                    switch ($i->status) {
                        case 'Draft':
                            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 font-monospace"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                        case 'Menunggu Persetujuan':
                            return '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-clock me-1"></i>Menunggu Approval</span>';
                        case 'Unpaid':
                            return '<span class="badge bg-primary text-white border border-primary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check2-circle me-1"></i>Disetujui (Unpaid)</span>';
                        case 'Partial':
                            return '<span class="badge bg-info text-dark border border-info rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pie-chart-fill me-1"></i>Sebagian</span>';
                        case 'Paid':
                            return '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check2-all me-1"></i>Lunas</span>';
                        case 'Rejected':
                            return '<span class="badge bg-danger text-white border border-danger rounded-pill px-3 py-1 fw-bold" title="' . e($i->alasan_penolakan ?? 'Ditolak') . '"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
                        case 'Cancelled':
                            return '<span class="badge bg-secondary text-white border border-secondary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-slash-circle me-1"></i>Batal</span>';
                        default:
                            return '<span class="badge bg-light text-dark border rounded-pill px-3 py-1">' . e($i->status) . '</span>';
                    }
                })
                ->addColumn('action', function ($i) {
                    $user = auth()->user();
                    $canApprove = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('approve-sales-invoice');
                    $canEdit = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('edit-sales-invoice');
                    $canDelete = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('hapus-sales-invoice');
                    $canViewJurnal = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-sales-invoice');
                    $canPay = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-sales-payment');
                    $isCreator = $i->created_by == $user->id;

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-circle btn-view-invoice" data-id="' . $i->id . '" title="Detail Faktur"><i class="bi bi-eye"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-circle btn-print-invoice" data-id="' . $i->id . '" title="Cetak Faktur"><i class="bi bi-printer"></i></button>';

                    // Tombol Lihat Jurnal Akuntansi (jika status Unpaid, Partial, atau Paid dan berhak)
                    if (in_array($i->status, ['Unpaid', 'Partial', 'Paid']) && $canViewJurnal) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-circle btn-view-jurnal" data-id="' . $i->id . '" data-nomor="' . e($i->nomor_faktur) . '" title="Lihat Jurnal Akuntansi"><i class="bi bi-journal-bookmark-fill"></i></button>';
                    }

                    // Tombol Catat Pembayaran Cepat (jika status Unpaid atau Partial dan berhak catat bayar)
                    if (in_array($i->status, ['Unpaid', 'Partial']) && $canPay) {
                        $btn .= '<a href="' . route('modul_marketing.pembayaran.index') . '?customer_id=' . $i->customer_id . '&invoice_id=' . $i->id . '" class="btn btn-sm btn-icon btn-outline-primary rounded-circle" title="Catat Pembayaran"><i class="bi bi-cash-stack"></i></a>';
                    }

                    // Tombol Ajukan (bila Draft atau Rejected)
                    if (in_array($i->status, ['Draft', 'Rejected']) && ($canEdit || $isCreator)) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle btn-submit-invoice" data-id="' . $i->id . '" data-nomor="' . e($i->nomor_faktur) . '" title="Ajukan Approval"><i class="bi bi-send"></i></button>';
                    }

                    // Tombol Approve & Reject (bila Menunggu Persetujuan)
                    if ($i->status === 'Menunggu Persetujuan' && $canApprove) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-circle btn-approve-invoice" data-id="' . $i->id . '" data-nomor="' . e($i->nomor_faktur) . '" title="Setujui (Approve)"><i class="bi bi-check-lg"></i></button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-reject-invoice" data-id="' . $i->id . '" data-nomor="' . e($i->nomor_faktur) . '" title="Tolak (Reject)"><i class="bi bi-x-lg"></i></button>';
                    }

                    // Edit Faktur: Draf, Menunggu Persetujuan, dan Ditolak bisa diedit
                    if (in_array($i->status, ['Draft', 'Rejected', 'Menunggu Persetujuan']) && ($canEdit || $isCreator)) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-invoice" data-id="' . $i->id . '" title="Edit Faktur"><i class="bi bi-pencil"></i></button>';
                    }

                    // Tombol Batal Setujui / Kembalikan ke Draf (Khusus Super Admin jika status Unpaid dan belum ada riwayat bayar)
                    $hasAnyPayment = (float)$i->paid_amount > 0 || (float)$i->allocated_payment_amount > 0;
                    if ($i->status === 'Unpaid' && $user->hasRole('Super Admin') && !$hasAnyPayment) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-unapprove-invoice" data-id="' . $i->id . '" data-nomor="' . e($i->nomor_faktur) . '" title="Batal Setujui (Kembalikan ke Draf)"><i class="bi bi-arrow-counterclockwise"></i></button>';
                    }

                    // Hapus Faktur:
                    // Jika statusnya Disetujui (Unpaid, Paid, Approved, dll) atau Menunggu Persetujuan, tombol HAPUS TIDAK TAMPIL.
                    // Tombol hapus hanya tampil untuk status Draft dan Rejected.
                    if (in_array($i->status, ['Draft', 'Rejected']) && ($canDelete || ($i->status === 'Draft' && $isCreator))) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-invoice" data-id="' . $i->id . '" title="Hapus Faktur"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->with([
                    'totalFaktur'           => number_format($totalFaktur, 0, ',', '.'),
                    'totalPiutangUnpaid'    => 'Rp ' . number_format($totalPiutangUnpaid, 2, ',', '.'),
                    'totalJatuhTempo'       => number_format($totalJatuhTempo, 0, ',', '.'),
                    'totalTerbayarBulanIni' => 'Rp ' . number_format($totalTerbayarBulanIni, 2, ',', '.'),
                ])
                ->rawColumns(['nomor_faktur', 'customer_name', 'tanggal_jatuh_tempo_fmt', 'grand_total_fmt', 'sisa_tagihan_fmt', 'status_badge', 'action'])
                ->make(true);
        }

        $customers = MasterCustomer::orderBy('nama_customer')->get();
        $pajaks = MasterPajak::orderBy('nama_pajak')->get();
        $paymentTerms = PaymentTerm::orderBy('nama_term')->get();
        $currencies = MasterCurrency::all();
        
        $tendersQuery = Tender::query();
        if (!empty($activeBranchId)) {
            $tendersQuery->where('branch_id', $activeBranchId);
        } else {
            $tendersQuery->where(function($q) use ($activeCompanyId) {
                $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
            });
        }
        $tenders = $tendersQuery->orderByDesc('id')->get();
        $accounts = ChartOfAccount::where(function($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
            })
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        $kasBanks = KasBank::where('is_active', true)
            ->where(function($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('branch_id', $activeBranchId);
                } else {
                    $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
                }
            })
            ->orderBy('nama_akun')
            ->get();

        return view('modul_marketing.faktur_penjualan.index', compact(
            'totalFaktur',
            'totalPiutangUnpaid',
            'totalJatuhTempo',
            'totalTerbayarBulanIni',
            'customers',
            'pajaks',
            'paymentTerms',
            'currencies',
            'accounts',
            'kasBanks',
            'tenders'
        ));
    }

    public function getDeliveryOrders(Request $request)
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = DeliveryOrder::with(['customer:id,nama_customer', 'salesOrder:id,nomor_so'])
            ->whereIn('status', ['Disetujui', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered']);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->where(function($q) use ($activeCompanyId) {
                $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('q')) {
            $query->where('nomor_do', 'like', '%' . trim($request->q) . '%');
        }

        // Subquery aman untuk jutaan data (mencegah memory limit PHP & placeholder limit MySQL)
        $query->whereNotExists(function($sub) {
            $sub->select(DB::raw(1))
                ->from('sales_invoices')
                ->whereColumn('sales_invoices.delivery_order_id', 'delivery_orders.id')
                ->where('sales_invoices.status', '!=', 'Cancelled')
                ->whereNull('sales_invoices.deleted_at');
        });

        $deliveryOrders = $query->orderByDesc('id')->limit(50)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $deliveryOrders,
        ]);
    }

    public function getDeliveryOrderDetails($id)
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = DeliveryOrder::with([
            'customer',
            'salesOrder.currency',
            'salesOrder.paymentTerm',
            'salesOrder.tender',
            'salesOrder.quotation.tender',
            'details.barang.satuan',
            'details.salesOrderDetail'
        ]);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->where(function($q) use ($activeCompanyId) {
                $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
            });
        }

        $do = $query->findOrFail($id);

        $approvedDoStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered'];
        if (!in_array($do->status, $approvedDoStatuses)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Surat Jalan {$do->nomor_do} masih berstatus \"{$do->status}\" (belum di-ACC/disetujui). Faktur Penjualan hanya dapat dibuat setelah pengiriman disetujui."
            ], 422);
        }

        $tender = $do->salesOrder?->tender ?: ($do->salesOrder?->quotation?->tender ?? null);
        $tenderId = $tender?->id ?: null;
        $tenderText = $tender ? ($tender->nama_tender . (!empty($tender->nomor_kontrak) && $tender->nomor_kontrak !== '-' ? ' (' . $tender->nomor_kontrak . ')' : '')) : null;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                => $do->id,
                'nomor_do'          => $do->nomor_do,
                'sales_order_id'    => $do->sales_order_id,
                'nomor_so'          => $do->salesOrder?->nomor_so ?? '-',
                'tender_id'         => $tenderId,
                'nama_tender'       => $tender?->nama_tender ?? '-',
                'nomor_kontrak'     => $tender?->nomor_kontrak ?? '-',
                'tender_text'       => $tenderText,
                'customer_id'       => $do->customer_id,
                'customer_name'     => $do->customer?->nama_customer ?? '-',
                'currency_id'       => $do->salesOrder?->currency_id,
                'payment_term_id'   => $do->salesOrder?->payment_term_id,
                'pajak_id'          => $do->salesOrder?->pajak_id,
                'items'             => $do->details->map(function ($det) {
                    $soItem = $det->salesOrderDetail ?? null;
                    $harga = $soItem ? (float)$soItem->harga_satuan : (float)($det->barang?->harga_jual ?? 0);
                    $diskonPersen = $soItem ? (float)$soItem->diskon_persen : 0;
                    $diskonNominal = $soItem ? (float)$soItem->diskon_nominal : 0;
                    $ppnPajakId = $soItem ? $soItem->ppn_pajak_id : null;
                    $tarifPpn = $soItem ? (float)($soItem->tarif_ppn ?? 0) : 0;

                    $qtyKirim = (float) ($det->qty_kirim ?? $det->qty_order ?? 0);
                    $gross = $qtyKirim * $harga;
                    $diskonNom = $gross * ($diskonPersen / 100);
                    $net = $gross - $diskonNom;
                    $nilaiPpn = $net * ($tarifPpn / 100);
                    $subtotal = $net + $nilaiPpn;

                    return [
                        'barang_id'      => $det->barang_id,
                        'nama_barang'    => $det->nama_barang_custom ?? ($det->barang?->nama_barang ?? 'Item'),
                        'satuan_id'      => $det->barang?->satuan_id,
                        'satuan_nama'    => $det->nama_satuan ?? ($det->barang?->satuan?->nama_satuan ?? '-'),
                        'qty'            => $qtyKirim,
                        'harga_satuan'   => $harga,
                        'diskon_persen'  => $diskonPersen,
                        'diskon_nominal' => $diskonNominal,
                        'ppn_pajak_id'   => $ppnPajakId,
                        'tarif_ppn'      => $tarifPpn,
                        'nilai_ppn'      => $nilaiPpn,
                        'total_harga'    => $subtotal,
                    ];
                }),
            ],
        ]);
    }

    public function getSalesOrders(Request $request)
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesOrder::with([
            'customer:id,nama_customer,nama_perusahaan',
            'currency:id,kode_currency',
            'tender:id,nama_tender,nomor_kontrak',
            'quotation.tender:id,nama_tender,nomor_kontrak',
            'details.deliveryOrderDetails' => function ($q) {
                $q->whereHas('deliveryOrder', function ($q2) {
                    $q2->whereIn('status', ['Disetujui', 'Terkirim', 'Selesai', 'Approved', 'Dikirim']);
                });
            }
        ])
        ->whereNotIn('sales_orders.status', ['Cancelled', 'Draft']);

        if (!empty($activeBranchId)) {
            $query->where('sales_orders.branch_id', $activeBranchId);
        } else {
            $query->where(function($q) use ($activeCompanyId) {
                $q->whereNull('sales_orders.branch_id')->where(function($cq) use ($activeCompanyId) {
                    $cq->where('sales_orders.company_id', $activeCompanyId)->orWhereNull('sales_orders.company_id');
                });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('sales_orders.customer_id', $request->customer_id);
        }

        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function($sq) use ($term) {
                $sq->where('sales_orders.nomor_so', 'like', "%{$term}%")
                   ->orWhereHas('customer', function($cq) use ($term) {
                       $cq->where('nama_customer', 'like', "%{$term}%")
                          ->orWhere('kode_customer', 'like', "%{$term}%");
                   });
            });
        }

        // Hanya exclude SO yang SELURUH item-nya sudah diinvoice
        // SO masih tampil jika ada sisa qty yang belum diinvoice (partial invoicing diizinkan)
        $ignoreInvoiceId = $request->filled('ignore_invoice_id') ? (int) $request->ignore_invoice_id : null;

        $query->where(function ($q) use ($ignoreInvoiceId) {
            // Kondisi: ada minimal 1 item SO yang qty belum semua diinvoice
            // Menggunakan correlated subquery per-detail barang
            $q->whereExists(function ($sub) use ($ignoreInvoiceId) {
                $sub->select(DB::raw(1))
                    ->from('sales_order_details as sod_chk')
                    ->whereColumn('sod_chk.sales_order_id', 'sales_orders.id')
                    ->whereRaw(
                        'sod_chk.qty > COALESCE((
                            SELECT SUM(sid.qty)
                            FROM sales_invoice_details sid
                            JOIN sales_invoices si ON si.id = sid.sales_invoice_id
                            WHERE si.sales_order_id = sales_orders.id
                            AND si.status != ?
                            AND si.deleted_at IS NULL
                            AND sid.barang_id = sod_chk.barang_id
                            ' . ($ignoreInvoiceId ? 'AND si.id != ' . $ignoreInvoiceId : '') . '
                        ), 0)',
                        ['Cancelled']
                    );
            });
        });

        $salesOrders = $query->orderByDesc('id')->limit(50)->get()->map(function ($so) {
            $totalSoQty        = (float) $so->details->sum('qty');
            $totalDeliveredQty = 0;
            $totalInvoicedQty  = 0;

            foreach ($so->details as $det) {
                $totalDeliveredQty += (float) $det->deliveryOrderDetails->sum('qty_kirim');
            }

            // Hitung total qty yang sudah diinvoice untuk SO ini
            $totalInvoicedQty = (float) DB::table('sales_invoice_details')
                ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_details.sales_invoice_id')
                ->where('sales_invoices.sales_order_id', $so->id)
                ->where('sales_invoices.status', '!=', 'Cancelled')
                ->whereNull('sales_invoices.deleted_at')
                ->sum('sales_invoice_details.qty');

            $sisaBelumInvoice = max(0, $totalSoQty - $totalInvoicedQty);

            $shippingStatus = 'Belum Dikirim';
            if ($totalDeliveredQty >= $totalSoQty && $totalSoQty > 0) {
                $shippingStatus = 'Selesai Dikirim (100%)';
            } elseif ($totalDeliveredQty > 0) {
                $percent = round(($totalDeliveredQty / max(1, $totalSoQty)) * 100);
                $shippingStatus = "Parsial ({$percent}% Terkirim - Sisa: " . ($totalSoQty - $totalDeliveredQty) . ")";
            }

            // Tambahkan info invoice jika sudah ada sebagian yang diinvoice
            $invoiceStatus = '';
            if ($totalInvoicedQty > 0 && $sisaBelumInvoice > 0) {
                $invoiceStatus = " | Invoice Parsial (Sisa: {$sisaBelumInvoice})";
            }

            $tender = $so->tender ?: ($so->quotation?->tender ?? null);
            $tenderId = $tender?->id ?: null;
            $tenderName = $tender ? ($tender->nama_tender . (!empty($tender->nomor_kontrak) && $tender->nomor_kontrak !== '-' ? ' (' . $tender->nomor_kontrak . ')' : '')) : null;

            return [
                'id'                   => $so->id,
                'nomor_so'             => $so->nomor_so,
                'tanggal_so'           => $so->tanggal_so ? $so->tanggal_so->format('d/m/Y') : '-',
                'grand_total'          => (float) ($so->grand_total ?? $so->total),
                'tender_id'            => $tenderId,
                'tender_name'          => $tenderName,
                'customer_id'          => $so->customer_id,
                'customer_name'        => $so->customer?->nama_customer ?? '-',
                'total_so_qty'         => $totalSoQty,
                'total_delivered_qty'  => $totalDeliveredQty,
                'total_invoiced_qty'   => $totalInvoicedQty,
                'sisa_belum_invoice'   => $sisaBelumInvoice,
                'sisa_unshipped_qty'   => max(0, $totalSoQty - $totalDeliveredQty),
                'shipping_status'      => $shippingStatus . $invoiceStatus,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $salesOrders,
        ]);
    }

    public function getSalesOrderDetails($id)
    {
        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        $query = SalesOrder::with([
            'customer',
            'currency',
            'paymentTerm',
            'tender',
            'quotation.tender',
            'details.barang.satuan',
            'details.satuan',
            'details.ppnPajak',
            'details.deliveryOrderDetails' => function ($q) {
                $q->whereHas('deliveryOrder', function ($q2) {
                    $q2->whereIn('status', ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered']);
                });
            }
        ]);

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->where(function($q) use ($activeCompanyId) {
                $q->whereNull('branch_id')->where(function($cq) use ($activeCompanyId) {
                    $cq->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                });
            });
        }

        $so = $query->findOrFail($id);

        $items = $so->details->map(function ($det) use ($so) {
            $harga        = (float) $det->harga_satuan;
            $soQty        = (float) $det->qty;
            $deliveredQty = (float) $det->deliveryOrderDetails->sum('qty_kirim');
            $sisaUnshipped = max(0, $soQty - $deliveredQty);
            $diskonPersen  = (float) $det->diskon_persen;
            $diskonNominal = (float) $det->diskon_nominal;
            $ppnPajakId    = $det->ppn_pajak_id;
            $tarifPpn      = (float) ($det->tarif_ppn ?? 0);

            // Hitung qty yang sudah diinvoice dari SO ini untuk barang yang sama
            $qtyInvoiced = (float) DB::table('sales_invoice_details')
                ->join('sales_invoices', 'sales_invoices.id', '=', 'sales_invoice_details.sales_invoice_id')
                ->where('sales_invoices.sales_order_id', $so->id)
                ->where('sales_invoices.status', '!=', 'Cancelled')
                ->whereNull('sales_invoices.deleted_at')
                ->where('sales_invoice_details.barang_id', $det->barang_id)
                ->sum('sales_invoice_details.qty');

            // Sisa yang belum diinvoice = SO qty - sudah invoiced
            $sisaBelumInvoice = max(0, $soQty - $qtyInvoiced);

            // Default qty faktur = sisa belum invoice (parsial), jika 0 berarti sudah full
            $invoiceQty = $sisaBelumInvoice;

            $nilaiPpn = (($invoiceQty * $harga) - $diskonNominal) * ($tarifPpn / 100);
            $subtotal  = ($invoiceQty * $harga) - $diskonNominal + $nilaiPpn;

            return [
                'barang_id'           => $det->barang_id,
                'kode_barang'         => $det->barang?->kode_barang ?? ($det->barang?->barcode ?? 'PRODUK'),
                'nama_barang'         => $det->nama_barang_custom ?? ($det->barang?->nama_barang ?? 'Item'),
                'satuan_id'           => $det->satuan_id ?? $det->barang?->satuan_id,
                'satuan_nama'         => $det->satuan?->nama_satuan ?? ($det->nama_satuan ?? '-'),
                'qty_so'              => $soQty,
                'qty_delivered'       => $deliveredQty,
                'qty_invoiced'        => $qtyInvoiced,
                'sisa_unshipped'      => $sisaUnshipped,
                'sisa_belum_invoice'  => $sisaBelumInvoice,
                'qty'                 => $invoiceQty,
                'harga_satuan'        => $harga,
                'diskon_persen'       => $diskonPersen,
                'diskon_nominal'      => $diskonNominal,
                'ppn_pajak_id'        => $ppnPajakId,
                'tarif_ppn'           => $tarifPpn,
                'nilai_ppn'           => $nilaiPpn,
                'total_harga'         => $subtotal,
            ];
        })
        // Exclude item yang sudah fully invoiced (sisa = 0)
        ->filter(fn($item) => $item['sisa_belum_invoice'] > 0)
        ->values();

        // Hitung sisa DP yang belum pernah dipotong di faktur penjualan lain dari SO ini
        $usedDp = (float) SalesInvoice::where('sales_order_id', $so->id)
            ->where('status', '!=', 'Cancelled')
            ->whereNull('deleted_at')
            ->sum('uang_muka');
        $totalDpSo = (float) ($so->dp_nominal ?? 0);
        $sisaDpSo  = max(0, $totalDpSo - $usedDp);

        $tender = $so->tender ?: ($so->quotation?->tender ?? null);
        $tenderId = $tender?->id ?: null;
        $tenderText = $tender ? ($tender->nama_tender . (!empty($tender->nomor_kontrak) && $tender->nomor_kontrak !== '-' ? ' (' . $tender->nomor_kontrak . ')' : '')) : null;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                => $so->id,
                'nomor_so'          => $so->nomor_so,
                'tender_id'         => $tenderId,
                'nama_tender'       => $tender?->nama_tender ?? '-',
                'nomor_kontrak'     => $tender?->nomor_kontrak ?? '-',
                'tender_text'       => $tenderText,
                'customer_id'       => $so->customer_id,
                'customer_name'     => $so->customer?->nama_customer ?? '-',
                'currency_id'       => $so->currency_id,
                'payment_term_id'   => $so->payment_term_id,
                'pajak_id'          => $so->ppn_pajak_id,
                'biaya_pengiriman'  => (float) ($so->biaya_pengiriman ?? 0),
                'uang_muka'         => $sisaDpSo,
                'dp_nominal_total'  => $totalDpSo,
                'dp_sudah_dipotong' => $usedDp,
                'dp_persen'         => (float) ($so->dp_persen ?? 0),
                'sisa_tagihan'      => (float) ($so->sisa_tagihan ?? 0),
                'items'             => $items,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-sales-invoice');

        $request->validate([
            'nomor_faktur'        => 'nullable|string|max:50|unique:sales_invoices,nomor_faktur',
            'tanggal_faktur'      => 'required|date',
            'customer_id'         => 'required|exists:master_customers,id',
            'delivery_order_id'   => 'nullable|exists:delivery_orders,id',
            'sales_order_id'      => 'nullable|exists:sales_orders,id',
            'tender_id'           => 'nullable|exists:tenders,id',
            'payment_term_id'     => 'nullable|exists:payment_terms,id',
            'currency_id'         => 'nullable|exists:master_currencies,id',
            'kurs'                => 'nullable|numeric|min:0.0001',
            'subtotal'            => 'required|numeric|min:0',
            'diskon_persen'       => 'nullable|numeric|min:0|max:100',
            'diskon_nominal'      => 'nullable|numeric|min:0',
            'pajak_id'            => 'nullable|exists:master_pajaks,id',
            'pajak_nominal'       => 'nullable|numeric|min:0',
            'biaya_lain'          => 'nullable|numeric|min:0',
            'uang_muka'           => 'nullable|numeric|min:0',
            'uang_muka_coa_id'    => 'nullable|exists:chart_of_accounts,id',
            'kas_bank_id'         => 'nullable|exists:kas_banks,id',
            'grand_total'         => 'required|numeric|min:0',
            'items'               => 'required|array|min:1|max:50',
            'items.*.nama_barang' => 'required|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.harga_satuan'=> 'required|numeric|min:0',
            'costs'               => 'nullable|array|max:15',
            'costs.*.jenis_biaya' => 'required_with:costs|string|max:255',
            'costs.*.nominal'     => 'required_with:costs|numeric|min:0',
            'costs.*.coa_id'      => 'nullable|exists:chart_of_accounts,id',
            'file_pendukung'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'file_pendukung.max'   => 'Ukuran file dokumen/foto pendukung maksimal 10 MB.',
            'file_pendukung.mimes' => 'Format file pendukung harus berupa PDF, JPG, JPEG, PNG, atau WEBP.',
            'uang_muka.numeric'    => 'Nominal Uang Muka (DP) harus diisi berupa angka.',
            'uang_muka.min'        => 'Nominal Uang Muka (DP) tidak boleh bernilai negatif.',
        ]);

        $rawUangMuka = trim((string)($request->uang_muka ?? ''));
        if (strlen($rawUangMuka) > 20) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Input nominal Uang Muka (DP) tidak boleh melebihi 20 karakter.'
            ], 422);
        }

        $grandTotalCheck = $this->parseNumber($request->grand_total ?? 0);
        $uangMukaCheck   = $this->parseNumber($request->uang_muka ?? 0);

        if ($uangMukaCheck < 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nominal Uang Muka (DP) harus berupa angka positif.'
            ], 422);
        }

        if ($grandTotalCheck > 0 && $uangMukaCheck > $grandTotalCheck) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' . number_format($grandTotalCheck, 2, ',', '.') . ').'
            ], 422);
        }

        if ($grandTotalCheck <= 0 && $uangMukaCheck > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Total Faktur masih Rp 0. Silakan isi rincian barang/jasa terlebih dahulu sebelum mengisi Uang Muka (DP).'
            ], 422);
        }

        if ($uangMukaCheck > 0) {
            if (!$request->filled('kas_bank_id')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Rekening Kas & Bank penerima wajib dipilih apabila Faktur Penjualan memiliki nominal Uang Muka (DP).'
                ], 422);
            }
            if (!$request->filled('uang_muka_coa_id')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akun Uang Muka Penjualan (COA) wajib dipilih apabila Faktur Penjualan memiliki nominal Uang Muka (DP).'
                ], 422);
            }
        }

        if ($request->filled('delivery_order_id')) {
            $checkDo = DeliveryOrder::find($request->delivery_order_id);
            $approvedDoStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered'];
            if ($checkDo && !in_array($checkDo->status, $approvedDoStatuses)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Surat Jalan {$checkDo->nomor_do} masih berstatus \"{$checkDo->status}\" (belum di-ACC/disetujui). Faktur Penjualan hanya dapat dibuat jika Surat Jalan sudah disetujui."
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $activeBranchId = session('active_branch_id');
            $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

            $filePath = null;
            if ($request->hasFile('file_pendukung')) {
                $filePath = $this->compressAndSaveFile($request->file('file_pendukung'));
            }

            // Calculate Due Date based on Payment Term if not specified
            $tanggalJatuhTempo = null;
            if ($request->filled('tanggal_jatuh_tempo')) {
                $tanggalJatuhTempo = $request->tanggal_jatuh_tempo;
            } elseif ($request->filled('payment_term_id')) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari) {
                    $tanggalJatuhTempo = Carbon::parse($request->tanggal_faktur)->addDays($term->jumlah_hari)->toDateString();
                }
            }
            if (!$tanggalJatuhTempo) {
                $tanggalJatuhTempo = $request->tanggal_faktur;
            }

            // Generate Automatic Invoice Number if not manually entered
            if ($request->filled('nomor_faktur')) {
                $nomorFaktur = trim($request->nomor_faktur);
            } else {
                $nomorFaktur = SalesInvoice::generateNomorFaktur($activeCompanyId, $activeBranchId, $request->tanggal_faktur);
            }

            $grandTotal  = $this->parseNumber($request->grand_total);
            $uangMuka    = $this->parseNumber($request->uang_muka ?? 0);
            $sisaTagihan = max(0, $grandTotal - $uangMuka);

            $actionType = $request->input('action_type', 'submit');
            if ($actionType === 'draft') {
                $status = 'Draft';
            } elseif ($actionType === 'post') {
                if ($sisaTagihan <= 0.01) {
                    $status = 'Paid';
                } elseif ($uangMuka > 0) {
                    $status = 'Partial';
                } else {
                    $status = 'Unpaid';
                }
            } else {
                $status = 'Menunggu Persetujuan';
            }

            $totalBiayaLain = 0;
            $costsInput = $request->input('costs', []);
            if (is_array($costsInput) && count($costsInput) > 0) {
                foreach ($costsInput as $cInput) {
                    $nom = $this->parseNumber($cInput['nominal'] ?? 0);
                    if ($nom > 0) {
                        $totalBiayaLain += $nom;
                    }
                }
            } else {
                $totalBiayaLain = $this->parseNumber($request->biaya_lain);
            }

            $tenderId = $request->tender_id;
            if (empty($tenderId)) {
                if ($request->filled('delivery_order_id')) {
                    $do = DeliveryOrder::with('salesOrder')->find($request->delivery_order_id);
                    $tenderId = $do?->salesOrder?->tender_id;
                }
                if (empty($tenderId) && $request->filled('sales_order_id')) {
                    $so = SalesOrder::find($request->sales_order_id);
                    $tenderId = $so?->tender_id ?: ($so?->quotation?->tender_id ?? null);
                }
            }

            $invoice = SalesInvoice::create([
                'nomor_faktur'       => $nomorFaktur,
                'nomor_faktur_pajak' => $request->nomor_faktur_pajak,
                'tanggal_faktur'     => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'=> $tanggalJatuhTempo,
                'sales_order_id'     => $request->sales_order_id,
                'delivery_order_id'  => $request->delivery_order_id,
                'tender_id'          => $tenderId,
                'customer_id'        => $request->customer_id,
                'branch_id'          => $activeBranchId,
                'company_id'         => $activeBranchId ? null : $activeCompanyId,
                'currency_id'        => $request->currency_id ?: 1,
                'kurs'               => $this->parseNumber($request->kurs ?: 1),
                'subtotal'           => $this->parseNumber($request->subtotal),
                'diskon_persen'      => $this->parseNumber($request->diskon_persen),
                'diskon_nominal'     => $this->parseNumber($request->diskon_nominal),
                'pajak_id'           => $request->pajak_id,
                'pajak_nominal'      => $this->parseNumber($request->pajak_nominal),
                'pph_pajak_id'       => $request->pph_pajak_id,
                'tarif_pph'          => $this->parseNumber($request->tarif_pph),
                'pph_nominal'        => $this->parseNumber($request->pph_nominal),
                'pph_coa_id'         => $request->pph_coa_id,
                'biaya_lain'         => $totalBiayaLain,
                'biaya_lain_coa_id'  => $request->biaya_lain_coa_id,
                'grand_total'        => $grandTotal,
                'uang_muka'          => $uangMuka,
                'uang_muka_coa_id'   => $request->uang_muka_coa_id,
                'kas_bank_id'        => $request->kas_bank_id,
                'sisa_tagihan'       => $sisaTagihan,
                'status'             => $status,
                'payment_term_id'    => $request->payment_term_id,
                'catatan'            => $request->catatan,
                'file_pendukung'     => $filePath,
                'created_by'         => auth()->id(),
            ]);

            // Save multi-row additional costs if provided
            if (is_array($costsInput) && count($costsInput) > 0) {
                foreach ($costsInput as $cInput) {
                    $nom = $this->parseNumber($cInput['nominal'] ?? 0);
                    if ($nom > 0 || !empty($cInput['jenis_biaya'])) {
                        \App\Models\SalesInvoiceCost::create([
                            'sales_invoice_id' => $invoice->id,
                            'jenis_biaya'      => $cInput['jenis_biaya'] ?? 'Biaya Pengiriman',
                            'nominal'          => $nom,
                            'coa_id'           => !empty($cInput['coa_id']) ? $cInput['coa_id'] : null,
                            'keterangan'       => $cInput['keterangan'] ?? null,
                        ]);
                    }
                }
            }

            foreach ($request->items as $item) {
                $qty = $this->parseNumber($item['qty'] ?? 0);
                $harga = $this->parseNumber($item['harga_satuan'] ?? 0);
                $diskonPersen = $this->parseNumber($item['diskon_persen'] ?? 0);
                $subBruto = $qty * $harga;

                $diskonNom = isset($item['diskon_nominal']) && $item['diskon_nominal'] !== ''
                    ? $this->parseNumber($item['diskon_nominal'])
                    : ($subBruto * ($diskonPersen / 100));

                $dppItem = $subBruto - $diskonNom;

                $ppnPajakId = $item['ppn_pajak_id'] ?? null;
                $tarifPpn = 0;
                if (!empty($ppnPajakId)) {
                    if (isset($item['tarif_ppn']) && $item['tarif_ppn'] !== '' && (float)$item['tarif_ppn'] > 0) {
                        $tarifPpn = $this->parseNumber($item['tarif_ppn']);
                    } else {
                        $pajakObj = MasterPajak::find($ppnPajakId);
                        if ($pajakObj) $tarifPpn = (float)$pajakObj->tarif;
                    }
                }

                $nilaiPpn = isset($item['nilai_ppn']) && $item['nilai_ppn'] !== '' && (float)$item['nilai_ppn'] > 0
                    ? $this->parseNumber($item['nilai_ppn'])
                    : ($dppItem * ($tarifPpn / 100));

                $totalHarga = isset($item['total_harga']) && $item['total_harga'] !== '' && (float)$item['total_harga'] > 0
                    ? $this->parseNumber($item['total_harga'])
                    : ($dppItem + $nilaiPpn);

                SalesInvoiceDetail::create([
                    'sales_invoice_id' => $invoice->id,
                    'barang_id'        => $item['barang_id'] ?? null,
                    'nama_barang'      => $item['nama_barang'],
                    'satuan_id'        => $item['satuan_id'] ?? null,
                    'qty'              => $qty,
                    'harga_satuan'     => $harga,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonNom,
                    'ppn_pajak_id'     => $ppnPajakId,
                    'tarif_ppn'        => $tarifPpn,
                    'nilai_ppn'        => $nilaiPpn,
                    'total_harga'      => $totalHarga,
                    'catatan'          => $item['catatan'] ?? null,
                ]);
            }

            // Post Automatic Journal & send WhatsApp notification based on workflow status
            if ($status === 'Unpaid') {
                $this->createAutomaticJournal($invoice);
                WhatsAppGatewayController::sendSalesInvoiceApprovedNotification($invoice, auth()->user());
            } elseif ($status === 'Menunggu Persetujuan') {
                WhatsAppGatewayController::sendSalesInvoiceSubmissionNotification($invoice);
            }

            $this->clearKpiCache();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Penjualan {$nomorFaktur} berhasil disimpan!",
                'data'    => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store SalesInvoice: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan Faktur Penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $this->authorize('lihat-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id, [
            'customer',
            'deliveryOrder.details',
            'salesOrder.deliveryOrders',
            'salesOrder.details.deliveryOrderDetails.deliveryOrder',
            'tender',
            'pphPajak',
            'costs',
            'details',
            'jurnal.details.account',
            'paymentDetails.payment.kasBank',
            'paymentDetails.payment.paymentMethod',
        ]);

        // Jika statusnya Draft, hanya pembuatnya yang dapat melihat
        if ($invoice->status === 'Draft' && $invoice->created_by != auth()->id()) {
            abort(403, 'Draf faktur penjualan ini hanya dapat diakses oleh pembuatnya.');
        }

        if (request()->wantsJson() && !request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'data'   => $invoice,
            ]);
        }

        if (in_array($invoice->status, ['Unpaid', 'Partial', 'Paid'])) {
            $this->syncInvoiceDpKasMutation($invoice);
        }

        return view('modul_marketing.faktur_penjualan._modal_detail_content', compact('invoice'));
    }

    public function downloadDocument($id)
    {
        $this->authorize('lihat-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id);

        if (!$invoice->file_pendukung || !Storage::disk('public')->exists($invoice->file_pendukung)) {
            abort(404, 'Dokumen pendukung tidak ditemukan.');
        }

        $extension = pathinfo($invoice->file_pendukung, PATHINFO_EXTENSION);
        $cleanInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->nomor_faktur ?: 'faktur');
        $downloadName = "Dokumen_Faktur_{$cleanInvoiceNumber}.{$extension}";

        return Storage::disk('public')->download($invoice->file_pendukung, $downloadName);
    }

    /**
     * Ambil data Jurnal Akuntansi Faktur Penjualan (JSON)
     */
    public function getJurnal($id)
    {
        $this->authorize('lihat-jurnal-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id, [
            'customer',
            'branch',
            'company',
            'salesOrder.tender',
            'tender',
        ]);

        $jurnal = Jurnal::with(['details.account', 'creator', 'branch', 'company'])
            ->where('referensi_tipe', 'Sales Invoice')
            ->where('referensi_id', $invoice->id)
            ->first();

        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => "Belum ada Jurnal Akuntansi yang dibukukan untuk Faktur Penjualan {$invoice->nomor_faktur}."
            ], 404);
        }

        return response()->json([
            'success' => true,
            'jurnal'  => $jurnal,
            'invoice' => $invoice
        ]);
    }

    public function print($id)
    {
        $this->authorize('lihat-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id, [
            'customer',
            'deliveryOrder',
            'salesOrder.deliveryOrders',
            'tender',
            'details.barang.satuan',
            'details.satuan',
            'details.pajak',
            'costs.coa',
            'currency',
            'paymentTerm',
            'pajak',
            'pphPajak',
            'company',
            'branch.company',
            'creator',
            'paymentDetails.payment.kasBank',
            'paymentDetails.payment.paymentMethod',
        ]);

        // Jika statusnya Draft, hanya pembuatnya yang dapat mencetak (atau Super Admin / Owner)
        if ($invoice->status === 'Draft' && $invoice->created_by != auth()->id() && !auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) {
            abort(403, 'Draf faktur penjualan ini hanya dapat diakses dan dicetak oleh pembuatnya.');
        }

        $isBranch = !empty($invoice->branch_id);
        $branch = null;
        $company = null;

        if ($isBranch) {
            $branch = $invoice->branch ?: \App\Models\Branch::with('company')->find($invoice->branch_id);
            $company = $branch?->company ?: ($invoice->company ?: \App\Models\Company::first());
            $entityName = $branch?->nama_cabang ?: '';
            $parentName = ''; // Jangan tampilkan nama perusahaan induk jika di cabang
            $alamatParts = array_filter([
                $branch?->address ?: ($branch?->alamat ?? ''),
                $branch?->city ?: ($branch?->kota ?? ''),
                $branch?->province ?: ($branch?->provinsi ?? ''),
                $branch?->postal_code ?: ($branch?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $branch?->phone ?: ($branch?->telepon ?? '');
            $entityEmail   = $branch?->email ?: '';
            $entityNpwp    = '';
        } else {
            $company = $invoice->company ?: (\App\Models\Company::find($invoice->company_id) ?: \App\Models\Company::first());
            $entityName = $company?->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA';
            $parentName = '';
            $alamatParts = array_filter([
                $company?->address ?: ($company?->alamat ?? ''),
                $company?->city ?: ($company?->kota ?? ''),
                $company?->province ?: ($company?->provinsi ?? ''),
                $company?->postal_code ?: ($company?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $company?->phone ?: ($company?->telepon ?? '');
            $entityEmail   = $company?->email ?: '';
            $entityNpwp    = $company?->npwp ?: '';
        }

        $activeLogo = null;
        if ($isBranch && $branch && !empty($branch->logo)) {
            if (file_exists(public_path('storage/' . $branch->logo))) {
                $activeLogo = asset('storage/' . $branch->logo);
            } elseif (file_exists(public_path($branch->logo))) {
                $activeLogo = asset($branch->logo);
            }
        }
        if (!$activeLogo && $company && !empty($company->logo)) {
            if (file_exists(public_path('storage/' . $company->logo))) {
                $activeLogo = asset('storage/' . $company->logo);
            } elseif (file_exists(public_path($company->logo))) {
                $activeLogo = asset($company->logo);
            }
        }
        if (!$activeLogo) {
            $activeLogo = asset('gambar/logo.png');
        }

        $bankAccounts = \App\Models\KasBank::where('is_active', true)
            ->where(function($q) use ($invoice) {
                if (!empty($invoice->branch_id)) {
                    $q->where('branch_id', $invoice->branch_id);
                } else {
                    $q->where('company_id', $invoice->company_id ?: 1);
                }
            })
            ->whereNotNull('nomor_rekening')
            ->where('nomor_rekening', '!=', '')
            ->get();

        $validPayments = $invoice->paymentDetails ? $invoice->paymentDetails->filter(function($d) {
            return $d->payment && $d->payment->status !== 'Cancelled' && empty($d->payment->deleted_at);
        }) : collect();
        $totalPaid = (float)($invoice->uang_muka ?? 0) + (float)$validPayments->sum(function($d) {
            return (float)$d->jumlah_bayar + (float)$d->potongan;
        });
        $sisaTagihanBerjalan = max(0, (float)$invoice->grand_total - $totalPaid);
        $tagihanAkhir = ($invoice->status === 'Paid') ? 0 : $sisaTagihanBerjalan;
        $terbilang = $tagihanAkhir <= 0.01 ? 'Nol Rupiah (Lunas)' : ($this->terbilang($tagihanAkhir) . ' Rupiah');

        return view('modul_marketing.faktur_penjualan.print_faktur', compact(
            'invoice',
            'company',
            'branch',
            'isBranch',
            'entityName',
            'parentName',
            'entityAddress',
            'entityPhone',
            'entityEmail',
            'entityNpwp',
            'activeLogo',
            'bankAccounts',
            'terbilang'
        ));
    }

    private function terbilang($angka)
    {
        $angka = abs((float)$angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $terbilang = '';

        if ($angka < 12) {
            $terbilang = ' ' . $baca[(int)$angka];
        } elseif ($angka < 20) {
            $terbilang = $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $terbilang = $this->terbilang((int)($angka / 10)) . ' Puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' Seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = $this->terbilang((int)($angka / 100)) . ' Ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' Seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = $this->terbilang((int)($angka / 1000)) . ' Ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000)) . ' Juta ' . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000000)) . ' Miliar ' . $this->terbilang(fmod($angka, 1000000000));
        } elseif ($angka < 1000000000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000000000)) . ' Triliun ' . $this->terbilang(fmod($angka, 1000000000000));
        }

        return trim($terbilang);
    }

    public function edit($id)
    {
        $this->authorize('edit-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id, [
            'customer',
            'deliveryOrder.details',
            'salesOrder.details.deliveryOrderDetails.deliveryOrder:id,status',
            'tender',
            'details.barang.satuan',
            'details.satuan',
            'details.pajak',
            'costs.coa',
            'currency',
            'pajak',
            'pphPajak',
            'pphCoa',
            'uangMukaCoa'
        ]);

        // Status Draft hanya bisa diedit oleh pembuatnya
        if ($invoice->status === 'Draft' && $invoice->created_by != auth()->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Draf faktur penjualan ini hanya dapat diedit oleh pembuatnya.',
            ], 403);
        }

        if ($invoice->status === 'Paid') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Faktur yang sudah LUNAS tidak dapat diedit.',
            ], 422);
        }

        // Format tanggal string Y-m-d lokal secara akurat tanpa pergeseran zona waktu
        $tglFaktur = $invoice->tanggal_faktur ? Carbon::parse($invoice->tanggal_faktur)->format('Y-m-d') : null;
        $tglJatuhTempo = $invoice->tanggal_jatuh_tempo ? Carbon::parse($invoice->tanggal_jatuh_tempo)->format('Y-m-d') : null;

        $invoiceData = $invoice->toArray();
        $invoiceData['tanggal_faktur'] = $tglFaktur;
        $invoiceData['tanggal_jatuh_tempo'] = $tglJatuhTempo;
        $invoiceData['tanggal_faktur_raw'] = $tglFaktur;
        $invoiceData['tanggal_jatuh_tempo_raw'] = $tglJatuhTempo;

        $soDetails = $invoice->salesOrder?->details ?? collect();
        $doDetails = $invoice->deliveryOrder?->details ?? collect();

        $invoiceData['details'] = $invoice->details->map(function ($det) use ($soDetails, $doDetails) {
            $itemArr = $det->toArray();
            
            $soItem = $soDetails->firstWhere('barang_id', $det->barang_id) 
                ?: $soDetails->firstWhere('id', $det->sales_order_detail_id ?? null);
            
            $doItem = $doDetails->firstWhere('barang_id', $det->barang_id);

            $qtySo = $soItem ? (float)$soItem->qty : 0;
            $qtyDelivered = 0;
            $approvedDoStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai', 'Approved', 'Dikirim', 'Delivered'];

            if ($soItem && $soItem->deliveryOrderDetails) {
                $qtyDelivered = (float) $soItem->deliveryOrderDetails->filter(function($dod) use ($approvedDoStatuses) {
                    return $dod->deliveryOrder && in_array($dod->deliveryOrder->status, $approvedDoStatuses);
                })->sum('qty_kirim');
            } elseif ($doItem && $invoice->deliveryOrder && in_array($invoice->deliveryOrder->status, $approvedDoStatuses)) {
                $qtyDelivered = (float)($doItem->qty_kirim ?? $doItem->qty_order);
            }
            
            $sisaKirim  = $soItem ? max(0, $qtySo - $qtyDelivered) : 0;
            $sisaKurang = $soItem ? max(0, $qtySo - $qtyDelivered) : 0;

            $itemArr['qty_so'] = $qtySo;
            $itemArr['qty_delivered'] = $qtyDelivered;
            $itemArr['sisa_pengiriman'] = $sisaKirim;
            $itemArr['sisa_kurang'] = $sisaKurang;
            $itemArr['satuan_nama'] = $det->satuan?->nama_satuan ?? ($det->barang?->satuan?->nama_satuan ?? 'Pcs');
            $itemArr['kode_barang'] = $det->barang?->kode_barang ?? ($det->barang?->barcode ?? 'PRODUK');

            return $itemArr;
        })->toArray();

        return response()->json([
            'status' => 'success',
            'data'   => $invoiceData,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit-sales-invoice');

        $invoice = $this->findInvoiceForTenant($id);

        // 1. Status yang boleh diedit hanya Draft, Menunggu Persetujuan, dan Rejected
        if (!in_array($invoice->status, ['Draft', 'Menunggu Persetujuan', 'Rejected'])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Faktur dengan status '{$invoice->status}' tidak dapat diedit karena sudah disetujui atau sedang berjalan.",
            ], 422);
        }

        // 2. Status Draft hanya bisa diedit oleh pembuatnya (kecuali Super Admin / Owner)
        $user = auth()->user();
        if ($invoice->status === 'Draft' && $invoice->created_by != $user->id && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Draf faktur penjualan ini hanya dapat diedit oleh pembuatnya.',
            ], 403);
        }

        // 3. Cegah edit jika faktur sudah memiliki alokasi pembayaran tercatat
        if ($invoice->paymentDetails()->whereHas('payment', fn($q) => $q->where('status', '!=', 'Cancelled'))->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Faktur ini sudah memiliki riwayat pembayaran tercatat dan tidak dapat diedit.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'nomor_faktur_pajak'  => 'nullable|string|max:50',
            'tanggal_faktur'      => 'required|date',
            'customer_id'         => 'required|exists:master_customers,id',
            'subtotal'            => 'required|numeric|min:0',
            'grand_total'         => 'required|numeric|min:0',
            'diskon_persen'       => 'nullable|numeric|min:0|max:100',
            'diskon_nominal'      => 'nullable|numeric|min:0',
            'pajak_id'            => 'nullable|exists:master_pajaks,id',
            'pajak_nominal'       => 'nullable|numeric|min:0',
            'biaya_lain'          => 'nullable|numeric|min:0',
            'uang_muka'           => 'nullable|numeric|min:0',
            'uang_muka_coa_id'    => 'nullable|exists:chart_of_accounts,id',
            'kas_bank_id'         => 'nullable|exists:kas_banks,id',
            'items'               => 'required|array|min:1|max:50',
            'items.*.nama_barang' => 'required|string',
            'items.*.qty'         => 'required|numeric|min:0.01',
            'items.*.harga_satuan'=> 'required|numeric|min:0',
            'costs'               => 'nullable|array|max:15',
            'costs.*.jenis_biaya' => 'required_with:costs|string|max:255',
            'costs.*.nominal'     => 'required_with:costs|numeric|min:0',
            'costs.*.coa_id'      => 'nullable|exists:chart_of_accounts,id',
            'file_pendukung'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'tender_id'           => 'nullable|exists:tenders,id',
        ], [
            'file_pendukung.max'   => 'Ukuran file dokumen/foto pendukung maksimal 10 MB.',
            'file_pendukung.mimes' => 'Format file pendukung harus berupa PDF, JPG, JPEG, PNG, atau WEBP.',
            'uang_muka.numeric'    => 'Nominal Uang Muka (DP) harus diisi berupa angka.',
            'uang_muka.min'        => 'Nominal Uang Muka (DP) tidak boleh bernilai negatif.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal, silakan periksa inputan Anda.'
            ], 422);
        }

        $rawUangMuka = trim((string)($request->uang_muka ?? ''));
        if (strlen($rawUangMuka) > 20) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Input nominal Uang Muka (DP) tidak boleh melebihi 20 karakter.'
            ], 422);
        }

        $grandTotalCheck = $this->parseNumber($request->grand_total ?? 0);
        $uangMukaCheck   = $this->parseNumber($request->uang_muka ?? 0);

        if ($uangMukaCheck < 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nominal Uang Muka (DP) harus berupa angka positif.'
            ], 422);
        }

        if ($grandTotalCheck > 0 && $uangMukaCheck > $grandTotalCheck) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nominal Uang Muka (DP) tidak boleh melebihi Total Faktur (Rp ' . number_format($grandTotalCheck, 2, ',', '.') . ').'
            ], 422);
        }

        if ($grandTotalCheck <= 0 && $uangMukaCheck > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Total Faktur masih Rp 0. Silakan isi rincian barang/jasa terlebih dahulu sebelum mengisi Uang Muka (DP).'
            ], 422);
        }

        if ($uangMukaCheck > 0) {
            if (!$request->filled('kas_bank_id')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Rekening Kas & Bank penerima wajib dipilih apabila Faktur Penjualan memiliki nominal Uang Muka (DP).'
                ], 422);
            }
            if (!$request->filled('uang_muka_coa_id')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akun Uang Muka Penjualan (COA) wajib dipilih apabila Faktur Penjualan memiliki nominal Uang Muka (DP).'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $filePath = $invoice->file_pendukung;
            if ($request->hasFile('file_pendukung')) {
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $this->compressAndSaveFile($request->file('file_pendukung'));
            }

            $tanggalJatuhTempo = $request->tanggal_jatuh_tempo;
            if (!$tanggalJatuhTempo && $request->filled('payment_term_id')) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari > 0) {
                    $tanggalJatuhTempo = Carbon::parse($request->tanggal_faktur)->addDays($term->jumlah_hari)->toDateString();
                }
            }
            if (!$tanggalJatuhTempo) {
                $tanggalJatuhTempo = $request->tanggal_faktur;
            }

            $grandTotal  = $this->parseNumber($request->grand_total);
            $uangMuka    = $this->parseNumber($request->uang_muka ?? 0);
            $sisaTagihan = max(0, $grandTotal - $uangMuka);

            $actionType = $request->input('action_type', 'submit');
            if ($actionType === 'draft') {
                $status = 'Draft';
            } elseif ($actionType === 'post') {
                if ($sisaTagihan <= 0.01) {
                    $status = 'Paid';
                } elseif ($uangMuka > 0) {
                    $status = 'Partial';
                } else {
                    $status = 'Unpaid';
                }
            } elseif ($actionType === 'submit') {
                $status = 'Menunggu Persetujuan';
            } else {
                $status = $invoice->status;
            }
            $totalBiayaLain = 0;
            $costsInput = $request->input('costs', []);
            if (is_array($costsInput) && count($costsInput) > 0) {
                foreach ($costsInput as $cInput) {
                    $nom = $this->parseNumber($cInput['nominal'] ?? 0);
                    if ($nom > 0) {
                        $totalBiayaLain += $nom;
                    }
                }
            } else {
                $totalBiayaLain = $this->parseNumber($request->biaya_lain);
            }

            $tenderId = $request->tender_id;
            if (empty($tenderId)) {
                $targetDoId = $request->delivery_order_id ?: $invoice->delivery_order_id;
                $targetSoId = $request->sales_order_id ?: $invoice->sales_order_id;
                if ($targetDoId) {
                    $do = DeliveryOrder::with('salesOrder')->find($targetDoId);
                    $tenderId = $do?->salesOrder?->tender_id;
                }
                if (empty($tenderId) && $targetSoId) {
                    $so = SalesOrder::find($targetSoId);
                    $tenderId = $so?->tender_id ?: ($so?->quotation?->tender_id ?? null);
                }
            }

            $invoice->update([
                'nomor_faktur_pajak' => $request->nomor_faktur_pajak,
                'tanggal_faktur'     => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'=> $tanggalJatuhTempo,
                'sales_order_id'     => $request->sales_order_id ?: $invoice->sales_order_id,
                'delivery_order_id'  => $request->delivery_order_id ?: $invoice->delivery_order_id,
                'tender_id'          => $tenderId,
                'customer_id'        => $request->customer_id,
                'currency_id'        => $request->currency_id ?: 1,
                'kurs'               => $this->parseNumber($request->kurs ?: 1),
                'subtotal'           => $this->parseNumber($request->subtotal),
                'diskon_persen'      => $this->parseNumber($request->diskon_persen),
                'diskon_nominal'     => $this->parseNumber($request->diskon_nominal),
                'pajak_id'           => $request->pajak_id,
                'pajak_nominal'      => $this->parseNumber($request->pajak_nominal),
                'pph_pajak_id'       => $request->pph_pajak_id,
                'tarif_pph'          => $this->parseNumber($request->tarif_pph),
                'pph_nominal'        => $this->parseNumber($request->pph_nominal),
                'pph_coa_id'         => $request->pph_coa_id,
                'biaya_lain'         => $totalBiayaLain,
                'biaya_lain_coa_id'  => $request->biaya_lain_coa_id,
                'grand_total'        => $grandTotal,
                'uang_muka'          => $uangMuka,
                'uang_muka_coa_id'   => $request->uang_muka_coa_id,
                'kas_bank_id'        => $request->kas_bank_id,
                'sisa_tagihan'       => $sisaTagihan,
                'status'             => $status,
                'payment_term_id'    => $request->payment_term_id,
                'catatan'            => $request->catatan,
                'file_pendukung'     => $filePath,
                'updated_by'         => auth()->id(),
            ]);

            // Re-create costs
            $invoice->costs()->delete();
            if (is_array($costsInput) && count($costsInput) > 0) {
                foreach ($costsInput as $cInput) {
                    $nom = $this->parseNumber($cInput['nominal'] ?? 0);
                    if ($nom > 0 || !empty($cInput['jenis_biaya'])) {
                        \App\Models\SalesInvoiceCost::create([
                            'sales_invoice_id' => $invoice->id,
                            'jenis_biaya'      => $cInput['jenis_biaya'] ?? 'Biaya Pengiriman',
                            'nominal'          => $nom,
                            'coa_id'           => !empty($cInput['coa_id']) ? $cInput['coa_id'] : null,
                            'keterangan'       => $cInput['keterangan'] ?? null,
                        ]);
                    }
                }
            }

            // Re-create items
            $invoice->details()->delete();
            foreach ($request->items as $item) {
                $qty = $this->parseNumber($item['qty'] ?? 0);
                $harga = $this->parseNumber($item['harga_satuan'] ?? 0);
                $diskonPersen = $this->parseNumber($item['diskon_persen'] ?? 0);
                $subBruto = $qty * $harga;

                $diskonNom = isset($item['diskon_nominal']) && $item['diskon_nominal'] !== ''
                    ? $this->parseNumber($item['diskon_nominal'])
                    : ($subBruto * ($diskonPersen / 100));

                $dppItem = $subBruto - $diskonNom;

                $ppnPajakId = $item['ppn_pajak_id'] ?? null;
                $tarifPpn = 0;
                if (!empty($ppnPajakId)) {
                    if (isset($item['tarif_ppn']) && $item['tarif_ppn'] !== '' && (float)$item['tarif_ppn'] > 0) {
                        $tarifPpn = $this->parseNumber($item['tarif_ppn']);
                    } else {
                        $pajakObj = MasterPajak::find($ppnPajakId);
                        if ($pajakObj) $tarifPpn = (float)$pajakObj->tarif;
                    }
                }

                $nilaiPpn = isset($item['nilai_ppn']) && $item['nilai_ppn'] !== '' && (float)$item['nilai_ppn'] > 0
                    ? $this->parseNumber($item['nilai_ppn'])
                    : ($dppItem * ($tarifPpn / 100));

                $totalHarga = isset($item['total_harga']) && $item['total_harga'] !== '' && (float)$item['total_harga'] > 0
                    ? $this->parseNumber($item['total_harga'])
                    : ($dppItem + $nilaiPpn);

                SalesInvoiceDetail::create([
                    'sales_invoice_id' => $invoice->id,
                    'barang_id'        => $item['barang_id'] ?? null,
                    'nama_barang'      => $item['nama_barang'],
                    'satuan_id'        => $item['satuan_id'] ?? null,
                    'qty'              => $qty,
                    'harga_satuan'     => $harga,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonNom,
                    'ppn_pajak_id'     => $ppnPajakId,
                    'tarif_ppn'        => $tarifPpn,
                    'nilai_ppn'        => $nilaiPpn,
                    'total_harga'      => $totalHarga,
                    'catatan'          => $item['catatan'] ?? null,
                ]);
            }

            // Post Automatic Journal & send WhatsApp notification based on workflow status
            if ($status === 'Unpaid') {
                $this->createAutomaticJournal($invoice);
                WhatsAppGatewayController::sendSalesInvoiceApprovedNotification($invoice, auth()->user());
            } elseif ($status === 'Menunggu Persetujuan') {
                WhatsAppGatewayController::sendSalesInvoiceSubmissionNotification($invoice);
            }

            $this->clearKpiCache();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Penjualan {$invoice->nomor_faktur} berhasil diperbarui!",
                'data'    => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update SalesInvoice: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui Faktur Penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $invoice = $this->findInvoiceForTenant($id);

        $canDelete = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('hapus-sales-invoice');
        $isCreator = $invoice->created_by == $user->id;

        // Status yang sudah disetujui atau sedang diajukan tidak boleh dihapus
        if (!in_array($invoice->status, ['Draft', 'Rejected'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Faktur yang sudah disetujui atau sedang menunggu persetujuan tidak dapat dihapus!',
            ], 422);
        }

        // Validasi hak akses: jika Draft, pembuat draf atau yang punya hak hapus boleh menghapus
        if ($invoice->status === 'Draft') {
            if (!$canDelete && !$isCreator) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hanya pembuat draf atau pengguna berwenang yang dapat menghapus draf ini.',
                ], 403);
            }
        } else {
            if (!$canDelete) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk membatalkan/menghapus faktur penjualan ini.',
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Soft delete or mark Cancelled
            $invoice->status = 'Cancelled';
            $invoice->deleted_by = $user->id;
            $invoice->save();
            $invoice->delete();

            // Delete associated Journal (forceDelete to prevent UNIQUE constraint collision)
            $oldJurnals = Jurnal::withTrashed()
                ->where('referensi_tipe', 'Sales Invoice')
                ->where('referensi_id', $invoice->id)
                ->get();
            foreach ($oldJurnals as $oldJurnal) {
                $oldJurnal->details()->delete();
                $oldJurnal->forceDelete();
            }

            // Rollback KasMutasi DP & Saldo KasBank jika ada
            $this->rollbackInvoiceDpKasMutation($invoice);

            $this->clearKpiCache();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Faktur Penjualan berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error destroy SalesInvoice: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus Faktur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create automatic Accounting Journal entry for Sales Invoice
     */
    private function createAutomaticJournal(SalesInvoice $invoice)
    {
        // Penentuan Entitas Jurnal (Strict Isolation: Cabang vs PT Pusat):
        // Jika di Cabang: branch_id terisi, company_id WAJIB NULL
        // Jika di PT Pusat: branch_id WAJIB NULL, company_id terisi
        $isCabang = !empty($invoice->branch_id);
        $jurnalBranchId  = $isCabang ? (int) $invoice->branch_id : null;
        $jurnalCompanyId = $isCabang ? null : (int) ($invoice->company_id ?: (session('active_company_id') ?: 1));

        // Untuk mapping akun COA:
        $companyId = $invoice->company_id ?: (session('active_company_id') ?: 1);
        if ($isCabang && $jurnalBranchId) {
            $branch = \App\Models\Branch::find($jurnalBranchId);
            if ($branch && !empty($branch->company_id)) {
                $companyId = $branch->company_id;
            }
        }

        // Delete existing journal if updating an already-posted invoice to prevent double journal entries
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Sales Invoice')
            ->where('referensi_id', $invoice->id)
            ->get();
        foreach ($existingJurnals as $ej) {
            $ej->details()->delete();
            $ej->forceDelete();
        }
        $invoice->unsetRelation('jurnal');

        // Fetch Accounts from Account Mapping or fallback COA
        $arCoaId = AccountMappingHelper::getCoaId('default', 'ar_account', 'default_ar', $companyId);
        if (!$arCoaId) {
            $arCoa = ChartOfAccount::where(function($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                })
                ->where('is_header', false)
                ->where(function($q) {
                    $q->where('name', 'like', '%piutang usaha%')
                      ->orWhere('name', 'like', '%piutang dagang%');
                })
                ->first();
            $arCoaId = $arCoa?->id;
        }

        $salesCoaId = AccountMappingHelper::getCoaId('default', 'sales_account', 'default_sales', $companyId);
        if (!$salesCoaId) {
            $salesCoa = ChartOfAccount::where(function($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                })
                ->where('is_header', false)
                ->where('type', 'rev')
                ->where('name', 'like', '%penjualan%')
                ->first();
            $salesCoaId = $salesCoa?->id;
        }

        $ppnKeluaranCoaId = AccountMappingHelper::getCoaId('tax', 'tax_ppn_keluaran', 'default_ppn_keluaran', $companyId);
        if (!$ppnKeluaranCoaId) {
            $ppnCoa = ChartOfAccount::where(function($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                })
                ->where('name', 'like', '%ppn keluaran%')
                ->first();
            $ppnKeluaranCoaId = $ppnCoa?->id;
        }

        if (!$arCoaId || !$salesCoaId) {
            // Cannot create journal if core accounts are missing
            Log::warning("Accounting Mapping missing for Sales Invoice {$invoice->nomor_faktur}. Skipped journal creation.");
            return;
        }

        $kurs = (float) ($invoice->kurs ?: 1);
        $customerName = trim($invoice->customer?->nama_perusahaan ?? '') ?: ($invoice->customer?->nama_customer ?? 'Customer');
        $grandTotalIdr = (float) $invoice->grand_total * $kurs;
        $subtotalIdr = (float) ($invoice->subtotal - $invoice->diskon_nominal) * $kurs;
        $pajakNominalIdr = (float) $invoice->pajak_nominal * $kurs;
        $pphNominalIdr = (float) ($invoice->pph_nominal ?? 0) * $kurs;
        $biayaLainIdr = (float) $invoice->biaya_lain * $kurs;

        $totalJurnalIdr = $grandTotalIdr + $pphNominalIdr;

        // Generate Journal Number (Safe with withTrashed & collision check)
        $now = Carbon::parse($invoice->tanggal_faktur);
        $jurnalPrefix = "JRN-SLS/" . $now->format('Y/m') . "/";
        $lastJurnal = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', $jurnalPrefix . '%')
            ->orderByDesc('id')
            ->first();
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

        $invoice->loadMissing(['tender', 'salesOrder.tender', 'deliveryOrder.salesOrder.tender']);
        $tenderObj = $invoice->tender 
            ?? $invoice->salesOrder?->tender 
            ?? $invoice->deliveryOrder?->salesOrder?->tender;

        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));

            if (empty($invoice->tender_id)) {
                $invoice->update(['tender_id' => $tenderObj->id]);
            }
        }

        $tenderSuffix = $nomorTender ? " [Tender: {$nomorTender}]" : "";

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $invoice->tanggal_faktur,
            'referensi_tipe' => 'Sales Invoice',
            'referensi_id'   => $invoice->id,
            'no_tender'      => $nomorTender,
            'branch_id'      => $jurnalBranchId,
            'company_id'     => $jurnalCompanyId,
            'total_debit'    => $totalJurnalIdr,
            'total_kredit'   => $totalJurnalIdr,
            'keterangan'     => "Faktur Penjualan {$invoice->nomor_faktur} - {$customerName}{$tenderSuffix}",
            'created_by'     => auth()->id(),
        ]);

        $uangMukaIdr = (float) ($invoice->uang_muka ?? 0) * $kurs;
        $sisaTagihanIdr = max(0, $grandTotalIdr - $uangMukaIdr);

        // 1. DEBIT: Piutang Usaha (AR) -> Sisa Tagihan Bersih Customer
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $arCoaId,
            'debit'      => $sisaTagihanIdr,
            'kredit'     => 0,
            'keterangan' => "Piutang Usaha atas Faktur {$invoice->nomor_faktur}",
        ]);

        // 1a. DEBIT: Uang Muka Penjualan (DP Customer) -> Reklasifikasi pemotongan DP dari SO
        if ($uangMukaIdr > 0) {
            $dpCoaId = $invoice->uang_muka_coa_id;
            if (!$dpCoaId) {
                $dpCoaId = AccountMappingHelper::getCoaId('default', 'customer_deposit_account', 'down_payment_customer', $companyId);
            }
            if (!$dpCoaId) {
                $dpCoa = ChartOfAccount::where(function($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->where('is_header', false)
                    ->where('type', 'liab')
                    ->where(function ($q) {
                        $q->where('name', 'like', '%uang muka penjualan%')
                          ->orWhere('name', 'like', '%dp customer%')
                          ->orWhere('name', 'like', '%deposit%');
                    })
                    ->first();
                $dpCoaId = $dpCoa?->id ?: 2106;
            }

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $dpCoaId,
                'debit'      => $uangMukaIdr,
                'kredit'     => 0,
                'keterangan' => "Pemotongan Uang Muka (DP) atas Faktur {$invoice->nomor_faktur}",
            ]);
        }

        // 1b. DEBIT: Uang Muka PPh / PPh Dibayar Dimuka -> Jika ada potongan PPh
        if ($pphNominalIdr > 0) {
            $pphCoaId = $invoice->pph_coa_id;
            if (!$pphCoaId) {
                $pphCoaId = AccountMappingHelper::getCoaId('default', 'prepaid_tax_account', null, $companyId);
            }
            if (!$pphCoaId) {
                $pphCoa = ChartOfAccount::where(function($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->where('is_header', false)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%pph%')
                          ->where(function ($q2) {
                              $q2->where('name', 'like', '%dibayar dimuka%')
                                 ->orWhere('name', 'like', '%uang muka%')
                                 ->orWhere('name', 'like', '%23%')
                                 ->orWhere('name', 'like', '%22%');
                          });
                    })
                    ->first();
                $pphCoaId = $pphCoa?->id;
            }

            // Fallback ke akun aset lancar uang muka pajak lainnya (jangan fallback ke Piutang Usaha!)
            if (!$pphCoaId) {
                $pphAssetFallback = ChartOfAccount::where(function($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->where('is_header', false)
                    ->where('type', 'asset')
                    ->where(function ($q) {
                        $q->where('name', 'like', '%pajak dibayar dimuka%')
                          ->orWhere('name', 'like', '%uang muka pajak%');
                    })
                    ->first();
                $pphCoaId = $pphAssetFallback?->id;
            }

            if ($pphCoaId) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $pphCoaId,
                    'debit'      => $pphNominalIdr,
                    'kredit'     => 0,
                    'keterangan' => "Potongan PPh (Uang Muka PPh) atas Faktur {$invoice->nomor_faktur}",
                ]);
            }
        }

        // 2. KREDIT: Pendapatan Penjualan -> Subtotal (Net Diskon)
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $salesCoaId,
            'debit'      => 0,
            'kredit'     => $subtotalIdr,
            'keterangan' => "Penjualan Barang atas Faktur {$invoice->nomor_faktur}",
        ]);

        // 3. KREDIT: PPN Keluaran -> Jika ada nominal PPN
        if ($pajakNominalIdr > 0) {
            $effectivePpnCoaId = $ppnKeluaranCoaId;
            if (!$effectivePpnCoaId) {
                // Fallback cari COA Utang PPN/Pajak atau fallback ke Sales COA agar jurnal tidak pincang
                $ppnCoaFallback = ChartOfAccount::where(function($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->where('is_header', false)
                    ->where(function($q) {
                        $q->where('name', 'like', '%ppn%')
                          ->orWhere('name', 'like', '%utang pajak%');
                    })
                    ->first();
                $effectivePpnCoaId = $ppnCoaFallback?->id ?: $salesCoaId;
            }

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $effectivePpnCoaId,
                'debit'      => 0,
                'kredit'     => $pajakNominalIdr,
                'keterangan' => "PPN Keluaran atas Faktur {$invoice->nomor_faktur}",
            ]);
        }

        // 4. KREDIT: Pendapatan Biaya Tambahan / Lainnya
        $invoice->loadMissing('costs');
        if ($invoice->costs && $invoice->costs->count() > 0) {
            foreach ($invoice->costs as $costItem) {
                $cNominalIdr = (float) $costItem->nominal * $kurs;
                if ($cNominalIdr <= 0) continue;

                $cCoaId = $costItem->coa_id;
                if (!$cCoaId) {
                    $cCoaId = AccountMappingHelper::getCoaId('default', 'shipping_income_account', null, $companyId);
                }
                if (!$cCoaId) {
                    $shippingCoa = ChartOfAccount::where(function($q) use ($companyId) {
                            $q->where('company_id', $companyId)->orWhereNull('company_id');
                        })
                        ->where('is_header', false)
                        ->where(function ($q) use ($costItem) {
                            $q->where('name', 'like', '%' . strtolower($costItem->jenis_biaya) . '%')
                              ->orWhere('name', 'like', '%ongkos kirim%')
                              ->orWhere('name', 'like', '%pendapatan pengiriman%')
                              ->orWhere('name', 'like', '%biaya pengiriman%')
                              ->orWhere('name', 'like', '%freight%');
                        })
                        ->first();
                    $cCoaId = $shippingCoa?->id ?: $salesCoaId;
                }

                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $cCoaId,
                    'debit'      => 0,
                    'kredit'     => $cNominalIdr,
                    'keterangan' => "{$costItem->jenis_biaya} atas Faktur {$invoice->nomor_faktur}",
                ]);
            }
        } elseif ($biayaLainIdr > 0) {
            $shippingCoaId = $invoice->biaya_lain_coa_id;
            if (!$shippingCoaId) {
                $shippingCoaId = AccountMappingHelper::getCoaId('default', 'shipping_income_account', null, $companyId);
            }
            if (!$shippingCoaId) {
                $shippingCoa = ChartOfAccount::where(function($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->where('is_header', false)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%ongkos kirim%')
                          ->orWhere('name', 'like', '%pendapatan pengiriman%')
                          ->orWhere('name', 'like', '%biaya pengiriman%')
                          ->orWhere('name', 'like', '%freight%');
                    })
                    ->first();
                $shippingCoaId = $shippingCoa?->id ?: $salesCoaId;
            }

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $shippingCoaId,
                'debit'      => 0,
                'kredit'     => $biayaLainIdr,
                'keterangan' => "Pendapatan Biaya Pengiriman atas Faktur {$invoice->nomor_faktur}",
            ]);
        }

        // Verifikasi dan sinkronisasi total aktual Debit & Kredit di header jurnal
        $actualDebit  = (float) JurnalDetail::where('jurnal_id', $jurnal->id)->sum('debit');
        $actualKredit = (float) JurnalDetail::where('jurnal_id', $jurnal->id)->sum('kredit');

        $jurnal->update([
            'total_debit'  => $actualDebit,
            'total_kredit' => $actualKredit,
        ]);

        if (abs($actualDebit - $actualKredit) > 0.01) {
            Log::error("JURNAL TIDAK SEIMBANG pada Faktur {$invoice->nomor_faktur}! Total Debit: {$actualDebit}, Total Kredit: {$actualKredit}");
        }

        // Catat Mutasi Kas Masuk & Tambah Saldo KasBank untuk Uang Muka (DP)
        $this->syncInvoiceDpKasMutation($invoice);
    }

    /**
     * Batalkan / Rollback Mutasi Kas & Saldo KasBank dari Uang Muka (DP) Faktur Penjualan
     */
    private function rollbackInvoiceDpKasMutation(SalesInvoice $invoice)
    {
        $dpMutasis = KasMutasi::withTrashed()
            ->where('nomor_referensi', $invoice->nomor_faktur)
            ->where('kategori', 'Penerimaan Uang Muka')
            ->get();

        foreach ($dpMutasis as $dpMutasi) {
            $kasBank = KasBank::find($dpMutasi->kas_bank_id);
            if ($kasBank) {
                $kasBank->saldo_berjalan = max(0, (float) $kasBank->saldo_berjalan - (float) $dpMutasi->jumlah);
                $kasBank->save();
            }
            $dpMutasi->forceDelete();
        }
    }

    /**
     * Catat Mutasi Kas Masuk & Tambah Saldo KasBank untuk Uang Muka (DP) Faktur Penjualan
     */
    private function syncInvoiceDpKasMutation(SalesInvoice $invoice)
    {
        $dpNominal = (float) ($invoice->uang_muka ?? 0);
        if ($dpNominal <= 0) {
            return;
        }

        // DP hanya diposting jika status faktur sudah disetujui/aktif (bukan Draft, Cancelled, atau Rejected)
        if (in_array($invoice->status, ['Draft', 'Cancelled', 'Rejected'])) {
            return;
        }

        $existingDpMutasi = KasMutasi::where('nomor_referensi', $invoice->nomor_faktur)
            ->where('kategori', 'Penerimaan Uang Muka')
            ->first();

        if ($existingDpMutasi) {
            return; // Sudah pernah diposting ke Kas & Bank
        }

        // Cari akun Kas & Bank yang dituju
        $kasBank = null;
        if ($invoice->kas_bank_id) {
            $kasBank = KasBank::find($invoice->kas_bank_id);
        }
        if (!$kasBank && $invoice->uang_muka_coa_id) {
            $kasBank = KasBank::where('coa_id', $invoice->uang_muka_coa_id)->first();
        }

        $companyId = $invoice->company_id ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1));
        $branchId  = $invoice->branch_id ?: session('active_branch_id');

        if (!$kasBank) {
            $kasBankQuery = KasBank::query();
            if (!empty($branchId)) {
                $kasBankQuery->where('branch_id', $branchId);
            } else {
                $kasBankQuery->whereNull('branch_id')->where('company_id', $companyId);
            }
            $kasBank = $kasBankQuery->where('is_active', true)->first();
        }

        // Fallback jika belum ada Kas/Bank spesifik cabang, cari Kas/Bank aktif pertama di entitas
        if (!$kasBank) {
            $kasBank = KasBank::where('is_active', true)->first();
        }

        if (!$kasBank) {
            Log::warning("Tidak dapat mencatat mutasi DP untuk Faktur {$invoice->nomor_faktur}: Akun Kas/Bank tidak ditemukan.");
            return;
        }

        $mPrefix = "KM-" . date('ym') . "-";
        $latestM = KasMutasi::withTrashed()->where('nomor_transaksi', 'like', $mPrefix . '%')->orderByDesc('id')->first();
        $mNum = 1;
        if ($latestM && preg_match('/(\d+)$/', $latestM->nomor_transaksi, $matches)) {
            $mNum = ((int) $matches[1]) + 1;
        }
        $nomorTransaksi = $mPrefix . str_pad($mNum, 4, '0', STR_PAD_LEFT);

        $saldoSebelum = (float) ($kasBank->saldo_berjalan ?? 0);
        $saldoSesudah = $saldoSebelum + $dpNominal;

        $dpCoaId = $invoice->uang_muka_coa_id;
        if (!$dpCoaId) {
            $dpCoaId = AccountMappingHelper::getCoaId('default', 'customer_deposit_account', 'down_payment_customer', $companyId);
        }

        $customerName = trim($invoice->customer?->nama_perusahaan ?? '') ?: ($invoice->customer?->nama_customer ?? 'Customer');

        KasMutasi::create([
            'nomor_transaksi' => $nomorTransaksi,
            'kas_bank_id'     => $kasBank->id,
            'tipe'            => 'Masuk',
            'kategori'        => 'Penerimaan Uang Muka',
            'lawan_coa_id'    => $dpCoaId,
            'jumlah'          => $dpNominal,
            'saldo_sebelum'   => $saldoSebelum,
            'saldo_sesudah'   => $saldoSesudah,
            'tanggal'         => $invoice->tanggal_faktur,
            'nomor_referensi' => $invoice->nomor_faktur,
            'kontak_tipe'     => 'Customer',
            'kontak_id'       => $invoice->customer_id,
            'nama_kontak'     => $customerName,
            'keterangan'      => "Penerimaan Uang Muka (DP) atas Faktur {$invoice->nomor_faktur}",
            'company_id'      => $companyId,
            'branch_id'       => $branchId,
            'created_by'      => auth()->id(),
        ]);

        $kasBank->saldo_berjalan = $saldoSesudah;
        $kasBank->save();
    }

    private function parseNumber($val): float
    {
        if (is_null($val) || $val === '') return 0.0;
        if (is_int($val) || is_float($val)) return (float) $val;

        $clean = trim((string) $val);
        $clean = preg_replace('/[^\d.,-]/', '', $clean);
        if ($clean === '') return 0.0;

        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $lastDot = strrpos($clean, '.');
            $lastComma = strrpos($clean, ',');
            if ($lastComma > $lastDot) {
                // Format Indonesia: 1.500.000,50
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                // Format Internasional: 1,500,000.50
                $clean = str_replace(',', '', $clean);
            }
        } elseif (strpos($clean, '.') !== false) {
            $parts = explode('.', $clean);
            if (count($parts) > 2) {
                // Titik ribuan ganda, misal: 1.500.000
                $clean = str_replace('.', '', $clean);
            }
            // Jika hanya 1 titik (misal 1.500 atau 170.50 atau 1250.75), ini adalah desimal standar, jangan hapus titik!
        } elseif (strpos($clean, ',') !== false) {
            $parts = explode(',', $clean);
            if (count($parts) > 2) {
                // Koma ribuan ganda, misal: 1,500,000
                $clean = str_replace(',', '', $clean);
            } else {
                // Koma desimal tunggal, misal: 1500,50 atau 1,5
                $clean = str_replace(',', '.', $clean);
            }
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    private function compressAndSaveFile($file, $destinationDir = 'sales_invoices', $maxDimension = 1600, $quality = 75)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);

        if ($isImage && extension_loaded('gd')) {
            $storagePath = storage_path('app/public/' . $destinationDir);

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $sourceImage = null;
            switch ($extension) {
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
                // Auto-rotate based on EXIF orientation if available (mobile uploads)
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

                // Preserve alpha channel transparency for PNG / WEBP
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $width, $height, $transparent);

                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

                $filename = 'inv_' . time() . '_' . uniqid() . '.webp';
                $fullPath = $storagePath . '/' . $filename;

                if (function_exists('imagewebp')) {
                    imagewebp($resizedImage, $fullPath, $quality);
                } else {
                    $filename = 'inv_' . time() . '_' . uniqid() . '.jpg';
                    $fullPath = $storagePath . '/' . $filename;
                    imagejpeg($resizedImage, $fullPath, $quality);
                }

                imagedestroy($sourceImage);
                imagedestroy($resizedImage);

                return $destinationDir . '/' . $filename;
            }
        }

        // Default storage for PDF files or non-GD fallback
        $filename = 'inv_' . time() . '_' . uniqid() . '.' . $extension;
        return $file->storeAs($destinationDir, $filename, 'public');
    }

    /**
     * Kirim / Kirim Ulang notifikasi WhatsApp untuk Faktur Penjualan tertentu
     */
    public function sendWhatsApp($id)
    {
        $this->authorize('lihat-sales-invoice');

        try {
            $invoice = $this->findInvoiceForTenant($id);

            if ($invoice->status === 'Menunggu Persetujuan') {
                WhatsAppGatewayController::sendSalesInvoiceSubmissionNotification($invoice);
            } elseif ($invoice->status === 'Rejected') {
                WhatsAppGatewayController::sendSalesInvoiceRejectedNotification($invoice, auth()->user());
            } else {
                WhatsAppGatewayController::sendSalesInvoiceApprovedNotification($invoice, auth()->user());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Notifikasi WhatsApp Faktur {$invoice->nomor_faktur} sedang dikirim ke penerima!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim notifikasi WhatsApp: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajukan Faktur Penjualan untuk approval
     */
    public function submit($id)
    {
        $this->authorize('edit-sales-invoice');

        try {
            $invoice = $this->findInvoiceForTenant($id);

            if (!in_array($invoice->status, ['Draft', 'Rejected'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hanya faktur dengan status Draft atau Ditolak yang dapat diajukan.',
                ], 422);
            }

            $invoice->update([
                'status'           => 'Menunggu Persetujuan',
                'alasan_penolakan' => null,
            ]);

            $this->clearKpiCache();

            // Kirim notifikasi WhatsApp ke Approver
            WhatsAppGatewayController::sendSalesInvoiceSubmissionNotification($invoice);

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Penjualan {$invoice->nomor_faktur} berhasil diajukan untuk persetujuan (approval)!",
            ]);
        } catch (\Exception $e) {
            Log::error('Error submit SalesInvoice: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengajukan faktur penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Setujui (Approve) Faktur Penjualan
     */
    public function approve($id)
    {
        $this->authorize('approve-sales-invoice');

        DB::beginTransaction();
        try {
            $invoice = $this->findInvoiceForTenant($id);

            if (!in_array($invoice->status, ['Menunggu Persetujuan', 'Draft'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Faktur ini tidak dalam status menunggu persetujuan.',
                ], 422);
            }

            $invoice->update([
                'status'           => 'Unpaid',
                'approved_by'      => auth()->id(),
                'approved_at'      => now(),
                'alasan_penolakan' => null,
            ]);

            // Post Jurnal Otomatis Akuntansi Piutang Usaha
            $this->createAutomaticJournal($invoice);

            $this->clearKpiCache();
            DB::commit();

            // Kirim notifikasi WhatsApp balik ke Pembuat Faktur
            WhatsAppGatewayController::sendSalesInvoiceApprovedNotification($invoice, auth()->user());

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Penjualan {$invoice->nomor_faktur} berhasil disetujui (Approved) & resmi diterbitkan!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approve SalesInvoice: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyetujui faktur penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tolak (Reject) Faktur Penjualan
     */
    public function reject(Request $request, $id)
    {
        $this->authorize('approve-sales-invoice');

        $request->validate([
            'alasan_penolakan' => 'required|string|max:500',
        ]);

        try {
            $invoice = $this->findInvoiceForTenant($id);

            if ($invoice->status !== 'Menunggu Persetujuan') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hanya faktur yang sedang menunggu persetujuan yang dapat ditolak.',
                ], 422);
            }

            $invoice->update([
                'status'           => 'Rejected',
                'approved_by'      => auth()->id(),
                'approved_at'      => now(),
                'alasan_penolakan' => $request->alasan_penolakan,
            ]);

            $this->clearKpiCache();

            // Kirim notifikasi WhatsApp ke Pembuat Faktur dengan alasan penolakan
            WhatsAppGatewayController::sendSalesInvoiceRejectedNotification($invoice, auth()->user(), $request->alasan_penolakan);

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Penjualan {$invoice->nomor_faktur} telah ditolak.",
            ]);
        } catch (\Exception $e) {
            Log::error('Error reject SalesInvoice: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menolak faktur penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batal Setujui (Unapprove) Faktur Penjualan & Kembalikan ke Draf
     * Akses eksklusif hanya untuk Super Admin
     */
    public function unapprove($id)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Fitur Batal Setujui / Kembalikan ke Draf hanya dapat dilakukan oleh Super Admin.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $invoice = $this->findInvoiceForTenant($id, ['jurnal.details', 'paymentDetails.payment']);

            if ($invoice->status !== 'Unpaid') {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Faktur dengan status '{$invoice->status}' tidak dapat dibatalkan persetujuannya. Hanya faktur berstatus 'Unpaid' yang dapat dikembalikan ke Draf.",
                ], 422);
            }

            // Validasi ketat: jika sudah ada riwayat pembayaran yang aktif/valid, tolak pembatalan
            $hasActivePayment = $invoice->paymentDetails()
                ->whereHas('payment', function ($q) {
                    $q->where('status', '!=', 'Cancelled')->whereNull('deleted_at');
                })
                ->exists();

            if ($hasActivePayment || in_array($invoice->status, ['Partial', 'Paid'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Faktur ini tidak dapat dikembalikan ke Draf karena sudah memiliki riwayat pembayaran yang tercatat. Harap batalkan atau hapus terlebih dahulu data pembayaran terkait di modul Pembayaran Piutang!',
                ], 422);
            }

            // 1. Hapus Jurnal Otomatis Akuntansi terkait faktur ini agar neraca/buku besar tidak ada jurnal yatim piatu
            $oldJurnals = Jurnal::withTrashed()
                ->where('referensi_tipe', 'Sales Invoice')
                ->where('referensi_id', $invoice->id)
                ->get();
            foreach ($oldJurnals as $oldJurnal) {
                $oldJurnal->details()->delete();
                $oldJurnal->forceDelete();
            }
            $invoice->unsetRelation('jurnal');

            // 2. Rollback Mutasi Kas & Saldo KasBank dari DP jika ada
            $this->rollbackInvoiceDpKasMutation($invoice);

            // 3. Kembalikan status ke Draft dan reset data persetujuan
            $invoice->update([
                'status'           => 'Draft',
                'approved_by'      => null,
                'approved_at'      => null,
                'alasan_penolakan' => null,
            ]);

            $this->clearKpiCache();
            DB::commit();

            Log::info("Faktur Penjualan {$invoice->nomor_faktur} (ID: {$invoice->id}) berhasil dikembalikan ke Draf oleh Super Admin ID: {$user->id} ({$user->name}).");

            return response()->json([
                'status'  => 'success',
                'message' => "Persetujuan Faktur Penjualan {$invoice->nomor_faktur} berhasil dibatalkan dan status telah dikembalikan ke Draf. Jurnal akuntansi telah dibatalkan/dihapus sehingga faktur dapat diedit kembali.",
                'data'    => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error unapprove SalesInvoice: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membatalkan persetujuan faktur penjualan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
