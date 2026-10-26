<?php

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\KasBank;
use App\Models\Company;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerReceivableController extends Controller
{
    /**
     * Mendapatkan tenant aktif (Cabang vs PT Pusat)
     */
    private function getTenantContext(): array
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        return [
            'branch_id'  => $activeBranchId,
            'company_id' => $activeCompanyId,
        ];
    }

    /**
     * Terapkan isolasi multi-tenant ketat (Pemisahan PT Pusat vs Cabang & Antar-PT):
     * - Jika aktif sebagai Cabang: HANYA data transaksi milik cabang tersebut (branch_id = active_branch_id)
     * - Jika aktif sebagai PT Pusat: HANYA data transaksi milik PT Pusat sendiri (branch_id IS NULL)
     * - Keduanya terikat ketat pada company_id aktif (tidak bocor antar-PT)
     */
    private function applyTenantScope($query, string $table = '')
    {
        $tenant = $this->getTenantContext();
        $activeBranchId  = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $prefix = $table ? "{$table}." : "";

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> HANYA transaksi cabang ini
            $query->where($prefix . 'branch_id', $activeBranchId);
        } else {
            // Aktif sebagai PT Pusat -> HANYA transaksi tingkat PT Pusat sendiri (branch_id IS NULL)
            $query->whereNull($prefix . 'branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($prefix . 'company_id', $activeCompanyId);
            }
        }

        return $query;
    }

    /**
     * Halaman Utama & Server-Side DataTables Rekap Sisa Tagihan & Piutang Pelanggan
     */
    public function index(Request $request)
    {
        $tenant = $this->getTenantContext();
        $activeBranchId  = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        if ($request->ajax()) {
            // Subquery Aggregasi Faktur Penjualan per Customer
            $invoiceSub = DB::table('sales_invoices')
                ->select(
                    'customer_id',
                    DB::raw("COUNT(CASE WHEN status NOT IN ('Draft', 'Cancelled', 'Rejected') THEN 1 END) as total_faktur_count"),
                    DB::raw("SUM(CASE WHEN status NOT IN ('Draft', 'Cancelled', 'Rejected') THEN grand_total ELSE 0 END) as total_invoiced"),
                    DB::raw("SUM(CASE WHEN status NOT IN ('Draft', 'Cancelled', 'Rejected') THEN uang_muka ELSE 0 END) as total_dp"),
                    DB::raw("SUM(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 THEN sisa_tagihan ELSE 0 END) as sisa_piutang"),
                    DB::raw("COUNT(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 THEN 1 END) as unpaid_count"),
                    DB::raw("MIN(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 THEN tanggal_jatuh_tempo END) as oldest_due_date"),
                    DB::raw("MAX(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 AND tanggal_jatuh_tempo < CURDATE() THEN DATEDIFF(CURDATE(), tanggal_jatuh_tempo) ELSE 0 END) as max_overdue_days"),
                    DB::raw("SUM(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 AND tanggal_jatuh_tempo < CURDATE() THEN sisa_tagihan ELSE 0 END) as overdue_amount")
                )
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['Draft', 'Cancelled', 'Rejected']);

            // Filter pushdown for customer_id
            if ($request->filled('customer_id')) {
                $invoiceSub->where('customer_id', $request->customer_id);
            }

            $this->applyTenantScope($invoiceSub);
            $invoiceSub->groupBy('customer_id');

            // Subquery Pembayaran Masuk yang Posted per Customer
            $paymentSub = DB::table('sales_payments')
                ->select(
                    'customer_id',
                    DB::raw("SUM(total_bayar) as total_payment_received")
                )
                ->whereNull('deleted_at')
                ->where('status', 'Posted');

            // Filter pushdown for customer_id
            if ($request->filled('customer_id')) {
                $paymentSub->where('customer_id', $request->customer_id);
            }

            $this->applyTenantScope($paymentSub);
            $paymentSub->groupBy('customer_id');

            // Base Customer Query joined with aggregated invoices
            $query = DB::table('master_customers')
                ->leftJoinSub($invoiceSub, 'inv', function ($join) {
                    $join->on('master_customers.id', '=', 'inv.customer_id');
                })
                ->leftJoinSub($paymentSub, 'pay', function ($join) {
                    $join->on('master_customers.id', '=', 'pay.customer_id');
                })
                ->select([
                    'master_customers.id',
                    'master_customers.kode_customer',
                    'master_customers.nama_customer',
                    'master_customers.nama_perusahaan',
                    'master_customers.telepon',
                    'master_customers.hp',
                    'master_customers.email',
                    'master_customers.credit_limit',
                    DB::raw("COALESCE(inv.total_faktur_count, 0) as total_faktur_count"),
                    DB::raw("COALESCE(inv.total_invoiced, 0) as total_invoiced"),
                    DB::raw("COALESCE(inv.total_dp, 0) as total_dp"),
                    DB::raw("COALESCE(inv.sisa_piutang, 0) as sisa_piutang"),
                    DB::raw("COALESCE(inv.unpaid_count, 0) as unpaid_count"),
                    'inv.oldest_due_date',
                    DB::raw("COALESCE(inv.max_overdue_days, 0) as max_overdue_days"),
                    DB::raw("COALESCE(inv.overdue_amount, 0) as overdue_amount"),
                    DB::raw("COALESCE(pay.total_payment_received, 0) as total_paid"),
                ])
                ->whereNull('master_customers.deleted_at');

            // Multi-tenant scope on master_customers
            if (!empty($activeCompanyId)) {
                $query->where('master_customers.company_id', $activeCompanyId);
            }

            // Filter Customer ID
            if ($request->filled('customer_id')) {
                $query->where('master_customers.id', $request->customer_id);
            }

            // Filter Status Piutang / Aging
            if ($request->filled('aging_filter')) {
                $filter = $request->aging_filter;
                if ($filter === 'has_piutang') {
                    $query->where('inv.sisa_piutang', '>', 0);
                } elseif ($filter === 'lunas') {
                    $query->where('inv.sisa_piutang', '<=', 0)
                          ->where('inv.total_faktur_count', '>', 0);
                } elseif ($filter === 'current') {
                    $query->where('inv.sisa_piutang', '>', 0)
                          ->where('inv.max_overdue_days', '<=', 0);
                } elseif ($filter === 'overdue_1_30') {
                    $query->where('inv.sisa_piutang', '>', 0)
                          ->whereBetween('inv.max_overdue_days', [1, 30]);
                } elseif ($filter === 'overdue_31_60') {
                    $query->where('inv.sisa_piutang', '>', 0)
                          ->whereBetween('inv.max_overdue_days', [31, 60]);
                } elseif ($filter === 'overdue_60_plus') {
                    $query->where('inv.sisa_piutang', '>', 0)
                          ->where('inv.max_overdue_days', '>', 60);
                } elseif ($filter === 'all_master') {
                    // Tampilkan semua master customer (termasuk yang belum pernah bertransaksi)
                }
            } else {
                // Default: Hanya customer yang memiliki transaksi faktur
                $query->whereNotNull('inv.customer_id');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('customer_info', function ($q, $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('master_customers.nama_customer', 'like', "%{$keyword}%")
                            ->orWhere('master_customers.nama_perusahaan', 'like', "%{$keyword}%")
                            ->orWhere('master_customers.kode_customer', 'like', "%{$keyword}%")
                            ->orWhere('master_customers.hp', 'like', "%{$keyword}%")
                            ->orWhere('master_customers.telepon', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_info', function ($row) {
                    $kode = $row->kode_customer ? '<span class="badge bg-secondary-subtle text-secondary font-monospace border me-1">' . e($row->kode_customer) . '</span>' : '';
                    $pt = $row->nama_perusahaan ? '<div class="small text-body-secondary fw-semibold"><i class="bi bi-building me-1"></i>' . e($row->nama_perusahaan) . '</div>' : '';
                    $phone = $row->hp ?: $row->telepon;
                    $contact = $phone ? '<div class="small text-muted font-monospace"><i class="bi bi-telephone-fill me-1 text-success small"></i>' . e($phone) . '</div>' : '';

                    return '<div class="d-flex flex-column py-1">'
                         . '  <div class="d-flex align-items-center mb-0.5">' . $kode . '<span class="fw-bold text-body-emphasis">' . e($row->nama_customer) . '</span></div>'
                         .    $pt
                         .    $contact
                         . '</div>';
                })
                ->editColumn('total_invoiced', function ($row) {
                    $val = (float) $row->total_invoiced;
                    $fmt = number_format($val, 2, ',', '.');
                    return '<div class="text-end">'
                         . '  <span class="fw-semibold font-monospace text-body-emphasis">Rp ' . $fmt . '</span>'
                         . '  <div class="small text-muted">' . number_format($row->total_faktur_count) . ' Faktur</div>'
                         . '</div>';
                })
                ->editColumn('total_dp', function ($row) {
                    $val = (float) $row->total_dp;
                    $fmt = number_format($val, 2, ',', '.');
                    $class = $val > 0 ? 'text-info fw-semibold' : 'text-muted';
                    return '<div class="text-end font-monospace ' . $class . '">Rp ' . $fmt . '</div>';
                })
                ->editColumn('total_paid', function ($row) {
                    $val = (float) $row->total_paid;
                    $fmt = number_format($val, 2, ',', '.');
                    $class = $val > 0 ? 'text-success fw-semibold' : 'text-muted';
                    return '<div class="text-end font-monospace ' . $class . '">Rp ' . $fmt . '</div>';
                })
                ->editColumn('sisa_piutang', function ($row) {
                    $sisa = (float) $row->sisa_piutang;
                    if ($sisa <= 0) {
                        return '<div class="text-end"><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-all me-1"></i>Lunas</span></div>';
                    }
                    $fmt = number_format($sisa, 2, ',', '.');
                    return '<div class="text-end">'
                         . '  <span class="fw-bold font-monospace text-danger fs-6">Rp ' . $fmt . '</span>'
                         . '  <div class="small text-muted">' . number_format($row->unpaid_count) . ' Belum Lunas</div>'
                         . '</div>';
                })
                ->addColumn('aging_status', function ($row) {
                    $sisa = (float) $row->sisa_piutang;
                    if ($sisa <= 0) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5"><i class="bi bi-shield-check me-1"></i>Aman / Lunas</span>';
                    }

                    $days = (int) $row->max_overdue_days;
                    if ($days <= 0) {
                        $due = $row->oldest_due_date ? Carbon::parse($row->oldest_due_date)->format('d M Y') : '-';
                        return '<div class="d-flex flex-column">'
                             . '  <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5"><i class="bi bi-clock-history me-1"></i>Lancar (Belum Tempo)</span>'
                             . '  <small class="text-muted mt-1 font-monospace">Jth: ' . $due . '</small>'
                             . '</div>';
                    } elseif ($days <= 30) {
                        return '<div class="d-flex flex-column">'
                             . '  <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1.5"><i class="bi bi-exclamation-circle-fill me-1"></i>Tempo ' . $days . ' Hari</span>'
                             . '  <small class="text-warning-emphasis mt-0.5 font-monospace">1 - 30 Hari</small>'
                             . '</div>';
                    } elseif ($days <= 60) {
                        return '<div class="d-flex flex-column">'
                             . '  <span class="badge bg-orange-subtle text-warning-emphasis border border-warning rounded-pill px-2.5 py-1.5" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Tempo ' . $days . ' Hari</span>'
                             . '  <small class="text-warning mt-0.5 font-monospace">31 - 60 Hari</small>'
                             . '</div>';
                    } else {
                        return '<div class="d-flex flex-column">'
                             . '  <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1.5"><i class="bi bi-fire me-1"></i>Kritis (> ' . $days . ' Hari)</span>'
                             . '  <small class="text-danger mt-0.5 font-monospace">Macet > 60 Hari</small>'
                             . '</div>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $canPrint = auth()->user()->hasAnyRole(['Super Admin', 'Owner']) || auth()->user()->can('cetak-piutang');
                    $printBtn = $canPrint
                        ? '  <a href="' . route('modul_marketing.piutang.print_statement', $row->id) . '" target="_blank" class="action-btn action-btn-print" title="Cetak Kartu Piutang / Statement of Account">'
                        . '    <i class="bi bi-printer-fill"></i>'
                        . '  </a>'
                        : '';
                    return '<div class="action-cinematic-group">'
                         . '  <button type="button" class="action-btn action-btn-view btn-detail-customer" data-id="' . $row->id . '" title="Lihat Rincian Faktur & Pembayaran">'
                         . '    <i class="bi bi-eye-fill"></i>'
                         . '  </button>'
                         . $printBtn
                         . '</div>';
                })
                ->rawColumns(['customer_info', 'total_invoiced', 'total_dp', 'total_paid', 'sisa_piutang', 'aging_status', 'action'])
                ->make(true);
        }

        // Hitung Metrik Ringkasan Eksekutif (KPI Cards) - Dioptimasi scan index Unpaid / sisa > 0
        $kpiQuery = DB::table('sales_invoices')
            ->whereNull('deleted_at')
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->where('sisa_tagihan', '>', 0);

        $this->applyTenantScope($kpiQuery);

        $kpi = (clone $kpiQuery)->select(
            DB::raw("COALESCE(SUM(sisa_tagihan), 0) as total_piutang_berjalan"),
            DB::raw("COALESCE(SUM(CASE WHEN tanggal_jatuh_tempo < CURDATE() THEN sisa_tagihan ELSE 0 END), 0) as total_piutang_overdue"),
            DB::raw("COUNT(DISTINCT CASE WHEN tanggal_jatuh_tempo < CURDATE() THEN customer_id ELSE NULL END) as customer_overdue_count"),
            DB::raw("COUNT(DISTINCT customer_id) as customer_unpaid_count")
        )->first();

        // Total Pembayaran Bulan Berjalan (Menggunakan Range Scan Index agar cepat pada jutaan baris)
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();

        $paymentMonthQuery = DB::table('sales_payments')
            ->whereNull('deleted_at')
            ->where('status', 'Posted')
            ->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth]);

        $this->applyTenantScope($paymentMonthQuery);

        $totalBayarBulanIni = $paymentMonthQuery->sum('total_bayar');

        // Daftar Customer untuk Filter Dropdown (Scoped ke Perusahaan Aktif & limit 500)
        $customerList = MasterCustomer::forActiveCompany()
            ->select('id', 'kode_customer', 'nama_customer', 'nama_perusahaan')
            ->whereNull('deleted_at')
            ->orderBy('nama_customer', 'asc')
            ->limit(500)
            ->get();

        return view('modul_marketing.piutang.index', compact('kpi', 'totalBayarBulanIni', 'customerList'));
    }

    /**
     * Detail Rincian Faktur & Pembayaran Pelanggan (AJAX Modal)
     */
    public function getDetail(Request $request, $customerId)
    {
        $tenant = $this->getTenantContext();
        $activeBranchId  = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $customer = MasterCustomer::forActiveCompany()->with(['defaultAddress', 'paymentTerm'])->findOrFail($customerId);

        // Daftar Faktur Terkait (Faktur yang belum lunas atau riwayat faktur)
        $invoiceQuery = SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->whereNotIn('status', ['Draft', 'Cancelled', 'Rejected'])
            ->with(['paymentTerm', 'branch']);

        $this->applyTenantScope($invoiceQuery);

        // Summary agregat menyeluruh (Akurat tanpa terpotong limitasi pagination)
        $summaryTotals = (clone $invoiceQuery)->select(
            DB::raw("COALESCE(SUM(grand_total), 0) as total_invoiced"),
            DB::raw("COALESCE(SUM(uang_muka), 0) as total_dp"),
            DB::raw("COALESCE(SUM(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 THEN sisa_tagihan ELSE 0 END), 0) as sisa_piutang"),
            DB::raw("COUNT(CASE WHEN status IN ('Unpaid', 'Partial') AND sisa_tagihan > 0 THEN 1 END) as unpaid_count")
        )->first();

        // Daftar Faktur Terkait untuk ditampilkan di tab modal (hingga 500 transaksi terbaru)
        $allInvoices = $invoiceQuery->orderBy('tanggal_faktur', 'desc')->limit(500)->get();

        $unpaidInvoices = [];
        $paidInvoices = [];

        foreach ($allInvoices as $inv) {
            $grand = (float) $inv->grand_total;
            $dp = (float) $inv->uang_muka;
            $sisa = (float) $inv->sisa_tagihan;

            $daysOverdue = 0;
            if ($inv->tanggal_jatuh_tempo && Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay()->isPast()) {
                $daysOverdue = (int) abs(Carbon::now()->startOfDay()->diffInDays(Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay(), false));
            }

            // Pelunasan Masuk: Pembayaran di luar Uang Muka (DP)
            $pelunasanMasuk = max(0, $grand - $dp - $sisa);

            $item = [
                'id'                  => $inv->id,
                'nomor_faktur'        => $inv->nomor_faktur,
                'tanggal_faktur'      => Carbon::parse($inv->tanggal_faktur)->format('d/m/Y'),
                'tanggal_jatuh_tempo' => $inv->tanggal_jatuh_tempo ? Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') : '-',
                'grand_total'         => $grand,
                'uang_muka'           => $dp,
                'paid_amount'         => $pelunasanMasuk,
                'sisa_tagihan'        => $sisa,
                'status'              => $inv->status,
                'days_overdue'        => $daysOverdue,
                'is_overdue'          => $inv->status !== 'Paid' && $daysOverdue > 0,
            ];

            if (in_array($inv->status, ['Unpaid', 'Partial']) && $sisa > 0) {
                $unpaidInvoices[] = $item;
            } else {
                $paidInvoices[] = $item;
            }
        }

        // Riwayat Pembayaran Pelanggan
        $paymentQuery = SalesPayment::query()
            ->where('customer_id', $customerId)
            ->where('status', '!=', 'Cancelled')
            ->with(['kasBank', 'paymentMethod', 'details.invoice']);

        $this->applyTenantScope($paymentQuery);

        // Total Pembayaran Masuk yang status Posted (Akurat tanpa terpotong limit)
        $totalPaymentReceived = (float) (clone $paymentQuery)->where('status', 'Posted')->sum('total_bayar');
        $totalPotonganGiven   = (float) (clone $paymentQuery)->where('status', 'Posted')->sum('total_potongan');

        $payments = $paymentQuery->orderBy('tanggal_bayar', 'desc')->limit(100)->get();

        $paymentHistory = [];

        foreach ($payments as $p) {
            $totalBayar = (float) $p->total_bayar;
            $totalPotongan = (float) ($p->total_potongan ?? 0);
            $paymentHistory[] = [
                'id'               => $p->id,
                'nomor_pembayaran' => $p->nomor_pembayaran,
                'tanggal_bayar'    => Carbon::parse($p->tanggal_bayar)->format('d/m/Y'),
                'kas_bank'         => $p->kasBank?->nama_akun ?? '-',
                'metode_bayar'     => $p->paymentMethod?->nama_metode ?? '-',
                'total_bayar'      => $totalBayar,
                'total_potongan'   => $totalPotongan,
                'total_alokasi'    => $totalBayar + $totalPotongan,
                'status'           => $p->status,
                'faktur_list'      => $p->details->map(fn($d) => $d->invoice?->nomor_faktur)->filter()->implode(', '),
            ];
        }

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'              => $customer->id,
                'kode_customer'   => $customer->kode_customer,
                'nama_customer'   => $customer->nama_customer,
                'nama_perusahaan' => $customer->nama_perusahaan,
                'telepon'         => $customer->telepon,
                'hp'              => $customer->hp,
                'email'           => $customer->email,
                'alamat'          => $customer->alamat ?: ($customer->defaultAddress?->alamat_lengkap ?? '-'),
                'credit_limit'    => (float) ($customer->credit_limit ?? 0),
                'payment_term'    => $customer->paymentTerm?->nama_term ?? 'Net 30',
            ],
            'summary'  => [
                'total_invoiced' => (float) ($summaryTotals->total_invoiced ?? 0),
                'total_dp'       => (float) ($summaryTotals->total_dp ?? 0),
                'total_paid'     => $totalPaymentReceived,
                'total_potongan' => $totalPotonganGiven,
                'total_alokasi'  => $totalPaymentReceived + $totalPotonganGiven,
                'sisa_piutang'   => (float) ($summaryTotals->sisa_piutang ?? 0),
                'unpaid_count'   => (int) ($summaryTotals->unpaid_count ?? 0),
            ],
            'unpaid_invoices' => $unpaidInvoices,
            'paid_invoices'   => $paidInvoices,
            'payment_history' => $paymentHistory,
        ]);
    }

    /**
     * Cetak Kartu Piutang / Statement of Account Pelanggan
     */
    public function printStatement(Request $request, $customerId)
    {
        $tenant = $this->getTenantContext();
        $sessionBranchId = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $customer = MasterCustomer::forActiveCompany()->with(['defaultAddress', 'paymentTerm'])->findOrFail($customerId);

        // Ambil Data Entitas Company
        $company = Company::find($activeCompanyId) ?: Company::first();

        // Daftar Cabang yang tersedia di PT / Company ini
        $availableBranches = Branch::where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->orderBy('nama_cabang')
            ->get();

        // Tentukan Scope Cetak Dokumen (Cabang vs PT Pusat vs Konsolidasi)
        $requestedBranchId = $request->query('branch_id');

        if (!empty($sessionBranchId)) {
            // Sesi aktif di Cabang -> Terkunci ketat hanya untuk cabang tersebut (Multi-tenant Security)
            $printScope = 'branch';
            $targetBranchId = (int) $sessionBranchId;
            $branch = $availableBranches->firstWhere('id', $targetBranchId) 
                   ?: Branch::where('company_id', $activeCompanyId)->find($targetBranchId);
        } else {
            // Sesi aktif di PT Pusat -> Bebas memilih cetak khusus Pusat, Cabang tertentu, atau Konsolidasi
            if ($requestedBranchId === 'all') {
                $printScope = 'all'; // Konsolidasi Seluruh Cabang & Pusat
                $targetBranchId = null;
                $branch = null;
            } elseif (!empty($requestedBranchId) && is_numeric($requestedBranchId)) {
                // Keamanan IDOR: Pastikan branch_id yang diminta benar-benar milik company yang aktif
                $branch = $availableBranches->firstWhere('id', (int) $requestedBranchId);
                if ($branch) {
                    $printScope = 'branch';
                    $targetBranchId = $branch->id;
                } else {
                    // Fallback aman ke PT Pusat jika ID cabang tidak valid atau milik perusahaan lain
                    $printScope = 'pusat';
                    $targetBranchId = null;
                    $branch = null;
                }
            } else {
                $printScope = 'pusat'; // Transaksi Tingkat PT Pusat
                $targetBranchId = null;
                $branch = null;
            }
        }

        // Ambil Semua Faktur Aktif Sesuai Scope
        $invoiceQuery = SalesInvoice::query()
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['Draft', 'Cancelled', 'Rejected'])
            ->with(['paymentTerm', 'branch']);

        if ($printScope === 'branch' && !empty($targetBranchId)) {
            $invoiceQuery->where('branch_id', $targetBranchId);
        } elseif ($printScope === 'pusat') {
            $invoiceQuery->whereNull('branch_id')->where('company_id', $activeCompanyId);
        } elseif ($printScope === 'all') {
            $companyBranchIds = $availableBranches->pluck('id')->toArray();
            $invoiceQuery->where(function ($q) use ($activeCompanyId, $companyBranchIds) {
                $q->where(function ($sub) use ($activeCompanyId) {
                    $sub->whereNull('branch_id')->where('company_id', $activeCompanyId);
                });
                if (!empty($companyBranchIds)) {
                    $q->orWhereIn('branch_id', $companyBranchIds);
                }
            });
        }

        $invoices = $invoiceQuery->orderBy('tanggal_faktur', 'asc')->get();

        // Aging Buckets & Total Piutang (Kalkulasi Konsisten dengan Status Pembayaran)
        $aging = [
            'current'         => 0,
            'overdue_1_30'    => 0,
            'overdue_31_60'   => 0,
            'overdue_60_plus' => 0,
            'total_piutang'   => 0,
        ];

        foreach ($invoices as $inv) {
            $sisa = (float) $inv->sisa_tagihan;
            if ($sisa > 0 && $inv->status !== 'Paid') {
                $aging['total_piutang'] += $sisa;
                $days = 0;
                if ($inv->tanggal_jatuh_tempo && Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay()->isPast()) {
                    $days = (int) abs(Carbon::now()->startOfDay()->diffInDays(Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay(), false));
                }

                if ($days <= 0) {
                    $aging['current'] += $sisa;
                } elseif ($days <= 30) {
                    $aging['overdue_1_30'] += $sisa;
                } elseif ($days <= 60) {
                    $aging['overdue_31_60'] += $sisa;
                } else {
                    $aging['overdue_60_plus'] += $sisa;
                }
            }
        }

        // Ambil Rekening Kas/Bank Resmi Sesuai Entitas Penerbit
        $bankQuery = KasBank::query()
            ->where('tipe', 'Bank')
            ->where('is_active', true);

        if ($printScope === 'branch' && !empty($targetBranchId)) {
            $bankQuery->where('branch_id', $targetBranchId);
        } else {
            $bankQuery->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $bankAccounts = $bankQuery->limit(3)->get();
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = KasBank::query()
                ->where('tipe', 'Bank')
                ->where('is_active', true)
                ->where('company_id', $activeCompanyId)
                ->limit(3)
                ->get();
        }

        return view('modul_marketing.piutang.print_statement', compact(
            'customer', 'company', 'branch', 'invoices', 'aging', 'bankAccounts',
            'printScope', 'availableBranches', 'sessionBranchId'
        ));
    }
}
