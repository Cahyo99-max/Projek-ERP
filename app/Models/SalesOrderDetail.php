<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_order_details';

    protected $fillable = [
        'sales_order_id',
        'barang_id',
        'nama_barang_custom',
        'deskripsi_item',
        'kategori_id',
        'qty',
        'satuan_id',
        'nama_satuan',
        'pricing_tier',
        'harga_satuan',
        'diskon_persen',
        'diskon_nominal',
        'subtotal',
        'ppn_pajak_id',
        'tarif_ppn',
        'nilai_ppn',
        'hpp_satuan',
        'total_hpp',
        'estimasi_laba',
        'margin_persen',
        'urutan',
        'catatan',
    ];

    protected $casts = [
        'qty'            => 'decimal:4',
        'harga_satuan'   => 'decimal:2',
        'diskon_persen'  => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'subtotal'       => 'decimal:2',
        'tarif_ppn'      => 'decimal:2',
        'nilai_ppn'      => 'decimal:2',
        'hpp_satuan'     => 'decimal:2',
        'total_hpp'      => 'decimal:2',
        'estimasi_laba'  => 'decimal:2',
        'margin_persen'  => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function ppnPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function kategori()
    {
        return $this->belongsTo(MasterKategoriBarang::class, 'kategori_id');
    }

    public function deliveryOrderDetails()
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'sales_order_detail_id');
    }
}
