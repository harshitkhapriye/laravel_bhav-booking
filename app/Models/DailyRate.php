<?php

namespace App\Models;

use Carbon\Carbon;
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
        'rate' => 'decimal:2',
        'rate_date' => 'date:Y-m-d',
    ];

    /**
     * Get today's rate for a specific metal, or null if not set.
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
     * Get today's rates for all metals with fallback indicators and previous rate trends.
     */
    public static function getTodayRates(): array
    {
        $today = now()->toDateString();
        $rates = static::where('rate_date', $today)->get()->keyBy('metal');

        $goldDefault = (float) config('bhav.default_rates.GOLD', 165000);
        $silverDefault = (float) config('bhav.default_rates.SILVER', 1800);

        $goldRecord = $rates->get('GOLD');
        $silverRecord = $rates->get('SILVER');

        $isGoldSet = $goldRecord !== null;
        $isSilverSet = $silverRecord !== null;

        $goldRate = $isGoldSet ? (float) $goldRecord->rate : $goldDefault;
        $silverRate = $isSilverSet ? (float) $silverRecord->rate : $silverDefault;

        // Previous available rates (latest before today)
        $prevGoldRecord = static::where('metal', 'GOLD')
            ->where('rate_date', '<', $today)
            ->orderByDesc('rate_date')
            ->first();

        $prevSilverRecord = static::where('metal', 'SILVER')
            ->where('rate_date', '<', $today)
            ->orderByDesc('rate_date')
            ->first();

        $prevGoldRate = $prevGoldRecord ? (float) $prevGoldRecord->rate : null;
        $prevGoldDate = $prevGoldRecord && $prevGoldRecord->rate_date ? Carbon::parse($prevGoldRecord->rate_date)->format('d-m-Y') : null;

        $goldChange = null;
        $goldPercent = null;
        if ($isGoldSet && $prevGoldRate !== null) {
            $goldChange = round($goldRate - $prevGoldRate, 2);
            $goldPercent = ($prevGoldRate > 0) ? round(($goldChange / $prevGoldRate) * 100, 2) : 0;
        }

        $prevSilverRate = $prevSilverRecord ? (float) $prevSilverRecord->rate : null;
        $prevSilverDate = $prevSilverRecord && $prevSilverRecord->rate_date ? Carbon::parse($prevSilverRecord->rate_date)->format('d-m-Y') : null;

        $silverChange = null;
        $silverPercent = null;
        if ($isSilverSet && $prevSilverRate !== null) {
            $silverChange = round($silverRate - $prevSilverRate, 2);
            $silverPercent = ($prevSilverRate > 0) ? round(($silverChange / $prevSilverRate) * 100, 2) : 0;
        }

        return [
            'GOLD' => [
                'rate' => $goldRate,
                'is_set' => $isGoldSet,
                'previous_rate' => $prevGoldRate,
                'previous_date' => $prevGoldDate,
                'change' => $goldChange,
                'percent' => $goldPercent,
            ],
            'SILVER' => [
                'rate' => $silverRate,
                'is_set' => $isSilverSet,
                'previous_rate' => $prevSilverRate,
                'previous_date' => $prevSilverDate,
                'change' => $silverChange,
                'percent' => $silverPercent,
            ],
        ];
    }
}
