<?php

namespace App\Services;

use App\Models\BhavBooking;
use Illuminate\Support\Facades\DB;

class BhavBookingService
{
    /**
     * Calculate line item amounts and grand totals using the bullion trade formula.
     * Formula: Amount = round(weight * rate / rate_unit_grams, 2)
     * Why: In Indian bullion markets, rates are quoted per 10 grams (1 tola).
     * Server-side calculation ensures consistent calculations that client cannot tamper with.
     *
     * @param array $items
     * @return array{items: array, total_weight: float, total_amount: float}
     */
    public function calculateItems(array $items): array
    {
        $rateUnitGrams = config('bhav.rate_unit_grams', 10);

        $calculatedItems = [];
        $totalWeight = 0.0;
        $totalAmount = 0.0;

        foreach ($items as $index => $itemData) {
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

        // Round grand totals to proper bullion decimals
        $totalWeight = round($totalWeight, 3);
        $totalAmount = round($totalAmount, 2);

        return [
            'items'        => $calculatedItems,
            'total_weight' => $totalWeight,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Check whether a booking can be edited or deleted.
     * Allowed only if status = PENDING and delivered_weight = 0 and given_amount = 0 and received_amount = 0.
     * Why: Once gold/silver is delivered or payment is received, altering the original booking
     * would corrupt financial ledgers and stock balance.
     *
     * @param BhavBooking $booking
     * @return bool
     */
    public function canModify(BhavBooking $booking): bool
    {
        return $booking->status === 'PENDING'
            && round((float) $booking->delivered_weight, 3) == 0
            && round((float) $booking->given_amount, 2) == 0
            && round((float) $booking->received_amount, 2) == 0;
    }

    /**
     * Create a new Bhav Booking.
     * Includes idempotency check, server calculation, and DB transaction with locking.
     *
     * @param array $validated
     * @return array{booking: BhavBooking, created: bool}
     */
    public function create(array $validated): array
    {
        // 1. DUPLICATE PROTECTION (Idempotency)
        // Why: Double-clicks or network retries with the same UUID return the existing booking
        // instead of creating an unwanted duplicate order.
        $existing = BhavBooking::where('request_token', $validated['request_token'])
            ->with(['items', 'customer'])
            ->first();

        if ($existing) {
            return [
                'booking' => $existing,
                'created' => false,
            ];
        }

        // 2. SERVER-SIDE RECALCULATION
        // Why: Shared formula ensures store and update always compute totals identically.
        $calculated = $this->calculateItems($validated['items']);

        // 3. DATABASE TRANSACTION WITH ROW LOCK
        // Why: Prevents race conditions when two users save bookings at the exact same millisecond.
        $booking = DB::transaction(function () use ($validated, $calculated) {
            $bookingNo = BhavBooking::nextBookingNo(true);

            $newBooking = BhavBooking::create([
                'booking_no'       => $bookingNo,
                'customer_id'      => $validated['customer_id'],
                'trade_type'       => $validated['trade_type'],
                'entry_type'       => $validated['entry_type'],
                'metal'            => $validated['metal'],
                'total_weight'     => $calculated['total_weight'],
                'total_amount'     => $calculated['total_amount'],
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
            $newBooking->items()->createMany($calculated['items']);

            return $newBooking;
        });

        $booking->load(['items', 'customer']);

        return [
            'booking' => $booking,
            'created' => true,
        ];
    }

    /**
     * Update an existing Bhav Booking.
     * Recalculates totals and atomically replaces line items inside a DB transaction.
     * Note: Keeps existing request_token, booking_no, and audit columns intact.
     *
     * @param BhavBooking $booking
     * @param array $validated
     * @return BhavBooking
     */
    public function update(BhavBooking $booking, array $validated): BhavBooking
    {
        // 1. RECALCULATE ON SERVER
        $calculated = $this->calculateItems($validated['items']);

        // 2. ATOMIC DATABASE TRANSACTION
        // Why: Either all parent fields and children items update successfully, or all rollback.
        DB::transaction(function () use ($booking, $validated, $calculated) {
            $booking->update([
                // customer_id is immutable in edit mode: always retain original value from database
                'trade_type'    => $validated['trade_type'],
                'entry_type'    => $validated['entry_type'],
                'metal'         => $validated['metal'],
                'total_weight'  => $calculated['total_weight'],
                'total_amount'  => $calculated['total_amount'],
                'narration'     => $validated['narration'] ?? null,
                'send_whatsapp' => $validated['send_whatsapp'] ?? false,
            ]);

            // Delete old items and insert fresh calculated items
            $booking->items()->delete();
            $booking->items()->createMany($calculated['items']);
        });

        $booking->load(['items', 'customer']);

        return $booking;
    }

    /**
     * Soft delete an existing Bhav Booking.
     *
     * @param BhavBooking $booking
     * @return bool
     */
    public function delete(BhavBooking $booking): bool
    {
        return (bool) $booking->delete();
    }
}
