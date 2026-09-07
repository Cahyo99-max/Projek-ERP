<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkProduksiLogHarian extends Model
{
    use HasFactory;

    protected $table = 'spk_produksi_log_harians';

    protected $fillable = [
        'spk_id',
        'spk_tahapan_id',
        'tanggal',
        'qty_hasil_hari_ini',
        'qty_reject_hari_ini',
        'pic_operator',
        'catatan',
        'foto_bukti',
        'created_by',
    ];

    protected $casts = [
        'tanggal'             => 'date',
        'qty_hasil_hari_ini'  => 'decimal:4',
        'qty_reject_hari_ini' => 'decimal:4',
    ];

    protected $appends = ['foto_bukti_url'];

    public function getFotoBuktiUrlAttribute()
    {
        if ($this->foto_bukti) {
            return asset('storage/' . $this->foto_bukti);
        }
        return null;
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
    }

    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(SpkProduksiTahapan::class, 'spk_tahapan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
