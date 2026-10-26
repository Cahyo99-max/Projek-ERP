<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'karyawan_id',
        'kode_karyawan',
        'role_id',
        'departemen_id',
        'jabatan_id',
        'nama_lengkap',
        'inisial',
        'username',
        'email',
        'password',
        'status',
        'no_hp',
        'jenis_kelamin',
        'foto',
        'login_terakhir',
        'deleted_by',
    ];

    protected $appends = [
        'name',
    ];

    public function getNameAttribute(): string
    {
        return $this->nama_lengkap ?: ($this->username ?: 'User');
    }

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'departemen_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'jabatan_id');
    }

    /**
     * Semua cabang yang dapat diakses oleh user ini.
     */
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    /**
     * Semua PT / Perusahaan yang dapat diakses oleh user ini.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id')
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    /**
     * Cabang default user (yang di-set is_default = true).
     */
    public function defaultBranch()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id')
                    ->wherePivot('is_default', true)
                    ->withPivot('is_default')
                    ->withTimestamps();
    }

    /**
     * Riwayat tanda tangan digital user.
     */
    public function signatures()
    {
        return $this->hasMany(UserSignature::class, 'user_id');
    }

    /**
     * Tanda tangan digital user yang aktif saat ini.
     */
    public function activeSignature()
    {
        return $this->hasOne(UserSignature::class, 'user_id')->where('is_active', true);
    }
}
