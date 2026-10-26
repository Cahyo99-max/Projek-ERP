<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCash extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'petty_cashes';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'tipe',
        'kategori',
        'tipe_entitas',
        'company_id',
        'branch_id',
        'coa_kas_id',
        'lawan_coa_id',
        'tender_id',
        'nominal',
        'saldo_sebelum',
        'saldo_sesudah',
        'nama_pengaju',
        'keterangan',
        'alasan_pembatalan',
        'lampiran_nota',
        'status',
        'jurnal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'nominal'       => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            // Strict Tenancy Isolation:
            // Jika Cabang -> branch_id terisi, company_id WAJIB NULL, tipe_entitas = 'Cabang'
            // Jika PT Pusat -> company_id terisi, branch_id WAJIB NULL, tipe_entitas = 'PT'
            if (!empty($model->branch_id) || (!empty($activeBranchId) && empty($model->company_id))) {
                $model->branch_id    = $model->branch_id ?: $activeBranchId;
                $model->company_id   = null;
                $model->tipe_entitas = 'Cabang';
            } else {
                $model->branch_id    = null;
                $model->tipe_entitas = 'PT';
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            // Strict Tenancy Isolation pada update:
            if (!empty($model->branch_id)) {
                $model->company_id   = null;
                $model->tipe_entitas = 'Cabang';
            } else {
                $model->branch_id    = null;
                $model->tipe_entitas = 'PT';
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });
    }

    /**
     * Scope Filter Active Company (Strict Isolation PT vs Cabang)
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
     * Scope Filter Active Branch (Strict Isolation PT vs Cabang)
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
     * Scope Filter Berdasarkan Entitas (PT / Cabang)
     */
    public function scopeFilterEntitas($query, $entitas = 'all', $branchId = null)
    {
        if ($entitas === 'pt') {
            return $query->where('tipe_entitas', 'PT')->whereNull('branch_id');
        } elseif ($entitas === 'cabang') {
            $query->where('tipe_entitas', 'Cabang');
            if (!empty($branchId)) {
                $query->where('branch_id', $branchId);
            }
            return $query;
        }
        return $query;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function coaKas()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_kas_id');
    }

    public function lawanCoa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'lawan_coa_id');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function reversalJurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'PettyCashReversal');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Format Rupiah Nominal
     */
    public function getFormattedNominalAttribute()
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Label Entitas PT vs Cabang
     */
    public function getEntitasLabelAttribute()
    {
        if ($this->tipe_entitas === 'PT' || empty($this->branch_id)) {
            return 'PT Pusat (' . ($this->company->nama_perusahaan ?? 'Pusat') . ')';
        }
        return 'Cabang: ' . ($this->branch->nama_cabang ?? 'Cabang');
    }
}
