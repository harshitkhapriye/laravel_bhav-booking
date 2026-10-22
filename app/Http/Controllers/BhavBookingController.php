<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBhavBookingRequest;
use App\Models\BhavBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BhavBookingController extends Controller
{
    /**
     * Display the Bhav Booking UI page.
     */
    public function index()
    {
        return view('buy-sell.bhav-booking');
    }

    /**
     * Store a newly created Bhav Booking in storage.
     *
     * @param StoreBhavBookingRequest $request
     * @return JsonResponse
     */
    public function store(StoreBhavBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // 1. DUPLICATE PROTECTION (Idempotency)
        // Why: If a network hiccup or double-click occurs with the same request_token UUID,
        // we return the existing booking instead of inserting a duplicate record.
        $existingBooking = BhavBooking::where('request_token', $validated['request_token'])
            ->with(['items', 'customer'])
            ->first();

        if ($existingBooking) {
            return response()->json($existingBooking, 200);
        }

        // 2. SERVER-SIDE RECALCULATION
        // Why: Never trust client-submitted calculations. The server must be the single source of truth.
        // Rate unit is 10 grams (standard in Indian bullion trade).
        $rateUnitGrams = config('bhav.rate_unit_grams', 10);

        $calculatedItems = [];
        $totalWeight = 0.0;
        $totalAmount = 0.0;

        foreach ($validated['items'] as $index => $itemData) {
            $weight = round((float) $itemData['weight'], 3);
            $rate = round((float) $itemData['rate'], 2);

            // Row amount formula: Weight * Rate / rate_unit_grams
            $rowAmount = round(($weight * $rate) / $rateUnitGrams, 2);

            $totalWeight += $weight;
            $totalAmount += $rowAmount;

            $calculatedItems[] = [
                'sort_order' => $index + 1,
                'weight'     => $weight,
                'rate'       => $rate,
                'amount'     => $rowAmount,
            ];
        }

        // Round grand totals properly (avoid floating-point inaccuracies)
        $totalWeight = round($totalWeight, 3);
        $totalAmount = round($totalAmount, 2);

        // 3. DATABASE TRANSACTION WITH ROW LOCK
        // Why: Database transactions ensure either everything saves or nothing saves.
        // Generating booking_no inside the transaction with a lock prevents two simultaneous users
        // from receiving the same booking number (BB-000001).
        $booking = DB::transaction(function () use ($validated, $calculatedItems, $totalWeight, $totalAmount) {
            $bookingNo = BhavBooking::nextBookingNo(true);

            $newBooking = BhavBooking::create([
                'booking_no'       => $bookingNo,
                'customer_id'      => $validated['customer_id'],
                'trade_type'       => $validated['trade_type'],
                'entry_type'       => $validated['entry_type'],
                'metal'            => $validated['metal'],
                'total_weight'     => $totalWeight,
                'total_amount'     => $totalAmount,
                'delivered_weight' => 0.000,
                'given_amount'     => 0.00,
                'received_amount'  => 0.00,
                'status'           => 'PENDING',
                'narration'        => $validated['narration'] ?? null,
                'send_whatsapp'    => $validated['send_whatsapp'] ?? false,
                'request_token'    => $validated['request_token'],
                'booked_at'        => now(),
            ]);

            // Save line items
            $newBooking->items()->createMany($calculatedItems);

            return $newBooking;
        });

        // Load relationships (items and customer) for JSON response
        $booking->load(['items', 'customer']);

        // Return HTTP 201 Created
        return response()->json($booking, 201);
    }
}
