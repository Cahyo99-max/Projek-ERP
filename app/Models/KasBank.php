<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kas_banks';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe',
        'coa_id',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'saldo_awal',
        'saldo_berjalan',
        'deskripsi',
        'is_active',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'saldo_awal'     => 'decimal:2',
        'saldo_berjalan' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    /**
     * Scope Active Company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($companyId)) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    /**
     * Scope Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('branch_id', $branchId);
        }
        return $query->whereNull('branch_id');
    }

    /**
     * Label Entitas (PT Pusat vs Cabang)
     */
    public function getEntitasLabelAttribute(): string
    {
        if ($this->branch) {
            return 'Cabang: ' . $this->branch->nama_cabang;
        }
        return 'PT Pusat';
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    public function mutasis()
    {
        return $this->hasMany(KasMutasi::class, 'kas_bank_id');
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
