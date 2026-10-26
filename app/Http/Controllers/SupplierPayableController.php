<?php

namespace App\Http\Controllers;

use App\Models\MasterSupplier;
use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use App\Models\KasBank;
use App\Models\Company;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;
use Shuchkin\SimpleXLSXGen;

class SupplierPayableController extends Controller
{
    /**
     * Mendapatkan tenant context aktif (Cabang vs PT Pusat)
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
     * Terapkan isolasi multi-tenant ketat:
     * - Cabang: branch_id = active_branch_id
     * - Pusat: branch_id IS NULL AND company_id = active_company_id
     */
    private function applyTenantScope($query, string $table = '')
    {
        $tenant = $this->getTenantContext();
        $activeBranchId  = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $prefix = $table ? "{$table}." : "";

        if (!empty($activeBranchId)) {
            $query->where($prefix . 'branch_id', $activeBranchId);
        } else {
            $query->whereNull($prefix . 'branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($prefix . 'company_id', $activeCompanyId);
            } else {
                $query->where($prefix . 'company_id', 0);
            }
        }

        return $query;
    }

    private function canViewPayable(): bool
    {
        $u = auth()->user();
        if (!$u) {
            return false;
        }

        return $u->hasAnyRole(['Super Admin', 'Owner'])
            || $u->can('lihat-hutang');
    }

    private function canPrintPayable(): bool
    {
        $u = auth()->user();
        if (!$u) {
            return false;
        }

        return $u->hasAnyRole(['Super Admin', 'Owner'])
            || $u->can('cetak-hutang');
    }

