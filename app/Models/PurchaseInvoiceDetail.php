<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice_details';

    protected $fillable = [
        'purchase_invoice_id',
        'jenis',
        'keterangan',
        'coa_id',
        'masuk_hpp',
        'gr_detail_id',
        'barang_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'jumlah'      => 'decimal:4',
        'harga_satuan'=> 'decimal:2',
        'subtotal'    => 'decimal:2',
        'masuk_hpp'   => 'boolean',
    ];


    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function grDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptDetail::class, 'gr_detail_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
