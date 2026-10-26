<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'delivery_order_details';

    protected $fillable = [
        'delivery_order_id',
        'sales_order_detail_id',
        'barang_id',
        'nama_barang_custom',
        'qty_order',
        'qty_kirim',
        'nama_satuan',
        'catatan_item',
        'serial_numbers',
    ];

    protected $casts = [
        'qty_order'      => 'decimal:2',
        'qty_kirim'      => 'decimal:2',
        'serial_numbers' => 'array',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function salesOrderDetail(): BelongsTo
    {
        return $this->belongsTo(SalesOrderDetail::class, 'sales_order_detail_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
