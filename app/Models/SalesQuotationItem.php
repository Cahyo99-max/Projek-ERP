<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationItem extends Model
{
    use HasFactory;

    protected $table = 'sales_quotation_items';

    protected $fillable = [
        'sales_quotation_id',
        'barang_id',
        'nama_barang_custom',
        'deskripsi_item',
        'kategori_id',
        'satuan_id',
        'nama_satuan',
        'qty',
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
        'catatan',
        'urutan',
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

    public function quotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function ppnPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function kategori()
    {
        return $this->belongsTo(MasterKategoriBarang::class, 'kategori_id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    /**
     * Get clean display name (master barang name or custom input)
     */
    public function getNamaBarangDisplayAttribute()
    {
        return $this->nama_barang_custom ?: ($this->barang?->nama_barang ?? '-');
    }

    /**
     * Get clean display code (master barang code or custom)
     */
    public function getKodeBarangDisplayAttribute()
    {
        return $this->barang?->kode_barang ?? 'CUSTOM';
    }
}
