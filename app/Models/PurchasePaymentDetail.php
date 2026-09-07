<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePaymentDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_payment_details';

    protected $fillable = [
        'purchase_payment_id',
        'purchase_invoice_id',
        'total_faktur',
        'sisa_utang',
        'jumlah_bayar',
        'potongan',
        'sisa_akhir',
        'catatan',
    ];

    protected $casts = [
        'total_faktur' => 'decimal:2',
        'sisa_utang'   => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'potongan'     => 'decimal:2',
        'sisa_akhir'   => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PurchasePayment::class, 'purchase_payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }
}
