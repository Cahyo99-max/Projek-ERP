<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKategoriBarang extends Model
{
    use SoftDeletes;

    protected $table = 'master_kategori_barang';

    protected $fillable = [
        'company_id',
        'kode_kategori',
        'nama_kategori',
        'parent_id',
        'deskripsi',
        'urutan',
        'status',
        'tampil_di_bundle',
        'tampil_di_sales',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tampil_di_bundle' => 'boolean',
        'tampil_di_sales'  => 'boolean',
    ];

    // ==============================
    // Boot: Auto-fill audit trail & company_id
    // ==============================
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ==============================
    // Relasi Company
    // ==============================
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // ==============================
    // Relasi Hierarki (Multi-Level)
    // ==============================

    /**
     * Parent kategori (kategori induk).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterKategoriBarang::class, 'parent_id');
    }

    /**
     * Children kategori (subkategori langsung).
     */
    public function children(): HasMany
    {
        return $this->hasMany(MasterKategoriBarang::class, 'parent_id');
    }

    /**
     * Recursive children (seluruh subkategori di bawahnya tanpa batas level).
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Recursive parent (seluruh parent ke atas tanpa batas level).
     */
    public function parentRecursive(): BelongsTo
    {
        return $this->parent()->with('parentRecursive');
    }

    /**
     * Relasi ke MasterBarang (Daftar barang di bawah kategori ini).
     */
    public function barangs(): HasMany
    {
        return $this->hasMany(MasterBarang::class, 'kategori_barang_id');
    }

    // ==============================
    // Relasi Audit Trail
    // ==============================
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // ==============================
    // Scopes
    // ==============================
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        $q = $query->where($this->getTable() . '.status', 'Active');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }

    /**
     * Hanya kategori utama (tanpa parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope kategori yang diizinkan tampil di Katalog Penjualan / POS
     */
    public function scopeVisibleInSales($query)
    {
        return $query->where($this->getTable() . '.tampil_di_sales', true);
    }

    /**
     * Scope kategori yang diizinkan tampil di Pilihan Komponen Bundle
     */
    public function scopeVisibleInBundle($query)
    {
        return $query->where($this->getTable() . '.tampil_di_bundle', true);
    }
}
