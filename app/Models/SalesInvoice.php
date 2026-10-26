<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'nomor_faktur',
        'nomor_faktur_pajak',
        'tanggal_faktur',
        'tanggal_jatuh_tempo',
        'sales_order_id',
        'delivery_order_id',
        'tender_id',
        'customer_id',
        'branch_id',
        'company_id',
        'currency_id',
        'kurs',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'pajak_id',
        'pajak_nominal',
        'pph_pajak_id',
        'tarif_pph',
        'pph_nominal',
        'pph_coa_id',
        'biaya_lain',
        'biaya_lain_coa_id',
        'grand_total',
        'uang_muka',
        'uang_muka_coa_id',
        'kas_bank_id',
        'sisa_tagihan',
        'status',
        'approved_by',
        'approved_at',
        'alasan_penolakan',
        'payment_term_id',
        'catatan',
        'file_pendukung',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_faktur'      => 'date:Y-m-d',
        'tanggal_jatuh_tempo' => 'date:Y-m-d',
        'approved_at'         => 'datetime',
        'kurs'                => 'decimal:4',
        'subtotal'            => 'decimal:2',
        'diskon_persen'       => 'decimal:2',
        'diskon_nominal'      => 'decimal:2',
        'pajak_nominal'       => 'decimal:2',
        'tarif_pph'           => 'decimal:2',
        'pph_nominal'         => 'decimal:2',
        'biaya_lain'          => 'decimal:2',
        'grand_total'         => 'decimal:2',
        'uang_muka'           => 'decimal:2',
        'sisa_tagihan'        => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            if (!empty($model->branch_id) || !empty($activeBranchId)) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesInvoiceDetail::class, 'sales_invoice_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(SalesInvoiceCost::class, 'sales_invoice_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    /**
     * Dapatkan semua Delivery Order terkait faktur ini, baik via delivery_order_id langsung maupun via salesOrder->deliveryOrders
     */
    public function getRelatedDeliveryOrders()
    {
        $dos = collect();
        if ($this->deliveryOrder) {
            $dos->push($this->deliveryOrder);
        }
        if ($this->salesOrder) {
            // Gunakan relasi yang sudah ter-load atau query jika belum
            $soDos = $this->salesOrder->deliveryOrders ?? $this->salesOrder->deliveryOrders()->get();
            if ($soDos) {
                foreach ($soDos as $do) {
                    if (!$dos->contains('id', $do->id)) {
                        $dos->push($do);
                    }
                }
            }
        }
        return $dos;
    }

    /**
     * Accessor untuk daftar nomor DO (Surat Jalan) terhubung
     */
    public function getNomorDoListAttribute(): string
    {
        $dos = $this->getRelatedDeliveryOrders();
        return $dos->isNotEmpty() ? $dos->pluck('nomor_do')->filter()->unique()->implode(', ') : '';
    }

    /**
     * Accessor untuk nomor resi pengiriman jika ada ekspedisi
     */
    public function getNomorResiListAttribute(): string
    {
        $dos = $this->getRelatedDeliveryOrders();
        $resis = $dos->map(function ($do) {
            if ($do->nomor_resi) {
                return $do->nama_ekspedisi ? "{$do->nama_ekspedisi}: {$do->nomor_resi}" : $do->nomor_resi;
            }
            return null;
        })->filter()->unique();

        return $resis->isNotEmpty() ? $resis->implode(', ') : '';
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(MasterCurrency::class, 'currency_id');
    }

    public function pajak(): BelongsTo
    {
        return $this->belongsTo(MasterPajak::class, 'pajak_id');
    }

    public function pphPajak(): BelongsTo
    {
        return $this->belongsTo(MasterPajak::class, 'pph_pajak_id');
    }

    public function pphCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'pph_coa_id');
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function biayaLainCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'biaya_lain_coa_id');
    }

    public function uangMukaCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'uang_muka_coa_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Sales Invoice');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(SalesPaymentDetail::class, 'sales_invoice_id');
    }

    public function fakturPajakInvoices(): HasMany
    {
        return $this->hasMany(FakturPajakInvoice::class, 'sales_invoice_id');
    }

    public function fakturPajaks(): BelongsToMany
    {
        return $this->belongsToMany(FakturPajak::class, 'faktur_pajak_invoices', 'sales_invoice_id', 'faktur_pajak_id')
            ->withPivot('dpp', 'ppn')
            ->withTimestamps();
    }

    public function getPaidAmountAttribute(): float
    {
        $dp = (float) ($this->uang_muka ?? 0);
        if ($this->relationLoaded('paymentDetails')) {
            $paymentSum = (float) $this->paymentDetails
                ->filter(function($detail) {
                    return $detail->payment && $detail->payment->status === 'Posted';
                })
                ->sum(function($d) { return (float) $d->jumlah_bayar + (float) $d->potongan; });
            return $dp + $paymentSum;
        }
        $paymentSum = (float) $this->paymentDetails()
            ->whereHas('payment', function($q) {
                $q->where('status', 'Posted');
            })
            ->sum(DB::raw('jumlah_bayar + potongan'));

        return $dp + $paymentSum;
    }

    /**
     * Total pembayaran aktif yang sudah dicatat/dialokasikan (baik status Posted maupun Draft aktif).
     * Mencegah faktur yang sedang/sudah dibayar penuh dipilih kembali (double payment).
     */
    public function getAllocatedPaymentAmountAttribute(): float
    {
        if ($this->relationLoaded('paymentDetails')) {
            return (float) $this->paymentDetails
                ->filter(function($detail) {
                    return $detail->payment 
                        && $detail->payment->status !== 'Cancelled'
                        && empty($detail->payment->deleted_at);
                })
                ->sum(function($d) { return (float) $d->jumlah_bayar + (float) $d->potongan; });
        }
        return (float) $this->paymentDetails()
            ->whereHas('payment', function($q) {
                $q->where('status', '!=', 'Cancelled');
            })
            ->sum(DB::raw('jumlah_bayar + potongan'));
    }

    public function getRemainingBalanceAttribute(): float
    {
        if (in_array($this->status, ['Paid', 'Cancelled'])) {
            return 0.0;
        }
        $allocated = $this->allocated_payment_amount;
        $initialReceivable = max(0, (float) $this->grand_total - (float) ($this->uang_muka ?? 0));
        return max(0, $initialReceivable - $allocated);
    }

    public static function generateNomorFaktur($companyId = null, $branchId = null, $tanggalFaktur = null)
    {
        $activeBranchId = $branchId !== null ? $branchId : session('active_branch_id');
        $date = $tanggalFaktur ? \Carbon\Carbon::parse($tanggalFaktur) : now();
        $year = $date->format('Y');
        $month = $date->format('m');

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            $prefixEntity = $branch ? ($branch->kode_cabang ?: 'CBG') : 'CBG';
        } else {
            $companyId = $companyId ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1));
            $company = Company::find($companyId);
            $prefixEntity = $company ? ($company->kode_perusahaan ?: 'CP') : 'CP';
        }

        $prefix = "INV-SLS/{$prefixEntity}/{$year}/{$month}/";

        // Lock to ensure concurrency safety across simultaneous requests
        $lockKey = 'invoice_generate_lock_' . md5($prefix);
        $gotLock = false;
        try {
            $lockRes = DB::select("SELECT GET_LOCK(?, 10) as lk", [$lockKey]);
            $gotLock = !empty($lockRes) && $lockRes[0]->lk == 1;

            // Query the highest sequence directly using indexed column and limit 1
            $lastInvoice = self::withTrashed()
                ->where('nomor_faktur', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('nomor_faktur');

            $maxSeq = 0;
            if ($lastInvoice) {
                $parts = explode('/', $lastInvoice);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    $maxSeq = (int) $lastPart;
                }
            } else {
                // Fallback for legacy format INV-SLS/YYYY/MM/ if no branch-prefixed invoices exist yet
                $legacyPrefix = "INV-SLS/{$year}/{$month}/";
                $lastLegacy = self::withTrashed()
                    ->where('nomor_faktur', 'like', $legacyPrefix . '%')
                    ->orderByDesc('id')
                    ->value('nomor_faktur');

                if ($lastLegacy) {
                    $parts = explode('/', $lastLegacy);
                    $lastPart = end($parts);
                    if (is_numeric($lastPart)) {
                        $maxSeq = (int) $lastPart;
                    }
                }
            }

            return $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
        } finally {
            if ($gotLock) {
                DB::select("SELECT RELEASE_LOCK(?)", [$lockKey]);
            }
        }
    }

    /**
     * Scope for multi-company tenancy & branch isolation
     * - Jika aktif sebagai Cabang (branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika aktif sebagai PT Pusat (branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }
}
