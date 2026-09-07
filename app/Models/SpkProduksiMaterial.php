<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkProduksiMaterial extends Model
{
    protected $table = 'spk_produksi_materials';

    protected $fillable = [
        'spk_id',
        'barang_mentah_id',
        'satuan_id',
        'kebutuhan_per_unit',
        'waste_percent',
        'total_kebutuhan',
        'total_keluar_gudang',
        'estimasi_harga_satuan',
        'estimasi_subtotal',
        'catatan',
    ];

    protected $casts = [
        'kebutuhan_per_unit'    => 'decimal:4',
        'waste_percent'         => 'decimal:2',
        'total_kebutuhan'       => 'decimal:4',
        'total_keluar_gudang'   => 'decimal:4',
        'estimasi_harga_satuan' => 'decimal:4',
        'estimasi_subtotal'     => 'decimal:4',
    ];

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
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
