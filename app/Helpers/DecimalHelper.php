<?php

namespace App\Helpers;

class DecimalHelper
{
    /**
     * Format/sanitize input to a clean decimal string for BCMath without float conversion.
     */
    public static function str(mixed $val): string
    {
        if ($val === null || $val === '') {
            return '0';
        }
        if (is_numeric($val)) {
            $str = (string)$val;
            if (str_contains(strtolower($str), 'e')) {
                return sprintf("%.12f", (float)$val);
            }
            return $str;
        }
        return '0';
    }

    /**
     * Add two decimal values using BCMath.
     */
    public static function add(mixed $a, mixed $b, int $scale = 4): string
    {
        return bcadd(static::str($a), static::str($b), $scale);
    }

    /**
     * Subtract two decimal values using BCMath.
     */
    public static function sub(mixed $a, mixed $b, int $scale = 4): string
    {
        return bcsub(static::str($a), static::str($b), $scale);
    }

    /**
     * Multiply two decimal values using BCMath.
     */
    public static function mul(mixed $a, mixed $b, int $scale = 4): string
    {
        return bcmul(static::str($a), static::str($b), $scale);
    }

    /**
     * Divide two decimal values using BCMath.
     */
    public static function div(mixed $a, mixed $b, int $scale = 6): string
    {
        $valA = static::str($a);
        $valB = static::str($b);

        if (bccomp($valB, '0', 8) === 0) {
            return sprintf("%.{$scale}f", 0);
        }

        return bcdiv($valA, $valB, $scale);
    }

    /**
     * Compare two decimal values using BCMath.
     * Returns 0 if equal, 1 if $a > $b, -1 if $a < $b.
     */
    public static function comp(mixed $a, mixed $b, int $scale = 4): int
    {
        return bccomp(static::str($a), static::str($b), $scale);
    }
}
