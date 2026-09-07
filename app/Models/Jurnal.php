<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurnal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurnals';

    protected $fillable = [
        'nomor_jurnal',
        'tanggal_jurnal',
        'referensi_tipe',
        'referensi_id',
        'company_id',
        'branch_id',
        'total_debit',
        'total_kredit',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_jurnal' => 'date',
        'total_debit'    => 'decimal:2',
        'total_kredit'   => 'decimal:2',
    ];

    /**
     * Scope Filter Active Company (Jika di Cabang, company_id adalah NULL)
     */
    public function scopeForActiveCompany($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            // Ketika sesi aktif adalah Cabang, jurnal cabang memiliki company_id = NULL
            return $query->whereNull('company_id');
        }

        $companyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($companyId)) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    /**
     * Scope Filter Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('branch_id', $branchId);
        }
        return $query->whereNull('branch_id');
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
