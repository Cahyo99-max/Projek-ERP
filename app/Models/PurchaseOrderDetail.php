<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_details';

    protected $fillable = [
        'po_id',
        'barang_id',
        'satuan_id',
        'pr_detail_id',
        'jumlah',
        'faktor_pengali',
        'jumlah_dasar',
        'harga_satuan',
        'diskon_item',
        'subtotal',
        'keterangan',
    ];

    protected $casts = [
        'jumlah'         => 'decimal:4',
        'faktor_pengali' => 'decimal:4',
        'jumlah_dasar'   => 'decimal:4',
        'harga_satuan'   => 'decimal:2',
        'diskon_item'    => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
    public function satuan(): BelongsTo { return $this->belongsTo(MasterSatuan::class, 'satuan_id'); }
    public function prDetail(): BelongsTo { return $this->belongsTo(PurchaseRequisitionDetail::class, 'pr_detail_id'); }
}
