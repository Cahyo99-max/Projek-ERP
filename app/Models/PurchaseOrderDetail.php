<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'diskon_persen',
        'diskon_tipe',
        'subtotal',
        'ppn_pajak_id',
        'tarif_ppn',
        'ppn_nominal',
        'keterangan',
    ];

    protected $casts = [
        'jumlah'         => 'decimal:4',
        'faktor_pengali' => 'decimal:4',
        'jumlah_dasar'   => 'decimal:4',
        'harga_satuan'   => 'decimal:2',
        'diskon_item'    => 'decimal:2',
        'diskon_persen'  => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'tarif_ppn'      => 'decimal:2',
        'ppn_nominal'    => 'decimal:2',
    ];

    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
    public function satuan(): BelongsTo { return $this->belongsTo(MasterSatuan::class, 'satuan_id'); }
    public function prDetail(): BelongsTo { return $this->belongsTo(PurchaseRequisitionDetail::class, 'pr_detail_id'); }
    public function ppnPajak(): BelongsTo { return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id'); }
    public function pajak(): BelongsTo { return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id'); }
    public function grDetails(): HasMany { return $this->hasMany(GoodsReceiptDetail::class, 'po_detail_id'); }
}
