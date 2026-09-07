<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'kode_perusahaan',
        'nama_perusahaan',
        'logo',
        'npwp',
        'nib',
        'director_name',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Scope untuk perusahaan aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Relasi 1-to-N: Satu Perusahaan (PT) memiliki banyak Cabang.
     */
    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    /**
     * Relasi many-to-many ke User melalui tabel user_companies.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_companies', 'company_id', 'user_id')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
