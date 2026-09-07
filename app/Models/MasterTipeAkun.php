<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterTipeAkun extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_tipe_akuns';

    protected $fillable = [
        'kode_tipe',
        'nama_tipe',
        'kategori_laporan',
        'saldo_normal_default',
        'status',
        'keterangan',
    ];

    /**
     * Relationship to COA
     */
    public function accounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'master_tipe_akun_id');
    }

    /**
     * Active scope
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Kategori Laporan Label
     */
    public function getKategoriLaporanLabelAttribute(): string
    {
        return $this->kategori_laporan === 'neraca' ? 'Neraca (Balance Sheet)' : 'Laba Rugi (Profit & Loss)';
    }

    /**
     * Saldo Normal Label
     */
    public function getSaldoNormalLabelAttribute(): string
    {
        return ucfirst($this->saldo_normal_default ?? 'debit');
    }
}
