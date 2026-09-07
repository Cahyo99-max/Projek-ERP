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

    /**
     * Scope Filter Active Company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($companyId)) {
            return $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }
        return $query;
    }

    /**
     * Scope Filter Active Branch
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        return $query;
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
