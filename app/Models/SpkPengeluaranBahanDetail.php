<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkPengeluaranBahanDetail extends Model
{
    use HasFactory;

    protected $table = 'spk_pengeluaran_bahan_details';

    protected $fillable = [
        'pengeluaran_id',
        'spk_material_id',
        'barang_mentah_id',
        'satuan_id',
        'qty_total_spk',
        'qty_sudah_diambil',
        'qty_sisa_kebutuhan',
        'qty_diambil_sekarang',
        'catatan',
    ];

    protected $casts = [
        'qty_total_spk'        => 'decimal:4',
        'qty_sudah_diambil'    => 'decimal:4',
        'qty_sisa_kebutuhan'   => 'decimal:4',
        'qty_diambil_sekarang' => 'decimal:4',
    ];

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(SpkPengeluaranBahan::class, 'pengeluaran_id');
    }

    public function spkMaterial(): BelongsTo
    {
        return $this->belongsTo(SpkProduksiMaterial::class, 'spk_material_id');
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
