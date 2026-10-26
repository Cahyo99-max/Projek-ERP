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
        'nomor_batch',
        'tanggal_penerimaan',
        'tanggal_produksi',
        'tanggal_kedaluwarsa',
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
        'biaya_bahan_baku',
        'biaya_tenaga_kerja',
        'biaya_overhead',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'status',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_penerimaan'  => 'date',
        'tanggal_produksi'    => 'date',
        'tanggal_kedaluwarsa' => 'date',
        'jumlah_hasil_baik'   => 'decimal:4',
        'jumlah_reject'       => 'decimal:4',
        'hpp_satuan'          => 'decimal:4',
        'total_nilai_hpp'     => 'decimal:4',
        'biaya_bahan_baku'    => 'decimal:4',
        'biaya_tenaga_kerja'  => 'decimal:4',
        'biaya_overhead'      => 'decimal:4',
        'approved_at'         => 'datetime',
        'rejected_at'         => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    /**
     * Booted Model Event: Pastikan aturan pemisahan PT vs Cabang konsisten saat pembuatan transaksi.
     * - Jika input Cabang (branch_id terisi / sesi cabang aktif) → company_id dipastikan NULL.
     * - Jika input PT (branch_id kosong / sesi PT aktif)         → branch_id dipastikan NULL & company_id terisi.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            if (!empty($model->branch_id) || !empty($activeBranchId)) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id  = null;
                if (empty($model->company_id)) {
                    $model->company_id = $activeCompanyId ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });
    }

    /**
     * Scope Filter Active Company: Strict Isolation PT Pusat vs Cabang
     * - Sesi Cabang: branch_id = active_branch_id & company_id IS NULL
     * - Sesi PT Pusat: branch_id IS NULL & company_id = active_company_id
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang → hanya transaksi milik Cabang ini (company_id NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat → hanya transaksi level PT Pusat (branch_id IS NULL)
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }

    /**
     * Scope Filter Active Branch
     */
    public function scopeForActiveBranch($query)
    {
        return $this->scopeForActiveCompany($query);
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SpkPenerimaanBarangJadiDetail::class, 'penerimaan_id');
    }

    public function serials(): HasMany
    {
        return $this->hasMany(SpkPenerimaanBarangJadiSerial::class, 'penerimaan_id');
    }

    public function jurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Penerimaan Barang Jadi SPK');
    }
}
