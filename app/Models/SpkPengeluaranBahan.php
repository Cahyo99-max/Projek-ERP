<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkPengeluaranBahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'spk_pengeluaran_bahans';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_pengeluaran',
        'tanggal_pengeluaran',
        'spk_id',
        'gudang_id',
        'penyerah_karyawan_id',
        'penyerah_nama',
        'penerima_karyawan_id',
        'penerima_nama',
        'status',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_pengeluaran' => 'date:Y-m-d',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    /**
     * Booted Model Event: Pastikan aturan pemisahan PT vs Cabang konsisten saat pembuatan transaksi.
     * - Jika input Cabang (branch_id terisi) → company_id dipastikan NULL.
     * - Jika input PT (branch_id kosong)    → branch_id dipastikan NULL & company_id terisi.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } elseif (empty($model->company_id)) {
                $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
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

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function penyerah(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penyerah_karyawan_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penerima_karyawan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SpkPengeluaranBahanDetail::class, 'pengeluaran_id');
    }

    public function jurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Pengeluaran Bahan SPK');
    }
}
