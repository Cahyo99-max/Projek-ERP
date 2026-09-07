<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    protected $table = 'bom_items';

    protected $fillable = [
        'bom_id',
        'barang_mentah_id',
        'jumlah_kebutuhan',
        'satuan_id',
        'waste_percent',
        'estimasi_harga_satuan',
        'estimasi_subtotal',
        'catatan',
    ];

    protected $casts = [
        'jumlah_kebutuhan'      => 'decimal:4',
        'waste_percent'         => 'decimal:2',
        'estimasi_harga_satuan' => 'decimal:6',
        'estimasi_subtotal'     => 'decimal:4',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function barangMentah(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_mentah_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
