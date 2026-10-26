<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_quotations';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_quotation',
        'revisi',
        'parent_quotation_id',
        'customer_id',
        'tender_id',
        'sales_id',
        'tanggal_quotation',
        'berlaku_hingga',
        'perihal',
        'status',
        'payment_term_id',
        'tipe_dp',
        'dp_persen',
        'dp_nominal',
        'sisa_tagihan',
        'catatan_pembayaran',
        'currency',
        'subtotal',
        'diskon_tipe',
        'diskon_persen',
        'diskon_nominal',
        'ppn_pajak_id',
        'tipe_ppn',
        'tarif_ppn',
        'nilai_ppn',
        'pph_pajak_id',
        'tipe_pph',
        'tarif_pph',
        'nilai_pph',
        'biaya_pengiriman',
        'catatan_biaya_pengiriman',
        'grand_total',
        'total_hpp',
        'estimasi_laba_kotor',
        'margin_persen',
        'catatan',
        'syarat_ketentuan',
        'sales_order_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_quotation' => 'date',
        'berlaku_hingga'    => 'date',
        'approved_at'        => 'datetime',
        'dp_persen'         => 'decimal:2',
        'dp_nominal'        => 'decimal:2',
        'sisa_tagihan'      => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'diskon_persen'     => 'decimal:2',
        'diskon_nominal'    => 'decimal:2',
        'tarif_ppn'         => 'decimal:2',
        'nilai_ppn'         => 'decimal:2',
        'tarif_pph'         => 'decimal:2',
        'nilai_pph'         => 'decimal:2',
        'biaya_pengiriman'  => 'decimal:2',
        'grand_total'       => 'decimal:2',
        'total_hpp'         => 'decimal:2',
        'estimasi_laba_kotor' => 'decimal:2',
        'margin_persen'     => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            
            $activeBranchId = $model->branch_id ?: session('active_branch_id');
            if (!empty($activeBranchId)) {
                $model->branch_id = $activeBranchId;
                $model->company_id = null; // Cabang aktif -> company_id (PT) harus NULL
            } else {
                $model->branch_id = null;
                if (empty($model->company_id) && session()->has('active_company_id')) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            }
        });
    }

    /**
     * Scope for multi-company tenancy & branch isolation
     * - Jika aktif sebagai Cabang (branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika aktif sebagai PT Pusat (branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: Auth::user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> hanya data cabang ini (company_id NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat -> hanya data level PT (branch_id NULL)
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function sales()
    {
        return $this->belongsTo(Karyawan::class, 'sales_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function ppnPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function pphPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'pph_pajak_id');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class, 'sales_quotation_id')->orderBy('urutan', 'asc');
    }

    public function parentQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'parent_quotation_id');
    }

    public function revisions()
    {
        return $this->hasMany(SalesQuotation::class, 'parent_quotation_id')->orderBy('revisi', 'asc');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Auto generate Quotation Number: SQ/{PREFIX}/{YYYY}/{MM}/{XXXX}
     * - Jika di Cabang: Prefix kode cabang (SQ/CBG001/2026/09/0001)
     * - Jika di PT Pusat: Prefix kode PT (SQ/PT-MB/2026/09/0001)
     * Dilengkapi MySQL Advisory Lock & while-loop fallback untuk mencegah duplikasi saat input bersamaan.
     */
    public static function generateNomor($companyId = null, $branchId = null)
    {
        $activeBranchId = $branchId !== null ? $branchId : session('active_branch_id');
        $year = date('Y');
        $month = date('m');

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            $prefixBranch = $branch ? ($branch->kode_cabang ?: 'CBG') : 'CBG';
            $prefix = "SQ/{$prefixBranch}/{$year}/{$month}/";
        } else {
            $companyId = $companyId ?: (session('active_company_id') ?: (Auth::user()?->company_id ?? 1));
            $company = Company::find($companyId);
            $prefixCompany = $company ? ($company->kode_perusahaan ?: 'CP') : 'CP';
            $prefix = "SQ/{$prefixCompany}/{$year}/{$month}/";
        }

        // Lock to ensure concurrency safety across simultaneous requests
        $lockKey = 'sq_generate_lock_' . md5($prefix);
        $gotLock = false;
        try {
            $lockRes = DB::select("SELECT GET_LOCK(?, 10) as lk", [$lockKey]);
            $gotLock = !empty($lockRes) && $lockRes[0]->lk == 1;

            // Find all existing records with this prefix (including soft-deleted)
            $existingNumbers = self::withTrashed()
                ->where('nomor_quotation', 'like', $prefix . '%')
                ->pluck('nomor_quotation');

            $maxSeq = 0;
            foreach ($existingNumbers as $num) {
                $parts = explode('/', $num);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    $val = (int)$lastPart;
                    if ($val > $maxSeq) {
                        $maxSeq = $val;
                    }
                }
            }

            $nextNum = $maxSeq + 1;
            $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Double check existence to guarantee 100% uniqueness
            while (self::withTrashed()->where('nomor_quotation', $candidate)->exists()) {
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
