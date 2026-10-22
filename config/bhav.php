<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bhav Booking Configuration
    |--------------------------------------------------------------------------
    |
    | rate_unit_grams: In the Indian bullion trade, gold and silver rates
    | are quoted per 10 grams (1 tola). Formula: Amount = Weight * Rate / rate_unit_grams
    |
    */

    'rate_unit_grams' => 10,

    'default_rates' => [
        'GOLD' => 165000,
        'SILVER' => 1800,
    ],

    'max_weight' => 99999.999,
    'max_rate' => 9999999.99,
];
