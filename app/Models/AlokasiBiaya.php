<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use App\Models\Company;

class AlokasiBiaya extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alokasi_biayas';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_alokasi',
        'tanggal_alokasi',
        'po_biaya_id',
        'tender_id',
        'total_biaya',
        'metode_alokasi',
        'status',
        'keterangan',
        'alasan_batal',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_alokasi' => 'date',
        'total_biaya'     => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            if (!empty($activeBranchId)) {
                $model->branch_id = $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: 1;
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } elseif (!empty($model->company_id)) {
                $model->branch_id = null;
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function poBiaya(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_biaya_id'); }
    public function tender(): BelongsTo { return $this->belongsTo(Tender::class, 'tender_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }
    public function details(): HasMany { return $this->hasMany(AlokasiBiayaDetail::class, 'alokasi_biaya_id'); }

    public function scopeForActiveCompany($query)
    {
        if (!empty(session('active_branch_id'))) {
            return $query;
        }
        $companyId = session('active_company_id');
        if ($companyId) {
            return $query->where('alokasi_biayas.company_id', $companyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('alokasi_biayas.branch_id', intval($branchId));
        }
        return $query->whereNull('alokasi_biayas.branch_id');
    }

    /**
     * Generate unique Nomor Alokasi Biaya.
     * Format:
     * - Cabang: ALB/{kode_cabang}/{YYYY}/{MM}/{XXXX}
     * - Pusat (PT): ALB/{kode_perusahaan}/{YYYY}/{MM}/{XXXX}
     *
     * Dilengkapi MySQL Advisory Lock & while-loop fallback dengan withTrashed()
     * untuk mencegah duplikasi jika ada data yang dihapus (soft-deleted),
     * dibatalkan, atau diinput bersamaan antar PT & Cabang.
     */
    public static function generateNomorAlokasi($companyId = null, $branchId = null, $tanggalAlokasi = null)
    {
        $activeBranchId = $branchId !== null ? $branchId : session('active_branch_id');
        $date = $tanggalAlokasi ? \Carbon\Carbon::parse($tanggalAlokasi) : now();
        $year = $date->format('Y');
        $month = $date->format('m');

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            $prefixEntity = $branch ? ($branch->kode_cabang ?: 'CBG') : 'CBG';
        } else {
            $companyId = $companyId ?: (session('active_company_id') ?: (Auth::user()?->company_id ?? 1));
            $company = Company::find($companyId);
            $prefixEntity = $company ? ($company->kode_perusahaan ?: 'HO') : 'HO';
        }

        $prefix = "ALB/{$prefixEntity}/{$year}/{$month}/";
        $lockKey = 'alokasi_generate_lock_' . md5($prefix);
        $gotLock = false;

        try {
            $lockRes = DB::select("SELECT GET_LOCK(?, 10) as lk", [$lockKey]);
            $gotLock = !empty($lockRes) && $lockRes[0]->lk == 1;

            // Ambil nomor tertinggi menggunakan index B-Tree unik (O(1) seek instant, anti-lemot di 7+ juta data)
            $latestNum = self::withTrashed()
                ->where('nomor_alokasi', 'like', $prefix . '%')
                ->orderByDesc('nomor_alokasi')
                ->value('nomor_alokasi');

            $maxSeq = 0;
            if ($latestNum) {
                $parts = explode('/', $latestNum);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    $maxSeq = (int) $lastPart;
                }
            }

            $nextNum = $maxSeq + 1;
            $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Double check existence (termasuk soft-deleted) untuk garansi 100% unik
            while (self::withTrashed()->where('nomor_alokasi', $candidate)->exists()) {
                $nextNum++;
                $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            return $candidate;
        } finally {
            if ($gotLock) {
                DB::select("SELECT RELEASE_LOCK(?)", [$lockKey]);
            }
        }
    }
}
