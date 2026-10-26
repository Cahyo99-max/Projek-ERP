<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasMutasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kas_mutasis';

    protected $fillable = [
        'nomor_transaksi',
        'kas_bank_id',
        'tipe',
        'kategori',
        'lawan_coa_id',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'tanggal',
        'nomor_referensi',
        'kontak_tipe',
        'kontak_id',
        'nama_kontak',
        'keterangan',
        'lampiran',
        'transfer_to_kas_bank_id',
        'tender_id',
        'status',
        'jurnal_id',
        'company_id',
        'branch_id',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'jumlah'        => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
        'tanggal'       => 'date',
        'cancelled_at'  => 'datetime',
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

    public function kasBank()
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function transferToKasBank()
    {
        return $this->belongsTo(KasBank::class, 'transfer_to_kas_bank_id');
    }

    public function lawanCoa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'lawan_coa_id');
    }

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
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

    public function canceler()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function getLampiranUrlAttribute(): ?string
    {
        return $this->lampiran ? asset('storage/' . $this->lampiran) : null;
    }
}
