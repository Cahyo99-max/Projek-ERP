<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkProduksiTahapan extends Model
{
    protected $table = 'spk_produksi_tahapans';

    protected $fillable = [
        'spk_id',
        'urutan',
        'nama_tahapan',
        'target_qty',
        'qty_selesai',
        'qty_reject',
        'status',
        'pic_operator',
        'waktu_mulai',
        'waktu_selesai',
        'catatan',
    ];

    protected $casts = [
        'target_qty'    => 'decimal:4',
        'qty_selesai'   => 'decimal:4',
        'qty_reject'    => 'decimal:4',
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    protected $appends = ['persentase_selesai'];

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
    }

    public function logHarians(): HasMany
    {
        return $this->hasMany(SpkProduksiLogHarian::class, 'spk_tahapan_id')->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    }

    public function getPersentaseSelesaiAttribute()
    {
        $target = (float)$this->target_qty;
        if ($target <= 0) return 0;
        $selesai = (float)$this->qty_selesai;
        return round(($selesai / $target) * 100, 1);
    }
}
