<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSupplierBank extends Model
{
    use HasFactory;

    protected $table = 'master_supplier_banks';

    protected $fillable = [
        'supplier_id',
        'nama_bank',
        'nomor_rekening',
        'atas_nama_rekening',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }
}
