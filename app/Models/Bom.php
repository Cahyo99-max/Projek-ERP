<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bom extends Model
{
    use SoftDeletes;

    protected $table = 'boms';

    protected $fillable = [
        'company_id',
        'branch_id',
        'kode_bom',
        'nama_bom',
        'barang_jadi_id',
        'jumlah_hasil',
        'satuan_hasil_id',
        'tipe_bom',
        'tender_id',
        'biaya_tenaga_kerja',
        'biaya_overhead',
        'total_estimasi_hpp',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'jumlah_hasil'       => 'decimal:4',
        'biaya_tenaga_kerja' => 'decimal:4',
        'biaya_overhead'     => 'decimal:4',
        'total_estimasi_hpp' => 'decimal:4',
        'approved_at'        => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    /**
     * Scope: Filter berdasarkan active company + pisah PT Pusat vs Cabang.
     * - Jika aktif sebagai PT Pusat (branch_id = null) → hanya tampilkan BOM milik PT (branch_id IS NULL)
     * - Jika aktif sebagai Cabang → hanya tampilkan BOM milik cabang tersebut (branch_id = active_branch_id)
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeCompanyId)) {
            $query->where($table . '.company_id', $activeCompanyId);
        }

        if (!empty($activeBranchId)) {
            $query->where($table . '.branch_id', $activeBranchId);
        } else {
            $query->whereNull($table . '.branch_id');
        }

        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        $table = $this->getTable();
        return !empty($activeBranchId)
            ? $query->where($table . '.branch_id', $activeBranchId)
            : $query->whereNull($table . '.branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function satuanHasil(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_hasil_id');
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
