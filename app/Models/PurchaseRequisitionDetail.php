<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_requisition_details';

    protected $fillable = [
        'pr_id',
        'barang_id',
        'satuan_id',
        'jumlah',
        'faktor_pengali',
        'jumlah_dasar',
        'harga_estimasi',
        'keterangan',
    ];

    protected $casts = [
        'jumlah'         => 'decimal:4',
        'faktor_pengali' => 'decimal:4',
        'jumlah_dasar'   => 'decimal:4',
        'harga_estimasi' => 'decimal:2',
    ];

    public function pr()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }
}
