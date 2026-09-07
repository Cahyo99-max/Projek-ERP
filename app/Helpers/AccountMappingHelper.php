<?php

namespace App\Helpers;

use App\Models\AccountMapping;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Cache;

class AccountMappingHelper
{
    /**
     * Dapatkan ID Akun COA berdasarkan Group & Mapping Key per PT / Company.
     * Jika tidak ditemukan, akan fallback ke $defaultKey (jika diset).
     *
     * @param string $group Group mapping (e.g. 'default', 'inventory_category', 'freight', 'tax', 'payment_method')
     * @param string $key Key mapping (e.g. 'cat_1_inventory', 'freight_in', 'tax_ppn_masukan')
     * @param string|null $defaultKey Fallback key di group 'default' (e.g. 'default_inventory')
     * @param int|null $companyId Optional explicit company ID (defaults to active session or 1)
     * @return int|null ID COA
     */
    public static function getCoaId(string $group, string $key, ?string $defaultKey = null, ?int $companyId = null): ?int
    {
        $companyId = $companyId ?: (session('active_company_id') ?: 1);
        $cacheKey = "account_mapping_{$companyId}_{$group}_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($companyId, $group, $key, $defaultKey) {
            // 1. Cari mapping spesifik untuk PT aktif
            $mapping = AccountMapping::where('company_id', $companyId)
                ->where('group', $group)
                ->where('mapping_key', $key)
                ->first();

            if ($mapping && $mapping->coa_id) {
                return $mapping->coa_id;
            }

            // 2. Jika tidak ada dan ada $defaultKey, cari di group default untuk PT aktif
            if ($defaultKey) {
                $defaultMapping = AccountMapping::where('company_id', $companyId)
                    ->where('group', 'default')
                    ->where('mapping_key', $defaultKey)
                    ->first();

                if ($defaultMapping && $defaultMapping->coa_id) {
                    return $defaultMapping->coa_id;
                }
            }

            return null;
        });
    }

    /**
     * Dapatkan Objek Model ChartOfAccount berdasarkan Group & Mapping Key per PT.
     */
    public static function getCoa(string $group, string $key, ?string $defaultKey = null, ?int $companyId = null): ?ChartOfAccount
    {
        $coaId = self::getCoaId($group, $key, $defaultKey, $companyId);
        if (!$coaId) {
            return null;
        }

        return ChartOfAccount::find($coaId);
    }

    /**
     * Set atau update Account Mapping & bersihkan cache per PT.
     */
    public static function setMapping(string $group, string $key, int $coaId, ?string $description = null): AccountMapping
    {
        $companyId = session('active_company_id') ?: 1;

        $mapping = AccountMapping::updateOrCreate(
            [
                'company_id'  => $companyId,
                'group'       => $group,
                'mapping_key' => $key,
            ],
            [
                'coa_id'      => $coaId,
                'description' => $description,
            ]
        );

        Cache::forget("account_mapping_{$companyId}_{$group}_{$key}");

        return $mapping;
    }

    /**
     * Hapus cache semua mapping untuk PT tertentu atau PT aktif.
     */
    public static function clearCache(?int $companyId = null): void
    {
        $cId = $companyId ?: (session('active_company_id') ?: 1);
        Cache::flush();
    }
}
