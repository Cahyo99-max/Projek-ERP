<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    use SoftDeletes;

    protected $table = 'karyawans';

    protected $fillable = [
        'company_id',
        'nik',
        'kode_karyawan',
        'nama_lengkap',
        'inisial',
        'foto',
        'no_hp',
        'alamat',
        'jenis_kelamin',
        'tanggal_lahir',
        'tanggal_masuk',
        'departemen_id',
        'jabatan_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'departemen_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'jabatan_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id');
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        $q = $query->where($this->getTable() . '.status', 'Aktif');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }
}

