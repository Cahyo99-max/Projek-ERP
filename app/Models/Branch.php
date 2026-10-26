<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'company_id',
        'kode_cabang',
        'nama_cabang',
        'nama_perusahaan',
        'logo',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'manager_name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Accessor untuk URL Logo Cabang.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }
        return asset('storage/' . $this->logo);
    }

    /**
     * Relasi ke Perusahaan (PT Induk).
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Cabang ini dibuat oleh user siapa.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cabang ini terakhir diupdate oleh user siapa.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi many-to-many ke User melalui tabel pivot user_branches.
     * Mendapatkan semua user yang punya akses ke cabang ini.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches', 'branch_id', 'user_id')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    /**
     * Cabang ini dihapus oleh user siapa.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope untuk hanya menampilkan cabang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where($this->getTable() . '.status', 'Active');
    }

    /**
     * Scope untuk menyaring cabang berdasarkan PT/Company yang aktif di session.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }
}
