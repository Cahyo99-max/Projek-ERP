<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPaymentDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_payment_details';

    protected $fillable = [
        'sales_payment_id',
        'sales_invoice_id',
        'total_faktur',
        'sisa_piutang',
        'jumlah_bayar',
        'potongan',
        'sisa_akhir',
        'catatan',
    ];

    protected $casts = [
        'total_faktur' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'potongan'     => 'decimal:2',
        'sisa_akhir'   => 'decimal:2',
    ];

    protected $appends = ['lebih_bayar'];

    public function getLebihBayarAttribute(): float
    {
        $paid = (float)$this->jumlah_bayar + (float)$this->potongan;
        $diff = $paid - (float)$this->sisa_piutang;
        return $diff > 0.01 ? round($diff, 2) : 0.0;
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SalesPayment::class, 'sales_payment_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
