<?php

use App\Support\Format;

if (! function_exists('indian_money')) {
    /**
     * Format a number in Indian currency style (e.g. 1,65,000.00).
     */
    function indian_money(float|int|string|null $amount): string
    {
        return Format::indianMoney($amount);
    }
}

if (! function_exists('rate_unit_label')) {
    /**
     * Get rate unit suffix label based on bhav configuration.
     * Examples: 10 -> "(/10GM)", 1 -> "(/GM)", any other n -> "(/nGM)"
     * If $withParentheses is false: 10 -> "/10GM", 1 -> "/GM", n -> "/nGM"
     */
    function rate_unit_label(?int $unitGrams = null, bool $withParentheses = true): string
    {
        return Format::rateUnitLabel($unitGrams, $withParentheses);
    }
}

if (! function_exists('amount_to_indian_words')) {
    /**
     * Convert an amount into Indian words (e.g. "Rupees One Lakh Sixty Five Thousand Only").
     */
    function amount_to_indian_words(float|int|string|null $amount): string
    {
        return Format::amountToIndianWords($amount);
    }
}
