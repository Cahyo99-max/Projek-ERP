<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterGudang extends Model
{
    use SoftDeletes;

    protected $table = 'master_gudang';

    protected $fillable = [
        'company_id',
        'kode_gudang',
        'nama_gudang',
        'cabang_id',
        'jenis_gudang_id',
        'penanggung_jawab_id',
        'gunakan_lokasi_rak',
        'izinkan_stok_negatif',
        'alamat',
        'telepon',
        'email',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'gunakan_lokasi_rak' => 'boolean',
        'izinkan_stok_negatif' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) $model->created_by = auth()->id();
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) $model->updated_by = auth()->id();
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ==============================
    // Relasi FK
    // ==============================
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'cabang_id');
    }

    public function branch(): BelongsTo
    {
        return $this->cabang();
    }

    public function jenisGudang(): BelongsTo
    {
        return $this->belongsTo(MasterJenisGudang::class, 'jenis_gudang_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penanggung_jawab_id');
    }

    // ==============================
    // Relasi Audit Trail
    // ==============================
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }

    // ==============================
    // Scopes
    // ==============================
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            return $query->where($table . '.cabang_id', $activeBranchId);
        }
        return $query->whereNull($table . '.cabang_id');
    }

    public function scopeForActiveEntity($query)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if ($activeCompanyId) {
            $query->where($table . '.company_id', $activeCompanyId);
        }

        if (!empty($activeBranchId)) {
            $query->where($table . '.cabang_id', $activeBranchId);
        } else {
            $query->whereNull($table . '.cabang_id');
        }

        return $query;
    }

    public function scopeActive($query)
    {
        $q = $query->where($this->getTable() . '.status', 'Active');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }
}
