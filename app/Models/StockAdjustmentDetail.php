<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_id',
        'barang_id',
        'jumlah',
        'harga_satuan',
        'nilai_total',
        'keterangan_detail',
        'serial_numbers',
    ];

    protected $casts = [
        'jumlah'         => 'decimal:2',
        'harga_satuan'   => 'decimal:4',
        'nilai_total'    => 'decimal:4',
        'serial_numbers' => 'array',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
