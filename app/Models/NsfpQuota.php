<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class NsfpQuota extends Model
{
    use HasFactory;

    protected $table = 'nsfp_quotas';

    protected $fillable = [
        'company_id',
        'branch_id',
        'tahun',
        'prefix',
        'nomor_awal',
        'nomor_akhir',
        'nomor_terakhir',
        'tanggal_surat',
        'status',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Dapatkan nomor seri faktur pajak berikutnya secara otomatis dari jatah yang aktif
     */
    public static function getNextAvailableNsfp($companyId = null, $branchId = null, $jenisTransaksi = '01', $jenisFaktur = '0'): ?string
    {
        $year = date('y'); // 2 digit tahun, misal 26
        $fullYear = date('Y');

        $activeBranchId = $branchId ?: session('active_branch_id');
        $activeCompanyId = $companyId ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1));

        $quota = self::where('status', 'Aktif')
            ->where('tahun', $fullYear)
            ->where(function($q) use ($activeBranchId, $activeCompanyId) {
                if (!empty($activeBranchId)) {
                    $q->where('branch_id', $activeBranchId);
                } else {
                    $q->whereNull('branch_id')->where('company_id', $activeCompanyId);
                }
            })
            ->lockForUpdate()
            ->first();

        if (!$quota) {
            return null;
        }

        // Tentukan nomor urut integer berikutnya
        $nomorAwalInt = (int) preg_replace('/[^\d]/', '', $quota->nomor_awal);
        $nomorAkhirInt = (int) preg_replace('/[^\d]/', '', $quota->nomor_akhir);
        
        $currentInt = $quota->nomor_terakhir 
            ? (int) preg_replace('/[^\d]/', '', $quota->nomor_terakhir) 
            : ($nomorAwalInt - 1);

        $nextInt = $currentInt + 1;

        if ($nextInt > $nomorAkhirInt) {
            $quota->update(['status' => 'Habis']);
            return null;
        }

        $nextPadded = str_pad($nextInt, 8, '0', STR_PAD_LEFT);
        $quota->update(['nomor_terakhir' => $nextPadded]);

        if ($nextInt >= $nomorAkhirInt) {
            $quota->update(['status' => 'Habis']);
        }

        // Format standar DJP: [KD_TRANSAKSI][STATUS_PENGGANTI].[KODE_CABANG]-[TAHUN].[NOMOR_URUT_8_DIGIT]
        // Contoh: 010.000-26.00000001
        $kdTrx = str_pad($jenisTransaksi ?: '01', 2, '0', STR_PAD_LEFT);
        $fgPengganti = $jenisFaktur ?: '0';
        $prefix = "{$kdTrx}{$fgPengganti}.000-{$year}.";

        return $prefix . $nextPadded;
    }
}
