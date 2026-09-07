<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningStockDetail extends Model
{
    protected $table = 'opening_stock_details';

    protected $fillable = [
        'opening_stock_id',
        'barang_id',
        'jumlah',
        'harga_pokok',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(OpeningStock::class, 'opening_stock_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
