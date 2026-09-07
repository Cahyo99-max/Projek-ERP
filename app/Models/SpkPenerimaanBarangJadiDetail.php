<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkPenerimaanBarangJadiDetail extends Model
{
    use HasFactory;

    protected $table = 'spk_penerimaan_barang_jadi_details';

    protected $fillable = [
        'penerimaan_id',
        'barang_jadi_id',
        'satuan_id',
        'lokasi_rak_id',
        'jumlah_hasil_baik',
        'jumlah_reject',
        'alasan_reject',
        'catatan',
    ];

    protected $casts = [
        'jumlah_hasil_baik' => 'decimal:4',
        'jumlah_reject'     => 'decimal:4',
    ];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(SpkPenerimaanBarangJadi::class, 'penerimaan_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function lokasiRak(): BelongsTo
    {
        return $this->belongsTo(MasterLokasiRak::class, 'lokasi_rak_id');
    }
}