    /**
     * Halaman Utama & Server-Side DataTables Rekap Sisa Tagihan & Utang Supplier
     */
    public function index(Request $request)
    {
        if (!$this->canViewPayable()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Rekap Utang Supplier.');
        }

        $tenant = $this->getTenantContext();
        $activeBranchId  = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        if ($request->ajax()) {
            $query = $this->buildPayableQuery($request);

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('supplier_info', function ($q, $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('master_suppliers.nama_supplier', 'like', "%{$keyword}%")
                            ->orWhere('master_suppliers.kode_supplier', 'like', "%{$keyword}%")
                            ->orWhere('master_suppliers.nama_kontak', 'like', "%{$keyword}%")
                            ->orWhere('master_suppliers.telepon', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('supplier_info', function ($row) {
                    $cleanName = preg_replace('/^(PT\.?|CV\.?|UD\.?)\s+/i', '', trim($row->nama_supplier));
                    $words = preg_split('/\s+/', $cleanName);
                    if (count($words) >= 2 && !empty($words[0]) && !empty($words[1])) {
                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    } else {
                        $initials = strtoupper(substr($cleanName, 0, 2)) ?: 'SP';
                    }

                    $palettes = [
                        ['bg' => 'rgba(99, 102, 241, 0.12)', 'border' => 'rgba(99, 102, 241, 0.25)', 'text' => '#6366f1'],
                        ['bg' => 'rgba(14, 165, 233, 0.12)', 'border' => 'rgba(14, 165, 233, 0.25)', 'text' => '#0284c7'],
                        ['bg' => 'rgba(236, 72, 153, 0.12)', 'border' => 'rgba(236, 72, 153, 0.25)', 'text' => '#db2777'],
                        ['bg' => 'rgba(16, 185, 129, 0.12)', 'border' => 'rgba(16, 185, 129, 0.25)', 'text' => '#059669'],
                        ['bg' => 'rgba(245, 158, 11, 0.12)', 'border' => 'rgba(245, 158, 11, 0.25)', 'text' => '#d97706'],
                        ['bg' => 'rgba(139, 92, 246, 0.12)', 'border' => 'rgba(139, 92, 246, 0.25)', 'text' => '#7c3aed'],
                    ];
                    $palette = $palettes[abs((int)$row->id) % count($palettes)];

                    $kode = $row->kode_supplier 
                        ? '<span class="badge font-monospace rounded-pill px-2 py-0.5" style="background: rgba(148, 163, 184, 0.14); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.28); font-size:0.68rem;">' . e($row->kode_supplier) . '</span>' 
                        : '';
                    $pic = $row->nama_kontak ? '<div class="small mt-0.5" style="color: #cbd5e1; font-size:0.75rem;"><i class="bi bi-person me-1 text-primary opacity-75"></i>' . e($row->nama_kontak) . '</div>' : '';
                    $phone = $row->telepon ? '<div class="small font-monospace mt-0.5" style="color: #34d399; font-size:0.73rem;"><i class="bi bi-telephone-fill me-1 text-success opacity-75"></i>' . e($row->telepon) . '</div>' : '';

                    return '<div class="d-flex align-items-center py-1" style="gap: 12px;">'
                         . '  <div class="supplier-table-avatar" style="background: ' . $palette['bg'] . '; border-color: ' . $palette['border'] . '; color: ' . $palette['text'] . ';"><span>' . e($initials) . '</span></div>'
                         . '  <div class="d-flex flex-column min-w-0">'
                         . '    <div class="d-flex align-items-center gap-1.5 flex-wrap mb-0.5">'
                         .        $kode
                         . '      <span class="fw-bold" style="color: #f8fafc; letter-spacing:-0.01em;">' . e($row->nama_supplier) . '</span>'
                         . '    </div>'
                         .      $pic
                         .      $phone
                         . '  </div>'
                         . '</div>';
                })
                ->editColumn('total_invoiced', function ($row) {
                    $valIdr = (float) $row->total_invoiced;
                    $fmtIdr = number_format($valIdr, 2, ',', '.');
                    $isValas = (float) $row->foreign_total_invoiced > 0.01;
                    $sym = $row->foreign_currency_symbol ?: '$';

                    if ($isValas && (int)$row->idr_faktur_count === 0) {
                        // Seluruh faktur bernilai valas (misal DOLAR) -> tampilkan valas besar, baru rupiahnya
                        $fmtValas = number_format((float) $row->foreign_total_invoiced, 2, ',', '.');
                        return '<div class="text-end">'
                             . '  <div class="fw-bold font-monospace fs-6" style="color: #f8fafc;">' . e($sym) . ' ' . $fmtValas . '</div>'
                             . '  <div class="small font-monospace" style="color: #94a3b8; font-size:0.74rem;">≈ Rp ' . $fmtIdr . '</div>'
                             . '  <div class="mt-0.5"><span class="badge rounded-pill font-monospace" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); font-size:0.67rem;">' . number_format($row->total_faktur_count) . ' Faktur Valas</span></div>'
                             . '</div>';
                    } elseif ($isValas) {
                        // Campuran valas & rupiah
                        $fmtValas = number_format((float) $row->foreign_total_invoiced, 2, ',', '.');
                        return '<div class="text-end">'
                             . '  <div class="fw-semibold font-monospace" style="color: #f8fafc;">Rp ' . $fmtIdr . '</div>'
                             . '  <div class="small font-monospace" style="color: #38bdf8; font-size:0.73rem;">Valas: ' . e($sym) . ' ' . $fmtValas . '</div>'
                             . '  <div class="small" style="color: #94a3b8; font-size:0.72rem;">' . number_format($row->total_faktur_count) . ' Faktur</div>'
                             . '</div>';
                    } else {
                        // Murni rupiah
                        return '<div class="text-end">'
                             . '  <div class="fw-semibold font-monospace" style="color: #f8fafc;">Rp ' . $fmtIdr . '</div>'
                             . '  <div class="small" style="color: #94a3b8; font-size:0.72rem;">' . number_format($row->total_faktur_count) . ' Faktur</div>'
                             . '</div>';
                    }
                })
                ->editColumn('total_dp', function ($row) {
                    $valIdr = (float) $row->total_dp;
                    $fmtIdr = number_format($valIdr, 2, ',', '.');
                    $isValas = (float) ($row->foreign_total_dp ?? 0) > 0.01;
                    $sym = $row->foreign_currency_symbol ?: '$';

                    if ($isValas && (int)$row->idr_faktur_count === 0) {
                        $fmtValas = number_format((float) $row->foreign_total_dp, 2, ',', '.');
                        return '<div class="text-end font-monospace">'
                             . '  <div class="fw-bold fs-6" style="color: #38bdf8;">' . e($sym) . ' ' . $fmtValas . '</div>'
                             . '  <div class="small" style="color: #94a3b8; font-size:0.73rem;">≈ Rp ' . $fmtIdr . '</div>'
                             . '</div>';
                    }
                    if ($valIdr <= 0.01) {
                        return '<div class="text-end font-monospace" style="color: #64748b; font-size:0.85rem;">-</div>';
                    }
                    return '<div class="text-end font-monospace fw-semibold" style="color: #38bdf8;">Rp ' . $fmtIdr . '</div>';
                })
                ->editColumn('total_paid', function ($row) {
                    $valIdr = (float) $row->total_paid;
                    $fmtIdr = number_format($valIdr, 2, ',', '.');
                    $isValas = (float) ($row->foreign_total_paid ?? 0) > 0.01;
                    $sym = $row->foreign_currency_symbol ?: '$';

                    if ($isValas && (int)$row->idr_faktur_count === 0) {
                        $fmtValas = number_format((float) $row->foreign_total_paid, 2, ',', '.');
                        return '<div class="text-end font-monospace">'
                             . '  <div class="fw-bold fs-6" style="color: #34d399;">' . e($sym) . ' ' . $fmtValas . '</div>'
                             . '  <div class="small" style="color: #94a3b8; font-size:0.73rem;">≈ Rp ' . $fmtIdr . '</div>'
                             . '</div>';
                    }
                    if ($valIdr <= 0.01) {
                        return '<div class="text-end font-monospace" style="color: #64748b; font-size:0.85rem;">-</div>';
                    }
                    return '<div class="text-end font-monospace fw-semibold" style="color: #34d399;">Rp ' . $fmtIdr . '</div>';
                })
                ->editColumn('sisa_utang', function ($row) {
                    $sisaIdr = (float) $row->sisa_utang;
                    if ($sisaIdr <= 0.01) {
                        return '<div class="text-end"><span class="badge rounded-pill px-2.5 py-1 font-monospace" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size:0.75rem;"><i class="bi bi-check-all me-1"></i>Lunas</span></div>';
                    }
                    $fmtIdr = number_format($sisaIdr, 2, ',', '.');
                    $hasForeign = (float) $row->foreign_sisa_valas > 0.01;
                    $sym = $row->foreign_currency_symbol ?: '$';

                    if ($hasForeign) {
                        // TAMPILAN BESAR UTANG DOLAR / VALAS-NYA BARU RUPIAH-NYA
                        $fmtValas = number_format((float) $row->foreign_sisa_valas, 2, ',', '.');
                        return '<div class="text-end">'
                             . '  <div class="fw-bold font-monospace fs-5" style="color: #f87171; letter-spacing: -0.01em;">' . e($sym) . ' ' . $fmtValas . '</div>'
                             . '  <div class="small font-monospace" style="color: #94a3b8; font-size:0.74rem;">≈ Rp ' . $fmtIdr . '</div>'
                             . '  <div class="mt-0.5"><span class="badge rounded-pill font-monospace" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size:0.67rem;">' . number_format($row->unpaid_count) . ' Belum Lunas</span></div>'
                             . '</div>';
                    } else {
                        // Murni rupiah
                        return '<div class="text-end">'
                             . '  <div class="fw-bold font-monospace fs-6" style="color: #f87171;">Rp ' . $fmtIdr . '</div>'
                             . '  <div class="mt-0.5"><span class="badge rounded-pill font-monospace" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size:0.67rem;">' . number_format($row->unpaid_count) . ' Belum Lunas</span></div>'
                             . '</div>';
                    }
                })
                ->addColumn('aging_status', function ($row) {
                    $sisa = (float) $row->sisa_utang;
                    if ($sisa <= 0.01) {
                        return '<span class="badge rounded-pill px-2.5 py-1.5 font-monospace" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); font-size:0.75rem;"><i class="bi bi-shield-check me-1"></i>Aman / Lunas</span>';
                    }

                    $days = (int) $row->max_overdue_days;
                    if ($days <= 0) {
                        $due = $row->oldest_due_date ? Carbon::parse($row->oldest_due_date)->format('d M Y') : '-';
                        return '<div class="d-inline-flex flex-column align-items-center">'
                             . '  <span class="badge rounded-pill px-2.5 py-1.5 font-monospace" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); font-size:0.73rem;"><i class="bi bi-clock-history me-1"></i>Lancar (Tempo)</span>'
                             . '  <small class="mt-1 font-monospace" style="color: #94a3b8; font-size:0.71rem;">Jth: ' . $due . '</small>'
                             . '</div>';
                    } elseif ($days <= 30) {
                        return '<div class="d-inline-flex flex-column align-items-center">'
                             . '  <span class="badge rounded-pill px-2.5 py-1.5 font-monospace" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); font-size:0.73rem;"><i class="bi bi-exclamation-circle-fill me-1"></i>Tempo ' . $days . ' Hari</span>'
                             . '  <small class="mt-0.5 font-monospace" style="color: #94a3b8; font-size:0.71rem;">1 – 30 Hari</small>'
                             . '</div>';
                    } elseif ($days <= 60) {
                        return '<div class="d-inline-flex flex-column align-items-center">'
                             . '  <span class="badge rounded-pill px-2.5 py-1.5 font-monospace" style="background: rgba(249, 115, 22, 0.15); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.35); font-size:0.73rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Tempo ' . $days . ' Hari</span>'
                             . '  <small class="mt-0.5 font-monospace" style="color: #fb923c; font-size:0.71rem;">31 – 60 Hari</small>'
                             . '</div>';
                    } else {
                        return '<div class="d-inline-flex flex-column align-items-center">'
                             . '  <span class="badge rounded-pill px-2.5 py-1.5 font-monospace" style="background: rgba(239, 68, 68, 0.18); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size:0.73rem;"><i class="bi bi-fire me-1"></i>Kritis (> ' . $days . ' Hari)</span>'
                             . '  <small class="mt-0.5 font-monospace" style="color: #f87171; font-size:0.71rem;">Macet > 60 Hari</small>'
                             . '</div>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $canPrint = $this->canPrintPayable();
                    $printBtn = $canPrint
                        ? '  <a href="' . route('modul_purchasing.hutang.print_statement', $row->id) . '" target="_blank" class="action-btn action-btn-print" title="Cetak Kartu Utang / Statement of Account">'
                        . '    <i class="bi bi-printer-fill"></i>'
                        . '  </a>'
                        : '';

                    return '<div class="action-cinematic-group">'
                         . '  <button type="button" class="action-btn action-btn-view btn-detail-supplier" data-id="' . $row->id . '" title="Lihat Rincian Faktur & Pembayaran">'
                         . '    <i class="bi bi-eye-fill"></i>'
                         . '  </button>'
                         . $printBtn
                         . '</div>';
                })
                ->rawColumns(['supplier_info', 'total_invoiced', 'total_dp', 'total_paid', 'sisa_utang', 'aging_status', 'action'])
                ->make(true);
        }

        // ══════════════════════════════════════════════════════════════════
        // Hitung Metrik Ringkasan Eksekutif (KPI Cards) - Di-cache 60s untuk Skala Jutaan Baris
        // ══════════════════════════════════════════════════════════════════
        $tenantScopeKey = ($activeCompanyId ?: 'comp') . '_' . ($activeBranchId ?: 'pusat');
        $kpiCacheKey    = "payable_kpi_stats_{$tenantScopeKey}";

        $kpiStats = Cache::remember($kpiCacheKey, 60, function () {
            $ppdSubKpi = DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
                ->whereNull('pp.deleted_at')
                ->where('pp.status', 'Posted');

            $this->applyTenantScope($ppdSubKpi, 'pp');

            $ppdSubKpi->groupBy('ppd.purchase_invoice_id')
                ->select('ppd.purchase_invoice_id', DB::raw("SUM(ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)) as total_bayar"));

            $kpiBaseQuery = DB::table('purchase_invoices as pi')
                ->leftJoinSub($ppdSubKpi, 'pay_inv', 'pi.id', '=', 'pay_inv.purchase_invoice_id')
                ->whereNull('pi.deleted_at')
                ->whereNotIn('pi.status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval']);

            $this->applyTenantScope($kpiBaseQuery, 'pi');

            return (clone $kpiBaseQuery)->selectRaw("
                COALESCE(SUM(GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1))), 0) as total_utang_berjalan,
                COALESCE(SUM(CASE WHEN pi.tanggal_jatuh_tempo < CURDATE() THEN GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1)) ELSE 0 END), 0) as total_utang_overdue,
                COUNT(DISTINCT CASE WHEN pi.tanggal_jatuh_tempo < CURDATE() AND (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN pi.supplier_id ELSE NULL END) as supplier_overdue_count,
                COUNT(DISTINCT CASE WHEN (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN pi.supplier_id ELSE NULL END) as supplier_unpaid_count
            ")->first();
        });

        // Total Pembayaran Bulan Berjalan
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth   = now()->endOfMonth()->toDateString();
        $monthPaidCacheKey = "payable_month_paid_{$tenantScopeKey}_{$startOfMonth}";

        $totalBayarBulanIni = (float) Cache::remember($monthPaidCacheKey, 60, function () use ($startOfMonth, $endOfMonth) {
            $paymentMonthQuery = DB::table('purchase_payments as pp')
                ->whereNull('pp.deleted_at')
                ->where('pp.status', 'Posted')
                ->whereBetween('pp.tanggal_bayar', [$startOfMonth, $endOfMonth]);

            $this->applyTenantScope($paymentMonthQuery, 'pp');

            return (float) $paymentMonthQuery->sum(DB::raw("total_bayar * COALESCE(NULLIF(kurs, 0), 1)"));
        });

        // Daftar Supplier untuk Filter Dropdown
        $supplierList = MasterSupplier::query()
            ->whereNull('deleted_at')
            ->when(!empty($activeCompanyId), function ($q) use ($activeCompanyId) {
                $q->where(function ($sub) use ($activeCompanyId) {
                    $sub->where('company_id', $activeCompanyId)
                        ->orWhereNull('company_id');
                });
            })
            ->select('id', 'kode_supplier', 'nama_supplier')
            ->orderBy('nama_supplier', 'asc')
            ->limit(500)
            ->get();

        return view('modul_purchasing.hutang.index', compact('kpiStats', 'totalBayarBulanIni', 'supplierList'));
    }

    /**
     * Detail Rincian Faktur & Pembayaran Supplier (AJAX Modal)
     */
    public function getDetail(Request $request, $supplierId)
    {
        if (!$this->canViewPayable()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $tenant = $this->getTenantContext();
        $activeCompanyId = $tenant['company_id'];

        $supplier = MasterSupplier::with(['primaryBank', 'banks'])->findOrFail($supplierId);

        // Validasi Multi-Tenant: Cegah IDOR lintas perusahaan
        if (!empty($activeCompanyId) && !empty($supplier->company_id) && (int)$supplier->company_id !== (int)$activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data supplier tidak berada dalam lingkup entitas perusahaan Anda.'], 403);
        }

        // Ambil semua faktur pembelian yang sah
        $invoiceQuery = PurchaseInvoice::query()
            ->where('supplier_id', $supplierId)
            ->whereNotIn('status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval'])
            ->with(['paymentTerm', 'branch', 'currency', 'paymentDetails.payment']);

        $this->applyTenantScope($invoiceQuery);

        $allInvoices = $invoiceQuery->orderBy('tanggal_faktur', 'desc')->limit(500)->get();

        $unpaidInvoices = [];
        $paidInvoices   = [];

        $totalInvoicedAllIdr = 0;
        $totalDpAllIdr       = 0;
        $totalPaidAllIdr     = 0;
        $totalSisaAllIdr     = 0;
        $unpaidCountAll      = 0;

        $hasForeignCurrency   = false;
        $foreignCurrenciesList = [];
        $foreignSisaTotal     = 0;
        $foreignInvoicedTotal = 0;
        $foreignDpTotal       = 0;
        $foreignPaidTotal     = 0;
        $foreignSymbolMain    = '$';

        foreach ($allInvoices as $inv) {
            $cur       = $inv->currency;
            $curCode   = strtoupper($cur?->kode_currency ?? 'IDR');
            $kurs      = (float) ($inv->kurs ?: 1);
            $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']) && $kurs > 1;
            $curSymbol = $cur?->simbol ?? ($isForeign ? '$' : 'Rp');

            $grand = (float) $inv->grand_total;
            $dp    = (float) ($inv->uang_muka ?? 0);
            $paid  = (float) $inv->paid_amount;
            $sisa  = (float) $inv->remaining_balance;

            // Konversi ke IDR secara akurat
            $grandIdr = round($grand * $kurs, 2);
            $dpIdr    = round($dp * $kurs, 2);
            $paidIdr  = round($paid * $kurs, 2);
            $sisaIdr  = round($sisa * $kurs, 2);

            // Pembayaran setelah Uang Muka (Pelunasan Kas/Bank murni tanpa DP)
            $pelunasanMasuk    = max(0, $paid - $dp);
            $pelunasanMasukIdr = max(0, $paidIdr - $dpIdr);

            $totalInvoicedAllIdr += $grandIdr;
            $totalDpAllIdr       += $dpIdr;
            $totalPaidAllIdr     += $pelunasanMasukIdr;
            $totalSisaAllIdr     += $sisaIdr;

            if ($isForeign) {
                $hasForeignCurrency = true;
                $foreignCurrenciesList[$curCode] = true;
                $foreignSymbolMain = $curSymbol;
                $foreignSisaTotal += $sisa;
                $foreignInvoicedTotal += $grand;
                $foreignDpTotal += $dp;
                $foreignPaidTotal += $pelunasanMasuk;
            }

            $daysOverdue = 0;
            if ($inv->tanggal_jatuh_tempo && Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay()->isPast()) {
                $daysOverdue = (int) abs(Carbon::now()->startOfDay()->diffInDays(Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay(), false));
            }

            $item = [
                'id'                     => $inv->id,
                'nomor_faktur'           => $inv->nomor_faktur,
                'nomor_faktur_supplier'  => $inv->nomor_faktur_supplier ?: '-',
                'tanggal_faktur'         => $inv->tanggal_faktur ? Carbon::parse($inv->tanggal_faktur)->format('d/m/Y') : '-',
                'tanggal_jatuh_tempo'    => $inv->tanggal_jatuh_tempo ? Carbon::parse($inv->tanggal_jatuh_tempo)->format('d/m/Y') : '-',
                'currency_code'          => $curCode,
                'currency_symbol'        => $curSymbol,
                'is_foreign'             => $isForeign,
                'kurs'                   => $kurs,
                // Nilai Asli (Mata Uang Asal / Valas)
                'grand_total'            => $grand,
                'uang_muka'              => $dp,
                'paid_amount'            => $pelunasanMasuk,
                'sisa_utang'             => $sisa,
                // Nilai Konversi Kurs (IDR)
                'grand_total_idr'        => $grandIdr,
                'uang_muka_idr'          => $dpIdr,
                'paid_amount_idr'        => $pelunasanMasukIdr,
                'sisa_utang_idr'         => $sisaIdr,
                'status'                 => $inv->status,
                'days_overdue'           => $daysOverdue,
                'is_overdue'             => ($sisa > 0.01) && ($daysOverdue > 0),
            ];

            if ($sisa > 0.01) {
                $unpaidInvoices[] = $item;
                $unpaidCountAll++;
            } else {
                $paidInvoices[] = $item;
            }
        }

        // Riwayat Pembayaran ke Supplier (Purchase Payment) - Hanya yang berstatus Posted yang sah
        $paymentQuery = PurchasePayment::query()
            ->where('supplier_id', $supplierId)
            ->where('status', 'Posted')
            ->with(['kasBank', 'paymentMethod', 'currency', 'details.invoice.currency']);

        $this->applyTenantScope($paymentQuery);

        $payments = $paymentQuery->orderBy('tanggal_bayar', 'desc')->limit(100)->get();

        $paymentHistory = [];
        $totalPaymentReceived = 0;
        $totalPotonganGiven   = 0;

        foreach ($payments as $p) {
            $pKurs         = (float) ($p->kurs ?: 1);
            $totalBayar    = (float) $p->total_bayar;
            $totalPotongan = (float) ($p->total_potongan ?? 0);

            // Deteksi Mata Uang & Status Valas (Dolar / Valas Asing)
            $curCode = strtoupper(trim($p->currency?->kode_currency ?? ''));
            if (empty($curCode) && $p->details->isNotEmpty()) {
                $firstInvCur = $p->details->first()->invoice?->currency?->kode_currency;
                if (!empty($firstInvCur)) {
                    $curCode = strtoupper(trim($firstInvCur));
                }
            }
            if (empty($curCode)) {
                $curCode = 'IDR';
            }

            $curSymbol = $p->currency?->simbol ?: ($curCode === 'IDR' ? 'Rp' : '$');
            $isForeign = (!in_array($curCode, ['IDR', 'RP']) || $pKurs > 1.0001);

            $totalBayarIdr    = round($totalBayar * $pKurs, 2);
            $totalPotonganIdr = round($totalPotongan * $pKurs, 2);

            if ($p->status === 'Posted') {
                $totalPaymentReceived += $totalBayarIdr;
                $totalPotonganGiven   += $totalPotonganIdr;
            }

            $paymentHistory[] = [
                'id'                 => $p->id,
                'nomor_pembayaran'   => $p->nomor_pembayaran,
                'tanggal_bayar'      => Carbon::parse($p->tanggal_bayar)->format('d/m/Y'),
                'kas_bank'           => $p->kasBank?->nama_akun ?? '-',
                'metode_bayar'       => $p->paymentMethod?->nama_metode ?? '-',
                'currency_code'      => $curCode,
                'currency_symbol'    => $curSymbol,
                'is_foreign'         => $isForeign,
                'kurs'               => $pKurs,
                'total_bayar'        => $totalBayar,
                'total_bayar_idr'    => $totalBayarIdr,
                'total_potongan'     => $totalPotongan,
                'total_potongan_idr' => $totalPotonganIdr,
                'total_alokasi'      => $totalBayar + $totalPotongan,
                'total_alokasi_idr'  => $totalBayarIdr + $totalPotonganIdr,
                'status'             => $p->status,
                'faktur_list'        => $p->details->map(fn($d) => $d->invoice?->nomor_faktur)->filter()->implode(', '),
            ];
        }

        $supBank = $supplier->primaryBank ?? $supplier->banks->first();

        return response()->json([
            'success'  => true,
            'supplier' => [
                'id'              => $supplier->id,
                'kode_supplier'   => $supplier->kode_supplier,
                'nama_supplier'   => $supplier->nama_supplier,
                'nama_kontak'     => $supplier->nama_kontak ?? '-',
                'telepon'         => $supplier->telepon ?? '-',
                'email'           => $supplier->email ?? '-',
                'alamat'          => $supplier->alamat ?: '-',
                'term_pembayaran' => $supplier->term_pembayaran ?? 'Net 30',
                'limit_kredit'    => (float) ($supplier->limit_kredit ?? 0),
                'bank_name'       => $supBank?->nama_bank ?? '-',
                'bank_rekening'   => $supBank?->nomor_rekening ?? '-',
                'bank_atas_nama'  => $supBank?->atas_nama_rekening ?? ($supBank?->atas_nama ?? '-'),
            ],
            'summary'  => [
                'total_invoiced'     => $totalInvoicedAllIdr,
                'total_dp'           => $totalDpAllIdr,
                'total_paid'         => $totalPaidAllIdr,
                'total_potongan'     => $totalPotonganGiven,
                'total_alokasi'      => $totalPaidAllIdr,
                'sisa_utang'         => $totalSisaAllIdr,
                'unpaid_count'       => $unpaidCountAll,
                'has_foreign'        => $hasForeignCurrency,
                'foreign_currencies' => array_keys($foreignCurrenciesList),
                'foreign_sisa_total' => $foreignSisaTotal,
                'foreign_invoiced'   => $foreignInvoicedTotal,
                'foreign_dp'         => $foreignDpTotal,
                'foreign_paid'       => $foreignPaidTotal,
                'foreign_symbol'     => $foreignSymbolMain,
            ],
            'unpaid_invoices' => $unpaidInvoices,
            'paid_invoices'   => $paidInvoices,
            'payment_history' => $paymentHistory,
        ]);
    }

    /**
     * Cetak Kartu Utang / Statement of Account Supplier
     */
    public function printStatement(Request $request, $supplierId)
    {
        if (!$this->canPrintPayable()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak Kartu Utang.');
        }

        $tenant = $this->getTenantContext();
        $sessionBranchId = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $supplier = MasterSupplier::with(['primaryBank', 'banks'])->findOrFail($supplierId);

        // Validasi Multi-Tenant: Cegah IDOR cetak statement supplier lintas perusahaan
        if (!empty($activeCompanyId) && !empty($supplier->company_id) && (int)$supplier->company_id !== (int)$activeCompanyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak Kartu Utang supplier dari perusahaan lain.');
        }

        // Ambil Data Entitas Company
        $company = Company::find($activeCompanyId) ?: Company::first();

        // Daftar Cabang yang tersedia di PT / Company ini
        $availableBranches = Branch::where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->orderBy('nama_cabang')
            ->get();

        // Scope Cetak Dokumen (Otomatis sesuaikan apakah milik Cabang atau PT Pusat)
        $requestedBranchId = $request->query('branch_id');

        if (!empty($sessionBranchId)) {
            $printScope = 'branch';
            $targetBranchId = (int) $sessionBranchId;
            $branch = $availableBranches->firstWhere('id', $targetBranchId) 
                   ?: Branch::where('company_id', $activeCompanyId)->find($targetBranchId);
        } else {
            if (!empty($requestedBranchId) && is_numeric($requestedBranchId)) {
                $branch = $availableBranches->firstWhere('id', (int) $requestedBranchId);
                if ($branch) {
                    $printScope = 'branch';
                    $targetBranchId = $branch->id;
                } else {
                    $printScope = 'pusat';
                    $targetBranchId = null;
                    $branch = null;
                }
            } else {
                // Deteksi otomatis kepemilikan utang supplier: apakah ada di Cabang atau di PT Pusat
                $supplierBranchInvoices = PurchaseInvoice::query()
                    ->where('supplier_id', $supplierId)
                    ->whereNull('deleted_at')
                    ->whereNotIn('status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval'])
                    ->where(function ($q) use ($activeCompanyId, $availableBranches) {
                        $q->where('company_id', $activeCompanyId)
                          ->orWhereIn('branch_id', $availableBranches->pluck('id')->toArray());
                    })
                    ->get(['id', 'branch_id', 'company_id']);

                $hasPusatInvoices = $supplierBranchInvoices->contains(fn($i) => is_null($i->branch_id));
                $branchIds = $supplierBranchInvoices->pluck('branch_id')->filter()->unique()->values()->all();

                if (!$hasPusatInvoices && count($branchIds) === 1) {
                    // Semua invoice milik satu cabang spesifik
                    $targetBranchId = (int) $branchIds[0];
                    $branch = $availableBranches->firstWhere('id', $targetBranchId)
                           ?: Branch::where('company_id', $activeCompanyId)->find($targetBranchId);
                    $printScope = 'branch';
                } else {
                    // Default terikat ke PT Pusat
                    $printScope = 'pusat';
                    $targetBranchId = null;
                    $branch = null;
                }
            }
        }

        // Ambil Faktur Pembelian sesuai scope entitas (Pusat atau Cabang)
        $invoiceQuery = PurchaseInvoice::query()
            ->where('supplier_id', $supplierId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval'])
            ->with(['paymentTerm', 'branch', 'currency', 'paymentDetails.payment']);

        if ($printScope === 'branch' && !empty($targetBranchId)) {
            $invoiceQuery->where('branch_id', $targetBranchId);
        } else {
            $invoiceQuery->whereNull('branch_id')->where('company_id', $activeCompanyId);
        }

        $invoices = $invoiceQuery->orderBy('tanggal_faktur', 'asc')->get();

        // Aging Buckets & Total Utang dalam IDR (Konversi kurs)
        $aging = [
            'current'         => 0,
            'overdue_1_30'    => 0,
            'overdue_31_60'   => 0,
            'overdue_60_plus' => 0,
            'total_utang'     => 0,
        ];

        foreach ($invoices as $inv) {
            $kurs = (float) ($inv->kurs ?: 1);
            $sisaIdr = (float) $inv->remaining_balance * $kurs;
            if ($sisaIdr > 0.01) {
                $aging['total_utang'] += $sisaIdr;
                $days = 0;
                if ($inv->tanggal_jatuh_tempo && Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay()->isPast()) {
                    $days = (int) abs(Carbon::now()->startOfDay()->diffInDays(Carbon::parse($inv->tanggal_jatuh_tempo)->startOfDay(), false));
                }

                if ($days <= 0) {
                    $aging['current'] += $sisaIdr;
                } elseif ($days <= 30) {
                    $aging['overdue_1_30'] += $sisaIdr;
                } elseif ($days <= 60) {
                    $aging['overdue_31_60'] += $sisaIdr;
                } else {
                    $aging['overdue_60_plus'] += $sisaIdr;
                }
            }
        }

        // Ambil Rekening Perusahaan / Supplier Bank
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

        // Logo Cabang / Perusahaan aktif vs Favicon Logo App
        $activeLogo = null;
        if ($branch && !empty($branch->logo)) {
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

        // Favicon Logo Aplikasi resmi dari app_settings
        $appLogoSetting = DB::table('app_settings')->where('key', 'app_logo')->value('value');
        if (!empty($appLogoSetting) && file_exists(public_path('storage/' . $appLogoSetting))) {
            $appLogo = asset('storage/' . $appLogoSetting);
        } elseif (file_exists(public_path('storage/gambar/logo_1786070712.png'))) {
            $appLogo = asset('storage/gambar/logo_1786070712.png');
        } elseif (file_exists(public_path('gambar/logo-app.png'))) {
            $appLogo = asset('gambar/logo-app.png');
        } else {
            $appLogo = asset('gambar/logo.png');
        }

        return view('modul_purchasing.hutang.print_statement', compact(
            'supplier', 'company', 'branch', 'invoices', 'aging', 'bankAccounts',
            'printScope', 'availableBranches', 'sessionBranchId', 'activeLogo', 'appLogo'
        ));
    }

    /**
     * Membangun Query Rekap Utang Supplier sesuai Filter Aktif
     */
    private function buildPayableQuery(Request $request)
    {
        $tenant = $this->getTenantContext();
        $activeCompanyId = $tenant['company_id'];

        // 1. Subquery pembayaran per faktur (hanya yang statusnya Posted)
        $ppdSub = DB::table('purchase_payment_details as ppd')
            ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
            ->whereNull('pp.deleted_at')
            ->where('pp.status', 'Posted');

        $this->applyTenantScope($ppdSub, 'pp');

        $ppdSub->groupBy('ppd.purchase_invoice_id')
            ->select(
                'ppd.purchase_invoice_id',
                DB::raw("SUM(ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)) as total_bayar")
            );

        // 2. Subquery agregasi Faktur Pembelian per Supplier (Dikonversi ke Base Currency IDR secara akurat via kurs)
        $invoiceSub = DB::table('purchase_invoices as pi')
            ->leftJoinSub($ppdSub, 'pay_inv', 'pi.id', '=', 'pay_inv.purchase_invoice_id')
            ->leftJoin('master_currencies as mc', 'pi.currency_id', '=', 'mc.id')
            ->select(
                'pi.supplier_id',
                DB::raw("COUNT(pi.id) as total_faktur_count"),
                DB::raw("SUM(pi.grand_total * COALESCE(NULLIF(pi.kurs, 0), 1)) as total_invoiced"),
                DB::raw("SUM(COALESCE(pi.uang_muka, 0) * COALESCE(NULLIF(pi.kurs, 0), 1)) as total_dp"),
                DB::raw("SUM(COALESCE(pay_inv.total_bayar, 0) * COALESCE(NULLIF(pi.kurs, 0), 1)) as total_paid"),
                DB::raw("SUM(GREATEST(0, (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1))) as sisa_utang"),
                DB::raw("COUNT(CASE WHEN (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN 1 END) as unpaid_count"),
                DB::raw("MIN(CASE WHEN (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 THEN pi.tanggal_jatuh_tempo END) as oldest_due_date"),
                DB::raw("MAX(CASE WHEN (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 AND pi.tanggal_jatuh_tempo < CURDATE() THEN DATEDIFF(CURDATE(), pi.tanggal_jatuh_tempo) ELSE 0 END) as max_overdue_days"),
                DB::raw("SUM(CASE WHEN (pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) > 0.01 AND pi.tanggal_jatuh_tempo < CURDATE() THEN ((pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) * COALESCE(NULLIF(pi.kurs, 0), 1)) ELSE 0 END) as overdue_amount"),
                // Metrik Pemantau Transaksi Valas (Mata Uang Asing)
                DB::raw("COUNT(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN 1 END) as foreign_faktur_count"),
                DB::raw("COUNT(CASE WHEN pi.currency_id IS NULL OR COALESCE(pi.kurs, 1) <= 1 THEN 1 END) as idr_faktur_count"),
                DB::raw("SUM(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN pi.grand_total ELSE 0 END) as foreign_total_invoiced"),
                DB::raw("SUM(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN COALESCE(pi.uang_muka, 0) ELSE 0 END) as foreign_total_dp"),
                DB::raw("SUM(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN COALESCE(pay_inv.total_bayar, 0) ELSE 0 END) as foreign_total_paid"),
                DB::raw("SUM(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN GREATEST(0, pi.grand_total - COALESCE(pi.uang_muka, 0) - COALESCE(pay_inv.total_bayar, 0)) ELSE 0 END) as foreign_sisa_valas"),
                DB::raw("MAX(CASE WHEN pi.currency_id IS NOT NULL AND COALESCE(pi.kurs, 1) > 1 THEN COALESCE(mc.simbol, '$') ELSE NULL END) as foreign_currency_symbol")
            )
            ->whereNull('pi.deleted_at')
            ->whereNotIn('pi.status', ['Draft', 'Cancelled', 'Void', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval']);

        $this->applyTenantScope($invoiceSub, 'pi');

        if ($request->filled('supplier_id')) {
            $invoiceSub->where('pi.supplier_id', $request->supplier_id);
        }

        $invoiceSub->groupBy('pi.supplier_id');

        // 3. Subquery Pembayaran Utang yang Posted per Supplier
        $paymentSub = DB::table('purchase_payments as pp')
            ->select(
                'pp.supplier_id',
                DB::raw("SUM(pp.total_bayar * COALESCE(NULLIF(pp.kurs, 0), 1)) as total_payment_paid"),
                DB::raw("SUM(COALESCE(pp.total_potongan, 0) * COALESCE(NULLIF(pp.kurs, 0), 1)) as total_potongan_paid")
            )
            ->whereNull('pp.deleted_at')
            ->where('pp.status', 'Posted');

        $this->applyTenantScope($paymentSub, 'pp');

        if ($request->filled('supplier_id')) {
            $paymentSub->where('pp.supplier_id', $request->supplier_id);
        }

        $paymentSub->groupBy('pp.supplier_id');

        // 4. Base Supplier Query joined with aggregated invoices & payments
        $query = DB::table('master_suppliers')
            ->leftJoinSub($invoiceSub, 'inv', 'master_suppliers.id', '=', 'inv.supplier_id')
            ->leftJoinSub($paymentSub, 'pay', 'master_suppliers.id', '=', 'pay.supplier_id')
            ->select([
                'master_suppliers.id',
                'master_suppliers.kode_supplier',
                'master_suppliers.nama_supplier',
                'master_suppliers.nama_kontak',
                'master_suppliers.telepon',
                'master_suppliers.email',
                'master_suppliers.term_pembayaran',
                'master_suppliers.limit_kredit',
                DB::raw("COALESCE(inv.total_faktur_count, 0) as total_faktur_count"),
                DB::raw("COALESCE(inv.total_invoiced, 0) as total_invoiced"),
                DB::raw("COALESCE(inv.total_dp, 0) as total_dp"),
                DB::raw("COALESCE(inv.sisa_utang, 0) as sisa_utang"),
                DB::raw("COALESCE(inv.unpaid_count, 0) as unpaid_count"),
                'inv.oldest_due_date',
                DB::raw("COALESCE(inv.max_overdue_days, 0) as max_overdue_days"),
                DB::raw("COALESCE(inv.overdue_amount, 0) as overdue_amount"),
                DB::raw("COALESCE(inv.total_paid, 0) as total_paid"),
                DB::raw("COALESCE(inv.foreign_faktur_count, 0) as foreign_faktur_count"),
                DB::raw("COALESCE(inv.idr_faktur_count, 0) as idr_faktur_count"),
                DB::raw("COALESCE(inv.foreign_total_invoiced, 0) as foreign_total_invoiced"),
                DB::raw("COALESCE(inv.foreign_total_dp, 0) as foreign_total_dp"),
                DB::raw("COALESCE(inv.foreign_total_paid, 0) as foreign_total_paid"),
                DB::raw("COALESCE(inv.foreign_sisa_valas, 0) as foreign_sisa_valas"),
                'inv.foreign_currency_symbol',
            ])
            ->whereNull('master_suppliers.deleted_at');

        if (!empty($activeCompanyId)) {
            $query->where(function ($q) use ($activeCompanyId) {
                $q->where('master_suppliers.company_id', $activeCompanyId)
                  ->orWhereNull('master_suppliers.company_id');
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('master_suppliers.id', $request->supplier_id);
        }

        if ($request->filled('aging_filter')) {
            $filter = $request->aging_filter;
            if ($filter === 'has_utang') {
                $query->where('inv.sisa_utang', '>', 0.01);
            } elseif ($filter === 'lunas') {
                $query->where('inv.sisa_utang', '<=', 0.01)
                      ->where('inv.total_faktur_count', '>', 0);
            } elseif ($filter === 'current') {
                $query->where('inv.sisa_utang', '>', 0.01)
                      ->where('inv.max_overdue_days', '<=', 0);
            } elseif ($filter === 'overdue_1_30') {
                $query->where('inv.sisa_utang', '>', 0.01)
                      ->whereBetween('inv.max_overdue_days', [1, 30]);
            } elseif ($filter === 'overdue_31_60') {
                $query->where('inv.sisa_utang', '>', 0.01)
                      ->whereBetween('inv.max_overdue_days', [31, 60]);
            } elseif ($filter === 'overdue_60_plus') {
                $query->where('inv.sisa_utang', '>', 0.01)
                      ->where('inv.max_overdue_days', '>', 60);
            } elseif ($filter === 'all_master') {
                // Tampilkan semua master supplier
            }
        } else {
            $query->whereNotNull('inv.supplier_id');
        }

        return $query;
    }

    /**
     * Export Excel Rekap Utang Supplier & Status Aging Sesuai Filter
     */
    public function exportExcel(Request $request)
    {
        if (!$this->canViewPayable()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor data utang supplier.');
        }

        $query = $this->buildPayableQuery($request);
        $suppliers = $query->orderBy('inv.sisa_utang', 'desc')->get();

        $rows = [
            [
                'No',
                'Kode Supplier',
                'Nama Perusahaan (PT) / Supplier',
                'Kontak (PIC)',
                'Telepon',
                'Status Umur Utang (Aging)',
                'Keterlambatan (Hari)',
                'Jatuh Tempo Tertua',
                'Sisa Utang (Mata Uang Asing / Valas)',
                'Sisa Utang (Rupiah)',
                'Jumlah Faktur Belum Lunas'
            ]
        ];

        $no = 1;
        $totalSisaIdr = 0;
        $totalForeignSisa = 0;
        $totalUnpaidCount = 0;
        $foreignSymbolMain = '$';

        foreach ($suppliers as $s) {
            $sisaIdr = (float) $s->sisa_utang;
            $overdueDays = (int) $s->max_overdue_days;
            $oldestDue = $s->oldest_due_date ? Carbon::parse($s->oldest_due_date)->format('d/m/Y') : '-';

            if ($sisaIdr <= 0.01) {
                $statusAging = 'Lunas / Aman';
            } elseif ($overdueDays <= 0) {
                $statusAging = 'Lancar (Belum Jatuh Tempo)';
            } elseif ($overdueDays <= 30) {
                $statusAging = 'Tempo 1 - 30 Hari';
            } elseif ($overdueDays <= 60) {
                $statusAging = 'Tempo 31 - 60 Hari';
            } else {
                $statusAging = 'Kritis (> 60 Hari)';
            }

            $foreignSisaValas = (float) ($s->foreign_sisa_valas ?? 0);
            $sym = $s->foreign_currency_symbol ?: '$';
            if ($foreignSisaValas > 0.01) {
                $foreignSymbolMain = $sym;
                $valasStr = $sym . ' ' . number_format($foreignSisaValas, 2, ',', '.');
            } else {
                $valasStr = '-';
            }

            $totalSisaIdr += $sisaIdr;
            $totalForeignSisa += $foreignSisaValas;
            $totalUnpaidCount += (int) $s->unpaid_count;

            $rows[] = [
                $no++,
                $s->kode_supplier ?: ('SUP-' . $s->id),
                $s->nama_supplier,
                $s->nama_kontak ?: '-',
                $s->telepon ?: '-',
                $statusAging,
                $overdueDays > 0 ? $overdueDays : 0,
                $oldestDue,
                $valasStr,
                (float) $sisaIdr,
                (int) $s->unpaid_count,
            ];
        }

        // Summary Row
        $rows[] = [
            'TOTAL KESELURUHAN',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalForeignSisa > 0.01 ? ($foreignSymbolMain . ' ' . number_format($totalForeignSisa, 2, ',', '.')) : '-',
            $totalSisaIdr,
            $totalUnpaidCount
        ];

        $filename = 'Rekap_Utang_Supplier_' . date('Ymd_His') . '.xlsx';
        if (ob_get_length()) {
            ob_end_clean();
        }
        $xlsx = SimpleXLSXGen::fromArray($rows);
        $xlsx->downloadAs($filename);
        exit();
    }

    /**
     * Cetak PDF Laporan Rekap Sisa Tagihan & Aging Utang Supplier
     */
    public function printPdf(Request $request)
    {
        if (!$this->canPrintPayable()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak laporan utang supplier.');
        }

        $tenant = $this->getTenantContext();
        $sessionBranchId = $tenant['branch_id'];
        $activeCompanyId = $tenant['company_id'];

        $company = Company::find($activeCompanyId) ?: Company::first();
        $branch = !empty($sessionBranchId) ? Branch::find($sessionBranchId) : null;

        $query = $this->buildPayableQuery($request);
        $suppliers = $query->orderBy('inv.sisa_utang', 'desc')->get();

        // Selected filter labels
        $selectedSupplierName = 'Semua Supplier';
        if ($request->filled('supplier_id')) {
            $sup = MasterSupplier::find($request->supplier_id);
            if ($sup) {
                $selectedSupplierName = ($sup->kode_supplier ? '[' . $sup->kode_supplier . '] ' : '') . $sup->nama_supplier;
            }
        }

        $agingLabels = [
            'has_utang'       => 'Belum Lunas (Memiliki Sisa Utang)',
            'lunas'           => 'Lunas',
            'current'         => 'Lancar (Belum Jatuh Tempo)',
            'overdue_1_30'    => 'Tempo 1 - 30 Hari',
            'overdue_31_60'   => 'Tempo 31 - 60 Hari',
            'overdue_60_plus' => 'Kritis (> 60 Hari)',
            'all_master'      => 'Semua Master Supplier',
        ];
        $selectedAgingLabel = $agingLabels[$request->aging_filter] ?? 'Semua Status Utang';

        // Summary KPI
        $summary = [
            'total_invoiced'         => (float) $suppliers->sum('total_invoiced'),
            'total_dp'               => (float) $suppliers->sum('total_dp'),
            'total_paid'             => (float) $suppliers->sum('total_paid'),
            'total_sisa'             => (float) $suppliers->sum('sisa_utang'),
            'total_foreign_sisa'     => (float) $suppliers->sum('foreign_sisa_valas'),
            'total_foreign_invoiced' => (float) $suppliers->sum('foreign_total_invoiced'),
            'foreign_symbol'         => $suppliers->where('foreign_sisa_valas', '>', 0.01)->first()?->foreign_currency_symbol 
                                     ?: ($suppliers->where('foreign_total_invoiced', '>', 0.01)->first()?->foreign_currency_symbol ?: '$'),
            'supplier_count'         => $suppliers->count(),
            'unpaid_vendor_count'    => $suppliers->where('sisa_utang', '>', 0.01)->count(),
            'unpaid_faktur_count'    => (int) $suppliers->sum('unpaid_count'),
        ];

        // Logo Cabang / Perusahaan aktif vs Favicon Logo App
        $activeLogo = null;
        if ($branch && !empty($branch->logo)) {
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

        // Favicon Logo Aplikasi resmi dari app_settings
        $appLogoSetting = DB::table('app_settings')->where('key', 'app_logo')->value('value');
        if (!empty($appLogoSetting) && file_exists(public_path('storage/' . $appLogoSetting))) {
            $appLogo = asset('storage/' . $appLogoSetting);
        } elseif (file_exists(public_path('storage/gambar/logo_1786070712.png'))) {
            $appLogo = asset('storage/gambar/logo_1786070712.png');
        } elseif (file_exists(public_path('gambar/logo-app.png'))) {
            $appLogo = asset('gambar/logo-app.png');
        } else {
            $appLogo = asset('gambar/logo.png');
        }

        return view('modul_purchasing.hutang.print_pdf', compact(
            'suppliers',
            'company',
            'branch',
            'selectedSupplierName',
            'selectedAgingLabel',
            'summary',
            'activeLogo',
            'appLogo'
        ));
    }
}
