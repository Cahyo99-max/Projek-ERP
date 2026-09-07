<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuStok extends Model
{
    use HasFactory;

    protected $table = 'kartu_stoks';

    public $timestamps = false;

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'tipe',
        'jumlah',
        'stok_awal',
        'stok_akhir',
        'harga_satuan',
        'nilai_total',
        'referensi_jenis',
        'referensi_id',
        'referensi_nomor',
        'keterangan',
        'created_at',
    ];

    protected $casts = [
        'jumlah'       => 'decimal:4',
        'stok_awal'    => 'decimal:4',
        'stok_akhir'   => 'decimal:4',
        'harga_satuan' => 'decimal:6',
        'nilai_total'  => 'decimal:4',
        'created_at'   => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
    public function gudang(): BelongsTo { return $this->belongsTo(MasterGudang::class, 'gudang_id'); }
}
