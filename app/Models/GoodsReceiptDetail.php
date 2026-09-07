<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends Model
{
    use HasFactory;

    protected $table = 'goods_receipt_details';

    protected $fillable = [
        'gr_id',
        'po_detail_id',
        'barang_id',
        'lokasi_rak_id',
        'satuan_id',
        'faktor_pengali',
        'jumlah',
        'jumlah_dasar',
        'jumlah_reject',
        'jumlah_dasar_reject',
    ];

    protected $casts = [
        'faktor_pengali'      => 'decimal:4',
        'jumlah'              => 'decimal:4',
        'jumlah_dasar'        => 'decimal:4',
        'jumlah_reject'       => 'decimal:4',
        'jumlah_dasar_reject' => 'decimal:4',
    ];

    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class, 'gr_id'); }
    public function poDetail(): BelongsTo { return $this->belongsTo(PurchaseOrderDetail::class, 'po_detail_id'); }
    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
    public function satuan(): BelongsTo { return $this->belongsTo(MasterSatuan::class, 'satuan_id'); }
    public function lokasiRak(): BelongsTo { return $this->belongsTo(MasterLokasiRak::class, 'lokasi_rak_id'); }
    public function fixedAssets() { return $this->hasMany(FixedAsset::class, 'gr_detail_id'); }
}

