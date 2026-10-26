<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class PurchaseInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_invoices';

    protected $fillable = [
        'nomor_faktur',
        'nomor_faktur_supplier',
        'tanggal_faktur',
        'tanggal_jatuh_tempo',
        'po_id',
        'gr_id',
        'tender_id',
        'supplier_id',
        'company_id',
        'branch_id',
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
        'grand_total',
        'uang_muka',
        'uang_muka_coa_id',
        'kas_bank_id',
        'lampiran_faktur',
        'status',
        'payment_term_id',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'approved_by',
        'approved_at',
        'alasan_penolakan',
        'cancelled_by',
        'cancelled_at',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'tanggal_faktur'      => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'approved_at'         => 'datetime',
        'cancelled_at'        => 'datetime',
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
    ];

    protected $appends = [
        'lampiran_url',
    ];

    public function getLampiranUrlAttribute(): ?string
    {
        return $this->lampiran_faktur ? asset($this->lampiran_faktur) : null;
    }

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
            } elseif (!empty($model->company_id)) {
                $model->branch_id = null;
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

    public function scopeForActiveCompany($query)
    {
        $branchId  = session('active_branch_id');
        $companyId = session('active_company_id') ?: auth()->user()?->company_id;
        $table = $this->getTable();

        if (!empty($branchId)) {
            $query->where($table . '.branch_id', $branchId)
                  ->whereNull($table . '.company_id');
        } else {
            $query->whereNull($table . '.branch_id');
            if (!empty($companyId)) {
                $query->where($table . '.company_id', $companyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('purchase_invoices.branch_id', intval($branchId));
        }
        return $query->whereNull('purchase_invoices.branch_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_id');
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'gr_id');
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
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

    public function uangMukaCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'uang_muka_coa_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(PurchasePaymentDetail::class, 'purchase_invoice_id');
    }

    public function getPaidAmountAttribute(): float
    {
        $dp = (float) ($this->uang_muka ?? 0);
        if ($this->relationLoaded('paymentDetails')) {
            $paymentSum = (float) $this->paymentDetails
                ->filter(function ($d) {
                    $p = $d->payment;
                    return $p && $p->status === 'Posted' && !$p->trashed();
                })
                ->sum(function ($d) {
                    return (float) $d->jumlah_bayar + (float) ($d->potongan ?? 0);
                });
        } else {
            $paymentSum = (float) $this->paymentDetails()
                ->whereHas('payment', function ($q) {
                    $q->where('status', 'Posted')->whereNull('deleted_at');
                })
                ->sum(DB::raw('jumlah_bayar + potongan'));
        }

        return $dp + $paymentSum;
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->grand_total - $this->paid_amount);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Purchase Invoice');
    }

    public function reversalJurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Purchase Invoice Reversal');
    }
}
