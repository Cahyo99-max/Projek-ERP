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
        'supplier_id',
        'branch_id',
        'currency_id',
        'kurs',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'pajak_id',
        'pajak_nominal',
        'biaya_lain',
        'grand_total',
        'status',
        'payment_term_id',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_faktur'      => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'kurs'                => 'decimal:4',
        'subtotal'            => 'decimal:2',
        'diskon_persen'       => 'decimal:2',
        'diskon_nominal'      => 'decimal:2',
        'pajak_nominal'       => 'decimal:2',
        'biaya_lain'          => 'decimal:2',
        'grand_total'         => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
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
        return (float) $this->paymentDetails()
            ->whereHas('payment', function ($q) {
                $q->where('status', 'Posted');
            })
            ->sum(DB::raw('jumlah_bayar + potongan'));
    }

    public function getRemainingBalanceAttribute(): float
    {
        $paid = $this->paid_amount;
        return max(0, (float) $this->grand_total - $paid);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Purchase Invoice');
    }
}
