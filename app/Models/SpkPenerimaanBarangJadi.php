<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkPenerimaanBarangJadi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'spk_penerimaan_barang_jadis';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_penerimaan',
        'tanggal_penerimaan',
        'spk_id',
        'barang_jadi_id',
        'gudang_id',
        'lokasi_rak_id',
        'satuan_id',
        'jumlah_hasil_baik',
        'jumlah_reject',
        'hpp_satuan',
        'total_nilai_hpp',
        'penerima_karyawan_id',
        'penerima_nama',
        'penyerah_karyawan_id',
        'penyerah_nama',
        'status',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_penerimaan' => 'date',
        'jumlah_hasil_baik'  => 'decimal:4',
        'jumlah_reject'      => 'decimal:4',
        'hpp_satuan'         => 'decimal:4',
        'total_nilai_hpp'    => 'decimal:4',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (empty($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
        });
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if (!empty($activeCompanyId)) {
            return $query->where('spk_penerimaan_barang_jadis.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return $query->where('spk_penerimaan_barang_jadis.branch_id', $activeBranchId);
        }
        return $query;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function lokasiRak(): BelongsTo
    {
        return $this->belongsTo(MasterLokasiRak::class, 'lokasi_rak_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penerima_karyawan_id');
    }

    public function penyerah(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penyerah_karyawan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SpkPenerimaanBarangJadiDetail::class, 'penerimaan_id');
    }

    public function jurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Penerimaan Barang Jadi SPK');
    }
}
