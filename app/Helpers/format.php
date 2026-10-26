<?php

use App\Support\Format;

if (!function_exists('indian_money')) {
    /**
     * Format a number in Indian currency style (e.g. 1,65,000.00).
     *
     * @param float|int|string|null $amount
     * @return string
     */
    function indian_money(float|int|string|null $amount): string
    {
        return Format::indianMoney($amount);
    }
}

if (!function_exists('rate_unit_label')) {
    /**
     * Get rate unit suffix label based on bhav configuration.
     * Examples: 10 -> "(/10GM)", 1 -> "(/GM)", any other n -> "(/nGM)"
     * If $withParentheses is false: 10 -> "/10GM", 1 -> "/GM", n -> "/nGM"
     *
     * @param int|null $unitGrams
     * @param bool $withParentheses
     * @return string
     */
    function rate_unit_label(?int $unitGrams = null, bool $withParentheses = true): string
    {
        return Format::rateUnitLabel($unitGrams, $withParentheses);
    }
}
