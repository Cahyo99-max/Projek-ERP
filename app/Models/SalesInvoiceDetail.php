<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceDetail extends Model
{
    use HasFactory;

    protected $table = 'sales_invoice_details';

    protected $fillable = [
        'sales_invoice_id',
        'barang_id',
        'nama_barang',
        'satuan_id',
        'qty',
        'harga_satuan',
        'diskon_persen',
        'diskon_nominal',
        'ppn_pajak_id',
        'tarif_ppn',
        'nilai_ppn',
        'total_harga',
        'catatan',
    ];

    protected $casts = [
        'qty'           => 'decimal:2',
        'harga_satuan'  => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'diskon_nominal'=> 'decimal:2',
        'tarif_ppn'     => 'decimal:2',
        'nilai_ppn'     => 'decimal:2',
        'total_harga'   => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function pajak(): BelongsTo
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }
}
