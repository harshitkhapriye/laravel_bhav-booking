<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRate extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metal',
        'rate',
        'rate_date',
    ];

    /**
     * Attribute casts.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate'      => 'decimal:2',
        'rate_date' => 'date:Y-m-d',
    ];

    /**
     * Get today's rate for a specific metal, or null if not set.
     *
     * @param string $metal
     * @return float|null
     */
    public static function getTodayRate(string $metal): ?float
    {
        $today = now()->toDateString();
        $record = static::where('metal', strtoupper($metal))
            ->where('rate_date', $today)
            ->first();

        return $record ? (float) $record->rate : null;
    }

    /**
     * Get today's rates for all metals with fallback indicators.
     *
     * @return array
     */
    public static function getTodayRates(): array
    {
        $today = now()->toDateString();
        $rates = static::where('rate_date', $today)->get()->keyBy('metal');

        $goldDefault = (float) config('bhav.default_rates.GOLD', 165000);
        $silverDefault = (float) config('bhav.default_rates.SILVER', 1800);

        $goldRecord = $rates->get('GOLD');
        $silverRecord = $rates->get('SILVER');

        return [
            'GOLD' => [
                'rate'   => $goldRecord ? (float) $goldRecord->rate : $goldDefault,
                'is_set' => $goldRecord !== null,
            ],
            'SILVER' => [
                'rate'   => $silverRecord ? (float) $silverRecord->rate : $silverDefault,
                'is_set' => $silverRecord !== null,
            ],
        ];
    }
}
