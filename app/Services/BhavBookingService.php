<?php

namespace App\Services;

use App\Models\BhavBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
     * Allowed only if status = PENDING and delivered_weight = 0 and NO payments with source BUY_SELL.
     * Payments made at booking time (source BOOKING) do NOT block edit/delete.
     *
     * @param BhavBooking $booking
     * @return bool
     */
    public function canModify(BhavBooking $booking): bool
    {
        if ($booking->status !== 'PENDING' || round((float) $booking->delivered_weight, 3) != 0) {
            return false;
        }

        if ($booking->relationLoaded('payments')) {
            return ! $booking->payments->contains('source', 'BUY_SELL');
        }

        return ! $booking->payments()->where('source', 'BUY_SELL')->exists();
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
            ->with(['items', 'customer', 'payments'])
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

        // 3. PAYMENT TOTAL VALIDATION
        // Check that sum of payments does not exceed booking total amount
        $paymentsData = $validated['payments'] ?? [];
        $totalPayments = 0.0;
        foreach ($paymentsData as $p) {
            $totalPayments += (float) $p['amount'];
        }
        $totalPayments = round($totalPayments, 2);

        if ($totalPayments > $calculated['total_amount']) {
            throw ValidationException::withMessages([
                'payments' => ["Payment cannot be more than booking amount ₹" . indian_money($calculated['total_amount'])],
            ]);
        }

        // Direction: SELL booking -> customer pays shop -> received_amount
        //            BUY booking  -> shop pays customer -> given_amount
        $isSell = $validated['trade_type'] === 'SELL';
        $receivedAmount = $isSell ? $totalPayments : 0.00;
        $givenAmount = $isSell ? 0.00 : $totalPayments;

        // 4. DATABASE TRANSACTION WITH ROW LOCK
        // Why: Prevents race conditions when two users save bookings at the exact same millisecond.
        $booking = DB::transaction(function () use ($validated, $calculated, $paymentsData, $givenAmount, $receivedAmount) {
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
                'given_amount'     => $givenAmount,
                'received_amount'  => $receivedAmount,
                'status'           => 'PENDING',
                'narration'        => $validated['narration'] ?? null,
                'send_whatsapp'    => $validated['send_whatsapp'] ?? false,
                'request_token'    => $validated['request_token'],
                'booked_at'        => now(),
            ]);

            // Save line items
            $newBooking->items()->createMany($calculated['items']);

            // Save payments with source = 'BOOKING'
            if (!empty($paymentsData)) {
                $now = now();
                $paymentRows = [];
                foreach ($paymentsData as $index => $payment) {
                    $paymentRows[] = [
                        'mode'          => $payment['mode'],
                        'online_method' => $payment['mode'] === 'ONLINE' ? ($payment['online_method'] ?? 'UPI') : null,
                        'reference_no'  => $payment['reference_no'] ?? null,
                        'amount'        => round((float) $payment['amount'], 2),
                        'source'        => 'BOOKING',
                        'sort_order'    => $index + 1,
                        'paid_at'       => $now,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
                $newBooking->payments()->createMany($paymentRows);
            }

            return $newBooking;
        });

        $booking->load(['items', 'customer', 'payments']);

        return [
            'booking' => $booking,
            'created' => true,
        ];
    }

    /**
     * Update an existing Bhav Booking.
     * Recalculates totals and atomically replaces line items and BOOKING payments inside a DB transaction.
     * Never touches BUY_SELL source payments.
     *
     * @param BhavBooking $booking
     * @param array $validated
     * @return BhavBooking
     */
    public function update(BhavBooking $booking, array $validated): BhavBooking
    {
        // 1. RECALCULATE ON SERVER
        $calculated = $this->calculateItems($validated['items']);

        // 2. PAYMENT TOTAL VALIDATION
        // Check existing BUY_SELL payments + new BOOKING payments <= total_amount
        $existingBuySellTotal = (float) $booking->payments()
            ->where('source', 'BUY_SELL')
            ->sum('amount');

        $paymentsData = $validated['payments'] ?? [];
        $bookingPaymentsTotal = 0.0;
        foreach ($paymentsData as $p) {
            $bookingPaymentsTotal += (float) $p['amount'];
        }
        $bookingPaymentsTotal = round($bookingPaymentsTotal, 2);
        $totalPayments = round($existingBuySellTotal + $bookingPaymentsTotal, 2);

        if ($totalPayments > $calculated['total_amount']) {
            throw ValidationException::withMessages([
                'payments' => ["Payment cannot be more than booking amount ₹" . indian_money($calculated['total_amount'])],
            ]);
        }

        // Recalculate given_amount / received_amount based on current trade_type
        $isSell = $validated['trade_type'] === 'SELL';
        $receivedAmount = $isSell ? $totalPayments : 0.00;
        $givenAmount = $isSell ? 0.00 : $totalPayments;

        // 3. ATOMIC DATABASE TRANSACTION
        DB::transaction(function () use ($booking, $validated, $calculated, $paymentsData, $givenAmount, $receivedAmount) {
            $booking->update([
                // customer_id is immutable in edit mode: always retain original value from database
                'trade_type'      => $validated['trade_type'],
                'entry_type'      => $validated['entry_type'],
                'metal'           => $validated['metal'],
                'total_weight'    => $calculated['total_weight'],
                'total_amount'    => $calculated['total_amount'],
                'given_amount'    => $givenAmount,
                'received_amount' => $receivedAmount,
                'narration'       => $validated['narration'] ?? null,
                'send_whatsapp'   => $validated['send_whatsapp'] ?? false,
            ]);

            // Delete old items and insert fresh calculated items
            $booking->items()->delete();
            $booking->items()->createMany($calculated['items']);

            // Replace only the payments with source BOOKING; never touch source BUY_SELL payments
            $booking->payments()->where('source', 'BOOKING')->delete();

            if (!empty($paymentsData)) {
                $now = now();
                $paymentRows = [];
                foreach ($paymentsData as $index => $payment) {
                    $paymentRows[] = [
                        'mode'          => $payment['mode'],
                        'online_method' => $payment['mode'] === 'ONLINE' ? ($payment['online_method'] ?? 'UPI') : null,
                        'reference_no'  => $payment['reference_no'] ?? null,
                        'amount'        => round((float) $payment['amount'], 2),
                        'source'        => 'BOOKING',
                        'sort_order'    => $index + 1,
                        'paid_at'       => $now,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
                $booking->payments()->createMany($paymentRows);
            }
        });

        $booking->load(['items', 'customer', 'payments']);

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
