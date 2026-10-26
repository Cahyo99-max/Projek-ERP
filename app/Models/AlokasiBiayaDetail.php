<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiBiayaDetail extends Model
{
    use HasFactory;

    protected $table = 'alokasi_biaya_details';

    protected $fillable = [
        'alokasi_biaya_id',
        'gr_id',
        'gr_detail_id',
        'barang_id',
        'qty_diterima',
        'satuan_nama',
        'harga_beli_satuan',
        'nilai_barang',
        'porsi_persen',
        'biaya_dialokasikan',
        'biaya_per_unit',
        'hpp_awal',
        'hpp_baru',
    ];

    protected $casts = [
        'qty_diterima'       => 'decimal:4',
        'harga_beli_satuan'  => 'decimal:4',
        'nilai_barang'       => 'decimal:2',
        'porsi_persen'       => 'decimal:4',
        'biaya_dialokasikan' => 'decimal:2',
        'biaya_per_unit'     => 'decimal:4',
        'hpp_awal'           => 'decimal:4',
        'hpp_baru'           => 'decimal:4',
    ];

    public function alokasiBiaya(): BelongsTo { return $this->belongsTo(AlokasiBiaya::class, 'alokasi_biaya_id'); }
    public function goodsReceipt(): BelongsTo { return $this->belongsTo(GoodsReceipt::class, 'gr_id'); }
    public function grDetail(): BelongsTo { return $this->belongsTo(GoodsReceiptDetail::class, 'gr_detail_id'); }
    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
}
