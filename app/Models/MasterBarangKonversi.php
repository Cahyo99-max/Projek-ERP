<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBarangKonversi extends Model
{
    use SoftDeletes;

    protected $table = 'master_barang_konversi';

    protected $fillable = [
        'barang_id',
        'satuan_id',
        'faktor_pengali',
        'is_satuan_beli',
        'is_satuan_jual',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'faktor_pengali' => 'decimal:4',
        'is_satuan_beli' => 'boolean',
        'is_satuan_jual' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
