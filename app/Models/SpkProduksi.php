<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkProduksi extends Model
{
    use SoftDeletes;

    protected $table = 'spk_produksi';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_spk',
        'tanggal_spk',
        'target_selesai',
        'bom_id',
        'barang_jadi_id',
        'tender_id',
        'satuan_id',
        'jumlah_rencana',
        'jumlah_selesai',
        'jumlah_reject',
        'tipe_spk',
        'prioritas',
        'penanggung_jawab_id',
        'estimasi_biaya_bahan',
        'estimasi_biaya_tk',
        'estimasi_biaya_overhead',
        'estimasi_total_biaya',
        'is_hpp_closed',
        'hpp_closed_at',
        'hpp_closed_by',
        'status',
        'keterangan',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_spk'            => 'date',
        'target_selesai'         => 'date',
        'jumlah_rencana'         => 'decimal:4',
        'jumlah_selesai'         => 'decimal:4',
        'jumlah_reject'          => 'decimal:4',
        'estimasi_biaya_bahan'   => 'decimal:4',
        'estimasi_biaya_tk'      => 'decimal:4',
        'estimasi_biaya_overhead'=> 'decimal:4',
        'estimasi_total_biaya'   => 'decimal:4',
        'is_hpp_closed'          => 'boolean',
        'hpp_closed_at'          => 'datetime',
        'approved_at'            => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'deleted_at'             => 'datetime',
    ];

    /**
     * Scope: Filter berdasarkan active company + pisah PT Pusat vs Cabang.
     * - Jika aktif sebagai PT Pusat (branch_id = null) → hanya tampilkan SPK milik PT (branch_id IS NULL)
     * - Jika aktif sebagai Cabang → hanya tampilkan SPK milik cabang tersebut (branch_id = active_branch_id)
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeCompanyId)) {
            $query->where('spk_produksi.company_id', $activeCompanyId);
        }

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang → hanya data cabang ini
            $query->where('spk_produksi.branch_id', $activeBranchId);
        } else {
            // Aktif sebagai PT Pusat → hanya data level PT (branch_id IS NULL)
            $query->whereNull('spk_produksi.branch_id');
        }

        return $query;
    }

    /**
     * Scope tambahan untuk filter branch saja (opsional, jarang digunakan mandiri).
     */
    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return $query->where('spk_produksi.branch_id', $activeBranchId);
        }
        return $query->whereNull('spk_produksi.branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penanggung_jawab_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(SpkProduksiMaterial::class, 'spk_id');
    }

    public function tahapans(): HasMany
    {
        return $this->hasMany(SpkProduksiTahapan::class, 'spk_id')->orderBy('urutan', 'asc');
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

    public function hppClosedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hpp_closed_by');
    }

    public function pengeluaranBahans(): HasMany
    {
        return $this->hasMany(SpkPengeluaranBahan::class, 'spk_id')->latest('id');
    }

    public function penerimaanBarangJadis(): HasMany
    {
        return $this->hasMany(SpkPenerimaanBarangJadi::class, 'spk_id')->latest('id');
    }
}
