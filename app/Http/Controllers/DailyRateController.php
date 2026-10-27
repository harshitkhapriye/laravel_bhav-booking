<?php

namespace App\Http\Controllers;

use App\Models\DailyRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyRateController extends Controller
{
    /**
     * Get today's bullion rates for Gold and Silver.
     */
    public function today(Request $request): JsonResponse
    {
        return response()->json(DailyRate::getTodayRates());
    }

    /**
     * Store or update today's bullion rates.
     * Accepts gold_rate / silver_rate directly or an array of rates.
     * Rate must be strictly greater than 0.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gold_rate' => ['nullable', 'numeric', 'gt:0'],
            'silver_rate' => ['nullable', 'numeric', 'gt:0'],
            'rates' => ['nullable', 'array'],
            'rates.*.metal' => ['required_with:rates', 'in:GOLD,SILVER'],
            'rates.*.rate' => ['required_with:rates', 'numeric', 'gt:0'],
        ]);

        $today = now()->toDateString();

        // Handle direct fields
        if (isset($validated['gold_rate'])) {
            DailyRate::updateOrCreate(
                ['metal' => 'GOLD', 'rate_date' => $today],
                ['rate' => round((float) $validated['gold_rate'], 2)]
            );
        }

        if (isset($validated['silver_rate'])) {
            DailyRate::updateOrCreate(
                ['metal' => 'SILVER', 'rate_date' => $today],
                ['rate' => round((float) $validated['silver_rate'], 2)]
            );
        }

        // Handle array of rates
        if (! empty($validated['rates'])) {
            foreach ($validated['rates'] as $rateItem) {
                DailyRate::updateOrCreate(
                    ['metal' => strtoupper($rateItem['metal']), 'rate_date' => $today],
                    ['rate' => round((float) $rateItem['rate'], 2)]
                );
            }
        }

        return response()->json([
            'message' => "Today's rates updated successfully.",
            'rates' => DailyRate::getTodayRates(),
        ]);
    }
}
