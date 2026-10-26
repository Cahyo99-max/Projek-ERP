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
        'po_detail_id',
        'barang_id',
        'jumlah',
        'harga_satuan',
        'diskon_item',
        'subtotal',
        'ppn_pajak_id',
        'tarif_ppn',
        'ppn_nominal',
    ];

    protected $casts = [
        'jumlah'      => 'decimal:4',
        'harga_satuan'=> 'decimal:2',
        'diskon_item' => 'decimal:2',
        'subtotal'    => 'decimal:2',
        'tarif_ppn'   => 'decimal:2',
        'ppn_nominal' => 'decimal:2',
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

    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'po_detail_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    public function ppnPajak(): BelongsTo
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function pajak(): BelongsTo
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }
}
