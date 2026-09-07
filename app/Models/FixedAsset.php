<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    use SoftDeletes;

    protected $table = 'fixed_assets';

    protected $fillable = [
        'barang_id',
        'gr_detail_id',
        'kode_aset',
        'nomor_seri',
        'gudang_id',
        'departemen_id',
        'supplier_id',
        'penanggung_jawab_id',
        'tanggal_perolehan',
        'harga_perolehan',
        'masa_manfaat',
        'nilai_residu',
        'nilai_buku_saat_ini',
        'kondisi',
        'metode_penyusutan',
        'status',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_perolehan'   => 'date',
        'harga_perolehan'     => 'decimal:2',
        'nilai_residu'        => 'decimal:2',
        'nilai_buku_saat_ini' => 'decimal:2',
        'masa_manfaat'        => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) $model->created_by = auth()->id();
        });
        static::updating(function ($model) {
            if (auth()->check()) $model->updated_by = auth()->id();
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ==========================================
    // Relasi
    // ==========================================
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function grDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptDetail::class, 'gr_detail_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'departemen_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }

    // Audit Relations
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }
}
