<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBarangFoto extends Model
{
    protected $table = 'master_barang_foto';

    protected $fillable = [
        'barang_id',
        'foto_path',
        'is_primary',
        'urutan',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'urutan'     => 'integer',
    ];

    /**
     * Relasi ke MasterBarang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    /**
     * Scope untuk mengambil foto utama
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
