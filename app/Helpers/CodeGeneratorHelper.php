<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class CodeGeneratorHelper
{
    /**
     * Generate unique codes for ERP master data (e.g. STN-0001, BRG-0001)
     *
     * @param string $table
     * @param string $prefix
     * @param string $column
     * @param int $length
     * @return string
     */
    public static function generate(string $table, string $prefix, string $column = 'kode_satuan', int $length = 4, $companyId = null): string
    {
        $isGlobal = ($companyId === false);
        if (!$isGlobal) {
            $companyId = $companyId ?? session('active_company_id');
        } else {
            $companyId = null;
        }

        $prefixLen = strlen($prefix);

        $query = DB::table($table)->where($column, 'like', $prefix . '%');
        if (!$isGlobal && $companyId && \Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id')) {
            $query->where('company_id', $companyId);
        }

        $codes = $query->pluck($column);

        $maxNumber = 0;
        foreach ($codes as $codeStr) {
            $suffix = substr($codeStr, $prefixLen);
            if (is_numeric($suffix)) {
                $val = (int) $suffix;
                if ($val > $maxNumber) {
                    $maxNumber = $val;
                }
            }
        }

        $number = $maxNumber + 1;
        $code = $prefix . str_pad($number, $length, '0', STR_PAD_LEFT);

        while (
            DB::table($table)
                ->where($column, $code)
                ->when(!$isGlobal && $companyId && \Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id'), function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->exists()
        ) {
            $number++;
            $code = $prefix . str_pad($number, $length, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
