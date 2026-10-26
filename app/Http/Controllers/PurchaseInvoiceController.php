<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\MasterSupplier;
use App\Models\MasterPajak;
use App\Models\PaymentTerm;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentDetail;
use App\Models\ChartOfAccount;
use App\Models\KasBank;
use App\Models\KasMutasi;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\WhatsAppGatewayController;

class PurchaseInvoiceController extends Controller
{
    private function canViewInvoice(): bool
    {
        $u = auth()->user();
        return $u && $u->can('lihat-purchase-invoice');
    }

    private function canViewJurnal(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasAnyRole(['Super Admin', 'Owner']) || $u->can('lihat-jurnal-purchase-invoice') || $u->can('lihat-jurnal-umum'));
    }

    private function canCreateInvoice(): bool
    {
        $u = auth()->user();
        return $u && $u->can('tambah-purchase-invoice');
    }

    private function canEditInvoice(?PurchaseInvoice $inv = null): bool
    {
        $u = auth()->user();
        if (!$u || !$u->can('edit-purchase-invoice')) {
            return false;
        }

        if ($inv) {
            if (!in_array($inv->status, ['Draft', 'Rejected', 'Menunggu Persetujuan', 'Menunggu Approval'])) {
                return false;
            }

            // Isolasi Cabang / Tenant (Mencegah IDOR Antar Cabang)
            if (!$u->hasAnyRole(['Super Admin', 'Owner'])) {
                $activeBranchId  = session('active_branch_id');
                $activeCompanyId = session('active_company_id');
                if (!empty($activeBranchId) && !empty($inv->branch_id) && $inv->branch_id != $activeBranchId) {
                    return false;
                }
                if (!empty($activeCompanyId) && !empty($inv->company_id) && $inv->company_id != $activeCompanyId) {
                    return false;
                }
            }
        }

        return true;
    }

    private function canDeleteInvoice(?PurchaseInvoice $inv = null): bool
    {
        $u = auth()->user();
        if (!$u || !$u->can('hapus-purchase-invoice')) {
            return false;
        }

        if ($inv) {
            if (!in_array($inv->status, ['Draft', 'Rejected'])) {
                return false;
            }

            // Isolasi Cabang / Tenant (Mencegah IDOR Antar Cabang)
            if (!$u->hasAnyRole(['Super Admin', 'Owner'])) {
                $activeBranchId  = session('active_branch_id');
                $activeCompanyId = session('active_company_id');
                if (!empty($activeBranchId) && !empty($inv->branch_id) && $inv->branch_id != $activeBranchId) {
                    return false;
                }
                if (!empty($activeCompanyId) && !empty($inv->company_id) && $inv->company_id != $activeCompanyId) {
                    return false;
                }
            }
        }

        return true;
    }

    private function isInvoiceApprover(): bool
    {
        $u = auth()->user();
        if (!$u) return false;
        try {
            return $u->hasPermissionTo('approve-purchase-invoice');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function canCancelInvoice(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasAnyRole(['Super Admin', 'Owner']) || $u->can('batalkan-purchase-invoice'));
    }

    public function index(Request $request)
    {
        if (!$this->canViewInvoice()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Faktur Pembelian.');
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $currentUser = auth()->user();
        $userId = auth()->id();

        // Base Query - Only show invoices that have supplier invoice number entered
        // LOGIKA: Status Draft hanya bisa dilihat oleh pengguna yang membuatnya
        $query = PurchaseInvoice::query()
            ->select('purchase_invoices.*')
            ->with(['supplier', 'goodsReceipt.tender', 'goodsReceipt.po.tender', 'po.tender', 'tender', 'po.currency', 'currency', 'branch', 'company', 'paymentDetails.payment', 'creator'])
            ->forActiveBranch()
            ->forActiveCompany()
            ->whereNotNull('purchase_invoices.nomor_faktur_supplier')
            ->where('purchase_invoices.nomor_faktur_supplier', '!=', '')
            ->where(function ($q) use ($userId) {
                $q->where('purchase_invoices.status', '!=', 'Draft')
                  ->orWhere('purchase_invoices.created_by', $userId);
            })
            ->orderByDesc('purchase_invoices.tanggal_faktur')
            ->orderByDesc('purchase_invoices.id');

        // ===== ADVANCED FILTER (Modal Filter Pencarian) =====
        if ($request->filled('f_supplier_id')) {
            $query->where('purchase_invoices.supplier_id', $request->input('f_supplier_id'));
        }

        if ($request->filled('f_status')) {
            $statusMap = [
                'Draft'    => ['Draft'],
                'Approval' => ['Menunggu Persetujuan', 'Menunggu Approval'],
                'Rejected' => ['Rejected'],
                'Posted'   => ['Posted', 'Paid'],
                'Cancelled'=> ['Cancelled', 'Void'],
            ];
            $statuses = $statusMap[$request->input('f_status')] ?? null;
            if ($statuses) {
                $query->whereIn('purchase_invoices.status', $statuses);
            }
        }

        if ($request->filled('f_payment')) {
            // Mirror accessor paid_amount: DP + SUM(jumlah_bayar + potongan) dari pembayaran berstatus Posted
            $paidSql = "(COALESCE(purchase_invoices.uang_muka, 0) + COALESCE((
                SELECT SUM(ppd.jumlah_bayar + ppd.potongan)
                FROM purchase_payment_details ppd
                INNER JOIN purchase_payments pp ON pp.id = ppd.purchase_payment_id
                WHERE ppd.purchase_invoice_id = purchase_invoices.id
                  AND pp.status = 'Posted' AND pp.deleted_at IS NULL
            ), 0))";
            $query->whereIn('purchase_invoices.status', ['Posted', 'Paid']);
            switch ($request->input('f_payment')) {
                case 'lunas':
                    $query->where(function ($q) use ($paidSql) {
                        $q->where('purchase_invoices.status', 'Paid')
                          ->orWhereRaw("(purchase_invoices.grand_total - {$paidSql}) <= 0.01 AND {$paidSql} > 0");
                    });
                    break;
                case 'sebagian':
                    $query->where('purchase_invoices.status', '!=', 'Paid')
                          ->whereRaw("{$paidSql} > 0")
                          ->whereRaw("(purchase_invoices.grand_total - {$paidSql}) > 0.01");
                    break;
                case 'belum':
                    $query->where('purchase_invoices.status', '!=', 'Paid')
                          ->whereRaw("{$paidSql} <= 0");
                    break;
            }
        }

        if ($request->filled('f_tgl_from')) {
            $query->whereDate('purchase_invoices.tanggal_faktur', '>=', $request->input('f_tgl_from'));
        }
        if ($request->filled('f_tgl_to')) {
            $query->whereDate('purchase_invoices.tanggal_faktur', '<=', $request->input('f_tgl_to'));
        }
        if ($request->filled('f_due_from')) {
            $query->whereDate('purchase_invoices.tanggal_jatuh_tempo', '>=', $request->input('f_due_from'));
        }
        if ($request->filled('f_due_to')) {
            $query->whereDate('purchase_invoices.tanggal_jatuh_tempo', '<=', $request->input('f_due_to'));
        }

        if ($request->filled('f_due_state')) {
            $today = \Carbon\Carbon::today()->toDateString();
            // Kondisi jatuh tempo hanya relevan untuk faktur aktif yang belum lunas
            $query->whereIn('purchase_invoices.status', ['Posted', 'Menunggu Persetujuan', 'Menunggu Approval'])
                  ->whereNotNull('purchase_invoices.tanggal_jatuh_tempo');
            switch ($request->input('f_due_state')) {
                case 'overdue':
                    $query->whereDate('purchase_invoices.tanggal_jatuh_tempo', '<', $today);
                    break;
                case 'today':
                    $query->whereDate('purchase_invoices.tanggal_jatuh_tempo', '=', $today);
                    break;
                case 'week':
                    $query->whereDate('purchase_invoices.tanggal_jatuh_tempo', '>', $today)
                          ->whereDate('purchase_invoices.tanggal_jatuh_tempo', '<=', \Carbon\Carbon::today()->addDays(7)->toDateString());
                    break;
            }
        }

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('nomor_faktur', function ($i) {
                    return '<a href="javascript:void(0)" class="fw-bold text-primary btn-view-invoice text-decoration-none" data-id="' . $i->id . '" title="Lihat Detail">' . e($i->nomor_faktur) . '</a>';
                })
                ->addColumn('supplier_name', fn($i) => $i->supplier?->nama_supplier ?? '-')
                ->filterColumn('supplier_name', function ($query, $keyword) {
                    $query->whereHas('supplier', function ($sq) use ($keyword) {
                        $sq->where('nama_supplier', 'like', "%{$keyword}%")
                           ->orWhere('kode_supplier', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('supplier_name', function ($query, $order) {
                    $query->orderBy(
                        MasterSupplier::select('nama_supplier')
                            ->whereColumn('master_suppliers.id', 'purchase_invoices.supplier_id')
                            ->limit(1),
                        $order
                    );
                })
                ->addColumn('nomor_gr', function ($i) {
                    $refText = $i->goodsReceipt?->nomor_gr ?: ($i->po ? ('PO: ' . $i->po->nomor_po) : '-');
                    $tenderObj = $i->tender ?? $i->goodsReceipt?->tender ?? $i->po?->tender;
                    $tenderNomor = $tenderObj ? (!empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-' ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : '')) : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)))) : null;
                    $html = '<div><span class="text-info fw-semibold font-monospace">' . e($refText) . '</span></div>';
                    if ($tenderNomor) {
                        $html .= '<div class="mt-1"><span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;" title="Terkait Proyek Tender"><i class="bi bi-briefcase me-1"></i>' . e($tenderNomor) . '</span></div>';
                    }
                    return $html;
                })
                ->filterColumn('nomor_gr', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereHas('goodsReceipt', function ($grQ) use ($keyword) {
                            $grQ->where('nomor_gr', 'like', "%{$keyword}%");
                        })->orWhereHas('po', function ($poQ) use ($keyword) {
                            $poQ->where('nomor_po', 'like', "%{$keyword}%");
                        })->orWhereHas('tender', function ($tQ) use ($keyword) {
                            $tQ->where('nomor_tender', 'like', "%{$keyword}%")
                               ->orWhere('nomor_kontrak', 'like', "%{$keyword}%")
                               ->orWhere('nama_tender', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->orderColumn('nomor_gr', function ($query, $order) {
                    $query->orderBy(
                        GoodsReceipt::select('nomor_gr')
                            ->whereColumn('goods_receipts.id', 'purchase_invoices.gr_id')
                            ->limit(1),
                        $order
                    );
                })
                ->addColumn('tanggal_faktur_fmt', fn($i) => $i->tanggal_faktur ? $i->tanggal_faktur->format('d/m/Y') : '-')
                ->addColumn('tanggal_jatuh_tempo_fmt', function ($i) {
                    if (!$i->tanggal_jatuh_tempo) {
                        return '<span class="text-muted font-monospace">-</span>';
                    }

                    $tglStr = $i->tanggal_jatuh_tempo->format('d/m/Y');
                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;
                    $isPaid = ($i->status === 'Paid') || ($rem <= 0.01 && $paid > 0);

                    // Jika faktur sudah lunas
                    if ($isPaid) {
                        return '<div><span class="text-muted font-monospace fw-medium">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size:0.65rem;"><i class="bi bi-check2 me-1"></i>Lunas</span></div>';
                    }

                    // Jika faktur ditolak (Rejected)
                    if ($i->status === 'Rejected') {
                        return '<div><span class="text-muted font-monospace fw-medium">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5" style="font-size:0.65rem;">Ditolak</span></div>';
                    }

                    // Hitung selisih hari dengan hari ini
                    $today = \Carbon\Carbon::today();
                    $dueDate = $i->tanggal_jatuh_tempo->copy()->startOfDay();
                    $diffDays = (int) $today->diffInDays($dueDate, false);

                    if ($diffDays < 0) {
                        // LEWAT JATUH TEMPO (OVERDUE)
                        $daysLate = abs($diffDays);
                        return '<div><span class="text-danger font-monospace fw-bold">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge bg-danger text-white rounded-pill px-2 py-0.5 fw-bold shadow-sm" style="font-size:0.67rem;" title="Tagihan telah melewati jatuh tempo!"><i class="bi bi-exclamation-triangle-fill me-1"></i>Lewat ' . $daysLate . ' Hari</span></div>';
                    } elseif ($diffDays === 0) {
                        // JATUH TEMPO HARI INI
                        return '<div><span class="text-warning-emphasis font-monospace fw-bold">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge bg-warning text-dark border border-warning rounded-pill px-2 py-0.5 fw-bold shadow-sm" style="font-size:0.67rem;" title="Tagihan harus dilunasi hari ini!"><i class="bi bi-bell-fill text-danger me-1"></i>Hari Ini</span></div>';
                    } elseif ($diffDays <= 3) {
                        // SEGERA JATUH TEMPO (H-1 s/d H-3)
                        return '<div><span class="text-warning-emphasis font-monospace fw-semibold">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="font-size:0.67rem;background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.5);"><i class="bi bi-clock-history me-1"></i>Sisa ' . $diffDays . ' Hari</span></div>';
                    } elseif ($diffDays <= 7) {
                        // MENDEKATI JATUH TEMPO (H-4 s/d H-7)
                        return '<div><span class="text-body font-monospace fw-semibold">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge rounded-pill px-2 py-0.5 fw-medium" style="font-size:0.67rem;background:rgba(56,189,248,0.15);color:#7dd3fc;border:1px solid rgba(56,189,248,0.5);"><i class="bi bi-hourglass-split me-1"></i>Sisa ' . $diffDays . ' Hari</span></div>';
                    } else {
                        // MASIH AMAN (> 7 Hari)
                        return '<div><span class="text-body font-monospace fw-medium">' . e($tglStr) . '</span></div>'
                            . '<div class="mt-1"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5" style="font-size:0.65rem;"><i class="bi bi-calendar-check me-1"></i>Sisa ' . $diffDays . ' Hari</span></div>';
                    }
                })
                ->addColumn('grand_total_fmt', function ($i) {
                    $cur = $i->currency ?? $i->po?->currency;
                    $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
                    $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']);
                    $curSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : 'Rp'));
                    $kurs = (float) ($i->kurs ?: 1);

                    if ($isForeign) {
                        $idrEquiv = (float) $i->grand_total * $kurs;
                        return '<div class="fw-bold text-body font-monospace">' . e($curSymbol) . ' ' . number_format($i->grand_total, 2, ',', '.') . '</div>'
                            . '<small class="d-block font-monospace" style="font-size:0.72rem;color:#94a3b8;">≈ Rp ' . number_format($idrEquiv, 2, ',', '.') . '</small>';
                    }

                    return '<div class="fw-bold text-body font-monospace">Rp ' . number_format($i->grand_total, 2, ',', '.') . '</div>';
                })
                ->addColumn('sisa_hutang_fmt', function ($i) {
                    if (in_array($i->status, ['Draft', 'Menunggu Persetujuan', 'Menunggu Approval', 'Rejected'])) {
                        return '<span class="text-muted font-monospace small">-</span>';
                    }
                    if ($i->status === 'Cancelled' || $i->status === 'Void') {
                        return '<span class="badge rounded-pill fw-semibold px-2 py-1" style="font-size:0.7rem;background:rgba(148,163,184,0.15);color:#cbd5e1;border:1px solid rgba(148,163,184,0.45);"><i class="bi bi-slash-circle me-1"></i>Dibatalkan</span>';
                    }

                    $cur = $i->currency ?? $i->po?->currency;
                    $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
                    $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']);
                    $curSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : 'Rp'));
                    $kurs = (float) ($i->kurs ?: 1);
                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;

                    if ($i->status === 'Paid' || ($rem <= 0.01 && $paid > 0)) {
                        return '<span class="badge rounded-pill fw-semibold px-2 py-1" style="font-size:0.72rem;background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.5);"><i class="bi bi-check2-all me-1"></i>Lunas</span>';
                    }

                    $sisaFmt = $isForeign ? ($curSymbol . ' ' . number_format($rem, 2, ',', '.')) : ('Rp ' . number_format($rem, 2, ',', '.'));
                    $html = '<div class="fw-bold font-monospace text-warning">' . e($sisaFmt) . '</div>';
                    if ($isForeign && $rem > 0) {
                        $idrRem = $rem * $kurs;
                        $html .= '<small class="d-block font-monospace" style="font-size:0.72rem;color:#94a3b8;">≈ Rp ' . number_format($idrRem, 2, ',', '.') . '</small>';
                    }
                    return $html;
                })
                ->addColumn('status_badge', function ($i) {
                    if ($i->status === 'Draft') {
                        return '<span class="badge bg-secondary bg-opacity-75 text-white border border-secondary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    }

                    if ($i->status === 'Menunggu Persetujuan' || $i->status === 'Menunggu Approval') {
                        return '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-hourglass-split me-1"></i>Menunggu Approval</span>';
                    }

                    if ($i->status === 'Rejected') {
                        return '<span class="badge bg-danger text-white border border-danger rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
                    }

                    if ($i->status === 'Cancelled' || $i->status === 'Void') {
                        return '<span class="badge bg-danger bg-opacity-75 text-white border border-danger rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-octagon me-1"></i>Dibatalkan</span>';
                    }

                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;

                    if ($i->status === 'Paid' || ($rem <= 0.01 && $paid > 0)) {
                        return '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check2-all me-1"></i>Lunas</span>';
                    } elseif ($paid > 0 && $rem > 0.01) {
                        return '<span class="badge bg-info text-dark border border-info rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pie-chart-fill me-1"></i>Sebagian</span>';
                    } else {
                        return '<span class="badge bg-primary text-white border border-primary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Posted (Belum Lunas)</span>';
                    }
                })
                ->addColumn('action', function ($i) use ($currentUser) {
                    $isApprover = $this->isInvoiceApprover();
                    $isCreator = $currentUser && ($currentUser->id == $i->created_by);
                    $canEdit = $this->canEditInvoice($i);
                    $canDelete = $this->canDeleteInvoice($i);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    
                    // View Button
                    $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-circle btn-view-invoice" data-id="' . $i->id . '" title="Detail Faktur"><i class="bi bi-eye"></i></button>';

                    // Draft Actions
                    if ($i->status === 'Draft') {
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-invoice" data-id="' . $i->id . '" title="Edit Faktur"><i class="bi bi-pencil"></i></button>';
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-primary rounded-circle btn-submit-approval-invoice" data-id="' . $i->id . '" title="Ajukan ke Atasan (Kirim WA)"><i class="bi bi-send"></i></button>';
                        }
                        if ($canDelete) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-invoice" data-id="' . $i->id . '" title="Hapus Faktur"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    // Menunggu Persetujuan Actions
                    if ($i->status === 'Menunggu Persetujuan' || $i->status === 'Menunggu Approval') {
                        if ($isApprover) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-circle btn-approve-invoice" data-id="' . $i->id . '" title="Setujui & Posting Jurnal (ACC)"><i class="bi bi-check-lg"></i></button>';
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-reject-invoice" data-id="' . $i->id . '" title="Tolak Pengajuan"><i class="bi bi-x-lg"></i></button>';
                        }
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-secondary rounded-circle btn-wa-invoice" data-id="' . $i->id . '" title="Kirim Ulang WA ke Atasan"><i class="bi bi-whatsapp"></i></button>';
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-invoice" data-id="' . $i->id . '" title="Ubah Pengajuan"><i class="bi bi-pencil"></i></button>';
                        }
                    }

                    // Rejected Actions (Creator can edit & resubmit, or delete)
                    if ($i->status === 'Rejected') {
                        if ($canEdit) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-invoice" data-id="' . $i->id . '" title="Perbaiki & Ajukan Ulang"><i class="bi bi-pencil"></i></button>';
                        }
                        if ($canDelete) {
                            $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-invoice" data-id="' . $i->id . '" title="Hapus Faktur"><i class="bi bi-trash"></i></button>';
                        }
                    }

                    // Cetak Faktur PDF Button
                    if ($currentUser && $currentUser->can('cetak-purchase-invoice')) {
                        $btn .= '<a href="' . url('/purchase-invoices/' . $i->id . '/pdf') . '" target="_blank" class="btn btn-sm btn-icon btn-outline-primary rounded-circle" title="Cetak Faktur (PDF)"><i class="bi bi-printer"></i></a>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->setRowClass(function ($i) {
                    if (!$i->tanggal_jatuh_tempo) return '';
                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;
                    $isPaid = ($i->status === 'Paid') || ($rem <= 0.01 && $paid > 0);
                    if ($isPaid || in_array($i->status, ['Rejected', 'Draft', 'Cancelled', 'Void'])) return '';

                    $today = \Carbon\Carbon::today();
                    $dueDate = $i->tanggal_jatuh_tempo->copy()->startOfDay();
                    $diffDays = (int) $today->diffInDays($dueDate, false);

                    if ($diffDays < 0) {
                        return 'inv-row-overdue';
                    } elseif ($diffDays === 0) {
                        return 'inv-row-due-today';
                    }
                    return '';
                })
                ->rawColumns(['nomor_faktur', 'nomor_gr', 'grand_total_fmt', 'sisa_hutang_fmt', 'status_badge', 'tanggal_jatuh_tempo_fmt', 'action'])
                ->with('kpi', fn() => $this->calculateKpiSummary($userId, $activeBranchId, $activeCompanyId))
                ->make(true);
        }

        // Hitung KPI Summary untuk initial page load
        $kpi = $this->calculateKpiSummary($userId, $activeBranchId, $activeCompanyId);
        extract($kpi);

        // Fetch eligible posted GRs for invoice creation dropdown (dibatasi 30 data teratas agar super cepat)
        $eligibleGrs = $this->queryEligibleGrs()->limit(30)->get();
        // Fetch eligible approved POs for invoice creation dropdown (dibatasi 30 data teratas agar super cepat)
        $eligiblePos = $this->queryEligiblePos()->limit(30)->get();

        $suppliers = MasterSupplier::orderBy('nama_supplier')->get(['id', 'kode_supplier', 'nama_supplier']);
        $pajaks = MasterPajak::orderBy('nama_pajak')->get(['id', 'nama_pajak', 'tarif']);
        $paymentTerms = PaymentTerm::orderBy('nama_term')->get(['id', 'nama_term', 'jumlah_hari']);
        $coas = ChartOfAccount::where('is_header', 0)->orderBy('code')->get(['id', 'code', 'name']);

        $defaultGrniCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_gr_clearing')
            ?? \App\Helpers\AccountMappingHelper::getCoaId('default', 'unbilled_ap')
            ?? ChartOfAccount::where('code', 'like', '2102%')->orWhere('name', 'like', '%GR/IR%')->orWhere('name', 'like', '%Penerimaan Barang%')->value('id')
            ?? ChartOfAccount::where('is_header', 0)->value('id');

        $defaultPpnCoaId = \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
            ?? ChartOfAccount::where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%')->value('id')
            ?? ChartOfAccount::where('is_header', 0)->value('id');

        $defaultFreightCoaId = ChartOfAccount::where('code', 'like', '5103%')->orWhere('name', 'like', '%Biaya Angkut%')->orWhere('name', 'like', '%Freight%')->value('id')
            ?? ChartOfAccount::where('code', 'like', '6104%')->orWhere('name', 'like', '%Pengiriman%')->value('id')
            ?? $defaultGrniCoaId;

        // Default COA Utang PPh (PPh dipotong atas pembelian, mis. PPh 23)
        $defaultPphCoaId = $this->resolveDefaultPphCoaId();

        // Default COA Uang Muka Pembelian (DP Supplier)
        $defaultUangMukaCoaId = $this->resolveDefaultUangMukaCoaId();

        // Map pajak_id => COA hasil pemetaan sisi Pembelian (tax_{id}_in) untuk auto-pilih COA PPh
        $pphCoaMap = [];
        foreach ($pajaks as $pj) {
            $mapped = \App\Helpers\AccountMappingHelper::getCoaId('tax', "tax_{$pj->id}_in");
            if ($mapped) {
                $pphCoaMap[$pj->id] = $mapped;
            }
        }

        // Map MasterJenisBiaya => COA untuk auto-pilih COA Biaya Tambahan
        $expenseTypes = \App\Models\MasterJenisBiaya::forActiveCompany()->active()->get();
        $expenseTypeCoaMap = [];
        foreach ($expenseTypes as $et) {
            if ($et->coa_id) {
                $nameKey = strtolower(trim($et->nama_jenis_biaya));
                $expenseTypeCoaMap[$nameKey] = (int) $et->coa_id;
                $expenseTypeCoaMap[$et->id] = (int) $et->coa_id;
            }
        }

        // Data Rekening Kas & Bank Aktif untuk Pengeluaran Uang Muka (DP)
        $kasBanks = KasBank::with('coa')
            ->forActiveCompany()
            ->forActiveBranch()
            ->where('is_active', true)
            ->orderBy('tipe', 'asc')
            ->orderBy('nama_akun', 'asc')
            ->get();

        if ($kasBanks->isEmpty()) {
            $kasBanks = KasBank::with('coa')
                ->forActiveCompany()
                ->where('is_active', true)
                ->orderBy('tipe', 'asc')
                ->orderBy('nama_akun', 'asc')
                ->get();
        }

        return view('modul_purchasing.faktur_pembelian.index', compact(
            'eligibleGrs',
            'eligiblePos',
            'suppliers',
            'pajaks',
            'paymentTerms',
            'coas',
            'kasBanks',
            'defaultGrniCoaId',
            'defaultPpnCoaId',
            'defaultFreightCoaId',
            'defaultPphCoaId',
            'defaultUangMukaCoaId',
            'pphCoaMap',
            'expenseTypeCoaMap',
            'totalInvoiceCount',
            'countIdr',
            'countForeign',
            'totalUnpaidAmount',
            'unpaidIdr',
            'unpaidForeign',
            'unpaidForeignSymbol',
            'unpaidForeignCode',
            'totalPaidThisMonth',
            'paidThisMonthIdr',
            'paidThisMonthForeign',
            'paidThisMonthForeignSymbol',
            'paidThisMonthForeignCode',
            'currentMonthName'
        ));
    }

    /**
     * Hitung KPI Summary untuk Faktur Pembelian secara real-time dan modular.
     */
    public function calculateKpiSummary(?int $userId = null, $activeBranchId = null, $activeCompanyId = null): array
    {
        $userId          = $userId ?? auth()->id();
        $activeBranchId  = $activeBranchId ?? session('active_branch_id');
        $activeCompanyId = $activeCompanyId ?? session('active_company_id');

        $cacheKey = 'pi_kpi_summary_' . ($activeBranchId ?: 'all') . '_' . ($activeCompanyId ?: 'all') . '_' . ($userId ?: '0');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 4, function () use ($userId, $activeBranchId, $activeCompanyId) {
            $baseKpiQuery = PurchaseInvoice::query()
                ->forActiveBranch()
                ->forActiveCompany()
                ->whereNotNull('purchase_invoices.nomor_faktur_supplier')
                ->where('purchase_invoices.nomor_faktur_supplier', '!=', '')
                ->where(function ($q) use ($userId) {
                    $q->where('purchase_invoices.status', '!=', 'Draft')
                      ->orWhere('purchase_invoices.created_by', $userId);
                });

            // Subquery payments total per invoice (Hanya pembayaran sah yang sudah berstatus Posted)
            $paymentsSub = DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
                ->whereNull('pp.deleted_at')
                ->where('pp.status', 'Posted')
                ->groupBy('ppd.purchase_invoice_id')
                ->select('ppd.purchase_invoice_id', DB::raw('SUM(ppd.jumlah_bayar + COALESCE(ppd.potongan, 0)) as total_bayar'));

            // Single Fast Aggregate Query di database level (0.01s bahkan pada 7M+ baris)
            $kpiStats = (clone $baseKpiQuery)
                ->leftJoinSub($paymentsSub, 'pay', 'purchase_invoices.id', '=', 'pay.purchase_invoice_id')
                ->leftJoin('master_currencies as mc', 'purchase_invoices.currency_id', '=', 'mc.id')
                ->selectRaw("
                    COUNT(purchase_invoices.id) as total_invoice_count,
                    SUM(CASE WHEN (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(purchase_invoices.kurs - 1.0) < 0.0001) THEN 1 ELSE 0 END) as count_idr,
                    SUM(CASE WHEN NOT (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(purchase_invoices.kurs - 1.0) < 0.0001) THEN 1 ELSE 0 END) as count_foreign,
                    
                    -- Unpaid IDR
                    SUM(CASE 
                        WHEN purchase_invoices.status NOT IN ('Void', 'Rejected', 'Ditolak', 'Cancelled')
                             AND (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0)) > 0.01
                             AND (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(purchase_invoices.kurs - 1.0) < 0.0001)
                        THEN (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0))
                        ELSE 0 
                    END) as unpaid_idr,

                    -- Unpaid Foreign
                    SUM(CASE 
                        WHEN purchase_invoices.status NOT IN ('Void', 'Rejected', 'Ditolak', 'Cancelled')
                             AND (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0)) > 0.01
                             AND NOT (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(purchase_invoices.kurs - 1.0) < 0.0001)
                        THEN (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0))
                        ELSE 0 
                    END) as unpaid_foreign,

                    -- Total Unpaid Equivalent in IDR
                    SUM(CASE 
                        WHEN purchase_invoices.status NOT IN ('Void', 'Rejected', 'Ditolak', 'Cancelled')
                             AND (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0)) > 0.01
                        THEN (purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0)) * COALESCE(purchase_invoices.kurs, 1)
                        ELSE 0 
                    END) as total_unpaid_amount
                ")
                ->first();

            $totalInvoiceCount = (int) ($kpiStats->total_invoice_count ?? 0);
            $countIdr          = (int) ($kpiStats->count_idr ?? 0);
            $countForeign      = (int) ($kpiStats->count_foreign ?? 0);
            $unpaidIdr         = (float) ($kpiStats->unpaid_idr ?? 0);
            $unpaidForeign     = (float) ($kpiStats->unpaid_foreign ?? 0);
            $totalUnpaidAmount = (float) ($kpiStats->total_unpaid_amount ?? 0);

            // Simbol dan kode mata uang valas (jika ada valas belum lunas)
            $unpaidForeignSymbol = '$';
            $unpaidForeignCode   = 'USD';
            if ($unpaidForeign > 0.001) {
                $sampleForeign = (clone $baseKpiQuery)
                    ->leftJoinSub($paymentsSub, 'pay', 'purchase_invoices.id', '=', 'pay.purchase_invoice_id')
                    ->leftJoin('master_currencies as mc', 'purchase_invoices.currency_id', '=', 'mc.id')
                    ->whereNotIn('purchase_invoices.status', ['Void', 'Rejected', 'Ditolak', 'Cancelled'])
                    ->whereRaw("(purchase_invoices.grand_total - COALESCE(purchase_invoices.uang_muka, 0) - COALESCE(pay.total_bayar, 0)) > 0.01")
                    ->whereRaw("NOT (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(purchase_invoices.kurs - 1.0) < 0.0001)")
                    ->select('mc.kode_currency', 'mc.simbol')
                    ->first();
                if ($sampleForeign) {
                    $unpaidForeignCode   = $sampleForeign->kode_currency ?: 'USD';
                    $unpaidForeignSymbol = $sampleForeign->simbol ?: '$';
                }
            }

            // 3. Pembayaran Bulan Ini (Single SQL Query)
            $startOfMonth = now()->startOfMonth();
            $endOfMonth   = now()->endOfMonth();

            $paidStats = DB::table('purchase_payment_details as ppd')
                ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
                ->join('purchase_invoices as pi', 'ppd.purchase_invoice_id', '=', 'pi.id')
                ->leftJoin('master_currencies as mc', 'pi.currency_id', '=', 'mc.id')
                ->whereBetween('pp.tanggal_bayar', [$startOfMonth, $endOfMonth])
                ->whereNull('pp.deleted_at')
                ->where('pp.status', 'Posted')
                ->when($activeBranchId, fn($q) => $q->where('pp.branch_id', $activeBranchId))
                ->when(!$activeBranchId && $activeCompanyId, fn($q) => $q->where('pp.company_id', $activeCompanyId))
                ->selectRaw("
                    SUM(CASE WHEN (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(pi.kurs - 1.0) < 0.0001) THEN ppd.jumlah_bayar ELSE 0 END) as paid_idr,
                    SUM(CASE WHEN NOT (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(pi.kurs - 1.0) < 0.0001) THEN ppd.jumlah_bayar ELSE 0 END) as paid_foreign,
                    SUM(ppd.jumlah_bayar * COALESCE(pi.kurs, 1)) as total_paid_idr
                ")
                ->first();

            $paidThisMonthIdr     = (float) ($paidStats->paid_idr ?? 0);
            $paidThisMonthForeign = (float) ($paidStats->paid_foreign ?? 0);
            $totalPaidThisMonth   = (float) ($paidStats->total_paid_idr ?? 0);

            $paidThisMonthForeignSymbol = '$';
            $paidThisMonthForeignCode   = 'USD';
            if ($paidThisMonthForeign > 0.001) {
                $samplePaid = DB::table('purchase_payment_details as ppd')
                    ->join('purchase_payments as pp', 'ppd.purchase_payment_id', '=', 'pp.id')
                    ->join('purchase_invoices as pi', 'ppd.purchase_invoice_id', '=', 'pi.id')
                    ->leftJoin('master_currencies as mc', 'pi.currency_id', '=', 'mc.id')
                    ->whereBetween('pp.tanggal_bayar', [$startOfMonth, $endOfMonth])
                    ->whereNull('pp.deleted_at')
                    ->where('pp.status', 'Posted')
                    ->whereRaw("NOT (mc.kode_currency IN ('IDR', 'RUPIAH') OR mc.kode_currency IS NULL OR ABS(pi.kurs - 1.0) < 0.0001)")
                    ->select('mc.kode_currency', 'mc.simbol')
                    ->first();
                if ($samplePaid) {
                    $paidThisMonthForeignCode   = $samplePaid->kode_currency ?: 'USD';
                    $paidThisMonthForeignSymbol = $samplePaid->simbol ?: '$';
                }
            }

            $currentMonthName = \Carbon\Carbon::now()->translatedFormat('F Y');

            return compact(
                'totalInvoiceCount',
                'countIdr',
                'countForeign',
                'totalUnpaidAmount',
                'unpaidIdr',
                'unpaidForeign',
                'unpaidForeignSymbol',
                'unpaidForeignCode',
                'totalPaidThisMonth',
                'paidThisMonthIdr',
                'paidThisMonthForeign',
                'paidThisMonthForeignSymbol',
                'paidThisMonthForeignCode',
                'currentMonthName'
            );
        });
    }

    /**
     * Build base query for eligible posted Goods Receipts (GRN) that have not been invoiced yet.
     * Accurately supports both active branch and active company context.
     */
    public function queryEligibleGrs($search = null)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // GR layak ditagih: belum punya faktur aktif (faktur Dibatalkan/Void tidak menghalangi tagih ulang)
        // dan PO-nya belum ditagih langsung (faktur PO tanpa GR) agar tidak terjadi tagihan ganda.
        $query = GoodsReceipt::where('status', 'Posted')
            ->whereDoesntHave('purchaseInvoice', function ($q) {
                $q->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan']);
            })
            ->whereDoesntHave('po.purchaseInvoices', function ($q) {
                $q->whereNull('gr_id')->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan']);
            });

        if (!empty($activeBranchId)) {
            $query->where(function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId)
                  ->orWhereHas('po', fn($pq) => $pq->where('branch_id', $activeBranchId));
            });
        } elseif (!empty($activeCompanyId)) {
            $query->where(function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)
                  ->orWhereHas('po', fn($pq) => $pq->where('company_id', $activeCompanyId))
                  ->orWhereHas('branch', fn($bq) => $bq->where('company_id', $activeCompanyId));
            });
        }

        if (!empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_gr', 'like', "%{$search}%")
                  ->orWhereHas('po', function ($pq) use ($search) {
                      $pq->where('nomor_po', 'like', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('nama_supplier', 'like', "%{$search}%");
                         });
                  })
                  ->orWhereHas('gudang', function ($gq) use ($search) {
                      $gq->where('nama_gudang', 'like', "%{$search}%");
                  });
            });
        }

        return $query->with(['po.supplier', 'po.currency', 'po.tender', 'tender', 'details.barang', 'gudang'])
            ->orderByDesc('tanggal_gr')
            ->orderByDesc('id');
    }

    /**
     * AJAX endpoint to retrieve eligible posted GRNs with real-time live search.
     */
    public function getEligibleGrs(Request $request)
    {
        if (!$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat data ini.'], 403);
        }

        $search = $request->get('q');
        $grs = $this->queryEligibleGrs($search)->limit(50)->get();

        $data = $grs->map(function ($gr) {
            $cur = $gr->po?->currency;
            $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
            $curName = $cur?->nama_currency ?? ($cur?->name ?? $curCode);
            $curSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : 'Rp'));
            $kurs = ($gr->kurs && floatval($gr->kurs) > 0)
                ? number_format($gr->kurs, 4, '.', '')
                : ($gr->po?->kurs ? number_format($gr->po->kurs, 4, '.', '') : '1.0000');

            $tenderObj = $gr->tender ?? $gr->po?->tender;
            $nomorTender = null;
            if ($tenderObj) {
                $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
            }

            return [
                'id'              => $gr->id,
                'nomor_gr'        => $gr->nomor_gr,
                'tanggal_gr'      => $gr->tanggal_gr,
                'tanggal_gr_fmt'  => $gr->tanggal_gr ? date('d/m/Y', strtotime($gr->tanggal_gr)) : '-',
                'supplier_name'   => $gr->po?->supplier?->nama_supplier ?? '-',
                'supplier_npwp'   => $gr->po?->supplier?->npwp ?? '',
                'gudang_name'     => $gr->gudang?->nama_gudang ?? '-',
                'nomor_po'        => $gr->po?->nomor_po ?? '-',
                'currency_label'  => $curCode . ($curName ? " ({$curName})" : ''),
                'currency_code'   => $curCode,
                'currency_symbol' => $curSymbol,
                'kurs'            => $kurs,
                'payment_term_id' => $gr->po?->payment_term_id,
                'pajak_id'        => $gr->po?->pajak_id,
                'tender_id'       => $tenderObj?->id,
                'nomor_tender'    => $nomorTender,
                'nama_tender'     => $tenderObj?->nama_tender,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Build base query for eligible approved Purchase Orders (PO) that can be invoiced directly.
     */
    public function queryEligiblePos($search = null)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $query = PurchaseOrder::whereIn('status', ['Approved', 'Disetujui', 'Ordered', 'Partially Received', 'Confirmed', 'Diproses'])
            ->whereDoesntHave('purchaseInvoices', function ($q) {
                // Faktur Ditolak masih bisa diperbaiki & diajukan ulang, jadi tetap menghalangi tagihan baru
                $q->whereNotIn('status', ['Cancelled', 'Dibatalkan', 'Void']);
            });

        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } elseif (!empty($activeCompanyId)) {
            $query->where(function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)
                  ->orWhereHas('branch', fn($bq) => $bq->where('company_id', $activeCompanyId));
            });
        }

        if (!empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('nama_supplier', 'like', "%{$search}%"));
            });
        }

        return $query->with(['supplier', 'currency', 'pajak', 'paymentTerm', 'tender', 'details.barang.satuan', 'details.satuan', 'details.grDetails.goodsReceipt'])
            ->orderByDesc('tanggal_po')
            ->orderByDesc('id');
    }

    /**
     * AJAX endpoint to retrieve eligible POs with real-time live search.
     */
    public function getEligiblePos(Request $request)
    {
        if (!$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat data ini.'], 403);
        }

        $search = $request->get('q');
        $pos = $this->queryEligiblePos($search)->limit(50)->get();

        $data = $pos->map(function ($po) {
            $cur = $po->currency;
            $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
            $curName = $cur?->nama_currency ?? ($cur?->name ?? $curCode);
            $curSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : 'Rp'));
            $kurs = ($po->kurs && floatval($po->kurs) > 0)
                ? number_format($po->kurs, 4, '.', '')
                : '1.0000';

            $tenderObj = $po->tender;
            $nomorTender = null;
            if ($tenderObj) {
                $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
            }

            $totalQtyOrdered = floatval($po->details->sum('jumlah'));
            $totalQtyReceived = floatval($po->details->sum(function($d) {
                return $d->grDetails ? $d->grDetails->where('goodsReceipt.status', '!=', 'Dibatalkan')->sum('jumlah') : 0;
            }));

            $receiptStatus = 'none';
            $receiptLabel = 'Belum Ada Penerimaan (0 / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
            $receiptBadgeClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle';
            $receiptIcon = 'bi-hourglass';

            if ($totalQtyReceived > 0 && $totalQtyReceived < $totalQtyOrdered) {
                $receiptStatus = 'partial';
                $receiptLabel = 'Diterima Sebagian (' . number_format($totalQtyReceived, 0, ',', '.') . ' / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
                $receiptBadgeClass = 'bg-info bg-opacity-10 text-info border border-info-subtle';
                $receiptIcon = 'bi-pie-chart-fill';
            } elseif ($totalQtyReceived >= $totalQtyOrdered && $totalQtyOrdered > 0) {
                $receiptStatus = 'full';
                $receiptLabel = 'Diterima Lengkap (' . number_format($totalQtyReceived, 0, ',', '.') . ' / ' . number_format($totalQtyOrdered, 0, ',', '.') . ')';
                $receiptBadgeClass = 'bg-success bg-opacity-10 text-success border border-success-subtle';
                $receiptIcon = 'bi-check2-circle';
            }

            return [
                'id'                  => $po->id,
                'nomor_po'            => $po->nomor_po,
                'tanggal_po'          => $po->tanggal_po,
                'tanggal_po_fmt'      => $po->tanggal_po ? date('d/m/Y', strtotime($po->tanggal_po)) : '-',
                'supplier_name'       => $po->supplier?->nama_supplier ?? '-',
                'supplier_npwp'       => $po->supplier?->npwp ?? '',
                'grand_total'         => floatval($po->grand_total ?? 0),
                'grand_total_fmt'     => $curSymbol . ' ' . number_format($po->grand_total ?? 0, 2, ',', '.'),
                'status'              => $po->status,
                'currency_label'      => $curCode . ($curName ? " ({$curName})" : ''),
                'currency_code'       => $curCode,
                'currency_symbol'     => $curSymbol,
                'kurs'                => $kurs,
                'payment_term_id'     => $po->payment_term_id,
                'pajak_id'            => $po->pajak_id,
                'total_items'         => $po->details->count(),
                'total_qty_ordered'   => $totalQtyOrdered,
                'total_qty_received'  => $totalQtyReceived,
                'receipt_status'      => $receiptStatus,
                'receipt_label'       => $receiptLabel,
                'receipt_badge_class' => $receiptBadgeClass,
                'receipt_icon'        => $receiptIcon,
                'tender_id'           => $tenderObj?->id,
                'nomor_tender'        => $nomorTender,
                'nama_tender'         => $tenderObj?->nama_tender,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function getGrDetailForInvoice(GoodsReceipt $gr)
    {
        if (!$this->canCreateInvoice() && !$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat data ini.'], 403);
        }

        $user = auth()->user();
        $activeBranchId = session('active_branch_id');
        if ($user && !$user->hasAnyRole(['Super Admin', 'Owner']) && !empty($activeBranchId) && !empty($gr->branch_id) && $gr->branch_id != $activeBranchId) {
            return response()->json(['message' => 'Dokumen GRN ini tidak berada pada cabang aktif Anda.'], 403);
        }
        $gr->load([
            'po.supplier',
            'po.currency',
            'po.pajak',
            'po.paymentTerm',
            'po.tender',
            'po.details',
            'tender',
            'gudang',
            'details.barang.satuan',
            'details.barang.kategori',
            'details.poDetail.ppnPajak'
        ]);

        $tenderObj = $gr->tender ?? $gr->po?->tender;
        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
        }

        // Pre-resolve COA for each item category
        $defaultInvCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_inventory');
        foreach ($gr->details as $detail) {
            $kategoriId = $detail->barang?->kategori_barang_id;
            $invCoaId = null;
            if ($kategoriId) {
                $invCoaId = \App\Helpers\AccountMappingHelper::getCoaId('inventory_category', "cat_{$kategoriId}_inventory", 'default_inventory');
            }
            if (!$invCoaId) {
                $invCoaId = $defaultInvCoaId;
            }
            $detail->resolved_coa_id = $invCoaId;
        }

        $proratedData = $this->resolveProratedBiayaLainAndDp($gr->po, $gr);

        return response()->json([
            'gr'                        => $gr,
            'tender_id'                 => $tenderObj?->id,
            'nomor_tender'              => $nomorTender,
            'tender_nama'               => $tenderObj?->nama_tender,
            'biaya_lain_details'        => $proratedData['biaya_lain_details'],
            'down_payment'              => $proratedData['down_payment'],
            'default_uang_muka_coa_id'  => $this->resolveDefaultUangMukaCoaId(),
            'tracking'                  => $proratedData['tracking'],
        ]);
    }

    public function getPoDetailForInvoice(PurchaseOrder $po)
    {
        if (!$this->canCreateInvoice() && !$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat data ini.'], 403);
        }

        $user = auth()->user();
        $activeBranchId = session('active_branch_id');
        if ($user && !$user->hasAnyRole(['Super Admin', 'Owner']) && !empty($activeBranchId) && !empty($po->branch_id) && $po->branch_id != $activeBranchId) {
            return response()->json(['message' => 'Dokumen PO ini tidak berada pada cabang aktif Anda.'], 403);
        }
        $po->load([
            'supplier',
            'currency',
            'pajak',
            'pphPajak',
            'paymentTerm',
            'tender',
            'details.barang.satuan',
            'details.barang.kategori',
            'details.satuan',
            'details.ppnPajak',
            'details.grDetails.goodsReceipt'
        ]);

        $tenderObj = $po->tender;
        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
        }

        // Pre-resolve COA for each item category & calculate received quantity
        $defaultInvCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_inventory');
        foreach ($po->details as $detail) {
            $qtyOrdered = floatval($detail->jumlah);
            $qtyReceived = floatval($detail->grDetails ? $detail->grDetails->where('goodsReceipt.status', '!=', 'Dibatalkan')->sum('jumlah') : 0);
            $detail->qty_ordered = $qtyOrdered;
            $detail->qty_received = $qtyReceived;
            $detail->qty_remaining = max(0, $qtyOrdered - $qtyReceived);

            $kategoriId = $detail->barang?->kategori_barang_id;
            $invCoaId = null;
            if ($kategoriId) {
                $invCoaId = \App\Helpers\AccountMappingHelper::getCoaId('inventory_category', "cat_{$kategoriId}_inventory", 'default_inventory');
            }
            if (!$invCoaId) {
                $invCoaId = $defaultInvCoaId;
            }
            $detail->resolved_coa_id = $invCoaId;
        }

        $proratedData = $this->resolveProratedBiayaLainAndDp($po, null);

        return response()->json([
            'po'                        => $po,
            'tender_id'                 => $tenderObj?->id,
            'nomor_tender'              => $nomorTender,
            'tender_nama'               => $tenderObj?->nama_tender,
            'biaya_lain_details'        => $proratedData['biaya_lain_details'],
            'down_payment'              => $proratedData['down_payment'],
            'default_uang_muka_coa_id'  => $this->resolveDefaultUangMukaCoaId(),
            'tracking'                  => $proratedData['tracking'],
        ]);
    }

    /**
     * Hitung alokasi biaya pengiriman / biaya tambahan & uang muka secara proporsional (pro-rata)
     * saat pengiriman parsial (GR parsial), serta melacak sisa kuota PO agar tidak pernah ditagih dobel.
     */
    protected function resolveProratedBiayaLainAndDp(?PurchaseOrder $po, ?GoodsReceipt $gr = null, ?int $excludeInvoiceId = null): array
    {
        if (!$po) {
            return [
                'biaya_lain_details' => [],
                'down_payment'       => 0,
                'tracking'           => [
                    'is_partial'      => false,
                    'ratio'           => 1.0,
                    'gr_ratio_pct'    => 100,
                    'po_subtotal_net' => 0,
                    'gr_subtotal_net' => 0,
                    'po_dp'           => 0,
                    'claimed_dp'      => 0,
                    'remaining_dp'    => 0,
                ],
            ];
        }

        // 1. Hitung Subtotal Net PO
        $poSubtotalNet = 0;
        if ($po->relationLoaded('details') || $po->details) {
            foreach ($po->details as $pod) {
                $podGross = floatval($pod->jumlah) * floatval($pod->harga_satuan);
                $podDiskon = floatval($pod->jumlah) * floatval($pod->diskon_item ?? 0);
                $poSubtotalNet += max(0, $podGross - $podDiskon);
            }
        }
        $poSubtotalNet = round($poSubtotalNet, 2);

        // 2. Hitung Subtotal Net GR & Cek Pengiriman Parsial
        $grSubtotalNet = 0;
        $ratio = 1.0;
        $isFull = true;

        if ($gr && ($gr->relationLoaded('details') || $gr->details)) {
            foreach ($gr->details as $grd) {
                $qty = floatval($grd->jumlah);
                $price = floatval($grd->poDetail?->harga_satuan ?? $grd->harga_satuan ?? 0);
                $diskon = floatval($grd->poDetail?->diskon_item ?? 0);
                $grSubtotalNet += max(0, ($qty * $price) - ($qty * $diskon));
            }
            $grSubtotalNet = round($grSubtotalNet, 2);

            $ratio = ($poSubtotalNet > 0) ? min(1.0, max(0.0, $grSubtotalNet / $poSubtotalNet)) : 1.0;

            // Cek apakah seluruh kuantitas PO sudah selesai diterima pada GR ini (last GR)
            $totalPoQty = $po->details ? $po->details->sum('jumlah') : 0;
            $totalReceivedQty = GoodsReceiptDetail::whereHas('goodsReceipt', function($q) use ($po) {
                $q->where('po_id', $po->id)->whereNotIn('status', ['Dibatalkan', 'Cancelled']);
            })->sum('jumlah');

            $isLastGr = ($totalPoQty > 0 && $totalReceivedQty >= ($totalPoQty - 0.0001));
            $isFull = ($ratio >= 0.9995) || $isLastGr;
        }

        // 3. Ambil seluruh faktur aktif sebelumnya dari PO yang sama
        $invQuery = PurchaseInvoice::where('po_id', $po->id)
            ->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan']);
        if ($excludeInvoiceId) {
            $invQuery->where('id', '!=', $excludeInvoiceId);
        }
        $existingInvoices = $invQuery->with('details')->get();

        // 4. Hitung Pro-Rata & Sisa Kuota Uang Muka (DP)
        $poDp = floatval($po->down_payment ?? 0);
        $claimedDp = floatval($existingInvoices->sum('uang_muka'));
        $remainingDp = max(0, round($poDp - $claimedDp, 2));

        $proRataDp = round($poDp * $ratio, 2);
        $effectiveDp = ($isFull || $proRataDp > $remainingDp) ? $remainingDp : min($remainingDp, $proRataDp);

        // 5. Hitung Pro-Rata & Sisa Kuota Biaya Tambahan (Ongkir dll)
        $rawBiayaLain = $this->resolveBiayaLainFromPo($po);
        $allPastExtraCosts = $existingInvoices->flatMap->details->filter(function($d) {
            return empty($d->barang_id) && $d->jenis !== 'PPN';
        });

        $proratedBiayaLain = [];
        foreach ($rawBiayaLain as $item) {
            $jenisName = trim(strval($item['jenis'] ?? $item['keterangan'] ?? 'Biaya Tambahan'));
            $poNominal = floatval($item['nominal'] ?? 0);

            // Cari histori nominal yang sudah ditagihkan untuk jenis biaya ini
            $alreadyBilled = floatval($allPastExtraCosts->filter(function($d) use ($jenisName) {
                return strcasecmp(trim(strval($d->jenis)), $jenisName) === 0
                    || strcasecmp(trim(strval($d->keterangan)), $jenisName) === 0;
            })->sum('subtotal'));

            // Fallback jika tidak ada record detail terpisah tapi ada header biaya_lain
            if ($alreadyBilled <= 0 && $allPastExtraCosts->isEmpty()) {
                $alreadyBilled = floatval($existingInvoices->sum('biaya_lain'));
            }

            $remainingUnbilled = max(0, round($poNominal - $alreadyBilled, 2));
            $proRataNominal = round($poNominal * $ratio, 2);

            $effectiveNominal = ($isFull || $proRataNominal > $remainingUnbilled)
                ? $remainingUnbilled
                : min($remainingUnbilled, $proRataNominal);

            $item['po_original_nominal'] = $poNominal;
            $item['already_billed']       = $alreadyBilled;
            $item['remaining_unbilled']   = $remainingUnbilled;
            $item['nominal']              = $effectiveNominal;
            $item['is_partial']           = !$isFull;
            $item['ratio']                = $ratio;
            $item['gr_ratio_pct']         = round($ratio * 100, 1);

            $proratedBiayaLain[] = $item;
        }

        return [
            'biaya_lain_details' => $proratedBiayaLain,
            'down_payment'       => $effectiveDp,
            'tracking'           => [
                'is_partial'      => !$isFull,
                'ratio'           => $ratio,
                'gr_ratio_pct'    => round($ratio * 100, 1),
                'po_subtotal_net' => $poSubtotalNet,
                'gr_subtotal_net' => $grSubtotalNet,
                'po_dp'           => $poDp,
                'claimed_dp'      => $claimedDp,
                'remaining_dp'    => $remainingDp,
                'effective_dp'    => $effectiveDp,
            ]
        ];
    }

    /**
     * Extract and normalize biaya_lain_details from a PO for pre-filling in the invoice form.
     * Returns array of ['keterangan'=>..., 'nominal'=>..., 'masuk_hpp'=>..., 'coa_id'=>...]
     */
    /**
     * Extract and normalize biaya_lain_details from a PO for pre-filling in the invoice form.
     * Returns array of ['jenis'=>..., 'keterangan'=>..., 'nominal'=>..., 'masuk_hpp'=>..., 'coa_id'=>...]
     */
    protected function resolveBiayaLainFromPo($po): array
    {
        if (!$po) return [];

        $expenseTypes = \App\Models\MasterJenisBiaya::forActiveCompany()->active()->get();

        $details = $po->biaya_lain_details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        if (is_array($details) && count($details) > 0) {
            return array_map(function ($item) use ($expenseTypes) {
                $isHpp = isset($item['masuk_hpp'])
                    ? (in_array($item['masuk_hpp'], [1, '1', true, 'true', 'HPP'], true) ? 1 : 0)
                    : (isset($item['alokasi']) && strtoupper($item['alokasi']) === 'HPP' ? 1 : 0);

                // 1. Resolve MasterJenisBiaya by master_jenis_biaya_id first
                $jbId = $item['master_jenis_biaya_id'] ?? null;
                $matched = null;
                if ($jbId) {
                    $matched = $expenseTypes->firstWhere('id', $jbId)
                        ?? \App\Models\MasterJenisBiaya::find($jbId);
                }

                // 2. Resolve nama jenis biaya
                $jenisNama = (!empty(trim(strval($item['nama_jenis_biaya'] ?? ''))))
                    ? trim(strval($item['nama_jenis_biaya']))
                    : ($matched?->nama_jenis_biaya
                        ?? (!empty(trim(strval($item['jenis_biaya'] ?? ''))) ? trim(strval($item['jenis_biaya'])) : null)
                        ?? (!empty(trim(strval($item['jenis'] ?? ''))) ? trim(strval($item['jenis'])) : null)
                        ?? (!empty(trim(strval($item['nama'] ?? ''))) ? trim(strval($item['nama'])) : null)
                        ?? 'Biaya Lain-lain');

                // If not matched by ID, try matching by name
                if (!$matched && !empty($jenisNama) && $jenisNama !== 'Biaya Lain-lain') {
                    $matched = $expenseTypes->first(function($et) use ($jenisNama) {
                        return strtolower(trim($et->nama_jenis_biaya)) === strtolower(trim($jenisNama));
                    });
                }

                // If masuk_hpp was not explicitly defined, inherit from MasterJenisBiaya
                if (!isset($item['masuk_hpp']) && $matched) {
                    $isHpp = $matched->masuk_hpp ? 1 : 0;
                }

                $ket = !empty(trim(strval($item['keterangan'] ?? '')))
                    ? trim(strval($item['keterangan']))
                    : (!empty(trim(strval($item['deskripsi'] ?? ''))) ? trim(strval($item['deskripsi'])) : $jenisNama);

                // 3. Resolve COA ID from item, master, or intelligent fallback
                $coaId = $item['coa_id'] ?? $matched?->coa_id ?? null;
                if (!$coaId && !empty($jenisNama)) {
                    $coaId = $this->detectFallbackCoaForExpense($jenisNama, $isHpp);
                }

                return [
                    'master_jenis_biaya_id' => $jbId,
                    'jenis'                 => $jenisNama,
                    'keterangan'            => $ket,
                    'nominal'               => floatval($item['nominal'] ?? $item['jumlah'] ?? $item['biaya'] ?? 0),
                    'masuk_hpp'             => $isHpp,
                    'coa_id'                => $coaId,
                ];
            }, array_values($details));
        }

        // Fallback: single biaya_lain header field
        if (floatval($po->biaya_lain) > 0) {
            $isHpp = strtoupper(trim(strval($po->alokasi_biaya_lain ?? ''))) === 'HPP' ? 1 : 0;
            $ket = $po->deskripsi_biaya_lain ?? 'Biaya Lain-lain';
            $matched = $expenseTypes->first(function($et) use ($ket) {
                return strtolower(trim($et->nama_jenis_biaya)) === strtolower(trim($ket));
            });
            $coaId = $matched?->coa_id ?? $this->detectFallbackCoaForExpense($ket, $isHpp);
            return [[
                'master_jenis_biaya_id' => $matched?->id ?? null,
                'jenis'                 => $matched?->nama_jenis_biaya ?? $ket,
                'keterangan'            => $ket,
                'nominal'               => floatval($po->biaya_lain),
                'masuk_hpp'             => $isHpp,
                'coa_id'                => $coaId,
            ]];
        }

        return [];
    }

    /**
     * Fallback intelligent detection for Expense COA based on keywords.
     */
    protected function detectFallbackCoaForExpense(string $jenis, int $isHpp = 0): ?int
    {
        $j = strtolower(trim($jenis));
        if (str_contains($j, 'kirim') || str_contains($j, 'ongkir') || str_contains($j, 'freight') || str_contains($j, 'transport') || str_contains($j, 'antar') || str_contains($j, 'ekspedisi') || str_contains($j, 'bongkar') || str_contains($j, 'muat') || str_contains($j, 'handling')) {
            return ChartOfAccount::where('code', '5103')->value('id')
                ?? ChartOfAccount::where('name', 'like', '%Biaya Angkut Pembelian%')->value('id')
                ?? ChartOfAccount::where('name', 'like', '%Freight%')->value('id');
        }
        if (str_contains($j, 'admin') || str_contains($j, 'bank') || str_contains($j, 'provisi') || str_contains($j, 'transfer')) {
            return ChartOfAccount::where('code', '8101')->value('id')
                ?? ChartOfAccount::where('code', '6105')->value('id')
                ?? ChartOfAccount::where('name', 'like', '%Administrasi Bank%')->value('id');
        }
        if (str_contains($j, 'asuransi') || str_contains($j, 'insurance')) {
            return ChartOfAccount::where('code', 'like', '6103%')->value('id')
                ?? ChartOfAccount::where('name', 'like', '%Asuransi%')->value('id');
        }
        return ChartOfAccount::where('code', '5103')->value('id')
            ?? ChartOfAccount::where('name', 'like', '%Biaya Angkut Pembelian%')->value('id');
    }

    /**
     * Resolve default COA "Utang PPh" (liability) untuk PPh yang dipotong atas pembelian.
     */
    protected function resolveDefaultPphCoaId(): ?int
    {
        return \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_pph_payable')
            ?? ChartOfAccount::where('is_header', 0)->where(function ($q) {
                $q->where('name', 'like', '%Utang PPh%')
                  ->orWhere('name', 'like', '%Hutang PPh%')
                  ->orWhere('name', 'like', '%PPh 23%')
                  ->orWhere('name', 'like', '%PPh Pasal 23%');
            })->value('id')
            ?? ChartOfAccount::where('is_header', 0)->where('name', 'like', '%PPh%')->value('id');
    }

    /**
     * Resolve default COA "Uang Muka Pembelian" (Asset) untuk DP ke Supplier.
     */
    protected function resolveDefaultUangMukaCoaId(): ?int
    {
        return AccountMappingHelper::getCoaId('default', 'supplier_down_payment', 'default_supplier_down_payment')
            ?? ChartOfAccount::where('is_header', 0)->where(function ($q) {
                $q->where('name', 'like', '%Uang Muka Pembelian%')
                  ->orWhere('name', 'like', '%DP Supplier%')
                  ->orWhere('code', 'like', '1111%');
            })->value('id')
            ?? 208;
    }

    /**
     * Normalize PPh input from request.
     * Nominal manual dipakai bila diisi, jika tidak dihitung otomatis = DPP x tarif.
     *
     * @return array{pajak_id: ?int, tarif: float, nominal: float, coa_id: ?int}
     */
    protected function resolvePphFromRequest(Request $request, float $dpp): array
    {
        $pajakId = $request->filled('pph_pajak_id') ? (int) $request->pph_pajak_id : null;
        if (!$pajakId) {
            return ['pajak_id' => null, 'tarif' => 0.0, 'nominal' => 0.0, 'coa_id' => null];
        }

        $pajak = MasterPajak::find($pajakId);
        $tarif = $request->filled('tarif_pph')
            ? $this->unformatNumber($request->tarif_pph)
            : floatval($pajak?->tarif ?? 0);

        $nominal = $request->filled('pph_nominal')
            ? $this->unformatNumber($request->pph_nominal)
            : round($dpp * ($tarif / 100), 2);
        $nominal = max(0, round($nominal, 2));

        $coaId = $request->filled('pph_coa_id')
            ? (int) $request->pph_coa_id
            : (\App\Helpers\AccountMappingHelper::getCoaId('tax', "tax_{$pajakId}_in") ?? $this->resolveDefaultPphCoaId());

        return [
            'pajak_id' => $pajakId,
            'tarif'    => $tarif,
            'nominal'  => $nominal,
            'coa_id'   => $coaId,
        ];
    }

    /**
     * Parse Indonesian / US / localized number string into standard float with extreme precision.
     * Accurately supports both Indonesian (1.250.000,50) and US / Valas Dollar (1,250,000.50) formats.
     */
    private function unformatNumber($val): float
    {
        if (is_int($val) || is_float($val)) {
            return floatval($val);
        }
        if (empty($val)) {
            return 0.0;
        }
        $s = trim(strval($val));
        // Strip out currency codes, symbols, and spaces except digits, separators, minus
        $s = preg_replace('/[^\d.,-]/', '', $s);
        if ($s === '' || $s === '-') {
            return 0.0;
        }

        $hasComma = str_contains($s, ',');
        $hasDot   = str_contains($s, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($s, ',');
            $lastDot   = strrpos($s, '.');
            if ($lastDot > $lastComma) {
                // US / Dollar format: comma is thousands, dot is decimal (e.g. 1,250.50 or 1,250,000.00)
                $s = str_replace(',', '', $s);
            } else {
                // Indonesian / EU format: dot is thousands, comma is decimal (e.g. 1.250,50 or 1.250.000,00)
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            }
        } elseif ($hasComma) {
            // Only comma present -> decimal comma in Indonesian ERP (e.g. 1250,50 or 0,00)
            $s = str_replace(',', '.', $s);
        } elseif ($hasDot) {
            // Only dot present:
            // Check if it's multiple dots (e.g. 1.000.000) or exact 3-digit thousands (e.g. 50.000)
            if (substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            } elseif (preg_match('/^\d{1,3}\.\d{3}$/', $s)) {
                // Exactly 3 digits like 50.000 -> Indonesian thousands separator
                $s = str_replace('.', '', $s);
            }
            // Otherwise, keep dot as standard decimal point (e.g. 1234.56, 15.5)
        } elseif (is_numeric($s)) {
            return floatval($s);
        }

        return floatval($s);
    }



    public function store(Request $request)
    {
        if (!$this->canCreateInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk membuat Faktur Pembelian.'], 403);
        }

        // Normalize formatted inputs (e.g. "40.200,00" -> 40200.00)
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $item) {
                if (isset($item['qty'])) {
                    $items[$k]['qty'] = $this->unformatNumber($item['qty']);
                }
                if (isset($item['harga_satuan'])) {
                    $items[$k]['harga_satuan'] = $this->unformatNumber($item['harga_satuan']);
                }
                if (isset($item['diskon_item'])) {
                    $items[$k]['diskon_item'] = $this->unformatNumber($item['diskon_item']);
                }
            }
            $request->merge(['items' => $items]);
        }

        if ($request->has('extra_costs') && is_array($request->extra_costs)) {
            $extras = $request->extra_costs;
            foreach ($extras as $k => $extra) {
                if (isset($extra['nominal'])) {
                    $extras[$k]['nominal'] = $this->unformatNumber($extra['nominal']);
                }
            }
            $request->merge(['extra_costs' => $extras]);
        }

        if ($request->filled('kurs')) {
            $request->merge(['kurs' => $this->unformatNumber($request->kurs)]);
        }

        $uangMukaCheck = 0;
        if ($request->filled('uang_muka')) {
            $uangMukaCheck = $this->unformatNumber($request->uang_muka);
            $request->merge(['uang_muka' => $uangMukaCheck]);
        }

        $request->validate([
            'source_type'           => 'nullable|string|in:gr,po',
            'gr_id'                 => 'nullable|required_without:po_id|exists:goods_receipts,id',
            'po_id'                 => 'nullable|required_without:gr_id|exists:purchase_orders,id',
            'nomor_faktur_supplier' => 'required|string|max:100',
            'keterangan'            => 'nullable|string|max:1000',
            'tanggal_faktur'        => 'required|date',
            'tanggal_jatuh_tempo'   => 'required|date',
            'payment_term_id'       => 'nullable|exists:payment_terms,id',
            'pajak_id'              => 'nullable|exists:master_pajaks,id',
            'pph_pajak_id'          => 'nullable|exists:master_pajaks,id',
            'pph_coa_id'            => 'nullable|exists:chart_of_accounts,id',
            'biaya_lain'            => 'nullable|numeric|min:0',
            'uang_muka'             => 'nullable|numeric|min:0',
            'uang_muka_coa_id'      => 'nullable|exists:chart_of_accounts,id',
            'kas_bank_id'           => ($uangMukaCheck > 0.005) ? 'required|exists:kas_banks,id' : 'nullable|exists:kas_banks,id',
            'lampiran_faktur'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'items'                 => 'required|array|min:1',
            'items.*.gr_detail_id'  => 'nullable|exists:goods_receipt_details,id',
            'items.*.po_detail_id'  => 'nullable|exists:purchase_order_details,id',
            'items.*.qty'           => 'nullable|numeric|min:0.0001',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
        ], [
            'gr_id.required_without' => 'Dokumen Penerimaan Barang (GRN) atau Purchase Order (PO) wajib dipilih.',
            'po_id.required_without' => 'Dokumen Penerimaan Barang (GRN) atau Purchase Order (PO) wajib dipilih.',
            'nomor_faktur_supplier.required' => 'Nomor Faktur dari Supplier wajib diisi.',
            'nomor_faktur_supplier.max'      => 'Nomor Faktur dari Supplier maksimal 100 karakter.',
            'keterangan.max'                 => 'Catatan / Keterangan Faktur maksimal 1000 karakter.',
            'tanggal_jatuh_tempo.required'   => 'Tanggal Jatuh Tempo wajib diisi.',
            'tanggal_jatuh_tempo.date'       => 'Format Tanggal Jatuh Tempo tidak valid.',
            'kas_bank_id.required'           => 'Rekening Kas / Bank wajib dipilih untuk memotong saldo pengeluaran Uang Muka (DP).',
            'kas_bank_id.exists'             => 'Rekening Kas / Bank yang dipilih tidak valid atau tidak terdaftar.',
            'lampiran_faktur.mimes'          => 'Format foto/dokumen bukti harus JPG, JPEG, PNG, atau PDF.',
            'lampiran_faktur.max'            => 'Ukuran foto/dokumen bukti maksimal 5 MB.',
        ]);

        try {
            DB::beginTransaction();

            if ($request->filled('gr_id')) {
                $gr = GoodsReceipt::with(['po.supplier', 'po.currency'])->findOrFail($request->gr_id);
                $po = $gr->po;
                $activeBranchId  = session('active_branch_id') ?? $gr->branch_id ?? $po?->branch_id;
                $activeCompanyId = session('active_company_id') ?? $gr->company_id ?? $po?->company_id;
                $poId            = $gr->po_id;
                $grId            = $gr->id;
                $supplierId      = $po?->supplier_id;
                $currencyId      = $po?->currency_id;
                $sourceKurs      = ($gr->kurs && floatval($gr->kurs) > 0)
                    ? floatval($gr->kurs)
                    : ($po?->kurs ? floatval($po->kurs) : 1.0);
            } else {
                $po = PurchaseOrder::with(['supplier', 'currency'])->findOrFail($request->po_id);
                $gr = null;
                $activeBranchId  = session('active_branch_id') ?? $po->branch_id;
                $activeCompanyId = session('active_company_id') ?? $po->company_id;
                $poId            = $po->id;
                $grId            = null;
                $supplierId      = $po->supplier_id;
                $currencyId      = $po->currency_id;
                $sourceKurs      = ($po->kurs && floatval($po->kurs) > 0) ? floatval($po->kurs) : 1.0;
            }

            // ── Guard Anti Tagihan Ganda (server-side, dengan row lock untuk mencegah race / double submit) ──
            if ($grId) {
                GoodsReceipt::whereKey($grId)->lockForUpdate()->first();
                $dupInvoice = PurchaseInvoice::where('gr_id', $grId)
                    ->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan'])
                    ->first();
                if (!$dupInvoice && $poId) {
                    $dupInvoice = PurchaseInvoice::where('po_id', $poId)->whereNull('gr_id')
                        ->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan'])
                        ->first();
                }
            } else {
                PurchaseOrder::whereKey($poId)->lockForUpdate()->first();
                $dupInvoice = PurchaseInvoice::where('po_id', $poId)
                    ->whereNotIn('status', ['Cancelled', 'Void', 'Dibatalkan'])
                    ->first();
            }
            if ($dupInvoice) {
                DB::rollBack();
                return response()->json([
                    'message' => "Dokumen ini sudah ditagihkan pada Faktur {$dupInvoice->nomor_faktur} (status: {$dupInvoice->status}). Batalkan faktur tersebut terlebih dahulu bila ingin menagih ulang."
                ], 422);
            }

            // Generate Invoice Number (Custom Manual or Auto System INV/YYYY/MM/XXXX)
            if ($request->filled('nomor_faktur') && trim($request->nomor_faktur) !== '' && !str_contains($request->nomor_faktur, 'Otomatis')) {
                $nomorFaktur = trim($request->nomor_faktur);
            } else {
                $year   = date('Y', strtotime($request->tanggal_faktur));
                $month  = date('m', strtotime($request->tanggal_faktur));
                $prefix = "INV/{$year}/{$month}/";
                
                $latestInv = PurchaseInvoice::withTrashed()
                    ->where('nomor_faktur', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->first();

                $nextNum = 1;
                if ($latestInv && preg_match('/(\d+)$/', $latestInv->nomor_faktur, $matches)) {
                    $nextNum = ((int) $matches[1]) + 1;
                }

                $nomorFaktur = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                while (PurchaseInvoice::withTrashed()->where('nomor_faktur', $nomorFaktur)->exists()) {
                    $nextNum++;
                    $nomorFaktur = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                }
            }

            // Calculate Due Date (prioritize user input, fallback to term calculation)
            $dueDate = $request->filled('tanggal_jatuh_tempo') ? $request->tanggal_jatuh_tempo : $request->tanggal_faktur;
            if (!$request->filled('tanggal_jatuh_tempo') && $request->payment_term_id) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari > 0) {
                    $dueDate = date('Y-m-d', strtotime($request->tanggal_faktur . " +{$term->jumlah_hari} days"));
                }
            }

            // Calculate Subtotal Gross, Diskon, Net & PPN Items (Barang)
            $subtotalBarangGross = 0;
            $totalDiskon = 0;
            $totalPpnNominal = 0;
            $itemsPayload = [];
            $firstPajakId = null;

            foreach ($request->items as $itemData) {
                $grDetail = !empty($itemData['gr_detail_id']) ? GoodsReceiptDetail::find($itemData['gr_detail_id']) : null;
                $poDetail = !empty($itemData['po_detail_id']) ? PurchaseOrderDetail::find($itemData['po_detail_id']) : ($grDetail?->poDetail);

                if (!$grDetail && !$poDetail) continue;

                $hargaGross = floatval($itemData['harga_satuan']);
                $maxAllowedQty = $grDetail ? floatval($grDetail->jumlah) : floatval($poDetail->jumlah);
                $inputQty = isset($itemData['qty']) ? floatval($itemData['qty']) : $maxAllowedQty;
                $namaBarang = $grDetail ? $grDetail->barang?->nama_barang : ($poDetail->barang?->nama_barang ?? $poDetail->keterangan);

                if ($inputQty <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Jumlah Qty untuk barang \"{$namaBarang}\" harus lebih dari 0."
                    ], 422);
                }

                if ($inputQty > $maxAllowedQty + 0.0001) {
                    DB::rollBack();
                    $labelAcuan = $grDetail ? 'diterima (GR)' : 'di-order (PO)';
                    return response()->json([
                        'message' => "Jumlah Qty untuk barang \"{$namaBarang}\" (" . number_format($inputQty, 2, ',', '.') . ") melebihi jumlah barang yang {$labelAcuan} (" . number_format($maxAllowedQty, 2, ',', '.') . ")."
                    ], 422);
                }

                $qty = $inputQty;
                $barangId = $grDetail ? $grDetail->barang_id : $poDetail->barang_id;

                // Calculate Effective Diskon from PO Detail / PO Header
                $poHeader = $po ?? ($grDetail?->goodsReceipt?->po);
                $diskonItem = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;

                if ($diskonItem <= 0 && $poHeader) {
                    if (floatval($poHeader->diskon_persen) > 0) {
                        $diskonItem = $hargaGross * (floatval($poHeader->diskon_persen) / 100);
                    } elseif (floatval($poHeader->total_diskon) > 0 && floatval($poHeader->subtotal) > 0) {
                        $diskonItem = (floatval($poHeader->total_diskon) / floatval($poHeader->subtotal)) * $hargaGross;
                    }
                }

                // Pembulatan per baris (2 desimal) agar SAMA PERSIS dengan frontend & kolom DB decimal(…,2)
                $diskonItem = min($diskonItem, $hargaGross);
                $lineSubtotalGross = round($qty * $hargaGross, 2);
                $lineDiskon = round($qty * $diskonItem, 2);
                $lineSubtotalNet = max(0, round($lineSubtotalGross - $lineDiskon, 2));

                // Per-item PPN resolution
                $itemPajakId = !empty($itemData['ppn_pajak_id']) ? $itemData['ppn_pajak_id'] : ($poDetail?->ppn_pajak_id ?? null);
                $itemTarifPpn = isset($itemData['tarif_ppn']) ? floatval($itemData['tarif_ppn']) : ($poDetail ? floatval($poDetail->tarif_ppn ?? 0) : 0);

                if ($itemPajakId && $itemTarifPpn <= 0) {
                    $pj = MasterPajak::find($itemPajakId);
                    if ($pj) $itemTarifPpn = floatval($pj->tarif);
                }

                if ($itemPajakId && !$firstPajakId) {
                    $firstPajakId = $itemPajakId;
                }

                $linePpn = round($lineSubtotalNet * ($itemTarifPpn / 100), 2);

                $subtotalBarangGross += $lineSubtotalGross;
                $totalDiskon += $lineDiskon;
                $totalPpnNominal += $linePpn;

                $itemsPayload[] = [
                    'jenis'        => 'Barang',
                    'keterangan'   => $namaBarang,
                    'coa_id'       => $itemData['coa_id'] ?? null,
                    'masuk_hpp'    => true,
                    'gr_detail_id' => $grDetail?->id,
                    'po_detail_id' => $poDetail?->id,
                    'barang_id'    => $barangId,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaGross,
                    'diskon_item'  => $diskonItem,
                    'subtotal'     => $lineSubtotalNet,
                    'ppn_pajak_id' => $itemPajakId,
                    'tarif_ppn'    => $itemTarifPpn,
                    'ppn_nominal'  => $linePpn,
                ];
            }

            // Process extra costs - separate HPP vs non-HPP
            $biayaHppTotal    = 0;
            $biayaNonHppTotal = 0;
            $extraCostPayload = [];

            if ($request->extra_costs && is_array($request->extra_costs)) {
                foreach ($request->extra_costs as $extra) {
                    $nom = round(floatval($extra['nominal'] ?? 0), 2);
                    if ($nom <= 0) continue;

                    $isHpp = in_array($extra['masuk_hpp'] ?? '0', [1, '1', 'true', true], true);

                    if ($isHpp) {
                        $biayaHppTotal += $nom;
                    } else {
                        $biayaNonHppTotal += $nom;
                    }

                    $extraCostPayload[] = [
                        'jenis'        => !empty($extra['jenis']) ? trim($extra['jenis']) : 'Biaya Lain',
                        'keterangan'   => $extra['keterangan'] ?? 'Biaya Tambahan',
                        'coa_id'       => $extra['coa_id'] ?? null,
                        'masuk_hpp'    => $isHpp,
                        'gr_detail_id' => null,
                        'po_detail_id' => null,
                        'barang_id'    => null,
                        'jumlah'       => 1,
                        'harga_satuan' => $nom,
                        'subtotal'     => $nom,
                    ];
                }
            } elseif ($request->biaya_lain) {
                $biayaNonHppTotal = floatval($request->biaya_lain);
            }

            // Tax & PPN Calculation (DPP = Subtotal Barang Netto setelah diskon)
            $subtotalBarangGross = round($subtotalBarangGross, 2);
            $totalDiskon         = round($totalDiskon, 2);
            $subtotalBarangNet = max(0, round($subtotalBarangGross - $totalDiskon, 2));
            $dpp = $subtotalBarangNet;
            $pajakNominal = round($totalPpnNominal, 2);
            $headerPajakId = $firstPajakId ?? $request->pajak_id ?? null;

            // Kurs: Prioritas dari input pengguna (bisa diubah), default mengikuti dokumen acuan (GR/PO)
            $reqKurs = $request->filled('kurs') ? $this->unformatNumber($request->kurs) : 0;
            $effectiveKurs = ($reqKurs > 0) ? $reqKurs : $sourceKurs;

            // Grand Total = DPP + ALL Extra Costs (HPP + Non-HPP) + PPN Tax - PPh dipotong
            $totalBiayaLain = round($biayaHppTotal + $biayaNonHppTotal, 2);
            $pph            = $this->resolvePphFromRequest($request, $dpp);
            $grandTotal     = max(0, round($dpp + $totalBiayaLain + $pajakNominal - $pph['nominal'], 2));
            $status         = ($request->action_type === 'draft') ? 'Draft' : 'Menunggu Persetujuan';

            // Validasi DP SEBELUM upload file (hindari file yatim) + rollback transaksi yang sudah dibuka
            $uangMukaVal = $request->filled('uang_muka') ? round(floatval($request->uang_muka), 2) : 0;
            if ($uangMukaVal > $grandTotal + 0.005) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Nominal Uang Muka (DP) sebesar ' . number_format($uangMukaVal, 2, ',', '.') . ' tidak boleh melebihi Total Pembelian sebesar ' . number_format($grandTotal, 2, ',', '.') . '!'
                ], 422);
            }
            $uangMukaVal = min($uangMukaVal, $grandTotal);

            // Upload Lampiran / Foto Bukti Faktur Supplier (Otomatis Kompres Gambar ke KB)
            $lampiranPath = null;
            if ($request->hasFile('lampiran_faktur')) {
                $path = $this->compressAndStoreAttachment($request->file('lampiran_faktur'), 'purchase_invoices');
                $lampiranPath = 'storage/' . $path;
            }
            $uangMukaCoa = ($uangMukaVal > 0)
                ? ($request->filled('uang_muka_coa_id') ? $request->uang_muka_coa_id : $this->resolveDefaultUangMukaCoaId())
                : null;
            $kasBankId = ($uangMukaVal > 0 && $request->filled('kas_bank_id'))
                ? (int) $request->kas_bank_id
                : null;

            if ($uangMukaVal > 0) {
                if (!$kasBankId) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Rekening Kas / Bank wajib dipilih untuk pengeluaran Uang Muka (DP)!'
                    ], 422);
                }

                $kasBank = KasBank::find($kasBankId);
                if (!$kasBank) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Akun Rekening Kas / Bank yang dipilih tidak ditemukan!'
                    ], 422);
                }

                $dpNominalKeluar = round($uangMukaVal * $effectiveKurs, 2);
                $saldoTersedia = (float) ($kasBank->saldo_berjalan ?? 0);

                if ($saldoTersedia < $dpNominalKeluar) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Saldo pada rekening Kas/Bank \"{$kasBank->nama_akun}\" tidak mencukupi untuk pembayaran Uang Muka (DP)! Saldo saat ini: Rp " . number_format($saldoTersedia, 2, ',', '.') . ", sedangkan DP yang dibutuhkan: Rp " . number_format($dpNominalKeluar, 2, ',', '.') . "."
                    ], 422);
                }
            }

            $tenderId = $request->tender_id ?: ($gr ? ($gr->tender_id ?: $gr->po?->tender_id) : ($po ? $po->tender_id : null));

            $invoice = PurchaseInvoice::create([
                'nomor_faktur'          => $nomorFaktur,
                'nomor_faktur_supplier' => $request->nomor_faktur_supplier,
                'tanggal_faktur'        => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'   => $dueDate,
                'po_id'                 => $poId,
                'gr_id'                 => $grId,
                'tender_id'             => $tenderId,
                'supplier_id'           => $supplierId ?? $request->supplier_id,
                'branch_id'             => !empty($activeBranchId) ? $activeBranchId : null,
                'company_id'            => !empty($activeBranchId) ? null : ($activeCompanyId ?: (auth()->user()?->company_id ?? 1)),
                'currency_id'           => $currencyId,
                'kurs'                  => $effectiveKurs,
                'subtotal'              => $subtotalBarangGross,
                'diskon_persen'         => 0,
                'diskon_nominal'        => $totalDiskon,
                'pajak_id'              => $headerPajakId,
                'pajak_nominal'         => $pajakNominal,
                'pph_pajak_id'          => $pph['pajak_id'],
                'tarif_pph'             => $pph['tarif'],
                'pph_nominal'           => $pph['nominal'],
                'pph_coa_id'            => $pph['coa_id'],
                'biaya_lain'            => $totalBiayaLain,
                'grand_total'           => $grandTotal,
                'uang_muka'             => $uangMukaVal,
                'uang_muka_coa_id'      => $uangMukaCoa,
                'kas_bank_id'           => $kasBankId,
                'lampiran_faktur'       => $lampiranPath,
                'status'                => $status,
                'payment_term_id'       => $request->payment_term_id,
                'keterangan'            => $request->keterangan,
                'created_by'            => auth()->id(),
            ]);

            // Save item details
            foreach ($itemsPayload as $d) {
                $d['purchase_invoice_id'] = $invoice->id;
                PurchaseInvoiceDetail::create($d);
            }

            // Save extra cost details
            foreach ($extraCostPayload as $d) {
                $d['purchase_invoice_id'] = $invoice->id;
                if (empty($d['barang_id'])) {
                    unset($d['barang_id']);
                }
                PurchaseInvoiceDetail::create($d);
            }

            // Save PPN detail row if tax > 0
            if ($pajakNominal > 0) {
                $ppnCoaId = $request->ppn_coa_id
                    ?? \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
                    ?? ChartOfAccount::where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%')->value('id')
                    ?? ChartOfAccount::where('is_header', 0)->value('id');

                PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $invoice->id,
                    'jenis'               => 'PPN',
                    'keterangan'          => 'PPN Masukan Pembelian',
                    'coa_id'              => $ppnCoaId,
                    'masuk_hpp'           => false,
                    'gr_detail_id'        => null,
                    'barang_id'           => null,
                    'jumlah'              => 1,
                    'harga_satuan'        => $pajakNominal,
                    'subtotal'            => $pajakNominal,
                ]);
            }

            DB::commit();
            self::clearKpiCache();

            // Kirim notifikasi WA ke Atasan jika status Menunggu Persetujuan
            if ($status === 'Menunggu Persetujuan') {
                try {
                    WhatsAppGatewayController::sendPurchaseInvoiceSubmissionNotification($invoice);
                } catch (\Throwable $waEx) {
                    Log::warning('Gagal mengirim WA submission Faktur: ' . $waEx->getMessage());
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => ($status === 'Menunggu Persetujuan')
                    ? "Faktur Pembelian {$invoice->nomor_faktur} berhasil diajukan ke Atasan via WhatsApp untuk persetujuan (ACC)!"
                    : "Faktur Pembelian {$invoice->nomor_faktur} berhasil disimpan sebagai Draft!",
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Store Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat Faktur Pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat Faktur Pembelian.'], 403);
        }

        $canViewJurnal = $this->canViewJurnal();

        $relations = [
            'supplier',
            'tender',
            'goodsReceipt.tender',
            'goodsReceipt.po.tender',
            'goodsReceipt.po.currency',
            'goodsReceipt.gudang',
            'po.tender',
            'po.supplier',
            'po.currency',
            'currency',
            'branch',
            'pajak',
            'pphPajak',
            'pphCoa',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa',
            'details.ppnPajak',
            'details.grDetail.poDetail.ppnPajak',
            'details.poDetail.ppnPajak',
            'details.poDetail.grDetails.goodsReceipt',
            'paymentDetails.payment.kasBank',
            'paymentDetails.payment.paymentMethod',
            'kasBank',
            'uangMukaCoa',
            'creator.karyawan',
            'approver.karyawan',
            'canceller.karyawan'
        ];

        if ($canViewJurnal) {
            $relations[] = 'jurnal.details.account';
            $relations[] = 'reversalJurnal.details.account';
        }

        $purchaseInvoice->load($relations);

        if (!$canViewJurnal) {
            $purchaseInvoice->unsetRelation('jurnal');
            $purchaseInvoice->unsetRelation('reversalJurnal');
        }

        $activePayments = $purchaseInvoice->paymentDetails
            ->filter(function ($pd) {
                return $pd->payment && $pd->payment->status !== 'Cancelled';
            })
            ->values();

        $activePaymentVouchers = $activePayments->map(function ($pd) {
            return [
                'id'               => $pd->payment->id,
                'nomor_pembayaran' => $pd->payment->nomor_pembayaran,
                'status'           => $pd->payment->status,
                'tanggal_bayar'    => $pd->payment->tanggal_bayar ? $pd->payment->tanggal_bayar->format('Y-m-d') : null,
                'jumlah_bayar'     => (float) $pd->jumlah_bayar,
            ];
        })->unique('nomor_pembayaran')->values();

        return response()->json([
            'invoice'                 => $purchaseInvoice,
            'can_view_jurnal'         => $canViewJurnal,
            'can_approve'             => $this->isInvoiceApprover(),
            'can_cancel'              => $this->canCancelInvoice(),
            'has_active_payment'      => $activePaymentVouchers->isNotEmpty(),
            'active_payment_vouchers' => $activePaymentVouchers,
        ]);
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canEditInvoice($purchaseInvoice)) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah Faktur Pembelian ini.'], 403);
        }

        $purchaseInvoice->load([
            'supplier',
            'tender',
            'goodsReceipt.tender',
            'goodsReceipt.po.tender',
            'goodsReceipt.po.supplier',
            'goodsReceipt.po.currency',
            'goodsReceipt.gudang',
            'po.tender',
            'po.supplier',
            'po.currency',
            'currency',
            'pajak',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa',
            'details.ppnPajak',
            'details.grDetail.poDetail.ppnPajak',
            'details.poDetail.ppnPajak',
            'details.poDetail.grDetails.goodsReceipt'
        ]);

        $poObj = $purchaseInvoice->po ?? $purchaseInvoice->goodsReceipt?->po;
        $proratedData = $this->resolveProratedBiayaLainAndDp($poObj, $purchaseInvoice->goodsReceipt, $purchaseInvoice->id);

        return response()->json([
            'status'             => 'success',
            'invoice'            => $purchaseInvoice,
            'biaya_lain_details' => $proratedData['biaya_lain_details'],
            'tracking'           => $proratedData['tracking'],
        ]);
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canEditInvoice($purchaseInvoice)) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah Faktur Pembelian ini.'], 403);
        }

        if (!in_array($purchaseInvoice->status, ['Draft', 'Menunggu Persetujuan', 'Menunggu Approval', 'Rejected'])) {
            return response()->json(['message' => 'Hanya Faktur berstatus Draft, Menunggu Persetujuan, atau Ditolak yang dapat diubah!'], 400);
        }

        // Normalize formatted inputs (e.g. "40.200,00" -> 40200.00)
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
            foreach ($items as $k => $item) {
                if (isset($item['qty'])) {
                    $items[$k]['qty'] = $this->unformatNumber($item['qty']);
                }
                if (isset($item['harga_satuan'])) {
                    $items[$k]['harga_satuan'] = $this->unformatNumber($item['harga_satuan']);
                }
                if (isset($item['diskon_item'])) {
                    $items[$k]['diskon_item'] = $this->unformatNumber($item['diskon_item']);
                }
            }
            $request->merge(['items' => $items]);
        }

        if ($request->has('extra_costs') && is_array($request->extra_costs)) {
            $extras = $request->extra_costs;
            foreach ($extras as $k => $extra) {
                if (isset($extra['nominal'])) {
                    $extras[$k]['nominal'] = $this->unformatNumber($extra['nominal']);
                }
            }
            $request->merge(['extra_costs' => $extras]);
        }

        if ($request->filled('kurs')) {
            $request->merge(['kurs' => $this->unformatNumber($request->kurs)]);
        }

        $uangMukaCheck = 0;
        if ($request->filled('uang_muka')) {
            $uangMukaCheck = $this->unformatNumber($request->uang_muka);
            $request->merge(['uang_muka' => $uangMukaCheck]);
        }

        $request->validate([
            'nomor_faktur_supplier' => 'required|string|max:100',
            'keterangan'            => 'nullable|string|max:1000',
            'tanggal_faktur'        => 'required|date',
            'tanggal_jatuh_tempo'   => 'required|date',
            'payment_term_id'       => 'nullable|exists:payment_terms,id',
            'pajak_id'              => 'nullable|exists:master_pajaks,id',
            'pph_pajak_id'          => 'nullable|exists:master_pajaks,id',
            'pph_coa_id'            => 'nullable|exists:chart_of_accounts,id',
            'uang_muka'             => 'nullable|numeric|min:0',
            'uang_muka_coa_id'      => 'nullable|exists:chart_of_accounts,id',
            'kas_bank_id'           => ($uangMukaCheck > 0.005) ? 'required|exists:kas_banks,id' : 'nullable|exists:kas_banks,id',
            'lampiran_faktur'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'items'                 => 'required|array|min:1',
            'items.*.gr_detail_id'  => 'nullable|exists:goods_receipt_details,id',
            'items.*.po_detail_id'  => 'nullable|exists:purchase_order_details,id',
            'items.*.qty'           => 'nullable|numeric|min:0.0001',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
        ], [
            'nomor_faktur_supplier.required' => 'Nomor Faktur dari Supplier wajib diisi.',
            'nomor_faktur_supplier.max'      => 'Nomor Faktur dari Supplier maksimal 100 karakter.',
            'keterangan.max'                 => 'Catatan / Keterangan Faktur maksimal 1000 karakter.',
            'tanggal_jatuh_tempo.required'   => 'Tanggal Jatuh Tempo wajib diisi.',
            'tanggal_jatuh_tempo.date'       => 'Format Tanggal Jatuh Tempo tidak valid.',
            'kas_bank_id.required'           => 'Rekening Kas / Bank wajib dipilih untuk memotong saldo pengeluaran Uang Muka (DP).',
            'kas_bank_id.exists'             => 'Rekening Kas / Bank yang dipilih tidak valid atau tidak terdaftar.',
            'lampiran_faktur.mimes'          => 'Format foto/dokumen bukti harus JPG, JPEG, PNG, atau PDF.',
            'lampiran_faktur.max'            => 'Ukuran foto/dokumen bukti maksimal 5 MB.',
        ]);

        try {
            DB::beginTransaction();

            // Calculate Due Date (prioritize user input, fallback to term calculation)
            $dueDate = $request->filled('tanggal_jatuh_tempo') ? $request->tanggal_jatuh_tempo : $request->tanggal_faktur;
            if (!$request->filled('tanggal_jatuh_tempo') && $request->payment_term_id) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari > 0) {
                    $dueDate = date('Y-m-d', strtotime($request->tanggal_faktur . " +{$term->jumlah_hari} days"));
                }
            }

            // Calculate Subtotal Gross, Diskon, Net & PPN Items (Barang)
            $subtotalBarangGross = 0;
            $totalDiskon = 0;
            $totalPpnNominal = 0;
            $itemsPayload = [];
            $firstPajakId = null;

            foreach ($request->items as $itemData) {
                $grDetail = !empty($itemData['gr_detail_id']) ? GoodsReceiptDetail::find($itemData['gr_detail_id']) : null;
                $poDetail = !empty($itemData['po_detail_id']) ? PurchaseOrderDetail::find($itemData['po_detail_id']) : ($grDetail?->poDetail);

                if (!$grDetail && !$poDetail) continue;

                $hargaGross = floatval($itemData['harga_satuan']);
                $maxAllowedQty = $grDetail ? floatval($grDetail->jumlah) : floatval($poDetail->jumlah);
                $inputQty = isset($itemData['qty']) ? floatval($itemData['qty']) : $maxAllowedQty;
                $namaBarang = $grDetail ? $grDetail->barang?->nama_barang : ($poDetail->barang?->nama_barang ?? $poDetail->keterangan);

                if ($inputQty <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Jumlah Qty untuk barang \"{$namaBarang}\" harus lebih dari 0."
                    ], 422);
                }

                if ($inputQty > $maxAllowedQty + 0.0001) {
                    DB::rollBack();
                    $labelAcuan = $grDetail ? 'diterima (GR)' : 'di-order (PO)';
                    return response()->json([
                        'message' => "Jumlah Qty untuk barang \"{$namaBarang}\" (" . number_format($inputQty, 2, ',', '.') . ") melebihi jumlah barang yang {$labelAcuan} (" . number_format($maxAllowedQty, 2, ',', '.') . ")."
                    ], 422);
                }

                $qty = $inputQty;
                $barangId = $grDetail ? $grDetail->barang_id : $poDetail->barang_id;

                // Calculate Effective Diskon from PO Detail / PO Header
                $poHeader = $purchaseInvoice->po ?? ($grDetail?->goodsReceipt?->po);
                $diskonItem = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;

                if ($diskonItem <= 0 && $poHeader) {
                    if (floatval($poHeader->diskon_persen) > 0) {
                        $diskonItem = $hargaGross * (floatval($poHeader->diskon_persen) / 100);
                    } elseif (floatval($poHeader->total_diskon) > 0 && floatval($poHeader->subtotal) > 0) {
                        $diskonItem = (floatval($poHeader->total_diskon) / floatval($poHeader->subtotal)) * $hargaGross;
                    }
                }

                // Pembulatan per baris (2 desimal) agar SAMA PERSIS dengan frontend & kolom DB decimal(…,2)
                $diskonItem = min($diskonItem, $hargaGross);
                $lineSubtotalGross = round($qty * $hargaGross, 2);
                $lineDiskon = round($qty * $diskonItem, 2);
                $lineSubtotalNet = max(0, round($lineSubtotalGross - $lineDiskon, 2));

                // Per-item PPN resolution
                $itemPajakId = !empty($itemData['ppn_pajak_id']) ? $itemData['ppn_pajak_id'] : ($poDetail?->ppn_pajak_id ?? null);
                $itemTarifPpn = isset($itemData['tarif_ppn']) ? floatval($itemData['tarif_ppn']) : ($poDetail ? floatval($poDetail->tarif_ppn ?? 0) : 0);

                if ($itemPajakId && $itemTarifPpn <= 0) {
                    $pj = MasterPajak::find($itemPajakId);
                    if ($pj) $itemTarifPpn = floatval($pj->tarif);
                }

                if ($itemPajakId && !$firstPajakId) {
                    $firstPajakId = $itemPajakId;
                }

                $linePpn = round($lineSubtotalNet * ($itemTarifPpn / 100), 2);

                $subtotalBarangGross += $lineSubtotalGross;
                $totalDiskon += $lineDiskon;
                $totalPpnNominal += $linePpn;

                $itemsPayload[] = [
                    'jenis'        => 'Barang',
                    'keterangan'   => $namaBarang,
                    'coa_id'       => $itemData['coa_id'] ?? null,
                    'masuk_hpp'    => true,
                    'gr_detail_id' => $grDetail?->id,
                    'po_detail_id' => $poDetail?->id,
                    'barang_id'    => $barangId,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaGross,
                    'diskon_item'  => $diskonItem,
                    'subtotal'     => $lineSubtotalNet,
                    'ppn_pajak_id' => $itemPajakId,
                    'tarif_ppn'    => $itemTarifPpn,
                    'ppn_nominal'  => $linePpn,
                ];
            }

            // Process extra costs
            $biayaHppTotal    = 0;
            $biayaNonHppTotal = 0;
            $extraCostPayload = [];

            if ($request->extra_costs && is_array($request->extra_costs)) {
                foreach ($request->extra_costs as $extra) {
                    $nom = round(floatval($extra['nominal'] ?? 0), 2);
                    if ($nom <= 0) continue;

                    $isHpp = in_array($extra['masuk_hpp'] ?? '0', [1, '1', 'true', true], true);

                    if ($isHpp) {
                        $biayaHppTotal += $nom;
                    } else {
                        $biayaNonHppTotal += $nom;
                    }

                    $extraCostPayload[] = [
                        'jenis'        => !empty($extra['jenis']) ? trim($extra['jenis']) : 'Biaya Lain',
                        'keterangan'   => $extra['keterangan'] ?? 'Biaya Tambahan',
                        'coa_id'       => $extra['coa_id'] ?? null,
                        'masuk_hpp'    => $isHpp,
                        'gr_detail_id' => null,
                        'po_detail_id' => null,
                        'barang_id'    => null,
                        'jumlah'       => 1,
                        'harga_satuan' => $nom,
                        'subtotal'     => $nom,
                    ];
                }
            } elseif ($request->biaya_lain) {
                $biayaNonHppTotal = floatval($request->biaya_lain);
            }

            $subtotalBarangGross = round($subtotalBarangGross, 2);
            $totalDiskon         = round($totalDiskon, 2);
            $subtotalBarangNet = max(0, round($subtotalBarangGross - $totalDiskon, 2));
            $dpp = $subtotalBarangNet;
            $pajakNominal = round($totalPpnNominal, 2);
            $headerPajakId = $firstPajakId ?? $request->pajak_id ?? null;

            $totalBiayaLain = round($biayaHppTotal + $biayaNonHppTotal, 2);
            $pph            = $this->resolvePphFromRequest($request, $dpp);
            $grandTotal     = max(0, round($dpp + $totalBiayaLain + $pajakNominal - $pph['nominal'], 2));
            $status         = ($request->action_type === 'draft') ? 'Draft' : 'Menunggu Persetujuan';

            // Validasi DP SEBELUM upload file + rollback transaksi yang sudah dibuka
            $uangMukaVal = $request->filled('uang_muka') ? round(floatval($request->uang_muka), 2) : 0;
            if ($uangMukaVal > $grandTotal + 0.005) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Nominal Uang Muka (DP) sebesar ' . number_format($uangMukaVal, 2, ',', '.') . ' tidak boleh melebihi Total Pembelian sebesar ' . number_format($grandTotal, 2, ',', '.') . '!'
                ], 422);
            }
            $uangMukaVal = min($uangMukaVal, $grandTotal);

            // Upload Lampiran / Foto Bukti Faktur Supplier (Otomatis Kompres Gambar ke KB)
            $lampiranPath = $purchaseInvoice->lampiran_faktur;
            if ($request->hasFile('lampiran_faktur')) {
                if ($purchaseInvoice->lampiran_faktur) {
                    $oldPath = str_replace('storage/', '', $purchaseInvoice->lampiran_faktur);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $this->compressAndStoreAttachment($request->file('lampiran_faktur'), 'purchase_invoices');
                $lampiranPath = 'storage/' . $path;
            }
            $uangMukaCoa = ($uangMukaVal > 0)
                ? ($request->filled('uang_muka_coa_id') ? $request->uang_muka_coa_id : ($purchaseInvoice->uang_muka_coa_id ?: $this->resolveDefaultUangMukaCoaId()))
                : null;
            $kasBankId = ($uangMukaVal > 0)
                ? ($request->filled('kas_bank_id') ? (int) $request->kas_bank_id : $purchaseInvoice->kas_bank_id)
                : null;

            if ($uangMukaVal > 0) {
                if (!$kasBankId) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Rekening Kas / Bank wajib dipilih untuk pengeluaran Uang Muka (DP)!'
                    ], 422);
                }

                $kasBank = KasBank::find($kasBankId);
                if (!$kasBank) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Akun Rekening Kas / Bank yang dipilih tidak ditemukan!'
                    ], 422);
                }

                $effectiveKursUpdate = floatval($request->kurs) > 0 ? floatval($request->kurs) : (floatval($purchaseInvoice->kurs) > 0 ? floatval($purchaseInvoice->kurs) : 1.0);
                $dpNominalKeluar = round($uangMukaVal * $effectiveKursUpdate, 2);
                $saldoTersedia = (float) ($kasBank->saldo_berjalan ?? 0);

                if ($saldoTersedia < $dpNominalKeluar) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Saldo pada rekening Kas/Bank \"{$kasBank->nama_akun}\" tidak mencukupi untuk pembayaran Uang Muka (DP)! Saldo saat ini: Rp " . number_format($saldoTersedia, 2, ',', '.') . ", sedangkan DP yang dibutuhkan: Rp " . number_format($dpNominalKeluar, 2, ',', '.') . "."
                    ], 422);
                }
            }

            $tenderId = $request->tender_id ?: ($purchaseInvoice->goodsReceipt ? ($purchaseInvoice->goodsReceipt->tender_id ?: $purchaseInvoice->goodsReceipt->po?->tender_id) : ($purchaseInvoice->po ? $purchaseInvoice->po->tender_id : null));
            // Pertahankan tender yang sudah tertaut bila dokumen acuan tidak memiliki tender
            $tenderId = $tenderId ?: $purchaseInvoice->tender_id;

            $purchaseInvoice->update([
                'nomor_faktur'          => ($request->filled('nomor_faktur') && !str_contains($request->nomor_faktur, 'Otomatis')) ? trim($request->nomor_faktur) : $purchaseInvoice->nomor_faktur,
                'nomor_faktur_supplier' => $request->nomor_faktur_supplier,
                'tanggal_faktur'        => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'   => $dueDate,
                'tender_id'             => $tenderId,
                'kurs'                  => floatval($request->kurs) > 0 ? floatval($request->kurs) : $purchaseInvoice->kurs,
                'subtotal'              => $subtotalBarangGross,
                'diskon_nominal'        => $totalDiskon,
                'pajak_id'              => $headerPajakId,
                'pajak_nominal'         => $pajakNominal,
                'pph_pajak_id'          => $pph['pajak_id'],
                'tarif_pph'             => $pph['tarif'],
                'pph_nominal'           => $pph['nominal'],
                'pph_coa_id'            => $pph['coa_id'],
                'biaya_lain'            => $totalBiayaLain,
                'grand_total'           => $grandTotal,
                'uang_muka'             => $uangMukaVal,
                'uang_muka_coa_id'      => $uangMukaCoa,
                'kas_bank_id'           => $kasBankId,
                'lampiran_faktur'       => $lampiranPath,
                'status'                => $status,
                'payment_term_id'       => $request->payment_term_id,
                'keterangan'            => $request->keterangan,
                'alasan_penolakan'      => null, // Reset status penolakan jika diperbaiki
            ]);

            // Re-sync details
            $purchaseInvoice->details()->delete();

            foreach ($itemsPayload as $d) {
                $d['purchase_invoice_id'] = $purchaseInvoice->id;
                PurchaseInvoiceDetail::create($d);
            }

            foreach ($extraCostPayload as $d) {
                $d['purchase_invoice_id'] = $purchaseInvoice->id;
                if (empty($d['barang_id'])) {
                    unset($d['barang_id']);
                }
                PurchaseInvoiceDetail::create($d);
            }

            if ($pajakNominal > 0) {
                $ppnCoaId = $request->ppn_coa_id
                    ?? \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
                    ?? ChartOfAccount::where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%')->value('id')
                    ?? ChartOfAccount::where('is_header', 0)->value('id');

                PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'jenis'               => 'PPN',
                    'keterangan'          => 'PPN Masukan Pembelian',
                    'coa_id'              => $ppnCoaId,
                    'masuk_hpp'           => false,
                    'gr_detail_id'        => null,
                    'barang_id'           => null,
                    'jumlah'              => 1,
                    'harga_satuan'        => $pajakNominal,
                    'subtotal'            => $pajakNominal,
                ]);
            }

            DB::commit();
            self::clearKpiCache();

            // Kirim notifikasi WA ke Atasan jika status Menunggu Persetujuan
            if ($status === 'Menunggu Persetujuan') {
                try {
                    WhatsAppGatewayController::sendPurchaseInvoiceSubmissionNotification($purchaseInvoice);
                } catch (\Throwable $waEx) {
                    Log::warning('Gagal mengirim WA submission Faktur: ' . $waEx->getMessage());
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => ($status === 'Menunggu Persetujuan')
                    ? "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diperbarui & diajukan ke Atasan via WhatsApp untuk persetujuan (ACC)!"
                    : "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diperbarui sebagai Draft!",
                'invoice' => $purchaseInvoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Update Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Faktur Pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->isInvoiceApprover()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses (otoritas) untuk menyetujui Faktur Pembelian.'], 403);
        }

        if (!in_array($purchaseInvoice->status, ['Menunggu Persetujuan', 'Menunggu Approval', 'Draft'])) {
            return response()->json(['message' => 'Hanya Faktur berstatus Menunggu Persetujuan atau Draft yang dapat disetujui!'], 400);
        }

        try {
            DB::beginTransaction();

            // Kunci baris faktur & cek ulang status (cegah double-approve / double jurnal saat klik ganda)
            $purchaseInvoice = PurchaseInvoice::whereKey($purchaseInvoice->id)->lockForUpdate()->firstOrFail();
            if (!in_array($purchaseInvoice->status, ['Menunggu Persetujuan', 'Menunggu Approval', 'Draft'])) {
                DB::rollBack();
                return response()->json(['message' => "Faktur {$purchaseInvoice->nomor_faktur} sudah diproses sebelumnya (status: {$purchaseInvoice->status})."], 409);
            }

            // Validasi kecukupan saldo Kas / Bank jika ada Uang Muka (DP) sebelum approve
            $dpNominalApprove = (float) ($purchaseInvoice->uang_muka ?? 0);
            if ($dpNominalApprove > 0) {
                $kasBank = $purchaseInvoice->kas_bank_id ? KasBank::whereKey($purchaseInvoice->kas_bank_id)->lockForUpdate()->first() : null;
                if (!$kasBank) {
                    $companyId = $purchaseInvoice->company_id ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1));
                    $branchId  = $purchaseInvoice->branch_id ?: session('active_branch_id');
                    $kasBankQuery = KasBank::query();
                    if (!empty($branchId)) {
                        $kasBankQuery->where('branch_id', $branchId);
                    } else {
                        $kasBankQuery->whereNull('branch_id')->where('company_id', $companyId);
                    }
                    $kasBank = $kasBankQuery->where('is_active', true)->lockForUpdate()->first() ?: KasBank::where('is_active', true)->lockForUpdate()->first();
                }

                if ($kasBank) {
                    $kursApprove = (float) ($purchaseInvoice->kurs ?: 1);
                    $dpNominalKeluar = round($dpNominalApprove * $kursApprove, 2);
                    $saldoTersedia = (float) ($kasBank->saldo_berjalan ?? 0);

                    if ($saldoTersedia < $dpNominalKeluar) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Gagal menyetujui Faktur: Saldo pada rekening Kas/Bank \"{$kasBank->nama_akun}\" tidak mencukupi untuk pengeluaran Uang Muka (DP)! Saldo saat ini: Rp " . number_format($saldoTersedia, 2, ',', '.') . ", sedangkan DP yang dibutuhkan: Rp " . number_format($dpNominalKeluar, 2, ',', '.') . "."
                        ], 422);
                    }
                }
            }

            $purchaseInvoice->status = 'Posted';
            $purchaseInvoice->approved_by = auth()->id();
            $purchaseInvoice->approved_at = now();
            $purchaseInvoice->alasan_penolakan = null;
            $purchaseInvoice->save();

            // Menerbitkan jurnal otomatis akuntansi hanya saat sudah disetujui (ACC)
            $this->createJournalForInvoice($purchaseInvoice);

            // Potong saldo Kas / Bank jika ada Uang Muka (DP)
            $this->syncInvoiceDpKasMutation($purchaseInvoice);

            DB::commit();
            self::clearKpiCache();

            // Kirim notifikasi WA ke User pembuat faktur bahwa pengajuan telah di-ACC & diposting
            try {
                WhatsAppGatewayController::sendPurchaseInvoiceApprovedNotification($purchaseInvoice, auth()->user());
            } catch (\Throwable $waEx) {
                Log::warning('Gagal mengirim WA approve Faktur: ' . $waEx->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil disetujui (ACC) & diposting ke Jurnal Akuntansi!",
                'invoice' => $purchaseInvoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Approve Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyetujui Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->isInvoiceApprover()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses (otoritas) untuk menolak Faktur Pembelian.'], 403);
        }

        if (!in_array($purchaseInvoice->status, ['Menunggu Persetujuan', 'Menunggu Approval'])) {
            return response()->json(['message' => 'Hanya Faktur berstatus Menunggu Persetujuan yang dapat ditolak!'], 400);
        }

        $request->validate([
            'alasan' => 'required|string|max:500'
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi agar pembuat dapat memperbaikinya.'
        ]);

        try {
            DB::beginTransaction();

            $purchaseInvoice->status = 'Rejected';
            $purchaseInvoice->alasan_penolakan = $request->alasan;
            $purchaseInvoice->save();

            // Rollback mutasi kas DP jika ada
            $this->rollbackInvoiceDpKasMutation($purchaseInvoice);

            DB::commit();
            self::clearKpiCache();

            // Kirim notifikasi penolakan via WA ke pembuat faktur
            try {
                WhatsAppGatewayController::sendPurchaseInvoiceRejectedNotification($purchaseInvoice, $request->alasan, auth()->user());
            } catch (\Throwable $waEx) {
                Log::warning('Gagal mengirim WA reject Faktur: ' . $waEx->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Pengajuan Faktur Pembelian {$purchaseInvoice->nomor_faktur} telah ditolak.",
                'invoice' => $purchaseInvoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Reject Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menolak Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function sendWhatsApp(PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canViewInvoice()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengirim notifikasi Faktur Pembelian.'], 403);
        }

        try {
            if ($purchaseInvoice->status === 'Draft' || $purchaseInvoice->status === 'Rejected') {
                $purchaseInvoice->status = 'Menunggu Persetujuan';
                $purchaseInvoice->alasan_penolakan = null;
                $purchaseInvoice->save();
            }

            try {
                WhatsAppGatewayController::sendPurchaseInvoiceSubmissionNotification($purchaseInvoice);
            } catch (\Throwable $waEx) {
                Log::warning('Gagal mengirim WA submission Faktur: ' . $waEx->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Notifikasi pengajuan Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil dikirim ke Atasan via WhatsApp!",
            ]);
        } catch (\Exception $e) {
            Log::error('PurchaseInvoice Send WhatsApp Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim WhatsApp: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('batalkan-purchase-invoice')),
            403,
            'Anda tidak memiliki hak akses untuk membatalkan transaksi Faktur Pembelian.'
        );

        // Isolasi Cabang / Tenant
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId) && !empty($purchaseInvoice->branch_id) && $purchaseInvoice->branch_id != $activeBranchId) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses cabang untuk faktur ini.'], 403);
        }

        if (in_array($purchaseInvoice->status, ['Cancelled', 'Void'])) {
            return response()->json(['message' => 'Faktur Pembelian sudah dibatalkan sebelumnya!'], 400);
        }

        if ($purchaseInvoice->status === 'Draft') {
            return response()->json(['message' => 'Faktur berstatus Draft tidak perlu dibatalkan, silakan gunakan tombol Hapus Draft.'], 400);
        }

        // Cek integritas pembayaran utang (AP Settlements)
        $activePayments = $purchaseInvoice->paymentDetails()
            ->whereHas('payment', function ($q) {
                $q->where('status', '!=', 'Cancelled');
            })
            ->with('payment')
            ->get();

        if ($activePayments->isNotEmpty()) {
            $voucherList = $activePayments->map(function ($pd) {
                return ($pd->payment?->nomor_pembayaran ?? 'Voucher') . ' (' . ($pd->payment?->status ?? 'Aktif') . ')';
            })->filter()->unique()->implode(', ');

            return response()->json([
                'status'  => 'error',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} tidak dapat dibatalkan karena telah memiliki transaksi Pembayaran Utang yang masih aktif [{$voucherList}]. Anda wajib membatalkan transaksi Pembayaran Utang tersebut terlebih dahulu!"
            ], 422);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:255',
        ], [
            'alasan_pembatalan.required' => 'Alasan pembatalan wajib diisi!',
            'alasan_pembatalan.max'      => 'Alasan pembatalan maksimal 255 karakter!',
        ]);

        try {
            DB::beginTransaction();

            $purchaseInvoice->status            = 'Cancelled';
            $purchaseInvoice->cancelled_by     = auth()->id();
            $purchaseInvoice->cancelled_at     = now();
            $purchaseInvoice->alasan_pembatalan = $request->alasan_pembatalan;
            $purchaseInvoice->save();

            // 1. Buat Jurnal Pembalik (Reversal Journal Entry) - Inversi Akurat dari Jurnal Asli
            $purchaseInvoice->loadMissing(['supplier']);
            $supplierName = $purchaseInvoice->supplier?->nama_supplier ?? 'Supplier';

            $origJurnal = Jurnal::with('details')
                ->where('referensi_tipe', 'Purchase Invoice')
                ->where('referensi_id', $purchaseInvoice->id)
                ->first();

            if ($origJurnal && $origJurnal->details->count() > 0) {
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

                $reversalJurnal = Jurnal::create([
                    'nomor_jurnal'    => $nomorJurnalReversal,
                    'tanggal_jurnal'  => now()->toDateString(),
                    'referensi_tipe'  => 'Purchase Invoice Reversal',
                    'referensi_id'    => $purchaseInvoice->id,
                    'no_tender'       => $origJurnal->no_tender, // Wajib ikut agar laporan per Tender ter-netralisir
                    'keterangan'      => "PEMBALIKAN FAKTUR PEMBELIAN ({$purchaseInvoice->nomor_faktur}) dari {$supplierName}. Alasan: {$request->alasan_pembatalan}",
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
            }

            // 2. Rollback mutasi kas DP jika ada
            $this->rollbackInvoiceDpKasMutation($purchaseInvoice);

            DB::commit();
            self::clearKpiCache();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil dibatalkan!",
                'invoice' => $purchaseInvoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Cancel Error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membatalkan Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function void(PurchaseInvoice $purchaseInvoice)
    {
        return $this->cancel(request(), $purchaseInvoice);
    }

    public function pdf(PurchaseInvoice $purchaseInvoice)
    {
        $user = auth()->user();
        if (!$user || !$user->can('cetak-purchase-invoice')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak Faktur Pembelian.');
        }

        // Multi-tenant & Branch boundary check (Mencegah IDOR / Celah Keamanan Akses Antar Cabang)
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $isSuperOrOwner  = $user->hasAnyRole(['Super Admin', 'Owner']);

        if (!$isSuperOrOwner) {
            if (!empty($activeBranchId) && !empty($purchaseInvoice->branch_id) && $purchaseInvoice->branch_id != $activeBranchId) {
                abort(403, 'Anda tidak memiliki akses cabang untuk mencetak Faktur Pembelian ini.');
            }
            if (!empty($activeCompanyId) && !empty($purchaseInvoice->company_id) && $purchaseInvoice->company_id != $activeCompanyId) {
                abort(403, 'Anda tidak memiliki akses perusahaan untuk mencetak Faktur Pembelian ini.');
            }
        }

        $purchaseInvoice->load([
            'supplier.primaryBank',
            'supplier.banks',
            'goodsReceipt.tender',
            'goodsReceipt.po.currency',
            'goodsReceipt.po.tender',
            'po.currency',
            'po.tender',
            'tender',
            'currency',
            'branch',
            'company',
            'pajak',
            'pphPajak',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa',
            'details.ppnPajak',
            'paymentDetails.payment',
            'creator.karyawan',
            'approver.karyawan',
            'kasBank'
        ]);

        $isBranch = !empty($purchaseInvoice->branch_id);
        $branch   = $purchaseInvoice->branch;
        $company  = null;

        if ($isBranch && $branch) {
            $company = $branch->company ?: ($purchaseInvoice->company ?: \App\Models\Company::find($branch->company_id));
            $entityName = $branch->nama_cabang ?: ($branch->nama_perusahaan ?: 'ICONIC DURABLE RELIABLE');
            $parentName = $company?->nama_perusahaan ?? '';
            $alamatParts = array_filter([
                $branch->address ?: ($branch->alamat ?? ''),
                $branch->city ?: ($branch->kota ?? ''),
                $branch->province ?: ($branch->provinsi ?? ''),
                $branch->postal_code ?: ($branch->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $branch->phone ?: ($branch->telepon ?? '');
            $entityEmail   = $branch->email ?: '';
            $entityNpwp    = '';
        } else {
            $company = $purchaseInvoice->company ?: (\App\Models\Company::find($purchaseInvoice->company_id) ?: \App\Models\Company::first());
            $entityName = $company?->nama_perusahaan ?? 'ICONIC DURABLE RELIABLE';
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

        $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
        $appLogo = $appLogoSetting && file_exists(public_path('storage/' . $appLogoSetting))
            ? asset('storage/' . $appLogoSetting)
            : ($appLogoSetting && file_exists(public_path($appLogoSetting)) ? asset($appLogoSetting) : asset('gambar/logo.png'));

        // Logo entitas pemilik dokumen (Cabang atau Perusahaan yang memiliki faktur ini)
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
            $firstComp = \App\Models\Company::first();
            if ($firstComp && !empty($firstComp->logo)) {
                if (file_exists(public_path('storage/' . $firstComp->logo))) {
                    $activeLogo = asset('storage/' . $firstComp->logo);
                } elseif (file_exists(public_path($firstComp->logo))) {
                    $activeLogo = asset($firstComp->logo);
                }
            }
        }
        if (!$activeLogo) {
            $activeLogo = $appLogo;
        }

        $bankAccounts = \App\Models\KasBank::where('is_active', true)
            ->where(function($q) use ($purchaseInvoice) {
                if (!empty($purchaseInvoice->branch_id)) {
                    $q->where('branch_id', $purchaseInvoice->branch_id);
                } else {
                    $q->where('company_id', $purchaseInvoice->company_id ?: 1);
                }
            })
            ->whereNotNull('nomor_rekening')
            ->where('nomor_rekening', '!=', '')
            ->get();

        $supplierBanks = $purchaseInvoice->supplier ? ($purchaseInvoice->supplier->banks ?: collect()) : collect();
        $primarySupplierBank = $purchaseInvoice->supplier?->primaryBank ?: $supplierBanks->first();

        $validPayments = $purchaseInvoice->paymentDetails ? $purchaseInvoice->paymentDetails->filter(function($d) {
            return $d->payment && $d->payment->status !== 'Cancelled' && empty($d->payment->deleted_at);
        }) : collect();

        $dpNominal = (float)($purchaseInvoice->uang_muka ?? 0);
        $totalPaid = $dpNominal + (float)$validPayments->sum(function($d) {
            return (float)$d->jumlah_bayar + (float)$d->potongan;
        });

        $sisaTagihanBerjalan = max(0, (float)$purchaseInvoice->grand_total - $totalPaid);

        $currencyCode = $purchaseInvoice->currency?->kode_currency ?? ($purchaseInvoice->po?->currency?->kode_currency ?? 'IDR');
        $isForeign = ($currencyCode !== 'IDR' && $currencyCode !== '');

        // Terbilang Total Nilai Faktur (Standard Akuntansi Komersial)
        $terbilang = $this->terbilang((float)$purchaseInvoice->grand_total) . ($isForeign ? " {$currencyCode}" : ' Rupiah');
        $terbilangSisa = ($sisaTagihanBerjalan > 0.01 && $purchaseInvoice->status !== 'Paid')
            ? ($this->terbilang($sisaTagihanBerjalan) . ($isForeign ? " {$currencyCode}" : ' Rupiah'))
            : null;

        return view('modul_purchasing.faktur_pembelian.pdf', compact(
            'purchaseInvoice',
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
            'appLogo',
            'bankAccounts',
            'supplierBanks',
            'primarySupplierBank',
            'validPayments',
            'dpNominal',
            'totalPaid',
            'sisaTagihanBerjalan',
            'terbilang',
            'terbilangSisa'
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

    /**
     * Endpoint API JSON untuk mengambil rincian Jurnal Akuntansi & Jurnal Pembalik Faktur Pembelian.
     * Menggunakan permission: lihat-jurnal-purchase-invoice atau lihat-jurnal-umum.
     */
    public function getJurnalData(PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canViewJurnal()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat rincian jurnal akuntansi.'
            ], 403);
        }

        $purchaseInvoice->loadMissing([
            'jurnal.details.account',
            'reversalJurnal.details.account',
            'supplier',
            'tender',
            'goodsReceipt.tender',
            'po.tender'
        ]);

        return response()->json([
            'success'         => true,
            'jurnal'          => $purchaseInvoice->jurnal,
            'reversal_jurnal' => $purchaseInvoice->reversalJurnal,
            'invoice'         => [
                'id'                    => $purchaseInvoice->id,
                'nomor_faktur'          => $purchaseInvoice->nomor_faktur,
                'nomor_faktur_supplier' => $purchaseInvoice->nomor_faktur_supplier,
                'status'                => $purchaseInvoice->status,
                'supplier_name'         => $purchaseInvoice->supplier?->nama_supplier ?? '-',
            ]
        ]);
    }

    public function post(PurchaseInvoice $purchaseInvoice)
    {
        // Posting langsung (tanpa alur ACC) hanya boleh dilakukan oleh Approver
        if (!$this->isInvoiceApprover()) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk memposting Faktur Pembelian.'], 403);
        }

        if ($purchaseInvoice->status !== 'Draft') {
            return response()->json(['message' => 'Hanya Faktur berstatus Draft yang dapat diposting!'], 400);
        }

        try {
            DB::beginTransaction();

            $purchaseInvoice->status = 'Posted';
            $purchaseInvoice->approved_by = auth()->id();
            $purchaseInvoice->approved_at = now();
            $purchaseInvoice->alasan_penolakan = null;
            $purchaseInvoice->save();

            // Auto-Journal Entry Generation for Purchase Invoice
            $this->createJournalForInvoice($purchaseInvoice);

            // Potong saldo Kas / Bank jika ada Uang Muka (DP)
            $this->syncInvoiceDpKasMutation($purchaseInvoice);

            DB::commit();
            self::clearKpiCache();

            // Kirim notifikasi WA ke User pembuat faktur bahwa faktur telah diposting (di-ACC)
            try {
                WhatsAppGatewayController::sendPurchaseInvoiceApprovedNotification($purchaseInvoice, auth()->user());
            } catch (\Throwable $waEx) {
                Log::warning('Gagal mengirim WA approve Faktur: ' . $waEx->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diposting & Jurnal Akuntansi diterbitkan!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Post Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memposting Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        if (!$this->canDeleteInvoice($purchaseInvoice)) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghapus Faktur Pembelian ini.'], 403);
        }

        if ($purchaseInvoice->status !== 'Draft') {
            return response()->json(['message' => 'Faktur yang sudah diposting tidak dapat dihapus!'], 400);
        }

        try {
            DB::beginTransaction();

            // Delete associated Jurnal entry
            Jurnal::where('referensi_tipe', 'Purchase Invoice')
                ->where('referensi_id', $purchaseInvoice->id)
                ->delete();

            // Rollback mutasi kas DP jika ada
            $this->rollbackInvoiceDpKasMutation($purchaseInvoice);

            $purchaseInvoice->details()->delete();

            // Hapus file fisik lampiran jika ada
            if ($purchaseInvoice->lampiran_faktur && Storage::disk('public')->exists($purchaseInvoice->lampiran_faktur)) {
                Storage::disk('public')->delete($purchaseInvoice->lampiran_faktur);
            }

            $purchaseInvoice->delete();

            DB::commit();
            self::clearKpiCache();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus Faktur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Automatic General Ledger Journal Entry Generator for Purchase Invoice.
     *
     * Journal Mapping:
     *   DEBIT  Utang Belum Ditagih  = Subtotal Barang (+ HPP extra costs)
     *   DEBIT  PPN Masukan          = pajak_nominal
     *   DEBIT  COA Beban / Detail   = each non-HPP extra cost (per coa_id)
     *   KREDIT Utang PPh            = pph_nominal (PPh dipotong, mis. PPh 23)
     *   KREDIT Utang Usaha (AP)     = grand_total (neto setelah potong PPh)
     */
    protected function createJournalForInvoice(PurchaseInvoice $invoice)
    {
        // Delete existing journal if any (idempotent re-post, forceDelete to clear soft-deleted duplicates)
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Purchase Invoice')
            ->where('referensi_id', $invoice->id)
            ->get();

        foreach ($existingJurnals as $exJnl) {
            $exJnl->details()->delete();
            $exJnl->forceDelete();
        }

        $activeBranchId = session('active_branch_id') ?? $invoice->branch_id;

        // Load details to inspect per-row masuk_hpp and coa_id
        $invoice->loadMissing(['details', 'supplier']);

        // ── COA Resolution ───────────────────────────────────────────────
        // 1. Unbilled AP (Utang Belum Ditagih / GRNI) – DEBIT for barang & HPP costs
        $unbilledApCoaId = AccountMappingHelper::getCoaId('default', 'default_gr_clearing')
            ?? AccountMappingHelper::getCoaId('default', 'unbilled_ap', 'accrued_po')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                $q->where('code', 'like', '2102%')->orWhere('name', 'like', '%GR/IR%')->orWhere('name', 'like', '%Penerimaan Barang%');
            })->value('id')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where('type', 'Liability')->value('id');

        // 2. Tax PPN Masukan – DEBIT
        $ppnDetail = $invoice->details->firstWhere('jenis', 'PPN');
        $taxInputCoaId = ($ppnDetail && $ppnDetail->coa_id) ? $ppnDetail->coa_id : null;
        if (!$taxInputCoaId) {
            $pajakId = $invoice->po?->pajak_id;
            $taxInputCoaId = $pajakId ? AccountMappingHelper::getCoaId('tax', "tax_{$pajakId}_in") : null;
            if (!$taxInputCoaId) {
                $taxInputCoaId = AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
                    ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                        $q->where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%');
                    })->value('id');
            }
        }

        // 3. Accounts Payable (Utang Usaha) – KREDIT
        $apCoaId = AccountMappingHelper::getCoaId('default', 'default_ap')
            ?? AccountMappingHelper::getCoaId('default', 'accounts_payable', 'ap')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                $q->where('code', 'like', '2101%')->orWhere('code', 'like', '211%')->orWhere('name', 'like', '%Hutang Usaha%')->orWhere('name', 'like', '%Utang Usaha%');
            })->value('id');

        // Fallback to first non-header COA if any mapping fails
        $firstCoa = ChartOfAccount::forActiveCompany()->detailOnly()->first();
        if (!$unbilledApCoaId) $unbilledApCoaId = $firstCoa?->id;
        if (!$taxInputCoaId)   $taxInputCoaId   = $firstCoa?->id;
        if (!$apCoaId)         $apCoaId         = $firstCoa?->id;

        // ── Journal Number Generation JNL/YYYY/MM/XXXX ──────────────────
        $year   = date('Y', strtotime($invoice->tanggal_faktur));
        $month  = date('m', strtotime($invoice->tanggal_faktur));
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

        $supplierName = $invoice->supplier?->nama_supplier ?? 'Supplier';
        $kurs         = (float) ($invoice->kurs ?: 1);
        if ($kurs <= 0) $kurs = 1.0;

        $cur          = $invoice->currency ?? $invoice->po?->currency;
        $curCode      = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
        $isForeign    = ($curCode !== 'IDR' || abs($kurs - 1.0) > 0.0001);

        $grandTotal   = floatval($invoice->grand_total);
        $taxNominal   = floatval($invoice->pajak_nominal);
        $pphNominal   = floatval($invoice->pph_nominal ?? 0);
        $pphCoaId     = $invoice->pph_coa_id ?: ($pphNominal > 0 ? ($this->resolveDefaultPphCoaId() ?? $firstCoa?->id) : null);

        // ── Separate detail rows by type and target COA ───────────────────
        $barangDebitRows = [];
        $biayaHppRows    = [];
        $biayaNonHppRows = [];

        $isFromGr = !empty($invoice->gr_id);
        // Default COA barang/biaya HPP untuk faktur PO langsung (tanpa GR) = Persediaan
        $defaultInventoryCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory') ?? $unbilledApCoaId;

        foreach ($invoice->details as $det) {
            $nom = floatval($det->subtotal);
            if ($nom <= 0) continue;
            // Baris PPN dilewati di sini karena dicatat secara eksplisit & khusus di bagian PPN Masukan (poin 3)
            if ($det->jenis === 'PPN') continue;

            if ($det->jenis === 'Barang') {
                // Faktur dari GR WAJIB menutup GRNI (GR sudah mendebit Persediaan & mengkredit GRNI)
                $coa = $isFromGr ? $unbilledApCoaId : ($det->coa_id ?? $defaultInventoryCoaId);
                $ket = $isFromGr ? 'Penutupan Utang Belum Ditagih (GRNI)' : 'Pembelian Barang (PO)';
                if (!isset($barangDebitRows[$coa])) {
                    $barangDebitRows[$coa] = ['total' => 0, 'keterangan' => $ket];
                }
                $barangDebitRows[$coa]['total'] += $nom;
            } elseif ($det->jenis === 'Biaya Lain' || $det->barang_id === null || in_array($det->jenis, ['Pengiriman', 'Ongkir', 'Asuransi', 'Biaya Tambahan'])) {
                $ketBiaya = !empty($det->keterangan) ? $det->keterangan : ($det->jenis ?: 'Biaya Tambahan');
                if ($det->masuk_hpp) {
                    // Prioritaskan akun COA yang dipilih user ($det->coa_id) di tabel Biaya Tambahan
                    $coa = $det->coa_id ?? ($isFromGr ? $unbilledApCoaId : $defaultInventoryCoaId);
                    if (!isset($biayaHppRows[$coa])) {
                        $biayaHppRows[$coa] = ['total' => 0, 'keterangan' => $ketBiaya];
                    }
                    $biayaHppRows[$coa]['total'] += $nom;
                } else {
                    $coa = $det->coa_id ?? $unbilledApCoaId;
                    if (!isset($biayaNonHppRows[$coa])) {
                        $biayaNonHppRows[$coa] = ['total' => 0, 'keterangan' => $ketBiaya];
                    }
                    $biayaNonHppRows[$coa]['total'] += $nom;
                }
            }
        }

        // Helper format keterangan valas
        $valasSuffix = function($val) use ($isForeign, $curCode, $kurs) {
            if (!$isForeign) return '';
            return ' [' . $curCode . ' ' . number_format($val, 2, ',', '.') . ' @ Kurs ' . number_format($kurs, 2, ',', '.') . ']';
        };

        // ── Calculate Debit Lines in IDR ────────────────────────────────
        $totalDebitIdr = 0;
        $debitEntries = [];

        // 1. Barang (Penutupan Utang Belum Ditagih / GRNI per COA atau Pembelian Barang)
        foreach ($barangDebitRows as $coaId => $row) {
            $valIdr = round($row['total'] * $kurs, 2);
            if ($valIdr > 0) {
                $totalDebitIdr += $valIdr;
                $narration = $row['keterangan'] ?? 'Penutupan Utang Belum Ditagih (GRNI)';
                $debitEntries[] = [
                    'account_id' => $coaId,
                    'debit'      => $valIdr,
                    'kredit'     => 0,
                    'keterangan' => "{$narration} ({$invoice->nomor_faktur})" . $valasSuffix($row['total']),
                ];
            }
        }

        // 2. Biaya Masuk HPP
        foreach ($biayaHppRows as $coaId => $row) {
            $valIdr = round($row['total'] * $kurs, 2);
            if ($valIdr > 0) {
                $totalDebitIdr += $valIdr;
                $debitEntries[] = [
                    'account_id' => $coaId,
                    'debit'      => $valIdr,
                    'kredit'     => 0,
                    'keterangan' => "Alokasi Biaya HPP - " . ($row['keterangan'] ?? 'Biaya Tambahan') . " ({$invoice->nomor_faktur})" . $valasSuffix($row['total']),
                ];
            }
        }

        // 3. PPN Masukan
        $taxNominalIdr = round($taxNominal * $kurs, 2);
        if ($taxNominalIdr > 0) {
            $totalDebitIdr += $taxNominalIdr;
            $debitEntries[] = [
                'account_id' => $taxInputCoaId,
                'debit'      => $taxNominalIdr,
                'kredit'     => 0,
                'keterangan' => "PPN Masukan Pembelian ({$invoice->nomor_faktur})" . $valasSuffix($taxNominal),
            ];
        }

        // 4. Non-HPP extra costs
        foreach ($biayaNonHppRows as $coaId => $row) {
            $valIdr = round($row['total'] * $kurs, 2);
            if ($valIdr > 0) {
                $totalDebitIdr += $valIdr;
                $debitEntries[] = [
                    'account_id' => $coaId,
                    'debit'      => $valIdr,
                    'kredit'     => 0,
                    'keterangan' => ($row['keterangan'] ?? 'Biaya Lain-lain') . " ({$invoice->nomor_faktur})" . $valasSuffix($row['total']),
                ];
            }
        }

        // ── Calculate Credit Lines in IDR ───────────────────────────────
        $creditEntries = [];

        // 5. Utang PPh (PPh dipotong atas pembelian)
        $pphNominalIdr = round($pphNominal * $kurs, 2);
        if ($pphNominalIdr > 0 && $pphCoaId) {
            $pphLabel = $invoice->pphPajak?->nama_pajak ?? 'PPh';
            $creditEntries[] = [
                'account_id' => $pphCoaId,
                'debit'      => 0,
                'kredit'     => $pphNominalIdr,
                'keterangan' => "Utang {$pphLabel} dipotong - {$supplierName} ({$invoice->nomor_faktur})" . $valasSuffix($pphNominal),
            ];
        }

        // 6. Uang Muka (DP) Pembelian
        $uangMukaVal = floatval($invoice->uang_muka ?? 0);
        $uangMukaCoaId = $invoice->uang_muka_coa_id ?: $this->resolveDefaultUangMukaCoaId();
        $dpDeduction = min($grandTotal, $uangMukaVal);
        $dpDeductionIdr = round($dpDeduction * $kurs, 2);

        if ($dpDeductionIdr > 0 && $uangMukaCoaId) {
            $creditEntries[] = [
                'account_id' => $uangMukaCoaId,
                'debit'      => 0,
                'kredit'     => $dpDeductionIdr,
                'keterangan' => "Penutupan Uang Muka (DP) Pembelian - {$supplierName} ({$invoice->nomor_faktur})" . $valasSuffix($dpDeduction),
            ];
        }

        // 7. Utang Usaha / Accounts Payable (Sisa Tagihan setelah PPh & DP)
        // Penyeimbang persis agar Total Debit == Total Kredit (Nol Selisih Pembulatan)
        $sisaHutangIdr = max(0, round($totalDebitIdr - $pphNominalIdr - $dpDeductionIdr, 2));
        if ($sisaHutangIdr > 0 || ($pphNominalIdr == 0 && $dpDeductionIdr == 0)) {
            $sisaHutangValas = max(0, $grandTotal - $dpDeduction);
            $creditEntries[] = [
                'account_id' => $apCoaId,
                'debit'      => 0,
                'kredit'     => $sisaHutangIdr,
                'keterangan' => "Utang Usaha Supplier - {$supplierName} ({$invoice->nomor_faktur})" . $valasSuffix($sisaHutangValas),
            ];
        }

        $totalKreditIdr = 0;
        foreach ($creditEntries as $ce) {
            $totalKreditIdr += $ce['kredit'];
        }
        $totalJurnalIdr = max($totalDebitIdr, $totalKreditIdr);

        // ── Resolve Associated Tender (Direct Invoice, Goods Receipt, or PO) ─────
        $tenderObj = $invoice->tender ?? $invoice->goodsReceipt?->tender ?? $invoice->po?->tender;
        if (!$tenderObj && ($invoice->tender_id || $invoice->goodsReceipt?->tender_id || $invoice->po?->tender_id)) {
            $tId = $invoice->tender_id ?: ($invoice->goodsReceipt?->tender_id ?: $invoice->po?->tender_id);
            $tenderObj = \App\Models\Tender::find($tId);
        }

        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));

            if (empty($invoice->tender_id)) {
                $invoice->update(['tender_id' => $tenderObj->id]);
            }
        }

        $tenderSuffix = $nomorTender ? " [Tender: {$nomorTender}]" : "";

        // ── Create Journal Header ────────────────────────────────────────
        $ketHeader = "Jurnal Faktur Pembelian {$invoice->nomor_faktur} dari {$supplierName}";
        if ($isForeign) {
            $ketHeader .= " ({$curCode} " . number_format($grandTotal, 2, ',', '.') . " @ Kurs " . number_format($kurs, 2, ',', '.') . ")";
        }
        $ketHeader .= $tenderSuffix;

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $invoice->tanggal_faktur,
            'referensi_tipe' => 'Purchase Invoice',
            'referensi_id'   => $invoice->id,
            'no_tender'      => $nomorTender,
            'branch_id'      => $invoice->branch_id,
            'company_id'     => $invoice->branch_id ? null : ($invoice->company_id ?: session('active_company_id')),
            'total_debit'    => $totalJurnalIdr,
            'total_kredit'   => $totalJurnalIdr,
            'keterangan'     => $ketHeader,
            'created_by'     => auth()->id(),
        ]);

        // ── Insert Journal Lines ────────────────────────────────────────
        foreach ($debitEntries as $de) {
            $de['jurnal_id'] = $jurnal->id;
            JurnalDetail::create($de);
        }

        foreach ($creditEntries as $ce) {
            $ce['jurnal_id'] = $jurnal->id;
            JurnalDetail::create($ce);
        }
    }

    /**
     * Compress and store invoice attachment image into KB size (~50-250 KB) with strict security sanitization.
     * Re-encodes images via GD into pure WebP to strip embedded webshells, EXIF exploits, or polyglots.
     * Validates PDF magic bytes (%PDF) to prevent masquerading executable files.
     */
    protected function compressAndStoreAttachment($file, $directory = 'purchase_invoices'): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            $extension = strtolower($file->guessExtension() ?? '');
            if (!in_array($extension, $allowedExtensions)) {
                throw new \InvalidArgumentException('Format berkas lampiran tidak diizinkan. Hanya format JPG, JPEG, PNG, dan PDF yang diperbolehkan.');
            }
        }

        $fullDirPath = storage_path('app/public/' . $directory);
        if (!file_exists($fullDirPath)) {
            @mkdir($fullDirPath, 0755, true);
        }

        // ── 1. Penanganan Dokumen PDF Aman ───────────────────────────────
        if ($extension === 'pdf') {
            $realPath = $file->getRealPath();
            $header = @file_get_contents($realPath, false, null, 0, 5);
            if (strpos((string) $header, '%PDF') !== 0) {
                throw new \InvalidArgumentException('Berkas PDF tidak valid atau rusak!');
            }

            $filename = 'PI_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
            return $file->storeAs($directory, $filename, 'public');
        }

        // ── 2. Penanganan & Sanitasi Gambar (Re-Encoding Anti-Webshell) ─
        $imageInfo = @getimagesize($file->getRealPath());
        if (!$imageInfo) {
            throw new \InvalidArgumentException('Berkas gambar tidak valid atau terdeteksi korup!');
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
                $srcImage = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : null;
                break;
            default:
                throw new \InvalidArgumentException('Tipe gambar tidak didukung atau berisiko keamanan.');
        }

        if (!$srcImage) {
            throw new \InvalidArgumentException('Gagal memproses gambar. Format berkas tidak valid.');
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

        // Preserve transparency
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Re-encoding gambar ke WebP (78% quality) membersihkan seluruh payload tersembunyi / webshell
        $randomHash = bin2hex(random_bytes(8));
        if (function_exists('imagewebp')) {
            $filename     = 'PI_' . date('Ymd_His') . '_' . $randomHash . '.webp';
            $targetPath   = $fullDirPath . '/' . $filename;
            $relativePath = $directory . '/' . $filename;
            imagewebp($dstImage, $targetPath, 78);
        } else {
            $filename     = 'PI_' . date('Ymd_His') . '_' . $randomHash . '.jpg';
            $targetPath   = $fullDirPath . '/' . $filename;
            $relativePath = $directory . '/' . $filename;
            imagejpeg($dstImage, $targetPath, 80);
        }

        @imagedestroy($srcImage);
        @imagedestroy($dstImage);

        return $relativePath;
    }

    /**
     * Rollback Mutasi Kas Pengeluaran & Kembalikan Saldo KasBank untuk Uang Muka (DP) Faktur Pembelian
     */
    private function rollbackInvoiceDpKasMutation(PurchaseInvoice $invoice)
    {
        $dpMutasis = KasMutasi::withTrashed()
            ->where('nomor_referensi', $invoice->nomor_faktur)
            ->where('kategori', 'Pengeluaran Uang Muka')
            ->get();

        foreach ($dpMutasis as $dpMutasi) {
            $kasBank = KasBank::whereKey($dpMutasi->kas_bank_id)->lockForUpdate()->first();
            if ($kasBank) {
                $kasBank->saldo_berjalan = (float) $kasBank->saldo_berjalan + (float) $dpMutasi->jumlah;
                $kasBank->save();
            }
            // Hapus jurnal GL pengeluaran DP yang tertaut agar Buku Besar Kas & Uang Muka kembali seimbang
            if ($dpMutasi->jurnal_id) {
                $dpJurnal = Jurnal::withTrashed()->find($dpMutasi->jurnal_id);
                if ($dpJurnal) {
                    $dpJurnal->details()->delete();
                    $dpJurnal->forceDelete();
                }
            }
            $dpMutasi->forceDelete();
        }
    }

    /**
     * Catat Mutasi Kas Keluar & Kurangi Saldo KasBank untuk Uang Muka (DP) Faktur Pembelian
     */
    private function syncInvoiceDpKasMutation(PurchaseInvoice $invoice)
    {
        $dpNominal = (float) ($invoice->uang_muka ?? 0);
        if ($dpNominal <= 0) {
            return;
        }

        // DP hanya memotong kas/bank jika status faktur sudah disetujui/posted
        if (in_array($invoice->status, ['Draft', 'Menunggu Persetujuan', 'Menunggu Approval', 'Rejected', 'Void'])) {
            return;
        }

        $existingDpMutasi = KasMutasi::where('nomor_referensi', $invoice->nomor_faktur)
            ->where('kategori', 'Pengeluaran Uang Muka')
            ->first();

        if ($existingDpMutasi) {
            return; // Sudah pernah diposting ke Kas & Bank
        }

        // Cari akun Kas & Bank yang dituju dengan row lock untuk mencegah race condition
        $kasBank = null;
        if ($invoice->kas_bank_id) {
            $kasBank = KasBank::whereKey($invoice->kas_bank_id)->lockForUpdate()->first();
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
            $kasBank = $kasBankQuery->where('is_active', true)->lockForUpdate()->first();
        }

        if (!$kasBank) {
            $kasBank = KasBank::where('is_active', true)->lockForUpdate()->first();
        }

        if (!$kasBank) {
            Log::warning("Tidak dapat mencatat mutasi DP untuk Faktur Pembelian {$invoice->nomor_faktur}: Akun Kas/Bank tidak ditemukan.");
            return;
        }

        $mPrefix = "KK-" . date('ym') . "-";
        $latestM = KasMutasi::withTrashed()->where('nomor_transaksi', 'like', $mPrefix . '%')->orderByDesc('id')->first();
        $mNum = 1;
        if ($latestM && preg_match('/(\d+)$/', $latestM->nomor_transaksi, $matches)) {
            $mNum = ((int) $matches[1]) + 1;
        }
        $nomorTransaksi = $mPrefix . str_pad($mNum, 4, '0', STR_PAD_LEFT);

        $kurs = (float) ($invoice->kurs ?: 1);
        $curObj = $invoice->currency ?? $invoice->po?->currency;
        $currCode = strtoupper($curObj?->kode_currency ?? ($curObj?->code ?? 'IDR'));
        $isForeign = ($currCode !== 'IDR' && $currCode !== '') || abs($kurs - 1.0) > 0.0001;
        $dpNominalKeluar = round($dpNominal * $kurs, 2);

        $saldoSebelum = (float) ($kasBank->saldo_berjalan ?? 0);
        if ($saldoSebelum < $dpNominalKeluar) {
            throw new \Exception("Saldo pada rekening Kas/Bank \"{$kasBank->nama_akun}\" tidak mencukupi untuk pengeluaran Uang Muka (DP)! Saldo saat ini: Rp " . number_format($saldoSebelum, 2, ',', '.') . ", sedangkan DP yang dibutuhkan: Rp " . number_format($dpNominalKeluar, 2, ',', '.') . ".");
        }
        $saldoSesudah = $saldoSebelum - $dpNominalKeluar;

        $dpCoaId = $invoice->uang_muka_coa_id ?: $this->resolveDefaultUangMukaCoaId();
        $supplierName = trim($invoice->supplier?->nama_perusahaan ?? '') ?: ($invoice->supplier?->nama_supplier ?? 'Supplier');

        $keteranganMutasi = "Pengeluaran Uang Muka (DP) atas Faktur Pembelian {$invoice->nomor_faktur}";
        if ($isForeign) {
            $keteranganMutasi .= " [{$currCode} " . number_format($dpNominal, 2, ',', '.') . " @ Kurs " . number_format($kurs, 2, ',', '.') . "]";
        }

        $mutasi = KasMutasi::create([
            'nomor_transaksi' => $nomorTransaksi,
            'kas_bank_id'     => $kasBank->id,
            'tipe'            => 'Keluar',
            'kategori'        => 'Pengeluaran Uang Muka',
            'lawan_coa_id'    => $dpCoaId,
            'jumlah'          => $dpNominalKeluar,
            'saldo_sebelum'   => $saldoSebelum,
            'saldo_sesudah'   => $saldoSesudah,
            'tanggal'         => $invoice->tanggal_faktur,
            'nomor_referensi' => $invoice->nomor_faktur,
            'kontak_tipe'     => 'Supplier',
            'kontak_id'       => $invoice->supplier_id,
            'nama_kontak'     => $supplierName,
            'keterangan'      => $keteranganMutasi,
            'company_id'      => $companyId,
            'branch_id'       => $branchId,
            'created_by'      => auth()->id(),
        ]);

        $kasBank->saldo_berjalan = $saldoSesudah;
        $kasBank->save();

        // ── Jurnal GL Pengeluaran DP: DEBIT Uang Muka Pembelian / KREDIT Kas-Bank ──
        // Tanpa jurnal ini, jurnal faktur (yang mengkredit Uang Muka) membuat saldo Uang Muka minus
        // dan saldo Kas di Buku Besar tidak berkurang walau saldo_berjalan rekening sudah dipotong.
        $kasCoaId = $kasBank->coa_id;
        if (!$kasCoaId) {
            $kasCoaId = AccountMappingHelper::getCoaId('default', 'default_cash_bank', 'cash')
                ?? ChartOfAccount::forActiveCompany()->detailOnly()->where('type', 'Asset')->where(function($q) {
                    $q->where('code', 'like', '1101%')->orWhere('code', 'like', '1102%')->orWhere('name', 'like', '%Kas%')->orWhere('name', 'like', '%Bank%');
                })->value('id');
        }
        if ($kasCoaId && $dpCoaId && $dpNominalKeluar > 0) {
            $tglJurnal = $invoice->tanggal_faktur ? date('Y-m-d', strtotime($invoice->tanggal_faktur)) : now()->toDateString();
            $prefix = 'JNL/' . date('Y', strtotime($tglJurnal)) . '/' . date('m', strtotime($tglJurnal)) . '/';
            $latestJnl = Jurnal::withTrashed()->where('nomor_jurnal', 'like', $prefix . '%')->orderByDesc('id')->first();
            $nextNum = 1;
            if ($latestJnl && preg_match('/(\d+)$/', $latestJnl->nomor_jurnal, $m)) {
                $nextNum = ((int) $m[1]) + 1;
            }
            do {
                $nomorJurnal = $prefix . str_pad($nextNum++, 4, '0', STR_PAD_LEFT);
            } while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists());

            $dpJurnal = Jurnal::create([
                'nomor_jurnal'   => $nomorJurnal,
                'tanggal_jurnal' => $tglJurnal,
                'referensi_tipe' => 'Purchase Invoice DP',
                'referensi_id'   => $invoice->id,
                'no_tender'      => $this->resolveInvoiceTenderLabel($invoice),
                'branch_id'      => $invoice->branch_id,
                'company_id'     => $invoice->branch_id ? null : ($invoice->company_id ?: session('active_company_id')),
                'total_debit'    => $dpNominalKeluar,
                'total_kredit'   => $dpNominalKeluar,
                'keterangan'     => $keteranganMutasi . " ({$nomorTransaksi})",
                'created_by'     => auth()->id(),
            ]);
            JurnalDetail::create([
                'jurnal_id'  => $dpJurnal->id,
                'account_id' => $dpCoaId,
                'debit'      => $dpNominalKeluar,
                'kredit'     => 0,
                'keterangan' => "Uang Muka (DP) Pembelian - {$supplierName} ({$invoice->nomor_faktur})",
            ]);
            JurnalDetail::create([
                'jurnal_id'  => $dpJurnal->id,
                'account_id' => $kasCoaId,
                'debit'      => 0,
                'kredit'     => $dpNominalKeluar,
                'keterangan' => "Pembayaran DP via {$kasBank->nama_akun} ({$nomorTransaksi})",
            ]);
            $mutasi->update(['jurnal_id' => $dpJurnal->id]);
        } else {
            Log::warning("Jurnal DP Faktur {$invoice->nomor_faktur} tidak dibuat: COA Kas/Bank atau COA Uang Muka belum dipetakan.");
        }
    }

    /**
     * Label Tender (nomor kontrak - nama tender) untuk kolom no_tender pada jurnal.
     */
    private function resolveInvoiceTenderLabel(PurchaseInvoice $invoice): ?string
    {
        $tenderObj = $invoice->tender ?? $invoice->goodsReceipt?->tender ?? $invoice->po?->tender;
        if (!$tenderObj) {
            $tId = $invoice->tender_id ?: ($invoice->goodsReceipt?->tender_id ?: $invoice->po?->tender_id);
            $tenderObj = $tId ? \App\Models\Tender::find($tId) : null;
        }
        if (!$tenderObj) {
            return null;
        }
        return !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
            ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
            : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));
    }

    /**
     * Clear KPI Summary Cache instantly on invoice mutations.
     */
    public static function clearKpiCache(): void
    {
        try {
            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');
            $userId          = auth()->id();
            
            $cacheKey = 'pi_kpi_summary_' . ($activeBranchId ?: 'all') . '_' . ($activeCompanyId ?: 'all') . '_' . ($userId ?: '0');
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            \Illuminate\Support\Facades\Cache::forget('pi_kpi_summary_all_all_0');
        } catch (\Throwable $e) {
            // Silently ignore cache forget exceptions
        }
    }
}


