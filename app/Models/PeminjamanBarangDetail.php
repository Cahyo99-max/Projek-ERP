<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanBarangDetail extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_barang_details';

    protected $fillable = [
        'peminjaman_id',
        'barang_id',
        'satuan_id',
        'qty_pinjam',
        'serial_number',
        'kondisi_awal',
        'qty_kembali_bagus',
        'qty_kembali_rusak',
        'qty_hilang',
        'kondisi_akhir',
        'catatan',
    ];

    protected $casts = [
        'qty_pinjam'        => 'decimal:4',
        'qty_kembali_bagus' => 'decimal:4',
        'qty_kembali_rusak' => 'decimal:4',
        'qty_hilang'        => 'decimal:4',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(PeminjamanBarang::class, 'peminjaman_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
