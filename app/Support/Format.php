<?php

namespace App\Support;

class Format
{
    /**
     * Format a number in Indian currency style (e.g. 1,65,000.00).
     *
     * @param float|int|string|null $amount
     * @return string
     */
    public static function indianMoney(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '0.00';
        }

        $num = (float) $amount;
        $isNegative = $num < 0;
        $absNum = abs($num);
        $parts = explode('.', sprintf('%.2f', $absNum));
        $intPart = $parts[0];
        $decPart = $parts[1] ?? '00';

        if (strlen($intPart) <= 3) {
            $formattedInt = $intPart;
        } else {
            $lastThree = substr($intPart, -3);
            $remaining = substr($intPart, 0, -3);
            $formattedRemaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
            $formattedInt = $formattedRemaining . ',' . $lastThree;
        }

        return ($isNegative ? '-' : '') . $formattedInt . '.' . $decPart;
    }

    /**
     * Get rate unit suffix label based on bhav configuration.
     * Examples: 10 -> "(/10GM)", 1 -> "(/GM)", any other n -> "(/nGM)"
     * If $withParentheses is false: 10 -> "/10GM", 1 -> "/GM", n -> "/nGM"
     *
     * @param int|null $unitGrams
     * @param bool $withParentheses
     * @return string
     */
    public static function rateUnitLabel(?int $unitGrams = null, bool $withParentheses = true): string
    {
        $unit = $unitGrams ?? (int) config('bhav.rate_unit_grams', 10);
        $suffix = $unit === 1 ? '/GM' : "/{$unit}GM";
        return $withParentheses ? "({$suffix})" : $suffix;
    }

    /**
     * Mask Aadhaar number (e.g. "XXXX XXXX 1234").
     *
     * @param string|null $aadhaar
     * @return string|null
     */
    public static function maskAadhaar(?string $aadhaar): ?string
    {
        if (!$aadhaar) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $aadhaar);
        if (strlen($digits) < 4) {
            return 'XXXX XXXX XXXX';
        }
        $last4 = substr($digits, -4);
        return 'XXXX XXXX ' . $last4;
    }

    /**
     * Mask PAN number (e.g. "XXXXX1234X").
     *
     * @param string|null $pan
     * @return string|null
     */
    public static function maskPan(?string $pan): ?string
    {
        if (!$pan) {
            return null;
        }
        $clean = strtoupper(trim($pan));
        if (strlen($clean) === 10) {
            return 'XXXXX' . substr($clean, 5, 4) . 'X';
        }
        return 'XXXXX' . substr($clean, -5);
    }
}
