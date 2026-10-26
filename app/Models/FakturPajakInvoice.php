<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FakturPajakInvoice extends Model
{
    use HasFactory;

    protected $table = 'faktur_pajak_invoices';

    protected $fillable = [
        'faktur_pajak_id',
        'sales_invoice_id',
        'dpp',
        'ppn',
    ];

    protected $casts = [
        'dpp' => 'decimal:2',
        'ppn' => 'decimal:2',
    ];

    public function fakturPajak(): BelongsTo
    {
        return $this->belongsTo(FakturPajak::class, 'faktur_pajak_id');
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }
}
