<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    protected $table = 'asset_depreciations';

    protected $fillable = [
        'asset_id',
        'periode_penyusutan',
        'nilai_penyusutan',
        'nilai_buku_sebelumnya',
        'nilai_buku_setelahnya',
        'created_by',
    ];

    protected $casts = [
        'nilai_penyusutan'       => 'decimal:2',
        'nilai_buku_sebelumnya'  => 'decimal:2',
        'nilai_buku_setelahnya'  => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) $model->created_by = auth()->id();
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
