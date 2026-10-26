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

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            // Strict Tenancy Isolation:
            // Jika Cabang -> branch_id terisi, company_id WAJIB NULL
            // Jika PT Pusat -> company_id terisi, branch_id WAJIB NULL
            if (!empty($model->branch_id) || (!empty($activeBranchId) && empty($model->company_id))) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            // Strict Tenancy Isolation pada update
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });
    }

    /**
     * Scope: Filter berdasarkan entitas aktif (PT Pusat vs Cabang)
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> hanya data cabang ini (company_id WAJIB NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat -> hanya data level PT (branch_id WAJIB NULL)
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
     * Scope Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        $table = $this->getTable();
        if (!empty($activeBranchId)) {
            return $query->where($table . '.branch_id', $activeBranchId);
        }
        return $query->whereNull($table . '.branch_id');
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
