<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderItem extends Model
{
    use HasFactory;

    protected $table = 'tender_items';

    protected $fillable = [
        'tender_id',
        'barang_id',
        'nama_barang_custom',
        'qty',
        'satuan',
        'catatan_item',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
