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
        'company_id',
        'kode_tipe',
        'nama_tipe',
        'kategori_laporan',
        'saldo_normal_default',
        'status',
        'keterangan',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
    }

    /**
     * Scope query ke PT / Company yang sedang aktif di session
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId) {
            return $query->where('company_id', $activeCompanyId);
        }
        return $query;
    }

    /**
     * Relationship to Company (PT)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

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

    /**
     * Helper to seed standard default account types for a specific company
     */
    public static function seedDefaultsForCompany(int $companyId): void
    {
        $defaultTypes = [
            [
                'kode_tipe' => 'AST',
                'nama_tipe' => 'Aset / Aktiva',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Kas, Bank, Piutang, Persediaan, dan Aset Tetap'
            ],
            [
                'kode_tipe' => 'LIAB',
                'nama_tipe' => 'Kewajiban / Utang',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Hutang Dagang, PPN Keluaran, Utang Pajak'
            ],
            [
                'kode_tipe' => 'EQT',
                'nama_tipe' => 'Ekuitas / Modal',
                'kategori_laporan' => 'neraca',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Modal Disetor, Laba Ditahan'
            ],
            [
                'kode_tipe' => 'REV',
                'nama_tipe' => 'Pendapatan / Penjualan',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Penjualan Produk dan Jasa Utama'
            ],
            [
                'kode_tipe' => 'COGS',
                'nama_tipe' => 'Harga Pokok Penjualan (HPP)',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Modal Pokok Barang/Jasa Terjual & Ongkir Beli'
            ],
            [
                'kode_tipe' => 'EXP',
                'nama_tipe' => 'Beban Operasional',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Gaji, Utilitas, Ongkir Jual, Sewa, Penyusutan'
            ],
            [
                'kode_tipe' => 'OREV',
                'nama_tipe' => 'Pendapatan Lain-lain',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'credit',
                'status' => 'Active',
                'keterangan' => 'Pendapatan diluar operasional utama'
            ],
            [
                'kode_tipe' => 'OEXP',
                'nama_tipe' => 'Beban Lain-lain',
                'kategori_laporan' => 'laba_rugi',
                'saldo_normal_default' => 'debit',
                'status' => 'Active',
                'keterangan' => 'Beban diluar operasional utama'
            ],
        ];

        foreach ($defaultTypes as $type) {
            static::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'kode_tipe' => $type['kode_tipe'],
                ],
                $type
            );
        }
    }
}
