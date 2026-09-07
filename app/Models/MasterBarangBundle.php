<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBarangBundle extends Model
{
    use HasFactory;

    protected $table = 'master_barang_bundle';

    protected $fillable = [
        'parent_barang_id',
        'child_barang_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function parentBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'parent_barang_id');
    }

    public function childBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'child_barang_id');
    }
}
