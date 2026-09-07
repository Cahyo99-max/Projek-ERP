<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Position extends Model
{
    use SoftDeletes;

    protected $table = 'positions';

    protected $fillable = [
        'company_id',
        'department_id',
        'position_code',
        'position_name',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        static::creating(function ($position) {
            if (auth()->check()) {
                $position->created_by = auth()->id();
            }
            if (empty($position->company_id) && session()->has('active_company_id')) {
                $position->company_id = session('active_company_id');
            }
        });

        static::updating(function ($position) {
            if (auth()->check()) {
                $position->updated_by = auth()->id();
            }
        });

        static::deleting(function ($position) {
            if (auth()->check()) {
                $position->deleted_by = auth()->id();
                $position->saveQuietly();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Relasi: Jabatan ini dimiliki oleh Departemen apa
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relasi ke User pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User pengubah
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke User penghapus
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
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
        $q = $query->where($this->getTable() . '.status', 'Active');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }
}
