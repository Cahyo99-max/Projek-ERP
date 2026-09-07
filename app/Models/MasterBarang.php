<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBarang extends Model
{
    use SoftDeletes;

    protected $table = 'master_barang';

    protected $fillable = [
        'company_id',
        'kode_barang',
        'barcode',
        'nama_barang',
        'nama_alias',
        'tipe_item',
        'tipe_pelacakan',
        'kategori_barang_id',
        'brand_id',
        'tipe_barang_id',
        'default_masa_manfaat',
        'default_nilai_residu',
        'default_metode_penyusutan',
        'default_kondisi',
        'default_gudang_id',
        'satuan_id',
        'warna_id',
        'parent_id',
        'ukuran',
        'spesifikasi',
        'harga_jual_retail',
        'harga_jual_grosir',
        'harga_jual_tender',
        'berat',
        'panjang',
        'lebar',
        'tinggi',
        'minimum_stok',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'harga_jual_retail'     => 'decimal:2',
        'harga_jual_grosir'     => 'decimal:2',
        'harga_jual_tender'     => 'decimal:2',
        'berat'                 => 'decimal:3',
        'panjang'               => 'decimal:2',
        'lebar'                 => 'decimal:2',
        'tinggi'                => 'decimal:2',
        'minimum_stok'          => 'decimal:2',
        'default_nilai_residu'  => 'decimal:2',
        'default_masa_manfaat'  => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) $model->created_by = auth()->id();
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) $model->updated_by = auth()->id();
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ==============================
    // Relasi FK & Company Scope
    // ==============================
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(MasterKategoriBarang::class, 'kategori_barang_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(MasterBrand::class, 'brand_id');
    }

    public function tipeBarang(): BelongsTo
    {
        return $this->belongsTo(MasterTipeBarang::class, 'tipe_barang_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function warna(): BelongsTo
    {
        return $this->belongsTo(MasterWarna::class, 'warna_id');
    }

    public function defaultGudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'default_gudang_id');
    }

    // ==============================
    // Relasi Audit Trail
    // ==============================
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }

    // ==============================
    // Relasi Galeri Foto
    // ==============================
    public function fotos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasterBarangFoto::class, 'barang_id')->orderBy('urutan', 'asc');
    }

    public function primaryFoto(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MasterBarangFoto::class, 'barang_id')->where('is_primary', true);
    }

    /**
     * Accessor for primaryFoto to automatically inherit parent's primary foto if variant has none.
     */
    public function getPrimaryFotoAttribute()
    {
        if ($this->relationLoaded('primaryFoto')) {
            $relation = $this->getRelationValue('primaryFoto');
            if ($relation) return $relation;
        } else {
            $relation = $this->primaryFoto()->first();
            if ($relation) {
                $this->setRelation('primaryFoto', $relation);
                return $relation;
            }
        }

        if ($this->parent_id) {
            if (!$this->relationLoaded('parent')) {
                $this->load('parent.primaryFoto');
            }
            return $this->parent ? $this->parent->primaryFoto : null;
        }

        return null;
    }

    /**
     * Accessor for fotos to automatically inherit parent's fotos list if variant has none.
     */
    public function getFotosAttribute()
    {
        if ($this->relationLoaded('fotos')) {
            $relation = $this->getRelationValue('fotos');
            if ($relation && $relation->count() > 0) return $relation;
        } else {
            $relation = $this->fotos()->get();
            if ($relation && $relation->count() > 0) {
                $this->setRelation('fotos', $relation);
                return $relation;
            }
        }

        if ($this->parent_id) {
            if (!$this->relationLoaded('parent')) {
                $this->load('parent.fotos');
            }
            return $this->parent ? $this->parent->fotos : collect();
        }

        return $relation ?? collect();
    }

    // ==============================
    // Relasi Varian & Bundle
    // ==============================
    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasterBarang::class, 'parent_id')->orderBy('id', 'asc');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'parent_id');
    }

    public function bundleComponents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasterBarangBundle::class, 'parent_barang_id');
    }

    public function isBundle(): bool
    {
        return $this->tipe_item === 'Bundle';
    }

    public function stokGudangs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StokGudang::class, 'barang_id');
    }

    public function konversiSatuan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MasterBarangKonversi::class, 'barang_id')->orderBy('id', 'asc');
    }

    public function konversis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->konversiSatuan();
    }

    // ==============================
    // Scopes
    // ==============================
    public function scopeActive($query)
    {
        $q = $query->where($this->getTable() . '.status', 'Active');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }
}
