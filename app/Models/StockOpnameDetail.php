<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_details';

    protected $fillable = [
        'opname_id',
        'barang_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'catatan',
        'serial_numbers',
        'serial_numbers_sistem',
    ];

    protected $casts = [
        'stok_sistem'           => 'decimal:2',
        'stok_fisik'            => 'decimal:2',
        'selisih'               => 'decimal:2',
        'serial_numbers'        => 'array',
        'serial_numbers_sistem' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────
    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
