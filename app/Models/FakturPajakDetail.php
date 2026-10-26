<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FakturPajakDetail extends Model
{
    use HasFactory;

    protected $table = 'faktur_pajak_details';

    protected $fillable = [
        'faktur_pajak_id',
        'barang_id',
        'nama_barang',
        'qty',
        'harga_satuan',
        'total_harga',
        'diskon',
        'dpp',
        'tarif_ppn',
        'ppn',
        'tarif_ppnbm',
        'ppnbm',
    ];

    protected $casts = [
        'qty'          => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_harga'  => 'decimal:2',
        'diskon'       => 'decimal:2',
        'dpp'          => 'decimal:2',
        'tarif_ppn'    => 'decimal:2',
        'ppn'          => 'decimal:2',
        'tarif_ppnbm'  => 'decimal:2',
        'ppnbm'        => 'decimal:2',
    ];

    public function fakturPajak(): BelongsTo
    {
        return $this->belongsTo(FakturPajak::class, 'faktur_pajak_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
