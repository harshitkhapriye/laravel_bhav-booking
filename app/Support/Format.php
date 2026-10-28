<?php

namespace App\Support;

class Format
{
    /**
     * Format a number in Indian currency style (e.g. 1,65,000.00).
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
            $formattedInt = $formattedRemaining.','.$lastThree;
        }

        return ($isNegative ? '-' : '').$formattedInt.'.'.$decPart;
    }

    /**
     * Get rate unit suffix label based on bhav configuration.
     * Examples: 10 -> "(/10GM)", 1 -> "(/GM)", any other n -> "(/nGM)"
     * If $withParentheses is false: 10 -> "/10GM", 1 -> "/GM", n -> "/nGM"
     */
    public static function rateUnitLabel(?int $unitGrams = null, bool $withParentheses = true): string
    {
        $unit = $unitGrams ?? (int) config('bhav.rate_unit_grams', 10);
        $suffix = $unit === 1 ? '/GM' : "/{$unit}GM";

        return $withParentheses ? "({$suffix})" : $suffix;
    }

    /**
     * Mask Aadhaar number (e.g. "XXXX XXXX 1234").
     */
    public static function maskAadhaar(?string $aadhaar): ?string
    {
        if (! $aadhaar) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $aadhaar);
        if (strlen($digits) < 4) {
            return 'XXXX XXXX XXXX';
        }
        $last4 = substr($digits, -4);

        return 'XXXX XXXX '.$last4;
    }

    /**
     * Mask PAN number (e.g. "XXXXX1234X").
     */
    public static function maskPan(?string $pan): ?string
    {
        if (! $pan) {
            return null;
        }
        $clean = strtoupper(trim($pan));
        if (strlen($clean) === 10) {
            return 'XXXXX'.substr($clean, 5, 4).'X';
        }

        return 'XXXXX'.substr($clean, -5);
    }

    /**
     * Convert an amount into Indian words (e.g. "Rupees One Lakh Sixty Five Thousand Only").
     * Supports crore, lakh, thousand, and paise ("and Fifty Paise").
     */
    public static function amountToIndianWords(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return 'Rupees Zero Only';
        }

        $num = (float) $amount;
        if ($num < 0) {
            $num = abs($num);
        }

        $parts = explode('.', sprintf('%.2f', $num));
        $rupees = (int) $parts[0];
        $paise = isset($parts[1]) ? (int) $parts[1] : 0;

        $rupeeWords = self::convertNumberToIndianWords($rupees);
        if ($rupeeWords === '') {
            $rupeeWords = 'Zero';
        }

        $paiseWords = '';
        if ($paise > 0) {
            $paiseWords = self::convertNumberToIndianWords($paise).' Paise';
        }

        if ($paiseWords !== '') {
            return 'Rupees '.$rupeeWords.' and '.$paiseWords.' Only';
        }

        return 'Rupees '.$rupeeWords.' Only';
    }

    /**
     * Internal recursive helper for Indian numbering word conversion.
     */
    private static function convertNumberToIndianWords(int $n): string
    {
        if ($n === 0) {
            return '';
        }

        $units = [
            1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
        ];

        $convertTwoDigits = function (int $num) use ($units, $tens) {
            if ($num === 0) return '';
            if ($num < 20) return $units[$num];
            $t = (int) ($num / 10);
            $u = $num % 10;
            return $tens[$t].($u > 0 ? ' '.$units[$u] : '');
        };

        $convertThreeDigits = function (int $num) use ($units, $convertTwoDigits) {
            $h = (int) ($num / 100);
            $rem = $num % 100;
            $res = [];
            if ($h > 0) {
                $res[] = $units[$h].' Hundred';
            }
            if ($rem > 0) {
                $res[] = $convertTwoDigits($rem);
            }
            return implode(' ', $res);
        };

        $crores = (int) ($n / 10000000);
        $n = $n % 10000000;
        $lakhs = (int) ($n / 100000);
        $n = $n % 100000;
        $thousands = (int) ($n / 1000);
        $remaining = $n % 1000;

        $words = [];

        if ($crores > 0) {
            $words[] = self::convertNumberToIndianWords($crores).' Crore';
        }
        if ($lakhs > 0) {
            $words[] = $convertTwoDigits($lakhs).' Lakh';
        }
        if ($thousands > 0) {
            $words[] = $convertTwoDigits($thousands).' Thousand';
        }
        if ($remaining > 0) {
            $words[] = $convertThreeDigits($remaining);
        }

        return implode(' ', $words);
    }
}
