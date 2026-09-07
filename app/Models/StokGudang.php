<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokGudang extends Model
{
    use HasFactory;

    protected $table = 'stok_gudangs';

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'stok_aktual',
        'nilai_persediaan',
        'harga_pokok_rata_rata',
        'hpp_sebelumnya',
        'harga_beli_terakhir',
        'harga_beli_sebelumnya',
    ];

    protected $casts = [
        'stok_aktual'           => 'decimal:4',
        'nilai_persediaan'      => 'decimal:4',
        'harga_pokok_rata_rata' => 'decimal:6',
        'hpp_sebelumnya'        => 'decimal:6',
        'harga_beli_terakhir'   => 'decimal:6',
        'harga_beli_sebelumnya' => 'decimal:6',
    ];

    public function barang(): BelongsTo { return $this->belongsTo(MasterBarang::class, 'barang_id'); }
    public function gudang(): BelongsTo { return $this->belongsTo(MasterGudang::class, 'gudang_id'); }

    /**
     * Synchronize global Inventory Asset Value & HPP to MasterBarang.
     * Source of truth: Nilai Persediaan ÷ Stok Aktual.
     */
    public static function syncMasterHpp($barangId): void
    {
        // Stock valuation & Moving Average Cost are now stored strictly in stok_gudangs per warehouse.
        return;
    }
}
